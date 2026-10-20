@extends('layouts.public')

@section('title', 'Safety & Trust – KaAyos')
@section('meta_description', 'How KaAyos keeps homeowners and workers safe with PESO verification, ratings and secure booking.')

@push('styles')
<style>

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


@media(max-width:768px){
.content{padding:36px 5%}
}
</style>
@endpush

@section('content')

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
@endsection
