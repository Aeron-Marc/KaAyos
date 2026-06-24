@extends('layouts.worker')

@section('title', 'My Schedule')
@section('page_title', 'My Schedule')

@section('topbar_actions')
    <a href="{{ route('worker.jobs') }}" class="btn btn-outline">
        <i class="fa-solid fa-clipboard-list" aria-hidden="true"></i> Job Requests
    </a>
@endsection

@section('content')

<div class="filter-pills">
    <button type="button" class="filter-pill active">Upcoming</button>
    <button type="button" class="filter-pill">Past Jobs</button>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Your Calendar</div>
            <h2 class="section-title">Upcoming Jobs</h2>
        </div>
    </div>

    @forelse($schedule as $item)
        <div style="display:flex;align-items:center;gap:18px;padding:18px 22px;border-bottom:1px solid var(--g1);transition:background .15s;">
            <div style="width:56px;height:56px;border-radius:12px;background:var(--b0);color:var(--b6);display:flex;flex-direction:column;align-items:center;justify-content:center;flex-shrink:0;font-weight:700;line-height:1.2;">
                <span style="font-size:.65rem;text-transform:uppercase;color:var(--b4);">{{ \Carbon\Carbon::parse($item['date'])->format('M') }}</span>
                <span style="font-size:1.2rem;color:var(--b9);">{{ \Carbon\Carbon::parse($item['date'])->format('d') }}</span>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.95rem;font-weight:600;color:var(--b9);">{{ $item['service'] }}</div>
                <div style="font-size:.82rem;color:var(--g4);margin-top:3px;">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $item['time'] }}
                </div>
                <div style="font-size:.82rem;color:var(--g4);">
                    <i class="fa-regular fa-user" aria-hidden="true"></i> {{ $item['client'] }}
                    <span style="margin:0 6px;">·</span>
                    <i class="fa-regular fa-location-dot" aria-hidden="true"></i> {{ $item['location'] }}
                </div>
            </div>
            <span class="status-badge {{ $item['status'] === 'Confirmed' ? 'status-active' : 'status-pending' }}">{{ $item['status'] }}</span>
        </div>
    @empty
        <div class="empty-state">
            <i class="fa-regular fa-calendar" aria-hidden="true"></i>
            <h3>No upcoming jobs</h3>
            <p>Your schedule will populate once you accept job requests from clients.</p>
        </div>
    @endforelse
</div>

@endsection
