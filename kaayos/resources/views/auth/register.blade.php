<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Register</title>
<link rel="icon" href="../images/KaAyos_logo.jpeg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --b9:#042C53;--b8:#0C447C;--b7:#185FA5;--b6:#1A6FC4;--b4:#378ADD;--b2:#85B7EB;--b1:#B5D4F4;--b0:#E6F1FB;
  --g9:#1B2430;--g7:#3D4A56;--g4:#8C97A4;--g1:#E8ECF0;--white:#fff;--off:#F7F8FA;
}
html,body{height:100%;font-family:'Inter',sans-serif;overflow:hidden}
body{
    background:var(--b9);
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    height:100vh;
    padding:16px;
}
body::before{
    content:'';
    position:fixed;
    inset:0;
    background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);
    background-size:28px 28px;
    pointer-events:none;
    z-index:0;
}
.wrap{
    position:relative;z-index:1;width:100%;max-width:480px;
    display:flex;flex-direction:column;
    height:100%;max-height:100vh;
}
.brand{display:flex;align-items:center;justify-content:center;gap:8px;padding:10px 0 8px;flex-shrink:0}
.brand-icon{width:32px;height:32px;background:var(--b6);border-radius:7px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:15px}
.brand-name{font-size:1.3rem;font-weight:700;color:#fff;letter-spacing:.02em}

/* Card fills remaining space, scrolls internally */
.card{
    background:#fff;border-radius:16px;padding:20px 28px 18px;width:100%;
    box-shadow:0 24px 60px rgba(0,0,0,.35);
    flex:1;min-height:0;
    overflow-y:auto;
    scrollbar-width:thin;scrollbar-color:var(--g1) transparent;
}
.card::-webkit-scrollbar{width:4px}
.card::-webkit-scrollbar-thumb{background:var(--g1);border-radius:2px}

.card-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--b6);margin-bottom:3px}
.card-title{font-size:1.25rem;font-weight:700;color:var(--b9);margin-bottom:2px}
.card-sub{font-size:.82rem;color:var(--g4);margin-bottom:14px}

/* Role selector */
.role-toggle{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:14px}
.role-btn{display:flex;flex-direction:row;align-items:center;gap:8px;padding:9px 12px;border:2px solid var(--g1);border-radius:9px;cursor:pointer;transition:border-color .18s,background .18s;background:var(--off)}
.role-btn i{font-size:1rem;color:var(--g4);transition:color .18s;flex-shrink:0}
.role-btn .role-text{display:flex;flex-direction:column;gap:1px}
.role-btn span{font-size:.8rem;font-weight:600;color:var(--g7);transition:color .18s}
.role-btn small{font-size:.68rem;color:var(--g4);line-height:1.2;transition:color .18s}
.role-btn.active{border-color:var(--b6);background:var(--b0)}
.role-btn.active i{color:var(--b6)}
.role-btn.active span{color:var(--b7)}
.role-btn.active small{color:var(--b4)}
.role-btn:hover:not(.active){border-color:var(--b2);background:#f4f8fd}

/* Section divider */
.section-label{font-size:.68rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--g4);margin:10px 0 8px;display:flex;align-items:center;gap:8px}
.section-label::after{content:'';flex:1;height:1px;background:var(--g1)}

.alert{background:#fde8e8;border:1px solid #f7c1c1;border-radius:8px;padding:8px 12px;font-size:.82rem;color:#a32d2d;margin-bottom:12px;display:flex;align-items:center;gap:8px}
label{display:block;font-size:.78rem;font-weight:600;color:var(--g7);margin-bottom:4px}
.input-wrap{position:relative;margin-bottom:10px}
.input-wrap i.icon{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--g4);font-size:.85rem;pointer-events:none}
input[type=email],input[type=password],input[type=text],select{width:100%;border:1.5px solid var(--g1);border-radius:8px;padding:8px 12px 8px 34px;font-size:.88rem;font-family:'Inter',sans-serif;color:var(--g9);background:var(--off);outline:none;transition:border-color .18s,box-shadow .18s;appearance:none;-webkit-appearance:none}
input:focus,select:focus{border-color:var(--b4);box-shadow:0 0 0 3px rgba(55,138,221,.12)}
input::placeholder{color:var(--g4)}
select{cursor:pointer;padding-right:34px}
.select-arrow{position:absolute;right:11px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--g4);font-size:.75rem}
.tog{position:absolute;right:11px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--g4);font-size:.85rem;padding:0;transition:color .18s}
.tog:hover{color:var(--b6)}

