@extends('layouts.public')

@section('title', 'Privacy Policy – KaAyos')
@section('meta_description', 'How KaAyos collects, uses and protects your personal data under the Philippine Data Privacy Act.')

@push('styles')
<style>
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
@media(max-width:768px){
.content{padding:36px 5%}
}
</style>
@endpush

@section('content')

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
@endsection
