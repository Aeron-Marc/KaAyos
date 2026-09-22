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

{{-- Live Tracking Active Banner for En Route Jobs --}}
<div id="workerLiveTrackingBanner" class="worker-live-banner" style="display:none;align-items:center;justify-content:space-between;gap:12px;background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);border:1px solid #93c5fd;border-radius:12px;padding:12px 18px;margin-bottom:16px;box-shadow:0 2px 8px rgba(37,99,235,0.08);">
    <div style="display:flex;align-items:center;gap:12px;">
        <span class="live-pulse-dot"></span>
        <div>
            <div style="font-weight:700;color:#1e40af;font-size:.9rem;display:flex;align-items:center;gap:6px;">
                <span>Live Location Sharing Active</span>
                <span class="badge" style="background:#2563eb;color:#fff;font-size:.7rem;padding:2px 7px;border-radius:99px;">EN ROUTE</span>
            </div>
            <div style="font-size:.8rem;color:#3b82f6;margin-top:2px;" id="workerLiveBannerSubtext">
                Transmitting GPS coordinates to client every 10 seconds.
            </div>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
        <span id="workerLiveDistBadge" style="font-size:.8rem;font-weight:600;color:#1e3a8a;background:rgba(255,255,255,0.7);padding:4px 10px;border-radius:8px;border:1px solid #bfdbfe;">
            Locating...
        </span>
        <a id="workerLiveNavBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-solid" style="padding:6px 12px;font-size:.8rem;gap:6px;">
            <i class="fa-solid fa-diamond-turn-right"></i> Navigate
        </a>
    </div>
</div>

@if(isset($todayUtilization))
<div class="schedule-maximization-card" style="background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);border:1px solid #cbd5e1;border-radius:12px;padding:14px 18px;margin-bottom:16px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:48px;height:48px;border-radius:10px;background:#2563eb;color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;box-shadow:0 2px 8px rgba(37,99,235,0.25);">
                {{ $todayUtilization['utilization_percent'] }}%
            </div>
            <div>
                <div style="font-weight:700;color:#1e293b;font-size:.95rem;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span>Daily Schedule Optimization</span>
                    <span class="badge" style="background:#0284c7;color:#fff;font-size:.7rem;padding:2px 8px;border-radius:99px;">{{ $todayUtilization['status_label'] }}</span>
                    @if(!empty($todayUtilization['availability_window'] ?? $todayUtilization['shift_window']))
                        <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.7rem;padding:2px 8px;border-radius:99px;border:1px solid #cbd5e1;"><i class="fa-regular fa-clock" style="margin-right:4px;"></i>{{ $todayUtilization['availability_window'] ?? $todayUtilization['shift_window'] }}</span>
                    @endif
                </div>
                <div style="font-size:.8rem;color:#64748b;margin-top:2px;">
                    {{ $todayUtilization['booked_hours'] ?? 0 }}h booked of {{ $todayUtilization['total_capacity_hours'] ?? 0 }}h capacity ({{ $todayUtilization['total_jobs'] ?? 0 }} active stops)
                </div>
            </div>
        </div>
        @if(!empty($todayUtilization['gaps']))
            <div style="display:flex;align-items:center;gap:8px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:6px 12px;font-size:.78rem;color:#92400e;">
                <i class="fa-solid fa-bolt" style="color:#d97706;"></i>
                <span><strong>{{ count($todayUtilization['gaps']) }} Express Gap Slot:</strong> {{ $todayUtilization['gaps'][0]['start'] }} – {{ $todayUtilization['gaps'][0]['end'] }} ({{ $todayUtilization['gaps'][0]['duration_hours'] }}h near {{ $todayUtilization['gaps'][0]['near_barangay'] ?? 'Tuy' }})</span>
            </div>
        @endif
    </div>
</div>
@endif

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

{{-- View Toggle --}}
<div class="view-toggle-wrap" style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:16px;">
    <div class="calendar-view-toggle">
        <button type="button" id="listViewBtn" class="active" onclick="switchView('list')">
            <i class="fa-solid fa-list"></i> List
        </button>
        <button type="button" id="calendarViewBtn" onclick="switchView('calendar')">
            <i class="fa-regular fa-calendar"></i> Calendar
        </button>
    </div>
</div>

{{-- Calendar Section --}}
<div id="calendarSection" class="calendar-wrap" style="display:none;margin-bottom:20px;">
    <div class="calendar-header">
        <div class="calendar-nav">
            <button type="button" class="calendar-nav-btn" id="calPrevBtn" onclick="calNav(-1)">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <span class="calendar-month-year" id="calMonthYear"></span>
            <button type="button" class="calendar-nav-btn" id="calNextBtn" onclick="calNav(1)">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <button type="button" class="calendar-today-btn" id="calTodayBtn" onclick="calGoToday()">Today</button>
        </div>
    </div>
    <div class="calendar-grid" id="calendarGrid">
        <div class="calendar-weekday">Sun</div>
        <div class="calendar-weekday">Mon</div>
        <div class="calendar-weekday">Tue</div>
        <div class="calendar-weekday">Wed</div>
        <div class="calendar-weekday">Thu</div>
        <div class="calendar-weekday">Fri</div>
        <div class="calendar-weekday">Sat</div>
    </div>
</div>

{{-- Calendar Day Detail Modal --}}
<div id="calDayModal" class="calendar-modal-overlay" onclick="closeCalModal(event)">
    <div class="calendar-modal" onclick="event.stopPropagation()" style="max-width:600px;">
        <div class="cal-modal-header">
            <div>
                <h3 id="calModalTitle">Schedule</h3>
                <div class="cal-modal-date" id="calModalDate"></div>
            </div>
            <button type="button" class="cal-modal-close" onclick="closeCalModal()">&times;</button>
        </div>

        {{-- Route Summary & Map Toolbar --}}
        <div id="calRouteBar" class="cal-route-bar" style="display:none;">
            <div class="cal-route-stats">
                <div class="cal-stat-item"><strong id="routeTotalJobs">0</strong> <span class="cal-stat-lbl">stops</span></div>
                <div class="cal-stat-sep">•</div>
                <div class="cal-stat-item"><strong id="routeTotalDist">0 km</strong> <span class="cal-stat-lbl">travel</span></div>
                <div class="cal-stat-sep">•</div>
                <div class="cal-stat-item"><strong id="routeTotalTime">0m</strong> <span class="cal-stat-lbl">est. transit</span></div>
            </div>
            <div style="display:flex;align-items:center;gap:6px;">
                <button type="button" class="btn btn-sm btn-outline" id="calOptimizeBtn" style="display:none;" onclick="toggleRouteOptimization()">
                    <i class="fa-solid fa-wand-magic-sparkles" style="color:#d97706;"></i> Optimize
                </button>
                <button type="button" class="btn btn-sm btn-outline" id="calMapToggleBtn" onclick="toggleCalMap()">
                    <i class="fa-solid fa-map-location-dot"></i> <span id="calMapToggleText">Show Map</span>
                </button>
            </div>
        </div>

        <div id="calOptimizationBox" style="display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:14px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;flex-wrap:wrap;gap:6px;">
                <div style="font-weight:700;color:#92400e;font-size:.85rem;display:flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-route"></i> Optimal Route Sequence
                </div>
                <span id="calOptSavingsBadge" style="background:#fef3c7;color:#b45309;border:1px solid #fcd34d;padding:2px 8px;border-radius:99px;font-size:.75rem;font-weight:700;"></span>
            </div>
            <div id="calOptSteps" style="font-size:.8rem;color:#78350f;"></div>
        </div>

        <div id="calMapContainer" style="display:none;margin-bottom:14px;">
            <div id="calRouteMap" style="height:230px;border-radius:10px;border:1px solid var(--g2,#e2e8f0);overflow:hidden;z-index:1;"></div>
        </div>

        <div class="cal-modal-jobs" id="calModalJobs"></div>
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
                $isCompPending = !empty($job['is_completion_pending']);
                $statusBadgeText = $job['status'];
                $statusClass = match($job['raw_status']) {
                    'new'        => 'status-pending',
                    'accepted'   => 'status-active',
                    'en_route'   => 'status-active',
                    'in_progress'=> 'status-active',
                    'completed'  => 'status-done',
                    default      => 'status-cancelled',
                };
                if ($isCompPending) {
                    $statusClass = 'status-pending';
                    $statusBadgeText = !empty($job['confirmed_by_worker_at']) ? 'Awaiting Client' : 'Confirm Complete';
                }
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
                        <span class="status-badge {{ $statusClass }}">{{ $statusBadgeText }}</span>
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

