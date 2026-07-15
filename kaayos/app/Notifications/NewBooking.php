<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBooking extends Notification
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
        return (new MailMessage)
            ->subject('New Booking Request')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->booking->client->name . ' booked you for ' . $this->booking->service_category . '.')
            ->line('Scheduled: ' . $this->booking->scheduled_at->format('M d, Y · h:i A'))
            ->line('Address: ' . $this->booking->address)
            ->action('View Booking', url('/worker/jobs'))
            ->line('Please check your dashboard to accept or review this request.');
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
