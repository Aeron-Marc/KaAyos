@extends('layouts.client')

@section('title', 'Bookings')
@section('page_title', 'Bookings')

@section('topbar_actions')
    <a href="{{ route('client.workers') }}" class="btn btn-solid">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> New Booking
    </a>
@endsection

@php
    $filterLabels = [
        ''               => 'All',
        'new'            => 'New',
        'accepted'       => 'Accepted',
        'en_route'       => 'En Route',
        'in_progress'    => 'In Progress',
        'completed'      => 'Completed',
    ];
    $statusLabelMap = [
        'new'         => 'New',
        'accepted'    => 'Accepted',
        'en_route'    => 'En Route',
        'in_progress' => 'In Progress',
        'completed'   => 'Completed',
        'cancelled'   => 'Cancelled',
    ];
@endphp

@section('content')

{{-- Filter Pills --}}
<div class="filter-pills" id="booking-filters">
    @foreach($filterLabels as $val => $label)
        <button type="button"
                class="filter-pill {{ ($activeFilter ?? '') === $val ? 'active' : '' }}"
                data-filter="{{ $val }}">
            {{ $label }}
        </button>
    @endforeach
</div>

{{-- Booking Cards --}}
<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Booking Management</div>
            <h2 class="section-title">Your Scheduled Jobs</h2>
        </div>
    </div>

    <div class="booking-card-list">
        @forelse($bookings as $i => $booking)
            @php
                $statusClass = match($booking['raw_status']) {
                    'new'        => 'status-pending',
                    'accepted'   => 'status-active',
                    'en_route'   => 'status-active',
                    'in_progress'=> 'status-active',
                    'completed'  => 'status-done',
                    default      => 'status-cancelled',
                };
            @endphp
            <div class="booking-card" data-status="{{ $booking['raw_status'] }}" data-booking-id="{{ $booking['id'] }}" onclick="openBookingModal({{ $i }})">
                <div class="schedule-date-box">
                    <span class="schedule-month">{{ $booking['month'] }}</span>
                    <span class="schedule-day">{{ $booking['day'] }}</span>
                </div>
                <div class="booking-card-body">
                    <div class="booking-card-top">
                        <span class="booking-card-service">{{ $booking['service'] }}</span>
                        <span class="booking-card-time"><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $booking['time'] }}</span>
                        <span class="status-badge {{ $statusClass }}">{{ $statusLabelMap[$booking['raw_status']] ?? $booking['status'] }}</span>
                    </div>
                    <div class="booking-card-bottom">
                        <span><i class="fa-regular fa-user" aria-hidden="true"></i> {{ $booking['worker'] }}</span>
                        <span class="meta-sep">·</span>
                        <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $booking['location'] }}</span>
                        <span class="meta-sep">·</span>
                        <span class="booking-card-amount">₱{{ number_format($booking['price']) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <h3>No bookings yet</h3>
                <p>Browse workers and book a service to get started.</p>
                <a href="{{ route('client.workers') }}" class="btn btn-solid" style="margin-top:12px;">
                    Find Workers
                </a>
            </div>
        @endforelse
    </div>
</div>

{{-- Booking Detail Modal --}}
<div id="bookingModal" class="modal-overlay" style="display:none;" onclick="closeBookingModal(event)">
    <div class="modal-box modal-wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="bookingModalTitle">Booking Details</h3>
            <button type="button" class="modal-close" onclick="closeBookingModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="booking-modal-layout">
                <div class="booking-modal-details" id="bookingModalDetails"></div>
                <div class="booking-modal-timeline" id="bookingModalTimeline"></div>
            </div>
        </div>
        <div class="modal-footer" id="bookingModalFooter"></div>
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
            <button type="button" class="btn btn-solid" style="background:#dc2626;" id="confirmCancelBtn" onclick="confirmCancel()">
                Yes, Cancel
            </button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ── Compact booking cards ── */
.booking-card-list { padding: 2px 0; }

.booking-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--g1);
    cursor: pointer;
    transition: background .12s;
}
.booking-card:last-child { border-bottom: none; }
.booking-card:hover { background: var(--b0); }

.booking-card-body { flex: 1; min-width: 0; }

