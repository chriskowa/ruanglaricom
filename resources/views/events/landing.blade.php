@php
    $pageSuffix = request()->has('page') && request('page') > 1 ? ' - Halaman ' . request('page') : '';
    $title = 'Jadwal Lari Indonesia 2026: 5K, 10K & Marathon | Ruang Lari' . $pageSuffix;
    $metaDescription = 'Temukan jadwal lari Indonesia 2026 terbaru: fun run, 5K, 10K, half marathon, marathon, trail run, dan virtual run. Cek tanggal, kota, kategori jarak, serta link pendaftaran resmi di Ruang Lari.';

    $canonicalUrl = request()->url();
    if (request()->has('page') && request('page') > 1) {
        $canonicalUrl .= '?page=' . request('page');
    }
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
    #events-page{
        --ep-bg:#07101c;
        --ep-surface:#0b1522;
        --ep-soft:#0f1b2a;
        --ep-line:rgba(255,255,255,.09);
        --ep-line-strong:rgba(255,255,255,.16);
        --ep-white:#fff;
        --ep-secondary:rgba(255,255,255,.74);
        --ep-support:rgba(255,255,255,.54);
        --ep-meta:rgba(255,255,255,.36);
        --ep-accent:#b8ff00;
        --ep-orange:#fc4c02;
        min-height:100vh;
        background:var(--ep-bg);
        color:var(--ep-white);
        font-variant-numeric:tabular-nums;
    }

    #events-page *{box-sizing:border-box}

    #events-page .ep-shell{max-width:1280px;margin:0 auto;padding-left:1rem;padding-right:1rem}

    /* HERO */
    #events-page .ep-hero{
        padding:2rem 0 2.6rem;
        border-bottom:1px solid var(--ep-line);
    }

    #events-page .ep-kicker{
        display:flex;
        align-items:center;
        gap:.7rem;
        color:var(--ep-accent);
        font-size:10px;
        font-weight:900;
        letter-spacing:.18em;
        text-transform:uppercase;
    }

    #events-page .ep-kicker:before{
        content:"";
        width:30px;
        height:2px;
        background:var(--ep-accent);
    }

    #events-page .ep-hero-grid{
        display:grid;
        grid-template-columns:1.05fr .95fr;
        gap:3rem;
        align-items:start;
        margin-top:1rem;
    }

    #events-page .ep-title{
        margin:0;
        color:#fff;
        font-size:clamp(2.8rem,6vw,5.8rem);
        line-height:.9;
        letter-spacing:-.06em;
        font-weight:900;
        text-transform:uppercase;
    }

    #events-page .ep-title em{
        color:var(--ep-accent);
        font-style:normal;
    }

    #events-page .ep-lead{
        max-width:46rem;
        margin-top:1.2rem;
        color:var(--ep-secondary);
        font-size:14px;
        line-height:1.8;
    }

    #events-page .ep-subcopy{
        max-width:40rem;
        margin-top:.8rem;
        color:var(--ep-support);
        font-size:11px;
        line-height:1.65;
    }

    #events-page .ep-actions{
        display:flex;
        flex-wrap:wrap;
        gap:.5rem;
        margin-top:1.4rem;
    }

    #events-page .ep-btn{
        min-height:44px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:.55rem;
        padding:0 1rem;
        border:1px solid var(--ep-line-strong);
        border-radius:3px;
        color:#fff;
        background:transparent;
        font-size:10px;
        font-weight:900;
        letter-spacing:.08em;
        text-transform:uppercase;
        transition:.18s ease;
    }

    #events-page .ep-btn:hover{border-color:rgba(255,255,255,.28);background:rgba(255,255,255,.025)}
    #events-page .ep-btn--primary{background:var(--ep-accent);border-color:var(--ep-accent);color:#07101c}
    #events-page .ep-btn--primary:hover{background:#d2ff65;border-color:#d2ff65}

    /* FEATURED */
    #events-page .ep-featured{
        position:relative;
        overflow:hidden;
        min-height:390px;
        border:1px solid var(--ep-line);
        background:#08111f;
    }

    #events-page .ep-featured-slide{
        position:relative;
        display:block;
        width:100%;
        min-height:390px;
        flex:0 0 100%;
        overflow:hidden;
    }

    #events-page .ep-featured-slide img{
        width:100%;height:100%;min-height:390px;object-fit:cover;transition:transform .7s ease;
    }

    #events-page .ep-featured-slide:hover img{transform:scale(1.025)}
    #events-page .ep-featured-slide:after{
        content:"";
        position:absolute;inset:0;
        background:linear-gradient(to top,rgba(3,7,14,.96) 0%,rgba(3,7,14,.45) 48%,rgba(3,7,14,.06) 78%);
    }

    #events-page .ep-featured-copy{
        position:absolute;
        left:0;right:0;bottom:0;
        z-index:2;
        padding:1.25rem;
    }

    #events-page .ep-featured-meta{
        display:flex;
        flex-wrap:wrap;
        gap:.7rem;
        align-items:center;
        color:rgba(255,255,255,.6);
        font-size:9px;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.08em;
    }

    #events-page .ep-featured-name{
        margin-top:.45rem;
        color:#fff;
        font-size:clamp(1.2rem,2.4vw,2rem);
        line-height:1.05;
        letter-spacing:-.035em;
        font-weight:900;
        text-transform:uppercase;
    }

    #events-page .ep-slider-nav{
        position:absolute;
        top:1rem;
        right:1rem;
        z-index:3;
        display:flex;
        gap:.35rem;
    }

    #events-page .ep-slider-nav button{
        width:34px;height:34px;
        display:flex;align-items:center;justify-content:center;
        border:1px solid rgba(255,255,255,.18);
        border-radius:2px;
        background:rgba(7,16,28,.78);
        color:#fff;
    }

    /* OPERATIONS STRIP */
    #events-page .ep-ops{
        display:grid;
        grid-template-columns:1fr auto;
        gap:2rem;
        align-items:center;
        padding:1.2rem 0;
        border-bottom:1px solid var(--ep-line);
    }

    #events-page .ep-ops-label{
        color:var(--ep-accent);
        font-size:9px;
        font-weight:900;
        letter-spacing:.15em;
        text-transform:uppercase;
    }

    #events-page .ep-ops h2{
        margin:.35rem 0 0;
        font-size:20px;
        line-height:1.2;
        font-weight:900;
        letter-spacing:-.025em;
        text-transform:uppercase;
    }

    #events-page .ep-ops p{
        margin:.35rem 0 0;
        max-width:52rem;
        color:var(--ep-secondary);
        font-size:11px;
        line-height:1.6;
    }

    /* MAP */
    #events-page .ep-section{padding:2rem 0 0}
    #events-page .ep-section-head{
        display:flex;
        align-items:end;
        justify-content:space-between;
        gap:1rem;
        margin-bottom:.8rem;
    }

    #events-page .ep-eyebrow{
        color:var(--ep-meta);
        font-size:9px;
        font-weight:900;
        letter-spacing:.15em;
        text-transform:uppercase;
    }

    #events-page .ep-heading{
        margin:.35rem 0 0;
        color:#fff;
        font-size:clamp(1.4rem,3vw,2.2rem);
        line-height:1.05;
        letter-spacing:-.035em;
        font-weight:900;
        text-transform:uppercase;
    }

    #events-page .ep-section-copy{
        margin-top:.4rem;
        color:var(--ep-support);
        font-size:11px;
        line-height:1.6;
    }

    #events-page .ep-map-shell{
        border:1px solid var(--ep-line);
        background:#08111f;
    }

    #events-page .ep-map-toolbar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:.7rem .85rem;
        border-bottom:1px solid var(--ep-line);
        background:#08111f;
    }

    #events-page .ep-map-tools{display:flex;align-items:center;gap:.35rem;flex-wrap:wrap}
    #events-page .ep-tool-btn{
        min-height:34px;
        padding:0 .7rem;
        display:inline-flex;align-items:center;gap:.45rem;
        border:1px solid var(--ep-line);
        border-radius:2px;
        color:var(--ep-secondary);
        background:transparent;
        font-size:9px;
        font-weight:800;
    }

    #events-page .ep-tool-btn:hover{color:#fff;border-color:var(--ep-line-strong)}
    #events-page #events-explorer-map{height:480px;background:#08111f}

    #events-page .ep-map-filter{
        display:flex;
        gap:.35rem;
        overflow-x:auto;
        padding:.65rem .75rem;
        border-top:1px solid var(--ep-line);
        scrollbar-width:none;
    }
    #events-page .ep-map-filter::-webkit-scrollbar{display:none}

    /* FILTERS */
    #events-page .ep-filter-shell{
        margin-top:1rem;
        border-top:1px solid var(--ep-line);
        border-bottom:1px solid var(--ep-line);
        padding:.9rem 0;
    }

    #events-page #filter-form{
        display:grid;
        grid-template-columns:1.25fr repeat(4,1fr);
        gap:.55rem;
    }

    #events-page label{
        display:block;
        margin-bottom:.35rem;
        color:rgba(255,255,255,.52);
        font-size:9px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.08em;
    }

    #events-page input:not([type=checkbox]):not([type=file]),
    #events-page select,
    #events-page textarea{
        width:100%;
        border:1px solid var(--ep-line);
        border-radius:2px;
        background:rgba(2,6,13,.45);
        color:#fff;
        outline:none;
        box-shadow:none;
    }

    #events-page input:not([type=checkbox]):not([type=file]),
    #events-page select{height:42px;padding:0 .75rem;font-size:11px}
    #events-page textarea{padding:.7rem .75rem;font-size:11px}

    #events-page input::placeholder{color:rgba(255,255,255,.28)}
    #events-page input:focus,#events-page select:focus,#events-page textarea:focus{
        border-color:rgba(184,255,0,.5);
        box-shadow:0 0 0 3px rgba(184,255,0,.04);
    }

    #events-page .ep-quick{
        display:flex;
        gap:.35rem;
        overflow-x:auto;
        padding:.7rem 0 0;
        scrollbar-width:none;
    }
    #events-page .ep-quick::-webkit-scrollbar{display:none}
    #events-page .quick-filter-btn,
    #events-page .btn-event-map-pill{
        flex:0 0 auto;
        min-height:32px;
        padding:0 .65rem;
        border:1px solid var(--ep-line);
        border-radius:2px;
        background:transparent;
        color:var(--ep-support);
        font-size:9px;
        font-weight:900;
        letter-spacing:.06em;
        text-transform:uppercase;
    }

    #events-page .quick-filter-btn.is-active,
    #events-page .btn-event-map-pill.is-active{
        background:var(--ep-accent);
        border-color:var(--ep-accent);
        color:#07101c;
    }

    /* EVENT LIST */
    #events-page .ep-list-head{
        display:flex;align-items:end;justify-content:space-between;gap:1rem;
        padding-top:2.2rem;margin-bottom:.9rem;
    }

    #events-page #events-container{
        border-top:1px solid var(--ep-line);
    }

    /* aggressively normalize event partial cards */
    #events-page #events-container > *{
        border-radius:0 !important;
        box-shadow:none !important;
    }

    /* SEO */
    #events-page .ep-seo{
        margin-top:4rem;
        padding-top:2.5rem;
        border-top:1px solid var(--ep-line);
    }

    #events-page .ep-directory{
        display:grid;
        grid-template-columns:repeat(6,minmax(0,1fr));
        border-top:1px solid var(--ep-line);
        border-left:1px solid var(--ep-line);
        margin-top:.9rem;
    }

    #events-page .ep-directory a{
        min-height:72px;
        display:flex;
        align-items:center;
        padding:.7rem;
        border-right:1px solid var(--ep-line);
        border-bottom:1px solid var(--ep-line);
        color:var(--ep-secondary);
        font-size:10px;
        font-weight:800;
        line-height:1.4;
        text-transform:uppercase;
        transition:.18s ease;
    }

    #events-page .ep-directory a:hover{color:var(--ep-accent);background:rgba(255,255,255,.015)}

    #events-page .ep-copy-panel{
        margin-top:2.5rem;
        padding:1.2rem 0;
        border-top:1px solid var(--ep-line);
        border-bottom:1px solid var(--ep-line);
    }

    #events-page .ep-copy-panel p{
        color:var(--ep-secondary);
        font-size:12px;
        line-height:1.8;
    }

    #events-page .ep-faq{
        margin-top:2.5rem;
        border-top:1px solid var(--ep-line);
    }

    #events-page .ep-faq-item{border-bottom:1px solid var(--ep-line)}
    #events-page .ep-faq-item button{
        width:100%;
        min-height:58px;
        display:flex;align-items:center;justify-content:space-between;gap:1rem;
        color:#fff;background:transparent;text-align:left;
        font-size:12px;font-weight:800;
    }

    #events-page .ep-faq-answer{
        padding:0 0 1rem;
        color:var(--ep-secondary);
        font-size:11px;
        line-height:1.7;
    }

    /* MODAL SUBMIT EVENT */
    #submit-event-modal .ep-modal{
        width:100%;max-width:1080px;max-height:92vh;
        display:flex;flex-direction:column;
        border:1px solid rgba(255,255,255,.18);
        border-radius:8px;
        background:#08111f;
        color:#fff;
        box-shadow:0 25px 50px -12px rgba(0,0,0,.75);
        overflow:hidden;
    }
    #submit-event-modal .ep-modal-head{
        flex:0 0 auto;
        display:flex;align-items:center;justify-content:space-between;gap:1rem;
        padding:1.1rem 1.5rem;
        border-bottom:1px solid rgba(255,255,255,.12);
        background:#060d18;
    }
    #submit-event-modal .ep-modal-foot{
        flex:0 0 auto;
        display:flex;align-items:center;justify-content:space-between;gap:1rem;
        padding:1rem 1.5rem;
        border-top:1px solid rgba(255,255,255,.14);
        border-bottom:0;
        background:#060d18;
        z-index:10;
    }
    #submit-event-modal .ep-modal-body{
        flex:1 1 auto;
        min-height:0;
        overflow-y:auto;
        padding:1.25rem 1.5rem;
        background:#08111f;
    }

    #submit-event-modal .ep-btn{
        min-height:42px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:.55rem;
        padding:0 1.25rem;
        border:1px solid rgba(255,255,255,.22);
        border-radius:6px;
        color:#F8FAFC;
        background:#111f32;
        font-size:11px;
        font-weight:800;
        letter-spacing:.08em;
        text-transform:uppercase;
        cursor:pointer;
        transition:all .15s ease;
        text-decoration:none;
        box-sizing:border-box;
    }
    #submit-event-modal .ep-btn:hover{
        background:#172a44;
        border-color:rgba(255,255,255,.4);
        color:#FFFFFF;
    }
    #submit-event-modal .ep-btn--primary{
        background:#B8FF00!important;
        border-color:#B8FF00!important;
        color:#05080E!important;
        font-weight:900!important;
        box-shadow:0 4px 14px rgba(184,255,0,.25);
    }
    #submit-event-modal .ep-btn--primary:hover{
        background:#CBFF4A!important;
        border-color:#CBFF4A!important;
        box-shadow:0 6px 18px rgba(184,255,0,.35);
    }
    #submit-event-modal .ep-btn:disabled,
    #submit-event-modal .ep-btn[disabled]{
        opacity:0.6;
        cursor:not-allowed;
        box-shadow:none;
    }

    @media (max-width: 640px) {
        #submit-event-modal .ep-modal {
            max-height: 96vh;
            border-radius: 6px;
        }
        #submit-event-modal .ep-modal-head,
        #submit-event-modal .ep-modal-body {
            padding: 1rem;
        }
        #submit-event-modal .ep-modal-foot {
            flex-direction: column-reverse;
            align-items: stretch;
            gap: 0.75rem;
            padding: 0.85rem 1rem;
        }
        #submit-event-modal .ep-modal-foot .flex {
            width: 100%;
        }
        #submit-event-modal .ep-modal-foot button {
            flex: 1;
        }
    }
    #submit-event-modal .ep-form-section{
        padding:1.1rem;
        border:1px solid rgba(255,255,255,.1);
        border-radius:6px;
        background:#0c1827;
    }
    #submit-event-modal .ep-form-section-title{
        padding-bottom:.6rem;margin-bottom:.85rem;border-bottom:1px solid rgba(255,255,255,.08);
        color:#b8ff00;font-size:10px;font-weight:900;letter-spacing:.14em;text-transform:uppercase;
    }
    #submit-event-modal label{
        display:block;
        font-size:11px;
        font-weight:700;
        color:#cbd5e1;
        margin-bottom:5px;
        letter-spacing:.02em;
    }
    #submit-event-modal input[type="text"],
    #submit-event-modal input[type="date"],
    #submit-event-modal input[type="time"],
    #submit-event-modal input[type="email"],
    #submit-event-modal select,
    #submit-event-modal textarea{
        width:100%;
        height:40px;
        padding:8px 12px;
        background:#111f32;
        border:1px solid rgba(255,255,255,.18);
        border-radius:6px;
        color:#ffffff;
        font-size:13px;
        font-family:inherit;
        outline:none;
        box-sizing:border-box;
        transition:border-color .15s ease, background .15s ease, box-shadow .15s ease;
        color-scheme:dark;
    }
    #submit-event-modal textarea{
        height:auto;
        min-height:70px;
        resize:vertical;
    }
    #submit-event-modal input[type="text"]:focus,
    #submit-event-modal input[type="date"]:focus,
    #submit-event-modal input[type="time"]:focus,
    #submit-event-modal input[type="email"]:focus,
    #submit-event-modal select:focus,
    #submit-event-modal textarea:focus{
        border-color:#b8ff00;
        background:#14253c;
        box-shadow:0 0 0 1px #b8ff00;
    }
    #submit-event-modal input::placeholder,
    #submit-event-modal textarea::placeholder{
        color:rgba(255,255,255,.38);
    }
    #submit-event-modal select option{
        background:#0f1c2e;
        color:#ffffff;
    }
    #submit-event-modal .race-distance-cb{
        width:16px;
        height:16px;
        accent-color:#b8ff00;
        cursor:pointer;
    }
    #submit-event-modal .ep-form-section label.race-distance-item{
        display:flex;
        align-items:center;
        gap:8px;
        border:1px solid rgba(255,255,255,.14);
        background:#111f32;
        border-radius:6px;
        padding:8px 10px;
        color:#e2e8f0;
        font-size:12px;
        font-weight:600;
        cursor:pointer;
        transition:border-color .15s ease, background .15s ease;
        margin-bottom:0!important;
        user-select:none;
    }
    #submit-event-modal .ep-form-section label.race-distance-item:hover{
        border-color:rgba(184,255,0,.4);
        background:#14253c;
    }
    #submit-event-modal #banner-dropzone{
        border:1px dashed rgba(255,255,255,.22);
        border-radius:6px;
        background:#111f32;
        padding:1.25rem;
        text-align:center;
        cursor:pointer;
        transition:border-color .15s ease, background .15s ease;
    }
    #submit-event-modal #banner-dropzone:hover{
        border-color:#b8ff00;
        background:#14253c;
    }
    #submit-event-modal #map-search-results{
        background:#111f32;
        border:1px solid rgba(255,255,255,.2);
        border-radius:6px;
        max-height:180px;
        overflow-y:auto;
        color:#ffffff;
        font-size:12px;
        margin-top:4px;
        box-shadow:0 10px 25px rgba(0,0,0,.5);
    }
    #submit-event-modal #map-search-results div{
        padding:8px 12px;
        border-bottom:1px solid rgba(255,255,255,.08);
        cursor:pointer;
        transition:background .12s ease;
    }
    #submit-event-modal #map-search-results div:hover{
        background:#192e4a;
        color:#b8ff00;
    }
    #submit-event-modal #event-map{
        border-radius:6px;
        border:1px solid rgba(255,255,255,.15);
    }

    /* MAPBOX GL CUSTOM STYLING */
    .mapboxgl-ctrl-bottom-right, .mapboxgl-ctrl-bottom-left, .mapboxgl-ctrl-logo, .mapboxgl-ctrl-attrib {
        display: none !important;
    }
    .mapboxgl-ctrl-group {
        background: #07101c !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        border-radius: 4px !important;
        overflow: hidden;
    }
    .mapboxgl-ctrl-group button {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .mapboxgl-ctrl-group button .mapboxgl-ctrl-icon {
        filter: invert(1) brightness(2);
    }
    .mapboxgl-popup {
        max-width: 290px !important;
        z-index: 1000 !important;
    }
    .mapboxgl-popup-content {
        background: #07101c !important;
        color: #fff !important;
        border-radius: 4px !important;
        border: 1px solid rgba(255, 255, 255, 0.16) !important;
        box-shadow: 0 16px 36px rgba(0, 0, 0, 0.7) !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    .mapboxgl-popup-anchor-top .mapboxgl-popup-tip { border-bottom-color: #07101c !important; }
    .mapboxgl-popup-anchor-bottom .mapboxgl-popup-tip { border-top-color: #07101c !important; }
    .mapboxgl-popup-anchor-left .mapboxgl-popup-tip { border-right-color: #07101c !important; }
    .mapboxgl-popup-anchor-right .mapboxgl-popup-tip { border-left-color: #07101c !important; }
    .mapboxgl-popup-close-button {
        color: #fff !important;
        font-size: 16px !important;
        padding: 4px 8px !important;
        background: rgba(0,0,0,0.6) !important;
        border-radius: 0 0 0 4px !important;
        right: 0 !important;
        top: 0 !important;
        z-index: 10 !important;
    }
    .mapboxgl-popup-close-button:hover {
        background: rgba(0,0,0,0.85) !important;
        color: #b8ff00 !important;
    }
    .custom-event-pin {
        cursor: pointer;
        transition: transform 0.15s ease;
    }
    .custom-event-pin:hover {
        transform: scale(1.18);
        z-index: 10;
    }

    @media(max-width:1023px){
        #events-page .ep-hero-grid{grid-template-columns:1fr;gap:1.5rem}
        #events-page .ep-featured,#events-page .ep-featured-slide,#events-page .ep-featured-slide img{min-height:340px}
        #events-page #filter-form{grid-template-columns:repeat(2,minmax(0,1fr))}
        #events-page .ep-directory{grid-template-columns:repeat(3,minmax(0,1fr))}
    }

    @media(max-width:639px){
        #events-page .ep-hero{padding-top:1.2rem}
        #events-page .ep-actions{display:grid;grid-template-columns:1fr}
        #events-page .ep-btn{width:100%}
        #events-page .ep-ops{grid-template-columns:1fr}
        #events-page .ep-map-toolbar{align-items:flex-start;flex-direction:column}
        #events-page #events-explorer-map{height:390px}
        #events-page #filter-form{grid-template-columns:1fr}
        #events-page .ep-directory{grid-template-columns:repeat(2,minmax(0,1fr))}
        #events-page .ep-featured,#events-page .ep-featured-slide,#events-page .ep-featured-slide img{min-height:300px}
    }
</style>
@endpush

@section('content')
<div id="events-page" class="pt-20 pb-16">

    <section class="ep-hero">
        <div class="ep-shell">
            <div class="ep-kicker">RuangLari / Indonesia Race Calendar</div>

            <div class="ep-hero-grid">
                <div>
                    <h1 class="ep-title">
                        Jadwal Lari <em>2026</em> Indonesia
                    </h1>

                    <p class="ep-lead">
                        Temukan jadwal lari 2026 di Indonesia dalam satu kalender lengkap.
                        Mulai dari fun run, 5K, 10K, half marathon, marathon, trail run,
                        ultra run, hingga virtual run.
                    </p>

                    <p class="ep-subcopy">
                        Cek tanggal, kota, kategori jarak, status pendaftaran, dan tautan resmi setiap event.
                        Penyelenggara juga dapat mengajukan event gratis atau membuat registration page sendiri.
                    </p>

                    <div class="ep-actions">
                        <button type="button" id="btn-open-submit-event" class="ep-btn ep-btn--primary">
                            <i class="fas fa-plus text-[9px]"></i>
                            Submit Event Lari Gratis
                        </button>

                        <a href="{{ route('eo.landing') }}" class="ep-btn">
                            Registration Page EO
                            <i class="fas fa-arrow-right text-[8px] text-white/40"></i>
                        </a>
                    </div>
                </div>

                <div>
                    @if(isset($featuredEvents) && $featuredEvents->isNotEmpty())
                        <div
                            class="ep-featured"
                            x-data="{
                                activeIndex: 0,
                                total: {{ $featuredEvents->count() }},
                                timer: null,
                                start(){ this.stop(); this.timer=setInterval(()=>this.next(),5000) },
                                stop(){ if(this.timer) clearInterval(this.timer) },
                                next(){ this.activeIndex=(this.activeIndex+1)%this.total },
                                prev(){ this.activeIndex=(this.activeIndex-1+this.total)%this.total }
                            }"
                            x-init="start()"
                            @mouseenter="stop()"
                            @mouseleave="start()"
                        >
                            <div class="flex transition-transform duration-500 ease-out"
                                 :style="`transform:translateX(-${activeIndex*100}%)`">
                                @foreach($featuredEvents as $event)
                                    <a href="{{ $event->public_url }}" class="ep-featured-slide">
                                        <img src="{{ $event->getHeroImageUrl() ?: asset('images/hero/jadwal-lari.webp') }}"
                                             alt="{{ $event->name }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}">

                                        <div class="ep-featured-copy">
                                            <div class="ep-featured-meta">
                                                <span class="text-[#B8FF00]">Featured Race</span>
                                                @if($event->start_at)
                                                    <span>{{ $event->start_at->translatedFormat('d M Y') }}</span>
                                                @endif
                                                <span>{{ $event->city ? $event->city->name : $event->location_name }}</span>
                                            </div>

                                            <div class="ep-featured-name">{{ $event->name }}</div>

                                            @if($event->distances->isNotEmpty())
                                                <div class="mt-3 flex flex-wrap gap-2 text-[9px] font-mono font-bold text-white/55">
                                                    @foreach($event->distances as $distance)
                                                        <span>{{ $distance->name }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            @if($featuredEvents->count() > 1)
                                <div class="ep-slider-nav">
                                    <button type="button" @click="prev()" aria-label="Previous event">
                                        <i class="fas fa-arrow-left text-[10px]"></i>
                                    </button>
                                    <button type="button" @click="next()" aria-label="Next event">
                                        <i class="fas fa-arrow-right text-[10px]"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="ep-featured">
                            <img src="{{ asset('images/hero/jadwal-lari.webp') }}"
                                 alt="Jadwal Lari 2026 Indonesia"
                                 class="w-full h-full object-cover min-h-[390px]">
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="ep-shell">
        <div class="ep-ops">
            <div>
                <div class="ep-ops-label">For Event Organizers</div>
                <h2>Publikasikan event dan buka pendaftaran tanpa biaya awal.</h2>
                <p>
                    Listing event, registration page, notifikasi WhatsApp, e-ticket QR, dan pembayaran online
                    dalam satu alur yang lebih sederhana.
                </p>
            </div>

            <div class="ep-actions !mt-0">
                <a href="{{ route('eo.landing') }}" class="ep-btn ep-btn--primary">Buat Registration Page</a>
                <button type="button" onclick="document.getElementById('btn-open-submit-event').click()" class="ep-btn">Submit Event</button>
            </div>
        </div>
    </section>

    <section class="ep-section ep-shell">
        <div class="ep-section-head">
            <div>
                <div class="ep-eyebrow">Race geography</div>
                <h2 class="ep-heading">Peta Sebaran Event Lari Indonesia</h2>
                <p class="ep-section-copy">Eksplor event berdasarkan lokasi, race type, dan kota.</p>
            </div>
            <div id="events-map-count" class="text-[10px] font-mono font-black text-[#B8FF00]">
                {{ count($mapEvents ?? []) }} EVENT
            </div>
        </div>

        <div id="events-explorer-map-section" class="ep-map-shell">
            <div class="ep-map-toolbar" id="btn-toggle-events-map">
                <div class="ep-map-tools">
                    <div id="events-map-layer-dropdown-wrap" class="relative inline-block">
                        <button type="button" id="btn-toggle-events-map-layer" class="ep-tool-btn" onclick="toggleEventsMapLayerMenu()">
                            <i class="fas fa-layer-group text-[#B8FF00]"></i>
                            <span id="label-events-active-layer">Streets</span>
                            <i class="fas fa-chevron-down text-[8px] opacity-60 ml-0.5"></i>
                        </button>

                        <div id="events-map-layer-menu" class="hidden absolute left-0 top-full mt-1.5 z-[9999] min-w-[170px] border border-white/15 bg-[#0b1522] rounded-md shadow-2xl overflow-hidden divide-y divide-white/5">
                            @foreach([
                                'streets' => 'Streets (Default)',
                                'outdoors' => 'Outdoors Terrain',
                                'dark' => 'Dark Tactical',
                                'satellite' => 'Satelit Mapbox',
                            ] as $layerKey => $layerLabel)
                                <button type="button"
                                        onclick="setEventsMapLayer('{{ $layerKey }}')"
                                        class="block w-full px-3.5 py-2.5 text-left text-[11px] font-bold text-slate-200 hover:text-white hover:bg-slate-800/80 transition flex items-center justify-between">
                                    <span>{{ $layerLabel }}</span>
                                    <i class="fas fa-check text-[#B8FF00] text-[10px] layer-check-icon {{ $layerKey === 'streets' ? '' : 'hidden' }}" data-layer="{{ $layerKey }}"></i>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <button type="button" id="btn-events-map-locate-me" class="ep-tool-btn">
                        <i class="fas fa-location-crosshairs text-[#B8FF00]"></i>
                        Lokasi Saya
                    </button>
                    <button type="button" id="btn-events-map-recenter" class="ep-tool-btn">
                        <i class="fas fa-expand"></i>
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

                <span class="w-px h-6 bg-white/10 mx-1 shrink-0"></span>

                <button type="button" class="quick-filter-btn" data-filter-type="type" data-value="">Semua Jenis</button>
                @foreach($raceTypes as $type)
                    <button type="button" class="quick-filter-btn" data-filter-type="type" data-value="{{ $type->id }}">{{ $type->name }}</button>
                @endforeach
            </div>
        </div>

        <div class="ep-list-head">
            <div>
                <div class="ep-eyebrow">Race database</div>
                <h2 class="ep-heading">Kalender Event Lari Terbaru</h2>
            </div>
        </div>

        <div id="events-container">
            @include('events.partials.list', ['events' => $events])
        </div>

        <div id="pagination-container" class="mt-8">
            {{ $events->links() }}
        </div>

        <div id="loading-indicator" class="hidden py-14 text-center">
            <div class="inline-block h-7 w-7 border-2 border-white/10 border-t-[#B8FF00] rounded-full animate-spin"></div>
            <p class="mt-2 text-white/55 text-[10px] font-mono uppercase tracking-wider">Memuat jadwal</p>
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
            <div class="ep-eyebrow">Browse by month</div>
            <h2 class="ep-heading">Jadwal Lari Berdasarkan Bulan</h2>

            <div class="ep-directory">
                @foreach($months as $num => $monthName)
                    <a href="{{ route('events.index') }}?month={{ sprintf('%04d-%02d', 2026, $num) }}">
                        Jadwal Lari {{ $monthName }} 2026
                    </a>
                @endforeach
            </div>
        </div>

        <div class="mb-10">
            <div class="ep-eyebrow">Browse by city</div>
            <h2 class="ep-heading">Jadwal Lari Berdasarkan Kota</h2>

            <div class="ep-directory">
                @foreach($seoCities as $slug => $cityName)
                    <a href="/event-lari-di-{{ $slug }}">Event Lari di {{ $cityName }}</a>
                @endforeach
            </div>
        </div>

        <div>
            <div class="ep-eyebrow">Browse by category</div>
            <h2 class="ep-heading">Jadwal Lari Berdasarkan Kategori</h2>

            <div class="ep-directory">
                @foreach($seoCategories as $slug => $label)
                    <a href="/jadwal-{{ $slug }}">{{ $label }}</a>
                @endforeach
            </div>
        </div>

        <div class="ep-copy-panel">
            <div class="ep-eyebrow">Race selection</div>
            <h2 class="ep-heading mb-4">Cara Memilih Event Lari yang Tepat</h2>

            <div class="max-w-4xl space-y-3">
                <p>
                    Sebelum mendaftar event lari, sesuaikan pilihan lomba dengan tingkat kebugaran,
                    target latihan, jarak tempuh, lokasi, dan waktu persiapan. Pelari pemula dapat
                    memulai dari fun run atau 5K, sedangkan pelari berpengalaman dapat memilih
                    10K, half marathon, marathon, atau trail run.
                </p>
                <p>
                    Perhatikan juga cut-off time, karakter rute, elevasi, race pack, hidrasi,
                    fasilitas peserta, dan reputasi penyelenggara agar pengalaman race lebih aman
                    dan sesuai target.
                </p>
            </div>
        </div>

        @php
            $faqs = [
                ['q'=>'Apa itu jadwal lari?','a'=>'Jadwal lari adalah daftar event lari yang disusun berdasarkan tanggal, lokasi, kategori jarak, dan jenis lomba seperti fun run, 5K, 10K, half marathon, marathon, trail run, dan virtual run.'],
                ['q'=>'Bagaimana cara mencari event lari terdekat?','a'=>'Gunakan filter kota, bulan, kategori jarak, atau peta event di Ruang Lari untuk menemukan event yang sesuai dengan lokasi dan target latihan Anda.'],
                ['q'=>'Apa saja kategori event lari yang tersedia?','a'=>'Kategori umum meliputi 5K, 10K, half marathon, marathon, ultra marathon, trail run, fun run, charity run, dan virtual run.'],
                ['q'=>'Apakah jadwal lari di Ruang Lari diperbarui?','a'=>'Ya. Kalender event diperbarui secara berkala berdasarkan informasi penyelenggara dan kanal pendaftaran resmi.'],
                ['q'=>'Bagaimana cara mendaftarkan event lari ke Ruang Lari?','a'=>'Gunakan tombol Submit Event Lari Gratis untuk mengirim nama event, tanggal, lokasi, kategori jarak, banner, dan tautan pendaftaran resmi.'],
            ];
        @endphp

        <div class="ep-faq" x-data="{active:null}">
            <div class="ep-eyebrow pt-8">FAQ</div>
            <h2 class="ep-heading mb-2">Pertanyaan Umum tentang Jadwal Lari</h2>

            @foreach($faqs as $i => $faq)
                <div class="ep-faq-item">
                    <button type="button" @click="active = active === {{ $i }} ? null : {{ $i }}">
                        <span>{{ $faq['q'] }}</span>
                        <i class="fas fa-plus text-[9px] text-[#B8FF00]"
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
    <div class="fixed inset-0 bg-black/85"></div>

    <div class="relative min-h-screen flex items-center justify-center p-3 sm:p-6">
        <div class="ep-modal">
            <div class="ep-modal-head">
                <div>
                    <div class="text-[10px] uppercase tracking-[.16em] font-black text-[#B8FF00]">RuangLari / Event Submission</div>
                    <h3 class="mt-1 text-lg sm:text-xl font-bold uppercase tracking-tight text-white">Ajukan Event Lari Baru</h3>
                </div>
                <button type="button" id="btn-close-submit-event" class="w-8 h-8 rounded-md border border-white/20 text-slate-300 hover:text-white hover:border-white/40 flex items-center justify-center transition">
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
                                    <i class="fas fa-upload text-xl text-[#B8FF00]"></i>
                                    <p class="mt-2 text-xs font-bold text-white">Upload Banner Landscape</p>
                                    <p class="mt-1 text-[10px] text-slate-400">PNG, JPG, WEBP · Maksimum 2MB</p>
                                </div>

                                <div id="banner-dropzone-preview" class="hidden">
                                    <img id="banner-preview-img" src="" class="max-h-36 mx-auto object-cover rounded">
                                    <span id="banner-filename" class="block mt-2 text-[11px] text-slate-300 font-mono"></span>
                                    <button type="button" id="btn-remove-banner" class="mt-2 text-xs font-bold text-red-400 hover:text-red-300 transition">Ganti Banner</button>
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
                                <button type="button" id="btn-geolocation" class="ep-btn !min-h-[40px] shrink-0" title="Gunakan Lokasi Saya">
                                    <i class="fas fa-crosshairs text-[#B8FF00]"></i>
                                </button>
                            </div>

                            <button type="button" id="btn-clear-map-search" class="hidden"></button>
                            <i id="map-search-icon" class="hidden"></i>
                            <div id="map-search-results" class="hidden"></div>

                            <div class="relative mb-3">
                                <div id="event-map" class="w-full h-44 bg-[#07101c]"></div>
                                <div id="map-geocoding-status" class="absolute left-2 bottom-2 text-[9px] font-mono px-1.5 py-0.5 rounded bg-[#07101c]/90 text-[#B8FF00]"></div>
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
                            <div class="ep-form-section-title">05 / Link & Penyelenggara</div>
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
                            <div class="ep-form-section-title">06 / Kontributor & OTP</div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="submit_event_contributor_name">Nama Pengirim</label>
                                    <input type="text" name="contributor_name" id="submit_event_contributor_name" placeholder="Nama kamu">
                                </div>
                                <div>
                                    <label for="submit_event_contributor_email">Email Verifikasi *</label>
                                    <input type="email" name="contributor_email" id="submit_event_contributor_email" placeholder="email@domain.com" required>
                                </div>
                            </div>

                            <div class="mt-3">
                                <label for="submit_event_notes">Catatan Tambahan</label>
                                <textarea name="notes" id="submit_event_notes" rows="2" placeholder="Informasi biaya pendaftaran, racepack, atau detail lainnya..."></textarea>
                            </div>

                            <div class="grid grid-cols-[1fr_auto] gap-2 mt-3 items-end">
                                <div>
                                    <label for="submit_event_otp_code">Kode OTP *</label>
                                    <input type="text" inputmode="numeric" maxlength="6" name="otp_code" id="submit_event_otp_code" placeholder="6 digit kode OTP">
                                </div>
                                <button type="button" id="btn-submit-event-send-otp" class="ep-btn !min-h-[40px] shrink-0">Kirim OTP</button>
                            </div>
                        </section>
                    </div>
                </div>
            </form>

            <div class="ep-modal-foot">
                <div class="text-[11px] text-slate-400">OTP email wajib diverifikasi sebelum pengajuan event disubmit.</div>
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
