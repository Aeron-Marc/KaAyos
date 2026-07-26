<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobCompletionStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Booking $booking,
        public string $confirmedBy, // 'worker' or 'client'
        public bool $isFullyCompleted,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("booking.{$this->booking->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'job.completion.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_ref' => $this->booking->booking_ref,
            'status' => $this->booking->status,
            'confirmed_by' => $this->confirmedBy,
            'is_fully_completed' => $this->isFullyCompleted,
            'worker_confirmed' => $this->booking->confirmed_by_worker_at !== null,
            'client_confirmed' => $this->booking->confirmed_by_client_at !== null,
            'completion_status' => $this->booking->getCompletionStatus(),
        ];
    }
}
