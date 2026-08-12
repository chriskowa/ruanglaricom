@extends('layouts.pacerhub')

@section('title', 'Detail Pesanan #' . $order->invoice_number . ' - Admin Marketplace')

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 font-sans bg-dark text-slate-200"
     x-data="{
        trackingNumber: '{{ $order->shipping_tracking_number }}',
        savingTracking: false,
        toast: { show: false, message: '', type: 'success' },

        showToast(msg, type = 'success') {
            this.toast.message = msg;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => this.toast.show = false, 4000);
        },

        updateTracking() {
            this.savingTracking = true;
            fetch('{{ route('admin.marketplace.orders.tracking', $order->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ shipping_tracking_number: this.trackingNumber })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    this.showToast(data.message, 'success');
                } else {
                    this.showToast('Gagal memperbarui nomor resi.', 'error');
                }
            })
            .catch(err => this.showToast('Terjadi kesalahan jaringan.', 'error'))
            .finally(() => this.savingTracking = false);
        }
     }">

    <!-- Toast Notification -->
    <div x-show="toast.show" 
         x-transition
         class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl border backdrop-blur-md shadow-2xl flex items-center gap-3 text-sm font-semibold max-w-sm"
         :class="toast.type === 'success' ? 'bg-emerald-950/90 border-emerald-500/30 text-emerald-400' : 'bg-red-950/90 border-red-500/30 text-red-400'"
         style="display: none;">
        <span x-text="toast.type === 'success' ? '✓' : '✗'" class="text-base font-bold"></span>
        <span x-text="toast.message"></span>
    </div>

    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Back & Header -->
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('admin.marketplace.orders.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-neon transition">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali ke Daftar Pesanan Marketplace</span>
            </a>
            <span class="text-xs font-mono text-slate-500">Invoice: #{{ $order->invoice_number }}</span>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 shadow-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-xs text-neon font-mono uppercase tracking-wider font-bold mb-1">Marketplace Order Details</p>
                <h1 class="text-2xl font-black text-white italic tracking-tight uppercase">
                    Invoice <span class="text-neon">#{{ $order->invoice_number }}</span>
                </h1>
                <p class="text-xs text-slate-400 font-mono mt-1">Dibuat pada: {{ $order->created_at->format('d M Y H:i:s') }}</p>
            </div>
            <div>
                @if($order->status === 'paid')
                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-cyan-500/10 text-cyan-400 border border-cyan-500/30">PAID (Sudah Dibayar)</span>
                @elseif($order->status === 'shipped')
                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-blue-500/10 text-blue-400 border border-blue-500/30">SHIPPED (Dikirim)</span>
                @elseif($order->status === 'completed')
                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">COMPLETED (Selesai)</span>
                @elseif($order->status === 'disputed')
                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/40 animate-pulse">DISPUTED (Berkendala)</span>
                @else
                    <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">{{ strtoupper($order->status) }}</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left: Order Items & Shipping (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Items List -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h2 class="text-sm font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3">Produk Dipesan</h2>
                    <div class="space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex items-center gap-4 p-3 rounded-2xl bg-slate-950 border border-slate-850">
                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-900 border border-slate-800 shrink-0">
                                    @if(optional($item->product)->primaryImage)
                                        <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-700"><i class="fas fa-box"></i></div>
                                    @endif
                                </div>
                                <div class="flex-grow min-w-0">
                                    <p class="text-sm font-bold text-white truncate">{{ optional($item->product)->title ?? 'Produk Marketplace' }}</p>
                                    <p class="text-xs font-mono text-slate-400 mt-0.5">Qty: {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right font-mono font-black text-neon text-sm">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Shipping Tracking Form (AJAX) -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h2 class="text-sm font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3">Informasi Pengiriman & Resi</h2>
                    <div class="space-y-3 text-xs">
                        <p class="text-slate-300"><strong>Alamat Tujuan:</strong> {{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
                        <p class="text-slate-300"><strong>Kurir / Layanan:</strong> {{ strtoupper($order->shipping_courier ?: 'Ekspedisi Standard') }}</p>
                        
                        <div class="pt-2">
                            <label class="block text-slate-300 font-bold mb-1.5">Nomor Resi Pengiriman (Tracking Number):</label>
                            <div class="flex gap-2">
                                <input type="text" 
                                       x-model="trackingNumber" 
                                       placeholder="Contoh: JNT12984918239" 
                                       class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-600 focus:outline-none focus:border-neon transition">
                                <button @click="updateTracking()" 
                                        :disabled="savingTracking"
                                        class="px-5 py-2.5 rounded-xl bg-neon text-dark font-black hover:bg-white transition text-xs uppercase tracking-wider">
                                    Simpan Resi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: Buyer, Seller & Financial Breakdown (5 Cols) -->
            <div class="lg:col-span-5 space-y-6">

                <!-- Financial Breakdown -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-3 font-mono text-xs">
                    <h2 class="text-sm font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3 font-sans">Rincian Finansial</h2>
                    <div class="flex justify-between text-slate-300">
                        <span>Harga Produk:</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Ongkos Kirim:</span>
                        <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-neon font-bold pt-2 border-t border-slate-800">
                        <span>Grand Total (Buyer Paid):</span>
                        <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-amber-400 pt-1">
                        <span>Fee Platform RuangLari:</span>
                        <span>Rp {{ number_format($order->commission_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-400 font-bold pt-1">
                        <span>Pencairan Saldo Seller:</span>
                        <span>Rp {{ number_format($order->seller_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Parties Profile -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <div>
                        <p class="text-[10px] text-slate-400 font-mono uppercase font-bold">Pembeli (Buyer)</p>
                        <p class="text-sm font-bold text-white">{{ optional($order->buyer)->name ?? 'Buyer' }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ optional($order->buyer)->email ?? '-' }}</p>
                    </div>
                    <div class="border-t border-slate-800 pt-3">
                        <p class="text-[10px] text-slate-400 font-mono uppercase font-bold">Penjual (Seller)</p>
                        <p class="text-sm font-bold text-white">{{ optional($order->seller)->name ?? 'Seller' }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ optional($order->seller)->email ?? '-' }} | {{ optional($order->seller)->phone ?? '-' }}</p>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
