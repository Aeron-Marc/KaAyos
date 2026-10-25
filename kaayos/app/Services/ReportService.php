<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\Review;
use App\Models\User;
use App\Models\WorkerDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class ReportService
{
    public function __construct(private BookingCategoryResolver $categories) {}

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
                'label' => __('admin.reports.types.bookings.label'),
                'icon' => 'fa-calendar-check',
                'description' => __('admin.reports.types.bookings.description'),
            ],
            'revenue' => [
                'group' => 'financial',
                'label' => __('admin.reports.types.revenue.label'),
                'icon' => 'fa-sack-dollar',
                'description' => __('admin.reports.types.revenue.description'),
            ],
            'users' => [
                'group' => 'people',
                'label' => __('admin.reports.types.users.label'),
                'icon' => 'fa-user-plus',
                'description' => __('admin.reports.types.users.description'),
            ],
            'worker_performance' => [
                'group' => 'performance',
                'label' => __('admin.reports.types.worker_performance.label'),
                'icon' => 'fa-arrow-trend-up',
                'description' => __('admin.reports.types.worker_performance.description'),
            ],
            'verifications' => [
                'group' => 'people',
                'label' => __('admin.reports.types.verifications.label'),
                'icon' => 'fa-clipboard-check',
                'description' => __('admin.reports.types.verifications.description'),
            ],
            'disputes' => [
                'group' => 'operational',
                'label' => __('admin.reports.types.disputes.label'),
                'icon' => 'fa-scale-balanced',
                'description' => __('admin.reports.types.disputes.description'),
            ],
            'service_popularity' => [
                'group' => 'financial',
                'label' => __('admin.reports.types.service_popularity.label'),
                'icon' => 'fa-wrench',
                'description' => __('admin.reports.types.service_popularity.description'),
            ],
            'reviews' => [
                'group' => 'performance',
                'label' => __('admin.reports.types.reviews.label'),
                'icon' => 'fa-star',
                'description' => __('admin.reports.types.reviews.description'),
            ],
        ];
    }

    public function printChart(string $type): bool
    {
        return in_array($type, ['revenue', 'service_popularity', 'worker_performance', 'reviews'], true);
    }

    public function groups(): array
    {
        $catalog = $this->catalog();
        $groups = self::GROUPS;

        foreach ($groups as $key => &$meta) {
            $meta['label'] = __('admin.reports.groups.'.$key);
            $meta['reports'] = array_filter($catalog, fn ($report) => $report['group'] === $key);
        }

        return $groups;
    }

    public function build(string $type, string $from, string $to, ?int $limit = null, ?int $sortCol = null, string $sortDir = 'asc'): array
    {
        $data = match ($type) {
            'bookings' => $this->bookingsReport($from, $to),
            'revenue' => $this->revenueReport($from, $to),
            'users' => $this->usersReport($from, $to),
            'worker_performance' => $this->workerPerformanceReport($from, $to),
            'verifications' => $this->verificationsReport($from, $to),
            'disputes' => $this->disputesReport($from, $to),
            'service_popularity' => $this->servicePopularityReport($from, $to),
            'reviews' => $this->reviewsReport($from, $to),
            default => ['summary' => [], 'chart' => null, 'columns' => [], 'rows' => [], 'totals' => [], 'total_rows' => 0],
        };

        $columns = $this->columnMeta($type);
        $data['column_meta'] = $columns;
        $data['columns'] = array_keys($columns);
        $data['totals'] = $data['totals'] ?? [];

        $sortDir = $sortDir === 'desc' ? 'desc' : 'asc';
        $validSort = $sortCol !== null && isset($data['columns'][$sortCol]) ? $sortCol : null;
        $data['rows'] = $this->sortRows($data['rows'], $columns, $validSort, $sortDir);

        if ($limit !== null) {
            $data['rows'] = array_slice($data['rows'], 0, $limit);
        }

        $data['sort_col'] = $validSort;
        $data['sort_dir'] = $sortDir;

        return $data;
    }

    /**
     * Canonical column metadata shared by the preview, print view and exports.
     *
     * @return array<string, array{type: string, total?: bool, align?: string}>
     */
    public function columnMeta(string $type): array
    {
        return match ($type) {
            'bookings' => [
                'Booking Ref' => ['type' => 'text'],
                'Client' => ['type' => 'text'],
                'Worker' => ['type' => 'text'],
                'Service Category' => ['type' => 'text'],
                'Status' => ['type' => 'status'],
                'Price' => ['type' => 'money', 'total' => true, 'align' => 'right'],
                'Scheduled At' => ['type' => 'date'],
                'Created At' => ['type' => 'date'],
            ],
            'revenue' => [
                'Booking Ref' => ['type' => 'text'],
                'Client' => ['type' => 'text'],
                'Worker' => ['type' => 'text'],
                'Service Category' => ['type' => 'text'],
                'Gross Value' => ['type' => 'money', 'total' => true, 'align' => 'right'],
                'Tip' => ['type' => 'money', 'total' => true, 'align' => 'right'],
                'Total' => ['type' => 'money', 'total' => true, 'align' => 'right'],
                'Completed At' => ['type' => 'date'],
            ],
            'users' => [
                'Registered At' => ['type' => 'date'],
                'Name' => ['type' => 'text'],
                'Email' => ['type' => 'text'],
                'Role' => ['type' => 'text'],
                'Phone' => ['type' => 'text'],
                'City' => ['type' => 'text'],
                'Status' => ['type' => 'status'],
            ],
            'worker_performance' => [
                'Worker' => ['type' => 'text'],
                'Email' => ['type' => 'text'],
                'Service Category' => ['type' => 'text'],
                'Bookings Created' => ['type' => 'number', 'total' => true, 'align' => 'right'],
                'Completed' => ['type' => 'number', 'total' => true, 'align' => 'right'],
                'Cancelled' => ['type' => 'number', 'total' => true, 'align' => 'right'],
                'Declined' => ['type' => 'number', 'total' => true, 'align' => 'right'],
                'Completion Rate' => ['type' => 'percent', 'align' => 'right'],
                'Avg Rating' => ['type' => 'decimal', 'align' => 'right'],
                'Gross Revenue' => ['type' => 'money', 'total' => true, 'align' => 'right'],
            ],
            'verifications' => [
                'Provider' => ['type' => 'text'],
                'Email' => ['type' => 'text'],
                'Document Type' => ['type' => 'text'],
                'Status' => ['type' => 'status'],
                'Submitted At' => ['type' => 'date'],
                'Reviewed At' => ['type' => 'date'],
                'Reviewed By' => ['type' => 'text'],
            ],
            'disputes' => [
                'Booking Ref' => ['type' => 'text'],
                'Type' => ['type' => 'text'],
                'Raised By' => ['type' => 'text'],
                'Reported Worker' => ['type' => 'text'],
                'Status' => ['type' => 'status'],
                'Reason' => ['type' => 'text'],
                'Created At' => ['type' => 'date'],
                'Resolved At' => ['type' => 'date'],
            ],
            'service_popularity' => [
                'Service Category' => ['type' => 'text'],
                'Bookings' => ['type' => 'number', 'total' => true, 'align' => 'right'],
                'Completed' => ['type' => 'number', 'total' => true, 'align' => 'right'],
                'Cancelled' => ['type' => 'number', 'total' => true, 'align' => 'right'],
                'Completed Value' => ['type' => 'money', 'total' => true, 'align' => 'right'],
            ],
            'reviews' => [
                'Date' => ['type' => 'date'],
                'Client' => ['type' => 'text'],
                'Worker' => ['type' => 'text'],
                'Booking Ref' => ['type' => 'text'],
                'Rating' => ['type' => 'rating', 'align' => 'right'],
                'Comment' => ['type' => 'text'],
            ],
            default => [],
        };
    }

    // ── Reports ────────────────────────────────────────────────

    private function bookingsReport(string $from, string $to): array
    {
        [$start, $end] = $this->range($from, $to);
        $base = Booking::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);

        $collection = (clone $base)->with(['client', 'worker'])->latest('created_at')->get();
        $total = (int) (clone $base)->count();
        $outcomes = $this->outcomeTotals($start, $end);

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
                $this->kpi('Bookings Created', $total, 'fa-calendar-check', 'blue'),
                $this->kpi('Completed', $outcomes['completed'], 'fa-circle-check', 'green'),
                $this->kpi('Cancelled', $outcomes['cancelled'], 'fa-circle-xmark', 'red'),
                $this->kpi('Declined', $outcomes['declined'], 'fa-ban', 'orange'),
                $this->kpi('Completion Rate', $this->percentage($outcomes['completed'], $outcomes['basis']), 'fa-percent', 'purple'),
                $this->kpi('Gross Value', $outcomes['gross_value'], 'fa-coins', 'green', true),
            ],
            'chart' => [
                'type' => 'bar',
                'labels' => $trend['labels'],
                'datasets' => [[
                    'label' => __('admin.reports.kpis.bookings-created'),
                    'data' => $trend['counts'],
                    'color' => '#1A6FC4',
                ]],
            ],
            'totals' => [],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function revenueReport(string $from, string $to): array
    {
        [$start, $end] = $this->range($from, $to);
        $base = Booking::query()
            ->where('status', 'completed')
            ->where('completed_at', '>=', $start)
            ->where('completed_at', '<', $end);

        $collection = (clone $base)->with(['client', 'worker'])->latest('completed_at')->get();
        $total = (int) $base->count();
        $totalQuoted = (float) (clone $base)->sum('price');
        $totalTips = (float) (clone $base)->sum('tip_amount');
        $grossCollected = $totalQuoted + $totalTips;

        $rows = $collection->map(fn (Booking $b) => [
            'Booking Ref' => $b->booking_ref,
            'Client' => $b->client->name ?? 'N/A',
            'Worker' => $b->worker->name ?? 'N/A',
            'Service Category' => $b->service_category,
            'Gross Value' => (float) ($b->price ?? 0),
            'Tip' => (float) ($b->tip_amount ?? 0),
            'Total' => (float) ($b->price ?? 0) + (float) ($b->tip_amount ?? 0),
            'Completed At' => $b->completed_at?->format('Y-m-d H:i'),
        ])->values()->all();

        $trend = $this->trend(clone $base, 'completed_at', $from, $to, 'revenue');

        return [
            'summary' => [
                $this->kpi('Completed Bookings', $total, 'fa-circle-check', 'blue'),
                $this->kpi('Gross Value', $totalQuoted, 'fa-coins', 'green', true),
                $this->kpi('Total Tips', $totalTips, 'fa-hand-holding-dollar', 'orange', true),
                $this->kpi('Gross Collected', $grossCollected, 'fa-sack-dollar', 'green', true),
                $this->kpi('Avg Job Value', $total ? round($totalQuoted / $total, 2) : 0, 'fa-chart-simple', 'blue', true),
            ],
            'chart' => $this->trendChart($from, $to, $trend, __('admin.reports.kpis.gross-value').' (PHP)', '#10B981', 'bar', __('admin.reports.kpis.completed-bookings')),
            'totals' => [
                'Gross Value' => $totalQuoted,
                'Tip' => $totalTips,
                'Total' => $grossCollected,
            ],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function usersReport(string $from, string $to): array
    {
        [$start, $end] = $this->range($from, $to);
        $base = User::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);

        $collection = (clone $base)->latest('created_at')->get();
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
            'Status' => $u->suspended_at ? 'suspended' : 'active',
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
                'labels' => [
                    __('admin.reports.kpis.clients'),
                    __('admin.reports.kpis.workers'),
                    __('admin.reports.kpis.admins'),
                ],
                'datasets' => [[
                    'data' => [
                        (int) (clone $base)->where('role', 'client')->count(),
                        (int) (clone $base)->where('role', 'worker')->count(),
                        (int) (clone $base)->where('role', 'admin')->count(),
                    ],
                    'colors' => ['#1A6FC4', '#10B981', '#8B5CF6'],
                ]],
            ],
            'totals' => [],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function workerPerformanceReport(string $from, string $to): array
    {
        [$start, $end] = $this->range($from, $to);

        $base = User::query()
            ->where('role', 'worker')
            ->withCount([
                'bookingsAsWorker as total_bookings' => fn ($q) => $q->where('created_at', '>=', $start)->where('created_at', '<', $end),
                'bookingsAsWorker as completed_bookings' => fn ($q) => $q->where('status', 'completed')->where('completed_at', '>=', $start)->where('completed_at', '<', $end),
                'bookingsAsWorker as cancelled_bookings' => fn ($q) => $q->where('status', 'cancelled')->where('cancelled_at', '>=', $start)->where('cancelled_at', '<', $end),
                'bookingsAsWorker as declined_bookings' => fn ($q) => $q->where('status', 'declined')->where('declined_at', '>=', $start)->where('declined_at', '<', $end),
                'reviewsReceived as review_count' => fn ($q) => $q->where('created_at', '>=', $start)->where('created_at', '<', $end),
            ])
            ->withSum([
                'bookingsAsWorker as gross_revenue' => fn ($q) => $q->where('status', 'completed')->where('completed_at', '>=', $start)->where('completed_at', '<', $end),
            ], 'price')
            ->withAvg([
                'reviewsReceived as avg_rating' => fn ($q) => $q->where('created_at', '>=', $start)->where('created_at', '<', $end),
            ], 'rating')
            ->orderByDesc('completed_bookings');

        $all = $base->get();
        $totalWorkers = $all->count();
        $totalCreated = (int) $all->sum('total_bookings');
        $totalCompleted = (int) $all->sum('completed_bookings');
        $totalCancelled = (int) $all->sum('cancelled_bookings');
        $totalDeclined = (int) $all->sum('declined_bookings');
        $totalTerminal = $totalCompleted + $totalCancelled + $totalDeclined;
        $totalRevenue = (float) $all->sum('gross_revenue');
        $avgRating = $all->avg('avg_rating');

        $rows = $all->map(function (User $w) {
            $terminal = (int) $w->completed_bookings + (int) $w->cancelled_bookings + (int) $w->declined_bookings;

            return [
                'Worker' => $w->name,
                'Email' => $w->email,
                'Service Category' => $w->service_category ?: 'N/A',
                'Bookings Created' => (int) $w->total_bookings,
                'Completed' => (int) $w->completed_bookings,
                'Cancelled' => (int) $w->cancelled_bookings,
                'Declined' => (int) $w->declined_bookings,
                'Completion Rate' => $this->percentage((int) $w->completed_bookings, $terminal),
                'Avg Rating' => $w->avg_rating !== null ? round((float) $w->avg_rating, 1) : null,
                'Gross Revenue' => (float) $w->gross_revenue,
            ];
        })->values()->all();

        $top = $all->take(10)->map(fn (User $w) => [
            'label' => $w->name,
            'value' => (int) $w->completed_bookings,
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('Workers Tracked', $totalWorkers, 'fa-briefcase', 'blue'),
                $this->kpi('Bookings Created', $totalCreated, 'fa-calendar-check', 'purple'),
                $this->kpi('Completed', $totalCompleted, 'fa-circle-check', 'green'),
                $this->kpi('Avg Rating', $avgRating !== null ? number_format($avgRating, 1) : 'N/A', 'fa-star', 'orange'),
                $this->kpi('Gross Revenue', $totalRevenue, 'fa-coins', 'green', true),
            ],
            'chart' => [
                'type' => 'bar',
                'labels' => array_column($top, 'label'),
                'datasets' => [[
                    'label' => __('admin.reports.kpis.completed'),
                    'data' => array_column($top, 'value'),
                    'color' => '#1A6FC4',
                ]],
            ],
            'totals' => [
                'Bookings Created' => $totalCreated,
                'Completed' => $totalCompleted,
                'Cancelled' => $totalCancelled,
                'Declined' => $totalDeclined,
                'Completion Rate' => $this->percentage($totalCompleted, $totalTerminal),
                'Gross Revenue' => $totalRevenue,
            ],
            'rows' => $rows,
            'total_rows' => $totalWorkers,
        ];
    }

    private function verificationsReport(string $from, string $to): array
    {
        [$start, $end] = $this->range($from, $to);
        $base = WorkerDocument::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);

        $collection = (clone $base)->with(['user', 'reviewedBy'])->latest('created_at')->get();
        $total = (int) (clone $base)->count();
        $pending = (int) (clone $base)->where('status', 'pending')->count();
        $verified = (int) (clone $base)->where('status', 'verified')->count();
        $rejected = (int) (clone $base)->where('status', 'rejected')->count();
        $reviewedInPeriod = (int) WorkerDocument::query()
            ->where('reviewed_at', '>=', $start)
            ->where('reviewed_at', '<', $end)
            ->count();

        $rows = $collection->map(fn (WorkerDocument $d) => [
            'Provider' => $d->user->name ?? 'N/A',
            'Email' => $d->user->email ?? 'N/A',
            'Document Type' => $d->document_type,
            'Status' => $d->status,
            'Submitted At' => $d->created_at->format('Y-m-d H:i'),
            'Reviewed At' => $d->reviewed_at?->format('Y-m-d H:i'),
            'Reviewed By' => $d->reviewedBy?->name ?? 'N/A',
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('Total Submissions', $total, 'fa-file-shield', 'blue'),
                $this->kpi('Pending', $pending, 'fa-clock', 'orange'),
                $this->kpi('Reviewed in Period', $reviewedInPeriod, 'fa-clipboard-check', 'purple'),
                $this->kpi('Verified', $verified, 'fa-circle-check', 'green'),
                $this->kpi('Rejected', $rejected, 'fa-circle-xmark', 'red'),
            ],
            'chart' => $this->distributionChart([
                ['label' => __('admin.reports.kpis.pending'), 'value' => $pending, 'color' => '#F59E0B'],
                ['label' => __('admin.reports.kpis.verified'), 'value' => $verified, 'color' => '#10B981'],
                ['label' => __('admin.reports.kpis.rejected'), 'value' => $rejected, 'color' => '#EF4444'],
            ]),
            'totals' => [],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function disputesReport(string $from, string $to): array
    {
        [$start, $end] = $this->range($from, $to);
        $base = Dispute::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);

        $collection = (clone $base)->with(['booking', 'raisedBy', 'reportedWorker', 'resolvedBy'])->latest('created_at')->get();
        $total = (int) (clone $base)->count();
        $open = (int) (clone $base)->where('status', 'open')->count();
        $underReview = (int) (clone $base)->where('status', 'under_review')->count();
        $resolved = (int) (clone $base)->where('status', 'resolved')->count();
        $resolvedInPeriod = (int) Dispute::query()
            ->where('resolved_at', '>=', $start)
            ->where('resolved_at', '<', $end)
            ->count();

        $rows = $collection->map(fn (Dispute $d) => [
            'Booking Ref' => $d->booking?->booking_ref ?? 'N/A',
            'Type' => str_replace('_', ' ', ucfirst($d->type)),
            'Raised By' => $d->raisedBy->name ?? 'N/A',
            'Reported Worker' => $d->reportedWorker?->name ?? 'N/A',
            'Status' => $d->status,
            'Reason' => $d->reason,
            'Created At' => $d->created_at->format('Y-m-d H:i'),
            'Resolved At' => $d->resolved_at?->format('Y-m-d H:i'),
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('Total Raised', $total, 'fa-scale-balanced', 'blue'),
                $this->kpi('Open', $open, 'fa-folder-open', 'red'),
                $this->kpi('Under Review', $underReview, 'fa-clock', 'orange'),
                $this->kpi('Resolved in Period', $resolvedInPeriod, 'fa-circle-check', 'green'),
            ],
            'chart' => $this->distributionChart([
                ['label' => __('admin.reports.kpis.open'), 'value' => $open, 'color' => '#EF4444'],
                ['label' => __('admin.reports.kpis.under-review'), 'value' => $underReview, 'color' => '#F59E0B'],
                ['label' => __('admin.reports.kpis.resolved'), 'value' => $resolved, 'color' => '#10B981'],
            ]),
            'totals' => [],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    private function servicePopularityReport(string $from, string $to): array
    {
        [$start, $end] = $this->range($from, $to);
        $rows = Booking::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end)
            ->selectRaw('service_category,
                COUNT(*) as total,
                COALESCE(SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END), 0) as completed,
                COALESCE(SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END), 0) as cancelled,
                COALESCE(SUM(CASE WHEN status = "completed" THEN CAST(price AS DECIMAL(12,2)) ELSE 0 END), 0) as completed_value')
            ->groupBy('service_category')
            ->get();

        $buckets = [];
        foreach ($rows as $row) {
            $label = $this->categories->resolve($row->service_category);
            if (! isset($buckets[$label])) {
                $buckets[$label] = ['total' => 0, 'completed' => 0, 'cancelled' => 0, 'completed_value' => 0.0];
            }
            $buckets[$label]['total'] += (int) $row->total;
            $buckets[$label]['completed'] += (int) $row->completed;
            $buckets[$label]['cancelled'] += (int) $row->cancelled;
            $buckets[$label]['completed_value'] += (float) $row->completed_value;
        }

        uasort($buckets, fn (array $a, array $b) => $b['total'] <=> $a['total']);

        $all = collect($buckets);
        $totalBookings = (int) $all->sum('total');
        $totalCompleted = (int) $all->sum('completed');
        $totalCancelled = (int) $all->sum('cancelled');
        $totalCompletedValue = (float) $all->sum('completed_value');

        $tableRows = $all->map(fn (array $row, string $label) => [
            'Service Category' => $label,
            'Bookings' => (int) $row['total'],
            'Completed' => (int) $row['completed'],
            'Cancelled' => (int) $row['cancelled'],
            'Completed Value' => (float) $row['completed_value'],
        ])->values()->all();

        return [
            'summary' => [
                $this->kpi('Categories Tracked', $all->count(), 'fa-layer-group', 'blue'),
                $this->kpi('Total Bookings', $totalBookings, 'fa-calendar-check', 'green'),
                $this->kpi('Completed', $totalCompleted, 'fa-circle-check', 'green'),
                $this->kpi('Completed Value', $totalCompletedValue, 'fa-coins', 'green', true),
            ],
            'chart' => [
                'type' => 'bar',
                'labels' => $all->keys()->values()->all(),
                'datasets' => [[
                    'label' => __('admin.reports.kpis.total-bookings'),
                    'data' => $all->pluck('total')->map(fn ($v) => (int) $v)->values()->all(),
                    'color' => '#1A6FC4',
                ]],
            ],
            'totals' => [
                'Bookings' => $totalBookings,
                'Completed' => $totalCompleted,
                'Cancelled' => $totalCancelled,
                'Completed Value' => $totalCompletedValue,
            ],
            'rows' => $tableRows,
            'total_rows' => $all->count(),
        ];
    }

    private function reviewsReport(string $from, string $to): array
    {
        [$start, $end] = $this->range($from, $to);
        $base = Review::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);

        $collection = (clone $base)->with(['client', 'worker', 'booking'])->latest('created_at')->get();
        $total = (int) (clone $base)->count();
        $avg = (float) $base->avg('rating');
        $fiveStar = (int) (clone $base)->where('rating', 5)->count();
        $oneStar = (int) (clone $base)->where('rating', 1)->count();

        $rows = $collection->map(fn (Review $r) => [
            'Date' => $r->created_at->format('Y-m-d H:i'),
            'Client' => ($r->client->name ?? 'N/A').($r->is_anonymous ? ' (posted anonymously)' : ''),
            'Worker' => $r->worker->name ?? 'N/A',
            'Booking Ref' => $r->booking?->booking_ref ?? 'N/A',
            'Rating' => (int) $r->rating,
            'Comment' => $r->comment,
        ])->values()->all();

        $stars = [];
        for ($s = 1; $s <= 5; $s++) {
            $stars[] = ['label' => __('admin.reports.star_count', ['count' => $s]), 'value' => (int) (clone $base)->where('rating', $s)->count(), 'color' => ['#EF4444', '#F59E0B', '#FBBF24', '#A3E635', '#10B981'][$s - 1]];
        }

        return [
            'summary' => [
                $this->kpi('Total Reviews', $total, 'fa-comment', 'blue'),
                $this->kpi('Average Rating', $total ? number_format($avg, 1).' / 5' : 'N/A', 'fa-star', 'orange'),
                $this->kpi('5-Star Reviews', $fiveStar, 'fa-face-smile', 'green'),
                $this->kpi('1-Star Reviews', $oneStar, 'fa-face-frown', 'red'),
            ],
            'chart' => $this->distributionChart($stars),
            'totals' => [],
            'rows' => $rows,
            'total_rows' => $total,
        ];
    }

    // ── Helpers ────────────────────────────────────────────────

    /**
     * @return array{completed: int, cancelled: int, declined: int, basis: int, gross_value: float, tips: float}
     */
    private function outcomeTotals(Carbon $start, Carbon $end): array
    {
        $row = Booking::query()
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($query) use ($start, $end) {
                    $query->where('status', 'completed')
                        ->where('completed_at', '>=', $start)
                        ->where('completed_at', '<', $end);
                })->orWhere(function ($query) use ($start, $end) {
                    $query->where('status', 'cancelled')
                        ->where('cancelled_at', '>=', $start)
                        ->where('cancelled_at', '<', $end);
                })->orWhere(function ($query) use ($start, $end) {
                    $query->where('status', 'declined')
                        ->where('declined_at', '>=', $start)
                        ->where('declined_at', '<', $end);
                });
            })
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) as cancelled")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END), 0) as declined")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN CAST(price AS DECIMAL(12,2)) ELSE 0 END), 0) as gross_value")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'completed' THEN CAST(tip_amount AS DECIMAL(12,2)) ELSE 0 END), 0) as tips")
            ->first();

        $completed = (int) ($row->completed ?? 0);
        $cancelled = (int) ($row->cancelled ?? 0);
        $declined = (int) ($row->declined ?? 0);

        return [
            'completed' => $completed,
            'cancelled' => $cancelled,
            'declined' => $declined,
            'basis' => $completed + $cancelled + $declined,
            'gross_value' => (float) ($row->gross_value ?? 0),
            'tips' => (float) ($row->tips ?? 0),
        ];
    }

    private function percentage(int $part, int $basis): string
    {
        return $basis > 0 ? round($part / $basis * 100).'%' : '0%';
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, array{type: string, total?: bool, align?: string}>  $columns
     * @return array<int, array<string, mixed>>
     */
    private function sortRows(array $rows, array $columns, ?int $sortCol, string $sortDir): array
    {
        if ($sortCol === null) {
            return $rows;
        }

        $labels = array_keys($columns);
        $label = $labels[$sortCol] ?? null;
        if ($label === null) {
            return $rows;
        }

        $format = $columns[$label]['type'] ?? 'text';
        $indexed = [];
        foreach (array_values($rows) as $index => $row) {
            $indexed[] = ['row' => $row, 'index' => $index];
        }

        usort($indexed, function (array $a, array $b) use ($label, $format, $sortDir) {
            $aValue = $a['row'][$label] ?? null;
            $bValue = $b['row'][$label] ?? null;
            $aNull = $aValue === null || $aValue === '';
            $bNull = $bValue === null || $bValue === '';

            if ($aNull && $bNull) {
                return $a['index'] <=> $b['index'];
            }
            if ($aNull) {
                return 1;
            }
            if ($bNull) {
                return -1;
            }

            $comparison = match ($format) {
                'money', 'number', 'decimal', 'percent', 'rating' => $this->numericValue($aValue) <=> $this->numericValue($bValue),
                'date' => (int) strtotime((string) $aValue) <=> (int) strtotime((string) $bValue),
                default => strnatcasecmp((string) $aValue, (string) $bValue),
            };

            if ($comparison === 0) {
                return $a['index'] <=> $b['index'];
            }

            return $sortDir === 'desc' ? -$comparison : $comparison;
        });

        return array_column($indexed, 'row');
    }

    private function numericValue(mixed $value): float
    {
        return (float) preg_replace('/[^\d.\-]/', '', (string) $value);
    }

    private function kpi(string $label, float|int|string $value, string $icon, string $accent, bool $money = false): array
    {
        $key = 'admin.reports.kpis.'.Str::slug($label);
        if (Lang::has($key)) {
            $label = __($key);
        }

        return compact('label', 'value', 'icon', 'accent', 'money');
    }

    private function trend($query, string $dateColumn, string $from, string $to, string $metric): array
    {
        [$start, $end] = $this->range($from, $to);
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
            ->where($dateColumn, '>=', $start)
            ->where($dateColumn, '<', $end)
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

    private function trendChart(string $from, string $to, array $trend, string $revenueLabel = 'Revenue (PHP)', string $revenueColor = '#10B981', string $type = 'line', string $countLabel = 'Bookings'): array
    {
        return [
            'type' => $type,
            'labels' => $trend['labels'],
            'datasets' => [
                [
                    'label' => $countLabel,
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

    /**
     * Return a half-open range: inclusive start, exclusive next-day start.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->startOfDay()->addDay();

        if ($start->gte($end)) {
            $start = Carbon::parse($to)->startOfDay();
            $end = Carbon::parse($from)->startOfDay()->addDay();
        }

        return [$start, $end];
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
