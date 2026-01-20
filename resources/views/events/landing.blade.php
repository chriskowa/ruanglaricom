@extends('layouts.pacerhub')

@section('title', 'Jadwal Lari & Kalender Event Lari Indonesia ' . date('Y'))
@section('description', 'Temukan jadwal event lari terbaru di Indonesia tahun ' . date('Y') . '. Kalender lari lengkap dengan filter kota, kategori jarak (5K, 10K, HM, FM), dan jenis lomba.')

@section('content')
<div class="min-h-screen pt-24 pb-16 px-4 md:px-8 bg-dark relative overflow-hidden font-sans">
    
    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto mb-12 text-center relative z-10" data-aos="fade-down">
        <h1 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter mb-4">
            KALENDER <span class="text-neon">LARI</span>
        </h1>
        <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto">
            Jadwal event lari terlengkap di Indonesia. Temukan race impianmu berikutnya, dari Fun Run hingga Ultra Marathon.
        </p>
    </div>

    <!-- Filter Section -->
    <div class="max-w-7xl mx-auto mb-8 relative z-10" data-aos="fade-up" data-aos-delay="100">
        <div class="bg-card/80 backdrop-blur-md border border-slate-700/50 rounded-2xl p-4 md:p-6 shadow-xl">
            <form id="filter-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-1">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Cari Event</label>
                    <div class="relative">
                        <input type="text" name="search" placeholder="Nama event atau lokasi..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 pl-10 text-white focus:outline-none focus:border-neon transition-colors">
                        <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>

                <!-- Month & Year -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Waktu</label>
                    <div class="flex gap-2">
                        <select name="month" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                            <option value="">Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endforeach
                        </select>
                        <select name="year" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                            <option value="">Tahun</option>
                            @foreach(range(date('Y'), date('Y') + 1) as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- City -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi</label>
                    <select name="city_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Race Type -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Lomba</label>
                    <select name="race_type_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Jenis</option>
                        @foreach($raceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Distance -->
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kategori Jarak</label>
                    <select name="race_distance_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:outline-none focus:border-neon transition-colors">
                        <option value="">Semua Jarak</option>
                        @foreach($raceDistances as $distance)
                            <option value="{{ $distance->id }}">{{ $distance->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Event List -->
    <div class="max-w-7xl mx-auto relative z-10">
        <div id="events-container" class="space-y-4">
            @include('events.partials.list', ['events' => $events])
        </div>
        
        <div id="pagination-container" class="mt-8">
            {{ $events->links() }}
        </div>

        <!-- Loading State -->
        <div id="loading-indicator" class="hidden py-12 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-neon"></div>
            <p class="mt-2 text-slate-400 text-sm">Memuat jadwal...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filter-form');
        const container = document.getElementById('events-container');
        const paginationContainer = document.getElementById('pagination-container');
        const loading = document.getElementById('loading-indicator');
        let timeout = null;

        function fetchEvents(url = "{{ route('events.index') }}") {
            // Show loading
            container.classList.add('opacity-50');
            loading.classList.remove('hidden');

            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            // If url already has params (pagination), append filter params
            if (url.includes('?')) {
                // Extract base url and existing params
                const [baseUrl, existingQuery] = url.split('?');
                const existingParams = new URLSearchParams(existingQuery);
                
                // Merge params (filter params override pagination params if needed, but usually we want to keep page if only paginating, reset page if filtering)
                // Actually, usually when filtering, we want to reset to page 1.
                // But if clicking pagination link, we want to keep filter params.
                
                // Case 1: Filter changed -> url is base route -> use params
                // Case 2: Pagination clicked -> url has ?page=X -> merge params
                
                for(let [key, value] of existingParams) {
                    if(key === 'page') params.set('page', value);
                }
            }

            fetch(`${url.split('?')[0]}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                container.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;
                
                // Re-attach pagination listeners
                attachPaginationListeners();
            })
            .finally(() => {
                container.classList.remove('opacity-50');
                loading.classList.add('hidden');
            });
        }

        function attachPaginationListeners() {
            document.querySelectorAll('#pagination-container a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchEvents(this.href);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        // Filter change events
        form.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', () => fetchEvents());
        });

        // Search debounce
        form.querySelector('input[name="search"]').addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fetchEvents(), 500);
        });

        // Initial listeners
        attachPaginationListeners();
    });
</script>
@endpush
@endsection
