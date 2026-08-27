@auth
@if(auth()->user()->isRunner() || auth()->user()->isCoach())
@php
    $user = auth()->user();
    $currentVdot = (float) ($user->vdot ?? 35);
    $currentPaces = $user->training_paces ?? [];

    $formatSecToPace = function($decimalMin) {
        if (!$decimalMin || $decimalMin <= 0) return '-';
        $mins = floor($decimalMin);
        $secs = round(($decimalMin - $mins) * 60);
        if ($secs >= 60) {
            $mins++;
            $secs = 0;
        }
        return sprintf('%d:%02d', $mins, $secs);
    };

    $initialEasyRange = isset($currentPaces['E_low'], $currentPaces['E_high'])
        ? $formatSecToPace($currentPaces['E_high']) . ' - ' . $formatSecToPace($currentPaces['E_low'])
        : (isset($currentPaces['E']) ? $formatSecToPace($currentPaces['E']) : '6:15 - 6:45');
    $initialM = isset($currentPaces['M']) ? $formatSecToPace($currentPaces['M']) : '5:35';
    $initialT = isset($currentPaces['T']) ? $formatSecToPace($currentPaces['T']) : '5:10';
    $initialI = isset($currentPaces['I']) ? $formatSecToPace($currentPaces['I']) : '4:45';
    $initialR = isset($currentPaces['R']) ? $formatSecToPace($currentPaces['R']) : '4:20';
@endphp

