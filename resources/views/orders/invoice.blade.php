<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h2>RUANG LARI</h2>
        <p>Platform Program Lari Indonesia</p>
        <h3>INVOICE</h3>
    </div>

    <div class="invoice-details">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div>
                <p><strong>Kepada:</strong></p>
                <p>{{ $order->user->name }}</p>
                <p>{{ $order->user->email }}</p>
            </div>
            <div style="text-align: right;">
                <p><strong>Invoice #:</strong> {{ $order->order_number }}</p>
                <p><strong>Tanggal:</strong> {{ $order->created_at->format('d F Y H:i') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->payment_status) }}</p>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Program</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Harga</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->program_title }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end">Subtotal:</td>
                <td class="text-end">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-end">Pajak:</td>
                <td class="text-end">Rp {{ number_format($order->tax, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-end">TOTAL:</td>
                <td class="text-end">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="text-align: center; margin-top: 30px;">
        <p>Terima kasih atas pembelian Anda!</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>










