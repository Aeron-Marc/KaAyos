<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VerificationRejected extends Notification
{
    use Queueable;

    public function __construct(public User $user, public string $reason) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Verification Rejected',
            'message' => "Your verification documents have been rejected. Reason: {$this->reason}",
            'user_id' => $this->user->id,
        ];
    }
}
