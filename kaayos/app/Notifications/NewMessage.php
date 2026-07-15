<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMessage extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->email && config('mail.mailers.smtp.username')) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sender = $this->message->sender;
        return (new MailMessage)
            ->subject('New Message from ' . $sender->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($sender->name . ' sent you a message:')
            ->line('"' . $this->message->message . '"')
            ->action('View Conversation', url('/messages'))
            ->line('Reply to continue the conversation.');
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
