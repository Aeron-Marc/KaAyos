<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking, public string $oldStatus) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $labelMap = [
            'accepted'    => 'Booking Accepted',
            'en_route'    => 'Worker is En Route',
            'in_progress' => 'Job In Progress',
            'completed'   => 'Job Completed',
        ];

        return [
            'title'      => $labelMap[$this->booking->status] ?? 'Status Updated',
            'message'    => $this->booking->worker->name . ' updated your booking for ' . $this->booking->service_category . ' to ' . ucfirst(str_replace('_', ' ', $this->booking->status)) . '.',
            'booking_id' => $this->booking->id,
            'type'       => 'booking',
            'status'     => $this->booking->status,
        ];
    }
}
