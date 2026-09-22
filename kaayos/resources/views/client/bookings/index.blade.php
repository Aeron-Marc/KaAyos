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

@section('skeleton')
    <div class="sp-tabs">
        <div class="skeleton" style="height:32px;width:50px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:60px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:70px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:80px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:90px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:65px;border-radius:99px;"></div>
    </div>
    <div class="sp-panel">
        <div class="skeleton skeleton-title" style="width:220px;margin-bottom:20px;"></div>
        <div style="display:flex;gap:16px;margin-bottom:16px;">
            <div class="skeleton" style="width:60px;height:60px;border-radius:12px;flex-shrink:0;"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:50%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:30%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:70%;"></div>
            </div>
        </div>
        <div style="display:flex;gap:16px;margin-bottom:16px;">
            <div class="skeleton" style="width:60px;height:60px;border-radius:12px;flex-shrink:0;"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:45%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:35%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:60%;"></div>
            </div>
        </div>
        <div style="display:flex;gap:16px;">
            <div class="skeleton" style="width:60px;height:60px;border-radius:12px;flex-shrink:0;"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:55%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:25%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:65%;"></div>
            </div>
        </div>
    </div>
@endsection

@section('content')

<div class="cat-dropdown" id="bookingStatusDropdown">
    <button class="cat-dropdown-trigger" id="bookingStatusTrigger">
        <span class="cat-dropdown-label" id="bookingStatusLabel">
            <i class="fa-solid fa-list"></i> All
        </span>
        <i class="fa-solid fa-chevron-down cat-chev"></i>
    </button>
    <div class="cat-dropdown-menu" id="bookingStatusMenu">
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
                        @if($booking['raw_status'] === 'en_route')
                            <div style="display:inline-flex;align-items:center;gap:6px;">
                                <span class="status-badge status-active" style="background:#dbeafe;color:#1d4ed8;border-color:#bfdbfe;">
                                    <i class="fa-solid fa-truck-fast" style="margin-right:3px;"></i> En Route{{ !empty($booking['estimated_transit_minutes']) ? ' (~' . $booking['estimated_transit_minutes'] . 'm)' : '' }}
                                </span>
                                <button type="button" class="btn btn-sm btn-solid" style="background:#2563eb;color:#fff;padding:2px 8px;font-size:.72rem;border-radius:99px;gap:4px;" onclick="event.stopPropagation(); openLiveTrackingModal({{ $booking['id'] }})">
                                    <span class="live-pulse-dot" style="width:6px;height:6px;background:#fff;"></span> Track
                                </button>
                            </div>
                        @else
                            <span class="status-badge {{ $statusClass }}">{{ $statusLabelMap[$booking['raw_status']] ?? $booking['status'] }}</span>
                        @endif
                        @if(($booking['scope_amendment_status'] ?? '') === 'pending')
                            <span class="badge" style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:99px;font-size:.72rem;font-weight:600;"><i class="fa-solid fa-file-pen"></i> Scope Review</span>
                        @endif
                        @if(($booking['team_status'] ?? '') === 'suggested')
                            <span class="badge" style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:99px;font-size:.72rem;font-weight:600;"><i class="fa-solid fa-users"></i> Team Proposal</span>
                        @endif
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

{{-- Report Modal --}}
<div id="reportModal" class="modal-overlay" style="display:none;" onclick="closeReportModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Report Worker</h3>
            <button type="button" class="modal-close" onclick="closeReportModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin:0 0 12px;color:var(--g6);font-size:.9rem;">
                Describe your issue with this worker. An admin will review your report.
            </p>
            <div id="reportSummary" style="margin-bottom:14px;padding:10px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.85rem;color:#991b1b;"></div>
            <div style="margin-top:14px;">
                <label style="font-size:.85rem;font-weight:500;color:var(--g6);display:block;margin-bottom:4px;">
                    Reason <span style="color:var(--d10)">*</span>
                </label>
                <textarea id="reportReason" class="review-textarea" placeholder="Explain what happened… (min. 10 characters)" style="min-height:100px;"></textarea>
                <div id="reportError" style="display:none;margin-top:6px;font-size:.8rem;color:var(--d10);"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeReportModal()">Cancel</button>
            <button type="button" class="btn btn-solid" style="background:#dc2626;" id="reportSubmitBtn" onclick="submitReport()">
                <i class="fa-solid fa-flag"></i> Submit Report
            </button>
        </div>
    </div>
</div>

