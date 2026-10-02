<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }} - RuangLari</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; padding: 0 !important; }
            .print-card { border: none !important; box-shadow: none !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 sm:p-8 min-h-screen flex flex-col items-center">

    <!-- Action Bar -->
    <div class="no-print w-full max-w-3xl mb-4 flex items-center justify-between">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white border border-slate-300 px-3 py-1.5 rounded-md shadow-sm transition">
            &larr; Kembali
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-4 py-1.5 rounded-md shadow-sm transition">
            Cetak / Simpan PDF
        </button>
    </div>

    <!-- Invoice Sheet -->
    <div class="print-card w-full max-w-3xl bg-white border border-slate-200 rounded-lg p-8 sm:p-12 shadow-sm relative overflow-hidden">
        
        <!-- Status Watermark / Stamp -->
        <div class="absolute top-8 right-8 text-right">
            @if($invoice->payment_status === 'paid')
                <div class="inline-block border-2 border-emerald-600 text-emerald-700 px-4 py-1 rounded text-xs font-black tracking-wider uppercase">
                    Lunas
                </div>
                <div class="text-[10px] text-slate-500 font-mono mt-1">
                    {{ $invoice->paid_at ? $invoice->paid_at->format('d M Y H:i') : 'Terverifikasi' }}
                </div>
            @elseif($invoice->is_overdue)
                <div class="inline-block border-2 border-rose-600 text-rose-700 px-4 py-1 rounded text-xs font-black tracking-wider uppercase">
                    Jatuh Tempo
                </div>
            @elseif($invoice->payment_status === 'cancelled')
                <div class="inline-block border-2 border-slate-400 text-slate-500 px-4 py-1 rounded text-xs font-black tracking-wider uppercase">
                    Dibatalkan
                </div>
            @else
                <div class="inline-block border-2 border-amber-600 text-amber-700 px-4 py-1 rounded text-xs font-black tracking-wider uppercase">
                    Belum Lunas
                </div>
            @endif
        </div>

        <!-- Header -->
        <div class="flex items-center gap-3 mb-8">
            <img src="{{ asset('images/logo saja ruang lari.png') }}" alt="RuangLari" class="h-10 w-auto">
            <div>
                <div class="text-xl font-black italic tracking-tighter text-slate-900">
                    RUANG<span style="color: #65a30d;">LARI</span>
                </div>
                <p class="text-xs text-slate-500 font-medium">Platform Pelatihan & Komunitas Lari Indonesia</p>
            </div>
        </div>

        <!-- Invoice Details Header -->
        <div class="border-t border-b border-slate-200 py-4 my-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">No. Invoice</span>
                <span class="font-mono font-bold text-slate-900">{{ $invoice->invoice_number }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Tanggal Terbit</span>
                <span class="font-medium text-slate-800">{{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('d F Y') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Jatuh Tempo</span>
                <span class="font-medium text-slate-800 {{ $invoice->is_overdue ? 'text-rose-600 font-bold' : '' }}">
                    {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d F Y') : '-' }}
                </span>
            </div>
            <div>
                <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Metode Bayar</span>
                <span class="font-medium text-slate-800 uppercase font-mono text-[11px]">
                    {{ $invoice->payment_method ? str_replace('_', ' ', $invoice->payment_method) : '-' }}
                </span>
            </div>
        </div>

        <!-- Parties Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 my-6 text-xs">
            <div class="bg-slate-50 p-4 rounded-md border border-slate-100">
                <span class="text-slate-400 uppercase text-[10px] font-bold tracking-wider block mb-1">Diterbitkan Oleh (Pelatih)</span>
                <div class="font-bold text-slate-900 text-sm">{{ $invoice->coach->name ?? 'Coach' }}</div>
                <div class="text-slate-600">{{ $invoice->coach->email ?? '-' }}</div>
                @if($invoice->coach->phone)
                    <div class="text-slate-600 mt-0.5">{{ $invoice->coach->phone }}</div>
                @endif
            </div>
            <div class="bg-slate-50 p-4 rounded-md border border-slate-100">
                <span class="text-slate-400 uppercase text-[10px] font-bold tracking-wider block mb-1">Ditujukan Kepada (Atlet)</span>
                <div class="font-bold text-slate-900 text-sm">{{ $invoice->runner->name ?? 'Atlet' }}</div>
                <div class="text-slate-600">{{ $invoice->runner->email ?? '-' }}</div>
                @if($invoice->runner->phone)
                    <div class="text-slate-600 mt-0.5">{{ $invoice->runner->phone }}</div>
                @endif
            </div>
        </div>

        <!-- Line Items Table -->
        <div class="my-6">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="border-b-2 border-slate-800 text-[11px] font-bold text-slate-700 uppercase tracking-wider">
                        <th class="py-2.5">Deskripsi Layanan / Program</th>
                        <th class="py-2.5 text-center">Model</th>
                        <th class="py-2.5 text-center">Kuantitas</th>
                        <th class="py-2.5 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr>
                        <td class="py-3">
                            <div class="font-bold text-slate-900">{{ $invoice->program->title ?? 'Program Pelatihan Personal Coach' }}</div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Periode: 
                                {{ $invoice->period_start ? \Carbon\Carbon::parse($invoice->period_start)->format('d/m/Y') : '-' }} 
                                @if($invoice->period_end)
                                    s/d {{ \Carbon\Carbon::parse($invoice->period_end)->format('d/m/Y') }}
                                @endif
                            </div>
                        </td>
                        <td class="py-3 text-center font-mono text-[11px] text-slate-700">
                            {{ $invoice->pricing_label }}
                        </td>
                        <td class="py-3 text-center font-mono text-slate-700">
                            {{ $invoice->quantity }} {{ $invoice->pricing_type === 'hourly' ? 'Sesi' : 'Unit' }}
                        </td>
                        <td class="py-3 text-right font-mono font-bold text-slate-900">
                            Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-300">
                        <td colspan="3" class="py-2.5 text-right font-semibold text-slate-600">Subtotal</td>
                        <td class="py-2.5 text-right font-mono font-semibold text-slate-800">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-t-2 border-slate-800">
                        <td colspan="3" class="py-3 text-right font-bold text-slate-900 text-sm">Total Tagihan</td>
                        <td class="py-3 text-right font-mono font-black text-slate-900 text-base">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes / Instructions -->
        @if($invoice->notes)
            <div class="bg-slate-50 p-4 rounded-md border border-slate-200 text-xs my-6">
                <span class="text-slate-500 font-bold block mb-1">Catatan & Instruksi Pembayaran:</span>
                <p class="text-slate-700 whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="pt-8 border-t border-slate-200 text-center text-slate-400 text-[11px] space-y-1">
            <p>Invoice ini sah dan diterbitkan secara digital melalui sistem RuangLari.</p>
            <p class="font-mono">ID Referensi: {{ $invoice->invoice_number }} &bull; Diunduh: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

</body>
</html>
