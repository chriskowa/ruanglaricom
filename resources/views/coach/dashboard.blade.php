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
<div id="coach-dashboard-app" x-data="commandWorkspace()" class="max-w-7xl mx-auto mt-[100px] pb-10 px-4 md:px-8 font-sans">
    
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

    <!-- Coach Command Center Workspace -->
    <div class="mt-12 relative z-10" data-aos="fade-up">
        <div class="bg-card/40 backdrop-blur-md border border-slate-700/50 rounded-3xl p-6 md:p-8">
            
            <!-- Header & Tabs -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 border-b border-slate-700/50 pb-6">
                <div>
                    <h2 class="text-2xl font-black text-white italic tracking-tight">Interactive Command Workspace</h2>
                    <p class="text-xs text-slate-400 mt-1">Manage all your training programs and runner progress in real-time.</p>
                </div>
                
                <div class="flex bg-slate-900/60 p-1.5 rounded-2xl border border-slate-800 self-stretch md:self-auto flex-wrap gap-1">
                    <button @click="activeTab = 'athletes'" :class="activeTab === 'athletes' ? 'bg-neon text-dark font-black shadow-lg shadow-neon/15' : 'text-slate-400 hover:text-white font-bold'" class="flex-grow md:flex-none px-5 py-2.5 rounded-xl text-sm transition-all">
                        <i class="fa-solid fa-users mr-2"></i> My Athletes
                    </button>
                    <button @click="activeTab = 'programs'" :class="activeTab === 'programs' ? 'bg-neon text-dark font-black shadow-lg shadow-neon/15' : 'text-slate-400 hover:text-white font-bold'" class="flex-grow md:flex-none px-5 py-2.5 rounded-xl text-sm transition-all">
                        <i class="fa-solid fa-person-running mr-2"></i> My Programs
                    </button>
                    <button @click="activeTab = 'calendar'" :class="activeTab === 'calendar' ? 'bg-neon text-dark font-black shadow-lg shadow-neon/15' : 'text-slate-400 hover:text-white font-bold'" class="flex-grow md:flex-none px-5 py-2.5 rounded-xl text-sm transition-all">
                        <i class="fa-solid fa-calendar mr-2"></i> Team Calendar
                    </button>
                </div>
            </div>

            <!-- Tab 1: Athletes Management -->
            <div x-show="activeTab === 'athletes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Search & Filters -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <!-- Search Input -->
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 group-focus-within:text-neon transition-colors">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" x-model="athleteSearch" placeholder="Search athlete by name or email..." class="w-full pl-10 pr-4 py-3 bg-slate-900/60 border border-slate-700/50 rounded-2xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-neon focus:border-neon transition-all">
                    </div>
                    <!-- Program Filter -->
                    <div class="relative">
                        <select x-model="athleteProgramFilter" class="w-full px-4 py-3 bg-slate-900/60 border border-slate-700/50 rounded-2xl text-sm text-white focus:outline-none focus:ring-1 focus:ring-neon focus:border-neon appearance-none cursor-pointer">
                            <option value="">All Programs</option>
                            @foreach($myPrograms as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                            @endforeach
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                    <!-- Risk / Status Filter -->
                    <div class="relative">
                        <select x-model="athleteRiskFilter" class="w-full px-4 py-3 bg-slate-900/60 border border-slate-700/50 rounded-2xl text-sm text-white focus:outline-none focus:ring-1 focus:ring-neon focus:border-neon appearance-none cursor-pointer">
                            <option value="">All Statuses</option>
                            <option value="risk">High Risk Athletes Only</option>
                            <option value="needs_review">Needs Review Only</option>
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <!-- Athletes Table/Cards Grid -->
                <div class="overflow-x-auto rounded-2xl border border-slate-700/40">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900/40 text-slate-400 text-xs font-mono uppercase tracking-wider border-b border-slate-700/50">
                                <th class="py-4 px-6">Athlete / Contact</th>
                                <th class="py-4 px-6">Current Training Plan</th>
                                <th class="py-4 px-6">Weekly Progress</th>
                                <th class="py-4 px-6">Target Volume</th>
                                <th class="py-4 px-6 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-slate-300 divide-y divide-slate-800/60">
                            <template x-for="ath in filteredAthletes" :key="ath.id">
                                <tr class="hover:bg-slate-800/20 transition-colors">
                                    <!-- Avatar & Name -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="relative">
                                                <template x-if="ath.runner_avatar">
                                                    <img :src="ath.runner_avatar" alt="" class="w-11 h-11 rounded-full object-cover border border-slate-700">
                                                </template>
                                                <template x-if="!ath.runner_avatar">
                                                    <div class="w-11 h-11 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-sm" x-text="ath.runner_name.substring(0, 1).toUpperCase()"></div>
                                                </template>
                                                <!-- Risk Badge Dot -->
                                                <template x-if="ath.is_risk">
                                                    <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-red-500 border-2 border-dark rounded-full animate-pulse"></span>
                                                </template>
                                            </div>
                                            <div>
                                                <div class="font-bold text-white text-sm flex items-center gap-1.5">
                                                    <span x-text="ath.runner_name"></span>
                                                    <template x-if="ath.is_risk">
                                                        <span class="px-1.5 py-0.5 bg-red-900/40 text-red-400 border border-red-800/40 rounded text-[9px] font-mono font-bold tracking-tight uppercase">High Risk</span>
                                                    </template>
                                                </div>
                                                <div class="text-xs text-slate-500 font-mono" x-text="ath.runner_email"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Program Info -->
                                    <td class="py-4 px-6">
                                        <div>
                                            <div class="font-bold text-white text-sm" x-text="ath.program_title"></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase" :class="{
                                                    'bg-green-500/10 text-green-400 border border-green-500/20': ath.program_difficulty === 'beginner',
                                                    'bg-blue-500/10 text-blue-400 border border-blue-500/20': ath.program_difficulty === 'intermediate',
                                                    'bg-purple-500/10 text-purple-400 border border-purple-500/20': ath.program_difficulty === 'advanced',
                                                }" x-text="ath.program_difficulty"></span>
                                                <span class="text-slate-500 text-xs font-mono" x-text="'Started ' + ath.start_date_formatted"></span>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Progress Bar -->
                                    <td class="py-4 px-6">
                                        <div class="w-36">
                                            <div class="flex justify-between text-xs font-mono mb-1 text-slate-400">
                                                <span x-text="'Week ' + ath.current_week"></span>
                                                <span class="font-bold text-white" x-text="ath.progress_pct + '%'"></span>
                                            </div>
                                            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                                                <div class="h-full bg-neon transition-all duration-500" :style="'width: ' + ath.progress_pct + '%'"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Weekly KM Target -->
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <div class="text-sm font-mono font-bold text-white" x-text="ath.weekly_km_target ? (ath.weekly_km_target + ' km') : 'Not set'"></div>
                                            <button @click="openTargetModal(ath)" class="p-1.5 text-slate-500 hover:text-cyan-400 transition-colors" title="Adjust Weekly Target">
                                                <i class="fa-solid fa-pencil text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <!-- Actions -->
                                    <td class="py-4 px-6 text-right">
                                        <div class="inline-flex gap-2">
                                            <button @click="triggerChat(ath.runner_id, ath.runner_name, ath.runner_avatar)" class="px-3.5 py-2 bg-slate-900 border border-slate-700/80 hover:border-neon hover:text-neon rounded-xl text-xs text-white font-bold transition flex items-center gap-1.5">
                                                <i class="fa-solid fa-comment"></i> Chat
                                                <template x-if="ath.unread_count > 0">
                                                    <span class="ml-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                                </template>
                                            </button>
                                            <a :href="'/coach/athletes/' + ath.id" class="px-3.5 py-2 bg-neon text-dark hover:bg-neon/90 rounded-xl text-xs font-black transition flex items-center gap-1.5">
                                                <i class="fa-solid fa-clipboard-check"></i> Monitor
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <!-- Empty State -->
                            <tr x-show="filteredAthletes.length === 0">
                                <td colspan="5" class="py-12 text-center text-slate-500">
                                    <i class="fa-solid fa-user-slash text-3xl mb-3 block opacity-40"></i>
                                    <span class="font-bold">No athletes found matching criteria.</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Programs Management -->
            <div x-show="activeTab === 'programs'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Program Search & Actions -->
                <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1">
                        <!-- Search Input -->
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 group-focus-within:text-neon transition-colors">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text" x-model="programSearch" placeholder="Search program by title..." class="w-full pl-10 pr-4 py-3 bg-slate-900/60 border border-slate-700/50 rounded-2xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-neon focus:border-neon transition-all">
                        </div>
                        <!-- Difficulty Filter -->
                        <div class="relative">
                            <select x-model="programDifficultyFilter" class="w-full px-4 py-3 bg-slate-900/60 border border-slate-700/50 rounded-2xl text-sm text-white focus:outline-none focus:ring-1 focus:ring-neon focus:border-neon appearance-none cursor-pointer">
                                <option value="">All Difficulties</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                            <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button @click="openImportModal = true" class="px-5 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-slate-300 hover:border-slate-700 hover:text-white font-bold text-xs transition flex items-center gap-2">
                            <i class="fa-solid fa-file-import"></i> Import JSON
                        </button>
                        <a href="{{ route('coach.programs.create') }}" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-cyan-600 to-purple-600 text-white font-black text-xs hover:scale-[1.02] transition shadow-lg flex items-center gap-2">
                            <i class="fa-solid fa-plus"></i> Create Plan
                        </a>
                    </div>
                </div>

                <!-- Programs Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="prog in filteredPrograms" :key="prog.id">
                        <div class="bg-slate-900/40 border border-slate-800 rounded-3xl p-5 hover:border-slate-700 transition flex flex-col justify-between group">
                            <div>
                                <!-- Top Bar -->
                                <div class="flex justify-between items-start mb-4">
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider" :class="{
                                        'bg-green-500/10 text-green-400 border border-green-500/20': prog.difficulty === 'beginner',
                                        'bg-blue-500/10 text-blue-400 border border-blue-500/20': prog.difficulty === 'intermediate',
                                        'bg-purple-500/10 text-purple-400 border border-purple-500/20': prog.difficulty === 'advanced',
                                    }" x-text="prog.difficulty"></span>
                                    
                                    <!-- Publish Status Badge -->
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full" :class="prog.is_published ? 'bg-green-400 shadow-[0_0_8px_rgba(74,222,128,0.5)]' : 'bg-slate-500'"></span>
                                        <span class="text-xs font-mono uppercase font-bold" :class="prog.is_published ? 'text-green-400' : 'text-slate-400'" x-text="prog.is_published ? 'Published' : 'Draft'"></span>
                                    </div>
                                </div>

                                <!-- Title & Details -->
                                <h3 class="text-lg font-black text-white italic group-hover:text-cyan-400 transition-colors" x-text="prog.title"></h3>
                                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-400 font-mono">
                                    <span x-text="'Target: ' + prog.distance_target.toUpperCase()"></span>
                                    <span>·</span>
                                    <span x-text="prog.duration_weeks + ' Weeks'"></span>
                                    <span>·</span>
                                    <span class="font-bold text-slate-300" x-text="prog.price > 0 ? formatIDR(prog.price) : 'FREE'"></span>
                                </div>

                                <div class="mt-4 flex items-center gap-3 p-3 rounded-2xl bg-slate-900/60 border border-slate-800">
                                    <i class="fa-solid fa-graduation-cap text-cyan-400 text-lg"></i>
                                    <div>
                                        <div class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Active Students</div>
                                        <div class="text-sm font-bold text-white" x-text="prog.enrollments_count + ' Runners'"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 pt-4 border-t border-slate-800/60 flex flex-col gap-2">
                                <div class="flex gap-2">
                                    <!-- Toggle Publish Status Form -->
                                    <button @click="togglePublish(prog)" class="flex-1 py-2.5 rounded-xl font-bold text-xs text-center border transition" :class="prog.is_published ? 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white' : 'bg-green-950/20 border-green-800/40 text-green-400 hover:bg-green-950/40'">
                                        <span x-text="prog.is_published ? 'Unpublish' : 'Publish'"></span>
                                    </button>
                                    <a :href="'/coach/programs/' + prog.id + '/export-json'" class="px-3.5 py-2.5 bg-slate-900 border border-slate-800 hover:border-slate-700 hover:text-white text-slate-400 rounded-xl text-xs font-bold transition flex items-center justify-center" title="Export as JSON">
                                        <i class="fa-solid fa-file-export"></i>
                                    </a>
                                </div>
                                <div class="flex gap-2">
                                    <a :href="'/coach/programs/' + prog.id + '/edit'" class="flex-1 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-bold text-xs text-center hover:border-cyan-400/40 hover:text-cyan-300 transition">
                                        <i class="fa-solid fa-pen-to-square mr-1"></i> Edit Plan
                                    </a>
                                    <a :href="'/coach/programs/' + prog.id" class="px-4 py-2.5 bg-cyan-600/10 border border-cyan-500/20 text-cyan-400 font-bold text-xs rounded-xl hover:bg-cyan-600/20 transition flex items-center justify-center">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                    <!-- Empty State -->
                    <div x-show="filteredPrograms.length === 0" class="col-span-1 md:col-span-2 lg:col-span-3 py-16 text-center bg-slate-900/20 border border-dashed border-slate-800 rounded-3xl">
                        <i class="fa-solid fa-folder-open text-4xl text-slate-600 mb-3 block"></i>
                        <h4 class="text-white font-bold">No Programs Found</h4>
                        <p class="text-xs text-slate-500 mt-1">Start by creating a new workout program or importing a JSON template.</p>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Team Calendar -->
            <div x-show="activeTab === 'calendar'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <!-- Athlete Selector -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-black text-white italic tracking-tight uppercase">Athlete Calendars</h3>
                        <p class="text-xs text-slate-400 mt-1">Select an athlete to view and edit their calendar training schedules.</p>
                    </div>
                    <div class="relative w-full sm:w-80">
                        <select x-model="selectedCalendarAthleteId" class="w-full px-4 py-3 bg-slate-900/60 border border-slate-700/50 rounded-2xl text-sm text-white focus:outline-none focus:ring-1 focus:ring-neon focus:border-neon appearance-none cursor-pointer">
                            <option value="">-- Choose Athlete --</option>
                            <template x-for="ath in athletes" :key="ath.id">
                                <option :value="ath.id" x-text="ath.runner_name + ' (' + ath.program_title + ')'"></option>
                            </template>
                        </select>
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </span>
                    </div>
                </div>

                <!-- Calendar View Container -->
                <div>
                    <!-- Empty State -->
                    <div x-show="!selectedCalendarAthleteId" class="py-20 text-center bg-slate-900/20 border border-dashed border-slate-800 rounded-3xl">
                        <i class="fa-solid fa-calendar-days text-4xl text-slate-600 mb-3 block"></i>
                        <h4 class="text-white font-bold">No Athlete Selected</h4>
                        <p class="text-xs text-slate-500 mt-1">Please select an athlete from the dropdown above to manage their training calendar.</p>
                    </div>

                    <!-- Iframe Workspace -->
                    <template x-if="selectedCalendarAthleteId">
                        <div class="h-[800px] w-full rounded-3xl overflow-hidden border border-slate-850 bg-slate-950/80 shadow-2xl relative">
                            <!-- Loading indicator inside iframe container -->
                            <div class="absolute inset-0 bg-slate-950 flex flex-col items-center justify-center gap-3 z-0">
                                <span class="animate-spin inline-block w-8 h-8 border-4 border-cyan-400 border-t-transparent rounded-full"></span>
                                <span class="text-xs font-mono text-slate-400 uppercase tracking-widest">Loading Monitor Panel...</span>
                            </div>
                            <iframe :src="'/coach/athletes/' + selectedCalendarAthleteId + '?embed=1'" class="w-full h-full border-none relative z-10 bg-transparent" @load="iframeLoaded = true"></iframe>
                        </div>
                    </template>
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



    <!-- Adjust Weekly Target Modal -->
    <div x-show="targetModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="targetModalOpen = false"></div>
        <div class="relative bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-6 md:p-8 shadow-2xl transition-all" x-show="targetModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-xl font-black text-white italic tracking-tight mb-2">Adjust Weekly Target</h3>
            <p class="text-xs text-slate-400 mb-6">Update the weekly target volume (in kilometers) for <span class="font-bold text-cyan-400" x-text="targetAthlete?.runner_name"></span>.</p>
            
            <form @submit.prevent="submitWeeklyTarget">
                <div class="mb-6">
                    <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest font-bold">Target Volume (KM)</label>
                    <div class="relative group">
                        <input type="number" step="0.1" min="0" max="999.9" x-model="targetValue" class="w-full px-4 py-3 bg-slate-800 border border-slate-700 text-white rounded-2xl focus:outline-none focus:ring-1 focus:ring-neon focus:border-neon font-mono text-lg" placeholder="0.0">
                        <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 font-mono font-bold">KM</span>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" @click="targetModalOpen = false" class="flex-1 py-3 border border-slate-700 rounded-2xl text-slate-300 hover:bg-slate-800 transition font-bold text-sm">Cancel</button>
                    <button type="submit" class="flex-[2] py-3 bg-neon text-dark rounded-2xl font-black text-sm shadow-lg shadow-neon/15 hover:bg-neon/90 transition flex items-center justify-center gap-2">
                        <template x-if="savingTarget">
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-dark border-t-transparent rounded-full mr-2"></span>
                        </template>
                        Save Target
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import JSON Modal -->
    <div x-show="openImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="openImportModal = false"></div>
        <div class="relative bg-slate-900 border border-slate-800 w-full max-w-md rounded-3xl p-6 md:p-8 shadow-2xl transition-all" x-show="openImportModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-xl font-black text-white italic tracking-tight mb-2">Import Program JSON</h3>
            <p class="text-xs text-slate-400 mb-6">Upload a valid training plan JSON file to instantiate or extend your catalog.</p>
            
            <form @submit.prevent="submitJsonImport" enctype="multipart/form-data">
                <div class="mb-6">
                    <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest font-bold">Select JSON File</label>
                    <div class="relative border-2 border-dashed border-slate-700 hover:border-neon rounded-2xl p-6 text-center cursor-pointer transition-colors group">
                        <input type="file" accept=".json" @change="handleImportFile" class="absolute inset-0 opacity-0 cursor-pointer">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-500 group-hover:text-neon mb-2 transition-colors"></i>
                        <p class="text-xs font-bold text-slate-300" x-text="importFileName || 'Drag and drop or click to upload'"></p>
                        <p class="text-[10px] text-slate-500 mt-1 font-mono">Max size 2MB (.json)</p>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" @click="openImportModal = false" class="flex-1 py-3 border border-slate-700 rounded-2xl text-slate-300 hover:bg-slate-800 transition font-bold text-sm">Cancel</button>
                    <button type="submit" class="flex-[2] py-3 bg-neon text-dark rounded-2xl font-black text-sm shadow-lg shadow-neon/15 hover:bg-neon/90 transition flex items-center justify-center gap-2" :disabled="!importFile">
                        <template x-if="importing">
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-dark border-t-transparent rounded-full mr-2"></span>
                        </template>
                        Import Catalog
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('commandWorkspace', () => ({
            activeTab: 'athletes',
            
            // Athletes Data
            athletes: @json($mappedAthletes),

            // Programs Data
            programs: @json($mappedPrograms),

            // Search & Filters state
            athleteSearch: '',
            athleteProgramFilter: '',
            athleteRiskFilter: '',
            
            programSearch: '',
            programDifficultyFilter: '',

            // Calendar Embed Tab State
            selectedCalendarAthleteId: '',
            iframeLoaded: false,

            init() {
                // Auto-select first athlete if exists
                if (this.athletes && this.athletes.length > 0) {
                    this.selectedCalendarAthleteId = this.athletes[0].id;
                }
            },

            // Weekly Target Modal State
            targetModalOpen: false,
            targetAthlete: null,
            targetValue: '',
            savingTarget: false,

            // Import JSON State
            openImportModal: false,
            importFile: null,
            importFileName: '',
            importing: false,

            get filteredAthletes() {
                return this.athletes.filter(ath => {
                    const matchesSearch = ath.runner_name.toLowerCase().includes(this.athleteSearch.toLowerCase()) || 
                                          ath.runner_email.toLowerCase().includes(this.athleteSearch.toLowerCase());
                    const matchesProgram = !this.athleteProgramFilter || String(ath.program_id) === String(this.athleteProgramFilter);
                    let matchesRisk = true;
                    if (this.athleteRiskFilter === 'risk') {
                        matchesRisk = ath.is_risk;
                    } else if (this.athleteRiskFilter === 'needs_review') {
                        matchesRisk = ath.needs_review;
                    }
                    return matchesSearch && matchesProgram && matchesRisk;
                });
            },

            get filteredPrograms() {
                return this.programs.filter(prog => {
                    const matchesSearch = prog.title.toLowerCase().includes(this.programSearch.toLowerCase());
                    const matchesDifficulty = !this.programDifficultyFilter || prog.difficulty === this.programDifficultyFilter;
                    return matchesSearch && matchesDifficulty;
                });
            },

            formatIDR(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(value);
            },

            // Trigger Chat widget
            triggerChat(runnerId, name, avatar) {
                if (window.openChat) {
                    window.openChat(runnerId, name, avatar);
                } else {
                    window.location.href = `/chat/${runnerId}`;
                }
            },

            // Toggle program publish status using AJAX
            togglePublish(prog) {
                const url = prog.is_published ? prog.unpublish_url : prog.publish_url;
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => {
                    prog.is_published = !prog.is_published;
                    this.showToast(prog.is_published ? 'Program published successfully!' : 'Program unpublished.');
                })
                .catch(err => {
                    console.error(err);
                    alert('Error changing program status.');
                });
            },

            // Open target modal
            openTargetModal(athlete) {
                this.targetAthlete = athlete;
                this.targetValue = athlete.weekly_km_target || '';
                this.targetModalOpen = true;
            },

            // Submit Target adjusted
            submitWeeklyTarget() {
                if (!this.targetAthlete) return;
                this.savingTarget = true;
                
                const url = `/coach/athletes/\${this.targetAthlete.id}/update-weekly-target`;
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        weekly_km_target: this.targetValue
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.targetAthlete.weekly_km_target = data.weekly_km_target;
                        const index = this.athletes.findIndex(a => a.id === this.targetAthlete.id);
                        if (index !== -1) {
                            this.athletes[index].weekly_km_target = data.weekly_km_target;
                        }
                        this.targetModalOpen = false;
                        this.showToast('Weekly target updated successfully!');
                    } else {
                        alert(data.message || 'Error updating target.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Failed to save weekly target.');
                })
                .finally(() => {
                    this.savingTarget = false;
                });
            },

            // Handle import file selection
            handleImportFile(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    this.importFile = files[0];
                    this.importFileName = this.importFile.name;
                }
            },

            // Submit imported JSON file via AJAX
            submitJsonImport() {
                if (!this.importFile) return;
                this.importing = true;
                
                const formData = new FormData();
                formData.append('json_file', this.importFile);
                
                fetch('{{ route("coach.programs.import-and-save") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.openImportModal = false;
                        this.importFile = null;
                        this.importFileName = '';
                        this.showToast('Program imported successfully as draft!');
                        
                        this.programs.unshift({
                            id: data.program.id,
                            title: data.program.title,
                            difficulty: data.program.difficulty,
                            distance_target: data.program.distance_target,
                            price: parseFloat(data.program.price),
                            duration_weeks: data.program.duration_weeks,
                            is_published: data.program.is_published,
                            enrollments_count: 0,
                            publish_url: `/coach/programs/\${data.program.id}/publish`,
                            unpublish_url: `/coach/programs/\${data.program.id}/unpublish`,
                        });
                    } else {
                        alert(data.message || 'Error importing program.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Import failed. Make sure the JSON format is valid.');
                })
                .finally(() => {
                    this.importing = false;
                });
            },

            // Helper to show custom toast
            showToast(message) {
                let toast = document.getElementById('ph-custom-toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'ph-custom-toast';
                    toast.className = 'fixed bottom-5 left-6 z-[100] px-5 py-3.5 bg-slate-900 border border-cyan-500/30 text-white text-xs font-black rounded-2xl shadow-xl shadow-cyan-500/10 flex items-center gap-2.5 transition-all duration-300 transform translate-y-10 opacity-0';
                    document.body.appendChild(toast);
                }
                toast.innerHTML = `<i class="fa-solid fa-circle-check text-neon"></i> \${message}`;
                
                setTimeout(() => {
                    toast.classList.remove('translate-y-10', 'opacity-0');
                }, 50);

                setTimeout(() => {
                    toast.classList.add('translate-y-10', 'opacity-0');
                }, 3000);
            }
        }));
    });
</script>
@endpush
