<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f5; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; margin-top: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #1e293b; padding: 30px 20px; text-align: center; }
        .header-title { color: #ffffff; font-size: 24px; font-weight: bold; margin: 0; }
        .header-subtitle { color: #94a3b8; font-size: 14px; margin-top: 5px; }
        .content { padding: 30px 20px; }
        .headline { font-size: 20px; font-weight: bold; color: #0f172a; margin-top: 0; margin-bottom: 15px; }
        .body-text { font-size: 15px; color: #334155; margin-bottom: 25px; white-space: pre-wrap; }
        .info-card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 25px; }
        .info-row { margin-bottom: 8px; font-size: 14px; }
        .info-label { font-weight: bold; color: #475569; display: inline-block; width: 120px; }
        .info-value { color: #0f172a; }
        .cta-container { text-align: center; margin-top: 30px; margin-bottom: 10px; }
        .cta-button { display: inline-block; background-color: #2563eb; color: #ffffff; font-weight: bold; text-decoration: none; padding: 12px 25px; border-radius: 6px; font-size: 16px; }
        .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="header-title">{{ $event->name }}</h1>
            @if($event->start_at)
                <div class="header-subtitle">{{ $event->start_at->format('d M Y, H:i') }}</div>
            @endif
        </div>
        
        <div class="content">
            @if(!empty($contentData['headline']))
                <h2 class="headline">{{ str_replace(['{{name}}', '{{bib}}'], [$participant->name, $participant->bib_number], $contentData['headline']) }}</h2>
            @else
                <h2 class="headline">Halo {{ $participant->name }},</h2>
            @endif

            @if(!empty($contentData['body_text']))
                <div class="body-text">{{ str_replace(['{{name}}', '{{bib}}'], [$participant->name, $participant->bib_number], $contentData['body_text']) }}</div>
            @endif

            @if($preset === 'reminder' || $preset === 'info')
                <div class="info-card">
                    <div class="info-row"><span class="info-label">Kategori:</span> <span class="info-value">{{ $participant->category ? $participant->category->name : '-' }}</span></div>
                    <div class="info-row"><span class="info-label">No. BIB:</span> <span class="info-value">{{ $participant->bib_number ?? 'Belum ada' }}</span></div>
                    <div class="info-row"><span class="info-label">Lokasi:</span> <span class="info-value">{{ $event->location_name ?? '-' }}</span></div>
                </div>
            @endif

            @if(!empty($contentData['cta_url']) && !empty($contentData['cta_text']))
                <div class="cta-container">
                    <a href="{{ $contentData['cta_url'] }}" class="cta-button">{{ $contentData['cta_text'] }}</a>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>Pesan ini dikirim secara otomatis oleh sistem RuangLari untuk peserta {{ $event->name }}.</p>
            <p>&copy; {{ date('Y') }} RuangLari. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
