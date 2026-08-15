@php
    $moneyCols = ['Price', 'Total Value', 'Gross Amount', 'Platform Fee', 'Net Amount', 'Gross Revenue', 'Revenue'];
    $statusCols = ['new', 'accepted', 'en_route', 'in_progress', 'completed', 'cancelled'];
    $hasRows = count($data['rows']) > 0;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>{{ $catalog['label'] }} — {{ $from }} to {{ $to }}</title>
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
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

        .summary{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px;padding:10px 12px;border:1px solid #CBD5E1;border-radius:8px;background:#F3F6FA}
        .summary-item{flex:1 1 130px;min-width:130px;padding:2px 8px;border-right:1px solid #E2E8F0}
        .summary-item:last-child{border-right:none}
        .summary-label{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#6B7280}
        .summary-value{font-size:14px;font-weight:700;color:#042C53;margin-top:2px}

        .table-heading{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin:22px 0 8px}
        .table-heading h2{font-size:13px;font-weight:700;color:#042C53}
        .table-heading span{font-size:10px;color:#6B7280}

        .table-wrap{overflow:visible}
        table{width:100%;border-collapse:collapse}
        thead{display:table-header-group}
        th,td{border:1px solid #9CA3AF;padding:5px 7px;text-align:left;vertical-align:top}
        thead th{background:#DCE9F7;color:#042C53;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        tbody td{font-size:10px;color:#111827}
        tbody tr{page-break-inside:avoid;break-inside:avoid}
        tbody tr:nth-child(even) td{background:#F7F9FC;-webkit-print-color-adjust:exact;print-color-adjust:exact}

        .cell-money{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}
        .cell-empty{color:#9CA3AF}

        .status-badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.03em;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        .status-new,.status-open{background:#FEF3C7;color:#92400E}
        .status-accepted,.status-confirmed{background:#EDE9FE;color:#5B21B6}
        .status-en_route,.status-in_progress{background:#DBEAFE;color:#1E40AF}
        .status-completed,.status-approved,.status-verified,.status-resolved{background:#D1FAE5;color:#065F46}
        .status-cancelled,.status-rejected,.status-suspended{background:#FEE2E2;color:#991B1B}

        .no-data{margin-top:20px;padding:30px;text-align:center;border:1px dashed #CBD5E1;border-radius:8px;color:#6B7280}

        .footer{margin-top:24px;padding-top:8px;border-top:1px solid #CBD5E1;font-size:9px;color:#8C97A4;display:flex;justify-content:space-between}
        .page-num::before{content:counter(page)}

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
    <button type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> Print</button>
    <a class="back" id="backToReportsBtn" href="{{ route('admin.reports.index', ['type' => $type, 'date_from' => $from, 'date_to' => $to]) }}"><i class="fa-solid fa-arrow-left"></i> Back to Reports</a>
    <span class="spacer"></span>
    <span class="hint">Print Preview — {{ $catalog['label'] }}</span>
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
            <strong>Period:</strong> {{ $from }} to {{ $to }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <strong>Generated:</strong> {{ $generated_at }}
        </div>
    </div>

    {{-- ── KPI Summary ────────────────────────────────── --}}
    @if(count($data['summary']))
    <div class="summary">
        @foreach($data['summary'] as $kpi)
        <div class="summary-item">
            <div class="summary-label">{{ $kpi['label'] }}</div>
            <div class="summary-value">
                @if($kpi['money'])₱{{ number_format((float)$kpi['value'], 2) }}
                @else{{ $kpi['value'] }}@endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Data Table ─────────────────────────────────── --}}
    <div class="table-heading">
        <h2>{{ $catalog['label'] }} Details</h2>
        <span>Total Records: {{ number_format($data['total_rows']) }}</span>
    </div>

    @if($hasRows)
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    @foreach($data['columns'] as $col)<th>{{ $col }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data['rows'] as $row)
                <tr>
                    @foreach($data['columns'] as $col)
                        @php $value = $row[$col] ?? null; @endphp
                        <td>
                            @if($col === 'Status' && in_array($value, $statusCols, true))
                                <span class="status-badge status-{{ $value }}">{{ str_replace('_', ' ', ucfirst($value)) }}</span>
                            @elseif(in_array($col, $moneyCols, true) && $value !== null)
                                <span class="cell-money">₱{{ number_format((float)$value, 2) }}</span>
                            @elseif($value === null || $value === '')
                                <span class="cell-empty">—</span>
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
    <div class="no-data">No records match the selected period.</div>
    @endif

    <div class="footer">
        <span>Republic of the Philippines — PESO, Municipal Hall Complex, Gomez St. corner Rizal St., Town Proper, Tuy, Batangas 4214</span>
        <span>Page <span class="page-num"></span></span>
    </div>
</div>
<script>
    var backBtn = document.getElementById('backToReportsBtn');
    if (backBtn) {
        var backHref = backBtn.getAttribute('href');
        backBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var current = window;
            try { current.close(); } catch (err) {}
            setTimeout(function () {
                if (!current.closed) {
                    window.location.href = backHref;
                }
            }, 60);
        });
    }
</script>
</body>
</html>