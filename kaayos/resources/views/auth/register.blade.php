<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Register</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#042C53;--ink-2:#0C447C;--signal:#1A6FC4;--signal-2:#15598F;--sky:#EAF3FC;--amber:#F2A33D;--amber-2:#D9842A;--paper:#FBF9F5;--paper-2:#F1ECE2;--graphite:#202B36;--slate:#6E7A88;--line:#E3DED2;--danger:#A32D2D;--danger-bg:#FBEAEA}
html,body{height:100%}
body{font-family:'Inter',sans-serif;color:var(--graphite);background:linear-gradient(135deg,var(--ink) 0%,#062d52 50%,var(--ink-2) 100%);position:relative;overflow:hidden;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}

body::before{content:'';position:fixed;inset:0;background-image:radial-gradient(circle at 15% 40%,rgba(55,138,221,.1) 0%,transparent 50%),radial-gradient(circle at 85% 60%,rgba(245,166,35,.06) 0%,transparent 45%);pointer-events:none;z-index:0}

.mouse-glow{position:fixed;inset:0;pointer-events:none;z-index:0;background:radial-gradient(circle 350px at var(--mx,50%) var(--my,50%),rgba(55,138,221,.12),transparent 70%);opacity:0;transition:opacity .6s ease}
.mouse-glow.active{opacity:1}

.hero-icons-floating{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden}
.hero-icon-f{position:absolute;color:rgba(255,255,255,.06);animation:heroDrift 8s ease-in-out infinite;cursor:default;pointer-events:auto;transition:color .4s,text-shadow .4s;translate:0 0}
.hero-icon-f:hover{color:rgba(255,255,255,.35);text-shadow:0 0 18px rgba(255,255,255,.45),0 0 40px rgba(55,138,221,.3);transform:scale(1.15) translateY(-6px) !important}

.hero-icon-f:nth-child(1){top:5%;left:3%;animation-delay:0s;font-size:clamp(2.2rem,4.5vw,5rem)}
.hero-icon-f:nth-child(2){top:28%;left:16%;animation-delay:2s;font-size:clamp(1.6rem,3vw,3.5rem)}
.hero-icon-f:nth-child(3){top:48%;left:19%;animation-delay:4s;font-size:clamp(1.8rem,3.5vw,4rem)}
.hero-icon-f:nth-child(4){top:65%;left:2%;animation-delay:6s;font-size:clamp(2rem,4vw,4.5rem)}
.hero-icon-f:nth-child(5){top:82%;left:4%;animation-delay:8s;font-size:clamp(1.4rem,2.8vw,3.2rem)}
.hero-icon-f:nth-child(6){bottom:4%;left:3%;animation-delay:1.5s;font-size:clamp(1.7rem,3.2vw,3.8rem)}

.hero-icon-f:nth-child(7){top:8%;right:4%;animation-delay:1.2s;font-size:clamp(2rem,4vw,4.5rem)}
.hero-icon-f:nth-child(8){top:30%;right:16%;animation-delay:3.5s;font-size:clamp(1.5rem,2.8vw,3.2rem)}
.hero-icon-f:nth-child(9){top:52%;right:19%;animation-delay:5.5s;font-size:clamp(1.9rem,3.8vw,4.2rem)}
.hero-icon-f:nth-child(10){top:70%;right:3%;animation-delay:7.5s;font-size:clamp(1.6rem,3vw,3.5rem)}
.hero-icon-f:nth-child(11){top:88%;right:6%;animation-delay:4.8s;font-size:clamp(1.3rem,2.5vw,3rem)}
.hero-icon-f:nth-child(12){bottom:3%;right:4%;animation-delay:3s;font-size:clamp(1.8rem,3.5vw,4rem)}

@keyframes heroDrift{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-20px) rotate(3deg)}}

@media(prefers-reduced-motion:reduce){.hero-icon-f{animation:none !important;transition:none !important}.mouse-glow{transition:none !important}}

.auth-page{width:100%;max-width:520px;display:flex;flex-direction:column;align-items:center;position:relative;z-index:1}

