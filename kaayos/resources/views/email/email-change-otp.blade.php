<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Change Code</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background: #f4f4f4; padding: 40px 20px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 32px;">
        <h1 style="font-size: 20px; margin: 0 0 16px;">Email Change Code</h1>
        <p style="color: #555; line-height: 1.6; margin: 0 0 20px;">
            Use the code below to verify your new email address on KaAyos. This code expires in 10 minutes.
        </p>
        <div style="text-align: center; padding: 24px; background: #f8faff; border-radius: 8px; letter-spacing: 8px; font-size: 32px; font-weight: 700; color: #1e3a8a;">
            {{ $otp }}
        </div>
        <p style="color: #999; font-size: 13px; margin: 20px 0 0;">
            If you did not request this change, you can safely ignore this email.
        </p>
    </div>
</body>
</html>
