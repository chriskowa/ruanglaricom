@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Runner Dashboard')

@section('content')
<div id="runner-dashboard-app" class="min-h-screen pt-20 pb-10 max-w-7xl mx-auto mt-auto relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="mb-10 relative z-10 mt-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase mb-1">Good Morning, Athlete</p>
                <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter">
                    {{ strtoupper(auth()->user()->name) }}
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('programs.realistic') }}" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    New Program
                </a>
                <a href="{{ route('runner.calendar') }}" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition-all shadow-lg shadow-neon/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    Training Calendar
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 relative z-10">
        
        <!-- Wallet Card -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-neon/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-neon transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Wallet Balance</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Rp {{ number_format($walletBalance, 0, ',', '.') }}</h3>
            <div class="mt-2 text-xs text-slate-400">Available for withdrawal</div>
        </div>

        <!-- Earnings Card -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-green-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Total Earnings</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Rp {{ number_format($totalEarnings ?? 0, 0, ',', '.') }}</h3>
            <div class="mt-2 text-xs text-green-400 font-bold">+12% vs last month</div>
        </div>

        <!-- Active Programs -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-purple-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-purple-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Active Programs</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ $activeEnrollments->count() }}</h3>
            <div class="mt-2 text-xs text-slate-400">Programs currently in progress</div>
        </div>

        <!-- Weekly Volume (Mockup) -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-cyan-400/50 transition-all group relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-cyan-400" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM9.8 8.9L7 23h2.1l1.8-8 2.1 2v6h2v-7.5l-2.1-2 .6-3C14.8 12 16.8 13 19 13v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.2L8 8v2h1.8z"/></svg>
            </div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-cyan-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Weekly Volume</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ $weeklyVolumeKm ?? 0 }} <span class="text-sm font-normal text-slate-400">km</span></h3>
            <div class="w-full bg-slate-800 h-1.5 rounded-full mt-4 overflow-hidden">
                <div class="bg-cyan-400 h-full rounded-full" style="width: {{ min(100, (($weeklyVolumeKm ?? 0) / 70) * 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Recent Activity / Programs -->
        <div class="lg:col-span-2 space-y-8">
            <div class="flex justify-between items-end">
                <h2 class="text-2xl font-bold text-white">Your Programs</h2>
                <a href="{{ route('programs.index') }}" class="text-sm text-neon hover:underline">View All</a>
            </div>

            @if($activeEnrollments->count() > 0)
                <div class="space-y-4">
                    @foreach($activeEnrollments as $enrollment)
                    <div class="bg-card/30 border border-slate-700 rounded-xl p-4 flex flex-col md:flex-row gap-4 items-center hover:bg-slate-800/50 transition-colors cursor-pointer group">
                        <div class="w-full md:w-32 h-32 md:h-24 bg-slate-800 rounded-lg overflow-hidden shrink-0">
                            @if($enrollment->program && $enrollment->program->thumbnail)
                                <img src="{{ $enrollment->program->thumbnail_url }}" alt="Program" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity">
                            @else
                                <img src="https://source.unsplash.com/random/200x200/?running" alt="Program" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity">
                            @endif
                        </div>
                        <div class="flex-1 text-center md:text-left">
                            <h3 class="text-lg font-bold text-white group-hover:text-neon transition-colors">
                                <a href="{{ route('programs.show', $enrollment->program->slug) }}" class="hover:underline">
                                    {{ $enrollment->program->title ?? 'Unknown Program' }}
                                </a>
                            </h3>
                            <p class="text-sm text-slate-400 mb-2">Coach {{ $enrollment->program->coach->user->name ?? 'System' }}</p>
                            
                            <div class="flex items-center justify-center md:justify-start gap-4 text-xs font-mono text-slate-500">
                                <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Week 4/12</span>
                                <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg> 85% Compliant</span>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <a href="{{ route('runner.calendar') }}" class="px-4 py-2 rounded-lg bg-slate-700 text-white text-sm font-bold hover:bg-neon hover:text-dark transition-colors">Continue</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-card/30 border border-slate-700 border-dashed rounded-xl p-10 text-center">
                    <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <h3 class="text-white font-bold mb-2">No Active Programs</h3>
                    <p class="text-slate-400 text-sm mb-6">Start your journey today with a personalized plan.</p>
                    <a href="{{ route('programs.index') }}" class="inline-block px-6 py-3 rounded-xl bg-neon text-dark font-bold hover:bg-neon/90">Find a Program</a>
                </div>
            @endif
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-6">

            @if (session('success'))
                <div class="bg-green-500/10 border border-green-500/40 text-green-200 rounded-2xl p-4">
                    <div class="font-bold">{{ session('success') }}</div>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-500/10 border border-red-500/40 text-red-200 rounded-2xl p-4">
                    <div class="font-bold">{{ session('error') }}</div>
                </div>
            @endif

            <div x-data="{ syncing:false, result:null, error:null }" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Strava</div>
                        <h3 class="text-lg font-black text-white italic tracking-tight">SYNC & ANALYTICS</h3>
                        <p class="text-xs text-slate-400 mt-1">Aktivitas Strava otomatis tersambung ke Training Plan & Kalender.</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-300">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4">
                        <div class="text-xs text-slate-400">This Week (Strava)</div>
                        <div class="text-2xl font-black text-white">{{ $stravaWeekDistanceKm ?? 0 }} <span class="text-sm font-bold text-slate-400">km</span></div>
                        <div class="text-[11px] text-slate-500 mt-1">{{ $lastStravaSyncAt ? 'Last sync: '.$lastStravaSyncAt->format('d M H:i') : 'Belum pernah sync' }}</div>
                    </div>
                    <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4">
                        <div class="text-xs text-slate-400">Last Activity</div>
                        <div class="text-sm font-bold text-white truncate">{{ $lastStravaActivity?->name ?? '—' }}</div>
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
                            Open Calendar
                        </a>
                    @else
                        <a href="{{ route('runner.strava.connect') }}" class="flex-1 px-4 py-3 rounded-xl bg-orange-500 text-white font-black hover:bg-orange-500/90 transition-all text-center">
                            Connect Strava
                        </a>
                        <a href="{{ route('runner.calendar') }}" class="px-4 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-neon hover:text-neon transition-all font-bold text-sm flex items-center justify-center">
                            Open Calendar
                        </a>
                    @endif
                </div>

                <div x-show="result" x-cloak class="mt-4 bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4 text-slate-200">
                    <div class="text-xs text-slate-400">Hasil Sync</div>
                    <div class="text-sm font-bold">Imported <span x-text="result?.imported ?? 0"></span> activities • Linked <span x-text="result?.linked_sessions ?? 0"></span> sessions</div>
                    <div class="text-[11px] text-slate-500 mt-1">Refresh kalender untuk melihat event Strava.</div>
                </div>
                <div x-show="error" x-cloak class="mt-4 bg-red-500/10 border border-red-500/30 rounded-2xl p-4 text-red-200">
                    <div class="text-sm font-bold" x-text="error"></div>
                </div>

                @if(isset($recentStravaActivities) && count($recentStravaActivities) > 0)
                <div class="mt-6 border-t border-slate-700/50 pt-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Recent Activities</div>
                        <a href="{{ route('runner.calendar') }}" class="text-xs text-neon hover:underline">View Calendar</a>
                    </div>
                    <div class="space-y-2">
                        @foreach($recentStravaActivities as $act)
                            <div class="bg-slate-900/40 border border-slate-700/60 rounded-xl p-3 flex items-center justify-between group hover:border-slate-500 transition-colors" style="border-left: 3px solid {{ $act->border_color }};">
                                <div class="flex items-center gap-3">
                                    <div class="text-xl">{{ $act->icon }}</div>
                                    <div>
                                        <div class="text-sm font-bold text-white group-hover:text-neon transition-colors">{{ $act->name }}</div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $act->start_date->format('D, d M H:i') }}
                                            @if($act->distance_km > 0)
                                             • {{ $act->distance_km }} km
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-mono text-slate-300">{{ $act->formatted_duration }}</div>
                                    @if($act->pace_min_km !== '-')
                                        <div class="text-[10px] text-slate-500">{{ $act->pace_min_km }} /km</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Training</div>
                        <h3 class="text-lg font-black text-white italic tracking-tight">NEXT 7 DAYS</h3>
                        <p class="text-xs text-slate-400 mt-1">Ringkasan plan paling dekat dari program & custom.</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-neon/10 border border-neon/20 flex items-center justify-center text-neon">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    @forelse($upcomingWorkouts as $w)
                        <div class="flex items-start justify-between gap-3 bg-slate-900/40 border border-slate-700/60 rounded-xl px-4 py-3">
                            <div class="min-w-0">
                                <div class="text-xs text-slate-400">{{ $w['date_label'] }}</div>
                                <div class="text-sm font-bold text-white truncate">{{ $w['type'] }} <span class="text-slate-400 font-normal">• {{ $w['program_title'] }}</span></div>
                                <div class="text-[11px] text-slate-500">
                                    @if($w['distance']) {{ $w['distance'] }} km @endif
                                    @if($w['duration']) <span class="text-slate-600">•</span> {{ $w['duration'] }} @endif
                                </div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-xs font-bold {{ $w['status'] === 'completed' ? 'text-green-300' : ($w['status'] === 'started' ? 'text-yellow-300' : 'text-slate-400') }}">
                                    {{ strtoupper($w['status']) }}
                                </div>
                                @if($w['strava_link'])
                                    <a href="{{ $w['strava_link'] }}" target="_blank" class="text-[11px] text-orange-300 hover:underline">Strava</a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-400">Belum ada plan 7 hari ke depan.</div>
                    @endforelse
                </div>

                <div class="mt-4">
                    <a href="{{ route('runner.calendar') }}" class="inline-flex items-center gap-2 text-sm text-neon hover:underline font-bold">
                        Buka Training Calendar
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </a>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2">Quick Access</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('runner.calendar') }}" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-center transition-colors group">
                        <svg class="w-6 h-6 text-purple-400 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                        <span class="text-xs text-slate-300 font-bold">Calendar</span>
                    </a>
                    <a href="{{ $stravaConnected ? route('runner.calendar') : route('runner.strava.connect') }}" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-center transition-colors group">
                        <svg class="w-6 h-6 text-cyan-400 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        <span class="text-xs text-slate-300 font-bold">Strava</span>
                    </a>
                    <a href="{{ route('wallet.index') }}" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-center transition-colors group">
                        <svg class="w-6 h-6 text-green-400 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <span class="text-xs text-slate-300 font-bold">Wallet</span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="p-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-center transition-colors group">
                        <svg class="w-6 h-6 text-pink-400 mx-auto mb-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span class="text-xs text-slate-300 font-bold">Settings</span>
                    </a>
                </div>
            </div>

            <!-- Weather Widget (Mockup) -->
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white/20 rounded-full blur-xl"></div>
                <div class="flex justify-between items-start">
                    <div>
                        <h4 id="weather-city" class="font-bold text-lg">—</h4>
                        <p id="weather-time" class="text-sm opacity-80">—</p>
                    </div>
                    <svg class="w-10 h-10 text-yellow-300 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                </div>
                <div class="mt-4">
                    <span id="weather-temp" class="text-4xl font-bold">—°C</span>
                    <p class="text-sm mt-1">Perfect for a morning run!</p>
                </div>
            </div>

        </div>
    </div>
