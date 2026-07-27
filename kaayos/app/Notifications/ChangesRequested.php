<?php

namespace App\Notifications;

use App\Models\WorkerVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ChangesRequested extends Notification
{
    use Queueable;

    public function __construct(public WorkerVerification $verification) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Verification Changes Requested',
            'message' => 'Some of your documents need changes. Please check the rejection reasons and re-upload the required documents.',
            'user_id' => $this->verification->user_id,
        ];
    }
}
