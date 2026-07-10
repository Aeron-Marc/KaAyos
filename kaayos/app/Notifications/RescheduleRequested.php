<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RescheduleRequested extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $requester = $this->booking->rescheduleRequestedBy;

        return [
            'title'      => 'Reschedule Request',
            'message'    => $requester->name . ' requested to reschedule the booking for ' . $this->booking->service_category . ' to ' . $this->booking->reschedule_proposed_at->format('M d, Y · h:i A') . '.',
            'booking_id' => $this->booking->id,
            'type'       => 'booking',
        ];
    }
}
