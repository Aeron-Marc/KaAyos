@extends('layouts.admin')

@section('title', 'Reports')
@section('content')
@php
    $routeWith = function (array $params) { return route('admin.reports.index', array_filter($params)); };
    $presetLink = fn ($key) => $routeWith(['type' => $type, 'preset' => $key]);
    $reportLink = fn ($key) => $preset
        ? $routeWith(['type' => $key, 'preset' => $preset])
        : $routeWith(['type' => $key, 'date_from' => $from, 'date_to' => $to]);
    $exportParams = ['type' => $type, 'date_from' => $from, 'date_to' => $to];
    $moneyCols = ['Price', 'Total Value', 'Gross Amount', 'Platform Fee', 'Net Amount', 'Gross Revenue', 'Revenue'];
    $statusCols = ['new', 'accepted', 'en_route', 'in_progress', 'completed', 'cancelled'];
@endphp
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-chart-column"></i> Reports</h1>
        <p>Build, preview and export platform activity reports</p>
    </div>
    @if($type && $preview)
    <div class="header-right">
        <a href="{{ route('admin.reports.print', $exportParams) }}" id="printReportBtn" class="btn btn-secondary">
            <i class="fa-solid fa-print"></i> Print
        </a>
        <div class="rpt-export">
            <button type="button" class="btn btn-success" id="exportToggle">
                <i class="fa-solid fa-download"></i> Export
                <i class="fa-solid fa-chevron-down" style="font-size:.7rem"></i>
            </button>
            <div class="rpt-export-menu" id="exportMenu">
                <a href="{{ route('admin.reports.export', $exportParams + ['format' => 'csv']) }}">
                    <i class="fa-solid fa-file-csv"></i>
                    <span class="rpt-export-label">
                        <strong>CSV</strong>
                        <small>Excel & Google Sheets compatible</small>
                    </span>
                </a>
                <a href="{{ route('admin.reports.export', $exportParams + ['format' => 'xlsx']) }}">
                    <i class="fa-solid fa-file-excel"></i>
                    <span class="rpt-export-label">
                        <strong>Excel (XLSX)</strong>
                        <small>Formatted spreadsheet</small>
                    </span>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ── Date range bar ─────────────────────────────────────── --}}
<div class="rpt-section-label">
    <i class="fa-solid fa-calendar-range"></i> Date Range
</div>
<div class="rpt-bar">
    <form method="GET" action="{{ route('admin.reports.index') }}" id="reportForm" class="rpt-bar-form">
        <input type="hidden" name="type" value="{{ $type ?? '' }}">
        <div class="rpt-presets">
            @foreach($presets as $key => $label)
                <a href="{{ $presetLink($key) }}" class="rpt-chip {{ $preset === $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <div class="rpt-range">
            <div class="rpt-range-field">
                <label for="date_from">From</label>
                <input type="date" id="date_from" name="date_from" value="{{ $from }}">
            </div>
            <div class="rpt-range-field">
                <label for="date_to">To</label>
                <input type="date" id="date_to" name="date_to" value="{{ $to }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter"></i> Apply</button>
        </div>
    </form>
</div>

{{-- ── Report picker ──────────────────────────────────────── --}}
<div class="rpt-section-label">
    <i class="fa-solid fa-layer-group"></i> Report Type
