@forelse($events as $event)
@php
    $regStatus = 'Ditutup';
    $regClass = 'bg-red-950/30 text-red-400 border-red-900/40';
    $now = now();
    if ($event->registration_open_at && $now < $event->registration_open_at) {
        $regStatus = 'Segera Dibuka';
        $regClass = 'bg-blue-950/30 text-blue-400 border-blue-900/40';
    } elseif ($event->registration_close_at && $now > $event->registration_close_at) {
        $regStatus = 'Ditutup';
        $regClass = 'bg-red-950/30 text-red-400 border-red-900/40';
    } elseif ($event->registration_open_at || $event->registration_close_at) {
        $regStatus = 'Dibuka';
        $regClass = 'bg-neon/10 text-neon border-neon/30';
    } else {
        $regStatus = 'Dibuka';
        $regClass = 'bg-neon/10 text-neon border-neon/30';
    }

    $cityName = $event->city ? $event->city->name : null;
    $venueName = $event->location_name;
    $locationDisplay = $cityName;
    if ($venueName && $cityName && !str_contains(strtolower($venueName), strtolower($cityName))) {
        $locationDisplay = $venueName . ', ' . $cityName;
    } elseif ($venueName) {
        $locationDisplay = $venueName;
    } elseif (!$locationDisplay) {
        $locationDisplay = 'Indonesia';
    }
@endphp
<article class="bg-slate-900 border border-slate-800/80 rounded-lg p-4 sm:p-5 hover:border-slate-700 transition-colors group event-card">
    <div class="event-card-inner flex flex-col md:flex-row gap-4 sm:gap-5 md:items-center">
        <!-- Event Thumbnail with Date Badge Overlay -->
        <div class="event-card-thumb relative flex-shrink-0 w-full md:w-44 h-40 md:h-28 rounded-md overflow-hidden border border-slate-800/80 bg-slate-950">
            <img src="{{ $event->getHeroImageUrl() ?: asset('images/hero/jadwal-lari.webp') }}" 
                 alt="{{ $event->name }}" 
                 class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500 ease-out"
                 loading="lazy">
            
            <!-- Date Badge Overlay -->
            <div class="absolute top-2 left-2 bg-slate-950/95 border border-slate-800 rounded px-2 py-1 text-center min-w-[42px] shadow-sm">
                <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-none">{{ $event->event_date->translatedFormat('M') }}</span>
                <span class="block text-base font-black text-neon leading-none mt-0.5">{{ $event->event_date->format('d') }}</span>
            </div>
        </div>

        <!-- Info -->
        <div class="event-card-body flex-grow space-y-2 min-w-0">
            <!-- Badges / Type -->
            <div class="flex items-center gap-2 text-xs">
                <span class="px-2 py-0.5 rounded text-[11px] font-medium border {{ $regClass }}">
                    {{ $regStatus }}
                </span>
                @if($event->is_featured)
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-neon text-dark">
                        Featured
                    </span>
                @endif
                @if($event->raceType)
                    <span class="text-xs text-slate-400 font-medium">
                        {{ $event->raceType->name }}
                    </span>
                @endif
            </div>
            
            <!-- Title -->
            <a href="{{ $event->public_url }}" class="block group-hover:text-neon transition-colors">
                <h3 class="text-base sm:text-lg font-bold text-white group-hover:text-neon transition-colors leading-snug">
                    {{ $event->name }}
                </h3>
            </a>
            
            <!-- Meta Details -->
            <div class="flex flex-wrap items-center gap-x-2.5 gap-y-1 text-xs sm:text-sm text-slate-300">
                <span class="text-slate-200">{{ $event->event_date->translatedFormat('d M Y') }}</span>
                <span class="text-slate-600">•</span>
                <button type="button" onclick="if(window.focusEventOnMap) window.focusEventOnMap({{ $event->id }})" class="text-slate-300 hover:text-neon transition-colors text-left inline-flex items-center gap-1 group/loc cursor-pointer" title="Lihat di Peta">
                    <span>{{ $locationDisplay }}</span>
                </button>
                @if($event->start_time)
                    <span class="text-slate-600">•</span>
                    <span class="text-slate-400">{{ $event->start_time->format('H:i') === '00:00' ? '05:00' : $event->start_time->format('H:i') }} WIB</span>
                @endif
            </div>

            <!-- Distances -->
            @if($event->raceDistances->isNotEmpty())
                <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                    @foreach($event->raceDistances as $distance)
                        <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-[11px] text-slate-300">
                            {{ $distance->name }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Action -->
        <div class="event-card-actions flex items-center md:self-center shrink-0 w-full md:w-auto mt-2 md:mt-0">
            <a href="{{ $event->public_url }}" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 rounded-md bg-white text-dark font-bold text-xs hover:bg-lime-300 transition-colors">
                Detail Event
            </a>
        </div>
    </div>
</article>
@empty
<div class="text-center py-16 bg-slate-900 rounded-lg border border-dashed border-slate-800">
    <div class="w-12 h-12 bg-slate-950 border border-slate-800 rounded-md flex items-center justify-center mx-auto mb-3 text-slate-500">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
    </div>
    <h3 class="text-base font-semibold text-white mb-1">Belum Ada Event Ditemukan</h3>
    <p class="text-slate-400 text-xs">Coba ubah filter kota, kategori jarak, atau kata kunci pencarian Anda.</p>
</div>
@endforelse
