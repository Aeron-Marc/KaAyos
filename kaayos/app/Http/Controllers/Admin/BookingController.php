<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Events\BookingStatusUpdated;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['client', 'worker']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                })->orWhereHas('worker', function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        $bookings = $query->latest()->paginate(20)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['client', 'worker']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking)
    {
        $oldStatus = $booking->status;

        try {
            $booking->cancel($request->input('reason', 'Cancelled by admin.'), auth()->id());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $booking->load(['client', 'worker']);

        Notification::send($booking->client, new BookingCancelled($booking));
        Notification::send($booking->worker, new BookingCancelled($booking));

        broadcast(new BookingStatusUpdated($booking, $oldStatus))->toOthers();

        return redirect()->back()->with('success', 'Booking #' . ($booking->booking_ref ?? $booking->id) . ' has been cancelled.');
    }
}
