<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('booking.' . $this->message->booking_id),
        ];

        if ($this->message->conversation_id) {
            $channels[] = new PrivateChannel('conversation.' . $this->message->conversation_id);
        }

        return $channels;
    }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'booking_id'      => $this->message->booking_id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'text'            => $this->message->message,
            'time'            => $this->message->created_at->diffForHumans(),
            'is_system'       => $this->message->receiver_id === null,
            'from'            => $this->message->receiver_id === null ? 'system' : 'them',
        ];
    }
}
