<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy – KaAyos</title>
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
.page-header{background:var(--b9);padding:64px 5% 56px;text-align:center;position:relative;overflow:hidden}
.page-header::before{content:'';position:absolute;inset:0;background-image:radial-gradient(circle at 20% 50%,rgba(55,138,221,.08) 0%,transparent 50%),radial-gradient(circle at 80% 20%,rgba(55,138,221,.06) 0%,transparent 40%);pointer-events:none}
.page-header>*{position:relative;z-index:1}
.page-header h1{font-size:clamp(2rem,4vw,3rem);font-weight:700;color:#fff;margin-bottom:10px}
.page-header p{font-size:.95rem;color:rgba(255,255,255,.6)}
.content{padding:48px 5%;max-width:800px;margin:0 auto}
.content h2{font-size:1.3rem;font-weight:700;color:var(--b9);margin:36px 0 10px}
.content h2:first-child{margin-top:0}
.content p{font-size:.92rem;color:var(--g7);margin-bottom:12px;line-height:1.7}
.content ul{margin:0 0 14px 20px}
.content li{font-size:.9rem;color:var(--g7);margin-bottom:6px;line-height:1.6}
.content strong{color:var(--g9)}
.content .updated{margin-top:40px;font-size:.82rem;color:var(--g4);font-style:italic}
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
  </ul>
  <div class="nav-cta">
    <a href="/login" class="btn btn-ghost"><i class="fa-regular fa-user" aria-hidden="true"></i> Log In</a>
    <a href="/register" class="btn btn-amber"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign Up Free</a>
  </div>
</nav>

<div class="page-header">
  <h1>Privacy Policy</h1>
  <p>Last updated: July 2026</p>
</div>

<div class="content">

<h2>1. Information We Collect</h2>
<p>When you create an account, we collect personal information including your name, email address, phone number, and barangay/location. Workers are also required to submit a valid government-issued ID and barangay clearance for verification purposes. We may collect profile photos, portfolio images, and other information you voluntarily provide.</p>

<h2>2. How We Use Your Information</h2>
<p>We use your information to:</p>
<ul>
  <li>Create and manage your account</li>
  <li>Facilitate bookings and communication between clients and workers</li>
  <li>Verify worker identities and credentials</li>
  <li>Improve our AI matching and recommendation system</li>
  <li>Send service-related notifications and updates</li>
  <li>Respond to your inquiries and support requests</li>
</ul>

<h2>3. Information Sharing</h2>
<p>We do not sell your personal information. We may share information with:</p>
<ul>
  <li><strong>Other Users:</strong> Your name, rating, and work portfolio (for workers) are visible to other users of the Platform.</li>
  <li><strong>PESO Tuy, Batangas:</strong> Worker verification data is shared with PESO for accreditation purposes.</li>
  <li><strong>Service Providers:</strong> We may engage third-party services for hosting, analytics, and email delivery, who are bound by confidentiality agreements.</li>
</ul>

<h2>4. Data Security</h2>
<p>We implement reasonable security measures including encryption of sensitive data, secure server infrastructure, and access controls. However, no online platform can guarantee absolute security. You are responsible for keeping your account credentials confidential.</p>

<h2>5. Data Retention</h2>
<p>We retain your information for as long as your account is active. If you delete your account, we will remove your personal data within a reasonable period, except where retention is required by law or for legitimate business purposes (e.g., transaction records).</p>

<h2>6. Your Rights</h2>
<p>You have the right to:</p>
<ul>
  <li>Access the personal data we hold about you</li>
  <li>Request correction of inaccurate data</li>
  <li>Request deletion of your account and data</li>
  <li>Opt out of non-essential communications</li>
</ul>
<p>To exercise these rights, please <a href="/contact" style="color:var(--b6);text-decoration:underline;">contact us</a>.</p>

<h2>7. Cookies</h2>
<p>We use essential cookies for authentication and platform functionality. We do not use tracking cookies for advertising purposes. By using the Platform, you consent to our use of essential cookies.</p>

<h2>8. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated "Last updated" date. Continued use of the Platform after changes constitutes acceptance of the updated policy.</p>

<h2>9. Contact</h2>
<p>For questions about this Privacy Policy, please <a href="/contact" style="color:var(--b6);text-decoration:underline;">contact us</a>.</p>

<p class="updated">This Privacy Policy was last updated on July 15, 2026.</p>

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