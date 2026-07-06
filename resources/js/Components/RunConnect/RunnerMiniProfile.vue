<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close']);

const profile = ref(null);
const loading = ref(true);
const error = ref(false);

const fetchProfile = async () => {
    loading.value = true;
    error.value = false;
    try {
        const res = await axios.get(`/api/run-connect/runner/${props.user.id}/profile`);
        profile.value = res.data;
    } catch (err) {
        console.error('Error fetching runner profile:', err);
        error.value = true;
    } finally {
        loading.value = false;
    }
};

onMounted(fetchProfile);

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

const calculateAge = (dob) => {
    if (!dob) return null;
    const birth = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    return age;
};

const ratingStars = computed(() => {
    if (!profile.value?.buddy_rating) return 0;
    return Math.round(Number(profile.value.buddy_rating) * 2) / 2; // round to 0.5
});

const genderLabel = computed(() => {
    if (!profile.value?.gender) return null;
    return profile.value.gender === 'female' ? 'Perempuan' : 'Laki-laki';
});
</script>

<template>
    <div 
        class="fixed inset-0 z-[250] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
        @click.self="emit('close')"
    >
        <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-sm shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden transform transition-all duration-300">
            
            <!-- Loading State -->
            <div v-if="loading" class="p-8 text-center">
                <div class="w-10 h-10 border-3 border-blue-500 dark:border-[#ccff00] border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">Memuat profil runner...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="p-8 text-center">
                <svg class="w-10 h-10 text-slate-300 dark:text-slate-700 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">Gagal memuat profil.</p>
                <button @click="emit('close')" class="mt-3 px-4 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-bold cursor-pointer hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Tutup</button>
            </div>

            <!-- Profile Content -->
            <template v-else-if="profile">
                <!-- Header with gradient -->
                <div class="relative h-20 bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-600 dark:from-[#ccff00]/20 dark:via-emerald-500/20 dark:to-blue-500/20">
                    <button 
                        @click="emit('close')"
                        class="absolute top-3 right-3 p-1.5 bg-white/20 hover:bg-white/30 rounded-full text-white dark:text-slate-200 transition-colors cursor-pointer backdrop-blur-sm"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Avatar + Name -->
                <div class="px-5 -mt-10 relative z-10">
                    <div class="flex items-end gap-3">
                        <img 
                            :src="getAvatarUrl(profile)" 
                            class="w-16 h-16 rounded-2xl border-4 border-white dark:border-slate-900 object-cover shadow-lg"
                        />
                        <div class="pb-1.5">
                            <h3 class="text-base font-black text-slate-900 dark:text-white leading-tight">{{ profile.name }}</h3>
                            <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                                <span v-if="genderLabel" class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border border-slate-200 dark:border-slate-700">
                                    {{ genderLabel }}
                                </span>
                                <span v-if="calculateAge(profile.date_of_birth)" class="text-[9px] px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border border-slate-200 dark:border-slate-700">
                                    {{ calculateAge(profile.date_of_birth) }} tahun
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="px-5 py-4">
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="bg-slate-50 dark:bg-slate-950/30 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold mb-0.5">Run Points</p>
                            <p class="text-sm font-black text-blue-600 dark:text-[#ccff00]">{{ profile.run_points || 0 }}</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-950/30 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold mb-0.5">Buddy Rating</p>
                            <p class="text-sm font-black text-amber-500">
                                {{ profile.buddy_rating ? Number(profile.buddy_rating).toFixed(1) : '—' }}
                                <span v-if="profile.buddy_rating" class="text-[9px]">⭐</span>
                            </p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-950/30 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold mb-0.5">Thread</p>
                            <p class="text-sm font-black text-slate-700 dark:text-white">{{ profile.total_threads || 0 }}</p>
                        </div>
                    </div>

                    <!-- PB Records -->
                    <div v-if="profile.pb_5k || profile.pb_10k || profile.pb_hm || profile.pb_fm" class="mb-4">
                        <h4 class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold mb-2">Personal Best</h4>
                        <div class="flex flex-wrap gap-1.5">
                            <span v-if="profile.pb_5k" class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-lg text-[10px] font-bold">
                                5K: {{ profile.pb_5k }}
                            </span>
                            <span v-if="profile.pb_10k" class="px-2.5 py-1 bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 rounded-lg text-[10px] font-bold">
                                10K: {{ profile.pb_10k }}
                            </span>
                            <span v-if="profile.pb_hm" class="px-2.5 py-1 bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 rounded-lg text-[10px] font-bold">
                                HM: {{ profile.pb_hm }}
                            </span>
                            <span v-if="profile.pb_fm" class="px-2.5 py-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 rounded-lg text-[10px] font-bold">
                                FM: {{ profile.pb_fm }}
                            </span>
                        </div>
                    </div>

                    <!-- Thread Breakdown -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-slate-50 dark:bg-slate-950/20 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800">
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold mb-0.5">Thread Hosted</p>
                            <p class="text-xs font-black text-slate-700 dark:text-white">{{ profile.threads_hosted || 0 }}</p>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-950/20 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800">
                            <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold mb-0.5">Thread Joined</p>
                            <p class="text-xs font-black text-slate-700 dark:text-white">{{ profile.threads_joined || 0 }}</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>
