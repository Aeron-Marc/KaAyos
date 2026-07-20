<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;

class BookingMessageService
{
    public static function post(Booking $booking, string $event): Message
    {
        $conversation = Conversation::findOrCreateForPair(
            $booking->client_id,
            $booking->worker_id
        );

        $systemUserId = User::getSystemUserId();

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'booking_id'      => $booking->id,
            'sender_id'       => $systemUserId,
            'receiver_id'     => null,
            'message'         => json_encode([
                'type'       => 'booking_status',
                'booking_id' => $booking->id,
                'ref'        => $booking->booking_ref,
                'service'    => $booking->service_category,
                'scheduled'  => $booking->scheduled_at->format('M d · h:i A'),
                'status'     => $event,
            ]),
        ]);

        $conversation->update(['last_message_at' => now()]);

        broadcast(new MessageSent($msg))->toOthers();

        return $msg;
    }
}