.auth-card{width:100%;background:#fff;border-radius:18px;padding:36px 32px 32px;box-shadow:0 12px 40px rgba(0,0,0,.25),0 2px 8px rgba(0,0,0,.1);display:flex;flex-direction:column}

.auth-brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:20px}
.auth-brand img{width:48px;height:48px;border-radius:8px;display:block}
.auth-brand span{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.2rem;color:#fff}

.step-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.step-count{font-size:.7rem;font-weight:600;color:var(--slate);letter-spacing:.04em}
.step-count b{color:var(--ink)}
.step-track{display:flex;gap:5px}
.step-seg{width:28px;height:4px;border-radius:2px;background:var(--paper-2)}
.step-seg.done{background:var(--signal)}
.step-seg.current{background:var(--amber)}

.eyebrow{font-size:.66rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--signal);margin-bottom:5px}
.step-title{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.3rem;color:var(--ink);letter-spacing:-.01em;margin-bottom:5px}
.step-sub{font-size:.84rem;color:var(--slate);margin-bottom:14px;line-height:1.45}

.alert{background:var(--danger-bg);border:1px solid #f3c6c6;border-radius:8px;padding:10px 13px;font-size:.8rem;color:var(--danger);margin-bottom:14px;display:flex;align-items:flex-start;gap:8px}
.alert i{margin-top:2px;flex-shrink:0}

.social-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
.social-btn{display:flex;align-items:center;justify-content:center;gap:8px;border:1.5px solid var(--line);border-radius:10px;padding:11px 16px;background:#fff;font-size:.84rem;font-weight:600;font-family:'Inter',sans-serif;color:var(--graphite);cursor:pointer;text-decoration:none;transition:border-color .15s,background .15s,transform .1s}
.social-btn:hover{border-color:var(--signal);background:var(--sky);transform:translateY(-1px)}
.social-btn:active{transform:translateY(0)}
.social-btn svg{width:18px;height:18px;flex-shrink:0}
.social-btn.google-btn:hover{border-color:#ea4335}
.social-btn.facebook-btn:hover{border-color:#1877f2}

.divider{display:flex;align-items:center;gap:12px;margin:0 0 16px}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--line)}
.divider span{font-size:.76rem;color:var(--slate);font-weight:500;white-space:nowrap}

.role-cards{display:flex;flex-direction:column;gap:10px}
.role-card{display:flex;align-items:center;gap:14px;border:1.5px solid var(--line);background:#fff;border-radius:11px;padding:16px 18px;cursor:pointer;transition:border-color .15s,box-shadow .15s,background .15s}
.role-card:hover{border-color:var(--signal)}
.role-card.active{border-color:var(--signal);background:var(--sky);box-shadow:0 0 0 3px rgba(26,111,196,.12)}
.role-card .ic{width:40px;height:40px;border-radius:9px;flex-shrink:0;background:var(--paper-2);color:var(--slate);display:flex;align-items:center;justify-content:center;font-size:1rem;transition:background .15s,color .15s}
.role-card.active .ic{background:var(--signal);color:#fff}
.role-card .body{flex:1;min-width:0}
.role-card .body strong{display:block;font-size:.88rem;color:var(--ink);font-weight:700;margin-bottom:2px}
.role-card .body span{font-size:.76rem;color:var(--slate);line-height:1.4}
.role-card .radio{width:17px;height:17px;border-radius:50%;border:2px solid var(--line);flex-shrink:0;position:relative;transition:border-color .15s}
.role-card.active .radio{border-color:var(--signal)}
.role-card.active .radio::after{content:'';position:absolute;inset:3px;border-radius:50%;background:var(--signal)}

.field{margin-bottom:15px}
.field label{display:block;font-size:.78rem;font-weight:600;color:var(--graphite);margin-bottom:5px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:6px 12px}
.input-wrap{position:relative}
.input-wrap i.icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--slate);font-size:.88rem;pointer-events:none}
input[type=email],input[type=password],input[type=text],select{width:100%;border:1.5px solid var(--line);border-radius:9px;padding:11px 13px 11px 38px;font-size:.88rem;font-family:'Inter',sans-serif;color:var(--graphite);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;appearance:none;-webkit-appearance:none}
input:focus,select:focus{border-color:var(--signal);box-shadow:0 0 0 3px rgba(26,111,196,.12)}
input::placeholder{color:#A8AFB8}
select{cursor:pointer;padding-right:38px}
.select-arrow{position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--slate);font-size:.72rem}
.tog{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--slate);font-size:.85rem;padding:0;transition:color .15s}
.tog:hover{color:var(--signal)}

.worker-block{margin-top:4px;margin-bottom:6px;padding-top:12px;border-top:1px dashed var(--line);display:none}
.worker-block.show{display:block}
.worker-block .tag-label{font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--amber-2);margin-bottom:8px;display:flex;align-items:center;gap:6px}

