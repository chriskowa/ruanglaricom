@extends('layouts.pacerhub')

@php
    $withSidebar = true;
@endphp

@section('title', 'Master Kupon')

@section('content')
<div class="min-h-screen pt-20 pb-10 px-4 md:px-8 relative overflow-hidden font-sans">
    
    <!-- Header -->
    <div class="mb-8 relative z-10" data-aos="fade-up">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('eo.dashboard') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-white">
                                Dashboard
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-white md:ml-2">Master Kupon</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    MASTER <span class="text-yellow-400">KUPON</span>
                </h1>
            </div>
            <div>
                <a href="{{ route('eo.coupons.create') }}" class="px-6 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-black font-black transition-all shadow-lg shadow-yellow-500/20 flex items-center gap-2 transform hover:scale-105">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    Buat Kupon Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-slate-800/50 backdrop-blur-md border border-slate-700 rounded-xl p-4 relative z-10">
        <form action="{{ route('eo.coupons.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Cari Kode</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: DISKON50" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-yellow-500 transition-colors">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Filter Event</label>
                <select name="event_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                    <option value="">Semua Event</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-700 hover:bg-slate-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div id="coupons-list-container" class="bg-card/50 backdrop-blur-md border border-slate-700/50 rounded-2xl overflow-hidden relative z-10 transition-opacity duration-200">
        @include('eo.coupons._list')
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('coupons-list-container');
    const filterForm = document.querySelector('form');
    if (!listContainer || !filterForm) return;

    const searchInput = filterForm.querySelector('input[name="search"]');
    const eventSelect = filterForm.querySelector('select[name="event_id"]');
    let debounceTimeout = null;

    async function fetchCoupons(url) {
        listContainer.style.opacity = '0.5';

        try {
            const urlObj = new URL(url);
            
            // Set params from inputs
            if (searchInput && searchInput.value) {
                urlObj.searchParams.set('search', searchInput.value);
            } else {
                urlObj.searchParams.delete('search');
            }

            if (eventSelect && eventSelect.value) {
                urlObj.searchParams.set('event_id', eventSelect.value);
            } else {
                urlObj.searchParams.delete('event_id');
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

        } catch (error) {
            console.error('Error fetching coupons:', error);
        } finally {
            listContainer.style.opacity = '1';
        }
    }

    function submitFilters() {
        fetchCoupons('{{ route("eo.coupons.index") }}');
    }

    // Intercept form submissions
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        submitFilters();
    });

    // Auto-filter on select changes
    if (eventSelect) {
        eventSelect.addEventListener('change', submitFilters);
    }

    // Debounced search on typing
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(submitFilters, 500);
        });
    }

    // Intercept pagination clicks
    listContainer.addEventListener('click', function(e) {
        const targetLink = e.target.closest('.ajax-pagination a, .pagination a');
        if (targetLink) {
            e.preventDefault();
            const url = targetLink.getAttribute('href');
            if (url) {
                fetchCoupons(url);
            }
        }
    });
});
</script>
@endpush
