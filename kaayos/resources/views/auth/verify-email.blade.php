<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Verify Email</title>
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
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    min-height:100vh;padding:24px;
}
body::before{
    content:'';position:fixed;inset:0;
    background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);
    background-size:28px 28px;pointer-events:none;z-index:0;
}
.wrap{position:relative;z-index:1;width:100%;max-width:460px}
.brand{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:28px;text-decoration:none}
.brand-icon img{width:40px;height:40px;border-radius:9px;display:flex;align-items:center;justify-content:center;object-fit:contain}
.brand-name{font-size:1.6rem;font-weight:700;color:#fff;letter-spacing:.02em}
.card{background:#fff;border-radius:16px;padding:36px 36px 32px;width:100%;box-shadow:0 24px 60px rgba(0,0,0,.35);text-align:center}
.card-icon{width:64px;height:64px;border-radius:50%;background:var(--b0);color:var(--b6);display:flex;align-items:center;justify-content:center;font-size:1.6rem;margin:0 auto 18px}
.card-title{font-size:1.35rem;font-weight:700;color:var(--b9);margin-bottom:8px}
.card-sub{font-size:.88rem;color:var(--g4);line-height:1.55;margin-bottom:24px}
.alert{background:#d6f5e8;border:1px solid #a3e0c0;border-radius:8px;padding:11px 14px;font-size:.85rem;color:#1a6852;margin-bottom:20px;display:flex;align-items:center;justify-content:center;gap:8px}
.alert-error{background:#fde8e8;border:1px solid #f7c1c1;color:#a32d2d}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 28px;border-radius:8px;font-size:.95rem;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all .18s;text-decoration:none;border:none}
.btn-primary{background:var(--b6);color:#fff}
.btn-primary:hover{background:var(--b7);transform:translateY(-1px)}
.btn-primary:active{transform:translateY(0)}
.btn-ghost{background:transparent;border:1.5px solid var(--g1);color:var(--g7)}
.btn-ghost:hover{border-color:var(--b4);color:var(--b6)}
.email-display{font-weight:700;color:var(--b9)}
.resend-note{font-size:.82rem;color:var(--g4);margin-top:16px;line-height:1.5}
.logout-form{display:inline}
.logout-link{background:none;border:none;color:var(--b6);font-weight:600;cursor:pointer;font-family:'Inter',sans-serif;font-size:.82rem;text-decoration:underline;transition:color .18s}
.logout-link:hover{color:var(--b8)}
@media(max-width:480px){.card{padding:28px 22px 24px}}
</style>
</head>
<body>

<div class="wrap">

    <a href="{{ route('home') }}" class="brand">
        <div class="brand-icon"><img src="../images/logo-gs-removebg-preview.png" alt="KaAyos Logo"></div>
        <span class="brand-name">KaAyos</span>
    </a>

    <div class="card">

        @if(session('message'))
            <div class="alert">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('message') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="card-icon">
            <i class="fa-regular fa-envelope"></i>
        </div>
        <div class="card-title">Verify your email</div>
        <div class="card-sub">
            We sent a verification link to<br>
            <span class="email-display">{{ auth()->user()->email }}</span>.<br><br>
            Click the link in the email to activate your account. If you don't see it, check your spam folder.
        </div>

        <form method="POST" action="{{ route('verification.send') }}" style="margin-bottom:12px;">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="fa-regular fa-paper-plane"></i>
                Resend Verification Link
            </button>
        </form>

        <div class="resend-note">
            Didn't receive the email?
            <form method="POST" action="{{ route('verification.send') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-link">Click to resend</button>
            </form>
        </div>

        <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--g1);">
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="btn btn-ghost">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Sign Out
                </button>
            </form>
        </div>

    </div>

</div>

</body>
</html>
