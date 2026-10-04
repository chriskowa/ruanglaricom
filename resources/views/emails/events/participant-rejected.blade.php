<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran Event - {{ $event->name }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0f172a; margin: 0; padding: 24px 12px; color: #e2e8f0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #1e293b; padding: 32px 24px; border-radius: 12px; border: 1px solid #334155; }
        .header { text-align: center; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #334155; }
        .brand-text { color: #ccff00; font-size: 20px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; }
        .title { color: #ffffff; font-size: 18px; font-weight: 700; margin-top: 0; margin-bottom: 12px; }
        .text { color: #cbd5e1; line-height: 1.6; font-size: 14px; margin-bottom: 14px; }
        .alert-box { background-color: #1a1622; border: 1px solid #7f1d1d; border-left: 4px solid #ef4444; padding: 16px; border-radius: 8px; margin: 20px 0; }
        .alert-label { font-size: 11px; font-weight: 800; color: #f87171; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px; }
        .alert-content { color: #ffffff; font-size: 14px; line-height: 1.5; white-space: pre-line; }
        .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; background-color: #0f172a; border-radius: 8px; overflow: hidden; border: 1px solid #334155; }
        .details-table td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid #1e293b; }
        .details-table tr:last-child td { border-bottom: 0; }
        .details-label { color: #94a3b8; font-weight: 600; width: 40%; }
        .details-value { color: #ffffff; font-weight: 700; }
        .btn { display: inline-block; background-color: #334155; color: #ffffff; font-weight: 700; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-size: 13px; border: 1px solid #475569; }
        .footer { border-top: 1px solid #334155; margin-top: 28px; padding-top: 20px; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5; }
        @media only screen and (max-width: 600px) {
            .container { padding: 20px 16px !important; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @php
                $eventLogoSrc = null;
                if (!empty($event->logo_image)) {
                    if (str_starts_with($event->logo_image, 'http://') || str_starts_with($event->logo_image, 'https://')) {
                        $eventLogoSrc = $event->logo_image;
                    } elseif (isset($message) && file_exists(storage_path('app/public/' . $event->logo_image))) {
                        $eventLogoSrc = $message->embed(storage_path('app/public/' . $event->logo_image));
                    } elseif (isset($message) && file_exists(public_path('storage/' . $event->logo_image))) {
                        $eventLogoSrc = $message->embed(public_path('storage/' . $event->logo_image));
                    } else {
                        $eventLogoSrc = asset('storage/' . ltrim($event->logo_image, '/'));
                    }
                }
            @endphp

            @if($eventLogoSrc)
                <img src="{{ $eventLogoSrc }}" alt="{{ $event->name }}" style="max-height: 60px; max-width: 180px; object-fit: contain;">
            @else
                <span class="brand-text">RUANGLARI</span>
            @endif
        </div>

        <h2 class="title">Halo, {{ $participant->name }}</h2>

        <p class="text">
            Terima kasih atas partisipasi dan antusiasme Anda mendaftar pada event <strong>{{ $event->name }}</strong>.
        </p>

        <p class="text">
            Setelah melalui proses verifikasi dan peninjauan oleh panitia penyelenggara, dengan ini kami memberitahukan bahwa pendaftaran Anda saat ini <strong>belum dapat disetujui (Ditolak)</strong>.
        </p>

        <div class="alert-box">
            <div class="alert-label">Alasan Penolakan:</div>
            <div class="alert-content">{{ $reason }}</div>
        </div>

        <table class="details-table">
            <tr>
                <td class="details-label">Nama Peserta</td>
                <td class="details-value">{{ $participant->name }}</td>
            </tr>
            <tr>
                <td class="details-label">Kategori Lomba</td>
                <td class="details-value">{{ $participant->category->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="details-label">Waktu Pendaftaran</td>
                <td class="details-value">{{ $participant->created_at ? $participant->created_at->format('d M Y, H:i') : '-' }} WIB</td>
            </tr>
            <tr>
                <td class="details-label">Status Terkini</td>
                <td class="details-value" style="color: #f87171;">Ditolak (Rejected)</td>
            </tr>
        </table>

        <p class="text">
            Jika Anda membutuhkan informasi lebih detail atau memiliki pertanyaan terkait proses verifikasi ini, silakan menghubungi panitia penyelenggara melalui kontak resmi pada halaman event.
        </p>

        <div style="text-align: center; margin: 26px 0 10px 0;">
            <a href="{{ url('/events/' . $event->slug) }}" class="btn">Kunjungi Halaman Event</a>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ $event->name }} &bull; RuangLari Platform.<br>
            Email pemberitahuan ini dikirimkan secara otomatis oleh sistem.
        </div>
    </div>
</body>
</html>
