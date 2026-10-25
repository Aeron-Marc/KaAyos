@extends('layouts.admin')

@section('title', __('admin.reports.title'))
@section('content')
@php
    $routeWith = function (array $params) { return route('admin.reports.index', array_filter($params)); };
    $presetLink = fn ($key) => $routeWith(['type' => $type, 'preset' => $key]);
    $reportLink = fn ($key) => $preset
        ? $routeWith(['type' => $key, 'preset' => $preset])
        : $routeWith(['type' => $key, 'date_from' => $from, 'date_to' => $to]);
    $exportParams = array_filter([
        'type' => $type,
        'date_from' => $from,
        'date_to' => $to,
        'sort_col' => $sortCol,
        'sort_dir' => $sortCol !== null ? $sortDir : null,
    ], fn ($value) => $value !== null && $value !== '');
    $printParams = $exportParams + array_filter([
        'preset' => $preset,
    ], fn ($value) => $value !== null && $value !== '');
    $columnMeta = is_array($preview) ? ($preview['column_meta'] ?? []) : [];
    $alignClass = fn (string $col) => ($columnMeta[$col]['align'] ?? null) === 'right' ? 'text-right' : '';
@endphp
<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-chart-column" aria-hidden="true"></i> {{ __('admin.reports.title') }}</h1>
        <p>{{ __('admin.reports.subtitle') }}</p>
    </div>
    @if($type && $preview && !$errors->any())
    <div class="header-right">
        <a href="{{ route('admin.reports.print', $printParams) }}" id="printPreviewBtn" class="btn btn-secondary" target="_blank" rel="noopener">
            <i class="fa-solid fa-eye" aria-hidden="true"></i> {{ __('admin.reports.print_preview') }}
        </a>
        <a href="{{ route('admin.reports.print', $printParams + ['autoprint' => 1]) }}" id="printReportBtn" class="btn btn-primary" target="_blank" rel="noopener">
            <i class="fa-solid fa-print" aria-hidden="true"></i> {{ __('admin.reports.print_now') }}
        </a>
        <div class="rpt-export">
            <button type="button" class="btn btn-success" id="exportToggle" aria-expanded="false" aria-controls="exportMenu">
                <i class="fa-solid fa-download" aria-hidden="true"></i> {{ __('admin.reports.export') }}
                <i class="fa-solid fa-chevron-down" style="font-size:.7rem" aria-hidden="true"></i>
            </button>
            <div class="rpt-export-menu" id="exportMenu" role="menu" aria-labelledby="exportToggle">
                <a href="{{ route('admin.reports.export', $exportParams + ['format' => 'csv']) }}" role="menuitem">
                    <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
                    <span class="rpt-export-label">
                        <strong>CSV</strong>
                        <small>{{ __('admin.reports.csv_hint') }}</small>
                    </span>
                </a>
                <a href="{{ route('admin.reports.export', $exportParams + ['format' => 'xlsx']) }}" role="menuitem">
                    <i class="fa-solid fa-file-excel" aria-hidden="true"></i>
                    <span class="rpt-export-label">
                        <strong>Excel (XLSX)</strong>
                        <small>{{ __('admin.reports.xlsx_hint') }}</small>
                    </span>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

@if($errors->any())
<div class="alert alert-danger" role="alert">
    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
    {{ $errors->first() }}
</div>
@endif

{{-- ── Date range bar ─────────────────────────────────────── --}}
<div class="rpt-section-label">
    <i class="fa-solid fa-calendar-range" aria-hidden="true"></i> {{ __('admin.reports.date_range') }}
