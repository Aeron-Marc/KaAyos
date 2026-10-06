<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Printable job invoice — accessible to the client, the worker, and admins.
     */
    public function show(Booking $booking): View
    {
        $user = auth()->user();

        $isParticipant = $booking->client_id === $user->id || $booking->worker_id === $user->id;
        if (!$isParticipant && $user->role !== 'admin') {
            abort(403);
        }

        $booking->load(['client', 'worker', 'materials', 'issueCategory']);

        return view('bookings.invoice', [
            'booking' => $booking,
        ]);
    }
}
