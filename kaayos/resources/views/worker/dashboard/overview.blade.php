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

@section('content')

@php
    $firstName = explode(' ', auth()->user()->name ?? 'there')[0];
    $hour = (int) now()->format('H');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

<div class="welcome-banner">
    <p class="welcome-location"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> Tuy, Batangas</p>
    <h2>{{ $greeting }}, {{ $firstName }} 👋</h2>
    <p>Manage your jobs, track your earnings, and keep your clients happy — all in one place.</p>
</div>

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

@endsection
