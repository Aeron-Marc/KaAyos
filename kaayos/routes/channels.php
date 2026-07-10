<?php

use App\Models\Booking;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('booking.{bookingId}', function ($user, $bookingId) {
    return Booking::where('id', $bookingId)
        ->where(function ($q) use ($user) {
            $q->where('client_id', $user->id)
              ->orWhere('worker_id', $user->id);
        })
        ->exists();
});
