<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none w-full">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[jersey][enabled]" value="1" x-model="amenities.jersey" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <span class="font-bold text-white">Official Jersey</span>
        </label>
    </div>

    <div x-show="amenities.jersey" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4 space-y-4">
        
        <!-- Jersey Design & Size Chart -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Desain Jersey (Mockup)</label>
                <div id="jersey-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors">
                    <div class="dz-message text-center py-6">
                        <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <p class="text-sm text-slate-400 font-medium">Desain Mockup Kaos</p>
                        <p class="text-xs text-slate-500 mt-1">Tampak depan / belakang</p>
                    </div>
                </div>
                <input type="hidden" name="jersey_image" id="jersey_image_input" value="{{ $event->jersey_image ?? '' }}">
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-slate-300">Panduan Ukuran (Size Chart Vendor)</label>
                    <span class="text-xs text-slate-400">Opsional</span>
                </div>
                <div id="jersey-chart-dropzone" class="dropzone bg-slate-900 border-2 border-dashed border-slate-700 rounded-xl hover:border-yellow-400 transition-colors">
                    <div class="dz-message text-center py-6">
                        <svg class="w-8 h-8 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <p class="text-sm text-slate-400 font-medium">Bagan Ukuran dari Vendor</p>
                        <p class="text-xs text-slate-500 mt-1">Tabel cm lebar/panjang kaos</p>
                    </div>
                </div>
                <input type="hidden" name="jersey_chart_image" id="jersey_chart_image_input" value="{{ $event->jersey_chart_image ?? '' }}">
                <p class="text-[11px] text-slate-400 mt-1.5 leading-tight">Jika tidak diunggah, tombol "Panduan Ukuran" pada form pendaftaran otomatis menggunakan bagan standar RuangLari.</p>
            </div>
        </div>

        <!-- Sizes & Stock Quota -->
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Available Sizes & Stock Quota</label>
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                @php
                    $sizes = ['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];
                    $currentSizes = old('jersey_sizes', isset($event) ? $event->jersey_sizes : []);
                    if(!is_array($currentSizes)) $currentSizes = [];

                    $currentStock = [];
                    if (isset($event) && $event->jerseyStock) {
                        foreach ($sizes as $size) {
                            $col = strtolower($size);
                            $currentStock[$size] = $event->jerseyStock->$col;
                        }
                    }
                    $currentStock = old('jersey_stock', $currentStock);
                @endphp
                @foreach($sizes as $size)
                    <div class="bg-slate-900 border border-slate-700 rounded-lg p-3 flex flex-col gap-2 hover:border-slate-600 transition-colors">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="jersey_sizes[]" value="{{ $size }}" {{ in_array($size, $currentSizes) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-yellow-500 focus:ring-yellow-500/50">
                            <span class="text-sm font-bold text-white">{{ $size }}</span>
                        </label>
                        <div>
                            <input type="number" name="jersey_stock[{{ $size }}]" value="{{ $currentStock[$size] ?? '' }}" placeholder="Unlimited" class="w-full bg-slate-800 border border-slate-700 rounded px-2 py-1 text-white text-xs focus:border-yellow-400 focus:outline-none" min="0">
                            <span class="text-[10px] text-slate-500">Kuota / Stok</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
