<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Login</title>
<link rel="icon" href="../images/KaAyos_logo.jpeg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#042C53;--ink-2:#0C447C;--signal:#1A6FC4;--signal-2:#15598F;
  --sky:#EAF3FC;--amber:#F2A33D;--amber-2:#D9842A;
  --paper:#FBF9F5;--paper-2:#F1ECE2;
  --graphite:#202B36;--slate:#6E7A88;--line:#E3DED2;
  --line-on-ink:rgba(255,255,255,.14);
  --danger:#A32D2D;--danger-bg:#FBEAEA;
  --success:#1a6852;--success-bg:#d6f5e8;
}
html,body{height:100%}
body{
  font-family:'Inter',sans-serif;
  color:var(--graphite);
  background:var(--ink);
}
.page{
  min-height:100vh;
  display:grid;
  grid-template-columns:minmax(340px,38%) 1fr;
}

/* ─── LEFT: TICKET PANEL ─── */
.ticket-panel{
  position:relative;
  background:
    radial-gradient(circle at 14% 8%, rgba(255,255,255,.06), transparent 40%),
    linear-gradient(165deg,var(--ink) 0%,#06203D 65%,#03182E 100%);
  color:#fff;
  padding:44px 44px 36px;
  display:flex;
  flex-direction:column;
  overflow:hidden;
}
.ticket-content{flex:1;display:flex;flex-direction:column;justify-content:center;min-height:0;position:relative;z-index:1}
.ticket-foot{position:relative;z-index:1;font-size:.72rem;color:rgba(255,255,255,.4);margin-top:18px}
.ticket-panel::before{
  content:'';position:absolute;inset:0;
  background-image:radial-gradient(rgba(255,255,255,.05) 1px,transparent 1px);
  background-size:24px 24px;pointer-events:none;
}
.brand{display:flex;align-items:center;gap:10px;position:relative;z-index:1}
.brand-icon img{width:50px;height:50px;background:var(--signal);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.brand-name{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.2rem;letter-spacing:.01em}

.ticket-headline{
  font-family:'Archivo',sans-serif;font-weight:800;
  font-size:clamp(1.6rem,2.6vw,2.15rem);line-height:1.14;
  letter-spacing:-.01em;margin-top:30px;max-width:420px;
  position:relative;z-index:1;
}
.ticket-headline em{color:var(--amber);font-style:normal}
.ticket-sub{
  margin-top:12px;font-size:.92rem;line-height:1.55;
  color:rgba(255,255,255,.68);max-width:380px;
  position:relative;z-index:1;
}

.ticket-mock{
  position:relative;z-index:1;margin-top:28px;
  background:rgba(255,255,255,.04);border:1px solid var(--line-on-ink);
  border-radius:14px;backdrop-filter:blur(2px);
  max-width:400px;display:flex;flex-direction:column;
}
.ticket-mock-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 18px;border-bottom:1px dashed var(--line-on-ink);
}
.ticket-mock-head .tag{
  font-family:'JetBrains Mono',monospace;font-size:.65rem;
  font-weight:600;letter-spacing:.08em;
  color:rgba(255,255,255,.5);text-transform:uppercase;
}
.ticket-mock-head .serial{
  font-family:'JetBrains Mono',monospace;
  font-size:.72rem;color:rgba(255,255,255,.85);font-weight:500;
}
.ticket-mock-status{
  display:inline-flex;align-items:center;gap:5px;
  background:rgba(242,163,61,.15);border:1px solid rgba(242,163,61,.4);
  color:var(--amber);font-size:.65rem;font-weight:700;
  letter-spacing:.06em;text-transform:uppercase;
  padding:3px 8px;border-radius:20px;
}
.ticket-mock-status .dot{width:5px;height:5px;border-radius:50%;background:var(--amber)}

.trade-list{padding:14px 18px;display:flex;flex-direction:column;gap:9px}
.trade-row{display:flex;align-items:center;gap:11px;font-size:.84rem}
.trade-row i.trade-icon{
  width:24px;height:24px;border-radius:6px;
  background:rgba(255,255,255,.08);
  display:flex;align-items:center;justify-content:center;
  font-size:.7rem;color:var(--sky);flex-shrink:0;
}
.trade-row span{color:rgba(255,255,255,.82);font-weight:500;flex:1}
.trade-row .check{color:var(--amber);font-size:.78rem}

.ticket-stub{
  margin-top:auto;padding:14px 18px 16px;
  border-top:1px dashed var(--line-on-ink);
  display:flex;flex-wrap:wrap;gap:12px 18px;
}
.stub-item{display:flex;align-items:center;gap:6px;font-size:.7rem;color:rgba(255,255,255,.55)}
.stub-item i{color:var(--amber);font-size:.72rem}
.ticket-mock::before,.ticket-mock::after{
  content:'';position:absolute;width:14px;height:14px;border-radius:50%;
  background:var(--ink);z-index:2;
}

/* ─── RIGHT: FORM PANEL ─── */
.form-panel{
  background:var(--paper);
  display:flex;justify-content:center;align-items:center;
  padding:38px 56px 32px;
}
.form-inner{width:100%;max-width:440px}

.form-head{margin-bottom:24px}
.form-eyebrow{
  font-size:.68rem;font-weight:700;letter-spacing:.1em;
  text-transform:uppercase;color:var(--signal);margin-bottom:6px;
  display:flex;align-items:center;gap:10px;
}
.form-eyebrow::after{content:'';flex:1;max-width:50px;height:2px;background:var(--amber);border-radius:1px}
.form-title{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.4rem;color:var(--ink);letter-spacing:-.01em;margin-bottom:6px}
.form-sub{font-size:.86rem;color:var(--slate);line-height:1.45;margin-bottom:0}

.alert{
  background:var(--danger-bg);border:1px solid #f3c6c6;
  border-radius:8px;padding:11px 14px;font-size:.82rem;
  color:var(--danger);margin-bottom:16px;display:flex;align-items:flex-start;gap:8px;
}
.alert i{margin-top:2px;flex-shrink:0}
.alert-success{
  background:var(--success-bg);border:1px solid #a3e0c0;
  border-radius:8px;padding:11px 14px;font-size:.82rem;
  color:var(--success);margin-bottom:16px;
}
.alert-success .s-head{font-weight:600;margin-bottom:4px;display:flex;align-items:flex-start;gap:10px}
.alert-success .s-head i{margin-top:2px;flex-shrink:0}
.alert-success .s-sub{font-size:.78rem;opacity:.85;margin-left:26px}

.field{margin-bottom:16px}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--graphite);margin-bottom:6px}
.input-wrap{position:relative}
.input-wrap i.icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--slate);font-size:.9rem;pointer-events:none}
input[type=email],input[type=password],input[type=text]{
  width:100%;border:1.5px solid var(--line);border-radius:9px;
  padding:12px 14px 12px 40px;font-size:.9rem;font-family:'Inter',sans-serif;
  color:var(--graphite);background:#fff;outline:none;
  transition:border-color .15s,box-shadow .15s;
}
input:focus{border-color:var(--signal);box-shadow:0 0 0 3px rgba(26,111,196,.12)}
input::placeholder{color:#A8AFB8}
.tog{
  position:absolute;right:13px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;color:var(--slate);
  font-size:.88rem;padding:0;transition:color .15s;
}
.tog:hover{color:var(--signal)}

.row-between{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.check-wrap{display:flex;align-items:center;gap:8px;cursor:pointer}
.check-wrap input[type=checkbox]{width:16px;height:16px;accent-color:var(--signal);cursor:pointer;margin:0}
.check-wrap span{font-size:.84rem;color:var(--graphite)}
.forgot{font-size:.84rem;color:var(--signal);font-weight:500;text-decoration:none;transition:color .15s}
.forgot:hover{color:var(--signal-2)}

.btn{
  border:none;border-radius:9px;padding:13px 20px;
  font-size:.95rem;font-weight:700;font-family:'Inter',sans-serif;
  cursor:pointer;display:flex;align-items:center;
  justify-content:center;gap:9px;
  transition:background .15s,transform .12s,color .15s;
  width:100%;
}
.btn-primary{background:var(--signal);color:#fff}
.btn-primary:hover{background:var(--signal-2);transform:translateY(-1px)}
.btn-primary:active{transform:translateY(0)}

.divider{display:flex;align-items:center;gap:12px;margin:20px 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--line)}
.divider span{font-size:.78rem;color:var(--slate);font-weight:500;white-space:nowrap}

.register-row{text-align:center;font-size:.84rem;color:var(--slate)}
.register-row a{color:var(--signal);font-weight:600;text-decoration:none}
.register-row a:hover{color:var(--signal-2)}

@media(max-width:900px){
  .page{grid-template-columns:1fr}
  .ticket-panel{padding:26px 24px 22px}
  .ticket-mock{display:none}
  .ticket-headline{font-size:1.4rem;margin-top:18px}
  .ticket-sub{display:none}
  .form-panel{padding:28px 24px 24px}
}
@media(max-width:480px){
  .form-panel{padding:22px 16px 20px}
}
</style>
</head>
<body>

<div class="page">

  <!-- LEFT: BRAND / TICKET -->
  <aside class="ticket-panel">
    <div class="brand">
      <div class="brand-icon"><img src="../images/logo-gs-removebg-preview.png" alt="KaAyos Logo"></div>
      <span class="brand-name">KaAyos</span>
    </div>

    <h1 class="ticket-headline" style="margin-top:0">Your next trusted worker is just <em>a sign-in away</em>.</h1>
    <div class="ticket-content">
    <p class="ticket-sub">Sign in to book verified workers, track jobs, and manage your home service needs all in one place.</p>

    <div class="ticket-mock" aria-hidden="true">
      <div class="ticket-mock-head">
        <span class="tag">Active Session</span>
        <span class="ticket-mock-status"><span class="dot"></span>Ready</span>
      </div>
      <div class="trade-list">
        <div class="trade-row"><i class="fa-solid fa-wrench trade-icon"></i><span>Plumbing</span><i class="fa-solid fa-check check"></i></div>
        <div class="trade-row"><i class="fa-solid fa-bolt trade-icon"></i><span>Electrical</span><i class="fa-solid fa-check check"></i></div>
        <div class="trade-row"><i class="fa-solid fa-hammer trade-icon"></i><span>Carpentry</span><i class="fa-solid fa-check check"></i></div>
        <div class="trade-row"><i class="fa-solid fa-paint-roller trade-icon"></i><span>Painting</span><i class="fa-solid fa-check check"></i></div>
      </div>
      <div class="ticket-stub">
        <div class="stub-item"><i class="fa-solid fa-id-card"></i>ID-verified workers</div>
        <div class="stub-item"><i class="fa-solid fa-shield-halved"></i>Secure login</div>
        <div class="stub-item"><i class="fa-solid fa-bolt"></i>Fast booking</div>
      </div>
    </div>
    </div>
  </aside>

  <!-- RIGHT: FORM -->
  <main class="form-panel">
    <div class="form-inner">

      <div class="form-head">
        <div class="form-eyebrow">Welcome back</div>
        <div class="form-title">Sign in to your account</div>
        <div class="form-sub">Find and book trusted workers near you.</div>
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

      <form method="POST" action="{{ route('login') }}">
        @csrf
        <input type="hidden" name="intended" id="intendedInput" value="">

        <div class="field">
          <label for="email">Email Address</label>
          <div class="input-wrap">
            <i class="fa-regular fa-envelope icon" aria-hidden="true"></i>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="email">
          </div>
          @error('email')<div class="alert" style="margin-top:6px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <i class="fa-solid fa-lock icon" aria-hidden="true"></i>
            <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            <button type="button" class="tog" id="togBtn" aria-label="Toggle password visibility"><i class="fa-regular fa-eye" id="togIcon"></i></button>
          </div>
          @error('password')<div class="alert" style="margin-top:6px"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</div>@enderror
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

      <div class="divider"><span>New to KaAyos?</span></div>

      <div class="register-row">
        <a href="{{ route('register') }}">Create a free account &rarr;</a>
      </div>

    </div>
  </main>

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
</script>
</body>
</html>
