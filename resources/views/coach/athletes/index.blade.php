@extends('layouts.pacerhub')
@php
    $withSidebar = true;
@endphp

@section('title', 'My Athletes')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-end mb-8">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase">Monitoring</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">My Athletes</h1>
            </div>
            <!-- Mobile Filter Trigger -->
            <button onclick="document.getElementById('mobileFilterSheet').classList.remove('translate-y-full')" class="md:hidden p-3 rounded-xl bg-slate-800 border border-slate-700 text-neon flex items-center gap-2 font-black text-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                FILTER
            </button>
        </div>

        <!-- Filter Section (Desktop) -->
        <div class="hidden md:block mb-8 bg-slate-900/50 backdrop-blur-md rounded-2xl p-6 border border-slate-800 shadow-lg">
            <form action="{{ route('coach.athletes.index') }}" method="GET" class="space-y-4">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                <div class="grid grid-cols-12 gap-4 items-end">
                    <div class="col-span-4">
                        <label for="search" class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-wider">Search Runner</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none transition-colors group-focus-within:text-neon">
                                <svg class="w-4 h-4 text-slate-500 group-focus-within:text-neon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." 
                                class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 pl-10 placeholder-slate-500 transition-all focus:bg-slate-800/80 focus:shadow-neon-cyan">
                        </div>
                    </div>
                    <div class="col-span-4">
                        <label for="program_id" class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-wider">Filter Program</label>
                        <div class="relative">
                            <select name="program_id" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 appearance-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                                <option value="">All Programs</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                                        {{ $program->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-4">
                        <label for="sort_by" class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-wider">Urutkan</label>
                        <div class="relative">
                            <select name="sort_by" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 appearance-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                                <option value="latest" {{ $sortBy == 'latest' ? 'selected' : '' }}>Pendaftaran Terbaru</option>
                                <option value="vdot_desc" {{ $sortBy == 'vdot_desc' ? 'selected' : '' }}>VDOT Tertinggi</option>
                                <option value="vdot_asc" {{ $sortBy == 'vdot_asc' ? 'selected' : '' }}>VDOT Terendah</option>
                                <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Nama Runner</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-12 gap-4 items-end pt-4 border-t border-slate-800/60">
                    <div class="col-span-3">
                        <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-wider">Range VDOT (Min - Max)</label>
                        <div class="flex gap-2">
                            <input type="number" name="vdot_min" value="{{ $vdotMin }}" placeholder="Min VDOT" step="0.1"
                                class="w-1/2 bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 placeholder-slate-500 transition-all">
                            <input type="number" name="vdot_max" value="{{ $vdotMax }}" placeholder="Max VDOT" step="0.1"
                                class="w-1/2 bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 placeholder-slate-500 transition-all">
                        </div>
                    </div>
                    <div class="col-span-4">
                        <label for="proximity_runner_id" class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-wider">PB Berdekatan Dengan</label>
                        <div class="relative">
                            <select name="proximity_runner_id" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 appearance-none cursor-pointer hover:bg-slate-700/50 transition-colors">
                                <option value="">-- Pilih Runner --</option>
                                @foreach($allCoachAthletes as $athlete)
                                    <option value="{{ $athlete->id }}" {{ $proximityRunnerId == $athlete->id ? 'selected' : '' }}>
                                        {{ $athlete->name }} (VDOT: {{ round($athlete->vdot, 1) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label for="proximity_diff" class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-wider">Toleransi (VDOT)</label>
                        <input type="number" name="proximity_diff" value="{{ $proximityDiff ?? 3.0 }}" placeholder="±3.0" step="0.1"
                            class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl focus:ring-neon focus:border-neon block p-3 placeholder-slate-500 transition-all">
                    </div>
                    <div class="col-span-3 flex gap-2">
                        <button type="submit" class="flex-1 px-5 py-3 text-sm font-black text-dark bg-neon rounded-xl hover:bg-white transition-all shadow-lg hover:shadow-neon-cyan transform hover:-translate-y-0.5">
                            FILTER
                        </button>
                        <a href="{{ route('coach.athletes.index') }}" id="desktop-reset-btn" class="{{ ($search || $programId || $vdotMin || $vdotMax || $proximityRunnerId) ? '' : 'hidden' }} px-5 py-3 text-sm font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700 hover:text-white transition-all border border-slate-700 hover:border-slate-500 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabs for View Mode -->
        <div class="flex gap-6 mb-6 border-b border-slate-800/80 pb-px">
            <button id="tab-all-btn" onclick="switchTab('all')" class="pb-3 text-sm font-bold transition-all relative {{ $tab === 'all' ? 'text-neon font-black' : 'text-slate-400 hover:text-white' }}">
                All Athletes
                @if($tab === 'all')
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-neon shadow-[0_0_8px_#ccff00]"></div>
                @endif
            </button>
            <button id="tab-clusters-btn" onclick="switchTab('clusters')" class="pb-3 text-sm font-bold transition-all relative {{ $tab === 'clusters' ? 'text-neon font-black' : 'text-slate-400 hover:text-white' }}">
                Smart VDOT Clusters / Groups
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
    <div class="absolute bottom-0 left-0 right-0 bg-slate-900 border-t border-slate-800 rounded-t-[2.5rem] p-8 shadow-2xl overflow-y-auto max-h-[85vh]">
        <div class="w-12 h-1.5 bg-slate-700 rounded-full mx-auto mb-8" onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')"></div>
        
        <h3 class="text-xl font-black text-white italic tracking-tight mb-6">Filter Athletes</h3>
        
        <form action="{{ route('coach.athletes.index') }}" method="GET" class="space-y-6">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest">Search Name/Email</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Type runner name..." 
                    class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-4 focus:ring-neon focus:border-neon transition-all">
            </div>

            <div>
                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest">Select Program</label>
                <select name="program_id" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-4 focus:ring-neon focus:border-neon transition-all appearance-none">
                    <option value="">All Programs</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ $programId == $program->id ? 'selected' : '' }}>
                            {{ $program->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest">Sort By</label>
                <select name="sort_by" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-4 focus:ring-neon focus:border-neon transition-all appearance-none">
                    <option value="latest" {{ $sortBy == 'latest' ? 'selected' : '' }}>Pendaftaran Terbaru</option>
                    <option value="vdot_desc" {{ $sortBy == 'vdot_desc' ? 'selected' : '' }}>VDOT Tertinggi</option>
                    <option value="vdot_asc" {{ $sortBy == 'vdot_asc' ? 'selected' : '' }}>VDOT Terendah</option>
                    <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Nama Runner</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest">Min VDOT</label>
                    <input type="number" name="vdot_min" value="{{ $vdotMin }}" placeholder="Min" step="0.1"
                        class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-4 focus:ring-neon focus:border-neon transition-all">
                </div>
                <div>
                    <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest">Max VDOT</label>
                    <input type="number" name="vdot_max" value="{{ $vdotMax }}" placeholder="Max" step="0.1"
                        class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-4 focus:ring-neon focus:border-neon transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest">PB Berdekatan Dengan</label>
                <select name="proximity_runner_id" class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-4 focus:ring-neon focus:border-neon transition-all appearance-none">
                    <option value="">-- Pilih Runner --</option>
                    @foreach($allCoachAthletes as $athlete)
                        <option value="{{ $athlete->id }}" {{ $proximityRunnerId == $athlete->id ? 'selected' : '' }}>
                            {{ $athlete->name }} (VDOT: {{ round($athlete->vdot, 1) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-mono text-cyan-400 mb-2 uppercase tracking-widest">Toleransi (VDOT)</label>
                <input type="number" name="proximity_diff" value="{{ $proximityDiff ?? 3.0 }}" placeholder="±3.0" step="0.1"
                    class="w-full bg-slate-800 border border-slate-700 text-white text-sm rounded-xl p-4 focus:ring-neon focus:border-neon transition-all">
            </div>

            <div class="flex gap-3 pt-2">
                <a href="{{ route('coach.athletes.index') }}" id="mobile-reset-btn" class="{{ ($search || $programId || $vdotMin || $vdotMax || $proximityRunnerId) ? '' : 'hidden' }} flex-1 py-4 text-sm font-bold text-slate-400 bg-slate-800 rounded-2xl border border-slate-700 text-center">
                    RESET
                </a>
                <button type="submit" class="flex-[2] py-4 text-sm font-black text-dark bg-neon rounded-2xl shadow-lg shadow-neon/20">
                    APPLY FILTER
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
});
</script>
@endpush