.strength-bar{height:3px;border-radius:2px;background:var(--paper-2);margin:4px 0 14px;overflow:hidden}
.strength-fill{height:100%;border-radius:2px;width:0;transition:width .3s,background .3s}

.terms-wrap{display:flex;align-items:flex-start;gap:8px;margin:4px 0 18px}
.terms-wrap input[type=checkbox]{width:15px;height:15px;accent-color:var(--signal);cursor:pointer;margin-top:2px;flex-shrink:0}
.terms-wrap span{font-size:.82rem;color:var(--graphite);line-height:1.45}
.terms-wrap a{color:var(--signal);font-weight:600;text-decoration:none}
.terms-wrap a:hover{color:var(--signal-2)}

.step-actions{display:flex;justify-content:flex-end;gap:10px;padding-top:8px}
.btn{border:none;border-radius:9px;padding:12px 20px;min-width:110px;font-size:.92rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s,transform .12s,color .15s,border-color .15s}
.btn-ghost{background:transparent;border:1.5px solid var(--line);color:var(--graphite)}
.btn-ghost:hover{border-color:var(--slate)}
.btn-primary{background:var(--signal);color:#fff}
.btn-primary:hover{background:var(--signal-2);transform:translateY(-1px)}
.btn-primary:active{transform:translateY(0)}

.signin-row{text-align:center;font-size:.84rem;color:var(--slate);margin-top:16px}
.signin-row a{color:var(--signal);font-weight:600;text-decoration:none}
.signin-row a:hover{color:var(--signal-2)}

.step{display:none}
.step.active{display:flex;flex-direction:column;flex:1}

@media(max-width:540px){.auth-card{padding:28px 22px 24px;border-radius:14px}}
@media(max-width:480px){.grid-2{grid-template-columns:1fr}.social-row{grid-template-columns:1fr}.hero-icon-f{opacity:.4}}
</style>
</head>
<body>
<div class="mouse-glow" id="mouseGlow"></div>

<div class="hero-icons-floating" id="iconsWrap">
  <div class="hero-icon-f" data-depth="0.03"><i class="fa-solid fa-wrench"></i></div>
  <div class="hero-icon-f" data-depth="0.05"><i class="fa-solid fa-bolt"></i></div>
  <div class="hero-icon-f" data-depth="0.02"><i class="fa-solid fa-paint-roller"></i></div>
  <div class="hero-icon-f" data-depth="0.04"><i class="fa-solid fa-screwdriver-wrench"></i></div>
  <div class="hero-icon-f" data-depth="0.03"><i class="fa-solid fa-hammer"></i></div>
  <div class="hero-icon-f" data-depth="0.05"><i class="fa-solid fa-broom"></i></div>
  <div class="hero-icon-f" data-depth="0.04"><i class="fa-solid fa-fan"></i></div>
  <div class="hero-icon-f" data-depth="0.02"><i class="fa-solid fa-plug"></i></div>
  <div class="hero-icon-f" data-depth="0.05"><i class="fa-solid fa-house-chimney"></i></div>
  <div class="hero-icon-f" data-depth="0.03"><i class="fa-solid fa-hard-hat"></i></div>
  <div class="hero-icon-f" data-depth="0.04"><i class="fa-solid fa-shield-halved"></i></div>
  <div class="hero-icon-f" data-depth="0.02"><i class="fa-solid fa-star"></i></div>
</div>

<div class="auth-page">
  <div class="auth-brand">
    <img src="../images/logo-gs-removebg-preview.png" alt="KaAyos Logo" width="48" height="48">
    <span>KaAyos</span>
  </div>
  <div class="auth-card">
    <div class="step-meta">
      <span class="step-count">STEP <b id="stepNow">01</b> / 03</span>
      <div class="step-track">
        <div class="step-seg current" data-seg="1"></div>
        <div class="step-seg" data-seg="2"></div>
        <div class="step-seg" data-seg="3"></div>
      </div>
    </div>
    @if($errors->any())
    <div style="background:#FBEAEA;border:1px solid #f3c6c6;border-radius:10px;padding:12px 14px;margin-bottom:16px">
      <div style="display:flex;align-items:flex-start;gap:8px">
        <i class="fa-solid fa-circle-exclamation" style="color:#A32D2D;margin-top:2px;flex-shrink:0"></i>
        <div style="flex:1">
          <div style="font-size:.82rem;font-weight:600;color:#A32D2D;margin-bottom:5px">Please fix the following errors:</div>
          <ul style="list-style:none;padding:0;margin:0">
            @foreach($errors->all() as $error)
              <li style="font-size:.78rem;color:#A32D2D;padding:2px 0;display:flex;align-items:flex-start;gap:5px">
                <i class="fa-solid fa-circle" style="font-size:.3rem;margin-top:6px;flex-shrink:0"></i>
                <span>{{ $error }}</span>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
    @endif
    @if(session('error'))
    <div class="alert"><i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}</div>
    @endif
    <form id="registerForm" method="POST" action="{{ route('register') }}" novalidate>
      @csrf
      <input type="hidden" name="role" id="roleInput" value="{{ old('role', 'client') }}">
      <input type="hidden" name="intended" id="intendedInput" value="">
      <!-- STEP 1: ROLE -->
      <section class="step active" data-step="1">
        <div class="eyebrow">Get started</div>
        <h2 class="step-title">Who's this account for?</h2>
        <p class="step-sub">This decides what we ask you next - pick the one that fits.</p>
        <div class="role-cards">
          <div class="role-card active" id="roleClient" onclick="setRole('client')">
            <div class="ic"><i class="fa-solid fa-user"></i></div>
            <div class="body"><strong>I'm a Client</strong><span>I want to find and hire trusted workers nearby</span></div>
            <div class="radio"></div>
          </div>
          <div class="role-card" id="roleWorker" onclick="setRole('worker')">
            <div class="ic"><i class="fa-solid fa-hard-hat"></i></div>
            <div class="body"><strong>I'm a Worker</strong><span>I want to offer my trade and get matched to jobs</span></div>
            <div class="radio"></div>
          </div>
        </div>
        <div class="divider" style="margin-top:20px"><span>or register with</span></div>
        <div class="social-row">
          <a id="socialGoogle" href="{{ route('social.redirect', 'google') }}?role=client" class="social-btn google-btn">
            <svg viewBox="0 0 24 24" width="18" height="18"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Google
          </a>
          <a id="socialFacebook" href="{{ route('social.redirect', 'facebook') }}?role=client" class="social-btn facebook-btn">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            Facebook
          </a>
        </div>
      </section>
      <!-- STEP 2: DETAILS -->
      <section class="step" data-step="2">
        <div class="eyebrow">Tell us about you</div>
        <h2 class="step-title">Your details</h2>
        <p class="step-sub">We'll use this to set up your profile and keep your account secure.</p>
        <div class="grid-2">
          <div class="field">
            <label for="first_name">First name</label>
            <div class="input-wrap"><i class="fa-solid fa-user icon"></i><input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" placeholder="Juan" required autocomplete="given-name"></div>
          </div>
          <div class="field">
            <label for="last_name">Last name</label>
            <div class="input-wrap"><i class="fa-solid fa-user icon"></i><input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" placeholder="dela Cruz" required autocomplete="family-name"></div>
          </div>
        </div>
        <div class="field">
          <label for="email">Email address</label>
            <div class="input-wrap"><i class="fa-regular fa-envelope icon"></i><input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email"></div>
        </div>
        <div class="field">
          <label for="phone">Phone number</label>
          <div class="input-wrap"><i class="fa-solid fa-phone icon"></i><input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="09XX XXX XXXX" autocomplete="tel"></div>
        </div>
        <div class="worker-block" id="workerBlock">
          <div class="tag-label"><i class="fa-solid fa-hard-hat"></i>Worker details</div>
          <div class="field">
            <label for="service_category">Service category</label>
            <div class="input-wrap">
              <i class="fa-solid fa-briefcase icon"></i>
              <select id="service_category" name="service_category">
                <option value="" disabled {{ old('service_category') ? '' : 'selected' }}>Select your trade</option>
                <option value="plumbing" {{ old('service_category')==='plumbing' ? 'selected' : '' }}>Plumbing</option>
                <option value="electrical" {{ old('service_category')==='electrical' ? 'selected' : '' }}>Electrical</option>
                <option value="carpentry" {{ old('service_category')==='carpentry' ? 'selected' : '' }}>Carpentry</option>
                <option value="painting" {{ old('service_category')==='painting' ? 'selected' : '' }}>Painting</option>
                <option value="aircon" {{ old('service_category')==='aircon' ? 'selected' : '' }}>Aircon Services</option>
                <option value="cleaning" {{ old('service_category')==='cleaning' ? 'selected' : '' }}>Cleaning</option>
                <option value="roofing" {{ old('service_category')==='roofing' ? 'selected' : '' }}>Roofing</option>
                <option value="welding" {{ old('service_category')==='welding' ? 'selected' : '' }}>Welding</option>
                <option value="gardening" {{ old('service_category')==='gardening' ? 'selected' : '' }}>Gardening</option>
                <option value="other" {{ old('service_category')==='other' ? 'selected' : '' }}>Other</option>
              </select>
              <i class="fa-solid fa-chevron-down select-arrow"></i>
            </div>
          </div>
          <div class="field">
            <label for="barangay">Barangay <span style="font-weight:500;color:var(--slate)">(Tuy, Batangas)</span></label>
            <div class="input-wrap">
              <i class="fa-solid fa-location-dot icon"></i>
              <select id="barangay" name="barangay">
                <option value="" disabled {{ old('barangay') ? '' : 'selected' }}>Select your barangay</option>
                @foreach($barangays as $barangay)
                  <option value="{{ $barangay }}" {{ old('barangay')===$barangay ? 'selected' : '' }}>{{ $barangay }}</option>
                @endforeach
              </select>
              <i class="fa-solid fa-chevron-down select-arrow"></i>
            </div>
          </div>
        </div>
      </section>
      <!-- STEP 3: SECURITY -->
      <section class="step" data-step="3">
        <div class="eyebrow">Last step</div>
        <h2 class="step-title">Lock it down</h2>
        <p class="step-sub">Choose a password you'll remember - you'll use it every time you sign in.</p>
        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <i class="fa-solid fa-lock icon"></i>
            <input type="password" id="password" name="password" placeholder="Create a strong password" required autocomplete="new-password" oninput="checkStrength(this.value)">
            <button type="button" class="tog" id="togBtn1" aria-label="Toggle password visibility"><i class="fa-regular fa-eye" id="togIcon1"></i></button>
          </div>
          <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        </div>
        <div class="field">
          <label for="password_confirmation">Confirm password</label>
          <div class="input-wrap">
            <i class="fa-solid fa-lock icon"></i>
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat your password" required autocomplete="new-password">
            <button type="button" class="tog" id="togBtn2" aria-label="Toggle confirm password visibility"><i class="fa-regular fa-eye" id="togIcon2"></i></button>
          </div>
        </div>
        <div class="terms-wrap">
          <input type="checkbox" id="terms" name="terms" required>
          <span>I agree to KaAyos' <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></span>
        </div>
      </section>
      <div class="step-actions">
        <button type="button" class="btn btn-ghost" id="backBtn" style="visibility:hidden">Back</button>
        <button type="button" class="btn btn-primary" id="nextBtn">Continue</button>
        <button type="submit" class="btn btn-primary" id="submitBtn" style="display:none"><i class="fa-solid fa-user-plus"></i>Create account</button>
      </div>
    </form>
    <p class="signin-row">Already have an account? <a href="{{ route('login') }}">Sign in instead</a></p>
  </div>
