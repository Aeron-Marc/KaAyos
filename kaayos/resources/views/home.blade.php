<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Trusted Home Services</title>
<link rel="icon" href="../images/KaAyos_logo.jpeg">
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

/* NAV */
.nav{background:var(--b9);padding:0 5%;display:flex;align-items:center;justify-content:space-between;height:60px;position:sticky;top:0;z-index:100}
.nav-logo{display:flex;align-items:center;gap:9px}
.logo-box{width:50px;height:50px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:17px}
.logo-box img{width:100%;height:100%;object-fit:contain;}
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
.btn-lg{font-size:1rem;padding:12px 26px;border-radius:8px}
.btn-outline{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.45);font-size:1rem;font-weight:600;padding:11px 24px;border-radius:8px;cursor:pointer;transition:all .18s;display:inline-flex;align-items:center;gap:8px}
.btn-outline:hover{border-color:#fff}

/* HERO — single column, centered */
.hero{
    background:var(--b9);
    padding:90px 5% 80px;
    text-align:center;
    display:flex;
    flex-direction:column;
    align-items:center;
}
.hero-tag{display:inline-flex;align-items:center;gap:7px;background:rgba(55,138,221,.18);border:1px solid rgba(55,138,221,.35);border-radius:100px;padding:5px 13px;margin-bottom:20px}
.hero-tag .dot{width:6px;height:6px;border-radius:50%;background:var(--b4);animation:pulse 1.8s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.hero-tag span{font-size:.75rem;font-weight:600;color:var(--b2);letter-spacing:.06em;text-transform:uppercase}
.hero h1{font-size:clamp(2.6rem,5vw,4rem);font-weight:700;line-height:1.08;color:#fff;margin-bottom:16px;letter-spacing:-.01em;max-width:640px}
.hero h1 em{font-style:normal;color:var(--b4)}
.hero-sub{font-size:1.05rem;color:rgba(255,255,255,.65);max-width:520px;margin-bottom:36px;line-height:1.75}
.hero-actions{display:flex;gap:12px;flex-wrap:wrap;justify-content:center}

/* SEARCH */
.search-section{background:#fff;padding:28px 5%;box-shadow:0 2px 16px rgba(0,0,0,.07)}
.search-label{font-size:.8rem;font-weight:600;color:var(--b8);letter-spacing:.07em;text-transform:uppercase;margin-bottom:10px}
.search-bar{display:flex;gap:9px;max-width:740px}
.search-bar input{flex:1;border:1.5px solid var(--g1);border-radius:7px;padding:11px 16px;font-size:.95rem;color:var(--g9);background:var(--off);outline:none;font-family:inherit;transition:border-color .18s}
.search-bar input:focus{border-color:var(--b4)}
.search-bar input::placeholder{color:var(--g4)}
.loc-input{width:200px!important;flex:none!important}

/* SECTIONS */
.section{padding:60px 5%}
.section-alt{background:#fff}
.eyebrow{font-size:.76rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--b6);margin-bottom:7px}
.sec-title{font-size:clamp(1.7rem,3vw,2.3rem);font-weight:700;color:var(--b9);line-height:1.12;margin-bottom:8px}
.sec-sub{font-size:.93rem;color:var(--g4);max-width:500px}
.sec-header{margin-bottom:36px}

/* SERVICES */
.service-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px}
.service-card{background:var(--off);border:1.5px solid var(--g1);border-radius:12px;padding:22px 16px;text-align:center;cursor:pointer;transition:all .2s;display:block}
.service-card:hover{border-color:var(--b4);background:var(--b0);transform:translateY(-2px)}
.svc-icon{width:50px;height:50px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:1.35rem}
.ic-b{background:#dbeeff;color:#185FA5}
.ic-y{background:#fef3d0;color:#a07b10}
.ic-o{background:#fde8d8;color:#b04d1a}
.ic-g{background:#d6f5e8;color:#1a6852}
.ic-p{background:#ede8fc;color:#534AB7}
.ic-s{background:#e4eaf0;color:#3D4A56}
.ic-t{background:#d4f4f4;color:#0F6E56}
.ic-r{background:#fde0de;color:#A32D2D}
.svc-name{font-size:.95rem;font-weight:600;color:var(--b9)}
.svc-sub{font-size:.76rem;color:var(--g4);margin-top:3px}

/* TRUST */
.trust{background:var(--b9);padding:24px 5%;display:flex;align-items:center;justify-content:space-around;gap:16px;flex-wrap:wrap}
.trust-item{display:flex;align-items:center;gap:12px}
.trust-ico{width:40px;height:40px;background:rgba(255,255,255,.08);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--b2);font-size:1.1rem;flex-shrink:0}
.trust-item strong{display:block;font-size:.88rem;font-weight:600;color:#fff}
.trust-item span{font-size:.74rem;color:rgba(255,255,255,.5)}

/* HOW IT WORKS */
.steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px}
.step{background:var(--b0);border-left:3px solid var(--b4);border-radius:0 12px 12px 0;padding:24px 22px}
.step-n{font-size:2.4rem;font-weight:700;color:rgba(55,138,221,.2);line-height:1;margin-bottom:12px}
.step-icon{width:40px;height:40px;background:var(--b6);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;margin-bottom:12px}
.step-title{font-size:1rem;font-weight:600;color:var(--b9);margin-bottom:6px}
.step-desc{font-size:.87rem;color:var(--g7);line-height:1.65}

/* JOIN */
.join{background:var(--b8);padding:60px 5%;display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center}
.join-text h2{font-size:clamp(1.8rem,3vw,2.6rem);font-weight:700;color:#fff;margin-bottom:14px;line-height:1.12}
.join-text p{color:rgba(255,255,255,.7);font-size:.97rem;line-height:1.75;max-width:420px;margin-bottom:28px}
.perks{display:flex;flex-direction:column;gap:12px}
.perk{display:flex;align-items:flex-start;gap:12px;background:rgba(255,255,255,.07);border-radius:10px;padding:14px 16px}
.perk-ico{width:38px;height:38px;background:rgba(255,255,255,.12);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff;flex-shrink:0}
.perk strong{display:block;font-weight:600;color:#fff;font-size:.92rem}
.perk span{font-size:.8rem;color:rgba(255,255,255,.6)}

/* FAQ */
.faq-list{max-width:680px}
.faq-item{border-bottom:1px solid var(--g1);padding:18px 0}
.faq-q{display:flex;justify-content:space-between;align-items:center;cursor:pointer;font-weight:600;color:var(--b9);font-size:.96rem;gap:14px}
.faq-q:hover{color:var(--b6)}
.faq-chev{color:var(--b4);transition:transform .25s;flex-shrink:0;font-size:.9rem}
.faq-item.open .faq-chev{transform:rotate(180deg)}
.faq-a{max-height:0;overflow:hidden;transition:max-height .3s ease,padding .3s;font-size:.9rem;color:var(--g7);line-height:1.7}
.faq-item.open .faq-a{max-height:180px;padding-top:10px}

/* FOOTER */
.footer{background:var(--g9);padding:48px 5% 24px}
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

/* RESPONSIVE */
@media(max-width:768px){
.nav-links{display:none}
.hero{padding:64px 5% 56px}
.hero h1{font-size:2.4rem}
.join{grid-template-columns:1fr;gap:28px}
.footer-grid{grid-template-columns:1fr 1fr}
.search-bar{flex-wrap:wrap}
.loc-input{width:100%!important}
.trust{justify-content:flex-start;gap:18px}
}
@media(max-width:480px){
.footer-grid{grid-template-columns:1fr}
.service-grid{grid-template-columns:repeat(2,1fr)}
.hero h1{font-size:2rem}
}
</style>
</head>
<body>

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
    <a href="/register" class="btn btn-solid"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign Up Free</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-tag"><div class="dot"></div><span>Tuy, Batangas &amp; nearby areas</span></div>
  <h1>Find a Trusted <em>Trabahador</em> in Minutes</h1>
  <p class="hero-sub">KaAyos connects homeowners with verified skilled workers matched by skill, rating, and location. No more endless referrals.</p>
  <div class="hero-actions">
    <a href="/register" class="btn btn-solid btn-lg"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Hire a Worker Now</a>
    <a href="#join" class="btn-outline"><i class="fa-solid fa-hammer" aria-hidden="true"></i> Join as Trabahador</a>
  </div>
</section>

<!-- SEARCH -->
<div class="search-section">
  <div class="search-label">What do you need fixed?</div>
  <div class="search-bar">
    <input type="text" placeholder="e.g. leaking pipe, broken circuit, painting…" aria-label="Service type">
    <input type="text" class="loc-input" placeholder="Your barangay" aria-label="Location">
    <a href="/search" class="btn btn-solid btn-lg"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Find Workers</a>
  </div>
</div>

<!-- SERVICES -->
<section class="section" id="services">
  <div class="sec-header">
    <div class="eyebrow">Our Services</div>
    <h2 class="sec-title">Every Trade, One Platform</h2>
    <p class="sec-sub">Browse verified workers across all major home service categories.</p>
  </div>
  <div class="service-grid">
    <a href="/services?category=plumbing" class="service-card"><div class="svc-icon ic-b"><i class="fa-solid fa-wrench"></i></div><div class="svc-name">Plumbing</div><div class="svc-sub">Pipes, leaks &amp; fixtures</div></a>
    <a href="/services?category=electrical" class="service-card"><div class="svc-icon ic-y"><i class="fa-solid fa-bolt"></i></div><div class="svc-name">Electrical</div><div class="svc-sub">Wiring, outlets &amp; panels</div></a>
    <a href="/services?category=carpentry" class="service-card"><div class="svc-icon ic-o"><i class="fa-solid fa-screwdriver-wrench"></i></div><div class="svc-name">Carpentry</div><div class="svc-sub">Furniture &amp; woodwork</div></a>
    <a href="/services?category=cleaning" class="service-card"><div class="svc-icon ic-g"><i class="fa-solid fa-broom"></i></div><div class="svc-name">Cleaning</div><div class="svc-sub">Deep clean &amp; maintenance</div></a>
    <a href="/services?category=painting" class="service-card"><div class="svc-icon ic-p"><i class="fa-solid fa-paint-roller"></i></div><div class="svc-name">Painting</div><div class="svc-sub">Interior &amp; exterior</div></a>
    <a href="/services?category=roofing" class="service-card"><div class="svc-icon ic-s"><i class="fa-solid fa-house"></i></div><div class="svc-name">Roofing</div><div class="svc-sub">Repair &amp; waterproofing</div></a>
    <a href="/services?category=aircon" class="service-card"><div class="svc-icon ic-t"><i class="fa-solid fa-snowflake"></i></div><div class="svc-name">Aircon</div><div class="svc-sub">Cleaning &amp; repair</div></a>
    <a href="/services?category=hauling" class="service-card"><div class="svc-icon ic-r"><i class="fa-solid fa-truck"></i></div><div class="svc-name">Hauling</div><div class="svc-sub">Junk removal &amp; moving</div></a>
  </div>
</section>

<!-- TRUST -->
<div class="trust">
  <div class="trust-item"><div class="trust-ico"><i class="fa-solid fa-id-card" aria-hidden="true"></i></div><div><strong>ID-Verified Workers</strong><span>Valid ID &amp; clearance required</span></div></div>
  <div class="trust-item"><div class="trust-ico"><i class="fa-solid fa-robot" aria-hidden="true"></i></div><div><strong>AI-Assisted Matching</strong><span>Best worker for your job</span></div></div>
  <div class="trust-item"><div class="trust-ico"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></div><div><strong>Geolocation-Based</strong><span>Nearest available worker</span></div></div>
  <div class="trust-item"><div class="trust-ico"><i class="fa-solid fa-star" aria-hidden="true"></i></div><div><strong>Rated &amp; Reviewed</strong><span>Read real feedback first</span></div></div>
</div>

<!-- HOW IT WORKS -->
<section class="section section-alt" id="how-it-works">
  <div class="sec-header">
    <div class="eyebrow">How It Works</div>
    <h2 class="sec-title">Booked in Four Steps</h2>
    <p class="sec-sub">From posting your job to getting it done — fast and simple.</p>
  </div>
  <div class="steps">
    <div class="step">
      <div class="step-n">01</div>
      <div class="step-icon"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></div>
      <div class="step-title">Post Your Job</div>
      <p class="step-desc">Describe what needs fixing — type, location, and when you need it done.</p>
    </div>
    <div class="step">
      <div class="step-n">02</div>
      <div class="step-icon"><i class="fa-solid fa-robot" aria-hidden="true"></i></div>
      <div class="step-title">AI Finds Matches</div>
      <p class="step-desc">Our system recommends verified, nearby workers ranked by rating and distance.</p>
    </div>
    <div class="step">
      <div class="step-n">03</div>
      <div class="step-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></div>
      <div class="step-title">Chat &amp; Book</div>
      <p class="step-desc">Message your worker, confirm pricing, and lock in the schedule in-app.</p>
    </div>
    <div class="step">
      <div class="step-n">04</div>
      <div class="step-icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>
      <div class="step-title">Rate &amp; Done</div>
      <p class="step-desc">Leave a review after the job. Your feedback helps the whole community.</p>
    </div>
  </div>
</section>

<!-- JOIN AS WORKER -->
<section class="join" id="join">
  <div class="join-text">
    <h2>Are You a Skilled Trabahador?</h2>
    <p>KaAyos helps Filipino workers earn more, reach more clients, and build a professional reputation — without relying on referrals alone.</p>
    <a href="/register/worker" class="btn btn-solid btn-lg"><i class="fa-solid fa-hammer" aria-hidden="true"></i> Register as a Worker</a>
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
  <div class="sec-header">
    <div class="eyebrow">FAQ</div>
    <h2 class="sec-title">Common Questions</h2>
  </div>
  <div class="faq-list">
    <div class="faq-item">
      <div class="faq-q" onclick="toggleFaq(this)">Is KaAyos free to use as a homeowner?<i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">Yes. Creating an account, browsing workers, and booking jobs is completely free. You only pay the worker directly.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q" onclick="toggleFaq(this)">How are workers verified?<i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">Every worker submits a valid government-issued ID and barangay clearance before their profile goes live. Workers who pass appear with a Verified badge.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q" onclick="toggleFaq(this)">How does AI matching work?<i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">When you post a job, KaAyos ranks candidates by distance, skill match, and community rating — so you see the most suitable workers first.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q" onclick="toggleFaq(this)">What areas does KaAyos cover?<i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">KaAyos currently serves all 42 barangays of Tuy, Batangas, with plans to expand to neighboring municipalities.</div>
    </div>
    <div class="faq-item">
      <div class="faq-q" onclick="toggleFaq(this)">Can I register as both a homeowner and a worker?<i class="fa-solid fa-chevron-down faq-chev"></i></div>
      <div class="faq-a">Yes. One account supports both roles. Switch between them from your dashboard.</div>
    </div>
  </div>
</section>

<!-- FOOTER -->
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
        <li><a href="#how-it-works"><i class="fa-solid fa-list-ol fa-fw" aria-hidden="true"></i> How It Works</a></li>
        <li><a href="/register"><i class="fa-solid fa-hammer fa-fw" aria-hidden="true"></i> Join as Worker</a></li>
        <li><a href="#faq"><i class="fa-solid fa-circle-question fa-fw" aria-hidden="true"></i> FAQ</a></li>
        <li><a href="/contact"><i class="fa-solid fa-envelope fa-fw" aria-hidden="true"></i> Contact</a></li>
        <li><a href="/privacy"><i class="fa-solid fa-shield fa-fw" aria-hidden="true"></i> Privacy Policy</a></li>
        <li><a href="/terms"><i class="fa-solid fa-file-lines fa-fw" aria-hidden="true"></i> Terms of Service</a></li>
      </ul>
    </div>
  </div>
  <div class="f-bottom">
    <!-- <p>© 2025 KaAyos — Capstone project by Salanguit, Formentos &amp; Briones · Batangas State University ARASOF – Nasugbu Campus.</p> -->
    <div class="socials">
      <div class="soc" title="Facebook"><i class="fa-brands fa-facebook-f"></i></div>
      <div class="soc" title="Email"><i class="fa-solid fa-envelope"></i></div>
    </div>
  </div>
</footer>

<script>
function toggleFaq(el){
  const item=el.closest('.faq-item'),open=item.classList.contains('open');
  document.querySelectorAll('.faq-item.open').forEach(i=>i.classList.remove('open'));
  if(!open)item.classList.add('open');
}
</script>
</body>
</html>