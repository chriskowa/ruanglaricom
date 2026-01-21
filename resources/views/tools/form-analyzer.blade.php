@extends('layouts.pacerhub')

@section('title', 'Analisis Form Lari AI')

@push('styles')
<style>
    .glass-panel {
        background: rgba(30, 41, 59, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .neon-glow { box-shadow: 0 0 15px rgba(204, 255, 0, 0.3); }
    .text-glow { text-shadow: 0 0 10px rgba(204, 255, 0, 0.4); }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .mask-image-gradient {
        -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1), rgba(0,0,0,0));
        mask-image: linear-gradient(to bottom, rgba(0,0,0,1), rgba(0,0,0,0));
    }
    @keyframes scan {
        0%, 100% { top: 0%; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }
    .animate-scan { animation: scan 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>
@endpush

@section('content')
<div class="relative overflow-hidden">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full z-0 pointer-events-none opacity-40">
        <div class="absolute top-20 left-10 w-96 h-96 bg-neon rounded-full mix-blend-multiply filter blur-[120px] animate-pulse-slow"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-blue-600 rounded-full mix-blend-multiply filter blur-[120px] opacity-70"></div>
    </div>

    <section class="relative pt-20 pb-16 lg:pt-28 lg:pb-24">
        <div class="max-w-5xl mx-auto px-4 relative z-10 text-center">
            <div class="inline-flex items-center px-3 py-1 rounded-full border border-neon/30 bg-neon/5 text-neon text-xs font-bold tracking-wide uppercase mb-8">
                <span class="w-2 h-2 bg-neon rounded-full mr-2 animate-pulse"></span>
                AI Biomechanics Engine (Beta)
            </div>

            <h1 class="text-4xl md:text-6xl font-black tracking-tight leading-[1.1] mb-6 text-white">
                Lari Kencang Percuma <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon to-emerald-400 text-glow">Kalau Menuju Cedera.</span>
            </h1>

            <p class="text-base md:text-lg text-slate-400 mb-10 leading-relaxed max-w-2xl mx-auto">
                Upload video lari 5–10 detik, tampak samping. Sistem mengecek kualitas video, mengoptimalkan ukuran file, dan memberi feedback yang bisa langsung dipraktikkan.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <button type="button" onclick="document.getElementById('rl-formanalyzer-demo').scrollIntoView({behavior: 'smooth'})" class="w-full sm:w-auto bg-neon text-dark font-black px-8 py-4 rounded-full hover:bg-white hover:scale-105 transition transform shadow-[0_0_25px_rgba(204,255,0,0.4)]">
                    COBA ANALISIS SEKARANG
                </button>
                <div class="text-xs text-slate-500 font-mono mt-2 sm:mt-0">
                    *Beta — hasil makin akurat jika video sesuai panduan
                </div>
            </div>
        </div>
    </section>

    <section id="rl-formanalyzer-demo" class="py-14 md:py-18 relative border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-5 space-y-8 order-2 lg:order-1">
                    <h2 class="text-2xl md:text-3xl font-bold text-white">Laboratorium Biomekanik<br>di Saku Anda.</h2>
                    <p class="text-slate-400">
                        Fokus utama: video yang layak dianalisis. Sistem akan memberi skor kelayakan, rekomendasi perbaikan rekaman, dan ringkasan yang mudah dipahami.
                    </p>

                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-white/5 transition border border-transparent hover:border-slate-700 cursor-default">
                            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Quality Gate</h4>
                                <p class="text-sm text-slate-400">Cek durasi, resolusi, FPS, orientasi, dan ukuran file sebelum analisis.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl hover:bg-white/5 transition border border-transparent hover:border-slate-700 cursor-default">
                            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-compress"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">Optimasi Upload</h4>
                                <p class="text-sm text-slate-400">Jika tersedia, video dikompres otomatis (tanpa merusak kelayakan analisis).</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-5">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Checklist Rekaman (Golden Rules)</div>
                        <ul class="text-sm text-slate-300 space-y-2">
                            <li class="flex gap-2"><span class="text-neon">•</span> Tampak samping (sagittal plane), kamera sejajar pinggang.</li>
                            <li class="flex gap-2"><span class="text-neon">•</span> 5–10 detik lari stabil, 1 orang di frame.</li>
                            <li class="flex gap-2"><span class="text-neon">•</span> Pencahayaan cukup, lutut & ankle terlihat jelas.</li>
                            <li class="flex gap-2"><span class="text-neon">•</span> Hindari zoom digital berlebihan & motion blur.</li>
                        </ul>
                    </div>
                </div>

                <div class="lg:col-span-7 flex justify-center order-1 lg:order-2">
                    <div class="relative w-[340px] h-[680px] bg-slate-950 border-[8px] border-slate-800 rounded-[3rem] shadow-2xl overflow-hidden ring-1 ring-slate-700 flex flex-col">
                        <div class="absolute top-0 w-full h-8 bg-black/50 z-50 flex justify-between items-center px-6">
                            <span class="text-[10px] font-bold text-white">RUANGLARI AI</span>
                            <div class="flex gap-1"><div class="w-3 h-3 bg-green-500 rounded-full"></div></div>
                        </div>

                        <div id="rlfa-app" class="w-full h-full bg-slate-950 relative">
                            <input id="rlfa-video-input" type="file" accept="video/mp4,video/quicktime,video/webm,video/x-matroska" class="hidden">

                            <div id="rlfa-state-instructions" class="absolute inset-0 flex flex-col justify-end p-6 bg-gradient-to-b from-slate-800 to-slate-950 transition-opacity duration-500 z-30">
                                <div class="absolute top-0 left-0 w-full h-1/2 bg-cover bg-center opacity-40 mask-image-gradient" style="background-image: url('{{ asset('images/big/img6.jpg') }}')"></div>
                                <div class="absolute top-0 left-0 w-full h-1/2 bg-gradient-to-b from-transparent to-slate-950"></div>

                                <div class="relative z-10 mb-4">
                                    <h3 class="text-2xl font-black text-white mb-2">Analisis Form Lari</h3>
                                    <p class="text-slate-400 text-sm mb-5">Upload video (MP4/MOV/WebM/MKV). Rekomendasi: 5–10 detik, tampak samping.</p>

                                    <div class="grid grid-cols-2 gap-3 mb-6">
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-3">
                                            <div class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Max ukuran</div>
                                            <div class="text-sm font-bold text-white">150 MB</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-3">
                                            <div class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Ideal</div>
                                            <div class="text-sm font-bold text-white">720p · 30fps</div>
                                        </div>
                                    </div>

                                    <button id="rlfa-upload-btn" type="button" class="w-full bg-neon text-dark font-bold py-4 rounded-xl hover:bg-white transition flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        Upload Video & Analisis
                                    </button>

                                    <div class="mt-4 bg-slate-900/60 border border-slate-800 rounded-xl p-3 text-xs text-slate-300">
                                        <label class="flex items-center gap-2">
                                            <input id="rlfa-mode-device" type="checkbox" class="rounded bg-slate-800 border-slate-600 text-neon focus:ring-0" checked>
                                            <span class="font-semibold text-white">Mode hemat</span>
                                            <span class="text-slate-400">(analisis form di perangkat, tidak upload video ke server)</span>
                                        </label>
                                        <div class="mt-2 text-[10px] text-slate-400 leading-relaxed">
                                            Jika dimatikan, video akan diupload ke server (masuk antrian max 5 user) untuk cek metadata & optimasi ukuran jika tersedia.
                                        </div>
                                    </div>

                                    <div id="rlfa-client-warnings" class="mt-4 hidden">
                                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-3 text-xs text-red-200 space-y-1"></div>
                                    </div>
                                </div>
                            </div>

                            <div id="rlfa-state-scanning" class="absolute inset-0 bg-black hidden z-40">
                                <img src="{{ asset('images/big/img2.jpg') }}" class="w-full h-full object-cover opacity-60 grayscale filter contrast-125" alt="Running Analysis">
                                <div class="absolute inset-0">
                                    <div class="absolute w-full h-[2px] bg-neon shadow-[0_0_20px_#ccff00] animate-scan z-50"></div>
                                    <div class="absolute top-[40%] left-[45%] w-3 h-3 bg-neon rounded-full shadow-[0_0_10px_#ccff00] animate-pulse"></div>
                                    <div class="absolute top-[65%] left-[55%] w-3 h-3 bg-neon rounded-full shadow-[0_0_10px_#ccff00] animate-pulse" style="animation-delay: 0.2s"></div>
                                    <div class="absolute top-[88%] left-[48%] w-3 h-3 bg-neon rounded-full shadow-[0_0_10px_#ccff00] animate-pulse" style="animation-delay: 0.4s"></div>
                                    <div class="absolute top-[41%] left-[46%] w-[50px] h-[2px] bg-neon/60 origin-top-left rotate-[65deg]"></div>
                                    <div class="absolute top-[66%] left-[56%] w-[50px] h-[2px] bg-neon/60 origin-top-left rotate-[110deg]"></div>

                                    <div class="absolute top-20 left-4 bg-black/70 backdrop-blur-md p-2 rounded border-l-2 border-neon">
                                        <p class="text-[10px] text-slate-300 font-mono">UPLOAD</p>
                                        <p class="text-sm font-bold text-white font-mono" id="rlfa-scan-metric-1">0%</p>
                                    </div>
                                    <div class="absolute bottom-32 right-4 bg-black/70 backdrop-blur-md p-2 rounded border-l-2 border-neon">
                                        <p class="text-[10px] text-slate-300 font-mono">OPTIMIZE</p>
                                        <p class="text-sm font-bold text-white font-mono" id="rlfa-scan-metric-2">--</p>
                                    </div>

                                    <div class="absolute bottom-10 w-full text-center px-6">
                                        <p class="text-neon font-mono text-xs animate-pulse" id="rlfa-scan-text">MENYIAPKAN...</p>
                                        <p class="text-[10px] text-slate-300 mt-1" id="rlfa-scan-subtext"></p>
                                    </div>
                                </div>
                            </div>

                            <div id="rlfa-state-results" class="absolute inset-0 bg-slate-950 hidden flex-col z-50 overflow-y-auto no-scrollbar">
                                <div class="p-6 pb-2 sticky top-0 bg-slate-950/95 backdrop-blur z-20 border-b border-slate-800">
                                    <div class="flex justify-between items-end mb-2">
                                        <div>
                                            <p class="text-xs text-slate-400 uppercase tracking-widest">Skor Kelayakan</p>
                                            <h2 class="text-4xl font-black text-white italic">
                                                <span id="rlfa-score">--</span><span class="text-lg text-slate-500 font-normal">/100</span>
                                            </h2>
                                        </div>
                                        <div id="rlfa-score-badge" class="bg-slate-800 text-slate-200 px-2 py-1 rounded text-xs font-bold border border-slate-700">
                                            MENUNGGU
                                        </div>
                                    </div>
                                    <div class="mt-2 grid grid-cols-2 gap-2 text-[10px] text-slate-300">
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-wider">Durasi</div>
                                            <div class="font-mono font-bold text-white" id="rlfa-meta-duration">--</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-wider">Resolusi</div>
                                            <div class="font-mono font-bold text-white" id="rlfa-meta-resolution">--</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-wider">FPS</div>
                                            <div class="font-mono font-bold text-white" id="rlfa-meta-fps">--</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-wider">Ukuran</div>
                                            <div class="font-mono font-bold text-white" id="rlfa-meta-size">--</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-5 space-y-4 pb-20">
                                    <div id="rlfa-visualization-wrap" class="hidden mb-6">
                                        <div class="relative rounded-2xl overflow-hidden border border-slate-800 shadow-2xl bg-black">
                                            <img id="rlfa-visualization-img" class="w-full h-auto object-cover opacity-90" src="" alt="Analysis Visualization">
                                            <div class="absolute bottom-0 left-0 w-full bg-gradient-to-t from-black/90 to-transparent p-4">
                                                <p class="text-neon text-[10px] font-bold tracking-widest uppercase">Biomechanics Visualization</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-slate-900 rounded-tr-2xl rounded-br-2xl rounded-bl-2xl rounded-tl-sm p-4 border border-slate-800 relative">
                                        <div class="absolute -top-3 left-0 bg-neon text-dark text-[10px] font-bold px-2 py-0.5 rounded">AI COACH SAYS:</div>
                                        <p class="text-sm text-slate-200 leading-relaxed mt-2" id="rlfa-coach-message"></p>
                                    </div>

                                    <div id="rlfa-positives-wrap" class="space-y-3 hidden">
                                        <h4 class="text-xs font-bold text-slate-500 uppercase mt-4 mb-2">Yang Sudah Bagus</h4>
                                        <div id="rlfa-positives" class="space-y-2"></div>
                                    </div>

                                    <div id="rlfa-issues-wrap" class="space-y-3 hidden">
                                        <h4 class="text-xs font-bold text-slate-500 uppercase mt-4 mb-2">Catatan Penting</h4>
                                        <div id="rlfa-issues" class="space-y-2"></div>
                                    </div>

                                    <div id="rlfa-suggestions-wrap" class="space-y-3 hidden">
                                        <h4 class="text-xs font-bold text-slate-500 uppercase mt-4 mb-2">Saran Perbaikan</h4>
                                        <div id="rlfa-suggestions" class="space-y-2"></div>
                                    </div>

                                    <div id="rlfa-formissues-wrap" class="space-y-3 hidden">
                                        <h4 class="text-xs font-bold text-slate-500 uppercase mt-4 mb-2">Analisis Form (Beta)</h4>
                                        <div id="rlfa-formissues" class="space-y-2"></div>
                                    </div>

                                    <div id="rlfa-strength-wrap" class="space-y-3 hidden">
                                        <h4 class="text-xs font-bold text-slate-500 uppercase mt-4 mb-2">Solusi Penguatan</h4>
                                        <div id="rlfa-strength" class="space-y-2"></div>
                                    </div>

                                    <div id="rlfa-recovery-wrap" class="space-y-3 hidden">
                                        <h4 class="text-xs font-bold text-slate-500 uppercase mt-4 mb-2">Pemulihan & Pengobatan Awal</h4>
                                        <div id="rlfa-recovery" class="space-y-2"></div>
                                    </div>

                                    <div class="pt-4 space-y-2">
                                        <button id="rlfa-retry-btn" type="button" class="w-full bg-white text-dark font-bold py-3 rounded-xl text-sm hover:bg-slate-200">
                                            Ulangi Analisis
                                        </button>
                                        <button id="rlfa-back-btn" type="button" class="w-full text-slate-500 text-xs py-3 hover:text-white">
                                            Kembali
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="absolute bottom-2 left-1/2 -translate-x-1/2 w-32 h-1 bg-slate-600 rounded-full z-50"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script type="module">
    import { FilesetResolver, PoseLandmarker } from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14";

    let landmarkerPromise = null;

    const clamp = (v, min, max) => Math.max(min, Math.min(max, v));
    const toDeg = (rad) => rad * 180 / Math.PI;
    const angle = (a, b, c) => {
        const abx = a.x - b.x, aby = a.y - b.y;
        const cbx = c.x - b.x, cby = c.y - b.y;
        const dot = abx * cbx + aby * cby;
        const mag1 = Math.hypot(abx, aby);
        const mag2 = Math.hypot(cbx, cby);
        if (!mag1 || !mag2) return null;
        const cos = clamp(dot / (mag1 * mag2), -1, 1);
        return toDeg(Math.acos(cos));
    };

    const getLandmarker = async () => {
        if (landmarkerPromise) return landmarkerPromise;
        landmarkerPromise = (async () => {
            const vision = await FilesetResolver.forVisionTasks("https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/wasm");
            return await PoseLandmarker.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: "https://storage.googleapis.com/mediapipe-models/pose_landmarker/pose_landmarker_lite/float16/1/pose_landmarker_lite.task",
                },
                runningMode: "VIDEO",
                numPoses: 1,
                minPoseDetectionConfidence: 0.5,
                minPosePresenceConfidence: 0.5,
                minTrackingConfidence: 0.5,
            });
        })();
        return landmarkerPromise;
    };

    const drawBiomechanics = (video, landmarks, width, height) => {
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, width, height);
        ctx.fillStyle = 'rgba(0, 0, 0, 0.5)';
        ctx.fillRect(0, 0, width, height);
        if (!landmarks) return canvas.toDataURL('image/jpeg', 0.8);
        const drawLine = (start, end, color = '#ccff00', width = 3) => {
            if (!start || !end) return;
            ctx.beginPath();
            ctx.moveTo(start.x * width, start.y * height);
            ctx.lineTo(end.x * width, end.y * height);
            ctx.strokeStyle = color;
            ctx.lineWidth = width;
            ctx.lineCap = 'round';
            ctx.stroke();
        };
        const drawPoint = (pt, color = '#ffffff', radius = 4) => {
            if (!pt) return;
            ctx.beginPath();
            ctx.arc(pt.x * width, pt.y * height, radius, 0, 2 * Math.PI);
            ctx.fillStyle = color;
            ctx.fill();
        };
        const drawAngle = (center, text) => {
            if (!center) return;
            ctx.font = 'bold 16px monospace';
            ctx.fillStyle = '#ffffff';
            ctx.shadowColor = '#000000';
            ctx.shadowBlur = 4;
            ctx.fillText(text, (center.x * width) + 10, (center.y * height) - 10);
        };
        const L = {
            leftShoulder: 11, rightShoulder: 12, leftHip: 23, rightHip: 24,
            leftKnee: 25, rightKnee: 26, leftAnkle: 27, rightAnkle: 28,
            leftFootIndex: 31, rightFootIndex: 32
        };
        const lVis = (landmarks[L.leftAnkle]?.visibility ?? 0) + (landmarks[L.leftKnee]?.visibility ?? 0);
        const rVis = (landmarks[L.rightAnkle]?.visibility ?? 0) + (landmarks[L.rightKnee]?.visibility ?? 0);
        const isLeft = lVis > rVis;
        const hip = landmarks[isLeft ? L.leftHip : L.rightHip];
        const knee = landmarks[isLeft ? L.leftKnee : L.rightKnee];
        const ankle = landmarks[isLeft ? L.leftAnkle : L.rightAnkle];
        const shoulder = landmarks[isLeft ? L.leftShoulder : L.rightShoulder];
        const foot = landmarks[isLeft ? L.leftFootIndex : L.rightFootIndex];
        drawLine(shoulder, hip, '#ccff00');
        drawLine(hip, knee, '#ccff00');
        drawLine(knee, ankle, '#ccff00');
        drawLine(ankle, foot, '#ccff00');
        drawPoint(shoulder);
        drawPoint(hip);
        drawPoint(knee);
        drawPoint(ankle);
        drawPoint(foot);
        if (hip && knee && ankle) {
             const kAngle = angle(hip, knee, ankle);
             const flex = kAngle ? (180 - kAngle).toFixed(0) : '--';
             drawAngle(knee, `${flex}°`);
        }
        if (knee && ankle) {
             const dx = Math.abs(knee.x - ankle.x);
             const dy = Math.abs(knee.y - ankle.y);
             const ang = (dy > 0) ? toDeg(Math.atan2(dx, dy)).toFixed(0) : '--';
             drawAngle(ankle, `${ang}°`);
        }
        return canvas.toDataURL('image/jpeg', 0.85);
    };

    const analyzeVideoFile = async (file, onProgress) => {
        const landmarker = await getLandmarker();

        const video = document.createElement('video');
        video.preload = 'metadata';
        video.muted = true;
        video.playsInline = true;
        video.crossOrigin = 'anonymous';

        const url = URL.createObjectURL(file);
        video.src = url;

        await new Promise((resolve, reject) => {
            video.onloadedmetadata = () => resolve();
            video.onerror = () => reject(new Error('Video tidak bisa dibaca.'));
        });

        const duration = Number(video.duration) || 0;
        const width = video.videoWidth;
        const height = video.videoHeight;

        const targetSamples = clamp(Math.floor(duration * 2), 12, 28);
        const start = clamp(0.4, 0, Math.max(0, duration - 0.4));
        const end = clamp(duration - 0.4, 0, duration);
        const step = targetSamples > 1 ? (end - start) / (targetSamples - 1) : 0;

        const frames = [];
        const keyVisibility = [];
        const L = {
            leftShoulder: 11,
            rightShoulder: 12,
            leftWrist: 15,
            rightWrist: 16,
            leftHip: 23,
            rightHip: 24,
            leftKnee: 25,
            rightKnee: 26,
            leftAnkle: 27,
            rightAnkle: 28,
            leftHeel: 29,
            rightHeel: 30,
            leftFootIndex: 31,
            rightFootIndex: 32,
        };

        const pickSide = (lm) => {
            const lv = lm[L.leftAnkle]?.visibility ?? 0;
            const rv = lm[L.rightAnkle]?.visibility ?? 0;
            return lv >= rv ? 'left' : 'right';
        };

        const getPt = (lm, idx) => lm?.[idx] ? { x: lm[idx].x, y: lm[idx].y, v: lm[idx].visibility ?? 0 } : null;

        for (let i = 0; i < targetSamples; i++) {
            const t = start + step * i;
            video.currentTime = t;
            await new Promise((resolve) => {
                const handler = () => { video.removeEventListener('seeked', handler); resolve(); };
                video.addEventListener('seeked', handler);
            });

            const r = landmarker.detectForVideo(video, Math.round(t * 1000));
            const lm = r?.landmarks?.[0];
            if (!lm || !Array.isArray(lm)) {
                if (onProgress) onProgress({ phase: 'pose', done: i + 1, total: targetSamples });
                continue;
            }

            const side = pickSide(lm);
            const hip = getPt(lm, side === 'left' ? L.leftHip : L.rightHip);
            const knee = getPt(lm, side === 'left' ? L.leftKnee : L.rightKnee);
            const ankle = getPt(lm, side === 'left' ? L.leftAnkle : L.rightAnkle);
            const heel = getPt(lm, side === 'left' ? L.leftHeel : L.rightHeel);
            const footIndex = getPt(lm, side === 'left' ? L.leftFootIndex : L.rightFootIndex);

            const lShoulder = getPt(lm, L.leftShoulder);
            const rShoulder = getPt(lm, L.rightShoulder);
            const lWrist = getPt(lm, L.leftWrist);
            const rWrist = getPt(lm, L.rightWrist);

            if (!hip || !knee || !ankle || !heel || !footIndex || !lShoulder || !rShoulder) {
                if (onProgress) onProgress({ phase: 'pose', done: i + 1, total: targetSamples });
                continue;
            }

            const kAngle = angle(hip, knee, ankle);
            const kneeFlex = kAngle ? (180 - kAngle) : null;

            const shinDx = Math.abs(knee.x - ankle.x);
            const shinDy = Math.abs(knee.y - ankle.y);
            const shinAng = (shinDy > 0) ? toDeg(Math.atan2(shinDx, shinDy)) : null;

            const midX = (lShoulder.x + rShoulder.x) / 2;
            const trunkDx = (lShoulder.x + rShoulder.x) / 2 - hip.x;
            const trunkDy = ((lShoulder.y + rShoulder.y) / 2 - hip.y);
            const trunkAng = trunkDy !== 0 ? Math.abs(toDeg(Math.atan2(trunkDx, -trunkDy))) : null;

            const cross = ((lWrist && lWrist.x > midX + 0.03) || (rWrist && rWrist.x < midX - 0.03)) ? 1 : 0;
            const visAvg = (hip.v + knee.v + ankle.v + heel.v + footIndex.v + lShoulder.v + rShoulder.v) / 7;
            keyVisibility.push(visAvg);

            const footY = Math.max(ankle.y, heel.y, footIndex.y);
            const ankleHipDx = Math.abs(ankle.x - hip.x);
            frames.push({
                t,
                footY,
                heelLower: heel.y > (footIndex.y + 0.01),
                ankleHipDx,
                kneeFlex,
                shinAng,
                trunkAng,
                cross,
                hipY: hip.y,
                landmarks: lm
            });

            if (onProgress) onProgress({ phase: 'pose', done: i + 1, total: targetSamples });
        }

        if (!frames.length) {
            URL.revokeObjectURL(url);
            return {
                confidence: 0,
                samples: 0,
            };
        }

        const maxFootY = frames.reduce((m, f) => Math.max(m, f.footY), 0);
        const contact = frames.filter((f) => f.footY > (maxFootY - 0.035) && f.ankleHipDx > 0.03);
        const contactCount = contact.length || 0;

        const heelCount = contact.filter((f) => f.heelLower).length;
        const overCount = contact.filter((f) => f.ankleHipDx > 0.12).length;

        const avg = (arr) => {
            const v = arr.filter((x) => Number.isFinite(x));
            if (!v.length) return null;
            return v.reduce((a, b) => a + b, 0) / v.length;
        };

        const std = (arr) => {
            const v = arr.filter((x) => Number.isFinite(x));
            if (v.length < 2) return null;
            const m = v.reduce((a, b) => a + b, 0) / v.length;
            const s = v.reduce((a, b) => a + (b - m) * (b - m), 0) / (v.length - 1);
            return Math.sqrt(s);
        };

        const confidence = avg(keyVisibility) ?? 0;
        const kneeFlex = avg(contact.map((f) => f.kneeFlex));
        const shinAng = avg(contact.map((f) => f.shinAng));
        const trunkAng = avg(frames.map((f) => f.trunkAng));
        const armCrossPct = 100 * (frames.reduce((a, f) => a + f.cross, 0) / frames.length);
        const verticalOsc = std(frames.map((f) => f.hipY));

        let visualization = null;
        try {
            let bestFrame = null;
            if (contact.length > 0) {
                 bestFrame = contact.sort((a, b) => b.kneeFlex - a.kneeFlex)[0];
            } else if (frames.length > 0) {
                 bestFrame = frames[Math.floor(frames.length / 2)];
            }

            if (bestFrame && bestFrame.landmarks) {
                video.currentTime = bestFrame.t;
                await new Promise((resolve) => {
                    const handler = () => { video.removeEventListener('seeked', handler); resolve(); };
                    video.addEventListener('seeked', handler);
                });
                visualization = drawBiomechanics(video, bestFrame.landmarks, width, height);
            }
        } catch (e) {
            console.error(e);
        }

        URL.revokeObjectURL(url);

        return {
            confidence: Number.isFinite(confidence) ? Number(confidence.toFixed(3)) : 0,
            samples: frames.length,
            heel_strike_pct: contactCount ? Number(((heelCount / contactCount) * 100).toFixed(1)) : null,
            overstride_pct: contactCount ? Number(((overCount / contactCount) * 100).toFixed(1)) : null,
            shin_angle_deg: Number.isFinite(shinAng) ? Number(shinAng.toFixed(1)) : null,
            knee_flex_deg: Number.isFinite(kneeFlex) ? Number(kneeFlex.toFixed(1)) : null,
            trunk_lean_deg: Number.isFinite(trunkAng) ? Number(trunkAng.toFixed(1)) : null,
            arm_cross_pct: Number.isFinite(armCrossPct) ? Number(armCrossPct.toFixed(1)) : null,
            vertical_oscillation: Number.isFinite(verticalOsc) ? Number(verticalOsc.toFixed(4)) : null,
            visualization: visualization
        };
    };

    window.RLFormAnalyzerPose = { analyzeVideoFile };
