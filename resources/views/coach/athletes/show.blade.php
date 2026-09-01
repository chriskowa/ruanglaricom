@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Monitor Atlet - ' . ($enrollment->runner->name ?? 'Ruang Lari'))

@push('styles')
<style>
[v-cloak]{display:none !important;}
.fc .fc-toolbar-title{font-size: 1rem;font-weight:600;color:#f1f5f9;}
.fc .fc-button{background:#1e293b;border-color:#334155;color:#cbd5e1;border-radius:6px;font-size:0.8rem;font-weight:500;padding:0.35rem 0.65rem;}
.fc .fc-button:hover{color:#fff;background:#334155;border-color:#475569;}
.fc-event{border:none;border-radius:4px;cursor:pointer;padding:2px 5px;font-size:0.75rem;font-weight:500;}
/* Calendar Dark Mode Overrides */
.fc-theme-standard .fc-scrollgrid { border-color: #334155; }
.fc-theme-standard td, .fc-theme-standard th { border-color: #334155; }
.fc .fc-daygrid-day-number { color: #94a3b8; text-decoration: none; font-size: 0.8rem; }
.fc .fc-col-header-cell-cushion { color: #cbd5e1; text-decoration: none; font-size: 0.8rem; font-weight: 600; padding: 6px 0; }
.fc-day-today { background-color: rgba(204, 255, 0, 0.04) !important; }
.fc-daygrid-day-frame { min-height: 85px; }
.fc .fc-daygrid-day.fc-day-other { background-color: rgba(0,0,0,0.15); }

/* Mobile List View Styling */
.fc-list { border: none !important; background: transparent !important; }
.fc-list-day-cushion { background-color: transparent !important; padding: 8px 4px !important; }
.fc-list-day-text, .fc-list-day-side-text { font-size: 0.85rem; font-weight: 600; color: #f1f5f9; }
.fc-list-event td { border: none !important; }
.fc-list-event { 
    background-color: #1e293b !important; 
    border-radius: 6px; 
    margin-bottom: 6px; 
    display: block;
    position: relative;
    border: 1px solid #334155;
}
.fc-list-table { border-collapse: separate; border-spacing: 0 6px; }
.fc-list-event:hover td { background-color: transparent !important; }
.fc-list-event-graphic { display: none; }
.fc-list-event-time { color: #94a3b8; font-size: 0.75rem; padding: 10px 0 10px 14px !important; width: 20%; }
.fc-list-event-title { color: #fff; font-weight: 500; padding: 10px 14px !important; font-size: 0.8rem; }

/* Workout Left Accent Border */
.fc-event.workout-easy_run, .fc-list-event.workout-easy_run { border-left: 3px solid #22c55e !important; }
.fc-event.workout-long_run, .fc-list-event.workout-long_run { border-left: 3px solid #3b82f6 !important; }
.fc-event.workout-interval, .fc-list-event.workout-interval { border-left: 3px solid #ef4444 !important; }
.fc-event.workout-tempo, .fc-list-event.workout-tempo { border-left: 3px solid #eab308 !important; }
.fc-event.workout-strength, .fc-list-event.workout-strength { border-left: 3px solid #a855f7 !important; }
.fc-event.workout-rest, .fc-list-event.workout-rest { border-left: 3px solid #64748b !important; }
.fc-event.workout-race, .fc-list-event.workout-race { border-left: 3px solid #eab308 !important; }
.fc-event.workout-threshold, .fc-event.workout-treshold, .fc-list-event.workout-threshold, .fc-list-event.workout-treshold { border-left: 3px solid #ec4899 !important; }
.fc-event.workout-recovery_run, .fc-list-event.workout-recovery_run { border-left: 3px solid #06b6d4 !important; }
.fc-event.workout-time_trial, .fc-list-event.workout-time_trial { border-left: 3px solid #f97316 !important; }

@media (max-width: 640px) {
    .fc .fc-header-toolbar { margin-bottom: 0.75rem; flex-direction: column; gap: 0.5rem; }
    .fc .fc-toolbar-title { font-size: 0.9rem; }
    .fc .fc-button { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
}
</style>
@if(request()->has('embed'))
<style>
    #ph-sidebar { display: none !important; }
    #main-content-wrapper { padding-left: 0 !important; }
    #pacerhub-nav { display: none !important; }
    #chatbox-toggle { display: none !important; }
    body { padding-top: 0 !important; }
    #coach-monitor-app { padding-top: 1rem !important; }
    a[href*="athletes"] { display: none !important; }
</style>
@endif
@endpush

@section('content')
<main id="coach-monitor-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans" v-cloak>
    <div class="max-w-7xl mx-auto">
        <!-- Top Athlete Header Bar -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 sm:gap-6 mb-6">
            <div class="w-full lg:w-auto">
                <a href="{{ route('coach.athletes.index') }}" class="text-slate-400 hover:text-white text-xs mb-3 inline-flex items-center gap-1 font-medium transition">
                    <span>&larr; Kembali ke Daftar Atlet</span>
                </a>
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                        {{ substr($enrollment->runner->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold text-white tracking-tight leading-tight">@{{ trainingProfile.name }}</h1>
                            <!-- Strava Indicator -->
                            <div v-if="trainingProfile.strava_connected" class="flex items-center gap-1.5 bg-[#FC4C02]/10 border border-[#FC4C02]/30 px-2 py-0.5 rounded text-xs font-semibold text-[#FC4C02]">
                                <span>Strava Terhubung</span>
                                <button type="button" @click="syncStrava" :disabled="loading" class="ml-1 px-1.5 py-0.5 rounded bg-[#FC4C02] text-white hover:bg-[#e34402] transition text-[10px] font-medium disabled:opacity-50">
                                    <span v-if="loading">Syncing...</span>
                                    <span v-else>Sync</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="text-xs font-semibold text-slate-300 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">{{ $enrollment->program->title }}</span>
                            <span class="px-2 py-0.5 rounded text-xs font-medium border
                                @if($enrollment->status === 'active') bg-emerald-500/10 text-emerald-400 border-emerald-500/20
                                @elseif($enrollment->status === 'inactive') bg-rose-500/10 text-rose-400 border-rose-500/20
                                @elseif($enrollment->status === 'completed') bg-blue-500/10 text-blue-400 border-blue-500/20
                                @else bg-amber-500/10 text-amber-400 border-amber-500/20 @endif">
                                {{ $enrollment->status === 'inactive' ? 'Expired' : ($enrollment->status === 'purchased' ? 'Belum Aktif' : ucfirst($enrollment->status)) }}
                            </span>
                            
                            <!-- Pricing & Session Quota Badge -->
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-900 border border-slate-700 text-slate-300">
                                {{ $enrollment->program->pricing_model_label }}
                            </span>
                            @if($enrollment->pricing_type === 'hourly' || $enrollment->program->pricing_model === 'hourly')
                                <div class="flex items-center gap-1.5 bg-emerald-950 border border-emerald-800 px-2 py-0.5 rounded text-xs font-medium text-emerald-300 font-mono">
                                    <span>Sisa Sesi: {{ $enrollment->sessions_remaining ?? 0 }} / {{ $enrollment->total_sessions_quota ?? 0 }}</span>
                                    @if(($enrollment->sessions_remaining ?? 0) > 0)
                                        <form method="POST" action="{{ route('coach.athletes.record-session', $enrollment->id) }}" onsubmit="return confirm('Catat 1 sesi latihan tatap muka selesai?');" class="inline">
                                            @csrf
                                            <button type="submit" class="px-1.5 py-0.5 rounded bg-emerald-700 hover:bg-emerald-600 text-white text-[10px] font-bold transition">
                                                -1 Sesi
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                            <a href="{{ route('coach.invoices.index', ['search' => $enrollment->runner->name]) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium rounded-md transition">
                                Tagihan Atlet
                            </a>
                            
                            <!-- Program Action Buttons -->
                            <button @click="openAiProgramModal()" class="px-3 py-1 bg-neon text-dark hover:bg-white font-semibold text-xs rounded-md transition flex items-center gap-1.5 shadow-sm">
                                <span>Generate Program AI</span>
                            </button>
                            @if($enrollment->status !== 'active')
                                <button @click="openRescheduleModal()" class="px-3 py-1 bg-slate-800 text-slate-200 border border-slate-700 hover:bg-slate-700 hover:text-white text-xs font-medium rounded-md transition">
                                    Aktifkan Program
                                </button>
                            @else
                                <button @click="openRescheduleModal()" class="px-3 py-1 bg-slate-800 text-slate-200 border border-slate-700 hover:bg-slate-700 hover:text-white text-xs font-medium rounded-md transition">
                                    Reschedule
                                </button>
                            @endif
                            <button @click="openReminderModal()" class="px-3 py-1 bg-slate-800 text-slate-200 border border-slate-700 hover:bg-slate-700 hover:text-white text-xs font-medium rounded-md transition">
                                Kirim Pengingat
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Runner Stats Summary Cards -->
            <div class="w-full lg:w-auto lg:min-w-[360px]">
                <div class="grid grid-cols-3 gap-3 w-full">
                    <div class="bg-slate-900 rounded-lg p-3.5 border border-slate-800 text-center">
                        <div class="text-xs text-slate-400 font-medium mb-1">Skor VDOT</div>
                        <div class="text-xl font-bold text-white font-mono">@{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                    </div>
                    <div class="bg-slate-900 rounded-lg p-3.5 border border-slate-800 text-center">
                        <div class="text-xs text-slate-400 font-medium mb-1">Target Mingguan</div>
                        <div class="text-xl font-bold text-white font-mono flex items-baseline justify-center">
                            <span>@{{ trainingProfile.weekly_km_target ? Number(trainingProfile.weekly_km_target).toFixed(0) : '-' }}</span>
                            <span class="text-xs font-normal text-slate-400 ml-1">km</span>
                        </div>
                    </div>
                    <div class="bg-slate-900 rounded-lg p-3.5 border border-slate-800 text-center">
                        <div class="text-xs text-slate-400 font-medium mb-1">Usia Atlet</div>
                        <div class="text-xl font-bold text-white font-mono">{{ $enrollment->runner->date_of_birth ? \Carbon\Carbon::parse($enrollment->runner->date_of_birth)->age . ' th' : '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($enrollment->status !== 'active')
        <div class="mb-6 p-4 rounded-lg bg-amber-950 border border-amber-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <div class="text-sm font-semibold text-white">Status: Program Belum Aktif</div>
                <div class="text-xs text-amber-200/90 mt-0.5">Atlet terdaftar pada {{ $enrollment->program->title }}. Tentukan tanggal mulai untuk menjadwalkan sesi latihan atlet.</div>
            </div>
            <button @click="openRescheduleModal()" class="w-full sm:w-auto px-4 py-2 rounded-md bg-neon text-dark font-semibold text-xs hover:bg-white transition flex-shrink-0">
                Aktifkan Program Sekarang
            </button>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Main Column: Fitness Profile & Calendar -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Streamlined Fitness & Training Profile Card -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Profil Kebugaran Atlet</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Parameter VDOT dan acuan target latihan atlet</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="openVdotModal()" class="px-3 py-1.5 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:text-white hover:bg-slate-700 transition text-xs font-medium">
                                Update PB / VDOT
                            </button>
                            <button @click="showWeeklyTargetModal = true" class="px-3 py-1.5 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:text-white hover:bg-slate-700 transition text-xs font-medium">
                                Edit Target KM
                            </button>
                        </div>
                    </div>

                    <!-- Clean Focused Tabs (No Icons) -->
                    <div class="flex gap-6 border-b border-slate-800 mb-5 overflow-x-auto pb-px">
                        <button 
                            class="text-xs font-semibold pb-2.5 transition border-b-2 whitespace-nowrap"
                            :class="profileTab === 'training' ? 'text-white border-neon' : 'text-slate-400 border-transparent hover:text-slate-200'"
                            @click="profileTab = 'training'">
                            Pace Latihan
                        </button>
                        <button 
                            class="text-xs font-semibold pb-2.5 transition border-b-2 whitespace-nowrap"
                            :class="profileTab === 'race' ? 'text-white border-neon' : 'text-slate-400 border-transparent hover:text-slate-200'"
                            @click="profileTab = 'race'">
                            Prediksi Waktu Lomba
                        </button>
                        <button 
                            class="text-xs font-semibold pb-2.5 transition border-b-2 whitespace-nowrap"
                            :class="profileTab === 'weekly_report' ? 'text-white border-neon' : 'text-slate-400 border-transparent hover:text-slate-200'"
                            @click="profileTab = 'weekly_report'">
                            Laporan Mingguan
                        </button>
                    </div>

                    <!-- Tab 1: Pace Latihan -->
                    <div v-if="profileTab === 'training'">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-slate-300">Pace Referensi Sesi</span>
                                <span v-if="trainingProfile.is_custom_paces" class="px-2 py-0.5 rounded text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                    Kustom Coach
                                </span>
                                <span v-else class="px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                    Otomatis VDOT
                                </span>
                            </div>
                            <button @click="openPaceModal()" class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:text-white hover:bg-slate-700 transition text-xs font-medium">
                                Edit Pace
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead>
                                    <tr class="text-xs text-slate-400 border-b border-slate-800 font-medium">
                                        <th class="py-2.5">Zona Latihan</th>
                                        <th class="py-2.5 text-right">Target Pace (per 1 KM)</th>
                                    </tr>
                                </thead>
                                <tbody class="text-slate-200 divide-y divide-slate-800/60">
                                    <tr>
                                        <td class="py-2.5 font-medium text-emerald-400">Easy (E)</td>
                                        <td class="py-2.5 text-right font-mono text-white">
                                            <template v-if="trainingProfile.paces?.E_high && trainingProfile.paces?.E_low">
                                                @{{ formatPace(trainingProfile.paces.E_high) }} - @{{ formatPace(trainingProfile.paces.E_low) }}
                                            </template>
                                            <template v-else>
                                                @{{ formatPace(trainingProfile.paces?.E) }}
                                            </template>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="py-2.5 font-medium text-sky-400">Marathon (M)</td>
                                        <td class="py-2.5 text-right font-mono text-white">@{{ formatPace(trainingProfile.paces?.M) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2.5 font-medium text-yellow-400">Threshold (T)</td>
                                        <td class="py-2.5 text-right font-mono text-white">@{{ formatPace(trainingProfile.paces?.T) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2.5 font-medium text-orange-400">Interval (I)</td>
                                        <td class="py-2.5 text-right font-mono text-white">@{{ formatPace(trainingProfile.paces?.I) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2.5 font-medium text-rose-400">Repetition (R)</td>
                                        <td class="py-2.5 text-right font-mono text-white">@{{ formatPace(trainingProfile.paces?.R) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Estimasi & Prediksi Lomba -->
                    <div v-if="profileTab === 'race'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <!-- Equivalent Race Times Table -->
                            <div class="bg-slate-950 border border-slate-800 rounded-lg p-4">
                                <h3 class="text-sm font-semibold text-white mb-1">Waktu Ekuivalen VDOT</h3>
                                <p class="text-xs text-slate-400 mb-3">Estimasi waktu lomba berdasarkan VDOT saat ini</p>
                                <table class="w-full text-xs text-left">
                                    <thead>
                                        <tr class="text-slate-400 border-b border-slate-800 font-medium">
                                            <th class="py-2">Jarak</th>
                                            <th class="py-2 text-right">Waktu Finish</th>
                                            <th class="py-2 text-right">Pace Rata-rata</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-200 divide-y divide-slate-800/60">
                                        <tr>
                                            <td class="py-2 font-medium">5K</td>
                                            <td class="py-2 text-right text-white font-mono">@{{ trainingProfile.equivalent_race_times?.['5k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-300 font-mono">@{{ trainingProfile.equivalent_race_times?.['5k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-medium">10K</td>
                                            <td class="py-2 text-right text-white font-mono">@{{ trainingProfile.equivalent_race_times?.['10k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-300 font-mono">@{{ trainingProfile.equivalent_race_times?.['10k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-medium">Half Marathon</td>
                                            <td class="py-2 text-right text-white font-mono">@{{ trainingProfile.equivalent_race_times?.['21k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-300 font-mono">@{{ trainingProfile.equivalent_race_times?.['21k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-medium">Full Marathon</td>
                                            <td class="py-2 text-right text-white font-mono">@{{ trainingProfile.equivalent_race_times?.['42k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-300 font-mono">@{{ trainingProfile.equivalent_race_times?.['42k']?.pace || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Interactive Race Predictor (Riegel Formula) -->
                            <div class="bg-slate-950 border border-slate-800 rounded-lg p-4">
                                <h3 class="text-sm font-semibold text-white mb-1">Kalkulator Prediksi Finish</h3>
                                <p class="text-xs text-slate-400 mb-3">Formula Riegel untuk estimasi jarak lomba kustom</p>
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-300 mb-1">Target Jarak (KM)</label>
                                        <input type="number" step="0.1" v-model.number="predictor.distance" class="w-full bg-slate-900 border border-slate-800 rounded-md p-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-300 mb-1">VDOT Acuan</label>
                                        <input type="number" step="0.1" v-model.number="predictor.vdot" class="w-full bg-slate-900 border border-slate-800 rounded-md p-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                    </div>
                                </div>
                                <div class="p-3 bg-slate-900 border border-slate-800 rounded-md text-center">
                                    <div class="text-xs text-slate-400 font-medium">Estimasi Waktu Selesai</div>
                                    <div class="text-2xl font-bold text-white mt-1 font-mono">@{{ predictedTime.time }}</div>
                                    <div class="text-xs text-slate-300 mt-1 font-mono">Pace: @{{ predictedTime.pace }} /km</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Laporan Mingguan -->
                    <div v-if="profileTab === 'weekly_report'" class="space-y-5">
                        <!-- Strava Connection Notice if not connected -->
                        <div v-if="!trainingProfile.strava_connected" class="p-3.5 rounded-lg bg-amber-950 border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="text-left">
                                <div class="text-xs font-semibold text-white">Strava Belum Terhubung</div>
                                <div class="text-xs text-slate-300 mt-0.5">Atlet belum menghubungkan akun Strava untuk sinkronisasi otomatis.</div>
                            </div>
                            <button type="button" @click="nudgeStrava" class="px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium transition whitespace-nowrap">
                                Kirim Pengingat
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <!-- Report Form (Col-span-2) -->
                            <div class="md:col-span-2 space-y-4">
                                <div class="bg-slate-950 p-4 rounded-lg border border-slate-800">
                                    <h3 class="text-sm font-semibold text-white mb-1">Tulis Evaluasi Mingguan</h3>
                                    <p class="text-xs text-slate-400 mb-3">Buat rapor berkala untuk atlet</p>
                                    
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-300 mb-1">Minggu Ke-</label>
                                            <input type="number" min="1" max="52" v-model.number="weeklyReportForm.week_number" class="w-full bg-slate-900 border border-slate-800 rounded-md p-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" @click="generateWeeklyReport" :disabled="weeklyReportLoading" class="w-full px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium rounded-md transition">
                                                <span v-if="!weeklyReportLoading">Hasilkan Draf AI</span>
                                                <span v-else>Menganalisis...</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-xs font-medium text-slate-300 mb-1">Catatan Evaluasi Coach</label>
                                        <textarea rows="6" v-model="weeklyReportForm.report_text" class="w-full bg-slate-900 border border-slate-800 rounded-md p-3 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none leading-relaxed" placeholder="Tulis catatan perkembangan atlet atau gunakan draf AI..."></textarea>
                                    </div>

                                    <div class="mt-4 flex justify-end">
                                        <button type="button" @click="publishWeeklyReport" :disabled="weeklyReportPublishing" class="px-4 py-2 bg-neon text-dark font-semibold text-xs rounded-md hover:bg-white transition">
                                            <span v-if="!weeklyReportPublishing">Terbitkan Rapor</span>
                                            <span v-else>Menyimpan...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- History List (Col-span-1) -->
                            <div class="md:col-span-1">
                                <div class="bg-slate-950 p-4 rounded-lg border border-slate-800">
                                    <h3 class="text-sm font-semibold text-white mb-3">Riwayat Rapor</h3>
                                    <div v-if="weeklyReportsList.length === 0" class="text-center py-6 text-xs text-slate-400">
                                        Belum ada laporan yang diterbitkan.
                                    </div>
                                    <div v-else class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                                        <div v-for="rep in weeklyReportsList" :key="rep.id" @click="selectWeeklyReport(rep)" class="p-2.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 rounded-md cursor-pointer transition">
                                            <div class="flex justify-between items-center mb-1">
                                                 <span class="text-xs font-semibold text-white">Minggu ke-@{{ rep.week_number }}</span>
                                                 <span class="text-xs text-slate-400 font-mono">@{{ formatDateShort(rep.created_at) }}</span>
                                            </div>
                                            <p class="text-xs text-slate-300 line-clamp-2 leading-relaxed">@{{ rep.report_text }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Section -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 sm:p-6" id="coach-calendar-section">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                        <div>
                            <h2 class="text-lg font-semibold text-white">Kalender Latihan Atlet</h2>
                            <p class="text-xs text-slate-400 mt-0.5">Klik tanggal untuk menambah sesi, drag untuk reschedule</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                            <button @click="exportCalendar('image')" class="flex-1 sm:flex-none px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium transition">
                                Export Gambar
                            </button>
                            <button @click="exportCalendar('pdf')" class="flex-1 sm:flex-none px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium transition">
                                Export PDF
                            </button>
                            <button @click="openRaceForm" class="w-full sm:w-auto px-3.5 py-1.5 rounded-md bg-neon text-dark hover:bg-white text-xs font-semibold transition">
                                + Tambah Lomba
                            </button>
                        </div>
                    </div>
                    <div id="calendar" class="fc-modern"></div>
                </div>
            </div>

            <!-- Right Column / Mobile Sheet: Session Detail & Feedback -->
            <div class="lg:col-span-1" id="detail-feedback-column">
                <!-- Mobile Backdrop Overlay -->
                <div v-if="selectedSession && isMobileSheetOpen" @click="closeMobileSheet" class="fixed inset-0 bg-black/70 z-[90] lg:hidden transition-opacity"></div>

                <div v-if="selectedSession" 
                     :class="{
                         'fixed inset-x-0 bottom-0 z-[100] max-h-[85vh] overflow-y-auto rounded-t-lg bg-slate-900 border-t border-slate-700 p-5 sm:p-6 shadow-2xl lg:relative lg:inset-auto lg:z-auto lg:max-h-none lg:overflow-visible lg:rounded-none lg:bg-transparent lg:border-none lg:p-0 lg:shadow-none': isMobileSheetOpen
                     }"
                     class="lg:sticky lg:top-24 space-y-6">

                    <!-- Mobile Handle Bar & Header -->
                    <div v-if="isMobileSheetOpen" class="lg:hidden flex flex-col items-center mb-2">
                        <div class="w-12 h-1 bg-slate-700 rounded-full mb-3 cursor-pointer" @click="closeMobileSheet" title="Tutup Detail"></div>
                        <div class="w-full flex justify-between items-center pb-3 border-b border-slate-800">
                            <span class="text-xs font-semibold text-white">Detail & Feedback Sesi</span>
                            <button type="button" @click="closeMobileSheet" class="text-slate-400 hover:text-white transition text-sm">
                                &times;
                            </button>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1 min-w-0">
                                <div class="text-xs text-slate-400 mb-0.5">@{{ formatDate(selectedSession.start) }}</div>
                                <h3 class="text-lg font-semibold text-white truncate">@{{ selectedSession.title }}</h3>
                            </div>
                            <span class="px-2 py-0.5 rounded text-xs font-medium" 
                                :class="statusClass(selectedSession.extendedProps.status)">
                                @{{ selectedSession.extendedProps.status }}
                            </span>
                        </div>

                        <!-- Session Actions -->
                        <div class="space-y-2 mb-4">
                            <div v-if="!selectedSession.extendedProps.is_strava">
                                <button @click="openForm(null, selectedSession)" class="w-full text-xs bg-slate-800 hover:bg-slate-700 text-white px-3 py-2 rounded-md transition border border-slate-700 font-medium text-center">
                                    @{{ selectedSession.extendedProps.is_custom ? 'Edit Custom Workout' : 'Sesuaikan / Edit Workout' }}
                                </button>
                            </div>
                            <div v-if="!selectedSession.extendedProps.is_strava && selectedSession.extendedProps.status !== 'completed'">
                                <button @click="openReminderModal()" class="w-full text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-2 rounded-md transition border border-slate-700 font-medium text-center">
                                    Kirim Pengingat Latihan
                                </button>
                            </div>
                        </div>

                        <!-- Session Detail Specs -->
                        <div class="space-y-3 mb-5 text-xs text-slate-300">
                            <div v-if="selectedSession.extendedProps.distance || getPaceInfo(selectedSession.extendedProps.type, selectedSession.extendedProps.distance, selectedSession.extendedProps.description)">
                                <div v-if="selectedSession.extendedProps.distance" class="text-slate-400 font-medium">Target Jarak: <strong class="text-white font-mono">@{{ selectedSession.extendedProps.distance }} km</strong></div>
                                <div v-if="getPaceInfo(selectedSession.extendedProps.type, selectedSession.extendedProps.distance, selectedSession.extendedProps.description)" class="mt-2 p-2 bg-slate-950 border border-slate-800 rounded-md text-slate-200 font-mono text-xs">
                                    @{{ getPaceInfo(selectedSession.extendedProps.type, selectedSession.extendedProps.distance, selectedSession.extendedProps.description) }}
                                </div>
                            </div>
                            <div v-if="selectedSession.extendedProps.description">
                                <div class="text-slate-400 font-medium mb-1">Deskripsi / Instruksi:</div>
                                <div class="p-2.5 bg-slate-950 border-l-2 border-slate-700 rounded-r-md text-slate-200 text-xs leading-relaxed">
                                    @{{ selectedSession.extendedProps.description }}
                                </div>
                            </div>
                            <div v-if="selectedSession.extendedProps.notes">
                                <div class="text-slate-400 font-medium mt-2 mb-1">Catatan Tambahan:</div>
                                <div class="p-2.5 bg-amber-950/40 border-l-2 border-amber-500 rounded-r-md text-slate-200 text-xs leading-relaxed">
                                    @{{ selectedSession.extendedProps.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Athlete Execution Report -->
                        <div v-if="selectedSession.extendedProps.tracking" class="mb-5 border-t border-slate-800 pt-4">
                            <h4 class="text-xs font-semibold text-white mb-3">Laporan Hasil Atlet</h4>
                            <div class="space-y-2.5">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="bg-slate-950 p-2.5 rounded-md border border-slate-800">
                                        <div class="text-xs text-slate-400">RPE (1-10)</div>
                                        <div class="font-semibold text-white font-mono mt-0.5">@{{ selectedSession.extendedProps.tracking.rpe || '-' }}</div>
                                    </div>
                                    <div class="bg-slate-950 p-2.5 rounded-md border border-slate-800">
                                        <div class="text-xs text-slate-400">Kondisi / Feeling</div>
                                        <div class="font-semibold text-white capitalize mt-0.5">@{{ selectedSession.extendedProps.tracking.feeling || '-' }}</div>
                                    </div>
                                </div>
                                
                                <div v-if="selectedSession.extendedProps.tracking.notes">
                                    <div class="text-xs text-slate-400 mb-1">Catatan Atlet</div>
                                    <p class="text-xs text-slate-200 bg-slate-950 p-2.5 rounded-md border border-slate-800 italic">"@{{ selectedSession.extendedProps.tracking.notes }}"</p>
                                </div>
                                
                                <div v-if="selectedSession.extendedProps.tracking.strava_link" class="space-y-2">
                                    <button type="button" @click="openStravaModal()" class="w-full py-2 rounded-md bg-[#FC4C02] text-white text-xs font-semibold hover:bg-[#e34402] transition shadow-sm flex items-center justify-center gap-1.5">
                                        <span>Detail, Grafik & Analisis AI</span>
                                    </button>
                                    <a :href="selectedSession.extendedProps.tracking.strava_link" target="_blank" class="block w-full text-center py-1.5 rounded-md bg-[#FC4C02]/10 text-[#FC4C02] text-xs font-medium hover:bg-[#FC4C02]/20 transition border border-[#FC4C02]/30">
                                        Buka di Strava.com
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="!selectedSession.extendedProps.is_strava" class="mb-5 border-t border-slate-800 pt-4 text-center text-slate-400 text-xs italic">
                            Atlet belum mencatat sesi ini.
                        </div>

                        <!-- Strava Metrics & Details -->
                        <div v-if="stravaDetailsLoading" class="mb-5 border-t border-slate-800 pt-4 text-center">
                            <div class="text-xs text-slate-400">Memuat detail Strava...</div>
                        </div>
                        <div v-else-if="stravaDetailsError" class="mb-5 border-t border-slate-800 pt-4">
                            <div class="text-xs text-rose-400">@{{ stravaDetailsError }}</div>
                        </div>
                        <div v-else-if="stravaMetrics" class="mb-5 border-t border-slate-800 pt-4 space-y-3">
                            <h4 class="text-xs font-semibold text-[#FC4C02] mb-2">Detail Data Strava</h4>

                            <!-- Classification -->
                            <div v-if="stravaWorkoutClassification" class="p-2.5 rounded-md border text-xs" :class="stravaWorkoutClassification.colorClass">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-medium opacity-80">Klasifikasi (@{{ stravaWorkoutClassification.source }})</span>
                                </div>
                                <div class="text-xs font-bold uppercase tracking-tight">
                                    @{{ stravaWorkoutClassification.type }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-slate-950 p-2.5 rounded-md border border-slate-800">
                                    <div class="text-xs text-slate-400">Jarak Aktual</div>
                                    <div class="font-semibold text-white font-mono mt-0.5">@{{ stravaMetrics.distance_m ? (stravaMetrics.distance_m / 1000).toFixed(2) : '-' }} km</div>
                                </div>
                                <div class="bg-slate-950 p-2.5 rounded-md border border-slate-800">
                                    <div class="text-xs text-slate-400">Avg Pace</div>
                                    <div class="font-semibold text-white font-mono mt-0.5">@{{ stravaMetrics.pace ? (stravaMetrics.pace + ' /km') : '-' }}</div>
                                </div>
                                <div class="bg-slate-950 p-2.5 rounded-md border border-slate-800">
                                    <div class="text-xs text-slate-400">Heart Rate</div>
                                    <div class="font-semibold text-white font-mono mt-0.5">@{{ stravaMetrics.average_heartrate ? Math.round(stravaMetrics.average_heartrate) : '-' }} bpm</div>
                                </div>
                                <div class="bg-slate-950 p-2.5 rounded-md border border-slate-800">
                                    <div class="text-xs text-slate-400">Cadence</div>
                                    <div class="font-semibold text-white font-mono mt-0.5">@{{ stravaMetrics.average_cadence ? Math.round(stravaMetrics.average_cadence) : '-' }} spm</div>
                                </div>
                            </div>

                            <!-- Splits / Laps summary -->
                            <div v-if="stravaSplits.length > 0" class="mt-3">
                                <div class="text-xs font-medium text-slate-400 mb-1.5">Splits per KM</div>
                                <div class="max-h-36 overflow-y-auto space-y-1 pr-1">
                                    <div v-for="s in stravaSplits" :key="s.split" class="flex justify-between items-center text-xs p-1.5 rounded bg-slate-950 border border-slate-800">
                                        <div class="text-slate-300 font-mono">KM @{{ s.split || '-' }}</div>
                                        <div class="text-white font-mono font-medium">@{{ s.pace || '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" @click="openStravaModal(stravaMetrics.strava_activity_id)" class="w-full py-2 px-3 bg-[#FC4C02] hover:bg-[#e34402] text-white font-semibold text-xs rounded-md transition flex items-center justify-center gap-1.5 shadow-sm mt-3">
                                <span>Detail, Grafik & Analisis AI</span>
                            </button>
                        </div>

                        <!-- Coach Feedback Form -->
                        <div v-if="selectedSession.extendedProps.tracking" class="border-t border-slate-800 pt-4">
                            <h4 class="text-xs font-semibold text-white mb-3">Feedback & Evaluasi Coach</h4>
                            <form @submit.prevent="saveFeedback" class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-400 mb-1">Rating Sesi</label>
                                    <div class="flex gap-2">
                                        <button type="button" v-for="i in 5" :key="i" 
                                            @click="feedbackForm.coach_rating = i"
                                            class="text-base transition hover:scale-105"
                                            :class="i <= feedbackForm.coach_rating ? 'text-amber-400' : 'text-slate-700'">
                                            <i class="fa-solid fa-star"></i>
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-400 mb-1">Catatan Feedback</label>
                                    <textarea v-model="feedbackForm.coach_feedback" rows="3" class="w-full bg-slate-950 border border-slate-800 rounded-md p-2.5 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none" placeholder="Tulis feedback untuk atlet..."></textarea>
                                </div>
                                <button type="submit" :disabled="loading" class="w-full py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs transition disabled:opacity-50 border border-slate-700">
                                    @{{ loading ? 'Menyimpan...' : 'Simpan Feedback' }}
                                </button>
                            </form>
                        </div>

                    </div>
                </div>

                <div v-else class="bg-slate-900 border border-slate-800 rounded-lg p-6 text-center text-slate-400 text-xs">
                    Pilih sesi pada kalender untuk melihat detail latihan dan memberikan feedback.
                </div>
            </div>
        </div>

        <!-- ────────────────── MODALS ────────────────── -->

        <!-- Unified Advanced Workout Builder Modal -->
        <div v-if="builderVisible" v-cloak class="fixed inset-0 z-[200] overflow-y-auto p-3 sm:p-4">
            <div class="fixed inset-0 bg-black/70" @click="builderVisible = false"></div>
            <div class="flex min-h-full items-center justify-center relative pointer-events-none">
                <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-2xl shadow-2xl max-h-[calc(100vh-2rem)] flex flex-col">
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 sm:p-5 border-b border-slate-800 flex-shrink-0 bg-slate-900 rounded-t-lg">
                        <h3 class="text-white font-semibold text-base">
                            @{{ form.workout_id ? 'Edit Workout Atlet' : 'Tambah Workout Baru' }}
                        </h3>
                        <button class="text-slate-400 hover:text-white transition text-lg" @click="builderVisible = false">
                            &times;
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-300 mb-1 block">Tanggal</label>
                                <input type="date" v-model="form.workout_date" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-300 mb-1 block">Tipe Latihan</label>
                                <select v-model="builderForm.type" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none">
                                    <option value="easy_run">Easy Run</option>
                                    <option value="long_run">Long Run</option>
                                    <option value="tempo">Tempo</option>
                                    <option value="interval">Intervals</option>
                                    <option value="strength">Strength</option>
                                    <option value="rest">Rest</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-slate-300 mb-1 block">Judul Sesi (Opsional)</label>
                                <input v-model="builderForm.title" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none" placeholder="misal: Easy 5K + Strides">
                            </div>
                        </div>

                        <!-- Warm Up & Cool Down -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="bg-slate-950 rounded-md p-3 border border-slate-800">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs font-semibold text-slate-300">Warm Up</div>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-400 cursor-pointer">
                                        <input type="checkbox" v-model="builderForm.warmup.enabled" class="rounded bg-slate-900 border-slate-700 text-slate-300 focus:ring-0">
                                        Aktifkan
                                    </label>
                                </div>
                                <div v-if="builderForm.warmup.enabled" class="mt-3 grid grid-cols-2 gap-2">
                                    <select v-model="builderForm.warmup.by" class="bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-white text-xs">
                                        <option value="distance">Jarak</option>
                                        <option value="time">Waktu</option>
                                    </select>
                                    <div v-if="builderForm.warmup.by==='distance'" class="flex gap-1">
                                        <input type="number" step="any" v-model.number="builderForm.warmup.distance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-white text-xs" placeholder="Jarak">
                                        <select v-model="builderForm.warmup.unit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-md px-1 py-1.5 text-white text-xs">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                    <input v-else type="text" v-model="builderForm.warmup.duration" class="bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-white text-xs font-mono" placeholder="00:10:00">
                                </div>
                            </div>

                            <div class="bg-slate-950 rounded-md p-3 border border-slate-800">
                                <div class="flex items-center justify-between">
                                    <div class="text-xs font-semibold text-slate-300">Cool Down</div>
                                    <label class="inline-flex items-center gap-1.5 text-xs text-slate-400 cursor-pointer">
                                        <input type="checkbox" v-model="builderForm.cooldown.enabled" class="rounded bg-slate-900 border-slate-700 text-slate-300 focus:ring-0">
                                        Aktifkan
                                    </label>
                                </div>
                                <div v-if="builderForm.cooldown.enabled" class="mt-3 grid grid-cols-2 gap-2">
                                    <select v-model="builderForm.cooldown.by" class="bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-white text-xs">
                                        <option value="distance">Jarak</option>
                                        <option value="time">Waktu</option>
                                    </select>
                                    <div v-if="builderForm.cooldown.by==='distance'" class="flex gap-1">
                                        <input type="number" step="any" v-model.number="builderForm.cooldown.distance" class="w-2/3 bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-white text-xs" placeholder="Jarak">
                                        <select v-model="builderForm.cooldown.unit" class="w-1/3 bg-slate-900 border border-slate-700 rounded-md px-1 py-1.5 text-white text-xs">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                    <input v-else type="text" v-model="builderForm.cooldown.duration" class="bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-white text-xs font-mono" placeholder="00:10:00">
                                </div>
                            </div>
                        </div>

                        <!-- Main Workout Section -->
                        <div class="bg-slate-950 rounded-md p-3.5 border border-slate-800">
                            <div class="text-xs font-semibold text-slate-200 mb-2.5">Menu Utama Latihan</div>
                            
                            <!-- Easy Run -->
                            <div v-if="builderForm.type==='easy_run'">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <select v-model="builderForm.main.by" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs">
                                        <option value="distance">Jarak</option>
                                        <option value="time">Durasi Waktu</option>
                                    </select>
                                    <div v-if="builderForm.main.by==='distance'" class="flex gap-1">
                                        <input type="number" step="any" v-model.number="builderForm.main.distance" class="w-2/3 bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs" placeholder="Jarak">
                                        <select v-model="builderForm.main.unit" class="w-1/3 bg-slate-900 border border-slate-800 rounded-md px-1 py-2 text-white text-xs">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                    <input v-else type="text" v-model="builderForm.main.duration" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs font-mono" placeholder="00:30:00">
                                    <input type="text" v-model="builderForm.main.pace" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs font-mono" placeholder="Target Pace (05:30)">
                                </div>
                            </div>

                            <!-- Long Run -->
                            <div v-else-if="builderForm.type==='long_run'">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-3">
                                    <select v-model="builderForm.main.by" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs">
                                        <option value="distance">Jarak</option>
                                        <option value="time">Durasi Waktu</option>
                                    </select>
                                    <div v-if="builderForm.main.by==='distance'" class="flex gap-1">
                                        <input type="number" step="any" v-model.number="builderForm.main.distance" class="w-2/3 bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs" placeholder="Jarak">
                                        <select v-model="builderForm.main.unit" class="w-1/3 bg-slate-900 border border-slate-800 rounded-md px-1 py-2 text-white text-xs">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                    <input v-else type="text" v-model="builderForm.main.duration" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs font-mono" placeholder="00:30:00">
                                    <input type="text" v-model="builderForm.main.pace" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs font-mono" placeholder="Target Pace (05:30)">
                                </div>
                                <div class="space-y-2 border-t border-slate-800 pt-2">
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                                        <input type="checkbox" v-model="builderForm.longRun.fastFinish.enabled" class="rounded bg-slate-900 border-slate-700 text-slate-300">
                                        Fast Finish (Selesai Lebih Cepat)
                                    </label>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2" v-if="builderForm.longRun.fastFinish.enabled">
                                        <input type="number" step="any" v-model.number="builderForm.longRun.fastFinish.distance" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs" placeholder="Jarak FF">
                                        <select v-model="builderForm.longRun.fastFinish.unit" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                        </select>
                                        <input type="text" v-model="builderForm.longRun.fastFinish.pace" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs font-mono" placeholder="Pace FF (04:45)">
                                    </div>
                                </div>
                            </div>

                            <!-- Tempo -->
                            <div v-else-if="builderForm.type==='tempo'">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                                    <select v-model="builderForm.tempo.by" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs">
                                        <option value="distance">Jarak</option>
                                        <option value="time">Waktu</option>
                                    </select>
                                    <div v-if="builderForm.tempo.by==='distance'" class="flex gap-1">
                                        <input type="number" step="any" v-model.number="builderForm.tempo.distance" class="w-2/3 bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs" placeholder="Jarak">
                                        <select v-model="builderForm.tempo.unit" class="w-1/3 bg-slate-900 border border-slate-800 rounded-md px-1 py-2 text-white text-xs">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                    <input v-else type="text" v-model="builderForm.tempo.duration" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs font-mono" placeholder="00:20:00">
                                    <input type="text" v-model="builderForm.tempo.pace" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs font-mono" placeholder="Pace Tempo">
                                    <select v-model="builderForm.tempo.effort" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs">
                                        <option value="moderate">Moderate Effort</option>
                                        <option value="hard">Hard Effort</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Interval -->
                            <div v-else-if="builderForm.type==='interval'">
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                                    <input type="number" v-model.number="builderForm.interval.reps" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs font-mono" placeholder="Total Reps">
                                    <select v-model="builderForm.interval.by" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs">
                                        <option value="distance">Jarak</option>
                                        <option value="time">Waktu</option>
                                    </select>
                                    <div v-if="builderForm.interval.by==='distance'" class="flex gap-1 col-span-2 sm:col-span-1">
                                        <input type="number" step="any" v-model.number="builderForm.interval.repDistance" class="w-2/3 bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs" placeholder="Jarak Rep">
                                        <select v-model="builderForm.interval.repDistanceUnit" class="w-1/3 bg-slate-900 border border-slate-800 rounded-md px-1 py-2 text-white text-xs">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                        </select>
                                    </div>
                                    <input v-else type="text" v-model="builderForm.interval.repTime" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs font-mono col-span-2 sm:col-span-1" placeholder="Rep 00:03:00">
                                    <input type="text" v-model="builderForm.interval.pace" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs font-mono" placeholder="Rep Pace">
                                    <input type="text" v-model="builderForm.interval.recovery" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs font-mono" placeholder="Recovery (90s / 200m)">
                                </div>
                            </div>

                            <!-- Strength -->
                            <div v-else-if="builderForm.type==='strength'">
                                <div class="space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        <select v-model="builderForm.strength.category" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs">
                                            <option value="">Pilih Kategori</option>
                                            <option value="full_body">Full Body</option>
                                            <option value="legs_lower_body">Legs/Lower Body</option>
                                            <option value="core">Core</option>
                                            <option value="upper_body">Upper Body</option>
                                        </select>
                                        <select v-model="builderForm.strength.exercise" class="bg-slate-900 border border-slate-800 rounded-md px-2.5 py-2 text-white text-xs">
                                            <option value="">Pilih Gerakan</option>
                                            <option v-for="ex in strengthOptions" :key="ex.name" :value="ex.name">@{{ ex.name }}</option>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2">
                                        <input type="text" v-model="builderForm.strength.sets" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs" placeholder="Set (misal 3)">
                                        <input type="text" v-model="builderForm.strength.reps" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs" placeholder="Rep/Durasi">
                                        <input type="text" v-model="builderForm.strength.equipment" class="bg-slate-900 border border-slate-800 rounded-md px-2 py-2 text-white text-xs" placeholder="Peralatan">
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="button" class="px-3 py-1.5 rounded-md bg-slate-800 text-white text-xs font-medium hover:bg-slate-700 transition" @click="addStrengthExercise">Tambah Gerakan</button>
                                    </div>
                                    <div class="space-y-1.5" v-if="builderForm.strength.plan && builderForm.strength.plan.length">
                                        <div v-for="(item, idx) in builderForm.strength.plan" :key="idx" class="flex items-center justify-between bg-slate-900 border border-slate-800 rounded-md px-2.5 py-1.5 text-xs text-white">
                                            <div>@{{ item.name }} — @{{ item.sets }} x @{{ item.reps }} (@{{ item.equipment }})</div>
                                            <button type="button" class="text-slate-400 hover:text-rose-400 transition" @click="removeStrengthExercise(idx)">&times;</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else-if="builderForm.type==='rest'">
                                <div class="text-slate-400 text-xs italic">Rest Day — Hari istirahat dan pemulihan atlet.</div>
                            </div>
                        </div>

                        <!-- Intensity & Notes -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-medium text-slate-300 mb-1 block">Intensitas Latihan</label>
                                <select v-model="builderForm.intensity" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none">
                                    <option value="low">Rendah (Low)</option>
                                    <option value="medium">Sedang (Medium)</option>
                                    <option value="high">Tinggi (High)</option>
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-medium text-slate-300 mb-1 block">Catatan Sesi Workout</label>
                                <input type="text" v-model="builderForm.notes" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none" placeholder="Instruksi khusus untuk atlet...">
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="bg-slate-950 rounded-md p-3.5 border border-slate-800">
                            <div class="text-xs font-medium text-slate-400 mb-1">Ringkasan Struktur Sesi</div>
                            <div class="text-white text-xs leading-relaxed">@{{ builderSummary }}</div>
                            <div class="text-slate-200 text-xs font-mono mt-1">Estimasi Total Jarak: <strong class="text-white">@{{ builderTotalDistance }} km</strong></div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 sm:p-5 border-t border-slate-800 bg-slate-900 rounded-b-lg flex justify-end items-center gap-2.5 flex-shrink-0">
                        <button v-if="form.workout_id" type="button" class="px-3.5 py-2 rounded-md bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-medium hover:bg-rose-500/20 mr-auto transition" @click="deleteCustomWorkout">Hapus Sesi</button>
                        <button type="button" class="px-4 py-2 rounded-md bg-slate-800 text-slate-300 border border-slate-700 text-xs font-medium hover:bg-slate-700 transition" @click="builderVisible = false">Batal</button>
                        <button type="button" class="px-5 py-2 rounded-md bg-neon text-dark font-semibold text-xs hover:bg-white transition" @click="submitBuilder">
                            @{{ loading ? 'Menyimpan...' : 'Simpan Workout' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generate AI Program Modal -->
        <div v-if="showAiProgramModal" v-cloak class="fixed inset-0 z-[200] overflow-y-auto p-3 sm:p-4">
            <div class="fixed inset-0 bg-black/80" @click="showAiProgramModal = false"></div>
            <div class="flex min-h-full items-center justify-center relative pointer-events-none">
                <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-3xl shadow-2xl max-h-[calc(100vh-2.5rem)] flex flex-col">

                    <!-- Header -->
                    <div class="p-4 sm:p-5 border-b border-slate-800 flex-shrink-0 bg-slate-900 rounded-t-lg">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-neon/10 border border-neon/30 text-neon text-[10px] font-bold rounded uppercase tracking-wider">Daniels VDOT 2.0</span>
                                    <h3 class="text-base font-semibold text-white">AI Program Generator</h3>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Periodisasi cerdas untuk atlet: <strong>@{{ trainingProfile.name }}</strong></p>
                            </div>
                            <button @click="showAiProgramModal = false" class="text-slate-400 hover:text-white transition text-xl p-1 leading-none">
                                &times;
                            </button>
                        </div>

                        <!-- Tab Header Navigation -->
                        <div class="flex gap-2 mt-4 bg-slate-950 p-1 rounded-md border border-slate-800">
                            <button type="button" @click="aiProgramTab = 'config'"
                                :class="aiProgramTab === 'config' ? 'bg-slate-800 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                                class="flex-1 py-1.5 px-3 text-xs rounded transition text-center">
                                1. Konfigurasi Parameter
                            </button>
                            <button type="button" @click="aiProgramPreview ? (aiProgramTab = 'preview') : null"
                                :disabled="!aiProgramPreview"
                                :class="aiProgramTab === 'preview' ? 'bg-slate-800 text-white font-semibold shadow-sm' : 'text-slate-400 hover:text-slate-200 disabled:opacity-40 disabled:cursor-not-allowed'"
                                class="flex-1 py-1.5 px-3 text-xs rounded transition text-center flex items-center justify-center gap-1.5">
                                <span>2. Preview & Terapkan</span>
                                <span v-if="aiProgramPreview" class="w-2 h-2 rounded-full bg-neon"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-6 overflow-y-auto flex-1 min-h-0 space-y-5">

                        <!-- Tab 1: Konfigurasi -->
                        <div v-show="aiProgramTab === 'config'" class="space-y-4">
                            <!-- Target Distance & Dates -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Target Jarak Lomba</label>
                                    <select v-model="aiProgramForm.target_distance" @change="updateDefaultGoalTime" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none">
                                        <option value="5k">5K (5.000m)</option>
                                        <option value="10k">10K (10.000m)</option>
                                        <option value="21k">Half Marathon (21.1 km)</option>
                                        <option value="42k">Full Marathon (42.2 km)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Mulai</label>
                                    <input type="date" v-model="aiProgramForm.start_date" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Race (Target)</label>
                                    <input type="date" v-model="aiProgramForm.target_date" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                </div>
                            </div>

                            <!-- Performance & Volume Profile -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Target Waktu Finish</label>
                                    <input type="text" v-model="aiProgramForm.goal_time" placeholder="HH:MM:SS / MM:SS" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">VDOT Acuan Saat Ini</label>
                                    <input type="number" step="0.1" v-model="aiProgramForm.current_vdot" placeholder="38.0" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Target Volume Mingguan (KM)</label>
                                    <input type="number" step="1" v-model="aiProgramForm.weekly_mileage" placeholder="35" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                </div>
                            </div>

                            <!-- Starting Phase / Lanjut Fase -->
                            <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5 space-y-2">
                                <div class="flex justify-between items-baseline">
                                    <label class="block text-xs font-semibold text-white">Kelanjutan Fase (Starting Phase)</label>
                                    <span class="text-[11px] text-slate-400">Pilih jika atlet sudah melewati base</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <label class="relative flex flex-col p-3 rounded-md border cursor-pointer transition text-left"
                                        :class="aiProgramForm.starting_phase === 'base' ? 'bg-slate-900 border-neon text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'">
                                        <input type="radio" value="base" v-model="aiProgramForm.starting_phase" class="sr-only">
                                        <div class="text-xs font-semibold" :class="aiProgramForm.starting_phase === 'base' ? 'text-neon' : 'text-slate-200'">Siklus Penuh (Base)</div>
                                        <div class="text-[11px] text-slate-400 mt-1 leading-snug">Mulai dari fase pondasi aerobik penuh (Base &rarr; Build &rarr; Peak &rarr; Taper).</div>
                                    </label>

                                    <label class="relative flex flex-col p-3 rounded-md border cursor-pointer transition text-left"
                                        :class="aiProgramForm.starting_phase === 'build' ? 'bg-slate-900 border-neon text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'">
                                        <input type="radio" value="build" v-model="aiProgramForm.starting_phase" class="sr-only">
                                        <div class="text-xs font-semibold" :class="aiProgramForm.starting_phase === 'build' ? 'text-neon' : 'text-slate-200'">Lanjut ke Build Fase</div>
                                        <div class="text-[11px] text-slate-400 mt-1 leading-snug">Lewati Base (atlet sudah 4-8 minggu Base). Fokus Threshold, Tempo & Speed.</div>
                                    </label>

                                    <label class="relative flex flex-col p-3 rounded-md border cursor-pointer transition text-left"
                                        :class="aiProgramForm.starting_phase === 'peak' ? 'bg-slate-900 border-neon text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'">
                                        <input type="radio" value="peak" v-model="aiProgramForm.starting_phase" class="sr-only">
                                        <div class="text-xs font-semibold" :class="aiProgramForm.starting_phase === 'peak' ? 'text-neon' : 'text-slate-200'">Lanjut ke Peak Fase</div>
                                        <div class="text-[11px] text-slate-400 mt-1 leading-snug">Lewati Base & Build. Langsung masuk VO2 Max intervals, race specific & taper.</div>
                                    </label>
                                </div>
                            </div>

                            <!-- Intensity Tone / Pilihan Generator -->
                            <div class="bg-slate-950 border border-slate-800 rounded-lg p-3.5 space-y-2">
                                <div class="flex justify-between items-baseline">
                                    <label class="block text-xs font-semibold text-white">Pilihan Ketajaman & Intensitas Generator</label>
                                    <span class="text-[11px] text-slate-400">Gaya pembebanan workout</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <label class="relative flex flex-col p-3 rounded-md border cursor-pointer transition text-left"
                                        :class="aiProgramForm.intensity_tone === 'standard' ? 'bg-slate-900 border-neon text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'">
                                        <input type="radio" value="standard" v-model="aiProgramForm.intensity_tone" class="sr-only">
                                        <div class="text-xs font-semibold" :class="aiProgramForm.intensity_tone === 'standard' ? 'text-neon' : 'text-slate-200'">Standar & Seimbang</div>
                                        <div class="text-[11px] text-slate-400 mt-1 leading-snug">Periodisasi Jack Daniels klasik yang seimbang dan stabil.</div>
                                    </label>

                                    <label class="relative flex flex-col p-3 rounded-md border cursor-pointer transition text-left"
                                        :class="aiProgramForm.intensity_tone === 'sharp' ? 'bg-slate-900 border-neon text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'">
                                        <input type="radio" value="sharp" v-model="aiProgramForm.intensity_tone" class="sr-only">
                                        <div class="text-xs font-semibold" :class="aiProgramForm.intensity_tone === 'sharp' ? 'text-neon' : 'text-slate-200'">Tajam & Progresif</div>
                                        <div class="text-[11px] text-slate-400 mt-1 leading-snug">Beban lebih keras, 2 sesi kualitas/minggu, progresif namun tetap aman.</div>
                                    </label>

                                    <label class="relative flex flex-col p-3 rounded-md border cursor-pointer transition text-left"
                                        :class="aiProgramForm.intensity_tone === 'conservative' ? 'bg-slate-900 border-neon text-white' : 'bg-slate-950 border-slate-800 text-slate-400 hover:border-slate-700'">
                                        <input type="radio" value="conservative" v-model="aiProgramForm.intensity_tone" class="sr-only">
                                        <div class="text-xs font-semibold" :class="aiProgramForm.intensity_tone === 'conservative' ? 'text-neon' : 'text-slate-200'">Konservatif & Aman</div>
                                        <div class="text-[11px] text-slate-400 mt-1 leading-snug">Volume lebih santai, porsi pemulihan lega untuk cegah cedera.</div>
                                    </label>
                                </div>
                            </div>

                            <!-- Runner Profile & Preferences -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Frekuensi Lari (Hari/Minggu)</label>
                                    <select v-model="aiProgramForm.frequency" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none">
                                        <option :value="3">3 Hari / Minggu</option>
                                        <option :value="4">4 Hari / Minggu</option>
                                        <option :value="5">5 Hari / Minggu</option>
                                        <option :value="6">6 Hari / Minggu</option>
                                        <option :value="7">7 Hari / Minggu</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Level Atlet</label>
                                    <select v-model="aiProgramForm.runner_level" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none">
                                        <option value="beginner">Beginner (Pemula)</option>
                                        <option value="intermediate">Intermediate (Menengah)</option>
                                        <option value="advanced">Advanced (Mahir)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Hari Long Run</label>
                                    <select v-model="aiProgramForm.long_run_day" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none">
                                        <option value="sunday">Minggu</option>
                                        <option value="saturday">Sabtu</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Strength & Climate Toggles -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                <div class="bg-slate-950 border border-slate-800 rounded-md p-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-medium text-white">Latihan Kekuatan (Strength)</div>
                                        <div class="text-[11px] text-slate-400">Sisipkan program core & strength</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <select v-if="aiProgramForm.include_strength" v-model="aiProgramForm.strength_type" class="bg-slate-900 border border-slate-700 text-white text-xs rounded p-1">
                                            <option value="bodyweight">Bodyweight</option>
                                            <option value="gym">Gym / Beban</option>
                                        </select>
                                        <input type="checkbox" v-model="aiProgramForm.include_strength" class="w-4 h-4 rounded text-neon focus:ring-0 bg-slate-900 border-slate-700">
                                    </div>
                                </div>

                                <div class="bg-slate-950 border border-slate-800 rounded-md p-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs font-medium text-white">Adaptasi Iklim Tropis</div>
                                        <div class="text-[11px] text-slate-400">Offset suhu & kelembapan Indonesia (+2-5% pace)</div>
                                    </div>
                                    <input type="checkbox" v-model="aiProgramForm.is_tropical" class="w-4 h-4 rounded text-neon focus:ring-0 bg-slate-900 border-slate-700">
                                </div>
                            </div>

                            <!-- Error Message -->
                            <div v-if="aiProgramError" class="text-rose-400 text-xs bg-rose-500/10 border border-rose-500/20 rounded-md p-3">
                                @{{ aiProgramError }}
                            </div>
                        </div>

                        <!-- Tab 2: Preview & Terapkan -->
                        <div v-show="aiProgramTab === 'preview' && aiProgramPreview" class="space-y-4">
                            <!-- Metrics Cards -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                <div class="bg-slate-950 border border-slate-800 rounded-md p-3 text-center">
                                    <div class="text-[11px] text-slate-400">Target & Durasi</div>
                                    <div class="text-sm font-bold text-white font-mono mt-0.5">@{{ aiProgramPreview?.target_distance?.toUpperCase() }} • @{{ aiProgramPreview?.weeks }} Mgg</div>
                                </div>
                                <div class="bg-slate-950 border border-slate-800 rounded-md p-3 text-center">
                                    <div class="text-[11px] text-slate-400">Target VDOT</div>
                                    <div class="text-sm font-bold text-neon font-mono mt-0.5">@{{ aiProgramPreview?.target_vdot }} <span class="text-xs text-slate-400 font-normal">(dari @{{ aiProgramPreview?.initial_vdot }})</span></div>
                                </div>
                                <div class="bg-slate-950 border border-slate-800 rounded-md p-3 text-center">
                                    <div class="text-[11px] text-slate-400">Mulai & Selesai</div>
                                    <div class="text-xs font-semibold text-slate-200 font-mono mt-0.5">@{{ formatDateShort(aiProgramPreview?.start_date) }} - @{{ formatDateShort(aiProgramPreview?.target_date) }}</div>
                                </div>
                                <div class="bg-slate-950 border border-slate-800 rounded-md p-3 text-center">
                                    <div class="text-[11px] text-slate-400">Fase & Intensitas</div>
                                    <div class="text-xs font-semibold text-white mt-0.5 capitalize">@{{ aiProgramPreview?.starting_phase }} • @{{ aiProgramPreview?.intensity_tone }}</div>
                                </div>
                            </div>

                            <!-- Phase Distribution Badges -->
                            <div class="bg-slate-950 border border-slate-800 rounded-md p-3.5 space-y-2">
                                <div class="text-xs font-semibold text-white">Alokasi Durasi Fase Latihan:</div>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2 text-center">
                                        <span class="text-slate-400 block text-[10px]">Base (Aerobik)</span>
                                        <span class="font-bold text-white font-mono">@{{ aiProgramPreview?.phases?.base ?? 0 }} Minggu</span>
                                    </div>
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2 text-center">
                                        <span class="text-slate-400 block text-[10px]">Build (Strength/Tempo)</span>
                                        <span class="font-bold text-white font-mono">@{{ aiProgramPreview?.phases?.strength ?? 0 }} Minggu</span>
                                    </div>
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2 text-center">
                                        <span class="text-slate-400 block text-[10px]">Peak (Speed/VO2Max)</span>
                                        <span class="font-bold text-white font-mono">@{{ aiProgramPreview?.phases?.speed ?? 0 }} Minggu</span>
                                    </div>
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2 text-center">
                                        <span class="text-slate-400 block text-[10px]">Taper (Sharpening)</span>
                                        <span class="font-bold text-white font-mono">@{{ aiProgramPreview?.phases?.taper ?? 1 }} Minggu</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Target Paces Breakdown -->
                            <div class="bg-slate-950 border border-slate-800 rounded-md p-3.5 space-y-2">
                                <div class="text-xs font-semibold text-white">Target Pace Pelatihan (Daniels VDOT):</div>
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs">
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2">
                                        <span class="text-emerald-400 block text-[10px] font-semibold">Easy (E)</span>
                                        <span class="font-mono text-white font-semibold">@{{ formatPace(aiProgramPreview?.paces?.E) }} /km</span>
                                    </div>
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2">
                                        <span class="text-sky-400 block text-[10px] font-semibold">Marathon (M)</span>
                                        <span class="font-mono text-white font-semibold">@{{ formatPace(aiProgramPreview?.paces?.M) }} /km</span>
                                    </div>
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2">
                                        <span class="text-yellow-400 block text-[10px] font-semibold">Threshold (T)</span>
                                        <span class="font-mono text-white font-semibold">@{{ formatPace(aiProgramPreview?.paces?.T) }} /km</span>
                                    </div>
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2">
                                        <span class="text-orange-400 block text-[10px] font-semibold">Interval (I)</span>
                                        <span class="font-mono text-white font-semibold">@{{ formatPace(aiProgramPreview?.paces?.I) }} /km</span>
                                    </div>
                                    <div class="bg-slate-900 border border-slate-800 rounded p-2">
                                        <span class="text-rose-400 block text-[10px] font-semibold">Repetition (R)</span>
                                        <span class="font-mono text-white font-semibold">@{{ formatPace(aiProgramPreview?.paces?.R) }} /km</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Sample Weekly Sessions Preview -->
                            <div class="bg-slate-950 border border-slate-800 rounded-md p-3.5 space-y-2">
                                <div class="flex justify-between items-center">
                                    <div class="text-xs font-semibold text-white">Pratinjau Sesi Program (@{{ (aiProgramPreview?.sessions || []).length }} Hari Terjadwal)</div>
                                    <span class="text-[10px] text-slate-400">Total volume progresif otomatis</span>
                                </div>
                                <div class="max-h-52 overflow-y-auto space-y-1.5 pr-1">
                                    <div v-for="(sess, idx) in (aiProgramPreview?.sessions || []).slice(0, 21)" :key="idx"
                                        class="flex items-center justify-between p-2 rounded bg-slate-900 border border-slate-800/80 text-xs">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-mono text-slate-400 w-10">Hari @{{ sess.day }}</span>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold"
                                                :class="{
                                                    'bg-emerald-500/20 text-emerald-300': sess.type === 'easy_run' || sess.type === 'recovery',
                                                    'bg-sky-500/20 text-sky-300': sess.type === 'long_run',
                                                    'bg-yellow-500/20 text-yellow-300': sess.type === 'tempo' || sess.type === 'threshold',
                                                    'bg-red-500/20 text-red-300': sess.type === 'interval' || sess.type === 'speed',
                                                    'bg-purple-500/20 text-purple-300': sess.type === 'strength',
                                                    'bg-slate-800 text-slate-400': sess.type === 'rest'
                                                }">
                                                @{{ (sess.type || 'rest').replace('_', ' ').toUpperCase() }}
                                            </span>
                                            <span class="text-white text-xs font-medium truncate max-w-[240px]">@{{ sess.name || sess.title }}</span>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span v-if="sess.target_distance || sess.distance" class="text-slate-300 font-mono text-[11px]">
                                                @{{ sess.target_distance || sess.distance }} km
                                            </span>
                                            <span v-else-if="sess.type === 'rest'" class="text-slate-500 text-[11px]">
                                                Rest Day
                                            </span>
                                        </div>
                                    </div>
                                    <div v-if="(aiProgramPreview?.sessions || []).length > 21" class="text-center py-1.5 text-[11px] text-slate-400">
                                        + @{{ (aiProgramPreview?.sessions || []).length - 21 }} sesi lainnya akan otomatis diterapkan ke kalender atlet.
                                    </div>
                                </div>
                            </div>

                            <!-- Error Message in Preview -->
                            <div v-if="aiProgramError" class="text-rose-400 text-xs bg-rose-500/10 border border-rose-500/20 rounded-md p-3">
                                @{{ aiProgramError }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 sm:p-5 border-t border-slate-800 bg-slate-900 rounded-b-lg flex justify-between items-center gap-3 flex-shrink-0">
                        <button type="button" @click="showAiProgramModal = false"
                            class="py-2 px-4 text-xs font-medium text-slate-300 bg-slate-800 rounded-md hover:bg-slate-700 transition border border-slate-700">
                            Tutup
                        </button>

                        <div class="flex items-center gap-2">
                            <button v-if="aiProgramTab === 'preview'" type="button" @click="aiProgramTab = 'config'"
                                class="py-2 px-3.5 text-xs font-medium text-slate-300 bg-slate-800 rounded-md hover:bg-slate-700 transition border border-slate-700">
                                &larr; Config
                            </button>

                            <button v-if="aiProgramTab === 'config'" type="button" @click="generateAiProgramPreview" :disabled="aiProgramLoading"
                                class="py-2 px-4 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition disabled:opacity-50">
                                <span v-if="aiProgramLoading">Memproses Kalkulasi AI...</span>
                                <span v-else>Generate Preview Program</span>
                            </button>

                            <button v-if="aiProgramTab === 'preview'" type="button" @click="applyAiProgram" :disabled="aiProgramApplying"
                                class="py-2 px-5 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition disabled:opacity-50 shadow-sm">
                                <span v-if="aiProgramApplying">Menerapkan ke Kalender...</span>
                                <span v-else>Terapkan</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Reschedule / Activate Program Modal -->
        <div v-if="showRescheduleModal" v-cloak class="fixed inset-0 z-[200] overflow-y-auto p-3 sm:p-4">
            <div class="fixed inset-0 bg-black/70" @click="showRescheduleModal = false"></div>
            <div class="flex min-h-full items-center justify-center relative pointer-events-none">
                <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-md shadow-2xl max-h-[calc(100vh-2.5rem)] flex flex-col">

                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 sm:p-5 border-b border-slate-800 flex-shrink-0 bg-slate-900 rounded-t-lg">
                        <div>
                            <h3 class="text-base font-semibold text-white">
                                {{ $enrollment->status === 'active' ? 'Reschedule Program' : 'Aktifkan Program Atlet' }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $enrollment->status === 'active' ? 'Jadwalkan ulang tanggal program latihan' : 'Tentukan tanggal mulai untuk mengaktifkan sesi latihan' }}
                            </p>
                        </div>
                        <button @click="showRescheduleModal = false" class="text-slate-400 hover:text-white transition text-lg">
                            &times;
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <div class="bg-slate-950 border border-slate-800 rounded-md p-3.5 space-y-1 text-xs">
                            <div class="text-slate-400">Program: <span class="font-semibold text-white">{{ $enrollment->program->title }}</span></div>
                            <div class="text-slate-400">Status: <span class="font-semibold text-white">{{ $enrollment->status === 'purchased' ? 'Belum Aktif' : ($enrollment->status === 'inactive' ? 'Expired' : ucfirst($enrollment->status)) }}</span></div>
                            <div class="text-slate-400">
                                Tanggal Aktif:
                                <span class="font-semibold text-white font-mono">
                                    {{ $enrollment->start_date ? \Carbon\Carbon::parse($enrollment->start_date)->format('d M Y') : 'Belum Ditentukan' }}
                                    @if($enrollment->end_date) &rarr; {{ \Carbon\Carbon::parse($enrollment->end_date)->format('d M Y') }} @endif
                                </span>
                            </div>
                            <div class="text-slate-400">Durasi: <span class="font-semibold text-white">{{ $enrollment->program->duration_weeks ?? 12 }} minggu</span></div>
                        </div>

                        <p class="text-slate-400 text-xs leading-relaxed">
                            Pilih tanggal mulai pertama untuk program ini. Seluruh sesi kalender latihan akan disesuaikan secara otomatis.
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal Mulai Program</label>
                                <input type="date" v-model="rescheduleForm.new_start_date"
                                    class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                            </div>

                            <!-- Quick Date Shortcuts -->
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1">Pintasan Tanggal</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" @click="setStartDateToday" class="py-1.5 px-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-medium rounded-md transition text-center truncate">Hari Ini</button>
                                    <button type="button" @click="setStartDateNextMonday" class="py-1.5 px-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-medium rounded-md transition text-center truncate">Senin Depan</button>
                                    <button type="button" @click="setStartDateNextMonth" class="py-1.5 px-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-300 text-xs font-medium rounded-md transition text-center truncate">Bulan Depan</button>
                                </div>
                            </div>

                            <!-- Preview end date -->
                            <div v-if="rescheduleForm.new_start_date" class="bg-slate-950 border border-slate-800 rounded-md p-3 text-xs flex justify-between items-center">
                                <span class="text-slate-400">Estimasi Selesai:</span>
                                <span class="text-white font-semibold font-mono">@{{ previewRescheduleEndDate }}</span>
                            </div>

                            <div v-if="rescheduleError" class="text-rose-400 text-xs bg-rose-500/10 border border-rose-500/20 rounded-md p-3">
                                @{{ rescheduleError }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 sm:p-5 border-t border-slate-800 bg-slate-900 rounded-b-lg flex justify-end gap-2.5 sm:gap-3 flex-shrink-0">
                        <button type="button" @click="showRescheduleModal = false"
                            class="flex-1 py-2 text-xs font-medium text-slate-300 bg-slate-800 rounded-md hover:bg-slate-700 transition border border-slate-700">
                            Batal
                        </button>
                        <button type="button" @click="submitReschedule" :disabled="rescheduleLoading"
                            class="flex-1 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition disabled:opacity-50">
                            <span v-if="rescheduleLoading">Menyimpan...</span>
                            <span v-else>{{ $enrollment->status === 'active' ? 'Simpan Jadwal' : 'Aktifkan Program' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Send Program Reminder Modal -->
        <div v-if="showReminderModal" v-cloak class="fixed inset-0 z-[200] overflow-y-auto p-3 sm:p-4">
            <div class="fixed inset-0 bg-black/70" @click="showReminderModal = false"></div>
            <div class="flex min-h-full items-center justify-center relative pointer-events-none">
                <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-md shadow-2xl max-h-[calc(100vh-2.5rem)] flex flex-col">

                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 sm:p-5 border-b border-slate-800 flex-shrink-0 bg-slate-900 rounded-t-lg">
                        <div>
                            <h3 class="text-base font-semibold text-white">Kirim Pengingat Latihan</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Kirim pesan pengingat jadwal sesi ke atlet</p>
                        </div>
                        <button @click="showReminderModal = false" class="text-slate-400 hover:text-white transition text-lg">
                            &times;
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <div class="bg-slate-950 border border-slate-800 rounded-md p-3.5 space-y-1 text-xs" v-if="reminderSessionInfo.title">
                            <div class="flex items-start gap-1.5">
                                <span class="text-slate-400 w-16 flex-shrink-0">Sesi:</span>
                                <span class="font-semibold text-white">@{{ reminderSessionInfo.title }}</span>
                            </div>
                            <div class="flex items-start gap-1.5" v-if="reminderSessionInfo.distance && reminderSessionInfo.distance !== '-'">
                                <span class="text-slate-400 w-16 flex-shrink-0">Jarak:</span>
                                <span class="font-semibold text-white font-mono">@{{ reminderSessionInfo.distance }}</span>
                            </div>
                            <div class="flex items-start gap-1.5" v-if="reminderSessionInfo.pace">
                                <span class="text-slate-400 w-16 flex-shrink-0">Pace:</span>
                                <span class="font-semibold text-white font-mono">@{{ reminderSessionInfo.pace }}</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Saluran Pengiriman</label>
                                <select v-model="reminderForm.channel" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none">
                                    <option value="both">WhatsApp & Email</option>
                                    <option value="wa">WhatsApp Saja</option>
                                    <option value="email">Email Saja</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Pesan Pengingat</label>
                                <textarea v-model="reminderForm.custom_message" rows="4" placeholder="Tulis pesan pengingat..."
                                    class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none resize-none leading-relaxed"></textarea>
                            </div>

                            <div v-if="reminderError" class="text-rose-400 text-xs bg-rose-500/10 border border-rose-500/20 rounded-md p-3">
                                @{{ reminderError }}
                            </div>

                            <div v-if="reminderSuccess" class="text-emerald-400 text-xs bg-emerald-500/10 border border-emerald-500/20 rounded-md p-3">
                                @{{ reminderSuccess }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 sm:p-5 border-t border-slate-800 bg-slate-900 rounded-b-lg flex justify-end gap-2.5 sm:gap-3 flex-shrink-0">
                        <button type="button" @click="showReminderModal = false"
                            class="flex-1 py-2 text-xs font-medium text-slate-300 bg-slate-800 rounded-md hover:bg-slate-700 transition border border-slate-700">
                            Batal
                        </button>
                        <button type="button" @click="submitReminder" :disabled="reminderLoading"
                            class="flex-1 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition disabled:opacity-50">
                            @{{ reminderLoading ? 'Mengirim...' : 'Kirim Pengingat' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Target Mingguan Modal -->
        <div v-if="showWeeklyTargetModal" v-cloak class="fixed inset-0 z-[200] overflow-y-auto p-3 sm:p-4">
            <div class="fixed inset-0 bg-black/70" @click="showWeeklyTargetModal = false"></div>
            <div class="flex min-h-full items-center justify-center relative pointer-events-none">
                <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-md shadow-2xl max-h-[calc(100vh-2.5rem)] flex flex-col">
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 sm:p-5 border-b border-slate-800 flex-shrink-0 bg-slate-900 rounded-t-lg">
                        <h3 class="text-white font-semibold text-base">Update Target Mingguan</h3>
                        <button @click="showWeeklyTargetModal = false" class="text-slate-400 hover:text-white transition text-lg">
                            &times;
                        </button>
                    </div>
                    <!-- Body -->
                    <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1">Target Volume Jarak Mingguan (KM)</label>
                            <input type="number" step="0.1" v-model="weeklyTargetForm.weekly_km_target" class="w-full bg-slate-950 border border-slate-800 rounded-md p-2.5 text-white text-sm focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                            <p class="text-xs text-slate-400 mt-1.5">Target total jarak lari yang diharapkan dicapai atlet per minggu.</p>
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="p-4 sm:p-5 border-t border-slate-800 bg-slate-900 rounded-b-lg flex justify-end gap-2.5 sm:gap-3 flex-shrink-0">
                        <button type="button" class="flex-1 py-2 text-xs font-medium text-slate-300 bg-slate-800 rounded-md hover:bg-slate-700 transition border border-slate-700" @click="showWeeklyTargetModal = false">Batal</button>
                        <button type="button" @click="updateWeeklyTarget" class="flex-1 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition disabled:opacity-50" :disabled="weeklyTargetLoading">
                            <span v-if="weeklyTargetLoading">Menyimpan...</span>
                            <span v-else>Simpan Target</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update VDOT / PB Modal -->
        <div v-if="showVdotModal" v-cloak class="fixed inset-0 z-[200] overflow-y-auto p-3 sm:p-4">
            <div class="fixed inset-0 bg-black/70" @click="showVdotModal = false"></div>
            <div class="flex min-h-full items-center justify-center relative pointer-events-none">
                <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-lg shadow-2xl max-h-[calc(100vh-2.5rem)] flex flex-col">

                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 sm:p-5 border-b border-slate-800 flex-shrink-0 bg-slate-900 rounded-t-lg">
                        <div>
                            <h3 class="text-base font-semibold text-white">Update PB & VDOT Atlet</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Sesuaikan hasil tes/PB untuk memperbarui kalkulasi pace latihan atlet</p>
                        </div>
                        <button @click="showVdotModal = false" class="text-slate-400 hover:text-white transition text-lg">
                            &times;
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-4 sm:p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                        <!-- Mode Selector Tabs -->
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-2">Metode Pengukuran</label>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-1.5 bg-slate-950 p-1.5 rounded-md border border-slate-800">
                                <button type="button" @click="vdotForm.mode = 'pb'"
                                    :class="vdotForm.mode === 'pb' ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white font-normal'"
                                    class="py-1.5 px-1 text-xs rounded transition text-center truncate">
                                    Race PB
                                </button>
                                <button type="button" @click="vdotForm.mode = 'cooper'"
                                    :class="vdotForm.mode === 'cooper' ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white font-normal'"
                                    class="py-1.5 px-1 text-xs rounded transition text-center truncate">
                                    Cooper 12M
                                </button>
                                <button type="button" @click="vdotForm.mode = 'balke'"
                                    :class="vdotForm.mode === 'balke' ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white font-normal'"
                                    class="py-1.5 px-1 text-xs rounded transition text-center truncate">
                                    Balke 15M
                                </button>
                                <button type="button" @click="vdotForm.mode = 'direct'"
                                    :class="vdotForm.mode === 'direct' ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white font-normal'"
                                    class="py-1.5 px-1 text-xs rounded transition text-center truncate">
                                    Skor Langsung
                                </button>
                            </div>
                        </div>

                        <!-- Mode Specific Form Fields -->
                        <div class="space-y-4">
                            <!-- Race PB -->
                            <div v-if="vdotForm.mode === 'pb'" class="space-y-3">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-300 mb-1">Jarak Lomba / PB</label>
                                        <select v-model="vdotForm.pb_distance" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none">
                                            <option value="5k">5K (5.000m)</option>
                                            <option value="10k">10K (10.000m)</option>
                                            <option value="21k">Half Marathon (21.097m)</option>
                                            <option value="42k">Full Marathon (42.195m)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-300 mb-1">Waktu PB (MM:SS / HH:MM:SS)</label>
                                        <input type="text" v-model="vdotForm.pb_time" placeholder="Contoh: 22:30 / 01:45:00"
                                            class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                                    </div>
                                </div>
                            </div>

                            <!-- Cooper 12 Min -->
                            <div v-if="vdotForm.mode === 'cooper'" class="space-y-2">
                                <label class="block text-xs font-medium text-slate-300">Jarak Tes Cooper 12 Menit (Meter)</label>
                                <input type="number" v-model="vdotForm.cooper_distance" placeholder="Contoh: 2800" min="500" max="10000"
                                    class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                            </div>

                            <!-- Balke 15 Min -->
                            <div v-if="vdotForm.mode === 'balke'" class="space-y-2">
                                <label class="block text-xs font-medium text-slate-300">Jarak Tes Balke 15 Menit (Meter)</label>
                                <input type="number" v-model="vdotForm.balke_distance" placeholder="Contoh: 3400" min="500" max="10000"
                                    class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                            </div>

                            <!-- Direct VDOT -->
                            <div v-if="vdotForm.mode === 'direct'" class="space-y-2">
                                <label class="block text-xs font-medium text-slate-300">Skor VDOT Langsung</label>
                                <input type="number" step="0.1" v-model="vdotForm.vdot_score" placeholder="Contoh: 45.0" min="10" max="85"
                                    class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 focus:ring-1 focus:ring-slate-500 outline-none font-mono">
                            </div>

                            <!-- Live VDOT Preview -->
                            <div v-if="previewVdot" class="bg-slate-950 border border-slate-800 rounded-md p-3 flex justify-between items-center text-xs">
                                <span class="text-slate-400">Estimasi Skor VDOT Baru:</span>
                                <span class="text-white font-semibold font-mono text-sm">@{{ previewVdot }}</span>
                            </div>

                            <div v-if="vdotError" class="text-rose-400 text-xs bg-rose-500/10 border border-rose-500/20 rounded-md p-3">
                                @{{ vdotError }}
                            </div>
                            <div v-if="vdotSuccess" class="text-emerald-400 text-xs bg-emerald-500/10 border border-emerald-500/20 rounded-md p-3">
                                @{{ vdotSuccess }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-4 sm:p-5 border-t border-slate-800 bg-slate-900 rounded-b-lg flex justify-end gap-2.5 sm:gap-3 flex-shrink-0">
                        <button type="button" @click="showVdotModal = false"
                            class="flex-1 py-2 text-xs font-medium text-slate-300 bg-slate-800 rounded-md hover:bg-slate-700 transition border border-slate-700">
                            Batal
                        </button>
                        <button type="button" @click="submitUpdateVdot" :disabled="vdotLoading"
                            class="flex-1 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition disabled:opacity-50">
                            @{{ vdotLoading ? 'Menyimpan...' : 'Simpan PB & VDOT' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Race Event Modal -->
        <div v-if="showRaceModal" v-cloak class="fixed inset-0 z-[200] overflow-y-auto p-3 sm:p-4">
            <div class="fixed inset-0 bg-black/70" @click="showRaceModal = false"></div>
            <div class="flex min-h-full items-center justify-center relative pointer-events-none">
                <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-sm shadow-2xl max-h-[calc(100vh-2.5rem)] flex flex-col">
                    <!-- Header -->
                    <div class="flex justify-between items-center p-4 sm:p-5 border-b border-slate-800 flex-shrink-0 bg-slate-900 rounded-t-lg">
                        <h3 class="text-base font-semibold text-white">Tambah Event Lomba</h3>
                        <button @click="showRaceModal = false" class="text-slate-400 hover:text-white transition text-lg">
                            &times;
                        </button>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="saveRace" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                        <div class="p-4 sm:p-5 space-y-3.5 overflow-y-auto flex-1 min-h-0">
                            <!-- RuangLari Import -->
                            <div class="bg-slate-950 p-2.5 rounded-md border border-slate-800 relative">
                                <label class="text-xs font-medium text-slate-400 block mb-1.5">Cari Event RuangLari</label>
                                
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        v-model="eventSearchQuery"
                                        @focus="showEventDropdown = true"
                                        @blur="hideEventDropdown"
                                        placeholder="Ketik nama event..."
                                        class="w-full bg-slate-900 border border-slate-800 rounded-md px-3 py-2 text-white text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none"
                                    >
                                    <button v-if="eventSearchQuery" type="button" @click="eventSearchQuery = ''; showEventDropdown = false" class="absolute right-3 top-2.5 text-slate-400 hover:text-white text-xs">&times;</button>
                                </div>

                                <!-- Dropdown List -->
                                <div v-if="showEventDropdown && filteredEvents.length > 0" 
                                    class="absolute left-0 right-0 mt-1.5 bg-slate-900 border border-slate-700 rounded-md shadow-xl z-50 max-h-48 overflow-y-auto">
                                    <ul>
                                        <li v-for="event in filteredEvents" :key="event.id"
                                            @click="selectRuangLariEvent(event)"
                                            class="px-3 py-2 hover:bg-slate-800 cursor-pointer border-b border-slate-800 last:border-0 text-slate-200"
                                        >
                                            <div class="text-xs font-semibold text-white">@{{ event.name || event.title }}</div>
                                            <div class="text-xs text-slate-400 flex justify-between mt-0.5 font-mono">
                                                <span>@{{ event.date || event.start_at }}</span>
                                                <span>@{{ event.location || event.location_name }}</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Nama Event</label>
                                <input v-model="raceForm.name" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-md p-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none" placeholder="misal: Jakarta Marathon 2026" required>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Tanggal</label>
                                    <input v-model="raceForm.date" type="date" class="w-full bg-slate-950 border border-slate-800 rounded-md p-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none font-mono" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-300 mb-1">Kategori Jarak</label>
                                    <select v-model="raceForm.distance" class="w-full bg-slate-950 border border-slate-800 rounded-md p-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none">
                                        <option value="5k">5K</option>
                                        <option value="10k">10K</option>
                                        <option value="21k">Half Marathon (21K)</option>
                                        <option value="42k">Full Marathon (42K)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Target Waktu (HH:MM:SS / MM:SS)</label>
                                <input v-model="raceForm.goal_time" type="text" class="w-full bg-slate-950 border border-slate-800 rounded-md p-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none font-mono" placeholder="misal: 01:45:00">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-300 mb-1">Catatan</label>
                                <textarea v-model="raceForm.notes" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-md p-2 text-white text-xs focus:ring-1 focus:ring-slate-500 outline-none resize-none" placeholder="Catatan event..."></textarea>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-4 border-t border-slate-800 bg-slate-900 rounded-b-lg flex justify-end gap-2.5 flex-shrink-0">
                            <button type="button" @click="showRaceModal = false" class="flex-1 py-2 text-xs font-medium text-slate-300 bg-slate-800 rounded-md hover:bg-slate-700 transition border border-slate-700">Batal</button>
                            <button type="submit" :disabled="loading" class="flex-1 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition disabled:opacity-50">
                                @{{ loading ? 'Menyimpan...' : 'Simpan Event' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Edit Pace Latihan (Custom Pace) -->
        <div v-if="showPaceModal" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50 overflow-y-auto">
            <div class="bg-slate-900 border border-slate-800 rounded-lg max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 relative">
                <div class="flex justify-between items-center border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-white font-semibold text-base">Edit Pace Latihan Atlet</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Tentukan target pace spesifik untuk masing-masing zona.</p>
                    </div>
                    <button @click="showPaceModal = false" class="text-slate-400 hover:text-white transition text-lg">
                        &times;
                    </button>
                </div>

                <div class="space-y-3">
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Format <strong>MM:SS</strong> (contoh: <code>05:30</code> untuk 5 menit 30 detik per KM). Kosongkan kolom untuk mengikuti kalkulasi otomatis VDOT.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-emerald-400 mb-1">Easy Pace (E)</label>
                            <input type="text" v-model="paceForm.E" placeholder="misal 05:30" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs font-mono focus:ring-1 focus:ring-slate-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-sky-400 mb-1">Marathon Pace (M)</label>
                            <input type="text" v-model="paceForm.M" placeholder="misal 04:50" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs font-mono focus:ring-1 focus:ring-slate-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-yellow-400 mb-1">Threshold Pace (T)</label>
                            <input type="text" v-model="paceForm.T" placeholder="misal 04:30" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs font-mono focus:ring-1 focus:ring-slate-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-orange-400 mb-1">Interval Pace (I)</label>
                            <input type="text" v-model="paceForm.I" placeholder="misal 04:00" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs font-mono focus:ring-1 focus:ring-slate-500 outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-rose-400 mb-1">Repetition Pace (R)</label>
                            <input type="text" v-model="paceForm.R" placeholder="misal 03:45" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs font-mono focus:ring-1 focus:ring-slate-500 outline-none">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 pt-3 border-t border-slate-800">
                    <button type="button" @click="updatePaces(true)" :disabled="paceLoading" class="w-full sm:w-auto px-3 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-medium text-xs transition">
                        Reset VDOT
                    </button>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <button type="button" @click="showPaceModal = false" class="flex-1 sm:flex-initial px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-white font-medium text-xs transition">
                            Batal
                        </button>
                        <button type="button" @click="updatePaces(false)" :disabled="paceLoading" class="flex-1 sm:flex-initial px-4 py-2 rounded-md bg-neon text-dark font-semibold text-xs hover:bg-white transition">
                            Simpan Pace
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comprehensive Strava Activity Detail & Analysis Modal -->
        <div v-if="showStravaModal" v-cloak class="fixed inset-0 z-[220] overflow-y-auto p-3 sm:p-4">
            <div class="fixed inset-0 bg-black/80" @click="showStravaModal = false"></div>
            <div class="flex min-h-full items-center justify-center relative pointer-events-none">
                <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-4xl shadow-2xl max-h-[calc(100vh-2rem)] flex flex-col overflow-hidden">
                    
                    <!-- Modal Header -->
                    <div class="p-4 sm:p-5 border-b border-slate-800 flex-shrink-0 bg-slate-900 flex justify-between items-start gap-4">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="bg-[#FC4C02] text-white font-bold text-[10px] uppercase tracking-wider px-2 py-0.5 rounded">
                                    Strava Activity
                                </span>
                                <span class="bg-slate-800 text-slate-300 text-xs px-2 py-0.5 rounded border border-slate-700 font-mono">
                                    @{{ stravaMetrics?.type || 'Run' }}
                                </span>
                                <span v-if="stravaMetrics?.device_name" class="text-xs text-slate-400">
                                    @{{ stravaMetrics.device_name }}
                                </span>
                            </div>
                            <h3 class="text-white font-bold text-lg sm:text-xl">
                                @{{ stravaMetrics?.name || 'Detail Sesi Lari Strava' }}
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5 font-mono">
                                @{{ stravaMetrics?.start_date ? formatDate(stravaMetrics.start_date) : '-' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a v-if="stravaMetrics?.strava_activity_id" :href="'https://www.strava.com/activities/' + stravaMetrics.strava_activity_id" target="_blank" class="px-2.5 py-1.5 rounded-md bg-[#FC4C02]/10 hover:bg-[#FC4C02]/20 text-[#FC4C02] border border-[#FC4C02]/30 text-xs font-semibold transition inline-flex items-center gap-1.5">
                                <span>Strava.com</span>
                            </a>
                            <button @click="showStravaModal = false" class="text-slate-400 hover:text-white transition text-2xl leading-none px-1">
                                &times;
                            </button>
                        </div>
                    </div>

                    <!-- Top KPI Summary Bar -->
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 p-3 sm:p-4 bg-slate-950 border-b border-slate-800 flex-shrink-0">
                        <div class="bg-slate-900/90 p-2 rounded-md border border-slate-800 text-center">
                            <div class="text-[11px] text-slate-400">Jarak</div>
                            <div class="font-bold text-white font-mono text-sm sm:text-base mt-0.5">
                                @{{ stravaMetrics?.distance_km ? stravaMetrics.distance_km : (stravaMetrics?.distance_m ? (stravaMetrics.distance_m / 1000).toFixed(2) : '0') }} <span class="text-xs font-normal text-slate-400">km</span>
                            </div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-md border border-slate-800 text-center">
                            <div class="text-[11px] text-slate-400">Waktu Bergerak</div>
                            <div class="font-bold text-white font-mono text-sm sm:text-base mt-0.5">
                                @{{ stravaMetrics?.moving_time_s ? (new Date(stravaMetrics.moving_time_s * 1000).toISOString().substr(11, 8)) : '-' }}
                            </div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-md border border-slate-800 text-center">
                            <div class="text-[11px] text-slate-400">Avg Pace</div>
                            <div class="font-bold text-neon font-mono text-sm sm:text-base mt-0.5">
                                @{{ stravaMetrics?.pace ? stravaMetrics.pace : '-' }} <span class="text-xs font-normal text-slate-400">/km</span>
                            </div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-md border border-slate-800 text-center">
                            <div class="text-[11px] text-slate-400">Avg Heart Rate</div>
                            <div class="font-bold text-rose-400 font-mono text-sm sm:text-base mt-0.5">
                                @{{ stravaMetrics?.average_heartrate ? Math.round(stravaMetrics.average_heartrate) : '-' }} <span class="text-xs font-normal text-slate-400">bpm</span>
                            </div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-md border border-slate-800 text-center">
                            <div class="text-[11px] text-slate-400">Avg Cadence</div>
                            <div class="font-bold text-amber-400 font-mono text-sm sm:text-base mt-0.5">
                                @{{ stravaMetrics?.average_cadence ? Math.round(stravaMetrics.average_cadence) : '-' }} <span class="text-xs font-normal text-slate-400">spm</span>
                            </div>
                        </div>
                        <div class="bg-slate-900/90 p-2 rounded-md border border-slate-800 text-center">
                            <div class="text-[11px] text-slate-400">Elevasi Gain</div>
                            <div class="font-bold text-slate-200 font-mono text-sm sm:text-base mt-0.5">
                                @{{ stravaMetrics?.total_elevation_gain ? Math.round(stravaMetrics.total_elevation_gain) : '0' }} <span class="text-xs font-normal text-slate-400">m</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Header -->
                    <div class="flex border-b border-slate-800 bg-slate-900 px-4 pt-2 gap-2 flex-shrink-0 overflow-x-auto">
                        <button type="button" @click="switchStravaTab('overview')" class="px-3 py-2 text-xs font-semibold border-b-2 transition whitespace-nowrap" :class="stravaModalTab === 'overview' ? 'border-neon text-white' : 'border-transparent text-slate-400 hover:text-slate-200'">
                            Ikhtisar & Zona
                        </button>
                        <button type="button" @click="switchStravaTab('telemetry')" class="px-3 py-2 text-xs font-semibold border-b-2 transition whitespace-nowrap" :class="stravaModalTab === 'telemetry' ? 'border-neon text-white' : 'border-transparent text-slate-400 hover:text-slate-200'">
                            Grafik Telemetri
                        </button>
                        <button type="button" @click="switchStravaTab('splits')" class="px-3 py-2 text-xs font-semibold border-b-2 transition whitespace-nowrap" :class="stravaModalTab === 'splits' ? 'border-neon text-white' : 'border-transparent text-slate-400 hover:text-slate-200'">
                            Laps & Splits KM (@{{ (stravaSplits?.length || stravaLaps?.length || 0) }})
                        </button>
                        <button type="button" @click="switchStravaTab('ai_wa')" class="px-3 py-2 text-xs font-semibold border-b-2 transition whitespace-nowrap flex items-center gap-1.5" :class="stravaModalTab === 'ai_wa' ? 'border-neon text-white' : 'border-transparent text-slate-400 hover:text-slate-200'">
                            <span>Analisis AI & Kirim WhatsApp</span>
                            <span v-if="stravaAiAnalysis" class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </button>
                    </div>

                    <!-- Modal Body Scrollable -->
                    <div class="p-4 sm:p-5 overflow-y-auto flex-1 min-h-0 space-y-4">
                        
                        <!-- TAB 1: IKHTISAR & ZONA -->
                        <div v-if="stravaModalTab === 'overview'" class="space-y-4">
                            <!-- Heart Rate Zones Distribution -->
                            <div class="bg-slate-950 p-4 rounded-lg border border-slate-800">
                                <div class="flex justify-between items-center mb-3">
                                    <div>
                                        <h4 class="text-xs font-semibold text-white">Distribusi Zona Heart Rate</h4>
                                        <p class="text-[11px] text-slate-400">Estimasi waktu pada masing-masing zona denyut jantung</p>
                                    </div>
                                    <div class="text-xs text-slate-400 font-mono">
                                        Max HR: <strong class="text-white">@{{ stravaMetrics?.max_heartrate ? Math.round(stravaMetrics.max_heartrate) : '-' }} bpm</strong>
                                    </div>
                                </div>

                                <div v-if="stravaMetrics?.hr_zones && stravaMetrics.hr_zones.length > 0" class="space-y-2.5">
                                    <div v-for="(z, idx) in stravaMetrics.hr_zones" :key="idx" class="space-y-1">
                                        <div class="flex justify-between items-center text-xs">
                                            <div class="flex items-center gap-2">
                                                <span class="w-2.5 h-2.5 rounded-sm" :style="{ backgroundColor: z.color }"></span>
                                                <span class="font-medium text-slate-200">@{{ z.name }}</span>
                                                <span class="text-[11px] text-slate-400 font-mono">(@{{ z.range }})</span>
                                            </div>
                                            <div class="font-mono text-white font-semibold">
                                                @{{ z.duration }} <span class="text-slate-400 font-normal">(@{{ z.percentage }}%)</span>
                                            </div>
                                        </div>
                                        <div class="w-full bg-slate-900 rounded-sm h-2 overflow-hidden border border-slate-800">
                                            <div class="h-full rounded-sm transition-all duration-300" :style="{ width: z.percentage + '%', backgroundColor: z.color }"></div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-6 text-slate-400 text-xs italic bg-slate-900/50 rounded-md border border-slate-800">
                                    Data streams denyut jantung (HR) tidak tersedia pada aktivitas ini.
                                </div>
                            </div>

                            <!-- Additional Telemetry Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="bg-slate-950 p-3.5 rounded-lg border border-slate-800 space-y-2">
                                    <h4 class="text-xs font-semibold text-white">Data Waktu & Energi</h4>
                                    <div class="space-y-1.5 text-xs">
                                        <div class="flex justify-between py-1 border-b border-slate-900">
                                            <span class="text-slate-400">Total Waktu (Elapsed)</span>
                                            <span class="text-white font-mono">@{{ stravaMetrics?.elapsed_time_s ? (new Date(stravaMetrics.elapsed_time_s * 1000).toISOString().substr(11, 8)) : '-' }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-slate-900">
                                            <span class="text-slate-400">Waktu Istirahat / Jeda</span>
                                            <span class="text-white font-mono">@{{ stravaMetrics?.pause_time_s ? (new Date(stravaMetrics.pause_time_s * 1000).toISOString().substr(11, 8)) : '00:00:00' }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-slate-900">
                                            <span class="text-slate-400">Kalori Terbakar</span>
                                            <span class="text-white font-mono">@{{ stravaMetrics?.calories ? Math.round(stravaMetrics.calories) + ' kcal' : '-' }}</span>
                                        </div>
                                        <div class="flex justify-between py-1">
                                            <span class="text-slate-400">Energi (Kilojoules)</span>
                                            <span class="text-white font-mono">@{{ stravaMetrics?.kilojoules ? Math.round(stravaMetrics.kilojoules) + ' kJ' : '-' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-slate-950 p-3.5 rounded-lg border border-slate-800 space-y-2">
                                    <h4 class="text-xs font-semibold text-white">Elevasi & Lingkungan</h4>
                                    <div class="space-y-1.5 text-xs">
                                        <div class="flex justify-between py-1 border-b border-slate-900">
                                            <span class="text-slate-400">Total Elevasi Naik</span>
                                            <span class="text-white font-mono">@{{ stravaMetrics?.total_elevation_gain ? Math.round(stravaMetrics.total_elevation_gain) + ' m' : '0 m' }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-slate-900">
                                            <span class="text-slate-400">Titik Tertinggi</span>
                                            <span class="text-white font-mono">@{{ stravaMetrics?.elev_high ? Math.round(stravaMetrics.elev_high) + ' m' : '-' }}</span>
                                        </div>
                                        <div class="flex justify-between py-1 border-b border-slate-900">
                                            <span class="text-slate-400">Titik Terendah</span>
                                            <span class="text-white font-mono">@{{ stravaMetrics?.elev_low ? Math.round(stravaMetrics.elev_low) + ' m' : '-' }}</span>
                                        </div>
                                        <div class="flex justify-between py-1">
                                            <span class="text-slate-400">Perangkat Rekam</span>
                                            <span class="text-white">@{{ stravaMetrics?.device_name || 'GPS Device' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Best Efforts -->
                            <div v-if="stravaMetrics?.best_efforts && stravaMetrics.best_efforts.length > 0" class="bg-slate-950 p-4 rounded-lg border border-slate-800">
                                <h4 class="text-xs font-semibold text-white mb-2.5">Best Efforts (Segmen Jarak Tertentu)</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <div v-for="(be, bIdx) in stravaMetrics.best_efforts" :key="bIdx" class="bg-slate-900 p-2.5 rounded-md border border-slate-800">
                                        <div class="flex justify-between items-center text-xs text-slate-400 mb-1">
                                            <span class="font-semibold text-slate-200">@{{ be.name }}</span>
                                            <span v-if="be.pr_rank" class="bg-amber-500/20 text-amber-300 font-mono text-[10px] px-1 rounded">PR #@{{ be.pr_rank }}</span>
                                        </div>
                                        <div class="text-white font-mono font-bold text-sm">
                                            @{{ be.moving_time_s ? (new Date(be.moving_time_s * 1000).toISOString().substr(11, 8)) : '-' }}
                                        </div>
                                        <div class="text-[11px] text-neon font-mono mt-0.5">
                                            @{{ be.pace ? be.pace + ' /km' : '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: GRAFIK TELEMETRI -->
                        <div v-show="stravaModalTab === 'telemetry'" class="space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 bg-slate-950 p-3 rounded-lg border border-slate-800">
                                <div class="text-xs font-medium text-slate-300">Tampilkan Grafik:</div>
                                <div class="flex flex-wrap items-center gap-3 text-xs">
                                    <label class="inline-flex items-center gap-1.5 text-sky-400 cursor-pointer">
                                        <input type="checkbox" v-model="telemetryFilters.pace" @change="renderTelemetryChart" class="rounded bg-slate-900 border-slate-700 text-sky-500">
                                        <span>Pace (min/km)</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 text-rose-400 cursor-pointer">
                                        <input type="checkbox" v-model="telemetryFilters.heartrate" @change="renderTelemetryChart" class="rounded bg-slate-900 border-slate-700 text-rose-500">
                                        <span>Heart Rate (bpm)</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 text-slate-400 cursor-pointer">
                                        <input type="checkbox" v-model="telemetryFilters.altitude" @change="renderTelemetryChart" class="rounded bg-slate-900 border-slate-700 text-slate-400">
                                        <span>Elevasi (m)</span>
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 text-amber-400 cursor-pointer">
                                        <input type="checkbox" v-model="telemetryFilters.cadence" @change="renderTelemetryChart" class="rounded bg-slate-900 border-slate-700 text-amber-500">
                                        <span>Cadence (spm)</span>
                                    </label>
                                </div>
                            </div>

                            <div class="bg-slate-950 p-4 rounded-lg border border-slate-800 relative h-72 sm:h-80">
                                <div v-if="stravaStreamsLoading" class="absolute inset-0 flex items-center justify-center bg-slate-950/80 z-10">
                                    <div class="text-xs text-slate-400">Memuat telemetri streams...</div>
                                </div>
                                <div v-else-if="stravaStreamsError" class="absolute inset-0 flex items-center justify-center bg-slate-950/80 z-10">
                                    <div class="text-xs text-rose-400">@{{ stravaStreamsError }}</div>
                                </div>
                                <canvas id="stravaChartCanvas" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <!-- TAB 3: LAPS & SPLITS KM -->
                        <div v-if="stravaModalTab === 'splits'" class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="inline-flex rounded-md bg-slate-950 p-1 border border-slate-800">
                                    <button type="button" @click="activeSplitType = 'splits'" class="px-3 py-1 text-xs rounded font-medium transition" :class="activeSplitType === 'splits' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'">
                                        Splits per KM (@{{ stravaSplits?.length || 0 }})
                                    </button>
                                    <button type="button" @click="activeSplitType = 'laps'" class="px-3 py-1 text-xs rounded font-medium transition" :class="activeSplitType === 'laps' ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white'">
                                        Laps (@{{ stravaLaps?.length || 0 }})
                                    </button>
                                </div>
                                <div class="text-xs text-slate-400">
                                    Total Sesi: <strong class="text-white font-mono">@{{ stravaMetrics?.distance_km || '-' }} km</strong>
                                </div>
                            </div>

                            <!-- Splits Table -->
                            <div v-if="activeSplitType === 'splits'" class="overflow-x-auto rounded-lg border border-slate-800 bg-slate-950">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-slate-900 border-b border-slate-800 text-slate-400 font-semibold uppercase text-[10px]">
                                        <tr>
                                            <th class="p-2.5">KM</th>
                                            <th class="p-2.5">Pace (/km)</th>
                                            <th class="p-2.5">Waktu</th>
                                            <th class="p-2.5">Elevasi</th>
                                            <th class="p-2.5">Avg HR</th>
                                            <th class="p-2.5">Cadence</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-850 font-mono">
                                        <tr v-for="s in stravaSplits" :key="s.split" class="hover:bg-slate-900/50 transition">
                                            <td class="p-2.5 font-bold text-slate-300">KM @{{ s.split }}</td>
                                            <td class="p-2.5 font-bold text-white">@{{ s.pace || '-' }}</td>
                                            <td class="p-2.5 text-slate-300">@{{ s.moving_time_s ? (new Date(s.moving_time_s * 1000).toISOString().substr(14, 5)) : '-' }}</td>
                                            <td class="p-2.5 text-slate-300">
                                                <span :class="(s.elevation_difference || 0) > 0 ? 'text-emerald-400' : ((s.elevation_difference || 0) < 0 ? 'text-sky-400' : 'text-slate-400')">
                                                    @{{ s.elevation_difference ? (s.elevation_difference > 0 ? '+' : '') + Math.round(s.elevation_difference) + 'm' : '0m' }}
                                                </span>
                                            </td>
                                            <td class="p-2.5 text-rose-400">@{{ s.average_heartrate ? Math.round(s.average_heartrate) + ' bpm' : '-' }}</td>
                                            <td class="p-2.5 text-amber-400">@{{ s.average_cadence ? Math.round(s.average_cadence) + ' spm' : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Laps Table -->
                            <div v-else class="overflow-x-auto rounded-lg border border-slate-800 bg-slate-950">
                                <table class="w-full text-xs text-left">
                                    <thead class="bg-slate-900 border-b border-slate-800 text-slate-400 font-semibold uppercase text-[10px]">
                                        <tr>
                                            <th class="p-2.5">Lap</th>
                                            <th class="p-2.5">Jarak</th>
                                            <th class="p-2.5">Pace (/km)</th>
                                            <th class="p-2.5">Durasi</th>
                                            <th class="p-2.5">Avg / Max HR</th>
                                            <th class="p-2.5">Cadence</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-850 font-mono">
                                        <tr v-for="l in stravaLaps" :key="l.lap_index" class="hover:bg-slate-900/50 transition">
                                            <td class="p-2.5 font-bold text-slate-300">@{{ l.name || ('Lap ' + l.lap_index) }}</td>
                                            <td class="p-2.5 text-white font-bold">@{{ l.distance_km ? l.distance_km + ' km' : ((l.distance_m / 1000).toFixed(2) + ' km') }}</td>
                                            <td class="p-2.5 font-bold text-neon">@{{ l.pace || '-' }}</td>
                                            <td class="p-2.5 text-slate-300">@{{ l.moving_time_s ? (new Date(l.moving_time_s * 1000).toISOString().substr(14, 5)) : '-' }}</td>
                                            <td class="p-2.5 text-rose-400">
                                                @{{ l.average_heartrate ? Math.round(l.average_heartrate) : '-' }} / @{{ l.max_heartrate ? Math.round(l.max_heartrate) : '-' }} bpm
                                            </td>
                                            <td class="p-2.5 text-amber-400">@{{ l.average_cadence ? Math.round(l.average_cadence) + ' spm' : '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 4: ANALISIS AI & KIRIM WHATSAPP -->
                        <div v-if="stravaModalTab === 'ai_wa'" class="space-y-4">
                            
                            <!-- AI Workout Analysis Card -->
                            <div class="bg-slate-950 p-4 rounded-lg border border-slate-800 space-y-3">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-850 pb-3">
                                    <div>
                                        <div class="text-xs font-semibold text-white">Analisis AI Ruang Lari</div>
                                        <p class="text-[11px] text-slate-400">Klasifikasi otomatis, evaluasi konsistensi pacing, dan beban latihan</p>
                                    </div>
                                    <button type="button" @click="runStravaAiAnalysis(true)" :disabled="stravaAiLoading" class="px-3 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium transition disabled:opacity-50 flex items-center gap-1">
                                        <span>@{{ stravaAiLoading ? 'Menganalisis...' : 'Analisis Ulang AI' }}</span>
                                    </button>
                                </div>

                                <div v-if="stravaAiLoading" class="text-center py-6 text-slate-400 text-xs">
                                    Sedang menganalisis telemetri dan struktur workout dengan AI...
                                </div>
                                <div v-else-if="stravaAiError" class="p-3 bg-rose-950/40 border border-rose-800/40 rounded-md text-xs text-rose-300">
                                    @{{ stravaAiError }}
                                </div>
                                <div v-else-if="stravaAiAnalysis" class="space-y-3">
                                    <!-- Classification Chip -->
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs text-slate-400">Klasifikasi:</span>
                                        <span class="px-2.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-bold uppercase tracking-wide">
                                            @{{ stravaAiAnalysis.workout_classification?.type || 'Workout' }}
                                        </span>
                                        <span v-if="stravaAiAnalysis.pacing_evaluation?.cardiac_drift" class="text-xs text-slate-400 font-mono">
                                            Cardiac Drift: <strong class="text-white">@{{ stravaAiAnalysis.pacing_evaluation.cardiac_drift }}</strong>
                                        </span>
                                    </div>

                                    <!-- Summary Text -->
                                    <div class="p-3 bg-slate-900 rounded-md border border-slate-800 text-xs text-slate-200 leading-relaxed">
                                        @{{ stravaAiAnalysis.summary }}
                                    </div>

                                    <!-- Interval & Repetition Scorecard -->
                                    <div v-if="stravaAiAnalysis.interval_analysis && stravaAiAnalysis.interval_analysis.detected_structure && !stravaAiAnalysis.interval_analysis.detected_structure.toLowerCase().includes('tidak ada')" class="p-3 bg-slate-900 rounded-md border border-orange-500/30 text-xs space-y-2">
                                        <div class="flex flex-wrap items-center justify-between gap-1">
                                            <div class="font-bold text-orange-400 uppercase tracking-wide">
                                                Struktur Repetisi / Interval Terdeteksi
                                            </div>
                                            <span class="px-2 py-0.5 rounded bg-orange-500/20 text-orange-300 font-mono text-[11px] font-semibold border border-orange-500/30">
                                                @{{ stravaAiAnalysis.interval_analysis.detected_structure }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1 text-[11px]">
                                            <div v-if="stravaAiAnalysis.interval_analysis.pacing_consistency" class="bg-slate-950 p-2 rounded border border-slate-800">
                                                <span class="text-slate-400">Konsistensi Pace:</span>
                                                <span class="text-white font-semibold ml-1 capitalize font-mono">@{{ stravaAiAnalysis.interval_analysis.pacing_consistency.replace(/_/g, ' ') }}</span>
                                            </div>
                                            <div v-if="stravaAiAnalysis.interval_analysis.recovery_quality" class="bg-slate-950 p-2 rounded border border-slate-800">
                                                <span class="text-slate-400">Kualitas Recovery:</span>
                                                <span class="text-white font-semibold ml-1 capitalize font-mono">@{{ stravaAiAnalysis.interval_analysis.recovery_quality.replace(/_/g, ' ') }}</span>
                                            </div>
                                        </div>
                                        <div v-if="stravaAiAnalysis.interval_analysis.notes" class="text-slate-300 text-[11px] italic bg-slate-950/60 p-2 rounded border border-slate-800/80">
                                            @{{ stravaAiAnalysis.interval_analysis.notes }}
                                        </div>
                                    </div>

                                    <!-- Went Well & To Improve -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                        <div class="p-3 bg-slate-900 rounded-md border border-slate-800">
                                            <div class="font-semibold text-emerald-400 mb-1.5">Poin Positif</div>
                                            <ul class="space-y-1 text-slate-300 list-disc list-inside">
                                                <li v-for="(p, pIdx) in stravaAiAnalysis.what_went_well" :key="pIdx">@{{ p }}</li>
                                            </ul>
                                        </div>
                                        <div class="p-3 bg-slate-900 rounded-md border border-slate-800">
                                            <div class="font-semibold text-amber-400 mb-1.5">Area Evaluasi & Catatan</div>
                                            <ul class="space-y-1 text-slate-300 list-disc list-inside">
                                                <li v-for="(ti, tIdx) in stravaAiAnalysis.what_to_improve" :key="tIdx">@{{ ti }}</li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Next Workout Suggestion -->
                                    <div v-if="stravaAiAnalysis.next_workout_suggestion" class="p-3 bg-slate-900 rounded-md border border-slate-800 text-xs">
                                        <div class="font-semibold text-white mb-1">Rekomendasi Sesi Berikutnya:</div>
                                        <div class="text-slate-300 leading-relaxed">
                                            <span class="font-bold text-neon uppercase">@{{ stravaAiAnalysis.next_workout_suggestion.type }}</span>: @{{ stravaAiAnalysis.next_workout_suggestion.reason }}
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-5">
                                    <button type="button" @click="runStravaAiAnalysis(false)" :disabled="stravaAiLoading" class="px-4 py-2 rounded-md bg-neon text-dark font-bold text-xs hover:bg-white transition">
                                        Mulai Analisis AI Sekarang
                                    </button>
                                </div>
                            </div>

                            <!-- Coach WhatsApp Manual Dispatch Panel -->
                            <div class="bg-slate-950 p-4 rounded-lg border border-slate-800 space-y-3">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="text-xs font-semibold text-white">Kirim Hasil Analisis ke WhatsApp Atlet</h4>
                                        <p class="text-[11px] text-slate-400">Coach dapat memeriksa, mengedit, lalu mengirim pesan via WhatsApp secara manual</p>
                                    </div>
                                    <span v-if="copySuccess" class="text-xs text-emerald-400 font-semibold">Teks tersalin!</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <div class="sm:col-span-1">
                                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Nomor WhatsApp Atlet</label>
                                        <input type="text" v-model="stravaAthletePhone" placeholder="08123456789 / 62812..." class="w-full bg-slate-900 border border-slate-800 rounded-md px-3 py-2 text-white text-xs font-mono focus:ring-1 focus:ring-slate-500 outline-none">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-[11px] font-medium text-slate-400 mb-1">Draf Pesan WhatsApp (Dapat Diedit)</label>
                                        <textarea v-model="stravaWaMessage" rows="6" class="w-full bg-slate-900 border border-slate-800 rounded-md p-2.5 text-white text-xs font-mono focus:ring-1 focus:ring-slate-500 outline-none leading-relaxed"></textarea>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row justify-end items-center gap-2 pt-2 border-t border-slate-850">
                                    <button type="button" @click="copyWaMessage" class="w-full sm:w-auto px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-semibold transition flex items-center justify-center gap-1.5">
                                        <span>Salin Teks Pesan</span>
                                    </button>
                                    <button type="button" @click="sendToWhatsApp" class="w-full sm:w-auto px-5 py-2 rounded-md bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                                        <span>Buka & Kirim ke WhatsApp</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@push('scripts')
@include('layouts.components.advanced-builder-utils')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
if (typeof Vue !== 'undefined') {
    const { createApp, ref, reactive, onMounted, watch, computed } = Vue;

    try {
createApp({
    setup() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let calendar = null;
        const selectedSession = ref(null);
        const isMobileSheetOpen = ref(false);
        const closeMobileSheet = () => { isMobileSheetOpen.value = false; };
        const loading = ref(false);
        const trainingProfile = reactive(@json($trainingProfile) || {});
        if (!trainingProfile.name) {
            trainingProfile.name = @json($enrollment->runner->name ?? '');
        }
        const profileTab = ref('training');
        const feedbackForm = reactive({
            coach_rating: 0,
            coach_feedback: ''
        });
        const coachUrl = @json(url('/coach'));
        const enrollmentId = @json($enrollment->id);
        const stravaDetailsLoading = ref(false);
        const stravaDetailsError = ref('');
        const stravaMetrics = ref(null);
        const stravaSplits = ref([]);
        const stravaLaps = ref([]);
        const stravaStreams = ref(null);
        const stravaPaceZones = ref(null);
        const stravaHrZones = ref(null);
        const showStravaModal = ref(false);
        const stravaModalTab = ref('overview');
        const stravaAiLoading = ref(false);
        const stravaAiError = ref('');
        const stravaAiAnalysis = ref(null);
        const stravaWaMessage = ref('');
        const stravaAthletePhone = ref('');
        const stravaStreamsData = ref(null);
        const stravaStreamsLoading = ref(false);
        const stravaStreamsError = ref('');
        const copySuccess = ref(false);
        const activeSplitType = ref('splits');
        let chartInstance = null;
        const telemetryFilters = reactive({ pace: true, heartrate: true, altitude: true, cadence: false });

        const stravaWorkoutClassification = computed(() => {
            if (!stravaPaceZones.value && !stravaHrZones.value) return null;
            let type = 'EASY RUN / RECOVERY';
            let colorClass = 'text-green-400 border-green-500/30 bg-green-500/10';

            const speedPace = parseFloat(stravaPaceZones.value?.summary?.speed || 0);
            const tempoPace = parseFloat(stravaPaceZones.value?.summary?.tempo || 0);
            const z4 = parseFloat(stravaHrZones.value?.Z4 || 0);
            const z5 = parseFloat(stravaHrZones.value?.Z5 || 0);
            const z3 = parseFloat(stravaHrZones.value?.Z3 || 0);

            if (speedPace > 15 || (z4 + z5) > 20) {
                type = 'INTERVAL / SPEED';
                colorClass = 'text-orange-400 border-orange-500/30 bg-orange-500/10';
            } else if (tempoPace > 25 || z3 > 30) {
                type = 'TEMPO / THRESHOLD';
                colorClass = 'text-yellow-400 border-yellow-500/30 bg-yellow-500/10';
            }

            return { source: 'Analitik Strava', type, colorClass };
        });

        // Weekly Target State
        const showWeeklyTargetModal = ref(false);
        const weeklyTargetLoading = ref(false);
        const weeklyTargetForm = reactive({
            weekly_km_target: trainingProfile.weekly_km_target || ''
        });

        // Custom Pace State
        const showPaceModal = ref(false);
        const paceLoading = ref(false);
        const paceForm = reactive({
            E: '', M: '', T: '', I: '', R: ''
        });

        const openPaceModal = () => {
            const raw = trainingProfile.custom_paces_raw || {};
            const curPaces = trainingProfile.paces || {};

            paceForm.E = raw.E || (curPaces.E ? formatPace(curPaces.E) : '');
            paceForm.M = raw.M || (curPaces.M ? formatPace(curPaces.M) : '');
            paceForm.T = raw.T || (curPaces.T ? formatPace(curPaces.T) : '');
            paceForm.I = raw.I || (curPaces.I ? formatPace(curPaces.I) : '');
            paceForm.R = raw.R || (curPaces.R ? formatPace(curPaces.R) : '');

            showPaceModal.value = true;
        };

        const updatePaces = async (reset = false) => {
            paceLoading.value = true;
            try {
                const payload = reset ? { reset: true } : { paces: { ...paceForm } };
                const res = await fetch(`{{ route('coach.athletes.update-paces', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    if (data.trainingProfile) {
                        Object.assign(trainingProfile, data.trainingProfile);
                    }
                    showPaceModal.value = false;
                    alert(data.message || 'Pace latihan berhasil diperbarui!');
                } else {
                    alert(data.message || 'Gagal memperbarui pace latihan');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi.');
            } finally {
                paceLoading.value = false;
            }
        };

        // Weekly Report State
        const weeklyReportLoading = ref(false);
        const weeklyReportPublishing = ref(false);
        const weeklyReportsList = ref(@json($enrollment->weeklyReports()->orderBy('week_number', 'desc')->get()) || []);
        const weeklyReportForm = reactive({
            week_number: weeklyReportsList.value.length > 0 ? Math.max(...weeklyReportsList.value.map(r => r.week_number)) + 1 : 1,
            report_text: ''
        });

        const updateWeeklyTarget = async () => {
            weeklyTargetLoading.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.update-weekly-target', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(weeklyTargetForm)
                });
                const data = await res.json();
                if (data.success) {
                    trainingProfile.weekly_km_target = data.weekly_km_target;
                    showWeeklyTargetModal.value = false;
                    alert('Target mingguan berhasil diperbarui!');
                } else {
                    alert(data.message || 'Gagal memperbarui target mingguan');
                }
            } catch (e) {
                alert('Terjadi kesalahan koneksi.');
            } finally {
                weeklyTargetLoading.value = false;
            }
        };

        // Reschedule Program State & Methods
        const showRescheduleModal = ref(false);
        const rescheduleForm = reactive({ new_start_date: '' });
        const rescheduleLoading = ref(false);
        const rescheduleError = ref('');
        const programDurationWeeks = {{ $enrollment->program->duration_weeks ?? 12 }};

        const previewRescheduleEndDate = computed(() => {
            if (!rescheduleForm.new_start_date) return '-';
            const d = new Date(rescheduleForm.new_start_date);
            d.setDate(d.getDate() + programDurationWeeks * 7);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        });

        const openRescheduleModal = () => {
            const existing = '{{ $enrollment->start_date ? \Carbon\Carbon::parse($enrollment->start_date)->format("Y-m-d") : "" }}';
            rescheduleForm.new_start_date = existing || new Date().toISOString().slice(0, 10);
            rescheduleError.value = '';
            showRescheduleModal.value = true;
        };

        const setStartDateToday = () => {
            rescheduleForm.new_start_date = new Date().toISOString().slice(0, 10);
        };

        const setStartDateNextMonday = () => {
            const today = new Date();
            const day = today.getDay();
            const daysUntilNextMonday = day === 0 ? 1 : (8 - day);
            const nextMonday = new Date(today);
            nextMonday.setDate(today.getDate() + daysUntilNextMonday);
            rescheduleForm.new_start_date = nextMonday.toISOString().slice(0, 10);
        };

        const setStartDateNextMonth = () => {
            const today = new Date();
            const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, 1);
            rescheduleForm.new_start_date = nextMonth.toISOString().slice(0, 10);
        };

        const submitReschedule = async () => {
            if (!rescheduleForm.new_start_date) {
                rescheduleError.value = 'Pilih tanggal mulai baru terlebih dahulu.';
                return;
            }
            rescheduleLoading.value = true;
            rescheduleError.value = '';
            try {
                const url = `{{ route('coach.athletes.reschedule', $enrollment->id) }}`;
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ new_start_date: rescheduleForm.new_start_date }),
                });
                const data = await res.json();
                if (data.success) {
                    showRescheduleModal.value = false;
                    alert(data.message || 'Program berhasil dijadwalkan ulang!');
                    window.location.reload();
                } else {
                    rescheduleError.value = data.message || 'Gagal mengubah jadwal.';
                }
            } catch (e) {
                rescheduleError.value = 'Terjadi kesalahan. Silakan coba lagi.';
            } finally {
                rescheduleLoading.value = false;
            }
        };

        // Send Program Reminder State & Methods
        const showReminderModal = ref(false);
        const reminderForm = reactive({ channel: 'both', custom_message: '' });
        const reminderSessionInfo = reactive({ title: '', distance: '', pace: '', description: '' });
        const reminderLoading = ref(false);
        const reminderError = ref('');
        const reminderSuccess = ref('');

        const openReminderModal = () => {
            reminderForm.channel = 'both';
            reminderError.value = '';
            reminderSuccess.value = '';

            const runnerName = @json($enrollment->runner->name ?? 'Atlet');
            const programTitle = @json($enrollment->program->title ?? 'Program Lari');

            let workoutTitle = 'Sesi Latihan';
            let type = 'easy_run';
            let notes = '';
            let distanceVal = '';
            let targetPaceVal = '';

            if (selectedSession.value) {
                const props = selectedSession.value.extendedProps || {};
                workoutTitle = selectedSession.value.title || props.title || props.name || 'Sesi Latihan';
                type = (props.workout_type || props.type || 'easy_run').toLowerCase();
                notes = props.description || props.notes || props.instruction || '';
                distanceVal = props.distance || props.target_distance || props.distance_km || '';
                targetPaceVal = props.target_pace || props.pace || '';
            }

            const isRest = ['rest', 'rest_day', 'rest day', 'libur'].includes(type);
            const paces = trainingProfile.paces || {};
            let paceGuidance = targetPaceVal;

            if (isRest) {
                reminderForm.custom_message = `Halo ${runnerName}, besok jadwal latihan kamu adalah: Rest Day (Istirahat & Pemulihan).`;
            } else {
                if (!paceGuidance && paces.E) {
                    paceGuidance = `~${formatPace(paces.E)} /km`;
                }
                const distText = distanceVal ? `${distanceVal} km` : '-';
                reminderForm.custom_message = `Halo ${runnerName}, pengingat sesi latihan besok pada program ${programTitle}:\n\n- Sesi: ${workoutTitle}\n- Jarak: ${distText}\n- Target Pace: ${paceGuidance || '-'}\n\nSemangat latihannya!`;
            }

            reminderSessionInfo.title = workoutTitle;
            reminderSessionInfo.distance = distanceVal ? `${distanceVal} km` : '-';
            reminderSessionInfo.pace = paceGuidance || '-';
            reminderSessionInfo.description = notes || '-';

            showReminderModal.value = true;
        };

        const submitReminder = async () => {
            reminderLoading.value = true;
            reminderError.value = '';
            reminderSuccess.value = '';
            try {
                const url = `{{ route('coach.athletes.send-reminder', $enrollment->id) }}`;
                const payload = {
                    channel: reminderForm.channel,
                    custom_message: reminderForm.custom_message,
                };
                if (selectedSession.value) {
                    if (selectedSession.value.extendedProps.is_custom) {
                        payload.custom_workout_id = selectedSession.value.extendedProps.id;
                    } else {
                        payload.session_day = selectedSession.value.extendedProps.session_day;
                    }
                }

                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (data.success) {
                    reminderSuccess.value = data.message || 'Pengingat berhasil dikirim!';
                    setTimeout(() => { showReminderModal.value = false; }, 1200);
                } else {
                    reminderError.value = data.message || 'Gagal mengirim pengingat.';
                }
            } catch (e) {
                reminderError.value = 'Terjadi kesalahan. Silakan coba lagi.';
            } finally {
                reminderLoading.value = false;
            }
        };

        // Update VDOT / PB Modal State & Methods
        const showVdotModal = ref(false);
        const runnerData = @json($enrollment->runner ?? null);
        const vdotForm = ref({
            mode: 'pb',
            cooper_distance: '',
            balke_distance: '',
            pb_distance: '5k',
            pb_time: '',
            vdot_score: ''
        });
        const vdotLoading = ref(false);
        const vdotError = ref('');
        const vdotSuccess = ref('');

        const openVdotModal = () => {
            vdotError.value = '';
            vdotSuccess.value = '';
            const curVdot = trainingProfile.vdot || @json($enrollment->current_vdot ?? '');
            
            let initialMode = 'pb';
            if (runnerData && (runnerData.pb_5k || runnerData.pb_10k || runnerData.pb_hm || runnerData.pb_fm)) {
                initialMode = 'pb';
            } else if (curVdot) {
                initialMode = 'direct';
            }

            vdotForm.value = {
                mode: initialMode,
                cooper_distance: runnerData?.pb_balke ? String(runnerData.pb_balke) : '',
                balke_distance: runnerData?.pb_balke ? String(runnerData.pb_balke) : '',
                pb_distance: '5k',
                pb_time: runnerData?.pb_5k || '',
                vdot_score: curVdot ? String(Number(curVdot).toFixed(1)) : ''
            };

            showVdotModal.value = true;
        };

        const previewVdot = computed(() => {
            if (vdotForm.value.mode === 'cooper') {
                const d = parseFloat(vdotForm.value.cooper_distance);
                if (!isNaN(d) && d > 0) {
                    const vVO2max = (d / 12) / 0.99;
                    const v = -4.6 + 0.182258 * vVO2max + 0.000104 * vVO2max * vVO2max;
                    return Math.max(10, Math.min(85, v)).toFixed(1);
                }
            } else if (vdotForm.value.mode === 'balke') {
                const d = parseFloat(vdotForm.value.balke_distance);
                if (!isNaN(d) && d > 0) {
                    const v = ((d / 15) - 133) * 0.172 + 33.3;
                    return Math.max(10, Math.min(85, v)).toFixed(1);
                }
            } else if (vdotForm.value.mode === 'pb') {
                const timeStr = vdotForm.value.pb_time;
                const dist = vdotForm.value.pb_distance;
                if (timeStr && timeStr.includes(':')) {
                    const parts = timeStr.trim().split(':').map(Number);
                    let sec = 0;
                    if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) sec = parts[0] * 60 + parts[1];
                    else if (parts.length === 3 && !isNaN(parts[0]) && !isNaN(parts[1]) && !isNaN(parts[2])) sec = parts[0] * 3600 + parts[1] * 60 + parts[2];
                    
                    let distMeters = 5000;
                    if (dist === '10k') distMeters = 10000;
                    else if (dist === '21k') distMeters = 21097;
                    else if (dist === '42k') distMeters = 42195;

                    if (sec > 0) {
                        const v = (distMeters / (sec / 60)) / 0.99;
                        const vdot = -4.6 + 0.182258 * v + 0.000104 * v * v;
                        if (!isNaN(vdot)) return Math.max(10, Math.min(85, vdot)).toFixed(1);
                    }
                }
            } else if (vdotForm.value.mode === 'direct') {
                const v = parseFloat(vdotForm.value.vdot_score);
                if (!isNaN(v) && v >= 10 && v <= 85) return v.toFixed(1);
            }
            return null;
        });

        const submitUpdateVdot = async () => {
            vdotLoading.value = true;
            vdotError.value = '';
            vdotSuccess.value = '';
            try {
                const response = await fetch('{{ route("coach.athletes.update-vdot", $enrollment->id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(vdotForm.value)
                });
                const res = await response.json();
                if (res.success) {
                    vdotSuccess.value = res.message;
                    if (res.trainingProfile) {
                        Object.assign(trainingProfile, res.trainingProfile);
                    }
                    setTimeout(() => {
                        showVdotModal.value = false;
                        vdotSuccess.value = '';
                        window.location.reload();
                    }, 800);
                } else {
                    vdotError.value = res.message || 'Gagal memperbarui VDOT.';
                }
            } catch (e) {
                vdotError.value = 'Terjadi kesalahan sistem saat menghubungi server.';
            } finally {
                vdotLoading.value = false;
            }
        };

        // Advanced Workout Builder State & Methods
        const builderVisible = ref(false);
        const form = reactive({ 
            workout_id: '', 
            workout_date: '', 
            type: 'easy_run', 
            difficulty: 'moderate', 
            distance: '', 
            duration: '', 
            description: '',
            notes: '',
            workout_structure: [] 
        });

        const builderForm = reactive({
            type: 'easy_run',
            title: '',
            notes: '',
            intensity: 'low',
            warmup: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' },
            cooldown: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '' },
            main: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
            longRun: { fastFinish: { enabled: false, distance: 0, unit: 'km', pace: '' } },
            tempo: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '', effort: 'moderate' },
            interval: { reps: 6, by: 'distance', repDistance: 0.8, repDistanceUnit: 'km', repTime: '', pace: '', recovery: 'Jog 2:00' },
            strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
        });

        const strengthData = {
            strength_training: {
                full_body: [
                    { name: 'Burpees', sets: '3', reps: '12-15', equipment: 'Bodyweight' },
                    { name: 'Kettlebell Swing', sets: '3', reps: '15-20', equipment: 'Kettlebell' },
                    { name: 'Clean and Press', sets: '4', reps: '8-10', equipment: 'Barbell/Dumbbell' }
                ],
                legs_lower_body: [
                    { name: 'Squats', sets: '4', reps: '8-12', equipment: 'Bodyweight' },
                    { name: 'Lunges', sets: '3', reps: '10 each leg', equipment: 'Bodyweight' },
                    { name: 'Calf Raises', sets: '3', reps: '15-20', equipment: 'Bodyweight' }
                ],
                core: [
                    { name: 'Plank', sets: '3', duration: '45-60s', equipment: 'Bodyweight' },
                    { name: 'Russian Twist', sets: '3', reps: '20 (10 each side)', equipment: 'Bodyweight' },
                    { name: 'Leg Raises', sets: '3', reps: '12-15', equipment: 'Bodyweight' }
                ],
                upper_body: [
                    { name: 'Push-Ups', sets: '3', reps: '12-20', equipment: 'Bodyweight' },
                    { name: 'Pull-Ups', sets: '3', reps: '8-12', equipment: 'Bodyweight' }
                ]
            }
        };

        const strengthOptions = computed(() => {
            const cat = builderForm.strength.category;
            const all = strengthData.strength_training;
            return (cat && all[cat]) ? all[cat] : [];
        });

        const addStrengthExercise = () => {
            const ex = builderForm.strength.exercise;
            const cat = builderForm.strength.category;
            const list = strengthData.strength_training[cat] || [];
            const found = list.find(i => i.name === ex);
            const item = {
                name: ex || '',
                sets: builderForm.strength.sets || (found ? found.sets : ''),
                reps: builderForm.strength.reps || (found ? found.reps : ''),
                equipment: builderForm.strength.equipment || (found ? found.equipment : '')
            };
            if (!builderForm.strength.plan) builderForm.strength.plan = [];
            builderForm.strength.plan.push(item);
            builderForm.strength.exercise = '';
        };

        const removeStrengthExercise = (idx) => {
            if (!builderForm.strength.plan) return;
            builderForm.strength.plan.splice(idx, 1);
        };

        const builderSummary = computed(() => {
            if (typeof RLBuilderUtils !== 'undefined') {
                return RLBuilderUtils.buildSummary(builderForm);
            }
            return builderForm.title || `${builderForm.type} workout`;
        });

        const builderTotalDistance = computed(() => {
            if (typeof RLBuilderUtils !== 'undefined') {
                return RLBuilderUtils.computeTotalDistance(builderForm);
            }
            return builderForm.main?.distance || 0;
        });

        const openForm = (dateStr, session = null) => {
            if (session) {
                if (session.extendedProps.is_custom) {
                    form.workout_id = session.extendedProps.id;
                    form.workout_structure = session.extendedProps.workout_structure || [];
                } else {
                    form.workout_id = '';
                    form.workout_structure = session.extendedProps.workout_structure || [];
                }
                form.workout_date = session.startStr.split('T')[0];
                form.type = session.extendedProps.type;
                form.difficulty = session.extendedProps.difficulty || 'moderate';
                form.distance = session.extendedProps.distance;
                form.duration = session.extendedProps.duration || '';
                form.description = session.extendedProps.description;
                form.notes = session.extendedProps.notes || '';
                openBuilder(true);
            } else {
                form.workout_id = '';
                form.workout_date = dateStr;
                form.type = 'easy_run';
                form.difficulty = 'moderate';
                form.distance = '';
                form.duration = '';
                form.description = '';
                form.notes = '';
                form.workout_structure = [];
                openBuilder(false);
            }
        };

        const openBuilder = (isEditing) => {
            Object.assign(builderForm, {
                type: 'easy_run',
                title: '',
                notes: '',
                intensity: 'low',
                warmup: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
                cooldown: { enabled: false, by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
                main: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '' },
                longRun: { fastFinish: { enabled: false, distance: 0, unit: 'km', pace: '' } },
                tempo: { by: 'distance', distance: 0, unit: 'km', duration: '', pace: '', effort: 'moderate' },
                interval: { reps: 6, by: 'distance', repDistance: 0.8, repDistanceUnit: 'km', repTime: '', pace: '', recovery: 'Jog 2:00' },
                strength: { category: '', exercise: '', sets: '', reps: '', equipment: '', plan: [] }
            });

            if (isEditing) {
                let config = null;
                if (form.workout_structure && form.workout_structure.advanced) {
                    config = form.workout_structure.advanced;
                } else if (form.workout_structure && typeof form.workout_structure === 'object' && !Array.isArray(form.workout_structure) && form.workout_structure.type) {
                    config = form.workout_structure;
                }
                
                if (config) {
                    Object.assign(builderForm, config);
                } else {
                    let targetType = typeof RLBuilderUtils !== 'undefined' ? RLBuilderUtils.normalizeType(form.type) : form.type;
                    builderForm.type = targetType;
                    builderForm.notes = form.notes || '';
                    if (form.distance) {
                        builderForm.main.distance = form.distance;
                    }
                }
            }
            builderVisible.value = true;
        };

        const submitBuilder = () => {
            const advancedConfig = JSON.parse(JSON.stringify(builderForm));
            form.workout_structure = { advanced: advancedConfig };
            form.description = builderSummary.value;
            form.notes = builderForm.notes;
            form.distance = builderTotalDistance.value;
            form.type = builderForm.type;
            saveCustomWorkout();
        };

        const saveCustomWorkout = async () => {
            loading.value = true;
            try {
                const payload = {
                    workout_date: form.workout_date,
                    type: form.type,
                    difficulty: form.difficulty,
                    distance: form.distance || null,
                    duration: form.duration || null,
                    description: form.description || null,
                    notes: form.notes || null,
                    workout_structure: form.workout_structure,
                };
                
                let url = `{{ route('coach.athletes.workout.store', $enrollment->id) }}`;
                let method = 'POST';
                if (form.workout_id) {
                    url = `{{ route('coach.athletes.workout.update', ['enrollment' => $enrollment->id, 'customWorkout' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER', form.workout_id);
                    method = 'PUT';
                }

                const res = await fetch(url, {
                    method: method,
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    builderVisible.value = false;
                    alert('Workout berhasil disimpan!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menyimpan workout');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan koneksi.');
            } finally {
                loading.value = false;
            }
        };

        const deleteCustomWorkout = async () => {
            if (!confirm('Hapus sesi workout custom ini?')) return;
            loading.value = true;
            try {
                const url = `{{ route('coach.athletes.workout.destroy', ['enrollment' => $enrollment->id, 'customWorkout' => 'ID_PLACEHOLDER']) }}`.replace('ID_PLACEHOLDER', form.workout_id);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    builderVisible.value = false;
                    alert('Workout berhasil dihapus');
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus');
                }
            } catch(e) {
                alert('Terjadi kesalahan.');
            } finally {
                loading.value = false;
            }
        };

        // Race Event State & Methods
        const showRaceModal = ref(false);
        const raceForm = reactive({
            name: '',
            date: new Date().toISOString().slice(0,10),
            distance: '10k',
            goal_time: '',
            notes: ''
        });
        const ruangLariEvents = ref([]);
        const loadingEvents = ref(false);
        const eventSearchQuery = ref('');
        const showEventDropdown = ref(false);
        
        const filteredEvents = computed(() => {
            if (!eventSearchQuery.value) return ruangLariEvents.value;
            const query = eventSearchQuery.value.toLowerCase();
            return ruangLariEvents.value.filter(e => 
                (e.name || e.title || '').toLowerCase().includes(query) || 
                ((e.location_name || e.location || '').toLowerCase().includes(query))
            );
        });

        const fetchRuangLariEvents = async () => {
            if (ruangLariEvents.value.length > 0) return;
            loadingEvents.value = true;
            try {
                const res = await fetch('{{ route("calendar.events.proxy") }}');
                const data = await res.json();
                ruangLariEvents.value = Array.isArray(data) ? data : [];
            } catch (e) {
                console.error('Failed to fetch events', e);
            } finally {
                loadingEvents.value = false;
            }
        };

        const selectRuangLariEvent = (event) => {
            eventSearchQuery.value = event.name || event.title;
            showEventDropdown.value = false;
            raceForm.name = event.name || event.title;
            if (event.start_at || event.date) {
                const d = event.start_at || event.date;
                raceForm.date = d.includes(' ') ? d.split(' ')[0] : d;
            }
        };

        const hideEventDropdown = () => {
            setTimeout(() => { showEventDropdown.value = false; }, 200);
        };

        watch(showRaceModal, (val) => {
            if (val) fetchRuangLariEvents();
        });

        const openRaceForm = () => {
            raceForm.name = '';
            raceForm.date = new Date().toISOString().slice(0,10);
            raceForm.distance = '10k';
            raceForm.goal_time = '';
            raceForm.notes = '';
            showRaceModal.value = true;
        };

        const saveRace = async () => {
            loading.value = true;
            try {
                let label = raceForm.distance.toUpperCase();
                const res = await fetch(`{{ route('coach.athletes.race.store', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({
                        workout_date: raceForm.date,
                        race_name: raceForm.name,
                        dist_label: label,
                        goal_time: raceForm.goal_time,
                        notes: raceForm.notes
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showRaceModal.value = false;
                    alert('Event lomba berhasil ditambahkan!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Gagal menambahkan event');
                }
            } catch(e) {
                alert('Terjadi kesalahan koneksi.');
            } finally {
                loading.value = false;
            }
        };

        const formatPace = (minPerKm) => {
            if (!minPerKm) return '-';
            const mins = Math.floor(minPerKm);
            const secs = Math.round((minPerKm - mins) * 60);
            return `${mins}:${secs.toString().padStart(2,'0')}`;
        };

        const getPaceInfo = (type, distance, description = '') => {
            if (!type || !trainingProfile) return null;
            let tLower = String(type || '').toLowerCase();
            if (['rest', 'strength', 'yoga', 'cycling'].includes(tLower)) return null;

            const map = { easy_run: 'E', recovery: 'E', run: 'E', long_run: 'M', tempo: 'T', threshold: 'T', interval: 'I', repetition: 'R' };
            const typeKey = map[tLower];
            if (!typeKey) return null;

            let val = trainingProfile.paces?.[typeKey];
            const paceLabels = { E: 'Easy Pace', M: 'Marathon Pace', T: 'Tempo Pace', I: 'Interval Pace', R: 'Repetition Pace' };
            const label = paceLabels[typeKey] || 'Target Pace';
            return val ? `${label}: ${formatPace(val)} /km` : null;
        };

        const statusClass = (s) => {
            if (s === 'completed') return 'bg-green-500/20 text-green-400 border border-green-500/30';
            if (s === 'started') return 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30';
            return 'bg-slate-800 text-slate-400 border border-slate-700';
        };

        const formatDate = (d) => {
            return new Date(d).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        };

        const formatDateShort = (dateStr) => {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        };

        const loadStravaForActivity = async (activityId) => {
            const id = activityId ? String(activityId).trim() : '';
            if (!id || id === '0') return;

            stravaDetailsLoading.value = true;
            stravaDetailsError.value = '';
            try {
                const detailRes = await fetch(`${coachUrl}/athletes/${enrollmentId}/strava/activities/${id}/details`, { headers: { 'Accept': 'application/json' } });
                const detailJson = await detailRes.json();
                if (detailRes.ok && detailJson && detailJson.success) {
                    stravaMetrics.value = detailJson.activity || null;
                    stravaSplits.value = Array.isArray(detailJson.activity?.splits_metric) ? detailJson.activity.splits_metric : [];
                    stravaLaps.value = Array.isArray(detailJson.activity?.laps) ? detailJson.activity.laps : [];
                    if (detailJson.activity?.ai_analysis) {
                        stravaAiAnalysis.value = detailJson.activity.ai_analysis;
                    }
                }
            } catch (e) {
                stravaDetailsError.value = 'Gagal memuat data Strava.';
            } finally {
                stravaDetailsLoading.value = false;
            }
        };

        const openStravaModal = async (activityId = null) => {
            let id = activityId ? String(activityId).trim() : '';
            if (!id && selectedSession.value) {
                const props = selectedSession.value.extendedProps || {};
                if (props.strava_activity_id) id = String(props.strava_activity_id).trim();
                else if (props.tracking && props.tracking.strava_link) {
                    const m = String(props.tracking.strava_link).match(/strava\.com\/activities\/(\d+)/i);
                    if (m) id = m[1];
                }
            }
            if (!id && stravaMetrics.value?.strava_activity_id) {
                id = String(stravaMetrics.value.strava_activity_id).trim();
            }
            if (!id || id === '0') {
                alert('Aktivitas Strava tidak ditemukan untuk sesi ini.');
                return;
            }

            const defaultPhone = trainingProfile.phone || @json($enrollment->runner->phone ?? '') || '';
            stravaAthletePhone.value = defaultPhone;
            showStravaModal.value = true;
            stravaModalTab.value = 'overview';

            // If details not loaded or different ID, load details
            if (!stravaMetrics.value || String(stravaMetrics.value.strava_activity_id) !== String(id)) {
                await loadStravaForActivity(id);
            }

            // Pre-load streams in background
            loadStravaStreams(id);

            // If no AI analysis yet, build a draft WA template based on stats
            if (stravaAiAnalysis.value) {
                generateWaMessageFromAnalysis();
            } else {
                generateWaMessageFallback();
            }
        };

        const loadStravaStreams = async (activityId) => {
            const id = parseInt(activityId || 0, 10);
            if (!id) return;
            stravaStreamsLoading.value = true;
            stravaStreamsError.value = '';
            try {
                const res = await fetch(`${coachUrl}/athletes/${enrollmentId}/strava/activities/${id}/streams`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (res.ok && data.success && data.streams) {
                    stravaStreamsData.value = data.streams;
                    if (stravaModalTab.value === 'telemetry') {
                        setTimeout(() => renderTelemetryChart(), 100);
                    }
                } else {
                    stravaStreamsError.value = data.message || 'Streams tidak tersedia.';
                }
            } catch (e) {
                stravaStreamsError.value = 'Gagal memuat streams telemetri.';
            } finally {
                stravaStreamsLoading.value = false;
            }
        };

        const switchStravaTab = (tab) => {
            stravaModalTab.value = tab;
            if (tab === 'telemetry') {
                setTimeout(() => {
                    if (stravaStreamsData.value) {
                        renderTelemetryChart();
                    } else if (stravaMetrics.value?.strava_activity_id) {
                        loadStravaStreams(stravaMetrics.value.strava_activity_id);
                    }
                }, 150);
            }
        };

        const renderTelemetryChart = () => {
            const ctx = document.getElementById('stravaChartCanvas');
            if (!ctx || typeof Chart === 'undefined' || !stravaStreamsData.value) return;

            if (chartInstance) {
                chartInstance.destroy();
                chartInstance = null;
            }

            const streams = stravaStreamsData.value;
            const distanceKm = streams.distance_km || [];
            const count = distanceKm.length;
            if (count === 0) return;

            // Downsample if more than 300 points for smooth performance
            const step = Math.max(1, Math.floor(count / 250));
            const labels = [];
            const altitudeData = [];
            const paceData = [];
            const hrData = [];
            const cadenceData = [];

            for (let i = 0; i < count; i += step) {
                labels.push(distanceKm[i] + ' km');
                if (streams.altitude) altitudeData.push(streams.altitude[i] ?? null);
                if (streams.pace_min_km) paceData.push(streams.pace_min_km[i] ?? null);
                if (streams.heartrate) hrData.push(streams.heartrate[i] ?? null);
                if (streams.cadence) cadenceData.push(streams.cadence[i] ? streams.cadence[i] * 2 : null);
            }

            const datasets = [];

            // Altitude (Area Background)
            if (altitudeData.length > 0 && telemetryFilters.altitude) {
                datasets.push({
                    label: 'Elevasi (m)',
                    data: altitudeData,
                    borderColor: '#475569',
                    backgroundColor: 'rgba(51, 65, 85, 0.3)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 1.5,
                    pointRadius: 0,
                    yAxisID: 'yElevation',
                    order: 4
                });
            }

            // Pace (min/km)
            if (paceData.length > 0 && telemetryFilters.pace) {
                datasets.push({
                    label: 'Pace (min/km)',
                    data: paceData,
                    borderColor: '#38bdf8',
                    backgroundColor: 'rgba(56, 189, 248, 0.05)',
                    fill: false,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 0,
                    yAxisID: 'yPace',
                    order: 1
                });
            }

            // Heart Rate (bpm)
            if (hrData.length > 0 && telemetryFilters.heartrate) {
                datasets.push({
                    label: 'Heart Rate (bpm)',
                    data: hrData,
                    borderColor: '#ef4444',
                    backgroundColor: 'transparent',
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 0,
                    yAxisID: 'yHr',
                    order: 2
                });
            }

            // Cadence (spm)
            if (cadenceData.length > 0 && telemetryFilters.cadence) {
                datasets.push({
                    label: 'Cadence (spm)',
                    data: cadenceData,
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    tension: 0.3,
                    borderWidth: 1.5,
                    pointRadius: 0,
                    yAxisID: 'yCadence',
                    order: 3
                });
            }

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            borderColor: '#334155',
                            borderWidth: 1,
                            titleColor: '#f8fafc',
                            bodyColor: '#cbd5e1',
                            padding: 10,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    let val = context.parsed.y;
                                    if (label.includes('Pace') && val) {
                                        const m = Math.floor(val);
                                        const s = Math.round((val - m) * 60);
                                        return `Pace: ${m}:${s.toString().padStart(2, '0')} /km`;
                                    }
                                    return `${label}: ${val}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: '#1e293b' },
                            ticks: { color: '#64748b', maxTicksLimit: 10, font: { size: 10 } }
                        },
                        yElevation: {
                            type: 'linear',
                            display: telemetryFilters.altitude,
                            position: 'left',
                            grid: { color: '#1e293b' },
                            ticks: { color: '#64748b', font: { size: 10 } }
                        },
                        yPace: {
                            type: 'linear',
                            display: telemetryFilters.pace,
                            position: 'right',
                            reverse: true,
                            grid: { display: false },
                            ticks: {
                                color: '#38bdf8',
                                font: { size: 10 },
                                callback: function(value) {
                                    const m = Math.floor(value);
                                    const s = Math.round((value - m) * 60);
                                    return `${m}:${s.toString().padStart(2, '0')}`;
                                }
                            }
                        },
                        yHr: {
                            type: 'linear',
                            display: telemetryFilters.heartrate,
                            position: 'right',
                            grid: { display: false },
                            ticks: { color: '#ef4444', font: { size: 10 } }
                        },
                        yCadence: {
                            type: 'linear',
                            display: telemetryFilters.cadence,
                            position: 'right',
                            grid: { display: false },
                            ticks: { color: '#f59e0b', font: { size: 10 } }
                        }
                    }
                }
            });
        };

        const runStravaAiAnalysis = async (force = false) => {
            if (!stravaMetrics.value?.strava_activity_id) return;
            stravaAiLoading.value = true;
            stravaAiError.value = '';
            try {
                const id = stravaMetrics.value.strava_activity_id;
                const res = await fetch(`${coachUrl}/athletes/${enrollmentId}/strava/activities/${id}/ai-analysis?force=${force ? 1 : 0}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (res.ok && data.success) {
                    stravaAiAnalysis.value = data.analysis;
                    if (data.wa_message) {
                        stravaWaMessage.value = data.wa_message;
                    } else {
                        generateWaMessageFromAnalysis();
                    }
                } else {
                    stravaAiError.value = data.message || 'Gagal melakukan analisis AI.';
                }
            } catch (e) {
                stravaAiError.value = 'Terjadi kesalahan sistem saat analisis AI.';
            } finally {
                stravaAiLoading.value = false;
            }
        };

        const generateWaMessageFromAnalysis = () => {
            if (!stravaAiAnalysis.value || !stravaMetrics.value) return;
            const act = stravaMetrics.value;
            const an = stravaAiAnalysis.value;
            const runnerName = trainingProfile.name || 'Runner';
            const coachName = @json(auth()->user()->name ?? 'Coach');
            const actName = act.name || 'Sesi Lari';
            const distKm = act.distance_km || (act.distance_m ? (act.distance_m / 1000).toFixed(2) : '-');
            const durStr = act.moving_time_s ? (new Date(act.moving_time_s * 1000).toISOString().substr(11, 8)) : '-';
            const paceStr = act.pace || '-';
            const hrStr = act.average_heartrate ? Math.round(act.average_heartrate) + ' bpm' : '-';

            let msg = `*EVALUASI SESI LATIHAN — ${coachName}*\n`;
            msg += `Halo ${runnerName},\nBerikut review sesi latihan kamu:\n\n`;
            msg += `Aktivitas: ${actName}\n`;
            msg += `Jarak: ${distKm} km | Waktu: ${durStr} | Avg Pace: ${paceStr} /km\n`;
            if (act.average_heartrate) msg += `Avg Heart Rate: ${hrStr}\n`;
            msg += `\n--- ANALISIS SESI ---\n`;
            if (an.summary) msg += `${an.summary}\n\n`;

            if (an.interval_analysis && an.interval_analysis.detected_structure && !an.interval_analysis.detected_structure.toLowerCase().includes('tidak ada')) {
                msg += `*Analisis Repetisi & Interval:*\n`;
                msg += `- Struktur: ${an.interval_analysis.detected_structure}\n`;
                if (an.interval_analysis.pacing_consistency) {
                    msg += `- Konsistensi Pace: ${an.interval_analysis.pacing_consistency.replace(/_/g, ' ')}\n`;
                }
                if (an.interval_analysis.recovery_quality) {
                    msg += `- Pemulihan / Rest: ${an.interval_analysis.recovery_quality.replace(/_/g, ' ')}\n`;
                }
                if (an.interval_analysis.notes) {
                    msg += `- Catatan: ${an.interval_analysis.notes}\n`;
                }
                msg += `\n`;
            }

            if (Array.isArray(an.what_went_well) && an.what_went_well.length > 0) {
                msg += `*Poin Positif:*\n`;
                an.what_went_well.forEach(p => { msg += `- ${p}\n`; });
                msg += `\n`;
            }

            if (Array.isArray(an.what_to_improve) && an.what_to_improve.length > 0) {
                msg += `*Evaluasi & Catatan:*\n`;
                an.what_to_improve.forEach(p => { msg += `- ${p}\n`; });
                msg += `\n`;
            }

            if (an.next_workout_suggestion?.type) {
                msg += `*Saran Sesi Berikutnya:*\n`;
                msg += `- Tipe: ${String(an.next_workout_suggestion.type).toUpperCase()}\n`;
                if (an.next_workout_suggestion.reason) msg += `- Fokus: ${an.next_workout_suggestion.reason}\n`;
                msg += `\n`;
            }

            if (an.coach_recommendation) {
                msg += `*Saran Pelatih:*\n${an.coach_recommendation}\n\n`;
            }

            msg += `Tetap konsisten dan jaga pemulihan!`;
            stravaWaMessage.value = msg;
        };

        const generateWaMessageFallback = () => {
            if (!stravaMetrics.value) return;
            const act = stravaMetrics.value;
            const runnerName = trainingProfile.name || 'Runner';
            const coachName = @json(auth()->user()->name ?? 'Coach');
            const actName = act.name || 'Sesi Lari';
            const distKm = act.distance_km || (act.distance_m ? (act.distance_m / 1000).toFixed(2) : '-');
            const durStr = act.moving_time_s ? (new Date(act.moving_time_s * 1000).toISOString().substr(11, 8)) : '-';
            const paceStr = act.pace || '-';

            stravaWaMessage.value = `*EVALUASI SESI LATIHAN — ${coachName}*\n`
                + `Halo ${runnerName},\n`
                + `Review sesi latihan kamu:\n\n`
                + `Aktivitas: ${actName}\n`
                + `Jarak: ${distKm} km | Waktu: ${durStr} | Avg Pace: ${paceStr} /km\n\n`
                + `Sesi latihan berjalan dengan baik. Tetap jaga hidrasi dan pemulihan untuk sesi berikutnya!`;
        };

        const copyWaMessage = () => {
            if (!stravaWaMessage.value) return;
            navigator.clipboard.writeText(stravaWaMessage.value);
            copySuccess.value = true;
            setTimeout(() => { copySuccess.value = false; }, 2000);
        };

        const sendToWhatsApp = () => {
            if (!stravaWaMessage.value.trim()) {
                alert('Pesan evaluasi tidak boleh kosong.');
                return;
            }
            let rawPhone = String(stravaAthletePhone.value || '').trim();
            rawPhone = rawPhone.replace(/[^0-9]/g, '');
            if (rawPhone.startsWith('0')) {
                rawPhone = '62' + rawPhone.slice(1);
            } else if (rawPhone.startsWith('8')) {
                rawPhone = '62' + rawPhone;
            }

            if (!rawPhone || rawPhone.length < 9) {
                const input = prompt('Masukkan nomor WhatsApp atlet (contoh: 08123456789):', rawPhone);
                if (!input) return;
                rawPhone = input.replace(/[^0-9]/g, '');
                if (rawPhone.startsWith('0')) rawPhone = '62' + rawPhone.slice(1);
                else if (rawPhone.startsWith('8')) rawPhone = '62' + rawPhone;
                stravaAthletePhone.value = rawPhone;
            }

            const encoded = encodeURIComponent(stravaWaMessage.value);
            window.open(`https://wa.me/${rawPhone}?text=${encoded}`, '_blank');
        };

        const saveFeedback = async () => {
            if (!selectedSession.value) return;
            loading.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.feedback', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept':'application/json', 'Content-Type':'application/json' },
                    body: JSON.stringify({
                        session_day: selectedSession.value.extendedProps.session_day,
                        coach_rating: feedbackForm.coach_rating,
                        coach_feedback: feedbackForm.coach_feedback
                    })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Feedback berhasil disimpan!');
                    if (selectedSession.value.extendedProps.tracking) {
                        selectedSession.value.extendedProps.tracking.coach_rating = feedbackForm.coach_rating;
                        selectedSession.value.extendedProps.tracking.coach_feedback = feedbackForm.coach_feedback;
                    }
                }
            } catch (e) {
                alert('Gagal menyimpan feedback.');
            } finally {
                loading.value = false;
            }
        };

        // Race Predictor State & Logic (Riegel Formula)
        const predictor = reactive({
            distance: 15,
            vdot: trainingProfile.vdot || 40
        });

        const predictedTime = computed(() => {
            const timeStr = trainingProfile.equivalent_race_times?.['10k']?.time || '00:50:00';
            const parts = timeStr.split(':');
            let baselineSeconds = 3000;
            if (parts.length === 3) baselineSeconds = (+parts[0]) * 3600 + (+parts[1]) * 60 + (+parts[2]);
            else if (parts.length === 2) baselineSeconds = (+parts[0]) * 60 + (+parts[1]);
            
            const baselineVdot = trainingProfile.vdot || 40;
            const chosenVdot = predictor.vdot || 40;
            const adjustedBaselineSeconds = baselineSeconds * (baselineVdot / chosenVdot);
            const predictedSeconds = adjustedBaselineSeconds * Math.pow(predictor.distance / 10, 1.06);
            
            const hours = Math.floor(predictedSeconds / 3600);
            const minutes = Math.floor((predictedSeconds % 3600) / 60);
            const seconds = Math.floor(predictedSeconds % 60);
            
            let timeFormatted = (hours > 0 ? String(hours).padStart(2, '0') + ':' : '') + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            const paceMin = (predictedSeconds / 60) / predictor.distance;
            const paceM = Math.floor(paceMin);
            const paceS = Math.floor((paceMin - paceM) * 60);
            const paceFormatted = String(paceM).padStart(2, '0') + ':' + String(paceS).padStart(2, '0');

            return { time: timeFormatted, pace: paceFormatted };
        });

        // Weekly Report Actions
        const nudgeStrava = async () => {
            if (confirm("Kirim notifikasi ke atlet untuk menghubungkan akun Strava?")) {
                try {
                    const res = await fetch(`{{ route('coach.athletes.nudge-strava', $enrollment->id) }}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    alert(data.message || 'Notifikasi terkirim.');
                } catch (e) {
                    alert("Terjadi kesalahan.");
                }
            }
        };

        const syncStrava = async () => {
            loading.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.sync-strava', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                alert(data.message || "Sinkronisasi selesai.");
                if (calendar) calendar.refetchEvents();
            } catch (e) {
                alert("Terjadi kesalahan saat sinkronisasi.");
            } finally {
                loading.value = false;
            }
        };

        const generateWeeklyReport = async () => {
            weeklyReportLoading.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.generate-weekly-report', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    weeklyReportForm.report_text = data.draft;
                } else {
                    alert(data.message || "Gagal menghasilkan draf laporan.");
                }
            } catch (e) {
                alert("Terjadi kesalahan menghubungi server.");
            } finally {
                weeklyReportLoading.value = false;
            }
        };

        const publishWeeklyReport = async () => {
            if (!weeklyReportForm.report_text.trim()) {
                alert("Konten evaluasi tidak boleh kosong.");
                return;
            }
            weeklyReportPublishing.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.store-weekly-report', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(weeklyReportForm)
                });
                const data = await res.json();
                if (data.success) {
                    const idx = weeklyReportsList.value.findIndex(r => r.week_number === data.report.week_number);
                    if (idx !== -1) weeklyReportsList.value[idx] = data.report;
                    else weeklyReportsList.value.unshift(data.report);
                    weeklyReportForm.week_number = Math.max(...weeklyReportsList.value.map(r => r.week_number)) + 1;
                    weeklyReportForm.report_text = '';
                    alert(data.message || 'Laporan mingguan berhasil diterbitkan!');
                }
            } catch (e) {
                alert("Terjadi kesalahan.");
            } finally {
                weeklyReportPublishing.value = false;
            }
        };

        const selectWeeklyReport = (rep) => {
            weeklyReportForm.week_number = rep.week_number;
            weeklyReportForm.report_text = rep.report_text;
        };

        const handleEventDrop = async (info) => {
            if (!confirm(`Reschedule ${info.event.title} ke ${info.event.startStr}?`)) {
                info.revert();
                return;
            }

            const props = info.event.extendedProps;
            const payload = {
                type: props.is_custom ? 'custom_workout' : 'program_session',
                new_date: info.event.startStr,
            };

            if (payload.type === 'custom_workout') payload.workout_id = props.id;
            else payload.session_day = props.session_day;

            try {
                const res = await fetch(`${coachUrl}/athletes/${enrollmentId}/reschedule`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    calendar.refetchEvents();
                } else {
                    alert(data.message || 'Gagal mengubah jadwal');
                    info.revert();
                }
            } catch (e) {
                alert('Kesalahan koneksi');
                info.revert();
            }
        };

        const exportCalendar = async (type) => {
            try {
                const response = await fetch('{{ route("coach.athletes.events", $enrollment->id) }}');
                const list = await response.json();
                if (!list || list.length === 0) {
                    alert('Tidak ada data kalender untuk diekspor.');
                    return;
                }

                const programTitle = @json($enrollment->program->title);
                const runnerName = @json($enrollment->runner->name);
                const sortedPlans = [...list].sort((a, b) => new Date(a.start) - new Date(b.start));

                const opt = { useCORS: true, allowTaint: true, backgroundColor: '#0b1220', scale: 2, logging: false };

                const getRowHtml = (plan, globalIdx) => {
                    const dateObj = new Date(plan.start);
                    const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
                    const dayStr = dateObj.toLocaleDateString('id-ID', { weekday: 'long' });
                    const props = plan.extendedProps || {};
                    const desc = props.description || plan.title;
                    const title = desc ? desc.split('\n')[0].replace(/^✅\s*|^❌\s*/, '') : 'Sesi Latihan';
                    const wType = props.type || 'Workout';
                    const target = props.distance ? `${props.distance} km` : (props.duration || '-');
                    
                    let statusTextStr = 'Pending';
                    let statusColor = '#94a3b8';
                    if (props.status === 'completed' || props.status === 'imported') {
                        statusTextStr = 'Selesai';
                        statusColor = '#10b981';
                    }

                    return `
                        <tr style="border-bottom: 1px solid #1e293b;">
                            <td style="padding: 10px 8px; font-size: 11px; color: #64748b;">${globalIdx + 1}</td>
                            <td style="padding: 10px 8px; font-size: 11px; font-weight: bold; color: #ccff00;">${dayStr}</td>
                            <td style="padding: 10px 8px; font-size: 11px; color: #cbd5e1;">${dateStr}</td>
                            <td style="padding: 10px 8px; font-size: 11px; color: #ffffff;">${title} (${wType})</td>
                            <td style="padding: 10px 8px; font-size: 11px; color: #ccff00; text-align: center;">${target}</td>
                            <td style="padding: 10px 8px; text-align: center; color: ${statusColor}; font-size: 10px; font-weight: bold;">${statusTextStr}</td>
                        </tr>
                    `;
                };

                const getTableHeaderHtml = () => `
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid #334155; color: #94a3b8; font-size: 10px; text-transform: uppercase;">
                                <th style="padding: 8px; width: 35px;">No</th>
                                <th style="padding: 8px; width: 90px;">Hari</th>
                                <th style="padding: 8px; width: 100px;">Tanggal</th>
                                <th style="padding: 8px;">Detail Latihan</th>
                                <th style="padding: 8px; width: 80px; text-align: center;">Target</th>
                                <th style="padding: 8px; width: 80px; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                if (type === 'image') {
                    const container = document.createElement('div');
                    container.style.position = 'absolute';
                    container.style.left = '-9999px';
                    container.style.width = '800px';
                    container.style.padding = '30px';
                    container.style.backgroundColor = '#0b1220';
                    container.style.color = '#e2e8f0';
                    container.style.fontFamily = 'sans-serif';

                    const headerHtml = `
                        <div style="border-bottom: 2px solid #ccff00; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between;">
                            <div>
                                <h1 style="color: #ffffff; font-size: 20px; font-weight: bold; margin: 0;">Ruang Lari</h1>
                                <p style="color: #94a3b8; font-size: 11px; margin: 4px 0 0 0;">Jadwal Program Latihan Atlet</p>
                            </div>
                            <div style="text-align: right;">
                                <h3 style="color: #ccff00; font-size: 13px; font-weight: bold; margin: 0;">${programTitle}</h3>
                                <p style="color: #94a3b8; font-size: 11px; margin: 3px 0 0 0;">Atlet: ${runnerName}</p>
                            </div>
                        </div>
                    `;

                    let tableRows = '';
                    sortedPlans.forEach((plan, idx) => { tableRows += getRowHtml(plan, idx); });
                    container.innerHTML = headerHtml + getTableHeaderHtml() + tableRows + '</tbody></table>';
                    document.body.appendChild(container);

                    html2canvas(container, opt).then(canvas => {
                        document.body.removeChild(container);
                        const link = document.createElement('a');
                        link.download = `kalender-${programTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    }).catch(() => {
                        if (document.body.contains(container)) document.body.removeChild(container);
                        alert('Gagal mengekspor gambar.');
                    });

                } else if (type === 'pdf') {
                    const rowsPerPage = 16;
                    const totalPages = Math.ceil(sortedPlans.length / rowsPerPage);
                    const { jsPDF } = window.jspdf;
                    const pdf = new jsPDF('p', 'mm', 'a4');

                    for (let i = 0; i < totalPages; i++) {
                        const startIdx = i * rowsPerPage;
                        const pagePlans = sortedPlans.slice(startIdx, startIdx + rowsPerPage);

                        const container = document.createElement('div');
                        container.style.position = 'absolute';
                        container.style.left = '-9999px';
                        container.style.width = '800px';
                        container.style.height = '1120px';
                        container.style.padding = '30px';
                        container.style.backgroundColor = '#0b1220';
                        container.style.color = '#e2e8f0';
                        container.style.fontFamily = 'sans-serif';
                        container.style.boxSizing = 'border-box';

                        const headerHtml = `
                            <div style="border-bottom: 2px solid #ccff00; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between;">
                                <div>
                                    <h1 style="color: #ffffff; font-size: 20px; font-weight: bold; margin: 0;">Ruang Lari</h1>
                                    <p style="color: #94a3b8; font-size: 11px; margin: 4px 0 0 0;">Jadwal Program Latihan Atlet</p>
                                </div>
                                <div style="text-align: right;">
                                    <h3 style="color: #ccff00; font-size: 13px; font-weight: bold; margin: 0;">${programTitle}</h3>
                                    <p style="color: #94a3b8; font-size: 11px; margin: 3px 0 0 0;">Halaman ${i + 1} dari ${totalPages}</p>
                                </div>
                            </div>
                        `;

                        let tableRows = '';
                        pagePlans.forEach((plan, localIdx) => { tableRows += getRowHtml(plan, startIdx + localIdx); });
                        container.innerHTML = headerHtml + getTableHeaderHtml() + tableRows + '</tbody></table>';

                        document.body.appendChild(container);
                        const canvas = await html2canvas(container, opt);
                        document.body.removeChild(container);

                        if (i > 0) pdf.addPage();
                        pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 10, 10, 190, 277);
                    }
                    pdf.save(`kalender-${programTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.pdf`);
                }
            } catch (e) {
                alert('Gagal mengekspor kalender.');
            }
        };

        // AI Program Generator State & Methods
        const showAiProgramModal = ref(false);
        const aiProgramTab = ref('config');
        const aiProgramLoading = ref(false);
        const aiProgramApplying = ref(false);
        const aiProgramError = ref('');
        const aiProgramPreview = ref(null);

        const aiProgramForm = reactive({
            target_distance: '10k',
            target_date: '',
            start_date: '',
            goal_time: '00:50:00',
            current_vdot: trainingProfile.vdot || 38,
            weekly_mileage: trainingProfile.weekly_km_target || 35,
            frequency: 4,
            runner_level: 'intermediate',
            long_run_day: 'sunday',
            starting_phase: 'base',
            intensity_tone: 'standard',
            include_strength: true,
            strength_type: 'bodyweight',
            is_tropical: true,
        });

        const updateDefaultGoalTime = () => {
            const vdot = parseFloat(aiProgramForm.current_vdot) || 38;
            const dist = aiProgramForm.target_distance || '10k';
            const defaults = {
                '5k': vdot >= 45 ? '00:21:00' : (vdot <= 32 ? '00:30:00' : '00:25:00'),
                '10k': vdot >= 45 ? '00:44:00' : (vdot <= 32 ? '01:03:00' : '00:52:00'),
                '21k': vdot >= 45 ? '01:38:00' : (vdot <= 32 ? '02:18:00' : '01:55:00'),
                '42k': vdot >= 45 ? '03:25:00' : (vdot <= 32 ? '04:48:00' : '04:05:00'),
            };
            aiProgramForm.goal_time = defaults[dist] || '00:50:00';
        };

        const openAiProgramModal = () => {
            aiProgramError.value = '';
            aiProgramTab.value = 'config';
            aiProgramPreview.value = null;

            const today = new Date();
            const day = today.getDay();
            const daysUntilNextMonday = day === 0 ? 1 : (8 - day);
            const nextMonday = new Date(today);
            nextMonday.setDate(today.getDate() + daysUntilNextMonday);

            const raceDate = new Date(nextMonday);
            raceDate.setDate(raceDate.getDate() + (12 * 7) - 1);

            aiProgramForm.start_date = nextMonday.toISOString().slice(0, 10);
            aiProgramForm.target_date = raceDate.toISOString().slice(0, 10);
            aiProgramForm.current_vdot = trainingProfile.vdot || 38;
            aiProgramForm.weekly_mileage = trainingProfile.weekly_km_target || 35;
            updateDefaultGoalTime();

            showAiProgramModal.value = true;
        };

        const generateAiProgramPreview = async () => {
            aiProgramError.value = '';
            aiProgramLoading.value = true;
            try {
                const res = await fetch(`{{ route('coach.athletes.generate-ai-program', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(aiProgramForm),
                });
                const data = await res.json();
                if (data.success && data.program) {
                    aiProgramPreview.value = data.program;
                    aiProgramTab.value = 'preview';
                } else {
                    aiProgramError.value = data.message || 'Gagal menghasilkan preview program AI.';
                }
            } catch (e) {
                aiProgramError.value = 'Terjadi kesalahan koneksi saat generate program.';
            } finally {
                aiProgramLoading.value = false;
            }
        };

        const applyAiProgram = async () => {
            if (!aiProgramPreview.value) return;
            if (!confirm('Terapkan program latihan AI ini ke kalender atlet? Sesi kalender sebelumnya akan disinkronkan dengan program baru ini.')) {
                return;
            }

            aiProgramApplying.value = true;
            aiProgramError.value = '';
            try {
                const payload = {
                    start_date: aiProgramPreview.value.start_date,
                    target_date: aiProgramPreview.value.target_date,
                    target_distance: aiProgramPreview.value.target_distance,
                    sessions: aiProgramPreview.value.sessions,
                    vdot: aiProgramPreview.value.target_vdot,
                    weekly_mileage: aiProgramForm.weekly_mileage,
                    summary: aiProgramPreview.value.summary,
                    title: aiProgramPreview.value.title,
                };

                const res = await fetch(`{{ route('coach.athletes.apply-ai-program', $enrollment->id) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (data.success) {
                    alert('Program latihan AI berhasil diterapkan ke kalender atlet!');
                    showAiProgramModal.value = false;
                    window.location.reload();
                } else {
                    aiProgramError.value = data.message || 'Gagal menerapkan program.';
                }
            } catch (e) {
                aiProgramError.value = 'Terjadi kesalahan koneksi saat menerapkan program.';
            } finally {
                aiProgramApplying.value = false;
            }
        };

        watch(selectedSession, (ev) => {
            stravaMetrics.value = null;
            stravaSplits.value = [];
            stravaLaps.value = [];
            if (!ev || !ev.extendedProps) return;
            const props = ev.extendedProps || {};
            let id = props.is_strava ? props.strava_activity_id : null;
            if (!id && props.tracking && props.tracking.strava_link) {
                const m = String(props.tracking.strava_link).match(/strava\.com\/activities\/(\d+)/i);
                if (m) id = parseInt(m[1], 10);
            }
            if (id) loadStravaForActivity(id);
        });

        onMounted(() => {
            const el = document.getElementById('calendar');
            const isMobile = window.innerWidth < 768;

            calendar = new FullCalendar.Calendar(el, {
                initialView: isMobile ? 'listWeek' : 'dayGridMonth',
                headerToolbar: isMobile 
                    ? { left: 'prev,next', center: 'title', right: '' }
                    : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
                events: '{{ route("coach.athletes.events", $enrollment->id) }}',
                locale: 'id',
                firstDay: 1,
                editable: true,
                eventDrop: handleEventDrop,
                eventClassNames: (arg) => {
                    const props = arg.event.extendedProps || {};
                    const type = props.type || 'run';
                    return ['workout-' + type];
                },
                dateClick: (info) => {
                    openForm(info.dateStr);
                },
                eventClick: (info) => {
                    selectedSession.value = info.event;
                    isMobileSheetOpen.value = true;
                    const tracking = info.event.extendedProps.tracking;
                    if (tracking) {
                        feedbackForm.coach_rating = tracking.coach_rating || 0;
                        feedbackForm.coach_feedback = tracking.coach_feedback || '';
                    } else {
                        feedbackForm.coach_rating = 0;
                        feedbackForm.coach_feedback = '';
                    }
                    setTimeout(() => {
                        const detailEl = document.getElementById('detail-feedback-column');
                        if (detailEl) detailEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 100);
                },
                height: 'auto'
            });
            calendar.render();
        });

        return { 
            trainingProfile, profileTab, formatPace,
            showWeeklyTargetModal, weeklyTargetForm, weeklyTargetLoading, updateWeeklyTarget,
            showPaceModal, paceLoading, paceForm, openPaceModal, updatePaces,
            showVdotModal, openVdotModal, vdotForm, vdotLoading, vdotError, vdotSuccess, previewVdot, submitUpdateVdot,
            selectedSession, statusClass, formatDate, feedbackForm, saveFeedback, loading, getPaceInfo, 
            exportCalendar,
            stravaDetailsLoading, stravaDetailsError, stravaMetrics, stravaSplits, stravaLaps,
            stravaWorkoutClassification,
            showStravaModal, stravaModalTab, stravaAiLoading, stravaAiError, stravaAiAnalysis,
            stravaWaMessage, stravaAthletePhone, stravaStreamsData, stravaStreamsLoading, stravaStreamsError,
            copySuccess, activeSplitType, telemetryFilters,
            openStravaModal, switchStravaTab, renderTelemetryChart, runStravaAiAnalysis,
            copyWaMessage, sendToWhatsApp,
            showRaceModal, raceForm, openRaceForm, saveRace, ruangLariEvents, loadingEvents, fetchRuangLariEvents, eventSearchQuery, showEventDropdown, filteredEvents, selectRuangLariEvent, hideEventDropdown,
            form, openForm, saveCustomWorkout, deleteCustomWorkout,
            builderVisible, builderForm, openBuilder, submitBuilder, builderSummary, builderTotalDistance, strengthOptions, addStrengthExercise, removeStrengthExercise,
            predictor, predictedTime,
            nudgeStrava, syncStrava, generateWeeklyReport, publishWeeklyReport, selectWeeklyReport, formatDateShort,
            showRescheduleModal, rescheduleForm, rescheduleLoading, rescheduleError,
            openRescheduleModal, submitReschedule, previewRescheduleEndDate,
            setStartDateToday, setStartDateNextMonday, setStartDateNextMonth,
            showReminderModal, reminderForm, reminderSessionInfo, reminderLoading, reminderError, reminderSuccess,
            openReminderModal, submitReminder,
            weeklyReportLoading, weeklyReportPublishing, weeklyReportsList, weeklyReportForm,
            showAiProgramModal, aiProgramTab, aiProgramLoading, aiProgramApplying, aiProgramError, aiProgramPreview,
            aiProgramForm, updateDefaultGoalTime, openAiProgramModal, generateAiProgramPreview, applyAiProgram
        };
    }
}).mount('#coach-monitor-app');
    } catch (e) {
        console.error('Vue mount error:', e);
    }
}
</script>
@endpush
