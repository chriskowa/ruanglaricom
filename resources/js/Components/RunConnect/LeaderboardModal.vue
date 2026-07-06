<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close']);

const leaderboard = ref([]);
const loading = ref(false);
const timeframe = ref('all_time'); // 'weekly', 'monthly', 'all_time'

const fetchLeaderboard = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/run-connect/leaderboard', { params: { timeframe: timeframe.value } });
        leaderboard.value = res.data;
    } catch (err) {
        console.error('Error fetching leaderboard:', err);
    } finally {
        loading.value = false;
    }
};

watch(() => props.isOpen, (newVal) => {
    if (newVal && leaderboard.value.length === 0) {
        fetchLeaderboard();
    }
});

watch(timeframe, () => {
    fetchLeaderboard();
});

const getAvatarUrl = (user) => {
    if (!user) return 'https://avatar.iran.liara.run/public/boy';
    if (user.avatar && !user.avatar.includes('default-')) {
        if (user.avatar.startsWith('http')) return user.avatar;
        if (user.avatar.startsWith('images/')) return '/' + user.avatar;
        let path = user.avatar;
        if (path.startsWith('/')) path = path.substring(1);
        if (path.startsWith('storage/')) return '/' + path;
        return `/storage/${path}`;
    }
    if (user.gender === 'female') {
        return 'https://avatar.iran.liara.run/public/girl?username=' + encodeURIComponent(user.name);
    }
    return 'https://avatar.iran.liara.run/public/boy?username=' + encodeURIComponent(user.name);
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[200] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/60 backdrop-blur-sm" @click.self="emit('close')">
        <div class="bg-white dark:bg-slate-900 w-full sm:w-[500px] sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] transform transition-all duration-300 translate-y-0">
            
            <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-950/50 sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 dark:bg-amber-500/20 rounded-full flex items-center justify-center text-amber-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Leaderboard</h3>
                        <p class="text-[10px] text-slate-500 font-bold mt-0.5">Top Runner Komunitas Lokal</p>
                    </div>
                </div>
                <button @click="emit('close')" class="p-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 rounded-full text-slate-600 dark:text-slate-300 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Timeframe Filter -->
            <div class="px-6 pt-4">
                <div class="flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl">
                    <button @click="timeframe = 'weekly'" :class="timeframe === 'weekly' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all">Mingguan</button>
                    <button @click="timeframe = 'monthly'" :class="timeframe === 'monthly' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all">Bulanan</button>
                    <button @click="timeframe = 'all_time'" :class="timeframe === 'all_time' ? 'bg-white dark:bg-slate-700 shadow-sm text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300'" class="flex-1 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider transition-all">Semua Waktu</button>
                </div>
            </div>

            <div class="p-6 overflow-y-auto">
                <div v-if="loading" class="py-12 flex justify-center">
                    <div class="w-8 h-8 border-3 border-amber-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
                <div v-else-if="leaderboard.length === 0" class="text-center py-12">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Belum ada data leaderboard.</p>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="(runner, index) in leaderboard" :key="runner.id" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-950/60 rounded-2xl border border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-100 dark:hover:bg-slate-900 relative overflow-hidden">
                        
                        <!-- Rank Badge -->
                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-black text-xs z-10"
                            :class="[
                                index === 0 ? 'bg-yellow-400 text-yellow-900 shadow-lg shadow-yellow-400/20' :
                                index === 1 ? 'bg-slate-300 text-slate-700 shadow-lg shadow-slate-400/20' :
                                index === 2 ? 'bg-amber-600 text-amber-100 shadow-lg shadow-amber-600/20' :
                                'bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400'
                            ]"
                        >
                            {{ index + 1 }}
                        </div>

                        <!-- Avatar -->
                        <img :src="getAvatarUrl(runner)" class="w-12 h-12 rounded-full object-cover z-10 border-2" 
                            :class="[
                                index === 0 ? 'border-yellow-400' :
                                index === 1 ? 'border-slate-300' :
                                index === 2 ? 'border-amber-600' :
                                'border-transparent'
                            ]"
                        />

                        <!-- Info -->
                        <div class="flex-1 z-10">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-1">
                                {{ runner.name }}
                                <svg v-if="index === 0" class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 2l2.25 6.5H19l-5.5 4.25 2.25 6.5L10 15l-5.75 4.25 2.25-6.5L1 8.5h6.75L10 2z" clip-rule="evenodd" />
                                </svg>
                            </h4>
                            <div class="flex gap-2 text-[10px] text-slate-500 font-bold mt-1">
                                <span class="flex items-center gap-0.5">
                                    <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ runner.buddy_rating ? Number(runner.buddy_rating).toFixed(1) : '—' }}
                                </span>
                            </div>
                        </div>

                        <!-- Points -->
                        <div class="text-right z-10">
                            <div class="text-lg font-black text-amber-500 dark:text-amber-400 leading-none">{{ runner.run_points }}</div>
                            <div class="text-[9px] uppercase tracking-widest text-slate-400 font-bold mt-1">Run Points</div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</template>
