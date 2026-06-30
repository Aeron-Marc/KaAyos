<?php

namespace App\Notifications;

use App\Models\Dispute;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DisputeResolved extends Notification
{
    use Queueable;

    public function __construct(public Dispute $dispute, public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => 'Dispute Resolved',
            'message'    => "Dispute #{$this->dispute->id} for Booking #{$this->dispute->booking_id} has been resolved.",
            'dispute_id' => $this->dispute->id,
            'booking_id' => $this->dispute->booking_id,
        ];
    }
}
