@extends('layouts.worker')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('tabs')
    <a href="{{ route('worker.dashboard') }}"
       class="subtab {{ request()->routeIs('worker.dashboard') && !request()->routeIs('worker.dashboard.notifications') ? 'active' : '' }}">
        Overview
    </a>
    <a href="{{ route('worker.dashboard.notifications') }}"
       class="subtab {{ request()->routeIs('worker.dashboard.notifications') ? 'active' : '' }}">
        Notifications
    </a>
@endsection

@section('skeleton')
    <div class="sp-stats">
        <div class="skeleton skeleton-stat"><div class="skeleton skeleton-stat-circle"></div><div class="skeleton skeleton-title" style="width:40px;"></div><div class="skeleton skeleton-text-sm" style="width:80px;"></div></div>
        <div class="skeleton skeleton-stat"><div class="skeleton skeleton-stat-circle"></div><div class="skeleton skeleton-title" style="width:40px;"></div><div class="skeleton skeleton-text-sm" style="width:80px;"></div></div>
        <div class="skeleton skeleton-stat"><div class="skeleton skeleton-stat-circle"></div><div class="skeleton skeleton-title" style="width:40px;"></div><div class="skeleton skeleton-text-sm" style="width:80px;"></div></div>
        <div class="skeleton skeleton-stat"><div class="skeleton skeleton-stat-circle"></div><div class="skeleton skeleton-title" style="width:40px;"></div><div class="skeleton skeleton-text-sm" style="width:80px;"></div></div>
    </div>
    <div class="sp-panel">
        <div class="sp-title-row">
            <div class="skeleton skeleton-title" style="width:200px;"></div>
            <div class="skeleton skeleton-text-sm" style="width:60px;margin:0;"></div>
        </div>
        <div class="sp-table-header">
            <div class="skeleton skeleton-text" style="height:16px;"></div>
            <div class="skeleton skeleton-text" style="height:16px;"></div>
            <div class="skeleton skeleton-text" style="height:16px;"></div>
            <div class="skeleton skeleton-text" style="height:16px;width:80px;"></div>
            <div class="skeleton skeleton-text" style="height:16px;width:60px;"></div>
        </div>
        <div class="skeleton skeleton-table-row"></div>
        <div class="skeleton skeleton-table-row"></div>
        <div class="skeleton skeleton-table-row"></div>
    </div>
    <div class="sp-panel">
        <div class="sp-title-row">
            <div class="skeleton skeleton-title" style="width:180px;"></div>
            <div class="skeleton skeleton-text-sm" style="width:60px;margin:0;"></div>
        </div>
        <div style="display:flex;gap:16px;padding:16px;border-bottom:1px solid var(--g1);">
            <div class="skeleton" style="width:48px;height:48px;border-radius:10px;flex-shrink:0;"></div>
            <div style="flex:1;"><div class="skeleton skeleton-text" style="width:50%;"></div><div class="skeleton skeleton-text-sm" style="width:40%;"></div></div>
            <div class="skeleton skeleton-badge"></div>
        </div>
        <div style="display:flex;gap:16px;padding:16px;">
            <div class="skeleton" style="width:48px;height:48px;border-radius:10px;flex-shrink:0;"></div>
            <div style="flex:1;"><div class="skeleton skeleton-text" style="width:45%;"></div><div class="skeleton skeleton-text-sm" style="width:35%;"></div></div>
            <div class="skeleton skeleton-badge"></div>
        </div>
    </div>
@endsection

@section('content')

@php
    $firstName = explode(' ', auth()->user()->name ?? 'there')[0];
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

    $profile = auth()->user()->workerProfile;
    $needsLocation = !$profile
        || $profile->current_latitude === null
        || $profile->current_longitude === null
        || $profile->location_is_approximate === true;
@endphp

<div class="welcome-banner">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <p class="welcome-location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> <span id="displayedResidence">{{ auth()->user()->residence }}</span></p>
        <button type="button" class="btn btn-ghost btn-sm" id="shareLocationBtn" style="font-size:.78rem;padding:6px 14px;">
            <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Share My Location
        </button>
    </div>
    <h2>{{ $greeting }}, {{ $firstName }} 👋</h2>
    <p>Manage your jobs, track your earnings, and keep your clients happy — all in one place.</p>
    <div id="locMsg" style="display:none;margin-top:8px;font-size:.82rem;"></div>
