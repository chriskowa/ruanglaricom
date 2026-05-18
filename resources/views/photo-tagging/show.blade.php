@extends('layouts.pacerhub')

@section('title', 'Cari Foto - ' . $event->name)

@section('content')
<div class="min-h-screen pb-20 font-sans bg-slate-950">
    
    <!-- Event Cover & Search Section -->
    <div class="relative pt-24 pb-16 lg:pt-32 lg:pb-24 overflow-hidden border-b border-slate-800">
        <div class="absolute inset-0 z-0">
            @if($event->getHeroImageUrl())
                <img src="{{ $event->getHeroImageUrl() }}" alt="Cover" class="w-full h-full object-cover opacity-20 blur-sm">
            @else
                <div class="absolute inset-0 bg-slate-900"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/80 via-slate-950/90 to-slate-950 z-10"></div>
        </div>

        <div class="container mx-auto px-4 relative z-20">
            <div class="max-w-4xl mx-auto text-center">
                <a href="{{ route('photo-tagging.index') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors mb-6 text-sm font-bold uppercase tracking-widest">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
                
                <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tighter mb-4">{{ $event->name }}</h1>
                <div class="flex flex-wrap justify-center gap-4 text-sm font-bold text-slate-400 mb-10">
                    <div class="flex items-center gap-1 bg-slate-900/50 px-3 py-1.5 rounded-full border border-slate-700/50">
                        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $event->start_at ? $event->start_at->format('d M Y') : 'TBA' }}
                    </div>
                    <div class="flex items-center gap-1 bg-slate-900/50 px-3 py-1.5 rounded-full border border-slate-700/50">
                        <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $event->location_name ?? 'Indonesia' }}
                    </div>
                </div>

                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 md:p-8 max-w-2xl mx-auto shadow-2xl shadow-red-900/10">
                    <p class="text-sm font-bold text-slate-300 mb-4 tracking-wide uppercase">Masukkan nomor BIB yang tertera di dada peserta</p>
                    <form action="{{ route('photo-tagging.show', $event->slug) }}" method="GET" class="relative flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-6 h-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                            </div>
                            <input type="text" name="bib_number" value="{{ $bib }}" required placeholder="Contoh: 1025" class="w-full pl-12 pr-4 py-4 bg-slate-900 border border-slate-600 text-white rounded-2xl focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 transition-colors font-bold text-lg md:text-xl text-center sm:text-left shadow-inner">
                        </div>
                        <button type="submit" class="py-4 px-8 bg-red-600 hover:bg-red-700 text-white font-black rounded-2xl transition-all shadow-lg shadow-red-600/30 hover:scale-105 active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span class="hidden sm:inline">Cari Foto</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Results Section -->
    <div class="container mx-auto px-4 mt-12">
        
        @if(!$bib)
            <!-- Empty State: Before Search -->
            <div class="max-w-xl mx-auto text-center py-12">
                <div class="w-24 h-24 bg-slate-900 rounded-full flex items-center justify-center mx-auto mb-6 border border-slate-800">
                    <svg class="w-12 h-12 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">Siap Mencari Momenmu?</h3>
                <p class="text-slate-400">Ketik nomor BIB pada kolom pencarian di atas untuk melihat foto-foto kerenmu selama perlombaan.</p>
            </div>
        @elseif($photos && $photos->count() > 0)
            <!-- Results Grid -->
            <div class="mb-8 flex justify-between items-end border-b border-slate-800 pb-4">
                <div>
                    <h2 class="text-2xl font-black text-white italic">Hasil Pencarian BIB: <span class="text-red-500">{{ $bib }}</span></h2>
                    <p class="text-slate-400 text-sm mt-1">Ditemukan {{ $photos->total() }} foto momen terbaikmu.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                @foreach($photos as $tag)
                    <div class="bg-card/50 border border-slate-800 rounded-2xl overflow-hidden group shadow-lg">
                        <div class="relative aspect-[4/3] bg-slate-900 overflow-hidden cursor-pointer" onclick="openModal('{{ $tag->photo->image_url }}')">
                            <img src="{{ $tag->photo->image_url }}" alt="Photo BIB {{ $bib }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="bg-slate-900/80 backdrop-blur text-white px-4 py-2 rounded-full font-bold text-sm border border-slate-700 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                                    Perbesar
                                </span>
                            </div>
                        </div>
                        <div class="p-4 flex gap-2">
                            <a href="{{ $tag->photo->image_url }}" download class="flex-1 py-2.5 bg-slate-800 hover:bg-red-600 text-white font-bold text-center rounded-xl transition-colors text-sm border border-slate-700 hover:border-red-500 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download
                            </a>
                            <button onclick="sharePhoto('{{ $tag->photo->image_url }}')" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl border border-slate-700 transition-colors" title="Bagikan">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($photos->hasPages())
                <div class="mt-12">
                    {{ $photos->links() }}
                </div>
            @endif
        @else
            <!-- Empty State: No Results -->
            <div class="max-w-xl mx-auto text-center py-16 px-4 bg-slate-900/50 border border-slate-800 rounded-3xl">
                <div class="w-24 h-24 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Foto Belum Ditemukan</h3>
                <p class="text-slate-400 mb-6">Foto untuk nomor BIB <strong class="text-white">{{ $bib }}</strong> belum tersedia atau belum dipublish. Coba periksa kembali nomor BIB kamu atau tunggu proses upload oleh admin selesai.</p>
                <a href="{{ route('photo-tagging.show', $event->slug) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl transition-colors text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Ulangi Pencarian
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Modal untuk View Foto Penuh -->
<div id="photoModal" class="fixed inset-0 z-50 hidden bg-slate-950/95 backdrop-blur-sm flex items-center justify-center opacity-0 transition-opacity duration-300">
    <button onclick="closeModal()" class="absolute top-6 right-6 p-2 bg-slate-800/80 hover:bg-red-600 text-white rounded-full transition-colors z-50 border border-slate-700">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
    <div class="container mx-auto p-4 md:p-8 h-full flex flex-col justify-center relative">
        <img id="modalImage" src="" alt="Full Screen Photo" class="max-w-full max-h-[85vh] mx-auto object-contain rounded-lg shadow-2xl">
        <div class="mt-6 flex justify-center gap-4">
            <a id="modalDownloadBtn" href="" download class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download Foto
            </a>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('photoModal');
    const modalImage = document.getElementById('modalImage');
    const modalDownloadBtn = document.getElementById('modalDownloadBtn');

    function openModal(imageUrl) {
        modalImage.src = imageUrl;
        modalDownloadBtn.href = imageUrl;
        modal.classList.remove('hidden');
        // trigger reflow
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            modalImage.src = '';
        }, 300);
    }

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    async function sharePhoto(url) {
        if (navigator.share) {
            try {
                await navigator.share({
                    title: 'Foto Lari Saya - {{ $event->name }}',
                    text: 'Lihat momen lari saya di {{ $event->name }}! Nomor BIB: {{ $bib }}',
                    url: url
                });
            } catch (err) {
                console.log('Error sharing:', err);
            }
        } else {
            // Fallback copy to clipboard
            navigator.clipboard.writeText(url).then(() => {
                alert('Link foto berhasil disalin ke clipboard!');
            });
        }
    }
</script>
@endsection
