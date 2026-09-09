@php
    $destImg = public_path('images/hero/vdot-runner-training.jpg');
    $srcImg = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/ad786e94-2a56-4d78-962d-8aea9a7effaf/vdot_runner_training_1788923975440.jpg';
    if (!file_exists($destImg) && file_exists($srcImg)) {
        @copy($srcImg, $destImg);
    }
@endphp
@extends('layouts.pacerhub')

@section('title', 'Buat Program Lari Gratis: Generator VDOT 5K, 10K, HM & Marathon - RuangLari')

@section('meta_title', 'Buat Program Lari Gratis: Generator VDOT 5K, 10K, HM & Marathon - RuangLari')
@section('meta_description', 'Buat program lari gratis berbasis formula VDOT untuk 5K, 10K, Half Marathon & Marathon. Lengkap dengan target pace, zona detak jantung, dan jadwal latihan.')
@section('meta_keywords', 'buat program lari, generator program lari, program lari gratis, kalkulator vdot, program lari 5k, program lari 10k, program latihan half marathon, program marathon, jadwal latihan lari, run walk method, jack daniels running formula, pacerhub, ruang lari')
@section('canonical_url', url('/buat-program-lari'))
@section('meta_robots', 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1')

@section('og_type', 'website')
@section('og_image', 'https://ruanglari.com/storage/blog/media/kP2oNYsx0wEzCGJMQYKN1xxUBW3oaUMTCfydDSig.webp')

@push('head')
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
    <script>
        // Extending existing Tailwind config if available
        if (typeof tailwind !== 'undefined' && tailwind && tailwind.config) {
            tailwind.config.theme = tailwind.config.theme || {};
            tailwind.config.theme.extend = tailwind.config.theme.extend || {};
            tailwind.config.theme.extend.colors = {
                ...(tailwind.config.theme.extend.colors || {}),
                brand: {
                    50: '#fcfef0',
                    100: '#f5facc',
                    200: '#ebf699',
                    300: '#def066',
                    400: '#d1e833',
                    500: '#ccff00',  // Neon Yellow/Green
                    600: '#b8e600',
                    700: '#94bf00',
                    800: '#719900',
                    900: '#4e7300',
                }
            };
        }
    </script>
    
    <style>
        /* Brand Colors & Button Fallbacks */
        .bg-brand-500 { background-color: #ccff00 !important; color: #08111f !important; }
        .bg-brand-600 { background-color: #b8e600 !important; color: #08111f !important; }
        .hover\:bg-brand-600:hover { background-color: #b8e600 !important; color: #08111f !important; }
        .hover\:bg-brand-500:hover { background-color: #ccff00 !important; color: #08111f !important; }
        .text-brand-500 { color: #ccff00 !important; }
        .border-brand-500 { border-color: #ccff00 !important; }
        .accent-brand-500 { accent-color: #ccff00 !important; }

        .generator-v2-wrapper { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; 
            background-color: #090D15;
            color: #f1f5f9;
            min-height: 100vh;
            position: relative;
        }

        /* Hero Background Slider & Ken Burns Zoom Out */
        .hero-slider-wrapper {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            user-select: none;
            z-index: 0;
            background-color: #090D15;
            background-size: cover;
            background-position: center center;
        }

        .hero-slide-item {
            position: absolute;
            inset: -4%;
            width: 108%;
            height: 108%;
            background-size: cover;
            background-position: center center;
            opacity: 0;
            transform: scale(1.15);
            transition: opacity 1.5s ease-in-out, transform 0.1s ease;
            will-change: transform, opacity;
        }

        .hero-slide-item.active-slide {
            opacity: 1 !important;
            transform: scale(1.0);
            transition: opacity 1.5s ease-in-out, transform 8.5s cubic-bezier(0.2, 1, 0.3, 1);
            z-index: 1;
        }

        .hero-slider-overlay {
            position: absolute;
            inset: 0;
            z-index: 2;
            background: rgba(9, 13, 21, 0.75) !important; /* 50% overlay transparan */
        }
        
        .generator-v2-wrapper .card-dark {
            background: #131B2D;
            border: 1.5px solid #283750;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
        }

        .fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
        .fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(4px); }

        .input-field {
            width: 100%;
            padding: 0.6rem 0.85rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            border: 1.5px solid #3A4C6D !important;
            background-color: #1C273B !important;
            color: #ffffff !important;
            font-weight: 500;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        .input-field::placeholder {
            color: #94a3b8 !important;
            opacity: 1;
        }

        /* Select styling with custom arrow */
        select.input-field {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23cbd5e1' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.25em 1.25em !important;
            padding-right: 2.25rem !important;
        }

        /* Date input styling */
        input[type="date"].input-field {
            position: relative;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23cbd5e1' stroke-width='2'%3e%3cpath stroke-linecap='round' stroke-linejoin='round' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.2em 1.2em !important;
            padding-right: 2.25rem !important;
        }
        input[type="date"].input-field::-webkit-calendar-picker-indicator {
            background: transparent;
            bottom: 0;
            color: transparent;
            cursor: pointer;
            height: auto;
            left: 0;
            position: absolute;
            right: 0;
            top: 0;
            width: auto;
            z-index: 10;
        }

        .input-field:focus {
            border-color: #ccff00 !important;
            background-color: #223048 !important;
            box-shadow: 0 0 0 2px rgba(204, 255, 0, 0.25) !important;
        }

        .label-text {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 0.35rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Custom Scrollbar */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* FAQ Accordion Details & Summary */
        details.faq-item summary::-webkit-details-marker { display: none; }
        details.faq-item summary { list-style: none; }
        details.faq-item[open] .faq-chevron { transform: rotate(180deg); }
    </style>
@endpush

@section('content')
<div id="generator-v2-app" class="generator-v2-wrapper relative w-full pb-16 pt-0">
    
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
            <div class="card-dark max-w-md w-full p-6 rounded-lg border border-amber-500/40 shadow-xl space-y-4 relative">
                <div>
                    <h3 class="text-base font-bold text-white">Program Aktif Terdeteksi</h3>
                    <p class="text-xs text-slate-300 mt-0.5">Kalender Anda sudah memiliki program latihan aktif saat ini.</p>
                </div>

                <div class="p-3 rounded-md bg-slate-900 border border-slate-800 space-y-1">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Program Aktif Saat Ini:</div>
                    <div class="text-sm font-semibold text-white">@{{ conflictModal.activeTitle }}</div>
                    <div v-if="conflictModal.activeStartDate" class="text-xs text-slate-400 font-mono">
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

    <!-- Hero Section: Scientific VDOT Running Program Generator with Background Zoom-Out Slider -->
<header v-show="step === 1" class="relative w-full overflow-hidden border-b border-slate-800/80 bg-[#090D15] text-white py-14 md:py-20 px-4 sm:px-6 lg:px-8 min-h-[580px] flex items-center">

    <!-- Background Slider with Cinematic Ken Burns Zoom Out -->
    <div class="hero-slider-wrapper" style="background-image: url('{{ asset('images/hero/vdot-runner-training.jpg') }}');">
        <div v-for="(slide, idx) in heroSlides" :key="'hero-slide-' + idx"
             class="hero-slide-item"
             :class="{ 'active-slide': activeHeroSlide === idx }"
             :style="{ backgroundImage: 'url(' + slide + ')' }">
        </div>
        <div class="hero-slider-overlay"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">

        <!-- Left Column -->
        <div class="lg:col-span-7 space-y-5">

            <!-- Category Kicker -->
            <div class="text-xs font-mono uppercase tracking-widest text-brand-500 font-bold">
                Kalkulator VDOT Jack Daniels • Program Latihan Lari Berbasis Pace
            </div>

            <!-- SEO H1 -->
            <h1 class="text-3xl sm:text-4xl lg:text-4xl font-bold text-white tracking-tight leading-tight drop-shadow-md">
                Buat Program Latihan Lari Gratis untuk 5K, 10K, Half Marathon & Marathon dengan Kalkulator VDOT
            </h1>

            <!-- Value Proposition -->
            <p class="text-sm sm:text-base text-slate-200 leading-relaxed max-w-2xl drop-shadow-sm">
                Hitung pace latihan berdasarkan performa terbaikmu menggunakan metode VDOT Jack Daniels.
                Dapatkan program latihan yang mencakup Easy Run, Threshold, Interval hingga Repetition
                dengan intensitas yang sesuai untuk mencapai target lomba.
            </p>

            <!-- Feature Highlights -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 pt-1">

                <div class="p-3 rounded-md bg-[#131B2D]/95 border border-[#283750] shadow-sm backdrop-blur-sm">
                    <div class="text-[10px] text-slate-400 font-mono uppercase">Metode</div>
                    <div class="text-xs font-bold text-white mt-0.5">Jack Daniels VDOT</div>
                </div>

                <div class="p-3 rounded-md bg-[#131B2D]/95 border border-[#283750] shadow-sm backdrop-blur-sm">
                    <div class="text-[10px] text-slate-400 font-mono uppercase">Perhitungan</div>
                    <div class="text-xs font-bold text-brand-500 mt-0.5">5 Zona Pace</div>
                </div>

                <div class="p-3 rounded-md bg-[#131B2D]/95 border border-[#283750] shadow-sm backdrop-blur-sm">
                    <div class="text-[10px] text-slate-400 font-mono uppercase">Program</div>
                    <div class="text-xs font-bold text-emerald-400 mt-0.5">Sesuai Target</div>
                </div>

                <div class="p-3 rounded-md bg-[#131B2D]/95 border border-[#283750] shadow-sm backdrop-blur-sm">
                    <div class="text-[10px] text-slate-400 font-mono uppercase">Akses</div>
                    <div class="text-xs font-bold text-white mt-0.5">Gratis Selamanya</div>
                </div>

            </div>


            <!-- CTA -->
            <div class="flex flex-wrap items-center justify-between gap-4 pt-2">

                <div class="flex flex-wrap items-center gap-3">

                    <a href="#generator-form"
                       class="px-5 py-3 bg-brand-500 hover:bg-brand-600 text-slate-950 font-bold text-xs uppercase tracking-wider rounded-md transition shadow-md cursor-pointer inline-flex items-center gap-2">

                        <span>Buat Program Lari Saya</span>
                        <span class="text-base leading-none">&darr;</span>

                    </a>


                    <a href="#panduan-vdot"
                       class="px-4 py-3 bg-[#1C273B]/90 hover:bg-[#283750] text-slate-200 hover:text-white font-semibold text-xs uppercase tracking-wider rounded-md border border-[#3A4C6D] transition cursor-pointer">

                        Pelajari Cara Kerja VDOT

                    </a>

                </div>


                <!-- Slider Indicator -->
                <div class="flex items-center gap-2">

                    <button v-for="(slide, idx) in heroSlides"
                            :key="'slide-dot-' + idx"
                            @click="setHeroSlide(idx)"
                            type="button"
                            :class="activeHeroSlide === idx ? 'w-6 bg-brand-500' : 'w-2 bg-slate-500/60 hover:bg-slate-300'"
                            class="h-1.5 rounded-full transition-all duration-300 cursor-pointer"
                            :title="'Slide ' + (idx + 1)">
                    </button>

                </div>

            </div>

        </div>


        <!-- Right Column -->
        <div class="lg:col-span-5">

            <div class="p-5 rounded-lg bg-[#131B2D]/95 border border-[#283750] shadow-2xl backdrop-blur-sm space-y-4">

                <div class="flex items-center justify-between pb-3 border-b border-slate-700/80">

                    <div>
                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-wider block">
                            Sistem Latihan
                        </span>

                        <h2 class="text-sm font-bold text-white">
                            Generator Program Lari VDOT
                        </h2>
                    </div>


                    <span class="text-[10px] font-mono font-bold text-brand-500 bg-brand-500/10 border border-brand-500/30 px-2 py-0.5 rounded">
                        v2.0
                    </span>

                </div>


                <div class="space-y-2.5 text-xs">


                    <div class="flex items-center justify-between p-2.5 rounded bg-[#1C273B] border border-[#2A3952]">
                        <span class="text-slate-300">
                            Target Lomba
                        </span>

                        <span class="font-mono font-bold text-white">
                            5K • 10K • 21K • Marathon
                        </span>
                    </div>


                    <div class="flex items-center justify-between p-2.5 rounded bg-[#1C273B] border border-[#2A3952]">
                        <span class="text-slate-300">
                            Data Awal
                        </span>

                        <span class="font-mono font-bold text-slate-200">
                            Personal Best / Tes Cooper
                        </span>
                    </div>


                    <div class="flex items-center justify-between p-2.5 rounded bg-[#1C273B] border border-[#2A3952]">
                        <span class="text-slate-300">
                            Zona Pace
                        </span>

                        <span class="font-mono font-bold text-brand-500">
                            Easy • Tempo • Interval
                        </span>
                    </div>


                    <div class="flex items-center justify-between p-2.5 rounded bg-[#1C273B] border border-[#2A3952]">
                        <span class="text-slate-300">
                            Pendukung
                        </span>

                        <span class="font-mono font-bold text-emerald-400">
                            Strength • Recovery
                        </span>
                    </div>


                </div>


                <div class="p-3 rounded bg-[#0E1523] border border-[#23324A] text-[11px] text-slate-300 leading-relaxed">

                    Program latihan dibuat berdasarkan data performa untuk membantu mengatur pace,
                    intensitas, dan progres latihan menuju target lomba.

                </div>


            </div>

        </div>

    </div>

</header>

    <main id="generator-form" class="relative z-10 max-w-5xl mx-auto px-4 pt-10 pb-8">
        
        <!-- Form Section Header in Step 1 -->
        <div v-if="step === 1" class="mb-6 pb-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-white tracking-tight">Formulir Parameter Program Latihan</h2>
                <p class="text-xs text-slate-300 mt-1">Lengkapi data tolok ukur awal dan target lomba untuk menghasilkan periodisasi yang realistis.</p>
            </div>
            <div class="text-xs font-mono text-slate-300 bg-[#131B2D] px-3 py-1.5 rounded-md border border-[#283750] self-start sm:self-auto">
                Langkah 1 dari 2
            </div>
        </div>

        <transition name="fade" mode="out-in">
            
            <!-- Step 1: 2-Column Professional Athletic Form -->
            <div v-if="step === 1" key="form" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- Left Column: Benchmark & Target (7 cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <!-- Card 1: Benchmark & Parameter Test / PB -->
                        <div class="card-dark p-5 rounded-lg border border-[#283750]">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-700/80">
                                <div>
                                    <h3 class="text-base font-semibold text-white">1. Tolok Ukur Kebugaran Awal</h3>
                                    <p class="text-xs text-slate-300 mt-0.5">Hasil tes kebugaran atau PB terkini untuk kalkulasi VDOT</p>
                                </div>
                                <div v-if="current_vdot && current_vdot > 0" class="text-right">
                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider block">Estimasi VDOT</span>
                                    <span class="text-sm font-mono font-bold text-brand-500 bg-brand-500/10 border border-brand-500/20 px-2 py-0.5 rounded">
                                        @{{ current_vdot.toFixed(1) }}
                                    </span>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="label-text">Jarak Parameter Test / PB</label>
                                    <select v-model="form.pb_distance" class="input-field cursor-pointer">
                                        <option value="5k">5 Kilometer (5K)</option>
                                        <option value="10k">10 Kilometer (10K)</option>
                                        <option value="21k">Half Marathon (21.1K)</option>
                                        <option value="42k">Full Marathon (42.2K)</option>
                                        <option value="cooper12">Cooper Test (Lari Maksimal 12 Menit)</option>
                                        <option value="balke15">Balke Test (Lari Maksimal 15 Menit)</option>
                                    </select>
                                </div>

                                <!-- Standard Time Input -->
                                <div v-if="form.pb_distance !== 'cooper12' && form.pb_distance !== 'balke15'">
                                    <label class="label-text">Waktu Tempuh Parameter Test (Jam : Menit : Detik)</label>
                                    <div class="grid grid-cols-3 gap-2.5">
                                        <div>
                                            <input v-model.number="pb_hours" type="number" min="0" max="99" class="input-field text-center font-mono font-bold" placeholder="00">
                                            <span class="text-[10px] text-slate-300 text-center block mt-1">Jam</span>
                                        </div>
                                        <div>
                                            <input v-model.number="pb_minutes" type="number" min="0" max="59" class="input-field text-center font-mono font-bold" placeholder="00">
                                            <span class="text-[10px] text-slate-300 text-center block mt-1">Menit</span>
                                        </div>
                                        <div>
                                            <input v-model.number="pb_seconds" type="number" min="0" max="59" class="input-field text-center font-mono font-bold" placeholder="00">
                                            <span class="text-[10px] text-slate-300 text-center block mt-1">Detik</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cooper/Balke Distance Input -->
                                <div v-else>
                                    <label class="label-text">Jarak Tempuh Hasil Tes (Meter)</label>
                                    <div class="relative">
                                        <input v-model.number="pb_distance_meters" type="number" min="100" max="9999" class="input-field font-mono font-bold" placeholder="Contoh: 2400">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-300 font-mono">meter</span>
                                    </div>
                                    <p class="text-[11px] text-slate-300 mt-1.5 leading-normal">Standar tes 12 menit: 2.000m - 2.800m untuk rekreasional, >3.000m untuk terlatih.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Target Lomba & Kalender -->
                        <div class="card-dark p-5 rounded-lg border border-[#283750]">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-700/80">
                                <div>
                                    <h3 class="text-base font-semibold text-white">2. Target Lomba & Kalender</h3>
                                    <p class="text-xs text-slate-300 mt-0.5">Jarak sasaran, waktu race, dan batas durasi persiapan</p>
                                </div>
                                <span v-if="realism" :class="realism.color" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border">
                                    @{{ realism.label }}
                                </span>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="label-text">Target Jarak Lomba</label>
                                    <select v-model="form.target_distance" @change="recommendMileage" class="input-field cursor-pointer">
                                        <option value="5k">5K (5 Kilometer)</option>
                                        <option value="10k">10K (10 Kilometer)</option>
                                        <option value="21k">Half Marathon (21.0975 Km)</option>
                                        <option value="42k">Full Marathon (42.195 Km)</option>
                                        <option value="cooper12">Cooper Test 12 Menit</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="label-text">Tanggal Mulai Latihan</label>
                                        <input v-model="form.start_date" type="date" class="input-field font-mono">
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="label-text !mb-0">Tanggal Race / Target Selesai</label>
                                            <button v-if="recommendedTargetDate && form.target_date !== recommendedTargetDate" 
                                                    @click="applyRecommendedTargetDate" 
                                                    type="button" 
                                                    class="text-[10px] font-semibold text-brand-500 hover:underline cursor-pointer">
                                                Set @{{ recommendedWeeks }} Mgg
                                            </button>
                                        </div>
                                        <input v-model="form.target_date" type="date" class="input-field font-mono">
                                    </div>
                                </div>

                                <div>
                                    <label class="label-text">Target Waktu Finish (Jam : Menit : Detik)</label>
                                    <div class="grid grid-cols-3 gap-2.5">
                                        <div>
                                            <input v-model.number="goal_hours" type="number" min="0" max="99" class="input-field text-center font-mono font-bold" placeholder="00">
                                            <span class="text-[10px] text-slate-300 text-center block mt-1">Jam</span>
                                        </div>
                                        <div>
                                            <input v-model.number="goal_minutes" type="number" min="0" max="59" class="input-field text-center font-mono font-bold" placeholder="00">
                                            <span class="text-[10px] text-slate-300 text-center block mt-1">Menit</span>
                                        </div>
                                        <div>
                                            <input v-model.number="goal_seconds" type="number" min="0" max="59" class="input-field text-center font-mono font-bold" placeholder="00">
                                            <span class="text-[10px] text-slate-300 text-center block mt-1">Detik</span>
                                        </div>
                                    </div>
                                    <p v-if="realism" class="text-xs text-slate-200 mt-2.5 bg-[#0E1523] p-3 rounded-md border border-[#23324A] leading-relaxed">
                                        @{{ realism.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Training Load & Profile (5 cols) -->
                    <div class="lg:col-span-5 space-y-6">

                        <!-- Card 3: Beban & Jadwal Latihan -->
                        <div class="card-dark p-5 rounded-lg border border-[#283750]">
                            <div class="mb-4 pb-3 border-b border-slate-700/80">
                                <h3 class="text-base font-semibold text-white">3. Beban & Jadwal Latihan</h3>
                                <p class="text-xs text-slate-300 mt-0.5">Alokasi volume mingguan dan preferensi sesi</p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="label-text !mb-0">Mileage Mingguan Puncak (Km)</label>
                                        <span class="text-[10px] font-mono text-brand-500">Saran: @{{ idealMileage }} km</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input v-model.number="form.weekly_mileage" type="number" min="15" max="150" step="1" class="input-field font-mono font-bold">
                                        <button type="button" @click="recommendMileage" class="px-2.5 py-2 bg-[#1C273B] hover:bg-[#283750] text-slate-200 text-xs rounded-md border border-[#3A4C6D] whitespace-nowrap transition cursor-pointer">
                                            Reset Saran
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="label-text">Frekuensi Latihan Mingguan</label>
                                    <div class="grid grid-cols-5 gap-1.5">
                                        <button v-for="f in [3,4,5,6,7]" :key="f" type="button" @click="form.frequency = f" 
                                                :class="form.frequency === f ? 'bg-brand-500 text-slate-950 font-bold border-brand-500' : 'bg-[#1C273B] text-slate-200 border-[#3A4C6D] hover:border-slate-400'"
                                                class="py-2 rounded-md text-xs transition border text-center cursor-pointer">
                                            @{{ f }} Hari
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="label-text">Level Pelari</label>
                                        <select v-model="form.runner_level" class="input-field cursor-pointer">
                                            <option value="beginner">Pemula (Beginner)</option>
                                            <option value="intermediate">Menengah (Intermediate)</option>
                                            <option value="advanced">Mahir (Advanced)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="label-text">Hari Long Run</label>
                                        <select v-model="form.long_run_day" class="input-field cursor-pointer">
                                            <option value="saturday">Sabtu</option>
                                            <option value="sunday">Minggu</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Strength Training -->
                                <div class="p-3.5 bg-[#0E1523] rounded-md border border-[#23324A] space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" v-model="form.include_strength" class="w-4 h-4 accent-brand-500 rounded border-slate-600 bg-[#1C273B]">
                                            <span class="text-xs font-semibold text-slate-200">Sertakan Strength Training</span>
                                        </label>
                                        <span v-if="form.include_strength" class="text-[9px] font-mono text-emerald-400 bg-emerald-950/40 border border-emerald-500/30 px-1.5 py-0.5 rounded">2x/Mgg</span>
                                    </div>
                                    <div v-if="form.include_strength">
                                        <select v-model="form.strength_type" class="input-field text-xs cursor-pointer">
                                            <option value="bodyweight">Rumah / Bodyweight (Tanpa Alat)</option>
                                            <option value="gym">Gym / Weighted (Beban & Dumbbell)</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Tropical Adjustment -->
                                <div class="p-3.5 bg-[#0E1523] rounded-md border border-[#23324A]">
                                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                                        <input type="checkbox" v-model="form.is_tropical" class="w-4 h-4 mt-0.5 accent-brand-500 rounded border-slate-600 bg-[#1C273B]">
                                        <div>
                                            <span class="text-xs font-semibold text-slate-200 block">Adaptasi Iklim Tropis</span>
                                            <span class="text-[11px] text-slate-300 block mt-0.5 leading-normal">
                                                Menyesuaikan pace +10–15s/km untuk menjaga kestabilan beban kardiovaskular di suhu dan kelembapan Indonesia.
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Profil Fisik & Riwayat Cedera -->
                        <div class="card-dark p-5 rounded-lg border border-[#283750]">
                            <div class="mb-4 pb-3 border-b border-slate-700/80">
                                <h3 class="text-base font-semibold text-white">4. Profil Fisik & Riwayat Cedera</h3>
                                <p class="text-xs text-slate-300 mt-0.5">Parameter nutrisi dan rekomendasi protektif</p>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="label-text">Jenis Kelamin</label>
                                        <div class="grid grid-cols-2 gap-1 p-1 bg-[#0E1523] rounded-md border border-[#23324A]">
                                            <button type="button" @click="form.gender = 'male'" :class="form.gender === 'male' ? 'bg-[#283750] text-white font-bold' : 'text-slate-300 hover:text-white'" class="py-1.5 rounded text-xs transition cursor-pointer">Laki-laki</button>
                                            <button type="button" @click="form.gender = 'female'" :class="form.gender === 'female' ? 'bg-[#283750] text-white font-bold' : 'text-slate-300 hover:text-white'" class="py-1.5 rounded text-xs transition cursor-pointer">Perempuan</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="label-text">Usia (Tahun)</label>
                                        <input v-model.number="form.age" type="number" min="12" max="99" class="input-field font-mono font-bold">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="label-text">Tinggi Badan (cm)</label>
                                        <input v-model.number="form.height_cm" type="number" min="100" max="230" class="input-field font-mono" placeholder="170">
                                    </div>
                                    <div>
                                        <label class="label-text">Berat Badan (kg)</label>
                                        <input v-model.number="form.weight_kg" type="number" min="30" max="200" class="input-field font-mono" placeholder="65">
                                    </div>
                                </div>

                                <div>
                                    <label class="label-text">Riwayat Cedera Terkini</label>
                                    <select v-model="form.injury_history" class="input-field cursor-pointer">
                                        <option value="none">Tidak Ada (Sehat & Bugar)</option>
                                        <option value="knee">Lutut (Runner's Knee / Patella)</option>
                                        <option value="hamstring">Hamstring / Paha Belakang</option>
                                        <option value="ankle">Pergelangan Kaki (Ankle / Tendon)</option>
                                        <option value="shin">Shin Splints / Tulang Kering</option>
                                        <option value="back">Punggung Bawah (Lower Back)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit CTA -->
                        <button @click="generateProgram" :disabled="loading" 
                                class="w-full py-3.5 bg-brand-500 hover:bg-brand-600 disabled:bg-slate-800 disabled:text-slate-500 text-slate-950 font-bold text-sm rounded-md transition uppercase tracking-wider flex items-center justify-center gap-2 cursor-pointer shadow-md">
                            <span v-if="!loading">Buat Program Latihan</span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-slate-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses Perhitungan VDOT...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Results Display -->
            <div v-else-if="step === 2" key="result" class="space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-800">
                    <div>
                        <h2 class="text-xl font-bold text-white">Program Latihan @{{ form.target_distance.toUpperCase() }} Selesai Dirancang</h2>
                        <p class="text-xs text-slate-300 mt-0.5">Estimasi skor VDOT @{{ result?.vdot }} • Durasi @{{ result?.weeks }} pekan • @{{ form.frequency }} sesi/minggu</p>
                    </div>
                    <button @click="step = 1" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold rounded-md border border-slate-700 transition cursor-pointer">
                        Ubah Parameter
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                    
                    <!-- Left: Summary Sidebar (4 cols) -->
                    <div class="lg:col-span-4 space-y-5">
                        
                        <!-- Save Action Card -->
                        <div class="card-dark p-5 rounded-lg border border-slate-800">
                            <div class="flex justify-between items-center mb-3 pb-3 border-b border-slate-800">
                                <span class="text-xs text-slate-400">Skor Kebugaran VDOT</span>
                                <span class="text-2xl font-mono font-bold text-white">@{{ result?.vdot }}</span>
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
                                    class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-slate-950 font-bold text-xs uppercase tracking-wider rounded-md transition flex items-center justify-center gap-2 cursor-pointer mb-2">
                                <span>@{{ saving ? 'Menyimpan...' : 'Simpan ke Kalender Lari' }}</span>
                            </button>
                            <button @click="step = 1" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-slate-300 text-xs font-semibold rounded-md border border-slate-800 transition cursor-pointer">
                                Ubah Parameter
                            </button>
                        </div>

                        <!-- Training Paces & HR Zones Card -->
                        <div class="card-dark p-5 rounded-lg border border-slate-800">
                            <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">Target Pace & Zona HR</h3>

                            <!-- Run-Walk Beginner Educational Notice -->
                            <div v-if="result?.paces?.is_run_walk" class="p-3 mb-3 rounded-md bg-teal-950/40 border border-teal-500/30 text-[11px] text-teal-200/90 leading-relaxed">
                                <div class="font-bold text-teal-300 mb-1">
                                    Metode Lari-Jalan (Run-Walk) Aktif
                                </div>
                                Berdasarkan tingkat kebugaran awal, sesi lari santai Anda dikalibrasi ke ritme lari alami (<span class="text-white font-mono font-bold">Pace 8:00 - 8:30</span>) diselingi jalan cepat aktif (<span class="text-white font-mono font-bold">Pace 10:30 - 11:30</span>) untuk melindungi sendi dan menjaga detak jantung aerobik Zona 2.
                            </div>

                            <div class="space-y-2">
                                <div v-for="(pace, type) in displayPaces" :key="type" class="p-2.5 rounded-md bg-slate-900 border border-slate-800/80 space-y-1">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-xs uppercase" :class="getPaceColor(type)">
                                            @{{ getPaceLabel(type) }}
                                        </span>
                                        <span class="font-mono font-bold text-xs text-white">@{{ formatPace(pace, type) }}</span>
                                    </div>
                                    <div v-if="result?.hr_zones && result.hr_zones[type]" class="flex justify-between items-center text-[10px] text-slate-400 pt-1 border-t border-slate-800/50 font-mono">
                                        <span>Target HR</span>
                                        <span class="text-slate-300">@{{ result.hr_zones[type].min }}–@{{ result.hr_zones[type].max }} BPM</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Athlete Profile & Nutrition Card -->
                        <div v-if="result?.bmi || result?.protein_recommendation || bmi || proteinRecommendation" class="card-dark p-5 rounded-lg border border-slate-800 space-y-3">
                            <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider pb-2 border-b border-slate-800">Profil & Nutrisi</h3>

                            <div v-if="result?.bmi || bmi" class="p-2.5 rounded-md bg-slate-900 border border-slate-800 flex justify-between items-center">
                                <div>
                                    <div class="text-[10px] text-slate-400 uppercase tracking-wider">Indeks Massa Tubuh (BMI)</div>
                                    <div class="text-base font-bold text-white font-mono">@{{ result?.bmi || bmi }} <span class="text-xs text-slate-400 font-normal">kg/m²</span></div>
                                </div>
                                <div v-if="bmiCategory" :class="bmiCategory.badgeClass + ' ' + bmiCategory.color" class="px-2 py-0.5 rounded border text-[10px] font-bold uppercase tracking-wider">
                                    @{{ bmiCategory.label }}
                                </div>
                            </div>

                            <div v-if="result?.protein_recommendation || proteinRecommendation" class="p-2.5 rounded-md bg-slate-900 border border-slate-800 space-y-1">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400">Target Protein Harian</span>
                                    <span class="font-bold text-indigo-400 font-mono">
                                        @{{ (result?.protein_recommendation || proteinRecommendation)?.min }}–@{{ (result?.protein_recommendation || proteinRecommendation)?.max }} g/hari
                                    </span>
                                </div>
                                <p class="text-[10px] text-slate-400 italic leading-tight">
                                    @{{ (result?.protein_recommendation || proteinRecommendation)?.note }}
                                </p>
                            </div>

                            <div v-if="form.injury_history && form.injury_history !== 'none'" class="p-2.5 rounded-md bg-amber-950/30 border border-amber-500/20 text-[11px] text-amber-200/90 leading-relaxed">
                                <strong>Catatan Cedera (@{{ form.injury_history.toUpperCase() }}):</strong> Latihan strength dan volume lari telah disesuaikan dengan instruksi protektif.
                            </div>
                        </div>
                    </div>

                    <!-- Right: Weekly Schedule Preview (8 cols) -->
                    <div class="lg:col-span-8 space-y-5">
                        <div v-for="(weekSessions, weekNum) in sessionsByWeek" :key="weekNum" class="card-dark p-5 rounded-lg border border-slate-800">
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
                                <span class="text-[11px] text-slate-400 font-mono">@{{ weekSessions.length }} Sesi</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-2">
                                <div v-for="day in weekSessions" :key="day.day" 
                                     class="p-2.5 rounded-md border min-h-[110px] flex flex-col justify-between transition border-slate-800"
                                     :class="getSessionClass(day.type)">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Hari @{{ day.day }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-[10px] font-bold text-white leading-tight mb-1 uppercase tracking-tight">@{{ day.type.replace('_', ' ') }}</h4>
                                        <p class="text-xs font-bold text-white font-mono">@{{ day.distance }} <span class="text-[9px] font-normal text-slate-400">KM</span></p>
                                        <p v-if="day.target_pace" class="text-[9px] font-mono text-brand-500 mt-0.5">@{{ day.target_pace }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </main>

    <!-- Auth Modal Integration (handled by layout pacerhub) -->
</div>

<!-- Rich Crawlable SEO Knowledge & Guide Section (Server-Rendered Semantic Content) -->
<section id="panduan-vdot" class="bg-[#080A0D] border-t border-slate-800 text-slate-300 py-16 px-4">
    <div class="max-w-5xl mx-auto space-y-12">
        
        <!-- Breadcrumb Navigation -->
        <nav aria-label="Breadcrumb" class="text-xs text-slate-400">
            <ol class="flex items-center gap-2 flex-wrap">
                <li>
                    <a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a>
                </li>
                <li class="text-slate-600">/</li>
                <li>
                    <a href="{{ route('tools.index') }}" class="hover:text-white transition">Running Tools</a>
                </li>
                <li class="text-slate-600">/</li>
                <li class="text-slate-200 font-semibold" aria-current="page">Buat Program Lari</li>
            </ol>
        </nav>

        <!-- Section 1: Scientific Foundation (Jack Daniels' VDOT Formula) -->
        <article class="space-y-4">
            <h2 class="text-xl font-bold text-white tracking-tight">Panduan Ilmiah Buat Program Lari Berbasis Formula VDOT Jack Daniels</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                Sebelum Anda <strong>buat program lari</strong> untuk mencapai target perlombaan, penting memastikan bahwa <strong>program lari</strong> yang Anda jalani berlandaskan kapasitas fisiologis riil tubuh saat ini. Di RuangLari, Anda dapat menyusun <strong>program latihan lari</strong> terstruktur menggunakan formula empiris karya Dr. Jack Daniels (penulis buku legendaris <em>Daniels' Running Formula</em>). Konsep <strong>VDOT</strong> menyelaraskan konsumsi oksigen maksimal (<span class="font-mono text-white">VO2 Max</span>) dengan efisiensi mekanik gerak (<span class="font-mono text-white">Running Economy</span>) agar setiap sesi latihan memberikan hasil optimal tanpa risiko cedera.
            </p>
            <p class="text-sm text-slate-300 leading-relaxed">
                Banyak pelari pemula hingga maratonis mengalami cedera karena mengikuti <strong>program lari</strong> yang terlalu agresif dan tidak cocok dengan kondisi fisik awal. Melalui alat <strong>buat program lari gratis</strong> ini, Anda hanya perlu memasukkan catatan waktu Personal Best (PB) atau hasil uji lari terbaru. Sistem cerdas kami secara otomatis mengalkulasi 5 zona pace spesifik, jadwal periodisasi mingguan, serta metode run-walk protektif untuk memastikan progres latihan Anda terukur dan konsisten.
            </p>

            <!-- Table of 5 Training Paces -->
            <div class="mt-6 overflow-x-auto rounded-lg border border-slate-800 bg-[#12161F]">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4">Zona Pace</th>
                            <th class="py-3 px-4 font-mono">% VO2 Max</th>
                            <th class="py-3 px-4 font-mono">% HR Max</th>
                            <th class="py-3 px-4">Tujuan & Stimulus Fisiologis</th>
                            <th class="py-3 px-4">Contoh Penggunaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-bold text-emerald-400">Easy (E)</td>
                            <td class="py-3 px-4 font-mono">59% - 74%</td>
                            <td class="py-3 px-4 font-mono">65% - 78%</td>
                            <td class="py-3 px-4 leading-normal">Membangun kapilarisasi otot, memperbanyak mitokondria, memperkuat ligamen, dan pemulihan aktif.</td>
                            <td class="py-3 px-4 font-mono text-slate-400">Easy Run, Long Run Dasar</td>
                        </tr>
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-bold text-blue-400">Marathon (M)</td>
                            <td class="py-3 px-4 font-mono">75% - 84%</td>
                            <td class="py-3 px-4 font-mono">79% - 88%</td>
                            <td class="py-3 px-4 leading-normal">Melatih efisiensi penggunaan glikogen dan pembakaran asam lemak pada kecepatan target lomba.</td>
                            <td class="py-3 px-4 font-mono text-slate-400">Long Run Spesifik Marathon</td>
                        </tr>
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-bold text-amber-400">Threshold (T)</td>
                            <td class="py-3 px-4 font-mono">83% - 88%</td>
                            <td class="py-3 px-4 font-mono">88% - 92%</td>
                            <td class="py-3 px-4 leading-normal">Meningkatkan kapasitas tubuh membersihkan laktat darah (Lactate Clearance) dan daya tahan ambang batas.</td>
                            <td class="py-3 px-4 font-mono text-slate-400">Tempo Run 20 Menit, Cruise Intervals</td>
                        </tr>
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-bold text-rose-400">Interval (I)</td>
                            <td class="py-3 px-4 font-mono">95% - 100%</td>
                            <td class="py-3 px-4 font-mono">95% - 100%</td>
                            <td class="py-3 px-4 leading-normal">Mengembangkan kapasitas VO2 Max puncak dan kekuatan pompa stroke volume jantung.</td>
                            <td class="py-3 px-4 font-mono text-slate-400">Repeats 800m - 1200m (3-5 Menit)</td>
                        </tr>
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-bold text-[#FC4C02]">Repetition (R)</td>
                            <td class="py-3 px-4 font-mono">> 105%</td>
                            <td class="py-3 px-4 font-mono">Anaerobik</td>
                            <td class="py-3 px-4 leading-normal">Mengoptimalkan efisiensi langkah (running economy), kecepatan neuromuskular, dan irama cadence tinggi.</td>
                            <td class="py-3 px-4 font-mono text-slate-400">Repeats 200m - 400m dengan istirahat penuh</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>

        <!-- Section 2: Periodization Distance Guides -->
        <article class="space-y-4">
            <h2 class="text-xl font-bold text-white tracking-tight">Panduan Periodisasi Berdasarkan Jarak Lomba</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                Setiap nomor lomba membutuhkan adaptasi sistem energi dominan yang berbeda. Generator RuangLari merancang periodisasi bertahap (Fase Fondasi, Fase Pengembangan Kualitas, Fase Puncak, hingga Fase Tapering):
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                
                <!-- 5K Card -->
                <div class="card-dark p-5 rounded-lg border border-slate-800 space-y-2">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base font-semibold text-white">Program Latihan 5K (Speed & Aerobic Power)</h3>
                        <span class="text-[10px] font-mono text-brand-500 bg-brand-500/10 border border-brand-500/20 px-2 py-0.5 rounded">8 - 10 Pekan</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Lomba 5 Kilometer membutuhkan kapasitas VO2 Max tinggi dan toleransi laktat yang kuat. Program memadukan interval 400m–800m dengan lari santai untuk menjaga kaki tetap bertenaga tanpa mengorbankan stamina akhir.
                    </p>
                    <div class="pt-2 text-xs">
                        <a href="{{ route('landing.program-lari-5k') }}" class="text-brand-500 hover:underline font-semibold">Pelajari Program Latihan 5K Lengkap →</a>
                    </div>
                </div>

                <!-- 10K Card -->
                <div class="card-dark p-5 rounded-lg border border-slate-800 space-y-2">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base font-semibold text-white">Program Latihan 10K (Threshold & Stamina)</h3>
                        <span class="text-[10px] font-mono text-brand-500 bg-brand-500/10 border border-brand-500/20 px-2 py-0.5 rounded">10 - 12 Pekan</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Jarak 10K merupakan jembatan antara kecepatan murni dan ketahanan aerobik. Sesi tempo run pada zona Threshold (T) menjadi pilar utama untuk memperpanjang daya tahan laktat di paruh kedua lomba.
                    </p>
                    <div class="pt-2 text-xs">
                        <a href="{{ route('landing.program-lari-10k') }}" class="text-brand-500 hover:underline font-semibold">Pelajari Program Latihan 10K Lengkap →</a>
                    </div>
                </div>

                <!-- 21K Half Marathon Card -->
                <div class="card-dark p-5 rounded-lg border border-slate-800 space-y-2">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base font-semibold text-white">Program Latihan Half Marathon (21.1K)</h3>
                        <span class="text-[10px] font-mono text-brand-500 bg-brand-500/10 border border-brand-500/20 px-2 py-0.5 rounded">12 - 14 Pekan</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Half Marathon menguji efisiensi metabolisme lemak dan ketahanan muskuloskeletal. Program menekankan progresivitas long run hingga 16–19 km dengan sisipan segmen Marathon Pace (M) untuk membiasakan ritme perlombaan.
                    </p>
                    <div class="pt-2 text-xs">
                        <a href="{{ route('programs.index') }}" class="text-brand-500 hover:underline font-semibold">Lihat Katalog Program Half Marathon →</a>
                    </div>
                </div>

                <!-- 42K Full Marathon Card -->
                <div class="card-dark p-5 rounded-lg border border-slate-800 space-y-2">
                    <div class="flex justify-between items-center">
                        <h3 class="text-base font-semibold text-white">Program Latihan Full Marathon (42.2K)</h3>
                        <span class="text-[10px] font-mono text-brand-500 bg-brand-500/10 border border-brand-500/20 px-2 py-0.5 rounded">16 - 20 Pekan</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Persiapan marathon berfokus pada adaptasi simpanan glikogen, simulasi hidrasi/nutrisi gel, serta manajemen kelelahan. Dilengkapi minggu pemulihan berkala (de-load week) dan masa tapering 3 pekan sebelum hari perlombaan.
                    </p>
                    <div class="pt-2 text-xs">
                        <a href="{{ route('programs.index') }}" class="text-brand-500 hover:underline font-semibold">Lihat Katalog Program Marathon →</a>
                    </div>
                </div>

            </div>
        </article>

        <!-- Section 3: Run-Walk Method for Beginners -->
        <article class="space-y-4">
            <h2 class="text-xl font-bold text-white tracking-tight">Metode Lari-Jalan (Run-Walk): Solusi Latihan Bebas Cedera untuk Pelari Pemula</h2>
            <p class="text-sm text-slate-300 leading-relaxed">
                Salah satu kendala terbesar pelari pemula adalah memaksakan lari non-stop dengan kecepatan sangat lambat (pace 11:00 hingga 13:00 /km). Secara biomekanik, berlari pada kecepatan tersebut menyebabkan kontak kaki dengan tanah terlalu lama (ground contact time tinggi), memicu goncangan vertikal berlebih pada persendian patela (runner's knee), serta peradangan periosteum tulang kering (shin splints).
            </p>
            <p class="text-sm text-slate-300 leading-relaxed">
                Platform RuangLari mengadopsi <strong>Metode Run-Walk (Lari-Jalan Berirama)</strong> terstruktur untuk skor VDOT pemula. Pelari diarahkan berlari santai dengan mekanika alami pada <span class="font-mono text-white font-bold">Pace 08:00 - 08:30 /km</span>, kemudian diselingi interval jalan cepat aktif pada <span class="font-mono text-white font-bold">Pace 10:30 - 11:30 /km</span>. Pendekatan ini menjaga detak jantung stabil di Zona 2 aerobik murni, memulihkan otot secara mikro, dan meningkatkan konsistensi jarak tempuh mingguan secara aman.
            </p>
            <div class="pt-1 text-xs">
                <a href="{{ route('landing.program-lari-5k-pemula') }}" class="text-brand-500 hover:underline font-semibold">Panduan Lengkap Program Lari 5K Pemula Tanpa Cedera →</a>
            </div>
        </article>

        <!-- Section 4: FAQ (Frequently Asked Questions) -->
        <article class="space-y-4">
            <h2 class="text-xl font-bold text-white tracking-tight">Pertanyaan yang Sering Diajukan (FAQ)</h2>
            <div class="space-y-3">
                
                <details class="faq-item card-dark p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-sm text-white select-none">
                        <span>Bagaimana cara kerja kalkulator VDOT dalam membuat program latihan lari?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3 pt-3 border-t border-slate-800/80">
                        Kalkulator VDOT mengukur tingkat kebugaran aerobik (VO2 Max fungsional) berdasarkan catatan waktu terbaik (Personal Best) terkini pada jarak standar (5K, 10K, Half Marathon, Full Marathon, atau tes waktu Cooper/Balke). Dari nilai VDOT ini, sistem menghitung 5 intensitas pace spesifik (Easy, Marathon, Threshold, Interval, Repetition) sesuai metodologi ilmiah Dr. Jack Daniels untuk menstimulasi adaptasi kardiovaskular secara optimal tanpa risiko overtraining.
                    </p>
                </details>

                <details class="faq-item card-dark p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-sm text-white select-none">
                        <span>Berapa lama durasi persiapan ideal untuk 5K, 10K, Half Marathon, dan Full Marathon?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3 pt-3 border-t border-slate-800/80">
                        Durasi persiapan terstruktur yang direkomendasikan adalah 8–10 pekan untuk 5K, 10–12 pekan untuk 10K, 12–14 pekan untuk Half Marathon (21.1K), dan 16–20 pekan untuk Full Marathon (42.2K). Rentang waktu ini memastikan adaptasi tendon, ligamen, dan kapasitas simpanan glikogen otot terbangun secara bertahap dengan fase de-load dan tapering menjelang hari perlombaan.
                    </p>
                </details>

                <details class="faq-item card-dark p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-sm text-white select-none">
                        <span>Apakah program latihan ini aman untuk pelari pemula yang baru mulai lari?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3 pt-3 border-t border-slate-800/80">
                        Sangat aman. Untuk pelari pemula dengan tingkat kebugaran awal (VDOT di bawah 32), sistem secara otomatis mengaktifkan Metode Run-Walk (Lari-Jalan Berirama). Pelari tidak dipaksa berlari lambat terus-menerus pada pace 11–13 min/km yang berisiko merusak postur, melainkan berlari pada ritme alami (08:00 - 08:30 /km) diselingi interval jalan cepat aktif untuk menjaga detak jantung aerobik Zona 2 dan melindungi sendi lutut serta tulang kering.
                    </p>
                </details>

                <details class="faq-item card-dark p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-sm text-white select-none">
                        <span>Mengapa ada opsi penyesuaian iklim tropis Indonesia?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3 pt-3 border-t border-slate-800/80">
                        Berlari di lingkungan tropis dengan suhu tinggi dan kelembapan di atas 75% memicu kenaikan detak jantung lebih cepat (cardiac drift) akibat beban termoregulasi tubuh. Fitur adaptasi tropis melonggarkan target pace sebesar 10–15 detik/km agar beban fisiologis pada sistem kardiovaskular tetap sesuai dengan tujuan latihan tanpa memicu kelelahan ekstrem.
                    </p>
                </details>

                <details class="faq-item card-dark p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-sm text-white select-none">
                        <span>Bagaimana cara menyimpan dan menyinkronkan program ke kalender lari?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3 pt-3 border-t border-slate-800/80">
                        Setelah menekan tombol 'Buat Program Latihan' dan meninjau hasil kalkulasi, klik tombol 'Simpan ke Kalender Lari'. Jika Anda telah masuk (login), seluruh jadwal latihan harian—termasuk jarak, target pace, dan jenis sesi—akan tersinkronisasi otomatis ke dashboard Kalender Lari Anda.
                    </p>
                </details>

                <details class="faq-item card-dark p-4 rounded-lg border border-slate-800 transition">
                    <summary class="flex justify-between items-center cursor-pointer font-semibold text-sm text-white select-none">
                        <span>Apakah generator program lari ini 100% gratis digunakan?</span>
                        <svg class="faq-chevron w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <p class="text-xs text-slate-300 leading-relaxed mt-3 pt-3 border-t border-slate-800/80">
                        Ya, Anda dapat buat program lari secara 100% gratis tanpa biaya langganan. Fitur ini mencakup penentuan target pace VDOT, periodisasi jadwal latihan harian (5K, 10K, Half Marathon, Full Marathon), panduan nutrisi protein, hingga sinkronisasi langsung ke kalender lari pribadi Anda.
                    </p>
                </details>

            </div>
        </article>

        <!-- Section 5: Internal Link Hub (Ecosystem Navigation) -->
        <article class="p-6 card-dark rounded-lg border border-slate-800 space-y-4">
            <h2 class="text-base font-semibold text-white">Jelajahi Ekosistem Lari RuangLari</h2>
            <p class="text-xs text-slate-400 leading-relaxed">
                Tingkatkan pengalaman latihan Anda dengan berbagai fitur dan alat bantu lari lainnya yang tersedia di RuangLari:
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 pt-1 text-xs">
                <a href="{{ route('landing.program-lari-5k') }}" class="p-3 bg-slate-900 hover:bg-slate-800 rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    Program 5K
                </a>
                <a href="{{ route('landing.program-lari-5k-pemula') }}" class="p-3 bg-slate-900 hover:bg-slate-800 rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    5K Pemula
                </a>
                <a href="{{ route('landing.program-lari-10k') }}" class="p-3 bg-slate-900 hover:bg-slate-800 rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    Program 10K
                </a>
                <a href="{{ route('tools.index') }}" class="p-3 bg-slate-900 hover:bg-slate-800 rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    Running Tools
                </a>
                <a href="{{ route('gpx.index') }}" class="p-3 bg-slate-900 hover:bg-slate-800 rounded border border-slate-800 text-slate-200 hover:text-white font-medium transition block text-center">
                    Database Rute GPX
                </a>
            </div>
        </article>

    </div>
</section>
@endsection

@push('scripts')
<script>
    const { createApp, ref, reactive, computed, onMounted, watch } = Vue;

    createApp({
        setup() {
            // Hero Background Slider (Ken Burns Zoom-Out effect)
            const heroSlides = [
                '{{ file_exists(public_path("images/hero/vdot-runner-training.jpg")) ? asset("images/hero/vdot-runner-training.jpg") : asset("images/hero/marathon-hero-cinematic.jpg") }}',
                '{{ asset("images/hero/marathon-hero-cinematic.jpg") }}',
                '{{ asset("images/hero/runner-hero.jpg") }}'
            ];
            const activeHeroSlide = ref(0);
            let heroSlideTimer = null;

            const startHeroSlideTimer = () => {
                if (heroSlideTimer) clearInterval(heroSlideTimer);
                heroSlideTimer = setInterval(() => {
                    activeHeroSlide.value = (activeHeroSlide.value + 1) % heroSlides.length;
                }, 8000);
            };

            const setHeroSlide = (idx) => {
                activeHeroSlide.value = idx;
                startHeroSlideTimer();
            };

            const step = ref(1);
            const loading = ref(false);
            const saving = ref(false);
            const result = ref(null);
            const errors = ref(null);
            const notification = ref(null);

            // Default standard benchmark PB times (5K 30m, 10K 1h, 21K 2h15m, 42K 4h30m)
            const defaultPbTimes = {
                '5k':       { h: 0, m: 30, s: 0 },
                '10k':      { h: 1, m: 0,  s: 0 },
                '21k':      { h: 2, m: 15, s: 0 },
                '42k':      { h: 4, m: 30, s: 0 },
                'cooper12': { meters: 2400 },
                'balke15':  { meters: 3000 }
            };

            const pb_hours = ref(0);
            const pb_minutes = ref(30);
            const pb_seconds = ref(0);
            const pb_distance_meters = ref(2400);

            const goal_hours = ref(0);
            const goal_minutes = ref(0);
            const goal_seconds = ref(0);

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
                weekly_mileage: 50,
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

            const showNotification = (message, type = 'success') => {
                notification.value = { message, type };
                setTimeout(() => {
                    notification.value = null;
                }, 5000);
            };

            const distanceKm = {
                '5k': 5,
                '10k': 10,
                '21k': 21.0975,
                '42k': 42.195,
                'cooper12': 2.4,
                'balke15': 3.0
            };

            const distanceMeters = {
                '5k': 5000,
                '10k': 10000,
                '21k': 21097.5,
                '42k': 42195,
                'cooper12': 2400,
                'balke15': 3000
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
                    const sec = form.pb_distance === 'balke15' ? 900 : 720;
                    return calculateVDOTFromPerformance(form.pb_distance, sec);
                }
                const t = (pb_hours.value * 3600) + (pb_minutes.value * 60) + pb_seconds.value;
                return calculateVDOTFromPerformance(form.pb_distance, t);
            });

            const target_vdot = computed(() => {
                const t = (goal_hours.value * 3600) + (goal_minutes.value * 60) + goal_seconds.value;
                return calculateVDOTFromPerformance(form.target_distance, t);
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

            let isInitializingFromParams = false;

            const applyDefaultPbTime = (distKey) => {
                if (isInitializingFromParams) return;
                const defaults = defaultPbTimes[distKey] || defaultPbTimes['5k'];
                if (distKey === 'cooper12' || distKey === 'balke15') {
                    pb_distance_meters.value = defaults.meters || 2400;
                } else {
                    pb_hours.value = defaults.h;
                    pb_minutes.value = defaults.m;
                    pb_seconds.value = defaults.s;
                }
            };

            const suggestGoalTime = () => {
                const cv = current_vdot.value;
                if (!cv || cv <= 0) return;
                const targetVdot = recommendedTargetVdot.value;
                const predictedSeconds = predictRaceTimeSeconds(targetVdot, form.target_distance);
                if (predictedSeconds > 0) {
                    goal_hours.value = Math.floor(predictedSeconds / 3600);
                    goal_minutes.value = Math.floor((predictedSeconds % 3600) / 60);
                    goal_seconds.value = Math.floor(predictedSeconds % 60);
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

            watch(current_vdot, (newVdot) => {
                if (newVdot && newVdot > 0) {
                    form.runner_level = autoDetermineRunnerLevel(newVdot);
                }
            }, { immediate: true });

            watch([() => form.start_date, () => form.target_distance], ([newStartDate, newDist], [oldStartDate, oldDist]) => {
                if (recommendedTargetDate.value && (!form.target_date || newDist !== oldDist)) {
                    applyRecommendedTargetDate();
                }
            });

            // Watch PB distance change to auto fill default standard PB time
            watch(() => form.pb_distance, (newDist) => {
                applyDefaultPbTime(newDist);
            });

            // Auto-suggest when PB or Target Distance or Runner Level changes
            watch([pb_hours, pb_minutes, pb_seconds, pb_distance_meters, () => form.pb_distance, () => form.target_distance, () => form.start_date, () => form.target_date, () => form.runner_level], () => {
                suggestGoalTime();
                recommendMileage();
            });

            onMounted(() => {
                startHeroSlideTimer();

                const params = new URLSearchParams(window.location.search);
                let dist = params.get('distance');
                const time = params.get('time');
                const meters = params.get('meters');

                if (dist || time || meters) {
                    isInitializingFromParams = true;
                    step.value = 1; // Automatically open form step when params are provided
                    setTimeout(() => {
                        const el = document.getElementById('generator-form');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 300);
                }

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
                } else {
                    applyDefaultPbTime(form.pb_distance);
                }

                if (!form.target_date) {
                    applyRecommendedTargetDate();
                }

                suggestGoalTime();
                recommendMileage();

                setTimeout(() => {
                    isInitializingFromParams = false;
                }, 500);
            });

            const realism = computed(() => {
                const cv = current_vdot.value;
                const tv = target_vdot.value;
                if (!cv || !tv) return null;
                const diff = tv - cv;
                const diffPercent = diff / cv;
                const rec = recommendedImprovementPercent.value;
                const diffLabel = Math.max(0, diffPercent) * 100;
                const recLabel = rec * 100;

                if (diff < 0) {
                    return { label: 'Mudah', color: 'bg-green-900/20 text-green-400 border-green-500/30', description: 'Target ini berada di bawah performa terbaik Anda saat ini.' };
                }
                if (diffPercent <= rec * 1.1) {
                    return { label: 'Realistis', color: 'bg-green-900/20 text-green-400 border-blue-500/30', description: `Target ini setara peningkatan sekitar ${diffLabel.toFixed(1)}% dari VDOT Anda. Rentang realistis saat ini ~${recLabel.toFixed(1)}%.` };
                }
                if (diffPercent <= rec * 1.6) {
                    return { label: 'Ambisius', color: 'bg-green-900/20 text-green-400 border-orange-500/30', description: `Peningkatan sekitar ${diffLabel.toFixed(1)}% tergolong menantang untuk jarak ${form.target_distance.toUpperCase()}.` };
                }
                return { label: 'Sangat Ambisius', color: 'bg-red-900/20 text-red-400 border-red-500/30', description: `Peningkatan sekitar ${diffLabel.toFixed(1)}% terlalu agresif untuk target ${form.target_distance.toUpperCase()}.` };
            });

            const idealMileage = computed(() => {
                // Realistic Daniels / Pfitzinger Recreational Coach Weekly Mileage Standards
                const baseMileageMap = {
                    '5k':       { beginner: 25, intermediate: 35, advanced: 50 },
                    '10k':      { beginner: 30, intermediate: 45, advanced: 60 },
                    '21k':      { beginner: 35, intermediate: 50, advanced: 70 },
                    '42k':      { beginner: 45, intermediate: 65, advanced: 85 },
                    'cooper12': { beginner: 25, intermediate: 35, advanced: 50 },
                    'balke15':  { beginner: 25, intermediate: 35, advanced: 50 }
                };

                const level = form.runner_level || 'intermediate';
                const dist = form.target_distance || '10k';
                let base = baseMileageMap[dist]?.[level] || 45;

                // Dynamically adjust mileage recommendation based on current VDOT
                const cv = current_vdot.value;
                if (cv && cv > 0) {
                    if (cv < 35) {
                        base = Math.max(20, base - 5);
                    } else if (cv >= 55) {
                        base = base + 10;
                    } else if (cv >= 48) {
                        base = base + 5;
                    }
                }

                const rounded = Math.round(base / 5) * 5;
                return Math.min(120, Math.max(20, rounded));
            });

            const recommendMileage = () => {
                form.weekly_mileage = idealMileage.value;
            };

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
                return result.value.sessions; // Show all sessions
            });

            const freeWeeksCount = computed(() => {
                if (!result.value) return 0;
                return result.value.weeks; // Show full program in preview
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
                
                // Format PB time
                if (form.pb_distance === 'cooper12' || form.pb_distance === 'balke15') {
                    form.pb_time = String(pb_distance_meters.value || 0);
                } else {
                    const h = String(pb_hours.value || 0).padStart(2, '0');
                    const m = String(pb_minutes.value || 0).padStart(2, '0');
                    const s = String(pb_seconds.value || 0).padStart(2, '0');
                    form.pb_time = `${h}:${m}:${s}`;
                }

                // Format Goal time
                const gh = String(goal_hours.value || 0).padStart(2, '0');
                const gm = String(goal_minutes.value || 0).padStart(2, '0');
                const gs = String(goal_seconds.value || 0).padStart(2, '0');
                form.goal_time = `${gh}:${gm}:${gs}`;

                if (form.pb_distance !== 'cooper12' && form.pb_distance !== 'balke15') {
                    if (pb_hours.value === 0 && pb_minutes.value === 0 && pb_seconds.value === 0) {
                        showNotification('Harap isi waktu parameter test/PB!', 'error');
                        return;
                    }
                } else {
                    if (!pb_distance_meters.value || pb_distance_meters.value <= 0) {
                        showNotification('Harap isi jarak hasil tes parameter!', 'error');
                        return;
                    }
                }

                if (goal_hours.value === 0 && goal_minutes.value === 0 && goal_seconds.value === 0) {
                    showNotification('Harap isi target waktu lomba!', 'error');
                    return;
                }

                if (!form.target_date) {
                    showNotification('Harap lengkapi target tanggal lomba!', 'error');
                    return;
                }

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

                    const data = await response.json();
                    
                    if (data.success) {
                        result.value = data.data;
                        step.value = 2;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        errors.value = data.errors;
                        showNotification('Gagal memproses data. Silakan cek input Anda.', 'error');
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
                    // Store state in session before showing login modal
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
                    'easy_run': 'bg-green-900/20 border-green-500/20 text-green-400',
                    'run_walk': 'bg-teal-900/20 border-teal-500/20 text-teal-400',
                    'long_run': 'bg-blue-900/20 border-blue-500/20 text-blue-400',
                    'marathon': 'bg-cyan-900/20 border-cyan-500/20 text-cyan-400',
                    'tempo': 'bg-orange-900/20 border-orange-500/20 text-orange-400',
                    'threshold': 'bg-orange-900/20 border-orange-500/20 text-orange-400',
                    'interval': 'bg-red-900/20 border-red-500/20 text-red-400',
                    'repetition': 'bg-fuchsia-900/20 border-fuchsia-500/20 text-fuchsia-400',
                    'hill': 'bg-sky-900/20 border-sky-500/20 text-sky-400',
                    'strength': 'bg-indigo-900/20 border-indigo-500/20 text-indigo-400',
                    'rest': 'bg-slate-900/40 border-slate-800 opacity-60 text-slate-400'
                };
                return classes[type] || 'bg-slate-900/40 border-slate-800';
            };

            const getSessionIcon = (type) => {
                const icons = {
                    'easy_run': '<i class="fa-solid fa-leaf"></i>',
                    'run_walk': '<i class="fa-solid fa-person-walking"></i>',
                    'rest': '<i class="fa-solid fa-bed"></i>',
                    'long_run': '<i class="fa-solid fa-battery-full"></i>',
                    'marathon': '<i class="fa-solid fa-flag-checkered"></i>',
                    'tempo': '<i class="fa-solid fa-fire"></i>',
                    'threshold': '<i class="fa-solid fa-fire"></i>',
                    'interval': '<i class="fa-solid fa-bolt"></i>',
                    'repetition': '<i class="fa-solid fa-rocket"></i>',
                    'hill': '<i class="fa-solid fa-mountain"></i>',
                    'strength': '<i class="fa-solid fa-dumbbell"></i>'
                };
                return icons[type] || '<i class="fa-solid fa-person-running"></i>';
            };



            return {
                heroSlides, activeHeroSlide, setHeroSlide,
                step, form, loading, saving, result, freePreviewSessions, freeWeeksCount, sessionsByWeek, errors, notification,
                conflictModal, confirmConflictAction,
                generateProgram, saveAndOpenCalendar,
                displayPaces, getPaceLabel, getPaceColor, formatPace, getSessionClass, getSessionIcon,
                pb_hours, pb_minutes, pb_seconds, pb_distance_meters,
                goal_hours, goal_minutes, goal_seconds,
                idealMileage, recommendMileage, realism,
                current_vdot, target_vdot, recommendedTargetDate, recommendedWeeks, applyRecommendedTargetDate, suggestGoalTime,
                bmi, bmiCategory, proteinRecommendation,
                showNotification
            };
        }
    }).mount('#generator-v2-app');
</script>
@endpush
