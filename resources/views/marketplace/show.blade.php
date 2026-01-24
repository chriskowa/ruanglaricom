@extends('layouts.pacerhub')

@section('title', ($product->title ?? 'Product') . ' - Marketplace')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    @php
        $isAuction = $product->sale_type === 'auction';
        $now = now();
        $auctionRunning = $isAuction
            && $product->auction_status === 'running'
            && (! $product->auction_start_at || $now->gte($product->auction_start_at))
            && (! $product->auction_end_at || $now->lt($product->auction_end_at));
        $auctionEnded = $isAuction && ($product->auction_status === 'ended' || ($product->auction_end_at && $now->gte($product->auction_end_at)));
        $currentBid = $product->current_price ?? $product->starting_price ?? $product->price;
        $minIncrement = $product->min_increment ?? 0;
        $minAllowedBid = ($recentBids->count() > 0) ? ($currentBid + $minIncrement) : ($product->starting_price ?? $product->price);
    @endphp

    @if(session('success'))
        <div class="mb-6 bg-green-900/20 border border-green-500/30 text-green-300 px-5 py-3 rounded-xl">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-900/20 border border-red-500/30 text-red-300 px-5 py-3 rounded-xl">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Breadcrumb -->
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500 font-mono">
        <a href="{{ route('marketplace.index') }}" class="hover:text-neon transition-colors">MARKETPLACE</a>
        <span>/</span>
        <a href="{{ route('marketplace.index', ['category' => $product->category->slug]) }}" class="hover:text-neon transition-colors uppercase">{{ $product->category->name }}</a>
        <span>/</span>
        <span class="text-slate-300 truncate max-w-[200px]">{{ $product->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        
        <!-- Left: Image Gallery -->
        <div class="space-y-4">
            <div class="aspect-square bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden relative group">
                @if($product->primaryImage)
                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" class="w-full h-full object-contain p-4 group-hover:scale-105 transition duration-500">
                @else
                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-600">
                        <svg class="w-16 h-16 mb-2 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span class="text-sm font-mono uppercase">No Image</span>
                    </div>
                @endif
                <div class="absolute top-4 left-4">
                     <span class="bg-dark/80 backdrop-blur border border-slate-700 text-white text-sm font-bold px-3 py-1.5 rounded uppercase tracking-wider shadow-lg">
                        {{ $product->condition == 'new' ? 'Brand New' : 'Used Condition' }}
                    </span>
                </div>
            </div>

            @if($product->images->count() > 1)
            <div class="grid grid-cols-4 gap-4">
                @foreach($product->images as $img)
                    <div class="aspect-square bg-slate-900 border border-slate-800 rounded-xl overflow-hidden cursor-pointer hover:border-neon transition-colors">
                        <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Right: Product Info -->
        <div class="flex flex-col h-full">
            <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter mb-4 leading-tight">
                {{ strtoupper($product->title) }}
            </h1>

            <div class="flex items-center gap-4 mb-8">
                @if($isAuction)
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Current Bid</div>
                        <div class="text-3xl font-black text-neon italic">
                            Rp {{ number_format($currentBid, 0, ',', '.') }}
                        </div>
                    </div>
                @else
                    <div class="text-3xl font-black text-neon italic">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </div>
                @endif
                <div class="h-6 w-px bg-slate-700"></div>
                <div class="text-sm text-slate-400">
                    Listed {{ $product->created_at->diffForHumans() }}
                </div>
                @if($isAuction)
                    <div class="ml-auto flex items-center gap-2">
                        <span class="bg-neon/20 border border-neon/40 text-neon text-xs font-black px-3 py-1.5 rounded uppercase tracking-wider">
                            LELANG
                        </span>
                        @if($auctionRunning && $product->auction_end_at)
                            <span class="text-xs text-slate-400 font-mono">
                                Ends {{ $product->auction_end_at->diffForHumans($now, null, false, 2) }}
                            </span>
                        @elseif($auctionEnded)
                            <span class="text-xs text-slate-500 font-mono">Ended</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Seller Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-8 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-slate-800 border border-slate-700 overflow-hidden flex-shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($product->seller->name) }}&background=random" class="w-full h-full object-cover">
                </div>
                <div>
                    <div class="text-xs text-slate-500 uppercase tracking-wider mb-0.5">Seller</div>
                    <div class="font-bold text-white">{{ $product->seller->name }}</div>
                </div>
                <div class="ml-auto">
                    <button class="text-xs font-bold text-slate-400 hover:text-white border border-slate-700 hover:border-slate-500 px-3 py-1.5 rounded-lg transition-all">
                        View Profile
                    </button>
                </div>
            </div>

            @if($product->fulfillment_mode === 'consignment')
                <div class="mb-8 bg-cyan-500/10 border border-cyan-400/20 p-5 rounded-xl">
                    <h3 class="font-bold text-cyan-200 mb-2 text-sm uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                        Titip Jual
                    </h3>
                    <div class="text-sm text-slate-300">
                        Produk ini dijual via titip jual. Proses pengepakan dan pengiriman ditangani oleh tim RuangLari.
                    </div>
                </div>
            @endif

            <!-- Description -->
            <div class="mb-8 flex-1">
                <h3 class="font-bold text-white text-lg mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                    Description
                </h3>
                <div class="prose prose-invert prose-sm text-slate-300 leading-relaxed max-w-none">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>

            @if($product->type === 'digital_slot' && !empty($product->meta_data))
            <div class="mb-8 bg-blue-900/20 border border-blue-500/30 p-5 rounded-xl">
                <h3 class="font-bold text-blue-400 mb-4 text-sm uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Race Details
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-500 text-xs mb-1">Race Name</div>
                        <div class="font-bold text-white">{{ $product->meta_data['race_name'] ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs mb-1">Race Date</div>
                        <div class="font-bold text-white">{{ $product->meta_data['race_date'] ?? '-' }}</div>
                    </div>
                    <div class="col-span-2 border-t border-blue-500/20 pt-3 mt-1">
                        <div class="text-slate-500 text-xs mb-1">Transfer Policy</div>
                        <div class="font-medium text-slate-300">{{ $product->meta_data['transfer_policy'] ?? '-' }}</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="space-y-4">
                @if($isAuction)
                    @if($auctionRunning)
                        @auth
                            <form action="{{ route('marketplace.auction.bid', $product->slug) }}" method="POST" class="space-y-3">
                                @csrf
                                <div class="grid grid-cols-1 gap-3">
                                    <div>
                                        <div class="text-xs text-slate-500 mb-1">Minimal Bid</div>
                                        <div class="text-sm font-bold text-white">Rp {{ number_format($minAllowedBid, 0, ',', '.') }}</div>
                                    </div>
                                    <input type="number" name="amount" min="{{ $minAllowedBid }}" step="1000" required
                                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                        placeholder="Masukkan nominal bid">
                                    <button type="submit" class="w-full bg-neon text-dark font-black text-lg py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.01] transition-all flex items-center justify-center gap-2">
                                        BID NOW
                                    </button>
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Bid di menit terakhir akan memperpanjang waktu lelang otomatis.
                                </div>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="w-full bg-neon text-dark font-black text-lg py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.01] transition-all flex items-center justify-center gap-2">
                                <span>LOGIN TO BID</span>
                            </a>
                        @endauth
                    @elseif($auctionEnded)
                        <div class="w-full bg-slate-900 border border-slate-800 rounded-xl p-4">
                            <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Lelang Berakhir</div>
                            @if($product->auction_winner_id)
                                <div class="text-white font-bold mb-3">Pemenang sudah ditentukan.</div>
                                @auth
                                    @if(auth()->id() === $product->auction_winner_id)
                                        <a href="{{ route('marketplace.orders.index') }}" class="inline-flex items-center justify-center w-full bg-neon text-dark font-black text-lg py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.01] transition-all">
                                            LIHAT ORDER \u0026 BAYAR
                                        </a>
                                    @endif
                                @endauth
                            @else
                                <div class="text-slate-300">Tidak ada pemenang (reserve tidak terpenuhi atau tidak ada bid).</div>
                            @endif
                        </div>
                    @endif
                @else
                    @if($product->stock > 0)
                        @auth
                        <form action="{{ route('marketplace.checkout.init') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="w-full bg-neon text-dark font-black text-lg py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.01] transition-all flex items-center justify-center gap-2">
                                <span>BUY NOW</span>
                                <span class="text-sm font-medium opacity-70">(Secure Escrow)</span>
                            </button>
                        </form>
                        
                        <a href="{{ route('chat.show', $product->seller->id) }}" class="w-full bg-slate-800 text-slate-300 font-bold py-3 rounded-xl hover:bg-slate-700 hover:text-white transition-all flex items-center justify-center gap-2 border border-slate-700">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            Chat Seller
                        </a>
                        @else
                        <a href="{{ route('login') }}" class="w-full bg-neon text-dark font-black text-lg py-4 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.01] transition-all flex items-center justify-center gap-2">
                            <span>LOGIN TO BUY</span>
                        </a>
                        
                        <a href="{{ route('login') }}" class="w-full bg-slate-800 text-slate-300 font-bold py-3 rounded-xl hover:bg-slate-700 hover:text-white transition-all flex items-center justify-center gap-2 border border-slate-700">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            Login to Chat
                        </a>
                        @endauth
                    @else
                        <button disabled class="w-full bg-slate-800 text-slate-500 font-black text-lg py-4 rounded-xl border border-slate-700 cursor-not-allowed flex items-center justify-center gap-2">
                            SOLD OUT
                        </button>
                    @endif
                @endif

                <div class="flex items-center justify-center gap-2 text-xs text-slate-500 mt-4">
                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span>Transaksi aman dengan Rekening Bersama RuangLari.</span>
                </div>
            </div>

            @if($isAuction && $recentBids->count() > 0)
                <div class="mt-8 bg-slate-900 border border-slate-800 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-sm font-black text-white uppercase tracking-wider">Bid Terbaru</div>
                        <div class="text-xs text-slate-500 font-mono">{{ $recentBids->count() }} bid terakhir</div>
                    </div>
                    <div class="space-y-3">
                        @foreach($recentBids as $b)
                            <div class="flex items-center justify-between text-sm">
                                <div class="text-slate-300 font-medium truncate max-w-[180px]">{{ $b->bidder->name ?? 'User' }}</div>
                                <div class="text-white font-black">Rp {{ number_format($b->amount, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="mt-20 border-t border-slate-800 pt-10">
        <h3 class="text-2xl font-black text-white italic tracking-tighter mb-8">
            MORE FROM <span class="text-neon">{{ strtoupper($product->category->name) }}</span>
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($relatedProducts as $related)
                <div class="group">
                    <a href="{{ route('marketplace.show', $related->slug) }}" class="block aspect-square bg-slate-900 rounded-xl mb-3 overflow-hidden border border-slate-800 group-hover:border-neon/50 transition-all">
                         @if($related->primaryImage)
                            <img src="{{ asset('storage/' . $related->primaryImage->image_path) }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-700">
                                <svg class="w-8 h-8 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        @endif
                    </a>
                    <div class="font-bold text-white text-sm truncate group-hover:text-neon transition-colors">{{ $related->title }}</div>
                    <div class="text-slate-400 font-bold text-sm">Rp {{ number_format($related->price, 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
