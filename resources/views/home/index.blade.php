@extends('layouts.pacerhub')

@section('title', 'Ruang Lari | Media Lari Indonesia, Race, Training & Running Tools')
@section('meta_title', 'Ruang Lari | Media Lari Indonesia, Race, Training & Running Tools')
@section('meta_description', 'Ruang Lari adalah media dan platform lari Indonesia. Temukan berita running, program latihan gratis, kalender race, rute lari, komunitas, coach, dan running tools.')
@section('canonical_url', url('/'))

@section('content')
<div class="rl-home">

    {{-- =========================================================
        HERO / EDITORIAL RUNNING CHASSIS
    ========================================================== --}}
    {{-- =========================================================
        HERO / FLAGSHIP RUNNING ECOSYSTEM (EDITORIAL SPORTS-TECH)
    ========================================================== --}}
    @php
        $heroSlides = (isset($homepageContent) && $homepageContent)
            ? $homepageContent->hero_slides 
            : [
                'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp',
                'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp',
                'https://ruanglari.com/storage/blog/media/21bb785d-d104-4129-99ce-d84ae98afd3a.webp',
            ];

        $athleteImages = (isset($homepageContent) && $homepageContent)
            ? $homepageContent->athlete_images 
            : [
                'https://ruanglari.com/storage/blog/media/469168c8-1ae3-4f55-ae3d-f30954235ae9.webp',
                'https://ruanglari.com/storage/blog/media/d348fe51-96c4-4df4-a4f1-50e76ad33091.webp',
                'https://ruanglari.com/storage/blog/media/93702c19-25c6-47c6-b165-ce879dc963d6.webp',
            ];

        $athletePrimaryImg = $athleteImages[0] ?? 'https://ruanglari.com/storage/blog/media/469168c8-1ae3-4f55-ae3d-f30954235ae9.webp';
    @endphp

    <header class="rl-hero-stage pt-10" id="heroStage">
        {{-- Ambient Parallax Background Slider (Dynamic via Admin Homepage Management) --}}
        <div class="rl-hero-bg-slider" id="heroBgSlider" aria-hidden="true">
            @foreach($heroSlides as $sIdx => $slideUrl)
                <div class="rl-hero-bg-slide {{ $sIdx === 0 ? 'is-active' : '' }}" data-bg-index="{{ $sIdx }}">
                    <div class="rl-hero-bg-img" style="background-image: url('{{ $slideUrl }}');"></div>
                </div>
            @endforeach
            {{-- Dark cinematic scrims to maintain pristine typography contrast --}}
            <div class="rl-hero-bg-scrim"></div>
            <div class="rl-hero-bg-gradient"></div>
        </div>

        {{-- Minimal Slide Indicators --}}
        <div class="rl-hero-bg-nav font-mono" id="heroBgNav" aria-label="Background Slider Controls">
            @foreach($heroSlides as $sIdx => $slideUrl)
                <button type="button" class="rl-bg-dot {{ $sIdx === 0 ? 'is-active' : '' }}" data-slide-to="{{ $sIdx }}" aria-label="Slide {{ $sIdx + 1 }}"></button>
            @endforeach
        </div>

        {{-- Atmospheric Kinetic Canvas & Background Depth --}}
        <div class="rl-stage-glow" aria-hidden="true"></div>
        <div class="rl-stage-grid-lines" aria-hidden="true"></div>

        <div class="rl-shell">
            <div class="rl-hero-grid">
                
                {{-- Left Column: Brand Story, Headline & Compact CTA --}}
                <div class="rl-hero-copy">
                    

                    {{-- 2. Editorial Headline --}}
                    <h1 class="rl-hero-title">
                        Media & Platform<br>
                        <span class="rl-title-accent">Lari Indonesia.</span>
                    </h1>

                    {{-- 3. Comfortable Subheadline --}}
                    <p class="rl-hero-lead">
                        Berita terbaru, event race, komunitas, program latihan, dan insight pelari Indonesia dalam satu platform.
                    </p>

                    {{-- 4. Compact Premium CTA Buttons --}}
                    <div class="rl-hero-actions">
                        <a href="{{ route('events.index') }}" class="rl-btn rl-btn-primary">
                            <span>JELAJAHI LARI</span>
                            <span class="rl-btn-arr" aria-hidden="true">&rarr;</span>
                        </a>

                        <a href="{{ route('blog.index') }}" class="rl-btn rl-btn-outline">
                            <span>BACA BERITA</span>
                        </a>
                    </div>
                </div>

                {{-- Right Column: Interactive Story-Driven Athlete Visual --}}
                <div class="rl-stage-athlete-wrap" id="heroInteractiveStage">
                    {{-- Dynamic Atmospheric Aura & Velocity Lines --}}
                    <div class="rl-athlete-stage-aura" id="athleteStageAura" aria-hidden="true"></div>
                    <div class="rl-velocity-energy-field" id="velocityEnergyField" aria-hidden="true">
                        <span class="rl-streak rl-streak-1"></span>
                        <span class="rl-streak rl-streak-2"></span>
                        <span class="rl-streak rl-streak-3"></span>
                    </div>

                    {{-- Multi-Layer Athlete Kinetic Viewport --}}
                    <div class="rl-athlete-viewport" id="heroAthletePortal" data-total-poses="{{ count($athleteImages) }}">
                        @if(count($athleteImages) > 1)
                            {{-- Multi-Pose Dynamic Athlete Layers --}}
                            @foreach($athleteImages as $aIdx => $aUrl)
                                <img
                                    src="{{ $aUrl }}"
                                    alt="Pelari RuangLari Indonesia - Pose {{ $aIdx + 1 }}"
                                    class="rl-athlete-img rl-athlete-layer {{ $aIdx === 0 ? 'is-active rl-athlete-primary' : '' }}"
                                    data-pose-index="{{ $aIdx }}"
                                    id="{{ $aIdx === 0 ? 'heroAthleteImg' : 'heroAthleteImg_' . $aIdx }}"
                                    {!! $aIdx === 0 ? 'fetchpriority="high"' : 'loading="lazy"' !!}
                                >
                            @endforeach
                        @else
                            {{-- Ghost Momentum Trail 2 (Deep Sprint) --}}
                            <img
                                src="https://ruanglari.com/storage/blog/media/93702c19-25c6-47c6-b165-ce879dc963d6.webp"
                                alt=""
                                aria-hidden="true"
                                class="rl-athlete-img rl-runner-trail rl-trail-2"
                            >
                            {{-- Ghost Momentum Trail 1 (Ready Stride) --}}
                            <img
                                src="https://ruanglari.com/storage/blog/media/d348fe51-96c4-4df4-a4f1-50e76ad33091.webp"
                                alt=""
                                aria-hidden="true"
                                class="rl-athlete-img rl-runner-trail rl-trail-1"
                            >
                            {{-- Primary Athlete Asset --}}
                            <img
                                src="{{ $athletePrimaryImg }}"
                                alt="Pelari RuangLari Indonesia"
                                fetchpriority="high"
                                class="rl-athlete-img rl-athlete-primary"
                                id="heroAthleteImg"
                            >
                        @endif

                        {{-- Athletic Ground Silhouette Shadow (Mengikuti pijakan kaki pelari) --}}
                        <div class="rl-athlete-ground-shadow" aria-hidden="true"></div>
                        <div class="rl-kinetic-flash-layer" aria-hidden="true"></div>
                    </div>
                    
                    {{-- Dynamic Movement State Telemetry Pill --}}
                    <div class="rl-athlete-accent-pill font-mono" id="athleteStatePill">                        
                        <span id="pillStateText">FAKTUAL</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Bottom of Hero: Editorial News & Event Highlight Strip (Max 5 Items) --}}
        @php
            $combinedHighlights = collect();

            // 1. Masukkan Event Unggulan (Maksimal 3 event jika tersedia)
            if (isset($featuredEvents) && $featuredEvents->isNotEmpty()) {
                foreach ($featuredEvents->take(3) as $ev) {
                    $evImg = method_exists($ev, 'getHeroImageUrl') ? $ev->getHeroImageUrl() : null;
                    if (empty($evImg) && !empty($ev->hero_image)) {
                        $evImg = asset($ev->hero_image);
                    }
                    if (empty($evImg)) {
                        $evImg = 'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp';
                    }

                    $combinedHighlights->push([
                        'type'     => 'event',
                        'tag'      => 'RACE',
                        'meta'     => $ev->city ?? 'Indonesia',
                        'title'    => $ev->name ?? $ev->title,
                        'date'     => optional($ev->start_at)->translatedFormat('d M Y') ?? 'Segera Dibuka',
                        'url'      => route('events.show', $ev->slug ?? $ev->id),
                        'cta'      => 'Detail Race',
                        'image'    => $evImg,
                        'is_event' => true,
                    ]);
                }
            } elseif (isset($featuredEvent) && $featuredEvent) {
                $evImg = method_exists($featuredEvent, 'getHeroImageUrl') ? $featuredEvent->getHeroImageUrl() : null;
                if (empty($evImg) && !empty($featuredEvent->hero_image)) {
                    $evImg = asset($featuredEvent->hero_image);
                }
                if (empty($evImg)) {
                    $evImg = 'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp';
                }

                $combinedHighlights->push([
                    'type'     => 'event',
                    'tag'      => 'RACE',
                    'meta'     => $featuredEvent->city ?? 'Indonesia',
                    'title'    => $featuredEvent->name ?? $featuredEvent->title,
                    'date'     => optional($featuredEvent->start_at)->translatedFormat('d M Y') ?? 'Segera Dibuka',
                    'url'      => route('events.show', $featuredEvent->slug ?? $featuredEvent->id),
                    'cta'      => 'Detail Race',
                    'image'    => $evImg,
                    'is_event' => true,
                ]);
            }

            // 2. Isi sisa slot dengan Berita / Journal Terkini hingga batas 5 items
            if (isset($featuredArticles) && $featuredArticles->isNotEmpty()) {
                $slotsLeft = 5 - $combinedHighlights->count();
                if ($slotsLeft > 0) {
                    foreach ($featuredArticles->filter(fn($a) => !empty($a->slug))->take($slotsLeft) as $art) {
                        $artImg = method_exists($art, 'getFeaturedImageUrl') ? $art->getFeaturedImageUrl() : null;
                        if (empty($artImg) && !empty($art->featured_image)) {
                            $artImg = asset($art->featured_image);
                        }
                        if (empty($artImg)) {
                            $artImg = 'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp';
                        }

                        $combinedHighlights->push([
                            'type'     => 'news',
                            'tag'      => strtoupper($art->category->name ?? 'WARTA'),
                            'meta'     => optional($art->published_at ?: $art->created_at)->translatedFormat('d M Y'),
                            'title'    => $art->title,
                            'date'     => optional($art->published_at ?: $art->created_at)->translatedFormat('d M Y'),
                            'url'      => route('blog.show', $art->slug),
                            'cta'      => 'Baca Ulasan',
                            'image'    => $artImg,
                            'is_event' => false,
                        ]);
                    }
                }
            }

            // 3. Fallback jika total item belum mencapai 5 (menjamin tepat 5 card gabungan event & news untuk auto-sliding)
            $defaultFallbacks = [
                [
                    'type'     => 'event',
                    'tag'      => 'KALENDER LARI',
                    'meta'     => 'Nasional',
                    'title'    => 'Kalender Event & Marathon Indonesia Terverifikasi',
                    'date'     => 'Jadwal Lengkap',
                    'url'      => route('events.index'),
                    'cta'      => 'Lihat Agenda',
                    'image'    => 'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp',
                    'is_event' => true,
                ],
                [
                    'type'     => 'news',
                    'tag'      => 'RISET & TIPS',
                    'meta'     => 'Panduan Latihan',
                    'title'    => 'Panduan Periodisasi Latihan & Riset Fisiologi Lari Terkini',
                    'date'     => 'Riset Terbaru',
                    'url'      => route('blog.index'),
                    'cta'      => 'Baca Riset',
                    'image'    => 'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp',
                    'is_event' => false,
                ],
                [
                    'type'     => 'event',
                    'tag'      => 'HALF MARATHON',
                    'meta'     => 'Jawa & Bali',
                    'title'    => 'Daftar Race 21K Terpopuler Musim Ini di Indonesia',
                    'date'     => 'Registrasi Dibuka',
                    'url'      => route('events.index'),
                    'cta'      => 'Detail Race',
                    'image'    => 'https://ruanglari.com/storage/blog/media/21bb785d-d104-4129-99ce-d84ae98afd3a.webp',
                    'is_event' => true,
                ],
                [
                    'type'     => 'news',
                    'tag'      => 'GEAR REVIEW',
                    'meta'     => 'Sepatu & Peralatan',
                    'title'    => 'Review Super Shoes & Rekomendasi Daily Trainer Terbaik',
                    'date'     => 'Ulasan Gear',
                    'url'      => route('blog.index'),
                    'cta'      => 'Ulasan Lengkap',
                    'image'    => 'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp',
                    'is_event' => false,
                ],
                [
                    'type'     => 'event',
                    'tag'      => 'TRAIL RUN',
                    'meta'     => 'Nusantara',
                    'title'    => 'Eksplorasi Kalender Trail Run & Ultra Marathon Indonesia',
                    'date'     => 'Lihat Agenda',
                    'url'      => route('events.index'),
                    'cta'      => 'Ikuti Race',
                    'image'    => 'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp',
                    'is_event' => true,
                ],
            ];
            $fbIdx = 0;
            while ($combinedHighlights->count() < 5 && $fbIdx < count($defaultFallbacks)) {
                $combinedHighlights->push($defaultFallbacks[$fbIdx]);
                $fbIdx++;
            }
        @endphp

        <div class="rl-hero-strip" id="heroFeaturedStrip">
            <div class="rl-shell">
                <div class="rl-strip-container">
                    {{-- Strip Header: Natural Editorial Title (No AI Buzzwords, Pure Clean Sans) --}}
                    <div class="rl-strip-header">
                        <div class="rl-strip-title-wrap">
                            <span class="rl-strip-pulse-indicator" aria-hidden="true"></span>
                            <h2 class="rl-strip-heading">Warta Pilihan</h2>
                            <span class="rl-strip-counter" id="stripCounterText">1 dari {{ $combinedHighlights->count() }}</span>
                        </div>

                        {{-- Navigasi Tombol Sliding --}}
                        <div class="rl-strip-nav">
                            <button type="button" id="stripPrevBtn" class="rl-strip-nav-btn" aria-label="Sorotan sebelumnya">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 19l-7-7 7-7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                            <button type="button" id="stripNextBtn" class="rl-strip-nav-btn" aria-label="Sorotan berikutnya">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 5l7 7-7 7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Track Slider Horizontal --}}
                    <div class="rl-strip-slider-wrapper" id="stripSliderWrapper">
                        <div class="rl-strip-track" id="stripTrack">
                            @foreach($combinedHighlights as $idx => $item)
                                <a href="{{ $item['url'] }}" class="rl-strip-card group" data-slide-index="{{ $idx }}">
                                    {{-- Left Thumbnail (Clean image without overlay badge) --}}
                                    <div class="rl-strip-card-thumb">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy">
                                    </div>

                                    {{-- Right Content Body (Pure Sans, Clean Contrast) --}}
                                    <div class="rl-strip-card-body">
                                        <div class="rl-strip-card-meta">
                                            <span class="rl-meta-location">{{ $item['meta'] }}</span>
                                            <span class="rl-meta-sep">&bull;</span>
                                            <span class="rl-meta-date">{{ $item['date'] }}</span>
                                        </div>
                                        <div class="rl-strip-card-tag-wrap">
                                            <span class="rl-badge-tag {{ $item['is_event'] ? 'rl-badge-event' : '' }}">
                                                {{ $item['tag'] }}
                                            </span>
                                        </div>
                                        <h3 class="rl-strip-card-title">{{ $item['title'] }}</h3>
                                        <div class="rl-strip-card-footer">
                                            <span class="rl-strip-action-text">
                                                {{ $item['cta'] }}
                                                <svg viewBox="0 0 20 20" fill="currentColor" class="rl-action-arrow" aria-hidden="true"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- =========================================================
        EDITORIAL STATS BAR (SEPARATED FROM HERO)
    ========================================================== --}}
    <section class="rl-editorial-stats-bar" aria-label="Statistik Ruang Lari">
        <div class="rl-shell">
            <div class="rl-stats-track">
                <div class="rl-stat-col">
                    <span class="rl-stat-number font-mono">1.8K+</span>
                    <span class="rl-stat-desc">Event Lari Terkurasi</span>
                </div>
                <div class="rl-stat-divider" aria-hidden="true"></div>
                <div class="rl-stat-col">
                    <span class="rl-stat-number font-mono">50+</span>
                    <span class="rl-stat-desc">Komunitas Terdaftar</span>
                </div>
                <div class="rl-stat-divider" aria-hidden="true"></div>
                <div class="rl-stat-col">
                    <span class="rl-stat-number font-mono">10K+</span>
                    <span class="rl-stat-desc">Pelari Aktif Indonesia</span>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================
        01 / TRAINING LAB — VDOT
    ========================================================== --}}
    <section id="vdot-section" class="rl-section rl-training">
        <div class="rl-shell">
            <div class="rl-section-rail">
                <span>01</span>
                <p>Training Lab</p>
            </div>

            <div class="rl-training-grid">
                <div class="rl-training-copy">
                    <span class="rl-overline">Program latihan terukur</span>

                    <h2>
                        Latihan sesuai kemampuan.
                        <em>Bukan tebakan.</em>
                    </h2>

                    <p class="rl-section-lead">
                        Gunakan hasil PB atau time trial terbaru untuk mendapatkan estimasi VDOT,
                        pace Easy, Marathon, Tempo, Interval, Repetition, serta equivalent race.
                    </p>

                    <div class="rl-feature-lines">
                        <div>
                            <b>01</b>
                            <span>
                                <strong>Target pace yang relevan</strong>
                                Pace setiap sesi mengikuti kemampuan aktual dari performa terakhirmu.
                            </span>
                        </div>
                        <div>
                            <b>02</b>
                            <span>
                                <strong>Progres yang lebih terukur</strong>
                                Susun beban latihan bertahap tanpa membuat semua sesi terasa seperti race.
                            </span>
                        </div>
                        <div>
                            <b>03</b>
                            <span>
                                <strong>Terhubung ke program latihan</strong>
                                Setelah menghitung VDOT, lanjutkan langsung ke generator program RuangLari.
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('programs.realistic') }}" class="rl-text-link rl-text-link-light">
                        Buat program lengkap
                        <span></span>
                    </a>
                </div>

                <div class="rl-lab">
                    <div class="rl-lab-head">
                        <div>
                            <span>PERFORMANCE / VDOT</span>
                            <h3>Race input</h3>
                        </div>
                        <strong>RL—01</strong>
                    </div>

                    <div class="rl-lab-form">
                        <label>
                            <span>Jarak parameter / PB terakhir</span>
                            <select id="vdot_widget_distance">
                                <option value="5K">5K (5 Kilometer)</option>
                                <option value="10K">10K (10 Kilometer)</option>
                                <option value="21K">Half Marathon (21.1K)</option>
                                <option value="42K">Full Marathon (42.2K)</option>
                                <option value="cooper12">Cooper Test (12 Menit)</option>
                                <option value="balke15">Balke Test (15 Menit)</option>
                            </select>
                        </label>

                        <div id="vdot_time_input_group">
                            <span class="rl-label">Waktu PB / catatan waktu</span>
                            <div class="rl-time-grid">
                                <label>
                                    <input type="number" id="vdot_widget_h" min="0" max="23" value="0">
                                    <small>Jam</small>
                                </label>
                                <label>
                                    <input type="number" id="vdot_widget_m" min="0" max="59" value="25">
                                    <small>Menit</small>
                                </label>
                                <label>
                                    <input type="number" id="vdot_widget_s" min="0" max="59" value="00">
                                    <small>Detik</small>
                                </label>
                            </div>
                        </div>

                        <label id="vdot_meters_input_group" class="hidden">
                            <span>Jarak tempuh (meter)</span>
                            <input type="number"
                                   id="vdot_widget_meters"
                                   min="500"
                                   max="10000"
                                   placeholder="Contoh: 2800">
                            <small>Masukkan total jarak yang dicapai selama tes.</small>
                        </label>

                        <button type="button" id="btn-vdot-calculate" class="rl-btn rl-btn-primary rl-btn-block">
                            Hitung VDOT &amp; target pace
                        </button>
                    </div>

                    <div id="vdot_widget_result"
                         class="rl-lab-result hidden opacity-0 translate-y-4">
                        <div class="rl-vdot-score">
                            <div>
                                <span>VDOT SCORE</span>
                                <strong id="vdot_score_display">42.5</strong>
                            </div>
                            <div>
                                <span>LEVEL</span>
                                <b id="vdot_fitness_level">Intermediate</b>
                            </div>
                        </div>

                        <div class="rl-tabbar">
                            <button type="button" id="vdot_tab_paces_btn" class="is-active">Pace latihan</button>
                            <button type="button" id="vdot_tab_races_btn">Target race</button>
                        </div>

                        <div id="vdot_tab_paces_content" class="rl-result-list">
                            <div><span><b class="rl-pace-badge rl-pace-e">E</b> Easy / Recovery</span><strong id="vdot_easy_pace">05:00/km</strong></div>
                            <div><span><b class="rl-pace-badge rl-pace-m">M</b> Marathon</span><strong id="vdot_marathon_pace">04:30/km</strong></div>
                            <div><span><b class="rl-pace-badge rl-pace-t">T</b> Threshold / Tempo</span><strong id="vdot_tempo_pace">04:10/km</strong></div>
                            <div>
                                <span><b class="rl-pace-badge rl-pace-i">I</b> Interval</span>
                                <strong>
                                    <u id="vdot_interval_pace">03:45/km</u>
                                    <small id="vdot_interval_400m">(90s / 400m)</small>
                                </strong>
                            </div>
                            <div>
                                <span><b class="rl-pace-badge rl-pace-r">R</b> Repetition</span>
                                <strong>
                                    <u id="vdot_repetition_pace">03:30/km</u>
                                    <small id="vdot_repetition_400m">(84s / 400m)</small>
                                </strong>
                            </div>
                        </div>

                        <div id="vdot_tab_races_content" class="rl-result-list hidden">
                            <div><span>5K</span><strong id="vdot_race_5k">18:00 (3:36/km)</strong></div>
                            <div><span>10K</span><strong id="vdot_race_10k">37:25 (3:44/km)</strong></div>
                            <div><span>Half Marathon</span><strong id="vdot_race_21k">1:22:45 (3:55/km)</strong></div>
                            <div><span>Marathon</span><strong id="vdot_race_42k">2:53:10 (4:06/km)</strong></div>
                        </div>

                        <a id="vdot_widget_action_btn"
                           href="{{ route('programs.realistic') }}"
                           class="rl-btn rl-btn-primary rl-btn-block">
                            Lanjut buat program
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================
        02 / RACE CALENDAR
    ========================================================== --}}
    <section id="events" class="rl-section">
        <div class="rl-shell">
            <div class="rl-section-rail">
                <span>02</span>
                <p>Race Calendar</p>
            </div>

            <div class="rl-section-head">
                <div>
                    <span class="rl-overline">Upcoming races / Indonesia</span>
                    <h2>Race berikutnya.</h2>
                    <p>Temukan event lari mendatang dan pilih race yang cocok dengan targetmu.</p>
                </div>

                <div class="rl-head-actions">
                    <a href="{{ route('events.index') }}?submit=1" class="rl-btn rl-btn-primary">
                        Submit event
                    </a>
                    <a href="{{ route('events.index') }}" class="rl-text-link rl-text-link-light">
                        Semua event <span></span>
                    </a>
                </div>
            </div>

            <div id="homeEvents" class="rl-event-feed">
                <div class="rl-loading">
                    <span></span>
                    Memuat race calendar...
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================
        03 / ROUTE LIBRARY
    ========================================================== --}}
    <section id="database-gpx" class="rl-section rl-section-alt">
        <div class="rl-shell">
            <div class="rl-section-rail">
                <span>03</span>
                <p>Route Library</p>
            </div>

            <div class="rl-route-grid">
                <div class="rl-route-visual">
                    <img
                        src="https://ruanglari.com/storage/blog/media/7fd6f9b8-5b5f-49d6-a0f6-f1d35b915e36.webp"
                        onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';"
                        alt="Database rute lari GPX RuangLari"
                        loading="lazy"
                    >
                    <div class="rl-route-stamp">
                        <span>GPX</span>
                        <strong>Route Library</strong>
                        <small>Indonesia</small>
                    </div>
                </div>

                <div class="rl-route-copy">
                    <span class="rl-overline">Explore before you run</span>
                    <h2>
                        Kenali rute sebelum
                        <em>start.</em>
                    </h2>

                    <p>
                        Cari referensi rute lari, pelajari jarak dan elevasi, lalu gunakan file GPX
                        untuk membantu eksplorasi rute baru.
                    </p>

                    <div class="rl-route-facts">
                        <div><b>01</b><span>Database rute dari berbagai kota.</span></div>
                        <div><b>02</b><span>Informasi jarak dan karakter rute.</span></div>
                        <div><b>03</b><span>Tools untuk membuat rute lari sendiri.</span></div>
                    </div>

                    <div class="rl-inline-actions">
                        <a href="{{ route('gpx.index') }}" class="rl-btn rl-btn-primary">Jelajahi rute</a>
                        <a href="{{ route('tools.buat-rute-lari') }}" class="rl-text-link rl-text-link-light">
                            Buat rute <span></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- =========================================================
        04 / ECOSYSTEM
    ========================================================== --}}
    <section class="rl-section">
        <div class="rl-shell">
            <div class="rl-section-rail">
                <span>04</span>
                <p>Ecosystem</p>
            </div>

            <div class="rl-section-head">
                <div>
                    <span class="rl-overline">Built around running</span>
                    <h2>Satu ekosistem.<br>Banyak kebutuhan pelari.</h2>
                </div>
            </div>

            <div class="rl-service-list">
                <article>
                    <span>01</span>
                    <div>
                        <h3>Training &amp; Performance</h3>
                        <p>Kalkulator, kalender, program latihan, pacer, dan tools untuk merencanakan progres.</p>
                    </div>
                    <div class="rl-service-links">
                        <a href="{{ route('programs.index') }}">Program</a>
                        <a href="{{ route('calculator') }}">Calculator</a>
                        <a href="{{ route('pacer.index') }}">Pacer</a>
                        <a href="{{ route('calendar.public') }}">Calendar</a>
                    </div>
                </article>

                <article>
                    <span>02</span>
                    <div>
                        <h3>Community &amp; Network</h3>
                        <p>Temukan pelari, buat sesi lari bersama, dan bangun koneksi di kota yang sama.</p>
                    </div>
                    <div class="rl-service-links">
                        <a href="{{ route('run-connect.index') }}">Run Connect</a>
                        <a href="{{ route('users.runners') }}">Cari Pelari</a>
                    </div>
                </article>

                <article>
                    <span>03</span>
                    <div>
                        <h3>Race &amp; Ticketing</h3>
                        <p>Kalender event untuk pelari dan sistem registrasi/ticketing untuk event organizer.</p>
                    </div>
                    <div class="rl-service-links">
                        <a href="{{ route('events.index') }}">Race Calendar</a>
                        <a href="{{ route('eo.landing') }}">Untuk EO</a>
                    </div>
                </article>

                <article>
                    <span>04</span>
                    <div>
                        <h3>Running Gear</h3>
                        <p>Marketplace perlengkapan lari dan pilihan gear pre-loved dalam ekosistem yang sama.</p>
                    </div>
                    <div class="rl-service-links">
                        <a href="{{ route('marketplace.index') }}">Marketplace</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- =========================================================
        05 / RUN CONNECT
    ========================================================== --}}
    <section class="rl-section rl-connect">
        <div class="rl-shell">
            <div class="rl-section-rail">
                <span>05</span>
                <p>Run Connect</p>
            </div>

            <div class="rl-connect-grid">
                <div class="rl-connect-copy">
                    <span class="rl-overline">Running is social</span>
                    <h2>
                        Tidak harus lari
                        <em>sendirian.</em>
                    </h2>

                    <p>
                        Temukan pelari di kotamu, buat jadwal lari bersama, lalu koordinasikan
                        titik kumpul dan detail sesi langsung dari Run Connect.
                    </p>

                    <div class="rl-connect-points">
                        <div><b>Nearby</b><span>Temukan pelari dan sesi di area yang relevan.</span></div>
                        <div><b>Schedule</b><span>Tentukan tanggal, jam, pace, dan titik kumpul.</span></div>
                        <div><b>Chat</b><span>Koordinasi peserta lewat obrolan grup.</span></div>
                    </div>

                    <a href="{{ route('run-connect.index') }}" class="rl-btn rl-btn-primary">
                        Cari teman lari
                    </a>
                </div>

                <figure class="rl-connect-photo">
                    <img
                        src="https://ruanglari.com/storage/blog/media/Eg6tJAZfqg7uRUqFufYDcPdFzd1uCJy1Uad4A2xg.webp"
                        alt="Cari Teman Lari RuangLari"
                        loading="lazy"
                        decoding="async"
                    >
                    <figcaption>
                        <span>RUN CONNECT / COMMUNITY</span>
                        <strong>Cari. Atur. Lari bareng.</strong>
                    </figcaption>
                </figure>
            </div>
        </div>
    </section>

    {{-- =========================================================
        06 / FOR EVENT ORGANIZER
    ========================================================== --}}
    <section class="rl-section rl-eo">
        <div class="rl-shell">
            <div class="rl-section-rail">
                <span>06</span>
                <p>For Organizers</p>
            </div>

            <div class="rl-eo-intro">
                <div>
                    <span class="rl-overline">Race operations</span>
                    <h2>Kelola event lari tanpa ribet.</h2>
                </div>
                <p>
                    Sistem registrasi untuk event lari dengan pengelolaan kuota,
                    promo, add-on, pembayaran, e-ticket, dan data peserta.
                </p>
                <div class="rl-head-actions">
                    <a href="{{ route('eo.landing') }}" class="rl-btn rl-btn-dark">Lihat solusi EO</a>
                    <a href="{{ route('events.index') }}" class="rl-text-link">Contoh event <span></span></a>
                </div>
            </div>

            <div class="rl-eo-grid">
                <article><b>01</b><h3>Quota</h3><p>Kategori dan kuota peserta yang dapat dipantau secara real-time.</p></article>
                <article><b>02</b><h3>Commerce</h3><p>Promo, kupon, jersey, merchandise, dan add-on dalam alur registrasi.</p></article>
                <article><b>03</b><h3>Payment</h3><p>Pembayaran online dan status registrasi tanpa konfirmasi manual berulang.</p></article>
                <article><b>04</b><h3>Race Day</h3><p>Data peserta siap diekspor untuk kebutuhan operasional dan check-in.</p></article>
            </div>
        </div>
    </section>

    {{-- =========================================================
        07 / JOURNAL
    ========================================================== --}}
    <section id="blog" class="rl-section">
        <div class="rl-shell">
            <div class="rl-section-rail">
                <span>07</span>
                <p>Journal</p>
            </div>

            <div class="rl-section-head">
                <div>
                    <span class="rl-overline">Stories / Training / Gear</span>
                    <h2>Baca sebelum berlari.</h2>
                    <p>Tips latihan, race preparation, gear, nutrisi, dan cerita dari kultur lari Indonesia.</p>
                </div>

                <a href="{{ url('/blog') }}" class="rl-text-link rl-text-link-light">
                    Semua artikel <span></span>
                </a>
            </div>

            <div id="blogCards" class="rl-journal-grid">
                <div class="rl-loading" style="grid-column: 1 / -1;">
                    <span></span>
                    Memuat journal...
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter+Tight:wght@600;700;800&family=Inter:wght@400;500;600;700&family=Sora:wght@700;800&display=swap');

    :root {
        --rl-bg: #050A14;
        --rl-bg-2: #0B1628;
        --rl-panel: #0D131F;
        --rl-surface: #0D131F;
        --rl-line: #1E293B;
        --rl-border-subtle: rgba(255, 255, 255, 0.08);
        --rl-text: #FFFFFF;
        --rl-muted: #94A3B8;
        --rl-lime: #C7FF00;
        --rl-lime-hover: #D4FF33;
        --rl-font: 'Inter Tight', 'Inter', system-ui, -apple-system, sans-serif;
        --rl-font-heading: 'Inter Tight', 'Sora', sans-serif;
        --rl-font-body: 'Inter', sans-serif;
        --rl-mono: 'Space Mono', SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    html { scroll-behavior: smooth; }

    .rl-home {
        background: var(--rl-bg);
        color: var(--rl-text);
        overflow-x: hidden;
        width: 100%;
        font-family: var(--rl-font-body);
        margin-top: -5rem;
    }

    .rl-shell {
        width: min(100% - 2.5rem, 80rem);
        margin-inline: auto;
        box-sizing: border-box;
    }

    /* ----------------------------------------------------
       HERO / FLAGSHIP RUNNING ECOSYSTEM (EDITORIAL SPORTS-TECH)
    ---------------------------------------------------- */
    .rl-hero-stage {
        position: relative;
        background: #05080E;
        border-bottom: 1px solid var(--rl-line);
        padding: clamp(5.5rem, 8vw, 7.5rem) 0 0;
        overflow: hidden;
        min-height: 88vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding-top:10px;
    }

    /* Ambient Parallax Background Slider */
    .rl-hero-bg-slider {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
        z-index: 0;
    }

    .rl-hero-bg-slide {
        position: absolute;
        inset: -8% -8%;
        opacity: 0;
        visibility: hidden;
        transition: opacity 1.6s cubic-bezier(0.16, 1, 0.3, 1), visibility 1.6s ease;
        will-change: transform, opacity;
    }

    .rl-hero-bg-slide.is-active {
        opacity: 1;
        visibility: visible;
        z-index: 1;
    }

    /* ============================================================
       PENGATURAN KECERAHAN / OVERLAY BACKGROUND HERO:
       - .rl-hero-bg-img: filter brightness(0.92) -> Foto cerah & hidup
       - .rl-hero-bg-scrim: Gradien horizontal (kiri lebih gelap untuk teks, kanan transparan)
       - .rl-hero-bg-gradient: Gradien vertikal (atas & bawah lembut)
       ============================================================ */
    .rl-hero-bg-img {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center 35%;
        background-repeat: no-repeat;
        /* Foto jauh lebih terang (0.92) & kontras natural */
        filter: brightness(0.75) contrast(1.05) saturate(1.05);
        transform: translate3d(0, 0, 0) scale(1.06);
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: transform;
    }

    .rl-hero-bg-slide.is-active .rl-hero-bg-img {
        animation: rl-bg-drift 14s ease-in-out infinite alternate;
    }

    @keyframes rl-bg-drift {
        0% { transform: scale(1.05) translate3d(0, 0, 0); }
        100% { transform: scale(1.1) translate3d(-15px, -8px, 0); }
    }

    @keyframes rl-runner-enter {
        0% {
            opacity: 0;
            transform: translate3d(36px, 0, 0);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes rl-hero-fadein {
        0% {
            opacity: 0;
            transform: translate3d(0, 16px, 0);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    /* Scrim Horizontal: Gelap pekat di sisi kiri untuk ketajaman teks, transparan & terang di sisi kanan (rl-athlete) */
    .rl-hero-bg-scrim {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, 
            rgba(5, 8, 14, 0.94) 0%, 
            rgba(5, 8, 14, 0.88) 32%, 
            rgba(5, 8, 14, 0.50) 56%, 
            rgba(5, 8, 14, 0.10) 78%, 
            rgba(5, 8, 14, 0.00) 100%
        );
        z-index: 2;
        pointer-events: none;
    }

    /* Gradient Vertikal: Halus di atas dan dasar agar menyatu natural dengan section bawah */
    .rl-hero-bg-gradient {
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, 
            rgba(5, 8, 14, 0.28) 0%, 
            transparent 30%, 
            rgba(5, 8, 14, 0.32) 65%, 
            #05080E 100%
        );
        z-index: 3;
        pointer-events: none;
    }

    /* Minimal Slide Indicators */
    .rl-hero-bg-nav {
        position: absolute;
        top: 2rem;
        right: clamp(1.5rem, 5vw, 4.5rem);
        display: flex;
        align-items: center;
        gap: 0.5rem;
        z-index: 10;
        pointer-events: auto;
    }

    .rl-bg-dot {
        width: 28px;
        height: 3px;
        background: rgba(255, 255, 255, 0.2);
        border: none;
        border-radius: 2px;
        padding: 0;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .rl-bg-dot:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    .rl-bg-dot.is-active {
        width: 45px;
        background: var(--rl-lime);
        box-shadow: 0 0 8px rgba(184, 255, 0, 0.6);
    }

    /* Subtle Ambient Stage Glow */
    .rl-stage-glow {
        position: absolute;
        top: 10%;
        right: 18%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(184, 255, 0, 0.07) 0%, rgba(184, 255, 0, 0) 70%);
        filter: blur(80px);
        pointer-events: none;
        z-index: 4;
    }

    .rl-stage-grid-lines {
        position: absolute;
        inset: 0;
        background-image: linear-gradient(rgba(255, 255, 255, 0.015) 1px, transparent 1px),
                          linear-gradient(90deg, rgba(255, 255, 255, 0.015) 1px, transparent 1px);
        background-size: 80px 80px;
        mask-image: radial-gradient(ellipse at 60% 30%, black 20%, transparent 75%);
        -webkit-mask-image: radial-gradient(ellipse at 60% 30%, black 20%, transparent 75%);
        pointer-events: none;
        z-index: 1;
    }

    .rl-hero-stage > .rl-shell {
        position: relative;
        z-index: 5;
    }

    .rl-hero-grid {
        position: relative;
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.85fr);
        gap: clamp(2.5rem, 5vw, 4.5rem);
        align-items: center;
        width: 100%;
        z-index: 5;
    }

    /* Kicker / Eyebrow */
    .rl-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        background: rgba(11, 22, 40, 0.65);
        border: 1px solid rgba(199, 255, 0, 0.2);
        padding: 0.35rem 0.75rem;
        border-radius: 4px;
        margin-bottom: 0.75rem;
    }

    .rl-kicker-dot {
        width: 6px;
        height: 6px;
        background: var(--rl-lime);
        border-radius: 50%;
        box-shadow: 0 0 8px var(--rl-lime);
        flex-shrink: 0;
    }

    .rl-kicker-text {
        color: #FFFFFF;
        font-family: var(--rl-font-body);
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        line-height: 1;
    }

    /* Main Headline with Editorial Font */
    .rl-hero-title {
        margin: 0.75rem 0 0;
        color: #FFFFFF;
        font-family: var(--rl-font-heading);
        font-size: clamp(2.5rem, 4.8vw, 4.2rem);
        font-weight: 800;
        line-height: 1.05;
        letter-spacing: -0.03em;
    }

    .rl-title-accent {
        color: var(--rl-lime);
    }

    /* Human & Clear Subheadline */
    .rl-hero-lead {
        margin: 1.25rem 0 0;
        color: #94A3B8;
        font-family: var(--rl-font-body);
        font-size: clamp(0.95rem, 1.2vw, 1.08rem);
        line-height: 1.7;
        max-width: 32rem;
        font-weight: 400;
    }

    /* CTA Buttons */
    .rl-hero-actions {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .rl-btn {
        min-height: 2.85rem;
        padding: 0 1.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-radius: 6px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-decoration: none;
        cursor: pointer;
        box-sizing: border-box;
    }

    .rl-btn-primary {
        background: var(--rl-lime);
        color: #050A14;
        border: 1px solid var(--rl-lime);
        box-shadow: 0 4px 20px rgba(199, 255, 0, 0.22);
    }
    .rl-btn-primary:hover {
        background: var(--rl-lime-hover);
        border-color: var(--rl-lime-hover);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(199, 255, 0, 0.32);
    }

    .rl-btn-outline {
        background: transparent;
        color: #FFFFFF;
        border: 1px solid rgba(255, 255, 255, 0.25);
    }
    .rl-btn-outline:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.45);
        transform: translateY(-2px);
    }

    .rl-btn-dark {
        background: var(--rl-bg);
        color: #fff;
    }
    .rl-btn-dark:hover {
        background: #fff;
        color: var(--rl-bg);
    }

    .rl-btn-block { width: 100%; }

    /* Editorial Stats Bar (Separated from Hero) */
    .rl-editorial-stats-bar {
        background: #0B1628;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        padding: 1.5rem 0;
        position: relative;
        z-index: 10;
    }

    .rl-stats-track {
        display: flex;
        align-items: center;
        justify-content: space-around;
        gap: 1.5rem;
    }

    .rl-stat-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 0.25rem;
    }

    .rl-stat-number {
        color: #FFFFFF;
        font-size: clamp(1.4rem, 3.5vw, 1.85rem);
        font-weight: 800;
        line-height: 1;
        letter-spacing: -0.02em;
    }

    .rl-stat-desc {
        color: #94A3B8;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .rl-stat-divider {
        width: 1px;
        height: 2.2rem;
        background: rgba(255, 255, 255, 0.1);
    }

    /* ----------------------------------------------------
       RIGHT COLUMN: INTERACTIVE STORY-DRIVEN ATHLETE VISUAL
    ---------------------------------------------------- */
    .rl-stage-athlete-wrap {
        position: relative;
        width: 100%;
        max-width: 480px;
        margin-left: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 480px;
        cursor: pointer;
    }

    /* Ambient Spotlight & Kinetic Field */
    .rl-athlete-stage-aura {
        position: absolute;
        inset: -18%;
        background: radial-gradient(circle at 60% 40%, rgba(184, 255, 0, var(--aura-opacity, 0.12)) 0%, rgba(13, 22, 38, 0) 65%);
        filter: blur(45px);
        pointer-events: none;
        z-index: 1;
        transition: background 0.4s ease, transform 0.4s ease;
        transform: scale(var(--aura-scale, 1));
    }

    /* Velocity Energy Lines */
    .rl-velocity-energy-field {
        position: absolute;
        inset: -10% -20%;
        pointer-events: none;
        z-index: 2;
        overflow: hidden;
        opacity: var(--energy-opacity, 0);
        transition: opacity 0.4s ease;
    }

    .rl-streak {
        position: absolute;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(184, 255, 0, 0.6), transparent);
        border-radius: 999px;
        transform: translateX(-100%);
    }

    .rl-streak-1 { top: 25%; width: 140px; animation: rl-streak-glide 2.4s infinite ease-in; animation-delay: 0.2s; }
    .rl-streak-2 { top: 55%; width: 220px; animation: rl-streak-glide 1.8s infinite ease-in; animation-delay: 0.8s; }
    .rl-streak-3 { top: 78%; width: 160px; animation: rl-streak-glide 2.1s infinite ease-in; animation-delay: 0.5s; }

    @keyframes rl-streak-glide {
        0% { transform: translateX(-120%) scaleX(0.4); opacity: 0; }
        40% { opacity: 0.8; }
        100% { transform: translateX(350%) scaleX(1.4); opacity: 0; }
    }

    /* Athlete Viewport & Physics Container (Transparent PNG Support - No border/shadow/box) */
    .rl-athlete-viewport {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 5;
        overflow: visible;
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        z-index: 3;
        transform-origin: center bottom;
        transition: transform 0.25s ease-out;
        will-change: transform;
        cursor: grab;
        touch-action: none;
        user-select: none;
        -webkit-user-select: none;
    }

    .rl-athlete-viewport:active,
    .rl-athlete-viewport.is-being-dragged {
        cursor: grabbing !important;
        animation: none !important;
        transition: none !important;
    }

    .rl-athlete-viewport.is-being-dragged .rl-athlete-primary,
    .rl-athlete-viewport.is-being-dragged .rl-athlete-layer.is-active {
        filter: drop-shadow(0 32px 42px rgba(0, 0, 0, 0.9)) drop-shadow(0 0 25px rgba(255, 255, 255, 0.3));
    }

    /* Idle Breathing Physics */
    .rl-athlete-viewport.is-idle-breathing {
        animation: rl-runner-breathe 4.5s ease-in-out infinite alternate;
    }

    @keyframes rl-runner-breathe {
        0% {
            transform: translate3d(0, 0, 0) scale(1);
        }
        50% {
            transform: translate3d(0, -5px, 0) scale(1.012);
        }
        100% {
            transform: translate3d(0, 0, 0) scale(1);
        }
    }

    /* Layered Runner Visuals (Transparent PNG Asset) */
    .rl-athlete-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center bottom;
        filter: contrast(1.05) brightness(1.0);
        pointer-events: none;
        transform-origin: center 70%;
        will-change: transform, opacity;
        border: none !important;
        outline: none !important;
        box-shadow: none !important;
        -webkit-user-drag: none;
    }

    .rl-athlete-primary {
        z-index: 4;
        /* Silhouette Contour Shadow: Mengikuti persis lekuk tubuh PNG tanpa bingkai kotak */
        filter: drop-shadow(0 20px 28px rgba(0, 0, 0, 0.75)) drop-shadow(0 4px 10px rgba(0, 0, 0, 0.45));
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), filter 0.3s ease;
    }

    /* Multi-Pose Dynamic Layers */
    .rl-athlete-layer {
        opacity: 0;
        z-index: 2;
        pointer-events: none;
        transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), filter 0.3s ease;
        filter: drop-shadow(0 20px 28px rgba(0, 0, 0, 0.75)) drop-shadow(0 4px 10px rgba(0, 0, 0, 0.45));
    }

    .rl-athlete-layer.is-active {
        opacity: 1;
        z-index: 4;
        pointer-events: auto;
    }

    /* Athletic Ground Silhouette Shadow (Pijakan kaki realistis di lantai) */
    .rl-athlete-ground-shadow {
        position: absolute;
        bottom: 3%;
        left: 18%;
        width: 64%;
        height: 18px;
        background: radial-gradient(ellipse at center, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.35) 45%, transparent 75%);
        border-radius: 50%;
        filter: blur(6px);
        pointer-events: none;
        z-index: 1;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    /* Motion Ghost Trails */
    .rl-runner-trail {
        z-index: 3;
        opacity: 0;
        mix-blend-mode: screen;
        pointer-events: none;
        transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        border: none !important;
        box-shadow: none !important;
    }

    .rl-trail-1 {
        filter: contrast(1.2) brightness(1.2) hue-rotate(20deg);
        transform: translate3d(calc(var(--trail-x, 0) * -12px), calc(var(--trail-y, 0) * -6px), 0) scale(0.985);
    }

    .rl-trail-2 {
        filter: contrast(1.4) brightness(1.4) hue-rotate(45deg);
        transform: translate3d(calc(var(--trail-x, 0) * -24px), calc(var(--trail-y, 0) * -12px), 0) scale(0.97);
    }

    /* Hover Energetic Stride Response - Clean PNG response without any box/frame */
    .rl-stage-athlete-wrap:hover .rl-athlete-viewport {
        border: none !important;
        box-shadow: none !important;
    }

    .rl-stage-athlete-wrap.is-hover-surge .rl-athlete-primary,
    .rl-stage-athlete-wrap.is-hover-surge .rl-athlete-layer.is-active {
        transform: scale(1.045) translateY(-6px) skewX(-2.5deg);
        filter: drop-shadow(0 28px 36px rgba(0, 0, 0, 0.85)) drop-shadow(0 0 25px rgba(255, 255, 255, 0.28));
    }

    .rl-stage-athlete-wrap.is-hover-surge .rl-athlete-ground-shadow {
        transform: scale(1.12) translateY(2px);
        opacity: 0.95;
    }

    .rl-stage-athlete-wrap.is-hover-surge .rl-trail-1 {
        opacity: 0.38;
        transform: translate3d(-18px, -4px, 0) scale(1.02);
    }

    .rl-stage-athlete-wrap.is-hover-surge .rl-trail-2 {
        opacity: 0.2;
        transform: translate3d(-34px, -8px, 0) scale(0.99);
    }

    .rl-stage-athlete-wrap.is-hover-surge .rl-velocity-energy-field {
        opacity: 1;
    }

    .rl-stage-athlete-wrap.is-hover-surge .rl-athlete-stage-aura {
        --aura-opacity: 0.22;
        --aura-scale: 1.15;
    }

    /* Matikan box blend & ground fade agar PNG pelari tidak terpotong garis hitam/kotak */
    .rl-athlete-edge-blend,
    .rl-athlete-ground-fade {
        display: none !important;
    }

    .rl-kinetic-flash-layer {
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at 50% 60%, rgba(255, 255, 255, 0.15) 0%, transparent 60%);
        opacity: 0;
        pointer-events: none;
        z-index: 7;
        transition: opacity 0.2s ease;
    }

    .rl-stage-athlete-wrap.is-hover-surge .rl-kinetic-flash-layer {
        opacity: 1;
    }

    /* Dynamic State Telemetry Badge */
    .rl-athlete-accent-pill {
        position: absolute;
        bottom: -0.75rem;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(10, 15, 24, 0.95);
        border: 1px solid rgba(184, 255, 0, 0.35);
        padding: 0.45rem 0.95rem;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.62rem;
        font-weight: 700;
        color: #E2E8F0;
        letter-spacing: 0.1em;
        white-space: nowrap;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.85);
        z-index: 10;
        backdrop-filter: blur(8px);
        transition: all 0.25s ease;
    }

    .rl-stage-athlete-wrap:hover .rl-athlete-accent-pill {
        border-color: var(--rl-lime);
        box-shadow: 0 10px 30px rgba(184, 255, 0, 0.25);
    }

    /* ----------------------------------------------------
       BOTTOM OF HERO: WARTA & AGENDA PILIHAN (EDITORIAL SLIDER)
    ---------------------------------------------------- */
    .rl-hero-strip {
        margin-top: 3.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        background: #080C14;
        padding: 1.25rem 0 1.35rem;
        position: relative;
        z-index: 5;
    }

    .rl-strip-container {
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
    }

    .rl-strip-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .rl-strip-title-wrap {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    .rl-strip-pulse-indicator {
        width: 8px;
        height: 8px;
        background-color: var(--rl-lime);
        border-radius: 50%;
        box-shadow: 0 0 0 3px rgba(184, 255, 0, 0.2);
        display: inline-block;
        flex-shrink: 0;
    }

    .rl-strip-heading {
        margin: 0;
        font-family: var(--rl-font, inherit);
        font-size: 0.85rem;
        font-weight: 700;
        color: #F8FAFC;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .rl-strip-counter {
        font-family: var(--rl-font, inherit);
        color: #94A3B8;
        font-size: 0.72rem;
        font-weight: 500;
        margin-left: 0.35rem;
    }

    .rl-strip-nav {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .rl-strip-nav-btn {
        width: 1.85rem;
        height: 1.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #0F172A;
        border: 1px solid #1E293B;
        border-radius: 4px;
        color: #CBD5E1;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .rl-strip-nav-btn:hover:not(:disabled) {
        background: #1E293B;
        border-color: var(--rl-lime);
        color: #FFFFFF;
    }

    .rl-strip-nav-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    .rl-strip-nav-btn svg {
        width: 0.9rem;
        height: 0.9rem;
    }

    /* Slider Track Wrapper */
    .rl-strip-slider-wrapper {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .rl-strip-track {
        display: flex;
        gap: 1rem;
        transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        will-change: transform;
    }

    /* Card styling: Clean horizontal layout with thumbnail on left */
    .rl-strip-card {
        flex: 0 0 calc((100% - 2rem) / 3);
        min-width: calc((100% - 2rem) / 3);
        box-sizing: border-box;
        background: #0B111D;
        border: 1px solid #1A2436;
        border-radius: 6px;
        padding: 0.75rem;
        display: flex;
        align-items: stretch;
        gap: 0.85rem;
        text-decoration: none;
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .rl-strip-card:hover {
        background: #111A2C;
        border-color: rgba(184, 255, 0, 0.45);
        transform: translateY(-2px);
    }

    /* Thumbnail box */
    .rl-strip-card-thumb {
        position: relative;
        flex: 0 0 5.25rem;
        width: 5.25rem;
        min-height: 5.25rem;
        border-radius: 4px;
        overflow: hidden;
        background: #080D17;
    }

    .rl-strip-card-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.35s ease;
    }

    .rl-strip-card:hover .rl-strip-card-thumb img {
        transform: scale(1.05);
    }

    /* Category Badge under date */
    .rl-strip-card-tag-wrap {
        margin: 0.2rem 0 0.15rem;
        display: flex;
        align-items: center;
    }

    .rl-badge-tag {
        display: inline-block;
        font-family: var(--rl-font, inherit);
        font-size: 0.58rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        background: #111A2C;
        color: #CBD5E1;
        border: 1px solid #1E293B;
        padding: 0.1rem 0.4rem;
        border-radius: 3px;
        line-height: 1.25;
    }

    .rl-badge-tag.rl-badge-event {
        color: var(--rl-lime);
        border-color: rgba(184, 255, 0, 0.35);
        background: rgba(184, 255, 0, 0.06);
    }

    /* Card Right Body */
    .rl-strip-card-body {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 0.15rem 0;
    }

    .rl-strip-card-meta {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-family: var(--rl-font, inherit);
        font-size: 0.65rem;
        color: #94A3B8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rl-meta-location {
        font-weight: 600;
        color: #CBD5E1;
    }

    .rl-meta-sep {
        color: #64748B;
        font-size: 0.6rem;
    }

    .rl-meta-date {
        color: #94A3B8;
    }

    .rl-strip-card-title {
        margin: 0.25rem 0;
        font-family: var(--rl-font, inherit);
        font-size: 0.82rem;
        font-weight: 600;
        color: #F1F5F9;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color 0.15s ease;
    }

    .rl-strip-card:hover .rl-strip-card-title {
        color: #FFFFFF;
    }

    .rl-strip-card-footer {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin-top: 0.2rem;
    }

    .rl-strip-action-text {
        font-family: var(--rl-font, inherit);
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        color: var(--rl-lime);
        font-size: 0.68rem;
        font-weight: 600;
        transition: gap 0.15s ease;
    }

    .rl-strip-card:hover .rl-strip-action-text {
        gap: 0.45rem;
    }

    .rl-action-arrow {
        width: 0.75rem;
        height: 0.75rem;
    }

    @media (max-width: 1024px) {
        .rl-strip-card {
            flex: 0 0 calc((100% - 1rem) / 2);
            min-width: calc((100% - 1rem) / 2);
        }
    }

    @media (max-width: 640px) {
        .rl-strip-card {
            flex: 0 0 calc(100% - 1.5rem);
            min-width: calc(100% - 1.5rem);
        }
    }

    /* ----------------------------------------------------
       COMMON SECTIONS & LAB
    ---------------------------------------------------- */
    .rl-overline {
        display: flex;
        align-items: center;
        gap: .8rem;
        color: var(--rl-lime);
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .19em;
        line-height: 1;
        text-transform: uppercase;
    }

    .rl-section {
        padding: clamp(4.5rem, 7vw, 7.5rem) 0;
        border-top: 1px solid var(--rl-line);
        background: var(--rl-bg);
    }

    .rl-section-alt { background: var(--rl-bg-2); }

    .rl-section-rail {
        margin-bottom: clamp(2.5rem, 4vw, 4rem);
        display: grid;
        grid-template-columns: 4rem 1fr;
        align-items: center;
        color: rgba(255,255,255,.38);
        font-size: .66rem;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .rl-section-rail span { color: var(--rl-lime); }

    .rl-section-rail p {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 0;
    }

    .rl-section-rail p::after {
        content: "";
        flex: 1;
        height: 1px;
        background: var(--rl-line);
    }

    .rl-section-head {
        margin-bottom: clamp(2.5rem, 4vw, 4rem);
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 2rem;
    }

    .rl-section-head h2,
    .rl-training-copy h2,
    .rl-route-copy h2,
    .rl-connect-copy h2,
    .rl-eo-intro h2 {
        margin: 1rem 0 0;
        color: #fff;
        font-size: clamp(2.6rem, 4.5vw, 4.8rem);
        font-weight: 950;
        letter-spacing: -.055em;
        line-height: .96;
    }

    .rl-training-copy h2 em,
    .rl-route-copy h2 em,
    .rl-connect-copy h2 em {
        color: var(--rl-lime);
        font-style: normal;
    }

    .rl-section-head p,
    .rl-section-lead,
    .rl-route-copy > p,
    .rl-connect-copy > p {
        max-width: 38rem;
        margin: 1.2rem 0 0;
        color: var(--rl-muted);
        font-size: 1rem;
        line-height: 1.8;
    }

    .rl-training { background: #08101E; }

    .rl-training-grid {
        display: grid;
        grid-template-columns: minmax(0,1.08fr) minmax(26rem,.92fr);
        gap: clamp(3rem, 6vw, 6rem);
        align-items: start;
    }

    .rl-feature-lines { margin: 2.5rem 0; }

    .rl-feature-lines > div {
        padding: 1.2rem 0;
        display: grid;
        grid-template-columns: 3rem 1fr;
        gap: 1rem;
        border-top: 1px solid var(--rl-line);
    }

    .rl-feature-lines > div:last-child { border-bottom: 1px solid var(--rl-line); }

    .rl-feature-lines b {
        color: var(--rl-lime);
        font-size: .67rem;
        letter-spacing: .12em;
    }

    .rl-feature-lines span {
        color: var(--rl-muted);
        font-size: .84rem;
        line-height: 1.6;
    }

    .rl-feature-lines strong {
        display: block;
        margin-bottom: .25rem;
        color: #fff;
        font-size: .9rem;
    }

    .rl-text-link {
        display: inline-flex;
        align-items: center;
        gap: .75rem;
        color: var(--rl-bg);
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
        text-decoration: none;
    }

    .rl-text-link-light { color: #fff; }

    .rl-text-link > span {
        display: block;
        width: 2rem;
        height: 1px;
        background: currentColor;
        transition: width .2s ease;
    }

    .rl-text-link:hover { color: var(--rl-lime); }
    .rl-text-link:hover > span { width: 3rem; }

    .rl-lab {
        border-top: 3px solid var(--rl-lime);
        background: var(--rl-panel);
        box-shadow: 0 35px 80px rgba(0,0,0,.22);
    }

    .rl-lab-head {
        padding: 1.25rem 1.4rem;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        border-bottom: 1px solid var(--rl-line);
    }

    .rl-lab-head span,
    .rl-lab-head > strong,
    .rl-label,
    .rl-lab-form label > span,
    .rl-vdot-score span {
        color: rgba(255,255,255,.48);
        font-size: .62rem;
        font-weight: 850;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .rl-lab-head h3 {
        margin: .2rem 0 0;
        color: #fff;
        font-size: 1.25rem;
        font-weight: 900;
    }

    .rl-lab-head > strong { color: var(--rl-lime); }

    .rl-lab-form { padding: 1.4rem; }

    .rl-lab-form > label,
    .rl-lab-form > div { display: block; margin-bottom: 1rem; }

    .rl-lab-form select,
    .rl-lab-form input {
        width: 100%;
        margin-top: .5rem;
        padding: .8rem .85rem;
        color: #fff;
        background: var(--rl-bg);
        border: 1px solid var(--rl-line);
        border-radius: 2px;
        font-size: .83rem;
        outline: none;
        transition: border-color .2s ease;
        box-sizing: border-box;
    }

    .rl-lab-form select:focus,
    .rl-lab-form input:focus { border-color: var(--rl-lime); }

    .rl-lab-form small {
        display: block;
        margin-top: .4rem;
        color: rgba(255,255,255,.38);
        font-size: .65rem;
    }

    .rl-time-grid {
        display: grid;
        grid-template-columns: repeat(3,1fr);
        gap: .55rem;
    }

    .rl-time-grid input {
        text-align: center;
        font-variant-numeric: tabular-nums;
    }

    .rl-time-grid small { text-align: center; }

    .rl-lab-result {
        padding: 1.4rem;
        border-top: 1px solid var(--rl-line);
        transition: opacity .3s ease, transform .3s ease;
    }

    .rl-vdot-score {
        margin-bottom: 1rem;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .rl-vdot-score > div:last-child { text-align: right; }

    .rl-vdot-score strong {
        display: block;
        color: var(--rl-lime);
        font-size: 2.25rem;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    .rl-vdot-score b {
        display: block;
        margin-top: .35rem;
        color: #fff;
        font-size: .78rem;
        text-transform: uppercase;
    }

    .rl-tabbar {
        margin: 1rem 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        border: 1px solid var(--rl-line);
    }

    .rl-tabbar button {
        padding: .7rem;
        color: rgba(255,255,255,.5);
        font-size: .7rem;
        font-weight: 800;
        text-transform: uppercase;
        background: transparent;
        border: none;
        cursor: pointer;
    }

    .rl-tabbar button.is-active {
        color: var(--rl-bg);
        background: var(--rl-lime);
    }

    .rl-result-list > div {
        padding: .75rem 0;
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        border-top: 1px solid var(--rl-line);
        color: rgba(255,255,255,.7);
        font-size: .72rem;
    }

    .rl-result-list > div:first-child { border-top: 0; }
    .rl-result-list span {
        display: inline-flex;
        align-items: center;
    }
    .rl-result-list span b.rl-pace-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.35rem;
        height: 1.35rem;
        border-radius: 2px;
        font-family: var(--rl-mono);
        font-size: .68rem;
        font-weight: 800;
        margin-right: .45rem;
        flex-shrink: 0;
    }

    .rl-result-list span b.rl-pace-e {
        color: #34d399;
        background: rgba(52, 211, 153, 0.12);
        border: 1px solid rgba(52, 211, 153, 0.35);
    }

    .rl-result-list span b.rl-pace-m {
        color: #38bdf8;
        background: rgba(56, 189, 248, 0.12);
        border: 1px solid rgba(56, 189, 248, 0.35);
    }

    .rl-result-list span b.rl-pace-t {
        color: #fbbf24;
        background: rgba(251, 191, 36, 0.12);
        border: 1px solid rgba(251, 191, 36, 0.35);
    }

    .rl-result-list span b.rl-pace-i {
        color: #fb923c;
        background: rgba(251, 146, 60, 0.12);
        border: 1px solid rgba(251, 146, 60, 0.35);
    }

    .rl-result-list span b.rl-pace-r {
        color: #f43f5e;
        background: rgba(244, 63, 94, 0.12);
        border: 1px solid rgba(244, 63, 94, 0.35);
    }

    .rl-result-list strong {
        color: #fff;
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .rl-result-list strong u,
    .rl-result-list strong small {
        display: block;
        text-decoration: none;
    }

    .rl-result-list strong small {
        margin-top: .2rem;
        color: rgba(255,255,255,.42);
        font-size: .62rem;
    }

    /* Events List */
    .rl-event-feed { border-top: 1px solid var(--rl-line); }

    .rl-event-item {
        min-height: 8.5rem;
        display: grid;
        grid-template-columns: 8rem minmax(0,1fr) minmax(12rem,.45fr) auto;
        gap: 1.5rem;
        align-items: center;
        border-bottom: 1px solid var(--rl-line);
        transition: background .2s ease;
        text-decoration: none;
    }

    .rl-event-item:hover { background: rgba(255,255,255,.018); }

    .rl-event-date {
        padding-left: .4rem;
        display: grid;
        grid-template-columns: 2.5rem 1fr;
        align-items: center;
        gap: .65rem;
    }

    .rl-event-date strong {
        color: #fff;
        font-size: 2.55rem;
        font-weight: 950;
        line-height: 1;
        letter-spacing: -.06em;
    }

    .rl-event-date span {
        color: var(--rl-lime);
        font-size: .65rem;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .rl-event-main h3 {
        margin: 0;
        color: #fff;
        font-size: clamp(1.2rem, 1.8vw, 1.65rem);
        font-weight: 900;
        letter-spacing: -.025em;
    }

    .rl-event-main p,
    .rl-event-meta {
        margin: .45rem 0 0;
        color: rgba(255,255,255,.48);
        font-size: .75rem;
        line-height: 1.5;
    }

    .rl-event-distances {
        display: flex;
        gap: .4rem;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .rl-event-distances span {
        padding: .35rem .5rem;
        color: var(--rl-lime);
        border: 1px solid var(--rl-line);
        font-size: .61rem;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .rl-event-arrow {
        width: 2.8rem;
        height: 2.8rem;
        display: grid;
        place-items: center;
        color: #fff;
        border: 1px solid var(--rl-line);
        transition: .2s ease;
    }

    .rl-event-item:hover .rl-event-arrow {
        color: var(--rl-bg);
        background: var(--rl-lime);
        border-color: var(--rl-lime);
    }

    .rl-event-arrow svg { width: 1rem; height: 1rem; }

    .rl-loading {
        padding: 4rem 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .7rem;
        color: rgba(255,255,255,.42);
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .rl-loading > span {
        width: .55rem;
        height: .55rem;
        background: var(--rl-lime);
        border-radius: 50%;
        animation: rlPulse 1s infinite alternate;
    }

    @keyframes rlPulse { to { opacity: .25; } }

    /* Routes & Connect */
    .rl-route-grid,
    .rl-connect-grid {
        display: grid;
        grid-template-columns: minmax(0,1.05fr) minmax(0,.95fr);
        gap: clamp(3rem, 6vw, 6rem);
        align-items: center;
    }

    .rl-route-visual,
    .rl-connect-photo {
        position: relative;
        min-height: clamp(24rem, 44vw, 32rem);
        overflow: hidden;
        margin: 0;
    }

    .rl-route-visual img,
    .rl-connect-photo img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rl-route-visual::after,
    .rl-connect-photo::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(8,17,31,.74), transparent 55%);
    }

    .rl-route-stamp,
    .rl-connect-photo figcaption {
        position: absolute;
        z-index: 2;
        left: 1.5rem;
        right: 1.5rem;
        bottom: 1.5rem;
    }

    .rl-route-stamp span,
    .rl-connect-photo figcaption span {
        display: block;
        color: var(--rl-lime);
        font-size: .63rem;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    .rl-route-stamp strong,
    .rl-connect-photo figcaption strong {
        display: block;
        margin-top: .35rem;
        color: #fff;
        font-size: 1.6rem;
        font-weight: 950;
    }

    .rl-route-stamp small {
        color: rgba(255,255,255,.6);
        font-size: .7rem;
        text-transform: uppercase;
    }

    .rl-route-facts { margin: 2rem 0; }

    .rl-route-facts > div {
        padding: .9rem 0;
        display: grid;
        grid-template-columns: 2.5rem 1fr;
        border-top: 1px solid var(--rl-line);
        color: var(--rl-muted);
        font-size: .8rem;
    }

    .rl-route-facts > div:last-child { border-bottom: 1px solid var(--rl-line); }
    .rl-route-facts b { color: var(--rl-lime); font-size: .65rem; }

    .rl-service-list { border-top: 1px solid var(--rl-line); }

    .rl-service-list article {
        min-height: 9.5rem;
        padding: 1.8rem 0;
        display: grid;
        grid-template-columns: 4rem minmax(0,1fr) minmax(18rem,.7fr);
        gap: 2rem;
        align-items: center;
        border-bottom: 1px solid var(--rl-line);
    }

    .rl-service-list article > span {
        color: var(--rl-lime);
        font-size: .65rem;
        font-weight: 900;
        letter-spacing: .1em;
    }

    .rl-service-list h3 {
        margin: 0 0 .5rem;
        color: #fff;
        font-size: 1.45rem;
        font-weight: 900;
        letter-spacing: -.025em;
    }

    .rl-service-list p {
        max-width: 40rem;
        margin: 0;
        color: var(--rl-muted);
        font-size: .82rem;
        line-height: 1.65;
    }

    .rl-service-links {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem 1.2rem;
        justify-content: flex-end;
    }

    .rl-service-links a {
        color: rgba(255,255,255,.58);
        font-size: .68rem;
        font-weight: 850;
        letter-spacing: .07em;
        text-transform: uppercase;
        text-decoration: none;
    }

    .rl-service-links a:hover { color: var(--rl-lime); }

    .rl-connect { background: #07101C; }

    .rl-connect-points {
        margin: 2rem 0 2.4rem;
        display: grid;
        grid-template-columns: repeat(3,1fr);
        border-top: 1px solid var(--rl-line);
        border-bottom: 1px solid var(--rl-line);
    }

    .rl-connect-points > div {
        padding: 1rem;
        border-right: 1px solid var(--rl-line);
    }

    .rl-connect-points > div:first-child { padding-left: 0; }
    .rl-connect-points > div:last-child { border-right: 0; }

    .rl-connect-points b {
        display: block;
        margin-bottom: .4rem;
        color: var(--rl-lime);
        font-size: .7rem;
        text-transform: uppercase;
    }

    .rl-connect-points span {
        color: rgba(255,255,255,.5);
        font-size: .7rem;
        line-height: 1.5;
    }

    /* EO Banner Section */
    .rl-eo {
        color: var(--rl-bg);
        background: var(--rl-lime);
        border-top-color: var(--rl-lime);
    }

    .rl-eo .rl-section-rail { color: rgba(8,17,31,.5); }
    .rl-eo .rl-section-rail span { color: var(--rl-bg); }
    .rl-eo .rl-section-rail p::after { background: rgba(8,17,31,.25); }
    .rl-eo .rl-overline { color: var(--rl-bg); }

    .rl-eo-intro {
        display: grid;
        grid-template-columns: minmax(0,1.3fr) minmax(15rem,.7fr) auto;
        gap: 2.5rem;
        align-items: end;
    }

    .rl-eo-intro h2 { color: var(--rl-bg); }

    .rl-eo-intro > p {
        margin: 0;
        color: rgba(8,17,31,.72);
        font-size: .85rem;
        line-height: 1.7;
    }

    .rl-eo-grid {
        margin-top: 4rem;
        display: grid;
        grid-template-columns: repeat(4,1fr);
        border-top: 1px solid rgba(8,17,31,.3);
        border-bottom: 1px solid rgba(8,17,31,.3);
    }

    .rl-eo-grid article {
        min-height: 14rem;
        padding: 1.4rem;
        border-right: 1px solid rgba(8,17,31,.3);
    }

    .rl-eo-grid article:first-child { padding-left: 0; }
    .rl-eo-grid article:last-child { border-right: 0; }
    .rl-eo-grid b { color: rgba(8,17,31,.48); font-size: .65rem; }

    .rl-eo-grid h3 {
        margin: 4rem 0 .6rem;
        color: var(--rl-bg);
        font-size: 1.45rem;
        font-weight: 950;
    }

    .rl-eo-grid p {
        margin: 0;
        color: rgba(8,17,31,.7);
        font-size: .75rem;
        line-height: 1.6;
    }

    /* Journal Feed */
    .rl-journal-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.5rem;
    }

    .rl-journal-card {
        background: var(--rl-panel);
        border: 1px solid var(--rl-line);
        border-radius: 4px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
        height: 100%;
    }

    .rl-journal-card:hover {
        transform: translateY(-4px);
        border-color: rgba(184, 255, 0, 0.4);
        box-shadow: 0 12px 28px -8px rgba(0, 0, 0, 0.5);
    }

    .rl-journal-thumb {
        width: 100%;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        position: relative;
        background: #060d18;
    }

    .rl-journal-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .5s ease;
    }

    .rl-journal-card:hover .rl-journal-thumb img { transform: scale(1.05); }

    .rl-journal-body {
        padding: 1.35rem 1.4rem 1.1rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .rl-journal-meta-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        margin-bottom: .75rem;
        font-size: .68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .rl-journal-date { color: var(--rl-lime); }
    .rl-journal-cat { color: var(--rl-muted); font-size: .64rem; }

    .rl-journal-title {
        margin: 0 0 1rem;
        color: #fff;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: color .2s ease;
    }

    .rl-journal-card:hover .rl-journal-title { color: var(--rl-lime); }

    .rl-journal-footer {
        padding: .85rem 1.4rem 1.1rem;
        border-top: 1px solid var(--rl-line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: var(--rl-muted);
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .rl-journal-card:hover .rl-journal-footer { color: #fff; }

    .rl-journal-arrow {
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 2px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--rl-line);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all .2s ease;
    }

    .rl-journal-arrow svg { width: .85rem; height: .85rem; }

    .rl-journal-card:hover .rl-journal-arrow {
        background: var(--rl-lime);
        border-color: var(--rl-lime);
        color: var(--rl-bg);
        transform: translateX(3px);
    }

    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    .rl-home .hidden { display: none; }
    .rl-home .opacity-0 { opacity: 0; }
    .rl-home .opacity-100 { opacity: 1; }
    .rl-home .translate-y-4 { transform: translateY(1rem); }
    .rl-home .translate-y-0 { transform: translateY(0); }

    /* ----------------------------------------------------
       RESPONSIVENESS (TABLET & MOBILE BUGPROOFING)
    ---------------------------------------------------- */
    @media (max-width: 1024px) {
        .rl-hero-grid,
        .rl-training-grid,
        .rl-route-grid,
        .rl-connect-grid {
            grid-template-columns: minmax(0, 1fr) !important;
            width: 100%;
        }

        .rl-hero-copy,
        .rl-featured {
            width: 100%;
            max-width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }

        .rl-training-grid { gap: 4rem; }
        .rl-lab { max-width: 42rem; }
        .rl-route-copy, .rl-connect-copy { max-width: 42rem; }

        .rl-event-item { grid-template-columns: 7rem minmax(0,1fr) auto; }
        .rl-event-meta { display: none; }

        .rl-service-list article { grid-template-columns: 3rem 1fr; }
        .rl-service-links {
            grid-column: 2;
            justify-content: flex-start;
        }

        .rl-eo-intro { grid-template-columns: 1fr 1fr; }
        .rl-eo-intro .rl-head-actions { grid-column: 1 / -1; }

        .rl-eo-grid { grid-template-columns: repeat(2,1fr); }
        .rl-eo-grid article:nth-child(2) { border-right: 0; }
        .rl-eo-grid article:nth-child(-n+2) { border-bottom: 1px solid rgba(8,17,31,.3); }

        .rl-journal-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem;
        }
    }

    @media (max-width: 767px) {
        .rl-shell {
            width: calc(100% - 2rem);
            max-width: 100%;
        }

        /* ----------------------------------------------------
           MOBILE HERO: CINEMATIC DARK SPORTS EDITORIAL
           Layer 1: Dark navy gradient
           Layer 2: Lower-right dynamic runner visual
        ---------------------------------------------------- */
        .rl-hero-stage {
            min-height: 86vh;
            min-height: 86dvh;
            background: radial-gradient(ellipse 95% 85% at 92% 70%, #0B1628 0%, #050A14 65%), #050A14;
            padding: 5.5rem 0 1.25rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        /* Hide full blurry background photo slider and indicators on mobile */
        .rl-hero-bg-slider,
        .rl-hero-bg-nav,
        .rl-stage-grid-lines {
            display: none !important;
        }

        /* Ambient subtle light depth on mobile */
        .rl-stage-glow {
            top: 20%;
            right: 5%;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(199, 255, 0, 0.08) 0%, rgba(11, 22, 40, 0) 70%);
            filter: blur(50px);
        }

        .rl-hero-stage > .rl-shell {
            position: relative;
            z-index: 5;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .rl-hero-grid {
            display: block !important;
            position: relative;
            width: 100% !important;
            min-height: 480px;
            padding-bottom: 1.5rem;
        }

        /* Text Area: Top-Left / Center-Left with strong hierarchy */
        .rl-hero-copy {
            position: relative;
            z-index: 6;
            max-width: 84%;
            animation: rl-hero-fadein 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .rl-kicker {
            padding: 0.3rem 0.65rem;
            margin-bottom: 0.65rem;
            background: rgba(11, 22, 40, 0.75);
            border: 1px solid rgba(199, 255, 0, 0.25);
        }

        .rl-kicker-dot {
            width: 5px;
            height: 5px;
        }

        .rl-kicker-text {
            font-size: 0.6rem;
            letter-spacing: 0.1em;
        }

        .rl-hero-title {
            font-size: clamp(2.1rem, 8.8vw, 2.75rem);
            line-height: 1.05;
            letter-spacing: -0.035em;
            word-break: break-word;
            margin: 0.5rem 0 0;
            color: #FFFFFF;
        }

        .rl-title-accent {
            color: var(--rl-lime, #C7FF00);
        }

        .rl-hero-lead {
            margin-top: 0.95rem;
            font-size: 0.88rem;
            line-height: 1.55;
            color: #94A3B8;
            max-width: 18rem;
        }

        /* Compact Horizontal CTA Buttons on Mobile */
        .rl-hero-actions {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            gap: 0.65rem !important;
            margin-top: 1.5rem !important;
            width: auto !important;
        }

        .rl-hero-actions .rl-btn {
            width: auto !important;
            min-height: 2.65rem;
            padding: 0 1.15rem;
            font-size: 0.72rem;
            border-radius: 6px;
            white-space: nowrap;
        }

        .rl-hero-actions .rl-btn-primary {
            background: var(--rl-lime, #C7FF00);
            color: #050A14;
            font-weight: 800;
            border: 1px solid var(--rl-lime, #C7FF00);
        }

        .rl-hero-actions .rl-btn-outline {
            background: transparent;
            color: #FFFFFF;
            border: 1px solid rgba(255, 255, 255, 0.25);
            font-weight: 700;
        }

        /* Runner Visual: Anchored to lower right */
        .rl-stage-athlete-wrap {
            position: absolute !important;
            right: -6% !important;
            bottom: 0 !important;
            width: 66% !important;
            max-width: 320px !important;
            height: 75% !important;
            max-height: 520px !important;
            min-height: auto !important;
            z-index: 2 !important;
            margin: 0 !important;
            pointer-events: none !important;
            display: flex !important;
            align-items: flex-end !important;
            justify-content: flex-end !important;
            animation: rl-runner-enter 0.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .rl-athlete-viewport {
            width: 100% !important;
            height: 100% !important;
            display: flex;
            align-items: flex-end;
            justify-content: flex-end;
            position: relative;
        }

        .rl-athlete-img {
            max-height: 100% !important;
            width: auto !important;
            object-fit: contain !important;
            object-position: bottom right !important;
            filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.85)) drop-shadow(0 0 25px rgba(199, 255, 0, 0.12));
        }

        .rl-athlete-stage-aura {
            right: 0 !important;
            bottom: 5% !important;
            width: 260px !important;
            height: 260px !important;
            background: radial-gradient(circle, rgba(199, 255, 0, 0.14) 0%, rgba(11, 22, 40, 0) 70%) !important;
            filter: blur(40px) !important;
        }

        .rl-athlete-ground-shadow {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 90%;
            height: 22px;
            background: radial-gradient(ellipse at 50% 50%, rgba(0, 0, 0, 0.85) 0%, transparent 75%);
            filter: blur(6px);
        }

        /* Subtle Motion Trail 1 on Mobile */
        .rl-trail-1 {
            opacity: 0.22 !important;
            filter: blur(2px) !important;
            transform: translate3d(-10px, -4px, 0) scale(0.98) !important;
        }

        /* Remove cluttering HUD / pill badges on mobile */
        #athleteStatePill,
        .rl-velocity-energy-field {
            display: none !important;
        }

        /* Editorial Stats Bar on Mobile */
        .rl-editorial-stats-bar {
            padding: 1.25rem 0;
        }

        .rl-stats-track {
            display: grid;
            grid-template-columns: 1fr auto 1fr auto 1fr;
            align-items: center;
            gap: 0.5rem;
        }

        .rl-stat-number {
            font-size: 1.25rem;
        }

        .rl-stat-desc {
            font-size: 0.62rem;
            line-height: 1.2;
        }

        .rl-stat-divider {
            height: 1.8rem;
        }

        .rl-hud-cadence {
            left: 0;
            top: 8%;
        }

        .rl-hud-vdot {
            right: 0;
            bottom: 14%;
        }

        .rl-dock-flex {
            grid-template-columns: 1fr !important;
            gap: 1.25rem;
        }

        .rl-eco-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .rl-eco-grid a:last-child {
            grid-column: span 2;
        }

        .rl-slide-viewport {
            aspect-ratio: 16 / 10;
            min-height: 210px;
            max-height: 250px;
        }

        .rl-cover-display {
            font-size: clamp(1.15rem, 5.2vw, 1.45rem);
        }

        .rl-cover-headline-wrap {
            bottom: 0.85rem;
            right: 0.85rem;
            left: 0.85rem;
        }

        .rl-slide-editorial {
            padding: 1.15rem 1.15rem 1.25rem;
        }

        .rl-editorial-headline {
            font-size: clamp(1.15rem, 4.8vw, 1.4rem);
        }

        /* Index grid swipeable on phone */
        .rl-index-grid {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding-bottom: 0.25rem;
            gap: 0.4rem;
        }

        .rl-index-item {
            flex: 0 0 160px;
            min-width: 160px;
            max-width: 160px;
            scroll-snap-align: start;
            padding: 0.55rem 0.75rem;
        }

        /* Common sections mobile */
        .rl-section { padding: 4rem 0; }
        .rl-section-rail { margin-bottom: 2.2rem; grid-template-columns: 2.6rem 1fr; }
        .rl-section-head { flex-direction: column; align-items: flex-start; }

        .rl-section-head h2,
        .rl-training-copy h2,
        .rl-route-copy h2,
        .rl-connect-copy h2,
        .rl-eo-intro h2 {
            font-size: clamp(2.4rem, 11vw, 3.4rem);
        }

        .rl-training-grid { gap: 3rem; }
        .rl-lab { margin-inline: 0; }

        .rl-event-item {
            padding: 1.2rem 0;
            grid-template-columns: 4.7rem minmax(0,1fr) auto;
            gap: .9rem;
        }

        .rl-event-date { grid-template-columns: 1fr; gap: .2rem; padding-left: 0; }
        .rl-event-date strong { font-size: 2rem; }
        .rl-event-main h3 { font-size: 1rem; }
        .rl-event-main p { font-size: .68rem; }
        .rl-event-distances { display: none; }
        .rl-event-arrow { width: 2.4rem; height: 2.4rem; }

        .rl-route-grid, .rl-connect-grid { gap: 2.6rem; }
        .rl-route-visual, .rl-connect-photo { min-height: 22rem; }

        .rl-service-list article {
            padding: 1.4rem 0;
            grid-template-columns: 2.5rem 1fr;
            gap: .7rem;
        }

        .rl-service-list h3 { font-size: 1.2rem; }
        .rl-service-links { gap: .5rem 1rem; }

        .rl-connect-points { grid-template-columns: 1fr; }
        .rl-connect-points > div {
            padding: .8rem 0;
            border-right: 0;
            border-bottom: 1px solid var(--rl-line);
        }
        .rl-connect-points > div:last-child { border-bottom: 0; }

        .rl-eo-intro { grid-template-columns: 1fr; }
        .rl-eo-intro .rl-head-actions { grid-column: auto; }
        .rl-eo-grid { grid-template-columns: 1fr; }
        .rl-eo-grid article {
            min-height: auto;
            padding: 1.2rem 0;
            border-right: 0;
            border-bottom: 1px solid rgba(8,17,31,.3);
        }
        .rl-eo-grid article:last-child { border-bottom: 0; }
        .rl-eo-grid h3 { margin: 2rem 0 .5rem; }

        .rl-journal-grid { grid-template-columns: 1fr; gap: 1rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        html { scroll-behavior: auto; }
        *, *::before, *::after {
            scroll-behavior: auto !important;
            animation-duration: .001ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: .001ms !important;
        }
    }
</style>
@endpush

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            'name' => 'Ruang Lari',
            'url' => url('/'),
            'logo' => asset('images/ruanglari_green.png'),
        ],
        [
            '@type' => 'WebSite',
            'name' => 'Ruang Lari',
            'url' => url('/'),
        ],
        [
            '@type' => 'WebPage',
            'name' => 'Ruang Lari | Kalender Event Lari, Komunitas, dan Tools Pelari Indonesia',
            'description' => 'Temukan event lari terbaru, kalender race, komunitas pelari, leaderboard, dan tools latihan dalam satu platform RuangLari.',
            'url' => url('/'),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initHeroBgSlider();
    initHeroParallax();
    initFeaturedStripSlider();
    initEditorialDispatch();
    initVdotWidget();
    loadUpcomingEvents();
    loadLatestBlogs();
});

function initHeroParallax() {
    const stage = document.getElementById('heroStage');
    const athleteWrap = document.getElementById('heroInteractiveStage');
    const athletePortal = document.getElementById('heroAthletePortal');
    const athletePrimary = document.getElementById('heroAthleteImg');
    const trail1 = athletePortal?.querySelector('.rl-trail-1');
    const trail2 = athletePortal?.querySelector('.rl-trail-2');
    const stageAura = document.getElementById('athleteStageAura');
    const energyField = document.getElementById('velocityEnergyField');
    const pillStateText = document.getElementById('pillStateText');    

    if (!stage || !athletePortal) return;

    const reducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducedMotion) return;

    // Physics & state variables
    let targetMouseX = 0;
    let targetMouseY = 0;
    let currentMouseX = 0;
    let currentMouseY = 0;
    let isTicking = false;
    let isHovered = false;
    let hoverBurstTimeout = null;
    let currentPhase = 1;

    // Initial load: activate breathing state
    athletePortal.classList.add('is-idle-breathing');

    // Mouse coordinates tracking
    stage.addEventListener('mousemove', (e) => {
        const rect = stage.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width;
        const y = (e.clientY - rect.top) / rect.height;
        targetMouseX = (x - 0.5) * 2;
        targetMouseY = (y - 0.5) * 2;

        if (!isTicking) {
            isTicking = true;
            requestAnimationFrame(updateKineticStage);
        }
    }, { passive: true });

    stage.addEventListener('mouseleave', () => {
        targetMouseX = 0;
        targetMouseY = 0;
        if (!isTicking) {
            isTicking = true;
            requestAnimationFrame(updateKineticStage);
        }
    });

    const athleteLayers = athletePortal?.querySelectorAll('.rl-athlete-layer');
    function setAthleteActivePose(index) {
        if (!athleteLayers || !athleteLayers.length) return;
        const target = Math.min(Math.max(index, 0), athleteLayers.length - 1);
        athleteLayers.forEach((layer, idx) => {
            if (idx === target) {
                layer.classList.add('is-active');
            } else {
                layer.classList.remove('is-active');
            }
        });
    }

    // Hover interaction: refined athletic running surge / stride burst
    athleteWrap?.addEventListener('mouseenter', () => {
        isHovered = true;
        athleteWrap.classList.add('is-hover-surge');
        athletePortal.classList.remove('is-idle-breathing');

        // Multi-pose transition: switch to Pose 2 (Hover/Ready state) if available
        if (athleteLayers && athleteLayers.length > 1) {
            setAthleteActivePose(1);
        }

        if (pillStateText) {
            pillStateText.innerHTML = 'CEPAT';
        }
        

        if (!isTicking) {
            isTicking = true;
            requestAnimationFrame(updateKineticStage);
        }
    });

    athleteWrap?.addEventListener('mouseleave', () => {
        isHovered = false;
        athleteWrap.classList.remove('is-hover-surge');
        
        // Restore current scroll phase text & athlete pose
        updatePhaseIndicator(window.scrollY);

        if (window.scrollY < 30) {
            athletePortal.classList.add('is-idle-breathing');
        }

        if (!isTicking) {
            isTicking = true;
            requestAnimationFrame(updateKineticStage);
        }
    });

    // ==========================================
    // Interactive Kinetic Drag & Snap-Back Physics
    // ==========================================
    let isDragging = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let dragTargetX = 0;
    let dragTargetY = 0;
    let dragCurrentX = 0;
    let dragCurrentY = 0;
    let isSpringing = false;
    const groundShadow = athletePortal.querySelector('.rl-athlete-ground-shadow');

    // Prevent default browser ghost image dragging
    athletePortal.addEventListener('dragstart', (e) => e.preventDefault());

    athletePortal.addEventListener('pointerdown', (e) => {
        if (e.button !== undefined && e.button !== 0) return;

        isDragging = true;
        isSpringing = false;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        dragTargetX = dragCurrentX;
        dragTargetY = dragCurrentY;

        athletePortal.classList.add('is-being-dragged');
        athletePortal.classList.remove('is-idle-breathing');

        try {
            athletePortal.setPointerCapture(e.pointerId);
        } catch (err) {}

        if (pillStateText) {
            pillStateText.innerHTML = 'PERGERAKAN CEPAT';
        }

        if (!isTicking) {
            isTicking = true;
            requestAnimationFrame(updateKineticStage);
        }
    });

    athletePortal.addEventListener('pointermove', (e) => {
        if (!isDragging) return;

        const rawDx = e.clientX - dragStartX;
        const rawDy = e.clientY - dragStartY;

        // Natural non-linear elastic damping resistance
        const dist = Math.hypot(rawDx, rawDy);
        const maxDist = 200;
        const dampedDist = maxDist * Math.tanh(dist / maxDist);
        const ratio = dist > 0 ? (dampedDist / dist) : 0;

        dragTargetX = rawDx * ratio;
        dragTargetY = rawDy * ratio;

        if (!isTicking) {
            isTicking = true;
            requestAnimationFrame(updateKineticStage);
        }
    });

    function finishDrag(e) {
        if (!isDragging) return;
        isDragging = false;
        isSpringing = true;
        dragTargetX = 0;
        dragTargetY = 0;

        athletePortal.classList.remove('is-being-dragged');

        if (e && e.pointerId) {
            try {
                athletePortal.releasePointerCapture(e.pointerId);
            } catch (err) {}
        }

        // Restore status text to user's configured text
        if (pillStateText) {
            pillStateText.innerHTML = isHovered ? 'CEPAT' : 'FAKTUAL';
        }

        if (!isTicking) {
            isTicking = true;
            requestAnimationFrame(updateKineticStage);
        }
    }

    athletePortal.addEventListener('pointerup', finishDrag);
    athletePortal.addEventListener('pointercancel', finishDrag);

    // Scroll listener with RAF throttling
    window.addEventListener('scroll', () => {
        if (!isTicking) {
            isTicking = true;
            requestAnimationFrame(updateKineticStage);
        }
    }, { passive: true });

    function updatePhaseIndicator(scrollY) {
        let newPhase = 1;
        let text = 'FAKTUAL';

        if (scrollY >= 300) {
            newPhase = 3;
            text = 'TERPERCAYA';
        } else if (scrollY >= 60) {
            newPhase = 2;
            text = 'CEPAT';
        }

        if (newPhase !== currentPhase || !isHovered) {
            currentPhase = newPhase;
            if (!isHovered && !isDragging && pillStateText) {
                pillStateText.innerHTML = (currentPhase === 1) ? 'FAKTUAL' : text;
            }

            // Sync athlete active pose with scroll phase if not hovered and not dragging
            if (!isHovered && !isDragging && athleteLayers && athleteLayers.length > 1) {
                if (currentPhase === 1) {
                    setAthleteActivePose(0);
                } else if (currentPhase === 2) {
                    setAthleteActivePose(1);
                } else if (currentPhase === 3) {
                    setAthleteActivePose(athleteLayers.length > 2 ? 2 : 1);
                }
            }
        }
    }

    function updateKineticStage() {
        // Smooth mouse damping (lerp)
        currentMouseX += (targetMouseX - currentMouseX) * 0.08;
        currentMouseY += (targetMouseY - currentMouseY) * 0.08;

        // Smooth drag lerp or spring snap-back
        if (isDragging) {
            dragCurrentX += (dragTargetX - dragCurrentX) * 0.35;
            dragCurrentY += (dragTargetY - dragCurrentY) * 0.35;
        } else if (isSpringing || Math.abs(dragCurrentX) > 0.05 || Math.abs(dragCurrentY) > 0.05) {
            // Elastic spring return to origin
            dragCurrentX += (0 - dragCurrentX) * 0.18;
            dragCurrentY += (0 - dragCurrentY) * 0.18;
            if (Math.abs(dragCurrentX) < 0.2 && Math.abs(dragCurrentY) < 0.2) {
                dragCurrentX = 0;
                dragCurrentY = 0;
                isSpringing = false;
                if (window.scrollY < 30 && !isHovered) {
                    athletePortal.classList.add('is-idle-breathing');
                }
            }
        }

        const scrollY = window.scrollY;
        const maxScroll = 650;
        const scrollNorm = Math.min(Math.max(scrollY / maxScroll, 0), 1);

        // Manage breathing class on scroll
        if (scrollY > 30 || isDragging || isSpringing) {
            athletePortal.classList.remove('is-idle-breathing');
        } else if (!isHovered && !isDragging && !isSpringing) {
            athletePortal.classList.add('is-idle-breathing');
        }

        updatePhaseIndicator(scrollY);

        // Story-driven scroll physics calculation
        // 1. Forward Lean Angle (Pitch): scroll pitch + lateral drag tilt
        const dragTilt = (dragCurrentX * 0.055);
        const leanAngle = (scrollNorm * 4.8) + dragTilt;
        
        // 2. Combined Horizontal & Vertical Position
        const propulsionX = scrollNorm * 26 + (currentMouseX * 14) + dragCurrentX;
        const travelY = (currentMouseY * 12) - (scrollY * 0.12) + dragCurrentY;

        // 3. Dynamic Scale (Depth + subtle lift surge when dragged)
        const dynamicScale = (1 + (scrollNorm * 0.085)) * (isDragging ? 1.035 : 1);

        // Apply transforms to athlete viewport
        athletePortal.style.transform = `translate3d(${propulsionX.toFixed(1)}px, ${travelY.toFixed(1)}px, 0) scale(${dynamicScale.toFixed(3)}) rotate(${leanAngle.toFixed(2)}deg)`;

        // Athletic Ground Silhouette Shadow reacts to drag height and offset
        if (groundShadow) {
            const liftHeight = Math.max(-dragCurrentY, 0);
            const shadowScaleX = Math.max(1 - (liftHeight * 0.0025), 0.5);
            const shadowOpacity = Math.max(0.85 - (liftHeight * 0.0035), 0.15);
            const shadowFollowX = dragCurrentX * 0.28;
            groundShadow.style.transform = `scale(${shadowScaleX.toFixed(2)}) translate3d(${shadowFollowX.toFixed(1)}px, 0, 0)`;
            groundShadow.style.opacity = shadowOpacity.toFixed(2);
        }

        // Update Ghost Trail offsets and opacities based on velocity/scroll + drag
        if (trail1) {
            const trail1Opacity = Math.min(scrollNorm * 0.35 + (isHovered ? 0.35 : 0) + (isDragging ? 0.25 : 0), 0.5);
            trail1.style.opacity = trail1Opacity.toFixed(2);
            trail1.style.transform = `translate3d(${(-scrollNorm * 14 - (isHovered ? 8 : 0) - dragCurrentX * 0.2).toFixed(1)}px, ${(-scrollNorm * 4 - dragCurrentY * 0.2).toFixed(1)}px, 0) scale(${1 + scrollNorm * 0.02})`;
        }

        if (trail2) {
            const trail2Opacity = Math.min(Math.max((scrollNorm - 0.3) * 0.45, 0) + (isHovered ? 0.2 : 0) + (isDragging ? 0.15 : 0), 0.35);
            trail2.style.opacity = trail2Opacity.toFixed(2);
            trail2.style.transform = `translate3d(${(-scrollNorm * 28 - (isHovered ? 14 : 0) - dragCurrentX * 0.35).toFixed(1)}px, ${(-scrollNorm * 8 - dragCurrentY * 0.35).toFixed(1)}px, 0) scale(${1 + scrollNorm * 0.04})`;
        }

        // Atmosphere and energy lines opacity
        if (energyField) {
            const energyOp = Math.min(scrollNorm * 0.9 + (isHovered ? 0.8 : 0) + (isDragging ? 0.5 : 0), 1);
            energyField.style.opacity = energyOp.toFixed(2);
        }

        if (stageAura) {
            const auraScale = 1 + (scrollNorm * 0.2) + (isHovered ? 0.15 : 0) + (isDragging ? 0.18 : 0);
            const auraOpacity = 0.12 + (scrollNorm * 0.1) + (isHovered ? 0.1 : 0) + (isDragging ? 0.15 : 0);
            stageAura.style.setProperty('--aura-scale', auraScale.toFixed(2));
            stageAura.style.setProperty('--aura-opacity', auraOpacity.toFixed(2));
        }

        // Ambient background image parallax
        const activeBgImg = document.querySelector('#heroBgSlider .rl-hero-bg-slide.is-active .rl-hero-bg-img');
        if (activeBgImg) {
            const bgParallaxX = currentMouseX * -15;
            const bgParallaxY = (currentMouseY * -10) + (scrollY * 0.08);
            activeBgImg.style.transform = `translate3d(${bgParallaxX.toFixed(1)}px, ${bgParallaxY.toFixed(1)}px, 0) scale(1.06)`;
        }

        // Loop continuation criteria
        const isDragActive = isDragging || isSpringing || Math.abs(dragCurrentX) > 0.1 || Math.abs(dragCurrentY) > 0.1;
        const mouseDelta = Math.abs(targetMouseX - currentMouseX) + Math.abs(targetMouseY - currentMouseY);
        if (mouseDelta > 0.001 || isHovered || isDragActive) {
            requestAnimationFrame(updateKineticStage);
        } else {
            isTicking = false;
        }
    }
}

function initHeroBgSlider() {
    const slider = document.getElementById('heroBgSlider');
    const nav = document.getElementById('heroBgNav');
    if (!slider) return;

    const slides = slider.querySelectorAll('.rl-hero-bg-slide');
    const dots = nav ? nav.querySelectorAll('.rl-bg-dot') : [];
    if (slides.length <= 1) return;

    let currentIndex = 0;
    let timer = null;
    const intervalTime = 6500; // 6.5s per slide

    function goToSlide(index) {
        currentIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, i) => {
            slide.classList.toggle('is-active', i === currentIndex);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === currentIndex);
        });
    }

    function nextSlide() {
        goToSlide(currentIndex + 1);
    }

    function startAutoSlide() {
        if (timer) clearInterval(timer);
        timer = setInterval(nextSlide, intervalTime);
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            goToSlide(i);
            startAutoSlide();
        });
    });

    startAutoSlide();

    // Pause on tab hidden, resume on tab visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (timer) clearInterval(timer);
        } else {
            startAutoSlide();
        }
    });
}

function initFeaturedStripSlider() {
    const wrapper = document.getElementById('stripSliderWrapper');
    const track = document.getElementById('stripTrack');
    const prevBtn = document.getElementById('stripPrevBtn');
    const nextBtn = document.getElementById('stripNextBtn');
    const counterText = document.getElementById('stripCounterText');

    if (!wrapper || !track) return;

    const cards = track.querySelectorAll('.rl-strip-card');
    const totalCards = cards.length;
    if (totalCards <= 1) {
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        return;
    }

    let currentIndex = 0;
    let autoSlideTimer = null;
    const slideInterval = 4500; // 4.5 detik per slide

    function getVisibleCount() {
        const width = window.innerWidth;
        if (width >= 1024) return 3;
        if (width >= 640) return 2;
        return 1;
    }

    function getMaxIndex() {
        const visible = getVisibleCount();
        return Math.max(0, totalCards - visible);
    }

    function updateSlider() {
        const maxIndex = getMaxIndex();
        if (currentIndex > maxIndex) currentIndex = maxIndex;
        if (currentIndex < 0) currentIndex = 0;

        const firstCard = cards[0];
        if (!firstCard) return;

        // Card step width + gap (1rem = 16px)
        const cardWidth = firstCard.getBoundingClientRect().width;
        const gap = 16;
        const offset = currentIndex * (cardWidth + gap);

        track.style.transform = `translate3d(-${offset}px, 0, 0)`;

        // Clean natural Indonesian counter (misal: 1 dari 5)
        if (counterText) {
            counterText.textContent = `${currentIndex + 1} dari ${totalCards}`;
        }

        // Nav buttons state - responsif dan selalu siap navigasi
        if (prevBtn) {
            prevBtn.disabled = false;
            prevBtn.style.opacity = '1';
            prevBtn.style.cursor = 'pointer';
        }
        if (nextBtn) {
            nextBtn.disabled = false;
            nextBtn.style.opacity = '1';
            nextBtn.style.cursor = 'pointer';
        }
    }

    function nextSlide() {
        const maxIndex = getMaxIndex();
        if (currentIndex >= maxIndex) {
            currentIndex = 0; // Loop kembali ke awal
        } else {
            currentIndex++;
        }
        updateSlider();
    }

    function prevSlide() {
        const maxIndex = getMaxIndex();
        if (currentIndex <= 0) {
            currentIndex = maxIndex; // Loop ke akhir
        } else {
            currentIndex--;
        }
        updateSlider();
    }

    function startAutoSlide() {
        stopAutoSlide();
        autoSlideTimer = setInterval(nextSlide, slideInterval);
    }

    function stopAutoSlide() {
        if (autoSlideTimer) {
            clearInterval(autoSlideTimer);
            autoSlideTimer = null;
        }
    }

    prevBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        prevSlide();
        startAutoSlide(); // Reset jeda auto-slide saat ada interaksi
    });

    nextBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        nextSlide();
        startAutoSlide(); // Reset jeda auto-slide saat ada interaksi
    });

    // Jeda auto-slide saat kursor berada di atas card atau wrapper slider
    wrapper.addEventListener('mouseenter', stopAutoSlide);
    wrapper.addEventListener('mouseleave', startAutoSlide);

    // Touch swipe support untuk mobile / tablet
    let touchStartX = 0;
    let touchEndX = 0;

    wrapper.addEventListener('touchstart', (e) => {
        stopAutoSlide();
        touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });

    wrapper.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        const diffX = touchStartX - touchEndX;
        if (Math.abs(diffX) > 35) {
            if (diffX > 0) {
                nextSlide();
            } else {
                prevSlide();
            }
        }
        startAutoSlide();
    }, { passive: true });

    // Hentikan auto-slide jika tab browser di-minimize / pindah tab agar hemat performa
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoSlide();
        } else {
            startAutoSlide();
        }
    });

    window.addEventListener('resize', () => {
        updateSlider();
    });

    // Inisialisasi awal
    updateSlider();
    startAutoSlide();
}

