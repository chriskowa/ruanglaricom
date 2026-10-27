<div id="runner-calendar-app" v-cloak>
    <!-- Notification Toast -->
    <transition name="fade">
        <div v-if="notification" class="fixed top-24 right-4 z-[5000] max-w-sm w-full">
            <div :class="notification.type === 'error' ? 'bg-red-500/90 border-red-400 text-white' : 'bg-green-500/90 border-green-400 text-white'" 
                 class="p-4 rounded-2xl border backdrop-blur-md shadow-2xl flex items-start gap-3">
                <i :class="notification.type === 'error' ? 'fa-solid fa-triangle-exclamation text-amber-400' : 'fa-solid fa-circle-check text-green-400'" class="text-sm"></i>
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
                    <a href="/buat-program-lari" class="relative z-[5001] no-underline inline-flex items-center px-2.5 py-1.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 transition text-[11px] font-semibold shadow-sm">Generate VDOT</a>
                    <button type="button" @click="openStravaAnalysisModal" class="relative z-[5001] cursor-pointer px-2.5 py-1.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 hover:bg-slate-700 transition text-[11px] font-semibold shadow-sm flex items-center gap-1.5"><i class="fa-solid fa-bolt text-amber-400"></i> Analisis My Training</button>
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
                        <span><i class="fa-solid fa-trophy text-amber-400 mr-1"></i> Add Race Event</span>
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
                    <a href="/buat-program-lari" class="w-full no-underline px-3 py-2.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs flex items-center justify-between">
                        <span>Buat Program</span>
                        <span class="text-slate-400">›</span>
                    </a>
                    <button type="button" class="w-full px-3 py-2.5 rounded-[4px] bg-slate-800 border border-slate-700 text-slate-200 font-bold text-xs flex items-center justify-between" @click="showHeaderActions = false; openStravaAnalysisModal();">
                        <span>Analisis Strava</span>
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
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="closeApplyModal"></div>
            <div class="relative z-10 max-w-sm w-full rounded-lg p-5 border border-slate-800 shadow-2xl bg-slate-900">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-800">
                    <h3 class="text-white font-bold text-base">
                        Mulai Program
                    </h3>
                    <button class="w-6 h-6 rounded-md bg-slate-800 border border-slate-700 text-slate-400 hover:text-white text-xs flex items-center justify-center transition" @click="closeApplyModal">✕</button>
                </div>

                <!-- State 1: Konfirmasi Ganti Program Aktif -->
                <div v-if="showConfirmReplace" class="space-y-4">
                    <div class="p-3.5 rounded-md bg-amber-500/10 border border-amber-500/30 text-xs">
                        <div class="text-[10px] text-amber-400 font-bold uppercase tracking-wider font-mono mb-1">Konfirmasi Ganti Program</div>
                        <p class="text-slate-200 text-xs leading-relaxed">
                            Saat ini Anda sedang menjalankan program <strong class="text-white">@{{ currentActiveProgram?.program?.title || activeConflictTitle }}</strong>.
                        </p>
                        <p class="text-slate-300 text-xs mt-2 leading-relaxed">
                            Apakah Anda ingin mengganti program aktif dengan <strong class="text-white">@{{ applyTarget?.program?.title }}</strong> mulai tanggal <strong class="text-neon">@{{ applyForm.start_date }}</strong>?
                        </p>
                        <div class="mt-2.5 pt-2 border-t border-amber-500/20 text-[11px] text-slate-400">
                            Program aktif sebelumnya akan dinonaktifkan dan otomatis disimpan kembali ke Program Bag.
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" class="px-3.5 py-2 rounded-md bg-slate-800 text-slate-300 border border-slate-700 text-xs font-semibold hover:text-white transition" @click="showConfirmReplace = false">
                            Kembali
                        </button>
                        <button type="button" @click="submitApply('replace')" :disabled="applyLoading" class="px-4 py-2 rounded-md bg-neon text-slate-950 font-bold text-xs hover:bg-white transition flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span v-if="applyLoading" class="animate-spin text-[10px]">⟳</span>
                            <span>Ya, Ganti Program</span>
                        </button>
                    </div>
                </div>

                <!-- State 2: Form Input Mulai Program -->
                <div v-else>
                    <div class="mb-4 space-y-3">
                        <p class="text-slate-400 text-xs leading-relaxed">Aktifkan program dari Program Bag dengan menentukan tanggal mulai latihan.</p>
                        
                        <div class="bg-slate-950 border border-slate-800 rounded-md p-3" v-if="applyTarget">
                            <div class="text-[10px] text-neon font-bold uppercase tracking-wider font-mono">Program Pilihan</div>
                            <div class="text-xs font-bold text-white mt-0.5">@{{ applyTarget?.program?.title }}</div>
                        </div>

                        <!-- Notifikasi jika sudah ada program aktif berjalan -->
                        <div v-if="currentActiveProgram" class="p-3 rounded-md bg-amber-500/10 border border-amber-500/20 text-xs">
                            <div class="text-[10px] text-amber-400 font-bold uppercase tracking-wider font-mono mb-1">Ada Program Aktif</div>
                            <p class="text-slate-300 text-xs leading-relaxed">
                                Anda sedang menjalankan <strong class="text-white">@{{ currentActiveProgram?.program?.title }}</strong>.
                            </p>
                            <p class="text-slate-400 text-[11px] mt-1 leading-normal">
                                Mengaktifkan program ini akan menggantikan program aktif lama dan memindahkannya kembali ke Program Bag.
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="handleApplySubmit" class="space-y-4">
                        <div>
                            <label class="text-[11px] font-semibold text-slate-300 block mb-1.5">Tanggal Mulai Program</label>
                            <input type="date" v-model="applyForm.start_date" required class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-xs text-white focus:border-neon focus:outline-none transition">
                        </div>

                        <div class="flex justify-end gap-2 pt-3.5 border-t border-slate-800">
                            <button type="button" class="px-4 py-2 rounded-md bg-slate-800 text-slate-300 border border-slate-700 text-xs font-semibold hover:text-white transition" @click="closeApplyModal">Batal</button>
                            <button type="submit" :disabled="applyLoading" class="px-5 py-2 rounded-md bg-neon text-slate-950 font-bold text-xs hover:bg-white transition flex items-center gap-1.5 disabled:opacity-70 disabled:cursor-not-allowed">
                                <span v-if="applyLoading" class="animate-spin text-[10px]">⟳</span>
                                <span v-if="currentActiveProgram">Ganti & Aktifkan</span>
                                <span v-else>Aktifkan Program</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div v-if="showAdaptivePopup"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="adaptive-run-intel-title"
                 aria-describedby="tc-description"
                 tabindex="0"
                 @keydown.escape.prevent.stop="closeAdaptivePopup"
                 class="training-checkin fixed inset-0 z-[9998] flex items-center justify-center p-3 sm:p-4 md:p-6 overflow-hidden">

                <!-- Backdrop Overlay -->
                <div class="tc-backdrop fixed inset-0 bg-black/80 z-[1]"
                     aria-hidden="true"
                     @click="closeAdaptivePopup"></div>

                <!-- Dialog Content -->
                <section class="tc-dialog relative z-[2] w-full max-w-2xl max-h-[90vh] sm:max-h-[86vh] flex flex-col rounded-lg border border-slate-800 shadow-2xl bg-slate-950 text-slate-200 overflow-hidden">
                    <header class="tc-header shrink-0 border-b border-slate-800 px-4 sm:px-5 py-3 bg-slate-900">
                        <div class="tc-heading flex items-start justify-between gap-3">
                            <div>
                                <p class="tc-eyebrow text-[9px] font-bold uppercase tracking-wider text-neon mb-0.5">Catatan mingguan</p>
                                <h2 id="adaptive-run-intel-title" class="text-white font-bold text-sm sm:text-base leading-snug">Bagaimana latihanmu minggu ini?</h2>
                            </div>
                            <button type="button" class="tc-close w-7 h-7 rounded-md bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition flex items-center justify-center text-xs font-bold flex-shrink-0 cursor-pointer focus:outline-none" @click.stop.prevent="closeAdaptivePopup" aria-label="Tutup evaluasi">✕</button>
                        </div>
                        <p id="tc-description" class="tc-muted text-[11px] text-slate-400 mt-1">Isi kondisi terakhir untuk menyesuaikan jadwal berikutnya.</p>
                        <p v-if="adaptiveLoading" class="tc-status text-[11px] text-sky-400 mt-1" role="status">Memuat data latihan…</p>
                        <p v-else-if="adaptiveStatus.recovery_alert?.is_alert" class="tc-alert text-[11px] text-rose-400 mt-1" role="status">Perhatikan pemulihan sebelum menambah latihan.</p>
                        <div v-if="adaptiveStatus.has_active_program" class="tc-program mt-2 pt-2 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-1.5 text-[11px]">
                            <span class="text-white font-semibold">@{{ adaptiveStatus.program_title || 'Program latihan' }}</span>
                            <span class="tc-muted text-slate-400">@{{ adaptiveStatus.training_phase?.label || 'Build' }} · Minggu @{{ adaptiveStatus.training_phase?.week_current || 1 }}/@{{ adaptiveStatus.training_phase?.week_total || 12 }}</span>
                        </div>
                    </header>

                    <div class="tc-body flex-1 overflow-y-auto overscroll-contain p-4 sm:p-5 space-y-4 bg-slate-950" :aria-busy="adaptiveLoading">
                        <div class="tc-fields space-y-3.5">

                            <!-- 01 Sesi yang selesai -->
                            <div class="tc-group space-y-1.5">
                                <div class="tc-legend text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="tc-number text-neon font-mono font-bold">01</span>
                                    <span>Sesi yang selesai</span>
                                </div>
                                <div class="tc-options grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.completion === 'all' ? 'is-selected border-neon bg-slate-900 ring-1 ring-neon text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.completion === 'all'"
                                            @click="checkinForm.completion = 'all'">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-white mb-0.5">
                                            <span>Semua sesi</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.completion === 'all' ? 'border-neon bg-neon text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.completion === 'all'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Sesuai jadwal minggu ini.</span>
                                    </button>

                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.completion === 'partial' ? 'is-selected border-neon bg-slate-900 ring-1 ring-neon text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.completion === 'partial'"
                                            @click="checkinForm.completion = 'partial'">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-white mb-0.5">
                                            <span>Sebagian</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.completion === 'partial' ? 'border-neon bg-neon text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.completion === 'partial'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Ada sesi yang terlewat.</span>
                                    </button>

                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.completion === 'none' ? 'is-selected border-neon bg-slate-900 ring-1 ring-neon text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.completion === 'none'"
                                            @click="checkinForm.completion = 'none'">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-white mb-0.5">
                                            <span>Belum latihan</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.completion === 'none' ? 'border-neon bg-neon text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.completion === 'none'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Tidak ada sesi yang selesai.</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 02 Kondisi tubuh saat ini -->
                            <div class="tc-group space-y-1.5">
                                <div class="tc-legend text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="tc-number text-neon font-mono font-bold">02</span>
                                    <span>Kondisi tubuh saat ini</span>
                                </div>
                                <div class="tc-options grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.feeling === 'strong' ? 'is-selected border-neon bg-slate-900 ring-1 ring-neon text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.feeling === 'strong'"
                                            @click="selectFeeling('strong')">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-white mb-0.5">
                                            <span>Segar</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.feeling === 'strong' ? 'border-neon bg-neon text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.feeling === 'strong'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Tenaga terasa cukup.</span>
                                    </button>

                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.feeling === 'good' ? 'is-selected border-neon bg-slate-900 ring-1 ring-neon text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.feeling === 'good'"
                                            @click="selectFeeling('good')">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-white mb-0.5">
                                            <span>Cukup baik</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.feeling === 'good' ? 'border-neon bg-neon text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.feeling === 'good'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Lelah ringan setelah latihan.</span>
                                    </button>

                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.feeling === 'tired' ? 'is-selected border-rose-500 bg-slate-900 ring-1 ring-rose-500 text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.feeling === 'tired'"
                                            @click="selectFeeling('tired')">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-rose-300 mb-0.5">
                                            <span>Lelah atau pegal</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.feeling === 'tired' ? 'border-rose-500 bg-rose-500 text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.feeling === 'tired'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Butuh lebih banyak istirahat.</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 03 Jarak latihan minggu depan -->
                            <div class="tc-group space-y-1.5">
                                <div class="tc-legend text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="tc-number text-neon font-mono font-bold">03</span>
                                    <span>Jarak latihan minggu depan</span>
                                </div>
                                <div class="tc-options grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.volume_goal === 'increase' ? 'is-selected border-emerald-500 bg-slate-900 ring-1 ring-emerald-500 text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.volume_goal === 'increase'"
                                            @click="checkinForm.volume_goal = 'increase'">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-emerald-300 mb-0.5">
                                            <span>Tambah</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.volume_goal === 'increase' ? 'border-emerald-500 bg-emerald-500 text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.volume_goal === 'increase'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Naik sekitar 5–10%.</span>
                                    </button>

                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.volume_goal === 'maintain' ? 'is-selected border-neon bg-slate-900 ring-1 ring-neon text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.volume_goal === 'maintain'"
                                            @click="checkinForm.volume_goal = 'maintain'">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-white mb-0.5">
                                            <span>Tetap</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.volume_goal === 'maintain' ? 'border-neon bg-neon text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.volume_goal === 'maintain'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Ikuti rencana semula.</span>
                                    </button>

                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.volume_goal === 'decrease' ? 'is-selected border-rose-500 bg-slate-900 ring-1 ring-rose-500 text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.volume_goal === 'decrease'"
                                            @click="checkinForm.volume_goal = 'decrease'">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-rose-300 mb-0.5">
                                            <span>Kurangi</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.volume_goal === 'decrease' ? 'border-rose-500 bg-rose-500 text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.volume_goal === 'decrease'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Turun sekitar 20%.</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 04 Jadwal besok -->
                            <div class="tc-group space-y-1.5">
                                <div class="tc-legend text-xs font-bold text-white uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="tc-number text-neon font-mono font-bold">04</span>
                                    <span>Jadwal besok</span>
                                </div>
                                <div class="tc-options tc-options-two grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.shift_action === 'none' ? 'is-selected border-neon bg-slate-900 ring-1 ring-neon text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.shift_action === 'none'"
                                            @click="checkinForm.shift_action = 'none'">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-white mb-0.5">
                                            <span>Sesuai jadwal</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.shift_action === 'none' ? 'border-neon bg-neon text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.shift_action === 'none'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Lanjutkan rencana besok.</span>
                                    </button>

                                    <button type="button"
                                            class="tc-option p-2.5 rounded-md border text-left transition cursor-pointer flex flex-col justify-between select-none"
                                            :class="checkinForm.shift_action === 'shift_tomorrow' ? 'is-selected border-amber-500 bg-slate-900 ring-1 ring-amber-500 text-white' : 'border-slate-800 bg-slate-900/60 text-slate-300 hover:border-slate-700 hover:bg-slate-900'"
                                            :aria-pressed="checkinForm.shift_action === 'shift_tomorrow'"
                                            @click="checkinForm.shift_action = 'shift_tomorrow'">
                                        <span class="tc-option-title flex items-center justify-between text-xs font-bold text-amber-300 mb-0.5">
                                            <span>Pindah ke lusa</span>
                                            <span class="tc-mark w-3.5 h-3.5 rounded-full border flex items-center justify-center text-[9px]"
                                                  :class="checkinForm.shift_action === 'shift_tomorrow' ? 'border-amber-500 bg-amber-500 text-slate-950 font-bold' : 'border-slate-700'"
                                                  aria-hidden="true">
                                                <span v-if="checkinForm.shift_action === 'shift_tomorrow'">✓</span>
                                            </span>
                                        </span>
                                        <span class="tc-option-note text-[11px] text-slate-400 leading-snug">Istirahat besok, geser satu hari.</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Perubahan jadwal Summary -->
                        <section class="tc-summary rounded-md border p-2.5 sm:p-3 space-y-1.5"
                                 :class="[
                                     checkinForm.volume_goal === 'decrease' ? 'border-rose-500/40 bg-rose-500/5' :
                                     checkinForm.volume_goal === 'increase' ? 'border-emerald-500/40 bg-emerald-500/5' :
                                     'border-cyan-500/30 bg-cyan-500/5'
                                 ]"
                                 aria-labelledby="tc-summary-title" aria-live="polite" aria-atomic="true">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full flex-shrink-0"
                                      :class="[
                                          checkinForm.volume_goal === 'decrease' ? 'bg-rose-400' :
                                          checkinForm.volume_goal === 'increase' ? 'bg-emerald-400' :
                                          'bg-cyan-400'
                                      ]"></span>
                                <h3 id="tc-summary-title" class="text-xs font-bold text-white uppercase tracking-wider">Perubahan jadwal</h3>
                            </div>
                            <p class="text-[11px] text-slate-300 leading-relaxed pl-3.5" v-if="checkinForm.volume_goal === 'decrease'">
                                Jarak minggu depan berkurang sekitar 20%, dengan fokus pada lari ringan.
                            </p>
                            <p class="text-[11px] text-slate-300 leading-relaxed pl-3.5" v-else-if="checkinForm.volume_goal === 'increase'">
                                Jarak minggu depan bertambah sekitar 5–10%.
                            </p>
                            <p class="text-[11px] text-slate-300 leading-relaxed pl-3.5" v-else>
                                Jarak dan intensitas tetap mengikuti rencana semula.
                            </p>
                            <div v-if="checkinForm.shift_action === 'shift_tomorrow'" class="pt-1.5 mt-1.5 border-t border-slate-800/80 flex items-start gap-1.5 pl-3.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 mt-1 flex-shrink-0"></span>
                                <p class="text-[11px] text-amber-200/90 leading-relaxed">
                                    <strong class="font-bold text-amber-300">Proteksi Long Run:</strong> Sesi besok pindah ke lusa. Jadwal long run tetap pada hari semula.
                                </p>
                            </div>
                        </section>

                        <!-- Dasar penyesuaian (Diagnostics accordion) -->
                        <div class="tc-details pt-1">
                            <button type="button" class="tc-details-toggle text-xs text-slate-400 hover:text-slate-200 transition inline-flex items-center gap-1.5 font-medium cursor-pointer focus:outline-none" @click="checkinForm.show_technical = !checkinForm.show_technical" :aria-expanded="!!checkinForm.show_technical" aria-controls="tc-training-details">
                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-90': checkinForm.show_technical }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <span>Dasar penyesuaian</span>
                            </button>
                            <div id="tc-training-details" v-show="checkinForm.show_technical" class="tc-details-body mt-2.5 space-y-2.5 pt-2.5 border-t border-slate-800">
                                <p class="tc-muted text-[11px] text-slate-400 leading-relaxed">Ringkasan dari sesi tercatat, durasi lari, dan RPE:</p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5">
                                    <div class="rounded-lg border border-slate-800 bg-slate-900/60 p-2.5 space-y-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Kesiapan volume</span>
                                        <strong class="text-xs font-bold text-white block">@{{ adaptiveStatus.readiness_3tier?.title || 'Belum tersedia' }}</strong>
                                        <p class="text-[11px] text-slate-300 leading-relaxed">@{{ adaptiveStatus.readiness_3tier?.volume_recommendation || 'Belum cukup data latihan.' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-800 bg-slate-900/60 p-2.5 space-y-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Intensitas</span>
                                        <strong class="text-xs font-bold text-white block">@{{ adaptiveStatus.intensity_decision?.title || 'Belum tersedia' }}</strong>
                                        <p class="text-[11px] text-slate-300 leading-relaxed">@{{ adaptiveStatus.intensity_decision?.description || 'Belum cukup data latihan.' }}</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-800 bg-slate-900/60 p-2.5 space-y-1">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Pemulihan</span>
                                        <strong class="text-xs font-bold text-white block">@{{ adaptiveStatus.recovery_alert?.title || 'Belum tersedia' }}</strong>
                                        <p class="text-[11px] text-slate-300 leading-relaxed">@{{ adaptiveStatus.recovery_alert?.recommendation || 'Belum cukup data pemulihan.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <footer class="tc-footer shrink-0 border-t border-slate-800 px-4 sm:px-5 py-3 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5 rounded-b-lg bg-slate-900">
                        <p v-if="!adaptiveStatus.has_active_program && !adaptiveStatus.active_enrollment_id && !adaptiveLoading" class="tc-muted text-[11px] text-slate-400 max-w-md leading-relaxed">
                            Belum ada program aktif. <a href="{{ route('programs.index') }}" class="text-neon hover:underline font-bold">Pilih program</a>.
                        </p>
                        <p v-else class="tc-muted text-[11px] text-slate-400 max-w-md leading-relaxed">Perubahan diterapkan setelah disimpan.</p>
                        <div class="tc-actions flex items-center gap-2 shrink-0">
                            <button type="button" class="tc-cancel flex-1 sm:flex-none px-3.5 py-1.5 rounded-md bg-slate-800 border border-slate-700 text-slate-200 hover:text-white hover:bg-slate-700 transition text-xs font-semibold uppercase tracking-wider cursor-pointer focus:outline-none" @click.stop.prevent="closeAdaptivePopup">Batal</button>
                            <button type="button" class="tc-save flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-1.5 rounded-md bg-neon text-dark font-bold text-xs uppercase tracking-wider hover:bg-lime-300 transition-all shadow shadow-neon/12 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer focus:outline-none" @click.stop.prevent="applyAdaptiveProgram" :disabled="adaptiveApplying || (!adaptiveStatus.has_active_program && !adaptiveStatus.active_enrollment_id)">
                                <svg v-if="adaptiveApplying" class="w-3.5 h-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span>@{{ adaptiveApplying ? 'Menyimpan…' : 'Simpan perubahan' }}</span>
                            </button>
                        </div>
                    </footer>
                </section>
            </div>
        </transition>

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
                    
                    <span>Jadwal</span>
                </button>
                <button type="button" 
                        class="flex-1 py-1.5 rounded-[4px] text-[11px] font-bold transition-all duration-300 flex items-center justify-center gap-1"
                        :class="activeMobileTab === 'programs' ? 'bg-neon text-dark font-black shadow' : 'bg-[#0d1527] text-slate-400'"
                        @click="activeMobileTab = 'programs'">

                    <span>Program</span>
                </button>
                <button type="button" 
                        class="flex-1 py-1.5 rounded-[4px] text-[11px] font-bold transition-all duration-300 flex items-center justify-center gap-1"
                        :class="activeMobileTab === 'profile' ? 'bg-neon text-dark font-black shadow' : 'bg-[#0d1527] text-slate-400'"
                        @click="activeMobileTab = 'profile'">
                    
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
                <!-- Training Profile / Training Pace Card -->
                <div class="glass-panel rounded-2xl p-4 sm:p-6 relative overflow-hidden" :class="activeMobileTab === 'profile' ? 'block' : 'hidden lg:block'">
                    <div class="relative z-10">
                        <!-- Header & Action Buttons -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-5">
                            <div>
                                <h3 class="text-white font-extrabold text-lg tracking-tight">
                                    Training Profile
                                </h3>
                                <p class="text-xs text-slate-300 mt-0.5">Kalkulasi pace berbasis Personal Best (PB) terkini</p>
                            </div>
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <button type="button" @click.stop="syncTraining" class="text-[11px] bg-slate-800 text-slate-200 px-3 py-1.5 rounded-[4px] border border-slate-700 hover:bg-slate-700 hover:text-white transition font-semibold" :disabled="syncLoading">
                                    <span v-if="syncLoading" class="animate-spin">⟳</span>
                                    <span v-else>Sync</span>
                                </button>
                                <button type="button" @click.stop="openStravaAnalysisModal" class="text-[11px] bg-slate-800 text-slate-200 px-3 py-1.5 rounded-[4px] border border-slate-700 hover:bg-slate-700 hover:text-white transition font-semibold">
                                    Analisis
                                </button>
                                <button type="button" @click.stop="openPbModal" class="text-[11px] bg-neon/15 text-neon border border-neon/30 hover:bg-neon hover:text-dark px-3 py-1.5 rounded-[4px] font-bold transition">
                                    Update PB
                                </button>
                            </div>
                        </div>

                        <!-- VDOT & Weekly Target Metrics (2 Columns on all screens) -->
                        <div class="grid grid-cols-2 gap-2.5 sm:gap-4 mb-5">
                            <!-- VDOT Score Card -->
                            <div class="bg-slate-900/90 rounded-xl p-3 sm:p-4 border border-slate-800 hover:border-slate-700 transition cursor-pointer text-center relative" @click="openVdotModal">
                                <div class="text-[10px] font-mono text-slate-400 uppercase tracking-wider mb-1">VDOT Score</div>
                                <div class="text-2xl sm:text-4xl font-black text-white font-mono tracking-tight">
                                    @{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}
                                </div>
                                <div v-if="trainingProfile.vdot" class="mt-1.5">
                                    <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-[3px] border inline-block"
                                          :class="{
                                              'bg-yellow-500/20 text-yellow-300 border-yellow-500/40': trainingProfile.vdot >= 75,
                                              'bg-purple-500/20 text-purple-300 border-purple-500/40': trainingProfile.vdot >= 60 && trainingProfile.vdot < 75,
                                              'bg-orange-500/20 text-orange-300 border-orange-500/40': trainingProfile.vdot >= 50 && trainingProfile.vdot < 60,
                                              'bg-blue-500/20 text-blue-300 border-blue-500/40': trainingProfile.vdot >= 40 && trainingProfile.vdot < 50,
                                              'bg-emerald-500/20 text-emerald-300 border-emerald-500/40': trainingProfile.vdot < 40
                                          }">
                                        <span v-if="trainingProfile.vdot >= 75">Elite</span>
                                        <span v-else-if="trainingProfile.vdot >= 60">Sub-Elite</span>
                                        <span v-else-if="trainingProfile.vdot >= 50">Advanced</span>
                                        <span v-else-if="trainingProfile.vdot >= 40">Intermediate</span>
                                        <span v-else-if="trainingProfile.vdot >= 30">Beginner+</span>
                                        <span v-else>Beginner</span>
                                    </span>
                                </div>
                                <div class="text-[9px] text-slate-400 mt-1">VO2Max ~@{{ trainingProfile.vdot ? Number(trainingProfile.vdot).toFixed(1) : '-' }}</div>
                            </div>

                            <!-- Weekly Target Card -->
                            <div class="bg-slate-900/90 rounded-xl p-3 sm:p-4 border border-slate-800 hover:border-slate-700 transition cursor-pointer text-center relative" @click="openWeeklyTargetModal">
                                <div class="text-[10px] font-mono text-slate-400 uppercase tracking-wider mb-1">Target Mingguan</div>
                                <div class="text-2xl sm:text-4xl font-black text-white font-mono tracking-tight">
                                    @{{ currentTargetDisplay }}<span class="text-xs font-bold text-brand-lime ml-0.5">km</span>
                                </div>
                                <div class="mt-2 flex items-center justify-center gap-1.5">
                                    <span v-if="isWeeklyTargetAuto" class="text-[9px] px-1.5 py-0.5 rounded bg-brand-lime/10 text-brand-lime font-mono font-bold border border-brand-lime/30">Otomatis Program</span>
                                    <span v-else class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-mono border border-slate-700">Target Kustom</span>
                                </div>
                            </div>
                        </div>

                        <!-- Segmented Sub-Tabs Bar -->
                        <div class="grid grid-cols-3 p-1 bg-slate-950/90 border border-slate-800 rounded-lg mb-5 gap-1">
                            <button 
                                type="button"
                                class="py-2 rounded-[5px] text-xs font-bold transition text-center"
                                :class="profileTab === 'training' ? 'bg-slate-800 text-neon border border-slate-700 shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                                @click="profileTab = 'training'">
                                Paces
                            </button>
                            <button 
                                type="button"
                                class="py-2 rounded-[5px] text-xs font-bold transition text-center"
                                :class="profileTab === 'equivalent' ? 'bg-slate-800 text-neon border border-slate-700 shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                                @click="profileTab = 'equivalent'">
                                Equivalent
                            </button>
                            <button 
                                type="button"
                                class="py-2 rounded-[5px] text-xs font-bold transition text-center"
                                :class="profileTab === 'track' ? 'bg-slate-800 text-neon border border-slate-700 shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                                @click="profileTab = 'track'">
                                Track
                            </button>
                        </div>

                        <!-- 1. Training Paces Tab -->
                        <div v-if="profileTab === 'training'" class="space-y-2">
                            <!-- Easy Pace -->
                            <div class="p-3 sm:p-3.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-white font-bold text-xs sm:text-sm" id="pace-easy-label">Easy (E) Pace</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Recovery, Long Run & Aerobic Base</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-white font-mono font-bold text-sm sm:text-base whitespace-nowrap" id="pace-easy-value">
                                        <template v-if="trainingProfile?.paces?.E_high && trainingProfile?.paces?.E_low">
                                            @{{ formatPace(trainingProfile.paces.E_high) }} - @{{ formatPace(trainingProfile.paces.E_low) }}
                                        </template>
                                        <template v-else>
                                            @{{ formatPace(trainingProfile.paces?.E) }}
                                        </template>
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-mono">per km</div>
                                </div>
                            </div>

                            <!-- Marathon Pace -->
                            <div class="p-3 sm:p-3.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-sky-300 font-bold text-xs sm:text-sm" id="pace-marathon-label">Marathon (M) Pace</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Steady Aerobic & Race Pace Stamina</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-white font-mono font-bold text-sm sm:text-base whitespace-nowrap" id="pace-marathon-value">
                                        @{{ formatPace(trainingProfile.paces?.M) }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-mono">per km</div>
                                </div>
                            </div>

                            <!-- Threshold Pace -->
                            <div class="p-3 sm:p-3.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-amber-300 font-bold text-xs sm:text-sm" id="pace-threshold-label">Threshold (T) Pace</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Tempo & Lactate Clearance Endurance</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-white font-mono font-bold text-sm sm:text-base whitespace-nowrap" id="pace-threshold-value">
                                        @{{ formatPace(trainingProfile.paces?.T) }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-mono">per km</div>
                                </div>
                            </div>

                            <!-- Interval Pace -->
                            <div class="p-3 sm:p-3.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-orange-300 font-bold text-xs sm:text-sm" id="pace-interval-label">Interval (I) Pace</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">VO2Max & Aerobic Power Booster</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-white font-mono font-bold text-sm sm:text-base whitespace-nowrap" id="pace-interval-value">
                                        @{{ formatPace(trainingProfile.paces?.I) }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-mono">per km</div>
                                </div>
                            </div>

                            <!-- Repetition Pace -->
                            <div class="p-3 sm:p-3.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-between gap-3">
                                <div>
                                    <div class="text-rose-300 font-bold text-xs sm:text-sm" id="pace-repetition-label">Repetition (R) Pace</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Speed, Running Economy & Mechanics</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-white font-mono font-bold text-sm sm:text-base whitespace-nowrap" id="pace-repetition-value">
                                        @{{ formatPace(trainingProfile.paces?.R) }}
                                    </div>
                                    <div class="text-[9px] text-slate-400 font-mono">per km</div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Equivalent Race Predictions Tab -->
                        <div v-if="profileTab === 'equivalent'">
                            <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                                <!-- 5K -->
                                <div class="p-3 sm:p-4 rounded-lg bg-slate-950/70 border border-slate-800">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-mono font-bold text-neon bg-neon/10 border border-neon/30 px-1.5 py-0.5 rounded-[3px]">5K</span>
                                        <span class="text-[9px] font-mono text-slate-400">Pace</span>
                                    </div>
                                    <div class="text-base sm:text-xl font-mono font-black text-white mt-1" id="equiv-5k">
                                        @{{ trainingProfile.equivalent_race_times?.['5k']?.time || '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-300 font-mono mt-0.5">
                                        @{{ trainingProfile.equivalent_race_times?.['5k']?.pace || '-' }}
                                    </div>
                                </div>

                                <!-- 10K -->
                                <div class="p-3 sm:p-4 rounded-lg bg-slate-950/70 border border-slate-800">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-mono font-bold text-neon bg-neon/10 border border-neon/30 px-1.5 py-0.5 rounded-[3px]">10K</span>
                                        <span class="text-[9px] font-mono text-slate-400">Pace</span>
                                    </div>
                                    <div class="text-base sm:text-xl font-mono font-black text-white mt-1" id="equiv-10k">
                                        @{{ trainingProfile.equivalent_race_times?.['10k']?.time || '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-300 font-mono mt-0.5">
                                        @{{ trainingProfile.equivalent_race_times?.['10k']?.pace || '-' }}
                                    </div>
                                </div>

                                <!-- Half Marathon -->
                                <div class="p-3 sm:p-4 rounded-lg bg-slate-950/70 border border-slate-800">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-mono font-bold text-sky-400 bg-sky-500/10 border border-sky-500/30 px-1.5 py-0.5 rounded-[3px]">21.1K (HM)</span>
                                        <span class="text-[9px] font-mono text-slate-400">Pace</span>
                                    </div>
                                    <div class="text-base sm:text-xl font-mono font-black text-white mt-1" id="equiv-hm">
                                        @{{ trainingProfile.equivalent_race_times?.['21k']?.time || '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-300 font-mono mt-0.5">
                                        @{{ trainingProfile.equivalent_race_times?.['21k']?.pace || '-' }}
                                    </div>
                                </div>

                                <!-- Full Marathon -->
                                <div class="p-3 sm:p-4 rounded-lg bg-slate-950/70 border border-slate-800">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px] font-mono font-bold text-amber-400 bg-amber-500/10 border border-amber-500/30 px-1.5 py-0.5 rounded-[3px]">42.2K (FM)</span>
                                        <span class="text-[9px] font-mono text-slate-400">Pace</span>
                                    </div>
                                    <div class="text-base sm:text-xl font-mono font-black text-white mt-1" id="equiv-fm">
                                        @{{ trainingProfile.equivalent_race_times?.['42k']?.time || '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-300 font-mono mt-0.5">
                                        @{{ trainingProfile.equivalent_race_times?.['42k']?.pace || '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Track Intervals Tab -->
                        <div v-if="profileTab === 'track'">
                            <div class="overflow-x-auto rounded-lg border border-slate-800 bg-slate-950/70">
                                <table class="w-full text-xs text-left">
                                    <thead>
                                        <tr class="text-[10px] font-mono uppercase bg-slate-900 border-b border-slate-800">
                                            <th class="py-2.5 px-3 text-slate-300 font-bold">Jarak</th>
                                            <th class="py-2.5 px-2 text-right text-rose-400 font-bold">Rep (R)</th>
                                            <th class="py-2.5 px-2 text-right text-orange-400 font-bold">Int (I)</th>
                                            <th class="py-2.5 px-3 text-right text-amber-400 font-bold">Thr (T)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-800/60">
                                        <tr v-for="(times, dist) in trainingProfile.track_times" :key="dist" class="hover:bg-slate-900/40 transition">
                                            <td class="py-2.5 px-3 font-bold text-white font-mono">@{{ dist }}</td>
                                            <td class="py-2.5 px-2 text-right font-mono">
                                                <div class="text-white font-bold">@{{ times.R }}</div>
                                                <div class="text-[9px] text-slate-400">@{{ times.pace_R }}/km</div>
                                            </td>
                                            <td class="py-2.5 px-2 text-right font-mono">
                                                <div class="text-white font-bold">@{{ times.I }}</div>
                                                <div class="text-[9px] text-slate-400">@{{ times.pace_I }}/km</div>
                                            </td>
                                            <td class="py-2.5 px-3 text-right font-mono">
                                                <div class="text-white font-bold">@{{ times.T }}</div>
                                                <div class="text-[9px] text-slate-400">@{{ times.pace_T }}/km</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buka Akses Lanjutan Program Lari Banner -->
                <div v-if="hasUnpaidGenerator" class="mb-5" :class="activeMobileTab === 'calendar' ? 'block' : 'hidden lg:block'">
                    <!-- Minimized Bar when dismissed -->
                    <div v-if="isUnlockBannerDismissed" class="p-3.5 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-between gap-3 text-xs shadow-sm">
                        <div class="text-slate-300 truncate">
                            Jadwal aktif di kalender: Minggu 1 s/d @{{ firstLockedWeek - 1 }}. Minggu @{{ firstLockedWeek }}+ belum terbuka.
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="isUnlockBannerDismissed = false" class="px-3 py-1.5 rounded-md bg-neon hover:bg-white text-slate-950 font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                                Buka Akses
                            </button>
                        </div>
                    </div>

                    <!-- Full Structured Card -->
                    <div v-else class="rounded-lg bg-slate-900 border border-slate-800 p-5 shadow-sm space-y-4">
                        <!-- Header with Title & Dismiss Button -->
                        <div class="flex items-start justify-between gap-4 pb-3 border-b border-slate-800">
                            <div>
                                <h3 class="text-base sm:text-lg font-bold text-white tracking-tight" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                                    Buka Fase Lanjutan Program Lari
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Jadwal aktif di kalender saat ini mencakup Minggu 1 s/d @{{ firstLockedWeek - 1 }}.
                                </p>
                            </div>
                            <button type="button" 
                                    @click="isUnlockBannerDismissed = true" 
                                    class="text-xs text-slate-400 hover:text-white transition px-2 py-1 rounded hover:bg-slate-800 shrink-0 cursor-pointer"
                                    title="Sembunyikan banner sementara">
                                Tutup ✕
                            </button>
                        </div>

                        <!-- Main Content Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
                            <!-- Left Info Column -->
                            <div class="lg:col-span-6 space-y-3">
                                <p class="text-xs text-slate-300 leading-relaxed">
                                    Untuk membuka seluruh sesi latihan fase puncak (Minggu @{{ firstLockedWeek }} sampai selesai), berikan kontribusi sukarela pengembangan platform atau masukkan nomor HP/kode yang terdaftar pada event lari.
                                </p>
                                <div class="p-3.5 rounded-md bg-slate-950 border border-slate-800 text-xs text-slate-300 space-y-2">
                                    <div class="text-[10px] font-mono text-slate-400 uppercase tracking-wider font-semibold">Cakupan Akses Penuh:</div>
                                    <div class="space-y-1.5 text-slate-300">
                                        <div>• Seluruh sesi latihan fase Peak & Taper tampil lengkap di kalender</div>
                                        <div>• Panduan target pace personal berbasis VDOT Jack Daniels</div>
                                        <div>• Sinkronisasi otomatis aktivitas Strava ke jadwal latihan</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Donation / Promo Controls Column -->
                            <div class="lg:col-span-6 p-4 rounded-md bg-slate-950 border border-slate-800 space-y-3.5">
                                <!-- Preset Nominal Chips -->
                                <div>
                                    <div class="flex items-center justify-between mb-1.5 text-xs">
                                        <span class="font-semibold text-slate-300">Dukungan / Kontribusi Sukarela</span>
                                        <span class="font-mono text-white font-bold text-sm">Rp @{{ (parseInt(donationAmount) || 25000).toLocaleString('id-ID') }}</span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 mb-2.5">
                                        <button type="button" 
                                                @click="donationAmount = 15000" 
                                                :class="donationAmount == 15000 ? 'bg-neon text-slate-950 font-bold border-neon' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700'"
                                                class="py-1.5 px-2 rounded-md border text-xs text-center transition cursor-pointer">
                                            Rp 15.000
                                        </button>
                                        <button type="button" 
                                                @click="donationAmount = 25000" 
                                                :class="donationAmount == 25000 ? 'bg-neon text-slate-950 font-bold border-neon' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700'"
                                                class="py-1.5 px-2 rounded-md border text-xs text-center transition cursor-pointer">
                                            Rp 25.000 <span class="text-[9px] block opacity-80">Rekomendasi</span>
                                        </button>
                                        <button type="button" 
                                                @click="donationAmount = 50000" 
                                                :class="donationAmount == 50000 ? 'bg-neon text-slate-950 font-bold border-neon' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700'"
                                                class="py-1.5 px-2 rounded-md border text-xs text-center transition cursor-pointer">
                                            Rp 50.000
                                        </button>
                                    </div>
                                    <input v-model.number="donationAmount" type="range" min="10000" max="250000" step="5000" 
                                           class="w-full h-1.5 bg-slate-800 rounded appearance-none cursor-pointer accent-[#B8FF00]">
                                    <div class="flex justify-between text-[10px] text-slate-500 font-mono mt-1">
                                        <span>Min Rp 10.000</span>
                                        <span>Max Rp 250.000</span>
                                    </div>
                                </div>

                                <!-- Promo Code (No HP Event) -->
                                <div class="pt-2 border-t border-slate-850">
                                    <label class="block text-[11px] font-semibold text-slate-400 mb-1">
                                        Punya Kode Promo atau Terdaftar di Event?
                                    </label>
                                    <div class="flex gap-2">
                                        <input v-model="promoCode" type="text" placeholder="Masukkan Kode / No HP Event" 
                                               class="flex-1 bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 font-mono"
                                               :class="{'border-slate-600': promoApplied, 'border-rose-500': promoError}">
                                        <button v-if="!promoApplied" 
                                                type="button"
                                                @click="applyPromo" 
                                                :disabled="checkingPromo || !promoCode"
                                                class="px-3.5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition cursor-pointer disabled:opacity-50">
                                            @{{ checkingPromo ? 'Cek...' : 'Terapkan' }}
                                        </button>
                                    </div>
                                    <p v-if="promoError" class="text-[11px] text-rose-400 mt-1">@{{ promoError }}</p>
                                    <p v-if="promoApplied" class="text-[11px] text-slate-200 mt-1 font-semibold">Kode promo aktif. Akses fase lanjutan dapat dibuka gratis.</p>
                                </div>

                                <!-- Action Button -->
                                <button type="button"
                                        @click="handleUnlockAction" 
                                        :disabled="donationLoading || checkingPromo" 
                                        class="w-full py-2.5 px-4 rounded-md bg-neon hover:bg-white text-slate-950 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50">
                                    <span v-if="!donationLoading">
                                        @{{ promoApplied ? 'Buka Akses Penuh Sekarang (Gratis)' : 'Buka Akses Penuh (Rp ' + (parseInt(donationAmount) || 25000).toLocaleString('id-ID') + ')' }}
                                    </span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses Transaksi...
                                    </span>
                                </button>
                                <div class="text-center text-[10px] text-slate-500 font-mono">
                                    Didukung Midtrans Payment Gateway (QRIS, GoPay, Bank Transfer)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="glass-panel rounded-lg p-4 md:p-6" id="runner-calendar-section" :class="activeMobileTab === 'calendar' ? 'block' : 'hidden lg:block'">
                    <div class="flex flex-wrap sm:flex-nowrap items-center justify-between gap-2.5 mb-4 border-b border-slate-850 pb-3">
                        <div class="flex items-center gap-2 shrink-0">                            
                            <span class="text-xs font-bold text-slate-350 uppercase tracking-widest font-mono">Program Aktif</span>
                        </div>
                        <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                            <!-- ========================================================= -->
                            <!-- ADAPTIVE RUN INTELLIGENCE - FLOATING MINI BADGE PERSISTENT -->
                            <!-- Selalu tampil: rose highlight jika recovery alert ON, default accent phase jika stabil -->
                            <!-- ========================================================= -->
                            <button
                                type="button"
                                v-if="adaptiveStatus.has_active_program || adaptiveStatus._raw"
                                @click.stop.prevent="openAdaptivePopup(true)"
                                class="group relative inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-md text-xs font-semibold transition border shadow-sm shrink-0 cursor-pointer focus:outline-none focus:ring-1 focus:ring-neon"
                                :class="[
                                    adaptiveStatus.recovery_alert?.is_alert
                                        ? 'border-rose-500/50 bg-rose-500/10 text-rose-300 hover:bg-rose-500/20'
                                        : 'border-slate-700 bg-slate-800 text-slate-200 hover:text-white hover:bg-slate-700 hover:border-slate-600'
                                ]"
                                title="Evaluasi & sesuaikan beban latihan Anda"
                            >
                                <span class="inline-block w-2 h-2 rounded-full shrink-0"
                                      :class="adaptiveStatus.recovery_alert?.is_alert ? 'bg-rose-400' : 'bg-neon'"></span>
                                <span>Evaluasi</span>
                                <span v-if="adaptiveStatus.training_phase?.label" class="hidden sm:inline text-[10px] text-slate-400 font-mono font-normal">(@{{ adaptiveStatus.training_phase.label }})</span>
                            </button>

                            <button @click="exportCalendar('image')" 
                                    :disabled="isExporting"
                                    :class="{'opacity-50 cursor-not-allowed': isExporting}"
                                    class="px-2.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-semibold transition inline-flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer"
                                    title="Unduh Kalender dalam format gambar JPG">
                                <svg v-if="isExporting && exportType === 'image'" class="animate-spin h-3.5 w-3.5 text-neon shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span class="font-mono text-[11px]">JPG</span>
                            </button>

                            <button @click="exportCalendar('pdf')" 
                                    :disabled="isExporting"
                                    :class="{'opacity-50 cursor-not-allowed': isExporting}"
                                    class="px-2.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-semibold transition inline-flex items-center gap-1.5 shadow-sm shrink-0 cursor-pointer"
                                    title="Unduh Kalender dalam format dokumen PDF">
                                <svg v-if="isExporting && exportType === 'pdf'" class="animate-spin h-3.5 w-3.5 text-neon shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="font-mono text-[11px]">PDF</span>
                            </button>
                        </div>
                    </div>

                    <!-- Floating Export Progress Indicator -->
                    <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-2">
                        <div v-if="isExporting" class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-lg bg-slate-900 border border-slate-700 shadow-2xl text-white text-xs font-semibold backdrop-blur-md">
                            <svg class="animate-spin h-4 w-4 text-neon shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <div class="flex flex-col">
                                <span class="font-bold text-white text-[11px] uppercase tracking-wider">Mengekspor Kalender Latihan</span>
                                <span class="text-xs text-slate-300 font-mono">@{{ exportProgressText || 'Sedang memproses...' }}</span>
                            </div>
                        </div>
                    </transition>

                    <!-- Telemetry Progress Bar Pekan Ini -->
                    <div v-if="currentWeekSummary && currentWeekSummary.plannedKm > 0" 
                         class="mb-4 p-3 sm:p-3.5 rounded-lg bg-slate-900 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-md bg-slate-950 border border-slate-800 flex flex-col items-center justify-center text-center shrink-0">
                                <span class="text-[8px] font-mono text-slate-400 uppercase tracking-widest leading-none">PEKAN</span>
                                <span class="text-xs font-black text-brand-lime font-mono leading-tight mt-0.5">@{{ currentWeekSummary.weekNumber || 'W' }}</span>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white flex items-center gap-2">
                                    <span>Ringkasan Pekan Berjalan</span>
                                    <span class="text-[10px] text-slate-400 font-mono font-normal">(@{{ currentWeekSummary.dateRange }})</span>
                                </div>
                                <div class="text-[11px] text-slate-300 mt-0.5 flex flex-wrap items-center gap-2 sm:gap-3">
                                    <span>Rencana: <strong class="text-white font-mono">@{{ currentWeekSummary.plannedKm.toFixed(1) }} km</strong> <span class="text-slate-400 text-[10px]">(@{{ currentWeekSummary.sessionCount }} sesi)</span></span>
                                    <span class="text-slate-700 hidden sm:inline">•</span>
                                    <span>Selesai: <strong class="text-brand-lime font-mono">@{{ currentWeekSummary.completedKm.toFixed(1) }} km</strong></span>
                                    <span class="text-slate-700 hidden sm:inline">•</span>
                                    <span>Sisa: <strong class="text-slate-200 font-mono">@{{ currentWeekSummary.remainingKm.toFixed(1) }} km</strong></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-56 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-800">
                            <div class="flex-1">
                                <div class="flex justify-between items-center text-[10px] font-mono mb-1">
                                    <span class="text-slate-400">Target Tercapai</span>
                                    <span class="font-bold text-brand-lime">@{{ currentWeekSummary.percent }}%</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                                    <div class="h-full bg-brand-lime transition-all duration-500 rounded-full" :style="{ width: Math.min(100, currentWeekSummary.percent) + '%' }"></div>
                                </div>
                            </div>
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
                                <div class="flex items-center gap-1.5">
                                    <button v-if="!plan.is_locked && ((plan.source === 'custom' || plan.workout_id) || (plan.enrollment_id && plan.session_day))"
                                            class="text-[10px] text-slate-500 hover:text-red-400 p-0.5 rounded transition cursor-pointer"
                                            title="Hapus workout ini"
                                            @click.stop="deleteWorkout(plan)">
                                        ✕
                                    </button>
                                    <span v-if="plan.is_locked" class="text-[9px] font-mono text-slate-500 font-medium uppercase">Terkunci</span>
                                    <span v-else class="text-[8px] font-bold px-1.5 py-0.5 rounded-[4px] border uppercase" :class="[plan.status === 'completed' || plan.status === 'imported' ? 'bg-green-500/10 text-green-400 border-green-500/20' : (plan.status === 'started' ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20')]">
                                        @{{ statusText(plan.status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body (Activity Content) -->
                            <div class="space-y-1">
                                <h4 class="font-bold text-xs md:text-sm leading-tight transition line-clamp-1 truncate cursor-pointer" 
                                    :class="plan.is_locked ? 'text-slate-300 hover:text-white' : 'text-white hover:text-neon'"
                                    :title="plan.description ? plan.description.split('\n')[0] : (plan.program_title || 'Workout Session')"
                                    @click="showPlanDetail(plan)">
                                    @{{ plan.description ? truncatePlanDesc(plan.description, 45) : (plan.program_title || 'Workout Session') }}
                                </h4>

                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-bold tracking-wider border"
                                          :class="plan.is_locked ? 'bg-slate-900 text-slate-300 border-slate-800' : 'bg-slate-700 text-slate-300 border-slate-600'">
                                        @{{ plan.type === 'custom_workout' ? activityLabel(plan.activity_type || 'Custom') : activityLabel(plan.type || 'Workout') }}
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400">@{{ plan.distance ? plan.distance + ' km' : (plan.duration || '-') }}</span>
                                </div>
                            </div>

                            <!-- Card Footer Button -->
                            <button v-if="plan.is_locked" class="w-full mt-1.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-semibold border border-slate-700 flex items-center justify-center transition cursor-pointer" @click.stop="showPlanDetail(plan)">
                                Buka Akses Sesi
                            </button>
                            <div v-else-if="plan.status==='pending'" class="grid grid-cols-2 gap-1 mt-1">
                                <button class="py-1 rounded-[4px] bg-slate-800 text-slate-300 text-[9px] font-bold hover:bg-slate-700 transition border border-slate-700 flex items-center justify-center" @click.stop="updateSessionStatus(plan,'started')">
                                    START
                                </button>
                                <button class="py-1 rounded-[4px] bg-neon text-dark text-[9px] font-black hover:bg-white transition flex items-center justify-center shadow-sm" @click.stop="updateSessionStatus(plan,'completed')">
                                    FINISH
                                </button>
                            </div>
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

        <div v-if="showDetailModal" class="fixed inset-0 z-[1050] overflow-y-auto flex items-center justify-center p-3 sm:p-4 md:p-6">
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="closeDetail"></div>
            <!-- Dynamic Modal Width & Bounded Height for Smooth Internal Scrolling -->
            <div class="relative z-10 w-full rounded-lg p-0 border border-slate-800 overflow-hidden transition-all duration-300 my-auto shadow-2xl bg-slate-900 flex flex-col min-h-0"
                 :style="detail.type === 'strength' ? 'max-width: 680px; width: 100%; max-height: 88vh; height: 88vh;' : ((detail.session?.is_locked || detail.is_locked) ? 'max-width: 720px; width: 100%; max-height: 90vh; height: auto;' : 'max-width: min(94vw, 1200px); width: 100%; max-height: 90vh; height: 90vh;')">
                
                <!-- STRENGTH TRAINING UI -->
                <div v-if="detail.type === 'strength'" class="flex flex-col flex-1 min-h-0 overflow-hidden">
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
                    <div class="flex-1 min-h-0 overflow-y-auto p-4 space-y-4 custom-scrollbar">
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
                                <i class="fa-solid fa-clipboard-list text-neon mr-1"></i> Workout Plan
                            </h3>
                            <div class="space-y-2">
                                <div v-for="(exercise, idx) in parseStrengthExercises(detail)" :key="idx" 
                                     class="group flex items-center gap-3 p-2.5 rounded-[6px] bg-slate-800/40 border border-slate-700 hover:bg-slate-800 transition cursor-pointer"
                                     @click="previewExercise(exercise)">
                                    <!-- Thumbnail Placeholder / Media Preview -->
                                    <div class="w-12 h-12 rounded-[4px] bg-slate-900 border border-slate-700 flex-shrink-0 overflow-hidden relative">
                                        <template v-if="exercise.media_url">
                                            <video v-if="exercise.media_type === 'video'" :src="exercise.media_url" autoplay loop muted class="w-full h-full object-cover"></video>
                                            <img v-else :src="exercise.media_url" class="w-full h-full object-cover" :alt="exercise.name">
                                        </template>
                                        <div v-else class="absolute inset-0 flex items-center justify-center text-xl group-hover:scale-110 transition" v-html="getExerciseIcon(exercise.name)">
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow">
                                        <div class="text-xs font-bold text-white">@{{ exercise.name }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">@{{ exercise.sets }} Sets • @{{ exercise.reps }} Reps</div>
                                        <div v-if="exercise.notes" class="text-[9px] text-slate-500 italic mt-0.5">"@{{ exercise.notes }}"</div>
                                    </div>

                                    <div class="w-6 h-6 rounded-[4px] border border-slate-600 flex items-center justify-center text-slate-400 text-xs group-hover:border-brand-500 group-hover:text-brand-400 transition">
                                        <i class="fa-solid fa-play text-[10px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Equipment -->
                        <div v-if="detail.strength?.equipment || detail.description?.includes('Equipment')" class="text-[11px] text-slate-400 bg-slate-900/50 p-2.5 rounded-[6px] border border-slate-800">
                            <span class="font-bold text-slate-300 font-mono">EQUIPMENT:</span> @{{ detail.strength?.equipment || 'Dumbbells, Mat' }}
                        </div>

                        <div v-if="showGuidedPlayer" class="rounded-[6px] border border-brand-500/20 bg-slate-900/90 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-[9px] font-bold uppercase tracking-widest text-brand-400">Guided Workout</div>
                                    <div class="text-xs font-bold text-white truncate">
                                        @{{ currentExercise ? currentExercise.name : 'Workout' }}
                                    </div>
                                </div>
                                <button type="button" class="px-2.5 py-1.5 rounded-[4px] bg-slate-900 border border-slate-700 text-slate-200 text-[10px] font-bold hover:border-brand-500/40 transition" @click="exitGuidedWorkout">Exit</button>
                            </div>

                            <!-- Media Display Box -->
                            <div v-if="currentExercise && (currentExercise.media_url || currentExercise.instructions)" class="my-2.5 p-2 rounded-lg bg-slate-950/80 border border-slate-800 space-y-2">
                                <div v-if="currentExercise.media_url" class="w-full h-40 rounded-md overflow-hidden bg-black flex items-center justify-center border border-slate-800">
                                    <video v-if="currentExercise.media_type === 'video'" :src="currentExercise.media_url" autoplay loop muted class="w-full h-full object-contain"></video>
                                    <img v-else :src="currentExercise.media_url" class="w-full h-full object-contain" :alt="currentExercise.name">
                                </div>
                                <div v-if="currentExercise.instructions" class="text-[10px] text-slate-300 leading-relaxed bg-slate-900/60 p-2 rounded border border-slate-800">
                                    <span class="font-bold text-brand-400">Panduan Form:</span> @{{ currentExercise.instructions }}
                                </div>
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
                        <button v-if="detail.status === 'completed'" type="button" disabled class="w-full py-2.5 rounded-md bg-slate-800/60 text-slate-500 font-bold text-xs uppercase tracking-wider border border-slate-700">
                            Completed
                        </button>
                        <div v-else class="grid grid-cols-2 gap-2">
                            <button type="button" class="w-full py-2.5 rounded-md bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider border border-slate-700 transition flex items-center justify-center"
                                    @click="startGuidedWorkout(detail)">
                                <span>@{{ detail.status === 'started' ? 'Resume Workout' : 'Start Guided' }}</span>
                            </button>
                            <button type="button" class="w-full py-2.5 rounded-md bg-neon hover:bg-white text-dark font-black text-xs uppercase tracking-wider shadow-sm transition flex items-center justify-center"
                                    @click="finishGuidedWorkout">
                                <span>Finish Activity</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- STANDARD RUNNING/OTHER UI (Existing) -->
                <div v-else class="flex flex-col flex-1 min-h-0 overflow-hidden">
                    <!-- Locked Session UI -->
                    <div v-if="detail.session?.is_locked || detail.is_locked" class="flex flex-col flex-1 min-h-0 overflow-hidden">
                        <!-- Pinned Modal Header Bar -->
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-800 bg-slate-900 shrink-0">
                            <div class="flex items-center gap-2 text-xs">
                                <span class="text-white font-bold">Sesi Fase Lanjutan</span>
                                <span class="text-slate-500">•</span>
                                <span class="text-slate-400 font-mono">Minggu @{{ firstLockedWeek }}+</span>
                                <span v-if="detail.date_formatted || detail.date" class="text-slate-500 font-mono hidden sm:inline">• @{{ detail.date_formatted || formatDate(detail.date) }}</span>
                            </div>
                            <button class="w-8 h-8 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition text-sm font-bold shrink-0 ml-2 cursor-pointer" 
                                    @click="closeDetail" 
                                    title="Tutup">
                                ✕
                            </button>
                        </div>

                        <!-- Scrollable Body -->
                        <div class="flex-1 min-h-0 overflow-y-auto p-5 md:p-6 custom-scrollbar space-y-4">
                            <!-- Info Context -->
                            <div class="space-y-1">
                                <h3 class="text-lg font-bold text-white tracking-tight" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                                    Buka Fase Lanjutan Program Lari
                                </h3>
                                <p class="text-xs text-slate-300 leading-relaxed">
                                    Jadwal aktif di kalender saat ini mencakup <strong class="text-white">Minggu 1 s/d @{{ firstLockedWeek - 1 }}</strong>. Sesi pada tanggal ini berada di fase lanjutan (<strong class="text-white">Minggu @{{ firstLockedWeek }} ke atas</strong>). Buka seluruh sesi tersisa dengan kontribusi sukarela atau kode promo event.
                                </p>
                            </div>

                            <!-- Contribution & Promo Form Box -->
                            <div class="p-4 md:p-5 rounded-md bg-slate-950 border border-slate-800 space-y-3.5">
                                <!-- Preset Nominal Chips & Slider -->
                                <div>
                                    <div class="flex items-center justify-between mb-2 text-xs">
                                        <span class="font-semibold text-slate-300">Dukungan / Kontribusi Sukarela</span>
                                        <span class="font-mono text-white font-bold text-base">
                                            Rp @{{ (parseInt(donationAmount) || 25000).toLocaleString('id-ID') }}
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-3 gap-2 mb-2.5">
                                        <button type="button" 
                                                @click="donationAmount = 15000" 
                                                :class="donationAmount == 15000 ? 'bg-neon text-slate-950 font-bold border-neon' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700'"
                                                class="py-2 px-2 rounded-md border text-xs text-center transition cursor-pointer">
                                            Rp 15.000
                                        </button>
                                        <button type="button" 
                                                @click="donationAmount = 25000" 
                                                :class="donationAmount == 25000 ? 'bg-neon text-slate-950 font-bold border-neon' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700'"
                                                class="py-2 px-2 rounded-md border text-xs text-center transition cursor-pointer">
                                            Rp 25.000 <span class="text-[9px] block opacity-80">Rekomendasi</span>
                                        </button>
                                        <button type="button" 
                                                @click="donationAmount = 50000" 
                                                :class="donationAmount == 50000 ? 'bg-neon text-slate-950 font-bold border-neon' : 'bg-slate-900 text-slate-300 border-slate-800 hover:border-slate-700'"
                                                class="py-2 px-2 rounded-md border text-xs text-center transition cursor-pointer">
                                            Rp 50.000
                                        </button>
                                    </div>

                                    <input v-model.number="donationAmount" type="range" min="10000" max="250000" step="5000" 
                                           class="w-full h-1.5 bg-slate-800 rounded appearance-none cursor-pointer accent-[#B8FF00]">
                                    <div class="flex justify-between text-[10px] text-slate-500 font-mono mt-1">
                                        <span>Min Rp 10.000</span>
                                        <span>Max Rp 250.000</span>
                                    </div>
                                </div>

                                <!-- Promo Code Input -->
                                <div class="pt-3 border-t border-slate-850">
                                    <label class="block text-xs font-semibold text-slate-400 mb-1.5">
                                        Punya Kode Promo atau Terdaftar di Event Lari?
                                    </label>
                                    <div class="flex gap-2">
                                        <input v-model="promoCode" type="text" placeholder="Masukkan Kode / No HP Event" 
                                               class="flex-1 bg-slate-900 border border-slate-700 rounded-md px-3 py-2 text-xs text-white focus:outline-none focus:border-slate-500 font-mono"
                                               :class="{'border-slate-600': promoApplied, 'border-rose-500': promoError}">
                                        <button v-if="!promoApplied" 
                                                type="button"
                                                @click="applyPromo" 
                                                :disabled="checkingPromo || !promoCode"
                                                class="px-4 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 transition cursor-pointer disabled:opacity-50">
                                            @{{ checkingPromo ? 'Cek...' : 'Terapkan' }}
                                        </button>
                                    </div>
                                    <p v-if="promoError" class="text-xs text-rose-400 mt-1">@{{ promoError }}</p>
                                    <p v-if="promoApplied" class="text-xs text-slate-200 mt-1 font-semibold">Kode promo aktif. Akses fase lanjutan dapat dibuka gratis.</p>
                                </div>

                                <!-- Action Button -->
                                <button type="button"
                                        @click="handleUnlockAction" 
                                        :disabled="donationLoading || checkingPromo" 
                                        class="w-full py-3 px-4 rounded-md bg-neon hover:bg-white text-slate-950 font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 shadow-sm">
                                    <span v-if="!donationLoading">
                                        @{{ promoApplied ? 'Buka Akses Penuh Sekarang (Gratis)' : 'Buka Akses Penuh (Rp ' + (parseInt(donationAmount) || 25000).toLocaleString('id-ID') + ')' }}
                                    </span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-slate-950" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses Transaksi...
                                    </span>
                                </button>
                                <div class="text-center text-[10px] text-slate-500 font-mono">
                                    Didukung Midtrans Payment Gateway (QRIS, GoPay, Bank Transfer)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Active Workout Detail UI (Spacious 2-Columns on lg+) -->
                    <div v-else class="flex flex-col flex-1 min-h-0 overflow-hidden">
                        <!-- Pinned Modal Header Bar -->
                        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-800 bg-slate-900 shrink-0">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="px-2.5 py-1 rounded bg-slate-800 border border-slate-700 text-xs font-semibold text-white tracking-wider uppercase font-mono shrink-0">
                                    @{{ activityLabel(detail.type) }}
                                </span>
                                <div class="flex items-center gap-2 bg-slate-950 border border-slate-800 rounded px-2.5 py-1 shrink-0">
                                    <span class="w-2 h-2 rounded-full" :class="statusDotClass(detail.status)"></span>
                                    <span class="text-xs font-mono text-slate-400">Status:</span>
                                    <span class="text-xs font-mono font-bold uppercase" :class="statusClass(detail.status)">@{{ statusText(detail.status) }}</span>
                                </div>
                                <span class="text-xs font-mono text-slate-400 hidden sm:inline-block truncate ml-1">@{{ detail.date_formatted || formatDate(detail.date) }}</span>
                            </div>
                            <button class="w-8 h-8 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition text-sm font-bold shrink-0 ml-2" @click="closeDetail" title="Tutup">✕</button>
                        </div>

                        <!-- Scrollable Body with Clean Telemetry HUD & 2-Column Blueprint -->
                        <div class="flex-1 min-h-0 overflow-y-auto p-5 md:p-6 custom-scrollbar space-y-5">
                            
                            <!-- Session Title Header -->
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-mono text-slate-400 uppercase tracking-widest font-semibold">@{{ detailTitle }}</span>
                                    <span class="text-xs font-mono text-slate-400 sm:hidden">@{{ detail.date_formatted || formatDate(detail.date) }}</span>
                                </div>
                                <h2 class="text-xl md:text-2xl font-extrabold text-white tracking-tight leading-snug" style="font-family: 'Inter Tight', 'Sora', sans-serif; letter-spacing: -0.03em;">
                                    @{{ detail.session?.title || detail.workout_structure?.race_name || 'Workout Session' }}
                                </h2>
                            </div>

                            <!-- 4-Stat Athletic Telemetry HUD -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <!-- Distance -->
                                <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 font-mono uppercase tracking-wider font-semibold">Distance</div>
                                    <div class="text-lg md:text-xl font-bold font-mono text-white mt-0.5 tabular-nums">
                                        <template v-if="detail.distance && String(detail.type || '').toLowerCase() !== 'rest' && String(detail.type || '').toLowerCase() !== 'rest_day'">
                                            @{{ detail.distance }} <span class="text-xs text-slate-400 font-normal">km</span>
                                        </template>
                                        <template v-else>
                                            <span class="text-xs text-slate-400 font-normal">Rest Day</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Target Pace -->
                                <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 font-mono uppercase tracking-wider font-semibold">Target Pace</div>
                                    <div class="text-lg md:text-xl font-bold font-mono text-neon mt-0.5 tabular-nums">
                                        @{{ displayPace || '-' }}
                                    </div>
                                </div>

                                <!-- Duration -->
                                <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 font-mono uppercase tracking-wider font-semibold">Duration</div>
                                    <div class="text-lg md:text-xl font-bold font-mono text-white mt-0.5 tabular-nums">
                                        @{{ detail.duration || '-' }}
                                    </div>
                                </div>

                                <!-- Intensity / Difficulty -->
                                <div class="p-3.5 rounded-lg bg-slate-950 border border-slate-800">
                                    <div class="text-[10px] text-slate-400 font-mono uppercase tracking-wider font-semibold">Intensity</div>
                                    <div class="text-base md:text-lg font-bold text-white mt-1 capitalize truncate">
                                        @{{ (detail.program_difficulty || detail.difficulty || '').toUpperCase() || 'MODERATE' }}
                                    </div>
                                </div>
                            </div>

                            <!-- 2-COLUMN GRID (Spacious 12-column layout on lg+) -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start">
                                
                                <!-- LEFT COLUMN: Instruksi, Fisiologi & Langkah Sesi (lg:col-span-7) -->
                                <div class="lg:col-span-7 space-y-5">
                                    <!-- Deskripsi Aktivitas (Clean, High-Contrast Typography, No AI Clichés) -->
                                    <div v-if="detail.description" class="bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Instruksi & Struktur Latihan</h3>
                                            <span class="text-[11px] text-slate-400 font-mono">Blueprint</span>
                                        </div>
                                        <div class="text-sm text-slate-200 leading-relaxed font-normal whitespace-pre-line">
                                            @{{ detail.description }}
                                        </div>
                                    </div>

                                    <!-- Tujuan & Efek Fisiologis -->
                                    <div v-if="['run', 'easy_run', 'recovery', 'long_run', 'tempo', 'tempo_run', 'threshold', 'interval', 'repetition', 'speed', 'hill', 'hill_repeats', 'strength', 'rest', 'yoga', 'cycling', 'race'].includes(String(detail.type || '').toLowerCase())"
                                         class="bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Tujuan & Efek Fisiologis</h3>
                                            <span class="text-[11px] text-slate-400 font-mono">Adaptasi</span>
                                        </div>
                                        <div class="space-y-3 text-sm">
                                            <div>
                                                <div class="text-xs font-semibold text-slate-400 font-mono uppercase tracking-wider mb-0.5">Target Sesi Utama</div>
                                                <div class="text-white font-semibold" id="detail-target-dist">@{{ detail.session?.session_name || '-' }}</div>
                                            </div>
                                            <div class="pt-2.5 border-t border-slate-800">
                                                <div class="text-xs font-semibold text-slate-400 font-mono uppercase tracking-wider mb-0.5">Dampak bagi Tubuh</div>
                                                <div class="text-slate-300 leading-relaxed text-xs">@{{ workoutEffectText }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tahapan Sesi Latihan (guidedSteps) -->
                                    <div v-if="guidedSteps.length > 0" class="bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Tahapan Sesi Latihan</h3>
                                            <div class="text-xs font-mono text-slate-400">@{{ guidedStepsDoneCount }}/@{{ guidedSteps.length }} Selesai</div>
                                        </div>
                                        <!-- Progress Bar -->
                                        <div class="h-1.5 rounded-sm bg-slate-950 border border-slate-800 overflow-hidden">
                                            <div class="h-full bg-neon transition-all duration-300" :style="{ width: guidedStepsProgressPct + '%' }"></div>
                                        </div>
                                        <!-- Interactive Step Checklist -->
                                        <div class="space-y-2 max-h-72 overflow-y-auto custom-scrollbar pr-1">
                                            <button v-for="(step, idx) in guidedSteps" :key="step.id" type="button" 
                                                    class="w-full flex justify-between items-center text-xs p-3 rounded-md border transition text-left"
                                                    :class="guidedStepChecked(step) ? 'bg-emerald-950/20 border-emerald-800/50' : 'bg-slate-950 border-slate-800 hover:border-slate-700'"
                                                    @click="toggleGuidedStep(step)">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="w-5 h-5 rounded-full border flex items-center justify-center flex-shrink-0 text-xs font-bold transition"
                                                         :class="guidedStepChecked(step) ? 'bg-emerald-500 border-emerald-500 text-slate-950' : 'bg-slate-900 border-slate-700 text-slate-500'">
                                                        @{{ guidedStepChecked(step) ? '✓' : '' }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-2">
                                                            <span v-if="step.badge" class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border"
                                                                  :class="step.badgeClass">@{{ step.badge }}</span>
                                                            <span class="font-bold text-slate-200 truncate">@{{ step.title }}</span>
                                                        </div>
                                                        <div v-if="step.subtitle" class="text-[11px] text-slate-400 truncate mt-0.5">@{{ step.subtitle }}</div>
                                                    </div>
                                                </div>
                                                <div class="text-right flex-shrink-0 pl-3">
                                                    <div v-if="step.valueText" class="text-white font-mono text-xs font-semibold tabular-nums">@{{ step.valueText }}</div>
                                                    <div v-if="step.paceText" class="text-[11px] text-slate-400 font-mono">@{{ step.paceText }}</div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Catatan Khusus Pelatih (Notes) -->
                                    <div v-if="detail.notes" class="p-4 bg-amber-950/20 border border-amber-800/60 rounded-lg space-y-1.5">
                                        <div class="text-xs text-amber-400 uppercase font-bold font-mono tracking-wider">Catatan Khusus Pelatih</div>
                                        <div class="text-sm text-slate-200 leading-relaxed font-normal">@{{ detail.notes }}</div>
                                    </div>
                                </div>

                                <!-- RIGHT COLUMN: Eksekusi Latihan, Panduan Pacing & Telemetri (lg:col-span-5) -->
                                <div class="lg:col-span-5 space-y-5">
                                    
                                    <!-- Panel Aksi / Status Laporan Sesi -->
                                    <div v-if="detail.type === 'run' || detail.type === 'easy_run' || detail.type === 'interval' || detail.type === 'tempo' || detail.type === 'tempo_run' || detail.type === 'repetition' || detail.type === 'hill' || detail.type === 'hill_repeats' || detail.type === 'program_session' || detail.type === 'yoga' || detail.type === 'cycling' || detail.type === 'rest' || detail.type === 'race' || detail.type === 'custom_workout'"
                                         class="bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-sm space-y-3">
                                        
                                        <!-- Pending state -->
                                        <div v-if="(detail.status === 'pending' || !detail.status) && !showFinishForm" class="space-y-3">
                                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                                <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Status & Aksi Latihan</h3>
                                                <span class="text-[11px] text-slate-400 font-mono">Siap Dijalankan</span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2.5">
                                                <button class="w-full py-2.5 px-3 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs border border-slate-700 transition uppercase tracking-wider flex items-center justify-center" @click="updateSessionStatus(detail, 'started')">
                                                    Mulai Latihan
                                                </button>
                                                <button class="w-full py-2.5 px-3 rounded-md bg-neon hover:bg-white text-dark font-extrabold text-xs transition uppercase tracking-wider shadow-sm flex items-center justify-center" @click="showFinishForm = true">
                                                    Catat Selesai
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Started or Finishing Form state -->
                                        <div v-else-if="detail.status === 'started' || showFinishForm" class="space-y-3">
                                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                                <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Lapor Selesai Latihan</h3>
                                                <span class="text-[11px] font-mono font-bold uppercase text-neon">
                                                    @{{ detail.status === 'started' ? 'Sedang Berjalan' : 'Form Laporan' }}
                                                </span>
                                            </div>
                                            <div class="space-y-3 text-xs">
                                                <div>
                                                    <label class="text-xs text-slate-300 block mb-1 font-semibold">Tautan Aktivitas Strava (Opsional)</label>
                                                    <input type="url" v-model="stravaLinkInput" placeholder="https://www.strava.com/activities/..." class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 text-white text-xs focus:border-slate-600 focus:outline-none transition">
                                                </div>
                                                
                                                <!-- RPE & Feeling Input -->
                                                <div class="grid grid-cols-2 gap-2.5">
                                                    <div>
                                                        <label class="text-xs text-slate-300 block mb-1 font-semibold">RPE Effort (1-10)</label>
                                                        <select v-model="rpeInput" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs focus:border-slate-600 focus:outline-none transition">
                                                            <option value="">Pilih Skala</option>
                                                            <option value="1">1 - Sangat Ringan</option>
                                                            <option value="3">3 - Ringan / Santai</option>
                                                            <option value="5">5 - Sedang</option>
                                                            <option value="7">7 - Keras (Threshold)</option>
                                                            <option value="9">9 - Sangat Keras</option>
                                                            <option value="10">10 - Maksimal (All-out)</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs text-slate-300 block mb-1 font-semibold">Feeling Tubuh</label>
                                                        <select v-model="feelingInput" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3 py-2 text-white text-xs focus:border-slate-600 focus:outline-none transition">
                                                            <option value="">Pilih Kondisi</option>
                                                            <option value="strong">Bertenaga (Strong)</option>
                                                            <option value="good">Bagus (Good)</option>
                                                            <option value="average">Biasa Saja (Average)</option>
                                                            <option value="weak">Lelah (Weak)</option>
                                                            <option value="terrible">Sakit/Drop (Terrible)</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="text-xs text-slate-300 block mb-1 font-semibold">Catatan Evaluasi / Keluhan</label>
                                                    <textarea v-model="notesInput" rows="2" placeholder="Bagaimana sesi latihan Anda? Ada kendala atau keluhan otot?" class="w-full bg-slate-950 border border-slate-800 rounded-md px-3.5 py-2 text-white text-xs focus:border-slate-600 focus:outline-none transition leading-relaxed"></textarea>
                                                </div>

                                                <div class="flex gap-2.5 pt-1">
                                                    <button v-if="detail.status === 'started'" class="w-1/2 py-2.5 rounded-md bg-slate-800 text-slate-200 font-bold text-xs hover:bg-slate-700 transition border border-slate-700 uppercase tracking-wider" @click="updateSessionStatus(detail, 'pending')">Hentikan</button>
                                                    <button v-else class="w-1/2 py-2.5 rounded-md bg-slate-800 text-slate-200 font-bold text-xs hover:bg-slate-700 transition border border-slate-700 uppercase tracking-wider" @click="showFinishForm = false">Batal</button>
                                                    <button class="w-1/2 py-2.5 rounded-md bg-neon text-dark font-extrabold text-xs hover:bg-white transition uppercase tracking-wider shadow-sm flex items-center justify-center" @click="finishActivityWithLink">Simpan Selesai</button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Completed state -->
                                        <div v-else-if="detail.status === 'completed'" class="bg-emerald-950/20 border border-emerald-800/40 rounded-lg p-4 text-center space-y-1">
                                            <div class="text-xs font-mono text-emerald-400 uppercase tracking-wider font-bold">Sesi Selesai</div>
                                            <div class="text-xs text-slate-300">Aktivitas tercatat selesai pada @{{ formatDate(detail.completed_at || new Date()) }}</div>
                                        </div>
                                    </div>

                                    <!-- Panduan Teknis Pelatih & Pace Cheat Sheet (Clean Athletic Guidance) -->
                                    <div v-if="['run', 'easy_run', 'recovery', 'long_run', 'tempo', 'tempo_run', 'threshold', 'interval', 'repetition', 'speed', 'hill', 'hill_repeats', 'race'].includes(String(detail.type || '').toLowerCase())"
                                         class="bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Panduan Teknis Pelatih</h3>
                                            <button v-if="!trainingProfile?.paces?.E && !trainingProfile?.paces?.T && !trainingProfile?.paces?.I" 
                                                    type="button" class="px-2.5 py-1 rounded bg-slate-800 text-slate-200 border border-slate-700 text-[11px] font-bold hover:border-slate-600 transition" @click="openVdotModal">
                                                Set Pace
                                            </button>
                                        </div>

                                        <div class="text-sm text-slate-200 leading-relaxed font-normal">
                                            @{{ aiCoachSummary }}
                                        </div>

                                        <!-- Coaching Cues -->
                                        <div v-if="aiCoachCues.length" class="space-y-2 pt-2 border-t border-slate-800">
                                            <div class="text-xs font-semibold text-slate-400 font-mono uppercase tracking-wider">Fokus Eksekusi (Cues)</div>
                                            <div class="grid grid-cols-1 gap-2">
                                                <div v-for="(c, idx) in aiCoachCues" :key="'cue-' + idx" 
                                                     class="text-xs text-slate-200 bg-slate-950 border border-slate-800 rounded-md p-2.5 leading-relaxed">
                                                    @{{ c }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pace Cheat Sheet -->
                                        <div v-if="trainingProfile?.paces?.E || trainingProfile?.paces?.T || trainingProfile?.paces?.I || trainingProfile?.paces?.R" 
                                             class="space-y-2 pt-2 border-t border-slate-800">
                                            <div class="text-xs font-semibold text-slate-400 font-mono uppercase tracking-wider">Target Pace Personal (VDOT)</div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div v-if="trainingProfile?.paces?.E" class="rounded-md bg-slate-950 border border-slate-800 p-2.5">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Easy (E)</div>
                                                    <div class="text-white font-bold font-mono text-xs mt-0.5 tabular-nums">
                                                        <template v-if="trainingProfile?.paces?.E_high && trainingProfile?.paces?.E_low">
                                                            @{{ formatPace(trainingProfile.paces.E_high) }} - @{{ formatPace(trainingProfile.paces.E_low) }}/km
                                                        </template>
                                                        <template v-else>
                                                            @{{ formatPace(trainingProfile.paces.E) }}/km
                                                        </template>
                                                    </div>
                                                </div>
                                                <div v-if="trainingProfile?.paces?.M" class="rounded-md bg-slate-950 border border-slate-800 p-2.5">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Marathon (M)</div>
                                                    <div class="text-white font-bold font-mono text-xs mt-0.5 tabular-nums">@{{ formatPace(trainingProfile.paces.M) }}/km</div>
                                                </div>
                                                <div v-if="trainingProfile?.paces?.T" class="rounded-md bg-slate-950 border border-slate-800 p-2.5">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Tempo (T)</div>
                                                    <div class="text-white font-bold font-mono text-xs mt-0.5 tabular-nums">@{{ formatPace(trainingProfile.paces.T) }}/km</div>
                                                </div>
                                                <div v-if="trainingProfile?.paces?.I" class="rounded-md bg-slate-950 border border-slate-800 p-2.5">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Interval (I)</div>
                                                    <div class="text-white font-bold font-mono text-xs mt-0.5 tabular-nums">@{{ formatPace(trainingProfile.paces.I) }}/km</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Data Telemetri & Analisis Strava (Jika Tersedia) -->
                                    <div v-if="stravaDetailsLoading" class="bg-slate-900 border border-slate-800 rounded-lg p-4 text-xs text-slate-300">
                                        Memuat telemetri Strava...
                                    </div>
                                    <div v-else-if="stravaDetailsError" class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 text-xs text-red-200">
                                        @{{ stravaDetailsError }}
                                    </div>
                                    <div v-else-if="detail.strava_metrics" class="space-y-4">
                                        <div class="p-5 rounded-lg bg-slate-900 border border-slate-800 space-y-3">
                                            <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                                <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Telemetri Strava</h3>
                                                <span class="text-[11px] text-slate-400 font-mono">Actual Metrics</span>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                                <div class="flex flex-col items-center justify-center rounded-md bg-slate-950 border border-slate-800 p-2.5 text-center">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Heart Rate</div>
                                                    <div class="text-white font-bold font-mono text-xs mt-0.5 tabular-nums">
                                                        @{{ detail.strava_metrics.average_heartrate ? Math.round(detail.strava_metrics.average_heartrate) : '-' }}
                                                        <span class="text-[10px] text-slate-400 font-normal">avg</span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-mono">
                                                        max @{{ detail.strava_metrics.max_heartrate ? Math.round(detail.strava_metrics.max_heartrate) : '-' }}
                                                    </div>
                                                </div>
                                                <div class="flex flex-col items-center justify-center rounded-md bg-slate-950 border border-slate-800 p-2.5 text-center">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Cadence</div>
                                                    <div class="text-white font-bold font-mono text-xs mt-0.5 tabular-nums">
                                                        @{{ detail.strava_metrics.average_cadence ? Math.round(detail.strava_metrics.average_cadence) : '-' }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-mono">spm</div>
                                                </div>
                                                <div class="flex flex-col items-center justify-center rounded-md bg-slate-950 border border-slate-800 p-2.5 text-center">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Avg Pace</div>
                                                    <div class="text-neon font-bold font-mono text-xs mt-0.5 tabular-nums">@{{ detail.strava_metrics.pace ? (detail.strava_metrics.pace + ' /km') : '-' }}</div>
                                                    <div class="text-[10px] text-slate-400">&nbsp;</div>
                                                </div>
                                                <div class="flex flex-col items-center justify-center rounded-md bg-slate-950 border border-slate-800 p-2.5 text-center">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Power</div>
                                                    <div class="text-white font-bold font-mono text-xs mt-0.5 tabular-nums">
                                                        @{{ detail.strava_metrics.average_watts ? Math.round(detail.strava_metrics.average_watts) : '-' }}
                                                    </div>
                                                    <div class="text-[10px] text-slate-400 font-mono">watts</div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-3 gap-2 pt-1">
                                                <div class="flex flex-col items-center justify-center rounded-md bg-slate-950 border border-slate-800 p-2 text-center">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Total Time</div>
                                                    <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_metrics.total_time_s ? formatSeconds(detail.strava_metrics.total_time_s) : '-' }}</div>
                                                </div>
                                                <div class="flex flex-col items-center justify-center rounded-md bg-slate-950 border border-slate-800 p-2 text-center">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Moving</div>
                                                    <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_metrics.moving_time_s ? formatSeconds(detail.strava_metrics.moving_time_s) : '-' }}</div>
                                                </div>
                                                <div class="flex flex-col items-center justify-center rounded-md bg-slate-950 border border-slate-800 p-2 text-center">
                                                    <div class="text-[10px] text-slate-400 uppercase font-mono">Paused</div>
                                                    <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_metrics.pause_time_s ? formatSeconds(detail.strava_metrics.pause_time_s) : '-' }}</div>
                                                </div>
                                            </div>

                                            <!-- Pace Distribution -->
                                            <div v-if="detail.strava_pace_zones" class="p-3 rounded-md bg-slate-950 border border-slate-800 space-y-2">
                                                <div class="text-[10px] font-bold text-slate-400 uppercase font-mono tracking-wider">Distribusi Pace</div>
                                                <div class="grid grid-cols-3 gap-2">
                                                    <div class="flex flex-col items-center justify-center rounded bg-slate-900 border border-slate-800 p-2">
                                                        <div class="text-[10px] text-slate-400 font-mono">Easy</div>
                                                        <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_pace_zones.summary.easy }}%</div>
                                                    </div>
                                                    <div class="flex flex-col items-center justify-center rounded bg-slate-900 border border-slate-800 p-2">
                                                        <div class="text-[10px] text-slate-400 font-mono">Tempo</div>
                                                        <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_pace_zones.summary.tempo }}%</div>
                                                    </div>
                                                    <div class="flex flex-col items-center justify-center rounded bg-slate-900 border border-slate-800 p-2">
                                                        <div class="text-[10px] text-slate-400 font-mono">Speed</div>
                                                        <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_pace_zones.summary.speed }}%</div>
                                                    </div>
                                                </div>
                                                <div class="text-[10px] text-slate-400 font-mono text-center pt-1">
                                                    E @{{ detail.strava_pace_zones.zones.E }}% • M @{{ detail.strava_pace_zones.zones.M }}% • T @{{ detail.strava_pace_zones.zones.T }}% • I @{{ detail.strava_pace_zones.zones.I }}% • R @{{ detail.strava_pace_zones.zones.R }}%
                                                </div>
                                            </div>

                                            <!-- HR Distribution -->
                                            <div v-if="detail.strava_hr_zones" class="p-3 rounded-md bg-slate-950 border border-slate-800 space-y-2">
                                                <div class="text-[10px] font-bold text-slate-400 uppercase font-mono tracking-wider">Distribusi Heart Rate</div>
                                                <div class="grid grid-cols-5 gap-1.5">
                                                    <div class="flex flex-col items-center justify-center rounded bg-slate-900 border border-slate-800 p-1.5 text-center">
                                                        <div class="text-[10px] text-slate-400 font-mono">Z1</div>
                                                        <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_hr_zones.Z1 }}%</div>
                                                    </div>
                                                    <div class="flex flex-col items-center justify-center rounded bg-slate-900 border border-slate-800 p-1.5 text-center">
                                                        <div class="text-[10px] text-slate-400 font-mono">Z2</div>
                                                        <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_hr_zones.Z2 }}%</div>
                                                    </div>
                                                    <div class="flex flex-col items-center justify-center rounded bg-slate-900 border border-slate-800 p-1.5 text-center">
                                                        <div class="text-[10px] text-slate-400 font-mono">Z3</div>
                                                        <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_hr_zones.Z3 }}%</div>
                                                    </div>
                                                    <div class="flex flex-col items-center justify-center rounded bg-slate-900 border border-slate-800 p-1.5 text-center">
                                                        <div class="text-[10px] text-slate-400 font-mono">Z4</div>
                                                        <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_hr_zones.Z4 }}%</div>
                                                    </div>
                                                    <div class="flex flex-col items-center justify-center rounded bg-slate-900 border border-slate-800 p-1.5 text-center">
                                                        <div class="text-[10px] text-slate-400 font-mono">Z5</div>
                                                        <div class="text-white font-bold font-mono text-xs tabular-nums">@{{ detail.strava_hr_zones.Z5 }}%</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Analisis Zona Strava -->
                                        <div v-if="detail.strava_zone_analysis || detail.analysis" class="p-5 rounded-lg bg-slate-900 border border-slate-800 space-y-3">
                                            <div v-if="detail.strava_zone_analysis" class="space-y-2">
                                                <div class="text-xs font-bold text-white uppercase font-mono tracking-wider">Analisis Zona & Efek</div>
                                                <div class="text-slate-300 text-xs leading-relaxed">@{{ detail.strava_zone_analysis }}</div>
                                                <div v-if="detail.strava_zone_effect" class="text-xs text-neon font-bold uppercase mt-1">Efek Latihan: <span class="text-white font-medium">@{{ detail.strava_zone_effect }}</span></div>
                                                <div v-if="detail.strava_zone_suggestion" class="pt-2 border-t border-slate-800 text-xs text-amber-400 font-bold uppercase">
                                                    Saran: <span class="text-white font-medium">@{{ detail.strava_zone_suggestion }}</span>
                                                </div>
                                            </div>

                                            <div v-if="detail.analysis" class="space-y-2 pt-2 border-t border-slate-800">
                                                <div class="text-xs font-bold text-white uppercase font-mono tracking-wider">Ringkasan Analisis</div>
                                                <p class="text-slate-300 text-xs leading-relaxed">@{{ detail.analysis }}</p>
                                                <div v-if="detail.suggestion" class="pt-2 border-t border-slate-800">
                                                    <div class="text-xs text-amber-400 font-bold uppercase font-mono">Saran Latihan Berikutnya</div>
                                                    <p class="text-white font-medium text-xs mt-0.5">@{{ detail.suggestion }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Performance Chart -->
                                        <div v-if="detail.strava_streams && detail.strava_streams.time && detail.strava_streams.time.length > 0" class="p-5 rounded-lg bg-slate-900 border border-slate-800 space-y-3">
                                            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                                <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Grafik Performa Sesi</h3>
                                                <button @click="showStravaGraphModal = true" class="p-1.5 bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white rounded transition text-xs" title="Perbesar Grafik">
                                                    Perbesar
                                                </button>
                                            </div>
                                            <div class="h-44 bg-slate-950 border border-slate-800 rounded-md p-2 relative group">
                                                <canvas id="stravaMetricsChart" class="w-full h-full"></canvas>
                                            </div>
                                        </div>

                                        <!-- Splits List -->
                                        <div v-if="detail.strava_splits && detail.strava_splits.length > 0" class="p-5 rounded-lg bg-slate-900 border border-slate-800 space-y-3">
                                            <div class="text-xs font-bold text-white uppercase font-mono tracking-wider">Splits per Kilometer</div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1.5 pr-1">
                                                <div v-for="s in detail.strava_splits" :key="s.split" class="flex justify-between items-center text-xs p-2.5 rounded-md bg-slate-950 border border-slate-800">
                                                    <div class="text-slate-200 font-bold font-mono">KM @{{ s.split || '-' }}</div>
                                                    <div class="text-right">
                                                        <div class="text-neon font-mono font-semibold tabular-nums">@{{ s.pace || '-' }}</div>
                                                        <div class="text-[10px] text-slate-400 font-mono">@{{ formatSeconds(s.moving_time_s) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Laps List -->
                                        <div v-if="detail.strava_laps && detail.strava_laps.length > 0" class="p-5 rounded-lg bg-slate-900 border border-slate-800 space-y-3">
                                            <div class="text-xs font-bold text-white uppercase font-mono tracking-wider">Laps Aktivitas</div>
                                            <div class="max-h-48 overflow-y-auto custom-scrollbar space-y-1.5 pr-1">
                                                <div v-for="(l, idx) in detail.strava_laps" :key="idx" class="flex justify-between items-center text-xs p-2.5 rounded-md bg-slate-950 border border-slate-800">
                                                    <div class="min-w-0">
                                                        <div class="text-slate-200 font-bold truncate">@{{ l.name || ('Lap ' + (idx + 1)) }}</div>
                                                        <div class="text-[10px] text-slate-400 font-mono">
                                                            @{{ l.distance_m ? (Math.round(l.distance_m) + ' m') : '-' }} • @{{ formatSeconds(l.moving_time_s) }}
                                                        </div>
                                                    </div>
                                                    <div class="text-right">
                                                        <div class="text-white font-mono font-semibold tabular-nums">@{{ l.pace || '-' }}</div>
                                                        <div class="text-[10px] text-slate-400 font-mono">@{{ l.average_heartrate ? (Math.round(l.average_heartrate) + ' bpm') : '' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Media Photos -->
                                        <div v-if="detail.strava_media && detail.strava_media.length" class="p-5 rounded-lg bg-slate-900 border border-slate-800 space-y-3">
                                            <div class="text-xs font-bold text-white uppercase font-mono tracking-wider">Foto Aktivitas</div>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                                <a v-for="(m, idx) in detail.strava_media" :key="idx" :href="m" target="_blank" class="block">
                                                    <img :src="m" class="w-full h-24 object-cover rounded-md border border-slate-800 bg-slate-950" loading="lazy">
                                                </a>
                                            </div>
                                        </div>

                                        <div v-if="detail.strava_link" class="text-xs pt-1">
                                            <a :href="detail.strava_link" target="_blank" class="text-neon hover:underline inline-flex items-center gap-1 font-semibold">
                                                <span>Buka Detail di Strava</span>
                                                <span>›</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Feedback Pelatih Manual (Jika Ada) -->
                                    <div v-if="detail.coach_feedback || detail.coach_rating" class="bg-slate-900 rounded-lg p-5 border border-slate-800 shadow-sm space-y-2.5">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Evaluasi Pelatih</h3>
                                            <div v-if="detail.coach_rating" class="flex text-amber-400 text-xs">
                                                <span v-for="i in 5" :key="i">@{{ i <= detail.coach_rating ? '★' : '☆' }}</span>
                                            </div>
                                        </div>
                                        <div v-if="detail.coach_feedback" class="text-xs text-slate-200 italic leading-relaxed">
                                            "@{{ detail.coach_feedback }}"
                                        </div>
                                    </div>

                                    <!-- Post-Workout Deep Analysis (Strava) -->
                                    <div v-if="detail.source === 'strava' || detail.strava_metrics" class="bg-slate-900 rounded-lg p-5 border border-slate-800 shadow-sm space-y-3">
                                        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800">
                                            <h3 class="text-xs font-bold text-white uppercase tracking-wider font-mono">Analisis Telemetri Sesi</h3>
                                            <button class="px-2.5 py-1 rounded bg-slate-800 text-slate-300 border border-slate-700 text-[11px] font-semibold hover:border-slate-600 transition disabled:opacity-50"
                                                    :disabled="aiAnalysisLoading"
                                                    @click="loadAiWorkoutAnalysis(detail.strava_metrics?.strava_activity_id || detail.strava_activity_id, true)">
                                                @{{ aiAnalysisLoading ? 'Menganalisis...' : 'Refresh Analisis' }}
                                            </button>
                                        </div>

                                        <div v-if="aiAnalysisLoading" class="text-xs text-slate-400">Sedang mengevaluasi telemetri sesi latihan Anda...</div>
                                        <div v-else-if="aiAnalysisError" class="text-xs text-red-300 bg-red-500/10 border border-red-500/20 rounded-md p-3">@{{ aiAnalysisError }}</div>
                                        <div v-else-if="detail.ai_analysis" class="space-y-3 text-xs">
                                            <div v-if="detail.ai_analysis.summary" class="text-slate-200 leading-relaxed">@{{ detail.ai_analysis.summary }}</div>
                                            <div class="text-xs text-slate-300">
                                                <span class="text-slate-400 font-mono">Junk Miles Risk:</span>
                                                <span class="font-bold ml-1 font-mono uppercase" :class="detail.ai_analysis.junk_miles_risk?.level === 'high' ? 'text-red-400' : (detail.ai_analysis.junk_miles_risk?.level === 'medium' ? 'text-amber-400' : 'text-emerald-400')">@{{ detail.ai_analysis.junk_miles_risk?.level || 'unknown' }}</span>
                                            </div>

                                            <div v-if="detail.ai_analysis.what_went_well?.length">
                                                <div class="text-[10px] font-bold text-emerald-400 uppercase font-mono tracking-wider mb-1">Pencapaian Positif</div>
                                                <ul class="space-y-1 text-slate-300">
                                                    <li v-for="(item, idx) in detail.ai_analysis.what_went_well" :key="'well-' + idx">• @{{ item }}</li>
                                                </ul>
                                            </div>

                                            <div v-if="detail.ai_analysis.what_to_improve?.length">
                                                <div class="text-[10px] font-bold text-amber-400 uppercase font-mono tracking-wider mb-1">Area Peningkatan</div>
                                                <ul class="space-y-1 text-slate-300">
                                                    <li v-for="(item, idx) in detail.ai_analysis.what_to_improve" :key="'improve-' + idx">• @{{ item }}</li>
                                                </ul>
                                            </div>

                                            <div v-if="detail.ai_analysis.next_workout_suggestion?.type || detail.ai_analysis.next_workout_suggestion?.reason" class="rounded-md bg-slate-950 border border-slate-800 p-3">
                                                <div class="text-[10px] font-bold text-neon uppercase font-mono tracking-wider mb-1">Rekomendasi Sesi Berikutnya</div>
                                                <div class="text-white font-bold text-xs">@{{ detail.ai_analysis.next_workout_suggestion.type || '-' }}</div>
                                                <div v-if="detail.ai_analysis.next_workout_suggestion.duration" class="text-[11px] text-slate-400 mt-0.5">Durasi: @{{ detail.ai_analysis.next_workout_suggestion.duration }}</div>
                                                <div v-if="detail.ai_analysis.next_workout_suggestion.target" class="text-[11px] text-slate-400">Target: @{{ detail.ai_analysis.next_workout_suggestion.target }}</div>
                                                <div v-if="detail.ai_analysis.next_workout_suggestion.reason" class="text-xs text-slate-300 mt-1.5 leading-relaxed">@{{ detail.ai_analysis.next_workout_suggestion.reason }}</div>
                                            </div>

                                            <div v-if="detail.ai_analysis.recovery_advice?.length">
                                                <div class="text-[10px] font-bold text-sky-400 uppercase font-mono tracking-wider mb-1">Saran Pemulihan (Recovery)</div>
                                                <ul class="space-y-1 text-slate-300">
                                                    <li v-for="(item, idx) in detail.ai_analysis.recovery_advice" :key="'recovery-' + idx">• @{{ item }}</li>
                                                </ul>
                                            </div>

                                            <div v-if="detail.ai_analysis.risk_flags?.length" class="rounded-md bg-red-500/10 border border-red-500/20 p-3">
                                                <div class="text-[10px] font-bold text-red-400 uppercase font-mono tracking-wider mb-1">Catatan Risiko</div>
                                                <ul class="space-y-1 text-red-200">
                                                    <li v-for="(item, idx) in detail.ai_analysis.risk_flags" :key="'risk-' + idx">• @{{ item }}</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pinned Modal Footer Bar -->
                        <div class="p-4 md:px-6 md:py-3.5 border-t border-slate-800 bg-slate-900 flex justify-between items-center shrink-0">
                            <button v-if="!detail.session?.is_locked && ((detail.source === 'custom' || detail.workout_id) || (detail.enrollment_id && detail.session_day))" 
                                    class="px-3.5 py-2 rounded-md bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/20 text-xs font-semibold transition" 
                                    @click="deleteWorkout(detail)">
                                Hapus Sesi
                            </button>
                            <button class="px-5 py-2 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs ml-auto font-semibold uppercase tracking-wider transition" 
                                    @click="closeDetail">
                                Tutup
                            </button>
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

        <!-- LEGACY Generate VDOT Program - DISABLED (diganti redirect ke /buat-program-lari) -->
        <div v-if="false && showVdotModal" class="fixed inset-0 z-[1050] overflow-y-auto">
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
            <div class="fixed inset-0 bg-black/80" @click="showWeeklyTargetModal = false"></div>
            <div class="relative z-10 max-w-md mx-auto my-20 bg-slate-900 border border-slate-800 rounded-lg p-5 shadow-2xl">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-800">
                    <h3 class="text-white font-bold text-base flex items-center gap-2">
                        <span>Pengaturan Target Mingguan</span>
                    </h3>
                    <button @click="showWeeklyTargetModal = false" class="text-slate-400 hover:text-white text-xs">✕</button>
                </div>

                <div class="space-y-3.5">
                    <!-- Option 1: Auto Sync from Active Program -->
                    <label class="block p-3.5 rounded-lg border transition cursor-pointer"
                           :class="weeklyTargetForm.mode === 'auto' ? 'bg-slate-800/90 border-brand-lime/60' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700'">
                        <div class="flex items-start gap-3">
                            <input type="radio" value="auto" v-model="weeklyTargetForm.mode" class="mt-1 accent-[#CCFF00]">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-white">Otomatis dari Program Aktif</span>
                                    <span class="text-xs font-mono font-black text-brand-lime">@{{ autoTargetKm }} km</span>
                                </div>
                                <p class="text-[11px] text-slate-300 mt-1 leading-relaxed">
                                    Target jarak mingguan otomatis disinkronkan dari akumulasi volume sesi latihan pekan berjalan.
                                </p>
                            </div>
                        </div>
                    </label>

                    <!-- Option 2: Custom Manual Target -->
                    <label class="block p-3.5 rounded-lg border transition cursor-pointer"
                           :class="weeklyTargetForm.mode === 'manual' ? 'bg-slate-800/90 border-brand-lime/60' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700'">
                        <div class="flex items-start gap-3">
                            <input type="radio" value="manual" v-model="weeklyTargetForm.mode" class="mt-1 accent-[#CCFF00]">
                            <div class="flex-1">
                                <span class="text-xs font-bold text-white block">Target Kustom (Manual)</span>
                                <p class="text-[11px] text-slate-300 mt-0.5 leading-relaxed">
                                    Tentukan sendiri batas target jarak lari mingguan Anda secara mandiri.
                                </p>
                                <div v-if="weeklyTargetForm.mode === 'manual'" class="mt-3">
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.5" min="1" max="300" v-model.number="weeklyTargetForm.weekly_km_target" 
                                               placeholder="Contoh: 35.0"
                                               class="w-full bg-slate-950 border border-slate-700 focus:border-brand-lime rounded-md px-3 py-2 text-white text-xs font-mono font-bold outline-none">
                                        <span class="text-xs font-mono text-slate-300 font-bold">KM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </label>

                    <div class="flex justify-end gap-2 pt-3 border-t border-slate-800">
                        <button type="button" class="px-3.5 py-2 rounded-md bg-slate-800 text-slate-300 border border-slate-700 text-xs hover:text-white font-bold" @click="showWeeklyTargetModal = false">Batal</button>
                        <button type="button" @click="updateWeeklyTarget" class="px-5 py-2 rounded-md bg-brand-lime text-slate-950 font-extrabold text-xs hover:bg-[#b8e600] transition flex items-center gap-2 cursor-pointer" :disabled="weeklyTargetLoading">
                            <span v-if="weeklyTargetLoading" class="animate-spin">⟳</span>
                            Simpan Target
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
                                    <span v-if="insightType === 'generate'">Proyeksi Waktu Lomba (Setelah Program)</span>
                                    <span v-else>Perubahan Equivalent Race Times</span>
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
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kenapa Pace Ini Meningkatkan Performamu?</span>
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
                                <span class="text-neon text-sm flex-shrink-0"><i class="fa-solid fa-lightbulb"></i></span>
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
                            Mulai Latihan!
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
                            <option value="hill_repeats">Hill Repeats (Repetition)</option>
                            <option value="threshold">Threshold</option>
                            <option value="tempo">Tempo</option>
                            <option value="tempo_run">Tempo Run</option>
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
                        Add Race Event
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
                            <span class="absolute left-3 top-2 text-slate-500 text-xs"><i class="fa-solid fa-magnifying-glass"></i></span>
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
                                        <span>@{{ formatDate(event.start_at) }}</span>
                                        <span>@{{ event.location_name }}</span>
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
                        <h4 class="text-white font-bold text-xs border-b border-slate-700 pb-2">Hasil Analisis & Jadwal Baru</h4>
                        
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

        <div class="fixed bottom-0 left-4 right-4 z-[900] md:hidden pointer-events-none">
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