</div>

{{-- Location onboarding popup — shown only when worker has no real GPS coordinates --}}
@if($needsLocation)
<div id="locationPromptModal" class="modal-overlay" style="display:flex;">
    <div class="modal-box" style="max-width:420px;" onclick="event.stopPropagation()">
        <div class="modal-header" style="background:var(--b0);border-bottom:1px solid var(--g1);">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#185FA5,#378ADD);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fa-solid fa-map-location-dot" style="color:#fff;font-size:1.1rem;"></i>
                </div>
                <div>
                    <h3 style="font-size:.95rem;font-weight:700;color:var(--g9);margin:0;">Set your location</h3>
                    <p style="font-size:.75rem;color:var(--g4);margin:2px 0 0;">Appear on client maps</p>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="dismissLocationPrompt()">&times;</button>
        </div>
        <div class="modal-body" style="padding:20px 22px;">
            <p style="font-size:.85rem;color:var(--g7);line-height:1.6;margin:0 0 16px;">
                KaAyos uses your location to help nearby clients find you through the AI suggestion system. When you share your real GPS location, your pin appears accurately on the client's map — making it easier for them to book you.
            </p>
            <div style="background:var(--off);border:1px solid var(--g1);border-radius:10px;padding:14px;">
                <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;">
                    <i class="fa-solid fa-check-circle" style="color:var(--green,#16a34a);margin-top:2px;flex-shrink:0;"></i>
                    <span style="font-size:.8rem;color:var(--g7);">Your pin shows accurately on client maps</span>
                </div>
                <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:10px;">
                    <i class="fa-solid fa-check-circle" style="color:var(--green,#16a34a);margin-top:2px;flex-shrink:0;"></i>
                    <span style="font-size:.8rem;color:var(--g7);">Clients nearby can discover your services</span>
                </div>
                <div style="display:flex;align-items:flex-start;gap:10px;">
                    <i class="fa-solid fa-check-circle" style="color:var(--green,#16a34a);margin-top:2px;flex-shrink:0;"></i>
                    <span style="font-size:.8rem;color:var(--g7);">Your location is never shared publicly</span>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding:14px 22px;border-top:1px solid var(--g1);display:flex;gap:10px;justify-content:flex-end;">
            <button type="button" class="btn btn-ghost" onclick="dismissLocationPrompt()" style="font-size:.82rem;">
                Maybe later
            </button>
            <button type="button" class="btn btn-solid" id="locationPromptShareBtn" onclick="triggerLocationShare()" style="font-size:.82rem;">
                <i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Share My Location
            </button>
        </div>
    </div>
</div>
@endif

<div class="stats-grid">
    @foreach($stats as $stat)
        <div class="stat-card {{ !empty($stat['accent']) ? 'accent' : '' }}">
            <div class="stat-icon"><i class="fa-solid {{ $stat['icon'] }}" aria-hidden="true"></i></div>
            <div class="stat-value">{{ $stat['value'] }}</div>
            <div class="stat-label">{{ $stat['label'] }}</div>
        </div>
    @endforeach
</div>

<div class="section-header">
    <h2 class="section-title">Recent Job Requests</h2>
    <a href="{{ route('worker.jobs') }}" class="link-action">View all</a>
</div>

