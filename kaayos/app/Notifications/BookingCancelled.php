<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => 'Booking Cancelled',
            'message'    => $this->booking->client->name . ' cancelled the booking for ' . $this->booking->service_category . ' scheduled on ' . $this->booking->scheduled_at->format('M d, Y · h:i A') . '.',
            'booking_id' => $this->booking->id,
            'type'       => 'booking',
            'status'     => 'cancelled',
        ];
    }
}
