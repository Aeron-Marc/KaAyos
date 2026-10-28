<?php

namespace App\Notifications;

use App\Models\ClientIdentityVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClientIdentityApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ClientIdentityVerification $verification) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Identity Verified',
            'message' => 'Your identity documents have been approved. You can now create bookings.',
            'user_id' => $this->verification->user_id,
        ];
    }
}
