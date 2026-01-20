<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';

const activeTab = ref('magicMile');
const globalUnit = ref('metric');

const tabs = [
    { id: 'magicMile', name: '🎯 Magic Mile' },
    { id: 'marathon', name: '🏃‍♀️ Marathon' },
    { id: 'pace', name: '⏱️ Pace' },
    { id: 'predictor', name: '🔮 Predictor' },
    { id: 'improvement', name: '📈 Improvement' },
    { id: 'splits', name: '📊 Splits' },
    { id: 'steps', name: '👣 Steps' },
    { id: 'stride', name: '📏 Stride' },
    { id: 'training', name: '🎯 Training' },
    { id: 'vo2max', name: '💨 VO2 Max' },
    { id: 'heartrate', name: '❤️ Heart Rate' },
];

// Helpers
const timeToSeconds = (h, m, s) => (parseInt(h) || 0) * 3600 + (parseInt(m) || 0) * 60 + (parseInt(s) || 0);
const formatTime = (totalSeconds, includeHours = true) => {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = Math.floor(totalSeconds % 60);
    
    if (includeHours && hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    } else {
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }
};

// Data & Logic for Magic Mile
const magicMile = reactive({ h: '', m: '', s: '', results: null, error: null });
const calculateMagicMile = () => {
    const totalSec = timeToSeconds(magicMile.h, magicMile.m, magicMile.s);
    if (!totalSec) { magicMile.error = "Please enter a valid time"; return; }
    magicMile.error = null;
    
    const mileToKm = 1.60934;
    const basePace = totalSec / (globalUnit.value === 'metric' ? mileToKm : 1);
    
    magicMile.results = [
        { label: '5K', time: basePace * (globalUnit.value === 'metric' ? 5 : 3.1) * 1.05 },
        { label: '10K', time: basePace * (globalUnit.value === 'metric' ? 10 : 6.2) * 1.08 },
        { label: 'Half Marathon', time: basePace * (globalUnit.value === 'metric' ? 21.1 : 13.1) * 1.15 },
        { label: 'Marathon', time: basePace * (globalUnit.value === 'metric' ? 42.2 : 26.2) * 1.2 },
    ];
};

// Marathon Pace
const marathon = reactive({ h: '', m: '', s: '', results: null, error: null });
const calculateMarathon = () => {
    const totalSec = timeToSeconds(marathon.h, marathon.m, marathon.s);
    if (!totalSec) { marathon.error = "Please enter a valid time"; return; }
    marathon.error = null;
    
    const dist = globalUnit.value === 'metric' ? 42.195 : 26.2;
    const pace = totalSec / dist;
    const speed = dist / (totalSec / 3600);
    
    marathon.results = {
        pace: formatTime(pace, false) + (globalUnit.value === 'metric' ? ' min/km' : ' min/mile'),
        speed: speed.toFixed(2) + (globalUnit.value === 'metric' ? ' km/h' : ' mph')
    };
};

// Pace Calculator
const pace = reactive({ dist: '', unit: 'km', h: '', m: '', s: '', results: null, error: null });
const calculatePace = () => {
    if (!pace.dist) { pace.error = "Please enter distance"; return; }
    const totalSec = timeToSeconds(pace.h, pace.m, pace.s);
    if (!totalSec) { pace.error = "Please enter time"; return; }
    pace.error = null;
    
    let distKm = parseFloat(pace.dist);
    if (pace.unit === 'meter') distKm /= 1000;
    
    const distUsed = globalUnit.value === 'imperial' ? distKm * 0.621371 : distKm;
    const paceVal = totalSec / distUsed;
    const speed = distUsed / (totalSec / 3600);
    
    pace.results = {
        pace: formatTime(paceVal, false) + (globalUnit.value === 'metric' ? ' min/km' : ' min/mile'),
        speed: speed.toFixed(2) + (globalUnit.value === 'metric' ? ' km/h' : ' mph')
    };
};

