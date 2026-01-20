@extends('layouts.pacerhub')

@section('title', $event->name . ' - Jadwal Lari')
@section('description', Str::limit($event->description, 150))

@section('content')
<div class="min-h-screen pt-20 pb-16 px-4 md:px-8 font-sans">
    
    <!-- Breadcrumb -->
    <div class="max-w-5xl mx-auto mb-6">
        <a href="{{ route('events.index') }}" class="inline-flex items-center text-slate-400 hover:text-white text-sm transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Kembali ke Kalender
        </a>
    </div>

    <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Header -->
            <div>
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-neon text-dark uppercase tracking-wide">
                        {{ $event->raceType->name }}
                    </span>
                    @if($event->is_featured)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500 text-dark uppercase tracking-wide">Featured</span>
                    @endif
                </div>
                <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tighter mb-4 leading-tight">
                    {{ $event->name }}
                </h1>
                <div class="flex flex-wrap items-center gap-6 text-slate-300">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-neon border border-slate-700">
                            <span class="font-bold text-lg">{{ $event->event_date->format('d') }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold uppercase text-slate-500">{{ $event->event_date->format('M Y') }}</span>
                            <span class="text-sm font-bold">{{ $event->event_date->format('l') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400 border border-slate-700">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold uppercase text-slate-500">Lokasi</span>
                            <span class="text-sm font-bold">{{ $event->city ? $event->city->name : $event->location_name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Banner -->
            @if($event->banner_image)
                <div class="rounded-2xl overflow-hidden border border-slate-700 shadow-2xl">
                    <img src="{{ $event->banner_image }}" alt="{{ $event->name }}" class="w-full h-auto object-cover">
                </div>
            @endif

            <!-- Description -->
            <div class="prose prose-invert max-w-none">
                <h3 class="text-white font-bold text-xl mb-4">Tentang Event</h3>
                <div class="text-slate-300 leading-relaxed whitespace-pre-line">
                    {{ $event->description }}
                </div>
            </div>

            <!-- Categories -->
            <div>
                <h3 class="text-white font-bold text-xl mb-4">Kategori Lomba</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach($event->raceDistances as $distance)
                        <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 text-center">
                            <span class="block text-2xl font-black text-neon mb-1">{{ $distance->name }}</span>
                            <span class="text-xs text-slate-400 uppercase tracking-wider">Jarak</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Registration Card -->
            <div class="bg-card/80 backdrop-blur-md border border-slate-700 rounded-2xl p-6 sticky top-24">
                <h3 class="text-lg font-bold text-white mb-4">Informasi Pendaftaran</h3>
                
                @if($event->registration_link)
                    <a href="{{ $event->registration_link }}" target="_blank" rel="noopener noreferrer" class="block w-full py-4 rounded-xl bg-neon text-dark font-black text-center hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 mb-4 text-lg">
                        DAFTAR SEKARANG
                    </a>
                @else
                    <button disabled class="block w-full py-4 rounded-xl bg-slate-700 text-slate-400 font-bold text-center cursor-not-allowed mb-4">
                        Link Belum Tersedia
                    </button>
                @endif

                <div class="space-y-4 text-sm border-t border-slate-700 pt-4">
                    @if($event->organizer_name)
                    <div>
                        <span class="block text-slate-500 text-xs uppercase mb-1">Penyelenggara</span>
                        <span class="font-bold text-white">{{ $event->organizer_name }}</span>
                    </div>
                    @endif
                    
                    @if($event->start_time)
                    <div>
                        <span class="block text-slate-500 text-xs uppercase mb-1">Waktu Start</span>
                        <span class="font-bold text-white">{{ $event->start_time->format('H:i') }} WIB</span>
                    </div>
                    @endif

                    @if($event->location_name)
                    <div>
                        <span class="block text-slate-500 text-xs uppercase mb-1">Lokasi Spesifik</span>
                        <span class="font-bold text-white">{{ $event->location_name }}</span>
                    </div>
                    @endif
                </div>

                <div class="mt-6 pt-4 border-t border-slate-700 flex gap-2 justify-center">
                    @if($event->social_media_link)
                        <a href="{{ $event->social_media_link }}" target="_blank" class="p-2 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                    @endif
                    <!-- Share Button (Optional) -->
                    <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link disalin!')" class="p-2 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
