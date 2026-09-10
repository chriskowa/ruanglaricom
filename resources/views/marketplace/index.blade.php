@php
    $withSidebar = true;
    if (request('theme') === 'dark') {
        session()->forget('marketplace_theme');
    }
@endphp
@extends('layouts.pacerhub', ['lightMode' => false])

@section('title', 'Marketplace Perlengkapan Lari | RuangLari')
@section('meta_title', 'Marketplace Perlengkapan Lari | RuangLari')
@section('meta_description', 'Jual beli perlengkapan lari original, sepatu lari, jam GPS, jersey, dan aksesori lari terpercaya dari komunitas pelari Indonesia.')
@section('meta_keywords', 'marketplace lari, sepatu lari bekas, running gear indonesia, titip jual sepatu lari, Garmin second, Nike Vaporfly, Adidas Adizero, slot marathon')
@section('og_type', 'website')
@section('og_title', 'Marketplace RuangLari')
@section('og_description', 'Temukan sepatu lari original, jam GPS, jersey, dan aksesori lari berkualitas dari komunitas pelari.')
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

@push('styles')
<style>
/* Solid Opaque Sidebar Drawer */
#mobile-sidebar-filter {
    background-color: #0c121e;
    opacity: 1 !important;
}
#mobile-sidebar-filter .sidebar-sticky-head {
    background-color: #0c121e;
    opacity: 1 !important;
}

/* =========================================================
   LIGHT MODE STYLES (HIGH CONTRAST & ACCESSIBLE)
========================================================= */
html.theme-light,
body.theme-light,
.theme-light #marketplace-container {
    background-color: #f8fafc !important;
    color: #0f172a !important;
}

.theme-light #marketplace-container h1 {
    color: #020617 !important;
}
.theme-light #marketplace-container p {
    color: #475569 !important;
}
.theme-light .editorial-header-border {
    border-color: #e2e8f0 !important;
}

.theme-light #market-theme-toggle-btn {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light #market-theme-toggle-btn:hover {
    background-color: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #020617 !important;
}
.theme-light a[href*="marketplace/cart"] {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light a[href*="marketplace/cart"]:hover {
    background-color: #f1f5f9 !important;
    border-color: #94a3b8 !important;
    color: #020617 !important;
}
.theme-light a[href*="marketplace/cart"] svg {
    color: #475569 !important;
}
.theme-light a[href*="seller/products/create"] {
    background-color: #020617 !important;
    color: #ffffff !important;
}
.theme-light a[href*="seller/products/create"]:hover {
    background-color: #1e293b !important;
}

/* Category Navigation in Light Mode */
.theme-light nav[aria-label="Kategori Marketplace"] {
    border-color: #e2e8f0 !important;
}
.theme-light .cat-chip-btn {
    color: #64748b !important;
}
.theme-light .cat-chip-btn:hover {
    color: #020617 !important;
}
.theme-light .cat-chip-btn.active-cat {
    color: #020617 !important;
    border-color: #020617 !important;
}

/* Sticky Filter Bar */
.theme-light .sticky-filter-bar {
    background-color: #f8fafc !important;
    border-color: #e2e8f0 !important;
}
.theme-light .filter-toggle-btn {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light .filter-toggle-btn:hover {
    background-color: #f1f5f9 !important;
    border-color: #94a3b8 !important;
}
.theme-light #search-top {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light #search-top::placeholder {
    color: #64748b !important;
}
.theme-light #search-top:focus {
    border-color: #0f172a !important;
}
.theme-light #sort-select-top {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light #results-count-label {
    color: #334155 !important;
    font-weight: 700 !important;
}

/* Sidebar Filter Panel (Mobile & Desktop) */
.theme-light #mobile-sidebar-filter {
    background-color: #ffffff !important;
    border-color: #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light #mobile-sidebar-filter .sidebar-sticky-head {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
}
.theme-light #mobile-sidebar-filter .sidebar-sticky-head span {
    color: #020617 !important;
}
.theme-light #mobile-sidebar-filter .sidebar-sticky-head a {
    color: #0284c7 !important;
}
.theme-light #mobile-sidebar-filter label {
    color: #020617 !important;
    font-weight: 700 !important;
}
.theme-light #mobile-sidebar-filter input[type="text"],
.theme-light #mobile-sidebar-filter input[type="number"],
.theme-light #mobile-sidebar-filter select {
    background-color: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #020617 !important;
    font-weight: 600 !important;
}
.theme-light #mobile-sidebar-filter input[type="text"]::placeholder,
.theme-light #mobile-sidebar-filter input[type="number"]::placeholder {
    color: #64748b !important;
}
.theme-light #mobile-sidebar-filter input[type="text"]:focus,
.theme-light #mobile-sidebar-filter input[type="number"]:focus,
.theme-light #mobile-sidebar-filter select:focus {
    border-color: #020617 !important;
    box-shadow: 0 0 0 1px #020617 !important;
}