{{-- Scope Amendment Modal --}}
<div id="scopeAmendmentModal" class="modal-overlay" style="display:none;" onclick="closeModals(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3>Request Scope Amendment</h3>
            <button type="button" class="modal-close" onclick="closeModals()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin:0 0 12px;color:var(--g6);font-size:.85rem;">
                Adjust pricing for on-site unforeseen conditions, extra parts, or expanded labor. Client must approve before taking effect.
            </p>
            <div style="margin-bottom:12px;">
                <label style="font-size:.82rem;font-weight:600;color:var(--g7);display:block;margin-bottom:4px;">Revised Total Price (₱) *</label>
                <input type="number" id="amendmentPriceInput" class="form-input" placeholder="e.g. 1500" min="0" step="50" style="width:100%;padding:8px 12px;border:1px solid var(--g3);border-radius:8px;">
            </div>
            <div>
                <label style="font-size:.82rem;font-weight:600;color:var(--g7);display:block;margin-bottom:4px;">Justification / Scope Breakdown *</label>
                <textarea id="amendmentNotesInput" class="review-textarea" placeholder="Explain the extra work required, materials, or complications encountered…" style="min-height:80px;width:100%;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModals()">Cancel</button>
            <button type="button" class="btn btn-solid" id="submitAmendmentBtn" onclick="submitScopeAmendment()">Submit Proposal</button>
        </div>
    </div>
</div>

