<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobCompletionConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Booking $booking,
        public string $confirmerName,
        public string $confirmerRole,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $title = 'Job Completion Confirmed';

        $message = "{$this->confirmerName} ({$this->confirmerRole}) has confirmed job completion.";

        // Check if job is now fully completed (both parties confirmed)
        $isFullyCompleted = $this->booking->confirmed_by_worker_at !== null 
            && $this->booking->confirmed_by_client_at !== null;

        if ($isFullyCompleted) {
            $message .= " The job is now officially completed!";
        }

        return (new MailMessage)
            ->subject($title)
            ->line($message)
            ->line("Booking Reference: {$this->booking->booking_ref}")
            ->action('View Job', url("/bookings/{$this->booking->id}"))
            ->line('Thank you for using KaAyos!');
    }

    public function toArray(object $notifiable): array
    {
        $isFullyCompleted = $this->booking->confirmed_by_worker_at !== null 
            && $this->booking->confirmed_by_client_at !== null;

        return [
            'type' => 'job_completion_confirmed',
            'booking_id' => $this->booking->id,
            'booking_ref' => $this->booking->booking_ref,
            'confirmer_name' => $this->confirmerName,
            'confirmer_role' => $this->confirmerRole,
            'is_fully_completed' => $isFullyCompleted,
            'message' => $this->confirmerName . " confirmed job completion." 
                . ($isFullyCompleted ? " Job is now complete!" : ""),
        ];
    }
}
