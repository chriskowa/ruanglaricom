<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    user: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'edit']);

const threads = ref([]);
const isLoading = ref(false);

const fetchHistory = async () => {
    isLoading.value = true;
    try {
        const res = await axios.get('/api/run-connect/history');
        threads.value = res.data.data || [];
    } catch (err) {
        console.error('Error fetching history:', err);
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        fetchHistory();
    }
});

const getStatusBadge = (thread) => {
    switch(thread.status) {
        case 'open': return { text: 'Terbuka', class: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' };
        case 'full': return { text: 'Penuh', class: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' };
        case 'completed': return { text: 'Selesai', class: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' };
        case 'cancelled': return { text: 'Batal', class: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' };
        default: return { text: thread.status, class: 'bg-slate-100 text-slate-700' };
    }
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-md shadow-2xl flex flex-col max-h-[85vh] overflow-hidden border border-slate-200 dark:border-slate-800">
            <!-- Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-slate-800 dark:text-white text-lg">My Threads</h3>
                <button @click="emit('close')" class="p-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 rounded-full transition-colors">
                    <svg class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-4 overflow-y-auto scroll-thin flex-1 bg-slate-50 dark:bg-slate-900">
                <div v-if="isLoading" class="py-8 text-center text-slate-500 text-sm font-medium">Memuat data...</div>
                <div v-else-if="threads.length === 0" class="py-12 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-semibold">Belum ada thread.</p>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="thread in threads" :key="thread.id" class="bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-150 dark:border-slate-750 flex flex-col gap-2">
                        <div class="flex justify-between items-start">
                            <h4 class="font-bold text-sm text-slate-800 dark:text-white">{{ thread.title }}</h4>
                            <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider', getStatusBadge(thread).class]">
                                {{ getStatusBadge(thread).text }}
                            </span>
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ new Date(thread.start_date).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }) }} &bull; {{ thread.start_time.substring(0,5) }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ thread.start_location_name || 'Lokasi tidak ditentukan' }}
                        </div>
                        <div v-if="user && thread.creator_id === user.id" class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                            <button @click="emit('edit', thread); emit('close')" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                Edit Thread
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