/* Sidebar Action Buttons */
.theme-light #filter-form button[type="submit"] {
    background-color: #020617 !important;
    color: #ffffff !important;
    border: 1px solid #020617 !important;
}
.theme-light #filter-form button[type="submit"]:hover {
    background-color: #1e293b !important;
}
.theme-light #filter-form a[href*="marketplace"] {
    background-color: #ffffff !important;
    color: #334155 !important;
    border: 1px solid #cbd5e1 !important;
}
.theme-light #filter-form a[href*="marketplace"]:hover {
    background-color: #f1f5f9 !important;
    color: #020617 !important;
}

/* Product Cards in Light Mode */
.theme-light .product-card-item {
    background-color: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04) !important;
}
.theme-light .product-card-item:hover {
    border-color: #cbd5e1 !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06) !important;
}
.theme-light .product-card-item .product-card-img {
    background-color: #f1f5f9 !important;
}
.theme-light .product-card-item .product-card-meta {
    color: #64748b !important;
    font-weight: 700 !important;
}
.theme-light .product-card-item .product-card-title,
.theme-light .product-card-item .product-card-title a {
    color: #0f172a !important;
}
.theme-light .product-card-item .product-card-title a:hover {
    color: #0284c7 !important;
}
.theme-light .product-card-item .product-card-price {
    color: #020617 !important;
    font-family: inherit !important;
}
.theme-light .product-card-item span[title] {
    color: #64748b !important;
}
.theme-light .btn-manage-gear {
    background-color: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    color: #0f172a !important;
}
.theme-light .btn-manage-gear:hover {
    background-color: #e2e8f0 !important;
}

/* Empty State */
.theme-light .empty-state-box {
    background-color: #ffffff !important;
    border-color: #e2e8f0 !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
}
.theme-light .empty-state-box h3 {
    color: #020617 !important;
}
.theme-light .empty-state-box p {
    color: #475569 !important;
}
.theme-light .empty-state-box .btn-empty-sell {
    background-color: #020617 !important;
    color: #ffffff !important;
}

/* Pagination in Light Mode */
.theme-light .pagination-container a,
.theme-light .pagination-container span {
    border-color: #cbd5e1 !important;
    color: #334155 !important;
}
.theme-light .pagination-container a:hover {
    background-color: #f1f5f9 !important;
    color: #020617 !important;
}

/* CRITICAL: STRICT FOOTER PROTECTION - FOOTER IS ALWAYS DARK ATHLETIC */
.theme-light footer,
body.theme-light footer,
html.theme-light footer,
footer[aria-label="Footer Ruang Lari"] {
    background-color: #020617 !important;
    color: #94a3b8 !important;
}
.theme-light footer h2,
.theme-light footer h3,
.theme-light footer h4 {
    color: #ffffff !important;
}
.theme-light footer p {
    color: #cbd5e1 !important;
}
.theme-light footer a {
    color: #cbd5e1 !important;
}
.theme-light footer a:hover {
    color: #ffffff !important;
}
.theme-light footer a[title="RuangLari Utama"] {
    color: #ffffff !important;
}
.theme-light footer a[title="RuangLari Utama"] span {
    color: #ccff00 !important;
}
</style>
@endpush

