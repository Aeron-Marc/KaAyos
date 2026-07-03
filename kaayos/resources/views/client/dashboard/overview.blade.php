@extends('layouts.client')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('tabs')
    <a href="{{ route('client.dashboard') }}"
       class="subtab {{ request()->routeIs('client.dashboard') && !request()->routeIs('client.dashboard.notifications') ? 'active' : '' }}">
        Overview
    </a>
    <a href="{{ route('client.dashboard.notifications') }}"
       class="subtab {{ request()->routeIs('client.dashboard.notifications') ? 'active' : '' }}">
        Notifications
    </a>
@endsection

@section('topbar_actions')
    <a href="{{ route('client.workers') }}" class="btn btn-solid">
        <i class="fa-solid fa-plus" aria-hidden="true"></i> New Booking
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
    <p>Find a trusted trabahador, manage your bookings, and keep your home in kaayos — all in one place.</p>
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

<form action="{{ route('client.workers') }}" class="search-row">
    <div class="search-field">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="text" name="q" placeholder="What service do you need? e.g. leaking pipe, broken circuit…" aria-label="Search services">
    </div>
    <button type="submit" class="btn btn-solid">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Find Workers
    </button>
</form>

<div class="section-header">
    <h2 class="section-title">Browse by Category</h2>
</div>

<div class="category-scroll">
    @foreach($categories as $cat)
        <a href="{{ route('client.workers', ['category' => $cat['id']]) }}" class="category-chip">
            <div class="cat-icon"><i class="fa-solid {{ $cat['icon'] }}" aria-hidden="true"></i></div>
            <span class="cat-name">{{ $cat['name'] }}</span>
        </a>
    @endforeach
</div>

<div class="section-header">
    <h2 class="section-title">Recommended for You</h2>
    <a href="{{ route('client.workers') }}" class="link-action">View all workers</a>
</div>

<div class="workers-grid">
    @include('client.partials.worker-cards', ['workers' => $workers])
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Recent Activity</div>
            <h2 class="section-title">Your Bookings</h2>
        </div>
        <a href="{{ route('client.bookings') }}" class="link-action">Manage all</a>
    </div>
    <table class="bookings-table">
        <thead>
            <tr>
                <th>Worker</th>
                <th>Service</th>
                <th>Date</th>
                <th>Status</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bookings as $booking)
                <tr>
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
                            $label = [
                                'new' => 'Pending', 'accepted' => 'Active', 'en_route' => 'En Route',
                                'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled',
                            ][$booking['raw_status']] ?? ucfirst($booking['raw_status']);
                        @endphp
                        <span class="status-badge {{ $cls }}">{{ $label }}</span>
                    </td>
                    <td>₱{{ number_format($booking['price']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div style="text-align:center;padding:24px;color:var(--g4);font-size:.85rem;">
                            No bookings yet. <a href="{{ route('client.workers') }}" style="color:var(--b6);">Find a worker</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
