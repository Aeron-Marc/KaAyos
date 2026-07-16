<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $worker->name }} — KaAyos</title>
<link rel="icon" href="{{ asset('images/KaAyos_logo.jpeg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
--b9:#042C53;--b8:#0C447C;--b7:#185FA5;--b6:#1A6FC4;--b4:#378ADD;--b2:#85B7EB;--b1:#B5D4F4;--b0:#E6F1FB;
--g9:#1B2430;--g7:#3D4A56;--g4:#8C97A4;--g1:#E8ECF0;--white:#fff;--off:#F7F8FA;
}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;color:var(--g7);background:var(--off);line-height:1.6;font-size:16px}
a{text-decoration:none;color:inherit}

.nav{background:var(--b9);padding:0 5%;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.nav-logo{display:flex;align-items:center;gap:9px}
.logo-box{width:50px;height:50px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px}
.logo-box img{width:100%;height:100%;object-fit:contain}
.nav-logo span{font-size:1.4rem;font-weight:700;color:#fff;letter-spacing:.02em}
.nav-links{display:flex;gap:26px;list-style:none}
.nav-links a{font-size:.875rem;font-weight:500;color:rgba(255,255,255,.72);transition:color .18s}
.nav-links a:hover{color:#fff}
.nav-cta{display:flex;gap:9px}
.btn{font-size:.875rem;font-weight:600;border-radius:7px;padding:8px 18px;cursor:pointer;border:none;transition:all .18s;white-space:nowrap;display:inline-flex;align-items:center;gap:7px}
.btn-ghost{background:transparent;color:rgba(255,255,255,.82);border:1.5px solid rgba(255,255,255,.3)}
.btn-ghost:hover{border-color:rgba(255,255,255,.7);color:#fff}
.btn-solid{background:var(--b6);color:#fff}
.btn-solid:hover{background:var(--b7)}
.btn-outline{background:transparent;color:var(--b6);border:1.5px solid var(--b4);font-size:.875rem;font-weight:600;padding:8px 18px;border-radius:7px;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:7px}
.btn-outline:hover{background:var(--b0)}

.back-bar{background:var(--white);padding:12px 5%;border-bottom:1px solid var(--g1)}
.back-bar a{font-size:.875rem;font-weight:500;color:var(--b6);display:inline-flex;align-items:center;gap:6px;transition:color .18s}
.back-bar a:hover{color:var(--b8)}

.profile-section{padding:40px 5%;max-width:1100px;margin:0 auto}

.profile-layout{display:flex;gap:28px;align-items:flex-start}

.profile-card{background:var(--white);border-radius:14px;padding:28px 24px;flex:0 0 320px;align-self:start;box-shadow:0 2px 16px rgba(0,0,0,.07)}
.profile-card .avatar-wrap{text-align:center;padding:4px 0}
.profile-card .avatar-wrap img{width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--b2)}
.profile-card .avatar-wrap .initials{width:100px;height:100px;border-radius:50%;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:700;margin:0 auto}
.profile-card h2{margin-top:14px;font-size:1.15rem;text-align:center;color:var(--b9)}
.profile-card .trade{text-align:center;color:var(--b6);font-weight:500;font-size:.9rem;margin-top:2px}
.profile-card .rating-row{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:8px}
.profile-card .rating-row i{color:#f59e0b}
.profile-card .rating-row span{font-weight:600;color:var(--g9)}
.profile-card .rating-row small{color:var(--g4);font-size:.82rem}
.verified-badge{display:inline-flex;align-items:center;gap:4px;background:#dcfce7;color:#166534;padding:4px 10px;border-radius:20px;font-size:.78rem;font-weight:500;margin-top:10px}
.profile-details{margin-top:20px;display:flex;flex-direction:column;gap:10px}
.profile-details .detail-row{display:flex;justify-content:space-between;font-size:.88rem}
.profile-details .detail-row .label{color:var(--g4)}
.profile-details .detail-row .value{font-weight:600;color:var(--g9);text-align:right}

.profile-content{flex:1;min-width:0;display:flex;flex-direction:column;gap:20px}
.content-card{background:var(--white);border-radius:14px;padding:22px 24px;box-shadow:0 2px 16px rgba(0,0,0,.07)}
.content-card h3{font-size:1.05rem;font-weight:700;color:var(--b9);margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--g1)}
.content-card p{font-size:.9rem;color:var(--g7);line-height:1.75}

.skill-tags{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px}
.skill-tag{background:var(--b0);color:var(--b7);padding:5px 14px;border-radius:20px;font-size:.82rem;font-weight:500}

.lang-tags{display:flex;gap:8px;flex-wrap:wrap;margin-top:4px}
.lang-tag{background:var(--g1);color:var(--g7);padding:4px 12px;border-radius:20px;font-size:.82rem}

.review-item{padding:14px 0;border-bottom:1px solid var(--g1)}
.review-item:last-child{border-bottom:none}
.review-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px}
.review-header .reviewer{font-weight:500;font-size:.88rem;color:var(--g9)}
.review-header .stars{display:flex;gap:2px}
.review-header .stars i{color:#f59e0b;font-size:.75rem}
.review-comment{font-size:.85rem;color:var(--g7);line-height:1.6;margin-top:4px}
.review-date{font-size:.75rem;color:var(--g4);margin-top:4px}

.footer{background:var(--g9);padding:48px 5% 24px;margin-top:60px}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:36px;margin-bottom:36px}
.f-brand p{font-size:.84rem;color:rgba(255,255,255,.45);line-height:1.65;max-width:250px;margin-top:12px}
.f-brand .brand{display:flex;align-items:center;gap:9px}
.f-brand .brand span{font-size:1.3rem;font-weight:700;color:#fff}
.f-brand .flogo{width:32px;height:32px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px}
.f-title{font-size:.8rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#fff;margin-bottom:12px}
.f-links{list-style:none;display:flex;flex-direction:column;gap:8px}
.f-links a{font-size:.84rem;color:rgba(255,255,255,.45);transition:color .18s;display:inline-flex;align-items:center;gap:6px}
.f-links a:hover{color:var(--b2)}
.f-bottom{border-top:1px solid rgba(255,255,255,.07);padding-top:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.f-bottom p{font-size:.76rem;color:rgba(255,255,255,.3)}
.socials{display:flex;gap:10px}
.soc{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:.9rem;color:rgba(255,255,255,.45);cursor:pointer;transition:all .18s}
.soc:hover{background:var(--b6);color:#fff}

/* SIGN-IN MODAL */
.modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:1000;display:none;align-items:center;justify-content:center;animation:fadeIn .2s ease}
.modal-overlay.active{display:flex}
.modal-box{background:var(--white);border-radius:16px;width:90%;max-width:400px;padding:32px 28px 24px;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:center;position:relative;animation:slideUp .25s ease}
.modal-close{position:absolute;top:14px;right:16px;background:none;border:none;font-size:1.4rem;color:var(--g4);cursor:pointer;line-height:1;padding:4px;transition:color .18s}
.modal-close:hover{color:var(--g9)}
.modal-icon{width:56px;height:56px;border-radius:50%;background:var(--b0);display:flex;align-items:center;justify-content:center;margin:0 auto 16px}
.modal-icon i{font-size:1.5rem;color:var(--b6)}
.modal-box h2{font-size:1.2rem;font-weight:700;color:var(--b9);margin-bottom:8px}
.modal-box p{font-size:.9rem;color:var(--g7);line-height:1.65;margin-bottom:24px}
.modal-actions{display:flex;flex-direction:column;gap:10px}
.modal-actions .btn{justify-content:center;padding:12px 0;font-size:.95rem;width:100%}
.modal-actions .btn-ghost-dark{background:transparent;color:var(--b6);border:1.5px solid var(--b4);border-radius:7px;font-weight:600;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:7px;text-decoration:none;padding:12px 0;font-size:.95rem;justify-content:center}
.modal-actions .btn-ghost-dark:hover{background:var(--b0)}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideUp{from{opacity:0;transform:translateY(16px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}

@media(max-width:768px){
.profile-layout{flex-direction:column}
.profile-card{flex:none!important;width:100%}
.nav-links{display:none}
.footer-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:480px){
.footer-grid{grid-template-columns:1fr}
}
</style>
</head>
<body>

<nav class="nav">
  <a href="{{ route('home') }}" class="nav-logo">
    <div class="logo-box"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos Logo"></div>
    <span>KaAyos</span>
  </a>
  <ul class="nav-links">
    <li><a href="{{ route('home') }}#services">Services</a></li>
    <li><a href="{{ route('home') }}#how-it-works">How It Works</a></li>
    <li><a href="{{ route('home') }}#join">Join as Worker</a></li>
    <li><a href="{{ route('home') }}#faq">FAQ</a></li>
  </ul>
  <div class="nav-cta">
    <a href="{{ route('login') }}" class="btn btn-ghost"><i class="fa-regular fa-user" aria-hidden="true"></i> Log In</a>
    <a href="{{ route('register') }}" class="btn btn-solid"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign Up Free</a>
  </div>
</nav>

<div class="back-bar">
  <a href="{{ route('home') }}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Home</a>
</div>

<div class="profile-section">
  <div class="profile-layout">

    <div class="profile-card">
      <div class="avatar-wrap">
        @if($worker->avatar)
          <img src="{{ Storage::url($worker->avatar) }}" alt="{{ $worker->name }}">
        @else
          <div class="initials">{{ strtoupper(substr($worker->first_name, 0, 1) . substr($worker->last_name, 0, 1)) }}</div>
        @endif
      </div>
      <h2>{{ $worker->name }}</h2>
      <p class="trade">{{ $worker->service_category ?? 'General' }}</p>

      @if($workerProfile && $workerProfile->average_rating)
        <div class="rating-row">
          <i class="fa-solid fa-star" aria-hidden="true"></i>
          <span>{{ number_format($workerProfile->average_rating, 1) }}</span>
          <small>({{ $reviews->count() }} {{ \Illuminate\Support\Str::plural('review', $reviews->count()) }})</small>
        </div>
      @endif

      @if($workerProfile && $workerProfile->government_id_verified)
        <div style="text-align:center;">
          <span class="verified-badge"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Verified</span>
        </div>
      @endif

      <div class="profile-details">
        @if($workerProfile && $workerProfile->hourly_rate)
          <div class="detail-row">
            <span class="label">Rate</span>
            <span class="value">₱{{ number_format($workerProfile->hourly_rate) }}/hr</span>
          </div>
        @endif
        @if($workerProfile && $workerProfile->years_of_experience)
          <div class="detail-row">
            <span class="label">Experience</span>
            <span class="value">{{ $workerProfile->years_of_experience }} years</span>
          </div>
        @endif
        @if($workerProfile && !empty($workerProfile->spoken_languages))
          <div class="detail-row" style="flex-direction:column;gap:4px;">
            <span class="label">Languages</span>
            <div class="lang-tags">
              @foreach($workerProfile->spoken_languages as $lang)
                <span class="lang-tag">{{ $lang }}</span>
              @endforeach
            </div>
          </div>
        @endif
        @if($worker->phone)
          <div class="detail-row">
            <span class="label">Contact</span>
            <span class="value">{{ $worker->phone }}</span>
          </div>
        @endif
      </div>

      @auth
        <a href="{{ route('client.workers.show', $worker->id) }}" class="btn btn-solid" style="width:100%;margin-top:18px;justify-content:center;">
          <i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book Now
        </a>
      @else
        <button onclick="showSignInModal()" class="btn btn-solid" style="width:100%;margin-top:18px;justify-content:center;">
          <i class="fa-solid fa-calendar-check" aria-hidden="true"></i> Book Now
        </button>
      @endauth
    </div>

    <div class="profile-content">

      @if($workerProfile && $workerProfile->bio)
        <div class="content-card">
          <h3>About</h3>
          <p>{{ $workerProfile->bio }}</p>
        </div>
      @endif

      @if($workerProfile && !empty($workerProfile->skills))
        <div class="content-card">
          <h3>Skills</h3>
          <div class="skill-tags">
            @foreach($workerProfile->skills as $skill)
              <span class="skill-tag">{{ $skill }}</span>
            @endforeach
          </div>
        </div>
      @endif

      @if($reviews->count() > 0)
        <div class="content-card">
          <h3>Reviews ({{ $reviews->count() }})</h3>
          @foreach($reviews as $review)
            <div class="review-item">
              <div class="review-header">
                <span class="reviewer">{{ $review->client?->name ?? 'Anonymous' }}</span>
                <div class="stars">
                  @for($s = 1; $s <= 5; $s++)
                    <i class="fa-{{ $s <= $review->rating ? 'solid' : 'regular' }} fa-star" aria-hidden="true"></i>
                  @endfor
                </div>
              </div>
              @if($review->comment)
                <p class="review-comment">{{ $review->comment }}</p>
              @endif
              <p class="review-date">{{ $review->created_at->diffForHumans() }}</p>
            </div>
          @endforeach
        </div>
      @endif

    </div>
  </div>
</div>

<footer class="footer">
  <div class="footer-grid">
    <div class="f-brand">
      <div class="brand">
        <div class="flogo"><i class="fa-solid fa-house-chimney" aria-hidden="true"></i></div>
        <span>KaAyos</span>
      </div>
      <p>A web-based home service platform connecting homeowners with verified skilled workers in Tuy, Batangas — powered by AI matching.</p>
    </div>
    <div>
      <div class="f-title">Services</div>
      <ul class="f-links">
        <li><a href="/services?category=plumbing"><i class="fa-solid fa-wrench fa-fw" aria-hidden="true"></i> Plumbing</a></li>
        <li><a href="/services?category=electrical"><i class="fa-solid fa-bolt fa-fw" aria-hidden="true"></i> Electrical</a></li>
        <li><a href="/services?category=carpentry"><i class="fa-solid fa-screwdriver-wrench fa-fw" aria-hidden="true"></i> Carpentry</a></li>
        <li><a href="/services?category=cleaning"><i class="fa-solid fa-broom fa-fw" aria-hidden="true"></i> Cleaning</a></li>
        <li><a href="/services"><i class="fa-solid fa-grid-2 fa-fw" aria-hidden="true"></i> View all</a></li>
      </ul>
    </div>
    <div>
      <div class="f-title">Company</div>
      <ul class="f-links">
        <li><a href="/about"><i class="fa-solid fa-circle-info fa-fw" aria-hidden="true"></i> About KaAyos</a></li>
        <li><a href="{{ route('home') }}#how-it-works"><i class="fa-solid fa-list-ol fa-fw" aria-hidden="true"></i> How It Works</a></li>
        <li><a href="{{ route('register') }}"><i class="fa-solid fa-hammer fa-fw" aria-hidden="true"></i> Join as Worker</a></li>
        <li><a href="{{ route('home') }}#faq"><i class="fa-solid fa-circle-question fa-fw" aria-hidden="true"></i> FAQ</a></li>
        <li><a href="/contact"><i class="fa-solid fa-envelope fa-fw" aria-hidden="true"></i> Contact</a></li>
        <li><a href="/privacy"><i class="fa-solid fa-shield fa-fw" aria-hidden="true"></i> Privacy Policy</a></li>
        <li><a href="/terms"><i class="fa-solid fa-file-lines fa-fw" aria-hidden="true"></i> Terms of Service</a></li>
      </ul>
    </div>
  </div>
  <div class="f-bottom">
    <div class="socials">
      <div class="soc" title="Facebook"><i class="fa-brands fa-facebook-f"></i></div>
      <div class="soc" title="Email"><i class="fa-solid fa-envelope"></i></div>
    </div>
  </div>
</footer>

<div id="signInModal" class="modal-overlay" onclick="if(event.target===this)hideSignInModal()">
  <div class="modal-box">
    <button class="modal-close" onclick="hideSignInModal()" aria-label="Close">&times;</button>
    <div class="modal-icon"><i class="fa-solid fa-lock" aria-hidden="true"></i></div>
    <h2>Sign In Required</h2>
    <p>Please sign in or create an account to continue booking this worker.</p>
    <div class="modal-actions">
      <button onclick="goToSignIn()" class="btn btn-solid"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign In</button>
      <button onclick="goToSignUp()" class="btn-ghost-dark"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Create Account</button>
    </div>
  </div>
</div>

<script>
function saveBookingIntent() {
  var data = {
    worker_id: {{ $worker->id }},
    worker_name: @json($worker->name),
    service_category: @json($worker->service_category ?? 'General'),
    timestamp: Date.now()
  };
  sessionStorage.setItem('kaayos_booking_intent', JSON.stringify(data));
  localStorage.setItem('kaayos_booking_intent', JSON.stringify({
    worker_id: data.worker_id,
    worker_name: data.worker_name,
    service_category: data.service_category,
    timestamp: data.timestamp,
    expires: Date.now() + 24 * 60 * 60 * 1000
  }));
}
function goToSignIn() {
  saveBookingIntent();
  window.location.href = '{{ route('login') }}?intended={{ urlencode('/workers/' . $worker->id) }}';
}
function goToSignUp() {
  saveBookingIntent();
  window.location.href = '{{ route('register') }}?intended={{ urlencode('/workers/' . $worker->id) }}';
}
function showSignInModal() { document.getElementById('signInModal').classList.add('active'); }
function hideSignInModal() { document.getElementById('signInModal').classList.remove('active'); }

(function() {
  var intent = null;
  var raw = sessionStorage.getItem('kaayos_booking_intent');
  if (raw) { try { intent = JSON.parse(raw); } catch(e) {} }
  if (!intent) {
    raw = localStorage.getItem('kaayos_booking_intent');
    if (raw) { try { var p = JSON.parse(raw); if (p.expires && Date.now() < p.expires) { intent = p; } else if (p.expires) { localStorage.removeItem('kaayos_booking_intent'); } } catch(e) {} }
  }
  if (intent && intent.worker_id) {
    sessionStorage.removeItem('kaayos_booking_intent');
    localStorage.removeItem('kaayos_booking_intent');
    @auth
    window.location.href = '/client/workers/' + intent.worker_id;
    @endauth
  }
})();
</script>
</body>
</html>
