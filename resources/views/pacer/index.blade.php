@extends('layouts.pacerhub')

@section('content')
<div id="pacer-app" class="min-h-screen bg-dark text-white font-sans selection:bg-neon selection:text-dark">
    
    <!-- Hero / Header -->
    <header class="relative pt-32 pb-12 px-4 text-center overflow-hidden">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-neon/10 rounded-full blur-[100px] -z-10"></div>
        <h1 class="text-4xl md:text-6xl font-black tracking-tighter mb-4">
            FIND YOUR <span class="text-neon">RHYTHM</span>
        </h1>
        <p class="text-slate-400 max-w-xl mx-auto text-sm md:text-base">
            Temukan pacer profesional untuk membantu Anda mencapai target waktu terbaik di event lari berikutnya.
        </p>
    </header>

    <main class="max-w-7xl mx-auto px-4 pb-20">
        
        <!-- Mobile Search & Filter Bar -->
        <div class="md:hidden sticky top-24 z-30 mb-6 space-y-3">
            <div class="bg-card/80 backdrop-blur-md border border-slate-700 p-3 rounded-2xl shadow-xl flex gap-3">
                <div class="relative flex-grow">
                    <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="Cari nama..." class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-sm focus:border-neon focus:ring-1 focus:ring-neon outline-none text-white placeholder-slate-500 transition-all">
                    <i class="fa-solid fa-search absolute left-3.5 top-3.5 text-slate-500"></i>
                </div>
                <button @click="showMobileFilter = true" class="bg-slate-800 border border-slate-700 hover:border-neon text-white px-4 rounded-xl flex items-center justify-center transition-colors relative">
                    <i class="fa-solid fa-sliders text-lg"></i>
                    <span v-if="activeFilterCount > 0" class="absolute -top-2 -right-2 bg-neon text-dark text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">@{{ activeFilterCount }}</span>
                </button>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-8 items-start">
            
            <!-- Desktop Sidebar Filters -->
            <aside class="hidden md:block w-72 flex-shrink-0 sticky top-32 space-y-8">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-mono text-neon mb-2 uppercase tracking-widest">Search</label>
                    <div class="relative">
                        <input v-model="filters.search" @input="debouncedSearch" type="text" placeholder="Nama pacer..." class="w-full bg-card border border-slate-700 rounded-xl pl-10 pr-4 py-3 text-sm focus:border-neon outline-none transition-colors">
                        <i class="fa-solid fa-search absolute left-3.5 top-3.5 text-slate-500"></i>
                    </div>
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-xs font-mono text-neon mb-3 uppercase tracking-widest">Category</label>
                    <div class="flex flex-wrap gap-2">
                        <button 
                            v-for="cat in categories" 
                            :key="cat"
                            @click="setCategory(cat)"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-bold border transition-all duration-300',
                                filters.category === cat 
                                    ? 'bg-neon text-dark border-neon shadow-[0_0_15px_rgba(204,255,0,0.3)]' 
                                    : 'bg-slate-800/50 text-slate-400 border-slate-700 hover:border-slate-500 hover:text-white'
                            ]"
                        >
                            @{{ cat }}
                        </button>
                    </div>
                </div>

                <!-- City -->
                <div>
                    <label class="block text-xs font-mono text-neon mb-2 uppercase tracking-widest">City</label>
                    <select v-model="filters.city_id" @change="fetchPacers" class="w-full bg-card border border-slate-700 text-white px-4 py-3 rounded-xl focus:border-neon outline-none text-sm appearance-none cursor-pointer hover:bg-slate-800 transition-colors">
                        <option value="">All Cities</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">@{{ city.name }}</option>
                    </select>
                </div>

                <!-- Pace -->
                <div>
                    <label class="block text-xs font-mono text-neon mb-2 uppercase tracking-widest">Target Pace</label>
                    <input v-model="filters.pace" @input="debouncedSearch" type="text" placeholder="e.g. 5:30" class="w-full bg-card border border-slate-700 rounded-xl px-4 py-3 text-sm focus:border-neon outline-none text-center font-mono placeholder-slate-600">
                </div>

                <!-- PBs -->
                <div class="bg-card/50 rounded-xl p-4 border border-slate-800">
                    <button @click="showAdvanced = !showAdvanced" class="flex items-center justify-between w-full text-xs font-mono text-slate-400 hover:text-neon transition-colors uppercase tracking-widest mb-3">
                        <span>PB Requirements (Max)</span>
                        <i class="fa-solid fa-chevron-down transition-transform duration-300" :class="showAdvanced ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <div v-show="showAdvanced" class="space-y-3 transition-all">
                        <div v-for="(label, key) in pbFields" :key="key">
                            <label class="text-[10px] text-slate-500 uppercase mb-1 block">@{{ label }}</label>
                            <input v-model="filters[key]" @input="debouncedSearch" type="text" placeholder="00:00:00" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-mono text-center focus:border-neon outline-none">
                        </div>
                    </div>
                </div>

                <button v-if="activeFilterCount > 0" @click="resetFilters" class="w-full py-2 text-xs font-bold text-slate-500 hover:text-white transition-colors uppercase tracking-widest">
                    Reset Filters
                </button>
            </aside>

            <!-- Results Grid -->
            <div class="flex-grow w-full">
                <!-- Status Bar -->
                <div class="hidden md:flex justify-between items-center mb-6 pb-4 border-b border-slate-800">
                    <p class="text-slate-400 text-sm">Showing <span class="text-white font-bold">@{{ pacers.length }}</span> professionals</p>
                    <div class="flex gap-2">
                        <!-- Future: Sort options -->
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-for="n in 6" :key="n" class="bg-card rounded-2xl p-4 border border-slate-800 animate-pulse h-96">
                        <div class="bg-slate-800 h-48 rounded-xl mb-4 w-full"></div>
                        <div class="bg-slate-800 h-6 w-3/4 rounded mb-2"></div>
                        <div class="bg-slate-800 h-4 w-1/2 rounded"></div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else-if="pacers.length === 0" class="text-center py-20 bg-card/30 rounded-3xl border border-slate-800 border-dashed">
                    <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-500">
                        <i class="fa-solid fa-person-running text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">No Pacers Found</h3>
                    <p class="text-slate-400 mb-6 max-w-xs mx-auto">Try adjusting your filters or search terms to find what you're looking for.</p>
                    <button @click="resetFilters" class="px-6 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg font-bold transition-colors">Clear All Filters</button>
                </div>

                <!-- Grid -->
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    <div v-for="pacer in pacers" :key="pacer.id" class="group bg-card rounded-2xl overflow-hidden border border-slate-800 hover:border-neon/50 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl relative flex flex-col h-full">
                        
                        <!-- Badge -->
                        <div class="absolute top-3 left-3 z-10">
                            <span :class="[
                                'px-2.5 py-1 rounded-md text-[10px] font-bold border backdrop-blur-md uppercase tracking-wider',
                                pacer.verified 
                                    ? 'bg-green-500/10 text-green-400 border-green-500/20' 
                                    : 'bg-slate-800/80 text-slate-400 border-slate-600'
                            ]">
                                @{{ pacer.verified ? 'Verified' : 'Pacer' }}
                            </span>
                        </div>

                        <!-- Image -->
                        <div class="h-60 overflow-hidden relative bg-slate-900">
                            <div class="absolute inset-0 bg-gradient-to-t from-card via-transparent to-transparent z-10 opacity-80 group-hover:opacity-60 transition-opacity duration-500"></div>
                            
                            <!-- Fallback logic for image handled in computed or template -->
                            <img :src="getPacerImage(pacer)" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-110 opacity-90 group-hover:opacity-100">
                            
                            <div class="absolute bottom-3 right-3 z-20 bg-neon text-dark font-black text-[10px] px-2 py-1 -skew-x-12 shadow-[2px_2px_0px_rgba(0,0,0,0.5)]">
                                @{{ pacer.category }}
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-5 relative z-20 flex flex-col flex-grow">
                            <div class="flex justify-between items-end mb-4 border-b border-slate-800/50 pb-4">
                                <div>
                                    <p class="text-slate-500 text-[10px] uppercase tracking-wider mb-1">Target Pace</p>
                                    <h3 class="font-mono text-3xl font-bold text-white group-hover:text-neon transition-colors">@{{ pacer.pace }}</h3>
                                </div>
                                <div class="text-right">
                                    <p class="text-slate-500 text-[10px] uppercase tracking-wider mb-1">Experience</p>
                                    <p class="font-mono text-sm font-bold text-slate-300">@{{ pacer.total_races }} <span class="text-xs font-normal text-slate-500">Races</span></p>
                                </div>
                            </div>
                            
                            <div class="flex-grow">
                                <h4 class="font-bold text-lg text-white leading-tight mb-1">@{{ pacer.user.name }}</h4>
                                <p v-if="pacer.nickname" class="text-neon text-sm italic font-medium">"@{{ pacer.nickname }}"</p>
                                <div v-if="pacer.user.city" class="flex items-center gap-1.5 mt-2 text-xs text-slate-400">
                                    <i class="fa-solid fa-location-dot text-slate-600"></i>
                                    @{{ pacer.user.city.name }}
                                </div>
                            </div>

                            <a :href="'/pacer/' + pacer.seo_slug" class="mt-6 w-full block py-3 bg-slate-800 hover:bg-neon hover:text-dark text-white text-center rounded-xl text-xs font-black uppercase tracking-widest transition-all duration-300 group-hover:shadow-[0_0_15px_rgba(204,255,0,0.4)]">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Mobile Filter Drawer (Slide-over) -->
    <div v-if="showMobileFilter" class="fixed inset-0 z-50 md:hidden">
        <!-- Backdrop -->
        <div @click="showMobileFilter = false" class="absolute inset-0 bg-dark/90 backdrop-blur-sm transition-opacity"></div>
        
        <!-- Drawer -->
        <div class="absolute inset-x-0 bottom-0 h-[85vh] bg-card rounded-t-3xl p-6 flex flex-col shadow-2xl animate-[slideUp_0.3s_ease-out]">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">Filters</h3>
                <button @click="showMobileFilter = false" class="p-2 bg-slate-800 rounded-full text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="overflow-y-auto flex-grow space-y-6 pr-2">
                <!-- Mobile Category -->
                <div>
                    <label class="block text-xs font-mono text-neon mb-3 uppercase tracking-widest">Category</label>
                    <div class="flex flex-wrap gap-2">
                        <button v-for="cat in categories" :key="cat" @click="setCategory(cat)" :class="['px-3 py-2 rounded-lg text-xs font-bold border', filters.category === cat ? 'bg-neon text-dark border-neon' : 'bg-slate-800 text-slate-400 border-slate-700']">
                            @{{ cat }}
                        </button>
                    </div>
                </div>

                <!-- Mobile City -->
                <div>
                    <label class="block text-xs font-mono text-neon mb-2 uppercase tracking-widest">City</label>
                    <select v-model="filters.city_id" @change="fetchPacers" class="w-full bg-slate-900 border border-slate-700 text-white px-4 py-3 rounded-xl">
                        <option value="">All Cities</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">@{{ city.name }}</option>
                    </select>
                </div>

                <!-- Mobile Pace -->
                <div>
                    <label class="block text-xs font-mono text-neon mb-2 uppercase tracking-widest">Target Pace</label>
                    <input v-model="filters.pace" @input="debouncedSearch" type="text" placeholder="e.g. 5:30" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white text-center">
                </div>

                <!-- Mobile PBs -->
                <div>
                    <label class="block text-xs font-mono text-neon mb-3 uppercase tracking-widest">PB Requirements</label>
                    <div class="grid grid-cols-2 gap-3">
                        <div v-for="(label, key) in pbFields" :key="key">
                            <label class="text-[10px] text-slate-500 uppercase mb-1 block">@{{ label }}</label>
                            <input v-model="filters[key]" @input="debouncedSearch" type="text" placeholder="00:00:00" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-xs font-mono text-center text-white">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-800 mt-4 flex gap-3">
                <button @click="resetFilters" class="flex-1 py-3 bg-slate-800 text-slate-300 font-bold rounded-xl text-sm">Reset</button>
                <button @click="showMobileFilter = false" class="flex-2 w-2/3 py-3 bg-neon text-dark font-black rounded-xl text-sm shadow-neon-cyan">
                    Show Results (@{{ pacers.length }})
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    const { createApp, ref, reactive, watch, computed, onMounted } = Vue;

    createApp({
        setup() {
            const pacers = ref(@json($pacers));
            const cities = ref(@json($cities));
            const loading = ref(false);
            const showMobileFilter = ref(false);
            const showAdvanced = ref(false);

            const categories = ['All', 'HM (21K)', 'FM (42K)', '10K'];
            const pbFields = {
                'pb_5k': '5K PB',
                'pb_10k': '10K PB',
                'pb_hm': 'Half Marathon',
                'pb_fm': 'Full Marathon'
            };

            const filters = reactive({
                search: '{{ request("search") }}',
                city_id: '{{ request("city_id") }}',
                category: '{{ request("category", "All") }}',
                pace: '{{ request("pace") }}',
                pb_5k: '{{ request("pb_5k") }}',
                pb_10k: '{{ request("pb_10k") }}',
                pb_hm: '{{ request("pb_hm") }}',
                pb_fm: '{{ request("pb_fm") }}',
            });

            // Clean up nulls
            Object.keys(filters).forEach(key => {
                if(filters[key] === 'null') filters[key] = '';
            });

            const activeFilterCount = computed(() => {
                let count = 0;
                if(filters.search) count++;
                if(filters.city_id) count++;
                if(filters.category !== 'All') count++;
                if(filters.pace) count++;
                Object.keys(pbFields).forEach(k => { if(filters[k]) count++; });
                return count;
            });

            let debounceTimer;
            const debouncedSearch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    fetchPacers();
                }, 500);
            };

            const setCategory = (cat) => {
                filters.category = cat;
                fetchPacers();
            };

            const resetFilters = () => {
                filters.search = '';
                filters.city_id = '';
                filters.category = 'All';
                filters.pace = '';
                filters.pb_5k = '';
                filters.pb_10k = '';
                filters.pb_hm = '';
                filters.pb_fm = '';
                fetchPacers();
            };

            const fetchPacers = async () => {
                loading.value = true;
                try {
                    // Build query string
                    const params = new URLSearchParams();
                    Object.keys(filters).forEach(key => {
                        if (filters[key]) params.append(key, filters[key]);
                    });

                    const response = await fetch(`{{ route('pacer.index') }}?` + params.toString(), {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        pacers.value = data.pacers;
                        // Update URL without reload
                        window.history.replaceState({}, '', `{{ route('pacer.index') }}?` + params.toString());
                    }
                } catch (error) {
                    console.error('Error fetching pacers:', error);
                } finally {
                    loading.value = false;
                }
            };

            const getPacerImage = (pacer) => {
                if (pacer.user.avatar) {
                    return `{{ asset('storage') }}/${pacer.user.avatar}`;
                }
                return pacer.user.gender === 'female' 
                    ? `{{ asset('images/default-female.svg') }}` 
                    : `{{ asset('images/default-male.svg') }}`;
            };

            return {
                pacers, cities, filters, loading, 
                showMobileFilter, showAdvanced,
                categories, pbFields, activeFilterCount,
                debouncedSearch, setCategory, resetFilters, fetchPacers, getPacerImage
            };
        }
    }).mount('#pacer-app');
</script>

<style>
    @keyframes slideUp {
        from { transform: translateY(100%); }
        to { transform: translateY(0); }
    }
    /* Custom scrollbar for sidebar */
    aside::-webkit-scrollbar { width: 4px; }
    aside::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
</style>
@endsection
