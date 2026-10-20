@extends('layouts.public')

@section('title', 'About KaAyos – Trusted Home Services')
@section('meta_description', 'Learn how KaAyos connects homeowners in Tuy, Batangas with PESO-verified skilled workers through AI-assisted matching.')

@push('styles')
<style>

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

/* Capstone Team cards — single row, flex centered */
.dev-grid{display:flex;justify-content:center;align-items:stretch;gap:22px;margin:22px 0;flex-wrap:nowrap}
.dev-card{flex:0 1 240px;min-width:0;background:var(--white);border:1.5px solid var(--g1);border-radius:16px;padding:14px;box-shadow:0 2px 8px rgba(4,44,83,.06);transition:transform .25s,box-shadow .25s,border-color .25s}
.dev-card:hover{transform:translateY(-4px);box-shadow:0 10px 28px rgba(4,44,83,.12);border-color:var(--b2)}
.dev-photo{aspect-ratio:1/1;border-radius:20px;background:var(--b0);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;overflow:hidden}
.dev-photo img{width:100%;height:100%;object-fit:cover;border-radius:20px}
.dev-photo-empty i{font-size:1.6rem;color:var(--b4);opacity:.75}
.dev-photo-empty span{font-size:.72rem;color:var(--g4);letter-spacing:.02em}
.dev-info{text-align:center;padding:14px 4px 8px}
.dev-name{font-size:.95rem;font-weight:700;color:var(--b9);line-height:1.3;hyphens:auto}
.dev-role{font-size:.78rem;color:var(--g4);margin-top:4px;line-height:1.35}
@media(max-width:820px){
.dev-grid{flex-wrap:wrap}
.dev-card{flex:1 1 46%}
}
@media(max-width:480px){
.dev-card{flex:1 1 100%}
}

@media(max-width:768px){
.content{padding:36px 5%}
}
</style>
@endpush

@section('content')

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

<h2>The Capstone Team</h2>
<p>KaAyos was designed and built by students of <strong>Batangas State University ARASOF &ndash; Nasugbu Campus</strong>, in partnership with PESO Tuy.</p>

<div class="dev-grid">
  @foreach($developers as $dev)
    @php $photoPath = $dev['photo'] ? public_path('images/team/' . $dev['photo']) : null; @endphp
    <div class="dev-card">
      <div class="dev-photo {{ $dev['photo'] && $photoPath && file_exists($photoPath) ? '' : 'dev-photo-empty' }}">
        @if($dev['photo'] && $photoPath && file_exists($photoPath))
          <img src="{{ asset('images/team/' . $dev['photo']) }}" alt="{{ $dev['name'] }}">
        @else
          <i class="fa-solid fa-camera" aria-hidden="true"></i>
          <span>Photo coming soon</span>
        @endif
      </div>
      <div class="dev-info">
        <div class="dev-name">{{ $dev['name'] }}</div>
        <div class="dev-role">{{ $dev['role'] }}</div>
      </div>
    </div>
  @endforeach
</div>

@if($team->count() > 0)
  <h2>Platform Admins</h2>
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

@endsection
