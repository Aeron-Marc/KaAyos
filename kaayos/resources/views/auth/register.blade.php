<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Register</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --ink:#042C53;
  --ink-2:#0C447C;
  --signal:#1A6FC4;
  --signal-2:#15598F;
  --sky:#EAF3FC;
  --amber:#F2A33D;
  --amber-2:#D9842A;
  --paper:#FBF9F5;
  --paper-2:#F1ECE2;
  --graphite:#202B36;
  --slate:#6E7A88;
  --line:#E3DED2;
  --line-on-ink:rgba(255,255,255,.14);
  --danger:#A32D2D;
  --danger-bg:#FBEAEA;
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

/* ============== LEFT: TICKET / BRAND PANEL ============== */
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
  content:'';
  position:absolute;inset:0;
  background-image:radial-gradient(rgba(255,255,255,.05) 1px,transparent 1px);
  background-size:24px 24px;
  pointer-events:none;
}
.brand{display:flex;align-items:center;gap:10px;position:relative;z-index:1}
.brand-icon img{width:50px;height:50px;background:var(--signal);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.brand-name{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.2rem;letter-spacing:.01em}

.ticket-headline{
  font-family:'Archivo',sans-serif;
  font-weight:800;
  font-size:clamp(1.6rem,2.6vw,2.15rem);
  line-height:1.14;
  letter-spacing:-.01em;
  margin-top:30px;
  max-width:420px;
  position:relative;z-index:1;
}
.ticket-headline em{color:var(--amber);font-style:normal}
.ticket-sub{
  margin-top:12px;
  font-size:.92rem;
  line-height:1.55;
  color:rgba(255,255,255,.68);
  max-width:380px;
  position:relative;z-index:1;
}

/* the dispatch-ticket mockup */
.ticket-mock{
  position:relative;z-index:1;
  margin-top:28px;
  background:rgba(255,255,255,.04);
  border:1px solid var(--line-on-ink);
  border-radius:14px;
  backdrop-filter:blur(2px);
  max-width:400px;
  display:flex;
  flex-direction:column;
}
.ticket-mock-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 18px;
  border-bottom:1px dashed var(--line-on-ink);
}
.ticket-mock-head .tag{
  font-family:'JetBrains Mono',monospace;
  font-size:.65rem;font-weight:600;letter-spacing:.08em;
  color:rgba(255,255,255,.5);text-transform:uppercase;
}
.ticket-mock-head .serial{
  font-family:'JetBrains Mono',monospace;
  font-size:.72rem;color:rgba(255,255,255,.85);font-weight:500;
}
.ticket-mock-status{
  display:inline-flex;align-items:center;gap:5px;
  background:rgba(242,163,61,.15);
  border:1px solid rgba(242,163,61,.4);
  color:var(--amber);
  font-size:.65rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
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
  margin-top:auto;
  padding:14px 18px 16px;
  border-top:1px dashed var(--line-on-ink);
  display:flex;flex-wrap:wrap;gap:12px 18px;
}
.stub-item{display:flex;align-items:center;gap:6px;font-size:.7rem;color:rgba(255,255,255,.55)}
.stub-item i{color:var(--amber);font-size:.72rem}

/* notch cutouts to sell the "ticket stub" idea */
.ticket-mock::before,.ticket-mock::after{
  content:'';position:absolute;width:14px;height:14px;border-radius:50%;
  background:var(--ink);z-index:2;
}

/* ============== RIGHT: FORM PANEL ============== */
.form-panel{
  background:var(--paper);
  display:flex;
  justify-content:center;
  padding:38px 56px 32px;
  overflow-y:auto;
}
.form-inner{width:100%;max-width:440px;display:flex;flex-direction:column;min-height:100%}

.step-meta{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
.step-count{font-family:'JetBrains Mono',monospace;font-size:.72rem;font-weight:600;color:var(--slate);letter-spacing:.04em}
.step-count b{color:var(--ink)}
.step-track{display:flex;gap:6px}
.step-seg{width:30px;height:4px;border-radius:2px;background:var(--paper-2)}
.step-seg.done{background:var(--signal)}
.step-seg.current{background:var(--amber)}

.eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--signal);margin-bottom:6px}
.step-title{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.55rem;color:var(--ink);letter-spacing:-.01em;margin-bottom:6px}
.step-sub{font-size:.86rem;color:var(--slate);margin-bottom:22px;line-height:1.45}

