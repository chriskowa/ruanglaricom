<div style="font-family: Arial, sans-serif; max-width: 640px; margin: 0 auto; padding: 24px; color: #111827;">
    <h2 style="margin: 0 0 8px 0;">Booking Pacer Paid</h2>
    <p style="margin: 0 0 16px 0; color: #374151;">
        Hai {{ $booking->pacer->user->name }}, ada booking baru yang sudah dibayar.
    </p>

    <div style="border: 1px solid #E5E7EB; border-radius: 12px; padding: 16px; margin-bottom: 16px;">
        <div style="font-size: 12px; color: #6B7280; text-transform: uppercase; letter-spacing: 0.06em;">Invoice</div>
        <div style="font-size: 18px; font-weight: 700; margin-top: 4px;">{{ $booking->invoice_number }}</div>
        <div style="margin-top: 12px; font-size: 14px; color: #111827;">
            <div><strong>Runner:</strong> {{ $booking->runner->name }}</div>
            <div><strong>Race:</strong> {{ $booking->event_name ?: '-' }}</div>
            <div><strong>Tanggal:</strong> {{ $booking->race_date ? $booking->race_date->format('Y-m-d') : '-' }}</div>
            <div><strong>Jarak:</strong> {{ $booking->distance ?: '-' }}</div>
            <div><strong>Target pace:</strong> {{ $booking->target_pace ?: '-' }}</div>
            <div><strong>Meeting point:</strong> {{ $booking->meeting_point ?: '-' }}</div>
        </div>
    </div>

    <p style="margin: 0 0 16px 0; color: #374151;">
        Silakan login ke RuangLari untuk konfirmasi booking dan koordinasi.
    </p>

    <p style="margin: 0; font-size: 12px; color: #6B7280;">
        Email ini dikirim otomatis oleh RuangLari.
    </p>
</div>