</div>
<div class="rpt-section">
    @foreach($groups as $gKey => $group)
    <div class="rpt-group">
        <div class="rpt-group-head">
            <span class="rpt-group-icon"><i class="fa-solid {{ $group['icon'] }}"></i></span>
            <h3>{{ $group['label'] }} Reports</h3>
        </div>
        <div class="rpt-grid">
            @foreach($group['reports'] as $rKey => $report)
            <a href="{{ $reportLink($rKey) }}" class="rpt-card {{ $type === $rKey ? 'active' : '' }}">
                <div class="rpt-card-top">
                    <span class="rpt-card-icon"><i class="fa-solid {{ $report['icon'] }}"></i></span>
                    @if($type === $rKey)
                        <span class="rpt-card-selected"><i class="fa-solid fa-circle-check"></i></span>
                    @endif
                </div>
                <div class="rpt-card-title">{{ $report['label'] }}</div>
                <div class="rpt-card-desc">{{ $report['description'] }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach
</div>

<div class="rpt-divider"></div>

{{-- ── Results ────────────────────────────────────────────── --}}
@if($type && $preview)
<div class="rpt-results">
    @if(count($preview['summary']))
    <div class="metrics-grid">
        @foreach($preview['summary'] as $kpi)
        <div class="metric-card accent-{{ $kpi['accent'] }}">
            <div class="metric-label"><i class="fa-solid {{ $kpi['icon'] }}" style="margin-right:6px"></i>{{ $kpi['label'] }}</div>
            <div class="metric-value">
                @if($kpi['money'])₱{{ number_format((float)$kpi['value'], 2) }}
                @else{{ $kpi['value'] }}@endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if(!empty($preview['chart']) && !empty($preview['chart']['labels']))
    <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fa-solid fa-chart-line"></i> {{ $meta['label'] }} Overview</div>
        <div style="height:250px;position:relative">
            <canvas id="reportChart"></canvas>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="rpt-table-head">
            <div class="card-title" style="margin-bottom:0"><i class="fa-solid fa-table"></i> {{ $meta['label'] }} Details</div>
            <a href="{{ route('admin.reports.export', $exportParams + ['format' => 'xlsx']) }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-file-excel"></i> Export Excel
            </a>
        </div>
        @if($preview['total_rows'] > count($preview['rows']))
        <div class="alert alert-info" style="margin-top:16px;margin-bottom:0">
            <i class="fa-solid fa-circle-info"></i>
            Showing the first {{ number_format(count($preview['rows'])) }} of {{ number_format($preview['total_rows']) }} records. Use the export button for the full dataset.
        </div>
        @endif
        @if(count($preview['rows']))
        <div class="table-container" style="margin-top:16px">
            <table>
                <thead>
                    <tr>
                        @foreach($preview['columns'] as $col)<th>{{ $col }}</th>@endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview['rows'] as $row)
                    <tr>
                        @foreach($preview['columns'] as $col)
                            @php $value = $row[$col] ?? null; @endphp
                            <td class="text-sm">
                                @if($col === 'Status' && in_array($value, $statusCols, true))
                                    <span class="status-badge status-{{ $value }}">{{ str_replace('_', ' ', ucfirst($value)) }}</span>
                                @elseif(in_array($col, $moneyCols, true) && $value !== null)
                                    ₱{{ number_format((float)$value, 2) }}
                                @elseif($value === null || $value === '')
                                    <span class="text-muted">—</span>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-icon"><i class="fa-solid fa-file-circle-exclamation"></i></div>
            <div class="empty-state-title">No data found</div>
            <div class="empty-state-subtitle">No records match the selected period. Try a wider date range.</div>
        </div>
        @endif
    </div>
