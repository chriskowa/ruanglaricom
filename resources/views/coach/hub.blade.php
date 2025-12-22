@extends('layouts.pacerhub')

@section('title', 'Coach Command Center - Realistic Values')

@push('styles')
    <script>
        // Extending existing Tailwind config from pacerhub layout
        tailwind.config.theme.extend = {
            ...tailwind.config.theme.extend,
            colors: {
                ...tailwind.config.theme.extend.colors,
                neon: {
                    cyan: '#06b6d4',
                    purple: '#a855f7',
                    green: '#22c55e',
                    yellow: '#eab308',
                    dark: '#0f172a',
                    card: '#1e293b'
                }
            },
            boxShadow: {
                ...tailwind.config.theme.extend.boxShadow,
                'neon-cyan': '0 0 5px rgba(6, 182, 212, 0.5), 0 0 15px rgba(6, 182, 212, 0.2)',
                'neon-purple': '0 0 5px rgba(168, 85, 247, 0.5), 0 0 15px rgba(168, 85, 247, 0.2)',
            }
        }
    </script>
    <style>
        .glass {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .glass-active {
            background: rgba(6, 182, 212, 0.15);
            border: 1px solid rgba(6, 182, 212, 0.5);
            box-shadow: 0 0 15px rgba(6, 182, 212, 0.1);
        }
        .slide-enter-active, .slide-leave-active { transition: transform 0.3s ease; }
        .slide-enter-from, .slide-leave-to { transform: translateX(100%); }
    </style>
@endpush

@section('content')
<div id="coach-hub-app" class="h-full flex flex-col min-h-screen pt-20">
    
    <header class="h-16 glass border-b border-slate-700 flex items-center justify-between px-6 z-20 mx-4 rounded-xl mb-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded bg-gradient-to-br from-cyan-500 to-purple-600 flex items-center justify-center font-bold text-white">
                RV
            </div>
            <h1 class="font-bold text-lg tracking-tight text-white">
                COACH <span class="text-cyan-400 font-mono text-sm">OS v1.0</span>
            </h1>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="relative hidden md:block">
                <input v-model="searchQuery" type="text" placeholder="Cari nama atlet..." class="bg-slate-800 border border-slate-600 rounded-full py-1.5 px-4 text-sm text-white focus:outline-none focus:border-cyan-400 w-64 transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute right-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <button class="relative p-2 text-slate-400 hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
            </button>
            
            <div class="w-8 h-8 rounded-full bg-slate-700 border border-slate-500 overflow-hidden cursor-pointer">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Coach" alt="Coach Profile">
            </div>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden px-4 pb-4 gap-4">
        
        <aside class="w-64 bg-slate-900 border border-slate-700 rounded-xl hidden lg:flex flex-col p-6 space-y-6">
            <div>
                <h3 class="text-xs font-mono text-slate-500 uppercase mb-4">Overview</h3>
                <div class="grid grid-cols-1 gap-4">
                    <div class="glass p-4 rounded-lg border-l-2 border-cyan-500">
                        <span class="text-slate-400 text-xs">Total Atlet</span>
                        <div class="text-2xl font-bold text-white">@{{ athletes.length }}</div>
                    </div>
                    <div class="glass p-4 rounded-lg border-l-2 border-green-500">
                        <span class="text-slate-400 text-xs">Program Aktif</span>
                        <div class="text-2xl font-bold text-white">@{{ activeProgramsCount }}</div>
                    </div>
                        <div class="glass p-4 rounded-lg border-l-2 border-purple-500">
                        <span class="text-slate-400 text-xs">New Requests</span>
                        <div class="text-2xl font-bold text-white">3</div>
                    </div>
                </div>
            </div>

            <div class="mt-auto">
                    <button class="w-full py-3 rounded border border-slate-700 hover:bg-slate-800 text-slate-400 text-sm flex items-center justify-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Logout
                </button>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto rounded-xl border border-slate-700 bg-slate-900/50 p-6 relative">
            
            <div class="flex flex-wrap items-center justify-between mb-8 gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-white">Roster Atlet</h2>
                    <p class="text-slate-400 text-sm">Manajemen program berdasarkan level performa.</p>
                </div>

                <div v-if="connectionError" class="bg-red-500/10 border border-red-500 text-red-400 px-4 py-2 rounded text-sm max-w-md">
                    <span class="font-bold">Mode Demo (Offline):</span> @{{ connectionError }}
                </div>
                
                <div class="bg-slate-800 p-1 rounded-lg flex shadow-lg">
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id"
                        @click="selectedCategory = cat.id"
                        class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-300 flex items-center gap-2"
                        :class="selectedCategory === cat.id ? 'bg-slate-700 text-white shadow-md ' + cat.color : 'text-slate-400 hover:text-slate-200'">
                        <span class="w-2 h-2 rounded-full" :class="cat.dotColor"></span>
                        @{{ cat.name }}
                        <span class="ml-1 text-xs opacity-50 bg-slate-900 px-1.5 rounded-full">@{{ getCountByCategory(cat.id) }}</span>
                    </button>
                </div>
            </div>

            <div v-if="filteredAthletes.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                
                <div v-for="athlete in filteredAthletes" :key="athlete.id" 
                        class="glass rounded-xl p-5 hover:border-slate-500 transition-all cursor-pointer group relative overflow-hidden"
                        @click="openDetail(athlete)">
                    
                    <div class="absolute left-0 top-0 bottom-0 w-1" :class="getCategoryColor(athlete)"></div>

                    <div class="flex justify-between items-start mb-4 pl-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-sm font-bold text-white border border-slate-600">
                                @{{ getInitials(athlete.name) }}
                            </div>
                            <div>
                                <h3 class="font-bold text-white group-hover:text-cyan-400 transition-colors">@{{ athlete.name }}</h3>
                                <span class="text-xs text-slate-500 font-mono">@{{ athlete.gender }}, @{{ athlete.age }}th</span>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded border" 
                                :class="getCategoryBadgeClass(athlete)">
                            @{{ getCategoryLabel(athlete) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-4 pl-3">
                        <div class="bg-slate-800/50 p-2 rounded border border-slate-700/50">
                            <span class="block text-[10px] text-slate-400 uppercase tracking-wider">Best @{{ athlete.latestDistance }}</span>
                            <span class="block text-sm font-mono font-bold text-white">@{{ athlete.latestTime }}</span>
                        </div>
                        <div class="bg-slate-800/50 p-2 rounded border border-slate-700/50">
                            <span class="block text-[10px] text-slate-400 uppercase tracking-wider">Volume/Wk</span>
                            <span class="block text-sm font-mono font-bold text-white">@{{ athlete.weeklyVolume }} KM</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pl-3 pt-2 border-t border-slate-700/50">
                        <div class="flex -space-x-2">
                            <span v-if="athlete.status === 'new'" class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse" title="Program Baru"></span>
                            <span v-else class="w-2 h-2 rounded-full bg-green-500" title="Aktif"></span>
                            <span class="text-xs text-slate-500 ml-4">Minggu @{{ athlete.currentWeek || 1 }}</span>
                        </div>
                        <span class="text-xs text-cyan-400 font-semibold group-hover:underline">Lihat Program →</span>
                    </div>
                </div>

            </div>

            <div v-else class="flex flex-col items-center justify-center h-64 text-slate-500">
                <svg class="w-12 h-12 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <p>Tidak ada atlet di kategori ini.</p>
            </div>
        </main>
    </div>

    <transition name="slide">
        <div v-if="selectedAthlete" class="fixed inset-0 z-50 flex justify-end">
            <div @click="selectedAthlete = null" class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
            
            <div class="relative w-full max-w-md bg-slate-900 h-full border-l border-slate-700 shadow-2xl overflow-y-auto">
                
                <div class="p-6 border-b border-slate-800 flex justify-between items-start sticky top-0 bg-slate-900/95 backdrop-blur z-10">
                    <div>
                        <h2 class="text-2xl font-bold text-white">@{{ selectedAthlete.name }}</h2>
                        <p class="text-sm text-cyan-400 font-mono">@{{ getCategoryLabel(selectedAthlete) }} Athlete</p>
                    </div>
                    <button @click="selectedAthlete = null" class="text-slate-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 space-y-8">
                    <div class="grid grid-cols-2 gap-3">
                        <button class="flex items-center justify-center gap-2 bg-cyan-600 hover:bg-cyan-500 text-white py-2 rounded text-sm font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Download PDF
                        </button>
                        <button class="flex items-center justify-center gap-2 border border-slate-600 hover:bg-slate-800 text-white py-2 rounded text-sm font-bold transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            WhatsApp
                        </button>
                    </div>

                    <div class="glass p-5 rounded-lg border border-slate-700">
                        <h3 class="text-xs font-mono text-slate-500 uppercase mb-3">Gap Analysis</h3>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-400 text-sm">Realita Saat Ini</span>
                            <span class="text-white font-bold">@{{ selectedAthlete.latestTime }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-slate-400 text-sm">Target Impian</span>
                            <span class="text-white font-bold">@{{ selectedAthlete.goalDescription }}</span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-700">
                            <span class="text-xs text-slate-500">Rekomendasi Coach:</span>
                            <p class="text-sm text-green-400 mt-1">Fokus pada Base Building 4 minggu. Targetkan sub @{{ calculateMicroTarget(selectedAthlete.latestTime) }} dulu.</p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-white mb-4">Program Minggu Ini</h3>
                        <div class="space-y-3">
                            <div v-for="(day, i) in ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']" :key="i" class="flex items-center p-3 rounded bg-slate-800/50 border border-slate-700/50">
                                <div class="w-16 text-xs text-slate-500 font-mono uppercase">@{{ day }}</div>
                                <div class="flex-1">
                                    <div class="text-sm text-white font-medium">Easy Run 5KM</div>
                                    <div class="text-xs text-cyan-400">Pace: 6:30/km</div>
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
    // Using Vue from global scope (loaded in pacerhub layout)
    const { createApp, ref, computed, onMounted } = Vue;
    
    // Import Firebase from CDN just like in the original file (if needed for DB connection)
    // Note: In a real Laravel app, we might want to bundle these or use a different approach, 
    // but here we keep consistency with the provided HTML file's approach
    import { initializeApp } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-app.js";
    import { getFirestore, collection, getDocs, query, orderBy } from "https://www.gstatic.com/firebasejs/9.23.0/firebase-firestore.js";

    // CONFIG FIREBASE (Ganti dengan punya Anda)
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
    
    // Init (Safe check)
    let db;
    try {
        const app = initializeApp(firebaseConfig);
        db = getFirestore(app);
    } catch(e) { 
        console.error("Firebase Init Error:", e);
        console.log("Using Mock Data due to init failure");
    }

    createApp({
        setup() {
            const searchQuery = ref('');
            const selectedCategory = ref('all');
            const selectedAthlete = ref(null);
            const athletes = ref([]);
            const isLoading = ref(true);
            const connectionError = ref(null);

            // MOCK DATA (Fallback)
            const mockAthletes = [
                { id: 1, name: "Andi Saputra", gender: "Pria", age: 29, latestDistance: "10k", latestTime: "34:15", weeklyVolume: 60, status: 'active', goalDescription: "Sub 33" },
                { id: 2, name: "Budi Santoso", gender: "Pria", age: 35, latestDistance: "10k", latestTime: "39:00", weeklyVolume: 45, status: 'active', goalDescription: "Sub 38" },
                { id: 3, name: "Citra Lestari", gender: "Wanita", age: 26, latestDistance: "5k", latestTime: "28:30", weeklyVolume: 20, status: 'new', goalDescription: "Finish Strong 10K" },
                { id: 4, name: "Doni Pratama", gender: "Pria", age: 40, latestDistance: "10k", latestTime: "52:10", weeklyVolume: 30, status: 'active', goalDescription: "Sub 45" },
                { id: 5, name: "Eka Wijaya", gender: "Pria", age: 31, latestDistance: "10k", latestTime: "48:00", weeklyVolume: 35, status: 'active', goalDescription: "Sub 45" },
                { id: 6, name: "Fani Rose", gender: "Wanita", age: 28, latestDistance: "10k", latestTime: "01:05:00", weeklyVolume: 15, status: 'new', goalDescription: "Bisa lari 1 jam nonstop" },
            ];

            // CATEGORY DEFINITIONS
            const categories = [
                { id: 'all', name: 'Semua', color: '', dotColor: 'bg-slate-400' },
                { id: 'elite', name: 'Elite', color: 'border-purple-500/50 text-purple-400', dotColor: 'bg-purple-500' },
                { id: 'fast', name: 'Fast', color: 'border-cyan-500/50 text-cyan-400', dotColor: 'bg-cyan-500' },
                { id: 'intermediate', name: 'Intermediate', color: 'border-yellow-500/50 text-yellow-400', dotColor: 'bg-yellow-500' },
                { id: 'beginner', name: 'Beginner', color: 'border-green-500/50 text-green-400', dotColor: 'bg-green-500' },
            ];

            // LOGIC: Kategori berdasarkan Waktu (Estimasi 10K)
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

            // Populate data awal
            onMounted(async () => {
                if (db) {
                    try {
                        console.log("Fetching data from Firebase...");
                        const querySnapshot = await getDocs(collection(db, "program_assessments"));
                        
                        const dbData = [];
                        querySnapshot.forEach((doc) => {
                            // Normalize data structure from DB to match UI expectation
                            const data = doc.data();
                            // Handle potential missing fields with defaults
                            dbData.push({ 
                                id: doc.id,
                                name: data.name || 'Unknown',
                                gender: data.gender || '-',
                                age: data.age || 0,
                                latestDistance: data.latestDistance || '10k',
                                latestTime: `${data.timeMin || 0}:${data.timeSec || 0}`, // Construct time string
                                weeklyVolume: data.weeklyVolume || 0,
                                status: 'new', // Default to new since it's from assessment
                                goalDescription: data.goalDescription || '-'
                            });
                        });

                        console.log("Data fetched:", dbData);

                        if (dbData.length > 0) {
                            athletes.value = dbData;
                        } else {
                            console.log("No documents found in 'program_assessments'. Using mock data.");
                            athletes.value = mockAthletes;
                        }
                    } catch (e) { 
                        console.error("Error fetching documents: ", e);
                        connectionError.value = "Gagal terhubung ke Database: " + e.message;
                        athletes.value = mockAthletes; 
                    }
                } else {
                    console.log("DB not initialized. Using mock data.");
                    athletes.value = mockAthletes;
                }

                // Assign kategori ke setiap atlet
                athletes.value.forEach(a => {
                    a.category = determineCategory(a);
                });
                
                isLoading.value = false;
            });

            // Computed: Filter Logic
            const filteredAthletes = computed(() => {
                return athletes.value.filter(a => {
                    // Filter by Category
                    const catMatch = selectedCategory.value === 'all' || a.category === selectedCategory.value;
                    // Filter by Search
                    const searchMatch = a.name.toLowerCase().includes(searchQuery.value.toLowerCase());
                    return catMatch && searchMatch;
                });
            });

            const activeProgramsCount = computed(() => athletes.value.filter(a => a.status === 'active').length);

            // Helper Functions untuk UI
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

            const getCategoryColor = (athlete) => {
                switch(athlete.category) {
                    case 'elite': return 'bg-purple-500';
                    case 'fast': return 'bg-cyan-500';
                    case 'intermediate': return 'bg-yellow-500';
                    case 'beginner': return 'bg-green-500';
                    default: return 'bg-slate-500';
                }
            };
            
            const getCategoryBadgeClass = (athlete) => {
                switch(athlete.category) {
                    case 'elite': return 'border-purple-500 text-purple-400 bg-purple-900/20';
                    case 'fast': return 'border-cyan-500 text-cyan-400 bg-cyan-900/20';
                    case 'intermediate': return 'border-yellow-500 text-yellow-400 bg-yellow-900/20';
                    case 'beginner': return 'border-green-500 text-green-400 bg-green-900/20';
                    default: return 'border-slate-500 text-slate-400';
                }
            };

            const calculateMicroTarget = (timeStr) => {
                // Logic sederhana: kurangi 1 menit dari waktu saat ini
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
                getInitials, getCategoryLabel, getCategoryColor, getCategoryBadgeClass, getCountByCategory,
                openDetail, calculateMicroTarget
            }
        }
    }).mount('#coach-hub-app')
</script>
@endpush