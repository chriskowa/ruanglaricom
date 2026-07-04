<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
});
const emit = defineEmits(['close', 'updated']);

const threads = ref([]);
const isLoading = ref(false);

const fetchApprovals = async () => {
    isLoading.value = true;
    try {
        const res = await axios.get('/api/run-connect/approvals');
        threads.value = res.data || [];
    } catch (err) {
        console.error('Error fetching approvals:', err);
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        fetchApprovals();
    }
});

const handleApprove = async (thread, participant) => {
    try {
        const res = await axios.post(`/api/run-connect/threads/${thread.id}/approve/${participant.id}`);
        emit('updated', res.data.thread);
        fetchApprovals();
    } catch (err) {
        alert(err.response?.data?.message || 'Error approving.');
    }
};

const handleReject = async (thread, participant) => {
    try {
        const res = await axios.post(`/api/run-connect/threads/${thread.id}/reject/${participant.id}`);
        emit('updated', res.data.thread);
        fetchApprovals();
    } catch (err) {
        alert(err.response?.data?.message || 'Error rejecting.');
    }
};

const getUserAvatar = (user) => {
    if (!user?.avatar) return '/images/profile/17.jpg';
    if (user.avatar.startsWith('http')) return user.avatar;
    let path = user.avatar;
    if (path.startsWith('/')) path = path.substring(1);
    if (path.startsWith('storage/')) return '/' + path;
    return `/storage/${path}`;
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-md shadow-2xl flex flex-col max-h-[85vh] overflow-hidden border border-slate-200 dark:border-slate-800">
            <!-- Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-800/50">
                <h3 class="font-bold text-slate-800 dark:text-white text-lg">Approval Runners</h3>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-semibold">Belum ada permintaan yang menunggu.</p>
                </div>
                <div v-else class="space-y-4">
                    <div v-for="thread in threads" :key="thread.id" class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-150 dark:border-slate-750">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-white mb-3 pb-2 border-b border-slate-100 dark:border-slate-700">Thread: {{ thread.title }}</h4>
                        <div class="space-y-3">
                            <template v-for="participant in thread.participants" :key="participant.id">
                                <div v-if="participant.status === 'pending'" class="flex items-center justify-between gap-3 p-3 bg-slate-50 dark:bg-slate-900 rounded-xl border border-slate-100 dark:border-slate-750">
                                    <div class="flex items-center gap-3">
                                        <img :src="getUserAvatar(participant.user)" class="w-10 h-10 rounded-full object-cover border-2 border-white dark:border-slate-800 shadow-sm" alt="Avatar">
                                        <div>
                                            <p class="text-xs font-bold text-slate-800 dark:text-white">{{ participant.user?.name || 'User' }}</p>
                                            <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ new Date(participant.created_at).toLocaleString('id-ID') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1.5 shrink-0">
                                        <button @click="handleApprove(thread, participant)" class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white text-[10px] font-bold uppercase tracking-wider rounded-lg transition-colors shadow-sm">Terima</button>
                                        <button @click="handleReject(thread, participant)" class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-colors">Tolak</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
