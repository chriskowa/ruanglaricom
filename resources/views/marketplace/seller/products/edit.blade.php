@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Edit Product')

@section('content')
<div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto min-h-screen">
    <!-- Breadcrumb -->
    <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('marketplace.index') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                    Marketplace
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <a href="{{ route('marketplace.seller.products.index') }}" class="ml-1 text-sm font-medium text-slate-400 hover:text-white md:ml-2">My Products</a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                    <span class="ml-1 text-sm font-medium text-slate-500 md:ml-2">Edit Product</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-slate-800">
            <h2 class="text-3xl font-black italic text-white uppercase tracking-wider">
                EDIT <span class="text-neon">PRODUCT</span>
            </h2>
            <p class="text-slate-400 mt-2">Update your product details</p>
        </div>

        <div class="p-8">
            @if ($errors->any())
                <div class="bg-red-900/50 border border-red-700 rounded-xl p-4 mb-6">
                    <ul class="space-y-2 text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('marketplace.seller.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Product Title</label>
                    <input type="text" name="title" value="{{ old('title', $product->title) }}" required 
                        class="w-full bg-slate-800 border @error('title') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Category</label>
                        <div class="relative">
                            <select name="category_id" id="category-select" required 
                                class="w-full bg-slate-800 border @error('category_id') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                <option value="" disabled>Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" data-slug="{{ $category->slug }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Brand -->
                    <div>
                        <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Brand</label>
                        <div class="relative">
                            <select name="brand_id" id="brand-select"
                                class="w-full bg-slate-800 border @error('brand_id') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                <option value="" selected>Select Brand (Optional)</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                        @error('brand_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Type (Disabled) -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Type</label>
                    <input type="text" value="{{ ucfirst(str_replace('_', ' ', $product->type)) }}" disabled 
                        class="w-full bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-400 cursor-not-allowed">
                </div>

                @php
                    $hasBids = $product->sale_type === 'auction' ? $product->bids()->exists() : false;
                    $canSwitchFromConsignment = in_array($product->consignment_status, ['none', 'requested'], true);
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-6 bg-slate-800/30 rounded-xl border border-slate-700">
                        <div class="text-xs text-slate-500 uppercase tracking-widest mb-2">Mode Penjualan</div>
                        <div class="text-white font-bold">
                            {{ $product->sale_type === 'auction' ? 'Lelang (Bidding)' : 'Jual Normal' }}
                        </div>
                    </div>

                    <div class="p-6 bg-slate-800/30 rounded-xl border border-slate-700">
                        <div class="text-xs text-slate-500 uppercase tracking-widest mb-3">Fulfillment</div>
                        <div class="flex flex-col gap-3">
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" class="form-radio text-neon focus:ring-neon bg-slate-800 border-slate-600" name="fulfillment_mode" value="self_ship"
                                    {{ old('fulfillment_mode', $product->fulfillment_mode) === 'self_ship' ? 'checked' : '' }}
                                    {{ ($product->fulfillment_mode === 'consignment' && ! $canSwitchFromConsignment) ? 'disabled' : '' }}>
                                <span class="ml-2 {{ ($product->fulfillment_mode === 'consignment' && ! $canSwitchFromConsignment) ? 'text-slate-500' : 'text-white group-hover:text-neon transition-colors' }}">Kirim Sendiri</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer group">
                                <input type="radio" class="form-radio text-neon focus:ring-neon bg-slate-800 border-slate-600" name="fulfillment_mode" value="consignment"
                                    {{ old('fulfillment_mode', $product->fulfillment_mode) === 'consignment' ? 'checked' : '' }}>
                                <span class="ml-2 text-white group-hover:text-neon transition-colors">Titip Jual</span>
                            </label>
                        </div>
                        @if($product->fulfillment_mode === 'consignment')
                            <div class="mt-3 text-xs text-slate-400">
                                Status titip jual: <span class="font-bold text-white">{{ strtoupper($product->consignment_status) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Price & Stock -->
                @if($product->sale_type === 'fixed')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Price (Rp)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-slate-400 font-bold">Rp</span>
                                </div>
                                <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" 
                                    class="w-full bg-slate-800 border @error('price') border-red-500 @else border-slate-700 @enderror rounded-xl pl-12 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                            </div>
                            @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Stock</label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" 
                                class="w-full bg-slate-800 border @error('stock') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                            @error('stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @else
                    <div class="p-6 bg-slate-800/30 rounded-xl border border-slate-700 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Starting Price</div>
                                <div class="text-white font-bold">Rp {{ number_format($product->starting_price ?? $product->price, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Current Bid</div>
                                <div class="text-white font-bold">Rp {{ number_format($product->current_price ?? $product->starting_price ?? $product->price, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Auction Ends At</label>
                                <input type="datetime-local" name="auction_end_at"
                                    value="{{ old('auction_end_at', optional($product->auction_end_at)->format('Y-m-d\\TH:i')) }}"
                                    {{ $hasBids ? 'disabled' : '' }}
                                    class="w-full bg-slate-800 border @error('auction_end_at') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all {{ $hasBids ? 'opacity-60 cursor-not-allowed' : '' }}">
                                @error('auction_end_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Buy Now Price</label>
                                <input type="number" name="buy_now_price" min="0" value="{{ old('buy_now_price', $product->buy_now_price) }}"
                                    {{ $hasBids ? 'disabled' : '' }}
                                    class="w-full bg-slate-800 border @error('buy_now_price') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all {{ $hasBids ? 'opacity-60 cursor-not-allowed' : '' }}">
                                @error('buy_now_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Reserve Price</label>
                            <input type="number" name="reserve_price" min="0" value="{{ old('reserve_price', $product->reserve_price) }}"
                                {{ $hasBids ? 'disabled' : '' }}
                                class="w-full bg-slate-800 border @error('reserve_price') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all {{ $hasBids ? 'opacity-60 cursor-not-allowed' : '' }}">
                            @error('reserve_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        @if($hasBids)
                            <div class="text-xs text-slate-500">Auction settings terkunci karena sudah ada bid.</div>
                        @endif
                    </div>
                @endif

                <div id="consignment-fields" class="p-6 bg-slate-800/30 rounded-xl border border-slate-700 {{ old('fulfillment_mode', $product->fulfillment_mode) === 'consignment' ? '' : 'hidden' }} space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Dropoff Method</label>
                            <input type="text" name="dropoff_method" value="{{ old('dropoff_method', optional($product->consignmentIntake)->dropoff_method) }}"
                                class="w-full bg-slate-800 border @error('dropoff_method') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                placeholder="Dropoff / Pickup">
                            @error('dropoff_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Dropoff Location</label>
                            <input type="text" name="dropoff_location" value="{{ old('dropoff_location', optional($product->consignmentIntake)->dropoff_location) }}"
                                class="w-full bg-slate-800 border @error('dropoff_location') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                placeholder="Kota / titik temu">
                            @error('dropoff_location') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Condition -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Condition</label>
                    <div class="flex gap-6 mt-2">
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" class="form-radio text-neon focus:ring-neon bg-slate-800 border-slate-600" name="condition" value="new" {{ old('condition', $product->condition) == 'new' ? 'checked' : '' }}>
                            <span class="ml-2 text-white group-hover:text-neon transition-colors">New</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" class="form-radio text-neon focus:ring-neon bg-slate-800 border-slate-600" name="condition" value="used" {{ old('condition', $product->condition) == 'used' ? 'checked' : '' }}>
                            <span class="ml-2 text-white group-hover:text-neon transition-colors">Used / Pre-loved</span>
                        </label>
                    </div>
                    @error('condition') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Description</label>
                    <textarea name="description" rows="5" required 
                        class="w-full bg-slate-800 border @error('description') border-red-500 @else border-slate-700 @enderror rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">{{ old('description', $product->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Image -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Update Image (Optional)</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 @error('image') border-red-500 @else border-slate-700 @enderror border-dashed rounded-xl cursor-pointer bg-slate-800/50 hover:bg-slate-800 hover:border-neon/50 transition-all group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-slate-400 group-hover:text-neon transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="mb-2 text-sm text-slate-400"><span class="font-bold text-white group-hover:text-neon transition-colors">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-slate-500">SVG, PNG, JPG or GIF (MAX. 2MB)</p>
                            </div>
                            <input id="dropzone-file" name="image" type="file" accept="image/*" class="hidden" onchange="previewImage(this)" />
                        </label>
                    </div>
                    @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    
                    <div id="image-preview" class="mt-4 {{ $product->primaryImage ? '' : 'hidden' }}">
                        @if($product->primaryImage)
                            <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" class="h-32 rounded-lg border border-slate-700 object-cover">
                        @else
                            <img src="" class="h-32 rounded-lg border border-slate-700 object-cover hidden">
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6 border-t border-slate-800">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-600/20 transition-all flex items-center gap-2">
                        <span>UPDATE PRODUCT</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category-select');
        const brandSelect = document.getElementById('brand-select');
        const brandOptions = Array.from(brandSelect.querySelectorAll('option'));

        function filterBrands() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const selectedCategorySlug = selectedOption ? selectedOption.dataset.slug : null;
            const currentBrand = brandSelect.value;
            let isCurrentValid = false;

            brandOptions.forEach(option => {
                if (option.value === "") return; // Skip default option

                const categories = JSON.parse(option.dataset.categories || '[]');
                
                if (!selectedCategorySlug || categories.includes(selectedCategorySlug)) {
                    option.hidden = false;
                    option.disabled = false;
                    if (option.value == currentBrand) isCurrentValid = true;
                } else {
                    option.hidden = true;
                    option.disabled = true;
                }
            });

            if (!isCurrentValid && currentBrand !== "") {
                 brandSelect.value = "";
            }
        }

        categorySelect.addEventListener('change', filterBrands);
        
        // Run once on load
        filterBrands();

        const radios = document.querySelectorAll('input[name="fulfillment_mode"]');
        const consignmentFields = document.getElementById('consignment-fields');
        radios.forEach(r => {
            r.addEventListener('change', function() {
                if (this.value === 'consignment') consignmentFields.classList.remove('hidden');
                else consignmentFields.classList.add('hidden');
            });
        });
    });

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    let img = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (!img) {
                img = document.createElement('img');
                img.className = "h-32 rounded-lg border border-slate-700 object-cover";
                preview.appendChild(img);
            }
            img.src = e.target.result;
            img.classList.remove('hidden');
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
