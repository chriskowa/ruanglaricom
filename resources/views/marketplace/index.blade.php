@php
    $withSidebar = true;
    $savedTheme = request('theme', request()->cookie('marketplace_theme', session('marketplace_theme', 'dark')));
    if (request()->has('theme')) {
        session(['marketplace_theme' => request('theme')]);
    }
    $lightMode = ($savedTheme === 'light');
@endphp
@extends('layouts.pacerhub', ['lightMode' => (request('theme', request()->cookie('marketplace_theme', session('marketplace_theme', 'dark'))) === 'light')])

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
@push('styles')
<style>
/* Segmented Radio Toggle (Tipe Layanan & Kondisi) - High Contrast Active/Inactive */
.segmented-toggle input[type="radio"]:checked + div {
    background-color: #ffffff !important;
    color: #020617 !important;
    font-weight: 900 !important;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.25) !important;
}
.segmented-toggle input[type="radio"]:not(:checked) + div {
    background-color: transparent !important;
    color: #94a3b8 !important;
    font-weight: 700 !important;
}
.segmented-toggle input[type="radio"]:not(:checked) + div:hover {
    color: #ffffff !important;
}

/* Solid Opaque Filter Sidebar Drawer (Zero Transparency on Mobile & Desktop) */
#mobile-sidebar-filter {
    background-color: #0c121e !important;
    opacity: 1 !important;
}
#mobile-sidebar-filter .sticky {
    background-color: #0c121e !important;
    opacity: 1 !important;
}

/* =========================================================
   LIGHT MODE STYLES FOR /MARKETPLACE
========================================================= */
html.theme-light,
body.theme-light,
.theme-light #marketplace-container {
    background-color: #f8fafc !important;
    color: #0f172a !important;
}

.theme-light #marketplace-container h1 {
    color: #0f172a !important;
}
.theme-light #marketplace-container h1 span.text-neon {
    color: #0284c7 !important;
}
.theme-light #marketplace-container h2 {
    color: #0f172a !important;
}
.theme-light #marketplace-container p {
    color: #475569 !important;
}

.theme-light .editorial-header-border {
    border-color: #e2e8f0 !important;
}

/* Category Chips in Light Mode */
.theme-light .cat-chip-btn {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    color: #64748b !important;
}
.theme-light .cat-chip-btn:hover {
    color: #0f172a !important;
    border-color: #cbd5e1 !important;
}

/* Featured Strip */
.theme-light .featured-strip-wrap {
    border-color: #e2e8f0 !important;
}
.theme-light .featured-strip-wrap a {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
}
.theme-light .featured-strip-wrap a:hover {
    border-color: #cbd5e1 !important;
}
.theme-light .featured-strip-wrap h3 {
    color: #0f172a !important;
}
.theme-light .featured-strip-wrap p {
    color: #0f172a !important;
}

/* Sticky Filter Bar */
.theme-light .sticky-filter-bar {
    background-color: rgba(248, 250, 252, 0.95) !important;
    border-color: #e2e8f0 !important;
}
.theme-light .filter-toggle-btn {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light .filter-toggle-btn:hover {
    background-color: #f1f5f9 !important;
}
.theme-light #search-top {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light #search-top::placeholder {
    color: #94a3b8 !important;
}
.theme-light #sort-select-top {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light #results-count-label {
    color: #64748b !important;
}

/* Sidebar Filter Panel (Mobile & Desktop) */
.theme-light #mobile-sidebar-filter {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    color: #0f172a !important;
}
.theme-light #mobile-sidebar-filter .sticky {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
}
.theme-light #mobile-sidebar-filter label {
    color: #0f172a !important;
}
.theme-light #mobile-sidebar-filter input[type="text"],
.theme-light #mobile-sidebar-filter input[type="number"],
.theme-light #mobile-sidebar-filter select {
    background-color: #f8fafc !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light #mobile-sidebar-filter input[type="text"]:focus,
.theme-light #mobile-sidebar-filter input[type="number"]:focus,
.theme-light #mobile-sidebar-filter select:focus {
    border-color: #0f172a !important;
    background-color: #ffffff !important;
}
.theme-light .segmented-toggle {
    background-color: #f1f5f9 !important;
    border-color: #e2e8f0 !important;
}