</div>
<div class="rpt-bar">
    <form method="GET" action="{{ route('admin.reports.index') }}" id="reportForm" class="rpt-bar-form">
        <input type="hidden" name="type" value="{{ old('type', $type ?? '') }}">
        <div class="rpt-presets">
            @foreach($presets as $key => $label)
                <a href="{{ $presetLink($key) }}" class="rpt-chip {{ $preset === $key ? 'active' : '' }}"
                   @if($preset === $key) aria-current="true" @endif>
                    {{ $label }}
                </a>
            @endforeach
        </div>
        <div class="rpt-range">
            <div class="rpt-range-field">
                <label for="date_from">{{ __('admin.reports.from') }}</label>
                <input type="date" id="date_from" name="date_from" value="{{ old('date_from', $from) }}">
            </div>
            <div class="rpt-range-field">
                <label for="date_to">{{ __('admin.reports.to') }}</label>
                <input type="date" id="date_to" name="date_to" value="{{ old('date_to', $to) }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-filter" aria-hidden="true"></i> {{ __('admin.reports.apply') }}</button>
            @if($type)
            <a href="{{ route('admin.reports.index', ['type' => $type]) }}" class="btn btn-secondary btn-sm">{{ __('admin.reports.reset_dates') }}</a>
            @endif
        </div>
    </form>
</div>

{{-- ── Report picker ──────────────────────────────────────── --}}
<details class="rpt-picker" @if(!$type) open @endif>
    <summary class="rpt-section-label rpt-picker-summary">
        <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
        <span>{{ __('admin.reports.report_type') }}</span>
        <span class="rpt-picker-current">{{ $meta['label'] ?? __('admin.reports.choose_report') }}</span>
        <i class="fa-solid fa-chevron-down rpt-picker-chevron" aria-hidden="true"></i>
    </summary>
    <div class="rpt-section">
        @foreach($groups as $gKey => $group)
        <div class="rpt-group">
            <div class="rpt-group-head">
                <span class="rpt-group-icon"><i class="fa-solid {{ $group['icon'] }}" aria-hidden="true"></i></span>
                <h3>{{ $group['label'] }}</h3>
            </div>
            <div class="rpt-grid">
                @foreach($group['reports'] as $rKey => $report)
                <a href="{{ $reportLink($rKey) }}" class="rpt-card {{ $type === $rKey ? 'active' : '' }}"
                   @if($type === $rKey) aria-current="true" @endif>
                    <div class="rpt-card-top">
                        <span class="rpt-card-icon"><i class="fa-solid {{ $report['icon'] }}" aria-hidden="true"></i></span>
                        @if($type === $rKey)
                            <span class="rpt-card-selected"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
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
</details>

<div class="rpt-divider"></div>

