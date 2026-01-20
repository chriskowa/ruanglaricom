@forelse($media as $item)
    <div class="relative group aspect-square bg-slate-800 rounded-xl overflow-hidden border border-slate-700 hover:border-neon transition-colors cursor-pointer media-item"
         data-id="{{ $item->id }}"
         data-url="{{ $item->url }}"
         data-filename="{{ $item->filename }}">
        
        <div class="absolute top-2 left-2 z-10">
            <span class="bg-blue-600 text-white text-[10px] px-1.5 py-0.5 rounded shadow">Cloudinary</span>
        </div>

        <img src="{{ $item->url }}" class="w-full h-full object-cover">

        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-end p-2">
            <div class="text-xs text-white truncate px-1">
                {{ $item->filename }}
            </div>
        </div>
    </div>
@empty
    <div class="col-span-full py-12 text-center text-slate-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
        <p>No images found in Cloudinary</p>
    </div>
@endforelse
