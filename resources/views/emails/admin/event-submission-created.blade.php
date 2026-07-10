<!DOCTYPE html>
<html>
<head>
    <title>Pengajuan Event Baru Perlu Review</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f5f7; padding: 20px; margin: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-top: 4px solid #ccff00;">
        <h2 style="color: #0f172a; margin-top: 0; font-size: 20px;">Ada Pengajuan Event Lari Baru!</h2>
        
        <p style="color: #475569; line-height: 1.6; font-size: 14px;">
            Halo Admin, seorang kontributor baru saja mengajukan event lari baru ke platform RuangLari. Mohon lakukan review detail dan berikan persetujuan atau penolakan.
        </p>

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #0f172a; font-size: 16px; border-b: 1px solid #cbd5e1; padding-bottom: 8px;">Detail Event:</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; color: #334155;">
                <tr>
                    <td style="padding: 6px 0; font-weight: bold; width: 130px; vertical-align: top;">Nama Event:</td>
                    <td style="padding: 6px 0; color: #0f172a;">{{ $submission->event_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-weight: bold; vertical-align: top;">Tanggal:</td>
                    <td style="padding: 6px 0;">{{ $submission->event_date ? $submission->event_date->format('d M Y') : '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-weight: bold; vertical-align: top;">Lokasi:</td>
                    <td style="padding: 6px 0;">{{ $submission->location_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-weight: bold; vertical-align: top;">Kota:</td>
                    <td style="padding: 6px 0;">{{ $submission->city_text ?: optional($submission->city)->name ?: '-' }}</td>
                </tr>
                <tr>
                    <td style="padding: 6px 0; font-weight: bold; vertical-align: top;">Kontributor:</td>
                    <td style="padding: 6px 0;">{{ $submission->contributor_name }} ({{ $submission->contributor_email }})</td>
                </tr>
            </table>
        </div>

        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ route('admin.event-submissions.show', $submission) }}" style="background-color: #0f172a; color: #ccff00; text-decoration: none; padding: 12px 24px; font-weight: bold; border-radius: 6px; font-size: 14px; display: inline-block; box-shadow: 0 4px 6px rgba(15,23,42,0.15);">
                Review Pengajuan
            </a>
        </div>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        <p style="font-size: 12px; color: #94a3b8; text-align: center; margin: 0;">
            Email ini dikirim secara otomatis oleh sistem RuangLari.
        </p>
    </div>
</body>
</html>
