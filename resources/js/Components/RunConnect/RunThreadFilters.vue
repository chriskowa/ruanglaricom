<script setup>
import { ref } from 'vue';

const props = defineProps({
    filters: {
        type: Object,
        required: true
    },
    theme: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['update:filters', 'change']);

const isCollapsed = ref(true);

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value;
};

const updateFilter = (key, value) => {
    const updated = { ...props.filters, [key]: value };
    emit('update:filters', updated);
    emit('change', updated);
};

const resetAll = () => {
    const defaulted = {
        radius: 5,
        type: '',
        distance_filter: '',
        pace_filter: '',
        start_time_filter: '',
        slot_available: false,
        beginner_friendly: false,
        women_friendly: false
    };
    emit('update:filters', defaulted);
    emit('change', defaulted);
};

const runTypes = [
    'Casual Run',
    'Long Run',
    'Speed Session',
    'Recovery Run',
    'Race Prep',
    'Community Run'
];

const radiuses = [1, 3, 5, 10, 25];
const times = [
    { label: 'Sekarang', value: 'now' },
    { label: 'Hari Ini', value: 'today' },
    { label: 'Malam Ini', value: 'tonight' },
    { label: 'Besok Pagi', value: 'tomorrow_morning' },
    { label: 'Weekend', value: 'weekend' }
];

const distances = [
    { label: '3-5K', value: '3_5' },
    { label: '5-10K', value: '5_10' },
    { label: '10-15K', value: '10_15' },
    { label: '15K+', value: '15_plus' }
];

const paces = [
    { label: 'Relaxed', value: 'relaxed' },
    { label: '7:00+', value: '7_plus' },
    { label: '6:00-7:00', value: '6_7' },
    { label: '5:00-6:00', value: '5_6' },
    { label: 'Sub 5:00', value: 'sub_5' }
];
</script>

<template>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-2xl p-4 mb-4 transition-colors duration-300">
        <!-- Collapsible Header -->
        <div @click="toggleCollapse" class="flex items-center justify-between cursor-pointer select-none">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-[#ccff00]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider">Filter Pencarian</span>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    @click.stop="resetAll"
                    class="text-xs text-slate-400 dark:text-slate-555 hover:text-slate-800 dark:hover:text-white transition-colors cursor-pointer mr-1.5 font-medium"
                >
                    Reset
                </button>
                <div 
                    class="p-1.5 rounded-lg text-slate-400 dark:text-slate-500 transition-colors"
                >
                    <svg 
                        class="w-4 h-4 transform transition-transform duration-300"
                        :class="{ 'rotate-180': !isCollapsed }"
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Filter Fields -->
        <div v-show="!isCollapsed" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-4">
            
            <!-- Radius -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-2">Radius Jarak (KM)</label>
                <div class="flex gap-2 flex-wrap">
                    <button 
                        v-for="r in radiuses" 
                        :key="r"
                        @click="updateFilter('radius', r)"
                        :class="filters.radius === r 
                            ? 'bg-blue-600 dark:bg-[#ccff00] text-white dark:text-slate-950 font-black' 
                            : 'bg-slate-50 dark:bg-slate-950/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-850 border border-slate-200 dark:border-slate-800'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer"
                    >
                        {{ r }} km
                    </button>
                </div>
            </div>

            <!-- Start Time -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-2">Waktu Start</label>
                <div class="flex gap-2 flex-wrap">
                    <button 
                        v-for="t in times" 
                        :key="t.value"
                        @click="updateFilter('start_time_filter', filters.start_time_filter === t.value ? '' : t.value)"
                        :class="filters.start_time_filter === t.value 
                            ? 'bg-blue-600 dark:bg-[#ccff00] text-white dark:text-slate-950 font-black' 
                            : 'bg-slate-50 dark:bg-slate-950/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-850 border border-slate-200 dark:border-slate-800'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer"
                    >
                        {{ t.label }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Distance Target -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-2">Jarak Lari</label>
                    <div class="flex gap-2 flex-wrap">
                        <button 
                            v-for="d in distances" 
                            :key="d.value"
                            @click="updateFilter('distance_filter', filters.distance_filter === d.value ? '' : d.value)"
                            :class="filters.distance_filter === d.value 
                                ? 'bg-blue-600 dark:bg-[#ccff00] text-white dark:text-slate-950 font-black' 
                                : 'bg-slate-50 dark:bg-slate-950/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-850 border border-slate-200 dark:border-slate-800'"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer"
                        >
                            {{ d.label }}
                        </button>
                    </div>
                </div>

                <!-- Pace Target -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-2">Pace Target</label>
                    <div class="flex gap-2 flex-wrap">
                        <button 
                            v-for="p in paces" 
                            :key="p.value"
                            @click="updateFilter('pace_filter', filters.pace_filter === p.value ? '' : p.value)"
                            :class="filters.pace_filter === p.value 
                                ? 'bg-blue-600 dark:bg-[#ccff00] text-white dark:text-slate-950 font-black' 
                                : 'bg-slate-50 dark:bg-slate-950/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-850 border border-slate-200 dark:border-slate-800'"
                            class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-all cursor-pointer"
                        >
                            {{ p.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Run Type -->
            <div>
                <label class="block text-[11px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest mb-2">Tipe Lari</label>
                <select 
                    :value="filters.type"
                    @change="updateFilter('type', $event.target.value)"
                    class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-300 rounded-xl p-2.5 text-xs outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                >
                    <option value="">Semua Tipe Lari</option>
                    <option v-for="type in runTypes" :key="type" :value="type">{{ type }}</option>
                </select>
            </div>

            <!-- Custom switches/toggles -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-350">
                    <input 
                        type="checkbox"
                        :checked="filters.slot_available"
                        @change="updateFilter('slot_available', !filters.slot_available)"
                        class="w-4 h-4 rounded text-blue-600 dark:text-[#ccff00] focus:ring-0 bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 accent-blue-600 dark:accent-[#ccff00]"
                    />
                    <span>Hanya Kuota Tersedia</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-350">
                    <input 
                        type="checkbox"
                        :checked="filters.beginner_friendly"
                        @change="updateFilter('beginner_friendly', !filters.beginner_friendly)"
                        class="w-4 h-4 rounded text-blue-600 dark:text-[#ccff00] focus:ring-0 bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 accent-blue-600 dark:accent-[#ccff00]"
                    />
                    <span>Ramah Pemula</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-350">
                    <input 
                        type="checkbox"
                        :checked="filters.women_friendly"
                        @change="updateFilter('women_friendly', !filters.women_friendly)"
                        class="w-4 h-4 rounded text-blue-600 dark:text-[#ccff00] focus:ring-0 bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800 accent-blue-600 dark:accent-[#ccff00]"
                    />
                    <span>Ramah Pelari Wanita</span>
                </label>
            </div>
        </div>
    </div>
</template>
