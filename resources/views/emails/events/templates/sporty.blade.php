<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Success - Sporty</title>
    <style>
        body { font-family: 'Arial Black', Arial, sans-serif; background-color: #f1f5f9; color: #0f172a; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 12px 30px rgba(234,88,12,0.15); border: 2px solid #ea580c; }
        .header { background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); padding: 35px 20px; text-align: center; color: #ffffff; }
        .header h1 { color: #ffffff; margin: 0; font-size: 26px; text-transform: uppercase; font-style: italic; letter-spacing: -1px; }
        .content { padding: 30px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
        .event-info { text-align: center; margin-bottom: 25px; background-color: #fff7ed; border-radius: 12px; padding: 18px; border: 1px solid #ffedd5; }
        .event-name { font-size: 24px; font-weight: 900; color: #ea580c; text-transform: uppercase; font-style: italic; letter-spacing: -0.5px; margin-bottom: 6px; }
        .event-meta { color: #9a3412; font-size: 13px; font-weight: 800; text-transform: uppercase; }
        .ticket { border: 3px solid #ea580c; border-radius: 16px; padding: 22px; margin-bottom: 25px; background-color: #ffffff; position: relative; }
        .ticket-header { text-align: center; margin-bottom: 18px; }
        .badge { background-color: #ea580c; color: #ffffff; padding: 8px 22px; border-radius: 30px; font-size: 14px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; display: inline-block; box-shadow: 0 4px 10px rgba(234,88,12,0.3); }
        .ticket-label { font-size: 11px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 40%; vertical-align: top; }
        .ticket-value { font-size: 15px; font-weight: 800; color: #0f172a; word-wrap: break-word; }
        .qr-code { text-align: center; margin-top: 20px; padding-top: 15px; border-top: 2px dashed #fed7aa; }
        .qr-code img { border: 4px solid #ea580c; border-radius: 12px; }
        .footer { background-color: #0f172a; padding: 22px; text-align: center; font-size: 12px; color: #94a3b8; }
        .btn { display: inline-block; background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); color: #ffffff; padding: 14px 30px; border-radius: 12px; text-decoration: none; font-weight: 900; font-size: 15px; text-transform: uppercase; font-style: italic; letter-spacing: 0.5px; margin-top: 20px; box-shadow: 0 6px 16px rgba(234,88,12,0.35); }
        
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; margin: 0 !important; border-radius: 0 !important; }
            .content { padding: 18px !important; }
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
            <h1 style="margin-top: 15px;">OFFICIAL EVENT TICKET</h1>
        </div>
        
        <div class="content">
            <div class="event-info">
                <div class="event-name">{{ $event->name }}</div>
                <div class="event-meta">
                    {{ $event->start_at ? $event->start_at->format('d F Y, H:i') : 'Date TBA' }} | {{ $event->location_name }}
                </div>
            </div>

            @if($event->custom_email_message)
            <div style="margin-bottom: 25px; color: #431407; border-left: 4px solid #ea580c; padding: 14px 18px; background-color: #fff7ed; border-radius: 0 10px 10px 0; font-size: 14px; line-height: 1.6;">
                {!! $event->custom_email_message !!}
            </div>
            @endif

            <p style="text-align: center; margin-bottom: 25px; color: #475569; font-size: 14px; font-weight: 500;">
                Selamat <strong style="color: #ea580c;">{{ $notifiableName }}</strong>! Registrasi Anda telah resmi terdaftar. Tunjukkan E-Tiket ini saat Racepack Collection & Event Day.
            </p>

            @foreach($participants as $participant)
            <div class="ticket">
                <div class="ticket-header">
                    <span class="badge">
                        {{ $participant->category->name ?? 'Participant' }}
                    </span>
                </div>
                
                <table width="100%" cellpadding="6" cellspacing="0" border="0">
                    <tr>
                        <td class="ticket-label">Nama Runner</td>
                        <td class="ticket-value">{{ $participant->name }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Email</td>
                        <td class="ticket-value">{{ $participant->email }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">No. Handphone</td>
                        <td class="ticket-value">{{ $participant->phone }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">Kategori Lari</td>
                        <td class="ticket-value">
                            {{ $participant->category->name ?? '-' }} ({{ $participant->getAgeGroup($event->start_at) }})
                        </td>
                    </tr>

                    @php
                        $formFields = $event->premium_amenities['form_fields'] ?? [];
                    @endphp

                    @if(!empty($formFields['id_card']) && !empty($participant->id_card))
                    <tr>
                        <td class="ticket-label">No. Identitas (KTP)</td>
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
                            <a href="{{ $participant->strava_url }}" target="_blank" style="color: #ea580c; text-decoration: underline;">{{ $participant->strava_url }}</a>
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
                        <td class="ticket-label">Status Bayar</td>
                        <td class="ticket-value" style="color: {{ $transaction->payment_status == 'paid' ? '#16a34a' : '#d97706' }}">
                            {{ strtoupper($transaction->payment_status) }}
                        </td>
                    </tr>

                    @if(($event->ticket_email_show_pic ?? true) && !empty($transaction->pic_data['name']))
                    <tr>
                        <td class="ticket-label">PIC Kontrak</td>
                        <td class="ticket-value">{{ $transaction->pic_data['name'] ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">PIC Kontak</td>
                        <td class="ticket-value">{{ $transaction->pic_data['phone'] ?? '-' }}</td>
                    </tr>
                    @endif

                    <tr>
                        <td class="ticket-label">Kode Reg.</td>
                        <td class="ticket-value">{{ $transaction->public_ref ?? ('REG-'.$transaction->id) }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label">No. BIB / Tiket</td>
                        <td class="ticket-value" style="color: #ea580c; font-weight: 900; font-size: 17px;">{{ $participant->bib_number ?? 'TICKET-'.$participant->id }}</td>
                    </tr>
                </table>

                <div style="border-top: 2px dashed #fed7aa; padding-top: 12px; margin-top: 12px;">
                    <table width="100%" cellpadding="6" cellspacing="0" border="0">
                        <tr>
                            <td class="ticket-label">Lokasi Venue</td>
                            <td class="ticket-value">{{ $event->location_name }}</td>
                        </tr>
                        <tr>
                            <td class="ticket-label">Jadwal Start</td>
                            <td class="ticket-value">{{ $event->start_at ? $event->start_at->format('d F Y, H:i') : 'TBA' }}</td>
                        </tr>
                    </table>
                </div>

                @if($event->ticket_email_use_qr ?? true)
                    <div class="qr-code">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=TICKET-{{ $participant->id }}-{{ $participant->transaction_id }}" width="150" height="150" alt="Ticket QR">
                        <p style="font-size: 11px; color: #ea580c; font-weight: 800; margin-top: 6px;">E-TICKET SCAN CODE #{{ $participant->id }}</p>
                    </div>
                @endif
            </div>
            @endforeach

            @if(isset($customHtml) && !empty($customHtml))
            <div style="margin-top: 25px; margin-bottom: 25px; padding: 20px; border: 2px solid #ea580c; border-radius: 12px; background-color: #fff7ed; color: #431407; font-size: 14px; line-height: 1.6;">
                {!! $customHtml !!}
            </div>
            @endif

            <div style="text-align: center; margin-top: 25px;">
                <p style="font-size: 14px; color: #64748b; margin: 4px 0;">Total Bayar: <strong style="color: #ea580c;">Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</strong></p>
                <p style="font-size: 14px; color: #64748b; margin: 4px 0;">Status: <strong style="color: {{ $transaction->payment_status == 'paid' ? '#16a34a' : '#d97706' }}">{{ strtoupper($transaction->payment_status) }}</strong></p>
                
                <a href="{{ !empty($event->slug) ? route('events.show', $event->slug) : '#' }}" class="btn">Info Lengkap Event</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            Email Tiket Resmi Event.
        </div>
    </div>
</body>
</html>
