@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Detail Proyek Kalender #' . $project->id . ' - Admin Ruang Lari')

@section('content')
<div class="container mx-auto px-4 md:px-8 py-8 pt-0">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('admin.kalender-pelari.projects.index') }}" class="hover:text-white transition">Proyek Kalender</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-200">Detail #{{ $project->id }}</span>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                {{ $project->name ?: 'Proyek Kalender Tanpa Judul' }}
            </h1>
            <p class="text-slate-300 text-sm mt-1">
                Spesifikasi kalender, status antrean cetak, dan catatan operasional percetakan.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.kalender-pelari.projects.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold rounded-md border border-slate-700 transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ $project->routeEditor() }}" target="_blank" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-sm transition flex items-center gap-2">
                <i class="fas fa-desktop"></i>
                <span>Buka di Studio Kalender</span>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Project Info & Summary -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Card 1: Spesifikasi Proyek -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                <h2 class="text-base font-bold text-white tracking-tight mb-4 flex items-center gap-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    <i class="fas fa-info-circle text-red-500"></i>
                    <span>Spesifikasi Kalender</span>
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div class="p-3 bg-slate-950 rounded-md border border-slate-800">
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px]">Format Kalender</span>
                        <span class="text-white font-semibold text-sm mt-1 block">{{ str_replace('_', ' ', $project->format_type) }}</span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-md border border-slate-800">
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px]">Tahun Kalender</span>
                        <span class="text-white font-semibold text-sm font-mono mt-1 block">{{ $project->year }}</span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-md border border-slate-800">
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px]">Mulai Hari</span>
                        <span class="text-white font-semibold text-sm mt-1 block">{{ $project->start_week_on === 'MONDAY' ? 'Senin' : 'Minggu' }}</span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-md border border-slate-800">
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px]">Tipe Kepemilikan</span>
                        <span class="text-white font-semibold text-sm mt-1 block">{{ $project->is_anonymous ? 'Tamu / Anonim' : 'Akun Terdaftar' }}</span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-md border border-slate-800">
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px]">Versi State</span>
                        <span class="text-white font-semibold text-sm font-mono mt-1 block">v{{ $project->version_counter }}</span>
                    </div>

                    <div class="p-3 bg-slate-950 rounded-md border border-slate-800">
                        <span class="text-slate-400 uppercase tracking-wider block text-[10px]">Jumlah Halaman</span>
                        <span class="text-white font-semibold text-sm font-mono mt-1 block">{{ $project->pages->count() }} Halaman</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Informasi Pemesan / Runner -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                <h2 class="text-base font-bold text-white tracking-tight mb-4 flex items-center gap-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    <i class="fas fa-user text-red-500"></i>
                    <span>Informasi Pemilik Proyek</span>
                </h2>

                @if($project->user)
                    <div class="flex items-center gap-4 p-4 rounded-md bg-slate-950 border border-slate-800">
                        <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-white font-bold text-lg font-mono">
                            {{ substr($project->user->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-white">{{ $project->user->name }}</div>
                            <div class="text-xs text-slate-300 font-mono">{{ $project->user->email }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">Role: <span class="capitalize text-slate-200">{{ $project->user->role }}</span> | Terdaftar: {{ $project->user->created_at ? $project->user->created_at->format('d M Y') : '-' }}</div>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-md bg-slate-950 border border-slate-800">
                        <div class="text-xs font-semibold text-amber-400">Pengunjung Tamu (Uji Coba Anonim)</div>
                        <p class="text-xs text-slate-400 mt-1">
                            Proyek dibuat tanpa login menggunakan UUID sesi uji coba.
                        </p>
                        <div class="text-xs font-mono text-slate-300 bg-slate-900 p-2 rounded mt-2 border border-slate-800">
                            UUID: {{ $project->anonymous_uuid }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Status & Cetak Action -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Form Status Cetak & Admin Notes -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                <h2 class="text-base font-bold text-white tracking-tight mb-4 flex items-center gap-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    <i class="fas fa-print text-red-500"></i>
                    <span>Status Antrean Cetak & Catatan</span>
                </h2>

                <form method="POST" action="{{ route('admin.kalender-pelari.projects.update-status', $project) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Status Produksi
                        </label>
                        <select name="status" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2.5 text-sm text-white focus:border-red-500 outline-none">
                            <option value="PRODUCTION_ACTIVE" {{ $project->status === 'PRODUCTION_ACTIVE' ? 'selected' : '' }}>Siap Dicetak / Antrean Cetak (PRODUCTION_ACTIVE)</option>
                            <option value="PENDING_CHECKOUT" {{ $project->status === 'PENDING_CHECKOUT' ? 'selected' : '' }}>Menunggu Pembayaran (PENDING_CHECKOUT)</option>
                            <option value="DRAFT_SAVED" {{ $project->status === 'DRAFT_SAVED' ? 'selected' : '' }}>Draf Disimpan Pengguna (DRAFT_SAVED)</option>
                            <option value="DRAFT_TRIAL" {{ $project->status === 'DRAFT_TRIAL' ? 'selected' : '' }}>Sesi Uji Coba Anonim (DRAFT_TRIAL)</option>
                            <option value="ARCHIVED" {{ $project->status === 'ARCHIVED' ? 'selected' : '' }}>Produksi Selesai / Dikirim / Arsip (ARCHIVED)</option>
                            <option value="EXPIRED_TRIAL" {{ $project->status === 'EXPIRED_TRIAL' ? 'selected' : '' }}>Kedaluwarsa (EXPIRED_TRIAL)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Catatan Operasional / Instruksi Cetak
                        </label>
                        <textarea name="admin_notes" rows="6" placeholder="Spesifikasi cetak fisik: Jenis kertas, jenis spiral kawat, alamat penerima, nomor resi kirim..." class="w-full bg-slate-950 border border-slate-800 rounded-md p-3 text-sm text-white placeholder-slate-500 focus:border-red-500 outline-none">{{ old('admin_notes', $project->admin_notes) }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md shadow-sm transition">
                        Perbarui Status & Catatan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
