<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    thread: {
        type: Object,
        required: true
    },
    user: {
        type: Object,
        default: null
    },
    isJoining: {
        type: Boolean,
        default: false
    },
    theme: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['select', 'join', 'leave']);

const isUserJoined = computed(() => {
    if (!props.user) return false;
    return (props.thread.participants || []).some(p => p.user_id === props.user.id && p.status === 'joined');
});

const isUserPending = computed(() => {
    if (!props.user) return false;
    return (props.thread.participants || []).some(p => p.user_id === props.user.id && p.status === 'pending');
});

const isCreator = computed(() => {
    if (!props.user) return false;
    return props.thread.creator_id === props.user.id;
});

const joinedCount = computed(() => {
    return (props.thread.participants || []).filter(p => p.status === 'joined').length;
});

const isFull = computed(() => {
    return joinedCount.value >= props.thread.quota;
});

const typeColors = {
    'Casual Run': 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
    'Long Run': 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
    'Speed Session': 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
    'Recovery Run': 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
    'Race Prep': 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
    'Community Run': 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20'
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
};

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};

// Countdown timer
const countdownLabel = ref('');
const countdownBadgeClass = ref('');
let countdownTimer = null;

const updateCountdown = () => {
    const t = props.thread;
    if (!t) return;
    const dateStr = (typeof t.start_date === 'string' ? t.start_date : t.start_date).substring(0, 10);
    const target = new Date(`${dateStr}T${t.start_time}`);
    const now = new Date();
    const diff = target - now;

    if (diff <= 0) {
        const hoursAgo = Math.floor(Math.abs(diff) / (1000 * 60 * 60));
        if (hoursAgo < 1) {
            countdownLabel.value = '🏃 Berlangsung';
            countdownBadgeClass.value = 'bg-green-500/15 text-green-600 dark:text-green-400 border-green-500/25';
        } else {
            countdownLabel.value = '';
        }
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    if (days > 0) {
        countdownLabel.value = `${days}h ${hours}j lagi`;
        countdownBadgeClass.value = 'bg-slate-500/10 text-slate-500 dark:text-slate-400 border-slate-500/20';
    } else if (hours > 0) {
        countdownLabel.value = `${hours}j ${minutes}m lagi`;
        countdownBadgeClass.value = 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20';
    } else if (minutes > 0) {
        countdownLabel.value = `🔥 ${minutes}m lagi`;
        countdownBadgeClass.value = 'bg-red-500/10 text-red-500 dark:text-red-400 border-red-500/20 animate-pulse';
    } else {
        countdownLabel.value = '🏃 Sekarang!';
        countdownBadgeClass.value = 'bg-green-500/15 text-green-600 dark:text-green-400 border-green-500/25 animate-pulse';
    }
};

onMounted(() => {
    updateCountdown();
    countdownTimer = setInterval(updateCountdown, 60000); // every 60s
});

onUnmounted(() => {
    if (countdownTimer) clearInterval(countdownTimer);
});

const avatarUrl = computed(() => {
    const creator = props.thread.creator;
    if (!creator) return 'https://avatar.iran.liara.run/public/boy';
    
    if (creator.avatar && !creator.avatar.includes('default-')) {
        if (creator.avatar.startsWith('http')) return creator.avatar;
        if (creator.avatar.startsWith('images/')) return '/' + creator.avatar;
        let path = creator.avatar;
        if (path.startsWith('/')) path = path.substring(1);
        if (path.startsWith('storage/')) return '/' + path;
        return `/storage/${path}`;
    }
    
    if (creator.gender === 'female') {
        return 'https://avatar.iran.liara.run/public/girl?username=' + encodeURIComponent(creator.name);
    }
    return 'https://avatar.iran.liara.run/public/boy?username=' + encodeURIComponent(creator.name);
});
</script>

<template>
    <div 
        @click="$emit('select', thread)"
        class="p-5 rounded-2xl border bg-white dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 hover:border-blue-500/40 dark:hover:border-[#ccff00]/40 shadow-sm hover:shadow-md dark:shadow-none hover:bg-slate-50 dark:hover:bg-slate-900/90 transition-all duration-300 flex flex-col justify-between cursor-pointer group"
    >
        <div>
            <!-- Header Badges -->
            <div class="flex items-center justify-between gap-2 mb-3">
                <span 
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider"
                    :class="typeColors[thread.type] || 'bg-slate-500/10 text-slate-500 dark:text-slate-400 border-slate-500/20'"
                >
                    {{ thread.type }}
                </span>
                
                <div class="flex items-center gap-1">
                    <span v-if="countdownLabel" 
                        :class="countdownBadgeClass" 
                        class="px-1.5 py-0.5 rounded border text-[8px] font-bold"
                    >
                        {{ countdownLabel }}
                    </span>
                    <span v-if="thread.distance !== undefined" class="text-xs font-mono font-bold text-blue-600 dark:text-[#ccff00]">
                        {{ Number(thread.distance).toFixed(1) }} km
                    </span>
                    <span v-if="thread.is_beginner_friendly" class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[9px] font-bold">
                        Pemula OK
                    </span>
                </div>
            </div>

            <!-- Title & Location -->
            <h4 class="text-base font-black text-slate-800 dark:text-white leading-snug group-hover:text-blue-600 dark:group-hover:text-[#ccff00] transition-colors mb-1">
                {{ thread.title }}
            </h4>
            <p class="text-xs text-slate-400 dark:text-slate-500 mb-4 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate max-w-[220px]">{{ thread.start_location_name }}</span>
            </p>

            <!-- Specs Grid -->
            <div class="grid grid-cols-3 gap-2 bg-slate-50 dark:bg-slate-950/40 p-3 rounded-xl border border-slate-100 dark:border-slate-800/80 mb-4 text-center">
                <div>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold">Waktu</p>
                    <p class="text-[11px] font-bold text-slate-700 dark:text-white mt-0.5">
                        {{ formatDate(thread.start_date) }} {{ formatTime(thread.start_time) }}
                    </p>
                </div>
                <div>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold">Jarak</p>
                    <p class="text-[11px] font-bold text-slate-700 dark:text-white mt-0.5">
                        {{ Number(thread.run_distance_km).toFixed(1) }}K
                    </p>
                </div>
                <div>
                    <p class="text-[9px] text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold">Pace</p>
                    <p class="text-[11px] font-bold text-slate-700 dark:text-white mt-0.5">
                        {{ thread.pace_min && thread.pace_max ? `${thread.pace_min}-${thread.pace_max}` : 'Bebas' }}
                    </p>
                </div>
            </div>

            <!-- Quota Bar -->
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-slate-400 dark:text-slate-500">Kuota Terisi</span>
                    <span class="font-bold text-slate-700 dark:text-white font-mono">{{ joinedCount }} / {{ thread.quota }}</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div 
                        class="h-full rounded-full transition-all duration-500"
                        :class="isFull ? 'bg-red-500' : 'bg-blue-600 dark:bg-[#ccff00]'"
                        :style="{ width: `${(joinedCount / thread.quota) * 100}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Footer / Action Area -->
        <div class="flex items-center justify-between pt-2 border-t border-slate-100 dark:border-slate-800/50 mt-auto">
            <div class="flex items-center gap-2">
                <img 
                    :src="avatarUrl" 
                    class="w-7 h-7 rounded-full border border-slate-200 dark:border-slate-700 object-cover"
                />
                <div>
                    <p class="text-[10px] text-slate-400">Host</p>
                    <p class="text-xs font-bold text-slate-650 dark:text-slate-300 truncate max-w-[100px]">{{ thread.creator.name }}</p>
                </div>
            </div>

            <div @click.stop>
                <template v-if="!user">
                    <a 
                        href="/login" 
                        class="px-4 py-1.5 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 text-xs font-black rounded-lg transition-all shadow-sm block text-center"
                    >
                        Join
                    </a>
                </template>
                <template v-else>
                    <button 
                        v-if="isCreator"
                        disabled
                        class="px-3 py-1.5 bg-slate-105 dark:bg-slate-800 text-slate-400 dark:text-slate-500 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 cursor-not-allowed"
                    >
                        Host
                    </button>
                    <button 
                        v-else-if="isUserJoined"
                        @click="$emit('leave', thread.id)"
                        :disabled="isJoining"
                        class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-500 dark:text-red-400 border border-red-500/20 hover:border-red-500/30 text-xs font-bold rounded-lg transition-all cursor-pointer disabled:opacity-50"
                    >
                        Joined
                    </button>
                    <button 
                        v-else-if="isUserPending"
                        disabled
                        class="px-3 py-1.5 bg-slate-105 dark:bg-slate-800 text-slate-400 dark:text-slate-500 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 cursor-not-allowed"
                    >
                        Pending
                    </button>
                    <button 
                        v-else-if="isFull"
                        disabled
                        class="px-3 py-1.5 bg-slate-105 dark:bg-slate-800 text-slate-400 dark:text-slate-500 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 cursor-not-allowed"
                    >
                        Full
                    </button>
                    <button 
                        v-else
                        @click="$emit('join', thread.id)"
                        :disabled="isJoining"
                        class="px-4 py-1.5 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-750 dark:hover:bg-white text-white dark:text-slate-950 text-xs font-black rounded-lg transition-all shadow-sm cursor-pointer disabled:opacity-50"
                    >
                        Join
                    </button>
                </template>
            </div>
        </div>
    </div>
</template>
