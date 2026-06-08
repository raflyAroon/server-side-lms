<!DOCTYPE html>
<html>
<head>
    <title>Kode OTP Verifikasi</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: white; border-radius: 16px; padding: 30px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .code { font-size: 32px; letter-spacing: 8px; font-weight: bold; color: #2563eb; text-align: center; margin: 20px 0; }
        .footer { font-size: 12px; color: #666; text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verifikasi Akun Hackathon MPR RI</h2>
        <p>Halo, gunakan kode OTP berikut untuk menyelesaikan pendaftaran:</p>
        <div class="code">{{ $code }}</div>
        <p>Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun.</p>
        <div class="footer">Hackathon Inovasi Digital MPR RI — © 2026</div>
    </div>
</body>
</html>