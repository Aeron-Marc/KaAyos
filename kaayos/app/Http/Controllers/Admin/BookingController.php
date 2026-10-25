<?php

namespace App\Http\Controllers\Admin;

use App\Events\BookingStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingCancelled;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with(['client', 'worker']);

        if ($status = $request->input('status')) {
            match ($status) {
                'active' => $query->workInProgress(),
                'stuck' => $query->stuck((int) config('dashboard.targets.stuck_booking_hours', 24)),
                'unassigned' => $query->unassigned((int) config('dashboard.targets.unassigned_booking_hours', 12)),
                'other' => $query->whereNotIn('status', Booking::STATUSES),
                default => $query->where('status', $status),
            };
        }

        $outcomeBasis = $request->input('date_basis') === 'outcome';

        if ($dateFrom = $request->input('date_from')) {
            $from = Carbon::parse($dateFrom)->startOfDay();

            if ($outcomeBasis) {
                $query->whereRaw('COALESCE(completed_at, cancelled_at, declined_at) >= ?', [$from]);
            } else {
                $query->where('created_at', '>=', $from);
            }
        }

        if ($dateTo = $request->input('date_to')) {
            $to = Carbon::parse($dateTo)->addDay()->startOfDay();

            if ($outcomeBasis) {
                $query->whereRaw('COALESCE(completed_at, cancelled_at, declined_at) < ?', [$to]);
            } else {
                $query->where('created_at', '<', $to);
            }
        }

        if ($outcomeBasis && ! $request->filled('date_from') && ! $request->filled('date_to')) {
            $query->whereIn('status', Booking::TERMINAL_STATUSES);
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

        return redirect()->back()->with('success', 'Booking #'.($booking->booking_ref ?? $booking->id).' has been cancelled.');
    }
}
