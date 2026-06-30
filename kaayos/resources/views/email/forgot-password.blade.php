<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your KaAyos Password</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f4f6fb;
            padding: 40px 16px;
            color: #374151;
        }
        .wrapper { max-width: 440px; margin: 0 auto; }
        .card {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px 36px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .logo {
            font-size: 22px;
            font-weight: 700;
            color: #1e4fa8;
            letter-spacing: -0.5px;
            margin-bottom: 28px;
        }
        .heading {
            font-size: 15px;
            color: #374151;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background: #1a6fc4;
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            padding: 14px 32px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .btn:hover { background: #185fa5; }
        .note {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.7;
            margin-top: 8px;
        }
        .divider {
            border: none;
            border-top: 1px solid #f0f0f0;
            margin: 28px 0 20px;
        }
        .footer {
            font-size: 12px;
            color: #9ca3af;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="logo">KaAyos</div>

            <p class="heading">
                We received a request to reset the password<br>
                for your <strong>KaAyos</strong> account.
            </p>

            <a href="{{ $url }}" class="btn" style="display: inline-block; background: #1a6fc4; color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 32px; border-radius: 8px; margin: 20px 0;">Reset Password</a>

            <p class="note">
                This link expires in <strong>60 minutes</strong>.<br>
                If you did not request a password reset, you can safely ignore this email.
            </p>

            <hr class="divider">

            <p class="footer">
                If the button above doesn't work, copy and paste this URL into your browser:<br>
                <span style="color: #1a6fc4; word-break: break-all;">{{ $url }}</span>
                <br><br>
                &copy; {{ date('Y') }} KaAyos. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
