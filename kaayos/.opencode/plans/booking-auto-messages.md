# Plan: Auto-generated Booking Messages in Chat

## Summary

When a booking is created or its status changes, insert a system message into the
conversation between client and worker. System messages render as centered info
banners (not chat bubbles) in the messages UI.

---

## Files to create

### 1. pp/Services/BookingMessageService.php

Single-purpose service that posts a system message into the booking's conversation.

`php
<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;

class BookingMessageService
{
    public static function post(Booking \, string \): Message
    {
        \ = Conversation::findOrCreateForPair(
            \->client_id,
            \->worker_id
        );

        \ = User::getSystemUserId();

        \ = match (\) {
            'new'         => "📋 Booking {\->booking_ref} created — {\->service_category}, {\->scheduled_at->format('M d · h:i A')}",
            'cancelled'   => "❌ Booking {\->booking_ref} cancelled",
            'accepted'    => "✅ Booking {\->booking_ref} accepted — worker is preparing for the job",
            'en_route'    => "🚗 Worker is on the way to your location",
            'in_progress' => "🔧 Work has started on booking {\->booking_ref}",
            'completed'   => "✅ Booking {\->booking_ref} completed! Thank you for using KaAyos.",
            default       => "Booking {\->booking_ref}: {\}",
        };

        \ = Message::create([
            'conversation_id' => \->id,
            'booking_id'      => \->id,
            'sender_id'       => \,
            'receiver_id'     => null,
            'message'         => \,
        ]);

        \->update(['last_message_at' => now()]);

        broadcast(new MessageSent(\))->toOthers();

        return \;
    }
}
`

---

## Files to modify

### 2. pp\Models\User.php

Add a static method to get/create the system user with in-request caching.

### 3. pp\Events\MessageSent.php

Add is_system flag to broadcast payload so the frontend can style it differently.

### 4. pp\Http\Controllers\Client\ClientController.php

**A) storeBooking()** — after the notification/broadcast block, call:
BookingMessageService::post(\, 'new')

**B) cancelBooking()** — after the broadcast, call:
BookingMessageService::post(\, 'cancelled')

**C) getConversations()** — update messages map to set rom = 'system' when
sender is the system user, and include is_system flag.

**D) pollMessages()** — include is_system in the response map.

### 5. pp\Http\Controllers\Worker\WorkerController.php

**A) getConversations()** — same changes as client's getConversations().
**B) pollMessages()** — same changes as client's pollMessages().

### 6. pp\Http\Controllers\Worker\WorkerDashboardController.php

**updateJobStatus()** — after broadcast, call:
BookingMessageService::post(\, \->status)

**cancelJob()** — after broadcast, call:
BookingMessageService::post(\, 'cancelled')

### 7. esources/views/client/messages/index.blade.php

**A) switchConversation()** — render system messages as .chat-banner divs
(centered, muted background) instead of .chat-bubble.

**B) Echo listener** — check e.is_system; if true render as banner.

**C) startPolling()** — include system messages (they're neither 'me' nor 'them').

**D) Add CSS:**
`css
.chat-banner {
    text-align: center;
    font-size: .78rem;
    color: var(--slate);
    padding: 8px 16px;
    margin: 4px auto;
    line-height: 1.5;
    background: var(--paper-2);
    border-radius: 8px;
    max-width: 90%;
}
`

### 8. esources/views/worker/messages/index.blade.php

Same changes as #7, applied to the equivalent JS locations.
