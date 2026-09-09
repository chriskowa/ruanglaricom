@php
    $pageSuffix = request()->has('page') && request('page') > 1 ? ' - Halaman ' . request('page') : '';
    $title = 'Jadwal Lari Indonesia 2026: 5K, 10K & Marathon | Ruang Lari' . $pageSuffix;
    $metaDescription = 'Temukan jadwal lari Indonesia 2026 terbaru: fun run, 5K, 10K, half marathon, marathon, trail run, dan virtual run. Cek tanggal, kota, kategori jarak, serta link pendaftaran resmi di Ruang Lari.';

    $canonicalUrl = request()->url();
    if (request()->has('page') && request('page') > 1) {
        $canonicalUrl .= '?page=' . request('page');
    }

    $heroBgPath = public_path('images/hero/marathon-hero-cinematic.jpg');
    $sourceArtifactImg = 'C:/Users/LENOVO/.gemini/antigravity-ide/brain/ad786e94-2a56-4d78-962d-8aea9a7effaf/marathon_hero_bg_1788863966283.jpg';
    if (!file_exists($heroBgPath) && file_exists($sourceArtifactImg)) {
        @copy($sourceArtifactImg, $heroBgPath);
    }
    $heroBgUrl = file_exists($heroBgPath) 
        ? asset('images/hero/marathon-hero-cinematic.jpg') 
        : asset('images/hero/runner-hero.jpg');
@endphp

@extends('layouts.pacerhub')

@section('title', $title)
@section('meta_title', $title)
@section('meta_description', $metaDescription)
@section('meta_keywords', 'jadwal lari 2026, submit event lari gratis, kalender event lari indonesia, event lari 5k 10k, marathon indonesia, publikasi event lari gratis, registration page event lari, ruang lari eo')
@section('canonical_url', $canonicalUrl)
@section('og_image', 'https://ruanglari.com/storage/blog/media/012cce42-9e25-41b8-9e03-dc3a177fd595.webp')

