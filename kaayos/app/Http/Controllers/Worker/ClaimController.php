<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;

class ClaimController extends Controller
{
    public function index(Request $request)
    {
        $workerId = $request->user()->id;

        $claims = Dispute::query()
            ->where(function ($q) use ($workerId) {
                $q->where('reported_worker_id', $workerId)
                  ->orWhereHas('booking', function ($b) use ($workerId) {
                      $b->where('worker_id', $workerId);
                  });
            })
            ->with(['booking.client', 'booking.worker', 'raisedBy', 'reportedWorker'])
            ->latest()
            ->paginate(10);

        return view('worker.claims.index', compact('claims'));
    }

    public function storeCounterClaim(Request $request, Dispute $dispute)
    {
        $workerId = $request->user()->id;

        $isReportedWorker = $dispute->reported_worker_id === $workerId;
        $isBookingWorker = $dispute->booking && $dispute->booking->worker_id === $workerId;

        if (!$isReportedWorker && !$isBookingWorker) {
            abort(403);
        }

        if ($dispute->status === 'resolved') {
            return back()->with('error', 'This dispute has been resolved, so a counter-claim can no longer be submitted.');
        }

        $validated = $request->validate([
            'counter_claim' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $dispute->update([
            'counter_claim'             => $validated['counter_claim'],
            'counter_claim_submitted_at' => now(),
        ]);

        return back()->with('success', 'Your counter-claim has been submitted for admin review.');
    }
}
