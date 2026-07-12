<!DOCTYPE html>
<html lang="{{ auth()->check() && auth()->user()->language === 'Filipino' ? 'fil' : 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('page_title.default')) — KaAyos</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/client.css', 'resources/js/echo.js'])
    @stack('styles')
</head>
<body>

<div class="shell">

    <aside class="sidebar">

        <a href="{{ route('home') }}" class="sidebar-logo">
            <div class="logo-mark">
                <i class="fa-solid fa-house-chimney" aria-hidden="true"></i>
            </div>
            <span class="logo-text">KaAyos</span>
        </a>

        <nav class="sidebar-nav">

            <a href="{{ route('client.dashboard') }}"
               class="nav-item {{ request()->routeIs('client.dashboard*') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high nav-icon" aria-hidden="true"></i>
                {{ __('nav.dashboard') }}
            </a>

            <a href="{{ route('client.workers') }}"
               class="nav-item {{ request()->routeIs('client.workers*') ? 'active' : '' }}">
                <i class="fa-solid fa-users nav-icon" aria-hidden="true"></i>
                {{ __('nav.find_workers') }}
            </a>

            <a href="{{ route('client.bookings') }}"
               class="nav-item {{ request()->routeIs('client.bookings*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check nav-icon" aria-hidden="true"></i>
                {{ __('nav.bookings') }}
            </a>

            <a href="{{ route('client.messages') }}"
               class="nav-item {{ request()->routeIs('client.messages*') ? 'active' : '' }}">
                <i class="fa-solid fa-comment-dots nav-icon" aria-hidden="true"></i>
                {{ __('nav.messages') }}
            </a>

            <a href="{{ route('client.reviews') }}"
               class="nav-item {{ request()->routeIs('client.reviews*') ? 'active' : '' }}">
                <i class="fa-solid fa-star nav-icon" aria-hidden="true"></i>
                {{ __('nav.reviews') }}
            </a>

            <a href="{{ route('client.account.profile') }}"
               class="nav-item {{ request()->routeIs('client.account*') ? 'active' : '' }}">
                <i class="fa-solid fa-user nav-icon" aria-hidden="true"></i>
                {{ __('nav.account') }}
            </a>

        </nav>

        <div class="sidebar-spacer"></div>

        <div class="sidebar-profile">
            <div class="profile-avatar">
                @if(auth()->user()->avatar)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url(auth()->user()->avatar) }}" alt="" class="sidebar-avatar-img" />
                @else
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                @endif
            </div>
            <div class="profile-info">
                <p class="profile-name">{{ auth()->user()->name ?? 'User' }}</p>
                <span class="profile-role">{{ __('role.client') }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout">
                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                {{ __('action.logout') }}
            </button>
        </form>

    </aside>

    <div class="main">

        <header class="topbar">
            <h1 class="page-title">@yield('page_title', __('page_title.default'))</h1>

            <div class="topbar-actions">
                <a href="{{ route('client.dashboard.notifications') }}" class="icon-btn" aria-label="{{ __('action.notifications') }}">
                    <i class="fa-solid fa-bell" style="font-size:1rem;" aria-hidden="true"></i>
                    @if(collect($notifications ?? [])->where('unread', true)->count() > 0)
                        <span class="badge-dot"></span>
                    @endif
                </a>

                @yield('topbar_actions')
            </div>
        </header>

        @hasSection('tabs')
        <nav class="subtab-bar" role="tablist">
            @yield('tabs')
        </nav>
        @endif

        <main class="content" id="main-content">
            @yield('content')
        </main>

    </div>

</div>

@stack('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var checkEcho = setInterval(function () {
        if (window.Echo) {
            clearInterval(checkEcho);
            var userId = {{ auth()->id() }};
            window.Echo.private('user.' + userId)
                .listen('BookingStatusUpdated', function (e) {
                    var badge = document.querySelector('.badge-dot');
                    if (badge) badge.style.display = '';
                });
        }
    }, 200);
});
</script>
</body>
</html>
