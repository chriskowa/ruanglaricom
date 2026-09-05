@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Shopping Cart - RuangLari')

@section('content')
<div class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-[#08111F] text-slate-200">
    
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter mb-2 uppercase">
                KERANJANG <span class="text-[#B8FF00]">BELANJA</span>
            </h1>
            <p class="text-xs text-slate-400 font-mono">Kelola barang marketplace dan program lari pilihan Anda sebelum checkout.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-4">
                @if($cartItems->count() > 0)
                    <div class="flex justify-end mb-4">
                        <form action="{{ route('marketplace.cart.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan keranjang belanja?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-bold flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                Kosongkan Keranjang
                            </button>
                        </form>
                    </div>

                    @foreach($cartItems as $item)
                        <?php
                            $isProduct = (bool) $item->product_id;
                            $title = $isProduct ? ($item->product->title ?? 'Produk') : ($item->program->title ?? 'Program');
                            $url = $isProduct ? route('marketplace.show', $item->product->slug ?? '#') : route('programs.show', $item->program->slug ?? '#');
                            $subtitle = $isProduct ? ('Penjual: ' . ($item->product->seller->name ?? 'Seller')) : ('Coach: ' . ($item->program->coach->name ?? 'Coach'));
                        ?>

                        <div class="bg-[#0E1A2D] rounded-2xl border border-[#1F2D44] p-4 md:p-6 hover:border-[#B8FF00]/40 transition-all group relative overflow-hidden shadow-xl">
                            <div class="flex flex-col md:flex-row gap-6 relative z-10">
                                <!-- Image -->
                                <div class="w-full md:w-32 h-32 rounded-xl overflow-hidden shrink-0 border border-[#1F2D44] bg-[#08111F] flex items-center justify-center p-2">
                                    @if($isProduct && $item->product && $item->product->primaryImage)
                                        <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" class="max-h-full max-w-full object-contain group-hover:scale-105 transition duration-500">
                                    @elseif(!$isProduct && $item->program)
                                        <img src="{{ $item->program->image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-600">
                                            <svg class="w-8 h-8 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Details -->
                                <div class="flex-grow flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between items-start gap-4">
                                            <div>
                                                <div class="text-[10px] text-[#B8FF00] font-mono uppercase tracking-wider mb-0.5">
                                                    {{ $isProduct ? 'Produk Marketplace' : 'Program Latihan' }}
                                                </div>
                                                <h3 class="text-base md:text-lg font-bold text-white mb-1 group-hover:text-[#B8FF00] transition-colors leading-snug">
                                                    <a href="{{ $url }}">{{ $title }}</a>
                                                </h3>
                                                <p class="text-xs text-slate-400 mb-2">{{ $subtitle }}</p>
                                            </div>

                                            <form action="{{ route('marketplace.cart.remove', $item->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-all" title="Hapus Item">
                                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </form>
                                        </div>

                                        @if($isProduct && $item->product)
                                            <div class="flex flex-wrap gap-2 mt-1">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-[#111F35] text-slate-300 border border-[#1F2D44]">Size: {{ $item->product->size ?: '-' }}</span>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-[#111F35] text-[#B8FF00] border border-[#1F2D44]">{{ $item->product->condition == 'new' ? 'Baru' : 'Bekas' }}</span>
                                                @if($item->product->is_sold || $item->product->stock < 1)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-500/20 text-rose-300 border border-rose-500/30">Stok Habis / Terjual</span>
                                                @elseif($item->product->isReservedByOther())
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                                        Sedang Di-checkout Pembeli Lain (Sisa {{ $item->product->getReservationRemainingMinutes() }}m)
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex justify-between items-end mt-4">
                                        <!-- Quantity Control -->
                                        <div class="flex items-center gap-2 bg-[#08111F] rounded-xl p-1 border border-[#1F2D44]">
                                            <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-[#111F35] text-slate-400 hover:text-white transition-colors" {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                                            <span class="w-8 text-center font-bold text-white text-xs font-mono" id="qty-text-{{ $item->id }}">{{ $item->quantity }}</span>
                                            <button onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-[#111F35] text-slate-400 hover:text-white transition-colors" {{ $item->quantity >= 10 ? 'disabled' : '' }}>+</button>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-[10px] text-slate-400 uppercase tracking-widest font-mono mb-0.5">Subtotal</p>
                                            <p class="text-lg font-black text-[#B8FF00] font-mono">Rp <span id="subtotal-{{ $item->id }}">{{ number_format($item->subtotal, 0, ',', '.') }}</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="bg-[#0E1A2D] rounded-3xl border border-[#1F2D44] p-12 text-center border-dashed">
                        <div class="w-16 h-16 bg-[#111F35] rounded-full flex items-center justify-center mx-auto mb-4 text-[#B8FF00]">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Keranjang Belanja Anda Kosong</h3>
                        <p class="text-xs text-slate-400 mb-6 font-mono">Jelajahi marketplace untuk menemukan sepatu lari, perlengkapan, dan program latihan pilihan.</p>
                        <a href="{{ route('marketplace.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#B8FF00] text-[#08111F] font-black text-xs uppercase tracking-wider hover:bg-[#9FE000] transition shadow-lg shadow-[#B8FF00]/20">
                            Jelajahi Marketplace
                        </a>
                    </div>
                @endif
            </div>

            <!-- Summary Card -->
            <div>
                <div class="bg-[#0E1A2D] rounded-3xl border border-[#1F2D44] p-6 shadow-xl sticky top-24">
                    <h3 class="text-lg font-black text-white italic uppercase tracking-tight mb-6 border-b border-[#1F2D44] pb-4">
                        RINGKASAN <span class="text-[#B8FF00]">BELANJA</span>
                    </h3>

                    <div class="space-y-4 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>Subtotal ({{ $cartItems->count() }} item)</span>
                            <span class="font-mono text-white font-bold">Rp <span id="cart-subtotal">{{ number_format($subtotal, 0, ',', '.') }}</span></span>
                        </div>
                        <div class="flex justify-between text-slate-400">
                            <span>Ongkos Kirim / Biaya Layanan</span>
                            <span class="font-mono text-slate-400">Dihitung saat Checkout</span>
                        </div>
                        <div class="border-t border-[#1F2D44] pt-4 flex justify-between items-center text-sm">
                            <span class="font-bold text-white uppercase font-mono">Total Estimasi</span>
                            <span class="font-black text-xl text-[#B8FF00] font-mono">Rp <span id="cart-total">{{ number_format($total, 0, ',', '.') }}</span></span>
                        </div>
                    </div>

                    @if($cartItems->count() > 0)
                        <?php
                            $firstProduct = $cartItems->first(function($it) {
                                return !empty($it->product_id);
                            });
                            $isFirstProductBlocked = $firstProduct && $firstProduct->product && (
                                $firstProduct->product->is_sold ||
                                $firstProduct->product->stock < 1 ||
                                $firstProduct->product->isReservedByOther()
                            );
                        ?>
                        @if($firstProduct)
                            @if($isFirstProductBlocked)
                                <div class="mt-6 p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-center">
                                    <p class="text-xs text-amber-300 font-bold">Item Terkunci Sementara</p>
                                    <p class="text-[11px] text-slate-400 mt-1">Item dalam keranjang sedang dalam proses checkout pembeli lain. Jika pembayaran pembeli tersebut tidak diselesaikan, item akan otomatis terbuka kembali.</p>
                                    <button type="button" disabled class="w-full mt-3 py-3 rounded-xl bg-slate-800 text-slate-500 font-bold text-xs uppercase tracking-wider cursor-not-allowed">
                                        Checkout Terkunci
                                    </button>
                                </div>
                            @else
                                <form action="{{ route('marketplace.checkout.init') }}" method="POST" class="mt-6">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $firstProduct->product_id }}">
                                    <button type="submit" class="w-full py-4 rounded-xl bg-[#B8FF00] text-[#08111F] font-black text-xs uppercase tracking-wider hover:bg-[#9FE000] transition flex items-center justify-center gap-2 shadow-lg shadow-[#B8FF00]/20">
                                        <span>Lanjut ke Checkout</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endif

                    <div class="mt-6 text-[11px] text-slate-400 font-mono text-center flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Jaminan pembayaran aman RuangLari Escrow.</span>
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
