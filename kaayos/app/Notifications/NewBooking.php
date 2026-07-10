<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBooking extends Notification
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
            'title'      => 'New Booking Request',
            'message'    => $this->booking->client->name . ' booked you for ' . $this->booking->service_category . ' on ' . $this->booking->scheduled_at->format('M d, Y · h:i A'),
            'booking_id' => $this->booking->id,
            'type'       => 'booking',
        ];
    }
}
