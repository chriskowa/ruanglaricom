@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Marketplace Perlengkapan Lari & Titip Jual Running Gear | RuangLari')
@section('meta_title', 'Marketplace Perlengkapan Lari & Titip Jual Running Gear | RuangLari Market')
@section('meta_description', 'Beli dan jual perlengkapan lari original, sepatu lari second/baru, smartwatch, jersey, slot event, dan titip jual running gear terpercaya di RuangLari Market.')
@section('meta_keywords', 'marketplace lari, sepatu lari bekas, running gear indonesia, titip jual sepatu lari, Garmin second, Nike Vaporfly, Adidas Adizero, slot marathon')
@section('og_type', 'website')
@section('og_title', 'RuangLari Market - Marketplace Perlengkapan Lari Indonesia')
@section('og_description', 'Temukan sepatu lari original, jam GPS, jersey, dan aksesori lari berkualitas dari komunitas pelari Indonesia.')
@section('canonical_url', route('marketplace.index'))

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "WebSite",
  "name": "RuangLari Market",
  "url": "{{ route('marketplace.index') }}",
  "potentialAction": {
    "@@type": "SearchAction",
    "target": "{{ route('marketplace.index') }}?search={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>
@endpush

@section('content')
<div x-data="{ sidebarOpen: window.innerWidth >= 1024, activeCategory: '{{ request('category') }}' }" class="min-h-screen pt-24 pb-20 px-4 md:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-neon selection:text-dark">
    
    <!-- Hero / Editorial Header (Nike & Adidas Running Style) -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 border-b border-slate-800/80 pb-6">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-slate-850 border border-slate-750 text-[10px] uppercase tracking-wider text-neon mb-3 font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-neon"></span>
                    Verified Running Gear & Marketplace
                </div>
                <h1 class="text-3xl md:text-5xl font-black text-white uppercase tracking-tight font-sans">
                    RUNNING <span class="text-neon">MARKET</span>
                </h1>
                <p class="text-slate-400 text-xs md:text-sm mt-1.5 max-w-2xl leading-relaxed">
                    Beli dan jual sepatu lari original, jam GPS, apparel, slot race, dan titip jual running gear terpercaya dari komunitas pelari.
                </p>
            </div>
            
            <div class="flex items-center gap-3 shrink-0 w-full md:w-auto">
                <a href="{{ route('marketplace.cart.index') }}" class="relative px-4 py-2.5 rounded-md bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white text-xs font-bold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Keranjang</span>
                    <span id="market-cart-badge" class="min-w-[18px] h-[18px] px-1 bg-neon text-dark text-[10px] font-black rounded flex items-center justify-center hidden font-mono">0</span>
                </a>

                <a href="{{ auth()->check() ? route('marketplace.seller.products.create') : route('login', ['redirect' => route('marketplace.seller.products.create')]) }}" class="flex-1 md:flex-initial px-5 py-2.5 rounded-md bg-white hover:bg-slate-200 text-slate-950 font-black text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1.5 shadow-sm">
                    <span>+ Jual Gear</span>
                </a>
            </div>
        </div>

        <!-- Horizontal Quick Category Chips (Nike Category Bar) -->
        <div class="flex items-center gap-2 overflow-x-auto pt-4 pb-1 no-scrollbar text-xs font-bold">
            <button type="button" @click="selectCategoryChip('')" 
                    :class="!activeCategory ? 'bg-white text-dark shadow-sm' : 'bg-slate-900/80 text-slate-400 border border-slate-800 hover:text-white hover:border-slate-700'"
                    class="px-4 py-2 rounded-md whitespace-nowrap uppercase tracking-wider text-[11px] transition-all">
                Semua Kategori
            </button>
            @foreach($categories as $cat)
            <button type="button" @click="selectCategoryChip('{{ $cat->slug }}')" 
                    :class="activeCategory === '{{ $cat->slug }}' ? 'bg-white text-dark shadow-sm' : 'bg-slate-900/80 text-slate-400 border border-slate-800 hover:text-white hover:border-slate-700'"
                    class="px-4 py-2 rounded-md whitespace-nowrap uppercase tracking-wider text-[11px] transition-all flex items-center gap-1.5">
                <span>{{ $cat->name }}</span>
                @if($cat->products_count > 0)
                    <span class="text-[9px] opacity-60 font-bold">({{ $cat->products_count }})</span>
                @endif
            </button>
            @endforeach
        </div>

        <!-- Featured Gear Highlight Strip (if any active featured products) -->
        @if(isset($featuredProducts) && $featuredProducts->count() > 0)
        <div class="mt-8 pt-6 border-t border-slate-800/60">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-neon"></span>
                    <h2 class="text-xs font-black uppercase tracking-wider text-white">FEATURED GEAR</h2>
                </div>
                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">Pilihan Unggulan Komunitas</span>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
                @foreach($featuredProducts as $fProd)
                <a href="{{ route('marketplace.show', $fProd->slug) }}" class="group block p-2.5 rounded-lg bg-slate-900/60 border border-slate-800/80 hover:border-slate-600 transition shadow-sm">
                    <div class="aspect-square rounded-md overflow-hidden bg-[#131b2c] mb-2.5 relative">
                        <img src="{{ $fProd->primaryImage ? asset('storage/'.$fProd->primaryImage->image_path) : ($fProd->images->first() ? asset('storage/'.$fProd->images->first()->image_path) : '') }}" 
                             alt="{{ $fProd->title }}" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-neon text-dark text-[8px] font-black uppercase tracking-wider shadow">
                            FEATURED
                        </span>
                    </div>
                    <div class="space-y-0.5">
                        <p class="text-[9px] text-slate-500 uppercase truncate font-semibold">{{ optional($fProd->brand)->name ?? 'GEAR' }}</p>
                        <h3 class="text-xs font-bold text-white truncate group-hover:text-neon transition">{{ $fProd->title }}</h3>
                        <p class="text-xs font-black text-white font-mono">Rp {{ number_format($fProd->price, 0, ',', '.') }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Minimalist Sticky Filter Bar -->
    <div class="max-w-7xl mx-auto sticky top-20 z-30 bg-[#090D16]/90 backdrop-blur-xl border-y border-slate-800/80 py-3 mb-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            
            <!-- Left: Filter Toggle & Live Search -->
            <div class="flex items-center gap-2.5 w-full sm:w-auto flex-grow max-w-xl">
                <!-- Filter Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="flex items-center gap-2 px-4 py-2 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-200 rounded-md transition-all shrink-0 text-xs font-bold"
                        :class="sidebarOpen ? 'border-neon/40 text-neon' : ''">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Filter</span>
                </button>
                
                <!-- Search Input -->
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" id="search-top" placeholder="Cari Vaporfly, Adizero, Garmin, Jersey..." 
                           class="w-full bg-slate-900/90 border border-slate-800 text-white rounded-md pl-10 pr-8 py-2 text-xs focus:border-white focus:outline-none transition-all placeholder-slate-500">
                    <button type="button" id="clear-search-btn" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-500 hover:text-white hidden">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
            
            <!-- Right: Sort Dropdown & Item Counter -->
            <div class="flex items-center gap-3 w-full sm:w-auto shrink-0 justify-between sm:justify-end">
                <div class="text-xs text-slate-400 font-medium" id="results-count-label">
                    {{ $products->total() }} Produk
                </div>
                
                <div class="flex items-center gap-1.5">
                    <span class="text-xs text-slate-500 uppercase tracking-wider hidden sm:inline font-semibold">Urutkan:</span>
                    <select id="sort-select-top" class="bg-slate-900 border border-slate-800 text-slate-200 rounded-md px-3 py-2 text-xs focus:border-white focus:outline-none transition-all font-medium">
                        <option value="latest">Terbaru</option>
                        <option value="price_asc">Harga: Terendah</option>
                        <option value="price_desc">Harga: Tertinggi</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8 items-start relative w-full">
        
        <!-- Mobile Sidebar Overlay Backdrop -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-40 lg:hidden" x-transition:opacity></div>

        <!-- Sidebar Filter Panel (Clean Nike.com Style) -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 -translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-4"
             class="fixed inset-y-0 left-0 z-50 w-80 bg-[#0c121e] p-0 overflow-y-auto lg:static lg:w-72 lg:z-20 lg:h-[calc(100vh-8rem)] lg:sticky lg:top-36 lg:overflow-y-auto lg:bg-slate-900/40 lg:backdrop-blur-md lg:border lg:border-slate-800 lg:rounded-lg custom-scrollbar shrink-0 shadow-2xl lg:shadow-none">
            
            <form id="filter-form" action="{{ route('marketplace.index') }}" method="GET" class="h-full">
                <!-- Sticky Header inside Sidebar -->
                <div class="sticky top-0 bg-[#0c121e]/95 lg:bg-slate-900/90 backdrop-blur-md z-10 py-3.5 px-5 border-b border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-2">
                        <span>Filter Produk</span>
                    </span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('marketplace.index') }}" class="text-[11px] text-neon hover:underline font-bold transition-all">Reset All</a>
                        <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1 text-slate-400 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-6">
                    <!-- Hidden inputs for Syncing Top Controls -->
                    <input type="hidden" name="search" id="hidden-search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" id="hidden-sort" value="{{ request('sort') }}">
                    <input type="hidden" name="category" id="hidden-category" value="{{ request('category') }}">
                    
                    <!-- Fulfillment Mode Filter (Titip Jual / Kirim Sendiri) -->
                    <div class="space-y-2.5">
                        <label class="text-[11px] text-white font-bold uppercase tracking-wider block">Tipe Layanan</label>
                        <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-950 rounded-md border border-slate-800">
                            <label class="text-center cursor-pointer">
                                <input type="radio" name="fulfillment_mode" value="" class="hidden peer" {{ !request('fulfillment_mode') ? 'checked' : '' }}>
                                <div class="py-1.5 rounded text-[10px] text-slate-400 peer-checked:bg-white peer-checked:text-dark font-bold hover:text-white transition-all">Semua</div>
                            </label>
                            <label class="text-center cursor-pointer">
                                <input type="radio" name="fulfillment_mode" value="consignment" class="hidden peer" {{ request('fulfillment_mode') == 'consignment' ? 'checked' : '' }}>
                                <div class="py-1.5 rounded text-[10px] text-slate-400 peer-checked:bg-white peer-checked:text-dark font-bold hover:text-white transition-all">Titip Jual</div>
                            </label>
                            <label class="text-center cursor-pointer">
                                <input type="radio" name="fulfillment_mode" value="self_ship" class="hidden peer" {{ request('fulfillment_mode') == 'self_ship' ? 'checked' : '' }}>
                                <div class="py-1.5 rounded text-[10px] text-slate-400 peer-checked:bg-white peer-checked:text-dark font-bold hover:text-white transition-all">Direct</div>
                            </label>
                        </div>
                    </div>

                    <!-- Condition Filter (Segmented Control) -->
                    <div class="space-y-2.5">
                        <label class="text-[11px] text-white font-bold uppercase tracking-wider block">Kondisi</label>
                        <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-950 rounded-md border border-slate-800">
                            <label class="text-center cursor-pointer">
                                <input type="radio" name="condition" value="" class="hidden peer" {{ !request('condition') ? 'checked' : '' }}>
                                <div class="py-1.5 rounded text-[11px] text-slate-400 peer-checked:bg-white peer-checked:text-dark font-bold hover:text-white transition-all">Semua</div>
                            </label>
                            <label class="text-center cursor-pointer">
                                <input type="radio" name="condition" value="new" class="hidden peer" {{ request('condition') == 'new' ? 'checked' : '' }}>
                                <div class="py-1.5 rounded text-[11px] text-slate-400 peer-checked:bg-white peer-checked:text-dark font-bold hover:text-white transition-all">Baru</div>
                            </label>
                            <label class="text-center cursor-pointer">
                                <input type="radio" name="condition" value="used" class="hidden peer" {{ request('condition') == 'used' ? 'checked' : '' }}>
                                <div class="py-1.5 rounded text-[11px] text-slate-400 peer-checked:bg-white peer-checked:text-dark font-bold hover:text-white transition-all">Bekas</div>
                            </label>
                        </div>
                    </div>

                    <!-- Price Range (Clean Athletic Minimalist) -->
                    <div class="space-y-2.5">
                        <label class="text-[11px] text-white font-bold uppercase tracking-wider block">Rentang Harga (Rp)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}" 
                                       class="w-full bg-slate-950 border border-slate-800 text-white rounded-md px-3 py-2 text-xs focus:border-white focus:outline-none placeholder-slate-600 transition-all font-semibold font-mono">
                            </div>
                            <div class="relative">
                                <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}" 
                                       class="w-full bg-slate-950 border border-slate-800 text-white rounded-md px-3 py-2 text-xs focus:border-white focus:outline-none placeholder-slate-600 transition-all font-semibold font-mono">
                            </div>
                        </div>
                    </div>

                    <!-- Brand Filter (Dropdown / Select) -->
                    <div class="space-y-2">
                        <label class="text-[11px] text-white font-bold uppercase tracking-wider block">Brand</label>
                        <select name="brand" id="brand-select" class="w-full bg-slate-950 border border-slate-800 text-slate-200 rounded-md px-3 py-2.5 text-xs focus:border-white focus:outline-none transition-colors">
                            <option value="">Semua Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Size Filter -->
                    <div class="space-y-2">
                        <label class="text-[11px] text-white font-bold uppercase tracking-wider block">Ukuran / Size</label>
                        <input type="text" name="size" value="{{ request('size') }}" placeholder="Contoh: 42, 43, M, L..." 
                               class="w-full bg-slate-950 border border-slate-800 text-white rounded-md px-3 py-2.5 text-xs focus:border-white focus:outline-none placeholder-slate-600 transition-all">
                    </div>

                    <!-- Location / City -->
                    <div class="space-y-2">
                        <label class="text-[11px] text-white font-bold uppercase tracking-wider block">Lokasi Seller</label>
                        <select name="city" class="w-full bg-slate-950 border border-slate-800 text-slate-200 rounded-md px-3 py-2.5 text-xs focus:border-white focus:outline-none transition-colors">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Category List (Detailed in sidebar) -->
                    <div class="space-y-2.5 pt-2 border-t border-slate-800">
                        <label class="text-[11px] text-white font-bold uppercase tracking-wider block">Pilih Kategori</label>
                        <div class="space-y-1">
                            <button type="button" @click="selectCategoryChip('')" 
                                    class="w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-colors flex items-center justify-between"
                                    :class="!activeCategory ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-900/50'">
                                <span>Semua Kategori</span>
                            </button>
                            @foreach($categories as $cat)
                                <button type="button" @click="selectCategoryChip('{{ $cat->slug }}')" 
                                        class="w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-colors flex items-center justify-between"
                                        :class="activeCategory === '{{ $cat->slug }}' ? 'bg-slate-800 text-white font-bold' : 'text-slate-400 hover:text-white hover:bg-slate-900/50'">
                                    <span>{{ $cat->name }}</span>
                                    @if($cat->products_count > 0)
                                        <span class="text-[10px] font-semibold text-slate-500 font-mono">({{ $cat->products_count }})</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="pt-3 pb-6 flex gap-2">
                        <button type="submit" class="flex-1 bg-white hover:bg-slate-200 text-slate-950 font-black py-2.5 rounded-md transition-colors text-xs uppercase tracking-wider">Terapkan</button>
                        <a href="{{ route('marketplace.index') }}" class="px-4 py-2.5 bg-slate-900 text-slate-400 hover:text-white font-bold rounded-md border border-slate-800 hover:border-slate-700 transition-colors text-xs flex items-center justify-center">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Product Grid Column -->
        <div class="flex-grow w-full">
            <div id="product-grid-container" class="relative min-h-[400px]">
                @include('marketplace.partials.product-grid')
            </div>
        </div>
        
    </div>
