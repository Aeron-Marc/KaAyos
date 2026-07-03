@extends('layouts.client')

@section('title', 'Bookings')
@section('page_title', 'Bookings')

@section('topbar_actions')
    <a href="{{ route('client.workers') }}" class="btn btn-solid">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> New Booking
    </a>
@endsection

@php
    $statusLabelMap = [
        'new'         => 'Pending',
        'accepted'    => 'Active',
        'en_route'    => 'En Route',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'cancelled'   => 'Cancelled',
    ];
@endphp

@section('content')

<div class="filter-pills" id="booking-filters">
    <button type="button" class="filter-pill active" data-filter="">All</button>
    <button type="button" class="filter-pill" data-filter="active">Active</button>
    <button type="button" class="filter-pill" data-filter="pending">Pending</button>
    <button type="button" class="filter-pill" data-filter="completed">Completed</button>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Booking Management</div>
            <h2 class="section-title">Your Scheduled Jobs</h2>
        </div>
    </div>

    <table class="bookings-table">
        <thead>
            <tr>
                <th>Worker</th>
                <th>Service</th>
                <th>Date &amp; Time</th>
                <th>Status</th>
                <th>Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $i => $booking)
                <tr class="booking-row" data-status="{{ $booking['raw_status'] }}" data-index="{{ $i }}">
                    <td><span class="booking-worker">{{ $booking['worker'] }}</span></td>
                    <td>{{ $booking['service'] }}</td>
                    <td>{{ $booking['date'] }}</td>
                    <td>
                        @php
                            $cls = match($booking['raw_status']) {
                                'new'         => 'status-pending',
                                'accepted'    => 'status-active',
                                'en_route'    => 'status-active',
                                'in_progress' => 'status-active',
                                'completed'   => 'status-done',
                                default       => 'status-cancelled',
                            };
                        @endphp
                        <span class="status-badge {{ $cls }}">{{ $statusLabelMap[$booking['raw_status']] ?? $booking['status'] }}</span>
                    </td>
                    <td>₱{{ number_format($booking['price']) }}</td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;">
                            <button type="button"
                                    class="btn btn-outline"
                                    style="padding:6px 10px;font-size:.78rem;"
                                    onclick="showBookingInfo({{ $i }})"
                                    title="View details">
                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                            </button>
                            @if(in_array($booking['raw_status'], ['new', 'accepted']))
                                <button type="button"
                                        class="btn btn-outline"
                                        style="padding:6px 10px;font-size:.78rem;color:var(--r7);border-color:var(--r4);"
                                        onclick="showCancelModal({{ $i }})"
                                        title="Cancel booking">
                                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                                </button>
                            @endif
                            <a href="{{ route('client.messages') }}?booking={{ $booking['id'] }}"
                               class="btn btn-ghost" style="padding:6px 10px;font-size:.78rem;"
                               title="Send message">
                                <i class="fa-solid fa-comment" aria-hidden="true"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="empty-row">
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                            <h3>No bookings yet</h3>
                            <p>Browse workers and book a service to get started.</p>
                            <a href="{{ route('client.workers') }}" class="btn btn-solid" style="margin-top:12px;">
                                Find Workers
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Info Modal --}}
<div id="infoModal" class="modal-overlay" style="display:none;" onclick="closeModals(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="infoModalTitle">Booking Details</h3>
            <button type="button" class="modal-close" onclick="closeModals()">&times;</button>
        </div>
        <div class="modal-body" id="infoModalBody"></div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModals()">Close</button>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div id="cancelModal" class="modal-overlay" style="display:none;" onclick="closeModals(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Cancel Booking</h3>
            <button type="button" class="modal-close" onclick="closeModals()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin:0 0 12px;color:var(--g6);">
                Are you sure you want to cancel this booking?
            </p>
            <div id="cancelSummary"></div>
            <div style="margin-top:14px;">
                <label style="font-size:.85rem;font-weight:500;color:var(--g6);display:block;margin-bottom:4px;">
                    Reason (optional)
                </label>
                <textarea id="cancelReason" class="review-textarea" placeholder="Tell the worker why…" style="min-height:70px;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModals()">Keep Booking</button>
            <button type="button" class="btn btn-solid" style="background:var(--r6);" id="confirmCancelBtn" onclick="confirmCancel()">
                Yes, Cancel
            </button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.45); z-index: 1000;
    display: flex; align-items: center; justify-content: center;
}
.modal-box {
    background: #fff; border-radius: 14px; width: 90%; max-width: 520px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2); animation: modalIn .2s ease;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(.95) translateY(10px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px 0; font-weight: 600; font-size: 1.05rem;
}
.modal-close {
    background: none; border: none; font-size: 1.5rem; cursor: pointer;
    color: var(--g4); line-height: 1;
}
.modal-close:hover { color: var(--g8); }
.modal-body { padding: 16px 22px; max-height: 60vh; overflow-y: auto; }
.modal-footer {
    display: flex; gap: 10px; justify-content: flex-end;
    padding: 0 22px 18px;
}
.detail-grid {
    display: grid; grid-template-columns: 110px 1fr; gap: 10px 14px; font-size: .88rem;
}
.detail-label { color: var(--g5); font-weight: 500; }
.detail-value { color: var(--b9); }
</style>
@endpush

