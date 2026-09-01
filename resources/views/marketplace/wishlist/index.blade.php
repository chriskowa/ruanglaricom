@extends('layouts.pacerhub')

@section('title', 'Wishlist Saya - RuangLari Marketplace')

@section('content')
    @php($withSidebar = true)

    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                <div>
                    <h1 class="text-3xl font-black text-white italic tracking-tight uppercase">
                        Wishlist Saya
                    </h1>
                    <p class="text-slate-400 mt-1 text-sm">Daftar produk perlengkapan lari favorit yang Anda simpan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('marketplace.index') }}" class="px-4 py-2.5 rounded-md bg-neon text-dark font-black text-xs hover:bg-white transition shadow-sm">
                        <span>Jelajahi Marketplace</span>
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-slate-900 border border-slate-700 text-slate-200 px-4 py-3 rounded-md text-sm font-semibold flex items-center gap-2">
                    <span class="text-neon font-bold">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($wishlists->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($wishlists as $wl)
                        @php($product = $wl->product)
                        @if(!$product) @continue @endif
                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-4 flex flex-col justify-between hover:border-slate-700 transition group relative">
                            <!-- Quick Delete Wishlist Button -->
                            <button type="button" onclick="quickRemoveWishlist({{ $product->id }}, this)" class="absolute top-6 right-6 z-10 w-8 h-8 rounded-full bg-slate-950/80 backdrop-blur border border-slate-700 text-rose-500 hover:bg-rose-500 hover:text-white transition flex items-center justify-center" title="Hapus dari Wishlist">
                                <i class="fas fa-trash-alt text-xs"></i>
                            </button>

                            <div>
                                <a href="{{ route('marketplace.show', $product->slug) }}" class="block aspect-square bg-slate-950 rounded-md overflow-hidden mb-3 relative">
                                    @if($product->primaryImage)
                                        <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-500 text-xs font-mono">
                                            NO IMAGE
                                        </div>
                                    @endif
                                </a>

                                <div class="text-[10px] text-neon font-black uppercase tracking-wider mb-1">
                                    {{ $product->brand ? $product->brand->name : 'Gear' }}
                                </div>

                                <h3 class="text-sm font-bold text-white group-hover:text-neon transition line-clamp-2 mb-2">
                                    <a href="{{ route('marketplace.show', $product->slug) }}">{{ $product->title }}</a>
                                </h3>

                                <div class="text-base font-black text-white font-mono mb-3">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </div>

                                @if($product->seller)
                                    <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mb-4">
                                        <span>{{ $product->seller->name }}</span>
                                        @if(optional($product->seller->city)->name)
                                            <span class="text-slate-600">•</span>
                                            <span>{{ $product->seller->city->name }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('marketplace.show', $product->slug) }}" class="w-full py-2.5 bg-slate-800 text-slate-200 hover:bg-white hover:text-dark text-xs font-bold rounded-md text-center transition">
                                <span>Lihat Detail Produk</span>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $wishlists->links() }}
                </div>
            @else
                <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-12 text-center">
                    <h3 class="text-lg font-bold text-white">Wishlist Anda Masih Kosong</h3>
                    <p class="text-slate-400 text-xs mt-1 max-w-md mx-auto">Temukan perlengkapan lari impian Anda di Marketplace dan klik tombol simpan untuk melihatnya di sini.</p>
                    <a href="{{ route('marketplace.index') }}" class="mt-5 px-5 py-2.5 rounded-md bg-neon text-dark font-black text-xs hover:bg-white transition inline-flex items-center shadow-sm">
                        <span>Eksplor Produk Marketplace</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        async function quickRemoveWishlist(productId, btn) {
            if(!confirm('Hapus produk ini dari wishlist Anda?')) return;
            try {
                const res = await fetch(`/marketplace/wishlist/toggle/${productId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Gagal mengubah wishlist');
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan.');
            }
        }
    </script>
    @endpush
@endsection
