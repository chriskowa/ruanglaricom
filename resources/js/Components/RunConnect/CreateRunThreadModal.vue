<script setup>
import { ref, reactive, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';

const props = defineProps({
    isOpen: {
        type: Boolean,
        required: true
    },
    userLocation: {
        type: Object,
        default: null
    },
    theme: {
        type: String,
        default: 'dark'
    },
    mapboxToken: {
        type: String,
        default: ''
    },
    editThread: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'created', 'updated']);

const step = ref(1);
const loading = ref(false);
const errors = ref({});

const initialForm = {
    title: '',
    description: '',
    type: 'Casual Run',
    run_distance_km: 5,
    pace_min: '6:00',
    pace_max: '7:00',
    start_date: new Date().toISOString().substring(0, 10),
    start_time: '06:00',
    start_location_name: '',
    start_latitude: '',
    start_longitude: '',
    route_url: '',
    quota: 10,
    visibility: 'public',
    is_beginner_friendly: false,
    is_women_friendly: false,
    notes: ''
};

const form = reactive({ ...initialForm });

const runTypes = [
    'Casual Run',
    'Long Run',
    'Speed Session',
    'Recovery Run',
    'Race Prep',
    'Community Run'
];

const paces = ['4:00', '4:30', '5:00', '5:30', '6:00', '6:30', '7:00', '7:30', '8:00', 'Bebas'];

const resetForm = () => {
    if (props.editThread) {
        Object.keys(initialForm).forEach(key => {
            if (props.editThread[key] !== undefined) {
                form[key] = props.editThread[key];
            }
        });
        if (props.editThread.start_date) {
            // format start_date to YYYY-MM-DD
            const d = new Date(props.editThread.start_date);
            form.start_date = d.toISOString().substring(0, 10);
        }
    } else {
        Object.assign(form, initialForm);
        if (props.userLocation) {
            form.start_latitude = props.userLocation.lat;
            form.start_longitude = props.userLocation.lng;
        }
    }
    step.value = 1;
    errors.value = {};
};

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        resetForm();
    }
});

const miniMapContainer = ref(null);
const miniMap = ref(null);
const miniMarker = ref(null);

watch(step, (newStep) => {
    if (newStep === 2) {
        nextTick(() => {
            initMiniMap();
        });
    } else {
        if (miniMap.value) {
            miniMap.value.remove();
            miniMap.value = null;
            miniMarker.value = null;
        }
    }
});

const initMiniMap = () => {
    if (!miniMapContainer.value) return;

    const token = props.mapboxToken || '';
    mapboxgl.accessToken = token;

    const style = props.theme === 'light' 
        ? 'mapbox://styles/mapbox/streets-v12' 
        : 'mapbox://styles/mapbox/dark-v11';

    const initialLat = parseFloat(form.start_latitude) || (props.userLocation ? props.userLocation.lat : -6.1754);
    const initialLng = parseFloat(form.start_longitude) || (props.userLocation ? props.userLocation.lng : 106.8272);

    miniMap.value = new mapboxgl.Map({
        container: miniMapContainer.value,
        style: style,
        center: [initialLng, initialLat],
        zoom: 14
    });

    miniMap.value.addControl(new mapboxgl.NavigationControl(), 'top-right');

    miniMarker.value = new mapboxgl.Marker({
        draggable: true,
        color: '#2563eb'
    })
    .setLngLat([initialLng, initialLat])
    .addTo(miniMap.value);

    // Set initial form values if they were empty
    if (!form.start_latitude) form.start_latitude = parseFloat(initialLat.toFixed(6));
    if (!form.start_longitude) form.start_longitude = parseFloat(initialLng.toFixed(6));

    miniMarker.value.on('dragend', () => {
        const lngLat = miniMarker.value.getLngLat();
        form.start_latitude = parseFloat(lngLat.lat.toFixed(6));
        form.start_longitude = parseFloat(lngLat.lng.toFixed(6));
    });

    miniMap.value.on('click', (e) => {
        const lngLat = e.lngLat;
        miniMarker.value.setLngLat([lngLat.lng, lngLat.lat]);
        form.start_latitude = parseFloat(lngLat.lat.toFixed(6));
        form.start_longitude = parseFloat(lngLat.lng.toFixed(6));
    });

    setTimeout(() => {
        if (miniMap.value) {
            miniMap.value.resize();
        }
    }, 150);
};

const updateMarkerFromInputs = () => {
    const lat = parseFloat(form.start_latitude);
    const lng = parseFloat(form.start_longitude);
    if (!isNaN(lat) && !isNaN(lng) && miniMarker.value && miniMap.value) {
        miniMarker.value.setLngLat([lng, lat]);
        miniMap.value.setCenter([lng, lat]);
    }
};

