@auth
@if(auth()->user()->isRunner() || auth()->user()->isCoach())
@php
    $user = auth()->user();
    $currentVdot = (float) ($user->vdot ?? 35);
    $currentPaces = $user->training_paces ?? [];

    // Check last updated / created date of PB (every 1 week / 7 days)
    $lastPbDate = $user->updated_at ?: $user->created_at;
    $daysSinceLastPb = $lastPbDate ? (int) $lastPbDate->diffInDays(now()) : 999;
    $hasNeverSetPb = empty($user->pb_5k) && empty($user->pb_10k) && empty($user->pb_hm) && empty($user->pb_fm) && empty($user->pb_cooper) && empty($user->pb_balke);
    $isPbOlderThanOneWeek = $daysSinceLastPb >= 7;
    $shouldPromptWeeklyPb = $isPbOlderThanOneWeek || $hasNeverSetPb;
    $lastPbDateFormatted = $lastPbDate ? $lastPbDate->translatedFormat('d M Y') : '-';

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


<style>
    :root {
        --perf-bg: #07101c;
        --perf-panel: #0b1522;
        --perf-panel-soft: #0f1b2a;
        --perf-line: rgba(255,255,255,.09);
        --perf-line-strong: rgba(255,255,255,.16);
        --perf-text: #f7f9fb;
        --perf-muted: #8794a6;
        --perf-accent: #b8ff00;
        --perf-danger: #fb7185;
        --perf-warning: #fbbf24;
    }

    #global-pb-modal {
        font-variant-numeric: tabular-nums;
    }

    .performance-modal {
        width: min(1180px, calc(100vw - 32px));
        max-height: min(94vh, 920px);
        background: var(--perf-panel);
        border: 1px solid var(--perf-line-strong);
        box-shadow: 0 34px 100px rgba(0,0,0,.58);
    }

    .performance-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.18) transparent;
    }

    .performance-scroll::-webkit-scrollbar { width: 8px; }
    .performance-scroll::-webkit-scrollbar-track { background: transparent; }
    .performance-scroll::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,.14);
        border-radius: 999px;
    }

    .performance-overline {
        display: inline-flex;
        align-items: center;
        gap: .65rem;
        color: var(--perf-accent);
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .performance-overline::before {
        content: "";
        width: 30px;
        height: 2px;
        background: var(--perf-accent);
    }

    .performance-step {
        color: rgba(255,255,255,.28);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .performance-section {
        padding-top: 1.75rem;
        border-top: 1px solid var(--perf-line);
    }

    .performance-label {
        display: block;
        margin-bottom: .5rem;
        color: #c9d1dc;
        font-size: 11px;
        font-weight: 700;
    }

    .performance-input,
    .performance-textarea {
        width: 100%;
        border: 1px solid var(--perf-line);
        border-radius: 3px;
        background: rgba(255,255,255,.018);
        color: #fff;
        outline: none;
        transition: border-color .18s ease, background .18s ease, box-shadow .18s ease;
    }

    .performance-input {
        min-height: 46px;
        padding: 0 13px;
        font-size: 12px;
    }

    .performance-textarea {
        min-height: 84px;
        padding: 12px 13px;
        font-size: 12px;
        resize: vertical;
    }

    .performance-input::placeholder,
    .performance-textarea::placeholder {
        color: rgba(255,255,255,.24);
    }

    .performance-input:focus,
    .performance-textarea:focus {
        border-color: rgba(184,255,0,.58);
        background: rgba(255,255,255,.028);
        box-shadow: 0 0 0 3px rgba(184,255,0,.055);
    }

    .performance-help {
        margin-top: .42rem;
        color: rgba(255,255,255,.31);
        font-size: 9.5px;
        line-height: 1.45;
    }

    .feeling-btn {
        position: relative;
        min-height: 72px;
        padding: 11px 12px;
        border: 1px solid var(--perf-line);
        border-radius: 3px;
        background: rgba(255,255,255,.018);
        text-align: left;
        transition: border-color .18s ease, background .18s ease;
    }

    .feeling-btn:hover {
        border-color: rgba(255,255,255,.22);
        background: rgba(255,255,255,.03);
    }

    .feeling-btn.is-active {
        border-color: var(--perf-accent);
        background: rgba(184,255,0,.045);
    }

    .feeling-btn.is-active::after {
        content: "";
        position: absolute;
        left: -1px;
        top: -1px;
        bottom: -1px;
        width: 3px;
        background: var(--perf-accent);
    }

    .feeling-btn.is-active .feeling-title {
        color: var(--perf-accent);
    }

    .performance-status {
        border-left: 3px solid var(--perf-accent);
        background: rgba(255,255,255,.018);
    }

    .performance-status.is-due {
        border-left-color: var(--perf-warning);
    }

    .performance-score {
        background: var(--perf-accent);
        color: #07101c;
    }

    .pace-table {
        width: 100%;
        border-collapse: collapse;
    }

    .pace-table tr {
        border-bottom: 1px solid var(--perf-line);
    }

    .pace-table tr:last-child {
        border-bottom: 0;
    }

    .pace-table td {
        padding: 15px 0;
        vertical-align: middle;
    }

    .pace-tag {
        display: inline-flex;
        width: 28px;
        height: 28px;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--perf-line-strong);
        color: rgba(255,255,255,.7);
        font-size: 10px;
        font-weight: 900;
    }

    .pace-name {
        color: #fff;
        font-size: 11px;
        font-weight: 800;
    }

    .pace-sub {
        margin-top: 2px;
        color: rgba(255,255,255,.28);
        font-size: 9px;
    }

    .pace-value {
        color: #fff;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 15px;
        font-weight: 800;
        text-align: right;
        white-space: nowrap;
        letter-spacing: -.02em;
    }

    .pace-unit {
        color: rgba(255,255,255,.28);
        font-size: 9px;
        font-weight: 500;
    }

    .performance-alert {
        margin: 18px 24px 0;
        padding: 12px 14px;
        border: 1px solid;
        font-size: 11px;
        font-weight: 700;
    }

    .performance-alert--success {
        color: #a7f3d0;
        border-color: rgba(16,185,129,.30);
        background: rgba(16,185,129,.06);
    }

    .performance-alert--error {
        color: #fecdd3;
        border-color: rgba(244,63,94,.30);
        background: rgba(244,63,94,.06);
    }

    .performance-btn-primary,
    .performance-btn-secondary {
        min-height: 44px;
        border-radius: 3px;
        padding: 0 17px;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        transition: background .18s ease, border-color .18s ease, color .18s ease;
    }

    .performance-btn-primary {
        background: var(--perf-accent);
        border: 1px solid var(--perf-accent);
        color: #07101c;
    }

    .performance-btn-primary:hover {
        background: #d2ff65;
        border-color: #d2ff65;
    }

    .performance-btn-primary:disabled {
        cursor: wait;
        opacity: .55;
    }

    .performance-btn-secondary {
        background: transparent;
        border: 1px solid var(--perf-line-strong);
        color: rgba(255,255,255,.58);
    }

    .performance-btn-secondary:hover {
        border-color: rgba(255,255,255,.28);
        color: #fff;
        background: rgba(255,255,255,.025);
    }

    @media (max-width: 899px) {
        .performance-modal {
            width: 100%;
            max-height: 100dvh;
            height: 100dvh;
            border: 0;
        }
    }
</style>

<div id="global-pb-modal" class="fixed inset-0 z-[9999] hidden flex items-center justify-center bg-black/80 backdrop-blur-[2px] p-0 md:p-5">
    <div class="performance-modal flex flex-col overflow-hidden">

        <!-- HEADER -->
        <header class="shrink-0 border-b border-white/10 px-5 py-4 md:px-7 md:py-5">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <div class="performance-overline">Performance check-in</div>

                    <div class="mt-3 flex flex-wrap items-end gap-x-4 gap-y-2">
                        <h2 class="text-xl md:text-[1.65rem] font-black tracking-[-.035em] text-white">
                            Athlete Readiness &amp; Pace
                        </h2>

                        <div class="performance-score inline-flex items-center gap-2 px-2.5 py-1">
                            <span class="text-[9px] font-black uppercase tracking-[.12em]">VDOT</span>
                            <strong id="pb-display-vdot" class="font-mono text-sm">{{ number_format($currentVdot, 1) }}</strong>
                        </div>
                    </div>

                    <p class="mt-2 max-w-2xl text-[11px] leading-relaxed text-white/38">
                        Check-in mingguan untuk memperbarui kondisi, benchmark, dan pace latihan berdasarkan performa terkini.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="closeGlobalPbModal()"
                    aria-label="Tutup"
                    class="w-9 h-9 shrink-0 border border-white/10 text-white/40 hover:text-white hover:border-white/25 transition-colors flex items-center justify-center"
                >
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        </header>

        <!-- STATUS STRIP -->
        <div class="shrink-0 px-5 md:px-7 py-3 border-b border-white/10 bg-black/10">
            <div class="performance-status {{ $isPbOlderThanOneWeek ? 'is-due' : '' }} pl-3.5 py-1">
                @if($hasNeverSetPb)
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="text-[10px] font-black uppercase tracking-[.13em] text-white">Initial setup</span>
                        <span class="text-[10px] text-white/35">Belum ada benchmark performa. Tambahkan satu hasil tes atau race terbaru.</span>
                    </div>
                @elseif($isPbOlderThanOneWeek)
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="text-[10px] font-black uppercase tracking-[.13em] text-amber-300">Weekly review due</span>
                        <span class="text-[10px] text-white/40">Terakhir diperbarui {{ $lastPbDateFormatted }} · {{ $daysSinceLastPb }} hari lalu</span>
                    </div>
                @else
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="text-[10px] font-black uppercase tracking-[.13em] text-[#B8FF00]">Up to date</span>
                        <span class="text-[10px] text-white/40">Terakhir diperbarui {{ $lastPbDateFormatted }} · review berikutnya {{ 7 - $daysSinceLastPb }} hari lagi</span>
                    </div>
                @endif
            </div>
        </div>

        <form id="global-pb-form" onsubmit="submitGlobalPb(event)" class="performance-scroll min-h-0 flex-1 overflow-y-auto">
            @csrf

            <div id="global-pb-alert" class="hidden"></div>

            <div class="grid xl:grid-cols-[minmax(0,1fr)_390px]">

                <!-- INPUT COLUMN -->
                <div class="px-5 py-6 md:px-7 md:py-7 xl:border-r xl:border-white/10">
                    <section>
                        <div class="flex items-end justify-between gap-5">
                            <div>
                                <div class="performance-step">01 / Readiness</div>
                                <h3 class="mt-1 text-sm font-black uppercase tracking-[.055em] text-white">
                                    Kondisi hari ini
                                </h3>
                            </div>
                            <span class="text-[9px] uppercase tracking-[.12em] text-white/24">Required</span>
                        </div>

                        <div class="mt-5">
                            <input type="hidden" name="feeling" id="global_pb_feeling" value="good">

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2" id="feeling-btn-group">
                                <button type="button" onclick="selectFeeling('strong')" data-feeling="strong" aria-pressed="false" class="feeling-btn">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        <span class="feeling-title text-[11px] font-black text-white">Sangat Prima</span>
                                    </div>
                                    <div class="mt-2 text-[9px] leading-snug text-white/28">Energi tinggi, siap sesi kualitas.</div>
                                </button>

                                <button type="button" onclick="selectFeeling('good')" data-feeling="good" aria-pressed="true" class="feeling-btn is-active">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#B8FF00]"></span>
                                        <span class="feeling-title text-[11px] font-black text-white">Bugar &amp; Siap</span>
                                    </div>
                                    <div class="mt-2 text-[9px] leading-snug text-white/28">Siap menjalankan beban normal.</div>
                                </button>

                                <button type="button" onclick="selectFeeling('average')" data-feeling="average" aria-pressed="false" class="feeling-btn">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span class="feeling-title text-[11px] font-black text-white">Normal</span>
                                    </div>
                                    <div class="mt-2 text-[9px] leading-snug text-white/28">Cukup baik, pantau respons tubuh.</div>
                                </button>

                                <button type="button" onclick="selectFeeling('tired')" data-feeling="tired" aria-pressed="false" class="feeling-btn">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        <span class="feeling-title text-[11px] font-black text-white">Lelah</span>
                                    </div>
                                    <div class="mt-2 text-[9px] leading-snug text-white/28">Fatigue atau kualitas tidur rendah.</div>
                                </button>

                                <button type="button" onclick="selectFeeling('sore')" data-feeling="sore" aria-pressed="false" class="feeling-btn">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                                        <span class="feeling-title text-[11px] font-black text-white">Nyeri Otot</span>
                                    </div>
                                    <div class="mt-2 text-[9px] leading-snug text-white/28">Pertimbangkan recovery atau easy day.</div>
                                </button>

                                <button type="button" onclick="selectFeeling('injured')" data-feeling="injured" aria-pressed="false" class="feeling-btn">
                                    <div class="flex items-center gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                        <span class="feeling-title text-[11px] font-black text-white">Terbatas</span>
                                    </div>
                                    <div class="mt-2 text-[9px] leading-snug text-white/28">Ada pain atau concern cedera.</div>
                                </button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="performance-label" for="global_pb_notes">Catatan fisik <span class="font-normal text-white/28">— opsional</span></label>
                            <textarea
                                name="physical_notes"
                                id="global_pb_notes"
                                rows="3"
                                placeholder="Contoh: betis kanan terasa kencang setelah tempo run kemarin."
                                class="performance-textarea"
                            ></textarea>
                        </div>
                    </section>

                    <section class="performance-section mt-7">
                        <div>
                            <div class="performance-step">02 / Benchmark</div>
                            <h3 class="mt-1 text-sm font-black uppercase tracking-[.055em] text-white">
                                Data performa
                            </h3>
                            <p class="mt-1.5 max-w-xl text-[10px] leading-relaxed text-white/31">
                                Tidak perlu mengisi semuanya. Sistem akan memakai benchmark terbaik yang tersedia untuk estimasi VDOT.
                            </p>
                        </div>

                        <div class="mt-5 grid md:grid-cols-2 gap-5">
                            <div class="space-y-4">
                                <div class="text-[9px] font-black uppercase tracking-[.13em] text-white/26">Field test</div>

                                <div>
                                    <label class="performance-label" for="global_pb_cooper">Cooper 12 menit</label>
                                    <div class="relative">
                                        <input type="number" name="pb_cooper" id="global_pb_cooper" value="{{ $user->pb_cooper }}" oninput="handlePbInputChange()" min="0" max="10000" placeholder="2650" class="performance-input pr-16 font-mono">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] uppercase tracking-[.1em] text-white/28">meter</span>
                                    </div>
                                    <div class="performance-help">Jarak maksimal selama 12 menit.</div>
                                </div>

                                <div>
                                    <label class="performance-label" for="global_pb_balke">Balke 15 menit</label>
                                    <div class="relative">
                                        <input type="number" name="pb_balke" id="global_pb_balke" value="{{ $user->pb_balke }}" oninput="handlePbInputChange()" min="0" max="10000" placeholder="3200" class="performance-input pr-16 font-mono">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] uppercase tracking-[.1em] text-white/28">meter</span>
                                    </div>
                                    <div class="performance-help">Jarak maksimal selama 15 menit.</div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="text-[9px] font-black uppercase tracking-[.13em] text-white/26">Race result</div>
                                    <div class="text-[9px] text-white/22">HH:MM:SS / MM:SS</div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="performance-label" for="global_pb_5k">5K</label>
                                        <input type="text" name="pb_5k" id="global_pb_5k" value="{{ $user->pb_5k }}" oninput="handlePbInputChange()" placeholder="00:25:00" class="performance-input font-mono">
                                    </div>

                                    <div>
                                        <label class="performance-label" for="global_pb_10k">10K</label>
                                        <input type="text" name="pb_10k" id="global_pb_10k" value="{{ $user->pb_10k }}" oninput="handlePbInputChange()" placeholder="00:52:00" class="performance-input font-mono">
                                    </div>

                                    <div>
                                        <label class="performance-label" for="global_pb_hm">Half Marathon</label>
                                        <input type="text" name="pb_hm" id="global_pb_hm" value="{{ $user->pb_hm }}" oninput="handlePbInputChange()" placeholder="01:55:00" class="performance-input font-mono">
                                    </div>

                                    <div>
                                        <label class="performance-label" for="global_pb_fm">Marathon</label>
                                        <input type="text" name="pb_fm" id="global_pb_fm" value="{{ $user->pb_fm }}" oninput="handlePbInputChange()" placeholder="04:15:00" class="performance-input font-mono">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- LIVE RESULT COLUMN -->
                <aside class="bg-black/[.09] px-5 py-6 md:px-7 md:py-7">
                    <div class="xl:sticky xl:top-0">
                        <div class="flex items-end justify-between gap-5">
                            <div>
                                <div class="performance-step">03 / Training output</div>
                                <h3 class="mt-1 text-sm font-black uppercase tracking-[.055em] text-white">
                                    Pace rekomendasi
                                </h3>
                            </div>
                            <span class="text-[9px] uppercase tracking-[.12em] text-white/24">min / km</span>
                        </div>

                        <table class="pace-table mt-4">
                            <tbody>
                                <tr>
                                    <td class="pr-3">
                                        <div class="flex items-center gap-3">
                                            <span class="pace-tag">E</span>
                                            <div>
                                                <div class="pace-name">Easy</div>
                                                <div class="pace-sub">Recovery / aerobic</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td id="pb-pace-easy" class="pace-value">{{ $initialEasyRange }} <span class="pace-unit">/km</span></td>
                                </tr>

                                <tr>
                                    <td class="pr-3">
                                        <div class="flex items-center gap-3">
                                            <span class="pace-tag">M</span>
                                            <div>
                                                <div class="pace-name">Marathon</div>
                                                <div class="pace-sub">Steady endurance</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td id="pb-pace-marathon" class="pace-value">{{ $initialM }} <span class="pace-unit">/km</span></td>
                                </tr>

                                <tr>
                                    <td class="pr-3">
                                        <div class="flex items-center gap-3">
                                            <span class="pace-tag">T</span>
                                            <div>
                                                <div class="pace-name">Threshold</div>
                                                <div class="pace-sub">Lactate threshold</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td id="pb-pace-threshold" class="pace-value">{{ $initialT }} <span class="pace-unit">/km</span></td>
                                </tr>

                                <tr>
                                    <td class="pr-3">
                                        <div class="flex items-center gap-3">
                                            <span class="pace-tag">I</span>
                                            <div>
                                                <div class="pace-name">Interval</div>
                                                <div class="pace-sub">VO₂max</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td id="pb-pace-interval" class="pace-value">{{ $initialI }} <span class="pace-unit">/km</span></td>
                                </tr>

                                <tr>
                                    <td class="pr-3">
                                        <div class="flex items-center gap-3">
                                            <span class="pace-tag">R</span>
                                            <div>
                                                <div class="pace-name">Repetition</div>
                                                <div class="pace-sub">Speed / economy</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td id="pb-pace-repetition" class="pace-value">{{ $initialR }} <span class="pace-unit">/km</span></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="mt-6 border-t border-white/10 pt-5">
                            <div class="text-[9px] font-black uppercase tracking-[.13em] text-[#B8FF00]">
                                Calculation note
                            </div>
                            <p class="mt-2 text-[10px] leading-relaxed text-white/32">
                                Pace berubah secara langsung saat Anda memperbarui benchmark. Estimasi menggunakan formula Jack Daniels VDOT dan memilih nilai performa terbaik yang tersedia.
                            </p>
                        </div>

                        <div class="mt-6 border border-white/10 p-4">
                            <div class="grid grid-cols-[auto_1fr] gap-3">
                                <i class="fas fa-circle-info text-[#B8FF00] text-[11px] mt-0.5"></i>
                                <p class="text-[9.5px] leading-relaxed text-white/34">
                                    Gunakan pace sebagai acuan training. Kondisi fisik harian tetap menjadi pertimbangan utama sebelum menjalankan sesi intensitas tinggi.
                                </p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- FOOTER -->
            <footer class="sticky bottom-0 z-20 border-t border-white/10 bg-[#0b1522]/96 backdrop-blur-md px-5 py-4 md:px-7">
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-[9px] leading-relaxed text-white/23">
                        Weekly athlete check-in · RuangLari Performance
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button
                            type="button"
                            onclick="closeGlobalPbModal()"
                            class="performance-btn-secondary"
                        >
                            Nanti saja
                        </button>

                        <button
                            type="submit"
                            id="global-pb-submit-btn"
                            class="performance-btn-primary inline-flex items-center justify-center gap-2"
                        >
                            <span>Simpan Check-in</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </button>
                    </div>
                </div>
            </footer>
        </form>
    </div>
