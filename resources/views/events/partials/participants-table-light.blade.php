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
                    participants.value = [];
                } finally {
                    isLoading.value = false;
                }
            };

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

            const getInitials = (name) => name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : '??';

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
            <div class="w-full max-w-full">
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-3 items-start">
                        <div class="relative w-full">
                            <input
                                type="text"
                                v-model="search"
                                @input="onSearch"
                                placeholder="Cari nama..."
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 pl-10 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-300 focus:ring-2 focus:ring-blue-100 outline-none"
                            >
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        </div>
                        <div class="grid grid-cols-2 gap-3 w-full md:w-auto">
                            <select v-model="selectedGender" @change="fetchParticipants(1)"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-300 focus:ring-2 focus:ring-blue-100 outline-none">
                                <option value="all">Gender</option>
                                <option value="male">Laki-laki</option>
                                <option value="female">Perempuan</option>
                            </select>
                            <select v-model="selectedAgeGroup" @change="fetchParticipants(1)"
                                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 focus:border-blue-300 focus:ring-2 focus:ring-blue-100 outline-none">
                                <option value="all">Umur</option>
                                <option value="Umum">Umum</option>
                                <option value="Master">Master</option>
                                <option value="50+">50+</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <div class="text-xs font-bold text-slate-500">
                            Total: <span class="text-slate-900">{{ pagination.total || 0 }}</span>
                        </div>
                    </div>

                    <div class="flex overflow-x-auto no-scrollbar gap-2 pb-2">
                        <button
                            type="button"
                            @click="onCategoryChange('all')"
                            class="shrink-0 whitespace-nowrap px-4 py-2 rounded-full text-[11px] font-black tracking-wide border transition"
                            :class="selectedCategory === 'all'
                                ? 'bg-blue-600 border-blue-600 text-white'
                                : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                            Semua
                        </button>
                        <button v-for="cat in categories" :key="cat.id"
                            type="button"
                            @click="onCategoryChange(cat.id)"
                            class="shrink-0 whitespace-nowrap px-4 py-2 rounded-full text-[11px] font-black tracking-wide border transition"
                            :class="selectedCategory === cat.id
                                ? 'bg-blue-600 border-blue-600 text-white'
                                : 'bg-white border-slate-200 text-slate-700 hover:border-slate-300'">
                            {{ cat.name }}
                        </button>
                    </div>

                    <div class="md:hidden space-y-3">
                        <div v-if="isLoading" class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500">
                            <i class="fas fa-circle-notch fa-spin text-xl mb-2 text-blue-600"></i>
                            <div class="text-sm font-semibold">Memuat peserta...</div>
                        </div>

                        <div v-else-if="participants.length === 0" class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-500">
                            <div class="text-sm font-semibold">Belum ada peserta</div>
                        </div>

                        <div v-else v-for="(p, index) in participants" :key="index" class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-700 font-black text-xs shrink-0">
                                        {{ getInitials(p.name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-black text-slate-900 truncate">{{ p.name }}</div>
                                        <div class="mt-1 text-xs text-slate-500 truncate">
                                            {{ p.category ? p.category.name : '-' }} • {{ p.age_group }}
                                        </div>
                                    </div>
                                </div>
                                <span
                                    class="inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wide border shrink-0"
                                    :class="p.payment_status_public === 'cod'
                                        ? 'bg-orange-50 text-orange-700 border-orange-200'
                                        : 'bg-emerald-50 text-emerald-700 border-emerald-200'">
                                    {{ p.payment_status_public === 'cod' ? 'COD' : 'PAID' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:block overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <table class="w-full text-left">
                            <thead class="bg-slate-50">
                                <tr class="text-[11px] font-black uppercase tracking-wide text-slate-500">
                                    <th class="p-4">Nama</th>
                                    <th class="p-4">Kategori</th>
                                    <th class="p-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr v-if="isLoading">
                                    <td colspan="3" class="p-10 text-center text-slate-500">
                                        <i class="fas fa-circle-notch fa-spin text-xl mb-2 text-blue-600"></i>
                                        <div class="text-sm font-semibold">Memuat peserta...</div>
                                    </td>
                                </tr>

                                <tr v-else-if="participants.length === 0">
                                    <td colspan="3" class="p-10 text-center text-slate-500">
                                        <div class="text-sm font-semibold">Belum ada peserta</div>
                                    </td>
                                </tr>

                                <tr v-for="(p, index) in participants" :key="index" class="hover:bg-slate-50 transition">
                                    <td class="p-4">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-700 font-black text-xs shrink-0">
                                                {{ getInitials(p.name) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-black text-slate-900 truncate">{{ p.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-sm text-slate-600">
                                        <div class="font-bold text-slate-900">{{ p.category ? p.category.name : '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ p.age_group }}</div>
                                    </td>
                                    <td class="p-4 text-right">
                                        <span
                                            class="inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wide border"
                                            :class="p.payment_status_public === 'cod'
                                                ? 'bg-orange-50 text-orange-700 border-orange-200'
                                                : 'bg-emerald-50 text-emerald-700 border-emerald-200'">
                                            {{ p.payment_status_public === 'cod' ? 'COD' : 'PAID' }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between" v-if="pagination.last_page > 1">
                        <button
                            type="button"
                            @click="fetchParticipants(pagination.current_page - 1)"
                            :disabled="pagination.current_page <= 1"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-200 bg-white text-sm font-black text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            <i class="fas fa-arrow-left text-xs"></i>
                            Prev
                        </button>

                        <div class="text-sm font-bold text-slate-600">
                            {{ pagination.current_page }} / {{ pagination.last_page }}
                        </div>

                        <button
                            type="button"
                            @click="fetchParticipants(pagination.current_page + 1)"
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-200 bg-white text-sm font-black text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed">
                            Next
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                    </div>
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
