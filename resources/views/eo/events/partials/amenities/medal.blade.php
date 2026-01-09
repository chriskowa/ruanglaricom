<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none w-full">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[medal][enabled]" value="1" x-model="amenities.medal" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <span class="font-bold text-white">Medal</span>
        </label>
    </div>

    <div x-show="amenities.medal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4">
        <label class="block text-sm font-medium text-slate-300 mb-2">Medal Design</label>
        <div class="border-2 border-dashed border-slate-700 rounded-xl p-6 text-center hover:border-yellow-400 transition-colors cursor-pointer relative" onclick="document.getElementById('medal_image').click()">
            <input type="file" name="medal_image" id="medal_image" class="hidden" accept="image/*" onchange="previewImage(this, 'medal_preview')">
            
            <div id="medal_preview" class="{{ isset($event) && $event->medal_image ? '' : 'hidden' }} mb-2">
                <img src="{{ isset($event) && $event->medal_image ? asset('storage/' . $event->medal_image) : '' }}" class="max-h-40 mx-auto rounded-lg shadow-lg">
            </div>
            
            <div id="medal_placeholder" class="{{ isset($event) && $event->medal_image ? 'hidden' : '' }}">
                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <p class="text-sm text-slate-400">Click to upload Medal Design</p>
            </div>
        </div>
    </div>
</div>
