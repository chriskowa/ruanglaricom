<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>{{ $metaTitle ?? ('Hasil Lomba ' . ($raceName ?? 'Lari') . ' | Leaderboard & Kartu Finisher') }}</title>
    <meta name="title" content="{{ $metaTitle ?? ('Hasil Lomba ' . ($raceName ?? 'Lari') . ' | Leaderboard & Kartu Finisher') }}">
    <meta name="description" content="{{ $metaDesc ?? ('Lihat hasil resmi, leaderboard, peringkat, waktu finish, dan unduh Kartu Finisher 4:5 resmi lomba ' . ($raceName ?? 'Lari') . ' di RuangLari.com.') }}">
    <meta name="keywords" content="hasil lomba {{ strtolower($raceName ?? 'lari') }}, leaderboard {{ strtolower($raceName ?? 'lari') }}, race result {{ strtolower($raceName ?? 'lari') }}, kartu finisher {{ strtolower($raceName ?? 'lari') }}, finisher certificate {{ strtolower($raceName ?? 'lari') }}, timing gate ruang lari">
    <link rel="canonical" href="{{ route('tools.race-master.results', ['slug' => $slug]) }}">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('tools.race-master.results', ['slug' => $slug]) }}">
    <meta property="og:title" content="{{ $metaTitle ?? ('Hasil Lomba ' . ($raceName ?? 'Lari') . ' | Leaderboard & Kartu Finisher') }}">
    <meta property="og:description" content="{{ $metaDesc ?? ('Lihat hasil resmi leaderboard dan unduh Kartu Finisher resmi lomba ' . ($raceName ?? 'Lari') . ' di RuangLari.com.') }}">
    <meta property="og:image" content="https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp">
    <meta property="og:site_name" content="RuangLari.com">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ route('tools.race-master.results', ['slug' => $slug]) }}">
    <meta name="twitter:title" content="{{ $metaTitle ?? ('Hasil Lomba ' . ($raceName ?? 'Lari') . ' | Leaderboard & Kartu Finisher') }}">
    <meta name="twitter:description" content="{{ $metaDesc ?? ('Lihat hasil resmi leaderboard dan unduh Kartu Finisher resmi lomba ' . ($raceName ?? 'Lari') . ' di RuangLari.com.') }}">
    <meta name="twitter:image" content="https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp">

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "SportsEvent",
      "name": {{ json_encode($raceName ?? 'Ruang Lari Race') }},
      "description": {{ json_encode($metaDesc ?? 'Hasil resmi perlombaan lari di RuangLari.com') }},
      "url": {{ json_encode(route('tools.race-master.results', ['slug' => $slug])) }},
      "eventStatus": "https://schema.org/EventCompleted",
      "organizer": {
        "@@type": "Organization",
        "name": "Ruang Lari Race Master",
        "url": "https://ruanglari.com"
      },
      "image": "https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp"
    }
    </script>

    <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"></noscript>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Default Dark Theme */
            --page: #0a0d11;
            --surface: #10151b;
            --surface-2: #151b22;
            --surface-3: #1a212a;
            --line: #252d37;
            --line-soft: #1d242c;
            --text: #f4f6f8;
            --muted: #8f9aa8;
            --muted-2: #687482;
            --accent: #fc5200;
            --accent-hover: #ff651a;
            --success: #43c78f;
            --gold: #f3b94f;
            --silver: #bac2cc;
            --bronze: #c67b4e;
            --topbar-bg: rgba(10, 13, 17, .94);
            --table-head-bg: #0e1318;
            --table-hover-bg: #141a21;
            --modal-bg: #0f141a;
            --modal-border: #2b333d;
            --radius: 10px;
        }

        @media (prefers-color-scheme: light) {
            :root {
                --page: #f6f8fa;
                --surface: #ffffff;
                --surface-2: #f0f3f6;
                --surface-3: #e5ebf1;
                --line: #d2d9e1;
                --line-soft: #e5ebf1;
                --text: #13171d;
                --muted: #596574;
                --muted-2: #7c8a99;
                --accent: #fc5200;
                --accent-hover: #e04900;
                --success: #1b8352;
                --gold: #c9850c;
                --silver: #64707d;
                --bronze: #9c5427;
                --topbar-bg: rgba(246, 248, 250, .94);
                --table-head-bg: #edf2f7;
                --table-hover-bg: #f3f6f9;
                --modal-bg: #ffffff;
                --modal-border: #d2d9e1;
            }
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--page);
            color: var(--text);
            font-family: 'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        button, input { font: inherit; }
        button, a { -webkit-tap-highlight-color: transparent; }
        [v-cloak] { display: none; }
        .mono { font-family: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, monospace; font-variant-numeric: tabular-nums; }
        .shell { width: min(1120px, calc(100% - 40px)); margin: 0 auto; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--topbar-bg);
            border-bottom: 1px solid var(--line-soft);
            backdrop-filter: blur(12px);
        }
        .topbar-inner { min-height: 66px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .brand-lockup { display: flex; align-items: center; min-width: 0; gap: 14px; text-decoration: none; color: inherit; }
        .brand-logo { width: 96px; height: 32px; object-fit: contain; flex: 0 0 auto; }
        .brand-divider { width: 1px; height: 26px; background: var(--line); flex: 0 0 auto; }
        .brand-copy { min-width: 0; }
        .brand-kicker { color: var(--muted); font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; line-height: 1.2; }
        .brand-event { margin-top: 3px; color: var(--text); font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 470px; }
        .topbar-actions { display: flex; gap: 8px; flex: 0 0 auto; }

        .btn {
            border: 1px solid transparent;
            border-radius: 8px;
            min-height: 38px;
            padding: 0 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: var(--text);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: background-color .16s ease, border-color .16s ease, color .16s ease, transform .16s ease;
        }
        .btn:active { transform: translateY(1px); }
        .btn-secondary { background: var(--surface); border-color: var(--line); color: var(--text); }
        .btn-secondary:hover { background: var(--surface-2); border-color: var(--line); }
        .btn-primary { background: var(--accent); color: #ffffff; }
        .btn-primary:hover { background: var(--accent-hover); }
        .btn-quiet { background: transparent; border-color: var(--line); color: var(--text); }
        .btn-quiet:hover { background: var(--surface-2); }
        .btn-icon { width: 38px; padding: 0; }
        .btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }

        .content { padding: 54px 0 72px; }
        .race-head { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: end; gap: 42px; padding-bottom: 32px; border-bottom: 1px solid var(--line); }
        .eyebrow { display: flex; align-items: center; flex-wrap: wrap; gap: 9px; color: var(--muted); font-size: 12px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
        .eyebrow-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--accent); }
        .race-title { margin: 12px 0 9px; max-width: 760px; font-size: clamp(32px, 5vw, 58px); line-height: .98; letter-spacing: -.045em; font-weight: 700; color: var(--text); }
        .race-description { margin: 0; color: var(--muted); font-size: 15px; line-height: 1.6; max-width: 680px; }
        .race-summary { display: flex; align-items: stretch; border: 1px solid var(--line); border-radius: var(--radius); background: var(--surface); overflow: hidden; }
        .summary-item { min-width: 138px; padding: 17px 19px; }
        .summary-item + .summary-item { border-left: 1px solid var(--line); }
        .summary-label { color: var(--muted-2); font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .summary-value { margin-top: 6px; color: var(--text); font-family: 'IBM Plex Mono', monospace; font-size: 19px; font-weight: 600; white-space: nowrap; }

        .result-section { margin-top: 32px; }
        .section-heading { display: flex; justify-content: space-between; align-items: end; gap: 16px; margin-bottom: 14px; }
        .section-title { margin: 0; font-size: 18px; font-weight: 600; letter-spacing: -.015em; color: var(--text); }
        .section-count { margin-top: 4px; color: var(--muted); font-size: 13px; }

        .toolbar { display: grid; grid-template-columns: minmax(240px, 1fr) auto; gap: 10px; margin-bottom: 12px; }
        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted-2); font-size: 12px; pointer-events: none; }
        .search-input {
            width: 100%; height: 44px; padding: 0 14px 0 38px;
            border: 1px solid var(--line); border-radius: 8px;
            outline: none; background: var(--surface); color: var(--text); font-size: 14px;
            transition: border-color .16s ease, box-shadow .16s ease;
        }
        .search-input::placeholder { color: var(--muted-2); }
        .search-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(252, 82, 0, .12); }
        .filter-group { display: inline-flex; border: 1px solid var(--line); border-radius: 8px; padding: 3px; background: var(--surface); }
        .filter-btn { height: 36px; border: 0; border-radius: 6px; padding: 0 13px; background: transparent; color: var(--muted); font-size: 12px; font-weight: 600; cursor: pointer; }
        .filter-btn:hover { color: var(--text); }
        .filter-btn.active { background: var(--surface-3); color: var(--text); box-shadow: inset 0 0 0 1px var(--line); }

        .results-frame { border: 1px solid var(--line); border-radius: var(--radius); overflow: hidden; background: var(--surface); }
        .results-table { width: 100%; border-collapse: collapse; }
        .results-table th { height: 45px; padding: 0 16px; background: var(--table-head-bg); border-bottom: 1px solid var(--line); color: var(--muted-2); font-size: 10px; font-weight: 700; letter-spacing: .075em; text-transform: uppercase; text-align: left; white-space: nowrap; }
        .results-table td { height: 68px; padding: 0 16px; border-bottom: 1px solid var(--line-soft); color: var(--text); font-size: 14px; vertical-align: middle; }
        .results-table tbody tr:last-child td { border-bottom: 0; }
        .results-table tbody tr { transition: background-color .13s ease; }
        .results-table tbody tr:hover { background: var(--table-hover-bg); }
        .results-table .align-right { text-align: right; }
        .results-table .align-center { text-align: center; }
        .rank { width: 42px; color: var(--muted); font-family: 'IBM Plex Mono', monospace; font-size: 13px; font-weight: 600; }
        .rank.p1 { color: var(--gold); font-weight: 700; }
        .rank.p2 { color: var(--silver); font-weight: 700; }
        .rank.p3 { color: var(--bronze); font-weight: 700; }
        .bib { font-family: 'IBM Plex Mono', monospace; color: var(--muted); font-size: 12px; font-weight: 600; }
        .runner-name { color: var(--text); font-weight: 600; }
        .finish-time { color: var(--text); font-family: 'IBM Plex Mono', monospace; font-weight: 600; letter-spacing: -.02em; }
        .pace { color: var(--muted); font-family: 'IBM Plex Mono', monospace; font-size: 12px; }
        .status { display: inline-flex; align-items: center; gap: 7px; color: var(--success); font-size: 11px; font-weight: 600; }
        .status::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--success); }
        .result-action { width: 34px; height: 34px; border: 1px solid var(--line); border-radius: 7px; background: transparent; color: var(--muted); cursor: pointer; transition: background-color .15s ease, border-color .15s ease, color .15s ease; }
        .result-action:hover { background: var(--surface-3); border-color: var(--line); color: var(--accent); }

        .mobile-results { display: none; }
        .mobile-row { padding: 17px 16px; border-bottom: 1px solid var(--line-soft); }
        .mobile-row:last-child { border-bottom: 0; }
        .mobile-main { display: grid; grid-template-columns: 38px minmax(0,1fr) auto; align-items: center; gap: 10px; }
        .mobile-rank { width: 34px; height: 34px; border: 1px solid var(--line); border-radius: 7px; display: grid; place-items: center; color: var(--muted); font-family: 'IBM Plex Mono', monospace; font-size: 12px; font-weight: 600; }
        .mobile-rank.p1 { color: var(--gold); border-color: var(--gold); }
        .mobile-rank.p2 { color: var(--silver); }
        .mobile-rank.p3 { color: var(--bronze); border-color: var(--bronze); }
        .mobile-identity { min-width: 0; }
        .mobile-name { color: var(--text); font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .mobile-sub { margin-top: 3px; color: var(--muted-2); font-size: 11px; }
        .mobile-time { text-align: right; }
        .mobile-time strong { display: block; color: var(--text); font-family: 'IBM Plex Mono', monospace; font-size: 13px; font-weight: 600; }
        .mobile-time span { display: block; margin-top: 3px; color: var(--muted-2); font-family: 'IBM Plex Mono', monospace; font-size: 10px; }
        .mobile-foot { margin: 13px 0 0 48px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .mobile-card-link { border: 0; padding: 0; background: transparent; color: var(--accent); font-size: 12px; font-weight: 600; cursor: pointer; }
        .mobile-card-link:hover { color: var(--accent-hover); }

        .table-state { padding: 52px 20px; text-align: center; color: var(--muted); font-size: 13px; }
        .table-state i { margin-right: 8px; color: var(--accent); }

        .modal-overlay { position: fixed; inset: 0; z-index: 100; display: grid; place-items: center; padding: 20px; background: rgba(0,0,0,.75); }
        .modal-card { width: min(900px, 100%); max-height: calc(100vh - 40px); overflow: auto; background: var(--modal-bg); border: 1px solid var(--modal-border); border-radius: 12px; box-shadow: 0 28px 80px rgba(0,0,0,.4); }
        .modal-head { min-height: 66px; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; gap: 18px; border-bottom: 1px solid var(--line); }
        .modal-title { margin: 0; font-size: 16px; font-weight: 600; color: var(--text); }
        .modal-subtitle { margin-top: 4px; color: var(--muted); font-size: 12px; }
        .modal-close { width: 34px; height: 34px; border: 0; border-radius: 7px; background: transparent; color: var(--muted); cursor: pointer; }
        .modal-close:hover { background: var(--surface-3); color: var(--text); }
        .modal-body { display: grid; grid-template-columns: minmax(280px, 390px) minmax(280px, 1fr); gap: 28px; padding: 24px; }
        .preview-panel { min-width: 0; }
        .preview-frame { width: min(100%, 360px); aspect-ratio: 4 / 5; margin: 0 auto; overflow: hidden; border: 1px solid var(--line); border-radius: 8px; background: #07090c; display: grid; place-items: center; }
        .preview-frame img { width: 100%; height: 100%; object-fit: contain; display: block; }
        .render-state { color: var(--muted); font-size: 12px; text-align: center; }
        .render-state i { display: block; margin-bottom: 10px; color: var(--accent); font-size: 22px; }
        .control-panel { display: flex; flex-direction: column; min-width: 0; }
        .control-label { margin-bottom: 8px; color: var(--text); font-size: 12px; font-weight: 600; }
        .control-help { margin: 8px 0 0; color: var(--muted-2); font-size: 11px; line-height: 1.55; }
        .upload-box { padding: 16px; border: 1px solid var(--line); border-radius: 8px; background: var(--surface); }
        .file-input { width: 100%; color: var(--muted); font-size: 12px; }
        .file-input::file-selector-button { margin-right: 10px; border: 1px solid var(--line); border-radius: 7px; padding: 8px 11px; background: var(--surface-3); color: var(--text); font: inherit; font-size: 12px; font-weight: 600; cursor: pointer; }
        .file-input::file-selector-button:hover { background: var(--surface-2); }
        .control-note { margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--line); color: var(--muted); font-size: 12px; line-height: 1.6; }
        .modal-actions { margin-top: auto; padding-top: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }

        .site-footer { border-top: 1px solid var(--line-soft); }
        .footer-inner { min-height: 76px; display: flex; align-items: center; justify-content: space-between; gap: 20px; color: var(--muted-2); font-size: 11px; }
        .footer-inner a { color: var(--text); text-decoration: none; font-weight: 600; }
        .footer-inner a:hover { color: var(--accent); }

        @media (max-width: 820px) {
            .shell { width: min(100% - 28px, 1120px); }
            .content { padding: 36px 0 56px; }
            .brand-divider, .brand-copy { display: none; }
            .topbar-inner { min-height: 60px; }
            .brand-logo { width: 88px; }
            .race-head { grid-template-columns: 1fr; gap: 24px; align-items: start; }
            .race-title { font-size: clamp(34px, 10vw, 50px); }
            .race-summary { width: 100%; }
            .summary-item { flex: 1; min-width: 0; }
            .toolbar { grid-template-columns: 1fr; }
            .filter-group { width: 100%; }
            .filter-btn { flex: 1; }
            .desktop-results { display: none; }
            .mobile-results { display: block; }
            .modal-body { grid-template-columns: 1fr; }
            .control-panel { min-height: auto; }
            .modal-actions { margin-top: 22px; }
            .footer-inner { padding: 20px 0; min-height: 0; flex-direction: column; align-items: flex-start; gap: 6px; }
        }

        @media (max-width: 520px) {
            .shell { width: min(100% - 24px, 1120px); }
            .topbar-actions .btn span { display: none; }
            .topbar-actions .btn { width: 38px; padding: 0; }
            .race-summary { display: grid; grid-template-columns: 1fr 1fr; }
            .summary-item { padding: 14px 15px; }
            .summary-value { font-size: 16px; }
            .section-heading { align-items: start; }
            .modal-overlay { padding: 10px; }
            .modal-card { max-height: calc(100vh - 20px); border-radius: 10px; }
            .modal-head { padding: 0 15px; }
            .modal-body { padding: 15px; gap: 18px; }
            .modal-actions { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div id="app" v-cloak class="min-h-screen">

    <header class="topbar">
        <div class="shell topbar-inner">
            <a href="https://ruanglari.com" class="brand-lockup" aria-label="Ruang Lari">
                <img src="https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp" alt="Ruang Lari" class="brand-logo" width="108" height="36">
                <span class="brand-divider" aria-hidden="true"></span>
                <span class="brand-copy">
                    <span class="brand-kicker">Race results </span>
                    <span class="brand-event">{{ $raceName ?? 'Ruang Lari Race Championship' }}</span>
                </span>
            </a>

            <div class="topbar-actions">
                <button type="button" @click="copyLink" class="btn btn-secondary" aria-label="Salin tautan hasil lomba">
                    <i :class="copied ? 'fa-solid fa-check' : 'fa-solid fa-link'"></i>
                    <span>@{{ copied ? 'Tersalin' : 'Salin tautan' }}</span>
                </button>
                <a href="{{ route('tools.race-master') }}" class="btn btn-primary">
                    <span>Race Master</span>
                </a>
            </div>
        </div>
    </header>

    <main class="shell content">
        <section class="race-head" aria-labelledby="race-title">
            <div>
                <div class="eyebrow">
                    <span class="eyebrow-dot"></span>
                    <span>@{{ session.category || defaultCategory }}</span>
                    <span>Official result</span>
                </div>
                <h1 id="race-title" class="race-title">{{ $raceName ?? 'Ruang Lari Race' }}</h1>
                <p class="race-description">Catatan waktu dan peringkat peserta berdasarkan hasil timing resmi Ruang Lari Race Master.</p>
            </div>

            <div class="race-summary" aria-label="Ringkasan lomba">
                <div class="summary-item">
                    <div class="summary-label">Finisher</div>
                    <div class="summary-value">@{{ results.length }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Jarak</div>
                    <div class="summary-value">@{{ session.distance_km ? (session.distance_km + ' km') : (defaultDistanceKm ? (defaultDistanceKm + ' km') : 'Road race') }}</div>
                </div>
            </div>
        </section>

        <section class="result-section" aria-labelledby="leaderboard-title">
            <div class="section-heading">
                <div>
                    <h2 id="leaderboard-title" class="section-title">Leaderboard</h2>
                    <div class="section-count">@{{ filteredResults.length }} dari @{{ results.length }} peserta</div>
                </div>
            </div>

            <div class="toolbar">
                <label class="search-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input v-model="query" type="search" class="search-input" placeholder="Cari BIB atau nama peserta" aria-label="Cari BIB atau nama peserta">
                </label>

                <div class="filter-group" aria-label="Filter leaderboard">
                    <button type="button" @click="statusFilter = 'all'" class="filter-btn" :class="{ active: statusFilter === 'all' }">Semua</button>
                    <button type="button" @click="statusFilter = 'top3'" class="filter-btn" :class="{ active: statusFilter === 'top3' }">Top 3</button>
                </div>
            </div>

            <div class="results-frame">
                <div class="desktop-results">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th style="width:72px">Pos.</th>
                                <th style="width:100px">BIB</th>
                                <th>Peserta</th>
                                <th class="align-right">Finish time</th>
                                <th class="align-right">Pace</th>
                                <th class="align-center">Status</th>
                                <th class="align-center" style="width:76px">Kartu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="r in filteredResults" :key="r.participant_id">
                                <td><span class="rank" :class="{ p1: r.rank === 1, p2: r.rank === 2, p3: r.rank === 3 }">@{{ r.rank ? String(r.rank).padStart(2, '0') : '—' }}</span></td>
                                <td><span class="bib">#@{{ r.bib }}</span></td>
                                <td><span class="runner-name">@{{ r.name }}</span></td>
                                <td class="align-right"><span class="finish-time">@{{ formatTimeHms(r.total_time_ms) }}</span></td>
                                <td class="align-right"><span class="pace">@{{ paceFor(r) }}</span></td>
                                <td class="align-center"><span class="status">Finish</span></td>
                                <td class="align-center">
                                    <button type="button" @click="openFinisherCard(r)" class="result-action" title="Buka kartu finisher" :aria-label="'Buka kartu finisher ' + r.name">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!loading && filteredResults.length === 0">
                                <td colspan="7"><div class="table-state">Tidak ada peserta yang cocok dengan pencarian.</div></td>
                            </tr>
                            <tr v-if="loading">
                                <td colspan="7"><div class="table-state"><i class="fa-solid fa-circle-notch fa-spin"></i>Memuat hasil lomba</div></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mobile-results">
                    <article v-for="r in filteredResults" :key="r.participant_id" class="mobile-row">
                        <div class="mobile-main">
                            <div class="mobile-rank" :class="{ p1: r.rank === 1, p2: r.rank === 2, p3: r.rank === 3 }">@{{ r.rank || '—' }}</div>
                            <div class="mobile-identity">
                                <div class="mobile-name">@{{ r.name }}</div>
                                <div class="mobile-sub">BIB #@{{ r.bib }} · @{{ r.laps || 1 }} lap</div>
                            </div>
                            <div class="mobile-time">
                                <strong>@{{ formatTimeHms(r.total_time_ms) }}</strong>
                                <span>@{{ paceFor(r) }}</span>
                            </div>
                        </div>
                        <div class="mobile-foot">
                            <span class="status">Finish</span>
                            <button type="button" @click="openFinisherCard(r)" class="mobile-card-link">Kartu finisher <i class="fa-solid fa-arrow-right-long"></i></button>
                        </div>
                    </article>

                    <div v-if="!loading && filteredResults.length === 0" class="table-state">Tidak ada peserta yang cocok dengan pencarian.</div>
                    <div v-if="loading" class="table-state"><i class="fa-solid fa-circle-notch fa-spin"></i>Memuat hasil lomba</div>
                </div>
            </div>
        </section>
    </main>

    <div v-if="cardModalOpen" class="modal-overlay" @click.self="closeCardModal">
        <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="finisher-modal-title">
            <header class="modal-head">
                <div>
                    <h3 id="finisher-modal-title" class="modal-title">Kartu finisher</h3>
                    <div class="modal-subtitle">Format portrait 1080 × 1350 px</div>
                </div>
                <button type="button" @click="closeCardModal" class="modal-close" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
            </header>

            <div class="modal-body">
                <div class="preview-panel">
                    <div class="preview-frame">
                        <img v-if="previewUrl" :src="previewUrl" alt="Pratinjau kartu finisher">
                        <div v-else-if="generating" class="render-state"><i class="fa-solid fa-circle-notch fa-spin"></i>Menyiapkan kartu</div>
                    </div>
                </div>

                <div class="control-panel">
                    <div>
                        <div class="control-label">Gunakan foto lari</div>
                        <div class="upload-box">
                            <input @change="onBgChange" type="file" accept="image/png,image/jpeg,image/webp" class="file-input">
                            <p class="control-help">Opsional. Foto akan memenuhi bidang kartu dan diberi lapisan gelap agar data hasil tetap terbaca.</p>
                        </div>

                        <div class="control-note">
                            Kartu menampilkan nama peserta, BIB, waktu finish, peringkat, pace, jarak, dan identitas hasil resmi. Tanpa foto, kartu memakai layout editorial Ruang Lari.
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" @click="downloadCard" :disabled="!previewUrl || generating" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-down"></i>
                            <span>Unduh PNG</span>
                        </button>
                        <button type="button" @click="shareCard" :disabled="!cardFile || generating" class="btn btn-primary">
                            <i class="fa-solid fa-share-nodes"></i>
                            <span>Bagikan</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <footer class="site-footer">
        <div class="shell footer-inner">
            <div>© {{ date('Y') }} <a href="https://ruanglari.com">RuangLari.com</a></div>
            <div>Official timing result · Race Master</div>
        </div>
    </footer>
</div>

<script>
const { createApp, ref, computed, nextTick } = Vue;

createApp({
    setup() {
        const slug = @json($slug);
        const initialRaceName = @json($raceName ?? 'Ruang Lari Race');
        const defaultCategory = @json($category ?? 'OFFICIAL EVENT');
        const defaultDistanceKm = @json($distanceKm ?? '');
        const apiBase = @json(url('api/tools/race-master'));

        const loading = ref(true);
        const query = ref('');
        const statusFilter = ref('all');
        const copied = ref(false);
        const session = ref({});
        const race = ref({});
        const results = ref([]);

        // Card Modal State
        const cardModalOpen = ref(false);
        const activeParticipant = ref(null);
        const previewUrl = ref('');
        const cardFile = ref(null);
        const bgImage = ref(null);
        const generating = ref(false);

        // Fetch Official Results Data
        const fetchResults = async () => {
            loading.value = true;
            try {
                const res = await fetch(`${apiBase}/public/${encodeURIComponent(slug)}/results`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    session.value = data.session || {};
                    race.value = data.race || {};
                    results.value = Array.isArray(data.results) ? data.results : [];
                }
            } catch (e) {
                console.error('Error loading race results:', e);
            } finally {
                loading.value = false;
            }
        };

        fetchResults();

        const filteredResults = computed(() => {
            let list = results.value;
            if (statusFilter.value === 'top3') {
                list = list.filter(r => r.rank && r.rank <= 3);
            }
            const q = query.value.trim().toLowerCase();
            if (q) {
                list = list.filter(r => String(r.bib || '').toLowerCase().includes(q) || String(r.name || '').toLowerCase().includes(q));
            }
            return list;
        });

        // Time Formatter without Milliseconds (hh:mm:ss)
        const formatTimeHms = (ms) => {
            if (!ms || ms < 0 || isNaN(ms)) return '00:00:00';
            const totalSeconds = Math.floor(ms / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        };

        const paceFor = (r) => {
            const dist = parseFloat(session.value.distance_km || defaultDistanceKm || 0);
            if (!dist || !r.total_time_ms) return '-';
            const sec = Math.max(1, Math.floor(r.total_time_ms / 1000));
            const paceSec = Math.round(sec / dist);
            const m = Math.floor(paceSec / 60);
            const s = paceSec % 60;
            return `${m}'${String(s).padStart(2, '0')}" /km`;
        };

        const copyLink = async () => {
            const url = window.location.href;
            try {
                if (!navigator.clipboard || !navigator.clipboard.writeText) throw new Error('Clipboard API unavailable');
                await navigator.clipboard.writeText(url);
                copied.value = true;
                window.setTimeout(() => { copied.value = false; }, 1800);
            } catch (e) {
                prompt('Salin tautan hasil lomba:', url);
            }
        };

        // Render 4:5 editorial finisher card
        const renderCanvasCard = async () => {
            const p = activeParticipant.value;
            if (!p) return;

            generating.value = true;

            try {
                const canvas = document.createElement('canvas');
                canvas.width = 1080;
                canvas.height = 1350;
                const ctx = canvas.getContext('2d');

                const coverImage = (img) => {
                    const scale = Math.max(canvas.width / img.width, canvas.height / img.height);
                    const w = img.width * scale;
                    const h = img.height * scale;
                    const x = (canvas.width - w) / 2;
                    const y = (canvas.height - h) / 2;
                    ctx.drawImage(img, x, y, w, h);
                };

                const fitText = (text, maxWidth, startSize, minSize, weight = 700, family = 'DM Sans') => {
                    let size = startSize;
                    do {
                        ctx.font = `${weight} ${size}px "${family}", sans-serif`;
                        if (ctx.measureText(text).width <= maxWidth) break;
                        size -= 2;
                    } while (size > minSize);
                    return size;
                };

                // Background: photo or restrained editorial base
                if (bgImage.value) {
                    coverImage(bgImage.value);
                    ctx.fillStyle = 'rgba(5, 7, 9, 0.50)';
                    ctx.fillRect(0, 0, 1080, 1350);
                    const shade = ctx.createLinearGradient(0, 420, 0, 1350);
                    shade.addColorStop(0, 'rgba(5,7,9,0.05)');
                    shade.addColorStop(1, 'rgba(5,7,9,0.96)');
                    ctx.fillStyle = shade;
                    ctx.fillRect(0, 380, 1080, 970);
                } else {
                    ctx.fillStyle = '#0a0d11';
                    ctx.fillRect(0, 0, 1080, 1350);

                    ctx.fillStyle = '#fc5200';
                    ctx.fillRect(0, 0, 18, 1350);

                    ctx.strokeStyle = 'rgba(255,255,255,0.055)';
                    ctx.lineWidth = 1;
                    for (let x = 72; x < 1080; x += 72) {
                        ctx.beginPath();
                        ctx.moveTo(x, 0);
                        ctx.lineTo(x, 1350);
                        ctx.stroke();
                    }
                    for (let y = 72; y < 1350; y += 72) {
                        ctx.beginPath();
                        ctx.moveTo(0, y);
                        ctx.lineTo(1080, y);
                        ctx.stroke();
                    }
                }

                // Header logo
                try {
                    const logoImg = new Image();
                    logoImg.crossOrigin = 'anonymous';
                    await new Promise((resolve) => {
                        logoImg.onload = resolve;
                        logoImg.onerror = resolve;
                        logoImg.src = 'https://ruanglari.com/storage/blog/media/23deca03-e89f-4c14-a0d1-7f4e4b2059a7.webp';
                    });
                    if (logoImg.width > 0) {
                        const logoH = 58;
                        const logoW = (logoImg.width / logoImg.height) * logoH;
                        ctx.drawImage(logoImg, 72, 66, logoW, logoH);
                    }
                } catch (err) {}

                ctx.textAlign = 'right';
                ctx.fillStyle = '#c7ced7';
                ctx.font = '600 22px "DM Sans", sans-serif';
                ctx.fillText('OFFICIAL RESULT', 1008, 91);
                ctx.fillStyle = '#8e99a6';
                ctx.font = '500 17px "DM Sans", sans-serif';
                ctx.fillText(String(session.value.category || defaultCategory || 'RACE').toUpperCase(), 1008, 120);

                // Event label
                ctx.textAlign = 'left';
                ctx.fillStyle = '#9aa4af';
                ctx.font = '600 18px "DM Sans", sans-serif';
                ctx.fillText('RACE', 72, 210);

                const displayRaceTitle = String(race.value?.name || initialRaceName || '').toUpperCase();
                const titleSize = fitText(displayRaceTitle, 936, 52, 34, 700);
                ctx.fillStyle = '#ffffff';
                ctx.font = `700 ${titleSize}px "DM Sans", sans-serif`;
                ctx.fillText(displayRaceTitle, 72, 270);

                ctx.fillStyle = '#fc5200';
                ctx.fillRect(72, 305, 92, 6);

                // Participant identity
                ctx.fillStyle = '#9aa4af';
                ctx.font = '600 19px "DM Sans", sans-serif';
                ctx.fillText(`BIB #${p.bib}`, 72, 398);

                const runnerName = String(p.name || 'FINISHER').toUpperCase();
                const nameSize = fitText(runnerName, 930, 66, 38, 700);
                ctx.fillStyle = '#ffffff';
                ctx.font = `700 ${nameSize}px "DM Sans", sans-serif`;
                ctx.fillText(runnerName, 72, 475);

                // Hero time
                ctx.fillStyle = '#9aa4af';
                ctx.font = '600 18px "DM Sans", sans-serif';
                ctx.fillText('FINISH TIME', 72, 575);

                const timeHms = formatTimeHms(p.total_time_ms);
                ctx.fillStyle = '#ffffff';
                ctx.font = '600 112px "IBM Plex Mono", monospace';
                ctx.fillText(timeHms, 66, 700);

                // Divider
                ctx.fillStyle = 'rgba(255,255,255,0.20)';
                ctx.fillRect(72, 760, 936, 1);

                // Metrics, one clean row with separators
                const rankStr = p.rank ? `#${p.rank}` : '—';
                const paceStr = paceFor(p);
                const lapsCount = p.laps || 1;
                const distKm = session.value.distance_km || defaultDistanceKm || 0;
                const distStr = distKm ? `${distKm} KM` : `${lapsCount} LAPS`;
                const metrics = [
                    ['POSITION', rankStr],
                    ['AVG PACE', paceStr],
                    ['DISTANCE', distStr],
                ];

                const metricY = 825;
                const metricW = 312;
                metrics.forEach((m, i) => {
                    const x = 72 + i * metricW;
                    if (i > 0) {
                        ctx.fillStyle = 'rgba(255,255,255,0.18)';
                        ctx.fillRect(x, metricY, 1, 116);
                    }
                    ctx.fillStyle = '#8e99a6';
                    ctx.font = '600 16px "DM Sans", sans-serif';
                    ctx.fillText(m[0], x + (i > 0 ? 28 : 0), metricY + 22);
                    ctx.fillStyle = i === 0 ? '#fc5200' : '#ffffff';
                    const valSize = fitText(String(m[1]), metricW - 42, 37, 25, 600, i === 0 ? 'DM Sans' : 'IBM Plex Mono');
                    ctx.font = `600 ${valSize}px "${i === 0 ? 'DM Sans' : 'IBM Plex Mono'}", ${i === 0 ? 'sans-serif' : 'monospace'}`;
                    ctx.fillText(String(m[1]), x + (i > 0 ? 28 : 0), metricY + 77);
                });

                // Result provenance
                ctx.fillStyle = 'rgba(255,255,255,0.20)';
                ctx.fillRect(72, 1000, 936, 1);

                ctx.fillStyle = '#ffffff';
                ctx.font = '600 22px "DM Sans", sans-serif';
                ctx.fillText('Verified race result', 72, 1060);

                ctx.fillStyle = '#8e99a6';
                ctx.font = '500 17px "DM Sans", sans-serif';
                ctx.fillText('Ruang Lari Race Master · Digital timing result', 72, 1096);
                ctx.fillText(`Session ${slug}`, 72, 1130);

                const dateText = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                ctx.textAlign = 'right';
                ctx.fillStyle = '#c9d0d8';
                ctx.font = '600 17px "IBM Plex Mono", monospace';
                ctx.fillText(dateText.toUpperCase(), 1008, 1096);
                ctx.fillStyle = '#fc5200';
                ctx.font = '600 18px "DM Sans", sans-serif';
                ctx.fillText('RUANGLARI.COM', 1008, 1130);

                // Bottom statement
                ctx.textAlign = 'left';
                ctx.fillStyle = '#ffffff';
                ctx.font = '600 27px "DM Sans", sans-serif';
                ctx.fillText('FINISHER', 72, 1253);
                ctx.fillStyle = '#8e99a6';
                ctx.font = '500 17px "DM Sans", sans-serif';
                ctx.fillText('Keep this card as your official race result.', 72, 1287);

                canvas.toBlob((blob) => {
                    if (!blob) return;
                    if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
                        try { URL.revokeObjectURL(previewUrl.value); } catch (_) {}
                    }
                    const url = URL.createObjectURL(blob);
                    previewUrl.value = url;
                    cardFile.value = new File([blob], `finisher-card-BIB-${p.bib}.png`, { type: 'image/png' });
                }, 'image/png');
            } catch (e) {
                console.error('Render card error:', e);
            } finally {
                generating.value = false;
            }
        };

        const openFinisherCard = (participant) => {
            activeParticipant.value = participant;
            bgImage.value = null;
            if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
                try { URL.revokeObjectURL(previewUrl.value); } catch (_) {}
            }
            previewUrl.value = '';
            cardFile.value = null;
            cardModalOpen.value = true;
            nextTick(renderCanvasCard);
        };

        const closeCardModal = () => {
            cardModalOpen.value = false;
            if (previewUrl.value && previewUrl.value.startsWith('blob:')) {
                try { URL.revokeObjectURL(previewUrl.value); } catch (_) {}
            }
            previewUrl.value = '';
            bgImage.value = null;
        };

        const onBgChange = (e) => {
            const file = e?.target?.files?.[0];
            if (!file) {
                bgImage.value = null;
                renderCanvasCard();
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    bgImage.value = img;
                    renderCanvasCard();
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        };

        const downloadCard = () => {
            if (!previewUrl.value) return;
            const p = activeParticipant.value;
            const a = document.createElement('a');
            a.href = previewUrl.value;
            a.download = `finisher-card-BIB-${p?.bib || 'runner'}.png`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };

        const shareCard = async () => {
            if (!cardFile.value) return;
            if (!navigator.share || !navigator.canShare || !navigator.canShare({ files: [cardFile.value] })) {
                downloadCard();
                return;
            }
            try {
                await navigator.share({
                    title: `${race.value?.name || initialRaceName} - Kartu Finisher Resmi`,
                    text: `Hasil lari resmi ${activeParticipant.value?.name || ''} (BIB #${activeParticipant.value?.bib || ''})`,
                    files: [cardFile.value],
                });
            } catch (e) {}
        };

        return {
            slug, session, race, results, filteredResults, loading, query, statusFilter, copied,
            defaultCategory, defaultDistanceKm,
            formatTimeHms, paceFor, copyLink,
            cardModalOpen, activeParticipant, previewUrl, cardFile, generating,
            openFinisherCard, closeCardModal, onBgChange, downloadCard, shareCard
        };
    }
}).mount('#app');
</script>
</body>
</html>
