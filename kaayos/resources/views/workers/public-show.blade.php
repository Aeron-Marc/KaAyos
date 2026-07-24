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

.works-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:8px}
.works-thumb{aspect-ratio:1;border-radius:10px;background:var(--off);background-size:cover;background-position:center;border:1.5px solid var(--g1);display:flex;align-items:center;justify-content:center;color:var(--g4);font-size:1.3rem;transition:all .2s;cursor:pointer}
.works-thumb:hover{transform:scale(1.05);box-shadow:0 4px 12px rgba(0,0,0,.1);border-color:var(--b4)}
.works-sample{background:var(--b0);color:var(--b2);border-style:dashed}
.works-sample:hover{border-color:var(--b4);color:var(--b6)}

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

/* TABS */
.tabs-card{position:relative}
.tabs-bar{display:flex;position:relative;border-bottom:2px solid var(--g1);margin-bottom:16px}
.tabs-bar label{flex:1;padding:10px 0;text-align:center;cursor:pointer;font-weight:600;font-size:.9rem;color:var(--g4);transition:color .2s;position:relative;z-index:1}
.tabs-card input[type="radio"]{display:none}
#pt-posts:checked~.tabs-bar label[for="pt-posts"],
#pt-reviews:checked~.tabs-bar label[for="pt-reviews"]{color:var(--b6)}
.tabs-slider{position:absolute;bottom:-2px;left:0;width:50%;height:3px;background:var(--b6);transition:left .3s ease;border-radius:2px}
#pt-reviews:checked~.tabs-bar .tabs-slider{left:50%}
.tab-content{display:none}
#pt-posts:checked~.tab-content#tc-posts,
#pt-reviews:checked~.tab-content#tc-reviews{display:block}
.tab-empty{text-align:center;padding:40px 20px;color:var(--g4)}
.tab-empty i{font-size:2.5rem;display:block;margin-bottom:12px}
.tab-empty p{font-size:.9rem}
.works-item{position:relative;border-radius:10px;overflow:hidden;background:var(--off);border:1.5px solid var(--g1);transition:all .2s}
.works-item:hover{border-color:var(--b4);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.works-item .thumb{width:100%;aspect-ratio:1;background-size:cover;background-position:center;display:flex;align-items:center;justify-content:center;color:var(--g4);font-size:1.3rem}
.works-item .thumb.sample{background:var(--b0);color:var(--b2);border-style:dashed}
.works-caption{padding:6px 10px 10px;font-size:.8rem;color:var(--g7);line-height:1.4}

.footer{background:var(--g9);padding:48px 5% 24px;margin-top:60px}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:36px;margin-bottom:36px}
.f-brand p{font-size:.84rem;color:rgba(255,255,255,.45);line-height:1.65;max-width:250px;margin-top:12px}
.f-brand .brand{display:flex;align-items:center;gap:9px}
.f-brand .brand span{font-size:1.3rem;font-weight:700;color:#fff}
.f-brand .flogo{width:32px;height:32px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px}
.f-brand .flogo img{width:100%;height:100%;object-fit:contain;border-radius:6px}
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

.nav-hamburger{display:none;flex-direction:column;gap:5px;background:none;border:none;cursor:pointer;padding:8px}
.nav-hamburger span{display:block;width:22px;height:2px;background:#fff;border-radius:2px;transition:all .3s}
.nav-hamburger.active span:nth-child(1){transform:rotate(45deg) translate(5px,5px)}
.nav-hamburger.active span:nth-child(2){opacity:0}
.nav-hamburger.active span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px)}
.mobile-nav{display:none;position:fixed;top:60px;left:0;right:0;background:var(--b9);z-index:99;padding:16px 5% 20px;box-shadow:0 4px 12px rgba(0,0,0,.2);flex-direction:column;gap:4px}
.mobile-nav.open{display:flex}
.mobile-nav a{display:block;padding:12px 16px;border-radius:8px;color:rgba(255,255,255,.72);font-size:.95rem;font-weight:500;transition:all .18s}
.mobile-nav a:hover{background:rgba(255,255,255,.08);color:#fff}
@media(max-width:768px){
.profile-layout{flex-direction:column}
.profile-card{flex:none!important;width:100%}
.nav-links{display:none}
.nav-hamburger{display:flex}
.nav{padding:0 4%}
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
  <button class="nav-hamburger" id="navHamburger" aria-label="Toggle menu">
    <span></span><span></span><span></span>
  </button>
</nav>
<div class="mobile-nav" id="mobileNav">
  <a href="{{ route('home') }}#services">Services</a>
  <a href="{{ route('home') }}#how-it-works">How It Works</a>
  <a href="{{ route('home') }}#join">Join as Worker</a>
  <a href="{{ route('home') }}#faq">FAQ</a>
</div>

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

      @if($reviewCount > 0)
        <div class="rating-row">
          <i class="fa-solid fa-star" aria-hidden="true"></i>
          <span>{{ number_format($averageRating, 1) }}</span>
          <small>({{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }})</small>
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

      @php
        $hasPortfolio = $workerProfile && $workerProfile->portfolios && $workerProfile->portfolios->count() > 0;
        $hasReviews = $reviews->count() > 0;
      @endphp

      <div class="content-card tabs-card">
        <input type="radio" name="p-tabs" id="pt-posts"{{ $hasPortfolio ? ' checked' : '' }}>
        <input type="radio" name="p-tabs" id="pt-reviews"{{ !$hasPortfolio ? ' checked' : '' }}>

        <div class="tabs-bar">
          <label for="pt-posts"><i class="fa-solid fa-images" aria-hidden="true"></i> Posts</label>
          <label for="pt-reviews"><i class="fa-solid fa-star" aria-hidden="true"></i> Reviews</label>
          <div class="tabs-slider"></div>
        </div>

        <div class="tab-content" id="tc-posts">
          @if($hasPortfolio)
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;">
              @foreach($workerProfile->portfolios as $i => $item)
                <div class="works-item">
                  <div class="thumb{{ !$item->photo_path ? ' sample' : '' }}"@if($item->photo_path) style="background-image:url('{{ Storage::url($item->photo_path) }}');cursor:pointer"@endif data-index="{{ $i }}">
                    @if(!$item->photo_path)<i class="fa-solid fa-camera"></i>@endif
                  </div>
                  @if($item->caption)
                    <div class="works-caption">{{ $item->caption }}</div>
                  @endif
                </div>
              @endforeach
            </div>
          @else
            <div class="tab-empty">
              <i class="fa-regular fa-image" aria-hidden="true"></i>
              <p>No posts yet</p>
            </div>
          @endif
        </div>

        <div class="tab-content" id="tc-reviews">
          @if($hasReviews)
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
          @else
            <div class="tab-empty">
              <i class="fa-regular fa-comment" aria-hidden="true"></i>
              <p>No reviews yet</p>
            </div>
          @endif
        </div>
      </div>

    </div>
  </div>
</div>

<footer class="footer">
  <div class="footer-grid">
    <div class="f-brand">
      <div class="brand">
        <div class="flogo"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos"></div>
        <span>KaAyos</span>
      </div>
      <p>A web-based home service platform connecting homeowners with verified skilled workers in Tuy, Batangas — powered by AI matching.</p>
    </div>
    <div>
      <div class="f-title">Services</div>
      <ul class="f-links">
        <li><a href="/?category=plumbing"><i class="fa-solid fa-wrench fa-fw" aria-hidden="true"></i> Plumbing</a></li>
        <li><a href="/?category=electrical"><i class="fa-solid fa-bolt fa-fw" aria-hidden="true"></i> Electrical</a></li>
        <li><a href="/?category=carpentry"><i class="fa-solid fa-screwdriver-wrench fa-fw" aria-hidden="true"></i> Carpentry</a></li>
        <li><a href="/?category=cleaning"><i class="fa-solid fa-broom fa-fw" aria-hidden="true"></i> Cleaning</a></li>
        <li><a href="/#services"><i class="fa-solid fa-grid-2 fa-fw" aria-hidden="true"></i> View all</a></li>
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

<div id="photoLightbox" class="photo-lightbox" onclick="if(event.target===this)closeLightbox()" role="dialog" aria-modal="true" aria-label="Photo viewer">
  <button class="pl-close" onclick="closeLightbox()" aria-label="Close">&times;</button>
  <button class="pl-nav pl-prev" onclick="navigateLightbox(-1)" aria-label="Previous photo"><i class="fa-solid fa-chevron-left"></i></button>
  <button class="pl-nav pl-next" onclick="navigateLightbox(1)" aria-label="Next photo"><i class="fa-solid fa-chevron-right"></i></button>
  <div class="pl-content">
    <img class="pl-image" id="plImage" src="" alt="Portfolio photo">
    <div class="pl-caption" id="plCaption"></div>
  </div>
</div>

<style>
.photo-lightbox{position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:2000;display:none;align-items:center;justify-content:center;animation:plFadeIn .2s ease;padding:20px}
.photo-lightbox.active{display:flex}
.pl-close{position:absolute;top:16px;right:20px;background:none;border:none;color:rgba(255,255,255,.7);font-size:2.2rem;cursor:pointer;line-height:1;z-index:10;padding:4px 8px;border-radius:8px;transition:color .2s,background .2s}
.pl-close:hover{color:#fff;background:rgba(255,255,255,.1)}
.pl-nav{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.08);border:none;color:rgba(255,255,255,.7);width:44px;height:44px;border-radius:50%;cursor:pointer;font-size:1.2rem;transition:all .2s;z-index:10;display:none;align-items:center;justify-content:center}
.pl-nav:hover{background:rgba(255,255,255,.18);color:#fff}
.pl-nav.show{display:flex}
.pl-prev{left:16px}
.pl-next{right:16px}
.pl-content{display:flex;flex-direction:column;align-items:center;max-width:90vw;max-height:90vh}
.pl-image{max-width:100%;max-height:80vh;object-fit:contain;border-radius:8px;box-shadow:0 8px 40px rgba(0,0,0,.4)}
.pl-caption{color:rgba(255,255,255,.7);font-size:.9rem;margin-top:16px;text-align:center;max-width:600px;line-height:1.5}
@keyframes plFadeIn{from{opacity:0}to{opacity:1}}
</style>

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

@php
$pubPhotos = $workerProfile && $workerProfile->portfolios
    ? $workerProfile->portfolios->map(fn($p) => [
        'url'     => $p->photo_path ? Storage::url($p->photo_path) : null,
        'caption' => $p->caption,
    ])->values()->toArray()
    : [];
@endphp

<script>
document.getElementById('navHamburger').addEventListener('click', function() {
  this.classList.toggle('active');
  document.getElementById('mobileNav').classList.toggle('open');
});
var pubPhotos = @json($pubPhotos);

function openLightbox(index) {
  plIndex = index;
  updateLightbox();
  document.getElementById('photoLightbox').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  document.getElementById('photoLightbox').classList.remove('active');
  document.body.style.overflow = '';
}

function navigateLightbox(dir) {
  plIndex = (plIndex + dir + pubPhotos.length) % pubPhotos.length;
  updateLightbox();
}

function updateLightbox() {
  var img = document.getElementById('plImage');
  var cap = document.getElementById('plCaption');
  img.src = pubPhotos[plIndex].url;
  cap.textContent = pubPhotos[plIndex].caption || '';
  document.querySelectorAll('.pl-nav').forEach(function(n){ n.classList.toggle('show', pubPhotos.length > 1); });
}

document.addEventListener('keydown', function(e) {
  if (!document.getElementById('photoLightbox').classList.contains('active')) return;
  if (e.key === 'Escape') closeLightbox();
  if (e.key === 'ArrowLeft') navigateLightbox(-1);
  if (e.key === 'ArrowRight') navigateLightbox(1);
});

document.querySelectorAll('.works-item .thumb[data-index]').forEach(function(el) {
  el.addEventListener('click', function() {
    openLightbox(parseInt(this.dataset.index));
  });
});

var plIndex = 0;

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
  window.location.href = '{{ route('login') }}?intended={{ urlencode('/client/workers/' . $worker->id) }}';
}
function goToSignUp() {
  saveBookingIntent();
  window.location.href = '{{ route('register') }}?intended={{ urlencode('/client/workers/' . $worker->id) }}';
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
    @if(auth()->user()->role === 'client')
    window.location.href = '/client/workers/' + intent.worker_id;
    @endif
    @endauth
  }
})();
</script>
</body>
</html>