// Predictor
const predictor = reactive({ recentDist: '', targetDist: '', h: '', m: '', s: '', results: null, error: null });
const calculatePredictor = () => {
    if (!predictor.recentDist || !predictor.targetDist) { predictor.error = "Enter distances"; return; }
    const totalSec = timeToSeconds(predictor.h, predictor.m, predictor.s);
    if (!totalSec) { predictor.error = "Enter time"; return; }
    predictor.error = null;
    
    const rDist = parseFloat(predictor.recentDist);
    const tDist = parseFloat(predictor.targetDist);
    const predSec = totalSec * Math.pow(tDist / rDist, 1.06);
    
    predictor.results = {
        time: formatTime(predSec, true),
        pace: formatTime(predSec / tDist, false) + '/km' // Simplified
    };
};

// Improvement
const improvement = reactive({ dist: '', curH: '', curM: '', curS: '', tarH: '', tarM: '', tarS: '', results: null, error: null });
const calculateImprovement = () => {
    const curSec = timeToSeconds(improvement.curH, improvement.curM, improvement.curS);
    const tarSec = timeToSeconds(improvement.tarH, improvement.tarM, improvement.tarS);
    if (!improvement.dist || !curSec || !tarSec) { improvement.error = "Fill all fields"; return; }
    improvement.error = null;
    
    const imp = curSec - tarSec;
    const pct = (imp / curSec * 100).toFixed(2);
    improvement.results = { time: formatTime(imp, true), percent: pct + '%' };
};

// Heart Rate
const hr = reactive({ age: '', rest: '', gender: 'male', fitness: 'intermediate', results: null, error: null });
const calculateHR = () => {
    if (!hr.age) { hr.error = "Enter Age"; return; }
    hr.error = null;
    
    const maxHR = 220 - hr.age; // Simple formula for now
    const restHR = hr.rest ? parseInt(hr.rest) : 60;
    const hrr = maxHR - restHR;
    
    const zones = [
        { name: 'Z1 Recovery', range: `${Math.round(restHR + hrr*0.5)}-${Math.round(restHR + hrr*0.6)}` },
        { name: 'Z2 Easy', range: `${Math.round(restHR + hrr*0.6)}-${Math.round(restHR + hrr*0.7)}` },
        { name: 'Z3 Moderate', range: `${Math.round(restHR + hrr*0.7)}-${Math.round(restHR + hrr*0.8)}` },
        { name: 'Z4 Threshold', range: `${Math.round(restHR + hrr*0.8)}-${Math.round(restHR + hrr*0.9)}` },
        { name: 'Z5 Max', range: `${Math.round(restHR + hrr*0.9)}-${Math.round(restHR + hrr*1.0)}` },
    ];
    hr.results = { maxHR, zones };
};

// VO2 Max
const vo2 = reactive({ dist: '', h: '', m: '', s: '', results: null });
const calculateVO2 = () => {
    const sec = timeToSeconds(vo2.h, vo2.m, vo2.s);
    if (!vo2.dist || !sec) return;
    
    const vel = (parseFloat(vo2.dist) * 1000) / (sec / 60);
    const val = -4.6 + 0.182258 * vel + 0.000104 * Math.pow(vel, 2);
    vo2.results = val.toFixed(1);
};

// Training Pace (Simplified VDOT)
const training = reactive({ dist: '5', h: '', m: '', s: '', results: null });
const calculateTraining = () => {
    const sec = timeToSeconds(training.h, training.m, training.s);
    if (!sec) return;
    
    // Very simplified VDOT approximation
    const vdot = calculateVO2Internal(parseFloat(training.dist), sec); 
    
    training.results = [
        { type: 'Easy', pace: getPaceFromVDOT(vdot, 0.70) },
        { type: 'Marathon', pace: getPaceFromVDOT(vdot, 0.82) },
        { type: 'Threshold', pace: getPaceFromVDOT(vdot, 0.88) },
        { type: 'Interval', pace: getPaceFromVDOT(vdot, 0.95) },
        { type: 'Repetition', pace: getPaceFromVDOT(vdot, 1.05) },
    ];
};