<div class="card-panel">
    <table class="bookings-table">
        <thead>
            <tr>
                <th>Client</th>
                <th>Service</th>
                <th>Date</th>
                <th>Status</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($jobRequests, 0, 3) as $job)
                <tr>
                    <td><span class="booking-worker">{{ $job['client'] }}</span></td>
                    <td>{{ $job['service'] }}</td>
                    <td>{{ $job['date'] }}</td>
                    <td>
                        @php
                            $statusClass = match($job['status']) {
                                'Accepted' => 'status-active',
                                'Pending' => 'status-pending',
                                'Completed' => 'status-done',
                                default => 'status-cancelled',
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $job['status'] }}</span>
                    </td>
                    <td>₱{{ number_format($job['price']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="section-header">
    <h2 class="section-title">Upcoming Schedule</h2>
    <a href="{{ route('worker.schedule') }}" class="link-action">View all</a>
</div>

<div class="card-panel">
    @forelse(array_slice($schedule, 0, 2) as $item)
        <div style="display:flex;align-items:center;gap:16px;padding:16px 22px;border-bottom:1px solid var(--g1);">
            <div style="width:48px;height:48px;border-radius:10px;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa-solid fa-briefcase" aria-hidden="true"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.9rem;font-weight:600;color:var(--b9);">{{ $item['service'] }}</div>
                <div style="font-size:.82rem;color:var(--g4);margin-top:2px;">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $item['time'] }}
                    <span style="margin:0 6px;">·</span>
                    <i class="fa-regular fa-user" aria-hidden="true"></i> {{ $item['client'] }}
                </div>
            </div>
            <span class="status-badge {{ $item['status'] === 'Confirmed' ? 'status-active' : 'status-pending' }}">{{ $item['status'] }}</span>
        </div>
    @empty
        <div class="empty-state">
            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            <h3>No upcoming jobs</h3>
            <p>New bookings will appear here once clients request your service.</p>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
(function(){
    var btn = document.getElementById('shareLocationBtn');
    var msg = document.getElementById('locMsg');
    var promptModal = document.getElementById('locationPromptModal');
    if (!btn) return;

    function showMsg(text, isError) {
        if (!msg) return;
        msg.textContent = text;
        msg.style.display = 'block';
        msg.style.color = isError ? 'var(--red,#dc3545)' : 'var(--green,#16a34a)';
        if (!isError) {
            setTimeout(function(){ msg.style.display = 'none'; }, 5000);
        }
    }

    function dismissLocationPrompt() {
        if (promptModal) {
            promptModal.style.display = 'none';
        }
    }

    function triggerLocationShare() {
        dismissLocationPrompt();
        btn.click();
    }

    // When location is shared successfully, also close the prompt modal
    var _originalBtnListener = null;

    btn.addEventListener('click', function() {
        if (!navigator.geolocation) {
            showMsg('Your browser does not support location sharing.', true);
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Sharing…';
        showMsg('');

        navigator.geolocation.getCurrentPosition(
            function(position) {
                var lat = parseFloat(position.coords.latitude.toFixed(7));
                var lng = parseFloat(position.coords.longitude.toFixed(7));

                var csrf = document.querySelector('meta[name=csrf-token]');
                csrf = csrf ? csrf.content : (document.querySelector('input[name=_token]') ? document.querySelector('input[name=_token]').value : '');

                fetch('/worker/location', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ latitude: lat, longitude: lng }),
                })
                .then(function(r){ return r.json(); })
                .then(function(data) {
                    dismissLocationPrompt();
                    if (data.latitude && data.longitude) {
                        showMsg('Location shared successfully! Your pin now shows on the map.', false);
                        var residence = document.getElementById('displayedResidence');
                        if (residence) {
                            var brgyMatch = residence.textContent.match(/^Brgy\.\s*([^,]+)/);
                            if (brgyMatch) {
                                residence.textContent = 'Brgy. ' + brgyMatch[1] + ', Tuy, Batangas';
                            }
                        }
                    } else {
                        showMsg('Location saved. It will appear on client maps shortly.', false);
                    }
                })
                .catch(function() {
                    showMsg('Failed to share location. Please try again.', true);
                })
                .finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Share My Location';
                });
            },
            function(err) {
                var txt = 'Could not get your location.';
                if (err.code === 1) txt = 'Location permission denied. Please allow location access in your browser settings.';
                if (err.code === 2) txt = 'Location unavailable. Make sure GPS is enabled.';
                showMsg(txt, true);
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-location-crosshairs" aria-hidden="true"></i> Share My Location';
            },
            { timeout: 10000, maximumAge: 0 }
        );
    });
})();
</script>
@endpush

@endsection
