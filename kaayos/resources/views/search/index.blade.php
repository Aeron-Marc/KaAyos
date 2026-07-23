<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find Workers – KaAyos</title>
<link rel="icon" href="{{ asset('images/KaAyos_logo.jpeg') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--b9:#042C53;--b8:#0C447C;--b7:#185FA5;--b6:#1A6FC4;--b4:#378ADD;--b2:#85B7EB;--b1:#B5D4F4;--b0:#E6F1FB;--g9:#1B2430;--g7:#3D4A56;--g4:#8C97A4;--g1:#E8ECF0;--white:#fff;--off:#F7F8FA;--amber:#f5a623;--amber-hover:#d4891a}
html{scroll-behavior:smooth}
body{font-family:'Inter',sans-serif;color:var(--g7);background:var(--off);line-height:1.6;font-size:16px}
a{text-decoration:none;color:inherit}
.nav{background:var(--b9);padding:0 5%;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.nav-logo{display:flex;align-items:center;gap:9px}
.logo-box{width:50px;height:50px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px}
.logo-box img{width:100%;height:100%;object-fit:contain}
.nav-logo span{font-size:1.4rem;font-weight:700;color:#fff;letter-spacing:.02em}
.nav-links{display:flex;gap:26px;list-style:none}
.nav-links a{font-size:.875rem;font-weight:500;color:rgba(255,255,255,.72);transition:color .18s;position:relative}
.nav-links a::after{content:'';position:absolute;bottom:-4px;left:0;width:0;height:2px;background:var(--amber);transition:width .25s;border-radius:1px}
.nav-links a:hover{color:#fff}
.nav-links a:hover::after{width:100%}
.nav-cta{display:flex;gap:9px}
.btn{font-size:.875rem;font-weight:600;border-radius:7px;padding:8px 18px;cursor:pointer;border:none;transition:all .18s;white-space:nowrap;display:inline-flex;align-items:center;gap:7px}
.btn-ghost{background:transparent;color:rgba(255,255,255,.82);border:1.5px solid rgba(255,255,255,.3)}
.btn-ghost:hover{border-color:rgba(255,255,255,.7);color:#fff}
.btn-solid{background:var(--b6);color:#fff}
.btn-solid:hover{background:var(--b7)}
.btn-amber{background:var(--amber);color:#fff}
.btn-amber:hover{background:var(--amber-hover)}
.btn-primary{background:var(--amber);color:#fff;box-shadow:0 4px 14px rgba(245,166,35,.35)}
.btn-primary:hover{background:var(--amber-hover);transform:translateY(-1px);box-shadow:0 6px 20px rgba(245,166,35,.4)}
.btn-outline{background:transparent;color:var(--b6);border:1.5px solid var(--b4);font-size:.875rem;font-weight:600;padding:8px 18px;border-radius:7px;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:7px}
.btn-outline:hover{background:var(--b0)}
.btn-outline-dark{background:transparent;color:var(--b9);border:1.5px solid var(--g1);border-radius:7px;font-weight:600;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:7px;padding:12px 0;font-size:.95rem;justify-content:center}
.btn-outline-dark:hover{background:var(--b0)}

.page-header{background:var(--b9);padding:48px 5% 40px;text-align:center}
.page-header h1{font-size:clamp(1.8rem,3vw,2.4rem);font-weight:700;color:#fff;margin-bottom:8px}
.page-header p{font-size:.93rem;color:rgba(255,255,255,.6)}

.search-section{background:#fff;padding:24px 5%;margin:0 auto;max-width:1100px;border-radius:0 0 14px 14px;box-shadow:0 4px 20px rgba(0,0,0,.06)}
.search-bar{display:flex;gap:12px;max-width:800px;margin:0 auto}
.search-bar .input-wrap{position:relative;flex:1}
.search-bar .input-wrap i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--g4);font-size:1rem;pointer-events:none}
.search-bar .input-wrap input{width:100%;border:1.5px solid var(--g1);border-radius:9px;padding:13px 18px 13px 42px;font-size:1rem;color:var(--g9);background:var(--off);outline:none;font-family:inherit;transition:border-color .18s;box-sizing:border-box}
.search-bar .input-wrap input:focus{border-color:var(--b4)}
.search-bar .input-wrap input::placeholder{color:var(--g4)}

.layout{display:flex;gap:28px;max-width:1200px;margin:0 auto;padding:32px 5%}
.sidebar{flex:0 0 220px}
.sidebar h3{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--b8);margin-bottom:14px}
.cat-list{display:flex;flex-direction:column;gap:6px}
.cat-link{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:9px;font-size:.88rem;font-weight:500;color:var(--g7);transition:all .18s;border:1.5px solid transparent}
.cat-link:hover{background:var(--b0);color:var(--b6);border-color:var(--b2)}
.cat-link.active{background:var(--b0);color:var(--b6);border-color:var(--b4);font-weight:600}
.cat-link .cat-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.cat-link .cat-count{font-size:.76rem;color:var(--g4);margin-left:auto}

.main-content{flex:1;min-width:0}
.result-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:8px}
.result-header h2{font-size:1.15rem;font-weight:600;color:var(--b9)}
.result-header .count{font-size:.88rem;color:var(--g4)}
.result-header .clear-link{font-size:.84rem;color:var(--b6);font-weight:500;display:inline-flex;align-items:center;gap:4px}
.result-header .clear-link:hover{text-decoration:underline}

.worker-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
.worker-card{background:var(--white);border:1.5px solid var(--g1);border-radius:14px;padding:18px 20px;cursor:pointer;transition:all .25s;display:block;box-shadow:0 2px 8px rgba(0,0,0,.04)}
.worker-card:hover{border-color:var(--b4);box-shadow:0 8px 28px rgba(0,0,0,.1);transform:translateY(-3px)}
.w-card-top{display:flex;gap:14px;align-items:flex-start}
.w-avatar{width:56px;height:56px;border-radius:12px;object-fit:cover;flex-shrink:0}
.w-initials{background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:1.1rem;font-weight:700}
.w-meta{flex:1;min-width:0}
.w-name-row{display:flex;justify-content:space-between;align-items:flex-start;gap:8px}
.w-name{font-size:.9rem;font-weight:600;color:var(--b9)}
.w-trade{font-size:.78rem;color:var(--b6);font-weight:500;margin-top:1px}
.w-rating{display:flex;align-items:center;gap:3px;background:var(--b0);padding:3px 7px;border-radius:7px;font-size:.78rem;font-weight:600;color:var(--b8);white-space:nowrap;flex-shrink:0}
.w-rating i{color:#f59e0b;font-size:.68rem}
.w-details-row{display:flex;align-items:center;gap:14px;margin-top:5px;font-size:.78rem;color:var(--g4)}
.w-details-row .w-price{font-weight:600;color:var(--b7);margin-left:auto}
.w-skills{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.w-skill-tag{padding:3px 10px;border-radius:20px;font-size:.73rem;font-weight:500}
.w-skill-tag:nth-child(6n+1){background:#dbeeff;color:#185FA5}
.w-skill-tag:nth-child(6n+2){background:#fef3d0;color:#a07b10}
.w-skill-tag:nth-child(6n+3){background:#fde8d8;color:#b04d1a}
.w-skill-tag:nth-child(6n+4){background:#d6f5e8;color:#1a6852}
.w-skill-tag:nth-child(6n+5){background:#ede8fc;color:#534AB7}
.w-skill-tag:nth-child(6n+6){background:#d4f4f4;color:#0F6E56}
.w-works{margin-top:10px;padding-top:10px;border-top:1px solid var(--g1)}
.w-works-row{display:flex;gap:8px}
.w-work-thumb{width:64px;height:64px;border-radius:9px;background:var(--off);background-size:cover;background-position:center;border:1.5px solid var(--g1);display:flex;align-items:center;justify-content:center;color:var(--g4);font-size:1.1rem;transition:all .2s;cursor:pointer;flex-shrink:0}
.w-work-thumb:hover{transform:scale(1.08);box-shadow:0 4px 12px rgba(0,0,0,.1);border-color:var(--b4)}
.w-work-sample{background:var(--b0);color:var(--b2);border-style:dashed;font-size:1rem}
.w-card-actions{display:flex;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid var(--g1)}
.w-card-actions .btn{flex:1;justify-content:center;padding:9px 0;font-size:.8rem}
.w-card-actions .btn-outline{flex:1;justify-content:center;padding:9px 0;font-size:.8rem}

.pagination{display:flex;justify-content:center;gap:6px;margin-top:36px;flex-wrap:wrap}
.pagination a,.pagination span{padding:8px 14px;border-radius:8px;font-size:.88rem;font-weight:500;border:1.5px solid var(--g1);transition:all .18s}
.pagination a:hover{background:var(--b0);border-color:var(--b4);color:var(--b6)}
.pagination .active{background:var(--b6);color:#fff;border-color:var(--b6)}

.empty-state{text-align:center;padding:60px 20px}
.empty-state i{font-size:3rem;color:var(--g1);margin-bottom:16px}
.empty-state h3{font-size:1.2rem;color:var(--g9);margin-bottom:8px}
.empty-state p{font-size:.9rem;color:var(--g4);max-width:360px;margin:0 auto}

.footer{background:var(--g9);padding:40px 5% 20px;margin-top:48px}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr;gap:36px;margin-bottom:28px}
.f-brand p{font-size:.84rem;color:rgba(255,255,255,.45);line-height:1.65;max-width:280px;margin-top:10px}
.f-brand .brand{display:flex;align-items:center;gap:9px}
.f-brand .brand span{font-size:1.2rem;font-weight:700;color:#fff}
.f-brand .flogo{width:32px;height:32px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px}
.f-brand .flogo img{width:100%;height:100%;object-fit:contain;border-radius:6px}
.f-title{font-size:.78rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:#fff;margin-bottom:10px}
.f-links{list-style:none;display:flex;flex-direction:column;gap:7px}
.f-links a{font-size:.84rem;color:rgba(255,255,255,.45);transition:color .18s;display:inline-flex;align-items:center;gap:6px}
.f-links a:hover{color:var(--b2)}
.f-bottom{border-top:1px solid rgba(255,255,255,.07);padding-top:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.f-bottom p{font-size:.76rem;color:rgba(255,255,255,.3)}
.socials{display:flex;gap:10px}
.soc{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.07);display:flex;align-items:center;justify-content:center;font-size:.9rem;color:rgba(255,255,255,.45);cursor:pointer;transition:all .18s;text-decoration:none}
.soc:hover{background:var(--b6);color:#fff}

@media(max-width:768px){
.layout{flex-direction:column}
.sidebar{flex:none;overflow-x:auto;display:flex;flex-wrap:wrap;gap:10px}
.sidebar h3{width:100;margin-bottom:6px}
.cat-list{flex-direction:row;flex-wrap:wrap;gap:4px}
.cat-link{padding:8px 12px;font-size:.82rem}
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
  <a href="/" class="nav-logo">
    <div class="logo-box"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos Logo"></div>
    <span>KaAyos</span>
  </a>
  <ul class="nav-links">
    <li><a href="/">Home</a></li>
    <li><a href="/services">Services</a></li>
  </ul>
  <div class="nav-cta">
    <a href="/login" class="btn btn-ghost"><i class="fa-regular fa-user" aria-hidden="true"></i> Log In</a>
    <a href="/register" class="btn btn-amber"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign Up Free</a>
  </div>
</nav>

<div class="page-header">
  <h1>Find a Trabahador</h1>
  <p>Browse verified skilled workers in Tuy, Batangas</p>
</div>

<div class="search-section">
  <form class="search-bar" action="/search" method="GET">
    <div class="input-wrap">
      <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
      <input type="text" name="q" placeholder="e.g. leaking pipe, cleaning, painting..." value="{{ $query ?? '' }}" aria-label="Search workers">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Search</button>
  </form>
</div>

<div class="layout">
  <aside class="sidebar">
    <h3>Categories</h3>
    <div class="cat-list">
      <a href="/search{{ $query ? '?q=' . urlencode($query) : '' }}" class="cat-link {{ !$category ? 'active' : '' }}">
        <span class="cat-icon" style="background:var(--b0);color:var(--b6)"><i class="fa-solid fa-grid-2"></i></span>
        All Services
      </a>
      @foreach($categories as $cat)
        @php
          $url = '/search?category=' . urlencode($cat->slug);
          if ($query) $url .= '&q=' . urlencode($query);
        @endphp
        <a href="{{ $url }}" class="cat-link {{ $category === $cat->slug ? 'active' : '' }}">
          <span class="cat-icon" style="background:var(--b0);color:var(--b6)"><i class="fa-solid {{ $cat->icon ?: 'fa-wrench' }}"></i></span>
          {{ $cat->name }}
        </a>
      @endforeach
    </div>
  </aside>

  <div class="main-content">
    <div class="result-header">
      <h2>
        @if($category)
          {{ ucfirst($category) }} Workers
        @else
          All Workers
        @endif
      </h2>
      <div>
        @if($category || $query)
          <a href="/search" class="clear-link"><i class="fa-solid fa-xmark"></i> Clear filters</a>
        @endif
        <span class="count">{{ $workers->total() }} result{{ $workers->total() !== 1 ? 's' : '' }}</span>
      </div>
    </div>

    @if($workers->count() > 0)
      <div class="worker-grid">
        @foreach($workers as $w)
          <a href="{{ route('workers.public.show', $w['id']) }}" class="worker-card">
            <div class="w-card-top">
              @if($w['avatar'])
                <img src="{{ $w['avatar'] }}" alt="{{ $w['name'] }}" class="w-avatar">
              @else
                <div class="w-avatar w-initials">{{ $w['initials'] }}</div>
              @endif
              <div class="w-meta">
                <div class="w-name-row">
                  <div>
                    <div class="w-name">{{ $w['name'] }}</div>
                    <div class="w-trade">{{ $w['category'] }}</div>
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
            @if(!empty($w['works']))
              <div class="w-works">
                <div class="w-works-row">
                  @for($i = 0; $i < min(3, count($w['works'])); $i++)
                    @if($w['works'][$i]['photo'])
                      <div class="w-work-thumb" style="background-image:url('{{ $w['works'][$i]['photo'] }}')" title="{{ $w['works'][$i]['caption'] ?? '' }}"></div>
                    @else
                      <div class="w-work-thumb w-work-sample"><i class="fa-solid fa-camera"></i></div>
                    @endif
                  @endfor
                </div>
              </div>
            @endif
            <div class="w-card-actions">
              <span class="btn btn-outline" onclick="event.stopPropagation();event.preventDefault();window.location.href='{{ route('workers.public.show', $w['id']) }}'"><i class="fa-regular fa-user" aria-hidden="true"></i> View Profile</span>
            </div>
          </a>
        @endforeach
      </div>

      <div class="pagination">
        {{ $workers->links('vendor.pagination.tailwind') }}
      </div>
    @else
      <div class="empty-state">
        <i class="fa-solid fa-user-slash"></i>
        <h3>No workers found</h3>
        <p>Try adjusting your search or category filter to find available workers.</p>
        <a href="/search" class="btn btn-primary" style="margin-top:16px;display:inline-flex"><i class="fa-solid fa-rotate-left"></i> Reset Filters</a>
      </div>
    @endif
  </div>
</div>

<footer class="footer">
  <div class="footer-grid">
    <div class="f-brand">
      <a href="/" class="brand">
        <div class="flogo"><img src="{{ asset('images/logo-gs-removebg-preview.png') }}" alt="KaAyos"></div>
        <span>KaAyos</span>
      </a>
      <p>Connecting homeowners with verified skilled workers in Tuy, Batangas.</p>
    </div>
    <div>
      <div class="f-title">Services</div>
      <ul class="f-links">
        <li><a href="/?category=plumbing">Plumbing</a></li>
        <li><a href="/?category=electrical">Electrical</a></li>
        <li><a href="/#services">View all</a></li>
      </ul>
    </div>
    <div>
      <div class="f-title">Company</div>
      <ul class="f-links">
        <li><a href="/about">About KaAyos</a></li>
        <li><a href="/contact">Contact</a></li>
        <li><a href="/privacy">Privacy Policy</a></li>
        <li><a href="/terms">Terms of Service</a></li>
        <li><a href="/safety">Safety</a></li>
      </ul>
    </div>
  </div>
  <div class="f-bottom">
    <p>&copy; 2026 KaAyos</p>
    <div class="socials">
      <a href="https://facebook.com/kaayos" class="soc" title="Facebook" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
      <a href="mailto:hello@kaayos.com" class="soc" title="Email"><i class="fa-solid fa-envelope"></i></a>
    </div>
  </div>
</footer>

</body>
</html>