{{-- Suggest Team / Peer Work Modal --}}
<div id="suggestTeamModal" class="modal-overlay" style="display:none;" onclick="closeModals(event)">
    <div class="modal-box modal-wide" onclick="event.stopPropagation()" style="max-width:680px;">
        <div class="modal-header">
            <div>
                <h3 style="margin:0;"><i class="fa-solid fa-users" style="color:#2563eb;margin-right:6px;"></i> Suggest Team Workers for this Job</h3>
                <div style="font-size:.78rem;color:var(--g5);margin-top:2px;">Directly suggest other skilled workers (Person B, Person C...) to collaborate with you on this job</div>
            </div>
            <button type="button" class="modal-close" onclick="closeModals()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="margin:0 0 14px;color:var(--g6);font-size:.84rem;">
                Recommend one or more skilled workers (Person B, Person C...) to collaborate on this job. The client will review your recommendation, and each accepted worker will be invited to your crew.
            </p>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <label style="font-size:.82rem;font-weight:700;color:var(--g7);margin:0;">Workers to Recommend (Person B, Person C...) *</label>
                <button type="button" class="btn btn-sm btn-outline" onclick="addPeerSuggestionRow()" style="font-size:.75rem;padding:3px 10px;gap:4px;">
                    <i class="fa-solid fa-user-plus"></i> Add Another Worker
                </button>
            </div>

            <div id="peerRowsContainer" style="display:flex;flex-direction:column;gap:10px;margin-bottom:14px;max-height:280px;overflow-y:auto;padding-right:2px;">
                <!-- Dynamically populated via JS -->
            </div>

            <div>
                <label style="font-size:.82rem;font-weight:600;color:var(--g7);display:block;margin-bottom:4px;">Recommendation Justification for Client *</label>
                <textarea id="teamJustificationInput" class="review-textarea" placeholder="e.g. Heavy lifting and dual safety tethering required. Bringing an experienced electrician and safety spotter ensures quick, safe completion…" style="min-height:80px;width:100%;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModals()">Cancel</button>
            <button type="button" class="btn btn-solid" id="submitTeamBtn" onclick="submitTeamSuggestion()">Send Recommendation</button>
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
let currentActionJobId = null;

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
                })
                .listen('BookingStatusUpdated', function (e) {
                    if (window.showToast) {
                        window.showToast('Job status updated: ' + (e.new_status || 'updated'), 'info');
                    }
                    setTimeout(function () { location.reload(); }, 1200);
                })
                .listen('JobCompletionStatusUpdated', function (e) {
                    if (window.showToast) {
                        window.showToast(e.fully_completed ? 'Job #' + e.booking_id + ' is fully completed!' : 'Job #' + e.booking_id + ' completion status updated.', 'info');
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
    var navUrl = job.gmaps_nav_url || ('https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent((job.barangay ? job.barangay + ', ' : '') + job.location + ', Tuy, Batangas'));
    var enRouteBanner = (job.raw_status === 'en_route')
        ? '<div class="en-route-banner" style="display:flex;align-items:center;justify-content:space-between;gap:10px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-bottom:14px;color:#1e40af;font-size:.85rem;">' +
            '<div style="display:flex;align-items:center;gap:8px;">' +
              '<span class="live-pulse-dot"></span>' +
              '<div><strong>You are en route!</strong><div style="font-size:.75rem;color:#3b82f6;">Client is actively tracking your location.</div></div>' +
            '</div>' +
            '<a href="' + navUrl + '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-solid" style="padding:4px 10px;font-size:.78rem;gap:4px;flex-shrink:0;"><i class="fa-solid fa-diamond-turn-right"></i> Navigation</a>' +
          '</div>'
        : '';

    var propTypeLabels = {
        'residential': 'Residential',
        'commercial': 'Commercial / Business',
        'property_manager': 'Property Management',
        'tenant': 'Tenant / Renter'
    };
    var propType = propTypeLabels[job.property_type] || (job.property_type ? job.property_type.replace('_',' ') : 'Residential');
    var pricingType = (job.pricing_type === 'hourly') ? 'Hourly (est. ' + (job.estimated_duration_hours || 2) + 'h)' : 'Fixed Price';
    var complexityLabels = {
        'standard': '<span class="badge" style="background:#e0f2fe;color:#0369a1;padding:2px 8px;border-radius:99px;font-size:.72rem;">Standard (1.0x)</span>',
        'complex': '<span class="badge" style="background:#fef3c7;color:#b45309;padding:2px 8px;border-radius:99px;font-size:.72rem;">Complex (1.2x)</span>',
        'hazardous': '<span class="badge" style="background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:99px;font-size:.72rem;">Hazardous / High-Rise (1.5x)</span>'
    };
    var complexityHtml = complexityLabels[job.complexity_level] || '<span class="badge" style="background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:99px;font-size:.72rem;">Standard</span>';

    // Work Timer Section
    var timerSection = '';
    if (['accepted', 'en_route', 'in_progress'].includes(job.raw_status)) {
        var timerStatusText = job.work_started_at 
            ? (job.work_ended_at ? 'Completed at ' + job.work_ended_at : 'Started at ' + job.work_started_at + ' (Work Active)') 
            : 'Not yet started';
        var timerBtn = !job.work_started_at 
            ? '<button type="button" class="btn btn-sm btn-solid" style="background:#16a34a;padding:4px 10px;font-size:.78rem;gap:4px;" onclick="startWorkTimer(' + job.id + ')"><i class="fa-solid fa-play"></i> Start Work Timer</button>'
            : (!job.work_ended_at 
                ? '<button type="button" class="btn btn-sm btn-solid" style="background:#dc2626;padding:4px 10px;font-size:.78rem;gap:4px;" onclick="endWorkTimer(' + job.id + ')"><i class="fa-solid fa-stop"></i> End Work Timer</button>'
                : '<span style="color:#16a34a;font-size:.8rem;font-weight:600;"><i class="fa-solid fa-circle-check"></i> Work Logged</span>');

        timerSection = '<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 12px;margin-top:12px;display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">' +
            '<div>' +
                '<div style="font-size:.8rem;font-weight:700;color:#1e293b;"><i class="fa-solid fa-stopwatch"></i> On-Site Work Timer</div>' +
                '<div style="font-size:.75rem;color:#64748b;">' + timerStatusText + '</div>' +
            '</div>' +
            timerBtn +
        '</div>';
    }

    // Scope Amendment Section
    var scopeSection = '';
    if (job.scope_amendment_status === 'pending') {
        scopeSection = '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;margin-top:10px;font-size:.8rem;color:#92400e;">' +
            '<strong><i class="fa-solid fa-hourglass-half"></i> Scope Revision Proposal:</strong> ₱' + Number(job.scope_amendment_price).toLocaleString() + ' pending client approval.' +
        '</div>';
    } else if (job.scope_amendment_status === 'approved') {
        scopeSection = '<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:8px 12px;margin-top:10px;font-size:.8rem;color:#065f46;">' +
            '<strong><i class="fa-solid fa-circle-check"></i> Scope Revision Approved:</strong> ₱' + Number(job.price).toLocaleString() +
        '</div>';
    } else if (['accepted', 'en_route', 'in_progress'].includes(job.raw_status)) {
        scopeSection = '<div style="margin-top:10px;">' +
            '<button type="button" class="btn btn-sm btn-outline" style="padding:4px 10px;font-size:.78rem;gap:4px;" onclick="openScopeAmendmentModal(' + index + ')"><i class="fa-solid fa-file-pen"></i> Request Scope Revision</button>' +
        '</div>';
    }

    // Team / Peer Work Section
    var teamSection = '';
    if (job.team_status === 'suggested') {
        var suggestedCrewNames = (job.crew && job.crew.length > 0)
            ? job.crew.map(function(c){ return c.worker_name + ' (' + c.role + ')'; }).join(', ')
            : 'Pending client review';
        teamSection = '<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:8px 12px;margin-top:10px;font-size:.8rem;color:#1e40af;">' +
            '<strong><i class="fa-solid fa-users"></i> Recommended Peers:</strong> ' + suggestedCrewNames + ' (Pending client review).' +
        '</div>';
    } else if (job.team_status === 'client_approved') {
        var crewNames = (job.crew && job.crew.length > 0) ? job.crew.map(function(c){ return c.worker_name + ' (' + c.role + ')'; }).join(', ') : 'Assigned';
        teamSection = '<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:8px 12px;margin-top:10px;font-size:.8rem;color:#065f46;">' +
            '<strong><i class="fa-solid fa-users-check"></i> Approved Team:</strong> ' + crewNames +
        '</div>';
    } else if (['accepted', 'en_route', 'in_progress'].includes(job.raw_status)) {
        teamSection = '<div style="margin-top:10px;">' +
            '<button type="button" class="btn btn-sm btn-outline" style="padding:4px 10px;font-size:.78rem;gap:4px;" onclick="openSuggestTeamModal(' + index + ')"><i class="fa-solid fa-users-plus"></i> Suggest Peer Team</button>' +
        '</div>';
    }

    var completionBanner = '';
    if (job.is_completion_pending) {
        if (job.confirmed_by_worker_at) {
            completionBanner = '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 14px;margin-bottom:14px;color:#92400e;font-size:.85rem;display:flex;align-items:center;gap:10px;">' +
                '<i class="fa-solid fa-hourglass-half" style="font-size:1.1rem;color:#d97706;"></i>' +
                '<div><strong>Awaiting Client Confirmation:</strong> You have marked this job as complete. The client has been notified to verify and confirm completion.</div>' +
            '</div>';
        } else {
            completionBanner = '<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:10px 14px;margin-bottom:14px;color:#065f46;font-size:.85rem;display:flex;align-items:center;gap:10px;">' +
                '<i class="fa-solid fa-circle-check" style="font-size:1.1rem;color:#16a34a;"></i>' +
                '<div><strong>Client Verified Completion!</strong> The client has marked this job as complete. Click "Confirm Completion" below to finalize and receive your earnings.</div>' +
            '</div>';
        }
    }

    document.getElementById('jobModalTitle').textContent = 'Job Details';
    document.getElementById('jobModalDetails').innerHTML =
        completionBanner +
        enRouteBanner +
        '<div class="detail-grid-compact">' +
            '<span class="detail-label">Reference</span>' +
            '<span class="detail-value">' + (job.booking_ref || 'BK-' + String(job.id).padStart(5,'0')) + '</span>' +
            '<span class="detail-label">Client</span>' +
            '<span class="detail-value">' + job.client + '</span>' +
            '<span class="detail-label">Property</span>' +
            '<span class="detail-value">' + propType + '</span>' +
            '<span class="detail-label">Billing</span>' +
            '<span class="detail-value">' + pricingType + '</span>' +
            '<span class="detail-label">Complexity</span>' +
            '<span class="detail-value">' + complexityHtml + '</span>' +
            '<span class="detail-label">Service</span>' +
            '<span class="detail-value">' + job.service + '</span>' +
            '<span class="detail-label">Schedule</span>' +
            '<span class="detail-value">' + job.date + '</span>' +
            '<span class="detail-label">Location</span>' +
            '<span class="detail-value">' + (job.barangay ? job.barangay + ', ' : '') + job.location + '</span>' +
            '<span class="detail-label">Navigation</span>' +
            '<span class="detail-value"><a href="' + navUrl + '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline" style="display:inline-flex;align-items:center;gap:6px;padding:3px 10px;font-size:.78rem;"><i class="fa-solid fa-diamond-turn-right" style="color:#2563eb;"></i> Open in Google Maps</a></span>' +
            '<span class="detail-label">Amount</span>' +
            '<span class="detail-value" style="font-weight:600;">₱' + Number(job.price).toLocaleString() + '</span>' +
            '<span class="detail-label">Description</span>' +
            '<span class="detail-value">' + desc + '</span>' +
            (cancelledAt ? '<span class="detail-label">Cancelled At</span><span class="detail-value">' + cancelledAt + '</span>' : '') +
            (cancelReason ? '<span class="detail-label">Cancel Reason</span><span class="detail-value">' + cancelReason + '</span>' : '') +
        '</div>' +
        timerSection +
        scopeSection +
        teamSection;

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
        var testimonialLink = job.raw_status === 'completed'
            ? '<a href="{{ route('worker.testimonials.create') }}" class="btn btn-outline"><i class="fa-solid fa-quote-left" aria-hidden="true"></i> Share a Testimonial</a>'
            : '';
        footer.innerHTML = testimonialLink + '<button type="button" class="btn btn-outline" onclick="closeJobModal()">Close</button>';
    } else if (job.raw_status === 'in_progress' && job.is_completion_pending) {
        if (job.confirmed_by_worker_at) {
            footer.innerHTML =
                '<button type="button" class="btn btn-outline" onclick="closeJobModal()">Close</button>' +
                '<button type="button" class="btn btn-outline" disabled style="opacity:.8;cursor:not-allowed;"><i class="fa-solid fa-clock"></i> Awaiting Client Confirmation</button>';
        } else {
            footer.innerHTML =
                '<button type="button" class="btn btn-outline" onclick="closeJobModal(); showCancelModal(' + index + ')">Cancel Job</button>' +
                '<button type="button" class="btn btn-solid" style="background:#16a34a;" onclick="closeJobModal(); showConfirmModal(' + index + ')"><i class="fa-regular fa-circle-check"></i> Confirm Completion</button>';
        }
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

// ── Confirm / Cancel / Scope / Team ──
function closeModals(e) {
    if (e && e.target && !e.target.closest) return;
    document.getElementById('confirmModal').style.display = 'none';
    document.getElementById('cancelModal').style.display = 'none';
    var sam = document.getElementById('scopeAmendmentModal');
    if (sam) sam.style.display = 'none';
    var stm = document.getElementById('suggestTeamModal');
    if (stm) stm.style.display = 'none';
    cancelIndex = null;
    currentActionJobId = null;
}

function startWorkTimer(jobId) {
    fetch('/worker/jobs/' + jobId + '/start-timer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Work timer started.', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Could not start timer.', 'error');
        }
    })
    .catch(() => showToast('Error starting work timer.', 'error'));
}

function endWorkTimer(jobId) {
    fetch('/worker/jobs/' + jobId + '/end-timer', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Work timer completed.', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Could not complete timer.', 'error');
        }
    })
    .catch(() => showToast('Error stopping work timer.', 'error'));
}

