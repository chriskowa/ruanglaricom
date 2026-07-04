<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

const props = defineProps({
    mapboxToken: {
        type: String,
        default: ''
    },
    userLocation: {
        type: Object,
        default: null
    },
    threads: {
        type: Array,
        required: true
    },
    theme: {
        type: String,
        default: 'dark'
    }
});

const emit = defineEmits(['select-thread', 'map-moved']);

const mapContainer = ref(null);
const map = ref(null);
const markers = ref([]);
let userMarker = null;
const realUserLocation = ref(null);
const detectingLocation = ref(false);

const defaultToken = '';

const getAvatarUrl = (user) => {
    if (!user) return 'https://avatar.iran.liara.run/public/boy';
    if (user.avatar && !user.avatar.includes('default-')) {
        if (user.avatar.startsWith('http')) return user.avatar;
        if (user.avatar.startsWith('images/')) return '/' + user.avatar;
        return '/storage/' + user.avatar;
    }
    if (user.gender === 'female') {
        return 'https://avatar.iran.liara.run/public/girl?username=' + encodeURIComponent(user.name);
    }
    return 'https://avatar.iran.liara.run/public/boy?username=' + encodeURIComponent(user.name);
};

const initMap = () => {
    if (!mapContainer.value) return;

    const token = props.mapboxToken || defaultToken;
    mapboxgl.accessToken = token;

    const style = props.theme === 'light' 
        ? 'mapbox://styles/mapbox/streets-v12' 
        : 'mapbox://styles/mapbox/dark-v11';

    const center = props.userLocation 
        ? [props.userLocation.lng, props.userLocation.lat] 
        : [106.8272, -6.1754];

    map.value = new mapboxgl.Map({
        container: mapContainer.value,
        style: style,
        center: center,
        zoom: 13,
        attributionControl: false
    });

    map.value.addControl(new mapboxgl.NavigationControl(), 'top-right');

    map.value.on('load', () => {
        updateUserMarker();
        updateThreadMarkers();
    });

    map.value.on('dragend', handleMapMoved);
    map.value.on('zoomend', handleMapMoved);
};

const handleMapMoved = () => {
    if (!map.value) return;
    const center = map.value.getCenter();
    emit('map-moved', {
        lat: center.lat,
        lng: center.lng
    });
};

const detectCurrentLocation = () => {
    detectingLocation.value = true;
    if (!navigator.geolocation) {
        // Fallback to IP geolocation
        fetch('https://ipapi.co/json/')
            .then(res => res.json())
            .then(data => {
                detectingLocation.value = false;
                if (data && data.latitude && data.longitude) {
                    const loc = {
                        lat: data.latitude,
                        lng: data.longitude,
                        name: `IP: ${data.city || 'Lokasi Anda'}`
                    };
                    emit('location-selected', loc);
                }
            })
            .catch(err => {
                console.error('IP Geolocation failed:', err);
                detectingLocation.value = false;
            });
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            detectingLocation.value = false;
            const loc = {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                name: 'Lokasi Anda saat ini'
            };
            emit('location-selected', loc);
        },
        (error) => {
            console.warn("Geolocation error, falling back to IP:", error);
            // Fallback to IP
            fetch('https://ipapi.co/json/')
                .then(res => res.json())
                .then(data => {
                    detectingLocation.value = false;
                    if (data && data.latitude && data.longitude) {
                        const loc = {
                            lat: data.latitude,
                            lng: data.longitude,
                            name: `IP: ${data.city || 'Lokasi Anda'}`
                        };
                        emit('location-selected', loc);
                    }
                })
                .catch(err2 => {
                    console.error('IP Geolocation fallback failed:', err2);
                    detectingLocation.value = false;
                });
        },
        { enableHighAccuracy: false, timeout: 5000, maximumAge: 0 }
    );
};

const updateUserMarker = () => {
    if (!map.value) return;
    if (userMarker) userMarker.remove();
    
    const loc = realUserLocation.value || props.userLocation;
    if (!loc) return;

    const el = document.createElement('div');
    el.className = 'user-pulse-marker';

    userMarker = new mapboxgl.Marker(el)
        .setLngLat([loc.lng, loc.lat])
        .addTo(map.value);
};