.alert{background:var(--danger-bg);border:1px solid #f3c6c6;border-radius:8px;padding:9px 12px;font-size:.8rem;color:var(--danger);margin-bottom:14px;display:flex;align-items:flex-start;gap:8px}
.alert i{margin-top:1px}

/* role cards */
.role-cards{display:flex;flex-direction:column;gap:10px}
.role-card{
  display:flex;align-items:center;gap:14px;
  border:1.5px solid var(--line);
  background:#fff;
  border-radius:12px;
  padding:16px 16px;
  cursor:pointer;
  transition:border-color .15s,box-shadow .15s,background .15s;
}
.role-card:hover{border-color:var(--signal)}
.role-card.active{border-color:var(--signal);background:var(--sky);box-shadow:0 0 0 3px rgba(26,111,196,.1)}
.role-card .ic{
  width:42px;height:42px;border-radius:10px;flex-shrink:0;
  background:var(--paper-2);color:var(--slate);
  display:flex;align-items:center;justify-content:center;font-size:1.05rem;
  transition:background .15s,color .15s;
}
.role-card.active .ic{background:var(--signal);color:#fff}
.role-card .body{flex:1;min-width:0}
.role-card .body strong{display:block;font-size:.92rem;color:var(--ink);font-weight:700;margin-bottom:2px}
.role-card .body span{font-size:.79rem;color:var(--slate);line-height:1.4}
.role-card .radio{width:18px;height:18px;border-radius:50%;border:2px solid var(--line);flex-shrink:0;position:relative;transition:border-color .15s}
.role-card.active .radio{border-color:var(--signal)}
.role-card.active .radio::after{content:'';position:absolute;inset:3px;border-radius:50%;background:var(--signal)}

/* form fields */
.field{margin-bottom:14px}
.field label{display:block;font-size:.78rem;font-weight:600;color:var(--graphite);margin-bottom:5px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:0 12px}
.input-wrap{position:relative}
.input-wrap i.icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--slate);font-size:.85rem;pointer-events:none}
input[type=email],input[type=password],input[type=text],select{
  width:100%;border:1.5px solid var(--line);border-radius:9px;
  padding:10px 12px 10px 36px;font-size:.88rem;font-family:'Inter',sans-serif;
  color:var(--graphite);background:#fff;outline:none;
  transition:border-color .15s,box-shadow .15s;appearance:none;-webkit-appearance:none;
}
input:focus,select:focus{border-color:var(--signal);box-shadow:0 0 0 3px rgba(26,111,196,.12)}
input::placeholder{color:#A8AFB8}
select{cursor:pointer;padding-right:34px}
.select-arrow{position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--slate);font-size:.72rem}
.tog{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--slate);font-size:.85rem;padding:2px}
.tog:hover{color:var(--signal)}
.field-error{font-size:.74rem;color:var(--danger);margin-top:4px}

.worker-block{
  margin-top:4px;margin-bottom:6px;padding-top:14px;
  border-top:1px dashed var(--line);
  display:none;
}
.worker-block.show{display:block}
.worker-block .tag-label{font-size:.7rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--amber-2);margin-bottom:10px;display:flex;align-items:center;gap:6px}

.strength-bar{height:3px;border-radius:2px;background:var(--paper-2);margin:-6px 0 14px;overflow:hidden}
.strength-fill{height:100%;border-radius:2px;width:0;transition:width .3s,background .3s}

.terms-wrap{display:flex;align-items:flex-start;gap:9px;margin:6px 0 20px}
.terms-wrap input[type=checkbox]{width:16px;height:16px;accent-color:var(--signal);cursor:pointer;margin-top:2px;flex-shrink:0}
.terms-wrap span{font-size:.8rem;color:var(--graphite);line-height:1.45}
.terms-wrap a{color:var(--signal);font-weight:600;text-decoration:none}
.terms-wrap a:hover{color:var(--signal-2)}

