{{--
  Shared public site header (landing page style).
  Used by layouts/public.blade.php on every page that shows the landing header.
  Anchors resolve to the current page when already on the landing page so the
  browser scrolls instead of reloading.
--}}
@php
    $onLanding = request()->routeIs('home');
    $anchor = fn (string $id) => $onLanding ? '#'.$id : url('/').'/#'.$id;
@endphp

<!-- MOBILE OVERLAY -->
<div id="mobileOverlay" class="mobile-overlay" onclick="closeMobileMenu()"></div>

<!-- MOBILE DRAWER -->
<aside id="mobileDrawer" class="mobile-drawer" role="dialog" aria-label="Navigation menu">
  <button class="drawer-close" onclick="closeMobileMenu()" aria-label="Close menu"><i class="fa-solid fa-xmark"></i></button>
  <a href="{{ $anchor('services') }}" onclick="closeMobileMenu()"><i class="fa-solid fa-briefcase"></i> {{ __('landing.services') }}</a>
  <a href="{{ $anchor('how-it-works') }}" onclick="closeMobileMenu()"><i class="fa-solid fa-list-ol"></i> {{ __('landing.how_it_works') }}</a>
  <a href="{{ $anchor('join') }}" onclick="closeMobileMenu()"><i class="fa-solid fa-hammer"></i> {{ __('landing.join_worker') }}</a>
  <a href="{{ $anchor('faq') }}" onclick="closeMobileMenu()"><i class="fa-solid fa-circle-question"></i> {{ __('landing.faq') }}</a>
  <div class="drawer-cta">
    <a href="{{ url('/login') }}" class="btn-ghost"><i class="fa-regular fa-user"></i> {{ __('landing.log_in') }}</a>
    <a href="{{ url('/register') }}" class="btn-amber"><i class="fa-solid fa-arrow-right-to-bracket"></i> {{ __('landing.sign_up') }}</a>
  </div>
</aside>

<!-- NAV -->
<nav class="nav">
  <a href="{{ url('/') }}" class="nav-logo">
    <div class="logo-box"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos Logo" width="36" height="36"></div>
    <span>KaAyos</span>
  </a>
  <ul class="nav-links">
    <li><a href="{{ $anchor('services') }}">{{ __('landing.services') }}</a></li>
    <li><a href="{{ $anchor('how-it-works') }}">{{ __('landing.how_it_works') }}</a></li>
    <li><a href="{{ $anchor('join') }}">{{ __('landing.join_worker') }}</a></li>
    <li><a href="{{ $anchor('faq') }}">{{ __('landing.faq') }}</a></li>
  </ul>
  <div class="nav-cta">
    <x-language-switcher />
    <a href="{{ url('/login') }}" class="btn btn-ghost"><i class="fa-regular fa-user" aria-hidden="true"></i> {{ __('landing.log_in') }}</a>
    <a href="{{ url('/register') }}" class="btn btn-amber"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> {{ __('landing.sign_up') }}</a>
    <button class="nav-toggle" id="navToggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>
