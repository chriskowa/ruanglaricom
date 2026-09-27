@extends('layouts.pacerhub', ['lightMode' => true])

@section('title', 'Coach Lari Indonesia - Temukan Pelatih Lari Profesional & Terverifikasi | Ruang Lari')
@section('meta_title', 'Coach Lari Indonesia - Temukan Pelatih Lari Profesional & Terverifikasi')
@section('meta_description', 'Temukan coach lari profesional di Indonesia untuk pemula hingga marathon. Cari pelatih lari di Jakarta, Surabaya, Bandung, Bali, dan sesi online terstruktur.')
@section('meta_keywords', 'coach lari, coach lari Indonesia, coach lari Jakarta, coach lari Surabaya, coach lari Bandung, coach lari online, coach marathon, pelatih lari profesional')
@section('canonical_url', route('coaches.index'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">

<script>
    (function() {
        var savedTheme = localStorage.getItem('ruanglari_theme');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    })();
</script>

<style>
    .font-heading {
        font-family: 'Inter Tight', 'Sora', -apple-system, BlinkMacSystemFont, sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .font-body {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .focus-ring:focus {
        outline: none;
        box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #0f172a;
    }
    html.dark .focus-ring:focus {
        box-shadow: 0 0 0 2px #020617, 0 0 0 4px #ea580c;
    }
    html.dark body {
        background-color: #020617 !important;
        color: #f8fafc !important;
    }
    .custom-scrollbar::-webkit-scrollbar {
        height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    html.dark .custom-scrollbar::-webkit-scrollbar-track {
        background: #0f172a;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    html.dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #334155;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'CollectionPage',
      '@id' => route('coaches.index') . '#webpage',
      'url' => route('coaches.index'),
      'name' => 'Direktori Coach Lari Profesional Indonesia - Ruang Lari',
      'description' => 'Temukan coach lari profesional di Indonesia untuk pemula hingga marathon. Cari pelatih lari di Jakarta, Surabaya, Bandung, Bali, dan sesi online terstruktur.',
      'breadcrumb' => [
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
          [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Beranda',
            'item' => route('home'),
          ],
          [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Coach Lari',
            'item' => route('coaches.index'),
          ],
        ],
      ],
    ],
    [
      '@type' => 'FAQPage',
      'mainEntity' => [
        [
          '@type' => 'Question',
          'name' => 'Apa manfaat menggunakan coach lari?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Coach lari membantu menyusun program latihan yang terukur dan dipersonalisasi sesuai target Anda, baik untuk pemula maupun persiapan race. Selain meningkatkan teknik dan efisiensi lari, pelatih memastikan progresi beban latihan aman untuk meminimalkan risiko cedera.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'Apakah coach lari hanya untuk atlet?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Tidak. Mayoritas pelari yang berlatih bersama coach adalah pelari rekreasi dan pemula yang ingin membangun kebiasaan lari yang sehat, memperbaiki form lari, atau menyelesaikan 5K dan 10K pertama mereka tanpa cedera.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'Berapa biaya coach lari?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Biaya bervariasi tergantung metode latihan, frekuensi pendampingan, dan kualifikasi pelatih. Di Ruang Lari, tersedia pilihan program mulai dari program terstruktur gratis hingga paket coaching privat offline dan online dengan biaya terjangkau per bulan.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'Apakah tersedia coach lari online?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Ya. Banyak coach di Ruang Lari menyediakan layanan online coaching yang mencakup jadwal latihan mingguan via aplikasi, pemantauan log Strava atau Garmin, serta evaluasi video teknik lari dan konsultasi berkala via chat atau video call.',
          ],
        ],
        [
          '@type' => 'Question',
          'name' => 'Bagaimana memilih coach lari yang tepat?',
          'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => 'Pilih coach berdasarkan target lari spesifik Anda, lokasi jika membutuhkan sesi offline tatap muka, gaya komunikasi yang cocok, serta rekam jejak pelari yang pernah didampingi.',
          ],
        ],
      ],
    ],
  ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<div id="coaches-page-wrapper" class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-body min-h-screen pt-0 pb-20 transition-colors duration-200">

    <!-- ================================================
         SECTION 1: HERO AREA (MAX-W-7XL)
         Editorial sports hero with authentic photography & theme toggle
         ================================================ -->
    <section class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 transition-colors duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                <!-- Text Column -->
                <div class="lg:col-span-7">
                    <h1 class="font-heading text-3xl sm:text-4xl lg:text-5xl text-slate-900 dark:text-white leading-tight">
                        Temukan Coach Lari yang Sesuai dengan Target Anda
                    </h1>
                    
                    <p class="text-base sm:text-lg text-slate-600 dark:text-slate-300 mt-4 sm:mt-5 leading-relaxed max-w-2xl">
                        Mulai latihan dengan pendamping yang tepat untuk meningkatkan teknik, endurance, pace, dan persiapan lomba.
                    </p>

                    <!-- CTAs -->
                    <div class="mt-8 flex flex-wrap items-center gap-3 sm:gap-4">
                        <a href="#search-section" 
                           class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-slate-900 hover:bg-slate-800 text-white dark:bg-orange-600 dark:hover:bg-orange-500 text-sm font-bold tracking-wide transition-colors">
                            Cari Coach Lari
                        </a>
                        <a href="{{ route('register') }}?role=coach" 
                           class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm font-semibold tracking-wide transition-colors">
                            Menjadi Coach
                        </a>
                        <button type="button" 
                                onclick="toggleMatcherWidget()"
                                class="inline-flex items-center justify-center px-4 py-3 rounded-md bg-orange-100 hover:bg-orange-200 dark:bg-orange-200 dark:hover:bg-orange-300 border border-orange-300 dark:border-orange-300 text-slate-950 dark:text-slate-950 text-xs font-bold tracking-wide transition-colors">
                            Pencocokan Cepat
                        </button>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="mt-10 pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center gap-6 sm:gap-10 text-xs text-slate-500 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                            <span>Pelatih Terverifikasi</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Sesi Offline & Online</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-600 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Program Sesuai Target</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Image Column -->
                <div class="lg:col-span-5">
                    <div class="relative rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 shadow-sm bg-slate-100 dark:bg-slate-800 aspect-[4/3] lg:aspect-[5/4]">
                        <img src="https://ruanglari.com/storage/blog/media/aab0cf89-e95d-4f42-8031-59ecc5cc6889.webp" 
                             alt="Coach lari mendampingi atlet di lintasan lari" 
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null;this.src='{{ asset('images/hero/runner-hero.jpg') }}';">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-3 left-4 right-4 text-white text-xs font-medium">
                            Pendampingan terukur untuk pelari pemula hingga maratonis
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================================================
                 INTERACTIVE COACH MATCHER WIDGET (EXPANDABLE)
                 ================================================ -->
            <div id="matcherWidget" class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800 hidden transition-all duration-300">
                <div class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-5 sm:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                                Pencocokan Cepat Coach Lari
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Klik preferensi Anda di bawah untuk menyaring pelatih yang paling relevan secara instan.
                            </p>
                        </div>
                        <button type="button" 
                                onclick="toggleMatcherWidget()" 
                                class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white underline">
                            Tutup
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                        <!-- Step 1: Target Lari -->
                        <div class="bg-white dark:bg-slate-950 p-3.5 rounded-md border border-slate-200 dark:border-slate-800">
                            <span class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px] mb-2">
                                1. Target Lari
                            </span>
                            <div class="flex flex-wrap gap-1.5" id="matcherGoalChips">
                                <button type="button" onclick="selectMatcher('goal', 'mulai_lari', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Mulai Berlari</button>
                                <button type="button" onclick="selectMatcher('goal', '5k', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">5K & 10K</button>
                                <button type="button" onclick="selectMatcher('goal', '21k', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Half Marathon</button>
                                <button type="button" onclick="selectMatcher('goal', '42k', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Marathon</button>
                                <button type="button" onclick="selectMatcher('goal', 'performance', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Tingkatkan Pace</button>
                            </div>
                        </div>

                        <!-- Step 2: Format Pendampingan -->
                        <div class="bg-white dark:bg-slate-950 p-3.5 rounded-md border border-slate-200 dark:border-slate-800">
                            <span class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px] mb-2">
                                2. Format Latihan
                            </span>
                            <div class="flex flex-wrap gap-1.5" id="matcherMethodChips">
                                <button type="button" onclick="selectMatcher('method', 'offline', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Tatap Muka (Offline)</button>
                                <button type="button" onclick="selectMatcher('method', 'online', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Online Coaching</button>
                                <button type="button" onclick="selectMatcher('method', '', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Fleksibel (Semua)</button>
                            </div>
                        </div>

                        <!-- Step 3: Kota Anda -->
                        <div class="bg-white dark:bg-slate-950 p-3.5 rounded-md border border-slate-200 dark:border-slate-800">
                            <span class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider text-[10px] mb-2">
                                3. Kota / Wilayah
                            </span>
                            <div class="flex flex-wrap gap-1.5" id="matcherCityChips">
                                <button type="button" onclick="selectMatcher('city', 'Jakarta', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Jakarta</button>
                                <button type="button" onclick="selectMatcher('city', 'Surabaya', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Surabaya</button>
                                <button type="button" onclick="selectMatcher('city', 'Bandung', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Bandung</button>
                                <button type="button" onclick="selectMatcher('city', 'Bali', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Bali</button>
                                <button type="button" onclick="selectMatcher('city', '', this)" class="matcher-btn px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Semua Kota</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
                        <span id="matcherSummaryText" class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                            Pilih kriteria untuk melihat daftar coach yang cocok
                        </span>
                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    onclick="resetMatcher()" 
                                    class="px-3 py-1.5 rounded-md border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                Reset
                            </button>
                            <button type="button" 
                                    onclick="applyMatcherAndScroll()" 
                                    class="px-4 py-1.5 rounded-md bg-slate-900 text-white dark:bg-orange-600 dark:hover:bg-orange-500 text-xs font-bold hover:bg-slate-800 transition-colors">
                                Tampilkan Coach yang Cocok
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================
         SECTION 2: SMART SEARCH AREA (MAX-W-7XL)
         Marketplace-style search component
         ================================================ -->
    <section id="search-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 sm:-mt-8 relative z-20">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-5 sm:p-6 shadow-sm transition-colors duration-200">
            <form id="filterForm" onsubmit="event.preventDefault(); applyFilters();" class="space-y-4">
                
                <!-- Quick Segmented Method Control -->
                <div class="flex items-center justify-between flex-wrap gap-3 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Metode Coaching:</span>
                    <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-950 rounded-md gap-1 text-xs" id="segmentedMethod">
                        <button type="button" 
                                onclick="setSegmentedMethod('', this)" 
                                class="seg-btn px-3 py-1 rounded-md font-semibold transition-colors bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-xs">
                            Semua
                        </button>
                        <button type="button" 
                                onclick="setSegmentedMethod('offline', this)" 
                                class="seg-btn px-3 py-1 rounded-md font-semibold transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Tatap Muka (Offline)
                        </button>
                        <button type="button" 
                                onclick="setSegmentedMethod('online', this)" 
                                class="seg-btn px-3 py-1 rounded-md font-semibold transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Online Coaching
                        </button>
                        <button type="button" 
                                onclick="setSegmentedMethod('hybrid', this)" 
                                class="seg-btn px-3 py-1 rounded-md font-semibold transition-colors text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                            Hybrid
                        </button>
                    </div>
                </div>

                <!-- Main Search Bar -->
                <div>
                    <label for="searchInput" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">
                        Anda ingin mencari coach di mana?
                    </label>
                    <div class="relative">
                        <input type="text" 
                                id="searchInput" 
                                name="search"
                                value="{{ request('search') }}" 
                                placeholder="Cari kota atau nama coach (cth. Jakarta, Surabaya, Bandung, Bali, Online...)"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-md px-4 py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:bg-white dark:focus:bg-slate-950 focus:border-slate-900 dark:focus:border-orange-500 focus:outline-none transition-colors">
                        <button type="submit" 
                                class="absolute right-2 top-2 bottom-2 px-4 rounded-md bg-slate-900 hover:bg-slate-800 text-white dark:bg-orange-600 dark:hover:bg-orange-500 text-xs font-bold transition-colors">
                            Cari
                        </button>
                    </div>
                </div>

                <!-- Quick Goal Chips (1-Click Filter) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs custom-scrollbar">
                    <span class="text-slate-400 dark:text-slate-500 shrink-0 font-medium">Target Cepat:</span>
                    <button type="button" onclick="setQuickGoal('', this)" class="quick-goal-btn px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0 font-medium active-goal">Semua</button>
                    <button type="button" onclick="setQuickGoal('mulai_lari', this)" class="quick-goal-btn px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0 font-medium">Mulai Lari</button>
                    <button type="button" onclick="setQuickGoal('5k', this)" class="quick-goal-btn px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0 font-medium">5K</button>
                    <button type="button" onclick="setQuickGoal('10k', this)" class="quick-goal-btn px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0 font-medium">10K</button>
                    <button type="button" onclick="setQuickGoal('21k', this)" class="quick-goal-btn px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0 font-medium">Half Marathon</button>
                    <button type="button" onclick="setQuickGoal('42k', this)" class="quick-goal-btn px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0 font-medium">Marathon</button>
                    <button type="button" onclick="setQuickGoal('performance', this)" class="quick-goal-btn px-2.5 py-1 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0 font-medium">Latihan Pace</button>
                </div>

                <!-- Secondary Filter Selectors -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2">
                    
                    <!-- Location -->
                    <div>
                        <label for="cityFilter" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Lokasi
                        </label>
                        <select id="cityFilter" 
                                name="city_id" 
                                onchange="applyFilters()" 
                                class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-md px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:border-slate-900 dark:focus:border-orange-500 focus:outline-none">
                            <option value="">Semua Kota</option>
                            <optgroup label="Kota Populer">
                                <option value="Jakarta" {{ request('city_id') === 'Jakarta' ? 'selected' : '' }}>Jakarta</option>
                                <option value="Surabaya" {{ request('city_id') === 'Surabaya' ? 'selected' : '' }}>Surabaya</option>
                                <option value="Bandung" {{ request('city_id') === 'Bandung' ? 'selected' : '' }}>Bandung</option>
                                <option value="Yogyakarta" {{ request('city_id') === 'Yogyakarta' ? 'selected' : '' }}>Yogyakarta</option>
                                <option value="Bali" {{ request('city_id') === 'Bali' ? 'selected' : '' }}>Bali</option>
                                <option value="Medan" {{ request('city_id') === 'Medan' ? 'selected' : '' }}>Medan</option>
                                <option value="Makassar" {{ request('city_id') === 'Makassar' ? 'selected' : '' }}>Makassar</option>
                            </optgroup>
                            <optgroup label="Daftar Lengkap Kota">
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <!-- Training Goal -->
                    <div>
                        <label for="distanceFilter" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Target Latihan
                        </label>
                        <select id="distanceFilter" 
                                name="distance" 
                                onchange="syncGoalSelect(); applyFilters();" 
                                class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-md px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:border-slate-900 dark:focus:border-orange-500 focus:outline-none">
                            <option value="">Semua Target</option>
                            <option value="mulai_lari" {{ request('distance') === 'mulai_lari' ? 'selected' : '' }}>Mulai Lari</option>
                            <option value="5k" {{ request('distance') === '5k' ? 'selected' : '' }}>5K</option>
                            <option value="10k" {{ request('distance') === '10k' ? 'selected' : '' }}>10K</option>
                            <option value="21k" {{ request('distance') === '21k' ? 'selected' : '' }}>Half Marathon</option>
                            <option value="42k" {{ request('distance') === '42k' ? 'selected' : '' }}>Marathon</option>
                            <option value="performance" {{ request('distance') === 'performance' ? 'selected' : '' }}>Performance Training</option>
                        </select>
                    </div>

                    <!-- Experience Level -->
                    <div>
                        <label for="difficultyFilter" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Tingkat Pengalaman
                        </label>
                        <select id="difficultyFilter" 
                                name="difficulty" 
                                onchange="applyFilters()" 
                                class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-md px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:border-slate-900 dark:focus:border-orange-500 focus:outline-none">
                            <option value="">Semua Level</option>
                            <option value="beginner" {{ request('difficulty') === 'beginner' ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ request('difficulty') === 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ request('difficulty') === 'advanced' ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>

                    <!-- Training Method -->
                    <div>
                        <label for="methodFilter" class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Metode Latihan
                        </label>
                        <select id="methodFilter" 
                                name="method" 
                                onchange="syncMethodSelect(); applyFilters();" 
                                class="w-full bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-md px-3 py-2 text-xs text-slate-800 dark:text-slate-200 focus:border-slate-900 dark:focus:border-orange-500 focus:outline-none">
                            <option value="">Semua Metode</option>
                            <option value="offline" {{ request('method') === 'offline' ? 'selected' : '' }}>Offline (Tatap Muka)</option>
                            <option value="online" {{ request('method') === 'online' ? 'selected' : '' }}>Online Coaching</option>
                            <option value="hybrid" {{ request('method') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                    </div>
                </div>

                <!-- Active Filter Tags & Reset -->
                <div class="pt-3 flex items-center justify-between text-xs border-t border-slate-100 dark:border-slate-800 flex-wrap gap-2">
                    <div id="activePillsContainer" class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-slate-500 dark:text-slate-400">Filter Aktif:</span>
                        <span class="text-slate-400 dark:text-slate-500 italic text-[11px]" id="noFilterText">Tidak ada filter khusus</span>
                    </div>

                    <button type="button" 
                            onclick="resetFilters()" 
                            class="text-xs font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white underline transition-colors">
                        Reset Filter
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- ================================================
         SECTION 3: COACH CARD DIRECTORY (MAX-W-7XL)
         Clean directory with saved switcher & quick theme toggle
         ================================================ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <div>
                <h2 class="font-heading text-xl sm:text-2xl text-slate-900 dark:text-white">
                    Direktori Coach Lari
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Evaluasi kredibilitas, spesialisasi, dan ketersediaan sebelum memulai sesi.
                </p>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap">
                <!-- Theme Switcher (Directory Toolbar) -->
                <button type="button" 
                        onclick="toggleTheme()" 
                        class="theme-toggle-btn inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                        title="Ganti Mode Gelap / Terang">
                    <span class="theme-icon-light hidden dark:inline">
                        <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </span>
                    <span class="theme-icon-dark inline dark:hidden">
                        <svg class="w-4 h-4 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </span>
                    <span class="theme-label-text hidden sm:inline">Tema</span>
                </button>

                <!-- Saved / All Coaches Switcher -->
                <div class="inline-flex p-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md text-xs">
                    <button type="button" 
                            id="viewAllCoachesBtn"
                            onclick="setViewMode('all')" 
                            class="px-3 py-1 rounded-md font-semibold bg-slate-900 text-white dark:bg-orange-600 dark:text-white transition-colors">
                        Semua Coach
                    </button>
                    <button type="button" 
                            id="viewSavedCoachesBtn"
                            onclick="setViewMode('saved')" 
                            class="px-3 py-1 rounded-md font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center gap-1">
                        <span>Tersimpan</span>
                        <span id="savedCountBadge" class="font-mono text-[10px] px-1.5 py-0.2 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">0</span>
                    </button>
                </div>

                <!-- Quick Sort -->
                <div class="flex items-center gap-2">
                    <label for="sortFilter" class="text-xs text-slate-500 dark:text-slate-400 hidden sm:inline">Urutkan:</label>
                    <select id="sortFilter" 
                            name="sort" 
                            onchange="applyFilters()" 
                            class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-md px-2.5 py-1.5 text-xs text-slate-700 dark:text-slate-200 focus:border-slate-900 dark:focus:border-orange-500 focus:outline-none">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Program Terbanyak</option>
                        <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Nama Coach (A-Z)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Dynamic List Container with Skeleton screen -->
        <div id="coaches-list-container" class="relative min-h-[350px]">
            @include('coaches.partials.list')
        </div>
    </section>

    <!-- ================================================
         SECTION 4: FIND COACH BY GOAL (MAX-W-7XL)
         "Tujuan Lari Anda" with Interactive Target Selection
         ================================================ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-12 border-t border-slate-200 dark:border-slate-800">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h2 class="font-heading text-2xl sm:text-3xl text-slate-900 dark:text-white">
                Tujuan Lari Anda
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                Pilih fokus latihan yang paling relevan dengan sasaran berlari Anda saat ini untuk melihat pelatih spesialis.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" id="goalSectionCards">
            <!-- 1. Mulai Berlari -->
            <button type="button" 
                    data-goal-val="mulai_lari"
                    onclick="filterByGoalCard('mulai_lari', this)"
                    class="goal-card text-left bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-700 rounded-lg p-5 transition-all group focus-ring">
                <div class="w-10 h-10 rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 mb-4 group-hover:bg-slate-900 dark:group-hover:bg-orange-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Mulai Berlari
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                    Untuk membangun kebiasaan dan teknik dasar.
                </p>
                <span class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 group-hover:underline">
                    Lihat pelatih pemula →
                </span>
            </button>

            <!-- 2. Meningkatkan Pace -->
            <button type="button" 
                    data-goal-val="performance"
                    onclick="filterByGoalCard('performance', this)"
                    class="goal-card text-left bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-700 rounded-lg p-5 transition-all group focus-ring">
                <div class="w-10 h-10 rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 mb-4 group-hover:bg-slate-900 dark:group-hover:bg-orange-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Meningkatkan Pace
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                    Program latihan untuk meningkatkan performa.
                </p>
                <span class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 group-hover:underline">
                    Lihat pelatih performa →
                </span>
            </button>

            <!-- 3. Persiapan Race -->
            <button type="button" 
                    data-goal-val="42k"
                    onclick="filterByGoalCard('42k', this)"
                    class="goal-card text-left bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-700 rounded-lg p-5 transition-all group focus-ring">
                <div class="w-10 h-10 rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 mb-4 group-hover:bg-slate-900 dark:group-hover:bg-orange-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Persiapan Race
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                    Pendampingan menuju 5K, 10K, Half Marathon, dan Marathon.
                </p>
                <span class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 group-hover:underline">
                    Lihat pelatih marathon →
                </span>
            </button>

            <!-- 4. Kembali Berlari -->
            <button type="button" 
                    data-goal-val="mulai_lari"
                    onclick="filterByGoalCard('mulai_lari', this)"
                    class="goal-card text-left bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-700 rounded-lg p-5 transition-all group focus-ring">
                <div class="w-10 h-10 rounded-md bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-300 mb-4 group-hover:bg-slate-900 dark:group-hover:bg-orange-600 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Kembali Berlari
                </h3>
                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed mb-3">
                    Program bertahap setelah cedera atau lama berhenti.
                </p>
                <span class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 group-hover:underline">
                    Lihat program bertahap →
                </span>
            </button>
        </div>
    </section>

    <!-- ================================================
         SECTION 5: CITY SEO SECTION (MAX-W-7XL)
         "Coach Lari di Berbagai Kota" with 1-click filter
         ================================================ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-12 border-t border-slate-200 dark:border-slate-800">
        <div class="mb-8">
            <h2 class="font-heading text-2xl sm:text-3xl text-slate-900 dark:text-white">
                Coach Lari di Berbagai Kota
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 max-w-2xl leading-relaxed">
                Temukan pelatih lari berlisensi di kota Anda untuk sesi tatap muka langsung maupun mentoring online yang disesuaikan dengan rute lokal.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="citySectionCards">
            @foreach($featuredCities as $fc)
                <button type="button" 
                        data-city-name="{{ $fc['name'] }}"
                        onclick="filterByCityCard('{{ $fc['name'] }}', this)"
                        class="city-card text-left bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-400 dark:hover:border-slate-700 rounded-lg p-4 transition-all group focus-ring flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                                Coach Lari {{ $fc['name'] }}
                            </h3>
                            <span class="text-slate-400 dark:text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-300 text-xs">→</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                            {{ $fc['desc'] }}
                        </p>
                    </div>
                </button>
            @endforeach
        </div>
    </section>

    <!-- ================================================
         SECTION 6: WHY CHOOSE A COACH (MAX-W-7XL)
         "Mengapa Berlatih dengan Coach Lari?"
         ================================================ -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-12 border-t border-slate-200 dark:border-slate-800">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <h2 class="font-heading text-2xl sm:text-3xl text-slate-900 dark:text-white">
                Mengapa Berlatih dengan Coach Lari?
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                Berlari bukan sekadar menambah jarak tempuh. Pendampingan profesional memberikan struktur yang aman, efisien, dan terarah.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Point 1 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-6">
                <div class="text-orange-600 dark:text-orange-400 font-mono text-sm font-bold mb-2">01</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Program Latihan Lebih Terstruktur
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Setiap sesi memiliki tujuan jelas: membangun fondasi aerobik, kecepatan interval, hingga tapering teratur sebelum hari perlombaan.
                </p>
            </div>

            <!-- Point 2 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-6">
                <div class="text-orange-600 dark:text-orange-400 font-mono text-sm font-bold mb-2">02</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Teknik Lari Lebih Efektif
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Evaluasi postur tubuh, cadence langkah, dan foot strike untuk menghemat tenaga serta meningkatkan efisiensi mekanik setiap kilometer.
                </p>
            </div>

            <!-- Point 3 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-6">
                <div class="text-orange-600 dark:text-orange-400 font-mono text-sm font-bold mb-2">03</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Evaluasi Perkembangan Berkala
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Pemantauan metrik latihan nyata seperti pace, heart rate zone, dan volume mingguan secara berkala untuk menjaga progres adaptasi tubuh.
                </p>
            </div>

            <!-- Point 4 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-6">
                <div class="text-orange-600 dark:text-orange-400 font-mono text-sm font-bold mb-2">04</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Membantu Mengurangi Risiko Cedera
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Mengontrol penambahan beban latihan secara aman serta membiasakan latihan kekuatan pelengkap untuk mencegah cedera berulang.
                </p>
            </div>

            <!-- Point 5 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-6 md:col-span-2 lg:col-span-2">
                <div class="text-orange-600 dark:text-orange-400 font-mono text-sm font-bold mb-2">05</div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">
                    Latihan Sesuai Kemampuan Individu
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                    Menu latihan disesuaikan dengan tingkat kebugaran awal, kesibukan jadwal kerja, dan target waktu pribadi Anda tanpa memaksakan porsi atlet profesional.
                </p>
            </div>
        </div>
    </section>

    <!-- ================================================
         SECTION 7: FAQ SEO (MAX-W-5XL)
         Concise & accessible accordion
         ================================================ -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-16 pt-12 border-t border-slate-200 dark:border-slate-800">
        <div class="text-center mb-10">
            <h2 class="font-heading text-2xl sm:text-3xl text-slate-900 dark:text-white">
                Pertanyaan Umum Seputar Coach Lari
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 leading-relaxed">
                Informasi seputar peran pelatih, metode pendampingan, dan cara memilih coach yang sesuai.
            </p>
        </div>

        <div class="space-y-3" id="faq-accordion">
            
            <!-- Question 1 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
                <button type="button" 
                        class="w-full text-left px-5 py-4 flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors focus-ring"
                        onclick="toggleFaq(this)">
                    <span>Apa manfaat menggunakan coach lari?</span>
                    <span class="faq-icon text-slate-400 text-xs font-mono transition-transform">▼</span>
                </button>
                <div class="faq-content px-5 pb-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800 hidden pt-3">
                    Coach lari membantu menyusun program latihan yang terukur dan dipersonalisasi sesuai target Anda, baik untuk pemula maupun persiapan race. Selain meningkatkan teknik dan efisiensi lari, pelatih memastikan progresi beban latihan aman untuk meminimalkan risiko cedera.
                </div>
            </div>

            <!-- Question 2 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
                <button type="button" 
                        class="w-full text-left px-5 py-4 flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors focus-ring"
                        onclick="toggleFaq(this)">
                    <span>Apakah coach lari hanya untuk atlet?</span>
                    <span class="faq-icon text-slate-400 text-xs font-mono transition-transform">▼</span>
                </button>
                <div class="faq-content px-5 pb-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800 hidden pt-3">
                    Tidak. Mayoritas pelari yang berlatih bersama coach adalah pelari rekreasi dan pemula yang ingin membangun kebiasaan lari yang sehat, memperbaiki form lari, atau menyelesaikan 5K dan 10K pertama mereka tanpa cedera.
                </div>
            </div>

            <!-- Question 3 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
                <button type="button" 
                        class="w-full text-left px-5 py-4 flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors focus-ring"
                        onclick="toggleFaq(this)">
                    <span>Berapa biaya coach lari?</span>
                    <span class="faq-icon text-slate-400 text-xs font-mono transition-transform">▼</span>
                </button>
                <div class="faq-content px-5 pb-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800 hidden pt-3">
                    Biaya bervariasi tergantung metode latihan, frekuensi pendampingan, dan kualifikasi pelatih. Di Ruang Lari, tersedia pilihan program mulai dari program terstruktur gratis hingga paket coaching privat offline dan online dengan biaya terjangkau per bulan.
                </div>
            </div>

            <!-- Question 4 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
                <button type="button" 
                        class="w-full text-left px-5 py-4 flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors focus-ring"
                        onclick="toggleFaq(this)">
                    <span>Apakah tersedia coach lari online?</span>
                    <span class="faq-icon text-slate-400 text-xs font-mono transition-transform">▼</span>
                </button>
                <div class="faq-content px-5 pb-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800 hidden pt-3">
                    Ya. Banyak coach di Ruang Lari menyediakan layanan online coaching yang mencakup jadwal latihan mingguan via aplikasi, pemantauan log Strava atau Garmin, serta evaluasi video teknik lari dan konsultasi berkala via chat atau video call.
                </div>
            </div>

            <!-- Question 5 -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
                <button type="button" 
                        class="w-full text-left px-5 py-4 flex items-center justify-between gap-4 font-bold text-slate-900 dark:text-white text-sm hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors focus-ring"
                        onclick="toggleFaq(this)">
                    <span>Bagaimana memilih coach lari yang tepat?</span>
                    <span class="faq-icon text-slate-400 text-xs font-mono transition-transform">▼</span>
                </button>
                <div class="faq-content px-5 pb-4 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed border-t border-slate-100 dark:border-slate-800 hidden pt-3">
                    Pilih coach berdasarkan target lari spesifik Anda (misalnya fokus pemula, perbaikan pace, atau persiapan marathon), lokasi jika membutuhkan sesi offline tatap muka, gaya komunikasi yang cocok, serta rekam jejak pelari yang pernah didampingi.
                </div>
            </div>

        </div>
    </section>

</div>

<!-- ================================================
     INTERACTIVE QUICK PREVIEW MODAL ("PRATINJAU COACH")
     Athletic modal popup in dark and light modes
     ================================================ -->
<div id="coachPreviewModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modalCoachName" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-slate-950/70 transition-opacity" onclick="closeCoachPreview()"></div>

    <div class="min-h-screen px-4 text-center flex items-center justify-center py-6">
        <div class="relative bg-white dark:bg-slate-900 rounded-lg max-w-lg w-full p-6 text-left shadow-xl border border-slate-200 dark:border-slate-800 transform transition-all my-8">
            <!-- Close Button -->
            <button type="button" 
                    onclick="closeCoachPreview()" 
                    class="absolute top-4 right-4 text-slate-400 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 p-1 rounded-md transition-colors"
                    aria-label="Tutup Pratinjau">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Modal Header -->
            <div class="flex items-start gap-4 mb-4 pr-6">
                <img id="modalCoachAvatar" 
                     src="" 
                     alt="" 
                     class="w-16 h-16 rounded-full object-cover border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 shrink-0">
                <div class="min-w-0">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <h3 id="modalCoachName" class="text-lg font-bold text-slate-900 dark:text-white" style="font-family: 'Inter Tight', 'Sora', sans-serif;"></h3>
                        <span id="modalCoachVerified" class="text-emerald-600 dark:text-emerald-400 hidden" title="Terverifikasi">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </div>
                    <p class="text-xs font-bold text-orange-600 dark:text-orange-400 uppercase tracking-wider mt-0.5">Running Coach</p>
                    <p id="modalCoachCity" class="text-xs text-slate-500 dark:text-slate-400 mt-1"></p>
                </div>
            </div>

            <!-- Telemetry Stats -->
            <div class="grid grid-cols-2 gap-2 py-3 px-4 bg-slate-50 dark:bg-slate-950 rounded-md border border-slate-100 dark:border-slate-800 text-xs mb-4">
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Pengalaman</span>
                    <span id="modalCoachExp" class="font-mono font-bold text-slate-800 dark:text-slate-200"></span>
                </div>
                <div>
                    <span class="text-slate-400 dark:text-slate-500 block text-[10px] uppercase font-bold">Ketersediaan</span>
                    <span id="modalCoachAvailability" class="font-semibold text-slate-800 dark:text-slate-200"></span>
                </div>
            </div>

            <!-- Bio -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1.5">Tentang & Filosofi Coaching</h4>
                <p id="modalCoachBio" class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed"></p>
            </div>

            <!-- Programs List -->
            <div class="mb-6">
                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-2">Program Latihan Tersedia</h4>
                <div id="modalCoachPrograms" class="space-y-1.5 max-h-44 overflow-y-auto pr-1"></div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center gap-3">
                <button type="button" 
                        onclick="closeCoachPreview()" 
                        class="flex-1 py-2.5 px-4 rounded-md border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-semibold tracking-wide transition-colors">
                    Tutup
                </button>
                <a id="modalCoachFullProfile" 
                   href="#" 
                   class="flex-1 py-2.5 px-4 rounded-md bg-slate-900 hover:bg-slate-800 text-white dark:bg-orange-600 dark:hover:bg-orange-500 text-xs font-bold text-center tracking-wide transition-colors">
                    Lihat Profil Lengkap
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ================================================
     TOAST NOTIFICATION (FEEDBACK)
     ================================================ -->
<div id="coachToast" class="fixed bottom-6 right-6 z-50 hidden transition-all duration-300 transform translate-y-2 opacity-0">
    <div class="bg-slate-900 dark:bg-slate-800 text-white text-xs font-medium py-3 px-4 rounded-md shadow-lg flex items-center gap-2 border border-slate-800 dark:border-slate-700">
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
        <span id="toastMessage">Coach tersimpan ke daftar Anda</span>
    </div>
</div>

@push('scripts')
<script>
    // State & Constants
    let searchDebounceTimer;
    const debounceInterval = 350;
    let currentViewMode = 'all'; // 'all' or 'saved'
    let matcherState = { goal: '', method: '', city: '' };

    // Dark Mode Theme Switching Logic
    function toggleTheme() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('ruanglari_theme', isDark ? 'dark' : 'light');
        updateThemeToggleUI(isDark);
        showToast(isDark ? 'Mode Gelap diaktifkan' : 'Mode Terang diaktifkan');
    }

    function updateThemeToggleUI(isDark) {
        document.querySelectorAll('.theme-toggle-btn').forEach(btn => {
            const label = btn.querySelector('.theme-label-text');
            if (label) {
                label.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
            }
        });
    }

    function initTheme() {
        const isDark = document.documentElement.classList.contains('dark');
        updateThemeToggleUI(isDark);
    }

    // Local Storage Bookmark Management
    function getSavedCoaches() {
        try {
            return JSON.parse(localStorage.getItem('ruanglari_saved_coaches') || '[]');
        } catch (e) {
            return [];
        }
    }

    function saveCoachId(id) {
        const saved = getSavedCoaches();
        if (!saved.includes(id)) {
            saved.push(id);
            localStorage.setItem('ruanglari_saved_coaches', JSON.stringify(saved));
            updateSavedUI();
            showToast('Coach tersimpan ke daftar Anda');
        }
    }

    function removeCoachId(id) {
        let saved = getSavedCoaches();
        saved = saved.filter(item => item !== id);
        localStorage.setItem('ruanglari_saved_coaches', JSON.stringify(saved));
        updateSavedUI();
        showToast('Coach dihapus dari daftar tersimpan');
        if (currentViewMode === 'saved') {
            filterSavedCards();
        }
    }

    function toggleBookmark(id, btn) {
        const saved = getSavedCoaches();
        if (saved.includes(id)) {
            removeCoachId(id);
            btn.classList.remove('text-orange-600', 'dark:text-orange-400');
            btn.classList.add('text-slate-400', 'dark:text-slate-500');
            const icon = btn.querySelector('.bookmark-icon');
            if (icon) icon.setAttribute('fill', 'none');
        } else {
            saveCoachId(id);
            btn.classList.remove('text-slate-400', 'dark:text-slate-500');
            btn.classList.add('text-orange-600', 'dark:text-orange-400');
            const icon = btn.querySelector('.bookmark-icon');
            if (icon) icon.setAttribute('fill', 'currentColor');
        }
    }

    function updateSavedUI() {
        const saved = getSavedCoaches();
        const badge = document.getElementById('savedCountBadge');
        if (badge) badge.textContent = saved.length;

        document.querySelectorAll('.bookmark-btn').forEach(btn => {
            const id = parseInt(btn.getAttribute('data-coach-id'), 10);
            const icon = btn.querySelector('.bookmark-icon');
            if (saved.includes(id)) {
                btn.classList.remove('text-slate-400', 'dark:text-slate-500');
                btn.classList.add('text-orange-600', 'dark:text-orange-400');
                if (icon) icon.setAttribute('fill', 'currentColor');
            } else {
                btn.classList.remove('text-orange-600', 'dark:text-orange-400');
                btn.classList.add('text-slate-400', 'dark:text-slate-500');
                if (icon) icon.setAttribute('fill', 'none');
            }
        });
    }

    function setViewMode(mode) {
        currentViewMode = mode;
        const allBtn = document.getElementById('viewAllCoachesBtn');
        const savedBtn = document.getElementById('viewSavedCoachesBtn');

        if (mode === 'all') {
            allBtn.classList.add('bg-slate-900', 'text-white', 'dark:bg-orange-600');
            allBtn.classList.remove('text-slate-600', 'dark:text-slate-400');
            savedBtn.classList.remove('bg-slate-900', 'text-white', 'dark:bg-orange-600');
            savedBtn.classList.add('text-slate-600', 'dark:text-slate-400');
            applyFilters();
        } else {
            savedBtn.classList.add('bg-slate-900', 'text-white', 'dark:bg-orange-600');
            savedBtn.classList.remove('text-slate-600', 'dark:text-slate-400');
            allBtn.classList.remove('bg-slate-900', 'text-white', 'dark:bg-orange-600');
            allBtn.classList.add('text-slate-600', 'dark:text-slate-400');
            filterSavedCards();
        }
    }

    function filterSavedCards() {
        const saved = getSavedCoaches();
        const container = document.getElementById('coaches-list-container');
        if (!container) return;

        const cards = container.querySelectorAll('.coach-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const id = parseInt(card.getAttribute('data-coach-id'), 10);
            if (saved.includes(id)) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const pagination = container.querySelector('.pagination-container');
        if (pagination) pagination.style.display = (currentViewMode === 'saved') ? 'none' : 'flex';

        if (visibleCount === 0 && currentViewMode === 'saved') {
            let emptyState = document.getElementById('savedEmptyState');
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.id = 'savedEmptyState';
                emptyState.className = 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-10 text-center max-w-xl mx-auto shadow-xs';
                emptyState.innerHTML = `
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2" style="font-family: 'Inter Tight', 'Sora', sans-serif;">Belum Ada Coach yang Disimpan</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mb-4 leading-relaxed">Klik ikon penanda (bookmark) pada kartu coach untuk menyimpannya ke daftar evaluasi Anda.</p>
                    <button type="button" onclick="setViewMode('all')" class="px-5 py-2.5 rounded-md bg-slate-900 dark:bg-orange-600 text-white text-xs font-bold">Jelajahi Semua Coach</button>
                `;
                container.appendChild(emptyState);
            }
            emptyState.style.display = 'block';
        } else {
            const emptyState = document.getElementById('savedEmptyState');
            if (emptyState) emptyState.style.display = 'none';
        }
    }

    // Interactive Coach Preview Modal
    function openCoachPreview(coachId) {
        const card = document.querySelector(`.coach-card[data-coach-id="${coachId}"]`);
        if (!card) return;

        const name = card.getAttribute('data-coach-name');
        const city = card.getAttribute('data-coach-city');
        const exp = card.getAttribute('data-coach-experience');
        const avail = card.getAttribute('data-coach-availability');
        const bio = card.getAttribute('data-coach-bio');
        const avatar = card.getAttribute('data-coach-avatar');
        const verified = card.getAttribute('data-coach-verified') === '1';
        const profileUrl = card.getAttribute('data-coach-profile-url');
        const programsRaw = card.getAttribute('data-coach-programs');

        document.getElementById('modalCoachName').textContent = name;
        document.getElementById('modalCoachCity').textContent = city;
        document.getElementById('modalCoachExp').textContent = exp;
        document.getElementById('modalCoachAvailability').textContent = avail;
        document.getElementById('modalCoachBio').textContent = bio;
        document.getElementById('modalCoachAvatar').src = avatar;
        document.getElementById('modalCoachAvatar').onerror = function() {
            this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=0f172a&color=ffffff&bold=true&size=256`;
        };

        const verifiedEl = document.getElementById('modalCoachVerified');
        if (verifiedEl) verifiedEl.style.display = verified ? 'inline-flex' : 'none';

        const profileBtn = document.getElementById('modalCoachFullProfile');
        if (profileBtn) profileBtn.href = profileUrl;

        // Render Programs
        const programsContainer = document.getElementById('modalCoachPrograms');
        programsContainer.innerHTML = '';
        try {
            const programs = JSON.parse(programsRaw || '[]');
            if (programs.length > 0) {
                programs.forEach(p => {
                    const row = document.createElement('a');
                    row.href = p.url;
                    row.className = 'flex items-center justify-between p-2 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded text-xs hover:border-slate-400 dark:hover:border-slate-700 hover:bg-white dark:hover:bg-slate-900 transition-colors';
                    row.innerHTML = `
                        <div class="truncate pr-2">
                            <span class="font-bold text-slate-800 dark:text-slate-200">${p.title}</span>
                            <span class="text-slate-400 dark:text-slate-500 text-[10px] ml-1.5 font-mono">${p.distance}</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 shrink-0 font-mono">${p.price}</span>
                    `;
                    programsContainer.appendChild(row);
                });
            } else {
                programsContainer.innerHTML = '<p class="text-xs text-slate-400 dark:text-slate-500 italic">Belum ada program latihan yang dipublikasikan secara publik.</p>';
            }
        } catch (e) {
            programsContainer.innerHTML = '<p class="text-xs text-slate-400 dark:text-slate-500 italic">Informasi program sedang disiapkan.</p>';
        }

        const modal = document.getElementById('coachPreviewModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }
    }

    function closeCoachPreview() {
        const modal = document.getElementById('coachPreviewModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }

    // Toggle Card Programs Accordion
    function toggleCardPrograms(coachId) {
        const panel = document.getElementById(`card-programs-${coachId}`);
        const chevron = document.getElementById(`program-chevron-${coachId}`);
        if (!panel) return;

        if (panel.classList.contains('hidden')) {
            panel.classList.remove('hidden');
            if (chevron) chevron.style.transform = 'rotate(180deg)';
        } else {
            panel.classList.add('hidden');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    }

    // Interactive Coach Matcher Widget
    function toggleMatcherWidget() {
        const widget = document.getElementById('matcherWidget');
        if (!widget) return;
        widget.classList.toggle('hidden');
        if (!widget.classList.contains('hidden')) {
            widget.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function selectMatcher(type, value, btn) {
        matcherState[type] = value;
        const parent = btn.parentElement;
        parent.querySelectorAll('.matcher-btn').forEach(b => {
            b.classList.remove('bg-slate-900', 'text-white', 'border-slate-900', 'dark:bg-orange-600', 'dark:border-orange-600');
            b.classList.add('border-slate-200', 'dark:border-slate-700', 'text-slate-700', 'dark:text-slate-300');
        });
        btn.classList.add('bg-slate-900', 'text-white', 'border-slate-900', 'dark:bg-orange-600', 'dark:border-orange-600');
        btn.classList.remove('border-slate-200', 'dark:border-slate-700', 'text-slate-700', 'dark:text-slate-300');

        updateMatcherSummary();
    }

    function updateMatcherSummary() {
        const textEl = document.getElementById('matcherSummaryText');
        if (!textEl) return;
        const goalLabel = matcherState.goal ? matcherState.goal.toUpperCase() : 'Semua Target';
        const methodLabel = matcherState.method ? matcherState.method.toUpperCase() : 'Semua Metode';
        const cityLabel = matcherState.city ? matcherState.city : 'Semua Kota';
        textEl.textContent = `Kriteria terpilih: ${goalLabel} • ${methodLabel} • ${cityLabel}`;
    }

    function resetMatcher() {
        matcherState = { goal: '', method: '', city: '' };
        document.querySelectorAll('.matcher-btn').forEach(b => {
            b.classList.remove('bg-slate-900', 'text-white', 'border-slate-900', 'dark:bg-orange-600', 'dark:border-orange-600');
            b.classList.add('border-slate-200', 'dark:border-slate-700', 'text-slate-700', 'dark:text-slate-300');
        });
        updateMatcherSummary();
        resetFilters();
    }

    function applyMatcherAndScroll() {
        if (matcherState.goal) {
            const distanceFilter = document.getElementById('distanceFilter');
            if (distanceFilter) distanceFilter.value = matcherState.goal;
        }
        if (matcherState.method) {
            const methodFilter = document.getElementById('methodFilter');
            if (methodFilter) methodFilter.value = matcherState.method;
            syncMethodSelect();
        }
        if (matcherState.city) {
            const cityFilter = document.getElementById('cityFilter');
            if (cityFilter) cityFilter.value = matcherState.city;
        }

        applyFilters();
        scrollToSection('coaches-list-container');
    }

    // Segmented Method Controls
    function setSegmentedMethod(val, btn) {
        const methodFilter = document.getElementById('methodFilter');
        if (methodFilter) methodFilter.value = val;

        const parent = document.getElementById('segmentedMethod');
        if (parent) {
            parent.querySelectorAll('.seg-btn').forEach(b => {
                b.classList.remove('bg-white', 'dark:bg-slate-800', 'text-slate-900', 'dark:text-white', 'shadow-xs');
                b.classList.add('text-slate-600', 'dark:text-slate-400');
            });
            btn.classList.add('bg-white', 'dark:bg-slate-800', 'text-slate-900', 'dark:text-white', 'shadow-xs');
            btn.classList.remove('text-slate-600', 'dark:text-slate-400');
        }
        applyFilters();
    }

    function syncMethodSelect() {
        const val = document.getElementById('methodFilter')?.value || '';
        const parent = document.getElementById('segmentedMethod');
        if (!parent) return;

        parent.querySelectorAll('.seg-btn').forEach(b => {
            b.classList.remove('bg-white', 'dark:bg-slate-800', 'text-slate-900', 'dark:text-white', 'shadow-xs');
            b.classList.add('text-slate-600', 'dark:text-slate-400');
        });

        let targetIndex = 0;
        if (val === 'offline') targetIndex = 1;
        else if (val === 'online') targetIndex = 2;
        else if (val === 'hybrid') targetIndex = 3;

        const btns = parent.querySelectorAll('.seg-btn');
        if (btns[targetIndex]) {
            btns[targetIndex].classList.add('bg-white', 'dark:bg-slate-800', 'text-slate-900', 'dark:text-white', 'shadow-xs');
            btns[targetIndex].classList.remove('text-slate-600', 'dark:text-slate-400');
        }
    }

    // Quick Goal Chips
    function setQuickGoal(val, btn) {
        const distanceFilter = document.getElementById('distanceFilter');
        if (distanceFilter) distanceFilter.value = val;

        document.querySelectorAll('.quick-goal-btn').forEach(b => {
            b.classList.remove('bg-slate-900', 'text-white', 'border-slate-900', 'dark:bg-orange-600', 'dark:border-orange-600');
            b.classList.add('bg-white', 'dark:bg-slate-950', 'text-slate-700', 'dark:text-slate-300', 'border-slate-200', 'dark:border-slate-700');
        });
        btn.classList.add('bg-slate-900', 'text-white', 'border-slate-900', 'dark:bg-orange-600', 'dark:border-orange-600');
        btn.classList.remove('bg-white', 'dark:bg-slate-950', 'text-slate-700', 'dark:text-slate-300', 'border-slate-200', 'dark:border-slate-700');

        applyFilters();
    }

    function syncGoalSelect() {
        const val = document.getElementById('distanceFilter')?.value || '';
        document.querySelectorAll('.quick-goal-btn').forEach(b => {
            b.classList.remove('bg-slate-900', 'text-white', 'border-slate-900', 'dark:bg-orange-600', 'dark:border-orange-600');
            b.classList.add('bg-white', 'dark:bg-slate-950', 'text-slate-700', 'dark:text-slate-300', 'border-slate-200', 'dark:border-slate-700');
        });
        const matched = Array.from(document.querySelectorAll('.quick-goal-btn')).find(b => {
            const onclickAttr = b.getAttribute('onclick') || '';
            return onclickAttr.includes(`'${val}'`);
        });
        if (matched) {
            matched.classList.add('bg-slate-900', 'text-white', 'border-slate-900', 'dark:bg-orange-600', 'dark:border-orange-600');
            matched.classList.remove('bg-white', 'dark:bg-slate-950', 'text-slate-700', 'dark:text-slate-300', 'border-slate-200', 'dark:border-slate-700');
        }
    }

    // Section 4: Goal Cards
    function filterByGoalCard(goal, cardEl) {
        document.querySelectorAll('#goalSectionCards .goal-card').forEach(c => {
            c.classList.remove('border-orange-500', 'ring-2', 'ring-orange-400', 'bg-orange-50/20', 'dark:bg-orange-950/20');
        });
        if (cardEl) {
            cardEl.classList.add('border-orange-500', 'ring-2', 'ring-orange-400', 'bg-orange-50/20', 'dark:bg-orange-950/20');
        }
        const distanceFilter = document.getElementById('distanceFilter');
        if (distanceFilter) {
            distanceFilter.value = goal;
            syncGoalSelect();
            applyFilters();
            scrollToSection('search-section');
        }
    }

    // Section 5: City Cards
    function filterByCityCard(cityName, cardEl) {
        document.querySelectorAll('#citySectionCards .city-card').forEach(c => {
            c.classList.remove('border-orange-500', 'ring-2', 'ring-orange-400', 'bg-orange-50/20', 'dark:bg-orange-950/20');
        });
        if (cardEl) {
            cardEl.classList.add('border-orange-500', 'ring-2', 'ring-orange-400', 'bg-orange-50/20', 'dark:bg-orange-950/20');
        }
        filterByCity(cityName);
    }

    // Search Input Debounce
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(function() {
                applyFilters();
            }, debounceInterval);
        });
    }

    // Shimmer Skeleton Generator (with Dark Theme support)
    function renderSkeletonLoader() {
        return `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-pulse">
                ${[1, 2, 3].map(() => `
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-6 space-y-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-14 h-14 rounded-full bg-slate-200 dark:bg-slate-800 shrink-0"></div>
                            <div class="space-y-2 flex-1">
                                <div class="h-4 bg-slate-200 dark:bg-slate-800 rounded w-3/4"></div>
                                <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-1/2"></div>
                            </div>
                        </div>
                        <div class="h-8 bg-slate-100 dark:bg-slate-950 rounded"></div>
                        <div class="space-y-2">
                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-full"></div>
                            <div class="h-3 bg-slate-200 dark:bg-slate-800 rounded w-4/5"></div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <div class="h-8 bg-slate-100 dark:bg-slate-800 rounded flex-1"></div>
                            <div class="h-8 bg-slate-200 dark:bg-slate-700 rounded flex-1"></div>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    // Dynamic Active Filter Pills Bar
    function updateActivePills() {
        const container = document.getElementById('activePillsContainer');
        const noFilterText = document.getElementById('noFilterText');
        if (!container) return;

        container.querySelectorAll('.active-filter-pill').forEach(p => p.remove());

        const search = document.getElementById('searchInput')?.value.trim() || '';
        const citySelect = document.getElementById('cityFilter');
        const cityName = citySelect && citySelect.value ? citySelect.options[citySelect.selectedIndex].text : '';
        const distanceSelect = document.getElementById('distanceFilter');
        const distanceName = distanceSelect && distanceSelect.value ? distanceSelect.options[distanceSelect.selectedIndex].text : '';
        const methodSelect = document.getElementById('methodFilter');
        const methodName = methodSelect && methodSelect.value ? methodSelect.options[methodSelect.selectedIndex].text : '';
        const diffSelect = document.getElementById('difficultyFilter');
        const diffName = diffSelect && diffSelect.value ? diffSelect.options[diffSelect.selectedIndex].text : '';

        const activeItems = [];
        if (search) activeItems.push({ label: `Kata Kunci: "${search}"`, field: 'search' });
        if (cityName && citySelect.value) activeItems.push({ label: `Kota: ${cityName}`, field: 'city_id' });
        if (distanceName && distanceSelect.value) activeItems.push({ label: `Target: ${distanceName}`, field: 'distance' });
        if (methodName && methodSelect.value) activeItems.push({ label: `Metode: ${methodName}`, field: 'method' });
        if (diffName && diffSelect.value) activeItems.push({ label: `Level: ${diffName}`, field: 'difficulty' });

        if (activeItems.length > 0) {
            if (noFilterText) noFilterText.style.display = 'none';
            activeItems.forEach(item => {
                const pill = document.createElement('span');
                pill.className = 'active-filter-pill inline-flex items-center gap-1 px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-[11px] font-medium';
                pill.innerHTML = `
                    <span>${item.label}</span>
                    <button type="button" onclick="removeSingleFilter('${item.field}')" class="text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold ml-0.5">×</button>
                `;
                container.appendChild(pill);
            });
        } else {
            if (noFilterText) noFilterText.style.display = 'inline';
        }
    }

    function removeSingleFilter(field) {
        if (field === 'search') document.getElementById('searchInput').value = '';
        if (field === 'city_id') document.getElementById('cityFilter').value = '';
        if (field === 'distance') {
            document.getElementById('distanceFilter').value = '';
            syncGoalSelect();
        }
        if (field === 'method') {
            document.getElementById('methodFilter').value = '';
            syncMethodSelect();
        }
        if (field === 'difficulty') document.getElementById('difficultyFilter').value = '';
        applyFilters();
    }

    // AJAX Filter Fetching
    function applyFilters(pageUrl = null) {
        const container = document.getElementById('coaches-list-container');
        if (!container) return;

        container.innerHTML = renderSkeletonLoader();

        const search = document.getElementById('searchInput')?.value.trim() || '';
        const city_id = document.getElementById('cityFilter')?.value || '';
        const distance = document.getElementById('distanceFilter')?.value || '';
        const difficulty = document.getElementById('difficultyFilter')?.value || '';
        const method = document.getElementById('methodFilter')?.value || '';
        const sort = document.getElementById('sortFilter')?.value || '';

        updateActivePills();

        let fetchUrl = pageUrl || "{{ route('coaches.index') }}";
        const params = new URLSearchParams();

        if (search) params.append('search', search);
        if (city_id) params.append('city_id', city_id);
        if (distance) params.append('distance', distance);
        if (difficulty) params.append('difficulty', difficulty);
        if (method) params.append('method', method);
        if (sort) params.append('sort', sort);

        if (!pageUrl) {
            fetchUrl = params.toString() ? `${fetchUrl}?${params.toString()}` : fetchUrl;
        }

        if (window.history && window.history.pushState) {
            window.history.pushState({ path: fetchUrl }, '', fetchUrl);
        }

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
            updateSavedUI();
            if (currentViewMode === 'saved') {
                filterSavedCards();
            }
        })
        .catch(err => {
            console.error('Filter error:', err);
            container.innerHTML = `<div class="p-8 text-center text-xs text-red-600 bg-red-50 dark:bg-red-950/40 rounded-lg border border-red-200 dark:border-red-900">Gagal memuat daftar coach. Silakan coba lagi.</div>`;
        });
    }

    function resetFilters() {
        if (document.getElementById('searchInput')) document.getElementById('searchInput').value = '';
        if (document.getElementById('cityFilter')) document.getElementById('cityFilter').value = '';
        if (document.getElementById('distanceFilter')) document.getElementById('distanceFilter').value = '';
        if (document.getElementById('difficultyFilter')) document.getElementById('difficultyFilter').value = '';
        if (document.getElementById('methodFilter')) document.getElementById('methodFilter').value = '';
        if (document.getElementById('sortFilter')) document.getElementById('sortFilter').value = 'latest';
        
        syncMethodSelect();
        syncGoalSelect();

        document.querySelectorAll('#goalSectionCards .goal-card').forEach(c => {
            c.classList.remove('border-orange-500', 'ring-2', 'ring-orange-400', 'bg-orange-50/20', 'dark:bg-orange-950/20');
        });
        document.querySelectorAll('#citySectionCards .city-card').forEach(c => {
            c.classList.remove('border-orange-500', 'ring-2', 'ring-orange-400', 'bg-orange-50/20', 'dark:bg-orange-950/20');
        });

        applyFilters();
    }

    function filterByCity(cityName) {
        const cityFilter = document.getElementById('cityFilter');
        if (cityFilter) {
            let found = false;
            for (let i = 0; i < cityFilter.options.length; i++) {
                if (cityFilter.options[i].text.toLowerCase().includes(cityName.toLowerCase())) {
                    cityFilter.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) {
                const searchInput = document.getElementById('searchInput');
                if (searchInput) searchInput.value = cityName;
            }
            applyFilters();
            scrollToSection('search-section');
        }
    }

    function scrollToSection(id) {
        const el = document.getElementById(id);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // FAQ Accordion Toggle
    function toggleFaq(button) {
        const content = button.nextElementSibling;
        const icon = button.querySelector('.faq-icon');
        const isHidden = content.classList.contains('hidden');

        document.querySelectorAll('#faq-accordion .faq-content').forEach(c => c.classList.add('hidden'));
        document.querySelectorAll('#faq-accordion .faq-icon').forEach(i => i.style.transform = 'rotate(0deg)');

        if (isHidden) {
            content.classList.remove('hidden');
            if (icon) icon.style.transform = 'rotate(180deg)';
        }
    }

    // Toast Feedback Notification
    function showToast(message) {
        const toast = document.getElementById('coachToast');
        const msgEl = document.getElementById('toastMessage');
        if (!toast || !msgEl) return;

        msgEl.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
            toast.classList.add('opacity-100', 'translate-y-0');
        }, 10);

        setTimeout(() => {
            toast.classList.remove('opacity-100', 'translate-y-0');
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 2500);
    }

    // Keyboard support: Close modal on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCoachPreview();
        }
    });

    // Handle AJAX Pagination clicks
    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-container a');
        if (link) {
            e.preventDefault();
            const url = link.getAttribute('href');
            applyFilters(url);
            const listEl = document.getElementById('coaches-list-container');
            if (listEl) {
                listEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    });

    // Initialize UI on load
    document.addEventListener('DOMContentLoaded', function() {
        initTheme();
        updateSavedUI();
        updateActivePills();
    });
</script>
@endpush
@endsection
