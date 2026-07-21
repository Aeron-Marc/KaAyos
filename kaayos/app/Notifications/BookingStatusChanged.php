<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Booking $booking, public string $oldStatus) {}

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
            ->subject('Booking ' . ucfirst(str_replace('_', ' ', $this->booking->status)))
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your booking for ' . $this->booking->service_category . ' has been updated to: ' . ucfirst(str_replace('_', ' ', $this->booking->status)) . '.')
            ->action('View Booking', url('/client/bookings'))
            ->line('Scheduled: ' . $this->booking->scheduled_at->format('M d, Y · h:i A'));
    }

    public function toDatabase(object $notifiable): array
    {
        $labelMap = [
            'accepted'    => 'Booking Accepted',
            'en_route'    => 'Worker is En Route',
            'in_progress' => 'Job In Progress',
            'completed'   => 'Job Completed',
            'cancelled'   => 'Booking Cancelled',
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
