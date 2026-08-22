<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Success - Minimalist</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .header { background-color: #ffffff; padding: 25px 30px; text-align: center; border-bottom: 1px solid #f1f5f9; }
        .content { padding: 35px 30px; }
        .event-info { text-align: center; margin-bottom: 25px; }
        .event-name { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
        .event-meta { color: #64748b; font-size: 14px; font-weight: 500; }
        .ticket { border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; margin-bottom: 20px; background-color: #ffffff; }
        .ticket-label { font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 40%; vertical-align: top; }
        .ticket-value { font-size: 14px; font-weight: 600; color: #0f172a; word-wrap: break-word; }
        .badge { background-color: #f1f5f9; color: #0f172a; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; border: 1px solid #cbd5e1; display: inline-block; }
        .qr-code { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9; }
        .qr-code img { border: 1px solid #e2e8f0; border-radius: 6px; padding: 6px; background-color: #ffffff; }
        .footer { background-color: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .btn { display: inline-block; background-color: #0f172a; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; margin-top: 20px; }
        
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; margin: 0 !important; border-radius: 0 !important; }
            .content { padding: 20px !important; }
            .ticket { padding: 16px !important; }
            .ticket-label { font-size: 11px !important; width: 35% !important; }
            .ticket-value { font-size: 13px !important; }
            .btn { display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: center !important; }
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
                <img src="{{ $eventLogoSrc }}" alt="{{ $event->name }}" style="max-height: 70px; max-width: 180px; object-fit: contain;">
            @else
                @php
                    $defaultLogo = 'images/logo-text.png';
                    $fallbackSrc = (isset($message) && file_exists(public_path($defaultLogo)))
                        ? $message->embed(public_path($defaultLogo))
                        : asset($defaultLogo);
                @endphp
                <img src="{{ $fallbackSrc }}" alt="{{ config('app.name') }}" style="max-height: 45px; object-fit: contain;">
            @endif
        </div>
        
        <div class="content">
            <div class="event-info">
                <div class="event-name">{{ $event->name }}</div>
                <div class="event-meta">
                    {{ $event->start_at ? $event->start_at->format('d F Y, H:i') : 'Date TBA' }} | {{ $event->location_name }}
                </div>
            </div>

            @if($event->custom_email_message)
            <div style="margin-bottom: 25px; color: #334155; border-left: 3px solid #0f172a; padding: 12px 16px; background-color: #f8fafc; border-radius: 0 6px 6px 0; font-size: 14px; line-height: 1.6;">
                {!! $event->custom_email_message !!}
            </div>
            @endif

            <p style="text-align: center; margin-bottom: 25px; color: #475569; font-size: 14px; line-height: 1.5;">
                Halo <strong>{{ $notifiableName }}</strong>, pendaftaran Anda untuk event ini telah berhasil dikonfirmasi. Berikut adalah rincian tiket Anda:
            </p>

            @foreach($participants as $participant)
            <div class="ticket">
                <div style="text-align: center; margin-bottom: 18px;">
                    <span class="badge">
                        {{ $participant->category->name ?? 'Participant' }}
                    </span>
                </div>
                
                <table width="100%" cellpadding="6" cellspacing="0" border="0">
                    <tr>
                        <td class="ticket-label">Nama Peserta</td>
                        <td class="ticket-value">{{ $participant->name }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Email</td>
                        <td class="ticket-value">{{ $participant->email }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Telepon</td>
                        <td class="ticket-value">{{ $participant->phone }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Kategori</td>
                        <td class="ticket-value">
                            {{ $participant->category->name ?? '-' }} ({{ $participant->getAgeGroup($event->start_at) }})
                        </td>
                    </tr>

                    @if($event->ticket_email_show_jersey ?? true)
                    <tr>
                        <td class="ticket-label">Jersey</td>
                        <td class="ticket-value">{{ $participant->jersey_size ?? '-' }}</td>
                    </tr>
                    @endif

                    @if($event->ticket_email_show_addons ?? true)
                    <tr>
                        <td class="ticket-label">Addon</td>
                        <td class="ticket-value">
                            @php($addons = is_array($participant->addons) ? $participant->addons : [])
                            @if(count($addons) > 0)
                                {{ collect($addons)->map(function ($a) { if (is_string($a)) { $name = trim($a); return $name !== '' ? $name : null; } if (! is_array($a)) return null; $name = $a['name'] ?? null; $price = $a['price'] ?? null; if (! is_string($name) || trim($name) === '') return null; $name = trim($name); if ($price === null || $price === '') return $name; return $name.' (Rp '.number_format((int) $price, 0, ',', '.').')'; })->filter()->implode(', ') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endif

                    <tr>
                        <td class="ticket-label">Status Bayar</td>
                        <td class="ticket-value" style="color: {{ $transaction->payment_status == 'paid' ? '#059669' : '#d97706' }}">
                            {{ strtoupper($transaction->payment_status) }}
                        </td>
                    </tr>

                    @if(($event->ticket_email_show_pic ?? true) && !empty($transaction->pic_data['name']))
                    <tr>
                        <td class="ticket-label">Nama PIC</td>
                        <td class="ticket-value">{{ $transaction->pic_data['name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Telepon PIC</td>
                        <td class="ticket-value">{{ $transaction->pic_data['phone'] ?? '-' }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td class="ticket-label">No. Registrasi</td>
                        <td class="ticket-value">{{ $transaction->public_ref ?? ('REG-'.$transaction->id) }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">No. Tiket/BIB</td>
                        <td class="ticket-value" style="color: #0f172a; font-family: monospace;">{{ $participant->bib_number ?? 'TICKET-'.$participant->id }}</td>
                    </tr>
                </table>

                <div style="border-top: 1px solid #f1f5f9; padding-top: 12px; margin-top: 12px;">
                    <table width="100%" cellpadding="6" cellspacing="0" border="0">
                        <tr>
                            <td class="ticket-label">Lokasi Event</td>
                            <td class="ticket-value">{{ $event->location_name }}</td>
                        </tr>
                        <tr>
                            <td class="ticket-label">Waktu Event</td>
                            <td class="ticket-value">{{ $event->start_at ? $event->start_at->format('d F Y, H:i') : 'TBA' }}</td>
                        </tr>
                    </table>
                </div>

                @if($event->ticket_email_use_qr ?? true)
                    <div class="qr-code">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=TICKET-{{ $participant->id }}-{{ $participant->transaction_id }}" width="140" height="140" alt="Ticket QR">
                        <p style="font-size: 11px; color: #94a3b8; margin-top: 6px;">Verifikasi E-Tiket #{{ $participant->id }}</p>
                    </div>
                @endif
            </div>
            @endforeach

            @if(isset($customHtml) && !empty($customHtml))
            <div style="margin-top: 25px; margin-bottom: 25px; padding: 18px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #f8fafc; color: #334155; font-size: 14px; line-height: 1.6;">
                {!! $customHtml !!}
            </div>
            @endif

            <div style="text-align: center; margin-top: 25px;">
                <p style="font-size: 14px; color: #64748b; margin: 4px 0;">Total Biaya: <strong style="color: #0f172a;">Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</strong></p>
                <p style="font-size: 14px; color: #64748b; margin: 4px 0;">Status Transaksi: <strong style="color: {{ $transaction->payment_status == 'paid' ? '#059669' : '#d97706' }}">{{ strtoupper($transaction->payment_status) }}</strong></p>
                
                <a href="{{ !empty($event->slug) ? route('events.show', $event->slug) : '#' }}" class="btn">Halaman Detail Event</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            Email konfirmasi pendaftaran otomatis.
        </div>
    </div>
</body>
</html>
