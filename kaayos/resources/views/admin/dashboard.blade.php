@extends('layouts.admin')

@section('title', __('admin.dash.title'))
@section('body_class', 'dashboard-page')
@section('skeleton')
    <div class="dash-skeleton">
        <div class="ad-sk" style="height:190px;border-radius:18px;"></div>
        <div class="dash-skeleton-kpis">
            @for($i = 0; $i < 4; $i++)
                <div class="ad-sk" style="height:150px;border-radius:14px;"></div>
            @endfor
        </div>
        <div class="dash-skeleton-main">
            <div class="ad-sk" style="border-radius:14px;"></div>
            <div class="ad-sk" style="border-radius:14px;"></div>
        </div>
    </div>
@endsection

@section('content')
@php
    $kpi = $metrics['kpis'];
    $attention = $metrics['attention'];
    $queue = $metrics['action_queue'];
    $trend = $metrics['trend'];
    $statusDist = $metrics['status_distribution'];
    $cats = $metrics['top_categories'];
    $recent = $metrics['recent_bookings'];

    $rangeQuery = [];
    if ($period['from'] !== null) {
        $rangeQuery['date_from'] = $period['from']->format('Y-m-d');
        $rangeQuery['date_to'] = $period['to']->format('Y-m-d');
    }

    $dashboardUrl = fn (array $extra = []) => route('admin.dashboard', array_merge([
        'period' => $period['key'],
        'queue_sort' => $queueSort,
    ], $extra));
    $periodUrl = fn (string $key) => route('admin.dashboard', [
        'period' => $key,
        'queue_sort' => $queueSort,
    ]);
    $queueSortUrl = fn (string $sort) => route('admin.dashboard', [
        'period' => $period['key'],
        'queue_sort' => $sort,
    ]);
    $bookingsUrl = fn (array $extra = []) => route('admin.bookings.index', array_merge($rangeQuery, $extra));
    $outcomeUrl = fn (array $extra = []) => route('admin.bookings.index', array_merge($rangeQuery, ['date_basis' => 'outcome'], $extra));
    $reportUrl = route('admin.reports.index', [
        'preset' => $period['key'] === 'all_time' ? 'all' : $period['key'],
    ]);
    $exportUrl = route('admin.dashboard.export', [
        'period' => $period['key'],
        'queue_sort' => $queueSort,
    ]);

    $periodLabel = __('admin.dash.period.'.$period['key']);

    $deltaGoodUp = [
        'total_bookings' => true,
        'completion_rate' => true,
        'cancellation_rate' => false,
        'gross_collected' => true,
    ];
    $deltaClass = function (string $key) use ($deltas, $deltaGoodUp) {
        $delta = $deltas[$key] ?? null;
        if (! $delta || ! array_key_exists($key, $deltaGoodUp) || $delta['dir'] === 'same') {
            return 'is-neutral';
        }

        return (($delta['dir'] === 'up') === $deltaGoodUp[$key]) ? 'is-good' : 'is-bad';
    };
    $deltaIcon = fn (string $key) => match ($deltas[$key]['dir'] ?? 'same') {
        'up' => 'fa-arrow-up',
        'down' => 'fa-arrow-down',
        default => 'fa-minus',
    };

    $ageLabel = function (int $minutes): string {
        if ($minutes < 60) {
            return __('admin.dash.queue.age.minutes', ['n' => max(1, $minutes)]);
        }
        if ($minutes < 1440) {
            $hours = intdiv($minutes, 60);
            $mins = $minutes % 60;

            return $mins > 0
                ? __('admin.dash.queue.age.hours_minutes', ['h' => $hours, 'm' => $mins])
                : __('admin.dash.queue.age.hours', ['n' => $hours]);
        }

        return __('admin.dash.queue.age.days', ['n' => intdiv($minutes, 1440)]);
    };

    $relTime = function ($dt) {
        $mins = (int) $dt->diffInMinutes(now());
        if ($mins < 1) {
            return __('admin.dash.time.just_now');
        }
        if ($mins < 60) {
            return __('admin.dash.time.ago_minutes', ['n' => $mins]);
        }
        if ($mins < 1440) {
            return __('admin.dash.time.ago_hours', ['n' => intdiv($mins, 60)]);
        }

        return __('admin.dash.time.ago_days', ['n' => intdiv($mins, 1440)]);
    };

    $completionTarget = $targets['completion_rate_min'] ?? null;
    $cancellationTarget = $targets['cancellation_rate_max'] ?? null;
    $completionOk = $completionTarget !== null && $kpi['completion_rate']['value'] >= $completionTarget;
    $cancellationOk = $cancellationTarget !== null && $kpi['cancellation_rate']['value'] <= $cancellationTarget;

    $actionTiles = [
        [
            'key' => 'disputes',
            'label' => __('admin.dash.attention.unresolved_disputes'),
            'desc' => __('admin.dash.attention.unresolved_disputes_desc'),
            'count' => $attention['unresolved_disputes'],
            'age' => $attention['oldest_unresolved_dispute_days'] !== null
                ? __('admin.dash.attention.oldest_open', ['days' => $attention['oldest_unresolved_dispute_days']])
                : null,
            'icon' => 'fa-scale-balanced',
            'href' => route('admin.disputes.index', ['status' => 'unresolved']),
        ],
        [
            'key' => 'verifications',
            'label' => __('admin.dash.attention.pending_workers'),
            'desc' => __('admin.dash.attention.pending_workers_desc'),
            'count' => $attention['pending_workers'],
            'age' => $attention['oldest_pending_verification_hours'] !== null
                ? __('admin.dash.attention.oldest_stuck', ['hours' => $attention['oldest_pending_verification_hours']])
                : null,
            'icon' => 'fa-id-card-clip',
            'href' => route('admin.verification.index', ['status' => 'pending']),
        ],
        [
            'key' => 'stuck',
            'label' => __('admin.dash.attention.stuck'),
            'desc' => __('admin.dash.attention.stuck_desc', ['hours' => $attention['stuck_threshold_hours']]),
            'count' => $attention['stuck_bookings'],
            'age' => $attention['oldest_stuck_hours'] !== null
                ? __('admin.dash.attention.oldest_stuck', ['hours' => $attention['oldest_stuck_hours']])
                : null,
            'icon' => 'fa-hourglass-half',
            'href' => route('admin.bookings.index', ['status' => 'stuck']),
        ],
        [
            'key' => 'unassigned',
            'label' => __('admin.dash.attention.unassigned'),
            'desc' => __('admin.dash.attention.unassigned_desc', ['hours' => $attention['unassigned_threshold_hours']]),
            'count' => $attention['unassigned_bookings'],
            'age' => $attention['oldest_unassigned_hours'] !== null
                ? __('admin.dash.attention.oldest_unassigned', ['hours' => $attention['oldest_unassigned_hours']])
                : null,
            'icon' => 'fa-user-clock',
            'href' => route('admin.bookings.index', ['status' => 'unassigned']),
        ],
    ];

    $queueTone = [
        'dispute' => 'danger',
        'verification' => 'warning',
        'stuck' => 'warning',
        'unassigned' => 'info',
    ];
    $queueIcon = [
        'dispute' => 'fa-scale-balanced',
        'verification' => 'fa-id-card-clip',
        'stuck' => 'fa-hourglass-half',
        'unassigned' => 'fa-user-clock',
    ];

    $catMax = count($cats['values']) > 0 ? max($cats['values']) : 1;
    $jsLocale = app()->getLocale() === 'fil' ? 'fil-PH' : 'en-PH';
    $currency = "\u{20B1}";
