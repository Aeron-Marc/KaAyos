<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Forgot Password</title>
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
html,body{height:100%;font-family:'Inter',sans-serif}
body{
    background:var(--b9);
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    min-height:100vh;
    padding:24px;
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
.wrap{position:relative;z-index:1;width:100%;max-width:420px}
.brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:28px; text-decoration: none;}
.brand-icon img{width:40px;height:40px;background:var(--b6);border-radius:9px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px; object-fit: contain;}
.brand-name{font-size:1.6rem;font-weight:700;color:#fff;letter-spacing:.02em}
.card{background:#fff;border-radius:16px;padding:36px 36px 32px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,.35)}
.card-eyebrow{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--b6);margin-bottom:6px}
.card-title{font-size:1.45rem;font-weight:700;color:var(--b9);margin-bottom:4px}
.card-sub{font-size:.875rem;color:var(--g4);margin-bottom:28px}
.alert{background:#fde8e8;border:1px solid #f7c1c1;border-radius:8px;padding:11px 14px;font-size:.85rem;color:#a32d2d;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.success{background:#d6f5e8;border:1px solid #a3e0c0;border-radius:8px;padding:11px 14px;font-size:.85rem;color:#1a6852;margin-bottom:20px;display:flex;align-items:center;gap:8px}
label{display:block;font-size:.82rem;font-weight:600;color:var(--g7);margin-bottom:6px}
.input-wrap{position:relative;margin-bottom:18px}
.input-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--g4);font-size:.9rem;pointer-events:none}
input[type=email]{width:100%;border:1.5px solid var(--g1);border-radius:8px;padding:11px 14px 11px 38px;font-size:.95rem;font-family:'Inter',sans-serif;color:var(--g9);background:var(--off);outline:none;transition:border-color .18s,box-shadow .18s}
input:focus{border-color:var(--b4);box-shadow:0 0 0 3px rgba(55,138,221,.12)}
input::placeholder{color:var(--g4)}
.btn-submit{width:100%;background:var(--b6);color:#fff;border:none;border-radius:8px;padding:13px;font-size:1rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:background .18s,transform .15s;display:flex;align-items:center;justify-content:center;gap:9px}
.btn-submit:hover{background:var(--b7);transform:translateY(-1px)}
.btn-submit:active{transform:translateY(0)}
.back-row{text-align:center;margin-top:22px}
.back-row a{color:var(--b6);font-weight:600;text-decoration:none;font-size:.875rem;transition:color .18s}
.back-row a:hover{color:var(--b8)}
.trust-row{display:flex;align-items:center;justify-content:center;gap:20px;margin-top:22px;flex-wrap:wrap}
.trust-item{display:flex;align-items:center;gap:6px;font-size:.75rem;color:rgba(255,255,255,.5)}
.trust-item i{font-size:.8rem;color:var(--b2)}
@media(max-width:480px){.card{padding:28px 22px 24px}}
</style>
</head>
<body>

<div class="wrap">

    <a href="{{ route('home') }}" class="brand">
        <div class="brand">
            <div class="brand-icon"><img src="../images/logo-gs-removebg-preview.png" alt="KaAyos Logo"></div>
            <span class="brand-name">KaAyos</span>
        </div>
    </a>

    <div class="card">

        <div class="card-eyebrow">Password reset</div>
        <div class="card-title">Forgot your password?</div>
        <div class="card-sub">Enter your email and we'll send you a reset link</div>

        @if(session('status'))
        <div class="success">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div>
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <i class="fa-regular fa-envelope" aria-hidden="true"></i>
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
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                Send Reset Link
            </button>
        </form>

        <div class="back-row">
            <a href="{{ route('login') }}">&larr; Back to sign in</a>
        </div>

    </div>

    <div class="trust-row">
        <div class="trust-item"><i class="fa-solid fa-id-card"></i> ID-verified workers</div>
        <div class="trust-item"><i class="fa-solid fa-shield-halved"></i> Secure login</div>
    </div>

</div>

</body>
</html>