const useCurrentLocation = () => {
    if (props.userLocation) {
        form.start_latitude = parseFloat(props.userLocation.lat.toFixed(6));
        form.start_longitude = parseFloat(props.userLocation.lng.toFixed(6));
        form.start_location_name = form.start_location_name || 'Lokasi Terdekat Anda';
        
        if (miniMap.value && miniMarker.value) {
            miniMap.value.flyTo({
                center: [props.userLocation.lng, props.userLocation.lat],
                zoom: 15
            });
            miniMarker.value.setLngLat([props.userLocation.lng, props.userLocation.lat]);
        }
    } else {
        alert("Lokasi Anda tidak terdeteksi. Silakan aktifkan izin lokasi browser.");
    }
};

const nextStep = () => {
    errors.value = {};
    if (step.value === 1) {
        if (!form.title) { errors.value.title = "Judul lari wajib diisi."; return; }
        if (form.title.length > 100) { errors.value.title = "Judul maksimal 100 karakter."; return; }
    } else if (step.value === 2) {
        if (!form.start_location_name) { errors.value.start_location_name = "Nama lokasi start wajib diisi."; return; }
        if (!form.start_latitude || !form.start_longitude) { errors.value.coordinates = "Koordinat latitude & longitude wajib terisi."; return; }
        if (!form.start_date) { errors.value.start_date = "Tanggal start wajib diisi."; return; }
        if (!form.start_time) { errors.value.start_time = "Jam start wajib diisi."; return; }
    } else if (step.value === 3) {
        if (!form.run_distance_km || form.run_distance_km <= 0) { errors.value.run_distance_km = "Jarak lari minimal 0.5 km."; return; }
        if (!form.quota || form.quota < 2) { errors.value.quota = "Kuota minimal 2 peserta."; return; }
    }
    step.value++;
};

const prevStep = () => {
    step.value--;
};

