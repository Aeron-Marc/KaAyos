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

@push('styles')
<style>
.filter-pills{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;}
.filter-pill{padding:6px 14px;border:1px solid var(--g1);border-radius:999px;background:var(--white);color:var(--g7);font-size:.82rem;cursor:pointer;transition:all .2s;}
.filter-pill:hover{border-color:var(--b6);color:var(--b6);}
.filter-pill.active{border-color:var(--b4);background:var(--b4);color:#fff;}
.card-panel{background:var(--white);border:1px solid var(--g1);border-radius:var(--radius);overflow:hidden;}
.card-panel-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--g1);}
.card-panel-header .eyebrow{font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:var(--g5);margin-bottom:2px;}
.card-panel-header h2.section-title{font-size:1.05rem;color:var(--b9);margin:0;font-weight:700;}
.notif-list{display:flex;flex-direction:column;}
.notif-item{display:flex;align-items:center;gap:14px;padding:12px 16px;border-bottom:1px solid var(--g1);text-decoration:none;color:inherit;transition:background .15s;}
.notif-item:last-child{border-bottom:none;}
.notif-item.unread{background:#f4f9ff;}
.notif-item:hover{background:var(--off);}
.notif-icon{flex-shrink:0;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#fff;}
.notif-icon.booking{background:var(--b4);}
.notif-icon.message{background:var(--g6);}
.notif-icon.review{background:var(--orange);}
.notif-icon.system{background:var(--b9);}
.notif-icon .fa-bell{color:var(--g5);}
.notif-body{flex:1;min-width:0;}
.notif-title{font-size:.9rem;color:var(--g9);font-weight:700;display:block;}
.notif-desc{font-size:.8rem;color:var(--g6);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.notif-time{font-size:.72rem;color:var(--g5);white-space:nowrap;margin-left:auto;}
.notif-unread-dot{flex-shrink:0;width:8px;height:8px;background:var(--b4);border-radius:50%;}
.empty-state{display:flex;flex-direction:column;align-items:center;gap:10px;padding:40px 20px;color:var(--g6);}
.empty-state i{font-size:2.5rem;color:var(--g3);}
.empty-state h3{margin:0;font-size:1rem;color:var(--g9);font-weight:700;}
.empty-state p{margin:4px 0 0;font-size:.85rem;}
@media(max-width:640px){.card-panel-header{flex-direction:column;align-items:flex-start;gap:8px;}.notif-time{font-size:.7rem;}}
</style>
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
            <a href="{{ $notif['url'] ?? '#' }}" class="notif-item {{ $notif['unread'] ? 'unread' : '' }}">
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
            </a>
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
