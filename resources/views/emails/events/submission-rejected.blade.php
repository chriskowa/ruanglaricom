<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pengajuan Event Lari - RuangLari</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #0c121e; margin: 0; padding: 24px 12px; color: #e2e8f0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #111724; padding: 32px 24px; border-radius: 12px; border: 1px solid #334155;">
        <div style="text-align: center; margin-bottom: 24px;">
            <span style="color: #ccff00; font-size: 22px; font-weight: 900; letter-spacing: -0.5px; text-transform: uppercase;">RUANGLARI</span>
        </div>

        <h2 style="color: #ffffff; margin-top: 0; font-size: 18px; font-weight: 700;">Halo, {{ $submission->contributor_name ?? 'Pelari' }}</h2>
        
        <p style="color: #cbd5e1; line-height: 1.6; font-size: 14px;">
            Terima kasih telah mengajukan event lari <strong>{{ $submission->event_name }}</strong> ke direktori jadwal lari RuangLari.
        </p>

        <p style="color: #cbd5e1; line-height: 1.6; font-size: 14px;">
            Setelah dilakukan proses peninjauan oleh tim admin, dengan ini kami sampaikan bahwa pengajuan event kamu saat ini <strong>belum dapat disetujui (ditolak)</strong> dengan alasan sebagai berikut:
        </p>

        <div style="background-color: #1a1622; border: 1px solid #7f1d1d; border-left: 4px solid #ef4444; padding: 16px; border-radius: 8px; margin: 20px 0; line-height: 1.6; font-size: 14px;">
            <div style="font-size: 12px; font-weight: 700; color: #f87171; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;">Alasan Penolakan:</div>
            <div style="color: #ffffff; white-space: pre-line;">{{ $submission->review_note }}</div>
        </div>

        <p style="color: #cbd5e1; line-height: 1.6; font-size: 14px;">
            Kamu dapat melengkapi data atau memperbaiki informasi yang dibutuhkan, kemudian mengajukan kembali event tersebut melalui portal Jadwal Lari RuangLari.
        </p>

        <div style="margin: 28px 0; text-align: center;">
            <a href="{{ url('/jadwal-lari?submit=1') }}" style="display: inline-block; background-color: #ccff00; color: #0c121e; font-weight: 800; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px;">Ajukan Ulang Event</a>
        </div>

        <hr style="border: 0; border-top: 1px solid #334155; margin: 24px 0;">
        <p style="font-size: 12px; color: #64748b; text-align: center; margin: 0;">
            &copy; {{ date('Y') }} RuangLari. Email ini dikirim otomatis oleh sistem.
        </p>
    </div>
</body>
</html>
