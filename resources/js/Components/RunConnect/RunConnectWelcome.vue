<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    theme: {
        type: String,
        default: 'dark'
    },
    auth: {
        type: Object,
        default: () => ({ user: null })
    },
    cachedLocation: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['location-selected', 'permission-denied', 'open-login']);

const loading = ref(false);

// Preset cities fallback
const cities = [
    { name: 'Jakarta (GBK)', lat: -6.2183, lng: 106.8025 },
    { name: 'Malang (Kayutangan)', lat: -7.9786, lng: 112.6318 },
    { name: 'Jogja', lat: -7.7926, lng: 110.3658 },
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

// Slider Data
const slides = [
    {
        icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z',
        title: 'Temukan Buddy Lari',
        desc: 'Cari teman atau grup lari di sekitar lokasi Anda secara real-time.'
    },
    {
        icon: 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7',
        title: 'Eksplorasi Rute GPX',
        desc: 'Bagikan dan ikuti rute lari interaktif dengan format GPX.'
    },
    {
        icon: 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
        title: 'Obrolan Komunitas',
        desc: 'Diskusi langsung dengan pelari lain dalam satu grup secara langsung.'
    }
];

const activeSlide = ref(0);
let slideInterval = null;

onMounted(() => {
    slideInterval = setInterval(() => {
        activeSlide.value = (activeSlide.value + 1) % slides.length;
    }, 4000);
});

onUnmounted(() => {
    if (slideInterval) clearInterval(slideInterval);
});

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

const handleStartExploration = () => {
    if (props.cachedLocation) {
        // Automatically skip if we already have location cached
        emit('location-selected', props.cachedLocation);
        return;
    }

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
</script>

<template>
    <div class="backdrop-blur-xl bg-white/95 dark:bg-slate-900/95 border border-white/40 dark:border-slate-800/85 shadow-2xl rounded-3xl p-6 sm:p-8 max-w-md w-full text-center space-y-6 transition-all duration-300 relative overflow-hidden">
        
        <!-- Slider Carousel -->
        <div class="relative h-[220px] flex flex-col items-center justify-center z-10 mb-4">
            <transition
                mode="out-in"
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-y-4"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-4"
            >
                <div :key="activeSlide" class="flex flex-col items-center w-full">
                    <!-- Icon -->
                    <div class="relative w-16 h-16 mx-auto flex items-center justify-center bg-blue-50 dark:bg-slate-950 rounded-full border border-blue-100 dark:border-slate-800 mb-6 shadow-inner">
                        <svg class="w-8 h-8 text-blue-600 dark:text-[#ccff00]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="slides[activeSlide].icon" />
                        </svg>
                    </div>

                    <!-- Texts -->
                    <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-wider italic mb-2 px-2">
                        {{ slides[activeSlide].title }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed px-6 min-h-[40px]">
                        {{ slides[activeSlide].desc }}
                    </p>
                </div>
            </transition>

            <!-- Dots Indicator -->
            <div class="absolute bottom-0 left-0 right-0 flex justify-center gap-1.5 translate-y-2">
                <button 
                    v-for="(_, index) in slides" 
                    :key="index"
                    @click="activeSlide = index"
                    class="h-1.5 rounded-full transition-all duration-300"
                    :class="activeSlide === index ? 'bg-blue-600 dark:bg-[#ccff00] w-5' : 'bg-slate-300 dark:bg-slate-700 w-1.5 hover:bg-slate-400 dark:hover:bg-slate-600'"
                ></button>
            </div>
        </div>

        <!-- Call to Action Area -->
        <div class="pt-2 z-10 relative space-y-4">
            
            <template v-if="!auth.user">
                <div class="grid grid-cols-2 gap-3">
                    <button 
                        @click="emit('open-login')"
                        class="w-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-white text-xs font-bold py-3 px-4 rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        Login
                    </button>
                    <a 
                        href="/register"
                        class="w-full bg-blue-650 dark:bg-indigo-600 hover:bg-blue-700 dark:hover:bg-indigo-500 text-white text-xs font-bold py-3 px-4 rounded-xl shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer"
                    >
                        Register
                    </a>
                </div>
                
                <div class="relative flex py-1 items-center">
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
                    <span class="flex-shrink-0 mx-4 text-slate-400 text-[10px] uppercase font-semibold">Atau</span>
                    <div class="flex-grow border-t border-slate-200 dark:border-slate-800"></div>
                </div>

                <button 
                    @click="handleStartExploration" 
                    :disabled="loading"
                    class="w-full bg-transparent border-2 border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300 text-xs font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Eksplorasi Peta (Guest)
                </button>
            </template>

            <template v-else>
                <button 
                    @click="handleStartExploration" 
                    :disabled="loading"
                    class="w-full bg-blue-650 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-[#b3e600] text-white dark:text-slate-900 text-sm font-black italic tracking-wide py-3.5 px-4 rounded-xl shadow-lg transition-all transform hover:-translate-y-0.5 hover:shadow-xl flex items-center justify-center gap-2 cursor-pointer"
                >
                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span v-else>MULAI EKSPLORASI SEKARANG</span>
                </button>
            </template>
        </div>

        <!-- Preset Cities Divider -->
        <div class="relative flex py-1 items-center mt-4">
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
                class="p-2 bg-white/40 dark:bg-slate-950/40 border border-slate-205/30 dark:border-slate-800/50 rounded-xl text-[11px] font-semibold text-slate-600 dark:text-slate-300 hover:bg-white/80 dark:hover:bg-slate-950/80 hover:border-slate-300 dark:hover:border-slate-600 transition-all cursor-pointer text-center"
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
