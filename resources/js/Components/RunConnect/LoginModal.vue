<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    theme: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['close', 'success']);

const form = reactive({
    email: '',
    password: '',
    remember: true
});

const loading = ref(false);
const error = ref('');

const submitLogin = async () => {
    if (!form.email || !form.password) {
        error.value = 'Email dan password wajib diisi.';
        return;
    }

    loading.value = true;
    error.value = '';

    try {
        // Send AJAX post request to standard Laravel login route
        await axios.post('/login', form, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        emit('success');
        emit('close');
        // Reset form
        form.email = '';
        form.password = '';
    } catch (err) {
        error.value = err.response?.data?.message || 'Login gagal. Silakan periksa kembali email dan password Anda.';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div 
        v-if="isOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
    >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col w-full max-w-sm">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white">Masuk ke RuangLari</h3>
                <button 
                    @click="$emit('close')" 
                    class="text-slate-400 hover:text-slate-800 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="submitLogin" class="p-6 space-y-4">
                <p class="text-xs text-slate-500 dark:text-slate-300 leading-relaxed">
                    Silakan masuk untuk dapat membuat, bergabung, dan berinteraksi di ruang lari.
                </p>

                <div v-if="error" class="bg-red-500/10 border border-red-550/20 text-red-550 dark:text-red-400 p-3 rounded-xl text-xs">
                    {{ error }}
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-450 dark:text-slate-300 uppercase mb-2">Alamat Email</label>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        placeholder="nama@email.com"
                        required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-white outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-450 dark:text-slate-300 uppercase mb-2">Kata Sandi</label>
                    <input 
                        v-model="form.password" 
                        type="password" 
                        placeholder="••••••••"
                        required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-white outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                    />
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 text-slate-600 dark:text-slate-300 cursor-pointer">
                        <input 
                            v-model="form.remember" 
                            type="checkbox"
                            class="w-4 h-4 rounded text-blue-600 dark:text-[#ccff00] bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 accent-blue-600 dark:accent-[#ccff00]"
                        />
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <button 
                    type="submit"
                    :disabled="loading"
                    class="w-full py-3 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 font-semibold rounded-xl text-sm transition-all shadow-md cursor-pointer flex items-center justify-center gap-2"
                >
                    <svg v-if="loading" class="animate-spin h-5 w-5 text-current" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>{{ loading ? 'Memproses...' : 'Masuk' }}</span>
                </button>
            </form>
        </div>
    </div>
</template>
