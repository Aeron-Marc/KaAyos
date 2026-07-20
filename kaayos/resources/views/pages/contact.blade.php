<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us – KaAyos</title>
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

.contact-section{display:grid;grid-template-columns:1fr 1fr;gap:36px;max-width:1000px;margin:0 auto;padding:48px 5%}

.contact-info h2{font-size:1.3rem;font-weight:700;color:var(--b9);margin-bottom:16px}
.contact-info p{font-size:.92rem;color:var(--g7);line-height:1.7;margin-bottom:24px}
.contact-method{display:flex;align-items:flex-start;gap:14px;margin-bottom:20px}
.contact-method .icon{width:44px;height:44px;border-radius:10px;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}
.contact-method .label{font-size:.82rem;color:var(--g4);margin-bottom:2px}
.contact-method .value{font-size:.92rem;font-weight:600;color:var(--g9)}
.contact-method .value a{color:var(--b6);text-decoration:underline}

.contact-form{background:var(--white);border:1.5px solid var(--g1);border-radius:14px;padding:28px 24px;box-shadow:0 2px 12px rgba(0,0,0,.05)}
.contact-form h2{font-size:1.15rem;font-weight:700;color:var(--b9);margin-bottom:18px}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.85rem;font-weight:600;color:var(--g7);margin-bottom:6px}
.form-group input,.form-group textarea,.form-group select{width:100%;padding:11px 14px;border:1.5px solid var(--g1);border-radius:8px;font-size:.92rem;font-family:inherit;color:var(--g9);background:var(--off);outline:none;transition:border-color .18s;box-sizing:border-box}
.form-group input:focus,.form-group textarea:focus{border-color:var(--b4)}
.form-group textarea{resize:vertical;min-height:110px}
.form-group .hint{font-size:.78rem;color:var(--g4);margin-top:4px}

.mini-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:28px}
.mini-stat{background:var(--b0);border-radius:10px;padding:16px;text-align:center}
.mini-stat .num{font-size:1.3rem;font-weight:700;color:var(--b6)}
.mini-stat .lbl{font-size:.75rem;color:var(--g7);margin-top:2px}

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
.contact-section{grid-template-columns:1fr}
.footer-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:480px){
.footer-grid{grid-template-columns:1fr}
.mini-stats{grid-template-columns:1fr}
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
  <h1>Contact Us</h1>
  <p>We'd love to hear from you. Get in touch with the KaAyos team.</p>
</div>

<div class="contact-section">
  <div class="contact-info">
    <h2>Get in Touch</h2>
    <p>Have a question, suggestion, or need support? Reach out to us through any of the channels below, or send us a message using the form.</p>

    <div class="contact-method">
      <div class="icon"><i class="fa-solid fa-envelope"></i></div>
      <div>
        <div class="label">Email</div>
        <div class="value"><a href="mailto:hello@kaayos.com">hello@kaayos.com</a></div>
      </div>
    </div>

    <div class="contact-method">
      <div class="icon"><i class="fa-solid fa-location-dot"></i></div>
      <div>
        <div class="label">Location</div>
        <div class="value">Tuy, Batangas, Philippines</div>
      </div>
    </div>

    <div class="contact-method">
      <div class="icon"><i class="fa-solid fa-building"></i></div>
      <div>
        <div class="label">University</div>
        <div class="value">Batangas State University ARASOF – Nasugbu Campus</div>
      </div>
    </div>

    <div class="contact-method">
      <div class="icon"><i class="fa-solid fa-handshake"></i></div>
      <div>
        <div class="label">PESO Partnership</div>
        <div class="value">In partnership with PESO Tuy, Batangas</div>
      </div>
    </div>

    <div class="mini-stats">
      <div class="mini-stat"><div class="num">{{ number_format($stats['workers']) }}+</div><div class="lbl">Active Workers</div></div>
      <div class="mini-stat"><div class="num">{{ number_format($stats['jobs']) }}+</div><div class="lbl">Jobs Done</div></div>
      <div class="mini-stat"><div class="num">{{ $stats['barangays'] }}</div><div class="lbl">Barangays</div></div>
    </div>
  </div>

  <div class="contact-form">
    <h2>Send Us a Message</h2>
    @if(session('success'))
      <div style="background:#d6f5e8;border:1px solid #8cd4b0;border-radius:10px;padding:14px 16px;margin-bottom:18px;font-size:.88rem;color:#1a6852;display:flex;align-items:center;gap:10px"><i class="fa-solid fa-circle-check" style="font-size:1.1rem"></i> {{ session('success') }}</div>
    @endif
    <form action="/contact" method="POST">
      @csrf
      <div class="form-group">
        <label for="name">Your Name</label>
        <input type="text" id="name" name="name" placeholder="Enter your name" required>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
      </div>
      <div class="form-group">
        <label for="subject">Subject</label>
        <input type="text" id="subject" name="subject" placeholder="What is this about?">
      </div>
      <div class="form-group">
        <label for="message">Message</label>
        <textarea id="message" name="message" placeholder="Write your message here..." required></textarea>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px"><i class="fa-solid fa-paper-plane"></i> Send Message</button>
    </form>
    <p class="hint" style="margin-top:12px;text-align:center">We'll get back to you within 24 hours.</p>
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