</div>
@push('scripts')
<script>
    (function(){
        var cityEl = document.getElementById('weather-city');
        var tempEl = document.getElementById('weather-temp');
        var timeEl = document.getElementById('weather-time');
        function setInfo(city, temp){
            if(cityEl) cityEl.textContent = city;
            if(tempEl) tempEl.textContent = Math.round(temp) + '°C';
            if(timeEl) timeEl.textContent = 'Today, ' + dayjs().format('HH:mm');
        }
        function fetchWeather(lat, lon){
            var wUrl = 'https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current=temperature_2m&timezone=auto';
            fetch(wUrl).then(function(r){ return r.json(); }).then(function(data){
                var temp = (data && data.current && data.current.temperature_2m) ? data.current.temperature_2m : null;
                if(temp == null){ setInfo('Unknown', 28); return; }
                var gUrl = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lon;
                fetch(gUrl, { headers: { 'Accept': 'application/json' } }).then(function(r){ return r.json(); }).then(function(geo){
                    var addr = geo && geo.address ? geo.address : {};
                    var city = addr.city || addr.town || addr.village || addr.state || 'Unknown';
                    setInfo(city, temp);
                }).catch(function(){ setInfo('Unknown', temp); });
            }).catch(function(){ setInfo('Jakarta', 28); });
        }
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(function(pos){
                var lat = pos.coords.latitude;
                var lon = pos.coords.longitude;
                fetchWeather(lat, lon);
            }, function(){
                fetchWeather(-6.2, 106.8);
            }, { timeout: 5000 });
        } else {
            fetchWeather(-6.2, 106.8);
        }
    })();
</script>
@endpush
@endsection
