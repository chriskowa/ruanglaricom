@extends('layouts.pacerhub')

@section('title', ($seller->name ?? 'Seller') . ' Store - Marketplace RuangLari')

@section('content')
<div class="min-h-screen pt-20 pb-16 px-4 md:px-8 relative overflow-hidden font-sans bg-[#08111F]">
    <div class="max-w-7xl mx-auto">
        <!-- Breadcrumb -->
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('marketplace.index') }}" class="hover:text-[#B8FF00] transition-colors">MARKETPLACE</a>
                <span>/</span>
                <span class="text-white uppercase font-bold">PENJUAL: {{ $seller->name }}</span>
            </div>

            <!-- Share Store Button -->
            <button type="button" onclick="openShareModal('Toko {{ e($seller->name) }} di RuangLari Market', '{{ route('marketplace.seller.store', $seller->username ?: $seller->id) }}')" class="px-3.5 py-1.5 rounded-xl border border-[#1F2D44] bg-[#0E1A2D] text-xs font-bold text-slate-300 hover:text-white hover:border-[#B8FF00] transition flex items-center gap-1.5" title="Bagikan Toko">
                <svg class="w-3.5 h-3.5 text-[#B8FF00]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13" />
                </svg>
                <span>Bagikan Toko</span>
            </button>
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

                    <p class="text-slate-400 text-xs md:text-sm mb-4">
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
                            <div class="text-xs text-slate-400 uppercase font-semibold">Produk Aktif</div>
                            <div class="text-xl font-black text-white" id="seller-products-count">{{ $products->total() }}</div>
                        </div>
                        <div class="bg-[#111F35] border border-[#1F2D44] px-4 py-2 rounded-2xl text-center">
                            <div class="text-xs text-slate-400 uppercase font-semibold">Total Terjual</div>
                            <div class="text-xl font-black text-[#B8FF00]">{{ $salesCount }}</div>
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

        <!-- Filter & Search Toolbar Card -->
        <div class="bg-[#0E1A2D] border border-[#1F2D44] rounded-2xl p-4 md:p-6 mb-8 shadow-xl">
            <form id="seller-filter-form" action="{{ route('marketplace.seller.store', $seller->username ?: $seller->id) }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">
                    
                    <!-- Search Input -->
                    <div class="lg:col-span-4 relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </span>
                        <input type="text" id="seller-search" name="search" value="{{ request('search') }}" placeholder="Cari produk toko ini..." class="w-full bg-[#08111F] border border-[#1F2D44] text-white text-xs rounded-xl pl-10 pr-4 py-2.5 focus:border-[#B8FF00] focus:ring-1 focus:ring-[#B8FF00] focus:outline-none transition-all placeholder-slate-500">
                    </div>

                    <!-- Category Filter -->
                    <div class="lg:col-span-3">
                        <select id="seller-category" name="category" class="w-full bg-[#08111F] border border-[#1F2D44] text-slate-300 text-xs rounded-xl px-3.5 py-2.5 focus:border-[#B8FF00] focus:outline-none transition-all">
                            <option value="">Semua Kategori</option>
                            @if(isset($categories))
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Condition Radio/Pill -->
                    <div class="lg:col-span-3 flex gap-1.5 bg-[#08111F] p-1 border border-[#1F2D44] rounded-xl">
                        <label class="flex-1 text-center cursor-pointer">
                            <input type="radio" name="condition" value="" class="hidden peer" {{ !request('condition') ? 'checked' : '' }}>
                            <div class="py-1.5 rounded-lg text-[11px] font-bold text-slate-400 peer-checked:bg-[#B8FF00] peer-checked:text-[#08111F] transition-all">Semua</div>
                        </label>
                        <label class="flex-1 text-center cursor-pointer">
                            <input type="radio" name="condition" value="new" class="hidden peer" {{ request('condition') == 'new' ? 'checked' : '' }}>
                            <div class="py-1.5 rounded-lg text-[11px] font-bold text-slate-400 peer-checked:bg-[#B8FF00] peer-checked:text-[#08111F] transition-all">Baru</div>
                        </label>
                        <label class="flex-1 text-center cursor-pointer">
                            <input type="radio" name="condition" value="used" class="hidden peer" {{ request('condition') == 'used' ? 'checked' : '' }}>
                            <div class="py-1.5 rounded-lg text-[11px] font-bold text-slate-400 peer-checked:bg-[#B8FF00] peer-checked:text-[#08111F] transition-all">Bekas</div>
                        </label>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="lg:col-span-2">
                        <select id="seller-sort" name="sort" class="w-full bg-[#08111F] border border-[#1F2D44] text-slate-300 text-xs rounded-xl px-3.5 py-2.5 focus:border-[#B8FF00] focus:outline-none transition-all">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga: Rendah</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga: Tinggi</option>
                        </select>
                    </div>

                </div>
            </form>
        </div>

        <!-- Products Section Header -->
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-xl md:text-2xl font-black text-white italic tracking-tight uppercase">
                PRODUK DARI <span class="text-[#B8FF00]">{{ strtoupper($seller->name) }}</span>
            </h2>
            <div id="seller-results-count" class="text-xs text-slate-400 font-medium">
                Menampilkan {{ $products->count() }} dari {{ $products->total() }} barang
            </div>
        </div>

        <!-- Products Grid Container (AJAX Target) -->
        <div id="seller-product-grid-container" class="relative min-h-[300px]">
            <div id="seller-grid-loader" class="hidden absolute inset-0 bg-[#08111F]/70 backdrop-blur-xs z-30 flex items-center justify-center rounded-2xl">
                <div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[#0E1A2D] border border-[#1F2D44] text-white text-xs font-bold shadow-2xl">
                    <div class="w-4 h-4 border-2 border-[#B8FF00] border-t-transparent rounded-full animate-spin"></div>
                    <span>Memuat Produk...</span>
                </div>
            </div>

            @include('marketplace.partials.seller-product-grid')
        </div>
    </div>
</div>

<!-- Share Modal Component -->
<div id="mp-share-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-sm hidden" onclick="if(event.target===this) closeShareModal();">
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 max-w-sm w-full mx-4 shadow-2xl relative">
        <button type="button" onclick="closeShareModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-xl font-bold w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center transition">&times;</button>
        
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-2xl bg-[#B8FF00]/10 border border-[#B8FF00]/30 flex items-center justify-center text-[#B8FF00] shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-sm font-bold text-white">Bagikan</h3>
                <p id="mp-share-title" class="text-xs text-slate-400 truncate">Halaman Toko / Produk</p>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2.5 my-5">
            <!-- WhatsApp -->
            <a id="mp-share-wa" href="#" target="_blank" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/20 transition group">
                <i class="fa-brands fa-whatsapp text-2xl group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold">WhatsApp</span>
            </a>
            <!-- Facebook -->
            <a id="mp-share-fb" href="#" target="_blank" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-blue-500/10 border border-blue-500/30 text-blue-400 hover:bg-blue-500/20 transition group">
                <i class="fa-brands fa-facebook text-2xl group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold">Facebook</span>
            </a>
            <!-- X / Twitter -->
            <a id="mp-share-tw" href="#" target="_blank" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 transition group">
                <i class="fa-brands fa-x-twitter text-2xl group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold">Twitter</span>
            </a>
            <!-- Copy Link -->
            <button type="button" onclick="copyShareLink()" class="flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-[#B8FF00]/10 border border-[#B8FF00]/30 text-[#B8FF00] hover:bg-[#B8FF00]/20 transition group">
                <i class="fa-solid fa-link text-2xl group-hover:scale-110 transition-transform"></i>
                <span class="text-[10px] font-bold">Salin</span>
            </button>
        </div>

        <div class="relative mt-2">
            <input id="mp-share-url-input" type="text" readonly class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 pr-16 text-xs text-slate-300 focus:outline-none select-all font-medium">
            <button type="button" onclick="copyShareLink()" class="absolute right-1.5 top-1/2 -translate-y-1/2 px-3 py-1 rounded-lg bg-[#B8FF00] text-[#08111F] text-[10px] font-black hover:bg-white transition-colors">
                <span id="mp-copy-btn-text">Salin</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('seller-filter-form');
        var container = document.getElementById('seller-product-grid-container');
        var loader = document.getElementById('seller-grid-loader');
        var searchInput = document.getElementById('seller-search');
        var categorySelect = document.getElementById('seller-category');
        var sortSelect = document.getElementById('seller-sort');
        var conditionInputs = form ? form.querySelectorAll('input[name="condition"]') : [];
        var searchTimeout = null;

        function fetchFilteredProducts(url) {
            if (!form || !container) return;
            var fetchUrl = url || form.action + '?' + new URLSearchParams(new FormData(form)).toString();
            
            if (loader) loader.classList.remove('hidden');

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) { return res.text(); })
            .then(function(html) {
                container.innerHTML = '<div id="seller-grid-loader" class="hidden absolute inset-0 bg-[#08111F]/70 backdrop-blur-xs z-30 flex items-center justify-center rounded-2xl"><div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-[#0E1A2D] border border-[#1F2D44] text-white text-xs font-bold shadow-2xl"><div class="w-4 h-4 border-2 border-[#B8FF00] border-t-transparent rounded-full animate-spin"></div><span>Memuat Produk...</span></div></div>' + html;
                window.history.pushState({}, '', fetchUrl);
            })
            .catch(function(err) {
                console.error('AJAX Filter error:', err);
            })
            .finally(function() {
                if (loader) loader.classList.add('hidden');
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    fetchFilteredProducts();
                }, 350);
            });
        }

        if (categorySelect) categorySelect.addEventListener('change', function() { fetchFilteredProducts(); });
        if (sortSelect) sortSelect.addEventListener('change', function() { fetchFilteredProducts(); });
        conditionInputs.forEach(function(input) {
            input.addEventListener('change', function() { fetchFilteredProducts(); });
        });

        // Delegate pagination link clicks to AJAX
        document.addEventListener('click', function(e) {
            var pageLink = e.target.closest('.seller-pagination-wrapper a');
            if (pageLink && pageLink.href) {
                e.preventDefault();
                fetchFilteredProducts(pageLink.href);
            }
        });
    });

    function openShareModal(title, url) {
        if (navigator.share && /mobile|android|iphone/i.test(navigator.userAgent)) {
            navigator.share({
                title: title,
                url: url
            }).catch(function() {});
            return;
        }
        
        var modal = document.getElementById('mp-share-modal');
        if (!modal) return;
        document.getElementById('mp-share-title').textContent = title;
        document.getElementById('mp-share-url-input').value = url;
        document.getElementById('mp-share-wa').href = 'https://api.whatsapp.com/send?text=' + encodeURIComponent(title + ' ' + url);
        document.getElementById('mp-share-fb').href = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
        document.getElementById('mp-share-tw').href = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(title) + '&url=' + encodeURIComponent(url);
        modal.classList.remove('hidden');
    }

    function closeShareModal() {
        var modal = document.getElementById('mp-share-modal');
        if (modal) modal.classList.add('hidden');
    }

    function copyShareLink() {
        var input = document.getElementById('mp-share-url-input');
        if (!input) return;
        input.select();
        navigator.clipboard.writeText(input.value).then(function() {
            var btnText = document.getElementById('mp-copy-btn-text');
            if (btnText) {
                btnText.textContent = 'Tersalin!';
                setTimeout(function() { btnText.textContent = 'Salin'; }, 2000);
            }
        }).catch(function() {
            document.execCommand('copy');
        });
    }
</script>
@endpush
@endsection
