@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Coach Dashboard')

@push('styles')
    <script>
        if (typeof tailwind !== 'undefined' && tailwind && tailwind.config) {
            tailwind.config.theme = tailwind.config.theme || {};
            tailwind.config.theme.extend = tailwind.config.theme.extend || {};
            tailwind.config.theme.extend.colors = {
                ...(tailwind.config.theme.extend.colors || {}),
                neon: {
                    DEFAULT: '#ccff00',
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    yellow: '#eab308',
                }
            };
        }
    </script>
@endpush

@section('content')
<div id="coach-dashboard-app" x-data="commandWorkspace()" class="max-w-7xl mx-auto mt-[90px] pb-10 px-4 md:px-8 font-sans space-y-8">
    
    <!-- Hero Section -->
    <div class="border-b border-slate-800 pb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <p class="text-xs text-slate-400 mb-1">Coach Workspace</p>
            <h1 class="text-2xl font-bold text-white">
                {{ auth()->user()->name }}
            </h1>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('coach.programs.create') }}" class="px-4 py-2 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 hover:text-white transition font-medium text-xs">
                + Create Program
            </a>
            <a href="{{ route('coach.athletes.index') }}" class="px-4 py-2 rounded-md bg-neon text-dark font-bold hover:brightness-110 transition text-xs">
                Manage Athletes
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Wallet Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400">Wallet Balance</span>
                <h3 class="text-xl font-bold text-white font-mono mt-1">Rp {{ number_format($walletBalance, 0, ',', '.') }}</h3>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400 flex items-center justify-between">
                <span>Available to withdraw</span>
                <a href="{{ route('coach.withdrawals.index') }}" class="text-neon hover:underline font-medium text-xs">Withdraw &rarr;</a>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400">Lifetime Revenue</span>
                <h3 class="text-xl font-bold text-white font-mono mt-1">Rp {{ number_format($totalEarnings, 0, ',', '.') }}</h3>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400">
                Total earnings from coaching
            </div>
        </div>

        <!-- My Programs -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400">Created Programs</span>
                <h3 class="text-xl font-bold text-white font-mono mt-1">{{ auth()->user()->programs()->count() }}</h3>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400">
                Programs in marketplace
            </div>
        </div>

        <!-- Total Students -->
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 flex flex-col justify-between">
            <div>
                <span class="text-xs text-slate-400">Total Students</span>
                <h3 class="text-xl font-bold text-white font-mono mt-1">{{ \App\Models\ProgramEnrollment::whereHas('program', function($q) { $q->where('coach_id', auth()->id()); })->count() }}</h3>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-800 text-xs text-slate-400">
                Active & completed runners
            </div>
        </div>
    </div>

    <!-- Metric Snapshot Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs text-slate-400">Due Today</div>
            <div class="mt-1 text-lg font-bold text-white font-mono">{{ number_format((int) ($coachMetrics['due_today'] ?? 0)) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs text-slate-400">Needs Review</div>
            <div class="mt-1 text-lg font-bold text-white font-mono">{{ number_format((int) ($coachMetrics['needs_review'] ?? 0)) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs text-slate-400">Overdue</div>
            <div class="mt-1 text-lg font-bold text-white font-mono">{{ number_format((int) ($coachMetrics['overdue'] ?? 0)) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
            <div class="text-xs text-slate-400">Unread Chats</div>
            <div class="mt-1 text-lg font-bold text-white font-mono">{{ number_format((int) ($coachMetrics['unread_chats'] ?? 0)) }}</div>
        </div>
        <div class="bg-slate-900 border border-slate-800 rounded-lg p-4 col-span-2">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs text-slate-400">Weekly Completion</div>
                    <div class="mt-1 text-lg font-bold text-white font-mono">{{ number_format((int) ($coachMetrics['weekly_completion_rate'] ?? 0)) }}%</div>
                    <div class="mt-0.5 text-xs text-slate-400">
                        {{ number_format((int) ($coachMetrics['weekly_completed'] ?? 0)) }}/{{ number_format((int) ($coachMetrics['weekly_scheduled'] ?? 0)) }} sessions · {{ number_format((int) ($coachMetrics['active_athletes'] ?? 0)) }} athletes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Athletes & Programs Workspace -->
    <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6 space-y-6">
        
        <!-- Header & Tabs -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-white">Athletes & Programs</h2>
                <p class="text-xs text-slate-400 mt-0.5">Kelola atlet aktif dan katalog program latihan.</p>
            </div>
            
            <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-md border border-slate-800">
                <button @click="activeTab = 'athletes'" :class="activeTab === 'athletes' ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white font-medium'" class="px-3.5 py-1.5 rounded-md text-xs transition">
                    Athletes ({{ count($mappedAthletes ?? []) }})
                </button>
                <button @click="activeTab = 'programs'" :class="activeTab === 'programs' ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white font-medium'" class="px-3.5 py-1.5 rounded-md text-xs transition">
                    Programs ({{ count($mappedPrograms ?? []) }})
                </button>
            </div>
        </div>

        <!-- Tab 1: Athletes Management -->
        <div x-show="activeTab === 'athletes'" class="space-y-4">
            <!-- Search & Filters -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <input type="text" x-model="athleteSearch" placeholder="Cari atlet berdasarkan nama atau email..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-md text-xs text-white placeholder-slate-400 focus:border-slate-600 outline-none">
                </div>
                <div>
                    <select x-model="athleteProgramFilter" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-md text-xs text-white focus:border-slate-600 outline-none">
                        <option value="">Semua Program</option>
                        @foreach($myPrograms as $p)
                            <option value="{{ $p->id }}">{{ $p->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select x-model="athleteRiskFilter" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-md text-xs text-white focus:border-slate-600 outline-none">
                        <option value="">Semua Status</option>
                        <option value="risk">Atlet Risiko Tinggi</option>
                        <option value="needs_review">Perlu Evaluasi</option>
                    </select>
                </div>
            </div>

            <!-- Athletes Table -->
            <div class="overflow-x-auto rounded-lg border border-slate-800">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-950 text-slate-400 text-[10px] font-mono uppercase tracking-wider border-b border-slate-800">
                            <th class="py-3 px-4">Atlet / Kontak</th>
                            <th class="py-3 px-4">Program Latihan</th>
                            <th class="py-3 px-4">Progress Mingguan</th>
                            <th class="py-3 px-4">Target Volume</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-300 divide-y divide-slate-800/80 font-sans">
                        <template x-for="ath in filteredAthletes" :key="ath.id">
                            <tr class="hover:bg-slate-800/40 transition">
                                <!-- Avatar & Name -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative shrink-0">
                                            <template x-if="ath.runner_avatar">
                                                <img :src="ath.runner_avatar" alt="" class="w-8 h-8 rounded-full object-cover border border-slate-700">
                                            </template>
                                            <template x-if="!ath.runner_avatar">
                                                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xs" x-text="ath.runner_name.substring(0, 1).toUpperCase()"></div>
                                            </template>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-white text-xs flex items-center gap-1.5">
                                                <span class="truncate" x-text="ath.runner_name"></span>
                                                <template x-if="ath.is_risk">
                                                    <span class="px-1.5 py-0.5 bg-red-900 text-red-300 border border-red-800 rounded text-[10px] font-medium">Risk</span>
                                                </template>
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono truncate" x-text="ath.runner_email"></div>
                                        </div>
                                    </div>
                                </td>
                                <!-- Program Info -->
                                <td class="py-3 px-4">
                                    <div>
                                        <div class="font-medium text-white text-xs" x-text="ath.program_title"></div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium uppercase border" :class="{
                                                'bg-emerald-900/60 text-emerald-300 border-emerald-800': ath.program_difficulty === 'beginner',
                                                'bg-sky-900/60 text-sky-300 border-sky-800': ath.program_difficulty === 'intermediate',
                                                'bg-purple-900/60 text-purple-300 border-purple-800': ath.program_difficulty === 'advanced',
                                            }" x-text="ath.program_difficulty"></span>
                                            <span class="text-slate-400 text-[11px]" x-text="'Mulai ' + ath.start_date_formatted"></span>
                                        </div>
                                    </div>
                                </td>
                                <!-- Progress Bar -->
                                <td class="py-3 px-4">
                                    <div class="w-32">
                                        <div class="flex justify-between text-[11px] font-mono mb-1 text-slate-400">
                                            <span x-text="'Week ' + ath.current_week"></span>
                                            <span class="font-bold text-white" x-text="ath.progress_pct + '%'"></span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-800 rounded-sm overflow-hidden">
                                            <div class="h-full bg-neon transition-all" :style="'width: ' + ath.progress_pct + '%'"></div>
                                        </div>
                                    </div>
                                </td>
                                <!-- Weekly KM Target -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-mono font-medium text-white" x-text="ath.weekly_km_target ? (ath.weekly_km_target + ' km') : '-'"></span>
                                        <button @click="openTargetModal(ath)" class="text-slate-400 hover:text-white transition text-xs" title="Ubah Target">
                                            [Ubah]
                                        </button>
                                    </div>
                                </td>
                                <!-- Actions -->
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex gap-1.5">
                                        <button @click="triggerChat(ath.runner_id, ath.runner_name, ath.runner_avatar)" class="px-2.5 py-1 bg-slate-800 border border-slate-700 hover:bg-slate-700 rounded-md text-xs text-slate-200 hover:text-white font-medium transition">
                                            Chat
                                            <template x-if="ath.unread_count > 0">
                                                <span class="ml-1 px-1 rounded bg-red-800 text-red-200 text-[9px]" x-text="ath.unread_count"></span>
                                            </template>
                                        </button>
                                        <a :href="'/coach/athletes/' + ath.id" class="px-2.5 py-1 bg-neon text-dark hover:brightness-110 rounded-md text-xs font-bold transition">
                                            Review
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <!-- Empty State -->
                        <tr x-show="filteredAthletes.length === 0">
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Tidak ada atlet yang cocok dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 2: Programs Management -->
        <div x-show="activeTab === 'programs'" class="space-y-4">
            <!-- Program Search & Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 flex-1">
                    <div>
                        <input type="text" x-model="programSearch" placeholder="Cari nama program..." class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-md text-xs text-white placeholder-slate-400 focus:border-slate-600 outline-none">
                    </div>
                    <div>
                        <select x-model="programDifficultyFilter" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-md text-xs text-white focus:border-slate-600 outline-none">
                            <option value="">Semua Tingkat</option>
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <button @click="openImportModal = true" class="px-3.5 py-2 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 hover:text-white font-medium text-xs transition">
                        Import JSON
                    </button>
                    <a href="{{ route('coach.programs.create') }}" class="px-3.5 py-2 rounded-md bg-neon text-dark font-bold text-xs hover:brightness-110 transition">
                        + Buat Program
                    </a>
                </div>
            </div>

            <!-- Programs Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <template x-for="prog in filteredPrograms" :key="prog.id">
                    <div class="bg-slate-950 border border-slate-800 rounded-lg p-4 flex flex-col justify-between space-y-4">
                        <div>
                            <!-- Top Bar -->
                            <div class="flex justify-between items-center mb-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium uppercase border" :class="{
                                    'bg-emerald-900/60 text-emerald-300 border-emerald-800': prog.difficulty === 'beginner',
                                    'bg-sky-900/60 text-sky-300 border-sky-800': prog.difficulty === 'intermediate',
                                    'bg-purple-900/60 text-purple-300 border-purple-800': prog.difficulty === 'advanced',
                                }" x-text="prog.difficulty"></span>
                                
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium border" :class="prog.is_published ? 'bg-emerald-900 text-emerald-300 border-emerald-800' : 'bg-slate-800 text-slate-400 border-slate-700'" x-text="prog.is_published ? 'Published' : 'Draft'"></span>
                            </div>

                            <!-- Title & Details -->
                            <h3 class="text-sm font-semibold text-white truncate" x-text="prog.title"></h3>
                            <div class="mt-1 flex flex-wrap gap-x-2 text-xs text-slate-400">
                                <span x-text="'Target: ' + prog.distance_target.toUpperCase()"></span>
                                <span>·</span>
                                <span x-text="prog.duration_weeks + ' Minggu'"></span>
                                <span>·</span>
                                <span class="font-medium text-slate-300" x-text="prog.price > 0 ? formatIDR(prog.price) : 'Gratis'"></span>
                            </div>

                            <div class="mt-3 p-2.5 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-between text-xs">
                                <span class="text-slate-400">Peserta Aktif:</span>
                                <span class="font-medium text-white" x-text="prog.enrollments_count + ' Atlet'"></span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-3 border-t border-slate-800 flex items-center justify-between gap-2">
                            <button @click="togglePublish(prog)" class="px-2.5 py-1.5 rounded-md text-xs font-medium border transition" :class="prog.is_published ? 'bg-slate-900 border-slate-700 text-slate-300 hover:text-white' : 'bg-emerald-900 border-emerald-800 text-emerald-200 hover:bg-emerald-800'">
                                <span x-text="prog.is_published ? 'Unpublish' : 'Publish'"></span>
                            </button>
                            <div class="flex items-center gap-1.5">
                                <a :href="'/coach/programs/' + prog.id + '/export-json'" class="px-2 py-1.5 bg-slate-900 border border-slate-700 text-slate-300 hover:text-white rounded-md text-xs font-medium transition" title="Export JSON">
                                    JSON
                                </a>
                                <a :href="'/coach/programs/' + prog.id + '/edit'" class="px-2.5 py-1.5 bg-slate-800 border border-slate-700 text-slate-200 hover:text-white font-medium text-xs rounded-md transition">
                                    Edit
                                </a>
                                <a :href="'/coach/programs/' + prog.id" class="px-2.5 py-1.5 bg-slate-800 border border-slate-700 text-cyan-400 hover:text-cyan-300 font-medium text-xs rounded-md transition">
                                    Lihat
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
                <!-- Empty State -->
                <div x-show="filteredPrograms.length === 0" class="col-span-full py-8 text-center border border-dashed border-slate-800 rounded-lg">
                    <p class="text-xs text-slate-400">Belum ada program latihan yang dibuat.</p>
                </div>
            </div>
        </div>

    </div>

    <!-- Two-column: Today Queue & Activity Sidebar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Queue & Activity (2 cols on desktop) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Today Queue -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4 mb-4 pb-3 border-b border-slate-800">
                    <div>
                        <h3 class="text-base font-semibold text-white">Today Queue</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Antrean prioritas: atlet risiko, overdue, perlu evaluasi, dan pesan baru.</p>
                    </div>
                    <a href="{{ route('coach.athletes.index') }}" class="text-xs text-neon hover:underline font-medium">Lihat Semua &rarr;</a>
                </div>

                @if(empty($queueItems ?? []))
                    <div class="py-8 text-center border border-dashed border-slate-800 rounded-md">
                        <p class="text-xs text-slate-400">Antrean bersih. Tidak ada atlet yang butuh tindakan segera hari ini.</p>
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach(($queueItems ?? []) as $it)
                            <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0">
                                    @if(!empty($it['runner_avatar']))
                                        <img src="{{ $it['runner_avatar'] }}" alt="{{ $it['runner_name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                            {{ strtoupper(mb_substr($it['runner_name'] ?? 'R', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2">
                                            <span class="text-white font-medium text-xs truncate">{{ $it['runner_name'] }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $it['program_title'] }}</span>
                                        </div>
                                        <div class="mt-0.5 text-xs text-slate-300">
                                            <span class="font-medium">{{ $it['date_label'] }}</span>
                                            <span class="text-slate-500">·</span>
                                            <span class="capitalize">{{ str_replace('_', ' ', $it['type'] ?? 'run') }}</span>
                                            @if(!empty($it['distance']))
                                                <span class="text-slate-500">·</span>
                                                <span>{{ $it['distance'] }} km</span>
                                            @endif
                                        </div>
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            @if(!empty($it['flags']['risk']))
                                                <span class="px-2 py-0.5 rounded bg-red-900 text-red-300 border border-red-800 text-[10px] font-medium">Risk</span>
                                            @endif
                                            @if(!empty($it['flags']['overdue']))
                                                <span class="px-2 py-0.5 rounded bg-red-900 text-red-300 border border-red-800 text-[10px] font-medium">Overdue</span>
                                            @endif
                                            @if(!empty($it['flags']['needs_review']))
                                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700 text-[10px] font-medium">Needs Review</span>
                                            @endif
                                            @if(!empty($it['flags']['due_today']))
                                                <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 border border-slate-700 text-[10px] font-medium">Due Today</span>
                                            @endif
                                            @if(!empty($it['unread_count']))
                                                <span class="px-2 py-0.5 rounded bg-amber-900 text-amber-300 border border-amber-800 text-[10px] font-medium">{{ (int) $it['unread_count'] }} Unread</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="{{ route('chat.show', $it['runner_id']) }}" class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:text-white font-medium text-xs transition">Chat</a>
                                    @if(!empty($it['enrollment_id']))
                                        <a href="{{ route('coach.athletes.show', $it['enrollment_id']) }}" class="px-2.5 py-1 rounded-md bg-neon text-dark font-bold text-xs hover:brightness-110 transition">Review</a>
                                    @else
                                        <a href="{{ route('coach.athletes.index') }}" class="px-2.5 py-1 rounded-md bg-neon text-dark font-bold text-xs hover:brightness-110 transition">Buka</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Recent Activity -->
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6">
                <div class="flex items-center justify-between gap-4 mb-4 pb-3 border-b border-slate-800">
                    <div>
                        <h3 class="text-base font-semibold text-white">Recent Activity</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Aktivitas lari atlet yang selesai dalam 7 hari terakhir.</p>
                    </div>
                    <a href="{{ route('chat.index') }}" class="text-xs text-slate-300 hover:text-white font-medium">Buka Chat &rarr;</a>
                </div>

                @if(empty($recentActivities ?? []))
                    <div class="py-8 text-center border border-dashed border-slate-800 rounded-md">
                        <p class="text-xs text-slate-400">Belum ada aktivitas lari yang diselesaikan baru-baru ini.</p>
                    </div>
                @else
                    <div class="space-y-2.5">
                        @foreach(($recentActivities ?? []) as $a)
                            <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3 min-w-0">
                                    @if(!empty($a['runner_avatar']))
                                        <img src="{{ $a['runner_avatar'] }}" alt="{{ $a['runner_name'] }}" class="w-8 h-8 rounded-full object-cover border border-slate-700 shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-white font-bold text-xs shrink-0">
                                            {{ strtoupper(mb_substr($a['runner_name'] ?? 'R', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-x-2">
                                            <span class="text-white font-medium text-xs truncate">{{ $a['runner_name'] }}</span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ $a['program_title'] }}</span>
                                        </div>
                                        <div class="mt-0.5 text-xs text-slate-300">
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
                                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                                            @if(!empty($a['rpe']))
                                                <span class="px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px] text-slate-200 font-medium">RPE {{ (int) $a['rpe'] }}</span>
                                            @endif
                                            @if(!empty($a['feeling']))
                                                <span class="px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px] text-slate-200 font-medium capitalize">{{ $a['feeling'] }}</span>
                                            @endif
                                            @if(empty($a['coach_rating']))
                                                <span class="px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700 text-slate-400 text-[10px] font-medium">Belum Direview</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded bg-emerald-900 text-emerald-300 border border-emerald-800 text-[10px] font-medium">Sudah Direview</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="{{ route('chat.show', $a['runner_id']) }}" class="px-2.5 py-1 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:text-white font-medium text-xs transition">Chat</a>
                                    @if(!empty($a['enrollment_id']))
                                        <a href="{{ route('coach.athletes.show', $a['enrollment_id']) }}" class="px-2.5 py-1 rounded-md bg-neon text-dark font-bold text-xs hover:brightness-110 transition">Review</a>
                                    @else
                                        <a href="{{ route('coach.athletes.index') }}" class="px-2.5 py-1 rounded-md bg-neon text-dark font-bold text-xs hover:brightness-110 transition">Buka</a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions Sidebar (1 col on desktop) -->
        <div class="space-y-6">
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                <h3 class="text-sm font-semibold text-white mb-1">Coach Command Center</h3>
                <p class="text-xs text-slate-400 mb-4">Akses monitor atlet, evaluasi log latihan, dan update catatan bimbingan.</p>
                <a href="{{ route('coach.athletes.index') }}" class="block w-full py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white border border-slate-700 font-medium text-center rounded-md text-xs transition">
                    Buka Athlete Hub
                </a>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 space-y-3">
                <div class="text-xs text-slate-400 font-mono uppercase">Snapshot Tim</div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="p-3 rounded-md bg-slate-950 border border-slate-800">
                        <div class="text-[10px] text-slate-400 uppercase">Risiko</div>
                        <div class="mt-0.5 text-base font-bold text-white font-mono">{{ number_format((int) ($coachMetrics['risk'] ?? 0)) }}</div>
                    </div>
                    <div class="p-3 rounded-md bg-slate-950 border border-slate-800">
                        <div class="text-[10px] text-slate-400 uppercase">Aktif</div>
                        <div class="mt-0.5 text-base font-bold text-white font-mono">{{ number_format((int) ($coachMetrics['active_athletes'] ?? 0)) }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                <h3 class="text-xs font-semibold text-white uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">Pintasan Menu</h3>
                <div class="space-y-1.5 text-xs">
                    <a href="{{ route('coach.programs.index') }}" class="flex items-center justify-between p-2.5 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white transition">
                        <span>Daftar Program Latihan</span>
                        <span class="text-slate-500">&rarr;</span>
                    </a>
                    <a href="{{ route('coach.finance.index') }}" class="flex items-center justify-between p-2.5 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white transition">
                        <span>Dashboard Keuangan</span>
                        <span class="text-slate-500">&rarr;</span>
                    </a>
                    <a href="{{ route('coach.withdrawals.index') }}" class="flex items-center justify-between p-2.5 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white transition">
                        <span>Riwayat Penarikan Dana</span>
                        <span class="text-slate-500">&rarr;</span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center justify-between p-2.5 rounded-md bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-300 hover:text-white transition">
                        <span>Pengaturan Profil</span>
                        <span class="text-slate-500">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Adjust Weekly Target Modal -->
    <div x-show="targetModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="absolute inset-0 bg-black/70" @click="targetModalOpen = false"></div>
        <div class="relative bg-slate-900 border border-slate-800 w-full max-w-md rounded-lg p-6 shadow-2xl transition-all" x-show="targetModalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-base font-bold text-white mb-1">Sesuaikan Target Mingguan</h3>
            <p class="text-xs text-slate-400 mb-4">Ubah volume target mingguan (kilometer) untuk <span class="font-semibold text-white" x-text="targetAthlete?.runner_name"></span>.</p>
            
            <form @submit.prevent="submitWeeklyTarget" class="space-y-4">
                <div>
                    <label class="block text-xs font-mono text-slate-300 mb-1 font-medium">Target Volume (KM)</label>
                    <div class="relative">
                        <input type="number" step="0.1" min="0" max="999.9" x-model="targetValue" class="w-full px-3 py-2 bg-slate-950 border border-slate-800 text-white rounded-md focus:border-slate-600 outline-none font-mono text-sm" placeholder="0.0">
                        <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 font-mono text-xs">KM</span>
                    </div>
                </div>
                
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="targetModalOpen = false" class="flex-1 py-2 border border-slate-700 rounded-md text-slate-300 hover:bg-slate-800 transition font-medium text-xs">Batal</button>
                    <button type="submit" class="flex-1 py-2 bg-neon text-dark rounded-md font-bold text-xs hover:brightness-110 transition flex items-center justify-center gap-1.5">
                        <template x-if="savingTarget">
                            <span class="animate-spin inline-block w-3 h-3 border-2 border-dark border-t-transparent rounded-full mr-1"></span>
                        </template>
                        Simpan Target
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import JSON Modal -->
    <div x-show="openImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="absolute inset-0 bg-black/70" @click="openImportModal = false"></div>
        <div class="relative bg-slate-900 border border-slate-800 w-full max-w-md rounded-lg p-6 shadow-2xl transition-all" x-show="openImportModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <h3 class="text-base font-bold text-white mb-1">Import Program JSON</h3>
            <p class="text-xs text-slate-400 mb-4">Upload file training plan JSON yang valid untuk menambahkan katalog program baru.</p>
            
            <form @submit.prevent="submitJsonImport" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-xs font-mono text-slate-300 mb-1 font-medium">Pilih File JSON</label>
                    <div class="relative border border-dashed border-slate-700 hover:border-slate-500 rounded-md p-6 text-center cursor-pointer transition">
                        <input type="file" accept=".json" @change="handleImportFile" class="absolute inset-0 opacity-0 cursor-pointer">
                        <p class="text-xs font-medium text-slate-300" x-text="importFileName || 'Klik atau drag file JSON ke sini'"></p>
                        <p class="text-[11px] text-slate-400 mt-1 font-mono">Maksimal 2MB (.json)</p>
                    </div>
                </div>
                
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="openImportModal = false" class="flex-1 py-2 border border-slate-700 rounded-md text-slate-300 hover:bg-slate-800 transition font-medium text-xs">Batal</button>
                    <button type="submit" class="flex-1 py-2 bg-neon text-dark rounded-md font-bold text-xs hover:brightness-110 transition flex items-center justify-center gap-1.5" :disabled="!importFile">
                        <template x-if="importing">
                            <span class="animate-spin inline-block w-3 h-3 border-2 border-dark border-t-transparent rounded-full mr-1"></span>
                        </template>
                        Import Program
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

            triggerChat(runnerId, name, avatar) {
                if (window.openChat) {
                    window.openChat(runnerId, name, avatar);
                } else {
                    window.location.href = `/chat/${runnerId}`;
                }
            },

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
                    this.showToast(prog.is_published ? 'Program berhasil dipublikasikan.' : 'Program diubah ke draft.');
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal mengubah status program.');
                });
            },

            openTargetModal(athlete) {
                this.targetAthlete = athlete;
                this.targetValue = athlete.weekly_km_target || '';
                this.targetModalOpen = true;
            },

            submitWeeklyTarget() {
                if (!this.targetAthlete) return;
                this.savingTarget = true;
                
                const url = `/coach/athletes/${this.targetAthlete.id}/update-weekly-target`;
                
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
                        this.showToast('Target mingguan berhasil diperbarui.');
                    } else {
                        alert(data.message || 'Gagal menyimpan target.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal menyimpan target mingguan.');
                })
                .finally(() => {
                    this.savingTarget = false;
                });
            },

            handleImportFile(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    this.importFile = files[0];
                    this.importFileName = this.importFile.name;
                }
            },

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
                        this.showToast('Program berhasil diimpor sebagai draft.');
                        
                        this.programs.unshift({
                            id: data.program.id,
                            title: data.program.title,
                            difficulty: data.program.difficulty,
                            distance_target: data.program.distance_target,
                            price: parseFloat(data.program.price),
                            duration_weeks: data.program.duration_weeks,
                            is_published: data.program.is_published,
                            enrollments_count: 0,
                            publish_url: `/coach/programs/${data.program.id}/publish`,
                            unpublish_url: `/coach/programs/${data.program.id}/unpublish`,
                        });
                    } else {
                        alert(data.message || 'Gagal mengimpor program.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Import gagal. Pastikan format JSON sesuai template.');
                })
                .finally(() => {
                    this.importing = false;
                });
            },

            showToast(message) {
                let toast = document.getElementById('ph-custom-toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'ph-custom-toast';
                    toast.className = 'fixed bottom-5 left-6 z-[100] px-4 py-2.5 bg-slate-900 border border-slate-700 text-white text-xs font-semibold rounded-md shadow-xl flex items-center gap-2 transition-all duration-200 transform translate-y-6 opacity-0';
                    document.body.appendChild(toast);
                }
                toast.textContent = message;
                
                setTimeout(() => {
                    toast.classList.remove('translate-y-6', 'opacity-0');
                }, 50);

                setTimeout(() => {
                    toast.classList.add('translate-y-6', 'opacity-0');
                }, 3000);
            }
        }));
    });
</script>
@endpush
