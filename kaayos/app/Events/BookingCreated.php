<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class BookingCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Booking $booking) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->booking->worker_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->booking->id,
            'client_name'  => $this->booking->client->name,
            'service'      => $this->booking->service_category,
            'scheduled_at' => $this->booking->scheduled_at->format('M d, Y · h:i A'),
            'price'        => $this->booking->price ?? 0,
            'status'       => $this->booking->status,
        ];
    }
}
