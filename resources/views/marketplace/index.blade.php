@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title')
    RuangLari Market
@endsection

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="mb-10 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase mb-1">Buy & Sell Gear</p>
                <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter">
                    MARKET<span class="text-neon">PLACE</span>
                </h1>
                <p class="text-slate-400 mt-2 max-w-xl">
                    Tempat jual beli gear lari, slot event, dan merchandise terpercaya.
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('marketplace.seller.products.index') }}" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Mulai Jualan
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Sidebar Filters -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto custom-scrollbar">
                
                <form id="filter-form" action="{{ route('marketplace.index') }}" method="GET">
                    
                    <!-- Search -->
                    <div class="mb-6">
                        <label class="block text-slate-400 text-xs font-bold uppercase mb-2">Search</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang..." class="w-full bg-slate-950 border border-slate-700 text-white rounded-xl px-4 py-2 text-sm focus:border-neon focus:ring-1 focus:ring-neon focus:outline-none transition-all placeholder-slate-600">
                            <button type="submit" class="absolute right-2 top-2 p-0.5 text-slate-400 hover:text-neon transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="mb-6 border-b border-slate-800 pb-6">
                        <label class="block text-slate-400 text-xs font-bold uppercase mb-2">Category</label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="category" value="" class="hidden peer" {{ !request('category') ? 'checked' : '' }}>
                                <div class="w-4 h-4 rounded-full border border-slate-600 peer-checked:bg-neon peer-checked:border-neon transition-all flex items-center justify-center"></div>
                                <span class="text-sm text-slate-400 group-hover:text-white transition-colors peer-checked:text-white peer-checked:font-bold">All Items</span>
                            </label>
                            @foreach($categories as $cat)
                            <div class="mb-2">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="radio" name="category" value="{{ $cat->slug }}" class="hidden peer" {{ request('category') == $cat->slug ? 'checked' : '' }}>
                                    <div class="w-4 h-4 rounded-full border border-slate-600 peer-checked:bg-neon peer-checked:border-neon transition-all flex items-center justify-center"></div>
                                    <span class="text-sm text-slate-400 group-hover:text-white transition-colors peer-checked:text-white peer-checked:font-bold">{{ $cat->name }}</span>
                                    @if($cat->products_count > 0)
                                    <span class="text-[10px] bg-slate-800 text-slate-500 px-1.5 rounded ml-auto">{{ $cat->products_count }}</span>
                                    @endif
                                </label>
                                
                                @if($cat->children->count() > 0)
                                <div class="ml-2 mt-2 pl-4 border-l border-slate-800 space-y-2">
                                    @foreach($cat->children as $child)
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="radio" name="category" value="{{ $child->slug }}" class="hidden peer" {{ request('category') == $child->slug ? 'checked' : '' }}>
                                        <div class="w-3 h-3 rounded-full border border-slate-600 peer-checked:bg-neon peer-checked:border-neon transition-all"></div>
                                        <span class="text-xs text-slate-400 group-hover:text-white transition-colors peer-checked:text-white peer-checked:font-bold">{{ $child->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="mb-6 border-b border-slate-800 pb-6">
                        <label class="block text-slate-400 text-xs font-bold uppercase mb-2">Price Range</label>
                        <div class="flex items-center gap-2 mb-2">
                            <input type="number" name="price_min" placeholder="Min" value="{{ request('price_min') }}" class="w-full bg-slate-950 border border-slate-700 text-white rounded-lg px-3 py-2 text-xs focus:border-neon focus:outline-none">
                            <span class="text-slate-500">-</span>
                            <input type="number" name="price_max" placeholder="Max" value="{{ request('price_max') }}" class="w-full bg-slate-950 border border-slate-700 text-white rounded-lg px-3 py-2 text-xs focus:border-neon focus:outline-none">
                        </div>
                    </div>

                    <!-- Condition -->
                    <div class="mb-6 border-b border-slate-800 pb-6">
                        <label class="block text-slate-400 text-xs font-bold uppercase mb-2">Condition</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="condition" value="" class="hidden peer" {{ !request('condition') ? 'checked' : '' }}>
                                <span class="text-sm text-slate-400 group-hover:text-white transition-colors peer-checked:text-neon peer-checked:font-bold">Any</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="condition" value="new" class="hidden peer" {{ request('condition') == 'new' ? 'checked' : '' }}>
                                <span class="text-sm text-slate-400 group-hover:text-white transition-colors peer-checked:text-neon peer-checked:font-bold">New</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="radio" name="condition" value="used" class="hidden peer" {{ request('condition') == 'used' ? 'checked' : '' }}>
                                <span class="text-sm text-slate-400 group-hover:text-white transition-colors peer-checked:text-neon peer-checked:font-bold">Used</span>
                            </label>
                        </div>
                    </div>

                    <!-- Brands -->
                    <div class="mb-6 border-b border-slate-800 pb-6">
                        <label class="block text-slate-400 text-xs font-bold uppercase mb-2">Brand</label>
                        <select name="brand" id="brand-select" class="w-full bg-slate-950 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:border-neon focus:outline-none">
                            <option value="">All Brands</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- City -->
                    <div class="mb-6 border-b border-slate-800 pb-6">
                        <label class="block text-slate-400 text-xs font-bold uppercase mb-2">Seller Location</label>
                        <select name="city" class="w-full bg-slate-950 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:border-neon focus:outline-none">
                            <option value="">All Cities</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Size -->
                    <div class="mb-6">
                        <label class="block text-slate-400 text-xs font-bold uppercase mb-2">Size</label>
                        <input type="text" name="size" value="{{ request('size') }}" placeholder="e.g. 42, M, L" class="w-full bg-slate-950 border border-slate-700 text-white rounded-lg px-3 py-2 text-sm focus:border-neon focus:outline-none">
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 bg-neon text-dark font-bold py-2 rounded-lg hover:bg-neon/90 transition-colors text-sm">Apply</button>
                        <a href="{{ route('marketplace.index') }}" class="px-4 py-2 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition-colors text-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="lg:col-span-3">
            <!-- Sort & Results Count -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-slate-400 text-sm">
                    Showing <span class="text-white font-bold">{{ $products->count() }}</span> items
                </div>
                <select id="sort-select" class="bg-slate-900 border border-slate-700 text-white rounded-lg px-3 py-1.5 text-sm focus:border-neon focus:outline-none">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Newest Listed</option>
                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                </select>
            </div>

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
    const sortSelect = document.getElementById('sort-select');
    const gridContainer = document.getElementById('product-grid-container');
    const brandSelect = document.getElementById('brand-select');
    const categoryInputs = document.querySelectorAll('input[name="category"]');
    let searchTimeout;

    // Brand Filtering Logic
    function filterBrands() {
        const selectedCategoryInput = document.querySelector('input[name="category"]:checked');
        const selectedCategory = selectedCategoryInput ? selectedCategoryInput.value : '';
        
        // Reset brand selection if it's not valid for the new category
        // (Optional: keep if valid, but for now let's keep it simple)
        // Actually, better UX is to keep it if it's still visible
        
        const options = brandSelect.querySelectorAll('option');
        let anyVisible = false;

        options.forEach(option => {
            if (option.value === "") return; // Skip "All Brands"

            const categories = JSON.parse(option.dataset.categories || '[]');
            
            // Show if no category selected, or if brand belongs to selected category
            if (!selectedCategory || categories.includes(selectedCategory)) {
                option.hidden = false;
                option.disabled = false;
                anyVisible = true;
            } else {
                option.hidden = true;
                option.disabled = true;
                // If this hidden option was selected, deselect it
                if (option.selected) {
                    brandSelect.value = "";
                }
            }
        });
    }

    // Initial filter
    filterBrands();

    // Listen for category changes
    categoryInputs.forEach(input => {
        input.addEventListener('change', filterBrands);
    });

    // Function to fetch products via AJAX
    function fetchProducts() {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        
        // Add sort
        params.append('sort', sortSelect.value);

        // Show loading state
        gridContainer.style.opacity = '0.5';

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

    // Event Listeners
    
    // Input changes (Text inputs with debounce)
    const textInputs = filterForm.querySelectorAll('input[type="text"], input[type="number"]');
    textInputs.forEach(input => {
        input.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(fetchProducts, 500);
        });
    });

    // Radio, Checkbox, Select changes
    const changeInputs = filterForm.querySelectorAll('input[type="radio"], input[type="checkbox"], select');
    changeInputs.forEach(input => {
        input.addEventListener('change', fetchProducts);
    });

    // Sort change
    sortSelect.addEventListener('change', fetchProducts);

    // Form submit (prevent default and fetch)
    filterForm.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchProducts();
    });

    // Handle Pagination Clicks via AJAX
    gridContainer.addEventListener('click', (e) => {
        if (e.target.tagName === 'A' && e.target.closest('.pagination-container')) {
            e.preventDefault();
            const url = e.target.getAttribute('href');
            
            gridContainer.style.opacity = '0.5';
            
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
