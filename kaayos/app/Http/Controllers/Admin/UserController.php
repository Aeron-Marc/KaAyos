<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuspendUserRequest;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingStatusChanged;
use App\Events\BookingStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            if ($status === 'suspended') {
                $query->suspended();
            } elseif ($status === 'active') {
                $query->active();
            }
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $bookings = Booking::where(function ($q) use ($user) {
            $q->where('client_id', $user->id)
              ->orWhere('worker_id', $user->id);
        })->with(['client', 'worker'])->latest()->paginate(10);

        return view('admin.users.show', compact('user', 'bookings'));
    }

    public function suspend(SuspendUserRequest $request, User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Cannot suspend an admin account.');
        }

        $user->update([
            'suspended_at'    => now(),
            'suspended_reason' => $request->input('reason'),
        ]);

        $activeBookings = Booking::whereIn('status', [
            Booking::STATUS_NEW,
            Booking::STATUS_ACCEPTED,
            Booking::STATUS_EN_ROUTE,
            Booking::STATUS_IN_PROGRESS,
        ])->where(function ($q) use ($user) {
            $q->where('client_id', $user->id)
              ->orWhere('worker_id', $user->id);
        })->get();

        foreach ($activeBookings as $booking) {
            $booking->update([
                'status'              => Booking::STATUS_CANCELLED,
                'cancelled_at'        => now(),
                'cancellation_reason' => 'User account suspended.',
            ]);

            if ($booking->client_id === $user->id) {
                Notification::send($booking->worker, new BookingCancelled($booking));
            } else {
                Notification::send($booking->client, new BookingCancelled($booking));
            }
        }

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} has been suspended. " . $activeBookings->count() . " active booking(s) cancelled.");
    }

    public function reactivate(User $user)
    {
        $user->update([
            'suspended_at'    => null,
            'suspended_reason' => null,
        ]);

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} has been reactivated.");
    }
}