@endphp

<div class="dash-page">
    <header class="dash-head">
        <div class="dash-head-copy">
            <h1><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> {{ __('admin.dash.title') }}</h1>
            <p>{{ __('admin.dash.subtitle') }}</p>
        </div>
        <div class="dash-controls">
            <div class="dash-period-label" id="periodLabel">{{ __('admin.dash.period.label') }}</div>
            <nav class="dash-periods" aria-labelledby="periodLabel">
                @foreach(array_keys(config('dashboard.periods', [])) as $periodKey)
                    <a class="dash-period {{ $period['key'] === $periodKey ? 'is-active' : '' }}"
                       href="{{ $periodUrl($periodKey) }}"
                       @if($period['key'] === $periodKey) aria-current="page" @endif>
                        {{ __('admin.dash.period.'.$periodKey) }}
                    </a>
                @endforeach
            </nav>
            <div class="dash-tool-row">
                <a class="dash-refresh" href="{{ $dashboardUrl() }}">
                    <i class="fa-solid fa-rotate" aria-hidden="true"></i>
                    {{ __('admin.dash.updated_at', ['time' => $updatedAt->translatedFormat('g:i A')]) }}
                </a>
                <a class="dash-report-link" href="{{ $reportUrl }}">
                    <i class="fa-solid fa-file-lines" aria-hidden="true"></i>
                    {{ __('admin.dash.performance.report') }}
                </a>
            </div>
        </div>
    </header>

    <section class="priority-card" id="actionQueue" aria-labelledby="priority-heading">
        <div class="priority-head">
            <div class="priority-copy">
                <span class="scope-pill scope-live">
                    <span class="scope-dot" aria-hidden="true"></span>
                    {{ __('admin.dash.scope.live') }}
                </span>
                <h2 id="priority-heading">{{ __('admin.dash.priority.title') }}</h2>
                <p>{{ __('admin.dash.priority.sub') }}</p>
            </div>
            <div class="priority-score">
                <div class="score-number">{{ number_format($attention['total_items']) }}</div>
                <div class="score-label">{{ $attention['total_items'] === 1 ? __('admin.dash.priority.item') : __('admin.dash.priority.items') }}</div>
                <div class="score-meta">
                    <span class="score-target">{{ __('admin.dash.priority.target') }}</span>
                    <span class="score-status {{ $attention['total_items'] === 0 ? 'is-ok' : 'is-bad' }}">
                        {{ $attention['total_items'] === 0 ? __('admin.dash.priority.on_target') : __('admin.dash.priority.off_target') }}
                    </span>
                </div>
                @if($queue['oldest_minutes'] !== null)
                    <div class="score-oldest">{{ __('admin.dash.priority.oldest', ['age' => $ageLabel($queue['oldest_minutes'])]) }}</div>
                @endif
            </div>
        </div>

        <div class="action-tiles">
            @foreach($actionTiles as $tile)
                <a href="{{ $tile['href'] }}" class="action-tile {{ $tile['count'] === 0 ? 'is-clear' : '' }}">
                    <span class="action-tile-icon tone-{{ $tile['key'] }}" aria-hidden="true"><i class="fa-solid {{ $tile['icon'] }}"></i></span>
                    <span class="action-tile-body">
                        <span class="action-tile-top">
                            <span class="action-tile-count">{{ number_format($tile['count']) }}</span>
                            <span class="action-tile-label">{{ $tile['label'] }}</span>
                        </span>
                        <span class="action-tile-meta">{{ $tile['count'] > 0 && $tile['age'] ? $tile['age'] : $tile['desc'] }}</span>
                    </span>
                    <i class="fa-solid fa-arrow-right action-tile-arrow" aria-hidden="true"></i>
                </a>
            @endforeach
        </div>

        <div class="priority-queue">
            <div class="queue-head">
                <div>
                    <h3>{{ __('admin.dash.priority.queue_title') }}</h3>
                    <p>{{ __('admin.dash.priority.queue_sub') }}</p>
                </div>
                <div class="queue-head-actions">
                    <span class="queue-count">
                        @if($queue['shown'] < $queue['total'])
                            {{ __('admin.dash.priority.showing', ['shown' => $queue['shown'], 'total' => number_format($queue['total'])]) }}
                        @else
                            {{ __('admin.dash.priority.all_shown', ['total' => number_format($queue['total'])]) }}
                        @endif
                    </span>
                    @if($queue['shown'] > 0)
                        <a class="queue-export" href="{{ $exportUrl }}">
                            <i class="fa-solid fa-download" aria-hidden="true"></i>
                            {{ __('admin.dash.priority.export') }}
                        </a>
                    @endif
                </div>
            </div>

            @if($queue['shown'] > 0)
                <div class="table-scroll">
                    <table class="queue-table">
                        <caption class="visually-hidden">{{ __('admin.dash.priority.queue_title') }}</caption>
                        <thead>
                            <tr>
                                <th scope="col" aria-sort="{{ $queueSort === 'priority' ? 'ascending' : 'none' }}">
                                    <a class="queue-sort" href="{{ $queueSortUrl('priority') }}">
                                        {{ __('admin.dash.queue.columns.priority') }}
                                        @if($queueSort === 'priority')<i class="fa-solid fa-arrow-down" aria-hidden="true"></i>@endif
                                    </a>
                                </th>
                                <th scope="col" aria-sort="{{ $queueSort === 'type' ? 'ascending' : 'none' }}">
                                    <a class="queue-sort" href="{{ $queueSortUrl('type') }}">
                                        {{ __('admin.dash.queue.columns.type') }}
                                        @if($queueSort === 'type')<i class="fa-solid fa-arrow-down" aria-hidden="true"></i>@endif
                                    </a>
                                </th>
                                <th scope="col">{{ __('admin.dash.queue.columns.item') }}</th>
                                <th scope="col" aria-sort="{{ $queueSort === 'age' ? 'descending' : 'none' }}">
                                    <a class="queue-sort" href="{{ $queueSortUrl('age') }}">
                                        {{ __('admin.dash.queue.columns.age') }}
                                        @if($queueSort === 'age')<i class="fa-solid fa-arrow-down" aria-hidden="true"></i>@endif
                                    </a>
                                </th>
                                <th scope="col">{{ __('admin.dash.queue.columns.status') }}</th>
                                <th scope="col">{{ __('admin.dash.queue.columns.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($queue['items'] as $item)
                                <tr>
                                    <td><span class="priority-badge" title="{{ __('admin.dash.queue.columns.priority') }}">{{ $item['priority'] }}</span></td>
                                    <td>
                                        <span class="queue-type tone-{{ $queueTone[$item['type']] ?? 'info' }}">
                                            <i class="fa-solid {{ $queueIcon[$item['type']] ?? 'fa-inbox' }}" aria-hidden="true"></i>
                                            {{ $item['type_label'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <a class="queue-item" href="{{ $item['href'] }}">
                                            <span class="queue-item-title">{{ $item['title'] }}</span>
                                            <span class="queue-item-context">{{ $item['context'] }}</span>
                                        </a>
                                    </td>
                                    <td><span class="queue-age">{{ $ageLabel($item['age_minutes']) }}</span></td>
                                    <td><x-status-badge :status="$item['status']" /></td>
                                    <td>
                                        <a class="queue-action" href="{{ $item['href'] }}">
                                            {{ __('admin.dash.queue.open') }}
                                            <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="queue-empty" role="status">
                    <span class="queue-empty-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
                    <div>
                        <strong>{{ __('admin.dash.priority.empty') }}</strong>
                        <p>{{ __('admin.dash.priority.empty_sub') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section class="performance-section" aria-labelledby="performance-heading">
        <div class="section-head">
            <div>
                <span class="scope-pill scope-performance">{{ __('admin.dash.scope.performance', ['period' => $periodLabel]) }}</span>
                <h2 id="performance-heading">{{ __('admin.dash.performance.title', ['period' => $periodLabel]) }}</h2>
                <p>{{ __('admin.dash.scope.performance_sub') }}</p>
            </div>
            <a class="section-link" href="{{ $reportUrl }}">
                {{ __('admin.dash.performance.report') }}
                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="perf-kpis">
            <a class="perf-card tone-green" href="{{ $outcomeUrl(['status' => 'completed']) }}">
                <div class="perf-card-top">
                    <span class="perf-icon" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
                    <span class="target-chip {{ $completionOk ? 'is-ok' : 'is-bad' }}">
                        {{ $completionTarget !== null ? __('admin.dash.kpi.target_min', ['value' => $completionTarget]) : __('admin.dash.kpi.no_target') }}
                    </span>
                </div>
                <div class="perf-label">{{ __('admin.dash.kpi.completion') }}</div>
                <div class="perf-value-row">
                    <span class="perf-value">{{ $kpi['completion_rate']['value'] }}%</span>
                    @if($deltas['completion_rate'] ?? null)
                        <span class="dash-delta {{ $deltaClass('completion_rate') }}">
                            <i class="fa-solid {{ $deltaIcon('completion_rate') }}" aria-hidden="true"></i>{{ $deltas['completion_rate']['text'] }}
                        </span>
                    @endif
                </div>
                <div class="perf-meta">
                    {{ __('admin.dash.kpi.completed_terminal', [
                        'count' => number_format($kpi['completion_rate']['completed']),
                        'basis' => number_format($kpi['completion_rate']['basis']),
                    ]) }}
                </div>
                <div class="perf-footer">
                    <span>{{ __('admin.dash.kpi.vs_previous') }}</span>
                    <span class="perf-link">{{ __('admin.dash.kpi.view_records') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>

            <a class="perf-card tone-red" href="{{ $outcomeUrl(['status' => 'cancelled']) }}">
                <div class="perf-card-top">
                    <span class="perf-icon" aria-hidden="true"><i class="fa-solid fa-circle-xmark"></i></span>
                    <span class="target-chip {{ $cancellationOk ? 'is-ok' : 'is-bad' }}">
                        {{ $cancellationTarget !== null ? __('admin.dash.kpi.target_max', ['value' => $cancellationTarget]) : __('admin.dash.kpi.no_target') }}
                    </span>
                </div>
                <div class="perf-label">{{ __('admin.dash.kpi.cancellation') }}</div>
                <div class="perf-value-row">
                    <span class="perf-value">{{ $kpi['cancellation_rate']['value'] }}%</span>
                    @if($deltas['cancellation_rate'] ?? null)
                        <span class="dash-delta {{ $deltaClass('cancellation_rate') }}">
                            <i class="fa-solid {{ $deltaIcon('cancellation_rate') }}" aria-hidden="true"></i>{{ $deltas['cancellation_rate']['text'] }}
                        </span>
                    @endif
                </div>
                <div class="perf-meta">
                    {{ __('admin.dash.kpi.completed_terminal', [
                        'count' => number_format($kpi['cancellation_rate']['cancelled']),
                        'basis' => number_format($kpi['cancellation_rate']['basis']),
                    ]) }}
                </div>
                <div class="perf-footer">
                    <span>{{ __('admin.dash.kpi.vs_previous') }}</span>
                    <span class="perf-link">{{ __('admin.dash.kpi.view_records') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>

            <a class="perf-card tone-blue" href="{{ $bookingsUrl() }}">
                <div class="perf-card-top">
                    <span class="perf-icon" aria-hidden="true"><i class="fa-solid fa-calendar-check"></i></span>
                    <span class="target-chip is-neutral">{{ $periodLabel }}</span>
                </div>
                <div class="perf-label">{{ __('admin.dash.kpi.total_bookings') }}</div>
                <div class="perf-value-row">
                    <span class="perf-value">{{ number_format($kpi['total_bookings']) }}</span>
                    @if($deltas['total_bookings'] ?? null)
                        <span class="dash-delta {{ $deltaClass('total_bookings') }}">
                            <i class="fa-solid {{ $deltaIcon('total_bookings') }}" aria-hidden="true"></i>{{ $deltas['total_bookings']['text'] }}
                        </span>
                    @endif
                </div>
                <div class="perf-meta">
                    {{ __('admin.dash.kpi.completed_outcomes', ['count' => number_format($kpi['completed'])]) }}
                    <span aria-hidden="true">&middot;</span>
                    {{ __('admin.dash.kpi.cancelled_outcomes', ['count' => number_format($kpi['cancelled'])]) }}
                </div>
                <div class="perf-footer">
                    <span>{{ __('admin.dash.kpi.created_period') }}</span>
                    <span class="perf-link">{{ __('admin.dash.kpi.view_records') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>

            <a class="perf-card tone-purple" href="{{ $outcomeUrl(['status' => 'completed']) }}">
                <div class="perf-card-top">
                    <span class="perf-icon" aria-hidden="true"><i class="fa-solid fa-sack-dollar"></i></span>
                    <span class="target-chip is-neutral">{{ __('admin.dash.kpi.no_target') }}</span>
                </div>
                <div class="perf-label">{{ __('admin.dash.kpi.gross_collected') }}</div>
                <div class="perf-value-row">
                    <x-money class="perf-value perf-money" :value="$kpi['gross_collected']['value']" />
                    @if($deltas['gross_collected'] ?? null)
                        <span class="dash-delta {{ $deltaClass('gross_collected') }}">
                            <i class="fa-solid {{ $deltaIcon('gross_collected') }}" aria-hidden="true"></i>{{ $deltas['gross_collected']['text'] }}
                        </span>
                    @endif
                </div>
                <div class="perf-meta">
                    {{ __('admin.dash.performance.gross_note', [
                        'count' => number_format($kpi['revenue']['basis']),
                        'tips' => number_format($kpi['tips']['value'], 2),
                    ]) }}
                </div>
                <div class="perf-footer">
                    <span>{{ __('admin.dash.kpi.gross_formula') }}</span>
                    <span class="perf-link">{{ __('admin.dash.kpi.view_records') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
                </div>
            </a>
        </div>

        <div class="perf-grid">
            <section class="dash-card chart-card" aria-labelledby="trend-heading">
                <div class="dash-card-head">
                    <div>
                        <h3 class="dash-card-title" id="trend-heading"><i class="fa-solid fa-chart-column" aria-hidden="true"></i> {{ __('admin.dash.performance.trend_title') }}</h3>
                        <p class="dash-card-sub">{{ __('admin.dash.performance.trend_sub') }}</p>
                    </div>
                    <div class="chart-legend" aria-hidden="true">
                        <span><i class="legend-dot" style="background:#1A6FC4"></i>{{ __('admin.dash.chart.bookings_legend') }}</span>
                        <span><i class="legend-dot" style="background:#10B981"></i>{{ __('admin.dash.chart.revenue_legend') }}</span>
                    </div>
                </div>
                <div class="chart-wrap">
                    @if(array_sum($trend['counts']) > 0 || array_sum($trend['revenues']) > 0)
                        <canvas id="bookingTrendChart" role="img" aria-label="{{ __('admin.dash.chart.trend_aria') }}"></canvas>
                        <div class="visually-hidden">
                            <table>
                                <caption>{{ __('admin.dash.chart.trend_data_caption') }}</caption>
                                <thead><tr><th scope="col">{{ __('admin.dash.table.date') }}</th><th scope="col">{{ __('admin.dash.chart.bookings_legend') }}</th><th scope="col">{{ __('admin.dash.chart.revenue_legend') }}</th></tr></thead>
                                <tbody>
                                    @foreach($trend['labels'] as $i => $label)
                                        <tr><th scope="row">{{ $label }}</th><td>{{ $trend['counts'][$i] }}</td><td>{{ number_format($trend['revenues'][$i], 2) }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <x-empty-state icon="fa-chart-line" :title="__('admin.dash.chart.empty_title')" :subtitle="__('admin.dash.chart.empty_subtitle', ['from' => $period['from']?->translatedFormat('M j, Y') ?? $periodLabel, 'to' => $period['to']->translatedFormat('M j, Y')])">
                            <div class="empty-switches">
                                @if($period['key'] !== '30d')
                                    <a class="btn btn-secondary" href="{{ $periodUrl('30d') }}">{{ __('admin.dash.chart.empty_switch_30d') }}</a>
                                @endif
                                @if($period['key'] !== 'all_time')
                                    <a class="btn btn-secondary" href="{{ $periodUrl('all_time') }}">{{ __('admin.dash.chart.empty_switch_all') }}</a>
                                @endif
                            </div>
                        </x-empty-state>
                    @endif
                </div>
            </section>

            <section class="dash-card status-card" aria-labelledby="status-heading">
                <div class="dash-card-head">
                    <div>
                        <h3 class="dash-card-title" id="status-heading"><i class="fa-solid fa-chart-simple" aria-hidden="true"></i> {{ __('admin.dash.performance.status_title') }}</h3>
                        <p class="dash-card-sub">{{ __('admin.dash.performance.status_sub') }}</p>
                    </div>
                </div>

                @if($statusDist['total'] > 0)
                    <ul class="status-bars">
                        @foreach($statusDist['keys'] as $i => $key)
                            <li>
                                <a class="status-bar-row" href="{{ $bookingsUrl(['status' => $key]) }}">
                                    <span class="status-bar-head">
                                        <span class="status-dot" style="background:{{ $statusDist['colors'][$i] }}" aria-hidden="true"></span>
                                        <span class="status-name">{{ $statusDist['labels'][$i] }}</span>
                                        <span class="status-value">{{ number_format($statusDist['values'][$i]) }} <small>{{ $statusDist['percentages'][$i] }}%</small></span>
                                    </span>
                                    <span class="status-track" aria-hidden="true">
                                        <span class="status-fill" style="width:{{ max($statusDist['values'][$i] > 0 ? 4 : 0, $statusDist['percentages'][$i]) }}%;background:{{ $statusDist['colors'][$i] }}"></span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-empty-state icon="fa-chart-simple" :title="__('admin.dash.chart.status_empty')" />
                @endif
            </section>
        </div>
    </section>

    <section class="support-section" aria-labelledby="supporting-heading">
        <div class="section-head section-head-compact">
            <div>
                <h2 id="supporting-heading">{{ __('admin.dash.performance.supporting_title') }}</h2>
                <p>{{ __('admin.dash.performance.supporting_sub') }}</p>
            </div>
        </div>
        <div class="support-grid">
            <a class="support-item" href="{{ route('admin.bookings.index', ['status' => 'active']) }}">
                <span class="support-icon tone-blue" aria-hidden="true"><i class="fa-solid fa-play"></i></span>
                <span class="support-body">
                    <span class="support-value">{{ number_format($kpi['active_now']['value']) }}</span>
                    <span class="support-label">{{ __('admin.dash.kpi.active') }}</span>
                    <span class="support-sub">{{ __('admin.dash.kpi.current') }}</span>
                </span>
            </a>
            <a class="support-item" href="{{ route('admin.bookings.index', ['status' => 'new']) }}">
                <span class="support-icon tone-orange" aria-hidden="true"><i class="fa-solid fa-inbox"></i></span>
                <span class="support-body">
                    <span class="support-value">{{ number_format($kpi['awaiting_acceptance']['value']) }}</span>
                    <span class="support-label">{{ __('admin.dash.kpi.awaiting') }}</span>
                    <span class="support-sub">{{ __('admin.dash.kpi.current') }}</span>
                </span>
            </a>
            <a class="support-item" href="{{ route('admin.users.index') }}">
                <span class="support-icon tone-blue" aria-hidden="true"><i class="fa-solid fa-users"></i></span>
                <span class="support-body">
                    <span class="support-value">{{ number_format($kpi['users']['total']) }}</span>
                    <span class="support-label">{{ __('admin.dash.supporting.total_users') }}</span>
                    <span class="support-sub">{{ __('admin.dash.supporting.clients', ['count' => $kpi['users']['clients']]) }} &middot; {{ __('admin.dash.supporting.workers', ['count' => $kpi['users']['workers']]) }}</span>
                </span>
            </a>
            <a class="support-item" href="{{ $outcomeUrl(['status' => 'completed']) }}">
                <span class="support-icon tone-purple" aria-hidden="true"><i class="fa-solid fa-chart-simple"></i></span>
                <span class="support-body">
                    <x-money class="support-value" :value="$kpi['avg_value']['value']" />
                    <span class="support-label">{{ __('admin.dash.supporting.avg_value') }}</span>
                    <span class="support-sub">{{ __('admin.dash.supporting.avg_value_sub') }}</span>
                </span>
            </a>
            <a class="support-item" href="{{ route('admin.reports.index', ['preset' => 'this_month']) }}">
                <span class="support-icon tone-orange" aria-hidden="true"><i class="fa-solid fa-calendar-days"></i></span>
                <span class="support-body">
                    <x-money class="support-value" :value="$metrics['revenue_this_month']" />
                    <span class="support-label">{{ __('admin.dash.supporting.month_revenue') }}</span>
                    <span class="support-sub">{{ __('admin.dash.supporting.month_revenue_sub') }}</span>
                </span>
            </a>
            <a class="support-item" href="{{ route('admin.reports.index', ['preset' => 'all']) }}">
                <span class="support-icon tone-green" aria-hidden="true"><i class="fa-solid fa-sack-dollar"></i></span>
                <span class="support-body">
                    <x-money class="support-value" :value="$kpi['all_time_revenue'] + $kpi['all_time_tips']" />
                    <span class="support-label">{{ __('admin.dash.supporting.all_time_gross') }}</span>
                    <span class="support-sub">{{ __('admin.dash.supporting.all_time_gross_sub') }}</span>
                </span>
            </a>
        </div>
    </section>

    <div class="dash-bottom">
        <section class="dash-card recent-card" aria-labelledby="recent-heading">
            <div class="dash-card-head">
                <div>
                    <h3 class="dash-card-title" id="recent-heading"><i class="fa-solid fa-calendar" aria-hidden="true"></i> {{ __('admin.dash.table.recent_title') }}</h3>
                    <p class="dash-card-sub">{{ __('admin.dash.performance.recent_sub') }}</p>
                </div>
                <a href="{{ $bookingsUrl() }}" class="section-link">{{ __('admin.dash.table.view_all') }}</a>
            </div>
            @if($recent->count())
                <div class="table-scroll">
                    <table class="recent-table">
                        <caption class="visually-hidden">{{ __('admin.dash.table.recent_caption') }}</caption>
                        <thead>
                            <tr>
                                <th scope="col">{{ __('admin.dash.table.date') }}</th>
                                <th scope="col">{{ __('admin.dash.table.client') }}</th>
                                <th scope="col">{{ __('admin.dash.table.worker') }}</th>
                                <th scope="col">{{ __('admin.dash.table.category') }}</th>
                                <th scope="col">{{ __('admin.dash.table.status') }}</th>
                                <th scope="col" class="th-price">{{ __('admin.dash.table.price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent as $booking)
                                <tr>
                                    <td>
                                        <a class="dash-booking-link" href="{{ route('admin.bookings.show', $booking->id) }}" title="{{ $booking->created_at->toDayDateTimeString() }}">
                                            <span class="booking-id">#{{ $booking->id }}</span>
                                            <span class="booking-date"><x-datetime :value="$booking->created_at" format="M j, Y" /></span>
                                            <span class="booking-rel">{{ $relTime($booking->created_at) }}</span>
                                        </a>
                                    </td>
                                    <td class="text-sm">{{ $booking->client->name ?? __('admin.common.not_available') }}</td>
                                    <td class="text-sm">{{ $booking->worker->name ?? __('admin.common.not_available') }}</td>
                                    <td class="text-sm cat-cell" title="{{ $booking->service_category }}">{{ $booking->service_category ?: __('admin.dash.categories.uncategorized') }}</td>
                                    <td><x-status-badge :status="$booking->status" /></td>
                                    <td class="td-price"><x-money :value="$booking->price" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="dash-table-foot">
                    <span>{{ __('admin.dash.table.showing', ['shown' => $recent->count(), 'total' => number_format($metrics['recent_total'])]) }}</span>
                    <a href="{{ $bookingsUrl() }}" class="section-link">{{ __('admin.dash.table.view_all') }}</a>
                </div>
            @else
                <x-empty-state icon="fa-calendar" :title="__('admin.dash.table.empty')" :subtitle="__('admin.dash.table.empty_sub')" />
            @endif
        </section>

        <section class="dash-card categories-card" aria-labelledby="categories-heading">
            <div class="dash-card-head">
                <div>
                    <h3 class="dash-card-title" id="categories-heading"><i class="fa-solid fa-wrench" aria-hidden="true"></i> {{ __('admin.dash.categories.title') }}</h3>
                    <p class="dash-card-sub">{{ __('admin.dash.performance.categories_sub') }}</p>
                </div>
            </div>
            @if(count($cats['labels']) > 0)
                <div class="cats">
                    @foreach($cats['labels'] as $i => $label)
                        <div class="cat-row">
                            <div class="cat-main">
                                <span class="cat-name">{{ $label }}</span>
                                <span class="cat-meta">{{ number_format($cats['values'][$i]) }} &middot; {{ $cats['percentages'][$i] }}%</span>
                            </div>
                            <div class="cat-bar" aria-hidden="true"><div class="cat-bar-fill" style="width:{{ max(4, (int) round(($cats['values'][$i] / $catMax) * 100)) }}%"></div></div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state icon="fa-wrench" :title="__('admin.dash.categories.title')" />
            @endif
        </section>
    </div>
</div>

@push('styles')
<style>
    .dashboard-page .main-content{padding:28px 32px 40px;min-width:0}
    .dash-page{max-width:1680px;margin:0 auto;display:flex;flex-direction:column;gap:22px}
    .dash-head{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;flex-wrap:wrap}
    .dash-head-copy h1{font-size:1.85rem;line-height:1.15;margin:0 0 6px;color:var(--b9);display:flex;align-items:center;gap:11px}
    .dash-head-copy h1 i{font-size:1.5rem;color:var(--b6)}
    .dash-head-copy p{margin:0;color:var(--g4);font-size:.95rem;max-width:720px}
    .dash-controls{display:flex;flex-direction:column;align-items:flex-end;gap:8px}
    .dash-period-label{font-size:.68rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--g4)}
    .dash-periods{display:inline-flex;background:#fff;border:1px solid rgba(0,0,0,.07);border-radius:12px;padding:4px;gap:2px;box-shadow:0 3px 10px rgba(0,0,0,.05);flex-wrap:wrap}
    .dash-period{display:inline-block;padding:8px 13px;border-radius:9px;font-size:.8rem;font-weight:700;color:var(--g7);text-decoration:none;transition:all .15s;white-space:nowrap}
    .dash-period:hover{color:var(--b7);background:var(--b0)}
    .dash-period.is-active{background:var(--b6);color:#fff;box-shadow:0 3px 8px rgba(26,111,196,.3)}
    .dash-tool-row{display:flex;align-items:center;gap:8px;flex-wrap:wrap;justify-content:flex-end}
    .dash-refresh,.dash-report-link{display:inline-flex;align-items:center;gap:6px;font-size:.76rem;font-weight:600;color:var(--g7);text-decoration:none;padding:6px 10px;border-radius:8px;background:#fff;border:1px solid rgba(0,0,0,.06);transition:all .15s}
    .dash-refresh:hover,.dash-report-link:hover{color:var(--b6);border-color:var(--b4)}
    .dash-refresh i,.dash-report-link i{font-size:.72rem}
    .dash-report-link{color:var(--b7);background:var(--b0);border-color:transparent}

    .scope-pill{display:inline-flex;align-items:center;gap:7px;padding:4px 9px;border-radius:999px;font-size:.68rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
    .scope-live{background:rgba(239,68,68,.1);color:#B91C1C}
    .scope-dot{width:7px;height:7px;border-radius:50%;background:#EF4444;box-shadow:0 0 0 3px rgba(239,68,68,.14)}
    .scope-performance{background:var(--b0);color:var(--b7)}

    .priority-card{background:#fff;border:1px solid #FED7AA;border-radius:18px;overflow:hidden;box-shadow:0 12px 34px rgba(124,45,18,.08)}
    .priority-head{display:flex;align-items:flex-start;justify-content:space-between;gap:24px;padding:22px 24px 18px;background:linear-gradient(135deg,#FFF7ED 0%,#FFFDF8 55%,#F8FAFC 100%);border-bottom:1px solid #FDE8CE}
    .priority-copy{max-width:860px}
    .priority-copy h2{margin:9px 0 5px;font-size:1.5rem;line-height:1.2;color:var(--b9)}
    .priority-copy p{margin:0;color:var(--g7);font-size:.88rem;line-height:1.5}
    .priority-score{text-align:right;flex-shrink:0;min-width:190px}
    .score-number{font-size:2.7rem;line-height:1;font-weight:800;color:var(--b9);font-variant-numeric:tabular-nums}
    .score-label{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--g4);margin-top:2px}
    .score-meta{display:flex;justify-content:flex-end;align-items:center;gap:6px;margin-top:8px;flex-wrap:wrap}
    .score-target,.score-status{font-size:.68rem;font-weight:800;padding:3px 7px;border-radius:6px}
    .score-target{background:#fff;color:var(--g7);border:1px solid #FED7AA}
    .score-status.is-ok{background:rgba(16,185,129,.12);color:#047857}
    .score-status.is-bad{background:rgba(239,68,68,.12);color:#B91C1C}
    .score-oldest{font-size:.72rem;color:var(--g4);margin-top:5px}

    .action-tiles{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding:14px 18px;border-bottom:1px solid var(--g1)}
    .action-tile{display:flex;align-items:center;gap:11px;padding:12px;border:1px solid var(--g1);border-radius:12px;background:#fff;text-decoration:none;color:var(--g9);transition:all .16s;min-width:0}
    .action-tile:hover{border-color:#FDBA74;box-shadow:0 6px 16px rgba(124,45,18,.08);transform:translateY(-1px)}
    .action-tile.is-clear{opacity:.76}
    .action-tile-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.95rem}
    .action-tile-icon.tone-disputes{background:rgba(239,68,68,.1);color:#B91C1C}
    .action-tile-icon.tone-verifications{background:rgba(245,158,11,.14);color:#B45309}
    .action-tile-icon.tone-stuck{background:rgba(245,158,11,.14);color:#B45309}
    .action-tile-icon.tone-unassigned{background:rgba(26,111,196,.1);color:var(--b7)}
    .action-tile-body{min-width:0;flex:1}
    .action-tile-top{display:flex;align-items:baseline;gap:6px;min-width:0}
    .action-tile-count{font-size:1.28rem;font-weight:800;color:var(--b9);line-height:1;font-variant-numeric:tabular-nums}
    .action-tile-label{font-size:.76rem;font-weight:700;color:var(--g7);line-height:1.15;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
    .action-tile-meta{display:-webkit-box;margin-top:4px;font-size:.7rem;color:var(--g4);line-height:1.25;overflow:hidden;-webkit-line-clamp:2;-webkit-box-orient:vertical}
    .action-tile-arrow{margin-left:auto;color:var(--g4);font-size:.7rem;flex-shrink:0}

    .priority-queue{padding:16px 18px 18px}
    .queue-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:10px}
    .queue-head h3{font-size:1rem;color:var(--b9);margin:0}
    .queue-head p{font-size:.76rem;color:var(--g4);margin:3px 0 0;max-width:760px}
    .queue-head-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;justify-content:flex-end}
    .queue-count{font-size:.72rem;color:var(--g4);white-space:nowrap}
    .queue-export{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:8px;background:var(--b0);color:var(--b7);font-size:.74rem;font-weight:700;text-decoration:none;white-space:nowrap}
    .queue-export:hover{background:var(--b1)}
    .table-scroll{overflow-x:auto}
    .queue-table{width:100%;border-collapse:collapse;min-width:780px}
    .queue-table th{padding:8px 10px;background:var(--off);border-bottom:1px solid var(--g1);text-align:left;font-size:.67rem;font-weight:800;color:var(--g4);letter-spacing:.06em;text-transform:uppercase}
    .queue-table td{padding:10px;border-bottom:1px solid var(--g1);font-size:.8rem;color:var(--g9);vertical-align:middle}
    .queue-table tbody tr:last-child td{border-bottom:0}
    .queue-table tbody tr:hover{background:rgba(26,111,196,.025)}
    .queue-sort{display:inline-flex;align-items:center;gap:4px;color:inherit;text-decoration:none}
    .queue-sort:hover{color:var(--b6)}
    .queue-sort i{font-size:.58rem}
    .priority-badge{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:6px;background:var(--off);color:var(--g7);font-size:.7rem;font-weight:800;font-variant-numeric:tabular-nums}
    .queue-type{display:inline-flex;align-items:center;gap:6px;font-size:.73rem;font-weight:700;white-space:nowrap}
    .queue-type.tone-danger{color:#B91C1C}
    .queue-type.tone-warning{color:#B45309}
    .queue-type.tone-info{color:var(--b7)}
    .queue-item{display:flex;flex-direction:column;gap:2px;text-decoration:none;color:inherit;min-width:180px}
    .queue-item-title{font-weight:700;color:var(--b8);font-size:.82rem}
    .queue-item:hover .queue-item-title{text-decoration:underline}
    .queue-item-context{font-size:.72rem;color:var(--g4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:380px}
    .queue-age{font-size:.78rem;font-weight:700;color:var(--g7);white-space:nowrap;font-variant-numeric:tabular-nums}
    .queue-action{display:inline-flex;align-items:center;gap:5px;font-size:.74rem;font-weight:700;color:var(--b6);text-decoration:none;white-space:nowrap}
    .queue-action:hover{text-decoration:underline}
    .queue-action i{font-size:.62rem}
    .queue-empty{display:flex;align-items:center;gap:12px;padding:18px;border-radius:12px;background:rgba(16,185,129,.06);border:1px solid rgba(16,185,129,.18)}
    .queue-empty-icon{width:38px;height:38px;border-radius:50%;background:rgba(16,185,129,.13);color:#047857;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
    .queue-empty strong{display:block;color:#047857;font-size:.9rem}
    .queue-empty p{margin:2px 0 0;color:var(--g7);font-size:.76rem}

    .performance-section{display:flex;flex-direction:column;gap:14px}
    .section-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;flex-wrap:wrap}
    .section-head h2{font-size:1.28rem;color:var(--b9);margin:7px 0 3px}
    .section-head p{font-size:.82rem;color:var(--g4);margin:0}
    .section-head-compact h2{margin-top:0}
    .section-link{display:inline-flex;align-items:center;gap:6px;font-size:.8rem;font-weight:700;color:var(--b6);text-decoration:none;white-space:nowrap;padding-top:4px}
    .section-link:hover{text-decoration:underline}
    .section-link i{font-size:.65rem}

    .perf-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .perf-card{display:flex;flex-direction:column;background:#fff;border:1px solid rgba(0,0,0,.06);border-radius:15px;padding:16px 16px 14px;text-decoration:none;color:var(--g9);box-shadow:0 4px 14px rgba(0,0,0,.05);transition:all .18s;min-width:0;position:relative;overflow:hidden}
    .perf-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--b6)}
    .perf-card.tone-green::before{background:#10B981}
    .perf-card.tone-red::before{background:#EF4444}
    .perf-card.tone-blue::before{background:#1A6FC4}
    .perf-card.tone-purple::before{background:#8B5CF6}
    .perf-card:hover{transform:translateY(-2px);box-shadow:0 12px 26px rgba(0,0,0,.09);border-color:rgba(26,111,196,.2)}
    .perf-card-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
    .perf-icon{width:31px;height:31px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.84rem;background:var(--off);color:var(--g7)}
    .tone-green .perf-icon{background:rgba(16,185,129,.11);color:#047857}
    .tone-red .perf-icon{background:rgba(239,68,68,.1);color:#B91C1C}
    .tone-blue .perf-icon{background:rgba(26,111,196,.1);color:var(--b7)}
    .tone-purple .perf-icon{background:rgba(139,92,246,.11);color:#6D28D9}
    .target-chip{font-size:.65rem;font-weight:800;border-radius:6px;padding:3px 7px;white-space:nowrap}
    .target-chip.is-ok{background:rgba(16,185,129,.11);color:#047857}
    .target-chip.is-bad{background:rgba(239,68,68,.11);color:#B91C1C}
    .target-chip.is-neutral{background:var(--off);color:var(--g4)}
    .perf-label{font-size:.72rem;font-weight:800;letter-spacing:.055em;text-transform:uppercase;color:var(--g4)}
    .perf-value-row{display:flex;align-items:baseline;gap:7px;flex-wrap:wrap;margin-top:3px}
    .perf-value{font-size:2rem;line-height:1.08;font-weight:800;color:var(--b9);font-variant-numeric:tabular-nums;white-space:nowrap}
    .perf-money{font-size:1.72rem}
    .perf-meta{font-size:.75rem;color:var(--g7);margin-top:6px;line-height:1.35}
    .perf-footer{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;border-top:1px solid var(--g1);padding-top:8px;margin-top:auto;padding-top:10px;font-size:.68rem;color:var(--g4);line-height:1.35}
    .perf-link{color:var(--b6);font-weight:700;white-space:nowrap}
    .dash-delta{font-size:.68rem;font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
    .dash-delta i{font-size:.58rem;margin-right:2px}
    .dash-delta.is-good{color:#047857}
    .dash-delta.is-bad{color:#B91C1C}
    .dash-delta.is-neutral{color:var(--g4)}

    .perf-grid{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(0,1fr);gap:14px;align-items:stretch}
    .dash-card{background:#fff;border-radius:15px;padding:18px;border:1px solid rgba(0,0,0,.055);box-shadow:0 4px 14px rgba(0,0,0,.05);min-width:0}
    .dash-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}
    .dash-card-title{font-size:1rem;font-weight:800;color:var(--b9);display:flex;align-items:center;gap:8px;margin:0}
    .dash-card-title i{color:var(--b6);font-size:.95rem}
    .dash-card-sub{font-size:.75rem;color:var(--g4);margin:4px 0 0;line-height:1.4}
    .chart-wrap{position:relative;width:100%;height:300px}
    .chart-wrap canvas{width:100%!important;height:100%!important}
    .chart-legend{display:flex;gap:12px;font-size:.72rem;color:var(--g4);flex-wrap:wrap;justify-content:flex-end}
    .chart-legend span{display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
    .legend-dot{width:8px;height:8px;border-radius:50%;display:inline-block}
    .status-card{display:flex;flex-direction:column}
    .status-bars{list-style:none;display:flex;flex-direction:column;gap:8px;margin:0;padding:0}
    .status-bar-row{display:block;padding:5px 6px;border-radius:8px;text-decoration:none;color:inherit;transition:background .15s}
    .status-bar-row:hover{background:var(--off)}
    .status-bar-head{display:flex;align-items:center;gap:7px;font-size:.77rem;margin-bottom:4px;min-width:0}
    .status-dot{width:8px;height:8px;border-radius:50%;display:inline-block;flex-shrink:0}
    .status-name{color:var(--g7);font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .status-value{margin-left:auto;color:var(--g7);font-weight:800;font-variant-numeric:tabular-nums;white-space:nowrap}
    .status-value small{color:var(--g4);font-weight:600;margin-left:3px}
    .status-track{display:block;height:6px;border-radius:4px;background:var(--off);overflow:hidden}
    .status-fill{display:block;height:100%;border-radius:4px;min-width:0}
    .empty-switches{display:flex;gap:8px;justify-content:center;margin-top:12px;flex-wrap:wrap}
    .empty-switches .btn{font-size:.78rem;padding:6px 12px;text-decoration:none}
    .status-card .empty-state,.chart-card .empty-state{padding:40px 20px}
    .status-card .empty-state-icon,.chart-card .empty-state-icon{font-size:2rem;margin-bottom:10px}

    .support-section{display:flex;flex-direction:column;gap:10px}
    .support-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
    .support-item{display:flex;align-items:center;gap:9px;background:#fff;border:1px solid rgba(0,0,0,.055);border-radius:12px;padding:11px 12px;text-decoration:none;color:inherit;box-shadow:0 2px 8px rgba(0,0,0,.035);min-width:0;transition:all .16s}
    .support-item:hover{border-color:var(--b2);box-shadow:0 6px 16px rgba(0,0,0,.06)}
    .support-icon{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.8rem}
    .support-icon.tone-blue{background:rgba(26,111,196,.1);color:var(--b7)}
    .support-icon.tone-orange{background:rgba(245,158,11,.13);color:#B45309}
    .support-icon.tone-purple{background:rgba(139,92,246,.11);color:#6D28D9}
    .support-icon.tone-green{background:rgba(16,185,129,.11);color:#047857}
    .support-body{min-width:0}
    .support-value{display:block;font-size:1.02rem;font-weight:800;color:var(--b9);line-height:1.1;font-variant-numeric:tabular-nums;white-space:nowrap}
    .support-label{display:block;font-size:.66rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--g4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
    .support-sub{display:block;font-size:.68rem;color:var(--g4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px}

    .dash-bottom{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(0,1fr);gap:14px;align-items:start}
    .recent-table{width:100%;border-collapse:collapse;min-width:620px}
    .recent-table th{padding:8px;background:var(--off);border-bottom:1px solid var(--g1);text-align:left;font-size:.65rem;font-weight:800;color:var(--g4);letter-spacing:.05em;text-transform:uppercase}
    .recent-table td{padding:10px 8px;border-bottom:1px solid var(--g1);font-size:.78rem;color:var(--g9);vertical-align:middle}
    .recent-table tbody tr:last-child td{border-bottom:0}
    .recent-table tbody tr:hover{background:rgba(26,111,196,.025)}
    .th-price,.td-price{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
    .cat-cell{max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--g4)}
    .dash-booking-link{display:flex;flex-direction:column;gap:1px;text-decoration:none;color:inherit}
    .dash-booking-link:hover .booking-id{color:var(--b6);text-decoration:underline}
    .booking-id{font-weight:700;color:var(--b7);font-size:.78rem}
    .booking-date{font-size:.76rem;color:var(--g9)}
    .booking-rel{font-size:.66rem;color:var(--g4)}
    .dash-table-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:10px;font-size:.73rem;color:var(--g4)}
    .cats{display:flex;flex-direction:column;gap:11px}
    .cat-row{display:flex;flex-direction:column;gap:4px}
    .cat-main{display:flex;align-items:baseline;justify-content:space-between;gap:10px}
    .cat-name{min-width:0;font-size:.8rem;font-weight:600;color:var(--g7);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .cat-meta{font-size:.73rem;font-weight:700;color:var(--g4);font-variant-numeric:tabular-nums;white-space:nowrap}
    .cat-bar{height:6px;border-radius:3px;background:var(--off);overflow:hidden}
    .cat-bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--b6),var(--b4))}

    .dash-skeleton{display:flex;flex-direction:column;gap:14px}
    .dash-skeleton-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .dash-skeleton-main{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(0,1fr);gap:14px}
    .dash-skeleton-main .ad-sk{height:300px}

    @media(max-width:1500px){
        .action-tiles{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media(max-width:1400px){
        .support-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
    }
    @media(max-width:1150px){
        .perf-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
        .perf-grid,.dash-bottom{grid-template-columns:1fr}
        .chart-wrap{height:280px}
        .action-tiles{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media(max-width:900px){
        .dashboard-page .main-content{padding:22px 18px 32px}
        .dash-head{align-items:flex-start}
        .dash-controls{align-items:flex-start;width:100%}
        .dash-tool-row{justify-content:flex-start}
        .priority-head{flex-direction:column;gap:14px}
        .priority-score{text-align:left;min-width:0;display:flex;flex-wrap:wrap;align-items:baseline;gap:8px}
        .score-number{font-size:2.2rem}
        .score-meta{margin-top:0}
        .score-oldest{width:100%;margin-top:0}
        .queue-head{flex-direction:column}
        .queue-head-actions{justify-content:flex-start}
        .support-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media(max-width:640px){
        .dash-head-copy h1{font-size:1.5rem}
        .dash-periods{width:100%}
        .dash-period{flex:1;text-align:center;padding:8px 6px;font-size:.74rem}
        .action-tiles{grid-template-columns:1fr}
        .perf-kpis{grid-template-columns:1fr}
        .support-grid{grid-template-columns:1fr}
        .chart-legend{justify-content:flex-start}
        .chart-wrap{height:250px}
        .priority-head,.priority-queue{padding-left:14px;padding-right:14px}
        .action-tiles{padding-left:14px;padding-right:14px}
        .dash-card,.perf-card{border-radius:12px}
        .perf-value{font-size:1.8rem}
    }
    @media(max-width:768px){
        .dashboard-page .main-content{padding:72px 20px 32px}
    }
    @media print{
        .dashboard-page .main-content{padding:0}
        .dash-periods,.dash-tool-row,.queue-export,.section-link,.action-tile-arrow{display:none!important}
        .priority-card,.dash-card,.perf-card,.support-item{box-shadow:none}
        .dash-page{gap:14px}
        .queue-table,.recent-table{min-width:0}
        .table-scroll{overflow:visible}
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
(function () {
    var currency = @json($currency);
    var jsLocale = @json($jsLocale);

    function initDashboardChart() {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
        Chart.defaults.color = '#6B7280';

        var el = document.getElementById('bookingTrendChart');
        if (!el) return;

        var trend = @json($trend);

        new Chart(el, {
            type: 'bar',
            data: {
                labels: trend.labels,
                datasets: [
                    {
                        type: 'bar',
                        label: @json(__('admin.dash.chart.bookings_legend')),
                        data: trend.counts,
                        backgroundColor: 'rgba(26,111,196,.62)',
                        hoverBackgroundColor: '#1A6FC4',
                        borderRadius: 4,
                        maxBarThickness: 26,
                        yAxisID: 'y'
                    },
                    {
                        type: 'line',
                        label: @json(__('admin.dash.chart.revenue_legend')),
                        data: trend.revenues,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16,185,129,.12)',
                        fill: false,
                        tension: .35,
                        pointRadius: 2,
                        pointHoverRadius: 4,
                        pointBackgroundColor: '#10B981',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,.04)' },
                        ticks: { precision: 0 }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function (v) {
                                return currency + Number(v).toLocaleString(jsLocale);
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 10 }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ctx.dataset.label === @json(__('admin.dash.chart.revenue_legend'))
                                    ? currency + Number(ctx.raw).toLocaleString(jsLocale, { minimumFractionDigits: 2 })
                                    : ctx.dataset.label + ': ' + ctx.raw;
                            }
                        }
                    }
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboardChart);
    } else {
        initDashboardChart();
    }
})();
</script>
@endpush
@endsection
