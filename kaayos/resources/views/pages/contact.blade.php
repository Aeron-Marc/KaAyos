@extends('layouts.public')

@section('title', 'Contact Us – KaAyos')
@section('meta_description', 'Contact the KaAyos team in Tuy, Batangas – questions, support and partnership inquiries.')

@push('styles')
<style>

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


@media(max-width:768px){
.contact-section{grid-template-columns:1fr}
}
@media(max-width:480px){
.mini-stats{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')

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
        <div class="value"><a href="mailto:hello@kaayos.tech">hello@kaayos.tech</a></div>
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
@endsection
