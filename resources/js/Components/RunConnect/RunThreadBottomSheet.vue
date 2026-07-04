<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import RunThreadChat from './RunThreadChat.vue';

const activeTab = ref('info'); // 'info', 'chat', 'gpx'

const props = defineProps({
    thread: {
        type: Object,
        default: null
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

const emit = defineEmits(['close', 'join', 'leave', 'report-success', 'edit', 'deleted', 'updated']);

const reporting = ref(false);
const reportReason = ref('');
const reportDesc = ref('');
const reportErrors = ref({});
const reportLoading = ref(false);

const uploadStatus = ref('');

const uploadGpx = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('gpx_file', file);

    uploadStatus.value = 'Mengunggah...';
    try {
        const res = await axios.post(`/api/run-connect/threads/${props.thread.id}/upload-gpx`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        uploadStatus.value = 'Berhasil diunggah!';
        props.thread.gpx_file_path = res.data.path; // update local state
    } catch (err) {
        console.error('Error uploading GPX:', err);
        uploadStatus.value = 'Gagal mengunggah. Pastikan format .gpx dan ukuran max 5MB.';
    }
};

const deleting = ref(false);

const deleteThread = async () => {
    if (!confirm('Apakah Anda yakin ingin membatalkan dan menghapus thread lari ini? Peserta lain akan mendapatkan notifikasi.')) {
        return;
    }

    deleting.value = true;
    try {
        await axios.delete(`/api/run-connect/threads/${props.thread.id}`);
        emit('deleted', props.thread.id);
        emit('close');
    } catch (err) {
        console.error('Error deleting thread:', err);
        alert(err.response?.data?.message || 'Gagal menghapus thread.');
    } finally {
        deleting.value = false;
    }
};

const processingApproval = ref(false);

const approveUser = async (participantId) => {
    processingApproval.value = true;
    try {
        const res = await axios.post(`/api/run-connect/threads/${props.thread.id}/approve/${participantId}`);
        emit('updated', res.data.thread, res.data.message);
    } catch (err) {
        console.error('Error approving user:', err);
        alert(err.response?.data?.message || 'Gagal menyetujui runner.');
    } finally {
        processingApproval.value = false;
    }
};

const rejectUser = async (participantId) => {
    if (!confirm('Apakah Anda yakin ingin menolak runner ini?')) {
        return;
    }
    processingApproval.value = true;
    try {
        const res = await axios.post(`/api/run-connect/threads/${props.thread.id}/reject/${participantId}`);
        emit('updated', res.data.thread, res.data.message);
    } catch (err) {
        console.error('Error rejecting user:', err);
        alert(err.response?.data?.message || 'Gagal menolak runner.');
    } finally {
        processingApproval.value = false;
    }
};

const joinedParticipants = computed(() => {
    return (props.thread?.participants || []).filter(p => p.status === 'joined');
});

const pendingParticipants = computed(() => {
    return (props.thread?.participants || []).filter(p => p.status === 'pending');
});

const isUserJoined = computed(() => {
    if (!props.user || !props.thread) return false;
    return isCreator.value || joinedParticipants.value.some(p => p.user_id === props.user.id);
});

const isUserPending = computed(() => {
    if (!props.user || !props.thread) return false;
    return (props.thread?.participants || []).some(p => p.user_id === props.user.id && p.status === 'pending');
});

const isCreator = computed(() => {
    if (!props.user || !props.thread) return false;
    return props.thread.creator_id === props.user.id;
});

const joinedCount = computed(() => {
    return joinedParticipants.value.length;
});

const isFull = computed(() => {
    if (!props.thread) return false;
    return joinedCount.value >= props.thread.quota;
});

watch(() => props.thread, (newThread) => {
    if (newThread && isCreator.value && pendingParticipants.value.length > 0) {
        nextTick(() => {
            const el = document.getElementById('pending-requests-section');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
}, { immediate: true });

const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
};

const formatTime = (timeString) => {
    if (!timeString) return '';
    return timeString.substring(0, 5);
};

const submitReport = async () => {
    if (!reportReason.value) {
        reportErrors.value = { reason: 'Alasan wajib diisi.' };
        return;
    }

    reportLoading.value = true;
    reportErrors.value = {};

    try {
        await axios.post(`/api/run-connect/threads/${props.thread.id}/report`, {
            reason: reportReason.value,
            description: reportDesc.value
        });
        alert('Laporan berhasil dikirim. Kami akan meninjau thread ini segera.');
        emit('report-success');
        reporting.value = false;
        reportReason.value = '';
        reportDesc.value = '';
    } catch (err) {
        alert(err.response?.data?.message || 'Gagal mengirim laporan.');
    } finally {
        reportLoading.value = false;
    }
};

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

const typeColors = {
    'Casual Run': 'bg-green-500/10 text-green-600 dark:text-green-400 border-green-500/20',
    'Long Run': 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
    'Speed Session': 'bg-red-500/10 text-red-650 dark:text-red-400 border-red-500/20',
    'Recovery Run': 'bg-indigo-500/10 text-indigo-650 dark:text-indigo-400 border-indigo-500/20',
    'Race Prep': 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border-yellow-500/20',
    'Community Run': 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20'
};
</script>

<template>
    <div 
        v-if="thread"
        class="fixed inset-x-0 bottom-0 z-40 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 rounded-t-3xl shadow-2xl p-6 transition-transform duration-300 max-h-[85vh] overflow-y-auto no-scrollbar md:max-w-xl md:mx-auto md:bottom-4 md:rounded-3xl md:border text-slate-800 dark:text-slate-100"
    >
        <!-- Pull bar for mobile -->
        <div class="w-12 h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto mb-4 md:hidden"></div>

        <!-- Header -->
        <div class="flex justify-between items-start gap-4 mb-4">
            <div>
                <span 
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider"
                    :class="typeColors[thread.type] || 'bg-slate-500/10 text-slate-400 border-slate-500/20'"
                >
                    {{ thread.type }}
                </span>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mt-2">{{ thread.title }}</h3>
            </div>
            
            <button 
                @click="$emit('close')"
                class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 dark:text-slate-500 hover:text-slate-850 dark:hover:text-white transition-colors cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Tabs -->
        <div class="flex gap-4 mb-4 border-b border-slate-200 dark:border-slate-800">
            <button @click="activeTab = 'info'" :class="activeTab === 'info' ? 'text-blue-600 dark:text-[#ccff00] border-b-2 border-blue-600 dark:border-[#ccff00] font-bold' : 'text-slate-500 dark:text-slate-400 border-b-2 border-transparent'" class="pb-2 px-2 text-xs transition-all uppercase tracking-wider">Info Detail</button>
            <button v-if="isUserJoined" @click="activeTab = 'chat'" :class="activeTab === 'chat' ? 'text-blue-600 dark:text-[#ccff00] border-b-2 border-blue-600 dark:border-[#ccff00] font-bold' : 'text-slate-500 dark:text-slate-400 border-b-2 border-transparent'" class="pb-2 px-2 text-xs transition-all uppercase tracking-wider">Group Chat</button>
            <button @click="activeTab = 'gpx'" :class="activeTab === 'gpx' ? 'text-blue-600 dark:text-[#ccff00] border-b-2 border-blue-600 dark:border-[#ccff00] font-bold' : 'text-slate-500 dark:text-slate-400 border-b-2 border-transparent'" class="pb-2 px-2 text-xs transition-all uppercase tracking-wider">Rute GPX</button>
        </div>

        <!-- Info Tab -->
        <div v-show="activeTab === 'info'">
        <!-- Description -->
        <p v-if="thread.description" class="text-xs text-slate-600 dark:text-slate-300 mb-4 leading-relaxed whitespace-pre-line bg-slate-50 dark:bg-slate-950/40 p-3 rounded-xl border border-slate-150 dark:border-slate-800/80">
            {{ thread.description }}
        </p>

        <!-- Details Grid -->
        <div class="grid grid-cols-2 gap-3 mb-4 text-xs">
            <div class="bg-slate-50 dark:bg-slate-950/20 p-3 border border-slate-200 dark:border-slate-800/80 rounded-xl">
                <span class="text-slate-400 dark:text-slate-550 block uppercase tracking-widest text-[9px] font-bold mb-1">Tanggal</span>
                <strong class="text-slate-700 dark:text-slate-200">{{ formatDate(thread.start_date) }}</strong>
            </div>
            <div class="bg-slate-50 dark:bg-slate-950/20 p-3 border border-slate-200 dark:border-slate-800/80 rounded-xl">
                <span class="text-slate-400 dark:text-slate-550 block uppercase tracking-widest text-[9px] font-bold mb-1">Waktu Mulai</span>
                <strong class="text-slate-700 dark:text-slate-200">{{ formatTime(thread.start_time) }} WIB</strong>
            </div>
            <div class="bg-slate-50 dark:bg-slate-950/20 p-3 border border-slate-200 dark:border-slate-800/80 rounded-xl">
                <span class="text-slate-400 dark:text-slate-550 block uppercase tracking-widest text-[9px] font-bold mb-1">Target Jarak</span>
                <strong class="text-slate-700 dark:text-slate-200">{{ Number(thread.run_distance_km).toFixed(1) }} KM</strong>
            </div>
            <div class="bg-slate-50 dark:bg-slate-950/20 p-3 border border-slate-200 dark:border-slate-800/80 rounded-xl">
                <span class="text-slate-400 dark:text-slate-550 block uppercase tracking-widest text-[9px] font-bold mb-1">Target Pace</span>
                <strong class="text-slate-700 dark:text-slate-200">{{ thread.pace_min && thread.pace_max ? `${thread.pace_min} - ${thread.pace_max}` : 'Bebas' }}</strong>
            </div>
        </div>

        <!-- Location -->
        <div class="bg-slate-50 dark:bg-slate-950/30 p-3 border border-slate-200 dark:border-slate-800/80 rounded-xl mb-4 text-xs">
            <span class="text-slate-400 dark:text-slate-550 block uppercase tracking-widest text-[9px] font-bold mb-1">Titik Kumpul</span>
            <strong class="text-slate-700 dark:text-slate-200 block mb-1">{{ thread.start_location_name }}</strong>
            <div class="flex items-center gap-2 mt-2">
                <a 
                    :href="`https://www.google.com/maps/search/?api=1&query=${thread.start_latitude},${thread.start_longitude}`"
                    target="_blank"
                    class="text-[10px] text-blue-600 dark:text-[#ccff00] hover:underline font-bold"
                >
                    Buka di Google Maps &rarr;
                </a>
                <span class="text-slate-300 dark:text-slate-700">|</span>
                <a 
                    v-if="thread.route_url"
                    :href="thread.route_url"
                    target="_blank"
                    class="text-[10px] text-blue-600 dark:text-[#ccff00] hover:underline font-bold"
                >
                    Tautan Rute Lari &rarr;
                </a>
            </div>
        </div>

        <!-- Notes -->
        <div v-if="thread.notes" class="bg-amber-500/5 dark:bg-slate-950/10 p-3 border border-amber-500/10 dark:border-slate-800/80 rounded-xl mb-4 text-xs text-slate-650 dark:text-slate-300">
            <span class="text-amber-600 dark:text-slate-500 block uppercase tracking-widest text-[9px] font-bold mb-1">Catatan</span>
            {{ thread.notes }}
        </div>

        <!-- Pending Requests (Host Only) -->
        <div v-if="isCreator && pendingParticipants.length > 0" id="pending-requests-section" class="mb-6">
            <h4 class="text-xs font-bold text-amber-600 dark:text-[#ccff00] uppercase tracking-widest mb-3">Permintaan Bergabung ({{ pendingParticipants.length }})</h4>
            <div class="space-y-2 bg-amber-500/5 dark:bg-slate-950/20 p-3 rounded-xl border border-amber-500/10 dark:border-slate-850">
                <div 
                    v-for="p in pendingParticipants" 
                    :key="p.id"
                    class="flex items-center justify-between bg-white dark:bg-slate-900/60 p-2.5 rounded-lg border border-slate-150 dark:border-slate-800/40"
                >
                    <div class="flex items-center gap-2">
                        <img 
                            :src="getAvatarUrl(p.user)" 
                            class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-800 object-cover"
                        />
                        <div class="truncate">
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate">{{ p.user.name }}</p>
                            <span class="text-[9px] text-slate-400 dark:text-slate-500">Menunggu persetujuan</span>
                        </div>
                    </div>
                    <div class="flex gap-1.5">
                        <button 
                            @click="approveUser(p.id)"
                            :disabled="processingApproval"
                            class="px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white text-[10px] font-bold rounded-lg transition-all cursor-pointer disabled:opacity-50"
                        >
                            Setujui
                        </button>
                        <button 
                            @click="rejectUser(p.id)"
                            :disabled="processingApproval"
                            class="px-2.5 py-1 bg-red-500/10 hover:bg-red-500/20 text-red-550 dark:text-red-400 border border-red-500/20 text-[10px] font-bold rounded-lg transition-all cursor-pointer disabled:opacity-50"
                        >
                            Tolak
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participants List -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-xs font-bold text-slate-400 dark:text-slate-550 uppercase tracking-widest">Peserta Joined ({{ joinedCount }} / {{ thread.quota }})</h4>
                <div class="w-24 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                    <div 
                        class="h-full rounded-full transition-all duration-300"
                        :class="theme === 'light' ? 'bg-blue-600' : 'bg-[#ccff00]'"
                        :style="{ width: `${(joinedCount / thread.quota) * 100}%` }"
                    ></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 max-h-36 overflow-y-auto no-scrollbar bg-slate-50 dark:bg-slate-950/20 p-3 rounded-xl border border-slate-200 dark:border-slate-850">
                <div 
                    v-for="p in joinedParticipants" 
                    :key="p.id"
                    class="flex items-center gap-2 bg-white dark:bg-slate-900/60 p-2 rounded-lg border border-slate-150 dark:border-slate-800/40"
                >
                    <img 
                        :src="getAvatarUrl(p.user)" 
                        class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-800 object-cover"
                    />
                    <div class="truncate">
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate">{{ p.user.name }}</p>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500">{{ p.user_id === thread.creator_id ? 'Host' : 'Runner' }}</span>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- Chat Tab -->
        <div v-if="activeTab === 'chat'" class="mb-4">
            <RunThreadChat :thread="thread" :auth="{ user }" />
        </div>

        <!-- GPX Tab -->
        <div v-if="activeTab === 'gpx'" class="mb-4">
            <div class="bg-slate-50 dark:bg-slate-950/20 p-4 rounded-xl border border-slate-200 dark:border-slate-800 text-center">
                <template v-if="thread.gpx_file_path">
                    <p class="text-xs text-slate-500 mb-2">Peta rute GPX sudah tersedia. Anda bisa mengunduhnya:</p>
                    <a :href="thread.gpx_file_path" download class="text-xs text-blue-600 dark:text-[#ccff00] font-bold">Download File GPX</a>
                </template>
                <template v-else>
                    <p class="text-xs text-slate-500 mb-3">Rute GPX belum diunggah oleh pembuat thread.</p>
                    
                    <div v-if="isCreator">
                        <label class="block mb-2 text-xs font-bold text-slate-700 dark:text-slate-200">Unggah File GPX (Max 5MB)</label>
                        <input type="file" @change="uploadGpx" accept=".gpx,.xml" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        <p v-if="uploadStatus" class="mt-2 text-xs text-blue-600">{{ uploadStatus }}</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button 
                v-if="user && !isCreator"
                @click="reporting = !reporting"
                class="p-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white rounded-xl transition-all cursor-pointer text-xs"
                title="Laporkan Thread"
            >
                Laporkan
            </button>

            <div class="flex-grow flex gap-2">
                <button
                    v-if="isUserJoined"
                    @click="$emit('open-rating')"
                    class="py-3 px-4 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold rounded-xl transition-all text-center"
                >
                    Beri Ulasan
                </button>
                <template v-if="!user">
                    <a 
                        href="/login" 
                        class="w-full py-3 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 text-xs font-black rounded-xl transition-all shadow-md text-center block"
                    >
                        Login untuk Gabung
                    </a>
                </template>
                <template v-else>
                    <div v-if="isCreator" class="w-full flex gap-2">
                        <button 
                            @click="$emit('edit', thread)"
                            class="flex-1 py-3 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 text-xs font-semibold rounded-xl transition-all shadow-sm text-center cursor-pointer"
                        >
                            Edit Thread
                        </button>
                        <button 
                            @click="deleteThread"
                            :disabled="deleting"
                            class="flex-1 py-3 bg-red-500/10 hover:bg-red-500/20 text-red-500 dark:text-red-400 border border-red-500/20 text-xs font-semibold rounded-xl transition-all text-center cursor-pointer"
                        >
                            {{ deleting ? 'Menghapus...' : 'Hapus Thread' }}
                        </button>
                    </div>
                    
                    <button 
                        v-else-if="isUserPending"
                        disabled
                        class="w-full py-3 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/25 text-xs font-bold rounded-xl text-center cursor-default"
                    >
                        Menunggu Persetujuan Host
                    </button>

                    <button 
                        v-else-if="isUserJoined"
                        @click="$emit('leave', thread.id)"
                        :disabled="isJoining"
                        class="w-full py-3 bg-red-500/10 hover:bg-red-500/20 text-red-650 dark:text-red-400 border border-red-500/20 text-xs font-bold rounded-xl transition-all cursor-pointer disabled:opacity-50 text-center"
                    >
                        Keluar dari Thread Lari
                    </button>
                    
                    <button 
                        v-else-if="isFull"
                        disabled
                        class="w-full py-3 bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-800 cursor-not-allowed text-center"
                    >
                        Kuota Penuh
                    </button>
                    
                    <button 
                        v-else
                        @click="$emit('join', thread.id)"
                        :disabled="isJoining"
                        class="w-full py-3 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-750 dark:hover:bg-white text-white dark:text-slate-950 text-xs font-black rounded-xl transition-all shadow-md cursor-pointer disabled:opacity-50 text-center"
                    >
                        Gabung Run &rarr;
                    </button>
                </template>
            </div>
        </div>

        <!-- Reporting Form Expand -->
        <div v-if="reporting" class="mt-5 p-4 border border-red-500/20 dark:border-red-500/25 bg-red-500/5 rounded-xl space-y-3">
            <h5 class="text-xs font-bold text-red-500 dark:text-red-400">Laporkan Thread Lari Ini</h5>
            
            <div>
                <label class="block text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold mb-1">Alasan *</label>
                <select 
                    v-model="reportReason"
                    class="w-full bg-white dark:bg-slate-950 border border-slate-250 dark:border-slate-800 text-slate-800 dark:text-slate-200 rounded-lg p-2 text-xs outline-none focus:ring-1 focus:ring-red-500"
                >
                    <option value="">Pilih Alasan...</option>
                    <option value="Spam / Thread Duplikat">Spam / Thread Duplikat</option>
                    <option value="Lokasi Palsu / Mencurigakan">Lokasi Palsu / Mencurigakan</option>
                    <option value="Konten Tidak Layak / Tidak Sopan">Konten Tidak Layak / Tidak Sopan</option>
                    <option value="Penipuan / Komersial Ilegal">Penipuan / Komersial Ilegal</option>
                </select>
                <p v-if="reportErrors.reason" class="text-[10px] text-red-500 mt-1">{{ reportErrors.reason }}</p>
            </div>

            <div>
                <label class="block text-[10px] text-slate-400 dark:text-slate-500 uppercase font-bold mb-1">Keterangan Tambahan</label>
                <textarea 
                    v-model="reportDesc" 
                    rows="2"
                    placeholder="Berikan info tambahan untuk admin..."
                    class="w-full bg-white dark:bg-slate-950 border border-slate-250 dark:border-slate-800 text-slate-800 dark:text-slate-200 rounded-lg p-2 text-xs outline-none focus:ring-1 focus:ring-red-500"
                ></textarea>
            </div>

            <div class="flex items-center gap-2">
                <button 
                    @click="submitReport"
                    :disabled="reportLoading"
                    class="px-4 py-1.5 bg-red-500 hover:bg-red-600 text-white text-xs font-bold rounded-lg cursor-pointer disabled:opacity-50"
                >
                    Kirim Laporan
                </button>
                <button 
                    @click="reporting = false"
                    class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-850 dark:hover:text-white text-xs font-bold rounded-lg cursor-pointer"
                >
                    Batal
                </button>
            </div>
        </div>

    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