</div>
<script>
let currentStep=1;const totalSteps=3;
function setRole(role){document.getElementById('roleInput').value=role;document.getElementById('roleClient').classList.toggle('active',role==='client');document.getElementById('roleWorker').classList.toggle('active',role==='worker');document.getElementById('workerBlock').classList.toggle('show',role==='worker');document.querySelectorAll('#workerBlock select, #workerBlock input').forEach(el=>el.required=(role==='worker'));document.getElementById('socialGoogle').href='{{ route("social.redirect","google") }}?role='+role;document.getElementById('socialFacebook').href='{{ route("social.redirect","facebook") }}?role='+role;}
function goToStep(n){document.querySelectorAll('.step').forEach(s=>s.classList.toggle('active',Number(s.dataset.step)===n));document.querySelectorAll('.step-seg').forEach(seg=>{const i=Number(seg.dataset.seg);seg.classList.remove('done','current');if(i<n)seg.classList.add('done');if(i===n)seg.classList.add('current')});document.getElementById('stepNow').textContent=String(n).padStart(2,'0');const backBtn=document.getElementById('backBtn');const nextBtn=document.getElementById('nextBtn');const submitBtn=document.getElementById('submitBtn');if(n===1){backBtn.style.visibility='hidden';nextBtn.style.display='flex';submitBtn.style.display='none'}else if(n===2){backBtn.style.visibility='visible';nextBtn.style.display='flex';submitBtn.style.display='none'}else if(n===3){backBtn.style.visibility='visible';nextBtn.style.display='none';submitBtn.style.display='flex'}currentStep=n;}
function currentStepEl(){return document.querySelector('.step[data-step="'+currentStep+'"]');}
function validateStep(){const inputs=currentStepEl().querySelectorAll('input:not([type=hidden]), select');for(const el of inputs){if(el.offsetParent!==null&&!el.checkValidity()){el.reportValidity();return false}}return true;}
document.getElementById('nextBtn').addEventListener('click',()=>{if(!validateStep())return;if(currentStep<totalSteps)goToStep(currentStep+1)});
document.getElementById('backBtn').addEventListener('click',()=>{if(currentStep>1)goToStep(currentStep-1)});
function makeToggle(btnId,iconId,inputId){document.getElementById(btnId).addEventListener('click',()=>{const input=document.getElementById(inputId);const icon=document.getElementById(iconId);const show=input.type==='password';input.type=show?'text':'password';icon.className=show?'fa-regular fa-eye-slash':'fa-regular fa-eye'})}
makeToggle('togBtn1','togIcon1','password');makeToggle('togBtn2','togIcon2','password_confirmation');
function checkStrength(val){const fill=document.getElementById('strengthFill');let score=0;if(val.length>=8)score++;if(/[A-Z]/.test(val))score++;if(/[a-z]/.test(val))score++;if(/[0-9]/.test(val))score++;if(/[^A-Za-z0-9]/.test(val))score++;const widths=['0%','20%','40%','60%','80%','100%'];const colors=['transparent','#e57373','#e57373','#ffb74d','#81c784','#43a047'];fill.style.width=widths[score];fill.style.background=colors[score]}
var intended=new URLSearchParams(window.location.search).get('intended');
if(intended){document.getElementById('intendedInput').value=intended;var workerCard=document.getElementById('roleWorker');if(workerCard)workerCard.style.display='none';setRole('client');setTimeout(function(){document.getElementById('nextBtn').click()},100)}

