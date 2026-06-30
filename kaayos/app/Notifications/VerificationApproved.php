<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VerificationApproved extends Notification
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Verification Approved',
            'message' => 'Your verification documents have been approved. You are now a verified service provider.',
            'user_id' => $this->user->id,
        ];
    }
}
