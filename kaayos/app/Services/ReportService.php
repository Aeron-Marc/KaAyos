<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\Review;
use App\Models\User;
use App\Models\WorkerDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public const REPORT_KEYS = [
        'bookings',
        'revenue',
        'users',
        'worker_performance',
        'verifications',
        'disputes',
        'service_popularity',
        'reviews',
    ];

    public const GROUPS = [
        'operational' => ['label' => 'Operational', 'icon' => 'fa-helmet-safety'],
        'financial' => ['label' => 'Financial', 'icon' => 'fa-coins'],
        'people' => ['label' => 'People', 'icon' => 'fa-users'],
        'performance' => ['label' => 'Performance', 'icon' => 'fa-chart-line'],
    ];

    public function catalog(): array
    {
        return [
            'bookings' => [
                'group' => 'operational',
                'label' => 'Bookings Report',
                'icon' => 'fa-calendar-check',
                'description' => 'Every booking in the period with client, worker, service, status and value.',
            ],
            'revenue' => [
                'group' => 'financial',
                'label' => 'Revenue & Earnings',
                'icon' => 'fa-sack-dollar',
                'description' => 'Completed bookings, gross revenue, platform fees and net worker payouts.',
            ],
            'users' => [
                'group' => 'people',
                'label' => 'User Registrations',
                'icon' => 'fa-user-plus',
                'description' => 'New account registrations split by role and account status.',
            ],
            'worker_performance' => [
                'group' => 'performance',
                'label' => 'Worker Performance',
                'icon' => 'fa-arrow-trend-up',
                'description' => 'Per-worker productivity, completion rates, ratings and revenue generated.',
            ],
            'verifications' => [
                'group' => 'people',
                'label' => 'Verification Activity',
                'icon' => 'fa-clipboard-check',
                'description' => 'Document submissions and review outcomes during the period.',
            ],
            'disputes' => [
                'group' => 'operational',
                'label' => 'Disputes & Reports',
                'icon' => 'fa-scale-balanced',
                'description' => 'Disputes and worker reports raised, their status and resolution.',
            ],
            'service_popularity' => [
                'group' => 'financial',
                'label' => 'Service Popularity',
                'icon' => 'fa-wrench',
                'description' => 'Bookings and revenue grouped by service category.',
            ],
            'reviews' => [
                'group' => 'performance',
                'label' => 'Reviews & Ratings',
                'icon' => 'fa-star',
                'description' => 'Ratings and feedback submitted, with average and star distribution.',
            ],
        ];
    }

    public function groups(): array
    {
        $catalog = $this->catalog();
        $groups = self::GROUPS;

        foreach ($groups as $key => &$meta) {
            $meta['reports'] = array_filter($catalog, fn ($report) => $report['group'] === $key);
        }

        return $groups;
    }

    public function build(string $type, string $from, string $to, ?int $limit = null): array
    {
        return match ($type) {
            'bookings' => $this->bookingsReport($from, $to, $limit),
            'revenue' => $this->revenueReport($from, $to, $limit),
            'users' => $this->usersReport($from, $to, $limit),
            'worker_performance' => $this->workerPerformanceReport($from, $to, $limit),
            'verifications' => $this->verificationsReport($from, $to, $limit),
            'disputes' => $this->disputesReport($from, $to, $limit),
            'service_popularity' => $this->servicePopularityReport($from, $to, $limit),
            'reviews' => $this->reviewsReport($from, $to, $limit),
            default => ['summary' => [], 'chart' => null, 'columns' => [], 'rows' => [], 'total_rows' => 0],
        };
    }

    // ── Reports ────────────────────────────────────────────────

    private function bookingsReport(string $from, string $to, ?int $limit): array
    {
        $end = $to.' 23:59:59';
        $base = Booking::query()->whereBetween('created_at', [$from.' 00:00:00', $end]);

        $collection = (clone $base)->with(['client', 'worker'])->latest('created_at')->when($limit, fn ($q) => $q->limit($limit))->get();
        $total = (int) (clone $base)->count();
        $completed = (int) (clone $base)->where('status', 'completed')->count();
        $cancelled = (int) (clone $base)->where('status', 'cancelled')->count();
        $active = (int) (clone $base)->whereIn('status', ['new', 'accepted', 'en_route', 'in_progress'])->count();
        $totalValue = (float) (clone $base)->sum('price');

        $rows = $collection->map(fn (Booking $b) => [
            'Booking Ref' => $b->booking_ref,
            'Client' => $b->client->name ?? 'N/A',
            'Worker' => $b->worker->name ?? 'N/A',
            'Service Category' => $b->service_category,
            'Status' => $b->status,
            'Price' => (float) $b->price,
            'Scheduled At' => $b->scheduled_at?->format('Y-m-d H:i'),
            'Created At' => $b->created_at->format('Y-m-d H:i'),
        ])->values()->all();

        $trend = $this->trend(clone $base, 'created_at', $from, $to, 'count');

        return [
            'summary' => [
                $this->kpi('Total Bookings', $total, 'fa-calendar-check', 'blue'),
                $this->kpi('Completed', $completed, 'fa-circle-check', 'green'),
                $this->kpi('Cancelled', $cancelled, 'fa-circle-xmark', 'red'),
                $this->kpi('Active', $active, 'fa-play', 'orange'),
                $this->kpi('Completion Rate', $total ? round($completed / $total * 100).'%' : '0%', 'fa-percent', 'purple'),
                $this->kpi('Total Value', $totalValue, 'fa-coins', 'green', true),
            ],
            'chart' => $this->trendChart($from, $to, $trend),
            'columns' => ['Booking Ref', 'Client', 'Worker', 'Service Category', 'Status', 'Price', 'Scheduled At', 'Created At'],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function revenueReport(string $from, string $to, ?int $limit): array
    {
        $end = $to.' 23:59:59';
        $base = Booking::query()
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$from.' 00:00:00', $end]);

        $collection = (clone $base)->with(['client', 'worker', 'earning'])->latest('completed_at')->when($limit, fn ($q) => $q->limit($limit))->get();
        $total = (int) $base->count();
        $gross = (float) $base->sum('price');
        $platformFees = (float) $collection->sum(fn (Booking $b) => (float) ($b->earning?->platform_fee ?? 0));
        $net = (float) $collection->sum(fn (Booking $b) => (float) ($b->earning?->net_amount ?? 0));

        $rows = $collection->map(fn (Booking $b) => [
            'Booking Ref' => $b->booking_ref,
            'Client' => $b->client->name ?? 'N/A',
            'Worker' => $b->worker->name ?? 'N/A',
            'Service Category' => $b->service_category,
            'Gross Amount' => (float) $b->price,
            'Platform Fee' => $b->earning?->platform_fee !== null ? (float) $b->earning->platform_fee : null,
            'Net Amount' => $b->earning?->net_amount !== null ? (float) $b->earning->net_amount : null,
            'Paid At' => $b->earning?->paid_at?->format('Y-m-d H:i'),
            'Completed At' => $b->completed_at?->format('Y-m-d H:i'),
        ])->values()->all();

        $trend = $this->trend(clone $base, 'completed_at', $from, $to, 'revenue');

        return [
            'summary' => [
                $this->kpi('Completed Bookings', $total, 'fa-circle-check', 'blue'),
                $this->kpi('Gross Revenue', $gross, 'fa-coins', 'green', true),
                $this->kpi('Platform Fees', $platformFees, 'fa-hand-holding-dollar', 'orange', true),
                $this->kpi('Net Payout', $net, 'fa-wallet', 'purple', true),
                $this->kpi('Avg Booking Value', $total ? round($gross / $total, 2) : 0, 'fa-chart-simple', 'blue', true),
            ],
            'chart' => $this->trendChart($from, $to, $trend, 'Revenue (₱)', '#10B981', 'bar'),
            'columns' => ['Booking Ref', 'Client', 'Worker', 'Service Category', 'Gross Amount', 'Platform Fee', 'Net Amount', 'Paid At', 'Completed At'],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function usersReport(string $from, string $to, ?int $limit): array
    {
        $end = $to.' 23:59:59';
        $base = User::query()->whereBetween('created_at', [$from.' 00:00:00', $end]);

        $collection = (clone $base)->latest('created_at')->when($limit, fn ($q) => $q->limit($limit))->get();
        $total = (int) (clone $base)->count();
        $clients = (int) (clone $base)->where('role', 'client')->count();
        $workers = (int) (clone $base)->where('role', 'worker')->count();
        $suspended = (int) (clone $base)->whereNotNull('suspended_at')->count();

        $rows = $collection->map(fn (User $u) => [
            'Registered At' => $u->created_at->format('Y-m-d H:i'),
            'Name' => $u->name,
            'Email' => $u->email,
            'Role' => ucfirst($u->role),
            'Phone' => $u->phone,
            'City' => $u->city,
            'Status' => $u->suspended_at ? 'Suspended' : 'Active',
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('New Users', $total, 'fa-user-plus', 'blue'),
                $this->kpi('Clients', $clients, 'fa-user', 'green'),
                $this->kpi('Workers', $workers, 'fa-briefcase', 'orange'),
                $this->kpi('Suspended', $suspended, 'fa-user-slash', 'red'),
            ],
            'chart' => [
                'type' => 'doughnut',
                'labels' => ['Clients', 'Workers', 'Admins'],
                'datasets' => [[
                    'data' => [
                        (int) (clone $base)->where('role', 'client')->count(),
                        (int) (clone $base)->where('role', 'worker')->count(),
                        (int) (clone $base)->where('role', 'admin')->count(),
                    ],
                    'colors' => ['#1A6FC4', '#10B981', '#8B5CF6'],
                ]],
            ],
            'columns' => ['Registered At', 'Name', 'Email', 'Role', 'Phone', 'City', 'Status'],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function workerPerformanceReport(string $from, string $to, ?int $limit): array
    {
        $end = $to.' 23:59:59';

        $base = User::query()
            ->where('role', 'worker')
            ->withCount([
                'bookingsAsWorker as total_bookings' => fn ($q) => $q->whereBetween('created_at', [$from.' 00:00:00', $end]),
                'bookingsAsWorker as completed_bookings' => fn ($q) => $q->where('status', 'completed')->whereBetween('completed_at', [$from.' 00:00:00', $end]),
                'bookingsAsWorker as cancelled_bookings' => fn ($q) => $q->where('status', 'cancelled')->whereBetween('cancelled_at', [$from.' 00:00:00', $end]),
                'reviewsReceived as review_count' => fn ($q) => $q->whereBetween('created_at', [$from.' 00:00:00', $end]),
            ])
            ->withSum([
                'bookingsAsWorker as gross_revenue' => fn ($q) => $q->where('status', 'completed')->whereBetween('completed_at', [$from.' 00:00:00', $end]),
            ], 'price')
            ->withAvg([
                'reviewsReceived as avg_rating' => fn ($q) => $q->whereBetween('created_at', [$from.' 00:00:00', $end]),
            ], 'rating')
            ->orderByDesc('completed_bookings');

        $all = $base->get();
        $totalWorkers = $all->count();
        $totalCompleted = (int) $all->sum('completed_bookings');
        $totalRevenue = (float) $all->sum('gross_revenue');
        $avgRating = $all->avg('avg_rating');
        $collection = $limit ? $all->take($limit) : $all;

        $rows = $collection->map(fn (User $w) => [
            'Worker' => $w->name,
            'Email' => $w->email,
            'Service Category' => $w->service_category ?: 'N/A',
            'Total Bookings' => (int) $w->total_bookings,
            'Completed' => (int) $w->completed_bookings,
            'Cancelled' => (int) $w->cancelled_bookings,
            'Completion Rate' => $w->total_bookings ? round($w->completed_bookings / $w->total_bookings * 100).'%' : '0%',
            'Avg Rating' => $w->avg_rating !== null ? round((float) $w->avg_rating, 1) : null,
            'Gross Revenue' => (float) $w->gross_revenue,
        ])->values()->all();

        $top = $collection->take(10)->map(fn (User $w) => [
            'label' => $w->name,
            'value' => (int) $w->completed_bookings,
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('Workers Tracked', $totalWorkers, 'fa-briefcase', 'blue'),
                $this->kpi('Total Completed', $totalCompleted, 'fa-circle-check', 'green'),
                $this->kpi('Avg Rating', $avgRating !== null ? number_format($avgRating, 1) : 'N/A', 'fa-star', 'orange'),
                $this->kpi('Gross Revenue', $totalRevenue, 'fa-coins', 'green', true),
            ],
            'chart' => [
                'type' => 'bar',
                'labels' => array_column($top, 'label'),
                'datasets' => [[
                    'label' => 'Completed',
                    'data' => array_column($top, 'value'),
                    'color' => '#1A6FC4',
                ]],
            ],
            'columns' => ['Worker', 'Email', 'Service Category', 'Total Bookings', 'Completed', 'Cancelled', 'Completion Rate', 'Avg Rating', 'Gross Revenue'],
            'rows' => $rows,
            'total_rows' => $totalWorkers,
        ];
    }

    private function verificationsReport(string $from, string $to, ?int $limit): array
    {
        $end = $to.' 23:59:59';
        $base = WorkerDocument::query()->whereBetween('created_at', [$from.' 00:00:00', $end]);

        $collection = (clone $base)->with(['user', 'reviewedBy'])->latest('created_at')->when($limit, fn ($q) => $q->limit($limit))->get();
        $total = (int) (clone $base)->count();
        $pending = (int) (clone $base)->where('status', 'pending')->count();
        $verified = (int) (clone $base)->where('status', 'verified')->count();
        $rejected = (int) (clone $base)->where('status', 'rejected')->count();

        $rows = $collection->map(fn (WorkerDocument $d) => [
            'Provider' => $d->user->name ?? 'N/A',
            'Email' => $d->user->email ?? 'N/A',
            'Document Type' => $d->document_type,
            'Status' => $d->status,
            'Reviewed At' => $d->reviewed_at?->format('Y-m-d H:i'),
            'Reviewed By' => $d->reviewedBy?->name ?? 'N/A',
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('Total Submissions', $total, 'fa-file-shield', 'blue'),
                $this->kpi('Pending', $pending, 'fa-clock', 'orange'),
                $this->kpi('Verified', $verified, 'fa-circle-check', 'green'),
                $this->kpi('Rejected', $rejected, 'fa-circle-xmark', 'red'),
            ],
            'chart' => $this->distributionChart([
                ['label' => 'Pending', 'value' => $pending, 'color' => '#F59E0B'],
                ['label' => 'Verified', 'value' => $verified, 'color' => '#10B981'],
                ['label' => 'Rejected', 'value' => $rejected, 'color' => '#EF4444'],
            ]),
            'columns' => ['Provider', 'Email', 'Document Type', 'Status', 'Reviewed At', 'Reviewed By'],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function disputesReport(string $from, string $to, ?int $limit): array
    {
        $end = $to.' 23:59:59';
        $base = Dispute::query()->whereBetween('created_at', [$from.' 00:00:00', $end]);

        $collection = (clone $base)->with(['booking', 'raisedBy', 'reportedWorker', 'resolvedBy'])->latest('created_at')->when($limit, fn ($q) => $q->limit($limit))->get();
        $total = (int) (clone $base)->count();
        $open = (int) (clone $base)->where('status', 'open')->count();
        $underReview = (int) (clone $base)->where('status', 'under_review')->count();
        $resolved = (int) (clone $base)->where('status', 'resolved')->count();

        $rows = $collection->map(fn (Dispute $d) => [
            'Booking Ref' => $d->booking?->booking_ref ?? 'N/A',
            'Type' => str_replace('_', ' ', ucfirst($d->type)),
            'Raised By' => $d->raisedBy->name ?? 'N/A',
            'Reported Worker' => $d->reportedWorker?->name ?? 'N/A',
            'Status' => str_replace('_', ' ', ucfirst($d->status)),
            'Reason' => $d->reason,
            'Resolved At' => $d->resolved_at?->format('Y-m-d H:i'),
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('Total Raised', $total, 'fa-scale-balanced', 'blue'),
                $this->kpi('Open', $open, 'fa-folder-open', 'red'),
                $this->kpi('Under Review', $underReview, 'fa-clock', 'orange'),
                $this->kpi('Resolved', $resolved, 'fa-circle-check', 'green'),
            ],
            'chart' => $this->distributionChart([
                ['label' => 'Open', 'value' => $open, 'color' => '#EF4444'],
                ['label' => 'Under Review', 'value' => $underReview, 'color' => '#F59E0B'],
                ['label' => 'Resolved', 'value' => $resolved, 'color' => '#10B981'],
            ]),
            'columns' => ['Booking Ref', 'Type', 'Raised By', 'Reported Worker', 'Status', 'Reason', 'Resolved At'],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function servicePopularityReport(string $from, string $to, ?int $limit): array
    {
        $end = $to.' 23:59:59';
        $base = Booking::query()
            ->whereBetween('created_at', [$from.' 00:00:00', $end])
            ->whereNotNull('service_category')
            ->where('service_category', '!=', '')
            ->selectRaw('service_category,
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END), 0) as completed,
                COALESCE(SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END), 0) as cancelled,
                COALESCE(SUM(CAST(price AS DECIMAL(12,2))), 0) as revenue')
            ->groupBy('service_category');

        $all = $base->get();
        $collection = (clone $base)->orderByDesc('total')->when($limit, fn ($q) => $q->limit($limit))->get();
        $totalBookings = (int) $all->sum('total');
        $totalRevenue = (float) $all->sum('revenue');

        $rows = $collection->map(fn ($row) => [
            'Service Category' => $row->service_category,
            'Bookings' => (int) $row->total,
            'Completed' => (int) $row->completed,
            'Cancelled' => (int) $row->cancelled,
            'Revenue' => (float) $row->revenue,
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('Categories Tracked', $all->count(), 'fa-layer-group', 'blue'),
                $this->kpi('Total Bookings', $totalBookings, 'fa-calendar-check', 'green'),
                $this->kpi('Total Revenue', $totalRevenue, 'fa-coins', 'green', true),
            ],
            'chart' => [
                'type' => 'bar',
                'labels' => $collection->pluck('service_category')->values()->all(),
                'datasets' => [[
                    'label' => 'Bookings',
                    'data' => $collection->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
                    'color' => '#1A6FC4',
                ]],
            ],
            'columns' => ['Service Category', 'Bookings', 'Completed', 'Cancelled', 'Revenue'],
            'rows' => $rows,
            'total_rows' => $all->count(),
        ];
    }

    private function reviewsReport(string $from, string $to, ?int $limit): array
    {
        $end = $to.' 23:59:59';
        $base = Review::query()->whereBetween('created_at', [$from.' 00:00:00', $end]);

        $collection = (clone $base)->with(['client', 'worker', 'booking'])->latest('created_at')->when($limit, fn ($q) => $q->limit($limit))->get();
        $total = (int) (clone $base)->count();
        $avg = (float) $base->avg('rating');
        $fiveStar = (int) (clone $base)->where('rating', 5)->count();
        $oneStar = (int) (clone $base)->where('rating', 1)->count();

        $rows = $collection->map(fn (Review $r) => [
            'Date' => $r->created_at->format('Y-m-d H:i'),
            'Client' => $r->client->name ?? 'N/A',
            'Worker' => $r->worker->name ?? 'N/A',
            'Booking Ref' => $r->booking?->booking_ref ?? 'N/A',
            'Rating' => $r->rating.' / 5',
            'Comment' => $r->comment,
        ])->values()->all();

        $stars = [];
        for ($s = 1; $s <= 5; $s++) {
            $stars[] = ['label' => $s.' Star', 'value' => (int) (clone $base)->where('rating', $s)->count(), 'color' => ['#EF4444', '#F59E0B', '#FBBF24', '#A3E635', '#10B981'][$s - 1]];
        }

        return [
            'summary' => [
                $this->kpi('Total Reviews', $total, 'fa-comment', 'blue'),
                $this->kpi('Average Rating', $total ? number_format($avg, 1).' / 5' : 'N/A', 'fa-star', 'orange'),
                $this->kpi('5-Star Reviews', $fiveStar, 'fa-face-smile', 'green'),
                $this->kpi('1-Star Reviews', $oneStar, 'fa-face-frown', 'red'),
            ],
            'chart' => $this->distributionChart($stars),
            'columns' => ['Date', 'Client', 'Worker', 'Booking Ref', 'Rating', 'Comment'],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    // ── Helpers ────────────────────────────────────────────────

    private function kpi(string $label, float|int|string $value, string $icon, string $accent, bool $money = false): array
    {
        return compact('label', 'value', 'icon', 'accent', 'money');
    }

    private function trend($query, string $dateColumn, string $from, string $to, string $metric): array
    {
        $end = $to.' 23:59:59';
        $series = $this->dateRange($from, $to);
        $unit = $series['unit'];
        $driver = DB::connection()->getDriverName();

        $groupExpr = $unit === 'month'
            ? ($driver === 'mysql' ? "DATE_FORMAT({$dateColumn}, '%Y-%m')" : "strftime('%Y-%m', {$dateColumn})")
            : ($driver === 'mysql' ? "DATE({$dateColumn})" : "date({$dateColumn})");
        $select = $groupExpr.' as d, COUNT(*) as count';
        if ($metric === 'revenue') {
            $select .= ', COALESCE(SUM(CAST(price AS DECIMAL(12,2))), 0) as revenue';
        }

        $agg = (clone $query)
            ->whereBetween($dateColumn, [$series['from'].' 00:00:00', $end])
            ->selectRaw($select)
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $labels = [];
        $counts = [];
        $revenues = [];
        foreach ($series['labels'] as $label) {
            $row = $agg->get($label);
            $labels[] = $label;
            $counts[] = (int) ($row->count ?? 0);
            $revenues[] = (float) ($row->revenue ?? 0);
        }

        return ['labels' => $labels, 'counts' => $counts, 'revenues' => $revenues, 'unit' => $unit];
    }

    private function trendChart(string $from, string $to, array $trend, string $revenueLabel = 'Revenue (₱)', string $revenueColor = '#10B981', string $type = 'line'): array
    {
        return [
            'type' => $type,
            'labels' => $trend['labels'],
            'datasets' => [
                [
                    'label' => 'Bookings',
                    'data' => $trend['counts'],
                    'color' => '#1A6FC4',
                ],
                [
                    'label' => $revenueLabel,
                    'data' => $trend['revenues'],
                    'color' => $revenueColor,
                ],
            ],
        ];
    }

    private function distributionChart(array $slices): array
    {
        return [
            'type' => 'doughnut',
            'labels' => array_column($slices, 'label'),
            'datasets' => [[
                'data' => array_column($slices, 'value'),
                'colors' => array_column($slices, 'color'),
            ]],
        ];
    }

    private function dateRange(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        $days = (int) $start->diffInDays($end) + 1;

        if ($days > 120) {
            $labels = [];
            $cursor = $start->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }

            return [
                'labels' => $labels,
                'unit' => 'month',
                'from' => $start->format('Y-m-d'),
                'to' => $end->format('Y-m-d'),
            ];
        }

        $labels = [];
        for ($i = 0; $i < $days; $i++) {
            $labels[] = $start->copy()->addDays($i)->format('Y-m-d');
        }

        return [
            'labels' => $labels,
            'unit' => 'day',
            'from' => $start->format('Y-m-d'),
            'to' => $end->format('Y-m-d'),
        ];
    }
}
