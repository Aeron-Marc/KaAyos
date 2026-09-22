<?php

namespace App\Http\Controllers\Worker;

use App\Events\BookingStatusUpdated;
use App\Events\JobCompletionStatusUpdated;
use App\Exceptions\BookingStateException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Earning;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingStatusChanged;
use App\Notifications\JobCompletionConfirmed;
use App\Notifications\JobCompletionRequested;
use App\Notifications\RescheduleRequested;
use App\Services\BookingMessageService;
use App\Support\TuyBarangays;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class WorkerDashboardController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $profile = $user->workerProfile;

        $activeJobs = $user->bookingsAsWorker()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->with('client')
            ->latest()
            ->get();

        $recentEarnings = Earning::where('worker_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'active_jobs' => $activeJobs->count(),
            'completed_jobs' => $user->bookingsAsWorker()->completed()->count(),
            'total_earnings' => Earning::where('worker_id', $user->id)->sum('net_amount'),
            'average_rating' => $profile?->average_rating ?? 0.00,
        ];

        return view('worker.dashboard.overview', compact('activeJobs', 'recentEarnings', 'stats'));
    }

    public function updateJobStatus(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        $user = auth()->user();

        if ($booking->worker_id !== $user->id) {
            abort(403, 'This job is not assigned to you.');
        }

        // Allow normal status transitions or completion mark
        $allowed = array_filter([
            Booking::STATUS_FLOW[$booking->status] ?? null,
            Booking::STATUS_CANCELLED,
        ]);

        // Workers can decline new bookings
        if ($booking->status === Booking::STATUS_NEW) {
            $allowed[] = Booking::STATUS_DECLINED;
        }

        // Special handling for completion - worker can mark complete when in_progress
        if ($booking->status === Booking::STATUS_IN_PROGRESS) {
            $allowed[] = Booking::STATUS_COMPLETED;
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', $allowed)],
        ]);

        if ($validated['status'] === Booking::STATUS_ACCEPTED) {
            $activeCount = $user->bookingsAsWorker()
                ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
                ->count();
            if ($activeCount >= config('kaayos.max_concurrent_jobs', 3)) {
                $msg = 'You have reached the maximum of '.config('kaayos.max_concurrent_jobs', 3).' concurrent jobs. Complete an existing job first.';
                if ($request->expectsJson()) {
                    return response()->json(['message' => $msg], 422);
                }

                return redirect()->back()->with('error', $msg);
            }
        }

        $oldStatus = $booking->status;

        try {
            // Handle completion confirmation workflow
            if ($validated['status'] === Booking::STATUS_COMPLETED) {
                $afterSave = function (Booking $fresh) use ($user) {
                    // Only record earnings if job is fully completed (both parties confirmed)
                    if ($fresh->status === Booking::STATUS_COMPLETED) {
                        $platformFeePercent = config('kaayos.platform_fee_percent', 10);
                        $gross = $fresh->price ?? 0;
                        $fee = round($gross * ($platformFeePercent / 100), 2);
                        $net = $gross - $fee;

                        Earning::updateOrCreate(
                            ['booking_id' => $fresh->id],
                            [
                                'worker_id' => $user->id,
                                'gross_amount' => $gross,
                                'platform_fee' => $fee,
                                'net_amount' => $net,
                            ]
                        );
                    }
                };

                $booking->markComplete($user, $afterSave);
                $booking->fresh();

                // Notify client if this was first request
                if ($booking->completion_requested_by === $user->id && $booking->confirmed_by_client_at === null) {
                    Notification::send(
                        $booking->client,
                        new JobCompletionRequested($booking, $user->name, 'worker')
                    );
                } elseif ($booking->confirmed_by_client_at !== null && $booking->status === Booking::STATUS_COMPLETED) {
                    // Both confirmed - notify of full completion
                    Notification::send(
                        $booking->client,
                        new JobCompletionConfirmed($booking, $user->name, 'worker')
                    );
                }
            } elseif ($validated['status'] === Booking::STATUS_ACCEPTED) {
                $booking->update(['agreed_by_worker_at' => now()]);
                $booking->transitionTo($validated['status'], auth()->id());
                $booking->load('client');
                Notification::send($booking->client, new BookingStatusChanged($booking, $oldStatus));
            } elseif ($validated['status'] === Booking::STATUS_CANCELLED) {
                $booking->cancel($request->input('reason', 'Cancelled by worker'), auth()->id());
                $booking->load('client');
                Notification::send($booking->client, new BookingCancelled($booking, $user->name));
            } elseif ($validated['status'] === Booking::STATUS_DECLINED) {
                $booking->decline($request->input('reason', 'Declined by worker'), auth()->id());
                $booking->load('client');
                Notification::send($booking->client, new BookingStatusChanged($booking, $oldStatus));
            } else {
                $booking->transitionTo($validated['status'], auth()->id());
            }
        } catch (BookingStateException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 409);
            }

            return redirect()->back()->with('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }

        $booking->load('client');

        // Broadcast completion status if relevant
        if ($oldStatus !== $booking->status) {
        if ($oldStatus !== $booking->status || $booking->isCompletionPending()) {
            if ($booking->isCompletionPending()) {
                broadcast(new JobCompletionStatusUpdated(
                    $booking,
                    $user->id === $booking->worker_id ? 'worker' : 'client',
                    false
                ))->toOthers();
            } elseif ($booking->status === Booking::STATUS_COMPLETED) {
                broadcast(new JobCompletionStatusUpdated(
                    $booking,
                    $user->id === $booking->worker_id ? 'worker' : 'client',
                    true
                ))->toOthers();
            } else {
                broadcast(new BookingStatusUpdated($booking, $oldStatus))->toOthers();
            }
        }

        BookingMessageService::post($booking, $booking->status);

        if ($booking->isCompletionPending()) {
            $successMessage = 'Completion request sent! Waiting for client confirmation to finalize.';
        } elseif ($booking->status === Booking::STATUS_COMPLETED) {
            $successMessage = 'Job completed successfully! Both parties have confirmed.';
        } else {
            $successMessage = 'Job status updated to ' . ucfirst(str_replace('_', ' ', $booking->status)) . '.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Job status updated successfully.',
                'success' => true,
                'message' => $successMessage,
                'booking' => $booking->fresh()->load('earning'),
                'completion_status' => $booking->getCompletionStatus(),
            ]);
        }

        return redirect()->back()->with('success', 'Job status updated successfully.');
        return redirect()->back()->with('success', $successMessage);
    }

    public function confirmJobCompletion(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        $user = auth()->user();

        if ($booking->worker_id !== $user->id) {
            abort(403, 'This job is not assigned to you.');
        }

        if (! $booking->isCompletionPending()) {
            $msg = 'This job is not awaiting completion confirmation.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        try {
            $afterSave = function (Booking $fresh) use ($user) {
                // Record earnings if job is now fully completed
                if ($fresh->status === Booking::STATUS_COMPLETED) {
                    $platformFeePercent = config('kaayos.platform_fee_percent', 10);
                    $gross = $fresh->price ?? 0;
                    $fee = round($gross * ($platformFeePercent / 100), 2);
                    $net = $gross - $fee;

                    Earning::updateOrCreate(
                        ['booking_id' => $fresh->id],
                        [
                            'worker_id' => $user->id,
                            'gross_amount' => $gross,
                            'platform_fee' => $fee,
                            'net_amount' => $net,
                        ]
                    );
                }
            };

            $booking->markComplete($user, $afterSave);
            $booking->fresh();
            $isFullyCompleted = $booking->status === Booking::STATUS_COMPLETED;

            $booking->load('client');

            // Notify client
            Notification::send(
                $booking->client,
                new JobCompletionConfirmed($booking, $user->name, 'worker')
            );

            broadcast(new JobCompletionStatusUpdated(
                $booking,
                'worker',
                $isFullyCompleted
            ))->toOthers();

        } catch (\InvalidArgumentException $e) {
            $msg = $e->getMessage();
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Job completion confirmed.',
                'booking' => $booking->fresh()->load('earning'),
                'completion_status' => $booking->getCompletionStatus(),
            ]);
        }

        return redirect()->back()->with('success', 'Job completion confirmed.');
    }

    public function cancelJob(Booking $booking): JsonResponse|RedirectResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }

        $oldStatus = $booking->status;

        try {
            $booking->cancel(request()->input('reason', 'Cancelled by worker'), auth()->id());
        } catch (\InvalidArgumentException $e) {
            $msg = $e->getMessage();
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        } catch (BookingStateException $e) {
            $msg = $e->getMessage();
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 409);
            }

            return redirect()->back()->with('error', $msg);
        }

        $booking->load('client');
        Notification::send($booking->client, new BookingCancelled($booking, $booking->worker->name));
        broadcast(new BookingStatusUpdated($booking, $oldStatus))->toOthers();

        BookingMessageService::post($booking, 'cancelled');

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Job cancelled.']);
        }

        return redirect()->back()->with('success', 'Job cancelled.');
    }

    public function declineJob(Booking $booking): JsonResponse|RedirectResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }

        $oldStatus = $booking->status;

        try {
            $booking->decline(request()->input('reason', 'Declined by worker'), auth()->id());
        } catch (\InvalidArgumentException $e) {
            $msg = $e->getMessage();
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        } catch (BookingStateException $e) {
            $msg = $e->getMessage();
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 409);
            }

            return redirect()->back()->with('error', $msg);
        }

        $booking->load('client');
        Notification::send($booking->client, new BookingStatusChanged($booking, $oldStatus));
        broadcast(new BookingStatusUpdated($booking, $oldStatus))->toOthers();

        BookingMessageService::post($booking, 'declined');

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Job declined.']);
        }

        return redirect()->back()->with('success', 'Job declined.');
    }

    public function updateLocation(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $profile = auth()->user()->workerProfile;

        if (! $profile) {
            abort(404, 'Worker profile not found. Please complete your profile first.');
        }

        try {
            $profile->update([
                'current_latitude' => $validated['latitude'],
                'current_longitude' => $validated['longitude'],
                'location_is_approximate' => false,
            ]);

            $barangay = TuyBarangays::barangayFor(
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );

            $user = auth()->user();
            $user->update([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'barangay' => $barangay,
                'location_source' => 'gps',
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update worker location', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Failed to update location.'], 500);
            }

            return redirect()->back()->with('error', 'Failed to update location.');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Location updated successfully.',
                'latitude' => $profile->current_latitude,
                'longitude' => $profile->current_longitude,
                'barangay' => $barangay,
            ]);
        }

        return redirect()->back()->with('success', 'Location updated successfully.');
    }

    public function uploadPhoto(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $existingCount = $booking->photos()->count();
        if ($existingCount >= 5) {
            $msg = 'Maximum of 5 photos per booking.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $path = $request->file('photo')->store('booking-photos', 'public');

        $photo = $booking->photos()->create([
            'photo_path' => $path,
            'caption' => $validated['caption'] ?? null,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'photo' => $photo]);
        }

        return redirect()->back()->with('success', 'Photo uploaded.');
    }

    public function rescheduleRequest(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }
        if (! $booking->isActive()) {
            $msg = 'Can only reschedule active bookings.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $validated = $request->validate([
            'proposed_at' => ['required', 'date', 'after:now'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'reschedule_requested_by' => auth()->id(),
            'reschedule_proposed_at' => $validated['proposed_at'],
            'reschedule_reason' => $validated['reason'] ?? null,
            'reschedule_status' => 'pending',
        ]);

        $booking->load('rescheduleRequestedBy');
        Notification::send($booking->client, new RescheduleRequested($booking));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Reschedule request sent to client.']);
        }

        return redirect()->back()->with('success', 'Reschedule request sent.');
    }

    public function respondReschedule(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }
        if ($booking->reschedule_status !== 'pending') {
            $msg = 'No pending reschedule request.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return redirect()->back()->with('error', $msg);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:approve,decline'],
        ]);

        if ($validated['action'] === 'approve') {
            $booking->update([
                'scheduled_at' => $booking->reschedule_proposed_at,
                'reschedule_status' => 'approved',
                'reschedule_responded_at' => now(),
            ]);
        } else {
            $booking->update([
                'reschedule_status' => 'declined',
                'reschedule_responded_at' => now(),
            ]);
        }

        $booking->load('client');
        Notification::send($booking->client, new BookingStatusChanged($booking, $booking->status));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Reschedule '.$validated['action'].'d.']);
        }

        return redirect()->back()->with('success', 'Reschedule '.$validated['action'].'d.');
    }

    public function startTimer(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }

        $booking->update(['work_started_at' => now()]);

        if ($booking->status === Booking::STATUS_EN_ROUTE) {
            try {
                $booking->transitionTo(Booking::STATUS_IN_PROGRESS, auth()->id());
            } catch (\Throwable $e) {
                // Keep updated timestamp even if transition had race condition
            }
        } elseif ($booking->status === Booking::STATUS_ACCEPTED) {
            try {
                $booking->transitionTo(Booking::STATUS_EN_ROUTE, auth()->id());
                $booking->transitionTo(Booking::STATUS_IN_PROGRESS, auth()->id());
            } catch (\Throwable $e) {
                // Keep updated timestamp even if transition had race condition
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Work timer started.',
            'work_started_at' => $booking->work_started_at->format('g:i A'),
        ]);
    }

    public function endTimer(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }

        $booking->update(['work_ended_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Work timer completed.',
            'work_ended_at' => $booking->work_ended_at->format('g:i A'),
        ]);
    }

    public function requestScopeAmendment(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amendment_price' => ['required', 'numeric', 'min:0'],
            'notes'           => ['required', 'string', 'max:2000'],
        ]);

        $booking->update([
            'scope_amendment_price'        => $validated['amendment_price'],
            'scope_amendment_notes'        => $validated['notes'],
            'scope_amendment_status'       => 'pending',
            'scope_amendment_requested_at' => now(),
        ]);

        $booking->history()->create([
            'user_id'    => auth()->id(),
            'old_status' => $booking->status,
            'new_status' => $booking->status,
            'notes'      => 'Worker requested on-site scope amendment: ₱' . number_format($validated['amendment_price'], 2) . ' — ' . $validated['notes'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Scope amendment request sent to client for approval.',
        ]);
    }

    public function suggestTeam(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'peer_ids'      => ['required', 'array', 'min:1'],
            'peer_ids.*'    => ['required', 'exists:users,id'],
            'roles'         => ['nullable', 'array'],
            'payouts'       => ['nullable', 'array'],
            'justification' => ['required', 'string', 'max:2000'],
        ]);

        $booking->update([
            'team_status'        => 'suggested',
            'team_suggested_at'  => now(),
            'team_justification' => $validated['justification'],
        ]);

        foreach ($validated['peer_ids'] as $idx => $peerId) {
            if ($peerId == auth()->id()) continue;

            $role = $validated['roles'][$idx] ?? 'assistant';
            $payout = (float) ($validated['payouts'][$idx] ?? 0);

            \App\Models\BookingWorker::updateOrCreate(
                [
                    'booking_id' => $booking->id,
                    'worker_id'  => $peerId,
                ],
                [
                    'role'               => $role,
                    'payout_amount'      => $payout,
                    'status'             => 'pending_client_approval',
                    'justification_note' => $validated['justification'],
                ]
            );
        }

        $booking->history()->create([
            'user_id'    => auth()->id(),
            'old_status' => $booking->status,
            'new_status' => $booking->status,
            'notes'      => 'Worker suggested peer work / recommended team: ' . $validated['justification'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Team recommendation sent to client for review and approval.',
        ]);
    }

    public function respondPeerInvitation(Request $request, Booking $booking): JsonResponse
    {
        $peerWorker = \App\Models\BookingWorker::where('booking_id', $booking->id)
            ->where('worker_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'action' => ['required', 'in:accept,decline'],
        ]);

        $newStatus = $validated['action'] === 'accept' ? 'accepted' : 'declined';

        $peerWorker->update([
            'status'        => $newStatus,
            'responded_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'You have ' . $newStatus . ' the crew invitation.',
            'status'  => $newStatus,
        ]);
    }
}
