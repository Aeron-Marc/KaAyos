@extends('layouts.admin')

@section('title', 'Dashboard')
@section('skeleton')
    <div class="sp-title-row">
        <div class="ad-sk ad-sk-title" style="width:220px;"></div>
    </div>
    <div class="sp-stats">
        <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:40px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
        <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:40px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
        <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:40px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
        <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:40px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
        <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:40px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
        <div class="ad-sk ad-sk-stat"><div class="ad-sk ad-sk-stat-circle"></div><div class="ad-sk ad-sk-title" style="width:40px;"></div><div class="ad-sk ad-sk-text-sm" style="width:100px;"></div></div>
    </div>
    <div style="display:grid;grid-template-columns:1.9fr 1fr;gap:20px;">
        <div class="ad-sk-panel">
            <div class="ad-sk ad-sk-title" style="width:150px;"></div>
            <div class="ad-sk" style="height:240px;border-radius:12px;"></div>
            <div style="height:20px;"></div>
            <div class="ad-sk ad-sk-row"></div>
            <div class="ad-sk ad-sk-row"></div>
        </div>
        <div>
            <div class="ad-sk-panel" style="height:150px;">
                <div class="ad-sk ad-sk-title" style="width:120px;"></div>
                <div class="ad-sk ad-sk-row"></div>
                <div class="ad-sk ad-sk-row"></div>
                <div class="ad-sk ad-sk-row"></div>
            </div>
            <div class="ad-sk-panel" style="height:120px;">
                <div class="ad-sk ad-sk-title" style="width:120px;"></div>
                <div class="ad-sk ad-sk-row"></div>
                <div class="ad-sk ad-sk-row"></div>
            </div>
        </div>
    </div>
@endsection
@section('content')
@php
    $statusTotal = array_sum($bookingStatusDist['values']);
@endphp
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-gauge-high"></i> Dashboard</h1>
        <p>Overview of platform activity</p>
    </div>
    <div class="header-right">
        <div class="dash-clock" id="dashClock">
            <div class="dash-clock-time" id="dashClockTime">{{ now()->format('h:i:s A') }}</div>
            <div class="dash-clock-date" id="dashClockDate">{{ now()->format('l, F j, Y') }}</div>
        </div>
    </div>
</div>

<div class="dash-kpis">
    <div class="dash-kpi">
        <div class="dash-kpi-icon icon-blue"><i class="fa-solid fa-users"></i></div>
        <div class="dash-kpi-body">
            <div class="dash-kpi-value">{{ number_format($totalUsers) }}</div>
            <div class="dash-kpi-label">Total Users</div>
            <div class="dash-kpi-sub">{{ $totalClients }} clients · {{ $totalWorkers }} workers</div>
        </div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon icon-navy"><i class="fa-solid fa-calendar-check"></i></div>
        <div class="dash-kpi-body">
            <div class="dash-kpi-value">{{ number_format($totalBookings) }}</div>
            <div class="dash-kpi-label">Total Bookings</div>
            <div class="dash-kpi-sub">{{ $completionRate }}% completed</div>
        </div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon icon-green"><i class="fa-solid fa-play"></i></div>
        <div class="dash-kpi-body">
            <div class="dash-kpi-value">{{ number_format($activeBookings) }}</div>
            <div class="dash-kpi-label">Active Bookings</div>
            <div class="dash-kpi-sub">{{ $completedBookings }} done · {{ $cancelledBookings }} cancelled</div>
        </div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon icon-orange"><i class="fa-solid fa-coins"></i></div>
        <div class="dash-kpi-body">
            <div class="dash-kpi-value">₱{{ number_format($revenueThisMonth, 2) }}</div>
            <div class="dash-kpi-label">Revenue This Month</div>
            <div class="dash-kpi-sub">₱{{ number_format($totalRevenue, 2) }} all-time</div>
        </div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon icon-purple"><i class="fa-solid fa-chart-simple"></i></div>
        <div class="dash-kpi-body">
            <div class="dash-kpi-value">₱{{ number_format((float)$avgBookingValue, 2) }}</div>
            <div class="dash-kpi-label">Avg Booking Value</div>
            <div class="dash-kpi-sub">per completed booking</div>
        </div>
    </div>
    <div class="dash-kpi">
        <div class="dash-kpi-icon icon-green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="dash-kpi-body">
            <div class="dash-kpi-value">{{ $completionRate }}%</div>
            <div class="dash-kpi-label">Completion Rate</div>
            <div class="dash-kpi-sub">of all bookings</div>
        </div>
    </div>
