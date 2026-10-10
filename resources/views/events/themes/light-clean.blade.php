<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <script>
        // Nominatim CORS Proxy Interceptor (Fetch & XHR)
        (function() {
            var originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                    var proxyUrl = '/image-proxy?url=' + encodeURIComponent(url);
                    return originalFetch(proxyUrl, options);
                }
                return originalFetch(url, options);
            };

            var originalOpen = XMLHttpRequest.prototype.open;
            XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
                if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                    url = '/image-proxy?url=' + encodeURIComponent(url);
                }
                return originalOpen.apply(this, arguments);
            };
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @php
        $seoTitle = isset($seo['title']) && $seo['title'] ? $seo['title'] : $event->name . ' - ' . ($event->location_name ?? 'Official Event');
        $seoDesc = isset($seo['description']) && $seo['description'] ? $seo['description'] : Str::limit(strip_tags($event->short_description ?: $event->full_description), 155);
        $seoKeywords = isset($seo['keywords']) && $seo['keywords'] ? $seo['keywords'] : 'lari, event lari, ' . $event->name . ', ' . ($event->location_name ?? '') . ', pendaftaran lari, ruanglari';
        $seoUrl = isset($seo['url']) && $seo['url'] ? $seo['url'] : route('events.show', $event->slug);
        $seoImage = isset($seo['image']) && $seo['image'] ? $seo['image'] : ($event->hero_image ? asset('storage/' . $event->hero_image) : asset('images/ruanglari_green.png'));

        $now = now();
        $isComingSoon = ($event->registration_open_at && $now < $event->registration_open_at);
        $isNaturallyClosed = ($event->registration_close_at && $now > $event->registration_close_at);
        $isRegOpen = !$isComingSoon && !$isNaturallyClosed;

        $gaId = \App\Models\AppSettings::get('google_analytics');
        $gadsId = \App\Models\AppSettings::get('google_ads_tag');

        $categoriesWithGpx = ($categories ?? collect())->filter(function ($cat) {
            return !empty($cat->master_gpx_id) && !empty($cat->masterGpx);
        })->values();

        $gpxDataList = [];
        foreach ($categoriesWithGpx as $index => $cat) {
            $mg = $cat->masterGpx;
            $rawCoords = $mg->coordinates_json ?? [];
            $distKm = (float) ($mg->distance_km ?? $cat->distance_km ?? 0);
            $gainM = (float) ($mg->elevation_gain_m ?? 0);
            $lossM = (float) ($mg->elevation_loss_m ?? 0);
            $routeType = $mg->route_type_label ?? 'Road';
            $gpxFilePath = $mg->gpx_path ? (str_starts_with($mg->gpx_path, 'http') ? $mg->gpx_path : asset('storage/' . ltrim($mg->gpx_path, '/'))) : null;
            $downloadUrl = !empty($mg->id) ? route('gpx.download', $mg->slug ?: $mg->id) : $gpxFilePath;

            // Generate sampled elevation points for SVG Profile (instant render, 0KB library)
            $sampledElevations = [];
            if (is_array($rawCoords) && count($rawCoords) > 0) {
                $step = max(1, (int) floor(count($rawCoords) / 60));
                for ($i = 0; $i < count($rawCoords); $i += $step) {
                    $pt = $rawCoords[$i];
                    $ele = is_array($pt) ? ($pt['ele'] ?? $pt[2] ?? null) : ($pt->ele ?? null);
                    if ($ele !== null && is_numeric($ele)) {
                        $sampledElevations[] = (float) $ele;
                    }
                }
            }

            // Fallback smooth undulating terrain curve if raw ele coordinates are missing
            if (count($sampledElevations) < 5) {
                $sampledElevations = [];
                $baseEle = 25.0;
                $numSamples = 50;
                for ($i = 0; $i <= $numSamples; $i++) {
                    $progress = $i / $numSamples;
                    $wave1 = sin($progress * M_PI * 2.5) * ($gainM * 0.45);
                    $wave2 = sin($progress * M_PI * 5.0) * ($gainM * 0.18);
                    $wave3 = cos($progress * M_PI * 1.5) * ($gainM * 0.25);
                    $sampleEle = max(5.0, round($baseEle + max(0, $wave1 + $wave2 + $wave3), 1));
                    $sampledElevations[] = $sampleEle;
                }
            }

            $minEle = count($sampledElevations) > 0 ? (int) floor(min($sampledElevations)) : 0;
            $maxEle = count($sampledElevations) > 0 ? (int) ceil(max($sampledElevations)) : 100;
            $eleRange = max(10, $maxEle - $minEle);

            // Build SVG Path points for viewBox="0 0 800 140"
            $svgWidth = 800;
            $svgHeight = 140;
            $chartPaddingBottom = 15;
            $chartPaddingTop = 15;
            $usableHeight = $svgHeight - $chartPaddingBottom - $chartPaddingTop;
            $totalPts = count($sampledElevations);

            $linePoints = [];
            $areaD = "M 0 {$svgHeight}";

            foreach ($sampledElevations as $pIdx => $eleVal) {
                $x = round(($pIdx / max(1, $totalPts - 1)) * $svgWidth, 1);
                $normEle = ($eleVal - $minEle) / $eleRange;
                $y = round(($svgHeight - $chartPaddingBottom) - ($normEle * $usableHeight), 1);
                $linePoints[] = "{$x},{$y}";
                $areaD .= " L {$x} {$y}";
            }
            $areaD .= " L {$svgWidth} {$svgHeight} Z";
            $lineD = "M " . implode(' L ', array_map(fn($p) => str_replace(',', ' ', $p), $linePoints));

            $gpxDataList[] = [
                'id' => $cat->id,
                'category_name' => $cat->name,
                'distance_km' => $distKm,
                'distance_formatted' => number_format($distKm, 2),
                'elevation_gain_m' => (int) round($gainM),
                'elevation_loss_m' => (int) round($lossM),
                'min_elevation' => $minEle,
                'max_elevation' => $maxEle,
                'route_type' => $routeType,
                'slug' => $mg->slug ?: $mg->id,
                'detail_url' => !empty($mg->id) ? route('gpx.show', $mg->slug ?: $mg->id) : null,
                'gpx_url' => $gpxFilePath,
                'download_url' => $downloadUrl,
                'coordinates' => $rawCoords,
                'area_path' => $areaD,
                'line_path' => $lineD,
            ];
        }
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}" />
    <meta name="keywords" content="{{ $seoKeywords }}">
    <link rel="canonical" href="{{ $seoUrl }}">
    <meta name="robots" content="index, follow, max-image-preview:large" />
    <meta name="theme-color" content="#ffffff">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="event" />
    <meta property="og:locale" content="id_ID" />
    <meta property="og:title" content="{{ $seoTitle }}" />
    <meta property="og:description" content="{{ $seoDesc }}" />
    <meta property="og:url" content="{{ $seoUrl }}" />
    <meta property="og:image" content="{{ $seoImage }}" />
    <meta property="og:site_name" content="RuangLari" />

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seoTitle }}" />
    <meta name="twitter:description" content="{{ $seoDesc }}" />
    <meta name="twitter:image" content="{{ $seoImage }}" />

    <!-- GEO / Geotargeting -->
    @if($event->location_lat && $event->location_lng)
    <meta name="geo.region" content="ID" />
    <meta name="geo.placename" content="{{ $event->city ?? ($event->location_name ?? 'Indonesia') }}" />
    <meta name="geo.position" content="{{ $event->location_lat }};{{ $event->location_lng }}" />
    <meta name="ICBM" content="{{ $event->location_lat }}, {{ $event->location_lng }}" />
    @endif

    @if($gaId || $gadsId)
    <!-- Google Analytics (GA4) / Google Tag Manager -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId ?: $gadsId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        @if($gaId)
        gtag('config', '{{ $gaId }}');
        @endif
        @if($gadsId)
        gtag('config', '{{ $gadsId }}');
        @endif
    </script>
    @endif

    <!-- Schema.org Structured Data (Event, Breadcrumb, FAQ for SEO & AIO) -->
    @php
        $eventSchema = [
            '@context' => 'https://schema.org',
            '@type' => ['Event', 'SportsEvent'],
            'name' => $event->name,
            'description' => $seoDesc,
            'url' => $seoUrl,
            'image' => [$seoImage],
            'startDate' => $event->start_at ? $event->start_at->toIso8601String() : '',
            'endDate' => $event->end_at ? $event->end_at->toIso8601String() : ($event->start_at ? $event->start_at->addHours(4)->toIso8601String() : ''),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => [
                '@type' => 'Place',
                'name' => $event->location_name ?? 'TBA',
                'address' => [
                    '@type' => 'PostalAddress',
                    'streetAddress' => $event->location_address ?? '',
                    'addressLocality' => $event->city ?? '',
                    'addressCountry' => 'ID',
                ],
            ],
            'organizer' => [
                '@type' => 'Organization',
                'name' => $event->organizer_name ?: ($event->user->name ?? 'RuangLari'),
                'url' => url('/'),
            ],
        ];

        if (!empty($event->location_lat) && !empty($event->location_lng)) {
            $eventSchema['location']['geo'] = [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $event->location_lat,
                'longitude' => (float) $event->location_lng,
            ];
        }

        $offersList = [];
        if (isset($categories) && $categories->isNotEmpty()) {
            foreach ($categories as $cat) {
                $catPrice = (float) ($cat->price_regular ?: ($cat->price_early ?: 0));
                $offersList[] = [
                    '@type' => 'Offer',
                    'name' => $cat->name . ($cat->distance_km ? ' (' . $cat->distance_km . 'K)' : ''),
                    'price' => (string) $catPrice,
                    'priceCurrency' => 'IDR',
                    'url' => $seoUrl,
                    'validFrom' => $event->registration_open_at ? $event->registration_open_at->toIso8601String() : null,
                    'availability' => ($isRegOpen && ($cat->is_active ?? true))
                        ? 'https://schema.org/InStock'
                        : 'https://schema.org/SoldOut',
                ];
            }
        }

        if (!empty($offersList)) {
            $eventSchema['offers'] = count($offersList) === 1 ? $offersList[0] : $offersList;
        } else {
            $eventSchema['offers'] = [
                '@type' => 'Offer',
                'url' => $seoUrl,
                'price' => '0',
                'priceCurrency' => 'IDR',
                'availability' => $isRegOpen ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
                'validFrom' => $event->registration_open_at ? $event->registration_open_at->toIso8601String() : null,
            ];
        }

        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Beranda',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Event Lari',
                    'item' => route('events.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $event->name,
                    'item' => $seoUrl,
                ],
            ],
        ];

        $rawFaqs = $event->premium_amenities['faq']['items'] ?? [];
        if (empty($rawFaqs)) {
            $rawFaqs = [
                ['question' => 'Kapan pengambilan Race Pack (RPC) dilakukan?', 'answer' => 'Jadwal dan lokasi pengambilan race pack akan diumumkan resmi melalui email dan WhatsApp terdaftar menjelang hari H.'],
                ['question' => 'Apakah tiket yang sudah dibeli dapat dipindahtangankan?', 'answer' => 'Tiket bersifat personal sesuai identitas diri saat pendaftaran demi keselamatan dan asuransi medis pelari.'],
                ['question' => 'Apakah tersedia penitipan barang (Baggage Drop)?', 'answer' => 'Ya, panitia menyediakan fasilitas baggage drop resmi di area venue lomba untuk barang esensial non-berharga.'],
                ['question' => 'Bagaimana jika event dibatalkan karena cuaca ekstrim?', 'answer' => 'Keputusan keselamatan merujuk pada standar medis & kepolisian dengan pengumuman berkala kepada seluruh peserta.']
            ];
        }

        $faqSchema = null;
        if (!empty($rawFaqs)) {
            $faqEntities = [];
            foreach ($rawFaqs as $f) {
                if (!empty($f['question']) && !empty($f['answer'])) {
                    $faqEntities[] = [
                        '@type' => 'Question',
                        'name' => strip_tags($f['question']),
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags($f['answer']),
                        ],
                    ];
                }
            }
            if (!empty($faqEntities)) {
                $faqSchema = [
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqEntities,
                ];
            }
        }
    @endphp

    <script type="application/ld+json">
    {!! json_encode($eventSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @if($faqSchema)
    <script type="application/ld+json">
    {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif

    <!-- Favicon -->
    @php
        $faviconUrl = $event->getFaviconUrl();
    @endphp
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconUrl }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">

    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="app-url" content="{{ url('/') }}" />
    <script>
        window.APP_URL = @json(url('/'));
        window.rlUrl = function(path) {
            var base = window.APP_URL || (window.location.origin || '');
            var normalizedBase = String(base).replace(/\/+$/, '') + '/';
            var normalizedPath = String(path || '').replace(/^\/+/, '');
            return new URL(normalizedPath, normalizedBase).toString();
        };
    </script>

    @if(env('RECAPTCHA_SITE_KEY_v3'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}"></script>
    @endif

    <!-- Fonts: Inter Tight & Sora (Headings pakem), Plus Jakarta Sans (Body), JetBrains Mono (Numbers) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6.5.1 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        // Master Event Palette Setup
        $themeColors = is_array($event->theme_colors) ? $event->theme_colors : (is_string($event->theme_colors) ? json_decode($event->theme_colors, true) : []);
        if (!is_array($themeColors)) {
            $themeColors = [];
        }

        $primaryColor = !empty($themeColors['primary']) ? $themeColors['primary'] : '#f1631e';
        $primaryDark = !empty($themeColors['primary_dark']) ? $themeColors['primary_dark'] : null;
        $accentColor = !empty($themeColors['accent']) ? $themeColors['accent'] : (!empty($themeColors['secondary']) ? $themeColors['secondary'] : '#f97316');
        $darkColor = !empty($themeColors['dark']) ? $themeColors['dark'] : '#0f172a';

        // Calculate RGB & Dark Fallbacks
        $hexToRgb = function($hex) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }
            if (strlen($hex) !== 6) return [241, 99, 30];
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            ];
        };
        $rgb = $hexToRgb($primaryColor);
        $primaryRgb = implode(',', $rgb);
        $primaryDarkCalculated = sprintf('#%02x%02x%02x', max(0, (int)($rgb[0] * 0.8)), max(0, (int)($rgb[1] * 0.8)), max(0, (int)($rgb[2] * 0.8)));

        $paymentConfig = $event->payment_config ?? [];
        if (isset($paymentConfig['allowed_methods']) && is_array($paymentConfig['allowed_methods'])) {
            $allowed = $paymentConfig['allowed_methods'];
            $showMidtrans = in_array('midtrans', $allowed) || in_array('all', $allowed);
            $showMoota = in_array('moota', $allowed) || in_array('all', $allowed);
            $showCOD = in_array('cod', $allowed) || in_array('all', $allowed);
            $showManualTransfer = in_array('manual_transfer', $allowed) || in_array('all', $allowed);
        } else {
            $showMidtrans = $paymentConfig['midtrans'] ?? true;
            $showMoota = $paymentConfig['moota'] ?? false;
            $showCOD = $paymentConfig['cod'] ?? false;
            $showManualTransfer = $paymentConfig['manual_transfer'] ?? false;
        }

        if (!$showMidtrans && !$showMoota && !$showCOD && !$showManualTransfer) {
            $showMidtrans = true;
        }

        $midtransDemoMode = filter_var($paymentConfig['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
        $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');

        $now = now();
        $isComingSoon = ($event->registration_open_at && $now < $event->registration_open_at);
        $isNaturallyClosed = ($event->registration_close_at && $now > $event->registration_close_at);
        $isRegOpen = !$isComingSoon && !$isNaturallyClosed;

        $countdownTarget = $event->start_at;
        $countdownLabel = 'Event Dimulai Dalam';
        if ($isComingSoon) {
            $countdownTarget = $event->registration_open_at;
            $countdownLabel = 'Pendaftaran Dibuka Dalam';
        } elseif ($isRegOpen && $event->registration_close_at) {
            $countdownTarget = $event->registration_close_at;
            $countdownLabel = 'Pendaftaran Ditutup Dalam';
        }

        // Precompute Jersey Stock
        $jerseyStockData = [];
        $event->load('jerseyStock');
        if ($event->jerseyStock) {
            $jerseySizesList = $event->jersey_sizes ?? ['XS','S','M','L','XL','2XL','3XL'];
            foreach ($jerseySizesList as $sz) {
                $col = strtolower($sz);
                $quota = $event->jerseyStock->$col ?? null;
                if ($quota !== null) {
                    $checkSizes = [strtoupper(trim($sz))];
                    if (strtoupper(trim($sz)) === '2XL' || strtoupper(trim($sz)) === 'XXL') {
                        $checkSizes = ['2XL', 'XXL'];
                    } elseif (strtoupper(trim($sz)) === '3XL' || strtoupper(trim($sz)) === 'XXXL') {
                        $checkSizes = ['3XL', 'XXXL'];
                    }

                    $usedCount = \App\Models\Participant::whereNotNull('jersey_size')
                        ->whereIn(\DB::raw('UPPER(TRIM(jersey_size))'), $checkSizes)
                        ->whereHas('transaction', fn($q) => $q->where('event_id', $event->id)->whereIn('payment_status', ['paid','cod']))
                        ->count();
                    $jerseyStockData[$sz] = ['quota' => (int) $quota, 'remaining' => max(0, (int)$quota - $usedCount)];
                }
            }
        }

        $formFields = $event->premium_amenities['form_fields'] ?? [];
        $showTargetTime = !empty($formFields['target_time']);
        $showStravaField = !empty($formFields['strava_activity']) || !empty($formFields['strava_url']);

        $ticketDate = $event->start_at
            ? \Carbon\Carbon::parse($event->start_at)->isoFormat('D MMM Y, HH:mm')
            : ($event->date ? \Carbon\Carbon::parse($event->date)->isoFormat('D MMM Y') : '-');
        $ticketLocation = $event->location_name ?? $event->city ?? '-';
    @endphp

    @if($showMidtrans && $midtransClientKey)
        <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
    @endif

    <style>
        /* Dynamic Theme Palette from Master Event */
        :root {
            --theme-primary: {{ $primaryColor }};
            --theme-primary-rgb: {{ $primaryRgb }};
            --theme-primary-dark: {{ $primaryDark ?? $primaryDarkCalculated }};
            --theme-primary-light: rgba({{ $primaryRgb }}, 0.1);
            --theme-primary-surface: rgba({{ $primaryRgb }}, 0.04);
            --theme-primary-border: rgba({{ $primaryRgb }}, 0.22);
            --theme-primary-ring: rgba({{ $primaryRgb }}, 0.2);
            --theme-accent: {{ $accentColor }};
            --theme-dark: {{ $darkColor }};
        }

        @supports (background-color: color-mix(in srgb, red 50%, white)) {
            :root {
                --theme-primary-dark: {{ $primaryDark ?? 'color-mix(in srgb, ' . $primaryColor . ' 82%, black)' }};
                --theme-primary-light: color-mix(in srgb, {{ $primaryColor }} 12%, #ffffff);
                --theme-primary-surface: color-mix(in srgb, {{ $primaryColor }} 5%, #ffffff);
                --theme-primary-border: color-mix(in srgb, {{ $primaryColor }} 28%, #cbd5e1);
                --theme-primary-ring: color-mix(in srgb, {{ $primaryColor }} 22%, transparent);
            }
        }

        /* Dynamic Theme Utilities */
        .bg-theme-primary { background-color: var(--theme-primary) !important; color: #ffffff !important; }
        .hover-bg-theme-primary:hover { background-color: var(--theme-primary-dark) !important; }
        .bg-theme-light { background-color: var(--theme-primary-light) !important; }
        .bg-theme-surface { background-color: var(--theme-primary-surface) !important; }

        .text-theme-primary { color: var(--theme-primary) !important; }
        .hover-text-theme-primary:hover { color: var(--theme-primary-dark) !important; }

        .border-theme-primary { border-color: var(--theme-primary) !important; }
        .border-theme-light { border-color: var(--theme-primary-border) !important; }

        /* Dynamic Overrides for Included Partials */
        #prizes-section .prize-tab-btn[aria-selected="true"] {
            background-color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
            color: #ffffff !important;
        }

        #termsModal:not(.hidden),
        #imageLightbox:not(.hidden),
        #confirmationModal:not(.hidden),
        #registrationFailureModal:not(.hidden),
        #registrationSuccessModal:not(.hidden) {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        #vue-participants-app .bg-blue-600 {
            background-color: var(--theme-primary) !important;
            border-color: var(--theme-primary) !important;
            color: #ffffff !important;
        }
        #vue-participants-app .text-blue-600 {
            color: var(--theme-primary) !important;
        }
        #vue-participants-app .text-blue-700 {
            color: var(--theme-primary-dark) !important;
        }
        #vue-participants-app .bg-blue-50 {
            background-color: var(--theme-primary-light) !important;
        }
        #vue-participants-app .border-blue-100 {
            border-color: var(--theme-primary-border) !important;
        }
        #vue-participants-app input:focus,
        #vue-participants-app select:focus {
            border-color: var(--theme-primary) !important;
            box-shadow: 0 0 0 3px var(--theme-primary-ring) !important;
        }

        /* Typography Pakem */
        .font-heading {
            font-family: 'Inter Tight', 'Sora', sans-serif !important;
            font-weight: 800 !important;
            letter-spacing: -0.03em !important;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 5.5rem;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc; /* bg-slate-50 */
            color: #0f172a; /* text-slate-900 */
            overflow-x: hidden;
        }

        /* Light theme inputs with dynamic focus */
        .input-light {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem;
            color: #0f172a;
            font-size: 0.875rem;
            transition: all 0.15s ease-in-out;
        }
        .input-light:focus {
            background-color: #ffffff;
            border-color: var(--theme-primary) !important;
            outline: none;
            box-shadow: 0 0 0 3px var(--theme-primary-ring) !important;
        }
        .input-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
        .input-error:focus {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2) !important;
        }
        .field-error-msg {
            animation: fadeInError 0.18s ease-out;
        }
        @keyframes fadeInError {
            from { opacity: 0; transform: translateY(-3px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .input-success {
            border-color: #10b981 !important;
        }

        /* Category Radio Card Active State */
        .cat-radio:checked + div {
            border-color: var(--theme-primary) !important;
            background-color: var(--theme-primary-surface) !important;
            box-shadow: 0 0 0 1px var(--theme-primary) !important;
        }
        .cat-radio:checked + div .cat-price {
            color: var(--theme-primary) !important;
        }

        /* Accent for Inputs */
        input[type="checkbox"]:checked,
        input[type="radio"]:checked {
            accent-color: var(--theme-primary) !important;
        }

        /* Navbar elevation shadows */
        #navbar {
            box-shadow: 0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px -1px rgba(15, 23, 42, 0.04);
        }
        .nav-scrolled {
            background-color: rgba(255, 255, 255, 0.98) !important;
            box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.07) !important;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        @if(env('RECAPTCHA_SITE_KEY_v3'))
        .grecaptcha-badge { visibility: hidden !important; }
        @endif
    </style>
</head>
<body class="antialiased flex flex-col min-h-screen overflow-x-hidden max-w-full">

    @if(!$isRegOpen && !$isNaturallyClosed)
    <!-- Maintenance / Coming Soon Screen -->
    <div class="fixed inset-0 z-[100] bg-white overflow-y-auto custom-scrollbar flex flex-col items-center justify-center p-6 text-center">
        <div class="max-w-xl mx-auto w-full">
            @if($event->logo_image)
                <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-20 w-auto mx-auto mb-6">
            @else
                <div class="w-16 h-16 rounded-md bg-theme-primary text-white font-heading text-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                    {{ substr($event->name, 0, 1) }}
                </div>
            @endif

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase tracking-wider mb-4">
                Pendaftaran Belum Dibuka
            </span>

            <h1 class="text-3xl sm:text-4xl font-heading text-slate-900 mb-4">{{ $event->name }}</h1>
            <p class="text-slate-600 text-sm leading-relaxed mb-8 max-w-md mx-auto">
                {{ strip_tags($event->short_description ?: 'Pendaftaran event ini akan segera dibuka. Siapkan diri Anda untuk mengamankan slot.') }}
            </p>

            <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 mb-8 text-left space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500">Tanggal Pelaksanaan</span>
                    <span class="font-bold text-slate-900">{{ $event->start_at ? $event->start_at->format('d F Y') : '-' }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-500">Lokasi Venue</span>
                    <span class="font-bold text-slate-900">{{ $event->location_name ?? 'To Be Announced' }}</span>
                </div>
                @if($event->registration_open_at)
                <div class="flex justify-between items-center text-sm border-t border-slate-200 pt-3">
                    <span class="text-slate-500">Jadwal Buka</span>
                    <span class="font-bold text-theme-primary">{{ $event->registration_open_at->format('d M Y, H:i') }} WIB</span>
                </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('community.register.index', ['slug' => $event->slug]) }}" class="px-6 py-2.5 rounded-md border border-slate-300 hover:border-theme-primary bg-slate-50 hover:bg-theme-light text-slate-800 hover-text-theme-primary font-bold text-sm transition">
                    Daftar via Komunitas
                </a>
                <a href="{{ url('/') }}" class="px-6 py-2.5 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold text-sm transition">
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Navigation Bar -->
    <header class="fixed top-0 inset-x-0 z-50 transition duration-200 bg-white/95 backdrop-blur-md shadow-xs sm:shadow-sm" id="navbar">
        <div class="max-w-7xl 2xl:max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-3 lg:gap-6 xl:gap-8">
                <!-- Brand / Logo -->
                <a href="#top" class="flex items-center gap-2.5 sm:gap-3 shrink-0 group">
                    @if($event->logo_image)
                        <img src="{{ asset('storage/' . $event->logo_image) }}" alt="{{ $event->name }}" class="h-9 sm:h-11 w-auto object-contain shrink-0">
                    @else
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-md bg-theme-primary text-white font-heading text-lg flex items-center justify-center shadow-sm shrink-0 group-hover:opacity-95 transition">
                            {{ substr($event->name, 0, 1) }}
                        </div>
                        <span class="font-heading text-base sm:text-lg xl:text-xl text-slate-900 uppercase tracking-tight truncate max-w-[150px] sm:max-w-[220px] lg:max-w-[180px] xl:max-w-xs shrink-0 group-hover:text-theme-primary transition">
                            {{ $event->name }}
                        </span>
                    @endif
                </a>

                <!-- Desktop Menu -->
                <nav class="hidden lg:flex items-center justify-center flex-1 mx-2 lg:mx-4 xl:mx-8 gap-1 lg:gap-1.5 xl:gap-2.5 2xl:gap-4">
                    <a href="#about" class="px-2.5 lg:px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-md text-xs xl:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 hover-text-theme-primary transition duration-150 whitespace-nowrap">
                        Tentang
                    </a>
                    <a href="#categories" class="px-2.5 lg:px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-md text-xs xl:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 hover-text-theme-primary transition duration-150 whitespace-nowrap">
                        Kategori
                    </a>
                    <a href="#benefits" class="px-2.5 lg:px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-md text-xs xl:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 hover-text-theme-primary transition duration-150 whitespace-nowrap">
                        Benefit Peserta
                    </a>
                    @if(!empty($gpxDataList))
                        <a href="#route" class="px-2.5 lg:px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-md text-xs xl:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 hover-text-theme-primary transition duration-150 whitespace-nowrap">
                            Rute & Elevasi
                        </a>
                    @endif
                    <a href="#venue" class="px-2.5 lg:px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-md text-xs xl:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 hover-text-theme-primary transition duration-150 whitespace-nowrap">
                        Lokasi
                    </a>
                    <a href="#info" class="px-2.5 lg:px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-md text-xs xl:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 hover-text-theme-primary transition duration-150 whitespace-nowrap">
                        Info
                    </a>
                    <a href="#faq" class="px-2.5 lg:px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-md text-xs xl:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 hover-text-theme-primary transition duration-150 whitespace-nowrap">
                        FAQ
                    </a>
                    @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
                        <a href="#participants-list" class="px-2.5 lg:px-3 xl:px-3.5 py-1.5 xl:py-2 rounded-md text-xs xl:text-sm font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-100 hover-text-theme-primary transition duration-150 whitespace-nowrap">
                            Daftar Peserta
                        </a>
                    @endif
                </nav>

                <!-- Actions -->
                <div class="hidden sm:flex items-center gap-2 xl:gap-3 shrink-0">                    
                    @if($isRegOpen)
                        <a href="#register" class="px-4 xl:px-5 py-2 xl:py-2.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white text-xs font-bold transition shadow-sm whitespace-nowrap">
                            Daftar Sekarang
                        </a>
                    @elseif($isNaturallyClosed)
                        <a href="#register" class="px-4 xl:px-5 py-2 xl:py-2.5 rounded-md bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-sm whitespace-nowrap">
                            Slot Penuh
                        </a>
                    @else
                        <span class="px-3 xl:px-4 py-2 rounded-md bg-slate-100 text-slate-400 text-xs font-bold border border-slate-200 whitespace-nowrap">
                            Pendaftaran Tutup
                        </span>
                    @endif
                </div>

                <!-- Mobile Menu Button -->
                <button type="button" id="mobileMenuBtn" aria-label="Buka Menu" class="lg:hidden p-2 rounded-md text-slate-600 hover:bg-slate-100 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobileMenu" class="hidden lg:hidden border-t border-slate-200 bg-white px-4 py-4 space-y-3 shadow-lg">
            <a href="#about" class="block text-sm font-semibold text-slate-700 p-2 hover:bg-slate-50 rounded-md">Tentang Event</a>
            <a href="#categories" class="block text-sm font-semibold text-slate-700 p-2 hover:bg-slate-50 rounded-md">Kategori Lomba</a>
            <a href="#benefits" class="block text-sm font-semibold text-slate-700 p-2 hover:bg-slate-50 rounded-md">Benefit Peserta</a>
            @if(!empty($gpxDataList))
                <a href="#route" class="block text-sm font-semibold text-slate-700 p-2 hover:bg-slate-50 rounded-md">Rute & Elevasi</a>
            @endif
            <a href="#venue" class="block text-sm font-semibold text-slate-700 p-2 hover:bg-slate-50 rounded-md">Lokasi & Venue</a>
            <a href="#info" class="block text-sm font-semibold text-slate-700 p-2 hover:bg-slate-50 rounded-md">Info & Jadwal</a>
            <a href="#faq" class="block text-sm font-semibold text-slate-700 p-2 hover:bg-slate-50 rounded-md">FAQ</a>
            @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
                <a href="#participants-list" class="block text-sm font-semibold text-slate-700 p-2 hover:bg-slate-50 rounded-md">Daftar Peserta</a>
            @endif
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                <a href="{{ route('community.register.index', ['slug' => $event->slug]) }}" class="block text-center py-2.5 rounded-md border border-slate-300 hover:border-theme-primary bg-slate-50 text-slate-700 text-xs font-bold">
                    Daftar via Komunitas
                </a>
                @if($isRegOpen)
                    <a href="#register" class="block text-center py-2.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white text-xs font-bold">
                        Daftar Individu / Multi
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main id="top" class="flex-grow pt-0">
        <!-- Hero Section -->
        <section class="relative pt-24 pb-16 md:pt-32 md:pb-24 bg-white border-b border-slate-200 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Hero Details -->
                    <div class="lg:col-span-7 space-y-6">
                        <!-- Top Meta Chips -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded text-xs font-bold bg-theme-light text-theme-primary border border-theme-light">
                                <span class="w-2 h-2 rounded-full bg-theme-primary"></span>
                                {{ $event->start_at ? $event->start_at->format('d F Y') : 'Tanggal Diumumkan' }}
                            </span>
                            @if($event->location_name)
                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                <i class="fas fa-map-marker-alt text-slate-400 mr-1.5"></i>
                                {{ $event->location_name }}
                            </span>
                            @endif
                            @if($isRegOpen)
                            <span class="inline-flex items-center px-3 py-1 rounded text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                Kuota Terbatas
                            </span>
                            @endif
                        </div>

                        <!-- H1 Heading -->
                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-heading text-slate-900 tracking-tight leading-[1.05]">
                            {{ strtoupper($event->name) }}
                        </h1>

                        <!-- Description -->
                        <p class="text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl">
                            {!! $event->short_description ?: 'Bergabunglah dalam event lari resmi yang kompetitif, dan aman di rute terbaik.' !!}
                        </p>

                        <!-- CTA Row -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            @if($isRegOpen)
                                <a href="#register" class="px-8 py-3.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white font-bold text-sm transition shadow-sm flex items-center gap-2">
                                    <span>Amankan Slot Sekarang</span>
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            @elseif($isNaturallyClosed)
                                <a href="#register" class="px-8 py-3.5 rounded-md bg-red-600 hover:bg-red-700 text-white font-bold text-sm transition shadow-sm">
                                    Cek Ketersediaan Slot
                                </a>
                            @else
                                <button disabled class="px-7 py-3.5 rounded-md bg-slate-200 text-slate-400 font-bold text-sm cursor-not-allowed">
                                    Pendaftaran Ditutup
                                </button>
                            @endif
                         
                            <a href="#about" class="px-5 py-3.5 rounded-md text-slate-600 hover:text-slate-900 font-semibold text-sm transition">
                                Pelajari Acara
                            </a>
                        </div>

                        <!-- Countdown Timer Container -->
                        @if($countdownTarget)
                        <div class="pt-6 border-t border-slate-100">
                            <span class="block text-xs font-bold uppercase text-slate-500 tracking-wider mb-3">
                                {{ $countdownLabel }}
                            </span>
                            <div class="grid grid-cols-4 gap-2 sm:gap-4 max-w-md">
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
                                    <span class="block text-2xl sm:text-3xl font-heading text-slate-900 font-mono" id="cd-days">00</span>
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase">Hari</span>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
                                    <span class="block text-2xl sm:text-3xl font-heading text-slate-900 font-mono" id="cd-hours">00</span>
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase">Jam</span>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
                                    <span class="block text-2xl sm:text-3xl font-heading text-slate-900 font-mono" id="cd-minutes">00</span>
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase">Menit</span>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-center">
                                    <span class="block text-2xl sm:text-3xl font-heading text-theme-primary font-mono" id="cd-seconds">00</span>
                                    <span class="text-[11px] font-semibold text-slate-500 uppercase">Detik</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Hero Visual Card -->
                    <div class="lg:col-span-5">
                        <div class="bg-white border border-slate-200 rounded-lg p-3 shadow-sm relative">
                            <div class="aspect-[4/3] rounded-md overflow-hidden bg-slate-100 border border-slate-100 relative {{ $event->hero_image ? 'cursor-pointer group' : '' }}" @if($event->hero_image) onclick="openLightbox('{{ asset('storage/' . $event->hero_image) }}')" title="Klik untuk memperbesar" @endif>
                                @if($event->hero_image)
                                    <img src="{{ asset('storage/' . $event->hero_image) }}" alt="{{ $event->name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                    <div class="absolute bottom-3 right-3 bg-slate-900/80 hover:bg-slate-900 text-white w-8 h-8 rounded-md flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition shadow-sm pointer-events-none">
                                        <i class="fas fa-search-plus text-[11px]"></i>
                                    </div>
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 font-bold bg-slate-100">
                                        Foto Resmi Event
                                    </div>
                                @endif
                                <div class="absolute top-3 left-3 bg-white/95 border border-slate-200 px-3 py-1 rounded text-xs font-bold text-slate-800 shadow-sm pointer-events-none">
                                    Official Race
                                </div>
                            </div>

                            <!-- Fast telemetry summary bar -->
                            <div class="grid grid-cols-3 gap-3 p-4 bg-slate-50 rounded-md border border-slate-200 mt-3 text-center">
                                <div>
                                    <span class="block text-[11px] text-slate-500 uppercase font-semibold">Kategori</span>
                                    <span class="text-lg font-heading text-slate-900">{{ $categories->count() }} Pilihan</span>
                                </div>
                                <div class="border-x border-slate-200">
                                    <span class="block text-[11px] text-slate-500 uppercase font-semibold">Flag Off</span>
                                    <span class="text-lg font-heading text-slate-900">{{ $event->start_at ? $event->start_at->format('H:i') : 'TBA' }} WIB</span>
                                </div>
                                <div>
                                    <span class="block text-[11px] text-slate-500 uppercase font-semibold">Peserta</span>
                                    <span class="text-lg font-heading text-theme-primary">Terbuka</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Section: Tentang Event -->
        <section id="about" class="py-20 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mb-12">
                    <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Profil & Visi Event</span>
                    <h2 class="text-3xl sm:text-4xl font-heading text-slate-900 mt-1">Tentang {{ $event->name }}</h2>
                    <p class="text-slate-600 text-sm sm:text-base leading-relaxed mt-4">
                        {!! $event->full_description ?: $event->short_description ?: 'Event lari ini didesain untuk menghadirkan pengalaman kompetisi yang adil, jalur aman terarah, serta fasilitas pelari yang lengkap dan berkualitas tinggi.' !!}
                    </p>
                </div>

                <!-- 4 Highlight Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-4">
                            <i class="fas fa-tint text-base"></i>
                        </div>
                        <h3 class="text-base font-heading text-slate-900 mb-1">Water Station Berkala</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pos hidrasi terjamin di setiap kilometer utama dengan air mineral segar dan tim marshall siaga.
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-4">
                            <i class="fas fa-heartbeat text-base"></i>
                        </div>
                        <h3 class="text-base font-heading text-slate-900 mb-1">Dukungan Medis Siaga</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Ambulans, paramedis profesional, dan tim pertolongan pertama di rute serta area garis finish.
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-4">
                            <i class="fas fa-stopwatch text-base"></i>
                        </div>
                        <h3 class="text-base font-heading text-slate-900 mb-1">Pencatatan Waktu Akurat</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Hasil waktu resmi yang diverifikasi transparan oleh juri kompetisi dan panitia teknis.
                        </p>
                    </div>

                    <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                        <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-4">
                            <i class="fas fa-award text-base"></i>
                        </div>
                        <h3 class="text-base font-heading text-slate-900 mb-1">Medali & Finisher Pack</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Medali finisher logam cetak timbul dan race pack lengkap bagi peserta yang menyelesaikan lomba.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section: Kategori Lomba -->
        <section id="categories" class="py-20 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Pilihan Jarak Lomba</span>
                        <h2 class="text-3xl sm:text-4xl font-heading text-slate-900 mt-1">Kategori Lomba</h2>
                        <p class="text-slate-600 text-sm mt-2">Pilih tantangan yang sesuai dengan target kecepatan dan jarak Anda.</p>
                    </div>
                    @if($isRegOpen)
                    <a href="#register" class="text-xs font-bold text-theme-primary hover-text-theme-primary transition">
                        Daftar Kategori Sekarang &rarr;
                    </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($categories as $cat)
                    @php
                        $priceRegular = (int) ($cat->price_regular ?? 0);
                        $priceEarly = (int) ($cat->price_early ?? 0);
                        $priceLate = (int) ($cat->price_late ?? 0);
                        $displayPrice = $priceRegular;
                        if ($priceEarly > 0) {
                            $displayPrice = $priceEarly;
                        } elseif ($priceLate > 0) {
                            $displayPrice = $priceLate;
                        }
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm hover:border-theme-primary transition flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <span class="px-2.5 py-1 rounded text-xs font-heading bg-slate-900 text-white">
                                    {{ $cat->distance_km ?? 0 }} KM
                                </span>
                                <div class="text-right">
                                    @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                        <span class="text-xs text-slate-400 line-through block font-mono">
                                            Rp {{ number_format($priceRegular, 0, ',', '.') }}
                                        </span>
                                    @endif
                                    <span class="text-lg font-heading text-theme-primary font-mono cat-price">
                                        Rp {{ number_format($displayPrice, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            <h3 class="text-xl font-heading text-slate-900 mb-2">{{ $cat->name }}</h3>
                            <p class="text-xs text-slate-500 mb-6">
                                Start: {{ $cat->start_time ? \Carbon\Carbon::parse($cat->start_time)->format('H:i') : 'TBA' }} WIB
                            </p>

                            <div class="space-y-2 border-t border-slate-100 pt-4 text-xs text-slate-600">
                                <div class="flex justify-between">
                                    <span>Cut Off Time (COT)</span>
                                    <span class="font-bold text-slate-900 font-mono">{{ $cat->cot_hours ?? '-' }} Jam</span>
                                </div>                                                            
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100">
                            @if($isRegOpen)
                                <a href="#register" class="w-full block text-center py-2.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white font-bold text-xs transition shadow-sm">
                                    Pilih {{ $cat->name }}
                                </a>
                            @else
                                <button disabled class="w-full py-2.5 rounded-md bg-slate-100 text-slate-400 font-bold text-xs cursor-not-allowed">
                                    Tutup
                                </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Section: Benefit untuk Peserta -->
        <section id="benefits" class="py-20 bg-slate-50 border-b border-slate-200 scroll-mt-16 relative">
            <span id="racepack" class="absolute -top-16 sr-only"></span>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-14">
                    <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Fasilitas & Kelengkapan Lari</span>
                    <h2 class="text-3xl sm:text-4xl font-heading text-slate-900 mt-1">Benefit untuk Peserta</h2>
                    <p class="text-slate-600 text-sm mt-2">Seluruh peserta resmi berhak mendapatkan paket benefit eksklusif perlombaan untuk mendukung kenyamanan dan performa terbaik.</p>
                </div>

                <!-- 6 Highlight Benefit Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-5 mb-10">
                    <!-- 1. Medali -->
                    <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm hover:border-theme-primary transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-3.5">
                                <i class="fas fa-medal text-base"></i>
                            </div>
                            <h3 class="text-base font-heading text-slate-900 mb-1.5">Medali Finisher</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Medali cetak logam cor eksklusif dengan pita bergradien warna untuk seluruh peserta yang menyelesaikan rute lomba dalam batas waktu Cut Off Time (COT).
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-theme-primary uppercase">All Finisher COT</span>
                        </div>
                    </div>

                    <!-- 2. Jersey -->
                    <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm hover:border-theme-primary transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-3.5">
                                <i class="fas fa-tshirt text-base"></i>
                            </div>
                            <h3 class="text-base font-heading text-slate-900 mb-1.5">Jersey Resmi</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Jersey lari berbahan Dry-Fit premium berpori mikro yang ringan, sejuk di iklim tropis, dan dirancang khusus mengikuti pola gerak atletik.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-theme-primary uppercase">Dry-Fit Atletik</span>
                        </div>
                    </div>

                    <!-- 3. Refreshment -->
                    <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm hover:border-theme-primary transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-3.5">
                                <i class="fas fa-tint text-base"></i>
                            </div>
                            <h3 class="text-base font-heading text-slate-900 mb-1.5">Refreshment</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Pos hidrasi berkala berisi air mineral segar dan isotonik di sepanjang rute, serta camilan pemulihan nutrisi di tenda garis finish.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-theme-primary uppercase">Rute & Garis Finish</span>
                        </div>
                    </div>

                    <!-- 4. Nomor BIB -->
                    <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm hover:border-theme-primary transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-3.5">
                                <i class="fas fa-id-card text-base"></i>
                            </div>
                            <h3 class="text-base font-heading text-slate-900 mb-1.5">Nomor BIB</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Nomor dada cetak presisi tahan air sebagai identitas atlet resmi yang memuat nama pelari, kategori lomba, dan data darurat.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-theme-primary uppercase">Nomor Dada Resmi</span>
                        </div>
                    </div>

                    <!-- 5. Dokumentasi -->
                    <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm hover:border-theme-primary transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-3.5">
                                <i class="fas fa-camera text-base"></i>
                            </div>
                            <h3 class="text-base font-heading text-slate-900 mb-1.5">Dokumentasi</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Foto aksi lari dan momen berharga di sepanjang jalur serta garis finish oleh tim fotografer profesional, dapat diunduh gratis pasca-event.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-theme-primary uppercase">Akses Foto Gratis</span>
                        </div>
                    </div>

                    <!-- 6. Race Pack -->
                    <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm hover:border-theme-primary transition duration-150 flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-md bg-theme-light text-theme-primary flex items-center justify-center mb-3.5">
                                <i class="fas fa-box-open text-base"></i>
                            </div>
                            <h3 class="text-base font-heading text-slate-900 mb-1.5">Race Pack</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Paket perlengkapan lari eksklusif berupa tas serut (drawstring bag), produk suplemen, voucher mitra, dan merchandise pendukung dari sponsor.
                            </p>
                        </div>
                        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-bold text-theme-primary uppercase">Paket Eksklusif RPC</span>
                        </div>
                    </div>
                </div>

                <!-- Showcase Jersey & Dokumen Tambahan -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- Jersey Showcase Card -->
                    <div class="lg:col-span-5 bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="text-xs font-bold text-theme-primary uppercase">Desain Perlengkapan</span>
                                <h3 class="text-xl font-heading text-slate-900">Official Running Jersey</h3>
                            </div>
                            <button type="button" onclick="openLightbox('https://ruanglari.com/storage/blog/media/SEIthtxRb1p8CPI9wjjYfkiWYzcuzFek7tTVbrqq.webp')" class="text-xs font-bold text-theme-primary hover:underline">
                                Panduan Ukuran
                            </button>
                        </div>
                        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                            Bahan Dry-Fit bernapas tinggi yang nyaman untuk iklim tropis, dirancang khusus dengan pola gerak atletik.
                        </p>
                        <div class="aspect-square rounded-md bg-slate-100 border border-slate-200 overflow-hidden relative cursor-pointer" onclick="openLightbox('{{ $event->jersey_image ? asset('storage/' . $event->jersey_image) : 'https://ruanglari.com/storage/blog/media/SEIthtxRb1p8CPI9wjjYfkiWYzcuzFek7tTVbrqq.webp' }}')">
                            @if($event->jersey_image)
                                <img src="{{ asset('storage/' . $event->jersey_image) }}" alt="Official Jersey" class="w-full h-full object-contain p-4 hover:scale-105 transition duration-300">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center text-slate-400">
                                    <i class="fas fa-tshirt text-4xl mb-2"></i>
                                    <span class="text-xs font-bold text-slate-500">Preview Desain Jersey Resmi</span>
                                    <span class="text-[11px] text-slate-400 mt-1">Klik untuk melihat bagan ukuran</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Additional Entitlements & Documents -->
                    <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                            <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center mb-3">
                                <i class="fas fa-file-invoice text-sm"></i>
                            </div>
                            <h4 class="text-sm font-heading text-slate-900 mb-1">E-Ticket & E-Certificate</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Tiket digital instan ber-QR code dan sertifikat catatan waktu resmi yang dapat diunduh langsung setelah balapan berakhir.
                            </p>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm">
                            <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center mb-3">
                                <i class="fas fa-box-open text-sm"></i>
                            </div>
                            <h4 class="text-sm font-heading text-slate-900 mb-1">Pengambilan Race Pack (RPC)</h4>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Pengambilan paket perlengkapan dilakukan sesuai jadwal dengan membawa identitas resmi (KTP/SIM) dan E-Ticket terdaftar.
                            </p>
                        </div>

                        <!-- Documents Download Card -->
                        <div class="sm:col-span-2 bg-theme-light border border-theme-light rounded-lg p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-bold text-theme-primary uppercase">Dokumen Peserta</span>
                                <h4 class="text-sm font-heading text-slate-900 mt-0.5">Surat Kuasa</h4>
                                <p class="text-xs text-slate-600 mt-1">Surat kuasa untuk pengambilan racepack oleh orang lain.</p>
                            </div>
                            <a href="https://res.cloudinary.com/dqm0gzjmu/raw/upload/v1791536264/Surat-Kuasa-Polkesma_hlyx7w.docx" class="shrink-0 px-4 py-2 rounded-md bg-white border border-slate-300 hover:bg-slate-50 text-slate-800 text-xs font-bold transition flex items-center justify-center gap-2">
                                <i class="fas fa-download text-xs text-slate-500"></i>
                                <span>Unduh Formulir</span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        @if(!empty($gpxDataList))
        <!-- Section: Rute Lomba & Profil Elevasi (GPX) -->
        <section id="route" class="py-20 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <!-- Section Header -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Navigasi Lomba</span>
                        <h2 class="text-3xl font-heading text-slate-900 mt-1">Rute Resmi & Profil Elevasi</h2>
                        <p class="text-slate-600 text-sm mt-1.5 max-w-2xl">
                            Jalur lomba terverifikasi dengan profil ketinggian presisi untuk membantu strategi pacing dan kesiapan fisik Anda.
                        </p>
                    </div>

                    <!-- Category Switcher Tabs & Database GPX Link -->
                    <div class="shrink-0 flex flex-wrap items-center gap-2">
                        @if(count($gpxDataList) > 1)
                            <div class="inline-flex p-1 bg-white border border-slate-200 rounded-md shadow-sm gap-1" id="gpx-category-tabs">
                                @foreach($gpxDataList as $gIdx => $gData)
                                    <button type="button" 
                                            onclick="switchGpxCategory({{ $gIdx }})"
                                            data-gpx-tab-idx="{{ $gIdx }}"
                                            class="gpx-tab-btn px-3 py-1.5 text-xs font-bold rounded-md transition cursor-pointer {{ $gIdx === 0 ? 'bg-theme-primary text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                        {{ $gData['category_name'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($gpxDataList[0]['detail_url']))
                            <a href="{{ $gpxDataList[0]['detail_url'] }}" 
                               id="btn-header-gpx-detail"
                               target="_blank"
                               class="px-3.5 py-1.5 rounded-md bg-white border border-slate-300 hover:border-theme-primary hover-text-theme-primary text-slate-700 text-xs font-bold transition shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                                <i class="fas fa-database text-[11px] text-theme-primary"></i>
                                <span>Database GPX</span>
                                <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                            </a>
                        @endif
                    </div>
                </div>

                @foreach($gpxDataList as $gIdx => $gData)
                <div id="gpx-tab-content-{{ $gIdx }}" class="gpx-tab-pane space-y-6 {{ $gIdx > 0 ? 'hidden' : '' }}">
                    
                    <!-- Telemetry Metrics Grid (4 Cards) -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <div class="bg-white border border-slate-200 rounded-lg p-4 sm:p-5 shadow-sm">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider">Jarak Tempuh</span>
                                <div class="w-7 h-7 rounded-md bg-theme-light text-theme-primary flex items-center justify-center">
                                    <i class="fas fa-route text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-2xl sm:text-3xl font-heading font-black text-slate-900 font-mono">{{ $gData['distance_formatted'] }}</span>
                                <span class="text-xs font-bold text-slate-500">KM</span>
                            </div>
                            <span class="block text-xs text-slate-500 mt-1">Kategori {{ $gData['category_name'] }}</span>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-lg p-4 sm:p-5 shadow-sm">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider">Elevasi Naik</span>
                                <div class="w-7 h-7 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                    <i class="fas fa-arrow-trend-up text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-2xl sm:text-3xl font-heading font-black text-emerald-600 font-mono">+{{ $gData['elevation_gain_m'] }}</span>
                                <span class="text-xs font-bold text-slate-500">m</span>
                            </div>
                            <span class="block text-xs text-slate-500 mt-1">Total Tanjakan (Gain)</span>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-lg p-4 sm:p-5 shadow-sm">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider">Elevasi Turun</span>
                                <div class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center">
                                    <i class="fas fa-arrow-trend-down text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-2xl sm:text-3xl font-heading font-black text-blue-600 font-mono">-{{ $gData['elevation_loss_m'] }}</span>
                                <span class="text-xs font-bold text-slate-500">m</span>
                            </div>
                            <span class="block text-xs text-slate-500 mt-1">Total Turunan (Loss)</span>
                        </div>

                        <div class="bg-white border border-slate-200 rounded-lg p-4 sm:p-5 shadow-sm">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-bold uppercase tracking-wider">Karakter Rute</span>
                                <div class="w-7 h-7 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center">
                                    <i class="fas fa-mountain text-xs"></i>
                                </div>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-2xl sm:text-3xl font-heading font-black text-slate-900 uppercase font-mono">{{ $gData['route_type'] }}</span>
                            </div>
                            <span class="block text-xs text-slate-500 mt-1">Elevasi: {{ $gData['min_elevation'] }}m - {{ $gData['max_elevation'] }}m dpl</span>
                        </div>
                    </div>

                    <!-- Elevation Profile Vector SVG Card (Instant 0KB) -->
                    <div class="bg-white border border-slate-200 rounded-lg p-5 sm:p-6 shadow-sm space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-theme-light text-theme-primary flex items-center justify-center">
                                    <i class="fas fa-chart-area text-xs"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900">Profil Topografi & Kontur Ketinggian</h3>
                            </div>
                            <div class="flex items-center gap-3 text-xs font-mono text-slate-500">
                                <span>Min: <strong class="text-slate-800 font-bold">{{ $gData['min_elevation'] }}m</strong></span>
                                <span>Max: <strong class="text-slate-800 font-bold">{{ $gData['max_elevation'] }}m</strong></span>
                                <span>Gain: <strong class="text-emerald-600 font-bold">+{{ $gData['elevation_gain_m'] }}m</strong></span>
                            </div>
                        </div>

                        <!-- Pure Vector SVG -->
                        <div class="relative w-full h-32 sm:h-36 select-none bg-slate-50 rounded-md border border-slate-100 p-2 overflow-hidden">
                            <svg viewBox="0 0 800 140" preserveAspectRatio="none" class="w-full h-full block">
                                <defs>
                                    <linearGradient id="gpxElevGrad-{{ $gIdx }}" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" stop-color="var(--theme-primary, #059669)" stop-opacity="0.32" />
                                        <stop offset="100%" stop-color="var(--theme-primary, #059669)" stop-opacity="0.02" />
                                    </linearGradient>
                                </defs>
                                <path d="{{ $gData['area_path'] }}" fill="url(#gpxElevGrad-{{ $gIdx }})" />
                                <path d="{{ $gData['line_path'] }}" fill="none" stroke="var(--theme-primary, #059669)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div class="flex items-center justify-between text-[11px] font-mono text-slate-400 px-1">
                            <span>Titik Start (0 KM)</span>
                            <span>Separuh Rute ({{ number_format($gData['distance_km'] / 2, 1) }} KM)</span>
                            <span>Garis Finish ({{ $gData['distance_formatted'] }} KM)</span>
                        </div>
                    </div>

                    <!-- Lazy Loaded Interactive Leaflet Map Card -->
                    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                        <!-- Map Header & Action Toolbar -->
                        <div class="px-5 py-3.5 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded bg-slate-200 text-slate-700 flex items-center justify-center">
                                    <i class="fas fa-map-marked-alt text-xs"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-slate-900">Peta Interaktif Jalur (GPX)</span>
                                    <span class="block text-[11px] text-slate-500">Kategori: {{ $gData['category_name'] }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                @if(!empty($gData['detail_url']))
                                    <a href="{{ $gData['detail_url'] }}" 
                                       target="_blank" 
                                       class="px-3 py-1.5 rounded-md border border-slate-300 hover:border-theme-primary bg-white hover:bg-slate-50 text-slate-700 hover-text-theme-primary text-xs font-bold transition flex items-center gap-1.5 shadow-sm"
                                       title="Buka rute ini di Database GPX RuangLari untuk navigasi GPS live, flyover 3D & PacePro">
                                        <i class="fas fa-arrow-up-right-from-square text-[11px] text-theme-primary"></i>
                                        <span>Buka di Database GPX</span>
                                    </a>
                                @endif
                                @if($gData['download_url'])
                                    <a href="{{ $gData['download_url'] }}" 
                                       download 
                                       class="px-3 py-1.5 rounded-md border border-slate-300 hover:border-theme-primary bg-white hover:bg-slate-50 text-slate-700 hover-text-theme-primary text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                        <i class="fas fa-download text-[11px]"></i>
                                        <span>Unduh GPX</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Map Canvas Container (Lazy Loaded On-Demand) -->
                        <div class="relative w-full h-[420px] bg-slate-900" id="gpx-map-container-{{ $gIdx }}">
                            
                            <!-- Placeholder Before Auto-load / User Click -->
                            <div id="gpx-placeholder-{{ $gIdx }}" class="absolute inset-0 z-10 flex flex-col items-center justify-center p-6 text-center bg-slate-900 text-white">
                                <div class="w-14 h-14 rounded-full bg-slate-800 border border-slate-700 text-theme-primary flex items-center justify-center mb-4 shadow-lg">
                                    <i class="fas fa-map-location-dot text-2xl"></i>
                                </div>
                                <h4 class="text-base font-bold text-white mb-1">Peta Rute {{ $gData['category_name'] }}</h4>
                                <p class="text-xs text-slate-400 max-w-md mb-5 leading-relaxed">
                                    Peta GPS interaktif dimuat otomatis saat rute dibuka untuk menjaga kecepatan browsing dan menghemat kuota data.
                                </p>
                                <div class="flex items-center gap-2 flex-wrap justify-center">
                                    <button type="button" 
                                            onclick="loadGpxMap({{ $gIdx }})" 
                                            id="btn-load-gpx-map-{{ $gIdx }}"
                                            class="px-5 py-2.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white text-xs font-bold transition shadow flex items-center gap-2 cursor-pointer">
                                        <i class="fas fa-play text-xs"></i>
                                        <span>Buka Peta Interaktif</span>
                                    </button>
                                    @if(!empty($gData['detail_url']))
                                        <a href="{{ $gData['detail_url'] }}" 
                                           target="_blank" 
                                           class="px-4 py-2.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 hover:text-white text-xs font-bold transition shadow flex items-center gap-2">
                                            <i class="fas fa-database text-xs text-theme-primary"></i>
                                            <span>Buka di Database GPX</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Actual Map DOM element -->
                            <div id="gpx-live-map-{{ $gIdx }}" class="w-full h-full z-0"></div>
                        </div>
                    </div>

                </div>
                @endforeach

            </div>
        </section>
        @endif

        <!-- Section: Venue & Lokasi (Rute) -->
        <section id="venue" class="py-20 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- RPC Info Panel -->
                    <div class="lg:col-span-5 space-y-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Informasi Lokasi</span>
                            <h2 class="text-3xl font-heading text-slate-900 mt-1">Pengambilan Race Pack & Venue</h2>
                            <p class="text-slate-600 text-sm mt-2">
                                Pastikan membawa identitas diri (KTP/SIM) dan bukti E-Ticket saat pengambilan race pack collection (RPC).
                            </p>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-md bg-theme-light text-theme-primary flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-box text-xs"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase text-slate-500">Lokasi RPC</span>
                                    <span class="text-sm font-bold text-slate-900">{{ $event->rpc_location_name ?? ($event->location_name ?? 'To Be Announced') }}</span>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $event->rpc_location_address ?? ($event->location_address ?? '') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 border-t border-slate-200 pt-3">
                                <div class="w-8 h-8 rounded-md bg-theme-light text-theme-primary flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-clock text-xs"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase text-slate-500">Jadwal Pengambilan</span>
                                    <span class="text-sm font-bold text-slate-900">H-1 Sebelum Hari Lomba</span>
                                    <p class="text-xs text-slate-500 mt-0.5">10:00 - 20:00 WIB</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 border-t border-slate-200 pt-3">
                                <div class="w-8 h-8 rounded-md bg-theme-light text-theme-primary flex items-center justify-center shrink-0 mt-0.5">
                                    <i class="fas fa-flag-checkered text-xs"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold uppercase text-slate-500">Titik Kumpul & Start</span>
                                    <span class="text-sm font-bold text-slate-900">{{ $event->location_name ?? 'Lokasi Utama Acara' }}</span>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $event->location_address ?? '' }}</p>
                                </div>
                            </div>

                            @if($event->location_lat && $event->location_lng)
                            <div class="pt-2">
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $event->location_lat }},{{ $event->location_lng }}" target="_blank" class="w-full py-2.5 rounded-md bg-white border border-slate-300 hover:bg-slate-100 text-slate-800 text-xs font-bold transition flex items-center justify-center gap-2">
                                    <i class="fas fa-directions text-theme-primary"></i>
                                    <span>Petunjuk Arah Google Maps</span>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Map Container -->
                    <div class="lg:col-span-7">
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm h-[400px] relative">
                            @if($event->map_embed_url)
                                <iframe src="{{ $event->map_embed_url }}" class="w-full h-full border-0" allowfullscreen="" loading="lazy"></iframe>
                            @elseif($event->location_lat && $event->location_lng)
                                <iframe src="https://maps.google.com/maps?q={{ $event->location_lat }},{{ $event->location_lng }}&hl=id&z=15&output=embed" class="w-full h-full border-0" allowfullscreen="" loading="lazy"></iframe>
                            @else
                                <div class="w-full h-full bg-slate-100 flex flex-col items-center justify-center p-6 text-center text-slate-400">
                                    <i class="fas fa-map-marked-alt text-4xl mb-3 text-slate-300"></i>
                                    <span class="text-sm font-bold text-slate-600">{{ $event->location_name ?? 'Peta Lokasi' }}</span>
                                    <span class="text-xs text-slate-500 mt-1 max-w-sm">{{ $event->location_address ?? 'Titik peta akan diperbarui oleh panitia penyelenggara.' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Section: Hadiah Pemenang -->
        @include('events.partials.prizes-section', ['categories' => $categories, 'isDark' => false, 'variant' => 'light-clean'])

        <!-- Section: Info, Rundown & FAQ -->
        <section id="info" class="py-20 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    
                    <!-- Rundown Column -->
                    <div class="lg:col-span-5 space-y-6">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Rundown Kegiatan</span>
                            <h2 class="text-2xl sm:text-3xl font-heading text-slate-900 mt-1">Jadwal Race Day</h2>
                            <p class="text-slate-600 text-xs sm:text-sm mt-1">Waktu dapat disesuaikan dengan pengumuman panitia di area lomba.</p>
                        </div>
                        <div class="bg-white border border-slate-200 rounded-lg p-5 divide-y divide-slate-100 shadow-sm text-sm">
                            <div class="py-3 flex justify-between items-center">
                                <span class="font-bold text-slate-900">04:30 WIB</span>
                                <span class="text-slate-600 text-xs">Open Gate</span>
                            </div>
                            <div class="py-3 flex justify-between items-center">
                                <span class="font-bold text-slate-900">05:00 WIB</span>
                                <span class="text-slate-600 text-xs">Pemanasan</span>
                            </div>
                            <div class="py-3 flex justify-between items-center bg-theme-surface border border-theme-light -mx-5 px-5 rounded">
                                <span class="font-bold text-theme-primary font-mono">{{ $event->start_at ? $event->start_at->format('H:i') : '05:30' }} WIB</span>
                                <span class="font-bold text-slate-900 text-xs">Flag Off Start Lomba</span>
                            </div>
                            <div class="py-3 flex justify-between items-center">
                                <span class="font-bold text-slate-900">07:00 WIB</span>
                                <span class="text-slate-600 text-xs">Cut Off Time (COT)</span>
                            </div>
                            <div class="py-3 flex justify-between items-center">
                                <span class="font-bold text-slate-900">07:00 WIB</span>
                                <span class="text-slate-600 text-xs">Zumba</span>
                            </div>
                            <div class="py-3 flex justify-between items-center">
                                <span class="font-bold text-slate-900">07:30 - 09:00 WIB</span>
                                <span class="text-slate-600 text-xs">Hiburan, Doorprize dan Podium</span>
                            </div>
                        </div>

                        <!-- Race Rules Button -->
                        @if($event->terms_and_conditions)
                        <div>
                            <button type="button" onclick="openTermsModal()" class="w-full py-3 rounded-md border border-slate-300 bg-white hover:bg-slate-50 text-slate-800 text-xs font-bold transition flex items-center justify-center gap-2">
                                <i class="fas fa-file-shield text-slate-500"></i>
                                <span>Lihat Peraturan & Syarat Ketentuan Lomba</span>
                            </button>
                        </div>
                        @endif
                    </div>

                    <!-- FAQ Column -->
                    <div class="lg:col-span-7 space-y-6" id="faq">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Pertanyaan Umum</span>
                            <h2 class="text-2xl sm:text-3xl font-heading text-slate-900 mt-1">Frequently Asked Questions</h2>
                            <p class="text-slate-600 text-xs sm:text-sm mt-1">Jawaban cepat atas pertanyaan seputar pendaftaran dan hari lomba.</p>
                        </div>

                        @php
                            $faqs = $event->premium_amenities['faq']['items'] ?? [];
                            if(empty($faqs)) {
                                $faqs = [
                                    ['question' => 'Bagaimana cara konfirmasi pembayaran?', 'answer' => 'Pembayaran melalui Payment Gateway terverifikasi secara otomatis. Setelah pembayaran berhasil, E-Ticket langsung dikirim ke email penanggung jawab.'],
                                    ['question' => 'Apakah satu orang bisa mendaftarkan beberapa peserta?', 'answer' => 'Bisa. Gunakan tombol Tambah Peserta di formulir pendaftaran untuk mendaftarkan teman, keluarga, atau komunitas dalam satu transaksi.'],
                                    ['question' => 'Apakah nomor dada (BIB) bisa dipindahtangankan?', 'answer' => 'Demi keselamatan dan keabsahan pencatatan waktu resmi, nomor BIB tidak dapat dipindahtangankan kepada pihak lain tanpa persetujuan panitia.'],
                                    ['question' => 'Bagaimana jika saya tidak sempat mengambil race pack di jadwal RPC?', 'answer' => 'Pengambilan dapat diwakilkan dengan membawa surat kuasa bertandatangan dan fotokopi E-Ticket serta KTP peserta yang bersangkutan.']
                                ];
                            }
                        @endphp

                        <div class="space-y-3">
                            @foreach($faqs as $f)
                            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-sm">
                                <button type="button" class="w-full px-5 py-4 text-left font-bold text-slate-900 text-sm flex justify-between items-center hover:bg-slate-50 transition" onclick="toggleFaq(this)">
                                    <span>{{ $f['question'] }}</span>
                                    <i class="fas fa-chevron-down text-slate-400 text-xs transition duration-200"></i>
                                </button>
                                <div class="px-5 pb-4 text-xs text-slate-600 leading-relaxed hidden border-t border-slate-100 pt-3">
                                    {!! nl2br(e($f['answer'])) !!}
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Section: Daftar Peserta (Jika Diaktifkan) -->
        @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
        <section id="participants-list" class="py-20 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-10">
                    <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Komunitas & Runner</span>
                    <h2 class="text-3xl font-heading text-slate-900 mt-1">Daftar Peserta Terdaftar</h2>
                    <p class="text-slate-600 text-sm mt-1">Data pelari yang telah menyelesaikan proses registrasi dan pembayaran terkonfirmasi.</p>
                </div>
                
                <div id="vue-participants-app" class="bg-slate-50 border border-slate-200 rounded-lg p-6 shadow-sm">
                    @include('events.partials.participants-table-light')
                </div>
            </div>
        </section>
        @endif

        <!-- Section: Formulir Pendaftaran (Utama) -->
        <section id="register" class="py-20 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="mb-10 text-center max-w-2xl mx-auto">
                    <span class="text-xs font-bold uppercase tracking-wider text-theme-primary">Registrasi Resmi</span>
                    <h2 class="text-3xl sm:text-4xl font-heading text-slate-900 mt-1">Formulir Pendaftaran</h2>
                    <p class="text-slate-600 text-sm mt-2">
                        Isi data penanggung jawab dan peserta sesuai kartu identitas (KTP/SIM). Mendukung pendaftaran perorangan maupun banyak peserta sekaligus.
                    </p>
                </div>

                @if(!$isRegOpen)
                    <div class="max-w-2xl mx-auto bg-white border border-slate-200 rounded-lg p-10 text-center shadow-sm">
                        <div class="w-14 h-14 rounded-md bg-red-50 text-red-600 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-lock text-xl"></i>
                        </div>
                        <h3 class="text-2xl font-heading text-slate-900 mb-2">Pendaftaran Ditutup</h3>
                        <p class="text-slate-600 text-sm leading-relaxed mb-6">
                            Mohon maaf, kuota peserta telah terpenuhi atau batas waktu registrasi telah berakhir. Pantau pembaruan info di media sosial resmi.
                        </p>
                        <a href="{{ url('/') }}" class="px-6 py-2.5 rounded-md bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition">
                            Cari Event Lari Lainnya
                        </a>
                    </div>
                @else
                    
                    <!-- Alert Status Banner -->
                    @if(request('payment') === 'pending')
                    <div class="max-w-5xl mx-auto mb-8 bg-amber-50 border border-amber-300 text-amber-900 rounded-lg p-5 shadow-sm">
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-md bg-amber-200 text-amber-800 flex items-center justify-center shrink-0">
                                <i class="fas fa-hourglass-half text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="font-bold text-sm">Menunggu Penyelesaian Pembayaran</h4>
                                <p class="text-xs text-amber-800 mt-0.5">
                                    Anda memiliki transaksi yang belum selesai. Jika popup pembayaran sebelumnya tertutup, Anda dapat melanjutkannya tanpa perlu mengisi ulang data.
                                </p>
                                <div class="mt-3">
                                    <a href="{{ route('events.payments.continue', $event->slug) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-amber-400 hover:bg-amber-300 text-black font-bold text-xs transition shadow-sm">
                                        <span>Lanjutkan Pembayaran Sebelumnya</span>
                                        <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif(request('payment') === 'success')
                    <div class="max-w-5xl mx-auto mb-8 bg-emerald-50 border border-emerald-300 text-emerald-900 rounded-lg p-5 shadow-sm">
                        <div class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-md bg-emerald-200 text-emerald-800 flex items-center justify-center shrink-0">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm">Pembayaran Berhasil Dikonfirmasi</h4>
                                <p class="text-xs text-emerald-800 mt-0.5">
                                    Terima kasih telah mendaftar. E-Ticket dan detail kepesertaan telah dikirimkan ke email PIC Anda.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(isset($errors) && $errors->any())
                    <div class="max-w-5xl mx-auto mb-8 bg-red-50 border border-red-300 text-red-900 rounded-lg p-5 shadow-sm">
                        <h4 class="font-bold text-xs uppercase tracking-wider mb-2">Mohon Periksa Kembali Isian Formulir:</h4>
                        <ul class="list-disc pl-5 space-y-1 text-xs text-red-800">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Main Form -->
                    <form action="{{ route('events.register.store', ['slug' => $event->slug]) }}" method="POST" id="registrationForm" novalidate class="max-w-7xl mx-auto">
                        @csrf

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                            
                            <!-- Left: PIC and Participants (8 cols) -->
                            <div class="lg:col-span-8 space-y-8">
                                
                                <!-- Step 1: Data PIC -->
                                <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                                    <div class="border-b border-slate-100 pb-3 mb-5 flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-md bg-theme-primary text-white font-heading text-xs flex items-center justify-center">1</span>
                                            <h3 class="text-base font-heading text-slate-900">Data Penanggung Jawab (PIC)</h3>
                                        </div>
                                        <span class="text-xs text-slate-400">Penerima E-Ticket & Notifikasi</span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Nama Lengkap PIC</label>
                                            <input type="text" name="pic_name" value="{{ old('pic_name') }}" required class="input-light w-full px-3 py-2.5" placeholder="Sesuai KTP">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Email PIC</label>
                                            <input type="email" name="pic_email" value="{{ old('pic_email') }}" required class="input-light w-full px-3 py-2.5" placeholder="email@contoh.com">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">No. WhatsApp PIC</label>
                                            <input type="text" name="pic_phone" value="{{ old('pic_phone') }}" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="input-light w-full px-3 py-2.5" placeholder="0812xxxxxxxx">
                                        </div>
                                    </div>
                                </div>

                                <!-- Step 2: Data Peserta Multi -->
                                <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm">
                                    <div class="border-b border-slate-100 pb-3 mb-5 flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-7 h-7 rounded-md bg-theme-primary text-white font-heading text-xs flex items-center justify-center">2</span>
                                            <h3 class="text-base font-heading text-slate-900">Data Peserta Lomba</h3>
                                        </div>
                                        <button type="button" id="addParticipantTop" class="px-3.5 py-1.5 rounded-md bg-theme-light border border-theme-light text-theme-primary hover-bg-theme-primary hover:text-white text-xs font-bold transition flex items-center gap-1.5">
                                            <i class="fas fa-plus text-[10px]"></i>
                                            <span>Tambah Peserta</span>
                                        </button>
                                    </div>

                                    <!-- Participants Wrapper -->
                                    <div id="participantsWrapper" class="space-y-6">
                                        
                                        <!-- Participant Card Template #1 -->
                                        <div class="participant-item bg-slate-50 border border-slate-200 rounded-lg p-5 transition hover:border-slate-300" data-index="0">
                                            
                                            <!-- Participant Top Bar -->
                                            <div class="flex flex-wrap items-center justify-between border-b border-slate-200 pb-3 mb-4 gap-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 rounded bg-slate-900 text-white text-[11px] font-heading participant-title">
                                                        PESERTA #1
                                                    </span>
                                                    <button type="button" class="copy-pic-btn text-[10px] font-bold px-2 py-1 rounded bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 transition" onclick="copyFromPic(this)">
                                                        Isi Data PIC
                                                    </button>
                                                    <button type="button" class="copy-prev-btn text-[10px] font-bold px-2 py-1 rounded bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 transition hidden" onclick="copyFromPrev(this)">
                                                        Salin Peserta Sebelumnya
                                                    </button>
                                                </div>
                                                <button type="button" class="remove-participant text-xs font-bold text-red-600 hover:text-red-800 transition hidden">
                                                    Hapus Peserta
                                                </button>
                                            </div>

                                            <div class="space-y-4">
                                                <!-- Category Selection -->
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Pilih Kategori Lomba</label>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5">
                                                        @foreach($categories as $cat)
                                                        @php
                                                            $pRegular = (int) ($cat->price_regular ?? 0);
                                                            $pEarly = (int) ($cat->price_early ?? 0);
                                                            $pLate = (int) ($cat->price_late ?? 0);
                                                            $pDisplay = $pRegular;
                                                            if ($pEarly > 0) {
                                                                $pDisplay = $pEarly;
                                                            } elseif ($pLate > 0) {
                                                                $pDisplay = $pLate;
                                                            }
                                                        @endphp
                                                        <label class="cursor-pointer relative block">
                                                            <input type="radio" name="participants[0][category_id]" value="{{ $cat->id }}" class="cat-radio peer sr-only" data-price="{{ $pDisplay }}" required {{ $loop->first ? 'checked' : '' }}>
                                                            <div class="p-3 bg-white border border-slate-300 rounded-md transition hover:border-slate-400">
                                                                <div class="flex justify-between items-center">
                                                                    <span class="font-bold text-xs text-slate-900">{{ $cat->name }}</span>
                                                                    <span class="text-xs font-mono font-bold text-theme-primary cat-price">Rp {{ number_format($pDisplay/1000, 0) }}k</span>
                                                                </div>
                                                                <span class="text-[10px] text-slate-500 block mt-0.5">{{ $cat->distance_km ?? 0 }}K • COT {{ $cat->cot_hours ?? 0 }} Jam</span>
                                                            </div>
                                                        </label>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <!-- Names and Gender -->
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                    <div class="sm:col-span-2">
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Peserta (BIB Name)</label>
                                                        <input type="text" name="participants[0][name]" required class="input-light w-full px-3 py-2 text-sm" placeholder="Nama lengkap">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Kelamin</label>
                                                        <select name="participants[0][gender]" required class="input-light w-full px-3 py-2 text-sm">
                                                            <option value="">Pilih</option>
                                                            <option value="male">Laki-laki</option>
                                                            <option value="female">Perempuan</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <!-- Contact Info -->
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Peserta</label>
                                                        <input type="email" name="participants[0][email]" required class="input-light w-full px-3 py-2 text-sm participant-email" placeholder="email@peserta.com">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. WhatsApp / HP</label>
                                                        <input type="text" name="participants[0][phone]" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="input-light w-full px-3 py-2 text-sm" placeholder="0812xxxxxxxx">
                                                    </div>
                                                </div>

                                                <!-- NIK & Date of Birth -->
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Identitas (KTP / SIM)</label>
                                                        <input type="text" name="participants[0][id_card]" required class="input-light w-full px-3 py-2 text-sm" placeholder="NIK / No KTP">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Lahir</label>
                                                        <input type="date" name="participants[0][date_of_birth]" required class="input-light w-full px-3 py-2 text-sm">
                                                    </div>
                                                </div>

                                                <!-- Address -->
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Lengkap</label>
                                                    <textarea name="participants[0][address]" required maxlength="500" rows="2" class="input-light w-full px-3 py-2 text-sm" placeholder="Alamat domisili lengkap"></textarea>
                                                </div>

                                                <!-- Emergency Contact -->
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Kontak Darurat</label>
                                                        <input type="text" name="participants[0][emergency_contact_name]" required class="input-light w-full px-3 py-2 text-sm" placeholder="Nama keluarga/rekan">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Kontak Darurat</label>
                                                        <input type="text" name="participants[0][emergency_contact_number]" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" class="input-light w-full px-3 py-2 text-sm" placeholder="08xxxxxxxxxx">
                                                    </div>
                                                </div>

                                                <!-- Jersey Size & Target Time -->
                                                <div class="grid grid-cols-1 {{ $showTargetTime ? 'sm:grid-cols-2' : '' }} gap-3 items-start">
                                                    <div>
                                                        <div class="flex justify-between items-center mb-1">
                                                            <label class="text-xs font-bold text-slate-700 uppercase">Ukuran Jersey</label>
                                                            <button type="button" onclick="openLightbox('https://res.cloudinary.com/dqm0gzjmu/image/upload/v1791534615/size_jersey_new_xlqovf.webp')" class="text-[11px] font-bold text-theme-primary hover:underline">
                                                                Panduan Ukuran
                                                            </button>
                                                        </div>
                                                        @php
                                                            $sizes = $event->jersey_sizes ?? ['XS','S','M','L','XL','2XL','3XL'];
                                                        @endphp
                                                        <select name="participants[0][jersey_size]" required class="input-light w-full px-3 py-2 text-sm">
                                                            <option value="">-- Pilih Ukuran Jersey --</option>
                                                            @foreach($sizes as $sz)
                                                                @php
                                                                    $stk = $jerseyStockData[$sz] ?? null;
                                                                    $isOut = $stk !== null && $stk['remaining'] <= 0;
                                                                    $isLow = $stk !== null && $stk['remaining'] > 0 && $stk['remaining'] <= 5;
                                                                    $label = $sz;
                                                                    if ($isOut) $label .= ' - HABIS';
                                                                    elseif ($isLow) $label .= ' - Sisa ' . $stk['remaining'];
                                                                @endphp
                                                                <option value="{{ $sz }}" {{ $isOut ? 'disabled' : '' }}>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    @if($showTargetTime)
                                                    <div>
                                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Target Waktu (Opsional)</label>
                                                        <input type="text" name="participants[0][target_time]" placeholder="01:30:00 (JJ:MM:DD)" class="input-light w-full px-3 py-2 text-sm font-mono">
                                                    </div>
                                                    @endif
                                                </div>

                                                @if($showStravaField)
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tautan Aktivitas Strava (Opsional)</label>
                                                    <input type="url" name="participants[0][strava_url]" placeholder="https://www.strava.com/activities/..." class="input-light w-full px-3 py-2 text-sm">
                                                </div>
                                                @endif

                                                <!-- Add-ons (If available) -->
                                                @if(!empty($event->addons) && is_array($event->addons))
                                                <div class="border-t border-slate-200 pt-3">
                                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Pilihan Add-On Tambahan</label>
                                                    <div class="space-y-2">
                                                        @foreach($event->addons as $aIdx => $addon)
                                                        <label class="flex items-center justify-between p-2.5 rounded-md bg-white border border-slate-200 hover:border-theme-primary cursor-pointer transition">
                                                            <div class="flex items-center gap-2.5">
                                                                <input type="checkbox" name="participants[0][addons][{{ $aIdx }}][selected]" value="1" class="addon-checkbox w-4 h-4 rounded text-theme-primary border-slate-300" data-price="{{ $addon['price'] ?? 0 }}">
                                                                <input type="hidden" name="participants[0][addons][{{ $aIdx }}][name]" value="{{ $addon['name'] }}">
                                                                <input type="hidden" name="participants[0][addons][{{ $aIdx }}][price]" value="{{ $addon['price'] ?? 0 }}">
                                                                <span class="text-xs font-bold text-slate-800">{{ $addon['name'] }}</span>
                                                            </div>
                                                            <span class="text-xs font-mono font-bold text-theme-primary">+Rp {{ number_format($addon['price'] ?? 0, 0, ',', '.') }}</span>
                                                        </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                @endif

                                            </div>
                                        </div>

                                    </div>

                                    <!-- Bottom Add Participant Button -->
                                    <div class="pt-5 flex justify-center">
                                        <button type="button" id="addParticipantBottom" class="px-5 py-2.5 rounded-md border border-theme-primary text-theme-primary hover:bg-theme-light text-xs font-bold transition flex items-center gap-2">
                                            <i class="fas fa-user-plus text-xs"></i>
                                            <span>Tambah Peserta Lainnya</span>
                                        </button>
                                    </div>

                                </div>

                            </div>

                            <!-- Right: Sticky Order Summary & Payment (4 cols) -->
                            <div class="lg:col-span-4 sticky top-24">
                                <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm space-y-6">
                                    
                                    <h3 class="text-base font-heading text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
                                        <span>Ringkasan Transaksi</span>
                                        <span class="text-xs font-normal text-slate-400" id="participantCountBadge">1 Peserta</span>
                                    </h3>

                                    <!-- Coupon Input -->
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Kode Promo</label>
                                        <div class="flex gap-2">
                                            <input type="text" id="coupon_code" placeholder="KODE..." class="input-light flex-1 px-3 py-2 text-sm uppercase font-mono font-bold">
                                            <button type="button" id="applyCouponBtn" class="px-4 py-2 rounded-md bg-theme-primary hover-bg-theme-primary text-white font-bold text-xs transition shadow-sm">
                                                Pakai
                                            </button>
                                        </div>
                                        <div id="couponMessage" class="mt-1.5 text-xs font-medium"></div>
                                        <input type="hidden" name="coupon_code" id="coupon_code_hidden">
                                    </div>

                                    <!-- Summary Calculation Rows -->
                                    <div class="space-y-2.5 border-t border-slate-100 pt-4 text-xs">
                                        <div class="flex justify-between text-slate-600">
                                            <span>Subtotal Kategori</span>
                                            <span id="subtotalDisplay" class="font-mono text-slate-900 font-bold">Rp 0</span>
                                        </div>
                                        <div id="discountRow" class="flex justify-between text-emerald-600 hidden">
                                            <span>Potongan Diskon</span>
                                            <span id="discountDisplay" class="font-mono font-bold">-Rp 0</span>
                                        </div>
                                        @if(($event->platform_fee ?? 0) > 0)
                                        <div class="flex justify-between text-slate-600">
                                            <span>Biaya Layanan</span>
                                            <span id="platformFeeDisplay" class="font-mono text-slate-900 font-bold">Rp 0</span>
                                        </div>
                                        @endif
                                        <div class="border-t border-slate-200 pt-3 flex justify-between items-end">
                                            <span class="font-heading text-slate-900 text-sm">TOTAL PEMBAYARAN</span>
                                            <span id="totalDisplay" class="text-2xl font-heading text-theme-primary font-mono tabular-nums">Rp 0</span>
                                        </div>
                                    </div>

                                    <!-- Payment Gateway Selector -->
                                    <div class="border-t border-slate-100 pt-4 space-y-2.5">
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Metode Pembayaran</label>
                                        
                                        @if($showMidtrans)
                                        <label class="flex items-center gap-3 p-3 rounded-md border border-slate-200 hover:border-theme-primary cursor-pointer transition bg-slate-50">
                                            <input type="radio" name="payment_method" value="midtrans" class="w-4 h-4 text-theme-primary" {{ $showMidtrans && !$showMoota ? 'checked' : '' }} required>
                                            <div class="flex-1">
                                                <span class="block text-xs font-bold text-slate-900">Pembayaran Otomatis</span>
                                                <span class="text-[11px] text-slate-500">QRIS, Virtual Account, E-Wallet (Midtrans)</span>
                                            </div>
                                        </label>
                                        @endif

                                        @if($showMoota)
                                        <label class="flex items-center gap-3 p-3 rounded-md border border-slate-200 hover:border-theme-primary cursor-pointer transition bg-slate-50">
                                            <input type="radio" name="payment_method" value="moota" class="w-4 h-4 text-theme-primary" {{ !$showMidtrans && $showMoota ? 'checked' : '' }} required>
                                            <div class="flex-1">
                                                <span class="block text-xs font-bold text-slate-900">Transfer Bank Otomatis</span>
                                                <span class="text-[11px] text-slate-500">BCA, Mandiri, BRI, BNI (Moota)</span>
                                            </div>
                                        </label>
                                        @endif

                                        @if($showCOD)
                                        <label class="flex items-center gap-3 p-3 rounded-md border border-slate-200 hover:border-theme-primary cursor-pointer transition bg-slate-50">
                                            <input type="radio" name="payment_method" value="cod" class="w-4 h-4 text-theme-primary" {{ !$showMidtrans && !$showMoota && $showCOD ? 'checked' : '' }} required>
                                            <div class="flex-1">
                                                <span class="block text-xs font-bold text-slate-900">Pembayaran Tunai (COD / Offline)</span>
                                                <span class="text-[11px] text-slate-500">Bayar saat pengambilan race pack</span>
                                            </div>
                                        </label>
                                        @endif

                                        @if($showManualTransfer)
                                        <label class="flex items-center gap-3 p-3 rounded-md border border-slate-200 hover:border-theme-primary cursor-pointer transition bg-slate-50">
                                            <input type="radio" name="payment_method" value="manual_transfer" class="w-4 h-4 text-theme-primary" {{ !$showMidtrans && !$showMoota && !$showCOD && $showManualTransfer ? 'checked' : '' }} required>
                                            <div class="flex-1">
                                                <span class="block text-xs font-bold text-slate-900">Transfer Rekening EO</span>
                                                <span class="text-[11px] text-slate-500">Transfer langsung ke rekening EO dengan 3 digit kode unik verifikasi</span>
                                            </div>
                                        </label>
                                        @endif
                                    </div>

                                    @if($event->terms_and_conditions)
                                    <div class="border-t border-slate-100 pt-4">
                                        <label class="flex items-start gap-2.5 cursor-pointer">
                                            <input type="checkbox" name="terms_agreed" required class="mt-0.5 w-4 h-4 rounded text-theme-primary border-slate-300">
                                            <span class="text-xs text-slate-600 leading-tight">
                                                Saya menyetujui seluruh <button type="button" onclick="openTermsModal()" class="text-theme-primary underline font-semibold">Syarat & Ketentuan</button> yang berlaku pada event ini.
                                            </span>
                                        </label>
                                    </div>
                                    @endif

                                    <input type="hidden" name="g-recaptcha-response" id="recaptchaToken">

                                    <button type="submit" id="submitBtn" class="w-full py-3.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white font-bold text-sm transition shadow-sm flex items-center justify-center gap-2">
                                        <span>Lanjut Pembayaran</span>
                                        <i class="fas fa-chevron-right text-xs"></i>
                                    </button>

                                    <div class="text-center pt-2">
                                        <button type="button" onclick="window.resetRegistrationForm()" class="text-xs text-slate-400 hover:text-slate-600 transition">
                                            Reset Formulir & Kosongkan Isian
                                        </button>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </form>
                @endif

            </div>
        </section>

        <!-- Section: Sponsor Carousel -->
        @include('events.partials.sponsor-carousel', [
            'gradientFrom' => 'from-slate-50',
            'titleColor' => 'text-slate-400',
            'containerClass' => 'bg-white border-y border-slate-200',
            'sectionClass' => 'py-16 relative z-10'
        ])
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-12 text-slate-600 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <span class="font-heading text-slate-900 text-base uppercase">{{ $event->name }}</span>
                    <p class="text-slate-400 mt-1">Platform registrasi event resmi diselenggarakan oleh {{ $event->organizer_name ?? 'Panitia Event' }}.</p>
                </div>
                <div class="flex flex-wrap items-center gap-6 font-semibold">
                    <a href="#about" class="hover-text-theme-primary transition">Tentang</a>
                    <a href="#categories" class="hover-text-theme-primary transition">Kategori</a>
                    <a href="#venue" class="hover-text-theme-primary transition">Lokasi</a>
                    <a href="#register" class="hover-text-theme-primary transition">Pendaftaran</a>
                    @if($event->terms_and_conditions)
                    <button type="button" onclick="openTermsModal()" class="hover-text-theme-primary transition">Syarat & Ketentuan</button>
                    @endif
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center text-slate-400 text-[11px] gap-2">
                <span>&copy; {{ date('Y') }} {{ $event->name }}. All rights reserved.</span>
                <span>Powered by RuangLari Event Technology</span>
            </div>
        </div>
    </footer>

    <!-- MODAL: Confirmation Review Before Submit -->
    <div id="confirmationModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/75 transition-opacity" onclick="closeConfirmationModal()"></div>
        <div class="relative bg-white rounded-lg w-full max-w-lg shadow-xl overflow-hidden z-10 border border-slate-200">
            <div class="bg-theme-primary text-white px-5 py-4 flex items-center justify-between">
                <h3 class="font-heading text-sm uppercase">Konfirmasi Data Pendaftaran</h3>
                <button type="button" onclick="closeConfirmationModal()" class="text-white hover:text-slate-200 text-lg">&times;</button>
            </div>
            <div class="p-6 space-y-4 max-h-[70vh] overflow-y-auto custom-scrollbar">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Mohon periksa data Anda sebelum dialihkan ke sistem pembayaran. Pastikan NIK, gender, dan ukuran jersey telah sesuai.
                </p>

                <div class="bg-slate-50 border border-slate-200 rounded-md p-3 text-xs space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama PIC</span>
                        <span class="font-bold text-slate-900" id="confPicName">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">No. WhatsApp</span>
                        <span class="font-mono text-slate-900" id="confPicPhone">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Email</span>
                        <span class="text-slate-900" id="confPicEmail">-</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-700 uppercase">Daftar Peserta (<span id="confCountBadge">1</span>)</span>
                    <div id="confParticipantsList" class="space-y-2 max-h-48 overflow-y-auto"></div>
                </div>

                <div class="bg-theme-light border border-theme-light rounded-md p-3 flex justify-between items-center text-xs">
                    <span class="font-bold text-slate-900">Total Pembayaran</span>
                    <span class="font-mono font-heading text-theme-primary text-lg" id="confTotal">Rp 0</span>
                </div>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeConfirmationModal()" class="px-4 py-2 rounded-md border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-100 transition">
                    Ubah Data
                </button>
                <button type="button" id="confirmSubmitBtn" class="px-5 py-2 rounded-md bg-theme-primary hover-bg-theme-primary text-white text-xs font-bold transition shadow-sm">
                    Lanjut Bayar
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL: Success E-Ticket Generator -->
    <div id="registrationSuccessModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" id="registrationSuccessModalBackdrop"></div>
        <div class="relative bg-white rounded-lg w-full max-w-lg shadow-2xl overflow-hidden z-10 border border-slate-200" id="registrationSuccessContainer"
            data-event-id="{{ $event->id }}"
            data-event-name="{{ $event->name }}"
            data-event-date="{{ $ticketDate }}"
            data-event-location="{{ $ticketLocation }}"
            @if($event->logo_image) data-event-logo="{{ asset('storage/' . $event->logo_image) }}" @endif
        >
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                <h3 class="font-heading text-sm text-slate-900 uppercase">Pendaftaran Berhasil</h3>
                <button type="button" id="registrationSuccessCloseBtn" class="w-8 h-8 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-md bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <i class="fas fa-check text-base"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">Terima kasih atas pendaftaran Anda.</h4>
                        <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">
                            Konfirmasi tiket telah dikirim ke email penanggung jawab. Anda dapat mengunduh E-Ticket digital di bawah ini.
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between border border-slate-200 rounded-md px-3 py-2 bg-slate-50 text-xs">
                    <div>
                        <span class="text-slate-500">Peserta:</span>
                        <span id="eticketParticipantLabel" class="font-bold text-slate-900 ml-1">-</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button type="button" id="eticketPrevBtn" class="w-6 h-6 rounded border border-slate-300 text-slate-700 hover:bg-slate-100 disabled:opacity-30">&larr;</button>
                        <span class="text-slate-600 font-mono"><span id="eticketParticipantIndex">1</span>/<span id="eticketParticipantTotal">1</span></span>
                        <button type="button" id="eticketNextBtn" class="w-6 h-6 rounded border border-slate-300 text-slate-700 hover:bg-slate-100 disabled:opacity-30">&rarr;</button>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-md bg-slate-50 p-2 overflow-hidden">
                    <canvas id="registrationSuccessEticketCanvas" width="900" height="450" class="w-full h-auto rounded"></canvas>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" id="downloadEticketBtn" class="flex-1 py-2.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white text-xs font-bold transition shadow-sm">
                        Unduh E-Ticket (PNG)
                    </button>
                    <button type="button" id="closeNowBtn" class="px-5 py-2.5 rounded-md border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Failure Message -->
    <div id="registrationFailureModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="failureModalTitle">
        <div class="fixed inset-0 bg-slate-950/75 transition-opacity" id="registrationFailureModalBackdrop" onclick="closeFailureModal()"></div>
        <div class="relative bg-white rounded-lg w-full max-w-md shadow-xl overflow-hidden z-10 border border-slate-200 p-6 text-center">
            <button type="button" onclick="closeFailureModal()" class="absolute top-3 right-3 w-8 h-8 rounded-md hover:bg-slate-100 text-slate-400 hover:text-slate-700 text-lg flex items-center justify-center transition" aria-label="Tutup">&times;</button>
            <div class="w-12 h-12 rounded-md bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-exclamation-triangle text-lg"></i>
            </div>
            <h3 id="failureModalTitle" class="font-heading text-base text-slate-900 mb-2">Pendaftaran Belum Berhasil</h3>
            <div id="registrationFailureMessage" class="text-xs text-slate-700 leading-relaxed mb-6 max-h-48 overflow-y-auto custom-scrollbar text-left bg-red-50/70 p-3 rounded border border-red-100">
                Terjadi kendala saat memproses pendaftaran Anda.
            </div>
            <button type="button" id="closeFailureNowBtn" onclick="closeFailureModal()" class="w-full py-2.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white text-xs font-bold transition shadow-sm">
                Tutup dan Periksa Kembali
            </button>
        </div>
    </div>

    <!-- MODAL: Terms and Conditions -->
    @if($event->terms_and_conditions)
    <div id="termsModal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 sm:p-6 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="termsModalTitle">
        <div class="fixed inset-0 bg-slate-950/75 transition-opacity" onclick="closeTermsModal()"></div>
        <div class="relative bg-white rounded-lg w-full max-w-2xl shadow-lg overflow-hidden z-10 border border-slate-200 my-auto mx-auto flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50 shrink-0">
                <h3 id="termsModalTitle" class="font-heading text-sm font-bold text-slate-900 uppercase">Syarat & Ketentuan Lomba</h3>
                <button type="button" onclick="closeTermsModal()" class="w-8 h-8 rounded-md hover:bg-slate-200 text-slate-500 hover:text-slate-800 text-lg flex items-center justify-center transition" aria-label="Tutup">&times;</button>
            </div>
            <div class="p-6 overflow-y-auto text-xs text-slate-600 space-y-3 leading-relaxed custom-scrollbar prose prose-sm max-w-none">
                {!! $event->terms_and_conditions !!}
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end shrink-0">
                <button type="button" onclick="closeTermsModal()" class="px-5 py-2.5 rounded-md bg-theme-primary hover-bg-theme-primary text-white text-xs font-bold transition shadow-sm">
                    Saya Mengerti
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL: Lightbox for Jersey & Charts -->
    <div id="imageLightbox" class="fixed inset-0 z-[120] hidden items-center justify-center p-4 bg-black/80" onclick="closeLightbox()">
        <div class="relative max-w-3xl max-h-[90vh]" onclick="event.stopPropagation()">
            <button type="button" onclick="closeLightbox()" class="absolute -top-10 right-0 text-white hover:text-slate-300 text-2xl font-bold">&times;</button>
            <img id="lightboxImg" src="" alt="Preview" class="max-w-full max-h-[85vh] rounded-md object-contain shadow-2xl bg-white p-2">
        </div>
    </div>

    <!-- Include Moota Modal Partial -->
    @include('events.partials.moota-payment-modal', [
        'modalPanelClass' => 'bg-white text-slate-900 border border-slate-200 rounded-lg',
        'modalTitleClass' => 'text-slate-900 font-heading',
        'modalAccentClass' => 'text-theme-primary',
        'modalCloseClass' => 'bg-theme-primary text-white hover-bg-theme-primary rounded-md',
    ])

    <!-- Scripts Section -->
    @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
        <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
        <script>
            (function() {
                if (typeof Vue !== 'undefined') {
                    const { createApp } = Vue;
                    const vueApp = createApp({});
                    if (typeof ParticipantsTableComponent !== 'undefined') {
                        vueApp.component('participants-table', ParticipantsTableComponent);
                    }
                    const mountEl = document.getElementById('vue-participants-app');
                    if (mountEl) vueApp.mount(mountEl);
                }
            })();
        </script>
    @endif

    <script>
        // Global Theme Primary Color
        window.THEME_PRIMARY_COLOR = '{{ $primaryColor }}';

        // Global Lightbox
        window.openLightbox = function(url) {
            const lb = document.getElementById('imageLightbox');
            const img = document.getElementById('lightboxImg');
            if(lb && img && url) {
                img.src = url;
                lb.classList.remove('hidden');
                lb.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
        };
        window.closeLightbox = function() {
            const lb = document.getElementById('imageLightbox');
            if(lb) {
                lb.classList.add('hidden');
                lb.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
        };

        // Global Terms Modal
        window.openTermsModal = function() {
            const modal = document.getElementById('termsModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
        };
        window.closeTermsModal = function() {
            const modal = document.getElementById('termsModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }
        };

        // Close Modals on ESC Key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (typeof window.closeTermsModal === 'function') window.closeTermsModal();
                if (typeof window.closeLightbox === 'function') window.closeLightbox();
            }
        });

        // Navigation Scroll Behavior
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 15) {
                nav.classList.add('nav-scrolled');
            } else {
                nav.classList.remove('nav-scrolled');
            }
        });

        // Mobile Menu Toggle
        const mobBtn = document.getElementById('mobileMenuBtn');
        const mobMenu = document.getElementById('mobileMenu');
        if (mobBtn && mobMenu) {
            mobBtn.addEventListener('click', () => {
                mobMenu.classList.toggle('hidden');
            });
            mobMenu.querySelectorAll('a').forEach(a => {
                a.addEventListener('click', () => mobMenu.classList.add('hidden'));
            });
        }

        // FAQ Toggle
        window.toggleFaq = function(btn) {
            const body = btn.nextElementSibling;
            const icon = btn.querySelector('.fa-chevron-down');
            if(body) {
                body.classList.toggle('hidden');
                if(icon) {
                    icon.style.transform = body.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
                }
            }
        };

        // Live Countdown
        @if($countdownTarget)
        (function() {
            const target = {{ $countdownTarget->timestamp * 1000 }};
            function updateCd() {
                const now = new Date().getTime();
                const dist = target - now;
                if (dist <= 0) {
                    const daysEl = document.getElementById('cd-days');
                    if (daysEl) daysEl.innerText = '00';
                    return;
                }
                const d = Math.floor(dist / (1000 * 60 * 60 * 24));
                const h = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((dist % (1000 * 60)) / 1000);

                const dEl = document.getElementById('cd-days');
                const hEl = document.getElementById('cd-hours');
                const mEl = document.getElementById('cd-minutes');
                const sEl = document.getElementById('cd-seconds');

                if (dEl) dEl.innerText = String(d).padStart(2, '0');
                if (hEl) hEl.innerText = String(h).padStart(2, '0');
                if (mEl) mEl.innerText = String(m).padStart(2, '0');
                if (sEl) sEl.innerText = String(s).padStart(2, '0');
            }
            setInterval(updateCd, 1000);
            updateCd();
        })();
        @endif

        // Copy Helpers
        window.copyFromPic = function(btn) {
            const item = btn.closest('.participant-item');
            if (!item) return;

            const isFirst = item.getAttribute('data-index') === '0';
            if (isFirst && typeof window.resetP1Customized === 'function') {
                window.resetP1Customized();
            }

            const picName = document.querySelector('input[name="pic_name"]')?.value || '';
            const picEmail = document.querySelector('input[name="pic_email"]')?.value || '';
            const picPhone = document.querySelector('input[name="pic_phone"]')?.value || '';

            if (picName) {
                const nameInp = item.querySelector('input[name*="[name]"]');
                if (nameInp) {
                    nameInp.value = picName;
                    nameInp.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
            if (picEmail) {
                const emailInp = item.querySelector('input[name*="[email]"]');
                if (emailInp) {
                    emailInp.value = picEmail;
                    emailInp.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
            if (picPhone) {
                const phoneInp = item.querySelector('input[name*="[phone]"]');
                if (phoneInp) {
                    phoneInp.value = picPhone.replace(/[^0-9]/g, '');
                    phoneInp.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        };

        window.copyFromPrev = function(btn) {
            const currentItem = btn.closest('.participant-item');
            const currentIndex = parseInt(currentItem.dataset.index);
            if (currentIndex > 0) {
                const prevItem = document.querySelector(`.participant-item[data-index="${currentIndex - 1}"]`);
                if (prevItem) {
                    const fields = ['emergency_contact_name', 'emergency_contact_number', 'address'];
                    fields.forEach(f => {
                        const prevEl = prevItem.querySelector(`[name*="[${f}]"]`);
                        const curEl = currentItem.querySelector(`[name*="[${f}]"]`);
                        if (prevEl && curEl && prevEl.value) {
                            curEl.value = prevEl.value;
                        }
                    });
                }
            }
        };

        // Realtime Mirroring: Data PIC ke Peserta #1
        (function() {
            function initPicMirroring() {
                const picName = document.querySelector('input[name="pic_name"]');
                const picEmail = document.querySelector('input[name="pic_email"]');
                const picPhone = document.querySelector('input[name="pic_phone"]');

                const p1Item = document.querySelector('.participant-item[data-index="0"]');
                if (!p1Item || !picName || !picEmail || !picPhone) return;

                const p1Name = p1Item.querySelector('input[name="participants[0][name]"]');
                const p1Email = p1Item.querySelector('input[name="participants[0][email]"]');
                const p1Phone = p1Item.querySelector('input[name="participants[0][phone]"]');

                if (!p1Name || !p1Email || !p1Phone) return;

                const isCustomized = {
                    name: false,
                    email: false,
                    phone: false
                };

                let isSyncing = false;

                // Cek nilai awal (misalnya dari old() atau autofill)
                if (p1Name.value.trim() !== '' && p1Name.value !== picName.value) {
                    isCustomized.name = true;
                }
                if (p1Email.value.trim() !== '' && p1Email.value !== picEmail.value) {
                    isCustomized.email = true;
                }
                if (p1Phone.value.trim() !== '' && p1Phone.value !== picPhone.value) {
                    isCustomized.phone = true;
                }

                function mirror(fieldKey, sourceEl, targetEl) {
                    if (isSyncing) return;
                    // Jangan timpa jika user sudah mengubah manual dan tidak kosong
                    if (isCustomized[fieldKey] && targetEl.value.trim() !== '') return;

                    isSyncing = true;
                    targetEl.value = sourceEl.value;
                    if (fieldKey === 'phone') {
                        targetEl.value = targetEl.value.replace(/[^0-9]/g, '');
                    }
                    targetEl.dispatchEvent(new Event('input', { bubbles: true }));
                    isSyncing = false;
                }

                function bindPicSource(fieldKey, sourceEl, targetEl) {
                    const handleSync = function() {
                        mirror(fieldKey, sourceEl, targetEl);
                    };
                    sourceEl.addEventListener('input', handleSync);
                    sourceEl.addEventListener('change', handleSync);
                    sourceEl.addEventListener('paste', function() {
                        setTimeout(handleSync, 10);
                    });
                }

                function bindP1Target(fieldKey, sourceEl, targetEl) {
                    const handleManualEdit = function() {
                        if (isSyncing) return;
                        if (targetEl.value.trim() === '') {
                            // Jika dikosongkan, reset agar mirroring aktif kembali
                            isCustomized[fieldKey] = false;
                        } else if (targetEl.value !== sourceEl.value) {
                            // User mengetik manual nilai yang berbeda dari PIC
                            isCustomized[fieldKey] = true;
                        }
                    };
                    targetEl.addEventListener('input', handleManualEdit);
                    targetEl.addEventListener('change', handleManualEdit);
                }

                bindPicSource('name', picName, p1Name);
                bindPicSource('email', picEmail, p1Email);
                bindPicSource('phone', picPhone, p1Phone);

                bindP1Target('name', picName, p1Name);
                bindP1Target('email', picEmail, p1Email);
                bindP1Target('phone', picPhone, p1Phone);

                window.resetP1Customized = function() {
                    isCustomized.name = false;
                    isCustomized.email = false;
                    isCustomized.phone = false;
                };

                // Sinkronisasi awal saat muat halaman jika PIC sudah terisi namun Peserta 1 kosong
                if (picName.value && !p1Name.value) mirror('name', picName, p1Name);
                if (picEmail.value && !p1Email.value) mirror('email', picEmail, p1Email);
                if (picPhone.value && !p1Phone.value) mirror('phone', picPhone, p1Phone);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPicMirroring);
            } else {
                initPicMirroring();
            }
        })();

        window.resetRegistrationForm = function() {
            if (!confirm('Kosongkan semua data yang telah diisi pada formulir?')) return;
            const form = document.getElementById('registrationForm');
            if (form) form.reset();
            window.location.reload();
        };

        // Form Calculation & Multi-Participant Logic
        (function() {
            const form = document.getElementById('registrationForm');
            if (!form) return;
            form.setAttribute('novalidate', 'true');

            const participantsWrapper = document.getElementById('participantsWrapper');
            const addBtnTop = document.getElementById('addParticipantTop');
            const addBtnBottom = document.getElementById('addParticipantBottom');
            const subtotalDisplay = document.getElementById('subtotalDisplay');
            const totalDisplay = document.getElementById('totalDisplay');
            const discountRow = document.getElementById('discountRow');
            const discountDisplay = document.getElementById('discountDisplay');
            const platformFeeDisplay = document.getElementById('platformFeeDisplay');
            const participantCountBadge = document.getElementById('participantCountBadge');

            const platformFee = {{ (float) ($event->platform_fee ?? 0) }};
            const promoBuyX = {{ (int) ($event->promo_buy_x ?? 0) }};
            const eventId = {{ $event->id }};
            const eventSlug = "{{ $event->slug }}";

            let participantCount = 1;
            let appliedCoupon = null;
            let discountAmount = 0;

            const formatRp = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(num));

            const template = participantsWrapper.querySelector('.participant-item').cloneNode(true);

            function resetCoupon() {
                if (appliedCoupon || discountAmount > 0) {
                    appliedCoupon = null;
                    discountAmount = 0;
                    const codeEl = document.getElementById('coupon_code');
                    const codeHidden = document.getElementById('coupon_code_hidden');
                    const msgEl = document.getElementById('couponMessage');
                    if (codeEl) codeEl.value = '';
                    if (codeHidden) codeHidden.value = '';
                    if (msgEl) msgEl.innerHTML = '';
                }
            }

            function updateCalc() {
                let categorySubtotal = 0;
                let addonsTotal = 0;
                let count = 0;

                const categoryCounts = new Map();
                const categoryPrices = new Map();

                document.querySelectorAll('.participant-item').forEach(item => {
                    const checkedRadio = item.querySelector('input[type="radio"].cat-radio:checked');
                    if (checkedRadio) {
                        count++;
                        const catId = checkedRadio.value;
                        const price = parseFloat(checkedRadio.getAttribute('data-price') || 0);
                        categoryCounts.set(catId, (categoryCounts.get(catId) || 0) + 1);
                        categoryPrices.set(catId, price);
                    }

                    item.querySelectorAll('.addon-checkbox:checked').forEach(cb => {
                        addonsTotal += parseFloat(cb.getAttribute('data-price') || 0);
                    });
                });

                categoryCounts.forEach((qty, catId) => {
                    const price = categoryPrices.get(catId) || 0;
                    let paidQty = qty;
                    if (promoBuyX > 0) {
                        const bundle = promoBuyX + 1;
                        const freeCount = Math.floor(qty / bundle);
                        paidQty = qty - freeCount;
                    }
                    categorySubtotal += (price * paidQty);
                });

                const subtotal = categorySubtotal + addonsTotal;
                const totalFee = (subtotal - discountAmount <= 0) ? 0 : (count * platformFee);
                let grandTotal = subtotal + totalFee - discountAmount;
                if (grandTotal < 0) grandTotal = 0;

                if (subtotalDisplay) subtotalDisplay.textContent = formatRp(subtotal);
                if (platformFeeDisplay) platformFeeDisplay.textContent = formatRp(totalFee);
                if (totalDisplay) totalDisplay.textContent = formatRp(grandTotal);

                if (participantCountBadge) {
                    participantCountBadge.textContent = count + ' Peserta';
                }

                if (discountRow && discountDisplay) {
                    if (discountAmount > 0) {
                        discountRow.classList.remove('hidden');
                        discountDisplay.textContent = '- ' + formatRp(discountAmount);
                    } else {
                        discountRow.classList.add('hidden');
                        discountDisplay.textContent = '-Rp 0';
                    }
                }
            }

            function attachListeners(context) {
                context.querySelectorAll('input[type="radio"], input.addon-checkbox').forEach(input => {
                    input.addEventListener('change', () => {
                        resetCoupon();
                        updateCalc();
                    });
                });
            }

            function addParticipant() {
                resetCoupon();
                const clone = template.cloneNode(true);
                const idx = participantCount++;

                clone.setAttribute('data-index', idx);
                clone.querySelector('.participant-title').textContent = `PESERTA #${idx + 1}`;

                // Show remove button & copy prev
                const removeBtn = clone.querySelector('.remove-participant');
                if (removeBtn) removeBtn.classList.remove('hidden');

                const copyPrev = clone.querySelector('.copy-prev-btn');
                if (copyPrev) copyPrev.classList.remove('hidden');

                // Update input names
                clone.querySelectorAll('input, select, textarea').forEach(el => {
                    const name = el.getAttribute('name');
                    if (name) {
                        el.setAttribute('name', name.replace(/participants\[\d+\]/, `participants[${idx}]`));
                    }
                    if (el.type === 'radio') {
                        // Keep radios grouped per participant
                        const isFirstRadio = el.closest('.grid').firstElementChild.contains(el);
                        el.checked = isFirstRadio;
                    } else if (el.type === 'checkbox') {
                        el.checked = false;
                    } else if (el.tagName === 'SELECT') {
                        el.selectedIndex = 0;
                    } else if (el.type !== 'hidden') {
                        el.value = '';
                    }
                });

                removeBtn?.addEventListener('click', function() {
                    clone.remove();
                    resetCoupon();
                    updateCalc();
                });

                participantsWrapper.appendChild(clone);
                attachListeners(clone);
                updateCalc();

                setTimeout(() => {
                    clone.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 80);
            }

            if (addBtnTop) addBtnTop.addEventListener('click', addParticipant);
            if (addBtnBottom) addBtnBottom.addEventListener('click', addParticipant);

            attachListeners(document);
            updateCalc();

            // Coupon Logic
            const couponBtn = document.getElementById('applyCouponBtn');
            if (couponBtn) {
                couponBtn.addEventListener('click', () => {
                    const code = (document.getElementById('coupon_code').value || '').trim();
                    const msgEl = document.getElementById('couponMessage');
                    if (!code) {
                        if (msgEl) msgEl.innerHTML = '<span class="text-red-500">Masukkan kode promo.</span>';
                        return;
                    }

                    // Compute current subtotal
                    let currentSubtotal = 0;
                    document.querySelectorAll('.participant-item').forEach(item => {
                        const checkedRadio = item.querySelector('input[type="radio"].cat-radio:checked');
                        if (checkedRadio) {
                            currentSubtotal += parseFloat(checkedRadio.getAttribute('data-price') || 0);
                        }
                    });

                    if (currentSubtotal <= 0) {
                        if (msgEl) msgEl.innerHTML = '<span class="text-red-500">Pilih kategori peserta terlebih dahulu.</span>';
                        return;
                    }

                    const originalText = couponBtn.innerHTML;
                    couponBtn.innerHTML = '...';
                    couponBtn.disabled = true;

                    fetch(`{{ route('events.register.coupon', $event->slug) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            event_id: eventId,
                            coupon_code: code,
                            total_amount: currentSubtotal
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            appliedCoupon = data.coupon;
                            discountAmount = parseFloat(data.discount_amount || 0);
                            document.getElementById('coupon_code_hidden').value = data.coupon.code;
                            if (msgEl) msgEl.innerHTML = '<span class="text-emerald-600 font-bold">Kupon berhasil digunakan!</span>';
                            updateCalc();
                        } else {
                            discountAmount = 0;
                            document.getElementById('coupon_code_hidden').value = '';
                            if (msgEl) msgEl.innerHTML = `<span class="text-red-500">${data.message || 'Kupon tidak valid.'}</span>`;
                            updateCalc();
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (msgEl) msgEl.innerHTML = '<span class="text-red-500">Gagal memvalidasi kupon.</span>';
                    })
                    .finally(() => {
                        couponBtn.innerHTML = originalText;
                        couponBtn.disabled = false;
                    });
                });
            }

            // Modal Helpers
            let isConfirmed = false;

            window.closeConfirmationModal = function() {
                const modal = document.getElementById('confirmationModal');
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
                document.body.classList.remove('overflow-hidden');
            };

            window.closeFailureModal = function() {
                const failModal = document.getElementById('registrationFailureModal');
                if (failModal) {
                    failModal.classList.add('hidden');
                    failModal.classList.remove('flex');
                }
                document.body.classList.remove('overflow-hidden');

                // If any invalid input exists, focus it smoothly
                const firstErr = form.querySelector('.input-error');
                if (firstErr) {
                    setTimeout(() => {
                        firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstErr.focus();
                    }, 60);
                }
            };

            window.openFailureModal = function(contentHtml) {
                const failModal = document.getElementById('registrationFailureModal');
                const failMsg = document.getElementById('registrationFailureMessage');
                if (failModal) {
                    if (failMsg && contentHtml) {
                        failMsg.innerHTML = contentHtml;
                    }
                    failModal.classList.remove('hidden');
                    failModal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                } else {
                    alert((contentHtml || 'Terjadi kendala pada pendaftaran.').replace(/<[^>]*>?/gm, ' '));
                }
            };

            // Event listeners for failure modal close
            const failCloseBtn = document.getElementById('closeFailureNowBtn');
            const failBackdropEl = document.getElementById('registrationFailureModalBackdrop');
            if (failCloseBtn) failCloseBtn.addEventListener('click', window.closeFailureModal);
            if (failBackdropEl) failBackdropEl.addEventListener('click', window.closeFailureModal);

            // Escape key listener for all modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    window.closeConfirmationModal();
                    window.closeFailureModal();
                    if (typeof closeTermsModal === 'function') closeTermsModal();
                    if (typeof closeLightbox === 'function') closeLightbox();
                }
            });

            // Comprehensive Client-side Form Validation with Visible Inline Messages
            function showFieldError(input, msg, globalMsg = null) {
                if (!input) return;

                input.classList.add('input-error');

                // Determine target container and insert position
                let container = null;
                let insertAfter = null;

                if (input.classList.contains('cat-radio')) {
                    const grid = input.closest('.grid');
                    if (grid) {
                        container = grid.parentElement;
                        insertAfter = grid;
                    }
                } else if (input.name === 'payment_method') {
                    const paymentWrapper = input.closest('.space-y-2\\.5') || input.closest('div');
                    if (paymentWrapper) {
                        container = paymentWrapper;
                        insertAfter = null;
                    }
                } else if (input.name === 'terms_agreed') {
                    const lbl = input.closest('label');
                    if (lbl) {
                        container = lbl.parentElement;
                        insertAfter = lbl;
                    }
                } else {
                    container = input.parentElement;
                    insertAfter = input;
                }

                if (!container) container = input.parentElement;

                let errEl = container.querySelector('.field-error-msg');
                if (!errEl) {
                    errEl = document.createElement('p');
                    errEl.className = 'field-error-msg text-xs text-rose-600 font-semibold mt-1.5 flex items-center gap-1.5';
                    if (insertAfter && insertAfter.nextSibling) {
                        container.insertBefore(errEl, insertAfter.nextSibling);
                    } else {
                        container.appendChild(errEl);
                    }
                }

                errEl.innerHTML = `<i class="fas fa-exclamation-circle text-[11px] shrink-0"></i><span>${msg}</span>`;

                if (!firstErrorInput) {
                    firstErrorInput = input;
                    firstErrorMessage = globalMsg || msg;
                }
                errorCount++;
            }

            function clearFieldError(input) {
                if (!input) return;
                input.classList.remove('input-error');

                let container = null;
                if (input.classList.contains('cat-radio')) {
                    container = input.closest('.grid')?.parentElement;
                } else if (input.name === 'payment_method') {
                    container = input.closest('.space-y-2\\.5') || input.closest('div');
                } else if (input.name === 'terms_agreed') {
                    container = input.closest('label')?.parentElement;
                } else {
                    container = input.parentElement;
                }

                const err = container?.querySelector('.field-error-msg');
                if (err) err.remove();

                if (!form.querySelector('.field-error-msg')) {
                    const banner = document.getElementById('formValidationBanner');
                    if (banner) banner.remove();
                }
            }

            function clearAllFieldErrors() {
                form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
                form.querySelectorAll('.field-error-msg').forEach(el => el.remove());
                const banner = document.getElementById('formValidationBanner');
                if (banner) banner.remove();
            }

            // Real-time error highlight removal on input
            form.addEventListener('input', function(e) {
                if (e.target) clearFieldError(e.target);
            });
            form.addEventListener('change', function(e) {
                if (e.target) clearFieldError(e.target);
            });

            let firstErrorInput = null;
            let firstErrorMessage = '';
            let errorCount = 0;

            // Comprehensive Client-side Form Validation
            function validateRegistrationForm() {
                clearAllFieldErrors();
                firstErrorInput = null;
                firstErrorMessage = '';
                errorCount = 0;

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                // 1. Validate PIC Data
                const picNameInput = form.querySelector('[name="pic_name"]');
                const picEmailInput = form.querySelector('[name="pic_email"]');
                const picPhoneInput = form.querySelector('[name="pic_phone"]');

                if (picNameInput) {
                    const val = picNameInput.value.trim();
                    if (!val) {
                        showFieldError(picNameInput, 'Nama PIC wajib diisi.', 'Nama PIC wajib diisi.');
                    } else if (val.length < 2) {
                        showFieldError(picNameInput, 'Nama PIC minimal 2 karakter.', 'Nama PIC minimal 2 karakter.');
                    }
                }

                if (picEmailInput) {
                    const val = picEmailInput.value.trim();
                    if (!val) {
                        showFieldError(picEmailInput, 'Email PIC wajib diisi.', 'Email PIC wajib diisi.');
                    } else if (!emailRegex.test(val)) {
                        showFieldError(picEmailInput, 'Format email PIC tidak valid.', 'Format email PIC tidak valid.');
                    }
                }

                if (picPhoneInput) {
                    const rawVal = picPhoneInput.value.trim();
                    const digits = rawVal.replace(/[^0-9]/g, '');
                    if (!digits) {
                        showFieldError(picPhoneInput, 'Nomor WhatsApp PIC wajib diisi.', 'Nomor WhatsApp PIC wajib diisi.');
                    } else if (digits.length < 10) {
                        showFieldError(picPhoneInput, 'Nomor WhatsApp PIC minimal 10 digit angka.', 'Nomor WhatsApp PIC minimal 10 digit angka.');
                    } else if (digits.length > 15) {
                        showFieldError(picPhoneInput, 'Nomor WhatsApp PIC maksimal 15 digit angka.', 'Nomor WhatsApp PIC maksimal 15 digit angka.');
                    }
                }

                // 2. Validate Participants Data
                const participantItems = form.querySelectorAll('.participant-item');
                if (participantItems.length === 0) {
                    window.openFailureModal('Minimal harus ada 1 peserta lomba.');
                    return false;
                }

                const participantEmails = [];
                const participantNikList = [];

                participantItems.forEach((item, index) => {
                    const participantNum = index + 1;
                    const labelPrefix = `Peserta #${participantNum}: `;

                    // Category
                    const catChecked = item.querySelector('input[type="radio"].cat-radio:checked');
                    if (!catChecked) {
                        const firstCatRadio = item.querySelector('input[type="radio"].cat-radio');
                        showFieldError(firstCatRadio, 'Silakan pilih salah satu kategori lomba.', `${labelPrefix}Pilih salah satu kategori lomba.`);
                    }

                    // Name
                    const nameInput = item.querySelector('input[name*="[name]"]');
                    if (nameInput) {
                        const val = nameInput.value.trim();
                        if (!val) {
                            showFieldError(nameInput, 'Nama lengkap peserta wajib diisi.', `${labelPrefix}Nama lengkap wajib diisi.`);
                        } else if (val.length < 2) {
                            showFieldError(nameInput, 'Nama lengkap minimal 2 karakter.', `${labelPrefix}Nama lengkap minimal 2 karakter.`);
                        }
                    }

                    // Gender
                    const genderSelect = item.querySelector('select[name*="[gender]"], input[name*="[gender]"]:checked');
                    const genderVal = genderSelect ? genderSelect.value : '';
                    if (!genderVal) {
                        const selectEl = item.querySelector('select[name*="[gender]"]');
                        showFieldError(selectEl, 'Jenis kelamin wajib dipilih.', `${labelPrefix}Jenis kelamin wajib dipilih.`);
                    }

                    // Email
                    const emailInput = item.querySelector('input[name*="[email]"]');
                    if (emailInput) {
                        const val = emailInput.value.trim().toLowerCase();
                        if (!val) {
                            showFieldError(emailInput, 'Email peserta wajib diisi.', `${labelPrefix}Email peserta wajib diisi.`);
                        } else if (!emailRegex.test(val)) {
                            showFieldError(emailInput, 'Format email tidak valid.', `${labelPrefix}Format email tidak valid.`);
                        } else if (participantEmails.includes(val)) {
                            showFieldError(emailInput, `Email "${val}" sudah digunakan peserta lain. Email setiap peserta harus unik.`, `${labelPrefix}Email "${val}" sudah digunakan peserta lain.`);
                        } else {
                            participantEmails.push(val);
                        }
                    }

                    // Phone
                    const phoneInput = item.querySelector('input[name*="[phone]"]');
                    if (phoneInput) {
                        const digits = phoneInput.value.trim().replace(/[^0-9]/g, '');
                        if (!digits) {
                            showFieldError(phoneInput, 'Nomor WhatsApp/HP wajib diisi.', `${labelPrefix}Nomor WhatsApp/HP wajib diisi.`);
                        } else if (digits.length < 10) {
                            showFieldError(phoneInput, 'Nomor WhatsApp minimal 10 digit angka.', `${labelPrefix}Nomor WhatsApp minimal 10 digit angka.`);
                        } else if (digits.length > 15) {
                            showFieldError(phoneInput, 'Nomor WhatsApp maksimal 15 digit angka.', `${labelPrefix}Nomor WhatsApp maksimal 15 digit angka.`);
                        }
                    }

                    // ID Card / NIK
                    const idCardInput = item.querySelector('input[name*="[id_card]"]');
                    if (idCardInput && (idCardInput.hasAttribute('required') || idCardInput.value.trim())) {
                        const val = idCardInput.value.trim();
                        if (!val) {
                            showFieldError(idCardInput, 'Nomor identitas (NIK/KTP/SIM) wajib diisi.', `${labelPrefix}Nomor identitas (NIK/KTP/SIM) wajib diisi.`);
                        } else if (val.length < 5) {
                            showFieldError(idCardInput, 'Nomor identitas minimal 5 karakter.', `${labelPrefix}Nomor identitas minimal 5 karakter.`);
                        } else if (participantNikList.includes(val)) {
                            showFieldError(idCardInput, `Nomor identitas "${val}" sudah digunakan peserta lain.`, `${labelPrefix}Nomor identitas "${val}" sudah digunakan peserta lain.`);
                        } else {
                            participantNikList.push(val);
                        }
                    }

                    // Date of Birth
                    const dobInput = item.querySelector('input[name*="[date_of_birth]"]');
                    if (dobInput && (dobInput.hasAttribute('required') || dobInput.value.trim())) {
                        const val = dobInput.value.trim();
                        if (!val) {
                            showFieldError(dobInput, 'Tanggal lahir wajib diisi.', `${labelPrefix}Tanggal lahir wajib diisi.`);
                        } else {
                            const dobDate = new Date(val);
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            if (isNaN(dobDate.getTime()) || dobDate >= today) {
                                showFieldError(dobInput, 'Tanggal lahir harus sebelum hari ini.', `${labelPrefix}Tanggal lahir harus sebelum hari ini.`);
                            }
                        }
                    }

                    // Address
                    const addressInput = item.querySelector('textarea[name*="[address]"], input[name*="[address]"]');
                    if (addressInput && (addressInput.hasAttribute('required') || addressInput.value.trim())) {
                        const val = addressInput.value.trim();
                        if (!val) {
                            showFieldError(addressInput, 'Alamat lengkap domisili wajib diisi.', `${labelPrefix}Alamat lengkap domisili wajib diisi.`);
                        } else if (val.length < 5) {
                            showFieldError(addressInput, 'Alamat lengkap minimal 5 karakter.', `${labelPrefix}Alamat lengkap minimal 5 karakter.`);
                        }
                    }

                    // Emergency Contact Name
                    const emNameInput = item.querySelector('input[name*="[emergency_contact_name]"]');
                    if (emNameInput && (emNameInput.hasAttribute('required') || emNameInput.value.trim())) {
                        const val = emNameInput.value.trim();
                        if (!val) {
                            showFieldError(emNameInput, 'Nama kontak darurat wajib diisi.', `${labelPrefix}Nama kontak darurat wajib diisi.`);
                        } else if (val.length < 2) {
                            showFieldError(emNameInput, 'Nama kontak darurat minimal 2 karakter.', `${labelPrefix}Nama kontak darurat minimal 2 karakter.`);
                        }
                    }

                    // Emergency Contact Number
                    const emNumInput = item.querySelector('input[name*="[emergency_contact_number]"]');
                    if (emNumInput && (emNumInput.hasAttribute('required') || emNumInput.value.trim())) {
                        const rawVal = emNumInput.value.trim();
                        const digits = rawVal.replace(/[^0-9]/g, '');
                        if (!digits) {
                            showFieldError(emNumInput, 'Nomor kontak darurat wajib diisi.', `${labelPrefix}Nomor kontak darurat wajib diisi.`);
                        } else if (digits.length < 10) {
                            showFieldError(emNumInput, 'Nomor kontak darurat minimal 10 digit angka.', `${labelPrefix}Nomor kontak darurat minimal 10 digit angka.`);
                        } else if (digits.length > 15) {
                            showFieldError(emNumInput, 'Nomor kontak darurat maksimal 15 digit angka.', `${labelPrefix}Nomor kontak darurat maksimal 15 digit angka.`);
                        }
                    }

                    // Jersey Size
                    const jerseySelect = item.querySelector('select[name*="[jersey_size]"]');
                    if (jerseySelect && jerseySelect.hasAttribute('required')) {
                        const val = jerseySelect.value.trim();
                        if (!val) {
                            showFieldError(jerseySelect, 'Ukuran jersey wajib dipilih.', `${labelPrefix}Ukuran jersey wajib dipilih.`);
                        }
                    }

                    // Target Time
                    const targetTimeInput = item.querySelector('input[name*="[target_time]"]');
                    if (targetTimeInput && targetTimeInput.value.trim()) {
                        const val = targetTimeInput.value.trim();
                        if (!/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/.test(val)) {
                            showFieldError(targetTimeInput, 'Format target waktu harus JJ:MM:DD (contoh: 01:30:00).', `${labelPrefix}Format target waktu harus JJ:MM:DD.`);
                        }
                    }
                });

                // 3. Payment Method
                const paymentRadios = form.querySelectorAll('input[name="payment_method"]');
                if (paymentRadios.length > 0) {
                    const checkedPm = form.querySelector('input[name="payment_method"]:checked');
                    if (!checkedPm) {
                        showFieldError(paymentRadios[0], 'Silakan pilih salah satu metode pembayaran.', 'Silakan pilih metode pembayaran.');
                    }
                }

                // 4. Terms Agreement
                const termsCheckbox = form.querySelector('input[name="terms_agreed"]');
                if (termsCheckbox && termsCheckbox.hasAttribute('required') && !termsCheckbox.checked) {
                    showFieldError(termsCheckbox, 'Anda harus menyetujui Syarat & Ketentuan lomba.', 'Anda harus menyetujui Syarat & Ketentuan lomba.');
                }

                if (firstErrorInput) {
                    // Show a summary banner in checkout card above submit button
                    const submitBtn = document.getElementById('submitBtn');
                    let banner = document.getElementById('formValidationBanner');
                    if (!banner && submitBtn && submitBtn.parentElement) {
                        banner = document.createElement('div');
                        banner.id = 'formValidationBanner';
                        banner.className = 'p-3 mb-3 rounded-md bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-2 shadow-sm animate-fadeIn';
                        submitBtn.parentElement.insertBefore(banner, submitBtn);
                    }
                    if (banner) {
                        banner.innerHTML = `<i class="fas fa-exclamation-circle text-rose-500 mt-0.5 shrink-0"></i><div><strong class="font-bold block">Mohon periksa kolom yang ditandai merah:</strong><span class="text-rose-600">${firstErrorMessage}</span></div>`;
                    }

                    firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => {
                        try {
                            firstErrorInput.focus({ preventScroll: true });
                        } catch(err) {
                            firstErrorInput.focus();
                        }
                    }, 120);

                    return false;
                }

                return true;
            }

            form.addEventListener('submit', function(e) {
                if (isConfirmed) return;

                e.preventDefault();

                // Run Strict Pre-Submit Client-Side Validation
                if (!validateRegistrationForm()) {
                    return;
                }

                // Populate Confirmation Review
                const picName = form.querySelector('[name="pic_name"]')?.value || '';
                const picPhone = form.querySelector('[name="pic_phone"]')?.value || '';
                const picEmail = form.querySelector('[name="pic_email"]')?.value || '';

                document.getElementById('confPicName').textContent = picName || '-';
                document.getElementById('confPicPhone').textContent = picPhone || '-';
                document.getElementById('confPicEmail').textContent = picEmail || '-';

                const listContainer = document.getElementById('confParticipantsList');
                listContainer.innerHTML = '';

                const items = document.querySelectorAll('.participant-item');
                document.getElementById('confCountBadge').textContent = items.length + ' Peserta';

                items.forEach((item, i) => {
                    const pName = item.querySelector('input[name*="[name]"]')?.value || '-';
                    const pNik = item.querySelector('input[name*="[id_card]"]')?.value || '-';
                    const pSize = item.querySelector('select[name*="[jersey_size]"]')?.value || '-';
                    const catRadio = item.querySelector('input[type="radio"].cat-radio:checked');
                    let catName = '-';
                    if (catRadio) {
                        catName = catRadio.closest('label').querySelector('.font-bold')?.textContent || '-';
                    }

                    const row = document.createElement('div');
                    row.className = 'bg-white border border-slate-200 rounded p-2.5 text-xs flex justify-between items-center';
                    row.innerHTML = `
                        <div>
                            <span class="font-bold text-slate-900 block">${i + 1}. ${pName}</span>
                            <span class="text-[11px] text-slate-500">NIK: ${pNik} • Jersey: ${pSize}</span>
                        </div>
                        <span class="font-bold text-theme-primary text-xs">${catName}</span>
                    `;
                    listContainer.appendChild(row);
                });

                document.getElementById('confTotal').textContent = totalDisplay ? totalDisplay.textContent : 'Rp 0';

                // Open Confirmation Modal
                const confModal = document.getElementById('confirmationModal');
                if (confModal) {
                    confModal.classList.remove('hidden');
                    confModal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }
            });

            // Confirm Submit Click
            const confirmBtn = document.getElementById('confirmSubmitBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    isConfirmed = true;
                    closeConfirmationModal();
                    processSubmission();
                });
            }

            function processSubmission() {
                const btn = document.getElementById('submitBtn');
                const originalText = btn ? btn.innerHTML : '';
                if (btn) {
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
                    btn.disabled = true;
                }

                const executeRequest = () => {
                    const formData = new FormData(form);
                    const tokenEl = document.getElementById('recaptchaToken');
                    if (tokenEl && tokenEl.value) {
                        formData.set('g-recaptcha-response', tokenEl.value);
                    }

                    fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async r => {
                        let data;
                        try {
                            data = await r.json();
                        } catch(parseErr) {
                            data = { success: false, message: 'Respon server tidak valid (' + r.status + ').' };
                        }

                        if (!r.ok || !data.success) {
                            let errorList = [];
                            if (data.errors && typeof data.errors === 'object') {
                                Object.entries(data.errors).forEach(([field, msgs]) => {
                                    const arr = Array.isArray(msgs) ? msgs : [msgs];
                                    arr.forEach(msg => errorList.push(msg));

                                    // Map Laravel dot notation to form input name
                                    const parts = field.split('.');
                                    let fieldName = parts[0];
                                    for (let i = 1; i < parts.length; i++) {
                                        fieldName += `[${parts[i]}]`;
                                    }
                                    const invalidEl = form.querySelector(`[name="${fieldName}"]`);
                                    if (invalidEl) {
                                        showFieldError(invalidEl, arr[0], arr[0]);
                                    }
                                });
                            }

                            let formattedMsg = '';
                            if (errorList.length > 0) {
                                formattedMsg = '<ul class="list-disc pl-4 space-y-1">' + errorList.map(e => `<li>${e}</li>`).join('') + '</ul>';
                            } else {
                                formattedMsg = data.message || 'Terjadi kesalahan pada proses registrasi.';
                            }

                            window.openFailureModal(formattedMsg);

                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = originalText;
                                isConfirmed = false;
                            }
                            return;
                        }

                        // Cache participant info for E-ticket
                        try {
                            const participantsData = [];
                            document.querySelectorAll('.participant-item').forEach(item => {
                                const n = item.querySelector('input[name*="[name]"]')?.value;
                                const sz = item.querySelector('select[name*="[jersey_size]"]')?.value;
                                const catRadio = item.querySelector('input[type="radio"].cat-radio:checked');
                                const catLabel = catRadio ? catRadio.closest('label').querySelector('.font-bold')?.textContent : '';
                                const ph = item.querySelector('input[name*="[phone]"]')?.value;
                                const idc = item.querySelector('input[name*="[id_card]"]')?.value;
                                if (n) {
                                    participantsData.push({
                                        participant_name: n,
                                        jersey_size_label: sz,
                                        category_label: catLabel,
                                        phone: ph,
                                        id_card: idc
                                    });
                                }
                            });

                            const cachePayload = {
                                participants: participantsData,
                                registration_id: data.registration_id || null,
                                transaction_id: data.transaction_id || null,
                                pic_phone: form.querySelector('[name="pic_phone"]')?.value || ''
                            };
                            localStorage.setItem('ruanglari_last_ticket_' + eventId, JSON.stringify(cachePayload));
                        } catch(e) {}

                        // 1. Midtrans Snap
                        if (data.success && data.snap_token) {
                            snap.pay(data.snap_token, {
                                onSuccess: function() {
                                    window.location.href = `{{ route("events.show", $event->slug) }}?payment=success&ref=` + (data.registration_id || '');
                                },
                                onPending: function() {
                                    window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending&tx=` + (data.transaction_id || '');
                                },
                                onError: function() {
                                    alert('Pembayaran tidak berhasil diselesaikan.');
                                    if (btn) { btn.disabled = false; btn.innerHTML = originalText; isConfirmed = false; }
                                },
                                onClose: function() {
                                    window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending&tx=` + (data.transaction_id || '');
                                }
                            });
                            return;
                        }

                        // 2. Moota Payment
                        if (data.success && data.payment_gateway === 'moota') {
                            if (window.RuangLariMoota && typeof window.RuangLariMoota.open === 'function' && data.transaction_id) {
                                if (btn) { btn.disabled = false; btn.innerHTML = originalText; isConfirmed = false; }
                                window.RuangLariMoota.open({
                                    transaction_id: data.transaction_id,
                                    registration_id: data.registration_id,
                                    final_amount: data.final_amount,
                                    unique_code: data.unique_code,
                                    phone: form.querySelector('[name="pic_phone"]')?.value || '',
                                    name: form.querySelector('[name="pic_name"]')?.value || '',
                                });
                                return;
                            }
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                                return;
                            }
                        }

                        // 3. Direct Success (Free / COD / Completed)
                        if (data.success) {
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.href = `{{ route("events.show", $event->slug) }}?payment=success&ref=` + (data.registration_id || '');
                            }
                            return;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Gagal menghubungi server. Silakan coba kembali.');
                        if (btn) { btn.disabled = false; btn.innerHTML = originalText; isConfirmed = false; }
                    });
                };

                @if(env('RECAPTCHA_SITE_KEY_v3'))
                    if (typeof grecaptcha !== 'undefined') {
                        grecaptcha.ready(function() {
                            grecaptcha.execute('{{ env('RECAPTCHA_SITE_KEY_v3') }}', {action: 'event_register'})
                            .then(function(token) {
                                const el = document.getElementById('recaptchaToken');
                                if (el) el.value = token;
                                executeRequest();
                            })
                            .catch(function(err) {
                                console.error('reCAPTCHA error:', err);
                                executeRequest();
                            });
                        });
                    } else {
                        executeRequest();
                    }
                @else
                    executeRequest();
                @endif
            }
        })();

        // E-Ticket Canvas Modal Handling on Success
        (function() {
            const successModal = document.getElementById('registrationSuccessModal');
            if (!successModal) return;

            const closeBtn = document.getElementById('registrationSuccessCloseBtn');
            const closeNowBtn = document.getElementById('closeNowBtn');
            const downloadBtn = document.getElementById('downloadEticketBtn');
            const canvas = document.getElementById('registrationSuccessEticketCanvas');
            const prevBtn = document.getElementById('eticketPrevBtn');
            const nextBtn = document.getElementById('eticketNextBtn');
            const participantLabel = document.getElementById('eticketParticipantLabel');
            const participantIndexEl = document.getElementById('eticketParticipantIndex');
            const participantTotalEl = document.getElementById('eticketParticipantTotal');
            const container = document.getElementById('registrationSuccessContainer');

            let cachedParticipants = [];
            let currentParticipantIdx = 0;
            let ticketNumber = '';

            function loadTicketCache() {
                try {
                    const eventId = container?.dataset.eventId || '{{ $event->id }}';
                    const raw = localStorage.getItem('ruanglari_last_ticket_' + eventId);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        ticketNumber = parsed.registration_id || '';
                        if (Array.isArray(parsed.participants)) {
                            cachedParticipants = parsed.participants;
                        }
                    }
                } catch(e) {}
            }

            function drawTicket(idx) {
                if (!canvas) return;
                const ctx = canvas.getContext('2d');
                if (!ctx) return;

                const eventName = container?.dataset.eventName || '{{ $event->name }}';
                const eventDate = container?.dataset.eventDate || '{{ $ticketDate }}';
                const eventLocation = container?.dataset.eventLocation || '{{ $ticketLocation }}';
                const primaryColor = window.THEME_PRIMARY_COLOR || '{{ $primaryColor }}';

                let pName = '-';
                let pSize = '-';
                let pCat = '-';

                if (cachedParticipants.length > 0) {
                    const p = cachedParticipants[idx] || cachedParticipants[0];
                    pName = p.participant_name || '-';
                    pSize = p.jersey_size_label || '-';
                    pCat = p.category_label || '-';
                }

                if (participantLabel) participantLabel.textContent = pName;
                if (participantIndexEl) participantIndexEl.textContent = String(idx + 1);
                if (participantTotalEl) participantTotalEl.textContent = String(cachedParticipants.length || 1);

                const w = canvas.width;
                const h = canvas.height;
                ctx.clearRect(0, 0, w, h);

                // Ticket Background
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, w, h);

                // Top Primary Header Bar
                ctx.fillStyle = primaryColor;
                ctx.fillRect(0, 0, w, 24);

                // Outer border
                ctx.strokeStyle = '#cbd5e1';
                ctx.lineWidth = 2;
                ctx.strokeRect(1, 1, w - 2, h - 2);

                // Dotted Cut Separator
                const cutX = w * 0.68;
                ctx.strokeStyle = '#94a3b8';
                ctx.setLineDash([8, 6]);
                ctx.beginPath();
                ctx.moveTo(cutX, 36);
                ctx.lineTo(cutX, h - 36);
                ctx.stroke();
                ctx.setLineDash([]);

                // Left Section: Event Info
                ctx.fillStyle = '#0f172a';
                ctx.font = '800 28px "Inter Tight", sans-serif';
                ctx.fillText(eventName.toUpperCase(), 40, 80);

                ctx.fillStyle = '#64748b';
                ctx.font = '600 13px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('WAKTU & TANGGAL PELAKSANAAN', 40, 125);

                ctx.fillStyle = '#0f172a';
                ctx.font = '700 16px "Plus Jakarta Sans", sans-serif';
                ctx.fillText(eventDate, 40, 148);

                ctx.fillStyle = '#64748b';
                ctx.font = '600 13px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('LOKASI VENUE', 40, 190);

                ctx.fillStyle = '#0f172a';
                ctx.font = '700 16px "Plus Jakarta Sans", sans-serif';
                ctx.fillText(eventLocation, 40, 213);

                // Participant Box
                ctx.fillStyle = '#f8fafc';
                ctx.fillRect(40, 255, cutX - 80, 140);
                ctx.strokeStyle = '#e2e8f0';
                ctx.strokeRect(40, 255, cutX - 80, 140);

                ctx.fillStyle = '#64748b';
                ctx.font = '600 12px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('NAMA PESERTA', 60, 285);

                ctx.fillStyle = '#0f172a';
                ctx.font = '800 20px "Plus Jakarta Sans", sans-serif';
                ctx.fillText(pName, 60, 312);

                ctx.fillStyle = '#64748b';
                ctx.font = '600 12px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('KATEGORI', 60, 350);

                ctx.fillStyle = primaryColor;
                ctx.font = '700 16px "Plus Jakarta Sans", sans-serif';
                ctx.fillText(pCat, 60, 375);

                // Right Section: Stub
                ctx.fillStyle = '#10b981';
                ctx.font = '800 22px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('CONFIRMED', cutX + 35, 80);

                ctx.fillStyle = '#64748b';
                ctx.font = '600 12px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('UKURAN JERSEY', cutX + 35, 125);

                ctx.fillStyle = primaryColor;
                ctx.font = '800 24px "Plus Jakarta Sans", sans-serif';
                ctx.fillText(pSize, cutX + 35, 155);

                ctx.fillStyle = '#64748b';
                ctx.font = '600 12px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('NO. REGISTRASI', cutX + 35, 205);

                ctx.fillStyle = '#0f172a';
                ctx.font = '700 14px "JetBrains Mono", monospace';
                ctx.fillText(ticketNumber || 'VERIFIED', cutX + 35, 228);

                // Instructions footer
                ctx.fillStyle = '#94a3b8';
                ctx.font = '500 11px "Plus Jakarta Sans", sans-serif';
                ctx.fillText('Tunjukkan E-Ticket ini saat Race Pack Collection (RPC).', 40, 425);
            }

            function openSuccessModal() {
                loadTicketCache();
                drawTicket(0);
                successModal.classList.remove('hidden');
                successModal.classList.add('flex');
            }

            function closeSuccessModal() {
                successModal.classList.add('hidden');
                successModal.classList.remove('flex');
                try {
                    const u = new URL(window.location.href);
                    u.searchParams.delete('payment');
                    window.history.replaceState({}, '', u.toString());
                } catch(e) {}
            }

            closeBtn?.addEventListener('click', closeSuccessModal);
            closeNowBtn?.addEventListener('click', closeSuccessModal);

            prevBtn?.addEventListener('click', () => {
                if (cachedParticipants.length > 1) {
                    currentParticipantIdx = (currentParticipantIdx - 1 + cachedParticipants.length) % cachedParticipants.length;
                    drawTicket(currentParticipantIdx);
                }
            });

            nextBtn?.addEventListener('click', () => {
                if (cachedParticipants.length > 1) {
                    currentParticipantIdx = (currentParticipantIdx + 1) % cachedParticipants.length;
                    drawTicket(currentParticipantIdx);
                }
            });

            downloadBtn?.addEventListener('click', function() {
                if (!canvas) return;
                const link = document.createElement('a');
                link.download = 'eticket-' + '{{ Str::slug($event->name) }}' + '-' + (currentParticipantIdx + 1) + '.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });

            // Check URL param payment=success
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('payment') === 'success') {
                openSuccessModal();
            }
        })();

        // ==========================================
        // GPX INTERACTIVE MAP & LAZY LOADER MODULE
        // ==========================================
        window.activeGpxIdx = 0;
        window.gpxMaps = {};
        window.isLeafletLoaded = false;
        window.isLeafletLoading = false;
        window.gpxDataList = {!! json_encode(array_map(function($g) {
            return [
                'id' => $g['id'],
                'category_name' => $g['category_name'],
                'distance_formatted' => $g['distance_formatted'],
                'gpx_url' => $g['gpx_url'],
                'detail_url' => $g['detail_url'],
                'coordinates' => $g['coordinates'] ?? [],
            ];
        }, $gpxDataList)) !!};

        window.switchGpxCategory = function(targetIdx) {
            window.activeGpxIdx = targetIdx;
            
            // Switch tabs styling
            document.querySelectorAll('.gpx-tab-btn').forEach(btn => {
                const bIdx = parseInt(btn.getAttribute('data-gpx-tab-idx'), 10);
                if (bIdx === targetIdx) {
                    btn.classList.add('bg-theme-primary', 'text-white', 'shadow-sm');
                    btn.classList.remove('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
                } else {
                    btn.classList.remove('bg-theme-primary', 'text-white', 'shadow-sm');
                    btn.classList.add('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
                }
            });

            // Update header Database GPX button URL if present
            const headerDbBtn = document.getElementById('btn-header-gpx-detail');
            if (headerDbBtn && window.gpxDataList && window.gpxDataList[targetIdx] && window.gpxDataList[targetIdx].detail_url) {
                headerDbBtn.href = window.gpxDataList[targetIdx].detail_url;
            }

            // Switch content panes
            document.querySelectorAll('.gpx-tab-pane').forEach((pane, idx) => {
                if (idx === targetIdx) {
                    pane.classList.remove('hidden');
                    if (window.gpxMaps[targetIdx]) {
                        setTimeout(() => {
                            window.gpxMaps[targetIdx].invalidateSize();
                        }, 100);
                    } else {
                        window.loadGpxMap(targetIdx);
                    }
                } else {
                    pane.classList.add('hidden');
                }
            });
        };

        window.ensureLeafletLibraries = function() {
            return new Promise((resolve, reject) => {
                if (window.isLeafletLoaded && window.L && window.L.GPX) {
                    return resolve();
                }

                if (window.isLeafletLoading) {
                    const checkInterval = setInterval(() => {
                        if (window.isLeafletLoaded && window.L && window.L.GPX) {
                            clearInterval(checkInterval);
                            resolve();
                        }
                    }, 50);
                    return;
                }

                window.isLeafletLoading = true;

                // 1. Inject Leaflet CSS
                if (!document.getElementById('leaflet-css-cdn')) {
                    const link = document.createElement('link');
                    link.id = 'leaflet-css-cdn';
                    link.rel = 'stylesheet';
                    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    document.head.appendChild(link);
                }

                // 2. Inject Leaflet JS
                const loadLeafletJs = new Promise((resJs, rejJs) => {
                    if (window.L) return resJs();
                    const s = document.createElement('script');
                    s.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    s.async = true;
                    s.onload = resJs;
                    s.onerror = rejJs;
                    document.head.appendChild(s);
                });

                loadLeafletJs.then(() => {
                    if (window.L && window.L.GPX) {
                        window.isLeafletLoaded = true;
                        window.isLeafletLoading = false;
                        return resolve();
                    }

                    const gpxScript = document.createElement('script');
                    gpxScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/gpx.min.js';
                    gpxScript.async = true;
                    gpxScript.onload = () => {
                        window.isLeafletLoaded = true;
                        window.isLeafletLoading = false;
                        resolve();
                    };
                    gpxScript.onerror = () => {
                        window.isLeafletLoading = false;
                        reject(new Error('Gagal memuat plugin Leaflet GPX.'));
                    };
                    document.head.appendChild(gpxScript);
                }).catch((err) => {
                    window.isLeafletLoading = false;
                    reject(err);
                });
            });
        };

        window.loadGpxMap = function(idx) {
            const dataItem = (window.gpxDataList && window.gpxDataList[idx]) ? window.gpxDataList[idx] : null;
            if (!dataItem) return;

            const placeholder = document.getElementById('gpx-placeholder-' + idx);
            const loadBtn = document.getElementById('btn-load-gpx-map-' + idx);
            const mapDom = document.getElementById('gpx-live-map-' + idx);

            if (!mapDom) return;

            if (loadBtn) {
                loadBtn.disabled = true;
                loadBtn.innerHTML = '<i class="fas fa-spinner fa-spin text-xs"></i> <span>Memuat Peta...</span>';
            }

            window.ensureLeafletLibraries().then(() => {
                if (placeholder) {
                    placeholder.classList.add('hidden');
                }

                if (window.gpxMaps[idx]) {
                    window.gpxMaps[idx].invalidateSize();
                    return;
                }

                const themeColor = getComputedStyle(document.documentElement).getPropertyValue('--theme-primary').trim() || '#059669';

                // Initialize Leaflet Map
                const map = L.map(mapDom, {
                    scrollWheelZoom: false, // Prevent scroll trapping on mobile
                    zoomControl: true,
                }).setView([-6.2, 106.8], 12);

                // Add CartoDB Voyager tiles (clean light tiles)
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    maxZoom: 19,
                    subdomains: ['a', 'b', 'c', 'd'],
                    attribution: '&copy; CARTO &copy; OpenStreetMap'
                }).addTo(map);

                window.gpxMaps[idx] = map;

                let hasRenderedTrack = false;

                // 1. Try rendering polyline directly from coordinates (instant & 100% reliable)
                if (Array.isArray(dataItem.coordinates) && dataItem.coordinates.length > 1) {
                    const latlngs = [];
                    dataItem.coordinates.forEach(pt => {
                        let lat = null, lng = null;
                        if (Array.isArray(pt)) {
                            lat = parseFloat(pt[0]);
                            lng = parseFloat(pt[1]);
                        } else if (typeof pt === 'object' && pt !== null) {
                            lat = parseFloat(pt.lat || pt.latitude);
                            lng = parseFloat(pt.lng || pt.lon || pt.longitude);
                        }
                        if (!isNaN(lat) && !isNaN(lng)) {
                            latlngs.push([lat, lng]);
                        }
                    });

                    if (latlngs.length > 1) {
                        const polyline = L.polyline(latlngs, {
                            color: themeColor,
                            weight: 5,
                            opacity: 0.95,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }).addTo(map);

                        // Start Marker
                        L.circleMarker(latlngs[0], {
                            radius: 7,
                            fillColor: '#10B981',
                            color: '#ffffff',
                            weight: 2.5,
                            opacity: 1,
                            fillOpacity: 1
                        }).bindTooltip('Start (0 KM)', { permanent: false }).addTo(map);

                        // Finish Marker
                        L.circleMarker(latlngs[latlngs.length - 1], {
                            radius: 7,
                            fillColor: '#EF4444',
                            color: '#ffffff',
                            weight: 2.5,
                            opacity: 1,
                            fillOpacity: 1
                        }).bindTooltip('Finish (' + (dataItem.distance_formatted || '') + ' KM)', { permanent: false }).addTo(map);

                        map.fitBounds(polyline.getBounds(), { padding: [35, 35] });
                        hasRenderedTrack = true;
                    }
                }

                // 2. Fallback to L.GPX if coordinates not available
                if (!hasRenderedTrack && dataItem.gpx_url) {
                    new L.GPX(dataItem.gpx_url, {
                        async: true,
                        marker_options: {
                            startIconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/pin-icon-start.png',
                            endIconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/pin-icon-end.png',
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet-gpx/1.7.0/pin-shadow.png'
                        },
                        polyline_options: {
                            color: themeColor,
                            weight: 5,
                            opacity: 0.95,
                            lineCap: 'round',
                            lineJoin: 'round'
                        }
                    }).on('loaded', function(e) {
                        map.fitBounds(e.target.getBounds(), { padding: [35, 35] });
                    }).on('error', function() {
                        console.warn('GPX file could not be parsed directly:', dataItem.gpx_url);
                    }).addTo(map);
                }

                setTimeout(() => map.invalidateSize(), 200);

                window.addEventListener('resize', () => {
                    map.invalidateSize();
                });
            }).catch(err => {
                console.error(err);
                if (loadBtn) {
                    loadBtn.disabled = false;
                    loadBtn.innerHTML = '<i class="fas fa-triangle-exclamation text-xs"></i> <span>Gagal Memuat. Coba Lagi</span>';
                }
            });
        };

        // Auto-load on scroll intersection (Load saat dibuka / saat section terlihat)
        document.addEventListener('DOMContentLoaded', function() {
            const routeSec = document.getElementById('route');
            if (routeSec && 'IntersectionObserver' in window) {
                const routeObs = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            window.loadGpxMap(window.activeGpxIdx || 0);
                            obs.disconnect();
                        }
                    });
                }, { rootMargin: '150px 0px' });
                routeObs.observe(routeSec);
            }

            // Also trigger on click navbar anchor #route
            document.querySelectorAll('a[href="#route"]').forEach(a => {
                a.addEventListener('click', () => {
                    setTimeout(() => window.loadGpxMap(window.activeGpxIdx || 0), 100);
                });
            });
        });
    </script>
</body>
</html>
