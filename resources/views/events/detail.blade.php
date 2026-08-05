@extends('layouts.pacerhub')

@section('title', $event->name . ' - Jadwal Lari')
@section('description', Str::limit($event->full_description, 150))

@section('content')
<div class="min-h-screen pt-20 pb-16 px-4 sm:px-6 md:px-8 font-sans">
    
    <!-- Breadcrumb -->
    <div class="max-w-5xl mx-auto mb-6">
        <a href="{{ route('events.index') }}" class="inline-flex items-center text-slate-400 hover:text-white text-xs sm:text-sm transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Kembali ke Kalender
        </a>
    </div>

    <!-- Main 2-Column Container (On Mobile: Main Content -> Sidebar) -->
    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6 md:space-y-8">
            <!-- Header -->
            <div>
                <div class="flex flex-wrap gap-2 mb-3">
                    <span class="px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-neon text-dark uppercase tracking-wide">
                        {{ $event->raceType->name ?? 'Running Event' }}
                    </span>
                    @if($event->is_featured)
                        <span class="px-3 py-1 rounded-full text-[11px] sm:text-xs font-bold bg-amber-500 text-dark uppercase tracking-wide">Featured</span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-black text-white tracking-tight mb-4 leading-tight">
                    {{ $event->name }}
                </h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-slate-300">
                    <div class="flex items-center gap-3 bg-slate-900/80 border border-slate-800 p-3 rounded-xl">
                        <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-neon border border-slate-700 shrink-0">
                            <span class="font-bold text-base">{{ $event->event_date->format('d') }}</span>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">{{ $event->event_date->format('M Y') }}</span>
                            <span class="text-xs sm:text-sm font-bold text-white truncate">{{ $event->event_date->format('l') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-slate-900/80 border border-slate-800 p-3 rounded-xl">
                        <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <div class="flex flex-col min-w-0">
                            <span class="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Lokasi</span>
                            <span class="text-xs sm:text-sm font-bold text-white truncate">{{ $event->city ? $event->city->name : $event->location_name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner -->
            @if($event->hero_image_url)
                <div class="rounded-2xl overflow-hidden border border-slate-800 shadow-2xl bg-slate-900">
                    <img src="{{ $event->hero_image_url }}" alt="{{ $event->name }}" class="w-full h-auto max-h-[420px] object-cover">
                </div>
            @endif

            <!-- Description -->
            <div class="prose prose-invert max-w-none">
                <h3 class="text-white font-extrabold text-lg sm:text-xl mb-3 uppercase tracking-tight">Tentang Event</h3>
                <div class="text-slate-300 leading-relaxed text-sm sm:text-base whitespace-pre-line bg-slate-900/60 border border-slate-800/80 p-5 rounded-2xl">
                    {{ $event->full_description }}
                </div>
            </div>

            <!-- Compact Race Categories -->
            @if($event->raceDistances && count($event->raceDistances) > 0)
            <div>
                <h3 class="text-white font-extrabold text-lg sm:text-xl mb-3 uppercase tracking-tight flex items-center gap-2">
                    <i class="fa-solid fa-flag-checkered text-neon text-sm"></i>
                    <span>Kategori Lomba</span>
                </h3>
                <div class="flex flex-wrap gap-2.5">
                    @foreach($event->raceDistances as $distance)
                        <div class="bg-slate-900/90 border border-slate-800 rounded-xl px-4 py-2.5 flex items-center gap-2 shadow-sm">
                            <i class="fa-solid fa-person-running text-neon text-xs"></i>
                            <span class="text-xs sm:text-sm font-black text-neon font-mono">{{ $distance->name }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar (On Mobile: Renders after Main Content) -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Registration Card -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 sm:p-6 sticky top-24 shadow-xl">
                <h3 class="text-base sm:text-lg font-bold text-white mb-4 uppercase tracking-tight">Informasi Pendaftaran</h3>
                
                @if($event->external_registration_link)
                    <a href="{{ $event->external_registration_link }}" target="_blank" rel="noopener noreferrer" class="block w-full py-3.5 rounded-xl bg-neon text-dark font-black text-center hover:bg-white transition-all shadow-lg shadow-neon/20 mb-4 text-sm sm:text-base uppercase tracking-wider">
                        DAFTAR SEKARANG
                    </a>
                @else
                    <button disabled class="block w-full py-3.5 rounded-xl bg-slate-800 text-slate-500 font-bold text-center cursor-not-allowed mb-4 text-xs sm:text-sm">
                        Link Belum Tersedia
                    </button>
                @endif

                <div class="space-y-3.5 text-xs sm:text-sm border-t border-slate-800 pt-4">
                    @if($event->organizer_name)
                    <div>
                        <span class="block text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-0.5">Penyelenggara</span>
                        <span class="font-bold text-white">{{ $event->organizer_name }}</span>
                    </div>
                    @endif
                    
                    @if($event->start_time)
                    <div>
                        <span class="block text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-0.5">Waktu Start</span>
                        <span class="font-bold text-white">{{ $event->start_time->format('H:i') }} WIB</span>
                    </div>
                    @endif

                    @if($event->location_name)
                    <div>
                        <span class="block text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-0.5">Lokasi Spesifik</span>
                        <span class="font-bold text-white">{{ $event->location_name }}</span>
                    </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-slate-800 flex gap-2 justify-center">
                    @if($event->social_media_link)
                        <a href="{{ $event->social_media_link }}" target="_blank" class="p-2.5 rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors" title="Instagram Event">
                            <i class="fab fa-instagram text-lg"></i>
                        </a>
                    @endif
                    <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link disalin!')" class="p-2.5 rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors" title="Bagikan Link Event">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Serupa (Renders Below Sidebar on Mobile & Desktop) -->
    @if(isset($relatedEvents) && count($relatedEvents) > 0)
    <div class="max-w-5xl mx-auto mt-12 pt-8 border-t border-slate-800">
        <h3 class="text-lg sm:text-xl font-extrabold text-white mb-6 uppercase tracking-tight flex items-center gap-2">
            <i class="fa-solid fa-calendar-days text-neon text-sm"></i>
            <span>Event Lari Serupa</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($relatedEvents as $rel)
                <a href="{{ route('running-event.detail', $rel->slug) }}" class="group bg-slate-900/80 border border-slate-800 rounded-2xl p-4 hover:border-neon/50 transition duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 mb-2">
                            <span class="text-neon font-bold uppercase font-mono">{{ $rel->raceType->name ?? 'Event' }}</span>
                            <span>{{ \Carbon\Carbon::parse($rel->start_at)->format('d M Y') }}</span>
                        </div>
                        <h4 class="font-bold text-white text-sm group-hover:text-neon transition-colors line-clamp-2 mb-2">{{ $rel->name }}</h4>
                    </div>
                    <div class="text-xs text-slate-400 flex items-center gap-1.5 mt-3 pt-3 border-t border-slate-800">
                        <i class="fa-solid fa-location-dot text-slate-500 text-[10px]"></i>
                        <span class="truncate">{{ $rel->city->name ?? $rel->location_name }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection