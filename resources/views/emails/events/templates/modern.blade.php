<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Success - Modern</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #0f172a; color: #f8fafc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #1e293b; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.3); border: 1px solid #334155; }
        .header { background-color: #0f172a; padding: 30px; text-align: center; border-b: 2px solid #ccff00; }
        .header h1 { color: #ccff00; margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .content { padding: 30px; }
        .event-info { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #334155; }
        .event-name { font-size: 22px; font-weight: 900; color: #ffffff; margin-bottom: 8px; line-height: 1.3; letter-spacing: -0.5px; }
        .event-meta { color: #ccff00; font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .ticket { border: 2px dashed #475569; border-radius: 12px; padding: 24px; margin-bottom: 24px; background-color: #0f172a; }
        .ticket-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; width: 40%; vertical-align: top; }
        .ticket-value { font-size: 15px; font-weight: 700; color: #ffffff; word-wrap: break-word; }
        .badge { background-color: #ccff00; color: #0f172a; padding: 6px 18px; border-radius: 20px; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; display: inline-block; }
        .qr-code { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 1px dashed #334155; }
        .qr-code img { border: 4px solid #ffffff; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .footer { background-color: #0f172a; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #334155; }
        .btn { display: inline-block; background-color: #ccff00; color: #0f172a; padding: 14px 28px; border-radius: 10px; text-decoration: none; font-weight: 900; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-top: 20px; box-shadow: 0 4px 12px rgba(204,255,0,0.2); }
        
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; margin: 0 !important; border-radius: 0 !important; }
            .content { padding: 20px !important; }
            .ticket { padding: 16px !important; }
            .ticket-label { font-size: 10px !important; width: 35% !important; }
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
                <img src="{{ $eventLogoSrc }}" alt="{{ $event->name }}" style="max-height: 80px; max-width: 200px; object-fit: contain;">
            @else
                @php
                    $defaultLogo = 'images/logo-text-white.png';
                    $fallbackSrc = (isset($message) && file_exists(public_path($defaultLogo)))
                        ? $message->embed(public_path($defaultLogo))
                        : asset($defaultLogo);
                @endphp
                <img src="{{ $fallbackSrc }}" alt="{{ config('app.name') }}" style="max-height: 50px; object-fit: contain;">
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
            <div style="margin-bottom: 25px; color: #cbd5e1; border-left: 4px solid #ccff00; padding: 12px 16px; background-color: #0f172a; border-radius: 0 8px 8px 0; font-size: 14px; line-height: 1.6;">
                {!! $event->custom_email_message !!}
            </div>
            @endif

            <p style="text-align: center; margin-bottom: 25px; color: #94a3b8; font-size: 14px;">
                Terima kasih <strong style="color: #ffffff;">{{ $notifiableName }}</strong>, registrasi Anda telah berhasil. Simpan tiket ini untuk pengambilan race pack (RPC) dan check-in acara.
            </p>

            @foreach($participants as $participant)
            <div class="ticket">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span class="badge">
                        {{ $participant->category->name ?? 'Participant' }}
                    </span>
                </div>
                
                <table width="100%" cellpadding="6" cellspacing="0" border="0">
                    <tr>
                        <td class="ticket-label">Nama</td>
                        <td class="ticket-value">{{ $participant->name }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Email</td>
                        <td class="ticket-value">{{ $participant->email }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Nomor HP</td>
                        <td class="ticket-value">{{ $participant->phone }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Kategori Lari</td>
                        <td class="ticket-value">
                            {{ $participant->category->name ?? '-' }} - {{ $participant->getAgeGroup($event->start_at) }}
                        </td>
                    </tr>

                    @php
                        $formFields = $event->premium_amenities['form_fields'] ?? [];
                    @endphp

                    @if(!empty($formFields['id_card']) && !empty($participant->id_card))
                    <tr>
                        <td class="ticket-label">No. Identitas (KTP/SIM)</td>
                        <td class="ticket-value">{{ $participant->id_card }}</td>
                    </tr>
                    @endif

                    @if(!empty($formFields['date_of_birth']) && !empty($participant->date_of_birth))
                    <tr>
                        <td class="ticket-label">Tanggal Lahir</td>
                        <td class="ticket-value">
                            {{ $participant->date_of_birth instanceof \Carbon\Carbon ? $participant->date_of_birth->format('d F Y') : \Carbon\Carbon::parse($participant->date_of_birth)->format('d F Y') }}
                        </td>
                    </tr>
                    @endif

                    @if(!empty($formFields['blood_type']) && !empty($participant->blood_type))
                    <tr>
                        <td class="ticket-label">Golongan Darah</td>
                        <td class="ticket-value">{{ strtoupper($participant->blood_type) }}</td>
                    </tr>
                    @endif

                    @if((!empty($formFields['jersey_size']) || ($event->ticket_email_show_jersey ?? true)) && !empty($participant->jersey_size))
                    <tr>
                        <td class="ticket-label">Ukuran Jersey</td>
                        <td class="ticket-value">{{ $participant->jersey_size }}</td>
                    </tr>
                    @endif

                    @if(!empty($formFields['target_time']) && !empty($participant->target_time))
                    <tr>
                        <td class="ticket-label">Target Waktu</td>
                        <td class="ticket-value">{{ $participant->target_time }}</td>
                    </tr>
                    @endif

                    @if(!empty($formFields['emergency_contact']) && (!empty($participant->emergency_contact_name) || !empty($participant->emergency_contact_number)))
                    <tr>
                        <td class="ticket-label">Kontak Darurat</td>
                        <td class="ticket-value">
                            {{ $participant->emergency_contact_name ?? '-' }} ({{ $participant->emergency_contact_number ?? '-' }})
                        </td>
                    </tr>
                    @endif

                    @if(!empty($formFields['address']) && (!empty($participant->address) || !empty($participant->city)))
                    <tr>
                        <td class="ticket-label">Alamat</td>
                        <td class="ticket-value">
                            {{ collect([$participant->address, $participant->city, $participant->province, $participant->postal_code])->filter()->implode(', ') }}
                        </td>
                    </tr>
                    @endif

                    @if(!empty($formFields['strava_activity']) && !empty($participant->strava_url))
                    <tr>
                        <td class="ticket-label">Link Strava</td>
                        <td class="ticket-value">
                            <a href="{{ $participant->strava_url }}" target="_blank" style="color: #ccff00; text-decoration: underline;">{{ $participant->strava_url }}</a>
                        </td>
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
                        <td class="ticket-label">Status Pembayaran</td>
                        <td class="ticket-value" style="color: {{ $transaction->payment_status == 'paid' ? '#34d399' : '#fbbf24' }}">
                            {{ strtoupper($transaction->payment_status) }}
                        </td>
                    </tr>

                    @if(($event->ticket_email_show_pic ?? true) && !empty($transaction->pic_data['name']))
                    <tr>
                        <td class="ticket-label">PIC Name</td>
                        <td class="ticket-value">{{ $transaction->pic_data['name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">PIC Phone</td>
                        <td class="ticket-value">{{ $transaction->pic_data['phone'] ?? '-' }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td class="ticket-label">Nomor Registrasi</td>
                        <td class="ticket-value">{{ $transaction->public_ref ?? ('REG-'.$transaction->id) }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Nomor Tiket</td>
                        <td class="ticket-value" style="color: #ccff00;">{{ $participant->bib_number ?? 'TICKET-'.$participant->id }}</td>
                    </tr>
                </table>

                <div style="border-top: 1px dashed #334155; padding-top: 12px; margin-top: 12px;">
                    <table width="100%" cellpadding="6" cellspacing="0" border="0">
                        <tr>
                            <td class="ticket-label">Lokasi</td>
                            <td class="ticket-value">{{ $event->location_name }}</td>
                        </tr>
                        <tr>
                            <td class="ticket-label">Tanggal</td>
                            <td class="ticket-value">{{ $event->start_at ? $event->start_at->format('d F Y, H:i') : 'TBA' }}</td>
                        </tr>
                    </table>
                </div>

                @if($event->ticket_email_use_qr ?? true)
                    <div class="qr-code">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=TICKET-{{ $participant->id }}-{{ $participant->transaction_id }}" width="150" height="150" alt="Ticket QR">
                        <p style="font-size: 11px; color: #94a3b8; margin-top: 6px;">SCAN QR UNTUK SCANNER VERIFIKASI #{{ $participant->id }}</p>
                    </div>
                @endif
            </div>
            @endforeach

            @if(isset($customHtml) && !empty($customHtml))
            <div style="margin-top: 25px; margin-bottom: 25px; padding: 20px; border: 1px solid #334155; border-radius: 12px; background-color: #0f172a; color: #cbd5e1; font-size: 14px; line-height: 1.6;">
                {!! $customHtml !!}
            </div>
            @endif

            <div style="text-align: center; margin-top: 25px;">
                <p style="font-size: 14px; color: #94a3b8; margin: 4px 0;">Total Pembayaran: <strong style="color: #ffffff;">Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</strong></p>
                <p style="font-size: 14px; color: #94a3b8; margin: 4px 0;">Status: <strong style="color: {{ $transaction->payment_status == 'paid' ? '#34d399' : '#fbbf24' }}">{{ strtoupper($transaction->payment_status) }}</strong></p>
                
                <a href="{{ !empty($event->slug) ? route('events.show', $event->slug) : '#' }}" class="btn">Lihat Detail Event</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            Email ini dibuat secara otomatis. Mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>
