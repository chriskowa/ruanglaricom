@extends('layouts.pacerhub')

@section('title', 'Admin - Master GPX')

@section('content')
    @php($withSidebar = true)

    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8">
        <div class="max-w-6xl mx-auto">
            <div class="flex items-start justify-between gap-4 flex-wrap mb-6">
                <div>
                    <h1 class="text-3xl font-black text-white">Master GPX</h1>
                    <p class="text-slate-400 mt-1">Kelola file GPX untuk event lari Indonesia.</p>
                </div>
                <a href="{{ route('admin.master-gpx.create') }}" class="px-4 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                    Tambah GPX
                </a>
            </div>

            @if(session('success'))
                <div class="mb-4 bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-900/60 text-slate-400 uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-left">Judul</th>
                                <th class="px-4 py-3 text-left">Event</th>
                                <th class="px-4 py-3 text-left">Jarak</th>
                                <th class="px-4 py-3 text-left">Elev</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($items as $it)
                                <tr class="hover:bg-slate-900/40 transition">
                                    <td class="px-4 py-3 font-bold text-white">{{ $it->title }}</td>
                                    <td class="px-4 py-3 text-slate-300">
                                        {{ $it->runningEvent?->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-300">
                                        {{ $it->distance_km ? number_format((float) $it->distance_km, 2) . ' km' : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-300">
                                        @if($it->elevation_gain_m !== null)
                                            +{{ $it->elevation_gain_m }}m
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($it->is_published)
                                            <span class="px-2 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-black">Published</span>
                                        @else
                                            <span class="px-2 py-1 rounded-lg bg-slate-800 border border-slate-700 text-slate-300 text-xs font-black">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('tools.pace-pro.gpx', $it) }}" target="_blank" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 transition">
                                                View
                                            </a>
                                            <a href="{{ route('admin.master-gpx.edit', $it) }}" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 transition">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.master-gpx.destroy', $it) }}" onsubmit="return confirm('Hapus Master GPX ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-2 rounded-xl bg-red-500/10 border border-red-500/30 text-red-300 font-bold hover:bg-red-500/20 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-slate-500 font-semibold">Belum ada GPX.</td>
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

