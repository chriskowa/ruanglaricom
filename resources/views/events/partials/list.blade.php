@forelse($events as $event)
@php
    $regStatus = 'Tutup';
    $regClass = 'bg-red-950/30 text-red-400 border-red-900/50';
    $now = now();
    if ($event->registration_open_at && $now < $event->registration_open_at) {
        $regStatus = 'Segera Dibuka';
        $regClass = 'bg-blue-950/30 text-blue-400 border-blue-900/50';
    } elseif ($event->registration_close_at && $now > $event->registration_close_at) {
        $regStatus = 'Ditutup';
        $regClass = 'bg-red-950/30 text-red-400 border-red-900/50';
    } elseif ($event->registration_open_at || $event->registration_close_at) {
        $regStatus = 'Dibuka';
        $regClass = 'bg-green-950/30 text-neon border-neon/30';
    } else {
        $regStatus = 'Dibuka';
        $regClass = 'bg-green-950/30 text-neon border-neon/30';
    }
@endphp
<article class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 hover:border-neon/50 transition-all group event-card">
    <div class="flex flex-col md:flex-row gap-5 md:items-center">
        <!-- Event Thumbnail with Date Badge Overlay -->
        <div class="relative flex-shrink-0 w-full md:w-44 h-44 md:h-28 rounded-xl overflow-hidden border border-slate-700/50 bg-slate-800 shadow-inner">
            <img src="{{ $event->getHeroImageUrl() ?: asset('images/hero/jadwal-lari.webp') }}" 
                 alt="{{ $event->name }}" 
                 class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500 ease-out"
                 loading="lazy">
            
            <!-- Date Badge Overlay -->
            <div class="absolute top-2.5 left-2.5 bg-slate-950/85 backdrop-blur-md border border-slate-700/50 rounded-lg p-1.5 flex flex-col items-center justify-center min-w-[48px] shadow-lg">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-0.5">{{ $event->event_date->format('M') }}</span>
                <span class="text-lg font-black text-neon leading-none">{{ $event->event_date->format('d') }}</span>
                <span class="text-[8px] font-bold text-slate-500 uppercase tracking-wider leading-none mt-0.5">{{ $event->event_date->format('D') }}</span>
            </div>
        </div>

        <!-- Info -->
        <div class="flex-grow space-y-2">
            <!-- Badges -->
            <div class="flex flex-wrap gap-1.5 items-center">
                @if($event->is_featured)
                    <span class="px-2 py-0.5 rounded text-[9px] font-extrabold bg-neon/10 text-neon border border-neon/30 uppercase tracking-wider">Featured</span>
                @endif
                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-800/60 text-slate-300 border border-slate-700/50 uppercase tracking-wider">
                    {{ $event->raceType->name ?? 'Running Event' }}
                </span>
                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-800/60 text-slate-300 border border-slate-700/50 uppercase tracking-wider">
                    {{ $event->city ? $event->city->name : $event->location_name }}
                </span>
                <span class="px-2 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider {{ $regClass }}">
                    {{ $regStatus }}
                </span>
            </div>
            
            <!-- Title -->
            <a href="{{ $event->public_url }}" class="block group-hover:text-neon transition-colors">
                <h3 class="text-lg md:text-xl font-black text-white group-hover:text-neon transition-colors uppercase tracking-tight leading-snug">{{ $event->name }}</h3>
            </a>
            
            <!-- Meta Details -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs md:text-sm text-slate-400">
                <!-- Date -->
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $event->event_date->translatedFormat('d M Y') }}</span>
                </div>
                
                <!-- Time -->
                @if($event->start_time)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ $event->start_time->format('H:i') === '00:00' ? '05:00' : $event->start_time->format('H:i') }} WIB</span>
                    </div>
                @endif
                
                <!-- Location -->
                <div class="flex items-center gap-1.5 max-w-xs sm:max-w-sm md:max-w-md">
                    <svg class="w-4 h-4 text-neon flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="truncate">{{ $event->location_name ?? ($event->city ? $event->city->name : 'TBA') }}</span>
                </div>
            </div>

            <!-- Distances -->
            @if($event->raceDistances->isNotEmpty())
                <div class="flex flex-wrap items-center gap-1.5 pt-1">
                    <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider mr-1">Kategori:</span>
                    @foreach($event->raceDistances as $distance)
                        <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-[11px] text-slate-300 font-mono font-bold hover:border-neon/30 transition-colors">
                            {{ $distance->name }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Action -->
        <div class="flex flex-row md:flex-col gap-2 w-full md:w-auto md:self-center md:items-end mt-3 md:mt-0">
            <a href="{{ $event->public_url }}" class="flex-1 md:flex-none inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-neon text-dark font-extrabold text-sm hover:bg-lime-300 transition-all shadow-lg shadow-neon/10 hover:shadow-neon/20">
                Detail Event
                <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
            
            @php
                $startDate = $event->event_date->format('Ymd');
                $startTime = $event->start_time ? $event->start_time->format('His') : '050000';
                $startDateTime = $startDate . 'T' . $startTime;
                $endDateTime = \Carbon\Carbon::parse($startDate . ' ' . ($event->start_time ? $event->start_time->format('H:i:s') : '05:00:00'))->addHours(5)->format('Ymd\THis');
                
                $gCalUrl = "https://www.google.com/calendar/render?action=TEMPLATE";
                $gCalUrl .= "&text=" . urlencode($event->name);
                $gCalUrl .= "&dates=" . $startDateTime . "/" . $endDateTime;
                $gCalUrl .= "&details=" . urlencode("Event Lari: " . $event->name . "\nLokasi: " . ($event->location_name ?? 'TBA'));
                $gCalUrl .= "&location=" . urlencode($event->location_name ?? ($event->city ? $event->city->name : ''));
                $gCalUrl .= "&sf=true&output=xml";
            @endphp
            
            <a href="{{ $gCalUrl }}" target="_blank" class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-slate-300 hover:text-white text-xs font-bold transition-all">
                <svg class="w-4 h-4 mr-1.5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Add to Calendar
            </a>
        </div>
    </div>
</article>
@empty
<div class="text-center py-16 bg-card/30 rounded-2xl border border-dashed border-slate-700">
    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
    </div>
    <h3 class="text-lg font-bold text-white mb-1">Belum ada event ditemukan</h3>
    <p class="text-slate-400 text-sm">Coba ubah filter pencarian Anda.</p>
</div>
@endforelse
