@php
    $paymentConfig = $event->payment_config ?? [];
    if (isset($paymentConfig['allowed_methods']) && is_array($paymentConfig['allowed_methods'])) {
        $allowed = $paymentConfig['allowed_methods'];
        $showMidtrans = in_array('midtrans', $allowed) || in_array('all', $allowed);
        $showMoota = in_array('moota', $allowed) || in_array('all', $allowed);
        $showCOD = in_array('cod', $allowed) || in_array('all', $allowed);
        $showManualTransfer = in_array('manual_transfer', $allowed) || in_array('all', $allowed);
    } elseif (isset($paymentConfig['allowed_gateways']) && is_array($paymentConfig['allowed_gateways'])) {
        $allowed = $paymentConfig['allowed_gateways'];
        $showMidtrans = in_array('midtrans', $allowed) || in_array('all', $allowed);
        $showMoota = in_array('moota', $allowed) || in_array('all', $allowed);
        $showCOD = in_array('cod', $allowed) || in_array('all', $allowed);
        $showManualTransfer = in_array('manual_transfer', $allowed) || in_array('all', $allowed);
    } else {
        $showMidtrans = $paymentConfig['midtrans'] ?? true;
        $showMoota = $paymentConfig['moota'] ?? false;
        $showCOD = $paymentConfig['cod'] ?? true;
        $showManualTransfer = $paymentConfig['manual_transfer'] ?? false;
    }
    if (!$showMidtrans && !$showMoota && !$showCOD && !$showManualTransfer) {
        $showMidtrans = true;
    }
    $midtransDemoMode = filter_var($paymentConfig['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
    $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');
    $categories = $categories ?? ($event->categories ?? collect());
    $gpxList = $gpxList ?? ($categories ? $categories->filter(function($cat) {
        return $cat->master_gpx_id && $cat->masterGpx;
    })->map(function($cat) {
        return (object)[
            'category_name' => $cat->name,
            'file_path' => $cat->masterGpx->gpx_path ?? $cat->masterGpx->file_path ?? '',
            'distance_km' => $cat->masterGpx->distance_km ?? null,
            'elevation_gain_m' => $cat->masterGpx->elevation_gain_m ?? null,
        ];
    })->values() : collect());
    $addons = is_array($event->addons ?? null) ? $event->addons : [];
    $isRegOpen = method_exists($event, 'isRegistrationOpen') ? $event->isRegistrationOpen() : true;
    $heroImage = $event->getHeroImageUrl() ?? asset('images/ruanglari_green.png');
    $rawGallery = is_array($event->gallery ?? null) ? $event->gallery : [];
    $galleryUrls = [];
    foreach ($rawGallery as $img) {
        $img = is_string($img) ? trim($img) : '';
        if ($img === '') {
            continue;
        }
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            $galleryUrls[] = $img;
        } else {
            $galleryUrls[] = asset('storage/'.$img);
        }
    }
    if (count($galleryUrls) === 0) {
        $galleryUrls[] = $heroImage;
    }
    $rawSponsors = is_array($event->sponsors ?? null) ? $event->sponsors : [];
    $sponsorUrls = [];
    foreach ($rawSponsors as $img) {
        $img = is_string($img) ? trim($img) : '';
        if ($img === '') {
            continue;
        }
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://')) {
            $sponsorUrls[] = $img;
        } else {
            $sponsorUrls[] = asset('storage/'.$img);
        }
    }
    $sponsorUrls = array_values(array_unique($sponsorUrls));
    $logoImage = $event->logo_image ? asset('storage/'.$event->logo_image) : null;
    $faviconImage = $event->getFaviconUrl();
    $canonicalUrl = route('events.show', $event->slug);
    $rawShortDescription = (string) ($event->short_description ?? '');
    $rawShortDescription = html_entity_decode($rawShortDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $rawShortDescription = str_replace(["\u{00A0}", "\xc2\xa0"], ' ', $rawShortDescription);
    $rawShortDescription = str_replace('&nbsp;', ' ', $rawShortDescription);
    $shortDescription = trim(preg_replace('/\s+/u', ' ', strip_tags($rawShortDescription)));
    $platformFee = (int) ($event->platform_fee ?? 0);
    $defaultJersey = collect($event->jersey_sizes ?? [])->filter()->first() ?? '';
    $formFields = $event->premium_amenities['form_fields'] ?? [];
    $showIdCard = !empty($formFields['id_card']);
    $showAddress = !empty($formFields['address']);
    $showEmergency = !empty($formFields['emergency_contact']);
    $showDob = !empty($formFields['date_of_birth']);
    $showJersey = !empty($formFields['jersey_size']);
    $showTargetTime = !empty($formFields['target_time']);
    $showBloodType = !empty($formFields['blood_type']);
    $showStrava = !empty($formFields['strava_activity']) || !empty($formFields['strava_url']) || !empty($formFields['strava_link']);
    $primaryColor = !empty($event->theme_colors['primary']) ? $event->theme_colors['primary'] : '#f1631e';
    $primaryDark = !empty($event->theme_colors['primary_dark']) ? $event->theme_colors['primary_dark'] : null;
    $isFaqEnabled = !empty($event->premium_amenities['faq']['enabled']);
    $rawFaqs = $event->premium_amenities['faq']['items'] ?? [];
    $validFaqs = is_array($rawFaqs) ? array_values(array_filter($rawFaqs, function($f) {
        return !empty(trim($f['question'] ?? '')) && !empty(trim($f['answer'] ?? ''));
    })) : [];
    $schemaDescription = $shortDescription !== '' ? $shortDescription : trim(strip_tags((string) ($event->full_description ?? '')));
    if ($schemaDescription === '') {
        $schemaDescription = $event->name;
    }
    $metaTitle = $event->name.(($event->location_name ?? '') !== '' ? ' - '.$event->location_name : '');
    $metaDescription = \Illuminate\Support\Str::limit($schemaDescription, 155, '');
    $metaImage = $galleryUrls[0] ?? $heroImage;
    $schemaEvent = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $metaTitle,
        'description' => $schemaDescription,
        'image' => [$metaImage],
        'url' => $canonicalUrl,
        'startDate' => optional($event->start_at)->toIso8601String(),
        'endDate' => optional($event->end_at ?? null)->toIso8601String(),
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => 'https://schema.org/EventScheduled',
        'location' => [
            '@type' => 'Place',
            'name' => $event->location_name,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $event->location_address,
                'addressCountry' => 'ID',
            ],
        ],
        'organizer' => [
            '@type' => 'Organization',
            'name' => config('app.name'),
            'url' => url('/'),
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth overflow-x-hidden max-w-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ $metaTitle }} - Ruang Lari</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="index,follow,max-image-preview:large">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ $faviconImage }}">
    <link rel="apple-touch-icon" href="{{ $faviconImage }}">
    <meta itemprop="name" content="{{ $metaTitle }}">
    <meta itemprop="description" content="{{ $schemaDescription }}">
    <meta itemprop="image" content="{{ $metaImage }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="event">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:image:secure_url" content="{{ $metaImage }}">
    <meta property="og:image:alt" content="{{ $metaTitle }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
    <meta name="twitter:image:alt" content="{{ $metaTitle }}">
    <script type="application/ld+json">{!! json_encode($schemaEvent, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <!-- DNS Preconnect & Preload for High Speed Rendering -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preload" as="image" href="{{ $heroImage }}" fetchpriority="high">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Non-blocking Third-Party Integration Scripts -->
    @if($showMidtrans && $midtransClientKey)
        <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}" defer></script>
    @endif
    @if(env('RECAPTCHA_SITE_KEY_v3'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}" async defer onerror="this.onerror=null;this.src='https://www.recaptcha.net/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}';"></script>
    @endif
    <style>
        :root { 
            --primary: {{ $primaryColor }}; 
            --primary-dark: {{ $primaryDark ?? 'color-mix(in srgb, '.$primaryColor.' 82%, black)' }}; 
            --primary-light: color-mix(in srgb, {{ $primaryColor }} 8%, white);
            --primary-ring: color-mix(in srgb, {{ $primaryColor }} 22%, transparent);
            --ink: #0f172a; 
            --muted: #64748b; 
            --line: #e2e8f0; 
            --bg: #f8fafc; 
        }
        .bg-theme-primary { background-color: var(--primary) !important; }
        .bg-theme-primary-dark { background-color: var(--primary-dark) !important; }
        .hover-bg-theme-primary-dark:hover { background-color: var(--primary-dark) !important; }
        .text-theme-primary { color: var(--primary) !important; }
        .border-theme-primary { border-color: var(--primary) !important; }
        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden !important;
            margin: 0;
            padding: 0;
        }
        body { 
            background: #f8fafc;
            color: #0f172a; 
            font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif; 
        }
        .hero-section {
            background: #0f172a;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .hero-overlay { 
            background: rgba(15, 23, 42, 0.65);
        }
        .glass-header { 
            background: rgba(15, 23, 42, 0.85); 
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(8px);
        }
        .pro-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 20px -5px rgba(15, 23, 42, 0.05);
            max-width: 100%;
            overflow: hidden;
        }
        .no-scrollbar { scrollbar-width: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .field { 
            width: 100%; 
            border: 1.5px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 12px 16px; 
            font-weight: 600; 
            font-size: 0.9rem;
            color: #0f172a; 
            background: #ffffff; 
            outline: none; 
            transition: border-color 0.2s ease, box-shadow 0.2s ease; 
        }
        .field:focus { 
            border-color: var(--primary); 
            box-shadow: 0 0 0 3px var(--primary-ring); 
        }
        .field::placeholder { color: #94a3b8; font-weight: 400; }
        .pill-badge { 
            display: inline-flex; 
            align-items: center; 
            gap: 0.4rem; 
            border-radius: 9999px; 
            padding: 0.4rem 0.85rem; 
            font-size: 0.7rem; 
            font-weight: 700; 
            letter-spacing: 0.05em; 
            text-transform: uppercase; 
            max-width: 100%;
        }
        .choice input { position: absolute; opacity: 0; pointer-events: none; }
        .choice-box { 
            border: 1.5px solid #e2e8f0; 
            border-radius: 12px; 
            padding: 14px 16px; 
            background: #ffffff; 
            transition: all 0.2s ease; 
        }
        .choice input:checked + .choice-box { 
            border-color: var(--primary); 
            background: var(--primary-light); 
            box-shadow: 0 0 0 3px var(--primary-ring); 
        }
        .submit-btn[disabled] { opacity: 0.65; cursor: not-allowed; }
        .content-html p { margin-top: 0; margin-bottom: 1rem; line-height: 1.75; color: #334155; font-size: 0.925rem; }
        .content-html h1, .content-html h2, .content-html h3, .content-html h4 { color: #0f172a; font-weight: 800; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .content-html ul, .content-html ol { padding-left: 1.25rem; color: #334155; margin-bottom: 1rem; }
        .content-html table { display: block; width: 100%; overflow-x: auto; }
        .content-html img, .content-html iframe { max-width: 100%; height: auto; }
        .ql-gallery-dot { width: 8px; height: 8px; border-radius: 999px; background: rgba(15,23,42,0.2); transition: transform 0.2s ease, background 0.2s ease; }
        .ql-gallery-dot[data-active="1"] { background: var(--primary); transform: scale(1.25); }
        #qlSponsorDots .ql-gallery-dot { background: rgba(15,23,42,0.2); }
        #qlSponsorDots .ql-gallery-dot[data-active="1"] { background: var(--primary); }
        @if(env('RECAPTCHA_SITE_KEY_v3'))
        .grecaptcha-badge { visibility: hidden !important; }
        @endif
    </style>
</head>
<body class="overflow-x-hidden w-full max-w-full bg-slate-50 text-slate-900 antialiased">
    <!-- Top Hero Header Section with 65% Dark Overlay -->
    <header class="relative hero-section w-full max-w-full min-h-[480px] lg:min-h-[540px] flex flex-col justify-between overflow-hidden">
        <!-- Background Image with 65% Dark Overlay -->
        <div class="absolute inset-0 z-0">
            <img src="{{ $heroImage }}" alt="{{ $event->name }}" class="w-full h-full object-cover" fetchpriority="high" decoding="async">
            <div class="absolute inset-0 hero-overlay"></div>
        </div>

        <!-- Top Navigation Bar -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-3.5 sm:px-6 md:px-8 pt-4 sm:pt-6">
            <div class="flex items-center justify-between gap-2 sm:gap-4 p-2.5 sm:p-3.5 px-3.5 sm:px-5 rounded-2xl glass-header shadow-sm min-w-0">
                <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                    <a href="{{ route('events.index') }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-slate-800 hover:bg-slate-700 text-white flex items-center justify-center transition-colors shrink-0">
                        <i class="fa-solid fa-arrow-left text-xs text-white"></i>
                    </a>
                    <span class="pill-badge bg-slate-900/80 text-white border border-slate-700 text-[10px] sm:text-xs min-w-0 truncate">
                        <i class="fa-solid fa-bolt text-white shrink-0"></i>
                        <span class="truncate">Form Registrasi {{ $event->name }}</span>
                    </span>
                </div>
                @if($event->logo_image)
                    <img src="{{ asset('storage/'.$event->logo_image) }}" alt="{{ $event->name }}" class="h-8 sm:h-11 max-w-[85px] sm:max-w-none object-contain rounded-xl bg-white p-1 shadow-sm shrink-0" loading="lazy" decoding="async">
                @else
                    <a href="{{ url('/') }}" class="text-white font-black italic tracking-tighter text-xs sm:text-base shrink-0">
                        RUANG<span class="text-[#ccff00]">LARI</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Main Hero Title & Quick Info Grid -->
        <div class="relative z-10 w-full max-w-7xl mx-auto px-3.5 sm:px-6 md:px-8 py-8 sm:py-12">
            <div class="max-w-3xl">
                <!-- Status Badges Row -->
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="pill-badge bg-theme-primary text-white shadow-sm text-[10px] sm:text-xs">
                        <i class="fa-solid fa-person-running text-white"></i>
                        <span>{{ strtoupper($event->event_type ?? 'Running Event') }}</span>
                    </span>
                    @if($isRegOpen)
                        <span class="pill-badge bg-emerald-700 text-white border border-emerald-600 text-[10px] sm:text-xs">
                            <i class="fa-solid fa-circle-check text-white"></i>
                            <span>Pendaftaran Dibuka</span>
                        </span>
                    @else
                        <span class="pill-badge bg-rose-700 text-white border border-rose-600 text-[10px] sm:text-xs">
                            <i class="fa-solid fa-lock text-white"></i>
                            <span>Pendaftaran Ditutup</span>
                        </span>
                    @endif
                </div>

                <!-- Event Main Title -->
                <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight sm:leading-tight drop-shadow-md">
                    {{ $event->name }}
                </h1>

                <!-- Event Quick Info Line -->
                <div class="mt-4 flex flex-wrap items-center gap-y-2 gap-x-4 sm:gap-x-6 text-xs sm:text-sm text-slate-200 font-medium">
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-calendar text-white"></i>
                        <span>{{ optional($event->start_at)->format('d F Y') ?: 'Tanggal TBA' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fa-regular fa-clock text-white"></i>
                        <span>{{ optional($event->start_at)->format('H:i') ?: '06:00' }} WIB</span>
                    </div>
                    @if($event->location_name)
                        <div class="flex items-center gap-2 truncate max-w-full">
                            <i class="fa-solid fa-location-dot text-white shrink-0"></i>
                            <span class="truncate">{{ $event->location_name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Summary Bar at Hero Bottom -->
        <div class="relative z-10 w-full bg-slate-950/70 border-t border-white/10 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-3.5 sm:px-6 md:px-8 py-3.5 flex flex-wrap items-center justify-between gap-3 text-xs sm:text-sm text-white">
                <div class="flex items-center gap-4 flex-wrap">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase block font-bold">Kategori</span>
                        <span class="font-bold">{{ $categories->pluck('name')->join(', ') ?: '-' }}</span>
                    </div>
                    <div class="hidden sm:block h-6 w-px bg-white/20"></div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase block font-bold">Lokasi</span>
                        <span class="font-bold truncate max-w-[200px] block">{{ $event->location_name ?: '-' }}</span>
                    </div>
                </div>
                <a href="#register" class="inline-flex items-center gap-2 rounded-xl bg-theme-primary hover-bg-theme-primary-dark px-4 py-2 text-xs font-bold text-white transition-colors shadow-sm">
                    <i class="fa-solid fa-arrow-down text-white"></i> Daftar Sekarang
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-3.5 sm:px-6 md:px-8 py-6 sm:py-10 overflow-hidden">
        
        <!-- Alerts and Notices -->
        @if(session('success') || request('payment') === 'success')
            <div id="payment-notification-badge" class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 p-4 sm:p-5 flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-md bg-emerald-700 text-white flex items-center justify-center text-sm shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-check text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-950">Pendaftaran Berhasil</h3>
                    <p class="text-xs text-emerald-800 mt-0.5">{{ session('success') ?? 'Terima kasih telah mendaftar. Silakan cek email Anda (inbox/spam) untuk melihat E-Ticket dan detail acara.' }}</p>
                </div>
            </div>
        @endif

        @if(session('payment') === 'pending' || request('payment') === 'pending')
            <div id="payment-notification-badge" class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 sm:p-5 flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-md bg-amber-700 text-white flex items-center justify-center text-sm shrink-0 mt-0.5">
                    <i class="fa-solid fa-clock text-white"></i>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-amber-950">Menunggu Pembayaran</h3>
                    <p class="text-xs text-amber-800 mt-0.5">Transaksi kamu telah dicatat. Jika popup pembayaran sebelumnya tertutup atau belum diselesaikan, kamu dapat melanjutkannya sekarang tanpa perlu mengisi data ulang.</p>
                    <div class="mt-3">
                        <a href="{{ route('events.payments.continue', $event->slug) }}" class="inline-flex items-center gap-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-3.5 py-2 rounded-md transition shadow-sm">
                            <span>Lanjutkan Pembayaran</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if(session('payment') === 'approval_pending' || request('payment') === 'approval_pending')
            <div id="payment-notification-badge" class="mb-6 rounded-lg bg-amber-50 border border-amber-300 p-4 sm:p-5 flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-md bg-amber-700 text-white flex items-center justify-center text-sm shrink-0 mt-0.5">
                    <i class="fa-solid fa-hourglass-half text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-950">Pendaftaran Berhasil Dikirim (Menunggu Persetujuan)</h3>
                    <p class="text-xs text-amber-800 mt-0.5">Data pendaftaran kamu telah tersimpan dan sedang diverifikasi oleh panitia untuk seleksi kuota tantangan (challenge). Setelah disetujui, e-Tiket resmi dan nomor BIB akan dikirim ke email kamu.</p>
                </div>
            </div>
        @endif

        @if(session('payment') === 'cod_pending' || request('payment') === 'cod_pending')
            <div id="payment-notification-badge" class="mb-6 rounded-lg bg-amber-50 border border-amber-300 p-4 sm:p-5 flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-md bg-amber-700 text-white flex items-center justify-center text-sm shrink-0 mt-0.5">
                    <i class="fa-solid fa-hourglass-half text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-amber-950">Pendaftaran COD Menunggu Persetujuan (Approval)</h3>
                    <p class="text-xs text-amber-800 mt-0.5">Data pendaftaran dan foto Anda telah tersimpan dan sedang diverifikasi oleh panitia EO. Setelah disetujui, e-Tiket resmi akan dikirim ke email Anda.</p>
                </div>
            </div>
        @endif

        @if(session('error') || request('payment') === 'failed')
            <div id="payment-notification-badge" class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 sm:p-5 flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 rounded-md bg-rose-700 text-white flex items-center justify-center text-sm shrink-0 mt-0.5">
                    <i class="fa-solid fa-circle-xmark text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-rose-950">Pembayaran Gagal</h3>
                    <p class="text-xs text-rose-800 mt-0.5">{{ session('error') ?? 'Pembayaran belum berhasil diselesaikan. Silakan coba kembali atau gunakan metode pembayaran lain.' }}</p>
                </div>
            </div>
        @endif

        <!-- Layout Grid: 2 Columns on Desktop -->
        <div class="grid lg:grid-cols-12 gap-6 lg:gap-8 items-start max-w-full">
            
            <!-- LEFT COLUMN: Event Information & Details -->
            <section class="lg:col-span-7 space-y-6 max-w-full overflow-hidden">
                
                <!-- Quick Info Cards Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div class="pro-card p-4">
                        <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs mb-2">
                            <i class="fa-regular fa-calendar text-white"></i>
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Tanggal</div>
                        <div class="mt-0.5 text-xs sm:text-sm font-bold text-slate-900">{{ optional($event->start_at)->format('d M Y') ?: '-' }}</div>
                    </div>
                    <div class="pro-card p-4">
                        <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs mb-2">
                            <i class="fa-regular fa-clock text-white"></i>
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Waktu Flag Off</div>
                        <div class="mt-0.5 text-xs sm:text-sm font-bold text-slate-900">{{ optional($event->start_at)->format('H:i') ?: '-' }} WIB</div>
                    </div>
                    <div class="pro-card p-4 col-span-2 sm:col-span-1">
                        <div class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs mb-2">
                            <i class="fa-solid fa-location-dot text-white"></i>
                        </div>
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Lokasi</div>
                        <div class="mt-0.5 text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $event->location_name ?: '-' }}</div>
                    </div>
                </div>

                <!-- Event Gallery Slider -->
                <div class="pro-card p-4 sm:p-5 max-w-full overflow-hidden">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-md bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                            <i class="fa-solid fa-images text-white"></i>
                        </span>
                        Galeri Event
                    </div>
                    <div id="qlGallery" data-images='@json($galleryUrls)' class="relative w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-900/5 min-h-[200px] flex items-center justify-center group">
                        <img id="qlGalleryImg" src="{{ $galleryUrls[0] }}" alt="Event Gallery" class="w-full h-auto max-h-[550px] sm:max-h-[650px] object-contain mx-auto block rounded-xl cursor-zoom-in transition-opacity duration-300" loading="lazy" decoding="async" title="Klik untuk membuka lightbox">
                        
                        <!-- Zoom Hint Badge -->
                        <div class="absolute top-3 right-3 pointer-events-none z-10 flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-900/80 text-white text-[11px] font-bold backdrop-blur-sm shadow-sm transition-opacity group-hover:bg-slate-900">
                            <i class="fa-solid fa-magnifying-glass-plus text-white text-xs"></i>
                            <span class="hidden sm:inline">Perbesar</span>
                        </div>

                        <button id="qlGalleryPrev" type="button" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-900/80 hover:bg-slate-900 text-white flex items-center justify-center text-xs transition-colors shadow z-10" aria-label="Foto sebelumnya">
                            <i class="fa-solid fa-chevron-left text-white"></i>
                        </button>
                        <button id="qlGalleryNext" type="button" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-slate-900/80 hover:bg-slate-900 text-white flex items-center justify-center text-xs transition-colors shadow z-10" aria-label="Foto selanjutnya">
                            <i class="fa-solid fa-chevron-right text-white"></i>
                        </button>
                        <div id="qlGalleryDots" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-950/80 border border-white/20 z-10"></div>
                    </div>
                </div>

                <!-- Full Description -->
                @if($event->full_description)
                    <div class="pro-card p-5 sm:p-6 max-w-full overflow-hidden">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-3">
                            <span class="w-6 h-6 rounded-md bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                                <i class="fa-solid fa-align-left text-white"></i>
                            </span>
                            Description
                        </div>
                        <div class="content-html text-slate-700 overflow-x-auto max-w-full">
                            {!! $event->full_description !!}
                        </div>
                    </div>
                @elseif($event->short_description)
                    <div class="pro-card p-5 sm:p-6 max-w-full overflow-hidden">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 rounded-md bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                                <i class="fa-solid fa-align-left text-white"></i>
                            </span>
                            Description
                        </div>
                        <p class="text-slate-700 leading-relaxed text-sm">{{ $event->short_description }}</p>
                    </div>
                @endif

                <!-- Route Map Section -->
                <div class="pro-card p-5 sm:p-6 max-w-full overflow-hidden">
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-md bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                            <i class="fa-solid fa-map-location-dot text-white"></i>
                        </span>
                        Rute Lari
                    </div>
                    <div class="mt-3 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Peta Rute & Elevasi</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Lihat jalur rute lari, check point, dan detail elevasi event ini.</p>
                        </div>
                        <button type="button" onclick="openRouteModal()" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800 transition-colors whitespace-nowrap justify-center">
                            <i class="fa-solid fa-map-marked-alt text-white"></i> Lihat Peta Rute
                        </button>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                @if($event->terms_and_conditions)
                    <div class="pro-card p-5 sm:p-6 max-w-full overflow-hidden">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-md bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                                <i class="fa-solid fa-file-shield text-white"></i>
                            </span>
                            Syarat & Ketentuan
                        </div>
                        <div class="mt-3 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Peraturan & Ketentuan</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Baca syarat, ketentuan, serta syarat keselamatan peserta.</p>
                            </div>
                            <button type="button" onclick="document.getElementById('termsModal').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800 transition-colors whitespace-nowrap justify-center">
                                <i class="fa-solid fa-shield-halved text-white"></i> Baca Ketentuan
                            </button>
                        </div>
                    </div>
                @endif

                <!-- FAQ Section -->
                @if($isFaqEnabled && count($validFaqs) > 0)
                    <div class="pro-card p-5 sm:p-6 max-w-full overflow-hidden" id="faq-section">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-3">
                            <span class="w-6 h-6 rounded-md bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                                <i class="fa-solid fa-circle-question text-white"></i>
                            </span>
                            FAQ (Pertanyaan Umum)
                        </div>
                        <div class="space-y-2.5">
                            @foreach($validFaqs as $idx => $faq)
                                <div class="rounded-xl border border-slate-200 bg-white overflow-hidden transition-colors">
                                    <button type="button" class="w-full px-4 py-3.5 text-left font-bold text-slate-900 flex justify-between items-center gap-3 text-sm focus:outline-none hover:bg-slate-50 transition-colors" onclick="const body = this.nextElementSibling; const icon = this.querySelector('.faq-icon'); body.classList.toggle('hidden'); icon.classList.toggle('rotate-180');">
                                        <span class="leading-snug">{{ $faq['question'] }}</span>
                                        <span class="w-6 h-6 rounded-full bg-slate-900 flex items-center justify-center text-white shrink-0 faq-icon transition-transform duration-200">
                                            <i class="fa-solid fa-chevron-down text-[10px] text-white"></i>
                                        </span>
                                    </button>
                                    <div class="px-4 pb-4 pt-2 text-xs text-slate-600 leading-relaxed border-t border-slate-100 hidden">
                                        {!! nl2br(e($faq['answer'])) !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                
            </section>

            <!-- RIGHT COLUMN: Sticky Registration Card -->
            <aside id="register" class="lg:col-span-5 lg:sticky lg:top-24 self-start w-full max-w-full">
                <div class="pro-card overflow-hidden">
                    <!-- Card Header -->
                    <div class="px-5 sm:px-6 py-4 bg-slate-900 text-white">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Quick Registration</div>
                                <h3 class="mt-0.5 text-base sm:text-lg font-bold">Formulir Pendaftaran</h3>
                            </div>
                            <div class="w-8 h-8 rounded-lg bg-slate-800 flex items-center justify-center text-sm shrink-0 text-white">
                                <i class="fa-solid fa-bolt text-white"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Card Body / Form -->
                    <div class="p-4 sm:p-6">
                        @if(!$isRegOpen)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center">
                                <div class="w-12 h-12 mx-auto rounded-full bg-slate-900 text-white flex items-center justify-center text-lg">
                                    <i class="fa-solid fa-lock text-white"></i>
                                </div>
                                <div class="mt-3 text-base font-bold text-slate-900">Pendaftaran Ditutup</div>
                                <div class="mt-1 text-xs text-slate-600">Saat ini pendaftaran belum dibuka untuk event ini.</div>
                            </div>
                        @else
                            <form id="quickForm" action="{{ route('events.register.store', ['slug' => $event->slug]) }}" method="POST" class="space-y-4 max-w-full">
                                @csrf
                                
                                <!-- Participant Header Actions -->
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">Data Peserta</div>
                                        <div class="text-[11px] text-slate-500">Bisa daftar beberapa peserta sekaligus.</div>
                                    </div>
                                    <button id="addParticipantBtn" type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition-colors shrink-0">
                                        <i class="fa-solid fa-plus text-white"></i> Tambah Peserta
                                    </button>
                                </div>

                                <div id="participantsWrapper" class="space-y-4 max-w-full">
                                    <div class="participant-card rounded-xl border border-slate-200 bg-slate-50 p-3.5 sm:p-4 max-w-full" data-participant-item>
                                        <div class="flex items-center justify-between gap-4 pb-2.5 border-b border-slate-200">
                                            <div>
                                                <div class="participant-title text-xs font-bold text-slate-900">Peserta 1</div>
                                                <div class="text-[11px] text-slate-500">Isi data ringkas peserta.</div>
                                            </div>
                                            <button type="button" class="remove-participant hidden rounded-lg bg-rose-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-rose-700 transition-colors">
                                                Hapus
                                            </button>
                                        </div>

                                        <div class="mt-3.5 space-y-3.5">
                                            <div>
                                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Nama Lengkap <span class="text-orange-600">*</span></label>
                                                <input class="field mt-1" data-field="name" name="participants[0][name]" placeholder="Nama peserta" required>
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Gender <span class="text-orange-600">*</span></label>
                                                    <select class="field mt-1" data-field="gender" name="participants[0][gender]" required>
                                                        <option value="">Pilih gender</option>
                                                        <option value="male">Laki-laki</option>
                                                        <option value="female">Perempuan</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">No. WhatsApp <span class="text-orange-600">*</span></label>
                                                    <input class="field mt-1" data-field="phone" name="participants[0][phone]" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Email Active <span class="text-orange-600">*</span></label>
                                                <input class="field mt-1" type="email" data-field="email" name="participants[0][email]" placeholder="email@contoh.com" required>
                                            </div>

                                            <!-- Categories -->
                                            @if($categories && $categories->count() > 0)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Kategori Lomba <span class="text-orange-600">*</span></label>
                                                    <div class="mt-2 grid gap-2">
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
                                                            <label class="choice relative block cursor-pointer">
                                                                <input type="radio" data-field="category_id" name="participants[0][category_id]" value="{{ $cat->id }}" data-price="{{ $displayPrice }}" {{ $loop->first ? 'checked' : '' }} required>
                                                                <div class="choice-box flex items-center justify-between gap-3">
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-900 truncate">{{ $cat->name }}</div>
                                                                        @if($cat->distance_km)
                                                                            <div class="mt-0.5 text-[10px] font-bold text-slate-500">{{ number_format($cat->distance_km, 0, ',', '.') }}K</div>
                                                                        @endif
                                                                    </div>
                                                                    @if($displayPrice > 0)
                                                                        <div class="text-right shrink-0">
                                                                            @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                                <div class="text-[10px] text-slate-400 line-through">Rp {{ number_format($priceRegular, 0, ',', '.') }}</div>
                                                                            @endif
                                                                            <div class="text-xs font-bold text-theme-primary">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Addons -->
                                            @if(!empty($addons))
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Add-on Opsional</label>
                                                    <div class="mt-2 grid gap-2">
                                                        @foreach($addons as $idx => $addon)
                                                            <label class="choice relative block cursor-pointer">
                                                                <input type="checkbox" data-addon-index="{{ $idx }}" name="participants[0][addons][{{ $idx }}][selected]" value="1" data-addon-price="{{ (int) ($addon['price'] ?? 0) }}">
                                                                <div class="choice-box flex items-center justify-between gap-3">
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-900 truncate">{{ $addon['name'] }}</div>
                                                                        <div class="mt-0.5 text-[10px] text-slate-500">Opsional</div>
                                                                    </div>
                                                                    <div class="text-xs font-bold text-slate-900 shrink-0">
                                                                        @if((int) ($addon['price'] ?? 0) > 0)
                                                                            +Rp {{ number_format((int) ($addon['price'] ?? 0), 0, ',', '.') }}
                                                                        @else
                                                                            Gratis
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" data-addon-name="{{ $idx }}" name="participants[0][addons][{{ $idx }}][name]" value="{{ $addon['name'] }}">
                                                                <input type="hidden" data-addon-price-hidden="{{ $idx }}" name="participants[0][addons][{{ $idx }}][price]" value="{{ (int) ($addon['price'] ?? 0) }}">
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($showIdCard)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">No. Identitas (KTP/SIM)</label>
                                                    <input class="field mt-1" data-field="id_card" name="participants[0][id_card]" placeholder="Nomor KTP/SIM" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="id_card" name="participants[0][id_card]">
                                            @endif

                                            @if($showAddress)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Alamat Lengkap</label>
                                                    <textarea class="field mt-1" data-field="address" name="participants[0][address]" placeholder="Alamat lengkap" rows="2" required></textarea>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="address" name="participants[0][address]">
                                            @endif

                                            @if($showEmergency)
                                                <div class="grid sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Kontak Darurat</label>
                                                        <input class="field mt-1" data-field="emergency_contact_name" name="participants[0][emergency_contact_name]" placeholder="Nama kontak" required>
                                                    </div>
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">No. HP Darurat</label>
                                                        <input class="field mt-1" data-field="emergency_contact_number" name="participants[0][emergency_contact_number]" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="emergency_contact_name" name="participants[0][emergency_contact_name]">
                                                <input type="hidden" data-hidden-auto="emergency_contact_number" name="participants[0][emergency_contact_number]">
                                            @endif

                                            @if($showDob)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Tanggal Lahir</label>
                                                    <input type="date" class="field mt-1" data-field="date_of_birth" name="participants[0][date_of_birth]" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="date_of_birth" name="participants[0][date_of_birth]" value="">
                                            @endif

                                            @if($showJersey)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Ukuran Jersey</label>
                                                    <select class="field mt-1" data-field="jersey_size" name="participants[0][jersey_size]" required>
                                                        <option value="">Pilih ukuran jersey</option>
                                                        @foreach($event->jersey_sizes ?? [] as $sz)
                                                            <option value="{{ $sz }}">{{ $sz }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="jersey_size" name="participants[0][jersey_size]" value="">
                                            @endif

                                            @if($showTargetTime)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Personal Best</label>
                                                    <div class="grid grid-cols-3 gap-2 mt-1">
                                                        <div>
                                                            <select class="field" data-target-hour required>
                                                                @for ($i = 0; $i <= 23; $i++)
                                                                    <option value="{{ sprintf('%02d', $i) }}" {{ $i == 0 ? 'selected' : '' }}>{{ sprintf('%02d', $i) }} Jam</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <select class="field" data-target-minute required>
                                                                @for ($i = 0; $i <= 59; $i++)
                                                                    <option value="{{ sprintf('%02d', $i) }}" {{ $i == 30 ? 'selected' : '' }}>{{ sprintf('%02d', $i) }} Menit</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <select class="field" data-target-second required>
                                                                @for ($i = 0; $i <= 59; $i++)
                                                                    <option value="{{ sprintf('%02d', $i) }}" {{ $i == 0 ? 'selected' : '' }}>{{ sprintf('%02d', $i) }} Detik</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" data-field="target_time" name="participants[0][target_time]" value="00:30:00">
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="target_time" name="participants[0][target_time]" value="">
                                            @endif

                                            @if($showBloodType)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Golongan Darah</label>
                                                    <select class="field mt-1" data-field="blood_type" name="participants[0][blood_type]" required>
                                                        <option value="">Pilih golongan darah</option>
                                                        <option value="A">A</option>
                                                        <option value="B">B</option>
                                                        <option value="AB">AB</option>
                                                        <option value="O">O</option>
                                                    </select>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="blood_type" name="participants[0][blood_type]" value="">
                                            @endif

                                            @if($showStrava)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Link Aktivitas Strava <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                                                    <input class="field mt-1" type="url" data-field="strava_url" name="participants[0][strava_url]" placeholder="https://www.strava.com/activities/...">
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="strava_url" name="participants[0][strava_url]" value="">
                                            @endif

                                            <!-- Photo Upload Box for COD / Biometrics -->
                                            <div class="photo-upload-box p-3.5 rounded-xl border border-slate-200 bg-white transition-colors" data-photo-container>
                                                <div class="flex items-center justify-between gap-2 mb-2">
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                                            <span>Foto Wajah / Identitas</span>
                                                            <span class="photo-badge-required text-orange-600 font-bold hidden">(Wajib untuk COD) *</span>
                                                        </label>
                                                        <div class="text-[10px] text-slate-500 mt-0.5">Upload file foto atau langsung jepret dengan kamera.</div>
                                                    </div>
                                                    <span class="photo-badge-optional text-[10px] font-bold text-slate-400 uppercase">Opsional</span>
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    <div class="w-14 h-14 rounded-xl border border-slate-300 bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 photo-preview-box">
                                                        <img class="photo-preview-img hidden w-full h-full object-cover" alt="Preview Foto">
                                                        <i class="fa-solid fa-camera text-slate-400 text-lg photo-placeholder-icon"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex items-center gap-2 flex-wrap">
                                                        <!-- 1. Upload File Button -->
                                                        <label class="px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs cursor-pointer transition flex items-center gap-1.5 shrink-0">
                                                            <i class="fa-solid fa-upload text-white text-xs"></i>
                                                            <span>Upload Foto</span>
                                                            <input type="file" accept="image/*" class="hidden photo-file-input">
                                                        </label>
                                                        
                                                        <!-- 2. Direct Camera Capture Button -->
                                                        <button type="button" class="photo-camera-btn px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 font-bold text-xs transition flex items-center gap-1.5 shrink-0">
                                                            <i class="fa-solid fa-camera text-slate-700 text-xs"></i>
                                                            <span>Langsung Foto</span>
                                                        </button>

                                                        <!-- 3. Clear Button -->
                                                        <button type="button" class="photo-clear-btn hidden px-2.5 py-2 rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition flex items-center gap-1">
                                                            <i class="fa-solid fa-trash text-rose-600 text-xs"></i>
                                                            <span>Hapus</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" data-field="photo" name="participants[0][photo]" value="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Multi Participant Template -->
                                <template id="participantTemplate">
                                    <div class="participant-card rounded-xl border border-slate-200 bg-slate-50 p-3.5 sm:p-4 max-w-full" data-participant-item>
                                        <div class="flex items-center justify-between gap-4 pb-2.5 border-b border-slate-200">
                                            <div>
                                                <div class="participant-title text-xs font-bold text-slate-900">Peserta</div>
                                                <div class="text-[11px] text-slate-500">Isi data ringkas peserta.</div>
                                            </div>
                                            <button type="button" class="remove-participant rounded-lg bg-rose-600 px-2.5 py-1 text-[11px] font-bold text-white hover:bg-rose-700 transition-colors">
                                                Hapus
                                            </button>
                                        </div>

                                        <div class="mt-3.5 space-y-3.5">
                                            <div>
                                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Nama Lengkap</label>
                                                <input class="field mt-1" data-field="name" placeholder="Nama peserta" required>
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Gender</label>
                                                    <select class="field mt-1" data-field="gender" required>
                                                        <option value="">Pilih gender</option>
                                                        <option value="male">Laki-laki</option>
                                                        <option value="female">Perempuan</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">No. WhatsApp</label>
                                                    <input class="field mt-1" data-field="phone" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Email Active</label>
                                                <input class="field mt-1" type="email" data-field="email" placeholder="email@contoh.com" required>
                                            </div>

                                            @if($categories && $categories->count() > 0)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Kategori Lomba</label>
                                                    <div class="mt-2 grid gap-2">
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
                                                            <label class="choice relative block cursor-pointer">
                                                                <input type="radio" data-field="category_id" value="{{ $cat->id }}" data-price="{{ $displayPrice }}" {{ $loop->first ? 'checked' : '' }} required>
                                                                <div class="choice-box flex items-center justify-between gap-3">
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-900 truncate">{{ $cat->name }}</div>
                                                                        @if($cat->distance_km)
                                                                            <div class="mt-0.5 text-[10px] font-bold text-slate-500">{{ number_format($cat->distance_km, 0, ',', '.') }}K</div>
                                                                        @endif
                                                                    </div>
                                                                    @if($displayPrice > 0)
                                                                        <div class="text-right shrink-0">
                                                                            @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                                <div class="text-[10px] text-slate-400 line-through">Rp {{ number_format($priceRegular, 0, ',', '.') }}</div>
                                                                            @endif
                                                                            <div class="text-xs font-bold text-theme-primary">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if(!empty($addons))
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Add-on</label>
                                                    <div class="mt-2 grid gap-2">
                                                        @foreach($addons as $idx => $addon)
                                                            <label class="choice relative block cursor-pointer">
                                                                <input type="checkbox" data-addon-index="{{ $idx }}" value="1" data-addon-price="{{ (int) ($addon['price'] ?? 0) }}">
                                                                <div class="choice-box flex items-center justify-between gap-3">
                                                                    <div class="min-w-0">
                                                                        <div class="text-xs font-bold text-slate-900 truncate">{{ $addon['name'] }}</div>
                                                                        <div class="mt-0.5 text-[10px] text-slate-500">Opsional</div>
                                                                    </div>
                                                                    <div class="text-xs font-bold text-slate-900 shrink-0">
                                                                        @if((int) ($addon['price'] ?? 0) > 0)
                                                                            +Rp {{ number_format((int) ($addon['price'] ?? 0), 0, ',', '.') }}
                                                                        @else
                                                                            Gratis
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <input type="hidden" data-addon-name="{{ $idx }}" value="{{ $addon['name'] }}">
                                                                <input type="hidden" data-addon-price-hidden="{{ $idx }}" value="{{ (int) ($addon['price'] ?? 0) }}">
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($showIdCard)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">No. Identitas (KTP/SIM)</label>
                                                    <input class="field mt-1" data-field="id_card" placeholder="Nomor KTP/SIM" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="id_card" value="">
                                            @endif

                                            @if($showAddress)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Alamat Lengkap</label>
                                                    <textarea class="field mt-1" data-field="address" placeholder="Alamat lengkap" rows="2" required></textarea>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="address" value="">
                                            @endif

                                            @if($showEmergency)
                                                <div class="grid sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Kontak Darurat</label>
                                                        <input class="field mt-1" data-field="emergency_contact_name" placeholder="Nama kontak darurat" required>
                                                    </div>
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">No. HP Darurat</label>
                                                        <input class="field mt-1" data-field="emergency_contact_number" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="emergency_contact_name" value="">
                                                <input type="hidden" data-hidden-auto="emergency_contact_number" value="">
                                            @endif

                                            @if($showDob)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Tanggal Lahir</label>
                                                    <input class="field mt-1" type="date" data-field="date_of_birth" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="date_of_birth" value="">
                                            @endif

                                            @if($showJersey)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Ukuran Jersey</label>
                                                    <select class="field mt-1" data-field="jersey_size" required>
                                                        <option value="">Pilih ukuran jersey</option>
                                                        @if(!empty($event->jersey_sizes) && is_array($event->jersey_sizes))
                                                            @foreach($event->jersey_sizes as $size)
                                                                <option value="{{ $size }}">{{ $size }}</option>
                                                            @endforeach
                                                        @else
                                                            <option value="S">S</option>
                                                            <option value="M">M</option>
                                                            <option value="L">L</option>
                                                            <option value="XL">XL</option>
                                                            <option value="XXL">XXL</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="jersey_size" value="">
                                            @endif

                                            @if($showTargetTime)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Target Waktu (COT)</label>
                                                    <input class="field mt-1" data-field="target_time" placeholder="Contoh: 01:45:00" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="target_time" value="">
                                            @endif

                                            @if($showBloodType)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Golongan Darah</label>
                                                    <select class="field mt-1" data-field="blood_type" required>
                                                        <option value="">Pilih golongan darah</option>
                                                        <option value="A">A</option>
                                                        <option value="B">B</option>
                                                        <option value="AB">AB</option>
                                                        <option value="O">O</option>
                                                    </select>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="blood_type" value="">
                                            @endif

                                            @if($showStrava)
                                                <div>
                                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Link Aktivitas Strava <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                                                    <input class="field mt-1" type="url" data-field="strava_url" placeholder="https://www.strava.com/activities/...">
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="strava_url" value="">
                                            @endif

                                            <!-- Photo Upload Box for COD / Biometrics (Template) -->
                                            <div class="photo-upload-box p-3.5 rounded-xl border border-slate-200 bg-white transition-colors" data-photo-container>
                                                <div class="flex items-center justify-between gap-2 mb-2">
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                                            <span>Foto Wajah / Identitas</span>
                                                            <span class="photo-badge-required text-orange-600 font-bold hidden">(Wajib untuk COD) *</span>
                                                        </label>
                                                        <div class="text-[10px] text-slate-500 mt-0.5">Upload file foto atau langsung jepret dengan kamera.</div>
                                                    </div>
                                                    <span class="photo-badge-optional text-[10px] font-bold text-slate-400 uppercase">Opsional</span>
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    <div class="w-14 h-14 rounded-xl border border-slate-300 bg-slate-100 flex items-center justify-center overflow-hidden shrink-0 photo-preview-box">
                                                        <img class="photo-preview-img hidden w-full h-full object-cover" alt="Preview Foto">
                                                        <i class="fa-solid fa-camera text-slate-400 text-lg photo-placeholder-icon"></i>
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex items-center gap-2 flex-wrap">
                                                        <label class="px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs cursor-pointer transition flex items-center gap-1.5 shrink-0">
                                                            <i class="fa-solid fa-upload text-white text-xs"></i>
                                                            <span>Upload Foto</span>
                                                            <input type="file" accept="image/*" class="hidden photo-file-input">
                                                        </label>
                                                        <button type="button" class="photo-camera-btn px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-800 font-bold text-xs transition flex items-center gap-1.5 shrink-0">
                                                            <i class="fa-solid fa-camera text-slate-700 text-xs"></i>
                                                            <span>Langsung Foto</span>
                                                        </button>
                                                        <button type="button" class="photo-clear-btn hidden px-2.5 py-2 rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-50 text-xs font-bold transition flex items-center gap-1">
                                                            <i class="fa-solid fa-trash text-rose-600 text-xs"></i>
                                                            <span>Hapus</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <input type="hidden" data-field="photo" value="">
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Payment Methods Selector -->
                                <div id="payment-methods-container">
                                    <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Metode Pembayaran</label>
                                    <div class="mt-2 grid gap-2">
                                        @if($showMidtrans)
                                            <label class="choice relative block cursor-pointer">
                                                <input type="radio" name="payment_method" value="midtrans" {{ $showMidtrans ? 'checked' : '' }} required>
                                                <div class="choice-box flex items-center justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="text-xs font-bold text-slate-900 truncate">Online Payment Gateway</div>
                                                        <div class="mt-0.5 text-[10px] text-slate-500 truncate">QRIS, GoPay, Virtual Account, Credit Card</div>
                                                    </div>
                                                    <span class="w-7 h-7 rounded-lg bg-slate-900 text-white inline-flex items-center justify-center text-xs shrink-0">
                                                        <i class="fa-solid fa-credit-card text-white"></i>
                                                    </span>
                                                </div>
                                            </label>
                                        @endif
                                        @if($showMoota)
                                            <label class="choice relative block cursor-pointer">
                                                <input type="radio" name="payment_method" value="moota" {{ !$showMidtrans && $showMoota ? 'checked' : '' }} required>
                                                <div class="choice-box flex items-center justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="text-xs font-bold text-slate-900 truncate">Transfer Bank Otomatis</div>
                                                        <div class="mt-0.5 text-[10px] text-slate-500 truncate">Verifikasi otomatis via Moota</div>
                                                    </div>
                                                    <span class="w-7 h-7 rounded-lg bg-slate-900 text-white inline-flex items-center justify-center text-xs shrink-0">
                                                        <i class="fa-solid fa-building-columns text-white"></i>
                                                    </span>
                                                </div>
                                            </label>
                                        @endif
                                        @if($showCOD)
                                            <label class="choice relative block cursor-pointer">
                                                <input type="radio" name="payment_method" value="cod" {{ !$showMidtrans && !$showMoota && $showCOD ? 'checked' : '' }} required>
                                                <div class="choice-box flex items-center justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="text-xs font-bold text-slate-900 truncate">Bayar di Tempat (COD)</div>
                                                        <div class="mt-0.5 text-[10px] text-slate-500 truncate">Wajib upload foto dan butuh approval panitia</div>
                                                    </div>
                                                    <span class="w-7 h-7 rounded-lg bg-slate-900 text-white inline-flex items-center justify-center text-xs shrink-0">
                                                        <i class="fa-solid fa-money-bill-wave text-white"></i>
                                                    </span>
                                                </div>
                                            </label>
                                        @endif
                                        @if($showManualTransfer)
                                            <label class="choice relative block cursor-pointer">
                                                <input type="radio" name="payment_method" value="manual_transfer" {{ !$showMidtrans && !$showMoota && !$showCOD && $showManualTransfer ? 'checked' : '' }} required>
                                                <div class="choice-box flex items-center justify-between gap-2">
                                                    <div class="min-w-0">
                                                        <div class="text-xs font-bold text-slate-900 truncate">Transfer Rekening EO</div>
                                                        <div class="mt-0.5 text-[10px] text-slate-500 truncate">Transfer langsung dengan kode unik & unggah bukti</div>
                                                    </div>
                                                    <span class="w-7 h-7 rounded-lg bg-slate-900 text-white inline-flex items-center justify-center text-xs shrink-0">
                                                        <i class="fa-solid fa-receipt text-white"></i>
                                                    </span>
                                                </div>
                                            </label>
                                        @endif
                                    </div>

                                    <!-- COD Required Approval Notice -->
                                    <div id="qlCodNotice" class="hidden mt-2.5 p-3.5 rounded-xl bg-amber-50 border border-amber-300 text-amber-900 text-xs leading-relaxed space-y-1">
                                        <div class="font-bold text-amber-950 flex items-center gap-1.5 text-[11px] uppercase tracking-wider">
                                            <i class="fa-solid fa-circle-info text-amber-700"></i>
                                            <span>Ketentuan Bayar di Tempat (COD)</span>
                                        </div>
                                        <div class="text-[11px] text-amber-900">
                                            1. Anda <strong>wajib mengunggah/mengambil foto wajah/identitas</strong> pada formulir peserta di atas.
                                        </div>
                                        <div class="text-[11px] text-amber-900">
                                            2. Pendaftaran COD akan masuk ke status <strong>Menunggu Persetujuan (Approval) Panitia EO</strong> sebelum nomor BIB dan nama resmi masuk ke daftar peserta.
                                        </div>
                                    </div>
                                </div>

                                <!-- Coupon Input Box -->
                                <div id="qlCouponContainer" class="rounded-xl border border-slate-200 bg-white p-3.5">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-xs font-bold uppercase tracking-wider text-slate-700">Kode Kupon / Promo</div>
                                            <div class="text-[11px] text-slate-500 mt-0.5">Punya kode voucher diskon?</div>
                                        </div>
                                        <span class="text-[10px] font-bold text-slate-400 uppercase">Opsional</span>
                                    </div>
                                    <div class="mt-2.5 flex gap-2">
                                        <input id="qlCouponInput" type="text" class="field text-xs uppercase min-w-0" placeholder="Contoh: PROMO2026" autocomplete="off" />
                                        <button id="qlCouponApplyBtn" type="button" class="px-3.5 py-2 rounded-lg bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition-colors shrink-0">Apply</button>
                                        <button id="qlCouponClearBtn" type="button" class="hidden px-3.5 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition-colors shrink-0">Reset</button>
                                    </div>
                                    <input type="hidden" name="coupon_code" id="qlCouponCodeHidden" />
                                    <div id="qlCouponMessage" class="hidden mt-2 text-xs font-bold"></div>
                                </div>

                                <!-- Price Breakdown Summary -->
                                <div id="qlPriceBreakdownContainer" class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-2 text-xs">
                                    <div id="qlTicketRow" class="flex items-center justify-between text-slate-600">
                                        <span>Subtotal Tiket</span>
                                        <span id="ql-ticket-price" class="font-bold text-slate-900">Rp 0</span>
                                    </div>
                                    <div id="qlAddonRow" class="flex items-center justify-between text-slate-600">
                                        <span>Add-on</span>
                                        <span id="ql-addon-price" class="font-bold text-slate-900">Rp 0</span>
                                    </div>
                                    <div id="qlCouponRow" class="hidden flex items-center justify-between text-slate-600">
                                        <span>Diskon Kupon <span id="qlCouponLabel" class="font-bold text-slate-700"></span></span>
                                        <span id="qlDiscountAmount" class="font-bold text-emerald-600">-Rp 0</span>
                                    </div>
                                    <div id="qlFeeRow" class="flex items-center justify-between text-slate-600">
                                        <span>Biaya Layanan (Platform Fee)</span>
                                        <span id="ql-platform-fee" class="font-bold text-slate-900">Rp {{ number_format($platformFee, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="pt-2.5 border-t border-slate-200 flex items-center justify-between">
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Total Pembayaran</span>
                                        <span id="ql-total-price" class="text-lg font-bold text-theme-primary">Rp 0</span>
                                    </div>
                                </div>

                                <input type="hidden" name="pic_name">
                                <input type="hidden" name="pic_email">
                                <input type="hidden" name="pic_phone">

                                <button id="quickSubmitBtn" type="submit" class="submit-btn w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-theme-primary hover-bg-theme-primary-dark text-white font-bold text-sm shadow transition-colors">
                                    <i class="fa-solid fa-paper-plane text-white"></i>
                                    <span>Proses Pendaftaran</span>
                                </button>
                                
                                @if(env('RECAPTCHA_SITE_KEY_v3'))
                                    <div class="text-[10px] text-slate-400 text-center leading-relaxed">
                                        Protected by Google reCAPTCHA. 
                                        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="underline hover:text-slate-600">Privacy Policy</a>
                                        &
                                        <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer" class="underline hover:text-slate-600">Terms of Service</a>
                                    </div>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            </aside>
        </div>

        <!-- Participants List Table (Full Width Below 2-Column Grid) -->
        @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
            <div class="pro-card p-5 sm:p-6 max-w-full overflow-hidden mt-8 sm:mt-10" id="participants-list">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-4">
                    <span class="w-6 h-6 rounded-md bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                        <i class="fa-solid fa-users text-white"></i>
                    </span>
                    Daftar Peserta Terdaftar
                </div>
                <div id="vue-participants-app" class="w-full max-w-full overflow-hidden">
                    @include('events.partials.participants-table-light')
                </div>
            </div>
        @endif
    </main>

    @include('events.partials.moota-payment-modal')

    <!-- Event Gallery Lightbox Modal -->
    <div id="qlLightboxModal" class="fixed inset-0 z-[1100] hidden flex items-center justify-center p-3 sm:p-6 bg-slate-950/90 backdrop-blur-sm select-none" role="dialog" aria-modal="true">
        <!-- Close Button -->
        <button type="button" id="qlLightboxClose" class="absolute top-4 right-4 z-20 w-10 h-10 rounded-full bg-slate-900/80 hover:bg-slate-900 text-white flex items-center justify-center transition border border-white/20 shadow-lg cursor-pointer" aria-label="Tutup Lightbox">
            <i class="fa-solid fa-xmark text-lg text-white"></i>
        </button>

        <!-- Navigation Buttons -->
        <button type="button" id="qlLightboxPrev" class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-slate-900 text-white flex items-center justify-center transition border border-white/20 shadow-lg cursor-pointer" aria-label="Foto Sebelumnya">
            <i class="fa-solid fa-chevron-left text-base text-white"></i>
        </button>

        <button type="button" id="qlLightboxNext" class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-slate-900/80 hover:bg-slate-900 text-white flex items-center justify-center transition border border-white/20 shadow-lg cursor-pointer" aria-label="Foto Selanjutnya">
            <i class="fa-solid fa-chevron-right text-base text-white"></i>
        </button>

        <!-- Counter Indicator -->
        <div id="qlLightboxCounter" class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 px-3.5 py-1 rounded-full bg-slate-900/85 border border-white/20 text-white text-xs font-bold tracking-wider">
            1 / 1
        </div>

        <!-- Lightbox Image Wrapper -->
        <div id="qlLightboxContent" class="relative max-w-5xl max-h-[88vh] flex items-center justify-center overflow-hidden">
            <img id="qlLightboxImg" src="" alt="Full Preview" class="max-w-full max-h-[85vh] object-contain rounded-xl shadow-2xl transition-opacity duration-200">
        </div>
    </div>

    <!-- Webcam Capture Modal for Direct Photo Snapping -->
    <div id="qlWebcamModal" class="fixed inset-0 z-[1000] hidden flex items-center justify-center p-4 bg-slate-900/80">
        <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-md shadow-2xl overflow-hidden flex flex-col relative z-10">
            <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-sm">
                        <i class="fa-solid fa-camera text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Ambil Foto Langsung</h3>
                        <p class="text-xs text-slate-500">Posisikan wajah menghadap kamera.</p>
                    </div>
                </div>
                <button type="button" onclick="closeWebcamModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-slate-700"></i>
                </button>
            </div>
            <div class="relative bg-black aspect-square overflow-hidden flex items-center justify-center">
                <video id="qlWebcamVideo" autoplay playsinline muted class="w-full h-full object-cover"></video>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div class="w-48 h-60 rounded-[50%] border-2 border-dashed border-white/80 bg-white/5"></div>
                </div>
            </div>
            <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center gap-2">
                <button type="button" onclick="closeWebcamModal()" class="flex-1 py-2.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition">
                    Batal
                </button>
                <button type="button" id="qlSnapBtn" class="flex-1 py-2.5 rounded-xl bg-theme-primary hover-bg-theme-primary-dark text-white font-bold text-xs flex items-center justify-center gap-2 transition shadow">
                    <i class="fa-solid fa-camera text-white"></i>
                    <span>Jepret Foto</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Route Map Modal -->
    <div id="routeModal" class="fixed inset-0 z-[999] hidden">
        <div class="absolute inset-0 bg-slate-900/60" onclick="closeRouteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-4xl max-h-[90vh] shadow-2xl overflow-hidden flex flex-col relative z-10">
                <div class="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-sm">
                            <i class="fa-solid fa-map-location-dot text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-bold text-slate-900">Rute Lari & GPX Tracker</h3>
                            <p class="text-xs text-slate-500">Peta interaktif rute event {{ $event->name }}.</p>
                        </div>
                    </div>
                    <button onclick="closeRouteModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 flex items-center justify-center text-slate-700 transition-colors">
                        <i class="fa-solid fa-xmark text-slate-700"></i>
                    </button>
                </div>

                <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar max-w-full">
                        @if(!empty($gpxList) && $gpxList->isNotEmpty())
                            @foreach($gpxList as $idx => $gpx)
                                <button type="button" onclick="selectGpxTrack(this)" data-gpx-url="{{ asset('storage/'.$gpx->file_path) }}" class="gpx-tab-btn px-3 py-1.5 rounded-lg text-xs font-bold transition border {{ $idx === 0 ? 'bg-orange-600 text-white border-orange-600' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                                    {{ $gpx->category_name ?: 'Rute '.($idx+1) }}
                                </button>
                            @endforeach
                        @else
                            <span class="text-xs text-slate-500 font-medium">Titik Lokasi Event: {{ $event->location_name }}</span>
                        @endif
                    </div>
                    @if(!empty($gpxList) && $gpxList->isNotEmpty())
                        <a id="gpx-download-link" href="{{ asset('storage/'.$gpxList[0]->file_path) }}" download class="inline-flex items-center gap-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white px-3 py-1.5 text-xs font-bold transition-colors shrink-0">
                            <i class="fa-solid fa-download text-white"></i> Unduh File GPX
                        </a>
                    @endif
                </div>

                <div class="relative flex-1 min-h-[350px] sm:min-h-[460px] bg-slate-100">
                    <div id="route-mapbox-loader" class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-slate-100/90 text-slate-500 gap-2 transition-opacity duration-300">
                        <i class="fa-solid fa-spinner fa-spin text-2xl text-theme-primary"></i>
                        <span class="text-xs font-bold text-slate-700">Memuat Peta Rute...</span>
                    </div>
                    <div id="route-mapbox-map" class="w-full h-full min-h-[350px] sm:min-h-[460px]"></div>
                    @if($event->map_embed_url)
                        <div id="route-iframe-container" class="hidden w-full h-full min-h-[350px] sm:min-h-[460px]">
                            <iframe src="{{ $event->map_embed_url }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Terms Modal -->
    @if($event->terms_and_conditions)
        <div id="termsModal" class="fixed inset-0 z-[999] hidden">
            <div class="absolute inset-0 bg-slate-900/60" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-2xl max-h-[85vh] shadow-2xl overflow-hidden flex flex-col relative z-10">
                    <div class="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center text-sm">
                                <i class="fa-solid fa-file-shield text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-sm sm:text-base font-bold text-slate-900">Syarat & Ketentuan Event</h3>
                                <p class="text-xs text-slate-500">Peraturan resmi bagi seluruh peserta.</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('termsModal').classList.add('hidden')" class="w-8 h-8 rounded-lg bg-slate-900 hover:bg-slate-800 flex items-center justify-center text-white transition-colors">
                            <i class="fa-solid fa-xmark text-white"></i>
                        </button>
                    </div>
                    <div class="flex-1 p-6 md:p-8 overflow-y-auto content-html">
                        {!! $event->terms_and_conditions !!}
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Registration Success Modal -->
    <div id="registrationSuccessModal" class="fixed inset-0 z-[999] hidden">
        <div class="absolute inset-0 bg-slate-900/65" onclick="closeSuccessModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-lg shadow-2xl overflow-hidden relative z-10">
                <div class="p-6 sm:p-8 text-center">
                    <div id="modalSuccessIconNormal" class="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-2xl mx-auto shadow-sm mb-4">
                        <i class="fa-solid fa-circle-check text-white"></i>
                    </div>
                    <div id="modalSuccessIconCod" class="hidden w-16 h-16 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl mx-auto shadow-sm mb-4">
                        <i class="fa-solid fa-clock-rotate-left text-white"></i>
                    </div>
                    <div id="modalSuccessIconPending" class="hidden w-16 h-16 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-2xl mx-auto shadow-sm mb-4">
                        <i class="fa-solid fa-clock text-white"></i>
                    </div>
                    <h3 id="modalSuccessTitle" class="text-xl sm:text-2xl font-black text-slate-900">Pendaftaran Berhasil!</h3>
                    <p id="modalSuccessDesc" class="mt-2 text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Terima kasih telah mendaftar di <strong>{{ $event->name }}</strong>. Konfirmasi pendaftaran dan e-Tiket resmi telah dikirim ke email kamu.
                    </p>

                    <div id="modalCodPendingAlert" class="hidden mt-4 p-3.5 rounded-xl bg-amber-50 border border-amber-300 text-left text-xs text-amber-900 space-y-1">
                        <div class="font-bold text-amber-950 flex items-center gap-1.5">
                            <i class="fa-solid fa-hourglass-half text-amber-700"></i>
                            <span>Status: Menunggu Persetujuan Panitia (Approval)</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Data pendaftaran dan foto Anda telah tersimpan. Panitia EO akan memverifikasi pendaftaran Anda. Setelah disetujui, e-Tiket resmi akan dikirim dan nama Anda akan masuk ke daftar peserta resmi.
                        </p>
                    </div>

                    <div id="modalPendingAlert" class="hidden mt-4 p-3.5 rounded-xl bg-amber-50 border border-amber-300 text-left text-xs text-amber-900 space-y-1">
                        <div class="font-bold text-amber-950 flex items-center gap-1.5">
                            <i class="fa-solid fa-clock text-amber-700"></i>
                            <span>Status: Menunggu Pembayaran</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Transaksi pendaftaran kamu telah dicatat. Jika popup pembayaran sebelumnya tertutup atau belum selesai, silakan klik tombol "Lanjutkan Pembayaran" di bawah ini.
                        </p>
                    </div>

                    <div class="mt-5 p-4 rounded-xl bg-slate-50 border border-slate-200 text-left space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Event:</span>
                            <span class="font-bold text-slate-900 truncate max-w-[240px]">{{ $event->name }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600">
                            <span>Tanggal:</span>
                            <span class="font-bold text-slate-900">{{ optional($event->start_at)->format('d F Y') ?: '-' }}</span>
                        </div>
                        @if($event->location_name)
                            <div class="flex items-center justify-between text-slate-600">
                                <span>Lokasi:</span>
                                <span class="font-bold text-slate-900 truncate max-w-[240px]">{{ $event->location_name }}</span>
                            </div>
                        @endif
                    </div>

                    <div id="modalPendingAction" class="hidden mt-4">
                        <a href="{{ route('events.payments.continue', $event->slug) }}" class="w-full inline-flex items-center justify-center gap-1.5 py-3 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold transition-colors text-center shadow-sm">
                            <span>Lanjutkan Pembayaran Sekarang</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row items-center gap-3">
                        @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
                            <a href="#participants-list" onclick="closeSuccessModal()" class="w-full sm:w-1/2 py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors text-center shadow-sm">
                                <i class="fa-solid fa-users text-white mr-1.5"></i> Lihat Peserta
                            </a>
                        @endif
                        <button type="button" onclick="closeSuccessModal()" class="w-full {{ (($hasPaidParticipants ?? false) && $event->show_participant_list) ? 'sm:w-1/2' : 'sm:w-full' }} py-3 px-4 rounded-xl bg-theme-primary hover-bg-theme-primary-dark text-white text-xs font-bold transition-colors text-center shadow-sm">
                            <i class="fa-solid fa-check text-white mr-1.5"></i> Tutup & Lihat Event
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var routeMap = null;
        var startMarker = null;
        var finishMarker = null;
        var gpxTracks = @json($gpxList ?? []);
        var eventLat = {{ $event->location_lat ?: 'null' }};
        var eventLng = {{ $event->location_lng ?: 'null' }};
        var hasGpx = {{ (!empty($gpxList) && $gpxList->isNotEmpty()) ? 'true' : 'false' }};
        var mapEmbedUrl = "{{ $event->map_embed_url ?: '' }}";

        var mapboxAssetsLoading = false;
        var mapboxAssetsLoaded = false;

        function loadMapboxAssets(callback) {
            if (window.mapboxgl) {
                if (callback) callback();
                return;
            }
            if (mapboxAssetsLoading) {
                const checkInterval = setInterval(function() {
                    if (window.mapboxgl) {
                        clearInterval(checkInterval);
                        if (callback) callback();
                    }
                }, 50);
                return;
            }
            mapboxAssetsLoading = true;

            // Load Mapbox CSS on demand
            if (!document.getElementById('mapbox-gl-css')) {
                const link = document.createElement('link');
                link.id = 'mapbox-gl-css';
                link.rel = 'stylesheet';
                link.href = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css';
                document.head.appendChild(link);
            }

            // Load Mapbox JS on demand
            const script = document.createElement('script');
            script.src = 'https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js';
            script.async = true;
            script.onload = function() {
                mapboxAssetsLoaded = true;
                mapboxAssetsLoading = false;
                if (callback) callback();
            };
            script.onerror = function() {
                mapboxAssetsLoading = false;
                showIframeFallback();
            };
            document.head.appendChild(script);
        }

        function hideMapLoader() {
            const loader = document.getElementById('route-mapbox-loader');
            if (loader) {
                loader.classList.add('opacity-0', 'pointer-events-none');
                setTimeout(() => loader.classList.add('hidden'), 300);
            }
        }

        function openRouteModal() {
            const modal = document.getElementById('routeModal');
            modal.classList.remove('hidden');
            loadMapboxAssets(function() {
                setTimeout(initRouteMap, 100);
            });
        }

        function closeRouteModal() {
            const modal = document.getElementById('routeModal');
            modal.classList.add('hidden');
        }

        function openSuccessModal() {
            const modal = document.getElementById('registrationSuccessModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeSuccessModal() {
            const modal = document.getElementById('registrationSuccessModal');
            if (modal) {
                modal.classList.add('hidden');
            }
            scrollToPaymentBadge();
        }

        function scrollToPaymentBadge() {
            const badge = document.getElementById('payment-notification-badge');
            if (badge) {
                badge.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const paymentStatus = urlParams.get('payment');
            if (paymentStatus === 'success') {
                const normalIcon = document.getElementById('modalSuccessIconNormal');
                const codIcon = document.getElementById('modalSuccessIconCod');
                const pendingIcon = document.getElementById('modalSuccessIconPending');
                const codAlert = document.getElementById('modalCodPendingAlert');
                const pendingAlert = document.getElementById('modalPendingAlert');
                const pendingAction = document.getElementById('modalPendingAction');

                if (normalIcon) normalIcon.classList.remove('hidden');
                if (codIcon) codIcon.classList.add('hidden');
                if (pendingIcon) pendingIcon.classList.add('hidden');
                if (codAlert) codAlert.classList.add('hidden');
                if (pendingAlert) pendingAlert.classList.add('hidden');
                if (pendingAction) pendingAction.classList.add('hidden');

                openSuccessModal();
                setTimeout(scrollToPaymentBadge, 200);
            } else if (paymentStatus === 'cod_pending') {
                const titleEl = document.getElementById('modalSuccessTitle');
                const descEl = document.getElementById('modalSuccessDesc');
                const normalIcon = document.getElementById('modalSuccessIconNormal');
                const codIcon = document.getElementById('modalSuccessIconCod');
                const pendingIcon = document.getElementById('modalSuccessIconPending');
                const codAlert = document.getElementById('modalCodPendingAlert');
                const pendingAlert = document.getElementById('modalPendingAlert');
                const pendingAction = document.getElementById('modalPendingAction');

                if (titleEl) titleEl.textContent = 'Pendaftaran COD Berhasil Terkirim!';
                if (descEl) descEl.innerHTML = 'Pendaftaran Anda di <strong>{{ $event->name }}</strong> telah kami terima dan sedang dalam tahap verifikasi panitia.';
                if (normalIcon) normalIcon.classList.add('hidden');
                if (codIcon) codIcon.classList.remove('hidden');
                if (pendingIcon) pendingIcon.classList.add('hidden');
                if (codAlert) codAlert.classList.remove('hidden');
                if (pendingAlert) pendingAlert.classList.add('hidden');
                if (pendingAction) pendingAction.classList.add('hidden');

                openSuccessModal();
                setTimeout(scrollToPaymentBadge, 200);
            } else if (paymentStatus === 'pending') {
                const titleEl = document.getElementById('modalSuccessTitle');
                const descEl = document.getElementById('modalSuccessDesc');
                const normalIcon = document.getElementById('modalSuccessIconNormal');
                const codIcon = document.getElementById('modalSuccessIconCod');
                const pendingIcon = document.getElementById('modalSuccessIconPending');
                const codAlert = document.getElementById('modalCodPendingAlert');
                const pendingAlert = document.getElementById('modalPendingAlert');
                const pendingAction = document.getElementById('modalPendingAction');

                if (titleEl) titleEl.textContent = 'Menunggu Pembayaran';
                if (descEl) descEl.innerHTML = 'Pendaftaran Anda di <strong>{{ $event->name }}</strong> telah dicatat. Silakan selesaikan pembayaran untuk mengonfirmasi pendaftaran.';
                if (normalIcon) normalIcon.classList.add('hidden');
                if (codIcon) codIcon.classList.add('hidden');
                if (pendingIcon) pendingIcon.classList.remove('hidden');
                if (codAlert) codAlert.classList.add('hidden');
                if (pendingAlert) pendingAlert.classList.remove('hidden');
                if (pendingAction) pendingAction.classList.remove('hidden');

                openSuccessModal();
                setTimeout(scrollToPaymentBadge, 200);
            }
        });

        function initRouteMap() {
            const mapboxToken = '{{ config('services.mapbox.token') }}';
            if (!mapboxToken || !window.mapboxgl) {
                showIframeFallback();
                hideMapLoader();
                return;
            }

            if (!routeMap) {
                mapboxgl.accessToken = mapboxToken;
                routeMap = new mapboxgl.Map({
                    container: 'route-mapbox-map',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: eventLng && eventLat ? [eventLng, eventLat] : [106.8271, -6.1754],
                    zoom: 13
                });

                routeMap.addControl(new mapboxgl.NavigationControl());

                routeMap.on('load', function() {
                    hideMapLoader();
                    triggerGpxOrEventLocation();
                });
            } else {
                hideMapLoader();
                routeMap.resize();
                triggerGpxOrEventLocation();
            }
        }

        function triggerGpxOrEventLocation() {
            if (hasGpx && gpxTracks.length > 0) {
                const firstBtn = document.querySelector('.gpx-tab-btn');
                if (firstBtn) {
                    selectGpxTrack(firstBtn);
                }
            } else if (eventLat && eventLng) {
                routeMap.setCenter([eventLng, eventLat]);
                routeMap.setZoom(14);
                
                if (startMarker) {
                    startMarker.remove();
                }
                
                const el = document.createElement('div');
                el.innerHTML = '<div style="width:24px;height:24px;border-radius:999px;background:#f1631e;border:2px solid #fff;box-shadow:0 4px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;"><i class="fa-solid fa-location-dot text-white"></i></div>';

                startMarker = new mapboxgl.Marker(el)
                    .setLngLat([eventLng, eventLat])
                    .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML("<b>{{ $event->location_name }}</b>"))
                    .addTo(routeMap);

                startMarker.togglePopup();
            } else {
                showIframeFallback();
            }
        }

        function showIframeFallback() {
            hideMapLoader();
            document.getElementById('route-mapbox-map').classList.add('hidden');
            document.getElementById('route-iframe-container').classList.remove('hidden');
        }

        function selectGpxTrack(btn) {
            document.querySelectorAll('.gpx-tab-btn').forEach(b => {
                b.classList.remove('bg-orange-600', 'text-white', 'border-orange-600');
                b.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
            });
            btn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            btn.classList.add('bg-orange-600', 'text-white', 'border-orange-600');

            const gpxUrl = btn.getAttribute('data-gpx-url');
            
            const downloadLink = document.getElementById('gpx-download-link');
            if (downloadLink) {
                downloadLink.href = gpxUrl;
            }

            fetch(gpxUrl)
                .then(response => response.text())
                .then(gpxText => {
                    const parser = new DOMParser();
                    const xml = parser.parseFromString(gpxText, 'text/xml');
                    const points = [];
                    const trkpts = xml.querySelectorAll('trkpt, rtept');
                    
                    trkpts.forEach(pt => {
                        const lat = parseFloat(pt.getAttribute('lat'));
                        const lon = parseFloat(pt.getAttribute('lon'));
                        if (!isNaN(lat) && !isNaN(lon)) {
                            points.push([lon, lat]);
                        }
                    });

                    if (points.length === 0) return;

                    drawRouteOnMap(points);
                })
                .catch(err => {
                    console.error('Error loading GPX file:', err);
                });
        }

        function drawRouteOnMap(points) {
            if (!routeMap || !routeMap.isStyleLoaded()) {
                setTimeout(function() {
                    drawRouteOnMap(points);
                }, 100);
                return;
            }

            if (startMarker) startMarker.remove();
            if (finishMarker) finishMarker.remove();

            if (routeMap.getLayer('route-line')) routeMap.removeLayer('route-line');
            if (routeMap.getSource('route-source')) routeMap.removeSource('route-source');

            routeMap.addSource('route-source', {
                type: 'geojson',
                data: {
                    type: 'Feature',
                    geometry: {
                        type: 'LineString',
                        coordinates: points
                    }
                }
            });

            routeMap.addLayer({
                id: 'route-line',
                type: 'line',
                source: 'route-source',
                layout: {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                paint: {
                    'line-color': '#f1631e',
                    'line-width': 5,
                    'line-opacity': 0.85
                }
            });

            const startPt = points[0];
            const finishPt = points[points.length - 1];

            const elStart = document.createElement('div');
            elStart.innerHTML = '<div style="width:24px;height:24px;border-radius:999px;background:#22c55e;border:2px solid #fff;box-shadow:0 4px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;"><i class="fa-solid fa-play ml-0.5 text-white"></i></div>';

            startMarker = new mapboxgl.Marker(elStart)
                .setLngLat(startPt)
                .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML('<b>Start</b>'))
                .addTo(routeMap);

            const elFinish = document.createElement('div');
            elFinish.innerHTML = '<div style="width:24px;height:24px;border-radius:999px;background:#ef4444;border:2px solid #fff;box-shadow:0 4px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;"><i class="fa-solid fa-flag-checkered text-white"></i></div>';

            finishMarker = new mapboxgl.Marker(elFinish)
                .setLngLat(finishPt)
                .setPopup(new mapboxgl.Popup({ offset: 25 }).setHTML('<b>Finish</b>'))
                .addTo(routeMap);

            const bounds = new mapboxgl.LngLatBounds();
            points.forEach(p => bounds.extend(p));
            routeMap.fitBounds(bounds, { padding: 40, duration: 1000 });
        }
    </script>

    <script>
        (function () {
            const root = document.getElementById('qlGallery');
            const imgEl = document.getElementById('qlGalleryImg');
            const prevBtn = document.getElementById('qlGalleryPrev');
            const nextBtn = document.getElementById('qlGalleryNext');
            const dotsEl = document.getElementById('qlGalleryDots');

            // Lightbox elements
            const lightbox = document.getElementById('qlLightboxModal');
            const lightboxImg = document.getElementById('qlLightboxImg');
            const lightboxClose = document.getElementById('qlLightboxClose');
            const lightboxPrev = document.getElementById('qlLightboxPrev');
            const lightboxNext = document.getElementById('qlLightboxNext');
            const lightboxCounter = document.getElementById('qlLightboxCounter');

            if (!root || !imgEl) return;

            let items = [];
            try {
                items = JSON.parse(root.getAttribute('data-images') || '[]');
            } catch (e) {
                items = [];
            }
            items = Array.isArray(items) ? items.filter(Boolean) : [];
            if (items.length === 0 && imgEl.src) {
                items.push(imgEl.src);
            }

            let index = 0;

            if (items.length <= 1) {
                if (prevBtn) prevBtn.style.display = 'none';
                if (nextBtn) nextBtn.style.display = 'none';
                if (dotsEl) dotsEl.style.display = 'none';
                if (lightboxPrev) lightboxPrev.style.display = 'none';
                if (lightboxNext) lightboxNext.style.display = 'none';
                if (lightboxCounter) lightboxCounter.style.display = 'none';
            }

            function show(i) {
                if (items.length === 0) return;
                index = (i + items.length) % items.length;
                imgEl.style.opacity = '0.35';
                setTimeout(() => {
                    imgEl.src = items[index];
                    imgEl.style.opacity = '1';
                    renderDots();
                }, 120);
            }

            function renderDots() {
                if (!dotsEl || items.length <= 1) return;
                dotsEl.innerHTML = '';
                items.forEach((_, i) => {
                    const dot = document.createElement('div');
                    dot.className = 'ql-gallery-dot' + (i === index ? ' ql-gallery-dot-active' : '');
                    dot.setAttribute('data-active', i === index ? '1' : '0');
                    dot.onclick = () => show(i);
                    dotsEl.appendChild(dot);
                });
            }

            if (prevBtn) prevBtn.onclick = (e) => { e.stopPropagation(); show(index - 1); };
            if (nextBtn) nextBtn.onclick = (e) => { e.stopPropagation(); show(index + 1); };
            renderDots();

            // Lightbox logic
            function openLightbox(idx) {
                if (!lightbox || !lightboxImg || items.length === 0) return;
                if (idx !== undefined) {
                    show(idx);
                }
                updateLightboxView();
                lightbox.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                if (!lightbox) return;
                lightbox.classList.add('hidden');
                document.body.style.overflow = '';
            }

            function updateLightboxView() {
                if (!lightboxImg || items.length === 0) return;
                lightboxImg.style.opacity = '0.4';
                setTimeout(() => {
                    lightboxImg.src = items[index];
                    lightboxImg.style.opacity = '1';
                    if (lightboxCounter && items.length > 1) {
                        lightboxCounter.textContent = (index + 1) + ' / ' + items.length;
                    }
                }, 100);
            }

            // Click on gallery image opens lightbox
            imgEl.addEventListener('click', function () {
                openLightbox(index);
            });

            if (lightboxClose) {
                lightboxClose.addEventListener('click', closeLightbox);
            }

            if (lightboxPrev) {
                lightboxPrev.addEventListener('click', function (e) {
                    e.stopPropagation();
                    show(index - 1);
                    updateLightboxView();
                });
            }

            if (lightboxNext) {
                lightboxNext.addEventListener('click', function (e) {
                    e.stopPropagation();
                    show(index + 1);
                    updateLightboxView();
                });
            }

            if (lightbox) {
                lightbox.addEventListener('click', function (e) {
                    if (e.target === lightbox || e.target.id === 'qlLightboxContent') {
                        closeLightbox();
                    }
                });
            }

            window.addEventListener('keydown', function (e) {
                if (!lightbox || lightbox.classList.contains('hidden')) return;
                if (e.key === 'Escape') {
                    closeLightbox();
                } else if (e.key === 'ArrowLeft' && items.length > 1) {
                    show(index - 1);
                    updateLightboxView();
                } else if (e.key === 'ArrowRight' && items.length > 1) {
                    show(index + 1);
                    updateLightboxView();
                }
            });
        })();
    </script>

    <script>
        var activeWebcamParticipantItem = null;
        var activeWebcamStream = null;

        function openWebcamModal(item) {
            activeWebcamParticipantItem = item;
            const modal = document.getElementById('qlWebcamModal');
            const video = document.getElementById('qlWebcamVideo');
            if (!modal || !video) return;

            modal.classList.remove('hidden');
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 640 } }
                }).then(function(stream) {
                    activeWebcamStream = stream;
                    video.srcObject = stream;
                }).catch(function(err) {
                    alert('Tidak dapat mengakses kamera: ' + (err.message || err));
                    closeWebcamModal();
                });
            } else {
                alert('Browser Anda tidak mendukung akses kamera langsung.');
                closeWebcamModal();
            }
        }

        function closeWebcamModal() {
            const modal = document.getElementById('qlWebcamModal');
            const video = document.getElementById('qlWebcamVideo');
            if (modal) modal.classList.add('hidden');
            if (activeWebcamStream) {
                activeWebcamStream.getTracks().forEach(t => t.stop());
                activeWebcamStream = null;
            }
            if (video) video.srcObject = null;
            activeWebcamParticipantItem = null;
        }

        (function(){
            const form = document.getElementById('quickForm');
            if(!form) return;

            const participantsWrapper = document.getElementById('participantsWrapper');
            const participantTemplate = document.getElementById('participantTemplate');
            const addParticipantBtn = document.getElementById('addParticipantBtn');
            const ticketPriceEl = document.getElementById('ql-ticket-price');
            const addonPriceEl = document.getElementById('ql-addon-price');
            const platformFeeEl = document.getElementById('ql-platform-fee');
            const totalPriceEl = document.getElementById('ql-total-price');
            const submitBtn = document.getElementById('quickSubmitBtn');
            const platformFee = {{ $platformFee }};
            const originalSubmitText = submitBtn ? submitBtn.innerHTML : '';

            const couponInputEl = document.getElementById('qlCouponInput');
            const couponHiddenEl = document.getElementById('qlCouponCodeHidden');
            const couponApplyBtn = document.getElementById('qlCouponApplyBtn');
            const couponClearBtn = document.getElementById('qlCouponClearBtn');
            const couponMessageEl = document.getElementById('qlCouponMessage');
            const couponRowEl = document.getElementById('qlCouponRow');
            const couponLabelEl = document.getElementById('qlCouponLabel');
            const discountAmountEl = document.getElementById('qlDiscountAmount');
            const couponUrl = '{{ route('events.register.coupon', $event->slug) }}';
            const eventId = {{ $event->id }};
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // Handle webcam snap button
            const snapBtn = document.getElementById('qlSnapBtn');
            if (snapBtn) {
                snapBtn.addEventListener('click', function() {
                    const video = document.getElementById('qlWebcamVideo');
                    if (!video || !activeWebcamParticipantItem) return;

                    const canvas = document.createElement('canvas');
                    canvas.width = 320;
                    canvas.height = 320;
                    const ctx = canvas.getContext('2d');

                    const minSide = Math.min(video.videoWidth || 320, video.videoHeight || 320);
                    const sx = ((video.videoWidth || 320) - minSide) / 2;
                    const sy = ((video.videoHeight || 320) - minSide) / 2;
                    ctx.drawImage(video, sx, sy, minSide, minSide, 0, 0, 320, 320);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

                    const photoHidden = activeWebcamParticipantItem.querySelector('input[data-field="photo"]');
                    const previewImg = activeWebcamParticipantItem.querySelector('.photo-preview-img');
                    const placeholderIcon = activeWebcamParticipantItem.querySelector('.photo-placeholder-icon');
                    const clearBtn = activeWebcamParticipantItem.querySelector('.photo-clear-btn');

                    if (photoHidden) photoHidden.value = dataUrl;
                    if (previewImg) {
                        previewImg.src = dataUrl;
                        previewImg.classList.remove('hidden');
                    }
                    if (placeholderIcon) placeholderIcon.classList.add('hidden');
                    if (clearBtn) clearBtn.classList.remove('hidden');

                    closeWebcamModal();
                });
            }

            let coupon = null;
            let discountAmount = 0;
            let couponAppliedOnSubTotal = null;

            function formatCurrency(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value || 0));
            }

            function setCouponMessage(text, type) {
                if (!couponMessageEl) return;
                if (!text) {
                    couponMessageEl.textContent = '';
                    couponMessageEl.classList.add('hidden');
                    return;
                }
                couponMessageEl.textContent = text;
                couponMessageEl.classList.remove('hidden');
                couponMessageEl.classList.toggle('text-emerald-700', type === 'success');
                couponMessageEl.classList.toggle('text-rose-600', type === 'error');
                couponMessageEl.classList.toggle('text-slate-600', type === 'info');
            }

            function getParticipantItems() {
                return Array.from(participantsWrapper.querySelectorAll('[data-participant-item]'));
            }

            function selectedCategoryPrice(item) {
                const checked = item.querySelector('input[data-field="category_id"]:checked');
                return checked ? parseInt(checked.dataset.price || '0', 10) : 0;
            }

            function selectedAddonPrice(item) {
                return Array.from(item.querySelectorAll('input[data-addon-price]:checked')).reduce(function(sum, el) {
                    return sum + parseInt(el.dataset.addonPrice || '0', 10);
                }, 0);
            }

            function computeBaseTotals() {
                const items = getParticipantItems();
                const ticket = items.reduce(function(sum, item) { return sum + selectedCategoryPrice(item); }, 0);
                const addons = items.reduce(function(sum, item) { return sum + selectedAddonPrice(item); }, 0);
                const participantCount = items.length;
                const subTotal = ticket + addons;
                return { ticket, addons, participantCount, subTotal };
            }

            function clearCoupon(reason) {
                coupon = null;
                discountAmount = 0;
                couponAppliedOnSubTotal = null;
                if (couponHiddenEl) couponHiddenEl.value = '';
                if (couponRowEl) couponRowEl.classList.add('hidden');
                if (couponClearBtn) couponClearBtn.classList.add('hidden');
                if (couponLabelEl) couponLabelEl.textContent = '';
                if (discountAmountEl) discountAmountEl.textContent = '-Rp 0';
                setCouponMessage(reason || '', reason ? 'info' : null);
            }

            async function applyCoupon() {
                if (!couponInputEl || !couponHiddenEl) return;

                const code = String(couponInputEl.value || '').trim().toUpperCase();
                couponInputEl.value = code;
                if (!code) {
                    clearCoupon('');
                    updateSummary();
                    return;
                }

                const totals = computeBaseTotals();
                const subTotal = totals.subTotal;
                if (subTotal <= 0) {
                    clearCoupon('Total tiket masih 0, kupon belum bisa diterapkan.');
                    updateSummary();
                    return;
                }

                setCouponMessage('Memverifikasi kupon...', 'info');

                let res;
                try {
                    const r = await fetch(couponUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            event_id: eventId,
                            coupon_code: code,
                            total_amount: subTotal,
                        }),
                    });
                    res = await r.json().catch(function () { return null; });
                    if (!r.ok || !res || !res.success) {
                        throw new Error((res && res.message) ? res.message : 'Kupon tidak valid.');
                    }
                } catch (e) {
                    clearCoupon('Kupon gagal: ' + (e && e.message ? e.message : 'Terjadi kesalahan.'));
                    updateSummary();
                    return;
                }

                coupon = res.coupon || null;
                discountAmount = Math.max(0, Math.min(subTotal, Number(res.discount_amount || 0)));
                couponAppliedOnSubTotal = subTotal;
                couponHiddenEl.value = coupon && coupon.code ? coupon.code : code;

                if (couponRowEl) couponRowEl.classList.remove('hidden');
                if (couponClearBtn) couponClearBtn.classList.remove('hidden');
                if (couponLabelEl) couponLabelEl.textContent = '(' + (couponHiddenEl.value || code) + ')';
                if (discountAmountEl) discountAmountEl.textContent = '-' + formatCurrency(discountAmount);

                setCouponMessage('Kupon diterapkan: ' + (couponHiddenEl.value || code), 'success');
                updateSummary();
            }

            function updateSummary() {
                const totals = computeBaseTotals();
                const ticket = totals.ticket;
                const addons = totals.addons;
                const participantCount = totals.participantCount;
                const subTotal = totals.subTotal;

                if (couponHiddenEl && couponHiddenEl.value && couponAppliedOnSubTotal !== null && couponAppliedOnSubTotal !== subTotal) {
                    clearCoupon('Kupon direset karena total berubah. Silakan apply lagi.');
                }

                const discount = (couponHiddenEl && couponHiddenEl.value) ? discountAmount : 0;
                const fee = (subTotal - discount) > 0 ? platformFee * participantCount : 0;
                const total = (subTotal - discount) + fee;
                if (ticketPriceEl) ticketPriceEl.textContent = formatCurrency(ticket);
                if (addonPriceEl) addonPriceEl.textContent = formatCurrency(addons);
                if (platformFeeEl) platformFeeEl.textContent = formatCurrency(fee);
                if (totalPriceEl) totalPriceEl.textContent = formatCurrency(total);

                // Hide individual rows if 0
                const ticketRow = document.getElementById('qlTicketRow');
                const addonRow = document.getElementById('qlAddonRow');
                const feeRow = document.getElementById('qlFeeRow');
                const breakdownContainer = document.getElementById('qlPriceBreakdownContainer');
                const couponContainer = document.getElementById('qlCouponContainer');

                if (ticketRow) ticketRow.classList.toggle('hidden', ticket <= 0);
                if (addonRow) addonRow.classList.toggle('hidden', addons <= 0);
                if (feeRow) feeRow.classList.toggle('hidden', fee <= 0);

                // If total is 0 (nominal 0 / free event), hide the entire price breakdown and coupon box
                const isFree = total <= 0;
                if (breakdownContainer) breakdownContainer.classList.toggle('hidden', isFree);
                if (couponContainer) couponContainer.classList.toggle('hidden', isFree);

                if (couponRowEl) couponRowEl.classList.toggle('hidden', ! (couponHiddenEl && couponHiddenEl.value));
                if (couponLabelEl && couponHiddenEl && couponHiddenEl.value) couponLabelEl.textContent = '(' + couponHiddenEl.value + ')';
                if (discountAmountEl) discountAmountEl.textContent = discount > 0 ? '-' + formatCurrency(discount) : '-Rp 0';

                const paymentContainer = document.getElementById('payment-methods-container');
                const codNotice = document.getElementById('qlCodNotice');
                const selectedMethod = form.querySelector('input[name="payment_method"]:checked')?.value || '';
                const isCod = selectedMethod === 'cod';

                if (codNotice) {
                    codNotice.classList.toggle('hidden', !isCod || total <= 0);
                }

                // Update Photo Requirements based on COD selection
                getParticipantItems().forEach(function(item) {
                    const photoBox = item.querySelector('[data-photo-container]');
                    const reqBadge = item.querySelector('.photo-badge-required');
                    const optBadge = item.querySelector('.photo-badge-optional');

                    if (photoBox) {
                        if (isCod && total > 0) {
                            photoBox.classList.add('border-orange-300', 'bg-orange-50/30');
                            photoBox.classList.remove('border-slate-200', 'bg-white');
                        } else {
                            photoBox.classList.remove('border-orange-300', 'bg-orange-50/30');
                            photoBox.classList.add('border-slate-200', 'bg-white');
                        }
                    }
                    if (reqBadge) reqBadge.classList.toggle('hidden', !isCod || total <= 0);
                    if (optBadge) optBadge.classList.toggle('hidden', isCod && total > 0);
                });

                if (paymentContainer) {
                    const radios = paymentContainer.querySelectorAll('input[name="payment_method"]');
                    if (total <= 0) {
                        paymentContainer.classList.add('hidden');
                        radios.forEach(radio => {
                            radio.required = false;
                            radio.checked = false;
                        });
                    } else {
                        paymentContainer.classList.remove('hidden');
                        let checkedAny = false;
                        radios.forEach(radio => {
                            radio.required = true;
                            if (radio.checked) checkedAny = true;
                        });
                        if (!checkedAny && radios.length > 0) {
                            radios[0].checked = true;
                        }
                    }
                }
            }

            if (couponApplyBtn) couponApplyBtn.addEventListener('click', function () { applyCoupon(); });
            if (couponClearBtn) couponClearBtn.addEventListener('click', function () { clearCoupon(''); updateSummary(); });
            if (couponInputEl) couponInputEl.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyCoupon();
                }
            });

            function normalizePhone(phone) {
                return String(phone || '').replace(/[^0-9]/g, '');
            }

            function compressImageFile(file, maxDimension, quality, callback) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    const img = new Image();
                    img.onload = function() {
                        let width = img.width;
                        let height = img.height;

                        if (width > maxDimension || height > maxDimension) {
                            if (width > height) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            } else {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        const compressedDataUrl = canvas.toDataURL('image/jpeg', quality || 0.8);
                        callback(compressedDataUrl);
                    };
                    img.onerror = function() {
                        callback(evt.target.result);
                    };
                    img.src = evt.target.result;
                };
                reader.onerror = function() {
                    alert('Gagal membaca file foto.');
                };
                reader.readAsDataURL(file);
            }

            function setupPhotoUploadListeners(item) {
                const fileInput = item.querySelector('.photo-file-input');
                const photoHidden = item.querySelector('input[data-field="photo"]');
                const previewImg = item.querySelector('.photo-preview-img');
                const placeholderIcon = item.querySelector('.photo-placeholder-icon');
                const clearBtn = item.querySelector('.photo-clear-btn');
                const cameraBtn = item.querySelector('.photo-camera-btn');

                if (!fileInput || !photoHidden) return;

                fileInput.addEventListener('change', function(e) {
                    const file = e.target.files?.[0];
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        alert('File yang dipilih harus berupa gambar (JPG, PNG, WebP).');
                        return;
                    }

                    if (file.size > 15 * 1024 * 1024) {
                        alert('Ukuran file maksimal 15MB.');
                        return;
                    }

                    if (placeholderIcon) {
                        placeholderIcon.className = 'fa-solid fa-spinner fa-spin text-theme-primary text-lg photo-placeholder-icon';
                    }

                    compressImageFile(file, 1000, 0.8, function(compressedDataUrl) {
                        photoHidden.value = compressedDataUrl;
                        if (previewImg) {
                            previewImg.src = compressedDataUrl;
                            previewImg.classList.remove('hidden');
                        }
                        if (placeholderIcon) {
                            placeholderIcon.className = 'fa-solid fa-camera text-slate-400 text-lg photo-placeholder-icon';
                            placeholderIcon.classList.add('hidden');
                        }
                        if (clearBtn) clearBtn.classList.remove('hidden');
                    });
                });

                if (cameraBtn) {
                    cameraBtn.addEventListener('click', function() {
                        openWebcamModal(item);
                    });
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', function() {
                        photoHidden.value = '';
                        fileInput.value = '';
                        if (previewImg) {
                            previewImg.src = '';
                            previewImg.classList.add('hidden');
                        }
                        if (placeholderIcon) {
                            placeholderIcon.className = 'fa-solid fa-camera text-slate-400 text-lg photo-placeholder-icon';
                            placeholderIcon.classList.remove('hidden');
                        }
                        clearBtn.classList.add('hidden');
                    });
                }
            }

            function reindexParticipants() {
                getParticipantItems().forEach(function(item, index) {
                    const title = item.querySelector('.participant-title');
                    if (title) title.textContent = 'Peserta ' + (index + 1);

                    item.querySelectorAll('[data-field]').forEach(function(el) {
                        const field = el.dataset.field;
                        el.name = 'participants[' + index + '][' + field + ']';
                    });

                    item.querySelectorAll('[data-hidden-auto]').forEach(function(el) {
                        const field = el.dataset.hiddenAuto;
                        el.name = 'participants[' + index + '][' + field + ']';
                    });

                    item.querySelectorAll('[data-addon-index]').forEach(function(el) {
                        const addonIndex = el.dataset.addonIndex;
                        el.name = 'participants[' + index + '][addons][' + addonIndex + '][selected]';
                    });

                    item.querySelectorAll('[data-addon-name]').forEach(function(el) {
                        const addonIndex = el.dataset.addonName;
                        el.name = 'participants[' + index + '][addons][' + addonIndex + '][name]';
                    });

                    item.querySelectorAll('[data-addon-price-hidden]').forEach(function(el) {
                        const addonIndex = el.dataset.addonPriceHidden;
                        el.name = 'participants[' + index + '][addons][' + addonIndex + '][price]';
                    });

                    setupPhotoUploadListeners(item);

                    const removeBtn = item.querySelector('.remove-participant');
                    if (removeBtn) {
                        removeBtn.classList.toggle('hidden', getParticipantItems().length === 1);
                    }
                });
            }

            function createParticipantItem() {
                const fragment = participantTemplate.content.cloneNode(true);
                const item = fragment.querySelector('[data-participant-item]');
                participantsWrapper.appendChild(fragment);
                reindexParticipants();
                updateSummary();
                return item;
            }

            function fillHiddenFields() {
                const items = getParticipantItems();
                const first = items[0];
                const firstName = first?.querySelector('[data-field="name"]')?.value?.trim() || '';
                const firstEmail = first?.querySelector('[data-field="email"]')?.value?.trim() || '';
                const firstPhone = normalizePhone(first?.querySelector('[data-field="phone"]')?.value || '');

                form.querySelector('[name="pic_name"]').value = firstName;
                form.querySelector('[name="pic_email"]').value = firstEmail;
                form.querySelector('[name="pic_phone"]').value = firstPhone;

                items.forEach(function(item, index) {
                    const name = item.querySelector('[data-field="name"]')?.value?.trim() || '';
                    const email = item.querySelector('[data-field="email"]')?.value?.trim() || '';
                    const phone = normalizePhone(item.querySelector('[data-field="phone"]')?.value || '');
                    const categoryId = item.querySelector('[data-field="category_id"]:checked')?.value || '0';
                    const identity = ('QL-' + phone + '-' + email.toLowerCase() + '-' + categoryId + '-' + index).replace(/[^a-zA-Z0-9@._-]/g, '').slice(0, 50) || ('QL-' + Date.now() + '-' + index);
                    
                    const idCardEl = item.querySelector('[data-hidden-auto="id_card"]');
                    if (idCardEl) idCardEl.value = identity;

                    const addressEl = item.querySelector('[data-hidden-auto="address"]');
                    if (addressEl) addressEl.value = '-';

                    const emNameEl = item.querySelector('[data-hidden-auto="emergency_contact_name"]');
                    if (emNameEl) emNameEl.value = name || 'Kontak Darurat';

                    const emPhoneEl = item.querySelector('[data-hidden-auto="emergency_contact_number"]');
                    if (emPhoneEl) emPhoneEl.value = phone || '0811111111';

                    const dobEl = item.querySelector('[data-hidden-auto="date_of_birth"]');
                    if (dobEl) dobEl.value = '';

                    const targetTimeField = item.querySelector('[data-field="target_time"]');
                    if (targetTimeField) {
                        const targetHour = item.querySelector('[data-target-hour]')?.value || '00';
                        const targetMinute = item.querySelector('[data-target-minute]')?.value || '30';
                        const targetSecond = item.querySelector('[data-target-second]')?.value || '00';
                        targetTimeField.value = targetHour + ':' + targetMinute + ':' + targetSecond;
                    } else {
                        const targetTimeHidden = item.querySelector('[data-hidden-auto="target_time"]');
                        if (targetTimeHidden) targetTimeHidden.value = '';
                    }
                });
            }

            function setSubmittingState(isSubmitting) {
                if (!submitBtn) return;
                submitBtn.disabled = isSubmitting;
                submitBtn.innerHTML = isSubmitting ? '<i class="fa-solid fa-spinner fa-spin text-white"></i><span>Memproses...</span>' : originalSubmitText;
            }

            function handleSuccess(data) {
                if (!data || !data.success) {
                    throw new Error((data && data.message) || 'Pendaftaran gagal diproses.');
                }

                // 1. Free Registration with Pending Approval (Challenge / Curated Selection)
                if (data.payment_gateway === 'free' && data.payment_status === 'pending') {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    window.location.href = `{{ route('events.show', $event->slug) }}?payment=approval_pending`;
                    return;
                }

                // 2. Direct success for Free Confirmed / Paid Registration (Never trigger payment modal)
                if (data.payment_status === 'paid' || (data.payment_gateway === 'free' && data.payment_status !== 'pending') || (Number(data.final_amount || 0) <= 0 && !data.snap_token && data.payment_gateway !== 'cod')) {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    window.location.href = `{{ route('events.show', $event->slug) }}?payment=success`;
                    return;
                }

                // 2. COD
                if (data.payment_gateway === 'cod') {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    window.location.href = `{{ route('events.show', $event->slug) }}?payment=cod_pending`;
                    return;
                }

                // 3. Midtrans Snap
                if (data.snap_token) {
                    const qs = new URLSearchParams();
                    if (data.transaction_id) qs.set('tx', String(data.transaction_id));
                    if (data.registration_id) qs.set('ref', String(data.registration_id));

                    if (typeof window.snap !== 'undefined' && typeof window.snap.pay === 'function') {
                        window.snap.pay(data.snap_token, {
                            onSuccess: function() { window.location.href = `{{ route('events.show', $event->slug) }}?payment=success&` + qs.toString(); },
                            onPending: function() { window.location.href = `{{ route('events.show', $event->slug) }}?payment=pending&` + qs.toString(); },
                            onError: function() {
                                alert('Pembayaran gagal diproses.');
                                setSubmittingState(false);
                            },
                            onClose: function() {
                                window.location.href = `{{ route('events.show', $event->slug) }}?payment=pending&` + qs.toString();
                            }
                        });
                        return;
                    } else if (data.payment_url || data.redirect_url) {
                        window.location.href = data.payment_url || data.redirect_url;
                        return;
                    } else {
                        alert('Modul pembayaran Midtrans gagal dimuat di browser kamu. Pastikan koneksi internet lancar dan nonaktifkan adblocker/shield jika aktif.');
                        setSubmittingState(false);
                        window.location.href = `{{ route('events.show', $event->slug) }}?payment=pending&` + qs.toString();
                        return;
                    }
                }

                // 4. Moota (HANYA jika payment_gateway secara eksplisit adalah 'moota' dan belum paid)
                if (data.payment_gateway === 'moota' && window.RuangLariMoota && typeof window.RuangLariMoota.open === 'function' && data.transaction_id) {
                    window.RuangLariMoota.open({
                        transaction_id: data.transaction_id,
                        registration_id: data.registration_id,
                        final_amount: data.final_amount,
                        unique_code: data.unique_code,
                        phone: form.querySelector('[name="pic_phone"]').value || '',
                        name: form.querySelector('[name="pic_name"]').value || '',
                        type: 'transaction'
                    });
                    setSubmittingState(false);
                    return;
                }

                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }

                window.location.href = `{{ route('events.show', $event->slug) }}?payment=success`;
            }

            function processSubmission() {
                const selectedMethod = form.querySelector('input[name="payment_method"]:checked')?.value || '';
                const items = getParticipantItems();

                // Validate COD photo requirement
                if (selectedMethod === 'cod') {
                    for (let i = 0; i < items.length; i++) {
                        const item = items[i];
                        const photoVal = item.querySelector('input[data-field="photo"]')?.value?.trim();
                        const pName = item.querySelector('[data-field="name"]')?.value?.trim() || ('Peserta ' + (i + 1));
                        if (!photoVal) {
                            alert('Untuk metode Bayar di Tempat (COD), ' + pName + ' wajib mengunggah/mengambil foto wajah atau identitas.');
                            const photoContainer = item.querySelector('[data-photo-container]');
                            if (photoContainer) {
                                photoContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                photoContainer.classList.add('ring-2', 'ring-orange-500');
                                setTimeout(() => photoContainer.classList.remove('ring-2', 'ring-orange-500'), 3000);
                            }
                            return;
                        }
                    }
                }

                fillHiddenFields();
                setSubmittingState(true);

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async function(response) {
                    if (response.redirected && response.url) {
                        window.location.href = response.url;
                        return;
                    }

                    let data = null;
                    let rawText = '';
                    try {
                        rawText = await response.text();
                        if (rawText) {
                            data = JSON.parse(rawText);
                        }
                    } catch (e) {
                        if (rawText && rawText.includes('{') && rawText.includes('}')) {
                            try {
                                const jsonStart = rawText.indexOf('{');
                                const jsonEnd = rawText.lastIndexOf('}') + 1;
                                data = JSON.parse(rawText.slice(jsonStart, jsonEnd));
                            } catch (e2) {}
                        }
                    }

                    if (!response.ok) {
                        if (data && data.message) {
                            throw new Error(data.message);
                        }
                        if (data && data.errors) {
                            const first = Object.values(data.errors)[0];
                            throw new Error(Array.isArray(first) ? first[0] : 'Data pendaftaran belum lengkap.');
                        }
                        if (response.status === 419) {
                            throw new Error('Sesi pendaftaran telah berakhir (CSRF expired). Silakan refresh halaman.');
                        }
                        if (response.status === 422) {
                            throw new Error('Data pendaftaran belum lengkap atau tidak valid.');
                        }
                        if (response.status === 429) {
                            throw new Error('Terlalu banyak percobaan pendaftaran. Silakan tunggu 1 menit lalu coba lagi.');
                        }
                        throw new Error('Terjadi kesalahan pada server (Status ' + response.status + ').');
                    }

                    if (!data) {
                        if (rawText && (rawText.includes('<!DOCTYPE') || rawText.includes('<html'))) {
                            if (rawText.includes('payment=success') || rawText.includes('Menunggu Pembayaran')) {
                                window.location.reload();
                                return;
                            }
                            throw new Error('Sesi pendaftaran berakhir atau terjadi pengalihan halaman. Silakan refresh dan coba lagi.');
                        }
                        throw new Error('Respons server tidak valid atau kosong.');
                    }
                    return data;
                })
                .then(handleSuccess)
                .catch(function(err) {
                    if (err) {
                        alert(err.message || 'Gagal menghubungi server.');
                    }
                    setSubmittingState(false);
                });
            }

            if (addParticipantBtn) {
                addParticipantBtn.addEventListener('click', function() {
                    const item = createParticipantItem();
                    const firstInput = item ? item.querySelector('[data-field="name"]') : null;
                    if (firstInput) firstInput.focus();
                });
            }

            participantsWrapper.addEventListener('click', function(e) {
                const removeBtn = e.target.closest('.remove-participant');
                if (!removeBtn) return;
                const item = removeBtn.closest('[data-participant-item]');
                if (!item) return;
                item.remove();
                reindexParticipants();
                updateSummary();
            });

            participantsWrapper.addEventListener('change', function(e) {
                const target = e.target;
                if (target.matches('[data-target-hour], [data-target-minute], [data-target-second]')) {
                    const card = target.closest('[data-participant-item]');
                    if (card) {
                        const h = card.querySelector('[data-target-hour]')?.value || '00';
                        const m = card.querySelector('[data-target-minute]')?.value || '30';
                        const s = card.querySelector('[data-target-second]')?.value || '00';
                        const hiddenInput = card.querySelector('input[data-field="target_time"]') || card.querySelector('input[data-hidden-auto="target_time"]');
                        if (hiddenInput) {
                            hiddenInput.value = h + ':' + m + ':' + s;
                        }
                    }
                }
            });

            form.addEventListener('change', function(e) {
                if (e.target.matches('input[data-field="category_id"], input[data-addon-price], input[name="payment_method"]')) {
                    updateSummary();
                }
            });

            form.addEventListener('input', function(e) {
                if (e.target.matches('[data-field="phone"]')) {
                    e.target.value = normalizePhone(e.target.value).slice(0, 15);
                }
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                @if(env('RECAPTCHA_SITE_KEY_v3'))
                    if (typeof grecaptcha === 'undefined') {
                        alert('Gagal memuat reCAPTCHA. Silakan refresh halaman atau coba jaringan lain.');
                        return;
                    }
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ env('RECAPTCHA_SITE_KEY_v3') }}', { action: 'event_register' }).then(function(token) {
                            let input = document.getElementById('recaptchaToken');
                            if (!input) {
                                input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'g-recaptcha-response';
                                input.id = 'recaptchaToken';
                                form.appendChild(input);
                            }
                            input.value = token;
                            processSubmission();
                        }).catch(function() {
                            alert('Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
                        });
                    });
                    return;
                @endif
                processSubmission();
            });

            reindexParticipants();
            updateSummary();
        })();
    </script>

    @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
        <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js'"></script>
        <script>
            (function () {
                if (!window.Vue) return;
                const { createApp } = Vue;
                const vueApp = createApp({});
                if (typeof ParticipantsTableComponent !== 'undefined') {
                    vueApp.component('participants-table', ParticipantsTableComponent);
                }
                const mountEl = document.getElementById('vue-participants-app');
                if (mountEl) vueApp.mount(mountEl);
            })();
        </script>
    @endif
</body>
</html>