/* Two-column grid for name fields */
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}

/* Worker-only section hidden by default */
.worker-fields{display:none}
.worker-fields.show{display:block}

/* Password strength */
.strength-bar{height:3px;border-radius:2px;background:var(--g1);margin-top:-8px;margin-bottom:10px;overflow:hidden}
.strength-fill{height:100%;border-radius:2px;width:0;transition:width .3s,background .3s}

/* Terms */
.terms-wrap{display:flex;align-items:flex-start;gap:8px;margin-bottom:14px}
.terms-wrap input[type=checkbox]{width:15px;height:15px;accent-color:var(--b6);cursor:pointer;margin-top:2px;flex-shrink:0}
.terms-wrap span{font-size:.8rem;color:var(--g7);line-height:1.4}
.terms-wrap a{color:var(--b6);font-weight:500;text-decoration:none}
.terms-wrap a:hover{color:var(--b8)}

.btn-register{width:100%;background:var(--b6);color:#fff;border:none;border-radius:8px;padding:11px;font-size:.95rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:background .18s,transform .15s;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-register:hover{background:var(--b7);transform:translateY(-1px)}
.btn-register:active{transform:translateY(0)}
.divider{display:flex;align-items:center;gap:10px;margin:14px 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--g1)}
.divider span{font-size:.75rem;color:var(--g4);font-weight:500;white-space:nowrap}
.login-row{text-align:center;font-size:.83rem;color:var(--g7)}
.login-row a{color:var(--b6);font-weight:600;text-decoration:none;transition:color .18s}
.login-row a:hover{color:var(--b8)}
.trust-row{display:flex;align-items:center;justify-content:center;gap:16px;padding:8px 0 4px;flex-wrap:wrap;flex-shrink:0}
.trust-item{display:flex;align-items:center;gap:5px;font-size:.7rem;color:rgba(255,255,255,.45)}
.trust-item i{font-size:.75rem;color:var(--b2)}
@media(max-width:520px){
    .card{padding:16px 16px 14px}
    .grid-2{grid-template-columns:1fr}
    body{padding:10px}
}
</style>
</head>
<body>

