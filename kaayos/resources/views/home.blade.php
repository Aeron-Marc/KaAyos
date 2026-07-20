<!DOCTYPE html>
<html lang="fil">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="KaAyos – Find trusted, PESO-accredited skilled workers (trabahador) in Tuy, Batangas. AI-matched plumbing, electrical, carpentry &amp; cleaning services.">
<meta property="og:title" content="KaAyos – Trusted Home Services in Tuy, Batangas">
<meta property="og:description" content="Find verified skilled workers near you. AI-matched, PESO-accredited, and community-rated.">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<title>KaAyos – Trusted Home Services</title>
<link rel="icon" href="../images/KaAyos_logo.jpeg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@vite(['resources/css/landing.css'])
</head>
<body>

<!-- MOBILE OVERLAY -->
<div id="mobileOverlay" class="mobile-overlay" onclick="closeMobileMenu()"></div>

<!-- MOBILE DRAWER -->
<aside id="mobileDrawer" class="mobile-drawer" role="dialog" aria-label="Navigation menu">
  <button class="drawer-close" onclick="closeMobileMenu()" aria-label="Close menu"><i class="fa-solid fa-xmark"></i></button>
  <a href="#services" onclick="closeMobileMenu()"><i class="fa-solid fa-briefcase"></i> Services</a>
  <a href="#how-it-works" onclick="closeMobileMenu()"><i class="fa-solid fa-list-ol"></i> How It Works</a>
  <a href="#join" onclick="closeMobileMenu()"><i class="fa-solid fa-hammer"></i> Join as Worker</a>
  <a href="#faq" onclick="closeMobileMenu()"><i class="fa-solid fa-circle-question"></i> FAQ</a>
  <div class="drawer-cta">
    <a href="/login" class="btn-ghost"><i class="fa-regular fa-user"></i> Log In</a>
    <a href="/register" class="btn-amber"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign Up Free</a>
  </div>
</aside>

<!-- NAV -->
<nav class="nav">
  <div class="nav-logo">
    <div class="logo-box"><img src="../images/logo-gs-removebg-preview.png" alt="KaAyos Logo"></div>
    <span>KaAyos</span>
  </div>
  <ul class="nav-links">
    <li><a href="#services">Services</a></li>
    <li><a href="#how-it-works">How It Works</a></li>
    <li><a href="#join">Join as Worker</a></li>
    <li><a href="#faq">FAQ</a></li>
  </ul>
  <div class="nav-cta">
    <a href="/login" class="btn btn-ghost"><i class="fa-regular fa-user" aria-hidden="true"></i> Log In</a>
    <a href="/register" class="btn btn-amber"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign Up Free</a>
    <button class="nav-toggle" id="navToggle" onclick="toggleMobileMenu()" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-icons-floating">
    <div class="hero-icon-f"><i class="fa-solid fa-wrench"></i></div>
    <div class="hero-icon-f"><i class="fa-solid fa-bolt"></i></div>
    <div class="hero-icon-f"><i class="fa-solid fa-paint-roller"></i></div>
    <div class="hero-icon-f"><i class="fa-solid fa-broom"></i></div>
    <div class="hero-icon-f"><i class="fa-solid fa-screwdriver-wrench"></i></div>
    <div class="hero-icon-f"><i class="fa-solid fa-hammer"></i></div>
  </div>
  <div class="peso-stamp">
    <img src="{{ asset('images/peso-logo.jpg') }}" alt="PESO Tuy Accredited">
  </div>
  <div class="hero-tag"><div class="dot"></div><span>In Partnership with PESO Tuy, Batangas</span></div>
  <h1>Find a Trusted <em>Trabahador</em> in Minutes</h1>
  <p class="hero-sub">KaAyos connects homeowners with verified skilled workers matched by skill, rating, and location. Every worker is PESO-accredited and verified by the Public Employment Service Office.</p>
  <div class="hero-actions">
    <a href="/register" class="btn btn-primary btn-lg"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Hire a Worker Now</a>
    <a href="#join" class="btn-outline"><i class="fa-solid fa-hammer" aria-hidden="true"></i> Join as Trabahador</a>
  </div>
</section>

