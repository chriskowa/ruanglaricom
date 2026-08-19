<!-- GPX Cards Grid & Pagination Partial -->
@if($items->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($items as $item)
            <article class="group bg-[#0c121e] border border-slate-800 hover:border-slate-600 rounded-2xl p-4 transition duration-200 flex flex-col justify-between">
                
                <div>
                    <div class="relative mb-3 rounded-xl overflow-hidden border border-slate-800 aspect-[16/9] gpx-svg-grid flex items-center justify-center">
                        <svg id="gpx-svg-{{ $item->id }}" 
                             class="gpx-route-svg w-full h-full p-3 transition-transform duration-300 group-hover:scale-[1.03]" 
                             viewBox="0 0 320 180" 
                             data-coords="{{ json_encode($item->coordinates_json ?? []) }}">
                            <circle cx="160" cy="90" r="45" fill="none" stroke="rgba(255,255,255,0.03)" stroke-dasharray="3 3"/>
                        </svg>
                        
                        @if($item->city)
                            <div class="absolute top-2.5 left-2.5 z-10 px-2.5 py-1 rounded-md bg-slate-950/80 backdrop-blur border border-slate-800 text-xs text-slate-200 flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-accent text-[10px]"></i>
                                <span>{{ $item->city }}</span>
                            </div>
                        @endif

                        @if(isset($item->user_distance_km))
                            <div class="absolute top-2.5 left-1/2 -translate-x-1/2 z-10 px-2.5 py-1 rounded-md bg-slate-950/80 backdrop-blur border border-slate-800 text-[11px] font-medium text-white flex items-center gap-1.5 whitespace-nowrap shadow-sm">
                                <svg class="w-3 h-3 text-white shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="22" y1="12" x2="18" y2="12"></line>
                                    <line x1="6" y1="12" x2="2" y2="12"></line>
                                    <line x1="12" y1="6" x2="12" y2="2"></line>
                                    <line x1="12" y1="22" x2="12" y2="18"></line>
                                </svg>
                                <span>{{ number_format($item->user_distance_km, 1) }} km dari Anda</span>
                            </div>
                        @endif

                        <div class="absolute top-2.5 right-2.5 z-10 flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-md bg-slate-950/85 backdrop-blur border border-slate-750 text-[11px] font-semibold text-slate-200 uppercase tracking-wider font-mono">
                                {{ $item->route_type_label }}
                            </span>
                        </div>
                    </div>

                    <h2 class="text-base font-semibold text-white group-hover:text-accent transition-colors line-clamp-1 mb-2">
                        <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}">
                            {{ $item->title }}
                        </a>
                    </h2>

                    <div class="grid grid-cols-3 gap-2 text-center mb-3">
                        <div class="bg-[#090D16] p-2.5 rounded-xl border border-slate-800">
                            <div class="text-[11px] text-slate-200">Jarak</div>
                            <div class="text-sm font-semibold text-white mt-0.5 font-mono">
                                {{ $item->distance_km ? number_format($item->distance_km, 2) . ' km' : '-' }}
                            </div>
                        </div>
                        <div class="bg-[#090D16] p-2.5 rounded-xl border border-slate-800">
                            <div class="text-[11px] text-slate-200">Elev gain</div>
                            <div class="text-sm font-semibold text-white mt-0.5 font-mono">
                                +{{ round($item->elevation_gain_m ?? 0) }} m
                            </div>
                        </div>
                        <div class="bg-[#090D16] p-2.5 rounded-xl border border-slate-800">
                            <div class="text-[11px] text-slate-200">Elev loss</div>
                            <div class="text-sm font-semibold text-white mt-0.5 font-mono">
                                -{{ round($item->elevation_loss_m ?? 0) }} m
                            </div>
                        </div>
                    </div>

                    @if($item->notes)
                        <p class="text-xs text-slate-200 line-clamp-2 mb-3 italic bg-[#090D16]/60 p-2.5 rounded-xl border border-slate-800/60">
                            "{{ $item->notes }}"
                        </p>
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-800 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-[10px] font-medium text-slate-300">
                            {{ strtoupper(substr($item->user?->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="text-xs text-slate-200 line-clamp-1 max-w-[100px]">
                            {{ $item->user?->name ?? 'Ruang Lari' }}
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs transition flex items-center gap-1">
                            <span>Detail</span>
                        </a>
                        <a href="{{ route('gpx.download', $item) }}" class="px-2.5 py-1.5 rounded-lg bg-accent text-slate-950 hover:bg-white text-xs font-bold transition flex items-center gap-1 shadow-sm">
                            <i class="fa-solid fa-download text-[10px] text-slate-950"></i>
                            <span>Unduh</span>
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-10 gpx-pagination-wrap">
        {{ $items->links() }}
    </div>
@else
    <div class="bg-[#0c121e] border border-slate-800 rounded-2xl p-12 text-center max-w-xl mx-auto">
        <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-200 mb-4 text-xl">
            <i class="fa-solid fa-route"></i>
        </div>
        <h3 class="text-lg font-semibold text-white">Belum ada rute yang cocok</h3>
        <p class="text-slate-200 text-sm mt-2">
            Coba ubah filter kata kunci, kota atau jarak untuk menemukan rute lainnya.
        </p>
        <div class="mt-6">
            <button type="button" id="btn-reset-empty-filters" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium transition cursor-pointer">
                Reset semua filter
            </button>
        </div>
    </div>
@endif
