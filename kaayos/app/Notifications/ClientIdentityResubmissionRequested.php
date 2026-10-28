<?php

namespace App\Notifications;

use App\Models\ClientIdentityVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClientIdentityResubmissionRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ClientIdentityVerification $verification,
        public string $explanation,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'   => 'Resubmit Identity Documents',
            'message' => 'Please resubmit your identity documents. '.$this->explanation,
            'user_id' => $this->verification->user_id,
        ];
    }
}
