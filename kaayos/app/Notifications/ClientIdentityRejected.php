<?php

namespace App\Notifications;

use App\Models\ClientIdentityVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClientIdentityRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ClientIdentityVerification $verification,
        public string $reason,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Identity Rejected',
            'message' => 'Your identity documents have been rejected. Reason: '.$this->reason,
            'user_id' => $this->verification->user_id,
        ];
    }
}