function calculateVO2Internal(dist, sec) {
    const vel = (dist * 1000) / (sec / 60);
    return -4.6 + 0.182258 * vel + 0.000104 * Math.pow(vel, 2);
}

function getPaceFromVDOT(vdot, intensity) {
    // Reverse VDOT approx (simplified)
    // This is complex, using a placeholder logic for speed
    // Ideally need full VDOT tables or formula
    // Using simple pace multipliers for demo
    // Better: just use the VDOT value to display
    return vdot.toFixed(1); // Placeholder
}
</script>

<template>
    <Head title="Running Calculator" />
    <div class="min-h-screen bg-gray-50 font-sans text-gray-800 pb-12">
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-8 px-4 shadow-lg">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-3xl md:text-4xl font-bold mb-2">Ruang Lari Calculator</h1>
                <p class="text-indigo-100">Kalkulator Lari Lengkap untuk Performa Terbaik Anda</p>
                <div class="mt-4 inline-flex bg-white/10 rounded-lg p-1">
                    <button @click="globalUnit = 'metric'" :class="{'bg-white text-indigo-600': globalUnit === 'metric', 'text-white hover:bg-white/5': globalUnit !== 'metric'}" class="px-4 py-1 rounded-md text-sm font-medium transition-colors">Metric (km)</button>
                    <button @click="globalUnit = 'imperial'" :class="{'bg-white text-indigo-600': globalUnit === 'imperial', 'text-white hover:bg-white/5': globalUnit !== 'imperial'}" class="px-4 py-1 rounded-md text-sm font-medium transition-colors">Imperial (mi)</button>
                </div>
            </div>
        </div>

        <div class="max-w-4xl mx-auto px-4 mt-8">
            <!-- Navigation -->
            <div class="flex overflow-x-auto gap-2 pb-4 mb-4 scrollbar-hide">
                <button v-for="tab in tabs" :key="tab.id" 
                    @click="activeTab = tab.id"
                    :class="{'bg-indigo-600 text-white shadow-md transform scale-105': activeTab === tab.id, 'bg-white text-gray-600 hover:bg-gray-50': activeTab !== tab.id}"
                    class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all whitespace-nowrap border border-gray-100">
                    {{ tab.name }}
                </button>
            </div>

            <!-- Content Area -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 md:p-8 min-h-[400px]">
                
                <!-- Magic Mile -->
                <div v-if="activeTab === 'magicMile'">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        🎯 Magic Mile Calculator
                    </h2>
                    <p class="text-gray-500 mb-6 text-sm bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                        Prediksi waktu race berdasarkan waktu lari 1 mile (1.6 km). Tes ini sangat akurat untuk menentukan benchmark.
                    </p>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Magic Mile</label>
                            <div class="flex gap-2">
                                <input v-model="magicMile.h" type="number" placeholder="Jam" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all" />
                                <input v-model="magicMile.m" type="number" placeholder="Menit" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all" />
                                <input v-model="magicMile.s" type="number" placeholder="Detik" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none transition-all" />
                            </div>
                            <button @click="calculateMagicMile" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                                Hitung Prediksi
                            </button>
                            <p v-if="magicMile.error" class="mt-2 text-red-500 text-sm">{{ magicMile.error }}</p>
                        </div>
                        <div v-if="magicMile.results" class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                            <h3 class="font-bold text-gray-800 mb-4">Hasil Prediksi Race</h3>
                            <div class="space-y-3">
                                <div v-for="res in magicMile.results" :key="res.label" class="flex justify-between items-center bg-white p-3 rounded-lg shadow-sm">
                                    <span class="text-gray-600 font-medium">{{ res.label }}</span>
                                    <span class="text-indigo-600 font-bold font-mono">{{ formatTime(res.time, true) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Marathon Pace -->
                <div v-if="activeTab === 'marathon'">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">🏃‍♀️ Marathon Pace Calculator</h2>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Target Waktu Marathon</label>
                            <div class="flex gap-2">
                                <input v-model="marathon.h" type="number" placeholder="Jam" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                                <input v-model="marathon.m" type="number" placeholder="Menit" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                                <input v-model="marathon.s" type="number" placeholder="Detik" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                            </div>
                            <button @click="calculateMarathon" class="mt-4 w-full bg-indigo-600 text-white font-bold py-3 rounded-xl transition-all shadow-lg">Hitung Pace</button>
                        </div>
                        <div v-if="marathon.results" class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                            <div class="text-center mb-4">
                                <p class="text-sm text-gray-500 uppercase tracking-wide">Required Pace</p>
                                <p class="text-3xl font-bold text-indigo-600">{{ marathon.results.pace }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-500 uppercase tracking-wide">Average Speed</p>
                                <p class="text-xl font-bold text-gray-800">{{ marathon.results.speed }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pace Calculator -->
                <div v-if="activeTab === 'pace'">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">⏱️ Pace Calculator</h2>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jarak</label>
                                <div class="flex gap-2">
                                    <input v-model="pace.dist" type="number" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="e.g. 10" />
                                    <select v-model="pace.unit" class="bg-gray-50 border border-gray-200 rounded-lg px-3 outline-none">
                                        <option value="km">km</option>
                                        <option value="meter">m</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Waktu</label>
                                <div class="flex gap-2">
                                    <input v-model="pace.h" type="number" placeholder="H" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                                    <input v-model="pace.m" type="number" placeholder="M" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                                    <input v-model="pace.s" type="number" placeholder="S" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                                </div>
                            </div>
                            <button @click="calculatePace" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg">Hitung</button>
                        </div>
                        <div v-if="pace.results" class="flex flex-col justify-center items-center bg-gray-50 rounded-xl p-6 border border-gray-100">
                            <div class="text-center mb-6">
                                <p class="text-sm text-gray-500 uppercase">Pace</p>
                                <p class="text-4xl font-bold text-indigo-600">{{ pace.results.pace }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-500 uppercase">Speed</p>
                                <p class="text-2xl font-bold text-gray-800">{{ pace.results.speed }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Heart Rate -->
                <div v-if="activeTab === 'heartrate'">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">❤️ Heart Rate Zones</h2>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Usia</label>
                                <input v-model="hr.age" type="number" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Resting HR (Optional)</label>
                                <input v-model="hr.rest" type="number" placeholder="60" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none" />
                            </div>
                            <button @click="calculateHR" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg">Hitung Zona</button>
                        </div>
                        <div v-if="hr.results" class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                            <div class="mb-4 text-center">
                                <span class="text-sm text-gray-500">Max HR Est.</span>
                                <span class="block text-2xl font-bold text-red-500">{{ hr.results.maxHR }} bpm</span>
                            </div>
                            <div class="space-y-2">
                                <div v-for="zone in hr.results.zones" :key="zone.name" class="flex justify-between items-center bg-white p-3 rounded-lg border border-gray-100">
                                    <span class="font-medium text-gray-700">{{ zone.name }}</span>
                                    <span class="font-bold text-indigo-600">{{ zone.range }} bpm</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Placeholder for other tabs -->
                <div v-if="!['magicMile', 'marathon', 'pace', 'heartrate'].includes(activeTab)" class="text-center py-12">
                    <p class="text-gray-500">Fitur {{ tabs.find(t => t.id === activeTab).name }} akan segera hadir dalam versi Vue.</p>
                </div>

            </div>
            
            <div class="mt-8 text-center text-gray-400 text-sm">
                &copy; Ruang Lari Tools - Developed with ❤️ for Runners
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar hide for nav */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
