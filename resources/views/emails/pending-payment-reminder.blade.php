<!DOCTYPE html>
<html>
<head>
    <title>Reminder Pembayaran</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;">
        <h2 style="color: #2563eb; text-align: center;">Reminder Pembayaran</h2>
        
        <p>Halo <strong>{{ $transaction->pic_data['name'] ?? 'Peserta' }}</strong>,</p>
        
        <p>Terima kasih telah mendaftar di event <strong>{{ $transaction->event->name }}</strong>.</p>
        
        <p>Kami mencatat bahwa pembayaran Anda masih berstatus <strong>Pending</strong>. Untuk menyelesaikan pendaftaran Anda dan mengamankan slot, mohon segera selesaikan pembayaran.</p>
        
        <div style="background-color: #f8fafc; padding: 15px; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>ID Transaksi:</strong> {{ $transaction->public_ref ?? $transaction->id }}</p>
            <p style="margin: 5px 0;"><strong>Total Pembayaran:</strong> Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</p>
            <p style="margin: 5px 0;"><strong>Tanggal Daftar:</strong> {{ $transaction->created_at->format('d M Y H:i') }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('events.payments.continue', $transaction->event->slug) }}" 
               style="background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">
                Lanjutkan Pembayaran
            </a>
        </div>
        
        <p>Jika Anda sudah melakukan pembayaran namun status belum berubah, mohon hubungi panitia penyelenggara.</p>
        
        <p>Terima kasih,<br>Tim {{ $transaction->event->name }}</p>
        
        <div style="margin-top: 30px; font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #e0e0e0; padding-top: 20px;">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
