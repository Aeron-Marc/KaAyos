<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Earning;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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
            'active_jobs'       => $activeJobs->count(),
            'completed_jobs'    => $user->bookingsAsWorker()->completed()->count(),
            'total_earnings'    => Earning::where('worker_id', $user->id)->sum('net_amount'),
            'average_rating'    => $profile?->average_rating ?? 0.00,
        ];

        return view('worker.dashboard.overview', compact('activeJobs', 'recentEarnings', 'stats'));
    }

    public function updateJobStatus(Request $request, Booking $booking): JsonResponse|RedirectResponse
    {
        $user = auth()->user();

        if ($booking->worker_id !== $user->id) {
            abort(403, 'This job is not assigned to you.');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', Booking::STATUSES)],
        ]);

        $booking->transitionTo($validated['status']);

        if ($validated['status'] === Booking::STATUS_COMPLETED) {
            $platformFeePercent = config('kaayos.platform_fee_percent', 10);
            $gross = $booking->price ?? 0;
            $fee = round($gross * ($platformFeePercent / 100), 2);
            $net = $gross - $fee;

            Earning::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'worker_id'    => $user->id,
                    'gross_amount' => $gross,
                    'platform_fee' => $fee,
                    'net_amount'   => $net,
                ]
            );
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Job status updated successfully.',
                'booking' => $booking->fresh()->load('earning'),
            ]);
        }

        return redirect()->back()->with('success', 'Job status updated successfully.');
    }

    public function updateLocation(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $profile = auth()->user()->workerProfile;

        if (!$profile) {
            abort(404, 'Worker profile not found. Please complete your profile first.');
        }

        $profile->update([
            'current_latitude'  => $validated['latitude'],
            'current_longitude' => $validated['longitude'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Location updated successfully.',
                'latitude'  => $profile->current_latitude,
                'longitude' => $profile->current_longitude,
            ]);
        }

        return redirect()->back()->with('success', 'Location updated successfully.');
    }
}
