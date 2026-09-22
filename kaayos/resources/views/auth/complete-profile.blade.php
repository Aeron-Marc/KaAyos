<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Complete Your Profile</title>
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
body{font-family:'Inter',sans-serif;color:var(--graphite);background:var(--ink);display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}

.card{background:var(--paper);border-radius:18px;max-width:520px;width:100%;padding:40px 36px;box-shadow:0 20px 60px rgba(0,0,0,.25)}

.card-head{text-align:center;margin-bottom:28px}
.card-eyebrow{font-size:.68rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--signal);margin-bottom:6px;display:flex;align-items:center;justify-content:center;gap:10px}
.card-eyebrow::before,.card-eyebrow::after{content:'';width:30px;height:2px;background:var(--amber);border-radius:1px}
.card-title{font-family:'Archivo',sans-serif;font-weight:800;font-size:1.5rem;color:var(--ink);letter-spacing:-.01em;margin-bottom:6px}
.card-sub{font-size:.88rem;color:var(--slate);line-height:1.5}

.alert-success{background:var(--success-bg);border:1px solid #a3e0c0;border-radius:8px;padding:11px 14px;font-size:.82rem;color:var(--success);margin-bottom:20px;display:flex;align-items:flex-start;gap:8px}
.alert-success i{margin-top:2px;flex-shrink:0}

.alert{background:var(--danger-bg);border:1px solid #f3c6c6;border-radius:8px;padding:11px 14px;font-size:.82rem;color:var(--danger);margin-bottom:20px;display:flex;align-items:flex-start;gap:8px}
.alert i{margin-top:2px;flex-shrink:0}

.user-badge{display:flex;align-items:center;gap:12px;background:var(--sky);border:1px solid #d0e3f5;border-radius:10px;padding:12px 16px;margin-bottom:24px}
.user-badge img{width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.1)}
.user-badge .info{flex:1;min-width:0}
.user-badge .info strong{display:block;font-size:.88rem;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.user-badge .info span{font-size:.76rem;color:var(--slate)}

.field{margin-bottom:18px}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--graphite);margin-bottom:6px}
.field label .req{color:var(--danger)}
.input-wrap{position:relative}
.input-wrap i.icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--slate);font-size:.9rem;pointer-events:none}
select,input[type=text]{
  width:100%;border:1.5px solid var(--line);border-radius:9px;
  padding:12px 14px 12px 40px;font-size:.9rem;font-family:'Inter',sans-serif;
  color:var(--graphite);background:#fff;outline:none;
  transition:border-color .15s,box-shadow .15s;appearance:none;-webkit-appearance:none;
}
select:focus,input[type=text]:focus{border-color:var(--signal);box-shadow:0 0 0 3px rgba(26,111,196,.12)}
select{cursor:pointer;padding-right:38px}
.select-arrow{position:absolute;right:12px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--slate);font-size:.72rem}

.btn{border:none;border-radius:9px;padding:13px 20px;width:100%;font-size:.95rem;font-weight:700;font-family:'Inter',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;transition:background .15s,transform .12s}
.btn-primary{background:var(--signal);color:#fff}
.btn-primary:hover{background:var(--signal-2);transform:translateY(-1px)}
.btn-primary:active{transform:translateY(0)}

.card-foot{text-align:center;margin-top:20px;font-size:.8rem;color:var(--slate)}
.card-foot a{color:var(--signal);font-weight:600;text-decoration:none}
.card-foot a:hover{color:var(--signal-2)}

@media(max-width:480px){
  .card{padding:28px 20px}
}
</style>
</head>
<body>

<div class="card">
  <div class="card-head">
    <div class="card-eyebrow">Almost there</div>
    <h1 class="card-title">Complete your worker profile</h1>
    <p class="card-sub">Just a few more details so clients can find and book you.</p>
  </div>

  @if(session('success'))
  <div class="alert-success"><i class="fa-solid fa-circle-check"></i> {{ session('success') }}</div>
  @endif

  @if($errors->any())
  <div class="alert"><i class="fa-solid fa-circle-exclamation"></i> {{ $errors->first() }}</div>
  @endif

  <div class="user-badge">
    @if($user->avatar)
      <img src="{{ $user->avatar }}" alt="Avatar">
    @else
      <div style="width:40px;height:40px;border-radius:50%;background:var(--signal);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;flex-shrink:0">{{ strtoupper(substr($user->first_name,0,1)) }}</div>
    @endif
    <div class="info">
      <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>
      <span>{{ $user->email }}</span>
    </div>
  </div>

  <form method="POST" action="{{ route('worker.complete-profile.store') }}">
    @csrf

    <div class="field">
      <label>Service category <span class="req">*</span></label>
      <div class="input-wrap">
        <i class="fa-solid fa-briefcase icon"></i>
        <select name="service_category" required>
          <option value="" disabled selected>Select your trade</option>
          <option value="plumbing" {{ old('service_category') === 'plumbing' ? 'selected' : '' }}>Plumbing</option>
          <option value="electrical" {{ old('service_category') === 'electrical' ? 'selected' : '' }}>Electrical</option>
          <option value="carpentry" {{ old('service_category') === 'carpentry' ? 'selected' : '' }}>Carpentry</option>
          <option value="painting" {{ old('service_category') === 'painting' ? 'selected' : '' }}>Painting</option>
          <option value="aircon" {{ old('service_category') === 'aircon' ? 'selected' : '' }}>Aircon Services</option>
          <option value="cleaning" {{ old('service_category') === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
          <option value="roofing" {{ old('service_category') === 'roofing' ? 'selected' : '' }}>Roofing</option>
          <option value="welding" {{ old('service_category') === 'welding' ? 'selected' : '' }}>Welding</option>
          <option value="gardening" {{ old('service_category') === 'gardening' ? 'selected' : '' }}>Gardening</option>
          <option value="other" {{ old('service_category') === 'other' ? 'selected' : '' }}>Other</option>
        </select>
        <i class="fa-solid fa-chevron-down select-arrow"></i>
      </div>
      @error('service_category')<div style="font-size:.74rem;color:var(--danger);margin-top:4px">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label>Barangay <span style="font-weight:500;color:var(--slate)">(Tuy, Batangas)</span> <span class="req">*</span></label>
      <div class="input-wrap">
        <i class="fa-solid fa-location-dot icon"></i>
        <select name="barangay" required>
          <option value="" disabled selected>Select your barangay</option>
          @foreach($barangays as $barangay)
            <option value="{{ $barangay }}" {{ old('barangay') === $barangay ? 'selected' : '' }}>{{ $barangay }}</option>
          @endforeach
        </select>
        <i class="fa-solid fa-chevron-down select-arrow"></i>
      </div>
      @error('barangay')<div style="font-size:.74rem;color:var(--danger);margin-top:4px">{{ $message }}</div>@enderror
    </div>

    <div class="field">
      <label>Phone number <span style="font-weight:500;color:var(--slate)">(optional)</span></label>
      <div class="input-wrap">
        <i class="fa-solid fa-phone icon"></i>
        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="09XX XXX XXXX">
      </div>
      @error('phone')<div style="font-size:.74rem;color:var(--danger);margin-top:4px">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Complete Profile</button>
  </form>

  <div class="card-foot">
    <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Sign out and use a different account</a>
    <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
  </div>
</div>

</body>
</html>