const submitForm = async () => {
    loading.value = true;
    errors.value = {};

    try {
        const payload = {
            ...form,
            is_beginner_friendly: form.is_beginner_friendly ? 1 : 0,
            is_women_friendly: form.is_women_friendly ? 1 : 0,
        };

        let res;
        if (props.editThread) {
            res = await axios.put(`/api/run-connect/threads/${props.editThread.id}`, payload);
            emit('updated', res.data.thread);
        } else {
            res = await axios.post('/api/run-connect/threads', payload);
            emit('created', res.data.thread);
        }
        resetForm();
        emit('close');
    } catch (err) {
        if (err.response && err.response.data && err.response.data.errors) {
            errors.value = err.response.data.errors;
            if (errors.value.title || errors.value.description || errors.value.type) {
                step.value = 1;
            } else if (errors.value.start_location_name || errors.value.start_latitude || errors.value.start_longitude || errors.value.start_date || errors.value.start_time) {
                step.value = 2;
            } else {
                step.value = 3;
            }
        } else {
            alert(err.response?.data?.message || 'Gagal menyimpan running thread. Silakan coba lagi.');
        }
    } finally {
        loading.value = false;
    }
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
        v-if="isOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
    >
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col w-full max-w-lg max-h-[90vh]">
            
            <!-- Modal Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white italic uppercase tracking-wider">Create Run Thread</h3>
                    <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase tracking-widest mt-0.5">Step {{ step }} of 4</p>
                </div>
                <button 
                    @click="$emit('close')" 
                    class="text-slate-400 hover:text-slate-800 dark:hover:text-white p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Steps Progress Bar -->
            <div class="w-full bg-slate-100 dark:bg-slate-950 h-1">
                <div 
                    :class="theme === 'light' ? 'bg-blue-600' : 'bg-[#ccff00]'"
                    class="h-full transition-all duration-300"
                    :style="{ width: `${(step / 4) * 100}%` }"
                ></div>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-6 overflow-y-auto flex-grow space-y-4 text-slate-800 dark:text-slate-100">
                
                <!-- STEP 1: Detail Run -->
                <div v-if="step === 1" class="space-y-4">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Detail Acara Lari</h4>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2">Nama Running Thread *</label>
                        <input 
                            v-model="form.title" 
                            type="text" 
                            placeholder="Contoh: Morning Run GBK Slow Pace"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        />
                        <p v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2">Deskripsi Singkat (Opsional)</label>
                        <textarea 
                            v-model="form.description" 
                            rows="3"
                            placeholder="Jelaskan detail rute, meeting point spesifik, atau info penting lainnya..."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase mb-2">Tipe Running Thread *</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button 
                                v-for="type in runTypes" 
                                :key="type"
                                @click="form.type = type"
                                type="button"
                                :class="form.type === type 
                                    ? (theme === 'light' ? 'border-blue-600 bg-blue-50 text-blue-600 font-bold' : 'border-[#ccff00] bg-[#ccff00]/5 text-[#ccff00] font-black') 
                                    : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-650 dark:text-slate-300'"
                                class="p-2.5 rounded-xl border text-xs text-center transition-all cursor-pointer font-semibold"
                            >
                                {{ type }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Lokasi & Waktu -->
                <div v-if="step === 2" class="space-y-4">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Waktu & Titik Kumpul</h4>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-550 uppercase mb-2">Tanggal *</label>
                            <input 
                                v-model="form.start_date" 
                                type="date"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                            />
                            <p v-if="errors.start_date" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.start_date) ? errors.start_date[0] : errors.start_date }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase mb-2">Jam Mulai *</label>
                            <input 
                                v-model="form.start_time" 
                                type="time"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                            />
                            <p v-if="errors.start_time" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.start_time) ? errors.start_time[0] : errors.start_time }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase mb-2">Nama Tempat Berkumpul *</label>
                        <input 
                            v-model="form.start_location_name" 
                            type="text" 
                            placeholder="Contoh: Depan Starbucks Plaza Senayan"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        />
                        <p v-if="errors.start_location_name" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.start_location_name) ? errors.start_location_name[0] : errors.start_location_name }}</p>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase">Koordinat Lokasi Start *</label>
                            <button 
                                type="button"
                                @click="useCurrentLocation"
                                class="text-xs text-blue-600 dark:text-[#ccff00] hover:underline cursor-pointer"
                            >
                                Gunakan Lokasi Saat Ini
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-3 mb-2">
                            <input 
                                v-model="form.start_latitude" 
                                @input="updateMarkerFromInputs"
                                type="number" 
                                step="any"
                                placeholder="Latitude"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-xs text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                            />
                            <input 
                                v-model="form.start_longitude" 
                                @input="updateMarkerFromInputs"
                                type="number" 
                                step="any"
                                placeholder="Longitude"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-xs text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                            />
                        </div>
                        
                        <!-- Map Selection -->
                        <div ref="miniMapContainer" class="w-full h-48 rounded-xl overflow-hidden mt-3 border border-slate-200 dark:border-slate-800"></div>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Geser pin biru atau klik di peta untuk memilih lokasi secara presisi.</p>

                        <p v-if="errors.coordinates" class="text-xs text-red-500 mt-1">{{ errors.coordinates }}</p>
                        <p v-if="errors.start_latitude" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.start_latitude) ? errors.start_latitude[0] : errors.start_latitude }}</p>
                        <p v-if="errors.start_longitude" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.start_longitude) ? errors.start_longitude[0] : errors.start_longitude }}</p>
                    </div>
                </div>

                <!-- STEP 3: Parameter Lari -->
                <div v-if="step === 3" class="space-y-4">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Parameter & Kriteria Lari</h4>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-550 uppercase mb-2">Jarak Lari (KM) *</label>
                            <input 
                                v-model="form.run_distance_km" 
                                type="number" 
                                step="0.1" 
                                min="0.5"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                            />
                            <p v-if="errors.run_distance_km" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.run_distance_km) ? errors.run_distance_km[0] : errors.run_distance_km }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase mb-2">Kuota Peserta *</label>
                            <input 
                                v-model="form.quota" 
                                type="number" 
                                min="2" 
                                max="100"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                            />
                            <p v-if="errors.quota" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.quota) ? errors.quota[0] : errors.quota }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase mb-2">Pace Minimal *</label>
                            <select 
                                v-model="form.pace_min"
                                class="w-full bg-slate-55 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                            >
                                <option v-for="p in paces" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <p v-if="errors.pace_min" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.pace_min) ? errors.pace_min[0] : errors.pace_min }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 dark:text-slate-555 uppercase mb-2">Pace Maksimal *</label>
                            <select 
                                v-model="form.pace_max"
                                class="w-full bg-slate-55 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                            >
                                <option v-for="p in paces" :key="p" :value="p">{{ p }}</option>
                            </select>
                            <p v-if="errors.pace_max" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.pace_max) ? errors.pace_max[0] : errors.pace_max }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-550 uppercase mb-2">Preferensi Lingkungan</label>
                        <div class="space-y-2 bg-slate-50 dark:bg-slate-950 p-4 rounded-xl border border-slate-200 dark:border-slate-850">
                            <label class="flex items-center gap-3 cursor-pointer text-xs text-slate-750 dark:text-slate-300 font-semibold">
                                <input 
                                    v-model="form.is_beginner_friendly" 
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-blue-600 dark:text-[#ccff00] bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 accent-blue-600 dark:accent-[#ccff00]"
                                />
                                <span>Ramah Pemula</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer text-xs text-slate-750 dark:text-slate-300 font-semibold">
                                <input 
                                    v-model="form.is_women_friendly" 
                                    type="checkbox"
                                    class="w-4 h-4 rounded text-blue-600 dark:text-[#ccff00] bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 accent-blue-600 dark:accent-[#ccff00]"
                                />
                                <span>Ramah Pelari Wanita (Women Friendly)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-550 uppercase mb-2">Visibility *</label>
                        <div class="flex gap-2">
                            <button 
                                type="button"
                                @click="form.visibility = 'public'"
                                :class="form.visibility === 'public' 
                                    ? 'bg-blue-600 dark:bg-[#ccff00] text-white dark:text-slate-950 font-black' 
                                    : 'bg-slate-50 dark:bg-slate-950 text-slate-650 dark:text-slate-400 border border-slate-200 dark:border-slate-800'"
                                class="flex-grow p-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
                            >
                                Public
                            </button>
                            <button 
                                type="button"
                                @click="form.visibility = 'community'"
                                :class="form.visibility === 'community' 
                                    ? 'bg-blue-600 dark:bg-[#ccff00] text-white dark:text-slate-950 font-black' 
                                    : 'bg-slate-50 dark:bg-slate-950 text-slate-650 dark:text-slate-400 border border-slate-200 dark:border-slate-800'"
                                class="flex-grow p-2.5 rounded-xl text-xs font-bold transition-all cursor-pointer"
                            >
                                Community Only
                            </button>
                        </div>
                        <p v-if="errors.visibility" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.visibility) ? errors.visibility[0] : errors.visibility }}</p>
                    </div>
                </div>

                <!-- STEP 4: Rute & Preview -->
                <div v-if="step === 4" class="space-y-4">
                    <h4 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-2">Catatan Rute & Publikasi</h4>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-550 uppercase mb-2">Tautan Rute (Opsional)</label>
                        <input 
                            v-model="form.route_url" 
                            type="url" 
                            placeholder="Tautan Strava Route, Komoot, atau Google Maps"
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        />
                        <p v-if="errors.route_url" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.route_url) ? errors.route_url[0] : errors.route_url }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-400 dark:text-slate-550 uppercase mb-2">Catatan Tambahan</label>
                        <input 
                            v-model="form.notes" 
                            type="text" 
                            placeholder="Contoh: Bawa botol hidrasi sendiri."
                            class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-200 outline-none focus:ring-1 focus:ring-blue-600 dark:focus:ring-[#ccff00]"
                        />
                        <p v-if="errors.notes" class="text-xs text-red-500 mt-1">{{ Array.isArray(errors.notes) ? errors.notes[0] : errors.notes }}</p>
                    </div>

                    <!-- Thread Preview Card -->
                    <div class="border border-slate-200 dark:border-slate-800/80 rounded-2xl bg-slate-50 dark:bg-slate-950/60 p-4">
                        <h5 class="text-[10px] font-bold text-slate-400 dark:text-slate-555 uppercase tracking-widest mb-3">Preview Thread Lari</h5>
                        
                        <div class="bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                            <div class="flex justify-between items-center">
                                <span class="px-2 py-0.5 rounded bg-blue-600/10 dark:bg-[#ccff00]/10 text-blue-650 dark:text-[#ccff00] border border-blue-500/20 dark:border-[#ccff00]/25 text-[9px] font-black uppercase tracking-wider">
                                    {{ form.type }}
                                </span>
                                <span class="text-xs font-mono font-bold text-slate-450 dark:text-slate-400">
                                    Target: {{ form.run_distance_km }} KM
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-white">{{ form.title || 'Judul Thread' }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-450 flex items-center gap-1">
                                <span>Meeting Point:</span>
                                <span class="font-bold text-slate-700 dark:text-white truncate max-w-[200px]">{{ form.start_location_name || 'Belum diisi' }}</span>
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center bg-white dark:bg-slate-900 mt-auto">
                <button 
                    v-if="step > 1" 
                    type="button" 
                    @click="prevStep"
                    class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-xl border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer text-xs"
                >
                    Kembali
                </button>
                <div v-else></div>

                <div>
                    <button 
                        v-if="step < 4" 
                        type="button" 
                        @click="nextStep"
                        class="px-5 py-2.5 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 font-black rounded-xl shadow-sm transition-colors cursor-pointer text-xs"
                    >
                        Lanjutkan &rarr;
                    </button>
                    
                    <button 
                        v-else 
                        type="button" 
                        @click="submitForm"
                        :disabled="loading"
                        class="px-6 py-2.5 bg-blue-600 dark:bg-[#ccff00] hover:bg-blue-700 dark:hover:bg-white text-white dark:text-slate-950 font-black rounded-xl shadow-md transition-all cursor-pointer text-xs flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <span v-if="loading" class="w-3.5 h-3.5 border-2 border-white dark:border-slate-950 border-t-transparent rounded-full animate-spin"></span>
                        Publish Run Thread
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>
