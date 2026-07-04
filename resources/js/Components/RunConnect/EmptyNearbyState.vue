<script setup>
const props = defineProps({
    hasFilters: {
        type: Boolean,
        default: false
    },
    theme: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['reset-filters', 'create-click']);
</script>

<template>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-none rounded-2xl p-8 text-center space-y-4 transition-colors duration-300">
        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
            <svg class="w-6 h-6 text-slate-400 dark:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        
        <div class="space-y-1">
            <h4 class="text-sm font-semibold text-slate-800 dark:text-white">Tidak Ada Running Thread Terdekat</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-xs mx-auto leading-relaxed">
                <span v-if="hasFilters">
                    Tidak menemukan kecocokan dengan filter aktif Anda. Coba reset filter untuk melihat semua rute.
                </span>
                <span v-else>
                    Belum ada runner yang membuat thread di area ini. Jadilah yang pertama membuat rute lari!
                </span>
            </p>
        </div>

        <div class="pt-2">
            <button 
                v-if="hasFilters"
                @click="$emit('reset-filters')"
                class="px-5 py-2 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 text-xs font-semibold rounded-xl transition-all cursor-pointer shadow-sm"
            >
                Reset Semua Filter
            </button>
            <button 
                v-else
                @click="$emit('create-click')"
                class="px-5 py-2 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-750 dark:hover:bg-white text-white dark:text-slate-950 text-xs font-black rounded-xl transition-all cursor-pointer shadow-sm"
            >
                Buat Thread Lari +
            </button>
        </div>
    </div>
</template>
