<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking, public ?string $cancelledBy = null) {}

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
        $who = $this->cancelledBy ?? $this->booking->client->name;
        return (new MailMessage)
            ->subject('Booking Cancelled')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($who . ' cancelled the booking for ' . $this->booking->service_category . '.')
            ->line('Scheduled: ' . $this->booking->scheduled_at->format('M d, Y · h:i A'))
            ->action('View Details', url('/client/bookings'))
            ->line('If you have any questions, please contact support.');
    }

    public function toDatabase(object $notifiable): array
    {
        $who = $this->cancelledBy ?? $this->booking->client->name;
        return [
            'title'      => 'Booking Cancelled',
            'message'    => $who . ' cancelled the booking for ' . $this->booking->service_category . ' scheduled on ' . $this->booking->scheduled_at->format('M d, Y · h:i A') . '.',
            'booking_id' => $this->booking->id,
            'type'       => 'booking',
            'status'     => 'cancelled',
        ];
    }
}