var savedRole=document.getElementById('roleInput').value;
if(savedRole==='worker'){setRole('worker')}
@if($errors->any())
  var targetStep=1;
  @if($errors->has('first_name')||$errors->has('last_name')||$errors->has('email')||$errors->has('phone')||$errors->has('service_category')||$errors->has('barangay'))
    targetStep=2;
  @elseif($errors->has('password')||$errors->has('password_confirmation')||$errors->has('terms'))
    targetStep=3;
  @endif
  if(targetStep>1){goToStep(targetStep)}
@endif

var urlRole=new URLSearchParams(window.location.search).get('role');
if(urlRole==='worker'){
  setRole('worker');
  document.getElementById('roleClient').style.opacity='0.4';
  document.getElementById('roleClient').style.pointerEvents='none';
  document.getElementById('roleClient').style.cursor='default';
  goToStep(2);
}

(function() {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var glow = document.getElementById('mouseGlow');
  var icons = document.querySelectorAll('.hero-icon-f');
  var vw = window.innerWidth, vh = window.innerHeight;

  function onResize() { vw = window.innerWidth; vh = window.innerHeight; }
  window.addEventListener('resize', onResize);

  document.addEventListener('mousemove', function(e) {
    glow.style.setProperty('--mx', e.clientX + 'px');
    glow.style.setProperty('--my', e.clientY + 'px');
    glow.classList.add('active');

    var cx = e.clientX - vw / 2;
    var cy = e.clientY - vh / 2;
    for (var i = 0; i < icons.length; i++) {
      var depth = parseFloat(icons[i].dataset.depth) || 0.03;
      icons[i].style.translate = (cx * depth * -1) + 'px ' + (cy * depth * -1) + 'px';
    }
  });

  document.addEventListener('mouseleave', function() {
    glow.classList.remove('active');
    for (var i = 0; i < icons.length; i++) {
      icons[i].style.translate = '0 0';
    }
  });
})();
</script>
</body>
</html>
