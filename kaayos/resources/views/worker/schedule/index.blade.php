@extends('layouts.worker')

@section('title', 'My Schedule')
@section('page_title', 'My Schedule')

@php
    $filterLabels = [
        ''               => 'All',
        'new'            => 'New',
        'accepted'       => 'Accepted',
        'en_route'       => 'En Route',
        'in_progress'    => 'In Progress',
        'completed'      => 'Completed',
    ];
@endphp

@section('skeleton')
    <div class="sp-panel">
        <div class="skeleton skeleton-title" style="width:180px;margin-bottom:20px;"></div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <div class="skeleton" style="height:44px;width:100px;border-radius:10px;"></div>
            <div class="skeleton" style="height:44px;width:100px;border-radius:10px;"></div>
            <div class="skeleton" style="height:44px;width:100px;border-radius:10px;"></div>
            <div class="skeleton" style="height:44px;width:100px;border-radius:10px;"></div>
            <div class="skeleton" style="height:44px;width:100px;border-radius:10px;"></div>
            <div class="skeleton" style="height:44px;width:100px;border-radius:10px;"></div>
            <div class="skeleton" style="height:44px;width:100px;border-radius:10px;"></div>
        </div>
    </div>
    <div class="sp-tabs">
        <div class="skeleton" style="height:32px;width:50px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:70px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:90px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:80px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:60px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:100px;border-radius:99px;"></div>
    </div>
    <div style="display:flex;gap:16px;">
        <div class="skeleton" style="width:60px;height:60px;border-radius:12px;flex-shrink:0;"></div>
        <div style="flex:1;">
            <div class="skeleton skeleton-text" style="width:50%;"></div>
            <div class="skeleton skeleton-text-sm" style="width:40%;"></div>
            <div class="skeleton skeleton-text-sm" style="width:65%;"></div>
        </div>
    </div>
    <div style="display:flex;gap:16px;margin-top:16px;">
        <div class="skeleton" style="width:60px;height:60px;border-radius:12px;flex-shrink:0;"></div>
        <div style="flex:1;">
            <div class="skeleton skeleton-text" style="width:45%;"></div>
            <div class="skeleton skeleton-text-sm" style="width:35%;"></div>
            <div class="skeleton skeleton-text-sm" style="width:55%;"></div>
        </div>
    </div>
@endsection

@section('content')



<div class="cat-dropdown" id="jobStatusDropdown">
    <button class="cat-dropdown-trigger" id="jobStatusTrigger">
        <span class="cat-dropdown-label" id="jobStatusLabel">
            <i class="fa-solid fa-list"></i> All
        </span>
        <i class="fa-solid fa-chevron-down cat-chev"></i>
    </button>
    <div class="cat-dropdown-menu" id="jobStatusMenu">
        <div class="cat-menu-header">Filter by Status</div>
        <button type="button" class="cat-option active" data-filter="">
            <span class="cat-option-icon"><i class="fa-solid fa-list"></i></span>
            <span class="cat-option-label">All</span>
        </button>
        <div class="cat-menu-divider"></div>
        <button type="button" class="cat-option" data-filter="new">
            <span class="cat-option-icon"><i class="fa-regular fa-clock"></i></span>
            <span class="cat-option-label">New</span>
        </button>
        <button type="button" class="cat-option" data-filter="accepted">
            <span class="cat-option-icon"><i class="fa-regular fa-handshake"></i></span>
            <span class="cat-option-label">Accepted</span>
        </button>
        <button type="button" class="cat-option" data-filter="en_route">
            <span class="cat-option-icon"><i class="fa-solid fa-truck"></i></span>
            <span class="cat-option-label">En Route</span>
        </button>
        <button type="button" class="cat-option" data-filter="in_progress">
            <span class="cat-option-icon"><i class="fa-solid fa-spinner"></i></span>
            <span class="cat-option-label">In Progress</span>
        </button>
        <button type="button" class="cat-option" data-filter="completed">
            <span class="cat-option-icon"><i class="fa-regular fa-circle-check"></i></span>
            <span class="cat-option-label">Completed</span>
        </button>
    </div>
</div>

