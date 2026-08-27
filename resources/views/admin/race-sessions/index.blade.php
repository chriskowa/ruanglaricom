@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Manajemen Sesi Balap - Race Sessions')

@section('content')
<div class="min-h-screen pt-20 pb-12 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4 pb-2 border-b border-slate-800">
            <div>
                <a href="{{ route('admin.races.index') }}" class="text-slate-400 hover:text-white text-sm mb-2 inline-flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Kembali ke Race Master</span>
                </a>
                <h1 class="text-2xl md:text-3xl font-black text-white uppercase tracking-tight">Manajemen Sesi Balap</h1>
                <p class="text-slate-400 text-sm mt-1">Pantau seluruh sesi lomba aktif, lihat riwayat hasil, dan bersihkan sesi percobaan/sampah.</p>
            </div>
            
            <div class="flex items-center gap-2.5 flex-wrap w-full md:w-auto">
                <a href="{{ route('tools.race-master') }}" target="_blank" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow-sm">
                    <i class="fa-solid fa-stopwatch"></i>
                    <span>Buka Race Master App</span>
                </a>
                <form method="POST" action="{{ route('admin.race-sessions.clean-empty') }}" onsubmit="return confirm('Bersihkan semua sesi percobaan yang tidak memiliki catatan lap? Aksi ini tidak dapat dibatalkan.')">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition">
                        <i class="fa-solid fa-broom"></i>
                        <span>Bersihkan Sesi Kosong ({{ $emptySessions }})</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Flash Alerts -->
        @if (session('success'))
            <div class="bg-emerald-950/80 border border-emerald-700 text-emerald-200 p-4 rounded-2xl flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('info'))
            <div class="bg-indigo-950/80 border border-indigo-700 text-indigo-200 p-4 rounded-2xl flex items-center gap-3">
                <i class="fa-solid fa-circle-info text-indigo-400 text-base"></i>
                <span class="text-sm font-medium">{{ session('info') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-950/80 border border-red-700 text-red-200 p-4 rounded-2xl">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Quick Summary Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl space-y-1">
                <div class="text-xs font-bold uppercase text-slate-400 tracking-wider">Total Sesi Lomba</div>
                <div class="text-2xl sm:text-3xl font-black text-white">{{ number_format($totalSessions) }}</div>
                <div class="text-[11px] text-slate-500">Semua sesi tersimpan</div>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl space-y-1">
                <div class="text-xs font-bold uppercase text-indigo-400 tracking-wider">Sedang Berjalan</div>
                <div class="text-2xl sm:text-3xl font-black text-indigo-400">{{ number_format($runningSessions) }}</div>
                <div class="text-[11px] text-slate-500">Timer aktif live-sync</div>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl space-y-1">
                <div class="text-xs font-bold uppercase text-emerald-400 tracking-wider">Sesi Selesai (Finish)</div>
                <div class="text-2xl sm:text-3xl font-black text-emerald-400">{{ number_format($finishedSessions) }}</div>
                <div class="text-[11px] text-slate-500">Memiliki hasil resmi</div>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl space-y-1">
                <div class="text-xs font-bold uppercase text-amber-400 tracking-wider">Sesi Kosong / Tes</div>
                <div class="text-2xl sm:text-3xl font-black text-amber-400">{{ number_format($emptySessions) }}</div>
                <div class="text-[11px] text-slate-500">Tanpa catatan lap</div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <form method="GET" action="{{ route('admin.race-sessions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                <!-- Search Query -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Pencarian</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama race, kategori, atau slug..." 
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white placeholder:text-slate-500 focus:ring-2 focus:ring-indigo-500 outline-none text-sm">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Status Sesi</label>
                    <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white text-sm outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                        <option value="">Semua Status</option>
                        <option value="running" {{ request('status') === 'running' ? 'selected' : '' }}>Sedang Berjalan</option>
                        <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }}>Sudah Selesai</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft / Belum Start</option>
                        <option value="empty" {{ request('status') === 'empty' ? 'selected' : '' }}>Kosong (Tanpa Lap)</option>
                    </select>
                </div>

                <!-- Race Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Filter Race</label>
                    <select name="race_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-700 bg-slate-950 text-white text-sm outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                        <option value="">Semua Race</option>
                        @foreach ($races as $r)
                            <option value="{{ $r->id }}" {{ request('race_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-filter"></i>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('admin.race-sessions.index') }}" class="py-2.5 px-3.5 bg-slate-950 hover:bg-slate-800 border border-slate-700 text-slate-300 font-bold text-xs rounded-xl transition text-center" title="Reset filter">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Bulk Action Form & Data Table -->
        <form id="bulkDeleteForm" method="POST" action="{{ route('admin.race-sessions.bulk-destroy') }}" onsubmit="return confirmBulkDelete()">
            @csrf
            
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
                <!-- Table Header Actions Bar -->
                <div class="p-4 bg-slate-950/80 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-300">
                            <input type="checkbox" id="selectAllCheckbox" class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                            <span>Pilih Semua di Halaman Ini</span>
                        </label>
                        <span id="selectedCountBadge" class="hidden px-2.5 py-0.5 rounded-full bg-indigo-950 text-indigo-300 border border-indigo-700 text-xs font-mono font-bold">
                            0 dipilih
                        </span>
                    </div>

                    <button type="submit" id="bulkDeleteBtn" disabled class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                        <i class="fa-solid fa-trash"></i>
                        <span>Hapus Sesi Terpilih</span>
                    </button>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-800 text-left text-xs">
                        <thead class="bg-slate-950 text-slate-400 uppercase font-bold text-[11px] tracking-wider">
                            <tr>
                                <th class="p-4 w-10 text-center"></th>
                                <th class="p-4">Sesi & Kategori</th>
                                <th class="p-4">Nama Race / Event</th>
                                <th class="p-4">Dibuat Oleh</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-center">Catatan Laps</th>
                                <th class="p-4">Waktu Pelaksanaan</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @forelse ($sessions as $session)
                                <tr class="hover:bg-slate-950/60 transition-colors">
                                    <!-- Checkbox -->
                                    <td class="p-4 text-center">
                                        <input type="checkbox" name="session_ids[]" value="{{ $session->id }}" class="session-checkbox rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                                    </td>

                                    <!-- Session & Category -->
                                    <td class="p-4">
                                        <div class="font-mono font-black text-sm text-white flex items-center gap-2">
                                            <span>#{{ $session->slug ?: $session->id }}</span>
                                            @if ($session->category)
                                                <span class="px-2 py-0.5 rounded-lg bg-indigo-950 text-indigo-300 border border-indigo-700 text-[10px] font-bold">
                                                    {{ $session->category }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            @if ($session->distance_km) {{ (float) $session->distance_km }} KM @endif
                                            @if ($session->quota) • Kuota: {{ $session->quota }} @endif
                                        </div>
                                    </td>

                                    <!-- Race / Event -->
                                    <td class="p-4">
                                        @if ($session->race)
                                            <a href="{{ route('admin.races.show', $session->race) }}" class="font-bold text-white hover:text-indigo-400 transition-colors">
                                                {{ $session->race->name }}
                                            </a>
                                            <div class="text-[11px] text-slate-500">Race ID #{{ $session->race->id }}</div>
                                        @else
                                            <span class="text-slate-500">Race terhapus</span>
                                        @endif
                                    </td>

                                    <!-- Creator -->
                                    <td class="p-4 text-slate-300">
                                        @if ($session->creator)
                                            <div class="font-semibold text-slate-200">{{ $session->creator->name }}</div>
                                            <div class="text-[11px] text-slate-500">{{ $session->creator->email }}</div>
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="p-4 text-center">
                                        @if ($session->ended_at)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-emerald-950 text-emerald-300 border border-emerald-700 text-[10px] font-bold uppercase">
                                                <span>Finish</span>
                                            </span>
                                        @elseif ($session->started_at)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-indigo-950 text-indigo-300 border border-indigo-700 text-[10px] font-bold uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                                                <span>Running</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl bg-slate-800 text-slate-400 border border-slate-700 text-[10px] font-bold uppercase">
                                                Draft
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Laps & Certificates Count -->
                                    <td class="p-4 text-center font-mono">
                                        <div class="font-bold text-sm text-slate-200">{{ number_format($session->laps_count) }} laps</div>
                                        <div class="text-[10px] text-slate-500">{{ number_format($session->certificates_count) }} e-cert</div>
                                    </td>

                                    <!-- Timing / Dates -->
                                    <td class="p-4 text-slate-400 font-mono text-[11px]">
                                        @if ($session->started_at)
                                            <div>Start: <strong class="text-slate-200">{{ $session->started_at->format('d M Y H:i') }}</strong></div>
                                            @if ($session->ended_at)
                                                <div>End: <strong class="text-emerald-400">{{ $session->ended_at->format('H:i:s') }}</strong></div>
                                            @endif
                                        @else
                                            <div>Dibuat: {{ $session->created_at?->format('d M Y H:i') }}</div>
                                        @endif
                                    </td>

                                    <!-- Action Buttons -->
                                    <td class="p-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                            <!-- Public Results Link -->
                                            @if ($session->slug)
                                                <a href="{{ route('tools.race-master.results', ['slug' => $session->slug]) }}" target="_blank" class="px-2.5 py-1.5 rounded-lg bg-slate-950 hover:bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs transition" title="Lihat Hasil Publik">
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                                </a>
                                            @endif

                                            <!-- Reset Leaderboard -->
                                            @if ($session->laps_count > 0 || $session->started_at)
                                                <button type="button" onclick="submitResetSession('{{ route('admin.race-sessions.reset', $session) }}', '{{ $session->slug ?: $session->id }}')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-amber-950 hover:border-amber-700 hover:text-amber-300 border border-slate-700 text-slate-300 font-bold text-xs transition" title="Reset Waktu & Leaderboard">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            @endif

                                            <!-- Delete Session -->
                                            <button type="button" onclick="submitDeleteSession('{{ route('admin.race-sessions.destroy', $session) }}', '{{ $session->slug ?: $session->id }}')" class="px-2.5 py-1.5 rounded-lg bg-slate-950 hover:bg-red-950 hover:border-red-700 hover:text-red-300 border border-slate-700 text-slate-400 transition text-xs font-bold" title="Hapus Sesi">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-12 text-center text-slate-400">
                                        <div class="text-base font-bold text-slate-300">Tidak ada sesi balap yang ditemukan.</div>
                                        <p class="text-xs text-slate-500 mt-1">Coba ubah kata kunci pencarian atau reset filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                @if ($sessions->hasPages())
                    <div class="p-4 bg-slate-950 border-t border-slate-800">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>
        </form>

    </div>
</div>

<!-- Standalone Action Form for Single Item Reset / Delete -->
<form id="singleActionForm" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="_method" id="singleActionMethod" value="POST">
</form>

<script>
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.session-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCountBadge = document.getElementById('selectedCountBadge');

    function updateBulkState() {
        const checked = document.querySelectorAll('.session-checkbox:checked');
        const count = checked.length;
        
        if (count > 0) {
            bulkDeleteBtn.removeAttribute('disabled');
            selectedCountBadge.classList.remove('hidden');
            selectedCountBadge.textContent = `${count} sesi dipilih`;
        } else {
            bulkDeleteBtn.setAttribute('disabled', 'disabled');
            selectedCountBadge.classList.add('hidden');
        }

        if (checkboxes.length > 0 && count === checkboxes.length) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else if (count > 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkState();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkState);
    });

    function confirmBulkDelete() {
        const checked = document.querySelectorAll('.session-checkbox:checked');
        if (checked.length === 0) return false;
        return confirm(`Yakin ingin menghapus ${checked.length} sesi lomba terpilih secara permanen? Semua data lap dan sertifikat terkait akan ikut terhapus.`);
    }

    function submitDeleteSession(actionUrl, sessionCode) {
        if (!confirm(`Hapus sesi #${sessionCode}? Semua catatan lap dan sertifikat di sesi ini akan ikut terhapus.`)) return;
        const form = document.getElementById('singleActionForm');
        form.action = actionUrl;
        document.getElementById('singleActionMethod').value = 'DELETE';
        form.submit();
    }

    function submitResetSession(actionUrl, sessionCode) {
        if (!confirm(`Reset leaderboard sesi #${sessionCode}? Catatan lap dan sertifikat akan dihapus dan timer dikembalikan ke nol.`)) return;
        const form = document.getElementById('singleActionForm');
        form.action = actionUrl;
        document.getElementById('singleActionMethod').value = 'POST';
        form.submit();
    }
</script>
@endsection
