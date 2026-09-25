@php
    $destImg = public_path('images/hero/vdot-runner-training.jpg');
    $srcImg = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/ad786e94-2a56-4d78-962d-8aea9a7effaf/vdot_runner_training_1788923975440.jpg';
    if (!file_exists($destImg) && file_exists($srcImg)) {
        @copy($srcImg, $destImg);
    }
@endphp
@extends('layouts.pacerhub')

@section('title', 'Buat Program Lari Terstruktur: 5K, 10K, Half Marathon & Marathon Gratis | RuangLari')

@section('meta_title', 'Buat Program Lari Terstruktur: 5K, 10K, Half Marathon & Marathon Gratis | RuangLari')
@section('meta_description', 'Buat program lari terstruktur dan gratis berbasis formula VDOT Jack Daniels untuk 5K, 10K, Half Marathon & Marathon. Lengkap dengan target pace dan jadwal latihan harian.')
@section('meta_keywords', 'buat program lari, program lari, generator program lari, program lari gratis, kalkulator vdot, program lari 5k, program lari 10k, program latihan half marathon, program marathon, jadwal latihan lari, jack daniels running formula, pacerhub, ruang lari')
@section('canonical_url', url('/buat-program-lari'))
@section('meta_robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')

@section('og_type', 'website')
@section('og_image', 'https://ruanglari.com/storage/blog/media/kP2oNYsx0wEzCGJMQYKN1xxUBW3oaUMTCfydDSig.webp')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800;900&family=Sora:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebApplication",
      "@id": "https://ruanglari.com/buat-program-lari#webapp",
      "name": "Buat Program Lari Gratis: Generator VDOT RuangLari",
      "alternateName": ["Generator Program Lari", "Kalkulator VDOT & Pembuat Jadwal Latihan Lari"],
      "url": "https://ruanglari.com/buat-program-lari",
      "description": "Aplikasi web gratis untuk buat program lari terstruktur berbasis formula VDOT Jack Daniels untuk 5K, 10K, Half Marathon, dan Full Marathon lengkap dengan zona pace dan kalender latihan.",
      "applicationCategory": "HealthApplication",
      "operatingSystem": "All",
      "browserRequirements": "Requires JavaScript. Requires HTML5.",
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "IDR"
      },
      "featureList": [
        "Kalkulator VDOT Jack Daniels Formula",
        "Buat Program Lari 5K, 10K, Half Marathon, dan Full Marathon",
        "Penetapan Target Pace (Easy, Marathon, Threshold, Interval, Repetition)",
        "Metode Run-Walk Khusus Pelari Pemula",
        "Sinkronisasi Kalender Latihan Lari Pribadi",
        "Kalkulasi Indeks Massa Tubuh (BMI) & Rekomendasi Nutrisi Atlet"
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://ruanglari.com/buat-program-lari#breadcrumbs",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Beranda",
          "item": "https://ruanglari.com"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Running Tools",
          "item": "https://ruanglari.com/tools"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Buat Program Lari",
          "item": "https://ruanglari.com/buat-program-lari"
        }
      ]
    },
    {
      "@type": "FAQPage",
      "@id": "https://ruanglari.com/buat-program-lari#faq",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "Bagaimana cara kerja kalkulator VDOT dalam membuat program latihan lari?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Kalkulator VDOT mengukur tingkat kebugaran aerobik (VO2 Max fungsional) berdasarkan catatan waktu terbaik (Personal Best) terkini pada jarak standar (5K, 10K, Half Marathon, Full Marathon, atau tes waktu Cooper/Balke). Dari nilai VDOT ini, sistem menghitung 5 intensitas pace spesifik (Easy, Marathon, Threshold, Interval, Repetition) sesuai metodologi ilmiah Dr. Jack Daniels untuk menstimulasi adaptasi kardiovaskular secara optimal tanpa risiko overtraining."
          }
        },
        {
          "@type": "Question",
          "name": "Berapa lama durasi persiapan ideal untuk 5K, 10K, Half Marathon, dan Full Marathon?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Durasi persiapan terstruktur yang direkomendasikan adalah 8–10 pekan untuk 5K, 10–12 pekan untuk 10K, 12–14 pekan untuk Half Marathon (21.1K), dan 16–20 pekan untuk Full Marathon (42.2K). Rentang waktu ini memastikan adaptasi tendon, ligamen, dan kapasitas simpanan glikogen otot terbangun secara bertahap dengan fase de-load dan tapering menjelang hari perlombaan."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah program latihan ini aman untuk pelari pemula yang baru mulai lari?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sangat aman. Untuk pelari pemula dengan tingkat kebugaran awal (VDOT di bawah 32), sistem secara otomatis mengaktifkan Metode Run-Walk (Lari-Jalan Berirama). Pelari tidak dipaksa berlari lambat terus-menerus pada pace 11–13 min/km yang berisiko merusak postur, melainkan berlari pada ritme alami (08:00 - 08:30 /km) diselingi interval jalan cepat aktif untuk menjaga detak jantung aerobik Zona 2 dan melindungi sendi lutut serta tulang kering."
          }
        },
        {
          "@type": "Question",
          "name": "Mengapa ada opsi penyesuaian iklim tropis Indonesia?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Berlari di lingkungan tropis dengan suhu tinggi dan kelembapan di atas 75% memicu kenaikan detak jantung lebih cepat (cardiac drift) akibat beban termoregulasi tubuh. Fitur adaptasi tropis melonggarkan target pace sebesar 10–15 detik/km agar beban fisiologis pada sistem kardiovaskular tetap sesuai dengan tujuan latihan tanpa memicu kelelahan ekstrem."
          }
        },
        {
          "@type": "Question",
          "name": "Bagaimana cara menyimpan dan menyinkronkan program ke kalender lari?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Setelah menekan tombol 'Buat Program Latihan' dan meninjau hasil kalkulasi, klik tombol 'Simpan ke Kalender Lari'. Jika Anda telah masuk (login), seluruh jadwal latihan harian—termasuk jarak, target pace, dan jenis sesi—akan tersinkronisasi otomatis ke dashboard Kalender Lari Anda."
          }
        },
        {
          "@type": "Question",
          "name": "Apakah generator program lari ini 100% gratis digunakan?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Ya, Anda dapat buat program lari secara 100% gratis tanpa biaya langganan. Fitur ini mencakup penentuan target pace VDOT, periodisasi jadwal latihan harian (5K, 10K, Half Marathon, Full Marathon), panduan nutrisi protein, hingga sinkronisasi langsung ke kalender lari pribadi Anda."
          }
        }
      ]
    }
  ]
}
</script>
@endverbatim
@endpush