function openScopeAmendmentModal(index) {
    var job = jobs[index];
    if (!job) return;
    currentActionJobId = job.id;
    document.getElementById('amendmentPriceInput').value = job.price || '';
    document.getElementById('amendmentNotesInput').value = '';
    closeJobModal();
    document.getElementById('scopeAmendmentModal').style.display = 'flex';
}

function submitScopeAmendment() {
    if (!currentActionJobId) return;
    var price = document.getElementById('amendmentPriceInput').value;
    var notes = document.getElementById('amendmentNotesInput').value.trim();

    if (!price || price <= 0) {
        showToast('Please enter a valid amendment price.', 'warning');
        return;
    }
    if (!notes) {
        showToast('Please provide a justification for this scope change.', 'warning');
        return;
    }

    var btn = document.getElementById('submitAmendmentBtn');
    btn.disabled = true;
    btn.textContent = 'Submitting…';

    fetch('/worker/jobs/' + currentActionJobId + '/request-scope-amendment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ amendment_price: price, notes: notes }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            btn.disabled = false;
            btn.textContent = 'Submit Proposal';
            showToast(data.message || 'Failed to submit proposal.', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Submit Proposal';
        showToast('Error submitting scope amendment.', 'error');
    });
}

const availablePeersList = @json($availablePeers ?? []);

function getUniqueSpecializations() {
    var list = [];
    availablePeersList.forEach(function(p) {
        if (p.service_category && !list.includes(p.service_category)) {
            list.push(p.service_category);
        }
    });
    return list.sort();
}

function buildWorkerSelectOptions(selectedCategory, selectedWorkerId) {
    var placeholder = selectedCategory
        ? '-- Select ' + selectedCategory + ' Worker --'
        : '-- Select Worker (e.g. Person B) --';
    var html = '<option value="">' + placeholder + '</option>';

    var filtered = availablePeersList.filter(function(p) {
        return !selectedCategory || p.service_category === selectedCategory;
    });

    filtered.forEach(function(p) {
        var sel = (String(p.id) === String(selectedWorkerId)) ? 'selected' : '';
        var workerName = (p.name && p.name.trim().length > 0)
            ? p.name.trim()
            : (((p.first_name || '') + ' ' + (p.last_name || '')).trim() || 'Worker #' + p.id);
        var category = p.service_category ? ' — ' + p.service_category : '';
        html += '<option value="' + p.id + '" ' + sel + '>' + workerName + category + '</option>';
    });

    return html;
}

function onPeerCategoryChange(catSelectElem) {
    var row = catSelectElem.closest('.peer-suggestion-row');
    if (!row) return;
    var cat = catSelectElem.value;
    var selectElem = row.querySelector('.peer-select-elem');
    if (!selectElem) return;

    var currentVal = selectElem.value;
    selectElem.innerHTML = buildWorkerSelectOptions(cat, currentVal);
}

function addPeerSuggestionRow(peerId, role, payout) {
    peerId = peerId || '';
    role = role || '';
    payout = payout || '';

    var container = document.getElementById('peerRowsContainer');
    if (!container) return;

    var initialCat = '';
    if (peerId) {
        var found = availablePeersList.find(function(p) { return String(p.id) === String(peerId); });
        if (found && found.service_category) {
            initialCat = found.service_category;
        }
    }

    var specs = getUniqueSpecializations();
    var specOptHtml = '<option value="">All Specializations</option>';
    specs.forEach(function(s) {
        var sSel = (s === initialCat) ? 'selected' : '';
        specOptHtml += '<option value="' + s + '" ' + sSel + '>' + s + '</option>';
    });

    var workerOptHtml = buildWorkerSelectOptions(initialCat, peerId);

    var row = document.createElement('div');
    row.className = 'peer-suggestion-row';
    row.style = 'background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;';
    row.innerHTML =
        '<div style="display:grid;grid-template-columns:160px 1fr auto;gap:10px;align-items:center;margin-bottom:8px;" class="peer-row-top-grid">' +
            '<div>' +
                '<label style="font-size:.78rem;font-weight:600;color:var(--g7);display:block;margin-bottom:3px;"><i class="fa-solid fa-filter" style="font-size:.7rem;color:#64748b;"></i> Specialization</label>' +
                '<select class="form-input peer-category-elem" onchange="onPeerCategoryChange(this)" style="width:100%;padding:7px 10px;border:1px solid var(--g3);border-radius:7px;font-size:.82rem;background:#fff;">' +
                    specOptHtml +
                '</select>' +
            '</div>' +
            '<div>' +
                '<label style="font-size:.78rem;font-weight:600;color:var(--g7);display:block;margin-bottom:3px;"><i class="fa-solid fa-user" style="font-size:.7rem;color:#64748b;"></i> Worker Name *</label>' +
                '<select class="form-input peer-select-elem" style="width:100%;padding:7px 10px;border:1px solid var(--g3);border-radius:7px;font-size:.85rem;background:#fff;">' +
                    workerOptHtml +
                '</select>' +
            '</div>' +
            '<div style="align-self:flex-end;">' +
                '<button type="button" class="btn btn-sm btn-ghost" onclick="removePeerSuggestionRow(this)" style="color:#dc2626;padding:6px 10px;font-size:.8rem;" title="Remove Worker">' +
                    '<i class="fa-solid fa-trash-can"></i>' +
                '</button>' +
            '</div>' +
        '</div>' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">' +
            '<div>' +
                '<label style="font-size:.78rem;font-weight:600;color:var(--g7);display:block;margin-bottom:3px;">Designated Role</label>' +
                '<input type="text" class="form-input peer-role-elem" value="' + role + '" placeholder="e.g. Lead Assistant, Electrician" style="width:100%;padding:7px 10px;border:1px solid var(--g3);border-radius:7px;font-size:.82rem;">' +
            '</div>' +
            '<div>' +
                '<label style="font-size:.78rem;font-weight:600;color:var(--g7);display:block;margin-bottom:3px;">Payout Share (₱)</label>' +
                '<input type="number" class="form-input peer-payout-elem" value="' + payout + '" placeholder="e.g. 600" min="0" style="width:100%;padding:7px 10px;border:1px solid var(--g3);border-radius:7px;font-size:.82rem;">' +
            '</div>' +
        '</div>';

    container.appendChild(row);
}