{{-- Live Tracking Modal --}}
<div id="liveTrackingModal" class="modal-overlay" style="display:none;" onclick="closeLiveTrackingModal(event)">
    <div class="modal-box modal-wide" onclick="event.stopPropagation()" style="max-width:720px;padding:0;overflow:hidden;border-radius:14px;">
        <div class="modal-header" style="padding:16px 20px;border-bottom:1px solid var(--g2,#e2e8f0);background:#fff;">
            <div style="display:flex;align-items:center;gap:10px;">
                <span class="live-pulse-dot" style="background:#22c55e;"></span>
                <div>
                    <h3 style="margin:0;font-size:1.05rem;color:var(--g8,#1e293b);" id="trackWorkerTitle">Live Worker Tracking</h3>
                    <div style="font-size:.78rem;color:var(--g5,#64748b);" id="trackWorkerSubtitle">Real-time GPS transit monitoring</div>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeLiveTrackingModal()">&times;</button>
        </div>

        {{-- Live Status Bar --}}
        <div id="trackStatusBar" style="display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);padding:12px 20px;border-bottom:1px solid #bfdbfe;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:700;flex-shrink:0;">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div>
                    <div style="font-weight:700;color:#1e3a8a;font-size:.92rem;" id="trackWorkerName">Worker</div>
                    <div style="font-size:.78rem;color:#3b82f6;" id="trackServiceName">Service</div>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:1.2rem;font-weight:800;color:#1d4ed8;" id="trackEta">Calculating...</div>
                <div style="font-size:.78rem;color:#3b82f6;" id="trackDistance">Connecting...</div>
            </div>
        </div>

        {{-- Arrival Alert Container --}}
        <div id="trackArrivalNotice" style="display:none;background:#dcfce7;border-bottom:1px solid #86efac;padding:12px 20px;color:#15803d;font-size:.9rem;font-weight:600;align-items:center;gap:10px;">
            <i class="fa-solid fa-circle-check" style="font-size:1.2rem;"></i>
            <div>
                <div>Worker has arrived at your location!</div>
                <div style="font-size:.78rem;font-weight:400;color:#166534;">Job status will update to In Progress once work begins.</div>
            </div>
        </div>

        {{-- Interactive Map Container --}}
        <div style="position:relative;width:100%;height:360px;background:#f1f5f9;">
            <div id="liveTrackingMap" style="width:100%;height:100%;z-index:1;"></div>
            
            {{-- Fit Route button --}}
            <button type="button" onclick="recenterTrackingMap()" title="Fit entire route" style="position:absolute;top:12px;right:12px;z-index:999;background:rgba(255,255,255,0.92);backdrop-filter:blur(4px);border:1px solid #cbd5e1;border-radius:8px;padding:6px 10px;font-size:.75rem;font-weight:600;color:#1e293b;box-shadow:0 2px 6px rgba(0,0,0,0.12);display:inline-flex;align-items:center;gap:5px;cursor:pointer;">
                <i class="fa-solid fa-expand" style="color:#2563eb;"></i> Fit Route
            </button>

            {{-- Ping info badge overlay --}}
            <div id="trackPingBadge" style="position:absolute;bottom:12px;left:12px;z-index:999;background:rgba(255,255,255,0.94);backdrop-filter:blur(4px);padding:5px 12px;border-radius:20px;font-size:.74rem;color:#475569;box-shadow:0 2px 8px rgba(0,0,0,0.12);border:1px solid #e2e8f0;display:flex;align-items:center;gap:6px;">
                <span class="live-pulse-dot" style="width:7px;height:7px;"></span>
                <span id="trackPingTime">Connecting...</span>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="modal-footer" style="padding:12px 20px;border-top:1px solid var(--g2,#e2e8f0);background:#fff;display:flex;justify-content:space-between;align-items:center;">
            <div style="font-size:.8rem;color:var(--g5,#64748b);" id="trackDestAddress">
                <i class="fa-solid fa-location-dot" style="color:#ef4444;margin-right:4px;"></i> Destination
            </div>
            <div style="display:flex;gap:8px;">
                <a id="trackCallBtn" href="#" class="btn btn-sm btn-outline" style="display:none;align-items:center;gap:5px;">
                    <i class="fa-solid fa-phone"></i> Call
                </a>
                <button type="button" class="btn btn-sm btn-outline" onclick="closeLiveTrackingModal()">Close</button>
            </div>
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

