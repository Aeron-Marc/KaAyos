<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $booking->booking_ref ?? '#' . $booking->id }} — KaAyos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            padding: 32px 16px;
        }
        .inv-wrap { max-width: 780px; margin: 0 auto; }
        .inv-toolbar { display: flex; gap: 10px; margin-bottom: 16px; }
        .inv-toolbar button, .inv-toolbar a {
            font-size: .85rem; font-weight: 600; padding: 9px 16px; border-radius: 8px;
            border: none; cursor: pointer; text-decoration: none; display: inline-flex;
            align-items: center; gap: 7px;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-print:hover { background: #1d4ed8; }
        .btn-back { background: #fff; color: #334155; border: 1px solid #cbd5e1; }
        .btn-back:hover { background: #f8fafc; }
        .inv-sheet {
            background: #fff; border-radius: 14px; padding: 36px 40px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, .08);
        }
        .inv-head { display: flex; justify-content: space-between; gap: 20px; border-bottom: 2px solid #0f172a; padding-bottom: 18px; }
        .inv-brand { display: flex; align-items: center; gap: 10px; }
        .inv-logo {
            width: 38px; height: 38px; border-radius: 9px; background: #2563eb; color: #fff;
            display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem;
        }
        .inv-brand h1 { font-size: 1.3rem; font-weight: 800; letter-spacing: -.02em; }
        .inv-brand p { font-size: .72rem; color: #64748b; text-transform: uppercase; letter-spacing: .08em; }
        .inv-title { text-align: right; }
        .inv-title h2 { font-size: 1.5rem; font-weight: 800; letter-spacing: .12em; color: #0f172a; }
        .inv-title .inv-no { font-size: .85rem; color: #475569; font-weight: 600; margin-top: 3px; }
        .inv-title .inv-date { font-size: .78rem; color: #64748b; }
        .inv-parties { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin: 22px 0; }
        .inv-box .lbl { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; color: #64748b; font-weight: 700; margin-bottom: 5px; }
        .inv-box .val { font-size: .9rem; font-weight: 600; color: #0f172a; }
        .inv-box .sub { font-size: .8rem; color: #475569; margin-top: 2px; }
        .inv-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin-bottom: 22px; }
        .inv-meta .lbl { font-size: .66rem; text-transform: uppercase; letter-spacing: .07em; color: #64748b; font-weight: 700; }
        .inv-meta .val { font-size: .84rem; font-weight: 600; margin-top: 2px; }
        .inv-items { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .inv-items th {
            text-align: left; font-size: .68rem; text-transform: uppercase; letter-spacing: .07em;
            color: #64748b; padding: 9px 10px; border-bottom: 2px solid #e2e8f0;
        }
        .inv-items td { padding: 11px 10px; font-size: .87rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .inv-items th.r, .inv-items td.r { text-align: right; }
        .inv-items .receipt-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #e2e8f0; display: block; }
        .no-items { font-size: .85rem; color: #64748b; padding: 18px 10px; text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; }
        .inv-totals { margin-top: 18px; margin-left: auto; width: min(320px, 100%); }
        .tot-row { display: flex; justify-content: space-between; padding: 7px 10px; font-size: .88rem; color: #334155; }
        .tot-row.grand {
            border-top: 2px solid #0f172a; margin-top: 6px; padding-top: 11px;
            font-size: 1.08rem; font-weight: 800; color: #0f172a;
        }
        .inv-note { margin-top: 24px; font-size: .74rem; color: #64748b; line-height: 1.6; border-top: 1px solid #e2e8f0; padding-top: 14px; }
        @media (max-width: 640px) {
            .inv-sheet { padding: 24px 18px; }
            .inv-head { flex-direction: column; }
            .inv-title { text-align: left; }
            .inv-parties { grid-template-columns: 1fr; }
            .inv-items th.hide-sm, .inv-items td.hide-sm { display: none; }
        }
        @media print {
            body { background: #fff; padding: 0; }
            .inv-toolbar { display: none; }
            .inv-sheet { box-shadow: none; border-radius: 0; padding: 0; }
        }
    </style>
</head>
<body>
<div class="inv-wrap">
    <div class="inv-toolbar">
        <button type="button" class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print invoice</button>
        <button type="button" class="btn-back" onclick="history.back()"><i class="fa-solid fa-arrow-left"></i> Back</button>
    </div>

    <div class="inv-sheet">
        <div class="inv-head">
            <div class="inv-brand">
                <div class="inv-logo">K</div>
                <div>
                    <h1>KaAyos</h1>
                    <p>Home services marketplace</p>
                </div>
            </div>
            <div class="inv-title">
                <h2>INVOICE</h2>
                <div class="inv-no">{{ $booking->booking_ref ?? ('#' . $booking->id) }}</div>
                <div class="inv-date">Issued {{ ($booking->completed_at ?? $booking->created_at)->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="inv-parties">
            <div class="inv-box">
                <div class="lbl">Billed to (Client)</div>
                <div class="val">{{ $booking->client?->name ?? 'Unknown client' }}</div>
                <div class="sub">{{ trim(($booking->house_no ? $booking->house_no . ', ' : '') . ($booking->barangay ?? '')) ?: ($booking->address ?? '') }}</div>
            </div>
            <div class="inv-box">
                <div class="lbl">Service provider (Worker)</div>
                <div class="val">{{ $booking->worker?->name ?? 'Unknown worker' }}</div>
                <div class="sub">{{ ucfirst(str_replace('_', ' ', $booking->service_category ?? '')) }}</div>
            </div>
        </div>

        <div class="inv-meta">
            <div>
                <div class="lbl">Issue type</div>
                <div class="val">{{ $booking->issueCategory?->name ?? '—' }}</div>
            </div>
            <div>
                <div class="lbl">Scheduled</div>
                <div class="val">{{ $booking->scheduled_at?->format('M d, Y g:i A') ?? '—' }}</div>
            </div>
            <div>
                <div class="lbl">Urgency</div>
                <div class="val">{{ ucfirst($booking->urgency ?? 'normal') }}{{ ($booking->urgency_multiplier ?? 1) > 1 ? ' (×' . $booking->urgency_multiplier . ')' : '' }}</div>
            </div>
            <div>
                <div class="lbl">Status</div>
                <div class="val">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</div>
            </div>
        </div>

        <table class="inv-items">
            <thead>
                <tr>
                    <th style="width:36%;">Description</th>
                    <th class="r" style="width:10%;">Qty</th>
                    <th class="r" style="width:18%;">Unit price</th>
                    <th class="r" style="width:18%;">Amount</th>
                    <th class="hide-sm" style="width:18%;">Receipt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($booking->materials as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td class="r">{{ rtrim(rtrim(number_format((float) $item->qty, 2), '0'), '.') }}</td>
                        <td class="r">₱{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="r">₱{{ number_format((float) $item->line_total, 2) }}</td>
                        <td class="hide-sm">
                            @if($item->receipt_url)
                                <a href="{{ $item->receipt_url }}" target="_blank" rel="noopener">
                                    <img class="receipt-thumb" src="{{ $item->receipt_url }}" alt="Receipt">
                                </a>
                            @else
                                <span style="color:#94a3b8;font-size:.75rem;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="no-items">No material line items were added for this job.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="inv-totals">
            <div class="tot-row">
                <span>Service charge</span>
                <span>₱{{ number_format((float) $booking->price, 2) }}</span>
            </div>
            <div class="tot-row">
                <span>Materials &amp; supplies ({{ $booking->materials->count() }} {{ \Illuminate\Support\Str::plural('item', $booking->materials->count()) }})</span>
                <span>₱{{ number_format((float) $booking->materials_total, 2) }}</span>
            </div>
            <div class="tot-row grand">
                <span>Total due</span>
                <span>₱{{ number_format($booking->invoice_total, 2) }}</span>
            </div>
        </div>

        <div class="inv-note">
            The service charge is the price agreed between client and worker for this booking
            (including any approved scope revisions and urgency multiplier).
            Material line items (hardware/supplies purchased for this job) are billed in addition
            to the service charge. This invoice was generated by KaAyos for
            booking <strong>{{ $booking->booking_ref ?? ('#' . $booking->id) }}</strong>.
        </div>
    </div>
</div>
</body>
</html>
