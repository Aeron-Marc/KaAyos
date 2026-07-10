<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('booking.' . $this->message->booking_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'        => $this->message->id,
            'booking_id'=> $this->message->booking_id,
            'sender_id' => $this->message->sender_id,
            'text'      => $this->message->message,
            'time'      => $this->message->created_at->diffForHumans(),
            'from'      => 'them',
        ];
    }
}