</div>

<div class="dash-main">
    <div class="dash-col dash-col-main">
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title"><i class="fa-solid fa-chart-column"></i> Bookings & Revenue</div>
                    <div class="dash-card-sub">Last 14 days</div>
                </div>
                <div class="chart-legend">
                    <span><i class="legend-dot" style="background:#1A6FC4"></i>Bookings</span>
                    <span><i class="legend-dot" style="background:#10B981"></i>Revenue</span>
                </div>
            </div>
            <div class="chart-wrap" style="height:200px">
                @if(array_sum($bookingTrend['counts']) > 0)
                    <canvas id="bookingTrendChart"></canvas>
                @else
                    <div class="empty-state"><div class="empty-state-icon"><i class="fa-solid fa-chart-line"></i></div><div class="empty-state-title">No bookings yet</div><div class="empty-state-subtitle">Booking activity will appear here.</div></div>
                @endif
            </div>
        </div>

        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title"><i class="fa-solid fa-calendar"></i> Recent Bookings</div>
                    <div class="dash-card-sub">Latest activity</div>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="dash-link">View all</a>
            </div>
            @if($recentBookings->count())
                <table>
                    <thead>
                        <tr><th>Client</th><th>Worker</th><th>Status</th><th>Price</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentBookings as $b)
                        <tr>
                            <td class="text-sm">{{ $b->client->name ?? 'N/A' }}</td>
                            <td class="text-sm">{{ $b->worker->name ?? 'N/A' }}</td>
                            <td><span class="status-badge status-{{ $b->status }}">{{ str_replace('_', ' ', ucfirst($b->status)) }}</span></td>
                            <td class="table-col-price">₱{{ number_format((float)$b->price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state"><div class="empty-state-icon"><i class="fa-solid fa-calendar"></i></div><div class="empty-state-title">No bookings yet</div></div>
            @endif
        </div>
    </div>

    <div class="dash-col dash-col-rail">
        <div class="dash-card" id="attentionSection">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title"><i class="fa-solid fa-triangle-exclamation"></i> Needs Attention</div>
                </div>
            </div>
            <a href="{{ route('admin.verification.index') }}" class="attention-row">
                <span class="attention-icon warning"><i class="fa-solid fa-id-card-clip"></i></span>
                <span class="attention-body">
                    <span class="attention-name">Pending Verifications</span>
                    <span class="attention-desc">Documents awaiting review</span>
                </span>
                <span class="attention-count">{{ number_format($pendingVerifications) }}</span>
            </a>
            @if($pendingTestimonials > 0)
            <a href="{{ route('admin.testimonials.index') }}?status=pending" class="attention-row">
                <span class="attention-icon" style="background:rgba(139,92,246,.12);color:#7C3AED;"><i class="fa-solid fa-quote-left"></i></span>
                <span class="attention-body">
                    <span class="attention-name">Pending Testimonials</span>
                    <span class="attention-desc">Testimonials awaiting approval</span>
                </span>
                <span class="attention-count">{{ number_format($pendingTestimonials) }}</span>
            </a>
            @endif
            <a href="{{ route('admin.disputes.index') }}" class="attention-row">
                <span class="attention-icon danger"><i class="fa-solid fa-scale-balanced"></i></span>
                <span class="attention-body">
                    <span class="attention-name">Open Disputes</span>
                    <span class="attention-desc">Cases needing follow-up</span>
                </span>
                <span class="attention-count">{{ number_format($openDisputes) }}</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="attention-row">
                <span class="attention-icon neutral"><i class="fa-solid fa-user-slash"></i></span>
                <span class="attention-body">
                    <span class="attention-name">Suspended Users</span>
                    <span class="attention-desc">Accounts currently restricted</span>
                </span>
                <span class="attention-count">{{ number_format($suspendedUsers) }}</span>
            </a>
        </div>

        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title"><i class="fa-solid fa-chart-pie"></i> Bookings by Status</div>
                    <div class="dash-card-sub">All-time split</div>
                </div>
            </div>
            <div class="donut-wrap" style="height:170px">
                @if($statusTotal > 0)
                    <canvas id="bookingStatusChart"></canvas>
                @else
                    <div class="empty-state"><div class="empty-state-icon"><i class="fa-solid fa-chart-pie"></i></div><div class="empty-state-title">No data yet</div></div>
                @endif
            </div>
        </div>

        @if(count($topCategories['labels']) > 0)
        <div class="dash-card">
            <div class="dash-card-head">
                <div>
                    <div class="dash-card-title"><i class="fa-solid fa-wrench"></i> Top Categories</div>
                    <div class="dash-card-sub">Most booked services</div>
                </div>
            </div>
            <div class="cats">
                @foreach($topCategories['labels'] as $i => $label)
                    <div class="cat-row">
                        <span class="cat-bullet"></span>
                        <span class="cat-name">{{ $label }}</span>
                        <span class="cat-count">{{ $topCategories['values'][$i] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

@push('styles')
<style>
    .header{margin-bottom:16px}
    .dash-clock{display:flex;flex-direction:column;align-items:flex-end;gap:2px;padding:12px 20px;border-radius:12px;background:linear-gradient(135deg,var(--b9),#0C447C);color:#fff;box-shadow:0 4px 14px rgba(4,44,83,.25);min-width:200px}
    .dash-clock-time{font-size:1.55rem;font-weight:800;letter-spacing:.04em;font-variant-numeric:tabular-nums;line-height:1.1}
    .dash-clock-date{font-size:.74rem;color:rgba(255,255,255,.82)}
    .dash-kpis{display:grid;grid-template-columns:repeat(6,1fr);gap:8px;margin-bottom:16px}
    .dash-kpi{display:flex;align-items:center;gap:9px;background:#fff;border-radius:10px;padding:9px 12px;border:1px solid rgba(0,0,0,.05);box-shadow:0 2px 6px rgba(0,0,0,.05);min-width:0}
    .dash-kpi-icon{width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.88rem;flex-shrink:0}
    .icon-blue{background:rgba(26,111,196,.1);color:var(--b6)}
    .icon-navy{background:rgba(11,63,120,.1);color:var(--b8)}
    .icon-green{background:rgba(16,185,129,.12);color:var(--s9)}
    .icon-orange{background:rgba(245,158,11,.14);color:var(--y8)}
    .icon-purple{background:rgba(139,92,246,.12);color:#7C3AED}
    .dash-kpi-body{min-width:0}
    .dash-kpi-value{font-size:1.05rem;font-weight:800;color:var(--b9);line-height:1.2;font-variant-numeric:tabular-nums;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .dash-kpi-label{font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--g4);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .dash-kpi-sub{font-size:.68rem;color:var(--g4);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:3px}
    .dash-main{display:grid;grid-template-columns:minmax(0,1.9fr) minmax(0,1fr);gap:18px;align-items:start}
    .dash-col{display:flex;flex-direction:column;gap:18px;min-width:0}
    .dash-card{background:#fff;border-radius:14px;padding:18px;box-shadow:0 3px 10px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05)}
    .dash-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}
    .dash-card-title{font-size:.95rem;font-weight:700;color:var(--b9);display:flex;align-items:center;gap:9px}
    .dash-card-title i{color:var(--b6);font-size:1rem}
    .dash-card-sub{font-size:.73rem;color:var(--g4);margin-top:2px}
    .dash-link{font-size:.78rem;font-weight:700;color:var(--b6);text-decoration:none}
    .dash-link:hover{text-decoration:underline}
    .dash-card table{min-width:0}
    .dash-card th,.dash-card td{padding:9px}
    .chart-legend{display:flex;gap:12px;font-size:.72rem;color:var(--g4)}
    .chart-legend span{display:inline-flex;align-items:center;gap:5px}
    .legend-dot{width:8px;height:8px;border-radius:50%;display:inline-block}
    .chart-wrap{position:relative;width:100%}
    .chart-wrap canvas{width:100%!important;height:100%!important}
    .donut-wrap{position:relative;width:100%}
    .donut-wrap canvas{width:100%!important;height:100%!important}
    .attention-row{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;text-decoration:none;color:var(--g9);transition:background .15s}
    .attention-row + .attention-row{margin-top:2px}
    .attention-row:hover{background:var(--off)}
    .attention-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
    .attention-icon.warning{background:rgba(245,158,11,.12);color:var(--y8)}
    .attention-icon.danger{background:rgba(239,68,68,.12);color:var(--d8)}
    .attention-icon.neutral{background:rgba(26,111,196,.12);color:var(--b6)}
    .attention-body{flex:1;min-width:0}
    .attention-name{display:block;font-weight:700;font-size:.84rem;color:var(--g9)}
    .attention-desc{display:block;font-size:.72rem;color:var(--g4);margin-top:1px}
    .attention-count{font-size:1.05rem;font-weight:800;color:var(--b9);font-variant-numeric:tabular-nums}
    .cats{display:flex;flex-direction:column;gap:8px}
    .cat-row{display:flex;align-items:center;gap:10px}
    .cat-bullet{width:8px;height:8px;border-radius:50%;background:var(--b6);flex-shrink:0}
    .cat-name{flex:1;min-width:0;font-size:.78rem;font-weight:600;color:var(--g7);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .cat-count{font-size:.78rem;font-weight:700;color:var(--g9);font-variant-numeric:tabular-nums}
    @media(max-width:1280px){.dash-kpis{grid-template-columns:repeat(3,1fr)}.dash-main{grid-template-columns:1fr}}
    @media(max-width:768px){.dash-kpis{grid-template-columns:repeat(2,1fr)}}
    @media(max-width:480px){.dash-kpis{grid-template-columns:1fr}.dash-card table{min-width:420px}.dash-kpi-sub{display:none}}
</style>
@endpush
@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
function initDashboardCharts() {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#6B7280';

    var el = document.getElementById('bookingTrendChart');
    if (!el) return;

    var data = {{ Js::from($bookingTrend) }};

    new Chart(el, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Bookings',
                    data: data.counts,
                    borderColor: '#1A6FC4',
                    backgroundColor: 'rgba(26,111,196,.12)',
                    fill: true,
                    tension: .35,
                    pointRadius: 2,
                    pointBackgroundColor: '#1A6FC4',
                    yAxisID: 'y'
                },
                {
                    label: 'Revenue (₱)',
                    data: data.revenues,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16,185,129,.12)',
                    fill: true,
                    tension: .35,
                    borderDash: [6,4],
                    pointRadius: 2,
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
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: function(v){ return '₱' + v; } } },
                x: { grid: { display: false } }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label === 'Revenue (₱)' ? '₱' + Number(ctx.raw).toLocaleString(undefined, {minimumFractionDigits:2}) : ctx.dataset.label + ': ' + ctx.raw;
                        }
                    }
                }
            }
        }
    });
}

function initStatusDonut() {
    var el = document.getElementById('bookingStatusChart');
    if (!el) return;

    var data = {{ Js::from($bookingStatusDist) }};

    new Chart(el, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{ data: data.values, backgroundColor: data.colors, borderWidth: 2, borderColor: '#fff' }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 12, font: { size: 11 } } }
            }
        }
    });
}

(function () {
    var sk = document.getElementById('skeletonPage');
    function init() {
        if (typeof Chart === 'undefined') return;
        initDashboardCharts();
        initStatusDonut();
        var canvases = document.querySelectorAll('canvas');
        for (var i = 0; i < canvases.length; i++) {
            var ch = Chart.getChart(canvases[i]);
            if (ch) ch.resize();
        }
    }
    if (!sk || sk.style.display === 'none') { init(); return; }
    var attempts = 0;
    var timer = setInterval(function () {
        attempts++;
        if (sk.style.display === 'none' || attempts > 50) {
            clearInterval(timer);
            init();
        }
    }, 50);
})();
</script>
<script>
(function () {
    var timeEl = document.getElementById('dashClockTime');
    var dateEl = document.getElementById('dashClockDate');
    if (!timeEl || !dateEl) return;

    var timeFmt = new Intl.DateTimeFormat('en-PH', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true, timeZone: 'Asia/Manila' });
    var dateFmt = new Intl.DateTimeFormat('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'Asia/Manila' });

    function tick() {
        var now = new Date();
        timeEl.textContent = timeFmt.format(now).toUpperCase();
        dateEl.textContent = dateFmt.format(now);
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
@endsection
