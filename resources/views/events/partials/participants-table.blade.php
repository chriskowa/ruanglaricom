<script>
    const ParticipantsTableComponent = {
        props: {
            eventSlug: { type: String, required: true },
            categories: { type: Array, required: true },
            fetchUrl: { type: String, required: false, default: '' }
        },
        setup(props) {
            const participants = Vue.ref([]);
            const pagination = Vue.ref({});
            const isLoading = Vue.ref(false);
            const search = Vue.ref('');
            const selectedCategory = Vue.ref('all');
            const selectedGender = Vue.ref('all');
            const selectedAgeGroup = Vue.ref('all');
            
            const fetchParticipants = async (page = 1) => {
                isLoading.value = true;
                try {
                    const params = new URLSearchParams();
                    params.append('page', page);
                    if (search.value) params.append('search', search.value);
                    if (selectedCategory.value !== 'all') params.append('category_id', selectedCategory.value);
                    if (selectedGender.value !== 'all') params.append('gender', selectedGender.value);
                    if (selectedAgeGroup.value !== 'all') params.append('age_group', selectedAgeGroup.value);
                    
                    let url;
                    if (props.fetchUrl) {
                        url = `${props.fetchUrl}?${params.toString()}`;
                    } else {
                        url = (window.rlUrl ? window.rlUrl(`event/${props.eventSlug}/participants-list?${params.toString()}`) : `/event/${props.eventSlug}/participants-list?${params.toString()}`);
                    }
                    
                    const response = await fetch(url);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    
                    // Validate response structure
                    if (!data || !data.data) {
                        participants.value = [];
                        pagination.value = { current_page: 1, last_page: 1, total: 0 };
                        return;
                    }
                    
                    participants.value = Array.isArray(data.data) ? data.data : [];
                    pagination.value = {
                        current_page: data.current_page || 1,
                        last_page: data.last_page || 1,
                        total: data.total || 0
                    };
                } catch (error) {
                    console.error('Error fetching participants:', error);
                    participants.value = []; // Fallback to empty array to prevent UI errors
                } finally {
                    isLoading.value = false;
                }
            };
            
            // Debounce search
            let timeout;
            const onSearch = () => {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    fetchParticipants(1);
                }, 500);
            };
            
            const onCategoryChange = (catId) => {
                selectedCategory.value = catId;
                fetchParticipants(1);
            };

            const getInitials = (name) => name ? name.split(' ').map(n => n[0]).slice(0,2).join('').toUpperCase() : '??';

            Vue.onMounted(() => {
                fetchParticipants();
            });

            return {
                participants,
                pagination,
                isLoading,
                search,
                selectedCategory,
                selectedGender,
                selectedAgeGroup,
                onSearch,
                onCategoryChange,
                fetchParticipants,
                getInitials
            };
        },
        template: `
            @verbatim
        <div class="bg-slate-800 p-4 md:p-8 rounded-2xl border border-white/10 mt-8 mb-8 shadow-2xl">
            
            <div class="mb-8 border-b border-white/10 pb-5">
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-2 h-6 bg-sport-volt rounded-full"></div>
                    <h3 class="text-xl md:text-2xl font-display uppercase text-white tracking-wider">Daftar Peserta</h3>
                </div>
                <p class="text-[10px] md:text-xs text-gray-400 font-mono uppercase tracking-[0.2em] ml-5">
                    Live Participant Database • {{ pagination.total || 0 }} Terdaftar
                </p>
            </div>

            <div class="space-y-4 mb-8">
                <div class="flex flex-col lg:flex-row justify-between gap-4">
                    
                    <div class="grid grid-cols-2 md:flex gap-3 w-full lg:w-auto">
                        <div class="relative flex-1 md:w-44">
                            <select v-model="selectedGender" @change="fetchParticipants(1)" 
                                class="w-full bg-slate-900 border border-white/10 rounded-xl pl-4 pr-10 py-3 text-xs text-white focus:border-sport-volt focus:ring-1 focus:ring-sport-volt outline-none appearance-none transition-all">
                                <option value="all">Semua Gender</option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[10px]"></i>
                        </div>

                        <div class="relative flex-1 md:w-44">
                            <select v-model="selectedAgeGroup" @change="fetchParticipants(1)" 
                                class="w-full bg-slate-900 border border-white/10 rounded-xl pl-4 pr-10 py-3 text-xs text-white focus:border-sport-volt focus:ring-1 focus:ring-sport-volt outline-none appearance-none transition-all">
                                <option value="all">Semua Umur</option>
                                <option value="Umum">Umum</option>
                                <option value="Master">Master</option>
                                <option value="50+">50+</option>
                            </select>
                            <i class="fas fa-filter absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none text-[10px]"></i>
                        </div>
                    </div>

                    <div class="relative w-full lg:w-80">
                        <input type="text" v-model="search" @input="onSearch" 
                            placeholder="Cari nama atau BIB..." 
                            class="bg-slate-900 border border-white/10 rounded-xl px-5 py-3 text-xs text-white focus:border-sport-volt focus:ring-1 focus:ring-sport-volt outline-none w-full pl-12 transition-all">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-xs"></i>
                    </div>
                </div>

                <div class="flex overflow-x-auto no-scrollbar gap-2 pb-2 -mx-4 px-4 md:mx-0 md:px-0">
                    <button 
                        @click="onCategoryChange('all')"
                        class="whitespace-nowrap px-6 py-2 rounded-full text-[10px] font-black transition-all border shrink-0 tracking-widest"
                        :class="selectedCategory === 'all' ? 'bg-sport-volt border-sport-volt text-black shadow-lg shadow-sport-volt/20' : 'bg-transparent border-white/10 text-gray-400 hover:border-white/30'">
                        ALL CATEGORIES
                    </button>
                    <button v-for="cat in categories" :key="cat.id"
                        @click="onCategoryChange(cat.id)"
                        class="whitespace-nowrap px-6 py-2 rounded-full text-[10px] font-black transition-all border shrink-0 tracking-widest"
                        :class="selectedCategory === cat.id ? 'bg-sport-volt border-sport-volt text-black shadow-lg shadow-sport-volt/20' : 'bg-transparent border-white/10 text-gray-400 hover:border-white/30'">
                        {{ cat.name.toUpperCase() }}
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-white/5 bg-slate-900/40 backdrop-blur-sm">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white/5 text-gray-500 text-[10px] uppercase tracking-[0.2em] font-mono">
                            <th class="p-4 font-semibold">Runner Information</th>
                            <th class="p-4 font-semibold hidden md:table-cell text-center">Category / Group</th>
                            <th class="p-4 font-semibold text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-if="isLoading">
                            <td colspan="3" class="p-16 text-center">
                                <i class="fas fa-spinner-third fa-spin text-3xl text-sport-volt mb-4"></i>
                                <p class="text-[10px] text-gray-400 tracking-[0.3em] uppercase">Fetching Runners...</p>
                            </td>
                        </tr>
                        
                        <tr v-else-if="participants.length === 0">
                            <td colspan="3" class="p-16 text-center text-gray-500">
                                <i class="fas fa-user-slash text-2xl mb-3 opacity-20"></i>
                                <p class="text-xs uppercase font-mono">Tidak ada peserta ditemukan</p>
                            </td>
                        </tr>

                        <tr v-for="(p, index) in participants" :key="index" class="hover:bg-white/[0.03] transition-all group">
                            <td class="p-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center text-white font-display text-xs border border-white/10 group-hover:border-sport-volt/50 transition-colors shrink-0">
                                        {{ getInitials(p.name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-white text-sm md:text-base group-hover:text-sport-volt transition-colors truncate">
                                            {{ p.name }}
                                        </div>
                                        <div class="flex items-center gap-2 mt-1 md:hidden text-[9px] font-mono">
                                            <span class="text-sport-volt bg-sport-volt/10 px-1.5 py-0.5 rounded border border-sport-volt/20 uppercase tracking-tighter">
                                                {{ p.category ? p.category.name : '-' }}
                                            </span>
                                            <span class="text-gray-500">{{ p.age_group }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-xs text-gray-400 hidden md:table-cell text-center font-mono">
                                <div class="text-white font-bold">{{ p.category ? p.category.name : '-' }}</div>
                                <div class="text-[10px] opacity-50">{{ p.age_group }}</div>
                            </td>
                            <td class="p-4 text-right">
                                <span
                                    class="inline-block px-3 py-1 rounded-md text-[9px] font-black uppercase tracking-widest border transition-all"
                                    :class="p.payment_status_public === 'cod'
                                        ? 'bg-orange-500/10 text-orange-400 border-orange-400/20'
                                        : 'bg-sport-volt/10 text-sport-volt border-sport-volt/30 shadow-[0_0_10px_rgba(204,255,0,0.1)]'">
                                    {{ p.payment_status_public === 'cod' ? 'PENDING' : 'CONFIRMED' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-between items-center mt-8 px-2" v-if="pagination.last_page > 1">
                <button @click="fetchParticipants(pagination.current_page - 1)" 
                    :disabled="pagination.current_page <= 1"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 text-[10px] text-white hover:bg-sport-volt hover:text-black disabled:opacity-20 transition-all uppercase font-black tracking-widest">
                    <i class="fas fa-arrow-left"></i> Prev
                </button>
                
                <div class="text-center">
                    <div class="text-[10px] font-mono text-gray-500 uppercase tracking-widest">Page</div>
                    <div class="text-xs font-bold text-white">{{ pagination.current_page }} <span class="text-gray-600 mx-1">/</span> {{ pagination.last_page }}</div>
                </div>

                <button @click="fetchParticipants(pagination.current_page + 1)" 
                    :disabled="pagination.current_page >= pagination.last_page"
                    class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 text-[10px] text-white hover:bg-sport-volt hover:text-black disabled:opacity-20 transition-all uppercase font-black tracking-widest">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        @endverbatim
        `
    };
</script>

<participants-table 
    :event-slug="'{{ $event->slug }}'" 
    :categories="categories"
    :fetch-url="'{{ route('events.participants-list', $event->slug) }}'"
></participants-table>
