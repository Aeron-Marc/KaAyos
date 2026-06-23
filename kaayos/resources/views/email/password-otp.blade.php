<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KaAyos Password Change Code</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f4f6fb;
            padding: 40px 16px;
            color: #374151;
        }
        .wrapper {
            max-width: 440px;
            margin: 0 auto;
        }
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
            margin-bottom: 8px;
        }
        .otp-box {
            display: inline-block;
            font-size: 40px;
            font-weight: 700;
            letter-spacing: 12px;
            color: #0f2044;
            background: #f0f5ff;
            border: 1px solid #c7d9f8;
            border-radius: 10px;
            padding: 16px 28px;
            margin: 20px 0;
        }
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

            <p class="heading">Your password change verification code:</p>

            <div class="otp-box">{{ $otp }}</div>

            <p class="note">
                This code expires in <strong>10 minutes</strong>.<br>
                Do not share this code with anyone.
            </p>

            <hr class="divider">

            <p class="footer">
                If you did not request a password change,<br>
                you can safely ignore this email.<br><br>
                &copy; {{ date('Y') }} KaAyos. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>