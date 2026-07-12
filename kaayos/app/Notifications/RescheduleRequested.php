<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RescheduleRequested extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->email && config('mail.mailers.smtp.username')) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $requester = $this->booking->rescheduleRequestedBy;
        return (new MailMessage)
            ->subject('Reschedule Request')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($requester->name . ' requested to reschedule the booking for ' . $this->booking->service_category . '.')
            ->line('Proposed time: ' . $this->booking->reschedule_proposed_at->format('M d, Y · h:i A'))
            ->action('Respond to Request', url('/client/bookings'))
            ->line('Please review and respond to this request.');
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
