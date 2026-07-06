<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import axios from 'axios';
import RunThreadChat from './RunThreadChat.vue';
import RunnerMiniProfile from './RunnerMiniProfile.vue';

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
    },
    mapboxToken: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['close', 'join', 'leave', 'report-success', 'edit', 'deleted', 'updated']);

const activeTab = ref('info'); // 'info', 'chat', 'gpx', 'recap'
const sheetContainer = ref(null);

const recapForm = ref({
    notes: '',
    image: null
});
const recapPreviewUrl = ref(null);
const uploadingRecap = ref(false);
const toast = ref({ show: false, type: '', message: '' });

const gpxMapContainer = ref(null);
const gpxMap = ref(null);

const renderGpxMap = async () => {
    if (!props.thread || !props.thread.gpx_file_path) return;

    const containerId = 'gpx-map-container-' + props.thread.id;
    const mapContainer = document.getElementById(containerId);
    if (!mapContainer) {
        console.error("Map container not found in DOM");
        return;
    }

    if (gpxMap.value) {
        gpxMap.value.remove();
        gpxMap.value = null;
    }

    const token = props.mapboxToken || '';
    mapboxgl.accessToken = token;

    const style = props.theme === 'light' 
        ? 'mapbox://styles/mapbox/streets-v12' 
        : 'mapbox://styles/mapbox/dark-v11';

    const initialLng = parseFloat(props.thread.start_longitude) || 106.8272;
    const initialLat = parseFloat(props.thread.start_latitude) || -6.1754;

    try {
        gpxMap.value = new mapboxgl.Map({
            container: containerId,
            style: style,
            center: [initialLng, initialLat],
            zoom: 13,
            interactive: true
        });

        gpxMap.value.addControl(new mapboxgl.NavigationControl(), 'top-right');

        setTimeout(() => {
            if (gpxMap.value) {
                gpxMap.value.resize();
            }
        }, 250);
    } catch (e) {
        console.error("Failed to initialize Mapbox:", e);
        return;
    }

    try {
        const fetchPath = props.thread.gpx_file_path.startsWith('/') ? props.thread.gpx_file_path : '/' + props.thread.gpx_file_path;
        const res = await fetch(fetchPath);
        const gpxText = await res.text();
        
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(gpxText, 'text/xml');
        const trkpts = xmlDoc.getElementsByTagName('trkpt');
        
        const coordinates = [];
        for (let i = 0; i < trkpts.length; i++) {
            const lat = parseFloat(trkpts[i].getAttribute('lat'));
            const lon = parseFloat(trkpts[i].getAttribute('lon'));
            if (!isNaN(lat) && !isNaN(lon)) {
                coordinates.push([lon, lat]);
            }
        }
        
        if (coordinates.length > 0 && gpxMap.value) {
            gpxMap.value.on('load', () => {
                if(!gpxMap.value) return;
                gpxMap.value.addSource('gpx-source', {
                    type: 'geojson',
                    data: {
                        type: 'Feature',
                        properties: {},
                        geometry: {
                            type: 'LineString',
                            coordinates: coordinates
                        }
                    }
                });
                
                gpxMap.value.addLayer({
                    id: 'gpx-route',
                    type: 'line',
                    source: 'gpx-source',
                    layout: {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    paint: {
                        'line-color': props.theme === 'dark' ? '#ccff00' : '#2563eb',
                        'line-width': 4
                    }
                });

                // Add start marker
                new mapboxgl.Marker({ color: '#10b981' }) // green for start
                    .setLngLat(coordinates[0])
                    .addTo(gpxMap.value);

                // Add end marker
                new mapboxgl.Marker({ color: '#ef4444' }) // red for end
                    .setLngLat(coordinates[coordinates.length - 1])
                    .addTo(gpxMap.value);

                const bounds = coordinates.reduce((bounds, coord) => {
                    return bounds.extend(coord);
                }, new mapboxgl.LngLatBounds(coordinates[0], coordinates[0]));
                
                gpxMap.value.fitBounds(bounds, {
                    padding: 40,
                    duration: 1000
                });
            });
        }
    } catch (err) {
        console.error('Failed to render GPX in bottom sheet:', err);
    }
};

watch(activeTab, (newTab) => {
    if (sheetContainer.value) {
        sheetContainer.value.scrollTop = 0;
    }
    if (newTab === 'gpx') {
        setTimeout(() => {
            if (gpxMap.value) gpxMap.value.resize();
        }, 150);
        setTimeout(() => {
            if (gpxMap.value) gpxMap.value.resize();
        }, 500);
    }
});

watch(() => props.thread, (newThread) => {
    if (newThread) {
        activeTab.value = 'info';
        setTimeout(() => {
            renderGpxMap();
        }, 300);
    }
});

const reporting = ref(false);
const reportReason = ref('');
const reportDesc = ref('');
const reportErrors = ref({});
const reportLoading = ref(false);

// Share functionality
const isShareOpen = ref(false);
const shareCopied = ref(false);

const getShareUrl = () => {
    return `${window.location.origin}/cari-teman-lari?thread=${props.thread?.id}`;
};

const getShareText = () => {
    if (!props.thread) return '';
    const t = props.thread;
    const date = new Date(t.start_date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
    const time = t.start_time?.substring(0, 5) || '';
    return `🏃 Yuk lari bareng!\n\n*${t.title}*\n📍 ${t.start_location_name}\n📅 ${date} jam ${time} WIB\n🎯 ${Number(t.run_distance_km).toFixed(1)} KM | Pace ${t.pace_min && t.pace_max ? t.pace_min + '-' + t.pace_max : 'Bebas'}\n👥 Kuota: ${t.quota} orang\n\nGabung sekarang:`;
};

const shareWhatsApp = () => {
    const text = encodeURIComponent(getShareText() + '\n' + getShareUrl());
    window.open(`https://wa.me/?text=${text}`, '_blank');
};

const shareTelegram = () => {
    const text = encodeURIComponent(getShareText());
    const url = encodeURIComponent(getShareUrl());
    window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
};

const copyShareLink = async () => {
    try {
        await navigator.clipboard.writeText(getShareUrl());
        shareCopied.value = true;
        setTimeout(() => { shareCopied.value = false; }, 2000);
    } catch (err) {
        // Fallback for older browsers
        const ta = document.createElement('textarea');
        ta.value = getShareUrl();
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        shareCopied.value = true;
        setTimeout(() => { shareCopied.value = false; }, 2000);
    }
};

// Countdown timer
const countdownText = ref('');
const countdownClass = ref('');
let countdownInterval = null;

const updateCountdown = () => {
    if (!props.thread) {
        countdownText.value = '';
        return;
    }
    const t = props.thread;
    const dateStr = (typeof t.start_date === 'string' ? t.start_date : t.start_date).substring(0, 10);
    const target = new Date(`${dateStr}T${t.start_time}`);
    const now = new Date();
    const diff = target - now;

    if (diff <= 0) {
        // Already past
        const absDiff = Math.abs(diff);
        const hoursAgo = Math.floor(absDiff / (1000 * 60 * 60));
        if (hoursAgo < 1) {
            countdownText.value = '🏃 Sedang berlangsung!';
            countdownClass.value = 'text-green-500 bg-green-500/10 border-green-500/20';
        } else if (hoursAgo < 3) {
            countdownText.value = '🏁 Baru selesai';
            countdownClass.value = 'text-blue-500 bg-blue-500/10 border-blue-500/20';
        } else {
            countdownText.value = '✅ Selesai';
            countdownClass.value = 'text-slate-400 bg-slate-500/10 border-slate-500/20';
        }
        return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

    if (days > 0) {
        countdownText.value = `⏱️ Mulai dalam ${days}h ${hours}j ${minutes}m`;
        countdownClass.value = 'text-slate-500 bg-slate-500/10 border-slate-500/20';
    } else if (hours > 0) {
        countdownText.value = `⏱️ Mulai dalam ${hours}j ${minutes}m`;
        countdownClass.value = 'text-amber-500 bg-amber-500/10 border-amber-500/20';
    } else if (minutes > 0) {
        countdownText.value = `🔥 Segera! ${minutes} menit lagi`;
        countdownClass.value = 'text-red-500 bg-red-500/10 border-red-500/20 animate-pulse';
    } else {
        countdownText.value = '🏃 Mulai sekarang!';
        countdownClass.value = 'text-green-500 bg-green-500/10 border-green-500/20 animate-pulse';
    }
};

watch(() => props.thread, () => {
    updateCountdown();
}, { immediate: true });

onMounted(() => {
    countdownInterval = setInterval(updateCountdown, 30000); // update every 30s
});

onUnmounted(() => {
    if (countdownInterval) clearInterval(countdownInterval);
});

// Runner Mini-Profile
const selectedRunner = ref(null);
const showMiniProfile = ref(false);

const openRunnerProfile = (user) => {
    if (!user) return;
    selectedRunner.value = user;
    showMiniProfile.value = true;
};

const closeMiniProfile = () => {
    showMiniProfile.value = false;
    selectedRunner.value = null;
};

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
        uploadStatus.value = 'Gagal mengunggah. Pastikan format file sesuai dan ukuran max 5MB.';
    }
};

const deleteGpx = async () => {
    if (!confirm('Apakah Anda yakin ingin menghapus rute GPX ini?')) return;
    
    try {
        await axios.delete(`/api/run-connect/threads/${props.thread.id}/gpx`);
        props.thread.gpx_file_path = null;
        uploadStatus.value = 'File GPX berhasil dihapus.';
        setTimeout(() => { uploadStatus.value = ''; }, 3000);
    } catch (err) {
        console.error('Error deleting GPX:', err);
        alert('Gagal menghapus file GPX.');
    }
};

const deleting = ref(false);

const confirmDeleteThread = async () => {
    deleting.value = true;
    try {
        await axios.delete(`/api/run-connect/threads/${props.thread.id}`);
        toast.value = { show: true, type: 'success', message: 'Thread lari berhasil dibatalkan.' };
        emit('deleted', props.thread.id);
        emit('close');
    } catch (err) {
        console.error('Delete thread error:', err);
        toast.value = { show: true, type: 'error', message: 'Gagal membatalkan thread.' };
    } finally {
        deleting.value = false;
    }
};

const handleRecapImageChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        toast.value = { show: true, type: 'error', message: 'Ukuran maksimal foto adalah 5MB.' };
        return;
    }
    recapForm.value.image = file;
    recapPreviewUrl.value = URL.createObjectURL(file);
};

