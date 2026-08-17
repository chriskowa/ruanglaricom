@extends('layouts.pacerhub')

@php
    $withSidebar = true;
    $pageTitle = 'Riwayat Aktivitas Lari | RuangLari';
    $pageDesc = 'Kelola dan pantau seluruh riwayat aktivitas lari Anda di RuangLari. Analisis pace, elevasi, kalori, dan ekspor GPX.';
    $totalDist = (float)($totalStats->total_distance ?? 0);
    $totalRuns = (int)($totalStats->total_runs ?? 0);
    $totalSec = (int)($totalStats->total_time ?? 0);
    $totalHours = floor($totalSec / 3600);
    $totalMins = floor(($totalSec % 3600) / 60);
    $totalTimeFormatted = $totalHours > 0 ? "{$totalHours}j {$totalMins}m" : "{$totalMins}m";
    $totalCalories = (int)($totalStats->total_calories ?? 0);
@endphp

@section('title', $pageTitle)
@section('meta_title', $pageTitle)
@section('meta_description', $pageDesc)

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endpush

@section('content')
<div class="min-h-screen pt-20 pb-20 px-3 sm:px-6 md:px-8 bg-[#0B0F17] text-slate-200 font-sans">
    <div class="max-w-6xl mx-auto space-y-6">

        <!-- Top Header & Primary CTAs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                    <a href="{{ route('runner.dashboard') }}" class="hover:text-white transition">Runner Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-200">Aktivitas Lari</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white flex items-center gap-2.5">
                    
                    <span>Riwayat Aktivitas Lari</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-0.5">
                    Catatan lari live GPS dan rute tersimpan di akun Anda.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('run.free') }}" class="px-4 py-2.5 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 shadow-lg shadow-[#FC4C02]/20 cursor-pointer">
                    <i class="fa-solid fa-play text-xs"></i>
                    <span>Mulai Lari</span>
                </a>
                <a href="{{ route('gpx.index') }}" class="px-3.5 py-2.5 rounded-xl border border-slate-700 hover:border-slate-600 bg-[#111724] hover:bg-slate-800 text-slate-300 hover:text-white font-semibold text-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                    
                    <span>Database GPX</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-emerald-950/60 border border-emerald-800/80 text-emerald-400 text-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        <!-- Lifetime Running Stats Strip -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="bg-[#111724] border border-slate-800/80 rounded-xl p-3.5 sm:p-4 flex items-center gap-3">
                
                <div class="min-w-0">
                    <span class="text-[11px] text-slate-400 font-medium block">Total Jarak</span>
                    <span class="text-lg sm:text-xl font-bold font-mono text-white mt-0.5 block truncate">
                        {{ number_format($totalDist, 2) }} <span class="text-xs font-normal text-slate-400">km</span>
                    </span>
                </div>
            </div>

            <div class="bg-[#111724] border border-slate-800/80 rounded-xl p-3.5 sm:p-4 flex items-center gap-3">
                
                <div class="min-w-0">
                    <span class="text-[11px] text-slate-400 font-medium block">Total Sesi Lari</span>
                    <span class="text-lg sm:text-xl font-bold font-mono text-white mt-0.5 block truncate">
                        {{ number_format($totalRuns) }} <span class="text-xs font-normal text-slate-400">sesi</span>
                    </span>
                </div>
            </div>

            <div class="bg-[#111724] border border-slate-800/80 rounded-xl p-3.5 sm:p-4 flex items-center gap-3">
                <div class="min-w-0">
                    <span class="text-[11px] text-slate-400 font-medium block">Waktu Bergerak</span>
                    <span class="text-lg sm:text-xl font-bold font-mono text-white mt-0.5 block truncate">
                        {{ $totalTimeFormatted }}
                    </span>
                </div>
            </div>

            <div class="bg-[#111724] border border-slate-800/80 rounded-xl p-3.5 sm:p-4 flex items-center gap-3">
                
                <div class="min-w-0">
                    <span class="text-[11px] text-slate-400 font-medium block">Total Kalori</span>
                    <span class="text-lg sm:text-xl font-bold font-mono text-white mt-0.5 block truncate">
                        {{ number_format($totalCalories) }} <span class="text-xs font-normal text-slate-400">kcal</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form action="{{ route('activities.index') }}" method="GET" class="relative flex-1 max-w-md">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama aktivitas..." 
                       class="w-full bg-[#111724] border border-slate-700 focus:border-[#FC4C02] rounded-xl pl-9 pr-4 py-2 text-xs text-white placeholder-slate-500 outline-none transition">
            </form>

            <div class="text-xs text-slate-400 font-mono self-end sm:self-center">
                Menampilkan <strong>{{ $activities->total() }}</strong> aktivitas
            </div>
        </div>

        <!-- Activities List -->
        @if($activities->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                @foreach($activities as $act)
                    <div class="bg-[#111724] border border-slate-800 hover:border-slate-700 rounded-xl p-4 transition space-y-3 flex flex-col justify-between" id="activity-card-{{ $act->id }}">
                        
                        <!-- Card Header -->
                        <div>
                            <div class="flex items-center justify-between gap-2 text-[11px] font-mono text-slate-400 mb-1">
                                <span class="flex items-center gap-1.5 text-slate-300">
                                    <i class="fa-regular fa-calendar text-[10px] text-[#FC4C02]"></i>
                                    <span>{{ $act->start_time ? $act->start_time->format('d M Y • H:i') : $act->created_at->format('d M Y') }}</span>
                                </span>

                                @if($act->masterGpx)
                                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px] text-slate-300 font-sans truncate max-w-[140px]" title="Mengikuti rute: {{ $act->masterGpx->title }}">
                                        {{ $act->masterGpx->title }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-slate-800/80 border border-slate-750 text-[10px] text-[#FC4C02] font-sans">
                                        Free Run
                                    </span>
                                @endif
                            </div>

                            <a href="{{ route('activities.show', $act->id) }}" class="text-base font-bold text-white hover:text-[#FC4C02] transition block truncate">
                                {{ $act->title }}
                            </a>

                            @if($act->notes)
                                <p class="text-xs text-slate-400 line-clamp-1 mt-0.5 italic">
                                    "{{ $act->notes }}"
                                </p>
                            @endif
                        </div>

                        <!-- Card Metrics Strip -->
                        <div class="bg-[#0D131F] border border-slate-800 rounded-lg p-2.5 grid grid-cols-4 divide-x divide-slate-800 text-center">
                            <div class="px-1">
                                <span class="text-[10px] text-slate-400 font-mono block">Jarak</span>
                                <span class="text-sm sm:text-base font-bold font-mono text-white mt-0.5 block truncate">
                                    {{ number_format((float)$act->distance_km, 2) }} <span class="text-[10px] text-slate-400 font-normal">km</span>
                                </span>
                            </div>
                            <div class="px-1">
                                <span class="text-[10px] text-slate-400 font-mono block">Waktu</span>
                                <span class="text-sm sm:text-base font-bold font-mono text-white mt-0.5 block truncate">
                                    {{ $act->formatted_moving_time }}
                                </span>
                            </div>
                            <div class="px-1">
                                <span class="text-[10px] text-slate-400 font-mono block">Avg Pace</span>
                                <span class="text-sm sm:text-base font-bold font-mono text-[#FC4C02] mt-0.5 block truncate">
                                    {{ $act->formatted_avg_pace }}
                                </span>
                            </div>
                            <div class="px-1">
                                <span class="text-[10px] text-slate-400 font-mono block">Gain</span>
                                <span class="text-sm sm:text-base font-bold font-mono text-white mt-0.5 block truncate">
                                    +{{ round($act->elevation_gain_m ?? 0) }}m
                                </span>
                            </div>
                        </div>

                        <!-- Card Actions Footer -->
                        <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-800/80 text-xs">
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('activities.download', $act->id) }}" class="px-2.5 py-1.5 rounded-lg border border-slate-700 hover:border-slate-600 bg-[#0D131F] text-slate-300 hover:text-white transition flex items-center gap-1" title="Download file GPX">
                                    <i class="fa-solid fa-download text-[11px] text-[#FC4C02]"></i>
                                    <span>GPX</span>
                                </a>
                                <button type="button" onclick="deleteUserActivity({{ $act->id }}, '{{ addslashes($act->title) }}')" class="px-2.5 py-1.5 rounded-lg border border-red-900/50 hover:border-red-700 bg-red-950/20 text-red-400 hover:text-red-300 transition flex items-center gap-1 cursor-pointer" title="Hapus aktivitas ini">
                                    <i class="fa-regular fa-trash-can text-[11px]"></i>
                                    <span>Hapus</span>
                                </button>
                            </div>

                            <a href="{{ route('activities.show', $act->id) }}" class="px-3.5 py-1.5 rounded-lg bg-[#FC4C02] hover:bg-[#e04300] text-white font-semibold transition flex items-center gap-1">
                                <span>Detail</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>

            <div class="pt-4">
                {{ $activities->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-[#111724] border border-slate-800 rounded-2xl p-8 sm:p-12 text-center space-y-4 max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-full bg-[#FC4C02]/15 border border-[#FC4C02]/30 flex items-center justify-center text-[#FC4C02] text-2xl mx-auto">
                    <i class="fa-solid fa-person-running"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base sm:text-lg font-bold text-white">Belum Ada Aktivitas Lari</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Anda belum merekam aktivitas lari. Mulai sesi lari bebas dengan GPS live atau pilih salah satu rute dari Database GPX.
                    </p>
                </div>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <a href="{{ route('run.free') }}" class="px-4 py-2.5 rounded-xl bg-[#FC4C02] hover:bg-[#e04300] text-white font-bold text-xs uppercase tracking-wide transition flex items-center gap-2 shadow-lg shadow-[#FC4C02]/20">
                        <i class="fa-solid fa-play text-xs"></i>
                        <span>Mulai Lari Sekarang</span>
                    </a>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    function deleteUserActivity(id, title) {
        if (!confirm(`Apakah Anda yakin ingin menghapus aktivitas "${title}"? Data statistik dan jejak GPS akan dihapus secara permanen.`)) {
            return;
        }

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(`/activities/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById(`activity-card-${id}`);
                if (card) {
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                    setTimeout(() => card.remove(), 300);
                } else {
                    window.location.reload();
                }
            } else {
                alert(data.message || 'Gagal menghapus aktivitas.');
            }
        })
        .catch(err => {
            console.error('Delete error:', err);
            alert('Terjadi kesalahan jaringan saat menghapus aktivitas.');
        });
    }
</script>
@endpush
