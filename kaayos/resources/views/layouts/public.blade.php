{{--
  Public site layout — the single source of truth for the landing header and
  footer. Any page that previously copied the landing nav/footer should extend
  this layout instead, so header, footer and their CSS can never drift again.

  Sections:
    title, meta_description, og_description — optional SEO overrides
    loader   — optional page-loader block (landing page only)
    content  — page body
  Stacks: styles, scripts
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="@yield('meta_description', 'KaAyos – Find trusted, PESO-accredited skilled workers (trabahador) in Tuy, Batangas. AI-matched plumbing, electrical, carpentry & cleaning services.')">
<meta property="og:title" content="@yield('title', 'KaAyos – Trusted Home Services')">
<meta property="og:description" content="@yield('og_description', 'Find verified skilled workers near you. AI-matched, PESO-accredited, and community-rated.')">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<title>@yield('title', 'KaAyos – Trusted Home Services')</title>
<link rel="icon" href="{{ asset('images/KaAyos_logo.jpeg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@vite(['resources/css/landing.css'])
@stack('styles')
</head>
<body>

@hasSection('loader')
  @yield('loader')
@endif

@include('partials.public-nav')

@yield('content')

@include('partials.public-footer')

@stack('scripts')

<script>
function toggleMobileMenu() {
  var open = document.getElementById('mobileDrawer').classList.toggle('open');
  document.getElementById('mobileOverlay').classList.toggle('open');
  document.getElementById('navToggle').classList.toggle('open');
  document.body.style.overflow = open ? 'hidden' : '';
}

function closeMobileMenu() {
  var drawer = document.getElementById('mobileDrawer');
  if (!drawer) return;
  drawer.classList.remove('open');
  document.getElementById('mobileOverlay').classList.remove('open');
  document.getElementById('navToggle').classList.remove('open');
  document.body.style.overflow = '';
}

document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeMobileMenu();
});

/* NAV SCROLL */
(function () {
  var nav = document.querySelector('.nav');
  if (!nav) return;
  var onScroll = function () {
    if (window.scrollY > 40) { nav.classList.add('scrolled'); }
    else { nav.classList.remove('scrolled'); }
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();
</script>
</body>
</html>