@section('content')
<script>
    (function() {
        var urlTheme = new URLSearchParams(window.location.search).get('theme');
        var savedTheme = urlTheme || localStorage.getItem('marketplace_theme') || (document.cookie.match(/marketplace_theme=([^;]+)/) || [])[1];
        if (urlTheme === 'dark') {
            savedTheme = 'dark';
            localStorage.setItem('marketplace_theme', 'dark');
            document.cookie = "marketplace_theme=dark; path=/; max-age=31536000; SameSite=Lax";
        } else if (urlTheme === 'light') {
            savedTheme = 'light';
            localStorage.setItem('marketplace_theme', 'light');
            document.cookie = "marketplace_theme=light; path=/; max-age=31536000; SameSite=Lax";
        }

        if (savedTheme === 'light') {
            document.documentElement.classList.add('theme-light');
            if (document.body) document.body.classList.add('theme-light');
        } else {
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
    class="min-h-screen mt-10 pt-2 md:pt-4 pb-20 px-4 md:px-8 bg-[#090D16] text-slate-200 font-sans selection:bg-white selection:text-slate-950 transition-colors duration-200">
    
    <!-- Clean, Focused Header -->
    <div class="max-w-7xl mx-auto mb-6">
        <div class="editorial-header-border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">
                    Marketplace
                </h1>
                <p class="text-slate-300 text-xs md:text-sm mt-1">
                    Jual beli perlengkapan lari original dari komunitas pelari.
                </p>                
            </div>
            
            <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto">
                <!-- Theme Toggle Button -->
                <button type="button" onclick="toggleMarketplaceTheme()" id="market-theme-toggle-btn" 
                        class="px-3 py-2 rounded-md border text-xs font-semibold transition-all flex items-center gap-2 bg-slate-900 border-slate-700 hover:border-slate-600 text-slate-200 hover:text-white"
                        title="Ubah Tema">
                    <svg id="theme-icon-sun" class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg id="theme-icon-moon" class="w-4 h-4 text-slate-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span id="market-theme-text">Mode Terang</span>
                </button>

                <!-- Keranjang -->
                <a href="{{ route('marketplace.cart.index') }}" class="relative px-3.5 py-2 rounded-md bg-slate-900 border-slate-700 hover:border-slate-600 text-slate-200 hover:text-white border text-xs font-semibold transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Keranjang</span>
                    <span id="market-cart-badge" class="min-w-[18px] h-[18px] px-1 bg-white text-slate-950 text-[10px] font-bold rounded flex items-center justify-center hidden font-sans">0</span>
                </a>

                <!-- Jual Gear -->
                <a href="{{ auth()->check() ? route('marketplace.seller.products.create') : route('login', ['redirect' => route('marketplace.seller.products.create')]) }}" class="flex-1 sm:flex-initial px-4 py-2 rounded-md bg-white hover:bg-slate-200 text-slate-950 font-bold text-xs transition-all flex items-center justify-center shadow-sm">
                    <span>+ Jual Gear</span>
                </a>
            </div>
        </div>

        <!-- Clean Horizontal Category Navigation Tabs (No Pill Frames, Clear Hierarchy) -->
        <nav class="flex items-center gap-6 overflow-x-auto border-b border-slate-800 pt-2 pb-3 no-scrollbar text-xs md:text-sm font-semibold" aria-label="Kategori Marketplace">
            @php $currentCat = request('category', ''); @endphp
            <button type="button" onclick="selectCategoryChip('')" data-category=""
                    class="cat-chip-btn pb-1 whitespace-nowrap transition-colors relative {{ empty($currentCat) ? 'text-white border-b-2 border-white font-bold active-cat' : 'text-slate-300 hover:text-white' }}">
                Semua
            </button>
            @foreach($categories as $cat)
            <button type="button" onclick="selectCategoryChip('{{ $cat->slug }}')" data-category="{{ $cat->slug }}"
                    class="cat-chip-btn pb-1 whitespace-nowrap transition-colors relative {{ $currentCat == $cat->slug ? 'text-white border-b-2 border-white font-bold active-cat' : 'text-slate-300 hover:text-white' }}">
                {{ $cat->name }}
            </button>
            @endforeach
        </nav>
    </div>

    <!-- Clean Sticky Filter Bar -->
    <div class="sticky-filter-bar max-w-7xl mx-auto sticky top-16 md:top-20 z-30 bg-[#090D16] border-slate-800 border-b py-3 mb-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            
            <!-- Left: Filter Toggle & Search -->
            <div class="flex items-center gap-2.5 w-full sm:w-auto flex-grow max-w-lg">
                <!-- Filter Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="filter-toggle-btn flex items-center gap-2 px-3.5 py-2 bg-slate-900 border border-slate-700 hover:border-slate-600 text-slate-200 rounded-md transition-all shrink-0 text-xs font-semibold"
                        :class="sidebarOpen ? 'border-white text-white' : ''">
                    <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Filter</span>
                </button>
                
                <!-- Search Input -->
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" id="search-top" placeholder="Cari Vaporfly, Adizero, Garmin, Jersey..." 
                           class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-400 focus:border-white border rounded-md pl-10 pr-8 py-2 text-xs focus:outline-none transition-all font-medium">
                    <button type="button" id="clear-search-btn" class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-white hidden">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
            
            <!-- Right: Item Counter & Sort Dropdown -->
            <div class="flex items-center gap-3 w-full sm:w-auto shrink-0 justify-between sm:justify-end">
                <div class="text-xs text-slate-200 font-semibold" id="results-count-label">
                    {{ $products->total() }} Produk
                </div>
                
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-300 hidden sm:inline font-medium">Urutkan:</span>
                    <select id="sort-select-top" class="bg-slate-900 border-slate-700 text-slate-200 focus:border-white border rounded-md px-3 py-2 text-xs focus:outline-none transition-all font-medium">
                        <option value="latest">Terbaru</option>
                        <option value="price_asc">Harga Terendah</option>
                        <option value="price_desc">Harga Tertinggi</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8 items-start relative w-full">
        
        <!-- Mobile Sidebar Overlay Backdrop -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 lg:hidden" style="background-color: rgba(0, 0, 0, 0.8) !important;" x-transition:opacity></div>

        <!-- Sidebar Filter Panel (Clean, Crisp & Functional) -->
        <div x-show="sidebarOpen"
             id="mobile-sidebar-filter"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-x-4"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-4"
             class="fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] p-0 overflow-y-auto lg:static lg:w-72 lg:z-20 lg:h-[calc(100vh-8rem)] lg:sticky lg:top-36 lg:overflow-y-auto border-r border-slate-800 lg:border lg:border-slate-800 lg:rounded-lg custom-scrollbar shrink-0 shadow-2xl lg:shadow-none">
            
            <form id="filter-form" action="{{ route('marketplace.index') }}" method="GET" class="h-full">
                <!-- Sticky Header inside Sidebar -->
                <div class="sidebar-sticky-head sticky top-0 z-10 py-3.5 px-5 border-b border-slate-800 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-white">
                        Filter Produk
                    </span>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('marketplace.index') }}" class="text-xs font-semibold text-slate-300 hover:text-white underline">Reset</a>
                        <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1 text-slate-300 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <div class="p-5 space-y-5">
                    <!-- Hidden inputs for Syncing Top Controls -->
                    <input type="hidden" name="search" id="hidden-search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" id="hidden-sort" value="{{ request('sort') }}">
                    <input type="hidden" name="category" id="hidden-category" value="{{ request('category') }}">
                    
                    <!-- Kondisi Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-200 block">Kondisi</label>
                        <select name="condition" class="w-full bg-slate-900 border-slate-700 text-slate-200 focus:border-white border rounded-md px-3 py-2 text-xs focus:outline-none transition-colors">
                            <option value="">Semua Kondisi</option>
                            <option value="new" {{ request('condition') == 'new' ? 'selected' : '' }}>Baru (BNIB)</option>
                            <option value="used" {{ request('condition') == 'used' ? 'selected' : '' }}>Bekas (Used)</option>
                        </select>
                    </div>

                    <!-- Tipe Layanan Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-200 block">Tipe Layanan</label>
                        <select name="fulfillment_mode" class="w-full bg-slate-900 border-slate-700 text-slate-200 focus:border-white border rounded-md px-3 py-2 text-xs focus:outline-none transition-colors">
                            <option value="">Semua Layanan</option>
                            <option value="consignment" {{ request('fulfillment_mode') == 'consignment' ? 'selected' : '' }}>Titip Jual Resmi</option>
                            <option value="self_ship" {{ request('fulfillment_mode') == 'self_ship' ? 'selected' : '' }}>Direct Seller</option>
                        </select>
                    </div>

                    <!-- Brand Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-200 block">Brand</label>
                        <select name="brand" id="brand-select" class="w-full bg-slate-900 border-slate-700 text-slate-200 focus:border-white border rounded-md px-3 py-2 text-xs focus:outline-none transition-colors">
                            <option value="">Semua Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Size Filter -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-200 block">Ukuran / Size</label>
                        <input type="text" name="size" value="{{ request('size') }}" placeholder="Contoh: 42, 43, M, L..." 
                               class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-400 focus:border-white border rounded-md px-3 py-2 text-xs focus:outline-none transition-all">
                    </div>

                    <!-- Location / City -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-200 block">Lokasi Seller</label>
                        <select name="city" class="w-full bg-slate-900 border-slate-700 text-slate-200 focus:border-white border rounded-md px-3 py-2 text-xs focus:outline-none transition-colors">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-200 block">Rentang Harga (Rp)</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}" 
                                   class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-400 focus:border-white border rounded-md px-3 py-2 text-xs focus:outline-none transition-all font-semibold font-sans">
                            <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}" 
                                   class="w-full bg-slate-900 border-slate-700 text-white placeholder-slate-400 focus:border-white border rounded-md px-3 py-2 text-xs focus:outline-none transition-all font-semibold font-sans">
                        </div>
                    </div>

                    <!-- Filter Actions -->
                    <div class="pt-3 pb-6 flex gap-2">
                        <button type="submit" class="flex-1 bg-white hover:bg-slate-200 text-slate-950 font-bold py-2.5 rounded-md transition-colors text-xs uppercase tracking-wider">Terapkan</button>
                        <a href="{{ route('marketplace.index') }}" class="px-4 py-2.5 bg-slate-900 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 font-semibold rounded-md transition-colors text-xs flex items-center justify-center">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Product Grid Container (AJAX Target) -->
        <div class="flex-1 w-full min-w-0" id="marketplace-grid-container">
            @include('marketplace.partials.product-grid')
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchTop = document.getElementById('search-top');
    const sortSelectTop = document.getElementById('sort-select-top');
    const clearSearchBtn = document.getElementById('clear-search-btn');
    const gridContainer = document.getElementById('marketplace-grid-container');
    const resultsCountLabel = document.getElementById('results-count-label');
    const filterForm = document.getElementById('filter-form');
    const brandSelect = document.getElementById('brand-select');
    
    // Hidden inputs
    const hiddenCategory = document.getElementById('hidden-category');
    const hiddenSearch = document.getElementById('hidden-search');
    const hiddenSort = document.getElementById('hidden-sort');

    let searchTimeout = null;

    // Synchronize Top Controls with Hidden Inputs
    if (hiddenSearch.value) {
        searchTop.value = hiddenSearch.value;
        if (clearSearchBtn) clearSearchBtn.classList.remove('hidden');
    }
    if (hiddenSort.value) {
        sortSelectTop.value = hiddenSort.value;
    }

    // Set Active Category UI (Clean Text Tabs)
    function setActiveCategoryUI(slug) {
        const isLight = document.documentElement.classList.contains('theme-light') || document.body.classList.contains('theme-light');
        document.querySelectorAll('.cat-chip-btn').forEach(btn => {
            const isMatch = (btn.dataset.category || '') === slug;
            if (isMatch) {
                btn.classList.add('active-cat');
                if (isLight) {
                    btn.className = 'cat-chip-btn pb-1 whitespace-nowrap transition-colors relative text-slate-950 border-b-2 border-slate-950 font-bold active-cat';
                } else {
                    btn.className = 'cat-chip-btn pb-1 whitespace-nowrap transition-colors relative text-white border-b-2 border-white font-bold active-cat';
                }
            } else {
                btn.classList.remove('active-cat');
                if (isLight) {
                    btn.className = 'cat-chip-btn pb-1 whitespace-nowrap transition-colors relative text-slate-600 hover:text-slate-950 font-semibold';
                } else {
                    btn.className = 'cat-chip-btn pb-1 whitespace-nowrap transition-colors relative text-slate-300 hover:text-white font-semibold';
                }
            }
        });
    }

    // Category Selector
    window.selectCategoryChip = function(slug) {
        slug = slug || '';
        setActiveCategoryUI(slug);
        hiddenCategory.value = slug;
        filterBrands();
        fetchProducts(true);
    };

    // Theme Mode Toggle (Light / Dark Mode)
    window.toggleMarketplaceTheme = function() {
        const isCurrentlyLight = document.documentElement.classList.contains('theme-light') || document.body.classList.contains('theme-light');
        const nextTheme = isCurrentlyLight ? 'dark' : 'light';

        document.cookie = "marketplace_theme=" + nextTheme + "; path=/; max-age=31536000; SameSite=Lax";
        localStorage.setItem('marketplace_theme', nextTheme);

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

        syncThemeButtonUI();
        const currentCategory = hiddenCategory ? hiddenCategory.value : '';
        setActiveCategoryUI(currentCategory);
    };

    function syncThemeButtonUI() {
        const isLight = document.documentElement.classList.contains('theme-light');
        const sunIcon = document.getElementById('theme-icon-sun');
        const moonIcon = document.getElementById('theme-icon-moon');
        const themeText = document.getElementById('market-theme-text');
        const themeBtn = document.getElementById('market-theme-toggle-btn');

        if (isLight) {
            if (sunIcon) sunIcon.classList.add('hidden');
            if (moonIcon) moonIcon.classList.remove('hidden');
            if (themeText) themeText.innerText = 'Mode Gelap';
            if (themeBtn) {
                themeBtn.className = 'px-3 py-2 rounded-md border text-xs font-semibold transition-all flex items-center gap-2 bg-white border-slate-300 hover:border-slate-400 text-slate-800';
            }
        } else {
            if (sunIcon) sunIcon.classList.remove('hidden');
            if (moonIcon) moonIcon.classList.add('hidden');
            if (themeText) themeText.innerText = 'Mode Terang';
            if (themeBtn) {
                themeBtn.className = 'px-3 py-2 rounded-md border text-xs font-semibold transition-all flex items-center gap-2 bg-slate-900 border-slate-700 hover:border-slate-600 text-slate-200 hover:text-white';
            }
        }
    }
    syncThemeButtonUI();

    const initialCategoryFromUrl = new URLSearchParams(window.location.search).get('category') || hiddenCategory.value || '';
    if (initialCategoryFromUrl) {
        setActiveCategoryUI(initialCategoryFromUrl);
    }

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

    // Wishlist Toggle Function (AJAX)
    window.quickToggleWishlist = function(productId, btnEl) {
        const isAuth = {{ auth()->check() ? 'true' : 'false' }};
        if (!isAuth) {
            window.location.href = "{{ route('login') }}";
            return;
        }

        const svgEl = btnEl.querySelector('svg');
        btnEl.disabled = true;

        fetch(`/marketplace/wishlist/toggle/${productId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.status === 401) {
                window.location.href = "{{ route('login') }}";
                return;
            }
            return response.json();
        })
        .then(data => {
            btnEl.disabled = false;
            if (!data) return;
            if (data.in_wishlist) {
                btnEl.classList.remove('text-slate-300', 'hover:text-white');
                btnEl.classList.add('text-rose-500', 'border-rose-500/50');
                if (svgEl) {
                    svgEl.classList.remove('fill-none');
                    svgEl.classList.add('fill-current');
                }
            } else {
                btnEl.classList.remove('text-rose-500', 'border-rose-500/50');
                btnEl.classList.add('text-slate-300', 'hover:text-white');
                if (svgEl) {
                    svgEl.classList.remove('fill-current');
                    svgEl.classList.add('fill-none');
                }
            }
        })
        .catch(err => {
            console.error('Wishlist error:', err);
            btnEl.disabled = false;
        });
    };

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
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== null && value !== undefined && value.toString().trim() !== '') {
                params.append(key, value.toString().trim());
            }
        }

        gridContainer.style.opacity = '0.4';

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
            
            if (pushHistory) {
                window.history.pushState({}, '', queryString ? `?${queryString}` : window.location.pathname);
            }

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

        const fulfillmentSelect = filterForm.querySelector('select[name="fulfillment_mode"]');
        if (fulfillmentSelect) fulfillmentSelect.value = fulfillmentFromUrl;

        const conditionSelect = filterForm.querySelector('select[name="condition"]');
        if (conditionSelect) conditionSelect.value = conditionFromUrl;

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

        setActiveCategoryUI(categoryFromUrl);
        filterBrands();
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

    // Changes on Select elements
    const changeInputs = filterForm.querySelectorAll('select');
    changeInputs.forEach(input => {
        input.addEventListener('change', () => {
            fetchProducts(true);
        });
    });

    sortSelectTop.addEventListener('change', () => fetchProducts(true));

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
            
            gridContainer.style.opacity = '0.4';
            
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
                window.scrollTo({ top: 120, behavior: 'smooth' });
            });
        }
    });
});
</script>
@endpush
