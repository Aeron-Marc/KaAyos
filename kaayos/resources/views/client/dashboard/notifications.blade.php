@extends('layouts.client')

@section('title', 'Notifications')
@section('page_title', 'Dashboard')

@section('tabs')
    <a href="{{ route('client.dashboard') }}"
       class="subtab {{ request()->routeIs('client.dashboard') && !request()->routeIs('client.dashboard.notifications') ? 'active' : '' }}">
        Overview
    </a>
    <a href="{{ route('client.dashboard.notifications') }}"
       class="subtab {{ request()->routeIs('client.dashboard.notifications') ? 'active' : '' }}">
        Notifications
        @if(collect($notifications)->where('unread', true)->count())
            <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:var(--b4);color:#fff;border-radius:50%;font-size:.65rem;margin-left:4px;">
                {{ collect($notifications)->where('unread', true)->count() }}
            </span>
        @endif
    </a>
@endsection

@section('skeleton')
    <div class="skeleton skeleton-title" style="width:160px;margin-bottom:20px;"></div>
    <div class="sp-tabs">
        <div class="skeleton" style="height:32px;width:60px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:80px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:70px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:60px;border-radius:99px;"></div>
        <div class="skeleton" style="height:32px;width:70px;border-radius:99px;"></div>
    </div>
    <div class="sp-panel">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div class="skeleton" style="width:40px;height:40px;border-radius:10px;flex-shrink:0;"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:70%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:40%;"></div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div class="skeleton" style="width:40px;height:40px;border-radius:10px;flex-shrink:0;"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:60%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:50%;"></div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div class="skeleton" style="width:40px;height:40px;border-radius:10px;flex-shrink:0;"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:80%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:30%;"></div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="skeleton" style="width:40px;height:40px;border-radius:10px;flex-shrink:0;"></div>
            <div style="flex:1;">
                <div class="skeleton skeleton-text" style="width:55%;"></div>
                <div class="skeleton skeleton-text-sm" style="width:45%;"></div>
            </div>
        </div>
    </div>
@endsection

@section('content')

<div class="filter-pills">
    <button type="button" class="filter-pill active">All</button>
    <button type="button" class="filter-pill">Bookings</button>
    <button type="button" class="filter-pill">Messages</button>
    <button type="button" class="filter-pill">Reviews</button>
    <button type="button" class="filter-pill">System</button>
</div>

<div class="card-panel">
    <div class="card-panel-header">
        <div>
            <div class="eyebrow">Inbox</div>
            <h2 class="section-title">Notifications</h2>
        </div>
        <button type="button" class="btn btn-ghost" style="font-size:.82rem;">
            <i class="fa-solid fa-check-double" aria-hidden="true"></i> Mark all read
        </button>
    </div>

    <div class="notif-list">
        @forelse($notifications as $notif)
            <div class="notif-item {{ $notif['unread'] ? 'unread' : '' }}">
                <div class="notif-icon {{ $notif['type'] }}">
                    @switch($notif['type'])
                        @case('booking')
                            <i class="fa-solid fa-calendar-check" aria-hidden="true"></i>
                            @break
                        @case('message')
                            <i class="fa-solid fa-comment" aria-hidden="true"></i>
                            @break
                        @case('review')
                            <i class="fa-solid fa-star" aria-hidden="true"></i>
                            @break
                        @default
                            <i class="fa-solid fa-bell" aria-hidden="true"></i>
                    @endswitch
                </div>
                <div class="notif-body">
                    <div class="notif-title">{{ $notif['title'] }}</div>
                    <div class="notif-desc">{{ $notif['desc'] }}</div>
                </div>
                <span class="notif-time">{{ $notif['time'] }}</span>
                @if($notif['unread'])
                    <span class="notif-unread-dot" aria-label="Unread"></span>
                @endif
            </div>
        @empty
            <div class="empty-state">
                <i class="fa-regular fa-bell" aria-hidden="true"></i>
                <h3>No notifications yet</h3>
                <p>We'll let you know when something needs your attention.</p>
            </div>
        @endforelse
    </div>
</div>

@endsection
