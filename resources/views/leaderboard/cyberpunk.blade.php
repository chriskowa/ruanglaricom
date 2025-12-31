@extends('layouts.pacerhub')
@php($withSidebar = true)
@section('title', 'Cyberpunk Leaderboard')
@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    cyber: {
                        black: '#050505',
                        dark: '#0a0a0a',
                        panel: '#111111',
                        green: '#39ff14',
                        cyan: '#00f3ff',
                        dim: '#333333'
                    }
                },
                fontFamily: {
                    'orbitron': ['"Orbitron"', 'sans-serif'],
                    'mono': ['"Share Tech Mono"', 'monospace'],
                },
                boxShadow: {
                    'neon-green': '0 0 5px #39ff14, 0 0 10px #39ff14',
                    'neon-cyan': '0 0 5px #00f3ff, 0 0 10px #00f3ff',
                },
                animation: {
                    'pulse-fast': 'pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    'scanline': 'scanline 8s linear infinite',
                },
                keyframes: {
                    scanline: {
                        '0%': { transform: 'translateY(-100%)' },
                        '100%': { transform: 'translateY(100%)' }
                    }
                }
            }
        }
    }
</script>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Share+Tech+Mono&display=swap" rel="stylesheet">
<style>
    body { 
        background-color: #050505; 
        color: #e0e0e0;
        font-family: 'Share Tech Mono', monospace;
        background-image: 
            linear-gradient(rgba(0, 255, 65, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 255, 65, 0.03) 1px, transparent 1px);
        background-size: 30px 30px;
    }
    ::-webkit-scrollbar { width: 8px; height: 8px; }
    ::-webkit-scrollbar-track { background: #111; }
    ::-webkit-scrollbar-thumb { background: #333; border: 1px solid #39ff14; }
    ::-webkit-scrollbar-thumb:hover { background: #39ff14; }
    .clip-corner {
        clip-path: polygon(0 0, 100% 0, 100% calc(100% - 15px), calc(100% - 15px) 100%, 0 100%);
    }
</style>
@endpush
@section('content')
<div id="app" class="min-h-screen py-8 px-2 sm:px-6 relative overflow-hidden mt-[100px]">
    
    <div class="fixed inset-0 pointer-events-none z-0 opacity-10 bg-gradient-to-b from-transparent via-cyber-green to-transparent h-screen w-full animate-scanline"></div>

    <div class="relative z-10 max-w-6xl mx-auto mb-10 text-center uppercase">
        <div class="inline-block border border-cyber-green text-cyber-green px-4 py-1 text-xs tracking-[0.2em] mb-4 shadow-neon-green bg-black">
            System Status: Online
        </div>
        <h1 class="text-4xl md:text-6xl font-black font-orbitron tracking-tighter text-white mb-2" style="text-shadow: 0 0 20px rgba(57, 255, 20, 0.5);">
            RUANGLARI <span class="text-cyber-green">LEADERBOARD</span>
        </h1>
        <p class="text-cyber-cyan text-sm tracking-widest font-mono opacity-80">
            >> INITIATE SEQUENCE: 40 DAYS ENDURANCE TEST <<
        </p>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto">
        
        <div class="flex flex-col sm:flex-row justify-center gap-4 mb-8">
            <button 
                @click="activeTab = 'discipline'"
                :class="activeTab === 'discipline' ? 'bg-cyber-green text-black shadow-neon-green border-cyber-green' : 'bg-transparent text-gray-500 border-gray-700 hover:text-cyber-green hover:border-cyber-green'"
                class="px-8 py-3 border-2 font-orbitron font-bold tracking-wider transition-all duration-300 transform skew-x-[-10deg] uppercase">
                <span class="block transform skew-x-[10deg]">1. Consistency Data</span>
            </button>
            <button 
                @click="activeTab = 'performance'"
                :class="activeTab === 'performance' ? 'bg-cyber-cyan text-black shadow-neon-cyan border-cyber-cyan' : 'bg-transparent text-gray-500 border-gray-700 hover:text-cyber-cyan hover:border-cyber-cyan'"
                class="px-8 py-3 border-2 font-orbitron font-bold tracking-wider transition-all duration-300 transform skew-x-[-10deg] uppercase">
                <span class="block transform skew-x-[10deg]">2. Final Showdown</span>
            </button>
            <button 
                @click="activeTab = 'club_athletes'"
                :class="activeTab === 'club_athletes' ? 'bg-cyber-cyan text-black shadow-neon-cyan border-cyber-cyan' : 'bg-transparent text-gray-500 border-gray-700 hover:text-cyber-cyan hover:border-cyber-cyan'"
                class="px-8 py-3 border-2 font-orbitron font-bold tracking-wider transition-all duration-300 transform skew-x-[-10deg] uppercase">
                <span class="block transform skew-x-[10deg]">3. Club Athletes</span>
            </button>
        </div>

        <div class="bg-cyber-panel border border-gray-800 relative clip-corner">
            <div class="absolute top-0 left-0 w-2 h-2 bg-cyber-green"></div>
            <div class="absolute top-0 right-0 w-2 h-2 bg-cyber-green"></div>
            <div class="absolute bottom-0 left-0 w-2 h-2 bg-cyber-green"></div>
            
            <div class="p-4 border-b border-gray-800 flex flex-col sm:flex-row justify_between items-center gap-4 bg-black/40">
                <div class="flex items-center gap-2 text-xs text-cyber-green">
                    <span class="animate-pulse-fast">●</span> 
                    SYNC: @{{ lastUpdated }}
                </div>
                
                <div class="flex gap-2 w-full sm:w-auto">
                    <select v-model="filterGender" class="bg_black border border-cyber-green text-cyber-green text-sm p-2 w-full focus:outline-none focus:shadow-neon-green font-mono uppercase">
                        <option value="all">>> ALL UNITS</option>
                        <option value="M">>> MALE CLASS</option>
                        <option value="F">>> FEMALE CLASS</option>
                    </select>
                    <button @click="fetchData" class="bg-black border border-cyber-green text-cyber-green p-2 hover:bg-cyber-green hover:text-black transition-colors">
                        [REFRESH]
                    </button>
                </div>
            </div>

            <div class="relative min-h-[400px]">
                
                <div v-if="loading" class="absolute inset-0 flex flex-col items-center justify-center bg-black/90 z-20">
                    <div class="text-4xl font-orbitron text-cyber-green animate-pulse">LOADING...</div>
                    <div class="text-xs text-cyber-cyan mt-2">DECRYPTING STRAVA PACKETS</div>
                </div>

                <transition name="fade">
                    <div v-if="activeTab === 'discipline'" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-cyber-dim border-b border-gray-800 font-orbitron text-sm">
                                    <th class="p-4 text-center w-20">RK</th>
                                    <th class="p-4">OPERATIVE</th>
                                    <th class="p-4 w-5/12">SYNC PROGRESS</th>
                                    <th class="p-4 text-center">RATE</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800 font-mono">
                                <tr v-for="(runner, index) in filteredDiscipline" :key="runner.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="p-4 text-center font-bold text-xl font-orbitron">
                                        <span v-if="index === 0" class="text-yellow-400 drop-shadow-[0_0_5px_rgba(250,204,21,0.8)]">1</span>
                                        <span v-else-if="index === 1" class="text-gray-300">2</span>
                                        <span v-else-if="index === 2" class="text-orange-600">3</span>
                                        <span v-else class="text-gray-600">0@{{ index + 1 }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-4">
                                            <div class="relative">
                                                <img :src="runner.avatar" class="w-10 h-10 grayscale group-hover:grayscale-0 transition-all border border-cyber-green p-0.5">
                                            </div>
                                            <div>
                                                <div class="font-bold text-white group-hover:text-cyber-green tracking-wide uppercase">@{{ runner.name }}</div>
                                                <div v-if="runner.qualified" class="text-[10px] text-cyber-green border border-cyber-green px-1 inline-block mt-1">
                                                    QUALIFIED
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex justify-between text-[10px] mb-1 text-cyber-cyan">
                                            <span>STREAK: @{{ runner.streak }}</span>
                                            <span>TARGET: 40</span>
                                        </div>
                                        <div class="w-full bg-gray-900 border border-gray-700 h-2 relative">
                                            <div class="h-full bg-cyber-green shadow-neon-green relative transition-all duration-1000" 
                                                 :style="{ width: runner.percentage + '%' }">
                                            </div>
                                        </div>
                                        <div class="text-right text-[10px] text-gray-500 mt-1">@{{ runner.active_days }}/40 COMPLETED</div>
                                    </td>
                                    <td class="p-4 text-center font-bold text-cyber-green text-lg font-orbitron">
                                        @{{ runner.percentage }}<span class="text-xs text-gray-500">%</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </transition>

                <transition name="fade">
                    <div v-if="activeTab === 'performance'" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-cyber-dim border-b border-gray-800 font-orbitron text-sm">
                                    <th class="p-4 text-center w-20">RK</th>
                                    <th class="p-4">OPERATIVE</th>
                                    <th class="p-4 text-center hidden sm:table-cell">BASELINE</th>
                                    <th class="p-4 text-center">FINAL</th>
                                    <th class="p-4 text-right">DELTA (GAP)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800 font-mono">
                                <tr v-for="(runner, index) in filteredPerformance" :key="runner.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="p-4 text-center font-bold text-xl font-orbitron text-cyber-cyan">
                                        0@{{ index + 1 }}
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-4">
                                            <img :src="runner.avatar" class="w-10 h-10 grayscale group-hover:grayscale-0 border border-cyber-cyan p-0.5">
                                            <div>
                                                <div class="font-bold text-white group-hover:text-cyber-cyan uppercase">@{{ runner.name }}</div>
                                                <div class="text-[10px] text-gray-500">PACE: @{{ runner.pace }}/KM</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center text-gray-600 hidden sm:table_cell line-through decoration-red-500">
                                        @{{ runner.old_pb }}
                                    </td>
                                    <td class="p-4 text_center">
                                        <span class="font-orbitron text-xl text-white border-b-2 border-cyber-cyan pb-1">
                                            @{{ runner.new_pb }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <div class="inline_block bg-cyber-green/10 border border-cyber-green text-cyber-green px-3 py-1 font-bold shadow-neon-green">
                                            @{{ runner.gap }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </transition>

                <transition name="fade">
                    <div v-if="activeTab === 'club_athletes'" class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-cyber-dim border-b border-gray-800 font-orbitron text-sm">
                                    <th class="p-4 text-center w-20">RK</th>
                                    <th class="p-4">ATHLETE</th>
                                    <th class="p-4 text-center hidden sm:table-cell">GENDER</th>
                                    <th class="p-4">LOCATION</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800 font-mono">
                                <tr v-for="(member, index) in filteredClubMembers" :key="member.id" class="hover:bg-white/5 transition-colors group">
                                    <td class="p-4 text-center font-bold text-xl font-orbitron text-cyber-cyan">0@{{ index + 1 }}</td>
                                    <td class="p-4">
                                        <div class="flex items-center gap-4">
                                            <img :src="member.avatar" class="w-10 h-10 grayscale group-hover:grayscale-0 border border-cyber-cyan p-0.5">
                                            <div>
                                                <div class="font-bold text-white group-hover:text-cyber-cyan uppercase">@{{ member.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center hidden sm:table-cell">@{{ member.gender }}</td>
                                    <td class="p-4">@{{ member.city || member.state || member.country || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </transition>
                
                <div v-if="(activeTab === 'discipline' && filteredDiscipline.length === 0) || (activeTab === 'performance' && filteredPerformance.length === 0) || (activeTab === 'club_athletes' && filteredClubMembers.length === 0)" class="text-center py-20 border border-dashed border-gray-800 m-4">
                    <p class="text-gray-600 font-orbitron">NO DATA FOUND IN SECTOR.</p>
                </div>

            </div>
        </div>
        
        <div class="mt-4 flex justify-between items-center text-[10px] text-gray-600 uppercase font-mono border-t border-gray-900 pt-4">
            <div>SECURE CONNECTION ESTABLISHED</div>
            <div>POWERED BY <span class="text-cyber-green">STRAVA API</span> // RLAN-40</div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    const { createApp } = Vue
    const APP_URL = '{{ rtrim(config('app.url'), '/') }}' || window.location.origin
    createApp({
        data() {
            return {
                activeTab: 'discipline',
                loading: true,
                filterGender: 'all',
                lastUpdated: 'OFFLINE',
                rawData: { discipline: [], performance: [], club_members: [] }
            }
        },
        computed: {
            filteredDiscipline() {
                let data = this.rawData.discipline || [];
                if (this.filterGender !== 'all') {
                    data = data.filter(r => (r.gender || '').toUpperCase() === this.filterGender);
                }
                return data.sort((a, b) => (b.percentage || 0) - (a.percentage || 0));
            },
            filteredPerformance() {
                let data = this.rawData.performance || [];
                if (this.filterGender !== 'all') {
                    data = data.filter(r => (r.gender || '').toUpperCase() === this.filterGender);
                }
                return data.sort((a, b) => (b.gap_seconds || 0) - (a.gap_seconds || 0));
            },
            filteredClubMembers() {
                let data = this.rawData.club_members || [];
                if (this.filterGender !== 'all') {
                    data = data.filter(r => (r.gender || '').toUpperCase() === this.filterGender);
                }
                return data.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
            }
        },
        mounted() {
            this.fetchData();
            this.fetchClubMembers();
        },
        methods: {
            async fetchData() {
                this.loading = true;
                try {
                    const apiUrl = APP_URL + '/api/leaderboard/40days';
                    const res = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error('Failed to load leaderboard');
                    const data = await res.json();
                    this.rawData.discipline = data.discipline || [];
                    this.rawData.performance = data.performance || [];
                    this.lastUpdated = new Date().toLocaleTimeString('en-US', { hour12: false }) + " UTC+7";
                } catch (e) {
                    console.error('fetchData error', e);
                    this.rawData.discipline = [];
                    this.rawData.performance = [];
                } finally {
                    this.loading = false;
                }
            },
            async fetchClubMembers() {
                try {
                    const apiUrl = APP_URL + '/api/club/members';
                    const res = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) throw new Error('Failed to load club members');
                    const data = await res.json();
                    this.rawData.club_members = data.members || [];
                } catch (e) {
                    console.error('fetchClubMembers error', e);
                    this.rawData.club_members = [];
                }
            }
        }
    }).mount('#app')
</script>
@endpush
