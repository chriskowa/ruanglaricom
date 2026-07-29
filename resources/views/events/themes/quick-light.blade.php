@php
    $paymentConfig = $event->payment_config ?? [];
    if (isset($paymentConfig['allowed_methods']) && is_array($paymentConfig['allowed_methods'])) {
        $allowed = $paymentConfig['allowed_methods'];
        $showMidtrans = in_array('midtrans', $allowed) || in_array('all', $allowed);
        $showMoota = in_array('moota', $allowed) || in_array('all', $allowed);
        $showCOD = in_array('cod', $allowed) || in_array('all', $allowed);
    } elseif (isset($paymentConfig['allowed_gateways']) && is_array($paymentConfig['allowed_gateways'])) {
        $allowed = $paymentConfig['allowed_gateways'];
        $showMidtrans = in_array('midtrans', $allowed) || in_array('all', $allowed);
        $showMoota = in_array('moota', $allowed) || in_array('all', $allowed);
        $showCOD = in_array('cod', $allowed) || in_array('all', $allowed);
    } else {
        $showMidtrans = $paymentConfig['midtrans'] ?? true;
        $showMoota = $paymentConfig['moota'] ?? false;
        $showCOD = $paymentConfig['cod'] ?? true;
    }
    if (!$showMidtrans && !$showMoota && !$showCOD) {
        $showMidtrans = true;
    }
    $midtransDemoMode = filter_var($paymentConfig['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
    $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');
    $categories = $categories ?? ($event->categories ?? collect());
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
    $faviconImage = $logoImage ?? asset('favicon.ico');
    $canonicalUrl = route('events.show', $event->slug);
    $rawShortDescription = (string) ($event->short_description ?? '');
    $rawShortDescription = html_entity_decode($rawShortDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $rawShortDescription = str_replace(["\u{00A0}", "\xc2\xa0"], ' ', $rawShortDescription);
    $rawShortDescription = str_replace('&nbsp;', ' ', $rawShortDescription);
    $shortDescription = trim(preg_replace('/\s+/u', ' ', strip_tags($rawShortDescription)));
    $platformFee = (int) ($event->platform_fee ?? 0);
    $defaultJersey = collect($event->jersey_sizes ?? [])->filter()->first() ?? 'L';
    $formFields = $event->premium_amenities['form_fields'] ?? [];
    $showIdCard = !empty($formFields['id_card']);
    $showAddress = !empty($formFields['address']);
    $showEmergency = !empty($formFields['emergency_contact']);
    $showDob = !empty($formFields['date_of_birth']);
    $showJersey = !empty($formFields['jersey_size']);
    $showTargetTime = !empty($formFields['target_time']);
    $showBloodType = !empty($formFields['blood_type']);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css" rel="stylesheet" />
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if($showMidtrans && $midtransClientKey)
        <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
    @endif
    @if(env('RECAPTCHA_SITE_KEY_v3'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}" onerror="this.onerror=null;this.src='https://www.recaptcha.net/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}';"></script>
    @endif
    <style>
        :root { 
            --primary: #f1631e; 
            --primary-dark: #d94f0b; 
            --ink: #0f172a; 
            --muted: #64748b; 
            --line: #e2e8f0; 
            --bg: #f8fafc; 
        }
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
            font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, sans-serif; 
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
            border-radius: 20px;
            box-shadow: 0 4px 20px -5px rgba(15, 23, 42, 0.05);
            max-width: 100%;
            overflow: hidden;
        }
        .no-scrollbar { scrollbar-width: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .field { 
            width: 100%; 
            border: 1.5px solid #e2e8f0; 
            border-radius: 14px; 
            padding: 12px 16px; 
            font-weight: 600; 
            font-size: 0.9rem;
            color: #0f172a; 
            background: #ffffff; 
            outline: none; 
            transition: border-color 0.2s ease, box-shadow 0.2s ease; 
        }
        .field:focus { 
            border-color: #f1631e; 
            box-shadow: 0 0 0 3px rgba(241, 99, 30, 0.12); 
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
            border-radius: 14px; 
            padding: 14px 16px; 
            background: #ffffff; 
            transition: all 0.2s ease; 
        }
        .choice input:checked + .choice-box { 
            border-color: #f1631e; 
            background: #fff8f5; 
            box-shadow: 0 0 0 3px rgba(241, 99, 30, 0.1); 
        }
        .submit-btn[disabled] { opacity: 0.65; cursor: not-allowed; }
        .content-html p { margin-top: 0; margin-bottom: 1rem; line-height: 1.75; color: #334155; font-size: 0.925rem; }
        .content-html h1, .content-html h2, .content-html h3, .content-html h4 { color: #0f172a; font-weight: 800; margin-top: 1.5rem; margin-bottom: 0.75rem; }
        .content-html ul, .content-html ol { padding-left: 1.25rem; color: #334155; margin-bottom: 1rem; }
        .content-html table { display: block; width: 100%; overflow-x: auto; }
        .content-html img, .content-html iframe { max-width: 100%; height: auto; }
        .ql-gallery-dot { width: 8px; height: 8px; border-radius: 999px; background: rgba(15,23,42,0.2); transition: transform 0.2s ease, background 0.2s ease; }
        .ql-gallery-dot[data-active="1"] { background: #f1631e; transform: scale(1.25); }
        #qlSponsorDots .ql-gallery-dot { background: rgba(15,23,42,0.2); }
        #qlSponsorDots .ql-gallery-dot[data-active="1"] { background: #f1631e; }
        i { color: #ffffff !important; }
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
        <div class="relative z-10 w-full max-w-7xl mx-auto px-3.5 sm:px-6 md:px-8 pt-6 sm:pt-8 pb-12 sm:pb-16 lg:pb-20 my-auto">
            <div class="grid lg:grid-cols-[1.15fr_.85fr] gap-6 lg:gap-12 items-end">
                <div class="text-white space-y-3.5 sm:space-y-4 min-w-0">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <span class="pill-badge bg-slate-900/80 text-white border border-slate-700 text-[10px] sm:text-xs">
                            <i class="fa-regular fa-calendar text-white shrink-0"></i>
                            {{ optional($event->start_at)->format('d F Y') ?: 'Tanggal menyusul' }}
                        </span>
                        <span class="pill-badge bg-slate-900/80 text-white border border-slate-700 text-[10px] sm:text-xs max-w-full truncate">
                            <i class="fa-solid fa-location-dot text-white shrink-0"></i>
                            <span class="truncate">{{ $event->location_name ?: 'Lokasi menyusul' }}</span>
                        </span>
                        <span class="pill-badge {{ $isRegOpen ? 'bg-emerald-950/90 text-emerald-300 border-emerald-800' : 'bg-rose-950/90 text-rose-300 border-rose-800' }} text-[10px] sm:text-xs">
                            <span class="w-1.5 h-1.5 rounded-full {{ $isRegOpen ? 'bg-emerald-400' : 'bg-rose-400' }} shrink-0"></span>
                            {{ $isRegOpen ? 'Pendaftaran Dibuka' : 'Ditutup' }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-4xl lg:text-5xl font-black leading-tight tracking-tight text-white break-words">
                        {{ $event->name }}
                    </h1>

                    <p class="text-xs sm:text-base text-slate-200 leading-relaxed max-w-2xl font-normal break-words">
                        {{ $shortDescription !== '' ? $shortDescription : 'Sistem pendaftaran cepat resmi Ruang Lari. Isi data diri dengan cepat, pilih kategori, dan dapatkan konfirmasi e-Tiket otomatis.' }}
                    </p>

                    <div class="pt-1 sm:pt-2 flex flex-wrap gap-2.5 sm:gap-3">
                        <a href="#register" class="inline-flex items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl bg-[#f1631e] hover:bg-[#d94f0b] text-white font-extrabold shadow-md transition-colors text-xs sm:text-sm">
                            <i class="fa-solid fa-bolt text-white"></i>
                            Daftar Sekarang
                        </a>
                        <a href="#detail-event" class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2.5 sm:py-3 rounded-xl bg-slate-800/90 hover:bg-slate-700 border border-slate-700 text-white font-bold transition-colors text-xs sm:text-sm shadow-sm">
                            <i class="fa-solid fa-circle-info text-white"></i>
                            Informasi Detail
                        </a>
                    </div>
                </div>

                <!-- Hero Stat Badges Card -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5 shadow-xl max-w-full overflow-hidden">
                    <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                        <div class="rounded-xl bg-slate-950 p-3 sm:p-3.5 border border-slate-800 min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-person-running text-white shrink-0"></i> Event Type
                            </div>
                            <div class="mt-1.5 text-xs font-bold text-white truncate">
                                Running Event
                            </div>
                        </div>
                        <div class="rounded-xl bg-slate-950 p-3 sm:p-3.5 border border-slate-800 min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-ticket text-white shrink-0"></i> e-Tiket
                            </div>
                            <div class="mt-1.5 text-xs font-bold text-white truncate">Langsung di Email</div>
                        </div>
                        <div class="rounded-xl bg-slate-950 p-3 sm:p-3.5 border border-slate-800 min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-layer-group text-white shrink-0"></i> Kategori
                            </div>
                            <div class="mt-1.5 text-xs font-bold text-white truncate">{{ $categories->count() }} Pilihan Jarak</div>
                        </div>
                        <div class="rounded-xl bg-slate-950 p-3 sm:p-3.5 border border-slate-800 min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                <i class="fa-solid fa-shield-check text-white shrink-0"></i> Status
                            </div>
                            <div class="mt-1.5 text-xs font-bold {{ $isRegOpen ? 'text-emerald-400' : 'text-rose-400' }} truncate">
                                {{ $isRegOpen ? 'Sedang Dibuka' : 'Pendaftaran Ditutup' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="w-full max-w-full overflow-hidden bg-slate-50 text-slate-900 min-h-screen py-8 sm:py-10 relative z-10">
        <div class="max-w-7xl mx-auto px-3.5 sm:px-6 md:px-8">

            <!-- Sponsor Carousel Banner -->
            @if(count($sponsorUrls) > 0)
                <section class="pro-card p-4 sm:p-5 mb-6 sm:mb-8">
                    <div class="flex items-center justify-between gap-4 pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-slate-900"></div>
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Official Sponsors</div>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" id="qlSponsorPrev" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-center text-white hover:bg-slate-800 transition-colors" aria-label="Sebelumnya">
                                <i class="fa-solid fa-chevron-left text-xs text-white"></i>
                            </button>
                            <button type="button" id="qlSponsorNext" class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 shadow-sm flex items-center justify-center text-white hover:bg-slate-800 transition-colors" aria-label="Berikutnya">
                                <i class="fa-solid fa-chevron-right text-xs text-white"></i>
                            </button>
                        </div>
                    </div>

                    <div id="qlSponsorTrack" class="mt-3.5 flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-1 no-scrollbar max-w-full">
                        @foreach($sponsorUrls as $logo)
                            <div class="snap-center flex-none w-full sm:w-1/2 md:w-1/3 lg:w-1/4">
                                <div class="h-16 rounded-xl border border-slate-200 bg-white flex items-center justify-center p-3">
                                    <img src="{{ $logo }}" alt="Sponsor {{ $event->name }}" class="max-h-full max-w-full object-contain" loading="lazy" decoding="async">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="qlSponsorDots" class="mt-2.5 flex items-center justify-center gap-1.5"></div>
                </section>
            @endif

            <!-- Payment Notification Badges -->
            @if(request('payment') === 'pending')
                <div class="pro-card p-4 sm:p-5 mb-6 sm:mb-8 border-amber-300 bg-amber-50">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center text-lg shrink-0"><i class="fa-solid fa-clock text-white"></i></div>
                        <div>
                            <div class="text-base font-bold text-slate-900">Menunggu Pembayaran</div>
                            <div class="mt-0.5 text-xs text-slate-600">Transaksi kamu belum selesai. Silakan klik tombol di bawah untuk melanjutkan pembayaran.</div>
                            <a href="{{ route('events.payments.continue', $event->slug) }}" class="inline-flex mt-3 items-center gap-2 px-4 py-2 rounded-lg bg-amber-600 text-white font-bold hover:bg-amber-700 transition-colors text-xs">
                                Lanjutkan Pembayaran <i class="fa-solid fa-arrow-right text-white"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @elseif(request('payment') === 'success')
                <div class="pro-card p-4 sm:p-5 mb-6 sm:mb-8 border-emerald-300 bg-emerald-50">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shrink-0"><i class="fa-solid fa-circle-check text-white"></i></div>
                        <div>
                            <div class="text-base font-bold text-slate-900">Pendaftaran Berhasil</div>
                            <div class="mt-0.5 text-xs text-slate-600">Terima kasih. Cek email kamu untuk informasi konfirmasi e-Tiket dan rincian transaksi.</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 2 Column Layout: Event Details & Sticky Form -->
            <div class="grid lg:grid-cols-[1.05fr_.95fr] gap-6 lg:gap-8 items-start max-w-full">
                
                <!-- LEFT COLUMN: Overview, Description, Route & Details -->
                <section id="detail-event" class="space-y-6 w-full max-w-full overflow-hidden">
                    
                    <!-- Overview Card -->
                    <div class="pro-card overflow-hidden">
                        <div class="grid md:grid-cols-[1.1fr_.9fr]">
                            <div class="p-5 sm:p-6">
                                <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-md bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                                        <i class="fa-solid fa-circle-info text-white"></i>
                                    </span>
                                    Event Overview
                                </div>
                                <h2 class="mt-2 text-lg sm:text-xl font-bold text-slate-900">Informasi Pelaksanaan</h2>
                                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                                        <div class="text-[10px] font-bold uppercase text-slate-400">Tanggal</div>
                                        <div class="mt-1 text-xs font-bold text-slate-900">{{ optional($event->start_at)->format('d M Y') ?: '-' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3">
                                        <div class="text-[10px] font-bold uppercase text-slate-400">Jam Mulai</div>
                                        <div class="mt-1 text-xs font-bold text-slate-900">{{ optional($event->start_at)->format('H:i') ?: '-' }} WIB</div>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 border border-slate-200 p-3 col-span-2 sm:col-span-1">
                                        <div class="text-[10px] font-bold uppercase text-slate-400">Lokasi</div>
                                        <div class="mt-1 text-xs font-bold text-slate-900 truncate">{{ $event->location_name ?: '-' }}</div>
                                    </div>
                                </div>
                                @if($event->location_address)
                                    <div class="mt-3.5 rounded-xl bg-slate-100 border border-slate-200 p-3.5 text-xs text-slate-700 leading-relaxed">
                                        <div class="font-bold text-slate-900 mb-0.5 flex items-center gap-2">
                                            <span class="w-5 h-5 rounded bg-slate-900 text-white inline-flex items-center justify-center text-[10px]">
                                                <i class="fa-solid fa-map-pin text-white"></i>
                                            </span>
                                            Alamat Lengkap
                                        </div>
                                        {{ $event->location_address }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Gallery Carousel -->
                            <div class="min-h-[220px] sm:min-h-[240px] relative bg-slate-900 max-w-full overflow-hidden" id="qlGallery" data-images='@json($galleryUrls)'>
                                <img id="qlGalleryImg" src="{{ $galleryUrls[0] }}" alt="{{ $event->name }}" class="w-full h-full object-cover" fetchpriority="high" decoding="async">
                                <button type="button" id="qlGalleryPrev" class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 shadow flex items-center justify-center text-white transition-colors">
                                    <i class="fa-solid fa-chevron-left text-xs text-white"></i>
                                </button>
                                <button type="button" id="qlGalleryNext" class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-slate-900 border border-slate-800 shadow flex items-center justify-center text-white transition-colors">
                                    <i class="fa-solid fa-chevron-right text-xs text-white"></i>
                                </button>
                                <div id="qlGalleryDots" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-950/80 border border-white/20"></div>
                            </div>
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
                    
                    <!-- Participants List Table (Vue Component) -->
                    @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
                        <div class="pro-card p-5 sm:p-6 max-w-full overflow-hidden" id="participants-list">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-900 flex items-center gap-2 mb-3">
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
                </section>

                <!-- RIGHT COLUMN: Sticky Registration Card -->
                <aside id="register" class="lg:sticky lg:top-24 self-start w-full max-w-full">
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
                                                                        <div class="text-right shrink-0">
                                                                            @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                                <div class="text-[10px] text-slate-400 line-through">Rp {{ number_format($priceRegular, 0, ',', '.') }}</div>
                                                                            @endif
                                                                            <div class="text-xs font-bold text-orange-600">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                                                                        </div>
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
                                                    <input type="hidden" data-hidden-auto="date_of_birth" name="participants[0][date_of_birth]">
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
                                                    <input type="hidden" data-hidden-auto="jersey_size" name="participants[0][jersey_size]" value="{{ $defaultJersey }}">
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
                                                                        <div class="text-right shrink-0">
                                                                            @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                                <div class="text-[10px] text-slate-400 line-through">Rp {{ number_format($priceRegular, 0, ',', '.') }}</div>
                                                                            @endif
                                                                            <div class="text-xs font-bold text-orange-600">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                                                                        </div>
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
                                                    <input type="hidden" data-hidden-auto="id_card">
                                                @endif

                                                @if($showAddress)
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Alamat Lengkap</label>
                                                        <textarea class="field mt-1" data-field="address" placeholder="Alamat lengkap" rows="2" required></textarea>
                                                    </div>
                                                @else
                                                    <input type="hidden" data-hidden-auto="address">
                                                @endif

                                                @if($showEmergency)
                                                    <div class="grid sm:grid-cols-2 gap-3">
                                                        <div>
                                                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Nama Kontak Darurat</label>
                                                            <input class="field mt-1" data-field="emergency_contact_name" placeholder="Nama kontak" required>
                                                        </div>
                                                        <div>
                                                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">No. HP Darurat</label>
                                                            <input class="field mt-1" data-field="emergency_contact_number" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                        </div>
                                                    </div>
                                                @else
                                                    <input type="hidden" data-hidden-auto="emergency_contact_name">
                                                    <input type="hidden" data-hidden-auto="emergency_contact_number">
                                                @endif

                                                @if($showDob)
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Tanggal Lahir</label>
                                                        <input type="date" class="field mt-1" data-field="date_of_birth" required>
                                                    </div>
                                                @else
                                                    <input type="hidden" data-hidden-auto="date_of_birth">
                                                @endif

                                                @if($showJersey)
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Ukuran Jersey</label>
                                                        <select class="field mt-1" data-field="jersey_size" required>
                                                            <option value="">Pilih ukuran jersey</option>
                                                            @foreach($event->jersey_sizes ?? [] as $sz)
                                                                <option value="{{ $sz }}">{{ $sz }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @else
                                                    <input type="hidden" data-hidden-auto="jersey_size" value="{{ $defaultJersey }}">
                                                @endif

                                                @if($showTargetTime)
                                                    <div>
                                                        <label class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Target Time</label>
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
                                                        <input type="hidden" data-field="target_time" value="00:30:00">
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
                                                            <div class="text-xs font-bold text-slate-900 truncate">Midtrans Payment Gateway</div>
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
                                                            <div class="mt-0.5 text-[10px] text-slate-500 truncate">Bayar tunai sesuai petunjuk panitia EO</div>
                                                        </div>
                                                        <span class="w-7 h-7 rounded-lg bg-slate-900 text-white inline-flex items-center justify-center text-xs shrink-0">
                                                            <i class="fa-solid fa-money-bill-wave text-white"></i>
                                                        </span>
                                                    </div>
                                                </label>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Coupon Input Box -->
                                    <div class="rounded-xl border border-slate-200 bg-white p-3.5">
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
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-2 text-xs">
                                        <div class="flex items-center justify-between text-slate-600">
                                            <span>Subtotal Tiket</span>
                                            <span id="ql-ticket-price" class="font-bold text-slate-900">Rp 0</span>
                                        </div>
                                        <div class="flex items-center justify-between text-slate-600">
                                            <span>Add-on</span>
                                            <span id="ql-addon-price" class="font-bold text-slate-900">Rp 0</span>
                                        </div>
                                        <div id="qlCouponRow" class="hidden flex items-center justify-between text-slate-600">
                                            <span>Diskon Kupon <span id="qlCouponLabel" class="font-bold text-slate-700"></span></span>
                                            <span id="qlDiscountAmount" class="font-bold text-emerald-600">-Rp 0</span>
                                        </div>
                                        <div class="flex items-center justify-between text-slate-600">
                                            <span>Biaya Layanan (Platform Fee)</span>
                                            <span id="ql-platform-fee" class="font-bold text-slate-900">Rp {{ number_format($platformFee, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="pt-2.5 border-t border-slate-200 flex items-center justify-between">
                                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Total Pembayaran</span>
                                            <span id="ql-total-price" class="text-lg font-bold text-orange-600">Rp 0</span>
                                        </div>
                                    </div>

                                    <input type="hidden" name="pic_name">
                                    <input type="hidden" name="pic_email">
                                    <input type="hidden" name="pic_phone">

                                    <button id="quickSubmitBtn" type="submit" class="submit-btn w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-[#f1631e] hover:bg-[#d94f0b] text-white font-bold text-sm shadow transition-colors">
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
        </div>
    </main>

    @include('events.partials.moota-payment-modal')

    <!-- Route Map Modal -->
    <div id="routeModal" class="fixed inset-0 z-[999] hidden">
        <div class="absolute inset-0 bg-slate-900/60" onclick="closeRouteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-4xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold">
                            <i class="fa-solid fa-map-location-dot text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-sm text-slate-900">Peta Rute Lari</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $event->name }}</p>
                        </div>
                    </div>
                    <button onclick="closeRouteModal()" class="w-8 h-8 rounded-lg bg-slate-900 hover:bg-slate-800 flex items-center justify-center text-white transition-colors">
                        <i class="fa-solid fa-xmark text-white"></i>
                    </button>
                </div>
                
                @php
                    $gpxList = collect();
                    if (isset($categories)) {
                        foreach($categories as $category) {
                            if ($category->masterGpx && $category->masterGpx->is_published && $category->masterGpx->gpx_path) {
                                $gpxList->put($category->masterGpx->id, (object)[
                                    'id' => $category->masterGpx->id,
                                    'title' => $category->name,
                                    'gpx_path' => $category->masterGpx->gpx_path,
                                    'distance_km' => $category->masterGpx->distance_km ?: $category->distance_km,
                                    'elevation_gain_m' => $category->masterGpx->elevation_gain_m,
                                    'elevation_loss_m' => $category->masterGpx->elevation_loss_m,
                                ]);
                            }
                        }
                    }
                    
                    $eventGpxes = $event->masterGpxes()->where('is_published', true)->get();
                    foreach($eventGpxes as $gpx) {
                        if ($gpx->gpx_path) {
                            $gpxList->put($gpx->id, (object)[
                                'id' => $gpx->id,
                                'title' => $gpx->title,
                                'gpx_path' => $gpx->gpx_path,
                                'distance_km' => $gpx->distance_km,
                                'elevation_gain_m' => $gpx->elevation_gain_m,
                                'elevation_loss_m' => $gpx->elevation_loss_m,
                            ]);
                        }
                    }
                    $gpxList = $gpxList->values();
                @endphp
                
                @if($gpxList->isNotEmpty())
                    <div class="flex flex-wrap gap-2 p-3 bg-slate-50 border-b border-slate-100">
                        @foreach($gpxList as $idx => $gpx)
                            <button type="button" 
                                    class="gpx-tab-btn px-3 py-1.5 text-xs font-bold rounded-lg border transition-all"
                                    data-gpx-url="{{ asset('storage/' . $gpx->gpx_path) }}" 
                                    data-idx="{{ $idx }}"
                                    onclick="selectGpxTrack(this)">
                                {{ $gpx->title }} @if($gpx->distance_km) ({{ number_format($gpx->distance_km, 2) }} km) @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="flex-1 min-h-[350px] md:min-h-[450px] relative">
                    <div id="route-mapbox-map" class="w-full h-full min-h-[350px] md:min-h-[450px] z-10"></div>
                    
                    <div id="route-iframe-container" class="hidden w-full h-full min-h-[350px] md:min-h-[450px]">
                        @if($event->map_embed_url)
                            <iframe src="{{ $event->map_embed_url }}" class="w-full h-full border-0" allowfullscreen="" loading="lazy"></iframe>
                        @endif
                    </div>
                </div>
                
                @if($gpxList->isNotEmpty())
                    <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-semibold">
                        <span>Pilih kategori di atas untuk memuat rute lari pada peta.</span>
                        <a id="gpx-download-link" href="#" download class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-bold text-xs">
                            <i class="fa-solid fa-download text-white"></i> Download File GPX
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Terms & Conditions Modal -->
    @if($event->terms_and_conditions)
        <div id="termsModal" class="fixed inset-0 z-[999] hidden">
            <div class="absolute inset-0 bg-slate-900/60" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-slate-200 w-full max-w-3xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center">
                                <i class="fa-solid fa-shield-halved text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm text-slate-900">Syarat & Ketentuan</h3>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $event->name }}</p>
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

    <script>
        var routeMap = null;
        var startMarker = null;
        var finishMarker = null;
        var gpxTracks = @json($gpxList);
        var eventLat = {{ $event->location_lat ?: 'null' }};
        var eventLng = {{ $event->location_lng ?: 'null' }};
        var hasGpx = {{ $gpxList->isNotEmpty() ? 'true' : 'false' }};
        var mapEmbedUrl = "{{ $event->map_embed_url ?: '' }}";

        function openRouteModal() {
            const modal = document.getElementById('routeModal');
            modal.classList.remove('hidden');
            setTimeout(initRouteMap, 100);
        }

        function closeRouteModal() {
            const modal = document.getElementById('routeModal');
            modal.classList.add('hidden');
        }

        function initRouteMap() {
            const mapboxToken = '{{ config('services.mapbox.token') }}';
            if (!mapboxToken || !window.mapboxgl) {
                showIframeFallback();
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
                    triggerGpxOrEventLocation();
                });
            } else {
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
            if (!root || !imgEl || !prevBtn || !nextBtn || !dotsEl) return;

            let items = [];
            try {
                items = JSON.parse(root.getAttribute('data-images') || '[]');
            } catch (e) {
                items = [];
            }
            items = Array.isArray(items) ? items.filter(Boolean) : [];
            if (items.length <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
                dotsEl.style.display = 'none';
                return;
            }

            let index = 0;

            function renderDots() {
                dotsEl.innerHTML = items.map(function (_, i) {
                    return '<button type="button" class="ql-gallery-dot" data-dot="' + i + '" data-active="' + (i === index ? '1' : '0') + '"></button>';
                }).join('');
            }

            function preload(i) {
                const url = items[i];
                if (!url) return;
                const im = new Image();
                im.src = url;
            }

            function go(i) {
                index = (i + items.length) % items.length;
                imgEl.src = items[index];
                imgEl.alt = '{{ $event->name }} - ' + (index + 1);
                Array.from(dotsEl.querySelectorAll('[data-dot]')).forEach(function (btn) {
                    const b = parseInt(btn.getAttribute('data-dot') || '0', 10);
                    btn.setAttribute('data-active', b === index ? '1' : '0');
                });
                preload((index + 1) % items.length);
                preload((index - 1 + items.length) % items.length);
            }

            prevBtn.addEventListener('click', function () { go(index - 1); });
            nextBtn.addEventListener('click', function () { go(index + 1); });
            dotsEl.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-dot]');
                if (!btn) return;
                go(parseInt(btn.getAttribute('data-dot') || '0', 10));
            });

            renderDots();
            preload(1);
        })();
    </script>

    <script>
        (function () {
            const track = document.getElementById('qlSponsorTrack');
            const prevBtn = document.getElementById('qlSponsorPrev');
            const nextBtn = document.getElementById('qlSponsorNext');
            const dotsEl = document.getElementById('qlSponsorDots');
            if (!track || !prevBtn || !nextBtn || !dotsEl) return;

            const items = Array.from(track.children);
            if (items.length <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
                dotsEl.style.display = 'none';
                return;
            }

            let index = 0;
            let lastInteractionAt = Date.now();
            let intervalId = null;

            function clamp(n) {
                return Math.max(0, Math.min(items.length - 1, n));
            }

            function setActiveDot() {
                Array.from(dotsEl.querySelectorAll('[data-dot]')).forEach(function (btn) {
                    const b = parseInt(btn.getAttribute('data-dot') || '0', 10);
                    btn.setAttribute('data-active', b === index ? '1' : '0');
                });
            }

            function targetLeftFor(i) {
                const el = items[i];
                if (!el) return 0;
                const left = el.offsetLeft;
                const w = el.getBoundingClientRect().width || el.offsetWidth || 0;
                const viewport = track.clientWidth || 0;
                return Math.max(0, left - Math.max(0, (viewport - w) / 2));
            }

            function go(i) {
                index = clamp(i);
                track.scrollTo({ left: targetLeftFor(index), behavior: 'smooth' });
                setActiveDot();
            }

            function renderDots() {
                dotsEl.innerHTML = items.map(function (_, i) {
                    return '<button type="button" class="ql-gallery-dot" data-dot="' + i + '" data-active="' + (i === index ? '1' : '0') + '"></button>';
                }).join('');
            }

            function syncFromScroll() {
                const center = track.scrollLeft + (track.clientWidth / 2);
                let best = index;
                let bestDist = Infinity;
                for (let i = 0; i < items.length; i++) {
                    const el = items[i];
                    const elCenter = el.offsetLeft + (el.getBoundingClientRect().width / 2);
                    const dist = Math.abs(elCenter - center);
                    if (dist < bestDist) {
                        bestDist = dist;
                        best = i;
                    }
                }
                if (best === index) return;
                index = best;
                setActiveDot();
            }

            function bumpInteraction() {
                lastInteractionAt = Date.now();
            }

            function startAuto() {
                if (intervalId) return;
                intervalId = window.setInterval(function () {
                    if (document.hidden) return;
                    if (Date.now() - lastInteractionAt < 5000) return;
                    go(index + 1);
                }, 3500);
            }

            function stopAuto() {
                if (!intervalId) return;
                window.clearInterval(intervalId);
                intervalId = null;
            }

            prevBtn.addEventListener('click', function () { bumpInteraction(); go(index - 1); });
            nextBtn.addEventListener('click', function () { bumpInteraction(); go(index + 1); });
            dotsEl.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-dot]');
                if (!btn) return;
                bumpInteraction();
                go(parseInt(btn.getAttribute('data-dot') || '0', 10));
            });
            track.addEventListener('scroll', function () { bumpInteraction(); window.requestAnimationFrame(syncFromScroll); }, { passive: true });
            track.addEventListener('pointerenter', stopAuto);
            track.addEventListener('pointerleave', startAuto);
            prevBtn.addEventListener('pointerenter', stopAuto);
            nextBtn.addEventListener('pointerenter', stopAuto);
            prevBtn.addEventListener('pointerleave', startAuto);
            nextBtn.addEventListener('pointerleave', startAuto);
            dotsEl.addEventListener('pointerenter', stopAuto);
            dotsEl.addEventListener('pointerleave', startAuto);
            window.addEventListener('resize', function () { bumpInteraction(); syncFromScroll(); });

            renderDots();
            syncFromScroll();
            go(0);
            startAuto();
        })();
    </script>

    <script>
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

                if (couponRowEl) couponRowEl.classList.toggle('hidden', ! (couponHiddenEl && couponHiddenEl.value));
                if (couponLabelEl && couponHiddenEl && couponHiddenEl.value) couponLabelEl.textContent = '(' + couponHiddenEl.value + ')';
                if (discountAmountEl) discountAmountEl.textContent = discount > 0 ? '-' + formatCurrency(discount) : '-Rp 0';

                const paymentContainer = document.getElementById('payment-methods-container');
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

            function setNameByField(item, selector, name) {
                item.querySelectorAll(selector).forEach(function(el) {
                    el.name = name;
                });
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
                    if (dobEl) dobEl.value = '1990-01-01';

                    const targetHour = item.querySelector('[data-target-hour]')?.value || '00';
                    const targetMinute = item.querySelector('[data-target-minute]')?.value || '30';
                    const targetSecond = item.querySelector('[data-target-second]')?.value || '00';
                    const targetTimeEl = item.querySelector('[data-field="target_time"]') || item.querySelector('[data-hidden-auto="target_time"]');
                    if (targetTimeEl) {
                        targetTimeEl.value = targetHour + ':' + targetMinute + ':' + targetSecond;
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

                if (data.payment_gateway === 'cod') {
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    window.location.href = `{{ route('events.show', $event->slug) }}?payment=success`;
                    return;
                }

                if (data.snap_token && window.snap) {
                    window.snap.pay(data.snap_token, {
                        onSuccess: function() { window.location.href = `{{ route('events.show', $event->slug) }}?payment=success`; },
                        onPending: function() { window.location.href = `{{ route('events.show', $event->slug) }}?payment=pending`; },
                        onError: function() {
                            alert('Pembayaran gagal diproses.');
                            setSubmittingState(false);
                        },
                        onClose: function() {
                            window.location.href = `{{ route('events.show', $event->slug) }}?payment=pending`;
                        }
                    });
                    return;
                }

                if ((data.payment_gateway === 'moota' || data.redirect_url) && window.RuangLariMoota && typeof window.RuangLariMoota.open === 'function' && data.transaction_id) {
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
                fillHiddenFields();
                setSubmittingState(true);

                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(async function(response) {
                    const contentType = response.headers.get('content-type') || '';
                    if (!contentType.includes('application/json')) {
                        throw new Error('Respons server tidak valid.');
                    }
                    const data = await response.json();
                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            const first = Object.values(data.errors)[0];
                            throw new Error(Array.isArray(first) ? first[0] : 'Data pendaftaran belum lengkap.');
                        }
                        throw new Error(data.message || 'Terjadi kesalahan pada server.');
                    }
                    return data;
                })
                .then(handleSuccess)
                .catch(function(err) {
                    alert(err.message || 'Gagal menghubungi server.');
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
        <script src="https://unpkg.com/vue@3/dist/vue.global.js" onerror="this.onerror=null;this.src='https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js'"></script>
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
