<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 0; }
        .container { max-width: 560px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .header { background: #4f46e5; padding: 28px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; }
        .header p { color: #c7d2fe; margin: 4px 0 0; font-size: 13px; }
        .body { padding: 28px 32px; }
        .body p { color: #374151; font-size: 14px; line-height: 1.7; margin: 0 0 14px; }
        .info-box { background: #f3f4f6; border-radius: 8px; padding: 16px 20px; margin: 20px 0; }
        .info-box p { margin: 0 0 6px; font-size: 13px; color: #6b7280; }
        .info-box .value { font-size: 15px; font-weight: bold; color: #111827; }
        .password-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px 20px; margin: 20px 0; text-align: center; }
        .password-box p { margin: 0 0 6px; font-size: 13px; color: #3b82f6; }
        .password-box .pw { font-size: 22px; font-weight: bold; font-family: monospace; color: #1e3a8a; letter-spacing: 2px; }
        .warning { background: #fef3c7; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #92400e; margin: 16px 0; }
        .footer { background: #f9fafb; padding: 16px 32px; text-align: center; font-size: 12px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .btn { display: inline-block; background: #4f46e5; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: bold; margin: 16px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>HRI System</h1>
            <p>Human Resource Intelligence</p>
        </div>
        <div class="body">
            <p>Yth. <strong>{{ $fullName }}</strong>,</p>
            <p>Akun staf HRI System Anda telah dibuat. Berikut informasi login Anda:</p>

            <div class="info-box">
                <p>Departemen</p>
                <p class="value">{{ $roleLabel }}</p>
                <br>
                <p>Email Login</p>
                <p class="value">{{ $email }}</p>
            </div>

            <div class="password-box">
                <p>Password Sementara</p>
                <p class="pw">{{ $tempPassword }}</p>
            </div>

            <div class="warning">
                ⚠️ Harap ganti password segera setelah login pertama. Password sementara ini hanya berlaku untuk login pertama kali.
            </div>

            <p>Silakan login melalui tautan berikut:</p>
            <a href="{{ config('app.url') }}/staff/login" class="btn">Login ke HRI System</a>

            <p style="margin-top:24px; font-size:13px; color:#6b7280;">
                Jika Anda tidak merasa mendaftar, abaikan email ini atau hubungi administrator.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} HRI System. All rights reserved.
        </div>
    </div>
</body>
</html>