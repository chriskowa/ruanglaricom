@forelse($events as $event)
<div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 hover:border-neon/50 transition-all group">
    <div class="flex flex-col md:flex-row gap-4 md:items-center">
        <!-- Date Badge -->
        <div class="flex-shrink-0 flex md:flex-col items-center justify-center bg-slate-800 rounded-xl p-3 md:w-20 md:h-20 border border-slate-700">
            <span class="text-xs font-bold text-slate-400 uppercase">{{ $event->event_date->format('M') }}</span>
            <span class="text-2xl md:text-3xl font-black text-white leading-none">{{ $event->event_date->format('d') }}</span>
            <span class="text-xs text-slate-500">{{ $event->event_date->format('D') }}</span>
        </div>

        <!-- Info -->
        <div class="flex-grow">
            <div class="flex flex-wrap gap-2 mb-2">
                @if($event->is_featured)
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-neon text-dark uppercase tracking-wide">Featured</span>
                @endif
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-700 text-slate-300 uppercase tracking-wide border border-slate-600">
                    {{ $event->raceType->name ?? 'Running Event' }}
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-700 text-slate-300 uppercase tracking-wide border border-slate-600">
                    {{ $event->city ? $event->city->name : $event->location_name }}
                </span>
            </div>
            
            <a href="{{ isset($event->is_eo) && $event->is_eo ? route('events.show', $event->slug) : route('running-event.detail', $event->slug) }}" class="block group-hover:text-neon transition-colors">
                <h3 class="text-xl font-bold text-white mb-1">{{ $event->name }}</h3>
            </a>
            
            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-slate-400 mb-2">
                @if($event->start_time)
                    <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $event->start_time->format('H:i') }} WIB
                    </div>
                @endif
                <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    {{ $event->location_name ?? ($event->city ? $event->city->name : 'TBA') }}
                </div>
            </div>

            <!-- Distances -->
            <div class="flex flex-wrap gap-2 mt-3">
                @foreach($event->raceDistances as $distance)
                    <span class="px-2 py-1 rounded bg-slate-800 text-xs text-neon font-mono border border-slate-700">
                        {{ $distance->name }}
                    </span>
                @endforeach
            </div>
        </div>

        <!-- Action -->
        <div class="flex-shrink-0 mt-2 md:mt-0">
            <a href="{{ isset($event->is_eo) && $event->is_eo ? route('events.show', $event->slug) : route('running-event.detail', $event->slug) }}" class="inline-flex items-center justify-center w-full md:w-auto px-6 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white font-bold hover:bg-neon hover:text-dark hover:border-neon transition-all">
                Detail Event
                <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </a>
        </div>
    </div>
</div>
@empty
<div class="text-center py-16 bg-card/30 rounded-2xl border border-dashed border-slate-700">
    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
    </div>
    <h3 class="text-lg font-bold text-white mb-1">Belum ada event ditemukan</h3>
    <p class="text-slate-400 text-sm">Coba ubah filter pencarian Anda.</p>
</div>
@endforelse
