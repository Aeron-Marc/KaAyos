<?php

namespace App\Http\Controllers\Client;

use App\Exceptions\BookingStateException;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Review;
use App\Models\WorkerProfile;
use App\Models\BookingHistory;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Events\BookingCreated;
use App\Events\BookingStatusUpdated;
use App\Events\MessageSent;
use App\Services\BookingMessageService;
use App\Notifications\BookingCancelled;
use App\Notifications\NewBooking;
use App\Notifications\NewMessage;
use App\Notifications\NewReview;
use App\Notifications\RescheduleRequested;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ClientController extends Controller
{
    protected function shared(): array
    {
        $user = auth()->user();

        return [
            'categories'    => $this->getCategories(),
            'workers'       => $this->getWorkers(),
            'bookings'      => $this->getBookings(),
            'notifications' => $this->getNotifications(),
            'conversations' => $this->getConversations(),
            'reviews'       => $this->getReviews(),
            'stats'         => $this->getStats(),
        ];
    }

    private static function previewText(string $message): string
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

    protected function getCategories(): array
    {
        return ServiceCategory::orderBy('name')->get()->map(fn ($c) => [
            'id'    => strtolower($c->slug ?? $c->name),
            'name'  => $c->name,
            'icon'  => $c->icon ?? 'fa-wrench',
            'color' => 'ic-b',
        ])->toArray();
    }

    protected function getWorkers(): array
    {
        return User::where('role', 'worker')
            ->with('workerProfile.portfolios')
            ->active()
            ->get()
            ->map(fn ($u) => [
                'id'               => $u->id,
                'name'             => $u->name,
                'category'         => $u->service_category ?? 'General',
                'avatar'           => $u->avatar ? \Storage::url($u->avatar) : null,
                'initials'         => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
                'rating'           => $u->workerProfile?->average_rating ?? 0,
                'reviews'          => $u->reviewsReceived()->count(),
                'distance'         => config('kaayos.default_location'),
                'price'            => $u->workerProfile?->hourly_rate ?? 0,
                'verified'         => $u->workerProfile?->government_id_verified ?? false,
                'skills'           => $u->workerProfile?->skills ?? [],
                'profile_complete' => $u->workerProfile && (
                    $u->workerProfile->bio
                    || !empty($u->workerProfile->skills)
                    || !empty($u->workerProfile->spoken_languages)
                    || ($u->workerProfile->portfolios && $u->workerProfile->portfolios->count() > 0)
                ),
            ])->toArray();
    }

    protected function getBookings(): array
    {
        return auth()->user()->bookingsAsClient()
            ->with('worker', 'history')
            ->latest()
            ->get()
            ->map(function ($b) {
                $statusHistory = [];
                foreach ($b->history as $h) {
                    $statusHistory[$h->new_status] = $h->created_at;
                }
                if (!isset($statusHistory['new'])) {
                    $statusHistory['new'] = $b->created_at;
                }

                return [
                    'id'            => $b->id,
                    'worker'        => $b->worker->name ?? 'Unknown',
                    'worker_id'     => $b->worker_id,
                    'service'       => $b->service_category,
                    'date'          => $b->scheduled_at->format('M d, Y · h:i A'),
                    'month'         => $b->scheduled_at->format('M'),
                    'day'           => $b->scheduled_at->format('d'),
                    'time'          => $b->scheduled_at->format('g:i A'),
                    'status'        => ucfirst($b->status),
                    'raw_status'    => $b->status,
                    'price'         => $b->price ?? 0,
                    'location'      => $b->address,
                    'notes'         => $b->notes,
                    'created'       => $b->created_at->format('M d, Y · h:i A'),
                    'booking_ref'   => $b->booking_ref ?? 'BK-' . str_pad($b->id, 5, '0', STR_PAD_LEFT),
                    'status_history'=> $statusHistory,
                ];
            })->toArray();
    }

    protected function getNotifications(): array
    {
        $user = auth()->user();
        $list = [];

        $unreadBookings = $user->bookingsAsClient()->where('status', Booking::STATUS_NEW)->count();
        if ($unreadBookings > 0) {
            $list[] = [
                'type' => 'booking',
                'title' => 'Pending bookings',
                'desc' => "You have {$unreadBookings} booking request(s) waiting for worker response.",
                'time' => 'Just now',
                'unread' => true,
            ];
        }

        $unreadMessages = Message::forUser($user->id)->unread()->count();
        if ($unreadMessages > 0) {
            $list[] = [
                'type' => 'message',
                'title' => 'Unread messages',
                'desc' => "You have {$unreadMessages} unread message(s).",
                'time' => 'Just now',
                'unread' => true,
            ];
        }

        $pendingReview = $user->bookingsAsClient()
            ->where('status', Booking::STATUS_COMPLETED)
            ->whereDoesntHave('review')
            ->count();

        if (count($list) === 0) {
            $list[] = [
                'type' => 'system',
                'title' => 'Welcome to KaAyos!',
                'desc' => 'Browse workers and book a service to get started.',
                'time' => '1 day ago',
                'unread' => false,
            ];
        }

        return $list;
    }

    protected function getConversations(): array
    {
        $userId = auth()->id();
        $systemUserId = User::getSystemUserId();

        $conversations = Conversation::where('client_id', $userId)
            ->with('worker', 'messages')
            ->latest('last_message_at')
            ->get();

        $result = [];

        foreach ($conversations as $convo) {
            $messages = $convo->messages->sortBy('created_at');
            $lastMsg = $messages->last();
            $other = $convo->worker;

            $result[] = [
                'id'              => $convo->id,
                'conversation_id' => $convo->id,
                'name'            => $other?->name ?? 'Unknown',
                'worker_id'       => $convo->worker_id,
                'initials'        => strtoupper(
                    substr($other?->first_name ?? 'U', 0, 1) .
                    substr($other?->last_name ?? 'N', 0, 1)
                ),
                'preview'         => $lastMsg ? self::previewText($lastMsg->message) : 'No messages yet',
                'time'            => $lastMsg?->created_at?->diffForHumans()
                                    ?? $convo->last_message_at?->diffForHumans()
                                    ?? $convo->created_at->diffForHumans(),
                'active'          => false,
                'messages'        => $messages->map(fn ($m) => [
                    'id'        => $m->id,
                    'from'      => $m->sender_id === $systemUserId ? 'system' : ($m->sender_id === $userId ? 'me' : 'them'),
                    'text'      => $m->message,
                    'time'      => $m->created_at->diffForHumans(),
                    'is_system' => $m->sender_id === $systemUserId,
                ])->values()->toArray(),
            ];
        }

        if (!empty($result)) {
            $result[0]['active'] = true;
        }

        return $result;
    }

    protected function getReviews(): array
    {
        $user = auth()->user();

        $pending = $user->bookingsAsClient()
            ->where('status', Booking::STATUS_COMPLETED)
            ->whereDoesntHave('review')
            ->get()
            ->map(fn ($b) => [
                'worker'  => $b->worker->name ?? 'Unknown',
                'service' => $b->service_category,
                'date'    => $b->completed_at?->format('M d, Y') ?? $b->scheduled_at->format('M d, Y'),
                'booking_id' => $b->id,
            ])->toArray();

        return [
            'pending' => $pending,
            'past'    => $user->reviews()->with('worker')->latest()->get()->map(fn ($r) => [
                'worker'    => $r->worker->name ?? 'Unknown',
                'service'   => $r->booking->service_category ?? '',
                'date'      => $r->created_at->format('M d, Y'),
                'rating'    => $r->rating,
                'comment'   => $r->comment,
                'photo_url' => $r->photo_url,
            ])->toArray(),
        ];
    }

    protected function getStats(): array
    {
        $user = auth()->user();

        $activeBookings = $user->bookingsAsClient()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->count();

        $completedBookings = $user->bookingsAsClient()
            ->where('status', Booking::STATUS_COMPLETED)
            ->count();

        $unreadMessages = Message::forUser($user->id)->unread()->count();

        $pendingReviews = $user->bookingsAsClient()
            ->where('status', Booking::STATUS_COMPLETED)
            ->whereDoesntHave('review')
            ->count();

        return [
            ['label' => 'Active Bookings', 'value' => $activeBookings, 'icon' => 'fa-calendar-check', 'accent' => true],
            ['label' => 'Completed Jobs',  'value' => $completedBookings, 'icon' => 'fa-circle-check'],
            ['label' => 'Unread Messages', 'value' => $unreadMessages, 'icon' => 'fa-comment-dots'],
            ['label' => 'Pending Reviews', 'value' => $pendingReviews, 'icon' => 'fa-star'],
        ];
    }

    // ── Page Methods ──────────────────────────────────────────────

    public function dashboard(): View
    {
        return view('client.dashboard.overview', $this->shared());
    }

    public function notifications(): View
    {
        $data = $this->shared();
        $data['notifications'] = $this->getNotifications();  // refresh
        return view('client.dashboard.notifications', $data);
    }

    public function bookings(): View
    {
        return view('client.bookings.index', $this->shared());
    }

    public function messages(): View
    {
        return view('client.messages.index', $this->shared());
    }

    public function reviews(): View
    {
        return view('client.reviews.index', $this->shared());
    }

    public function profile(): View
    {
        return view('client.account.profile', $this->shared());
    }

    public function rescheduleRequest(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->client_id !== auth()->id()) abort(403);
        if (!$booking->isActive()) {
            return response()->json(['success' => false, 'message' => 'Can only reschedule active bookings.'], 422);
        }

        $validated = $request->validate([
            'proposed_at' => ['required', 'date', 'after:now'],
            'reason'      => ['nullable', 'string', 'max:500'],
        ]);

        $booking->update([
            'reschedule_requested_by'  => auth()->id(),
            'reschedule_proposed_at'   => $validated['proposed_at'],
            'reschedule_reason'        => $validated['reason'] ?? null,
            'reschedule_status'        => 'pending',
        ]);

        $booking->load('rescheduleRequestedBy');
        Notification::send($booking->worker, new RescheduleRequested($booking));

        return response()->json(['success' => true, 'message' => 'Reschedule request sent to worker.']);
    }

    public function respondReschedule(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->client_id !== auth()->id()) abort(403);
        if ($booking->reschedule_status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'No pending reschedule request.'], 422);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:approve,decline'],
        ]);

        if ($validated['action'] === 'approve') {
            $booking->update([
                'scheduled_at'           => $booking->reschedule_proposed_at,
                'reschedule_status'      => 'approved',
                'reschedule_responded_at' => now(),
            ]);
        } else {
            $booking->update([
                'reschedule_status'       => 'declined',
                'reschedule_responded_at' => now(),
            ]);
        }

        $booking->load('worker');
        Notification::send($booking->worker, new \App\Notifications\BookingStatusChanged($booking, $booking->status));

        return response()->json(['success' => true, 'message' => 'Reschedule request ' . $validated['action'] . 'd.']);
    }

    public function pollMessages(Request $request, Conversation $conversation): JsonResponse
    {
        if ($conversation->client_id !== auth()->id()) {
            abort(403);
        }

        $systemUserId = User::getSystemUserId();

        $query = $conversation->messages()->orderBy('created_at');

        if ($afterId = $request->integer('after')) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query->get()->map(fn ($m) => [
            'id'        => $m->id,
            'from'      => $m->sender_id === $systemUserId ? 'system' : ($m->sender_id === auth()->id() ? 'me' : 'them'),
            'text'      => $m->message,
            'time'      => $m->created_at->diffForHumans(),
            'is_system' => $m->sender_id === $systemUserId,
        ]);

        return response()->json(['messages' => $messages]);
    }

    public function markMessagesRead(Conversation $conversation): JsonResponse
    {
        if ($conversation->client_id !== auth()->id()) {
            abort(403);
        }

        $count = Message::markAllAsReadForConversation($conversation->id, auth()->id());

        return response()->json(['success' => true, 'marked_read' => $count]);
    }

    public function startConversation(Request $request): RedirectResponse
    {
        $request->validate(['worker_id' => ['required', 'exists:users,id']]);

        $worker = User::findOrFail($request->worker_id);

        if ($worker->role !== 'worker') {
            abort(404);
        }

        $conversation = Conversation::findOrCreateForPair(auth()->id(), $worker->id);

        return redirect()->route('client.messages', ['conversation' => $conversation->id]);
    }

    // ── API / Mutation Methods ────────────────────────────────────

    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
            'message'         => ['required', 'string', 'max:2000'],
        ]);

        $conversation = Conversation::findOrFail($validated['conversation_id']);

        if ($conversation->client_id !== auth()->id()) {
            abort(403);
        }

        $latestBooking = Booking::where('client_id', $conversation->client_id)
            ->where('worker_id', $conversation->worker_id)
            ->latest('created_at')
            ->first();

        $msg = Message::create([
            'conversation_id' => $conversation->id,
            'booking_id'      => $latestBooking?->id,
            'sender_id'       => auth()->id(),
            'receiver_id'     => $conversation->worker_id,
            'message'         => $validated['message'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        $msg->load('sender');

        $recipient = $conversation->worker;
        if ($recipient) {
            Notification::send($recipient, new NewMessage($msg));
        }
        broadcast(new MessageSent($msg))->toOthers();

        return response()->json([
            'success' => true,
            'message' => [
                'id'   => $msg->id,
                'from' => 'me',
                'text' => $msg->message,
                'time' => $msg->created_at->diffForHumans(),
            ],
        ]);
    }

    public function cancelBooking(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->client_id !== auth()->id()) {
            abort(403);
        }

        $oldStatus = $booking->status;

        try {
            $booking->cancel($request->input('reason', 'Cancelled by client'), auth()->id());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (BookingStateException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
        }

        $booking->load('worker');

        Notification::send($booking->worker, new BookingCancelled($booking, $booking->worker->name));
        broadcast(new BookingStatusUpdated($booking, $oldStatus))->toOthers();

        BookingMessageService::post($booking, 'cancelled');

        return response()->json(['success' => true]);
    }

    public function storeBooking(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worker_id'       => ['required', 'exists:users,id'],
            'service_category' => ['required', 'string', 'max:255'],
            'scheduled_at'    => ['required', 'date', 'after:now'],
            'house_no'        => ['required', 'string', 'max:255'],
            'barangay'        => ['required', 'string', 'max:255'],
            'notes'           => ['nullable', 'string', 'max:2000'],
            'price'           => ['nullable', 'numeric', 'min:0'],
        ]);

        $worker = User::findOrFail($validated['worker_id']);
        if ($worker->role !== 'worker') {
            return response()->json(['success' => false, 'message' => 'Invalid worker.'], 422);
        }

        if ($worker->suspended_at) {
            return response()->json(['success' => false, 'message' => 'This worker is currently unavailable.'], 422);
        }

        $overlap = Booking::where('worker_id', $worker->id)
            ->whereNotIn('status', [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED])
            ->where('scheduled_at', $validated['scheduled_at'])
            ->exists();

        if ($overlap) {
            return response()->json(['success' => false, 'message' => 'This worker already has a booking at the selected time.'], 422);
        }

        $address = $validated['house_no'] . ', ' . $validated['barangay'] . ', ' . config('kaayos.default_location');

        $booking = DB::transaction(function () use ($validated, $address, $worker) {
            $booking = Booking::create([
                'client_id'          => auth()->id(),
                'worker_id'          => $validated['worker_id'],
                'service_category'   => $validated['service_category'],
                'scheduled_at'       => $validated['scheduled_at'],
                'address'            => $address,
                'house_no'           => $validated['house_no'],
                'barangay'           => $validated['barangay'],
                'notes'              => $validated['notes'] ?? null,
                'price'              => $validated['price'] ?? 0,
                'status'             => Booking::STATUS_NEW,
                'agreed_by_client_at' => now(),
            ]);

            $booking->history()->create([
                'old_status' => null,
                'new_status' => Booking::STATUS_NEW,
                'user_id'    => auth()->id(),
            ]);

            return $booking;
        });

        $booking->load('client', 'worker');

        Notification::send($booking->worker, new NewBooking($booking));
        broadcast(new BookingCreated($booking))->toOthers();

        BookingMessageService::post($booking, 'new');

        return response()->json([
            'success' => true,
            'booking' => $booking,
            'redirect' => route('client.bookings'),
        ]);
    }

    public function submitReview(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->client_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== Booking::STATUS_COMPLETED) {
            return response()->json(['success' => false, 'message' => 'Can only review completed bookings.'], 422);
        }

        $validated = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'photo'   => ['nullable', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('review-photos', 'public')
            : null;

        $review = Review::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'client_id'  => auth()->id(),
                'worker_id'  => $booking->worker_id,
                'rating'     => $validated['rating'],
                'comment'    => $validated['comment'] ?? null,
                'photo_path' => $photoPath,
            ]
        );

        $review->load('client');

        $averageRating = (float) Review::where('worker_id', $booking->worker_id)->avg('rating');
        WorkerProfile::updateOrCreate(
            ['user_id' => $booking->worker_id],
            ['average_rating' => round($averageRating, 2)]
        );

        Notification::send($booking->worker, new NewReview($review));

        return response()->json(['success' => true, 'review' => $review]);
    }
}
