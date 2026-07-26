<?php

namespace App\Notifications;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportReceived extends Notification
{
    use Queueable;

    public function __construct(public Dispute $dispute) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title'      => 'Report Received',
            'message'    => 'We have received your report and are currently reviewing your situation. Please bear with us while we settle your issue.',
            'booking_id' => $this->dispute->booking_id,
            'dispute_id' => $this->dispute->id,
            'type'       => 'report',
        ];
    }
}
