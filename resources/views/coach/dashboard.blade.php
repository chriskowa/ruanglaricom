@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Coach Dashboard')

@push('styles')
    <script>
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
                    DEFAULT: '#ccff00',
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    yellow: '#eab308',
                }
            }
        }
    </script>
@endpush

@section('content')
<div id="coach-dashboard-app" class="max-w-7xl mx-auto mt-[100px] pb-10 px-4 md:px-8 font-sans">
    
    <!-- Hero Section -->
    <div class="mb-10 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <p class="text-cyan-400 font-mono text-sm tracking-widest uppercase mb-1">Coach Command Center</p>
                <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter">
                    {{ strtoupper(auth()->user()->name) }}
                </h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('coach.programs.create') }}" class="px-6 py-3 rounded-xl bg-slate-800 border border-slate-600 text-white hover:border-cyan-400 hover:text-cyan-400 transition-all font-bold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Create Program
                </a>
                <a href="{{ route('coach.athletes.index') }}" class="px-6 py-3 rounded-xl bg-gradient-to-r from-cyan-600 to-purple-600 text-white font-black hover:scale-105 transition-all shadow-lg shadow-cyan-500/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    Manage Athletes
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 relative z-10">
        
        <!-- Wallet Card -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-cyan-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-cyan-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Wallet Balance</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Rp {{ number_format($walletBalance, 0, ',', '.') }}</h3>
            <div class="mt-2 text-xs text-slate-400">Ready for withdrawal</div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-green-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-green-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Lifetime Revenue</span>
            </div>
            <h3 class="text-2xl font-bold text-white">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</h3>
            <div class="mt-2 text-xs text-green-400 font-bold">Consistent Growth</div>
        </div>

        <!-- My Programs -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-purple-400/50 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-purple-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Created Programs</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ auth()->user()->programs()->count() }}</h3>
            <div class="mt-2 text-xs text-slate-400">Programs in marketplace</div>
        </div>

        <!-- Total Students -->
        <div class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl p-6 hover:border-yellow-400/50 transition-all group relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
            <div class="flex justify-between items-start mb-4">
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 group-hover:text-yellow-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <span class="text-xs font-mono text-slate-500 uppercase">Total Students</span>
            </div>
            <h3 class="text-2xl font-bold text-white">{{ \App\Models\ProgramEnrollment::whereHas('program', function($q) { $q->where('coach_id', auth()->id()); })->count() }}</h3>
            <div class="mt-2 text-xs text-slate-400">Active enrollments</div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-10 relative z-10">
        <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4">
            <div class="text-[11px] font-mono text-slate-500 uppercase tracking-widest">Due Today</div>
            <div class="mt-2 text-2xl font-black text-white">{{ number_format((int) ($coachMetrics['due_today'] ?? 0)) }}</div>
        </div>
        <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4">
            <div class="text-[11px] font-mono text-slate-500 uppercase tracking-widest">Needs Review</div>
            <div class="mt-2 text-2xl font-black text-white">{{ number_format((int) ($coachMetrics['needs_review'] ?? 0)) }}</div>
        </div>
        <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4">
            <div class="text-[11px] font-mono text-slate-500 uppercase tracking-widest">Overdue</div>
            <div class="mt-2 text-2xl font-black text-white">{{ number_format((int) ($coachMetrics['overdue'] ?? 0)) }}</div>
        </div>
        <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4">
            <div class="text-[11px] font-mono text-slate-500 uppercase tracking-widest">Unread Chats</div>
            <div class="mt-2 text-2xl font-black text-white">{{ number_format((int) ($coachMetrics['unread_chats'] ?? 0)) }}</div>
        </div>
        <div class="bg-slate-900/40 border border-slate-700/60 rounded-2xl p-4 col-span-2 md:col-span-2">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[11px] font-mono text-slate-500 uppercase tracking-widest">Week Completion</div>
                    <div class="mt-2 text-2xl font-black text-white">{{ number_format((int) ($coachMetrics['weekly_completion_rate'] ?? 0)) }}%</div>
                    <div class="mt-1 text-xs text-slate-400">
                        {{ number_format((int) ($coachMetrics['weekly_completed'] ?? 0)) }}/{{ number_format((int) ($coachMetrics['weekly_scheduled'] ?? 0)) }} sessions · {{ number_format((int) ($coachMetrics['active_athletes'] ?? 0)) }} athletes
                    </div>
                </div>
                <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3a1 1 0 012 0v18a1 1 0 01-2 0V3zm8 8a1 1 0 011 1v8a1 1 0 11-2 0v-8a1 1 0 011-1zM5 13a1 1 0 011 1v6a1 1 0 11-2 0v-6a1 1 0 011-1z" /></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links & Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-card/30 border border-slate-700 rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-lg font-black text-white italic tracking-tight">Today Queue</h3>
                        <div class="text-xs text-slate-400 mt-1">Prioritas: risk, overdue, needs review, due today, unread chat.</div>
                    </div>
                    <a href="{{ route('coach.athletes.index') }}" class="text-sm text-cyan-400 hover:underline font-bold">View Athletes</a>
                </div>

                @if(empty($queueItems ?? []))
                    <div class="py-10 text-center border border-dashed border-slate-700 rounded-2xl bg-slate-900/40">
                        <div class="text-white font-bold">Queue bersih.</div>
                        <div class="text-xs text-slate-400 mt-1">Belum ada atlet yang butuh tindakan hari ini.</div>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach(($queueItems ?? []) as $it)
                            <div class="p-4 rounded-2xl bg-slate-900/40 border border-slate-700/60 hover:border-cyan-400/40 transition-all">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3 min-w-0">
                                        @if(!empty($it['runner_avatar']))
                                            <img src="{{ $it['runner_avatar'] }}" alt="{{ $it['runner_name'] }}" class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-black text-sm">
                                                {{ strtoupper(mb_substr($it['runner_name'] ?? 'R', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <div class="text-white font-black truncate">{{ $it['runner_name'] }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">{{ $it['program_title'] }}</div>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-300">
                                                <span class="font-bold">{{ $it['date_label'] }}</span>
                                                <span class="text-slate-500">·</span>
                                                <span class="capitalize">{{ str_replace('_', ' ', $it['type'] ?? 'run') }}</span>
                                                @if(!empty($it['distance']))
                                                    <span class="text-slate-500">·</span>
                                                    <span>{{ $it['distance'] }} km</span>
                                                @endif
                                                @if(!empty($it['duration']))
                                                    <span class="text-slate-500">·</span>
                                                    <span>{{ $it['duration'] }}</span>
                                                @endif
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @if(!empty($it['flags']['risk']))
                                                    <span class="px-2 py-1 rounded-lg bg-red-600/20 text-red-300 border border-red-600/30 text-[11px] font-black">Risk</span>
                                                @endif
                                                @if(!empty($it['flags']['overdue']))
                                                    <span class="px-2 py-1 rounded-lg bg-orange-600/20 text-orange-300 border border-orange-600/30 text-[11px] font-black">Overdue</span>
                                                @endif
                                                @if(!empty($it['flags']['needs_review']))
                                                    <span class="px-2 py-1 rounded-lg bg-yellow-600/20 text-yellow-300 border border-yellow-600/30 text-[11px] font-black">Needs Review</span>
                                                @endif
                                                @if(!empty($it['flags']['due_today']))
                                                    <span class="px-2 py-1 rounded-lg bg-cyan-600/20 text-cyan-300 border border-cyan-600/30 text-[11px] font-black">Due Today</span>
                                                @endif
                                                @if(!empty($it['unread_count']))
                                                    <span class="px-2 py-1 rounded-lg bg-purple-600/20 text-purple-300 border border-purple-600/30 text-[11px] font-black">{{ (int) $it['unread_count'] }} Unread</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2 w-[120px] shrink-0">
                                        <a href="{{ route('chat.show', $it['runner_id']) }}" class="px-3 py-2 rounded-xl bg-neon text-dark font-black text-xs text-center hover:bg-neon/90 transition">Chat</a>
                                        @if(!empty($it['enrollment_id']))
                                            <a href="{{ route('coach.athletes.show', $it['enrollment_id']) }}" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold text-xs text-center hover:border-cyan-400/60 hover:text-cyan-300 transition">Review</a>
                                        @else
                                            <a href="{{ route('coach.athletes.index') }}" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold text-xs text-center hover:border-cyan-400/60 hover:text-cyan-300 transition">Open</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-card/30 border border-slate-700 rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-lg font-black text-white italic tracking-tight">Recent Activity</h3>
                        <div class="text-xs text-slate-400 mt-1">Aktivitas selesai (7 hari terakhir).</div>
                    </div>
                    <a href="{{ route('chat.index') }}" class="text-sm text-slate-300 hover:text-white font-bold">Open Chat</a>
                </div>

                @if(empty($recentActivities ?? []))
                    <div class="py-10 text-center border border-dashed border-slate-700 rounded-2xl bg-slate-900/40">
                        <div class="text-white font-bold">Belum ada aktivitas.</div>
                        <div class="text-xs text-slate-400 mt-1">Nanti hasil latihan atlet akan muncul di sini.</div>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach(($recentActivities ?? []) as $a)
                            <div class="p-4 rounded-2xl bg-slate-900/40 border border-slate-700/60 hover:border-slate-500/60 transition-all">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3 min-w-0">
                                        @if(!empty($a['runner_avatar']))
                                            <img src="{{ $a['runner_avatar'] }}" alt="{{ $a['runner_name'] }}" class="w-10 h-10 rounded-full object-cover border border-slate-700">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-black text-sm">
                                                {{ strtoupper(mb_substr($a['runner_name'] ?? 'R', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                                <div class="text-white font-black truncate">{{ $a['runner_name'] }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono">{{ $a['program_title'] }}</div>
                                            </div>
                                            <div class="mt-1 text-xs text-slate-300">
                                                <span class="capitalize">{{ str_replace('_', ' ', $a['type'] ?? 'run') }}</span>
                                                @if(!empty($a['distance']))
                                                    <span class="text-slate-500">·</span>
                                                    <span>{{ $a['distance'] }} km</span>
                                                @endif
                                                @if(!empty($a['duration']))
                                                    <span class="text-slate-500">·</span>
                                                    <span>{{ $a['duration'] }}</span>
                                                @endif
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                @if(!empty($a['rpe']))
                                                    <span class="px-2 py-1 rounded-lg bg-slate-800 border border-slate-700 text-[11px] text-slate-200 font-bold">RPE {{ (int) $a['rpe'] }}</span>
                                                @endif
                                                @if(!empty($a['feeling']))
                                                    <span class="px-2 py-1 rounded-lg bg-slate-800 border border-slate-700 text-[11px] text-slate-200 font-bold capitalize">{{ $a['feeling'] }}</span>
                                                @endif
                                                @if(empty($a['coach_rating']))
                                                    <span class="px-2 py-1 rounded-lg bg-yellow-600/20 text-yellow-300 border border-yellow-600/30 text-[11px] font-black">Not Reviewed</span>
                                                @else
                                                    <span class="px-2 py-1 rounded-lg bg-green-600/20 text-green-300 border border-green-600/30 text-[11px] font-black">Reviewed</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2 w-[120px] shrink-0">
                                        <a href="{{ route('chat.show', $a['runner_id']) }}" class="px-3 py-2 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold text-xs text-center hover:border-neon hover:text-neon transition">Chat</a>
                                        @if(!empty($a['enrollment_id']))
                                            <a href="{{ route('coach.athletes.show', $a['enrollment_id']) }}" class="px-3 py-2 rounded-xl bg-neon text-dark font-black text-xs text-center hover:bg-neon/90 transition">Review</a>
                                        @else
                                            <a href="{{ route('coach.athletes.index') }}" class="px-3 py-2 rounded-xl bg-neon text-dark font-black text-xs text-center hover:bg-neon/90 transition">Open</a>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                    <div>
                                        @if(!empty($a['completed_at']))
                                            {{ \Carbon\Carbon::parse($a['completed_at'])->translatedFormat('d M Y, H:i') }}
                                        @endif
                                    </div>
                                    @if(!empty($a['strava_link']))
                                        <a href="{{ $a['strava_link'] }}" target="_blank" rel="noopener" class="text-cyan-400 hover:underline font-bold">Strava</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions Sidebar -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-purple-900/50 to-slate-900 border border-purple-500/30 rounded-2xl p-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl"></div>
                <h3 class="text-white font-bold text-lg mb-2">Coach Hub</h3>
                <p class="text-slate-400 text-sm mb-6">Manage your athletes, review training logs, and provide feedback.</p>
                <a href="{{ route('coach.athletes.index') }}" class="block w-full py-3 bg-purple-600 hover:bg-purple-500 text-white font-bold text-center rounded-xl transition-colors shadow-lg shadow-purple-500/25">
                    Open Hub
                </a>
            </div>

            <div class="bg-card/50 border border-slate-700 rounded-2xl p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-mono text-slate-500 uppercase tracking-widest">Team Snapshot</div>
                        <div class="mt-2 text-white font-black text-xl">{{ number_format((int) ($coachMetrics['weekly_completion_rate'] ?? 0)) }}%</div>
                        <div class="mt-1 text-xs text-slate-400">Completion week-to-date</div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m4 0a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h14z" /></svg>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-700/60">
                        <div class="text-[11px] font-mono text-slate-500 uppercase tracking-widest">Risk</div>
                        <div class="mt-1 text-white font-black">{{ number_format((int) ($coachMetrics['risk'] ?? 0)) }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900/40 border border-slate-700/60">
                        <div class="text-[11px] font-mono text-slate-500 uppercase tracking-widest">Active</div>
                        <div class="mt-1 text-white font-black">{{ number_format((int) ($coachMetrics['active_athletes'] ?? 0)) }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-card/50 border border-slate-700 rounded-2xl p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 border-b border-slate-700 pb-2">Shortcuts</h3>
                <div class="space-y-2">
                    <a href="{{ route('coach.programs.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group">
                        <span class="text-slate-300 group-hover:text-white text-sm font-medium">My Programs List</span>
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="{{ route('coach.withdrawals.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group">
                        <span class="text-slate-300 group-hover:text-white text-sm font-medium">Withdrawal History</span>
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-800 hover:bg-slate-700 transition-colors group">
                        <span class="text-slate-300 group-hover:text-white text-sm font-medium">Edit Profile</span>
                        <svg class="w-4 h-4 text-slate-500 group-hover:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
