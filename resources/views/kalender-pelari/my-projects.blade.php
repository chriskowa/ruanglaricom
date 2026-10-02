@extends('layouts.pacerhub')

@section('page-title', 'Proyek Kalender Saya — RuangLari KalenderPelari')

@push('meta-head')
    <meta name="robots" content="noindex,nofollow">
    <meta name="description" content="Kelola semua proyek desain kalender lari pribadi Anda — edit, hapus, cetak, atau checkout untuk dicetak.">
@endpush

@section('content')
<main id="tool-container" class="min-h-screen bg-dark pt-20 pb-32 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <header class="mb-10">
            <div class="flex items-center gap-2 mb-4 text-xs uppercase tracking-widest text-slate-400">
                <a href="{{ route('kalender-pelari.landing') }}" class="hover:text-neon transition-colors">KalenderPelari</a>
                <span>/</span>
                <span class="text-slate-200">Proyek Saya</span>
            </div>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-50 mb-2 font-['Inter_Tight',sans-serif] tracking-tight">
                        <span class="text-sky-400">Proyek</span> Kalender Saya
                    </h1>
                    <p class="text-slate-300 max-w-2xl text-sm sm:text-base">
                        Semua proyek kalender yang Anda simpan. Edit, hapus, cetak PDF, atau checkout untuk produksi cetak fisik.
                    </p>
                </div>
                <a href="{{ route('kalender-pelari.wizard') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-neon text-slate-950 font-bold text-sm hover:bg-lime-300 transition-colors shadow-[0_0_0_1px_rgba(204,255,0,0.35)]">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Buat Proyek Baru
                </a>
            </div>

            @if($can_create_new === false)
                <div class="mt-5 rounded-xl border border-amber-400/30 bg-amber-400/5 p-4 flex items-start gap-3">
                    <div class="shrink-0 text-amber-400 text-lg pt-0.5"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="flex-1">
                        <h3 class="font-bold text-amber-200 text-sm mb-1">Free Tier Penuh ({{ $total_owned }}/{{ $free_limit }} proyek)</h3>
                        <p class="text-slate-300 text-sm">Anda sudah mencapai batas free tier {{ $free_limit }} proyek. Hapus proyek yang tidak digunakan atau upgrade paket untuk menambah kapasitas.</p>
                    </div>
                </div>
            @else
                <div class="mt-5 rounded-xl border border-sky-400/20 bg-sky-400/5 p-4 flex items-start gap-3">
                    <div class="shrink-0 text-sky-400 text-lg pt-0.5"><i class="fa-solid fa-inbox"></i></div>
                    <div class="flex-1 text-sm">
                        <span class="font-bold text-sky-200">Kapasitas:</span>
                        <span class="text-slate-200 ml-2">{{ $total_owned }} / {{ $free_limit }} proyek free tersimpan.</span>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mt-5 rounded-xl border border-emerald-400/30 bg-emerald-400/5 p-4 text-emerald-200 text-sm">
                    <i class="fa-solid fa-circle-check text-emerald-400 mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif
        </header>

        @if($projects->isEmpty())
            <section class="rounded-2xl border border-dashed border-slate-700 bg-slate-900/30 p-16 text-center">
                <div class="text-slate-500 text-6xl mb-5"><i class="fa-regular fa-folder-open"></i></div>
                <h2 class="text-2xl font-bold text-slate-100 mb-2">Belum ada proyek tersimpan</h2>
                <p class="text-slate-300 mb-7 max-w-md mx-auto">
                    Coba buat kalender pertama Anda lewat wizard 5 langkah — tanpa login pun bisa, lalu login kapan saja untuk menyimpan permanen.
                </p>
                <a href="{{ route('kalender-pelari.wizard') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-neon text-slate-950 font-bold hover:bg-lime-300 transition-colors">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Mulai Buat Kalender
                </a>
            </section>
        @else
            <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($projects as $p)
                    <article class="group relative rounded-2xl overflow-hidden border border-slate-800 bg-slate-900/60 hover:border-sky-400/40 transition-colors">
                        <a href="{{ $p->routeEditor() }}" class="block">
                            <div class="aspect-[4/3] bg-gradient-to-br from-slate-800 to-slate-950 relative overflow-hidden">
                                <div class="absolute inset-0 opacity-40 pattern-dots pattern-slate-700 pattern-bg-transparent pattern-size-2"></div>
                                <div class="absolute top-3 left-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-mono uppercase tracking-wider
                                    {{ $p->status === 'ACTIVE' ? 'bg-emerald-400/10 text-emerald-300 border border-emerald-400/20'
                                       : ($p->status === 'DRAFT_TRIAL' ? 'bg-amber-400/10 text-amber-300 border border-amber-400/20'
                                       : 'bg-slate-400/10 text-slate-300 border border-slate-400/20') }}">
                                    {{ $p->status }}
                                </div>
                                <div class="absolute top-3 right-3 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-mono text-slate-300 bg-slate-800/70 border border-slate-700">
                                    <i class="fa-solid fa-calendar-days text-[10px] text-slate-400"></i>
                                    {{ $p->year }}
                                </div>
                                <div class="absolute bottom-0 inset-x-0 h-1.5
                                    {{ $p->format_type === 'A4_LANDSCAPE' ? 'bg-sky-400/70' :
                                       ($p->format_type === 'A3_LANDSCAPE' ? 'bg-violet-400/70' :
                                       ($p->format_type === 'DESK_CALENDAR' ? 'bg-emerald-400/70' : 'bg-pink-400/70')) }}"></div>
                            </div>
                        </a>
                        <div class="p-5">
                            <a href="{{ $p->routeEditor() }}" class="block">
                                <h3 class="text-lg font-bold text-slate-50 mb-1 group-hover:text-sky-300 transition-colors truncate">
                                    {{ $p->name }}
                                </h3>
                                <p class="text-xs text-slate-400 mb-4 font-mono">
                                    {{ $p->format_type }} · {{ $p->pages_count ?? 0 }} halaman · v{{ $p->version_counter ?? 1 }}
                                </p>
                            </a>
                            <div class="flex items-center gap-2 text-xs">
                                <a href="{{ $p->routeEditor() }}"
                                   class="flex-1 inline-flex justify-center items-center gap-1.5 px-3 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-100 font-semibold border border-slate-700 transition-colors">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Edit
                                </a>
                                <a href="{{ route('kalender-pelari.print.preview', $p) }}"
                                   class="inline-flex items-center justify-center w-10 h-10 rounded-md bg-slate-800 hover:bg-violet-400/10 text-slate-300 hover:text-violet-300 border border-slate-700 hover:border-violet-400/30 transition-colors"
                                   title="Cetak Preview">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <form method="POST"
                                      action="{{ route('kalender-pelari.destroy', $p) }}"
                                      onsubmit="return confirm('Hapus proyek {{ \Illuminate\Support\Str::limit($p->name, 40) }}? Data halaman dan elemen ikut terhapus permanen.');"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-10 h-10 rounded-md bg-slate-800 hover:bg-rose-500/10 text-slate-300 hover:text-rose-300 border border-slate-700 hover:border-rose-500/30 transition-colors"
                                            title="Hapus Proyek">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            @if($projects->hasPages())
                <nav class="mt-12 flex justify-center">
                    {{ $projects->links() }}
                </nav>
            @endif
        @endif
    </div>

    <div class="max-w-7xl mx-auto mt-20 pt-10 border-t border-slate-800/60 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
        <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-5">
            <div class="text-sky-400 text-lg mb-2"><i class="fa-solid fa-layer-group"></i></div>
            <h4 class="font-bold text-slate-100 mb-1">3 Proyek Free Tier</h4>
            <p class="text-slate-400">Simpan hingga 3 desain kalender permanen secara gratis tanpa batas waktu.</p>
        </div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-5">
            <div class="text-emerald-400 text-lg mb-2"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <h4 class="font-bold text-slate-100 mb-1">Autosave 2T</h4>
            <p class="text-slate-400">Setiap perubahan disimpan otomatis dual-tier: localStorage client + server DB.</p>
        </div>
        <div class="rounded-xl border border-slate-800 bg-slate-900/40 p-5">
            <div class="text-violet-400 text-lg mb-2"><i class="fa-solid fa-print"></i></div>
            <h4 class="font-bold text-slate-100 mb-1">Cetak PDF 300 DPI</h4>
            <p class="text-slate-400">Export PDF siap cetak CMYK atau lanjutkan checkout produksi cetak fisik premium.</p>
        </div>
    </div>
</main>
@endsection
