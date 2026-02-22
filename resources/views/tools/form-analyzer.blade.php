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
                            <input id="rlfa-photo-input-landing" type="file" accept="image/*" capture="environment" class="hidden">
                            <input id="rlfa-photo-input-lever" type="file" accept="image/*" capture="environment" class="hidden">
                            <input id="rlfa-photo-input-push" type="file" accept="image/*" capture="environment" class="hidden">
                            <input id="rlfa-photo-input-pull" type="file" accept="image/*" capture="environment" class="hidden">
                            <input id="rlfa-photo-input-front" type="file" accept="image/*" capture="environment" class="hidden">

                            <div id="rlfa-state-instructions" class="absolute inset-0 flex flex-col justify-end p-6 bg-gradient-to-b from-slate-800 to-slate-950 transition-opacity duration-500 z-30">
                                <div class="absolute top-0 left-0 w-full h-1/2 bg-cover bg-center opacity-40 mask-image-gradient" style="background-image: url('https://res.cloudinary.com/dslfarxct/image/upload/v1769242639/599829521_18496489774078626_8467367785730490975_n_osdvbh.jpg')"></div>
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

                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <button id="rlfa-inputmode-video" type="button" class="px-3 py-2 rounded-xl border border-neon/40 bg-neon/10 text-neon font-bold text-xs">Mode Video</button>
                                        <button id="rlfa-inputmode-photos" type="button" class="px-3 py-2 rounded-xl border border-slate-700 bg-slate-900/40 text-slate-300 font-bold text-xs hover:text-white">Mode 5 Foto</button>
                                    </div>

                                    <div id="rlfa-photo-slots" class="mt-4 hidden space-y-2">
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button" id="rlfa-photo-btn-landing" class="rlfa-photo-btn flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-800 bg-slate-900/60 text-slate-200 text-xs font-bold">
                                                <span class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center">
                                                    <img id="rlfa-photo-prev-landing" class="hidden w-full h-full object-cover" alt="Landing">
                                                    <i id="rlfa-photo-ico-landing" class="fa-regular fa-image text-slate-500"></i>
                                                </span>
                                                <span>Landing</span>
                                            </button>
                                            <button type="button" id="rlfa-photo-btn-lever" class="rlfa-photo-btn flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-800 bg-slate-900/60 text-slate-200 text-xs font-bold">
                                                <span class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center">
                                                    <img id="rlfa-photo-prev-lever" class="hidden w-full h-full object-cover" alt="Lever">
                                                    <i id="rlfa-photo-ico-lever" class="fa-regular fa-image text-slate-500"></i>
                                                </span>
                                                <span>Lever</span>
                                            </button>
                                            <button type="button" id="rlfa-photo-btn-push" class="rlfa-photo-btn flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-800 bg-slate-900/60 text-slate-200 text-xs font-bold">
                                                <span class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center">
                                                    <img id="rlfa-photo-prev-push" class="hidden w-full h-full object-cover" alt="Push">
                                                    <i id="rlfa-photo-ico-push" class="fa-regular fa-image text-slate-500"></i>
                                                </span>
                                                <span>Push</span>
                                            </button>
                                            <button type="button" id="rlfa-photo-btn-pull" class="rlfa-photo-btn flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-800 bg-slate-900/60 text-slate-200 text-xs font-bold">
                                                <span class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center">
                                                    <img id="rlfa-photo-prev-pull" class="hidden w-full h-full object-cover" alt="Pull">
                                                    <i id="rlfa-photo-ico-pull" class="fa-regular fa-image text-slate-500"></i>
                                                </span>
                                                <span>Pull</span>
                                            </button>
                                            <button type="button" id="rlfa-photo-btn-front" class="rlfa-photo-btn col-span-2 flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-800 bg-slate-900/60 text-slate-200 text-xs font-bold">
                                                <span class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 overflow-hidden flex items-center justify-center">
                                                    <img id="rlfa-photo-prev-front" class="hidden w-full h-full object-cover" alt="Front">
                                                    <i id="rlfa-photo-ico-front" class="fa-regular fa-image text-slate-500"></i>
                                                </span>
                                                <span>Front (opsional)</span>
                                            </button>
                                        </div>

                                        <button id="rlfa-analyze-photos-btn" type="button" disabled class="w-full bg-white/10 text-white font-bold py-3 rounded-xl text-sm border border-slate-800 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-white hover:text-dark transition">
                                            Analisis 5 Foto
                                        </button>
                                        <div class="text-[10px] text-slate-400 leading-relaxed">
                                            Minimal 4 foto (Landing, Lever, Push, Pull). Front opsional. Disarankan tampak samping untuk 4 foto pertama.
                                        </div>
                                    </div>

                                    <input id="rlfa-mode-device" type="hidden" value="1">

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
                                    <div class="flex flex-col gap-3 mb-3">
                                        <div class="flex justify-between items-end">
                                            <div>
                                                <p class="text-[10px] text-slate-400 uppercase tracking-widest">Skor Video (kualitas data)</p>
                                                <h2 class="text-2xl font-black text-slate-200 italic">
                                                    <span id="rlfa-video-score">--</span><span class="text-sm text-slate-500 font-normal">/100</span>
                                                </h2>
                                            </div>
                                            <div id="rlfa-video-score-badge" class="bg-slate-800 text-slate-200 px-2 py-1 rounded text-[10px] font-bold border border-slate-700">
                                                MENUNGGU
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-end">
                                            <div>
                                                <p class="text-[10px] text-slate-400 uppercase tracking-widest">Skor Form Lari</p>
                                                <h2 class="text-3xl font-black text-white italic">
                                                    <span id="rlfa-score">--</span><span class="text-lg text-slate-500 font-normal">/100</span>
                                                </h2>
                                            </div>
                                            <div id="rlfa-score-badge" class="bg-slate-800 text-slate-200 px-2 py-1 rounded text-xs font-bold border border-slate-700">
                                                MENUNGGU
                                            </div>
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

                                    <div id="rlfa-ideal-preview" class="mb-6 p-4 rounded-2xl border border-slate-800 bg-slate-900/60">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Perfect Running Form (Guide)</div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                                            <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-3 flex items-center justify-center">
                                                <svg viewBox="0 0 220 320" class="w-full h-56" fill="none">
                                                    <rect x="8" y="8" width="204" height="304" rx="16" stroke="#1f2937" stroke-width="2"/>
                                                    <line x1="110" y1="40" x2="110" y2="120" stroke="#ccff00" stroke-width="4" stroke-linecap="round"/>
                                                    <line x1="110" y1="120" x2="140" y2="200" stroke="#ccff00" stroke-width="4" stroke-linecap="round"/>
                                                    <line x1="140" y1="200" x2="90" y2="260" stroke="#ccff00" stroke-width="4" stroke-linecap="round"/>
                                                    <circle cx="110" cy="30" r="10" fill="#ccff00"/>
                                                    <circle cx="110" cy="120" r="5" fill="#ffffff"/>
                                                    <circle cx="140" cy="200" r="5" fill="#ffffff"/>
                                                    <circle cx="90" cy="260" r="5" fill="#60a5fa"/>
                                                    <path d="M110 120 L150 120" stroke="#60a5fa" stroke-width="2" stroke-dasharray="4 4"/>
                                                    <path d="M140 200 L170 200" stroke="#60a5fa" stroke-width="2" stroke-dasharray="4 4"/>
                                                    <text x="118" y="110" fill="#ccff00" font-size="10" font-weight="700">Trunk 5–10°</text>
                                                    <text x="148" y="190" fill="#ccff00" font-size="10" font-weight="700">Knee 30–45°</text>
                                                    <text x="100" y="250" fill="#ccff00" font-size="10" font-weight="700">Shin 85–95°</text>
                                                </svg>
                                            </div>
                                            <div class="space-y-2 text-xs text-slate-300">
                                                <div class="flex items-center justify-between bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-2">
                                                    <span>Trunk lean</span>
                                                    <span class="font-mono text-white">5–10°</span>
                                                </div>
                                                <div class="flex items-center justify-between bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-2">
                                                    <span>Knee flex (stance)</span>
                                                    <span class="font-mono text-white">30–45°</span>
                                                </div>
                                                <div class="flex items-center justify-between bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-2">
                                                    <span>Shin angle</span>
                                                    <span class="font-mono text-white">85–95°</span>
                                                </div>
                                                <div class="flex items-center justify-between bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-2">
                                                    <span>Foot strike</span>
                                                    <span class="font-mono text-white">Under hip</span>
                                                </div>
                                                <div class="flex items-center justify-between bg-slate-950/60 border border-slate-800 rounded-lg px-3 py-2">
                                                    <span>Arm swing</span>
                                                    <span class="font-mono text-white">Compact & drive</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-slate-900 rounded-tr-2xl rounded-br-2xl rounded-bl-2xl rounded-tl-sm p-4 border border-slate-800 relative">
                                        <div class="absolute -top-3 left-0 bg-neon text-dark text-[10px] font-bold px-2 py-0.5 rounded">AI COACH SAYS:</div>
                                        <button id="rlfa-tts-btn" type="button" class="absolute -top-3 right-0 bg-slate-800 text-neon text-[10px] font-bold px-2 py-0.5 rounded border border-slate-700 hover:bg-slate-700 transition flex items-center gap-1">
                                            <i class="fa-solid fa-volume-high"></i> BACAKAN
                                        </button>
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

                                    <div id="rlfa-formreport-wrap" class="space-y-3 hidden">
                                        <h4 class="text-xs font-bold text-slate-500 uppercase mt-4 mb-2">Laporan Form</h4>
                                        <div id="rlfa-formreport" class="space-y-3"></div>
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
                                        <button id="rlfa-advanced-btn" type="button" class="w-full bg-neon text-dark font-bold py-3 rounded-xl text-sm hover:bg-white">
                                            Lihat Analisis Advance
                                        </button>
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

                        <div id="rlfa-advanced-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-black/80 backdrop-blur p-4">
                            <div class="w-full max-w-5xl max-h-[90vh] overflow-y-auto no-scrollbar bg-slate-950 border border-slate-800 rounded-2xl shadow-2xl">
                                <div class="sticky top-0 z-10 bg-slate-950/95 backdrop-blur border-b border-slate-800 p-4 flex items-center justify-between">
                                    <div>
                                        <div class="text-xs text-slate-400 uppercase tracking-widest">Laporan Advance</div>
                                        <div class="text-white font-black text-lg">Analisis Form Lengkap</div>
                                    </div>
                                    <button id="rlfa-advanced-close" type="button" class="text-slate-400 hover:text-white bg-slate-900 border border-slate-800 rounded-lg px-3 py-2">Tutup</button>
                                </div>
                                <div class="p-5 space-y-6">
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        <div class="space-y-3">
                                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Preview Analisis</div>
                                            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl overflow-hidden">
                                                <img id="rlfa-advanced-preview" class="w-full h-auto object-cover" src="" alt="Preview Analisis">
                                            </div>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Ideal Form Guide</div>
                                            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-3">
                                                <svg viewBox="0 0 220 320" class="w-full h-56" fill="none">
                                                    <rect x="8" y="8" width="204" height="304" rx="16" stroke="#1f2937" stroke-width="2"/>
                                                    <line x1="110" y1="40" x2="110" y2="120" stroke="#ccff00" stroke-width="4" stroke-linecap="round"/>
                                                    <line x1="110" y1="120" x2="140" y2="200" stroke="#ccff00" stroke-width="4" stroke-linecap="round"/>
                                                    <line x1="140" y1="200" x2="90" y2="260" stroke="#ccff00" stroke-width="4" stroke-linecap="round"/>
                                                    <circle cx="110" cy="30" r="10" fill="#ccff00"/>
                                                    <circle cx="110" cy="120" r="5" fill="#ffffff"/>
                                                    <circle cx="140" cy="200" r="5" fill="#ffffff"/>
                                                    <circle cx="90" cy="260" r="5" fill="#60a5fa"/>
                                                    <path d="M110 120 L150 120" stroke="#60a5fa" stroke-width="2" stroke-dasharray="4 4"/>
                                                    <path d="M140 200 L170 200" stroke="#60a5fa" stroke-width="2" stroke-dasharray="4 4"/>
                                                    <text x="118" y="110" fill="#ccff00" font-size="10" font-weight="700">Trunk 5–10°</text>
                                                    <text x="148" y="190" fill="#ccff00" font-size="10" font-weight="700">Knee 30–45°</text>
                                                    <text x="100" y="250" fill="#ccff00" font-size="10" font-weight="700">Shin 85–95°</text>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="rlfa-adv-snapshots-wrap" class="hidden">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Snapshot Form Bermasalah</div>
                                        <div id="rlfa-adv-snapshots" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"></div>
                                    </div>

                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-widest">Trunk Lean</div>
                                            <div class="text-white font-mono" id="rlfa-adv-trunk">--</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-widest">Knee Flex</div>
                                            <div class="text-white font-mono" id="rlfa-adv-knee">--</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-widest">Shin Angle</div>
                                            <div class="text-white font-mono" id="rlfa-adv-shin">--</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-widest">Arm Swing</div>
                                            <div class="text-white font-mono" id="rlfa-adv-arm">--</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-widest">Overstride</div>
                                            <div class="text-white font-mono" id="rlfa-adv-overstride">--</div>
                                        </div>
                                        <div class="bg-slate-900/60 border border-slate-800 rounded-lg p-2">
                                            <div class="text-slate-400 uppercase tracking-widest">Heel Strike</div>
                                            <div class="text-white font-mono" id="rlfa-adv-heel">--</div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Analisis Ayunan & Postur</div>
                                            <div id="rlfa-adv-swing" class="bg-slate-900/60 border border-slate-800 rounded-xl p-3 text-slate-200 text-sm"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Saran Penguatan</div>
                                            <div id="rlfa-adv-strength" class="space-y-2"></div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        <div>
                                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Hip & Core Stability</div>
                                            <div id="rlfa-adv-hipcore" class="bg-slate-900/60 border border-slate-800 rounded-xl p-3 text-slate-200 text-sm"></div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Calf & Ankle Load</div>
                                            <div id="rlfa-adv-calf" class="bg-slate-900/60 border border-slate-800 rounded-xl p-3 text-slate-200 text-sm"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Laporan Form Lengkap</div>
                                        <div id="rlfa-adv-report" class="space-y-3"></div>
                                    </div>

                                    <div>
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Preview Rekomendasi</div>
                                        <div id="rlfa-adv-suggestions" class="space-y-2"></div>
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
    let landmarkerImagePromise = null;

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

    const getLandmarkerImage = async () => {
        if (landmarkerImagePromise) return landmarkerImagePromise;
        landmarkerImagePromise = (async () => {
            const vision = await FilesetResolver.forVisionTasks("https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/wasm");
            return await PoseLandmarker.createFromOptions(vision, {
                baseOptions: {
                    modelAssetPath: "https://storage.googleapis.com/mediapipe-models/pose_landmarker/pose_landmarker_lite/float16/1/pose_landmarker_lite.task",
                },
                runningMode: "IMAGE",
                numPoses: 1,
                minPoseDetectionConfidence: 0.5,
                minPosePresenceConfidence: 0.5,
                minTrackingConfidence: 0.5,
            });
        })();
        return landmarkerImagePromise;
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

    const drawBiomechanicsImage = (img, landmarks, width, height) => {
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);
        ctx.fillStyle = 'rgba(0, 0, 0, 0.35)';
        ctx.fillRect(0, 0, width, height);
        if (!landmarks) return canvas.toDataURL('image/jpeg', 0.85);

        const drawLine = (start, end, color = '#ccff00', widthPx = 3) => {
            if (!start || !end) return;
            ctx.beginPath();
            ctx.moveTo(start.x * width, start.y * height);
            ctx.lineTo(end.x * width, end.y * height);
            ctx.strokeStyle = color;
            ctx.lineWidth = widthPx;
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
            leftHeel: 29, rightHeel: 30, leftFootIndex: 31, rightFootIndex: 32
        };
        const lVis = (landmarks[L.leftAnkle]?.visibility ?? 0) + (landmarks[L.leftKnee]?.visibility ?? 0);
        const rVis = (landmarks[L.rightAnkle]?.visibility ?? 0) + (landmarks[L.rightKnee]?.visibility ?? 0);
        const isLeft = lVis >= rVis;
        const hip = landmarks[isLeft ? L.leftHip : L.rightHip];
        const knee = landmarks[isLeft ? L.leftKnee : L.rightKnee];
        const ankle = landmarks[isLeft ? L.leftAnkle : L.rightAnkle];
        const shoulder = landmarks[isLeft ? L.leftShoulder : L.rightShoulder];
        const heel = landmarks[isLeft ? L.leftHeel : L.rightHeel];
        const foot = landmarks[isLeft ? L.leftFootIndex : L.rightFootIndex];

        drawLine(shoulder, hip, '#ccff00');
        drawLine(hip, knee, '#ccff00');
        drawLine(knee, ankle, '#ccff00');
        drawLine(ankle, foot, '#ccff00');
        drawLine(heel, ankle, '#ccff00', 2);
        drawPoint(shoulder);
        drawPoint(hip);
        drawPoint(knee);
        drawPoint(ankle);
        drawPoint(heel, '#60a5fa', 4);
        drawPoint(foot);

        if (hip && knee && ankle) {
            const kAngle = angle(hip, knee, ankle);
            const flex = kAngle ? (180 - kAngle).toFixed(0) : '--';
            drawAngle(knee, `${flex}°`);
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

        const CATEGORY_REQUIREMENTS = {
            landing: { min: 3 },
            lever: { min: 3 },
            push: { min: 3 },
            pull: { min: 3 },
            arm_swing: { min: 4 },
            posture: { min: 4 },
        };

        const maxSamples = clamp(Math.floor(duration * 6), 30, 90);
        const start = clamp(0.4, 0, Math.max(0, duration - 0.4));
        const end = clamp(duration - 0.4, 0, duration);
        const step = maxSamples > 1 ? (end - start) / (maxSamples - 1) : 0;

        const frames = [];
        const counts = Object.keys(CATEGORY_REQUIREMENTS).reduce((acc, k) => { acc[k] = 0; return acc; }, {});
        const best = Object.keys(CATEGORY_REQUIREMENTS).reduce((acc, k) => { acc[k] = { score: -1, t: null, landmarks: null }; return acc; }, {});

        let runningMaxFootY = 0;
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
        const isSideView = (lShoulder, rShoulder, lHip, rHip) => {
            if (!lShoulder || !rShoulder || !lHip || !rHip) return false;
            const shoulderDx = Math.abs(lShoulder.x - rShoulder.x);
            const hipDx = Math.abs(lHip.x - rHip.x);
            const avgDx = (shoulderDx + hipDx) / 2;
            return avgDx <= 0.18;
        };

        const inFrame = (pt) => {
            if (!pt) return false;
            return pt.x > 0.05 && pt.x < 0.95 && pt.y > 0.05 && pt.y < 0.95;
        };

        const qualityGate = (keyPts, requiredKeys = []) => {
            const pts = Object.values(keyPts).filter(Boolean);
            const vis = pts.length ? (pts.reduce((a, p) => a + (p.v ?? 0), 0) / pts.length) : 0;
            if (vis < 0.55) return { ok: false, score: vis };

            for (const k of requiredKeys) {
                if (!keyPts[k] || !inFrame(keyPts[k]) || (keyPts[k].v ?? 0) < 0.5) {
                    return { ok: false, score: vis };
                }
            }

            const inFrameRatio = pts.length ? (pts.filter(inFrame).length / pts.length) : 0;
            if (inFrameRatio < 0.75) return { ok: false, score: vis * inFrameRatio };

            return { ok: true, score: (vis * 0.75) + (inFrameRatio * 0.25) };
        };

        const stopReady = () => {
            return Object.keys(CATEGORY_REQUIREMENTS).every((k) => counts[k] >= CATEGORY_REQUIREMENTS[k].min && !!best[k].landmarks);
        };

        for (let i = 0; i < maxSamples; i++) {
            const t = start + step * i;
            video.currentTime = t;
            await new Promise((resolve) => {
                const handler = () => { video.removeEventListener('seeked', handler); resolve(); };
                video.addEventListener('seeked', handler);
            });

            const r = landmarker.detectForVideo(video, Math.round(t * 1000));
            const lm = r?.landmarks?.[0];
            if (!lm || !Array.isArray(lm)) {
                if (onProgress) onProgress({ phase: 'pose', done: i + 1, total: maxSamples, coverage: { ...counts } });
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
            const lHip = getPt(lm, L.leftHip);
            const rHip = getPt(lm, L.rightHip);

            if (!hip || !knee || !ankle || !heel || !footIndex || !lShoulder || !rShoulder) {
                if (onProgress) onProgress({ phase: 'pose', done: i + 1, total: maxSamples, coverage: { ...counts } });
                continue;
            }

            const midX = (lShoulder.x + rShoulder.x) / 2;
            const sideOk = isSideView(lShoulder, rShoulder, lHip, rHip);
            if (!sideOk) {
                if (onProgress) onProgress({ phase: 'pose', done: i + 1, total: maxSamples, coverage: { ...counts }, note: 'Menunggu posisi samping...' });
                continue;
            }

            const keyPts = {
                hip,
                knee,
                ankle,
                heel,
                footIndex,
                lShoulder,
                rShoulder,
                lWrist,
                rWrist,
            };

            const qBase = qualityGate(keyPts, ['hip', 'knee', 'ankle', 'lShoulder', 'rShoulder', 'footIndex', 'heel']);
            if (!qBase.ok) {
                if (onProgress) onProgress({ phase: 'pose', done: i + 1, total: maxSamples, coverage: { ...counts } });
                continue;
            }

            const kAngle = angle(hip, knee, ankle);
            const kneeFlex = kAngle ? (180 - kAngle) : null;

            const shinDx = Math.abs(knee.x - ankle.x);
            const shinDy = Math.abs(knee.y - ankle.y);
            const shinAng = (shinDy > 0) ? toDeg(Math.atan2(shinDx, shinDy)) : null;

            const trunkDx = midX - hip.x;
            const trunkDy = ((lShoulder.y + rShoulder.y) / 2 - hip.y);
            const trunkAng = trunkDy !== 0 ? Math.abs(toDeg(Math.atan2(trunkDx, -trunkDy))) : null;

            const cross = ((lWrist && lWrist.x > midX + 0.03) || (rWrist && rWrist.x < midX - 0.03)) ? 1 : 0;

            const footY = Math.max(ankle.y, heel.y, footIndex.y);
            runningMaxFootY = Math.max(runningMaxFootY, footY);
            const ankleHipDx = Math.abs(ankle.x - hip.x);
            const dir = (footIndex.x - heel.x) >= 0 ? 1 : -1;
            const ankleRel = (ankle.x - hip.x) * dir;

            const footContact = footY > (runningMaxFootY - 0.035);
            const wristOk = (lWrist && (lWrist.v ?? 0) >= 0.5 && inFrame(lWrist)) || (rWrist && (rWrist.v ?? 0) >= 0.5 && inFrame(rWrist));

            const categories = [];
            if (footContact && ankleHipDx > 0.06) categories.push('landing');
            if (footContact && ankleHipDx >= 0.03 && ankleHipDx <= 0.09 && kneeFlex !== null && kneeFlex >= 22 && kneeFlex <= 60) categories.push('lever');
            if (footContact && ankleRel < -0.02) categories.push('push');
            if (!footContact && kneeFlex !== null && kneeFlex >= 35 && ankleHipDx > 0.03) categories.push('pull');
            if (wristOk) categories.push('arm_swing');
            if (trunkAng !== null && Number.isFinite(trunkAng)) categories.push('posture');

            const qScore = qBase.score;
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
                dir,
                ankleRel,
                categories,
                qScore,
                landmarks: lm,
            });

            for (const cat of categories) {
                if (!CATEGORY_REQUIREMENTS[cat]) continue;
                counts[cat] += 1;
                if (qScore > best[cat].score) {
                    best[cat] = { score: qScore, t, landmarks: lm };
                }
            }

            if (onProgress) onProgress({ phase: 'pose', done: i + 1, total: maxSamples, coverage: { ...counts } });

            if (i >= 10 && stopReady()) {
                break;
            }
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

        const confidence = avg(frames.map((f) => f.qScore)) ?? 0;
        const kneeFlex = avg(contact.map((f) => f.kneeFlex));
        const shinAng = avg(contact.map((f) => f.shinAng));
        const trunkAng = avg(frames.map((f) => f.trunkAng));
        const armCrossPct = 100 * (frames.reduce((a, f) => a + f.cross, 0) / frames.length);
        const verticalOsc = std(frames.map((f) => f.hipY));
        const trunkStd = std(frames.map((f) => f.trunkAng));

        let visualization = null;
        let snapshots = [];
        try {
            const pickBest = () => {
                const landing = best.landing?.landmarks ? best.landing : null;
                const posture = best.posture?.landmarks ? best.posture : null;
                const lever = best.lever?.landmarks ? best.lever : null;
                if (landing) return landing;
                if (posture) return posture;
                if (lever) return lever;
                return null;
            };
            const bf = pickBest();
            if (bf && bf.landmarks) {
                video.currentTime = bf.t;
                await new Promise((resolve) => {
                    const handler = () => { video.removeEventListener('seeked', handler); resolve(); };
                    video.addEventListener('seeked', handler);
                });
                visualization = drawBiomechanics(video, bf.landmarks, width, height);
            }
        } catch (e) {
            console.error(e);
        }

        try {
            const phaseLabel = {
                landing: 'Landing',
                lever: 'Lever',
                push: 'Push',
                pull: 'Pull',
            };
            const phaseOrder = ['landing', 'lever', 'push', 'pull'];
            const used = new Set();
            for (const phase of phaseOrder) {
                if (snapshots.length >= 3) break;
                const bf = best[phase];
                if (!bf || !bf.landmarks || !Number.isFinite(bf.t) || used.has(bf.t)) continue;
                used.add(bf.t);
                video.currentTime = bf.t;
                await new Promise((resolve) => {
                    const handler = () => { video.removeEventListener('seeked', handler); resolve(); };
                    video.addEventListener('seeked', handler);
                });
                const img = drawBiomechanics(video, bf.landmarks, width, height);
                if (img) {
                    snapshots.push({ phase, label: phaseLabel[phase] || phase, image: img });
                }
            }
        } catch (e) {
            console.error(e);
        }

        URL.revokeObjectURL(url);

        const coverage = Object.keys(CATEGORY_REQUIREMENTS).reduce((acc, k) => {
            const min = CATEGORY_REQUIREMENTS[k].min;
            const count = counts[k] || 0;
            acc[k] = {
                count,
                min,
                ok: count >= min && !!best[k].landmarks,
                rep_t: best[k].t,
            };
            return acc;
        }, {});
        const coverageMissing = Object.keys(coverage).filter((k) => !coverage[k].ok);

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
            trunk_std_deg: Number.isFinite(trunkStd) ? Number(trunkStd.toFixed(2)) : null,
            coverage: coverage,
            coverage_missing: coverageMissing,
            visualization: visualization,
            snapshots: snapshots,
        };
    };

    const analyzeImageFile = async (file) => {
        const landmarker = await getLandmarkerImage();
        const img = await createImageBitmap(file);
        const r = landmarker.detect(img);
        const lm = r?.landmarks?.[0];

        const width = img.width;
        const height = img.height;

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

        const getPt = (arr, idx) => arr?.[idx] ? { x: arr[idx].x, y: arr[idx].y, v: arr[idx].visibility ?? 0 } : null;
        const pickSide = (arr) => {
            const lv = arr?.[L.leftAnkle]?.visibility ?? 0;
            const rv = arr?.[L.rightAnkle]?.visibility ?? 0;
            return lv >= rv ? 'left' : 'right';
        };

        if (!lm || !Array.isArray(lm)) {
            const visualization = drawBiomechanicsImage(img, null, width, height);
            try { img.close(); } catch (e) {}
            return { ok: false, confidence: 0, width, height, visualization };
        }

        const side = pickSide(lm);
        const shoulder = getPt(lm, side === 'left' ? L.leftShoulder : L.rightShoulder);
        const hip = getPt(lm, side === 'left' ? L.leftHip : L.rightHip);
        const knee = getPt(lm, side === 'left' ? L.leftKnee : L.rightKnee);
        const ankle = getPt(lm, side === 'left' ? L.leftAnkle : L.rightAnkle);
        const heel = getPt(lm, side === 'left' ? L.leftHeel : L.rightHeel);
        const footIndex = getPt(lm, side === 'left' ? L.leftFootIndex : L.rightFootIndex);
        const lShoulder = getPt(lm, L.leftShoulder);
        const rShoulder = getPt(lm, L.rightShoulder);
        const lWrist = getPt(lm, L.leftWrist);
        const rWrist = getPt(lm, L.rightWrist);

        const confParts = [shoulder, hip, knee, ankle, heel, footIndex].filter(Boolean).map((p) => p.v ?? 0);
        const confidence = confParts.length ? (confParts.reduce((a, b) => a + b, 0) / confParts.length) : 0;

        const kneeFlex = (hip && knee && ankle) ? (180 - (angle(hip, knee, ankle) ?? 180)) : null;
        const shinDx = (knee && ankle) ? Math.abs(knee.x - ankle.x) : null;
        const shinDy = (knee && ankle) ? Math.abs(knee.y - ankle.y) : null;
        const shinAng = (shinDx !== null && shinDy !== null && shinDy > 0) ? toDeg(Math.atan2(shinDx, shinDy)) : null;

        const trunkDx = (shoulder && hip) ? Math.abs(shoulder.x - hip.x) : null;
        const trunkDy = (shoulder && hip) ? Math.abs(shoulder.y - hip.y) : null;
        const trunkAng = (trunkDx !== null && trunkDy !== null && trunkDy > 0) ? toDeg(Math.atan2(trunkDx, trunkDy)) : null;

        let dir = 1;
        if (footIndex && heel) dir = (footIndex.x - heel.x) >= 0 ? 1 : -1;
        const overstride = (hip && ankle) ? (((ankle.x - hip.x) * dir) > 0.035) : null;
        const heelStrike = (heel && footIndex) ? ((heel.y - footIndex.y) > 0.015) : null;

        let armCross = null;
        if (lShoulder && rShoulder && lWrist && rWrist) {
            const mid = (lShoulder.x + rShoulder.x) / 2;
            const leftCross = lWrist.x > mid;
            const rightCross = rWrist.x < mid;
            armCross = (leftCross || rightCross) ? 100 : 0;
        }

        const visualization = drawBiomechanicsImage(img, lm, width, height);
        try { img.close(); } catch (e) {}

        return {
            ok: true,
            confidence: Number.isFinite(confidence) ? Number(confidence.toFixed(3)) : 0,
            width,
            height,
            knee_flex_deg: Number.isFinite(kneeFlex) ? Number(kneeFlex.toFixed(1)) : null,
            shin_angle_deg: Number.isFinite(shinAng) ? Number(shinAng.toFixed(1)) : null,
            trunk_lean_deg: Number.isFinite(trunkAng) ? Number(trunkAng.toFixed(1)) : null,
            overstride: overstride,
            heel_strike: heelStrike,
            arm_cross_pct: Number.isFinite(armCross) ? Number(armCross.toFixed(1)) : null,
            visualization,
        };
    };

    const analyzePhotoSet = async (filesByPhase, onProgress) => {
        const phases = ['landing', 'lever', 'push', 'pull', 'front'];
        const results = {};
        let done = 0;
        const total = phases.filter((p) => !!filesByPhase?.[p]).length;

        for (const p of phases) {
            const f = filesByPhase?.[p];
            if (!f) continue;
            const r = await analyzeImageFile(f);
            results[p] = r;
            done += 1;
            if (onProgress) onProgress({ phase: 'pose', done, total: Math.max(1, total) });
        }

        const landing = results.landing || null;
        const lever = results.lever || null;
        const push = results.push || null;
        const pull = results.pull || null;
        const front = results.front || null;

        const pickNum = (x) => (typeof x === 'number' && Number.isFinite(x)) ? x : null;
        const avg = (arr) => {
            const xs = arr.map(pickNum).filter((v) => v !== null);
            if (!xs.length) return null;
            return xs.reduce((a, b) => a + b, 0) / xs.length;
        };

        const kneeFlex = avg([landing?.knee_flex_deg, lever?.knee_flex_deg, push?.knee_flex_deg, pull?.knee_flex_deg]);
        const shinAng = avg([landing?.shin_angle_deg, lever?.shin_angle_deg]);
        const trunkLean = avg([lever?.trunk_lean_deg, landing?.trunk_lean_deg]);

        const overstridePct = landing?.overstride === null ? null : (landing.overstride ? 100 : 0);
        const heelStrikePct = landing?.heel_strike === null ? null : (landing.heel_strike ? 100 : 0);

        const confAvg = avg(Object.values(results).map((r) => r?.confidence));
        const visualization = landing?.visualization || lever?.visualization || push?.visualization || pull?.visualization || front?.visualization || null;

        return {
            source: 'photos',
            phases: results,
            confidence: pickNum(confAvg) ? Number(confAvg.toFixed(3)) : 0,
            samples: Object.keys(results).length,
            heel_strike_pct: heelStrikePct,
            overstride_pct: overstridePct,
            shin_angle_deg: pickNum(shinAng) ? Number(shinAng.toFixed(1)) : null,
            knee_flex_deg: pickNum(kneeFlex) ? Number(kneeFlex.toFixed(1)) : null,
            trunk_lean_deg: pickNum(trunkLean) ? Number(trunkLean.toFixed(1)) : null,
            arm_cross_pct: pickNum(front?.arm_cross_pct) ? Number(front.arm_cross_pct.toFixed(1)) : null,
            vertical_oscillation: null,
            trunk_std_deg: null,
            visualization: visualization,
        };
    };

    window.RLFormAnalyzerPose = { analyzeVideoFile, analyzePhotoSet };
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
        const inputModeVideoBtn = document.getElementById('rlfa-inputmode-video');
        const inputModePhotosBtn = document.getElementById('rlfa-inputmode-photos');
        const photoSlotsWrap = document.getElementById('rlfa-photo-slots');
        const analyzePhotosBtn = document.getElementById('rlfa-analyze-photos-btn');

        const photoInputs = {
            landing: document.getElementById('rlfa-photo-input-landing'),
            lever: document.getElementById('rlfa-photo-input-lever'),
            push: document.getElementById('rlfa-photo-input-push'),
            pull: document.getElementById('rlfa-photo-input-pull'),
            front: document.getElementById('rlfa-photo-input-front'),
        };
        const photoButtons = {
            landing: document.getElementById('rlfa-photo-btn-landing'),
            lever: document.getElementById('rlfa-photo-btn-lever'),
            push: document.getElementById('rlfa-photo-btn-push'),
            pull: document.getElementById('rlfa-photo-btn-pull'),
            front: document.getElementById('rlfa-photo-btn-front'),
        };
        const photoPreviews = {
            landing: document.getElementById('rlfa-photo-prev-landing'),
            lever: document.getElementById('rlfa-photo-prev-lever'),
            push: document.getElementById('rlfa-photo-prev-push'),
            pull: document.getElementById('rlfa-photo-prev-pull'),
            front: document.getElementById('rlfa-photo-prev-front'),
        };
        const photoIcons = {
            landing: document.getElementById('rlfa-photo-ico-landing'),
            lever: document.getElementById('rlfa-photo-ico-lever'),
            push: document.getElementById('rlfa-photo-ico-push'),
            pull: document.getElementById('rlfa-photo-ico-pull'),
            front: document.getElementById('rlfa-photo-ico-front'),
        };

        const requiredPhotoPhases = ['landing', 'lever', 'push', 'pull'];
        const photoFiles = { landing: null, lever: null, push: null, pull: null, front: null };
        const photoPreviewUrls = { landing: null, lever: null, push: null, pull: null, front: null };
        let inputMode = 'video';

        const scanText = document.getElementById('rlfa-scan-text');
        const scanSubtext = document.getElementById('rlfa-scan-subtext');
        const scanMetric1 = document.getElementById('rlfa-scan-metric-1');
        const scanMetric2 = document.getElementById('rlfa-scan-metric-2');

        const clientWarningsWrap = document.getElementById('rlfa-client-warnings');
        const clientWarningsBox = clientWarningsWrap?.querySelector('div');

        const scoreEl = document.getElementById('rlfa-score');
        const scoreBadge = document.getElementById('rlfa-score-badge');
        const videoScoreEl = document.getElementById('rlfa-video-score');
        const videoScoreBadge = document.getElementById('rlfa-video-score-badge');
        const metaDuration = document.getElementById('rlfa-meta-duration');
        const metaResolution = document.getElementById('rlfa-meta-resolution');
        const metaFps = document.getElementById('rlfa-meta-fps');
        const metaSize = document.getElementById('rlfa-meta-size');
        const coachMessageEl = document.getElementById('rlfa-coach-message');
        const ttsBtn = document.getElementById('rlfa-tts-btn');
        const issuesWrap = document.getElementById('rlfa-issues-wrap');
        const issuesEl = document.getElementById('rlfa-issues');
        const suggestionsWrap = document.getElementById('rlfa-suggestions-wrap');
        const suggestionsEl = document.getElementById('rlfa-suggestions');
        const positivesWrap = document.getElementById('rlfa-positives-wrap');
        const positivesEl = document.getElementById('rlfa-positives');
        const formIssuesWrap = document.getElementById('rlfa-formissues-wrap');
        const formIssuesEl = document.getElementById('rlfa-formissues');
        const formReportWrap = document.getElementById('rlfa-formreport-wrap');
        const formReportEl = document.getElementById('rlfa-formreport');
        const strengthWrap = document.getElementById('rlfa-strength-wrap');
        const strengthEl = document.getElementById('rlfa-strength');
        const recoveryWrap = document.getElementById('rlfa-recovery-wrap');
        const recoveryEl = document.getElementById('rlfa-recovery');
        const visualizationWrap = document.getElementById('rlfa-visualization-wrap');
        const visualizationImg = document.getElementById('rlfa-visualization-img');
        const advancedBtn = document.getElementById('rlfa-advanced-btn');
        const advancedModal = document.getElementById('rlfa-advanced-modal');
        const advancedClose = document.getElementById('rlfa-advanced-close');
        const advancedPreview = document.getElementById('rlfa-advanced-preview');
        const advTrunk = document.getElementById('rlfa-adv-trunk');
        const advKnee = document.getElementById('rlfa-adv-knee');
        const advShin = document.getElementById('rlfa-adv-shin');
        const advArm = document.getElementById('rlfa-adv-arm');
        const advOverstride = document.getElementById('rlfa-adv-overstride');
        const advHeel = document.getElementById('rlfa-adv-heel');
        const advSwing = document.getElementById('rlfa-adv-swing');
        const advHipcore = document.getElementById('rlfa-adv-hipcore');
        const advCalf = document.getElementById('rlfa-adv-calf');
        const advSnapshotsWrap = document.getElementById('rlfa-adv-snapshots-wrap');
        const advSnapshots = document.getElementById('rlfa-adv-snapshots');
        const advStrength = document.getElementById('rlfa-adv-strength');
        const advReport = document.getElementById('rlfa-adv-report');
        const advSuggestions = document.getElementById('rlfa-adv-suggestions');
        let lastResult = null;

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

        const formatDeg = (v) => Number.isFinite(v) ? `${v.toFixed(1)}°` : '--';
        const formatPct = (v) => Number.isFinite(v) ? `${Math.round(v)}%` : '--';
        const formatNum = (v) => Number.isFinite(v) ? String(v) : '--';

        const buildSwingInsight = (metrics) => {
            if (!metrics) return 'Data ayunan tidak tersedia.';
            const armCross = metrics.arm_cross_pct;
            const trunk = metrics.trunk_lean_deg;
            const overstride = metrics.overstride_pct;
            const parts = [];
            if (Number.isFinite(armCross)) {
                if (armCross >= 15) parts.push('Ayunan tangan cukup banyak melintasi garis tengah, berpotensi membuang energi.');
                else if (armCross >= 8) parts.push('Ayunan tangan sedikit melintasi garis tengah, masih perlu dirapikan.');
                else parts.push('Ayunan tangan cukup rapi dan terkontrol.');
            }
            if (Number.isFinite(trunk)) {
                if (trunk < 3) parts.push('Trunk terlalu tegak, coba sedikit condong dari pergelangan kaki.');
                else if (trunk > 12) parts.push('Condong badan cukup besar, jaga agar tetap dari pergelangan kaki.');
                else parts.push('Trunk lean berada di rentang ideal.');
            }
            if (Number.isFinite(overstride)) {
                if (overstride >= 50) parts.push('Overstride sering terjadi, perpendek langkah dan fokus ke cadence.');
                else if (overstride >= 20) parts.push('Ada kecenderungan overstride, kontrol posisi kaki di bawah pinggul.');
                else parts.push('Posisi foot strike relatif dekat pinggul.');
            }
            return parts.length ? parts.join(' ') : 'Data ayunan tidak cukup untuk analisis detail.';
        };

        const buildHipCoreInsight = (metrics) => {
            if (!metrics) return 'Data hip dan core belum cukup.';
            const osc = metrics.vertical_oscillation;
            const trunk = metrics.trunk_lean_deg;
            const trunkStd = metrics.trunk_std_deg;
            const overstride = metrics.overstride_pct;
            const parts = [];
            if (Number.isFinite(osc)) {
                if (osc <= 0.007) parts.push('Stabilitas hip sangat baik, osilasi vertikal rendah.');
                else if (osc <= 0.012) parts.push('Stabilitas hip cukup baik, masih bisa lebih stabil.');
                else parts.push('Osilasi vertikal tinggi, indikasi kontrol hip & core belum stabil.');
            }
            if (Number.isFinite(trunkStd)) {
                if (trunkStd <= 3) parts.push('Variasi trunk kecil, core stabil sepanjang siklus.');
                else if (trunkStd <= 6) parts.push('Variasi trunk sedang, core cukup stabil.');
                else parts.push('Variasi trunk tinggi, core cenderung collapse saat fatigue.');
            }
            if (Number.isFinite(trunk)) {
                if (trunk < 3) parts.push('Core terlihat pasif karena trunk terlalu tegak.');
                else if (trunk > 12) parts.push('Trunk condong cukup besar, jaga stabilitas core agar tidak collapse.');
                else parts.push('Trunk lean berada di rentang efisien.');
            }
            if (Number.isFinite(overstride)) {
                if (overstride >= 40) parts.push('Overstride tinggi, sering dipicu hip control yang terlambat.');
                else if (overstride >= 20) parts.push('Ada kecenderungan overstride, kontrol pelvis & cadence.');
                else parts.push('Foot strike relatif di bawah hip.');
            }
            return parts.length ? parts.join(' ') : 'Data hip/core tidak cukup untuk analisis detail.';
        };

        const buildCalfInsight = (metrics) => {
            if (!metrics) return 'Data calf dan ankle belum cukup.';
            const heel = metrics.heel_strike_pct;
            const shin = metrics.shin_angle_deg;
            const knee = metrics.knee_flex_deg;
            const osc = metrics.vertical_oscillation;
            const parts = [];
            if (Number.isFinite(heel)) {
                if (heel >= 60) parts.push('Heel strike dominan, beban calf & tibialis meningkat.');
                else if (heel >= 35) parts.push('Heel strike sedang, kontrol dorsiflexion lebih baik.');
                else parts.push('Foot strike cenderung mid/forefoot, calf aktif.');
            }
            if (Number.isFinite(shin)) {
                if (shin < 80) parts.push('Shin angle kecil, potensi braking meningkat.');
                else if (shin > 100) parts.push('Shin angle terlalu maju, beban ankle tinggi.');
                else parts.push('Shin angle stabil di rentang efisien.');
            }
            if (Number.isFinite(knee)) {
                if (knee < 25) parts.push('Landing kaku, absorpsi shock kurang sehingga calf cepat lelah.');
                else if (knee > 60) parts.push('Knee flex besar, efektif menyerap tetapi butuh kontrol ankle.');
                else parts.push('Knee flex berada di rentang aman.');
            }
            if (Number.isFinite(osc)) {
                if (osc >= 0.014) parts.push('Osilasi vertikal tinggi, beban calf meningkat pada tiap langkah.');
            }
            return parts.length ? parts.join(' ') : 'Data calf/ankle tidak cukup untuk analisis detail.';
        };

        const buildIssueFlags = (metrics) => {
            if (!metrics) return {};
            return {
                overstride: Number.isFinite(metrics.overstride_pct) && metrics.overstride_pct >= 20,
                heel: Number.isFinite(metrics.heel_strike_pct) && metrics.heel_strike_pct >= 50,
                trunk: Number.isFinite(metrics.trunk_lean_deg) && (metrics.trunk_lean_deg < 3 || metrics.trunk_lean_deg > 12),
                knee: Number.isFinite(metrics.knee_flex_deg) && (metrics.knee_flex_deg < 25 || metrics.knee_flex_deg > 60),
                shin: Number.isFinite(metrics.shin_angle_deg) && (metrics.shin_angle_deg < 80 || metrics.shin_angle_deg > 100),
                arm: Number.isFinite(metrics.arm_cross_pct) && metrics.arm_cross_pct >= 10,
            };
        };

        const buildSnapshotsFromMetrics = (metrics) => {
            if (!metrics) return [];
            if (metrics.source === 'photos' && metrics.phases) {
                return Object.entries(metrics.phases)
                    .filter(([, v]) => v?.visualization)
                    .map(([phase, v]) => ({ phase, label: String(phase).toUpperCase(), image: v.visualization }));
            }
            if (Array.isArray(metrics.snapshots)) {
                return metrics.snapshots.filter((s) => s && s.image).map((s) => ({
                    ...s,
                    label: s.label ? String(s.label).toUpperCase() : String(s.phase || 'SNAPSHOT').toUpperCase()
                }));
            }
            return [];
        };

        const createSnapshotCard = (snap) => {
            const wrap = document.createElement('div');
            wrap.className = 'bg-slate-900/60 border border-slate-800 rounded-xl overflow-hidden';
            const img = document.createElement('img');
            img.src = snap.image;
            img.alt = snap.label || 'Snapshot';
            img.className = 'w-full h-40 object-cover';
            const label = document.createElement('div');
            label.className = 'px-3 py-2 text-xs font-bold text-slate-300 uppercase tracking-widest';
            label.textContent = snap.label || snap.phase || 'Snapshot';
            wrap.appendChild(img);
            wrap.appendChild(label);
            return wrap;
        };

        const buildAdvancedReportSections = (metrics) => {
            if (!metrics) return [];
            const trunkStd = metrics.trunk_std_deg;
            const osc = metrics.vertical_oscillation;
            const over = metrics.overstride_pct;
            const heel = metrics.heel_strike_pct;
            const shin = metrics.shin_angle_deg;
            const knee = metrics.knee_flex_deg;
            const trunk = metrics.trunk_lean_deg;

            const sections = [];
            const hipStatus = (Number.isFinite(osc) && osc >= 0.013) || (Number.isFinite(trunkStd) && trunkStd >= 6) ? 'issue'
                : (Number.isFinite(osc) && osc >= 0.009) ? 'warn' : 'ok';
            sections.push({
                title: 'Hip & Core Stability',
                status: hipStatus,
                summary: 'Stabilitas pelvis dan kontrol core selama siklus langkah.',
                findings: [
                    Number.isFinite(osc) ? `Osilasi vertikal: ${osc.toFixed(3)}` : null,
                    Number.isFinite(trunkStd) ? `Variasi trunk: ${trunkStd.toFixed(1)}°` : null,
                    Number.isFinite(trunk) ? `Trunk lean rata-rata: ${trunk.toFixed(1)}°` : null
                ].filter(Boolean),
                actions: [
                    hipStatus === 'issue' ? 'Kurangi bounding, fokus cadence stabil 170–185.' : null,
                    hipStatus !== 'ok' ? 'Latih kontrol pelvis dengan single-leg balance.' : null,
                    'Jaga core aktif saat kontak tanah.'
                ].filter(Boolean),
                strength: [
                    'Dead bug, side plank, hip bridge.',
                    'Single-leg RDL ringan.'
                ]
            });

            const calfStatus = (Number.isFinite(heel) && heel >= 60) || (Number.isFinite(shin) && (shin < 80 || shin > 100)) ? 'issue'
                : (Number.isFinite(heel) && heel >= 35) ? 'warn' : 'ok';
            sections.push({
                title: 'Calf & Ankle Load',
                status: calfStatus,
                summary: 'Distribusi beban pada ankle & calf saat kontak.',
                findings: [
                    Number.isFinite(heel) ? `Heel strike: ${Math.round(heel)}%` : null,
                    Number.isFinite(shin) ? `Shin angle: ${shin.toFixed(1)}°` : null,
                    Number.isFinite(knee) ? `Knee flex: ${knee.toFixed(1)}°` : null
                ].filter(Boolean),
                actions: [
                    calfStatus === 'issue' ? 'Turunkan braking dengan landing lebih dekat pinggul.' : null,
                    'Fokus ankle stiffness saat toe-off.'
                ].filter(Boolean),
                strength: [
                    'Calf raise eksentrik.',
                    'Ankle mobility + tibialis raise.'
                ]
            });

            const strideStatus = (Number.isFinite(over) && over >= 40) ? 'issue'
                : (Number.isFinite(over) && over >= 20) ? 'warn' : 'ok';
            sections.push({
                title: 'Stride Efficiency',
                status: strideStatus,
                summary: 'Keseimbangan panjang langkah dan braking.',
                findings: [
                    Number.isFinite(over) ? `Overstride: ${Math.round(over)}%` : null
                ].filter(Boolean),
                actions: [
                    strideStatus !== 'ok' ? 'Perpendek langkah, fokus landing di bawah hip.' : null,
                    'Pertahankan cadence stabil.'
                ].filter(Boolean),
                strength: [
                    'A-skip ringan, marching drill.',
                    'Wall drive drill.'
                ]
            });

            return sections;
        };

        let preferredVoice = null;
        const pickPreferredVoice = () => {
            const synth = window.speechSynthesis;
            if (!synth || typeof synth.getVoices !== 'function') return null;
            const voices = synth.getVoices() || [];
            if (!voices.length) return null;
            const idVoices = voices.filter((v) => (v.lang || '').toLowerCase().startsWith('id'));
            const from = (arr) => {
                if (!arr || !arr.length) return null;
                return arr.find((v) => /gadis/i.test(v.name))
                    || arr.find((v) => /damayanti/i.test(v.name))
                    || arr.find((v) => /google bahasa indonesia/i.test(v.name))
                    || arr.find((v) => /google/i.test(v.name) && /indonesian/i.test(v.name))
                    || arr.find((v) => /indonesian/i.test(v.name))
                    || arr.find((v) => /google/i.test(v.name))
                    || arr[0];
            };
            return from(idVoices) || from(voices);
        };
        const refreshPreferredVoice = () => {
            preferredVoice = pickPreferredVoice();
        };
        refreshPreferredVoice();
        if (window.speechSynthesis) {
            window.speechSynthesis.onvoiceschanged = refreshPreferredVoice;
        }

        const openAdvanced = () => {
            if (!advancedModal) return;
            advancedModal.classList.remove('hidden');
            advancedModal.classList.add('flex');
        };

        const closeAdvanced = () => {
            if (!advancedModal) return;
            advancedModal.classList.add('hidden');
            advancedModal.classList.remove('flex');
        };

        if (advancedBtn) advancedBtn.addEventListener('click', openAdvanced);
        if (advancedClose) advancedClose.addEventListener('click', closeAdvanced);
        if (advancedModal) {
            advancedModal.addEventListener('click', (e) => {
                if (e.target === advancedModal) closeAdvanced();
            });
        }

        if (ttsBtn) {
            ttsBtn.addEventListener('click', () => {
                const text = coachMessageEl.textContent;
                if (!text || !window.speechSynthesis) return;

                if (window.speechSynthesis.speaking) {
                    window.speechSynthesis.cancel();
                    ttsBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i> BACA';
                    return;
                }

                refreshPreferredVoice();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';
                if (preferredVoice) {
                    utterance.voice = preferredVoice;
                }
                utterance.pitch = 1.1;
                utterance.rate = 0.98;

                utterance.onstart = () => {
                    ttsBtn.innerHTML = '<i class="fa-solid fa-stop"></i> STOP';
                };

                utterance.onend = () => {
                    ttsBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i> BACA';
                };

                utterance.onerror = () => {
                     ttsBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i> BACA';
                };

                window.speechSynthesis.speak(utterance);
            });
        }

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
            stateResults.scrollTop = 0;
            document.getElementById('rlfa-app').scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => {
                if (visualizationWrap && visualizationImg && !visualizationWrap.classList.contains('hidden') && visualizationImg.src) {
                    visualizationImg.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    stateResults.scrollTo({ top: 0, behavior: 'smooth' });
                }
            }, 150);
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

        const setInputMode = (mode) => {
            inputMode = mode === 'photos' ? 'photos' : 'video';
            if (inputModeVideoBtn && inputModePhotosBtn) {
                if (inputMode === 'video') {
                    inputModeVideoBtn.className = 'px-3 py-2 rounded-xl border border-neon/40 bg-neon/10 text-neon font-bold text-xs';
                    inputModePhotosBtn.className = 'px-3 py-2 rounded-xl border border-slate-700 bg-slate-900/40 text-slate-300 font-bold text-xs hover:text-white';
                } else {
                    inputModePhotosBtn.className = 'px-3 py-2 rounded-xl border border-neon/40 bg-neon/10 text-neon font-bold text-xs';
                    inputModeVideoBtn.className = 'px-3 py-2 rounded-xl border border-slate-700 bg-slate-900/40 text-slate-300 font-bold text-xs hover:text-white';
                }
            }
            if (photoSlotsWrap) {
                photoSlotsWrap.classList.toggle('hidden', inputMode !== 'photos');
            }
            if (uploadBtn) {
                if (inputMode === 'video') {
                    uploadBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload Video & Analisis';
                } else {
                    uploadBtn.innerHTML = '<i class="fa-regular fa-images"></i> Pilih Foto';
                }
            }
            syncAnalyzePhotosBtn();
        };

        const clearPhotoPreview = (phase) => {
            const img = photoPreviews[phase];
            const ico = photoIcons[phase];
            if (photoPreviewUrls[phase]) {
                try { URL.revokeObjectURL(photoPreviewUrls[phase]); } catch (e) {}
                photoPreviewUrls[phase] = null;
            }
            if (img) {
                img.src = '';
                img.classList.add('hidden');
            }
            if (ico) {
                ico.classList.remove('hidden');
            }
        };

        const setPhotoPreview = (phase, file) => {
            clearPhotoPreview(phase);
            const img = photoPreviews[phase];
            const ico = photoIcons[phase];
            if (!file || !img) return;
            const url = URL.createObjectURL(file);
            photoPreviewUrls[phase] = url;
            img.src = url;
            img.classList.remove('hidden');
            if (ico) ico.classList.add('hidden');
        };

        const syncAnalyzePhotosBtn = () => {
            if (!analyzePhotosBtn) return;
            if (inputMode !== 'photos') {
                analyzePhotosBtn.disabled = true;
                return;
            }
            const ok = requiredPhotoPhases.every((p) => !!photoFiles[p]);
            analyzePhotosBtn.disabled = !ok;
        };

        const resetPhotoState = () => {
            Object.keys(photoFiles).forEach((p) => {
                photoFiles[p] = null;
                clearPhotoPreview(p);
                if (photoInputs[p]) photoInputs[p].value = '';
            });
            syncAnalyzePhotosBtn();
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

        const createReportCard = (section) => {
            const wrap = document.createElement('div');
            wrap.className = 'bg-slate-900/60 border border-slate-800 rounded-xl p-4';

            const status = (section?.status || 'ok').toLowerCase();
            const badge = document.createElement('span');
            badge.className = 'text-[10px] font-bold px-2 py-0.5 rounded border';
            if (status === 'issue') badge.className += ' bg-red-500/15 text-red-300 border-red-500/30';
            else if (status === 'warn') badge.className += ' bg-yellow-500/15 text-yellow-300 border-yellow-500/30';
            else if (status === 'missing') badge.className += ' bg-slate-500/15 text-slate-300 border-slate-500/30';
            else badge.className += ' bg-emerald-500/15 text-emerald-300 border-emerald-500/30';
            badge.textContent = status.toUpperCase();

            const head = document.createElement('div');
            head.className = 'flex items-start justify-between gap-3';
            head.innerHTML = `<div><div class="text-white font-black text-sm">${section?.title || 'Bagian Gerak'}</div>${section?.summary ? `<div class="text-xs text-slate-300 mt-1">${section.summary}</div>` : ''}</div>`;
            head.appendChild(badge);
            wrap.appendChild(head);

            const addList = (label, arr) => {
                const xs = Array.isArray(arr) ? arr.filter((x) => typeof x === 'string' && x.trim() !== '') : [];
                if (!xs.length) return;
                const box = document.createElement('div');
                box.className = 'mt-3';
                box.innerHTML = `<div class="text-[10px] uppercase tracking-widest text-slate-500 font-bold mb-1">${label}</div>`;
                const ul = document.createElement('ul');
                ul.className = 'text-xs text-slate-300 leading-relaxed space-y-1 list-disc pl-4';
                xs.forEach((x) => {
                    const li = document.createElement('li');
                    li.textContent = x;
                    ul.appendChild(li);
                });
                box.appendChild(ul);
                wrap.appendChild(box);
            };

            addList('Temuan', section?.findings);
            addList('Aksi Cepat', section?.actions);
            addList('Penguatan', section?.strength);

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
                if (isPortrait) pushWarning('Video portrait diperbolehkan. Pastikan seluruh tubuh masuk frame dan lutut/ankle terlihat jelas.');
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

            const formScore = Number(result.form_score ?? result.score) || 0;
            const videoScore = Number(result.video_score) || 0;

            if (videoScoreEl && videoScoreBadge) {
                videoScoreEl.textContent = videoScore ? String(videoScore) : '--';
                const vBadge = scoreToBadge(videoScore);
                videoScoreBadge.className = 'px-2 py-1 rounded text-[10px] font-bold border ' + vBadge.className;
                videoScoreBadge.textContent = vBadge.text;
            }

            scoreEl.textContent = String(formScore);

            const badge = scoreToBadge(formScore);
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
            if (formReportEl) formReportEl.innerHTML = '';
            strengthEl.innerHTML = '';
            recoveryEl.innerHTML = '';
            issuesWrap.classList.add('hidden');
            suggestionsWrap.classList.add('hidden');
            positivesWrap.classList.add('hidden');
            formIssuesWrap.classList.add('hidden');
            if (formReportWrap) formReportWrap.classList.add('hidden');
            strengthWrap.classList.add('hidden');
            recoveryWrap.classList.add('hidden');

            if (advStrength) advStrength.innerHTML = '';
            if (advReport) advReport.innerHTML = '';
            if (advSuggestions) advSuggestions.innerHTML = '';
            if (advSwing) advSwing.textContent = '';
            if (advHipcore) advHipcore.textContent = '';
            if (advCalf) advCalf.textContent = '';
            if (advSnapshots) advSnapshots.innerHTML = '';
            if (advSnapshotsWrap) advSnapshotsWrap.classList.add('hidden');
            if (advancedPreview) advancedPreview.src = '';
            if (advTrunk) advTrunk.textContent = '--';
            if (advKnee) advKnee.textContent = '--';
            if (advShin) advShin.textContent = '--';
            if (advArm) advArm.textContent = '--';
            if (advOverstride) advOverstride.textContent = '--';
            if (advHeel) advHeel.textContent = '--';

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

            const formReport = Array.isArray(result.form_report) ? result.form_report : [];
            if (formReportEl && formReportWrap && formReport.length) {
                formReport.forEach((s) => formReportEl.appendChild(createReportCard(s)));
                formReportWrap.classList.remove('hidden');
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

            lastResult = result;
            const metrics = result?.client_metrics || null;
            const snapshots = buildSnapshotsFromMetrics(metrics);
            if (advancedPreview) {
                if (result.visualization) {
                    advancedPreview.src = result.visualization;
                } else if (snapshots.length) {
                    advancedPreview.src = snapshots[0].image || '';
                } else {
                    advancedPreview.removeAttribute('src');
                }
            }
            if (advTrunk) advTrunk.textContent = formatDeg(metrics?.trunk_lean_deg);
            if (advKnee) advKnee.textContent = formatDeg(metrics?.knee_flex_deg);
            if (advShin) advShin.textContent = formatDeg(metrics?.shin_angle_deg);
            if (advArm) advArm.textContent = formatPct(metrics?.arm_cross_pct);
            if (advOverstride) advOverstride.textContent = formatPct(metrics?.overstride_pct);
            if (advHeel) advHeel.textContent = formatPct(metrics?.heel_strike_pct);
            if (advSwing) advSwing.textContent = buildSwingInsight(metrics);
            if (advHipcore) advHipcore.textContent = buildHipCoreInsight(metrics);
            if (advCalf) advCalf.textContent = buildCalfInsight(metrics);

            const issueFlags = buildIssueFlags(metrics);
            const issuePhases = new Set();
            if (issueFlags.overstride || issueFlags.heel || issueFlags.shin) issuePhases.add('landing');
            if (issueFlags.knee || issueFlags.trunk) issuePhases.add('lever');
            if (issueFlags.overstride || issueFlags.heel) issuePhases.add('push');
            if (issueFlags.arm) issuePhases.add('front');
            const issueSnapshots = issuePhases.size
                ? snapshots.filter((s) => issuePhases.has(s.phase))
                : [];
            let finalSnapshots = issueSnapshots;
            if (finalSnapshots.length < 3 && snapshots.length > finalSnapshots.length) {
                const extra = snapshots.filter((s) => !finalSnapshots.includes(s));
                finalSnapshots = finalSnapshots.concat(extra.slice(0, Math.max(0, 3 - finalSnapshots.length)));
            }
            if (advSnapshots && advSnapshotsWrap && finalSnapshots.length) {
                finalSnapshots.forEach((s) => advSnapshots.appendChild(createSnapshotCard(s)));
                advSnapshotsWrap.classList.remove('hidden');
            }

            const advStrengthList = Array.isArray(result.strength_plan) ? result.strength_plan : [];
            if (advStrength) advStrengthList.forEach((x) => advStrength.appendChild(createChip(x)));
            const advSections = buildAdvancedReportSections(metrics);
            if (advReport) advSections.forEach((x) => advReport.appendChild(createReportCard(x)));
            const advReportList = Array.isArray(result.form_report) ? result.form_report : [];
            if (advReport) advReportList.forEach((x) => advReport.appendChild(createReportCard(x)));
            const advSuggestionsList = Array.isArray(result.suggestions) ? result.suggestions : [];
            if (advSuggestions) advSuggestionsList.forEach((x) => advSuggestions.appendChild(createChip(x)));
        };

        const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

        const METRICS_MAX_CHARS = 20000;

        const stripLargeMetricFields = (value) => {
            if (!value || typeof value !== 'object') return value;
            if (Array.isArray(value)) return value.map(stripLargeMetricFields);
            const out = {};
            Object.keys(value).forEach((k) => {
                if (k === 'visualization' || k === 'snapshots') return;
                out[k] = stripLargeMetricFields(value[k]);
            });
            return out;
        };

        const buildMetricsPayload = (metrics) => {
            if (!metrics || typeof metrics !== 'object') return null;
            const stripped = stripLargeMetricFields(metrics);
            let json = '';
            try {
                json = JSON.stringify(stripped);
            } catch (e) {
                return null;
            }
            const len = json.length;
            if (len > METRICS_MAX_CHARS) {
                return { ok: false, length: len };
            }
            return { ok: true, json, length: len };
        };

        const analyze = async (file, clientMeta, metrics, options = {}) => {
            showScanning();
            scanText.textContent = 'MENYIAPKAN...';
            scanSubtext.textContent = 'Jangan tutup halaman ini.';
            scanMetric1.textContent = '--';
            scanMetric2.textContent = '--';

            // Dynamic loading messages
            let loadingInterval = setInterval(() => {
                const current = scanText.textContent;
                if (current === 'MEMPROSES...' || current === 'MENGIRIM METRICS...') {
                    const msgs = [
                        'Menganalisis data biomekanik...',
                        'Mengidentifikasi risiko cedera...',
                        'Menghitung efisiensi lari...',
                        'Menyusun rekomendasi latihan...',
                        'Finalisasi laporan AI...'
                    ];
                    let idx = parseInt(scanText.dataset.msgIdx || '0');
                    scanSubtext.textContent = msgs[idx % msgs.length];
                    scanText.dataset.msgIdx = String(idx + 1);
                }
            }, 2000);

            const uploadVideo = (options.uploadVideoOverride !== undefined)
                ? !!options.uploadVideoOverride
                : (modeDevice ? !modeDevice.checked : true);

            if (uploadVideo && !file) {
                throw { error: 'Video wajib diupload.' };
            }

            const metricsPayload = buildMetricsPayload(metrics);
            if (metrics && metricsPayload && metricsPayload.ok === false) {
                throw { error: `Data analisis (metrics) terlalu besar untuk dikirim (maksimal ${METRICS_MAX_CHARS} karakter). Coba ulang tanpa visualisasi atau gunakan resolusi lebih kecil.` };
            }

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
                    if (metricsPayload && metricsPayload.ok && metricsPayload.json) fd.append('metrics', metricsPayload.json);
                    fd.append('client_duration', String(clientMeta?.duration || ''));
                    fd.append('client_width', String(clientMeta?.width || ''));
                    fd.append('client_height', String(clientMeta?.height || ''));
                    xhr.send(fd);

                    if (!uploadVideo) {
                        scanText.textContent = 'MENGIRIM METRICS...';
                        scanMetric1.textContent = 'LOCAL';
                        scanMetric2.textContent = 'AI';
                    }
                });

                if (result.status >= 200 && result.status < 300) {
                    clearInterval(loadingInterval);
                    return result.json;
                }
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

            clearInterval(loadingInterval);
            throw { error: 'Antrian terlalu panjang. Coba lagi beberapa menit.' };
        };

        const startVideo = async () => {
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
                        const formatCoverage = (cov) => {
                            if (!cov || typeof cov !== 'object') return '';
                            const order = [
                                ['landing', 'Landing'],
                                ['lever', 'Lever'],
                                ['push', 'Push'],
                                ['pull', 'Pull'],
                                ['arm_swing', 'Ayunan'],
                                ['posture', 'Postur'],
                            ];
                            return order.map(([k, label]) => {
                                const x = cov[k];
                                if (!x) return null;
                                const ok = x.count >= x.min;
                                return `${label} ${x.count}/${x.min}${ok ? '✓' : ''}`;
                            }).filter(Boolean).join(' • ');
                        };
                        metrics = await window.RLFormAnalyzerPose.analyzeVideoFile(file, ({ done, total, coverage, note }) => {
                            const pct = total ? Math.round((done / total) * 100) : 0;
                            scanMetric1.textContent = pct + '%';
                            if (coverage) {
                                const msg = formatCoverage(coverage);
                                if (msg) scanSubtext.textContent = msg;
                            }
                            if (note) scanSubtext.textContent = note;
                        });
                    } catch (e) {
                        metrics = null;
                        scanText.textContent = 'ANALISIS POSE GAGAL';
                        scanSubtext.textContent = 'Tetap memberi feedback kualitas video.';
                        await sleep(800);
                    }
                }

                const result = await analyze(file, v.meta, metrics);
                if (metrics) result.client_metrics = metrics;
                if (metrics && metrics.visualization) result.visualization = metrics.visualization;
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

        const getPhotoMeta = async () => {
            const firstPhase = requiredPhotoPhases.find((p) => !!photoFiles[p]) || 'front';
            const file = photoFiles[firstPhase] || null;
            if (!file) return { duration: 0, width: 0, height: 0 };
            const img = await createImageBitmap(file);
            const meta = { duration: 0, width: img.width || 0, height: img.height || 0 };
            try { img.close(); } catch (e) {}
            return meta;
        };

        const startPhotos = async () => {
            resetWarnings();
            const ok = requiredPhotoPhases.every((p) => !!photoFiles[p]);
            if (!ok) {
                pushWarning('Minimal 4 foto wajib diisi: Landing, Lever, Push, Pull.');
                return;
            }

            try {
                showScanning();
                scanText.textContent = 'ANALISIS FOTO (DI PERANGKAT)...';
                scanSubtext.textContent = 'Memindai pose per fase.';
                scanMetric1.textContent = '0%';
                scanMetric2.textContent = 'POSE';

                let metrics = null;
                if (window.RLFormAnalyzerPose?.analyzePhotoSet) {
                    metrics = await window.RLFormAnalyzerPose.analyzePhotoSet(photoFiles, ({ done, total }) => {
                        const pct = total ? Math.round((done / total) * 100) : 0;
                        scanMetric1.textContent = pct + '%';
                    });
                }

                const meta = await getPhotoMeta();
                const result = await analyze(null, meta, metrics, { uploadVideoOverride: false });
                if (metrics) result.client_metrics = metrics;
                if (metrics && metrics.visualization) result.visualization = metrics.visualization;
                renderResults(result);
                showResults();
            } catch (e) {
                const msg = e?.message || e?.error || 'Foto gagal diproses. Pastikan badan utuh terlihat dan pencahayaan cukup.';
                showInstructions();
                pushWarning(msg);
            }
        };

        uploadBtn?.addEventListener('click', () => {
            resetWarnings();
            if (inputMode === 'video') {
                videoInput?.click();
                return;
            }
            const next = requiredPhotoPhases.find((p) => !photoFiles[p]) || 'front';
            photoInputs[next]?.click();
        });
        videoInput?.addEventListener('change', startVideo);
        analyzePhotosBtn?.addEventListener('click', startPhotos);
        retryBtn?.addEventListener('click', () => {
            showInstructions();
            resetWarnings();
            resetPhotoState();
        });
        backBtn?.addEventListener('click', () => {
            showInstructions();
            resetWarnings();
            resetPhotoState();
        });

        inputModeVideoBtn?.addEventListener('click', () => setInputMode('video'));
        inputModePhotosBtn?.addEventListener('click', () => setInputMode('photos'));

        Object.keys(photoButtons).forEach((phase) => {
            photoButtons[phase]?.addEventListener('click', () => {
                resetWarnings();
                photoInputs[phase]?.click();
            });
            photoInputs[phase]?.addEventListener('change', () => {
                const f = photoInputs[phase]?.files?.[0] || null;
                if (!f) return;
                photoFiles[phase] = f;
                setPhotoPreview(phase, f);
                syncAnalyzePhotosBtn();
            });
        });

        setInputMode('video');
    })();
</script>
@endpush
