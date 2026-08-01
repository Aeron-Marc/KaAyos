<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Events\MessageSent;
use App\Notifications\NewMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ChatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $systemUserId = User::getSystemUserId();

        $query = $user->isWorker()
            ? Conversation::where('worker_id', $user->id)->with('client', 'messages.sender')
            : Conversation::where('client_id', $user->id)->with('worker', 'latestMessage');

        $conversations = $query->latest('last_message_at')
            ->take(30)
            ->get()
            ->map(function ($convo) use ($user, $systemUserId) {
                $other = $convo->client_id === $user->id ? $convo->worker : $convo->client;

                if ($user->isWorker()) {
                    $messages = $convo->messages->sortBy('created_at');
                    $lastMsg = $messages->last();
                    $unreadCount = $messages->where('receiver_id', $user->id)->whereNull('read_at')->count();
                } else {
                    $lastMsg = $convo->latestMessage;
                    $unreadCount = Message::where('conversation_id', $convo->id)
                        ->where('receiver_id', $user->id)
                        ->whereNull('read_at')
                        ->count();
                }

                return [
                    'id'            => $convo->id,
                    'other_user'    => [
                        'id'        => $other?->id,
                        'name'      => $other?->name ?? 'Unknown',
                        'avatar_url'=> $other?->avatar ? \Storage::url($other->avatar) : null,
                        'initials'  => strtoupper(
                            substr($other?->first_name ?? 'U', 0, 1) .
                            substr($other?->last_name ?? 'N', 0, 1)
                        ),
                    ],
                    'preview'       => $lastMsg ? self::previewText($lastMsg->message) : 'No messages yet',
                    'time'          => $lastMsg?->created_at?->diffForHumans()
                                        ?? $convo->last_message_at?->diffForHumans()
                                        ?? $convo->created_at->diffForHumans(),
                    'unread_count'  => $unreadCount,
                ];
            })
            ->values();

        return response()->json(['success' => true, 'conversations' => $conversations]);
    }

    public function start(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isWorker()) {
            $validated = $request->validate(['client_id' => ['required', 'exists:users,id']]);
            $client = User::findOrFail($validated['client_id']);
            if ($client->role !== 'client') {
                return response()->json(['success' => false, 'message' => 'Invalid client.'], 404);
            }
            $conversation = Conversation::findOrCreateForPair($client->id, $user->id);
        } else {
            $validated = $request->validate(['worker_id' => ['required', 'exists:users,id']]);
            $worker = User::findOrFail($validated['worker_id']);
            if ($worker->role !== 'worker') {
                return response()->json(['success' => false, 'message' => 'Invalid worker.'], 404);
            }
            $conversation = Conversation::findOrCreateForPair($user->id, $worker->id);
        }

        return response()->json([
            'success'         => true,
            'conversation_id' => $conversation->id,
        ]);
    }

    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->client_id !== $user->id && $conversation->worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You are not part of this conversation.'], 403);
        }

        $systemUserId = User::getSystemUserId();

        $query = $conversation->messages()->orderBy('created_at');

        if ($afterId = $request->integer('after')) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query->take(200)->get()->map(fn ($m) => [
            'id'        => $m->id,
            'from'      => $m->sender_id === $systemUserId ? 'system' : ($m->sender_id === $user->id ? 'me' : 'them'),
            'text'      => $m->message,
            'time'      => $m->created_at->diffForHumans(),
            'is_system' => $m->sender_id === $systemUserId,
            'read_at'   => $m->read_at?->diffForHumans(),
        ]);

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    public function send(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if ($conversation->client_id !== $user->id && $conversation->worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You are not part of this conversation.'], 403);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $receiverId = $user->id === $conversation->client_id
            ? $conversation->worker_id
            : $conversation->client_id;

        $latestBooking = Booking::where('client_id', $conversation->client_id)
            ->where('worker_id', $conversation->worker_id)
            ->latest('created_at')
            ->first();

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'booking_id'      => $latestBooking?->id,
            'sender_id'       => $user->id,
            'receiver_id'     => $receiverId,
            'message'         => $validated['message'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        $msg->load('sender');

        $recipient = $user->isWorker() ? $conversation->client : $conversation->worker;
        if ($recipient) {
            Notification::send($recipient, new NewMessage($msg));
        }
        broadcast(new MessageSent($msg))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id'      => $msg->id,
                'from'    => 'me',
                'text'    => $msg->message,
                'time'    => $msg->created_at->diffForHumans(),
                'read_at' => null,
            ],
        ]);
    }

    public function markRead(Conversation $conversation): JsonResponse
    {
        $user = request()->user();

        if ($conversation->client_id !== $user->id && $conversation->worker_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'You are not part of this conversation.'], 403);
        }

        $count = Message::markAllAsReadForConversation($conversation->id, $user->id);

        return response()->json(['success' => true, 'marked_read' => $count]);
    }

    protected static function previewText(string $message): string
    {
        $decoded = json_decode($message, true);
        if ($decoded && isset($decoded['type']) && $decoded['type'] === 'booking_status') {
            $labels = [
                'new'         => '📋 Booking created',
                'cancelled'   => '❌ Booking cancelled',
                'accepted'    => '✅ Booking accepted',
                'en_route'    => '🚗 Worker on the way',
                'in_progress' => '🔧 Work in progress',
                'completed'   => '✅ Booking completed',
            ];
            return $labels[$decoded['status']] ?? '📋 Booking ' . $decoded['status'];
        }
        return $message;
    }
}
