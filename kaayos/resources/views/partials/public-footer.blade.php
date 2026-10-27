{{--
  Shared public site footer (landing page style).
  Used by layouts/public.blade.php on every page that shows the landing footer.
--}}
@php
    $onLanding = request()->routeIs('home');
    $anchor = fn (string $id) => $onLanding ? '#'.$id : url('/').'/#'.$id;
@endphp

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-grid">
    <div class="f-brand">
      <a href="{{ url('/') }}" class="brand">
        <div class="flogo"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos" width="40" height="40" loading="lazy"></div>
        <span>KaAyos</span>
      </a>
      <p>{{ __('landing.footer_desc') }}</p>
      <div class="partner-logos">
        <img src="{{ asset('images/peso-logo.jpg') }}" alt="PESO Tuy" width="48" height="48">
      </div>
    </div>
    <div>
      <div class="f-title">{{ __('landing.footer_services') }}</div>
      <ul class="f-links">
        <li><a href="{{ url('/') }}?category=plumbing"><i class="fa-solid fa-wrench fa-fw" aria-hidden="true"></i> Plumbing</a></li>
        <li><a href="{{ url('/') }}?category=electrical"><i class="fa-solid fa-bolt fa-fw" aria-hidden="true"></i> Electrical</a></li>
        <li><a href="{{ url('/') }}?category=carpentry"><i class="fa-solid fa-screwdriver-wrench fa-fw" aria-hidden="true"></i> Carpentry</a></li>
        <li><a href="{{ url('/') }}?category=cleaning"><i class="fa-solid fa-broom fa-fw" aria-hidden="true"></i> Cleaning</a></li>
        <li><a href="{{ $anchor('services') }}"><i class="fa-solid fa-list fa-fw" aria-hidden="true"></i> {{ __('landing.footer_view_all') }}</a></li>
      </ul>
    </div>
    <div>
      <div class="f-title">{{ __('landing.footer_company') }}</div>
      <ul class="f-links">
        <li><a href="{{ url('/about') }}"><i class="fa-solid fa-circle-info fa-fw" aria-hidden="true"></i> {{ __('landing.footer_about') }}</a></li>
        <li><a href="{{ $anchor('how-it-works') }}"><i class="fa-solid fa-list-ol fa-fw" aria-hidden="true"></i> {{ __('landing.how_it_works') }}</a></li>
        <li><a href="{{ url('/register') }}?role=worker"><i class="fa-solid fa-hammer fa-fw" aria-hidden="true"></i> {{ __('landing.join_worker') }}</a></li>
        <li><a href="{{ $anchor('faq') }}"><i class="fa-solid fa-circle-question fa-fw" aria-hidden="true"></i> {{ __('landing.faq') }}</a></li>
        <li><a href="{{ url('/contact') }}"><i class="fa-solid fa-envelope fa-fw" aria-hidden="true"></i> {{ __('landing.footer_contact') }}</a></li>
        <li><a href="{{ url('/privacy') }}"><i class="fa-solid fa-shield fa-fw" aria-hidden="true"></i> {{ __('landing.footer_privacy') }}</a></li>
        <li><a href="{{ url('/terms') }}"><i class="fa-solid fa-file-lines fa-fw" aria-hidden="true"></i> {{ __('landing.footer_terms') }}</a></li>
        <li><a href="{{ url('/safety') }}"><i class="fa-solid fa-shield-halved fa-fw" aria-hidden="true"></i> {{ __('landing.footer_safety') }}</a></li>
      </ul>
    </div>
  </div>
  <button class="back-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></button>
  <div class="f-bottom">
    <p>&copy; {{ date('Y') }} KaAyos &mdash; Capstone project by Salanguit, Formentos &amp; Briones &middot; Batangas State University ARASOF &ndash; Nasugbu Campus.</p>
    <div class="socials">
      <a href="https://www.facebook.com/profile.php?id=61586038325079" class="soc" title="Facebook" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
      <a href="mailto:hello@kaayos.tech" class="soc" title="Email" aria-label="Email"><i class="fa-solid fa-envelope"></i></a>
    </div>
  </div>
</footer>