.step-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:auto;padding-top:6px;}
.btn{border:none;border-radius:9px;padding:12px 20px;min-width:140px;font-size:.92rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .15s,transform .12s,color .15s,border-color .15s;}
.btn-ghost{background:transparent;border:1.5px solid var(--line);color:var(--graphite);}
.btn-ghost:hover{border-color:var(--slate)}
.btn-primary{background:var(--signal);color:#fff}
.btn-primary:hover{background:var(--signal-2);transform:translateY(-1px)}
.btn-primary:active{transform:translateY(0)}

.signin-row{text-align:center;font-size:.83rem;color:var(--slate);margin-top:18px}
.signin-row a{color:var(--signal);font-weight:600;text-decoration:none}
.signin-row a:hover{color:var(--signal-2)}

.step{display:none}
.step.active{display:flex;flex-direction:column;flex:1}

@media (max-width:900px){
  .page{grid-template-columns:1fr}
  .ticket-panel{padding:26px 24px 22px}
  .ticket-mock{display:none}
  .ticket-headline{font-size:1.4rem;margin-top:18px}
  .ticket-sub{display:none}
  .form-panel{padding:28px 22px 24px}
}
@media (max-width:480px){
  .grid-2{grid-template-columns:1fr}
}
</style>
</head>
<body>

<div class="page">

  <!-- ============ LEFT: BRAND / TICKET PANEL ============ -->
  <aside class="ticket-panel">
    <div class="brand">
      <div class="brand-icon"><img src="../images/logo-gs-removebg-preview.png" alt="KaAyos Logo"></div>
      <span class="brand-name">KaAyos</span>
    </div>

    <h1 class="ticket-headline" style="margin-top:0">Every fixed pipe starts as <em>one ticket</em>, matched to the right hands.</h1>
    <div class="ticket-content">
    <p class="ticket-sub">Create an account to request trusted help around the house, or get matched with jobs in your trade.</p>

    <div class="ticket-mock" aria-hidden="true">
      <div class="ticket-mock-head">
        <span class="tag">Work Order</span>
        <span class="ticket-mock-status"><span class="dot"></span>Matched</span>
      </div>
      <div class="trade-list">
        <div class="trade-row"><i class="fa-solid fa-wrench trade-icon"></i><span>Plumbing</span><i class="fa-solid fa-check check"></i></div>
        <div class="trade-row"><i class="fa-solid fa-bolt trade-icon"></i><span>Electrical</span><i class="fa-solid fa-check check"></i></div>
        <div class="trade-row"><i class="fa-solid fa-hammer trade-icon"></i><span>Carpentry</span><i class="fa-solid fa-check check"></i></div>
        <div class="trade-row"><i class="fa-solid fa-paint-roller trade-icon"></i><span>Painting</span><i class="fa-solid fa-check check"></i></div>
        <div class="trade-row"><i class="fa-solid fa-wind trade-icon"></i><span>Aircon Services</span><i class="fa-solid fa-check check"></i></div>
        <div class="trade-row"><i class="fa-solid fa-fire-flame-simple trade-icon"></i><span>Welding</span><i class="fa-solid fa-check check"></i></div>
      </div>
      <div class="ticket-stub">
        <div class="stub-item"><i class="fa-solid fa-id-card"></i>ID-verified workers</div>
        <div class="stub-item"><i class="fa-solid fa-shield-halved"></i>Secure registration</div>
        <div class="stub-item"><i class="fa-solid fa-handshake"></i>Free to join</div>
      </div>
    </div>
    </div>
  </aside>

  <!-- ============ RIGHT: FORM PANEL ============ -->
  <main class="form-panel">
    <div class="form-inner">

      <div class="step-meta">
        <span class="step-count">STEP <b id="stepNow">01</b> / 03</span>
        <div class="step-track">
          <div class="step-seg current" data-seg="1"></div>
          <div class="step-seg" data-seg="2"></div>
          <div class="step-seg" data-seg="3"></div>
        </div>
      </div>

      <form id="registerForm" method="POST" action="{{ route('register') }}">
        @csrf
        <input type="hidden" name="role" id="roleInput" value="client">

        <!-- STEP 1: ROLE -->
        <section class="step active" data-step="1">
          <div class="eyebrow">Get started</div>
          <h2 class="step-title">Who's this account for?</h2>
          <p class="step-sub">This decides what we ask you next — pick the one that fits.</p>

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
        </section>

        <!-- STEP 2: DETAILS -->
        <section class="step" data-step="2">
          <div class="eyebrow">Tell us about you</div>
          <h2 class="step-title">Your details</h2>
          <p class="step-sub">We'll use this to set up your profile and keep your account secure.</p>

          <div class="grid-2">
            <div class="field">
              <label for="first_name">First name</label>
              <div class="input-wrap">
                <i class="fa-solid fa-user icon"></i>
                <input type="text" id="first_name" name="first_name" placeholder="Juan" required autocomplete="given-name">
              </div>
            </div>
            <div class="field">
              <label for="last_name">Last name</label>
              <div class="input-wrap">
                <i class="fa-solid fa-user icon"></i>
                <input type="text" id="last_name" name="last_name" placeholder="dela Cruz" required autocomplete="family-name">
              </div>
            </div>
          </div>

          <div class="field">
            <label for="email">Email address</label>
            <div class="input-wrap">
              <i class="fa-regular fa-envelope icon"></i>
              <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email">
            </div>
          </div>

          <div class="field">
            <label for="phone">Phone number</label>
            <div class="input-wrap">
              <i class="fa-solid fa-phone icon"></i>
              <input type="text" id="phone" name="phone" placeholder="09XX XXX XXXX" autocomplete="tel">
            </div>
          </div>

          <div class="worker-block" id="workerBlock">
            <div class="tag-label"><i class="fa-solid fa-hard-hat"></i>Worker details</div>
            <div class="field">
              <label for="service_category">Service category</label>
              <div class="input-wrap">
                <i class="fa-solid fa-briefcase icon"></i>
                <select id="service_category" name="service_category">
                  <option value="" disabled selected>Select your trade</option>
                  <option value="plumbing">Plumbing</option>
                  <option value="electrical">Electrical</option>
                  <option value="carpentry">Carpentry</option>
                  <option value="painting">Painting</option>
                  <option value="aircon">Aircon Services</option>
                  <option value="cleaning">Cleaning</option>
                  <option value="roofing">Roofing</option>
                  <option value="welding">Welding</option>
                  <option value="gardening">Gardening</option>
                  <option value="other">Other</option>
                </select>
                <i class="fa-solid fa-chevron-down select-arrow"></i>
              </div>
            </div>
            <div class="field">
              <label for="city">City / Municipality</label>
              <div class="input-wrap">
                <i class="fa-solid fa-location-dot icon"></i>
                <input type="text" id="city" name="city" placeholder="e.g. Quezon City">
              </div>
            </div>
          </div>
        </section>

        <!-- STEP 3: SECURITY -->
        <section class="step" data-step="3">
          <div class="eyebrow">Last step</div>
          <h2 class="step-title">Lock it down</h2>
          <p class="step-sub">Choose a password you'll remember — you'll use it every time you sign in.</p>

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

      <p class="signin-row">Already have an account? <a href={{ route('login') }}>Sign in instead</a></p>
    </div>
  </main>

</div>

<script>
let currentStep = 1;
const totalSteps = 3;

function setRole(role){
  document.getElementById('roleInput').value = role;
  document.getElementById('roleClient').classList.toggle('active', role==='client');
  document.getElementById('roleWorker').classList.toggle('active', role==='worker');
  document.getElementById('workerBlock').classList.toggle('show', role==='worker');
  document.querySelectorAll('#workerBlock select, #workerBlock input').forEach(el=> el.required = (role==='worker'));
}

function goToStep(n){
  document.querySelectorAll('.step').forEach(s =>
    s.classList.toggle('active', Number(s.dataset.step) === n)
  );
  document.querySelectorAll('.step-seg').forEach(seg=>{
    const i = Number(seg.dataset.seg);
    seg.classList.remove('done','current');
    if(i < n) seg.classList.add('done');
    if(i === n) seg.classList.add('current');
  });
  document.getElementById('stepNow').textContent = String(n).padStart(2,'0');
  const backBtn = document.getElementById('backBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  if(n === 1){
    backBtn.style.visibility = 'hidden';
    nextBtn.style.display = 'flex';
    submitBtn.style.display = 'none';
  }
  else if(n === 2){
    backBtn.style.visibility = 'visible';
    nextBtn.style.display = 'flex';
    submitBtn.style.display = 'none';
  }
  else if(n === 3){
    backBtn.style.visibility = 'visible';
    nextBtn.style.display = 'none';
    submitBtn.style.display = 'flex';
  }
  currentStep = n;
}

function currentStepEl(){ return document.querySelector('.step[data-step="'+currentStep+'"]'); }

function validateStep(){
  const inputs = currentStepEl().querySelectorAll('input:not([type=hidden]), select');
  for(const el of inputs){
    if(el.offsetParent !== null && !el.checkValidity()){ el.reportValidity(); return false; }
  }
  return true;
}

document.getElementById('nextBtn').addEventListener('click', ()=>{
  if(!validateStep()) return;
  if(currentStep < totalSteps) goToStep(currentStep+1);
});
document.getElementById('backBtn').addEventListener('click', ()=>{
  if(currentStep > 1) goToStep(currentStep-1);
});

function makeToggle(btnId, iconId, inputId){
  document.getElementById(btnId).addEventListener('click', ()=>{
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
  });
}
makeToggle('togBtn1','togIcon1','password');
makeToggle('togBtn2','togIcon2','password_confirmation');

function checkStrength(val){
  const fill = document.getElementById('strengthFill');
  let score = 0;
  if(val.length>=8) score++;
  if(/[A-Z]/.test(val)) score++;
  if(/[a-z]/.test(val)) score++;
  if(/[0-9]/.test(val)) score++;
  if(/[^A-Za-z0-9]/.test(val)) score++;
  const widths = ['0%','20%','40%','60%','80%','100%'];
  const colors = ['transparent','#e57373','#e57373','#ffb74d','#81c784','#43a047'];
  fill.style.width = widths[score];
  fill.style.background = colors[score];
}
</script>
</body>
</html>