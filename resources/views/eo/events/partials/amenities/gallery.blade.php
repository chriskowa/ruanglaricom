<div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-3 cursor-pointer select-none w-full">
            <div class="relative">
                <input type="checkbox" name="premium_amenities[gallery][enabled]" value="1" x-model="amenities.gallery" class="peer sr-only">
                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </div>
            <span class="font-bold text-white">Event Gallery</span>
        </label>
    </div>

    <div x-show="amenities.gallery" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mt-4 border-t border-slate-700 pt-4">
        <p class="text-sm text-slate-400 mb-4">Upload foto-foto highlight event (Max 2MB per foto).</p>
        
        <!-- Upload Box -->
        <div class="border-2 border-dashed border-slate-700 rounded-xl p-6 text-center hover:border-yellow-400 transition-colors cursor-pointer relative mb-4">
            <input type="file" name="gallery[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
            <div class="pointer-events-none">
                <svg class="w-10 h-10 text-slate-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <p class="text-sm text-slate-400">Click to upload multiple images</p>
            </div>
        </div>

        <!-- Existing Images (Edit Mode) -->
        @if(isset($event) && $event->gallery)
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($event->gallery as $image)
                    <div class="relative group aspect-video bg-slate-900 rounded-lg overflow-hidden border border-slate-700">
                        <img src="{{ asset('storage/'.$image) }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <label class="cursor-pointer text-red-400 hover:text-red-300 flex items-center gap-2">
                                <input type="checkbox" name="remove_gallery_images[]" value="{{ $image }}" class="w-4 h-4 rounded border-red-400 bg-transparent text-red-500 focus:ring-red-500">
                                <span class="text-sm font-bold">Delete</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