{{-- Job Cards --}}
<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Jobs</div>
            <h2 class="section-title">All Jobs</h2>
        </div>
    </div>

    <div class="job-card-list">
        @forelse($jobRequests as $i => $job)
            @php
                $statusClass = match($job['raw_status']) {
                    'new'        => 'status-pending',
                    'accepted'   => 'status-active',
                    'en_route'   => 'status-active',
                    'in_progress'=> 'status-active',
                    'completed'  => 'status-done',
                    default      => 'status-cancelled',
                };
            @endphp
            <div class="job-card" data-status="{{ $job['raw_status'] }}" onclick="openJobModal({{ $i }})">
                <div class="schedule-date-box">
                    <span class="schedule-month">{{ $job['month'] }}</span>
                    <span class="schedule-day">{{ $job['day'] }}</span>
                </div>
                <div class="job-card-body">
                    <div class="job-card-top">
                        <span class="job-card-service">{{ $job['service'] }}</span>
                        <span class="job-card-time"><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $job['time'] }}</span>
                        <span class="status-badge {{ $statusClass }}">{{ $job['status'] }}</span>
                    </div>
                    <div class="job-card-bottom">
                        <span><i class="fa-regular fa-user" aria-hidden="true"></i> {{ $job['client'] }}</span>
                        <span class="meta-sep">·</span>
                        <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $job['location'] }}</span>
                        <span class="meta-sep">·</span>
                        <span class="job-card-amount">₱{{ number_format($job['price']) }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <i class="fa-solid fa-clipboard" aria-hidden="true"></i>
                <h3>No job requests yet</h3>
                <p>When a client books your service, it will appear here.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Job Detail Modal --}}
<div id="jobModal" class="modal-overlay" style="display:none;" onclick="closeJobModal(event)">
    <div class="modal-box modal-wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="jobModalTitle">Job Details</h3>
            <button type="button" class="modal-close" onclick="closeJobModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="job-modal-layout">
                <div class="job-modal-details" id="jobModalDetails"></div>
                <div class="job-modal-timeline" id="jobModalTimeline"></div>
            </div>
        </div>
        <div class="modal-footer" id="jobModalFooter"></div>
    </div>
</div>

{{-- Confirmation Modal --}}
<div id="confirmModal" class="modal-overlay" style="display:none;" onclick="closeModals(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 id="confirmModalTitle">Confirm Action</h3>
            <button type="button" class="modal-close" onclick="closeModals()">&times;</button>
        </div>
        <div class="modal-body" id="confirmModalBody">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModals()">Cancel</button>
            <form id="confirmForm" method="POST" action="" style="display:inline;">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" id="confirmStatus" value="">
                <button type="submit" class="btn btn-solid" id="confirmSubmit">Confirm</button>
            </form>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div id="cancelModal" class="modal-overlay" style="display:none;" onclick="closeModals(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Cancel Job</h3>
            <button type="button" class="modal-close" onclick="closeModals()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin:0 0 12px;color:var(--g6);">
                Are you sure you want to cancel this job?
            </p>
            <div id="cancelSummary"></div>
            <div style="margin-top:14px;">
                <label style="font-size:.85rem;font-weight:500;color:var(--g6);display:block;margin-bottom:4px;">
                    Reason (optional)
                </label>
                <textarea id="cancelReason" class="review-textarea" placeholder="Tell the client why…" style="min-height:70px;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModals()">Keep Job</button>
            <button type="button" class="btn btn-solid" style="background:#dc2626;" id="confirmCancelBtn" onclick="confirmCancel()">
                Yes, Cancel
            </button>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* ── Compact job cards ── */
.job-card-list { padding: 2px 0; }

.job-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--g1);
    cursor: pointer;
    transition: background .12s;
}
.job-card:last-child { border-bottom: none; }
.job-card:hover { background: var(--b0); }

.job-card-body { flex: 1; min-width: 0; }

.job-card-top {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 2px;
    flex-wrap: wrap;
}

.job-card-service {
    font-size: .92rem;
    font-weight: 600;
    color: var(--b9);
}

.job-card-time {
    font-size: .8rem;
    color: var(--g4);
    margin-left: auto;
    white-space: nowrap;
}
.job-card-time i { margin-right: 3px; }

.job-card-bottom {
    font-size: .8rem;
    color: var(--g4);
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 3px;
    line-height: 1.4;
}
.job-card-bottom i { margin-right: 3px; }
.meta-sep { color: var(--g2); }

