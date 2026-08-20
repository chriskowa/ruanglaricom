@extends('layouts.pacerhub')

@section('title', 'Admin - Master GPX & Saran Nama')

@section('content')
    @php($withSidebar = true)

    <div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header Bar -->
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div>
                    <h1 class="text-3xl font-black text-white">Master GPX</h1>
                    <p class="text-slate-400 mt-1 text-sm">Kelola file GPX rute lari dan moderasi saran penamaan rute dari komunitas.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('gpx.index') }}" target="_blank" class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 text-xs transition">
                        <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i>
                        Katalog Publik
                    </a>
                    <a href="{{ route('admin.master-gpx.create') }}" class="px-4 py-2 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition shadow-lg shadow-neon/10">
                        <i class="fa-solid fa-plus mr-1"></i>
                        Tambah GPX
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-emerald-500/10 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl text-sm font-semibold flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-500/10 border border-rose-500/40 text-rose-300 px-4 py-3 rounded-xl text-sm font-semibold flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Navigation Tabs -->
            <div class="flex items-center gap-2 border-b border-slate-800 pb-1">
                <a href="{{ route('admin.master-gpx.index', ['tab' => 'routes']) }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $tab !== 'suggestions' ? 'bg-neon text-dark shadow-sm' : 'bg-slate-900 text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Daftar Rute GPX</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black {{ $tab !== 'suggestions' ? 'bg-dark/20 text-dark' : 'bg-slate-800 text-slate-400' }}">{{ $items->total() }}</span>
                </a>

                <a href="{{ route('admin.master-gpx.index', ['tab' => 'suggestions']) }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $tab === 'suggestions' ? 'bg-neon text-dark shadow-sm' : 'bg-slate-900 text-slate-300 hover:text-white hover:bg-slate-800 border border-slate-800' }}">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Saran Nama Rute</span>
                    @if($pendingSuggestionsCount > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse">{{ $pendingSuggestionsCount }} baru</span>
                    @else
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $tab === 'suggestions' ? 'bg-dark/20 text-dark' : 'bg-slate-800 text-slate-400' }}">{{ $suggestions->total() }}</span>
                    @endif
                </a>
            </div>

            @if($tab === 'suggestions')
                <!-- TAB: SARAN NAMA RUTE -->
                <form method="GET" action="{{ route('admin.master-gpx.index') }}" class="bg-slate-900/60 border border-slate-800 p-4 rounded-xl flex flex-wrap items-center justify-between gap-3">
                    <input type="hidden" name="tab" value="suggestions">
                    <div class="flex items-center gap-3 flex-wrap">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama rute / usulan..." class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-neon">
                        
                        <select name="suggestion_status" class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-neon">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(request('suggestion_status') == 'pending')>Menunggu Tinjauan (Pending)</option>
                            <option value="approved" @selected(request('suggestion_status') == 'approved')>Disetujui (Approved)</option>
                            <option value="rejected" @selected(request('suggestion_status') == 'rejected')>Ditolak (Rejected)</option>
                        </select>

                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition cursor-pointer">
                            Filter
                        </button>
                    </div>

                    <div class="text-xs text-slate-400 font-semibold">
                        Total Saran: <span class="text-white font-bold">{{ $suggestions->total() }}</span>
                    </div>
                </form>

                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-900/80 text-slate-400 uppercase text-xs tracking-wider border-b border-slate-800">
                                <tr>
                                    <th class="px-4 py-3 text-left">Rute Asli</th>
                                    <th class="px-4 py-3 text-left">Usulan Nama Baru</th>
                                    <th class="px-4 py-3 text-left">Pengusul</th>
                                    <th class="px-4 py-3 text-left">Alasan / Catatan</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800">
                                @forelse($suggestions as $sug)
                                    <tr class="hover:bg-slate-900/40 transition">
                                        <td class="px-4 py-3">
                                            @if($sug->masterGpx)
                                                <a href="{{ route('gpx.show', $sug->masterGpx->slug ?: $sug->masterGpx->id) }}" target="_blank" class="font-bold text-white hover:text-neon transition">
                                                    {{ $sug->masterGpx->title }}
                                                </a>
                                                <div class="text-xs text-slate-400">{{ $sug->masterGpx->city ?? 'Indonesia' }} ({{ number_format((float)$sug->masterGpx->distance_km, 2) }} km)</div>
                                            @else
                                                <span class="text-slate-500 italic">Rute telah dihapus</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-black text-neon text-sm flex items-center gap-1.5">
                                                <i class="fa-solid fa-arrow-right text-[10px] text-slate-500"></i>
                                                <span>{{ $sug->proposed_title }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            <div class="font-semibold text-white">{{ $sug->user?->name ?? 'Runner' }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $sug->user?->email }}</div>
                                            <div class="text-[10px] text-slate-500 mt-0.5">{{ $sug->created_at->diffForHumans() }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-slate-300 max-w-xs">
                                            @if($sug->reason)
                                                <p class="italic text-slate-300 bg-slate-900/80 p-2 rounded-lg border border-slate-800">"{{ $sug->reason }}"</p>
                                            @else
                                                <span class="text-slate-600">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($sug->status === 'approved')
                                                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-bold inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-check text-[10px]"></i>
                                                    Diterapkan
                                                </span>
                                            @elseif($sug->status === 'rejected')
                                                <span class="px-2.5 py-1 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs font-bold inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                                    Ditolak
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-clock text-[10px]"></i>
                                                    Menunggu
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($sug->status === 'pending')
                                                    <!-- Approve Form -->
                                                    <form method="POST" action="{{ route('admin.master-gpx.suggestions.approve', $sug) }}" onsubmit="return confirm('Terapkan saran nama rute ini? Judul rute di katalog akan otomatis diubah.')">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-neon text-dark font-black hover:bg-neon/90 text-xs transition cursor-pointer shadow-sm flex items-center gap-1" title="Terapkan Nama Usulan">
                                                            <i class="fa-solid fa-check"></i>
                                                            <span>Terapkan</span>
                                                        </button>
                                                    </form>

                                                    <!-- Reject Form -->
                                                    <form method="POST" action="{{ route('admin.master-gpx.suggestions.reject', $sug) }}" onsubmit="return confirm('Tolak saran nama rute ini?')">
                                                        @csrf
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-rose-950/40 text-slate-300 hover:text-rose-300 border border-slate-700 hover:border-rose-500/40 font-bold text-xs transition cursor-pointer" title="Tolak Saran">
                                                            <i class="fa-solid fa-xmark"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Delete Form -->
                                                <form method="POST" action="{{ route('admin.master-gpx.suggestions.destroy', $sug) }}" onsubmit="return confirm('Hapus riwayat saran ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2 py-1.5 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 text-xs transition cursor-pointer" title="Hapus">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-12 text-center text-slate-500 font-semibold">
                                            <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-600 block"></i>
                                            Belum ada saran penamaan rute dari pengguna.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $suggestions->links() }}
                </div>

            @else
                <!-- TAB: DAFTAR RUTE GPX -->
                <form method="GET" action="{{ route('admin.master-gpx.index') }}" class="bg-slate-900/60 border border-slate-800 p-4 rounded-xl flex flex-wrap items-center justify-between gap-3">
                    <input type="hidden" name="tab" value="routes">
                    <div class="flex items-center gap-3 flex-wrap">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul / kota..." class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-neon">
                        
                        <select name="status" class="bg-slate-950 border border-slate-800 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-neon">
                            <option value="">Semua Status</option>
                            <option value="published" @selected(request('status') == 'published')>Published</option>
                            <option value="draft" @selected(request('status') == 'draft')>Belum Publish (Draft)</option>
                        </select>

                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition cursor-pointer">
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
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 font-bold hover:bg-amber-500/20 text-xs transition cursor-pointer">
                                                            Unpublish
                                                        </button>
                                                    @else
                                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 font-bold hover:bg-emerald-500/20 text-xs transition cursor-pointer">
                                                            Publish
                                                        </button>
                                                    @endif
                                                </form>

                                                <a href="{{ route('admin.master-gpx.edit', $it) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-slate-200 font-bold hover:bg-slate-700 text-xs transition cursor-pointer">
                                                    Edit
                                                </a>

                                                <form method="POST" action="{{ route('admin.master-gpx.destroy', $it) }}" onsubmit="return confirm('Hapus Master GPX ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-red-500/10 border border-red-500/30 text-red-300 font-bold hover:bg-red-500/20 text-xs transition cursor-pointer">
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
            @endif
        </div>
    </div>
@endsection
