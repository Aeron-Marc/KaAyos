<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class BookingStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public Booking $booking, public string $oldStatus) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->booking->client_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id'         => $this->booking->id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->booking->status,
            'service'    => $this->booking->service_category,
            'price'      => $this->booking->price ?? 0,
        ];
    }
}