.booking-card-top {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
    flex-wrap: wrap;
}

.booking-card-service {
    font-size: .92rem;
    font-weight: 600;
    color: var(--b9);
}

.booking-card-time {
    font-size: .8rem;
    color: var(--g4);
    margin-left: auto;
    white-space: nowrap;
}
.booking-card-time i { margin-right: 3px; }

.booking-card-bottom {
    font-size: .8rem;
    color: var(--g4);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 3px;
    line-height: 1.4;
}
.booking-card-bottom i { margin-right: 3px; }
.meta-sep { color: var(--g2); }

.booking-card-amount {
    font-weight: 600;
    color: var(--b9);
}

/* ── Date box ── */
.schedule-date-box {
    width: 48px; height: 48px; border-radius: 10px;
    background: var(--b0); color: var(--b6);
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    flex-shrink: 0; font-weight: 700; line-height: 1.15;
}
.schedule-month { font-size: .6rem; text-transform: uppercase; color: var(--b4); }
.schedule-day { font-size: 1.05rem; color: var(--b9); }

/* ── Detail modal layout ── */
.modal-wide { max-width: 640px !important; }

.booking-modal-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* ── Details grid ── */
.detail-grid-compact {
    display: grid;
    grid-template-columns: 95px 1fr;
    gap: 5px 12px;
    font-size: .85rem;
}
.detail-label { color: var(--g5); font-weight: 500; }
.detail-value { color: var(--b9); }

/* ── Status timeline ── */
.timeline {
    display: flex;
    flex-direction: column;
}

.timeline-item {
    display: flex;
    gap: 10px;
    min-height: 32px;
}

.timeline-dot-col {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 24px;
    flex-shrink: 0;
}

.timeline-dot {
    width: 18px; height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .55rem;
    flex-shrink: 0;
    margin-top: 2px;
    line-height: 1;
}

.timeline-dot.past {
    background: #16a34a;
    color: #fff;
}

.timeline-dot.current {
    background: var(--b6);
    color: #fff;
    box-shadow: 0 0 0 3px var(--b0);
}

.timeline-dot.future {
    background: var(--g1);
    color: var(--g4);
}

.timeline-line {
    width: 2px;
    flex: 1;
    background: var(--g1);
    min-height: 10px;
}

.timeline-content {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    padding-bottom: 2px;
}

.timeline-status {
    font-size: .82rem;
    font-weight: 500;
    color: var(--g7);
}

.timeline-item.past .timeline-status { color: #16a34a; }
.timeline-item.current .timeline-status { color: var(--b6); font-weight: 600; }
.timeline-item.future .timeline-status { color: var(--g4); }

.timeline-time {
    font-size: .75rem;
    color: var(--g4);
    margin-left: auto;
    white-space: nowrap;
}

.timeline-item.future .timeline-time { display: none; }

/* ── Modals ── */
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
    padding: 16px 20px 0; font-weight: 600; font-size: 1.05rem;
}
.modal-close {
    background: none; border: none; font-size: 1.5rem; cursor: pointer;
    color: var(--g4); line-height: 1;
}
.modal-close:hover { color: var(--g8); }
.modal-body { padding: 14px 20px; max-height: 70vh; overflow-y: auto; }
.modal-footer {
    display: flex; gap: 10px; justify-content: flex-end;
    padding: 0 20px 16px;
}

