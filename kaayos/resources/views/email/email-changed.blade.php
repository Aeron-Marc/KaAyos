<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Address Changed</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background: #f4f4f4; padding: 40px 20px;">
    <div style="max-width: 480px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 32px;">
        <h1 style="font-size: 20px; margin: 0 0 16px;">Email Address Changed</h1>
        <p style="color: #555; line-height: 1.6; margin: 0 0 12px;">
            Your KaAyos account email address has been updated.
        </p>
        <p style="color: #555; line-height: 1.6; margin: 0 0 8px;">
            <strong>Previous:</strong> {{ $oldEmail }}
        </p>
        <p style="color: #555; line-height: 1.6; margin: 0 0 20px;">
            <strong>New:</strong> {{ $newEmail }}
        </p>
        <p style="color: #c0392b; font-size: 14px; line-height: 1.6; margin: 0;">
            If you did not make this change, please contact support immediately at
            <a href="mailto:support@kaayos.ph" style="color: #2563eb;">support@kaayos.ph</a>.
        </p>
    </div>
</body>
</html>