</div>

<script>
    const shouldPromptWeeklyPb = @json($shouldPromptWeeklyPb);
    const daysSinceLastPb = @json($daysSinceLastPb);

    function selectFeeling(feeling) {
        document.getElementById('global_pb_feeling').value = feeling;
        document.querySelectorAll('#feeling-btn-group .feeling-btn').forEach(btn => {
            const active = btn.getAttribute('data-feeling') === feeling;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function openGlobalPbModal() {
        const modal = document.getElementById('global-pb-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeGlobalPbModal() {
        const modal = document.getElementById('global-pb-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            // Dismiss cooldown: don't auto-prompt again for 24 hours if closed manually
            localStorage.setItem('global_pb_modal_dismissed_time', Date.now().toString());
        }
    }

    function timeToMinutes(timeStr) {
        if (!timeStr) return null;
        const parts = timeStr.trim().split(':').map(Number);
        if (parts.length === 3) {
            return parts[0] * 60 + parts[1] + parts[2] / 60;
        } else if (parts.length === 2) {
            return parts[0] + parts[1] / 60;
        }
        return null;
    }

    function normalizeTimeFormat(timeStr) {
        if (!timeStr) return null;
        const parts = timeStr.trim().split(':').map(Number);
        if (parts.some(isNaN)) return null;
        if (parts.length === 2) {
            return `00:${String(parts[0]).padStart(2, '0')}:${String(parts[1]).padStart(2, '0')}`;
        } else if (parts.length === 3) {
            return `${String(parts[0]).padStart(2, '0')}:${String(parts[1]).padStart(2, '0')}:${String(parts[2]).padStart(2, '0')}`;
        }
        return null;
    }

    function calculateVdotFromTime(distanceMeters, timeMinutes) {
        if (!distanceMeters || !timeMinutes || timeMinutes <= 0) return null;
        const velocity = distanceMeters / timeMinutes; // m/min
        const vo2 = -4.60 + 0.182258 * velocity + 0.000104 * Math.pow(velocity, 2);
        const percentMax = 0.8 + 0.1894393 * Math.exp(-0.012778 * timeMinutes) + 0.2989558 * Math.exp(-0.1932605 * timeMinutes);
        if (percentMax <= 0) return null;
        const vdot = vo2 / percentMax;
        return (vdot > 10 && vdot < 90) ? vdot : null;
    }

    function calculateVdotFromCooper(meters) {
        if (!meters || meters <= 0) return null;
        return calculateVdotFromTime(meters, 12);
    }

    function calculateVdotFromBalke(meters) {
        if (!meters || meters <= 0) return null;
        return calculateVdotFromTime(meters, 15);
    }

    function calculatePacesFromVdot(vdot) {
        if (!vdot || vdot <= 0) return null;

        function getPacePerKm(intensity) {
            const vo2 = vdot * intensity;
            const a = 0.000104;
            const b = 0.182258;
            const c = -4.60 - vo2;
            const discriminant = Math.pow(b, 2) - 4 * a * c;
            if (discriminant < 0) return null;
            const velocity = (-b + Math.sqrt(discriminant)) / (2 * a);
            if (velocity <= 0) return null;
            return 1000 / velocity; // minutes per km (decimal)
        }

        function formatPace(decimalMin) {
            if (!decimalMin || decimalMin <= 0) return '-';
            const mins = floor(decimalMin);
            let secs = round((decimalMin - mins) * 60);
            let finalMins = mins;
            if (secs >= 60) {
                finalMins++;
                secs = 0;
            }
            return `${finalMins}:${String(secs).padStart(2, '0')}`;
        }

        function floor(n) { return Math.floor(n); }
        function round(n) { return Math.round(n); }

        const eLow = getPacePerKm(0.65);
        const eHigh = getPacePerKm(0.74);
        const mPace = getPacePerKm(0.80);
        const tPace = getPacePerKm(0.88);
        const iPace = getPacePerKm(0.975);
        const rPace = getPacePerKm(1.05);

        return {
            easy_range: `${formatPace(eHigh)} - ${formatPace(eLow)}`,
            marathon: formatPace(mPace),
            threshold: formatPace(tPace),
            interval: formatPace(iPace),
            repetition: formatPace(rPace)
        };
    }

    function handlePbInputChange() {
        const pb5k = timeToMinutes(document.getElementById('global_pb_5k')?.value);
        const pb10k = timeToMinutes(document.getElementById('global_pb_10k')?.value);
        const pbHm = timeToMinutes(document.getElementById('global_pb_hm')?.value);
        const pbFm = timeToMinutes(document.getElementById('global_pb_fm')?.value);
        const pbCooper = parseFloat(document.getElementById('global_pb_cooper')?.value) || null;
        const pbBalke = parseFloat(document.getElementById('global_pb_balke')?.value) || null;

        const vdots = [];
        if (pb5k) vdots.push(calculateVdotFromTime(5000, pb5k));
        if (pb10k) vdots.push(calculateVdotFromTime(10000, pb10k));
        if (pbHm) vdots.push(calculateVdotFromTime(21097.5, pbHm));
        if (pbFm) vdots.push(calculateVdotFromTime(42195, pbFm));
        if (pbCooper) vdots.push(calculateVdotFromCooper(pbCooper));
        if (pbBalke) vdots.push(calculateVdotFromBalke(pbBalke));

        const validVdots = vdots.filter(v => v !== null && !isNaN(v) && v > 0);
        if (validVdots.length === 0) return;

        const maxVdot = Math.max(...validVdots);
        const displayVdotEl = document.getElementById('pb-display-vdot');
        if (displayVdotEl) displayVdotEl.textContent = maxVdot.toFixed(1);

        const paces = calculatePacesFromVdot(maxVdot);
        if (paces) {
            const easyEl = document.getElementById('pb-pace-easy');
            const marathonEl = document.getElementById('pb-pace-marathon');
            const thresholdEl = document.getElementById('pb-pace-threshold');
            const intervalEl = document.getElementById('pb-pace-interval');
            const repetitionEl = document.getElementById('pb-pace-repetition');

            if (easyEl) easyEl.innerHTML = `${paces.easy_range} <span class="pace-unit">/km</span>`;
            if (marathonEl) marathonEl.innerHTML = `${paces.marathon} <span class="pace-unit">/km</span>`;
            if (thresholdEl) thresholdEl.innerHTML = `${paces.threshold} <span class="pace-unit">/km</span>`;
            if (intervalEl) intervalEl.innerHTML = `${paces.interval} <span class="pace-unit">/km</span>`;
            if (repetitionEl) repetitionEl.innerHTML = `${paces.repetition} <span class="pace-unit">/km</span>`;
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
                // Clear dismissal cooldown since user successfully updated
                localStorage.removeItem('global_pb_modal_dismissed_time');

                alertBox.className = 'performance-alert performance-alert--success block';
                alertBox.textContent = 'Laporan kondisi & Personal Best berhasil diperbarui! Target pace latihan telah disesuaikan.';

                setTimeout(() => {
                    closeGlobalPbModal();
                    if (window.location.pathname.includes('/runner/dashboard') || window.location.pathname.includes('/runner/calendar') || window.location.pathname.includes('/profile')) {
                        window.location.reload();
                    }
                }, 1200);
            } else {
                alertBox.className = 'performance-alert performance-alert--error block';
                alertBox.textContent = res.message || 'Gagal memperbarui Personal Best. Periksa format waktu (HH:MM:SS).';
            }
        } catch (err) {
            alertBox.className = 'performance-alert performance-alert--error block';
            alertBox.textContent = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan Check-in</span><i class="fas fa-arrow-right text-[10px]"></i>';
        }
    }

    // Auto-prompt every 1 week (7 days) based on DB updated_at / created_at date
    document.addEventListener('DOMContentLoaded', function() {
        if (shouldPromptWeeklyPb) {
            const lastDismissed = localStorage.getItem('global_pb_modal_dismissed_time');
            const oneDayMs = 24 * 60 * 60 * 1000;
            const isDismissedRecently = lastDismissed && (Date.now() - parseInt(lastDismissed, 10) < oneDayMs);

            if (!isDismissedRecently) {
                setTimeout(() => {
                    openGlobalPbModal();
                }, 1200);
            }
        }
    });
</script>
@endif
@endauth