function removePeerSuggestionRow(btn) {
    var container = document.getElementById('peerRowsContainer');
    var rows = container.querySelectorAll('.peer-suggestion-row');
    if (rows.length <= 1) {
        showToast('At least one worker is required to suggest a team.', 'warning');
        return;
    }
    var row = btn.closest('.peer-suggestion-row');
    if (row) row.remove();
}

function openSuggestTeamModal(index) {
    var job = jobs[index];
    if (!job) return;
    currentActionJobId = job.id;

    var container = document.getElementById('peerRowsContainer');
    container.innerHTML = '';
    addPeerSuggestionRow();

    document.getElementById('teamJustificationInput').value = '';
    closeJobModal();
    document.getElementById('suggestTeamModal').style.display = 'flex';
}

function submitTeamSuggestion() {
    if (!currentActionJobId) return;

    var container = document.getElementById('peerRowsContainer');
    var rows = container.querySelectorAll('.peer-suggestion-row');
    if (rows.length === 0) {
        showToast('Please add at least one worker.', 'warning');
        return;
    }

    var peerIds = [];
    var roles = [];
    var payouts = [];
    var selectedSet = new Set();
    var hasError = false;

    rows.forEach(function(row) {
        if (hasError) return;
        var sel = row.querySelector('.peer-select-elem');
        var roleInput = row.querySelector('.peer-role-elem');
        var payoutInput = row.querySelector('.peer-payout-elem');

        var pid = sel ? sel.value : '';
        if (!pid) {
            hasError = true;
            showToast('Please select a worker for all rows.', 'warning');
            return;
        }

        if (selectedSet.has(pid)) {
            hasError = true;
            showToast('The same peer worker was selected more than once.', 'warning');
            return;
        }
        selectedSet.add(pid);

        peerIds.push(pid);
        roles.push(roleInput ? (roleInput.value.trim() || 'Assistant') : 'Assistant');
        payouts.push(payoutInput ? (parseFloat(payoutInput.value) || 0) : 0);
    });

    if (hasError) return;

    var justification = document.getElementById('teamJustificationInput').value.trim();
    if (!justification) {
        showToast('Please provide a recommendation justification for the client.', 'warning');
        return;
    }

    var btn = document.getElementById('submitTeamBtn');
    btn.disabled = true;
    btn.textContent = 'Sending…';

    fetch('/worker/jobs/' + currentActionJobId + '/suggest-team', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({
            peer_ids: peerIds,
            roles: roles,
            payouts: payouts,
            justification: justification
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else {
            btn.disabled = false;
            btn.textContent = 'Send Recommendation';
            showToast(data.message || 'Failed to send recommendation.', 'error');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Send Recommendation';
        showToast('Error sending team recommendation.', 'error');
    });
}

function showConfirmModal(index) {
    var job = jobs[index];
    if (!job) return;
    var step = { 'new':{a:'accepted',l:'Accept',v:'accepting'}, 'accepted':{a:'en_route',l:'Mark as En Route',v:'marking as en route'}, 'en_route':{a:'in_progress',l:'Start',v:'starting'}, 'in_progress':{a:'completed',l:'Complete',v:'completing'} }[job.raw_status];
    if (!step) return;
    var titles = { 'new':'Accept Job Request', 'accepted':'Mark as En Route', 'en_route':'Start Job', 'in_progress':'Complete Job' };

    var actionNotice = 'You are about to <strong>' + step.v + '</strong> the following job:';
    if (job.raw_status === 'in_progress') {
        if (job.confirmed_by_client_at) {
            titles['in_progress'] = 'Confirm Job Completion';
            step.l = 'Confirm & Finalize';
            step.v = 'confirming completion of';
            actionNotice = 'The client has verified completion. Confirming will finalize this booking and record your earnings:';
        } else {
            titles['in_progress'] = 'Request Job Completion';
            step.l = 'Send Completion Request';
            step.v = 'requesting completion of';
            actionNotice = 'You are marking this job as complete. A confirmation request will be sent to the client to verify:';
        }
    }

    var agreeHtml = (job.raw_status === 'new')
        ? '<div class="agreement-check-wrap"><label class="agreement-check"><input type="checkbox" id="agree-terms-worker"><span>I agree to the <a href="{{ url('/terms') }}" target="_blank">Terms of Service</a> and confirm the details above to accept this booking.</span></label></div>'
        : '';
    document.getElementById('confirmModalTitle').textContent = titles[job.raw_status] || 'Confirm';
    document.getElementById('confirmModalBody').innerHTML =
        '<p style="margin:0 0 12px;color:var(--g6);">You are about to <strong>' + step.v + '</strong> the following job:</p>' +
        '<p style="margin:0 0 12px;color:var(--g6);">' + actionNotice + '</p>' +
        '<div class="detail-grid-compact">' +
            '<span class="detail-label">Client</span><span class="detail-value">' + job.client + '</span>' +
            '<span class="detail-label">Service</span><span class="detail-value">' + job.service + '</span>' +
            '<span class="detail-label">Schedule</span><span class="detail-value">' + job.date + '</span>' +
            '<span class="detail-label">Amount</span><span class="detail-value">₱' + Number(job.price).toLocaleString() + '</span>' +
        '</div>' +
        '<p style="margin:14px 0 0;font-size:.82rem;color:var(--g4);">This action cannot be undone.</p>' +
        agreeHtml;
    document.getElementById('confirmForm').action = '{{ route("worker.jobs.status", "__ID__") }}'.replace('__ID__', job.id);

    var statusUrl = window.location.origin + '{{ route("worker.jobs.status", "__ID__", false) }}'.replace('__ID__', job.id);
    document.getElementById('confirmForm').action = statusUrl;
    document.getElementById('confirmStatus').value = step.a;
    document.getElementById('confirmSubmit').textContent = step.l;

    document.getElementById('confirmForm').onsubmit = function(e) {
        e.preventDefault();
        if (agreeHtml && !document.getElementById('agree-terms-worker').checked) {
            e.preventDefault();
            alert('Please agree to the Service Agreement before accepting.');
            if (window.showToast) window.showToast('Please agree to the Service Agreement before accepting.', 'warning');
            else alert('Please agree to the Service Agreement before accepting.');
            return false;
        }

        var btn = document.getElementById('confirmSubmit');
        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        fetch(statusUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: step.a })
        })
        .then(function(r) {
            return r.json().then(function(data) { return { ok: r.ok, status: r.status, data: data }; });
        })
        .then(function(res) {
            if (res.ok) {
                if (window.showToast) window.showToast(res.data.message || 'Status updated successfully.', 'success');
                setTimeout(function() { location.reload(); }, 600);
            } else {
                btn.disabled = false;
                btn.innerHTML = originalText;
                var errMsg = (res.data && res.data.message) ? res.data.message : 'Could not update job status.';
                if (window.showToast) window.showToast(errMsg, 'error');
                else alert(errMsg);
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (window.showToast) window.showToast('Network error while updating job status. Please try again.', 'error');
            else alert('Network error while updating job status.');
        });
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

// ── Calendar ──
(function() {
    var calYear = {{ now()->year }};
    var calMonth = {{ now()->month }};
    var calData = null;
    var isLoading = false;

    window.switchView = function(view) {
        document.getElementById('listViewBtn').classList.toggle('active', view === 'list');
        document.getElementById('calendarViewBtn').classList.toggle('active', view === 'calendar');
        document.getElementById('calendarSection').style.display = view === 'calendar' ? 'block' : 'none';
        document.querySelector('.card-panel').style.display = view === 'list' ? 'block' : 'none';
        document.getElementById('jobStatusDropdown').style.display = view === 'list' ? 'flex' : 'none';
        if (view === 'calendar') {
            loadCalendar();
        }
    };

    window.calNav = function(dir) {
        if (isLoading) return;
        calMonth += dir;
        if (calMonth > 12) { calMonth = 1; calYear++; }
        if (calMonth < 1) { calMonth = 12; calYear--; }
        loadCalendar();
    };

    window.calGoToday = function() {
        if (isLoading) return;
        calYear = {{ now()->year }};
        calMonth = {{ now()->month }};
        loadCalendar();
    };

    function loadCalendar() {
        if (isLoading) return;
        isLoading = true;
        var url = '/worker/calendar/data?year=' + calYear + '&month=' + calMonth;
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                calData = data;
                renderCalendar();
            })
            .catch(function() { console.error('Failed to load calendar data'); })
            .finally(function() { isLoading = false; });
    }

    function renderCalendar() {
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        document.getElementById('calMonthYear').textContent = monthNames[calMonth - 1] + ' ' + calYear;

        var grid = document.getElementById('calendarGrid');
        var firstDay = new Date(calYear, calMonth - 1, 1).getDay();
        var daysInMonth = new Date(calYear, calMonth, 0).getDate();
        var daysInPrevMonth = new Date(calYear, calMonth - 1, 0).getDate();

        // Remove existing day cells (keep weekday headers)
        var existingDays = grid.querySelectorAll('.calendar-day');
        existingDays.forEach(function(d) { d.remove(); });

        // Prev month trailing days
        for (var i = firstDay - 1; i >= 0; i--) {
            var dayEl = document.createElement('div');
            dayEl.className = 'calendar-day other-month empty';
            dayEl.innerHTML = '<span class="day-number">' + (daysInPrevMonth - i) + '</span><div class="day-empty">-</div>';
            grid.appendChild(dayEl);
        }

        // Current month days
        for (var d = 1; d <= daysInMonth; d++) {
            var dayEl = document.createElement('div');
            var isToday = calData && calData.today === d && calData.year === calYear && calData.month === calMonth;
            dayEl.className = 'calendar-day';
            if (isToday) dayEl.classList.add('today');
            if (calData && calData.days[d] && calData.days[d].length > 0) {
                dayEl.classList.add('has-jobs');
                var dominantStatus = calData.days[d][0].raw_status;
                dayEl.classList.add('status-' + dominantStatus);
            } else {
                dayEl.classList.add('empty');
            }
            dayEl.innerHTML = '<span class="day-number">' + d + '</span><div class="day-jobs"></div>';
            dayEl.onclick = function(day) { return function() { openCalDayModal(day); }; }(d);
            grid.appendChild(dayEl);

            // Fill in job chips
            if (calData && calData.days[d]) {
                var jobsContainer = dayEl.querySelector('.day-jobs');
                calData.days[d].forEach(function(job) {
                    var chip = document.createElement('div');
                    chip.className = 'day-job-chip status-' + job.raw_status;
                    chip.innerHTML = '<span class="chip-time">' + job.time + '</span><span class="chip-service">' + job.service + '</span>';
                    chip.onclick = function(e) {
                        e.stopPropagation();
                        openCalDayModal(day);
                    };
                    jobsContainer.appendChild(chip);
                });
            }
        }

        // Next month leading days
        var totalCells = firstDay + daysInMonth;
        var remaining = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
        for (var n = 1; n <= remaining; n++) {
            var dayEl = document.createElement('div');
            dayEl.className = 'calendar-day other-month empty';
            dayEl.innerHTML = '<span class="day-number">' + n + '</span><div class="day-empty">-</div>';
            grid.appendChild(dayEl);
        }
    }

    var currentItinerary = null;
    var calMapInstance = null;

    function loadLeaflet(cb) {
        if (typeof L !== 'undefined') { cb(); return; }
        if (!document.querySelector('link[href*="leaflet.css"]')) {
            var l = document.createElement('link');
            l.rel = 'stylesheet';
            l.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(l);
        }
        var s = document.createElement('script');
        s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        s.onload = cb;
        document.head.appendChild(s);
    }

    window.toggleCalMap = function() {
        var container = document.getElementById('calMapContainer');
        var btnText = document.getElementById('calMapToggleText');
        if (!container) return;

        if (container.style.display === 'none' || !container.style.display) {
            container.style.display = 'block';
            if (btnText) btnText.textContent = 'Hide Map';
            loadLeaflet(function() {
                renderRouteMap();
            });
        } else {
            container.style.display = 'none';
            if (btnText) btnText.textContent = 'Show Map';
        }
    };

    function renderRouteMap() {
        if (!currentItinerary || !currentItinerary.waypoints || currentItinerary.waypoints.length === 0) return;

        var mapEl = document.getElementById('calRouteMap');
        if (!mapEl) return;

        if (calMapInstance) {
            calMapInstance.remove();
            calMapInstance = null;
        }

        calMapInstance = L.map('calRouteMap', { zoomControl: true });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
            maxZoom: 18
        }).addTo(calMapInstance);

        var bounds = [];
        var routeCoords = [];

        currentItinerary.waypoints.forEach(function(wp) {
            if (!wp.latitude || !wp.longitude) return;
            var pt = [parseFloat(wp.latitude), parseFloat(wp.longitude)];
            bounds.push(pt);
            routeCoords.push(pt);

            if (wp.type === 'base') {
                var homeIcon = L.divIcon({
                    className: 'route-map-pin pin-base',
                    html: '<i class="fa-solid fa-house"></i>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                L.marker(pt, { icon: homeIcon })
                    .addTo(calMapInstance)
                    .bindPopup('<strong>' + (wp.label || 'Home Base') + '</strong><br><small>Departure Base</small>');
            } else {
                var numIcon = L.divIcon({
                    className: 'route-map-pin pin-stop',
                    html: '<span>' + wp.step + '</span>',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                var navLink = wp.nav_url
                    ? '<br><a href="' + wp.nav_url + '" target="_blank" rel="noopener noreferrer" style="color:#2563eb;font-weight:600;display:inline-block;margin-top:6px;"><i class="fa-solid fa-diamond-turn-right"></i> Navigate (Google Maps)</a>'
                    : '';
                L.marker(pt, { icon: numIcon })
                    .addTo(calMapInstance)
                    .bindPopup('<strong>Stop #' + wp.step + ': ' + wp.service + '</strong><br>' +
                        wp.client + ' • ' + (wp.time || '') + '<br>' +
                        '<small>' + (wp.barangay || '') + '</small>' + navLink);
            }
        });

        if (currentItinerary.legs && currentItinerary.legs.length > 0) {
            currentItinerary.legs.forEach(function(leg) {
                var legColor = leg.feasibility === 'conflict' ? '#ef4444' : (leg.feasibility === 'tight' ? '#f59e0b' : '#2563eb');
                var legPts = (Array.isArray(leg.geometry) && leg.geometry.length > 1) ? leg.geometry : [];
                if (legPts.length > 0) {
                    legPts.forEach(function(p) { bounds.push(p); });
                    var poly = L.polyline(legPts, {
                        color: legColor,
                        weight: 5,
                        opacity: 0.88,
                        lineJoin: 'round',
                        lineCap: 'round'
                    }).addTo(calMapInstance);
                    poly.bindPopup('<strong>' + leg.from_title + ' &rarr; ' + leg.to_title + '</strong><br>' + leg.formatted_distance + ' • ~' + leg.formatted_time + ' transit');
                }
            });
        } else if (routeCoords.length > 1) {
            L.polyline(routeCoords, {
                color: '#2563eb',
                weight: 4,
                opacity: 0.85,
                dashArray: '6, 8',
                lineJoin: 'round'
            }).addTo(calMapInstance);
        }

        if (bounds.length > 0) {
            calMapInstance.fitBounds(bounds, { padding: [30, 30] });
        } else {
            calMapInstance.setView([14.0245, 120.7300], 13);
        }

        setTimeout(function() {
            if (calMapInstance) calMapInstance.invalidateSize();
        }, 200);
    }

    var currentCalDayFormatted = null;

    window.toggleRouteOptimization = function() {
        var box = document.getElementById('calOptimizationBox');
        if (!box) return;

        if (box.style.display === 'block') {
            box.style.display = 'none';
            return;
        }

        if (!currentCalDayFormatted) return;

        var badge = document.getElementById('calOptSavingsBadge');
        var stepsEl = document.getElementById('calOptSteps');
        badge.textContent = 'Analyzing...';
        stepsEl.innerHTML = '<div style="color:var(--g5);"><i class="fa-solid fa-spinner fa-spin"></i> Calculating shortest road path...</div>';
        box.style.display = 'block';

        fetch('/worker/calendar/optimize-route?date=' + currentCalDayFormatted)
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var opt = res.optimization;
                if (!opt) {
                    stepsEl.innerHTML = '<div>Optimization unavailable for this date.</div>';
                    return;
                }
                badge.textContent = opt.formatted_savings;

                var html = '<ol style="margin:6px 0 0;padding-left:18px;line-height:1.6;">';
                opt.ordered_bookings.forEach(function(b) {
                    html += '<li><strong>' + b.service + '</strong> (' + (b.barangay || 'Tuy') + ') — ' + b.client + ' <span style="color:var(--g5);">[Originally ' + b.current_time + ']</span></li>';
                });
                html += '</ol>';
                stepsEl.innerHTML = html;
            })
            .catch(function() {
                stepsEl.innerHTML = '<div style="color:var(--d10);">Failed to load route optimization.</div>';
            });
    };

    window.openCalDayModal = function(day) {
        if (!calData || !calData.days[day]) return;
        var dayJobs = calData.days[day];
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        document.getElementById('calModalTitle').textContent = 'Jobs on ' + monthNames[calData.month - 1] + ' ' + day + ', ' + calData.year;
        document.getElementById('calModalDate').textContent = monthNames[calData.month - 1] + ' ' + day + ', ' + calData.year;

        // Reset Route Bar and Map container
        var routeBar = document.getElementById('calRouteBar');
        var mapContainer = document.getElementById('calMapContainer');
        var mapToggleText = document.getElementById('calMapToggleText');
        var optBtn = document.getElementById('calOptimizeBtn');
        var optBox = document.getElementById('calOptimizationBox');
        if (mapContainer) mapContainer.style.display = 'none';
        if (mapToggleText) mapToggleText.textContent = 'Show Map';
        if (optBox) optBox.style.display = 'none';

        var formattedDate = calYear + '-' + String(calMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        currentCalDayFormatted = formattedDate;

        fetch('/worker/calendar/route?date=' + formattedDate)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.itinerary && data.itinerary.total_jobs > 0) {
                    currentItinerary = data.itinerary;
                    document.getElementById('routeTotalJobs').textContent = currentItinerary.total_jobs;
                    document.getElementById('routeTotalDist').textContent = currentItinerary.formatted_total_dist;
                    document.getElementById('routeTotalTime').textContent = currentItinerary.formatted_total_time;
                    if (routeBar) routeBar.style.display = 'flex';
                    if (optBtn) optBtn.style.display = currentItinerary.total_jobs >= 2 ? 'inline-flex' : 'none';
                } else {
                    currentItinerary = null;
                    if (routeBar) routeBar.style.display = 'none';
                    if (optBtn) optBtn.style.display = 'none';
                }
            })
            .catch(function() {
                currentItinerary = null;
                if (routeBar) routeBar.style.display = 'none';
                if (optBtn) optBtn.style.display = 'none';
            });

        var jobsContainer = document.getElementById('calModalJobs');
        jobsContainer.innerHTML = '';

        dayJobs.forEach(function(job, idx) {
            // ── Travel-time separator pill BEFORE this job (skip first job) ──
            if (idx > 0 && job.travel_from_prev) {
                var tp = job.travel_from_prev;
                var fStatus = job.feasibility || 'feasible';
                var fColors = {
                    conflict: { bg: '#fef2f2', border: '#fecaca', text: '#991b1b', icon: '🚨', label: 'Conflict' },
                    tight:    { bg: '#fffbeb', border: '#fde68a', text: '#92400e', icon: '⚠️',  label: 'Tight Buffer' },
                    feasible: { bg: '#f0fdf4', border: '#bbf7d0', text: '#15803d', icon: '🚗',  label: 'Feasible' },
                };
                var fc = fColors[fStatus] || fColors.feasible;
                var distStr = tp.road_distance_km ? tp.road_distance_km + ' km' : (tp.formatted_distance || '');
                var minStr  = tp.estimated_minutes ? '~' + tp.estimated_minutes + ' min' : (tp.formatted_time || '');

                var pill = document.createElement('div');
                pill.style.cssText = 'display:flex;align-items:center;justify-content:space-between;gap:10px;padding:7px 14px;margin:0;background:' + fc.bg + ';border-left:3px solid ' + fc.border + ';font-size:.78rem;color:' + fc.text + ';';
                pill.innerHTML =
                    '<span style="display:flex;align-items:center;gap:6px;">' +
                        '<span>' + fc.icon + '</span>' +
                        '<span><strong>Travel leg</strong>: ' + (distStr || '—') + (minStr ? ' · ' + minStr + ' transit' : '') + '</span>' +
                    '</span>' +
                    '<span style="font-size:.72rem;font-weight:700;background:' + fc.border + ';color:' + fc.text + ';padding:1px 8px;border-radius:99px;">' + fc.label + '</span>';
                jobsContainer.appendChild(pill);
            }

            var card = document.createElement('div');
            card.className = 'cal-job-card';
            card.onclick = function(e) {
                e.stopPropagation();
                closeCalModal();
                // Fetch full job details via AJAX, then open the job modal
                fetch('/worker/jobs/' + job.id + '/details')
                    .then(function(r) { return r.json(); })
                    .then(function(fullJob) {
                        jobs.push(fullJob);
                        openJobModal(jobs.length - 1);
                    })
                    .catch(function() { alert('Failed to load job details.'); });
            };

            var navBtnHtml = job.gmaps_nav_url
                ? '<a href="' + job.gmaps_nav_url + '" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline cal-nav-btn" onclick="event.stopPropagation()"><i class="fa-solid fa-diamond-turn-right" style="color:#2563eb;"></i> Navigate</a>'
                : '';

            card.innerHTML =
                '<div class="cal-job-card-header">' +
                    '<span class="cal-job-service"><span class="job-order-num">#' + (idx + 1) + '</span> ' + job.service + '</span>' +
                    '<span class="cal-job-status-badge status-' + job.raw_status + '">' + job.status + '</span>' +
                '</div>' +
                '<div class="cal-job-details">' +
                    '<div class="cal-job-row"><i class="fa-regular fa-clock"></i> ' + job.time + '</div>' +
                    '<div class="cal-job-row"><i class="fa-regular fa-user"></i> ' + job.client + '</div>' +
                    '<div class="cal-job-row"><i class="fa-solid fa-location-dot"></i> ' + (job.barangay ? job.barangay + ', ' : '') + job.location + '</div>' +
                    '<div class="cal-job-row" style="display:flex;justify-content:space-between;align-items:center;">' +
                        '<span><i class="fa-solid fa-peso-sign"></i> ' + Number(job.price).toLocaleString('en-PH', {minimumFractionDigits: 2}) + '</span>' +
                        navBtnHtml +
                    '</div>' +
                '</div>';

            jobsContainer.appendChild(card);
        });

        document.getElementById('calDayModal').classList.add('show');
    };

    window.closeCalModal = function(e) {
        if (e && e.target !== e.currentTarget) return;
        document.getElementById('calDayModal').classList.remove('show');
        if (calMapInstance) {
            calMapInstance.remove();
            calMapInstance = null;
        }
    };
})();