/* Product Cards in Light Mode */
.theme-light .product-card-item {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}
.theme-light .product-card-item:hover {
    border-color: #cbd5e1 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}
.theme-light .product-card-item .product-card-img {
    background-color: #f8fafc !important;
}
.theme-light .product-card-item .product-card-meta {
    color: #64748b !important;
}
.theme-light .product-card-item .product-card-title,
.theme-light .product-card-item .product-card-title a {
    color: #0f172a !important;
}
.theme-light .product-card-item .product-card-price {
    color: #020617 !important;
}
.theme-light .product-card-item .product-card-seller {
    border-color: #f1f5f9 !important;
    color: #64748b !important;
}
.theme-light .product-card-item .product-card-seller a {
    color: #475569 !important;
}
.theme-light .product-card-item .product-card-seller a:hover {
    color: #0f172a !important;
}
.theme-light .product-card-item .product-card-seller span {
    color: #64748b !important;
}
.theme-light .product-card-item button[type="submit"],
.theme-light .product-card-item a[href*="checkout"] {
    background-color: #0f172a !important;
    color: #ffffff !important;
}
.theme-light .product-card-item button[type="submit"]:hover,
.theme-light .product-card-item a[href*="checkout"]:hover {
    background-color: #1e293b !important;
}
</style>
@endpush

@section('content')
<script>
    (function() {
        var savedTheme = localStorage.getItem('marketplace_theme') || (document.cookie.match(/marketplace_theme=([^;]+)/) || [])[1];
        if (savedTheme === 'light' || window.location.search.indexOf('theme=light') !== -1) {
            document.documentElement.classList.add('theme-light');
            if (document.body) document.body.classList.add('theme-light');
        } else if (savedTheme === 'dark' || window.location.search.indexOf('theme=dark') !== -1) {
            document.documentElement.classList.remove('theme-light');
            if (document.body) document.body.classList.remove('theme-light');
        }
    })();
</script>
<div 
    id="marketplace-container"
    x-data="{
        sidebarOpen: window.innerWidth >= 1024
    }"
    class="min-h-screen mt-10 pt-2 md:pt-4 pb-20 px-4 md:px-8 {{ $lightMode ? 'bg-[#f8fafc] text-slate-900' : 'bg-[#090D16] text-slate-200' }} font-sans selection:bg-neon selection:text-slate-950 transition-colors duration-200">
    
    <!-- Hero / Editorial Header (Nike & Adidas Running Style) -->
    <div class="max-w-7xl mx-auto mb-8">
        <div class="editorial-header-border flex flex-col md:flex-row justify-between items-start md:items-end gap-6 border-b border-slate-800/80 pb-6">
            <div>
                
                <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tight font-sans {{ $lightMode ? 'text-slate-950' : 'text-white' }}">
                    RUNNING <span class="{{ $lightMode ? 'text-sky-600' : 'text-neon' }}">MARKET</span>
                </h1>
                <p class="{{ $lightMode ? 'text-slate-600' : 'text-slate-400' }} text-xs md:text-sm mt-1.5 max-w-2xl leading-relaxed">
                    Beli dan jual sepatu lari original, jam GPS, apparel, slot race, dan titip jual running gear terpercaya dari komunitas pelari.
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 shrink-0 w-full md:w-auto">
                <!-- Theme Toggle Navigation Button -->
                <button type="button" onclick="toggleMarketplaceTheme()" id="market-theme-toggle-btn" 
                        class="px-3.5 py-2.5 rounded-md border text-xs font-bold transition-all flex items-center gap-2 shadow-sm {{ $lightMode ? 'bg-white border-slate-300 hover:border-slate-400 text-slate-800' : 'bg-slate-900 border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white' }}"
                        title="Ubah Tema (Light / Dark Mode)">
                    <svg id="theme-icon-sun" class="w-4 h-4 text-amber-500 {{ $lightMode ? 'hidden' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg id="theme-icon-moon" class="w-4 h-4 text-slate-700 {{ $lightMode ? '' : 'hidden' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span id="market-theme-text">{{ $lightMode ? 'Mode Gelap' : 'Mode Terang' }}</span>
                </button>

                <a href="{{ route('marketplace.cart.index') }}" class="relative px-4 py-2.5 rounded-md {{ $lightMode ? 'bg-white border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900' : 'bg-slate-900 border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white' }} border text-xs font-bold transition-all flex items-center gap-2 shadow-sm">
                    <svg class="w-4 h-4 {{ $lightMode ? 'text-slate-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Keranjang</span>
                    <span id="market-cart-badge" class="min-w-[18px] h-[18px] px-1 bg-neon text-slate-950 text-[10px] font-black rounded flex items-center justify-center hidden font-mono">0</span>
                </a>

                <a href="{{ auth()->check() ? route('marketplace.seller.products.create') : route('login', ['redirect' => route('marketplace.seller.products.create')]) }}" class="flex-1 md:flex-initial px-5 py-2.5 rounded-md {{ $lightMode ? 'bg-slate-950 hover:bg-slate-800 text-white' : 'bg-white hover:bg-slate-200 text-slate-950' }} font-black text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1.5 shadow-sm">
                    <span>+ Jual Gear</span>
                </a>
            </div>
        </div>

        <!-- Horizontal Quick Category Chips (Nike Category Bar) -->
        <div class="flex items-center gap-2 overflow-x-auto pt-4 pb-1 no-scrollbar text-xs font-bold">
            @php $currentCat = request('category', ''); @endphp
            <button type="button" onclick="selectCategoryChip('')" data-category=""
                    class="cat-chip-btn px-4 py-2 rounded-md whitespace-nowrap uppercase tracking-wider text-[11px] transition-all flex items-center gap-1.5 {{ empty($currentCat) ? 'bg-white text-slate-950 font-black shadow-sm ring-1 ring-white' : 'bg-slate-900/80 text-slate-400 border border-slate-800 hover:text-white hover:border-slate-700' }}">
                Semua Kategori
            </button>
            @foreach($categories as $cat)
            <button type="button" onclick="selectCategoryChip('{{ $cat->slug }}')" data-category="{{ $cat->slug }}"
                    class="cat-chip-btn px-4 py-2 rounded-md whitespace-nowrap uppercase tracking-wider text-[11px] transition-all flex items-center gap-1.5 {{ $currentCat == $cat->slug ? 'bg-white text-slate-950 font-black shadow-sm ring-1 ring-white' : 'bg-slate-900/80 text-slate-400 border border-slate-800 hover:text-white hover:border-slate-700' }}">
                <span>{{ $cat->name }}</span>
                @if($cat->products_count > 0)
                    <span class="cat-count text-[9px] {{ $currentCat == $cat->slug ? 'text-slate-800 font-black' : 'text-slate-500 font-bold' }}">({{ $cat->products_count }})</span>
                @endif
            </button>
            @endforeach
        </div>

        <!-- Featured Gear Highlight Strip (if any active featured products) -->
        @if(isset($featuredProducts) && $featuredProducts->count() > 0)
        <div class="featured-strip-wrap mt-8 pt-6 border-t border-slate-800/60">
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
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded bg-neon text-slate-950 text-[8px] font-black uppercase tracking-wider shadow">
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
    <div class="sticky-filter-bar max-w-7xl mx-auto sticky top-20 z-30 bg-[#090D16]/90 backdrop-blur-xl border-y border-slate-800/80 py-3 mb-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            
            <!-- Left: Filter Toggle & Live Search -->
            <div class="flex items-center gap-2.5 w-full sm:w-auto flex-grow max-w-xl">
                <!-- Filter Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="filter-toggle-btn flex items-center gap-2 px-4 py-2 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-200 rounded-md transition-all shrink-0 text-xs font-bold"
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
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 lg:hidden" style="background-color: rgba(0, 0, 0, 0.8) !important;" x-transition:opacity></div>

        <!-- Sidebar Filter Panel (Clean Nike.com Style - 100% Opaque) -->
        <div x-show="sidebarOpen"
             id="mobile-sidebar-filter"
             style="background-color: #0c121e !important;"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 -translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-4"
             class="fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] p-0 overflow-y-auto lg:static lg:w-72 lg:z-20 lg:h-[calc(100vh-8rem)] lg:sticky lg:top-36 lg:overflow-y-auto border-r border-slate-800 lg:border lg:border-slate-800 lg:rounded-lg custom-scrollbar shrink-0 shadow-2xl lg:shadow-none">
            
            <form id="filter-form" action="{{ route('marketplace.index') }}" method="GET" class="h-full">
                <!-- Sticky Header inside Sidebar -->
                <div class="sticky top-0 z-10 py-3.5 px-5 border-b border-slate-800 flex items-center justify-between"
                     style="background-color: #0c121e !important;">
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
                        <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-950 rounded-md border border-slate-800 segmented-toggle">
                            <label class="text-center cursor-pointer select-none">
                                <input type="radio" name="fulfillment_mode" value="" class="sr-only peer" {{ !request('fulfillment_mode') ? 'checked' : '' }}>
                                <div class="py-2 px-1 rounded-md text-xs font-bold text-slate-400 transition-all peer-checked:bg-white peer-checked:text-slate-950 peer-checked:font-black peer-checked:shadow-sm hover:text-white flex items-center justify-center">
                                    Semua
                                </div>
                            </label>
                            <label class="text-center cursor-pointer select-none">
                                <input type="radio" name="fulfillment_mode" value="consignment" class="sr-only peer" {{ request('fulfillment_mode') == 'consignment' ? 'checked' : '' }}>
                                <div class="py-2 px-1 rounded-md text-xs font-bold text-slate-400 transition-all peer-checked:bg-white peer-checked:text-slate-950 peer-checked:font-black peer-checked:shadow-sm hover:text-white flex items-center justify-center">
                                    Titip Jual
                                </div>
                            </label>
                            <label class="text-center cursor-pointer select-none">
                                <input type="radio" name="fulfillment_mode" value="self_ship" class="sr-only peer" {{ request('fulfillment_mode') == 'self_ship' ? 'checked' : '' }}>
                                <div class="py-2 px-1 rounded-md text-xs font-bold text-slate-400 transition-all peer-checked:bg-white peer-checked:text-slate-950 peer-checked:font-black peer-checked:shadow-sm hover:text-white flex items-center justify-center">
                                    Direct
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Condition Filter (Segmented Control) -->
                    <div class="space-y-2.5">
                        <label class="text-[11px] text-white font-bold uppercase tracking-wider block">Kondisi</label>
                        <div class="grid grid-cols-3 gap-1.5 p-1 bg-slate-950 rounded-md border border-slate-800 segmented-toggle">
                            <label class="text-center cursor-pointer select-none">
                                <input type="radio" name="condition" value="" class="sr-only peer" {{ !request('condition') ? 'checked' : '' }}>
                                <div class="py-2 px-1 rounded-md text-xs font-bold text-slate-400 transition-all peer-checked:bg-white peer-checked:text-slate-950 peer-checked:font-black peer-checked:shadow-sm hover:text-white flex items-center justify-center">
                                    Semua
                                </div>
                            </label>
                            <label class="text-center cursor-pointer select-none">
                                <input type="radio" name="condition" value="new" class="sr-only peer" {{ request('condition') == 'new' ? 'checked' : '' }}>
                                <div class="py-2 px-1 rounded-md text-xs font-bold text-slate-400 transition-all peer-checked:bg-white peer-checked:text-slate-950 peer-checked:font-black peer-checked:shadow-sm hover:text-white flex items-center justify-center">
                                    Baru
                                </div>
                            </label>
                            <label class="text-center cursor-pointer select-none">
                                <input type="radio" name="condition" value="used" class="sr-only peer" {{ request('condition') == 'used' ? 'checked' : '' }}>
                                <div class="py-2 px-1 rounded-md text-xs font-bold text-slate-400 transition-all peer-checked:bg-white peer-checked:text-slate-950 peer-checked:font-black peer-checked:shadow-sm hover:text-white flex items-center justify-center">
                                    Bekas
                                </div>
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
                            <button type="button" onclick="selectCategoryChip('')" data-category="" 
                                    class="cat-sidebar-btn w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-colors flex items-center justify-between {{ empty($currentCat) ? 'bg-white text-slate-950 font-black shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/50' }}">
                                <span>Semua Kategori</span>
                            </button>
                            @foreach($categories as $cat)
                                <button type="button" onclick="selectCategoryChip('{{ $cat->slug }}')" data-category="{{ $cat->slug }}" 
                                        class="cat-sidebar-btn w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-colors flex items-center justify-between {{ $currentCat == $cat->slug ? 'bg-white text-slate-950 font-black shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-900/50' }}">
                                    <span>{{ $cat->name }}</span>
                                    @if($cat->products_count > 0)
                                        <span class="cat-sidebar-count text-[10px] font-mono {{ $currentCat == $cat->slug ? 'text-slate-800 font-black' : 'text-slate-500 font-semibold' }}">({{ $cat->products_count }})</span>
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

    // Direct CSS Active/Inactive UI Switcher (Pure & Instant, supports Light/Dark mode)
    function setActiveCategoryUI(slug) {
        slug = slug || '';
        const isLight = document.documentElement.classList.contains('theme-light') || document.body.classList.contains('theme-light');

        // 1. Sync Top Category Chips
        document.querySelectorAll('.cat-chip-btn').forEach(btn => {
            const isMatch = (btn.dataset.category || '') === slug;
            if (isMatch) {
                btn.className = isLight
                    ? 'cat-chip-btn px-4 py-2 rounded-md whitespace-nowrap uppercase tracking-wider text-[11px] transition-all flex items-center gap-1.5 bg-slate-950 text-white font-black shadow-sm ring-1 ring-slate-950'
                    : 'cat-chip-btn px-4 py-2 rounded-md whitespace-nowrap uppercase tracking-wider text-[11px] transition-all flex items-center gap-1.5 bg-white text-slate-950 font-black shadow-sm ring-1 ring-white';
                const badge = btn.querySelector('.cat-count');
                if (badge) {
                    badge.className = isLight ? 'cat-count text-[9px] text-slate-300 font-bold' : 'cat-count text-[9px] text-slate-800 font-black';
                }
            } else {
                btn.className = isLight
                    ? 'cat-chip-btn px-4 py-2 rounded-md whitespace-nowrap uppercase tracking-wider text-[11px] transition-all flex items-center gap-1.5 bg-white text-slate-600 border border-slate-300 hover:text-slate-950 hover:border-slate-400'
                    : 'cat-chip-btn px-4 py-2 rounded-md whitespace-nowrap uppercase tracking-wider text-[11px] transition-all flex items-center gap-1.5 bg-slate-900/80 text-slate-400 border border-slate-800 hover:text-white hover:border-slate-700';
                const badge = btn.querySelector('.cat-count');
                if (badge) {
                    badge.className = isLight ? 'cat-count text-[9px] text-slate-400 font-semibold' : 'cat-count text-[9px] text-slate-500 font-bold';
                }
            }
        });

        // 2. Sync Sidebar Category List
        document.querySelectorAll('.cat-sidebar-btn').forEach(btn => {
            const isMatch = (btn.dataset.category || '') === slug;
            if (isMatch) {
                btn.className = isLight
                    ? 'cat-sidebar-btn w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-colors flex items-center justify-between bg-slate-950 text-white font-black shadow-sm'
                    : 'cat-sidebar-btn w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-colors flex items-center justify-between bg-white text-slate-950 font-black shadow-sm';
                const badge = btn.querySelector('.cat-sidebar-count');
                if (badge) {
                    badge.className = isLight ? 'cat-sidebar-count text-[10px] font-mono text-slate-300 font-semibold' : 'cat-sidebar-count text-[10px] font-mono text-slate-800 font-black';
                }
            } else {
                btn.className = isLight
                    ? 'cat-sidebar-btn w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-colors flex items-center justify-between text-slate-600 hover:text-slate-950 hover:bg-slate-100'
                    : 'cat-sidebar-btn w-full text-left px-3 py-2 rounded-md text-xs font-medium transition-colors flex items-center justify-between text-slate-400 hover:text-white hover:bg-slate-900/50';
                const badge = btn.querySelector('.cat-sidebar-count');
                if (badge) {
                    badge.className = isLight ? 'cat-sidebar-count text-[10px] font-mono text-slate-400 font-semibold' : 'cat-sidebar-count text-[10px] font-mono text-slate-500 font-semibold';
                }
            }
        });
    }

    // Category Chip Selector (Pure instant CSS toggle on click)
    window.selectCategoryChip = function(slug) {
        slug = slug || '';

        // Instant CSS swap on the spot (0ms)
        setActiveCategoryUI(slug);

        // Update hidden form input
        hiddenCategory.value = slug;

        // Filter brand dropdown based on category
        filterBrands();

        // Fetch products via AJAX
        fetchProducts(true);
    };

    // Segmented Toggle Synchronizer (Tipe Layanan & Kondisi)
    function syncSegmentedToggles() {
        const isLight = document.documentElement.classList.contains('theme-light') || document.body.classList.contains('theme-light');
        document.querySelectorAll('.segmented-toggle').forEach(container => {
            const radios = container.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => {
                const targetDiv = radio.nextElementSibling;
                if (!targetDiv) return;
                if (radio.checked) {
                    targetDiv.style.backgroundColor = '#ffffff';
                    targetDiv.style.color = '#020617';
                    targetDiv.style.fontWeight = '900';
                    targetDiv.style.boxShadow = isLight ? '0 1px 3px rgba(0,0,0,0.1)' : '0 1px 3px rgba(0,0,0,0.25)';
                } else {
                    targetDiv.style.backgroundColor = 'transparent';
                    targetDiv.style.color = isLight ? '#64748b' : '#94a3b8';
                    targetDiv.style.fontWeight = '700';
                    targetDiv.style.boxShadow = 'none';
                }
            });
        });
    }

    // Theme Mode Toggle (Light / Dark Mode)
    window.toggleMarketplaceTheme = function() {
        const isCurrentlyLight = document.documentElement.classList.contains('theme-light') || document.body.classList.contains('theme-light');
        const nextTheme = isCurrentlyLight ? 'dark' : 'light';

        // 1. Update Cookie and localStorage
        document.cookie = "marketplace_theme=" + nextTheme + "; path=/; max-age=31536000; SameSite=Lax";
        localStorage.setItem('marketplace_theme', nextTheme);

        // 2. Toggle Classes on DOM
        const containerEl = document.getElementById('marketplace-container');
        if (nextTheme === 'light') {
            document.documentElement.classList.add('theme-light');
            document.body.classList.add('theme-light');
            if (containerEl) {
                containerEl.classList.remove('bg-[#090D16]', 'text-slate-200');
                containerEl.classList.add('bg-[#f8fafc]', 'text-slate-900');
            }
        } else {
            document.documentElement.classList.remove('theme-light');
            document.body.classList.remove('theme-light');
            if (containerEl) {
                containerEl.classList.remove('bg-[#f8fafc]', 'text-slate-900');
                containerEl.classList.add('bg-[#090D16]', 'text-slate-200');
            }
        }

        // 3. Update Toggle Buttons & Icons
        const sunIcons = document.querySelectorAll('#theme-icon-sun, #nav-theme-sun');
        const moonIcons = document.querySelectorAll('#theme-icon-moon, #nav-theme-moon');
        const themeText = document.getElementById('market-theme-text');
        const themeBtn = document.getElementById('market-theme-toggle-btn');

        if (nextTheme === 'light') {
            sunIcons.forEach(el => el.classList.add('hidden'));
            moonIcons.forEach(el => el.classList.remove('hidden'));
            if (themeText) themeText.innerText = 'Mode Gelap';
            if (themeBtn) {
                themeBtn.className = 'px-3.5 py-2.5 rounded-md border text-xs font-bold transition-all flex items-center gap-2 shadow-sm bg-white border-slate-300 hover:border-slate-400 text-slate-800';
            }
        } else {
            sunIcons.forEach(el => el.classList.remove('hidden'));
            moonIcons.forEach(el => el.classList.add('hidden'));
            if (themeText) themeText.innerText = 'Mode Terang';
            if (themeBtn) {
                themeBtn.className = 'px-3.5 py-2.5 rounded-md border text-xs font-bold transition-all flex items-center gap-2 shadow-sm bg-slate-900 border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white';
            }
        }

        // 4. Re-sync controls
        const currentCategory = hiddenCategory ? hiddenCategory.value : '';
        setActiveCategoryUI(currentCategory);
        syncSegmentedToggles();
    };

    // Ensure category active UI is synced with URL on initial load
    const initialCategoryFromUrl = new URLSearchParams(window.location.search).get('category') || hiddenCategory.value || '';
    if (initialCategoryFromUrl) {
        setActiveCategoryUI(initialCategoryFromUrl);
    }
    syncSegmentedToggles();

    // Clear Search Input
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            searchTop.value = '';
            hiddenSearch.value = '';
            clearSearchBtn.classList.add('hidden');
            fetchProducts(true);
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
    function fetchProducts(pushHistory = true) {
        hiddenSearch.value = searchTop.value;
        hiddenSort.value = sortSelectTop.value;

        if (searchTop.value) {
            clearSearchBtn.classList.remove('hidden');
        } else {
            clearSearchBtn.classList.add('hidden');
        }

        const formData = new FormData(filterForm);
        // Build clean query params (skip empty values)
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== null && value !== undefined && value.toString().trim() !== '') {
                params.append(key, value.toString().trim());
            }
        }

        // Show subtle loading state
        gridContainer.style.opacity = '0.35';

        const queryString = params.toString();
        const fetchUrl = queryString ? `{{ route('marketplace.index') }}?${queryString}` : `{{ route('marketplace.index') }}`;

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            gridContainer.innerHTML = html;
            gridContainer.style.opacity = '1';
            
            // Update URL without reloading if requested
            if (pushHistory) {
                window.history.pushState({}, '', queryString ? `?${queryString}` : window.location.pathname);
            }

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

    // Handle Browser Back / Forward Navigation without page reload
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const categoryFromUrl = urlParams.get('category') || '';
        const searchFromUrl = urlParams.get('search') || '';
        const sortFromUrl = urlParams.get('sort') || 'latest';
        const fulfillmentFromUrl = urlParams.get('fulfillment_mode') || urlParams.get('fulfillment') || '';
        const conditionFromUrl = urlParams.get('condition') || '';
        const cityFromUrl = urlParams.get('city') || '';
        const brandFromUrl = urlParams.get('brand') || '';
        const sizeFromUrl = urlParams.get('size') || '';
        const priceMinFromUrl = urlParams.get('price_min') || '';
        const priceMaxFromUrl = urlParams.get('price_max') || '';

        // Sync inputs
        hiddenCategory.value = categoryFromUrl;
        hiddenSearch.value = searchFromUrl;
        hiddenSort.value = sortFromUrl;
        searchTop.value = searchFromUrl;
        sortSelectTop.value = sortFromUrl;

        // Sync radio buttons in filterForm
        const fulfillmentRadio = filterForm.querySelector(`input[name="fulfillment_mode"][value="${fulfillmentFromUrl}"]`);
        if (fulfillmentRadio) fulfillmentRadio.checked = true;

        const conditionRadio = filterForm.querySelector(`input[name="condition"][value="${conditionFromUrl}"]`);
        if (conditionRadio) conditionRadio.checked = true;

        const citySelect = filterForm.querySelector('select[name="city"]');
        if (citySelect) citySelect.value = cityFromUrl;

        const brandSelectEl = filterForm.querySelector('select[name="brand"]');
        if (brandSelectEl) brandSelectEl.value = brandFromUrl;

        const sizeInput = filterForm.querySelector('input[name="size"]');
        if (sizeInput) sizeInput.value = sizeFromUrl;

        const priceMinInput = filterForm.querySelector('input[name="price_min"]');
        if (priceMinInput) priceMinInput.value = priceMinFromUrl;

        const priceMaxInput = filterForm.querySelector('input[name="price_max"]');
        if (priceMaxInput) priceMaxInput.value = priceMaxFromUrl;

        // Sync Category Active State (Instant CSS)
        setActiveCategoryUI(categoryFromUrl);
        syncSegmentedToggles();

        filterBrands();
        // Fetch products without pushing state again
        fetchProducts(false);
    });

    // Debounced text input event listeners
    searchTop.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => fetchProducts(true), 350);
    });

    const textInputs = filterForm.querySelectorAll('input[type="number"], input[name="size"]');
    textInputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => fetchProducts(true), 450);
        });
    });

    // Changes on Radio, Select elements
    const changeInputs = filterForm.querySelectorAll('input[type="radio"], select');
    changeInputs.forEach(input => {
        input.addEventListener('change', () => {
            syncSegmentedToggles();
            fetchProducts(true);
        });
    });

    sortSelectTop.addEventListener('change', () => fetchProducts(true));

    // Form submit
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchProducts(true);
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
