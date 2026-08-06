<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pesanan Baru Masuk</title>
</head>
<body style="font-family: sans-serif; background-color: #08111f; color: #f8fafc; padding: 20px;">
    <div style="max-w: 600px; margin: 0 auto; background-color: #0e1a2d; border: 1px solid #1f2d44; border-radius: 16px; padding: 24px;">
        <h2 style="color: #b8ff00; margin-top: 0;">Halo {{ $order->seller->name ?? 'Penjual' }}, Ada Pesanan Baru!</h2>
        <p style="color: #94a3b8;">Selamat! Barang jualan Anda telah berhasil dibeli dan dibayar oleh pembeli.</p>
        
        <div style="background-color: #111f35; border-radius: 12px; padding: 16px; margin: 20px 0;">
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">No. Invoice: <strong style="color: #ffffff;">{{ $order->invoice_number }}</strong></p>
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">Pembeli: <strong style="color: #ffffff;">{{ $order->buyer->name ?? 'Buyer' }}</strong> ({{ $order->shipping_phone ?? '-' }})</p>
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">Alamat Pengiriman: <strong style="color: #ffffff;">{{ $order->shipping_address }}, {{ $order->shipping_city }} {{ $order->shipping_postal_code }}</strong></p>
            <p style="margin: 4px 0; color: #94a3b8; font-size: 13px;">Kurir Pilihan: <strong style="color: #ffffff; text-transform: uppercase;">{{ $order->shipping_courier }}</strong></p>
        </div>

        <h3 style="color: #ffffff; border-bottom: 1px solid #1f2d44; padding-bottom: 8px;">Rincian Barang:</h3>
        @foreach($order->items as $item)
            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span style="color: #ffffff;">{{ $item->product_title_snapshot }} (x{{ $item->quantity }})</span>
                <strong style="color: #b8ff00;">Rp {{ number_format($item->price_snapshot * $item->quantity, 0, ',', '.') }}</strong>
            </div>
        @endforeach

        <div style="border-top: 1px solid #1f2d44; padding-top: 12px; margin-top: 16px;">
            <p style="margin: 4px 0; color: #94a3b8;">Penghasilan Bersih Seller: <strong style="color: #b8ff00; font-size: 18px;">Rp {{ number_format($order->seller_amount, 0, ',', '.') }}</strong></p>
        </div>

        <p style="color: #94a3b8; font-size: 13px; margin-top: 24px;">Silakan segera persiapkan paket dan kirimkan barang ke pembeli. Terima kasih telah berjualan di RuangLari!</p>
    </div>
</body>
</html>
