<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Login</title>
<link rel="icon" href="../images/KaAyos_logo.jpeg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#042C53;--ink-2:#0C447C;--signal:#1A6FC4;--signal-2:#15598F;--sky:#EAF3FC;--amber:#F2A33D;--amber-2:#D9842A;--paper:#FBF9F5;--paper-2:#F1ECE2;--graphite:#202B36;--slate:#6E7A88;--line:#E3DED2;--danger:#A32D2D;--danger-bg:#FBEAEA;--success:#1a6852;--success-bg:#d6f5e8}
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

.auth-page{width:100%;max-width:480px;display:flex;flex-direction:column;align-items:center;position:relative;z-index:1}

.auth-brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:20px}
.auth-brand img{width:48px;height:48px;border-radius:8px;display:block}
.auth-brand span{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.2rem;color:#fff}

.auth-card{width:100%;background:#fff;border-radius:18px;padding:36px 32px 32px;box-shadow:0 12px 40px rgba(0,0,0,.25),0 2px 8px rgba(0,0,0,.1);display:flex;flex-direction:column}

.auth-head{margin-bottom:20px}
.auth-eyebrow{font-size:.66rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--signal);margin-bottom:5px}
.auth-title{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.35rem;color:var(--ink);letter-spacing:-.01em;margin-bottom:5px}
.auth-sub{font-size:.84rem;color:var(--slate);line-height:1.45;margin-bottom:0}

.alert{background:var(--danger-bg);border:1px solid #f3c6c6;border-radius:8px;padding:10px 13px;font-size:.8rem;color:var(--danger);margin-bottom:14px;display:flex;align-items:flex-start;gap:8px}
.alert i{margin-top:2px;flex-shrink:0}
.alert-success{background:var(--success-bg);border:1px solid #a3e0c0;border-radius:8px;padding:10px 13px;font-size:.8rem;color:var(--success);margin-bottom:14px}
.alert-success .s-head{font-weight:600;margin-bottom:3px;display:flex;align-items:flex-start;gap:8px}
.alert-success .s-head i{margin-top:2px;flex-shrink:0}
.alert-success .s-sub{font-size:.74rem;opacity:.85;margin-left:24px}

.social-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px}
.social-btn{display:flex;align-items:center;justify-content:center;gap:8px;border:1.5px solid var(--line);border-radius:10px;padding:11px 16px;background:#fff;font-size:.84rem;font-weight:600;font-family:'Inter',sans-serif;color:var(--graphite);cursor:pointer;text-decoration:none;transition:border-color .15s,background .15s,transform .1s}
.social-btn:hover{border-color:var(--signal);background:var(--sky);transform:translateY(-1px)}
.social-btn:active{transform:translateY(0)}
.social-btn svg{width:18px;height:18px;flex-shrink:0}
.social-btn.google-btn:hover{border-color:#ea4335}
.social-btn.facebook-btn:hover{border-color:#1877f2}

.divider{display:flex;align-items:center;gap:12px;margin:0 0 20px}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--line)}
.divider span{font-size:.76rem;color:var(--slate);font-weight:500;white-space:nowrap}

.field{margin-bottom:15px}
.field label{display:block;font-size:.78rem;font-weight:600;color:var(--graphite);margin-bottom:5px}
.input-wrap{position:relative}
.input-wrap i.icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--slate);font-size:.88rem;pointer-events:none}
input[type=email],input[type=password],input[type=text]{width:100%;border:1.5px solid var(--line);border-radius:9px;padding:11px 13px 11px 38px;font-size:.88rem;font-family:'Inter',sans-serif;color:var(--graphite);background:#fff;outline:none;transition:border-color .15s,box-shadow .15s;appearance:none;-webkit-appearance:none}
input:focus{border-color:var(--signal);box-shadow:0 0 0 3px rgba(26,111,196,.12)}
input::placeholder{color:#A8AFB8}
.tog{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--slate);font-size:.85rem;padding:0;transition:color .15s}
.tog:hover{color:var(--signal)}

.row-between{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.check-wrap{display:flex;align-items:center;gap:7px;cursor:pointer}
.check-wrap input[type=checkbox]{width:15px;height:15px;accent-color:var(--signal);cursor:pointer;margin:0}
.check-wrap span{font-size:.82rem;color:var(--graphite)}
.forgot{font-size:.82rem;color:var(--signal);font-weight:500;text-decoration:none;transition:color .15s}
.forgot:hover{color:var(--signal-2)}

.btn{border:none;border-radius:9px;padding:12px 20px;font-size:.92rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s,transform .12s;color:#fff;width:100%}
.btn-primary{background:var(--signal)}
.btn-primary:hover{background:var(--signal-2);transform:translateY(-1px)}
.btn-primary:active{transform:translateY(0)}

.register-row{text-align:center;font-size:.84rem;color:var(--slate);margin-top:18px}
.register-row a{color:var(--signal);font-weight:600;text-decoration:none}
.register-row a:hover{color:var(--signal-2)}

@media(max-width:540px){.auth-card{padding:28px 22px 24px;border-radius:14px}}
@media(max-width:480px){.social-row{grid-template-columns:1fr}.hero-icon-f{opacity:.4}}
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
    <div class="auth-head">
      <div class="auth-eyebrow">Welcome back</div>
      <div class="auth-title">Sign in to your account</div>
      <div class="auth-sub">Find and book trusted workers near you.</div>
    </div>

    @if(session('status'))
    <div class="alert-success">
      <div class="s-head"><i class="fa-solid fa-circle-check"></i> <span>{{ session('status') }}</span></div>
      @if(session('registered_email'))
      <div class="s-sub"><i class="fa-regular fa-envelope"></i> Sent to <strong>{{ session('registered_email') }}</strong> &middot; Didn't arrive? Check your <strong>spam folder</strong> or try a different email.</div>
      @endif
    </div>
    @endif

    @if(session('error'))
    <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}</div>
    @endif

    @if($errors->any())
    <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
    @endif

    <div class="social-row">
      <a href="{{ route('auth.social.redirect', 'google') }}" class="social-btn google-btn">
        <svg viewBox="0 0 24 24" width="18" height="18"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
        Google
      </a>
      <a href="{{ route('auth.social.redirect', 'facebook') }}" class="social-btn facebook-btn">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
        Facebook
      </a>
    </div>

    <div class="divider"><span>or sign in with email</span></div>

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <input type="hidden" name="intended" id="intendedInput" value="">

      <div class="field">
        <label for="email">Email address</label>
        <div class="input-wrap">
          <i class="fa-regular fa-envelope icon" aria-hidden="true"></i>
          <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
        </div>
        @error('email')<div class="alert" style="margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="input-wrap">
          <i class="fa-solid fa-lock icon" aria-hidden="true"></i>
          <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
          <button type="button" class="tog" id="togBtn" aria-label="Toggle password visibility"><i class="fa-regular fa-eye" id="togIcon"></i></button>
        </div>
        @error('password')<div class="alert" style="margin-top:5px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
      </div>

      <div class="row-between">
        <label class="check-wrap">
          <input type="checkbox" name="remember">
          <span>Remember me</span>
        </label>
        @if(Route::has('password.request'))
          <a href="{{ route('password.request') }}" class="forgot">Forgot password?</a>
        @endif
      </div>

      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i> Sign In</button>
    </form>

    <div class="register-row">
      New to KaAyos? <a href="{{ route('register') }}">Create a free account &rarr;</a>
    </div>
  </div>
</div>

<script>
document.getElementById('togBtn')?.addEventListener('click', function() {
  var pwd = document.getElementById('password');
  var ico = document.getElementById('togIcon');
  var show = pwd.type === 'password';
  pwd.type = show ? 'text' : 'password';
  ico.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
});
var intended = new URLSearchParams(window.location.search).get('intended');
if (intended) document.getElementById('intendedInput').value = intended;

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
