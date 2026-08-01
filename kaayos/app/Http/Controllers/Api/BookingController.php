<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\BookingStateException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Dispute;
use App\Models\Earning;
use App\Models\Review;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Events\BookingCreated;
use App\Events\BookingStatusUpdated;
use App\Events\JobCompletionStatusUpdated;
use App\Services\BookingMessageService;
use App\Notifications\BookingCancelled;
use App\Notifications\NewBooking;
use App\Notifications\NewReview;
use App\Notifications\ReportReceived;
use App\Notifications\RescheduleRequested;
use App\Notifications\JobCompletionRequested;
use App\Notifications\JobCompletionConfirmed;
use App\Notifications\BookingStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->isWorker()
            ? $user->bookingsAsWorker()
            : $user->bookingsAsClient();

        $query->with('client', 'worker', 'history', 'review');

        if ($status = $request->input('status')) {
            if (in_array($status, Booking::STATUSES)) {
                $query->where('status', $status);
            }
        }

        if ($scope = $request->input('scope')) {
            if ($scope === 'upcoming') {
                $query->whereIn('status', [
                    Booking::STATUS_NEW,
                    Booking::STATUS_ACCEPTED,
                    Booking::STATUS_EN_ROUTE,
                    Booking::STATUS_IN_PROGRESS,
                ]);
            } elseif ($scope === 'past') {
                $query->whereIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED]);
            }
        }

        $bookings = $query->latest()
            ->take(100)
            ->get()
            ->map(fn ($b) => $this->bookingPayload($b, $user))
            ->values();

        return response()->json(['success' => true, 'bookings' => $bookings]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worker_id'        => ['required', 'exists:users,id'],
            'service_category' => ['required', 'string', 'max:255'],
            'scheduled_at'     => ['required', 'date', 'after:now'],
            'house_no'         => ['required', 'string', 'max:255'],
            'barangay'         => ['required', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:2000'],
            'price'            => ['nullable', 'numeric', 'min:0'],
        ]);

        $worker = User::findOrFail($validated['worker_id']);
        if ($worker->role !== 'worker') {
            return response()->json(['success' => false, 'message' => 'Invalid worker.'], 422);
        }

        if ($worker->suspended_at) {
            return response()->json(['success' => false, 'message' => 'This worker is currently unavailable.'], 422);
        }

        $overlap = Booking::where('worker_id', $worker->id)
            ->whereNotIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED])
            ->where('scheduled_at', $validated['scheduled_at'])
            ->exists();

        if ($overlap) {
            return response()->json(['success' => false, 'message' => 'This worker already has a booking at the selected time.'], 422);
        }

        $address = $validated['house_no'] . ', ' . $validated['barangay'] . ', ' . config('kaayos.default_location');

        $booking = DB::transaction(function () use ($validated, $address, $request) {
            $booking = Booking::create([
                'client_id'           => $request->user()->id,
                'worker_id'           => $validated['worker_id'],
                'service_category'    => $validated['service_category'],
                'scheduled_at'        => $validated['scheduled_at'],
                'address'             => $address,
                'house_no'            => $validated['house_no'],
                'barangay'            => $validated['barangay'],
                'notes'               => $validated['notes'] ?? null,
                'price'               => $validated['price'] ?? 0,
                'status'              => Booking::STATUS_NEW,
                'agreed_by_client_at' => now(),
            ]);

            $booking->history()->create([
                'old_status' => null,
                'new_status' => Booking::STATUS_NEW,
                'user_id'    => $request->user()->id,
            ]);

            return $booking;
        });

        $booking->load('client', 'worker', 'history');

        Notification::send($booking->worker, new NewBooking($booking));
        broadcast(new BookingCreated($booking))->toOthers();

        BookingMessageService::post($booking, 'new');

        return response()->json([
            'success' => true,
            'booking' => $this->bookingPayload($booking, $request->user()),
        ], 201);
    }

    public function status(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'This job is not assigned to you.'], 403);
        }

        $allowed = array_filter([
            Booking::STATUS_FLOW[$booking->status] ?? null,
            Booking::STATUS_CANCELLED,
        ]);

        if ($booking->status === Booking::STATUS_IN_PROGRESS) {
            $allowed[] = Booking::STATUS_COMPLETED;
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', $allowed)],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['status'] === Booking::STATUS_ACCEPTED) {
            $activeCount = $user->bookingsAsWorker()
                ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
                ->count();

            if ($activeCount >= config('kaayos.max_concurrent_jobs', 3)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have reached the maximum of ' . config('kaayos.max_concurrent_jobs', 3) . ' concurrent jobs. Complete an existing job first.',
                ], 422);
            }
        }

        $oldStatus = $booking->status;

        try {
            if ($validated['status'] === Booking::STATUS_CANCELLED) {
                $booking->cancel($validated['reason'] ?? 'Cancelled by worker', $user->id);
                BookingMessageService::post($booking, 'cancelled');
                Notification::send($booking->client, new BookingCancelled($booking, $booking->client->name));
                broadcast(new BookingStatusUpdated($booking, $oldStatus))->toOthers();
            } elseif ($validated['status'] === Booking::STATUS_COMPLETED) {
                $afterSave = function (Booking $fresh) use ($user) {
                    if ($fresh->status === Booking::STATUS_COMPLETED) {
                        $platformFeePercent = config('kaayos.platform_fee_percent', 10);
                        $gross = $fresh->price ?? 0;
                        $fee   = round($gross * ($platformFeePercent / 100), 2);

                        Earning::updateOrCreate(
                            ['booking_id' => $fresh->id],
                            [
                                'worker_id'    => $user->id,
                                'gross_amount' => $gross,
                                'platform_fee' => $fee,
                                'net_amount'   => $gross - $fee,
                            ]
                        );
                    }
                };

                $booking->markComplete($user, $afterSave);
                $booking->fresh();

                if ($booking->completion_requested_by === $user->id && $booking->confirmed_by_client_at === null) {
                    Notification::send($booking->client, new JobCompletionRequested($booking, $user->name, 'worker'));
                } elseif ($booking->confirmed_by_client_at !== null && $booking->status === Booking::STATUS_COMPLETED) {
                    Notification::send($booking->client, new JobCompletionConfirmed($booking, $user->name, 'worker'));
                }
            } elseif ($validated['status'] === Booking::STATUS_ACCEPTED) {
                $booking->update(['agreed_by_worker_at' => now()]);
                $booking->transitionTo($validated['status'], $user->id);
            } else {
                $booking->transitionTo($validated['status'], $user->id);
            }
        } catch (BookingStateException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $booking->load('client', 'worker', 'history', 'review');

        if ($booking->isCompletionPending()) {
            broadcast(new JobCompletionStatusUpdated($booking, 'worker', false))->toOthers();
        } elseif ($booking->status === Booking::STATUS_COMPLETED) {
            broadcast(new JobCompletionStatusUpdated($booking, 'worker', true))->toOthers();
        } elseif ($oldStatus !== $booking->status) {
            broadcast(new BookingStatusUpdated($booking, $oldStatus))->toOthers();
        }

        BookingMessageService::post($booking, $booking->status);

        return response()->json([
            'success' => true,
            'booking' => $this->bookingPayload($booking, $user),
        ]);
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->client_id !== $user->id && $booking->worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'This booking does not belong to you.'], 403);
        }

        $oldStatus = $booking->status;

        try {
            $booking->cancel($request->input('reason', 'Cancelled by ' . ($user->isWorker() ? 'worker' : 'client')), $user->id);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (BookingStateException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }

        $booking->load('client', 'worker');

        $otherParty = $user->isWorker() ? $booking->client : $booking->worker;
        if ($otherParty) {
            Notification::send($otherParty, new BookingCancelled($booking, $user->name));
        }
        broadcast(new BookingStatusUpdated($booking, $oldStatus))->toOthers();

        BookingMessageService::post($booking, 'cancelled');

        return response()->json([
            'success' => true,
            'booking' => $this->bookingPayload($booking, $user),
        ]);
    }

    public function confirmComplete(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->client_id !== $user->id && $booking->worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'This booking does not belong to you.'], 403);
        }

        if (!$booking->isCompletionPending()) {
            return response()->json(['success' => false, 'message' => 'This job is not awaiting completion confirmation.'], 422);
        }

        try {
            $afterSave = function (Booking $fresh) use ($user) {
                if ($fresh->status === Booking::STATUS_COMPLETED) {
                    $platformFeePercent = config('kaayos.platform_fee_percent', 10);
                    $gross = $fresh->price ?? 0;
                    $fee   = round($gross * ($platformFeePercent / 100), 2);

                    Earning::updateOrCreate(
                        ['booking_id' => $fresh->id],
                        [
                            'worker_id'    => $user->id,
                            'gross_amount' => $gross,
                            'platform_fee' => $fee,
                            'net_amount'   => $gross - $fee,
                        ]
                    );
                }
            };

            $booking->markComplete($user, $afterSave);
            $booking->fresh();
            $isFullyCompleted = $booking->status === Booking::STATUS_COMPLETED;

            $booking->load('client', 'worker');

            $otherParty = $user->isWorker() ? $booking->client : $booking->worker;
            if ($otherParty) {
                Notification::send($otherParty, new JobCompletionConfirmed($booking, $user->name, $user->isWorker() ? 'worker' : 'client'));
            }

            broadcast(new JobCompletionStatusUpdated($booking, $user->isWorker() ? 'worker' : 'client', $isFullyCompleted))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Job completion confirmed.' . ($isFullyCompleted ? ' Job is now complete!' : ''),
                'booking' => $this->bookingPayload($booking, $user),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function review(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->client_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Only the client can review this booking.'], 403);
        }

        if ($booking->status !== Booking::STATUS_COMPLETED) {
            return response()->json(['success' => false, 'message' => 'Can only review completed bookings.'], 422);
        }

        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = Review::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'client_id' => $user->id,
                'worker_id' => $booking->worker_id,
                'rating'    => $validated['rating'],
                'comment'   => $validated['comment'] ?? null,
            ]
        );

        $averageRating = (float) Review::where('worker_id', $booking->worker_id)->avg('rating');
        WorkerProfile::updateOrCreate(
            ['user_id' => $booking->worker_id],
            ['average_rating' => round($averageRating, 2)]
        );

        Notification::send($booking->worker, new NewReview($review));

        return response()->json([
            'success' => true,
            'message' => 'Review submitted. Thank you!',
        ]);
    }

    public function report(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->client_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Only the client can report this booking.'], 403);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $exists = Dispute::where('type', 'worker_report')
            ->where('booking_id', $booking->id)
            ->where('raised_by', $user->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a report for this booking.',
            ], 422);
        }

        $dispute = Dispute::create([
            'type'               => 'worker_report',
            'booking_id'         => $booking->id,
            'raised_by'          => $user->id,
            'reported_worker_id' => $booking->worker_id,
            'status'             => 'open',
            'reason'             => $validated['reason'],
        ]);

        $user->notify(new ReportReceived($dispute));

        return response()->json([
            'success' => true,
            'message' => 'Your report has been submitted. An admin will review it shortly.',
        ]);
    }

    public function reschedule(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->client_id !== $user->id && $booking->worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'This booking does not belong to you.'], 403);
        }

        if (!$booking->isActive()) {
            return response()->json(['success' => false, 'message' => 'Can only reschedule active bookings.'], 422);
        }

        $validated = $request->validate([
            'proposed_at' => ['required', 'date', 'after:now'],
            'reason'      => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'reschedule_requested_by' => $user->id,
            'reschedule_proposed_at'  => $validated['proposed_at'],
            'reschedule_reason'       => $validated['reason'] ?? null,
            'reschedule_status'       => 'pending',
        ]);

        $otherParty = $user->isWorker() ? $booking->client : $booking->worker;
        if ($otherParty) {
            Notification::send($otherParty, new RescheduleRequested($booking));
        }

        return response()->json([
            'success' => true,
            'message' => 'Reschedule request sent.',
        ]);
    }

    public function respondReschedule(Request $request, Booking $booking): JsonResponse
    {
        $user = $request->user();

        if ($booking->client_id !== $user->id && $booking->worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'This booking does not belong to you.'], 403);
        }

        if ($booking->reschedule_requested_by === $user->id) {
            return response()->json(['success' => false, 'message' => 'You cannot respond to your own request.'], 422);
        }

        if ($booking->reschedule_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'No pending reschedule request.'], 422);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:approve,decline'],
        ]);

        if ($validated['action'] === 'approve') {
            $booking->update([
                'scheduled_at'             => $booking->reschedule_proposed_at,
                'reschedule_status'        => 'approved',
                'reschedule_responded_at'  => now(),
            ]);
        } else {
            $booking->update([
                'reschedule_status'        => 'declined',
                'reschedule_responded_at'  => now(),
            ]);
        }

        $otherParty = $user->isWorker() ? $booking->client : $booking->worker;
        if ($otherParty) {
            Notification::send($otherParty, new BookingStatusChanged($booking, $booking->status));
        }

        return response()->json([
            'success' => true,
            'message' => 'Reschedule request ' . $validated['action'] . 'd.',
        ]);
    }

    protected function bookingPayload(Booking $b, User $viewer): array
    {
        $statusHistory = [];
        foreach ($b->history as $h) {
            $statusHistory[$h->new_status] = $h->created_at?->toIso8601String();
        }
        if (!isset($statusHistory['new'])) {
            $statusHistory['new'] = $b->created_at?->toIso8601String();
        }

        return [
            'id'                        => $b->id,
            'booking_ref'               => $b->booking_ref ?? 'BK-' . str_pad((string) $b->id, 5, '0', STR_PAD_LEFT),
            'client_id'                 => $b->client_id,
            'client_name'               => $b->client?->name ?? 'Unknown',
            'client_phone'              => $b->client?->phone ?? null,
            'client_email'              => $b->client?->email ?? null,
            'worker_id'                 => $b->worker_id,
            'worker_name'               => $b->worker?->name ?? 'Unknown',
            'worker_avatar_url'         => $b->worker?->avatar ? \Storage::url($b->worker?->avatar) : null,
            'service_category'          => $b->service_category,
            'scheduled_at'              => $b->scheduled_at?->format('M d, Y · h:i A'),
            'scheduled_at_iso'          => $b->scheduled_at?->toIso8601String(),
            'address'                   => $b->address,
            'house_no'                  => $b->house_no,
            'barangay'                  => $b->barangay,
            'notes'                     => $b->notes,
            'status'                    => $b->status,
            'price'                     => (float) ($b->price ?? 0),
            'created_at'                => $b->created_at?->toIso8601String(),
            'completed_at'              => $b->completed_at?->toIso8601String(),
            'cancelled_at'              => $b->cancelled_at?->toIso8601String(),
            'cancellation_reason'       => $b->cancellation_reason,
            'status_history'            => $statusHistory,
            'completion_status'         => $b->getCompletionStatus(),
            'reschedule_requested_by'   => $b->reschedule_requested_by,
            'reschedule_proposed_at'    => $b->reschedule_proposed_at?->toIso8601String(),
            'reschedule_reason'         => $b->reschedule_reason,
            'reschedule_status'         => $b->reschedule_status,
            'review'                    => $b->review ? [
                'id'      => $b->review->id,
                'rating'  => $b->review->rating,
                'comment' => $b->review->comment,
            ] : null,
            'viewer_role'               => $viewer->isWorker() ? 'worker' : 'client',
            'can_respond_reschedule'    => $b->reschedule_status === 'pending' && $b->reschedule_requested_by !== $viewer->id,
        ];
    }
}