@push('styles')
<style>
    /* Heading Typography Pakem */
    .generator-v2-wrapper h1,
    .generator-v2-wrapper h2,
    .generator-v2-wrapper h3,
    .generator-v2-wrapper h4,
    .heading-pakem {
        font-family: 'Inter Tight', 'Sora', sans-serif !important;
        font-weight: 800 !important;
        letter-spacing: -0.03em !important;
    }

    .generator-v2-wrapper {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        background-color: #090D15;
        color: #f1f5f9;
        min-height: 100vh;
        position: relative;
    }

    
    /* Active States in Parameter Form: Font MUST be dark (#090D15) on Lime */
    .form-tab-active {
        background-color: #CCFF00 !important;
        border-color: #CCFF00 !important;
        color: #090D15 !important;
    }
    .form-tab-active,
    .form-tab-active * {
        color: #090D15 !important;
    }
    .form-tab-active .badge-active-tag {
        background-color: #090D15 !important;
        color: #CCFF00 !important;
    }

    /* Dark Mode Surface System */
    .surface-canvas { background-color: #090D15; }
    .surface-card { background-color: #111726; border: 1px solid #1E293B; }
    .surface-nested { background-color: #162035; border: 1px solid #1E293B; }
    .surface-input { background-color: #141D30; border: 1px solid #1E293B; }

    /* Single Primary Action Button (Lime Solid) */
    .btn-lime-primary {
        background-color: #CCFF00 !important;
        color: #090D15 !important;
        font-weight: 800 !important;
        border-radius: 0.375rem !important; /* rounded-md */
        border: 1px solid #CCFF00 !important;
        box-shadow: 0 4px 14px rgba(204, 255, 0, 0.22) !important;
        transition: all 0.15s ease-in-out !important;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .btn-lime-primary:hover {
        background-color: #b8e600 !important;
        border-color: #b8e600 !important;
        color: #090D15 !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(204, 255, 0, 0.3) !important;
    }

    /* Secondary Ghost Button */
    .btn-ghost-secondary {
        background-color: transparent !important;
        color: #cbd5e1 !important;
        font-weight: 600 !important;
        border-radius: 0.375rem !important;
        border: 1px solid #334155 !important;
        transition: all 0.15s ease-in-out !important;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .btn-ghost-secondary:hover {
        background-color: #1e293b !important;
        color: #ffffff !important;
        border-color: #475569 !important;
    }

    /* Form Fields */
    .input-field {
        width: 100%;
        padding: 0.65rem 0.85rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
        border: 1px solid #1E293B !important;
        background-color: #141D30 !important;
        color: #ffffff !important;
        font-weight: 500;
        outline: none;
        transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
        appearance: none;
        -webkit-appearance: none;
    }
    .input-field::placeholder {
        color: #64748b !important;
        opacity: 1;
    }
    .input-field:focus {
        border-color: #CCFF00 !important;
        background-color: #18233C !important;
        box-shadow: 0 0 0 1px rgba(204, 255, 0, 0.3) !important;
    }

    select.input-field {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
        background-position: right 0.75rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1.25em 1.25em !important;
        padding-right: 2.25rem !important;
    }

    input[type="date"].input-field {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'/%3e%3c/svg%3e") !important;
        background-position: right 0.75rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1.2em 1.2em !important;
        padding-right: 2.25rem !important;
    }
    input[type="date"].input-field::-webkit-calendar-picker-indicator {
        background: transparent;
        cursor: pointer;
        opacity: 0;
    }

    .label-text {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #cbd5e1;
        margin-bottom: 0.35rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* FAQ Details & Summary */
    details.faq-item summary::-webkit-details-marker { display: none; }
    details.faq-item summary { list-style: none; }
    details.faq-item[open] .faq-chevron { transform: rotate(180deg); }

    .fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
    .fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(4px); }
</style>
@endpush

@section('content')
<div id="generator-v2-app" class="generator-v2-wrapper relative w-full pt-0 pb-16">

    <!-- Notification Toast -->
    <transition name="fade">
        <div v-if="notification" class="fixed top-6 right-4 z-[100] max-w-sm w-full">
            <div :class="notification.type === 'error' ? 'bg-red-950 border-red-800 text-red-100' : 'bg-slate-900 border-emerald-600 text-emerald-100'" 
                 class="p-4 rounded-md border shadow-lg flex items-start gap-3">
                <div class="flex-1 text-xs font-medium">@{{ notification.message }}</div>
                <button @click="notification = null" class="text-slate-400 hover:text-slate-200 text-xs">✕</button>
            </div>
        </div>
    </transition>

    <!-- Active Program Conflict Modal -->
    <transition name="fade">
        <div v-if="conflictModal.show" class="fixed inset-0 z-[150] flex items-center justify-center p-4 bg-black/80">
            <div class="surface-card max-w-md w-full p-6 rounded-lg border border-amber-500/40 shadow-xl space-y-4 relative">
                <div>
                    <h3 class="text-base font-bold text-white">Program Aktif Terdeteksi</h3>
                    <p class="text-xs text-slate-300 mt-0.5">Kalender Anda sudah memiliki program latihan aktif saat ini.</p>
                </div>

                <div class="p-3 rounded-md surface-nested space-y-1">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Program Aktif Saat Ini:</div>
                    <div class="text-sm font-semibold text-white">@{{ conflictModal.activeTitle }}</div>
                    <div v-if="conflictModal.activeStartDate" class="text-xs text-slate-400">
                        Periode: @{{ conflictModal.activeStartDate }} - @{{ conflictModal.activeEndDate }}
                    </div>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Apakah Anda ingin mengganti program lama dengan program baru ini, atau menambahkan program baru ini ke kalender?
                </p>

                <div class="flex flex-col gap-2 pt-2">
                    <button @click="confirmConflictAction('replace')" :disabled="saving" 
                            class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs uppercase tracking-wider rounded-md transition cursor-pointer">
                        Ganti Program Aktif (Replace)
                    </button>
                    <button @click="confirmConflictAction('add')" :disabled="saving" 
                            class="w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 font-semibold text-xs uppercase tracking-wider rounded-md transition cursor-pointer">
                        Tambahkan Saja (Add)
                    </button>
                    <button @click="conflictModal.show = false" :disabled="saving" 
                            class="w-full py-2 text-slate-400 hover:text-slate-200 text-xs font-medium transition cursor-pointer">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </transition>

    <!-- SECTION 1: HERO VIEWPORT (Controlled Height ~70-80vh, No Form Clutter) -->
    <header class="relative w-full min-h-[70vh] sm:min-h-[75vh] flex items-center justify-center overflow-hidden border-b border-slate-800 bg-[#090D15] text-white py-12 sm:py-16 px-4 sm:px-6 lg:px-8">
        
        <!-- Subtle Athletic Route Track Background -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden z-0 select-none opacity-20" aria-hidden="true">
            <svg class="w-full h-full object-cover min-w-[1000px]" viewBox="0 0 1440 600" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="heroGrid" width="48" height="48" patternUnits="userSpaceOnUse">
                        <path d="M 48 0 L 0 0 0 48" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="1440" height="600" fill="url(#heroGrid)" />
                <path d="M-80 220 C 220 120, 480 340, 820 200 C 1160 70, 1340 280, 1560 170" stroke="rgba(255, 255, 255, 0.05)" stroke-width="1.5" stroke-dasharray="6 6"/>
                <path d="M 40 450 C 160 370, 260 470, 420 410 C 560 350, 620 200, 780 220 C 940 240, 990 410, 1140 340 C 1280 280, 1360 150, 1480 170" 
                      stroke="#CCFF00" stroke-opacity="0.3" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">

            <!-- Left Column: Focused Copy, 3 Micro Steps, 1 Primary CTA -->
            <div class="lg:col-span-7 space-y-6 text-left">
                
                <!-- Main H1 Heading -->
                <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-extrabold text-white tracking-tight leading-[1.12]">
                    Buat Program Lari Terstruktur.<br>
                    <span class="text-[#CCFF00]">Capai Target 5K Hingga Marathon.</span>
                </h1>

                <!-- Subhead -->
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl font-normal">
                    Susun jadwal latihan harian yang dipersonalisasi dari catatan waktu terbaik (PB) Anda. Dihitung menggunakan formula empiris VDOT Jack Daniels untuk 5 zona pace presisi yang aman dan bebas cedera.
                </p>

                <!-- 3 Inline Micro Steps under Subhead -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-slate-300 pt-1">
                    <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-md">
                        <span class="w-4 h-4 rounded bg-[#CCFF00] text-slate-950 font-black text-[10px] flex items-center justify-center">1</span>
                        <span class="font-medium text-slate-200">Input PB Terkini</span>
                    </div>
                    <span class="text-slate-600 hidden sm:inline">→</span>
                    <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-md">
                        <span class="w-4 h-4 rounded bg-[#CCFF00] text-slate-950 font-black text-[10px] flex items-center justify-center">2</span>
                        <span class="font-medium text-slate-200">Tentukan Target Lomba</span>
                    </div>
                    <span class="text-slate-600 hidden sm:inline">→</span>
                    <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-800 px-3 py-1.5 rounded-md">
                        <span class="w-4 h-4 rounded bg-[#CCFF00] text-slate-950 font-black text-[10px] flex items-center justify-center">3</span>
                        <span class="font-medium text-slate-200">Dapatkan Kalender Latihan</span>
                    </div>
                </div>

                <!-- CTAs: Exactly 1 Primary Solid Lime Button + 1 Ghost Link -->
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    <button type="button" @click="openWizard" 
                            class="btn-lime-primary px-5 py-3.5 text-xs sm:text-sm tracking-wider uppercase font-extrabold">
                        <span>Buat Program Lari</span>
                    </button>
                    
                    <a href="#dasar-vdot" 
                       class="text-xs sm:text-sm font-semibold text-slate-300 hover:text-white underline-offset-4 hover:underline transition">
                        Lihat Dasar Ilmiah VDOT →
                    </a>
                </div>

                <!-- Trust Line -->
                <div class="pt-2 text-xs text-slate-400 font-medium flex items-center gap-2 flex-wrap">
                    <span class="text-slate-200">100% Gratis</span>
                    <span class="text-slate-600">•</span>
                    <span>Berbasis VDOT Jack Daniels</span>
                    <span class="text-slate-600">•</span>
                    <span>5 Zona Pace & Kalender Harian</span>
                </div>

            </div>

            <!-- Right Column: Interactive Training Lab (VDOT Pace Calculator) — sesuai /programs -->
            <div class="lg:col-span-5 relative">
                <div class="rounded-lg border border-slate-800 bg-slate-900 shadow-2xl overflow-hidden border-t-2 border-t-[#CCFF00]">
                    <!-- Card Header -->
                    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
                        <div>
                            <span class="text-[10px] font-mono font-bold tracking-widest text-slate-400 uppercase block">Performance Lab / VDOT</span>
                            <h2 class="text-base sm:text-lg font-bold text-white mt-0.5">Kalkulator Pace & Target Lari Interaktif</h2>
                        </div>
                        <span class="px-2 py-0.5 rounded bg-slate-800 border border-slate-700 text-[10px] font-mono font-bold text-[#CCFF00]">RL-01</span>
                    </div>

                    <!-- Form Input Body -->
                    <div class="p-5 sm:p-6 space-y-4">
                        <!-- Distance Parameter Selection -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Jarak Parameter / PB Terakhir
                            </label>
                            <select v-model="heroVdotDistance"
                                    class="w-full px-3.5 py-2.5 rounded-md bg-slate-950 border border-slate-800 text-white text-xs sm:text-sm focus:outline-none focus:border-[#CCFF00] transition cursor-pointer">
                                <option value="5k">5K (5 Kilometer)</option>
                                <option value="10k">10K (10 Kilometer)</option>
                                <option value="21k">Half Marathon (21.1K)</option>
                                <option value="42k">Full Marathon (42.2K)</option>
                            </select>
                        </div>

                        <!-- Time Input (Jam, Menit, Detik) -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Catatan Waktu PB
                            </label>
                            <div class="grid grid-cols-3 gap-2.5">
                                <div>
                                    <div class="relative">
                                        <input v-model.number="heroVdotH" type="number" min="0" max="99"
                                               class="w-full px-3 py-2.5 rounded-md bg-slate-950 border border-slate-800 text-white text-center text-sm font-bold focus:outline-none focus:border-[#CCFF00] transition">
                                    </div>
                                    <span class="text-[10px] text-slate-400 block text-center mt-1">Jam</span>
                                </div>
                                <div>
                                    <div class="relative">
                                        <input v-model.number="heroVdotM" type="number" min="0" max="59"
                                               class="w-full px-3 py-2.5 rounded-md bg-slate-950 border border-slate-800 text-white text-center text-sm font-bold focus:outline-none focus:border-[#CCFF00] transition">
                                    </div>
                                    <span class="text-[10px] text-slate-400 block text-center mt-1">Menit</span>
                                </div>
                                <div>
                                    <div class="relative">
                                        <input v-model.number="heroVdotS" type="number" min="0" max="59"
                                               class="w-full px-3 py-2.5 rounded-md bg-slate-950 border border-slate-800 text-white text-center text-sm font-bold focus:outline-none focus:border-[#CCFF00] transition">
                                    </div>
                                    <span class="text-[10px] text-slate-400 block text-center mt-1">Detik</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Calculate Button -->
                        <button type="button" @click="heroCalculateVdot"
                                class="w-full btn-lime-primary py-3 rounded-md font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer transition">
                            <span>Hitung VDOT & Target Pace</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Result Display Box (Revealed on calculate) -->
                    <div v-show="heroVdotCalculated" class="border-t border-slate-800 bg-slate-950/80 p-5 space-y-4">
                        <!-- VDOT Score Strip -->
                        <div class="p-3.5 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider block">Estimasi VDOT Score</span>
                                <span class="text-2xl font-black text-[#CCFF00] mt-0.5 block">@{{ (heroVdotCompute.vdot || 0).toFixed(1) }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider block">Level Kebugaran</span>
                                <span class="text-xs sm:text-sm font-bold text-white mt-0.5 block">@{{ heroVdotLevel }}</span>
                            </div>
                        </div>

                        <!-- Tab Buttons -->
                        <div class="grid grid-cols-2 gap-1.5 p-1 rounded-md bg-slate-900 border border-slate-800 text-xs font-semibold">
                            <button type="button" @click="heroVdotTab = 'paces'"
                                    :class="heroVdotTab === 'paces' ? 'bg-[#CCFF00] text-slate-950 font-bold' : 'text-slate-300 hover:text-white'"
                                    class="py-1.5 px-3 rounded text-center transition">
                                Pace Latihan
                            </button>
                            <button type="button" @click="heroVdotTab = 'races'"
                                    :class="heroVdotTab === 'races' ? 'bg-[#CCFF00] text-slate-950 font-bold' : 'text-slate-300 hover:text-white'"
                                    class="py-1.5 px-3 rounded text-center transition">
                                Prediksi Race
                            </button>
                        </div>

                        <!-- Tab 1: Paces -->
                        <div v-show="heroVdotTab === 'paces'" class="space-y-2 text-xs">
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">Easy / Recovery (Zone 2)</span>
                                <strong class="text-white font-bold">@{{ heroFormatPace(heroVdotCompute.easy_high) }} - @{{ heroFormatPace(heroVdotCompute.easy_low) }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">Marathon Pace</span>
                                <strong class="text-white font-bold">@{{ heroFormatPace(heroVdotCompute.marathon) }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">Threshold / Tempo</span>
                                <strong class="text-white font-bold">@{{ heroFormatPace(heroVdotCompute.threshold) }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">Interval (VO2max)</span>
                                <strong class="text-white font-bold">@{{ heroFormatPace(heroVdotCompute.interval) }} <span class="text-slate-400 font-normal text-[11px]">(@{{ heroVdotCompute.interval_400 }}s/400m)</span></strong>
                            </div>
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">Repetition (Speed Form)</span>
                                <strong class="text-white font-bold">@{{ heroFormatPace(heroVdotCompute.repetition) }} <span class="text-slate-400 font-normal text-[11px]">(@{{ heroVdotCompute.repetition_400 }}s/400m)</span></strong>
                            </div>
                        </div>

                        <!-- Tab 2: Races -->
                        <div v-show="heroVdotTab === 'races'" class="space-y-2 text-xs">
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">5K Race Target</span>
                                <strong class="text-white font-bold">@{{ heroFormatDur(heroVdotCompute.r_5k) }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">10K Race Target</span>
                                <strong class="text-white font-bold">@{{ heroFormatDur(heroVdotCompute.r_10k) }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">Half Marathon (21.1K)</span>
                                <strong class="text-white font-bold">@{{ heroFormatDur(heroVdotCompute.r_21k) }}</strong>
                            </div>
                            <div class="p-2.5 rounded bg-slate-900 border border-slate-800 flex items-center justify-between">
                                <span class="text-slate-300 font-medium">Marathon (42.2K)</span>
                                <strong class="text-white font-bold">@{{ heroFormatDur(heroVdotCompute.r_42k) }}</strong>
                            </div>
                        </div>

                        <!-- Action Buttons: CTA sync ke Wizard Step 1 (auto scroll ke tool-container) -->
                        <div class="flex flex-col sm:flex-row gap-2 pt-1">
                            <button type="button" @click="heroApplyToWizard"
                                    class="flex-1 btn-lime-primary py-2.5 px-4 rounded-md text-white text-xs font-bold uppercase tracking-wider transition cursor-pointer text-center">
                                Buat Program
                            </button>
                            <a href="#dasar-vdot"
                               class="flex-1 text-center py-2.5 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs uppercase tracking-wider transition cursor-pointer">
                                Lihat Metodologi
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </header>

    <!-- SECTION 2: TOOL AREA (Visual Separator 96px Spacing, Surface Elevation Shift) -->
    <section id="tool-container" class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 sm:pt-24 pb-16">
        
        <!-- Breadcrumb Navigation placed at top of tool area -->
        <nav aria-label="Breadcrumb" class="mb-6 text-xs text-slate-400">
            <ol class="flex items-center gap-2 flex-wrap">
                <li><a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a></li>
                <li class="text-slate-600">/</li>
                <li><a href="{{ route('tools.index') }}" class="hover:text-white transition">Running Tools</a></li>
                <li class="text-slate-600">/</li>
                <li class="text-slate-200 font-semibold" aria-current="page">Buat Program Lari</li>
            </ol>
        </nav>

        <!-- STATE A: PREVIEW TOOL (Static High-Converting Mock, not broken 0:00:00 form) -->
        <div v-if="!isWizardOpen && step === 1" class="space-y-8">
            
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 pb-4 border-b border-slate-800">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Preview Generator Program Lari</h2>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1">Lihat alur 4 tahap bagaimana Personal Best (PB) Anda diolah menjadi jadwal periodik terukur.</p>
                </div>
                <div>
                    <button type="button" @click="openWizard" class="btn-lime-primary px-5 py-2.5 text-xs font-bold uppercase tracking-wider">
                        Buka Formulir Lengkap
                    </button>
                </div>
            </div>

            <!-- 4-Step Progress Preview (Clean Roadmap, Visual Guide) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-3.5 rounded-lg surface-card border border-slate-800 flex flex-col justify-between opacity-90">
                    <div class="text-[10px] font-bold text-[#CCFF00] uppercase tracking-wider mb-1">Tahap 01</div>
                    <div class="text-xs sm:text-sm font-bold text-white">Kebugaran PB</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Tolok Ukur VDOT</div>
                </div>
                <div class="p-3.5 rounded-lg surface-card border border-slate-800 flex flex-col justify-between opacity-75">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tahap 02</div>
                    <div class="text-xs sm:text-sm font-bold text-white">Target Lomba</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Jarak & Kalender Race</div>
                </div>
                <div class="p-3.5 rounded-lg surface-card border border-slate-800 flex flex-col justify-between opacity-75">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tahap 03</div>
                    <div class="text-xs sm:text-sm font-bold text-white">Beban Latihan</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Mileage & Frekuensi</div>
                </div>
                <div class="p-3.5 rounded-lg surface-card border border-slate-800 flex flex-col justify-between opacity-75">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tahap 04</div>
                    <div class="text-xs sm:text-sm font-bold text-white">Profil & Review</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Konfirmasi Program</div>
                </div>
            </div>

            <!-- Single Mock/Preview Card with Realistic Sample (PB 5K 00:27:30 -> VDOT 36.5) -->
            <div class="surface-card p-6 sm:p-8 rounded-lg border border-slate-800 space-y-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-white">Simulasi Output Program</h3>
                            <span class="text-[10px] font-bold text-slate-950 bg-[#CCFF00] px-2 py-0.5 rounded uppercase">Contoh Hasil</span>
                        </div>
                        <p class="text-xs text-slate-300 mt-1">Ilustrasi program race 10K yang dihasilkan secara otomatis dari data tolok ukur PB 5K.</p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Skor Kebugaran Terhitung</span>
                        <span class="text-xl font-black text-white">VDOT 36.5</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-md surface-nested space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Tolok Ukur Contoh:</span>
                        <div class="text-sm font-bold text-white">5K — 00:27:30</div>
                        <div class="text-[11px] text-slate-400">Pace rata-rata 05:30 /km</div>
                    </div>
                    <div class="p-4 rounded-md surface-nested space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Target Lomba Contoh:</span>
                        <div class="text-sm font-bold text-white">10K (12 Pekan)</div>
                        <div class="text-[11px] text-slate-400">4 Sesi / Minggu • Puncak 35 km</div>
                    </div>
                    <div class="p-4 rounded-md surface-nested space-y-1">
                        <span class="text-[10px] font-bold uppercase text-slate-400 tracking-wider">Kelayakan Target:</span>
                        <div class="text-sm font-bold text-emerald-400">Realistis</div>
                        <div class="text-[11px] text-slate-400">+4.2% peningkatan terukur</div>
                    </div>
                </div>

                <!-- 5 Pace Zones Snapshot -->
                <div class="p-4 rounded-md surface-nested space-y-3">
                    <div class="text-xs font-bold text-slate-300 uppercase tracking-wider">5 Zona Pace Latihan Terkalibrasi:</div>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-xs">
                        <div class="p-2.5 rounded bg-[#111726] border border-slate-800">
                            <span class="text-emerald-400 font-bold block text-[11px]">Easy (E)</span>
                            <span class="text-white text-xs mt-0.5 block font-bold">06:45–07:15</span>
                            <span class="text-[10px] text-slate-400">65–78% HR Max</span>
                        </div>
                        <div class="p-2.5 rounded bg-[#111726] border border-slate-800">
                            <span class="text-blue-400 font-bold block text-[11px]">Marathon (M)</span>
                            <span class="text-white text-xs mt-0.5 block font-bold">06:12 /km</span>
                            <span class="text-[10px] text-slate-400">79–88% HR Max</span>
                        </div>
                        <div class="p-2.5 rounded bg-[#111726] border border-slate-800">
                            <span class="text-amber-400 font-bold block text-[11px]">Threshold (T)</span>
                            <span class="text-white text-xs mt-0.5 block font-bold">05:40 /km</span>
                            <span class="text-[10px] text-slate-400">88–92% HR Max</span>
                        </div>
                        <div class="p-2.5 rounded bg-[#111726] border border-slate-800">
                            <span class="text-rose-400 font-bold block text-[11px]">Interval (I)</span>
                            <span class="text-white text-xs mt-0.5 block font-bold">05:12 /km</span>
                            <span class="text-[10px] text-slate-400">95–100% HR Max</span>
                        </div>
                        <div class="p-2.5 rounded bg-[#111726] border border-slate-800 col-span-2 sm:col-span-1">
                            <span class="text-[#FC4C02] font-bold block text-[11px]">Repetition (R)</span>
                            <span class="text-white text-xs mt-0.5 block font-bold">04:50 /km</span>
                            <span class="text-[10px] text-slate-400">>105% Anaerobik</span>
                        </div>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-800">
                    <p class="text-xs text-slate-400 text-center sm:text-left">
                        Mulai dari catatan waktu PB asli Anda untuk menyusun kalender harian yang 100% dipersonalisasi.
                    </p>
                    <button type="button" @click="openWizard" 
                            class="btn-lime-primary w-full sm:w-auto px-5 py-3.5 text-xs sm:text-sm font-bold uppercase tracking-wider">
                        <span>Mulai</span>
                    </button>
                </div>

            </div>

        </div>

        <!-- STATE B: ACTIVE OPERATIONAL WIZARD (Opens upon clicking CTA) -->
        <div v-if="isWizardOpen && step === 1" id="wizard-container" class="space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 border-b border-slate-800">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Formulir Parameter Program Latihan</h2>
                    <p class="text-xs text-slate-300 mt-0.5">Panduan 4 tahap untuk menghasilkan periodisasi yang presisi dan realistis.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="closeWizard" class="text-xs text-slate-300 hover:text-white py-1.5 px-3 rounded-md border border-slate-800 bg-[#162035] transition cursor-pointer">
                        Tutup Formulir
                    </button>
                    <button type="button" @click="resetFormDraft" class="text-xs text-slate-300 hover:text-rose-400 py-1.5 px-3 rounded-md border border-slate-800 bg-[#162035] transition cursor-pointer">
                        Reset Draf
                    </button>
                </div>
            </div>

            <!-- 4-Step Interactive Stepper Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <button v-for="s in [
                            { step: 1, title: 'Kebugaran PB', sub: 'Tolok Ukur VDOT' },
                            { step: 2, title: 'Target Lomba', sub: 'Jarak & Kalender' },
                            { step: 3, title: 'Beban Latihan', sub: 'Mileage & Jadwal' },
                            { step: 4, title: 'Profil & Review', sub: 'Konfirmasi Program' }
                        ]" 
                        :key="s.step"
                        type="button"
                        @click="goToWizardStep(s.step)"
                        :class="[
                            wizardStep === s.step 
                                ? 'form-tab-active font-bold' 
                                : (wizardStep > s.step 
                                    ? 'bg-[#162035] text-[#CCFF00] border-slate-800' 
                                    : 'bg-[#111726] text-slate-300 border-slate-800')
                        ]"
                        class="p-3 rounded-md border text-left transition cursor-pointer flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-1">
                        <span :class="wizardStep === s.step ? 'text-slate-950 font-extrabold' : 'text-slate-400 opacity-80'" class="text-[10px] uppercase tracking-wider">Tahap 0@{{ s.step }}</span>
                        <span v-if="wizardStep > s.step" class="text-xs font-black">✓</span>
                        <span v-else-if="wizardStep === s.step" class="badge-active-tag text-[10px] font-extrabold px-1.5 py-0.5 rounded">Aktif</span>
                    </div>
                    <div :class="wizardStep === s.step ? 'text-slate-950 font-extrabold' : 'text-white font-bold'" class="text-xs sm:text-sm truncate">@{{ s.title }}</div>
                    <div :class="wizardStep === s.step ? 'text-slate-950 font-medium' : 'text-slate-400 opacity-75'" class="text-[10px] truncate mt-0.5">@{{ s.sub }}</div>
                </button>
            </div>

            <div>
                
                <!-- Wizard Step 1: Benchmark PB -->
                <div v-show="wizardStep === 1" key="wiz1" class="space-y-6">
                    <div class="surface-card p-5 sm:p-6 rounded-lg border border-slate-800">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 pb-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-white">1. Tolok Ukur Kebugaran Awal (Benchmark / PB)</h3>
                                <p class="text-xs text-slate-300 mt-0.5">Hasil tes kebugaran atau PB terkini untuk kalkulasi VDOT Jack Daniels</p>
                            </div>
                            <div v-if="current_vdot && current_vdot > 0" class="text-left sm:text-right bg-[#162035] sm:bg-transparent p-2.5 sm:p-0 rounded-md sm:rounded-none border border-slate-800 sm:border-0">
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-medium">Estimasi VDOT</span>
                                <span class="text-base font-black text-slate-950 bg-[#CCFF00] px-2.5 py-0.5 rounded inline-block mt-0.5 font-bold">
                                    @{{ current_vdot.toFixed(1) }}
                                </span>
                            </div>
                            <div v-else class="text-left sm:text-right text-xs text-slate-400">
                                <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-medium">Estimasi VDOT</span>
                                <span class="text-xs text-slate-400 italic">Isi waktu PB di bawah</span>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="label-text">Pilih Jarak Parameter Test / Personal Best Terkini</label>
                                <select v-model="form.pb_distance" class="input-field cursor-pointer text-sm">
                                    <option value="5k">5 Kilometer (5K)</option>
                                    <option value="10k">10 Kilometer (10K)</option>
                                    <option value="21k">Half Marathon (21.1K)</option>
                                    <option value="42k">Full Marathon (42.2K)</option>
                                    <option value="cooper12">Cooper Test (Lari Maksimal 12 Menit)</option>
                                    <option value="balke15">Balke Test (Lari Maksimal 15 Menit)</option>
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">Pilih jarak yang baru saja Anda selesaikan dengan upaya maksimal dalam 3-6 bulan terakhir.</p>
                            </div>

                            <!-- Standard Time Input (No dummy 0:30:0, clean placeholders) -->
                            <div v-if="form.pb_distance !== 'cooper12' && form.pb_distance !== 'balke15'">
                                <label class="label-text">Waktu Tempuh Parameter Test (Jam : Menit : Detik)</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <input v-model.number="pb_hours" type="number" min="0" max="99" class="input-field text-center font-bold text-base sm:text-lg" placeholder="00">
                                        <span class="text-[11px] text-slate-300 text-center block mt-1 font-medium">Jam</span>
                                    </div>
                                    <div>
                                        <input v-model.number="pb_minutes" type="number" min="0" max="59" class="input-field text-center font-bold text-base sm:text-lg" placeholder="mis. 27">
                                        <span class="text-[11px] text-slate-300 text-center block mt-1 font-medium">Menit</span>
                                    </div>
                                    <div>
                                        <input v-model.number="pb_seconds" type="number" min="0" max="59" class="input-field text-center font-bold text-base sm:text-lg" placeholder="mis. 30">
                                        <span class="text-[11px] text-slate-300 text-center block mt-1 font-medium">Detik</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cooper/Balke Distance Input -->
                            <div v-else>
                                <label class="label-text">Jarak Tempuh Hasil Tes (Meter)</label>
                                <div class="relative">
                                    <input v-model.number="pb_distance_meters" type="number" min="100" max="9999" class="input-field font-bold text-base sm:text-lg pr-16" placeholder="Contoh: 2400">
                                    <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-300">meter</span>
                                </div>
                                <p class="text-[11px] text-slate-300 mt-1.5 leading-normal">Standar tes 12 menit: 2.000m - 2.800m untuk rekreasional, >3.000m untuk terlatih.</p>
                            </div>

                            <!-- Informational Tip Box -->
                            <div class="p-3.5 surface-nested rounded-md flex items-start gap-3">
                                <div class="w-5 h-5 rounded bg-[#111726] text-[#CCFF00] font-bold flex items-center justify-center text-xs shrink-0 mt-0.5">i</div>
                                <div class="text-xs text-slate-300 leading-relaxed">
                                    <span class="font-semibold text-white">Prinsip Fisiologi:</span> Waktu tolok ukur digunakan untuk memetakan kapasitas VO2 Max fungsional (VDOT). Seluruh target pace latihan harian akan berpatokan dari data ini untuk mencegah overtraining.
                                </div>
                            </div>
                        </div>

                        <!-- Next Button (Only Primary Lime Button in this step) -->
                        <div class="flex justify-end pt-5 mt-6 border-t border-slate-800">
                            <button type="button" @click="nextWizardStep" class="btn-lime-primary px-6 py-2.5 text-xs sm:text-sm tracking-wider uppercase font-bold">
                                <span>Lanjut ke Target Lomba →</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Wizard Step 2: Target Lomba & Kalender -->
                <div v-show="wizardStep === 2" key="wiz2" class="space-y-6">
                    <div class="surface-card p-5 sm:p-6 rounded-lg border border-slate-800">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 pb-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-base font-bold text-white">2. Target Lomba & Kalender Persiapan</h3>
                                <p class="text-xs text-slate-300 mt-0.5">Jarak sasaran, batas waktu persiapan, dan estimasi waktu finish</p>
                            </div>
                            <span v-if="realism" :class="realism.color" class="px-2.5 py-1 rounded text-[10px] font-bold uppercase tracking-wider self-start sm:self-auto border">
                                @{{ realism.label }}
                            </span>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="label-text">Target Jarak Lomba</label>
                                <select v-model="form.target_distance" @change="recommendMileage" class="input-field cursor-pointer text-sm">
                                    <option value="5k">5K (5 Kilometer)</option>
                                    <option value="10k">10 Kilometer (10K)</option>
                                    <option value="21k">Half Marathon (21.0975 Km)</option>
                                    <option value="42k">Full Marathon (42.195 Km)</option>
                                    <option value="cooper12">Cooper Test 12 Menit</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="label-text">Tanggal Mulai Latihan</label>
                                    <input v-model="form.start_date" type="date" class="input-field">
                                </div>
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="label-text !mb-0">Tanggal Race / Target Selesai</label>
                                        <button v-if="recommendedTargetDate && form.target_date !== recommendedTargetDate" 
                                                @click="applyRecommendedTargetDate" 
                                                type="button" 
                                                class="text-[10px] font-semibold text-[#CCFF00] hover:underline cursor-pointer">
                                            Set @{{ recommendedWeeks }} Mgg
                                        </button>
                                    </div>
                                    <input v-model="form.target_date" type="date" class="input-field">
                                </div>
                            </div>

                            <div>
                                <label class="label-text">Target Waktu Finish (Jam : Menit : Detik)</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <input v-model.number="goal_hours" type="number" min="0" max="99" class="input-field text-center font-bold text-base sm:text-lg" placeholder="00">
                                        <span class="text-[11px] text-slate-300 text-center block mt-1 font-medium">Jam</span>
                                    </div>
                                    <div>
                                        <input v-model.number="goal_minutes" type="number" min="0" max="59" class="input-field text-center font-bold text-base sm:text-lg" placeholder="00">
                                        <span class="text-[11px] text-slate-300 text-center block mt-1 font-medium">Menit</span>
                                    </div>
                                    <div>
                                        <input v-model.number="goal_seconds" type="number" min="0" max="59" class="input-field text-center font-bold text-base sm:text-lg" placeholder="00">
                                        <span class="text-[11px] text-slate-300 text-center block mt-1 font-medium">Detik</span>
                                    </div>
                                </div>
                                <div v-if="realism" class="mt-4 space-y-3">
                                    <div class="surface-nested p-4 rounded-lg border border-slate-700/60">
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div>
                                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Skor Kelayakan</span>
                                                <div class="mt-1.5 flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold border" :class="realism.color">
                                                        @{{ realism.label }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-black text-white">@{{ realism.score }}<span class="text-sm font-semibold text-slate-500">/100</span></div>
                                                <div class="w-24 h-1.5 mt-1.5 bg-slate-800 rounded-full overflow-hidden">
                                                    <div class="h-full transition-all duration-500 rounded-full" :class="{
                                                        'bg-emerald-500': realism.feasibility === 'FEASIBLE',
                                                        'bg-amber-500': realism.feasibility === 'AGGRESSIVE',
                                                        'bg-orange-500': realism.feasibility === 'HIGH_RISK',
                                                        'bg-red-500': realism.feasibility === 'INFEASIBLE'
                                                    }" :style="{ width: (realism.score + '%') }"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-xs text-slate-200 leading-relaxed">@{{ realism.description }}</p>

                                        <div class="mt-4 pt-3 border-t border-slate-700/50">
                                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 block">Tingkat Agresivitas Latihan</span>
                                            <div class="grid grid-cols-3 gap-2">
                                                <button type="button" @click="formAggressiveness = 'conservative'"
                                                        :class="formAggressiveness === 'conservative' ? 'bg-emerald-900/40 border-emerald-500/50 text-emerald-300 font-bold' : 'bg-slate-800/50 border-slate-700/60 text-slate-300'"
                                                        class="py-2 px-2 rounded-md text-[11px] border transition cursor-pointer">
                                                    <div class="font-bold">Konservatif</div>
                                                    <div class="text-[10px] opacity-70 mt-0.5">Beban -8%, Minggu +15%</div>
                                                </button>
                                                <button type="button" @click="formAggressiveness = 'standard'"
                                                        :class="formAggressiveness === 'standard' ? 'bg-amber-900/40 border-amber-500/50 text-amber-300 font-bold' : 'bg-slate-800/50 border-slate-700/60 text-slate-300'"
                                                        class="py-2 px-2 rounded-md text-[11px] border transition cursor-pointer">
                                                    <div class="font-bold">Standar</div>
                                                    <div class="text-[10px] opacity-70 mt-0.5">Rekomendasi default</div>
                                                </button>
                                                <button type="button" @click="formAggressiveness = 'sharp'"
                                                        :class="formAggressiveness === 'sharp' ? 'bg-rose-900/40 border-rose-500/50 text-rose-300 font-bold' : 'bg-slate-800/50 border-slate-700/60 text-slate-300'"
                                                        class="py-2 px-2 rounded-md text-[11px] border transition cursor-pointer">
                                                    <div class="font-bold">Agresif</div>
                                                    <div class="text-[10px] opacity-70 mt-0.5">Beban +10%, Minggu -10%</div>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="realism.options && realism.options.length" class="space-y-2">
                                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 pl-1">Saran Penyesuaian 1-Klik</span>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2">
                                            <button v-for="(opt, idx) in realism.options" :key="opt.id || ('chip-'+idx)"
                                                    type="button" @click="applyChipOption(opt)"
                                                    class="text-left px-3 py-2.5 rounded-md border text-xs transition cursor-pointer hover:scale-[1.01] border-slate-700/60 bg-slate-800/40 text-slate-200 hover:bg-slate-800 hover:border-slate-600">
                                                <span class="inline-block w-1.5 h-1.5 rounded-full mr-2 align-middle bg-[#CCFF00]"></span>
                                                @{{ opt.label }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex items-center justify-between pt-5 mt-6 border-t border-slate-800">
                            <button type="button" @click="prevWizardStep" class="btn-ghost-secondary px-5 py-2.5 text-xs font-semibold">
                                ← Kembali
                            </button>
                            <button type="button" @click="nextWizardStep" class="btn-lime-primary px-6 py-2.5 text-xs sm:text-sm tracking-wider uppercase font-bold">
                                <span>Lanjut ke Beban Latihan →</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Wizard Step 3: Beban Latihan & Jadwal -->
                <div v-show="wizardStep === 3" key="wiz3" class="space-y-6">
                    <div class="surface-card p-5 sm:p-6 rounded-lg border border-slate-800">
                        <div class="mb-5 pb-4 border-b border-slate-800">
                            <h3 class="text-base font-bold text-white">3. Beban & Jadwal Latihan</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Alokasi volume mingguan, frekuensi hari latihan, dan preferensi penguatan</p>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="label-text !mb-0">Mileage Mingguan Puncak (Km)</label>
                                    <span class="text-[11px] font-semibold" :class="coachAssessment && coachAssessment.color === 'emerald' ? 'text-emerald-300' : (coachAssessment && coachAssessment.color === 'red' ? 'text-red-300' : 'text-[#CCFF00]')">Saran: @{{ idealMileage }} km · Min: @{{ coachAssessment.min_required_peak_mileage || '-' }} km</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input v-model.number="form.weekly_mileage" type="number" :min="minMileageDynamic" max="150" step="1"
                                           class="font-bold text-base rounded-md transition-all duration-300 w-full bg-slate-900/70 border text-slate-100 px-3 py-2.5 outline-none focus:ring-2 focus:ring-[#CCFF00]/30"
                                           :class="[
                                               highlightMileage ? 'border-[#CCFF00] ring-2 ring-[#CCFF00]/50 bg-slate-900 shadow-[0_0_16px_rgba(204,255,0,0.15)]' : 'border-slate-700 focus:border-[#CCFF00]',
                                               (coachAssessment && form.weekly_mileage > 0 && form.weekly_mileage < (coachAssessment.min_required_peak_mileage||0) * 0.85) ? '!border-red-500/70 !ring-2 !ring-red-500/20' : ''
                                           ]">
                                    <button type="button" @click="recommendMileage" class="btn-ghost-secondary px-3 py-2.5 text-xs whitespace-nowrap">
                                        Reset Saran
                                    </button>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1">Volume puncak pada fase transisi sebelum periode tapering perlombaan.</p>

                                <div v-if="coachAssessment && form.weekly_mileage > 0" class="mt-3 p-3 rounded-md border text-xs leading-relaxed"
                                     :class="{
                                        'bg-emerald-900/15 border-emerald-600/30 text-slate-200': coachAssessment.feasibility === 'FEASIBLE',
                                        'bg-amber-900/15 border-amber-600/30 text-slate-200': coachAssessment.feasibility === 'AGGRESSIVE',
                                        'bg-orange-900/15 border-orange-600/30 text-slate-200': coachAssessment.feasibility === 'HIGH_RISK',
                                        'bg-red-900/15 border-red-600/50 text-slate-200': coachAssessment.feasibility === 'INFEASIBLE'
                                     }">
                                    <div class="flex items-start gap-2">
                                        <span class="inline-flex items-center justify-center shrink-0 w-5 h-5 rounded-full font-bold mt-0.5"
                                              :class="{
                                                'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40': coachAssessment.feasibility === 'FEASIBLE',
                                                'bg-amber-500/20 text-amber-300 border border-amber-500/40': coachAssessment.feasibility === 'AGGRESSIVE',
                                                'bg-orange-500/20 text-orange-300 border border-orange-500/40': coachAssessment.feasibility === 'HIGH_RISK',
                                                'bg-red-500/20 text-red-300 border border-red-500/40': coachAssessment.feasibility === 'INFEASIBLE'
                                              }">
                                            <span v-if="coachAssessment.feasibility === 'FEASIBLE'">✓</span>
                                            <span v-else-if="coachAssessment.feasibility === 'AGGRESSIVE'">!</span>
                                            <span v-else-if="coachAssessment.feasibility === 'HIGH_RISK'">⚠</span>
                                            <span v-else>✕</span>
                                        </span>
                                        <div class="flex-1 space-y-2">
                                            <div>
                                                <span class="font-bold">
                                                    <span v-if="coachAssessment.feasibility === 'FEASIBLE'">Kelayakan Terpenuhi</span>
                                                    <span v-else-if="coachAssessment.feasibility === 'AGGRESSIVE'">Target Agresif</span>
                                                    <span v-else-if="coachAssessment.feasibility === 'HIGH_RISK'">Risiko Cedera Tinggi</span>
                                                    <span v-else>Tidak Realistis</span>
                                                </span>
                                                <span class="text-slate-400 ml-2 text-[10px] tabular-nums">Puncak @{{ form.weekly_mileage }}km vs batas @{{ coachAssessment.min_required_peak_mileage }}km</span>
                                            </div>
                                            <p class="text-slate-200">@{{ coachAssessment.reason }}</p>
                                            <div v-if="coachAssessment.feasibility === 'INFEASIBLE'" class="pt-1">
                                                <button type="button" @click="recommendMileage"
                                                        class="px-3 py-1.5 rounded-md bg-red-500/20 border border-red-500/40 text-red-300 hover:bg-red-500/30 transition cursor-pointer text-[11px] font-bold inline-flex items-center gap-1.5">
                                                    ↻ Terapkan Saran Pelatih (@{{ coachAssessment.ideal_peak_mileage }} km)
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="label-text">Frekuensi Latihan Mingguan</label>
                                <div class="grid grid-cols-5 gap-2">
                                    <button v-for="f in [3,4,5,6,7]" :key="f" type="button" @click="form.frequency = f" 
                                            :class="form.frequency === f ? 'form-tab-active font-bold' : 'surface-nested text-slate-200 border-slate-800'"
                                            class="py-2.5 rounded-md text-xs sm:text-sm transition border text-center cursor-pointer font-medium">
                                        @{{ f }} Hari
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="label-text">Level Pelari</label>
                                    <select v-model="form.runner_level" class="input-field cursor-pointer text-sm">
                                        <option value="beginner">Pemula (Beginner)</option>
                                        <option value="intermediate">Menengah (Intermediate)</option>
                                        <option value="advanced">Mahir (Advanced)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="label-text">Hari Long Run</label>
                                    <select v-model="form.long_run_day" class="input-field cursor-pointer text-sm">
                                        <option value="saturday">Sabtu</option>
                                        <option value="sunday">Minggu</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Strength Training -->
                            <div class="p-4 surface-nested rounded-md space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                                        <input type="checkbox" v-model="form.include_strength" class="w-4 h-4 accent-[#CCFF00] rounded">
                                        <span class="text-xs sm:text-sm font-semibold text-slate-200">Sertakan Strength Training</span>
                                    </label>
                                    <span v-if="form.include_strength" class="text-[10px] text-emerald-400 bg-emerald-950/40 px-2 py-0.5 rounded font-semibold border border-emerald-800/40">2x/Mgg</span>
                                </div>
                                <div v-if="form.include_strength" class="pt-1 space-y-2">
                                    <select v-model="form.strength_type" class="input-field text-xs sm:text-sm cursor-pointer">
                                        <option value="bodyweight">Rumah / Bodyweight (Tanpa Alat - Calisthenics & Core)</option>
                                        <option value="gym">Gym / Weighted (Beban Dumbbell, Squat & Deadlift)</option>
                                        <option value="plyometric">Plyometric (Daya Ledak, Reaktivitas & Tendon Achilles)</option>
                                        <option value="isometric">Isometric (Stabilitas Sendi, Patella & Core Hold)</option>
                                        <option value="hybrid">Hybrid Runner (Kombinasi Strength, Isometric & Plyo)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Tropical Adjustment -->
                            <div class="p-4 surface-nested rounded-md">
                                <label class="flex items-start gap-3 cursor-pointer select-none">
                                    <input type="checkbox" v-model="form.is_tropical" class="w-4 h-4 mt-0.5 accent-[#CCFF00] rounded">
                                    <div>
                                        <span class="text-xs sm:text-sm font-semibold text-slate-200 block">Adaptasi Iklim Tropis Indonesia</span>
                                        <span class="text-[11px] text-slate-300 block mt-1 leading-relaxed">
                                            Menyesuaikan target pace (+10 s/d 15 detik/km) untuk menjaga zona detak jantung dan toleransi kardiovaskular terhadap panas & kelembapan tinggi.
                                        </span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex items-center justify-between pt-5 mt-6 border-t border-slate-800">
                            <button type="button" @click="prevWizardStep" class="btn-ghost-secondary px-5 py-2.5 text-xs font-semibold">
                                ← Kembali
                            </button>
                            <button type="button" @click="nextWizardStep" class="btn-lime-primary px-6 py-2.5 text-xs sm:text-sm tracking-wider uppercase font-bold">
                                <span>Lanjut ke Profil & Review →</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Wizard Step 4: Profil Fisik & Konfirmasi Program -->
                <div v-show="wizardStep === 4" key="wiz4" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                        
                        <!-- Left Side: Physical Profile (6 cols) -->
                        <div class="lg:col-span-6 surface-card p-5 sm:p-6 rounded-lg border border-slate-800 space-y-5">
                            <div class="mb-4 pb-3 border-b border-slate-800">
                                <h3 class="text-base font-bold text-white">4. Profil Fisik & Riwayat Cedera</h3>
                                <p class="text-xs text-slate-300 mt-0.5">Penyesuaian nutrisi protein, indeks massa tubuh, dan rekomendasi protektif</p>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label-text">Jenis Kelamin</label>
                                    <div class="grid grid-cols-2 gap-1 p-1 surface-nested rounded-md">
                                        <button type="button" @click="form.gender = 'male'" :class="form.gender === 'male' ? 'form-tab-active font-bold' : 'text-slate-300 hover:text-white'" class="py-1.5 rounded text-xs transition cursor-pointer">Laki-laki</button>
                                        <button type="button" @click="form.gender = 'female'" :class="form.gender === 'female' ? 'form-tab-active font-bold' : 'text-slate-300 hover:text-white'" class="py-1.5 rounded text-xs transition cursor-pointer">Perempuan</button>
                                    </div>
                                </div>
                                <div>
                                    <label class="label-text">Usia (Tahun)</label>
                                    <input v-model.number="form.age" type="number" min="12" max="99" class="input-field font-bold">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="label-text">Tinggi Badan (cm)</label>
                                    <input v-model.number="form.height_cm" type="number" min="100" max="230" class="input-field" placeholder="170">
                                </div>
                                <div>
                                    <label class="label-text">Berat Badan (kg)</label>
                                    <input v-model.number="form.weight_kg" type="number" min="30" max="200" class="input-field" placeholder="65">
                                </div>
                            </div>

                            <div>
                                <label class="label-text">Riwayat Cedera Terkini</label>
                                <select v-model="form.injury_history" class="input-field cursor-pointer text-sm">
                                    <option value="none">Tidak Ada (Sehat & Bugar)</option>
                                    <option value="knee">Lutut (Runner's Knee / Patella)</option>
                                    <option value="hamstring">Hamstring / Paha Belakang</option>
                                    <option value="ankle">Pergelangan Kaki (Ankle / Tendon)</option>
                                    <option value="shin">Shin Splints / Tulang Kering</option>
                                    <option value="back">Punggung Bawah (Lower Back)</option>
                                </select>
                            </div>
                            
                            <div class="p-3.5 surface-nested rounded-md text-xs text-slate-300 flex flex-wrap items-center justify-between gap-2">
                                <span>Indeks Massa Tubuh: <strong class="text-white">@{{ bmi || '-' }}</strong> <span v-if="bmiCategory" :class="bmiCategory.color" class="font-semibold">(@{{ bmiCategory.label }})</span></span>
                                <span>Target Protein: <strong class="text-[#CCFF00]">@{{ proteinRecommendation ? (proteinRecommendation.min + '–' + proteinRecommendation.max + ' g/hari') : '-' }}</strong></span>
                            </div>
                        </div>

                        <!-- Right Side: Program Snapshot Review & CTA (6 cols) -->
                        <div class="lg:col-span-6 surface-card p-5 sm:p-6 rounded-lg border border-slate-800 space-y-5">
                            <div class="mb-4 pb-3 border-b border-slate-800 flex items-center justify-between">
                                <div>
                                    <h3 class="text-base font-bold text-white">Ringkasan Parameter Program</h3>
                                    <p class="text-xs text-slate-300 mt-0.5">Tinjau seluruh data sebelum membuat jadwal latihan</p>
                                </div>
                                <span class="text-[10px] font-bold text-slate-950 bg-[#CCFF00] px-2 py-0.5 rounded uppercase">
                                    Siap Generate
                                </span>
                            </div>

                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between p-3 surface-nested rounded-md text-xs">
                                    <span class="text-slate-400">Tolok Ukur / VDOT</span>
                                    <span class="font-bold text-white text-right">@{{ formatPbDisplay }} (VDOT @{{ current_vdot?.toFixed(1) || '-' }})</span>
                                </div>
                                <div class="flex items-center justify-between p-3 surface-nested rounded-md text-xs">
                                    <span class="text-slate-400">Target Lomba & Waktu</span>
                                    <span class="font-bold text-white text-right">@{{ form.target_distance.toUpperCase() }} (@{{ formatGoalTimeDisplay }})</span>
                                </div>
                                <div class="flex items-center justify-between p-3 surface-nested rounded-md text-xs">
                                    <span class="text-slate-400">Durasi Kalender</span>
                                    <span class="font-bold text-white text-right">@{{ calculatedDurationWeeks }} Pekan (@{{ form.start_date }} s/d @{{ form.target_date }})</span>
                                </div>
                                <div class="flex items-center justify-between p-3 surface-nested rounded-md text-xs">
                                    <span class="text-slate-400">Alokasi Latihan</span>
                                    <span class="font-bold text-white text-right">@{{ form.frequency }}x/minggu • Puncak @{{ form.weekly_mileage }} km</span>
                                </div>
                                <div class="p-3.5 surface-nested rounded-md text-xs space-y-3 border border-slate-700/40">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400 font-semibold uppercase tracking-wider text-[10px]">Status Kelayakan Program</span>
                                        <div class="flex items-center gap-2">
                                            <span class="font-black text-lg text-white">@{{ realism?.score || coachAssessment?.score || '-' }}<span class="text-[10px] font-semibold text-slate-500">/100</span></span>
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold border" :class="realism?.color || 'bg-emerald-900/20 text-emerald-300 border-emerald-500/30'">@{{ realism?.label || coachAssessment?.label || 'Realistis' }}</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             :class="{
                                                'bg-emerald-500': (realism?.feasibility || coachAssessment?.feasibility) === 'FEASIBLE',
                                                'bg-amber-500': (realism?.feasibility || coachAssessment?.feasibility) === 'AGGRESSIVE',
                                                'bg-orange-500': (realism?.feasibility || coachAssessment?.feasibility) === 'HIGH_RISK',
                                                'bg-red-500': (realism?.feasibility || coachAssessment?.feasibility) === 'INFEASIBLE'
                                             }"
                                             :style="{ width: ((realism?.score || coachAssessment?.score || 0) + '%') }"></div>
                                    </div>
                                    <p v-if="realism?.description || coachAssessment?.reason" class="text-slate-200 leading-relaxed">@{{ realism?.description || coachAssessment?.reason }}</p>
                                    <div v-if="realism?.options && realism.options.length" class="grid grid-cols-1 gap-1.5 pt-1">
                                        <button v-for="(opt, idx) in realism.options" :key="'rv-'+(opt.id||idx)"
                                                type="button" @click="applyChipOption(opt); wizardStep = 3;"
                                                class="text-left px-2.5 py-1.5 rounded border border-slate-700/60 bg-slate-800/50 text-slate-200 hover:bg-slate-800 hover:border-slate-600 transition cursor-pointer text-[11px]">
                                            <span class="inline-block w-1 h-1 rounded-full mr-2 align-middle bg-[#CCFF00]"></span>
                                            @{{ opt.label }} <span class="text-slate-500 text-[10px] ml-1">→ ke Step 3</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="pt-2 space-y-3">
                                <button @click="generateProgram" :disabled="loading" 
                                        class="btn-lime-primary w-full py-3.5 text-xs sm:text-sm tracking-wider uppercase font-extrabold disabled:opacity-50 disabled:cursor-not-allowed">
                                    <span v-if="!loading">Buat Program</span>
                                    <span v-else class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-[#090D15]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Memproses Perhitungan VDOT...
                                    </span>
                                </button>

                                <button type="button" @click="prevWizardStep" class="btn-ghost-secondary w-full py-2.5 text-xs font-semibold">
                                    ← Kembali ke Langkah Sebelumnya
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <div v-if="showFeasibilityModal" class="fixed inset-0 z-[9998] flex items-center justify-center px-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-black/75 backdrop-blur-sm" @click="showFeasibilityModal = false"></div>
            <div class="relative surface-card border border-red-500/40 rounded-xl max-w-md w-full shadow-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-800 bg-red-900/15">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-500/15 border border-red-500/40 text-red-300 font-black text-xl shrink-0">!</span>
                        <div>
                            <h3 class="text-white font-extrabold text-base">Target Tidak Realistis</h3>
                            <p class="text-xs text-slate-300 mt-0.5">Skor Kelayakan: @{{ feasibilityModalPayload.score }}/100</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-4 space-y-3 max-h-[55vh] overflow-y-auto">
                    <p class="text-xs text-slate-200 leading-relaxed">@{{ feasibilityModalPayload.reason }}</p>
                    <div v-if="feasibilityModalPayload.options && feasibilityModalPayload.options.length" class="space-y-2 pt-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Saran Pelatih:</span>
                        <button v-for="(opt, idx) in feasibilityModalPayload.options" :key="'mopt-'+idx"
                                type="button"
                                @click="applyChipOption(opt); showFeasibilityModal = false; setTimeout(() => generateProgram(), 120);"
                                class="w-full text-left px-3 py-2.5 rounded-md border border-slate-700/70 bg-slate-800/50 text-slate-200 hover:bg-slate-800 hover:border-[#CCFF00]/50 transition cursor-pointer text-xs">
                            <span class="inline-block w-1.5 h-1.5 rounded-full mr-2 align-middle bg-[#CCFF00]"></span>
                            <span class="font-semibold">@{{ opt.label }}</span>
                        </button>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-slate-800 flex flex-col sm:flex-row gap-2.5">
                    <button type="button"
                            @click="form.force_infeasible_ack = true; showFeasibilityModal = false; setTimeout(() => generateProgram(), 80);"
                            class="px-4 py-2.5 rounded-md border-2 border-red-500/40 bg-red-500/10 text-red-300 font-bold hover:bg-red-500/20 transition cursor-pointer text-xs flex-1">
                        Lanjut dengan Pemahaman Risiko
                    </button>
                    <button type="button"
                            @click="showFeasibilityModal = false;"
                            class="px-4 py-2.5 rounded-md btn-ghost-secondary font-bold text-xs flex-1">
                        Perbaiki Manual
                    </button>
                </div>
            </div>
        </div>

        <!-- STATE C: RESULTS DISPLAY (step === 2) -->
        <div v-if="step === 2" class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white">Program Latihan @{{ form.target_distance.toUpperCase() }} Selesai Dirancang</h2>
                    <p class="text-xs text-slate-300 mt-0.5">Estimasi skor VDOT @{{ result?.vdot }} • Durasi @{{ result?.weeks }} pekan • @{{ form.frequency }} sesi/minggu</p>
                </div>
                <button @click="step = 1; isWizardOpen = true" class="btn-ghost-secondary px-4 py-2 text-xs font-semibold self-start sm:self-auto">
                    Ubah Parameter
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Left: Summary Sidebar (4 cols) -->
                <div class="lg:col-span-4 space-y-5">
                    
                    <!-- Save Action Card -->
                    <div class="surface-card p-5 rounded-lg border border-slate-800">
                        <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-800">
                            <span class="text-xs text-slate-400">Skor Kebugaran VDOT</span>
                            <span class="text-2xl font-bold text-white">@{{ result?.vdot }}</span>
                        </div>
                        <div class="space-y-2 text-xs mb-4">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Target Jarak</span>
                                <span class="font-bold text-white uppercase">@{{ form.target_distance }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Durasi Persiapan</span>
                                <span class="font-bold text-white">@{{ result?.weeks }} Minggu</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Frekuensi Latihan</span>
                                <span class="font-bold text-white">@{{ form.frequency }} Hari / Minggu</span>
                            </div>
                        </div>

                        <button @click="saveAndOpenCalendar()" :disabled="saving" 
                                class="btn-lime-primary w-full py-3 text-xs uppercase tracking-wider font-extrabold mb-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span>@{{ saving ? 'Menyimpan...' : 'Simpan ke Kalender Lari' }}</span>
                        </button>
                        <button @click="step = 1; isWizardOpen = true" class="btn-ghost-secondary w-full py-2 text-xs font-semibold">
                            Ubah Parameter
                        </button>
                    </div>

                    <!-- Training Paces & HR Zones Card -->
                    <div class="surface-card p-5 rounded-lg border border-slate-800">
                        <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">Target Pace & Zona HR</h3>

                        <!-- Run-Walk Beginner Educational Notice -->
                        <div v-if="result?.paces?.is_run_walk" class="p-3 mb-3 rounded-md bg-teal-950/40 border border-teal-500/30 text-[11px] text-teal-200/90 leading-relaxed">
                            <div class="font-bold text-teal-300 mb-1">Metode Lari-Jalan (Run-Walk) Aktif</div>
                            Sesi lari santai Anda dikalibrasi ke ritme alami (<span class="text-white font-bold">Pace 8:00 - 8:30</span>) diselingi jalan cepat aktif (<span class="text-white font-bold">Pace 10:30 - 11:30</span>) untuk melindungi sendi dan menjaga detak jantung aerobik Zona 2.
                        </div>

                        <div class="space-y-2">
                            <div v-for="(pace, type) in displayPaces" :key="type" class="p-2.5 rounded-md surface-nested space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-xs uppercase" :class="getPaceColor(type)">
                                        @{{ getPaceLabel(type) }}
                                    </span>
                                    <span class="font-bold text-xs text-white">@{{ formatPace(pace, type) }}</span>
                                </div>
                                <div v-if="result?.hr_zones && result.hr_zones[type]" class="flex justify-between items-center text-[10px] text-slate-400 pt-1 border-t border-slate-800/50">
                                    <span>Target HR</span>
                                    <span class="text-slate-300">@{{ result.hr_zones[type].min }}–@{{ result.hr_zones[type].max }} BPM</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Athlete Profile & Nutrition Card -->
                    <div v-if="result?.bmi || result?.protein_recommendation || bmi || proteinRecommendation" class="surface-card p-5 rounded-lg border border-slate-800 space-y-3">
                        <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider pb-2 border-b border-slate-800">Profil & Nutrisi</h3>

                        <div v-if="result?.bmi || bmi" class="p-2.5 rounded-md surface-nested flex justify-between items-center">
                            <div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-wider">Indeks Massa Tubuh (BMI)</div>
                                <div class="text-base font-bold text-white">@{{ result?.bmi || bmi }} <span class="text-xs text-slate-400 font-normal">kg/m²</span></div>
                            </div>
                            <div v-if="bmiCategory" :class="bmiCategory.badgeClass + ' ' + bmiCategory.color" class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider">
                                @{{ bmiCategory.label }}
                            </div>
                        </div>

                        <div v-if="result?.protein_recommendation || proteinRecommendation" class="p-2.5 rounded-md surface-nested space-y-1">
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400">Target Protein Harian</span>
                                <span class="font-bold text-indigo-400">
                                    @{{ (result?.protein_recommendation || proteinRecommendation)?.min }}–@{{ (result?.protein_recommendation || proteinRecommendation)?.max }} g/hari
                                </span>
                            </div>
                            <p class="text-[10px] text-slate-400 italic leading-tight">
                                @{{ (result?.protein_recommendation || proteinRecommendation)?.note }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right: Weekly Schedule Preview (8 cols) -->
                <div class="lg:col-span-8 space-y-5">
                    <div v-for="(weekSessions, weekNum) in sessionsByWeek" :key="weekNum" class="surface-card p-5 rounded-lg border border-slate-800">
                        <div class="flex justify-between items-center mb-3 pb-2.5 border-b border-slate-800">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-white uppercase tracking-tight">
                                    Pekan @{{ weekNum }}
                                </h3>
                                <span v-if="weekSessions && weekSessions.length > 0 && weekSessions[0].is_deload" 
                                      class="px-2 py-0.5 bg-emerald-950/40 text-emerald-400 text-[10px] font-semibold rounded border border-emerald-500/30 uppercase tracking-wider">
                                    De-load / Pemulihan
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400">@{{ weekSessions.length }} Sesi</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-2">
                            <div v-for="day in weekSessions" :key="day.day" 
                                 class="p-2.5 rounded-md border min-h-[105px] flex flex-col justify-between transition border-slate-800"
                                 :class="getSessionClass(day.type)">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Hari @{{ day.day }}</span>
                                </div>
                                <div>
                                    <h4 class="text-[10px] font-bold text-white leading-tight mb-1 uppercase tracking-tight">@{{ day.workout_name || day.type.replace('_', ' ') }}</h4>
                                    <p class="text-xs font-bold text-white">@{{ day.distance }} <span class="text-[9px] font-normal text-slate-400">KM</span></p>
                                    <p v-if="day.target_pace" class="text-[9px] text-[#CCFF00] font-bold mt-0.5">@{{ day.target_pace }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- SECTION 3: CARA KERJA (3 Horizontal Steps, Clean Icons, Short Copy) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-800/80">
        <div class="space-y-8">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Cara Kerja Generator Program Lari</h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">Tiga langkah ilmiah menyusun program latihan terukur yang selaras dengan kapasitas riil Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <div class="surface-card p-6 rounded-lg border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-md bg-[#162035] text-[#CCFF00] font-bold text-sm flex items-center justify-center border border-slate-700">
                            01
                        </div>
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Masukkan Waktu PB Terkini</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Gunakan catatan waktu terbaik 3–6 bulan terakhir pada jarak 5K hingga Marathon, atau hasil uji waktu Cooper 12 menit.
                    </p>
                </div>

                <div class="surface-card p-6 rounded-lg border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-md bg-[#162035] text-[#CCFF00] font-bold text-sm flex items-center justify-center border border-slate-700">
                            02
                        </div>
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Tentukan Target & Kalender</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Pilih jarak sasaran lomba, tanggal race, dan frekuensi latihan mingguan yang realistis dengan rutinitas aktivitas harian Anda.
                    </p>
                </div>

                <div class="surface-card p-6 rounded-lg border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-8 h-8 rounded-md bg-[#162035] text-[#CCFF00] font-bold text-sm flex items-center justify-center border border-slate-700">
                            03
                        </div>
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-white">Dapatkan Jadwal & Pace Presisi</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Sistem menyusun periodisasi harian lengkap dengan 5 zona pace Jack Daniels yang siap disinkronkan ke kalender lari pribadi Anda.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION 4: TARGET LOMBA (4 Cards: 5K, 10K, HM, FM, 1 Sentence + Duration) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-800/80">
        <div class="space-y-8">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Program Latihan Berdasarkan Target Lomba</h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">Pilih periodisasi yang dirancang spesifik untuk tuntutan energi masing-masing jarak.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- 5K -->
                <div class="surface-card p-5 rounded-lg border border-slate-800 flex flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-white">Program 5K</h3>
                            <span class="text-[10px] font-bold text-slate-300 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">8–10 Pekan</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Membangun kecepatan dasar, kapasitas pompa VO2 Max, dan efisiensi biomekanik gerak aerobik.
                        </p>
                    </div>
                    <div class="pt-2 text-xs">
                        <a href="{{ route('landing.program-lari-5k') }}" class="text-[#CCFF00] hover:underline font-semibold">Pelajari Program 5K →</a>
                    </div>
                </div>

                <!-- 10K -->
                <div class="surface-card p-5 rounded-lg border border-slate-800 flex flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-white">Program 10K</h3>
                            <span class="text-[10px] font-bold text-slate-300 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">10–12 Pekan</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Mengembangkan ketahanan ambang laktat (threshold) untuk mempertahankan kecepatan kompetitif lebih lama.
                        </p>
                    </div>
                    <div class="pt-2 text-xs">
                        <a href="{{ route('landing.program-lari-10k') }}" class="text-[#CCFF00] hover:underline font-semibold">Pelajari Program 10K →</a>
                    </div>
                </div>

                <!-- Half Marathon -->
                <div class="surface-card p-5 rounded-lg border border-slate-800 flex flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-white">Half Marathon</h3>
                            <span class="text-[10px] font-bold text-slate-300 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">12–14 Pekan</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Melatih efisiensi metabolisme lemak dan ketahanan muskuloskeletal menuntaskan 21.1 km tanpa drop stamina.
                        </p>
                    </div>
                    <div class="pt-2 text-xs">
                        <a href="{{ route('programs.index') }}" class="text-[#CCFF00] hover:underline font-semibold">Katalog Half Marathon →</a>
                    </div>
                </div>

                <!-- Full Marathon -->
                <div class="surface-card p-5 rounded-lg border border-slate-800 flex flex-col justify-between space-y-3">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-white">Full Marathon</h3>
                            <span class="text-[10px] font-bold text-slate-300 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">16–20 Pekan</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Fokus pada adaptasi cadangan glikogen, nutrisi gel saat lari, dan ketahanan mental 42.2 km.
                        </p>
                    </div>
                    <div class="pt-2 text-xs">
                        <a href="{{ route('programs.index') }}" class="text-[#CCFF00] hover:underline font-semibold">Katalog Marathon →</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION 5: DASAR ILMIAH VDOT (Collapsible Accordion Table, 5 Rows) -->
    <section id="dasar-vdot" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-800/80">
        <div class="space-y-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Dasar Ilmiah: Formula VDOT Jack Daniels</h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-3xl leading-relaxed">
                    Formula VDOT karya Dr. Jack Daniels menyelaraskan konsumsi oksigen maksimal (VO2 Max) dengan efisiensi mekanik lari (Running Economy). Setiap sesi latihan memiliki zona pace spesifik agar memicu adaptasi fisiologis tepat sasaran tanpa memicu overtraining.
                </p>
            </div>

            <!-- Collapsible Accordion for Pace Zones -->
            <details class="faq-item surface-card rounded-lg border border-slate-800 transition">
                <summary class="p-4 sm:p-5 flex items-center justify-between cursor-pointer select-none">
                    <span class="text-xs sm:text-sm font-bold text-white">
                        Lihat Rincian 5 Zona Pace Ilmiah (Easy, Marathon, Threshold, Interval, Repetition)
                    </span>
                    <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>

                <div class="p-4 sm:p-5 pt-0 border-t border-slate-800">
                    <div class="overflow-x-auto rounded-md border border-slate-800 mt-3">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[#162035] border-b border-slate-800 text-slate-400 uppercase font-semibold">
                                <tr>
                                    <th class="py-2.5 px-3">Zona Pace</th>
                                    <th class="py-2.5 px-3">% VO2 Max</th>
                                    <th class="py-2.5 px-3">% HR Max</th>
                                    <th class="py-2.5 px-3">Tujuan & Stimulus</th>
                                    <th class="py-2.5 px-3">Contoh Sesi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800 text-slate-300">
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-emerald-400">Easy (E)</td>
                                    <td class="py-2.5 px-3">59% - 74%</td>
                                    <td class="py-2.5 px-3">65% - 78%</td>
                                    <td class="py-2.5 px-3">Kapilarisasi otot, mitokondria, pemulihan aktif.</td>
                                    <td class="py-2.5 px-3 text-slate-400">Easy Run, Long Run Dasar</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-blue-400">Marathon (M)</td>
                                    <td class="py-2.5 px-3">75% - 84%</td>
                                    <td class="py-2.5 px-3">79% - 88%</td>
                                    <td class="py-2.5 px-3">Efisiensi glikogen pada kecepatan race target.</td>
                                    <td class="py-2.5 px-3 text-slate-400">Long Run Spesifik</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-amber-400">Threshold (T)</td>
                                    <td class="py-2.5 px-3">83% - 88%</td>
                                    <td class="py-2.5 px-3">88% - 92%</td>
                                    <td class="py-2.5 px-3">Pembersihan laktat darah (Lactate Clearance).</td>
                                    <td class="py-2.5 px-3 text-slate-400">Tempo Run, Cruise Interval</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-rose-400">Interval (I)</td>
                                    <td class="py-2.5 px-3">95% - 100%</td>
                                    <td class="py-2.5 px-3">95% - 100%</td>
                                    <td class="py-2.5 px-3">VO2 Max puncak dan kekuatan pompa stroke jantung.</td>
                                    <td class="py-2.5 px-3 text-slate-400">Repeats 800m–1200m</td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 px-3 font-bold text-[#FC4C02]">Repetition (R)</td>
                                    <td class="py-2.5 px-3">> 105%</td>
                                    <td class="py-2.5 px-3">Anaerobik</td>
                                    <td class="py-2.5 px-3">Kecepatan neuromuskular dan irama cadence tinggi.</td>
                                    <td class="py-2.5 px-3 text-slate-400">Repeats 200m–400m</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>
        </div>
    </section>

    <!-- SECTION 6: METODE RUN-WALK (1 Compact Block + Link, No Essay) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="surface-card p-6 rounded-lg border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
            <div class="space-y-1.5 max-w-3xl">
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-bold text-white">Metode Lari-Jalan (Run-Walk) untuk Pelari Pemula</h3>
                    <span class="text-[10px] font-bold text-teal-400 bg-teal-950/40 px-2 py-0.5 rounded border border-teal-500/30 uppercase">Protektif</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Bagi pelari dengan VDOT di bawah 32, sistem otomatis mengaktifkan interval lari santai (pace 08:00–08:30 /km) diselingi jalan cepat aktif (pace 10:30–11:30 /km). Metode ini menjaga detak jantung stabil di Zona 2 dan melindungi persendian lutut serta tulang kering.
                </p>
            </div>
            <a href="{{ route('landing.program-lari-5k-pemula') }}" class="btn-ghost-secondary px-4 py-2.5 text-xs whitespace-nowrap shrink-0">
                Panduan Run-Walk →
            </a>
        </div>
    </section>

    <!-- SECTION 7: FAQ (6 Questions, Tight Accordion) -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-800/80">
        <div class="space-y-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">Jawaban seputar metodologi kalkulator VDOT dan sinkronisasi kalender latihan.</p>
            </div>

            <div class="space-y-2.5">
                
                <details class="faq-item surface-card p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-xs sm:text-sm text-white select-none">
                        <span>Bagaimana cara kerja kalkulator VDOT dalam membuat program latihan lari?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2.5 pt-2.5 border-t border-slate-800">
                        Kalkulator VDOT mengukur tingkat kebugaran aerobik (VO2 Max fungsional) berdasarkan catatan waktu terbaik (Personal Best) terkini pada jarak standar (5K, 10K, Half Marathon, Full Marathon, atau tes waktu Cooper/Balke). Dari nilai VDOT ini, sistem menghitung 5 intensitas pace spesifik (Easy, Marathon, Threshold, Interval, Repetition) sesuai metodologi ilmiah Dr. Jack Daniels untuk menstimulasi adaptasi kardiovaskular secara optimal tanpa risiko overtraining.
                    </p>
                </details>

                <details class="faq-item surface-card p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-xs sm:text-sm text-white select-none">
                        <span>Berapa lama durasi persiapan ideal untuk 5K, 10K, Half Marathon, dan Full Marathon?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2.5 pt-2.5 border-t border-slate-800">
                        Durasi persiapan terstruktur yang direkomendasikan adalah 8–10 pekan untuk 5K, 10–12 pekan untuk 10K, 12–14 pekan untuk Half Marathon (21.1K), dan 16–20 pekan untuk Full Marathon (42.2K). Rentang waktu ini memastikan adaptasi tendon, ligamen, dan kapasitas simpanan glikogen otot terbangun secara bertahap dengan fase de-load dan tapering menjelang hari perlombaan.
                    </p>
                </details>

                <details class="faq-item surface-card p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-xs sm:text-sm text-white select-none">
                        <span>Apakah program latihan ini aman untuk pelari pemula yang baru mulai lari?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2.5 pt-2.5 border-t border-slate-800">
                        Sangat aman. Untuk pelari pemula dengan tingkat kebugaran awal (VDOT di bawah 32), sistem secara otomatis mengaktifkan Metode Run-Walk (Lari-Jalan Berirama). Pelari tidak dipaksa berlari lambat terus-menerus pada pace 11–13 min/km yang berisiko merusak postur, melainkan berlari pada ritme alami (08:00 - 08:30 /km) diselingi interval jalan cepat aktif untuk menjaga detak jantung aerobik Zona 2 dan melindungi sendi lutut serta tulang kering.
                    </p>
                </details>

                <details class="faq-item surface-card p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-xs sm:text-sm text-white select-none">
                        <span>Mengapa ada opsi penyesuaian iklim tropis Indonesia?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2.5 pt-2.5 border-t border-slate-800">
                        Berlari di lingkungan tropis dengan suhu tinggi dan kelembapan di atas 75% memicu kenaikan detak jantung lebih cepat (cardiac drift) akibat beban termoregulasi tubuh. Fitur adaptasi tropis melonggarkan target pace sebesar 10–15 detik/km agar beban fisiologis pada sistem kardiovaskular tetap sesuai dengan tujuan latihan tanpa memicu kelelahan ekstrem.
                    </p>
                </details>

                <details class="faq-item surface-card p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-xs sm:text-sm text-white select-none">
                        <span>Bagaimana cara menyimpan dan menyinkronkan program ke kalender lari?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2.5 pt-2.5 border-t border-slate-800">
                        Setelah menekan tombol 'Buat Program Latihan' dan meninjau hasil kalkulasi, klik tombol 'Simpan ke Kalender Lari'. Jika Anda telah masuk (login), seluruh jadwal latihan harian—termasuk jarak, target pace, dan jenis sesi—akan tersinkronisasi otomatis ke dashboard Kalender Lari Anda.
                    </p>
                </details>

                <details class="faq-item surface-card p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-xs sm:text-sm text-white select-none">
                        <span>Apakah generator program lari ini 100% gratis digunakan?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-2.5 pt-2.5 border-t border-slate-800">
                        Ya, Anda dapat buat program lari secara 100% gratis tanpa biaya langganan. Fitur ini mencakup penentuan target pace VDOT, periodisasi jadwal latihan harian (5K, 10K, Half Marathon, Full Marathon), panduan nutrisi protein, hingga sinkronisasi langsung ke kalender lari pribadi Anda.
                    </p>
                </details>

            </div>
        </div>
    </section>

    <!-- SECTION 8: ECOSYSTEM NAVIGATION -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 border-t border-slate-800/80">
        <div class="surface-card p-6 rounded-lg border border-slate-800 space-y-4">
            <h2 class="text-sm font-bold text-white uppercase tracking-wider">Jelajahi Ekosistem Lari RuangLari</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 text-xs">
                <a href="{{ route('landing.program-lari-5k') }}" class="p-3 surface-nested rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    Program 5K
                </a>
                <a href="{{ route('landing.program-lari-5k-pemula') }}" class="p-3 surface-nested rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    5K Pemula
                </a>
                <a href="{{ route('landing.program-lari-10k') }}" class="p-3 surface-nested rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    Program 10K
                </a>
                <a href="{{ route('tools.index') }}" class="p-3 surface-nested rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    Running Tools
                </a>
                <a href="{{ route('gpx.index') }}" class="p-3 surface-nested rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    Database GPX
                </a>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
    const { createApp, ref, reactive, computed, onMounted, watch } = Vue;

    createApp({
        setup() {
            const step = ref(1);
            const isWizardOpen = ref(false);
            const wizardStep = ref(1);
            const lastSavedTime = ref('');
            const loading = ref(false);
            const saving = ref(false);
            const result = ref(null);
            const errors = ref(null);
            const notification = ref(null);

            // PB time inputs initialize as null/empty without fake dummy 0:30:0
            const pb_hours = ref(null);
            const pb_minutes = ref(null);
            const pb_seconds = ref(null);
            const pb_distance_meters = ref(null);

            const goal_hours = ref(null);
            const goal_minutes = ref(null);
            const goal_seconds = ref(null);

            const todayStr = new Date().toISOString().split('T')[0];

            const conflictModal = reactive({
                show: false,
                activeTitle: '',
                activeStartDate: '',
                activeEndDate: ''
            });

            const form = reactive({
                pb_distance: '5k',
                pb_time: '',
                target_distance: '10k',
                start_date: todayStr,
                target_date: '',
                goal_time: '',
                weekly_mileage: 45,
                frequency: 4,
                gender: 'male',
                age: 25,
                height_cm: 170,
                weight_kg: 65,
                injury_history: 'none',
                include_strength: true,
                strength_type: 'bodyweight',
                runner_level: 'intermediate',
                long_run_day: 'sunday',
                is_tropical: false
            });

            const openWizard = () => {
                isWizardOpen.value = true;
                setTimeout(() => {
                    const el = document.getElementById('wizard-container');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 80);
            };

            const closeWizard = () => {
                isWizardOpen.value = false;
                setTimeout(() => {
                    const el = document.getElementById('tool-container');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 80);
            };

            const showNotification = (message, type = 'success') => {
                notification.value = { message, type };
                setTimeout(() => {
                    notification.value = null;
                }, 5000);
            };

            const distanceMeters = {
                '5k': 5000,
                '10k': 10000,
                '21k': 21097.5,
                '42k': 42195,
                'cooper12': 2400,
                'balke15': 3000
            };

            const distanceKm = {
                '5k': 5.0,
                '10k': 10.0,
                '21k': 21.0975,
                '42k': 42.195,
                'cooper12': 2.4,
                'balke15': 3.0,
            };

            const COACH_MILEAGE_MATRIX = {
                '5k': {
                    beginner:     { min: 15, ideal: 22, max: 30, min_weeks: 8,  min_freq: 3 },
                    intermediate: { min: 22, ideal: 32, max: 45, min_weeks: 8,  min_freq: 4 },
                    advanced:     { min: 30, ideal: 42, max: 60, min_weeks: 8,  min_freq: 5 },
                },
                '10k': {
                    beginner:     { min: 18, ideal: 26, max: 35, min_weeks: 10, min_freq: 3 },
                    intermediate: { min: 28, ideal: 40, max: 55, min_weeks: 10, min_freq: 4 },
                    advanced:     { min: 42, ideal: 55, max: 75, min_weeks: 10, min_freq: 5 },
                },
                '21k': {
                    beginner:     { min: 22, ideal: 34, max: 48, min_weeks: 12, min_freq: 4 },
                    intermediate: { min: 35, ideal: 48, max: 68, min_weeks: 14, min_freq: 4 },
                    advanced:     { min: 50, ideal: 68, max: 95, min_weeks: 14, min_freq: 5 },
                },
                '42k': {
                    beginner:     { min: 30, ideal: 45, max: 65, min_weeks: 18, min_freq: 4 },
                    intermediate: { min: 50, ideal: 70, max: 95, min_weeks: 16, min_freq: 5 },
                    advanced:     { min: 70, ideal: 90, max: 120, min_weeks: 16, min_freq: 5 },
                },
                'cooper12': {
                    beginner:     { min: 15, ideal: 22, max: 30, min_weeks: 8, min_freq: 3 },
                    intermediate: { min: 22, ideal: 32, max: 45, min_weeks: 8, min_freq: 4 },
                    advanced:     { min: 30, ideal: 42, max: 60, min_weeks: 8, min_freq: 5 },
                },
                'balke15': {
                    beginner:     { min: 15, ideal: 22, max: 30, min_weeks: 8, min_freq: 3 },
                    intermediate: { min: 22, ideal: 32, max: 45, min_weeks: 8, min_freq: 4 },
                    advanced:     { min: 30, ideal: 42, max: 60, min_weeks: 8, min_freq: 5 },
                },
            };

            const COACH_VDOT_RATE = {
                beginner: 0.4,
                intermediate: 0.5,
                advanced: 0.6,
            };

            const COACH_AGG_MULT = {
                conservative: { mileage: 0.92, weeks: 1.15 },
                standard:     { mileage: 1.00, weeks: 1.00 },
                sharp:        { mileage: 1.10, weeks: 0.90 },
            };

            const formAggressiveness = ref('standard');
            const highlightMileage = ref(false);
            const showFeasibilityModal = ref(false);
            const feasibilityModalPayload = reactive({
                score: 100,
                reason: '',
                options: [],
                applyIdeal: () => {},
            });
            // #region hero-vdot-calculator: Interactive VDOT Performance Lab (mirip /programs)
            const heroVdotTab = ref('paces'); // 'paces' | 'races'
            const heroVdotDistance = ref('21k'); // default Half Marathon match /programs
            const heroVdotH = ref(1);
            const heroVdotM = ref(55);
            const heroVdotS = ref(0);
            const heroVdotCalculated = ref(false);
            const heroVdotCompute = reactive({
                vdot: 0,
                pbSec: 0,
                // pace zones (min/km): each value = pace min per km (float 5.83 etc)
                easy_low: 0, easy_high: 0,
                marathon: 0,
                tempo: 0, threshold: 0,
                interval: 0, interval_400: 0,
                repetition: 0, repetition_400: 0,
                // race predictions
                r_5k: 0, r_10k: 0, r_21k: 0, r_42k: 0,
            });
            const heroVdotLevel = computed(() => {
                const v = typeof heroVdotCompute !== 'undefined' ? (heroVdotCompute.vdot || 0) : 0;
                if (v >= 60) return 'Elite / Advanced';
                if (v >= 48) return 'Advanced';
                if (v >= 38) return 'Intermediate';
                if (v >= 28) return 'Beginner Plus';
                return 'Beginner';
            });
            const heroFormatPace = (minPerKm) => {
                if (!minPerKm || minPerKm <= 0) return '-';
                const sec = Math.max(0, Math.round(minPerKm * 60));
                const mm = Math.floor(sec / 60);
                const ss = sec % 60;
                return `${String(mm).padStart(2, '0')}:${String(ss).padStart(2, '0')}/km`;
            };
            const heroFormatDur = (sec) => {
                sec = Math.max(0, Math.round(Number(sec) || 0));
                if (!sec) return '-';
                const h = Math.floor(sec / 3600);
                const m = Math.floor((sec % 3600) / 60);
                const s = sec % 60;
                if (h > 0) return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            };
            // NOTE: heroCalculateVdot() & heroApplyToWizard() dipindahkan SETELAH helper distanceMeters / getRatioForDistance / vvo2FromVDOT / calculateVDOTFromPerformance / predictRaceTimeSeconds
            //       di bawah (setelah L~2400) untuk menghindari Temporal Dead Zone (ES6 const TDZ).
            // #endregion
            // #region debug-point vdot-target-clamp-45min [F1] userEditedGoal guard + debug counter prevent overwrite
            const userEditedGoal = ref(false);
            const userEditedPb = ref(false);
            const _debugSuggestOverwrites = ref(0);
            const _debugGoalTimeline = reactive([]);
            const _dbgPushGoal = (src, note = '') => {
                try {
                    const cv = typeof current_vdot !== 'undefined' ? (current_vdot.value || 0) : 0;
                    const tv = typeof recommendedTargetVdot !== 'undefined' ? (recommendedTargetVdot.value || 0) : 0;
                    _debugGoalTimeline.push({
                        at: Date.now(),
                        src,
                        note,
                        g_h: typeof goal_hours !== 'undefined' ? goal_hours.value : null,
                        g_m: typeof goal_minutes !== 'undefined' ? goal_minutes.value : null,
                        g_s: typeof goal_seconds !== 'undefined' ? goal_seconds.value : null,
                        cv: Math.round(cv * 100) / 100,
                        tv: Math.round(tv * 100) / 100,
                        user_edited_goal: typeof userEditedGoal !== 'undefined' ? userEditedGoal.value : false,
                    });
                    if (_debugGoalTimeline.length > 80) _debugGoalTimeline.splice(0, _debugGoalTimeline.length - 80);
                } catch (e) {}
            };
            watch([goal_hours, goal_minutes, goal_seconds], ([nh, nm, ns], [oh, om, os]) => {
                if (nh !== oh || nm !== om || ns !== os) {
                    userEditedGoal.value = true;
                    _dbgPushGoal('user_manual_edit_goal', `old=${oh}:${om}:${os} new=${nh}:${nm}:${ns}`);
                }
            }, { flush: 'sync' });
            // #endregion

            const formatDurationSec = (totalSec) => {
                totalSec = Math.max(0, Math.round(Number(totalSec) || 0));
                const h = Math.floor(totalSec / 3600);
                const m = Math.floor((totalSec % 3600) / 60);
                const s = totalSec % 60;
                if (h > 0) return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
                return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            };

            const goalSecondsTotal = computed(() => {
                const h = Number(goal_hours.value) || 0;
                const m = Number(goal_minutes.value) || 0;
                const s = Number(goal_seconds.value) || 0;
                return (h * 3600) + (m * 60) + s;
            });

            const computeCoachAssessment = (inputsOverride = {}) => {
                const dist = inputsOverride.target_distance ?? form.target_distance ?? '10k';
                const level = inputsOverride.runner_level ?? form.runner_level ?? 'intermediate';
                const goalSec = Number(inputsOverride.goal_time_sec ?? goalSecondsTotal.value) || 0;
                const weeklyMileage = Number(inputsOverride.weekly_mileage ?? form.weekly_mileage ?? 0);
                const freq = Number(inputsOverride.frequency ?? form.frequency ?? 4);
                const weeks = Number(inputsOverride.weeks ?? weeksUntilRace.value) || 12;
                const initVdot = Number(inputsOverride.initial_vdot ?? current_vdot.value) || 0;
                const targVdot = Number(inputsOverride.target_vdot ?? target_vdot.value) || 0;
                const injury = inputsOverride.injury_history ?? form.injury_history ?? 'none';
                let agg = inputsOverride.aggressiveness ?? formAggressiveness.value ?? 'standard';
                if (!COACH_AGG_MULT[agg]) agg = 'standard';

                const base = COACH_MILEAGE_MATRIX[dist]?.[level] ?? COACH_MILEAGE_MATRIX['10k'].intermediate;
                let aggMult = COACH_AGG_MULT[agg];

                if (injury !== 'none') {
                    agg = (agg === 'sharp') ? 'standard' : (agg === 'standard' ? 'conservative' : 'conservative');
                    aggMult = COACH_AGG_MULT[agg];
                }

                let minMileage = Number(base.min);
                let idealMileage = Math.round(base.ideal * aggMult.mileage);
                const maxMileage = Number(base.max);
                let minWeeks = Math.ceil(base.min_weeks * aggMult.weeks);
                let minFreq = Number(base.min_freq);

                const distKm = distanceKm[dist] || 10;
                const goalPaceSecPerKm = goalSec > 0 ? (goalSec / distKm) : 0;

                if (dist === '10k' && goalSec > 0 && goalPaceSecPerKm <= 222) {
                    if (level === 'advanced') {
                        minMileage = Math.max(minMileage, 42);
                        idealMileage = agg === 'sharp' ? Math.max(idealMileage, 56) : Math.max(idealMileage, 52);
                    } else {
                        minMileage = Math.max(minMileage, (level === 'intermediate' ? 45 : 48));
                        idealMileage = agg === 'sharp' ? Math.max(idealMileage, 55) : Math.max(idealMileage, 50);
                    }
                    minFreq = Math.max(minFreq, 5);
                    minWeeks = Math.max(minWeeks, 10);
                }

                if (injury !== 'none') {
                    minWeeks += 2;
                    idealMileage = Math.round(idealMileage * 0.95);
                }

                const vdotRate = COACH_VDOT_RATE[level] ?? 0.45;
                const tv = targVdot > 0 ? targVdot : initVdot;
                const deltaVdot = Math.max(0, tv - initVdot);
                const requiredWeeksByVdot = vdotRate > 0 ? Math.ceil(deltaVdot / vdotRate) : 0;
                const finalRequiredWeeksByVdot = dist === '42k' ? Math.max(requiredWeeksByVdot, Math.ceil(minWeeks * 0.85)) : requiredWeeksByVdot;

                const violations = [];
                let score = 100;

                if (weeklyMileage > 0) {
                    if (weeklyMileage < minMileage) {
                        violations.push(`Beban latihan saat ini (${weeklyMileage} km/minggu) di bawah minimum fisiologis (${minMileage} km) untuk target ini.`);
                        score -= 35;
                    } else if (weeklyMileage > maxMileage) {
                        violations.push(`Beban latihan (${weeklyMileage} km) melebihi batas aman (${maxMileage} km) untuk level ${level}.`);
                        score -= 30;
                    } else if (weeklyMileage > idealMileage * 1.04) {
                        score -= 8;
                        violations.push('Beban latihan mendekati batas atas aman; pastikan recovery cukup.');
                    }
                }

                if (freq < minFreq) {
                    violations.push(`Frekuensi latih (${freq} hari/minggu) kurang dari minimum (${minFreq} hari) untuk target ini.`);
                    score -= 18;
                }
                if (weeks < minWeeks) {
                    violations.push(`Timeline persiapan (${weeks} pekan) kurang dari minimum (${minWeeks} pekan) untuk jarak target ini.`);
                    score -= 30;
                }
                if (finalRequiredWeeksByVdot > 0 && weeks < finalRequiredWeeksByVdot) {
                    const pct = initVdot > 0 ? Math.round((deltaVdot / initVdot) * 1000) / 10 : 0;
                    violations.push(`Peningkatan VDOT +${pct}% (${initVdot} → ${tv}) butuh ${finalRequiredWeeksByVdot} pekan dengan rate ${vdotRate}/minggu; ${weeks} pekan tersedia tidak cukup.`);
                    score -= 32;
                }

                score = Math.max(0, Math.min(100, Math.round(score)));

                let feasibility = 'FEASIBLE';
                let color = 'emerald';
                let label = 'Realistis';

                if (weeklyMileage > 0 && weeklyMileage < minMileage * 0.85) {
                    feasibility = 'INFEASIBLE'; color = 'red'; label = 'Tidak Realistis';
                } else if (finalRequiredWeeksByVdot > 0 && weeks < finalRequiredWeeksByVdot * 0.8) {
                    feasibility = 'INFEASIBLE'; color = 'red'; label = 'Tidak Realistis';
                } else if (score >= 70) { feasibility = 'FEASIBLE'; color = 'emerald'; label = 'Realistis'; }
                else if (score >= 40) { feasibility = 'AGGRESSIVE'; color = 'amber'; label = 'Agresif'; }
                else if (score >= 20) { feasibility = 'HIGH_RISK'; color = 'orange'; label = 'Risiko Tinggi'; }
                else { feasibility = 'INFEASIBLE'; color = 'red'; label = 'Tidak Realistis'; }

                const paceStr = goalSec > 0 ? formatDurationSec(Math.round(goalPaceSecPerKm)) : '—';
                const goalTimeStr = goalSec > 0 ? formatDurationSec(goalSec) : '—';
                const distLabel = String(dist || '10k').toUpperCase();

                const pctVdot = initVdot > 0 ? Math.round((deltaVdot / initVdot) * 1000) / 10 : 0;

                let reason = '';
                if (dist === '10k' && minMileage >= 42) {
                    reason = `Target ${distLabel} ${goalTimeStr} (${paceStr}/km termasuk kategori cepat) menuntut stimulus aerobik + adaptasi ambang laktat yang tidak dapat dicapai hanya dengan 20–30 km/minggu. Minimum puncak mingguan = ${minMileage} km dengan frekuensi latih minimal 5 hari/minggu, idealnya ${idealMileage} km selama minimal ${minWeeks} pekan. `;
                } else {
                    reason = `Target ${distLabel} ${goalTimeStr} untuk level ${level} membutuhkan puncak beban ${minMileage}–${idealMileage} km/minggu. `;
                }
                if (weeklyMileage > 0) {
                    const ratio = minMileage > 0 ? Math.round((weeklyMileage / minMileage) * 100) : 0;
                    reason += `Beban Anda saat ini = ${weeklyMileage} km (${ratio}% dari minimum fisiologis). `;
                }
                if (deltaVdot > 0) {
                    reason += `Peningkatan VDOT ${initVdot} → ${tv} (+${pctVdot}%) pada rate ${vdotRate}/minggu membutuhkan minimal ${finalRequiredWeeksByVdot} pekan; timeline Anda ${weeks} pekan. `;
                    if (finalRequiredWeeksByVdot > weeks) {
                        reason += 'Risiko: beban stimulasi per pekan melebihi ambang adaptasi aman → risiko cedera > 60% (ITBS, shin splint, overload jantung). ';
                    }
                }
                if (feasibility === 'FEASIBLE' || feasibility === 'AGGRESSIVE') {
                    reason += 'Tetap menerapkan aturan 10% peningkatan mingguan + deload 20% setiap 4 minggu untuk menjaga keamanan. ';
                }
                if (violations.length) {
                    reason += 'Hal yang perlu diperbaiki: ' + violations.join(' ') + ' ';
                }
                reason = reason.trim();

                const options = [];
                const needsAdjust = ['AGGRESSIVE','HIGH_RISK','INFEASIBLE'].includes(feasibility);

                if (weeklyMileage < minMileage || needsAdjust) {
                    options.push({
                        id: 'apply_mileage',
                        label: 'Terapkan saran beban: ' + Math.round(idealMileage) + ' km peak',
                        apply: { weekly_mileage: Math.round(idealMileage) }
                    });
                }
                if (weeks < minWeeks || (finalRequiredWeeksByVdot > 0 && weeks < finalRequiredWeeksByVdot)) {
                    const targetWeeks = Math.max(minWeeks, finalRequiredWeeksByVdot + 2);
                    options.push({
                        id: 'extend_weeks',
                        label: 'Perpanjang timeline ke ' + targetWeeks + ' pekan',
                        apply: { extend_weeks: targetWeeks }
                    });
                }
                if (goalSec > 0) {
                    const goalMin = Math.floor(goalSec / 60);
                    if (dist === '10k' && goalMin <= 37) {
                        options.push({
                            id: 'ease_goal_42',
                            label: 'Ubah target finish menjadi 00:42:00 (4:12/km)',
                            apply: { goal_time_sec: 42 * 60 }
                        });
                    }
                    if (dist === '10k' && goalMin <= 40 && goalMin > 37) {
                        options.push({
                            id: 'ease_goal_45',
                            label: 'Ubah target finish menjadi 00:45:00 (4:30/km)',
                            apply: { goal_time_sec: 45 * 60 }
                        });
                    }
                    if (dist === '42k' && minWeeks > 16) {
                        options.push({
                            id: 'ease_goal_fm345',
                            label: 'Turunkan target Marathon menjadi 03:45:00 (5:20/km)',
                            apply: { goal_time_sec: (3 * 3600) + (45 * 60) }
                        });
                    }
                    if (dist === '21k' && minWeeks > 14) {
                        options.push({
                            id: 'ease_goal_hm145',
                            label: 'Turunkan target Half-Marathon menjadi 01:45:00 (4:58/km)',
                            apply: { goal_time_sec: (1 * 3600) + (45 * 60) }
                        });
                    }
                }
                if (weeklyMileage >= idealMileage && weeks >= minWeeks) {
                    options.push({
                        id: 'conservative_mode',
                        label: 'Pilih mode konservatif (beban -10%) untuk mengurangi risiko cedera',
                        apply: { aggressiveness: 'conservative' }
                    });
                }

                return {
                    feasibility, score,
                    min_required_peak_mileage: Math.round(minMileage),
                    ideal_peak_mileage: Math.round(idealMileage),
                    max_safe_peak_mileage: Math.round(maxMileage),
                    min_weeks: minWeeks,
                    max_weeks: Math.ceil(minWeeks * 2.2),
                    min_frequency: minFreq,
                    color, label, reason, options,
                    vdot_rate_per_week: vdotRate,
                    required_weeks_by_vdot: finalRequiredWeeksByVdot,
                    violations,
                    goal_pace_str: paceStr,
                    goal_time_str: goalTimeStr,
                };
            };

            const coachAssessment = computed(() => computeCoachAssessment());
            const idealMileage = computed(() => {
                const base = coachAssessment.value.ideal_peak_mileage || 45;
                const rounded = Math.round(base / 5) * 5;
                return Math.min(120, Math.max(20, rounded));
            });
            const minMileageDynamic = computed(() => Math.max(10, Math.floor((coachAssessment.value.min_required_peak_mileage || 15) - 1)));

            const applyChipOption = (opt) => {
                if (!opt || !opt.apply) return;
                const ovr = opt.apply || {};
                if (Number.isFinite(ovr.weekly_mileage)) {
                    form.weekly_mileage = Math.round(Number(ovr.weekly_mileage));
                }
                if (Number.isFinite(ovr.goal_time_sec)) {
                    const sec = Math.max(0, Math.round(Number(ovr.goal_time_sec)));
                    userEditedGoal.value = false; // clear flag karena user explicitly accept saran 1-klik
                    goal_hours.value = Math.floor(sec / 3600);
                    goal_minutes.value = Math.floor((sec % 3600) / 60);
                    goal_seconds.value = sec % 60;
                    _dbgPushGoal('applyChipOption_goal', `applied_sec=${sec} hms=${goal_hours.value}:${goal_minutes.value}:${goal_seconds.value}`);
                }
                if (Number.isFinite(ovr.extend_weeks) && form.start_date) {
                    const start = new Date(form.start_date);
                    if (!Number.isNaN(start.getTime())) {
                        const weeks = Math.max(8, Math.min(24, Number(ovr.extend_weeks)));
                        const target = new Date(start.getTime() + (weeks * 7 - 1) * 24 * 60 * 60 * 1000);
                        form.target_date = target.toISOString().split('T')[0];
                    }
                }
                if (ovr.aggressiveness && COACH_AGG_MULT[ovr.aggressiveness]) {
                    formAggressiveness.value = ovr.aggressiveness;
                }
                showNotification('Parameter disesuaikan sesuai saran pelatih.', 'success');
            };

            const recommendMileage = () => {
                const val = Math.round(coachAssessment.value.ideal_peak_mileage || idealMileage.value);
                form.weekly_mileage = val;
                highlightMileage.value = true;
                setTimeout(() => { highlightMileage.value = false; }, 1500);
            };

            const getRatioForDistance = (distanceKey, vdot) => {
                const ratios = {
                    '5k': 0.957,
                    '10k': 0.915,
                    '21k': 0.865,
                    '42k': 0.815,
                    'cooper12': 0.99,
                    'balke15': 0.95
                };
                const base = ratios[distanceKey] ?? 0.957;
                return base + (vdot - 50) * 0.0005;
            };

            const vvo2FromVDOT = (vdot) => {
                const a = 0.000104;
                const b = 0.182258;
                const c = -4.6 - vdot;
                return (-b + Math.sqrt(b * b - 4 * a * c)) / (2 * a);
            };

            const calculateVDOTFromPerformance = (distanceKey, totalSeconds) => {
                let distMeters = distanceMeters[distanceKey];
                
                if (distanceKey === 'cooper12' || distanceKey === 'balke15') {
                    distMeters = Number(pb_distance_meters.value) || (distanceKey === 'balke15' ? 3000 : 2400);
                    totalSeconds = (distanceKey === 'balke15') ? 900 : 720;
                }

                if (!totalSeconds || totalSeconds < 300) return 0;
                if (!distMeters || distMeters <= 0) return 0;

                const velocityMin = (distMeters / totalSeconds) * 60;
                let vdot = 50;
                for (let i = 0; i < 5; i++) {
                    const ratio = Math.max(0.01, getRatioForDistance(distanceKey, vdot));
                    const vvo2max = velocityMin / ratio;
                    const newVdot = -4.6 + 0.182258 * vvo2max + 0.000104 * vvo2max * vvo2max;
                    if (Math.abs(newVdot - vdot) < 0.01) {
                        vdot = newVdot;
                        break;
                    }
                    vdot = newVdot;
                }
                return Math.max(10, Math.min(85, Number(vdot.toFixed(4))));
            };

            const predictRaceTimeSeconds = (vdot, distanceKey) => {
                if (!vdot || vdot <= 0) return 0;
                const distMeters = distanceMeters[distanceKey];
                if (!distMeters) return 0;
                const vvo2max = vvo2FromVDOT(vdot);
                const ratio = getRatioForDistance(distanceKey, vdot);
                const velocity = vvo2max * ratio;
                if (!velocity || velocity <= 0) return 0;
                return Math.round((distMeters / velocity) * 60);
            };

            // #region hero-vdot-calculator (implementation: callables dipindahkan SETELAH semua helper distance/VDOT/predict di atas agar tdk kena TDZ)
            const heroCalculateVdot = () => {
                const distKey = String(heroVdotDistance.value || '5k').toLowerCase();
                const dm = distanceMeters[distKey] || 5000;
                const h = Math.max(0, Math.round(Number(heroVdotH.value) || 0));
                const m = Math.max(0, Math.min(59, Math.round(Number(heroVdotM.value) || 0)));
                const s = Math.max(0, Math.min(59, Math.round(Number(heroVdotS.value) || 0)));
                const sec = h * 3600 + m * 60 + s;
                if (sec < 300) {
                    showNotification('Masukkan waktu PB yang valid (minimal 5 menit).', 'error');
                    return;
                }
                heroVdotCompute.pbSec = sec;
                const vdot = Math.max(15, Math.min(85, calculateVDOTFromPerformance(distKey, sec)));
                heroVdotCompute.vdot = Math.round(vdot * 100) / 100;
                const vvo2max = vvo2FromVDOT(vdot);
                const paceSecPerKm = (vdot > 0 && vvo2max > 0) ? (60 / (vvo2max * getRatioForDistance(distKey, vdot))) : 0;
                heroVdotCompute.easy_low = paceSecPerKm * 1.28;
                heroVdotCompute.easy_high = paceSecPerKm * 1.19;
                heroVdotCompute.marathon = paceSecPerKm * 1.09;
                heroVdotCompute.tempo = paceSecPerKm * 1.03;
                heroVdotCompute.threshold = paceSecPerKm * 1.00;
                heroVdotCompute.interval = paceSecPerKm * 0.92;
                heroVdotCompute.interval_400 = Math.round((paceSecPerKm * 0.92) * 0.4);
                heroVdotCompute.repetition = paceSecPerKm * 0.87;
                heroVdotCompute.repetition_400 = Math.round((paceSecPerKm * 0.87) * 0.4);
                heroVdotCompute.r_5k = predictRaceTimeSeconds(vdot, '5k');
                heroVdotCompute.r_10k = predictRaceTimeSeconds(vdot, '10k');
                heroVdotCompute.r_21k = predictRaceTimeSeconds(vdot, '21k');
                heroVdotCompute.r_42k = predictRaceTimeSeconds(vdot, '42k');
                heroVdotCalculated.value = true;
                heroVdotTab.value = 'paces';
            };
            const heroApplyToWizard = () => {
                const distKey = String(heroVdotDistance.value || '5k').toLowerCase();
                const dm = distanceMeters[distKey] || 5000;
                const h = Math.max(0, Math.round(Number(heroVdotH.value) || 0));
                const m = Math.max(0, Math.min(59, Math.round(Number(heroVdotM.value) || 0)));
                const s = Math.max(0, Math.min(59, Math.round(Number(heroVdotS.value) || 0)));
                const sec = h * 3600 + m * 60 + s;
                if (sec < 300) {
                    showNotification('Masukkan waktu PB yang valid sebelum lanjut ke wizard.', 'error');
                    return;
                }
                form.pb_distance = distKey;
                if (distKey === 'cooper12' || distKey === 'balke15') {
                    pb_distance_meters.value = dm;
                } else {
                    pb_hours.value = h;
                    pb_minutes.value = m;
                    pb_seconds.value = s;
                }
                userEditedPb.value = true;
                userEditedGoal.value = false;
                const defaultTargetMap = {
                    '5k': '10k',
                    '10k': '21k',
                    '21k': '42k',
                    '42k': '10k',
                };
                form.target_distance = defaultTargetMap[distKey] || '10k';
                isWizardOpen.value = true;
                wizardStep.value = 1;
                step.value = 1;
                setTimeout(() => {
                    const el = document.getElementById('tool-container');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 90);
                showNotification('Data PB kalkulator disinkronkan ke Wizard Step 1.', 'success');
            };
            // #endregion

            const weeksUntilRace = computed(() => {
                if (!form.target_date) return 12;
                const target = new Date(form.target_date);
                if (Number.isNaN(target.getTime())) return 12;

                let start = Date.now();
                if (form.start_date) {
                    const parsedStart = new Date(form.start_date);
                    if (!Number.isNaN(parsedStart.getTime())) {
                        start = parsedStart.getTime();
                    }
                }

                const diffDays = Math.ceil((target.getTime() - start) / (1000 * 60 * 60 * 24));
                const weeks = Math.ceil(diffDays / 7);
                return Math.min(24, Math.max(8, weeks || 12));
            });

            const current_vdot = computed(() => {
                if (form.pb_distance === 'cooper12' || form.pb_distance === 'balke15') {
                    if (!pb_distance_meters.value || pb_distance_meters.value < 500) return null;
                    const sec = form.pb_distance === 'balke15' ? 900 : 720;
                    return calculateVDOTFromPerformance(form.pb_distance, sec);
                }
                const h = Number(pb_hours.value) || 0;
                const m = Number(pb_minutes.value) || 0;
                const s = Number(pb_seconds.value) || 0;
                const totalSec = (h * 3600) + (m * 60) + s;
                if (!totalSec || totalSec < 300) return null;
                return calculateVDOTFromPerformance(form.pb_distance, totalSec);
            });

            const target_vdot = computed(() => {
                const h = Number(goal_hours.value) || 0;
                const m = Number(goal_minutes.value) || 0;
                const s = Number(goal_seconds.value) || 0;
                const totalSec = (h * 3600) + (m * 60) + s;
                if (!totalSec || totalSec < 300) return null;
                return calculateVDOTFromPerformance(form.target_distance, totalSec);
            });

            const recommendedImprovementPercent = computed(() => {
                const base = {
                    '5k': 0.06,
                    '10k': 0.05,
                    '21k': 0.04,
                    '42k': 0.03,
                    'cooper12': 0.07,
                    'balke15': 0.06
                };
                const levelFactor = {
                    'beginner': 0.85,
                    'intermediate': 1,
                    'advanced': 1.1
                };
                const basePct = base[form.target_distance] ?? 0.04;
                const scale = Math.min(1.2, Math.max(0.4, weeksUntilRace.value / 16));
                const pct = basePct * scale * (levelFactor[form.runner_level] ?? 1);
                return Math.min(0.08, Math.max(0.015, pct));
            });

            const recommendedTargetVdot = computed(() => {
                const cv = current_vdot.value;
                if (!cv || cv <= 0) return 0;
                const target = cv * (1 + recommendedImprovementPercent.value);
                return Math.min(target, cv + 3.0);
            });

            const suggestGoalTime = (force = false) => {
                const cv = current_vdot.value;
                if (!cv || cv <= 0) return;
                if (!force && userEditedGoal.value) {
                    _dbgPushGoal('suggestGoalTime_skip_userEditedGoal_guard', `force=${force} g_hms=${goal_hours.value}:${goal_minutes.value}:${goal_seconds.value}`);
                    return;
                }
                // Maintenance case: jika user menginginkan target LEBIH LAMBAT dari PB (target VDOT < current VDOT),
                // JANGAN paksa +3 VDOT; cukup rekomendasikan waktu setara PB atau sedikit lebih lambat 3%
                const currentPredSec = predictRaceTimeSeconds(cv, form.target_distance);
                let targetVdotFinal = recommendedTargetVdot.value;
                let predictedSeconds = predictRaceTimeSeconds(targetVdotFinal, form.target_distance);
                if (currentPredSec > 0 && predictedSeconds > 0 && predictedSeconds < (currentPredSec * 0.96)) {
                    // Jika user ingin target SANGAT AGRESIF (≥4% lebih cepat dari PB), berikan clamp lembut:
                    // min 2% lebih cepat saja untuk default rekomendasi awal, user boleh ubah manual
                    const safeFloor = Math.max(currentPredSec * 0.97, predictedSeconds);
                    const safeVDotFloorApprox = targetVdotFinal;
                    predictedSeconds = Math.round(safeFloor);
                }
                if (predictedSeconds > 0) {
                    const oldH = goal_hours.value;
                    const oldM = goal_minutes.value;
                    const oldS = goal_seconds.value;
                    goal_hours.value = Math.floor(predictedSeconds / 3600);
                    goal_minutes.value = Math.floor((predictedSeconds % 3600) / 60);
                    goal_seconds.value = Math.floor(predictedSeconds % 60);
                    _debugSuggestOverwrites.value += 1;
                    _dbgPushGoal('suggestGoalTime_applied', `force=${force} old=${oldH}:${oldM}:${oldS} new=${goal_hours.value}:${goal_minutes.value}:${goal_seconds.value} targetVdot=${Math.round(targetVdotFinal*100)/100} cv=${Math.round(cv*100)/100} predSec=${predictedSeconds}`);
                }
            };

            const recommendedDurationWeeksMap = {
                '5k': 10,
                '10k': 12,
                '21k': 14,
                '42k': 16,
                'cooper12': 8,
                'balke15': 8
            };

            const recommendedWeeks = computed(() => {
                return recommendedDurationWeeksMap[form.target_distance] || 12;
            });

            const recommendedTargetDate = computed(() => {
                if (!form.start_date) return '';
                const startDateObj = new Date(form.start_date);
                if (isNaN(startDateObj.getTime())) return '';
                
                const recWeeks = recommendedWeeks.value;
                const targetDateObj = new Date(startDateObj.getTime() + (recWeeks * 7 - 1) * 24 * 60 * 60 * 1000);
                
                const year = targetDateObj.getFullYear();
                const month = String(targetDateObj.getMonth() + 1).padStart(2, '0');
                const day = String(targetDateObj.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            });

            const applyRecommendedTargetDate = () => {
                if (recommendedTargetDate.value) {
                    form.target_date = recommendedTargetDate.value;
                }
            };

            const autoDetermineRunnerLevel = (vdot) => {
                if (!vdot || vdot <= 0) return 'intermediate';
                if (vdot < 40) return 'beginner';
                if (vdot < 55) return 'intermediate';
                return 'advanced';
            };

            watch(current_vdot, (newVdot, oldVdot) => {
                if (newVdot && newVdot > 0) {
                    form.runner_level = autoDetermineRunnerLevel(newVdot);
                    suggestGoalTime(false); // HANYA auto-apply jika user BELUM pernah edit goal manual
                    recommendMileage();
                    _dbgPushGoal('watch_current_vdot', `new=${Math.round(newVdot*100)/100} old=${oldVdot ? Math.round(oldVdot*100)/100 : 'null'} userEditedGoal=${userEditedGoal.value}`);
                }
            });

            watch([() => form.start_date, () => form.target_distance], ([newStartDate, newDist], [oldStartDate, oldDist]) => {
                if (recommendedTargetDate.value && (!form.target_date || newDist !== oldDist)) {
                    applyRecommendedTargetDate();
                }
            });

            // Snapshot review computed labels
            const formatPbDisplay = computed(() => {
                if (form.pb_distance === 'cooper12') {
                    return `${pb_distance_meters.value || 0}m (Cooper 12 Menit)`;
                }
                if (form.pb_distance === 'balke15') {
                    return `${pb_distance_meters.value || 0}m (Balke 15 Menit)`;
                }
                const h = String(pb_hours.value || 0).padStart(2, '0');
                const m = String(pb_minutes.value || 0).padStart(2, '0');
                const s = String(pb_seconds.value || 0).padStart(2, '0');
                return `${form.pb_distance.toUpperCase()} (${h}:${m}:${s})`;
            });

            const formatGoalTimeDisplay = computed(() => {
                const h = String(goal_hours.value || 0).padStart(2, '0');
                const m = String(goal_minutes.value || 0).padStart(2, '0');
                const s = String(goal_seconds.value || 0).padStart(2, '0');
                return `${h}:${m}:${s}`;
            });

            const calculatedDurationWeeks = computed(() => {
                if (!form.start_date || !form.target_date) return recommendedWeeks.value || 8;
                const d1 = new Date(form.start_date);
                const d2 = new Date(form.target_date);
                const diff = Math.round((d2 - d1) / (1000 * 60 * 60 * 24));
                return Math.max(1, Math.round(diff / 7));
            });

            // Wizard Step Navigation
            const goToWizardStep = (n) => {
                if (n >= 1 && n <= 4) {
                    wizardStep.value = n;
                    const el = document.getElementById('wizard-container');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            };

            const nextWizardStep = () => {
                if (wizardStep.value === 1) {
                    if (form.pb_distance === 'cooper12' || form.pb_distance === 'balke15') {
                        if (!pb_distance_meters.value || pb_distance_meters.value < 500) {
                            showNotification('Masukkan jarak hasil tes minimal 500 meter.', 'error');
                            return;
                        }
                    } else {
                        const h = Number(pb_hours.value) || 0;
                        const m = Number(pb_minutes.value) || 0;
                        const s = Number(pb_seconds.value) || 0;
                        const totalSec = (h * 3600) + (m * 60) + s;
                        if (!totalSec || totalSec < 300) {
                            showNotification('Masukkan waktu tempuh PB minimal 5 menit.', 'error');
                            return;
                        }
                    }
                    wizardStep.value = 2;
                } else if (wizardStep.value === 2) {
                    if (!form.start_date || !form.target_date) {
                        showNotification('Tentukan tanggal mulai dan tanggal race / target.', 'error');
                        return;
                    }
                    if (new Date(form.target_date) <= new Date(form.start_date)) {
                        showNotification('Tanggal race harus setelah tanggal mulai latihan.', 'error');
                        return;
                    }
                    const gh = Number(goal_hours.value) || 0;
                    const gm = Number(goal_minutes.value) || 0;
                    const gs = Number(goal_seconds.value) || 0;
                    const goalSec = (gh * 3600) + (gm * 60) + gs;
                    if (!goalSec || goalSec < 300) {
                        showNotification('Tentukan target waktu finish yang valid.', 'error');
                        return;
                    }
                    wizardStep.value = 3;
                } else if (wizardStep.value === 3) {
                    const ca = coachAssessment.value;
                    const minMil = ca.min_required_peak_mileage || 10;
                    const minMilFloor = Math.max(10, Math.floor(minMil * 0.7));
                    if (!form.weekly_mileage || form.weekly_mileage < minMilFloor) {
                        showNotification('Mileage mingguan minimal ' + minMilFloor + ' km (batas bawah minimum target Anda: ' + minMil + ' km). Untuk saran ideal klik Reset Saran.', 'error');
                        return;
                    }
                    if (form.frequency < (ca.min_frequency || 3)) {
                        showNotification('Frekuensi latihan minimal ' + ca.min_frequency + ' hari/minggu untuk target ini.', 'error');
                        return;
                    }
                    wizardStep.value = 4;
                }
                const el = document.getElementById('wizard-container');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };

            const prevWizardStep = () => {
                if (wizardStep.value > 1) {
                    wizardStep.value--;
                    const el = document.getElementById('wizard-container');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            };

            // LocalStorage Persistence
            const STORAGE_KEY = 'ruanglari_vdot_form_v2';

            const saveFormDraft = () => {
                try {
                    const payload = {
                        wizardStep: wizardStep.value,
                        form: { ...form },
                        pb_hours: pb_hours.value,
                        pb_minutes: pb_minutes.value,
                        pb_seconds: pb_seconds.value,
                        pb_distance_meters: pb_distance_meters.value,
                        goal_hours: goal_hours.value,
                        goal_minutes: goal_minutes.value,
                        goal_seconds: goal_seconds.value,
                        saved_at: Date.now()
                    };
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
                    const d = new Date();
                    lastSavedTime.value = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                } catch (e) {}
            };

            const loadFormDraft = () => {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    if (!raw) return false;
                    const data = JSON.parse(raw);
                    if (data && data.form) {
                        Object.assign(form, data.form);
                        if (data.pb_hours !== undefined) pb_hours.value = data.pb_hours;
                        if (data.pb_minutes !== undefined) pb_minutes.value = data.pb_minutes;
                        if (data.pb_seconds !== undefined) pb_seconds.value = data.pb_seconds;
                        if (data.pb_distance_meters !== undefined) pb_distance_meters.value = data.pb_distance_meters;
                        if (data.goal_hours !== undefined) goal_hours.value = data.goal_hours;
                        if (data.goal_minutes !== undefined) goal_minutes.value = data.goal_minutes;
                        if (data.goal_seconds !== undefined) goal_seconds.value = data.goal_seconds;
                        if (data.wizardStep && data.wizardStep >= 1 && data.wizardStep <= 4) {
                            wizardStep.value = data.wizardStep;
                        }
                        if (data.saved_at) {
                            const d = new Date(data.saved_at);
                            lastSavedTime.value = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        }
                        return true;
                    }
                } catch (e) {}
                return false;
            };

            const resetFormDraft = () => {
                try {
                    localStorage.removeItem(STORAGE_KEY);
                } catch (e) {}
                wizardStep.value = 1;
                lastSavedTime.value = '';
                pb_hours.value = null;
                pb_minutes.value = null;
                pb_seconds.value = null;
                pb_distance_meters.value = null;
                goal_hours.value = null;
                goal_minutes.value = null;
                goal_seconds.value = null;
                form.pb_distance = '5k';
                form.target_distance = '10k';
                form.start_date = todayStr;
                form.weekly_mileage = 45;
                form.frequency = 4;
                form.gender = 'male';
                form.age = 25;
                form.height_cm = 170;
                form.weight_kg = 65;
                form.injury_history = 'none';
                form.include_strength = true;
                form.strength_type = 'bodyweight';
                form.runner_level = 'intermediate';
                form.long_run_day = 'sunday';
                form.is_tropical = false;
                applyRecommendedTargetDate();
                showNotification('Draf formulir telah direset.', 'info');
            };

            watch([
                wizardStep,
                () => form.pb_distance,
                () => form.target_distance,
                () => form.start_date,
                () => form.target_date,
                () => form.weekly_mileage,
                () => form.frequency,
                () => form.gender,
                () => form.age,
                () => form.height_cm,
                () => form.weight_kg,
                () => form.injury_history,
                () => form.include_strength,
                () => form.strength_type,
                () => form.runner_level,
                () => form.long_run_day,
                () => form.is_tropical,
                pb_hours, pb_minutes, pb_seconds, pb_distance_meters,
                goal_hours, goal_minutes, goal_seconds
            ], () => {
                if (isWizardOpen.value) {
                    saveFormDraft();
                }
            });

            onMounted(() => {
                const params = new URLSearchParams(window.location.search);
                let dist = params.get('distance');
                const time = params.get('time');
                const meters = params.get('meters');
                const start = params.get('start');

                const hasParams = !!(start || dist || time || meters);

                if (hasParams) {
                    isWizardOpen.value = true;
                    step.value = 1;
                    wizardStep.value = 1;

                    if (dist) {
                        dist = dist.toLowerCase();
                        if (['5k', '10k', '21k', '42k', 'cooper12', 'balke15'].includes(dist)) {
                            form.pb_distance = dist;
                            if (['5k', '10k', '21k', '42k'].includes(dist)) {
                                form.target_distance = dist;
                            }
                        }
                    }

                    if (meters && (dist === 'cooper12' || dist === 'balke15')) {
                        pb_distance_meters.value = parseInt(meters, 10) || 0;
                    } else if (time) {
                        const parts = time.split(':');
                        if (parts.length === 3) {
                            pb_hours.value = parseInt(parts[0], 10) || 0;
                            pb_minutes.value = parseInt(parts[1], 10) || 0;
                            pb_seconds.value = parseInt(parts[2], 10) || 0;
                        }
                    }

                    if (!form.target_date) {
                        applyRecommendedTargetDate();
                    }

                    suggestGoalTime(false);
                    recommendMileage();
                    _dbgPushGoal('onMounted_initial_suggest', `userEditedGoal=${userEditedGoal.value}`);

                    setTimeout(() => {
                        const el = document.getElementById('wizard-container');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 250);
                } else {
                    const restored = loadFormDraft();
                    if (!restored) {
                        applyRecommendedTargetDate();
                    }
                }
            });

            const realism = computed(() => {
                const ca = coachAssessment.value;
                if (!ca) return null;
                const badgeClassMap = {
                    emerald: 'bg-emerald-900/20 text-emerald-300 border-emerald-500/30',
                    amber: 'bg-amber-900/20 text-amber-300 border-amber-500/30',
                    orange: 'bg-orange-900/20 text-orange-300 border-orange-500/30',
                    red: 'bg-red-900/20 text-red-300 border-red-500/30',
                };
                return {
                    label: ca.label + ' · Skor ' + ca.score + '/100',
                    color: badgeClassMap[ca.color] || badgeClassMap.emerald,
                    description: ca.reason,
                    options: ca.options || [],
                    feasibility: ca.feasibility,
                    score: ca.score,
                };
            });

            const bmi = computed(() => {
                if (!form.height_cm || !form.weight_kg) return null;
                const heightM = form.height_cm / 100;
                return +(form.weight_kg / (heightM * heightM)).toFixed(1);
            });

            const bmiCategory = computed(() => {
                const b = bmi.value;
                if (!b) return null;
                if (b < 18.5) return { label: 'Kurus', color: 'text-blue-400', badgeClass: 'bg-blue-500/10 border-blue-500/30' };
                if (b < 25)   return { label: 'Normal', color: 'text-emerald-400', badgeClass: 'bg-emerald-500/10 border-emerald-500/30' };
                if (b < 30)   return { label: 'Overweight', color: 'text-amber-400', badgeClass: 'bg-amber-500/10 border-amber-500/30' };
                return { label: 'Obese', color: 'text-red-400', badgeClass: 'bg-red-500/10 border-red-500/30' };
            });

            const proteinRecommendation = computed(() => {
                if (!form.weight_kg) return null;
                const w = form.weight_kg;
                const level = form.runner_level || 'intermediate';
                let minFactor = 1.6, maxFactor = 1.8;
                if (level === 'beginner') { minFactor = 1.4; maxFactor = 1.6; }
                else if (level === 'advanced') { minFactor = 1.8; maxFactor = 2.0; }
                return {
                    min: Math.round(w * minFactor),
                    max: Math.round(w * maxFactor),
                    note: `Target ${minFactor}–${maxFactor} g/kg/hari (standar ACSM untuk atlet)`
                };
            });

            const freePreviewSessions = computed(() => {
                if (!result.value) return [];
                return result.value.sessions || [];
            });

            const freeWeeksCount = computed(() => {
                if (!result.value) return 0;
                return result.value.weeks || 0;
            });

            const sessionsByWeek = computed(() => {
                const weeks = {};
                freePreviewSessions.value.forEach(s => {
                    if (!weeks[s.week]) weeks[s.week] = [];
                    weeks[s.week].push(s);
                });
                return weeks;
            });

            const generateProgram = async () => {
                errors.value = null;

                if (form.pb_distance === 'cooper12' || form.pb_distance === 'balke15') {
                    form.pb_time = String(pb_distance_meters.value || 0);
                } else {
                    const h = String(pb_hours.value || 0).padStart(2, '0');
                    const m = String(pb_minutes.value || 0).padStart(2, '0');
                    const s = String(pb_seconds.value || 0).padStart(2, '0');
                    form.pb_time = `${h}:${m}:${s}`;
                }

                const gh = String(goal_hours.value || 0).padStart(2, '0');
                const gm = String(goal_minutes.value || 0).padStart(2, '0');
                const gs = String(goal_seconds.value || 0).padStart(2, '0');
                form.goal_time = `${gh}:${gm}:${gs}`;

                if (form.pb_distance !== 'cooper12' && form.pb_distance !== 'balke15') {
                    if ((!pb_hours.value && !pb_minutes.value && !pb_seconds.value) ||
                        (pb_hours.value === 0 && pb_minutes.value === 0 && pb_seconds.value === 0)) {
                        showNotification('Harap isi waktu parameter test/PB!', 'error');
                        return;
                    }
                } else {
                    if (!pb_distance_meters.value || pb_distance_meters.value <= 0) {
                        showNotification('Harap isi jarak hasil tes parameter!', 'error');
                        return;
                    }
                }

                if ((!goal_hours.value && !goal_minutes.value && !goal_seconds.value) ||
                    (goal_hours.value === 0 && goal_minutes.value === 0 && goal_seconds.value === 0)) {
                    showNotification('Harap isi target waktu lomba!', 'error');
                    return;
                }

                if (!form.target_date) {
                    showNotification('Harap lengkapi target tanggal lomba!', 'error');
                    return;
                }

                form.aggressiveness = formAggressiveness.value || 'standard';
                if (form.force_infeasible_ack !== true) form.force_infeasible_ack = false;

                loading.value = true;
                try {
                    const response = await fetch('{{ route("generator.generate", [], false) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(form)
                    });

                    const text = await response.text();
                    let data = {};
                    try { data = text ? JSON.parse(text) : {}; } catch (pe) { data = { success: false, message: 'Format respons server tidak valid.' }; }

                    const is422Infeasible = !response.ok && response.status === 422 && data && data.feasibility && data.feasibility.feasibility === 'INFEASIBLE';
                    const payloadFeasibleFailedButStructured = !data.success && data && data.feasibility && data.feasibility.feasibility === 'INFEASIBLE';

                    if (response.ok && data.success) {
                        result.value = data.data;
                        step.value = 2;
                        form.force_infeasible_ack = false;
                        setTimeout(() => {
                            const el = document.getElementById('tool-container');
                            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 70);
                    } else if (is422Infeasible || payloadFeasibleFailedButStructured) {
                        errors.value = data.errors || null;
                        const feas = data.feasibility;
                        feasibilityModalPayload.score = feas.score || 0;
                        feasibilityModalPayload.reason = feas.reason || data.message || 'Kombinasi target, beban, dan timeline saat ini tidak memadai untuk pencapaian yang sehat.';
                        feasibilityModalPayload.options = Array.isArray(feas.options) ? feas.options : (coachAssessment.value?.options || []);
                        showFeasibilityModal.value = true;
                    } else {
                        errors.value = data.errors;
                        showNotification(data.message || 'Gagal memproses data. Silakan cek input Anda.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    showNotification('Terjadi kesalahan sistem.', 'error');
                } finally {
                    loading.value = false;
                }
            };

            const saveAndOpenCalendar = async (overrideAction = null) => {
                const actionParam = (typeof overrideAction === 'string' && (overrideAction === 'replace' || overrideAction === 'add')) ? overrideAction : null;

                @guest
                    try {
                        await fetch('{{ route("generator.store-pending", [], false) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                form: form,
                                result: result.value
                            })
                        });
                    } catch (e) {
                        console.error('Failed to store pending program:', e);
                    }

                    if (window.openLoginModal) {
                        window.openLoginModal();
                    } else {
                        showNotification('Harap login terlebih dahulu.', 'error');
                    }
                    return;
                @endguest

                saving.value = true;
                try {
                    const payload = {
                        form: form,
                        result: result.value
                    };
                    if (actionParam) {
                        payload.action = actionParam;
                    }

                    const response = await fetch('{{ route("generator.save", [], false) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json();
                    if (data.has_active_program && !actionParam) {
                        conflictModal.activeTitle = data.active_program_title || 'Program Aktif';
                        conflictModal.activeStartDate = data.active_start_date || '';
                        conflictModal.activeEndDate = data.active_end_date || '';
                        conflictModal.show = true;
                        return;
                    }

                    if (data.success) {
                        window.location.href = '{{ route("runner.calendar", [], false) }}';
                    } else {
                        showNotification(data.message || 'Gagal menyimpan program.', 'error');
                    }
                } catch (e) {
                    console.error(e);
                    showNotification('Terjadi kesalahan sistem.', 'error');
                } finally {
                    saving.value = false;
                }
            };

            const confirmConflictAction = (actionType) => {
                conflictModal.show = false;
                saveAndOpenCalendar(actionType);
            };

            const displayPaces = computed(() => {
                if (!result.value || !result.value.paces) return {};
                const raw = result.value.paces;
                const filtered = {};
                ['E', 'M', 'T', 'I', 'R'].forEach(k => {
                    if (raw[k] !== undefined) {
                        filtered[k] = raw[k];
                    }
                });
                return filtered;
            });

            const getPaceLabel = (type) => {
                const labels = { 'E': 'Easy', 'M': 'Marathon', 'T': 'Threshold', 'I': 'Interval', 'R': 'Repetition' };
                return labels[type] || type;
            };

            const getPaceColor = (type) => {
                const colors = { 
                    'E': 'text-emerald-400', 
                    'M': 'text-blue-400', 
                    'T': 'text-amber-400', 
                    'I': 'text-rose-400', 
                    'R': 'text-[#FC4C02]' 
                };
                return colors[type] || 'text-slate-400';
            };

            const formatPaceVal = (val) => {
                if (!val || val <= 0) return '-';
                const totalSeconds = Math.round(val * 60);
                const m = Math.floor(totalSeconds / 60);
                const s = totalSeconds % 60;
                return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            };

            const formatPace = (pace, type) => {
                if (type === 'E' && result.value?.paces?.is_run_walk) {
                    return '08:00 - 08:30 /km (Run/Walk)';
                }
                if (type === 'E' && result.value?.paces?.E_high && result.value?.paces?.E_low) {
                    return `${formatPaceVal(result.value.paces.E_high)} - ${formatPaceVal(result.value.paces.E_low)} /km`;
                }
                if (!pace || pace <= 0) return '-';
                return `${formatPaceVal(pace)} /km`;
            };

            const getSessionClass = (type) => {
                const classes = {
                    'easy_run': 'bg-emerald-950/20 border-emerald-500/20 text-emerald-400',
                    'run_walk': 'bg-teal-950/20 border-teal-500/20 text-teal-400',
                    'long_run': 'bg-blue-950/20 border-blue-500/20 text-blue-400',
                    'marathon': 'bg-cyan-950/20 border-cyan-500/20 text-cyan-400',
                    'tempo': 'bg-amber-950/20 border-amber-500/20 text-amber-400',
                    'threshold': 'bg-amber-950/20 border-amber-500/20 text-amber-400',
                    'interval': 'bg-rose-950/20 border-rose-500/20 text-rose-400',
                    'repetition': 'bg-fuchsia-950/20 border-fuchsia-500/20 text-fuchsia-400',
                    'hill': 'bg-sky-950/20 border-sky-500/20 text-sky-400',
                    'strength': 'bg-indigo-950/20 border-indigo-500/20 text-indigo-400',
                    'rest': 'bg-slate-900/40 border-slate-800 opacity-60 text-slate-400'
                };
                return classes[type] || 'bg-slate-900/40 border-slate-800';
            };

            return {
                step, isWizardOpen, openWizard, closeWizard, form, loading, saving, result, 
                freePreviewSessions, freeWeeksCount, sessionsByWeek, errors, notification,
                conflictModal, confirmConflictAction,
                generateProgram, saveAndOpenCalendar,
                displayPaces, getPaceLabel, getPaceColor, formatPace, getSessionClass,
                pb_hours, pb_minutes, pb_seconds, pb_distance_meters,
                goal_hours, goal_minutes, goal_seconds,
                idealMileage, recommendMileage, realism,
                current_vdot, target_vdot, recommendedTargetDate, recommendedWeeks, applyRecommendedTargetDate, suggestGoalTime,
                bmi, bmiCategory, proteinRecommendation,
                showNotification,
                wizardStep, lastSavedTime, goToWizardStep, nextWizardStep, prevWizardStep, resetFormDraft,
                formatPbDisplay, formatGoalTimeDisplay, calculatedDurationWeeks,
                formAggressiveness, highlightMileage, minMileageDynamic, coachAssessment,
                applyChipOption, computeCoachAssessment, showFeasibilityModal, feasibilityModalPayload,
                userEditedGoal, userEditedPb, _debugGoalTimeline, _debugSuggestOverwrites, _dbgPushGoal,
                heroVdotTab, heroVdotDistance, heroVdotH, heroVdotM, heroVdotS, heroVdotCalculated, heroVdotCompute, heroVdotLevel,
                heroCalculateVdot, heroFormatPace, heroFormatDur, heroApplyToWizard
            };
        }
    }).mount('#generator-v2-app');
</script>
@endpush
