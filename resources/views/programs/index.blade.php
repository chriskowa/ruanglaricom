@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Marketplace Program Lari')

@section('content')
<div id="programs-app" class="min-h-screen pt-20 pb-10 px-4 md:px-8 font-sans bg-dark text-slate-200" v-cloak>
    
    <!-- Hero Section -->
    <div class="mb-10 relative z-10 mt-10" data-aos="fade-down">
        <div class="text-center max-w-3xl mx-auto">
            <p class="text-neon font-mono text-sm tracking-widest uppercase mb-2">Marketplace</p>
            <h1 class="text-4xl md:text-5xl font-black text-white italic tracking-tighter mb-4">
                FIND YOUR <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400 pr-2">PERFECT PLAN</span>
            </h1>
            <p class="text-slate-400 text-lg">
                Pilih program latihan yang sesuai dengan target dan kemampuanmu. Dibuat oleh pelatih profesional terverifikasi.
            </p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-8 relative z-10">
        
        <!-- Mobile Filter Button -->
        <div class="lg:hidden mb-4">
            <button @click="showMobileFilters = true" class="w-full py-3 bg-slate-800 border border-slate-700 rounded-xl text-white font-bold flex items-center justify-center gap-2 hover:border-neon transition-colors">
                <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                Filter Programs
            </button>
        </div>

        <!-- Sidebar Filters (Desktop) -->
        <aside class="hidden lg:block w-72 shrink-0 space-y-8 sticky top-24 h-fit">
            <!-- Search -->
            <div class="relative">
                <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="Search programs..." class="w-full bg-slate-900/80 border border-slate-700 rounded-xl px-4 py-3 pl-10 text-white focus:border-neon focus:ring-1 focus:ring-neon transition-all placeholder-slate-500">
                <svg class="w-5 h-5 text-slate-500 absolute left-3 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>

            <!-- Categories -->
            <div>
                <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Category</h3>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="radio" v-model="filters.category" value="" class="peer appearance-none w-5 h-5 border-2 border-slate-600 rounded-full checked:border-neon checked:bg-slate-800 transition-colors">
                            <div class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-neon scale-0 peer-checked:scale-100 transition-transform"></div>
                        </div>
                        <span class="text-slate-400 group-hover:text-white transition-colors">All Categories</span>
                    </label>
                    <label v-for="cat in categories" :key="cat.value" class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="radio" v-model="filters.category" :value="cat.value" class="peer appearance-none w-5 h-5 border-2 border-slate-600 rounded-full checked:border-neon checked:bg-slate-800 transition-colors">
                            <div class="absolute inset-0 m-auto w-2.5 h-2.5 rounded-full bg-neon scale-0 peer-checked:scale-100 transition-transform"></div>
                        </div>
                        <span class="text-slate-400 group-hover:text-white transition-colors">@{{ cat.label }}</span>
                    </label>
                </div>
            </div>

            <!-- Difficulty -->
            <div>
                <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Difficulty</h3>
                <div class="flex flex-wrap gap-2">
                    <button v-for="level in difficulties" :key="level.value" 
                        @click="toggleDifficulty(level.value)"
                        :class="filters.difficulty === level.value ? 'bg-neon text-dark border-neon' : 'bg-slate-800 text-slate-400 border-slate-700 hover:border-slate-500'"
                        class="px-3 py-1.5 rounded-lg border text-xs font-bold uppercase transition-all">
                        @{{ level.label }}
                    </button>
                </div>
            </div>

            <!-- Price Range -->
            <div>
                <h3 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Price Range</h3>
                <div class="flex items-center gap-2">
                    <input v-model.number="filters.price_min" @change="fetchPrograms" type="number" placeholder="Min" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon focus:outline-none">
                    <span class="text-slate-500">-</span>
                    <input v-model.number="filters.price_max" @change="fetchPrograms" type="number" placeholder="Max" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-neon focus:outline-none">
                </div>
            </div>

            <!-- Reset -->
            <button @click="resetFilters" class="w-full py-2 text-slate-500 hover:text-white text-sm underline decoration-slate-600 hover:decoration-white transition-all">
                Reset All Filters
            </button>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            
            <!-- Top Bar -->
            <div class="flex flex-wrap justify-between items-center mb-6 gap-4">
                <div class="text-sm text-slate-400">
                    Showing <span class="text-white font-bold">@{{ programs.from || 0 }}-@{{ programs.to || 0 }}</span> of <span class="text-white font-bold">@{{ programs.total || 0 }}</span> programs
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500">Sort by:</span>
                    <select v-model="filters.sort" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-white focus:border-neon focus:outline-none">
                        <option value="newest">Newest</option>
                        <option value="popular">Most Popular</option>
                        <option value="rating">Highest Rated</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                    </select>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="n in 6" :key="n" class="bg-slate-900 rounded-2xl p-4 border border-slate-800 animate-pulse">
                    <div class="h-48 bg-slate-800 rounded-xl mb-4"></div>
                    <div class="h-4 bg-slate-800 rounded w-3/4 mb-2"></div>
                    <div class="h-4 bg-slate-800 rounded w-1/2"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="programs.data && programs.data.length === 0" class="text-center py-20 bg-slate-900/50 rounded-3xl border border-dashed border-slate-800">
                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">No Programs Found</h3>
                <p class="text-slate-400 mb-6">Try adjusting your filters or search terms.</p>
                <button @click="resetFilters" class="px-6 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-colors">
                    Clear Filters
                </button>
            </div>

            <!-- Program Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <div v-for="program in programs.data" :key="program.id" class="group bg-slate-900/50 backdrop-blur-sm border border-slate-800 rounded-2xl overflow-hidden hover:border-neon/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-neon/5 flex flex-col">
                    
                    <!-- Image -->
                    <div class="relative h-48 overflow-hidden">
                        <img :src="program.image_url || 'https://source.unsplash.com/random/400x300/?running'" :alt="program.title" class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent opacity-80"></div>
                        
                        <!-- Badges -->
                        <div class="absolute top-3 right-3 flex flex-col gap-2 items-end">
                            <span class="px-3 py-1 rounded-full bg-slate-900/90 backdrop-blur text-xs font-bold text-white border border-slate-700">
                                @{{ formatCategory(program.distance_target) }}
                            </span>
                            <span :class="getDifficultyColor(program.difficulty)" class="px-3 py-1 rounded-full text-xs font-bold text-dark border border-transparent">
                                @{{ program.difficulty }}
                            </span>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5 flex-1 flex flex-col">
                        <!-- Coach Info -->
                        <div class="flex items-center gap-2 mb-3">
                            <img :src="program.coach?.avatar ? '/storage/' + program.coach.avatar : '/images/profile/17.jpg'" class="w-6 h-6 rounded-full object-cover border border-slate-600">
                            <span class="text-xs text-slate-400">Coach @{{ program.coach?.name || 'Unknown' }}</span>
                        </div>

                        <h3 class="text-lg font-bold text-white mb-2 line-clamp-2 group-hover:text-neon transition-colors">
                            <a :href="'/programs/' + program.slug">@{{ program.title }}</a>
                        </h3>

                        <!-- Rating -->
                        <div class="flex items-center gap-1 mb-4">
                            <div class="flex text-yellow-500 text-xs">
                                <i v-for="i in 5" :key="i" :class="i <= Math.round(program.average_rating || 0) ? 'fas fa-star' : 'far fa-star'"></i>
                            </div>
                            <span class="text-xs text-slate-500">(@{{ program.reviews_count || 0 }})</span>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 gap-2 mb-4 py-3 border-y border-slate-800">
                            <div class="text-center border-r border-slate-800">
                                <p class="text-[10px] text-slate-500 uppercase">Duration</p>
                                <p class="text-sm font-bold text-white">@{{ program.duration_weeks }} Weeks</p>
                            </div>
                            <div class="text-center">
                                <p class="text-[10px] text-slate-500 uppercase">Sessions</p>
                                <p class="text-sm font-bold text-white">@{{ program.sessions_per_week }}/week</p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="mt-auto flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs text-slate-500">Price</p>
                                <p class="text-xl font-black text-white">
                                    @{{ formatPrice(program.price) }}
                                </p>
                            </div>
                            <a :href="'{{ url('/programs') }}/' + program.slug" class="px-4 py-2 bg-white text-dark font-bold rounded-lg hover:bg-neon transition-colors text-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="programs.last_page > 1" class="mt-10 flex justify-center">
                <nav class="flex items-center gap-2">
                    <button @click="changePage(programs.current_page - 1)" :disabled="programs.current_page === 1" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <span class="text-sm text-slate-400 px-2">
                        Page <span class="text-white font-bold">@{{ programs.current_page }}</span> of @{{ programs.last_page }}
                    </span>

                    <button @click="changePage(programs.current_page + 1)" :disabled="programs.current_page === programs.last_page" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </nav>
            </div>
        </div>

    </div>

    <!-- Mobile Filters Slide-over -->
    <div v-if="showMobileFilters" class="fixed inset-0 z-50 lg:hidden">
        <div @click="showMobileFilters = false" class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
        <div class="absolute right-0 top-0 bottom-0 w-80 bg-slate-900 border-l border-slate-800 p-6 overflow-y-auto shadow-2xl transform transition-transform duration-300">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-xl font-black text-white italic">FILTERS</h2>
                <button @click="showMobileFilters = false" class="text-slate-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <!-- Mobile Filter Content (Same as sidebar) -->
            <div class="space-y-8">
                <!-- Search -->
                <div class="relative">
                    <input v-model="filters.search" type="text" placeholder="Search..." class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white">
                </div>

                <!-- Categories -->
                <div>
                    <h3 class="text-white font-bold mb-4 uppercase text-sm">Category</h3>
                    <div class="space-y-3">
                        <label v-for="cat in categories" :key="cat.value" class="flex items-center gap-3">
                            <input type="radio" v-model="filters.category" :value="cat.value" class="accent-neon w-5 h-5">
                            <span class="text-slate-300">@{{ cat.label }}</span>
                        </label>
                    </div>
                </div>

                <!-- Difficulty -->
                <div>
                    <h3 class="text-white font-bold mb-4 uppercase text-sm">Difficulty</h3>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="level in difficulties" :key="level.value" 
                            @click="toggleDifficulty(level.value)"
                            :class="filters.difficulty === level.value ? 'bg-neon text-dark' : 'bg-slate-800 text-slate-400'"
                            class="px-3 py-1.5 rounded-lg border border-transparent text-xs font-bold uppercase">
                            @{{ level.label }}
                        </button>
                    </div>
                </div>

                <button @click="showMobileFilters = false" class="w-full py-3 bg-neon text-dark font-bold rounded-xl mt-8">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref, reactive, watch, onMounted } = Vue;

    createApp({
        setup() {
            const programs = ref(@json($programs));
            const loading = ref(false);
            const showMobileFilters = ref(false);
            
            const filters = reactive({
                search: '{{ request("search") }}',
                category: '{{ request("category") }}',
                difficulty: '{{ request("difficulty") }}',
                rating: '{{ request("rating") }}',
                price_min: '{{ request("price_min") }}',
                price_max: '{{ request("price_max") }}',
                sort: '{{ request("sort", "newest") }}',
                page: 1
            });

            const categories = [
                { label: '5K', value: '5k' },
                { label: '10K', value: '10k' },
                { label: 'Half Marathon (21K)', value: '21k' },
                { label: 'Marathon (42K)', value: '42k' }
            ];

            const difficulties = [
                { label: 'Beginner', value: 'beginner' },
                { label: 'Intermediate', value: 'intermediate' },
                { label: 'Advanced', value: 'advanced' }
            ];

            let debounceTimer;
            const debouncedSearch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filters.page = 1;
                    fetchPrograms();
                }, 500);
            };

            const fetchPrograms = async () => {
                loading.value = true;
                
                // Construct Query String
                const params = new URLSearchParams();
                Object.entries(filters).forEach(([key, value]) => {
                    if (value) params.append(key, value);
                });

                try {
                    const response = await fetch(`{{ route("programs.index") }}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    programs.value = data;
                    
                    // Update Browser URL
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.pushState({}, '', newUrl);
                    
                } catch (error) {
                    console.error('Error fetching programs:', error);
                } finally {
                    loading.value = false;
                }
            };

            // Watchers for immediate filtering
            watch(() => filters.category, () => { filters.page = 1; fetchPrograms(); });
            watch(() => filters.sort, () => { filters.page = 1; fetchPrograms(); });
            
            const toggleDifficulty = (val) => {
                filters.difficulty = filters.difficulty === val ? '' : val;
                filters.page = 1;
                fetchPrograms();
            };

            const changePage = (page) => {
                filters.page = page;
                fetchPrograms();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const resetFilters = () => {
                filters.search = '';
                filters.category = '';
                filters.difficulty = '';
                filters.price_min = '';
                filters.price_max = '';
                filters.sort = 'newest';
                filters.page = 1;
                fetchPrograms();
            };

            // Helpers
            const formatPrice = (price) => {
                if (!price || price == 0) return 'Free';
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
            };

            const formatCategory = (val) => {
                if(!val) return 'General';
                const map = { '5k': '5K', '10k': '10K', '21k': 'Half Marathon', '42k': 'Full Marathon' };
                return map[val] || val.toUpperCase();
            };

            const getDifficultyColor = (diff) => {
                const map = {
                    'beginner': 'bg-green-400',
                    'intermediate': 'bg-yellow-400',
                    'advanced': 'bg-red-400 text-white'
                };
                return map[diff] || 'bg-slate-400';
            };

            return {
                programs,
                filters,
                loading,
                showMobileFilters,
                categories,
                difficulties,
                debouncedSearch,
                fetchPrograms,
                toggleDifficulty,
                changePage,
                resetFilters,
                formatPrice,
                formatCategory,
                getDifficultyColor
            };
        }
    }).mount('#programs-app');
</script>

<style>
    [v-cloak] { display: none; }
</style>
@endpush
