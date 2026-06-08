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
            <form action="{{ route('coach.athletes.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-5">
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
                <div class="md:col-span-4">
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
                <div class="md:col-span-3 flex gap-2">
                    <button type="submit" class="flex-1 px-5 py-3 text-sm font-black text-dark bg-neon rounded-xl hover:bg-white transition-all shadow-lg hover:shadow-neon-cyan transform hover:-translate-y-0.5">
                        FILTER
                    </button>
                    <a href="{{ route('coach.athletes.index') }}" id="desktop-reset-btn" class="{{ ($search || $programId) ? '' : 'hidden' }} px-5 py-3 text-sm font-bold text-slate-400 bg-slate-800 rounded-xl hover:bg-slate-700 hover:text-white transition-all border border-slate-700 hover:border-slate-500 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </a>
                </div>
            </form>
        </div>

        <div class="glass-panel rounded-2xl p-4 md:p-6" id="athletes-list-container">
            @include('coach.athletes._list')
        </div>
    </div>
</div>

<!-- Mobile Filter Bottom Sheet -->
<div id="mobileFilterSheet" class="fixed inset-0 z-[100] transition-transform duration-300 transform translate-y-full md:hidden">
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-slate-900 border-t border-slate-800 rounded-t-[2.5rem] p-8 shadow-2xl">
        <div class="w-12 h-1.5 bg-slate-700 rounded-full mx-auto mb-8" onclick="document.getElementById('mobileFilterSheet').classList.add('translate-y-full')"></div>
        
        <h3 class="text-xl font-black text-white italic tracking-tight mb-6">Filter Athletes</h3>
        
        <form action="{{ route('coach.athletes.index') }}" method="GET" class="space-y-6">
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

            <div class="flex gap-3 pt-2">
                <a href="{{ route('coach.athletes.index') }}" id="mobile-reset-btn" class="{{ ($search || $programId) ? '' : 'hidden' }} flex-1 py-4 text-sm font-bold text-slate-400 bg-slate-800 rounded-2xl border border-slate-700 text-center">
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
    const desktopSearchInput = desktopForm ? desktopForm.querySelector('input[name="search"]') : null;
    const desktopProgramSelect = desktopForm ? desktopForm.querySelector('select[name="program_id"]') : null;
    const desktopResetBtn = document.getElementById('desktop-reset-btn');

    // Mobile elements
    const mobileForm = document.querySelector('#mobileFilterSheet form');
    const mobileSearchInput = mobileForm ? mobileForm.querySelector('input[name="search"]') : null;
    const mobileProgramSelect = mobileForm ? mobileForm.querySelector('select[name="program_id"]') : null;
    const mobileResetBtn = document.getElementById('mobile-reset-btn');
    const mobileSheet = document.getElementById('mobileFilterSheet');

    let debounceTimeout = null;

    // Function to perform AJAX fetch
    async function fetchAthletes(url, searchVal, programIdVal) {
        listContainer.style.opacity = '0.5';

        try {
            const urlObj = new URL(url);
            if (searchVal !== undefined) {
                if (searchVal) {
                    urlObj.searchParams.set('search', searchVal);
                } else {
                    urlObj.searchParams.delete('search');
                }
            }
            if (programIdVal !== undefined) {
                if (programIdVal) {
                    urlObj.searchParams.set('program_id', programIdVal);
                } else {
                    urlObj.searchParams.delete('program_id');
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
            const hasFilter = urlObj.searchParams.has('search') || urlObj.searchParams.has('program_id');
            updateResetButtonsVisibility(hasFilter);

        } catch (error) {
            console.error('Error fetching athletes:', error);
        } finally {
            listContainer.style.opacity = '1';
        }
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
            const search = desktopSearchInput ? desktopSearchInput.value : '';
            const programId = desktopProgramSelect ? desktopProgramSelect.value : '';
            fetchAthletes('{{ route("coach.athletes.index") }}', search, programId);
        });
    }

    if (mobileForm) {
        mobileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const search = mobileSearchInput ? mobileSearchInput.value : '';
            const programId = mobileProgramSelect ? mobileProgramSelect.value : '';
            if (mobileSheet) mobileSheet.classList.add('translate-y-full'); // Close sheet
            fetchAthletes('{{ route("coach.athletes.index") }}', search, programId);
        });
    }

    // Interactive event listeners: Auto-filter on select change
    if (desktopProgramSelect) {
        desktopProgramSelect.addEventListener('change', function() {
            const search = desktopSearchInput ? desktopSearchInput.value : '';
            fetchAthletes('{{ route("coach.athletes.index") }}', search, this.value);
            if (mobileProgramSelect) mobileProgramSelect.value = this.value;
        });
    }

    if (mobileProgramSelect) {
        mobileProgramSelect.addEventListener('change', function() {
            const search = mobileSearchInput ? mobileSearchInput.value : '';
            fetchAthletes('{{ route("coach.athletes.index") }}', search, this.value);
            if (desktopProgramSelect) desktopProgramSelect.value = this.value;
        });
    }

    // Debounced search on typing
    const handleSearchInput = function() {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            const searchVal = this.value;
            const programId = desktopProgramSelect ? desktopProgramSelect.value : '';
            fetchAthletes('{{ route("coach.athletes.index") }}', searchVal, programId);

            if (this === desktopSearchInput && mobileSearchInput) {
                mobileSearchInput.value = searchVal;
            } else if (this === mobileSearchInput && desktopSearchInput) {
                desktopSearchInput.value = searchVal;
            }
        }, 500);
    };

    if (desktopSearchInput) {
        desktopSearchInput.addEventListener('input', handleSearchInput);
    }
    if (mobileSearchInput) {
        mobileSearchInput.addEventListener('input', handleSearchInput);
    }

    // Intercept pagination clicks
    listContainer.addEventListener('click', function(e) {
        const targetLink = e.target.closest('.ajax-pagination a, .pagination a');
        if (targetLink) {
            e.preventDefault();
            const url = targetLink.getAttribute('href');
            if (url) {
                const search = desktopSearchInput ? desktopSearchInput.value : '';
                const programId = desktopProgramSelect ? desktopProgramSelect.value : '';
                fetchAthletes(url, search, programId);
            }
        }
    });

    // Reset button handler
    const handleReset = function(e) {
        e.preventDefault();
        if (desktopSearchInput) desktopSearchInput.value = '';
        if (mobileSearchInput) mobileSearchInput.value = '';
        if (desktopProgramSelect) desktopProgramSelect.value = '';
        if (mobileProgramSelect) mobileProgramSelect.value = '';
        if (mobileSheet) mobileSheet.classList.add('translate-y-full');
        fetchAthletes('{{ route("coach.athletes.index") }}', '', '');
    };

    if (desktopResetBtn) desktopResetBtn.addEventListener('click', handleReset);
    if (mobileResetBtn) mobileResetBtn.addEventListener('click', handleReset);
});
</script>
@endpush
