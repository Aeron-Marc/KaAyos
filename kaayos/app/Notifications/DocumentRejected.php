<?php

namespace App\Notifications;

use App\Models\WorkerDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentRejected extends Notification
{
    use Queueable;

    public function __construct(public WorkerDocument $document, public string $reason) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Document Rejected',
            'message' => 'Your "' . str_replace('_', ' ', ucfirst($this->document->document_type)) . '" was rejected. Reason: ' . $this->reason,
            'user_id' => $this->document->user_id,
        ];
    }
}
