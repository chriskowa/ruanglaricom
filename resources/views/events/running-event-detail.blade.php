@extends('layouts.pacerhub')

@section('title', $event->name . ' - Jadwal Lari')
@section('description', Str::limit(strip_tags($event->short_description ?? $event->full_description), 150))

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    .detail-map-pin-glow {
        box-shadow: 0 0 16px rgba(204, 255, 0, 0.4), 0 2px 8px rgba(0, 0, 0, 0.8);
    }
    .detail-custom-leaflet-popup .leaflet-popup-content-wrapper {
        background: #12161F !important;
        color: #F1F5F9 !important;
        border-radius: 6px !important;
        border: 1px solid #28364F !important;
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.8) !important;
        padding: 10px 14px !important;
    }
    .detail-custom-leaflet-popup .leaflet-popup-content {
        margin: 0 !important;
        line-height: 1.4 !important;
    }
    .detail-custom-leaflet-popup .leaflet-popup-tip {
        background: #12161F !important;
        border: 1px solid #28364F !important;
    }
    .leaflet-control-attribution {
        display: none !important;
    }

    /* CKEditor Content Rendering (Detail Event Lari) */
    .ck-content {
        color: #CBD5E1;
        font-size: 0.9375rem;
        line-height: 1.75;
        word-break: break-word;
    }
    .ck-content > :first-child { margin-top: 0 !important; }
    .ck-content > :last-child { margin-bottom: 0 !important; }

    /* Paragraphs & Spacing */
    .ck-content p {
        margin-top: 0;
        margin-bottom: 1.1rem;
        line-height: 1.75;
        color: #CBD5E1;
    }

    /* Headings */
    .ck-content h1,
    .ck-content h2,
    .ck-content h3,
    .ck-content h4,
    .ck-content h5,
    .ck-content h6 {
        color: #FFFFFF;
        font-weight: 800;
        letter-spacing: -0.015em;
        line-height: 1.3;
    }
    .ck-content h1 { font-size: 1.75rem; margin-top: 2rem; margin-bottom: 0.85rem; }
    .ck-content h2 { font-size: 1.375rem; margin-top: 2rem; margin-bottom: 0.75rem; border-bottom: 1px solid #28364F; padding-bottom: 0.5rem; }
    .ck-content h3 { font-size: 1.15rem; margin-top: 1.6rem; margin-bottom: 0.6rem; }
    .ck-content h4 { font-size: 1rem; margin-top: 1.35rem; margin-bottom: 0.5rem; }
    .ck-content h5, .ck-content h6 { font-size: 0.875rem; margin-top: 1.1rem; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.05em; }

    /* Inline Text Styles */
    .ck-content strong, .ck-content b { font-weight: 700; color: #FFFFFF; }
    .ck-content em, .ck-content i { font-style: italic; }
    .ck-content u { text-decoration: underline; text-underline-offset: 3px; }
    .ck-content s, .ck-content strike, .ck-content del { text-decoration: line-through; opacity: 0.75; }
    .ck-content sub { font-size: 0.75em; vertical-align: sub; line-height: 0; }
    .ck-content sup { font-size: 0.75em; vertical-align: super; line-height: 0; }
    .ck-content mark { background-color: #EAB308; color: #080A0D; padding: 0.1em 0.35em; border-radius: 3px; font-weight: 600; }

    /* Lists */
    .ck-content ul {
        list-style-type: disc !important;
        padding-left: 1.5rem !important;
        margin-top: 0.75rem !important;
        margin-bottom: 1.1rem !important;
    }
    .ck-content ol {
        list-style-type: decimal !important;
        padding-left: 1.5rem !important;
        margin-top: 0.75rem !important;
        margin-bottom: 1.1rem !important;
    }
    .ck-content li {
        margin-bottom: 0.4rem;
        line-height: 1.65;
        color: #CBD5E1;
    }
    .ck-content li::marker {
        color: #CCFF00;
        font-weight: 700;
    }
    .ck-content ul ul { list-style-type: circle !important; margin: 0.35rem 0 !important; }
    .ck-content ul ul ul { list-style-type: square !important; }
    .ck-content ol ol { list-style-type: lower-latin !important; margin: 0.35rem 0 !important; }
    .ck-content .todo-list { list-style: none !important; padding-left: 0 !important; }
    .ck-content .todo-list li { display: flex; align-items: flex-start; gap: 0.6rem; margin-bottom: 0.4rem; }
    .ck-content .todo-list li input[type="checkbox"] { width: 16px; height: 16px; margin-top: 0.2rem; accent-color: #CCFF00; cursor: pointer; }

    /* Tables (CKEditor Figure Tables) */
    .ck-content figure.table {
        margin: 1.5rem 0;
        width: 100%;
        overflow-x: auto;
        display: block;
        border-radius: 6px;
        border: 1px solid #28364F;
        background: #0E1624;
    }
    .ck-content table {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        min-width: 480px;
        font-size: 0.875rem;
    }
    .ck-content th {
        background: #182234;
        color: #FFFFFF;
        font-weight: 700;
        text-align: left;
        padding: 0.75rem 1rem;
        border: 1px solid #28364F;
        font-size: 0.8125rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .ck-content td {
        padding: 0.75rem 1rem;
        border: 1px solid #233148;
        color: #E2E8F0;
        vertical-align: top;
        line-height: 1.5;
    }
    .ck-content tbody tr:nth-child(even) { background: rgba(255, 255, 255, 0.02); }
    .ck-content tbody tr:hover { background: rgba(255, 255, 255, 0.04); }
    .ck-content figcaption {
        padding: 0.5rem 0.75rem;
        font-size: 0.75rem;
        color: #94A3B8;
        text-align: center;
        background: #121A27;
        border-top: 1px solid #28364F;
    }

    /* Blockquotes */
    .ck-content blockquote {
        margin: 1.5rem 0;
        padding: 1rem 1.25rem;
        border-left: 4px solid #CCFF00;
        background: #182234;
        border-radius: 0 6px 6px 0;
        color: #E2E8F0;
        font-style: italic;
    }
    .ck-content blockquote p { margin: 0; color: #E2E8F0; }

    /* Code & Pre */
    .ck-content code {
        background: #182234;
        color: #CCFF00;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.85em;
        border: 1px solid #28364F;
    }
    .ck-content pre {
        background: #0E1624;
        border: 1px solid #28364F;
        border-radius: 6px;
        padding: 1rem;
        overflow-x: auto;
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8125rem;
        color: #E2E8F0;
        margin: 1.5rem 0;
        line-height: 1.5;
    }
    .ck-content pre code { background: transparent; padding: 0; border: none; color: inherit; }

    /* Horizontal Line */
    .ck-content hr {
        border: none;
        border-top: 1px solid #28364F;
        margin: 2rem 0;
    }

    /* Links */
    .ck-content a {
        color: #CCFF00;
        text-decoration: underline;
        text-underline-offset: 3px;
        font-weight: 600;
        transition: color 0.15s ease;
    }
    .ck-content a:hover {
        color: #DCFF33;
    }

    /* Images & Media */
    .ck-content figure.image { margin: 1.5rem 0; text-align: center; clear: both; }
    .ck-content figure.image img { border-radius: 6px; max-width: 100%; height: auto; display: inline-block; border: 1px solid #28364F; }
    .ck-content figure.image.image-style-side { float: right; margin-left: 1.5rem; max-width: 50%; }
    .ck-content figure.image.image-style-align-left { float: left; margin-right: 1.5rem; max-width: 50%; }
    .ck-content figure.image.image-style-align-right { float: right; margin-left: 1.5rem; max-width: 50%; }
    .ck-content figure.image.image-style-align-center { margin-left: auto; margin-right: auto; display: table; }
    .ck-content figure.media { margin: 1.5rem 0; position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 6px; border: 1px solid #28364F; }
    .ck-content figure.media iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

    /* Alignment Classes */
    .ck-content .text-left, .ck-content [style*="text-align: left"], .ck-content [style*="text-align:left"] { text-align: left !important; }
    .ck-content .text-center, .ck-content [style*="text-align: center"], .ck-content [style*="text-align:center"] { text-align: center !important; }
    .ck-content .text-right, .ck-content [style*="text-align: right"], .ck-content [style*="text-align:right"] { text-align: right !important; }
    .ck-content .text-justify, .ck-content [style*="text-align: justify"], .ck-content [style*="text-align:justify"] { text-align: justify !important; }

    /* Horizontal Rules */
    .ck-content hr {
        border: 0;
        border-top: 1px solid #28364F;
        margin: 2rem 0;
    }
</style>
@endpush

@section('content')
@if(!empty($isPreviewMode))
<div class="fixed top-0 inset-x-0 z-[99999] bg-gradient-to-r from-amber-500 via-yellow-400 to-amber-500 text-slate-950 font-black px-4 py-2.5 text-center text-xs sm:text-sm shadow-2xl flex items-center justify-center gap-3">
    <span>LIVE PREVIEW MODE &mdash; Ini adalah tampilan draf sementara event kamu (Data Belum Disimpan)</span>
    <button onclick="window.close()" class="px-3 py-1 bg-slate-950 text-white font-bold text-xs rounded-lg hover:bg-slate-800 transition-colors shadow">Tutup Preview</button>
</div>
@endif
<div class="relative min-h-screen bg-[#0B1120] font-sans selection:bg-neon selection:text-dark">
    <!-- Hero Background -->
    <div class="absolute inset-0 h-[60vh] overflow-hidden z-0">
        @if($event->hero_image_url)
            <img src="{{ $event->hero_image_url }}" class="w-full h-full object-cover opacity-30 blur-sm scale-105">
            <div class="absolute inset-0 bg-gradient-to-b from-dark/60 via-dark/80 to-[#0B1120]"></div>
        @else
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900"></div>
            <div class="absolute inset-0 opacity-20" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%239C92AC\' fill-opacity=\'0.1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        @endif
    </div>

    <!-- Content Container -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-32 pb-20">
        
        <!-- Breadcrumb -->
        <nav class="flex mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('events.index') }}" class="inline-flex items-center text-sm font-medium text-slate-400 hover:text-neon transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kalender Lari
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-slate-600 mx-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="text-sm font-medium text-slate-200 truncate max-w-[200px] md:max-w-xs">{{ $event->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        @php
            $cityCoordinatesFallback = [
                'jakarta' => [-6.2088, 106.8456],
                'dki jakarta' => [-6.2088, 106.8456],
                'bandung' => [-6.9175, 107.6191],
                'surabaya' => [-7.2575, 112.7521],
                'yogyakarta' => [-7.7956, 110.3695],
                'jogja' => [-7.7956, 110.3695],
                'sleman' => [-7.7167, 110.3556],
                'bantul' => [-7.8933, 110.3347],
                'semarang' => [-6.9667, 110.4167],
                'bogor' => [-6.5971, 106.8060],
                'tangerang' => [-6.1783, 106.6319],
                'tangerang selatan' => [-6.2888, 106.7179],
                'bekasi' => [-6.2383, 106.9756],
                'depok' => [-6.4025, 106.7942],
                'malang' => [-7.9666, 112.6326],
                'bali' => [-8.6705, 115.2126],
                'denpasar' => [-8.6705, 115.2126],
                'badung' => [-8.5819, 115.1771],
                'solo' => [-7.5755, 110.8243],
                'surakarta' => [-7.5755, 110.8243],
                'medan' => [3.5952, 98.6722],
                'makassar' => [-5.1477, 119.4327],
                'balikpapan' => [-1.2379, 116.8529],
                'samarinda' => [-0.5022, 117.1536],
                'batam' => [1.1301, 104.0529],
                'palembang' => [-2.9761, 104.7754],
                'pekanbaru' => [0.5071, 101.4478],
                'lampung' => [-5.4500, 105.2667],
                'bandar lampung' => [-5.4500, 105.2667],
                'padang' => [-0.9471, 100.4172],
                'pontianak' => [-0.0263, 109.3425],
                'banjarmasin' => [-3.3194, 114.5908],
                'manado' => [1.4748, 124.8421],
                'mataram' => [-8.5833, 116.1167],
                'lombok' => [-8.5833, 116.1167],
                'kupang' => [-10.1772, 123.6070],
                'cirebon' => [-6.7320, 108.5523],
                'tasikmalaya' => [-7.3274, 108.2207],
                'sukabumi' => [-6.9277, 106.9300],
                'magelang' => [-7.4706, 110.2178],
            ];

            $hasExactCoordinates = !empty($event->location_lat) && !empty($event->location_lng);
            $detailMapLat = $event->location_lat ?: ($event->rpc_latitude ?: ($event->city?->latitude ?? null));
            $detailMapLng = $event->location_lng ?: ($event->rpc_longitude ?: ($event->city?->longitude ?? null));

            if (!$detailMapLat || !$detailMapLng) {
                $cityNameLower = strtolower(trim($event->city?->name ?? ''));
                if (!$cityNameLower && $event->location_name) {
                    $cityNameLower = strtolower(trim($event->location_name));
                }
                foreach ($cityCoordinatesFallback as $k => $coords) {
                    if (str_contains($cityNameLower, $k)) {
                        $detailMapLat = $coords[0];
                        $detailMapLng = $coords[1];
                        break;
                    }
                }
            }

            if (!$detailMapLat || !$detailMapLng) {
                $detailMapLat = -6.2088;
                $detailMapLng = 106.8456;
            }

            $detailZoom = $hasExactCoordinates ? 16 : 13;
            $venueTitle = $event->location_name ?: ($event->city?->name ?: 'Lokasi Start Event');
            $venueAddr = $event->location_address ?: ($event->city?->name ?? 'Indonesia');
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
            <!-- Main Article Column -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- Title Section -->
                <header class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-1 rounded-sm text-xs font-bold bg-neon/10 text-neon border border-neon/30 uppercase tracking-wide">
                            {{ $event->raceType->name ?? 'Running Event' }}
                        </span>
                        @if($event->is_featured)
                            <span class="px-2.5 py-1 rounded-sm text-xs font-bold bg-amber-500 text-dark uppercase tracking-wide">Featured</span>
                        @endif
                    </div>
                    
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight">
                        {{ $event->name }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-slate-300 border-l-2 border-neon pl-4 sm:pl-5 py-1">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-md bg-slate-800/80 text-neon">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Tanggal Event</span>
                                <span class="font-bold text-white text-sm sm:text-base">{{ $event->event_date->translatedFormat('d F Y') }}</span>
                            </div>
                        </div>
                        <div class="hidden sm:block w-px h-8 bg-slate-800"></div>
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-md bg-slate-800/80 text-neon">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <div>
                                <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">Lokasi / Kota</span>
                                <span class="font-bold text-white text-sm sm:text-base">{{ $event->city ? $event->city->name : $event->location_name }}</span>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Featured Image (Article Style) -->
                @if($event->hero_image_url)
                    <figure class="relative rounded-lg overflow-hidden border border-slate-800 bg-slate-900 group shadow-lg">
                        <img src="{{ $event->hero_image_url }}" alt="{{ $event->name }}" class="w-full h-auto object-cover max-h-[480px]">
                    </figure>
                @endif

                <!-- Article Content (CKEditor Output) -->
                @php
                    $descriptionContent = $event->sanitized_description_html ?: ($event->full_description ?: $event->short_description);
                @endphp
                @if(!empty(trim(strip_tags((string)$descriptionContent, '<img><iframe><svg><figure><oembed>'))))
                <section class="bg-[#12161F] border border-slate-800 rounded-lg p-6 sm:p-8 shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3.5 mb-6">
                        <h2 class="text-base font-bold text-white uppercase tracking-tight flex items-center gap-2">
                            <span>Informasi & Deskripsi Event</span>
                        </h2>
                    </div>
                    <article class="ck-content event-article-body">
                        {!! $descriptionContent !!}
                    </article>
                </section>
                @endif

                <!-- Race Categories Grid -->
                @if($event->raceDistances->count() > 0)
                    <div class="bg-[#12161F] border border-slate-800 rounded-lg p-6 shadow-xl">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3.5 mb-5">
                            <h3 class="text-base font-bold text-white uppercase tracking-tight">Kategori Jarak</h3>
                            <span class="text-xs font-mono text-neon">{{ $event->raceDistances->count() }} Kategori</span>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($event->raceDistances as $distance)
                                <div class="bg-[#0E1624] border border-slate-800 hover:border-slate-700 rounded-md p-4 text-center transition-all">
                                    <span class="block text-xl font-black text-white mb-0.5">{{ $distance->name }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Kategori Lomba</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Related Events & Same Date Events -->
                <div class="space-y-8 pt-4">
                    @if(isset($relatedEvents) && $relatedEvents->count() > 0)
                        <div>
                            <div class="flex items-center justify-between gap-4 mb-4 pb-2 border-b border-slate-800">
                                <h3 class="text-base font-bold text-white uppercase">Event Serupa</h3>
                                <a href="{{ route('events.index') }}" class="text-xs font-bold text-neon hover:underline">LIHAT SEMUA</a>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($relatedEvents as $related)
                                    <a href="{{ route('running-event.detail', $related->slug) }}" class="group block bg-[#12161F] border border-slate-800 hover:border-slate-700 rounded-lg overflow-hidden transition-all">
                                        <div class="aspect-video relative overflow-hidden bg-slate-950">
                                            @if($related->hero_image_url)
                                                <img src="{{ $related->hero_image_url }}" alt="{{ $related->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            @else
                                                <div class="w-full h-full bg-slate-900 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div class="absolute top-2 right-2 bg-slate-950/90 px-2 py-0.5 rounded text-[10px] font-bold text-white border border-slate-800">
                                                {{ $related->event_date->format('d M Y') }}
                                            </div>
                                        </div>
                                        <div class="p-3.5">
                                            <h4 class="font-bold text-white text-sm group-hover:text-neon transition-colors line-clamp-1 mb-1">{{ $related->name }}</h4>
                                            <p class="text-xs text-slate-400 truncate">
                                                {{ $related->city ? $related->city->name : $related->location_name }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(isset($sameDateEvents) && $sameDateEvents->count() > 0)
                        <div>
                            <div class="flex items-center justify-between gap-4 mb-4 pb-2 border-b border-slate-800">
                                <h3 class="text-base font-bold text-white uppercase">Event di Tanggal Sama</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($sameDateEvents as $sameDate)
                                    <a href="{{ route('running-event.detail', $sameDate->slug) }}" class="group block bg-[#12161F] border border-slate-800 hover:border-slate-700 rounded-lg overflow-hidden transition-all">
                                        <div class="aspect-video relative overflow-hidden bg-slate-950">
                                            @if($sameDate->hero_image_url)
                                                <img src="{{ $sameDate->hero_image_url }}" alt="{{ $sameDate->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            @else
                                                <div class="w-full h-full bg-slate-900 flex items-center justify-center">
                                                    <svg class="w-8 h-8 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                                </div>
                                            @endif
                                            <div class="absolute top-2 right-2 bg-neon text-dark px-2 py-0.5 rounded text-[10px] font-extrabold">
                                                {{ $sameDate->event_date->format('d M Y') }}
                                            </div>
                                        </div>
                                        <div class="p-3.5">
                                            <h4 class="font-bold text-white text-sm group-hover:text-neon transition-colors line-clamp-1 mb-1">{{ $sameDate->name }}</h4>
                                            <p class="text-xs text-slate-400 truncate">
                                                {{ $sameDate->city ? $sameDate->city->name : $sameDate->location_name }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Registration Widget -->
                <div class="bg-[#12161F] border border-slate-800 rounded-lg p-6 shadow-xl relative">
                    <h3 class="text-base font-bold text-white mb-5 flex items-center gap-2">
                        <span class="w-1 h-5 bg-neon rounded-sm"></span>
                        <span>STATUS PENDAFTARAN</span>
                    </h3>
                    
                    @if($event->external_registration_link)
                        <a href="{{ $event->external_registration_link }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-full py-3.5 px-4 rounded-md bg-neon text-dark font-black text-sm uppercase tracking-wider hover:bg-lime-300 transition-all shadow-md">
                            <span>Daftar Sekarang</span>
                            <svg class="w-4 h-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                        <p class="text-center text-[11px] text-slate-400 mt-2.5">
                            Membuka halaman registrasi resmi event
                        </p>
                    @else
                        <div class="w-full py-3 px-4 rounded-md bg-slate-800/80 text-slate-400 font-bold text-sm text-center border border-slate-700">
                            Pendaftaran Belum Dibuka
                        </div>
                    @endif

                    <div class="mt-6 pt-5 border-t border-slate-800 space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-md bg-slate-800 text-neon shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 uppercase font-bold tracking-wider">Waktu Mulai</span>
                                <span class="text-slate-200 font-semibold text-sm">{{ $event->start_time ? $event->start_time->format('H:i') . ' WIB' : '05:00 WIB' }}</span>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-md bg-slate-800 text-neon shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 uppercase font-bold tracking-wider">Venue / Lokasi</span>
                                <span class="text-slate-200 font-semibold text-sm leading-snug">{{ $event->location_name ?: ($event->city ? $event->city->name : 'TBA') }}</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-md bg-slate-800 text-neon shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 uppercase font-bold tracking-wider">Penyelenggara</span>
                                <span class="text-slate-200 font-semibold text-sm">{{ $event->organizer_name ?? 'Komunitas / EO' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Map Widget (Pindah ke Sidebar Kanan) -->
                <div class="bg-[#12161F] border border-slate-800 rounded-lg p-5 shadow-xl">
                    <div class="flex items-center justify-between gap-2 mb-3.5 pb-3 border-b border-slate-800">
                        <h3 class="text-base font-bold text-white uppercase tracking-tight flex items-center gap-2">
                            <svg class="w-4 h-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Peta Lokasi</span>
                        </h3>
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $detailMapLat }},{{ $detailMapLng }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-neon hover:underline inline-flex items-center gap-1.5" title="Buka di Google Maps">
                            <span>Google Maps</span>
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                    </div>

                    <div class="relative rounded-md overflow-hidden border border-slate-800 bg-[#090D16]">
                        <!-- Map Canvas -->
                        <div id="event-detail-location-map" class="w-full h-[240px] z-0 bg-[#090D16]"></div>
                    </div>

                    <!-- Venue Info & Recenter -->
                    <div class="mt-3.5 space-y-2 text-xs">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-white leading-snug truncate">{{ $venueTitle }}</div>
                                <div class="text-slate-400 text-[11px] mt-0.5 line-clamp-2 leading-relaxed">{{ $venueAddr }}</div>
                            </div>
                            <button type="button" onclick="recenterEventDetailLocationMap()" class="shrink-0 p-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition border border-slate-700" title="Pusatkan Peta">
                                <i class="fa-solid fa-crosshairs text-xs text-neon"></i>
                            </button>
                        </div>
                        <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 font-mono">
                            <span>{{ number_format($detailMapLat, 4) }}, {{ number_format($detailMapLng, 4) }}</span>
                            <span class="text-slate-500">{{ $hasExactCoordinates ? 'Presisi GPS' : 'Estimasi Kota' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Google Calendar Widget -->
                <div class="bg-[#12161F] border border-slate-800 rounded-lg p-5 shadow-xl">
                    <h3 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                        <svg class="w-5 h-5 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>SIMPAN TANGGAL</span>
                    </h3>
                    <p class="text-slate-400 text-xs mb-4">
                        Tambahkan jadwal perlombaan ini ke Google Calendar agar Anda tidak terlewat.
                    </p>
                    
                    @php
                        $startDate = $event->event_date->format('Ymd');
                        $startTime = $event->start_time ? $event->start_time->format('His') : '050000';
                        $startDateTime = $startDate . 'T' . $startTime;
                        $endDateTime = \Carbon\Carbon::parse($startDate . ' ' . ($event->start_time ? $event->start_time->format('H:i:s') : '05:00:00'))->addHours(5)->format('Ymd\THis');
                        
                        $gCalUrl = "https://www.google.com/calendar/render?action=TEMPLATE";
                        $gCalUrl .= "&text=" . urlencode($event->name);
                        $gCalUrl .= "&dates=" . $startDateTime . "/" . $endDateTime;
                        $gCalUrl .= "&details=" . urlencode("Event Lari: " . $event->name . "\nLokasi: " . ($event->location_name ?? 'TBA') . "\n\nInfo: " . $event->public_url);
                        $gCalUrl .= "&location=" . urlencode($event->location_name ?? ($event->city ? $event->city->name : ''));
                        $gCalUrl .= "&sf=true&output=xml";
                    @endphp

                    <a href="{{ $gCalUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center w-full py-2.5 px-4 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white text-xs font-bold transition-all gap-2">
                        <span>Add to Google Calendar</span>
                        <svg class="w-4 h-4 text-neon" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                    </a>
                </div>

                <!-- Rating Widget -->
                <div class="bg-[#12161F] border border-slate-800 rounded-lg p-5 shadow-xl">
                    <h3 class="text-base font-bold text-white mb-1.5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                        <span>RATING EVENT</span>
                    </h3>
                    <p class="text-slate-400 text-xs mb-3.5">
                        Rata-rata <strong id="ph-event-rating-avg" class="text-white font-mono">{{ number_format($ratingAverage, 2) }}</strong> / 5 &bull; <span id="ph-event-rating-count">{{ $ratingCount }}</span> ulasan
                    </p>

                    <div>
                        <div class="flex items-center gap-1.5" role="radiogroup" aria-label="Beri rating untuk event ini">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="ph-rating-star p-2 rounded-md bg-slate-800 border border-slate-700 text-slate-600 hover:text-amber-400 hover:border-amber-500/40 focus:outline-none transition-all" data-rating="{{ $i }}" aria-label="Beri {{ $i }} bintang">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                </button>
                            @endfor
                        </div>
                        <div id="ph-event-rating-msg" class="hidden text-xs mt-2.5 font-medium"></div>
                    </div>
                </div>

                <!-- Share / Socials -->
                <div class="flex items-center justify-between p-4 rounded-lg bg-[#12161F] border border-slate-800">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Bagikan Event</span>
                    <div class="flex gap-2">
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link disalin!')" class="p-2 rounded-md bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-all border border-slate-700" title="Salin Tautan">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        </button>
                        @if($event->social_media_link)
                            <a href="{{ $event->social_media_link }}" target="_blank" rel="noopener noreferrer" class="p-2 rounded-md bg-slate-800 text-slate-300 hover:text-neon hover:bg-slate-700 transition-all border border-slate-700" title="Media Sosial">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const apiUrl = @json(route('api.running-events.rating.store', $event->slug));
        const csrfToken = @json(csrf_token());
        const avgEl = document.getElementById('ph-event-rating-avg');
        const countEl = document.getElementById('ph-event-rating-count');
        const msgEl = document.getElementById('ph-event-rating-msg');
        const stars = Array.from(document.querySelectorAll('.ph-rating-star'));
        if (!avgEl || !countEl || !msgEl || stars.length === 0) return;

        let isSubmitting = false;

        function showMessage(text, variant) {
            msgEl.textContent = text;
            msgEl.classList.remove('hidden', 'text-red-400', 'text-green-400', 'text-slate-400');
            msgEl.classList.add(variant === 'success' ? 'text-green-400' : variant === 'error' ? 'text-red-400' : 'text-slate-400');
        }

        function clearMessage() {
            msgEl.textContent = '';
            msgEl.classList.add('hidden');
        }

        function setStars(value) {
            stars.forEach((btn) => {
                const v = Number(btn.dataset.rating || 0);
                btn.classList.toggle('text-amber-400', v <= value);
                btn.classList.toggle('border-amber-500/40', v <= value);
                btn.classList.toggle('text-slate-600', v > value);
                btn.classList.toggle('border-slate-700', v > value);
            });
        }

        function disableStars(disabled) {
            stars.forEach((btn) => {
                btn.disabled = disabled;
                btn.classList.toggle('opacity-60', disabled);
                btn.classList.toggle('cursor-not-allowed', disabled);
            });
        }

        function buildFingerprint() {
            const tz = (() => {
                try {
                    return Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                } catch (e) {
                    return '';
                }
            })();

            return [
                navigator.userAgent || '',
                navigator.language || '',
                navigator.platform || '',
                String((screen && screen.width) ? screen.width : ''),
                String((screen && screen.height) ? screen.height : ''),
                String((screen && screen.colorDepth) ? screen.colorDepth : ''),
                tz,
                String(new Date().getTimezoneOffset()),
                String(navigator.hardwareConcurrency || ''),
            ].join('|');
        }

        async function submitRating(rating) {
            if (isSubmitting) return;
            isSubmitting = true;
            disableStars(true);
            clearMessage();
            showMessage('Mengirim rating...', 'info');

            try {
                const fingerprint = buildFingerprint();
                const res = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ rating, fingerprint }),
                });

                const data = await res.json().catch(() => ({}));

                if (res.status === 409) {
                    showMessage(data.message || 'Anda sudah pernah memberikan rating untuk event ini.', 'error');
                    return;
                }

                if (!res.ok) {
                    const msg = (data && data.message) ? data.message : 'Gagal mengirim rating. Coba lagi.';
                    showMessage(msg, 'error');
                    return;
                }

                if (typeof data.average_rating !== 'undefined') avgEl.textContent = String(data.average_rating);
                if (typeof data.rating_count !== 'undefined') countEl.textContent = String(data.rating_count);
                showMessage(data.message || 'Rating berhasil dikirim.', 'success');
            } catch (e) {
                showMessage('Gagal mengirim rating. Periksa koneksi internet Anda.', 'error');
            } finally {
                disableStars(false);
                isSubmitting = false;
            }
        }

        stars.forEach((btn) => {
            btn.addEventListener('mouseenter', () => setStars(Number(btn.dataset.rating || 0)));
            btn.addEventListener('focus', () => setStars(Number(btn.dataset.rating || 0)));
            btn.addEventListener('mouseleave', () => setStars(0));
            btn.addEventListener('blur', () => setStars(0));
            btn.addEventListener('click', () => submitRating(Number(btn.dataset.rating || 0)));
        });
    })();
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let detailEventMap = null;
    const detailEventLat = {{ $detailMapLat }};
    const detailEventLng = {{ $detailMapLng }};
    const detailEventZoom = {{ $detailZoom }};
    const detailEventName = @json($event->name);
    const detailEventLocation = @json($venueTitle);

    function initEventDetailLocationMap() {
        const mapEl = document.getElementById('event-detail-location-map');
        if (!mapEl || detailEventMap) return;

        detailEventMap = L.map('event-detail-location-map', {
            zoomControl: true,
            scrollWheelZoom: false,
            dragging: true,
        }).setView([detailEventLat, detailEventLng], detailEventZoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors'
        }).addTo(detailEventMap);

        // Custom Marker Pin (Neon Green Pin matching Landing Jadwal Lari)
        const locationPin = L.divIcon({
            className: 'border-0 bg-transparent',
            html: `
                <div class="relative group cursor-pointer">
                    <div class="w-10 h-10 rounded-full border-2 border-[#0c121e] shadow-xl flex items-center justify-center font-black text-xs transition-transform transform group-hover:scale-110" style="background-color: #ccff00; color: #020617;">
                        <i class="fa-solid fa-person-running text-sm"></i>
                    </div>
                    <div class="w-2.5 h-2.5 rounded-full mx-auto -mt-1 shadow-md" style="background-color: #ccff00;"></div>
                </div>
            `,
            iconSize: [36, 40],
            iconAnchor: [18, 40],
            popupAnchor: [0, -40]
        });

        const marker = L.marker([detailEventLat, detailEventLng], { icon: locationPin }).addTo(detailEventMap);

        const popupContent = `
            <div class="detail-custom-leaflet-popup font-sans text-xs space-y-1.5">
                <div class="font-black text-white text-sm leading-tight">${detailEventName}</div>
                <div class="text-slate-300 text-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-location-dot text-neon"></i>
                    <span>${detailEventLocation}</span>
                </div>
                <div class="pt-1.5">
                    <a href="https://www.google.com/maps/search/?api=1&query=${detailEventLat},${detailEventLng}" target="_blank" rel="noopener noreferrer" class="px-2.5 py-1 rounded-md bg-neon hover:bg-lime-300 text-dark font-black text-[11px] inline-flex items-center gap-1 transition shadow-sm">
                        <i class="fa-solid fa-diamond-turn-right text-[10px]"></i>
                        <span>Petunjuk Arah</span>
                    </a>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent, { maxWidth: 280, className: 'detail-custom-leaflet-popup' });
    }

    function recenterEventDetailLocationMap() {
        if (!detailEventMap) return;
        detailEventMap.setView([detailEventLat, detailEventLng], detailEventZoom, { animate: true });
    }

    document.addEventListener('DOMContentLoaded', function() {
        initEventDetailLocationMap();
        setTimeout(() => {
            if (detailEventMap) detailEventMap.invalidateSize();
        }, 300);
    });

    window.addEventListener('resize', function() {
        if (detailEventMap) detailEventMap.invalidateSize();
    });
</script>
@endpush

@push('structured_data')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "SportsEvent",
  "name": "{{ e($event->name) }}",
  "description": "{{ e(Str::limit(strip_tags($event->short_description ?? $event->full_description), 250)) }}",
  "startDate": "{{ $event->start_at->toIso8601String() }}",
  "endDate": "{{ $event->end_at ? $event->end_at->toIso8601String() : $event->start_at->addHours(5)->toIso8601String() }}",
  "eventStatus": "https://schema.org/EventScheduled",
  "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
  "location": {
    "@type": "Place",
    "name": "{{ e($event->location_name ?? ($event->city ? $event->city->name : 'TBA')) }}",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "{{ e($event->city ? $event->city->name : 'TBA') }}",
      "addressCountry": "ID"
    }
  },
  @if($event->hero_image_url)
  "image": [
    "{{ $event->hero_image_url }}"
  ],
  @endif
  "offers": {
    "@type": "Offer",
    "url": "{{ $event->public_url }}",
    "priceCurrency": "IDR",
    "price": "0",
    "availability": "https://schema.org/InStock"
  },
  "organizer": {
    "@type": "Organization",
    "name": "{{ e($event->organizer_name ?? 'Ruang Lari') }}",
    "url": "https://ruanglari.com"
  }
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
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": "{{ e($event->name) }}",
      "item": "{{ $event->public_url }}"
    }
  ]
}
</script>
@endpush