.toast-notification {
    position: fixed; bottom: 24px; right: 24px;
    background: #1e293b; color: #fff; padding: 14px 20px;
    border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.25);
    z-index: 9999; font-size: .85rem; max-width: 340px;
    animation: slideUp .3s ease;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 640px) {
    .booking-modal-layout { grid-template-columns: 1fr; gap: 14px; }
    .booking-card { padding: 10px 14px; gap: 10px; }
    .schedule-date-box {     width: 40px; height: 40px;  }
    .schedule-day { font-size: .9rem; }
    .schedule-month { font-size: .55rem; }
    .booking-card-time { margin-left: 0; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
const bookings = @json($bookings);
const cancelIndexHolder = { value: null };
const statusLabelMap = {
    'new':'New','accepted':'Accepted','en_route':'En Route',
    'in_progress':'In Progress','completed':'Completed','cancelled':'Cancelled',
};

// ── Real-time status update ──
document.addEventListener('DOMContentLoaded', function () {
    var checkEcho = setInterval(function () {
        if (window.Echo) {
            clearInterval(checkEcho);
            var userId = {{ auth()->id() }};
            window.Echo.private('user.' + userId)
                .listen('BookingStatusUpdated', function (e) {
                    var cards = document.querySelectorAll('.booking-card');
                    cards.forEach(function (card) {
                        if (String(card.dataset.bookingId) === String(e.id)) {
                            var badge = card.querySelector('.status-badge');
                            var clsMap = {
                                'new':'status-pending','accepted':'status-active',
                                'en_route':'status-active','in_progress':'status-active',
                                'completed':'status-done','cancelled':'status-cancelled',
                            };
                            badge.textContent = statusLabelMap[e.new_status] || e.new_status;
                            badge.className = 'status-badge ' + (clsMap[e.new_status] || '');
                            card.dataset.status = e.new_status;
                        }
                    });
                });
        }
    }, 200);
});

// ── Filter pills ──
document.addEventListener('DOMContentLoaded', function () {
    var pills = document.querySelectorAll('#booking-filters .filter-pill');
    var cards = document.querySelectorAll('.booking-card');
    var list = document.querySelector('.booking-card-list');
    var empty = list ? list.querySelector('.empty-state') : null;

    function updateEmpty() {
        if (!empty) return;
        var visible = Array.from(cards).filter(function (c) { return c.style.display !== 'none'; }).length;
        empty.style.display = visible === 0 ? '' : 'none';
    }

    pills.forEach(function (pill) {
        pill.addEventListener('click', function () {
            pills.forEach(function (p) { p.classList.remove('active'); });
            this.classList.add('active');
            var filter = this.dataset.filter;
            cards.forEach(function (card) {
                card.style.display = !filter || card.dataset.status === filter ? '' : 'none';
            });
            updateEmpty();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeBookingModal(); closeModals(); }
    });
});

// ── Booking Detail Modal ──
function openBookingModal(index) {
    var b = bookings[index];
    if (!b) return;

    var statusFlow = ['new', 'accepted', 'en_route', 'in_progress', 'completed'];
    var currentIdx = statusFlow.indexOf(b.raw_status);

    // Details
    var notes = b.notes || 'No details provided.';
    document.getElementById('bookingModalTitle').textContent = 'Booking Details';
    document.getElementById('bookingModalDetails').innerHTML =
        '<div class="detail-grid-compact">' +
            '<span class="detail-label">Reference</span>' +
            '<span class="detail-value">' + (b.booking_ref || 'BK-' + String(b.id).padStart(5,'0')) + '</span>' +
            '<span class="detail-label">Worker</span>' +
            '<span class="detail-value">' + b.worker + '</span>' +
            '<span class="detail-label">Service</span>' +
            '<span class="detail-value">' + b.service + '</span>' +
            '<span class="detail-label">Schedule</span>' +
            '<span class="detail-value">' + b.date + '</span>' +
            '<span class="detail-label">Location</span>' +
            '<span class="detail-value">' + b.location + '</span>' +
            '<span class="detail-label">Amount</span>' +
            '<span class="detail-value" style="font-weight:600;">₱' + Number(b.price).toLocaleString() + '</span>' +
            '<span class="detail-label">Notes</span>' +
            '<span class="detail-value">' + notes + '</span>' +
        '</div>';

    // Timeline
    var timelineHtml = '<div class="timeline">';
    for (var si = 0; si < statusFlow.length; si++) {
        var status = statusFlow[si];
        var ts = b.status_history ? b.status_history[status] : null;
        var isPast = si < currentIdx;
        var isCurrent = si === currentIdx;
        var isFuture = si > currentIdx;
        var dotClass = isPast ? 'past' : (isCurrent ? 'current' : 'future');
        var dotIcon = isPast ? '✓' : (isCurrent ? '●' : '○');
        var timeHtml = ts ? '<span class="timeline-time">' + formatTime(ts) + '</span>' : '';
        timelineHtml += '<div class="timeline-item ' + dotClass + '">';
        timelineHtml +=   '<div class="timeline-dot-col">';
        timelineHtml +=     '<div class="timeline-dot ' + dotClass + '">' + dotIcon + '</div>';
        if (si < statusFlow.length - 1) timelineHtml += '<div class="timeline-line"></div>';
        timelineHtml +=   '</div>';
        timelineHtml +=   '<div class="timeline-content">';
        timelineHtml +=     '<span class="timeline-status">' + statusLabelMap[status] + '</span>';
        timelineHtml +=     timeHtml;
        timelineHtml +=   '</div>';
        timelineHtml += '</div>';
    }
    timelineHtml += '</div>';
    document.getElementById('bookingModalTimeline').innerHTML = timelineHtml;

    // Footer
    var footer = document.getElementById('bookingModalFooter');
    if (b.raw_status === 'cancelled' || b.raw_status === 'completed') {
        var extra = '';
        if (b.raw_status === 'completed') {
            extra = '<a href="{{ route('client.reviews') }}" class="btn btn-outline"><i class="fa-regular fa-star" aria-hidden="true"></i> Leave Review</a>';
        }
        footer.innerHTML = extra + '<button type="button" class="btn btn-outline" onclick="closeBookingModal()">Close</button>';
    } else {
        footer.innerHTML =
            '<button type="button" class="btn btn-outline" onclick="closeBookingModal(); showCancelModal(' + index + ')">Cancel Booking</button>' +
            '<a href="{{ route('client.messages.start') }}?worker_id=' + b.worker_id + '" class="btn btn-solid"><i class="fa-regular fa-comment" aria-hidden="true"></i> Message</a>';
    }

    document.getElementById('bookingModal').style.display = 'flex';
}

function closeBookingModal(e) {
    if (e && e.target && !e.target.closest) return;
    document.getElementById('bookingModal').style.display = 'none';
}

function formatTime(ts) {
    if (!ts) return '';
    var d = new Date(ts);
    if (isNaN(d.getTime())) return ts;
    var hours = d.getHours(), mins = d.getMinutes();
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    return hours + ':' + String(mins).padStart(2,'0') + ' ' + ampm;
}

// ── Cancel ──
function closeModals(e) {
    if (e && e.target && !e.target.closest) return;
    document.getElementById('cancelModal').style.display = 'none';
    cancelIndexHolder.value = null;
}

function showCancelModal(index) {
    var b = bookings[index];
    if (!b) return;
    cancelIndexHolder.value = index;
    document.getElementById('cancelSummary').innerHTML =
        '<div class="detail-grid-compact">' +
            '<span class="detail-label">Worker</span><span class="detail-value">' + b.worker + '</span>' +
            '<span class="detail-label">Service</span><span class="detail-value">' + b.service + '</span>' +
            '<span class="detail-label">Schedule</span><span class="detail-value">' + b.date + '</span>' +
            '<span class="detail-label">Amount</span><span class="detail-value">₱' + Number(b.price).toLocaleString() + '</span>' +
        '</div>';
    document.getElementById('cancelReason').value = '';
    document.getElementById('cancelModal').style.display = 'flex';
}

function confirmCancel() {
    if (cancelIndexHolder.value === null) return;
    var b = bookings[cancelIndexHolder.value];
    var reason = document.getElementById('cancelReason').value.trim();
    document.getElementById('confirmCancelBtn').disabled = true;
    document.getElementById('confirmCancelBtn').textContent = 'Cancelling…';
    fetch('/client/bookings/' + b.id + '/cancel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ reason: reason || 'Cancelled by client' }),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'Failed to cancel booking.'); }
    })
    .catch(function () { alert('Something went wrong.'); })
    .finally(function () {
        document.getElementById('confirmCancelBtn').disabled = false;
        document.getElementById('confirmCancelBtn').textContent = 'Yes, Cancel';
    });
}

// Focus handler: ?focus=ID opens the booking modal on load
(function() {
    var params = new URLSearchParams(window.location.search);
    var focusId = params.get('focus');
    if (focusId) {
        for (var i = 0; i < bookings.length; i++) {
            if (String(bookings[i].id) === String(focusId)) {
                openBookingModal(i);
                break;
            }
        }
    }
})();
</script>
@endpush
