<?php

namespace App\Http\Controllers\Worker;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Events\MessageSent;
use App\Notifications\NewMessage;
use App\Support\WorkerDocuments;
use App\Services\GeoTravelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkerController extends Controller
{
    protected function shared(): array
    {
        return [
            'stats'         => $this->getStats(),
            'jobRequests'   => $this->getJobRequests(),
            'schedule'      => $this->getSchedule(),
            'pastSchedule'  => $this->getPastSchedule(),
            'notifications' => $this->getNotifications(),
            'conversations' => $this->getConversations(),
            'earnings'      => $this->getEarnings(),
            'documents'     => $this->getDocuments(),
        ];
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $weekStart = now()->startOfWeek();

        $weeklyEarnings = $user->bookingsAsWorker()->completed()
            ->where('completed_at', '>=', $weekStart)
            ->sum('price') ?? 0;

        $activeJobs = $user->bookingsAsWorker()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->count();

        $totalCompleted = $user->bookingsAsWorker()->completed()->count();

        return [
            ['label' => 'Earnings This Week', 'value' => '₱' . number_format($weeklyEarnings), 'icon' => 'fa-coins', 'accent' => true],
            ['label' => 'Active Jobs',       'value' => $activeJobs,                         'icon' => 'fa-briefcase'],
            ['label' => 'Rating',            'value' => number_format($user->workerProfile?->average_rating ?? 0, 1) . ' ★', 'icon' => 'fa-star'],
            ['label' => 'Completed Jobs',    'value' => $totalCompleted,                     'icon' => 'fa-circle-check'],
        ];
    }

    protected function getJobRequests(?string $filter = null): array
    {
        $query = auth()->user()->bookingsAsWorker()->with('client', 'history');

        if ($filter && in_array($filter, Booking::STATUSES)) {
            $query->where('status', $filter);
        }

        return $query->latest()
            ->take(50)
            ->get()
            ->map(function ($booking) {
                $labelMap = [
                    Booking::STATUS_NEW        => 'New',
                    Booking::STATUS_ACCEPTED   => 'Accepted',
                    Booking::STATUS_EN_ROUTE   => 'En Route',
                    Booking::STATUS_IN_PROGRESS => 'In Progress',
                    Booking::STATUS_COMPLETED  => 'Completed',
                ];

                $statusHistory = [];
                foreach ($booking->history as $h) {
                    $statusHistory[$h->new_status] = $h->created_at;
                }
                if (!isset($statusHistory['new'])) {
                    $statusHistory['new'] = $booking->created_at;
                }

                return [
                    'id'             => $booking->id,
                    'client'         => $booking->client->name ?? 'Unknown',
                    'client_phone'   => $booking->client->phone ?? 'N/A',
                    'client_email'   => $booking->client->email ?? 'N/A',
                    'service'        => $booking->service_category,
                    'description'    => $booking->notes ?? 'No details provided.',
                    'date'           => $booking->scheduled_at->format('M d, Y · h:i A'),
                    'month'          => $booking->scheduled_at->format('M'),
                    'day'            => $booking->scheduled_at->format('d'),
                    'time'           => $booking->scheduled_at->format('g:i A'),
                    'location'       => $booking->address,
                    'barangay'       => $booking->barangay,
                    'latitude'       => $booking->latitude,
                    'longitude'      => $booking->longitude,
                    'status'         => $labelMap[$booking->status] ?? ucfirst($booking->status),
                    'raw_status'     => $booking->status,
                    'price'          => $booking->price ?? 0,
                    'property_type'  => $booking->property_type ?? 'residential',
                    'pricing_type'   => $booking->pricing_type ?? 'fixed',
                    'estimated_duration_hours' => $booking->estimated_duration_hours ?? 2.0,
                    'complexity_level' => $booking->complexity_level ?? 'standard',
                    'complexity_multiplier' => $booking->complexity_multiplier ?? 1.0,
                    'work_started_at' => $booking->work_started_at?->format('g:i A'),
                    'work_ended_at'   => $booking->work_ended_at?->format('g:i A'),
                    'scope_amendment_status' => $booking->scope_amendment_status,
                    'scope_amendment_price'  => $booking->scope_amendment_price,
                    'scope_amendment_notes'  => $booking->scope_amendment_notes,
                    'team_status'    => $booking->team_status ?? 'none',
                    'team_justification' => $booking->team_justification,
                    'crew'           => $booking->bookingWorkers()->with('worker')->get()->map(fn($bw) => [
                        'id'            => $bw->id,
                        'worker_name'   => $bw->worker->name,
                        'role'          => $bw->role,
                        'payout_amount' => $bw->payout_amount,
                        'status'        => $bw->status,
                    ])->values()->toArray(),
                    'created'        => $booking->created_at->format('M d, Y · h:i A'),
                    'booking_ref'    => $booking->booking_ref ?? 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                    'status_history' => $statusHistory,
                    'cancellation_reason' => $booking->cancellation_reason,
                    'cancelled_at'  => $booking->cancelled_at?->toIso8601String(),
                    'gmaps_nav_url'  => $booking->google_maps_nav_url,
                ];
            })
            ->toArray();
    }

    protected function getSchedule(): array
    {
        return auth()->user()->bookingsAsWorker()
            ->whereIn('status', [
                Booking::STATUS_NEW,
                Booking::STATUS_ACCEPTED,
                Booking::STATUS_EN_ROUTE,
                Booking::STATUS_IN_PROGRESS,
            ])
            ->with('client')
            ->orderBy('scheduled_at')
            ->take(20)
            ->get()
            ->map(function ($booking) {
                $labelMap = [
                    Booking::STATUS_NEW        => 'Pending',
                    Booking::STATUS_ACCEPTED   => 'Confirmed',
                    Booking::STATUS_EN_ROUTE   => 'En Route',
                    Booking::STATUS_IN_PROGRESS => 'In Progress',
                ];

                return [
                    'id'       => $booking->id,
                    'client'   => $booking->client->name ?? 'Unknown',
                    'service'  => $booking->service_category,
                    'date'     => $booking->scheduled_at->format('M d, Y'),
                    'time'     => $booking->scheduled_at->format('g:i A'),
                    'location' => $booking->address,
                    'status'   => $labelMap[$booking->status] ?? ucfirst($booking->status),
                    'raw_status' => $booking->status,
                ];
            })
            ->toArray();
    }

    protected function getPastSchedule(): array
    {
        return auth()->user()->bookingsAsWorker()
            ->where('status', Booking::STATUS_COMPLETED)
            ->with('client')
            ->latest('completed_at')
            ->take(20)
            ->get()
            ->map(function ($booking) {
                return [
                    'id'         => $booking->id,
                    'client'     => $booking->client->name ?? 'Unknown',
                    'service'    => $booking->service_category,
                    'date'       => $booking->completed_at?->format('M d, Y') ?? $booking->scheduled_at->format('M d, Y'),
                    'time'       => $booking->completed_at?->format('g:i A') ?? '',
                    'location'   => $booking->address,
                    'status'     => 'Completed',
                    'raw_status' => Booking::STATUS_COMPLETED,
                ];
            })
            ->toArray();
    }

    public function calendarData(Request $request): JsonResponse
    {
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $bookings = auth()->user()->bookingsAsWorker()
            ->with('client')
            ->whereBetween('scheduled_at', [$start, $end])
            ->orderBy('scheduled_at')
            ->get();

        $labelMap = [
            Booking::STATUS_NEW        => 'Pending',
            Booking::STATUS_ACCEPTED   => 'Confirmed',
            Booking::STATUS_EN_ROUTE   => 'En Route',
            Booking::STATUS_IN_PROGRESS => 'In Progress',
            Booking::STATUS_COMPLETED  => 'Completed',
            Booking::STATUS_CANCELLED  => 'Cancelled',
        ];

        $geoTravel = app(GeoTravelService::class);
        $days = [];

        // Group by day to compute inter-job travel metrics
        $grouped = $bookings->groupBy(fn($b) => $b->scheduled_at->day);

        foreach ($grouped as $day => $dayBookings) {
            $days[$day] = [];
            $routeInfo = $geoTravel->buildDailyRoute($dayBookings, auth()->user());
            $waypointsById = collect($routeInfo['waypoints'])->where('type', 'job')->keyBy('id');

            foreach ($dayBookings as $booking) {
                $wp = $waypointsById->get($booking->id);

                $days[$day][] = [
                    'id'                   => $booking->id,
                    'client'               => $booking->client->name ?? 'Unknown',
                    'client_phone'         => $booking->client->phone ?? '',
                    'service'              => $booking->service_category,
                    'time'                 => $booking->scheduled_at->format('g:i A'),
                    'time_24'              => $booking->scheduled_at->format('H:i'),
                    'location'             => $booking->address,
                    'barangay'             => $booking->barangay,
                    'latitude'             => $wp['latitude'] ?? (float) $booking->latitude,
                    'longitude'            => $wp['longitude'] ?? (float) $booking->longitude,
                    'status'               => $labelMap[$booking->status] ?? ucfirst($booking->status),
                    'raw_status'           => $booking->status,
                    'price'                => $booking->price ?? 0,
                    'booking_ref'          => $booking->booking_ref ?? 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
                    'travel_from_prev'     => $wp['travel_prev'] ?? null,
                    'feasibility'          => $wp['feasibility'] ?? 'feasible',
                    'gmaps_nav_url'        => $wp['nav_url'] ?? $booking->google_maps_nav_url,
                ];
            }
        }

        return response()->json([
            'year'   => (int) $year,
            'month'  => (int) $month,
            'days'   => $days,
            'today'  => now()->year === (int) $year && now()->month === (int) $month ? now()->day : null,
        ]);
    }

    public function dailyRoute(Request $request, GeoTravelService $geoTravel): JsonResponse
    {
        $dateStr = $request->query('date', now()->toDateString());
        $targetDate = \Carbon\Carbon::parse($dateStr);

        $bookings = auth()->user()->bookingsAsWorker()
            ->with('client')
            ->whereDate('scheduled_at', $targetDate)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_DECLINED])
            ->orderBy('scheduled_at')
            ->get();

        $itinerary = $geoTravel->buildDailyRoute($bookings, auth()->user());

        return response()->json([
            'date'            => $targetDate->format('Y-m-d'),
            'formatted_date'  => $targetDate->format('F d, Y'),
            'itinerary'       => $itinerary,
        ]);
    }

    public function optimizeRoute(Request $request, GeoTravelService $geoTravel): JsonResponse
    {
        $dateStr = $request->query('date', now()->toDateString());
        $targetDate = \Carbon\Carbon::parse($dateStr);

        $bookings = auth()->user()->bookingsAsWorker()
            ->with('client')
            ->whereDate('scheduled_at', $targetDate)
            ->whereNotIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_DECLINED])
            ->orderBy('scheduled_at')
            ->get();

        $optimization = $geoTravel->suggestOptimizedStopOrder($bookings, auth()->user());

        return response()->json([
            'date'            => $targetDate->format('Y-m-d'),
            'formatted_date'  => $targetDate->format('F d, Y'),
            'optimization'    => $optimization,
        ]);
    }

    public function jobDetails(Booking $booking): JsonResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $labelMap = [
            Booking::STATUS_NEW        => 'New',
            Booking::STATUS_ACCEPTED   => 'Accepted',
            Booking::STATUS_EN_ROUTE   => 'En Route',
            Booking::STATUS_IN_PROGRESS => 'In Progress',
            Booking::STATUS_COMPLETED  => 'Completed',
        ];

        $booking->load('client', 'history');

        $statusHistory = [];
        foreach ($booking->history as $h) {
            $statusHistory[$h->new_status] = $h->created_at->toIso8601String();
        }
        if (!isset($statusHistory['new'])) {
            $statusHistory['new'] = $booking->created_at->toIso8601String();
        }

        $geoTravel = app(GeoTravelService::class);
        [$lat, $lng] = $geoTravel->resolveBookingCoordinates($booking);

        return response()->json([
            'id'                  => $booking->id,
            'client'              => $booking->client->name ?? 'Unknown',
            'client_phone'        => $booking->client->phone ?? 'N/A',
            'client_email'        => $booking->client->email ?? 'N/A',
            'service'             => $booking->service_category,
            'description'         => $booking->notes ?? 'No details provided.',
            'date'                => $booking->scheduled_at->format('M d, Y · h:i A'),
            'month'               => $booking->scheduled_at->format('M'),
            'day'                 => $booking->scheduled_at->format('d'),
            'time'                => $booking->scheduled_at->format('g:i A'),
            'location'            => $booking->address,
            'barangay'            => $booking->barangay,
            'latitude'            => $lat,
            'longitude'           => $lng,
            'status'              => $labelMap[$booking->status] ?? ucfirst($booking->status),
            'raw_status'          => $booking->status,
            'price'               => $booking->price ?? 0,
            'property_type'       => $booking->property_type ?? 'residential',
            'pricing_type'        => $booking->pricing_type ?? 'fixed',
            'estimated_duration_hours' => $booking->estimated_duration_hours ?? 2.0,
            'complexity_level'    => $booking->complexity_level ?? 'standard',
            'complexity_multiplier' => $booking->complexity_multiplier ?? 1.0,
            'work_started_at'     => $booking->work_started_at?->format('g:i A'),
            'work_ended_at'       => $booking->work_ended_at?->format('g:i A'),
            'scope_amendment_status' => $booking->scope_amendment_status,
            'scope_amendment_price'  => $booking->scope_amendment_price,
            'scope_amendment_notes'  => $booking->scope_amendment_notes,
            'team_status'         => $booking->team_status ?? 'none',
            'team_justification'  => $booking->team_justification,
            'crew'                => $booking->bookingWorkers()->with('worker')->get()->map(fn($bw) => [
                'id'            => $bw->id,
                'worker_name'   => $bw->worker->name,
                'role'          => $bw->role,
                'payout_amount' => $bw->payout_amount,
                'status'        => $bw->status,
            ])->values()->toArray(),
            'created'             => $booking->created_at->format('M d, Y · h:i A'),
            'booking_ref'         => $booking->booking_ref ?? 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
            'status_history'      => $statusHistory,
            'cancellation_reason' => $booking->cancellation_reason,
            'cancelled_at'        => $booking->cancelled_at?->toIso8601String(),
            'gmaps_nav_url'       => "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}",
        ]);
    }

    public function updateLiveLocation(Request $request, Booking $booking, GeoTravelService $geoTravel): JsonResponse
    {
        if ($booking->worker_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        if ($booking->status !== Booking::STATUS_EN_ROUTE) {
            return response()->json([
                'success' => false,
                'message' => 'Live tracking is only active while en route.',
                'status'  => $booking->status,
            ], 422);
        }

        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'heading'   => ['nullable', 'numeric'],
            'speed'     => ['nullable', 'numeric'],
        ]);

        $workerLat = (float) $validated['latitude'];
        $workerLng = (float) $validated['longitude'];

        [$destLat, $destLng] = $geoTravel->resolveBookingCoordinates($booking);
        $travel = $geoTravel->calculateTravel($workerLat, $workerLng, $destLat, $destLng);

        $booking->update([
            'worker_live_latitude'         => $workerLat,
            'worker_live_longitude'        => $workerLng,
            'worker_live_heading'          => $validated['heading'] ?? null,
            'worker_live_speed'            => $validated['speed'] ?? null,
            'worker_live_updated_at'       => now(),
            'travel_distance_from_prev_km' => $travel['road_distance_km'],
            'estimated_transit_minutes'    => $travel['estimated_minutes'],
        ]);

        $workerProfile = auth()->user()->workerProfile;
        if ($workerProfile) {
            $workerProfile->update([
                'current_latitude'        => $workerLat,
                'current_longitude'       => $workerLng,
                'location_is_approximate' => false,
            ]);
        }

        return response()->json([
            'success'           => true,
            'remaining_km'      => $travel['road_distance_km'],
            'formatted_dist'    => $travel['formatted_distance'],
            'remaining_minutes' => $travel['estimated_minutes'],
            'formatted_time'    => $travel['formatted_time'],
            'eta_time'          => now()->addMinutes($travel['estimated_minutes'])->format('g:i A'),
            'live_tracked_at'   => now()->toIso8601String(),
        ]);
    }

    protected function getNotifications(): array
    {
        return auth()->user()->notifications
            ->map(function ($notif) {
                $data = $notif->data;

                $typeMap = [
                    'App\Notifications\NewBooking'            => 'booking',
                    'App\Notifications\BookingConfirmed'      => 'booking',
                    'App\Notifications\BookingCancelled'      => 'booking',
                    'App\Notifications\BookingCompleted'      => 'booking',
                    'App\Notifications\BookingStatusChanged'  => 'booking',
                    'App\Notifications\NewMessage'            => 'message',
                    'App\Notifications\NewReview'             => 'review',
                    'App\Notifications\RescheduleRequested'   => 'booking',
                    'App\Notifications\PayoutProcessed'       => 'earnings',
                    'App\Notifications\VerificationApproved'  => 'system',
                    'App\Notifications\VerificationRejected'  => 'system',
                    'App\Notifications\DocumentVerified'      => 'system',
                    'App\Notifications\DocumentRejected'      => 'system',
                    'App\Notifications\DisputeResolved'       => 'system',
                ];

                $type = $typeMap[$notif->type] ?? 'system';

                $url = match ($type) {
                    'message' => route('worker.messages', isset($data['conversation_id']) ? ['conversation' => $data['conversation_id']] : []),
                    'booking' => route('worker.schedule'),
                    'review'  => route('worker.schedule'),
                    'earnings'=> route('worker.earnings'),
                    default   => route('worker.dashboard'),
                };

                return [
                    'type'           => $type,
                    'title'          => $data['title'] ?? 'Notification',
                    'desc'           => $data['message'] ?? '',
                    'time'           => $notif->created_at->diffForHumans(),
                    'unread'         => is_null($notif->read_at),
                    'url'            => $url,
                    'booking_id'     => $data['booking_id'] ?? null,
                    'conversation_id'=> $data['conversation_id'] ?? null,
                ];
            })
            ->toArray();
    }

    private static function previewText(string $message): string
    {
        $decoded = json_decode($message, true);
        if ($decoded && isset($decoded['type']) && $decoded['type'] === 'booking_status') {
            $labels = [
                'new'         => '📋 Booking created',
                'accepted'    => '✅ Booking accepted',
                'en_route'    => '🚗 On the way',
                'in_progress' => '🔧 Work in progress',
                'completed'   => '✅ Booking completed',
                'cancelled'   => '❌ Booking cancelled',
            ];
            return $labels[$decoded['status']] ?? '📋 Booking ' . $decoded['status'];
        }
        return $message;
    }

    protected function getConversations(): array
    {
        $user = auth()->user();
        $systemUserId = User::getSystemUserId();

        $conversations = Conversation::where('worker_id', $user->id)
            ->with('client', 'messages.sender')
            ->latest('last_message_at')
            ->take(20)
            ->get();

        $result = [];

        foreach ($conversations as $convo) {
            $client = $convo->client;

            $messages = $convo->messages->sortBy('created_at');

            $lastMsg = $messages->last();
            $unreadCount = $messages->where('receiver_id', $user->id)->whereNull('read_at')->count();

            $initials = strtoupper(
                ($client?->first_name ? $client->first_name[0] : '') .
                ($client?->last_name ? $client->last_name[0] : '')
            );

            $result[] = [
                'active'          => false,
                'conversation_id' => $convo->id,
                'client_id'       => $client?->id,
                'initials'        => $initials ?: '?',
                'name'            => $client?->name ?? 'Unknown',
                'time'            => $lastMsg?->created_at->diffForHumans()
                                    ?? $convo->last_message_at?->diffForHumans()
                                    ?? $convo->created_at->diffForHumans(),
                'preview'         => $lastMsg ? self::previewText($lastMsg->message) : '',
                'unread_count'    => $unreadCount,
                'messages'        => $messages->map(fn($msg) => [
                    'id'        => $msg->id,
                    'from'      => $msg->sender_id === $systemUserId ? 'system' : ($msg->sender_id === $user->id ? 'me' : 'them'),
                    'text'      => $msg->message,
                    'time'      => $msg->created_at->diffForHumans(),
                    'is_system' => $msg->sender_id === $systemUserId,
                    'read_at'   => $msg->read_at?->diffForHumans(),
                ])->values()->toArray(),
            ];
        }

        if (!empty($result)) {
            $result[0]['active'] = true;
        }

        return $result;
    }

    protected function getEarnings(): array
    {
        $user = auth()->user();
        $now = now();

        $completed = $user->bookingsAsWorker()->completed()->take(50)->get();

        $total = (int) ($completed->sum('price') ?? 0);
        $thisMonth = (int) ($completed->filter(function ($b) use ($now) {
            return $b->completed_at
                && $b->completed_at->month === $now->month
                && $b->completed_at->year === $now->year;
        })->sum('price') ?? 0);

        $pendingPayout = (int) ($user->bookingsAsWorker()
            ->whereIn('status', [Booking::STATUS_ACCEPTED, Booking::STATUS_EN_ROUTE, Booking::STATUS_IN_PROGRESS])
            ->sum('price') ?? 0);

        $count = $completed->count();
        $avgPerJob = $count > 0 ? round($total / $count) : 0;

        $payouts = $completed->sortByDesc('completed_at')->values()
            ->map(function ($booking) {
                return [
                    'date'   => $booking->completed_at?->format('M d, Y') ?? 'N/A',
                    'client' => $booking->client->name ?? 'Unknown',
                    'job'    => $booking->service_category,
                    'amount' => $booking->price ?? 0,
                    'status' => 'Completed',
                ];
            })
            ->toArray();

        return [
            'total'          => $total,
            'this_month'     => $thisMonth,
            'pending_payout' => $pendingPayout,
            'avg_per_job'    => $avgPerJob,
            'payouts'        => $payouts,
        ];
    }

    protected function getDocuments(): array
    {
        $types = WorkerDocuments::types();

        $userDocs = auth()->user()->workerDocuments->keyBy('document_type');

        return array_map(function ($type) use ($userDocs) {
            $userDoc = $userDocs->get($type['name']);

            return [
                'name'        => $type['name'],
                'description' => $type['description'],
                'icon'        => $type['icon'],
                'status'      => $userDoc
                    ? ($userDoc->status === 'verified' ? 'Verified'
                        : ($userDoc->status === 'pending' ? 'Pending' : 'Not Submitted'))
                    : 'Not Submitted',
                'file'        => $userDoc?->file_path
                    ? basename($userDoc->file_path)
                    : null,
                'id'          => $userDoc?->id,
            ];
        }, $types);
    }

    public function dashboard(): View
    {
        return view('worker.dashboard.overview', $this->shared());
    }

    public function notifications(): View
    {
        return view('worker.dashboard.notifications', $this->shared());
    }

    public function jobs(Request $request): RedirectResponse
    {
        $query = $request->query('filter') ? ['filter' => $request->query('filter')] : [];
        return redirect()->route('worker.schedule', $query);
    }

    public function schedule(Request $request): View
    {
        $filter = $request->query('filter');
        $data = $this->shared();
        $data['jobRequests'] = $this->getJobRequests($filter);
        $data['activeFilter'] = $filter ?? '';

        $maximizer = app(\App\Services\ScheduleMaximizationService::class);
        $data['todayUtilization'] = $maximizer->computeDailyUtilization(auth()->user(), now());
        $data['availablePeers'] = User::where('role', 'worker')
            ->where('id', '!=', auth()->id())
            ->active()
            ->with('workerProfile')
            ->orderBy('name')
            ->get(['id', 'first_name', 'last_name', 'name', 'phone', 'service_category']);

        return view('worker.schedule.index', $data);
    }

    public function messages(): View
    {
        return view('worker.messages.index', $this->shared());
    }

    public function startConversation(Request $request): RedirectResponse
    {
        $request->validate(['client_id' => ['required', 'exists:users,id']]);

        $client = User::findOrFail($request->client_id);

        if ($client->role !== 'client') {
            abort(404);
        }

        $conversation = Conversation::findOrCreateForPair($client->id, auth()->id());

        return redirect()->route('worker.messages', ['conversation' => $conversation->id]);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'exists:conversations,id'],
            'message'         => ['required', 'string', 'max:2000'],
        ]);

        $conversation = Conversation::findOrFail($validated['conversation_id']);

        if ($conversation->worker_id !== auth()->id()) {
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
            'receiver_id'     => $conversation->client_id,
            'message'         => $validated['message'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        $msg->load('sender');

        $recipient = $conversation->client;
        if ($recipient) {
            try {
                Notification::send($recipient, new NewMessage($msg));
            } catch (\Exception $e) {
                Log::warning('Failed to send new message notification', [
                    'message_id' => $msg->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        try {
            broadcast(new MessageSent($msg))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Failed to broadcast message', [
                'message_id' => $msg->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => [
                'id'       => $msg->id,
                'from'     => 'me',
                'text'     => $msg->message,
                'time'     => $msg->created_at->diffForHumans(),
                'read_at'  => null,
            ],
        ]);
    }

    public function earnings(): View
    {
        return view('worker.earnings.index', $this->shared());
    }

    public function exportEarnings()
    {
        $payouts = $this->getEarnings()['payouts'];

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="earnings-export.csv"',
        ];

        $callback = function () use ($payouts) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Client', 'Job', 'Amount (₱)', 'Status']);

            foreach ($payouts as $row) {
                fputcsv($handle, [
                    $row['date'],
                    $row['client'],
                    $row['job'],
                    number_format($row['amount'], 2),
                    $row['status'],
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function profile(): View
    {
        $user = auth()->user();

        $profile = $user->workerProfile ?? WorkerProfile::create([
            'user_id' => $user->id,
        ]);

        $user->load('workerDocuments');

        $documents = $this->getDocuments();

        return view('worker.profile.index', array_merge(
            $this->shared(),
            [
                'workerProfile' => $profile,
                'portfolios'    => $profile->portfolios()->latest()->get(),
                'documents'     => $documents,
                'categories'    => \App\Models\ServiceCategory::active()->orderBy('name')->get(),
                'otherWorkers'  => User::where('role', 'worker')
                    ->where('id', '!=', $user->id)
                    ->active()
                    ->with('workerProfile')
                    ->orderBy('name')
                    ->get(['id', 'first_name', 'last_name', 'name', 'phone', 'service_category']),
            ]
        ));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name'         => ['required', 'string', 'max:100'],
            'last_name'          => ['required', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
            'city'               => ['nullable', 'string', 'max:255'],
            'language'           => ['required', 'string', Rule::in(['English', 'Filipino'])],
            'service_category'   => ['nullable', 'string', 'max:255'],
            'bio'                => ['nullable', 'string', 'max:2000'],
            'skills'             => ['nullable', 'string'],
            'spoken_languages'   => ['nullable', 'string'],
            'hourly_rate'        => ['nullable', 'numeric', 'min:0'],
            'available_days'     => ['nullable', 'string', 'max:255'],
            'preferred_hours'    => ['nullable', 'string', 'max:255'],
            'availability'       => ['nullable', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '' && !is_string($value)) {
                    $fail('The availability must be valid JSON.');
                }
                if (is_string($value) && $value !== '' && json_decode($value) === null && json_last_error() !== JSON_ERROR_NONE) {
                    $fail('The availability must be valid JSON.');
                }
            }],
            'service_areas'      => ['nullable', 'string'],
            'years_of_experience'=> ['nullable', 'integer', 'min:0', 'max:100'],
            'service_radius'     => ['nullable', 'integer', 'min:0', 'max:500'],
            'service_zone'       => ['nullable', 'string'],
            'tools_equipped'     => ['nullable'],
            'recommended_peers'  => ['nullable', 'array'],
            'recommended_peers.*.worker_id' => ['nullable', 'exists:users,id'],
            'recommended_peers.*.note'      => ['nullable', 'string', 'max:500'],
            'tesda_certified'    => ['nullable', 'boolean'],
            'barangay_clearance_verified' => ['nullable', 'boolean'],
            'min_notice_hours'   => ['nullable', 'integer', 'min:1', 'max:72'],
            'emergency_available'=> ['nullable', 'boolean'],
        ]);

        $updates = [
            'first_name'       => $data['first_name'],
            'last_name'        => $data['last_name'],
            'name'             => $data['first_name'] . ' ' . $data['last_name'],
            'phone'            => $data['phone'] ?? null,
            'language'         => $data['language'],
            'service_category' => $data['service_category'] ?? null,
        ];

        if (!empty($data['city'])) {
            $updates['city'] = $data['city'];
        }

        $user->update($updates);

        $profile = $user->workerProfile ?? new WorkerProfile(['user_id' => $user->id]);

        $profile->fill([
            'bio'                => $data['bio'] ?? null,
            'skills'             => isset($data['skills']) ? array_values(array_filter(array_map('trim', explode(',', $data['skills'])))) : null,
            'tools_equipped'     => isset($data['tools_equipped'])
                ? (is_array($data['tools_equipped']) ? array_values(array_filter($data['tools_equipped'])) : array_values(array_filter(array_map('trim', explode(',', $data['tools_equipped'])))))
                : ($profile->tools_equipped ?? []),
            'spoken_languages'   => isset($data['spoken_languages']) ? array_values(array_filter(array_map('trim', explode(',', $data['spoken_languages'])))) : null,
            'hourly_rate'        => $data['hourly_rate'] ?? null,
            'available_days'     => $data['available_days'] ?? null,
            'preferred_hours'    => $data['preferred_hours'] ?? null,
            'availability'       => isset($data['availability']) ? json_decode($data['availability'], true) : null,
            'service_areas'      => isset($data['service_areas']) ? array_values(array_filter(array_map('trim', explode(',', $data['service_areas'])))) : null,
            'years_of_experience'=> $data['years_of_experience'] ?? null,
            'service_radius'     => $data['service_radius'] ?? null,
            'service_zone'       => isset($data['service_zone']) ? array_values(array_filter(array_map('trim', explode(',', $data['service_zone'])))) : null,
            'recommended_peers'  => $request->has('recommended_peers')
                ? array_values(array_filter(
                    $request->input('recommended_peers', []),
                    fn($item) => !empty($item['worker_id']) && $item['worker_id'] != $user->id
                ))
                : ($profile->recommended_peers ?? []),
            'tesda_certified'    => $request->boolean('tesda_certified'),
            'barangay_clearance_verified' => $request->boolean('barangay_clearance_verified'),
            'min_notice_hours'   => $data['min_notice_hours'] ?? 2,
            'emergency_available'=> $request->boolean('emergency_available'),
        ]);

        $profile->save();

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Profile saved successfully.');
    }

    public function uploadAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Profile photo updated.');
    }

    public function uploadPortfolio(Request $request): RedirectResponse
    {
        $request->validate([
            'photo'   => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'caption' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $profile = $user->workerProfile ?? WorkerProfile::create(['user_id' => $user->id]);

        $path = $request->file('photo')->store('portfolios', 'public');

        $profile->portfolios()->create([
            'photo_path' => $path,
            'caption'    => $request->input('caption'),
        ]);

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Work photo added to portfolio.');
    }

    public function deletePortfolio(Request $request, $id): JsonResponse|RedirectResponse
    {
        $profile = auth()->user()->workerProfile;

        if (!$profile) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Profile not found.'], 404)
                : redirect()->route('worker.profile')->with('error', 'Profile not found.');
        }

        $portfolio = $profile->portfolios()->findOrFail($id);

        Storage::disk('public')->delete($portfolio->photo_path);
        $portfolio->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Portfolio photo removed.']);
        }

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Portfolio photo removed.');
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required', 'string'],
            'file'          => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        $user = $request->user();

        $doc = $user->workerDocuments()
            ->firstOrNew(['document_type' => $request->input('document_type')]);

        if ($doc->file_path) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $path = $request->file('file')->store('documents', 'public');

        $doc->file_path = $path;
        $doc->status = 'pending';
        $doc->verified_at = null;
        $doc->save();

        if ($user->workerProfile) {
            $user->workerProfile->update(['government_id_verified' => false]);
        }

        return redirect()
            ->route('worker.profile')
            ->with('success', 'Document uploaded successfully. Awaiting verification.');
    }

    public function documents(): View
    {
        return view('worker.documents.index', $this->shared());
    }

    public function pollMessages(Request $request, Conversation $conversation): JsonResponse
    {
        if ($conversation->worker_id !== auth()->id()) {
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
            'read_at'   => $m->read_at?->diffForHumans(),
        ]);

        return response()->json(['messages' => $messages]);
    }

    public function markMessagesRead(Conversation $conversation): JsonResponse
    {
        if ($conversation->worker_id !== auth()->id()) {
            abort(403);
        }

        $count = Message::markAllAsReadForConversation($conversation->id, auth()->id());

        return response()->json(['success' => true, 'marked_read' => $count]);
    }
}