<!-- SEARCH -->
<div class="search-section">
  <div class="search-label">What do you need fixed?</div>
  <div class="search-bar">
    <div class="input-wrap">
      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      <input type="text" id="searchQuery" placeholder="e.g. leaking pipe, broken circuit, painting…" aria-label="Service type">
    </div>
    <div class="input-wrap loc-input">
      <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
      <input type="text" id="searchLocation" placeholder="Your barangay" aria-label="Location">
    </div>
    <button class="btn btn-primary btn-lg" onclick="doSearch()"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Find Workers</button>
  </div>
  <div class="search-tags">
    <button class="search-tag" onclick="searchTag(this)">Leaking Pipe</button>
    <button class="search-tag" onclick="searchTag(this)">Cleaning</button>
    <button class="search-tag" onclick="searchTag(this)">Painting</button>
    <button class="search-tag" onclick="searchTag(this)">Electrical Repair</button>
    <button class="search-tag" onclick="searchTag(this)">Aircon Cleaning</button>
  </div>
</div>

<div class="section-divider" style="margin-top:48px"></div>

<!-- SERVICES & WORKERS -->
<section class="section" id="services">
  <div class="sec-header fade-up">
    <div class="eyebrow">Services & Workers</div>
    <h2 class="sec-title">Every Trade, One Platform</h2>
    <p class="sec-sub">Browse verified workers across all major home service categories. Click a category to filter.</p>
  </div>

  <div class="cat-pills fade-up">
    <button class="cat-pill {{ !$category ? 'active' : '' }}" data-category="">All</button>
    @foreach($categories as $cat)
      <button class="cat-pill {{ $category === $cat->slug ? 'active' : '' }}" data-category="{{ $cat->slug }}"><i class="fa-solid {{ $cat->icon ?: 'fa-wrench' }}"></i> {{ $cat->name }}</button>
    @endforeach
  </div>

  @if(!empty($workers))
    <div class="worker-grid fade-up" id="workerGrid">
      @foreach($workers as $w)
        <a href="{{ route('workers.public.show', $w['id']) }}" class="worker-card" data-category="{{ strtolower($w['category']) }}">
          <div class="w-card-top">
            @if($w['avatar'])
              <img src="{{ $w['avatar'] }}" alt="{{ $w['name'] }}" class="w-avatar" loading="lazy">
            @else
              <div class="w-avatar w-initials">{{ $w['initials'] }}</div>
            @endif
            <div class="w-meta">
              <div class="w-name-row">
                <div>
                  <div class="w-name">{{ $w['name'] }}</div>
                  <div class="w-trade">{{ $w['category'] }} <span class="peso-badge"><i class="fa-solid fa-certificate"></i> PESO</span></div>
                </div>
                <div class="w-rating">
                  <i class="fa-solid fa-star" aria-hidden="true"></i>
                  {{ number_format($w['rating'], 1) }}
                </div>
              </div>
              <div class="w-details-row">
                <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $w['distance'] }}</span>
                @if($w['reviews'] > 0)
                  <span><i class="fa-regular fa-comment"></i> {{ $w['reviews'] }}</span>
                @endif
                @if($w['price'] > 0)
                  <span class="w-price">₱{{ number_format($w['price']) }}/hr</span>
                @endif
              </div>
            </div>
          </div>
          @if(!empty($w['skills']) && count($w['skills']) > 0)
            <div class="w-skills">
              @foreach(array_slice($w['skills'], 0, 3) as $skill)
                <span class="w-skill-tag">{{ $skill }}</span>
              @endforeach
            </div>
          @endif
          @if(!empty($w['works']) && count(array_filter(array_column($w['works'],'photo'))) > 0)
            <div class="w-works">
              <div class="w-works-row">
                @php $photos = array_filter(array_column($w['works'],'photo')); @endphp
                @foreach(array_slice($photos, 0, 3) as $photo)
                  <div class="w-work-thumb" style="background-image:url('{{ $photo }}')" title="Work sample"></div>
                @endforeach
              </div>
            </div>
          @endif

          <div class="w-card-actions">
            <span class="btn-outline-card" onclick="event.stopPropagation();event.preventDefault();window.location.href='{{ route('workers.public.show', $w['id']) }}'"><i class="fa-regular fa-user" aria-hidden="true"></i> View Profile</span>
            <span class="btn btn-solid" onclick="event.stopPropagation();event.preventDefault();showBookModal({{ $w['id'] }},'{{ addslashes($w['name']) }}','{{ addslashes($w['category']) }}')"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book Now</span>
          </div>
        </a>
      @endforeach
    </div>
  @else
    <div class="empty-workers fade-up">
      <i class="fa-solid fa-users-slash"></i>
      <h3>No workers found</h3>
      <p>No workers are available in this category yet. Check back soon or browse all categories.</p>
      <a href="/#services" class="btn btn-solid btn-lg" onclick="document.querySelector('.cat-pill.active')?.click()"><i class="fa-solid fa-arrow-left"></i> View All Workers</a>
    </div>
  @endif
