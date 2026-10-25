@php
    $columnMeta = $data['column_meta'] ?? [];
    $hasRows = count($data['rows']) > 0;
    $hasChart = ($includeChart ?? false) && !empty($data['chart']['labels']);
    $autoprint = $autoprint ?? false;
    $backParams = array_filter([
        'type' => $type,
        'date_from' => $from,
        'date_to' => $to,
        'sort_col' => $sortCol,
        'sort_dir' => $sortCol !== null ? $sortDir : null,
        'preset' => $preset ?? null,
    ], fn ($value) => $value !== null && $value !== '');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>{{ $catalog['label'] }} — {{ $from }} to {{ $to }}</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        .visually-hidden{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}
        @page{size:A4 landscape;margin:12mm 10mm}
        html,body{font-family:-apple-system,'Segoe UI','Inter',Arial,sans-serif;color:#1B2430;background:#fff;font-size:12px;line-height:1.45}
        body{padding:0}

        .print-toolbar{position:sticky;top:0;z-index:10;display:flex;align-items:center;gap:12px;padding:10px 16px;background:#042C53;color:#fff;border-bottom:2px solid #1A6FC4}
        .print-toolbar a,.print-toolbar button{display:inline-flex;align-items:center;gap:8px;padding:8px 16px;border-radius:8px;border:none;font-family:inherit;font-size:.85rem;font-weight:700;text-decoration:none;cursor:pointer;color:#fff;background:#1A6FC4;transition:background .15s}
        .print-toolbar a:hover,.print-toolbar button:hover{background:#185FA5}
        .print-toolbar .back{background:rgba(255,255,255,.12)}
        .print-toolbar .back:hover{background:rgba(255,255,255,.2)}
        .print-toolbar .spacer{flex:1}
        .print-toolbar .hint{font-size:.75rem;color:rgba(255,255,255,.7)}

        .sheet{max-width:100%;padding:20px 8px 40px}

        .letterhead{display:flex;align-items:center;gap:20px;padding-bottom:14px;border-bottom:3px solid #042C53}
        .letterhead-logo{width:90px;height:90px;object-fit:contain;flex-shrink:0}
        .letterhead-logo.right{margin-left:auto}
        .letterhead-text{flex:1;text-align:center;min-width:0}
        .letterhead-text .line-1{font-size:17px;font-weight:700;letter-spacing:.04em;color:#042C53}
        .letterhead-text .line-2{font-size:13px;font-weight:700;color:#0C447C;margin-top:3px}
        .letterhead-text .line-3{font-size:14px;font-weight:700;margin-top:5px}
        .letterhead-text .line-4,.letterhead-text .line-5{font-size:11px;color:#3D4A56;margin-top:2px}

        .report-head{margin-top:18px;text-align:center}
        .report-title{font-size:16px;font-weight:700;color:#042C53;letter-spacing:.02em}
        .report-meta{font-size:10.5px;color:#4B5563;margin-top:6px}
        .report-meta strong{font-weight:600;color:#111827}
        .definition{margin-top:10px;padding:8px 10px;border-left:3px solid #1A6FC4;background:#F3F6FA;font-size:10px;line-height:1.5;color:#4B5563}

        .summary{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px;padding:10px 12px;border:1px solid #CBD5E1;border-radius:8px;background:#F3F6FA;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        .summary-item{flex:1 1 130px;min-width:130px;padding:2px 8px;border-right:1px solid #E2E8F0}
        .summary-item:last-child{border-right:none}
        .summary-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6B7280}
        .summary-value{font-size:14px;font-weight:700;color:#042C53;margin-top:2px}

        .print-chart{margin-top:16px;padding:12px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;page-break-inside:avoid;break-inside:avoid}
        .print-chart h2{font-size:12px;font-weight:700;color:#042C53;margin-bottom:10px}
        .print-chart .canvas-wrap{position:relative;height:240px}

        .table-heading{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin:22px 0 8px}
        .table-heading h2{font-size:13px;font-weight:700;color:#042C53}
        .table-heading span{font-size:10px;color:#6B7280}

        .table-wrap{overflow:visible}
        table{width:100%;border-collapse:collapse;table-layout:auto}
        thead{display:table-header-group}
        th,td{border:1px solid #9CA3AF;padding:5px 7px;text-align:left;vertical-align:top;overflow-wrap:anywhere;word-break:break-word}
        thead th{background:#DCE9F7;color:#042C53;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        tbody td{font-size:10px;color:#111827}
        tbody tr{page-break-inside:avoid;break-inside:avoid}
        tbody tr:nth-child(even) td{background:#F7F9FC;-webkit-print-color-adjust:exact;print-color-adjust:exact}

        .cell-money,.cell-number,.cell-rating{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
        .cell-empty{color:#9CA3AF}
        tfoot th,tfoot td{background:#DCE9F7;border:1px solid #9CA3AF;padding:5px 7px;font-size:9.5px;font-weight:700;color:#042C53;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        tfoot td{text-align:right;font-variant-numeric:tabular-nums}

        .status-badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        .status-new,.status-open{background:#FEF3C7;color:#92400E}
        .status-accepted,.status-confirmed{background:#EDE9FE;color:#5B21B6}
        .status-en_route,.status-in_progress{background:#DBEAFE;color:#1E40AF}
        .status-completed,.status-approved,.status-verified,.status-resolved{background:#D1FAE5;color:#065F46}
        .status-cancelled,.status-declined,.status-rejected,.status-suspended{background:#FEE2E2;color:#991B1B}
        .status-open,.status-not_submitted{background:#FEF3C7;color:#92400E}
        .status-under_review,.status-pending{background:#DBEAFE;color:#1E40AF}
        .status-active{background:#D1FAE5;color:#065F46}

        .no-data{margin-top:20px;padding:30px;text-align:center;border:1px dashed #CBD5E1;border-radius:8px;color:#6B7280}

        .footer{margin-top:24px;padding-top:8px;border-top:1px solid #CBD5E1;font-size:9px;color:#8C97A4;display:flex;justify-content:space-between}

        @media print{
            .print-toolbar{display:none}
            .sheet{padding:0}
            body{background:#fff}
        }
        @media(max-width:600px){
            .letterhead{flex-wrap:wrap;justify-content:center}
            .letterhead-logo.right{margin-left:0}
            .letterhead-text{flex-basis:100%;order:-1}
        }
    </style>
</head>
<body>
<div class="print-toolbar" id="printToolbar">
    <button type="button" id="printNowBtn"><i class="fa-solid fa-print"></i> {{ __('admin.reports.print_now') }}</button>
    <a class="back" id="backToReportsBtn" href="{{ route('admin.reports.index', $backParams) }}"><i class="fa-solid fa-arrow-left"></i> {{ __('admin.reports.back_to_reports') }}</a>
    <span class="spacer"></span>
    <span class="hint">{{ $catalog['label'] }} · {{ __('admin.reports.print_page_hint') }}</span>
</div>

<div class="sheet">
    {{-- ── PESO Letterhead ────────────────────────────── --}}
    <div class="letterhead">
        <img src="{{ asset('images/tuy-logo.jpg') }}" alt="Municipality of Tuy" class="letterhead-logo">
        <div class="letterhead-text">
            <div class="line-1">Republic of the Philippines</div>
            <div class="line-2">PROVINCE OF BATANGAS</div>
            <div class="line-3">PUBLIC EMPLOYMENT SERVICE OFFICE</div>
            <div class="line-4">PESO, Municipal Hall Complex</div>
            <div class="line-5">Gomez St. corner Rizal St., Town Proper, Tuy, Batangas 4214</div>
        </div>
        <img src="{{ asset('images/peso-logo.jpg') }}" alt="PESO" class="letterhead-logo right">
    </div>

    {{-- ── Title & Meta ───────────────────────────────── --}}
    <div class="report-head">
        <div class="report-title">{{ $catalog['label'] }}</div>
        <div class="report-meta">
            <strong>{{ __('admin.reports.period') }}:</strong> {{ $from }} to {{ $to }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>{{ __('admin.reports.generated') }}:</strong> {{ $generated_at }}
            @if($sortLabel)
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>{{ __('admin.reports.sorted_by') }}:</strong> {{ $sortLabel }} ({{ __('admin.reports.sort_'.$sortDir) }})
            @endif
        </div>
        @if($definition)
        <div class="definition"><strong>{{ __('admin.reports.definition') }}:</strong> {{ $definition }}</div>
        @endif
    </div>

    {{-- ── KPI Summary ────────────────────────────────── --}}
    @if(count($data['summary']))
    <div class="summary">
        @foreach($data['summary'] as $kpi)
        <div class="summary-item">
            <div class="summary-label">{{ $kpi['label'] }}</div>
            <div class="summary-value">
                @if($kpi['money'])&#8369;{{ number_format((float)$kpi['value'], 2) }}
                @else{{ $kpi['value'] }}@endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @if($hasChart)
    <div class="print-chart">
        <h2>{{ $catalog['label'] }} {{ __('admin.reports.overview') }}</h2>
        <div class="canvas-wrap">
            <canvas id="printReportChart" role="img" aria-label="{{ __('admin.reports.chart_aria', ['report' => $catalog['label']]) }}"></canvas>
        </div>
    </div>
    @endif

    {{-- ── Data Table ─────────────────────────────────── --}}
    <div class="table-heading">
        <h2>{{ $catalog['label'] }} {{ __('admin.reports.details') }}</h2>
        <span>{{ __('admin.reports.total_records') }}: {{ number_format($data['total_rows']) }}</span>
    </div>

    @if($hasRows)
    <div class="table-wrap">
        <table>
            <caption class="visually-hidden">{{ __('admin.reports.caption', ['report' => $catalog['label']]) }}</caption>
            <thead>
                <tr>
                    @foreach($data['columns'] as $col)
                        @php $cellMeta = $columnMeta[$col] ?? ['type' => 'text']; @endphp
                        <th scope="col" class="{{ in_array($cellMeta['type'] ?? 'text', ['money', 'number', 'decimal', 'rating'], true) ? 'cell-number' : '' }}">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $row)
                <tr>
                    @foreach($data['columns'] as $col)
                        @php
                            $value = $row[$col] ?? null;
                            $cellMeta = $columnMeta[$col] ?? ['type' => 'text'];
                        @endphp
                        <td>
                            @if($value === null || $value === '')
                                <span class="cell-empty">—</span>
                            @elseif(($cellMeta['type'] ?? 'text') === 'status')
                                @php
                                    $statusKey = 'status.'.$value;
                                    $statusLabel = \Illuminate\Support\Facades\Lang::has($statusKey) ? __($statusKey) : str_replace('_', ' ', ucfirst($value));
                                @endphp
                                <span class="status-badge status-{{ $value }}">{{ $statusLabel }}</span>
                            @elseif(($cellMeta['type'] ?? 'text') === 'money')
                                <span class="cell-money">&#8369;{{ number_format((float) $value, 2) }}</span>
                            @elseif(($cellMeta['type'] ?? 'text') === 'rating')
                                <span class="cell-rating">{{ (int) $value }} / 5</span>
                            @elseif(($cellMeta['type'] ?? 'text') === 'number')
                                <span class="cell-number">{{ number_format((float) $value) }}</span>
                            @elseif(($cellMeta['type'] ?? 'text') === 'decimal')
                                <span class="cell-number">{{ number_format((float) $value, 1) }}</span>
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
            @if(!empty($data['totals']))
            <tfoot>
                <tr>
                    @foreach($data['columns'] as $col)
                        @php $cellMeta = $columnMeta[$col] ?? ['type' => 'text']; @endphp
                        @if($loop->first)
                            <th scope="row">{{ __('admin.reports.total') }}</th>
                        @elseif(array_key_exists($col, $data['totals']))
                            <td>
                                @if(($cellMeta['type'] ?? 'text') === 'money')
                                    <span class="cell-money">&#8369;{{ number_format((float) $data['totals'][$col], 2) }}</span>
                                @elseif(($cellMeta['type'] ?? 'text') === 'number')
                                    {{ number_format((float) $data['totals'][$col]) }}
                                @else
                                    {{ $data['totals'][$col] }}
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
    <div class="no-data">{{ __('admin.reports.no_records') }}</div>
    @endif

    <div class="footer">
        <span>Republic of the Philippines — PESO, Municipal Hall Complex, Gomez St. corner Rizal St., Town Proper, Tuy, Batangas 4214</span>
        <span>{{ now()->format('F d, Y') }}</span>
    </div>
</div>
@if($hasChart)
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
@include('admin.reports._chart')
@endif
<script>
(function () {
    var autoprint = {{ $autoprint ? 'true' : 'false' }};
    var printed = false;
    var printBtn = document.getElementById('printNowBtn');
    var backBtn = document.getElementById('backToReportsBtn');

    function ready(fn) {
        if (document.readyState === 'complete') { fn(); }
        else { window.addEventListener('load', fn); }
    }

    function requestPrint() {
        if (!autoprint || printed) return;
        printed = true;
        setTimeout(function () {
            try { window.print(); } catch (err) {}
        }, 120);
    }

    if (printBtn) {
        printBtn.addEventListener('click', function () { window.print(); });
    }
    if (backBtn) {
        var backHref = backBtn.getAttribute('href');
        backBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var current = window;
            try { current.close(); } catch (err) {}
            setTimeout(function () {
                if (!current.closed) window.location.href = backHref;
            }, 60);
        });
    }

    ready(function () {
        @if($hasChart)
        if (typeof window.renderKaAyosReportChart === 'function') {
            window.renderKaAyosReportChart('printReportChart', @js($data['chart'] ?? null), {
                animation: false,
                onRendered: requestPrint
            });
            setTimeout(requestPrint, 900);
        } else {
            requestPrint();
        }
        @else
        requestPrint();
        @endif
    });
})();
</script>
</body>
</html>