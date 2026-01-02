@extends('layouts.pacerhub')
@php($withSidebar = true)

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
                    <span class="ml-1 text-sm font-medium text-slate-500 md:ml-2">List New Product</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="bg-slate-900/80 backdrop-blur-md rounded-2xl border border-slate-800 shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-slate-800">
            <h2 class="text-3xl font-black italic text-white uppercase tracking-wider">
                LIST NEW <span class="text-neon">PRODUCT</span>
            </h2>
            <p class="text-slate-400 mt-2">Share your gear with the community</p>
        </div>

        <div class="p-8">
            <form action="{{ route('marketplace.seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Title -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Product Title</label>
                    <input type="text" name="title" required 
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                        placeholder="e.g. Nike Vaporfly Next% 2">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Category</label>
                        <div class="relative">
                            <select name="category_id" id="category-select" required 
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                <option value="" disabled selected>Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" data-slug="{{ $category->slug }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Brand -->
                    <div>
                        <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Brand</label>
                        <div class="relative">
                            <select name="brand_id" id="brand-select"
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                <option value="" selected>Select Brand (Optional)</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" data-categories="{{ json_encode($brand->categories->pluck('slug')->toArray()) }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Type</label>
                        <div class="relative">
                            <select name="type" id="type-select" onchange="toggleType(this.value)" 
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white appearance-none focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                                <option value="physical">Physical Item (Gear)</option>
                                <option value="digital_slot">Race Slot / Ticket</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price & Stock -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Price (Rp)</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-slate-400 font-bold">Rp</span>
                            </div>
                            <input type="number" name="price" required min="0" 
                                class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-12 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                                placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Stock</label>
                        <input type="number" name="stock" required min="1" value="1" 
                            class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all">
                    </div>
                </div>

                <!-- Condition -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Condition</label>
                    <div class="flex gap-6 mt-2">
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" class="form-radio text-neon focus:ring-neon bg-slate-800 border-slate-600" name="condition" value="new" checked>
                            <span class="ml-2 text-white group-hover:text-neon transition-colors">New</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer group">
                            <input type="radio" class="form-radio text-neon focus:ring-neon bg-slate-800 border-slate-600" name="condition" value="used">
                            <span class="ml-2 text-white group-hover:text-neon transition-colors">Used / Pre-loved</span>
                        </label>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Description</label>
                    <textarea name="description" rows="5" required 
                        class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all"
                        placeholder="Describe your product in detail..."></textarea>
                </div>

                <!-- Image -->
                <div>
                    <label class="block text-slate-300 text-sm font-bold mb-2 uppercase tracking-wide">Primary Image</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-700 border-dashed rounded-xl cursor-pointer bg-slate-800/50 hover:bg-slate-800 hover:border-neon/50 transition-all group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-slate-400 group-hover:text-neon transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="mb-2 text-sm text-slate-400"><span class="font-bold text-white group-hover:text-neon transition-colors">Click to upload</span> or drag and drop</p>
                                <p class="text-xs text-slate-500">SVG, PNG, JPG or GIF (MAX. 2MB)</p>
                            </div>
                            <input id="dropzone-file" name="image" type="file" required accept="image/*" class="hidden" onchange="previewImage(this)" />
                        </label>
                    </div>
                    <div id="image-preview" class="mt-4 hidden">
                        <img src="" class="h-32 rounded-lg border border-slate-700">
                    </div>
                </div>

                <!-- Slot Specific Fields -->
                <div id="slot-fields" style="display: none;" class="p-6 bg-slate-800/50 rounded-xl border border-slate-700">
                    <h3 class="font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                        Race Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Race Name</label>
                            <input type="text" name="meta_data[race_name]" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-neon transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">Race Date</label>
                            <input type="date" name="meta_data[race_date]" 
                                class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-neon transition-colors">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 mb-1 uppercase">BIB Transfer Policy</label>
                        <input type="text" name="meta_data[transfer_policy]" placeholder="e.g. Can change name until H-7" 
                            class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-neon transition-colors">
                    </div>
                </div>

                <div class="flex items-center justify-end pt-6 border-t border-slate-800">
                    <button type="submit" class="bg-neon text-slate-900 font-black text-lg py-3 px-8 rounded-xl shadow-lg shadow-neon/20 hover:bg-neon/90 hover:scale-[1.02] transition-all flex items-center gap-2">
                        <span>LIST PRODUCT</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleType(val) {
    const slotFields = document.getElementById('slot-fields');
    if(val === 'digital_slot') {
        slotFields.style.display = 'block';
    } else {
        slotFields.style.display = 'none';
    }
}

function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const img = preview.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category-select');
        const brandSelect = document.getElementById('brand-select');
        const brandOptions = Array.from(brandSelect.querySelectorAll('option'));

        function filterBrands() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const selectedCategorySlug = selectedOption.dataset.slug;
            
            // Reset brand selection
            brandSelect.value = "";

            brandOptions.forEach(option => {
                if (option.value === "") return; // Skip default option

                const categories = JSON.parse(option.dataset.categories || '[]');
                
                if (!selectedCategorySlug || categories.includes(selectedCategorySlug)) {
                    option.hidden = false;
                    option.disabled = false;
                } else {
                    option.hidden = true;
                    option.disabled = true;
                }
            });
        }

        categorySelect.addEventListener('change', filterBrands);
        
        // Run once on load
        filterBrands();
    });
</script>
@endpush
