<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobCompletionRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $initiatorName,
        public string $initiatorRole,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = match ($this->initiatorRole) {
            'worker' => 'Worker Marked Job Complete',
            'client' => 'Client Marked Job Complete',
            default => 'Job Completion Requested',
        };

        $message = match ($this->initiatorRole) {
            'worker' => "{$this->initiatorName} has marked the job as complete. Please review and confirm the completion.",
            'client' => "{$this->initiatorName} has marked the job as complete. Please review and confirm the completion.",
            default => "Please confirm the job completion.",
        };

        return (new MailMessage)
            ->subject($title)
            ->line($message)
            ->line("Booking Reference: {$this->booking->booking_ref}")
            ->action('Review Job', url("/bookings/{$this->booking->id}"))
            ->line('Thank you for using KaAyos!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'job_completion_requested',
            'booking_id' => $this->booking->id,
            'booking_ref' => $this->booking->booking_ref,
            'initiator_name' => $this->initiatorName,
            'initiator_role' => $this->initiatorRole,
            'message' => "{$this->initiatorName} ({$this->initiatorRole}) marked the job as complete. Please confirm.",
        ];
    }
}