const updateThreadMarkers = () => {
    if (!map.value) return;

    // Clear old markers
    markers.value.forEach(m => m.remove());
    markers.value = [];

    // Add new custom card markers
    props.threads.forEach(thread => {
        const el = document.createElement('div');
        el.className = 'custom-run-marker';
        
        const avatarUrl = getAvatarUrl(thread.creator);
        const distanceStr = Number(thread.run_distance_km).toFixed(1);
        const typeStr = thread.type.split(' ')[0]; // Take first word e.g. "Casual"

        // Inject card structure into marker
        el.innerHTML = `
            <img src="${avatarUrl}" class="marker-avatar" />
            <div class="marker-content">
                <span class="marker-distance">${distanceStr}K</span>
                <span class="marker-type">${typeStr}</span>
            </div>
        `;

        // Apply dynamic theme borders
        let badgeColor = thread.status === 'full' ? '#ef4444' : (props.theme === 'light' ? '#c11e09' : '#ccff00');
        el.style.borderColor = badgeColor;

        const popup = new mapboxgl.Popup({ offset: 15, closeButton: false })
            .setHTML(`
                <div class="p-2 text-xs font-sans bg-slate-900 text-white rounded-lg">
                    <p class="font-bold border-b border-slate-800 pb-1 mb-1">${thread.title}</p>
                    <p class="text-[10px] text-slate-400">Host: ${thread.creator.name}</p>
                    <p class="text-[9px] text-neon mt-1">Klik untuk detail</p>
                </div>
            `);

        const marker = new mapboxgl.Marker(el)
            .setLngLat([thread.start_longitude, thread.start_latitude])
            .setPopup(popup)
            .addTo(map.value);

        el.addEventListener('click', () => {
            emit('select-thread', thread);
        });

        el.addEventListener('mouseenter', () => marker.togglePopup());
        el.addEventListener('mouseleave', () => marker.togglePopup());

        markers.value.push(marker);
    });
};

watch(() => props.userLocation, (newLoc) => {
    if (newLoc && map.value) {
        if (!newLoc.isApprox) {
            // Only flyTo and set real location when it's an external update
            realUserLocation.value = { lat: newLoc.lat, lng: newLoc.lng };
            map.value.flyTo({
                center: [newLoc.lng, newLoc.lat],
                essential: true,
                zoom: 13
            });
        }
        updateUserMarker();
    }
}, { deep: true, immediate: true });

watch(() => props.threads, () => {
    updateThreadMarkers();
}, { deep: true });

watch(() => props.theme, (newTheme) => {
    if (map.value) {
        const style = newTheme === 'light' 
            ? 'mapbox://styles/mapbox/streets-v12' 
            : 'mapbox://styles/mapbox/dark-v11';
        map.value.setStyle(style);
        
        // Markers need small time to reload after style changes
        map.value.once('style.load', () => {
            updateUserMarker();
            updateThreadMarkers();
        });
    }
});

onMounted(() => {
    initMap();
});

onUnmounted(() => {
    if (map.value) {
        map.value.remove();
    }
});
</script>

<template>
    <div class="relative w-full h-[450px] md:h-[600px] rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-800 shadow-2xl">
        <div ref="mapContainer" class="w-full h-full"></div>
        
        <!-- Lokasi Terkini Button -->
        <button 
            @click="detectCurrentLocation"
            :disabled="detectingLocation"
            class="absolute top-[76px] right-[10px] p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-slate-700 dark:text-slate-350 shadow-md hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors z-10 cursor-pointer flex items-center justify-center disabled:opacity-50"
            title="Lokasi Terkini"
        >
            <svg v-if="!detectingLocation" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v3m0 14v3M2 12h3m14 0h3M12 8a4 4 0 100 8 4 4 0 000-8z" />
            </svg>
            <span v-else class="w-4 h-4 border-2 border-slate-700 dark:border-slate-300 border-t-transparent rounded-full animate-spin"></span>
        </button>

        <!-- Legend Overlay -->
        <div class="absolute bottom-4 left-4 bg-white/95 dark:bg-slate-950/85 backdrop-blur-md px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-[10px] font-bold text-slate-700 dark:text-slate-350 space-y-1.5 shadow-lg">
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600 dark:bg-[#ccff00] inline-block shadow-sm"></span>
                <span>Open / Tersedia</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shadow-sm"></span>
                <span>Full / Penuh</span>
            </div>
        </div>
    </div>
</template>

<style>
/* Custom Marker elements (must be global) */
.custom-run-marker {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #0f172a;
    border: 2px solid #ccff00;
    color: #ffffff;
    padding: 3px 6px 3px 3px;
    border-radius: 9999px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: ui-sans-serif, system-ui, sans-serif;
    white-space: nowrap;
}

.custom-run-marker:hover {
    transform: scale(1.1);
    z-index: 9999;
}

.marker-avatar {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.marker-content {
    display: flex;
    flex-direction: column;
    line-height: 1;
}

.marker-distance {
    font-size: 10px;
    font-weight: 900;
}

.marker-type {
    font-size: 7px;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 700;
    margin-top: 1px;
}

/* Light theme overrides */
.light .custom-run-marker {
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.light .marker-type {
    color: #64748b;
}

.light .user-pulse-marker {
    background-color: #c11e09;
    box-shadow: 0 0 10px #c11e09;
}

.light .user-pulse-marker::after {
    border-color: #c11e09;
}

/* User marker pulsing */
.user-pulse-marker {
    width: 18px;
    height: 18px;
    background-color: #3b82f6;
    border: 3px solid #ffffff;
    border-radius: 50%;
    box-shadow: 0 0 10px #3b82f6;
    cursor: pointer;
    position: relative;
}

.user-pulse-marker::after {
    content: '';
    position: absolute;
    width: 36px;
    height: 36px;
    border: 2px solid #3b82f6;
    border-radius: 50%;
    top: -12px;
    left: -12px;
    animation: markerPulse 1.8s infinite ease-out;
    opacity: 0;
}

@keyframes markerPulse {
    0% {
        transform: scale(0.5);
        opacity: 0.8;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}
</style>
