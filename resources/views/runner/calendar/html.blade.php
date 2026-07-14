<div id="runner-calendar-app" v-cloak>
    <!-- Notification Toast -->
    <transition name="fade">
        <div v-if="notification" class="fixed top-24 right-4 z-[5000] max-w-sm w-full">
            <div :class="notification.type === 'error' ? 'bg-red-500/90 border-red-400 text-white' : 'bg-green-500/90 border-green-400 text-white'" 
                 class="p-4 rounded-2xl border backdrop-blur-md shadow-2xl flex items-start gap-3">
                <span class="text-lg">@{{ notification.type === 'error' ? '⚠️' : '✅' }}</span>
                <div class="flex-1 text-sm font-bold">@{{ notification.message }}</div>
                <button @click="notification = null" class="text-white/70 hover:text-white">✕</button>
            </div>
        </div>
    </transition>

    <main class="min-h-screen pb-28 md:pb-10 px-4 md:px-8 font-sans bg-[#060a17] bg-gradient-to-b from-[#060a17] via-[#0d162d] to-[#060a17] text-slate-100">
    <div class="max-w-7xl mx-auto pt-10">
        <div class="flex flex-col md:flex-row justify-between items-end gap-4 mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight leading-none">
                    Training <span class="text-neon">Runner Calendar</span>
                </h1>
                <p class="text-xs text-slate-400 mt-2 font-medium">Jadwal program latihan lari, race, dan pantau progres mingguan Anda</p>
            </div>
            <div class="relative z-[10] isolate pointer-events-auto w-full md:w-auto">
                <div class="grid grid-cols-3 gap-2 md:hidden">
                    <button type="button" @click="openMobileAddSheet" class="w-full px-2.5 py-1.5 rounded-[4px] bg-neon text-dark text-[11px] font-bold shadow shadow-neon/10 active:scale-[0.97] transition flex items-center justify-center gap-1">
                        <span class="text-xs">＋</span>
                        <span>Add</span>
                    </button>
                    <button type="button" @click="syncStrava" :disabled="isSyncingStrava" class="w-full px-2.5 py-1.5 rounded-[4px] bg-[#FC4C02] text-white text-[11px] font-bold shadow shadow-orange-600/10 active:scale-[0.97] transition flex items-center justify-center gap-1 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span v-if="isSyncingStrava" class="animate-spin text-[10px]">⟳</span>
                        <span>Sync</span>
                    </button>
                    <button type="button" @click="showHeaderActions = true" class="w-full px-2.5 py-1.5 rounded-[4px] bg-slate-800/80 border border-slate-700/60 text-slate-300 text-[11px] font-bold active:scale-[0.97] transition flex items-center justify-center gap-1 shadow-sm">
                        <span class="text-xs">⋯</span>
                        <span>More</span>
                    </button>
                </div>

                <div class="hidden md:flex gap-2 flex-wrap justify-end" data-debug="runner-calendar-header-actions">
                    <button type="button" @click="() => { console.log('[RunnerCalendar] Click: Generate VDOT'); openVdotModal(); }" class="relative z-[5001] cursor-pointer px-2.5 py-1.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 transition text-[11px] font-semibold shadow-sm">Generate VDOT</button>
                    <button type="button" @click="openStravaAnalysisModal" class="relative z-[5001] cursor-pointer px-2.5 py-1.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 transition text-[11px] font-semibold shadow-sm flex items-center gap-1.5">⚡ Analisis My Training</button>
                    <button type="button" @click="syncStrava" :disabled="isSyncingStrava" class="relative z-[5001] cursor-pointer px-2.5 py-1.5 rounded-[4px] bg-orange-600 text-white hover:bg-orange-500 transition text-[11px] font-semibold shadow flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed">
                        <span v-if="isSyncingStrava" class="animate-spin text-[10px]">⟳</span>
                        Sync Strava
                    </button>
                    @if($isEnrolled40Days)
                    <a href="{{ route('challenge.create') }}" class="relative z-[5001] px-2.5 py-1.5 rounded-[4px] bg-orange-600 text-white font-semibold hover:bg-orange-500 transition text-[11px] shadow">Lapor Aktivitas</a>
                    @endif
                    <a href="{{ route('programs.index') }}" class="relative z-[5001] px-2.5 py-1.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 transition text-[11px] font-semibold shadow-sm">Browse Programs</a>
                    <button type="button" @click="() => { console.log('[RunnerCalendar] Click: Add Custom Workout'); openFormForToday(); }" class="relative z-[5001] cursor-pointer px-2.5 py-1.5 rounded-[4px] bg-neon text-dark font-semibold hover:opacity-90 transition shadow shadow-neon/10 text-[11px]">Add Custom Workout</button>
                    <button type="button" @click="() => { console.log('[RunnerCalendar] Click: Add Race'); openRaceForm(); }" class="relative z-[5001] cursor-pointer px-2.5 py-1.5 rounded-[4px] bg-orange-600 text-white font-semibold hover:bg-orange-500 transition shadow text-[11px]">Add Race</button>
                </div>
            </div>
        </div>

        <div v-if="showMobileAddSheet" class="fixed inset-0 z-[1200] md:hidden">
            <div class="fixed inset-0 bg-black/75 backdrop-blur-sm" @click="showMobileAddSheet = false"></div>
            <div class="fixed bottom-0 left-0 right-0 rounded-t-[8px] bg-slate-900 border-t border-slate-800 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">Add Activity</div>
                    <button type="button" class="w-7 h-7 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold transition hover:bg-slate-700" @click="showMobileAddSheet = false">✕</button>
                </div>
                <div class="grid grid-cols-1 gap-2">
                    <button type="button" class="w-full px-3 py-2.5 rounded-[4px] bg-neon text-dark font-bold text-xs flex items-center justify-between" @click="showMobileAddSheet = false; openFormForToday();">
                        <span>Add Custom Workout</span>
                        <span class="text-dark/70">›</span>
                    </button>
                    <button type="button" class="w-full px-3 py-2.5 rounded-[4px] bg-orange-600 text-white font-bold text-xs flex items-center justify-between" @click="showMobileAddSheet = false; openRaceForm();">
                        <span>Add Race</span>
                        <span class="text-white/70">›</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="showHeaderActions" class="fixed inset-0 z-[1200] md:hidden">
            <div class="fixed inset-0 bg-black/75 backdrop-blur-sm" @click="showHeaderActions = false"></div>
            <div class="fixed bottom-0 left-0 right-0 rounded-t-[8px] bg-slate-900 border-t border-slate-800 p-4">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-[10px] font-mono text-slate-400 uppercase tracking-widest">More Actions</div>
                    <button type="button" class="w-7 h-7 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold transition hover:bg-slate-700" @click="showHeaderActions = false">✕</button>
                </div>
                <div class="grid grid-cols-1 gap-2">
                    <button type="button" class="w-full px-3 py-2.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs flex items-center justify-between" @click="showHeaderActions = false; openVdotModal();">
                        <span>Generate VDOT</span>
                        <span class="text-slate-400">›</span>
                    </button>
                    <button type="button" class="w-full px-3 py-2.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs flex items-center justify-between" @click="showHeaderActions = false; openStravaAnalysisModal();">
                        <span>Analisis My Training (AI)</span>
                        <span class="text-slate-400">›</span>
                    </button>
                    <a href="{{ route('programs.index') }}" class="w-full px-3 py-2.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs flex items-center justify-between">
                        <span>Browse Programs</span>
                        <span class="text-slate-400">›</span>
                    </a>
                    @if($isEnrolled40Days)
                    <a href="{{ route('challenge.create') }}" class="w-full px-3 py-2.5 rounded-[4px] bg-orange-600 text-white font-bold text-xs flex items-center justify-between">
                        <span>Lapor Aktivitas</span>
                        <span class="text-white/85">›</span>
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Apply Program Modal -->
        <div v-if="showApplyModal" class="fixed inset-0 z-[250] overflow-y-auto flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showApplyModal = false"></div>
            <div class="relative z-10 max-w-sm w-full glass-panel rounded-2xl p-5 border border-slate-800 shadow-2xl shadow-neon/5 bg-[#0a1020]/95 backdrop-blur-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-black text-lg italic uppercase tracking-tight">
                        Mulai Program
                    </h3>
                    <button class="w-6 h-6 rounded-md bg-slate-800/80 border border-slate-700/60 text-slate-400 hover:text-white text-xs flex items-center justify-center transition" @click="showApplyModal = false">✕</button>
                </div>
                <div class="mb-4">
                    <p class="text-slate-400 text-xs leading-relaxed mb-3 font-sans">Aktifkan program dari Program Bag dengan memilih tanggal mulai.</p>
                    <div class="bg-[#ccff00]/5 border border-[#ccff00]/25 rounded-xl p-3" v-if="applyTarget">
                        <div class="text-[9px] text-[#ccff00] font-bold uppercase tracking-wider font-mono">Program Pilihan</div>
                        <div class="text-xs font-black text-white mt-0.5">@{{ applyTarget?.program?.title }}</div>
                    </div>
                </div>
                <form @submit.prevent="submitApply" class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block mb-1.5 font-mono">Start Date</label>
                        <input type="date" v-model="applyForm.start_date" required class="w-full bg-slate-900/80 border border-slate-750 rounded-xl px-3 py-2 text-xs text-white focus:border-neon focus:outline-none transition">
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-3.5 border-t border-slate-800">
                        <button type="button" class="px-4 py-2 rounded-xl bg-slate-800/80 text-slate-300 border border-slate-700/60 text-xs font-bold hover:text-white transition" @click="showApplyModal = false">Batal</button>
                        <button type="submit" :disabled="applyLoading" class="px-5 py-2 rounded-xl bg-neon text-dark font-black text-xs uppercase italic tracking-wider hover:bg-white transition shadow-lg shadow-neon/10 flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span v-if="applyLoading" class="animate-spin text-[10px]">⟳</span>
                            <span>Aktifkan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Weekly Volume Chart (Hidden per request) -->
        <div class="hidden glass-panel rounded-2xl p-6 mb-6" v-if="weeklyVolume.length > 0">
            <!-- content hidden -->
        </div>

        <!-- Mobile Navigation Tabs (Only visible on mobile) -->
        <div class="block md:hidden mb-4 bg-slate-900/60 p-1 rounded-[6px] border border-slate-800/80 backdrop-blur-md">
            <div class="flex items-center w-full gap-1">
                <button type="button" 
                        class="flex-1 py-1.5 rounded-[4px] text-[11px] font-bold transition-all duration-300 flex items-center justify-center gap-1"
                        :class="activeMobileTab === 'calendar' ? 'bg-neon text-dark font-black shadow' : 'bg-[#0d1527] text-slate-400'"
                        @click="activeMobileTab = 'calendar'">
                    <i class="fa-solid fa-calendar-days text-[10px]" :class="activeMobileTab === 'calendar' ? 'text-dark' : 'text-slate-400'"></i>
                    <span>Jadwal</span>
                </button>
                <button type="button" 
                        class="flex-1 py-1.5 rounded-[4px] text-[11px] font-bold transition-all duration-300 flex items-center justify-center gap-1"
                        :class="activeMobileTab === 'programs' ? 'bg-neon text-dark font-black shadow' : 'bg-[#0d1527] text-slate-400'"
                        @click="activeMobileTab = 'programs'">
                    <i class="fa-solid fa-route text-[10px]" :class="activeMobileTab === 'programs' ? 'text-dark' : 'text-slate-400'"></i>
                    <span>Program</span>
                </button>
                <button type="button" 
                        class="flex-1 py-1.5 rounded-[4px] text-[11px] font-bold transition-all duration-300 flex items-center justify-center gap-1"
                        :class="activeMobileTab === 'profile' ? 'bg-neon text-dark font-black shadow' : 'bg-[#0d1527] text-slate-400'"
                        @click="activeMobileTab = 'profile'">
                    <i class="fa-solid fa-gauge-high text-[10px]" :class="activeMobileTab === 'profile' ? 'text-dark' : 'text-slate-400'"></i>
                    <span>Training Pace</span>
                </button>
            </div>
        </div>

        <!-- Programs Row (Active & Bag) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" :class="activeMobileTab === 'programs' ? 'grid' : 'hidden md:grid'">
            <!-- Active Programs -->
            <div class="glass-panel-orange rounded-2xl p-4 md:p-6">
                <h3 class="text-sm font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    Active Programs
                </h3>
                <div class="space-y-3" v-if="enrollments.length > 0">
                    <div v-for="en in enrollments" :key="en.id" class="p-4 rounded-xl bg-slate-800/40 border border-slate-700 flex flex-col gap-3">
                        <div>
                            <div class="text-white font-bold">@{{ en.program.title }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">Start: @{{ formatDate(en.start_date) }} • End: @{{ formatDate(en.end_date) }}</div>
                            <div v-if="en.program && en.program.coach" class="mt-2 flex items-center gap-3">
                                <img :src="en.program.coach.avatar_url || assetProfile" class="w-8 h-8 rounded-full border border-slate-600" :alt="en.program.coach.name">
                                <div class="flex-1">
                                    <div class="text-slate-300 text-sm">@{{ en.program.coach.name }}</div>
                                    <div class="flex gap-2 mt-1">
                                        <a :href="runnerUrl + '/' + (en.program.coach.username || en.program.coach.id)" class="text-[10px] px-2 py-1 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-300 hover:text-white transition">Profile</a>
                                        <a :href="chatUrl + '/' + en.program.coach.id" @click.prevent="chatCoach(en.program.coach)" class="text-[10px] px-2 py-1 rounded-[4px] bg-neon text-dark font-bold hover:bg-neon/90 transition">Chat Coach</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button class="px-2 py-1.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-300 text-[10px] w-full hover:bg-slate-700 transition" @click="openRescheduleModal(en)">Reschedule</button>
                            <button class="px-2 py-1.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-300 text-[10px] w-full hover:bg-slate-700 transition" @click="resetPlan(en.id)">Reset to Bag</button>
                            <button class="px-2 py-1.5 rounded-[4px] bg-red-500/10 text-red-400 border border-red-500/20 text-[10px] w-full hover:bg-red-500/20 transition" @click="deleteEnrollment(en.id)">Delete</button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-slate-400 text-xs py-4 border border-dashed border-slate-800 rounded-[6px] text-center italic">No active programs.</div>
            </div>

            <!-- Program Bag -->
            <div class="glass-panel rounded-2xl p-4 md:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Program Bag</h3>
                    <!-- Tabs for Bag -->
                    <div class="flex gap-1">
                        <button class="text-[9px] font-bold px-2 py-1 rounded-lg transition" 
                            :class="bagTab === 'available' ? 'bg-neon text-dark' : 'text-slate-500 hover:text-white'"
                            @click="bagTab = 'available'">Available</button>
                        <button class="text-[9px] font-bold px-2 py-1 rounded-lg transition" 
                            :class="bagTab === 'cancelled' ? 'bg-slate-700 text-white' : 'text-slate-500 hover:text-white'"
                            @click="bagTab = 'cancelled'">History</button>
                    </div>
                </div>

                <!-- Available Programs -->
                <div v-if="bagTab === 'available'">
                    <div class="space-y-3" v-if="programBag.length > 0">
                        <div v-for="bg in programBag" :key="bg.id" class="p-3.5 rounded-[6px] bg-slate-800/40 border border-slate-700 flex flex-col gap-3">
                            <div>
                                <div class="text-white font-bold text-sm">@{{ bg.program.title }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">Purchased: @{{ formatDate(bg.created_at) }}</div>
                            </div>
                            <div class="flex gap-2">
                                <button class="px-2.5 py-1.5 rounded-[4px] bg-neon text-dark font-bold text-[10px] w-full hover:bg-neon/90 transition" @click="applyProgram(bg.id)">Apply to Calendar</button>
                                <button class="px-2.5 py-1.5 rounded-[4px] bg-red-500/10 text-red-400 border border-red-500/20 text-[10px] w-1/3 hover:bg-red-500/20 transition flex items-center justify-center" @click="deleteEnrollment(bg.id)">Hapus</button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-slate-400 text-xs py-4 border border-dashed border-slate-850 rounded-[6px] text-center italic">
                        Your bag is empty. <a href="{{ route('programs.index') }}" class="text-neon hover:underline">Browse Programs</a>
                    </div>
                </div>

                <!-- Cancelled/History Programs -->
                <div v-if="bagTab === 'cancelled'">
                    <div class="space-y-3" v-if="cancelledPrograms.length > 0">
                        <div v-for="bg in cancelledPrograms" :key="bg.id" class="p-3.5 rounded-[6px] bg-slate-800/40 border border-slate-700 flex flex-col gap-3 opacity-75">
                            <div>
                                <div class="text-slate-300 font-bold text-sm">@{{ bg.program.title }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">Cancelled: @{{ formatDate(bg.updated_at) }}</div>
                            </div>
                            <div class="flex gap-2">
                                <button class="px-2.5 py-1.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-300 font-bold text-[10px] w-full hover:bg-slate-700 transition" @click="restoreProgram(bg.id)">Restore to Bag</button>
                                <button class="px-2.5 py-1.5 rounded-[4px] bg-red-500/10 text-red-400 border border-red-500/20 text-[10px] w-full hover:bg-red-500/20 transition flex items-center justify-center" @click="deleteEnrollment(bg.id, true)">Hapus Permanen</button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-slate-400 text-xs py-4 border border-dashed border-slate-850 rounded-[6px] text-center italic">No history history.</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="glass-panel rounded-2xl p-4 md:p-6 relative overflow-hidden" :class="activeMobileTab === 'profile' ? 'block' : 'hidden lg:block'">
                    <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none" style="pointer-events: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                    Training Profile
                                </h3>
                                <p class="text-xs text-slate-400">Based on your Personal Best (PB)</p>
                            </div>
                            <div class="flex flex-wrap gap-1.5 justify-end relative z-10">
                                <button type="button" @click.stop="syncTraining" class="text-[11px] bg-slate-800 text-slate-300 px-2 py-1 rounded-[4px] border border-slate-700/80 hover:text-white transition flex items-center gap-1" :disabled="syncLoading">
                                    <span v-if="syncLoading" class="animate-spin">⟳</span>
                                    <span v-else>⟳</span>
                                    Sync Training
                                </button>
                                <button type="button" @click.stop="openStravaAnalysisModal" class="text-[11px] bg-slate-800 text-slate-300 px-2 py-1 rounded-[4px] border border-slate-700/80 hover:text-white transition flex items-center gap-1">
                                    <span>⚡</span> Analisis AI MCP
                                </button>
                                <button type="button" @click.stop="openPbModal" class="text-[11px] text-neon hover:underline font-bold px-1.5 py-1">Update PB</button>
                            </div>
                        </div>

                        <!-- VDOT & Weekly Target -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 text-center relative overflow-hidden group cursor-pointer" @click="openVdotModal">
                                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-purple-500 to-neon opacity-60"></div>
                                <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">VDOT Score</div>
                                <div class="text-4xl font-black text-white">@{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                                <div class="text-[10px] text-slate-500 mt-1">VO2Max Approx: @{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                                <!-- Runner Level Badge -->
                                <div v-if="trainingProfile.vdot" class="mt-2 inline-block">
                                    <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full border"
                                          :class="{
                                              'bg-yellow-500/20 text-yellow-300 border-yellow-500/40': trainingProfile.vdot >= 75,
                                              'bg-purple-500/20 text-purple-300 border-purple-500/40': trainingProfile.vdot >= 60 && trainingProfile.vdot < 75,
                                              'bg-orange-500/20 text-orange-300 border-orange-500/40': trainingProfile.vdot >= 50 && trainingProfile.vdot < 60,
                                              'bg-blue-500/20 text-blue-300 border-blue-500/40': trainingProfile.vdot >= 40 && trainingProfile.vdot < 50,
                                              'bg-green-500/20 text-green-300 border-green-500/40': trainingProfile.vdot < 40
                                          }">
                                        <span v-if="trainingProfile.vdot >= 75">🏆 Elite</span>
                                        <span v-else-if="trainingProfile.vdot >= 60">⭐ Sub-Elite</span>
                                        <span v-else-if="trainingProfile.vdot >= 50">🔥 Advanced</span>
                                        <span v-else-if="trainingProfile.vdot >= 40">💪 Intermediate</span>
                                        <span v-else-if="trainingProfile.vdot >= 30">🌱 Beginner+</span>
                                        <span v-else>🚶 Beginner</span>
                                    </span>
                                </div>
                                <!-- Hint -->
                                <div class="text-[9px] text-purple-400 mt-1.5 opacity-0 group-hover:opacity-100 transition">Klik untuk generate program ↗</div>
                            </div>
                            <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700 text-center relative group">
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                                    <button @click="showWeeklyTargetModal = true" class="text-xs text-neon hover:text-white bg-slate-700/50 p-1 rounded">Edit</button>
                                </div>
                                <div class="text-xs text-slate-400 uppercase tracking-wider mb-1">Weekly Target (km)</div>
                                <div class="text-4xl font-black text-white cursor-pointer" @click="showWeeklyTargetModal = true">@{{ trainingProfile.weekly_km_target ? Number(trainingProfile.weekly_km_target).toFixed(1) : '-' }}</div>
                                <div class="text-[10px] text-slate-500 mt-1">Target mingguan pengguna</div>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="flex gap-4 border-b border-slate-700 mb-4">
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2"
                                :class="profileTab === 'training' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'training'">
                                Training
                            </button>
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2"
                                :class="profileTab === 'equivalent' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'equivalent'">
                                Equivalent
                            </button>
                            <button 
                                class="text-sm font-bold pb-2 transition border-b-2"
                                :class="profileTab === 'track' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                                @click="profileTab = 'track'">
                                Track
                            </button>
                        </div>

                        <!-- Training Tab -->
                        <div v-if="profileTab === 'training'">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead>
                                        <tr class="text-xs text-slate-500 uppercase border-b border-slate-700">
                                            <th class="py-2">Type</th>
                                            <th class="py-2 text-right">1 Km</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-300">
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-green-400 font-bold" id="pace-easy-label">Easy (E) Pace</td>
                                            <td class="py-2 text-right" id="pace-easy-value">@{{ formatPace(trainingProfile.paces?.E) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-blue-400 font-bold" id="pace-marathon-label">Marathon (M) Pace</td>
                                            <td class="py-2 text-right" id="pace-marathon-value">@{{ formatPace(trainingProfile.paces?.M) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-yellow-400 font-bold" id="pace-threshold-label">Threshold (T) Pace</td>
                                            <td class="py-2 text-right" id="pace-threshold-value">@{{ formatPace(trainingProfile.paces?.T) }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 text-orange-400 font-bold" id="pace-interval-label">Interval (I) Pace</td>
                                            <td class="py-2 text-right" id="pace-interval-value">@{{ formatPace(trainingProfile.paces?.I) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 text-red-400 font-bold" id="pace-repetition-label">Repetition (R) Pace</td>
                                            <td class="py-2 text-right" id="pace-repetition-value">@{{ formatPace(trainingProfile.paces?.R) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Equivalent Tab -->
                        <div v-if="profileTab === 'equivalent'">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead>
                                        <tr class="text-xs text-slate-500 uppercase border-b border-slate-700">
                                            <th class="py-2">Race</th>
                                            <th class="py-2 text-right">Time</th>
                                            <th class="py-2 text-right">Pace</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-300">
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 font-bold">5K</td>
                                            <td class="py-2 text-right text-white font-mono" id="equiv-5k">@{{ trainingProfile.equivalent_race_times?.['5k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['5k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 font-bold">10K</td>
                                            <td class="py-2 text-right text-white font-mono" id="equiv-10k">@{{ trainingProfile.equivalent_race_times?.['10k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['10k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr class="border-b border-slate-800">
                                            <td class="py-2 font-bold">Half Marathon</td>
                                            <td class="py-2 text-right text-white font-mono" id="equiv-hm">@{{ trainingProfile.equivalent_race_times?.['21k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['21k']?.pace || '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-bold">Marathon</td>
                                            <td class="py-2 text-right text-white font-mono" id="equiv-fm">@{{ trainingProfile.equivalent_race_times?.['42k']?.time || '-' }}</td>
                                            <td class="py-2 text-right text-slate-400 font-mono text-xs">@{{ trainingProfile.equivalent_race_times?.['42k']?.pace || '-' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Track Tab -->
                        <div v-if="profileTab === 'track'">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead>
                                        <tr class="text-xs text-slate-500 uppercase border-b border-slate-700">
                                            <th class="py-2">Distance</th>
                                            <th class="py-2 text-right text-red-400">Rep (R)</th>
                                            <th class="py-2 text-right text-orange-400">Int (I)</th>
                                            <th class="py-2 text-right text-yellow-400">Thr (T)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-slate-300">
                                        <tr v-for="(times, dist) in trainingProfile.track_times" :key="dist" class="border-b border-slate-800 last:border-0">
                                            <td class="py-2 font-bold text-white">@{{ dist }}</td>
                                            <td class="py-2 text-right font-mono">
                                                <div class="text-white">@{{ times.R }}</div>
                                                <div class="text-[10px] text-slate-500">@{{ times.pace_R }}/km</div>
                                            </td>
                                            <td class="py-2 text-right font-mono">
                                                <div class="text-white">@{{ times.I }}</div>
                                                <div class="text-[10px] text-slate-500">@{{ times.pace_I }}/km</div>
                                            </td>
                                            <td class="py-2 text-right font-mono">
                                                <div class="text-white">@{{ times.T }}</div>
                                                <div class="text-[10px] text-slate-500">@{{ times.pace_T }}/km</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Global Unlock Banner -->
                <div v-if="hasUnpaidGenerator" class="mb-6 p-6 rounded-3xl bg-slate-900/80 border border-cyan-500/30 backdrop-blur-xl shadow-2xl overflow-hidden relative group" :class="activeMobileTab === 'calendar' ? 'block' : 'hidden lg:block'">
                    <!-- Background Accent -->
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-cyan-500/10 rounded-full blur-3xl group-hover:bg-cyan-500/20 transition-all duration-700"></div>
                    
                    <div class="relative z-10 flex flex-col lg:flex-row gap-6 items-center">
                        <!-- Icon & Info -->
                        <div class="flex items-center gap-5 w-full lg:w-auto">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-600/20 flex items-center justify-center text-3xl shadow-inner border border-cyan-500/20 flex-shrink-0">
                                🔓
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-lg md:text-xl font-black text-white italic tracking-tight uppercase leading-tight">Buka Program Lengkap!</h4>
                                <p class="text-xs md:text-sm text-slate-400 leading-relaxed mt-1">Dukung kami dengan donasi untuk membuka <span class="text-cyan-400 font-bold">Minggu @{{ firstLockedWeek }} sampai akhir program</span>.</p>
                            </div>
                        </div>

                        <!-- Controls Area -->
                        <div class="flex flex-col md:flex-row gap-4 items-center w-full lg:flex-1 bg-slate-800/40 p-4 rounded-2xl border border-slate-700/50">
                            <!-- Donation Slider -->
                            <div class="w-full md:flex-1 space-y-2">
                                <div class="flex justify-between items-center">
                                    <label class="text-[10px] font-mono text-cyan-400 uppercase tracking-widest">Donasi</label>
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-500 text-xs font-bold">Rp</span>
                                        <input type="number" v-model="donationAmount" min="10000" step="5000" 
                                               class="w-20 bg-transparent text-sm font-black text-white focus:outline-none text-right">
                                    </div>
                                </div>
                                <input v-model="donationAmount" type="range" min="10000" max="250000" step="5000" 
                                       class="w-full h-1.5 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-cyan-500">
                            </div>

                            <!-- Divider -->
                            <div class="hidden md:block w-px h-10 bg-slate-700/50"></div>

                            <!-- Promo Code -->
                            <div class="w-full md:w-48">
                                <div class="relative">
                                    <input v-model="promoCode" type="text" placeholder="Promo (No HP)" 
                                           class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:border-cyan-500 focus:outline-none transition-all"
                                           :class="{'border-green-500/50': promoApplied, 'border-red-500/50': promoError}">
                                    <button v-if="!promoApplied && promoCode" @click="applyPromo" :disabled="checkingPromo"
                                            class="absolute right-1.5 top-1.5 px-2 py-1 bg-cyan-500 text-slate-900 text-[10px] font-black rounded-lg hover:bg-cyan-400 transition-all">
                                        @{{ checkingPromo ? '...' : 'APPLY' }}
                                    </button>
                                    <span v-if="promoApplied" class="absolute right-3 top-2.5 text-green-400">✓</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <button @click="handleUnlockAction" :disabled="donationLoading || checkingPromo" 
                                    class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 text-slate-900 font-black rounded-xl shadow-lg hover:scale-105 active:scale-95 transition-all text-sm whitespace-normal md:whitespace-nowrap uppercase">
                                <span v-if="!donationLoading">@{{ promoApplied ? 'Unlock Gratis' : 'Unlock Now' }}</span>
                                <span v-else class="animate-spin inline-block w-4 h-4 border-2 border-slate-900 border-t-transparent rounded-full"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="glass-panel rounded-2xl p-4 md:p-6" id="runner-calendar-section" :class="activeMobileTab === 'calendar' ? 'block' : 'hidden lg:block'">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-850 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-neon animate-pulse"></span>
                            <span class="text-xs font-bold text-slate-350 uppercase tracking-widest font-mono">Program Kalender Aktif</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="exportCalendar('image')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-[10px] font-bold uppercase transition flex items-center gap-1.5 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Gambar (PNG)
                            </button>
                            <button @click="exportCalendar('pdf')" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-[10px] font-bold uppercase transition flex items-center gap-1.5 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                PDF
                            </button>
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Plan List moved to sidebar for better UX -->
                <div class="glass-panel-orange rounded-2xl p-4 md:p-6" id="runner-plans-section" :class="activeMobileTab === 'calendar' ? 'block' : 'hidden lg:block'">
                    <div class="flex flex-col sm:flex-row items-end justify-between mb-4 gap-4">
                        <div>
                            <h3 class="text-white font-bold text-lg tracking-tight italic uppercase">Plan List</h3>
                            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-mono">Workout Schedule</p>
                        </div>
                        <div class="flex gap-1 overflow-x-auto w-full sm:w-auto pb-1 no-scrollbar">
                            <button v-for="f in ['unfinished', 'all']" :key="f" 
                                    :class="[filter===f?'bg-neon text-dark':'bg-slate-800 text-slate-400']" 
                                    class="px-2 py-1 rounded-lg border border-slate-700 text-[10px] font-black uppercase transition-all" 
                                    @click="setFilter(f)">@{{ f }}</button>
                        </div>
                    </div>
                    
                    <div v-if="plansLoading" class="p-6 text-center text-slate-400">
                        <div class="animate-spin inline-block w-5 h-5 border-2 border-neon border-t-transparent rounded-full mb-2"></div>
                        <p class="text-[10px] uppercase font-mono">Loading plans...</p>
                    </div>
                    <div v-else-if="plans.length === 0" class="p-6 text-center text-slate-500 text-xs italic">No workout plans found.</div>
                    <div v-else class="space-y-3">
                        <div v-for="plan in displayedPlans" :key="plan.id || plan.date+plan.enrollment_id" 
                             class="p-3 rounded-[6px] border flex flex-col gap-2 relative overflow-hidden group hover:border-slate-600 transition-all duration-300"
                             :class="plan.is_locked ? 'bg-slate-900/60 border-slate-800' : 'bg-slate-800/40 border-slate-700'">
                            
                            <!-- Card Header (Date & Status) separated by divider -->
                            <div class="flex justify-between items-center pb-2 border-b border-slate-700/50">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-black uppercase tracking-widest" :class="plan.is_locked ? 'text-slate-600' : 'text-neon'">@{{ dayName(plan.date) }}</span>
                                    <span class="text-[9px] text-slate-500 font-mono">@{{ formatDate(plan.date) }}</span>
                                </div>
                                <div>
                                    <span v-if="plan.is_locked" class="text-[8px] font-bold px-1.5 py-0.5 rounded-[4px] bg-slate-900 text-slate-500 border border-slate-800 uppercase">Locked</span>
                                    <span v-else class="text-[8px] font-bold px-1.5 py-0.5 rounded-[4px] border uppercase" :class="[plan.status === 'completed' || plan.status === 'imported' ? 'bg-green-500/10 text-green-400 border-green-500/20' : (plan.status === 'started' ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20')]">
                                        @{{ statusText(plan.status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body (Activity Content) -->
                            <div class="space-y-1">
                                <h4 class="font-bold text-xs md:text-sm leading-tight transition" 
                                    :class="plan.is_locked ? 'text-slate-600' : 'text-white cursor-pointer hover:text-neon'"
                                    @click="showPlanDetail(plan)">
                                    @{{ plan.description ? plan.description.split('\n')[0] : (plan.program_title || 'Workout Session') }}
                                </h4>

                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded-[4px] text-[8px] font-bold tracking-wider border"
                                          :class="plan.is_locked ? 'bg-slate-900 text-slate-700 border-slate-800' : 'bg-slate-700 text-slate-300 border-slate-600'">
                                        @{{ plan.type === 'custom_workout' ? activityLabel(plan.activity_type || 'Custom') : activityLabel(plan.type || 'Workout') }}
                                    </span>
                                    <span class="text-[10px] font-mono" :class="plan.is_locked ? 'text-slate-700' : 'text-slate-400'">@{{ plan.distance ? plan.distance + ' km' : (plan.duration || '-') }}</span>
                                </div>
                            </div>

                            <!-- Card Footer Button -->
                            <button v-if="plan.is_locked" class="w-full mt-1 py-1.5 rounded-[4px] bg-slate-800/50 text-slate-600 text-[10px] font-bold border border-slate-700 flex items-center justify-center gap-1.5 hover:bg-slate-800 transition" @click.stop="showPlanDetail(plan)">
                                🔒 UNLOCK
                            </button>
                            <button v-else-if="plan.status==='pending'" class="w-full mt-1 py-1.5 rounded-[4px] bg-neon text-dark text-[10px] font-bold hover:bg-neon/90 transition shadow shadow-neon/10 flex items-center justify-center gap-1.5" @click.stop="updateSessionStatus(plan,'started')">
                                ▶ START
                            </button>
                        </div>
                        
                        <button v-if="canLoadMore" 
                                class="w-full py-1.5 rounded-[4px] bg-slate-800/50 border border-slate-700 text-slate-400 text-[10px] font-bold hover:bg-slate-700 transition uppercase tracking-widest"
                                @click="loadMorePlans">
                            Load More (@{{ plans.length - displayedPlans.length }} left)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showDetailModal" class="fixed inset-0 z-[1050] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm"></div>
            <!-- Dynamic Modal Width based on Type -->
            <div class="relative z-10 mx-4 my-4 md:mx-auto md:my-10 glass-panel rounded-[6px] p-0 border border-slate-800 overflow-hidden transition-all duration-300"
                 :class="detail.type === 'strength' ? 'max-w-2xl' : 'max-w-sm md:max-w-lg'">
                
                <!-- STRENGTH TRAINING UI -->
                <div v-if="detail.type === 'strength'" class="flex flex-col h-full max-h-[85vh]">
                    <!-- Header with Hero Image/Gradient -->
                    <div class="relative h-28 bg-gradient-to-br from-slate-800 to-slate-900 flex items-end p-4 overflow-hidden">
                        <div class="absolute inset-0 opacity-30 bg-[url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80')] bg-cover bg-center"></div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/50 to-transparent"></div>
                        
                        <div class="relative z-10 w-full flex justify-between items-end">
                            <div>
                                <div class="flex items-center flex-wrap gap-1.5 mb-1">
                                    <span class="px-1.5 py-0.5 rounded-[4px] bg-purple-500/20 border border-purple-500/40 text-[9px] text-purple-300 uppercase tracking-wide font-bold">Strength</span>
                                    <span class="text-[10px] text-slate-400">@{{ detail.duration || '45 min' }}</span>
                                    <span class="inline-flex items-center gap-1 bg-slate-950/60 border border-slate-800/80 rounded-[4px] px-1.5 py-0.5 text-[9px] font-mono font-bold uppercase">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(detail.status)"></span>
                                        <span :class="statusClass(detail.status)">@{{ statusText(detail.status) }}</span>
                                    </span>
                                </div>
                                <h2 class="text-sm md:text-base font-bold text-white leading-tight">@{{ detailTitle }}</h2>
                            </div>
                            <button class="w-6 h-6 rounded-[4px] bg-black/40 hover:bg-white/10 flex items-center justify-center text-white backdrop-blur-md transition text-xs" @click="closeDetail">✕</button>
                        </div>
                    </div>

                    <!-- Body: Scrollable Content -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">
                        <!-- Overview Stats -->
                        <div class="grid grid-cols-3 gap-2.5">
                            <div class="bg-slate-800/50 rounded-[6px] p-2.5 border border-slate-700/50">
                                <div class="text-[9px] text-slate-500 uppercase font-mono">Focus</div>
                                <div class="text-xs font-bold text-white">@{{ detail.strength?.category || 'Full Body' }}</div>
                            </div>
                            <div class="bg-slate-800/50 rounded-[6px] p-2.5 border border-slate-700/50">
                                <div class="text-[9px] text-slate-500 uppercase font-mono">Difficulty</div>
                                <div class="text-xs font-bold text-white capitalize">@{{ detail.difficulty || 'Moderate' }}</div>
                            </div>
                            <div class="bg-slate-800/50 rounded-[6px] p-2.5 border border-slate-700/50">
                                <div class="text-[9px] text-slate-500 uppercase font-mono">Exercises</div>
                                <div class="text-xs font-bold text-white">@{{ countExercises(detail) }} Moves</div>
                            </div>
                        </div>

                        <!-- Exercise List (Playlist) -->
                        <div>
                            <h3 class="text-xs font-bold text-white mb-2.5 flex items-center gap-1.5">
                                <span>📋</span> Workout Plan
                            </h3>
                            <div class="space-y-2">
                                <div v-for="(exercise, idx) in parseStrengthExercises(detail)" :key="idx" 
                                     class="group flex items-center gap-3 p-2.5 rounded-[6px] bg-slate-800/40 border border-slate-700 hover:bg-slate-800 transition cursor-pointer"
                                     @click="previewExercise(exercise)">
                                    <!-- Thumbnail Placeholder -->
                                    <div class="w-12 h-12 rounded-[4px] bg-slate-700 flex-shrink-0 overflow-hidden relative">
                                        <div class="absolute inset-0 flex items-center justify-center text-xl group-hover:scale-110 transition" v-html="getExerciseIcon(exercise.name)">
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow">
                                        <div class="text-xs font-bold text-white">@{{ exercise.name }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">@{{ exercise.sets }} Sets • @{{ exercise.reps }} Reps</div>
                                        <div v-if="exercise.notes" class="text-[9px] text-slate-500 italic mt-0.5">"@{{ exercise.notes }}"</div>
                                    </div>

                                    <div class="w-6 h-6 rounded-[4px] border border-slate-600 flex items-center justify-center text-slate-400 text-xs group-hover:border-purple-500 group-hover:text-purple-500 transition">
                                        ▶
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipment -->
                        <div v-if="detail.strength?.equipment || detail.description?.includes('Equipment')" class="text-[11px] text-slate-400 bg-slate-900/50 p-2.5 rounded-[6px] border border-slate-800">
                            <span class="font-bold text-slate-300 font-mono">EQUIPMENT:</span> @{{ detail.strength?.equipment || 'Dumbbells, Mat' }}
                        </div>

                        <div v-if="showGuidedPlayer" class="rounded-[6px] border border-purple-500/20 bg-purple-900/10 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-[9px] font-bold uppercase tracking-widest text-purple-300">Guided Workout</div>
                                    <div class="text-xs font-bold text-white truncate">
                                        @{{ currentExercise ? currentExercise.name : 'Workout' }}
                                    </div>
                                </div>
                                <button type="button" class="px-2.5 py-1.5 rounded-[4px] bg-slate-900/60 border border-slate-700 text-slate-200 text-[10px] font-bold hover:border-purple-500/40 transition" @click="exitGuidedWorkout">Exit</button>
                            </div>

                            <div v-if="!currentExercise" class="mt-2 text-[11px] text-slate-400">Workout plan belum punya exercise.</div>

                            <div v-else class="mt-2.5 grid grid-cols-2 gap-2.5">
                                <div class="bg-slate-900/40 border border-slate-700/60 rounded-[6px] p-2">
                                    <div class="text-[9px] text-slate-500 uppercase font-mono">Progress</div>
                                    <div class="text-xs font-bold text-white">@{{ currentExerciseIndex + 1 }} / @{{ guidedExercises.length }}</div>
                                </div>
                                <div class="bg-slate-900/40 border border-slate-700/60 rounded-[6px] p-2">
                                    <div class="text-[9px] text-slate-500 uppercase font-mono">Timer</div>
                                    <div class="text-xs font-bold text-white">@{{ formatTimer(timerSeconds) }}</div>
                                </div>
                            </div>

                            <div v-if="currentExercise" class="mt-2 text-[10px] text-slate-400">
                                <span>@{{ currentExercise.sets }} sets</span>
                                <span class="text-slate-600 font-mono mx-1.5">•</span>
                                <span>@{{ currentExercise.reps }} reps</span>
                            </div>

                            <div class="mt-3 grid grid-cols-4 gap-1.5">
                                <button type="button" class="py-1.5 rounded-[4px] bg-slate-900/60 border border-slate-700 text-slate-200 text-[10px] font-bold hover:border-purple-500/40 transition" @click="prevExercise">Prev</button>
                                <button type="button" class="py-1.5 rounded-[4px] bg-purple-600 text-white text-[10px] font-bold hover:bg-purple-500 transition" @click="togglePlay">@{{ isPlaying ? 'Pause' : 'Play' }}</button>
                                <button type="button" class="py-1.5 rounded-[4px] bg-slate-900/60 border border-slate-700 text-slate-200 text-[10px] font-bold hover:border-purple-500/40 transition" @click="nextExercise">Next</button>
                                <button type="button" class="py-1.5 rounded-[4px] bg-slate-900/60 border border-slate-700 text-slate-200 text-[10px] font-bold hover:border-purple-500/40 transition" @click="resetTimer">Reset</button>
                            </div>

                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <button type="button" class="py-1.5 rounded-[4px] bg-slate-800 text-slate-200 text-[10px] font-bold hover:bg-slate-700 transition border border-slate-700" @click="stopGuidedWorkout">Stop</button>
                                <button type="button" class="py-1.5 rounded-[4px] bg-green-500 text-white text-[10px] font-bold hover:bg-green-600 transition" @click="finishGuidedWorkout">Finish</button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer: Action -->
                    <div class="p-3 border-t border-slate-800 bg-slate-900/80 backdrop-blur-md">
                        <button v-if="detail.status === 'completed'" type="button" disabled class="w-full py-2.5 rounded-[6px] bg-slate-800/60 text-slate-500 font-bold text-xs uppercase tracking-wider border border-slate-700">
                            Completed
                        </button>
                        <button v-else type="button" class="w-full py-2.5 rounded-[6px] bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-purple-900/30 hover:scale-[1.02] active:scale-[0.98] transition flex items-center justify-center gap-2"
                                @click="startGuidedWorkout(detail)">
                            <span>@{{ detail.status === 'started' ? 'Resume Guided Workout' : 'Start Guided Workout' }}</span>
                            <span class="bg-white/20 px-1.5 py-0.5 rounded-[2px] text-[9px] uppercase tracking-wider">Beta</span>
                        </button>
                    </div>
                </div>

                <!-- STANDARD RUNNING/OTHER UI (Existing) -->
                <div v-else class="p-3 md:p-4">
                <!-- Locked Session UI -->
                <div v-if="detail.session?.is_locked" class="text-center py-6 px-4">
                    <div class="relative inline-block mb-4">
                        <div class="text-5xl animate-bounce">🔒</div>
                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full border-2 border-slate-900 flex items-center justify-center text-[9px] font-bold text-white">!</div>
                    </div>
                    
                    <h3 class="text-lg font-bold text-white mb-2 tracking-tight uppercase">Program Terkunci</h3>
                    
                    <div class="bg-slate-800/50 border border-slate-700 rounded-[6px] p-4 mb-6 text-left space-y-3">
                        <p class="text-slate-300 text-xs leading-relaxed">
                            Program lari periodisasi ini dirancang khusus untuk Anda. Dukung pengembangan 
                            <span class="text-brand font-bold">RuangLari</span> dengan donasi sukarela untuk membuka seluruh jadwal latihan (Minggu 2 sampai selesai).
                        </p>
                        
                        <ul class="space-y-1.5">
                            <li class="flex items-center gap-2 text-[11px] text-slate-400">
                                <span class="text-green-400">✓</span> Akses penuh ke fase Peak & Taper
                            </li>
                            <li class="flex items-center gap-2 text-[11px] text-slate-400">
                                <span class="text-green-400">✓</span> Target pace yang dipersonalisasi
                            </li>
                            <li class="flex items-center gap-2 text-[11px] text-slate-400">
                                <span class="text-green-400">✓</span> Sinkronisasi otomatis ke Strava
                            </li>
                        </ul>
                    </div>

                    <div class="space-y-4">
                        <div class="px-4">
                            <div class="flex justify-between items-center mb-3">
                                <label class="text-[10px] font-mono text-cyan-400 uppercase tracking-widest">Nominal Donasi</label>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-500 font-bold text-sm">Rp</span>
                                    <input type="number" v-model="donationAmount" min="10000" step="5000" 
                                           class="w-28 bg-slate-800 text-base font-bold text-white px-2.5 py-1.5 rounded-[4px] border border-slate-700 shadow-inner outline-none focus:border-cyan-500 transition-all text-right">
                                </div>
                            </div>
                            <input v-model="donationAmount" type="range" min="10000" max="250000" step="5000" 
                                   class="w-full h-2.5 bg-slate-700 rounded-[4px] appearance-none cursor-pointer accent-cyan-500 hover:accent-cyan-400 transition-all mb-2">
                            <div class="flex justify-between text-[9px] text-slate-500 font-mono uppercase tracking-tighter mb-4">
                                <span>Min 10rb</span>
                                <span>Max 250rb</span>
                            </div>

                            <!-- Promo Code Section -->
                            <div class="relative">
                                <input v-model="promoCode" type="text" placeholder="Punya Kode Promo? (No HP Event)" 
                                       class="w-full bg-slate-800 border border-slate-700 rounded-[4px] px-3.5 py-2.5 text-white text-xs focus:border-neon focus:outline-none transition-colors"
                                       :class="{'border-green-500': promoApplied, 'border-red-500': promoError}">
                                <button v-if="!promoApplied && promoCode" @click="applyPromo" :disabled="checkingPromo"
                                        class="absolute right-1.5 top-1.5 px-2.5 py-1 bg-slate-700 hover:bg-slate-600 text-white text-[10px] font-bold rounded-[4px] transition-colors">
                                    @{{ checkingPromo ? 'Checking...' : 'APPLY' }}
                                </button>
                                <span v-if="promoApplied" class="absolute right-3 top-2.5 text-green-400 text-base">✓</span>
                            </div>
                            <p v-if="promoError" class="text-[11px] text-red-400 mt-1 ml-1">@{{ promoError }}</p>
                            <p v-if="promoApplied" class="text-[11px] text-green-400 mt-1 ml-1">Kode valid! Program akan di-unlock gratis.</p>
                        </div>

                        <button @click="handleUnlockAction" :disabled="donationLoading || checkingPromo" 
                                class="w-full py-3.5 bg-gradient-to-r from-cyan-500 to-blue-600 text-slate-900 font-bold rounded-[6px] shadow-xl shadow-cyan-900/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex flex-col items-center justify-center gap-0.5">
                            <span v-if="!donationLoading" class="text-sm font-bold uppercase tracking-wider">
                                @{{ promoApplied ? '🔓 Unlock Gratis' : '🔓 Unlock Full Program' }}
                            </span>
                            <span v-if="!donationLoading" class="text-[8px] opacity-80 uppercase tracking-widest font-mono">
                                @{{ promoApplied ? 'Free Access via Event Participant' : 'Support RuangLari Development' }}
                            </span>
                            <span v-else class="flex items-center gap-2 text-xs">
                                <svg class="animate-spin h-4 w-4 text-slate-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                MEMPROSES...
                            </span>
                        </button>
                        <p v-if="!promoApplied" class="text-[9px] text-slate-500 uppercase tracking-widest font-mono">Secured by Midtrans</p>
                    </div>
                </div>
                
                <div v-else>
                    <!-- Modal Header Bar -->
                    <div class="flex items-center justify-between pb-2 mb-3.5 border-b border-slate-800/60">
                        <div class="flex items-center gap-1.5 bg-slate-950/60 border border-slate-800/80 rounded-[4px] px-2 py-0.5">
                            <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(detail.status)"></span>
                            <span class="text-[9px] font-mono tracking-wider uppercase text-slate-400">Status:</span>
                            <span class="text-[9px] font-mono font-bold tracking-wider uppercase" :class="statusClass(detail.status)">@{{ statusText(detail.status) }}</span>
                        </div>
                        <button class="text-slate-400 hover:text-white text-base font-bold p-1 leading-none transition" @click="closeDetail">✕</button>
                    </div>

                    <!-- Activity Info Header -->
                    <div class="mb-3">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-[9px] font-mono text-slate-500 uppercase tracking-widest">@{{ detailTitle }}</span>
                            <span class="px-2 py-0.5 rounded-[4px] bg-slate-800/80 border border-slate-700 text-[9px] font-bold text-slate-300 tracking-wider">@{{ activityLabel(detail.type) }}</span>
                        </div>
                        <h2 class="text-sm md:text-base font-bold text-white leading-tight">
                            @{{ detail.session?.title || detail.workout_structure?.race_name || 'Workout Session' }}
                        </h2>
                        <div class="text-[9px] font-mono text-slate-400 mt-1">@{{ detail.date_formatted || formatDate(detail.date) }}</div>
                    </div>

                    <!-- Vertically Aligned Metrics Table -->
                    <div class="space-y-1 mb-3.5 p-2.5 bg-slate-800/30 border border-slate-700/50 rounded-[6px]">
                        <div v-if="detail.distance && detail.type !== 'rest'" class="flex items-center justify-between py-1 border-b border-slate-800/60">
                            <span class="text-[9px] text-slate-400 font-mono uppercase">Distance</span>
                            <span class="text-xs font-bold text-white">@{{ detail.distance }} km</span>
                        </div>
                        <div v-if="detail.type !== 'rest'" class="flex items-center justify-between py-1 border-b border-slate-800/60">
                            <span class="text-[9px] text-slate-400 font-mono uppercase">Target Pace</span>
                            <span class="text-xs font-bold text-neon">@{{ displayPace || '-' }}</span>
                        </div>
                        <div v-if="detail.type !== 'rest'" class="flex items-center justify-between py-1 border-b border-slate-800/60">
                            <span class="text-[9px] text-slate-400 font-mono uppercase">Duration</span>
                            <span class="text-xs font-bold text-white">@{{ detail.duration || '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-[9px] text-slate-400 font-mono uppercase">Difficulty</span>
                            <span class="text-xs font-bold text-white">@{{ (detail.program_difficulty || detail.difficulty || '').toUpperCase() || '-' }}</span>
                        </div>
                    </div>

                        <!-- Description Section at the Top -->
                        <div v-if="detail.description" class="mb-3 bg-slate-800/40 border border-slate-700/60 rounded-[6px] p-3 text-xs text-slate-300">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Deskripsi Aktivitas</div>
                                <button v-if="ttsSupported" @click="speakDetailDescription" class="w-6 h-6 rounded-[4px] bg-slate-900/60 hover:bg-slate-800 flex items-center justify-center text-slate-300 hover:text-neon border border-slate-700 transition" type="button">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M5 9v6h4l5 5V4L9 9H5z" />
                                        <path d="M16.5 8.11a5 5 0 010 7.78v-1.74a3.25 3.25 0 000-4.3V8.11z" />
                                    </svg>
                                </button>
                            </div>
                            <div class="whitespace-pre-line leading-relaxed text-slate-200">@{{ detail.description }}</div>
                        </div>

                        <!-- Goals & Effects Section -->
                        <div v-if="['run', 'easy_run', 'recovery', 'long_run', 'tempo', 'threshold', 'interval', 'repetition', 'speed', 'strength', 'rest', 'yoga', 'cycling', 'race'].includes(String(detail.type || '').toLowerCase())"
                             class="mb-3 bg-slate-800/40 rounded-[6px] p-3 border border-slate-700/60">
                            <div class="flex items-center gap-1.5 mb-2">
                                <i class="fa-solid fa-bullseye text-neon text-xs"></i>
                                <span class="text-[10px] font-bold text-white uppercase tracking-wider">Tujuan & Efek Latihan</span>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div>
                                    <span class="text-slate-400 block font-semibold mb-0.5">🎯 Goal Utama:</span>
                                    <span class="text-slate-200 leading-relaxed">@{{ workoutGoalText }}</span>
                                </div>
                                <div class="pt-2 border-t border-slate-700/50">
                                    <span class="text-slate-400 block font-semibold mb-0.5">🧬 Efek bagi Tubuh:</span>
                                    <span class="text-slate-200 leading-relaxed">@{{ workoutEffectText }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- AI Coach Card -->
                        <div v-if="['run', 'easy_run', 'recovery', 'long_run', 'tempo', 'threshold', 'interval', 'repetition', 'speed', 'race'].includes(String(detail.type || '').toLowerCase())" class="mb-3 bg-slate-800/50 rounded-[6px] p-3 border border-slate-700">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded-[4px] bg-neon flex items-center justify-center text-dark font-bold text-[10px]">AI</div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-white truncate">AI Coach</div>
                                        <div class="text-[9px] text-slate-500 uppercase tracking-widest font-mono">Personal guidance</div>
                                    </div>
                                </div>
                                <button v-if="!trainingProfile?.paces?.E && !trainingProfile?.paces?.T && !trainingProfile?.paces?.I" type="button" class="px-2.5 py-1 rounded-[4px] bg-slate-900/60 text-slate-200 border border-slate-700 text-[10px] font-bold hover:border-neon/40 transition" @click="openVdotModal">
                                    Set Pace
                                </button>
                            </div>

                            <div class="mt-2.5 text-xs text-slate-200 leading-relaxed">@{{ aiCoachSummary }}</div>

                            <div v-if="aiCoachCues.length" class="mt-2.5">
                                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1.5">Coaching Cues</div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-1.5">
                                    <div v-for="(c, idx) in aiCoachCues" :key="'cue-' + idx" class="text-xs text-slate-200 bg-slate-900/60 border border-slate-700 rounded-[6px] px-2.5 py-1.5">
                                        @{{ c }}
                                    </div>
                                </div>
                            </div>

                            <div v-if="trainingProfile?.paces?.E || trainingProfile?.paces?.T || trainingProfile?.paces?.I || trainingProfile?.paces?.R" class="mt-2.5">
                                <div class="text-[10px] font-bold text-slate-400 uppercase mb-1.5">Pace Cheat Sheet</div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-1.5">
                                    <div v-if="trainingProfile?.paces?.E" class="rounded-[6px] bg-slate-900/60 border border-slate-700 px-2.5 py-1.5">
                                        <div class="text-[9px] text-slate-500 uppercase font-mono">Easy (E)</div>
                                        <div class="text-white font-bold text-xs">@{{ formatPace(trainingProfile.paces.E) }}/km</div>
                                    </div>
                                    <div v-if="trainingProfile?.paces?.M" class="rounded-[6px] bg-slate-900/60 border border-slate-700 px-2.5 py-1.5">
                                        <div class="text-[9px] text-slate-500 uppercase font-mono">Marathon (M)</div>
                                        <div class="text-white font-bold text-xs">@{{ formatPace(trainingProfile.paces.M) }}/km</div>
                                    </div>
                                    <div v-if="trainingProfile?.paces?.T" class="rounded-[6px] bg-slate-900/60 border border-slate-700 px-2.5 py-1.5">
                                        <div class="text-[9px] text-slate-500 uppercase font-mono">Tempo (T)</div>
                                        <div class="text-white font-bold text-xs">@{{ formatPace(trainingProfile.paces.T) }}/km</div>
                                    </div>
                                    <div v-if="trainingProfile?.paces?.I" class="rounded-[6px] bg-slate-900/60 border border-slate-700 px-2.5 py-1.5">
                                        <div class="text-[9px] text-slate-500 uppercase font-mono">Interval (I)</div>
                                        <div class="text-white font-bold text-xs">@{{ formatPace(trainingProfile.paces.I) }}/km</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <div v-if="stravaDetailsLoading" class="mb-3 bg-slate-900/40 border border-slate-700/60 rounded-[6px] p-3 text-xs text-slate-300">
                        Fetching Strava details…
                    </div>
                    <div v-else-if="stravaDetailsError" class="mb-3 bg-red-500/10 border border-red-500/30 rounded-[6px] p-3 text-xs text-red-200">
                        @{{ stravaDetailsError }}
                    </div>
                    <div v-else-if="detail.strava_metrics" class="mb-3">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <div class="flex flex-col items-center justify-center rounded-[6px] bg-slate-800/60 border border-slate-700 p-2.5">
                                <div class="text-[10px] text-slate-400">Heart Rate</div>
                                <div class="text-white font-bold text-xs">
                                    @{{ detail.strava_metrics.average_heartrate ? Math.round(detail.strava_metrics.average_heartrate) : '-' }}
                                    <span class="text-[9px] text-slate-500">avg</span>
                                </div>
                                <div class="text-[9px] text-slate-500">
                                    max @{{ detail.strava_metrics.max_heartrate ? Math.round(detail.strava_metrics.max_heartrate) : '-' }}
                                </div>
                            </div>
                            <div class="flex flex-col items-center justify-center rounded-[6px] bg-slate-800/60 border border-slate-700 p-2.5">
                                <div class="text-[10px] text-slate-400">Cadence</div>
                                <div class="text-white font-bold text-xs">
                                    @{{ detail.strava_metrics.average_cadence ? Math.round(detail.strava_metrics.average_cadence) : '-' }}
                                </div>
                                <div class="text-[9px] text-slate-500">spm</div>
                            </div>
                            <div class="flex flex-col items-center justify-center rounded-[6px] bg-slate-800/60 border border-slate-700 p-2.5">
                                <div class="text-[10px] text-slate-400">Avg Pace</div>
                                <div class="text-neon font-bold text-xs">@{{ detail.strava_metrics.pace ? (detail.strava_metrics.pace + ' /km') : '-' }}</div>
                                <div class="text-[9px] text-slate-500">&nbsp;</div>
                            </div>
                            <div class="flex flex-col items-center justify-center rounded-[6px] bg-slate-800/60 border border-slate-700 p-2.5">
                                <div class="text-[10px] text-slate-400">Power</div>
                                <div class="text-white font-bold text-xs">
                                    @{{ detail.strava_metrics.average_watts ? Math.round(detail.strava_metrics.average_watts) : '-' }}
                                </div>
                                <div class="text-[9px] text-slate-500">watts</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 mt-2.5">
                            <div class="flex flex-col items-center justify-center rounded-[6px] bg-slate-800/60 border border-slate-700 p-2.5">
                                <div class="text-[10px] text-slate-400">Total Time</div>
                                <div class="text-white font-bold text-xs">@{{ detail.strava_metrics.total_time_s ? formatSeconds(detail.strava_metrics.total_time_s) : '-' }}</div>
                            </div>
                            <div class="flex flex-col items-center justify-center rounded-[6px] bg-slate-800/60 border border-slate-700 p-2.5">
                                <div class="text-[10px] text-slate-400">Moving</div>
                                <div class="text-white font-bold text-xs">@{{ detail.strava_metrics.moving_time_s ? formatSeconds(detail.strava_metrics.moving_time_s) : '-' }}</div>
                            </div>
                            <div class="flex flex-col items-center justify-center rounded-[6px] bg-slate-800/60 border border-slate-700 p-2.5">
                                <div class="text-[10px] text-slate-400">Paused</div>
                                <div class="text-white font-bold text-xs">@{{ detail.strava_metrics.pause_time_s ? formatSeconds(detail.strava_metrics.pause_time_s) : '-' }}</div>
                            </div>
                        </div>

                        <div v-if="detail.strava_pace_zones" class="mt-2.5 p-2.5 rounded-[6px] bg-slate-800/40 border border-slate-700">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Pace Distribution</div>
                            <div class="grid grid-cols-3 gap-2">
                                <div class="flex flex-col items-center justify-center rounded-[4px] bg-slate-900/50 border border-slate-700 p-2">
                                    <div class="text-[9px] text-slate-400">Easy</div>
                                    <div class="text-white font-bold text-xs">@{{ detail.strava_pace_zones.summary.easy }}%</div>
                                </div>
                                <div class="flex flex-col items-center justify-center rounded-[4px] bg-slate-900/50 border border-slate-700 p-2">
                                    <div class="text-[9px] text-slate-400">Tempo</div>
                                    <div class="text-white font-bold text-xs">@{{ detail.strava_pace_zones.summary.tempo }}%</div>
                                </div>
                                <div class="flex flex-col items-center justify-center rounded-[4px] bg-slate-900/50 border border-slate-700 p-2">
                                    <div class="text-[9px] text-slate-400">Speed</div>
                                    <div class="text-white font-bold text-xs">@{{ detail.strava_pace_zones.summary.speed }}%</div>
                                </div>
                            </div>
                            <div class="text-[9px] text-slate-500 mt-2 font-mono">
                                E @{{ detail.strava_pace_zones.zones.E }}% • M @{{ detail.strava_pace_zones.zones.M }}% • T @{{ detail.strava_pace_zones.zones.T }}% • I @{{ detail.strava_pace_zones.zones.I }}% • R @{{ detail.strava_pace_zones.zones.R }}%
                            </div>
                        </div>

                        <div v-if="detail.strava_hr_zones" class="mt-2.5 p-2.5 rounded-[6px] bg-slate-800/40 border border-slate-700">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Heart Rate Distribution</div>
                            <div class="grid grid-cols-5 gap-1.5">
                                <div class="flex flex-col items-center justify-center rounded-[4px] bg-slate-900/50 border border-slate-700 p-1.5">
                                    <div class="text-[9px] text-slate-400">Z1</div>
                                    <div class="text-white font-bold text-[11px]">@{{ detail.strava_hr_zones.Z1 }}%</div>
                                </div>
                                <div class="flex flex-col items-center justify-center rounded-[4px] bg-slate-900/50 border border-slate-700 p-1.5">
                                    <div class="text-[9px] text-slate-400">Z2</div>
                                    <div class="text-white font-bold text-[11px]">@{{ detail.strava_hr_zones.Z2 }}%</div>
                                </div>
                                <div class="flex flex-col items-center justify-center rounded-[4px] bg-slate-900/50 border border-slate-700 p-1.5">
                                    <div class="text-[9px] text-slate-400">Z3</div>
                                    <div class="text-white font-bold text-[11px]">@{{ detail.strava_hr_zones.Z3 }}%</div>
                                </div>
                                <div class="flex flex-col items-center justify-center rounded-[4px] bg-slate-900/50 border border-slate-700 p-1.5">
                                    <div class="text-[9px] text-slate-400">Z4</div>
                                    <div class="text-white font-bold text-[11px]">@{{ detail.strava_hr_zones.Z4 }}%</div>
                                </div>
                                <div class="flex flex-col items-center justify-center rounded-[4px] bg-slate-900/50 border border-slate-700 p-1.5">
                                    <div class="text-[9px] text-slate-400">Z5</div>
                                    <div class="text-white font-bold text-[11px]">@{{ detail.strava_hr_zones.Z5 }}%</div>
                                </div>
                            </div>
                        </div>

                        <div v-if="detail.strava_zone_analysis || detail.analysis" class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                            <div v-if="detail.strava_zone_analysis" class="p-3 rounded-[6px] bg-slate-800/40 border border-slate-700 space-y-2">
                                <div class="text-[10px] font-bold text-slate-400 uppercase">Analisis Zona & Efek</div>
                                <div class="text-slate-300 text-xs leading-relaxed">@{{ detail.strava_zone_analysis }}</div>
                                <div v-if="detail.strava_zone_effect" class="text-[10px] text-neon font-bold uppercase">Efek Latihan</div>
                                <div v-if="detail.strava_zone_effect" class="text-white text-xs font-semibold">@{{ detail.strava_zone_effect }}</div>
                                <div v-if="detail.strava_zone_suggestion" class="pt-2 border-t border-slate-700/50">
                                    <div class="text-[10px] text-yellow-500 font-bold uppercase">Saran</div>
                                    <div class="text-white text-xs font-semibold">@{{ detail.strava_zone_suggestion }}</div>
                                </div>
                            </div>

                            <div v-if="detail.analysis" class="p-3.5 bg-slate-800/40 border border-slate-700 rounded-[6px] space-y-2.5">
                                <div>
                                    <h4 class="text-neon font-bold text-[10px] uppercase tracking-wider mb-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                        Analisis Singkat
                                    </h4>
                                    <p class="text-slate-300 text-xs leading-relaxed">@{{ detail.analysis }}</p>
                                </div>
                                <div class="pt-2.5 border-t border-slate-700/50">
                                    <h4 class="text-yellow-500 font-bold text-[10px] uppercase tracking-wider mb-1 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                        Saran Next Workout
                                    </h4>
                                    <p class="text-white font-semibold text-xs">@{{ detail.suggestion }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="detail.strava_media && detail.strava_media.length" class="mt-3 border-t border-slate-700 pt-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Media</div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                <a v-for="(m, idx) in detail.strava_media" :key="idx" :href="m" target="_blank" class="block">
                                    <img :src="m" class="w-full h-28 object-cover rounded-[4px] border border-slate-700 bg-slate-900" loading="lazy">
                                </a>
                            </div>
                        </div>

                        <div v-if="detail.strava_streams && detail.strava_streams.time && detail.strava_streams.time.length > 0" class="mt-3 border-t border-slate-700 pt-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Performance Chart</div>
                            <div class="h-44 bg-slate-900/30 border border-slate-700 rounded-[6px] p-2 relative group">
                                <button @click="showStravaGraphModal = true" class="absolute top-2 right-2 p-1.5 bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white rounded-[4px] opacity-0 group-hover:opacity-100 transition z-10" title="Expand Chart">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                                    </svg>
                                </button>
                                <canvas id="stravaMetricsChart" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <div v-if="detail.strava_splits && detail.strava_splits.length > 0" class="mt-3 border-t border-slate-700 pt-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Splits</div>
                            <div class="max-h-40 overflow-y-auto space-y-1">
                                <div v-for="s in detail.strava_splits" :key="s.split" class="flex justify-between items-center text-xs p-2 rounded-[6px] bg-slate-800 border border-slate-700">
                                    <div class="text-slate-300 font-bold">KM @{{ s.split || '-' }}</div>
                                    <div class="text-right">
                                        <div class="text-white font-mono">@{{ s.pace || '-' }}</div>
                                        <div class="text-[10px] text-slate-500">@{{ formatSeconds(s.moving_time_s) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="detail.strava_laps && detail.strava_laps.length > 0" class="mt-3 border-t border-slate-700 pt-3">
                            <div class="text-[10px] font-bold text-slate-400 uppercase mb-2">Laps</div>
                            <div class="max-h-40 overflow-y-auto space-y-1">
                                <div v-for="(l, idx) in detail.strava_laps" :key="idx" class="flex justify-between items-center text-xs p-2 rounded-[6px] bg-slate-800 border border-slate-700">
                                    <div class="min-w-0">
                                        <div class="text-slate-300 font-bold truncate">@{{ l.name || ('Lap ' + (idx + 1)) }}</div>
                                        <div class="text-[10px] text-slate-500">
                                            @{{ l.distance_m ? (Math.round(l.distance_m) + ' m') : '-' }} • @{{ formatSeconds(l.moving_time_s) }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-white font-mono">@{{ l.pace || '-' }}</div>
                                        <div class="text-[10px] text-slate-500">@{{ l.average_heartrate ? (Math.round(l.average_heartrate) + ' bpm') : '' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="guidedSteps.length > 0" class="mt-2 border-t border-slate-700 pt-3">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Workout Steps</div>
                            <div class="text-[10px] text-slate-500 font-mono">@{{ guidedStepsDoneCount }}/@{{ guidedSteps.length }}</div>
                        </div>
                        <div class="h-2 rounded-[4px] bg-slate-800 border border-slate-700 overflow-hidden mb-3">
                            <div class="h-full bg-neon transition-all duration-300" :style="{ width: guidedStepsProgressPct + '%' }"></div>
                        </div>
                        <div class="space-y-1">
                            <button v-for="(step, idx) in guidedSteps" :key="step.id" type="button" class="w-full flex justify-between items-center text-xs p-2 rounded-[6px] border transition"
                                    :class="guidedStepChecked(step) ? 'bg-green-500/10 border-green-500/30' : 'bg-slate-800 border-slate-700 hover:border-neon/30'"
                                    @click="toggleGuidedStep(step)">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-5 h-5 rounded-full border flex items-center justify-center flex-shrink-0"
                                         :class="guidedStepChecked(step) ? 'bg-green-500 border-green-500 text-white' : 'bg-slate-900 border-slate-600 text-slate-400'">
                                        @{{ guidedStepChecked(step) ? '✓' : '' }}
                                    </div>
                                    <div class="min-w-0 text-left">
                                        <div class="flex items-center gap-2">
                                            <span v-if="step.badge" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-widest border"
                                                  :class="step.badgeClass">@{{ step.badge }}</span>
                                            <span class="font-bold text-slate-200 truncate">@{{ step.title }}</span>
                                        </div>
                                        <div v-if="step.subtitle" class="text-[10px] text-slate-500 truncate">@{{ step.subtitle }}</div>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0 pl-3">
                                    <div v-if="step.valueText" class="text-white font-mono">@{{ step.valueText }}</div>
                                    <div v-if="step.paceText" class="text-[10px] text-slate-400">@{{ step.paceText }}</div>
                                </div>
                            </button>
                        </div>


                    <div v-if="detail.strava_link" class="mt-3 text-sm">
                        <a :href="detail.strava_link" target="_blank" class="text-neon hover:underline">View Strava Activity</a>
                    </div>
                    <div v-if="detail.notes" class="mt-3 p-3 bg-yellow-500/10 border-l-4 border-yellow-500 rounded-r-[6px]">
                        <div class="text-[10px] text-yellow-500 uppercase font-bold mb-1 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i> Notes
                        </div>
                        <div class="font-bold text-white text-xs">@{{ detail.notes }}</div>
                    </div>
                </div>
                </div>

                <!-- Coach Feedback Display -->
                <div v-if="detail.coach_feedback || detail.coach_rating" class="mt-4 bg-slate-800/50 rounded-[6px] p-3 border border-slate-700">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-5 h-5 rounded-[4px] bg-neon flex items-center justify-center text-dark font-bold text-[10px]">C</div>
                        <span class="text-xs font-bold text-neon">Coach Feedback</span>
                    </div>
                    <div class="space-y-2">
                        <div v-if="detail.coach_rating" class="flex items-center gap-1">
                            <span class="text-xs text-slate-400">Rating:</span>
                            <div class="flex text-yellow-400 text-xs">
                                <span v-for="i in 5" :key="i">@{{ i <= detail.coach_rating ? '★' : '☆' }}</span>
                            </div>
                        </div>
                        <div v-if="detail.coach_feedback" class="text-xs text-slate-300 italic">"@{{ detail.coach_feedback }}"</div>
                    </div>
                </div>

                <div v-if="detail.source === 'strava' || detail.strava_metrics" class="mt-4 bg-slate-800/50 rounded-[6px] p-3 border border-slate-700">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 rounded-[4px] bg-purple-400 flex items-center justify-center text-dark font-bold text-[10px]">AI</div>
                            <span class="text-xs font-bold text-purple-300">AI Workout Analysis</span>
                        </div>
                        <button class="px-2.5 py-1 rounded-[4px] bg-purple-500/20 text-purple-200 border border-purple-500/30 text-[10px] font-bold hover:bg-purple-500/30 transition disabled:opacity-50"
                                :disabled="aiAnalysisLoading"
                                @click="loadAiWorkoutAnalysis(detail.strava_metrics?.strava_activity_id || detail.strava_activity_id, true)">
                            @{{ aiAnalysisLoading ? 'Analyzing...' : 'Refresh AI' }}
                        </button>
                    </div>

                    <div v-if="aiAnalysisLoading" class="text-xs text-slate-400">AI sedang menganalisis workout dan konteks latihan Anda...</div>
                    <div v-else-if="aiAnalysisError" class="text-xs text-red-300 bg-red-500/10 border border-red-500/20 rounded-[4px] px-3 py-2">@{{ aiAnalysisError }}</div>
                    <div v-else-if="detail.ai_analysis" class="space-y-3 text-xs">
                        <div v-if="detail.ai_analysis.summary" class="text-slate-200 leading-relaxed">@{{ detail.ai_analysis.summary }}</div>
                        <div class="text-xs text-slate-300">
                            <span class="text-slate-400 font-mono">Junk Miles Risk:</span>
                            <span class="font-bold">@{{ detail.ai_analysis.junk_miles_risk?.level || 'unknown' }}</span>
                        </div>

                        <div v-if="detail.ai_analysis.what_went_well?.length">
                            <div class="text-[10px] font-bold text-green-300 uppercase mb-2">Yang Sudah Bagus</div>
                            <ul class="space-y-1 text-slate-300">
                                <li v-for="(item, idx) in detail.ai_analysis.what_went_well" :key="'well-' + idx">• @{{ item }}</li>
                            </ul>
                        </div>

                        <div v-if="detail.ai_analysis.what_to_improve?.length">
                            <div class="text-[10px] font-bold text-amber-300 uppercase mb-2">Yang Perlu Ditingkatkan</div>
                            <ul class="space-y-1 text-slate-300">
                                <li v-for="(item, idx) in detail.ai_analysis.what_to_improve" :key="'improve-' + idx">• @{{ item }}</li>
                            </ul>
                        </div>

                        <div v-if="detail.ai_analysis.next_workout_suggestion?.type || detail.ai_analysis.next_workout_suggestion?.reason" class="rounded-[6px] bg-slate-900/80 border border-slate-700 p-2.5">
                            <div class="text-[10px] font-bold text-neon uppercase mb-1.5">Saran Workout Berikutnya</div>
                            <div class="text-white font-bold text-xs">@{{ detail.ai_analysis.next_workout_suggestion.type || '-' }}</div>
                            <div v-if="detail.ai_analysis.next_workout_suggestion.duration" class="text-[10px] text-slate-400 mt-1">Durasi: @{{ detail.ai_analysis.next_workout_suggestion.duration }}</div>
                            <div v-if="detail.ai_analysis.next_workout_suggestion.target" class="text-[10px] text-slate-400">Target: @{{ detail.ai_analysis.next_workout_suggestion.target }}</div>
                            <div v-if="detail.ai_analysis.next_workout_suggestion.reason" class="text-xs text-slate-300 mt-2 leading-relaxed">@{{ detail.ai_analysis.next_workout_suggestion.reason }}</div>
                        </div>

                        <div v-if="detail.ai_analysis.recovery_advice?.length">
                            <div class="text-[10px] font-bold text-sky-300 uppercase mb-2">Recovery Advice</div>
                            <ul class="space-y-1 text-slate-300">
                                <li v-for="(item, idx) in detail.ai_analysis.recovery_advice" :key="'recovery-' + idx">• @{{ item }}</li>
                            </ul>
                        </div>

                        <div v-if="detail.ai_analysis.improve_next_time?.length">
                            <div class="text-[10px] font-bold text-purple-300 uppercase mb-2">Improve Next Time</div>
                            <ul class="space-y-1 text-slate-300">
                                <li v-for="(item, idx) in detail.ai_analysis.improve_next_time" :key="'next-' + idx">• @{{ item }}</li>
                            </ul>
                        </div>

                        <div v-if="detail.ai_analysis.risk_flags?.length" class="rounded-[6px] bg-red-500/10 border border-red-500/20 p-2.5">
                            <div class="text-[10px] font-bold text-red-300 uppercase mb-2">Risk Flags</div>
                            <ul class="space-y-1 text-red-100">
                                <li v-for="(item, idx) in detail.ai_analysis.risk_flags" :key="'risk-' + idx">• @{{ item }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
                   <!-- Action Buttons -->
                <div v-if="detail.type === 'run' || detail.type === 'easy_run' || detail.type === 'interval' || detail.type === 'tempo' || detail.type === 'repetition' || detail.type === 'program_session' || detail.type === 'yoga' || detail.type === 'cycling' || detail.type === 'rest' || detail.type === 'race'" class="mt-3.5 border-t border-slate-700/60 pt-3.5">
                    <div v-if="detail.status === 'pending' || !detail.status">
                        <button class="w-full py-2.5 rounded-[6px] bg-neon text-dark font-bold text-xs hover:bg-neon/90 transition uppercase tracking-wider" @click="updateSessionStatus(detail, 'started')">Start Activity</button>
                    </div>
                    <div v-else-if="detail.status === 'started'">
                        <div class="space-y-3">
                             <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Strava Activity Link (Optional)</label>
                                <input type="url" v-model="stravaLinkInput" placeholder="https://www.strava.com/activities/..." class="w-full bg-slate-900 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                             </div>
                            
                            <!-- RPE & Feeling Input -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">RPE (1-10)</label>
                                    <select v-model="rpeInput" class="w-full bg-slate-900 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                        <option value="">Select Effort</option>
                                        <option value="1">1 - Very Easy</option>
                                        <option value="3">3 - Moderate</option>
                                        <option value="5">5 - Hard</option>
                                        <option value="7">7 - Very Hard</option>
                                        <option value="9">9 - Extremely Hard</option>
                                        <option value="10">10 - Max Effort</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Feeling</label>
                                    <select v-model="feelingInput" class="w-full bg-slate-900 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                        <option value="">Select Feeling</option>
                                        <option value="strong">💪 Strong</option>
                                        <option value="good">😊 Good</option>
                                        <option value="average">😐 Average</option>
                                        <option value="weak">😫 Weak</option>
                                        <option value="terrible">💀 Terrible</option>
                                    </select>
                                </div>
                            </div>

                             <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Notes for Coach (Optional)</label>
                                <textarea v-model="notesInput" rows="2" placeholder="How was your run? Any pain or issues?" class="w-full bg-slate-900 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs"></textarea>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <button class="w-full py-2.5 rounded-[6px] bg-slate-800 text-slate-200 font-bold text-xs hover:bg-slate-700 transition border border-slate-700 uppercase tracking-wider" @click="updateSessionStatus(detail, 'pending')">Stop</button>
                                <button class="w-full py-2.5 rounded-[6px] bg-green-500 text-white font-bold text-xs hover:bg-green-600 transition uppercase tracking-wider" @click="finishActivityWithLink">Finish Activity</button>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="detail.status === 'completed'" class="text-center text-xs text-slate-500">
                        Activity completed on @{{ formatDate(detail.completed_at || new Date()) }}
                    </div>
                </div>

                <div class="mt-3.5 flex justify-between items-center">
                    <button v-if="detail.source === 'custom' || detail.workout_id" class="text-[11px] text-slate-400 hover:text-red-400" @click="deleteCustomWorkout(detail.workout_id)">Delete</button>
                    <button class="px-2.5 py-1.5 rounded-[6px] bg-slate-800 text-slate-300 border border-slate-700 text-[11px] ml-auto font-bold uppercase tracking-wider" @click="closeDetail">Close</button>
                </div>
            </div>
        </div>
        </div>
        </div>

        <div v-if="showPbModal" class="fixed inset-0 z-[1100] flex items-center justify-center p-4" style="backdrop-filter: blur(8px); background: rgba(0,0,0,0.8);">
            <div class="relative w-full max-w-md mx-auto">
                <!-- Glow Effect -->
                <!-- Glow Effect -->
                <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 via-neon/30 to-blue-600 rounded-[6px] blur-lg opacity-30 pointer-events-none"></div>
                <div class="relative bg-slate-900 border border-slate-700 rounded-[6px] p-5 shadow-2xl">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-white font-bold text-base">Update Personal Best</h3>
                            <p class="text-[10px] text-slate-400">Update your PBs to recalculate VDOT</p>
                        </div>
                        <button class="text-slate-400 hover:text-white w-7 h-7 rounded-[4px] bg-slate-800 flex items-center justify-center transition hover:bg-slate-700" @click="showPbModal = false">✕</button>
                    </div>
                    
                    <form @submit.prevent="updatePb" class="space-y-3">
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">5K (HH:MM:SS)</label>
                            <input type="text" v-model="pbForm.pb_5k" placeholder="00:25:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-neon focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">10K (HH:MM:SS)</label>
                            <input type="text" v-model="pbForm.pb_10k" placeholder="00:50:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-neon focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Half Marathon (HH:MM:SS)</label>
                            <input type="text" v-model="pbForm.pb_hm" placeholder="01:50:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-neon focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Full Marathon (HH:MM:SS)</label>
                            <input type="text" v-model="pbForm.pb_fm" placeholder="03:50:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-neon focus:outline-none transition">
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Balke 15-Min Test (Meters)</label>
                            <input type="number" v-model="pbForm.pb_balke" min="0" placeholder="e.g. 3200" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-neon focus:outline-none transition">
                        </div>

                        <div class="pt-3.5 border-t border-slate-700/60 flex justify-end gap-2">
                            <button type="button" class="px-3.5 py-2 rounded-[6px] bg-slate-800 text-slate-300 border border-slate-700 text-xs hover:bg-slate-700 transition uppercase tracking-wider font-bold" @click="showPbModal = false">Cancel</button>
                            <button type="submit" :disabled="pbLoading" class="px-5 py-2 rounded-[6px] bg-neon text-slate-900 font-bold hover:bg-[#b3e600] transition text-xs disabled:opacity-50 shadow-lg shadow-neon/20 uppercase tracking-wider">
                                @{{ pbLoading ? 'Updating...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div v-if="showVdotModal" class="fixed inset-0 z-[1050] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-2xl mx-auto my-10 bg-slate-900 border border-slate-700 rounded-[6px] p-5 shadow-2xl">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-800">
                    <div>
                        <h3 class="text-white font-bold text-base">Generate VDOT Program</h3>
                        <p class="text-[10px] text-slate-400">Based on Jack Daniels' Running Formula</p>
                    </div>
                    <button class="text-slate-400 hover:text-white" @click="showVdotModal = false">✕</button>
                </div>
                
                <form @submit.prevent="generateVdot" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Personal Info -->
                        <div class="space-y-2">
                            <h4 class="text-neon font-bold text-[10px] uppercase tracking-wider">Profile</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Age</label>
                                    <input type="number" v-model="vdotForm.age" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Gender</label>
                                    <select v-model="vdotForm.gender" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Current Fitness -->
                        <div class="space-y-2">
                            <h4 class="text-neon font-bold text-[10px] uppercase tracking-wider">Current Fitness</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Recent Race Dist.</label>
                                    <select v-model="vdotForm.race_distance" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                        <option value="5k">5K</option>
                                        <option value="10k">10K</option>
                                        <option value="21k">Half Marathon</option>
                                        <option value="42k">Marathon</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Recent Time</label>
                                    <input type="text" v-model="vdotForm.race_time" placeholder="00:25:00" required pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Race Date</label>
                                <input type="date" v-model="vdotForm.race_date" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                            </div>
                        </div>

                        <!-- Training History -->
                        <div class="space-y-2">
                            <h4 class="text-neon font-bold text-[10px] uppercase tracking-wider">Training Volume</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Avg Weekly (km)</label>
                                    <input type="number" v-model="vdotForm.weekly_mileage" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Peak Weekly (km)</label>
                                    <input type="number" v-model="vdotForm.peak_mileage" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Training Days/Week</label>
                                <select v-model="vdotForm.training_frequency" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                    <option value="3">3 Days</option>
                                    <option value="4">4 Days</option>
                                    <option value="5">5 Days</option>
                                    <option value="6">6 Days</option>
                                    <option value="7">7 Days</option>
                                </select>
                            </div>
                        </div>

                        <!-- Goal -->
                        <div class="space-y-2">
                            <h4 class="text-neon font-bold text-[10px] uppercase tracking-wider">Goal</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Target Race</label>
                                    <select v-model="vdotForm.goal_distance" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                        <option value="5k">5K</option>
                                        <option value="10k">10K</option>
                                        <option value="21k">Half Marathon</option>
                                        <option value="42k">Marathon</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Goal Time (Optional)</label>
                                    <input type="text" v-model="vdotForm.goal_time" placeholder="00:50:00" pattern="[0-9]{2}:[0-5][0-9]:[0-5][0-9]" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400 block mb-1">Target Race Date</label>
                                <input type="date" v-model="vdotForm.goal_race_date" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                <p class="text-[9px] text-slate-500 mt-1">Recommended: 8-16 weeks from today for optimal results.</p>
                            </div>
                        </div>
                        
                        <!-- AI & Sports Science Options -->
                        <div class="space-y-2 md:col-span-2 border-t border-slate-800 pt-3">
                            <h4 class="text-neon font-bold text-[10px] uppercase tracking-wider">Sports Science & AI settings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Runner Level</label>
                                    <select v-model="vdotForm.runner_level" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                        <option value="beginner">Beginner (Focus: Base & Consistency)</option>
                                        <option value="intermediate">Intermediate (Focus: Threshold & Endurance)</option>
                                        <option value="advanced">Advanced / Elite (Focus: VO2Max & Speed)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 block mb-1">Long Run Day</label>
                                    <select v-model="vdotForm.long_run_day" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                                        <option value="saturday">Saturday</option>
                                        <option value="sunday">Sunday</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-4 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                                    <input type="checkbox" v-model="vdotForm.is_tropical" class="rounded bg-slate-950 border-slate-700 text-purple-600 focus:ring-purple-500">
                                    <span>Tropical Climate Adaptation (+10-15s/km pace offset)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                                    <input type="checkbox" v-model="vdotForm.use_ai" class="rounded bg-slate-950 border-slate-700 text-purple-600 focus:ring-purple-500">
                                    <span>Enhance descriptions with AI Coach (OpenAI GPT)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3.5 border-t border-slate-700 flex justify-end gap-2">
                        <button type="button" class="px-3.5 py-2 rounded-[6px] bg-slate-800 text-slate-300 border border-slate-700 text-xs font-bold uppercase tracking-wider" @click="showVdotModal = false">Cancel</button>
                        <button type="submit" :disabled="vdotLoading" class="px-5 py-2 rounded-[6px] bg-purple-600 text-white font-bold hover:bg-purple-500 transition text-xs disabled:opacity-50 uppercase tracking-wider">
                            @{{ vdotLoading ? 'Generating...' : 'Generate Program' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Strava AI Analysis (Strava MCP) Modal -->
        <div v-if="showStravaAnalysisModal" class="fixed inset-0 z-[1050] overflow-y-auto">
            <div class="fixed inset-0 bg-black/85 backdrop-blur-sm" @click="showStravaAnalysisModal = false"></div>
            <div class="relative z-10 max-w-2xl mx-auto my-10 bg-slate-900 border border-slate-700 rounded-[6px] p-5 shadow-2xl">
                <div class="flex justify-between items-center mb-6 border-b border-slate-800 pb-4">
                    <h3 class="text-white font-black text-xl flex items-center gap-2">
                        Strava AI Training Analyzer (MCP)
                    </h3>
                    <button @click="showStravaAnalysisModal = false" class="text-slate-400 hover:text-white text-2xl font-bold">✕</button>
                </div>

                <!-- Step 1: Input Form -->
                <div v-if="!stravaAnalysisResult && !stravaAnalysisLoading" class="space-y-6">
                    <p class="text-slate-300 text-sm">
                        Fitur ini menganalisis data latihan Strava Anda secara mendalam — mengklasifikasikan setiap lari (Easy, Tempo, Interval, Long Run), menghitung distribusi intensitas 80/20, mengestimasi VDOT terbaru, dan mengevaluasi keselarasan latihan Anda dengan bantuan AI Coach profesional.
                    </p>

                    <!-- Strava Connection Status -->
                    <div v-if="stravaStatusLoading" class="flex items-center gap-3 p-3 rounded-[6px] bg-slate-900/60 border border-slate-800">
                        <div class="w-4 h-4 border-2 border-slate-500 border-t-purple-400 rounded-full animate-spin"></div>
                        <span class="text-xs text-slate-400">Memeriksa koneksi Strava...</span>
                    </div>
                    <div v-else-if="stravaStatus" class="p-3 rounded-[6px] border" :class="stravaStatus.strava_connected ? 'bg-green-900/10 border-green-800/40' : 'bg-orange-900/10 border-orange-800/40'">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full" :class="stravaStatus.strava_connected ? 'bg-green-400' : 'bg-orange-400'"></div>
                                <span class="text-xs font-bold" :class="stravaStatus.strava_connected ? 'text-green-300' : 'text-orange-300'">
                                    @{{ stravaStatus.strava_connected ? 'Strava Terhubung' : 'Strava Belum Terhubung' }}
                                </span>
                            </div>
                            <div v-if="stravaStatus.strava_connected" class="text-[10px] text-slate-500">
                                @{{ stravaStatus.total_activities }} aktivitas tersimpan
                            </div>
                        </div>
                        <div v-if="!stravaStatus.strava_connected" class="mt-2 flex items-center gap-2">
                            <button type="button" @click="connectStravaFirst" class="px-3 py-1.5 rounded-[4px] bg-[#FC4C02] text-white text-xs font-bold hover:bg-[#E34402] transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-.956l2.62 5.128L13.033.007H6.51L2.5 7.868h4.478"/></svg>
                                Hubungkan Strava
                            </button>
                            <span class="text-[10px] text-slate-500">Diperlukan untuk mengambil data latihan</span>
                        </div>
                        <div v-else-if="stravaStatus.total_activities === 0" class="mt-2 flex items-center gap-2">
                            <button type="button" @click="syncStravaFirst" :disabled="stravaStatusLoading" class="px-3 py-1.5 rounded-[4px] bg-[#FC4C02] text-white text-xs font-bold hover:bg-[#E34402] transition flex items-center gap-1 disabled:opacity-50">
                                <span v-if="stravaStatusLoading" class="animate-spin">⟳</span>
                                Sync Data Strava Sekarang
                            </button>
                            <span class="text-[10px] text-slate-500">Belum ada data. Sync dulu untuk mulai analisis.</span>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Pilih Rentang Waktu Analisis</label>
                        <select v-model="stravaAnalysisRange" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-white text-xs focus:ring-1 focus:ring-neon focus:border-neon focus:outline-none">
                            <option value="7">7 Hari Terakhir</option>
                            <option value="14">14 Hari Terakhir (Direkomendasikan)</option>
                            <option value="30">30 Hari Terakhir</option>
                            <option value="60">60 Hari Terakhir</option>
                            <option value="90">90 Hari Terakhir (Macro Cycle)</option>
                            <option value="custom">Kustom Rentang Tanggal</option>
                        </select>
                    </div>

                    <div v-if="stravaAnalysisRange === 'custom'" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Tanggal Mulai</label>
                            <input type="date" v-model="straCustomStartDate" class="w-full bg-slate-900 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                        </div>
                        <div>
                            <label class="text-[10px] text-slate-400 block mb-1">Tanggal Selesai</label>
                            <input type="date" v-model="straCustomEndDate" class="w-full bg-slate-900 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                        </div>
                    </div>

                    <div class="pt-3.5 border-t border-slate-800 flex justify-end gap-2">
                        <button type="button" class="px-3.5 py-2 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-xs font-bold uppercase tracking-wider" @click="showStravaAnalysisModal = false">Batal</button>
                        <button type="button" @click="runStravaAnalysis" :disabled="stravaStatus && !stravaStatus.strava_connected" class="px-5 py-2.5 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition text-xs flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed uppercase tracking-wider">
                            Mulai Analisis AI
                        </button>
                    </div>
                </div>

                <!-- Step 2: Loading State -->
                <div v-if="stravaAnalysisLoading" class="py-12 flex flex-col items-center justify-center space-y-4">
                    <div class="relative w-16 h-16">
                        <div class="absolute inset-0 rounded-full border-4 border-neon/20 border-t-neon animate-spin"></div>
                        <div class="absolute inset-2 bg-slate-900 rounded-full flex items-center justify-center text-sm"><i class="fa-solid fa-bolt text-neon"></i></div>
                    </div>
                    <p class="text-white font-bold text-sm">AI Coach sedang memproses data latihan Anda...</p>
                    <p class="text-xs text-slate-400 text-center max-w-md">Mengelompokkan lari (Easy, Tempo, Interval, Long Run), mengestimasi VDOT lari Anda, dan mengevaluasi status pemulihan.</p>
                </div>

                <!-- Step 3: Analysis Result View -->
                <div v-if="stravaAnalysisResult && !stravaAnalysisLoading" class="space-y-6">
                    <!-- Stat Summary Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-slate-900/60 p-3 rounded-[6px] border border-slate-800 text-center">
                            <div class="text-[10px] uppercase text-slate-500 tracking-wider">Total Lari</div>
                            <div class="text-lg font-bold text-white mt-1">@{{ stravaAnalysisResult.statistics.total_runs }} sesi</div>
                        </div>
                        <div class="bg-slate-900/60 p-3 rounded-[6px] border border-slate-800 text-center">
                            <div class="text-[10px] uppercase text-slate-500 tracking-wider">Total Jarak</div>
                            <div class="text-lg font-bold text-white mt-1">@{{ stravaAnalysisResult.statistics.total_distance_km }} km</div>
                        </div>
                        <div class="bg-slate-900/60 p-3 rounded-[6px] border border-slate-800 text-center">
                            <div class="text-[10px] uppercase text-slate-500 tracking-wider">Rata-rata Pace</div>
                            <div class="text-lg font-bold text-white mt-1">@{{ stravaAnalysisResult.statistics.avg_pace_str }}/km</div>
                        </div>
                        <div class="bg-slate-900/60 p-3 rounded-[6px] border border-slate-800 text-center relative overflow-hidden group">
                            <div class="absolute top-0 left-0 right-0 h-0.5 bg-neon"></div>
                            <div class="text-[10px] uppercase text-slate-500 tracking-wider">Estimasi VDOT</div>
                            <div class="text-lg font-bold text-neon mt-1">@{{ stravaAnalysisResult.estimated_vdot }}</div>
                        </div>
                    </div>

                    <!-- Classification Breakdown -->
                    <div class="bg-slate-950/80 rounded-[6px] p-3.5 border border-slate-800">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3">Workout Classification Breakdown</h4>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                            <div class="p-2 rounded-[4px] bg-slate-900 border border-slate-800">
                                <div class="text-[10px] text-slate-400 font-bold mb-1">Easy Run</div>
                                <div class="text-base font-bold text-green-400">@{{ stravaAnalysisResult.classification.easy_run_count }}</div>
                            </div>
                            <div class="p-2 rounded-[4px] bg-slate-900 border border-slate-800">
                                <div class="text-[10px] text-slate-400 font-bold mb-1">Tempo / Threshold</div>
                                <div class="text-base font-bold text-yellow-400">@{{ stravaAnalysisResult.classification.tempo_count }}</div>
                            </div>
                            <div class="p-2 rounded-[4px] bg-slate-900 border border-slate-800">
                                <div class="text-[10px] text-slate-400 font-bold mb-1">Interval / Speed</div>
                                <div class="text-base font-bold text-orange-400">@{{ stravaAnalysisResult.classification.interval_count }}</div>
                            </div>
                            <div class="p-2 rounded-[4px] bg-slate-900 border border-slate-800">
                                <div class="text-[10px] text-slate-400 font-bold mb-1">Long Run</div>
                                <div class="text-base font-bold text-blue-400">@{{ stravaAnalysisResult.classification.long_run_count }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Polarized Training Ratio (80/20 Rule) -->
                    <div v-if="stravaAnalysisResult.polarized_ratio" class="bg-slate-950/80 rounded-[6px] p-3.5 border border-slate-800">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Polarized Training Ratio (80/20)</h4>
                            <span class="text-[10px] text-slate-400 font-bold">Target: 80% Easy / 20% Hard</span>
                        </div>
                        <div class="h-3 w-full bg-slate-900 rounded-[4px] overflow-hidden flex border border-slate-800">
                            <div :style="{ width: stravaAnalysisResult.polarized_ratio.easy_pct + '%' }" class="h-full bg-green-500 transition-all duration-500" title="Easy / Long Run Volume"></div>
                            <div :style="{ width: stravaAnalysisResult.polarized_ratio.hard_pct + '%' }" class="h-full bg-orange-500 transition-all duration-500" title="Quality (Tempo / Interval) Volume"></div>
                        </div>
                        <div class="flex justify-between mt-2 text-[10px] font-bold">
                            <span class="text-green-400">@{{ stravaAnalysisResult.polarized_ratio.easy_pct }}% Easy / Long Run</span>
                            <span class="text-orange-400">@{{ stravaAnalysisResult.polarized_ratio.hard_pct }}% Quality</span>
                        </div>
                        <p class="text-[9px] text-slate-500 mt-2">
                            *Sains olahraga menyarankan porsi latihan aerobik intensitas rendah (Easy/Long) sebesar ~80% untuk membangun basis aerobik dan meminimalkan risiko cedera.
                        </p>
                    </div>

                    <!-- AI Coach Insights -->
                    <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4 relative">
                        <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-robot text-neon"></i> Coach AI Insights
                        </h4>
                        <div class="text-slate-300 text-xs leading-relaxed" v-html="parseMarkdown(stravaAnalysisResult.ai_insights)"></div>
                    </div>

                    <!-- Action Steps / CTA -->
                    <div class="pt-4 border-t border-slate-800 space-y-3">
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Rekomendasi Langkah Berikutnya:</div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button @click="applyAnalysisToGenerator" class="flex-1 px-4 py-2.5 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition text-xs flex items-center justify-center gap-2 shadow-lg shadow-neon/15 uppercase tracking-wider">
                                Generate Program Lari (Autofill)
                            </button>
                            <a href="/marketplace" class="flex-1 px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 border border-slate-700 text-center font-bold hover:bg-slate-700 hover:text-white transition text-xs flex items-center justify-center gap-2 uppercase tracking-wider">
                                Hubungi Personal Coach
                            </a>
                        </div>
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <button type="button" @click="stravaAnalysisResult = null" class="text-xs text-slate-400 hover:text-white underline">
                            ← Ulangi Analisis
                        </button>
                        <button type="button" class="text-xs text-slate-400 hover:text-white" @click="showStravaAnalysisModal = false">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Target Modal -->
        <div v-if="showWeeklyTargetModal" class="fixed inset-0 z-[250] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 bg-slate-900 border border-slate-700 rounded-[6px] p-5 shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-base flex items-center gap-2">
                        <span>🎯</span> Update Weekly Target
                    </h3>
                    <button @click="showWeeklyTargetModal = false" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Weekly Target (km)</label>
                        <input type="number" step="0.1" v-model="weeklyTargetForm.weekly_km_target" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                        <p class="text-[10px] text-slate-500 mt-1">Set your weekly running distance goal.</p>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-700">
                        <button type="button" class="px-3.5 py-2 rounded-[6px] bg-slate-800 text-slate-300 border border-slate-700 text-xs hover:text-white font-bold uppercase tracking-wider" @click="showWeeklyTargetModal = false">Cancel</button>
                        <button type="button" @click="updateWeeklyTarget" class="px-5 py-2 rounded-[6px] bg-neon text-dark font-bold text-xs hover:bg-neon/90 shadow-lg shadow-neon/20 flex items-center gap-2 uppercase tracking-wider" :disabled="weeklyTargetLoading">
                            <span v-if="weeklyTargetLoading" class="animate-spin">⟳</span>
                            Save Target
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════════════════════ -->
        <!-- PERFORMANCE IMPROVEMENT INSIGHT MODAL                          -->
        <!-- ═══════════════════════════════════════════════════════════════ -->
        <div v-if="showInsightModal && insightData" class="fixed inset-0 z-[1200] overflow-y-auto flex items-center justify-center p-4" style="backdrop-filter: blur(8px); background: rgba(0,0,0,0.85);">
            <div class="relative w-full max-w-2xl mx-auto">
                <!-- Glow Effect -->
                <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 via-neon/30 to-blue-600 rounded-[6px] blur-lg opacity-50 pointer-events-none"></div>
                <div class="relative bg-slate-900 border border-slate-700 rounded-[6px] overflow-hidden shadow-2xl">

                    <!-- Header -->
                    <div class="relative bg-gradient-to-r from-slate-900 via-purple-900/40 to-slate-900 px-5 py-4 border-b border-slate-700/60">
                        <div class="absolute inset-0 opacity-10 pointer-events-none" style="pointer-events: none; background: radial-gradient(ellipse at top, #a855f7, transparent)"></div>
                        <div class="flex justify-between items-start relative z-10">
                            <div>
                                <div class="flex items-center gap-2 mb-1">                                    
                                    <h3 class="text-white font-bold text-base tracking-tight">
                                        <span v-if="insightType === 'generate'">Program Performance Projection</span>
                                        <span v-else>PB Update — Performance Analysis</span>
                                    </h3>
                                </div>
                                <p class="text-slate-400 text-[10px]">
                                    <span v-if="insightType === 'generate'">Berdasarkan VDOT & Jack Daniels' Running Formula</span>
                                    <span v-else>Perubahan fitness level berdasarkan Personal Best terbaru</span>
                                </p>
                            </div>
                            <button @click="showInsightModal = false; window.location.reload()" class="text-slate-400 hover:text-white w-7 h-7 rounded-[4px] bg-slate-800 flex items-center justify-center transition hover:bg-slate-700">✕</button>
                        </div>
                    </div>

                    <div class="p-5 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">

                        <!-- ── VDOT Comparison Banner ── -->
                        <div class="rounded-[6px] bg-gradient-to-r from-purple-950/40 to-slate-900/60 border border-purple-500/20 p-4 shadow-inner">
                            <div class="grid grid-cols-3 gap-2 items-center">
                                <!-- Current VDOT -->
                                <div class="text-center">
                                    <div class="text-[8px] sm:text-[9px] text-slate-400 uppercase tracking-widest mb-1 leading-tight">
                                        <span v-if="insightType === 'generate'">VDOT Saat Ini</span>
                                        <span v-else>Sebelumnya</span>
                                    </div>
                                    <div class="text-xl sm:text-2xl font-bold text-slate-400 font-mono">
                                        @{{ insightType === 'generate' ? insightData.initial_vdot : insightData.old_vdot }}
                                    </div>
                                </div>

                                <!-- Delta Indicator -->
                                <div class="flex flex-col items-center justify-center">
                                    <div class="px-2 py-0.5 rounded-full text-xs font-bold border font-mono tracking-tight shadow-sm"
                                         :class="insightData.vdot_diff > 0
                                            ? 'bg-green-500/10 text-green-400 border-green-500/30'
                                            : (insightData.vdot_diff < 0
                                                ? 'bg-red-500/10 text-red-400 border-red-500/30'
                                                : 'bg-slate-800 text-slate-400 border-slate-700/60')">
                                        <span v-text="(insightData.vdot_diff > 0 ? '+' : '') + insightData.vdot_diff"></span>
                                    </div>
                                    <div class="text-[8px] sm:text-[9px] text-slate-500 font-medium uppercase tracking-wider mt-1 whitespace-nowrap text-center">VDOT Delta</div>
                                </div>

                                <!-- Target/New VDOT -->
                                <div class="text-center">
                                    <div class="text-[8px] sm:text-[9px] text-neon uppercase tracking-widest mb-1 leading-tight">
                                        <span v-if="insightType === 'generate'">Target VDOT</span>
                                        <span v-else>VDOT Baru</span>
                                    </div>
                                    <div class="text-xl sm:text-2xl font-bold text-neon font-mono">
                                        @{{ insightType === 'generate' ? insightData.target_vdot : insightData.new_vdot }}
                                    </div>
                                    <div class="text-[8px] sm:text-[9px] mt-0.5 font-bold font-mono whitespace-nowrap"
                                         :class="insightData.vdot_diff > 0 ? 'text-green-400' : (insightData.vdot_diff < 0 ? 'text-red-400' : 'text-slate-400')">
                                        @{{ insightData.vdot_diff > 0 ? '+' : '' }}@{{ (insightType === 'generate' ? insightData.vdot_pct : insightData.vdot_pct) || 0 }}% VO2Max
                                    </div>
                                </div>
                            </div>

                            <!-- Duration info for generate type -->
                            <div v-if="insightType === 'generate'" class="mt-3 pt-2.5 border-t border-purple-500/20 text-center">
                                <p class="text-[10px] text-slate-400">
                                    Program <strong class="text-white">@{{ insightData.duration_weeks }} Minggu</strong> untuk
                                    <strong class="text-neon">@{{ insightData.goal_distance?.toUpperCase() }}</strong>
                                    <span v-if="insightData.goal_time_input"> — Goal Time: <strong class="text-white">@{{ insightData.goal_time_input }}</strong></span>
                                </p>
                            </div>
                        </div>

                        <!-- ── Projected Race Time Improvements ── -->
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    <span v-if="insightType === 'generate'">📊 Proyeksi Waktu Lomba (Setelah Program)</span>
                                    <span v-else>📊 Perubahan Equivalent Race Times</span>
                                </span>
                                <div class="h-px bg-slate-700 flex-1"></div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <template v-if="insightType === 'generate'">
                                    <div v-for="(imp, distKey) in insightData.time_improvements" :key="distKey"
                                         class="bg-slate-800/60 border rounded-[6px] p-2.5 relative overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:bg-slate-800"
                                         :class="imp.diff_seconds > 0 
                                            ? 'border-green-500/30 shadow-sm shadow-green-500/5' 
                                            : 'border-slate-700/50'">
                                        <!-- Top indicator line -->
                                        <div class="absolute top-0 left-0 right-0 h-0.5"
                                             :class="imp.diff_seconds > 0 ? 'bg-gradient-to-r from-green-400 to-neon' : 'bg-slate-600'"></div>
                                        
                                        <div class="text-[9px] text-slate-400 uppercase font-bold tracking-wider mb-1">@{{ imp.label }}</div>
                                        
                                        <div class="flex items-center justify-between gap-2">
                                            <div>
                                                <div class="text-[9px] text-slate-500 font-mono line-through">@{{ imp.current_time }}</div>
                                                <div class="text-xs sm:text-sm font-bold text-white font-mono">@{{ imp.projected_time }}</div>
                                            </div>
                                            <div v-if="imp.improvement_pct > 0" class="text-right">
                                                <div class="text-green-400 font-bold text-xs font-mono leading-none">-@{{ imp.improvement_pct }}%</div>
                                                <div class="text-[8px] text-green-500 font-medium mt-0.5 leading-none whitespace-nowrap">lebih cepat</div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div v-for="(imp, distKey) in insightData.time_improvements" :key="distKey"
                                         class="bg-slate-800/60 border rounded-[6px] p-2.5 relative overflow-hidden transition-all duration-300 hover:scale-[1.02] hover:bg-slate-800"
                                         :class="imp.diff_seconds > 0 
                                            ? 'border-green-500/30 shadow-sm shadow-green-500/5' 
                                            : (imp.diff_seconds < 0 
                                                ? 'border-red-500/20' 
                                                : 'border-slate-700/50')">
                                        <!-- Top indicator line -->
                                        <div class="absolute top-0 left-0 right-0 h-0.5"
                                             :class="imp.diff_seconds > 0 
                                                ? 'bg-gradient-to-r from-green-400 to-neon' 
                                                : (imp.diff_seconds < 0 
                                                    ? 'bg-red-500' 
                                                    : 'bg-slate-600')"></div>
                                        
                                        <div class="text-[9px] text-slate-400 uppercase font-bold tracking-wider mb-1">@{{ imp.label }}</div>
                                        
                                        <div class="flex items-center justify-between gap-2">
                                            <div>
                                                <div class="text-[9px] text-slate-500 font-mono line-through">@{{ imp.old_time }}</div>
                                                <div class="text-xs sm:text-sm font-bold font-mono" 
                                                     :class="imp.diff_seconds > 0 
                                                        ? 'text-green-300' 
                                                        : (imp.diff_seconds < 0 
                                                            ? 'text-red-300' 
                                                            : 'text-slate-300')">
                                                    @{{ imp.new_time }}
                                                </div>
                                            </div>
                                            
                                            <div class="text-right">
                                                <div class="font-bold text-xs font-mono leading-none" 
                                                     :class="imp.diff_seconds > 0 
                                                        ? 'text-green-400' 
                                                        : (imp.diff_seconds < 0 
                                                            ? 'text-red-400' 
                                                            : 'text-slate-400')">
                                                    @{{ imp.diff_seconds > 0 ? '-' : (imp.diff_seconds < 0 ? '+' : '') }}@{{ imp.improvement_pct }}%
                                                </div>
                                                <div class="text-[8px] font-medium mt-0.5 leading-none whitespace-nowrap" 
                                                     :class="imp.diff_seconds > 0 
                                                        ? 'text-green-500' 
                                                        : (imp.diff_seconds < 0 
                                                            ? 'text-red-500' 
                                                            : 'text-slate-500')">
                                                    @{{ imp.diff_seconds > 0 ? 'lebih cepat' : (imp.diff_seconds < 0 ? 'lebih lambat' : 'stabil') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- ── Pace Rationale (Why these paces lead to improvement) ── -->
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">⚡ Kenapa Pace Ini Meningkatkan Performamu?</span>
                                <div class="h-px bg-slate-700 flex-1"></div>
                            </div>
                            <div class="space-y-2">
                                <div v-for="(p, idx) in (insightType === 'generate' ? insightData.pace_rationale : insightData.pace_insights)" :key="idx"
                                     class="flex items-start gap-3 p-3 rounded-[6px] bg-slate-800/40 border border-slate-700/50">
                                    <div class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0"
                                         :class="{
                                             'bg-green-400': p.color === 'green',
                                             'bg-yellow-400': p.color === 'yellow',
                                             'bg-orange-400': p.color === 'orange',
                                             'bg-red-400': p.color === 'red'
                                         }"></div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-xs font-bold text-white">@{{ p.type }}</span>
                                            <span v-if="p.pace" class="text-[9px] font-mono bg-slate-700 px-1.5 py-0.5 rounded-[4px] text-neon">@{{ p.pace }}/km</span>
                                            <span class="text-[9px] text-slate-500 ml-auto">@{{ p.contribution }}</span>
                                        </div>
                                        <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">@{{ p.purpose }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ── Runner Level Badge (for PB update) ── -->
                        <div v-if="insightType === 'pb' && insightData.level" class="flex items-center gap-3 p-3 rounded-[6px] bg-slate-800/40 border border-slate-700">
                            <div class="text-2xl">@{{ insightData.level.icon }}</div>
                            <div>
                                <div class="text-[9px] text-slate-400 uppercase tracking-wider">Level Baru Kamu</div>
                                <div class="text-base font-bold text-white">@{{ insightData.level.label }}</div>
                                <div class="text-xs text-slate-400">VDOT @{{ insightData.new_vdot }} (@{{ insightData.change_label }})</div>
                            </div>
                        </div>

                        <!-- ── Key Insight Box ── -->
                        <div class="bg-neon/5 border border-neon/20 rounded-[6px] p-3.5">
                            <div class="flex gap-3">
                                <span class="text-neon text-base flex-shrink-0">💡</span>
                                <div class="text-xs text-slate-300 leading-relaxed">
                                    <span v-if="insightType === 'generate'">
                                        Program ini dirancang agar pace latihanmu secara bertahap <strong class="text-white">meningkatkan VDOT +@{{ insightData.vdot_diff }}</strong> dalam @{{ insightData.duration_weeks }} minggu.
                                        Setiap sesi Easy Run membangun aerobic base, Threshold memperkuat daya tahan kecepatan, dan Interval mendorong VO2Max — ketiganya bekerja sinergis untuk menghasilkan peningkatan performa yang terukur.
                                    </span>
                                    <span v-else>
                                        PB-mu yang baru menunjukkan fitness level yang lebih akurat.
                                        <span v-if="insightData.vdot_diff > 0">VDOT naik <strong class="text-neon">+@{{ insightData.vdot_diff }}</strong> — training paces-mu otomatis disesuaikan agar tetap melatih pada intensitas yang tepat untuk terus berkembang.</span>
                                        <span v-else-if="insightData.vdot_diff < 0">VDOT turun @{{ insightData.vdot_diff }} — training paces disesuaikan ke level yang sesuai kondisi saat ini. Ini wajar dan penting agar kamu tidak overtraining.</span>
                                        <span v-else>VDOT tidak berubah — training paces tetap sama. Pertahankan konsistensi latihan!</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer CTA -->
                    <div class="px-5 py-3 border-t border-slate-700 bg-slate-900/80 flex gap-2 justify-end">
                        <button v-if="insightType === 'generate'"
                                @click="showInsightModal = false; window.location.reload()"
                                class="px-5 py-2 rounded-[6px] bg-neon text-slate-900 font-bold text-xs hover:bg-[#b3e600] transition shadow-lg shadow-neon/20 uppercase tracking-wider">
                            🏃 Mulai Latihan!
                        </button>
                        <button v-else
                                @click="showInsightModal = false"
                                class="px-5 py-2 rounded-[6px] bg-neon text-slate-900 font-bold text-xs hover:bg-[#b3e600] transition shadow-lg shadow-neon/20 uppercase tracking-wider">
                            ✓ Mengerti!
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showFormModal" class="fixed inset-0 z-[1050] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-lg mx-auto my-10 bg-slate-900 border border-slate-700 rounded-[6px] p-5 shadow-2xl">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-white font-bold text-base">Add Workout</h3>
                    <button class="text-slate-400 hover:text-white" @click="closeForm">✕</button>
                </div>
                <form @submit.prevent="saveCustomWorkout" class="space-y-3">
                    <input type="hidden" v-model="form.workout_id">
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Date</label>
                        <input type="date" v-model="form.workout_date" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Type</label>
                        <select v-model="form.type" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                            <option value="run">Run</option>
                            <option value="easy_run">Easy Run</option>
                            <option value="recovery_run">Recovery Run</option>
                            <option value="long_run">Long Run</option>
                            <option value="long_run_quality">Long Run Quality</option>
                            <option value="interval">Interval</option>
                            <option value="repetition">Repetition</option>
                            <option value="threshold">Threshold</option>
                            <option value="tempo">Tempo</option>
                            <option value="progression">Progression</option>
                            <option value="marathon_pace">Marathon Pace</option>
                            <option value="race">Race</option>
                            <option value="strength">Strength</option>
                            <option value="yoga">Yoga</option>
                            <option value="cycling">Cycling</option>
                            <option value="rest">Rest</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Difficulty</label>
                        <select v-model="form.difficulty" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                            <option value="easy">Mudah</option>
                            <option value="moderate">Sedang</option>
                            <option value="hard">Sulit</option>
                        </select>
                    </div>
                    <div v-if="form.type !== 'rest'" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase">Distance (km)</label>
                            <input type="number" step="0.1" v-model="form.distance" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase">Duration</label>
                            <input type="text" v-model="form.duration" placeholder="00:30:00" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                        </div>
                    </div>
                    
                    <!-- Workout Builder -->
                    <div v-if="form.type !== 'rest'" class="border-t border-slate-700 pt-4 mt-4">
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-2">Workout Builder</label>
                        
                        <div class="space-y-2 mb-3">
                            <div v-for="(step, index) in form.workout_structure" :key="index" class="flex flex-col gap-2 p-2.5 bg-slate-800 rounded-[4px] border border-slate-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-[9px] font-bold px-2 py-0.5 rounded-[4px] bg-slate-700" :class="{'text-green-400': step.type==='warmup', 'text-blue-400': step.type==='run', 'text-orange-400': step.type==='interval', 'text-yellow-400': step.type==='recovery', 'text-purple-400': step.type==='cool_down'}">
                                        @{{ activityLabel(step.type) }}
                                    </span>
                                    <div class="flex gap-1">
                                        <button type="button" class="text-slate-400 hover:text-white" @click="moveStep(index, -1)" v-if="index > 0">↑</button>
                                        <button type="button" class="text-slate-400 hover:text-white" @click="moveStep(index, 1)" v-if="index < form.workout_structure.length - 1">↓</button>
                                        <button type="button" class="text-red-400 hover:text-red-300 ml-2" @click="removeStep(index)">✕</button>
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <select v-model="step.duration_type" @change="calculateTotalDistance" class="bg-slate-950 border border-slate-700 rounded-[4px] text-xs text-white px-2 py-1">
                                        <option value="distance">Distance</option>
                                        <option value="time">Time</option>
                                    </select>
                                    <div class="flex gap-1 col-span-2">
                                        <input type="number" step="0.1" v-model="step.value" @change="calculateTotalDistance" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] text-xs text-white px-2 py-1" placeholder="Value">
                                        <select v-model="step.unit" @change="calculateTotalDistance" class="w-20 bg-slate-950 border border-slate-700 rounded-[4px] text-xs text-white px-2 py-1">
                                            <option value="km">km</option>
                                            <option value="m">m</option>
                                            <option value="min">min</option>
                                            <option value="sec">sec</option>
                                        </select>
                                    </div>
                                </div>
                                <input type="text" v-model="step.notes" placeholder="Notes (e.g. @ 5:00 pace)" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] text-xs text-white px-2 py-1">
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="addStep('warmup')" class="px-2 py-1 rounded-[4px] bg-green-500/20 text-green-400 border border-green-500/30 text-[10px] font-bold hover:bg-green-500/30">+ Warmup</button>
                            <button type="button" @click="addStep('run')" class="px-2 py-1 rounded-[4px] bg-blue-500/20 text-blue-400 border border-blue-500/30 text-[10px] font-bold hover:bg-blue-500/30">+ Run</button>
                            <button type="button" @click="addStep('interval')" class="px-2 py-1 rounded-[4px] bg-orange-500/20 text-orange-400 border border-orange-500/30 text-[10px] font-bold hover:bg-orange-500/30">+ Interval</button>
                            <button type="button" @click="addStep('recovery')" class="px-2 py-1 rounded-[4px] bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 text-[10px] font-bold hover:bg-yellow-500/30">+ Recovery</button>
                            <button type="button" @click="addStep('cool_down')" class="px-2 py-1 rounded-[4px] bg-purple-500/20 text-purple-400 border border-purple-500/30 text-[10px] font-bold hover:bg-purple-500/30">+ Cool Down</button>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Description</label>
                        <textarea v-model="form.description" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" class="px-3.5 py-2 rounded-[6px] bg-slate-800 text-slate-300 border border-slate-700 text-xs font-bold uppercase tracking-wider" @click="closeForm">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-[6px] bg-neon text-dark font-bold text-xs uppercase tracking-wider">Save</button>
                    </div>
                </form>
            </div>
        </div>



        <div v-if="showRaceModal" class="fixed inset-0 z-[1050] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80"></div>
            <div class="relative z-10 max-w-lg mx-auto my-10 bg-slate-900 border border-slate-700 rounded-[6px] p-5 shadow-2xl">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-white font-bold text-base flex items-center gap-2">
                        <span class="text-xl">🏆</span> Add Race Event
                    </h3>
                    <button class="text-slate-400 hover:text-white" @click="showRaceModal = false">✕</button>
                </div>
                <form @submit.prevent="saveRace" class="space-y-3">
                    <div class="mb-3 bg-slate-800/50 p-3 rounded-[6px] border border-slate-700 relative">
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1.5">Select Official Event</label>
                        
                        <!-- Search Input -->
                        <div class="relative">
                            <input 
                                type="text" 
                                v-model="eventSearchQuery"
                                @focus="showEventDropdown = true"
                                placeholder="Search events..."
                                class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-yellow-500 focus:outline-none pl-8"
                            >
                            <span class="absolute left-3 top-2 text-slate-500 text-xs">🔍</span>
                            <button v-if="eventSearchQuery" @click="eventSearchQuery = ''; showEventDropdown = false" class="absolute right-3 top-2 text-slate-500 hover:text-white">✕</button>
                        </div>

                        <!-- Dropdown List -->
                        <div v-if="showEventDropdown && filteredEvents.length > 0" 
                            class="absolute left-0 right-0 mt-2 bg-slate-950 border border-slate-700 rounded-[6px] shadow-xl z-50 max-h-60 overflow-y-auto">
                            <ul>
                                <li v-for="event in filteredEvents" :key="event.id"
                                    @click="selectRuangLariEvent(event)"
                                    class="px-3 py-2 hover:bg-slate-800 cursor-pointer border-b border-slate-800 last:border-0"
                                >
                                    <div class="text-xs font-bold text-white">@{{ event.name }}</div>
                                    <div class="text-[10px] text-slate-400 flex justify-between mt-1">
                                        <span>📅 @{{ formatDate(event.start_at) }}</span>
                                        <span>📍 @{{ event.location_name }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="showEventDropdown && filteredEvents.length === 0 && !loadingEvents" class="absolute left-0 right-0 mt-2 bg-slate-950 border border-slate-700 rounded-[6px] p-3 text-center text-slate-500 text-xs z-50">
                            No events found.
                        </div>

                        <div v-if="loadingEvents" class="text-[10px] text-yellow-500 mt-1 italic">Loading events...</div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-yellow-500 uppercase">Race Name</label>
                        <input type="text" v-model="raceForm.name" required placeholder="e.g. Jakarta Marathon 2025" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-yellow-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Date</label>
                        <input type="date" v-model="raceForm.date" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-yellow-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Distance</label>
                        <div class="grid grid-cols-4 gap-2 mb-2">
                            <button type="button" @click="setRaceDist(5, '5K')" class="px-2 py-1 rounded-[4px] bg-slate-800 border border-slate-700 text-xs hover:border-yellow-500 hover:text-yellow-500 transition" :class="raceForm.distLabel==='5K'?'border-yellow-500 text-yellow-500':''">5K</button>
                            <button type="button" @click="setRaceDist(10, '10K')" class="px-2 py-1 rounded-[4px] bg-slate-800 border border-slate-700 text-xs hover:border-yellow-500 hover:text-yellow-500 transition" :class="raceForm.distLabel==='10K'?'border-yellow-500 text-yellow-500':''">10K</button>
                            <button type="button" @click="setRaceDist(21.1, 'HM')" class="px-2 py-1 rounded-[4px] bg-slate-800 border border-slate-700 text-xs hover:border-yellow-500 hover:text-yellow-500 transition" :class="raceForm.distLabel==='HM'?'border-yellow-500 text-yellow-500':''">HM</button>
                            <button type="button" @click="setRaceDist(42.2, 'FM')" class="px-2 py-1 rounded-[4px] bg-slate-800 border border-slate-700 text-xs hover:border-yellow-500 hover:text-yellow-500 transition" :class="raceForm.distLabel==='FM'?'border-yellow-500 text-yellow-500':''">FM</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" v-model="raceForm.distance" placeholder="Custom" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                            <span class="text-slate-400 text-xs">km</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Goal Time (Optional)</label>
                        <input type="text" v-model="raceForm.goal_time" placeholder="hh:mm:ss" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase">Notes / Website</label>
                        <textarea v-model="raceForm.notes" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs"></textarea>
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" class="px-3.5 py-2 rounded-[6px] bg-slate-800 text-slate-300 border border-slate-700 text-xs font-bold uppercase tracking-wider" @click="showRaceModal = false">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-[6px] bg-yellow-500 text-black font-bold text-xs uppercase tracking-wider">Save Race</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reschedule Modal -->
        <div v-if="showRescheduleModal" class="fixed inset-0 z-[250] overflow-y-auto">
            <div class="fixed inset-0 bg-black/80" @click="showRescheduleModal = false"></div>
            <div class="relative z-10 max-w-lg mx-auto my-10 bg-slate-900 border border-slate-700 rounded-[6px] p-5 shadow-2xl">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-white font-bold text-base flex items-center gap-2">
                        Reschedule Program
                    </h3>
                    <button class="text-slate-400 hover:text-white" @click="showRescheduleModal = false">✕</button>
                </div>

                <!-- Tabs selection -->
                <div class="flex border-b border-slate-700 mb-6">
                    <button type="button" 
                            class="flex-1 text-center font-bold text-xs pb-2 transition-all border-b-2"
                            :class="rescheduleTab === 'standard' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                            @click="rescheduleTab = 'standard'">
                        Standard Shift
                    </button>
                    <button type="button" 
                            class="flex-1 text-center font-bold text-xs pb-2 transition-all border-b-2"
                            :class="rescheduleTab === 'adaptive' ? 'text-neon border-neon' : 'text-slate-400 border-transparent hover:text-white'"
                            @click="rescheduleTab = 'adaptive'">
                        Scientific / Adaptive
                    </button>
                </div>

                <!-- Program Info Header -->
                <div class="bg-blue-900/20 border border-blue-800 rounded-[6px] p-3 mb-6">
                    <p class="text-xs text-blue-300">Program: <span class="font-bold text-white">@{{ rescheduleTarget?.program?.title }}</span></p>
                    <p class="text-xs text-blue-300" v-if="rescheduleTarget?.current_vdot">VDOT Saat Ini: <span class="font-bold text-white">@{{ Number(rescheduleTarget?.current_vdot).toFixed(1) }}</span></p>
                </div>

                <!-- STANDARD TAB -->
                <div v-if="rescheduleTab === 'standard'">
                    <p class="text-slate-300 text-xs mb-4">Shift your entire program to a new start date. All future sessions will be moved accordingly.</p>
                    <form @submit.prevent="submitReschedule" class="space-y-4">
                        <div>
                            <label class="text-[10px] font-bold text-blue-400 uppercase">New Start Date</label>
                            <input type="date" v-model="rescheduleForm.new_start_date" required class="w-full bg-slate-950 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        
                        <div class="flex justify-end gap-2 pt-4 border-t border-slate-700">
                            <button type="button" class="px-3.5 py-2 rounded-[6px] bg-slate-800 text-slate-300 border border-slate-700 text-xs font-bold uppercase tracking-wider" @click="showRescheduleModal = false">Cancel</button>
                            <button type="submit" :disabled="rescheduleLoading" class="px-5 py-2 rounded-[6px] bg-blue-500 text-white font-bold text-xs uppercase tracking-wider flex items-center gap-2">
                                <span v-if="rescheduleLoading" class="animate-spin">⌛</span>
                                <span>Shift Calendar</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ADAPTIVE SCIENTIFIC TAB -->
                <div v-if="rescheduleTab === 'adaptive'" class="space-y-4">
                    <p class="text-slate-300 text-xs mb-2">Reschedule adaptif berdasarkan kondisi fisik Anda. Algoritma akan menghitung kurva detraining VDOT dan protokol pemulihan yang aman.</p>
                    
                    <div class="space-y-4">
                        <!-- Alasan Absen -->
                        <div>
                            <label class="text-[10px] font-bold text-blue-400 uppercase">Alasan Absen / Tidak Latihan</label>
                            <select v-model="adaptiveRescheduleForm.reason" class="w-full bg-slate-955 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-blue-500 focus:outline-none">
                                <option value="busy">Kesibukan / Halangan Acara</option>
                                <option value="sick">Sakit / Kurang Fit</option>
                                <option value="injury">Cedera Otot / Sendi</option>
                            </select>
                        </div>

                        <!-- Jumlah Hari Absen -->
                        <div>
                            <label class="text-[10px] font-bold text-blue-400 uppercase">Jumlah Hari Absen Latihan</label>
                            <div class="flex items-center gap-2">
                                <input type="number" v-model.number="adaptiveRescheduleForm.days_missed" min="0" required class="w-full bg-slate-955 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-blue-500 focus:outline-none">
                                <span class="text-slate-400 text-xs">hari</span>
                            </div>
                        </div>

                        <!-- Tanggal Mulai Kembali -->
                        <div>
                            <label class="text-[10px] font-bold text-blue-400 uppercase">Tanggal Aktif Kembali</label>
                            <input type="date" v-model="adaptiveRescheduleForm.start_date" required class="w-full bg-slate-955 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-blue-500 focus:outline-none">
                        </div>

                        <!-- Opsi Cedera tambahan -->
                        <div v-if="adaptiveRescheduleForm.reason === 'injury'" class="p-3 bg-red-950/20 border border-red-900/30 rounded-[6px] space-y-3">
                            <div>
                                <label class="text-[10px] font-bold text-red-400 uppercase">Tingkat Cedera</label>
                                <select v-model="adaptiveRescheduleForm.injury_severity" class="w-full bg-slate-955 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-red-500 focus:outline-none">
                                    <option value="minor">Ringan (Absen < 7 hari, pemulihan 1 minggu)</option>
                                    <option value="moderate">Sedang/Berat (Absen > 7 hari, pemulihan 2 minggu)</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-red-400 uppercase">Bagian Tubuh yang Cedera</label>
                                <input type="text" v-model="adaptiveRescheduleForm.body_part" placeholder="Misal: Knee, Ankle, Shin splints" class="w-full bg-slate-955 border border-slate-700 rounded-[4px] px-3 py-2 text-white text-xs focus:border-red-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Catatan tambahan -->
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase">Catatan</label>
                            <textarea v-model="adaptiveRescheduleForm.notes" rows="2" placeholder="Bagaimana kondisi fisik Anda sekarang?" class="w-full bg-slate-955 border border-slate-700 rounded-[4px] px-3 py-2 text-xs text-white focus:border-blue-500 focus:outline-none"></textarea>
                        </div>

                        <!-- Action Buttons: Preview & Apply -->
                        <div class="pt-4 border-t border-slate-700 flex flex-col gap-2">
                            <button type="button" @click="getAdaptivePreview" :disabled="previewLoading" class="w-full py-2 rounded-[6px] bg-purple-600 text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2">
                                <span v-if="previewLoading" class="animate-spin">⌛</span>
                                <span>Preview Rencana Reschedule</span>
                            </button>
                        </div>
                    </div>

                    <!-- PREVIEW SECTION -->
                    <div v-if="adaptivePreview" class="mt-4 p-4 bg-slate-800/80 border border-purple-500/30 rounded-[6px] space-y-3">
                        <h4 class="text-white font-bold text-xs border-b border-slate-700 pb-2">📋 Hasil Analisis & Jadwal Baru</h4>
                        
                        <div class="grid grid-cols-2 gap-2 text-xs text-slate-300">
                            <div>VDOT Baru: <span class="text-neon font-bold">@{{ Number(adaptivePreview.adjusted_vdot).toFixed(1) }}</span></div>
                            <div v-if="adaptivePreview.adjusted_vdot < (rescheduleTarget?.current_vdot || rescheduleTarget?.program?.vdot)">
                                Penurunan VDOT: <span class="text-red-400 font-bold">-@{{ Number((rescheduleTarget?.current_vdot || rescheduleTarget?.program?.vdot) - adaptivePreview.adjusted_vdot).toFixed(1) }}</span>
                            </div>
                        </div>

                        <p class="text-[9px] text-slate-400 italic">Sistem telah menjadwalkan sesi pemulihan (Easy recovery) dan menyesuaikan beban volume lari agar Anda tidak mengalami cedera (batas ACWR 1.3).</p>

                        <!-- Preview Sesi Latihan Baru -->
                        <div class="max-h-40 overflow-y-auto space-y-1.5 border border-slate-700 rounded-[4px] p-2 bg-slate-955/50">
                            <div v-for="(session, index) in adaptivePreview.sessions" :key="index" class="text-[10px] p-1.5 rounded-[4px] bg-slate-900 flex justify-between items-start">
                                <div>
                                    <div class="text-white font-bold">@{{ formatDate(session.date) }} - Week @{{ session.week }}</div>
                                    <div class="text-slate-400">@{{ session.description }}</div>
                                </div>
                                <div class="text-neon font-bold text-right">
                                    <div>@{{ session.type }}</div>
                                    <div class="text-slate-400 text-[9px]" v-if="session.distance > 0">@{{ session.distance }} km</div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" class="px-3.5 py-2 rounded-[6px] bg-slate-800 text-slate-300 border border-slate-700 text-xs font-bold uppercase tracking-wider" @click="showRescheduleModal = false">Batal</button>
                            <button type="button" @click="submitAdaptiveReschedule" :disabled="rescheduleLoading" class="px-5 py-2 rounded-[6px] bg-neon text-dark font-bold text-xs uppercase tracking-wider flex items-center gap-2">
                                <span v-if="rescheduleLoading" class="animate-spin">⌛</span>
                                <span>Terapkan Reschedule</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Strava Graph Modal -->
        <div v-if="showStravaGraphModal" class="fixed inset-0 z-[1200] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md">
            <div class="w-full max-w-5xl h-[80vh] bg-slate-900 border border-slate-700 rounded-[6px] p-5 relative flex flex-col shadow-2xl shadow-neon/10">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-[#FC4C02] italic uppercase flex items-center gap-2">
                        <svg role="img" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M15.387 17.944l-2.089-4.116h-3.065L15.387 24l5.15-10.172h-3.066m-7.008-5.599l2.836 5.598h4.172L10.463 0l-7 13.828h4.169"/></svg>
                        Strava Analysis
                    </h3>
                    <button @click="showStravaGraphModal = false" class="text-slate-400 hover:text-white bg-slate-800 p-1.5 rounded-[4px] transition">✕</button>
                </div>
                <div class="flex-grow relative bg-slate-900/50 rounded-[6px] border border-slate-800 p-3">
                    <canvas id="stravaMetricsChartFullscreen" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>

        <div class="fixed bottom-4 left-4 right-4 z-[900] md:hidden pointer-events-none">
            <div class="pointer-events-auto bg-slate-900/80 border border-slate-700/70 backdrop-blur-xl rounded-[8px] shadow-2xl px-2 py-1.5">
                <div class="grid grid-cols-4 gap-1.5">
                    <button type="button" class="py-1.5 rounded-[6px] text-[10px] font-bold transition flex flex-col items-center justify-center gap-0.5"
                            :class="activeDock === 'today' ? 'bg-neon text-dark' : 'bg-slate-800 text-slate-200 border border-slate-700'"
                            @click="scrollToSection('today')">
                        <span class="text-xs">●</span>
                        <span>Today</span>
                    </button>
                    <button type="button" class="py-1.5 rounded-[6px] text-[10px] font-bold transition flex flex-col items-center justify-center gap-0.5"
                            :class="activeDock === 'calendar' ? 'bg-neon text-dark' : 'bg-slate-800 text-slate-200 border border-slate-700'"
                            @click="scrollToSection('calendar')">
                        <span class="text-xs">⌁</span>
                        <span>Calendar</span>
                    </button>
                    <button type="button" class="py-1.5 rounded-[6px] bg-neon text-dark text-[10px] font-bold transition flex flex-col items-center justify-center gap-0.5 shadow-lg shadow-neon/20"
                            @click="openMobileAddSheet">
                        <span class="text-xs">＋</span>
                        <span>Add</span>
                    </button>
                    <button type="button" class="py-1.5 rounded-[6px] bg-slate-800 text-slate-200 border border-slate-700 text-[10px] font-bold transition flex flex-col items-center justify-center gap-0.5"
                            @click="showHeaderActions = true">
                        <span class="text-xs">⋯</span>
                        <span>More</span>
                    </button>
                </div>
            </div>
        </div>

    </main>
</div>