const submitRecap = async () => {
    if (!recapForm.value.notes && !recapForm.value.image) {
        toast.value = { show: true, type: 'error', message: 'Tolong isi catatan atau foto.' };
        return;
    }
    
    uploadingRecap.value = true;
    try {
        const formData = new FormData();
        if (recapForm.value.notes) formData.append('recap_notes', recapForm.value.notes);
        if (recapForm.value.image) formData.append('recap_image', recapForm.value.image);
        
        const res = await axios.post(`/api/run-connect/threads/${props.thread.id}/recap`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        toast.value = { show: true, type: 'success', message: 'Rekap berhasil diunggah!' };
        // Clean up
        recapForm.value.notes = '';
        recapForm.value.image = null;
        recapPreviewUrl.value = null;
        
        emit('updated', res.data.thread);
    } catch (err) {
        console.error('Upload recap error:', err);
        toast.value = { show: true, type: 'error', message: 'Gagal mengunggah rekap.' };
    } finally {
        uploadingRecap.value = false;
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
    return isCreator.value || joinedParticipants.value.some(p => p.user_id == props.user.id);
});

const isUserPending = computed(() => {
    if (!props.user || !props.thread) return false;
    return (props.thread?.participants || []).some(p => p.user_id == props.user.id && p.status === 'pending');
});

const isCreator = computed(() => {
    if (!props.user || !props.thread) return false;
    return props.thread.creator_id == props.user.id;
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
        ref="sheetContainer"
        class="fixed inset-x-0 bottom-0 z-40 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 rounded-t-3xl shadow-2xl p-6 transition-transform duration-300 h-[80vh] md:h-[75vh] overflow-y-auto no-scrollbar md:max-w-xl md:mx-auto md:bottom-4 md:rounded-3xl md:border text-slate-800 dark:text-slate-100"
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
            
            <div class="flex gap-2">
                <button 
                    @click="$emit('close')"
                    class="p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg text-slate-400 dark:text-slate-500 hover:text-slate-850 dark:hover:text-white transition-colors cursor-pointer"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <div>
        <!-- Tabs -->
        <div class="flex gap-4 mb-4 border-b border-slate-200 dark:border-slate-800">
            <button @click="activeTab = 'info'" :class="activeTab === 'info' ? 'text-blue-600 dark:text-[#ccff00] border-b-2 border-blue-600 dark:border-[#ccff00] font-bold' : 'text-slate-500 dark:text-slate-400 border-b-2 border-transparent'" class="pb-2 px-2 text-xs transition-all uppercase tracking-wider">Info Detail</button>
            <button @click="activeTab = 'chat'" :class="activeTab === 'chat' ? 'text-blue-600 dark:text-[#ccff00] border-b-2 border-blue-600 dark:border-[#ccff00] font-bold' : 'text-slate-500 dark:text-slate-400 border-b-2 border-transparent'" class="pb-2 px-2 text-xs transition-all uppercase tracking-wider">Group Chat</button>
            <button @click="activeTab = 'gpx'" :class="activeTab === 'gpx' ? 'text-blue-600 dark:text-[#ccff00] border-b-2 border-blue-600 dark:border-[#ccff00] font-bold' : 'text-slate-500 dark:text-slate-400 border-b-2 border-transparent'" class="pb-2 px-2 text-xs transition-all uppercase tracking-wider">Rute GPX</button>
            <button v-if="thread.status === 'completed'" @click="activeTab = 'recap'" :class="activeTab === 'recap' ? 'text-blue-600 dark:text-[#ccff00] border-b-2 border-blue-600 dark:border-[#ccff00] font-bold' : 'text-slate-500 dark:text-slate-400 border-b-2 border-transparent'" class="pb-2 px-2 text-xs transition-all uppercase tracking-wider text-amber-500">Recap</button>
        </div>

        <!-- Countdown Timer Banner -->
        <div v-if="countdownText" :class="countdownClass" class="text-xs font-bold px-4 py-2.5 rounded-xl border mb-4 text-center tracking-wide">
            {{ countdownText }}
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
                    <div class="flex items-center gap-2 cursor-pointer" @click="openRunnerProfile(p.user)">
                        <img 
                            :src="getAvatarUrl(p.user)" 
                            class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-800 object-cover hover:ring-2 hover:ring-blue-500/50 dark:hover:ring-[#ccff00]/50 transition-all"
                        />
                        <div class="truncate">
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200 truncate hover:text-blue-600 dark:hover:text-[#ccff00] transition-colors">{{ p.user.name }}</p>
                            <span class="text-[9px] text-slate-400 dark:text-slate-500">Tap untuk lihat profil</span>
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
                    @click="openRunnerProfile(p.user)"
                    class="flex items-center gap-2 bg-white dark:bg-slate-900/60 p-2 rounded-lg border border-slate-150 dark:border-slate-800/40 cursor-pointer hover:border-blue-500/40 dark:hover:border-[#ccff00]/40 transition-colors"
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
            <RunThreadChat v-if="isUserJoined" :thread="thread" :auth="{ user }" />
            <div v-else class="bg-slate-50 dark:bg-slate-950/20 p-6 rounded-xl border border-slate-200 dark:border-slate-800 text-center flex flex-col items-center gap-3">
                <svg class="w-10 h-10 text-slate-400 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div>
                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-200">Group Chat Terkunci</h5>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Fitur chat grup ini hanya dapat diakses oleh runner yang telah resmi bergabung.</p>
                </div>
            </div>
        </div>

        <!-- GPX Tab -->
        <div v-show="activeTab === 'gpx'" class="mb-4">
            <div class="bg-slate-50 dark:bg-slate-950/20 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                <template v-if="thread && thread.gpx_file_path">
                    <!-- Mapbox Container for GPX Route -->
                    <div :id="'gpx-map-container-' + thread.id" class="w-full h-64 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800 mb-4"></div>
                    
                    <p class="text-[10px] text-slate-500 mb-2">Peta rute GPX tersedia. Anda bisa mengunduhnya:</p>
                    <div class="flex gap-2 items-center mb-4">
                        <a :href="thread.gpx_file_path" download class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/30 dark:hover:bg-blue-800/50 text-blue-700 dark:text-blue-400 rounded-lg text-xs font-bold transition-colors">Download GPX</a>
                        <button v-if="thread.creator_id === user?.id" @click="deleteGpx" class="px-3 py-1.5 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-800/50 text-red-700 dark:text-red-400 rounded-lg text-xs font-bold transition-colors">Hapus GPX</button>
                    </div>
                    
                    <div v-if="thread.creator_id === user?.id" class="mt-4 border-t border-slate-200 dark:border-slate-800 pt-4">
                        <label class="block mb-2 text-xs font-bold text-slate-700 dark:text-slate-200">Ganti File GPX (Max 5MB)</label>
                        <input type="file" @change="uploadGpx" accept=".gpx,.xml" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-[#ccff00]/10 dark:file:text-[#ccff00]" />
                    </div>
                </template>
                <template v-else>
                    <p class="text-xs text-slate-500 mb-3">Rute GPX belum diunggah oleh pembuat thread.</p>
                    <div v-if="thread.creator_id === user?.id">
                        <label class="block mb-2 text-xs font-bold text-slate-700 dark:text-slate-200">Unggah File GPX (Max 5MB)</label>
                        <input type="file" @change="uploadGpx" accept=".gpx,.xml" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-[#ccff00]/10 dark:file:text-[#ccff00]" />
                    </div>
                </template>
                <p v-if="uploadStatus" class="mt-2 text-xs text-blue-600">{{ uploadStatus }}</p>
            </div>
        </div>

        <!-- Recap Tab -->
        <div v-if="activeTab === 'recap' && thread.status === 'completed'" class="mb-4">
            <template v-if="thread.recap_notes || thread.recap_image_path">
                <div class="bg-slate-50 dark:bg-slate-950/20 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-3">Post-Run Recap</h5>
                    <img v-if="thread.recap_image_path" :src="`/${thread.recap_image_path}`" class="w-full h-auto rounded-xl object-cover mb-3 shadow" />
                    <p v-if="thread.recap_notes" class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ thread.recap_notes }}</p>
                </div>
            </template>
            <template v-else-if="isCreator">
                <div class="bg-slate-50 dark:bg-slate-950/20 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
                    <h5 class="text-xs font-bold text-slate-700 dark:text-slate-200 mb-3">Unggah Recap (Khusus Host)</h5>
                    <div class="space-y-3">
                        <div>
                            <label class="block mb-1 text-[10px] font-bold text-slate-500 uppercase">Catatan Keseruan (Opsional)</label>
                            <textarea v-model="recapForm.notes" rows="3" class="w-full text-xs p-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg outline-none focus:ring-1 focus:ring-blue-500" placeholder="Ceritakan keseruan lari hari ini..."></textarea>
                        </div>
                        <div>
                            <label class="block mb-1 text-[10px] font-bold text-slate-500 uppercase">Foto Bersama (Max 5MB)</label>
                            <input type="file" @change="handleRecapImageChange" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-[10px] file:font-bold file:bg-blue-50 file:text-blue-700 dark:file:bg-slate-800 dark:file:text-slate-300 hover:file:bg-blue-100" />
                            <img v-if="recapPreviewUrl" :src="recapPreviewUrl" class="w-full h-32 object-cover rounded-lg mt-2 border border-slate-200 dark:border-slate-700" />
                        </div>
                        <button @click="submitRecap" :disabled="uploadingRecap" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-colors disabled:opacity-50">
                            {{ uploadingRecap ? 'Mengunggah...' : 'Simpan Recap' }}
                        </button>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="bg-slate-50 dark:bg-slate-950/20 p-6 rounded-xl border border-slate-200 dark:border-slate-800 text-center flex flex-col items-center gap-3">
                    <span class="text-2xl">📸</span>
                    <div>
                        <h5 class="text-xs font-bold text-slate-700 dark:text-slate-200">Belum ada Recap</h5>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Host belum mengunggah catatan atau foto setelah lari ini.</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <!-- Share Button -->
            <button 
                @click="isShareOpen = !isShareOpen"
                class="p-3 bg-blue-500/10 dark:bg-blue-500/10 hover:bg-blue-500/20 dark:hover:bg-blue-500/20 text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 rounded-xl transition-all cursor-pointer text-xs font-semibold border border-blue-500/20"
                title="Bagikan Thread"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
            </button>

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
                        @click="$emit('leave', thread.id)"
                        :disabled="isJoining"
                        class="w-full py-3 bg-amber-500/10 hover:bg-red-500/10 hover:text-red-500 hover:border-red-500/20 text-amber-600 dark:text-amber-400 border border-amber-500/25 text-xs font-bold rounded-xl text-center cursor-pointer transition-all flex items-center justify-center gap-1.5"
                    >
                        <svg class="w-4 h-4 text-amber-500 dark:text-amber-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Menunggu Persetujuan (Batalkan)</span>
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

        <!-- Share Panel -->
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-4"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-4"
        >
            <div v-if="isShareOpen" class="mt-5 p-4 border border-blue-500/20 dark:border-blue-500/25 bg-blue-500/5 dark:bg-blue-500/5 rounded-xl space-y-3">
                <h5 class="text-xs font-bold text-blue-600 dark:text-blue-400 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    Bagikan Thread Lari
                </h5>
                <div class="flex gap-2">
                    <!-- WhatsApp -->
                    <button 
                        @click="shareWhatsApp"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-[#25D366] hover:bg-[#20bd5a] text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </button>
                    <!-- Telegram -->
                    <button 
                        @click="shareTelegram"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-[#0088cc] hover:bg-[#0077b5] text-white text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm"
                    >
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        Telegram
                    </button>
                    <!-- Copy Link -->
                    <button 
                        @click="copyShareLink"
                        class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold rounded-xl transition-all cursor-pointer shadow-sm border"
                        :class="shareCopied 
                            ? 'bg-green-500 text-white border-green-500' 
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700'"
                    >
                        <svg v-if="!shareCopied" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ shareCopied ? 'Tersalin!' : 'Copy Link' }}
                    </button>
                </div>
            </div>
        </transition>
        </div>

        <!-- Runner Mini-Profile Modal -->
        <RunnerMiniProfile 
            v-if="showMiniProfile && selectedRunner" 
            :user="selectedRunner" 
            @close="closeMiniProfile" 
        />

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
