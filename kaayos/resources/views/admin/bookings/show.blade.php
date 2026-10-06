@extends('layouts.admin')

@section('title', 'Booking Details')
@section('content')
<a href="{{ route('admin.bookings.index') }}" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to Bookings</a>

<div class="header">
    <div class="header-left">
        <h1><i class="fa-solid fa-receipt"></i> Booking #{{ $booking->id }}</h1>
        <p>{{ $booking->service_category }}</p>
    </div>
    <div class="header-right">
        <span class="status-badge status-{{ $booking->status }}">{{ str_replace('_', ' ', ucfirst($booking->status)) }}</span>
    </div>
</div>

<div class="layout-grid-2">
    <div class="card">
        <div class="card-title"><i class="fa-solid fa-circle-info"></i> Booking Details</div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Booking Ref</span><span class="detail-value">{{ $booking->booking_ref ?? 'BK-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</span></div>
            <div class="detail-row"><span class="detail-label">Service</span><span class="detail-value">{{ $booking->service_category }}</span></div>
            <div class="detail-row"><span class="detail-label">Issue Type</span><span class="detail-value">{{ $booking->issueCategory?->name ?? '—' }}</span></div>
            <div class="detail-row"><span class="detail-label">Urgency</span><span class="detail-value">
                @php
                    $urgencyBadge = match($booking->urgency ?? 'normal') {
                        'emergency' => ['background:#fee2e2', 'color:#b91c1c', 'fa-triangle-exclamation'],
                        'soon'      => ['background:#fef3c7', 'color:#b45309', 'fa-clock'],
                        default     => ['background:#f1f5f9', 'color:#475569', 'fa-circle'],
                    };
                @endphp
                <span class="status-badge" style="{{ $urgencyBadge[0] }};{{ $urgencyBadge[1] }};">
                    <i class="fa-solid {{ $urgencyBadge[2] }}"></i> {{ $booking->urgencyLabel() }}
                </span>
                @if((float) ($booking->urgency_multiplier ?? 1) > 1)
                    <span class="text-sm text-muted" style="margin-left:6px;">&times;{{ number_format((float) $booking->urgency_multiplier, 2) }} price</span>
                @endif
            </span></div>
            <div class="detail-row"><span class="detail-label">Scheduled At</span><span class="detail-value">{{ $booking->scheduled_at?->format('F d, Y \a\t g:i A') ?? 'N/A' }}</span></div>
            <div class="detail-row"><span class="detail-label">Location</span><span class="detail-value" style="text-align:right;max-width:60%">{{ $booking->address }}</span></div>
            <div class="detail-row"><span class="detail-label">Price</span><span class="detail-value">₱{{ number_format((float)$booking->price, 2) }}</span></div>
        </div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Status</span><span class="detail-value">{{ str_replace('_', ' ', ucfirst($booking->status)) }}</span></div>
            @if($booking->completed_at)
            <div class="detail-row"><span class="detail-label">Completed At</span><span class="detail-value">{{ $booking->completed_at->format('F d, Y \a\t g:i A') }}</span></div>
            @endif
            @if($booking->cancelled_at)
            <div class="detail-row"><span class="detail-label">Cancelled At</span><span class="detail-value">{{ $booking->cancelled_at->format('F d, Y \a\t g:i A') }}</span></div>
            @endif
            @if($booking->cancellation_reason)
            <div class="detail-row"><span class="detail-label">Cancel Reason</span><span class="detail-value" style="text-align:right;max-width:60%">{{ $booking->cancellation_reason }}</span></div>
            @endif
        </div>
        @if($booking->notes)
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Problem Description</span><span class="detail-value" style="text-align:right;max-width:60%">{{ $booking->notes }}</span></div>
        </div>
        @endif
        @if($booking->photos->count())
        <div class="detail-section">
            <div class="detail-row" style="align-items:flex-start"><span class="detail-label">Photos</span>
                <span class="detail-value" style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;max-width:60%">
                    @foreach($booking->photos as $photo)
                        <a href="{{ asset('storage/' . $photo->photo_path) }}" target="_blank" rel="noopener" title="{{ $photo->caption ?? (($photo->uploaded_by === 'client') ? 'Client photo' : 'Worker photo') }}">
                            <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="Booking photo"
                                 style="width:64px;height:64px;object-fit:cover;border-radius:6px;border:1px solid var(--b2);display:block;">
                        </a>
                    @endforeach
                </span>
            </div>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-title"><i class="fa-solid fa-users"></i> Client & Worker</div>
        <div class="detail-section">
            <div class="detail-row" style="flex-direction:column;gap:4px">
                <span class="detail-label">Client</span>
                <div style="display:flex;align-items:center;gap:12px;width:100%">
                    <div class="user-initials" style="background:var(--b6);width:48px;height:48px;font-size:1.2rem">
                        {{ strtoupper(substr($booking->client->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($booking->client->last_name ?? 'N', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-600" style="font-size:1rem">{{ $booking->client->name ?? 'N/A' }}</div>
                        <div class="text-sm text-muted">{{ $booking->client->email }}</div>
                        <div class="text-sm text-muted">{{ $booking->client->phone ?? 'No phone' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="detail-section">
            <div class="detail-row" style="flex-direction:column;gap:4px">
                <span class="detail-label">Worker</span>
                <div style="display:flex;align-items:center;gap:12px;width:100%">
                    <div class="user-initials" style="background:var(--s10);width:48px;height:48px;font-size:1.2rem">
                        {{ strtoupper(substr($booking->worker->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($booking->worker->last_name ?? 'N', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-600" style="font-size:1rem">{{ $booking->worker->name ?? 'N/A' }}</div>
                        <div class="text-sm text-muted">{{ $booking->worker->email }}</div>
                        <div class="text-sm text-muted">{{ $booking->worker->phone ?? 'No phone' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="detail-section">
            <div class="detail-row"><span class="detail-label">Created At</span><span class="detail-value">{{ $booking->created_at->format('F d, Y \a\t g:i A') }}</span></div>
        </div>
    </div>
</div>

<div class="card" style="margin-top:24px;">
    <div class="card-title"><i class="fa-solid fa-boxes-stacked"></i> Billing of Materials (BOM)</div>

    <div class="detail-section">
        <div class="detail-row"><span class="detail-label">Service Charge</span><span class="detail-value">₱{{ number_format((float) $booking->price, 2) }}</span></div>
        <div class="detail-row"><span class="detail-label">Materials Total</span><span class="detail-value">₱{{ number_format((float) $booking->materials_total, 2) }}</span></div>
        <div class="detail-row"><span class="detail-label"><strong>Total Due</strong></span><span class="detail-value" style="font-weight:800;">₱{{ number_format($booking->invoice_total, 2) }}</span></div>
        <div class="detail-row"><span class="detail-label">Invoice</span><span class="detail-value">
            <a href="{{ route('bookings.invoice', $booking) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline" style="display:inline-flex;gap:6px;align-items:center;">
                <i class="fa-solid fa-file-invoice"></i> View printable invoice
            </a>
        </span></div>
    </div>

    <div class="detail-section" style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:2px solid var(--b2);">
                    <th style="text-align:left;padding:8px 6px;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--g3);">Item</th>
                    <th style="text-align:right;padding:8px 6px;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--g3);">Qty</th>
                    <th style="text-align:right;padding:8px 6px;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--g3);">Unit Price</th>
                    <th style="text-align:right;padding:8px 6px;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--g3);">Amount</th>
                    <th style="text-align:center;padding:8px 6px;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--g3);">Receipt</th>
                    <th style="width:70px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($booking->materials as $material)
                <tr style="border-bottom:1px solid var(--b2);">
                    <td style="padding:8px 6px;font-size:.85rem;">{{ $material->name }}</td>
                    <td style="padding:8px 6px;font-size:.85rem;text-align:right;">{{ rtrim(rtrim(number_format((float) $material->qty, 2), '0'), '.') }}</td>
                    <td style="padding:8px 6px;font-size:.85rem;text-align:right;">₱{{ number_format((float) $material->unit_price, 2) }}</td>
                    <td style="padding:8px 6px;font-size:.85rem;text-align:right;font-weight:700;">₱{{ number_format((float) $material->line_total, 2) }}</td>
                    <td style="padding:8px 6px;text-align:center;">
                        @if($material->receipt_url)
                            <a href="{{ $material->receipt_url }}" target="_blank" rel="noopener" title="View receipt">
                                <img src="{{ $material->receipt_url }}" alt="Receipt" style="width:36px;height:36px;object-fit:cover;border-radius:5px;border:1px solid var(--b2);display:block;margin:0 auto;">
                            </a>
                        @else
                            <span style="color:var(--g3);font-size:.75rem;">—</span>
                        @endif
                    </td>
                    <td style="padding:8px 6px;text-align:right;white-space:nowrap;">
                        <button type="button" title="Edit" class="admin-mat-edit"
                                data-id="{{ $material->id }}"
                                data-name="{{ $material->name }}"
                                data-qty="{{ $material->qty }}"
                                data-unit="{{ $material->unit_price }}"
                                data-receipt="{{ $material->receipt_url ? '1' : '0' }}"
                                onclick="adminMatEdit(this)" style="background:none;border:none;cursor:pointer;color:var(--b6);padding:2px 4px;"><i class="fa-solid fa-pen"></i></button>
                        <button type="button" title="Delete" class="admin-mat-del" data-id="{{ $material->id }}" onclick="adminMatDelete(this)" style="background:none;border:none;cursor:pointer;color:var(--r6);padding:2px 4px;"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:16px 6px;text-align:center;color:var(--g3);font-size:.85rem;">No material line items for this booking.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="detail-section" style="background:var(--b3);border-radius:8px;padding:12px;">
        <div style="font-size:.8rem;font-weight:700;color:var(--g1);margin-bottom:6px;">
            <i class="fa-solid fa-plus"></i> Add / Edit line item
            <span id="adminMatEditing" style="display:none;color:var(--b6);font-weight:600;margin-left:8px;font-size:.75rem;">Editing item — <a href="#" onclick="adminMatCancelEdit();return false;" style="color:var(--b6);">cancel</a></span>
        </div>
        <form id="adminMatForm" onsubmit="return adminMatSave(event)" style="display:flex;flex-wrap:wrap;gap:9px;align-items:flex-end;">
            <div style="flex:2 1 160px;"><label style="font-size:.68rem;font-weight:700;color:var(--g3);text-transform:uppercase;">Item</label>
                <input name="name" required maxlength="120" placeholder="e.g. PVC pipe 1/2" style="width:100%;margin-top:3px;padding:7px 9px;border:1px solid var(--b2);border-radius:7px;font-size:.85rem;"></div>
            <div style="flex:1 1 80px;"><label style="font-size:.68rem;font-weight:700;color:var(--g3);text-transform:uppercase;">Qty</label>
                <input name="qty" type="number" step="0.01" min="0.01" required placeholder="1" style="width:100%;margin-top:3px;padding:7px 9px;border:1px solid var(--b2);border-radius:7px;font-size:.85rem;"></div>
            <div style="flex:1 1 110px;"><label style="font-size:.68rem;font-weight:700;color:var(--g3);text-transform:uppercase;">Unit Price (₱)</label>
                <input name="unit_price" type="number" step="0.01" min="0" required placeholder="0.00" style="width:100%;margin-top:3px;padding:7px 9px;border:1px solid var(--b2);border-radius:7px;font-size:.85rem;"></div>
            <div style="flex:1 1 130px;"><label style="font-size:.68rem;font-weight:700;color:var(--g3);text-transform:uppercase;">Receipt (optional)</label>
                <input name="receipt" type="file" accept="image/jpeg,image/png,image/webp" style="width:100%;margin-top:5px;font-size:.78rem;"></div>
            <label id="adminMatRemoveWrap" style="display:none;flex:1 1 100%;font-size:.76rem;color:var(--g3);gap:6px;align-items:center;">
                <input type="checkbox" name="remove_receipt" value="1"> Remove saved receipt on save
            </label>
            <button type="submit" class="btn btn-solid" style="background:var(--b6);padding:9px 16px;font-size:.85rem;gap:6px;">
                <i class="fa-solid fa-plus"></i> <span id="adminMatBtnLabel">Add item</span>
            </button>
        </form>
        <div style="font-size:.72rem;color:var(--g3);margin-top:8px;">Admin edits are allowed on any booking status. Totals and the worker's earning are re-synced automatically; completed bookings recalculate the payout.</div>
    </div>
</div>

<div style="margin-top:24px;border-top:1px solid var(--b2);padding-top:20px;display:flex;gap:10px;">
    @if(!in_array($booking->status, [\App\Models\Booking::STATUS_COMPLETED, \App\Models\Booking::STATUS_CANCELLED]))
        <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
            @csrf
            <input type="hidden" name="reason" value="Cancelled by administrator.">
            <button type="submit" class="btn btn-solid" style="background:var(--r6);">
                <i class="fa-solid fa-ban"></i> Cancel Booking
            </button>
        </form>
    @endif
</div>
@endsection

<script>
function adminMatCsrf() { return '{{ csrf_token() }}'; }

function adminMatSave(e) {
    e.preventDefault();
    var form = e.target;
    var id = form.dataset.matId || '';
    var fd = new FormData(form);
    var url = '/admin/bookings/{{ $booking->id }}/materials' + (id ? '/' + id : '');
    if (id) fd.append('_method', 'PATCH');

    fetch(url, { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': adminMatCsrf(), 'Accept': 'application/json' } })
        .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
            if (!res.ok) {
                var msg = res.d && res.d.message;
                if (!msg && res.d && res.d.errors) { msg = Object.keys(res.d.errors).map(function (k) { return res.d.errors[k].join(' '); }).join('\n'); }
                alert(msg || 'Could not save the material item.');
                return;
            }
            location.reload();
        })
        .catch(function () { alert('Network error. Please try again.'); });
    return false;
}

function adminMatEdit(btn) {
    var form = document.getElementById('adminMatForm');
    if (!form || !btn) return;
    form.dataset.matId = btn.dataset.id;
    form.elements['name'].value = btn.dataset.name;
    form.elements['qty'].value = btn.dataset.qty;
    form.elements['unit_price'].value = btn.dataset.unit;
    var box = form.elements['remove_receipt'];
    if (box) box.checked = false;
    document.getElementById('adminMatRemoveWrap').style.display = btn.dataset.receipt === '1' ? 'flex' : 'none';
    document.getElementById('adminMatEditing').style.display = 'inline';
    document.getElementById('adminMatBtnLabel').textContent = 'Save changes';
    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    form.elements['name'].focus();
}

function adminMatCancelEdit() {
    var form = document.getElementById('adminMatForm');
    if (!form) return;
    delete form.dataset.matId;
    form.reset();
    document.getElementById('adminMatRemoveWrap').style.display = 'none';
    document.getElementById('adminMatEditing').style.display = 'none';
    document.getElementById('adminMatBtnLabel').textContent = 'Add item';
}

function adminMatDelete(btn) {
    if (!btn) return;
    if (!confirm('Remove this material line item from the booking?')) return;
    btn.disabled = true;

    fetch('/admin/bookings/{{ $booking->id }}/materials/' + btn.dataset.id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': adminMatCsrf(), 'Accept': 'application/json' }
    })
        .then(function (r) { return r.json().catch(function () { return {}; }).then(function (d) { return { ok: r.ok, d: d }; }); })
        .then(function (res) {
            if (!res.ok) { alert((res.d && res.d.message) || 'Could not remove the material item.'); btn.disabled = false; return; }
            location.reload();
        })
        .catch(function () { alert('Network error. Please try again.'); btn.disabled = false; });
}
</script>
