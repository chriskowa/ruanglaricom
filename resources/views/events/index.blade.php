@extends('layouts.pacerhub')

@section('content')
    <header class="pt-24 pb-6 px-4 relative overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-neon/10 rounded-full blur-[120px] -z-10"></div>
        <div class="max-w-7xl mx-auto text-center">
            <span class="text-neon font-mono font-bold tracking-widest text-xs uppercase mb-2 block">Official Race Calendar</span>
            <h1 class="text-4xl md:text-5xl font-black mb-3">FIND YOUR NEXT <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400">CHALLENGE</span></h1>
            <p class="text-slate-400 max-w-xl mx-auto text-sm md:text-base">Jadwal event lari terlengkap di Indonesia. Temukan race impianmu.</p>
        </div>
    </header>

    <div class="sticky top-20 z-40 bg-dark/95 backdrop-blur border-y border-slate-800 py-4">
        <div class="max-w-7xl mx-auto px-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input name="search" value="{{ request('search') }}" placeholder="Cari nama event..." class="bg-slate-900 border border-slate-700 rounded-xl py-2.5 px-4 text-sm text-white focus:border-neon outline-none">
                <select name="month" class="bg-slate-900 border border-slate-700 rounded-xl py-2.5 px-4 text-sm text-white focus:border-neon outline-none">
                    <option value="All">Semua Bulan</option>
                    @foreach($months as $m)
                        <option value="{{ $m }}" @selected(request('month')===$m)>{{ $m }}</option>
                    @endforeach
                </select>
                <select name="location" class="bg-slate-900 border border-slate-700 rounded-xl py-2.5 px-4 text-sm text-white focus:border-neon outline-none">
                    <option value="All">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}" @selected(request('location')===$loc)>{{ $loc }}</option>
                    @endforeach
                </select>
                <button class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white hover:bg-slate-700 transition">Filter</button>
            </form>
        </div>
    </div>

    <main class="max-w-7xl mx-auto px-4 py-10 min-h-[600px]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($events as $event)
            @php
                $date = $event->start_at ?? $event->created_at;
                $monthLabel = $date ? \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('F') : '';
                $dayLabel = $date ? \Carbon\Carbon::parse($date)->format('d') : '';
                $distLabels = $event->categories->map(function($c){ return $c->distance_label ?? $c->name; })->unique()->values();
                $cover = $event->cover_url ?? asset('images/placeholder-run.jpg');
            @endphp
            <div class="bg-card border border-slate-800 rounded-3xl overflow-hidden card-hover transition group flex flex-col h-full">
                <div class="h-52 relative overflow-hidden bg-slate-800">
                    <img src="{{ $cover }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                    <div class="absolute -bottom-4 right-6 bg-slate-900 border border-slate-700 p-2 rounded-xl text-center min-w-[60px] shadow-xl z-10 group-hover:border-neon transition">
                        <span class="block text-xs font-bold text-slate-400 uppercase">{{ $monthLabel }}</span>
                        <span class="block text-2xl font-black text-white font-mono">{{ $dayLabel }}</span>
                    </div>
                </div>
                <div class="p-6 pt-8 flex-grow flex flex-col">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold text-white mb-1 leading-tight group-hover:text-neon transition">{{ $event->name }}</h3>
                        <p class="text-sm text-slate-400">{{ $event->location_name }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-6">
                        @foreach($distLabels as $dist)
                            <span class="text-[10px] font-bold border border-slate-700 px-2 py-1 rounded text-slate-300 bg-slate-800/50">{{ $dist }}</span>
                        @endforeach
                    </div>
                    <div class="mt-auto border-t border-slate-800 pt-4 flex justify-between items-center">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase">Mulai dari</p>
                            <p class="font-mono font-bold text-white">{{ $event->starting_price ? 'Rp '.number_format($event->starting_price,0,',','.') : '-' }}</p>
                        </div>
                        <a href="{{ $event->public_url }}" class="bg-white text-dark hover:bg-neon transition px-5 py-2 rounded-lg text-xs font-bold uppercase tracking-wider">Detail</a>
                    </div>
                </div>
            </div>
            @empty
                <div class="col-span-3 text-center text-slate-500 py-20">Event tidak ditemukan</div>
            @endforelse
        </div>

        <div class="mt-10">
            {{ $events->withQueryString()->links() }}
        </div>
    </main>
@endsection

