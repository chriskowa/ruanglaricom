@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'Daftar Atlet Bimbingan')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-7xl mx-auto space-y-6">
        @if(session('success'))
            <div class="p-3.5 rounded-md bg-emerald-950 border border-emerald-800 text-emerald-300 text-xs font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3.5 rounded-md bg-rose-950 border border-rose-800 text-rose-300 text-xs font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-md bg-rose-950 border border-rose-800 text-rose-300 text-xs font-medium">
                <ul class="list-disc pl-4 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-800 pb-5">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Daftar Atlet</h1>
                <p class="text-xs text-slate-400 mt-1">Pantau perkembangan latihan, parameter VDOT, dan pendaftaran runner.</p>
            </div>
            <div class="flex flex-wrap sm:flex-nowrap gap-2 items-center w-full sm:w-auto">
                <button onclick="openEnrollModal()" class="px-3.5 py-2 rounded-md bg-neon text-dark font-semibold hover:bg-white transition text-xs flex-1 sm:flex-none">
                    + Daftarkan Atlet
                </button>
                <button onclick="openImportModal()" class="px-3.5 py-2 rounded-md bg-slate-800 text-slate-200 border border-slate-700 hover:bg-slate-700 hover:text-white font-medium transition text-xs flex-1 sm:flex-none">
                    Import CSV / JSON
                </button>
                <!-- Mobile Filter Trigger -->
                <button onclick="document.getElementById('mobileFilterSheet').classList.remove('translate-y-full')" class="md:hidden px-3.5 py-2 rounded-md bg-slate-800 border border-slate-700 text-slate-200 font-medium text-xs">
                    Filter
                </button>
            </div>
        </div>

        <!-- Filter Section (Desktop) -->
        <div class="hidden md:block bg-slate-900 rounded-lg p-5 border border-slate-800">
            <form action="{{ route('coach.athletes.index') }}" method="GET" class="space-y-4">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <div class="grid grid-cols-12 gap-3 items-end">
                    <div class="col-span-3">
                        <label for="search" class="block text-xs font-medium text-slate-300 mb-1">Cari Atlet</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Nama atau email..." 
                            class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md focus:ring-1 focus:ring-slate-500 px-3 py-2 outline-none">
                    </div>
                    <div class="col-span-3">
                        <label for="program_id" class="block text-xs font-medium text-slate-300 mb-1">Program Latihan</label>
                        <select name="program_id" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md focus:ring-1 focus:ring-slate-500 px-3 py-2 outline-none">
                            <option value="">Semua Program</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                                    {{ $program->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label for="status" class="block text-xs font-medium text-slate-300 mb-1">Status Keanggotaan</label>
                        <select name="status" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md focus:ring-1 focus:ring-slate-500 px-3 py-2 outline-none">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non-aktif (Expired)</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="purchased" {{ request('status') == 'purchased' ? 'selected' : '' }}>Belum Aktif</option>
                        </select>
                    </div>
                    <div class="col-span-3">
                        <label for="sort_by" class="block text-xs font-medium text-slate-300 mb-1">Urutan</label>
                        <select name="sort_by" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md focus:ring-1 focus:ring-slate-500 px-3 py-2 outline-none">
                            <option value="latest" {{ $sortBy == 'latest' ? 'selected' : '' }}>Pendaftaran Terbaru</option>
                            <option value="vdot_desc" {{ $sortBy == 'vdot_desc' ? 'selected' : '' }}>VDOT Tertinggi</option>
                            <option value="vdot_asc" {{ $sortBy == 'vdot_asc' ? 'selected' : '' }}>VDOT Terendah</option>
                            <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Nama Atlet</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-3 items-end pt-3 border-t border-slate-800">
                    <div class="col-span-3">
                        <label class="block text-xs font-medium text-slate-300 mb-1">Rentang VDOT (Min - Max)</label>
                        <div class="flex gap-2">
                            <input type="number" name="vdot_min" value="{{ $vdotMin }}" placeholder="Min" step="0.1"
                                class="w-1/2 bg-slate-950 border border-slate-800 text-white text-xs rounded-md px-3 py-2 font-mono outline-none">
                            <input type="number" name="vdot_max" value="{{ $vdotMax }}" placeholder="Max" step="0.1"
                                class="w-1/2 bg-slate-950 border border-slate-800 text-white text-xs rounded-md px-3 py-2 font-mono outline-none">
                        </div>
                    </div>
                    <div class="col-span-4">
                        <label for="proximity_runner_id" class="block text-xs font-medium text-slate-300 mb-1">Cari Atlet dengan VDOT Serupa</label>
                        <select name="proximity_runner_id" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md focus:ring-1 focus:ring-slate-500 px-3 py-2 outline-none">
                            <option value="">-- Pilih Acuan Atlet --</option>
                            @foreach($allCoachAthletes as $athlete)
                                <option value="{{ $athlete->id }}" {{ $proximityRunnerId == $athlete->id ? 'selected' : '' }}>
                                    {{ $athlete->name }} (VDOT: {{ round($athlete->vdot, 1) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label for="proximity_diff" class="block text-xs font-medium text-slate-300 mb-1">Toleransi (±)</label>
                        <input type="number" name="proximity_diff" value="{{ $proximityDiff ?? 3.0 }}" placeholder="±3.0" step="0.1"
                            class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md px-3 py-2 font-mono outline-none">
                    </div>
                    <div class="col-span-3 flex gap-2">
                        <button type="submit" class="flex-1 py-2 px-3 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition">
                            Terapkan Filter
                        </button>
                        <a href="{{ route('coach.athletes.index') }}" id="desktop-reset-btn" class="{{ ($search || $programId || $vdotMin || $vdotMax || $proximityRunnerId) ? '' : 'hidden' }} px-3 py-2 text-xs font-medium text-slate-400 bg-slate-950 rounded-md hover:bg-slate-800 hover:text-white transition border border-slate-800">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabs for View Mode -->
        <div class="flex gap-6 border-b border-slate-800 pb-px text-xs">
            <button id="tab-all-btn" onclick="switchTab('all')" class="pb-2.5 font-semibold transition relative {{ $tab === 'all' ? 'text-white border-b-2 border-neon' : 'text-slate-400 hover:text-slate-200' }}">
                Semua Atlet
            </button>
            <button id="tab-clusters-btn" onclick="switchTab('clusters')" class="pb-2.5 font-semibold transition relative {{ $tab === 'clusters' ? 'text-white border-b-2 border-neon' : 'text-slate-400 hover:text-slate-200' }}">
                Pengelompokan VDOT (Clusters)
            </button>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5 sm:p-6" id="athletes-list-container">
            @include('coach.athletes._list')
        </div>
    </div>
</div>

<!-- Mobile Filter Bottom Sheet -->
<div id="mobileFilterSheet" class="fixed inset-0 z-[100] transition-transform duration-300 transform translate-y-full md:hidden">
    <div class="absolute inset-0 bg-black/70" onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-slate-900 border-t border-slate-800 rounded-t-lg p-5 shadow-2xl overflow-y-auto max-h-[85vh]">
        <div class="w-12 h-1 bg-slate-700 rounded-full mx-auto mb-4 cursor-pointer" onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')"></div>
        
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
            <h3 class="text-sm font-semibold text-white">Filter Atlet</h3>
            <button onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')" class="text-slate-400 hover:text-white text-xs">
                Tutup
            </button>
        </div>
        
        <form action="{{ route('coach.athletes.index') }}" method="GET" class="space-y-3">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Cari Atlet</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nama atau email..." 
                    class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Pilih Program</label>
                <select name="program_id" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                    <option value="">Semua Program</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                            {{ $program->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Urutan</label>
                <select name="sort_by" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                    <option value="latest" {{ $sortBy == 'latest' ? 'selected' : '' }}>Pendaftaran Terbaru</option>
                    <option value="vdot_desc" {{ $sortBy == 'vdot_desc' ? 'selected' : '' }}>VDOT Tertinggi</option>
                    <option value="vdot_asc" {{ $sortBy == 'vdot_asc' ? 'selected' : '' }}>VDOT Terendah</option>
                    <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Nama Atlet</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Min VDOT</label>
                    <input type="number" name="vdot_min" value="{{ $vdotMin }}" placeholder="Min" step="0.1"
                        class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 font-mono outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Max VDOT</label>
                    <input type="number" name="vdot_max" value="{{ $vdotMax }}" placeholder="Max" step="0.1"
                        class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 font-mono outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Acuan Atlet Serupa</label>
                <select name="proximity_runner_id" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                    <option value="">-- Pilih Acuan Atlet --</option>
                    @foreach($allCoachAthletes as $athlete)
                        <option value="{{ $athlete->id }}" {{ $proximityRunnerId == $athlete->id ? 'selected' : '' }}>
                            {{ $athlete->name }} (VDOT: {{ round($athlete->vdot, 1) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-300 mb-1">Toleransi VDOT</label>
                <input type="number" name="proximity_diff" value="{{ $proximityDiff ?? 3.0 }}" placeholder="±3.0" step="0.1"
                    class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 font-mono outline-none">
            </div>

            <div class="flex gap-2 pt-2 border-t border-slate-800">
                <a href="{{ route('coach.athletes.index') }}" id="mobile-reset-btn" class="{{ ($search || $programId || $vdotMin || $vdotMax || $proximityRunnerId) ? '' : 'hidden' }} flex-1 py-2.5 text-xs font-medium text-slate-400 bg-slate-950 rounded-md border border-slate-800 text-center">
                    Reset
                </a>
                <button type="submit" class="flex-[2] py-2.5 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Manual Enrollment Modal -->
<div id="enrollModal" class="fixed inset-0 z-[110] hidden overflow-y-auto p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/70" onclick="closeEnrollModal()"></div>
    <div class="flex min-h-full items-center justify-center relative pointer-events-none">
        <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-lg shadow-2xl transition-all duration-200 scale-95 opacity-0 transform my-auto max-h-[calc(100vh-2.5rem)] flex flex-col" id="enrollModalContent">
            
            <!-- Header (Fixed Top) -->
            <div class="flex justify-between items-start p-5 pb-4 border-b border-slate-800 shrink-0 bg-slate-900 rounded-t-lg">
                <div>
                    <h3 class="text-base font-semibold text-white">Daftarkan Atlet Manual</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daftarkan atlet baru atau hubungkan atlet yang sudah memiliki akun.</p>
                </div>
                <button onclick="closeEnrollModal()" class="text-slate-400 hover:text-white transition text-lg">
                    &times;
                </button>
            </div>

            <!-- Scrollable Form Container -->
            <form action="{{ route('coach.athletes.enroll') }}" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                <input type="hidden" name="existing_user_id" id="enroll_existing_user_id">

                <!-- Form Fields (Scrollable) -->
                <div class="p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                    <div>
                        <label for="enroll_program_id" class="block text-xs font-medium text-slate-300 mb-1">Pilih Program Latihan</label>
                        <select name="program_id" id="enroll_program_id" required class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none" onchange="onEnrollProgramChange()">
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                                    {{ $program->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ── AJAX Runner Search ─────────────────────────────────── --}}
                    <div class="relative" id="enroll-search-wrap">
                        <label class="block text-xs font-medium text-slate-300 mb-1">Cari Atlet yang Sudah Terdaftar (Opsional)</label>
                        <div class="relative">
                            <input type="text" id="enroll_user_search" autocomplete="off"
                                placeholder="Ketik nama, email, atau nomor HP..."
                                class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md px-3 py-2 outline-none"
                                oninput="searchEnrollUsers(this.value)">
                            <span id="enroll-search-loading" class="hidden absolute right-3 top-1/2 -translate-y-1/2">
                                <span class="animate-spin inline-block w-3 h-3 border-2 border-slate-400 border-t-transparent rounded-full"></span>
                            </span>
                        </div>

                        {{-- Dropdown results --}}
                        <div id="enroll-user-dropdown" class="hidden absolute z-50 w-full mt-1 bg-slate-950 border border-slate-800 rounded-md shadow-2xl overflow-hidden max-h-56 overflow-y-auto"></div>

                        {{-- Selected badge --}}
                        <div id="enroll-selected-user" class="hidden mt-2 flex items-center gap-2 bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-xs">
                            <div id="enroll-selected-avatar" class="w-6 h-6 rounded-full bg-slate-700 text-white font-bold text-xs flex items-center justify-center shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div id="enroll-selected-name" class="text-white font-medium text-xs truncate"></div>
                                <div id="enroll-selected-email" class="text-slate-400 text-xs truncate"></div>
                            </div>
                            <button type="button" onclick="clearEnrollSelection()" class="text-slate-400 hover:text-white transition shrink-0">
                                &times;
                            </button>
                        </div>
                    </div>

                    <div id="enroll-manual-fields" class="space-y-4">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <div class="flex-1 h-px bg-slate-800"></div>
                            <span>atau buat akun baru</span>
                            <div class="flex-1 h-px bg-slate-800"></div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="enroll_name" class="block text-xs font-medium text-slate-300 mb-1">Nama Atlet</label>
                                <input type="text" name="name" id="enroll_name" required placeholder="Nama lengkap atlet" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                            </div>
                            <div>
                                <label for="enroll_phone" class="block text-xs font-medium text-slate-300 mb-1">Nomor WhatsApp / HP (Opsional)</label>
                                <input type="text" name="phone" id="enroll_phone" placeholder="08xxxxxxxxxx" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="enroll_email" class="block text-xs font-medium text-slate-300 mb-1">Email Atlet</label>
                                <input type="email" name="email" id="enroll_email" required placeholder="email@contoh.com" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                            </div>
                            <div>
                                <label for="enroll_start_date" class="block text-xs font-medium text-slate-300 mb-1">Tanggal Mulai</label>
                                <input type="date" name="start_date" id="enroll_start_date" required value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none font-mono">
                            </div>
                        </div>

                        <div>
                            <label for="enroll_password" class="block text-xs font-medium text-slate-300 mb-1">Password Awal (Opsional)</label>
                            <input type="text" name="password" id="enroll_password" placeholder="Kosongkan untuk otomatis dibuat oleh sistem" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Metode Penentuan VDOT</label>
                            <div class="grid grid-cols-3 gap-1 bg-slate-950 p-1 rounded-md border border-slate-800 text-xs">
                                <button type="button" onclick="setVdotMode('direct')" id="btn-vdot-direct" class="py-1.5 px-2 font-medium rounded-md transition bg-slate-800 text-white truncate">
                                    Skor Langsung
                                </button>
                                <button type="button" onclick="setVdotMode('pb')" id="btn-vdot-pb" class="py-1.5 px-2 font-medium rounded-md transition text-slate-400 hover:text-white truncate">
                                    Personal Best
                                </button>
                                <button type="button" onclick="setVdotMode('balke')" id="btn-vdot-balke" class="py-1.5 px-2 font-medium rounded-md transition text-slate-400 hover:text-white truncate">
                                    Tes Balke
                                </button>
                            </div>
                            <input type="hidden" name="vdot_mode" id="enroll_vdot_mode" value="direct">
                        </div>

                        <!-- VDOT Input Sections -->
                        <div id="sec-vdot-direct" class="space-y-1">
                            <label for="enroll_vdot" class="block text-xs font-medium text-slate-300">Skor VDOT (Opsional)</label>
                            <input type="number" name="vdot" id="enroll_vdot" placeholder="misal 45.0" step="0.1" min="10" max="85" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none font-mono" oninput="calculatePreviewVDOT()">
                        </div>

                        <div id="sec-vdot-pb" class="space-y-3 hidden">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="enroll_pb_distance" class="block text-xs font-medium text-slate-300 mb-1">Jarak PB</label>
                                    <select name="pb_distance" id="enroll_pb_distance" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none" onchange="calculatePreviewVDOT()">
                                        <option value="5k">5K (5.000m)</option>
                                        <option value="10k">10K (10.000m)</option>
                                        <option value="21k">Half Marathon (21.097m)</option>
                                        <option value="42k">Full Marathon (42.195m)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="enroll_pb_time" class="block text-xs font-medium text-slate-300 mb-1">Waktu PB (MM:SS / HH:MM:SS)</label>
                                    <input type="text" name="pb_time" id="enroll_pb_time" placeholder="misal 22:30" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none font-mono" oninput="calculatePreviewVDOT()">
                                </div>
                            </div>
                        </div>

                        <div id="sec-vdot-balke" class="space-y-1 hidden">
                            <label for="enroll_pb_balke" class="block text-xs font-medium text-slate-300">Jarak Tempuh Balke 15 Menit (Meter)</label>
                            <input type="number" name="pb_balke" id="enroll_pb_balke" placeholder="misal 3100" min="100" max="10000" class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none font-mono" oninput="calculatePreviewVDOT()">
                        </div>

                        <!-- Preview Box -->
                        <div id="vdot-preview-box" class="bg-slate-950 border border-slate-800 rounded-md p-2.5 flex justify-between items-center text-xs hidden">
                            <span class="text-slate-400">Estimasi Skor VDOT:</span>
                            <span class="text-white font-semibold font-mono" id="vdot-preview-val">-</span>
                        </div>
                    </div>
                </div>

                <!-- Footer (Fixed Bottom) -->
                <div class="flex justify-end gap-2 p-4 border-t border-slate-800 bg-slate-900 shrink-0 rounded-b-lg">
                    <button type="button" onclick="closeEnrollModal()" class="px-4 py-2 text-xs font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition">
                        Daftarkan Atlet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import CSV/JSON Modal -->
<div id="importModal" class="fixed inset-0 z-[110] hidden overflow-y-auto p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/70" onclick="closeImportModal()"></div>
    <div class="flex min-h-full items-center justify-center relative pointer-events-none">
        <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-lg shadow-2xl transition-all duration-200 scale-95 opacity-0 transform my-auto max-h-[calc(100vh-2.5rem)] flex flex-col" id="importModalContent">
            
            <!-- Header -->
            <div class="flex justify-between items-start p-5 pb-4 border-b border-slate-800 shrink-0 bg-slate-900 rounded-t-lg">
                <div>
                    <h3 class="text-base font-semibold text-white">Import Data Atlet</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daftarkan beberapa atlet sekaligus melalui berkas CSV atau JSON.</p>
                </div>
                <button onclick="closeImportModal()" class="text-slate-400 hover:text-white transition text-lg">
                    &times;
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('coach.athletes.import-enroll') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                <div class="p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                    <div>
                        <label for="import_program_id" class="block text-xs font-medium text-slate-300 mb-1">Pilih Program Latihan</label>
                        <select name="program_id" id="import_program_id" required class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                                    {{ $program->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">Upload Berkas (CSV / JSON)</label>
                        <div class="border border-dashed border-slate-700 rounded-md p-6 text-center hover:border-slate-500 transition relative cursor-pointer group bg-slate-950">
                            <input type="file" name="file" id="import_file" required accept=".csv,.json" class="absolute inset-0 opacity-0 cursor-pointer" onchange="updateFileName(this)">
                            <p class="text-xs text-slate-300 font-medium" id="file_label">Klik atau letakkan berkas CSV / JSON di sini</p>
                            <p class="text-xs text-slate-400 mt-1 font-mono">Ukuran maksimal: 2MB</p>
                        </div>
                    </div>

                    <div class="bg-slate-950 border border-slate-800 rounded-md p-3 text-xs space-y-1.5">
                        <p class="font-semibold text-white">Panduan Format Kolom:</p>
                        <p class="text-slate-400">Berkas CSV harus memiliki header kolom: <code class="text-slate-200 font-mono">name</code>, <code class="text-slate-200 font-mono">email</code>, <code class="text-slate-200 font-mono">phone</code>, <code class="text-slate-200 font-mono">vdot</code>, <code class="text-slate-200 font-mono">start_date</code>.</p>
                        <div class="pt-1">
                            <a href="{{ route('coach.athletes.import-template') }}" class="text-sky-400 hover:underline font-medium text-xs">
                                Unduh Contoh Template CSV &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 p-4 border-t border-slate-800 bg-slate-900 shrink-0 rounded-b-lg">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2 text-xs font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700 transition">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition">
                        Mulai Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Program Reminder Modal -->
<div id="reminderModal" class="fixed inset-0 z-[110] hidden overflow-y-auto p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/70" onclick="closeReminderModal()"></div>
    <div class="flex min-h-full items-center justify-center relative pointer-events-none">
        <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-md shadow-2xl transition-all duration-200 scale-95 opacity-0 transform my-auto max-h-[calc(100vh-2.5rem)] flex flex-col" id="reminderModalContent">
            
            <div class="flex justify-between items-start p-5 pb-4 border-b border-slate-800 shrink-0 bg-slate-900 rounded-t-lg">
                <div>
                    <h3 class="text-base font-semibold text-white">Kirim Pengingat Latihan</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Kirim pesan pengingat jadwal sesi ke atlet</p>
                </div>
                <button onclick="closeReminderModal()" class="text-slate-400 hover:text-white transition text-lg">
                    &times;
                </button>
            </div>

            <form id="reminderForm" onsubmit="submitReminderForm(event)" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                <input type="hidden" name="enrollment_id" id="reminder_enrollment_id">

                <div class="p-5 space-y-4 overflow-y-auto flex-1 min-h-0">
                    <div>
                        <label for="reminder_channel" class="block text-xs font-medium text-slate-300 mb-1">Saluran Pengiriman</label>
                        <select name="channel" id="reminder_channel" required class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                            <option value="both">WhatsApp & Email</option>
                            <option value="wa">WhatsApp Saja</option>
                            <option value="email">Email Saja</option>
                        </select>
                    </div>

                    <div>
                        <label for="reminder_custom_message" class="block text-xs font-medium text-slate-300 mb-1">Pesan Pengingat (Opsional)</label>
                        <textarea name="custom_message" id="reminder_custom_message" rows="3" placeholder="Tulis pesan khusus jika ada..."
                            class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none resize-none leading-relaxed"></textarea>
                    </div>

                    <div id="reminder-error-msg" class="hidden text-rose-300 text-xs bg-rose-950 border border-rose-800 rounded-md p-2.5"></div>
                    <div id="reminder-success-msg" class="hidden text-emerald-300 text-xs bg-emerald-950 border border-emerald-800 rounded-md p-2.5"></div>
                </div>

                <div class="flex justify-end gap-2 p-4 border-t border-slate-800 bg-slate-900 shrink-0 rounded-b-lg">
                    <button type="button" onclick="closeReminderModal()" class="px-4 py-2 text-xs font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700 transition">
                        Batal
                    </button>
                    <button type="submit" id="reminder-submit-btn" class="px-4 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition">
                        Kirim Pengingat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Send Login Access Modal -->
<div id="sendAccessModal" class="fixed inset-0 z-[110] hidden overflow-y-auto p-3 sm:p-4">
    <div class="fixed inset-0 bg-black/70" onclick="closeSendAccessModal()"></div>
    <div class="flex min-h-full items-center justify-center relative pointer-events-none">
        <div class="pointer-events-auto relative bg-slate-900 border border-slate-800 rounded-lg w-full max-w-md shadow-2xl transition-all duration-200 scale-95 opacity-0 transform my-auto max-h-[calc(100vh-2.5rem)] flex flex-col" id="sendAccessModalContent">
            
            <div class="flex justify-between items-start p-5 pb-4 border-b border-slate-800 shrink-0 bg-slate-900 rounded-t-lg">
                <div>
                    <h3 class="text-base font-semibold text-white">Kirim Akses Login</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Informasi login akun atlet: <span id="sendaccess-runner-name" class="font-semibold text-white"></span></p>
                </div>
                <button onclick="closeSendAccessModal()" class="text-slate-400 hover:text-white transition text-lg">
                    &times;
                </button>
            </div>

            <form id="sendAccessForm" onsubmit="submitSendAccessForm(event)" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                @csrf
                <input type="hidden" id="sendaccess_enrollment_id">

                <div class="p-5 space-y-3 overflow-y-auto flex-1 min-h-0 text-xs">
                    <div class="p-3 bg-slate-950 border border-slate-800 rounded-md space-y-1 text-slate-300">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">Halaman Login:</span>
                            <span class="font-mono text-slate-200 font-medium">/login</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">Email / Username:</span>
                            <span id="sendaccess-username" class="font-mono text-white font-medium"></span>
                        </div>
                    </div>

                    <div>
                        <label for="sendaccess_channel" class="block text-xs font-medium text-slate-300 mb-1">Saluran Pengiriman</label>
                        <select name="channel" id="sendaccess_channel" required class="w-full bg-slate-950 border border-slate-800 text-white text-xs rounded-md p-2.5 outline-none">
                            <option value="both">WhatsApp & Email</option>
                            <option value="wa">WhatsApp Saja</option>
                            <option value="email">Email Saja</option>
                        </select>
                    </div>

                    <div id="sendaccess-error-msg" class="hidden text-rose-300 text-xs bg-rose-950 border border-rose-800 rounded-md p-2.5"></div>
                    <div id="sendaccess-success-msg" class="hidden text-emerald-300 text-xs bg-emerald-950 border border-emerald-800 rounded-md p-2.5"></div>
                </div>

                <div class="flex justify-end gap-2 p-4 border-t border-slate-800 bg-slate-900 shrink-0 rounded-b-lg">
                    <button type="button" onclick="closeSendAccessModal()" class="px-4 py-2 text-xs font-medium text-slate-300 bg-slate-800 border border-slate-700 rounded-md hover:bg-slate-700 transition">
                        Batal
                    </button>
                    <button type="submit" id="sendaccess-submit-btn" class="px-4 py-2 text-xs font-semibold text-dark bg-neon rounded-md hover:bg-white transition">
                        Kirim Akses Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('athletes-list-container');
    if (!listContainer) return;

    // Desktop elements
    const desktopForm = document.querySelector('.hidden.md\\:block form');
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
            if (mobileSheet) mobileSheet.classList.add('translate-y-full');
            submitFilters();
        });
    }

    // Interactive event listeners: Auto-filter on select change
    const selects = document.querySelectorAll('form select');
    selects.forEach(select => {
        select.addEventListener('change', function() {
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
                allBtn.className = "pb-2.5 font-semibold text-white border-b-2 border-neon transition relative";
                allBtn.textContent = 'Semua Atlet';
                clustersBtn.className = "pb-2.5 font-semibold text-slate-400 hover:text-slate-200 transition relative";
                clustersBtn.textContent = 'Pengelompokan VDOT (Clusters)';
            } else {
                clustersBtn.className = "pb-2.5 font-semibold text-white border-b-2 border-neon transition relative";
                clustersBtn.textContent = 'Pengelompokan VDOT (Clusters)';
                allBtn.className = "pb-2.5 font-semibold text-slate-400 hover:text-slate-200 transition relative";
                allBtn.textContent = 'Semua Atlet';
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
            fileLabel.classList.add('text-white');
        } else {
            fileLabel.textContent = 'Klik atau letakkan berkas CSV / JSON di sini';
            fileLabel.classList.remove('text-white');
        }
    };

    // Set VDOT Mode Tabs
    window.setVdotMode = function(mode) {
        document.getElementById('enroll_vdot_mode').value = mode;

        const btnDirect = document.getElementById('btn-vdot-direct');
        const btnPb = document.getElementById('btn-vdot-pb');
        const btnBalke = document.getElementById('btn-vdot-balke');

        [btnDirect, btnPb, btnBalke].forEach(btn => {
            btn.className = "py-1.5 px-2 font-medium rounded-md transition text-slate-400 hover:text-white truncate";
        });

        const activeBtn = document.getElementById('btn-vdot-' + mode);
        if (activeBtn) {
            activeBtn.className = "py-1.5 px-2 font-medium rounded-md transition bg-slate-800 text-white truncate";
        }

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

            let meters = 5000;
            let ratio = 0.957;
            if (distance === '5k') { meters = 5000; ratio = 0.957; }
            else if (distance === '10k') { meters = 10000; ratio = 0.915; }
            else if (distance === '21k') { meters = 21097.5; ratio = 0.865; }
            else if (distance === '42k') { meters = 42195; ratio = 0.815; }

            const velocity = meters / totalSeconds;
            const velocityMin = velocity * 60;

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
                    dropdown.innerHTML = '<div class="px-4 py-3 text-xs text-slate-400 text-center">Tidak ada atlet ditemukan</div>';
                } else {
                    dropdown.innerHTML = users.map(u => `
                        <button type="button"
                            class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-slate-800 transition text-left"
                            onclick="selectEnrollUser(${JSON.stringify(u).replace(/"/g, '&quot;')})">
                            <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center font-semibold text-xs ${
                                u.avatar ? '' : 'bg-slate-800 border border-slate-700 text-slate-200'
                            }">
                                ${u.avatar
                                    ? `<img src="${u.avatar}" class="w-8 h-8 rounded-full object-cover">`
                                    : u.initials}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-white text-xs font-semibold truncate">${u.name}</div>
                                <div class="text-slate-400 text-xs truncate">${u.email}</div>
                            </div>
                            ${u.phone ? `<div class="text-slate-400 text-xs font-mono flex-shrink-0">${u.phone}</div>` : ''}
                        </button>
                    `).join('<div class="border-t border-slate-800"></div>');
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
        document.getElementById('enroll_existing_user_id').value = user.id;
        document.getElementById('enroll_name').value = user.name;
        document.getElementById('enroll_email').value = user.email;
        document.getElementById('enroll_phone').value = user.phone || '';

        document.getElementById('enroll_name').readOnly = true;
        document.getElementById('enroll_name').classList.add('opacity-60', 'cursor-not-allowed');
        document.getElementById('enroll_email').readOnly = true;
        document.getElementById('enroll_email').classList.add('opacity-60', 'cursor-not-allowed');

        const badge = document.getElementById('enroll-selected-user');
        badge.classList.remove('hidden');
        document.getElementById('enroll-selected-name').textContent = user.name;
        document.getElementById('enroll-selected-email').textContent = user.email;
        document.getElementById('enroll-selected-avatar').textContent = user.initials;

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

    document.addEventListener('click', function(e) {
        const wrap = document.getElementById('enroll-search-wrap');
        if (wrap && !wrap.contains(e.target)) {
            const dd = document.getElementById('enroll-user-dropdown');
            if (dd) dd.classList.add('hidden');
        }
    });

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

    // ── Send Login Access Modal ──────────────────────────────────────
    const sendAccessModal = document.getElementById('sendAccessModal');
    const sendAccessModalContent = document.getElementById('sendAccessModalContent');

    window.openSendAccessModal = function(enrollmentId, runnerName, runnerEmail, runnerPhone) {
        if (!sendAccessModal || !sendAccessModalContent) return;
        document.getElementById('sendaccess_enrollment_id').value = enrollmentId;
        document.getElementById('sendaccess-runner-name').textContent = runnerName;
        document.getElementById('sendaccess-username').textContent = runnerEmail || runnerPhone || '-';
        document.getElementById('sendaccess_channel').value = runnerPhone ? 'both' : 'email';
        document.getElementById('sendaccess-error-msg').classList.add('hidden');
        document.getElementById('sendaccess-success-msg').classList.add('hidden');

        sendAccessModal.classList.remove('hidden');
        setTimeout(() => {
            sendAccessModalContent.classList.remove('scale-95', 'opacity-0');
            sendAccessModalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    };

    window.closeSendAccessModal = function() {
        if (!sendAccessModal || !sendAccessModalContent) return;
        sendAccessModalContent.classList.remove('scale-100', 'opacity-100');
        sendAccessModalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            sendAccessModal.classList.add('hidden');
        }, 300);
    };

    window.submitSendAccessForm = async function(e) {
        e.preventDefault();
        const enrollmentId = document.getElementById('sendaccess_enrollment_id').value;
        const channel = document.getElementById('sendaccess_channel').value;
        const submitBtn = document.getElementById('sendaccess-submit-btn');
        const errorMsg = document.getElementById('sendaccess-error-msg');
        const successMsg = document.getElementById('sendaccess-success-msg');

        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengirim...';
        errorMsg.classList.add('hidden');
        successMsg.classList.add('hidden');

        try {
            const response = await fetch(`/coach/athletes/${enrollmentId}/send-login-access`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ channel: channel })
            });

            const data = await response.json();
            if (data.success) {
                successMsg.textContent = data.message || 'Akses login berhasil dikirim!';
                successMsg.classList.remove('hidden');

                if (data.wa_url && (channel === 'wa' || channel === 'both')) {
                    window.open(data.wa_url, '_blank');
                }

                setTimeout(() => {
                    closeSendAccessModal();
                }, 1800);
            } else {
                errorMsg.textContent = data.message || 'Gagal mengirim akses login.';
                errorMsg.classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            errorMsg.textContent = 'Terjadi kesalahan koneksi.';
            errorMsg.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Kirim Akses Login';
        }
    };

    window.confirmDeleteAthlete = function(enrollmentId, runnerName, programTitle) {
        if (!confirm(`Hapus atlet "${runnerName}" dari program "${programTitle}"?`)) {
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
