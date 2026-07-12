@extends('layouts.worker')

@section('title', 'My Schedule')
@section('page_title', 'My Schedule')

@section('topbar_actions')
    <a href="{{ route('worker.jobs') }}" class="btn btn-outline">
        <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i> Job Requests
    </a>
@endsection

@php
    $availability = auth()->user()->workerProfile?->availability ?? [];
    $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $dayShort = ['Monday'=>'Mon','Tuesday'=>'Tue','Wednesday'=>'Wed','Thursday'=>'Thu','Friday'=>'Fri','Saturday'=>'Sat','Sunday'=>'Sun'];
    $availByDay = [];
    foreach ($availability as $a) {
        $availByDay[$a['day']] = $a;
    }
@endphp

@section('content')

{{-- Weekly Availability --}}
<div class="card-panel" style="margin-bottom:18px;">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Your Schedule</div>
            <h2 class="section-title">Weekly Availability</h2>
        </div>
    </div>
    <div class="avail-days">
        @foreach($dayNames as $day)
            @php
                $a = $availByDay[$day] ?? null;
                $active = $a && ($a['active'] ?? false);
            @endphp
            <div class="avail-row {{ !$active ? 'avail-off' : '' }}">
                <span class="avail-day">{{ $dayShort[$day] ?? $day }}</span>
                @if($active)
                    <span class="avail-time">
                        {{ \Carbon\Carbon::createFromFormat('H:i', $a['start'])->format('g:i A') }} – {{ \Carbon\Carbon::createFromFormat('H:i', $a['end'])->format('g:i A') }}
                    </span>
                    <span class="avail-badge avail-badge-on">Available</span>
                @else
                    <span class="avail-time" style="color:var(--g4);">—</span>
                    <span class="avail-badge avail-badge-off">Off</span>
                @endif
            </div>
        @endforeach
    </div>
</div>

{{-- Upcoming / Past Jobs --}}
<div class="filter-pills" id="schedule-filters">
    <button type="button" class="filter-pill active" data-filter="upcoming">Upcoming</button>
    <button type="button" class="filter-pill" data-filter="past">Past Jobs</button>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Jobs</div>
            <h2 class="section-title" id="schedule-title">Upcoming Jobs</h2>
        </div>
    </div>

    <div id="upcoming-list">
        @forelse($schedule as $item)
            <div class="schedule-row">
                <div class="schedule-date-box">
                    <span class="schedule-month">{{ \Carbon\Carbon::parse($item['date'])->format('M') }}</span>
                    <span class="schedule-day">{{ \Carbon\Carbon::parse($item['date'])->format('d') }}</span>
                </div>
                <div class="schedule-info">
                    <div class="schedule-service">{{ $item['service'] }}</div>
                    <div class="schedule-meta">
                        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $item['time'] }}</span>
                    </div>
                    <div class="schedule-meta">
                        <span><i class="fa-regular fa-user" aria-hidden="true"></i> {{ $item['client'] }}</span>
                        <span style="margin:0 6px;">·</span>
                        <span><i class="fa-regular fa-location-dot" aria-hidden="true"></i> {{ $item['location'] }}</span>
                    </div>
                </div>
                @php
                    $statusClass = match($item['raw_status']) {
                        'new'        => 'status-pending',
                        'accepted'   => 'status-active',
                        'en_route'   => 'status-active',
                        'in_progress'=> 'status-active',
                        default      => 'status-pending',
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $item['status'] }}</span>
            </div>
        @empty
            <div class="empty-state" id="upcoming-empty">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <h3>No upcoming jobs</h3>
                <p>Your schedule will populate once you accept job requests from clients.</p>
            </div>
        @endforelse
    </div>

    <div id="past-list" style="display:none;">
        @forelse($pastSchedule as $item)
            <div class="schedule-row">
                <div class="schedule-date-box" style="background:var(--g0);color:var(--g5);">
                    <span class="schedule-month">{{ \Carbon\Carbon::parse($item['date'])->format('M') }}</span>
                    <span class="schedule-day">{{ \Carbon\Carbon::parse($item['date'])->format('d') }}</span>
                </div>
                <div class="schedule-info">
                    <div class="schedule-service">{{ $item['service'] }}</div>
                    <div class="schedule-meta">
                        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $item['time'] }} completed</span>
                    </div>
                    <div class="schedule-meta">
                        <span><i class="fa-regular fa-user" aria-hidden="true"></i> {{ $item['client'] }}</span>
                        <span style="margin:0 6px;">·</span>
                        <span><i class="fa-regular fa-location-dot" aria-hidden="true"></i> {{ $item['location'] }}</span>
                    </div>
                </div>
                <span class="status-badge status-done">Completed</span>
            </div>
        @empty
            <div class="empty-state" id="past-empty">
                <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                <h3>No past jobs</h3>
                <p>Completed jobs will appear here.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection

@push('styles')
<style>
.schedule-row {
    display: flex; align-items: center; gap: 18px;
    padding: 18px 22px; border-bottom: 1px solid var(--g1); transition: background .15s;
}
.schedule-date-box {
    width: 56px; height: 56px; border-radius: 12px; background: var(--b0); color: var(--b6);
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    flex-shrink: 0; font-weight: 700; line-height: 1.2;
}
.schedule-month { font-size: .65rem; text-transform: uppercase; color: var(--b4); }
.schedule-day { font-size: 1.2rem; color: var(--b9); }
.schedule-info { flex: 1; min-width: 0; }
.schedule-service { font-size: .95rem; font-weight: 600; color: var(--b9); }
.schedule-meta { font-size: .82rem; color: var(--g4); margin-top: 3px; }
.schedule-meta i { margin-right: 4px; }

.avail-days { padding: 0 22px 12px; }
.avail-row {
    display: flex; align-items: center; gap: 14px;
    padding: 10px 0; border-bottom: 1px solid var(--g0);
}
.avail-row:last-child { border-bottom: none; }
.avail-off { opacity: .6; }
.avail-day { width: 52px; font-weight: 600; font-size: .88rem; color: var(--b9); }
.avail-time { flex: 1; font-size: .85rem; color: var(--b7); }
.avail-badge {
    font-size: .75rem; font-weight: 500; padding: 2px 10px;
    border-radius: 10px; white-space: nowrap;
}
.avail-badge-on { background: #dcfce7; color: #166534; }
.avail-badge-off { background: var(--g0); color: var(--g5); }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pills = document.querySelectorAll('#schedule-filters .filter-pill');
    const upcomingList = document.getElementById('upcoming-list');
    const pastList = document.getElementById('past-list');
    const title = document.getElementById('schedule-title');

    pills.forEach(pill => {
        pill.addEventListener('click', function () {
            pills.forEach(p => p.classList.remove('active'));
            this.classList.add('active');

            if (this.dataset.filter === 'past') {
                upcomingList.style.display = 'none';
                pastList.style.display = '';
                title.textContent = 'Past Jobs';
            } else {
                upcomingList.style.display = '';
                pastList.style.display = 'none';
                title.textContent = 'Upcoming Jobs';
            }
        });
    });
});
</script>
@endpush
