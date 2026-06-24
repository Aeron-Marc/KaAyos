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

            <a href="{{ route('worker.dashboard') }}"
               class="nav-item {{ request()->routeIs('worker.dashboard*') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high nav-icon" aria-hidden="true"></i>
                Dashboard
            </a>

            <a href="{{ route('worker.jobs') }}"
               class="nav-item {{ request()->routeIs('worker.jobs*') ? 'active' : '' }}">
                <i class="fa-solid fa-clipboard-list nav-icon" aria-hidden="true"></i>
                Job Requests
            </a>

            <a href="{{ route('worker.schedule') }}"
               class="nav-item {{ request()->routeIs('worker.schedule*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-days nav-icon" aria-hidden="true"></i>
                My Schedule
            </a>

            <a href="{{ route('worker.messages') }}"
               class="nav-item {{ request()->routeIs('worker.messages*') ? 'active' : '' }}">
                <i class="fa-solid fa-comment-dots nav-icon" aria-hidden="true"></i>
                Messages
            </a>

            <a href="{{ route('worker.earnings') }}"
               class="nav-item {{ request()->routeIs('worker.earnings*') ? 'active' : '' }}">
                <i class="fa-solid fa-coins nav-icon" aria-hidden="true"></i>
                Earnings
            </a>

            <a href="{{ route('worker.profile') }}"
               class="nav-item {{ request()->routeIs('worker.profile*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-gear nav-icon" aria-hidden="true"></i>
                My Profile
            </a>

            <a href="{{ route('worker.documents') }}"
               class="nav-item {{ request()->routeIs('worker.documents*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-upload nav-icon" aria-hidden="true"></i>
                Documents
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
                <span class="profile-role">Trabahador</span>
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
                <a href="{{ route('worker.dashboard.notifications') }}" class="icon-btn" aria-label="Notifications">
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
</body>
</html>
