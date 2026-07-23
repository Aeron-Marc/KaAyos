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
                <img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos">
            </div>
            <span class="logo-text">KaAyos</span>
        </a>

        <nav class="sidebar-nav">

            <a href="{{ route('worker.dashboard') }}"
               class="nav-item {{ request()->routeIs('worker.dashboard*') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high nav-icon" aria-hidden="true"></i>
                {{ __('nav.dashboard') }}
            </a>

            <a href="{{ route('worker.schedule') }}"
               class="nav-item {{ request()->routeIs('worker.schedule*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days nav-icon" aria-hidden="true"></i>
                {{ __('nav.my_schedule') }}
            </a>

            <a href="{{ route('worker.messages') }}"
               class="nav-item {{ request()->routeIs('worker.messages*') ? 'active' : '' }}">
                <i class="fa-solid fa-comment-dots nav-icon" aria-hidden="true"></i>
                {{ __('nav.messages') }}
            </a>

            <a href="{{ route('worker.earnings') }}"
               class="nav-item {{ request()->routeIs('worker.earnings*') ? 'active' : '' }}">
                <i class="fa-solid fa-coins nav-icon" aria-hidden="true"></i>
                {{ __('nav.earnings') }}
            </a>

            <a href="{{ route('worker.profile') }}"
               class="nav-item {{ request()->routeIs('worker.profile*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-gear nav-icon" aria-hidden="true"></i>
                {{ __('nav.my_profile') }}
            </a>

            <a href="{{ route('worker.documents') }}"
               class="nav-item {{ request()->routeIs('worker.documents*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-upload nav-icon" aria-hidden="true"></i>
                {{ __('nav.documents') }}
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
                <span class="profile-role">{{ __('role.trabahador') }}</span>
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
                <a href="{{ route('worker.dashboard.notifications') }}" class="icon-btn" aria-label="{{ __('action.notifications') }}">
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
                .listen('BookingCreated', function (e) {
                    var badge = document.querySelector('.badge-dot');
                    if (badge) badge.style.display = '';
                    var toast = document.createElement('div');
                    toast.className = 'toast-notification';
                    toast.innerHTML = '<strong>New Booking!</strong><br>' + e.client_name + ' booked you for ' + e.service;
                    document.body.appendChild(toast);
                    setTimeout(function () { toast.remove(); }, 5000);
                });
        }
    }, 200);
});
</script>
</body>
</html>
