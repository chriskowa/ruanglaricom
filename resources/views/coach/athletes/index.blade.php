@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'My Athletes')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-7xl mx-auto">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-400 flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <p class="text-neon font-mono text-xs font-bold tracking-widest uppercase flex items-center gap-1.5 mb-1">
                    <i class="fa-solid fa-chart-line"></i> Monitoring & Graded Performance
                </p>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">My Athletes</h1>
            </div>
            <div class="flex flex-wrap sm:flex-nowrap gap-2 items-center w-full sm:w-auto">
                <button onclick="openEnrollModal()" class="px-3.5 py-2.5 rounded-xl bg-neon text-dark font-extrabold hover:bg-neon/90 transition shadow-lg shadow-neon/20 flex items-center justify-center gap-1.5 text-xs flex-1 sm:flex-none">
                    <i class="fa-solid fa-user-plus text-xs"></i>
                    <span>Daftar</span>
                </button>
                <button onclick="openImportModal()" class="px-3.5 py-2.5 rounded-xl bg-slate-800 text-slate-200 border border-slate-700/80 font-bold hover:bg-slate-700 transition flex items-center justify-center gap-1.5 text-xs flex-1 sm:flex-none">
                    <i class="fa-solid fa-file-import text-xs text-slate-400"></i>
                    <span>Import</span>
                </button>
                <!-- Mobile Filter Trigger -->
                <button onclick="document.getElementById('mobileFilterSheet').classList.remove('translate-y-full')" class="md:hidden px-3.5 py-2.5 rounded-xl bg-slate-800/90 border border-slate-700/90 text-neon flex items-center justify-center gap-1.5 font-bold text-xs">
                    <i class="fa-solid fa-sliders text-xs"></i>
                    <span>FILTER</span>
                </button>
            </div>
        </div>

        <!-- Filter Section (Desktop) -->
        <div class="hidden md:block mb-6 bg-slate-900/60 backdrop-blur-md rounded-2xl p-5 border border-slate-800/90 shadow-lg">
            <form action="{{ route('coach.athletes.index') }}" method="GET" class="space-y-4">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <div class="grid grid-cols-12 gap-4 items-end">
                    <div class="col-span-3">
                        <label for="search" class="block text-[11px] font-mono text-slate-400 mb-1.5 uppercase tracking-wider font-bold">Search Runner</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none transition-colors group-focus-within:text-neon">
                                <i class="fa-solid fa-magnifying-glass text-slate-500 group-focus-within:text-neon text-xs"></i>
                            </div>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau email..." 
                                class="w-full bg-slate-800/80 border border-slate-700/80 text-white text-xs rounded-xl focus:ring-neon focus:border-neon block p-2.5 pl-9 placeholder-slate-500 transition-all">
                        </div>
                    </div>
                    <div class="col-span-3">
                        <label for="program_id" class="block text-[11px] font-mono text-slate-400 mb-1.5 uppercase tracking-wider font-bold">Filter Program</label>
                        <div class="relative">
                            <select name="program_id" class="w-full bg-slate-800/80 border border-slate-700/80 text-white text-xs rounded-xl focus:ring-neon focus:border-neon block p-2.5 appearance-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                                <option value="">Semua Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                                        {{ $program->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-3">
                        <label for="status" class="block text-[11px] font-mono text-slate-400 mb-1.5 uppercase tracking-wider font-bold">Status Program</label>
                        <div class="relative">
                            <select name="status" class="w-full bg-slate-800/80 border border-slate-700/80 text-white text-xs rounded-xl focus:ring-neon focus:border-neon block p-2.5 appearance-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non-aktif (Expired)</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="purchased" {{ request('status') == 'purchased' ? 'selected' : '' }}>Kantong Program</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-3">
                        <label for="sort_by" class="block text-[11px] font-mono text-slate-400 mb-1.5 uppercase tracking-wider font-bold">Urutkan</label>
                        <div class="relative">
                            <select name="sort_by" class="w-full bg-slate-800/80 border border-slate-700/80 text-white text-xs rounded-xl focus:ring-neon focus:border-neon block p-2.5 appearance-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                                <option value="latest" {{ $sortBy == 'latest' ? 'selected' : '' }}>Pendaftaran Terbaru</option>
                                <option value="vdot_desc" {{ $sortBy == 'vdot_desc' ? 'selected' : '' }}>VDOT Tertinggi</option>
                                <option value="vdot_asc" {{ $sortBy == 'vdot_asc' ? 'selected' : '' }}>VDOT Terendah</option>
                                <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Nama Runner</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-4 items-end pt-3 border-t border-slate-800/80">
                    <div class="col-span-3">
                        <label class="block text-[11px] font-mono text-slate-400 mb-1.5 uppercase tracking-wider font-bold">Range VDOT (Min - Max)</label>
                        <div class="flex gap-2">
                            <input type="number" name="vdot_min" value="{{ $vdotMin }}" placeholder="Min" step="0.1"
                                class="w-1/2 bg-slate-800/80 border border-slate-700/80 text-white text-xs rounded-xl focus:ring-neon focus:border-neon block p-2.5 placeholder-slate-500 transition-all">
                            <input type="number" name="vdot_max" value="{{ $vdotMax }}" placeholder="Max" step="0.1"
                                class="w-1/2 bg-slate-800/80 border border-slate-700/80 text-white text-xs rounded-xl focus:ring-neon focus:border-neon block p-2.5 placeholder-slate-500 transition-all">
                        </div>
                    </div>
                    <div class="col-span-4">
                        <label for="proximity_runner_id" class="block text-[11px] font-mono text-slate-400 mb-1.5 uppercase tracking-wider font-bold">PB Berdekatan Dengan</label>
                        <div class="relative">
                            <select name="proximity_runner_id" class="w-full bg-slate-800/80 border border-slate-700/80 text-white text-xs rounded-xl focus:ring-neon focus:border-neon block p-2.5 appearance-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                                <option value="">-- Pilih Runner --</option>
                                @foreach($allCoachAthletes as $athlete)
                                    <option value="{{ $athlete->id }}" {{ $proximityRunnerId == $athlete->id ? 'selected' : '' }}>
                                        {{ $athlete->name }} (VDOT: {{ round($athlete->vdot, 1) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <i class="fa-solid fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label for="proximity_diff" class="block text-[11px] font-mono text-slate-400 mb-1.5 uppercase tracking-wider font-bold">Toleransi</label>
                        <input type="number" name="proximity_diff" value="{{ $proximityDiff ?? 3.0 }}" placeholder="±3.0" step="0.1"
                            class="w-full bg-slate-800/80 border border-slate-700/80 text-white text-xs rounded-xl focus:ring-neon focus:border-neon block p-2.5 placeholder-slate-500 transition-all">
                    </div>
                    <div class="col-span-3 flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2.5 text-xs font-black text-dark bg-neon rounded-xl hover:bg-white transition-all shadow-md flex items-center justify-center gap-1.5">
                            <i class="fa-solid fa-filter text-[10px]"></i>
                            <span>FILTER</span>
                        </button>
                        <a href="{{ route('coach.athletes.index') }}" id="desktop-reset-btn" class="{{ ($search || $programId || $vdotMin || $vdotMax || $proximityRunnerId) ? '' : 'hidden' }} px-3 py-2.5 text-xs font-bold text-slate-400 bg-slate-800/90 rounded-xl hover:bg-slate-700 hover:text-white transition-all border border-slate-700 flex items-center justify-center" title="Reset Filter">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabs for View Mode -->
        <div class="flex gap-4 sm:gap-6 mb-6 border-b border-slate-800/80 pb-px">
            <button id="tab-all-btn" onclick="switchTab('all')" class="pb-3 text-xs sm:text-sm font-extrabold transition-all relative flex items-center gap-2 {{ $tab === 'all' ? 'text-neon font-black' : 'text-slate-400 hover:text-white' }}">
                <i class="fa-solid fa-users text-xs"></i>
                <span>All Athletes</span>
                @if($tab === 'all')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-neon shadow-[0_0_8px_#ccff00]"></div>
                @endif
            </button>
            <button id="tab-clusters-btn" onclick="switchTab('clusters')" class="pb-3 text-xs sm:text-sm font-extrabold transition-all relative flex items-center gap-2 {{ $tab === 'clusters' ? 'text-neon font-black' : 'text-slate-400 hover:text-white' }}">
                <i class="fa-solid fa-layer-group text-xs"></i>
                <span>Smart VDOT Clusters</span>
                @if($tab === 'clusters')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-neon shadow-[0_0_8px_#ccff00]"></div>
                @endif
            </button>
        </div>

        <div class="glass-panel rounded-2xl p-4 md:p-6" id="athletes-list-container">
            @include('coach.athletes._list')
        </div>
    </div>
</div>

<!-- Mobile Filter Bottom Sheet -->
<div id="mobileFilterSheet" class="fixed inset-0 z-[100] transition-transform duration-300 transform translate-y-full md:hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-slate-900/95 backdrop-blur-xl border-t border-slate-800/90 rounded-t-3xl p-5 shadow-2xl overflow-y-auto max-h-[85vh]">
        <div class="w-10 h-1 bg-slate-700/80 rounded-full mx-auto mb-5" onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')"></div>
        
        <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-800/80">
            <h3 class="text-sm font-extrabold text-white uppercase tracking-wider flex items-center gap-2">
                <i class="fa-solid fa-sliders text-neon"></i>
                <span>Filter Athletes</span>
            </h3>
            <button onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')" class="text-slate-400 hover:text-white text-sm">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        
        <form action="{{ route('coach.athletes.index') }}" method="GET" class="space-y-4">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div>
                <label class="block text-[11px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Cari Nama / Email</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama runner..." 
                    class="w-full bg-slate-800/90 border border-slate-700/80 text-white text-xs rounded-xl p-3 focus:ring-neon focus:border-neon transition-all">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Pilih Program</label>
                <select name="program_id" class="w-full bg-slate-800/90 border border-slate-700/80 text-white text-xs rounded-xl p-3 focus:ring-neon focus:border-neon transition-all appearance-none">
                    <option value="">Semua Program</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                            {{ $program->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Urutkan Berdasarkan</label>
                <select name="sort_by" class="w-full bg-slate-800/90 border border-slate-700/80 text-white text-xs rounded-xl p-3 focus:ring-neon focus:border-neon transition-all appearance-none">
                    <option value="latest" {{ $sortBy == 'latest' ? 'selected' : '' }}>Pendaftaran Terbaru</option>
                    <option value="vdot_desc" {{ $sortBy == 'vdot_desc' ? 'selected' : '' }}>VDOT Tertinggi</option>
                    <option value="vdot_asc" {{ $sortBy == 'vdot_asc' ? 'selected' : '' }}>VDOT Terendah</option>
                    <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Nama Runner</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Min VDOT</label>
                    <input type="number" name="vdot_min" value="{{ $vdotMin }}" placeholder="Min" step="0.1"
                        class="w-full bg-slate-800/90 border border-slate-700/80 text-white text-xs rounded-xl p-3 focus:ring-neon focus:border-neon transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Max VDOT</label>
                    <input type="number" name="vdot_max" value="{{ $vdotMax }}" placeholder="Max" step="0.1"
                        class="w-full bg-slate-800/90 border border-slate-700/80 text-white text-xs rounded-xl p-3 focus:ring-neon focus:border-neon transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">PB Berdekatan Dengan</label>
                <select name="proximity_runner_id" class="w-full bg-slate-800/90 border border-slate-700/80 text-white text-xs rounded-xl p-3 focus:ring-neon focus:border-neon transition-all appearance-none">
                    <option value="">-- Pilih Runner --</option>
                    @foreach($allCoachAthletes as $athlete)
                        <option value="{{ $athlete->id }}" {{ $proximityRunnerId == $athlete->id ? 'selected' : '' }}>
                            {{ $athlete->name }} (VDOT: {{ round($athlete->vdot, 1) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 mb-1.5 uppercase tracking-wider">Toleransi VDOT</label>
                <input type="number" name="proximity_diff" value="{{ $proximityDiff ?? 3.0 }}" placeholder="±3.0" step="0.1"
                    class="w-full bg-slate-800/90 border border-slate-700/80 text-white text-xs rounded-xl p-3 focus:ring-neon focus:border-neon transition-all">
            </div>

            <div class="flex gap-2.5 pt-3 border-t border-slate-800/80">
                <a href="{{ route('coach.athletes.index') }}" id="mobile-reset-btn" class="{{ ($search || $programId || $vdotMin || $vdotMax || $proximityRunnerId) ? '' : 'hidden' }} flex-1 py-3 text-xs font-bold text-slate-400 bg-slate-800/90 rounded-xl border border-slate-700 text-center flex items-center justify-center">
                    RESET
                </a>
                <button type="submit" class="flex-[2] py-3 text-xs font-black text-dark bg-neon rounded-xl shadow-lg shadow-neon/20 flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>APPLY FILTER</span>
                </button>
            </div>
        </form>
    </div>
</div>v>

<!-- Manual Enrollment Modal -->
<div id="enrollModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/85 backdrop-blur-sm" onclick="closeEnrollModal()"></div>
    <div class="relative bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 md:p-8 shadow-2xl mx-4 transition-all duration-300 scale-95 opacity-0 transform" id="enrollModalContent">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-lg font-bold text-white tracking-tight uppercase">Daftarkan Runner Manual</h3>
                <p class="text-xs text-slate-400 mt-1">Daftarkan atlet baru atau hubungkan atlet yang sudah terdaftar.</p>
            </div>
            <button onclick="closeEnrollModal()" class="text-slate-500 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('coach.athletes.enroll') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="existing_user_id" id="enroll_existing_user_id">

            <div>
                <label for="enroll_program_id" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Pilih Program Latihan</label>
                <select name="program_id" id="enroll_program_id" required class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon" onchange="onEnrollProgramChange()">
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                            {{ $program->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ── AJAX Runner Search ─────────────────────────────────── --}}
            <div class="relative" id="enroll-search-wrap">
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Cari Runner yang Sudah Terdaftar (Opsional)</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text" id="enroll_user_search" autocomplete="off"
                        placeholder="Ketik nama, email, atau no HP..."
                        class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl pl-9 pr-4 py-3 focus:ring-1 focus:ring-neon focus:border-neon outline-none"
                        oninput="searchEnrollUsers(this.value)">
                    <span id="enroll-search-loading" class="hidden absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="w-4 h-4 text-slate-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </div>

                {{-- Dropdown results --}}
                <div id="enroll-user-dropdown" class="hidden absolute z-50 w-full mt-1 bg-slate-800 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-56 overflow-y-auto"></div>

                {{-- Selected badge --}}
                <div id="enroll-selected-user" class="hidden mt-2 flex items-center gap-2 bg-neon/10 border border-neon/30 rounded-xl px-3 py-2">
                    <div id="enroll-selected-avatar" class="w-7 h-7 rounded-lg bg-neon/20 text-neon font-black text-xs flex items-center justify-center flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div id="enroll-selected-name" class="text-white font-bold text-xs truncate"></div>
                        <div id="enroll-selected-email" class="text-slate-400 text-[10px] truncate"></div>
                    </div>
                    <button type="button" onclick="clearEnrollSelection()" class="text-slate-400 hover:text-white transition flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            {{-- ──────────────────────────────────────────────────────── --}}

            <div id="enroll-manual-fields" class="space-y-4">
                <div class="flex items-center gap-2 text-[10px] text-slate-500 uppercase tracking-wider font-semibold">
                    <div class="flex-1 h-px bg-slate-800"></div>
                    <span>atau isi data baru</span>
                    <div class="flex-1 h-px bg-slate-800"></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="enroll_name" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Nama Runner</label>
                    <input type="text" name="name" id="enroll_name" required placeholder="Contoh: John Doe" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon">
                </div>
                <div>
                    <label for="enroll_phone" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">No HP / WhatsApp (Opsional)</label>
                    <input type="text" name="phone" id="enroll_phone" placeholder="Contoh: 081234567890" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="enroll_email" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Email Runner</label>
                    <input type="email" name="email" id="enroll_email" required placeholder="Contoh: johndoe@gmail.com" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon">
                </div>
                <div>
                    <label for="enroll_start_date" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="enroll_start_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon">
                </div>
            </div>

            <div>
                <label for="enroll_password" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Password (Opsional)</label>
                <input type="text" name="password" id="enroll_password" placeholder="Kosongkan untuk auto-generate" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Metode Input Kebugaran (VDOT)</label>
                <div class="grid grid-cols-3 gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800">
                    <button type="button" onclick="setVdotMode('direct')" id="btn-vdot-direct" class="py-2 text-[10px] md:text-xs font-black rounded-lg transition-all bg-neon text-dark">
                        Direct VDOT
                    </button>
                    <button type="button" onclick="setVdotMode('pb')" id="btn-vdot-pb" class="py-2 text-[10px] md:text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                        Personal Best
                    </button>
                    <button type="button" onclick="setVdotMode('balke')" id="btn-vdot-balke" class="py-2 text-[10px] md:text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white">
                        Balke Test
                    </button>
                </div>
                <input type="hidden" name="vdot_mode" id="enroll_vdot_mode" value="direct">
            </div>

            <!-- VDOT Input Sections -->
            <div id="sec-vdot-direct" class="space-y-2">
                <label for="enroll_vdot" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">VDOT Score (Opsional)</label>
                <input type="number" name="vdot" id="enroll_vdot" placeholder="Contoh: 45" step="0.1" min="10" max="85" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon" oninput="calculatePreviewVDOT()">
            </div>

            <div id="sec-vdot-pb" class="space-y-3 hidden">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="enroll_pb_distance" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Jarak PB</label>
                        <select name="pb_distance" id="enroll_pb_distance" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon" onchange="calculatePreviewVDOT()">
                            <option value="5k">5K (5.000m)</option>
                            <option value="10k">10K (10.000m)</option>
                            <option value="21k">Half Marathon (21.097m)</option>
                            <option value="42k">Full Marathon (42.195m)</option>
                        </select>
                    </div>
                    <div>
                        <label for="enroll_pb_time" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Waktu (MM:SS / HH:MM:SS)</label>
                        <input type="text" name="pb_time" id="enroll_pb_time" placeholder="Waktu (e.g. 22:30)" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon" oninput="calculatePreviewVDOT()">
                    </div>
                </div>
            </div>

            <div id="sec-vdot-balke" class="space-y-2 hidden">
                <label for="enroll_pb_balke" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Jarak Tempuh Balke (Meter - 15 Menit)</label>
                <input type="number" name="pb_balke" id="enroll_pb_balke" placeholder="Contoh: 3100" min="100" max="10000" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon" oninput="calculatePreviewVDOT()">
            </div>

            <!-- Preview Box -->
            <div id="vdot-preview-box" class="bg-slate-950/60 border border-slate-800/80 rounded-xl p-3 flex justify-between items-center text-xs hidden">
                <span class="text-slate-400 font-medium">Estimasi VDOT Score:</span>
                <span class="text-neon font-black font-mono text-sm" id="vdot-preview-val">-</span>
            </div>

            </div>{{-- end enroll-manual-fields --}}

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800/80">
                <button type="button" onclick="closeEnrollModal()" class="px-5 py-3 text-sm font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700 transition">
                    Batal
                </button>
                <button type="submit" class="px-6 py-3 text-sm font-black text-dark bg-neon rounded-xl hover:bg-white transition-all shadow-lg hover:shadow-neon-cyan">
                    Daftarkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Import CSV/JSON Modal -->
<div id="importModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/85 backdrop-blur-sm" onclick="closeImportModal()"></div>
    <div class="relative bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-lg p-6 md:p-8 shadow-2xl mx-4 transition-all duration-300 scale-95 opacity-0 transform" id="importModalContent">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-lg font-bold text-white tracking-tight uppercase">Import Runner</h3>
                <p class="text-xs text-slate-400 mt-1">Import beberapa runner sekaligus menggunakan file CSV atau JSON.</p>
            </div>
            <button onclick="closeImportModal()" class="text-slate-500 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="{{ route('coach.athletes.import-enroll') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="import_program_id" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Pilih Program Latihan</label>
                <select name="program_id" id="import_program_id" required class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon">
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                            {{ $program->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Upload File (CSV / JSON)</label>
                <div class="border-2 border-dashed border-slate-700 rounded-2xl p-6 text-center hover:border-neon/60 transition-colors relative cursor-pointer group bg-slate-800/20">
                    <input type="file" name="file" id="import_file" required accept=".csv,.json" class="absolute inset-0 opacity-0 cursor-pointer" onchange="updateFileName(this)">
                    <svg class="w-10 h-10 text-slate-500 mx-auto mb-2 group-hover:text-neon transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <p class="text-sm text-slate-300 font-bold" id="file_label">Pilih file CSV atau JSON</p>
                    <p class="text-xs text-slate-500 mt-1">Maksimum ukuran file: 2MB</p>
                </div>
            </div>

            <div class="bg-slate-800/40 border border-slate-800 rounded-xl p-4 text-xs space-y-2">
                <p class="font-bold text-white uppercase tracking-wider">Panduan Format File:</p>
                <p class="text-slate-400">File harus berisi kolom header berikut: <code class="text-neon font-mono">name</code>, <code class="text-neon font-mono">email</code>, <code class="text-neon font-mono">phone</code> (opsional), <code class="text-neon font-mono">vdot</code> (opsional), <code class="text-neon font-mono">pb_distance</code>, <code class="text-neon font-mono">pb_time</code>, <code class="text-neon font-mono">pb_balke</code>, <code class="text-neon font-mono">start_date</code>.</p>
                <div class="flex justify-between items-center pt-2">
                    <span class="text-slate-500">Unduh contoh template:</span>
                    <a href="{{ route('coach.athletes.import-template') }}" class="text-cyan-400 hover:text-cyan-300 font-bold underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Download Template CSV
                    </a>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800/80">
                <button type="button" onclick="closeImportModal()" class="px-5 py-3 text-sm font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700 transition">
                    Batal
                </button>
                <button type="submit" class="px-6 py-3 text-sm font-black text-dark bg-neon rounded-xl hover:bg-white transition-all shadow-lg hover:shadow-neon-cyan">
                    Mulai Import
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Send Program Reminder Modal -->
<div id="reminderModal" class="fixed inset-0 z-[110] hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/85 backdrop-blur-sm" onclick="closeReminderModal()"></div>
    <div class="relative bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 md:p-8 shadow-2xl mx-4 transition-all duration-300 scale-95 opacity-0 transform" id="reminderModalContent">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-lg font-bold text-white tracking-tight uppercase">Kirim Pengingat Program</h3>
                <p class="text-xs text-slate-400 mt-1">Kirim pengingat sesi latihan besok ke atlet</p>
            </div>
            <button onclick="closeReminderModal()" class="text-slate-500 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="reminderForm" onsubmit="submitReminderForm(event)" class="space-y-4">
            @csrf
            <input type="hidden" name="enrollment_id" id="reminder_enrollment_id">

            <div>
                <label for="reminder_channel" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Saluran Pengiriman (Channel)</label>
                <select name="channel" id="reminder_channel" required class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon">
                    <option value="both">WhatsApp & Email</option>
                    <option value="wa">WhatsApp Saja</option>
                    <option value="email">Email Saja</option>
                </select>
            </div>

            <div>
                <label for="reminder_custom_message" class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Pesan Kustom (Opsional)</label>
                <textarea name="custom_message" id="reminder_custom_message" rows="4" placeholder="Tulis pesan kustom di sini... (Kosongkan untuk menggunakan pesan otomatis AI)"
                    class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-3 focus:ring-neon focus:border-neon outline-none resize-none"></textarea>
                <p class="text-[10px] text-slate-500 mt-1">Jika dikosongkan, sistem akan otomatis membuat pesan pengingat yang dipersonalisasi menggunakan AI.</p>
            </div>

            <div id="reminder-error-msg" class="hidden text-red-400 text-xs bg-red-500/10 border border-red-500/20 rounded-xl p-3"></div>
            <div id="reminder-success-msg" class="hidden text-neon text-xs bg-neon/10 border border-neon/20 rounded-xl p-3"></div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-800/80">
                <button type="button" onclick="closeReminderModal()" class="px-5 py-3 text-sm font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700 transition">
                    Batal
                </button>
                <button type="submit" id="reminder-submit-btn" class="px-6 py-3 text-sm font-black text-dark bg-neon rounded-xl hover:bg-white transition-all shadow-lg hover:shadow-neon-cyan">
                    Kirim Pengingat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('athletes-list-container');
    if (!listContainer) return;

    // Desktop elements
    const desktopForm = document.querySelector('.hidden.md\\:block.mb-8 form');
    const desktopResetBtn = document.getElementById('desktop-reset-btn');

    // Mobile elements
    const mobileForm = document.querySelector('#mobileFilterSheet form');
    const mobileResetBtn = document.getElementById('mobile-reset-btn');
    const mobileSheet = document.getElementById('mobileFilterSheet');

    let debounceTimeout = null;

    // Function to perform AJAX fetch
    async function fetchAthletes(url, formData) {
        listContainer.style.opacity = '0.5';

        try {
            const urlObj = new URL(url);
            if (formData) {
                for (const [key, value] of formData.entries()) {
                    if (value !== '' && value !== null) {
                        urlObj.searchParams.set(key, value);
                    } else {
                        urlObj.searchParams.delete(key);
                    }
                }
            }

            const response = await fetch(urlObj.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Network response was not ok');

            const html = await response.text();
            listContainer.innerHTML = html;

            // Update browser URL history
            window.history.pushState({}, '', urlObj.toString());

            // Re-render reset buttons if needed
            const hasFilter = urlObj.searchParams.has('search') || 
                              urlObj.searchParams.has('program_id') ||
                              urlObj.searchParams.has('vdot_min') ||
                              urlObj.searchParams.has('vdot_max') ||
                              urlObj.searchParams.has('proximity_runner_id');
            updateResetButtonsVisibility(hasFilter);

        } catch (error) {
            console.error('Error fetching athletes:', error);
        } finally {
            listContainer.style.opacity = '1';
        }
    }

    function submitFilters() {
        const activeForm = window.innerWidth >= 768 ? desktopForm : mobileForm;
        if (!activeForm) return;
        const formData = new FormData(activeForm);
        fetchAthletes('{{ route("coach.athletes.index") }}', formData);
    }

    function updateResetButtonsVisibility(show) {
        if (desktopResetBtn) {
            if (show) {
                desktopResetBtn.classList.remove('hidden');
                desktopResetBtn.style.display = 'flex';
            } else {
                desktopResetBtn.classList.add('hidden');
                desktopResetBtn.style.display = 'none';
            }
        }
        if (mobileResetBtn) {
            if (show) {
                mobileResetBtn.classList.remove('hidden');
                mobileResetBtn.style.display = 'block';
            } else {
                mobileResetBtn.classList.add('hidden');
                mobileResetBtn.style.display = 'none';
            }
        }
    }

    // Intercept form submissions
    if (desktopForm) {
        desktopForm.addEventListener('submit', function(e) {
            e.preventDefault();
            submitFilters();
        });
    }

    if (mobileForm) {
        mobileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (mobileSheet) mobileSheet.classList.add('translate-y-full'); // Close sheet
            submitFilters();
        });
    }

    // Interactive event listeners: Auto-filter on select change
    const selects = document.querySelectorAll('form select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
            // Sync values between desktop and mobile counterpart
            const name = this.getAttribute('name');
            const counterpart = document.querySelector(`form:not(${this.closest('form').className}) select[name="${name}"]`);
            if (counterpart) counterpart.value = this.value;
            submitFilters();
        });
    });

    // Debounced search on typing
    const inputs = document.querySelectorAll('form input[type="text"], form input[type="number"]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                // Sync values
                const name = this.getAttribute('name');
                const counterparts = document.querySelectorAll(`form input[name="${name}"]`);
                counterparts.forEach(c => { if (c !== this) c.value = this.value; });
                submitFilters();
            }, 500);
        });
    });

    // Intercept pagination clicks
    listContainer.addEventListener('click', function(e) {
        const targetLink = e.target.closest('.ajax-pagination a, .pagination a');
        if (targetLink) {
            e.preventDefault();
            const url = targetLink.getAttribute('href');
            if (url) {
                const activeForm = window.innerWidth >= 768 ? desktopForm : mobileForm;
                const formData = activeForm ? new FormData(activeForm) : null;
                fetchAthletes(url, formData);
            }
        }
    });

    // Reset button handler
    const handleReset = function(e) {
        e.preventDefault();
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.reset();
            form.querySelectorAll('input').forEach(input => {
                if (input.type !== 'hidden') input.value = '';
            });
            form.querySelectorAll('select').forEach(select => select.value = '');
        });
        if (mobileSheet) mobileSheet.classList.add('translate-y-full');
        
        // Force tab to remain consistent
        const tabValue = document.querySelector('input[name="tab"]')?.value || 'all';
        const formData = new FormData();
        formData.set('tab', tabValue);
        
        fetchAthletes('{{ route("coach.athletes.index") }}', formData);
    };

    if (desktopResetBtn) desktopResetBtn.addEventListener('click', handleReset);
    if (mobileResetBtn) mobileResetBtn.addEventListener('click', handleReset);

    // Global tab switching function
    window.switchTab = function(tabName) {
        const tabInputs = document.querySelectorAll('input[name="tab"]');
        tabInputs.forEach(input => input.value = tabName);
        
        const allBtn = document.getElementById('tab-all-btn');
        const clustersBtn = document.getElementById('tab-clusters-btn');
        
        if (allBtn && clustersBtn) {
            if (tabName === 'all') {
                allBtn.className = "pb-3 text-sm font-black text-neon transition-all relative";
                allBtn.innerHTML = 'All Athletes <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-neon shadow-[0_0_8px_#ccff00]"></div>';
                clustersBtn.className = "pb-3 text-sm font-bold text-slate-400 hover:text-white transition-all relative";
                clustersBtn.innerHTML = 'Smart VDOT Clusters / Groups';
            } else {
                clustersBtn.className = "pb-3 text-sm font-black text-neon transition-all relative";
                clustersBtn.innerHTML = 'Smart VDOT Clusters / Groups <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-neon shadow-[0_0_8px_#ccff00]"></div>';
                allBtn.className = "pb-3 text-sm font-bold text-slate-400 hover:text-white transition-all relative";
                allBtn.innerHTML = 'All Athletes';
            }
        }

        submitFilters();
    };

    // Modal controls
    const enrollModal = document.getElementById('enrollModal');
    const enrollModalContent = document.getElementById('enrollModalContent');
    const importModal = document.getElementById('importModal');
    const importModalContent = document.getElementById('importModalContent');

    window.openEnrollModal = function() {
        if (!enrollModal || !enrollModalContent) return;
        enrollModal.classList.remove('hidden');
        setTimeout(() => {
            enrollModalContent.classList.remove('scale-95', 'opacity-0');
            enrollModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    };

    window.closeEnrollModal = function() {
        if (!enrollModal || !enrollModalContent) return;
        enrollModalContent.classList.remove('scale-100', 'opacity-100');
        enrollModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            enrollModal.classList.add('hidden');
        }, 300);
    };

    window.openImportModal = function() {
        if (!importModal || !importModalContent) return;
        importModal.classList.remove('hidden');
        setTimeout(() => {
            importModalContent.classList.remove('scale-95', 'opacity-0');
            importModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    };

    window.closeImportModal = function() {
        if (!importModal || !importModalContent) return;
        importModalContent.classList.remove('scale-100', 'opacity-100');
        importModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            importModal.classList.add('hidden');
        }, 300);
    };

    window.updateFileName = function(input) {
        const fileLabel = document.getElementById('file_label');
        if (input.files && input.files.length > 0) {
            fileLabel.textContent = input.files[0].name;
            fileLabel.classList.add('text-neon');
        } else {
            fileLabel.textContent = 'Pilih file CSV atau JSON';
            fileLabel.classList.remove('text-neon');
        }
    };

    // Set VDOT Mode Tabs
    window.setVdotMode = function(mode) {
        document.getElementById('enroll_vdot_mode').value = mode;

        // Reset tab buttons styling
        const btnDirect = document.getElementById('btn-vdot-direct');
        const btnPb = document.getElementById('btn-vdot-pb');
        const btnBalke = document.getElementById('btn-vdot-balke');

        [btnDirect, btnPb, btnBalke].forEach(btn => {
            btn.className = "py-2 text-[10px] md:text-xs font-bold rounded-lg transition-all text-slate-400 hover:text-white";
        });

        // Set active button styling
        const activeBtn = document.getElementById('btn-vdot-' + mode);
        if (activeBtn) {
            activeBtn.className = "py-2 text-[10px] md:text-xs font-black rounded-lg transition-all bg-neon text-dark";
        }

        // Hide/Show sections
        document.getElementById('sec-vdot-direct').classList.add('hidden');
        document.getElementById('sec-vdot-pb').classList.add('hidden');
        document.getElementById('sec-vdot-balke').classList.add('hidden');

        document.getElementById('sec-vdot-' + mode).classList.remove('hidden');

        calculatePreviewVDOT();
    };

    // Calculate VDOT preview client-side
    window.calculatePreviewVDOT = function() {
        const mode = document.getElementById('enroll_vdot_mode').value;
        const previewBox = document.getElementById('vdot-preview-box');
        const previewVal = document.getElementById('vdot-preview-val');

        if (mode === 'direct') {
            const vdotVal = parseFloat(document.getElementById('enroll_vdot').value);
            if (!isNaN(vdotVal) && vdotVal >= 10 && vdotVal <= 85) {
                previewBox.classList.remove('hidden');
                previewVal.textContent = vdotVal.toFixed(1);
            } else {
                previewBox.classList.add('hidden');
            }
        } else if (mode === 'pb') {
            const distance = document.getElementById('enroll_pb_distance').value;
            const timeStr = document.getElementById('enroll_pb_time').value.trim();

            if (!timeStr) {
                previewBox.classList.add('hidden');
                return;
            }

            // Parse time MM:SS or HH:MM:SS
            const parts = timeStr.split(':');
            let totalSeconds = 0;
            if (parts.length === 3) {
                totalSeconds = (parseInt(parts[0]) * 3600) + (parseInt(parts[1]) * 60) + parseFloat(parts[2]);
            } else if (parts.length === 2) {
                totalSeconds = (parseInt(parts[0]) * 60) + parseFloat(parts[1]);
            } else {
                previewBox.classList.add('hidden');
                return;
            }

            if (isNaN(totalSeconds) || totalSeconds <= 0) {
                previewBox.classList.add('hidden');
                return;
            }

            // Map distances
            let meters = 5000;
            let ratio = 0.957;
            if (distance === '5k') { meters = 5000; ratio = 0.957; }
            else if (distance === '10k') { meters = 10000; ratio = 0.915; }
            else if (distance === '21k') { meters = 21097.5; ratio = 0.865; }
            else if (distance === '42k') { meters = 42195; ratio = 0.815; }

            const velocity = meters / totalSeconds;
            const velocityMin = velocity * 60;

            // VDOT estimation loop
            let vdot = 50.0;
            for (let i = 0; i < 5; i++) {
                let adjRatio = ratio + (vdot - 50.0) * 0.0005;
                if (adjRatio <= 0) adjRatio = 0.01;
                const vVO2max = velocityMin / adjRatio;
                const newVdot = -4.6 + 0.182258 * vVO2max + 0.000104 * vVO2max * vVO2max;
                if (Math.abs(newVdot - vdot) < 0.01) {
                    vdot = newVdot;
                    break;
                }
                vdot = newVdot;
            }

            vdot = Math.max(10, Math.min(85, vdot));
            previewBox.classList.remove('hidden');
            previewVal.textContent = vdot.toFixed(1);
        } else if (mode === 'balke') {
            const meters = parseFloat(document.getElementById('enroll_pb_balke').value);
            if (!isNaN(meters) && meters >= 100) {
                let vdot = ((meters / 15) - 133) * 0.172 + 33.3;
                vdot = Math.max(10, Math.min(85, vdot));
                previewBox.classList.remove('hidden');
                previewVal.textContent = vdot.toFixed(1);
            } else {
                previewBox.classList.add('hidden');
            }
        }
    };

    // ── AJAX Runner Search ──────────────────────────────────────────
    let enrollSearchTimer = null;

    window.onEnrollProgramChange = function() {
        // Re-run search if there's an active query
        const q = document.getElementById('enroll_user_search').value;
        if (q.length >= 2) searchEnrollUsers(q);
        clearEnrollSelection();
    };

    window.searchEnrollUsers = function(q) {
        const dropdown = document.getElementById('enroll-user-dropdown');
        const loading = document.getElementById('enroll-search-loading');
        const programId = document.getElementById('enroll_program_id').value;

        clearTimeout(enrollSearchTimer);

        if (q.length < 2) {
            dropdown.classList.add('hidden');
            dropdown.innerHTML = '';
            return;
        }

        loading.classList.remove('hidden');

        enrollSearchTimer = setTimeout(async () => {
            try {
                const url = `{{ route('coach.athletes.search-users') }}?q=${encodeURIComponent(q)}&program_id=${programId}`;
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const users = await res.json();

                if (users.length === 0) {
                    dropdown.innerHTML = '<div class="px-4 py-3 text-xs text-slate-500 text-center">Tidak ada runner ditemukan</div>';
                } else {
                    dropdown.innerHTML = users.map(u => `
                        <button type="button"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-700/80 transition text-left"
                            onclick="selectEnrollUser(${JSON.stringify(u).replace(/"/g, '&quot;')})">
                            <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center font-black text-sm ${
                                u.avatar ? '' : 'bg-slate-700 text-slate-300'
                            }">
                                ${u.avatar
                                    ? `<img src="${u.avatar}" class="w-8 h-8 rounded-lg object-cover">`
                                    : u.initials}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-white text-sm font-bold truncate">${u.name}</div>
                                <div class="text-slate-400 text-xs truncate">${u.email}</div>
                            </div>
                            ${u.phone ? `<div class="text-slate-500 text-[10px] flex-shrink-0">${u.phone}</div>` : ''}
                        </button>
                    `).join('<div class="border-t border-slate-700/60"></div>');
                }

                dropdown.classList.remove('hidden');
            } catch(e) {
                console.error('Search error', e);
            } finally {
                loading.classList.add('hidden');
            }
        }, 300);
    };

    window.selectEnrollUser = function(user) {
        // Fill hidden id
        document.getElementById('enroll_existing_user_id').value = user.id;

        // Auto-fill visible fields
        document.getElementById('enroll_name').value = user.name;
        document.getElementById('enroll_email').value = user.email;
        document.getElementById('enroll_phone').value = user.phone || '';

        // Make name & email read-only
        document.getElementById('enroll_name').readOnly = true;
        document.getElementById('enroll_name').classList.add('opacity-60', 'cursor-not-allowed');
        document.getElementById('enroll_email').readOnly = true;
        document.getElementById('enroll_email').classList.add('opacity-60', 'cursor-not-allowed');

        // Show selected badge
        const badge = document.getElementById('enroll-selected-user');
        badge.classList.remove('hidden');
        document.getElementById('enroll-selected-name').textContent = user.name;
        document.getElementById('enroll-selected-email').textContent = user.email;
        document.getElementById('enroll-selected-avatar').textContent = user.initials;

        // Clear search input & dropdown
        document.getElementById('enroll_user_search').value = '';
        document.getElementById('enroll-user-dropdown').classList.add('hidden');
        document.getElementById('enroll-user-dropdown').innerHTML = '';
    };

    window.clearEnrollSelection = function() {
        document.getElementById('enroll_existing_user_id').value = '';
        document.getElementById('enroll_name').value = '';
        document.getElementById('enroll_email').value = '';
        document.getElementById('enroll_phone').value = '';
        document.getElementById('enroll_name').readOnly = false;
        document.getElementById('enroll_name').classList.remove('opacity-60', 'cursor-not-allowed');
        document.getElementById('enroll_email').readOnly = false;
        document.getElementById('enroll_email').classList.remove('opacity-60', 'cursor-not-allowed');
        document.getElementById('enroll-selected-user').classList.add('hidden');
    };

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('enroll-search-wrap');
        if (wrap && !wrap.contains(e.target)) {
            const dd = document.getElementById('enroll-user-dropdown');
            if (dd) dd.classList.add('hidden');
        }
    });
    // ───────────────────────────────────────────────────────────────

    // ── Send Program Reminder Modal ───────────────────────────────
    const reminderModal = document.getElementById('reminderModal');
    const reminderModalContent = document.getElementById('reminderModalContent');

    window.openReminderModal = function(enrollmentId) {
        if (!reminderModal || !reminderModalContent) return;
        document.getElementById('reminder_enrollment_id').value = enrollmentId;
        document.getElementById('reminder_custom_message').value = '';
        document.getElementById('reminder_channel').value = 'both';
        document.getElementById('reminder-error-msg').classList.add('hidden');
        document.getElementById('reminder-success-msg').classList.add('hidden');
        
        reminderModal.classList.remove('hidden');
        setTimeout(() => {
            reminderModalContent.classList.remove('scale-95', 'opacity-0');
            reminderModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    };

    window.closeReminderModal = function() {
        if (!reminderModal || !reminderModalContent) return;
        reminderModalContent.classList.remove('scale-100', 'opacity-100');
        reminderModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            reminderModal.classList.add('hidden');
        }, 300);
    };

    window.submitReminderForm = async function(e) {
        e.preventDefault();
        const enrollmentId = document.getElementById('reminder_enrollment_id').value;
        const channel = document.getElementById('reminder_channel').value;
        const customMessage = document.getElementById('reminder_custom_message').value;
        const submitBtn = document.getElementById('reminder-submit-btn');
        const errorMsg = document.getElementById('reminder-error-msg');
        const successMsg = document.getElementById('reminder-success-msg');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengirim...';
        errorMsg.classList.add('hidden');
        successMsg.classList.add('hidden');

        try {
            const response = await fetch(`/coach/athletes/${enrollmentId}/send-reminder`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    channel: channel,
                    custom_message: customMessage
                })
            });

            const data = await response.json();
            if (data.success) {
                successMsg.textContent = data.message || 'Pengingat berhasil dikirim!';
                successMsg.classList.remove('hidden');
                setTimeout(() => {
                    closeReminderModal();
                }, 1500);
            } else {
                errorMsg.textContent = data.message || 'Gagal mengirim pengingat.';
                errorMsg.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            errorMsg.textContent = 'Terjadi kesalahan koneksi.';
            errorMsg.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Kirim Pengingat';
        }
    };
    // ───────────────────────────────────────────────────────────────

    window.confirmDeleteAthlete = function(enrollmentId, runnerName, programTitle) {
        if (!confirm(`Apakah Anda yakin ingin menghapus atlet "${runnerName}" dari program "${programTitle}"?\nSemua log tracking latihan atlet tersebut pada program ini juga akan dihapus secara permanen.`)) {
            return;
        }

        fetch(`/coach/athletes/${enrollmentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Atlet berhasil dihapus.');
                if (typeof submitFilters === 'function') {
                    submitFilters();
                } else {
                    location.reload();
                }
            } else {
                alert(data.message || 'Gagal menghapus atlet.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan koneksi.');
        });
    };
});
</script>
@endpush