{{-- ── Results ────────────────────────────────────────────── --}}
@if($errors->any())
{{-- The validation alert above already explains why results are hidden. --}}
@elseif($type && $preview)
<div class="rpt-results">
    <div class="rpt-results-head">
        <div class="rpt-results-title">{{ $meta['label'] }}</div>
        <div class="rpt-results-desc">{{ $meta['description'] }}</div>
        <div class="rpt-results-meta">
            <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $from }} &ndash; {{ $to }}</span>
            @if($sortLabel)
            <span><i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i> {{ __('admin.reports.sorted_by') }} {{ $sortLabel }} ({{ __('admin.reports.sort_'.$sortDir) }})</span>
            @endif
            <span><i class="fa-solid fa-table-list" aria-hidden="true"></i> {{ number_format($preview['total_rows']) }} {{ __('admin.reports.records') }}</span>
        </div>
        @if($definition)
        <div class="rpt-definition"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> {{ $definition }}</div>
        @endif
    </div>

    @if(count($preview['summary']))
    <div class="metrics-grid">
        @foreach($preview['summary'] as $kpi)
        <div class="metric-card accent-{{ $kpi['accent'] }}">
            <div class="metric-label"><i class="fa-solid {{ $kpi['icon'] }}" style="margin-right:6px" aria-hidden="true"></i>{{ $kpi['label'] }}</div>
            <div class="metric-value">
                @if($kpi['money'])&#8369;{{ number_format((float)$kpi['value'], 2) }}
                @else{{ $kpi['value'] }}@endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if(!empty($preview['chart']) && !empty($preview['chart']['labels']))
    <div class="card" style="margin-bottom:20px">
        <div class="card-title"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> {{ $meta['label'] }} {{ __('admin.reports.overview') }}</div>
        <div style="height:250px;position:relative">
            <canvas id="reportChart" role="img" aria-label="{{ __('admin.reports.chart_aria', ['report' => $meta['label']]) }}"></canvas>
        </div>
        <details class="rpt-chart-data">
            <summary>{{ __('admin.dash.chart.data_table') }}</summary>
            <div class="table-container">
                <table>
                    <caption>{{ __('admin.reports.chart_aria', ['report' => $meta['label']]) }}</caption>
                    <thead>
                        <tr>
                            <th scope="col">{{ __('admin.reports.chart_label') }}</th>
                            @foreach($preview['chart']['datasets'] as $dataset)
                            <th scope="col">{{ $dataset['label'] ?? __('admin.reports.chart_value') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($preview['chart']['labels'] as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            @foreach($preview['chart']['datasets'] as $dataset)
                            <td>{{ $dataset['data'][$i] ?? '' }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>
    @endif

    <div class="card">
        <div class="rpt-table-head">
            <div class="card-title" style="margin-bottom:0"><i class="fa-solid fa-table" aria-hidden="true"></i> {{ $meta['label'] }} {{ __('admin.reports.details') }}</div>
        </div>
        @if($preview['total_rows'] > count($preview['rows']))
        <div class="alert alert-info" style="margin-top:16px;margin-bottom:0">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            @if($sortLabel)
                {{ __('admin.reports.showing_sorted', ['shown' => number_format(count($preview['rows'])), 'total' => number_format($preview['total_rows']), 'sort' => $sortLabel, 'direction' => __('admin.reports.sort_'.$sortDir)]) }}
            @else
                {{ __('admin.reports.showing_first', ['shown' => number_format(count($preview['rows'])), 'total' => number_format($preview['total_rows'])]) }}
            @endif
        </div>
        @endif
        @if(count($preview['rows']))
        <div class="table-container" style="margin-top:16px">
            <table>
                <caption class="visually-hidden">{{ __('admin.reports.caption', ['report' => $meta['label']]) }}</caption>
                <thead>
                    <tr>
                        @foreach($preview['columns'] as $col)
                            @php
                                $activeSort = $sortCol === $loop->index;
                                $nextDir = $activeSort && $sortDir === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <th scope="col" class="{{ $alignClass($col) }}" aria-sort="{{ $activeSort ? ($sortDir === 'asc' ? 'ascending' : 'descending') : 'none' }}">
                                <a class="rpt-sort-link {{ $activeSort ? 'active' : '' }}" href="{{ request()->fullUrlWithQuery(['sort_col' => $loop->index, 'sort_dir' => $nextDir]) }}">
                                    <span>{{ $col }}</span>
                                    <i class="fa-solid {{ $activeSort ? ($sortDir === 'asc' ? 'fa-arrow-up-short-wide' : 'fa-arrow-down-wide-short') : 'fa-sort' }}" aria-hidden="true"></i>
                                </a>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($preview['rows'] as $row)
                    <tr>
                        @foreach($preview['columns'] as $col)
                            @php
                                $value = $row[$col] ?? null;
                                $cellMeta = $columnMeta[$col] ?? ['type' => 'text'];
                            @endphp
                            <td class="text-sm {{ $alignClass($col) }}">
                                @if($value === null || $value === '')
                                    <span class="text-muted">&mdash;</span>
                                @elseif(($cellMeta['type'] ?? 'text') === 'status')
                                    <x-status-badge :status="$value" />
                                @elseif(($cellMeta['type'] ?? 'text') === 'money')
                                    <x-money :value="$value" />
                                @elseif(($cellMeta['type'] ?? 'text') === 'rating')
                                    {{ (int) $value }} / 5
                                @elseif(($cellMeta['type'] ?? 'text') === 'number')
                                    {{ number_format((float) $value) }}
                                @elseif(($cellMeta['type'] ?? 'text') === 'decimal')
                                    {{ number_format((float) $value, 1) }}
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
                @if(!empty($preview['totals']))
                <tfoot>
                    <tr>
                        @foreach($preview['columns'] as $col)
                            @php $cellMeta = $columnMeta[$col] ?? ['type' => 'text']; @endphp
                            @if($loop->first)
                                <th scope="row">{{ __('admin.reports.total') }}</th>
                            @elseif(array_key_exists($col, $preview['totals']))
                                <td class="{{ $alignClass($col) }}">
                                    @if(($cellMeta['type'] ?? 'text') === 'money')
                                        <x-money :value="$preview['totals'][$col]" />
                                    @elseif(($cellMeta['type'] ?? 'text') === 'number')
                                        {{ number_format((float) $preview['totals'][$col]) }}
                                    @else
                                        {{ $preview['totals'][$col] }}
                                    @endif
                                </td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @else
        <x-empty-state icon="fa-file-circle-exclamation" :title="__('admin.reports.no_data_title')" :subtitle="__('admin.reports.no_data_subtitle')" />
        @endif
    </div>
</div>
@else
<div class="card">
    <x-empty-state icon="fa-chart-pie" :title="__('admin.reports.build_title')" :subtitle="__('admin.reports.build_subtitle')" />
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

    .rpt-picker{margin:0}
    .rpt-picker-summary{list-style:none;cursor:pointer;padding:2px 0}
    .rpt-picker-summary::-webkit-details-marker{display:none}
    .rpt-picker-summary::marker{content:''}
    .rpt-picker-current{text-transform:none;letter-spacing:0;color:var(--b6);font-weight:700;margin-left:4px}
    .rpt-picker-chevron{margin-left:auto;transition:transform .18s}
    .rpt-picker[open] .rpt-picker-chevron{transform:rotate(180deg)}
    .rpt-picker[open] .rpt-section{margin-top:12px}

    .rpt-results-head{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid rgba(0,0,0,.06);box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:16px}
    .rpt-results-title{font-size:1.15rem;font-weight:800;color:var(--b9)}
    .rpt-results-desc{font-size:.85rem;color:var(--g5);margin-top:3px}
    .rpt-results-meta{display:flex;flex-wrap:wrap;gap:8px 18px;margin-top:10px;font-size:.78rem;color:var(--g5)}
    .rpt-results-meta i{color:var(--b6);margin-right:4px}
    .rpt-definition{margin-top:10px;padding:9px 12px;border-radius:8px;background:var(--off);font-size:.78rem;line-height:1.5;color:var(--g6)}
    .rpt-definition i{color:var(--b6);margin-right:5px}

    .rpt-table-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .rpt-results .metrics-grid{margin-bottom:24px}
    .rpt-results th.text-right,.rpt-results td.text-right{text-align:right}
    .rpt-sort-link{display:inline-flex;align-items:center;gap:5px;color:inherit;text-decoration:none;white-space:nowrap}
    .rpt-sort-link i{font-size:.68rem;color:var(--g3)}
    .rpt-sort-link:hover,.rpt-sort-link.active{color:var(--b6)}
    .rpt-sort-link.active i{color:var(--b6)}
    .rpt-results th.text-right .rpt-sort-link{justify-content:flex-end;width:100%}
    .rpt-results tfoot th,.rpt-results tfoot td{background:var(--off);font-weight:700;border-top:2px solid var(--g1)}
    .rpt-chart-data{margin-top:12px;font-size:.8rem;color:var(--g7)}
    .rpt-chart-data summary{cursor:pointer;font-weight:600;color:var(--b6)}
    .rpt-chart-data .table-container{margin-top:10px}

    @media(max-width:768px){
        .rpt-bar-form{flex-direction:column;align-items:stretch}
        .rpt-range{width:100%}
        .rpt-range-field{flex:1}
        .rpt-section{grid-template-columns:1fr}
        .rpt-grid{grid-template-columns:1fr}
        .rpt-results-head{padding:16px}
        .rpt-results-title{font-size:1.05rem}
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
@include('admin.reports._chart')
<script>
function initReportChart() {
    window.renderKaAyosReportChart('reportChart', @js($preview['chart'] ?? null));
}

(function () {
    var toggle = document.getElementById('exportToggle');
    var menu = document.getElementById('exportMenu');
    if (toggle && menu) {
        function setExportOpen(open) {
            menu.classList.toggle('open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) menu.querySelector('a')?.focus();
        }
        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            setExportOpen(!menu.classList.contains('open'));
        });
        document.addEventListener('click', function (e) {
            if (!menu.contains(e.target) && e.target !== toggle) setExportOpen(false);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && menu.classList.contains('open')) {
                setExportOpen(false);
                toggle.focus();
            }
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
