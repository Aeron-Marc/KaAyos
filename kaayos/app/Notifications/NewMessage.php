<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $sender = $this->message->sender;

        return [
            'title'      => 'New Message',
            'message'    => $sender->name . ': ' . $this->message->message,
            'booking_id' => $this->message->booking_id,
            'type'       => 'message',
            'sender_id'  => $sender->id,
        ];
    }
}