function initEditorialDispatch() {
    const dispatchEl = document.getElementById('heroEditorialDispatch');
    if (!dispatchEl) return;

    const slides = [...dispatchEl.querySelectorAll('.rl-dispatch-slide')];
    if (slides.length <= 1) return;

    const prevBtn = document.getElementById('dispatchPrev');
    const nextBtn = document.getElementById('dispatchNext');
    const currentIdxEl = document.getElementById('dispatchCurrentIdx');

    let activeIndex = 0;
    let timer = null;
    const reducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function showSlide(index) {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, i) => {
            const isActive = i === activeIndex;
            slide.classList.toggle('is-active', isActive);
        });

        if (currentIdxEl) {
            currentIdxEl.textContent = String(activeIndex + 1).padStart(2, '0');
        }
    }

    function nextSlide() {
        showSlide(activeIndex + 1);
    }

    function prevSlide() {
        showSlide(activeIndex - 1);
    }

    function startAuto() {
        if (reducedMotion || document.hidden || timer) return;
        timer = setInterval(nextSlide, 6000);
    }

    function stopAuto() {
        if (!timer) return;
        clearInterval(timer);
        timer = null;
    }

    prevBtn?.addEventListener('click', () => {
        prevSlide();
        stopAuto();
        startAuto();
    });

    nextBtn?.addEventListener('click', () => {
        nextSlide();
        stopAuto();
        startAuto();
    });

    dispatchEl.addEventListener('mouseenter', stopAuto, { passive: true });
    dispatchEl.addEventListener('mouseleave', startAuto, { passive: true });
    dispatchEl.addEventListener('touchstart', stopAuto, { passive: true });
    dispatchEl.addEventListener('touchend', () => setTimeout(startAuto, 1000), { passive: true });

    document.addEventListener('visibilitychange', () => {
        document.hidden ? stopAuto() : startAuto();
    });

    startAuto();
}