</script>
<script>
    (function () {
        const routeAnalyze = @json(route('tools.form-analyzer.analyze'));
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const stateInstructions = document.getElementById('rlfa-state-instructions');
        const stateScanning = document.getElementById('rlfa-state-scanning');
        const stateResults = document.getElementById('rlfa-state-results');

        const videoInput = document.getElementById('rlfa-video-input');
        const uploadBtn = document.getElementById('rlfa-upload-btn');
        const retryBtn = document.getElementById('rlfa-retry-btn');
        const backBtn = document.getElementById('rlfa-back-btn');
        const modeDevice = document.getElementById('rlfa-mode-device');

        const scanText = document.getElementById('rlfa-scan-text');
        const scanSubtext = document.getElementById('rlfa-scan-subtext');
        const scanMetric1 = document.getElementById('rlfa-scan-metric-1');
        const scanMetric2 = document.getElementById('rlfa-scan-metric-2');

        const clientWarningsWrap = document.getElementById('rlfa-client-warnings');
        const clientWarningsBox = clientWarningsWrap?.querySelector('div');

        const scoreEl = document.getElementById('rlfa-score');
        const scoreBadge = document.getElementById('rlfa-score-badge');
        const metaDuration = document.getElementById('rlfa-meta-duration');
        const metaResolution = document.getElementById('rlfa-meta-resolution');
        const metaFps = document.getElementById('rlfa-meta-fps');
        const metaSize = document.getElementById('rlfa-meta-size');
        const coachMessageEl = document.getElementById('rlfa-coach-message');
        const issuesWrap = document.getElementById('rlfa-issues-wrap');
        const issuesEl = document.getElementById('rlfa-issues');
        const suggestionsWrap = document.getElementById('rlfa-suggestions-wrap');
        const suggestionsEl = document.getElementById('rlfa-suggestions');
        const positivesWrap = document.getElementById('rlfa-positives-wrap');
        const positivesEl = document.getElementById('rlfa-positives');
        const formIssuesWrap = document.getElementById('rlfa-formissues-wrap');
        const formIssuesEl = document.getElementById('rlfa-formissues');
        const strengthWrap = document.getElementById('rlfa-strength-wrap');
        const strengthEl = document.getElementById('rlfa-strength');
        const recoveryWrap = document.getElementById('rlfa-recovery-wrap');
        const recoveryEl = document.getElementById('rlfa-recovery');
        const visualizationWrap = document.getElementById('rlfa-visualization-wrap');
        const visualizationImg = document.getElementById('rlfa-visualization-img');

        const formatBytes = (bytes) => {
            if (!Number.isFinite(bytes) || bytes <= 0) return '--';
            const units = ['B','KB','MB','GB'];
            const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
            const val = bytes / Math.pow(1024, i);
            return (val >= 100 ? val.toFixed(0) : val >= 10 ? val.toFixed(1) : val.toFixed(2)) + ' ' + units[i];
        };

        const formatDuration = (seconds) => {
            if (!Number.isFinite(seconds) || seconds <= 0) return '--';
            const m = Math.floor(seconds / 60);
            const s = Math.round(seconds % 60);
            return (m > 0 ? (m + 'm ') : '') + s + 's';
        };

        const showInstructions = () => {
            stateResults.classList.add('hidden');
            stateResults.classList.remove('flex');
            stateScanning.classList.add('hidden');
            stateInstructions.classList.remove('hidden');
        };

        const showScanning = () => {
            stateInstructions.classList.add('hidden');
            stateScanning.classList.remove('hidden');
            stateResults.classList.add('hidden');
            stateResults.classList.remove('flex');
        };

        const showResults = () => {
            stateScanning.classList.add('hidden');
            stateResults.classList.remove('hidden');
            stateResults.classList.add('flex');
        };

        const resetWarnings = () => {
            if (!clientWarningsWrap || !clientWarningsBox) return;
            clientWarningsWrap.classList.add('hidden');
            clientWarningsBox.innerHTML = '';
        };

        const pushWarning = (message) => {
            if (!clientWarningsWrap || !clientWarningsBox) return;
            clientWarningsWrap.classList.remove('hidden');
            const p = document.createElement('p');
            p.textContent = message;
            clientWarningsBox.appendChild(p);
        };

        const scoreToBadge = (score) => {
            if (score >= 85) return { text: 'SIAP ANALISIS', className: 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30' };
            if (score >= 70) return { text: 'CUKUP OK', className: 'bg-blue-500/20 text-blue-300 border-blue-500/30' };
            if (score >= 55) return { text: 'PERLU PERBAIKAN', className: 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30' };
            return { text: 'RISIKO TINGGI', className: 'bg-red-500/20 text-red-300 border-red-500/30' };
        };

        const createChip = (item) => {
            const severity = (item.severity || 'info').toLowerCase();
            const wrap = document.createElement('div');
            let classes = 'bg-slate-900/60 p-3 rounded-lg border text-xs';
            if (severity === 'high' || severity === 'error') classes += ' border-red-500/30 text-slate-200';
            else if (severity === 'medium' || severity === 'warn' || severity === 'warning') classes += ' border-yellow-500/30 text-slate-200';
            else if (severity === 'good' || severity === 'success') classes += ' border-emerald-500/30 text-slate-200';
            else classes += ' border-slate-800 text-slate-200';
            wrap.className = classes;

            const title = document.createElement('div');
            title.className = 'flex items-center justify-between gap-2';
            title.innerHTML = `<span class="font-bold text-white">${item.title || 'Catatan'}</span><span class="font-mono text-[10px] text-slate-400">${(item.code || '').toUpperCase()}</span>`;

            const body = document.createElement('div');
            body.className = 'mt-1 text-slate-300 leading-relaxed';
            body.textContent = item.message || '';

            wrap.appendChild(title);
            wrap.appendChild(body);
            return wrap;
        };

        const typewriter = (el, text) => {
            el.innerHTML = '';
            let i = 0;
            const t = setInterval(() => {
                if (i >= text.length) { clearInterval(t); return; }
                const ch = text.charAt(i);
                if (ch === '\n') el.innerHTML += '<br>';
                else el.innerHTML += ch;
                i++;
                stateResults.scrollTop = stateResults.scrollHeight;
            }, 12);
        };

        const validateClient = async (file) => {
            resetWarnings();
            const maxBytes = 150 * 1024 * 1024;
            const okTypes = [
                'video/mp4',
                'video/quicktime',
                'video/webm',
                'video/x-matroska'
            ];
            if (!file) return { ok: false, meta: {} };
            if (file.size > maxBytes) {
                pushWarning(`Ukuran video terlalu besar (${formatBytes(file.size)}). Maksimal 150 MB.`);
                return { ok: false, meta: {} };
            }
            if (file.type && !okTypes.includes(file.type)) {
                pushWarning(`Format video kurang didukung (${file.type || 'unknown'}). Gunakan MP4/MOV/WebM/MKV.`);
            }

            const meta = await new Promise((resolve) => {
                const video = document.createElement('video');
                video.preload = 'metadata';
                video.muted = true;
                video.playsInline = true;
                const url = URL.createObjectURL(file);
                video.src = url;
                video.onloadedmetadata = () => {
                    const duration = Number(video.duration) || 0;
                    const width = Number(video.videoWidth) || 0;
                    const height = Number(video.videoHeight) || 0;
                    URL.revokeObjectURL(url);
                    resolve({ duration, width, height });
                };
                video.onerror = () => {
                    URL.revokeObjectURL(url);
                    resolve({ duration: 0, width: 0, height: 0 });
                };
            });

            if (meta.duration && (meta.duration < 4 || meta.duration > 25)) {
                pushWarning(`Durasi ${formatDuration(meta.duration)}. Rekomendasi 5–10 detik, maksimal 20 detik.`);
            }
            if (meta.width && meta.height) {
                const isPortrait = meta.height > meta.width;
                if (isPortrait) pushWarning('Video terdeteksi portrait. Untuk analisis form, landscape (horizontal) biasanya lebih optimal.');
                if (Math.max(meta.width, meta.height) < 720) pushWarning('Resolusi cukup rendah. Usahakan minimal 720p agar lutut & ankle terbaca jelas.');
            }

            return { ok: true, meta };
        };

        const renderResults = (result) => {
            if (visualizationWrap && visualizationImg) {
                if (result.visualization) {
                    visualizationImg.src = result.visualization;
                    visualizationWrap.classList.remove('hidden');
                } else {
                    visualizationWrap.classList.add('hidden');
                    visualizationImg.src = '';
                }
            }

            const score = Number(result.score) || 0;
            scoreEl.textContent = String(score);

            const badge = scoreToBadge(score);
            scoreBadge.className = 'px-2 py-1 rounded text-xs font-bold border ' + badge.className;
            scoreBadge.textContent = badge.text;

            const display = result?.meta?.display || {};
            metaDuration.textContent = display.duration_human || '--';
            metaResolution.textContent = display.resolution || '--';
            metaFps.textContent = display.fps_human || '--';

            const compression = result?.meta?.compression || {};
            const original = result?.meta?.original || {};
            const savedPct = compression?.saved_percent;
            if (compression?.used && original?.size_human && display?.size_human && Number.isFinite(savedPct)) {
                metaSize.textContent = `${original.size_human} → ${display.size_human} (${savedPct}% hemat)`;
            } else {
                metaSize.textContent = display.size_human || original.size_human || '--';
            }

            const coach = result?.coach_message || 'Analisis selesai. Coba ulang dengan video tampak samping 5–10 detik untuk hasil paling bagus.';
            typewriter(coachMessageEl, coach);

            issuesEl.innerHTML = '';
            suggestionsEl.innerHTML = '';
            positivesEl.innerHTML = '';
            formIssuesEl.innerHTML = '';
            strengthEl.innerHTML = '';
            recoveryEl.innerHTML = '';
            issuesWrap.classList.add('hidden');
            suggestionsWrap.classList.add('hidden');
            positivesWrap.classList.add('hidden');
            formIssuesWrap.classList.add('hidden');
            strengthWrap.classList.add('hidden');
            recoveryWrap.classList.add('hidden');

            const positives = Array.isArray(result.positives) ? result.positives : [];
            if (positives.length) {
                positives.forEach((x) => positivesEl.appendChild(createChip(x)));
                positivesWrap.classList.remove('hidden');
            }

            const issues = Array.isArray(result.issues) ? result.issues : [];
            if (issues.length) {
                issues.forEach((x) => issuesEl.appendChild(createChip(x)));
                issuesWrap.classList.remove('hidden');
            }

            const suggestions = Array.isArray(result.suggestions) ? result.suggestions : [];
            if (suggestions.length) {
                suggestions.forEach((x) => suggestionsEl.appendChild(createChip(x)));
                suggestionsWrap.classList.remove('hidden');
            }

            const formIssues = Array.isArray(result.form_issues) ? result.form_issues : [];
            if (formIssues.length) {
                formIssues.forEach((x) => formIssuesEl.appendChild(createChip(x)));
                formIssuesWrap.classList.remove('hidden');
            }

            const strength = Array.isArray(result.strength_plan) ? result.strength_plan : [];
            if (strength.length) {
                strength.forEach((x) => strengthEl.appendChild(createChip(x)));
                strengthWrap.classList.remove('hidden');
            }

            const recovery = Array.isArray(result.recovery_plan) ? result.recovery_plan : [];
            if (recovery.length) {
                recovery.forEach((x) => recoveryEl.appendChild(createChip(x)));
                recoveryWrap.classList.remove('hidden');
            }
        };

        const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

        const analyze = async (file, clientMeta, metrics) => {
            showScanning();
            scanText.textContent = 'MENYIAPKAN...';
            scanSubtext.textContent = 'Jangan tutup halaman ini.';
            scanMetric1.textContent = '--';
            scanMetric2.textContent = '--';

            const uploadVideo = modeDevice ? !modeDevice.checked : true;

            let attempt = 0;
            while (attempt < 25) {
                attempt++;
                const result = await new Promise((resolve) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', routeAnalyze, true);
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrf);
                    xhr.setRequestHeader('Accept', 'application/json');

                    if (uploadVideo) {
                        xhr.upload.onprogress = (e) => {
                            if (!e.lengthComputable) return;
                            const pct = Math.max(0, Math.min(100, Math.round((e.loaded / e.total) * 100)));
                            scanMetric1.textContent = pct + '%';
                            if (pct < 100) {
                                scanText.textContent = 'MENGUNGGAH...';
                                scanSubtext.textContent = `Terkirim ${formatBytes(e.loaded)} dari ${formatBytes(e.total)}`;
                            } else {
                                scanText.textContent = 'MEMPROSES...';
                                scanSubtext.textContent = 'Mengoptimalkan & menyusun feedback.';
                            }
                        };
                    }

                    xhr.onreadystatechange = () => {
                        if (xhr.readyState !== 4) return;
                        let json = null;
                        try { json = JSON.parse(xhr.responseText); } catch (e) {}
                        resolve({ status: xhr.status, json });
                    };

                    const fd = new FormData();
                    fd.append('upload_video', uploadVideo ? '1' : '0');
                    if (uploadVideo) fd.append('video', file);
                    if (metrics) fd.append('metrics', JSON.stringify(metrics));
                    fd.append('client_duration', String(clientMeta?.duration || ''));
                    fd.append('client_width', String(clientMeta?.width || ''));
                    fd.append('client_height', String(clientMeta?.height || ''));
                    xhr.send(fd);

                    if (!uploadVideo) {
                        scanText.textContent = 'MENGANTRI...';
                        scanMetric1.textContent = 'LOCAL';
                        scanMetric2.textContent = 'POSE';
                    }
                });

                if (result.status >= 200 && result.status < 300) return result.json;
                if (result.status === 429 && result.json && result.json.queued) {
                    const retryAfter = Math.max(2, Math.min(20, Number(result.json.retry_after) || 5));
                    let left = retryAfter;
                    scanText.textContent = 'ANTRIAN PENUH';
                    scanSubtext.textContent = 'Menunggu slot kosong...';
                    scanMetric1.textContent = `${attempt}`;
                    scanMetric2.textContent = `${left}s`;
                    while (left > 0) {
                        await sleep(1000);
                        left -= 1;
                        scanMetric2.textContent = `${left}s`;
                    }
                    continue;
                }

                throw (result.json || { error: 'Gagal memproses video.' });
            }

            throw { error: 'Antrian terlalu panjang. Coba lagi beberapa menit.' };
        };

        const start = async () => {
            const file = videoInput.files?.[0];
            if (!file) return;
            const v = await validateClient(file);
            if (!v.ok) return;

            try {
                showScanning();
                scanText.textContent = 'ANALISIS FORM (DI PERANGKAT)...';
                scanSubtext.textContent = 'Memindai pose & pola langkah.';
                scanMetric1.textContent = '0%';
                scanMetric2.textContent = 'POSE';

                let metrics = null;
                if (window.RLFormAnalyzerPose?.analyzeVideoFile) {
                    try {
                        metrics = await window.RLFormAnalyzerPose.analyzeVideoFile(file, ({ done, total }) => {
                            const pct = total ? Math.round((done / total) * 100) : 0;
                            scanMetric1.textContent = pct + '%';
                        });
                    } catch (e) {
                        metrics = null;
                        scanText.textContent = 'ANALISIS POSE GAGAL';
                        scanSubtext.textContent = 'Tetap memberi feedback kualitas video.';
                        await sleep(800);
                    }
                }

                const result = await analyze(file, v.meta, metrics);
                renderResults(result);
                showResults();
            } catch (e) {
                const msg = e?.message || e?.error || 'Video gagal diproses. Coba lagi dengan durasi 5–10 detik dan ukuran lebih kecil.';
                showInstructions();
                pushWarning(msg);
            } finally {
                videoInput.value = '';
            }
        };

        uploadBtn?.addEventListener('click', () => {
            resetWarnings();
            videoInput?.click();
        });
        videoInput?.addEventListener('change', start);
        retryBtn?.addEventListener('click', () => {
            showInstructions();
            resetWarnings();
        });
        backBtn?.addEventListener('click', () => {
            showInstructions();
            resetWarnings();
        });
    })();
</script>
@endpush