@push('scripts')
<script>
const bookings = @json($bookings);
let cancelIndex = null;

function closeModals(e) {
    if (e && e.target && !e.target.closest) return;
    document.getElementById('infoModal').style.display = 'none';
    document.getElementById('cancelModal').style.display = 'none';
    cancelIndex = null;
}

function showBookingInfo(index) {
    const b = bookings[index];
    if (!b) return;

    document.getElementById('infoModalTitle').textContent = 'Booking Details';
    document.getElementById('infoModalBody').innerHTML = `
        <div class="detail-grid">
            <span class="detail-label">Worker</span>
            <span class="detail-value">${b.worker}</span>
            <span class="detail-label">Service</span>
            <span class="detail-value">${b.service}</span>
            <span class="detail-label">Schedule</span>
            <span class="detail-value">${b.date}</span>
            <span class="detail-label">Amount</span>
            <span class="detail-value" style="font-weight:600;">₱${Number(b.price).toLocaleString()}</span>
            <span class="detail-label">Status</span>
            <span class="detail-value">${b.status}</span>
        </div>
    `;
    document.getElementById('infoModal').style.display = 'flex';
}

function showCancelModal(index) {
    const b = bookings[index];
    if (!b) return;
    cancelIndex = index;

    document.getElementById('cancelSummary').innerHTML = `
        <div class="detail-grid">
            <span class="detail-label">Worker</span>
            <span class="detail-value">${b.worker}</span>
            <span class="detail-label">Service</span>
            <span class="detail-value">${b.service}</span>
            <span class="detail-label">Schedule</span>
            <span class="detail-value">${b.date}</span>
            <span class="detail-label">Amount</span>
            <span class="detail-value">₱${Number(b.price).toLocaleString()}</span>
        </div>
    `;
    document.getElementById('cancelReason').value = '';
    document.getElementById('cancelModal').style.display = 'flex';
}

function confirmCancel() {
    if (cancelIndex === null) return;
    const b = bookings[cancelIndex];
    const reason = document.getElementById('cancelReason').value.trim();

    document.getElementById('confirmCancelBtn').disabled = true;
    document.getElementById('confirmCancelBtn').textContent = 'Cancelling…';

    fetch('/client/bookings/' + b.id + '/cancel', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ reason: reason || 'Cancelled by client' }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to cancel booking.');
        }
    })
    .catch(() => alert('Something went wrong.'))
    .finally(() => {
        document.getElementById('confirmCancelBtn').disabled = false;
        document.getElementById('confirmCancelBtn').textContent = 'Yes, Cancel';
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const pills = document.querySelectorAll('#booking-filters .filter-pill');
    const rows = document.querySelectorAll('.booking-row');
    const emptyRow = document.getElementById('empty-row');

    pills.forEach(pill => {
        pill.addEventListener('click', function () {
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            let visible = 0;

            rows.forEach(row => {
                const status = row.dataset.status;
                let match = false;
                if (!filter) match = true;
                else if (filter === 'active') match = ['accepted', 'en_route', 'in_progress'].includes(status);
                else if (filter === 'pending') match = status === 'new';
                else if (filter === 'completed') match = status === 'completed' || status === 'cancelled';
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });

            if (emptyRow) {
                emptyRow.style.display = visible === 0 ? '' : 'none';
            }
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeModals();
    });
});
</script>
@endpush
