@extends('layouts.pacerhub')

@section('title', 'Cari Foto Lari Berdasarkan Nomor BIB')

@section('content')
<!-- Hero Section -->
<div class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden font-sans">
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900 via-slate-900/90 to-slate-900 z-10"></div>
        <img src="{{ asset('img/hero-running.jpg') }}" alt="Running" class="w-full h-full object-cover opacity-30" onerror="this.src='https://images.unsplash.com/photo-1552674605-15c2145e9ca8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80'">
    </div>

    <div class="container mx-auto px-4 relative z-20">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter mb-6 leading-tight">
                CARI FOTO LARIMU <br/>
                <span class="text-red-500">DENGAN NOMOR BIB</span>
            </h1>
            <p class="text-lg md:text-xl text-slate-300 mb-10 leading-relaxed font-medium">
                Masukkan nomor BIB dan temukan momen terbaikmu di event lari. Cepat, mudah, dan langsung bisa didownload.
            </p>

            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 md:p-8 max-w-2xl mx-auto shadow-2xl shadow-red-900/20">
                <form action="#" method="GET" id="searchForm" onsubmit="event.preventDefault(); goToSearch();" class="space-y-4">
                    <div class="text-left">
                        <label class="block text-sm font-bold text-slate-300 mb-2 ml-2 uppercase tracking-wide">Pilih Event Lari</label>
                        <select id="eventSelect" class="w-full bg-slate-900/80 border border-slate-600 text-white rounded-2xl px-5 py-4 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors appearance-none font-bold text-lg">
                            <option value="" disabled selected>-- Pilih Event Lari Anda --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->slug }}">{{ $event->name }} ({{ $event->start_at?->format('d M Y') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500 text-white font-black rounded-2xl text-lg transition-all shadow-lg shadow-red-600/30 hover:scale-[1.02] active:scale-[0.98] flex justify-center items-center gap-2">
                        <span>Lanjut Cari Foto</span>
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- How it works -->
<div class="py-20 bg-slate-900 relative font-sans">
    <div class="container mx-auto px-4">
        <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter mb-4">CARA KERJA</h2>
            <div class="w-20 h-1 bg-red-600 mx-auto rounded-full"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-5xl mx-auto">
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto bg-slate-800 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-red-600/20 transition-all border border-slate-700 group-hover:border-red-500/50">
                    <svg class="w-8 h-8 text-slate-400 group-hover:text-red-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">1. Pilih Event</h3>
                <p class="text-slate-400 text-sm">Pilih event lari yang baru saja kamu ikuti dari daftar.</p>
            </div>
            
            <div class="text-center group">
                <div class="w-20 h-20 mx-auto bg-slate-800 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-blue-600/20 transition-all border border-slate-700 group-hover:border-blue-500/50">
                    <svg class="w-8 h-8 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">2. Masukkan BIB</h3>
                <p class="text-slate-400 text-sm">Ketik nomor BIB yang tertera di dada kamu saat race.</p>
            </div>

            <div class="text-center group">
                <div class="w-20 h-20 mx-auto bg-slate-800 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-green-600/20 transition-all border border-slate-700 group-hover:border-green-500/50">
                    <svg class="w-8 h-8 text-slate-400 group-hover:text-green-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">3. Temukan Foto</h3>
                <p class="text-slate-400 text-sm">Sistem kami akan menampilkan semua foto dengan nomormu.</p>
            </div>

            <div class="text-center group">
                <div class="w-20 h-20 mx-auto bg-slate-800 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-orange-600/20 transition-all border border-slate-700 group-hover:border-orange-500/50">
                    <svg class="w-8 h-8 text-slate-400 group-hover:text-orange-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">4. Download</h3>
                <p class="text-slate-400 text-sm">Download fotomu dan bagikan momen banggamu ke sosial media.</p>
            </div>
        </div>
    </div>
</div>

<!-- Latest Events -->
<div class="py-20 bg-slate-900 border-t border-slate-800 font-sans">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter mb-2">EVENT TERBARU</h2>
                <div class="w-20 h-1 bg-red-600 rounded-full"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($events as $event)
                <a href="{{ route('photo-tagging.show', $event->slug) }}" class="group bg-card/50 border border-slate-700 rounded-3xl overflow-hidden hover:border-red-500/50 hover:shadow-xl hover:shadow-red-900/20 transition-all duration-300">
                    <div class="h-56 relative overflow-hidden bg-slate-800">
                        @if($event->getHeroImageUrl())
                            <img src="{{ $event->getHeroImageUrl() }}" alt="{{ $event->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center">
                                <svg class="w-16 h-16 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent opacity-80"></div>
                        <div class="absolute bottom-4 left-4 right-4 flex justify-between items-end">
                            <span class="px-3 py-1 bg-red-600/90 backdrop-blur-sm text-white text-xs font-bold rounded-full border border-red-500 shadow-lg">Lihat Foto</span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-red-400 transition-colors">{{ $event->name }}</h3>
                        <div class="flex flex-col gap-2 text-sm text-slate-400">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                {{ $event->start_at ? $event->start_at->format('d F Y') : 'TBA' }}
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $event->location_name ?? 'Indonesia' }}
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-10 text-center text-slate-500">
                    Belum ada event yang dipublish.
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function goToSearch() {
        const select = document.getElementById('eventSelect');
        const slug = select.value;
        if (slug) {
            window.location.href = "{{ url('photo-tagging') }}/" + slug;
        } else {
            alert('Silakan pilih event lari terlebih dahulu.');
        }
    }
</script>
@endsection