// ── Worker Live Location GPS Broadcaster ──
(function() {
    var gpsWatcherId = null;
    var gpsIntervalId = null;
    var currentTrackingJob = null;
    var lastCoords = null;

    function initWorkerGpsTracking() {
        if (!Array.isArray(jobs) || jobs.length === 0) return;

        // Check if there is any active en_route job
        var enRouteJob = jobs.find(function(j) { return j.raw_status === 'en_route'; });
        if (!enRouteJob) return;

        currentTrackingJob = enRouteJob;
        var banner = document.getElementById('workerLiveTrackingBanner');
        var subtext = document.getElementById('workerLiveBannerSubtext');
        var navBtn = document.getElementById('workerLiveNavBtn');
        var distBadge = document.getElementById('workerLiveDistBadge');

        if (banner) {
            banner.style.display = 'flex';
            if (subtext) {
                subtext.innerHTML = 'En route to <strong>' + enRouteJob.client + '</strong> · ' + enRouteJob.service;
            }
            if (navBtn && enRouteJob.gmaps_nav_url) {
                navBtn.href = enRouteJob.gmaps_nav_url;
            }
        }

        if (!navigator.geolocation) {
            if (distBadge) distBadge.textContent = 'GPS not supported';
            return;
        }

        function sendPing(coords) {
            if (!currentTrackingJob) return;
            lastCoords = coords;
            fetch('/worker/jobs/' + currentTrackingJob.id + '/location-ping', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    latitude: coords.latitude,
                    longitude: coords.longitude,
                    heading: coords.heading || null,
                    speed: coords.speed || null
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success && distBadge) {
                    distBadge.innerHTML = '<i class="fa-solid fa-location-arrow" style="margin-right:4px;"></i>' + res.formatted_dist + ' · ~' + res.formatted_time;
                }
            })
            .catch(function(err) {
                console.warn('GPS ping error:', err);
            });
        }

        // 1. Initial getCurrentPosition
        navigator.geolocation.getCurrentPosition(
            function(pos) { sendPing(pos.coords); },
            function(err) { console.warn('Geolocation initial pos error:', err); },
            { enableHighAccuracy: true, timeout: 10000 }
        );

        // 2. Continuous watchPosition for responsive updates as worker moves
        try {
            gpsWatcherId = navigator.geolocation.watchPosition(
                function(pos) { sendPing(pos.coords); },
                function(err) { console.warn('Geolocation watchPosition error:', err); },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 6000 }
            );
        } catch (e) {
            console.warn('watchPosition init error:', e);
        }

        // 3. Fallback interval every 10 seconds
        gpsIntervalId = setInterval(function() {
            if (lastCoords) {
                sendPing(lastCoords);
            } else {
                navigator.geolocation.getCurrentPosition(
                    function(pos) { sendPing(pos.coords); },
                    function() {},
                    { enableHighAccuracy: true }
                );
            }
        }, 10000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWorkerGpsTracking);
    } else {
        initWorkerGpsTracking();
    }

    window.addEventListener('beforeunload', function() {
        if (gpsWatcherId !== null) navigator.geolocation.clearWatch(gpsWatcherId);
        if (gpsIntervalId !== null) clearInterval(gpsIntervalId);
    });
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

