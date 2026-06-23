<!DOCTYPE html>
<html lang="{{ auth()->check() && auth()->user()->language === 'Filipino' ? 'fil' : 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — KaAyos</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @vite(['resources/css/client.css'])
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
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>

            <a href="{{ route('client.workers') }}"
               class="nav-item {{ request()->routeIs('client.workers*') ? 'active' : '' }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0-3-3.87"/></svg>
                Find Workers
            </a>

            <a href="{{ route('client.bookings') }}"
               class="nav-item {{ request()->routeIs('client.bookings*') ? 'active' : '' }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>
                Bookings
            </a>

            <a href="{{ route('client.messages') }}"
               class="nav-item {{ request()->routeIs('client.messages*') ? 'active' : '' }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Messages
            </a>

            <a href="{{ route('client.reviews') }}"
               class="nav-item {{ request()->routeIs('client.reviews*') ? 'active' : '' }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Reviews
            </a>

            <a href="{{ route('client.account.profile') }}"
               class="nav-item {{ request()->routeIs('client.account*') ? 'active' : '' }}">
                <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                Account
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
                <span class="profile-role">Homeowner</span>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-logout">
                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                Log out
            </button>
        </form>

    </aside>

    <div class="main">

        <header class="topbar">
            <h1 class="page-title">@yield('page_title', 'Dashboard')</h1>

            <div class="topbar-actions">
                <a href="{{ route('client.dashboard.notifications') }}" class="icon-btn" aria-label="Notifications">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
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
</body>
</html>
