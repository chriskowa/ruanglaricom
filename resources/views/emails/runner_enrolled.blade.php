<!DOCTYPE html>
<html>
<head>
    <title>Pendaftaran Program Latihan</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #1e293b; margin-top: 0;">Halo, {{ $runner->name }}!</h2>
        
        <p style="color: #475569; line-height: 1.6;">
            Kabar baik! Anda telah didaftarkan ke dalam program latihan <strong>{{ $program->title }}</strong> oleh Pelatih Anda di RuangLari.
        </p>

        @if($cleartextPassword)
        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <h4 style="margin-top: 0; color: #0f172a;">Informasi Akun Anda:</h4>
            <p style="margin: 5px 0; color: #475569;"><strong>Email/Username:</strong> {{ $runner->email }}</p>
            <p style="margin: 5px 0; color: #475569;"><strong>Password:</strong> {{ $cleartextPassword }}</p>
            <p style="margin-top: 10px; font-size: 12px; color: #64748b;">* Harap segera ganti password Anda setelah login pertama kali.</p>
        </div>
        @endif

        <p style="color: #475569; line-height: 1.6;">
            Anda dapat langsung masuk ke dashboard Anda tanpa harus mengetikkan password dengan mengklik tautan ajaib (Magic Link) di bawah ini. Tautan ini aman dan hanya berlaku selama 7 hari.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $magicLink }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold;">Login Otomatis (Magic Link)</a>
        </div>

        <p style="color: #475569; line-height: 1.6;">
            Atau login secara manual melalui <a href="{{ url('/login') }}" style="color: #2563eb;">{{ url('/login') }}</a>
        </p>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        <p style="font-size: 12px; color: #94a3b8; text-align: center;">
            &copy; {{ date('Y') }} RuangLari. All rights reserved.
        </p>
    </div>
</body>
</html>
