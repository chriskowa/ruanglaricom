<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BIB Check - {{ $event->name }}</title>
    @php
        $eventFavicon = $event->getFaviconUrl();
    @endphp
    <link rel="icon" href="{{ $eventFavicon }}">
    <link rel="shortcut icon" href="{{ asset('images/green/favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ $eventFavicon }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Inter+Tight:wght@700;800&family=JetBrains+Mono:wght@600;700&family=Montserrat:wght@400;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
        }
        .font-heading {
            font-family: 'Inter Tight', 'Sora', system-ui, sans-serif;
            font-weight: 800;
            letter-spacing: -0.03em;
        }
        .font-mono-bib {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, monospace;
            font-variant-numeric: tabular-nums;
        }
        .bib-canvas-stage {
            container-type: inline-size;
            position: relative;
            width: 100%;
            overflow: hidden;
        }
        @keyframes bibCardReveal {
            0% {
                opacity: 0;
                transform: translateY(16px) scale(0.97);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        @keyframes bibMetaReveal {
            0% {
                opacity: 0;
                transform: translateY(8px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .bib-reveal {
            animation: bibCardReveal 340ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .bib-meta-reveal {
            opacity: 0;
            animation: bibMetaReveal 280ms cubic-bezier(0.16, 1, 0.3, 1) 90ms forwards;
        }
        @media (prefers-reduced-motion: reduce) {
            .bib-reveal,
            .bib-meta-reveal {
                animation: none !important;
                opacity: 1 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 pt-0 flex flex-col antialiased selection:bg-slate-900 selection:text-white">
    @php
        $logoUrl = $event->getLogoImageUrl();
        $eventDateText = $event->start_at ? $event->start_at->translatedFormat('d M Y') : null;
        $eventMeta = collect([$eventDateText, $event->location_name])->filter()->implode(' / ');
        $hasBibDesign = ! empty($bibDesign) && ! empty($bibDesign['bg_url']);
    @endphp

    <!-- Top Header -->
    <header class="w-full bg-white border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $event->name }}" class="h-10 w-10 sm:h-11 sm:w-11 object-contain rounded-md bg-white border border-slate-200 p-1 shrink-0" width="44" height="44">
                @else
                    <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-md bg-slate-900 flex items-center justify-center font-heading text-sm font-bold text-white shrink-0">
                        {{ strtoupper(substr($event->name, 0, 2)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <h1 class="font-heading text-lg sm:text-xl font-bold text-slate-900 truncate">{{ $event->name }}</h1>
                    @if($eventMeta)
                        <p class="text-xs sm:text-sm text-slate-600 truncate">{{ $eventMeta }}</p>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if($canManage)
                    <button type="button" id="toggleFormatPanelBtn" class="px-3 py-1.5 rounded-md bg-white border border-slate-300 text-xs font-semibold text-slate-800 hover:bg-slate-100 transition duration-150 cursor-pointer">
                        Atur Format BIB
                    </button>
                @endif

                <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 bg-white border border-slate-300 rounded-md px-2.5 py-1.5">
                    <span>Reset:</span>
                    <select id="autoResetSelect" class="bg-transparent text-slate-900 font-semibold focus:outline-none cursor-pointer">
                        <option value="0">Off</option>
                        <option value="15">15 dtk</option>
                        <option value="30">30 dtk</option>
                    </select>
                </label>

                <button type="button" id="soundToggleBtn" class="px-3 py-1.5 rounded-md bg-white border border-slate-300 text-xs font-medium text-slate-700 hover:bg-slate-100 transition duration-150 cursor-pointer">
                    Sound: On
                </button>

                <button type="button" id="fullscreenBtn" class="px-3 py-1.5 rounded-md bg-slate-900 text-xs font-semibold text-white hover:bg-slate-800 transition duration-150 cursor-pointer">
                    Fullscreen
                </button>
            </div>
        </div>

        @if($canManage)
        <!-- EO Format Settings Drawer (Only visible to event owner/admin) -->
        <div id="eoFormatPanel" class="hidden border-t border-slate-200 bg-slate-50">
            <form id="eoFormatForm" class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex flex-wrap items-end gap-3">
                <div class="w-full sm:w-56">
                    <label for="eoTakeLastInput" class="block text-xs font-semibold text-slate-700 mb-1">Potong / X Digit Terakhir</label>
                    <input type="text" id="eoTakeLastInput" value="{{ $takeLast }}" placeholder="Misal: 3 atau POLKESMASI-" class="w-full bg-white border border-slate-300 focus:border-slate-900 rounded-md px-3 py-1.5 text-sm text-slate-900 focus:outline-none">
                </div>
                <div class="w-full sm:w-44">
                    <label for="eoPrefixInput" class="block text-xs font-semibold text-slate-700 mb-1">Prefix Baru</label>
                    <input type="text" id="eoPrefixInput" value="{{ $prefix }}" placeholder="Misal: 25" class="w-full bg-white border border-slate-300 focus:border-slate-900 rounded-md px-3 py-1.5 text-sm text-slate-900 focus:outline-none">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" id="eoSaveFormatBtn" class="px-4 py-1.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition duration-150 cursor-pointer">
                        Simpan Format
                    </button>
                    <span id="eoFormatFeedback" class="text-xs font-medium text-emerald-700 hidden">Tersimpan</span>
                </div>
            </form>
        </div>
        @endif
    </header>

    <!-- Main Stage -->
    <main class="flex-1 w-full max-w-4xl mx-auto px-4 sm:px-6 py-5 sm:py-8 flex flex-col justify-center gap-5">
        <!-- Scanner / Manual Input -->
        <section aria-label="Input BIB" class="w-full">
            <form id="bibCheckForm" class="bg-white border border-slate-200 rounded-lg p-3.5 sm:p-4">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <label for="bibScanInput" class="text-xs sm:text-sm font-semibold text-slate-800">
                        Scan QR atau Ketik Nomor BIB
                    </label>
                    <span id="scannerFocusStatus" class="text-xs font-medium text-emerald-700">
                        Scanner Siap
                    </span>
                </div>
                <div class="flex flex-col sm:flex-row gap-2.5">
                    <input
                        type="text"
                        id="bibScanInput"
                        name="bib"
                        value="{{ $initialQuery }}"
                        placeholder="Arahkan scanner ke QR BIB atau ketik nomor BIB..."
                        autocomplete="off"
                        autocapitalize="off"
                        spellcheck="false"
                        autofocus
                        class="flex-1 bg-slate-50 border border-slate-300 focus:border-slate-900 focus:bg-white rounded-md px-4 py-2.5 sm:py-3 text-base sm:text-lg font-mono-bib text-slate-900 placeholder-slate-400 focus:outline-none transition duration-150"
                    >
                    <button
                        type="submit"
                        id="bibSubmitBtn"
                        class="px-6 py-2.5 sm:py-3 rounded-md bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm sm:text-base transition duration-150 cursor-pointer shrink-0"
                    >
                        Cek BIB
                    </button>
                </div>
            </form>
        </section>

        <!-- Result Stage -->
        <section id="bibStage" aria-live="polite" class="w-full">
            <!-- Standby State -->
            <div id="stateStandby" class="{{ ($initialResult || $initialNotFound) ? 'hidden' : '' }} bg-white border border-slate-200 rounded-lg p-8 sm:p-12 text-center">
                @if($hasBibDesign)
                    <div class="max-w-md mx-auto mb-5 rounded-md overflow-hidden border border-slate-200 opacity-85">
                        <img src="{{ $bibDesign['bg_url'] }}" alt="{{ $event->name }} BIB" class="w-full h-auto block">
                    </div>
                @elseif($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $event->name }}" class="h-16 sm:h-20 w-auto mx-auto mb-5 object-contain">
                @endif
                <h2 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">
                    Scan QR pada BIB Anda
                </h2>
                <p class="text-sm sm:text-base text-slate-600 mt-2 max-w-lg mx-auto leading-relaxed">
                    Arahkan QR Code di BIB ke scanner untuk menampilkan nomor BIB, nama peserta, gender, dan kategori lomba.
                </p>
            </div>

            <!-- Verified BIB Result Card -->
            <div id="stateResult" class="{{ $initialResult ? '' : 'hidden' }} {{ $initialResult ? 'bib-reveal' : '' }}">
                @if($hasBibDesign)
                    <!-- Exact Print BIB Replica Stage -->
                    <div class="bg-white border border-slate-200 rounded-lg p-3 sm:p-5">
                        <div
                            id="digitalBibCanvas"
                            class="bib-canvas-stage mx-auto rounded border border-slate-200 bg-slate-100"
                            style="aspect-ratio: {{ $bibDesign['canvas_width'] }} / {{ $bibDesign['canvas_height'] }}; max-height: 58vh; max-width: calc(58vh * {{ $bibDesign['canvas_width'] }} / {{ $bibDesign['canvas_height'] }});"
                        >
                            <img src="{{ $bibDesign['bg_url'] }}" alt="BIB {{ $event->name }}" class="w-full h-full object-fill block select-none">
                            <div id="digitalBibOverlay" class="absolute inset-0 pointer-events-none"></div>
                        </div>

                        <!-- Clean Participant Summary Bar -->
                        <div class="bib-meta-reveal grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 pt-4 border-t border-slate-200">
                            <div class="bg-slate-50 border border-slate-200 rounded-md px-3.5 py-2.5">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nomor BIB</div>
                                <div id="resBibNumber" class="font-mono-bib text-lg sm:text-xl font-bold text-slate-900 mt-0.5 break-all">
                                    {{ $initialResult['bib_number'] ?? '-' }}
                                </div>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-md px-3.5 py-2.5">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Nama</div>
                                <div id="resName" class="text-base sm:text-lg font-bold text-slate-900 mt-0.5 truncate">
                                    {{ $initialResult['name'] ?? '-' }}
                                </div>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-md px-3.5 py-2.5">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</div>
                                <div id="resGender" class="text-base sm:text-lg font-bold text-slate-900 mt-0.5">
                                    {{ $initialResult['gender'] ?? '-' }}
                                </div>
                            </div>
                            <div class="bg-slate-50 border border-slate-200 rounded-md px-3.5 py-2.5">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</div>
                                <div id="resCategory" class="text-base sm:text-lg font-bold text-emerald-700 mt-0.5">
                                    {{ $initialResult['category'] ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Standard Light Theme BIB Card (Fallback when no BIB background saved) -->
                    <div class="bg-white border border-slate-200 border-t-4 border-t-slate-900 rounded-lg p-6 sm:p-10">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-4 border-b border-slate-200">
                            <span class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-emerald-700">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Terverifikasi</span>
                            </span>
                            <span class="text-xs sm:text-sm font-medium text-slate-600">{{ $event->name }}</span>
                        </div>

                        <div class="py-6 sm:py-8 text-center">
                            <div class="text-xs sm:text-sm font-semibold uppercase tracking-wider text-slate-500">
                                Nomor BIB
                            </div>
                            <div id="resBibNumber" class="font-mono-bib text-6xl sm:text-7xl md:text-8xl font-bold text-slate-900 tracking-tight leading-none mt-2 break-all">
                                {{ $initialResult['bib_number'] ?? '-' }}
                            </div>

                            <div class="mt-6">
                                <div class="text-xs sm:text-sm font-semibold uppercase tracking-wider text-slate-500">
                                    Nama Peserta
                                </div>
                                <div id="resName" class="font-heading text-2xl sm:text-4xl md:text-5xl font-bold text-slate-900 mt-1.5 break-words">
                                    {{ $initialResult['name'] ?? '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="bib-meta-reveal grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t border-slate-200">
                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 sm:p-5 text-center sm:text-left">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Gender
                                </div>
                                <div id="resGender" class="text-xl sm:text-3xl font-bold text-slate-900 mt-1">
                                    {{ $initialResult['gender'] ?? '-' }}
                                </div>
                            </div>

                            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 sm:p-5 text-center sm:text-left">
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Kategori
                                </div>
                                <div id="resCategory" class="text-xl sm:text-3xl font-bold text-emerald-700 mt-1">
                                    {{ $initialResult['category'] ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Not Found State -->
            <div id="stateNotFound" class="{{ $initialNotFound ? '' : 'hidden' }} bg-white border border-red-300 border-t-4 border-t-red-600 rounded-lg p-8 sm:p-12 text-center {{ $initialNotFound ? 'bib-reveal' : '' }}">
                <h2 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">
                    Nomor BIB Tidak Ditemukan
                </h2>
                <p class="text-sm sm:text-base text-slate-600 mt-2">
                    Nomor <span id="notFoundQuery" class="font-mono-bib text-red-700 font-semibold">{{ $initialQuery }}</span> belum terdaftar pada event ini.
                </p>
                <p class="text-xs sm:text-sm text-slate-500 mt-3">
                    Silakan scan ulang QR atau periksa kembali nomor BIB yang dimasukkan.
                </p>
            </div>
        </section>
    </main>

    <!-- Minimal Footer -->
    <footer class="w-full bg-white border-t border-slate-200 py-3 px-4 sm:px-6">
        <div class="max-w-5xl mx-auto flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
            <span>{{ $event->name }} - BIB Check</span>
            <a href="{{ route('events.show', $event->slug) }}" class="text-slate-700 font-medium hover:text-slate-900 transition duration-150">
                Halaman Event
            </a>
        </div>
    </footer>

    <script>
        (function() {
            const lookupUrl = @json(route('api.events.bib-check', $event->slug));
            const bibDesign = @json($bibDesign);
            const initialResult = @json($initialResult);

            const form = document.getElementById('bibCheckForm');
            const input = document.getElementById('bibScanInput');
            const submitBtn = document.getElementById('bibSubmitBtn');

            const stateStandby = document.getElementById('stateStandby');
            const stateResult = document.getElementById('stateResult');
            const stateNotFound = document.getElementById('stateNotFound');

            const resBibNumber = document.getElementById('resBibNumber');
            const resName = document.getElementById('resName');
            const resGender = document.getElementById('resGender');
            const resCategory = document.getElementById('resCategory');
            const digitalBibOverlay = document.getElementById('digitalBibOverlay');
            const notFoundQuery = document.getElementById('notFoundQuery');

            const scannerFocusStatus = document.getElementById('scannerFocusStatus');
            const autoResetSelect = document.getElementById('autoResetSelect');
            const soundToggleBtn = document.getElementById('soundToggleBtn');
            const fullscreenBtn = document.getElementById('fullscreenBtn');

            const toggleFormatPanelBtn = document.getElementById('toggleFormatPanelBtn');
            const eoFormatPanel = document.getElementById('eoFormatPanel');
            const eoFormatForm = document.getElementById('eoFormatForm');

            let autoResetTimer = null;
            let soundEnabled = true;
            let activeAbortController = null;
            let audioCtx = null;
            let lastQuery = @json($initialQuery);

            function resolveFontFamily(rawFont) {
                const f = String(rawFont || 'Helvetica').toLowerCase();
                if (f === 'montserrat extrabold' || f === 'montserrat') return "'Montserrat', sans-serif";
                if (f === 'bebas neue' || f === 'bebasneue') return "'Bebas Neue', sans-serif";
                if (f === 'anton') return "'Anton', sans-serif";
                if (f.includes('times') || f === 'serif') return "'Times New Roman', Times, serif";
                if (f.includes('courier') || f === 'monospace') return "'Courier New', Courier, monospace";
                return "Helvetica, Arial, sans-serif";
            }

            function renderDigitalBibOverlay(participant) {
                if (!bibDesign || !digitalBibOverlay) return;

                const genderShort = participant.gender === 'Male' ? 'M' : (participant.gender === 'Female' ? 'F' : '-');
                const replacements = {
                    '#': participant.bib_number || '-',
                    '{bib_number}': participant.bib_number || '-',
                    '[N]': participant.name || '-',
                    '{name}': participant.name || '-',
                    '[C]': participant.category || '-',
                    '{category}': participant.category || '-',
                    '[G]': genderShort,
                    '{gender}': participant.gender || '-',
                };

                digitalBibOverlay.innerHTML = '';
                const cw = bibDesign.canvas_width || 1;
                const ch = bibDesign.canvas_height || 1;

                (bibDesign.texts || []).forEach(t => {
                    let textVal = String(t.text || '');
                    for (const [key, val] of Object.entries(replacements)) {
                        textVal = textVal.replaceAll(key, val);
                    }

                    const el = document.createElement('div');
                    el.textContent = textVal;

                    const leftPct = ((parseFloat(t.left) || 0) / cw) * 100;
                    const topPct = ((parseFloat(t.top) || 0) / ch) * 100;
                    const fontCqw = ((parseFloat(t.fontSize) || 24) / cw) * 100;
                    const tx = (t.originX === 'center') ? '-50%' : '0%';
                    const ty = (t.originY === 'center') ? '-50%' : '0%';
                    const angle = parseFloat(t.angle) || 0;

                    const isExtraBold = String(t.fontFamily || '').toLowerCase() === 'montserrat extrabold';
                    const fontWeight = isExtraBold ? '800' : (t.fontWeight === 'bold' || t.fontWeight === 700 || t.fontWeight === 800 ? '700' : '400');

                    el.style.position = 'absolute';
                    el.style.left = `${leftPct}%`;
                    el.style.top = `${topPct}%`;
                    el.style.transform = `translate(${tx}, ${ty}) rotate(${angle}deg)`;
                    el.style.fontSize = `${fontCqw}cqw`;
                    el.style.lineHeight = '1.15';
                    el.style.whiteSpace = 'nowrap';
                    el.style.color = t.fill || '#000000';
                    el.style.fontFamily = resolveFontFamily(t.fontFamily);
                    el.style.fontWeight = fontWeight;
                    el.style.fontStyle = t.fontStyle || 'normal';
                    el.style.textAlign = t.originX === 'center' ? 'center' : 'left';

                    digitalBibOverlay.appendChild(el);
                });
            }

            function playBeep(isSuccess) {
                if (!soundEnabled) return;
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    if (!audioCtx) audioCtx = new AudioContext();
                    if (audioCtx.state === 'suspended') audioCtx.resume();

                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(isSuccess ? 880 : 280, audioCtx.currentTime);
                    gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + (isSuccess ? 0.14 : 0.25));

                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    osc.start();
                    osc.stop(audioCtx.currentTime + (isSuccess ? 0.15 : 0.26));
                } catch (e) {}
            }

            function isEditingSettings() {
                return eoFormatPanel && !eoFormatPanel.classList.contains('hidden') && eoFormatPanel.contains(document.activeElement);
            }

            function focusScannerInput() {
                if (!input || isEditingSettings()) return;
                input.focus({ preventScroll: true });
            }

            function updateFocusIndicator(isFocused) {
                if (isFocused) {
                    scannerFocusStatus.textContent = 'Scanner Siap';
                    scannerFocusStatus.className = 'text-xs font-medium text-emerald-700';
                } else {
                    scannerFocusStatus.textContent = 'Klik layar untuk fokus';
                    scannerFocusStatus.className = 'text-xs font-medium text-slate-500';
                }
            }

            function scheduleAutoReset() {
                if (autoResetTimer) {
                    clearTimeout(autoResetTimer);
                    autoResetTimer = null;
                }
                const seconds = parseInt(autoResetSelect.value || '0', 10);
                if (seconds > 0) {
                    autoResetTimer = setTimeout(() => {
                        stateResult.classList.add('hidden');
                        stateNotFound.classList.add('hidden');
                        stateStandby.classList.remove('hidden');
                        lastQuery = '';
                        focusScannerInput();
                    }, seconds * 1000);
                }
            }

            function triggerReveal(el) {
                el.classList.remove('bib-reveal');
                void el.offsetWidth;
                el.classList.add('bib-reveal');
                const meta = el.querySelector('.bib-meta-reveal');
                if (meta) {
                    meta.classList.remove('bib-meta-reveal');
                    void meta.offsetWidth;
                    meta.classList.add('bib-meta-reveal');
                }
            }

            function showResult(participant) {
                resBibNumber.textContent = participant.bib_number || '-';
                resName.textContent = participant.name || '-';
                resGender.textContent = participant.gender || '-';
                resCategory.textContent = participant.category || '-';
                renderDigitalBibOverlay(participant);

                stateStandby.classList.add('hidden');
                stateNotFound.classList.add('hidden');
                stateResult.classList.remove('hidden');

                triggerReveal(stateResult);
                playBeep(true);
                scheduleAutoReset();
            }

            function showNotFound(query) {
                notFoundQuery.textContent = query;
                stateStandby.classList.add('hidden');
                stateResult.classList.add('hidden');
                stateNotFound.classList.remove('hidden');

                triggerReveal(stateNotFound);
                playBeep(false);
                scheduleAutoReset();
            }

            async function performLookup(rawQuery) {
                const query = (rawQuery || '').trim();
                if (!query) {
                    focusScannerInput();
                    return;
                }

                lastQuery = query;

                if (activeAbortController) {
                    activeAbortController.abort();
                }
                activeAbortController = new AbortController();

                submitBtn.disabled = true;
                submitBtn.textContent = 'Mencari...';

                try {
                    const url = `${lookupUrl}?bib=${encodeURIComponent(query)}`;
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' },
                        signal: activeAbortController.signal
                    });

                    const data = await response.json();
                    if (response.ok && data.found && data.participant) {
                        showResult(data.participant);
                    } else {
                        showNotFound(data.query || query);
                    }
                } catch (err) {
                    if (err.name !== 'AbortError') {
                        showNotFound(query);
                    }
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Cek BIB';
                    input.value = '';
                    focusScannerInput();
                }
            }

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                performLookup(input.value);
            });

            input.addEventListener('focus', () => updateFocusIndicator(true));
            input.addEventListener('blur', () => updateFocusIndicator(false));

            document.addEventListener('click', function(e) {
                const interactive = e.target.closest('button, select, option, a, input, #eoFormatPanel');
                if (!interactive) {
                    focusScannerInput();
                }
            });

            autoResetSelect.addEventListener('change', function() {
                if (stateStandby.classList.contains('hidden')) {
                    scheduleAutoReset();
                }
                focusScannerInput();
            });

            soundToggleBtn.addEventListener('click', function() {
                soundEnabled = !soundEnabled;
                this.textContent = soundEnabled ? 'Sound: On' : 'Sound: Off';
                focusScannerInput();
            });

            fullscreenBtn.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen().catch(() => {});
                } else {
                    document.exitFullscreen().catch(() => {});
                }
                focusScannerInput();
            });

            document.addEventListener('fullscreenchange', function() {
                fullscreenBtn.textContent = document.fullscreenElement ? 'Exit Fullscreen' : 'Fullscreen';
                focusScannerInput();
            });

            @if($canManage)
            if (toggleFormatPanelBtn && eoFormatPanel && eoFormatForm) {
                toggleFormatPanelBtn.addEventListener('click', function() {
                    eoFormatPanel.classList.toggle('hidden');
                    if (eoFormatPanel.classList.contains('hidden')) {
                        focusScannerInput();
                    }
                });

                eoFormatForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const saveBtn = document.getElementById('eoSaveFormatBtn');
                    const feedback = document.getElementById('eoFormatFeedback');
                    const takeLast = document.getElementById('eoTakeLastInput').value;
                    const prefix = document.getElementById('eoPrefixInput').value;

                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Menyimpan...';

                    try {
                        const res = await fetch(@json(route('eo.events.participants.bib-check-settings', $event->id)), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ take_last: takeLast, prefix: prefix })
                        });
                        if (res.ok) {
                            feedback.classList.remove('hidden');
                            setTimeout(() => feedback.classList.add('hidden'), 2500);
                            if (lastQuery) {
                                performLookup(lastQuery);
                            }
                        }
                    } finally {
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Simpan Format';
                    }
                });
            }
            @endif

            if (initialResult) {
                renderDigitalBibOverlay(initialResult);
            }

            window.addEventListener('load', focusScannerInput);
            focusScannerInput();
        })();
    </script>
</body>
</html>
