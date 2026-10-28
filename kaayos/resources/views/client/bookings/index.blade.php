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
<div id="bookingModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="bookingModalTitle" style="display:none;" onclick="closeBookingModal(event)">
    <div class="modal-box modal-wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-header-left">
                <span class="modal-eyebrow">BOOKING OVERVIEW</span>
                <h3 id="bookingModalTitle">Booking Overview</h3>
                <div id="bookingModalMeta" class="booking-modal-meta"></div>
            </div>
            <div class="modal-header-right">
                <span id="bookingModalStatus" class="booking-modal-status"></span>
                <span id="bookingModalComplexity" class="booking-modal-complexity"></span>
            </div>
            <button type="button" class="modal-close" aria-label="Close booking overview" onclick="closeBookingModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="modal-content-layout">
                <div class="modal-content-sidebar" id="bookingModalSidebar">
                    <div class="booking-modal-timeline" id="bookingModalTimeline"></div>
                </div>
                <div class="modal-content-main">
                    <div class="booking-modal-details" id="bookingModalDetails"></div>
                </div>
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

{{-- Tip Modal (shown when completing a job) --}}
<div id="tipModal" class="modal-overlay" style="display:none;" onclick="closeTipModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3><i class="fa-solid fa-hand-holding-heart" style="color:#16a34a;"></i> Add a Tip</h3>
            <button type="button" class="modal-close" onclick="closeTipModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin:0 0 12px;color:var(--g6);font-size:.9rem;">
                Optional — <strong>100% of your tip</strong> goes to <span id="tipWorkerName">your worker</span>.
                The platform fee is never deducted from tips.
            </p>

            <div id="tipSummary" style="margin-bottom:14px;padding:10px 12px;background:var(--off,#F7F8FA);border:1px solid var(--g1,#E8ECF0);border-radius:8px;font-size:.875rem;"></div>

            <div class="tip-chips" id="tipChips">
                <button type="button" class="tip-chip" data-tip="0">No tip</button>
                <button type="button" class="tip-chip" data-tip="20">&#8369;20</button>
                <button type="button" class="tip-chip" data-tip="50">&#8369;50</button>
                <button type="button" class="tip-chip" data-tip="100">&#8369;100</button>
                <button type="button" class="tip-chip" data-tip="custom">Custom</button>
            </div>

            <div id="tipCustomWrap" style="display:none;margin-top:10px;">
                <label style="font-size:.85rem;font-weight:500;color:var(--g6);display:block;margin-bottom:4px;">Tip amount (&#8369;)</label>
                <input type="number" id="tipCustomInput" min="0" max="10000" step="1" placeholder="Enter amount"
                    style="width:100%;padding:9px 12px;border:1px solid var(--g2,#D5DBE2);border-radius:8px;font-size:.9rem;">
            </div>
            <div id="tipError" style="display:none;margin-top:8px;font-size:.8rem;color:#dc2626;"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeTipModal()">Cancel</button>
            <button type="button" class="btn btn-solid" id="tipConfirmBtn" onclick="submitTipFlow()">
                <i class="fa-regular fa-circle-check"></i> <span id="tipConfirmLabel">Confirm Completion</span>
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
.modal-wide { max-width: 680px !important; }

.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px 18px; font-weight: 600; font-size: 1.05rem;
    background: #fff;
    border-bottom: 1px solid #e8eef5;
}
.modal-header h3 { margin: 0; color: #172b4d; font-family: inherit; font-size: 1.4rem; letter-spacing: -.02em; }
.modal-header-left { display: flex; flex-direction: column; gap: 4px; }
.modal-header-right { display: flex; align-items: center; gap: 8px; margin-left: auto; margin-right: 14px; }
.modal-close {
    background: none; border: none; font-size: 1.5rem; cursor: pointer;
    color: var(--g4); line-height: 1;
}
.modal-close:hover { color: var(--g8); }

/* ── Modal content layout ── */
.modal-content-layout {
    display: block; margin: 0;
    width: 100%;
}
.modal-content-main { min-width: 0; }
.modal-content-sidebar { width: 100%; }
.booking-order-hero {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    margin-bottom: 14px;
    border: 1px solid #dbeafe;
    border-radius: 14px;
    background: linear-gradient(135deg, #eff6ff, #f8fbff);
}
.booking-service-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 12px;
    background: #dbeafe;
    color: #2563eb;
    font-size: 1.1rem;
}
.booking-order-hero h4 { margin: 0 0 3px; color: #172b4d; font-size: 1rem; }
.booking-order-hero p { margin: 0; color: #64748b; font-size: .78rem; }
.booking-hero-amount { margin-left: auto; color: #172b4d; font-size: 1.08rem; font-weight: 700; }
.booking-card-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin: 0 0 9px;
}
.booking-card-heading h4 {
    margin: 0;
    color: #475569;
    font-size: .7rem;
    letter-spacing: .1em;
    text-transform: uppercase;
}
.booking-info-card {
    padding: 14px;
    border: 1px solid #e5eaf0;
    border-radius: 13px;
    background: #fff;
    box-shadow: 0 2px 6px rgba(15, 23, 42, .03);
}
.booking-info-row {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 9px 0;
    border-bottom: 1px solid #f1f5f9;
}
.booking-info-row:first-child { padding-top: 0; }
.booking-info-row:last-child { padding-bottom: 0; border-bottom: 0; }
.booking-info-icon {
    width: 28px;
    color: #64748b;
    text-align: center;
    font-size: .9rem;
}
.booking-info-label { display: block; color: #94a3b8; font-size: .7rem; }
.booking-info-value { display: block; margin-top: 2px; color: #334155; font-size: .84rem; font-weight: 600; }
.booking-worker-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    margin-bottom: 14px;
    border: 1px solid #dbeafe;
    border-radius: 14px;
    background: #f8fbff;
}
.booking-worker-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    overflow: hidden;
    border-radius: 50%;
    background: #dbeafe;
    color: #0066ff;
    font-size: 1rem;
    font-weight: 800;
}
.booking-worker-avatar img { width: 100%; height: 100%; object-fit: cover; }
.booking-worker-copy { min-width: 0; }
.booking-worker-copy strong { display: block; color: #172b4d; font-size: .92rem; }
.booking-worker-copy span { display: block; margin-top: 2px; color: #64748b; font-size: .75rem; }
.booking-worker-actions { display: flex; gap: 7px; margin-left: auto; }
.booking-quick-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border: 0;
    border-radius: 50%;
    background: #e0edff;
    color: #0066ff;
    text-decoration: none;
    cursor: pointer;
    transition: transform .15s ease, background .15s ease;
}
.booking-quick-action:hover { background: #cfe2ff; }
.booking-quick-action:active,
.modal-footer .btn:active { transform: scale(.95); }
.booking-location-card {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px 14px;
    margin-top: 12px;
    border-radius: 12px;
    background: #f8fafc;
}
.booking-location-card i { margin-top: 2px; color: #2563eb; }
.booking-location-card strong { display: block; color: #334155; font-size: .82rem; }
.booking-location-card span { display: block; margin-top: 2px; color: #64748b; font-size: .78rem; }
.booking-payment-card {
    padding: 14px;
    margin-top: 12px;
    border: 1px solid #e5eaf0;
    border-radius: 13px;
    background: #fff;
}
.booking-payment-row { display: flex; justify-content: space-between; gap: 10px; color: #64748b; font-size: .8rem; }
.booking-payment-row + .booking-payment-row { margin-top: 8px; }
.booking-payment-row.total {
    padding-top: 10px;
    margin-top: 10px;
    border-top: 1px solid #e5eaf0;
    color: #172b4d;
    font-size: .95rem;
    font-weight: 700;
}

/* ── Modal footer ── */
.modal-footer {
    display: flex; align-items: center; gap: 10px; justify-content: space-between;
    flex-wrap: wrap;
    padding: 12px 20px 20px;
    border-top: 1px solid var(--g1);
}
.modal-footer-left { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.modal-footer-right { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.modal-footer-center { display: flex; justify-content: center; width: 100%; }

/* ── Status timeline ── */
.booking-modal-timeline {
    padding: 14px 12px 12px;
    margin: 0 0 16px;
    border: 1px solid #dbeafe;
    border-radius: 14px;
    background: #f8fbff;
    box-shadow: inset 0 1px 0 #fff;
    overflow-x: auto;
}
.booking-modal-timeline::before {
    content: 'ORDER STATUS';
    display: block;
    margin-bottom: 12px;
    color: var(--g5);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .1em;
}
.timeline {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0;
    min-width: 520px;
    padding: 2px 6px 4px;
}
.timeline-item {
    position: relative;
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    min-width: 0;
    padding: 0 4px;
}
.timeline-dot-col {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 20px;
}
.timeline-dot {
    position: relative;
    z-index: 1;
    width: 19px; height: 19px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .55rem;
    flex-shrink: 0;
    line-height: 1;
}
.timeline-dot.past { background: #0066ff; color: #fff; }
.timeline-dot.current { background: #0066ff; color: #fff; box-shadow: 0 0 0 4px #bfdbfe; animation: statusPulse 1.8s ease-out infinite; }
.timeline-dot.future { background: #fff; border: 2px solid #cbd5e1; color: #94a3b8; }
.timeline-line {
    position: absolute;
    top: 9px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #dbeafe;
}
.timeline-item.past .timeline-line { background: #0066ff;
}
.timeline-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    width: 100%;
    gap: 3px;
    padding: 0;
}
.timeline-status {
    font-size: .7rem;
    font-weight: 500;
    color: var(--g7);
    text-align: center;
    white-space: normal;
    line-height: 1.25;
}
.timeline-item.past .timeline-status { color: #0066ff; }
.timeline-item.current .timeline-status { color: #0066ff; font-weight: 700; }
.timeline-item.future .timeline-status { color: var(--g4); }
.timeline-time {
    display: block;
    margin: 0;
    color: #94a3b8;
    font-size: .68rem;
    line-height: 1.2;
    white-space: nowrap;
}
.timeline-item.current .timeline-time,
.timeline-item.past .timeline-time { color: #64748b; }
@keyframes statusPulse {
    0%, 100% { box-shadow: 0 0 0 3px #bfdbfe; }
    50% { box-shadow: 0 0 0 7px rgba(191, 219, 254, .25); }
}
.booking-modal-status {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 100px;
    font-family: inherit;
    font-size: .75rem;
    font-weight: 600;
}
.booking-modal-status.status-active {
    background: var(--b0);
    color: var(--b7);
}
.booking-modal-status.status-pending {
    background: #fef3d0;
    color: #a07b10;
}
.booking-modal-status.status-cancelled {
    background: #fde0de;
    color: #a32d2d;
}
.booking-modal-status.status-done {
    background: #d6f5e8;
    color: #1a6852;
    animation: badgePulse 1.8s ease-out 1;
}
@keyframes badgePulse {
    0% { box-shadow: 0 0 0 0 rgba(0, 102, 255, .35); }
    100% { box-shadow: 0 0 0 9px rgba(0, 102, 255, 0); }
}

/* ── Modals ── */
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,.45); z-index: 1000;
    display: flex; align-items: center; justify-content: center;
}
.modal-box {
    background: #fff; border-radius: 14px; width: 90%; max-width: 520px;
    box-shadow: 0 20px 60px rgba(0,0,0,.2); animation: modalIn .2s ease;
    overflow: hidden;
    font-family: inherit;
}
#bookingModal.modal-overlay { align-items: flex-end; }
#bookingModal .modal-box {
    border-radius: 24px;
    width: calc(100% - 32px);
    max-width: 640px !important;
    max-height: calc(100vh - 60px);
    margin-bottom: 10px;
    animation: modalIn .35s ease-out;
    display: flex;
    flex-direction: column;
}
#bookingModal .modal-box::before {
    content: '';
    display: block;
    width: 38px;
    height: 4px;
    margin: 8px auto 0;
    border-radius: 99px;
    background: #cbd5e1;
}
@keyframes modalIn {
    from { opacity: 0; transform: translateY(100%); }
    to   { opacity: 1; transform: translateY(0); }
}
#bookingModal .modal-header {
    padding: 12px 16px 0;
}
#bookingModal .modal-body {
    min-height: 0;
    max-height: none;
    padding: 12px 16px;
    overflow-y: auto;
}
#bookingModal .modal-footer {
    flex-shrink: 0;
    padding: 10px 16px 14px;
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
.modal-body { padding: 16px 20px; max-height: 76vh; overflow-y: auto; }
.modal-footer {
    display: flex; align-items: center; gap: 10px; justify-content: space-between;
    flex-wrap: wrap;
    padding: 12px 20px 20px;
    border-top: 1px solid var(--g1);
}
.modal-footer-left,
.modal-footer-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.modal-footer .btn-solid { order: 0; }
.modal-footer .btn-primary-action {
    min-height: 40px;
    padding-inline: 16px;
    background: #0066ff;
    border-color: #0066ff;
    box-shadow: 0 3px 8px rgba(0,102,255,.2);
}
.modal-footer .btn-report {
    padding-inline: 2px;
    border-color: transparent !important;
    background: transparent;
    color: #b91c1c !important;
}
.modal-footer .btn-outline { min-height: 38px; }

/* ── Tip chips ── */
.tip-chips {
    display: flex; flex-wrap: wrap; gap: 8px;
}
.tip-chip {
    padding: 9px 16px;
    border: 1px solid var(--g2, #D5DBE2);
    background: #fff;
    border-radius: 999px;
    font-size: .875rem;
    font-weight: 600;
    color: var(--g7, #3D4A56);
    cursor: pointer;
    transition: all .15s;
}
.tip-chip:hover {
    border-color: var(--b4, #378ADD);
    color: var(--b6, #1A6FC4);
}
.tip-chip.active {
    background: var(--b6, #1A6FC4);
    border-color: var(--b6, #1A6FC4);
    color: #fff;
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
    .modal-content-layout { flex-direction: column; }
    .modal-content-sidebar { width: 100%; order: 0; margin-top: 0; }
    #bookingModal .modal-wide { width: calc(100% - 20px) !important; }
    .modal-header { padding: 16px; align-items: flex-start; }
    .modal-header-left { min-width: 0; }
    .modal-header-right { margin: 0 8px 0 auto; }
    .modal-header h3 { font-size: 1.15rem; }
    .modal-body { padding: 14px 16px; }
    .booking-order-hero { padding: 12px; }
    .booking-hero-amount { font-size: .95rem; }
    .booking-modal-timeline { padding: 14px; }
    .modal-footer { padding: 12px 16px 16px; }
    .modal-footer-left, .modal-footer-right { flex: 1 1 100%; justify-content: center; }
    .modal-footer-center { width: 100%; justify-content: center; }
    .booking-card { padding: 10px 14px; gap: 10px; }
    .schedule-date-box { width: 40px; height: 40px; }
    .schedule-day { font-size: .9rem; }
    .schedule-month { font-size: .55rem; }
    .booking-card-time { margin-left: 0; width: 100%; }
    .modal-content-main { padding-right: 0; }
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

// ── Booking Detail Modal Helper Functions ──
function primaryBtn(label, className, onclick, icon = '') {
    return '<button type="button" class="btn ' + className + '" ' + (onclick ? 'onclick="' + onclick + '"' : '') + '>' + (icon ? '<i class="fa-' + icon + '"></i> ' : '') + label + '</button>';
}

function secondaryBtn(label, className, onclick, icon = '') {
    return '<button type="button" class="btn ' + className + '" ' + (onclick ? 'onclick="' + onclick + '"' : '') + '>' + (icon ? '<i class="fa-' + icon + '"></i> ' : '') + label + '</button>';
}

function linkBtn(label, href, className, icon = '') {
    return '<a href="' + href + '" class="btn ' + (className || '') + '"' + (icon ? '<i class="fa-' + icon + '"></i> ' : '') + label + '</a>';
}

// ── Booking Detail Modal ──
function openBookingModal(index) {
    var b = bookings[index];
    if (!b) return;

    var status = b.raw_status || 'new';
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

    var statusClassMap = {
        'new': 'status-pending',
        'accepted': 'status-active',
        'en_route': 'status-active',
        'in_progress': 'status-active',
        'completed': 'status-done',
        'cancelled': 'status-cancelled'
    };
    var statusClass = statusClassMap[b.raw_status] || 'status-cancelled';
    var displayService = b.service ? b.service.charAt(0).toUpperCase() + b.service.slice(1) : 'Booking';
    var scheduleParts = String(b.date || '').split(' · ');
    var scheduleHtml = '<span>' + scheduleParts[0] + '</span>' +
        (scheduleParts[1] ? '<span class="booking-schedule-time">' + scheduleParts[1] + '</span>' : '');
    var workerName = b.worker || 'KaAyos worker';
    var workerInitials = workerName.split(/\s+/).filter(Boolean).slice(0, 2).map(function (part) {
        return part.charAt(0).toUpperCase();
    }).join('');
    var workerAvatar = b.worker_avatar
        ? '<img src="' + b.worker_avatar + '" alt="' + workerName + ' profile picture">'
        : workerInitials;
    var workerPhone = b.worker_phone || b.phone || '';
    var workerActions = '<a class="booking-quick-action" href="tel:' + workerPhone + '" aria-label="Call ' + workerName + '" title="Call worker"><i class="fa-solid fa-phone" aria-hidden="true"></i></a>' +
        '<a class="booking-quick-action" href="{{ route('client.messages.start') }}?worker_id=' + b.worker_id + '" aria-label="Message ' + workerName + '" title="Message worker"><i class="fa-regular fa-comment" aria-hidden="true"></i></a>';
    document.getElementById('bookingModalTitle').textContent = displayService;
    document.getElementById('bookingModalStatus').textContent = statusLabelMap[b.raw_status] || b.raw_status;
    document.getElementById('bookingModalStatus').className = 'booking-modal-status status-badge ' + statusClass;
    // Set complexity tag in header right
    var complexityLevel = b.complexity_level || 'standard';
    var complexityLabels = {
        'standard': '<span class="badge" style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:99px;font-size:.72rem;">Standard (1.0x)</span>',
        'complex': '<span class="badge" style="background:#fef3c7;color:#b45309;padding:2px 8px;border-radius:99px;font-size:.72rem;">Complex (1.2x)</span>',
        'hazardous': '<span class="badge" style="background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:99px;font-size:.72rem;">Hazardous (1.5x)</span>'
    };
    document.getElementById('bookingModalComplexity').innerHTML = complexityLabels[complexityLevel] || complexityLabels.standard;
    document.getElementById('bookingModalMeta').innerHTML =
        '<span><strong>Reference</strong> ' + (b.booking_ref || 'BK-' + String(b.id).padStart(5,'0')) + '</span>';
    document.getElementById('bookingModalDetails').innerHTML =
        enRouteBanner +
        '<div class="booking-worker-card">' +
            '<div class="booking-worker-avatar">' + workerAvatar + '</div>' +
            '<div class="booking-worker-copy"><strong>' + workerName + '</strong><span>Your worker</span></div>' +
            '<div class="booking-worker-actions">' + workerActions + '</div>' +
        '</div>' +
        '<div class="booking-order-hero">' +
            '<span class="booking-service-icon"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span>' +
            '<div><h4>' + displayService + '</h4><p>KaAyos service booking</p></div>' +
            '<strong class="booking-hero-amount">₱' + Number(b.price).toLocaleString() + '</strong>' +
        '</div>' +
        '<div class="booking-card-heading"><h4>Booking details</h4></div>' +
        '<div class="booking-info-card">' +
            '<div class="booking-info-row"><span class="booking-info-icon"><i class="fa-regular fa-calendar" aria-hidden="true"></i></span><div><span class="booking-info-label">Scheduled for</span><span class="booking-info-value">' + scheduleHtml + '</span></div></div>' +
            '<div class="booking-info-row"><span class="booking-info-icon"><i class="fa-solid fa-house" aria-hidden="true"></i></span><div><span class="booking-info-label">Property type</span><span class="booking-info-value">' + propType + '</span></div></div>' +
            '<div class="booking-info-row"><span class="booking-info-icon"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span><div><span class="booking-info-label">Payment</span><span class="booking-info-value">' + pricingType + '</span></div></div>' +
            '<div class="booking-info-row"><span class="booking-info-icon"><i class="fa-regular fa-note-sticky" aria-hidden="true"></i></span><div><span class="booking-info-label">Job notes</span><span class="booking-info-value">' + notes + '</span></div></div>' +
        '</div>' +
        '<div class="booking-location-card"><i class="fa-solid fa-location-dot" aria-hidden="true"></i><div><strong>Service location</strong><span>' + b.location + '</span></div></div>' +
        '<div class="booking-payment-card"><div class="booking-card-heading"><h4>Payment summary</h4></div>' +
            '<div class="booking-payment-row"><span>Service fee</span><strong>₱' + Number(b.price).toLocaleString() + '</strong></div>' +
            (Number(b.tip_amount) > 0 ? '<div class="booking-payment-row"><span>Tip</span><strong style="color:#16a34a;">₱' + Number(b.tip_amount).toLocaleString() + '</strong></div>' : '') +
            '<div class="booking-payment-row total"><span>Total</span><strong>₱' + (Number(b.price) + Number(b.tip_amount || 0)).toLocaleString() + '</strong></div>' +
        '</div>' +
        (cancelledAt ? '<div class="booking-location-card"><i class="fa-solid fa-circle-xmark" style="color:#dc2626;" aria-hidden="true"></i><div><strong>Cancelled</strong><span>' + cancelledAt + (cancelReason ? ' · ' + cancelReason : '') + '</span></div></div>' : '') +
        timerSection +
        scopeCard +
        teamCard;

    // Timeline
    var timelineHtml = '<div class="timeline">';
    for (var si = 0; si < statusFlow.length; si++) {
        var timelineStatus = statusFlow[si];
        var ts = b.status_history ? b.status_history[timelineStatus] : null;
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
        timelineHtml +=     '<span class="timeline-status">' + statusLabelMap[timelineStatus] + '</span>';
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
    if (status === 'cancelled') {
        footer.innerHTML = '<div class="modal-footer-center">' +
            '<button type="button" class="btn btn-outline" onclick="closeBookingModal()">Close</button>' +
        '</div>';
        document.getElementById('bookingModal').style.display = 'flex';
        return;
    }

    if (status === 'completed') {
        footer.innerHTML = '<div class="modal-footer-left">' +
            secondaryBtn('<i class="fa-solid fa-flag"></i> Report Worker', 'btn-outline', 'showReportModal(' + index + ')') +
        '</div>' +
        '<div class="modal-footer-right">' +
            linkBtn('<i class="fa-regular fa-star" aria-hidden="true"></i> Leave Review', '{{ route('client.reviews') }}', 'btn-solid btn-primary-action') +
            linkBtn('<i class="fa-solid fa-quote-left"></i> Share a Testimonial', '{{ route('client.testimonials.create') }}', 'btn-outline', 'quote-left') +
        '</div>';
        document.getElementById('bookingModal').style.display = 'flex';
        return;
    }

    if (status === 'in_progress') {
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

        footer.innerHTML = '<div class="modal-footer-left"></div>' +
            '<div class="modal-footer-right">' + completionHtml + '</div>';
        document.getElementById('bookingModal').style.display = 'flex';
        return;
    }

    // New, Accepted, En Route statuses
    var footerHtml = '<div class="modal-footer-left">';
    // Primary action depends on status
    if (status === 'en_route') {
        footerHtml += primaryBtn('<i class="fa-solid fa-location-crosshairs"></i> Track Live', 'btn-solid', 'closeBookingModal(); openLiveTrackingModal(' + b.id + ')');
    } else if (status === 'new' || status === 'accepted') {
        footerHtml += primaryBtn('<i class="fa-regular fa-circle-check"></i> Mark Complete', 'btn-solid', 'markJobComplete(' + index + ')');
    }
    footerHtml += '</div>';

    footerHtml += '<div class="modal-footer-right">';
    // Secondary actions - always show Message and Cancel
    footerHtml += '<a href="{{ route('client.messages.start') }}?worker_id=' + b.worker_id + '" class="btn btn-outline"><i class="fa-regular fa-comment"></i> Message</a>';
    footerHtml += '<button type="button" class="btn btn-outline" onclick="closeBookingModal()">Close</button>';

    // Status-specific secondary actions
    if (status === 'en_route') {
        // Track Live button already in primary, add nothing extra
    } else if (status === 'new' || status === 'accepted') {
        // Already have Cancel and Message
    }

    footerHtml += '</div>';

    document.getElementById('bookingModalFooter').innerHTML = footerHtml;
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

// ── Tip + Job Completion Confirmation ──
var tipFlow = null;        // { index, mode: 'mark' | 'confirm' }
var selectedTip = 0;

function initTipModal() {
    document.querySelectorAll('#tipChips .tip-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            document.querySelectorAll('#tipChips .tip-chip').forEach(function (c) { c.classList.remove('active'); });
            chip.classList.add('active');
            var value = chip.dataset.tip;
            var wrap = document.getElementById('tipCustomWrap');
            if (value === 'custom') {
                wrap.style.display = 'block';
                var input = document.getElementById('tipCustomInput');
                var parsed = parseFloat(input.value);
                selectedTip = isNaN(parsed) || parsed < 0 ? 0 : parsed;
                input.focus();
            } else {
                wrap.style.display = 'none';
                selectedTip = parseFloat(value) || 0;
            }
            updateTipSummary();
        });
    });

    var customInput = document.getElementById('tipCustomInput');
    if (customInput) {
        customInput.addEventListener('input', function () {
            var parsed = parseFloat(this.value);
            selectedTip = isNaN(parsed) || parsed < 0 ? 0 : parsed;
            updateTipSummary();
        });
    }
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTipModal);
} else {
    initTipModal();
}

function updateTipSummary() {
    if (!tipFlow) return;
    var b = bookings[tipFlow.index];
    if (!b) return;
    var price = Number(b.price) || 0;
    var tip = Number(selectedTip) || 0;
    document.getElementById('tipSummary').innerHTML =
        '<div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span>Service amount</span><span>\u20B1' + price.toLocaleString() + '</span></div>' +
        '<div style="display:flex;justify-content:space-between;margin-bottom:4px;color:#16a34a;"><span>Tip</span><span>\u20B1' + tip.toLocaleString() + '</span></div>' +
        '<div style="display:flex;justify-content:space-between;font-weight:700;border-top:1px solid var(--g1,#E8ECF0);padding-top:6px;"><span>Total</span><span>\u20B1' + (price + tip).toLocaleString() + '</span></div>';
}

function openTipModal(index, mode) {
    var b = bookings[index];
    if (!b) return;

    tipFlow = { index: index, mode: mode };
    selectedTip = 0;

    document.getElementById('tipWorkerName').textContent = b.worker || 'your worker';
    document.getElementById('tipConfirmLabel').textContent = mode === 'mark' ? 'Mark Complete' : 'Confirm Complete';
    document.getElementById('tipCustomWrap').style.display = 'none';
    document.getElementById('tipCustomInput').value = '';
    document.getElementById('tipError').style.display = 'none';
    document.querySelectorAll('#tipChips .tip-chip').forEach(function (c) {
        c.classList.toggle('active', c.dataset.tip === '0');
    });

    updateTipSummary();
    document.getElementById('tipModal').style.display = 'flex';
}

function closeTipModal(e) {
    if (e && e.target !== e.currentTarget) return;
    var modal = document.getElementById('tipModal');
    if (modal) modal.style.display = 'none';
    tipFlow = null;
}

function submitTipFlow() {
    var err = document.getElementById('tipError');
    err.style.display = 'none';

    if (selectedTip < 0 || selectedTip > 10000) {
        err.textContent = 'Tip must be between \u20B10 and \u20B110,000.';
        err.style.display = 'block';
        return;
    }

    var flow = tipFlow;
    var tip = Number(selectedTip) || 0;
    closeTipModal();
    if (flow) runCompletionRequest(flow.index, flow.mode, tip);
}

function runCompletionRequest(index, mode, tipAmount) {
    var b = bookings[index];
    if (!b) return;

    var pathTemplate = mode === 'mark'
        ? '{{ route("client.bookings.mark-complete", "__ID__", false) }}'
        : '{{ route("client.bookings.confirm-complete", "__ID__", false) }}';
    var url = window.location.origin + pathTemplate.replace('__ID__', b.id);
    var fallbackMessage = mode === 'mark'
        ? 'Marked as complete. Waiting for worker to confirm.'
        : 'Job completion confirmed!';

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ tip_amount: tipAmount }),
    })
    .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
    .then(function (res) {
        if (res.ok && res.data.success) {
            if (window.showToast) window.showToast(res.data.message || fallbackMessage, 'success');
            setTimeout(function () { location.reload(); }, 600);
        } else {
            var msg = (res.data && res.data.message) ? res.data.message : fallbackMessage;
            if (window.showToast) window.showToast(msg, 'error');
            else alert(msg);
        }
    })
    .catch(function () {
        if (window.showToast) window.showToast('Network error. Please try again.', 'error');
        else alert('Something went wrong.');
    });
}

function markJobComplete(index) {
    var b = bookings[index];
    if (!b) return;
    openTipModal(index, 'mark');
}

function confirmJobComplete(index) {
    var b = bookings[index];
    if (!b) return;
    openTipModal(index, 'confirm');
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
