@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Runner Dashboard')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="mt-6 md:mt-10 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div class="min-w-0">
                <div class="text-neon font-mono text-xs tracking-widest uppercase">{{ $greeting }}, Runner</div>
                <h1 class="text-3xl md:text-5xl font-black text-white italic tracking-tighter truncate">{{ strtoupper(auth()->user()->name) }}</h1>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/50 border border-slate-700/60 text-xs text-slate-300">
                        <span class="text-slate-400">Hari ini</span>
                        <span id="runner-dashboard-date" class="font-bold text-white"></span>
                        <span class="text-slate-600">•</span>
                        <span id="runner-dashboard-time" class="font-mono text-slate-300"></span>
                    </div>
                    @if(!empty($nextWorkout))
                        <a href="{{ route('runner.calendar') }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-neon/10 border border-neon/20 text-xs text-neon hover:bg-neon/15 transition">
                            <span class="font-bold">Next</span>
                            <span class="text-slate-200">{{ ucwords(str_replace('_', ' ', (string) ($nextWorkout['type'] ?? 'Run'))) }}</span>
                            @if(!empty($nextWorkout['distance'])) <span class="text-slate-400">•</span> <span class="text-slate-200">{{ $nextWorkout['distance'] }} km</span> @endif
                            <span class="text-slate-400">•</span>
                            <span class="text-slate-200">{{ $nextWorkout['date_label'] ?? '' }}</span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 w-full md:w-auto md:flex">
                <a href="{{ route('runner.calendar') }}" class="px-4 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Calendar
                </a>
                <a href="{{ route('programs.index') }}" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    Programs
                </a>
            </div>
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
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Today</div>
                            <h2 class="text-xl md:text-2xl font-black text-white italic tracking-tight mt-1">Latihan Hari Ini</h2>
                        </div>
                        <div class="shrink-0">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-900/50 border border-slate-700/60 text-xs text-slate-300">
                                <span class="text-slate-400">Active</span>
                                <span class="font-bold text-white">{{ number_format((int) ($activeEnrollments->count() ?? 0)) }}</span>
                            </div>
                        </div>
                    </div>

                    @if(!empty($todayWorkout))
                        @php($todayStatus = (string) ($todayWorkout['status'] ?? 'pending'))
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
                    @else
                        @if(($activeEnrollments->count() ?? 0) <= 0)
                            <div class="mt-5 bg-slate-900/40 border border-slate-700/60 rounded-2xl p-5">
                                <div class="text-sm font-bold text-white">Belum ada program aktif.</div>
                                <div class="text-xs text-slate-400 mt-1">Pilih program supaya dashboard langsung ngasih latihan harian dan progres minggu ini.</div>
                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <a href="{{ route('programs.index') }}" class="px-4 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all text-center">Pilih Program</a>
                                    <a href="{{ route('runner.calendar') }}" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm text-center">Buka Calendar</a>
                                </div>
                            </div>
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

                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Week</div>
                            <h3 class="text-lg font-black text-white italic tracking-tight mt-1">7 Hari ke Depan</h3>
                        </div>
                        <a href="{{ route('runner.calendar') }}" class="text-sm text-neon hover:underline font-bold">Lihat</a>
                    </div>
                    <div class="mt-4 -mx-2 px-2 overflow-x-auto">
                        <div class="flex gap-2 min-w-max">
                            @foreach(($weekStrip ?? []) as $d)
                                @php($st = (string) ($d['status'] ?? 'rest'))
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

                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Next Up</div>
                            <h3 class="text-lg font-black text-white italic tracking-tight mt-1">Latihan Berikutnya</h3>
                        </div>
                        <a href="{{ route('runner.calendar') }}" class="text-sm text-neon hover:underline font-bold">Calendar</a>
                    </div>

                    @php($rows = array_slice(($upcomingWorkouts ?? []), 0, 3))
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
                                    @php($s = (string) ($w['status'] ?? 'pending'))
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

            <div class="space-y-6">
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Progress</div>
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

                <!-- VDOT & Paces Card -->
                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Training Science</div>
                            <h3 class="text-lg font-black text-white italic tracking-tight mt-1">VDOT & Target Pace</h3>
                        </div>
                        @if(auth()->user()->vdot)
                            <div class="px-2.5 py-1 rounded-full bg-neon text-dark font-black text-xs flex items-center gap-1 font-mono">
                                ⚡ VDOT {{ round(auth()->user()->vdot, 1) }}
                            </div>
                        @endif
                    </div>

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

                <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                    <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Quick</div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <a href="{{ route('runner.calendar') }}" class="p-3 rounded-xl bg-slate-900/40 border border-slate-700/60 hover:border-neon/40 transition">
                            <div class="text-xs text-slate-400">Training</div>
                            <div class="text-sm font-black text-white">Calendar</div>
                        </a>
                        <a href="{{ route('programs.index') }}" class="p-3 rounded-xl bg-slate-900/40 border border-slate-700/60 hover:border-neon/40 transition">
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
                                        <div class="bg-slate-900/40 border border-slate-700/60 rounded-xl p-3 flex items-center justify-between" style="border-left: 3px solid {{ $act->border_color }};">
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
</div>
@push('scripts')
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
</script>
@endpush
@endsection
