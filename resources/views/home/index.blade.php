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
    <header class="rl-hero">
        <div class="rl-shell">
            <div class="rl-hero-grid">
                
                {{-- Hero Copy --}}
                <div class="rl-hero-copy">
                    <div class="rl-kicker">
                        <span class="rl-kicker-mark"></span>
                        <span class="rl-kicker-text">RUANGLARI &bull; ENGINE &amp; ECOSYSTEM</span>
                    </div>

                    <h1 class="rl-hero-title">
                        Platform &amp; Media Lari
                        <span class="rl-title-accent">Indonesia.</span>
                    </h1>

                    <p class="rl-hero-lead">
                        Pusat direktori race terverifikasi, kalkulator pace VDOT fisiologis, bank rute GPX, dan ruang kolaborasi pelari dalam satu ekosistem terintegrasi.
                    </p>

                    <div class="rl-hero-actions">
                        <a href="#vdot-section"
                           onclick="event.preventDefault(); document.getElementById('vdot-section')?.scrollIntoView({behavior:'smooth'});"
                           class="rl-btn rl-btn-primary">
                            <span>Hitung Pace &amp; VDOT</span>
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14m-6-6 6 6-6 6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>

                        <a href="{{ route('events.index') }}" class="rl-btn rl-btn-outline">
                            Jelajahi Race
                        </a>
                    </div>

                    <div class="rl-hero-telemetry">
                        <div class="rl-tele-cell">
                            <span class="rl-tele-val font-mono">1.000+</span>
                            <span class="rl-tele-lbl">Pelari Aktif</span>
                        </div>
                        <div class="rl-tele-pipe"></div>
                        <div class="rl-tele-cell">
                            <span class="rl-tele-val font-mono">50+</span>
                            <span class="rl-tele-lbl">Verified Race</span>
                        </div>
                        <div class="rl-tele-pipe"></div>
                        <div class="rl-tele-cell">
                            <span class="rl-tele-val font-mono">VDOT</span>
                            <span class="rl-tele-lbl">Training Model</span>
                        </div>
                    </div>
                </div>

                {{-- Hero Featured Spotlight Deck — Premium Running Media Newsroom --}}
                <div class="rl-featured">
                    @php
                        $articles = ($featuredArticles ?? collect())->filter(fn($a) => !empty($a->slug))->take(5);
                    @endphp

                    @if($articles->isNotEmpty())
                        {{-- Magazine Newsroom Slider Deck --}}
                        <div id="heroFeatured" class="rl-deck">
                            {{-- Top Header Deck: RUNNING NEWS & Counter --}}
                            <div class="rl-deck-head">
                                <div class="rl-deck-status">
                                    <span class="rl-deck-brand font-mono">RUNNING NEWS</span>
                                </div>
                                
                                <div class="rl-deck-pager font-mono">
                                    <span class="rl-deck-index-wrap">
                                        <strong id="heroCurrentIndex" class="rl-idx-active">01</strong>
                                        <span class="rl-divider-slash">/</span>
                                        <span class="rl-total-slides">{{ str_pad($articles->count(), 2, '0', STR_PAD_LEFT) }}</span>
                                    </span>
                                    <div class="rl-deck-buttons">
                                        <button type="button" id="heroFeaturedPrev" aria-label="Slide sebelumnya" class="rl-btn-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 19l-7-7 7-7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                        <button type="button" id="heroFeaturedNext" aria-label="Slide berikutnya" class="rl-btn-icon">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 5l7 7-7 7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Track Slider --}}
                            <div id="heroFeaturedTrack" class="rl-deck-track no-scrollbar">
                                @foreach($articles as $i => $article)
                                    <article class="rl-deck-slide" data-slide-index="{{ $i }}">
                                        {{-- Foto Full Width (Elemen Utama) --}}
                                        <div class="rl-slide-viewport">
                                            <img
                                                src="{{ $article->getFeaturedImageUrl() }}"
                                                alt="{{ $article->title }}"
                                                draggable="false"
                                                @if($i === 0) fetchpriority="high" @else loading="lazy" @endif
                                                onerror="this.onerror=null; this.src='{{ asset('ruanglari.webp') }}';"
                                                class="rl-slide-photo"
                                            >
                                            <div class="rl-slide-scrim"></div>

                                            {{-- Magazine Category Overlay --}}
                                            <div class="rl-cover-headline-wrap">
                                                <div class="rl-cover-display">
                                                    {{ $article->category?->name ?: 'RUNNING JOURNAL' }}
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Editorial Dispatch Block (Under Photo) --}}
                                        <div class="rl-slide-editorial">
                                            <div class="rl-editorial-eyebrow font-mono">
                                                <span class="rl-eyebrow-accent"></span>
                                                <span class="rl-eyebrow-text">EDITORIAL DISPATCH</span>
                                                @if($article->category)
                                                    <span class="rl-eyebrow-sep">&bull;</span>
                                                    <span class="rl-eyebrow-cat">{{ $article->category->name }}</span>
                                                @endif
                                            </div>

                                            <h2 class="rl-editorial-headline">
                                                <a href="{{ route('blog.show', $article->slug) }}">
                                                    {{ $article->title }}
                                                </a>
                                            </h2>

                                            <div class="rl-editorial-meta font-mono">
                                                @if($article->published_at)
                                                    <span>{{ $article->published_at->translatedFormat('d M Y') }}</span>
                                                @elseif($article->created_at)
                                                    <span>{{ $article->created_at->translatedFormat('d M Y') }}</span>
                                                @endif
                                                @if($article->category)
                                                    <span class="rl-meta-sep">&bull;</span>
                                                    <span>{{ $article->category->name }}</span>
                                                @endif
                                            </div>

                                            <div class="rl-editorial-action">
                                                <a href="{{ route('blog.show', $article->slug) }}" class="rl-editorial-cta font-mono">
                                                    <span>BACA CERITA</span>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path d="M5 12h14m-6-6 6 6-6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            {{-- Progress Segments --}}
                            <div id="heroFeaturedDots" class="rl-deck-progress"></div>
                        </div>

                        {{-- Bottom Slider: Indeks Majalah --}}
                        @if($articles->count() > 1)
                            <nav class="rl-magazine-index" id="heroFeaturedPlaylist" aria-label="Indeks Berita Majalah">
                                <div class="rl-index-grid no-scrollbar">
                                    @foreach($articles as $i => $article)
                                        <button type="button"
                                                class="rl-index-item {{ $i === 0 ? 'is-active' : '' }}"
                                                data-tab-index="{{ $i }}"
                                                aria-label="Pilih: {{ $article->title }}">
                                            <span class="rl-index-num font-mono">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="rl-index-title">{{ Str::limit($article->title, 26) }}</span>
                                            <span class="rl-index-indicator"></span>
                                        </button>
                                    @endforeach
                                </div>
                            </nav>
                        @endif
                    @endif
                </div>

            </div>
        </div>
    </header>

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
    :root {
        --rl-bg: #050505;
        --rl-bg-2: #090B0E;
        --rl-panel: #0D0F12;
        --rl-surface: #0D0F12;
        --rl-line: #1F2228;
        --rl-border-subtle: rgba(255, 255, 255, 0.08);
        --rl-text: #F5F5F0;
        --rl-muted: #9E9E96;
        --rl-lime: #B8FF00;
        --rl-lime-hover: #CBFF4A;
        --rl-font: 'Plus Jakarta Sans', 'Inter', system-ui, -apple-system, sans-serif;
        --rl-mono: 'Space Mono', SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    }

    html { scroll-behavior: smooth; }

    .rl-home {
        background: var(--rl-bg);
        color: var(--rl-text);
        overflow-x: hidden;
        width: 100%;
    }

    .rl-shell {
        width: min(100% - 2rem, 80rem);
        margin-inline: auto;
        box-sizing: border-box;
    }

    /* ----------------------------------------------------
       HERO FOUNDATION & GRID
    ---------------------------------------------------- */
    .rl-hero {
        position: relative;
        background: radial-gradient(circle at 85% 15%, rgba(184, 255, 0, 0.04) 0%, transparent 40%),
                    linear-gradient(180deg, #070D18 0%, #050912 100%);
        border-bottom: 1px solid var(--rl-line);
        padding: clamp(3.5rem, 6.5vw, 6rem) 0;
        overflow: hidden;
    }

    .rl-hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 0.92fr) minmax(0, 1.08fr);
        gap: clamp(2.5rem, 4.5vw, 4.5rem);
        align-items: center;
        width: 100%;
    }

    /* Kicker & Athletic Headline */
    .rl-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        background: #0D1626;
        border: 1px solid rgba(184, 255, 0, 0.3);
        padding: 0.35rem 0.75rem;
        border-radius: 2px;
    }

    .rl-kicker-mark {
        width: 6px;
        height: 6px;
        background: var(--rl-lime);
        border-radius: 1px;
    }

    .rl-kicker-text {
        color: var(--rl-lime);
        font-family: var(--rl-mono);
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        line-height: 1;
    }

    .rl-hero-title {
        margin: 1.25rem 0 0;
        color: #FFFFFF;
        font-size: clamp(2.4rem, 4.2vw, 3.8rem);
        font-weight: 950;
        line-height: 1.05;
        letter-spacing: -0.035em;
        text-transform: uppercase;
    }

    .rl-title-accent {
        display: block;
        color: var(--rl-lime);
    }

    .rl-hero-lead {
        margin: 1.5rem 0 0;
        color: #94A3B8;
        font-size: clamp(0.95rem, 1.2vw, 1.05rem);
        line-height: 1.7;
        max-width: 32rem;
    }

    /* Action Buttons */
    .rl-hero-actions {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .rl-btn {
        min-height: 3.1rem;
        padding: 0 1.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        font-size: 0.72rem;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-radius: 2px;
        transition: all 0.15s ease;
        text-decoration: none;
        cursor: pointer;
        box-sizing: border-box;
    }

    .rl-btn-primary {
        background: var(--rl-lime);
        color: #05080E;
        border: 1px solid var(--rl-lime);
    }
    .rl-btn-primary:hover {
        background: var(--rl-lime-hover);
        border-color: var(--rl-lime-hover);
        transform: translateY(-1px);
    }

    .rl-btn-outline {
        background: rgba(255, 255, 255, 0.02);
        color: #FFFFFF;
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    .rl-btn-outline:hover {
        background: rgba(255, 255, 255, 0.07);
        border-color: rgba(255, 255, 255, 0.35);
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

    /* Telemetry Bar */
    .rl-hero-telemetry {
        margin-top: 2.75rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--rl-line);
        display: flex;
        align-items: center;
        gap: 1.75rem;
    }

    .rl-tele-cell {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }

    .rl-tele-val {
        color: #FFFFFF;
        font-size: 1.3rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.02em;
    }

    .rl-tele-lbl {
        color: #64748B;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .rl-tele-pipe {
        width: 1px;
        height: 1.75rem;
        background: var(--rl-line);
    }

    /* ----------------------------------------------------
       PREMIUM RUNNING MEDIA NEWSROOM — MAGAZINE COVER SLIDER
    ---------------------------------------------------- */
    .rl-featured {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    .rl-deck {
        background: #0D0F12;
        border: 1px solid #1F2228;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 20px 45px -12px rgba(0, 0, 0, 0.9);
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
    }

    /* Magazine Top Header Rail */
    .rl-deck-head {
        background: #07090C;
        border-bottom: 1px solid #1A1D23;
        padding: 0.8rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .rl-deck-status {
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .rl-deck-brand {
        color: #F5F5F0;
        font-family: var(--rl-mono);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .rl-deck-brand::before {
        content: '';
        display: inline-block;
        width: 3px;
        height: 10px;
        background: var(--rl-lime);
    }

    .rl-deck-pager {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }

    .rl-deck-index-wrap {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-family: var(--rl-mono);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
    }

    .rl-idx-active {
        color: var(--rl-lime);
        font-weight: 800;
        font-size: 0.82rem;
    }

    .rl-divider-slash {
        color: #3F444E;
    }

    .rl-total-slides {
        color: #73736C;
    }

    .rl-deck-buttons {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .rl-btn-icon {
        width: 1.85rem;
        height: 1.85rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #101318;
        border: 1px solid #222731;
        border-radius: 2px;
        color: #9E9E96;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .rl-btn-icon svg {
        width: 0.85rem;
        height: 0.85rem;
    }

    .rl-btn-icon:hover {
        background: #171C23;
        border-color: #3B4353;
        color: #F5F5F0;
    }

    /* Slider Track */
    .rl-deck-track {
        position: relative;
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        touch-action: pan-y;
        user-select: none;
        -webkit-user-select: none;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        cursor: grab;
    }

    .rl-deck-track.is-dragging {
        cursor: grabbing;
        scroll-snap-type: none;
    }

    .rl-deck-slide {
        flex: 0 0 100%;
        width: 100%;
        min-width: 100%;
        max-width: 100%;
        scroll-snap-align: start;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
    }

    /* FOTO FULL WIDTH (Elemen Utama) */
    .rl-slide-viewport {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 10;
        min-height: 250px;
        max-height: 380px;
        background: #050505;
        overflow: hidden;
    }

    .rl-slide-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        pointer-events: none;
    }

    .rl-deck-slide:hover .rl-slide-photo {
        transform: scale(1.02);
    }

    /* Subtle Scrim for Depth */
    .rl-slide-scrim {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            180deg,
            rgba(5, 5, 5, 0.08) 0%,
            rgba(5, 5, 5, 0.15) 35%,
            rgba(13, 15, 18, 0.6) 75%,
            #0D0F12 100%
        );
        pointer-events: none;
    }

    /* Magazine Cover Typography Overlay (Inside Photo) */
    .rl-cover-headline-wrap {
        position: absolute;
        bottom: 1.25rem;
        right: 1.25rem;
        left: 1.25rem;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
        pointer-events: none;
        z-index: 2;
    }

    .rl-cover-display {
        font-family: var(--rl-font);
        font-size: clamp(1.4rem, 2.5vw, 2.15rem);
        font-weight: 950;
        line-height: 0.94;
        letter-spacing: -0.04em;
        text-transform: uppercase;
        color: #FFFFFF;
        text-shadow: 0 4px 18px rgba(0, 0, 0, 0.95);
        max-width: 20ch;
    }

    /* EDITORIAL DISPATCH BLOCK (Under Photo) */
    .rl-slide-editorial {
        background: #0D0F12;
        border-top: 1px solid #1A1D23;
        padding: 1.35rem 1.45rem 1.5rem;
        display: flex;
        flex-direction: column;
        flex: 1;
        box-sizing: border-box;
    }

    /* Badge Kategori Kecil (Typographic Eyebrow, Bukan Kotak) */
    .rl-editorial-eyebrow {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.45rem;
        font-family: var(--rl-mono);
        font-size: 0.64rem;
        font-weight: 700;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        color: #9E9E96;
    }

    .rl-eyebrow-accent {
        width: 5px;
        height: 5px;
        background: var(--rl-lime);
        border-radius: 1px;
    }

    .rl-eyebrow-text {
        color: var(--rl-lime);
        font-weight: 800;
    }

    .rl-eyebrow-sep {
        color: #3F444E;
    }

    .rl-eyebrow-cat {
        color: #D4D4CE;
    }

    .rl-eyebrow-status {
        color: var(--rl-lime);
        font-weight: 800;
    }

    /* Magazine Headline (Sleek, Athletic & Bold) */
    .rl-editorial-headline {
        margin: 0.65rem 0 0.75rem;
        font-family: var(--rl-font);
        font-size: clamp(1.2rem, 2vw, 1.6rem);
        font-weight: 900;
        line-height: 1.2;
        letter-spacing: -0.03em;
        text-transform: uppercase;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .rl-editorial-headline a {
        color: #F5F5F0;
        text-decoration: none;
        transition: color 0.15s ease;
    }

    .rl-editorial-headline a:hover {
        color: var(--rl-lime);
    }

    /* Metadata Row (Date & Location) */
    .rl-editorial-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-family: var(--rl-mono);
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #73736C;
        margin-bottom: 1.25rem;
    }

    .rl-meta-sep {
        color: #3F444E;
    }

    /* CTA Button (BACA CERITA) */
    .rl-editorial-action {
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #171A20;
    }

    .rl-editorial-cta {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        background: #11141A;
        color: #F5F5F0;
        border: 1px solid #222731;
        padding: 0.75rem 1.15rem;
        border-radius: 3px;
        font-family: var(--rl-mono);
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        text-decoration: none;
        transition: all 0.15s ease;
        box-sizing: border-box;
    }

    .rl-editorial-cta svg {
        width: 0.85rem;
        height: 0.85rem;
        color: var(--rl-lime);
        transition: transform 0.15s ease;
    }

    .rl-editorial-cta:hover {
        background: #171C24;
        border-color: var(--rl-lime);
        color: #FFFFFF;
    }

    .rl-editorial-cta:hover svg {
        transform: translateX(4px);
    }

    /* Thin Segment Progress */
    .rl-deck-progress {
        height: 2px;
        background: #0E1015;
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: 1fr;
        gap: 1px;
    }

    .rl-progress-segment {
        background: #1A1E26;
        border: none;
        padding: 0;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .rl-progress-segment.is-active {
        background: var(--rl-lime);
    }

    /* BOTTOM SLIDER: INDEKS MAJALAH */
    .rl-magazine-index {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin-top: 0.65rem;
        box-sizing: border-box;
    }

    .rl-index-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
        gap: 0.4rem;
        width: 100%;
        box-sizing: border-box;
    }

    .rl-index-item {
        display: flex;
        align-items: baseline;
        gap: 0.55rem;
        background: #090B0E;
        border: 1px solid #181B22;
        border-radius: 3px;
        padding: 0.65rem 0.85rem;
        position: relative;
        text-align: left;
        cursor: pointer;
        transition: all 0.15s ease;
        box-sizing: border-box;
        overflow: hidden;
    }

    .rl-index-item:hover {
        background: #0E1116;
        border-color: #262B36;
    }

    .rl-index-item.is-active {
        background: #11141B;
        border-color: #2E3544;
    }

    .rl-index-num {
        font-family: var(--rl-mono);
        font-size: 0.72rem;
        font-weight: 800;
        color: #52524E;
        letter-spacing: 0.05em;
        transition: color 0.15s ease;
        flex-shrink: 0;
    }

    .rl-index-item.is-active .rl-index-num {
        color: var(--rl-lime);
    }

    .rl-index-title {
        color: #73736C;
        font-size: 0.74rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color 0.15s ease;
    }

    .rl-index-item:hover .rl-index-title {
        color: #B5B5AF;
    }

    .rl-index-item.is-active .rl-index-title {
        color: #F5F5F0;
        font-weight: 700;
    }

    .rl-index-indicator {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: transparent;
        transition: background 0.15s ease;
    }

    .rl-index-item.is-active .rl-index-indicator {
        background: var(--rl-lime);
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
            width: calc(100% - 1.5rem);
            max-width: 100%;
        }

        .rl-hero {
            padding: 2rem 0 3rem;
            width: 100%;
        }

        /* Prevent Grid blow-out / horizontal page drift */
        .rl-hero-grid,
        .rl-hero-copy,
        .rl-featured,
        .rl-deck,
        .rl-deck-slide {
            min-width: 0 !important;
            width: 100% !important;
            box-sizing: border-box;
        }

        .rl-hero-grid {
            gap: 1.75rem;
            grid-template-columns: minmax(0, 1fr) !important;
        }

        .rl-hero-copy { order: 2; }
        .rl-featured { order: 1; }

        .rl-hero-title {
            font-size: clamp(1.85rem, 7.5vw, 2.4rem);
            line-height: 1.12;
            letter-spacing: -.03em;
            word-break: break-word;
        }

        .rl-hero-lead {
            margin-top: 1.1rem;
            font-size: 0.92rem;
            line-height: 1.65;
        }

        .rl-hero-actions {
            flex-direction: column;
            width: 100%;
            gap: 0.65rem;
            margin-top: 1.5rem;
        }

        .rl-hero-actions .rl-btn {
            width: 100%;
            justify-content: center;
        }

        /* Mobile Telemetry Grid - Safe & balanced columns */
        .rl-hero-telemetry {
            display: grid;
            grid-template-columns: 1fr auto 1fr auto 1fr;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding-top: 1.25rem;
            width: 100%;
            box-sizing: border-box;
        }

        .rl-tele-cell { text-align: center; min-width: 0; }
        .rl-tele-val { font-size: 1.15rem; }
        .rl-tele-lbl { font-size: 0.58rem; line-height: 1.2; }
        .rl-tele-pipe { height: 1.5rem; }

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
    initFeaturedSlider();
    initVdotWidget();
    loadUpcomingEvents();
    loadLatestBlogs();
});

function initFeaturedSlider() {
    const track = document.getElementById('heroFeaturedTrack');
    const dots = document.getElementById('heroFeaturedDots');
    const prev = document.getElementById('heroFeaturedPrev');
    const next = document.getElementById('heroFeaturedNext');
    const currentIndexEl = document.getElementById('heroCurrentIndex');
    const playlist = document.getElementById('heroFeaturedPlaylist');
    const playlistTabs = playlist ? [...playlist.querySelectorAll('.rl-index-item, .rl-playlist-item')] : [];

    if (!track) return;

    const slides = [...track.children];
    if (slides.length <= 1) return;

    const reducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let activeIndex = 0;
    let timer = null;
    let isProgrammaticScrolling = false;
    let scrollLockTimeout = null;

    function updateNav() {
        if (currentIndexEl) {
            currentIndexEl.textContent = String(activeIndex + 1).padStart(2, '0');
        }

        if (dots) {
            [...dots.children].forEach((item, index) => {
                const isActive = index === activeIndex;
                item.classList.toggle('is-active', isActive);
                item.setAttribute('aria-current', isActive ? 'true' : 'false');
            });
        }

        if (playlistTabs.length) {
            playlistTabs.forEach((tab, index) => {
                tab.classList.toggle('is-active', index === activeIndex);
            });
            const activeTab = playlistTabs[activeIndex];
            if (activeTab && playlist) {
                const grid = playlist.querySelector('.rl-index-grid') || playlist;
                if (grid && grid.scrollWidth > grid.clientWidth) {
                    const tabLeft = activeTab.offsetLeft;
                    const tabWidth = activeTab.offsetWidth;
                    const gridWidth = grid.clientWidth;
                    grid.scrollTo({
                        left: tabLeft - (gridWidth / 2) + (tabWidth / 2),
                        behavior: 'smooth'
                    });
                }
            }
        }
    }

    function goTo(index, smooth = true) {
        activeIndex = (index + slides.length) % slides.length;
        const targetSlide = slides[activeIndex];

        isProgrammaticScrolling = true;
        clearTimeout(scrollLockTimeout);

        if (targetSlide) {
            track.scrollTo({
                left: targetSlide.offsetLeft,
                behavior: (reducedMotion || !smooth) ? 'auto' : 'smooth'
            });
        }

        updateNav();

        scrollLockTimeout = setTimeout(() => {
            isProgrammaticScrolling = false;
        }, (reducedMotion || !smooth) ? 60 : 500);
    }

    function detectActive() {
        if (isProgrammaticScrolling) return;

        const width = track.clientWidth;
        if (!width) return;

        const scrollLeft = track.scrollLeft;
        const rawIndex = Math.round(scrollLeft / width);
        const newIndex = Math.max(0, Math.min(slides.length - 1, rawIndex));

        if (newIndex !== activeIndex) {
            activeIndex = newIndex;
            updateNav();
        }
    }

    function stopAuto() {
        if (!timer) return;
        clearInterval(timer);
        timer = null;
    }

    function startAuto() {
        if (reducedMotion || document.hidden || timer) return;
        timer = setInterval(() => goTo(activeIndex + 1), 7000);
    }

    function restartAuto() {
        stopAuto();
        startAuto();
    }

    // Initialize progress dots
    if (dots) {
        dots.innerHTML = '';
        slides.forEach((_, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'rl-progress-segment';
            button.setAttribute('aria-label', `Tampilkan slide ${index + 1}`);
            button.addEventListener('click', () => {
                goTo(index);
                restartAuto();
            });
            dots.appendChild(button);
        });
    }

    // Initialize playlist tabs
    if (playlistTabs.length) {
        playlistTabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                goTo(index);
                restartAuto();
            });
        });
    }

    // Scroll listener for manual swipe / scroll
    let scrollRaf = null;
    track.addEventListener('scroll', () => {
        if (isProgrammaticScrolling) return;
        if (scrollRaf) cancelAnimationFrame(scrollRaf);
        scrollRaf = requestAnimationFrame(() => {
            detectActive();
        });
    }, { passive: true });

    if ('onscrollend' in window) {
        track.addEventListener('scrollend', () => {
            isProgrammaticScrolling = false;
            detectActive();
        }, { passive: true });
    }

    // Hover and touch pause
    track.addEventListener('mouseenter', stopAuto, { passive: true });
    track.addEventListener('mouseleave', startAuto, { passive: true });

    track.addEventListener('touchstart', stopAuto, { passive: true });
    track.addEventListener('touchend', () => {
        setTimeout(restartAuto, 1000);
    }, { passive: true });

    prev?.addEventListener('click', () => {
        goTo(activeIndex - 1);
        restartAuto();
    });

    next?.addEventListener('click', () => {
        goTo(activeIndex + 1);
        restartAuto();
    });

    document.addEventListener('visibilitychange', () => {
        document.hidden ? stopAuto() : startAuto();
    });

    updateNav();
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
            `${formatSecToMinKm(eHighSecPerKm)} - ${formatSecToMinKm(eLowLow || eLowSecPerKm)}`;

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