</section>

<!-- STATS -->
<div class="stats fade-up">
  <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></div><div class="stat-num">500+</div><div class="stat-label">Active Workers</div></div>
  <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div><div class="stat-num">1,000+</div><div class="stat-label">Jobs Completed</div></div>
  <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-map-pin" aria-hidden="true"></i></div><div class="stat-num">42</div><div class="stat-label">Barangays Covered</div></div>
  <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-star" aria-hidden="true"></i></div><div class="stat-num">4.8★</div><div class="stat-label">Avg Rating</div></div>
</div>

<!-- TRUST -->
<div class="trust">
  <div class="trust-item"><div class="trust-ico"><i class="fa-solid fa-id-card" aria-hidden="true"></i></div><div><strong>ID-Verified Workers</strong><span>Valid ID &amp; clearance required</span></div></div>
  <div class="trust-item"><div class="trust-ico"><i class="fa-solid fa-robot" aria-hidden="true"></i></div><div><strong>AI-Assisted Matching</strong><span>Best worker for your job</span></div></div>
  <div class="trust-item"><div class="trust-ico"><i class="fa-solid fa-certificate" aria-hidden="true"></i></div><div><strong>PESO-Accredited Workers</strong><span>Verified by Public Employment Service Office</span></div></div>
  <div class="trust-item"><div class="trust-ico"><i class="fa-solid fa-star" aria-hidden="true"></i></div><div><strong>Rated &amp; Reviewed</strong><span>Read real feedback first</span></div></div>
</div>

<!-- HOW IT WORKS -->
<section class="section section-alt" id="how-it-works">
  <div class="sec-header fade-up">
    <div class="eyebrow">How It Works</div>
    <h2 class="sec-title">Booked in Four Steps</h2>
    <p class="sec-sub">From posting your job to getting it done — fast and simple.</p>
  </div>
  <div class="steps">
    <div class="step fade-up">
      <div class="step-n">01</div>
      <div class="step-icon"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></div>
      <h3 class="step-title">Post Your Job</h3>
      <p class="step-desc">Describe what needs fixing — type, location, and when you need it done.</p>
    </div>
    <div class="step fade-up">
      <div class="step-n">02</div>
      <div class="step-icon"><i class="fa-solid fa-robot" aria-hidden="true"></i></div>
      <h3 class="step-title">AI Finds Matches</h3>
      <p class="step-desc">Our system recommends verified, nearby workers ranked by rating and distance.</p>
    </div>
    <div class="step fade-up">
      <div class="step-n">03</div>
      <div class="step-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></div>
      <h3 class="step-title">Chat &amp; Book</h3>
      <p class="step-desc">Message your worker, confirm pricing, and lock in the schedule in-app.</p>
    </div>
    <div class="step fade-up">
      <div class="step-n">04</div>
      <div class="step-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>
      <h3 class="step-title">Rate &amp; Done</h3>
      <p class="step-desc">Leave a review after the job. Your feedback helps the whole community.</p>
    </div>
  </div>
</section>

