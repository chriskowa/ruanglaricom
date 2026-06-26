@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title')
    RuangLari Market
@endsection

@section('content')
<div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" class="min-h-screen pt-24 pb-20 px-4 md:px-8 bg-dark text-slate-200 font-sans">
    
    <!-- Hero / Header Section -->
    <div class="max-w-7xl mx-auto mb-6" data-aos="fade-down">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <p class="text-neon font-mono text-xs tracking-widest uppercase mb-1">Buy & Sell Gear</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    RUANG<span class="text-neon">LARI</span> MARKET
                </h1>
                <p class="text-slate-400 text-sm mt-1 max-w-xl">
                    Tempat terpercaya jual beli perlengkapan lari, slot event, dan merchandise komunitas.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('marketplace.seller.products.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-900/50 backdrop-blur-sm text-slate-300 hover:text-white font-semibold text-sm transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Mulai Jualan
                </a>
            </div>
        </div>
    </div>

    <!-- Sticky Header Bar -->
    <div class="max-w-7xl mx-auto sticky top-20 z-30 bg-dark/85 backdrop-blur-lg border border-slate-800/80 rounded-2xl p-4 mb-8 shadow-xl shadow-black/40">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            
            <!-- Left: Filter Toggle & Search Bar -->
            <div class="flex items-center gap-3 w-full sm:w-auto flex-grow max-w-xl">
                <!-- Filter Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen" class="flex items-center gap-2 px-4 py-2.5 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-white rounded-xl transition-all shrink-0">
                    <svg class="w-5 h-5 text-slate-400 transition-transform" :class="sidebarOpen ? 'text-neon rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    <span class="text-sm font-bold">Filter</span>
                </button>
                
                <!-- Search bar -->
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text" id="search-top" placeholder="Cari sepatu, apparel, aksesoris lari..." class="w-full bg-slate-900 border border-slate-800 text-white rounded-xl pl-11 pr-4 py-2.5 text-sm focus:border-neon focus:ring-1 focus:ring-neon focus:outline-none transition-all placeholder-slate-500">
                </div>
            </div>
            
            <!-- Right: Sort & Cart -->
            <div class="flex items-center gap-3 w-full sm:w-auto shrink-0 justify-end">
                <select id="sort-select-top" class="bg-slate-900 border border-slate-800 text-slate-300 rounded-xl px-4 py-2.5 text-sm focus:border-neon focus:outline-none transition-all">
                    <option value="latest">Terbaru</option>
                    <option value="price_asc">Harga: Rendah ke Tinggi</option>
                    <option value="price_desc">Harga: Tinggi ke Rendah</option>
                </select>
                
                <!-- Cart Button -->
                <a href="{{ route('marketplace.cart.index') }}" class="relative p-2.5 bg-slate-900 border border-slate-800 hover:border-slate-700 text-slate-300 hover:text-neon rounded-xl hover:scale-105 transition-all flex items-center justify-center" title="Keranjang Belanja">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span id="market-cart-badge" class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 bg-neon text-dark text-[10px] font-black rounded-full flex items-center justify-center border border-dark hidden">
                        0
                    </span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8 items-start relative w-full">
        
        <!-- Mobile Sidebar Overlay Backdrop -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/60 z-40 lg:hidden" x-transition:opacity></div>

        <!-- Sidebar Filter Panel (Fixed / Sticky Scrollable) -->
        <div x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-350"
             x-transition:enter-start="opacity-0 -translate-x-6"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-250"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 -translate-x-6"
             class="fixed inset-y-0 left-0 z-50 w-80 bg-slate-950 p-0 overflow-y-auto lg:static lg:w-72 lg:z-20 lg:h-[calc(100vh-7rem)] lg:max-h-[850px] lg:sticky lg:top-44 lg:overflow-y-auto lg:bg-slate-900/25 lg:backdrop-blur-md lg:border lg:border-slate-850 lg:rounded-2xl custom-scrollbar shrink-0">
            
            <form id="filter-form" action="{{ route('marketplace.index') }}" method="GET" class="h-full">
                <!-- Sticky Header inside Sidebar -->
                <div class="sticky top-0 bg-slate-900/95 backdrop-blur-md z-10 py-4 px-6 border-b border-slate-850 flex items-center justify-between">
                    <span class="text-xs font-black text-white uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Filter
                    </span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('marketplace.index') }}" class="text-[11px] text-neon hover:underline font-bold transition-all">Reset</a>
                        <button type="button" @click="sidebarOpen = false" class="lg:hidden p-1 text-slate-450 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Hidden inputs for Syncing Top Controls -->
                    <input type="hidden" name="search" id="hidden-search" value="{{ request('search') }}">
                    <input type="hidden" name="sort" id="hidden-sort" value="{{ request('sort') }}">
                    
                    <!-- Price Range -->
                    <div class="space-y-3">
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold font-mono block">Rentang Harga</span>
                        <div class="space-y-2">
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[10px] text-slate-500 font-semibold font-mono">Rp</span>
                                <input type="number" name="price_min" placeholder="Minimum" value="{{ request('price_min') }}" class="w-full bg-slate-950 border border-slate-850 text-white rounded-xl pl-9 pr-3 py-2 text-xs focus:border-neon focus:outline-none placeholder-slate-650 transition-all">
                            </div>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[10px] text-slate-500 font-semibold font-mono">Rp</span>
                                <input type="number" name="price_max" placeholder="Maximum" value="{{ request('price_max') }}" class="w-full bg-slate-950 border border-slate-850 text-white rounded-xl pl-9 pr-3 py-2 text-xs focus:border-neon focus:outline-none placeholder-slate-655 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Ratings (UI Filter) -->
                    <div class="space-y-3">
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold font-mono block">Rating Produk</span>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="radio" name="rating" value="" class="hidden peer" {{ !request('rating') ? 'checked' : '' }}>
                                <div class="w-4 h-4 rounded-full border border-slate-750 peer-checked:bg-neon peer-checked:border-neon transition-all flex items-center justify-center">
                                    <div class="w-1.5 h-1.5 rounded-full bg-dark opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <span class="text-xs text-slate-400 group-hover:text-white transition-colors peer-checked:text-white">Semua Rating</span>
                            </label>
                            @foreach([5, 4, 3] as $stars)
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="radio" name="rating" value="{{ $stars }}" class="hidden peer" {{ request('rating') == $stars ? 'checked' : '' }}>
                                <div class="w-4 h-4 rounded-full border border-slate-755 peer-checked:bg-neon peer-checked:border-neon transition-all flex items-center justify-center">
                                    <div class="w-1.5 h-1.5 rounded-full bg-dark opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <span class="text-xs text-slate-400 group-hover:text-white transition-colors peer-checked:text-white flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-3 h-3 {{ $i <= $stars ? 'text-amber-400' : 'text-slate-750' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    @endfor
                                    <span class="text-[10px] text-slate-500 ml-1 font-medium">{{ $stars == 5 ? '' : '& ke atas' }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Categories Accordion / Collapsible List -->
                    <div class="space-y-3">
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold font-mono block">Kategori</span>
                        <div class="space-y-2">
                            <!-- All Items Category -->
                            <div class="border border-slate-850/60 rounded-xl p-2.5 bg-slate-950/15">
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input type="radio" name="category" value="" class="hidden peer" {{ !request('category') ? 'checked' : '' }}>
                                    <div class="w-3.5 h-3.5 rounded-full border border-slate-700 peer-checked:bg-neon peer-checked:border-neon transition-all flex items-center justify-center">
                                        <div class="w-1.5 h-1.5 rounded-full bg-dark opacity-0 peer-checked:opacity-100"></div>
                                    </div>
                                    <span class="text-xs text-slate-400 group-hover:text-white transition-colors peer-checked:text-white peer-checked:font-bold">Semua Kategori</span>
                                </label>
                            </div>

                            @foreach($categories as $cat)
                            @php
                                $hasActiveChild = $cat->children && $cat->children->pluck('slug')->contains(request('category'));
                                $isCatOpen = request('category') == $cat->slug || $hasActiveChild;
                            @endphp
                            <div x-data="{ open: {{ $isCatOpen ? 'true' : 'false' }} }" class="border border-slate-850/60 rounded-xl p-2.5 bg-slate-950/15">
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2.5 cursor-pointer group flex-grow">
                                        <input type="radio" name="category" value="{{ $cat->slug }}" class="hidden peer" {{ request('category') == $cat->slug ? 'checked' : '' }}>
                                        <div class="w-3.5 h-3.5 rounded-full border border-slate-700 peer-checked:bg-neon peer-checked:border-neon transition-all flex items-center justify-center">
                                            <div class="w-1.5 h-1.5 rounded-full bg-dark opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                        <span class="text-xs text-slate-400 group-hover:text-white transition-colors peer-checked:text-white peer-checked:font-bold">{{ $cat->name }}</span>
                                        @if($cat->products_count > 0)
                                        <span class="text-[9px] bg-slate-800 text-slate-500 px-1.5 py-0.5 rounded ml-auto font-mono">{{ $cat->products_count }}</span>
                                        @endif
                                    </label>
                                    @if($cat->children->count() > 0)
                                        <button type="button" @click="open = !open" class="p-1 text-slate-500 hover:text-white transition-colors">
                                            <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                
                                @if($cat->children->count() > 0)
                                    <div x-show="open" x-transition class="mt-2 pl-3 ml-2 border-l border-slate-800 space-y-2 pt-1">
                                        @foreach($cat->children as $child)
                                        <label class="flex items-center gap-2 cursor-pointer group">
                                            <input type="radio" name="category" value="{{ $child->slug }}" class="hidden peer" {{ request('category') == $child->slug ? 'checked' : '' }}>
                                            <div class="w-3 h-3 rounded-full border border-slate-700 peer-checked:bg-neon peer-checked:border-neon transition-all flex items-center justify-center">
                                                <div class="w-1 h-1 rounded-full bg-dark opacity-0 peer-checked:opacity-100"></div>
                                            </div>
                                            <span class="text-[11px] text-slate-450 group-hover:text-white transition-colors peer-checked:text-white peer-checked:font-semibold">{{ $child->name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Condition -->
                    <div class="space-y-3">
                        <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold font-mono block">Kondisi Barang</span>
                        <div class="flex gap-2">
                            <label class="flex-1 text-center cursor-pointer">
                                <input type="radio" name="condition" value="" class="hidden peer" {{ !request('condition') ? 'checked' : '' }}>
                                <div class="py-1.5 rounded-lg border border-slate-850 text-[11px] text-slate-400 peer-checked:border-neon peer-checked:bg-neon/10 peer-checked:text-neon font-bold hover:border-slate-750 transition-all">Semua</div>
                            </label>
                            <label class="flex-1 text-center cursor-pointer">
                                <input type="radio" name="condition" value="new" class="hidden peer" {{ request('condition') == 'new' ? 'checked' : '' }}>
                                <div class="py-1.5 rounded-lg border border-slate-850 text-[11px] text-slate-400 peer-checked:border-neon peer-checked:bg-neon/10 peer-checked:text-neon font-bold hover:border-slate-750 transition-all">Baru</div>
                            </label>
                            <label class="flex-1 text-center cursor-pointer">
                                <input type="radio" name="condition" value="used" class="hidden peer" {{ request('condition') == 'used' ? 'checked' : '' }}>
                                <div class="py-1.5 rounded-lg border border-slate-850 text-[11px] text-slate-400 peer-checked:border-neon peer-checked:bg-neon/10 peer-checked:text-neon font-bold hover:border-slate-750 transition-all">Bekas</div>
                            </label>
                        </div>
                    </div>

                    <!-- Brands -->
                    <div class="space-y-2">
                        <label class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold font-mono block">Brand</label>
                        <select name="brand" id="brand-select" class="w-full bg-slate-950 border border-slate-850 text-slate-305 rounded-xl px-3 py-2 text-xs focus:border-neon focus:outline-none transition-colors">
                            <option value="">Semua Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location -->
                    <div class="space-y-2">
                        <label class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold font-mono block">Lokasi Seller</label>
                        <select name="city" class="w-full bg-slate-950 border border-slate-855 text-slate-305 rounded-xl px-3 py-2 text-xs focus:border-neon focus:outline-none transition-colors">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Size -->
                    <div class="space-y-2">
                        <label class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold font-mono block">Ukuran (Size)</label>
                        <input type="text" name="size" value="{{ request('size') }}" placeholder="Contoh: 42, M, L" class="w-full bg-slate-950 border border-slate-850 text-white rounded-xl px-3 py-2 text-xs focus:border-neon focus:outline-none placeholder-slate-650 transition-all">
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2 pt-2 pb-4">
                        <button type="submit" class="flex-1 bg-neon text-dark font-black py-2.5 rounded-xl hover:bg-white hover:text-dark transition-colors text-xs">Terapkan</button>
                        <a href="{{ route('marketplace.index') }}" class="px-4 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition-colors text-xs flex items-center justify-center">Reset</a>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filter-form');
    const hiddenSearch = document.getElementById('hidden-search');
    const hiddenSort = document.getElementById('hidden-sort');
    
    const searchTop = document.getElementById('search-top');
    const sortSelectTop = document.getElementById('sort-select-top');
    
    const gridContainer = document.getElementById('product-grid-container');
    const brandSelect = document.getElementById('brand-select');
    const categoryInputs = document.querySelectorAll('input[name="category"]');
    let searchTimeout;

    // Sync Top Controls to Form State initially
    searchTop.value = hiddenSearch.value;
    sortSelectTop.value = hiddenSort.value || 'latest';

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
        const selectedCategoryInput = document.querySelector('input[name="category"]:checked');
        const selectedCategory = selectedCategoryInput ? selectedCategoryInput.value : '';
        
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

    // Initial brand filtering
    filterBrands();

    // Listen for category changes to dynamically update brand option availability
    categoryInputs.forEach(input => {
        input.addEventListener('change', filterBrands);
    });

    // Function to fetch products via AJAX
    function fetchProducts() {
        // Sync top inputs to hidden fields inside form
        hiddenSearch.value = searchTop.value;
        hiddenSort.value = sortSelectTop.value;

        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);

        // Show loading state
        gridContainer.style.opacity = '0.4';

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
        })
        .catch(error => {
            console.error('Error:', error);
            gridContainer.style.opacity = '1';
        });
    }

    // Debounced text input event listeners
    searchTop.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(fetchProducts, 400);
    });

    const textInputs = filterForm.querySelectorAll('input[type="number"], input[name="size"]');
    textInputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchProducts, 500);
        });
    });

    // Changes on Radio, Checkbox, Select elements
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
        if (e.target.tagName === 'A' && e.target.closest('.pagination-container')) {
            e.preventDefault();
            const url = e.target.getAttribute('href');
            
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
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    });
});
</script>
@endpush
