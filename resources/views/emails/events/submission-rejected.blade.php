<!DOCTYPE html>
<html>
<head>
    <title>Status Pengajuan Event Lari</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="color: #1e293b; margin-top: 0;">Halo, {{ $submission->contributor_name ?? 'Pelari' }}!</h2>
        
        <p style="color: #475569; line-height: 1.6;">
            Terima kasih telah berkontribusi dan mengajukan event lari <strong>{{ $submission->event_name }}</strong> ke RuangLari.
        </p>

        <p style="color: #475569; line-height: 1.6;">
            Setelah dilakukan proses peninjauan oleh tim admin kami, dengan menyesal kami informasikan bahwa pengajuan event Anda saat ini <strong>belum dapat disetujui (ditolak)</strong> karena alasan berikut:
        </p>

        <div style="background-color: #fef2f2; border: 1px solid #fecaca; padding: 15px; border-radius: 6px; margin: 20px 0; color: #991b1b; line-height: 1.6;">
            <h4 style="margin-top: 0; color: #7f1d1d;">Catatan Reviewer / Alasan Penolakan:</h4>
            <p style="margin: 5px 0; font-style: italic; white-space: pre-line;">{{ $submission->review_note }}</p>
        </div>

        <p style="color: #475569; line-height: 1.6;">
            Anda dipersilakan untuk melakukan perbaikan data dan mengajukan kembali event lari tersebut dengan informasi yang lebih lengkap dan sesuai panduan.
        </p>

        <p style="color: #475569; line-height: 1.6;">
            Jika ada pertanyaan lebih lanjut, silakan hubungi tim kami atau balas email ini.
        </p>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        <p style="font-size: 12px; color: #94a3b8; text-align: center;">
            &copy; {{ date('Y') }} RuangLari. All rights reserved.
        </p>
    </div>
</body>
</html>
