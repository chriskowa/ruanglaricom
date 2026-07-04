<script setup>
import RunThreadCard from './RunThreadCard.vue';
import EmptyNearbyState from './EmptyNearbyState.vue';

const props = defineProps({
    threads: {
        type: Array,
        required: true
    },
    user: {
        type: Object,
        default: null
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    isJoining: {
        type: Boolean,
        default: false
    },
    hasFilters: {
        type: Boolean,
        default: false
    },
    theme: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['select-thread', 'join-thread', 'leave-thread', 'reset-filters', 'create-click']);
</script>

<template>
    <div>
        <!-- Loading State Skeletons -->
        <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
            <div 
                v-for="i in 3" 
                :key="i"
                class="p-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60 animate-pulse space-y-4"
            >
                <div class="flex justify-between items-center">
                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-1/4"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-12"></div>
                </div>
                <div class="h-6 bg-slate-200 dark:bg-slate-800 rounded w-3/4"></div>
                <div class="space-y-2">
                    <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-5/6"></div>
                    <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-2/3"></div>
                </div>
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                        <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-16"></div>
                    </div>
                    <div class="h-8 bg-slate-200 dark:bg-slate-800 rounded w-14"></div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="threads.length === 0" class="py-4">
            <EmptyNearbyState 
                :has-filters="hasFilters"
                :theme="theme"
                @reset-filters="$emit('reset-filters')"
                @create-click="$emit('create-click')"
            />
        </div>

        <!-- Threads Listing Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
            <RunThreadCard 
                v-for="thread in threads" 
                :key="thread.id"
                :thread="thread"
                :user="user"
                :is-joining="isJoining"
                :theme="theme"
                @select="$emit('select-thread', thread)"
                @join="$emit('join-thread', $event)"
                @leave="$emit('leave-thread', $event)"
            />
        </div>
    </div>
</template>
