@extends('layouts.pacerhub')

@section('title', $event->name . ' - Jadwal Lari')
@section('description', Str::limit(strip_tags($event->description), 150))

@section('content')
<div class="relative min-h-screen bg-[#0B1120] font-sans selection:bg-neon selection:text-dark">
    <!-- Hero Background -->
    <div class="absolute inset-0 h-[60vh] overflow-hidden z-0">
        @if($event->banner_image)
            <img src="{{ $event->banner_image }}" class="w-full h-full object-cover opacity-30 blur-sm scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-dark/60 via-dark/80 to-[#0B1120]"></div>
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900"></div>
            <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%239C92AC\' fill-opacity=\'0.1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        @endif
    </div>

    <!-- Content Container -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-20">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('events.index') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-neon transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kalender Lari
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-slate-600 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium text-slate-200 truncate max-w-[200px] md:max-w-xs">{{ $event->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Main Article Column -->
            <div class="lg:col-span-8 space-y-10">
                
                <!-- Title Section -->
                <header class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3 animate-fade-in-up">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-neon/10 text-neon border border-neon/20 uppercase tracking-wide">
                            {{ $event->raceType->name ?? 'Running Event' }}
                        </span>
                        @if($event->is_featured)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20 uppercase tracking-wide">Featured</span>
                        @endif
                    </div>
                    
                    <h1 class="text-4xl md:text-6xl font-black text-white tracking-tight leading-tight drop-shadow-lg">
                        {{ $event->name }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-6 text-slate-400 border-l-4 border-neon pl-6 py-1">
                        <div class="flex items-center gap-3 group">
                            <div class="p-2 rounded-lg bg-slate-800/50 group-hover:bg-neon/10 transition-colors">
                                <svg class="w-6 h-6 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <span class="block text-xs uppercase font-bold text-slate-500">Tanggal</span>
                                <span class="font-medium text-slate-200 text-lg">{{ $event->event_date->format('d F Y') }}</span>
                            </div>
                        </div>
                        <div class="hidden sm:block w-px h-10 bg-slate-700"></div>
                        <div class="flex items-center gap-3 group">
                            <div class="p-2 rounded-lg bg-slate-800/50 group-hover:bg-neon/10 transition-colors">
                                <svg class="w-6 h-6 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <div>
                                <span class="block text-xs uppercase font-bold text-slate-500">Lokasi</span>
                                <span class="font-medium text-slate-200 text-lg">{{ $event->city ? $event->city->name : $event->location_name }}</span>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Featured Image (Article Style) -->
                @if($event->banner_image)
                    <figure class="relative rounded-3xl overflow-hidden shadow-2xl ring-1 ring-white/10 group">
                        <img src="{{ $event->banner_image }}" alt="{{ $event->name }}" class="w-full h-auto object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out">
                        <div class="absolute inset-0 bg-gradient-to-t from-dark/50 to-transparent opacity-60"></div>
                    </figure>
                @endif

                <!-- Article Content -->
                <div class="bg-card/30 backdrop-blur-sm border border-white/5 rounded-3xl p-6 md:p-10">
                    <article class="prose prose-lg prose-invert max-w-none 
                        prose-headings:font-bold prose-headings:tracking-tight prose-headings:text-white
                        prose-p:text-slate-300 prose-p:leading-relaxed 
                        prose-a:text-neon prose-a:no-underline hover:prose-a:underline 
                        prose-strong:text-white prose-strong:font-black
                        prose-ul:list-disc prose-ul:pl-6 prose-li:text-slate-300
                        prose-img:rounded-2xl prose-img:shadow-xl">
                        {!! nl2br(e($event->description)) !!}
                    </article>
                </div>

                <!-- Race Categories Grid -->
                @if($event->raceDistances->count() > 0)
                    <div class="pt-8">
                        <div class="flex items-center gap-4 mb-8">
                            <h3 class="text-2xl font-black text-white italic">KATEGORI JARAK</h3>
                            <div class="h-px flex-1 bg-slate-800"></div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($event->raceDistances as $distance)
                                <div class="relative overflow-hidden bg-slate-800/50 hover:bg-slate-800 border border-slate-700 hover:border-neon/50 rounded-2xl p-6 text-center transition-all group cursor-default">
                                    <div class="absolute top-0 right-0 p-2 opacity-10 group-hover:opacity-20 transition-opacity">
                                        <svg class="w-12 h-12 text-neon" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 2c-5.621 0-10.211 4.443-10.475 10h-3.025l5 6.625 5-6.625h-2.975c.257-3.351 3.06-6 6.475-6 3.584 0 6.5 2.916 6.5 6.5s-2.916 6.5-6.5 6.5c-1.863 0-3.542-.793-4.728-2.053l-2.427 3.216c1.877 1.754 4.389 2.837 7.155 2.837 5.79 0 10.5-4.71 10.5-10.5s-4.71-10.5-10.5-10.5z"/></svg>
                                    </div>
                                    <span class="relative z-10 block text-3xl font-black text-white group-hover:text-neon mb-1 transition-colors">{{ $distance->name }}</span>
                                    <span class="relative z-10 text-xs text-slate-500 uppercase tracking-wider font-bold">Distance</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Related Events & Same Date Events -->
                <div class="pt-12 space-y-12">
                    @if(isset($relatedEvents) && $relatedEvents->count() > 0)
                        <div>
                            <div class="flex items-center gap-4 mb-6">
                                <h3 class="text-2xl font-black text-white italic uppercase">Event Serupa</h3>
                                <div class="h-px flex-1 bg-slate-800"></div>
                                <a href="{{ route('events.index') }}" class="text-xs font-bold text-neon hover:underline">LIHAT SEMUA</a>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @foreach($relatedEvents as $related)
                                    <a href="{{ route('event.detail', $related->slug) }}" class="group block bg-slate-800/30 border border-slate-700 hover:border-neon/50 rounded-2xl overflow-hidden transition-all hover:bg-slate-800/50">
                                        <div class="aspect-video relative overflow-hidden">
                                            @if($related->banner_image)
                                                <img src="{{ $related->banner_image }}" alt="{{ $related->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                            @else
                                                <div class="w-full h-full bg-slate-800 flex items-center justify-center">
                                                    <svg class="w-10 h-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div class="absolute top-2 right-2 bg-dark/80 backdrop-blur-sm px-2 py-1 rounded-lg text-xs font-bold text-white border border-white/10">
                                                {{ $related->event_date->format('d M') }}
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <h4 class="font-bold text-white group-hover:text-neon transition-colors line-clamp-1 mb-1">{{ $related->name }}</h4>
                                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                {{ $related->city ? $related->city->name : $related->location_name }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(isset($sameDateEvents) && $sameDateEvents->count() > 0)
                        <div>
                            <div class="flex items-center gap-4 mb-6">
                                <h3 class="text-2xl font-black text-white italic uppercase">Event di Tanggal Sama</h3>
                                <div class="h-px flex-1 bg-slate-800"></div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @foreach($sameDateEvents as $sameDate)
                                    <a href="{{ route('event.detail', $sameDate->slug) }}" class="group block bg-slate-800/30 border border-slate-700 hover:border-amber-500/50 rounded-2xl overflow-hidden transition-all hover:bg-slate-800/50">
                                        <div class="aspect-video relative overflow-hidden">
                                            @if($sameDate->banner_image)
                                                <img src="{{ $sameDate->banner_image }}" alt="{{ $sameDate->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                            @else
                                                <div class="w-full h-full bg-slate-800 flex items-center justify-center">
                                                    <svg class="w-10 h-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div class="absolute top-2 right-2 bg-amber-500 text-dark px-2 py-1 rounded-lg text-xs font-bold border border-amber-400 shadow-lg shadow-amber-500/20">
                                                {{ $sameDate->event_date->format('d M') }}
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <h4 class="font-bold text-white group-hover:text-amber-500 transition-colors line-clamp-1 mb-1">{{ $sameDate->name }}</h4>
                                            <p class="text-xs text-slate-400 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                {{ $sameDate->city ? $sameDate->city->name : $sameDate->location_name }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-4 space-y-8">
                <!-- Registration Widget -->
                <div class="sticky top-24 space-y-6">
                    <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-700 rounded-3xl p-6 shadow-2xl relative overflow-hidden ring-1 ring-white/5">
                        <!-- Decorative background -->
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-neon/5 rounded-full blur-3xl"></div>
                        
                        <h3 class="text-xl font-black text-white mb-6 relative z-10 flex items-center gap-2">
                            <span class="w-1 h-6 bg-neon rounded-full"></span>
                            STATUS PENDAFTARAN
                        </h3>
                        
                        @if($event->registration_link)
                            <a href="{{ $event->registration_link }}" target="_blank" class="relative z-10 flex items-center justify-center w-full py-4 rounded-xl bg-neon text-dark font-black text-lg hover:bg-neonHover hover:scale-[1.02] transition-all shadow-lg shadow-neon/25 group">
                                DAFTAR SEKARANG
                                <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </a>
                            <p class="text-center text-xs text-slate-500 mt-3 relative z-10">
                                Anda akan diarahkan ke halaman pendaftaran resmi
                            </p>
                        @else
                            <div class="relative z-10 w-full py-4 rounded-xl bg-slate-800 text-slate-500 font-bold text-lg text-center border border-slate-700 cursor-not-allowed">
                                Pendaftaran Belum Dibuka
                            </div>
                        @endif

                        <div class="mt-8 pt-8 border-t border-slate-800 relative z-10 space-y-5">
                            <div class="flex items-start gap-4 group">
                                <div class="p-3 rounded-xl bg-slate-800 text-slate-400 group-hover:text-neon transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-500 uppercase font-bold tracking-wider">Waktu Start</span>
                                    <span class="text-slate-200 font-bold text-lg">{{ $event->start_time ? $event->start_time->format('H:i') . ' WIB' : 'TBA' }}</span>
                                </div>
                            </div>
                            
                            <div class="flex items-start gap-4 group">
                                <div class="p-3 rounded-xl bg-slate-800 text-slate-400 group-hover:text-neon transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-500 uppercase font-bold tracking-wider">Lokasi Lengkap</span>
                                    <span class="text-slate-200 font-bold leading-snug">{{ $event->location_name ?? 'To be announced' }}</span>
                                </div>
                            </div>

                            <div class="flex items-start gap-4 group">
                                <div class="p-3 rounded-xl bg-slate-800 text-slate-400 group-hover:text-neon transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <div>
                                    <span class="block text-xs text-slate-500 uppercase font-bold tracking-wider">Penyelenggara</span>
                                    <span class="text-slate-200 font-bold">{{ $event->organizer_name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Share / Socials -->
                    <div class="flex items-center justify-between p-5 rounded-2xl bg-slate-900/50 border border-slate-700/50 backdrop-blur-sm">
                        <span class="text-sm font-bold text-slate-400">Bagikan Event</span>
                        <div class="flex gap-3">
                            <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link disalin!')" class="p-2.5 rounded-xl bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all" title="Copy Link">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            </button>
                            @if($event->social_media_link)
                                <a href="{{ $event->social_media_link }}" target="_blank" class="p-2.5 rounded-xl bg-slate-800 text-slate-400 hover:text-pink-500 hover:bg-slate-700 transition-all">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection