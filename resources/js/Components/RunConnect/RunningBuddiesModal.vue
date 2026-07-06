<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    isOpen: Boolean
});

const emit = defineEmits(['close', 'invite']);

const buddies = ref([]);
const loading = ref(false);

const fetchBuddies = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/run-connect/buddies');
        buddies.value = res.data;
    } catch (err) {
        console.error('Error fetching buddies:', err);
    } finally {
        loading.value = false;
    }
};

watch(() => props.isOpen, (newVal) => {
    if (newVal && buddies.value.length === 0) {
        fetchBuddies();
    }
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
        <div class="bg-white dark:bg-slate-900 w-full sm:w-[450px] sm:rounded-3xl rounded-t-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] transform transition-all duration-300 translate-y-0">
            
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-950/50 sticky top-0 z-10">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white uppercase tracking-wider">Teman Pelari</h3>
                    <p class="text-[10px] text-slate-500 font-bold mt-0.5">Orang yang pernah berlari dengan Anda</p>
                </div>
                <button @click="emit('close')" class="p-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 rounded-full text-slate-600 dark:text-slate-300 transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto">
                <div v-if="loading" class="py-12 flex justify-center">
                    <div class="w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
                <div v-else-if="buddies.length === 0" class="text-center py-12">
                    <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-1">Belum ada teman pelari</p>
                    <p class="text-xs text-slate-500">Ayo bergabung dengan thread komunitas untuk mulai mencari teman lari!</p>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="buddy in buddies" :key="buddy.id" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-950/60 rounded-2xl border border-slate-200 dark:border-slate-800 transition-colors hover:bg-slate-100 dark:hover:bg-slate-900">
                        <img :src="getAvatarUrl(buddy)" class="w-12 h-12 rounded-xl object-cover" />
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white">{{ buddy.name }}</h4>
                            <div class="flex gap-2 text-[10px] text-slate-500 font-bold mt-1">
                                <span class="flex items-center gap-0.5">
                                    <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    {{ buddy.buddy_rating ? Number(buddy.buddy_rating).toFixed(1) : '—' }}
                                </span>
                                <span>|</span>
                                <span class="text-blue-600 dark:text-[#ccff00]">{{ buddy.run_points }} RP</span>
                            </div>
                        </div>
                        <button 
                            @click="emit('invite', buddy)"
                            class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 dark:bg-[#ccff00] dark:hover:bg-[#b3e600] text-white dark:text-slate-900 rounded-lg text-xs font-black uppercase tracking-wider transition-colors cursor-pointer"
                        >
                            Ajak Lari
                        </button>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</template>