@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    #events-page {
        --ep-bg: #08111F;
        --ep-surface: #0E1626;
        --ep-input: #0B111C;
        --ep-line: #1F293D;
        --ep-line-strong: #334155;
        --ep-accent: #B7FF00;
        --ep-accent-hover: #a6e600;
        min-height: 100vh;
        background: var(--ep-bg);
        color: #f8fafc;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        font-variant-numeric: tabular-nums;
    }

    #events-page * { box-sizing: border-box; }
    #events-page .ep-shell { max-width: 1280px; margin: 0 auto; padding: 0 1rem; }

    /* HERO V2 HIGH CONTRAST & PEWARNAAN TEGAS */
    #events-page .hero-v2-container {
        position: relative;
        width: 100%;
        overflow: hidden;
        background-color: #08111F;
        border-bottom: 1px solid rgba(51, 65, 85, 0.5);
        min-height: 640px;
        display: flex;
        align-items: center;
    }
    @media (min-width: 1024px) {
        #events-page .hero-v2-container {
            height: 720px;
            min-height: 720px;
        }
    }

    #events-page .hero-cinematic-bg {
        position: absolute;
        inset: 0;
        z-index: 0;
        pointer-events: none;
        overflow: hidden;
        background-color: #08111F;
    }
    #events-page .hero-cinematic-bg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        filter: brightness(0.95) contrast(1.05);
    }
    #events-page .hero-cinematic-overlay {
        position: absolute;
        inset: 0;
        background: rgba(8, 17, 31, 0.75) !important; /* 50% overlay transparan */
    }

    #events-page .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.75rem;
        border-radius: 4px;
        background: rgba(183, 255, 0, 0.12) !important;
        border: 1px solid rgba(183, 255, 0, 0.4) !important;
        color: #B7FF00 !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    #events-page .hero-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: #B7FF00;
        box-shadow: 0 0 8px #B7FF00;
    }

    #events-page .hero-headline {
        color: #ffffff !important;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: -0.025em;
        line-height: 1.05;
        font-size: 2rem;
    }
    @media (min-width: 640px) {
        #events-page .hero-headline { font-size: 3rem; }
    }
    @media (min-width: 1024px) {
        #events-page .hero-headline { font-size: 64px; }
    }

    #events-page .text-lime-highlight {
        color: #B7FF00 !important;
        text-shadow: 0 0 25px rgba(183, 255, 0, 0.35);
    }

    #events-page .btn-hero-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        padding: 0.85rem 1.65rem;
        border-radius: 6px;
        background-color: #B7FF00 !important;
        color: #08111F !important;
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid #B7FF00 !important;
        box-shadow: 0 4px 20px rgba(183, 255, 0, 0.35);
        transition: all 0.15s ease;
    }
    #events-page .btn-hero-primary:hover {
        background-color: #c9ff33 !important;
        border-color: #c9ff33 !important;
        color: #08111F !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 28px rgba(183, 255, 0, 0.5);
    }

    #events-page .btn-hero-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        padding: 0.85rem 1.65rem;
        border-radius: 6px;
        background-color: #0E1626 !important;
        color: #ffffff !important;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid #2B384E !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
        transition: all 0.15s ease;
    }
    #events-page .btn-hero-secondary:hover {
        background-color: #162238 !important;
        border-color: #64748B !important;
        color: #ffffff !important;
        transform: translateY(-1px);
    }

    #events-page .floating-race-card {
        position: relative;
        background: rgba(13, 21, 39, 0.94);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(71, 85, 105, 0.6);
        border-radius: 8px;
        padding: 1.25rem;
        box-shadow: 0 25px 60px -12px rgba(0, 0, 0, 0.9);
        overflow: hidden;
    }

    #events-page .btn-card-action {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 6px;
        background-color: #B7FF00 !important;
        color: #08111F !important;
        font-size: 11px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid #B7FF00 !important;
        box-shadow: 0 4px 16px rgba(183, 255, 0, 0.25);
        transition: all 0.15s ease;
    }
    #events-page .btn-card-action:hover {
        background-color: #c9ff33 !important;
        border-color: #c9ff33 !important;
        color: #08111F !important;
    }

    /* OLD HERO FALLBACK */
    #events-page .ep-hero {
        padding: 1.5rem 0 2rem;
        border-bottom: 1px solid var(--ep-line);
    }
    #events-page .ep-hero-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 2.5rem;
        align-items: start;
    }
    #events-page .ep-title {
        margin: 0;
        color: #ffffff;
        font-size: 1.75rem;
        font-weight: 700;
        letter-spacing: -0.025em;
        line-height: 1.2;
    }
    #events-page .ep-lead {
        color: #cbd5e1;
        font-size: 14px;
        line-height: 1.6;
        margin-top: 0.75rem;
    }
    #events-page .ep-subcopy {
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.6;
        margin-top: 0.5rem;
    }
    #events-page .ep-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin-top: 1.25rem;
    }
    #events-page .ep-btn {
        min-height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0 1.15rem;
        border: 1px solid var(--ep-line);
        border-radius: 6px;
        color: #f8fafc;
        background: #12161F;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        transition: all 0.15s ease;
        text-decoration: none;
        cursor: pointer;
    }
    #events-page .ep-btn:hover {
        background: #1a2333;
        border-color: var(--ep-line-strong);
        color: #ffffff;
    }
    #events-page .ep-btn--primary {
        background: var(--ep-accent);
        border-color: var(--ep-accent);
        color: #080A0D !important;
        font-weight: 800 !important;
    }
    #events-page .ep-btn--primary:hover {
        background: var(--ep-accent-hover);
        border-color: var(--ep-accent-hover);
    }

    /* FEATURED SLIDER */
    #events-page .ep-featured {
        position: relative;
        overflow: hidden;
        min-height: 320px;
        border: 1px solid var(--ep-line);
        border-radius: 8px;
        background: #12161F;
    }
    #events-page .ep-featured-slide {
        position: relative;
        display: block;
        width: 100%;
        min-height: 320px;
        flex: 0 0 100%;
        overflow: hidden;
    }
    #events-page .ep-featured-slide img {
        width: 100%;
        height: 100%;
        min-height: 320px;
        object-fit: cover;
        transition: transform 0.4s ease;
    }
    #events-page .ep-featured-slide:hover img {
        transform: scale(1.02);
    }
    #events-page .ep-featured-slide:after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(8,10,13,0.95) 0%, rgba(8,10,13,0.4) 50%, rgba(8,10,13,0.05) 80%);
    }
    #events-page .ep-featured-copy {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 2;
        padding: 1.25rem;
    }
    #events-page .ep-featured-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        align-items: center;
        color: #94a3b8;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #events-page .ep-featured-name {
        margin-top: 0.4rem;
        color: #ffffff;
        font-size: 1.25rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }
    #events-page .ep-slider-nav {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 3;
        display: flex;
        gap: 0.35rem;
    }
    #events-page .ep-slider-nav button {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--ep-line-strong);
        border-radius: 6px;
        background: rgba(8,10,13,0.85);
        color: #ffffff;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    #events-page .ep-slider-nav button:hover {
        background: #1a2333;
    }

    /* OPERATIONS STRIP */
    #events-page .ep-ops {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        padding: 1.25rem;
        border: 1px solid var(--ep-line);
        border-radius: 8px;
        background: #12161F;
        margin-top: 1.5rem;
    }
    @media (min-width: 640px) {
        #events-page .ep-ops {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
    #events-page .ep-ops-label {
        color: var(--ep-accent);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }
    #events-page .ep-ops h2 {
        margin: 0.25rem 0 0;
        font-size: 15px;
        font-weight: 700;
        color: #ffffff;
    }
    #events-page .ep-ops p {
        margin: 0.25rem 0 0;
        color: #cbd5e1;
        font-size: 12px;
        line-height: 1.5;
    }

    /* MAP */
    #events-page .ep-section { padding-top: 2rem; }
    #events-page .ep-section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.85rem;
    }
    #events-page .ep-heading {
        margin: 0;
        color: #ffffff;
        font-size: 1.125rem;
        font-weight: 700;
        letter-spacing: -0.015em;
    }
    #events-page .ep-section-copy {
        margin-top: 0.25rem;
        color: #94a3b8;
        font-size: 12px;
    }
    #events-page .ep-map-shell {
        border: 1px solid var(--ep-line);
        border-radius: 8px;
        background: #12161F;
        overflow: hidden;
    }
    #events-page .ep-map-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 0.65rem 0.85rem;
        border-bottom: 1px solid var(--ep-line);
        background: #0B0F17;
    }
    #events-page .ep-map-tools { display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; }
    #events-page .ep-tool-btn {
        min-height: 32px;
        padding: 0 0.65rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: 1px solid var(--ep-line);
        border-radius: 6px;
        color: #cbd5e1;
        background: #12161F;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    #events-page .ep-tool-btn:hover { color: #ffffff; background: #1a2333; border-color: var(--ep-line-strong); }
    #events-page #events-explorer-map { height: 400px; background: #080A0D; }
    #events-page .ep-map-filter {
        display: flex;
        gap: 0.35rem;
        overflow-x: auto;
        padding: 0.6rem 0.85rem;
        border-top: 1px solid var(--ep-line);
        background: #0B0F17;
        scrollbar-width: none;
    }
    #events-page .ep-map-filter::-webkit-scrollbar { display: none; }

    /* FILTERS */
    #events-page .ep-filter-shell {
        margin-top: 1.5rem;
        border: 1px solid var(--ep-line);
        border-radius: 8px;
        padding: 1.25rem;
        background: #12161F;
    }
    #events-page #filter-form {
        display: grid;
        grid-template-columns: 1.25fr repeat(4, 1fr);
        gap: 0.75rem;
    }
    #events-page label {
        display: block;
        margin-bottom: 0.35rem;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    #events-page input:not([type=checkbox]):not([type=file]),
    #events-page select,
    #events-page textarea {
        width: 100%;
        border: 1px solid var(--ep-line);
        border-radius: 6px;
        background: var(--ep-input);
        color: #f8fafc;
        outline: none;
        box-shadow: none;
        font-family: inherit;
        transition: border-color 0.15s ease;
    }
    #events-page input:not([type=checkbox]):not([type=file]),
    #events-page select {
        height: 38px;
        padding: 0 0.75rem;
        font-size: 12px;
    }
    #events-page textarea { padding: 0.6rem 0.75rem; font-size: 12px; }
    #events-page input::placeholder { color: #64748b; }
    #events-page input:focus,
    #events-page select:focus,
    #events-page textarea:focus {
        border-color: var(--ep-accent);
        box-shadow: 0 0 0 1px var(--ep-accent);
    }
    #events-page .ep-quick {
        display: flex;
        gap: 0.4rem;
        overflow-x: auto;
        padding-top: 0.85rem;
        margin-top: 0.85rem;
        border-top: 1px solid var(--ep-line);
        scrollbar-width: none;
    }
    #events-page .ep-quick::-webkit-scrollbar { display: none; }
    #events-page .quick-filter-btn,
    #events-page .btn-event-map-pill {
        flex: 0 0 auto;
        min-height: 30px;
        padding: 0 0.65rem;
        border: 1px solid var(--ep-line);
        border-radius: 6px;
        background: var(--ep-input);
        color: #cbd5e1;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    #events-page .quick-filter-btn:hover,
    #events-page .btn-event-map-pill:hover {
        border-color: var(--ep-line-strong);
        color: #ffffff;
    }
    #events-page .quick-filter-btn.is-active,
    #events-page .btn-event-map-pill.is-active {
        background: var(--ep-accent);
        border-color: var(--ep-accent);
        color: #080A0D;
        font-weight: 700;
    }

    /* EVENT LIST & VIEW SWITCHER */
    #events-page .ep-list-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        padding-top: 2rem;
        margin-bottom: 1rem;
    }
    .ep-view-switcher {
        display: inline-flex;
        align-items: center;
        background: #0B0F17;
        border: 1px solid var(--ep-line);
        border-radius: 6px;
        padding: 2px;
        gap: 2px;
        flex-shrink: 0;
    }
    .ep-view-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        color: #94A3B8;
        background: transparent;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }
    .ep-view-btn:hover {
        color: #FFFFFF;
        background: rgba(255, 255, 255, 0.05);
    }
    .ep-view-btn.is-active {
        background: var(--ep-accent);
        color: #080A0D;
        border-color: var(--ep-accent);
        font-weight: 800;
    }
    .ep-view-btn svg {
        flex-shrink: 0;
    }

    /* DEFAULT LIST VIEW */
    #events-page #events-container {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* CARD VIEW (GRID) */
    #events-page #events-container.view-card {
        display: grid;
        grid-template-columns: repeat(1, minmax(0, 1fr));
        gap: 1.25rem;
    }
    @media (min-width: 640px) {
        #events-page #events-container.view-card {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1024px) {
        #events-page #events-container.view-card {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    #events-page #events-container.view-card .event-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    #events-page #events-container.view-card .event-card-inner {
        flex-direction: column !important;
        align-items: stretch !important;
        height: 100%;
        gap: 1rem !important;
    }
    #events-page #events-container.view-card .event-card-thumb {
        width: 100% !important;
        height: 190px !important;
    }
    #events-page #events-container.view-card .event-card-body {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }
    #events-page #events-container.view-card .event-card-body h3 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.8rem;
    }
    #events-page #events-container.view-card .event-card-actions {
        width: 100% !important;
        margin-top: auto !important;
        padding-top: 1rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        flex-direction: row !important;
        align-items: center !important;
    }
    #events-page #events-container.view-card .event-card-actions a {
        flex: 1 1 0%;
        text-align: center;
    }
    #events-page #events-container.view-card > div:only-child {
        grid-column: 1 / -1;
    }

    /* SEO DIRECTORY */
    #events-page .ep-seo {
        margin-top: 3.5rem;
        padding-top: 2.5rem;
        border-top: 1px solid var(--ep-line);
    }
    #events-page .ep-directory {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.5rem;
        margin-top: 0.85rem;
    }
    #events-page .ep-directory a {
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--ep-line);
        border-radius: 6px;
        background: #12161F;
        color: #cbd5e1;
        font-size: 11px;
        font-weight: 600;
        text-align: center;
        line-height: 1.3;
        text-decoration: none;
        transition: all 0.15s ease;
    }
    #events-page .ep-directory a:hover {
        color: var(--ep-accent);
        border-color: var(--ep-accent);
        background: #172030;
    }
    #events-page .ep-copy-panel {
        margin-top: 2rem;
        padding: 1.25rem;
        border: 1px solid var(--ep-line);
        border-radius: 8px;
        background: #12161F;
    }
    #events-page .ep-copy-panel p {
        color: #cbd5e1;
        font-size: 13px;
        line-height: 1.7;
    }
    #events-page .ep-faq {
        margin-top: 2rem;
    }
    #events-page .ep-faq-item {
        border: 1px solid var(--ep-line);
        border-radius: 8px;
        background: #12161F;
        margin-bottom: 0.5rem;
        overflow: hidden;
    }
    #events-page .ep-faq-item button {
        width: 100%;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.75rem 1rem;
        color: #ffffff;
        background: transparent;
        border: none;
        text-align: left;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    #events-page .ep-faq-answer {
        padding: 0 1rem 1rem;
        color: #94a3b8;
        font-size: 12px;
        line-height: 1.6;
        border-top: 1px solid rgba(255,255,255,0.05);
        padding-top: 0.75rem;
    }

    /* MODAL SUBMIT EVENT */
    #submit-event-modal .ep-modal {
        width: 100%;
        max-width: 1040px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--ep-line);
        border-radius: 8px;
        background: #080A0D;
        color: #ffffff;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.85);
        overflow: hidden;
    }
    #submit-event-modal .ep-modal-head {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #28364F;
        background: #121A27;
    }
    #submit-event-modal .ep-modal-foot {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-top: 1px solid #28364F;
        background: #121A27;
        z-index: 10;
    }
    #submit-event-modal .ep-modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        padding: 1.25rem;
        background: #080A0D;
    }
    #submit-event-modal .ep-form-section {
        padding: 1.25rem;
        border: 1px solid #28364F;
        border-radius: 6px;
        background: #182234;
        box-shadow: 0 4px 16px rgba(0,0,0,0.35);
    }
    #submit-event-modal .ep-form-section-title {
        padding-bottom: 0.5rem;
        margin-bottom: 0.85rem;
        border-bottom: 1px solid #28364F;
        color: #CCFF00;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    #submit-event-modal label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #F1F5F9;
        margin-bottom: 5px;
    }
    #submit-event-modal input[type="text"],
    #submit-event-modal input[type="date"],
    #submit-event-modal input[type="time"],
    #submit-event-modal input[type="email"],
    #submit-event-modal select,
    #submit-event-modal textarea {
        width: 100%;
        height: 38px;
        padding: 6px 10px;
        background: #0E1624;
        border: 1px solid #334460;
        border-radius: 6px;
        color: #FFFFFF;
        font-size: 12px;
        font-family: inherit;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.15s ease, background 0.15s ease;
        color-scheme: dark;
    }
    #submit-event-modal textarea {
        height: auto;
        min-height: 60px;
        resize: vertical;
    }
    #submit-event-modal input:focus,
    #submit-event-modal select:focus,
    #submit-event-modal textarea:focus {
        background: #0A101A;
        border-color: #CCFF00;
        box-shadow: 0 0 0 1px #CCFF00;
    }
    #submit-event-modal .race-distance-cb {
        width: 15px;
        height: 15px;
        accent-color: #CCFF00;
        cursor: pointer;
    }
    #submit-event-modal .ep-form-section label.race-distance-item {
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid #334460;
        background: #0E1624;
        border-radius: 6px;
        padding: 7px 10px;
        color: #F1F5F9;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        margin-bottom: 0 !important;
        user-select: none;
        transition: all 0.15s ease;
    }
    #submit-event-modal .ep-form-section label.race-distance-item:hover {
        border-color: #CCFF00;
        background: #141F32;
    }
    #submit-event-modal #banner-dropzone {
        border: 1.5px dashed #3A4D6E;
        border-radius: 6px;
        background: #0E1624;
        padding: 1.25rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    #submit-event-modal #banner-dropzone:hover {
        border-color: #CCFF00;
        background: #141F32;
    }
    #submit-event-modal #map-search-results {
        background: #182234;
        border: 1px solid #28364F;
        border-radius: 6px;
        max-height: 160px;
        overflow-y: auto;
        color: #FFFFFF;
        font-size: 12px;
        margin-top: 4px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.7);
    }
    #submit-event-modal #map-search-results div {
        padding: 8px 12px;
        border-bottom: 1px solid #28364F;
        cursor: pointer;
    }
    #submit-event-modal #map-search-results div:hover {
        background: #233148;
        color: #CCFF00;
    }
    #submit-event-modal #event-map {
        border-radius: 6px;
        border: 1px solid #28364F;
    }

    /* SUBMIT EVENT MODAL BUTTONS (LIGHT BACKGROUND WITH DARK TEXT & INTUITIVE HIERARCHY) */
    #submit-event-modal #btn-submit-event-cancel {
        background: #E2E8F0 !important;
        color: #0F172A !important;
        border: 1px solid #CBD5E1 !important;
        font-weight: 700 !important;
        padding: 8px 18px !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
    }
    #submit-event-modal #btn-submit-event-cancel:hover {
        background: #FFFFFF !important;
        color: #020617 !important;
        border-color: #94A3B8 !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15) !important;
    }
    #submit-event-modal #btn-submit-event-submit {
        background: #CCFF00 !important;
        color: #080A0D !important;
        border: 1px solid #CCFF00 !important;
        font-weight: 800 !important;
        padding: 8px 22px !important;
        border-radius: 6px !important;
        cursor: pointer !important;
        transition: all 0.15s ease !important;
        box-shadow: 0 2px 10px rgba(204, 255, 0, 0.2) !important;
    }
    #submit-event-modal #btn-submit-event-submit:hover {
        background: #D8FF33 !important;
        border-color: #D8FF33 !important;
        box-shadow: 0 4px 16px rgba(204, 255, 0, 0.35) !important;
    }
    #submit-event-modal #btn-submit-event-send-otp {
        background: #E2E8F0 !important;
        color: #0F172A !important;
        border: 1px solid #CBD5E1 !important;
        font-weight: 700 !important;
        padding: 8px 14px !important;
        border-radius: 6px !important;
        transition: all 0.15s ease !important;
    }
    #submit-event-modal #btn-submit-event-send-otp:hover {
        background: #FFFFFF !important;
        color: #020617 !important;
        border-color: #94A3B8 !important;
    }

    /* MAPBOX CUSTOM STYLES */
    .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo, .mapboxgl-ctrl-attrib { display: none !important; }
    .mapboxgl-ctrl-group {
        background: #0B0F17 !important;
        border: 1px solid var(--ep-line) !important;
        border-radius: 6px !important;
        overflow: hidden;
    }
    .mapboxgl-ctrl-group button { border-bottom: 1px solid var(--ep-line) !important; }
    .mapboxgl-ctrl-group button .mapboxgl-ctrl-icon { filter: invert(1) brightness(2); }
    .mapboxgl-popup { max-width: 280px !important; z-index: 1000 !important; }
    .mapboxgl-popup-content {
        background: #12161F !important;
        color: #ffffff !important;
        border-radius: 8px !important;
        border: 1px solid var(--ep-line) !important;
        box-shadow: 0 16px 36px rgba(0,0,0,0.8) !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    .mapboxgl-popup-anchor-top .mapboxgl-popup-tip { border-bottom-color: #12161F !important; }
    .mapboxgl-popup-anchor-bottom .mapboxgl-popup-tip { border-top-color: #12161F !important; }
    .mapboxgl-popup-anchor-left .mapboxgl-popup-tip { border-right-color: #12161F !important; }
    .mapboxgl-popup-anchor-right .mapboxgl-popup-tip { border-left-color: #12161F !important; }
    .mapboxgl-popup-close-button {
        color: #ffffff !important;
        font-size: 16px !important;
        padding: 4px 8px !important;
        background: rgba(0,0,0,0.6) !important;
        border-radius: 0 0 0 6px !important;
        right: 0 !important;
        top: 0 !important;
        z-index: 10 !important;
    }
    .mapboxgl-popup-close-button:hover { color: var(--ep-accent) !important; }
    .custom-event-pin { cursor: pointer; transition: transform 0.15s ease; }
    .custom-event-pin:hover { transform: scale(1.15); z-index: 10; }

    @media(max-width:1023px) {
        #events-page .ep-hero-grid { grid-template-columns: 1fr; gap: 1.5rem; }
        #events-page .ep-featured, #events-page .ep-featured-slide, #events-page .ep-featured-slide img { min-height: 280px; }
        #events-page #filter-form { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #events-page .ep-directory { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media(max-width:639px) {
        #events-page .ep-actions { display: grid; grid-template-columns: 1fr; }
        #events-page .ep-btn { width: 100%; }
        #events-page .ep-ops { flex-direction: column; }
        #events-page #events-explorer-map { height: 320px; }
        #events-page #filter-form { grid-template-columns: 1fr; }
        #events-page .ep-directory { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        #events-page .ep-featured, #events-page .ep-featured-slide, #events-page .ep-featured-slide img { min-height: 240px; }
    }
</style>
@endpush

@section('content')
<div id="events-page" class="pt-0 pb-16">

    @php
        $upcomingRaceList = (isset($featuredEvents) && $featuredEvents->isNotEmpty())
            ? $featuredEvents
            : ((isset($events) && $events->isNotEmpty()) ? $events->take(3) : collect());
    @endphp

    <!-- PREMIUM FULL-WIDTH HERO SECTION (720px Desktop) -->
    <section class="hero-v2-container">
        <!-- Dark Cinematic Marathon Background with Solid Contrast Overlay -->
        <div class="hero-cinematic-bg select-none">
            <img src="{{ $heroBgUrl }}"
                 alt="Indonesia Running Calendar 2026 - Marathon Race"
                 loading="eager">
            <!-- Multi-stop directional gradient overlays ensuring 100% dark solid canvas under left text -->
            <div class="hero-cinematic-overlay"></div>
            <!-- Ambient lime subtle accent glow -->
            <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full blur-3xl pointer-events-none" style="background: rgba(183, 255, 0, 0.08);"></div>
        </div>

        <!-- Left Aligned Content Container (Max-width: 7xl, Horizontal padding: 64px on desktop) -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-6 sm:px-10 lg:px-16 py-12 lg:py-0">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                
                <!-- Left Column: Core Hero Content (Max-width 560px) -->
                <div class="lg:col-span-7 max-w-[560px]">
                    
                    <!-- Badge -->
                    <div class="mb-5">
                        <div class="hero-badge">
                            <span class="hero-badge-dot"></span>
                            <span>INDONESIA RUNNING CALENDAR 2026</span>
                        </div>
                    </div>

                    <!-- Headline (64px, Line-height 1.05) -->
                    <h1 class="hero-headline">
                        Temukan Race <span class="text-lime-highlight">Berikutnya</span> di Indonesia
                    </h1>

                    <!-- Subheadline -->
                    <p class="text-slate-200 text-sm sm:text-base leading-relaxed mt-5 max-w-[520px]" style="color: #cbd5e1;">
                        Jelajahi event 5K, 10K, Half Marathon hingga Ultra Marathon dari seluruh Indonesia dalam satu kalender.
                    </p>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3.5 mt-8">
                        <a href="#filter-form"
                           onclick="document.getElementById('filter-form')?.scrollIntoView({behavior: 'smooth', block: 'start'})"
                           class="btn-hero-primary"
                           style="background-color: #B7FF00 !important; color: #08111F !important; border: 1px solid #B7FF00 !important;">
                            <i class="fas fa-magnifying-glass text-xs" style="color: #08111F !important;"></i>
                            <span style="color: #08111F !important; font-weight: 900;">Cari Event Lari</span>
                        </a>

                        <button type="button"
                                id="btn-open-submit-event"
                                class="btn-hero-secondary"
                                style="background-color: #0E1626 !important; color: #ffffff !important; border: 1px solid #2B384E !important;">
                            <i class="fas fa-plus text-xs" style="color: #B7FF00 !important;"></i>
                            <span style="color: #ffffff !important; font-weight: 700;">Submit Event Gratis</span>
                        </button>
                    </div>

                    <!-- Trust Statistics (500+ Event, 34 Provinsi, 10K+ Runner) -->
                    <div class="pt-8 mt-8 border-t border-slate-800/90 grid grid-cols-3 gap-4 max-w-[480px]">
                        <div>
                            <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight" style="color: #ffffff;">500<span style="color: #B7FF00;">+</span></div>
                            <div class="text-[11px] sm:text-xs font-bold uppercase tracking-wider mt-0.5" style="color: #94a3b8;">Event</div>
                        </div>
                        <div class="border-l border-slate-800/90 pl-4 sm:pl-6">
                            <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight" style="color: #ffffff;">34</div>
                            <div class="text-[11px] sm:text-xs font-bold uppercase tracking-wider mt-0.5" style="color: #94a3b8;">Provinsi</div>
                        </div>
                        <div class="border-l border-slate-800/90 pl-4 sm:pl-6">
                            <div class="text-2xl sm:text-3xl font-black font-mono tracking-tight" style="color: #ffffff;">10K<span style="color: #B7FF00;">+</span></div>
                            <div class="text-[11px] sm:text-xs font-bold uppercase tracking-wider mt-0.5" style="color: #94a3b8;">Runner</div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Floating Upcoming Race Card -->
                <div class="lg:col-span-5 flex justify-center lg:justify-end">
                    @if($upcomingRaceList->isNotEmpty())
                        <div class="w-full max-w-[420px] lg:max-w-[440px]"
                             x-data="{
                                 currentIndex: 0,
                                 total: {{ $upcomingRaceList->count() }},
                                 next() { this.currentIndex = (this.currentIndex + 1) % this.total; },
                                 prev() { this.currentIndex = (this.currentIndex - 1 + this.total) % this.total; }
                             }">
                            
                            <!-- Glassmorphism Upcoming Race Card -->
                            <div class="floating-race-card group">
                                
                                <!-- Ambient glow effect -->
                                <div class="absolute -top-16 -right-16 w-36 h-36 rounded-full blur-2xl pointer-events-none" style="background: rgba(183, 255, 0, 0.12);"></div>

                                <!-- Card Header: Badge & Controls -->
                                <div class="flex items-center justify-between gap-2 pb-3.5 border-b border-slate-800/90 relative z-10">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-2 h-2 rounded-full" style="background-color: #B7FF00; box-shadow: 0 0 6px #B7FF00;"></span>
                                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider" style="color: #B7FF00;">UPCOMING RACE</span>
                                    </div>

                                    @if($upcomingRaceList->count() > 1)
                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="prev()" aria-label="Previous race" 
                                                    class="w-7 h-7 rounded-md bg-slate-900 border border-slate-700/80 hover:border-slate-500 text-slate-300 hover:text-white flex items-center justify-center text-[10px] transition cursor-pointer">
                                                <i class="fas fa-chevron-left"></i>
                                            </button>
                                            <span class="text-[10px] font-mono font-bold text-slate-400 px-1.5" x-text="(currentIndex + 1) + '/' + total"></span>
                                            <button type="button" @click="next()" aria-label="Next race" 
                                                    class="w-7 h-7 rounded-md bg-slate-900 border border-slate-700/80 hover:border-slate-500 text-slate-300 hover:text-white flex items-center justify-center text-[10px] transition cursor-pointer">
                                                <i class="fas fa-chevron-right"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                <!-- Slide Items -->
                                <div class="relative overflow-hidden mt-3.5">
                                    @foreach($upcomingRaceList as $idx => $race)
                                        <div x-show="currentIndex === {{ $idx }}" 
                                             x-transition:enter="transition ease-out duration-300"
                                             x-transition:enter-start="opacity-0 translate-x-3"
                                             x-transition:enter-end="opacity-100 translate-x-0"
                                             class="space-y-3.5">

                                            <!-- Race Cover Image with Category Chips -->
                                            <a href="{{ $race->public_url }}" class="block relative aspect-[16/9] w-full rounded-md overflow-hidden bg-slate-950 border border-slate-800 group/img">
                                                <img src="{{ $race->getHeroImageUrl() ?: $heroBgUrl }}" 
                                                     alt="{{ $race->name }}" 
                                                     class="w-full h-full object-cover transition duration-500 group-hover/img:scale-105">
                                                
                                                <div class="absolute inset-0 bg-gradient-to-t from-[#08111F]/95 via-transparent to-black/30"></div>

                                                @if($race->distances && $race->distances->isNotEmpty())
                                                    <div class="absolute bottom-2.5 left-2.5 flex flex-wrap gap-1.5 z-10">
                                                        @foreach($race->distances->take(4) as $d)
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shadow"
                                                                  style="background: rgba(8, 17, 31, 0.95); border: 1px solid rgba(71, 85, 105, 0.7); color: #ffffff;">
                                                                {{ $d->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if($race->start_at)
                                                    @php
                                                        $daysUntil = now()->diffInDays($race->start_at, false);
                                                    @endphp
                                                    <div class="absolute top-2.5 right-2.5 z-10">
                                                        @if($daysUntil > 0)
                                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold shadow"
                                                                  style="background: rgba(8, 17, 31, 0.95); border: 1px solid rgba(183, 255, 0, 0.5); color: #B7FF00;">
                                                                {{ (int)$daysUntil }} HARI LAGI
                                                            </span>
                                                        @elseif($daysUntil === 0)
                                                            <span class="px-2 py-0.5 rounded bg-rose-950/90 border border-rose-700 text-rose-300 text-[10px] font-mono font-bold shadow">
                                                                HARI INI
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </a>

                                            <!-- Race Title & Meta -->
                                            <div>
                                                <a href="{{ $race->public_url }}" class="block text-base font-bold text-white hover:text-[#B7FF00] transition line-clamp-1">
                                                    {{ $race->name }}
                                                </a>

                                                <div class="mt-2 space-y-1.5 text-xs text-slate-300">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fas fa-calendar-day text-xs w-4" style="color: #B7FF00;"></i>
                                                        <span>{{ $race->start_at ? $race->start_at->translatedFormat('l, d F Y') : 'Jadwal Segera Diumumkan' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <i class="fas fa-location-dot text-xs w-4" style="color: #B7FF00;"></i>
                                                        <span class="line-clamp-1">{{ $race->city ? $race->city->name : $race->location_name }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Action Button -->
                                            <div class="pt-1.5">
                                                <a href="{{ $race->public_url }}" 
                                                   class="btn-card-action"
                                                   style="background-color: #B7FF00 !important; color: #08111F !important; border: 1px solid #B7FF00 !important;">
                                                    <span style="color: #08111F !important; font-weight: 900;">Lihat Detail Race</span>
                                                    <i class="fas fa-arrow-right text-[10px]" style="color: #08111F !important;"></i>
                                                </a>
                                            </div>

                                        </div>
                                    @endforeach
                                </div>

                            </div>
                        </div>
                    @else
                        <!-- Fallback Flagship Card if no upcoming race in DB -->
                        <div class="w-full max-w-[420px] lg:max-w-[440px]">
                            <div class="floating-race-card group">
                                <div class="flex items-center justify-between gap-2 pb-3.5 border-b border-slate-800/90">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block w-2 h-2 rounded-full" style="background-color: #B7FF00; box-shadow: 0 0 6px #B7FF00;"></span>
                                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider" style="color: #B7FF00;">UPCOMING RACE</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                          style="background: rgba(8, 17, 31, 0.95); border: 1px solid rgba(183, 255, 0, 0.5); color: #B7FF00;">
                                        OPEN REGISTRATION
                                    </span>
                                </div>

                                <div class="mt-3.5 space-y-3.5">
                                    <div class="block relative aspect-[16/9] w-full rounded-md overflow-hidden bg-slate-950 border border-slate-800">
                                        <img src="{{ $heroBgUrl }}" alt="Borobudur Marathon 2026" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#08111F]/95 via-transparent to-black/30"></div>
                                        <div class="absolute bottom-2.5 left-2.5 flex flex-wrap gap-1.5 z-10">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                                  style="background: rgba(8, 17, 31, 0.95); border: 1px solid rgba(71, 85, 105, 0.7); color: #ffffff;">42K FM</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                                  style="background: rgba(8, 17, 31, 0.95); border: 1px solid rgba(71, 85, 105, 0.7); color: #ffffff;">21K HM</span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                                  style="background: rgba(8, 17, 31, 0.95); border: 1px solid rgba(71, 85, 105, 0.7); color: #ffffff;">10K</span>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="text-base font-bold text-white">Borobudur Marathon 2026</div>
                                        <div class="mt-2 space-y-1.5 text-xs text-slate-300">
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-calendar-day text-xs w-4" style="color: #B7FF00;"></i>
                                                <span>Minggu, 15 November 2026</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <i class="fas fa-location-dot text-xs w-4" style="color: #B7FF00;"></i>
                                                <span>Taman Lumbini, Candi Borobudur, Jawa Tengah</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-1.5">
                                        <a href="#filter-form" onclick="document.getElementById('filter-form')?.scrollIntoView({behavior: 'smooth', block: 'start'})"
                                           class="btn-card-action"
                                           style="background-color: #B7FF00 !important; color: #08111F !important; border: 1px solid #B7FF00 !important;">
                                            <span style="color: #08111F !important; font-weight: 900;">Jelajahi Semua Race</span>
                                            <i class="fas fa-arrow-right text-[10px]" style="color: #08111F !important;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </section>

    <section class="ep-shell mt-6">
        <div class="ep-ops">
            <div>
                <div class="text-[11px] font-mono font-semibold uppercase tracking-wider text-[#ccff00] mb-1">Event Organizers</div>
                <h2 class="text-base font-semibold text-white">Publikasikan event dan buka pendaftaran tanpa biaya awal</h2>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Listing event, landing page pendaftaran mandiri, integrasi tiket QR, notifikasi WhatsApp, dan pembayaran online dalam satu alur terpusat.
                </p>
            </div>

            <div class="ep-actions !mt-0 shrink-0">
                <a href="{{ route('eo.landing') }}" class="ep-btn ep-btn--primary">Buat Registration Page</a>
                <button type="button" onclick="document.getElementById('btn-open-submit-event').click()" class="ep-btn">Submit Event Gratis</button>
            </div>
        </div>
    </section>

    <section class="ep-section ep-shell">
        <div class="ep-section-head">
            <div>
                <h2 class="text-lg font-semibold text-white">Peta Sebaran Event Lari Indonesia</h2>
                <p class="ep-section-copy">Eksplorasi lokasi event berdasarkan kota dan kategori lomba.</p>
            </div>
            <div id="events-map-count" class="text-xs font-mono font-semibold text-[#ccff00]">
                {{ count($mapEvents ?? []) }} EVENT
            </div>
        </div>

        <div id="events-explorer-map-section" class="ep-map-shell">
            <div class="ep-map-toolbar" id="btn-toggle-events-map">
                <div class="ep-map-tools">
                    <div id="events-map-layer-dropdown-wrap" class="relative inline-block">
                        <button type="button" id="btn-toggle-events-map-layer" class="ep-tool-btn" onclick="toggleEventsMapLayerMenu()">
                            <i class="fas fa-layer-group text-[#ccff00]"></i>
                            <span id="label-events-active-layer">Streets</span>
                            <i class="fas fa-chevron-down text-[8px] opacity-60 ml-0.5"></i>
                        </button>

                        <div id="events-map-layer-menu" class="hidden absolute left-0 top-full mt-1.5 z-[9999] min-w-[170px] border border-[#232B3B] bg-[#12161F] rounded-md shadow-2xl overflow-hidden divide-y divide-[#232B3B]">
                            @foreach([
                                'streets' => 'Streets (Default)',
                                'outdoors' => 'Outdoors Terrain',
                                'dark' => 'Dark Tactical',
                                'satellite' => 'Satelit Mapbox',
                            ] as $layerKey => $layerLabel)
                                <button type="button"
                                        onclick="setEventsMapLayer('{{ $layerKey }}')"
                                        class="w-full px-3.5 py-2.5 text-left text-[11px] font-semibold text-slate-200 hover:text-white hover:bg-[#1A202C] transition flex items-center justify-between">
                                    <span>{{ $layerLabel }}</span>
                                    <i class="fas fa-check text-[#ccff00] text-[10px] layer-check-icon {{ $layerKey === 'streets' ? '' : 'hidden' }}" data-layer="{{ $layerKey }}"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" id="btn-events-map-locate-me" class="ep-tool-btn">
                        <i class="fas fa-location-crosshairs text-[#ccff00]"></i>
                        Lokasi Saya
                    </button>
                    <button type="button" id="btn-events-map-recenter" class="ep-tool-btn">
                        Pusatkan
                    </button>
                </div>

                <button type="button" id="btn-events-map-minimize-toggle" class="ep-tool-btn">
                    <span id="label-events-map-toggle">Sembunyikan</span>
                    <i id="icon-events-map-toggle" class="fas fa-chevron-up text-[8px]"></i>
                </button>
            </div>

            <div id="events-map-collapse-wrap">
                <div id="events-explorer-map"></div>

                <div class="ep-map-filter">
                    <button type="button"
                            onclick="setEventsMapTypeFilter('')"
                            class="btn-event-map-pill is-active"
                            data-map-type="">
                        Semua
                    </button>

                    @foreach($raceTypes as $rType)
                        <button type="button"
                                onclick="setEventsMapTypeFilter('{{ $rType->id }}')"
                                class="btn-event-map-pill"
                                data-map-type="{{ $rType->id }}">
                            {{ $rType->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="ep-filter-shell">
            <form id="filter-form">
                @if(request('city'))
                    <input type="hidden" name="city" value="{{ request('city') }}">
                @endif
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif

                <div>
                    <label>Cari Event</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama event atau lokasi...">
                </div>

                <div>
                    <label>Bulan</label>
                    <select name="month">
                        <option value="">Semua Bulan</option>
                        @foreach(range(1,12) as $m)
                            <option value="{{ $m }}" @selected(request('month') == $m)>
                                {{ date('F', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Kota</label>
                    <select name="city_id">
                        <option value="">Semua Kota</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Jenis Lomba</label>
                    <select name="race_type_id">
                        <option value="">Semua Jenis</option>
                        @foreach($raceTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Jarak</label>
                    <select name="race_distance_id">
                        <option value="">Semua Jarak</option>
                        @foreach($raceDistances as $distance)
                            <option value="{{ $distance->id }}">{{ $distance->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="ep-quick">
                <button type="button" class="quick-filter-btn is-active" data-filter-type="distance" data-value="">Semua Jarak</button>
                @foreach($raceDistances as $distance)
                    <button type="button" class="quick-filter-btn" data-filter-type="distance" data-value="{{ $distance->id }}">{{ $distance->name }}</button>
                @endforeach

                <span class="w-px h-5 bg-white/10 mx-1 shrink-0"></span>

                <button type="button" class="quick-filter-btn" data-filter-type="type" data-value="">Semua Jenis</button>
                @foreach($raceTypes as $type)
                    <button type="button" class="quick-filter-btn" data-filter-type="type" data-value="{{ $type->id }}">{{ $type->name }}</button>
                @endforeach
            </div>
        </div>

        <div class="ep-list-head flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-white">Kalender Event Lari Terbaru</h2>
                <p class="text-xs text-slate-400 mt-0.5">Daftar jadwal perlombaan lari yang telah terverifikasi di Indonesia.</p>
            </div>
            <div class="ep-view-switcher self-start sm:self-auto" role="group" aria-label="Pilihan tampilan jadwal">
                <button type="button" id="btn-view-list" class="ep-view-btn is-active" onclick="setEventsView('list')" title="Tampilan List" aria-pressed="true">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>List</span>
                </button>
                <button type="button" id="btn-view-card" class="ep-view-btn" onclick="setEventsView('card')" title="Tampilan Card" aria-pressed="false">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Card</span>
                </button>
            </div>
        </div>

        <div id="events-container">
            @include('events.partials.list', ['events' => $events])
        </div>

        <div id="pagination-container" class="mt-8">
            {{ $events->links() }}
        </div>

        <div id="loading-indicator" class="hidden py-14 text-center">
            <div class="inline-block h-7 w-7 border-2 border-white/10 border-t-[#ccff00] rounded-full animate-spin"></div>
            <p class="mt-2 text-slate-400 text-xs font-mono uppercase tracking-wider">Memuat jadwal...</p>
        </div>
    </section>

    <section class="ep-seo ep-shell">
        @php
            $months = [
                1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
            ];

            $seoCities = [
                'jakarta'=>'Jakarta','bandung'=>'Bandung','surabaya'=>'Surabaya','yogyakarta'=>'Yogyakarta',
                'malang'=>'Malang','bali'=>'Bali','semarang'=>'Semarang','bogor'=>'Bogor',
                'makassar'=>'Makassar','medan'=>'Medan','balikpapan'=>'Balikpapan','batam'=>'Batam'
            ];

            $seoCategories = [
                '5k'=>'Jadwal lari 5K','10k'=>'Jadwal lari 10K','half-marathon'=>'Jadwal half marathon',
                'marathon'=>'Jadwal marathon','trail-run'=>'Jadwal trail run','fun-run'=>'Jadwal fun run',
                'virtual-run'=>'Jadwal virtual run','ultra-marathon'=>'Jadwal ultra marathon'
            ];
        @endphp

        <div class="mb-10">
            <h2 class="text-base font-semibold text-white mb-3">Jadwal Lari Berdasarkan Bulan</h2>

            <div class="ep-directory">
                @foreach($months as $num => $monthName)
                    <a href="{{ route('events.index') }}?month={{ sprintf('%04d-%02d', 2026, $num) }}">
                        Jadwal Lari {{ $monthName }} 2026
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mb-10">
            <h2 class="text-base font-semibold text-white mb-3">Jadwal Lari Berdasarkan Kota</h2>

            <div class="ep-directory">
                @foreach($seoCities as $slug => $cityName)
                    <a href="/event-lari-di-{{ $slug }}">Event Lari di {{ $cityName }}</a>
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="text-base font-semibold text-white mb-3">Jadwal Lari Berdasarkan Kategori</h2>

            <div class="ep-directory">
                @foreach($seoCategories as $slug => $label)
                    <a href="/jadwal-{{ $slug }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="ep-copy-panel">
            <h2 class="text-base font-semibold text-white mb-3">Panduan Memilih Event Lari</h2>

            <div class="max-w-4xl space-y-3 text-sm text-slate-300 leading-relaxed">
                <p>
                    Sebelum mendaftar lomba lari, sesuaikan pilihan rute dengan tingkat kebugaran,
                    target latihan, jarak tempuh, lokasi, dan kesiapan fisik. Pelari pemula disarankan
                    memulai dari fun run atau 5K, sedangkan pelari berpengalaman dapat mempersiapkan
                    10K, half marathon, marathon, maupun trail run.
                </p>
                <p>
                    Perhatikan cut-off time (COT), elevasi rute, fasilitas race pack, titik hidrasi (water station),
                    keamanan medis, serta rekam jejak penyelenggara lomba agar pengalaman race berjalan aman
                    dan terukur.
                </p>
            </div>
        </div>

        @php
            $faqs = [
                ['q'=>'Apa itu jadwal lari?','a'=>'Jadwal lari adalah kalender informasi event lomba lari yang disusun berdasarkan tanggal pelaksanaan, lokasi kota, kategori jarak, dan jenis perlombaan seperti fun run, 5K, 10K, half marathon, marathon, trail run, hingga virtual run.'],
                ['q'=>'Bagaimana cara mencari event lari terdekat?','a'=>'Gunakan filter kota, bulan pelaksanaan, kategori jarak, atau peta interaktif di Ruang Lari untuk menemukan perlombaan yang sesuai dengan lokasi dan jadwal latihan Anda.'],
                ['q'=>'Apa saja kategori jarak lomba lari yang tersedia?','a'=>'Kategori umum mencakup 5K, 10K, half marathon (21.1 km), marathon (42.2 km), ultra marathon, trail run, fun run keluarga, hingga virtual run.'],
                ['q'=>'Apakah data jadwal lari di Ruang Lari selalu diperbarui?','a'=>'Ya. Kalender event diperbarui secara teratur berdasarkan informasi resmi dari penyelenggara lomba, komunitas, dan kanal pendaftaran terverifikasi.'],
                ['q'=>'Bagaimana cara mengajukan event lari ke Ruang Lari?','a'=>'Gunakan tombol Submit Event Gratis untuk melengkapi nama lomba, tanggal, lokasi, kategori jarak, banner, dan tautan pendaftaran resmi tanpa dipungut biaya.'],
            ];
        @endphp

        <div class="ep-faq" x-data="{active:null}">
            <h2 class="text-base font-semibold text-white mb-3 pt-6">Pertanyaan Umum Seputar Jadwal Lari</h2>

            @foreach($faqs as $i => $faq)
                <div class="ep-faq-item">
                    <button type="button" @click="active = active === {{ $i }} ? null : {{ $i }}">
                        <span>{{ $faq['q'] }}</span>
                        <i class="fas fa-plus text-[10px] text-[#ccff00] transition-transform duration-200"
                           :class="active === {{ $i }} ? 'rotate-45' : ''"></i>
                    </button>
                    <div x-show="active === {{ $i }}" x-collapse x-cloak class="ep-faq-answer">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>

{{-- Submit Event modal: IDs retained for existing JS --}}
<div id="submit-event-modal" class="fixed inset-0 z-[9999] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black/80"></div>

    <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-6">
        <div class="ep-modal">
            <div class="ep-modal-head">
                <div>
                    <h3 class="text-lg font-bold text-white">Ajukan Event Lari Baru</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daftarkan event lari Anda untuk ditayangkan gratis di kalender Ruang Lari.</p>
                </div>
                <button type="button" id="btn-close-submit-event" class="w-8 h-8 rounded-md border border-[#232B3B] text-slate-300 hover:text-white hover:border-slate-500 flex items-center justify-center transition" aria-label="Tutup modal">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            <div id="submit-event-alert" class="hidden px-5 pt-4"></div>

            <form id="submit-event-form" class="ep-modal-body">
                <input type="text" name="website" id="submit_event_website" class="hidden" tabindex="-1" autocomplete="off">
                <input type="hidden" name="started_at" id="submit_event_started_at" value="0">
                <input type="hidden" name="otp_id" id="submit_event_otp_id" value="">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <section class="ep-form-section">
                            <div class="ep-form-section-title">01 / Informasi Utama</div>

                            <div class="space-y-3">
                                <div>
                                    <label for="submit_event_name">Nama Event *</label>
                                    <input type="text" name="event_name" id="submit_event_name" placeholder="Contoh: Jakarta City Run 2026" required>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="submit_event_date">Tanggal Event *</label>
                                        <input type="date" name="event_date" id="submit_event_date" required>
                                    </div>
                                    <div>
                                        <label for="submit_event_time">Jam Mulai</label>
                                        <input type="time" name="start_time" id="submit_event_time" value="05:00">
                                    </div>
                                </div>

                                <div>
                                    <label for="submit_event_race_type_id">Jenis Lomba</label>
                                    <select name="race_type_id" id="submit_event_race_type_id">
                                        <option value="">Pilih Jenis</option>
                                        @foreach($raceTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </section>

                        <section class="ep-form-section">
                            <div class="ep-form-section-title">02 / Banner Event</div>

                            <div id="banner-dropzone">
                                <input type="file" name="banner" id="submit_event_banner" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden">

                                <div id="banner-dropzone-default">
                                    <i class="fas fa-arrow-up-from-bracket text-xl text-[#ccff00]"></i>
                                    <p class="mt-2 text-xs font-semibold text-white">Upload Banner Landscape</p>
                                    <p class="mt-1 text-[11px] text-slate-400">PNG, JPG, WEBP · Maksimum 2MB</p>
                                </div>

                                <div id="banner-dropzone-preview" class="hidden">
                                    <img id="banner-preview-img" src="" class="max-h-36 mx-auto object-cover rounded-md">
                                    <span id="banner-filename" class="block mt-2 text-xs text-slate-300 font-mono"></span>
                                    <button type="button" id="btn-remove-banner" class="mt-2 text-xs font-semibold text-red-400 hover:text-red-300 transition">Ganti Banner</button>
                                </div>
                            </div>
                        </section>

                        <section class="ep-form-section">
                            <div class="ep-form-section-title">03 / Kategori Jarak</div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                @foreach($raceDistances as $distance)
                                    <label class="race-distance-item">
                                        <input type="checkbox" name="race_distance_ids[]" value="{{ $distance->id }}" class="race-distance-cb">
                                        <span>{{ $distance->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <label for="submit_event_custom_distances">Jarak Custom (Opsional)</label>
                                <input type="text" name="custom_distances" id="submit_event_custom_distances" placeholder="Contoh: 7K, 100K, 50 mil">
                            </div>
                        </section>
                    </div>

                    <div class="space-y-4">
                        <section class="ep-form-section">
                            <div class="ep-form-section-title">04 / Lokasi Event</div>

                            <div class="flex gap-2 mb-3">
                                <input type="text" id="submit_map_search_input" placeholder="Cari venue atau alamat di peta..." autocomplete="off">
                                <button type="button" id="btn-geolocation" class="ep-btn !min-h-[38px] shrink-0" title="Gunakan Lokasi Saya">
                                    <i class="fas fa-crosshairs text-[#ccff00]"></i>
                                </button>
                            </div>

                            <button type="button" id="btn-clear-map-search" class="hidden"></button>
                            <i id="map-search-icon" class="hidden"></i>
                            <div id="map-search-results" class="hidden"></div>

                            <div class="relative mb-3">
                                <div id="event-map" class="w-full h-44 bg-[#080A0D]"></div>
                                <div id="map-geocoding-status" class="absolute left-2 bottom-2 text-[10px] font-mono px-1.5 py-0.5 rounded bg-[#080A0D]/90 text-[#ccff00]"></div>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <label for="submit_event_city_id">Kota / Kabupaten</label>
                                    <select name="city_id" id="submit_event_city_id">
                                        <option value="">Pilih Kota</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="submit_event_location">Nama Venue *</label>
                                    <input type="text" name="location_name" id="submit_event_location" placeholder="Contoh: Plaza Parkir Timur GBK Senayan" required>
                                </div>
                                <div>
                                    <label for="submit_event_address">Alamat Lengkap</label>
                                    <input type="text" name="location_address" id="submit_event_address" placeholder="Contoh: Jl. Pintu Satu Senayan, Gelora, Jakarta Pusat">
                                </div>
                            </div>
                        </section>

                        <section class="ep-form-section">
                            <div class="ep-form-section-title">05 / Tautan & Penyelenggara</div>
                            <div class="space-y-3">
                                <div>
                                    <label for="submit_event_registration_link">Link Pendaftaran Resmi</label>
                                    <input type="text" name="registration_link" id="submit_event_registration_link" placeholder="https://...">
                                </div>
                                <div>
                                    <label for="submit_event_social_media_link">Instagram / Media Sosial Event</label>
                                    <input type="text" name="social_media_link" id="submit_event_social_media_link" placeholder="@jakartarun / https://instagram.com/...">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label for="submit_event_organizer_name">Penyelenggara / EO</label>
                                        <input type="text" name="organizer_name" id="submit_event_organizer_name" placeholder="Nama EO / Komunitas">
                                    </div>
                                    <div>
                                        <label for="submit_event_organizer_contact">Kontak EO</label>
                                        <input type="text" name="organizer_contact" id="submit_event_organizer_contact" placeholder="Email / No. WA">
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="ep-form-section">
                            <div class="ep-form-section-title">06 / Verifikasi Kontributor</div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="submit_event_contributor_name">Nama Pengirim</label>
                                    <input type="text" name="contributor_name" id="submit_event_contributor_name" placeholder="Nama Anda">
                                </div>
                                <div>
                                    <label for="submit_event_contributor_email">Email Verifikasi *</label>
                                    <input type="email" name="contributor_email" id="submit_event_contributor_email" placeholder="email@domain.com" required>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="submit_event_notes">Catatan Tambahan</label>
                                <textarea name="notes" id="submit_event_notes" rows="2" placeholder="Informasi biaya pendaftaran, racepack, atau detail penting lainnya..."></textarea>
                            </div>

                            <div class="grid grid-cols-[1fr_auto] gap-2 mt-3 items-end">
                                <div>
                                    <label for="submit_event_otp_code">Kode OTP *</label>
                                    <input type="text" inputmode="numeric" maxlength="6" name="otp_code" id="submit_event_otp_code" placeholder="6 digit kode OTP">
                                </div>
                                <button type="button" id="btn-submit-event-send-otp" class="ep-btn !min-h-[38px] shrink-0">Kirim OTP</button>
                            </div>
                        </section>
                    </div>
                </div>
            </form>

            <div class="ep-modal-foot">
                <div class="text-xs text-slate-400">Kode OTP dikirim ke email untuk verifikasi keabsahan data.</div>
                <div class="flex gap-2">
                    <button type="button" id="btn-submit-event-cancel" class="ep-btn">Batal</button>
                    <button type="button" id="btn-submit-event-submit" class="ep-btn ep-btn--primary">Submit Event</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapboxToken = '{{ config("services.mapbox.token") }}';
    if (typeof mapboxgl !== 'undefined' && mapboxToken) {
        mapboxgl.accessToken = mapboxToken;
    }

    // ----------------------------------------------------
    // 1. EXPLORER MAP ENGINE (MAPBOX GL)
    // ----------------------------------------------------
    let initialEvents = @json($mapEvents ?? []);
    let currentMapEvents = Array.isArray(initialEvents) ? initialEvents : [];
    let eventsExplorerMap = null;
    let explorerMarkers = [];
    let userLocationMarker = null;
    let activeLayerKey = 'streets';
    let activeTypeFilter = '';

    const mapStyles = {
        streets: 'mapbox://styles/mapbox/streets-v12',
        outdoors: 'mapbox://styles/mapbox/outdoors-v12',
        dark: 'mapbox://styles/mapbox/dark-v11',
        satellite: 'mapbox://styles/mapbox/satellite-streets-v12'
    };

    function initEventsExplorerMap() {
        const mapContainer = document.getElementById('events-explorer-map');
        if (!mapContainer || eventsExplorerMap || typeof mapboxgl === 'undefined' || !mapboxToken) return;

        const defaultCenter = [118.0149, -2.5489]; // [lng, lat] Indonesia Center
        const defaultZoom = 4.2;

        eventsExplorerMap = new mapboxgl.Map({
            container: 'events-explorer-map',
            style: mapStyles[activeLayerKey],
            center: defaultCenter,
            zoom: defaultZoom
        });

        eventsExplorerMap.addControl(new mapboxgl.NavigationControl({ showCompass: true }), 'top-right');

        eventsExplorerMap.on('load', function () {
            renderMapMarkers(currentMapEvents, true);
        });
    }

    function buildEventPopupHtml(event) {
        const heroImg = event.hero_image || '{{ asset("images/hero/jadwal-lari.webp") }}';
        const dateStr = event.start_at || '';
        const locStr = event.location_name || event.city || 'Indonesia';
        const raceType = event.race_type || 'Road Run';
        const distancesHtml = Array.isArray(event.distances) && event.distances.length
            ? event.distances.map(d => `<span style="padding:2px 5px;background:rgba(255,255,255,0.08);border-radius:2px;font-size:8px;font-weight:900;color:#fff;font-family:monospace;">${d}</span>`).join('')
            : '';

        return `
            <div style="background:#07101c; color:#fff; overflow:hidden; font-family:inherit;">
                <div style="position:relative; aspect-ratio:16/9; width:100%; overflow:hidden; background:#0b1522;">
                    <img src="${heroImg}" style="width:100%; height:100%; object-fit:cover;" alt="${event.name || 'Event'}">
                    <div style="position:absolute; inset:0; background:linear-gradient(to top, rgba(7,16,28,0.95), transparent 60%);"></div>
                    <div style="position:absolute; bottom:8px; left:10px; right:10px;">
                        <span style="font-size:8px; font-weight:900; color:#b8ff00; text-transform:uppercase; letter-spacing:0.1em;">${raceType}</span>
                        <div style="font-size:12px; font-weight:900; color:#fff; text-transform:uppercase; line-height:1.1; margin-top:2px;">${event.name}</div>
                    </div>
                </div>
                <div style="padding:10px 12px; font-size:10px; color:rgba(255,255,255,0.75);">
                    <div style="display:flex; align-items:center; gap:6px; margin-bottom:4px;">
                        <i class="fa-solid fa-calendar text-[#b8ff00] text-[9px]"></i>
                        <span>${dateStr}</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; margin-bottom:8px;">
                        <i class="fa-solid fa-location-dot text-[#b8ff00] text-[9px]"></i>
                        <span>${locStr}</span>
                    </div>
                    ${distancesHtml ? `<div style="display:flex; flex-wrap:wrap; gap:4px; margin-bottom:10px;">${distancesHtml}</div>` : ''}
                    <a href="${event.public_url || '#'}"
                       style="display:block; text-align:center; padding:7px; background:#b8ff00; color:#07101c; font-weight:900; text-transform:uppercase; font-size:9px; border-radius:2px; text-decoration:none; letter-spacing:0.05em;">
                        Lihat Detail Event &rarr;
                    </a>
                </div>
            </div>
        `;
    }

    function renderMapMarkers(events, shouldFitBounds = false) {
        if (!eventsExplorerMap) return;

        // Clear existing markers
        explorerMarkers.forEach(m => m.remove());
        explorerMarkers = [];

        let filtered = events;
        if (activeTypeFilter) {
            filtered = events.filter(e => String(e.race_type_id) === String(activeTypeFilter));
        }

        const bounds = new mapboxgl.LngLatBounds();
        let validCount = 0;

        filtered.forEach(event => {
            const lat = parseFloat(event.lat);
            const lng = parseFloat(event.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const isFeatured = !!event.is_featured;
            const el = document.createElement('div');
            el.className = 'custom-event-pin';
            el.innerHTML = `
                <div style="
                    width: 28px; height: 28px;
                    border-radius: 9999px;
                    background: ${isFeatured ? '#b8ff00' : '#0b1522'};
                    border: 2px solid ${isFeatured ? '#07101c' : '#b8ff00'};
                    color: ${isFeatured ? '#07101c' : '#b8ff00'};
                    display: flex; align-items: center; justify-content: center;
                    font-size: 11px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.6);
                    cursor: pointer;
                ">
                    <i class="fa-solid fa-person-running"></i>
                </div>
            `;

            const popup = new mapboxgl.Popup({ offset: 18, closeButton: true })
                .setHTML(buildEventPopupHtml(event));

            const marker = new mapboxgl.Marker({ element: el })
                .setLngLat([lng, lat])
                .setPopup(popup)
                .addTo(eventsExplorerMap);

            explorerMarkers.push(marker);
            bounds.extend([lng, lat]);
            validCount++;
        });

        if (shouldFitBounds && validCount > 0 && eventsExplorerMap) {
            try {
                eventsExplorerMap.fitBounds(bounds, { padding: 50, maxZoom: 12 });
            } catch (e) {}
        }
    }

    // Map Layer Switcher
    window.toggleEventsMapLayerMenu = function () {
        const menu = document.getElementById('events-map-layer-menu');
        if (menu) menu.classList.toggle('hidden');
    };

    window.setEventsMapLayer = function (layerKey) {
        if (!mapStyles[layerKey] || !eventsExplorerMap) return;
        activeLayerKey = layerKey;

        eventsExplorerMap.setStyle(mapStyles[layerKey]);
        eventsExplorerMap.once('style.load', function () {
            renderMapMarkers(currentMapEvents, false);
        });

        const label = document.getElementById('label-events-active-layer');
        if (label) {
            const labels = { streets: 'Streets', outdoors: 'Outdoors Terrain', dark: 'Dark Tactical', satellite: 'Satelit Mapbox' };
            label.textContent = labels[layerKey] || layerKey;
        }

        // Update active check icon in layer menu
        document.querySelectorAll('.layer-check-icon').forEach(icon => {
            if (icon.getAttribute('data-layer') === layerKey) {
                icon.classList.remove('hidden');
            } else {
                icon.classList.add('hidden');
            }
        });

        const menu = document.getElementById('events-map-layer-menu');
        if (menu) menu.classList.add('hidden');
    };

    // Close layer menu when clicked outside
    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('events-map-layer-dropdown-wrap');
        const btn = document.getElementById('btn-toggle-events-map-layer');
        const menu = document.getElementById('events-map-layer-menu');
        if (menu && !menu.classList.contains('hidden')) {
            if (wrap && !wrap.contains(e.target) && btn && !btn.contains(e.target)) {
                menu.classList.add('hidden');
            }
        }
    });

    // Map Type Filter Pills
    window.setEventsMapTypeFilter = function (typeId) {
        activeTypeFilter = typeId ? String(typeId) : '';
        document.querySelectorAll('.btn-event-map-pill').forEach(btn => {
            const btnType = btn.getAttribute('data-map-type') || '';
            if (btnType === activeTypeFilter) {
                btn.classList.add('is-active');
            } else {
                btn.classList.remove('is-active');
            }
        });
        renderMapMarkers(currentMapEvents, true);
    };

    // Recenter
    const btnRecenter = document.getElementById('btn-events-map-recenter');
    if (btnRecenter) {
        btnRecenter.addEventListener('click', function (e) {
            e.stopPropagation();
            if (eventsExplorerMap && explorerMarkers.length > 0) {
                try {
                    const bounds = new mapboxgl.LngLatBounds();
                    explorerMarkers.forEach(m => bounds.extend(m.getLngLat()));
                    eventsExplorerMap.fitBounds(bounds, { padding: 50, maxZoom: 12 });
                } catch (err) {}
            }
        });
    }

    // Locate Me
    const btnLocateMe = document.getElementById('btn-events-map-locate-me');
    if (btnLocateMe) {
        btnLocateMe.addEventListener('click', function (e) {
            e.stopPropagation();
            if (!navigator.geolocation) {
                alert('Geolokasi tidak didukung oleh browser Anda.');
                return;
            }
            btnLocateMe.innerHTML = '<i class="fas fa-spinner fa-spin text-[#B8FF00]"></i> Mencari...';
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    btnLocateMe.innerHTML = '<i class="fas fa-location-crosshairs text-[#B8FF00]"></i> Lokasi Saya';
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    if (eventsExplorerMap) {
                        eventsExplorerMap.flyTo({ center: [lng, lat], zoom: 12 });
                        if (userLocationMarker) userLocationMarker.remove();

                        const el = document.createElement('div');
                        el.style.width = '16px';
                        el.style.height = '16px';
                        el.style.borderRadius = '9999px';
                        el.style.background = '#3b82f6';
                        el.style.border = '3px solid #fff';
                        el.style.boxShadow = '0 0 10px rgba(59,130,246,0.8)';

                        userLocationMarker = new mapboxgl.Marker({ element: el })
                            .setLngLat([lng, lat])
                            .addTo(eventsExplorerMap);
                    }
                },
                function () {
                    btnLocateMe.innerHTML = '<i class="fas fa-location-crosshairs text-[#B8FF00]"></i> Lokasi Saya';
                    alert('Tidak dapat mengakses lokasi Anda. Pastikan izin lokasi aktif.');
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        });
    }

    // Collapse / Expand Map Toggle
    const btnMinimize = document.getElementById('btn-events-map-minimize-toggle');
    const mapCollapseWrap = document.getElementById('events-map-collapse-wrap');
    const labelMinimize = document.getElementById('label-events-map-toggle');
    const iconMinimize = document.getElementById('icon-events-map-toggle');

    if (btnMinimize && mapCollapseWrap) {
        btnMinimize.addEventListener('click', function (e) {
            e.stopPropagation();
            if (mapCollapseWrap.classList.contains('hidden')) {
                mapCollapseWrap.classList.remove('hidden');
                if (labelMinimize) labelMinimize.textContent = 'Sembunyikan';
                if (iconMinimize) iconMinimize.className = 'fas fa-chevron-up text-[8px]';
                setTimeout(() => {
                    if (eventsExplorerMap) eventsExplorerMap.resize();
                }, 150);
            } else {
                mapCollapseWrap.classList.add('hidden');
                if (labelMinimize) labelMinimize.textContent = 'Tampilkan';
                if (iconMinimize) iconMinimize.className = 'fas fa-chevron-down text-[8px]';
            }
        });
    }

    // Initialize the Explorer Map immediately
    initEventsExplorerMap();

    // ----------------------------------------------------
    // 2. LIVE AJAX FILTERING ENGINE
    // ----------------------------------------------------
    const filterForm = document.getElementById('filter-form');
    const eventsContainer = document.getElementById('events-container');
    const paginationContainer = document.getElementById('pagination-container');
    const loadingIndicator = document.getElementById('loading-indicator');
    const mapCountEl = document.getElementById('events-map-count');
    let searchDebounceTimer = null;

    function fetchEvents(url) {
        if (!eventsContainer) return;
        if (loadingIndicator) loadingIndicator.classList.remove('hidden');
        eventsContainer.style.opacity = '0.35';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (loadingIndicator) loadingIndicator.classList.add('hidden');
            eventsContainer.style.opacity = '1';

            if (data.html) {
                eventsContainer.innerHTML = data.html;
            }

            if (paginationContainer) {
                paginationContainer.innerHTML = data.pagination || '';
            }

            if (mapCountEl && data.total !== undefined) {
                mapCountEl.textContent = `${data.total} EVENT`;
            }

            if (Array.isArray(data.mapEvents)) {
                currentMapEvents = data.mapEvents;
                renderMapMarkers(currentMapEvents, false);
            }

            window.history.replaceState(null, '', url);
        })
        .catch(err => {
            console.error('Fetch events error:', err);
            if (loadingIndicator) loadingIndicator.classList.add('hidden');
            eventsContainer.style.opacity = '1';
        });
    }

    function triggerFilter() {
        if (!filterForm) return;
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            if (value !== '') {
                params.append(key, value);
            }
        }

        const baseUrl = window.location.pathname;
        const targetUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
        fetchEvents(targetUrl);
    }

    if (filterForm) {
        // Debounce on text input
        const searchInput = filterForm.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(triggerFilter, 350);
            });
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    triggerFilter();
                }
            });
        }

        // Change event on selects
        filterForm.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', function () {
                triggerFilter();
            });
        });
    }

    // Quick Filter Buttons (Distance & Race Type)
    document.querySelectorAll('.quick-filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const filterType = this.getAttribute('data-filter-type');
            const filterVal = this.getAttribute('data-value') || '';

            // Update active state in group
            const siblingGroup = Array.from(this.parentElement.querySelectorAll(`.quick-filter-btn[data-filter-type="${filterType}"]`));
            siblingGroup.forEach(b => b.classList.remove('is-active'));
            this.classList.add('is-active');

            if (filterForm) {
                if (filterType === 'distance') {
                    const sel = filterForm.querySelector('select[name="race_distance_id"]');
                    if (sel) sel.value = filterVal;
                } else if (filterType === 'type') {
                    const sel = filterForm.querySelector('select[name="race_type_id"]');
                    if (sel) sel.value = filterVal;
                }
                triggerFilter();
            }
        });
    });

    // Pagination Link Clicks Delegation
    if (paginationContainer) {
        paginationContainer.addEventListener('click', function (e) {
            const link = e.target.closest('a');
            if (link && link.href) {
                e.preventDefault();
                fetchEvents(link.href);
                const listSection = document.querySelector('.ep-list-head');
                if (listSection) {
                    listSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    }

    // ----------------------------------------------------
    // VIEW SWITCHER (LIST vs CARD)
    // ----------------------------------------------------
    window.setEventsView = function (mode) {
        const eventsContainer = document.getElementById('events-container');
        const btnList = document.getElementById('btn-view-list');
        const btnCard = document.getElementById('btn-view-card');

        if (mode === 'card') {
            if (eventsContainer) eventsContainer.classList.add('view-card');
            if (btnCard) {
                btnCard.classList.add('is-active');
                btnCard.setAttribute('aria-pressed', 'true');
            }
            if (btnList) {
                btnList.classList.remove('is-active');
                btnList.setAttribute('aria-pressed', 'false');
            }
            try {
                localStorage.setItem('ruanglari_events_view', 'card');
            } catch (e) {}
        } else {
            if (eventsContainer) eventsContainer.classList.remove('view-card');
            if (btnList) {
                btnList.classList.add('is-active');
                btnList.setAttribute('aria-pressed', 'true');
            }
            if (btnCard) {
                btnCard.classList.remove('is-active');
                btnCard.setAttribute('aria-pressed', 'false');
            }
            try {
                localStorage.setItem('ruanglari_events_view', 'list');
            } catch (e) {}
        }
    };

    // Load saved view preference
    try {
        const savedEventsView = localStorage.getItem('ruanglari_events_view') || 'list';
        setEventsView(savedEventsView);
    } catch (e) {}

    // ----------------------------------------------------
    // 3. SUBMIT EVENT MODAL & MINI MAP (MAPBOX GL)
    // ----------------------------------------------------
    const submitModal = document.getElementById('submit-event-modal');
    const btnOpenSubmit = document.getElementById('btn-open-submit-event');
    const btnCloseSubmit = document.getElementById('btn-close-submit-event');
    const btnCancelSubmit = document.getElementById('btn-submit-event-cancel');
    let modalMap = null;
    let modalMarker = null;

    function openSubmitModal() {
        if (!submitModal) return;
        submitModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            initSubmitModalMap();
        }, 200);
    }

    function closeSubmitModal() {
        if (!submitModal) return;
        submitModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    if (btnOpenSubmit) btnOpenSubmit.addEventListener('click', openSubmitModal);
    if (btnCloseSubmit) btnCloseSubmit.addEventListener('click', closeSubmitModal);
    if (btnCancelSubmit) btnCancelSubmit.addEventListener('click', closeSubmitModal);

    function initSubmitModalMap() {
        const container = document.getElementById('event-map');
        if (!container || typeof mapboxgl === 'undefined' || !mapboxToken) return;

        if (modalMap) {
            modalMap.resize();
            return;
        }

        const defaultLat = -6.2088;
        const defaultLng = 106.8456; // Jakarta

        modalMap = new mapboxgl.Map({
            container: 'event-map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [defaultLng, defaultLat],
            zoom: 13
        });

        modalMap.addControl(new mapboxgl.NavigationControl({ showCompass: false }), 'top-right');

        modalMarker = new mapboxgl.Marker({
            draggable: true,
            color: '#b8ff00'
        })
        .setLngLat([defaultLng, defaultLat])
        .addTo(modalMap);

        modalMarker.on('dragend', function () {
            const pos = modalMarker.getLngLat();
            reverseGeocode(pos.lat, pos.lng);
        });

        modalMap.on('click', function (e) {
            modalMarker.setLngLat(e.lngLat);
            reverseGeocode(e.lngLat.lat, e.lngLat.lng);
        });
    }

    function reverseGeocode(lat, lng) {
        const statusEl = document.getElementById('map-geocoding-status');
        if (statusEl) statusEl.textContent = 'Mendapatkan alamat...';

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`)
            .then(res => res.json())
            .then(data => {
                if (statusEl) statusEl.textContent = '';
                if (data && data.display_name) {
                    const addrInput = document.getElementById('submit_event_address');
                    const locInput = document.getElementById('submit_event_location');
                    if (addrInput && !addrInput.value) addrInput.value = data.display_name;
                    if (locInput && !locInput.value && data.name) locInput.value = data.name;
                }
            })
            .catch(() => {
                if (statusEl) statusEl.textContent = '';
            });
    }

    // Modal Map Geolocation Button
    const btnModalGeo = document.getElementById('btn-geolocation');
    if (btnModalGeo) {
        btnModalGeo.addEventListener('click', function () {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(pos => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                if (modalMap) {
                    modalMap.flyTo({ center: [lng, lat], zoom: 15 });
                    if (modalMarker) modalMarker.setLngLat([lng, lat]);
                    reverseGeocode(lat, lng);
                }
            });
        });
    }

    // Modal Location Search
    const searchMapInput = document.getElementById('submit_map_search_input');
    const searchMapResults = document.getElementById('map-search-results');
    let nominatimTimer = null;

    if (searchMapInput && searchMapResults) {
        searchMapInput.addEventListener('input', function () {
            clearTimeout(nominatimTimer);
            const q = this.value.trim();
            if (q.length < 3) {
                searchMapResults.classList.add('hidden');
                return;
            }

            nominatimTimer = setTimeout(() => {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=id&limit=5`)
                    .then(res => res.json())
                    .then(data => {
                        if (!Array.isArray(data) || !data.length) {
                            searchMapResults.classList.add('hidden');
                            return;
                        }

                        searchMapResults.innerHTML = data.map(item => `
                            <div class="p-2 border-b border-white/10 text-[10px] text-white/80 hover:bg-white/10 cursor-pointer"
                                 data-lat="${item.lat}" data-lng="${item.lon}" data-name="${item.display_name}">
                                ${item.display_name}
                            </div>
                        `).join('');
                        searchMapResults.classList.remove('hidden');
                    });
            }, 400);
        });

        searchMapResults.addEventListener('click', function (e) {
            const item = e.target.closest('[data-lat]');
            if (item) {
                const lat = parseFloat(item.getAttribute('data-lat'));
                const lng = parseFloat(item.getAttribute('data-lng'));
                const name = item.getAttribute('data-name');

                if (modalMap) {
                    modalMap.flyTo({ center: [lng, lat], zoom: 15 });
                    if (modalMarker) modalMarker.setLngLat([lng, lat]);
                }
                const locInput = document.getElementById('submit_event_location');
                if (locInput && !locInput.value) locInput.value = name.split(',')[0];
                const addrInput = document.getElementById('submit_event_address');
                if (addrInput) addrInput.value = name;

                searchMapResults.classList.add('hidden');
            }
        });
    }

    // Banner Dropzone & Upload Preview
    const bannerInput = document.getElementById('submit_event_banner');
    const bannerDropzone = document.getElementById('banner-dropzone');
    const bannerDefault = document.getElementById('banner-dropzone-default');
    const bannerPreview = document.getElementById('banner-dropzone-preview');
    const bannerPreviewImg = document.getElementById('banner-preview-img');
    const bannerFilename = document.getElementById('banner-filename');
    const btnRemoveBanner = document.getElementById('btn-remove-banner');

    if (bannerDropzone && bannerInput) {
        bannerDropzone.addEventListener('click', () => bannerInput.click());
        bannerInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (bannerPreviewImg) bannerPreviewImg.src = e.target.result;
                    if (bannerFilename) bannerFilename.textContent = file.name;
                    if (bannerDefault) bannerDefault.classList.add('hidden');
                    if (bannerPreview) bannerPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (btnRemoveBanner && bannerInput) {
        btnRemoveBanner.addEventListener('click', function (e) {
            e.stopPropagation();
            bannerInput.value = '';
            if (bannerDefault) bannerDefault.classList.remove('hidden');
            if (bannerPreview) bannerPreview.classList.add('hidden');
        });
    }

    // OTP Send & Form Submission
    const btnSendOtp = document.getElementById('btn-submit-event-send-otp');
    const submitForm = document.getElementById('submit-event-form');
    const btnSubmitForm = document.getElementById('btn-submit-event-submit');
    const submitAlert = document.getElementById('submit-event-alert');

    if (btnSendOtp) {
        btnSendOtp.addEventListener('click', function () {
            const emailInput = document.getElementById('submit_event_contributor_email');
            const email = emailInput ? emailInput.value.trim() : '';
            if (!email) {
                alert('Silakan masukkan email terlebih dahulu.');
                if (emailInput) emailInput.focus();
                return;
            }

            btnSendOtp.disabled = true;
            btnSendOtp.textContent = 'Mengirim...';

            fetch('{{ route("events.submissions.request-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const otpIdInput = document.getElementById('submit_event_otp_id');
                    if (otpIdInput && data.otp_id) otpIdInput.value = data.otp_id;
                    alert('Kode OTP telah dikirim ke email kamu. Silakan periksa inbox/spam.');
                    
                    let count = 60;
                    const timer = setInterval(() => {
                        count--;
                        btnSendOtp.textContent = `Kirim Ulang (${count}s)`;
                        if (count <= 0) {
                            clearInterval(timer);
                            btnSendOtp.disabled = false;
                            btnSendOtp.textContent = 'Kirim OTP';
                        }
                    }, 1000);
                } else {
                    btnSendOtp.disabled = false;
                    btnSendOtp.textContent = 'Kirim OTP';
                    alert(data.message || 'Gagal mengirim OTP.');
                }
            })
            .catch(() => {
                btnSendOtp.disabled = false;
                btnSendOtp.textContent = 'Kirim OTP';
                alert('Terjadi kesalahan jaringan.');
            });
        });
    }

    if (btnSubmitForm && submitForm) {
        btnSubmitForm.addEventListener('click', function () {
            submitForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        });

        submitForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(submitForm);

            if (modalMarker) {
                const pos = modalMarker.getLngLat();
                formData.append('latitude', pos.lat);
                formData.append('longitude', pos.lng);
            }

            btnSubmitForm.disabled = true;
            btnSubmitForm.textContent = 'Menyimpan...';

            fetch('{{ route("events.submissions.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btnSubmitForm.disabled = false;
                btnSubmitForm.textContent = 'Submit Event';
                if (data.success) {
                    alert('Event berhasil diajukan! Tim kami akan melakukan kurasi dan mempublikasikannya.');
                    closeSubmitModal();
                    submitForm.reset();
                } else {
                    if (submitAlert) {
                        submitAlert.innerHTML = `<div class="p-3 bg-red-950/80 border border-red-600 rounded text-red-200 text-xs">${data.message || 'Terjadi kesalahan.'}</div>`;
                        submitAlert.classList.remove('hidden');
                    } else {
                        alert(data.message || 'Gagal mengajukan event.');
                    }
                }
            })
            .catch(() => {
                btnSubmitForm.disabled = false;
                btnSubmitForm.textContent = 'Submit Event';
                alert('Terjadi kesalahan pengiriman form.');
            });
        });
    }
});
</script>
@endpush

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Jadwal Lari 2026 Indonesia",
  "description": "Kalender event lari Indonesia 2026 berisi jadwal fun run, 5K, 10K, half marathon, marathon, trail run, dan virtual run.",
  "url": "https://ruanglari.com/jadwal-lari",
  "isPartOf": {
    "@type": "WebSite",
    "name": "Ruang Lari",
    "url": "https://ruanglari.com"
  }
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Daftar Jadwal Lari 2026 Indonesia",
  "itemListElement": [
    @foreach($events as $index => $event)
    {
      "@type": "ListItem",
      "position": {{ $index + 1 }},
      "url": "{{ $event->public_url }}",
      "name": "{{ e($event->name) }}"
    }{{ !$loop->last ? ',' : '' }}
    @endforeach
  ]
}
</script>

<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BreadcrumbList",
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
      "name": "Jadwal Lari",
      "item": "https://ruanglari.com/jadwal-lari"
    }
  ]
}
</script>
@endpush
@endsection
