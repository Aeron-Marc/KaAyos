<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingCancelled;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';
    protected $description = 'Cancel booking requests that have expired (no worker action taken) or handle no-shows';

    public function handle(): void
    {
        $expiryHours = config('kaayos.booking_expiry_hours', 24);
        $noShowMinutes = config('kaayos.no_show_minutes', 60);

        $expiryCutoff = now()->subHours($expiryHours);
        $noShowCutoff = now()->subMinutes($noShowMinutes);

        $count = 0;

        // Expired new bookings (worker never responded)
        $expired = Booking::where('status', Booking::STATUS_NEW)
            ->where('created_at', '<', $expiryCutoff)
            ->get();

        foreach ($expired as $booking) {
            $booking->cancel('Booking request expired (no response within ' . $expiryHours . ' hours).');
            $booking->load('client');
            Notification::send($booking->client, new BookingCancelled($booking));
            $count++;
        }

        // No-show accepted bookings (worker accepted but never started past scheduled time)
        $noShows = Booking::where('status', Booking::STATUS_ACCEPTED)
            ->where('scheduled_at', '<', $noShowCutoff)
            ->get();

        foreach ($noShows as $booking) {
            $booking->cancel('Worker did not start the job within ' . $noShowMinutes . ' minutes of the scheduled time.');
            $booking->load(['client', 'worker']);
            Notification::send($booking->client, new BookingCancelled($booking, $booking->worker->name));
            Notification::send($booking->worker, new BookingCancelled($booking));
            $count++;
        }

        $this->info("Cancelled {$count} expired/no-show booking(s).");
    }
}
