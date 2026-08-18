@extends('layouts.pacerhub', ['withSidebar' => true])

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
                <span>← Kembali ke Daftar Pesanan Marketplace</span>
            </a>
            <span class="text-xs text-slate-500 font-medium">Invoice: #{{ $order->invoice_number }}</span>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 shadow-2xl flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-xs text-neon uppercase tracking-wider font-bold mb-1">Marketplace Order Details</p>
                <h1 class="text-2xl font-black text-white italic tracking-tight uppercase">
                    Invoice <span class="text-neon">#{{ $order->invoice_number }}</span>
                </h1>
                <p class="text-xs text-slate-400 mt-1 font-medium">Dibuat pada: {{ $order->created_at->format('d M Y H:i:s') }}</p>
            </div>
            <div>
                <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border
                    {{ in_array($order->status, ['completed']) ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 
                      (in_array($order->status, ['shipped', 'delivered']) ? 'bg-blue-500/10 text-blue-400 border-blue-500/30' : 
                      (in_array($order->status, ['paid', 'processing', 'packing']) ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30' :
                      (in_array($order->status, ['disputed', 'return_in_progress']) ? 'bg-rose-500/20 text-rose-400 border-rose-500/40' :
                      'bg-slate-800 text-slate-300 border-slate-700'))) }}">
                    {{ $order->status_label }}
                </span>
            </div>
        </div>

        <!-- Dispute / Sengketa Card for Admin -->
        @if(in_array($order->status, ['disputed', 'return_in_progress']) || $order->dispute_reason)
            <div class="bg-rose-950/20 border border-rose-500/40 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between border-b border-rose-500/20 pb-3">
                    <div>
                        <p class="text-xs font-black text-rose-400 uppercase tracking-wider">Sengketa / Komplain Pembeli</p>
                        <p class="text-xs text-slate-300 mt-1">
                            <strong>Kategori:</strong> {{ ucfirst(str_replace('_', ' ', $order->dispute_reason)) }}
                        </p>
                    </div>
                    @if($order->disputed_at)
                        <span class="text-xs text-slate-400 font-medium">Diajukan: {{ $order->disputed_at->format('d/m/Y H:i') }}</span>
                    @endif
                </div>

                <div class="text-xs text-slate-300">
                    <strong>Catatan Pembeli:</strong>
                    <p class="mt-1 p-3 rounded-xl bg-slate-950 border border-slate-800 text-slate-200">
                        {{ $order->dispute_notes ?: '-' }}
                    </p>
                </div>

                @if(!empty($order->dispute_proof_images) && is_array($order->dispute_proof_images))
                    <div>
                        <p class="text-xs font-bold text-slate-300 mb-2">Foto Bukti Fisik:</p>
                        <div class="flex gap-3">
                            @foreach($order->dispute_proof_images as $img)
                                <a href="{{ asset('storage/' . $img) }}" target="_blank" class="w-20 h-20 rounded-xl overflow-hidden border border-slate-700 bg-slate-900 block hover:border-neon transition">
                                    <img src="{{ asset('storage/' . $img) }}" class="w-full h-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($order->return_tracking_number)
                    <div class="text-xs text-slate-300 pt-2 border-t border-rose-500/20">
                        <strong>Nomor Resi Retur:</strong> <span class="font-bold text-neon">{{ $order->return_tracking_number }}</span> ({{ strtoupper($order->return_courier ?: 'Ekspedisi') }})
                    </div>
                @endif

                <!-- Admin Mediation Actions -->
                <div class="pt-3 border-t border-rose-500/20 flex flex-col sm:flex-row gap-3">
                    <form action="{{ route('admin.marketplace.orders.resolve-release', $order->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Putuskan sengketa dan lepaskan dana ke Penjual?')">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider transition">
                            Putuskan: Cairkan Dana ke Seller
                        </button>
                    </form>

                    <form action="{{ route('admin.marketplace.orders.resolve-refund', $order->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Putuskan sengketa dan kembalikan dana 100% ke Pembeli?')">
                        @csrf
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider transition">
                            Putuskan: Refund 100% ke Buyer
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left: Order Items & Shipping (7 Cols) -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Items List -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h2 class="text-sm font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3">Produk Dipesan</h2>
                    <div class="space-y-3">
                        @foreach($order->items as $item)
                            <div class="flex items-center gap-4 p-3 rounded-2xl bg-slate-950 border border-slate-850">
                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-900 border border-slate-800 shrink-0 flex items-center justify-center">
                                    @if(optional($item->product)->primaryImage)
                                        <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[10px] text-slate-700 font-bold">NO IMG</span>
                                    @endif
                                </div>
                                <div class="flex-grow min-w-0">
                                    <p class="text-sm font-bold text-white truncate">{{ optional($item->product)->title ?? 'Produk Marketplace' }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5 font-medium">Qty: {{ $item->quantity }} x Rp {{ number_format($item->price_snapshot ?: ($item->price ?? 0), 0, ',', '.') }}</p>
                                </div>
                                <div class="text-right font-black text-neon text-sm">
                                    Rp {{ number_format(($item->price_snapshot ?: ($item->price ?? 0)) * $item->quantity, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Shipping Tracking Form (AJAX) -->
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-4">
                    <h2 class="text-sm font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3">Informasi Pengiriman & Resi</h2>
                    <div class="space-y-3 text-xs">
                        <p class="text-slate-300"><strong>Penerima:</strong> {{ $order->shipping_name }} ({{ $order->shipping_phone }})</p>
                        <p class="text-slate-300"><strong>Alamat Tujuan:</strong> {{ $order->shipping_address }}, {{ $order->shipping_city }} {{ $order->shipping_postal_code }}</p>
                        <p class="text-slate-300"><strong>Kurir / Layanan:</strong> {{ strtoupper($order->shipping_courier ?: 'Ekspedisi Standard') }} {{ $order->shipping_service_name ? '(' . $order->shipping_service_name . ')' : '' }}</p>
                        
                        <div class="pt-2">
                            <label class="block text-slate-300 font-bold mb-1.5">Nomor Resi Pengiriman (Tracking Number):</label>
                            <div class="flex gap-2">
                                <input type="text" 
                                       x-model="trackingNumber" 
                                       placeholder="Contoh: JNE12984918239" 
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
                <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 space-y-3 text-xs">
                    <h2 class="text-sm font-black text-white uppercase tracking-wider border-b border-slate-800 pb-3 font-sans">Rincian Finansial</h2>
                    <div class="flex justify-between text-slate-300">
                        <span>Harga Produk:</span>
                        <span class="font-bold text-white">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-300">
                        <span>Ongkos Kirim:</span>
                        <span class="font-bold text-white">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-neon font-bold pt-2 border-t border-slate-800">
                        <span>Grand Total (Buyer Paid):</span>
                        <span>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-amber-400 pt-1 font-semibold">
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
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Pembeli (Buyer)</p>
                        <p class="text-sm font-bold text-white">{{ optional($order->buyer)->name ?? 'Buyer' }}</p>
                        <p class="text-xs text-slate-400 font-medium">{{ optional($order->buyer)->email ?? '-' }} | {{ optional($order->buyer)->phone ?? '-' }}</p>
                    </div>
                    <div class="border-t border-slate-800 pt-3">
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Penjual (Seller)</p>
                        <p class="text-sm font-bold text-white">{{ optional($order->seller)->name ?? 'Seller' }}</p>
                        <p class="text-xs text-slate-400 font-medium">{{ optional($order->seller)->email ?? '-' }} | {{ optional($order->seller)->phone ?? '-' }}</p>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection
