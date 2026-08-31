<!-- GPX Cards Grid & Pagination Partial -->
@if($items->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($items as $item)
            <article class="group bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-lg p-4 transition duration-200 flex flex-col justify-between">
                
                <div>
                    <!-- SVG Route Thumbnail -->
                    <div class="relative mb-3 rounded-md overflow-hidden border border-slate-800 aspect-[16/9] gpx-svg-grid flex items-center justify-center bg-slate-950">
                        <svg id="gpx-svg-{{ $item->id }}" 
                             class="gpx-route-svg w-full h-full p-3 transition-transform duration-300 group-hover:scale-[1.02]" 
                             viewBox="0 0 320 180" 
                             data-coords="{{ json_encode($item->coordinates_json ?? []) }}">
                            <circle cx="160" cy="90" r="45" fill="none" stroke="rgba(255,255,255,0.03)" stroke-dasharray="3 3"/>
                        </svg>
                        
                        @if($item->city)
                            <div class="absolute top-2.5 left-2.5 z-10 px-2 py-0.5 rounded bg-slate-900 border border-slate-750 text-xs text-slate-200 font-medium flex items-center gap-1">
                                <span>{{ $item->city }}</span>
                            </div>
                        @endif

                        @if(isset($item->user_distance_km))
                            <div class="absolute top-2.5 left-1/2 -translate-x-1/2 z-10 px-2 py-0.5 rounded bg-slate-900 border border-slate-750 text-xs font-medium text-slate-200 font-mono flex items-center gap-1.5 whitespace-nowrap">
                                <span>{{ number_format($item->user_distance_km, 1) }} km dari Anda</span>
                            </div>
                        @endif

                        <div class="absolute top-2.5 right-2.5 z-10 flex items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-750 text-xs font-semibold text-slate-300 uppercase font-mono">
                                {{ $item->route_type_label }}
                            </span>
                        </div>
                    </div>

                    <h2 class="text-sm font-semibold text-white group-hover:text-neon transition-colors line-clamp-1 mb-2">
                        <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}">
                            {{ $item->title }}
                        </a>
                    </h2>

                    <!-- Metric Stats -->
                    <div class="grid grid-cols-3 gap-2 text-center mb-3">
                        <div class="bg-slate-950 p-2 rounded-md border border-slate-800">
                            <div class="text-[11px] text-slate-400">Jarak</div>
                            <div class="text-xs font-semibold text-white mt-0.5 font-mono">
                                {{ $item->distance_km ? number_format($item->distance_km, 2) . ' km' : '-' }}
                            </div>
                        </div>
                        <div class="bg-slate-950 p-2 rounded-md border border-slate-800">
                            <div class="text-[11px] text-slate-400">Elev Gain</div>
                            <div class="text-xs font-semibold text-white mt-0.5 font-mono">
                                +{{ round($item->elevation_gain_m ?? 0) }} m
                            </div>
                        </div>
                        <div class="bg-slate-950 p-2 rounded-md border border-slate-800">
                            <div class="text-[11px] text-slate-400">Elev Loss</div>
                            <div class="text-xs font-semibold text-white mt-0.5 font-mono">
                                -{{ round($item->elevation_loss_m ?? 0) }} m
                            </div>
                        </div>
                    </div>

                    @if($item->notes)
                        <p class="text-xs text-slate-300 line-clamp-2 mb-3 bg-slate-950 p-2.5 rounded-md border border-slate-800 leading-relaxed">
                            "{{ $item->notes }}"
                        </p>
                    @endif
                </div>

                <!-- Footer Card Actions -->
                <div class="pt-3 border-t border-slate-800 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-6 h-6 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-slate-200 shrink-0">
                            {{ strtoupper(substr($item->user?->name ?? 'R', 0, 1)) }}
                        </div>
                        <div class="text-xs text-slate-400 truncate">
                            {{ $item->user?->name ?? 'Ruang Lari' }}
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        <button type="button" 
                                onclick="openSuggestTitleModal({{ $item->id }}, '{{ addslashes($item->title) }}', '{{ addslashes($item->city ?? 'Indonesia') }}')"
                                class="px-2 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-medium transition" 
                                title="Sarankan Nama Rute">
                            Saran Nama
                        </button>
                        <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}" class="px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-medium transition">
                            Detail
                        </a>
                        <a href="{{ route('gpx.download', $item) }}" class="px-2.5 py-1 rounded-md bg-neon text-dark hover:bg-white text-xs font-semibold transition">
                            Unduh
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-8 gpx-pagination-wrap">
        {{ $items->links() }}
    </div>
@else
    <div class="bg-slate-900 border border-slate-800 rounded-lg p-10 text-center max-w-lg mx-auto">
        <h3 class="text-base font-semibold text-white">Belum Ada Rute yang Sesuai</h3>
        <p class="text-slate-400 text-xs mt-1.5 leading-relaxed">
            Coba sesuaikan kata kunci pencarian, pilihan kota, atau filter jarak untuk menemukan rute lainnya.
        </p>
        <div class="mt-5">
            <button type="button" id="btn-reset-empty-filters" class="px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition border border-slate-700">
                Reset Semua Filter
            </button>
        </div>
    </div>
@endif
