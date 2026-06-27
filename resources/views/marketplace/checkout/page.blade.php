@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Checkout - Marketplace')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-dark text-slate-200 relative overflow-hidden">
    <!-- Subtle glow decoration -->
    <div class="absolute top-1/4 -left-32 w-96 h-96 bg-neon/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-5xl mx-auto relative z-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10 pb-6 border-b border-slate-850">
            <div>
                <p class="text-neon font-mono text-[10px] tracking-widest uppercase mb-1.5 font-bold">Secure checkout</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter uppercase leading-none">
                    Marketplace <span class="text-neon">Checkout</span>
                </h1>
            </div>
            <div class="text-left md:text-right font-mono">
                <span class="text-[10px] text-slate-500 block uppercase">INVOICE NUMBER</span>
                <span class="text-xs text-slate-350 font-bold">{{ $order->invoice_number }}</span>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-6 flex items-start gap-3 text-sm">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <span class="font-bold">Transaksi Gagal:</span> {{ session('error') }}
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-6 text-sm">
                <p class="font-bold mb-2">Mohon koreksi kesalahan berikut:</p>
                <ul class="list-disc list-inside space-y-1 opacity-90">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('marketplace.checkout.process', $order->id) }}" method="POST" id="marketplace-checkout-form">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Side: Shipping & Payment -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Step 1: Shipping Address -->
                    <div class="bg-slate-900/40 backdrop-blur-md rounded-2xl border border-slate-850/80 p-6 md:p-8 shadow-xl">
                        <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-850/50">
                            <div class="w-7 h-7 rounded-lg bg-neon/15 border border-neon/30 text-neon flex items-center justify-center font-black text-xs">1</div>
                            <h2 class="text-lg font-bold text-white uppercase tracking-tight">Alamat Pengiriman</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-[10px] font-mono tracking-wider text-slate-400 font-bold uppercase mb-1.5">Nama Penerima</label>
                                <input
                                    type="text"
                                    name="shipping_name"
                                    value="{{ old('shipping_name', $order->shipping_name ?? (auth()->user()->name ?? '')) }}"
                                    class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-[10px] font-mono tracking-wider text-slate-400 font-bold uppercase mb-1.5">Nomor Handphone</label>
                                <input
                                    type="text"
                                    name="shipping_phone"
                                    value="{{ old('shipping_phone', $order->shipping_phone ?? (auth()->user()->phone ?? '')) }}"
                                    class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                    required
                                >
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-mono tracking-wider text-slate-400 font-bold uppercase mb-1.5">Alamat Lengkap</label>
                                <textarea
                                    name="shipping_address"
                                    rows="3"
                                    placeholder="Masukkan nama jalan, nomor rumah, RT/RW, dan patokan..."
                                    class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-650 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all resize-none"
                                    required
                                >{{ old('shipping_address', $order->shipping_address) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-[10px] font-mono tracking-wider text-slate-400 font-bold uppercase mb-1.5">Kota / Kabupaten</label>
                                <input
                                    type="text"
                                    name="shipping_city"
                                    value="{{ old('shipping_city', $order->shipping_city) }}"
                                    class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-650 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-[10px] font-mono tracking-wider text-slate-400 font-bold uppercase mb-1.5">Kode Pos</label>
                                <input
                                    type="text"
                                    name="shipping_postal_code"
                                    value="{{ old('shipping_postal_code', $order->shipping_postal_code) }}"
                                    class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-650 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-[10px] font-mono tracking-wider text-slate-400 font-bold uppercase mb-1.5">Metode Pengiriman (Kurir)</label>
                                <select
                                    name="shipping_courier"
                                    id="shipping_courier"
                                    class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                    required
                                >
                                    @foreach($shippingOptions as $key => $option)
                                        <option
                                            value="{{ $key }}"
                                            data-cost="{{ $option['cost'] }}"
                                            {{ old('shipping_courier', $order->shipping_courier ?? 'regular') === $key ? 'selected' : '' }}
                                            class="bg-slate-950 text-white"
                                        >
                                            {{ $option['label'] }} - Rp {{ number_format($option['cost'], 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-mono tracking-wider text-slate-400 font-bold uppercase mb-1.5">Catatan untuk Seller (Opsional)</label>
                                <input
                                    type="text"
                                    name="shipping_note"
                                    value="{{ old('shipping_note', $order->shipping_note) }}"
                                    placeholder="Contoh: Titip di satpam rumah"
                                    class="w-full bg-slate-950/60 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-650 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                >
                            </div>
                        </div>
                    </div>

                    @php
                        $defaultCourier = old('shipping_courier', $order->shipping_courier ?? 'regular');
                        $defaultShippingCost = $shippingOptions[$defaultCourier]['cost'] ?? 0;
                        $estimatedTotal = $productSubtotal + $defaultShippingCost;
                        $walletBalance = $wallet?->balance ?? 0;
                        $walletSufficient = $walletBalance >= $estimatedTotal;
                    @endphp

                    <!-- Step 2: Payment Method -->
                    <div class="bg-slate-900/40 backdrop-blur-md rounded-2xl border border-slate-850/80 p-6 md:p-8 shadow-xl">
                        <div class="flex items-center gap-3.5 mb-6 pb-4 border-b border-slate-850/50">
                            <div class="w-7 h-7 rounded-lg bg-neon/15 border border-neon/30 text-neon flex items-center justify-center font-black text-xs">2</div>
                            <h2 class="text-lg font-bold text-white uppercase tracking-tight">Metode Pembayaran</h2>
                        </div>

                        <div class="space-y-4">
                            <!-- Wallet Payment Option -->
                            <label class="block cursor-pointer group">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="wallet"
                                    class="peer hidden"
                                    {{ old('payment_method', ($walletSufficient ?? false) ? 'wallet' : 'midtrans') === 'wallet' ? 'checked' : '' }}
                                >
                                <div class="p-4 rounded-xl border border-slate-800 bg-slate-950/30 hover:bg-slate-850/30 transition-all duration-300 flex items-center justify-between group-hover:border-slate-700 peer-checked:border-neon peer-checked:bg-neon/5 peer-checked:[&_.radio-indicator]:bg-neon peer-checked:[&_.radio-indicator]:border-neon peer-checked:[&_.radio-indicator]:shadow-[0_0_12px_rgba(204,255,0,0.25)]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-slate-950 flex items-center justify-center border border-slate-850">
                                            <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-white text-sm md:text-base leading-snug">Dompet Digital RuangLari</p>
                                            <p class="text-xs text-slate-400 mt-1 font-mono">
                                                Saldo Anda: 
                                                <span class="text-neon font-bold">
                                                    Rp {{ number_format($walletBalance ?? 0, 0, ',', '.') }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="radio-indicator w-4 h-4 rounded-full border border-slate-600 bg-transparent transition-all shrink-0"></div>
                                </div>
                                @if(!$wallet)
                                    <div class="mt-2.5 text-xs text-amber-400 pl-1.5 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                        Wallet belum aktif. Silakan lakukan top up terlebih dahulu.
                                    </div>
                                @elseif(!($walletSufficient ?? false))
                                    <div class="mt-2.5 text-xs text-rose-450 pl-1.5 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400 shrink-0"></span>
                                        <span>Saldo tidak mencukupi (Kurang Rp {{ number_format($estimatedTotal - $walletBalance, 0, ',', '.') }}).</span>
                                        <a href="{{ route('wallet.index') }}" class="underline hover:text-rose-300 font-bold font-mono">TOPUP</a>
                                    </div>
                                @endif
                            </label>

                            <!-- Midtrans Payment Option -->
                            <label class="block cursor-pointer group">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="midtrans"
                                    class="peer hidden"
                                    {{ old('payment_method', ($walletSufficient ?? false) ? 'wallet' : 'midtrans') === 'midtrans' ? 'checked' : '' }}
                                >
                                <div class="p-4 rounded-xl border border-slate-800 bg-slate-950/30 hover:bg-slate-850/30 transition-all duration-300 flex items-center justify-between group-hover:border-slate-700 peer-checked:border-neon peer-checked:bg-neon/5 peer-checked:[&_.radio-indicator]:bg-neon peer-checked:[&_.radio-indicator]:border-neon peer-checked:[&_.radio-indicator]:shadow-[0_0_12px_rgba(204,255,0,0.25)]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-slate-950 flex items-center justify-center border border-slate-850">
                                            <svg class="w-5 h-5 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-white text-sm md:text-base leading-snug">Virtual Account / e-Wallet (Midtrans)</p>
                                            <p class="text-xs text-slate-500 mt-1">Gopay, ShopeePay, QRIS, Bank Transfer (BCA, Mandiri, dll)</p>
                                        </div>
                                    </div>
                                    <div class="radio-indicator w-4 h-4 rounded-full border border-slate-600 bg-transparent transition-all shrink-0"></div>
                                </div>
                            </label>
                        </div>

                        <!-- CTA Pay Button -->
                        <button
                            type="submit"
                            id="marketplace-checkout-submit"
                            class="mt-8 w-full py-4 bg-neon hover:bg-white hover:text-dark text-dark font-black text-sm tracking-wider uppercase rounded-xl transition-all duration-300 shadow-lg shadow-neon/15 flex items-center justify-center gap-2 group disabled:opacity-55 disabled:cursor-not-allowed"
                        >
                            <span>Lanjutkan Pembayaran</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform stroke-[3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Right Side: Order Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <div class="bg-slate-900/40 backdrop-blur-md rounded-2xl border border-slate-850/80 p-6 shadow-xl space-y-6">
                            <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2.5">
                                <svg class="w-4.5 h-4.5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                Ringkasan Belanja
                            </h3>

                            @php
                                $item = $order->items->first();
                                $sidebarCourier = old('shipping_courier', $order->shipping_courier ?? 'regular');
                                $sidebarShippingCost = $shippingOptions[$sidebarCourier]['cost'] ?? 0;
                                $initialTotal = $productSubtotal + $sidebarShippingCost;
                            @endphp

                            @if($item)
                                <div class="flex gap-4 pb-5 border-b border-slate-850/60">
                                    <div class="w-16 h-16 bg-slate-950 rounded-xl overflow-hidden flex-shrink-0 border border-slate-850 flex items-center justify-center">
                                        @if($item->product && $item->product->primaryImage)
                                            <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-6 h-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-white line-clamp-2 leading-snug mb-1">{{ $item->product_title_snapshot }}</p>
                                        <p class="text-[10px] text-slate-500 font-medium">
                                            Seller: <span class="text-slate-350">{{ $order->seller->name ?? 'Unknown' }}</span>
                                        </p>
                                        <p class="text-xs font-black text-neon font-mono mt-1.5">
                                            Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Calculation Details -->
                            <div class="space-y-3.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Harga Barang</span>
                                    <span class="text-white font-semibold font-mono">Rp {{ number_format($productSubtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Ongkos Kirim</span>
                                    <span class="text-white font-semibold font-mono" id="shipping-cost-display">
                                        Rp {{ number_format($sidebarShippingCost, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-slate-850/60 pt-5">
                                <div class="bg-slate-950/40 rounded-xl p-4 border border-slate-850">
                                    <div class="flex justify-between items-center gap-2">
                                        <span class="text-[10px] text-slate-400 font-black uppercase tracking-wider">Total Pembayaran</span>
                                        <span class="text-xl font-black text-neon font-mono tracking-tight" id="total-amount-display">
                                            Rp {{ number_format($initialTotal, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Trust Badge -->
                            <div class="flex items-start gap-2.5 text-slate-500 text-[10px] leading-relaxed pt-2">
                                <svg class="w-4 h-4 text-slate-650 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Dana Anda aman. Pembayaran baru diteruskan ke seller setelah Anda mengonfirmasi penerimaan barang.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('marketplace-checkout-form');
        var submitBtn = document.getElementById('marketplace-checkout-submit');
        var courierSelect = document.getElementById('shipping_courier');
        var shippingDisplay = document.getElementById('shipping-cost-display');
        var totalDisplay = document.getElementById('total-amount-display');
        var productSubtotal = {{ (int) $productSubtotal }};

        if (courierSelect && shippingDisplay && totalDisplay) {
            courierSelect.addEventListener('change', function () {
                var selected = courierSelect.options[courierSelect.selectedIndex];
                var cost = parseInt(selected.getAttribute('data-cost') || '0', 10);
                var total = productSubtotal + cost;

                shippingDisplay.textContent = 'Rp ' + cost.toLocaleString('id-ID');
                totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
            });
        }

        if (form && submitBtn) {
            form.addEventListener('submit', function () {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>MEMPROSES...</span>
                `;
            });
        }
    });
</script>
@endpush
