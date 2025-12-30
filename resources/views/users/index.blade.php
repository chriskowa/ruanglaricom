@extends('layouts.pacerhub')

@section('title', $title)

@push('styles')
<script>
    tailwind.config.theme.extend.colors.neon = '#ccff00';
</script>
<style>
    .glass-panel {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    [v-cloak] { display: none; }
</style>
@endpush

@section('content')
<div id="users-app" class="min-h-screen pt-24 pb-20 px-4 md:px-8 font-sans bg-dark text-slate-200" v-cloak>
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-8" data-aos="fade-down">
            <div>
                <p class="text-neon font-mono text-sm tracking-widest uppercase mb-2">Community</p>
                <h1 class="text-3xl md:text-4xl font-black text-white italic tracking-tighter">
                    FIND <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-green-400 pr-2">{{ strtoupper(str_replace('Daftar ', '', $title)) }}</span>
                </h1>
            </div>
            
            <!-- Filter Toggle (Mobile) -->
            <button class="md:hidden px-4 py-2 bg-slate-800 rounded-lg text-white text-sm font-bold border border-slate-700" @click="showFilters = !showFilters">
                Filters
            </button>
        </div>

        <!-- Filters -->
        <div v-show="showFilters" class="glass-panel rounded-2xl p-6 mb-8" data-aos="fade-up">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Search</label>
                    <input v-model="filters.q" @input="debouncedFetch" type="text" placeholder="Name or Email..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all placeholder-slate-600">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Gender</label>
                    <select v-model="filters.gender" @change="fetchUsers" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all">
                        <option value="">All</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Location</label>
                    <select v-model="filters.city_id" @change="fetchUsers" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white focus:border-neon focus:ring-1 focus:ring-neon outline-none transition-all">
                        <option value="">All Cities</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">
                            @{{ city.name }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-3">
                    <button @click="resetFilters" class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-bold rounded-xl transition-colors border border-slate-700">
                        RESET
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="n in 8" :key="n" class="glass-panel rounded-2xl p-6 text-center animate-pulse">
                <div class="w-24 h-24 mx-auto mb-4 bg-slate-800 rounded-full"></div>
                <div class="h-4 bg-slate-800 rounded w-3/4 mx-auto mb-2"></div>
                <div class="h-3 bg-slate-800 rounded w-1/2 mx-auto mb-4"></div>
                <div class="flex justify-center gap-2">
                    <div class="w-8 h-8 bg-slate-800 rounded-lg"></div>
                    <div class="w-8 h-8 bg-slate-800 rounded-lg"></div>
                </div>
            </div>
        </div>

        <!-- User Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="user in users.data" :key="user.id" class="glass-panel rounded-2xl p-6 text-center group hover:border-neon/30 transition-all relative overflow-hidden">
                <!-- Background Glow -->
                <div class="absolute inset-0 bg-gradient-to-b from-neon/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>

                <div class="relative z-10">
                    <!-- Avatar -->
                    <div class="relative w-24 h-24 mx-auto mb-4">
                        <img :src="user.avatar ? (user.avatar.startsWith('/storage') ? (APP_URL + user.avatar) : (APP_URL + '/storage/' + user.avatar)) : (user.gender === 'female' ? defaultFemale : defaultMale)" 
                             loading="lazy"
                             decoding="async"
                             width="96" height="96"
                             class="w-24 h-24 rounded-full object-cover border-2 border-slate-700 group-hover:border-neon transition-colors shadow-xl bg-slate-800" 
                             :alt="user.name">
                        <div v-if="user.role === 'coach'" class="absolute -bottom-1 -right-1 bg-blue-500 text-white text-[10px] font-black px-2 py-0.5 rounded-full border border-dark">
                            COACH
                        </div>
                    </div>

                    <!-- Info -->
                    <h3 class="text-lg font-bold text-white mb-1 truncate">
                        <a :href="user.username ? ('/runner/' + user.username) : '#'" :class="[ 'hover:text-neon transition-colors', !user.username ? 'pointer-events-none opacity-50' : '' ]">@{{ user.name }}</a>
                    </h3>
                    
                    <p v-if="user.city" class="text-xs text-slate-400 mb-3 flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        @{{ user.city.name }}
                    </p>
                    <p v-else class="text-xs text-slate-500 mb-3 italic">Location unknown</p>

                    <!-- Stats / Badges -->
                    <div class="flex justify-center gap-2 mb-4">
                        <span v-if="user.gender" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                            @{{ user.gender == 'male' ? 'MALE' : 'FEMALE' }}
                        </span>
                        <span v-if="role === 'coach' && user.programs_count > 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                            @{{ user.programs_count }} PROGRAMS
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-center gap-2">
                        <template v-if="currentUserId !== user.id">
                            <button v-if="user.is_following" @click="toggleFollow(user)" :disabled="actionLoading === user.id" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-red-500/20 hover:text-red-500 text-slate-400 flex items-center justify-center transition-all disabled:opacity-50" title="Unfollow">
                                <i v-if="actionLoading === user.id" class="fas fa-spinner fa-spin text-xs"></i>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" /></svg>
                            </button>
                            
                            <button v-else @click="toggleFollow(user)" :disabled="actionLoading === user.id" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-neon hover:text-dark text-neon flex items-center justify-center transition-all disabled:opacity-50" title="Follow">
                                <i v-if="actionLoading === user.id" class="fas fa-spinner fa-spin text-xs"></i>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                            </button>
                            
                            <a :href="'/chat/' + user.id" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-blue-500/20 hover:text-blue-400 text-slate-400 flex items-center justify-center transition-all" title="Message">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            </a>
                        </template>
                        
                        <a :href="'/runner/' + (user.username || user.id)" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-white hover:text-dark text-slate-400 flex items-center justify-center transition-all" title="View Profile">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && users.data.length === 0" class="col-span-full py-20 text-center">
            <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-600">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">No users found</h3>
            <p class="text-slate-400">Try adjusting your filters to find more people.</p>
        </div>

        <!-- Pagination -->
        <div v-if="users.last_page > 1" class="mt-12 flex justify-center">
            <nav class="flex items-center gap-2">
                <button @click="changePage(users.current_page - 1)" :disabled="users.current_page === 1" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <span class="text-sm text-slate-400 px-2">
                    Page <span class="text-white font-bold">@{{ users.current_page }}</span> of @{{ users.last_page }}
                </span>

                <button @click="changePage(users.current_page + 1)" :disabled="users.current_page === users.last_page" class="w-10 h-10 flex items-center justify-center rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </nav>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref, reactive, onMounted, watch } = Vue;

    createApp({
        setup() {
            const users = ref(@json($users));
            const cities = ref(@json($cities));
            const role = ref('{{ $role }}');
            const currentUserId = {{ auth()->id() ?? 'null' }};
            const loading = ref(false);
            const showFilters = ref(window.innerWidth >= 768);
            const actionLoading = ref(null);
            const APP_URL = "{{ url('/') }}";
            
            const defaultMale = "{{ asset('images/default-male.svg') }}";
            const defaultFemale = "{{ asset('images/default-female.svg') }}";

            const filters = reactive({
                q: '{{ request("q") }}',
                gender: '{{ request("gender") }}',
                city_id: '{{ request("city_id") }}',
                page: 1
            });

            let debounceTimer;
            const debouncedFetch = () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filters.page = 1;
                    fetchUsers();
                }, 500);
            };

            const fetchUsers = async () => {
                loading.value = true;
                
                const params = new URLSearchParams();
                if (filters.q) params.append('q', filters.q);
                if (filters.gender) params.append('gender', filters.gender);
                if (filters.city_id) params.append('city_id', filters.city_id);
                if (filters.page > 1) params.append('page', filters.page);
                
                try {
                    const response = await fetch(`${window.location.pathname}?${params.toString()}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    users.value = data;
                    
                    // Update URL
                    const newUrl = `${window.location.pathname}?${params.toString()}`;
                    window.history.pushState({}, '', newUrl);
                    
                } catch (error) {
                    console.error('Error fetching users:', error);
                } finally {
                    loading.value = false;
                }
            };

            watch(() => filters.gender, () => { filters.page = 1; fetchUsers(); });
            watch(() => filters.city_id, () => { filters.page = 1; fetchUsers(); });

            const changePage = (page) => {
                filters.page = page;
                fetchUsers();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            };

            const resetFilters = () => {
                filters.q = '';
                filters.gender = '';
                filters.city_id = '';
                filters.page = 1;
                fetchUsers();
            };

            const toggleFollow = async (user) => {
                if (!currentUserId) {
                    window.location.href = '/login';
                    return;
                }

                actionLoading.value = user.id;
                const isFollowing = user.is_following;
                // Corrected endpoints with absolute APP_URL
                const url = APP_URL + (isFollowing ? `/unfollow/${user.id}` : `/follow/${user.id}`);

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        // Update local state
                        user.is_following = !isFollowing;
                    } else {
                        console.error('Action failed');
                    }
                } catch (error) {
                    console.error('Error:', error);
                } finally {
                    actionLoading.value = null;
                }
            };

            return {
                users,
                cities,
                role,
                currentUserId,
                filters,
                loading,
                showFilters,
                actionLoading,
                debouncedFetch,
                fetchUsers,
                changePage,
                resetFilters,
                toggleFollow,
                defaultMale,
                defaultFemale,
                APP_URL
            };
        }
    }).mount('#users-app');
</script>
@endpush
