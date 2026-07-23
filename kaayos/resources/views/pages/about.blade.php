<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About KaAyos – Trusted Home Services</title>
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

.page-header{background:var(--b9);padding:64px 5% 56px;text-align:center;position:relative;overflow:hidden}
.page-header::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle at 20% 50%,rgba(55,138,221,.08) 0%,transparent 50%),radial-gradient(circle at 80% 20%,rgba(55,138,221,.06) 0%,transparent 40%);pointer-events:none}
.page-header>*{position:relative;z-index:1}
.page-header h1{font-size:clamp(2rem,4vw,3rem);font-weight:700;color:#fff;margin-bottom:10px}
.page-header p{font-size:.95rem;color:rgba(255,255,255,.6);max-width:560px;margin:0 auto}

.stats{background:var(--white);padding:32px 5%;display:flex;align-items:center;justify-content:space-around;gap:0;flex-wrap:wrap;box-shadow:0 2px 12px rgba(0,0,0,.05)}
.stat-item{text-align:center;padding:0 28px;flex:1;min-width:120px}
.stat-item+.stat-item{border-left:1px solid var(--g1)}
.stat-num{font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;color:var(--b6);line-height:1}
.stat-label{font-size:.82rem;color:var(--g4);margin-top:4px}
.stat-icon{font-size:1.2rem;color:var(--b2);margin-bottom:8px}

.content{padding:48px 5%;max-width:800px;margin:0 auto}
.content h2{font-size:1.4rem;font-weight:700;color:var(--b9);margin:36px 0 10px}
.content h2:first-child{margin-top:0}
.content h3{font-size:1.05rem;font-weight:600;color:var(--b7);margin:24px 0 8px}
.content p{font-size:.93rem;color:var(--g7);margin-bottom:14px;line-height:1.75}
.content ul{margin:0 0 16px 20px}
.content li{font-size:.9rem;color:var(--g7);margin-bottom:6px;line-height:1.6}
.content strong{color:var(--g9)}

.mission{background:var(--b0);border-radius:14px;padding:32px;margin:28px 0;border-left:4px solid var(--b6)}
.mission h3{color:var(--b9);margin-top:0}
.mission p{font-size:.93rem;color:var(--g7);margin-bottom:0}

.team-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;margin:20px 0}
.team-card{background:var(--white);border:1.5px solid var(--g1);border-radius:12px;padding:24px 18px;text-align:center;transition:transform .25s,box-shadow .25s}
.team-card:hover{transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,.08)}
.team-avatar{width:72px;height:72px;border-radius:50%;object-fit:cover;margin:0 auto 12px}
.team-initials{width:72px;height:72px;border-radius:50%;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:700;margin:0 auto 12px}
.team-name{font-size:.95rem;font-weight:600;color:var(--b9)}
.team-role{font-size:.8rem;color:var(--g4);margin-top:3px}

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
.nav-links{display:none}
.footer-grid{grid-template-columns:1fr 1fr}
.content{padding:36px 5%}
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
    <li><a href="/search">Find Workers</a></li>
  </ul>
  <div class="nav-cta">
    <a href="/login" class="btn btn-ghost"><i class="fa-regular fa-user" aria-hidden="true"></i> Log In</a>
    <a href="/register" class="btn btn-amber"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign Up Free</a>
  </div>
</nav>

<div class="page-header">
  <h1>About KaAyos</h1>
  <p>Connecting homeowners with verified skilled workers in Tuy, Batangas — powered by AI matching and backed by PESO.</p>
</div>

<div class="stats">
  <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-users"></i></div><div class="stat-num">{{ number_format($stats['workers']) }}+</div><div class="stat-label">Active Workers</div></div>
  <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div><div class="stat-num">{{ number_format($stats['jobs']) }}+</div><div class="stat-label">Jobs Completed</div></div>
  <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-map-pin"></i></div><div class="stat-num">{{ $stats['barangays'] }}</div><div class="stat-label">Barangays Covered</div></div>
  <div class="stat-item"><div class="stat-icon"><i class="fa-solid fa-star"></i></div><div class="stat-num">{{ $stats['rating'] }}★</div><div class="stat-label">Avg Rating</div></div>
</div>

<div class="content">

<h2>Our Story</h2>
<p>KaAyos was born from a simple observation: in Tuy, Batangas, skilled workers like plumbers, electricians, and carpenters relied almost entirely on referrals to find work. Homeowners, on the other hand, had no easy way to find verified, trustworthy workers for their home repair and maintenance needs — they had to ask around, hope for recommendations, and take a chance on strangers.</p>
<p>We built KaAyos to bridge that gap. By partnering with the Public Employment Service Office (PESO) of Tuy, Batangas, we created a platform where every worker is government-ID verified, rated by real clients, and matched to jobs using AI — not luck.</p>

<div class="mission">
  <h3>Our Mission</h3>
  <p>To empower Filipino skilled workers with a professional platform to grow their livelihood, while giving homeowners a safe, fast, and transparent way to find trusted service professionals in their community.</p>
</div>

<h2>How We're Different</h2>
<ul>
  <li><strong>PESO Partnership</strong> — Every worker is verified through the Public Employment Service Office, ensuring legitimate and vetted professionals.</li>
  <li><strong>AI-Assisted Matching</strong> — Our system recommends the most suitable workers based on skill match, rating, and proximity.</li>
  <li><strong>Community Ratings</strong> — Real feedback from real neighbors helps you choose with confidence.</li>
  <li><strong>Local First</strong> — We focus on Tuy, Batangas first, building a hyper-local service that understands the community.</li>
</ul>

<h2>Our Team</h2>
<p>KaAyos is developed as a capstone project by students of Batangas State University ARASOF – Nasugbu Campus.</p>

@if($team->count() > 0)
  <div class="team-grid">
    @foreach($team as $member)
      <div class="team-card">
        @if($member['avatar'])
          <img src="{{ $member['avatar'] }}" alt="{{ $member['name'] }}" class="team-avatar">
        @else
          <div class="team-initials">{{ $member['initials'] }}</div>
        @endif
        <div class="team-name">{{ $member['name'] }}</div>
        <div class="team-role">Admin</div>
      </div>
    @endforeach
  </div>
@endif

<h2>Partnership with PESO</h2>
<p>KaAyos is proud to partner with the <strong>Public Employment Service Office (PESO) of Tuy, Batangas</strong>. This partnership ensures that every worker on our platform has submitted valid government identification and barangay clearance before their profile is approved. PESO accreditation is displayed as a badge on worker profiles, giving homeowners peace of mind.</p>

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
