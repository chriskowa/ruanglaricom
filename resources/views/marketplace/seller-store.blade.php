@extends('layouts.pacerhub')

@section('title', ($seller->name ?? 'Seller') . ' Store - Marketplace RuangLari')

@section('content')
<div class="min-h-screen pt-20 pb-16 px-4 md:px-8 relative overflow-hidden font-sans bg-[#08111F]">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <div class="mb-6 flex items-center gap-2 text-xs text-slate-400 font-mono">
            <a href="{{ route('marketplace.index') }}" class="hover:text-[#B8FF00] transition-colors">MARKETPLACE</a>
            <span>/</span>
            <span class="text-white uppercase font-bold">PENJUAL: {{ $seller->name }}</span>
        </div>

        <!-- Seller Header Banner Card -->
        <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-3xl p-6 md:p-8 mb-10 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-80 h-80 bg-[#B8FF00]/5 rounded-full blur-[100px] pointer-events-none"></div>

            <div class="flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10">
                <!-- Avatar -->
                <div class="w-24 h-24 md:w-28 md:h-28 rounded-full bg-[#111F35] border-2 border-[#B8FF00]/40 overflow-hidden flex-shrink-0 shadow-lg">
                    @if($seller->avatar_url)
                        <img src="{{ $seller->avatar_url }}" alt="{{ $seller->name }}" class="w-full h-full object-cover">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($seller->name) }}&background=0E1A2D&color=B8FF00&bold=true" alt="{{ $seller->name }}" class="w-full h-full object-cover">
                    @endif
                </div>

                <!-- Info -->
                <div class="text-center md:text-left flex-1 min-w-0">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mb-2">
                        <h1 class="text-2xl md:text-4xl font-black text-white italic tracking-tighter uppercase">{{ $seller->name }}</h1>
                        <span class="bg-[#B8FF00]/10 border border-[#B8FF00]/30 text-[#B8FF00] text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-wider">
                            Verified Seller
                        </span>
                    </div>

                    <p class="text-slate-400 text-xs md:text-sm font-mono mb-4">
                        @<span>{{ $seller->username ?: strtolower(str_replace(' ', '', $seller->name)) }}</span>
                        @if($seller->city)
                            <span class="mx-2">•</span>
                            <i class="fas fa-map-marker-alt text-[#B8FF00] mr-1"></i>{{ $seller->city->name }}
                        @endif
                        <span class="mx-2">•</span>
                        <span>Bergabung {{ $seller->created_at ? $seller->created_at->translatedFormat('F Y') : '2025' }}</span>
                    </p>

                    <!-- Stats Badges -->
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 pt-2">
                        <div class="bg-[#111F35] border border-[#1F2D44] px-4 py-2 rounded-2xl text-center">
                            <div class="text-xs text-slate-400 font-mono uppercase">Produk Aktif</div>
                            <div class="text-xl font-black text-white font-mono">{{ $products->total() }}</div>
                        </div>
                        <div class="bg-[#111F35] border border-[#1F2D44] px-4 py-2 rounded-2xl text-center">
                            <div class="text-xs text-slate-400 font-mono uppercase">Total Terjual</div>
                            <div class="text-xl font-black text-[#B8FF00] font-mono">{{ $salesCount }}</div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                @auth
                    @if(auth()->id() !== $seller->id)
                        <a href="{{ route('chat.show', $seller->id) }}" class="px-6 py-3 rounded-xl bg-[#B8FF00] text-[#08111F] font-black text-xs uppercase tracking-wider hover:bg-[#9FE000] hover:scale-105 transition transform flex items-center gap-2 shadow-lg shadow-[#B8FF00]/20">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            <span>Chat Penjual</span>
                        </a>
                    @endif
                @else
                    <a href="javascript:void(0)" onclick="openLoginModal()" class="px-6 py-3 rounded-xl bg-[#B8FF00] text-[#08111F] font-black text-xs uppercase tracking-wider hover:bg-[#9FE000] hover:scale-105 transition transform flex items-center gap-2 shadow-lg shadow-[#B8FF00]/20">
                        <span>Login to Chat</span>
                    </a>
                @endauth
            </div>
        </div>

        <!-- Products Section Header -->
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl md:text-2xl font-black text-white italic tracking-tight uppercase">
                PRODUK DARI <span class="text-[#B8FF00]">{{ strtoupper($seller->name) }}</span>
            </h2>
            <div class="text-xs text-slate-400 font-mono">
                Menampilkan {{ $products->count() }} dari {{ $products->total() }} barang
            </div>
        </div>

        <!-- Products Grid -->
        @if($products->count() > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @foreach($products as $prod)
                    <div class="group bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl overflow-hidden hover:border-[#B8FF00]/50 transition duration-300 flex flex-col justify-between h-full relative">
                        <div>
                            <!-- Image -->
                            <a href="{{ route('marketplace.show', $prod->slug) }}" class="block overflow-hidden relative aspect-square bg-[#08111F]">
                                @if($prod->primaryImage)
                                    <img src="{{ asset('storage/' . $prod->primaryImage->image_path) }}" alt="{{ $prod->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-600">
                                        <svg class="w-10 h-10 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    </div>
                                @endif
                                <div class="absolute top-2 left-2">
                                    <span class="bg-[#08111F]/80 backdrop-blur border border-[#1F2D44] text-[10px] text-white font-bold px-2 py-0.5 rounded uppercase">
                                        {{ $prod->condition == 'new' ? 'Baru' : 'Bekas' }}
                                    </span>
                                </div>
                            </a>

                            <!-- Body -->
                            <div class="p-4">
                                <div class="text-[10px] text-[#B8FF00] font-bold uppercase tracking-wider mb-1 truncate">
                                    {{ $prod->brand ? $prod->brand->name : ($prod->category ? $prod->category->name : 'Gear') }}
                                </div>
                                <h3 class="text-sm font-bold text-white mb-2 line-clamp-2 leading-snug hover:text-[#B8FF00] transition">
                                    <a href="{{ route('marketplace.show', $prod->slug) }}">{{ $prod->title }}</a>
                                </h3>
                                <div class="text-base font-black text-[#B8FF00] font-mono mb-3">
                                    Rp {{ number_format($prod->price, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>

                        <!-- Footer CTA -->
                        <div class="p-4 pt-0">
                            <a href="{{ route('marketplace.show', $prod->slug) }}" class="w-full py-2.5 rounded-xl bg-[#111F35] border border-[#1F2D44] text-white hover:bg-[#B8FF00] hover:text-[#08111F] font-bold text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1">
                                <span>Lihat Detail</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @else
            <div class="bg-[#0E1A2D] border border-dashed border-[#1F2D44] rounded-3xl p-12 text-center text-slate-400">
                Penjual ini belum memiliki barang jualan aktif lainnya saat ini.
            </div>
        @endif
    </div>
</div>
@endsection
