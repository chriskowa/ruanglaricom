<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Success</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f5; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; margin-top: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background-color: #0f172a; padding: 30px; text-align: center; }
        .header h1 { color: #ccff00; margin: 0; font-size: 24px; text-transform: uppercase; letter-spacing: 2px; }
        .content { padding: 30px; }
        .event-info { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .event-name { font-size: 20px; font-weight: bold; color: #0f172a; margin-bottom: 5px; }
        .event-meta { color: #64748b; font-size: 14px; }
        .ticket { border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; margin-bottom: 20px; background-color: #f8fafc; }
        .ticket-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .ticket-label { font-size: 12px; font-weight: bold; color: #94a3b8; text-transform: uppercase; }
        .ticket-value { font-size: 16px; font-weight: bold; color: #0f172a; }
        .qr-code { text-align: center; margin-top: 20px; }
        .qr-code img { border: 4px solid #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .footer { background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
        .btn { display: inline-block; background-color: #0f172a; color: #ccff00; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f4f4f5; color: #333; margin: 0; padding: 0;">
    <div class="container" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        <div class="header" style="background-color: #0f172a; padding: 30px; text-align: center;">
            @if(isset($event) && $event->logo_image)
                <img src="{{ $message->embed(storage_path('app/public/' . $event->logo_image)) }}" alt="{{ $event->name }}" style="max-height: 80px; max-width: 200px; object-fit: contain;">
            @else
                <img src="{{ $message->embed(public_path('images/logo-text-white.png')) }}" alt="{{ config('app.name') }}" style="max-height: 50px; object-fit: contain;">
            @endif
        </div>
        
        <div class="content" style="padding: 30px;">
            <div class="event-info">
                <div class="event-name">{{ $event->name }}</div>
                <div class="event-meta">
                    {{ $event->start_at ? $event->start_at->format('d F Y, H:i') : 'Date TBA' }} | {{ $event->location_name }}
                </div>
            </div>

            @if($event->custom_email_message)
            <div style="margin-bottom: 30px; color: #475569; border-left: 4px solid #ccff00; padding-left: 15px;">
                {!! $event->custom_email_message !!}
            </div>
            @endif

            <p style="text-align: center; margin-bottom: 30px; color: #475569;">
                Terima kasih <strong>{{ $notifiableName }}</strong>, registrasi Anda telah berhasil. Simpan tiket ini untuk pengambilan race pack (RPC) dan check-in saat acara.
            </p>

            @foreach($participants as $participant)
            <div class="ticket">
                <div style="text-align: center; margin-bottom: 20px;">
                    <span style="background-color: #ccff00; color: #0f172a; padding: 6px 16px; border-radius: 20px; font-size: 14px; font-weight: bold; text-transform: uppercase;">
                        {{ $participant->category->name ?? 'Participant' }}
                    </span>
                </div>
                
                <table width="100%" cellpadding="5" cellspacing="0" border="0" style="margin-bottom: 15px;">
                    <tr>
                        <td class="ticket-label" width="40%" style="padding-bottom: 8px;">Nama</td>
                        <td class="ticket-value" style="padding-bottom: 8px;">{{ $participant->name }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label" style="padding-bottom: 8px;">Email</td>
                        <td class="ticket-value" style="padding-bottom: 8px;">{{ $participant->email }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label" style="padding-bottom: 8px;">Nomor HP</td>
                        <td class="ticket-value" style="padding-bottom: 8px;">{{ $participant->phone }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label" style="padding-bottom: 8px;">Kategori Lari</td>
                        <td class="ticket-value" style="padding-bottom: 8px;">{{ $participant->category->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="ticket-label" style="padding-bottom: 8px;">Status Pembayaran</td>
                        <td class="ticket-value" style="padding-bottom: 8px; color: {{ $transaction->payment_status == 'paid' ? '#10b981' : '#f59e0b' }}">
                            {{ strtoupper($transaction->payment_status) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="ticket-label" style="padding-bottom: 8px;">Nomor Tiket</td>
                        <td class="ticket-value" style="padding-bottom: 8px;">{{ $participant->bib_number ?? 'TICKET-'.$participant->id }}</td>
                    </tr>
                </table>

                <div style="border-top: 1px dashed #cbd5e1; padding-top: 15px; margin-top: 10px;">
                    <table width="100%" cellpadding="5" cellspacing="0" border="0">
                        <tr>
                            <td class="ticket-label" width="40%" style="padding-bottom: 8px;">Lokasi</td>
                            <td class="ticket-value" style="padding-bottom: 8px;">{{ $event->location_name }}</td>
                        </tr>
                        <tr>
                            <td class="ticket-label" style="padding-bottom: 8px;">Tanggal</td>
                            <td class="ticket-value" style="padding-bottom: 8px;">{{ $event->start_at ? $event->start_at->format('d F Y, H:i') : 'TBA' }}</td>
                        </tr>
                    </table>
                </div>

                @php($useQr = $event->ticket_email_use_qr ?? true)
                @if($useQr)
                    <div class="qr-code">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=TICKET-{{ $participant->id }}-{{ $participant->transaction_id }}" width="150" height="150" alt="Ticket QR">
                        <p style="font-size: 10px; color: #94a3b8; margin-top: 5px;">ID: #{{ $participant->id }}</p>
                    </div>
                @endif
            </div>
            @endforeach

            <div style="text-align: center;">
                <p style="font-size: 14px; color: #64748b;">Total Pembayaran: <strong>Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</strong></p>
                <p style="font-size: 14px; color: #64748b;">Status: <strong style="color: {{ $transaction->payment_status == 'paid' ? '#10b981' : '#f59e0b' }}">{{ strtoupper($transaction->payment_status) }}</strong></p>
                
                <a href="{{ route('events.show', $event->slug) }}" class="btn" style="display: inline-block; background-color: #0f172a; color: #ccff00; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: 20px;">Lihat Detail Event</a>
            </div>
        </div>

        <div class="footer" style="background-color: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
            Email ini dibuat secara otomatis. Mohon tidak membalas email ini.
        </div>
    </div>
</body>
</html>