<!-- TESTIMONIALS -->
<section class="section" id="testimonials">
  <div class="sec-header fade-up">
    <div class="eyebrow">Testimonials</div>
    <h2 class="sec-title">What Our Users Say</h2>
    <p class="sec-sub">Real feedback from homeowners and workers in Tuy, Batangas.</p>
  </div>
  <div class="testimonials">
    <div class="testimonial-card fade-up">
      <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
      <p class="quote">"Na-book ko agad si Mang Jose para sa sirang gripo ko. Wala pang isang oras, nasa bahay na at naayos agad. Sobrang convenient!"</p>
      <div class="author"><div class="author-avatar">AR</div><div class="author-info"><div class="name">Ana Reyes</div><div class="role">Homeowner, Tuy</div></div></div>
    </div>
    <div class="testimonial-card fade-up">
      <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
      <p class="quote">"Dati referral lang ang kitaan. Ngayon, may regular akong booking galing sa KaAyos. Nakaipon na ako para sa bagong gamit."</p>
      <div class="author"><div class="author-avatar">MC</div><div class="author-info"><div class="name">Mang Carlos</div><div class="role">Electrician, Tuy</div></div></div>
    </div>
    <div class="testimonial-card fade-up">
      <div class="stars"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></div>
      <p class="quote">"Yung AI matching nila, hindi biro. Inirecommend agad yung tamang worker para sa painting project namin. Sulit na sulit!"</p>
      <div class="author"><div class="author-avatar">DB</div><div class="author-info"><div class="name">Dennis B.</div><div class="role">Homeowner, Nasugbu</div></div></div>
    </div>
  </div>
</section>

<!-- JOIN AS WORKER -->
<section class="join" id="join">
  <div class="join-text">
    <div class="join-badge">
      <img src="{{ asset('images/peso-logo.jpg') }}" alt="PESO Tuy">
      <span>In Partnership with PESO Tuy, Batangas</span>
    </div>
    <h2>Are You a Skilled Trabahador?</h2>
    <p>KaAyos helps Filipino workers earn more, reach more clients, and build a professional reputation — without relying on referrals alone.</p>
    <a href="/register" class="btn btn-primary btn-lg"><i class="fa-solid fa-hammer" aria-hidden="true"></i> Register as a Worker</a>
  </div>
  <div class="perks">
    <div class="perk"><div class="perk-ico"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i></div><div><strong>More Job Visibility</strong><span>Get discovered by homeowners in your barangay and beyond</span></div></div>
    <div class="perk"><div class="perk-ico"><i class="fa-solid fa-star" aria-hidden="true"></i></div><div><strong>Build Your Reputation</strong><span>Earn ratings that set you apart from unverified workers</span></div></div>
    <div class="perk"><div class="perk-ico"><i class="fa-solid fa-briefcase" aria-hidden="true"></i></div><div><strong>Manage Your Work</strong><span>Track bookings and your portfolio in one place</span></div></div>
    <div class="perk"><div class="perk-ico"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div><div><strong>Safer Transactions</strong><span>In-app chat and clear agreements protect you and your clients</span></div></div>
  </div>
</section>

<!-- FAQ -->
<section class="section section-alt" id="faq">
  <div class="sec-header fade-up">
    <div class="eyebrow">FAQ</div>
    <h2 class="sec-title">Common Questions</h2>
  </div>
  <div class="faq-list">
    <div class="faq-item fade-up">
      <div class="faq-q" onclick="toggleFaq(this)" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' ')this.click()"><span><i class="fa-regular fa-circle-question" style="color:var(--b6);margin-right:8px"></i>Is KaAyos free to use as a homeowner?</span><i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">Yes. Creating an account, browsing workers, and booking jobs is completely free. You only pay the worker directly.</div>
    </div>
    <div class="faq-item fade-up">
      <div class="faq-q" onclick="toggleFaq(this)" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' ')this.click()"><span><i class="fa-regular fa-id-card" style="color:var(--b6);margin-right:8px"></i>How are workers verified?</span><i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">Every worker submits a valid government-issued ID and barangay clearance before their profile goes live. Workers who pass appear with a Verified badge.</div>
    </div>
    <div class="faq-item fade-up">
      <div class="faq-q" onclick="toggleFaq(this)" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' ')this.click()"><span><i class="fa-solid fa-robot" style="color:var(--b6);margin-right:8px"></i>How does AI matching work?</span><i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">When you post a job, KaAyos ranks candidates by distance, skill match, and community rating — so you see the most suitable workers first.</div>
    </div>
    <div class="faq-item fade-up">
      <div class="faq-q" onclick="toggleFaq(this)" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' ')this.click()"><span><i class="fa-solid fa-location-dot" style="color:var(--b6);margin-right:8px"></i>What areas does KaAyos cover?</span><i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">KaAyos currently serves all 42 barangays of Tuy, Batangas, with plans to expand to neighboring municipalities.</div>
    </div>
    <div class="faq-item fade-up">
      <div class="faq-q" onclick="toggleFaq(this)" role="button" tabindex="0" onkeydown="if(event.key==='Enter'||event.key===' ')this.click()"><span><i class="fa-regular fa-user" style="color:var(--b6);margin-right:8px"></i>Can I register as both a homeowner and a worker?</span><i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">Yes. One account supports both roles. Switch between them from your dashboard.</div>
    </div>
  </div>
  <div class="faq-contact fade-up">
    Still have questions? <a href="/contact">Contact our support team</a>
  </div>