.job-card-amount {
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

/* ── Job detail modal layout ── */
.modal-wide { max-width: 640px !important; }

.job-modal-layout {
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
    .job-modal-layout { grid-template-columns: 1fr; gap: 14px; }
    .job-card { padding: 10px 14px; gap: 10px; }
    .schedule-date-box {     width: 40px; height: 40px; }
    .schedule-day { font-size: .9rem; }
    .schedule-month { font-size: .55rem; }
    .job-card-time { margin-left: 0; width: 100%; }
}
</style>
@endpush

@push('scripts')
<script>
const jobs = @json($jobRequests);
let cancelIndex = null;

// ── Real-time booking notification ──
document.addEventListener('DOMContentLoaded', function () {
    var userId = {{ auth()->id() }};
    var checkCount = 0;
    var checkEcho = setInterval(function () {
        checkCount++;
        if (window.Echo) {
            clearInterval(checkEcho);
            window.Echo.private('user.' + userId)
                .listen('BookingCreated', function (e) {
                    var toast = document.createElement('div');
                    toast.className = 'toast-notification';
                    toast.innerHTML = '<strong>New Booking Request!</strong><br>' + e.client_name + ' — ' + e.service + '<br><small>' + e.scheduled_at + '</small>';
                    document.body.appendChild(toast);
                    setTimeout(function () { toast.remove(); }, 6000);
                    var badge = document.querySelector('.badge-dot');
                    if (badge) badge.style.display = '';
                });
        } else if (checkCount >= 50) {
            clearInterval(checkEcho);
        }
    }, 200);
});

// ── Filter dropdown ──
document.addEventListener('DOMContentLoaded', function () {
    var dd = document.getElementById('jobStatusDropdown');
    var menu = document.getElementById('jobStatusMenu');
    var trigger = document.getElementById('jobStatusTrigger');
    var label = document.getElementById('jobStatusLabel');
    var cards = document.querySelectorAll('.job-card');
    var list = document.querySelector('.job-card-list');
    var empty = list ? list.querySelector('.empty-state') : null;

    var statusIcons = {
        '': 'fa-solid fa-list',
        'new': 'fa-regular fa-clock',
        'accepted': 'fa-regular fa-handshake',
        'en_route': 'fa-solid fa-truck',
        'in_progress': 'fa-solid fa-spinner',
        'completed': 'fa-regular fa-circle-check',
    };
    var statusLabels = {
        '': 'All', 'new': 'New', 'accepted': 'Accepted',
        'en_route': 'En Route', 'in_progress': 'In Progress', 'completed': 'Completed',
    };

    function updateEmpty() {
        if (!empty) return;
        var visible = Array.from(cards).filter(function (c) { return c.style.display !== 'none'; }).length;
        empty.style.display = visible === 0 ? '' : 'none';
    }

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        menu.classList.toggle('open');
        var chev = trigger.querySelector('.cat-chev');
        if (chev) chev.style.transform = menu.classList.contains('open') ? 'rotate(180deg)' : '';
    });

    menu.querySelectorAll('.cat-option').forEach(function (opt) {
        opt.addEventListener('click', function (e) {
            e.stopPropagation();
            var filter = this.dataset.filter;
            menu.querySelectorAll('.cat-option').forEach(function (o) { o.classList.remove('active'); });
            this.classList.add('active');
            label.innerHTML = '<i class="' + statusIcons[filter] + '"></i> ' + statusLabels[filter];
            menu.classList.remove('open');
            var chev = trigger.querySelector('.cat-chev');
            if (chev) chev.style.transform = '';
            cards.forEach(function (card) {
                card.style.display = !filter || card.dataset.status === filter ? '' : 'none';
            });
            updateEmpty();
        });
    });

    document.addEventListener('click', function () {
        menu.classList.remove('open');
        var chev = trigger?.querySelector('.cat-chev');
        if (chev) chev.style.transform = '';
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeJobModal(); closeModals(); }
    });
});