/* Route Summary Bar & Transit Styling */
.cal-route-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid var(--g2, #e2e8f0);
    border-radius: 10px;
    padding: 10px 14px;
    margin: 12px 0 14px;
    flex-wrap: wrap;
    gap: 10px;
}
.cal-route-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: .84rem;
    color: var(--g7, #334155);
}
.cal-stat-item strong {
    font-weight: 700;
    color: var(--b9, #1e3a8a);
}
.cal-stat-lbl {
    color: var(--g4, #94a3b8);
    font-size: .78rem;
}
.cal-stat-sep {
    color: var(--g3, #cbd5e1);
}
.job-order-num {
    display: inline-block;
    background: #e2e8f0;
    color: #334155;
    font-size: .72rem;
    font-weight: 700;
    padding: 1px 6px;
    border-radius: 4px;
    margin-right: 4px;
}
.cal-travel-leg {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    font-size: .75rem;
    padding: 6px 10px;
    margin-top: 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: var(--g6, #475569);
    border-left: 3px solid #94a3b8;
}
.cal-travel-leg.conflict {
    background: #fee2e2;
    color: #991b1b;
    border-left-color: #ef4444;
}
.cal-travel-leg.tight {
    background: #fef3c7;
    color: #92400e;
    border-left-color: #f59e0b;
}
.cal-travel-leg.feasible {
    background: #f0fdf4;
    color: #166534;
    border-left-color: #22c55e;
}
.feasibility-badge {
    padding: 2px 6px;
    border-radius: 4px;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.feasibility-badge.conflict {
    background: #fecaca;
    color: #991b1b;
}
.feasibility-badge.tight {
    background: #fde68a;
    color: #92400e;
}
.feasibility-badge.feasible {
    background: #dcfce7;
    color: #15803d;
}
.cal-nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    font-size: .75rem;
    border-radius: 6px;
}
.route-map-pin {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    font-weight: 700;
    font-size: .75rem;
    box-shadow: 0 2px 8px rgba(0,0,0,.28);
    border: 2px solid #fff;
}
.route-map-pin.pin-base {
    background: #059669;
}
.route-map-pin.pin-stop {
    background: #2563eb;
}
</style>
@endpush
