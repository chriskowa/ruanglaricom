<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
    thread: {
        type: Object,
        required: true
    },
    auth: {
        type: Object,
        required: true
    }
});

const messages = ref([]);
const newMessage = ref('');
const isSubmitting = ref(false);
const chatContainer = ref(null);
let pollInterval = null;

const fetchMessages = async () => {
    try {
        const res = await axios.get(`/api/run-connect/threads/${props.thread.id}/messages`);
        const oldLength = messages.value.length;
        messages.value = res.data.messages;
        if (messages.value.length > oldLength) {
            scrollToBottom();
        }
    } catch (err) {
        console.error('Error fetching chat:', err);
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim() || isSubmitting.value) return;
    
    isSubmitting.value = true;
    try {
        const res = await axios.post(`/api/run-connect/threads/${props.thread.id}/messages`, {
            message: newMessage.value
        });
        messages.value.push(res.data.data);
        newMessage.value = '';
        scrollToBottom();
    } catch (err) {
        console.error('Error sending message:', err);
        alert('Gagal mengirim pesan.');
    } finally {
        isSubmitting.value = false;
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

const formatTime = (isoString) => {
    const d = new Date(isoString);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
    fetchMessages();
    pollInterval = setInterval(fetchMessages, 5000); // Poll every 5s
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <div class="flex flex-col h-[60vh] bg-slate-50 dark:bg-slate-950 rounded-lg overflow-hidden">
        <!-- Chat Area -->
        <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
            <div v-if="messages.length === 0" class="text-center text-slate-400 dark:text-slate-500 py-10 text-xs">
                Belum ada pesan. Mulai obrolan dengan teman lari Anda!
            </div>
            
            <div 
                v-for="msg in messages" 
                :key="msg.id"
                class="flex gap-3"
                :class="msg.user_id === auth.user.id ? 'flex-row-reverse' : 'flex-row'"
            >
                <!-- Avatar -->
                <img 
                    :src="msg.user.avatar || '/images/default-avatar.svg'" 
                    class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-800"
                />
                
                <!-- Bubble -->
                <div class="flex flex-col max-w-[75%]" :class="msg.user_id === auth.user.id ? 'items-end' : 'items-start'">
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 mb-1 px-1">
                        {{ msg.user.name }} • {{ formatTime(msg.created_at) }}
                    </span>
                    <div 
                        class="px-3 py-2 rounded-2xl text-sm break-words"
                        :class="msg.user_id === auth.user.id 
                            ? 'bg-blue-600 text-white rounded-tr-sm' 
                            : 'bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-slate-800 rounded-tl-sm'"
                    >
                        {{ msg.message }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                <input 
                    v-model="newMessage"
                    type="text"
                    placeholder="Ketik pesan..."
                    class="flex-1 bg-slate-100 dark:bg-slate-950 border-none rounded-full px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 text-slate-800 dark:text-white"
                    :disabled="isSubmitting"
                />
                <button 
                    type="submit"
                    :disabled="isSubmitting || !newMessage.trim()"
                    class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 transition-colors"
                >
                    <svg class="w-5 h-5 translate-x-[-1px] translate-y-[1px]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</template>
