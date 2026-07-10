<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingCancelled;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';
    protected $description = 'Cancel booking requests that have expired (no worker action taken)';

    public function handle(): void
    {
        $cutoff = now()->subHours(config('kaayos.booking_expiry_hours', 24));

        $expired = Booking::where('status', Booking::STATUS_NEW)
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = 0;

        foreach ($expired as $booking) {
            $booking->update([
                'status'              => Booking::STATUS_CANCELLED,
                'cancelled_at'        => now(),
                'cancellation_reason' => 'Booking request expired (no response within ' . config('kaayos.booking_expiry_hours', 24) . ' hours).',
            ]);

            $booking->load('client');
            Notification::send($booking->client, new BookingCancelled($booking));

            $count++;
        }

        $this->info("Cancelled {$count} expired booking(s).");
    }
}
