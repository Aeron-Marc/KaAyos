@php
    // $retryAfter is provided by the registration throttle response callback.
    // It is missing when Laravel renders this view for other 429 responses.
    $seconds = isset($retryAfter) ? max(1, (int) $retryAfter) : null;
    $minutes = $seconds ? intdiv($seconds, 60) : 0;
    $rest    = $seconds ? $seconds % 60 : 0;
    $human   = null;

    if ($minutes > 0) {
        $human = $minutes.' minute'.($minutes === 1 ? '' : 's');
        $human .= $rest > 0 ? ' '.$rest.' second'.($rest === 1 ? '' : 's') : '';
    } elseif ($seconds) {
        $human = $seconds.' second'.($seconds === 1 ? '' : 's');
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KaAyos – Too many attempts</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#042C53;--ink-2:#0C447C;--signal:#1A6FC4;--paper:#FBF9F5;--graphite:#202B36;--slate:#6E7A88;--line:#E3DED2}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;font-family:'Inter',system-ui,-apple-system,sans-serif;color:var(--graphite);background:linear-gradient(135deg,var(--ink) 0%,#062d52 50%,var(--ink-2) 100%)}
.card{width:100%;max-width:470px;background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:32px;text-align:center;box-shadow:0 24px 60px rgba(0,0,0,.28)}
.badge{display:inline-flex;align-items:center;gap:8px;font-size:.72rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8a5a00;background:#FDF3E1;border:1px solid #F3DDB4;border-radius:999px;padding:6px 12px;margin-bottom:16px}
h1{font-size:1.35rem;line-height:1.3;margin-bottom:10px;color:var(--ink)}
p{font-size:.92rem;line-height:1.6;color:var(--slate)}
.delay{margin:18px 0;padding:12px 16px;border-radius:10px;background:#fff;border:1px solid var(--line);font-weight:600;color:var(--ink);font-size:.9rem}
.actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:20px}
a{text-decoration:none;font-weight:600;font-size:.86rem;padding:10px 18px;border-radius:10px;transition:opacity .2s ease}
a:hover{opacity:.88}
a.primary{background:var(--signal);color:#fff}
a.ghost{border:1px solid var(--line);color:var(--ink);background:#fff}
.help{margin-top:18px;font-size:.78rem;color:var(--slate)}
</style>
</head>
<body>
    <main class="card">
        <div class="badge">429 · Too many attempts</div>
        <h1>Please slow down for a moment</h1>
        <p>You've made too many sign-up or sign-in attempts from this device or email address in a short period. This is a security precaution to protect accounts.</p>

        @if($human)
            <div class="delay">You can try again in {{ $human }}.</div>
        @else
            <div class="delay">Please wait a little while before trying again.</div>
        @endif

        <div class="actions">
            <a class="primary" href="{{ url('/register') }}">Back to sign up</a>
            <a class="ghost" href="{{ url('/login') }}">Go to sign in</a>
        </div>

        <p class="help">If you believe this is a mistake, please wait for the timer above to finish and try again.</p>
    </main>
</body>
</html>