// ── Job Detail Modal ──
function openJobModal(index) {
    var job = jobs[index];
    if (!job) return;

    var statusFlow = ['new', 'accepted', 'en_route', 'in_progress', 'completed'];
    var statusLabels = { 'new':'New', 'accepted':'Accepted', 'en_route':'En Route', 'in_progress':'In Progress', 'completed':'Completed' };
    var currentIdx = statusFlow.indexOf(job.raw_status);

    // Details
    var desc = job.description || 'No details provided.';
    var cancelReason = job.cancellation_reason || '';
    var cancelledAt = job.cancelled_at ? formatTime(job.cancelled_at) : '';
    document.getElementById('jobModalTitle').textContent = 'Job Details';
    document.getElementById('jobModalDetails').innerHTML =
        '<div class="detail-grid-compact">' +
            '<span class="detail-label">Reference</span>' +
            '<span class="detail-value">' + (job.booking_ref || 'BK-' + String(job.id).padStart(5,'0')) + '</span>' +
            '<span class="detail-label">Client</span>' +
            '<span class="detail-value">' + job.client + '</span>' +
            '<span class="detail-label">Service</span>' +
            '<span class="detail-value">' + job.service + '</span>' +
            '<span class="detail-label">Schedule</span>' +
            '<span class="detail-value">' + job.date + '</span>' +
            '<span class="detail-label">Location</span>' +
            '<span class="detail-value">' + job.location + '</span>' +
            '<span class="detail-label">Amount</span>' +
            '<span class="detail-value" style="font-weight:600;">₱' + Number(job.price).toLocaleString() + '</span>' +
            '<span class="detail-label">Description</span>' +
            '<span class="detail-value">' + desc + '</span>' +
            (cancelledAt ? '<span class="detail-label">Cancelled At</span><span class="detail-value">' + cancelledAt + '</span>' : '') +
            (cancelReason ? '<span class="detail-label">Cancel Reason</span><span class="detail-value">' + cancelReason + '</span>' : '') +
        '</div>';

    // Timeline
    var timelineHtml = '<div class="timeline">';
    for (var si = 0; si < statusFlow.length; si++) {
        var status = statusFlow[si];
        var ts = job.status_history ? job.status_history[status] : null;
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
        timelineHtml +=     '<span class="timeline-status">' + statusLabels[status] + '</span>';
        timelineHtml +=     timeHtml;
        timelineHtml +=   '</div>';
        timelineHtml += '</div>';
    }
    timelineHtml += '</div>';

    // Add cancelled step if applicable
    if (job.raw_status === 'cancelled') {
        var cancelledTs = job.cancelled_at || null;
        var timeHtml2 = cancelledTs ? '<span class="timeline-time">' + formatTime(cancelledTs) + '</span>' : '';
        timelineHtml += '<div class="timeline-item current" style="color:var(--color-danger,#dc3545);">';
        timelineHtml +=   '<div class="timeline-dot-col">';
        timelineHtml +=     '<div class="timeline-dot current" style="background:var(--color-danger,#dc3545);border-color:var(--color-danger,#dc3545);">✕</div>';
        timelineHtml +=   '</div>';
        timelineHtml +=   '<div class="timeline-content">';
        timelineHtml +=     '<span class="timeline-status">Cancelled</span>';
        timelineHtml +=     timeHtml2;
        timelineHtml +=   '</div>';
        timelineHtml += '</div>';
    }

    document.getElementById('jobModalTimeline').innerHTML = timelineHtml;

    // Footer
    var footer = document.getElementById('jobModalFooter');
    if (job.raw_status === 'cancelled' || job.raw_status === 'completed') {
        footer.innerHTML = '<button type="button" class="btn btn-outline" onclick="closeJobModal()">Close</button>';
    } else {
        var nextLabels = { 'new':'Accept', 'accepted':'Mark En Route', 'en_route':'Start Job', 'in_progress':'Complete' };
        var nextLabel = nextLabels[job.raw_status] || 'Next';
        footer.innerHTML =
            '<button type="button" class="btn btn-outline" onclick="closeJobModal(); showCancelModal(' + index + ')">Cancel Job</button>' +
            '<button type="button" class="btn btn-solid" onclick="closeJobModal(); showConfirmModal(' + index + ')">' + nextLabel + '</button>';
    }

    document.getElementById('jobModal').style.display = 'flex';
}