async function loadUpcomingEvents() {
    const container = document.getElementById('homeEvents');
    if (!container) return;

    try {
        const response = await fetch('{{ route("api.events.upcoming") }}');
        const events = await response.json();

        if (!events || events.length === 0) {
            container.innerHTML = `
                <div class="rl-loading">
                    Belum ada event mendatang.
                </div>
            `;
            return;
        }

        container.innerHTML = '';

        events.slice(0, 6).forEach((event) => {
            const date = new Date(event.date + 'T' + (event.time || '00:00'));
            const day = String(date.getDate()).padStart(2, '0');
            const month = date.toLocaleString('id-ID', { month: 'short' }).toUpperCase();
            const year = date.getFullYear();

            const distances = (event.distances && event.distances.length)
                ? event.distances.map(distance => `<span>${escapeHTML(distance)}</span>`).join('')
                : '<span>RUN</span>';

            const item = document.createElement('a');
            item.href = event.url || '#';
            item.className = 'rl-event-item';

            item.innerHTML = `
                <div class="rl-event-date">
                    <strong>${day}</strong>
                    <span>${month}<br>${year}</span>
                </div>

                <div class="rl-event-main">
                    <h3>${escapeHTML(event.name || 'Event Lari')}</h3>
                    <p>${escapeHTML(event.location || 'Lokasi menyusul')} &middot; ${escapeHTML(event.time || 'TBA')} WIB</p>
                </div>

                <div class="rl-event-meta">
                    <div class="rl-event-distances">${distances}</div>
                    <div style="margin-top:.55rem">
                        ${event.is_eo ? 'Managed by EO' : 'RuangLari Listing'}
                    </div>
                </div>

                <span class="rl-event-arrow" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 12h14m-6-6 6 6-6 6"
                              stroke-width="1.7"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                    </svg>
                </span>
            `;

            container.appendChild(item);
        });
    } catch (error) {
        container.innerHTML = `
            <div class="rl-loading">
                Gagal memuat event. Silakan coba lagi.
            </div>
        `;
    }
}