.btn-report {
    color: #dc2626 !important;
    border-color: #fca5a5 !important;
}
.live-pulse-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: livePulse 1.8s infinite;
    display: inline-block;
    flex-shrink: 0;
}
@keyframes livePulse {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.live-worker-marker-wrap {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}
.worker-radar-ring {
    position: absolute;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(37, 99, 235, 0.25);
    animation: radarRipple 2s ease-out infinite;
}
@keyframes radarRipple {
    0% { transform: scale(0.5); opacity: 1; }
    100% { transform: scale(1.6); opacity: 0; }
}
.live-worker-icon {
    position: relative;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #2563eb;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .88rem;
    box-shadow: 0 2px 8px rgba(37,99,235,0.45);
    border: 2px solid #fff;
    z-index: 2;
}
.live-dest-marker-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
}
.live-dest-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #dc2626;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .8rem;
    box-shadow: 0 2px 8px rgba(220,38,38,0.45);
    border: 2px solid #fff;
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
    var userId = {{ auth()->id() }};
    var checkCount = 0;
    var checkEcho = setInterval(function () {
        checkCount++;
        if (window.Echo) {
            clearInterval(checkEcho);
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
                })
                .listen('JobCompletionStatusUpdated', function (e) {
                    if (window.showToast) {
                        window.showToast(e.fully_completed ? 'Booking #' + e.booking_id + ' is fully completed!' : 'Worker marked Booking #' + e.booking_id + ' as complete. Please review and confirm!', 'info');
                    }
                    setTimeout(function () { location.reload(); }, 1200);
                });
        } else if (checkCount >= 50) {
            clearInterval(checkEcho);
        }
    }, 200);
});

// ── Filter dropdown ──
document.addEventListener('DOMContentLoaded', function () {
    var dd = document.getElementById('bookingStatusDropdown');
    var menu = document.getElementById('bookingStatusMenu');
    var trigger = document.getElementById('bookingStatusTrigger');
    var label = document.getElementById('bookingStatusLabel');
    var cards = document.querySelectorAll('.booking-card');
    var list = document.querySelector('.booking-card-list');
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
        '': 'All',
        'new': 'New',
        'accepted': 'Accepted',
        'en_route': 'En Route',
        'in_progress': 'In Progress',
        'completed': 'Completed',
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
        if (e.key === 'Escape') { closeBookingModal(); closeModals(); }
    });
});

