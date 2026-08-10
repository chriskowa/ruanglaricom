@extends('layouts.pacerhub')

@section('title', 'My GPX - Kelola Rute Saya')

@section('content')
    @php($withSidebar = true)

    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                <div>
                    <h1 class="text-3xl font-black text-white flex items-center gap-2">
                        <i class="fas fa-map-marked-alt text-neon"></i>
                        <span>My GPX</span>
                    </h1>
                    <p class="text-slate-400 mt-1 text-sm">Kelola file GPX rute lari yang Anda unggah dan pantau status publikasinya.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('gpx.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 text-xs transition flex items-center gap-2">
                        <i class="fas fa-globe text-xs"></i>
                        <span>Katalog Database GPX</span>
                    </a>
                    <button onclick="if(window.open_gpx_modal){window.open_gpx_modal();}else{location.href='{{ route('gpx.index') }}';}" class="px-4 py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition flex items-center gap-2 shadow-lg shadow-neon/10">
                        <i class="fas fa-cloud-arrow-up text-xs"></i>
                        <span>Unggah GPX Baru (+10 PTS)</span>
                    </button>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2">
                    <i class="fas fa-check-circle text-emerald-400"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Search & Filter Bar -->
            <form method="GET" action="{{ route('runner.gpx.index') }}" class="mb-6 bg-slate-900/60 border border-slate-800 p-4 rounded-2xl flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul rute / kota..." class="w-64 bg-slate-950 border border-slate-800 rounded-xl pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-slate-700">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    </div>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition">
                        Cari
                    </button>

                    @if(request('q'))
                        <a href="{{ route('runner.gpx.index') }}" class="text-xs text-slate-400 hover:text-white underline">Reset Filter</a>
                    @endif
                </div>

                <div class="text-xs text-slate-400 font-semibold">
                    Total Rute Saya: <span class="text-white font-bold">{{ $items->total() }}</span>
                </div>
            </form>

            <!-- Table / List -->
            @if($items->count() > 0)
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden shadow-xl">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-3.5 px-4">Nama Rute</th>
                                    <th class="py-3.5 px-4">Lokasi</th>
                                    <th class="py-3.5 px-4">Jarak</th>
                                    <th class="py-3.5 px-4">Elevasi</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4">Tanggal Upload</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60">
                                @foreach($items as $item)
                                    <tr class="hover:bg-slate-800/40 transition">
                                        <td class="py-3.5 px-4">
                                            <div class="font-bold text-white text-sm">
                                                <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}" target="_blank" class="hover:text-neon transition">
                                                    {{ $item->title }}
                                                </a>
                                            </div>
                                            @if($item->slug)
                                                <div class="text-[10px] text-slate-500 font-mono mt-0.5">slug: {{ $item->slug }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-300 font-medium">
                                            {{ $item->city ?: '-' }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-black text-white">{{ $item->distance_km ? number_format((float) $item->distance_km, 2) . ' km' : '-' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2 text-[11px] font-bold">
                                                <span class="text-emerald-400">+{{ $item->elevation_gain_m ?? 0 }}m</span>
                                                <span class="text-slate-600">/</span>
                                                <span class="text-rose-400">-{{ $item->elevation_loss_m ?? 0 }}m</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($item->is_published)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                    <span>Published</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-500/10 border border-amber-500/30 text-amber-400">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                                    <span>Moderasi (Draft)</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-400">
                                            {{ $item->created_at ? $item->created_at->format('d M Y, H:i') : '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('gpx.show', $item->slug ?: $item->id) }}" target="_blank" class="p-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition" title="Lihat Halaman Detail">
                                                    <i class="fas fa-eye text-xs"></i>
                                                </a>
                                                <a href="{{ route('runner.gpx.edit', $item) }}" class="p-2 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition" title="Edit Detail Rute">
                                                    <i class="fas fa-pen text-xs"></i>
                                                </a>
                                                <form method="POST" action="{{ route('runner.gpx.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus rute GPX ini?')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-2 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 transition" title="Hapus Rute">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-6">
                    {{ $items->links() }}
                </div>
            @else
                <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-slate-800/80 border border-slate-700 flex items-center justify-center mx-auto mb-4 text-slate-400 text-2xl">
                        <i class="fas fa-route"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Belum Ada Rute GPX</h3>
                    <p class="text-slate-400 text-xs mt-1 max-w-md mx-auto">Anda belum pernah mengunggah rute GPX. Unggah file GPX rute lari favorit Anda dan dapatkan +10 Poin Runner!</p>
                    <button onclick="if(window.open_gpx_modal){window.open_gpx_modal();}else{location.href='{{ route('gpx.index') }}';}" class="mt-5 px-5 py-2.5 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition inline-flex items-center gap-2 shadow-lg shadow-neon/10">
                        <i class="fas fa-cloud-arrow-up text-xs"></i>
                        <span>Unggah Rute GPX Sekarang</span>
                    </button>
                </div>
            @endif
        </div>
    </div>
@endsection