<div id="global-pb-modal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/85 hidden">
    <div class="bg-slate-950 border border-slate-700 w-11/12 max-w-2xl rounded-2xl shadow-2xl overflow-hidden flex flex-col my-6 max-h-[90vh]">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800 bg-slate-900 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-neon">
                    <i class="fas fa-heart-pulse text-base"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Laporan Kondisi Atlet & Personal Best</h3>
                    <p class="text-[11px] text-slate-400">Update feeling fisik harian, parameter tes lari, dan target pace latihan</p>
                </div>
            </div>
            <button type="button" onclick="closeGlobalPbModal()" class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Scrollable Form Body -->
        <form id="global-pb-form" onsubmit="submitGlobalPb(event)" class="p-6 space-y-6 overflow-y-auto custom-scrollbar flex-1 bg-slate-950 text-slate-200">
            @csrf

            <div id="global-pb-alert" class="hidden p-3.5 rounded-xl text-xs font-semibold"></div>

            <!-- SECTION 1: LAPORAN KONDISI ATLET (FEELING & KELUHAN) -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-check text-neon text-xs"></i>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Kondisi & Kesiapan Fisik Hari Ini</h4>
                    </div>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold">Laporan Harian</span>
                </div>

                <!-- Feeling Selector -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-2">Bagaimana kondisi tubuh dan fisik Anda saat ini?</label>
                    <input type="hidden" name="feeling" id="global_pb_feeling" value="good">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5" id="feeling-btn-group">
                        <button type="button" onclick="selectFeeling('strong')" data-feeling="strong" class="feeling-btn flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-slate-800 bg-slate-950 text-left hover:border-emerald-500/60 transition text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0"></span>
                            <span class="font-semibold text-white">Sangat Prima</span>
                        </button>
                        <button type="button" onclick="selectFeeling('good')" data-feeling="good" class="feeling-btn active flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-neon bg-slate-800 text-left transition text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-neon shrink-0"></span>
                            <span class="font-semibold text-white">Bugar & Siap</span>
                        </button>
                        <button type="button" onclick="selectFeeling('average')" data-feeling="average" class="feeling-btn flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-slate-800 bg-slate-950 text-left hover:border-slate-600 transition text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-400 shrink-0"></span>
                            <span class="font-semibold text-white">Normal</span>
                        </button>
                        <button type="button" onclick="selectFeeling('tired')" data-feeling="tired" class="feeling-btn flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-slate-800 bg-slate-950 text-left hover:border-amber-500/60 transition text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shrink-0"></span>
                            <span class="font-semibold text-white">Lelah / Kurang Tidur</span>
                        </button>
                        <button type="button" onclick="selectFeeling('sore')" data-feeling="sore" class="feeling-btn flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-slate-800 bg-slate-950 text-left hover:border-orange-500/60 transition text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-400 shrink-0"></span>
                            <span class="font-semibold text-white">Nyeri Otot</span>
                        </button>
                        <button type="button" onclick="selectFeeling('injured')" data-feeling="injured" class="feeling-btn flex items-center gap-2.5 px-3 py-2.5 rounded-xl border border-slate-800 bg-slate-950 text-left hover:border-red-500/60 transition text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400 shrink-0"></span>
                            <span class="font-semibold text-white">Cedera / Terbatas</span>
                        </button>
                    </div>
                </div>

                <!-- Notes / Keluhan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Catatan Fisik / Keluhan Khusus (Opsional)</label>
                    <textarea name="physical_notes" id="global_pb_notes" rows="2" placeholder="Contoh: Betis kanan terasa kencang setelah tempo run kemarin, butuh recovery run ringan." class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-white text-xs focus:outline-none focus:border-neon transition-colors placeholder:text-slate-500 resize-none"></textarea>
                </div>
            </div>

            <!-- SECTION 2: PERSONAL BEST & TEST BENCHMARK -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-stopwatch text-neon text-xs"></i>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Personal Best & Parameter Tes</h4>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">Format: HH:MM:SS atau Meter</span>
                </div>

                <!-- 12m Cooper & 15m Balke -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Cooper Test 12-Menit (Meter)</label>
                        <input type="number" name="pb_cooper" id="global_pb_cooper" value="{{ $user->pb_cooper }}" oninput="handlePbInputChange()" min="0" max="10000" placeholder="e.g. 2650" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-500">
                        <span class="text-[10px] text-slate-500 mt-1 block">Jarak tempuh lari maksimal selama 12 menit</span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Balke Test 15-Menit (Meter)</label>
                        <input type="number" name="pb_balke" id="global_pb_balke" value="{{ $user->pb_balke }}" oninput="handlePbInputChange()" min="0" max="10000" placeholder="e.g. 3200" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-500">
                        <span class="text-[10px] text-slate-500 mt-1 block">Jarak tempuh lari maksimal selama 15 menit</span>
                    </div>
                </div>

                <!-- 5K, 10K, HM, FM -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">5K PB (HH:MM:SS / MM:SS)</label>
                        <input type="text" name="pb_5k" id="global_pb_5k" value="{{ $user->pb_5k }}" oninput="handlePbInputChange()" placeholder="00:25:00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">10K PB (HH:MM:SS / MM:SS)</label>
                        <input type="text" name="pb_10k" id="global_pb_10k" value="{{ $user->pb_10k }}" oninput="handlePbInputChange()" placeholder="00:52:00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Half Marathon / 21K (HH:MM:SS)</label>
                        <input type="text" name="pb_hm" id="global_pb_hm" value="{{ $user->pb_hm }}" oninput="handlePbInputChange()" placeholder="01:55:00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Full Marathon / 42K (HH:MM:SS)</label>
                        <input type="text" name="pb_fm" id="global_pb_fm" value="{{ $user->pb_fm }}" oninput="handlePbInputChange()" placeholder="04:15:00" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-white text-xs font-mono focus:outline-none focus:border-neon transition-colors placeholder:text-slate-500">
                    </div>
                </div>
            </div>

            <!-- SECTION 3: REKOMENDASI PACE TRAINING (JACK DANIELS VDOT) -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-gauge-high text-neon text-xs"></i>
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">Target Pace Latihan</h4>
                    </div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-950 border border-slate-800 text-xs font-bold text-white">
                        <span class="text-slate-400 font-normal text-[11px]">VDOT:</span>
                        <span id="pb-display-vdot" class="text-neon font-mono">{{ number_format($currentVdot, 1) }}</span>
                    </div>
                </div>

                <!-- Pace Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <!-- Easy Pace -->
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-emerald-400 uppercase tracking-wide">Easy (E) Pace</span>
                            <span class="text-[10px] text-slate-400">Recovery & Aerobic</span>
                        </div>
                        <div id="pb-pace-easy" class="text-base font-black font-mono text-white">{{ $initialEasyRange }} <span class="text-[11px] font-normal text-slate-400">/km</span></div>
                        <p class="text-[10px] text-slate-400 leading-tight">Untuk long run, pemulihan, dan membangun kapasitas aerobik dasar.</p>
                    </div>

                    <!-- Marathon Pace -->
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-cyan-400 uppercase tracking-wide">Marathon (M) Pace</span>
                            <span class="text-[10px] text-slate-400">Steady Endurance</span>
                        </div>
                        <div id="pb-pace-marathon" class="text-base font-black font-mono text-white">{{ $initialM }} <span class="text-[11px] font-normal text-slate-400">/km</span></div>
                        <p class="text-[10px] text-slate-400 leading-tight">Target ritme balap marathon & latihan daya tahan berkelanjutan.</p>
                    </div>

                    <!-- Threshold Pace -->
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-amber-400 uppercase tracking-wide">Threshold (T) Pace</span>
                            <span class="text-[10px] text-slate-400">Lactate Threshold</span>
                        </div>
                        <div id="pb-pace-threshold" class="text-base font-black font-mono text-white">{{ $initialT }} <span class="text-[11px] font-normal text-slate-400">/km</span></div>
                        <p class="text-[10px] text-slate-400 leading-tight">Tempo run 20-30 menit untuk menggeser ambang asam laktat.</p>
                    </div>

                    <!-- Interval Pace -->
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-orange-400 uppercase tracking-wide">Interval (I) Pace</span>
                            <span class="text-[10px] text-slate-400">VO2Max Booster</span>
                        </div>
                        <div id="pb-pace-interval" class="text-base font-black font-mono text-white">{{ $initialI }} <span class="text-[11px] font-normal text-slate-400">/km</span></div>
                        <p class="text-[10px] text-slate-400 leading-tight">Interval 800m - 1200m untuk meningkatkan kapasitas VO2Max.</p>
                    </div>

                    <!-- Repetition Pace -->
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-rose-400 uppercase tracking-wide">Repetition (R) Pace</span>
                            <span class="text-[10px] text-slate-400">Speed & Form</span>
                        </div>
                        <div id="pb-pace-repetition" class="text-base font-black font-mono text-white">{{ $initialR }} <span class="text-[11px] font-normal text-slate-400">/km</span></div>
                        <p class="text-[10px] text-slate-400 leading-tight">Latihan sprint pendek 200m - 400m dengan istirahat penuh.</p>
                    </div>

                    <!-- VDOT Summary Box -->
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex flex-col justify-between">
                        <div class="text-[11px] font-bold text-neon uppercase tracking-wide">Kalkulasi Otomatis</div>
                        <p class="text-[10px] text-slate-400 mt-1">Pace latihan langsung diperbarui saat Anda mengubah catatan waktu atau hasil tes.</p>
                        <div class="mt-2 text-[10px] text-slate-500 font-mono">Formula: Jack Daniels VDOT</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 border-t border-slate-800 flex items-center justify-end gap-3 shrink-0">
                <button type="button" onclick="closeGlobalPbModal()" class="px-4 py-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors text-xs font-bold">
                    Tutup
                </button>
                <button type="submit" id="global-pb-submit-btn" class="px-5 py-2.5 rounded-xl bg-neon text-dark font-black hover:bg-lime-400 transition-all text-xs flex items-center gap-2 shadow-lg shadow-neon/10">
                    <i class="fas fa-save text-xs"></i>
                    <span>Simpan & Perbarui Pace</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function selectFeeling(feeling) {
        document.getElementById('global_pb_feeling').value = feeling;
        document.querySelectorAll('#feeling-btn-group .feeling-btn').forEach(btn => {
            if (btn.getAttribute('data-feeling') === feeling) {
                btn.classList.add('border-neon', 'bg-slate-800');
                btn.classList.remove('border-slate-800', 'bg-slate-950');
            } else {
                btn.classList.remove('border-neon', 'bg-slate-800');
                btn.classList.add('border-slate-800', 'bg-slate-950');
            }
        });
    }

    function openGlobalPbModal() {
        const modal = document.getElementById('global-pb-modal');
        if (modal) {
            modal.classList.remove('hidden');
            const alertBox = document.getElementById('global-pb-alert');
            if (alertBox) alertBox.classList.add('hidden');
            handlePbInputChange();
        }
    }

    function closeGlobalPbModal() {
        const modal = document.getElementById('global-pb-modal');
        if (modal) modal.classList.add('hidden');
    }

    document.getElementById('global-pb-modal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeGlobalPbModal();
        }
    });

    // Parse time string (HH:MM:SS or MM:SS) to total seconds
    function parseTimeToSeconds(timeStr) {
        if (!timeStr) return null;
        timeStr = timeStr.trim();
        if (!timeStr) return null;
        const parts = timeStr.split(':').map(Number);
        if (parts.some(isNaN)) return null;
        if (parts.length === 3) {
            return parts[0] * 3600 + parts[1] * 60 + parts[2];
        } else if (parts.length === 2) {
            return parts[0] * 60 + parts[1];
        }
        return null;
    }

    // Format time input to standard HH:MM:SS
    function normalizeTimeFormat(val) {
        if (!val) return null;
        val = val.trim();
        if (!val) return null;
        const parts = val.split(':');
        if (parts.length === 2) {
            return `00:${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
        } else if (parts.length === 3) {
            return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}:${parts[2].padStart(2, '0')}`;
        }
        return val;
    }

    // Jack Daniels VDOT calculation from race distance & time in seconds
    function calcVdotFromRace(distMeters, timeSec) {
        if (!distMeters || !timeSec || timeSec <= 0) return 0;
        const velocity = distMeters / (timeSec / 60); // m/min
        const vo2 = -4.60 + (0.182258 * velocity) + (0.000104 * Math.pow(velocity, 2));
        const t = timeSec / 60; // minutes
        const percentMax = 0.8 + 0.1894393 * Math.exp(-0.012778 * t) + 0.2989558 * Math.exp(-0.1932605 * t);
        if (percentMax <= 0) return 0;
        return Math.max(10, Math.min(85, vo2 / percentMax));
    }

    function calcVdotFromCooper(meters) {
        if (!meters || meters <= 500) return 0;
        return Math.max(10, Math.min(85, (meters - 504.9) / 44.73));
    }

    function calcVdotFromBalke(meters) {
        if (!meters || meters <= 500) return 0;
        return Math.max(10, Math.min(85, ((meters / 15) - 133) * 0.172 + 33.3));
    }

    function calcTrainingPaces(vdot) {
        if (!vdot || vdot < 10) return null;
        const a = 0.000104;
        const b = 0.182258;
        const c = -4.6 - vdot;
        const vVO2max = (-b + Math.sqrt(Math.pow(b, 2) - 4 * a * c)) / (2 * a);
        if (!vVO2max || isNaN(vVO2max)) return null;

        const formatPace = (velocity) => {
            if (!velocity || velocity <= 0) return '-';
            const minPerKm = 1000 / velocity;
            let mins = Math.floor(minPerKm);
            let secs = Math.round((minPerKm - mins) * 60);
            if (secs >= 60) {
                mins++;
                secs = 0;
            }
            return `${mins}:${String(secs).padStart(2, '0')}`;
        };

        return {
            vdot: vdot.toFixed(1),
            easy_range: `${formatPace(vVO2max * 0.72)} - ${formatPace(vVO2max * 0.66)}`,
            marathon: formatPace(vVO2max * 0.82),
            threshold: formatPace(vVO2max * 0.88),
            interval: formatPace(vVO2max * 0.97),
            repetition: formatPace(vVO2max * 1.05),
        };
    }

    function handlePbInputChange() {
        const pb5kSec = parseTimeToSeconds(document.getElementById('global_pb_5k')?.value);
        const pb10kSec = parseTimeToSeconds(document.getElementById('global_pb_10k')?.value);
        const pbHmSec = parseTimeToSeconds(document.getElementById('global_pb_hm')?.value);
        const pbFmSec = parseTimeToSeconds(document.getElementById('global_pb_fm')?.value);
        const cooperMeters = parseInt(document.getElementById('global_pb_cooper')?.value) || 0;
        const balkeMeters = parseInt(document.getElementById('global_pb_balke')?.value) || 0;

        let maxVdot = 0;

        if (pb5kSec) maxVdot = Math.max(maxVdot, calcVdotFromRace(5000, pb5kSec));
        if (pb10kSec) maxVdot = Math.max(maxVdot, calcVdotFromRace(10000, pb10kSec));
        if (pbHmSec) maxVdot = Math.max(maxVdot, calcVdotFromRace(21097.5, pbHmSec));
        if (pbFmSec) maxVdot = Math.max(maxVdot, calcVdotFromRace(42195, pbFmSec));
        if (cooperMeters) maxVdot = Math.max(maxVdot, calcVdotFromCooper(cooperMeters));
        if (balkeMeters) maxVdot = Math.max(maxVdot, calcVdotFromBalke(balkeMeters));

        if (maxVdot < 10) {
            maxVdot = {{ $currentVdot }};
        }

        const paces = calcTrainingPaces(maxVdot);
        if (paces) {
            const vdotEl = document.getElementById('pb-display-vdot');
            const easyEl = document.getElementById('pb-pace-easy');
            const marathonEl = document.getElementById('pb-pace-marathon');
            const thresholdEl = document.getElementById('pb-pace-threshold');
            const intervalEl = document.getElementById('pb-pace-interval');
            const repetitionEl = document.getElementById('pb-pace-repetition');

            if (vdotEl) vdotEl.textContent = paces.vdot;
            if (easyEl) easyEl.innerHTML = `${paces.easy_range} <span class="text-[11px] font-normal text-slate-400">/km</span>`;
            if (marathonEl) marathonEl.innerHTML = `${paces.marathon} <span class="text-[11px] font-normal text-slate-400">/km</span>`;
            if (thresholdEl) thresholdEl.innerHTML = `${paces.threshold} <span class="text-[11px] font-normal text-slate-400">/km</span>`;
            if (intervalEl) intervalEl.innerHTML = `${paces.interval} <span class="text-[11px] font-normal text-slate-400">/km</span>`;
            if (repetitionEl) repetitionEl.innerHTML = `${paces.repetition} <span class="text-[11px] font-normal text-slate-400">/km</span>`;
        }
    }

    async function submitGlobalPb(e) {
        e.preventDefault();
        const btn = document.getElementById('global-pb-submit-btn');
        const alertBox = document.getElementById('global-pb-alert');

        const feeling = document.getElementById('global_pb_feeling')?.value || 'good';
        const notes = document.getElementById('global_pb_notes')?.value || '';
        const pb5k = normalizeTimeFormat(document.getElementById('global_pb_5k')?.value);
        const pb10k = normalizeTimeFormat(document.getElementById('global_pb_10k')?.value);
        const pbHm = normalizeTimeFormat(document.getElementById('global_pb_hm')?.value);
        const pbFm = normalizeTimeFormat(document.getElementById('global_pb_fm')?.value);
        const pbCooper = document.getElementById('global_pb_cooper')?.value ? parseInt(document.getElementById('global_pb_cooper').value) : null;
        const pbBalke = document.getElementById('global_pb_balke')?.value ? parseInt(document.getElementById('global_pb_balke').value) : null;

        const payload = { feeling, physical_notes: notes };
        if (pb5k) payload.pb_5k = pb5k;
        if (pb10k) payload.pb_10k = pb10k;
        if (pbHm) payload.pb_hm = pbHm;
        if (pbFm) payload.pb_fm = pbFm;
        if (pbCooper) payload.pb_cooper = pbCooper;
        if (pbBalke) payload.pb_balke = pbBalke;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> <span>Menyimpan...</span>';
        alertBox.className = 'hidden';

        try {
            const resp = await fetch('{{ route("runner.calendar.update-pb") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const res = await resp.json();

            if (res.success || resp.ok) {
                alertBox.className = 'p-3.5 rounded-xl text-xs font-semibold bg-emerald-950 border border-emerald-800 text-emerald-300 block';
                alertBox.textContent = 'Laporan kondisi & Personal Best berhasil diperbarui! Target pace latihan telah disesuaikan.';

                setTimeout(() => {
                    closeGlobalPbModal();
                    if (window.location.pathname.includes('/runner/dashboard') || window.location.pathname.includes('/runner/calendar') || window.location.pathname.includes('/profile')) {
                        window.location.reload();
                    }
                }, 1200);
            } else {
                alertBox.className = 'p-3.5 rounded-xl text-xs font-semibold bg-red-950 border border-red-800 text-red-300 block';
                alertBox.textContent = res.message || 'Gagal memperbarui Personal Best. Periksa format waktu (HH:MM:SS).';
            }
        } catch (err) {
            alertBox.className = 'p-3.5 rounded-xl text-xs font-semibold bg-red-950 border border-red-800 text-red-300 block';
            alertBox.textContent = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save text-xs"></i> <span>Simpan & Perbarui Pace</span>';
        }
    }

    // Auto-prompt on dashboard load if not prompted in current session
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.pathname.includes('/runner/dashboard')) {
            const hasShownSession = sessionStorage.getItem('pb_checkin_shown_session');
            if (!hasShownSession) {
                sessionStorage.setItem('pb_checkin_shown_session', 'true');
                setTimeout(() => {
                    openGlobalPbModal();
                }, 1000);
            }
        }
    });
</script>
@endif
@endauth
