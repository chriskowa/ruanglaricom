@extends('layouts.pacerhub')

@section('title', 'Jadwal Lari di ' . $city->name . ' ' . date('Y') . ' - Kalender Event Lari Terbaru')
@section('meta_title', 'Jadwal Lari di ' . $city->name . ' ' . date('Y') . ' - Kalender Event Lari Terbaru')
@section('meta_description', 'Daftar lengkap jadwal event lari di ' . $city->name . ' tahun ' . date('Y') . '. Temukan info pendaftaran lari 5K, 10K, Half Marathon, dan Marathon terbaru di ' . $city->name . '.')
@section('meta_keywords', 'lari di ' . $city->name . ', event lari ' . $city->name . ', jadwal lari ' . $city->name . ', kalender lari ' . $city->name . ', lomba lari ' . $city->name . ', ' . date('Y'))

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 bg-dark relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-12 text-center relative z-10" data-aos="fade-down">
        <h1 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter mb-4">
            LARI DI <span class="text-neon uppercase">{{ $city->name }}</span>
        </h1>
        <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto">
            Temukan jadwal event lari terbaru dan terlengkap yang diselenggarakan di {{ $city->name }}.
        </p>
    </div>

    <!-- Upcoming Events -->
    <div class="max-w-7xl mx-auto mb-16 relative z-10">
        <div class="flex items-center gap-4 mb-8">
            <h2 class="text-2xl md:text-3xl font-black text-white italic tracking-tight">
                EVENT <span class="text-neon">MENDATANG</span>
            </h2>
            <div class="h-px bg-slate-800 flex-grow"></div>
        </div>

        @if($upcomingEvents->count() > 0)
            <div class="space-y-4">
                @include('events.partials.list', ['events' => $upcomingEvents])
            </div>
        @else
            <div class="text-center py-16 bg-card/30 rounded-2xl border border-dashed border-slate-700">
                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <h3 class="text-lg font-bold text-white mb-1">Belum ada event mendatang di {{ $city->name }}</h3>
                <p class="text-slate-400 text-sm">Pantau terus halaman ini untuk update terbaru.</p>
            </div>
        @endif
    </div>

    <!-- Past Events (Archive) -->
    @if($pastEvents->count() > 0)
    <div class="max-w-7xl mx-auto relative z-10">
        <div class="flex items-center gap-4 mb-8">
            <h2 class="text-2xl md:text-3xl font-black text-slate-500 italic tracking-tight">
                ARSIP <span class="text-slate-600">EVENT</span>
            </h2>
            <div class="h-px bg-slate-800 flex-grow"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pastEvents as $event)
            <div class="bg-card/30 border border-slate-800 rounded-xl overflow-hidden hover:border-slate-600 transition-all group opacity-75 hover:opacity-100">
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xs font-bold text-slate-500 uppercase">{{ $event->start_at->format('d M Y') }}</span>
                        @if($event->raceType)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700 uppercase">
                            {{ $event->raceType->name }}
                        </span>
                        @endif
                    </div>
                    
                    <a href="{{ $event->public_url }}" class="block group-hover:text-neon transition-colors">
                        <h3 class="text-lg font-bold text-white mb-2 line-clamp-2">{{ $event->name }}</h3>
                    </a>
                    
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($event->raceDistances as $distance)
                            <span class="text-xs text-slate-500 font-mono">
                                {{ $distance->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
