@extends('layouts.pacerhub', ['withSidebar' => true])

@section('title', 'Shopping Cart - RuangLari')

@section('content')
<div class="min-h-screen pt-20 pb-20 px-4 md:px-8 font-sans bg-[#090A0E] text-slate-200">
    
    <div class="max-w-7xl mx-auto pt-4 md:pt-6">
        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <h1 class="text-2xl font-bold text-white mb-1">
                Keranjang Belanja
            </h1>
            <p class="text-xs text-zinc-300">Kelola barang marketplace dan program lari pilihan Anda sebelum checkout.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            
            <!-- Cart Items List -->
            <div class="lg:col-span-2 space-y-4">
                @forelse($cartItems as $item)
                    @if($loop->first)
                        <div class="flex justify-end mb-2">
                            <form action="{{ route('marketplace.cart.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan keranjang belanja?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-semibold flex items-center gap-1.5 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    <span>Kosongkan Keranjang</span>
                                </button>
                            </form>
                        </div>
                    @endif

                    @php
                        $isProduct = (bool) $item->product_id;
                        $title = $isProduct ? ($item->product->title ?? 'Produk') : ($item->program->title ?? 'Program');
                        $url = $isProduct ? route('marketplace.show', $item->product->slug ?? '#') : route('programs.show', $item->program->slug ?? '#');
                        $subtitle = $isProduct ? ('Penjual: ' . ($item->product->seller->name ?? 'Seller')) : ('Coach: ' . ($item->program->coach->name ?? 'Coach'));
                    @endphp

                    <div class="bg-[#131722] rounded-lg border border-zinc-800 p-5 md:p-6 shadow-sm hover:border-zinc-700 transition-colors">
                        <div class="flex flex-col sm:flex-row gap-5">
                            <!-- Image Container -->
                            <div class="w-full sm:w-28 h-28 rounded-md overflow-hidden shrink-0 border border-zinc-800 bg-[#0B0E14] flex items-center justify-center p-2">
                                @if($isProduct && $item->product && $item->product->primaryImage)
                                    <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" alt="{{ $title }}" class="max-h-full max-w-full object-contain">
                                @elseif(!$isProduct && $item->program)
                                    <img src="{{ $item->program->image_url }}" alt="{{ $title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-600">
                                        <svg class="w-8 h-8 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Details -->
                            <div class="flex-grow flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start gap-3">
                                        <div>
                                            <span class="text-xs font-semibold text-lime-400 uppercase tracking-wider block mb-1">
                                                {{ $isProduct ? 'Produk Marketplace' : 'Program Latihan' }}
                                            </span>
                                            <h3 class="text-base font-bold text-white mb-1 hover:text-lime-400 transition-colors leading-snug">
                                                <a href="{{ $url }}">{{ $title }}</a>
                                            </h3>
                                            <p class="text-xs text-zinc-300 mb-2">{{ $subtitle }}</p>
                                        </div>

                                        <form action="{{ route('marketplace.cart.remove', $item->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-zinc-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-md transition-colors" title="Hapus Item">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </form>
                                    </div>

                                    @if($isProduct && $item->product)
                                        <div class="flex flex-wrap gap-2 mt-1">
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#1A2130] text-zinc-200 border border-zinc-700/80">Size: {{ $item->product->size ?: '-' }}</span>
                                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-[#1A2130] text-zinc-200 border border-zinc-700/80">{{ $item->product->condition == 'new' ? 'Baru' : 'Bekas' }}</span>
                                            @if($item->product->is_sold || $item->product->stock < 1)
                                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/30">Stok Habis / Terjual</span>
                                            @elseif($item->product->isReservedByOther())
                                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                    Sedang Di-checkout Pembeli Lain (Sisa {{ $item->product->getReservationRemainingMinutes() }}m)
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="flex justify-between items-end mt-4 pt-3 border-t border-zinc-800/80">
                                    <!-- Quantity Control -->
                                    <div class="flex items-center gap-1.5 bg-[#0B0E14] rounded-md p-1 border border-zinc-800">
                                        <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#1A2130] text-zinc-300 hover:text-white transition-colors" {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                                        <span class="w-8 text-center font-bold text-white text-xs font-mono" id="qty-text-{{ $item->id }}">{{ $item->quantity }}</span>
                                        <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" class="w-7 h-7 flex items-center justify-center rounded-md hover:bg-[#1A2130] text-zinc-300 hover:text-white transition-colors" {{ $item->quantity >= 10 ? 'disabled' : '' }}>+</button>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs text-zinc-300 uppercase tracking-wider mb-0.5">Subtotal</p>
                                        <p class="text-base font-bold text-white font-mono">Rp <span id="subtotal-{{ $item->id }}">{{ number_format($item->subtotal, 0, ',', '.') }}</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-[#131722] rounded-lg border border-zinc-800 p-12 text-center">
                        <div class="w-12 h-12 bg-[#1A2130] rounded-lg flex items-center justify-center mx-auto mb-4 text-zinc-300">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <h3 class="text-base font-bold text-white mb-1.5">Keranjang Belanja Kosong</h3>
                        <p class="text-xs text-zinc-300 mb-5">Jelajahi marketplace untuk menemukan sepatu lari, apparel, dan program latihan pilihan.</p>
                        <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-md bg-lime-600 hover:bg-lime-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm">
                            Jelajahi Marketplace
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Summary Card -->
            <div>
                <div class="bg-[#131722] rounded-lg border border-zinc-800 p-6 shadow-sm sticky top-24">
                    <h2 class="text-base font-bold text-white uppercase tracking-wider pb-4 border-b border-zinc-800 mb-5">
                        Ringkasan Belanja
                    </h2>

                    <div class="space-y-3.5 text-xs">
                        <div class="flex justify-between text-zinc-300">
                            <span>Subtotal ({{ $cartItems->count() }} item)</span>
                            <span class="font-mono text-white font-bold">Rp <span id="cart-subtotal">{{ number_format($subtotal, 0, ',', '.') }}</span></span>
                        </div>
                        <div class="flex justify-between text-zinc-300">
                            <span>Ongkos Kirim</span>
                            <span class="font-mono text-zinc-300">Dihitung saat Checkout</span>
                        </div>
                        <div class="border-t border-zinc-800 pt-3.5 flex justify-between items-center text-sm">
                            <span class="font-bold text-white uppercase">Total Estimasi</span>
                            <span class="font-bold text-lg text-lime-400 font-mono">Rp <span id="cart-total">{{ number_format($total, 0, ',', '.') }}</span></span>
                        </div>
                    </div>

                    @if($cartItems->count() > 0)
                        @php
                            $firstProduct = $cartItems->first(function($it) {
                                return !empty($it->product_id);
                            });
                            $isFirstProductBlocked = $firstProduct && $firstProduct->product && (
                                $firstProduct->product->is_sold ||
                                $firstProduct->product->stock < 1 ||
                                $firstProduct->product->isReservedByOther()
                            );
                        @endphp
                        @if($firstProduct)
                            @if($isFirstProductBlocked)
                                <div class="mt-5 p-3 rounded-md bg-amber-500/10 border border-amber-500/30 text-center">
                                    <p class="text-xs text-amber-300 font-bold">Item Terkunci Sementara</p>
                                    <p class="text-xs text-zinc-300 mt-1">Item dalam keranjang sedang dalam proses checkout pembeli lain. Jika pembayaran tidak diselesaikan, item akan otomatis terbuka kembali.</p>
                                    <button type="button" disabled class="w-full mt-3 py-2.5 rounded-md bg-zinc-800 text-zinc-400 font-bold text-xs uppercase tracking-wider cursor-not-allowed">
                                        Checkout Terkunci
                                    </button>
                                </div>
                            @else
                                <form action="{{ route('marketplace.checkout.init') }}" method="POST" class="mt-5">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $firstProduct->product_id }}">
                                    <button type="submit" class="w-full py-3 rounded-md bg-lime-600 hover:bg-lime-700 text-white font-bold text-sm uppercase tracking-wider transition flex items-center justify-center gap-2 shadow-sm">
                                        <span>Lanjut ke Checkout</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endif

                    <div class="mt-5 pt-4 border-t border-zinc-800/80 text-xs text-zinc-300 text-center flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-lime-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Transaksi terlindungi Escrow RuangLari</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    async function updateQuantity(cartId, newQty) {
        if (newQty < 1) return;
        try {
            const res = await fetch(`/marketplace/cart/${cartId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ quantity: newQty })
            });
            const data = await res.json();
            if (data.success) {
                document.getElementById(`qty-text-${cartId}`).innerText = newQty;
                document.getElementById(`subtotal-${cartId}`).innerText = new Intl.NumberFormat('id-ID').format(data.subtotal);
                document.getElementById('cart-subtotal').innerText = new Intl.NumberFormat('id-ID').format(data.total);
                document.getElementById('cart-total').innerText = new Intl.NumberFormat('id-ID').format(data.total);
            }
        } catch (e) {
            console.error(e);
        }
    }
</script>
@endsection
