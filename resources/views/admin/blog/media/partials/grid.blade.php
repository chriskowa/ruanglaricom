@forelse($media as $item)
    <div class="relative group aspect-square bg-slate-800 rounded-xl overflow-hidden border border-slate-700 hover:border-neon transition-colors cursor-pointer media-item"
         data-id="{{ $item->id }}"
         data-url="{{ $item->url }}"
         data-filename="{{ $item->filename }}">
        
        @if(str_starts_with($item->mime_type, 'image/'))
            <img src="{{ $item->url }}" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full flex flex-col items-center justify-center text-slate-500">
                <svg class="w-12 h-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span class="text-xs uppercase font-bold">{{ pathinfo($item->filename, PATHINFO_EXTENSION) }}</span>
            </div>
        @endif

        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col justify-between p-2">
            <div class="flex justify-end">
                <button onclick="deleteMedia({{ $item->id }}, event)" class="p-1.5 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                </button>
            </div>
            <div class="text-xs text-white truncate px-1">
                {{ $item->filename }}
            </div>
        </div>
    </div>
@empty
    <div class="col-span-full py-12 text-center text-slate-500">
        <svg class="w-16 h-16 mx-auto mb-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        <p>No media files found</p>
    </div>
@endforelse
