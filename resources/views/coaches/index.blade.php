@extends('layouts.pacerhub')

@section('content')
<div class="min-h-screen pt-24 pb-12 px-4 md:px-8 relative overflow-hidden">
    <!-- Background Effects -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
        <div class="absolute top-20 right-10 w-96 h-96 bg-neon/5 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-20 left-10 w-72 h-72 bg-blue-500/10 rounded-full blur-[100px]"></div>
    </div>

    <div class="max-w-7xl mx-auto relative z-10">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
            <div data-aos="fade-right">
                <h1 class="text-4xl md:text-6xl font-black text-white italic tracking-tighter mb-2">
                    FIND YOUR <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400 pr-2">COACH</span>
                </h1>
                <p class="text-slate-400 text-lg max-w-xl">
                    Latih potensi terbaikmu dengan bimbingan pelatih profesional.
                </p>
            </div>
            
            <!-- Search & Filter Mobile Toggle -->
            <div class="w-full md:w-auto flex gap-3" data-aos="fade-left">
                <!-- Search -->
                <div class="relative w-full md:w-80">
                    <input type="text" id="searchInput" value="{{ request('search') }}" placeholder="Cari coach..." class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-5 py-3 text-white focus:outline-none focus:border-neon focus:ring-1 focus:ring-neon transition-all placeholder-slate-600">
                    <button type="button" onclick="applyFilters()" class="absolute right-3 top-3 text-slate-500 hover:text-white">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters & Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:col-span-1 space-y-6" data-aos="fade-up" data-aos-delay="100">
                <div class="bg-card/50 backdrop-blur-md border border-slate-800 rounded-2xl p-6 sticky top-24">
                    <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                        Filters
                    </h3>

                    <!-- Location Filter -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Lokasi</label>
                        <select id="cityFilter" onchange="applyFilters()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:border-neon outline-none">
                            <option value="">Semua Kota</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rating Filter -->
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Rating Minimal</label>
                        <div class="space-y-2">
                            @foreach([5, 4, 3] as $star)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="rating" value="{{ $star }}" {{ request('rating') == $star ? 'checked' : '' }} onchange="applyFilters()" class="hidden rating-filter">
                                <div class="w-5 h-5 rounded border flex items-center justify-center transition-colors rating-box {{ request('rating') == $star ? 'border-neon bg-neon text-dark' : 'border-slate-700 group-hover:border-slate-500' }}" data-value="{{ $star }}">
                                    @if(request('rating') == $star) <div class="w-2.5 h-2.5 rounded-full bg-dark"></div> @endif
                                </div>
                                <div class="flex items-center gap-1 text-yellow-500">
                                    <span class="text-sm font-bold text-white mr-1">{{ $star }}+</span>
                                    @for($i=0; $i<$star; $i++) <i class="fas fa-star text-xs"></i> @endfor
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <button onclick="resetFilters()" class="block w-full py-3 rounded-xl border border-slate-700 text-center text-slate-400 text-sm hover:border-white hover:text-white transition-all">
                        Reset Filters
                    </button>
                </div>
            </div>

            <!-- Coaches Grid -->
            <div class="lg:col-span-3">
                <div id="coaches-list-container" class="relative min-h-[400px]">
                    @include('coaches.partials.list')
                </div>
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="absolute inset-0 bg-dark/50 z-20 hidden flex items-center justify-center rounded-3xl backdrop-blur-sm">
                    <div class="loader"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let typingTimer;
    const doneTypingInterval = 500; // 0.5s

    // Search Input Debounce
    document.getElementById('searchInput').addEventListener('keyup', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(applyFilters, doneTypingInterval);
    });

    function applyFilters(url = null) {
        // Show Loading
        const container = document.getElementById('coaches-list-container');
        container.style.opacity = '0.5';
        
        // Gather Params
        const search = document.getElementById('searchInput').value;
        const city_id = document.getElementById('cityFilter').value;
        const rating = document.querySelector('input[name="rating"]:checked')?.value || '';

        // Build URL
        let fetchUrl = url || "{{ route('coaches.index') }}";
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (city_id) params.append('city_id', city_id);
        if (rating) params.append('rating', rating);
        
        // If url already has params (pagination), merge them? 
        // Actually, pagination links usually include all current params if we use withQueryString().
        // But here we are building params from current state.
        // If 'url' is provided (from pagination click), use it directly but make sure it preserves other filters?
        // Laravel's withQueryString() handles this in the link generation.
        // So if url is provided, we just use it.
        // EXCEPT if the user changed a filter AND clicked page 2 (unlikely).
        
        if (!url) {
            fetchUrl = `${fetchUrl}?${params.toString()}`;
        }

        // Update Browser URL (pushState)
        window.history.pushState({path: fetchUrl}, '', fetchUrl);

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            container.style.opacity = '1';
            updateRatingUI(rating);
            AOS.refresh(); // Refresh animations
        })
        .catch(err => {
            console.error('Error:', err);
            container.style.opacity = '1';
        });
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('cityFilter').value = '';
        const ratingRadios = document.querySelectorAll('input[name="rating"]');
        ratingRadios.forEach(r => r.checked = false);
        applyFilters();
    }

    function updateRatingUI(selectedRating) {
        // Update the visual radio boxes
        document.querySelectorAll('.rating-box').forEach(box => {
            const val = box.getAttribute('data-value');
            if (val == selectedRating) {
                box.className = 'w-5 h-5 rounded border flex items-center justify-center transition-colors rating-box border-neon bg-neon text-dark';
                box.innerHTML = '<div class="w-2.5 h-2.5 rounded-full bg-dark"></div>';
            } else {
                box.className = 'w-5 h-5 rounded border flex items-center justify-center transition-colors rating-box border-slate-700 group-hover:border-slate-500';
                box.innerHTML = '';
            }
        });
    }

    // Handle Pagination Clicks
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-container a');
        if (link) {
            e.preventDefault();
            const url = link.getAttribute('href');
            applyFilters(url);
        }
    });
</script>
@endpush
@endsection
