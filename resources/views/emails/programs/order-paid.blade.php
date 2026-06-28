<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembelian Program Sukses - RuangLari</title>
</head>
<body style="margin: 0; padding: 0; background-color: #030712; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #f3f4f6; -webkit-font-smoothing: antialiased;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #030712; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="100%" max-width="600" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #0b0f19; border: 1px solid #1f2937; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 40px 20px 40px;">
                            <span style="font-size: 32px; font-weight: 900; color: #ccff00; letter-spacing: -1px; font-style: italic;">RUANGLARI</span>
                            <div style="height: 1px; background: linear-gradient(90deg, transparent, #ccff00, transparent); margin-top: 15px; width: 100%;"></div>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 20px 40px;">
                            <h2 style="font-size: 22px; font-weight: 800; color: #ffffff; margin-top: 0; text-transform: uppercase; letter-spacing: -0.5px; font-style: italic;">Pembelian Program Sukses!</h2>
                            <p style="font-size: 14px; color: #9ca3af; line-height: 1.6;">
                                Halo <strong>{{ $order->user->name }}</strong>,
                            </p>
                            <p style="font-size: 14px; color: #9ca3af; line-height: 1.6;">
                                Terima kasih telah mempercayakan perjalanan lari Anda kepada RuangLari. Pembayaran Anda untuk pembelian program latihan telah berhasil dikonfirmasi. Berikut rincian transaksi Anda:
                            </p>
                            
                            <!-- Invoice Box -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #030712; border: 1px solid #1f2937; border-radius: 16px; margin: 24px 0; padding: 20px;">
                                <tr>
                                    <td style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; padding-bottom: 5px;">Nomor Invoice</td>
                                    <td align="right" style="font-size: 12px; color: #6b7280; font-weight: bold; text-transform: uppercase; padding-bottom: 5px;">Tanggal</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 14px; color: #ffffff; font-weight: bold; font-family: monospace;">{{ $order->order_number }}</td>
                                    <td align="right" style="font-size: 14px; color: #ffffff; font-weight: bold;">{{ $order->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding-top: 15px; border-top: 1px solid #1f2937; margin-top: 15px;">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            @foreach($order->items as $item)
                                            <tr>
                                                <td style="font-size: 14px; color: #ffffff; padding: 5px 0;">{{ $item->program_title }}</td>
                                                <td align="right" style="font-size: 14px; color: #ccff00; font-weight: bold; padding: 5px 0;">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td style="font-size: 14px; color: #ffffff; font-weight: bold; padding-top: 15px; border-top: 1px solid #1f2937;">Total Bayar</td>
                                                <td align="right" style="font-size: 16px; color: #ccff00; font-weight: 900; padding-top: 15px; border-top: 1px solid #1f2937;">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Login Credentials -->
                            <h3 style="font-size: 16px; font-weight: bold; color: #ffffff; margin-top: 30px; margin-bottom: 10px; text-transform: uppercase; tracking: 0.5px;">Akses Login & Latihan</h3>
                            <p style="font-size: 14px; color: #9ca3af; line-height: 1.6; margin-bottom: 20px;">
                                Anda dapat langsung login dan melihat jadwal program latihan Anda di dashboard dengan detail berikut:
                            </p>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #030712; border: 1px solid #1f2937; border-radius: 16px; margin-bottom: 30px; padding: 20px;">
                                <tr>
                                    <td style="font-size: 13px; color: #6b7280; padding-bottom: 8px;">Email Terdaftar:</td>
                                    <td align="right" style="font-size: 13px; color: #ffffff; font-weight: bold; padding-bottom: 8px;">{{ $order->user->email }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 13px; color: #6b7280;">Halaman Login:</td>
                                    <td align="right" style="font-size: 13px; color: #ccff00; font-weight: bold;"><a href="{{ route('login') }}" style="color: #ccff00; text-decoration: none;">{{ route('login') }}</a></td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="padding: 10px 0 30px 0;">
                                        <a href="{{ route('runner.dashboard') }}" style="display: inline-block; background-color: #ccff00; color: #030712; font-size: 14px; font-weight: bold; text-decoration: none; padding: 16px 32px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 14px 0 rgba(204, 255, 0, 0.3);">
                                            Mulai Latihan Sekarang
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 13px; color: #6b7280; line-height: 1.6; text-align: center;">
                                Jika memiliki pertanyaan atau kendala mengenai program latihan Anda, silakan hubungi tim support kami.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="padding: 20px 40px 40px 40px; background-color: #030712; border-top: 1px solid #1f2937;">
                            <p style="font-size: 11px; color: #4b5563; margin: 0; text-transform: uppercase; letter-spacing: 1px;">
                                &copy; {{ date('Y') }} RuangLari. All Rights Reserved.
                            </p>
                            <p style="font-size: 11px; color: #4b5563; margin-top: 5px;">
                                Email ini dikirim secara otomatis oleh sistem RuangLari.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