<div class="wrap">

    <div class="brand">
        <div class="brand-icon"><i class="fa-solid fa-house-chimney" aria-hidden="true"></i></div>
        <span class="brand-name">KaAyos</span>
    </div>

    <div class="card">

        <div class="card-eyebrow">Get started</div>
        <div class="card-title">Create your account</div>
        <div class="card-sub">Join KaAyos — find work or hire trusted workers near you</div>

        @if($errors->any())
        <div class="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Role selector --}}
            <input type="hidden" name="role" id="roleInput" value="client">

            <div class="role-toggle">
                <div class="role-btn active" id="roleClient" onclick="setRole('client')">
                    <i class="fa-solid fa-user"></i>
                    <div class="role-text"><span>I'm a Client</span><small>I want to hire workers</small></div>
                </div>
                <div class="role-btn" id="roleWorker" onclick="setRole('worker')">
                    <i class="fa-solid fa-hard-hat"></i>
                    <div class="role-text"><span>I'm a Worker</span><small>I want to offer services</small></div>
                </div>
            </div>

            {{-- Basic info --}}
            <div class="section-label">Basic Information</div>

            <div class="grid-2">
                <div>
                    <label for="first_name">First Name</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user icon" aria-hidden="true"></i>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="Juan"
                            required
                            autocomplete="given-name"
                        >
                    </div>
                </div>
                <div>
                    <label for="last_name">Last Name</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user icon" aria-hidden="true"></i>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            placeholder="dela Cruz"
                            required
                            autocomplete="family-name"
                        >
                    </div>
                </div>
            </div>

            <div>
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fa-regular fa-envelope icon" aria-hidden="true"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="you@example.com"
                        required
                        autocomplete="email"
                    >
                </div>
                @error('email')
                    <div class="alert"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="phone">Phone Number</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-phone icon" aria-hidden="true"></i>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        placeholder="09XX XXX XXXX"
                        autocomplete="tel"
                    >
                </div>
                @error('phone')
                    <div class="alert"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
                @enderror
            </div>

            {{-- Worker-only fields --}}
            <div class="worker-fields" id="workerFields">
                <div class="section-label">Worker Details</div>

                <div>
                    <label for="service_category">Service Category</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-briefcase icon" aria-hidden="true"></i>
                        <select id="service_category" name="service_category">
                            <option value="" disabled selected>Select your trade</option>
                            <option value="plumbing" {{ old('service_category') == 'plumbing' ? 'selected' : '' }}>Plumbing</option>
                            <option value="electrical" {{ old('service_category') == 'electrical' ? 'selected' : '' }}>Electrical</option>
                            <option value="carpentry" {{ old('service_category') == 'carpentry' ? 'selected' : '' }}>Carpentry</option>
                            <option value="painting" {{ old('service_category') == 'painting' ? 'selected' : '' }}>Painting</option>
                            <option value="aircon" {{ old('service_category') == 'aircon' ? 'selected' : '' }}>Aircon Services</option>
                            <option value="cleaning" {{ old('service_category') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                            <option value="roofing" {{ old('service_category') == 'roofing' ? 'selected' : '' }}>Roofing</option>
                            <option value="welding" {{ old('service_category') == 'welding' ? 'selected' : '' }}>Welding</option>
                            <option value="gardening" {{ old('service_category') == 'gardening' ? 'selected' : '' }}>Gardening</option>
                            <option value="other" {{ old('service_category') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <i class="fa-solid fa-chevron-down select-arrow"></i>
                    </div>
                    @error('service_category')
                        <div class="alert"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label for="city">City / Municipality</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-location-dot icon" aria-hidden="true"></i>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city') }}"
                            placeholder="e.g. Quezon City"
                        >
                    </div>
                    @error('city')
                        <div class="alert"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Password --}}
            <div class="section-label">Security</div>

            <div>
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock icon" aria-hidden="true"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Create a strong password"
                        required
                        autocomplete="new-password"
                        oninput="checkStrength(this.value)"
                    >
                    <button type="button" class="tog" id="togBtn1" aria-label="Toggle password visibility">
                        <i class="fa-regular fa-eye" id="togIcon1"></i>
                    </button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                @error('password')
                    <div class="alert"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
                @enderror
            </div>

            <div>
                <label for="password_confirmation">Confirm Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock icon" aria-hidden="true"></i>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Repeat your password"
                        required
                        autocomplete="new-password"
                    >
                    <button type="button" class="tog" id="togBtn2" aria-label="Toggle confirm password visibility">
                        <i class="fa-regular fa-eye" id="togIcon2"></i>
                    </button>
                </div>
            </div>

            <div class="terms-wrap">
                <input type="checkbox" id="terms" name="terms" required>
                <span>
                    I agree to KaAyos'
                    <a href="#">Terms of Service</a>
                    and
                    <a href="#">Privacy Policy</a>
                </span>
            </div>

            <button type="submit" class="btn-register">
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                Create Account
            </button>
        </form>

        <div class="divider"><span>Already have an account?</span></div>

        <div class="login-row">
            <a href="{{ route('login') }}">&larr; Sign in instead</a>
        </div>

    </div>

    <div class="trust-row">
        <div class="trust-item"><i class="fa-solid fa-id-card"></i> ID-verified workers</div>
        <div class="trust-item"><i class="fa-solid fa-shield-halved"></i> Secure registration</div>
        <div class="trust-item"><i class="fa-solid fa-hand-shake"></i> Free to join</div>
    </div>

</div>

<script>
// Role toggle
function setRole(role) {
    document.getElementById('roleInput').value = role;
    document.getElementById('roleClient').classList.toggle('active', role === 'client');
    document.getElementById('roleWorker').classList.toggle('active', role === 'worker');
    document.getElementById('workerFields').classList.toggle('show', role === 'worker');
    // Toggle required on worker fields
    const workerInputs = document.querySelectorAll('#workerFields input, #workerFields select');
    workerInputs.forEach(el => el.required = (role === 'worker'));
}


// Password toggles
function makeToggle(btnId, iconId, inputId) {
    document.getElementById(btnId).addEventListener('click', () => {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        icon.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    });
}
makeToggle('togBtn1', 'togIcon1', 'password');
makeToggle('togBtn2', 'togIcon2', 'password_confirmation');

// Password strength indicator
function checkStrength(val) {
    const fill = document.getElementById('strengthFill');
    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const widths = ['0%','25%','50%','75%','100%'];
    const colors = ['transparent','#e57373','#ffb74d','#81c784','#43a047'];
    fill.style.width  = widths[score];
    fill.style.background = colors[score];
}
</script>
</body>
</html>