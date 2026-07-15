@extends('layouts.pacerhub')
@php $withSidebar = true; @endphp

@section('title', 'Runner Dashboard')

@push('styles')
    @include('runner.calendar.styles')
    <style>
        .tab-btn {
            position: relative;
            cursor: pointer;
        }
        .tab-btn.active {
            background-color: #ccff00 !important;
            color: #121212 !important;
            box-shadow: 0 4px 12px rgba(204, 255, 0, 0.25);
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans bg-[#060a17] bg-gradient-to-b from-[#060a17] via-[#0d162d] to-[#060a17]">
    <div class="max-w-7xl mx-auto">
        <div class="mt-6 md:mt-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4 border-b border-slate-800/80 pb-4">
            <div class="min-w-0">
                <div class="text-neon font-mono text-xs tracking-widest uppercase">{{ $greeting }}, Runner</div>
                <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tighter truncate">{{ strtoupper(auth()->user()->name) }}</h1>
                
                <!-- Tab Switching Pills -->
                <div class="mt-6 inline-flex w-full sm:w-auto p-1 bg-zinc-900/80 border border-zinc-800 rounded-xl justify-between sm:justify-start">
                    <button onclick="switchTab('overview')" id="tab-btn-overview" class="tab-btn flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3 sm:px-5 py-2.5 sm:py-2 font-black uppercase tracking-wider text-[10px] sm:text-xs transition-all rounded-lg text-slate-400 hover:text-white active">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="hidden sm:inline">Ringkasan</span>
                    </button>
                    <button onclick="switchTab('calendar')" id="tab-btn-calendar" class="tab-btn flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3 sm:px-5 py-2.5 sm:py-2 font-black uppercase tracking-wider text-[10px] sm:text-xs transition-all rounded-lg text-slate-400 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="hidden sm:inline">Jadwal Lari</span>
                    </button>
                    <button onclick="switchTab('strava')" id="tab-btn-strava" class="tab-btn flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3 sm:px-5 py-2.5 sm:py-2 font-black uppercase tracking-wider text-[10px] sm:text-xs transition-all rounded-lg text-slate-400 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="hidden sm:inline">Strava</span>
                    </button>
                    <button onclick="switchTab('calculator')" id="tab-btn-calculator" class="tab-btn flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3 sm:px-5 py-2.5 sm:py-2 font-black uppercase tracking-wider text-[10px] sm:text-xs transition-all rounded-lg text-slate-400 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="hidden sm:inline">Kalkulator</span>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 w-full md:w-auto md:flex">
                <button onclick="switchTab('calendar')" id="dashboard-calendar-btn" class="px-4 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Calendar
                </button>
                <a href="{{ route('runner.programs') }}" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    Programs
                </a>
            </div>
        </div>

        <!-- Tab Content Overview (Alpine.js component wrapper) -->
        <div id="tab-content-overview" class="tab-content mt-6">
            <div x-data="dashboardComponent()">
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/50 border border-slate-700/60 text-xs text-slate-300">
                        <span class="text-slate-400">Hari ini</span>
                        <span id="runner-dashboard-date" class="font-bold text-white"></span>
                        <span class="text-slate-600">•</span>
                        <span id="runner-dashboard-time" class="font-mono text-slate-300"></span>
                    </div>
                    @if(!empty($nextWorkout))
                        <button onclick="switchTab('calendar')" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-neon/10 border border-neon/20 text-xs text-neon hover:bg-neon/15 transition">
                            <span class="font-bold">Next</span>
                            <span class="text-slate-200">{{ ucwords(str_replace('_', ' ', (string) ($nextWorkout['type'] ?? 'Run'))) }}</span>
                            @if(!empty($nextWorkout['distance'])) <span class="text-slate-400">•</span> <span class="text-slate-200">{{ $nextWorkout['distance'] }} km</span> @endif
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-200">{{ $nextWorkout['date_label'] ?? '' }}</span>
                        </button>
                    @endif

                    <!-- WhatsApp Daily Program Toggle Switch -->
                    <button @click="toggleReceiveWa" :class="isReceiveWa ? 'bg-green-500/10 border-green-500/30 text-green-300 hover:bg-green-500/15' : 'bg-slate-900/50 border-slate-700/60 text-slate-400 hover:bg-slate-800'" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-bold transition">
                        <i class="fab fa-whatsapp text-sm" :class="isReceiveWa ? 'text-green-400' : 'text-slate-500'"></i>
                        <span>Program Harian WA: </span>
                        <span class="font-black uppercase text-[10px]" :class="isReceiveWa ? 'text-green-400' : 'text-slate-400'" x-text="isReceiveWa ? 'Aktif' : 'Nonaktif'"></span>
                    </button>
                </div>

        @if (session('success'))
            <div class="mt-6 p-4 rounded-2xl bg-green-900/30 border border-green-500/30 text-green-200">
                <div class="font-bold text-sm">{{ session('success') }}</div>
            </div>
        @endif
        @if (session('error'))
            <div class="mt-6 p-4 rounded-2xl bg-red-900/30 border border-red-500/30 text-red-200">
                <div class="font-bold text-sm">{{ session('error') }}</div>
            </div>
        @endif

        <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4 cursor-pointer select-none" @click="collapsed = !collapsed">
                        <div class="min-w-0">
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Today</div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl md:text-2xl font-black text-white italic tracking-tight mt-1">Latihan Hari Ini</h2>
                                <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300 mt-1.5" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <div class="shrink-0" @click.stop>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/50 border border-slate-700/60 text-xs text-slate-300">
                                <span class="text-slate-400">Active</span>
                                <span class="font-bold text-white">{{ number_format((int) ($activeEnrollments->count() ?? 0)) }}</span>
                            </div>
                        </div>
                    </div>

                    <div x-show="!collapsed" x-transition class="mt-5">

                    @if(!empty($todayWorkout))
                        @php($todayStatus = $todayWorkout['status'] ?? 'pending')
                        <div class="mt-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="text-lg font-black text-white truncate">
                                        {{ ucwords(str_replace('_', ' ', (string) ($todayWorkout['type'] ?? 'Run'))) }}
                                    </div>
                                    <div class="text-xs px-2 py-1 rounded-full border {{ $todayStatus === 'completed' ? 'border-green-500/30 bg-green-900/20 text-green-200' : ($todayStatus === 'started' ? 'border-yellow-500/30 bg-yellow-900/20 text-yellow-200' : 'border-slate-700/60 bg-slate-900/40 text-slate-300') }}">
                                        {{ strtoupper($todayStatus) }}
                                    </div>
                                    @if(!empty($todayWorkoutCount) && $todayWorkoutCount > 1)
                                        <div class="text-xs px-2 py-1 rounded-full border border-neon/20 bg-neon/10 text-neon">
                                            {{ number_format((int) $todayWorkoutCount) }} sesi
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-1 text-xs text-slate-400 flex items-center gap-2">
                                    <span class="font-bold text-slate-300">{{ (string) ($todayWorkout['program_title'] ?? 'Training') }}</span>
                                    @if(!empty($todayWorkout['week_number']) && !empty($todayWorkout['session_day']))
                                        <span class="text-slate-600">•</span>
                                        <span class="px-2 py-0.5 rounded bg-slate-800 text-[10px] text-slate-400 font-mono">Week {{ $todayWorkout['week_number'] }} • Day {{ $todayWorkout['session_day'] }}</span>
                                    @endif
                                </div>
                                <div class="mt-2.5 flex flex-wrap items-center gap-3 text-xs text-slate-400">
                                    @if(!empty($todayWorkout['distance']))
                                        <span class="inline-flex items-center gap-1">
                                            <span class="text-slate-500">Distance</span>
                                            <span class="font-bold text-slate-200">{{ $todayWorkout['distance'] }} km</span>
                                        </span>
                                    @endif
                                    @if(!empty($todayWorkout['duration']))
                                        <span class="inline-flex items-center gap-1">
                                            <span class="text-slate-500">Duration</span>
                                            <span class="font-bold text-slate-200">{{ $todayWorkout['duration'] }}</span>
                                        </span>
                                    @endif
                                    @if(!empty($todayWorkout['strava_link']))
                                        <a href="{{ $todayWorkout['strava_link'] }}" target="_blank" class="inline-flex items-center gap-1 text-orange-300 hover:underline">
                                            Strava
                                        </a>
                                    @endif
                                </div>

                                @if(!empty($todayWorkout['target_pace']))
                                    <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-neon/10 border border-neon/30 text-neon font-black text-xs font-mono">
                                        ⚡ Target Pace: {{ $todayWorkout['target_pace'] }}
                                    </div>
                                @endif
                                @if(!empty($todayWorkout['description']))
                                    <div class="mt-3 text-xs text-slate-300 bg-slate-950/40 border border-slate-800/80 rounded-xl p-3 leading-relaxed whitespace-pre-line max-w-xl">
                                        {{ $todayWorkout['description'] }}
                                    </div>
                                @endif
                            </div>
                            <div class="grid grid-cols-2 gap-2 w-full md:w-auto">
                                <a href="{{ route('runner.calendar') }}" class="px-4 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all text-center">
                                    {{ $todayStatus === 'completed' ? 'Lihat' : 'Mulai' }}
                                </a>
                                <a href="{{ route('runner.calendar') }}" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm text-center">
                                    Jadwal
                                </a>
                            </div>
                        </div>
                                      @if($activeEnrollments->count() <= 0)
                            @if($programBag->count() > 0)
                                @php($pendingProgram = $programBag->first())
                                <div class="mt-5 bg-gradient-to-r from-green-950/40 to-[#ccff00]/5 border border-green-500/20 rounded-3xl p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 shadow-[0_0_30px_rgba(204,255,0,0.05)] animate-pulse-slow">
                                    <div class="flex items-start gap-4">
                                        <div class="p-4 bg-green-500/10 rounded-2xl text-green-400 shrink-0">
                                            <i class="fas fa-rocket text-3xl"></i>
                                        </div>
                                        <div>
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#ccff00]/10 border border-[#ccff00]/20 text-[#ccff00] font-black text-[10px] uppercase tracking-wider mb-2">
                                                Program Baru Dibeli
                                            </span>
                                            <h3 class="text-xl md:text-2xl font-black text-white italic tracking-tight uppercase">Program {{ $pendingProgram->program->title }} Siap Dimulai!</h3>
                                            <p class="text-xs text-slate-400 mt-1 max-w-lg leading-relaxed font-sans">
                                                Anda telah membeli program ini dari Coach {{ $pendingProgram->program->coach->name }}. Tentukan tanggal mulai latihan Anda sekarang untuk memasang jadwalnya ke kalender lari.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 w-full md:w-auto">
                                        <button onclick="triggerApplyProgram({{ $pendingProgram->id }})" class="w-full md:w-auto px-6 py-3.5 rounded-xl bg-[#ccff00] text-dark font-black hover:bg-white transition-all shadow-lg shadow-[#ccff00]/20 text-center text-sm uppercase italic tracking-wider flex items-center justify-center gap-2">
                                            Aktifkan Sekarang <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <!-- Gorgeous Bento Grid for Onboarding -->
                                <div class="mt-5 bg-slate-900/40 border border-slate-700/60 rounded-3xl p-6 md:p-8">
                                    <div class="text-center max-w-xl mx-auto mb-8">
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-neon/10 border border-neon/30 text-neon font-black text-xs uppercase tracking-wider mb-3">
                                            🚀 Mulai Latihan
                                        </div>
                                        <h3 class="text-2xl font-black text-white italic tracking-tight uppercase">Mulai Program Latihanmu</h3>
                                        <p class="text-xs text-slate-400 mt-2">Pilih program terstruktur dari Coach atau buat secara instan menggunakan generator program berbasis AI.</p>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <!-- Card 1: AI VDOT Generator -->
                                        <button @click="openGenerateModal = true" class="relative group overflow-hidden rounded-2xl bg-slate-800/80 border border-slate-700/80 hover:border-neon/50 p-6 text-left transition-all hover:scale-[1.01] duration-300">
                                            <div class="absolute -inset-px bg-gradient-to-r from-neon/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                            <div class="flex items-start justify-between gap-4 relative z-10">
                                                <div class="p-3 bg-neon/15 rounded-xl text-neon group-hover:scale-110 transition-transform">
                                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                    </svg>
                                                </div>
                                                <span class="px-2.5 py-1 rounded-full bg-neon text-dark text-[9px] font-black uppercase tracking-wider">AI VDOT</span>
                                            </div>
                                            <div class="mt-6 relative z-10">
                                                <h4 class="text-lg font-black text-white uppercase italic tracking-tight group-hover:text-neon transition-colors">AI Program Generator</h4>
                                                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                                                    Hasilkan program latihan periodisasi personal (5K - Full Marathon) secara instan menggunakan algoritma Jack Daniels' VDOT yang teruji secara ilmiah.
                                                </p>
                                            </div>
                                            <div class="mt-6 flex items-center gap-1.5 text-xs text-neon font-bold relative z-10">
                                                <span>Generate Sekarang</span>
                                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </div>
                                        </button>

                                        <!-- Card 2: Coach Programs -->
                                        <a href="{{ route('runner.programs') }}" class="relative group overflow-hidden rounded-2xl bg-slate-800/80 border border-slate-700/80 hover:border-neon/50 p-6 text-left transition-all hover:scale-[1.01] duration-300 font-bold">
                                            <div class="absolute -inset-px bg-gradient-to-r from-neon/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                                            <div class="flex items-start justify-between gap-4 relative z-10">
                                                <div class="p-3 bg-neon/15 rounded-xl text-neon group-hover:scale-110 transition-transform">
                                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                    </svg>
                                                </div>
                                                <span class="px-2.5 py-1 rounded-full bg-slate-700 text-white text-[9px] font-black uppercase tracking-wider">Coach</span>
                                            </div>
                                            <div class="mt-6 relative z-10">
                                                <h4 class="text-lg font-black text-white uppercase italic tracking-tight group-hover:text-neon transition-colors">Program Latihan Coach</h4>
                                                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                                                    Daftar program latihan lari terstruktur yang dibuat langsung oleh pelatih berlisensi. Dapatkan bimbingan dan feedback langsung dari Coach.
                                                </p>
                                            </div>
                                            <div class="mt-6 flex items-center gap-1.5 text-xs text-neon font-bold relative z-10">
                                                <span>Pilih Program Coach</span>
                                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7m0 0l-7 7m7-7H3" />
                                                </svg>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div class="mt-5 bg-slate-900/40 border border-slate-700/60 rounded-2xl p-5">
                                <div class="text-sm font-bold text-white">Rest day / tidak ada jadwal hari ini.</div>
                                <div class="text-xs text-slate-400 mt-1">Cek jadwal 7 hari ke depan atau reschedule dari calendar.</div>
                                <div class="mt-4 flex flex-wrap gap-2">
                                    <a href="{{ route('runner.calendar') }}" class="inline-flex items-center gap-2 text-sm text-neon hover:underline font-bold mr-4">
                                        Buka Training Calendar
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                    </a>
                                    <a href="{{ route('runner.calendar') }}?action=add_custom" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 hover:border-neon hover:text-neon text-xs font-bold transition text-slate-300">
                                        ➕ Tambah Latihan Kustom
                                    </a>
                                </div>
                            </div>
                        @endif
                    @endif
                    </div>
                </div>

                <!-- Event Lari Anda Section -->
                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4 cursor-pointer select-none" @click="collapsed = !collapsed">
                        <div class="min-w-0">
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Events</div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl md:text-2xl font-black text-white italic tracking-tight mt-1">Event Lari Anda</h2>
                                <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300 mt-1.5" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <div class="shrink-0" @click.stop>
                            <a href="{{ route('events.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-neon text-dark font-black text-xs transition hover:bg-neon/90">
                                Jelajahi Event
                            </a>
                        </div>
                    </div>

                    <div x-show="!collapsed" x-transition class="mt-5">

                    @if($eventRegistrations->isEmpty())
                        <div class="mt-5 bg-slate-900/40 border border-slate-700/60 rounded-2xl p-5 text-center">
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Kamu belum mendaftar di event lari manapun saat ini. Yuk, temukan event lari seru dan tantang dirimu!
                            </p>
                            <a href="{{ route('events.index') }}" class="mt-3 inline-block px-4 py-2 rounded-xl bg-neon text-dark font-black text-xs hover:bg-neon/90 transition">
                                Cari Event Lari
                            </a>
                        </div>
                    @else
                        <div class="mt-5 space-y-4">
                            @foreach($eventRegistrations as $reg)
                                @php($evt = $reg->event)
                                @if(!$evt) @continue @endif
                                <div class="bg-slate-900/40 border border-slate-750 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 transition hover:border-slate-650">
                                    <div class="min-w-0 flex items-start gap-3">
                                        <div class="p-2.5 bg-neon/10 border border-neon/20 rounded-xl text-neon shrink-0">
                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('events.show', $evt->slug) }}" class="text-base font-bold text-white hover:text-neon transition truncate block">
                                                {{ $evt->name }}
                                            </a>
                                            <div class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <span class="font-mono">{{ $evt->start_at ? $evt->start_at->format('d M Y') : '—' }}</span>
                                                <span class="text-slate-600">•</span>
                                                <span>{{ $evt->location_name ?? '—' }}</span>
                                            </div>
                                            <div class="text-[11px] text-slate-505 mt-2 flex flex-wrap items-center gap-1.5">
                                                <span class="text-slate-400 font-medium">Kategori:</span>
                                                @foreach($reg->participants as $p)
                                                    <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-300 font-mono text-[10px]">
                                                        {{ $p->name }} ({{ $p->category->name ?? 'N/A' }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col sm:flex-row md:flex-col items-start sm:items-center md:items-end justify-between md:justify-center gap-3 shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-slate-800">
                                        <div class="text-left sm:text-right md:text-right">
                                            <div class="text-xs text-slate-500">Status Pembayaran</div>
                                            <div class="mt-1">
                                                @if($reg->payment_status === 'paid' || $reg->payment_status === 'settlement' || $reg->payment_status === 'capture')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-green-500/10 border border-green-500/30 text-green-400 text-[10px] font-black uppercase tracking-wider">
                                                        ⚡ Lunas
                                                    </span>
                                                @elseif($reg->payment_status === 'pending')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 text-[10px] font-black uppercase tracking-wider">
                                                        ⏳ Pending
                                                    </span>
                                                @elseif($reg->payment_status === 'cod')
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-400 text-[10px] font-black uppercase tracking-wider">
                                                        💵 COD
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-[10px] font-black uppercase tracking-wider">
                                                        ❌ {{ ucfirst($reg->payment_status) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="w-full sm:w-auto text-right">
                                            @if($reg->payment_status === 'pending')
                                                @if(($reg->payment_gateway ?? '') === 'midtrans')
                                                    <a href="{{ route('events.payments.continue', $evt->slug) }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-black font-black text-xs transition">
                                                        Bayar Sekarang
                                                    </a>
                                                @elseif(($reg->payment_gateway ?? '') === 'moota')
                                                    <a href="{{ route('events.payment', ['slug' => $evt->slug, 'transaction' => $reg->id]) }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 rounded-xl bg-yellow-400 hover:bg-yellow-300 text-black font-black text-xs transition">
                                                        Bayar Sekarang
                                                    </a>
                                                @endif
                                            @elseif($reg->payment_status === 'paid' || $reg->payment_status === 'settlement' || $reg->payment_status === 'capture' || $reg->payment_status === 'cod')
                                                <a href="{{ route('events.show', $evt->slug) }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-750 transition">
                                                    Lihat Event
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    </div>
                </div>

                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4 cursor-pointer select-none" @click="collapsed = !collapsed">
                        <div>
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Week</div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-white italic tracking-tight mt-1">7 Hari ke Depan</h3>
                                <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300 mt-1" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('runner.calendar') }}" class="text-sm text-neon hover:underline font-bold" @click.stop>Lihat</a>
                    </div>
                    <div x-show="!collapsed" x-transition class="mt-4 -mx-2 px-2 overflow-x-auto">
                        <div class="flex gap-2 min-w-max">
                            @foreach($weekStrip ?? [] as $d)
                                @php($st = $d['status'] ?? 'rest')
                                <a href="{{ route('runner.calendar') }}" class="flex flex-col items-center justify-center w-16 h-20 rounded-2xl border {{ ($d['is_today'] ?? false) ? 'border-neon/40 bg-neon/10' : 'border-slate-700/60 bg-slate-900/40' }} hover:border-neon/40 transition">
                                    <div class="text-[11px] font-mono {{ ($d['is_today'] ?? false) ? 'text-neon' : 'text-slate-400' }}">{{ $d['day_short'] ?? '' }}</div>
                                    <div class="text-xl font-black text-white">{{ $d['day_num'] ?? '' }}</div>
                                    <div class="mt-1 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full {{ $st === 'completed' ? 'bg-green-400' : ($st === 'started' ? 'bg-yellow-300' : ($st === 'pending' ? 'bg-neon' : 'bg-slate-600')) }}"></span>
                                        @if(!empty($d['items_count']))
                                            <span class="text-[10px] text-slate-400">{{ (int) $d['items_count'] }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4 cursor-pointer select-none" @click="collapsed = !collapsed">
                        <div>
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Next Up</div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-white italic tracking-tight mt-1">Latihan Berikutnya</h3>
                                <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300 mt-1" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <a href="{{ route('runner.calendar') }}" class="text-sm text-neon hover:underline font-bold" @click.stop>Calendar</a>
                    </div>

                    <div x-show="!collapsed" x-transition>

                    @php($rows = array_slice($upcomingWorkouts ?? [], 0, 3))
                    <div class="mt-4 space-y-2">
                        @forelse($rows as $w)
                            <div class="flex items-start justify-between gap-3 bg-slate-900/40 border border-slate-700/60 rounded-xl px-4 py-3">
                                <div class="min-w-0">
                                    <div class="text-xs text-slate-400">{{ $w['date_label'] ?? '' }}</div>
                                    <div class="text-sm font-bold text-white truncate">
                                        {{ ucwords(str_replace('_', ' ', (string) ($w['type'] ?? 'Run'))) }}
                                        <span class="text-slate-400 font-normal">• {{ $w['program_title'] ?? 'Training' }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        @if(!empty($w['distance'])) {{ $w['distance'] }} km @endif
                                        @if(!empty($w['duration'])) <span class="text-slate-600">•</span> {{ $w['duration'] }} @endif
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    @php($s = $w['status'] ?? 'pending')
                                    <div class="text-xs font-bold {{ $s === 'completed' ? 'text-green-300' : ($s === 'started' ? 'text-yellow-300' : 'text-slate-400') }}">
                                        {{ strtoupper($s) }}
                                    </div>
                                    @if(!empty($w['strava_link']))
                                        <a href="{{ $w['strava_link'] }}" target="_blank" class="text-[11px] text-orange-300 hover:underline">Strava</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-400">Belum ada plan 7 hari ke depan.</div>
                        @endforelse
                    </div>
                    </div>
                </div>

                @if($programBag->count() > 0)
                <!-- Program Bag Sidebar Widget -->
                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4 mb-4 cursor-pointer select-none" @click="collapsed = !collapsed">
                        <div>
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Bag</div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-white italic tracking-tight mt-1">Program Bag</h3>
                                <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300 mt-1" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <button onclick="switchTab('calendar')" class="text-sm text-neon hover:underline font-bold" @click.stop>Lihat Semua</button>
                    </div>

                    <div x-show="!collapsed" x-transition>
                    <div class="space-y-3">
                        @foreach($programBag as $bg)
                            <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800 flex flex-col gap-3">
                                <div>
                                    <div class="text-white font-bold text-sm">{{ $bg->program->title }}</div>
                                    <div class="text-[10px] text-slate-400 mt-1 font-sans">Dibuat oleh: <span class="font-semibold text-slate-300">{{ $bg->program->coach->name }}</span></div>
                                    <div class="text-[10px] text-slate-505 font-mono mt-0.5">Dibeli: {{ $bg->created_at->format('d M Y') }}</div>
                                </div>
                                <button onclick="triggerApplyProgram({{ $bg->id }})" class="w-full py-2.5 rounded-xl bg-neon text-dark font-black hover:bg-white text-center text-xs uppercase italic tracking-wider transition-all flex items-center justify-center gap-1.5 shadow-sm shadow-neon/10">
                                    Aktifkan <i class="fas fa-play text-[9px]"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    </div>
                </div>
                @endif

                <!-- Running Form Analysis Section -->
                @if(isset($publishedTrials) && $publishedTrials->count() > 0)
                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4 mb-5 cursor-pointer select-none" @click="collapsed = !collapsed">
                        <div class="min-w-0">
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Analysis</div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl md:text-2xl font-black text-white italic tracking-tight mt-1">Running Form Analysis</h2>
                                <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300 mt-1.5" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <div class="shrink-0" @click.stop>
                            <span class="px-3 py-1.5 rounded-lg bg-[#ccff00]/15 border border-[#ccff00]/30 text-[10px] text-[#ccff00] font-black uppercase tracking-wider">
                                {{ $publishedTrials->count() }} Analisis
                            </span>
                        </div>
                    </div>

                    <div x-show="!collapsed" x-transition>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($publishedTrials as $trial)
                                @php($score = $trial->quality_score ? round((float) $trial->quality_score * 100) : null)
                                @php($report = $trial->latestReport)
                                @php($narrative = $report ? $report->runner_narrative_json : null)
                                @php($coachMessage = is_array($narrative) ? ($narrative['coach_message'] ?? null) : null)
                                <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-850 hover:border-[#ccff00]/40 transition-all flex flex-col justify-between gap-3">
                                    <div>
                                        <div class="flex items-start justify-between gap-2 mb-2">
                                            <div class="font-bold text-sm text-white">Trial Attempt #{{ $trial->attempt_no }}</div>
                                            @if($score)
                                                <span class="px-2 py-0.5 rounded bg-[#ccff00]/10 border border-[#ccff00]/30 text-[#ccff00] text-[10px] font-black tracking-tight">
                                                    {{ $score }} Pts
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono">
                                            Session: {{ $trial->session->name ?? 'Default Session' }}
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-1 font-sans">
                                            Tanggal: <span class="font-semibold text-slate-300">{{ $trial->published_at ? $trial->published_at->format('d M Y') : $trial->created_at->format('d M Y') }}</span>
                                        </div>
                                        @if($coachMessage)
                                            <p class="text-slate-400 text-xs mt-2 italic line-clamp-2">
                                                "{{ is_array($coachMessage) ? implode(' ', array_values($coachMessage)) : $coachMessage }}"
                                            </p>
                                        @endif
                                    </div>
                                    <div class="mt-2">
                                        <a href="{{ route('runner.running-analysis.trials.review', $trial) }}" 
                                           class="w-full py-2 rounded-lg bg-[#ccff00] text-black hover:bg-[#b3e600] font-black text-center text-xs uppercase italic tracking-wider transition-all flex items-center justify-center gap-1 shadow-sm">
                                            Lihat Analisis Detail <i class="fas fa-arrow-right text-[10px]"></i>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Riwayat Run Connect Section -->
                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4 mb-5 cursor-pointer select-none" @click="collapsed = !collapsed">
                        <div class="min-w-0">
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Connect</div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl md:text-2xl font-black text-white italic tracking-tight mt-1">Riwayat Run Connect</h2>
                                <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300 mt-1.5" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <div class="shrink-0" @click.stop>
                            <a href="{{ route('run-connect.index') }}" class="px-3 py-1.5 rounded-lg bg-neon/15 border border-neon/30 text-[10px] text-neon hover:bg-neon/20 transition-all font-black uppercase tracking-wider">
                                Cari Buddy Lari
                            </a>
                        </div>
                    </div>

                    <div x-show="!collapsed" x-transition>

                    <div class="space-y-3.5 max-h-[400px] overflow-y-auto pr-1 scroll-thin">
                        @forelse($runConnectHistory as $thread)
                            @php($isCreator = $thread->creator_id === auth()->id())
                            @php($participant = $thread->participants->where('user_id', auth()->id())->first())
                            @php($status = $isCreator ? 'host' : ($participant ? $participant->status : ''))
                            
                            <div class="p-4 rounded-xl bg-slate-950/80 border border-slate-850 hover:border-slate-750 transition-all flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="font-bold text-sm text-white truncate max-w-[200px] sm:max-w-xs">{{ $thread->title }}</h4>
                                        <span class="text-[8px] px-1.5 py-0.5 rounded font-black uppercase tracking-wider {{ $thread->status === 'cancelled' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : ($thread->status === 'completed' ? 'bg-slate-800 text-slate-400 border border-slate-700' : 'bg-green-500/10 text-green-400 border border-green-500/20') }}">
                                            {{ $thread->status }}
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                                        <span class="font-bold text-slate-300">{{ $thread->type }}</span>
                                        <span class="text-slate-700">•</span>
                                        <span class="text-slate-300 font-semibold">{{ $thread->run_distance_km }} km</span>
                                        <span class="text-slate-700">•</span>
                                        <span class="text-slate-400 truncate max-w-[150px] sm:max-w-xs">{{ $thread->start_location_name }}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-1.5 flex items-center gap-1.5 flex-wrap">
                                        <span>📅 {{ $thread->start_date->format('d M Y') }} • {{ \Carbon\Carbon::parse($thread->start_time)->format('H:i') }}</span>
                                        <span class="text-slate-800">•</span>
                                        <span>Buddy: {{ $thread->participants->where('status', 'joined')->count() }}/{{ $thread->quota }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 flex items-center justify-between sm:justify-end gap-3.5 border-t border-slate-900/50 sm:border-0 pt-2.5 sm:pt-0">
                                    <div>
                                        @if($status === 'host')
                                            <span class="px-2 py-0.5 rounded bg-neon/10 border border-neon/30 text-neon text-[9px] font-black uppercase tracking-wider">Host</span>
                                        @elseif($status === 'joined')
                                            <span class="px-2 py-0.5 rounded bg-green-500/10 border border-green-500/30 text-green-400 text-[9px] font-black uppercase tracking-wider">Terdaftar</span>
                                        @elseif($status === 'pending')
                                            <span class="px-2 py-0.5 rounded bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 text-[9px] font-black uppercase tracking-wider">Menunggu</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-400 text-[9px] font-black uppercase tracking-wider">{{ $status }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('run-connect.index') }}" class="p-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-750 hover:bg-slate-850 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 text-center py-8">
                                <p>Belum ada riwayat lari bersama di Run Connect.</p>
                                <a href="{{ route('run-connect.index') }}" class="inline-block mt-3 text-neon hover:underline font-bold text-[10px] uppercase tracking-wider">Mulai Cari Buddy Lari →</a>
                            </div>
                        @endforelse
                    </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Weekly Report Cards from Coach -->
                @if(isset($weeklyReports) && count($weeklyReports) > 0)
                    <div x-data="{ collapsed: false }" class="bg-slate-800/40 backdrop-blur-md border border-neon/30 rounded-[2rem] p-6 shadow-xl shadow-neon/5 relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-neon/10 rounded-full blur-3xl"></div>
                        <div class="flex items-center justify-between mb-4 cursor-pointer select-none" @click="collapsed = !collapsed">
                            <div>
                                <div class="text-[10px] font-mono text-neon uppercase tracking-widest leading-none">Weekly Insight</div>
                                <div class="flex items-center gap-2 mt-1 leading-none">
                                    <h3 class="text-xl font-black text-white italic tracking-tight uppercase">Rapor Mingguan</h3>
                                    <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl bg-neon/10 text-neon font-black text-[10px] uppercase border border-neon/20" @click.stop>
                                Active Coaching
                            </span>
                        </div>

                        <div x-show="!collapsed" x-transition>

                        <div class="space-y-4">
                            @foreach($weeklyReports as $index => $report)
                                <div class="p-4 bg-slate-950/40 rounded-2xl border border-slate-850 {{ $index > 0 ? 'hidden' : '' }}" id="report-card-{{ $report->id }}">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xs font-black text-white">Minggu ke-{{ $report->week_number }}</span>
                                        <span class="text-[9px] text-slate-500 font-mono">{{ $report->created_at->format('d M Y') }}</span>
                                    </div>
                                    <div class="text-xs text-slate-300 leading-relaxed font-sans whitespace-pre-line">
                                        {{ $report->report_text }}
                                    </div>
                                </div>
                            @endforeach

                            @if(count($weeklyReports) > 1)
                                <div class="flex justify-between items-center mt-2 text-[10px] text-slate-400 font-mono">
                                    <button onclick="toggleOtherReports()" id="toggle-reports-btn" class="hover:text-neon transition">
                                        Lihat Riwayat Laporan ({{ count($weeklyReports) - 1 }})
                                    </button>
                                </div>
                            @endif
                        </div>
                        </div>
                    </div>

                    <script>
                        function toggleOtherReports() {
                            const reports = document.querySelectorAll('[id^="report-card-"]');
                            const btn = document.getElementById('toggle-reports-btn');
                            let isShowingAll = false;
                            
                            reports.forEach((el, idx) => {
                                if (idx > 0) {
                                    if (el.classList.contains('hidden')) {
                                        el.classList.remove('hidden');
                                        isShowingAll = true;
                                    } else {
                                        el.classList.add('hidden');
                                    }
                                }
                            });
                            
                            if (isShowingAll) {
                                btn.textContent = "Sembunyikan Riwayat Laporan";
                            } else {
                                btn.textContent = "Lihat Riwayat Laporan (" + (reports.length - 1) + ")";
                            }
                        }
                    </script>
                @endif

                <!-- Card Update Personal Best -->
                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 relative overflow-hidden group">
                    <div class="absolute -right-10 -top-10 w-24 h-24 bg-neon/5 rounded-full blur-2xl group-hover:bg-neon/10 transition-all duration-700"></div>
                    
                    <div class="flex items-center gap-2 mb-3 cursor-pointer select-none" @click="collapsed = !collapsed">
                        <span class="text-lg">⚡</span>
                        <div class="text-xs font-mono text-slate-500 uppercase tracking-widest flex items-center gap-2">
                            <span>Personal Best & Parameter Test</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-300" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="!collapsed" x-transition>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Jarak / Parameter Test</label>
                            <select x-model="card_pb_distance" class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white text-xs focus:outline-none focus:border-neon transition-colors cursor-pointer">
                                <option value="5k">5 Kilometer (5K)</option>
                                <option value="10k">10 Kilometer (10K)</option>
                                <option value="21k">Half Marathon (21K)</option>
                                <option value="42k">Full Marathon (42K)</option>
                                <option value="balke">Balke Test (15 Menit Run)</option>
                            </select>
                        </div>

                        <!-- Time inputs for standard distances -->
                        <div x-show="card_pb_distance !== 'balke'" class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Catatan Waktu Terkini (PB)</label>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="flex flex-col items-center">
                                    <input x-model="card_pb_hours" type="number" min="0" max="99" class="w-full px-2.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white text-center font-bold text-xs focus:outline-none focus:border-neon" placeholder="HH">
                                    <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest mt-1">Jam</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <input x-model="card_pb_minutes" type="number" min="0" max="59" class="w-full px-2.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white text-center font-bold text-xs focus:outline-none focus:border-neon" placeholder="MM">
                                    <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest mt-1">Menit</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <input x-model="card_pb_seconds" type="number" min="0" max="59" class="w-full px-2.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white text-center font-bold text-xs focus:outline-none focus:border-neon" placeholder="SS">
                                    <span class="text-[8px] text-slate-500 font-bold uppercase tracking-widest mt-1">Detik</span>
                                </div>
                            </div>
                        </div>

                        <!-- Distance input for Balke test -->
                        <div x-show="card_pb_distance === 'balke'" class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Jarak yang Ditempuh (15 Menit)</label>
                            <div class="relative">
                                <input x-model="card_pb_balke" type="number" min="0" max="10000" class="w-full pl-3 pr-12 py-2 bg-slate-900 border border-slate-800 rounded-xl text-white font-bold text-xs focus:outline-none focus:border-neon" placeholder="Contoh: 3200">
                                <span class="absolute right-3 top-2.5 text-[10px] font-bold text-slate-500 uppercase">Meter</span>
                            </div>
                            <span class="text-[9px] text-slate-500 leading-tight block mt-1">
                                Jarak ini akan dihitung ke VDOT score menggunakan Balke 15-minute formula.
                            </span>
                        </div>

                        <button @click="submitCardPb()" :disabled="card_pb_loading" class="w-full py-2.5 rounded-xl bg-neon hover:bg-neon/90 text-dark font-black text-xs transition duration-300 flex items-center justify-center gap-2">
                            <span x-show="!card_pb_loading">Update & Hitung Ulang VDOT</span>
                            <span x-show="card_pb_loading" class="flex items-center gap-1">
                                <svg class="animate-spin h-3 w-3 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>

                    <!-- Current PBs at a glance -->
                    <div class="mt-4 pt-3 border-t border-slate-800/80 grid grid-cols-2 gap-2 text-[10px]">
                        @if(auth()->user()->pb_5k)
                            <div class="flex justify-between px-2 py-1 bg-slate-900/40 rounded border border-slate-850">
                                <span class="text-slate-500">5K:</span>
                                <span class="font-bold text-white font-mono">{{ auth()->user()->pb_5k }}</span>
                            </div>
                        @endif
                        @if(auth()->user()->pb_10k)
                            <div class="flex justify-between px-2 py-1 bg-slate-900/40 rounded border border-slate-850">
                                <span class="text-slate-500">10K:</span>
                                <span class="font-bold text-white font-mono">{{ auth()->user()->pb_10k }}</span>
                            </div>
                        @endif
                        @if(auth()->user()->pb_hm)
                            <div class="flex justify-between px-2 py-1 bg-slate-900/40 rounded border border-slate-850">
                                <span class="text-slate-500">HM:</span>
                                <span class="font-bold text-white font-mono">{{ auth()->user()->pb_hm }}</span>
                            </div>
                        @endif
                        @if(auth()->user()->pb_fm)
                            <div class="flex justify-between px-2 py-1 bg-slate-900/40 rounded border border-slate-850">
                                <span class="text-slate-500">FM:</span>
                                <span class="font-bold text-white font-mono">{{ auth()->user()->pb_fm }}</span>
                            </div>
                        @endif
                        @if(auth()->user()->pb_balke)
                            <div class="flex justify-between px-2 py-1 bg-slate-900/40 rounded border border-slate-850 col-span-2">
                                <span class="text-slate-500">Balke Test (15m):</span>
                                <span class="font-bold text-white font-mono">{{ auth()->user()->pb_balke }}m</span>
                            </div>
                        @endif
                    </div>
                    </div>
                </div>

                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between cursor-pointer select-none mb-3" @click="collapsed = !collapsed">
                        <div class="text-xs font-mono text-slate-500 uppercase tracking-widest flex items-center gap-2">
                            <span>Progress</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-300" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="!collapsed" x-transition>
                    <div class="mt-2 flex items-end justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-2xl font-black text-white">
                                {{ number_format((float) ($weeklyCompletedKm ?? 0), 1) }} <span class="text-sm font-bold text-slate-400">/ {{ number_format((float) ($weeklyPlannedKm ?? 0), 1) }} km</span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1">
                                {{ number_format((int) ($weeklySessionsCompleted ?? 0)) }} / {{ number_format((int) ($weeklySessionsPlanned ?? 0)) }} sesi selesai
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <div class="text-xs text-slate-400">Wallet</div>
                            <div class="text-sm font-black text-white">Rp {{ number_format((float) ($walletBalance ?? 0), 0, ',', '.') }}</div>
                        </div>
                    </div>
                    <div class="w-full bg-slate-800 h-2 rounded-full mt-4 overflow-hidden">
                        @php($planned = (float) ($weeklyPlannedKm ?? 0))
                        @php($done = (float) ($weeklyCompletedKm ?? 0))
                        @php($pct = $planned > 0 ? min(100, max(0, ($done / $planned) * 100)) : 0)
                        <div class="bg-neon h-full rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    </div>
                </div>

                <!-- VDOT & Paces Card -->
                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between cursor-pointer select-none" @click="collapsed = !collapsed">
                        <div>
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Training Science</div>
                            <div class="flex items-center gap-2 mt-1">
                                <h3 class="text-lg font-black text-white italic tracking-tight uppercase">VDOT & Target Pace</h3>
                                <svg class="w-4 h-4 text-slate-400 transform transition-transform duration-300" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @if(auth()->user()->vdot)
                            <div class="px-2.5 py-1 rounded-full bg-neon text-dark font-black text-xs flex items-center gap-1 font-mono" @click.stop>
                                ⚡ VDOT {{ round(auth()->user()->vdot, 1) }}
                            </div>
                        @endif
                    </div>

                    <div x-show="!collapsed" x-transition>

                    @if(auth()->user()->vdot && auth()->user()->training_paces)
                        @php($p = auth()->user()->training_paces)
                        <div class="mt-4 space-y-2.5">
                            <div class="flex items-center justify-between border-b border-slate-850 pb-2">
                                <span class="text-xs text-slate-400">Easy (E) Pace</span>
                                <span class="text-xs font-bold text-white font-mono bg-slate-900/60 px-2 py-1 rounded">
                                    {{ sprintf('%d:%02d', floor($p['E']), round(($p['E'] - floor($p['E'])) * 60)) }} /km
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-850 pb-2">
                                <span class="text-xs text-slate-400">Marathon (M) Pace</span>
                                <span class="text-xs font-bold text-white font-mono bg-slate-900/60 px-2 py-1 rounded">
                                    {{ sprintf('%d:%02d', floor($p['M']), round(($p['M'] - floor($p['M'])) * 60)) }} /km
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-850 pb-2">
                                <span class="text-xs text-slate-400">Threshold (T) Pace</span>
                                <span class="text-xs font-bold text-white font-mono bg-slate-900/60 px-2 py-1 rounded">
                                    {{ sprintf('%d:%02d', floor($p['T']), round(($p['T'] - floor($p['T'])) * 60)) }} /km
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-b border-slate-850 pb-2">
                                <span class="text-xs text-slate-400">Interval (I) Pace</span>
                                <span class="text-xs font-bold text-white font-mono bg-slate-900/60 px-2 py-1 rounded">
                                    {{ sprintf('%d:%02d', floor($p['I']), round(($p['I'] - floor($p['I'])) * 60)) }} /km
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400">Repetition (R) Pace</span>
                                <span class="text-xs font-bold text-white font-mono bg-slate-900/60 px-2 py-1 rounded">
                                    {{ sprintf('%d:%02d', floor($p['R']), round(($p['R'] - floor($p['R'])) * 60)) }} /km
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 bg-slate-950/40 border border-slate-800/80 rounded-xl p-4 text-center">
                            <p class="text-xs text-slate-400 leading-relaxed">
                                Personal Best (PB) belum diatur. Atur PB Anda untuk memunculkan target pace latihan terpersonalisasi.
                            </p>
                            <a href="{{ route('runner.calendar') }}" class="mt-3 inline-block w-full py-2 rounded-xl bg-neon/10 border border-neon/20 hover:bg-neon/15 text-neon font-bold text-xs transition text-center">
                                Atur Personal Best
                            </a>
                        </div>
                    @endif
                    </div>
                </div>

                <div x-data="{ collapsed: false }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between cursor-pointer select-none mb-3" @click="collapsed = !collapsed">
                        <div class="text-xs font-mono text-slate-500 uppercase tracking-widest flex items-center gap-2">
                            <span>Quick Links</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-300" :class="collapsed ? '-rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div x-show="!collapsed" x-transition>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <a href="{{ route('runner.calendar') }}" class="p-3 rounded-xl bg-slate-900/40 border border-slate-700/60 hover:border-neon/40 transition">
                            <div class="text-xs text-slate-400">Training</div>
                            <div class="text-sm font-black text-white">Calendar</div>
                        </a>
                        <a href="{{ route('runner.programs') }}" class="p-3 rounded-xl bg-slate-900/40 border border-slate-700/60 hover:border-neon/40 transition">
                            <div class="text-xs text-slate-400">Explore</div>
                            <div class="text-sm font-black text-white">Programs</div>
                        </a>
                        <a href="{{ route('wallet.index') }}" class="p-3 rounded-xl bg-slate-900/40 border border-slate-700/60 hover:border-neon/40 transition">
                            <div class="text-xs text-slate-400">Finance</div>
                            <div class="text-sm font-black text-white">Wallet</div>
                        </a>
                        <a href="{{ route('profile.show') }}" class="p-3 rounded-xl bg-slate-900/40 border border-slate-700/60 hover:border-neon/40 transition">
                            <div class="text-xs text-slate-400">Account</div>
                            <div class="text-sm font-black text-white">Profile</div>
                        </a>
                    </div>
                    </div>
                </div>

                <details class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-0 overflow-hidden">
                    <summary class="cursor-pointer px-6 py-5 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Strava</div>
                            <div class="text-lg font-black text-white italic tracking-tight mt-1">Sync</div>
                        </div>
                        <div class="text-xs text-slate-400">Open</div>
                    </summary>
                    <div x-data="{ syncing:false, result:null, error:null }" class="px-6 pb-6">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4">
                                <div class="text-xs text-slate-400">This Week</div>
                                <div class="text-2xl font-black text-white">{{ $stravaWeekDistanceKm ?? 0 }} <span class="text-sm font-bold text-slate-400">km</span></div>
                                <div class="text-[11px] text-slate-500 mt-1">{{ $lastStravaSyncAt ? 'Last sync: '.$lastStravaSyncAt->format('d M H:i') : 'Belum pernah sync' }}</div>
                            </div>
                            <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4">
                                <div class="text-xs text-slate-400">Last Activity</div>
                                <div class="text-sm font-bold text-white truncate">{{ optional($lastStravaActivity)->name ?? '—' }}</div>
                                <div class="text-[11px] text-slate-500 mt-1">
                                    @if($lastStravaActivity && $lastStravaActivity->start_date)
                                        {{ $lastStravaActivity->start_date->format('d M Y') }} • {{ number_format(((float) ($lastStravaActivity->distance_m ?? 0)) / 1000, 1) }} km
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex gap-2">
                            @if($stravaConnected)
                                <button
                                    type="button"
                                    @click="syncing=true; result=null; error=null; fetch('{{ route('runner.strava.sync') }}', { method:'POST', headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' } }).then(r=>r.json().then(j=>({ok:r.ok, j}))).then(({ok,j})=>{ if(ok && j.success){ result=j; } else { error=j.message||'Sync gagal'; } }).catch(()=>{ error='Sync gagal'; }).finally(()=>{ syncing=false; })"
                                    class="flex-1 px-4 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all disabled:opacity-60"
                                    :disabled="syncing"
                                >
                                    <span x-show="!syncing">Sync Now</span>
                                    <span x-show="syncing">Syncing…</span>
                                </button>
                                <a href="{{ route('runner.calendar') }}" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm flex items-center justify-center">
                                    Calendar
                                </a>
                            @else
                                <a href="{{ route('runner.strava.connect') }}" class="flex-1 px-4 py-3 rounded-xl bg-orange-500 text-white font-black hover:bg-orange-500/90 transition-all text-center">
                                    Connect
                                </a>
                                <a href="{{ route('runner.calendar') }}" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm flex items-center justify-center">
                                    Calendar
                                </a>
                            @endif
                        </div>

                        <div x-show="result" x-cloak class="mt-4 bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4 text-slate-200">
                            <div class="text-xs text-slate-400">Hasil Sync</div>
                            <div class="text-sm font-bold">Imported <span x-text="result?.imported ?? 0"></span> activities • Linked <span x-text="result?.linked_sessions ?? 0"></span> sessions</div>
                        </div>
                        <div x-show="error" x-cloak class="mt-4 bg-red-500/10 border border-red-500/30 rounded-2xl p-4 text-red-200">
                            <div class="text-sm font-bold" x-text="error"></div>
                        </div>

                        @if(isset($recentStravaActivities) && count($recentStravaActivities) > 0)
                            <div class="mt-6 border-t border-slate-700/50 pt-4">
                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Recent</div>
                                <div class="space-y-2">
                                    @foreach($recentStravaActivities as $act)
                                        <div onclick="window.dispatchEvent(new CustomEvent('open-strava-detail', {detail: {id: '{{ $act->strava_activity_id }}', name: {{ Js::from($act->name) }}, date: '{{ $act->start_date ? $act->start_date->format('Y-m-d') : '' }}'}}))" class="bg-slate-900/40 border border-slate-700/60 rounded-xl p-3 flex items-center justify-between cursor-pointer hover:bg-slate-800 transition shadow-sm hover:shadow-neon/10" style="border-left: 3px solid {{ $act->border_color }};">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="text-xl">{{ $act->icon }}</div>
                                                <div class="min-w-0">
                                                    <div class="text-sm font-bold text-white truncate">{{ $act->name }}</div>
                                                    <div class="text-[10px] text-slate-400">
                                                        {{ $act->start_date->format('D, d M H:i') }}
                                                        @if($act->distance_km > 0) • {{ $act->distance_km }} km @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <div class="text-xs font-mono text-slate-300">{{ $act->formatted_duration }}</div>
                                                @if($act->pace_min_km !== '-') <div class="text-[10px] text-slate-500">{{ $act->pace_min_km }} /km</div> @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </details>
            </div>
        </div>
    </div>

    <!-- VDOT AI Program Generator Modal -->
    <div x-show="openGenerateModal" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-dark/95 backdrop-blur-sm"
         @keydown.escape.window="openGenerateModal = false">
        
        <div class="relative w-full max-w-3xl bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-y-auto max-h-[90vh]"
             @click.outside="openGenerateModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Close Button -->
            <button @click="openGenerateModal = false" class="absolute top-5 right-5 text-slate-500 hover:text-white transition-colors z-30">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Step 1: Input Form -->
            <div x-show="step === 1" class="p-6 md:p-8">
                <h3 class="text-xl font-black text-white uppercase italic tracking-tight mb-2">AI Program Generator</h3>
                <p class="text-xs text-slate-400 mb-6 leading-relaxed">Masukkan catatan waktu lari dan target lomba Anda untuk menghasilkan program latihan terpersonalisasi.</p>

                <div class="space-y-6">
                    <!-- PB Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Parameter Test / PB Jarak</label>
                            <select x-model="pb_distance" class="w-full px-4 py-3 bg-slate-950 border border-slate-850 rounded-xl text-white text-sm focus:outline-none focus:border-neon transition-colors cursor-pointer">
                                <option value="5k">5 Kilometer</option>
                                <option value="10k">10 Kilometer</option>
                                <option value="21k">Half Marathon</option>
                                <option value="42k">Full Marathon</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Waktu Parameter Test / PB</label>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="flex flex-col items-center">
                                    <input x-model="pb_hours" type="number" min="0" max="99" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-white text-center font-bold text-sm focus:outline-none focus:border-neon" placeholder="HH">
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-1">Jam</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <input x-model="pb_minutes" type="number" min="0" max="59" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-white text-center font-bold text-sm focus:outline-none focus:border-neon" placeholder="MM">
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-1">Menit</span>
                                </div>
                                <div class="flex flex-col items-center">
                                    <input x-model="pb_seconds" type="number" min="0" max="59" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-white text-center font-bold text-sm focus:outline-none focus:border-neon" placeholder="SS">
                                    <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-1">Detik</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bio Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Jenis Kelamin</label>
                            <div class="flex p-1 bg-slate-950 rounded-xl border border-slate-850">
                                <button type="button" @click="gender = 'male'" :class="gender === 'male' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500'" class="flex-1 py-2 rounded-lg font-bold text-[11px] transition-all uppercase tracking-wider font-bold">Laki-laki</button>
                                <button type="button" @click="gender = 'female'" :class="gender === 'female' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500'" class="flex-1 py-2 rounded-lg font-bold text-[11px] transition-all uppercase tracking-wider font-bold">Perempuan</button>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Umur</label>
                            <input x-model="age" type="number" min="15" max="90" class="w-full px-4 py-3 bg-slate-950 border border-slate-850 rounded-xl text-white font-bold text-sm focus:outline-none focus:border-neon">
                        </div>
                    </div>

                    <!-- Target Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Target Jarak Lomba</label>
                            <select x-model="target_distance" class="w-full px-4 py-3 bg-slate-950 border border-slate-850 rounded-xl text-white text-sm focus:outline-none focus:border-neon transition-colors cursor-pointer">
                                <option value="5k">5K</option>
                                <option value="10k">10K</option>
                                <option value="21k">Half Marathon</option>
                                <option value="42k">Full Marathon</option>
                                <option value="cooper12">Cooper 12 Min</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Target Tanggal Lomba</label>
                            <input x-model="target_date" type="date" class="w-full px-4 py-3 bg-slate-950 border border-slate-850 rounded-xl text-white font-bold text-sm focus:outline-none focus:border-neon cursor-pointer">
                        </div>
                    </div>

                    <!-- Goal Time Section -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Target Waktu Lomba</label>
                            <template x-if="realism">
                                <div :class="realism.color" class="px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider border" x-text="realism.label"></div>
                            </template>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="flex flex-col items-center">
                                <input x-model="goal_hours" type="number" min="0" max="99" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-white text-center font-bold text-sm focus:outline-none focus:border-neon" placeholder="HH">
                                <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-1">Jam</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <input x-model="goal_minutes" type="number" min="0" max="59" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-white text-center font-bold text-sm focus:outline-none focus:border-neon" placeholder="MM">
                                <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-1">Menit</span>
                            </div>
                            <div class="flex flex-col items-center">
                                <input x-model="goal_seconds" type="number" min="0" max="59" class="w-full px-3 py-2.5 bg-slate-950 border border-slate-850 rounded-xl text-white text-center font-bold text-sm focus:outline-none focus:border-neon" placeholder="SS">
                                <span class="text-[9px] text-slate-500 font-bold uppercase tracking-widest mt-1">Detik</span>
                            </div>
                        </div>
                        <template x-if="realism">
                            <p class="text-[10px] text-slate-400 italic leading-tight mt-1" x-text="realism.description"></p>
                        </template>
                    </div>

                    <!-- Training Load Section -->
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Mileage Mingguan (Km)</label>
                                <span class="text-[9px] font-bold text-neon bg-neon/15 px-2 py-0.5 rounded-full border border-neon/20">Rekomendasi: <span x-text="idealMileage"></span> Km</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <input x-model="weekly_mileage" type="range" min="15" max="120" step="5" class="flex-1 h-1.5 bg-slate-850 rounded-lg appearance-none cursor-pointer accent-neon">
                                <span class="w-10 text-center font-black text-white text-lg" x-text="weekly_mileage"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Frekuensi Latihan (Hari/Minggu)</label>
                            <div class="flex justify-between gap-2">
                                <template x-for="f in [3,4,5,6,7]" :key="f">
                                    <button type="button" @click="frequency = f" 
                                            :class="frequency === f ? 'bg-neon text-dark font-black border-neon' : 'bg-slate-950 text-slate-500 border border-slate-850 hover:border-slate-800'"
                                            class="w-full py-2.5 rounded-xl text-xs transition-all border font-bold" x-text="f">
                                    </button>
                                </template>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Level Pelari</label>
                                <select x-model="runner_level" class="w-full px-4 py-3 bg-slate-950 border border-slate-850 rounded-xl text-white text-sm focus:outline-none focus:border-neon transition-colors cursor-pointer font-bold">
                                    <option value="beginner">Pemula (Beginner)</option>
                                    <option value="intermediate">Menengah (Medium)</option>
                                    <option value="advanced">Mahir / Elite (Advanced)</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Hari Long Run</label>
                                <select x-model="long_run_day" class="w-full px-4 py-3 bg-slate-950 border border-slate-850 rounded-xl text-white text-sm focus:outline-none focus:border-neon transition-colors cursor-pointer font-bold">
                                    <option value="saturday">Sabtu</option>
                                    <option value="sunday">Minggu</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Penyesuaian Tropis (Indonesia)</label>
                            <div class="flex items-center gap-3 p-3.5 bg-slate-950 rounded-xl border border-slate-850">
                                <input type="checkbox" x-model="is_tropical" id="is_tropical" class="w-4 h-4 accent-neon cursor-pointer rounded border-slate-850 bg-slate-850">
                                <label for="is_tropical" class="text-xs text-slate-400 font-bold cursor-pointer select-none">
                                    Aktifkan penyesuaian pace untuk cuaca panas (+10-15s/km untuk menjaga beban kardio stabil)
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="button" @click="generateProgram()" :disabled="loading" 
                            class="w-full py-4 bg-neon hover:bg-neon/90 disabled:bg-slate-800 disabled:text-slate-600 text-dark font-black text-sm rounded-xl transition-all flex items-center justify-center gap-2">
                        <span x-show="!loading">GENERATE AI PROGRAM</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            MEMPROSES...
                        </span>
                    </button>
                </div>
            </div>

            <!-- Step 2: Results Display -->
            <div x-show="step === 2" class="p-6 md:p-8" x-cloak>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-white uppercase italic tracking-tight">Program Latihan Anda</h3>
                    <button type="button" @click="step = 1" class="text-slate-500 hover:text-slate-350 text-xs font-bold flex items-center gap-1">
                        <span>←</span> Kembali
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Sidebar (VDOT & Paces) -->
                    <div class="lg:col-span-1 space-y-4">
                        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-850 text-center">
                            <div class="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Skor VDOT Estimasi</div>
                            <div class="text-5xl font-black text-white tracking-tighter" x-text="result ? result.vdot : '-'"></div>
                            
                            <div class="space-y-1.5 py-4 border-y border-slate-850 mt-4 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Target Jarak</span>
                                    <span class="font-bold text-white uppercase" x-text="target_distance"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Durasi</span>
                                    <span class="font-bold text-white"><span x-text="result ? result.weeks : '-'"></span> Minggu</span>
                                </div>
                            </div>
                        </div>

                        <!-- Paces list -->
                        <div class="bg-slate-950 p-6 rounded-2xl border border-slate-850">
                            <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-4">Training Paces</h4>
                            <div class="space-y-2.5">
                                <template x-for="(paceVal, paceKey) in (result ? result.paces : {})" :key="paceKey">
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="font-bold" :class="getPaceColor(paceKey)" x-text="getPaceLabel(paceKey)"></span>
                                        <span class="font-mono font-bold text-white" x-text="formatPace(paceVal)"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Right Main Content (Weeks Preview) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="max-h-[350px] overflow-y-auto space-y-4 pr-1">
                            <template x-for="(weekSessions, weekNum) in sessionsByWeek" :key="weekNum">
                                <div class="bg-slate-950 p-6 rounded-2xl border border-slate-850">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-sm font-black text-white italic uppercase tracking-tight">Preview Minggu <span x-text="weekNum"></span></h4>
                                        <span class="px-2 py-0.5 bg-neon/10 text-neon text-[9px] font-black rounded border border-neon/20 uppercase tracking-widest">Akses Gratis</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 sm:grid-cols-7 gap-2">
                                        <template x-for="day in weekSessions" :key="day.day">
                                            <div class="p-2.5 rounded-xl border flex flex-col justify-between min-h-[90px]" :class="getSessionClass(day.type)">
                                                <div class="flex justify-between items-start">
                                                    <span class="text-[8px] font-bold text-slate-400" x-text="'D' + day.day"></span>
                                                    <span class="text-xs" x-text="getSessionIcon(day.type)"></span>
                                                </div>
                                                <div class="mt-2">
                                                    <h5 class="text-[8px] font-black text-white uppercase truncate" x-text="day.type.replace('_', ' ')"></h5>
                                                    <p class="text-xs font-black text-white mt-0.5"><span x-text="day.distance"></span> <span class="text-[8px] font-normal text-slate-400">KM</span></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Remaining Weeks Locked Info -->
                        <div class="bg-slate-950 p-6 text-center rounded-2xl border border-dashed border-slate-800">
                            <h4 class="text-white font-bold text-sm mb-1">🔒 Sisa Program Terkunci</h4>
                            <p class="text-slate-400 text-xs mb-4">Sisa program akan terbuka secara otomatis setelah Anda menyimpannya ke kalender lari.</p>
                            <button type="button" @click="saveAndOpenCalendar()" :disabled="saving"
                                    class="px-5 py-2.5 bg-neon hover:bg-neon/90 disabled:bg-slate-800 disabled:text-slate-600 text-dark font-black text-xs rounded-xl transition-all inline-flex items-center gap-2">
                                <span x-show="!saving">SIMPAN KE KALENDER</span>
                                <span x-show="saving" class="w-3 h-3 border-2 border-dark border-t-transparent rounded-full animate-spin"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Required Phone Number Update Modal -->
    <div x-show="showPhoneModal" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-dark/95 backdrop-blur-md">
        
        <div class="relative w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Glow Accent -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-neon/15 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="p-6 md:p-8 relative z-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-neon/10 border border-neon/20 rounded-xl text-neon">
                        <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white uppercase italic tracking-tight">Verifikasi Kontak</h3>
                        <p class="text-[10px] font-mono text-neon uppercase tracking-wider">Nomor Handphone Wajib Diisi</p>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed mb-6">
                    Demi keamanan akun dan kelancaran proses pendaftaran event/coaching di RuangLari, silakan masukkan nomor handphone Anda yang aktif terlebih dahulu.
                </p>

                <form @submit.prevent="submitPhoneNumber()">
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Nomor Handphone</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500 text-sm">📞</span>
                                <input type="tel" 
                                       x-model="inputPhone" 
                                       required
                                       placeholder="Contoh: 081234567890" 
                                       class="w-full pl-10 pr-4 py-3 bg-slate-950 border border-slate-850 rounded-xl text-white font-bold text-sm focus:outline-none focus:border-neon transition-colors"
                                       pattern="[0-9+ ]{8,20}">
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1.5">Gunakan angka saja, minimal 8 digit.</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col gap-2">
                        <button type="submit" 
                                :disabled="submittingPhone"
                                class="w-full py-3 bg-neon hover:bg-neon/90 disabled:bg-slate-800 disabled:text-slate-655 text-dark font-black text-xs rounded-xl transition-all text-center tracking-wider uppercase shadow-lg shadow-neon/20 flex items-center justify-center gap-2">
                            <span x-show="!submittingPhone">Simpan & Lanjutkan</span>
                            <span x-show="submittingPhone" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Complete Profile Suggestion Modal -->
    <div x-show="showProfileCompletionModal" 
         x-cloak
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-dark/90 backdrop-blur-md"
         @keydown.escape.window="showProfileCompletionModal = false">
        
        <div class="relative w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl overflow-hidden"
             @click.outside="showProfileCompletionModal = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            
            <!-- Glow Accent -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-neon/15 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="p-6 md:p-8 relative z-10">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 bg-neon/10 border border-neon/20 rounded-xl text-neon">
                        <svg class="w-6 h-6 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-white uppercase italic tracking-tight">Lengkapi Profil Anda</h3>
                        <p class="text-[10px] font-mono text-neon uppercase tracking-wider">Step to peak performance</p>
                    </div>
                </div>

                <p class="text-xs text-slate-400 leading-relaxed mb-6">
                    Agar dapat menikmati seluruh fitur RuangLari dengan maksimal (coaching, pendaftaran event, dan sinkronisasi), silakan lengkapi informasi profil Anda:
                </p>

                <!-- Status List -->
                <div class="space-y-3 mb-8">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950 border border-slate-850">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">📸</span>
                            <span class="text-xs font-bold text-slate-300">Foto Profil (Avatar)</span>
                        </div>
                        @if(auth()->user()->avatar)
                            <span class="px-2 py-0.5 rounded-full bg-green-500/10 border border-green-500/30 text-green-400 text-[10px] font-bold">Lengkap</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-[10px] font-bold">Belum Ada</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-950 border border-slate-850">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">📞</span>
                            <span class="text-xs font-bold text-slate-300">Nomor Handphone</span>
                        </div>
                        @if(auth()->user()->phone)
                            <span class="px-2 py-0.5 rounded-full bg-green-500/10 border border-green-500/30 text-green-400 text-[10px] font-bold">Lengkap</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-[10px] font-bold">Belum Ada</span>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-2">
                    <a href="{{ route('profile.show') }}" class="w-full py-3 bg-neon hover:bg-neon/90 text-dark font-black text-xs rounded-xl transition-all text-center tracking-wider uppercase shadow-lg shadow-neon/20">
                        Lengkapi Profil Sekarang
                    </a>
                    <button type="button" @click="showProfileCompletionModal = false; sessionStorage.setItem('dismiss_profile_modal', 'true')" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition-all text-center">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div x-show="notification" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed top-24 right-4 z-[110] max-w-sm w-full"
         x-cloak>
        <div :class="notification?.type === 'error' ? 'bg-red-950 border-red-500/40 text-red-200' : 'bg-green-950 border-green-500/40 text-green-200'" 
             class="p-4 rounded-xl border backdrop-blur-md shadow-2xl flex items-start gap-3">
            <span class="text-base" x-text="notification?.type === 'error' ? '⚠️' : '✅'"></span>
            <div class="flex-1 text-xs font-semibold leading-snug" x-text="notification?.message"></div>
            <button @click="notification = null" class="text-slate-400 hover:text-slate-200">✕</button>
        </div>
    </div>

            </div> <!-- closes x-data="dashboardComponent()" -->
        </div> <!-- closes id="tab-content-overview" -->

        <!-- Tab Content Calendar (Vue.js component wrapper) -->
        <div id="tab-content-calendar" class="tab-content mt-6 hidden">
            @include('runner.calendar.html')
        </div>
        
        <!-- Tab Content Strava (Iframe loader) -->
        <div id="tab-content-strava" class="tab-content mt-6 hidden">
            <iframe id="strava-iframe" data-src="/calendar?embed=1#strava" src="" class="w-full min-h-[800px] border-0 bg-transparent" scrolling="no"></iframe>
        </div>

        <!-- Tab Content Calculator (Iframe loader) -->
        <div id="tab-content-calculator" class="tab-content mt-6 hidden">
            <iframe id="calculator-iframe" data-src="/tools/calculator?embed=1" src="" class="w-full min-h-[800px] border-0 bg-transparent" scrolling="no"></iframe>
        </div>

    </div> <!-- closes max-w-7xl mx-auto -->
</div> <!-- closes wrapper -->
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    (function () {
        function pad2(n) {
            return String(n).padStart(2, '0');
        }

        function updateDateTime() {
            var now = new Date();
            var dateEl = document.getElementById('runner-dashboard-date');
            var timeEl = document.getElementById('runner-dashboard-time');
            if (dateEl) {
                try {
                    dateEl.textContent = new Intl.DateTimeFormat('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }).format(now);
                } catch (e) {
                    dateEl.textContent = now.toDateString();
                }
            }
            if (timeEl) {
                timeEl.textContent = pad2(now.getHours()) + ':' + pad2(now.getMinutes());
            }
        }

        updateDateTime();
        setInterval(updateDateTime, 30000);
    })();

    function dashboardComponent() {
        return {
            openGenerateModal: {{ ($activeEnrollments->count() ?? 0) <= 0 ? 'true' : 'false' }},
            showPhoneModal: {{ empty(auth()->user()->phone) ? 'true' : 'false' }},
            showProfileCompletionModal: {{ (empty(auth()->user()->avatar) && !empty(auth()->user()->phone)) ? 'true' : 'false' }},
            inputPhone: '{{ auth()->user()->phone ?? "" }}',
            submittingPhone: false,
            step: 1,
            loading: false,
            saving: false,
            notification: null,
            isReceiveWa: {{ auth()->user()->is_receive_wa ? 'true' : 'false' }},

            card_pb_distance: '5k',
            card_pb_hours: '',
            card_pb_minutes: '',
            card_pb_seconds: '',
            card_pb_balke: '',
            card_pb_loading: false,
            pbs: {
                '5k': '{{ auth()->user()->pb_5k ?? "" }}',
                '10k': '{{ auth()->user()->pb_10k ?? "" }}',
                '21k': '{{ auth()->user()->pb_hm ?? "" }}',
                '42k': '{{ auth()->user()->pb_fm ?? "" }}',
                'balke': '{{ auth()->user()->pb_balke ?? "" }}'
            },

            onCardPbDistanceChange() {
                const dist = this.card_pb_distance;
                const value = this.pbs[dist] || '';
                if (dist === 'balke') {
                    this.card_pb_balke = value;
                } else {
                    if (value && value.includes(':')) {
                        const parts = value.split(':');
                        this.card_pb_hours = parts[0] || '';
                        this.card_pb_minutes = parts[1] || '';
                        this.card_pb_seconds = parts[2] || '';
                    } else {
                        this.card_pb_hours = '';
                        this.card_pb_minutes = '';
                        this.card_pb_seconds = '';
                    }
                }
            },

            async toggleReceiveWa() {
                try {
                    const response = await fetch('{{ route("runner.profile.toggle-wa") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ is_receive_wa: !this.isReceiveWa })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.isReceiveWa = data.is_receive_wa;
                        this.showNotification(data.message || 'Pengaturan WhatsApp diperbarui.', 'success');
                    } else {
                        this.showNotification(data.message || 'Gagal mengubah pengaturan.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    this.showNotification('Terjadi kesalahan koneksi.', 'error');
                }
            },

            async submitCardPb() {
                this.card_pb_loading = true;
                
                const payload = {};
                if (this.card_pb_distance === 'balke') {
                    if (!this.card_pb_balke || parseInt(this.card_pb_balke) <= 0) {
                        this.showNotification('Harap masukkan jarak balke test yang valid dalam meter!', 'error');
                        this.card_pb_loading = false;
                        return;
                    }
                    payload.pb_balke = parseInt(this.card_pb_balke);
                } else {
                    const h = String(this.card_pb_hours || 0).padStart(2, '0');
                    const m = String(this.card_pb_minutes || 0).padStart(2, '0');
                    const s = String(this.card_pb_seconds || 0).padStart(2, '0');
                    const pb_time = `${h}:${m}:${s}`;
                    
                    if (parseInt(h) === 0 && parseInt(m) === 0 && parseInt(s) === 0) {
                        this.showNotification('Harap isi waktu parameter test/PB!', 'error');
                        this.card_pb_loading = false;
                        return;
                    }

                    if (this.card_pb_distance === '5k') payload.pb_5k = pb_time;
                    if (this.card_pb_distance === '10k') payload.pb_10k = pb_time;
                    if (this.card_pb_distance === '21k') payload.pb_hm = pb_time;
                    if (this.card_pb_distance === '42k') payload.pb_fm = pb_time;
                }

                try {
                    const response = await fetch('{{ route("runner.calendar.update-pb") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        this.showNotification('Personal Best / Parameter Test berhasil diperbarui!', 'success');
                        
                        if (this.card_pb_distance === 'balke') {
                            this.pbs['balke'] = this.card_pb_balke;
                        } else {
                            const h = String(this.card_pb_hours || 0).padStart(2, '0');
                            const m = String(this.card_pb_minutes || 0).padStart(2, '0');
                            const s = String(this.card_pb_seconds || 0).padStart(2, '0');
                            const pb_time = `${h}:${m}:${s}`;
                            this.pbs[this.card_pb_distance] = pb_time;
                        }

                        setTimeout(() => {
                            window.location.reload();
                        }, 1200);
                    } else {
                        this.showNotification(data.message || 'Gagal memperbarui Personal Best.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    this.showNotification('Terjadi kesalahan sistem saat memperbarui Personal Best.', 'error');
                } finally {
                    this.card_pb_loading = false;
                }
            },
            
            pb_distance: '5k',
            pb_hours: '',
            pb_minutes: '',
            pb_seconds: '',
            
            target_distance: '10k',
            target_date: '',
            
            goal_hours: '',
            goal_minutes: '',
            goal_seconds: '',
            
            weekly_mileage: 30,
            frequency: 4,
            gender: '{{ auth()->user()->gender ?? 'male' }}',
            age: {{ auth()->user()->date_of_birth ? \Carbon\Carbon::parse(auth()->user()->date_of_birth)->age : 25 }},
            runner_level: 'intermediate',
            long_run_day: 'sunday',
            is_tropical: false,
            
            result: null,
            errors: null,

            async submitPhoneNumber() {
                if (!this.inputPhone || this.inputPhone.trim().length < 8) {
                    this.showNotification('Masukkan nomor handphone yang valid (minimal 8 digit)!', 'error');
                    return;
                }
                this.submittingPhone = true;
                try {
                    const response = await fetch('{{ route("profile.update-phone") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            phone: this.inputPhone
                        })
                    });
                    const data = await response.json();
                    if (response.ok && data.success) {
                        this.showNotification(data.message || 'Nomor HP berhasil disimpan!', 'success');
                        this.showPhoneModal = false;
                        @if(empty(auth()->user()->avatar))
                            this.showProfileCompletionModal = true;
                        @endif
                    } else {
                        this.showNotification(data.message || 'Gagal memperbarui nomor HP.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    this.showNotification('Terjadi kesalahan saat menyimpan nomor HP.', 'error');
                } finally {
                    this.submittingPhone = false;
                }
            },

            showNotification(message, type = 'success') {
                this.notification = { message, type };
                setTimeout(() => {
                    this.notification = null;
                }, 5000);
            },

            init() {
                this.onCardPbDistanceChange();
                this.$watch('card_pb_distance', () => this.onCardPbDistanceChange());

                // Auto suggest target time when pb or target distance changes
                this.$watch('pb_hours', () => this.suggestGoalTime());
                this.$watch('pb_minutes', () => this.suggestGoalTime());
                this.$watch('pb_seconds', () => this.suggestGoalTime());
                this.$watch('pb_distance', () => { this.suggestGoalTime(); this.recommendMileage(); });
                this.$watch('target_distance', () => { this.suggestGoalTime(); this.recommendMileage(); });
                this.$watch('runner_level', () => { this.suggestGoalTime(); this.recommendMileage(); });

                // Check profile modal dismissal state in session storage
                if (sessionStorage.getItem('dismiss_profile_modal') === 'true') {
                    this.showProfileCompletionModal = false;
                }
            },

            get distanceKm() {
                return {
                    '5k': 5,
                    '10k': 10,
                    '21k': 21.0975,
                    '42k': 42.195
                };
            },

            get distanceMeters() {
                return {
                    '5k': 5000,
                    '10k': 10000,
                    '21k': 21097.5,
                    '42k': 42195,
                    'cooper12': 3200
                };
            },

            getRatioForDistance(distanceKey, vdot) {
                const ratios = {
                    '5k': 0.957,
                    '10k': 0.915,
                    '21k': 0.865,
                    '42k': 0.815,
                    'cooper12': 0.99
                };
                const base = ratios[distanceKey] ?? 0.957;
                return base + (vdot - 50) * 0.0005;
            },

            vvo2FromVDOT(vdot) {
                const a = 0.000104;
                const b = 0.182258;
                const c = -4.6 - vdot;
                return (-b + Math.sqrt(b * b - 4 * a * c)) / (2 * a);
            },

            calculateVDOTFromPerformance(distanceKey, totalSeconds) {
                if (!totalSeconds || totalSeconds < 600) return 0;
                const distMeters = this.distanceMeters[distanceKey];
                if (!distMeters) return 0;
                const velocityMin = (distMeters / totalSeconds) * 60;
                let vdot = 50;
                for (let i = 0; i < 5; i++) {
                    const ratio = Math.max(0.01, this.getRatioForDistance(distanceKey, vdot));
                    const vvo2max = velocityMin / ratio;
                    const newVdot = -4.6 + 0.182258 * vvo2max + 0.000104 * vvo2max * vvo2max;
                    if (Math.abs(newVdot - vdot) < 0.01) {
                        vdot = newVdot;
                        break;
                    }
                    vdot = newVdot;
                }
                return Math.max(10, Math.min(85, Number(vdot.toFixed(4))));
            },

            predictRaceTimeSeconds(vdot, distanceKey) {
                if (!vdot || vdot <= 0) return 0;
                const distMeters = this.distanceMeters[distanceKey];
                if (!distMeters) return 0;
                const vvo2max = this.vvo2FromVDOT(vdot);
                const ratio = this.getRatioForDistance(distanceKey, vdot);
                const velocity = vvo2max * ratio;
                if (!velocity || velocity <= 0) return 0;
                return Math.round((distMeters / velocity) * 60);
            },

            get weeksUntilRace() {
                if (!this.target_date) return 12;
                const target = new Date(this.target_date);
                if (isNaN(target.getTime())) return 12;
                const diffDays = Math.ceil((target.getTime() - Date.now()) / (1000 * 60 * 60 * 24));
                const weeks = Math.ceil(diffDays / 7);
                return Math.min(24, Math.max(8, weeks || 12));
            },

            get current_vdot() {
                const t = (parseInt(this.pb_hours || 0) * 3600) + (parseInt(this.pb_minutes || 0) * 60) + parseInt(this.pb_seconds || 0);
                return this.calculateVDOTFromPerformance(this.pb_distance, t);
            },

            get target_vdot() {
                const t = (parseInt(this.goal_hours || 0) * 3600) + (parseInt(this.goal_minutes || 0) * 60) + parseInt(this.goal_seconds || 0);
                return this.calculateVDOTFromPerformance(this.target_distance, t);
            },

            get recommendedImprovementPercent() {
                const base = {
                    '5k': 0.06,
                    '10k': 0.05,
                    '21k': 0.04,
                    '42k': 0.03,
                    'cooper12': 0.07
                };
                const levelFactor = {
                    'beginner': 0.85,
                    'intermediate': 1,
                    'advanced': 1.1
                };
                const basePct = base[this.target_distance] ?? 0.04;
                const scale = Math.min(1.2, Math.max(0.4, this.weeksUntilRace / 16));
                const pct = basePct * scale * (levelFactor[this.runner_level] ?? 1);
                return Math.min(0.08, Math.max(0.015, pct));
            },

            get recommendedTargetVdot() {
                const cv = this.current_vdot;
                if (!cv || cv <= 0) return 0;
                const target = cv * (1 + this.recommendedImprovementPercent);
                return Math.min(target, cv + 3.0);
            },

            suggestGoalTime() {
                const cv = this.current_vdot;
                if (!cv || cv <= 0) return;
                const targetVdot = this.recommendedTargetVdot;
                const predictedSeconds = this.predictRaceTimeSeconds(targetVdot, this.target_distance);
                if (predictedSeconds > 0) {
                    this.goal_hours = Math.floor(predictedSeconds / 3600);
                    this.goal_minutes = Math.floor((predictedSeconds % 3600) / 60);
                    this.goal_seconds = Math.floor(predictedSeconds % 60);
                }
            },

            get realism() {
                const cv = this.current_vdot;
                const tv = this.target_vdot;
                if (!cv || !tv) return null;
                const diff = tv - cv;
                const diffPercent = diff / cv;
                const rec = this.recommendedImprovementPercent;
                const diffLabel = Math.max(0, diffPercent) * 100;
                const recLabel = rec * 100;

                if (diff < 0) {
                    return { label: 'Mudah', color: 'bg-green-950 border-green-500/40 text-green-400', description: 'Target ini berada di bawah performa terbaik Anda saat ini.' };
                }
                if (diffPercent <= rec * 1.1) {
                    return { label: 'Realistis', color: 'bg-blue-950 border-blue-500/40 text-blue-400', description: `Target setara peningkatan ${diffLabel.toFixed(1)}% dari VDOT. Rentang realistis saat ini ~${recLabel.toFixed(1)}%.` };
                }
                if (diffPercent <= rec * 1.6) {
                    return { label: 'Ambisius', color: 'bg-orange-950 border-orange-500/40 text-orange-400', description: `Peningkatan ${diffLabel.toFixed(1)}% tergolong menantang untuk jarak ini.` };
                }
                return { label: 'Sangat Ambisius', color: 'bg-red-950 border-red-500/40 text-red-400', description: `Peningkatan ${diffLabel.toFixed(1)}% terlalu agresif untuk target ini.` };
            },

            get idealMileage() {
                const map = { '5k': 30, '10k': 45, '21k': 65, '42k': 85, 'cooper12': 20 };
                const levelFactor = {
                    'beginner': 0.9,
                    'intermediate': 1,
                    'advanced': 1.1
                };
                const base = map[this.target_distance] || 30;
                const adjusted = base * (levelFactor[this.runner_level] ?? 1);
                const rounded = Math.round(adjusted / 5) * 5;
                return Math.min(120, Math.max(15, rounded));
            },

            recommendMileage() {
                this.weekly_mileage = this.idealMileage;
            },

            get sessionsByWeek() {
                if (!this.result || !this.result.sessions) return {};
                const totalWeeks = this.result.weeks || 8;
                const freeWeeks = Math.max(1, Math.floor(totalWeeks / 2));
                const freePreviewSessions = this.result.sessions.filter(s => s.week <= freeWeeks);
                
                const weeks = {};
                freePreviewSessions.forEach(s => {
                    if (!weeks[s.week]) weeks[s.week] = [];
                    weeks[s.week].push(s);
                });
                return weeks;
            },

            get freeWeeksCount() {
                if (!this.result) return 0;
                return Math.max(1, Math.floor(this.result.weeks / 2));
            },

            getPaceColor(type) {
                const colors = {
                    'E': 'text-emerald-400',
                    'M': 'text-blue-400',
                    'T': 'text-amber-400',
                    'I': 'text-orange-400',
                    'R': 'text-rose-400'
                };
                return colors[type] || 'text-slate-400';
            },

            getPaceLabel(type) {
                const labels = {
                    'E': 'Easy Pace (Aerobic)',
                    'M': 'Marathon Pace',
                    'T': 'Threshold Pace (Tempo)',
                    'I': 'Interval Pace (VO2max)',
                    'R': 'Repetition Pace'
                };
                return labels[type] || type;
            },

            formatPace(minPerKm) {
                if (!minPerKm) return '-';
                const m = Math.floor(minPerKm);
                const s = Math.round((minPerKm - m) * 60);
                return `@ ${m}:${String(s).padStart(2, '0')}/km`;
            },

            getSessionClass(type) {
                const classes = {
                    'rest': 'bg-slate-900/30 border-slate-800/80 text-slate-500',
                    'easy_run': 'bg-emerald-950/20 border-emerald-500/20 text-emerald-400',
                    'long_run': 'bg-blue-950/20 border-blue-500/20 text-blue-400',
                    'threshold': 'bg-amber-950/20 border-amber-500/20 text-amber-400',
                    'interval': 'bg-orange-950/20 border-orange-500/20 text-orange-400',
                    'repetition': 'bg-rose-950/20 border-rose-500/20 text-rose-400',
                    'marathon': 'bg-cyan-950/20 border-cyan-500/20 text-cyan-400',
                    'tempo': 'bg-amber-950/20 border-amber-500/20 text-amber-400'
                };
                return classes[type] || 'bg-slate-900/30 border-slate-800/80 text-slate-500';
            },

            getSessionIcon(type) {
                const icons = {
                    'rest': '🛋️',
                    'easy_run': '🏃',
                    'long_run': '🔋',
                    'threshold': '🔥',
                    'interval': '⚡',
                    'repetition': '🚀',
                    'marathon': '🏆',
                    'tempo': '🔥'
                };
                return icons[type] || '🏃';
            },

            async generateProgram() {
                this.errors = null;
                
                const h = String(this.pb_hours || 0).padStart(2, '0');
                const m = String(this.pb_minutes || 0).padStart(2, '0');
                const s = String(this.pb_seconds || 0).padStart(2, '0');
                const pb_time = `${h}:${m}:${s}`;

                const gh = String(this.goal_hours || 0).padStart(2, '0');
                const gm = String(this.goal_minutes || 0).padStart(2, '0');
                const gs = String(this.goal_seconds || 0).padStart(2, '0');
                const goal_time = `${gh}:${gm}:${gs}`;

                if (parseInt(this.pb_hours || 0) === 0 && parseInt(this.pb_minutes || 0) === 0 && parseInt(this.pb_seconds || 0) === 0) {
                    this.showNotification('Harap isi waktu parameter test/PB!', 'error');
                    return;
                }

                if (parseInt(this.goal_hours || 0) === 0 && parseInt(this.goal_minutes || 0) === 0 && parseInt(this.goal_seconds || 0) === 0) {
                    this.showNotification('Harap isi target waktu lomba!', 'error');
                    return;
                }

                if (!this.target_date) {
                    this.showNotification('Harap lengkapi target tanggal lomba!', 'error');
                    return;
                }

                this.loading = true;
                try {
                    const response = await fetch('{{ route("generator.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            pb_distance: this.pb_distance,
                            pb_time: pb_time,
                            target_distance: this.target_distance,
                            target_date: this.target_date,
                            goal_time: goal_time,
                            weekly_mileage: this.weekly_mileage,
                            frequency: this.frequency,
                            gender: this.gender,
                            age: this.age,
                            runner_level: this.runner_level,
                            long_run_day: this.long_run_day,
                            is_tropical: this.is_tropical
                        })
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        this.result = data.data;
                        this.step = 2;
                    } else {
                        this.errors = data.errors;
                        this.showNotification(data.message || 'Gagal memproses data. Silakan cek input Anda.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    this.showNotification('Terjadi kesalahan sistem.', 'error');
                } finally {
                    this.loading = false;
                }
            },

            async saveAndOpenCalendar() {
                this.saving = true;
                
                const h = String(this.pb_hours || 0).padStart(2, '0');
                const m = String(this.pb_minutes || 0).padStart(2, '0');
                const s = String(this.pb_seconds || 0).padStart(2, '0');
                const pb_time = `${h}:${m}:${s}`;

                const gh = String(this.goal_hours || 0).padStart(2, '0');
                const gm = String(this.goal_minutes || 0).padStart(2, '0');
                const gs = String(this.goal_seconds || 0).padStart(2, '0');
                const goal_time = `${gh}:${gm}:${gs}`;

                const formPayload = {
                    pb_distance: this.pb_distance,
                    pb_time: pb_time,
                    target_distance: this.target_distance,
                    target_date: this.target_date,
                    goal_time: goal_time,
                    weekly_mileage: this.weekly_mileage,
                    frequency: this.frequency,
                    gender: this.gender,
                    age: this.age,
                    runner_level: this.runner_level,
                    long_run_day: this.long_run_day,
                    is_tropical: this.is_tropical
                };

                try {
                    const response = await fetch('{{ route("generator.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            form: formPayload,
                            result: this.result
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.showNotification('Program berhasil disimpan! Memuat ulang halaman...', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showNotification(data.message || 'Gagal menyimpan program.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    this.showNotification('Terjadi kesalahan saat menyimpan.', 'error');
                } finally {
                    this.saving = false;
                }
            }
        };
    }
</script>

<script>
    function triggerApplyProgram(enrollmentId) {
        switchTab('calendar');
        // Wait a brief moment for the tab to transition and Vue to render
        setTimeout(() => {
            if (window.runnerCalendarInstance && typeof window.runnerCalendarInstance.applyProgram === 'function') {
                window.runnerCalendarInstance.applyProgram(enrollmentId);
            } else {
                console.error("Vue calendar instance not ready or applyProgram method not found.");
            }
        }, 300);
    }

    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        
        // Show target tab content
        const targetContent = document.getElementById('tab-content-' + tabName);
        if (targetContent) targetContent.classList.remove('hidden');

        // Update active class on tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        const targetBtn = document.getElementById('tab-btn-' + tabName);
        if (targetBtn) targetBtn.classList.add('active');

        // Update URL query parameter
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);

        // If calendar tab, trigger window resize event to force FullCalendar to re-render
        if (tabName === 'calendar') {
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 100);
        }

        // Lazy load Strava iframe
        if (tabName === 'strava') {
            const iframe = document.getElementById('strava-iframe');
            if (iframe && !iframe.getAttribute('src')) {
                iframe.setAttribute('src', iframe.getAttribute('data-src'));
            }
        }

        // Lazy load Calculator iframe
        if (tabName === 'calculator') {
            const iframe = document.getElementById('calculator-iframe');
            if (iframe && !iframe.getAttribute('src')) {
                iframe.setAttribute('src', iframe.getAttribute('data-src'));
            }
        }
    }

    // Auto-switch based on URL parameter on load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'calendar') {
            switchTab('calendar');
        } else if (tab === 'strava') {
            switchTab('strava');
        } else if (tab === 'calculator') {
            switchTab('calculator');
        } else {
            switchTab('overview');
        }
    });

    // Listen for iframe height adjustments to make it look native
    window.addEventListener('message', function(e) {
        if (e.data && e.data.type === 'resize-iframe') {
            const iframe = document.getElementById('strava-iframe');
            if (iframe && e.data.height) {
                const newHeight = e.data.height + 10;
                const currentHeight = parseInt(iframe.style.height) || 0;
                if (Math.abs(currentHeight - newHeight) > 15) {
                    iframe.style.height = newHeight + 'px';
                }
            }
        }
        if (e.data && e.data.type === 'resize-iframe-calculator') {
            const iframe = document.getElementById('calculator-iframe');
            if (iframe && e.data.height) {
                const newHeight = e.data.height + 10;
                const currentHeight = parseInt(iframe.style.height) || 0;
                if (Math.abs(currentHeight - newHeight) > 15) {
                    iframe.style.height = newHeight + 'px';
                }
            }
        }
    }, false);
</script>
@include('runner.calendar.scripts')
@endpush
@endsection