// ── Booking Detail Modal ──
function openBookingModal(index) {
    var b = bookings[index];
    if (!b) return;

    var statusFlow = ['new', 'accepted', 'en_route', 'in_progress', 'completed'];
    var currentIdx = statusFlow.indexOf(b.raw_status);

    var notes = b.notes || 'No details provided.';
    var cancelReason = b.cancellation_reason || '';
    var cancelledAt = b.cancelled_at ? formatTime(b.cancelled_at) : '';

    var enRouteBanner = (b.raw_status === 'en_route')
        ? '<div class="en-route-banner" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 14px;margin-bottom:14px;color:#1e40af;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">' +
            '<div style="display:flex;align-items:center;gap:10px;">' +
                '<span class="live-pulse-dot" style="background:#2563eb;"></span>' +
                '<div>' +
                    '<div style="font-weight:700;font-size:.9rem;color:#1e3a8a;">Worker is on the way!</div>' +
                    '<div style="font-size:.8rem;color:#2563eb;margin-top:2px;">' +
                        b.worker + ' is currently en route' + (b.estimated_transit_minutes ? ' (~' + b.estimated_transit_minutes + ' mins transit)' : '') + '.' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-solid" style="background:#2563eb;padding:5px 12px;font-size:.8rem;display:inline-flex;align-items:center;gap:6px;" onclick="closeBookingModal(); openLiveTrackingModal(' + b.id + ')">' +
                '<i class="fa-solid fa-location-crosshairs"></i> Track Live Map' +
            '</button>' +
          '</div>'
        : '';

    var propTypeLabels = {
        'residential': 'Residential',
        'commercial': 'Commercial / Business',
        'property_manager': 'Property Management',
        'tenant': 'Tenant / Renter'
    };
    var propType = propTypeLabels[b.property_type] || (b.property_type ? b.property_type.replace('_',' ') : 'Residential');
    var pricingType = (b.pricing_type === 'hourly') ? 'Hourly (est. ' + (b.estimated_duration_hours || 2) + 'h)' : 'Fixed Price';
    var complexityLabels = {
        'standard': '<span class="badge" style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:99px;font-size:.72rem;">Standard (1.0x)</span>',
        'complex': '<span class="badge" style="background:#fef3c7;color:#b45309;padding:2px 8px;border-radius:99px;font-size:.72rem;">Complex (1.2x)</span>',
        'hazardous': '<span class="badge" style="background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:99px;font-size:.72rem;">Hazardous (1.5x)</span>'
    };
    var complexityHtml = complexityLabels[b.complexity_level] || '<span class="badge" style="background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:99px;font-size:.72rem;">Standard</span>';

    // Work Timer Section
    var timerSection = '';
    if (b.work_started_at) {
        var timerText = b.work_ended_at ? 'Work completed at ' + b.work_ended_at : 'Work in progress since ' + b.work_started_at;
        timerSection = '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;margin-top:12px;font-size:.8rem;color:#1e293b;">' +
            '<i class="fa-solid fa-stopwatch" style="color:#2563eb;margin-right:6px;"></i><strong>On-Site Timer:</strong> ' + timerText +
        '</div>';
    }

    // Scope Amendment Card
    var scopeCard = '';
    if (b.scope_amendment_status === 'pending') {
        scopeCard = '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 14px;margin-top:12px;">' +
            '<div style="font-weight:700;color:#92400e;font-size:.88rem;display:flex;align-items:center;gap:6px;">' +
                '<i class="fa-solid fa-file-pen"></i> Worker Proposed Scope Revision' +
            '</div>' +
            '<div style="font-size:.82rem;color:#78350f;margin-top:4px;">' +
                '<strong>Reason:</strong> ' + (b.scope_amendment_notes || 'On-site scope adjustment') +
            '</div>' +
            '<div style="font-size:.85rem;font-weight:700;color:#92400e;margin-top:4px;">' +
                'Proposed Total Price: ₱' + Number(b.scope_amendment_price).toLocaleString() +
            '</div>' +
            '<div style="display:flex;gap:8px;margin-top:10px;">' +
                '<button type="button" class="btn btn-sm btn-solid" style="background:#16a34a;padding:5px 12px;font-size:.8rem;" onclick="respondScopeAmendment(' + b.id + ', \'approve\')">' +
                    '<i class="fa-solid fa-check"></i> Approve Revision' +
                '</button>' +
                '<button type="button" class="btn btn-sm btn-outline" style="padding:5px 12px;font-size:.8rem;" onclick="respondScopeAmendment(' + b.id + ', \'decline\')">' +
                    'Decline' +
                '</button>' +
            '</div>' +
        '</div>';
    } else if (b.scope_amendment_status === 'approved') {
        scopeCard = '<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:8px 12px;margin-top:12px;font-size:.8rem;color:#065f46;">' +
            '<strong><i class="fa-solid fa-circle-check"></i> Scope Revision Approved:</strong> ₱' + Number(b.price).toLocaleString() +
        '</div>';
    }

    // Team / Peer Work Card
    var teamCard = '';
    if (b.team_status === 'suggested') {
        var crewHtml = '';
        if (b.crew && b.crew.length > 0) {
            crewHtml = '<div style="margin-top:8px;display:flex;flex-direction:column;gap:6px;">' +
                b.crew.map(function(c) {
                    var payoutText = c.payout_amount > 0 ? '₱' + Number(c.payout_amount).toLocaleString() : '';
                    return '<div style="background:#fff;padding:6px 10px;border-radius:6px;border:1px solid #dbeafe;display:flex;justify-content:space-between;align-items:center;font-size:.8rem;">' +
                        '<span><i class="fa-solid fa-user-gear" style="color:#3b82f6;margin-right:6px;"></i><strong>' + c.worker_name + '</strong> <span style="color:#64748b;">(' + c.role + ')</span></span>' +
                        (payoutText ? '<span style="font-weight:600;color:#2563eb;">' + payoutText + '</span>' : '') +
                    '</div>';
                }).join('') +
            '</div>';
        } else {
            crewHtml = '<div style="font-size:.8rem;color:#2563eb;margin-top:6px;"><strong>Recommended Crew:</strong> Peer skilled worker</div>';
        }

        teamCard = '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 14px;margin-top:12px;">' +
            '<div style="font-weight:700;color:#1e40af;font-size:.88rem;display:flex;align-items:center;gap:6px;">' +
                '<i class="fa-solid fa-users"></i> Worker Recommends Team for this Job' +
            '</div>' +
            '<div style="font-size:.82rem;color:#1e3a8a;margin-top:4px;">' +
                '<strong>Reason:</strong> ' + (b.team_justification || 'Additional manpower recommended') +
            '</div>' +
            crewHtml +
            '<div style="display:flex;gap:8px;margin-top:10px;">' +
                '<button type="button" class="btn btn-sm btn-solid" style="background:#2563eb;padding:5px 12px;font-size:.8rem;" onclick="respondTeamSuggestion(' + b.id + ', \'approve\')">' +
                    '<i class="fa-solid fa-check"></i> Approve Team' +
                '</button>' +
                '<button type="button" class="btn btn-sm btn-outline" style="padding:5px 12px;font-size:.8rem;" onclick="respondTeamSuggestion(' + b.id + ', \'decline\')">' +
                    'Decline (Solo Worker)' +
                '</button>' +
            '</div>' +
        '</div>';
    } else if (b.team_status === 'client_approved') {
        var approvedCrewHtml = '';
        if (b.crew && b.crew.length > 0) {
            approvedCrewHtml = '<div style="margin-top:6px;display:flex;flex-direction:column;gap:4px;">' +
                b.crew.map(function(c) {
                    var statusBadge = c.status === 'accepted'
                        ? '<span style="color:#16a34a;font-weight:600;"><i class="fa-solid fa-circle-check"></i> Joined</span>'
                        : '<span style="color:#d97706;font-weight:600;"><i class="fa-solid fa-clock"></i> Invited</span>';
                    return '<div style="font-size:.8rem;display:flex;justify-content:space-between;align-items:center;background:#fff;padding:4px 8px;border-radius:5px;border:1px solid #a7f3d0;">' +
                        '<span><strong>' + c.worker_name + '</strong> <span style="color:#047857;">(' + c.role + ')</span></span>' +
                        statusBadge +
                    '</div>';
                }).join('') +
            '</div>';
        } else {
            approvedCrewHtml = ' Assigned';
        }

        teamCard = '<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:8px 12px;margin-top:12px;font-size:.8rem;color:#065f46;">' +
            '<strong><i class="fa-solid fa-users-check"></i> Approved Team:</strong>' + approvedCrewHtml +
        '</div>';
    }

    document.getElementById('bookingModalTitle').textContent = 'Booking Details';
    document.getElementById('bookingModalDetails').innerHTML =
        enRouteBanner +
        '<div class="detail-grid-compact">' +
            '<span class="detail-label">Reference</span>' +
            '<span class="detail-value">' + (b.booking_ref || 'BK-' + String(b.id).padStart(5,'0')) + '</span>' +
            '<span class="detail-label">Worker</span>' +
            '<span class="detail-value">' + b.worker + '</span>' +
            '<span class="detail-label">Property</span>' +
            '<span class="detail-value">' + propType + '</span>' +
            '<span class="detail-label">Billing</span>' +
            '<span class="detail-value">' + pricingType + '</span>' +
            '<span class="detail-label">Complexity</span>' +
            '<span class="detail-value">' + complexityHtml + '</span>' +
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
            (cancelledAt ? '<span class="detail-label">Cancelled At</span><span class="detail-value">' + cancelledAt + '</span>' : '') +
            (cancelReason ? '<span class="detail-label">Cancel Reason</span><span class="detail-value">' + cancelReason + '</span>' : '') +
        '</div>' +
        timerSection +
        scopeCard +
        teamCard;

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

    // Add cancelled step if applicable
    if (b.raw_status === 'cancelled') {
        var cancelledTs = b.cancelled_at || null;
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

    document.getElementById('bookingModalTimeline').innerHTML = timelineHtml;

    // Footer
    var footer = document.getElementById('bookingModalFooter');
    if (b.raw_status === 'cancelled' || b.raw_status === 'completed') {
        var extra = '';
        if (b.raw_status === 'completed') {
            extra = '<a href="{{ route('client.reviews') }}" class="btn btn-outline"><i class="fa-regular fa-star" aria-hidden="true"></i> Leave Review</a>' +
                    '<a href="{{ route('client.testimonials.create') }}" class="btn btn-outline"><i class="fa-solid fa-quote-left" aria-hidden="true"></i> Share a Testimonial</a>' +
                    '<button type="button" class="btn btn-outline btn-report" onclick="showReportModal(' + index + ')"><i class="fa-solid fa-flag"></i> Report Worker</button>';
        }
        footer.innerHTML = extra + '<button type="button" class="btn btn-outline" onclick="closeBookingModal()">Close</button>';
    } else if (b.raw_status === 'in_progress') {
        // Show completion workflow buttons
        var completionHtml = '';
        
        if (b.completion_status.is_pending) {
            // Completion is pending - show appropriate message and button
            if (b.confirmed_by_client_at) {
                completionHtml = '<p style="font-size:0.9rem;color:var(--g5);margin:0 0 12px;">✓ You confirmed completion. Waiting for worker to confirm…</p>';
            } else if (b.completion_requested_by && b.completion_requested_by !== {{ auth()->id() }}) {
                completionHtml = '<p style="font-size:0.9rem;color:var(--b6);margin:0 0 12px;"><i class="fa-solid fa-circle-info"></i> Worker marked job as complete. Review and confirm completion below.</p>' +
                    '<div class="btn-group" style="display:flex;gap:8px;">' +
                    '<button type="button" class="btn btn-outline" onclick="closeBookingModal()">Review Later</button>' +
                    '<button type="button" class="btn btn-solid" onclick="confirmJobComplete(' + index + ')"><i class="fa-regular fa-circle-check"></i> Confirm Complete</button>' +
                    '</div>';
            } else {
                completionHtml = '<p style="font-size:0.9rem;color:var(--g5);margin:0 0 12px;">✓ Marked complete. Waiting for worker to confirm…</p>';
            }
        } else {
            // Not yet marked complete - show "Mark as Complete" button
            completionHtml = '<div class="btn-group" style="display:flex;gap:8px;flex-wrap:wrap;">' +
                '<button type="button" class="btn btn-outline" onclick="closeBookingModal(); showCancelModal(' + index + ')"><i class="fa-regular fa-trash"></i> Cancel</button>' +
                '<a href="{{ route('client.messages.start') }}?worker_id=' + b.worker_id + '" class="btn btn-outline"><i class="fa-regular fa-comment"></i> Message</a>' +
                '<button type="button" class="btn btn-solid" onclick="markJobComplete(' + index + ')"><i class="fa-regular fa-circle-check"></i> Mark Complete</button>' +
                '</div>';
        }
        
        footer.innerHTML = completionHtml;
    } else {
        var trackBtn = (b.raw_status === 'en_route')
            ? '<button type="button" class="btn btn-solid" style="background:#2563eb;" onclick="closeBookingModal(); openLiveTrackingModal(' + b.id + ')"><i class="fa-solid fa-location-crosshairs"></i> Track Live</button>'
            : '';
        footer.innerHTML =
            '<button type="button" class="btn btn-outline" onclick="closeBookingModal(); showCancelModal(' + index + ')">Cancel Booking</button>' +
            '<a href="{{ route('client.messages.start') }}?worker_id=' + b.worker_id + '" class="btn btn-outline"><i class="fa-regular fa-comment" aria-hidden="true"></i> Message</a>' +
            trackBtn;
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

// ── Report ──
var reportIndexHolder = { value: null };

function showReportModal(index) {
    var b = bookings[index];
    if (!b) return;
    reportIndexHolder.value = index;
    document.getElementById('reportSummary').innerHTML =
        '<strong>Worker:</strong> ' + b.worker + '<br>' +
        '<strong>Service:</strong> ' + b.service + '<br>' +
        '<strong>Date:</strong> ' + b.date;
    document.getElementById('reportReason').value = '';
    document.getElementById('reportError').style.display = 'none';
    document.getElementById('reportSubmitBtn').disabled = false;
    document.getElementById('reportSubmitBtn').innerHTML = '<i class="fa-solid fa-flag"></i> Submit Report';
    document.getElementById('reportModal').style.display = 'flex';
}

function closeReportModal(e) {
    if (e && e.target && !e.target.closest) return;
    document.getElementById('reportModal').style.display = 'none';
    reportIndexHolder.value = null;
}

function submitReport() {
    if (reportIndexHolder.value === null) return;
    var b = bookings[reportIndexHolder.value];
    var reason = document.getElementById('reportReason').value.trim();
    var errorEl = document.getElementById('reportError');

    if (reason.length < 10) {
        errorEl.textContent = 'Please provide at least 10 characters describing the issue.';
        errorEl.style.display = 'block';
        return;
    }

    var btn = document.getElementById('reportSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting…';

    fetch('/client/bookings/' + b.id + '/report', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ reason: reason }),
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success) {
            closeReportModal();
            alert(data.message || 'Report submitted.');
        } else {
            errorEl.textContent = data.message || 'Failed to submit report.';
            errorEl.style.display = 'block';
        }
    })
    .catch(function () {
        errorEl.textContent = 'Something went wrong. Please try again.';
        errorEl.style.display = 'block';
    })
    .finally(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-flag"></i> Submit Report';
    });
}

