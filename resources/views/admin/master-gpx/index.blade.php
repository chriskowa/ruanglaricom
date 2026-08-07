@extends('layouts.pacerhub')

@section('title', 'Admin - Master GPX')

@section('content')
    @php($withSidebar = true)

    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                <div>
                    <h1 class="text-3xl font-black text-white">Master GPX</h1>
                    <p class="text-slate-400 mt-1 text-sm">Kelola dan publikasikan file GPX yang diunggah oleh runner.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('gpx.index') }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 text-xs transition">
                        Lihat Katalog Publik
                    </a>
                    <a href="{{ route('admin.master-gpx.create') }}" class="px-4 py-2 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition">
                        Tambah GPX
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Admin Filter Bar -->
            <form method="GET" action="{{ route('admin.master-gpx.index') }}" class="mb-4 bg-slate-900/60 border border-slate-800 p-4 rounded-xl flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul / kota..." class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none">
                    
                    <select name="status" class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none">
                        <option value="">Semua Status</option>
                        <option value="published" @selected(request('status') == 'published')>Published</option>
                        <option value="draft" @selected(request('status') == 'draft')>Belum Publish (Draft)</option>
                    </select>

                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition">
                        Filter
                    </button>
                </div>

                <div class="text-xs text-slate-400 font-semibold">
                    Total: <span class="text-white font-bold">{{ $items->total() }}</span> GPX
                </div>
            </form>

            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs tracking-wider border-b border-slate-800">
                            <tr>
                                <th class="px-4 py-3 text-left">Judul & Kota</th>
                                <th class="px-4 py-3 text-left">Pengunggah</th>
                                <th class="px-4 py-3 text-left">Event</th>
                                <th class="px-4 py-3 text-left">Jarak</th>
                                <th class="px-4 py-3 text-left">Elevasi</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($items as $it)
                                <tr class="hover:bg-slate-900/40 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-white">{{ $it->title }}</div>
                                        <div class="text-xs text-slate-400">{{ $it->city ?? 'Indonesia' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-300 text-xs">
                                        @if($it->user)
                                            <div class="font-semibold text-white">{{ $it->user->name }}</div>
                                            <div class="text-[10px] text-slate-500">{{ $it->user->email }}</div>
                                        @else
                                            <span class="text-slate-500">Admin</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-300 text-xs">
                                        {{ $it->event?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-300 font-mono text-xs">
                                        {{ $it->distance_km ? number_format((float) $it->distance_km, 2) . ' km' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-300 text-xs">
                                        @if($it->elevation_gain_m !== null)
                                            <span class="text-emerald-400 font-semibold">+{{ $it->elevation_gain_m }}m</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($it->is_published)
                                            <span class="px-2 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-bold">Published</span>
                                        @else
                                            <span class="px-2 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold">Belum Publish</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            
                                            <!-- Toggle Publish Button -->
                                            <form method="POST" action="{{ route('admin.master-gpx.toggle-publish', $it) }}">
                                                @csrf
                                                @if($it->is_published)
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 font-bold hover:bg-amber-500/20 text-xs transition">
                                                        Unpublish
                                                    </button>
                                                @else
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 font-bold hover:bg-emerald-500/20 text-xs transition">
                                                        Publish
                                                    </button>
                                                @endif
                                            </form>

                                            <a href="{{ route('admin.master-gpx.edit', $it) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 text-xs transition">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('admin.master-gpx.destroy', $it) }}" onsubmit="return confirm('Hapus Master GPX ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 font-bold hover:bg-red-500/20 text-xs transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-slate-500 font-semibold">Belum ada GPX terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
