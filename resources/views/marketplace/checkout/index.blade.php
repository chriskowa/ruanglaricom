@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-dark text-slate-200">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter mb-2">
                MARKETPLACE <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400">CHECKOUT</span>
            </h1>
            <p class="text-slate-400 text-sm md:text-base">
                Lengkapi alamat pengiriman dan pilih metode pembayaran.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                @if(session('error'))
                    <div class="bg-red-500/10 border border-red-500/50 rounded-xl p-4 flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <div>
                            <h4 class="font-bold text-red-500 text-sm">Transaksi Gagal</h4>
                            <p class="text-sm text-red-400 mt-1">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500/50 rounded-xl p-4">
                        <h4 class="font-bold text-red-500 text-sm mb-2">Periksa kembali data berikut:</h4>
                        <ul class="list-disc list-inside text-sm text-red-400 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('marketplace.checkout.process', $order->id) }}" method="POST" id="marketplace-checkout-form">
                    @csrf

                    <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 p-6 md:p-8 shadow-xl mb-6">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-neon font-mono text-sm">1</div>
                            Alamat Pengiriman
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Nama Penerima</label>
                                <input
                                    type="text"
                                    name="shipping_name"
                                    value="{{ old('shipping_name', $order->shipping_name ?? (auth()->user()->name ?? '')) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">No. HP</label>
                                <input
                                    type="text"
                                    name="shipping_phone"
                                    value="{{ old('shipping_phone', $order->shipping_phone ?? (auth()->user()->phone ?? '')) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon"
                                    required
                                >
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-400 mb-1">Alamat Lengkap</label>
                                <textarea
                                    name="shipping_address"
                                    rows="3"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon resize-none"
                                    required
                                >{{ old('shipping_address', $order->shipping_address) }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Kota / Kabupaten</label>
                                <input
                                    type="text"
                                    name="shipping_city"
                                    value="{{ old('shipping_city', $order->shipping_city) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Kode Pos</label>
                                <input
                                    type="text"
                                    name="shipping_postal_code"
                                    value="{{ old('shipping_postal_code', $order->shipping_postal_code) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Kurir</label>
                                <select
                                    name="shipping_courier"
                                    id="shipping_courier"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon"
                                    required
                                >
                                    @foreach($shippingOptions as $key => $option)
                                        <option
                                            value="{{ $key }}"
                                            data-cost="{{ $option['cost'] }}"
                                            {{ old('shipping_courier', $order->shipping_courier ?? 'regular') === $key ? 'selected' : '' }}
                                        >
                                            {{ $option['label'] }} - Rp {{ number_format($option['cost'], 0, ',', '.') }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 mb-1">Catatan untuk Kurir / Seller (Opsional)</label>
                                <input
                                    type="text"
                                    name="shipping_note"
                                    value="{{ old('shipping_note', $order->shipping_note) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 p-6 md:p-8 shadow-xl mb-6">
                        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center text-neon font-mono text-sm">2</div>
                            Metode Pembayaran
                        </h3>

                        @php
                            $defaultCourier = old('shipping_courier', $order->shipping_courier ?? 'regular');
                            $defaultShippingCost = $shippingOptions[$defaultCourier]['cost'] ?? 0;
                            $estimatedTotal = $productSubtotal + $defaultShippingCost;
                            $walletBalance = $wallet?->balance ?? 0;
                            $walletSufficient = $walletBalance >= $estimatedTotal;
                        @endphp

                        <div class="space-y-4">
                            <label class="block cursor-pointer group">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="wallet"
                                    class="peer hidden"
                                    {{ old('payment_method', ($walletSufficient ?? false) ? 'wallet' : 'midtrans') === 'wallet' ? 'checked' : '' }}
                                >
                                <div class="p-4 rounded-xl border border-slate-700 bg-slate-800/30 hover:bg-slate-800/50 transition-all flex items-center justify-between group-hover:border-slate-600 peer-checked:border-neon peer-checked:bg-neon/5 peer-checked:[&_.radio-indicator]:bg-neon peer-checked:[&_.radio-indicator]:border-neon peer-checked:[&_.radio-indicator]:shadow-[0_0_10px_rgba(204,255,0,0.3)]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-lg bg-slate-900 flex items-center justify-center border border-slate-700">
                                            <svg class="w-6 h-6 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-white">Wallet RuangLari</p>
                                            <p class="text-sm text-slate-400">
                                                Saldo: <span class="text-neon font-bold">Rp {{ number_format($walletBalance ?? 0, 0, ',', '.') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="radio-indicator w-5 h-5 rounded-full border-2 border-slate-600 bg-transparent transition-all"></div>
                                </div>
                                @if(!$wallet)
                                    <div class="mt-2 text-xs text-yellow-400 pl-1">
                                        Wallet belum aktif. Silakan lakukan top up terlebih dahulu.
                                    </div>
                                @elseif(!($walletSufficient ?? false))
                                    <div class="mt-2 text-xs text-red-400 flex items-center gap-1 pl-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                        Saldo wallet kemungkinan tidak cukup untuk total + ongkir.
                                        <a href="{{ route('wallet.index') }}" class="underline hover:text-red-300 font-bold ml-1">Top up</a>
                                    </div>
                                @endif
                            </label>

                            <label class="block cursor-pointer group">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="midtrans"
                                    class="peer hidden"
                                    {{ old('payment_method', ($walletSufficient ?? false) ? 'wallet' : 'midtrans') === 'midtrans' ? 'checked' : '' }}
                                >
                                <div class="p-4 rounded-xl border border-slate-700 bg-slate-800/30 hover:bg-slate-800/50 transition-all flex items-center justify-between group-hover:border-slate-600 peer-checked:border-neon peer-checked:bg-neon/5 peer-checked:[&_.radio-indicator]:bg-neon peer-checked:[&_.radio-indicator]:border-neon peer-checked:[&_.radio-indicator]:shadow-[0_0_10px_rgba(204,255,0,0.3)]">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-lg bg-slate-900 flex items-center justify-center border border-slate-700">
                                            <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        </div>
                                        <div>
                                            <p class="font-bold text-white">Virtual Account / e-Wallet</p>
                                            <p class="text-xs text-slate-500">Bayar via Midtrans (VA, e-Wallet, kartu, dll)</p>
                                        </div>
                                    </div>
                                    <div class="radio-indicator w-5 h-5 rounded-full border-2 border-slate-600 bg-transparent transition-all"></div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button
                        type="submit"
                        id="marketplace-checkout-submit"
                        class="w-full py-4 bg-neon hover:bg-white hover:text-dark text-dark font-black text-lg rounded-xl transition-all shadow-lg shadow-neon/20 flex items-center justify-center gap-2 group disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span>LANJUTKAN PEMBAYARAN</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
                    </button>
                    <p class="text-center text-xs text-slate-500 mt-4">
                        Dengan melanjutkan, Anda menyetujui syarat dan ketentuan RuangLari.
                    </p>
                </form>
            </div>

            <div class="lg:col-span-1">
                <div class="sticky top-24">
                    <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 p-6 shadow-xl">
                        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            Ringkasan Order
                        </h3>

                        @php
                            $item = $order->items->first();
                            $defaultCourier = old('shipping_courier', $order->shipping_courier ?? 'regular');
                            $defaultShippingCost = $shippingOptions[$defaultCourier]['cost'] ?? 0;
                            $initialTotal = $productSubtotal + $defaultShippingCost;
                        @endphp

                        @if($item)
                            <div class="flex gap-4 pb-4 border-b border-slate-800 mb-4">
                                <div class="w-16 h-16 bg-slate-800 rounded-lg overflow-hidden flex-shrink-0 border border-slate-700">
                                    @if($item->product && $item->product->primaryImage)
                                        <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-600">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-white line-clamp-2 mb-1">{{ $item->product_title_snapshot }}</p>
                                    <p class="text-xs text-slate-400 mb-1">
                                        Seller: <span class="font-semibold text-slate-200">{{ $order->seller->name ?? 'Unknown' }}</span>
                                    </p>
                                    <p class="text-sm font-black text-neon">
                                        Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-3 mb-6 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Subtotal Barang</span>
                                <span class="text-white font-medium">Rp {{ number_format($productSubtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Ongkir (perkiraan)</span>
                                <span class="text-white font-medium" id="shipping-cost-display">
                                    Rp {{ number_format($defaultShippingCost, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="bg-slate-800/50 rounded-xl p-4 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-slate-400 font-bold uppercase">Perkiraan Total</span>
                                <span class="text-xl font-black text-neon" id="total-amount-display">
                                    Rp {{ number_format($initialTotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-center gap-2 text-slate-500 text-xs">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            Pembayaran aman dengan sistem rekening bersama RuangLari.
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    MEMPROSES...
                `;
            });
        }
    });
</script>
@endpush
