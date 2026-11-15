@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Manajemen Proyek Kalender - Admin Ruang Lari')

@section('content')
<div class="container mx-auto px-4 md:px-8 py-8 pt-0">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                MANAJEMEN PROYEK KALENDER PELARI
            </h1>
            <p class="text-slate-300 text-sm mt-1">
                Kelola kalender yang dibuat pengguna, verifikasi desain visual, dan pantau antrean pesanan cetak fisik.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.kalender-pelari.templates.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold rounded-md border border-slate-700 transition flex items-center gap-2">
                <i class="fas fa-images"></i>
                <span>Kelola Template Idola</span>
            </a>
            <a href="{{ route('kalender-pelari.studio') }}" target="_blank" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-sm transition flex items-center gap-2">
                <i class="fas fa-external-link-alt"></i>
                <span>Buka Studio Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-md bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Proyek</div>
            <div class="text-2xl font-bold text-white font-mono mt-1">{{ number_format($stats['total']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Semua proyek terdaftar & uji coba</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Siap / Antrean Cetak</div>
            <div class="text-2xl font-bold text-emerald-400 font-mono mt-1">{{ number_format($stats['production_active']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Status PRODUCTION_ACTIVE</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-sky-400">Draf Pengguna</div>
            <div class="text-2xl font-bold text-sky-400 font-mono mt-1">{{ number_format($stats['saved']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Tersimpan di akun runner</div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs font-semibold uppercase tracking-wider text-amber-400">Uji Coba Tamu</div>
            <div class="text-2xl font-bold text-amber-400 font-mono mt-1">{{ number_format($stats['trial']) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">Sesi coba instan anonim</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.kalender-pelari.projects.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="lg:col-span-5 relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama proyek, nama user, email, UUID..." class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 pl-9 text-sm text-white placeholder-slate-400 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none">
                <i class="fas fa-search text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
            </div>

            <!-- Status Filter -->
            <div class="lg:col-span-3">
                <select name="status" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-sm text-white focus:border-red-500 outline-none">
                    <option value="">Semua Status</option>
                    <option value="PRODUCTION_ACTIVE" {{ request('status') === 'PRODUCTION_ACTIVE' ? 'selected' : '' }}>Siap Cetak (PRODUCTION_ACTIVE)</option>
                    <option value="PENDING_CHECKOUT" {{ request('status') === 'PENDING_CHECKOUT' ? 'selected' : '' }}>Menunggu Checkout (PENDING_CHECKOUT)</option>
                    <option value="DRAFT_SAVED" {{ request('status') === 'DRAFT_SAVED' ? 'selected' : '' }}>Draf Tersimpan (DRAFT_SAVED)</option>
                    <option value="DRAFT_TRIAL" {{ request('status') === 'DRAFT_TRIAL' ? 'selected' : '' }}>Uji Coba (DRAFT_TRIAL)</option>
                    <option value="ARCHIVED" {{ request('status') === 'ARCHIVED' ? 'selected' : '' }}>Selesai / Diarsipkan (ARCHIVED)</option>
                </select>
            </div>

            <!-- Format Filter -->
            <div class="lg:col-span-2">
                <select name="format_type" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-sm text-white focus:border-red-500 outline-none">
                    <option value="">Semua Format</option>
                    <option value="A4_LANDSCAPE" {{ request('format_type') === 'A4_LANDSCAPE' ? 'selected' : '' }}>A4 Landscape</option>
                    <option value="A3_LANDSCAPE" {{ request('format_type') === 'A3_LANDSCAPE' ? 'selected' : '' }}>A3 Landscape</option>
                    <option value="DESK_CALENDAR" {{ request('format_type') === 'DESK_CALENDAR' ? 'selected' : '' }}>Kalender Meja</option>
                    <option value="SQUARE" {{ request('format_type') === 'SQUARE' ? 'selected' : '' }}>Persegi</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md transition text-center">
                    Terapkan
                </button>
                @if(request()->hasAny(['q', 'status', 'format_type', 'year']))
                    <a href="{{ route('admin.kalender-pelari.projects.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium rounded-md transition" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Container -->
    <div class="bg-slate-900 border border-slate-800 rounded-lg overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-200">
                <thead class="bg-slate-950 text-slate-400 text-xs uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="px-4 py-3.5">ID & Nama Proyek</th>
                        <th class="px-4 py-3.5">Pembuat / Akun</th>
                        <th class="px-4 py-3.5">Format & Tahun</th>
                        <th class="px-4 py-3.5">Status Cetak</th>
                        <th class="px-4 py-3.5">Catatan Produksi</th>
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @if(count($projects) > 0)
                        @foreach($projects as $p)
                        <tr class="hover:bg-slate-800/40 transition">
                            <!-- ID & Nama Proyek -->
                            <td class="px-4 py-3.5">
                                <div class="font-semibold text-white">
                                    {{ $p->name ?: 'Tanpa Judul' }}
                                </div>
                                <div class="text-[11px] font-mono text-slate-400 flex items-center gap-1.5 mt-0.5">
                                    <span>#{{ $p->id }}</span>
                                    @if($p->is_anonymous)
                                        <span class="text-amber-400" title="Proyek Tamu / Uji Coba Anonim">[Trial UUID: {{ substr($p->anonymous_uuid, 0, 8) }}...]</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Pembuat / Akun -->
                            <td class="px-4 py-3.5">
                                @if($p->user)
                                    <div class="font-medium text-white">{{ $p->user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $p->user->email }}</div>
                                @else
                                    <div class="text-xs text-amber-300 font-medium">Tamu / Pengunjung Anonim</div>
                                    <div class="text-[11px] text-slate-400">Belum login</div>
                                @endif
                            </td>

                            <!-- Format & Tahun -->
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-800 text-slate-200 border border-slate-700/60">
                                    {{ str_replace('_', ' ', $p->format_type) }}
                                </span>
                                <div class="text-xs text-slate-400 font-mono mt-1">Tahun {{ $p->year }}</div>
                            </td>

                            <!-- Status Cetak -->
                            <td class="px-4 py-3.5">
                                @php
                                    $statusMap = [
                                        'PRODUCTION_ACTIVE' => ['bg' => 'bg-emerald-500/15', 'border' => 'border-emerald-500/40', 'text' => 'text-emerald-400', 'label' => 'Siap Dicetak'],
                                        'PENDING_CHECKOUT'  => ['bg' => 'bg-purple-500/15',  'border' => 'border-purple-500/40',  'text' => 'text-purple-400',  'label' => 'Checkout'],
                                        'DRAFT_SAVED'       => ['bg' => 'bg-sky-500/15',     'border' => 'border-sky-500/40',     'text' => 'text-sky-400',     'label' => 'Draf Tersimpan'],
                                        'DRAFT_TRIAL'       => ['bg' => 'bg-amber-500/15',   'border' => 'border-amber-500/40',   'text' => 'text-amber-400',   'label' => 'Uji Coba'],
                                        'ARCHIVED'          => ['bg' => 'bg-slate-700/30',   'border' => 'border-slate-700',      'text' => 'text-slate-400',   'label' => 'Selesai / Arsip'],
                                        'EXPIRED_TRIAL'     => ['bg' => 'bg-rose-500/15',    'border' => 'border-rose-500/40',    'text' => 'text-rose-400',    'label' => 'Kedaluwarsa'],
                                    ];
                                    $st = $statusMap[$p->status] ?? ['bg' => 'bg-slate-800', 'border' => 'border-slate-700', 'text' => 'text-slate-300', 'label' => $p->status];
                                @endphp
                                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-semibold border {{ $st['bg'] }} {{ $st['border'] }} {{ $st['text'] }}">
                                    {{ $st['label'] }}
                                </span>
                            </td>

                            <!-- Catatan Produksi -->
                            <td class="px-4 py-3.5 max-w-xs">
                                @if($p->admin_notes)
                                    <p class="text-xs text-slate-300 truncate" title="{{ $p->admin_notes }}">
                                        {{ $p->admin_notes }}
                                    </p>
                                @else
                                    <span class="text-xs text-slate-500 italic">Belum ada catatan</span>
                                @endif
                            </td>

                            <!-- Tanggal -->
                            <td class="px-4 py-3.5 text-xs text-slate-400 font-mono whitespace-nowrap">
                                <div>{{ $p->created_at ? $p->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $p->created_at ? $p->created_at->format('H:i') : '' }} WIB</div>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Buka di Studio -->
                                    <a href="{{ $p->routeEditor() }}" target="_blank" class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-md text-xs font-medium border border-slate-700 transition" title="Buka di Kalender Studio">
                                        <i class="fas fa-desktop"></i>
                                        <span class="ml-1 hidden xl:inline">Studio</span>
                                    </a>

                                    <!-- Edit Status & Notes Modal Trigger -->
                                    <button type="button" onclick="openStatusModal({{ $p->id }}, '{{ $p->status }}', '{{ addslashes($p->admin_notes ?? '') }}')" class="px-2.5 py-1.5 bg-sky-950/60 hover:bg-sky-900/80 text-sky-300 rounded-md text-xs font-medium border border-sky-800/60 transition" title="Kelola status cetak & catatan">
                                        <i class="fas fa-tasks"></i>
                                        <span class="ml-1 hidden xl:inline">Status</span>
                                    </button>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admin.kalender-pelari.projects.destroy', $p) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini? Proyek yang dihapus dapat dipulihkan melalui soft-delete.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1.5 bg-slate-800 hover:bg-rose-900/60 text-slate-400 hover:text-rose-300 rounded-md text-xs transition" title="Hapus Proyek">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-sm">
                                <div class="w-12 h-12 mx-auto mb-3 rounded-lg bg-slate-800 flex items-center justify-center text-slate-400">
                                    <i class="fas fa-calendar-times text-xl"></i>
                                </div>
                                <div class="font-semibold text-white">Tidak ada proyek kalender ditemukan</div>
                                <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter yang dipilih.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($projects->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/40">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Update Status & Catatan Produksi -->
<div id="statusModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-lg max-w-lg w-full p-6 shadow-xl">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <h3 class="text-lg font-bold text-white tracking-tight" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                Kelola Status & Catatan Cetak
            </h3>
            <button type="button" onclick="closeStatusModal()" class="text-slate-400 hover:text-white">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        <form id="statusForm" method="POST" action="" class="mt-4 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Status Produksi Kalender
                </label>
                <select name="status" id="modalStatusSelect" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2.5 text-sm text-white focus:border-red-500 outline-none">
                    <option value="PRODUCTION_ACTIVE">Siap Dicetak / Antrean Cetak (PRODUCTION_ACTIVE)</option>
                    <option value="PENDING_CHECKOUT">Menunggu Pembayaran (PENDING_CHECKOUT)</option>
                    <option value="DRAFT_SAVED">Draf Disimpan Pengguna (DRAFT_SAVED)</option>
                    <option value="DRAFT_TRIAL">Sesi Uji Coba Anonim (DRAFT_TRIAL)</option>
                    <option value="ARCHIVED">Produksi Selesai / Dikirim / Arsip (ARCHIVED)</option>
                    <option value="EXPIRED_TRIAL">Kedaluwarsa (EXPIRED_TRIAL)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Catatan Operasional / Instruksi Cetak
                </label>
                <textarea name="admin_notes" id="modalAdminNotes" rows="4" placeholder="Misal: Kertas Art Paper 260gr, jilid spiral kawat hitam di atas, alamat kirim Jl. Sudirman No 12..." class="w-full bg-slate-950 border border-slate-800 rounded-md p-3 text-sm text-white placeholder-slate-400 focus:border-red-500 outline-none"></textarea>
                <p class="text-[11px] text-slate-400 mt-1">Catatan ini hanya dapat dilihat oleh tim admin dan percetakan.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeStatusModal()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-md transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openStatusModal(projectId, currentStatus, currentNotes) {
    const form = document.getElementById('statusForm');
    form.action = `/admin/kalender-pelari/proyek/${projectId}/status`;
    document.getElementById('modalStatusSelect').value = currentStatus;
    document.getElementById('modalAdminNotes').value = currentNotes || '';
    document.getElementById('statusModal').classList.remove('hidden');
}

function closeStatusModal() {
    document.getElementById('statusModal').classList.add('hidden');
}
</script>
@endsection
