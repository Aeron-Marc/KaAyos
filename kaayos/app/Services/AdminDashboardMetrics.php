<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\User;
use App\Models\WorkerDocument;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for every admin dashboard widget.
 *
 * Reporting rules:
 *  - Booking counts, status splits and top categories use bookings created in
 *    the selected period.
 *  - Completion / cancellation / decline rates use terminal outcomes in the
 *    period (completed_at, cancelled_at, declined_at). The denominator is the
 *    number of terminal outcomes, so the three rates reconcile to 100%.
 *  - Revenue and tips use completed_at in the period.
 *  - Active, awaiting acceptance, stuck and unassigned bookings are current
 *    workload stocks and intentionally ignore the selected period.
 *  - Unresolved disputes and pending verifications are current stocks.
 */
class AdminDashboardMetrics
{
    public const ACTIVE_STATUSES = ['new', 'accepted', 'en_route', 'in_progress'];

    public const WORK_STATUSES = ['accepted', 'en_route', 'in_progress'];

    public const TERMINAL_STATUSES = ['completed', 'cancelled', 'declined'];

    public const STATUS_META = [
        'new' => ['color' => '#4B5563', 'icon' => 'fa-circle-plus'],
        'accepted' => ['color' => '#1D4ED8', 'icon' => 'fa-circle-check'],
        'en_route' => ['color' => '#0D9488', 'icon' => 'fa-location-arrow'],
        'in_progress' => ['color' => '#F59E0B', 'icon' => 'fa-gears'],
        'completed' => ['color' => '#10B981', 'icon' => 'fa-flag-checkered'],
        'cancelled' => ['color' => '#EF4444', 'icon' => 'fa-circle-xmark'],
        'declined' => ['color' => '#7C3AED', 'icon' => 'fa-ban'],
        'other' => ['color' => '#94A3B8', 'icon' => 'fa-circle-question'],
    ];

    public function __construct(private BookingCategoryResolver $categories) {}

    /**
     * @return array<string, mixed>
     */
    public function forPeriod(?string $period = null, string $queueSort = 'age'): array
    {
        $period = $this->resolvePeriodKey($period);
        $ttl = (int) config('dashboard.cache_ttl', 60);

        if ($ttl <= 0) {
            $data = $this->hydrate($this->build($period));
        } else {
            $data = Cache::remember(
                'admin.dashboard.metrics.'.app()->getLocale().'.'.$period,
                $ttl,
                fn () => $this->build($period)
            );
            $data = $this->hydrate($data);
        }

        $data['recent_bookings'] = $this->recentBookings($data['period']['from'], $data['period']['to']);
        $data['action_queue'] = $this->actionQueue($data['attention'], $queueSort);

        return $data;
    }

    /**
     * Small cached counts used by the admin sidebar.
     *
     * @return array{disputes: int, verifications: int}
     */
    public function navCounts(): array
    {
        $ttl = (int) config('dashboard.cache_ttl', 60);

        if ($ttl <= 0) {
            return $this->buildNavCounts();
        }

        return Cache::remember('admin.nav.counts', $ttl, fn () => $this->buildNavCounts());
    }