</section>

<!-- SIGN-IN MODAL -->
<div id="signInModal" class="modal-overlay" onclick="if(event.target===this)hideSignInModal()" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
  <div class="modal-box">
    <button class="modal-close" onclick="hideSignInModal()" aria-label="Close">&times;</button>
    <div class="modal-icon"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>
    <h2 id="modalTitle">Sign In Required</h2>
    <p>Please sign in or create an account to continue booking <strong id="modalWorkerName"></strong>.</p>
    <div class="modal-actions">
      <button onclick="goToSignIn()" class="btn btn-solid"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign In</button>
      <button onclick="goToSignUp()" class="btn-ghost-dark"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Create Account</button>
    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-grid">
    <div class="f-brand">
      <div class="brand">
        <div class="flogo"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos"></div>
        <span>KaAyos</span>
      </div>
      <p>A web-based home service platform connecting homeowners with verified skilled workers in Tuy, Batangas — powered by AI matching. In partnership with PESO Tuy, Batangas.</p>
      <div class="partner-logos">
        <img src="{{ asset('images/peso-logo.jpg') }}" alt="PESO Tuy">
      </div>
    </div>
    <div>
      <div class="f-title">Services</div>
      <ul class="f-links">
        <li><a href="/?category=plumbing"><i class="fa-solid fa-wrench fa-fw" aria-hidden="true"></i> Plumbing</a></li>
        <li><a href="/?category=electrical"><i class="fa-solid fa-bolt fa-fw" aria-hidden="true"></i> Electrical</a></li>
        <li><a href="/?category=carpentry"><i class="fa-solid fa-screwdriver-wrench fa-fw" aria-hidden="true"></i> Carpentry</a></li>
        <li><a href="/?category=cleaning"><i class="fa-solid fa-broom fa-fw" aria-hidden="true"></i> Cleaning</a></li>
        <li><a href="/#services"><i class="fa-solid fa-list fa-fw" aria-hidden="true"></i> View all</a></li>
      </ul>
    </div>
    <div>
      <div class="f-title">Company</div>
      <ul class="f-links">
        <li><a href="/about"><i class="fa-solid fa-circle-info fa-fw" aria-hidden="true"></i> About KaAyos</a></li>
        <li><a href="#how-it-works"><i class="fa-solid fa-list-ol fa-fw" aria-hidden="true"></i> How It Works</a></li>
        <li><a href="/register"><i class="fa-solid fa-hammer fa-fw" aria-hidden="true"></i> Join as Worker</a></li>
        <li><a href="#faq"><i class="fa-solid fa-circle-question fa-fw" aria-hidden="true"></i> FAQ</a></li>
        <li><a href="/contact"><i class="fa-solid fa-envelope fa-fw" aria-hidden="true"></i> Contact</a></li>
        <li><a href="/privacy"><i class="fa-solid fa-shield fa-fw" aria-hidden="true"></i> Privacy Policy</a></li>
        <li><a href="/terms"><i class="fa-solid fa-file-lines fa-fw" aria-hidden="true"></i> Terms of Service</a></li>
        <li><a href="/safety"><i class="fa-solid fa-shield-halved fa-fw" aria-hidden="true"></i> Safety</a></li>
      </ul>
    </div>
  </div>
  <button class="back-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></button>
  <div class="f-bottom">
    <p>&copy; {{ date('Y') }} KaAyos &mdash; Capstone project by Salanguit, Formentos &amp; Briones &middot; Batangas State University ARASOF &ndash; Nasugbu Campus.</p>
    <div class="socials">
      <a href="https://facebook.com/kaayos" class="soc" title="Facebook" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
      <a href="mailto:hello@kaayos.com" class="soc" title="Email" aria-label="Email"><i class="fa-solid fa-envelope"></i></a>
    </div>
  </div>
</footer>