async function loadLatestBlogs() {
    const container = document.getElementById('blogCards');
    if (!container) return;

    try {
        const response = await fetch('{{ route("api.blog.latest") }}');
        const posts = await response.json();

        if (!posts || posts.length === 0) {
            container.innerHTML = `
                <div class="rl-loading" style="grid-column: 1 / -1;">
                    Belum ada artikel yang dipublikasikan.
                </div>
            `;
            return;
        }

        container.innerHTML = '';

        posts.slice(0, 6).forEach((post) => {
            const url = post.url || '#';
            const title = post.title || 'Tanpa judul';
            const image = post.image || "{{ asset('ruanglari.webp') }}";
            const date = post.date
                ? new Date(post.date).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                })
                : '';

            const card = document.createElement('a');
            card.href = url;
            card.className = 'rl-journal-card';

            if (url !== '#') {
                card.target = '_blank';
                card.rel = 'noopener';
            }

            card.innerHTML = `
                <div>
                    <div class="rl-journal-thumb">
                        <img
                            src="${escapeAttribute(image)}"
                            alt="${escapeAttribute(stripHTML(title))}"
                            loading="lazy"
                            onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';"
                        >
                    </div>

                    <div class="rl-journal-body">
                        <div class="rl-journal-meta-top">
                            <span class="rl-journal-date">${escapeHTML(date)}</span>
                            <span class="rl-journal-cat">Journal</span>
                        </div>

                        <h3 class="rl-journal-title">${escapeHTML(stripHTML(title))}</h3>
                    </div>
                </div>

                <div class="rl-journal-footer">
                    <span>Baca Artikel</span>
                    <span class="rl-journal-arrow" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M5 12h14m-6-6 6 6-6 6"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            `;

            container.appendChild(card);
        });
    } catch (error) {
        container.innerHTML = `
            <div class="rl-loading" style="grid-column: 1 / -1;">
                Gagal memuat journal. Silakan coba lagi.
            </div>
        `;
    }
}

