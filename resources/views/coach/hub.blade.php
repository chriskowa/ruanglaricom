@extends('layouts.pacerhub')

@section('title', 'Coach Command Hub')

@push('styles')
<style>
    .slide-enter-active, .slide-leave-active { transition: transform 0.25s ease; }
    .slide-enter-from, .slide-leave-to { transform: translateX(100%); }
</style>
@endpush

@section('content')
<div id="coach-hub-app" class="h-full flex flex-col min-h-screen pt-20 font-sans">
    
    <!-- Top Bar -->
    <header class="bg-slate-900 border border-slate-800 flex items-center justify-between px-6 py-3.5 z-20 mx-4 rounded-lg mb-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-md bg-slate-800 border border-slate-700 flex items-center justify-center font-bold text-white text-xs">
                CH
            </div>
            <div>
                <h1 class="font-bold text-sm text-white">Coach Command Hub</h1>
                <p class="text-[11px] text-slate-400">Roster & Performance Overview</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative hidden md:block">
                <input v-model="searchQuery" type="text" placeholder="Cari nama runner..." class="bg-slate-950 border border-slate-800 rounded-md py-1.5 px-3 text-xs text-white placeholder-slate-400 outline-none w-56 focus:border-slate-600">
            </div>

            <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-slate-300">
                CO
            </div>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden px-4 pb-4 gap-4">
        
        <!-- Sidebar Overview -->
        <aside class="w-60 bg-slate-900 border border-slate-800 rounded-lg hidden lg:flex flex-col p-5 space-y-5">
            <div>
                <h3 class="text-xs font-semibold text-white mb-3">Ringkasan Tim</h3>
                <div class="space-y-3">
                    <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-md">
                        <span class="text-slate-400 text-xs">Total Atlet</span>
                        <div class="text-xl font-bold text-white mt-0.5">@{{ athletes.length }}</div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-md">
                        <span class="text-slate-400 text-xs">Program Aktif</span>
                        <div class="text-xl font-bold text-white mt-0.5">@{{ activeProgramsCount }}</div>
                    </div>
                    <div class="bg-slate-950 border border-slate-800 p-3.5 rounded-md">
                        <span class="text-slate-400 text-xs">Permintaan Baru</span>
                        <div class="text-xl font-bold text-white mt-0.5">3</div>
                    </div>
                </div>
            </div>

            <div class="mt-auto pt-4 border-t border-slate-800">
                <a href="{{ route('coach.dashboard') }}" class="w-full py-2 px-3 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-medium flex items-center justify-center transition">
                    &larr; Dashboard Coach
                </a>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="flex-1 overflow-y-auto rounded-lg border border-slate-800 bg-slate-900 p-6 relative">
            
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4 border-b border-slate-800 pb-5">
                <div>
                    <h2 class="text-lg font-bold text-white">Roster Atlet</h2>
                    <p class="text-slate-400 text-xs">Manajemen program berdasarkan level performa lari.</p>
                </div>

                <div v-if="connectionError" class="bg-amber-950 border border-amber-800 text-amber-300 px-3 py-1.5 rounded-md text-xs">
                    <span class="font-bold">Mode Offline:</span> @{{ connectionError }}
                </div>
                
                <div class="bg-slate-950 p-1 rounded-md border border-slate-800 flex gap-1 text-xs">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="selectedCategory = cat.id"
                        class="px-3 py-1.5 rounded-md font-medium transition"
                        :class="selectedCategory === cat.id ? 'bg-slate-800 text-white font-semibold' : 'text-slate-400 hover:text-white'">
                        @{{ cat.name }}
                        <span class="ml-1 text-[11px] text-slate-400">(@{{ getCountByCategory(cat.id) }})</span>
                    </button>
                </div>
            </div>

            <div v-if="filteredAthletes.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                
                <div v-for="athlete in filteredAthletes" :key="athlete.id" 
                        class="bg-slate-950 border border-slate-800 rounded-lg p-4 hover:border-slate-700 transition cursor-pointer flex flex-col justify-between space-y-3"
                        @click="openDetail(athlete)">
                    
                    <div>
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-xs font-bold text-white shrink-0">
                                    @{{ getInitials(athlete.name) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-white text-xs truncate">@{{ athlete.name }}</h3>
                                    <span class="text-[11px] text-slate-400 font-mono">@{{ athlete.gender }}, @{{ athlete.age }} th</span>
                                </div>
                            </div>
                            <span class="text-[10px] uppercase font-medium px-1.5 py-0.5 rounded border shrink-0" 
                                    :class="getCategoryBadgeClass(athlete)">
                                @{{ getCategoryLabel(athlete) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-slate-900 p-2 rounded border border-slate-800">
                                <span class="block text-[10px] text-slate-400">Best @{{ athlete.latestDistance }}</span>
                                <span class="block font-mono font-semibold text-white mt-0.5">@{{ athlete.latestTime }}</span>
                            </div>
                            <div class="bg-slate-900 p-2 rounded border border-slate-800">
                                <span class="block text-[10px] text-slate-400">Volume/Wk</span>
                                <span class="block font-mono font-semibold text-white mt-0.5">@{{ athlete.weeklyVolume }} KM</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2.5 border-t border-slate-800 text-xs">
                        <span class="text-slate-400 text-[11px]">Minggu @{{ athlete.currentWeek || 1 }}</span>
                        <span class="text-neon font-medium hover:underline text-[11px]">Detail &rarr;</span>
                    </div>
                </div>

            </div>

            <div v-else class="flex flex-col items-center justify-center py-16 text-slate-400 text-xs">
                <p>Tidak ada atlet di kategori ini.</p>
            </div>
        </main>
    </div>

    <!-- Detail Drawer -->
    <transition name="slide">
        <div v-if="selectedAthlete" class="fixed inset-0 z-50 flex justify-end">
            <div @click="selectedAthlete = null" class="absolute inset-0 bg-black/70 transition-opacity"></div>
            
            <div class="relative w-full max-w-md bg-slate-900 h-full border-l border-slate-800 shadow-2xl overflow-y-auto">
                
                <div class="p-5 border-b border-slate-800 flex justify-between items-start sticky top-0 bg-slate-900 z-10">
                    <div>
                        <h2 class="text-base font-bold text-white">@{{ selectedAthlete.name }}</h2>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">@{{ getCategoryLabel(selectedAthlete) }} Runner</p>
                    </div>
                    <button @click="selectedAthlete = null" class="text-slate-400 hover:text-white p-1 text-base">
                        &times;
                    </button>
                </div>

                <div class="p-5 space-y-5 text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <button class="bg-neon text-dark py-2 px-3 rounded-md font-bold text-xs hover:brightness-110 transition">
                            Download PDF
                        </button>
                        <button class="border border-slate-700 bg-slate-800 hover:bg-slate-700 text-white py-2 px-3 rounded-md font-medium text-xs transition">
                            WhatsApp
                        </button>
                    </div>

                    <div class="bg-slate-950 p-4 rounded-md border border-slate-800 space-y-2">
                        <h3 class="text-[11px] font-semibold text-slate-300">Analisis Target</h3>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Saat Ini</span>
                            <span class="text-white font-mono font-semibold">@{{ selectedAthlete.latestTime }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400">Target Impian</span>
                            <span class="text-white font-semibold">@{{ selectedAthlete.goalDescription }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-800 text-[11px]">
                            <span class="text-slate-400">Rekomendasi Coach:</span>
                            <p class="text-emerald-300 mt-1">Fokus pada Base Building 4 minggu. Targetkan sub @{{ calculateMicroTarget(selectedAthlete.latestTime) }} terlebih dahulu.</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-semibold text-white mb-2">Program Minggu Ini</h3>
                        <div class="space-y-1.5">
                            <div v-for="(day, i) in ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']" :key="i" class="flex items-center p-2.5 rounded-md bg-slate-950 border border-slate-800">
                                <div class="w-16 text-[11px] text-slate-400 font-mono uppercase">@{{ day }}</div>
                                <div class="flex-1">
                                    <div class="text-xs text-white font-medium">Easy Run 5KM</div>
                                    <div class="text-[11px] text-neon font-mono">Pace: 6:30/km</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </transition>

</div>
@endsection

@push('scripts')
<script type="module">
    const { createApp, ref, computed, onMounted } = Vue;
    
    import { initializeApp } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";
    import { getFirestore, collection, getDocs, query, orderBy } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore.js";

    const firebaseConfig = {
        apiKey: "AIzaSyBVAEiYBFSt2ZYMIxbl7q-5kZvLH_dRLKU",
        authDomain: "ruanglari-8d041.firebaseapp.com",
        databaseURL: "https://ruanglari-8d041-default-rtdb.asia-southeast1.firebasedatabase.app",
        projectId: "ruanglari-8d041",
        storageBucket: "ruanglari-8d041.firebasestorage.app",
        messagingSenderId: "887605981752",
        appId: "1:887605981752:web:42d420fcddd861ba21eccd",
        measurementId: "G-9TXDKSXRR8"
    };
    
    let db;
    try {
        const app = initializeApp(firebaseConfig);
        db = getFirestore(app);
    } catch(e) { 
        console.error("Firebase Init Error:", e);
    }

    createApp({
        setup() {
            const searchQuery = ref('');
            const selectedCategory = ref('all');
            const selectedAthlete = ref(null);
            const athletes = ref([]);
            const isLoading = ref(true);
            const connectionError = ref(null);

            const mockAthletes = [
                { id: 1, name: "Andi Saputra", gender: "Pria", age: 29, latestDistance: "10k", latestTime: "34:15", weeklyVolume: 60, status: 'active', goalDescription: "Sub 33" },
                { id: 2, name: "Budi Santoso", gender: "Pria", age: 35, latestDistance: "10k", latestTime: "39:00", weeklyVolume: 45, status: 'active', goalDescription: "Sub 38" },
                { id: 3, name: "Citra Lestari", gender: "Wanita", age: 26, latestDistance: "5k", latestTime: "28:30", weeklyVolume: 20, status: 'new', goalDescription: "Finish Strong 10K" },
                { id: 4, name: "Doni Pratama", gender: "Pria", age: 40, latestDistance: "10k", latestTime: "52:10", weeklyVolume: 30, status: 'active', goalDescription: "Sub 45" },
                { id: 5, name: "Eka Wijaya", gender: "Pria", age: 31, latestDistance: "10k", latestTime: "48:00", weeklyVolume: 35, status: 'active', goalDescription: "Sub 45" },
                { id: 6, name: "Fani Rose", gender: "Wanita", age: 28, latestDistance: "10k", latestTime: "01:05:00", weeklyVolume: 15, status: 'new', goalDescription: "Bisa lari 1 jam nonstop" },
            ];

            const categories = [
                { id: 'all', name: 'Semua' },
                { id: 'elite', name: 'Elite' },
                { id: 'fast', name: 'Fast' },
                { id: 'intermediate', name: 'Intermediate' },
                { id: 'beginner', name: 'Beginner' },
            ];

            const determineCategory = (athlete) => {
                const timeParts = athlete.latestTime.split(':').map(Number);
                let totalMinutes = 0;
                if (timeParts.length === 3) totalMinutes = timeParts[0] * 60 + timeParts[1];
                else totalMinutes = timeParts[0];
                
                if (athlete.latestDistance === '5k') totalMinutes *= 2.1;
                
                if (totalMinutes < 38) return 'elite';
                if (totalMinutes < 48) return 'fast';
                if (totalMinutes < 60) return 'intermediate';
                return 'beginner';
            };

            onMounted(async () => {
                if (db) {
                    try {
                        const querySnapshot = await getDocs(collection(db, "program_assessments"));
                        const dbData = [];
                        querySnapshot.forEach((doc) => {
                            const data = doc.data();
                            dbData.push({ 
                                id: doc.id,
                                name: data.name || 'Unknown',
                                gender: data.gender || '-',
                                age: data.age || 0,
                                latestDistance: data.latestDistance || '10k',
                                latestTime: `${data.timeMin || 0}:${data.timeSec || 0}`,
                                weeklyVolume: data.weeklyVolume || 0,
                                status: 'new',
                                goalDescription: data.goalDescription || '-'
                            });
                        });

                        if (dbData.length > 0) {
                            athletes.value = dbData;
                        } else {
                            athletes.value = mockAthletes;
                        }
                    } catch (e) { 
                        connectionError.value = e.message;
                        athletes.value = mockAthletes; 
                    }
                } else {
                    athletes.value = mockAthletes;
                }

                athletes.value.forEach(a => {
                    a.category = determineCategory(a);
                });
                
                isLoading.value = false;
            });

            const filteredAthletes = computed(() => {
                return athletes.value.filter(a => {
                    const catMatch = selectedCategory.value === 'all' || a.category === selectedCategory.value;
                    const searchMatch = a.name.toLowerCase().includes(searchQuery.value.toLowerCase());
                    return catMatch && searchMatch;
                });
            });

            const activeProgramsCount = computed(() => athletes.value.filter(a => a.status === 'active').length);

            const getCountByCategory = (catId) => {
                if (catId === 'all') return athletes.value.length;
                return athletes.value.filter(a => a.category === catId).length;
            };

            const getInitials = (name) => {
                return name.split(' ').map(n => n[0]).join('').substring(0,2).toUpperCase();
            };

            const getCategoryLabel = (athlete) => {
                const cat = categories.find(c => c.id === athlete.category);
                return cat ? cat.name : 'Unknown';
            };
            
            const getCategoryBadgeClass = (athlete) => {
                switch(athlete.category) {
                    case 'elite': return 'border-purple-800 text-purple-300 bg-purple-950';
                    case 'fast': return 'border-sky-800 text-sky-300 bg-sky-950';
                    case 'intermediate': return 'border-amber-800 text-amber-300 bg-amber-950';
                    case 'beginner': return 'border-emerald-800 text-emerald-300 bg-emerald-950';
                    default: return 'border-slate-700 text-slate-300 bg-slate-800';
                }
            };

            const calculateMicroTarget = (timeStr) => {
                const parts = timeStr.split(':');
                return parseInt(parts[0]) - 1; 
            };

            const openDetail = (athlete) => {
                selectedAthlete.value = athlete;
            };

            return {
                searchQuery, selectedCategory, categories,
                filteredAthletes, athletes, activeProgramsCount,
                selectedAthlete,
                getInitials, getCategoryLabel, getCategoryBadgeClass, getCountByCategory,
                openDetail, calculateMicroTarget
            }
        }
    }).mount('#coach-hub-app')
</script>
@endpush