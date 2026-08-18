@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Checkout - Marketplace')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-dark text-slate-200 relative overflow-hidden">
    <div class="max-w-5xl mx-auto relative z-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8 pb-6 border-b border-slate-850">
            <div>
                <p class="text-neon text-xs tracking-wider uppercase mb-1 font-bold">Secure Checkout</p>
                <h1 class="text-2xl md:text-3xl font-black text-white italic tracking-tight uppercase leading-none">
                    Marketplace <span class="text-neon">Checkout</span>
                </h1>
            </div>
            <div class="text-left md:text-right">
                <span class="text-[11px] text-slate-500 block uppercase font-semibold">INVOICE</span>
                <span class="text-xs text-slate-300 font-bold">#{{ $order->invoice_number }}</span>
            </div>
        </div>

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-6 text-sm">
                <span class="font-bold">Transaksi Gagal:</span> {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-xl mb-6 text-sm">
                <p class="font-bold mb-2">Mohon periksa data berikut:</p>
                <ul class="list-disc list-inside space-y-1 opacity-90">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('marketplace.checkout.process', $order->id) }}" method="POST" id="marketplace-checkout-form">
            @csrf

            <!-- Hidden Shipping Fields -->
            <input type="hidden" name="destination_city_id" id="destination_city_id" value="{{ old('destination_city_id', $order->destination_city_id) }}">
            <input type="hidden" name="shipping_courier" id="shipping_courier" value="{{ old('shipping_courier', $order->shipping_courier ?? 'jne') }}">
            <input type="hidden" name="shipping_service_name" id="shipping_service_name" value="{{ old('shipping_service_name', $order->shipping_service_name ?? 'REG') }}">
            <input type="hidden" name="shipping_etd" id="shipping_etd" value="{{ old('shipping_etd', $order->shipping_etd ?? '2-3 hari') }}">
            <input type="hidden" name="shipping_cost" id="shipping_cost_input" value="{{ old('shipping_cost', $order->shipping_cost ?? 20000) }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Side: Shipping & Payment -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Step 1: Shipping Address -->
                    <div class="bg-slate-900/60 rounded-2xl border border-slate-800 p-6 md:p-8 shadow-xl">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                            <div class="w-6 h-6 rounded-lg bg-neon text-dark font-black text-xs flex items-center justify-center">1</div>
                            <h2 class="text-base font-bold text-white uppercase tracking-wider">Alamat & Pengiriman</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs text-slate-400 font-bold uppercase mb-1.5">Nama Penerima</label>
                                <input
                                    type="text"
                                    name="shipping_name"
                                    value="{{ old('shipping_name', $order->shipping_name ?? (auth()->user()->name ?? '')) }}"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon transition"
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 font-bold uppercase mb-1.5">Nomor Handphone / WhatsApp</label>
                                <input
                                    type="text"
                                    name="shipping_phone"
                                    value="{{ old('shipping_phone', $order->shipping_phone ?? (auth()->user()->phone ?? '')) }}"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon transition"
                                    required
                                >
                            </div>
                            
                            <!-- Destination City with Floating Autocomplete (ala GPX Submit) -->
                            <div class="md:col-span-2 relative" id="checkout-city-autocomplete-wrapper">
                                <label class="block text-xs text-slate-400 font-bold uppercase mb-1.5">
                                    Kota / Kabupaten Tujuan <span class="text-neon font-semibold">(Cari Otomatis RajaOngkir)</span>
                                </label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        name="shipping_city"
                                        id="input-shipping-city"
                                        value="{{ old('shipping_city', $order->shipping_city) }}"
                                        placeholder="Ketik nama kota tujuan... (contoh: Jakarta Selatan, Surabaya, Bandung, Sleman)"
                                        class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon transition"
                                        autocomplete="off"
                                        required
                                    >
                                    <div id="city-loading-spinner" class="hidden absolute right-3 top-1/2 -translate-y-1/2">
                                        <svg class="animate-spin h-4 w-4 text-neon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Floating Autocomplete Dropdown List -->
                                <div id="checkout-city-autocomplete-list" class="hidden absolute left-0 right-0 top-full mt-1.5 bg-slate-950 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-56 overflow-y-auto divide-y divide-slate-800">
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs text-slate-400 font-bold uppercase mb-1.5">Alamat Lengkap</label>
                                <textarea
                                    name="shipping_address"
                                    rows="2"
                                    placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan..."
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon transition resize-none"
                                    required
                                >{{ old('shipping_address', $order->shipping_address) }}</textarea>
                            </div>

                            <div>
                                <label class="block text-xs text-slate-400 font-bold uppercase mb-1.5">Kode Pos (Opsional)</label>
                                <input
                                    type="text"
                                    name="shipping_postal_code"
                                    id="input-shipping-postal"
                                    value="{{ old('shipping_postal_code', $order->shipping_postal_code) }}"
                                    placeholder="Contoh: 12345"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon transition"
                                >
                            </div>

                            <div>
                                <label class="block text-xs text-slate-400 font-bold uppercase mb-1.5">Catatan Pengiriman (Opsional)</label>
                                <input
                                    type="text"
                                    name="shipping_note"
                                    value="{{ old('shipping_note', $order->shipping_note) }}"
                                    placeholder="Contoh: Titip di pos satpam"
                                    class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-neon transition"
                                >
                            </div>
                        </div>

                        <!-- Step 1.2: Dynamic Shipping Courier Selection -->
                        <div class="mt-6 pt-5 border-t border-slate-800">
                            <label class="block text-xs text-slate-400 font-bold uppercase mb-3">
                                Pilihan Layanan Kurir (Tarif Real-Time)
                            </label>
                            
                            <div id="shipping-courier-options" class="space-y-2.5">
                                @foreach($shippingOptions as $index => $opt)
                                    @php
                                        $isSelected = ($index === 0);
                                    @endphp
                                    <label class="courier-card flex items-center justify-between p-3.5 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 cursor-pointer transition {{ $isSelected ? 'border-neon/60 bg-slate-900' : '' }}">
                                        <div class="flex items-center gap-3">
                                            <input 
                                                type="radio" 
                                                name="courier_radio" 
                                                value="{{ $opt['courier_code'] }}" 
                                                data-service="{{ $opt['service'] }}"
                                                data-cost="{{ $opt['cost'] }}"
                                                data-etd="{{ $opt['etd'] }}"
                                                data-courier="{{ $opt['courier_code'] }}"
                                                class="text-neon focus:ring-0"
                                                {{ $isSelected ? 'checked' : '' }}
                                            >
                                            <div>
                                                <span class="text-xs font-bold text-white uppercase">{{ $opt['courier_name'] }} - {{ $opt['service'] }}</span>
                                                <span class="text-xs text-slate-400 block">{{ $opt['description'] }} (Estimasi: {{ $opt['etd'] }})</span>
                                            </div>
                                        </div>
                                        <div class="font-bold text-xs text-white">
                                            {{ $opt['formatted_cost'] ?? ('Rp ' . number_format($opt['cost'], 0, ',', '.')) }}
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @php
                        $initialShippingCost = (float) (old('shipping_cost', $order->shipping_cost ?? ($shippingOptions[0]['cost'] ?? 20000)));
                        $estimatedTotal = $productSubtotal + $initialShippingCost;
                        $walletBalance = (float) ($wallet?->balance ?? 0);
                        $walletSufficient = $walletBalance >= $estimatedTotal;
                    @endphp

                    <!-- Step 2: Payment Method -->
                    <div class="bg-slate-900/60 rounded-2xl border border-slate-800 p-6 md:p-8 shadow-xl">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                            <div class="w-6 h-6 rounded-lg bg-neon text-dark font-black text-xs flex items-center justify-center">2</div>
                            <h2 class="text-base font-bold text-white uppercase tracking-wider">Metode Pembayaran</h2>
                        </div>

                        <div class="space-y-3">
                            <!-- Wallet Option -->
                            <label class="flex items-center justify-between p-4 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 cursor-pointer transition">
                                <div class="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="wallet"
                                        class="text-neon focus:ring-0"
                                        {{ old('payment_method', $walletSufficient ? 'wallet' : 'midtrans') === 'wallet' ? 'checked' : '' }}
                                    >
                                    <div>
                                        <p class="font-bold text-white text-sm">Saldo Dompet RuangLari</p>
                                        <p class="text-xs text-slate-400 mt-0.5">
                                            Saldo: <span class="text-neon font-bold">Rp {{ number_format($walletBalance, 0, ',', '.') }}</span>
                                        </p>
                                    </div>
                                </div>
                            </label>

                            <!-- Midtrans Option -->
                            <label class="flex items-center justify-between p-4 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 cursor-pointer transition">
                                <div class="flex items-center gap-3">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="midtrans"
                                        class="text-neon focus:ring-0"
                                        {{ old('payment_method', $walletSufficient ? 'wallet' : 'midtrans') === 'midtrans' ? 'checked' : '' }}
                                    >
                                    <div>
                                        <p class="font-bold text-white text-sm">Virtual Account / QRIS / e-Wallet (Midtrans)</p>
                                        <p class="text-xs text-slate-400 mt-0.5">BCA, Mandiri, BNI, BRI, QRIS, Gopay, ShopeePay</p>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            id="marketplace-checkout-submit"
                            class="mt-6 w-full py-3.5 bg-neon hover:bg-white text-dark font-black text-xs tracking-wider uppercase rounded-xl transition shadow-lg"
                        >
                            Bayar Sekarang
                        </button>
                    </div>
                </div>

                <!-- Right Side: Order Summary -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24">
                        <div class="bg-slate-900/60 rounded-2xl border border-slate-800 p-6 shadow-xl space-y-5">
                            <h3 class="text-xs font-black text-white uppercase tracking-wider pb-3 border-b border-slate-800">
                                Ringkasan Pembelian
                            </h3>

                            @php($item = $order->items->first())
                            @if($item)
                                <div class="flex gap-3 pb-4 border-b border-slate-800">
                                    <div class="w-14 h-14 bg-slate-950 rounded-xl overflow-hidden shrink-0 border border-slate-800 flex items-center justify-center">
                                        @if($item->product && $item->product->primaryImage)
                                            <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="text-slate-700 text-xs">NO IMG</div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-white line-clamp-2 leading-snug">{{ $item->product_title_snapshot }}</p>
                                        <p class="text-xs text-slate-400 font-semibold mt-1">
                                            Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Financial details -->
                            <div class="space-y-2 text-xs font-medium">
                                <div class="flex justify-between text-slate-400">
                                    <span>Subtotal Produk:</span>
                                    <span class="text-white font-bold">Rp {{ number_format($productSubtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-slate-400">
                                    <span>Ongkos Kirim:</span>
                                    <span class="text-white font-bold" id="shipping-cost-display">Rp {{ number_format($initialShippingCost, 0, ',', '.') }}</span>
                                </div>
                                <div class="border-t border-slate-800 pt-2 flex justify-between text-sm font-bold">
                                    <span class="text-white">Total Bayar:</span>
                                    <span class="text-neon font-black" id="total-amount-display">Rp {{ number_format($estimatedTotal, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Escrow Security Notice -->
                            <div class="p-3 bg-slate-950 rounded-xl border border-slate-850 text-xs text-slate-400 leading-relaxed">
                                <strong class="text-white block mb-0.5">Rekening Bersama (Escrow)</strong>
                                Dana Anda aman tertampung di platform RuangLari. Dana baru akan diteruskan ke penjual setelah barang Anda terima sesuai deskripsi.
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
        const productSubtotal = {{ (int) $productSubtotal }};
        const orderId = {{ (int) $order->id }};
        const cityInput = document.getElementById('input-shipping-city');
        const cityAutoList = document.getElementById('checkout-city-autocomplete-list');
        const cityWrapper = document.getElementById('checkout-city-autocomplete-wrapper');
        const citySpinner = document.getElementById('city-loading-spinner');
        const destCityIdInput = document.getElementById('destination_city_id');
        const postalInput = document.getElementById('input-shipping-postal');
        
        const courierOptionsContainer = document.getElementById('shipping-courier-options');
        const shippingCostDisplay = document.getElementById('shipping-cost-display');
        const totalAmountDisplay = document.getElementById('total-amount-display');
        
        const courierCodeInput = document.getElementById('shipping_courier');
        const courierServiceInput = document.getElementById('shipping_service_name');
        const courierEtdInput = document.getElementById('shipping_etd');
        const shippingCostInput = document.getElementById('shipping_cost_input');

        let debounceTimer = null;

        // Bind radio selection change
        function bindCourierRadios() {
            document.querySelectorAll('input[name="courier_radio"]').forEach(radio => {
                radio.addEventListener('change', function () {
                    const cost = parseInt(this.getAttribute('data-cost') || '0', 10);
                    const service = this.getAttribute('data-service') || '';
                    const etd = this.getAttribute('data-etd') || '';
                    const courier = this.getAttribute('data-courier') || '';

                    courierCodeInput.value = courier;
                    courierServiceInput.value = service;
                    courierEtdInput.value = etd;
                    shippingCostInput.value = cost;

                    const total = productSubtotal + cost;
                    shippingCostDisplay.textContent = 'Rp ' + cost.toLocaleString('id-ID');
                    totalAmountDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');

                    // Highlight card
                    document.querySelectorAll('.courier-card').forEach(card => card.classList.remove('border-neon/60', 'bg-slate-900'));
                    const parent = this.closest('.courier-card');
                    if (parent) {
                        parent.classList.add('border-neon/60', 'bg-slate-900');
                    }
                });
            });
        }
        bindCourierRadios();

        // City Autocomplete Functionality
        function closeCityAutocomplete() {
            if (cityAutoList) {
                cityAutoList.classList.add('hidden');
                cityAutoList.innerHTML = '';
            }
        }

        function renderCityAutocomplete(cities) {
            if (!cityAutoList) return;
            cityAutoList.innerHTML = '';

            if (cities.length === 0) {
                cityAutoList.innerHTML = '<div class="px-4 py-3 text-xs text-slate-500">Kota tidak ditemukan</div>';
                cityAutoList.classList.remove('hidden');
                return;
            }

            cities.forEach(city => {
                const item = document.createElement('div');
                item.className = 'px-4 py-2.5 hover:bg-slate-900 cursor-pointer text-xs text-white transition flex items-center justify-between';
                item.innerHTML = `
                    <div>
                        <span class="font-bold text-white">${city.type || 'Kota'} ${city.city_name || ''}</span>
                        <span class="text-slate-400 text-xs block">${city.province || ''}</span>
                    </div>
                    ${city.postal_code ? `<span class="text-xs text-slate-500 font-semibold">${city.postal_code}</span>` : ''}
                `;

                item.addEventListener('click', function () {
                    cityInput.value = (city.type ? city.type + ' ' : '') + (city.city_name || '') + (city.province ? ', ' + city.province : '');
                    destCityIdInput.value = city.city_id || city.id;
                    if (city.postal_code && postalInput && !postalInput.value) {
                        postalInput.value = city.postal_code;
                    }
                    closeCityAutocomplete();

                    // Calculate real-time shipping costs via RajaOngkir
                    fetchShippingRates(city.city_id || city.id);
                });

                cityAutoList.appendChild(item);
            });

            cityAutoList.classList.remove('hidden');
        }

        function fetchShippingRates(cityId) {
            if (!cityId) return;

            courierOptionsContainer.innerHTML = `
                <div class="p-4 rounded-xl border border-slate-800 bg-slate-950 text-center text-xs text-slate-400 animate-pulse">
                    Menghitung ongkos kirim real-time RajaOngkir...
                </div>
            `;

            fetch('{{ route("marketplace.checkout.shipping-cost") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId,
                    destination_city_id: cityId
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.options && data.options.length > 0) {
                    let html = '';
                    data.options.forEach((opt, idx) => {
                        const isFirst = (idx === 0);
                        html += `
                            <label class="courier-card flex items-center justify-between p-3.5 rounded-xl border border-slate-800 bg-slate-950 hover:border-slate-700 cursor-pointer transition ${isFirst ? 'border-neon/60 bg-slate-900' : ''}">
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="radio" 
                                        name="courier_radio" 
                                        value="${opt.courier_code}" 
                                        data-service="${opt.service}"
                                        data-cost="${opt.cost}"
                                        data-etd="${opt.etd}"
                                        data-courier="${opt.courier_code}"
                                        class="text-neon focus:ring-0"
                                        ${isFirst ? 'checked' : ''}
                                    >
                                    <div>
                                        <span class="text-xs font-bold text-white uppercase">${opt.courier_name} - ${opt.service}</span>
                                        <span class="text-xs text-slate-400 block">${opt.description} (Estimasi: ${opt.etd})</span>
                                    </div>
                                </div>
                                <div class="font-bold text-xs text-white">
                                    ${opt.formatted_cost || ('Rp ' + opt.cost.toLocaleString('id-ID'))}
                                </div>
                            </label>
                        `;
                    });

                    courierOptionsContainer.innerHTML = html;
                    bindCourierRadios();

                    // Trigger first selection
                    const firstRadio = courierOptionsContainer.querySelector('input[name="courier_radio"]:checked');
                    if (firstRadio) {
                        firstRadio.dispatchEvent(new Event('change'));
                    }
                }
            })
            .catch(err => {
                console.error('Shipping calculation error:', err);
            });
        }

        if (cityInput) {
            cityInput.addEventListener('input', function () {
                const q = this.value.trim();
                clearTimeout(debounceTimer);

                if (q.length < 2) {
                    closeCityAutocomplete();
                    return;
                }

                if (citySpinner) citySpinner.classList.remove('hidden');

                debounceTimer = setTimeout(() => {
                    fetch('{{ route("marketplace.cities.autocomplete") }}?q=' + encodeURIComponent(q), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(cities => {
                        renderCityAutocomplete(cities);
                    })
                    .catch(err => {
                        console.error('City search error:', err);
                    })
                    .finally(() => {
                        if (citySpinner) citySpinner.classList.add('hidden');
                    });
                }, 250);
            });

            document.addEventListener('click', function (e) {
                if (cityWrapper && !cityWrapper.contains(e.target)) {
                    closeCityAutocomplete();
                }
            });
        }
    });
</script>
@endpush
