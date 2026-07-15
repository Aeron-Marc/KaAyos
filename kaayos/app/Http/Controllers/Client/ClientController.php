<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Message;
use App\Models\Review;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            ->with('workerProfile')
            ->active()
            ->get()
            ->map(fn ($u) => [
                'id'       => $u->id,
                'name'     => $u->name,
                'category' => $u->service_category ?? 'General',
                'avatar'   => $u->avatar ? \Storage::url($u->avatar) : null,
                'initials' => strtoupper(substr($u->first_name, 0, 1) . substr($u->last_name, 0, 1)),
                'rating'   => $u->workerProfile?->average_rating ?? 0,
                'reviews'  => $u->reviewsReceived()->count(),
                'distance' => $u->workerProfile?->service_areas ? 'Tuy, Batangas' : 'Tuy, Batangas',
                'price'    => $u->workerProfile?->hourly_rate ?? 0,
                'verified' => $u->workerProfile?->government_id_verified ?? false,
                'skills'   => $u->workerProfile?->skills ?? [],
            ])->toArray();
    }

    protected function getBookings(): array
    {
        return auth()->user()->bookingsAsClient()
            ->with('worker')
            ->latest()
            ->get()
            ->map(fn ($b) => [
                'id'         => $b->id,
                'worker'     => $b->worker->name ?? 'Unknown',
                'worker_id'  => $b->worker_id,
                'service'    => $b->service_category,
                'date'       => $b->scheduled_at->format('M d, Y · h:i A'),
                'status'     => ucfirst($b->status),
                'raw_status' => $b->status,
                'price'      => $b->price ?? 0,
            ])->toArray();
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

        $allBookings = auth()->user()->bookingsAsClient()
            ->with('worker')
            ->latest()
            ->get();

        $conversations = [];

        foreach ($allBookings as $booking) {
            $messages = Message::where('booking_id', $booking->id)
                ->orderBy('created_at')
                ->get();

            $lastMsg = $messages->last();
            $other = $booking->worker;

            $conversations[$booking->id] = [
                'id'         => $booking->id,
                'booking_id' => $booking->id,
                'name'       => $other?->name ?? 'Unknown',
                'worker_id'  => $booking->worker_id,
                'initials'   => strtoupper(
                    substr($other?->first_name ?? 'U', 0, 1) .
                    substr($other?->last_name ?? 'N', 0, 1)
                ),
                'preview'    => $lastMsg?->message ?? 'No messages yet',
                'time'       => $lastMsg?->created_at?->diffForHumans()
                               ?? $booking->scheduled_at->diffForHumans(),
                'active'     => false,
                'messages'   => $messages->map(fn ($m) => [
                    'from' => $m->sender_id === $userId ? 'me' : 'them',
                    'text' => $m->message,
                    'time' => $m->created_at->diffForHumans(),
                ])->values()->toArray(),
            ];
        }

        if (!empty($conversations)) {
            $first = reset($conversations);
            $first['active'] = true;
            $conversations[array_key_first($conversations)] = $first;
        }

        return array_values($conversations);
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
                'worker'  => $r->worker->name ?? 'Unknown',
                'service' => $r->booking->service_category ?? '',
                'date'    => $r->created_at->format('M d, Y'),
                'rating'  => $r->rating,
                'comment' => $r->comment,
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

    public function suggestions(): View
    {
        return view('client.suggestions.index', $this->shared());
    }

    // ── API / Mutation Methods ────────────────────────────────────

    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'message'    => ['required', 'string', 'max:2000'],
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        if ($booking->client_id !== auth()->id()) {
            abort(403);
        }

        $msg = Message::create([
            'booking_id'  => $booking->id,
            'sender_id'   => auth()->id(),
            'receiver_id' => $booking->worker_id,
            'message'     => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'message' => [
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

        if (!in_array($booking->status, [Booking::STATUS_NEW, Booking::STATUS_ACCEPTED])) {
            return response()->json([
                'success' => false,
                'message' => 'This booking can no longer be cancelled.',
            ], 422);
        }

        $booking->update([
            'status'             => 'cancelled',
            'cancelled_at'       => now(),
            'cancellation_reason' => $request->input('reason', 'Cancelled by client'),
        ]);

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

        $address = $validated['house_no'] . ', ' . $validated['barangay'] . ', Tuy, Batangas';

        $booking = Booking::create([
            'client_id'       => auth()->id(),
            'worker_id'       => $validated['worker_id'],
            'service_category' => $validated['service_category'],
            'scheduled_at'    => $validated['scheduled_at'],
            'address'         => $address,
            'notes'           => $validated['notes'] ?? null,
            'price'           => $validated['price'] ?? 0,
            'status'          => Booking::STATUS_NEW,
        ]);

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
        ]);

        $review = Review::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'client_id' => auth()->id(),
                'worker_id' => $booking->worker_id,
                'rating'    => $validated['rating'],
                'comment'   => $validated['comment'] ?? null,
            ]
        );

        return response()->json(['success' => true, 'review' => $review]);
    }
}