    /**
     * @return array{disputes: int, verifications: int}
     */
    private function buildNavCounts(): array
    {
        return [
            'disputes' => (int) Dispute::whereIn('status', ['open', 'under_review'])->count(),
            'verifications' => (int) WorkerDocument::pending()->distinct()->count('user_id'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function build(string $period): array
    {
        $range = $this->resolveRange($period);
        $statusCounts = $this->statusCounts($range['from'], $range['to']);
        $previousStatusCounts = $range['prev_from']
            ? $this->statusCounts($range['prev_from'], $range['prev_to'])
            : null;

        $outcomes = $this->outcomeCounts($range['from'], $range['to']);
        $previousOutcomes = $range['prev_from']
            ? $this->outcomeCounts($range['prev_from'], $range['prev_to'])
            : null;

        $allTimeOutcomes = $range['from'] === null
            ? $outcomes
            : $this->outcomeCounts(null, null);

        $currentStock = $this->currentStatusCounts();

        $meta = $range['meta'];
        foreach (['from', 'to', 'prev_from', 'prev_to'] as $key) {
            $meta[$key] = $meta[$key]?->toIso8601String();
        }

        return [
            'period' => $meta,
            'kpis' => $this->kpis(
                $statusCounts,
                $outcomes,
                $previousOutcomes,
                $previousStatusCounts,
                $allTimeOutcomes,
                $currentStock,
                $this->userCounts()
            ),
            'attention' => $this->attention(),
            'trend' => $this->trend($range['from'], $range['to'], $range['unit']),
            'status_distribution' => $this->statusDistribution($statusCounts),
            'top_categories' => $this->topCategories($range['from'], $range['to'], $statusCounts['total']),
            'recent_bookings' => [],
            'recent_total' => $this->recentTotal($range['from'], $range['to']),
            'revenue_this_month' => $this->calendarMonthRevenue(),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Restore date objects after a cache round-trip.
     *
     * Database/file cache may refuse to unserialize objects, so only scalar
     * values are cached and Carbon instances are rebuilt here.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function hydrate(array $data): array
    {
        foreach (['from', 'to', 'prev_from', 'prev_to'] as $key) {
            $data['period'][$key] = $data['period'][$key]
                ? Carbon::parse($data['period'][$key])
                : null;
        }

        $data['generated_at'] = Carbon::parse($data['generated_at']);

        return $data;
    }

    // Period handling -----------------------------------------------------

    private function resolvePeriodKey(?string $period): string
    {
        $allowed = config('dashboard.periods', []);

        return is_string($period) && isset($allowed[$period])
            ? $period
            : (string) config('dashboard.default_period', 'all_time');
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveRange(string $key): array
    {
        $now = now();
        $unit = (string) config("dashboard.periods.$key.unit", 'day');
        $from = null;
        $prevFrom = null;
        $prevTo = null;

        $from = match ($key) {
            'today' => $now->copy()->startOfDay(),
            '7d' => $now->copy()->subDays(6)->startOfDay(),
            '30d' => $now->copy()->subDays(29)->startOfDay(),
            'this_month' => $now->copy()->startOfMonth(),
            default => null,
        };

        if ($from !== null) {
            if ($key === 'this_month') {
                $prevFrom = $from->copy()->subMonthNoOverflow();
                $prevTo = $now->copy()->subMonthNoOverflow();
            } else {
                $days = $key === 'today' ? 1 : ($key === '7d' ? 7 : 30);
                $prevFrom = $from->copy()->subDays($days);
                $prevTo = $now->copy()->subDays($days);
            }
        }

        return [
            'from' => $from,
            'to' => $now,
            'prev_from' => $prevFrom,
            'prev_to' => $prevTo,
            'unit' => $unit,
            'meta' => [
                'key' => $key,
                'label_key' => 'admin.dash.period.'.$key,
                'unit' => $unit,
                'is_all_time' => $from === null,
                'from' => $from,
                'to' => $now,
                'prev_from' => $prevFrom,
                'prev_to' => $prevTo,
                'has_previous' => $prevFrom !== null,
            ],
        ];
    }

    // KPI blocks ----------------------------------------------------------

    /**
     * @param  array<string, mixed>  $statusCounts
     * @param  array<string, mixed>  $outcomes
     * @param  array<string, mixed>|null  $previousOutcomes
     * @param  array<string, mixed>|null  $previousStatusCounts
     * @param  array<string, mixed>  $allTimeOutcomes
     * @param  array<string, mixed>  $currentStock
     * @param  array<string, int>  $users
     * @return array<string, mixed>
     */
    private function kpis(
        array $statusCounts,
        array $outcomes,
        ?array $previousOutcomes,
        ?array $previousStatusCounts,
        array $allTimeOutcomes,
        array $currentStock,
        array $users
    ): array {
        $basis = (int) $outcomes['completed'] + (int) $outcomes['cancelled'] + (int) $outcomes['declined'];
        $previousBasis = $previousOutcomes
            ? (int) $previousOutcomes['completed'] + (int) $previousOutcomes['cancelled'] + (int) $previousOutcomes['declined']
            : 0;

        $completion = $this->rate((int) $outcomes['completed'], $basis);
        $cancellation = $this->rate((int) $outcomes['cancelled'], $basis);
        $decline = $this->rate((int) $outcomes['declined'], $basis);

        return [
            'total_bookings' => $statusCounts['total'],
            'previous_total_bookings' => $previousStatusCounts['total'] ?? null,
            'completed' => (int) $outcomes['completed'],
            'cancelled' => (int) $outcomes['cancelled'],
            'declined' => (int) $outcomes['declined'],
            'by_status' => $statusCounts['by_status'],
            'active_now' => [
                'value' => (int) $currentStock['active'],
            ],
            'awaiting_acceptance' => [
                'value' => (int) $currentStock['awaiting_acceptance'],
            ],
            'completion_rate' => [
                'value' => $completion,
                'previous' => $previousOutcomes ? $this->rate((int) $previousOutcomes['completed'], $previousBasis) : null,
                'completed' => (int) $outcomes['completed'],
                'basis' => $basis,
                'previous_basis' => $previousBasis,
            ],
            'cancellation_rate' => [
                'value' => $cancellation,
                'previous' => $previousOutcomes ? $this->rate((int) $previousOutcomes['cancelled'], $previousBasis) : null,
                'cancelled' => (int) $outcomes['cancelled'],
                'basis' => $basis,
                'previous_basis' => $previousBasis,
            ],
            'decline_rate' => [
                'value' => $decline,
                'previous' => $previousOutcomes ? $this->rate((int) $previousOutcomes['declined'], $previousBasis) : null,
                'declined' => (int) $outcomes['declined'],
                'basis' => $basis,
            ],
            'revenue' => [
                'value' => (float) $outcomes['revenue'],
                'previous' => $previousOutcomes ? (float) $previousOutcomes['revenue'] : null,
                'basis' => (int) $outcomes['completed'],
            ],
            'tips' => [
                'value' => (float) $outcomes['tips'],
                'previous' => $previousOutcomes ? (float) $previousOutcomes['tips'] : null,
            ],
            'gross_collected' => [
                'value' => (float) $outcomes['revenue'] + (float) $outcomes['tips'],
                'previous' => $previousOutcomes ? (float) $previousOutcomes['revenue'] + (float) $previousOutcomes['tips'] : null,
            ],
            'avg_value' => [
                'value' => (int) $outcomes['completed'] > 0
                    ? (float) $outcomes['revenue'] / (int) $outcomes['completed']
                    : null,
                'completed' => (int) $outcomes['completed'],
            ],
            'all_time_revenue' => (float) $allTimeOutcomes['revenue'],
            'all_time_tips' => (float) $allTimeOutcomes['tips'],
            'users' => $users,
        ];
    }

    private function rate(int $part, int $basis): int
    {
        return $basis > 0 ? (int) round(($part / $basis) * 100) : 0;
    }

    /**
     * @return array<string, int>
     */
    private function userCounts(): array
    {
        $roles = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return [
            'total' => (int) $roles->sum(),
            'clients' => (int) ($roles['client'] ?? 0),
            'workers' => (int) ($roles['worker'] ?? 0),
            'admins' => (int) ($roles['admin'] ?? 0),
        ];
    }

    // Needs attention -----------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function attention(): array
    {
        $stuckHours = (int) config('dashboard.targets.stuck_booking_hours', 24);
        $unassignedHours = (int) config('dashboard.targets.unassigned_booking_hours', 12);

        $stuck = Booking::query()
            ->stuck($stuckHours)
            ->selectRaw('COUNT(*) as total, MIN('.Booking::LAST_STATUS_TRANSITION_SQL.') as oldest')
            ->first();

        $unassigned = Booking::query()
            ->unassigned($unassignedHours)
            ->selectRaw('COUNT(*) as total, MIN(created_at) as oldest')
            ->first();

        $disputes = Dispute::whereIn('status', ['open', 'under_review'])
            ->selectRaw('COUNT(*) as total, MIN(created_at) as oldest')
            ->first();

        $verifications = WorkerDocument::pending()
            ->selectRaw('COUNT(DISTINCT user_id) as workers, COUNT(*) as documents, MIN(created_at) as oldest')
            ->first();

        $totalItems = (int) ($disputes->total ?? 0)
            + (int) ($verifications->workers ?? 0)
            + (int) ($stuck->total ?? 0)
            + (int) ($unassigned->total ?? 0);

        return [
            'unresolved_disputes' => (int) ($disputes->total ?? 0),
            'oldest_unresolved_dispute_days' => $disputes?->oldest
                ? (int) Carbon::parse($disputes->oldest)->diffInDays(now())
                : null,
            'pending_workers' => (int) ($verifications->workers ?? 0),
            'pending_documents' => (int) ($verifications->documents ?? 0),
            'oldest_pending_verification_hours' => $verifications?->oldest
                ? (int) Carbon::parse($verifications->oldest)->diffInHours(now())
                : null,
            'stuck_bookings' => (int) ($stuck->total ?? 0),
            'oldest_stuck_hours' => $stuck?->oldest
                ? (int) Carbon::parse($stuck->oldest)->diffInHours(now())
                : null,
            'unassigned_bookings' => (int) ($unassigned->total ?? 0),
            'oldest_unassigned_hours' => $unassigned?->oldest
                ? (int) Carbon::parse($unassigned->oldest)->diffInHours(now())
                : null,
            'stuck_threshold_hours' => $stuckHours,
            'unassigned_threshold_hours' => $unassignedHours,
            'suspended_users' => (int) User::whereNotNull('suspended_at')->count(),
            'completion_target' => config('dashboard.targets.completion_rate_min'),
            'cancellation_target' => config('dashboard.targets.cancellation_rate_max'),
            'total_items' => $totalItems,
            'all_clear' => $totalItems === 0,
        ];
    }

    // Action queue --------------------------------------------------------

    /**
     * Oldest actionable records across every live alert type.
     *
     * @param  array<string, mixed>  $attention
     * @return array<string, mixed>
     */
    private function actionQueue(array $attention, string $sort): array
    {
        $sort = $this->resolveQueueSort($sort);
        $limit = max(1, (int) config('dashboard.action_queue_limit', 8));
        $now = now();

        $items = array_merge(
            $this->unassignedQueueItems($limit, $now),
            $this->stuckQueueItems($limit, $now),
            $this->disputeQueueItems($limit, $now),
            $this->verificationQueueItems($limit, $now)
        );

        $items = match ($sort) {
            'priority' => collect($items)->sort(fn ($a, $b) => [$a['priority'], -$a['age_minutes']] <=> [$b['priority'], -$b['age_minutes']])->values()->all(),
            'type' => collect($items)->sort(fn ($a, $b) => [$a['type'], -$a['age_minutes']] <=> [$b['type'], -$b['age_minutes']])->values()->all(),
            default => collect($items)->sortByDesc('age_minutes')->values()->all(),
        };

        $items = array_slice($items, 0, $limit);
        $oldest = collect($items)->max('age_minutes');

        return [
            'items' => $items,
            'total' => (int) ($attention['total_items'] ?? 0),
            'shown' => count($items),
            'limit' => $limit,
            'sort' => $sort,
            'oldest_minutes' => $oldest !== null ? (int) $oldest : null,
        ];
    }

    private function resolveQueueSort(string $sort): string
    {
        return in_array($sort, ['age', 'priority', 'type'], true) ? $sort : 'age';
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function unassignedQueueItems(int $limit, Carbon $now): array
    {
        $hours = (int) config('dashboard.targets.unassigned_booking_hours', 12);

        return Booking::query()
            ->unassigned($hours)
            ->with('client')
            ->oldest('created_at')
            ->take($limit)
            ->get()
            ->map(fn (Booking $booking) => $this->queueItem(
                type: 'unassigned',
                priority: 3,
                title: $booking->booking_ref ?: '#'.$booking->id,
                context: $this->bookingContext($booking),
                ageMinutes: (int) $booking->created_at->diffInMinutes($now),
                status: $booking->status,
                href: route('admin.bookings.show', $booking->id),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stuckQueueItems(int $limit, Carbon $now): array
    {
        $hours = (int) config('dashboard.targets.stuck_booking_hours', 24);

        return Booking::query()
            ->stuck($hours)
            ->with(['client', 'worker'])
            ->select('bookings.*')
            ->selectRaw(Booking::LAST_STATUS_TRANSITION_SQL.' as last_transition_at')
            ->orderBy('last_transition_at')
            ->take($limit)
            ->get()
            ->map(fn (Booking $booking) => $this->queueItem(
                type: 'stuck',
                priority: 2,
                title: $booking->booking_ref ?: '#'.$booking->id,
                context: $this->bookingContext($booking, true),
                ageMinutes: (int) Carbon::parse($booking->last_transition_at)->diffInMinutes($now),
                status: $booking->status,
                href: route('admin.bookings.show', $booking->id),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function disputeQueueItems(int $limit, Carbon $now): array
    {
        return Dispute::query()
            ->whereIn('status', ['open', 'under_review'])
            ->with(['booking', 'raisedBy'])
            ->oldest('created_at')
            ->take($limit)
            ->get()
            ->map(fn (Dispute $dispute) => $this->queueItem(
                type: 'dispute',
                priority: 1,
                title: $dispute->booking?->booking_ref ?: '#'.$dispute->id,
                context: __('admin.dash.queue.context.dispute', [
                    'name' => $dispute->raisedBy->name ?? __('admin.common.not_available'),
                    'status' => __('status.'.$dispute->status),
                ]),
                ageMinutes: (int) $dispute->created_at->diffInMinutes($now),
                status: $dispute->status,
                href: route('admin.disputes.show', $dispute->id),
            ))
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function verificationQueueItems(int $limit, Carbon $now): array
    {
        $rows = WorkerDocument::query()
            ->pending()
            ->selectRaw('user_id, MIN(id) as document_id, MIN(created_at) as oldest_at, COUNT(*) as documents')
            ->groupBy('user_id')
            ->orderBy('oldest_at')
            ->take($limit)
            ->get();

        $workers = User::query()
            ->whereIn('id', $rows->pluck('user_id'))
            ->get()
            ->keyBy('id');

        return $rows
            ->map(fn ($row) => $this->queueItem(
                type: 'verification',
                priority: 4,
                title: $workers->get($row->user_id)?->name ?? __('admin.dash.queue.context.worker_fallback', ['id' => $row->user_id]),
                context: __('admin.dash.queue.context.verification', ['count' => (int) $row->documents]),
                ageMinutes: (int) Carbon::parse($row->oldest_at)->diffInMinutes($now),
                status: 'pending',
                href: route('admin.verification.show', (int) $row->document_id),
            ))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function queueItem(string $type, int $priority, string $title, string $context, int $ageMinutes, string $status, string $href): array
    {
        return [
            'type' => $type,
            'type_label' => __('admin.dash.queue.type.'.$type),
            'priority' => $priority,
            'title' => $title,
            'context' => $context,
            'age_minutes' => max(0, $ageMinutes),
            'status' => $status,
            'href' => $href,
        ];
    }

    private function bookingContext(Booking $booking, bool $withWorker = false): string
    {
        $context = [
            $booking->client->name ?? __('admin.common.not_available'),
            $booking->service_category ?: __('admin.dash.categories.uncategorized'),
        ];

        if ($withWorker) {
            $context[] = $booking->worker->name ?? __('admin.dash.queue.context.no_worker');
        }

        return implode(' · ', $context);
    }

    // Aggregates ----------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function statusCounts(?Carbon $from, ?Carbon $to): array
    {
        $rows = Booking::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $byStatus = [];
        foreach (array_keys(self::STATUS_META) as $status) {
            if ($status !== 'other') {
                $byStatus[$status] = (int) ($rows[$status] ?? 0);
            }
        }

        $total = (int) $rows->sum();
        $byStatus['other'] = max(0, $total - array_sum($byStatus));

        return [
            'by_status' => $byStatus,
            'total' => array_sum($byStatus),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function currentStatusCounts(): array
    {
        $rows = Booking::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $active = 0;
        foreach (self::WORK_STATUSES as $status) {
            $active += (int) ($rows[$status] ?? 0);
        }

        return [
            'active' => $active,
            'awaiting_acceptance' => (int) ($rows[Booking::STATUS_NEW] ?? 0),
        ];
    }

    /**
     * @return array{completed: int, cancelled: int, declined: int, revenue: float, tips: float}
     */
    private function outcomeCounts(?Carbon $from, ?Carbon $to): array
    {
        if ($from === null) {
            $completed = "status = 'completed'";
            $cancelled = "status = 'cancelled'";
            $declined = "status = 'declined'";
            $bindings = [];
        } else {
            $completed = "status = 'completed' AND completed_at >= ? AND completed_at <= ?";
            $cancelled = "status = 'cancelled' AND cancelled_at >= ? AND cancelled_at <= ?";
            $declined = "status = 'declined' AND declined_at >= ? AND declined_at <= ?";
            $bindings = [$from, $to];
        }

        $row = Booking::query()->selectRaw(
            "SUM(CASE WHEN $completed THEN 1 ELSE 0 END) as completed,
             SUM(CASE WHEN $cancelled THEN 1 ELSE 0 END) as cancelled,
             SUM(CASE WHEN $declined THEN 1 ELSE 0 END) as declined,
             COALESCE(SUM(CASE WHEN $completed THEN COALESCE(price, 0) ELSE 0 END), 0) as revenue,
             COALESCE(SUM(CASE WHEN $completed THEN COALESCE(tip_amount, 0) ELSE 0 END), 0) as tips",
            array_merge($bindings, $bindings, $bindings, $bindings, $bindings)
        )->first();

        return [
            'completed' => (int) ($row->completed ?? 0),
            'cancelled' => (int) ($row->cancelled ?? 0),
            'declined' => (int) ($row->declined ?? 0),
            'revenue' => (float) ($row->revenue ?? 0),
            'tips' => (float) ($row->tips ?? 0),
        ];
    }

    public function calendarMonthRevenue(): float
    {
        return (float) $this->outcomeCounts(now()->startOfMonth(), now())['revenue'];
    }

    /**
     * @param  array<string, mixed>  $statusCounts
     * @return array<string, mixed>
     */
    private function statusDistribution(array $statusCounts): array
    {
        $keys = $labels = $values = $colors = $icons = $percentages = [];

        foreach (self::STATUS_META as $key => $meta) {
            $count = $statusCounts['by_status'][$key] ?? 0;
            $keys[] = $key;
            $labels[] = __('status.'.$key);
            $values[] = $count;
            $colors[] = $meta['color'];
            $icons[] = $meta['icon'];
            $percentages[] = $statusCounts['total'] > 0
                ? (int) round(($count / $statusCounts['total']) * 100)
                : 0;
        }

        return [
            'keys' => $keys,
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
            'icons' => $icons,
            'percentages' => $percentages,
            'total' => $statusCounts['total'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function topCategories(?Carbon $from, ?Carbon $to, int $periodTotal): array
    {
        $rows = Booking::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->selectRaw('service_category, COUNT(*) as total')
            ->groupBy('service_category')
            ->get();

        $buckets = [];
        foreach ($rows as $row) {
            $label = $this->categories->resolve($row->service_category);
            $buckets[$label] = ($buckets[$label] ?? 0) + (int) $row->total;
        }

        arsort($buckets);
        $buckets = array_slice($buckets, 0, 5, true);

        $labels = array_keys($buckets);
        $values = array_values($buckets);
        $percentages = array_map(
            fn (int $value) => $periodTotal > 0 ? (int) round(($value / $periodTotal) * 100) : 0,
            $values
        );

        return [
            'labels' => $labels,
            'values' => $values,
            'percentages' => $percentages,
            'total_bookings' => $periodTotal,
        ];
    }

    // Trend ---------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    private function trend(?Carbon $from, ?Carbon $to, string $unit): array
    {
        if ($from === null) {
            $min = Booking::min('created_at');
            if (! $min) {
                return ['labels' => [], 'counts' => [], 'revenues' => [], 'unit' => $unit];
            }
            $from = Carbon::parse($min)->startOfDay();
        }

        $points = $this->buckets($from, $to, $unit);
        $pointCounts = $this->bucketed('created_at', null, $from, $to, $unit, false);
        $pointRevenues = $this->bucketed('completed_at', Booking::STATUS_COMPLETED, $from, $to, $unit, true);

        $labels = $counts = $revenues = [];
        foreach ($points as $key => $label) {
            $labels[] = $label;
            $counts[] = (int) ($pointCounts[$key] ?? 0);
            $revenues[] = (float) ($pointRevenues[$key] ?? 0);
        }

        return ['labels' => $labels, 'counts' => $counts, 'revenues' => $revenues, 'unit' => $unit];
    }

    /**
     * @return array<string, string> bucket key => axis label
     */
    private function buckets(Carbon $from, Carbon $to, string $unit): array
    {
        $points = [];
        $cursor = $from->copy();

        if ($unit === 'hour') {
            $cursor->startOfHour();
            while ($cursor <= $to) {
                $points[$cursor->format('Y-m-d H:00')] = $cursor->translatedFormat('g A');
                $cursor->addHour();
            }
        } elseif ($unit === 'month') {
            $cursor->startOfMonth();
            while ($cursor <= $to) {
                $points[$cursor->format('Y-m')] = $cursor->translatedFormat('M Y');
                $cursor->addMonthNoOverflow();
            }
        } else {
            $cursor->startOfDay();
            while ($cursor <= $to) {
                $points[$cursor->format('Y-m-d')] = $cursor->translatedFormat('M j');
                $cursor->addDay();
            }
        }

        return $points;
    }

    /**
     * @return array<string, float>
     */
    private function bucketed(string $dateColumn, ?string $status, Carbon $from, Carbon $to, string $unit, bool $sumPrice): array
    {
        $driver = DB::connection()->getDriverName();
        $expression = match ($unit) {
            'hour' => $driver === 'sqlite'
                ? "strftime('%Y-%m-%d %H:00', $dateColumn)"
                : "DATE_FORMAT($dateColumn, '%Y-%m-%d %H:00')",
            'month' => $driver === 'sqlite'
                ? "strftime('%Y-%m', $dateColumn)"
                : "DATE_FORMAT($dateColumn, '%Y-%m')",
            default => $driver === 'sqlite'
                ? "strftime('%Y-%m-%d', $dateColumn)"
                : "DATE($dateColumn)",
        };

        $metric = $sumPrice ? 'COALESCE(SUM(price), 0)' : 'COUNT(*)';

        return Booking::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->where($dateColumn, '>=', $from)
            ->where($dateColumn, '<=', $to)
            ->selectRaw("$expression as bucket, $metric as value")
            ->groupBy('bucket')
            ->pluck('value', 'bucket')
            ->all();
    }

    // Recent bookings -----------------------------------------------------

    /**
     * @return Collection<int, Booking>
     */
    private function recentBookings(?Carbon $from, ?Carbon $to): Collection
    {
        return Booking::with(['client', 'worker'])
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->latest()
            ->take(5)
            ->get();
    }

    private function recentTotal(?Carbon $from, ?Carbon $to): int
    {
        return Booking::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->count();
    }
}
