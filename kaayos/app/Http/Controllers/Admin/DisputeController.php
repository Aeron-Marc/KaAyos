<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateDisputeRequest;
use App\Models\Booking;
use App\Models\Dispute;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispute::with(['booking', 'raisedBy']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('raisedBy', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $disputes = $query->latest()->paginate(20)->withQueryString();

        return view('admin.disputes.index', compact('disputes'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load(['booking.client', 'booking.worker', 'raisedBy', 'resolvedBy']);
        return view('admin.disputes.show', compact('dispute'));
    }

    public function update(UpdateDisputeRequest $request, Dispute $dispute)
    {
        $data = [
            'status'           => $request->input('status'),
            'resolution_notes' => $request->input('resolution_notes'),
        ];

        if ($request->input('status') === 'resolved') {
            $data['resolved_at'] = now();
            $data['resolved_by'] = $request->user()->id;

            $dispute->booking->client->notify(
                new \App\Notifications\DisputeResolved($dispute, $dispute->booking->client)
            );
            $dispute->booking->worker->notify(
                new \App\Notifications\DisputeResolved($dispute, $dispute->booking->worker)
            );
        }

        $dispute->update($data);

        return redirect()->route('admin.disputes.index')
            ->with('success', "Dispute #{$dispute->id} has been updated to '{$dispute->status}'.");
    }
}