function initVdotWidget() {
    const distanceEl = document.getElementById('vdot_widget_distance');
    const timeGroup = document.getElementById('vdot_time_input_group');
    const metersGroup = document.getElementById('vdot_meters_input_group');
    const metersEl = document.getElementById('vdot_widget_meters');
    const hEl = document.getElementById('vdot_widget_h');
    const mEl = document.getElementById('vdot_widget_m');
    const sEl = document.getElementById('vdot_widget_s');
    const btnCalc = document.getElementById('btn-vdot-calculate');
    const resultBox = document.getElementById('vdot_widget_result');

    const tabPacesBtn = document.getElementById('vdot_tab_paces_btn');
    const tabRacesBtn = document.getElementById('vdot_tab_races_btn');
    const tabPacesContent = document.getElementById('vdot_tab_paces_content');
    const tabRacesContent = document.getElementById('vdot_tab_races_content');

    const scoreDisplay = document.getElementById('vdot_score_display');
    const fitnessLevelDisplay = document.getElementById('vdot_fitness_level');
    const easyPaceDisplay = document.getElementById('vdot_easy_pace');
    const marathonPaceDisplay = document.getElementById('vdot_marathon_pace');
    const tempoPaceDisplay = document.getElementById('vdot_tempo_pace');
    const intervalPaceDisplay = document.getElementById('vdot_interval_pace');
    const interval400mDisplay = document.getElementById('vdot_interval_400m');
    const repetitionPaceDisplay = document.getElementById('vdot_repetition_pace');
    const repetition400mDisplay = document.getElementById('vdot_repetition_400m');

    const race5kDisplay = document.getElementById('vdot_race_5k');
    const race10kDisplay = document.getElementById('vdot_race_10k');
    const race21kDisplay = document.getElementById('vdot_race_21k');
    const race42kDisplay = document.getElementById('vdot_race_42k');
    const actionBtn = document.getElementById('vdot_widget_action_btn');

    if (!distanceEl || !btnCalc || !resultBox) return;

    distanceEl.addEventListener('change', function () {
        const isFieldTest = ['cooper12', 'balke15'].includes(distanceEl.value);
        timeGroup.classList.toggle('hidden', isFieldTest);
        metersGroup.classList.toggle('hidden', !isFieldTest);
    });

    function selectTab(type) {
        const pacesActive = type === 'paces';
        tabPacesBtn?.classList.toggle('is-active', pacesActive);
        tabRacesBtn?.classList.toggle('is-active', !pacesActive);
        tabPacesContent?.classList.toggle('hidden', !pacesActive);
        tabRacesContent?.classList.toggle('hidden', pacesActive);
    }

    tabPacesBtn?.addEventListener('click', () => selectTab('paces'));
    tabRacesBtn?.addEventListener('click', () => selectTab('races'));

    btnCalc.addEventListener('click', function () {
        const dist = distanceEl.value;
        let timeMinutes = 0;
        let distMeters = 5000;
        let totalSeconds = 0;

        if (dist === 'cooper12') {
            timeMinutes = 12;
            distMeters = parseFloat(metersEl.value || '0');
            if (distMeters <= 500) {
                alert('Harap masukkan jarak Cooper Test yang valid (meter).');
                return;
            }
            totalSeconds = 720;
        } else if (dist === 'balke15') {
            timeMinutes = 15;
            distMeters = parseFloat(metersEl.value || '0');
            if (distMeters <= 500) {
                alert('Harap masukkan jarak Balke Test yang valid (meter).');
                return;
            }
            totalSeconds = 900;
        } else {
            const h = parseInt(hEl.value || '0', 10);
            const m = parseInt(mEl.value || '0', 10);
            const s = parseInt(sEl.value || '0', 10);

            totalSeconds = (h * 3600) + (m * 60) + s;

            if (totalSeconds <= 60) {
                alert('Silakan masukkan waktu PB yang valid.');
                return;
            }

            timeMinutes = totalSeconds / 60;

            if (dist === '5K') distMeters = 5000;
            else if (dist === '10K') distMeters = 10000;
            else if (dist === '21K') distMeters = 21097.5;
            else if (dist === '42K') distMeters = 42195;
        }

        const velocity = distMeters / timeMinutes;
        const vo2 = -4.60 + 0.182258 * velocity + 0.000104 * Math.pow(velocity, 2);
        const percentMax =
            0.8 +
            0.1894393 * Math.exp(-0.012778 * timeMinutes) +
            0.2989558 * Math.exp(-0.1932605 * timeMinutes);

        const vdotVal = Math.max(15, Math.min(85, vo2 / percentMax));

        const a = 0.000104;
        const b = 0.182258;
        const c = -4.6 - vdotVal;
        const vVO2max =
            (-b + Math.sqrt(Math.pow(b, 2) - 4 * a * c)) / (2 * a);

        const eHighVelocity = vVO2max * 0.72;
        const eLowVelocity = vVO2max * 0.66;
        const eHighSecPerKm = Math.round((1000 / eHighVelocity) * 60);
        const eLowSecPerKm = Math.round((1000 / eLowVelocity) * 60);

        const mVelocity = vVO2max * 0.82;
        const mSecPerKm = Math.round((1000 / mVelocity) * 60);

        const tVelocity = vVO2max * 0.88;
        const tSecPerKm = Math.round((1000 / tVelocity) * 60);

        const iVelocity = vVO2max * 0.97;
        const iSecPerKm = Math.round((1000 / iVelocity) * 60);
        const iSec400m = Math.round(iSecPerKm * 0.4);

        const rVelocity = vVO2max * 1.05;
        const rSecPerKm = Math.round((1000 / rVelocity) * 60);
        const rSec400m = Math.round(rSecPerKm * 0.4);

        const formatSecToMinKm = (sec) => {
            const min = Math.floor(sec / 60);
            const seconds = Math.round(sec % 60);
            return `${String(min).padStart(2, '0')}:${String(seconds).padStart(2, '0')}/km`;
        };

        const formatTimeFromSec = (sec) => {
            const h = Math.floor(sec / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const s = Math.round(sec % 60);

            return h > 0
                ? `${h}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
                : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
        };

        let level = 'Beginner';
        if (vdotVal >= 60) level = 'Elite / Advanced';
        else if (vdotVal >= 48) level = 'Advanced';
        else if (vdotVal >= 38) level = 'Intermediate';

        scoreDisplay.innerText = vdotVal.toFixed(1);
        fitnessLevelDisplay.innerText = level;
        easyPaceDisplay.innerText =
            `${formatSecToMinKm(eHighSecPerKm)} - ${formatSecToMinKm(eLowSecPerKm)}`;

        if (marathonPaceDisplay) marathonPaceDisplay.innerText = formatSecToMinKm(mSecPerKm);
        tempoPaceDisplay.innerText = formatSecToMinKm(tSecPerKm);
        intervalPaceDisplay.innerText = formatSecToMinKm(iSecPerKm);
        interval400mDisplay.innerText = `(${iSec400m}s / 400m)`;
        repetitionPaceDisplay.innerText = formatSecToMinKm(rSecPerKm);
        repetition400mDisplay.innerText = `(${rSec400m}s / 400m)`;

        [
            { id: race5kDisplay, distM: 5000, ratio: 0.957 },
            { id: race10kDisplay, distM: 10000, ratio: 0.915 },
            { id: race21kDisplay, distM: 21097.5, ratio: 0.865 },
            { id: race42kDisplay, distM: 42195, ratio: 0.815 }
        ].forEach(item => {
            const raceVelocity = vVO2max * item.ratio;
            const raceSeconds = (item.distM / raceVelocity) * 60;
            const racePace = (1000 / raceVelocity) * 60;

            if (item.id) {
                item.id.innerText =
                    `${formatTimeFromSec(raceSeconds)} (${formatSecToMinKm(racePace)})`;
            }
        });

        const distParam = dist.toLowerCase();

        if (['cooper12', 'balke15'].includes(distParam)) {
            actionBtn.href =
                `{{ route('programs.realistic') }}?distance=${encodeURIComponent(distParam)}&meters=${encodeURIComponent(distMeters)}`;
        } else {
            const h = parseInt(hEl.value || '0', 10);
            const m = parseInt(mEl.value || '0', 10);
            const s = parseInt(sEl.value || '0', 10);

            const formattedTime =
                `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

            actionBtn.href =
                `{{ route('programs.realistic') }}?distance=${encodeURIComponent(distParam)}&time=${encodeURIComponent(formattedTime)}`;
        }

        resultBox.classList.remove('hidden');

        requestAnimationFrame(() => {
            resultBox.classList.remove('opacity-0', 'translate-y-4');
            resultBox.classList.add('opacity-100', 'translate-y-0');
        });
    });
}

function stripHTML(value) {
    const div = document.createElement('div');
    div.innerHTML = value || '';
    return div.textContent || div.innerText || '';
}

function escapeHTML(value) {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
}

function escapeAttribute(value) {
    return escapeHTML(value).replace(/"/g, '&quot;');
}
</script>
@endpush