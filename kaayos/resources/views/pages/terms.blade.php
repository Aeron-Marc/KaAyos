<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terms, Regulations &amp; Guidelines – KaAyos</title>
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
.content{padding:48px 5%;max-width:860px;margin:0 auto}
.content h2{font-size:1.3rem;font-weight:700;color:var(--b9);margin:36px 0 10px;padding-top:8px}
.content h2:first-child{margin-top:0}
.content h3{font-size:1.05rem;font-weight:600;color:var(--b7);margin:22px 0 8px}
.content p{font-size:.92rem;color:var(--g7);margin-bottom:12px;line-height:1.7}
.content ul,.content ol{margin:0 0 14px 20px}
.content li{font-size:.9rem;color:var(--g7);margin-bottom:6px;line-height:1.6}
.content strong{color:var(--g9)}
.content .updated{margin-top:40px;font-size:.82rem;color:var(--g4);font-style:italic}
.part-label{display:inline-block;background:var(--b0);color:var(--b7);font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;padding:5px 12px;border-radius:999px;margin:36px 0 6px}
.part-label:first-of-type{margin-top:0}
.toc{background:#fff;border:1px solid var(--g1);border-radius:12px;padding:20px 24px;margin:26px 0}
.toc-title{font-size:.78rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--g4);margin-bottom:10px}
.toc ol{margin:0 0 0 18px;columns:2;column-gap:28px}
.toc li{font-size:.85rem;margin-bottom:5px;color:var(--b7)}
.toc a{text-decoration:none}
.toc a:hover{text-decoration:underline}
.callout{background:#fffbeb;border:1px solid #fcd34d;border-left:4px solid var(--amber);border-radius:8px;padding:14px 16px;margin:16px 0}
.callout p{margin:0;font-size:.88rem;color:#78350f}
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
.toc ol{columns:1}
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
    <li><a href="/safety">Safety</a></li>
    <li><a href="/privacy">Privacy</a></li>
  </ul>
  <div class="nav-cta">
    <a href="/login" class="btn btn-ghost"><i class="fa-regular fa-user" aria-hidden="true"></i> Log In</a>
    <a href="/register" class="btn btn-amber"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign Up Free</a>
  </div>
</nav>

<div class="page-header">
  <h1>Terms, Regulations &amp; Guidelines</h1>
  <p>The rules that keep KaAyos safe and fair for every client and worker · Last updated: October 2026</p>
</div>

<div class="content">

<div class="toc">
  <div class="toc-title">On this page</div>
  <ol>
    <li><a href="#acceptance">Acceptance of Terms</a></li>
    <li><a href="#service">The Service</a></li>
    <li><a href="#accounts">User Accounts</a></li>
    <li><a href="#verification">Worker Verification</a></li>
    <li><a href="#bookings">Bookings &amp; Service Agreement</a></li>
    <li><a href="#payments">Payments, Fees &amp; Tips</a></li>
    <li><a href="#cancellation">Cancellation &amp; Rescheduling</a></li>
    <li><a href="#conduct">User Conduct</a></li>
    <li><a href="#reviews">Reviews &amp; Ratings</a></li>
    <li><a href="#ip">Intellectual Property</a></li>
    <li><a href="#liability">Limitation of Liability</a></li>
    <li><a href="#termination">Termination</a></li>
    <li><a href="#rules">Platform Rules &amp; Guidelines</a></li>
    <li><a href="#enforcement">Enforcement &amp; Sanctions</a></li>
    <li><a href="#changes">Changes &amp; Contact</a></li>
  </ol>
</div>

<span class="part-label">Part I — Terms of Service</span>

<h2 id="acceptance">1. Acceptance of Terms</h2>
<p>By accessing or using KaAyos ("the Platform"), you agree to be bound by these Terms, Regulations &amp; Guidelines. If you do not agree, do not use the Platform. These terms apply to all users, including homeowners (Clients) and workers (Workers).</p>

<h2 id="service">2. The Service</h2>
<p>KaAyos is a web-based platform that connects homeowners with verified skilled workers in Tuy, Batangas. The Platform facilitates discovery, communication, and booking — but does not itself provide home services. All service work is performed directly between the Client and the Worker.</p>

<h2 id="accounts">3. User Accounts</h2>
<h3>3.1 Registration</h3>
<p>You must create an account to use the Platform. You agree to provide accurate, current, and complete information and to keep your account credentials confidential.</p>
<h3>3.2 Eligibility</h3>
<p>You must be at least 18 years old to register. By creating an account, you represent that you meet this requirement.</p>
<h3>3.3 Account Responsibility</h3>
<p>You are solely responsible for all activity that occurs under your account. Notify us immediately of any unauthorized use.</p>
<h3>3.4 One Account Per Person</h3>
<p>Creating multiple accounts to manipulate ratings, reviews, bookings, or suggestions is prohibited.</p>

<h2 id="verification">4. Worker Verification</h2>
<p>Workers must submit a valid government-issued ID, police or NBI clearance, barangay clearance, and proof of competency for verification. Submissions are reviewed by an admin, typically within <strong>1–2 business days</strong>. KaAyos reserves the right to reject or remove any worker profile that fails verification or violates these terms.</p>
<p>Verification status is displayed as a badge on the worker's profile but does not constitute a guarantee of work quality or conduct. Workers must keep their submitted documents current and re-upload if a document expires or is rejected.</p>

<h2 id="bookings">5. Bookings &amp; Service Agreement</h2>
<h3>5.1 Creating a Booking</h3>
<p>When a Client sends a booking request, they must agree to the Service Agreement presented at the time of booking. By clicking "I agree," the Client acknowledges and accepts the terms of the specific booking, including the service description, schedule, location, and price.</p>
<h3>5.2 Accepting a Booking</h3>
<p>When a Worker accepts a booking request, they must also agree to the Service Agreement. By clicking "I agree," the Worker confirms their commitment to perform the service as described and at the agreed price and schedule.</p>
<h3>5.3 Mutual Agreement</h3>
<p>A booking becomes a binding agreement only after <strong>both</strong> the Client and the Worker have agreed. The Platform records the timestamp of each party's agreement. Either party may cancel a booking before mutual agreement is reached without penalty.</p>
<h3>5.4 Completion Confirmation</h3>
<p>A job is only marked as completed once <strong>both</strong> the Client and the Worker confirm completion. This dual confirmation protects both parties and triggers the recording of the Worker's earnings.</p>

<h2 id="payments">6. Payments, Fees &amp; Tips</h2>
<p>KaAyos currently does not process payments on the Platform. Clients and Workers agree on payment terms directly. All payment arrangements are solely between the Client and the Worker. KaAyos is not responsible for any disputes regarding payment.</p>
<h3>6.1 Platform Fee</h3>
<p>A platform fee (configured as a percentage of the agreed service price, currently <strong>10%</strong>) is deducted from the Worker's earnings when a job is completed. This fee funds platform maintenance, verification, and support.</p>
<h3>6.2 Tips</h3>
<p>Clients may leave an <strong>optional tip</strong> when confirming job completion (preset amounts or a custom amount). Tips are entirely voluntary and are passed through to the Worker in full — <strong>the platform fee is never deducted from tips</strong>. Tips are recorded on the booking for transparency in both parties' records.</p>

<h2 id="cancellation">7. Cancellation &amp; Rescheduling</h2>
<h3>7.1 By the Client</h3>
<p>Clients may cancel a booking at any time. Frequent cancellations may result in account restrictions.</p>
<h3>7.2 By the Worker</h3>
<p>Workers may cancel a booking through the Platform. Excessive cancellations may affect the worker's rating and account standing.</p>
<h3>7.3 Rescheduling</h3>
<p>Either party may propose a reschedule. The new schedule takes effect only upon acceptance by the other party.</p>
<h3>7.4 No-Shows</h3>
<p>Bookings are automatically cancelled when a Worker does not respond within 24 hours, does not start an accepted job within 60 minutes of the scheduled time, or does not begin work within 2 hours of the scheduled time while en route. Repeated no-shows may lead to suspension.</p>

<h2 id="conduct">8. User Conduct</h2>
<p>You agree to:</p>
<ul>
  <li>Use the Platform only for lawful purposes</li>
  <li>Treat other users with respect and professionalism</li>
  <li>Provide accurate information in your profile and communications</li>
  <li>Complete accepted jobs, or communicate promptly if you cannot</li>
  <li>Not misuse the Platform for spam, fraud, or harassment</li>
  <li>Not move a booking or payment off the Platform to avoid the platform fee</li>
  <li>Not share another user's personal information obtained through the Platform</li>
</ul>
<p>Violation of these rules may result in account suspension or termination.</p>

<h2 id="reviews">9. Reviews &amp; Ratings</h2>
<p>Users may leave one review per completed booking. Reviews must be truthful and based on actual experience. Fake, misleading, or abusive reviews are prohibited. KaAyos reserves the right to remove reviews that violate this policy.</p>
<h3>9.1 Anonymous Reviews</h3>
<p>Clients may opt to post a review <strong>anonymously</strong>. When enabled, the client's name is masked and displayed publicly as "Anonymous." The real name remains visible to platform admins for integrity and moderation purposes, and to the Worker only in non-identifying form.</p>
<h3>9.2 Resolving Issues Before Negative Feedback</h3>
<p>If a Client rates a Worker 1 or 2 stars, the Platform will prompt the Client to message the Worker first so the issue can be resolved directly. This prompt is advisory — the Client may still submit the review.</p>

<h2 id="ip">10. Intellectual Property</h2>
<p>All content on KaAyos, including logos, text, graphics, and software, is the property of KaAyos and is protected by applicable intellectual property laws. You may not reproduce, distribute, or create derivative works without explicit permission.</p>

<h2 id="liability">11. Limitation of Liability</h2>
<p>KaAyos is provided "as is" without any warranty, express or implied. To the fullest extent permitted by law, KaAyos disclaims all liability for any damages arising from your use of the Platform, including but not limited to disputes between Clients and Workers, service quality, property damage, or personal injury. The Platform is a technology intermediary and is not a party to any service agreement between users.</p>

<h2 id="termination">12. Termination</h2>
<p>KaAyos reserves the right to suspend or terminate any account at its sole discretion, with or without notice, for conduct that violates these terms or is otherwise harmful to the Platform or its users.</p>

<span class="part-label">Part II — Platform Rules &amp; Guidelines</span>

<h2 id="rules">13. Platform Rules &amp; Guidelines</h2>
<p>These guidelines exist to keep KaAyos a safe, trustworthy marketplace for Tuy residents. They apply to every account, in-app and out of it.</p>

<h3>13.1 For Clients</h3>
<ul>
  <li>Describe the job accurately — service type, location, schedule, and any hazards — so the Worker can prepare properly.</li>
  <li>Be reachable during the scheduled job window and respond to Worker messages in a timely manner.</li>
  <li>Ensure the site is safe and accessible for the Worker on arrival.</li>
  <li>Pay the Worker directly as agreed, and confirm completion on the Platform only after the work is actually finished.</li>
  <li>Give honest ratings and reviews based on the actual job. Never offer payment or favors in exchange for a rating.</li>
  <li>Report problems through the in-app report feature rather than engaging in public disputes.</li>
</ul>

<h3>13.2 For Workers</h3>
<ul>
  <li>Keep your profile, skills, rates, and availability accurate and up to date.</li>
  <li>Respond to booking requests promptly. Bookings with no response after 24 hours are auto-cancelled.</li>
  <li>Arrive on time and within the agreed schedule; notify the Client as early as possible if delayed.</li>
  <li>Perform the agreed scope of work. If the job is bigger than described, request a scope revision through the Platform instead of changing the price unilaterally.</li>
  <li>Keep your verification documents valid and current.</li>
  <li>Do not ask the Client to pay outside the Platform in a way that bypasses the agreed terms, and do not solicit Clients off-platform for jobs booked through KaAyos.</li>
  <li>Upload job photos and confirm completion through the Platform so your earnings are recorded.</li>
</ul>

<h3>13.3 For Everyone</h3>
<ul>
  <li><strong>Respect first.</strong> Harbullyan, discrimination, threats, and personal attacks are not tolerated in messages, reviews, or reports.</li>
  <li><strong>Privacy.</strong> Do not share or publish another user's phone number, address, or documents.</li>
  <li><strong>No fraud.</strong> Fake bookings, impersonation, stolen photos, and fabricated credentials lead to immediate removal.</li>
  <li><strong>No spam.</strong> Unsolicited promotions, repetitive messaging, and automated abuse are prohibited.</li>
  <li><strong>Safety.</strong> Follow the tips on our <a href="/safety" style="color:var(--b6);text-decoration:underline;">Safety page</a>, and use the report feature for anything that feels unsafe.</li>
</ul>

<div class="callout">
  <p><strong>Shareable profile IDs:</strong> every worker has a unique profile link and QR code (for example, <em>/worker/ID-XXXX</em>). Use it on flyers, social media, and barangay boards so clients can find and book you directly.</p>
</div>

<h2 id="enforcement">14. Enforcement &amp; Sanctions</h2>
<p>KaAyos reviews reports and moderation flags. Depending on severity and history, actions may include:</p>
<ul>
  <li><strong>Warning</strong> — a first, minor violation</li>
  <li><strong>Content removal</strong> — removing reviews, photos, or listings that break these rules</li>
  <li><strong>Rating integrity actions</strong> — removing fraudulent reviews or ratings</li>
  <li><strong>Temporary restriction</strong> — limited booking or messaging ability for a set period</li>
  <li><strong>Suspension or permanent termination</strong> — for fraud, harassment, safety threats, or repeated violations</li>
</ul>
<p>Workers found in violation of verification or conduct rules may lose their Verified badge while under review. Decisions may be appealed by <a href="/contact" style="color:var(--b6);text-decoration:underline;">contacting us</a>.</p>

<h2 id="changes">15. Changes &amp; Contact</h2>
<p>We may update these Terms, Regulations &amp; Guidelines from time to time. Changes will be posted on this page with an updated "Last updated" date. Continued use of the Platform after changes constitutes acceptance of the new terms.</p>
<p>For questions, reports, or appeals, please <a href="/contact" style="color:var(--b6);text-decoration:underline;">contact us</a>. Related policies: <a href="/privacy" style="color:var(--b6);text-decoration:underline;">Privacy Policy</a> and <a href="/safety" style="color:var(--b6);text-decoration:underline;">Safety Guidelines</a>.</p>

<p class="updated">These Terms, Regulations &amp; Guidelines were last updated on October 6, 2026.</p>

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
        <li><a href="/terms">Terms &amp; Guidelines</a></li>
        <li><a href="/safety">Safety</a></li>
      </ul>
    </div>
  </div>
  <div class="f-bottom">
    <p>&copy; 2026 KaAyos</p>
    <div class="socials">
      <a href="https://facebook.com/kaayos" class="soc" title="Facebook" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a>
      <a href="mailto:hello@kaayos.tech" class="soc" title="Email"><i class="fa-solid fa-envelope"></i></a>
    </div>
  </div>
</footer>

</body>
</html>
