<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transaksi Baru Marketplace</title>
</head>
<body style="font-family: sans-serif; background-color: #08111f; color: #f8fafc; padding: 20px;">
    <div style="max-w: 600px; margin: 0 auto; background-color: #0e1a2d; border: 1px solid #1f2d44; border-radius: 16px; padding: 24px;">
        <h2 style="color: #b8ff00; margin-top: 0;">[Admin Alert] Transaksi Baru Marketplace</h2>
        <p style="color: #94a3b8;">Terjadi transaksi pembelian baru di platform RuangLari Marketplace.</p>

        <div style="background-color: #111f35; border-radius: 12px; padding: 16px; margin: 20px 0;">
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">No. Invoice: <strong style="color: #ffffff;">{{ $order->invoice_number }}</strong></p>
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">Pembeli: <strong style="color: #ffffff;">{{ $order->buyer->name ?? 'Buyer' }}</strong> ({{ $order->buyer->email ?? '-' }})</p>
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">Penjual: <strong style="color: #ffffff;">{{ $order->seller->name ?? 'Seller' }}</strong> ({{ $order->seller->email ?? '-' }})</p>
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">Metode Bayar: <strong style="color: #ffffff; text-transform: uppercase;">{{ $order->payment_method }}</strong></p>
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">Total Nilai Transaksi: <strong style="color: #b8ff00;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></p>
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">Komisi Platform: <strong style="color: #b8ff00;">Rp {{ number_format($order->commission_amount, 0, ',', '.') }}</strong></p>
        </div>
    </div>
</body>
</html>
