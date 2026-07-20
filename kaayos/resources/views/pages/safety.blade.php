<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Safety Guidelines – KaAyos</title>
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

.content{padding:48px 5%;max-width:800px;margin:0 auto}
.content h2{font-size:1.3rem;font-weight:700;color:var(--b9);margin:36px 0 10px}
.content h2:first-child{margin-top:0}
.content h3{font-size:1.05rem;font-weight:600;color:var(--b7);margin:24px 0 8px}
.content p{font-size:.92rem;color:var(--g7);margin-bottom:12px;line-height:1.7}
.content ul{margin:0 0 14px 20px}
.content li{font-size:.9rem;color:var(--g7);margin-bottom:6px;line-height:1.6}
.content strong{color:var(--g9)}

.safety-card{background:var(--white);border:1.5px solid var(--g1);border-radius:12px;padding:24px;margin-bottom:16px;transition:box-shadow .2s}
.safety-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.06)}
.safety-card .card-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.15rem;margin-bottom:12px}
.safety-card h3{font-size:1rem;font-weight:600;color:var(--b9);margin-bottom:8px}
.safety-card p{font-size:.88rem;color:var(--g7);line-height:1.65;margin-bottom:0}
.safety-card ul{margin:8px 0 0 18px}
.safety-card li{font-size:.85rem;color:var(--g7);margin-bottom:4px}

.alert-box{background:#fef3d0;border:1px solid #f5d98a;border-radius:10px;padding:18px 20px;margin-bottom:24px;display:flex;gap:12px;align-items:flex-start}
.alert-box i{color:#b8860b;font-size:1.3rem;flex-shrink:0;margin-top:2px}
.alert-box p{font-size:.88rem;color:var(--g9);margin:0}

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
  <h1>Safety Guidelines</h1>
  <p>Your safety is our priority. Follow these guidelines for a secure and positive experience.</p>
</div>

<div class="content">

<div class="alert-box">
  <i class="fa-solid fa-circle-exclamation"></i>
  <p><strong>Important:</strong> KaAyos is a platform that connects clients and workers. We facilitate discovery, communication, and booking, but we are not a party to any service agreement. Always exercise caution and good judgment when transacting with others.</p>
</div>

<h2>For Homeowners</h2>

<div class="safety-card">
  <div class="card-icon" style="background:#dbeeff;color:#185FA5"><i class="fa-solid fa-id-card"></i></div>
  <h3>Check Worker Verification</h3>
  <p>Look for the Verified badge and PESO accreditation on worker profiles. Verified workers have submitted valid government ID and barangay clearance. While verification improves trust, always use your best judgment.</p>
</div>

<div class="safety-card">
  <div class="card-icon" style="background:#d6f5e8;color:#1a6852"><i class="fa-solid fa-comments"></i></div>
  <h3>Communicate Within the Platform</h3>
  <p>Use KaAyos's in-app messaging for all communications. This keeps a record of your conversations and helps us assist you if any issues arise.</p>
</div>

<div class="safety-card">
  <div class="card-icon" style="background:#fef3d0;color:#a07b10"><i class="fa-solid fa-file-signature"></i></div>
  <h3>Agree on Terms Before Work Starts</h3>
  <p>Clearly discuss the scope of work, schedule, and price before the worker arrives. Use the Service Agreement feature in the booking flow so both parties are on the same page.</p>
</div>

<div class="safety-card">
  <div class="card-icon" style="background:#fde8d8;color:#b04d1a"><i class="fa-solid fa-shield-halved"></i></div>
  <h3>Personal Safety</h3>
  <ul>
    <li>Ensure another adult is present at home when a worker arrives, if possible.</li>
    <li>Secure valuables and pets before work begins.</li>
    <li>Verify the worker's identity by matching their profile photo.</li>
    <li>Do not share personal financial information like bank accounts or credit cards.</li>
  </ul>
</div>

<div class="safety-card">
  <div class="card-icon" style="background:#ede8fc;color:#534AB7"><i class="fa-solid fa-star"></i></div>
  <h3>Leave Honest Reviews</h3>
  <p>After a booking, leave a truthful review. Your feedback helps other homeowners make informed decisions and helps workers build their reputation.</p>
</div>

<h2>For Workers</h2>

<div class="safety-card">
  <div class="card-icon" style="background:#dbeeff;color:#185FA5"><i class="fa-solid fa-user-check"></i></div>
  <h3>Maintain a Professional Profile</h3>
  <p>Keep your profile complete with accurate information, clear photos of your work, and up-to-date skills. A complete profile attracts more clients and builds trust.</p>
</div>

<div class="safety-card">
  <div class="card-icon" style="background:#d4f4f4;color:#0F6E56"><i class="fa-solid fa-calendar-check"></i></div>
  <h3>Confirm Bookings Through the Platform</h3>
  <p>Always confirm bookings and communicate through KaAyos. This ensures there is a record of the agreement, schedule, and scope of work.</p>
</div>

<div class="safety-card">
  <div class="card-icon" style="background:#fde0de;color:#A32D2D"><i class="fa-solid fa-triangle-exclamation"></i></div>
  <h3>Personal Safety</h3>
  <ul>
    <li>Inform a family member or friend about your job location and expected return time.</li>
    <li>If a situation feels unsafe, leave immediately and report through the platform.</li>
    <li>Do not accept payments outside of the agreed terms.</li>
    <li>Keep your emergency contact information updated in your profile.</li>
  </ul>
</div>

<h2>Reporting Concerns</h2>
<p>If you experience or witness any safety issues, harassment, suspicious behavior, or policy violations, please report them immediately. You can:</p>
<ul>
  <li>Use the in-app reporting feature on your dashboard</li>
  <li><a href="/contact" style="color:var(--b6);text-decoration:underline;">Contact our support team</a> via email</li>
</ul>
<p>All reports are reviewed promptly and kept confidential. KaAyos reserves the right to suspend or remove users who violate our safety policies.</p>

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