<script>
function toggleMobileMenu() {
  var open = document.getElementById('mobileDrawer').classList.toggle('open');
  document.getElementById('mobileOverlay').classList.toggle('open');
  document.getElementById('navToggle').classList.toggle('open');
  document.body.style.overflow = open ? 'hidden' : '';
}

function closeMobileMenu() {
  document.getElementById('mobileDrawer').classList.remove('open');
  document.getElementById('mobileOverlay').classList.remove('open');
  document.getElementById('navToggle').classList.remove('open');
  document.body.style.overflow = '';
}

function toggleFaq(el){
  var item = el.closest('.faq-item');
  var open = item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(function(i){ i.classList.remove('open'); });
  if(!open) item.classList.add('open');
}

/* NAV SCROLL */
(function(){
  var nav = document.querySelector('.nav');
  if(nav){
    window.addEventListener('scroll',function(){
      if(window.scrollY>40){nav.classList.add('scrolled');}else{nav.classList.remove('scrolled');}
    });
  }
})();

/* SCROLL ANIMATIONS */
(function(){
  var observer = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
      if(e.isIntersecting){ e.target.classList.add('visible'); observer.unobserve(e.target); }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.fade-up').forEach(function(el){ observer.observe(el); });
})();

/* CATEGORY FILTER */
(function(){
  var pills = document.querySelectorAll('.cat-pill');
  var cards = document.querySelectorAll('.worker-card');
  if(!pills.length) return;
  pills.forEach(function(btn){
    btn.addEventListener('click', function(){
      var cat = btn.getAttribute('data-category');
      pills.forEach(function(p){ p.classList.remove('active'); });
      btn.classList.add('active');
      var visibleCount = 0;
      cards.forEach(function(card){
        var match = !cat || card.getAttribute('data-category') === cat;
        card.style.display = match ? '' : 'none';
        if(match) visibleCount++;
      });
      var url = new URL(window.location);
      if(cat){ url.searchParams.set('category', cat); }else{ url.searchParams.delete('category'); }
      history.replaceState(null, '', url);
    });
  });
})();

/* SEARCH */
function searchTag(el) {
  document.getElementById('searchQuery').value = el.textContent;
  doSearch();
}

function doSearch() {
  var q = document.getElementById('searchQuery').value.trim();
  var loc = document.getElementById('searchLocation').value.trim();
  var params = new URLSearchParams();
  if(q) params.set('q', q);
  if(loc) params.set('location', loc);
  window.location.href = '/search' + (params.toString() ? '?' + params.toString() : '');
}

/* ESC key closes modal + mobile menu */
document.addEventListener('keydown', function(e) {
  if(e.key === 'Escape') {
    if(document.getElementById('signInModal').classList.contains('active')) hideSignInModal();
    if(document.getElementById('mobileDrawer').classList.contains('open')) closeMobileMenu();
  }
});

/* BOOKING INTENT */
var _bookingWorkerId = null;
var _bookingWorkerName = '';
var _bookingCategory = '';

function saveBookingIntent(workerId, workerName, serviceCategory) {
  var data = {
    worker_id: workerId,
    worker_name: workerName,
    service_category: serviceCategory,
    timestamp: Date.now()
  };
  sessionStorage.setItem('kaayos_booking_intent', JSON.stringify(data));
  localStorage.setItem('kaayos_booking_intent', JSON.stringify({
    worker_id: workerId,
    worker_name: workerName,
    service_category: serviceCategory,
    timestamp: data.timestamp,
    expires: Date.now() + 24 * 60 * 60 * 1000
  }));
}

function showBookModal(id, name, category) {
  _bookingWorkerId = id;
  _bookingWorkerName = name;
  _bookingCategory = category;
  document.getElementById('modalWorkerName').textContent = name;
  var modal = document.getElementById('signInModal');
  modal.classList.add('active');
  setTimeout(function(){ modal.querySelector('.btn-solid').focus(); }, 100);
}

function hideSignInModal() {
  document.getElementById('signInModal').classList.remove('active');
}

function goToSignIn() {
  saveBookingIntent(_bookingWorkerId, _bookingWorkerName, _bookingCategory);
  window.location.href = '/login?intended=/workers/' + _bookingWorkerId;
}

function goToSignUp() {
  saveBookingIntent(_bookingWorkerId, _bookingWorkerName, _bookingCategory);
  window.location.href = '/register?intended=/workers/' + _bookingWorkerId;
}
</script>
</body>
</html>