</div>

@include('marketplace.partials.share-modal')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const hiddenSearch = document.getElementById('hidden-search');
    const hiddenSort = document.getElementById('hidden-sort');
    const hiddenCategory = document.getElementById('hidden-category');
    
    const searchTop = document.getElementById('search-top');
    const clearSearchBtn = document.getElementById('clear-search-btn');
    const sortSelectTop = document.getElementById('sort-select-top');
    const resultsCountLabel = document.getElementById('results-count-label');
    
    const gridContainer = document.getElementById('product-grid-container');
    const brandSelect = document.getElementById('brand-select');
    let searchTimeout;

    // Sync Top Controls to Form State initially
    searchTop.value = hiddenSearch.value;
    sortSelectTop.value = hiddenSort.value || 'latest';
    if (searchTop.value) {
        clearSearchBtn.classList.remove('hidden');
    }

    // Category Chip Selector (Global function for Alpine)
    window.selectCategoryChip = function(slug) {
        hiddenCategory.value = slug;
        const alpineEl = document.querySelector('[x-data]');
        if (alpineEl && alpineEl._x_dataStack) {
            alpineEl._x_dataStack[0].activeCategory = slug;
        }
        filterBrands();
        fetchProducts();
    };

    // Clear Search Input
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchTop.value = '';
            hiddenSearch.value = '';
            clearSearchBtn.classList.add('hidden');
            fetchProducts();
        });
    }

    // Update Cart Badge Count
    function updateMarketCartBadge() {
        fetch('{{ route("marketplace.cart.count") }}', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(data => {
            const badge = document.getElementById('market-cart-badge');
            if (badge) {
                badge.innerText = data.count || 0;
                if (data.count > 0) {
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        })
        .catch(console.error);
    }
    updateMarketCartBadge();

    // Brand Filtering Logic based on Selected Category
    function filterBrands() {
        const selectedCategory = hiddenCategory.value || '';
        const options = brandSelect.querySelectorAll('option');

        options.forEach(option => {
            if (option.value === "") return;
            const categories = JSON.parse(option.dataset.categories || '[]');
            
            if (!selectedCategory || categories.includes(selectedCategory)) {
                option.hidden = false;
                option.disabled = false;
            } else {
                option.hidden = true;
                option.disabled = true;
                if (option.selected) {
                    brandSelect.value = "";
                }
            }
        });
    }
    filterBrands();

    // Function to fetch products via AJAX
    function fetchProducts() {
        hiddenSearch.value = searchTop.value;
        hiddenSort.value = sortSelectTop.value;

        if (searchTop.value) {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);

        // Show subtle loading state
        gridContainer.style.opacity = '0.35';

        fetch(`{{ route('marketplace.index') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            gridContainer.innerHTML = html;
            gridContainer.style.opacity = '1';
            
            // Update URL without reloading
            window.history.pushState({}, '', `?${params.toString()}`);

            // Update item count from response if available
            const countEl = gridContainer.querySelector('[data-products-total]');
            if (countEl && resultsCountLabel) {
                resultsCountLabel.innerText = countEl.getAttribute('data-products-total') + ' Produk';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            gridContainer.style.opacity = '1';
        });
    }

    // Debounced text input event listeners
    searchTop.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchProducts, 350);
    });

    const textInputs = filterForm.querySelectorAll('input[type="number"], input[name="size"]');
    textInputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchProducts, 450);
        });
    });

    // Changes on Radio, Select elements
    const changeInputs = filterForm.querySelectorAll('input[type="radio"], select');
    changeInputs.forEach(input => {
        input.addEventListener('change', fetchProducts);
    });

    sortSelectTop.addEventListener('change', fetchProducts);

    // Form submit
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchProducts();
    });

    // Handle Pagination Clicks via AJAX
    gridContainer.addEventListener('click', (e) => {
        const paginationLink = e.target.closest('.pagination-container a');
        if (paginationLink) {
            e.preventDefault();
            const url = paginationLink.getAttribute('href');
            
            gridContainer.style.opacity = '0.35';
            
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                gridContainer.innerHTML = html;
                gridContainer.style.opacity = '1';
                window.history.pushState({}, '', url);
                window.scrollTo({ top: 180, behavior: 'smooth' });
            });
        }
    });
});
</script>
@endpush
