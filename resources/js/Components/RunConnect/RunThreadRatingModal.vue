<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    thread: Object,
    user: Object,
});

const emit = defineEmits(['close', 'rated']);

const participants = computed(() => {
    return props.thread?.participants?.filter(p => p.user_id !== props.user.id) || [];
});

const ratings = ref({});
const isSubmitting = ref(false);

const setRating = (userId, star) => {
    if (!ratings.value[userId]) {
        ratings.value[userId] = { rating: 0, comment: '' };
    }
    ratings.value[userId].rating = star;
};

const submitRatings = async () => {
    isSubmitting.value = true;
    try {
        const promises = Object.keys(ratings.value).map(userId => {
            if (ratings.value[userId].rating > 0) {
                return axios.post(`/api/run-connect/threads/${props.thread.id}/rate`, {
                    reviewee_id: userId,
                    rating: ratings.value[userId].rating,
                    comment: ratings.value[userId].comment || ''
                });
            }
            return Promise.resolve();
        });
        
        await Promise.all(promises);
        alert('Terima kasih atas ulasan Anda!');
        emit('rated');
        emit('close');
    } catch (err) {
        console.error('Error submitting ratings:', err);
        alert('Gagal mengirim ulasan.');
    } finally {
        isSubmitting.value = false;
    }
};

const getAvatarUrl = (u) => {
    if (u.avatar) return u.avatar;
    return 'https://avatar.iran.liara.run/public/boy?username=' + encodeURIComponent(u.name);
};
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-md p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-2 text-center">Beri Penilaian</h3>
            <p class="text-xs text-slate-500 text-center mb-6">Bagaimana pengalaman lari Anda dengan teman-teman ini?</p>

            <div class="space-y-4 max-h-[50vh] overflow-y-auto no-scrollbar mb-6">
                <div v-for="p in participants" :key="p.user_id" class="bg-slate-50 dark:bg-slate-950/40 p-4 rounded-xl border border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3 mb-3">
                        <img :src="getAvatarUrl(p.user)" class="w-10 h-10 rounded-full object-cover" />
                        <div>
                            <h4 class="font-bold text-sm text-slate-800 dark:text-white">{{ p.user.name }}</h4>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-1 mb-3">
                        <button 
                            v-for="star in 5" 
                            :key="star"
                            @click="setRating(p.user_id, star)"
                            class="text-2xl transition-transform hover:scale-110"
                            :class="ratings[p.user_id]?.rating >= star ? 'text-amber-400' : 'text-slate-300 dark:text-slate-700'"
                        >
                            ★
                        </button>
                    </div>

                    <input 
                        v-if="ratings[p.user_id]?.rating > 0"
                        v-model="ratings[p.user_id].comment"
                        type="text" 
                        placeholder="Komentar singkat (opsional)"
                        class="w-full text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-3 py-2 text-slate-800 dark:text-white"
                    />
                </div>
            </div>

            <div class="flex gap-3">
                <button 
                    @click="$emit('close')"
                    class="flex-1 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-bold text-sm transition-colors"
                >
                    Nanti Saja
                </button>
                <button 
                    @click="submitRatings"
                    :disabled="isSubmitting"
                    class="flex-1 py-3 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-900 rounded-xl font-semibold text-sm transition-all disabled:opacity-50"
                >
                    {{ isSubmitting ? 'Mengirim...' : 'Kirim Ulasan' }}
                </button>
            </div>
        </div>
    </div>
</template>
