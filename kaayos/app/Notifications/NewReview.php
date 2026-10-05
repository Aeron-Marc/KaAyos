<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewReview extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Review $review) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $reviewer = $this->review->is_anonymous
            ? 'A client'
            : ($this->review->client->name ?? 'A client');

        return [
            'title'      => 'New Review',
            'message'    => $reviewer . ' gave you a ' . $this->review->rating . '-star review.',
            'booking_id' => $this->review->booking_id,
            'type'       => 'review',
        ];
    }
}