</div>
@else
<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fa-solid fa-chart-pie"></i></div>
        <div class="empty-state-title">Build a report</div>
        <div class="empty-state-subtitle">Pick a report type above, choose a date range, then preview it here. Export to CSV or Excel when you're ready.</div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
    .rpt-bar{background:#fff;border-radius:14px;padding:14px 18px;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.05);margin-bottom:16px}
    .rpt-bar-form{display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;justify-content:space-between}
    .rpt-presets{display:flex;flex-wrap:wrap;gap:8px}
    .rpt-chip{display:inline-flex;align-items:center;padding:8px 14px;border-radius:999px;border:1.5px solid var(--g1);color:var(--g7);font-size:.82rem;font-weight:600;text-decoration:none;transition:all .18s;white-space:nowrap}
    .rpt-chip:hover{border-color:var(--b4);color:var(--b6);background:var(--b0)}
    .rpt-chip.active{background:var(--b6);border-color:var(--b6);color:#fff}
    .rpt-range{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap}
    .rpt-range-field{display:flex;flex-direction:column;gap:4px}
    .rpt-range-field label{font-size:.72rem;font-weight:700;color:var(--g4);text-transform:uppercase;letter-spacing:.04em}
    .rpt-range-field input{padding:8px 12px;border:1.5px solid var(--g1);border-radius:8px;font-size:.88rem;font-family:'Inter',sans-serif;color:var(--g9);outline:none;transition:border-color .18s}
    .rpt-range-field input:focus{border-color:var(--b4);box-shadow:0 0 0 3px rgba(26,111,196,.1)}

    .rpt-section{display:grid;grid-template-columns:repeat(auto-fit,minmax(500px,1fr));gap:20px;margin-bottom:16px}
    .rpt-section-label{display:flex;align-items:center;gap:8px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--g4);margin-bottom:12px}
    .rpt-section-label i{color:var(--b6)}
    .rpt-divider{height:1px;background:linear-gradient(90deg,rgba(4,44,83,.18),rgba(196,206,216,.35),rgba(255,255,255,0));margin:4px 0 24px}
    .rpt-group-head{display:flex;align-items:center;gap:10px;margin-bottom:10px}
    .rpt-group-icon{width:28px;height:28px;border-radius:8px;background:rgba(26,111,196,.1);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:.85rem}
    .rpt-group-head h3{font-size:.95rem;font-weight:700;color:var(--b9);margin:0}
    .rpt-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:12px}
    .rpt-card{background:#fff;border-radius:14px;padding:14px;border:1px solid rgba(0,0,0,.06);box-shadow:0 2px 8px rgba(0,0,0,.05);text-decoration:none;transition:all .2s;display:flex;flex-direction:column;gap:5px;position:relative}
    .rpt-card:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(0,0,0,.1);border-color:rgba(26,111,196,.35)}
    .rpt-card.active{border-color:var(--b6);background:linear-gradient(180deg,#fff,var(--b0));box-shadow:0 6px 18px rgba(26,111,196,.18)}
    .rpt-card.active::before{content:'';position:absolute;top:0;left:18px;right:18px;height:3px;border-radius:0 0 4px 4px;background:var(--b6)}
    .rpt-card-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:2px}
    .rpt-card-icon{width:32px;height:32px;border-radius:9px;background:rgba(26,111,196,.1);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:.95rem}
    .rpt-card-selected{color:var(--s9);font-size:1.05rem}
    .rpt-card-title{font-size:.88rem;font-weight:700;color:var(--b9)}
    .rpt-card-desc{font-size:.78rem;color:var(--g4);line-height:1.45}

    .rpt-export{position:relative}
    .rpt-export-menu{position:absolute;right:0;top:calc(100% + 8px);min-width:250px;background:#fff;border-radius:12px;box-shadow:0 12px 32px rgba(0,0,0,.16);border:1px solid rgba(0,0,0,.06);overflow:hidden;display:none;z-index:50}
    .rpt-export-menu.open{display:block}
    .rpt-export-menu a{display:flex;align-items:center;gap:12px;padding:12px 16px;text-decoration:none;color:var(--g9);transition:background .15s;border-bottom:1px solid var(--g1)}
    .rpt-export-menu a:last-child{border-bottom:none}
    .rpt-export-menu a:hover{background:var(--off)}
    .rpt-export-menu a i{font-size:1.15rem;width:22px;text-align:center;color:var(--s8)}
    .rpt-export-menu a:nth-child(2) i{color:#1D6F42}
    .rpt-export-label{display:flex;flex-direction:column}
    .rpt-export-label strong{font-size:.9rem}
    .rpt-export-label small{font-size:.74rem;color:var(--g4)}

    .rpt-table-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .rpt-results .metrics-grid{margin-bottom:24px}
    @media(max-width:768px){
        .rpt-bar-form{flex-direction:column;align-items:stretch}
        .rpt-range{width:100%}
        .rpt-range-field{flex:1}
        .rpt-section{grid-template-columns:1fr}
        .rpt-grid{grid-template-columns:1fr}
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
function initReportChart() {
    var el = document.getElementById('reportChart');
    var data = @js($preview['chart'] ?? null);
    if (!el || !data || !data.labels || !data.labels.length || typeof Chart === 'undefined') return;

    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = '#6B7280';

    var isDonut = data.type === 'doughnut';
    var isLine = data.type === 'line';

    var datasets = data.datasets.map(function (ds) {
        if (isDonut) {
            return { data: ds.data, backgroundColor: ds.colors, borderWidth: 2, borderColor: '#fff' };
        }
        return {
            label: ds.label,
            data: ds.data,
            borderColor: ds.color,
            backgroundColor: isLine ? ds.color + '26' : ds.color,
            borderWidth: 2,
            fill: isLine,
            tension: isLine ? .35 : 0,
            pointRadius: isLine ? 2 : 0
        };
    });

    var options = { responsive: true, maintainAspectRatio: false };

    if (isDonut) {
        options.cutout = '68%';
        options.plugins = {
            legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 12, font: { size: 11 } } }
        };
    } else {
        options.scales = {
            x: { grid: { display: false } },
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } }
        };
        options.plugins = { legend: { display: false } };
    }

    new Chart(el, { type: isDonut ? 'doughnut' : data.type, data: { labels: data.labels, datasets: datasets }, options: options });
}

(function () {
    var toggle = document.getElementById('exportToggle');
    var menu = document.getElementById('exportMenu');
    if (toggle && menu) {
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            menu.classList.toggle('open');
        });
        document.addEventListener('click', function (e) {
            if (menu && !menu.contains(e.target) && e.target !== toggle) menu.classList.remove('open');
        });
    }

    var printBtn = document.getElementById('printReportBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.open(printBtn.getAttribute('href'), 'kaayosReportPrint');
        });
    }

    var sk = document.getElementById('skeletonPage');
    function init() {
        initReportChart();
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
@endpush