// ── Job Completion Confirmation ──
function markJobComplete(index) {
    var b = bookings[index];
    if (!b) return;
    
    if (confirm('Mark this job as complete? The worker will need to confirm.')) {
        var btn = document.activeElement;
        if (btn) btn.disabled = true;
        var originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
        }
        
        fetch('/client/bookings/' + b.id + '/mark-complete', {
        var url = window.location.origin + '{{ route("client.bookings.mark-complete", "__ID__", false) }}'.replace('__ID__', b.id);
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                location.reload();
        .then(function (r) { return r.json().then(function(data){ return { ok: r.ok, data: data }; }); })
        .then(function (res) {
            if (res.ok && res.data.success) {
                if (window.showToast) window.showToast(res.data.message || 'Marked as complete. Waiting for worker to confirm.', 'success');
                setTimeout(function() { location.reload(); }, 600);
            } else {
                alert(data.message || 'Failed to mark job as complete.');
                if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
                var msg = (res.data && res.data.message) ? res.data.message : 'Failed to mark job as complete.';
                if (window.showToast) window.showToast(msg, 'error');
                else alert(msg);
            }
        })
        .catch(function () { 
            alert('Something went wrong.'); 
        })
        .finally(function () {
            if (btn) btn.disabled = false;
            if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
            if (window.showToast) window.showToast('Network error while marking job complete.', 'error');
            else alert('Something went wrong.'); 
        });
    }
}

function confirmJobComplete(index) {
    var b = bookings[index];
    if (!b) return;
    
    if (confirm('Confirm job completion? This will finalize the booking.')) {
        var btn = document.activeElement;
        if (btn) btn.disabled = true;
        var originalHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
        }
        
        fetch('/client/bookings/' + b.id + '/confirm-complete', {
        var url = window.location.origin + '{{ route("client.bookings.confirm-complete", "__ID__", false) }}'.replace('__ID__', b.id);
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                location.reload();
        .then(function (r) { return r.json().then(function(data){ return { ok: r.ok, data: data }; }); })
        .then(function (res) {
            if (res.ok && res.data.success) {
                if (window.showToast) window.showToast(res.data.message || 'Job completion confirmed!', 'success');
                setTimeout(function() { location.reload(); }, 600);
            } else {
                alert(data.message || 'Failed to confirm job completion.');
                if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
                var msg = (res.data && res.data.message) ? res.data.message : 'Failed to confirm job completion.';
                if (window.showToast) window.showToast(msg, 'error');
                else alert(msg);
            }
        })
        .catch(function () { 
            alert('Something went wrong.'); 
        })
        .finally(function () {
            if (btn) btn.disabled = false;
            if (btn) { btn.disabled = false; btn.innerHTML = originalHtml; }
            if (window.showToast) window.showToast('Network error while confirming job complete.', 'error');
            else alert('Something went wrong.'); 
        });
    }
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

// ── Live Tracking System ──
let trackingBookingId = null;
let trackingPollInterval = null;
let trackingMapInstance = null;
let trackingWorkerMarker = null;
let trackingDestMarker = null;
let trackingRouteLine = null;
let hasFittedTrackingBounds = false;

function loadLeaflet(cb) {
    if (window.L) { cb(); return; }
    var link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
    document.head.appendChild(link);
    var script = document.createElement('script');
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    script.onload = cb;
    document.head.appendChild(script);
}

function openLiveTrackingModal(bookingId) {
    trackingBookingId = bookingId;
    hasFittedTrackingBounds = false;
    var modal = document.getElementById('liveTrackingModal');
    if (!modal) return;
    modal.style.display = 'flex';

    // Reset status elements
    document.getElementById('trackArrivalNotice').style.display = 'none';
    document.getElementById('trackStatusBar').style.display = 'flex';
    document.getElementById('trackEta').textContent = 'Calculating...';
    document.getElementById('trackDistance').textContent = 'Locating worker...';
    document.getElementById('trackPingTime').textContent = 'Connecting to GPS...';

    loadLeaflet(function() {
        initTrackingMap();
        setTimeout(function() {
            if (trackingMapInstance) trackingMapInstance.invalidateSize();
        }, 150);
        fetchTrackingData();
        if (trackingPollInterval) clearInterval(trackingPollInterval);
        trackingPollInterval = setInterval(fetchTrackingData, 5000);
    });
}

function closeLiveTrackingModal(e) {
    if (e && e.target && !e.target.closest) return;
    var modal = document.getElementById('liveTrackingModal');
    if (modal) modal.style.display = 'none';
    if (trackingPollInterval) {
        clearInterval(trackingPollInterval);
        trackingPollInterval = null;
    }
    trackingBookingId = null;
}

function initTrackingMap() {
    var mapEl = document.getElementById('liveTrackingMap');
    if (!mapEl) return;

    if (trackingMapInstance) {
        trackingMapInstance.remove();
        trackingMapInstance = null;
        trackingWorkerMarker = null;
        trackingDestMarker = null;
        trackingRouteLine = null;
    }

    trackingMapInstance = L.map('liveTrackingMap', { zoomControl: true });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(trackingMapInstance);
}

function fetchTrackingData() {
    if (!trackingBookingId) return;

    fetch('/client/bookings/' + trackingBookingId + '/track', {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            document.getElementById('trackDistance').textContent = data.message || 'Tracking unavailable';
            return;
        }

        // 1. Arrival detection
        if (data.arrived) {
            document.getElementById('trackArrivalNotice').style.display = 'flex';
            document.getElementById('trackStatusBar').style.display = 'none';
            document.getElementById('trackPingBadge').style.display = 'none';
            if (trackingPollInterval) {
                clearInterval(trackingPollInterval);
                trackingPollInterval = null;
            }
            return;
        }

        // 2. Info bar updates
        document.getElementById('trackWorkerName').textContent = data.worker_name;
        document.getElementById('trackServiceName').textContent = data.service;
        document.getElementById('trackEta').textContent = '~' + data.formatted_time;
        document.getElementById('trackDistance').textContent = data.formatted_dist + ' remaining';
        document.getElementById('trackDestAddress').innerHTML = '<i class="fa-solid fa-location-dot" style="color:#ef4444;margin-right:4px;"></i> ' + data.dest_address;

        var callBtn = document.getElementById('trackCallBtn');
        if (data.worker_phone) {
            callBtn.href = 'tel:' + data.worker_phone;
            callBtn.style.display = 'inline-flex';
        } else {
            callBtn.style.display = 'none';
        }

        var pingBadge = document.getElementById('trackPingTime');
        var roadBadgeText = data.is_snapped_road ? ' • Road-snapped route' : '';
        if (data.is_live && data.last_ping_seconds !== null) {
            pingBadge.textContent = 'Live GPS (updated ' + (data.last_ping_seconds < 5 ? 'just now' : data.last_ping_seconds + 's ago') + ')' + roadBadgeText;
        } else {
            pingBadge.textContent = 'Worker starting location (waiting for GPS ping...)' + roadBadgeText;
        }

        // 3. Map Marker & Polyline updates
        if (!trackingMapInstance) return;

        var workerPos = [data.worker_lat, data.worker_lng];
        var destPos = [data.dest_lat, data.dest_lng];

        var workerIcon = L.divIcon({
            className: 'live-worker-marker-wrap',
            html: '<div class="worker-radar-ring"></div><div class="live-worker-icon"><i class="fa-solid fa-motorcycle"></i></div>',
            iconSize: [44, 44],
            iconAnchor: [22, 22]
        });

        var destIcon = L.divIcon({
            className: 'live-dest-marker-wrap',
            html: '<div class="live-dest-icon"><i class="fa-solid fa-house"></i></div>',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        if (!trackingWorkerMarker) {
            trackingWorkerMarker = L.marker(workerPos, { icon: workerIcon, zIndexOffset: 1000 }).addTo(trackingMapInstance);
            trackingWorkerMarker.bindPopup('<strong>' + data.worker_name + '</strong><br>En route to your location');
        } else {
            trackingWorkerMarker.setLatLng(workerPos);
        }

        if (!trackingDestMarker) {
            trackingDestMarker = L.marker(destPos, { icon: destIcon }).addTo(trackingMapInstance);
            trackingDestMarker.bindPopup('<strong>Service Location</strong><br>' + data.dest_address);
        } else {
            trackingDestMarker.setLatLng(destPos);
        }

        var routePts = (Array.isArray(data.route_coordinates) && data.route_coordinates.length > 1)
            ? data.route_coordinates
            : [workerPos, destPos];

        if (!trackingRouteLine) {
            trackingRouteLine = L.polyline(routePts, {
                color: '#2563eb',
                weight: 5,
                opacity: 0.88,
                lineJoin: 'round',
                lineCap: 'round'
            }).addTo(trackingMapInstance);
        } else {
            trackingRouteLine.setLatLngs(routePts);
        }

        if (!hasFittedTrackingBounds) {
            var bounds = L.latLngBounds(routePts);
            trackingMapInstance.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
            hasFittedTrackingBounds = true;
        }
    })
    .catch(function(err) {
        console.error('Tracking fetch error:', err);
    });
}

window.recenterTrackingMap = function() {
    if (!trackingMapInstance) return;
    if (trackingRouteLine) {
        trackingMapInstance.fitBounds(trackingRouteLine.getBounds(), { padding: [50, 50], maxZoom: 16 });
    }
};

window.respondScopeAmendment = function(bookingId, action) {
    if (!confirm('Are you sure you want to ' + action + ' this scope revision?')) return;
    fetch('/client/bookings/' + bookingId + '/respond-scope-amendment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ action: action }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Action failed.', 'error');
        }
    })
    .catch(() => showToast('Something went wrong.', 'error'));
};

window.respondTeamSuggestion = function(bookingId, action) {
    if (!confirm('Are you sure you want to ' + action + ' this team proposal?')) return;
    fetch('/client/bookings/' + bookingId + '/respond-team-suggestion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ action: action }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Action failed.', 'error');
        }
    })
    .catch(() => showToast('Something went wrong.', 'error'));
};

window.addEventListener('beforeunload', function() {
    if (trackingPollInterval) clearInterval(trackingPollInterval);
});
</script>
@endpush