function closeJobModal(e) {
    if (e && e.target && !e.target.closest) return;
    document.getElementById('jobModal').style.display = 'none';
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

// ── Confirm / Cancel ──
function closeModals(e) {
    if (e && e.target && !e.target.closest) return;
    document.getElementById('confirmModal').style.display = 'none';
    document.getElementById('cancelModal').style.display = 'none';
    cancelIndex = null;
}

function showConfirmModal(index) {
    var job = jobs[index];
    if (!job) return;
    var step = { 'new':{a:'accepted',l:'Accept',v:'accepting'}, 'accepted':{a:'en_route',l:'Mark as En Route',v:'marking as en route'}, 'en_route':{a:'in_progress',l:'Start',v:'starting'}, 'in_progress':{a:'completed',l:'Complete',v:'completing'} }[job.raw_status];
    if (!step) return;
    var titles = { 'new':'Accept Job Request', 'accepted':'Mark as En Route', 'en_route':'Start Job', 'in_progress':'Complete Job' };
    var agreeHtml = (job.raw_status === 'new')
        ? '<div class="agreement-check-wrap"><label class="agreement-check"><input type="checkbox" id="agree-terms-worker"><span>I agree to the <a href="{{ url('/terms') }}" target="_blank">Terms of Service</a> and confirm the details above to accept this booking.</span></label></div>'
        : '';
    document.getElementById('confirmModalTitle').textContent = titles[job.raw_status] || 'Confirm';
    document.getElementById('confirmModalBody').innerHTML =
        '<p style="margin:0 0 12px;color:var(--g6);">You are about to <strong>' + step.v + '</strong> the following job:</p>' +
        '<div class="detail-grid-compact">' +
            '<span class="detail-label">Client</span><span class="detail-value">' + job.client + '</span>' +
            '<span class="detail-label">Service</span><span class="detail-value">' + job.service + '</span>' +
            '<span class="detail-label">Schedule</span><span class="detail-value">' + job.date + '</span>' +
            '<span class="detail-label">Amount</span><span class="detail-value">₱' + Number(job.price).toLocaleString() + '</span>' +
        '</div>' +
        '<p style="margin:14px 0 0;font-size:.82rem;color:var(--g4);">This action cannot be undone.</p>' +
        agreeHtml;
    document.getElementById('confirmForm').action = '{{ route("worker.jobs.status", "__ID__") }}'.replace('__ID__', job.id);
    document.getElementById('confirmStatus').value = step.a;
    document.getElementById('confirmSubmit').textContent = step.l;
    document.getElementById('confirmForm').onsubmit = function(e) {
        if (agreeHtml && !document.getElementById('agree-terms-worker').checked) {
            e.preventDefault();
            alert('Please agree to the Service Agreement before accepting.');
            return false;
        }
    };
    document.getElementById('confirmModal').style.display = 'flex';
}

function showCancelModal(index) {
    var job = jobs[index];
    if (!job) return;
    cancelIndex = index;
    document.getElementById('cancelSummary').innerHTML =
        '<div class="detail-grid-compact">' +
            '<span class="detail-label">Client</span><span class="detail-value">' + job.client + '</span>' +
            '<span class="detail-label">Service</span><span class="detail-value">' + job.service + '</span>' +
            '<span class="detail-label">Schedule</span><span class="detail-value">' + job.date + '</span>' +
            '<span class="detail-label">Amount</span><span class="detail-value">₱' + Number(job.price).toLocaleString() + '</span>' +
        '</div>';
    document.getElementById('cancelReason').value = '';
    document.getElementById('cancelModal').style.display = 'flex';
}

function confirmCancel() {
    if (cancelIndex === null) return;
    var job = jobs[cancelIndex];
    var reason = document.getElementById('cancelReason').value.trim();
    document.getElementById('confirmCancelBtn').disabled = true;
    document.getElementById('confirmCancelBtn').textContent = 'Cancelling…';
    fetch('/worker/jobs/' + job.id + '/cancel', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ reason: reason || 'Cancelled by worker' }),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'Failed to cancel job.'); }
    })
    .catch(function () { alert('Something went wrong.'); })
    .finally(function () {
        document.getElementById('confirmCancelBtn').disabled = false;
        document.getElementById('confirmCancelBtn').textContent = 'Yes, Cancel';
    });
}

// Focus handler: ?focus=ID opens the job modal on load
(function() {
    var params = new URLSearchParams(window.location.search);
    var focusId = params.get('focus');
    if (focusId) {
        for (var i = 0; i < jobs.length; i++) {
            if (String(jobs[i].id) === String(focusId)) {
                openJobModal(i);
                break;
            }
        }
    }
})();
</script>
@endpush

@push('styles')
<style>
.agreement-check-wrap {
    margin-top: 12px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid var(--g1);
    border-radius: 8px;
}
.agreement-check {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: .78rem;
    color: var(--g6);
    line-height: 1.45;
    cursor: pointer;
}
.agreement-check input[type="checkbox"] {
    margin-top: 2px;
    flex-shrink: 0;
}
.agreement-check a {
    color: var(--b6);
    text-decoration: underline;
}
</style>
@endpush
