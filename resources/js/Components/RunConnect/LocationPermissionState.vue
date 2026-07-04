<script setup>
import { ref } from 'vue';

const props = defineProps({
    theme: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['location-selected', 'permission-denied']);

const loading = ref(false);

const getIpLocation = () => {
    loading.value = true;
    fetch('https://ipapi.co/json/')
        .then(res => res.json())
        .then(data => {
            loading.value = false;
            if (data && data.latitude && data.longitude) {
                emit('location-selected', {
                    lat: data.latitude,
                    lng: data.longitude,
                    name: `IP: ${data.city || 'Lokasi Anda'}`
                });
            } else {
                throw new Error('invalid_data');
            }
        })
        .catch(err => {
            console.error('IP Geolocation failed:', err);
            loading.value = false;
            emit('permission-denied');
        });
};

const requestLocation = () => {
    loading.value = true;
    if (!navigator.geolocation) {
        getIpLocation();
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            loading.value = false;
            emit('location-selected', {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                name: 'Lokasi Anda saat ini'
            });
        },
        (error) => {
            console.warn("Geolocation error, falling back to IP:", error);
            getIpLocation();
        },
        { enableHighAccuracy: false, timeout: 5000, maximumAge: 0 }
    );
};

// Preset cities fallback
const cities = [
    { name: 'Jakarta (GBK)', lat: -6.2183, lng: 106.8025 },
    { name: 'Bandung (Saparua)', lat: -6.9079, lng: 107.6186 },
    { name: 'Surabaya (Kertajaya)', lat: -7.2759, lng: 112.7663 },
    { name: 'Bali (Bajra Sandhi)', lat: -8.6723, lng: 115.2341 }
];

const selectCity = (city) => {
    emit('location-selected', {
        lat: city.lat,
        lng: city.lng,
        name: city.name
    });
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm dark:shadow-none rounded-3xl p-8 max-w-md w-full text-center space-y-6 transition-colors duration-300">
        <!-- Pulse Icon -->
        <div class="relative w-16 h-16 mx-auto flex items-center justify-center bg-blue-50 dark:bg-slate-950 rounded-full border border-blue-100 dark:border-slate-800">
            <span class="animate-ping absolute inline-flex h-8 w-8 rounded-full bg-blue-400 dark:bg-neon/30 opacity-75"></span>
            <svg class="w-8 h-8 text-blue-600 dark:text-[#ccff00] relative z-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>

        <div class="space-y-2">
            <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-wider italic">Temukan Pelari di Sekitar Anda</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed px-4">
                Aktifkan izin lokasi untuk mencari running thread dan komunitas pelari terdekat secara real-time.
            </p>
        </div>

        <!-- Main CTA -->
        <button 
            @click="requestLocation"
            :disabled="loading"
            class="w-full py-3.5 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-750 dark:hover:bg-white text-white dark:text-slate-950 font-black rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
        >
            <span v-if="loading" class="w-4 h-4 border-2 border-white dark:border-slate-950 border-t-transparent rounded-full animate-spin"></span>
            <span>Deteksi Lokasi Saya</span>
        </button>

        <!-- Preset Cities Divider -->
        <div class="relative flex py-2 items-center">
            <div class="flex-grow border-t border-slate-150 dark:border-slate-800/80"></div>
            <span class="flex-shrink mx-4 text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest">Atau Pilih Kota</span>
            <div class="flex-grow border-t border-slate-150 dark:border-slate-800/80"></div>
        </div>

        <!-- Presets Grid -->
        <div class="grid grid-cols-2 gap-2.5">
            <button 
                v-for="city in cities" 
                :key="city.name"
                @click="selectCity(city)"
                class="p-2.5 bg-slate-55 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/60 rounded-xl text-xs font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-850 hover:border-slate-300 dark:hover:border-slate-700 transition-all cursor-pointer text-center"
            >
                {{ city.name }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.text-neon {
    color: #ccff00;
}
.bg-neon {
    background-color: #ccff00;
}
</style>
