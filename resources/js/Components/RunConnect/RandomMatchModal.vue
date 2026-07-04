<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    userLocation: {
        type: Object,
        required: true
    },
    theme: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['close', 'select-thread']);

const loading = ref(false);
const searched = ref(false);
const matches = ref([]);

const form = reactive({
    preferred_distance: 5,
    preferred_pace: '6:00',
    preferred_type: 'Casual Run'
});

const runTypes = [
    'Casual Run',
    'Long Run',
    'Speed Session',
    'Recovery Run',
    'Race Prep',
    'Community Run'
];

const paces = ['4:00', '4:30', '5:00', '5:30', '6:00', '6:30', '7:00', '7:30', '8:00', 'Bebas'];

const searchMatches = async () => {
    loading.value = true;
    searched.value = true;
    matches.value = [];

    try {
        const res = await axios.get('/api/run-connect/random-match', {
            params: {
                latitude: props.userLocation.lat,
                longitude: props.userLocation.lng,
                preferred_distance: form.preferred_distance,
                preferred_pace: form.preferred_pace === 'Bebas' ? null : form.preferred_pace,
                preferred_type: form.preferred_type
            }
        });
        matches.value = res.data.matches || [];
    } catch (err) {
        alert(err.response?.data?.message || 'Gagal mencari jodoh lari. Silakan coba lagi.');
    } finally {
        loading.value = false;
    }
};

const resetModal = () => {
    searched.value = false;
    matches.value = [];
    form.preferred_distance = 5;
    form.preferred_pace = '6:00';
    form.preferred_type = 'Casual Run';
};
</script>

<template>
    <div 
        v-if="isOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
    >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col w-full max-w-md max-h-[90vh]">
            
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white">Match Buddy</h3>
                </div>
                <button 
                    @click="$emit('close'); resetModal()" 
                    class="text-slate-400 hover:text-slate-800 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto flex-grow space-y-4 text-slate-800 dark:text-slate-100">
                
                <!-- Initial Form -->
                <div v-if="!searched" class="space-y-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Masukkan preferensi lari Anda. Kami akan mencocokkan Anda dengan running thread terdekat yang paling sesuai dengan target lari Anda secara instan.
                    </p>

                    <div>
                        <label class="block text-xs font-bold text-slate-450 dark:text-slate-500 uppercase mb-2">Target Jarak Lari Anda (KM)</label>
                        <input 
                            v-model="form.preferred_distance" 
                            type="number" 
                            step="0.5"
                            min="1"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-850 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-450 dark:text-slate-500 uppercase mb-2">Target Pace Rata-rata</label>
                        <select 
                            v-model="form.preferred_pace"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-850 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        >
                            <option v-for="p in paces" :key="p" :value="p">{{ p }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-450 dark:text-slate-500 uppercase mb-2">Tipe Lari yang Diinginkan</label>
                        <select 
                            v-model="form.preferred_type"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-850 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        >
                            <option v-for="type in runTypes" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>

                    <button 
                        @click="searchMatches"
                        class="w-full py-3 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-750 dark:hover:bg-white text-white dark:text-slate-950 font-black rounded-xl transition-all shadow-md cursor-pointer flex items-center justify-center gap-1.5"
                    >
                        Cari Running Thread &rarr;
                    </button>
                </div>

                <!-- Searching state -->
                <div v-else-if="loading" class="py-12 text-center space-y-4">
                    <div class="w-12 h-12 border-4 border-blue-650 dark:border-[#ccff00] border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider animate-pulse">Menghubungkan & Mencocokkan...</p>
                    <p class="text-xs text-slate-500">Mencari thread dalam radius 25 km...</p>
                </div>

                <!-- Matches results list -->
                <div v-else class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-widest">Rekomendasi Teratas</h4>
                        <button 
                            @click="searched = false"
                            class="text-xs text-blue-650 dark:text-[#ccff00] hover:underline cursor-pointer"
                        >
                            Ubah Preferensi
                        </button>
                    </div>

                    <div v-if="matches.length > 0" class="space-y-3">
                        <div 
                            v-for="match in matches" 
                            :key="match.thread.id"
                            class="bg-slate-50 dark:bg-slate-950 p-4 border border-slate-200 dark:border-slate-800 rounded-2xl flex flex-col justify-between hover:border-blue-500/40 dark:hover:border-[#ccff00]/40 transition-colors"
                        >
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <div>
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[9px] font-black uppercase">
                                        {{ match.match_score }}% Match
                                    </span>
                                    <h5 class="text-sm font-bold text-slate-800 dark:text-white mt-1.5">{{ match.thread.title }}</h5>
                                </div>
                                <span class="text-xs font-mono font-semibold text-blue-600 dark:text-[#ccff00]">
                                    {{ Number(match.distance).toFixed(1) }} km
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-[10px] text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900/40 p-2.5 rounded-xl border border-slate-150 dark:border-slate-800/50 mb-3">
                                <div>Tanggal: <strong class="text-slate-800 dark:text-white">{{ match.thread.start_date.substring(5,10) }}</strong></div>
                                <div>Mulai: <strong class="text-slate-800 dark:text-white">{{ match.thread.start_time.substring(0,5) }}</strong></div>
                                <div>Jarak: <strong class="text-slate-800 dark:text-white">{{ Number(match.thread.run_distance_km).toFixed(1) }}K</strong></div>
                            </div>

                            <button 
                                @click="$emit('select-thread', match.thread); $emit('close'); resetModal()"
                                class="w-full py-2 bg-slate-200 dark:bg-slate-800 hover:bg-blue-600 dark:hover:bg-[#ccff00] text-slate-700 dark:text-slate-300 hover:text-white dark:hover:text-slate-950 font-bold rounded-xl transition-all text-xs cursor-pointer text-center"
                            >
                                Lihat Thread
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-8">
                        <p class="text-sm text-slate-650 dark:text-slate-400">Tidak menemukan thread yang cocok.</p>
                        <p class="text-xs text-slate-500 mt-1">Coba sesuaikan preferensi Anda.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
