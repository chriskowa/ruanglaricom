<script>
    const ParticipantsTableComponent = {
        props: {
            eventSlug: { type: String, required: true },
            categories: { type: Array, required: true }
        },
        setup(props) {
            const participants = Vue.ref([]);
            const pagination = Vue.ref({});
            const isLoading = Vue.ref(false);
            const search = Vue.ref('');
            const selectedCategory = Vue.ref('all');
            
            const fetchParticipants = async (page = 1) => {
                isLoading.value = true;
                try {
                    const params = new URLSearchParams();
                    params.append('page', page);
                    if (search.value) params.append('search', search.value);
                    if (selectedCategory.value !== 'all') params.append('category_id', selectedCategory.value);
                    
                    const response = await fetch(`/event/${props.eventSlug}/participants-list?${params.toString()}`);
                    
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
                onSearch,
                onCategoryChange,
                fetchParticipants,
                getInitials
            };
        },
        template: `
            @verbatim
            <div class="bg-slate-800 p-6 md:p-8 rounded-2xl border border-white/10 mt-8 mb-8 shadow-2xl">
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-2xl font-display uppercase text-white tracking-wide">Daftar Peserta</h3>
                        <p class="text-xs text-gray-400 mt-1 font-mono">CARI TEMAN LARIMU DI SINI</p>
                    </div>
                    
                    <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                        <!-- Search -->
                        <div class="relative w-full">
                            <input type="text" v-model="search" @input="onSearch" 
                                placeholder="Cari nama..." 
                                class="bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-sm text-white focus:border-sport-volt focus:ring-1 focus:ring-sport-volt outline-none w-full md:w-64 pl-10 transition-all">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                        </div>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="flex flex-wrap gap-2 mb-6 border-b border-white/10 pb-4">
                    <button 
                        @click="onCategoryChange('all')"
                        class="px-4 py-2 rounded-lg text-sm font-bold transition-all duration-300"
                        :class="selectedCategory === 'all' ? 'bg-sport-volt text-white shadow-[0_0_15px_rgba(204,255,0,0.3)]' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10'">
                        SEMUA
                    </button>
                    <button v-for="cat in categories" :key="cat.id"
                        @click="onCategoryChange(cat.id)"
                        class="px-4 py-2 rounded-lg text-sm font-bold transition-all duration-300"
                        :class="selectedCategory === cat.id ? 'bg-sport-volt text-black shadow-[0_0_15px_rgba(204,255,0,0.3)]' : 'bg-white/5 text-gray-400 hover:text-white hover:bg-white/10'">
                        {{ cat.name }}
                    </button>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-xl border border-white/5">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-white/5 text-gray-400 text-xs uppercase tracking-wider font-mono">
                                <th class="p-4 font-normal">Nama Peserta</th>
                                <th class="p-4 font-normal">Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-if="isLoading">
                                <td colspan="2" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-circle-notch fa-spin text-2xl mb-2 text-sport-volt"></i>
                                    <p>Memuat data...</p>
                                </td>
                            </tr>
                            <tr v-else-if="participants.length === 0">
                                <td colspan="2" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-users-slash text-2xl mb-2"></i>
                                    <p>Belum ada peserta</p>
                                </td>
                            </tr>
                            <tr v-for="(p, index) in participants" :key="index" class="hover:bg-white/5 transition-colors group">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded bg-gray-800 flex items-center justify-center text-sport-volt font-display text-xs border border-white/10 group-hover:border-sport-volt transition-colors">
                                            {{ getInitials(p.name) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-sm group-hover:text-sport-volt transition-colors">{{ p.name }}</div>
                                            <div class="text-[10px] text-gray-500 md:hidden">{{ p.category ? p.category.name : '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-gray-300">
                                    <span class="bg-white/5 px-2 py-1 rounded text-xs border border-white/10">
                                        {{ p.category ? p.category.name : '-' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex justify-between items-center mt-6 pt-4 border-t border-white/10" v-if="pagination.last_page > 1">
                    <button 
                        @click="fetchParticipants(pagination.current_page - 1)"
                        :disabled="pagination.current_page <= 1"
                        class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="pagination.current_page <= 1 ? 'bg-white/5 text-gray-500' : 'bg-white/10 text-white hover:bg-sport-volt hover:text-black'">
                        Prev
                    </button>
                    
                    <span class="text-xs text-gray-400 font-mono">
                        Page {{ pagination.current_page }} of {{ pagination.last_page }}
                    </span>
                    
                    <button 
                        @click="fetchParticipants(pagination.current_page + 1)"
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        :class="pagination.current_page >= pagination.last_page ? 'bg-white/5 text-gray-500' : 'bg-white/10 text-white hover:bg-sport-volt hover:text-black'">
                        Next
                    </button>
                </div>
            </div>
            @endverbatim
        `
    };
</script>

<participants-table :event-slug="'{{ $event->slug }}'" :categories="categories"></participants-table>
