<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    @php
        $seoTitle = !empty($seo['title']) ? $seo['title'] : $event->name . ' - ' . ($event->location_name ?? 'Official Event');
        $seoDesc = !empty($seo['description']) ? $seo['description'] : \Illuminate\Support\Str::limit(strip_tags($event->short_description ?: $event->full_description), 155);
        $seoKeywords = !empty($seo['keywords']) ? $seo['keywords'] : 'lari, event lari, ' . $event->name . ', ' . ($event->location_name ?? '') . ', pendaftaran lari, ruanglari';
        $seoUrl = !empty($seo['url']) ? $seo['url'] : route('events.show', $event->slug);
        $seoImage = !empty($seo['image']) ? $seo['image'] : ($event->hero_image ? asset('storage/' . $event->hero_image) : asset('images/ruanglari_green.png'));
        $faviconUrl = $event->getFaviconUrl();

        $now = now();
        $isRegOpen = !($event->registration_open_at && $now < $event->registration_open_at)
            && !($event->registration_close_at && $now > $event->registration_close_at);

        $primaryColor = $event->theme_colors['primary'] ?? '#1d4ed8';
        $pa = is_array($event->premium_amenities) ? $event->premium_amenities : [];
        $hasPa = !empty($pa);
        $showSection = function ($key) use ($pa, $hasPa) {
            if (!$hasPa) return false;
            return !empty($pa[$key]['enabled']);
        };

        $heroImg = $event->hero_image ? asset('storage/' . $event->hero_image) : ($event->hero_image_url ?? '');
        $facilities = is_array($event->facilities) ? array_values(array_filter($event->facilities, fn($f) => !empty($f['name']))) : [];
        $gallery = is_array($event->gallery) ? array_values(array_filter($event->gallery)) : [];
        $faqItems = isset($pa['faq']['items']) && is_array($pa['faq']['items'])
            ? array_values(array_filter($pa['faq']['items'], fn($f) => !empty($f['question']) && !empty($f['answer'])))
            : [];
        $whatToBring = isset($pa['what_to_bring']['items']) && is_array($pa['what_to_bring']['items'])
            ? array_values(array_filter($pa['what_to_bring']['items']))
            : [];
        $eventAddons = is_array($event->addons)
            ? array_values(array_filter($event->addons, fn($a) => !empty($a['name'])))
            : [];

        $hasRacePack = ($showSection('jersey') || $showSection('medal') || $event->jersey_image || $event->medal_image);
        $hasLogistics = $showSection('rpc') || $showSection('venue') || ($showSection('what_to_bring') && count($whatToBring) > 0);
        $hasRouteSection = !empty($event->location_name) || !empty($event->location_address) || !empty($event->map_embed_url) || !empty($event->route_map_image);

        // Payment config
        $paymentConfig = is_array($event->payment_config) ? $event->payment_config : [];
        if (isset($paymentConfig['allowed_methods']) && is_array($paymentConfig['allowed_methods'])) {
            $allowed = $paymentConfig['allowed_methods'];
            $showMidtrans = in_array('midtrans', $allowed) || in_array('all', $allowed);
            $showMoota = in_array('moota', $allowed) || in_array('all', $allowed);
            $showManualTransfer = in_array('manual_transfer', $allowed) || in_array('all', $allowed);
            $showCod = in_array('cod', $allowed);
        } else {
            $showMidtrans = $paymentConfig['midtrans'] ?? true;
            $showMoota = $paymentConfig['moota'] ?? false;
            $showManualTransfer = $paymentConfig['manual_transfer'] ?? false;
            $showCod = $paymentConfig['cod'] ?? false;
        }
        if (!$showMidtrans && !$showMoota && !$showManualTransfer && !$showCod) {
            $showMidtrans = true;
        }
        $defaultPayment = $showMidtrans ? 'midtrans' : ($showMoota ? 'moota' : ($showManualTransfer ? 'manual_transfer' : 'cod'));
        $manualTransferWaiveFee = !empty($paymentConfig['manual_transfer_waive_fee']);

        $midtransDemoMode = filter_var($paymentConfig['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
        $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');

        $jerseySizes = is_array($event->jersey_sizes) && count($event->jersey_sizes) > 0
            ? $event->jersey_sizes
            : ['XS', 'S', 'M', 'L', 'XL', '2XL'];
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}" />
    <meta name="keywords" content="{{ $seoKeywords }}">
    <link rel="canonical" href="{{ $seoUrl }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <meta property="og:type" content="event" />
    <meta property="og:title" content="{{ $seoTitle }}" />
    <meta property="og:description" content="{{ $seoDesc }}" />
    <meta property="og:url" content="{{ $seoUrl }}" />
    <meta property="og:image" content="{{ $seoImage }}" />
    <meta property="og:site_name" content="RuangLari" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $seoTitle }}" />
    <meta name="twitter:description" content="{{ $seoDesc }}" />
    <meta name="twitter:image" content="{{ $seoImage }}" />

    <link rel="icon" type="image/png" sizes="32x32" href="{{ $faviconUrl }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ $faviconUrl }}">
    <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    <link rel="shortcut icon" href="{{ $faviconUrl }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=JetBrains+Mono:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(env('RECAPTCHA_SITE_KEY_v3'))
        <script async defer src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}"></script>
    @endif
    @if($showMidtrans && $midtransClientKey)
        <script defer src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
    @endif

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Event",
      "name": @json($event->name),
      "description": @json($seoDesc),
      "image": @json($seoImage),
      "startDate": @json($event->start_at ? $event->start_at->toIso8601String() : ''),
      "endDate": @json($event->end_at ? $event->end_at->toIso8601String() : ($event->start_at ? $event->start_at->copy()->addHours(4)->toIso8601String() : '')),
      "eventStatus": "https://schema.org/EventScheduled",
      "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
      "location": {
        "@@type": "Place",
        "name": @json($event->location_name ?? 'TBA'),
        "address": {
          "@@type": "PostalAddress",
          "addressLocality": @json($event->city ?? ''),
          "addressCountry": "ID"
        }
      },
      "organizer": {
        "@@type": "Organization",
        "name": "RuangLari",
        "url": @json(url('/'))
      },
      "offers": {
        "@@type": "Offer",
        "url": @json($seoUrl),
        "priceCurrency": "IDR",
        "availability": "{{ $isRegOpen ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut' }}"
      }
    }
    </script>

    <style>
        :root {
            --theme-primary: {{ $primaryColor }};
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        h1, h2, h3, h4, .font-heading {
            font-family: 'Inter Tight', 'Sora', sans-serif;
            letter-spacing: -0.03em;
        }
        .font-mono-num {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-variant-numeric: tabular-nums;
        }
        .brand-btn {
            background-color: var(--theme-primary);
            color: #ffffff;
        }
        .brand-btn:hover {
            filter: brightness(0.92);
        }
        .brand-text {
            color: var(--theme-primary);
        }
        .brand-border-checked:checked + .cat-card {
            border-color: var(--theme-primary);
            background-color: #f8fafc;
            box-shadow: inset 0 0 0 1px var(--theme-primary);
        }
        a[href], button:not(:disabled), select, summary, label[for], input[type="checkbox"], input[type="radio"] {
            cursor: pointer;
        }
        button:disabled {
            cursor: not-allowed;
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased flex flex-col min-h-screen">

    <header class="sticky top-0 z-50 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="#top" class="flex items-center gap-3 min-w-0 rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                    @if($event->logo_image)
                        <img src="{{ asset('storage/' . $event->logo_image) }}" alt="{{ $event->name }}" width="36" height="36" class="h-9 w-auto object-contain rounded">
                    @endif
                    <span class="font-heading text-lg font-extrabold text-slate-900 truncate">{{ $event->name }}</span>
                </a>

                <nav aria-label="Navigasi utama" class="hidden md:flex items-center gap-6">
                    @if($categories->count() > 0)
                        <a href="#categories" class="text-sm font-medium text-slate-700 hover:text-slate-950 transition duration-150">Kategori</a>
                    @endif
                    @if(count($facilities) > 0)
                        <a href="#facilities" class="text-sm font-medium text-slate-700 hover:text-slate-950 transition duration-150">Fasilitas</a>
                    @endif
                    @if($hasRacePack)
                        <a href="#racepack" class="text-sm font-medium text-slate-700 hover:text-slate-950 transition duration-150">Race Pack</a>
                    @endif
                    @if($hasRouteSection)
                        <a href="#lokasi" class="text-sm font-medium text-slate-700 hover:text-slate-950 transition duration-150">Lokasi</a>
                    @endif
                    @if($showSection('faq') && count($faqItems) > 0)
                        <a href="#faq" class="text-sm font-medium text-slate-700 hover:text-slate-950 transition duration-150">FAQ</a>
                    @endif
                    @if($isRegOpen)
                        <a href="#register" class="brand-btn px-4 py-2 rounded-md text-sm font-bold transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                            Daftar Sekarang
                        </a>
                    @else
                        <span class="px-4 py-2 rounded-md bg-slate-200 text-slate-700 text-sm font-semibold">Ditutup</span>
                    @endif
                </nav>

                <button type="button" id="navToggle" aria-expanded="false" aria-controls="mobileMenu" aria-label="Buka menu navigasi" class="md:hidden inline-flex items-center justify-center w-11 h-11 rounded-md text-slate-900 hover:bg-slate-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                    <svg class="w-6 h-6" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
            </div>
        </div>

        <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-slate-200 px-4 py-4 space-y-2">
            @if($categories->count() > 0)
                <a href="#categories" class="mobile-nav-link block py-2.5 px-3 rounded-md text-sm font-medium text-slate-800 hover:bg-slate-100">Kategori</a>
            @endif
            @if(count($facilities) > 0)
                <a href="#facilities" class="mobile-nav-link block py-2.5 px-3 rounded-md text-sm font-medium text-slate-800 hover:bg-slate-100">Fasilitas</a>
            @endif
            @if($hasRacePack)
                <a href="#racepack" class="mobile-nav-link block py-2.5 px-3 rounded-md text-sm font-medium text-slate-800 hover:bg-slate-100">Race Pack</a>
            @endif
            @if($hasRouteSection)
                <a href="#lokasi" class="mobile-nav-link block py-2.5 px-3 rounded-md text-sm font-medium text-slate-800 hover:bg-slate-100">Lokasi</a>
            @endif
            @if($showSection('faq') && count($faqItems) > 0)
                <a href="#faq" class="mobile-nav-link block py-2.5 px-3 rounded-md text-sm font-medium text-slate-800 hover:bg-slate-100">FAQ</a>
            @endif
            @if($isRegOpen)
                <a href="#register" class="mobile-nav-link block text-center brand-btn py-2.5 px-4 rounded-md text-sm font-bold mt-2">Daftar Sekarang</a>
            @endif
        </div>
    </header>

    <main id="top" class="flex-grow pt-0">
        <!-- Editorial Hero -->
        <section class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    <div class="lg:col-span-7 space-y-6">
                        <h1 class="text-3xl sm:text-5xl font-extrabold text-slate-900 leading-tight">
                            {{ $event->name }}
                        </h1>

                        @if($event->short_description)
                            <div class="text-base sm:text-lg text-slate-700 leading-relaxed max-w-2xl prose prose-sm max-w-none">
                                {!! $event->short_description !!}
                            </div>
                        @endif

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-200">
                            @if($event->start_at)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal & Start</p>
                                    <p class="text-sm sm:text-base font-bold text-slate-900 font-mono-num mt-0.5">{{ $event->start_at->translatedFormat('d M Y') }}</p>
                                    <p class="text-xs text-slate-600 font-mono-num">{{ $event->start_at->format('H:i') }} WIB</p>
                                </div>
                            @endif
                            @if($categories->count() > 0)
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</p>
                                    <p class="text-sm sm:text-base font-bold text-slate-900 font-mono-num mt-0.5">{{ $categories->count() }} Kelas</p>
                                    <p class="text-xs text-slate-600 truncate">{{ $categories->pluck('name')->implode(', ') }}</p>
                                </div>
                            @endif
                            @if($event->location_name)
                                <div class="col-span-2 sm:col-span-1">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Lokasi</p>
                                    <p class="text-sm sm:text-base font-bold text-slate-900 mt-0.5">{{ $event->location_name }}</p>
                                    @if($event->city)
                                        <p class="text-xs text-slate-600">{{ $event->city }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            @if($isRegOpen)
                                <a href="#register" class="brand-btn px-6 py-3 rounded-md text-sm sm:text-base font-bold transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                    Daftar Sekarang
                                </a>
                            @else
                                <button type="button" disabled class="px-6 py-3 rounded-md bg-slate-200 text-slate-600 font-bold text-sm sm:text-base">
                                    Pendaftaran Ditutup
                                </button>
                            @endif
                            @if($categories->count() > 0)
                                <a href="#categories" class="px-6 py-3 rounded-md bg-white text-slate-900 border border-slate-300 hover:bg-slate-50 font-semibold text-sm sm:text-base transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                    Lihat Kategori & Tarif
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-5">
                        @if($heroImg)
                            <div class="bg-slate-100 border border-slate-200 rounded-lg overflow-hidden">
                                <img src="{{ $heroImg }}" alt="Poster {{ $event->name }}" width="800" height="1000" fetchpriority="high" class="w-full h-auto max-h-[520px] object-cover">
                            </div>
                        @elseif($event->start_at)
                            <div class="bg-slate-900 text-white rounded-lg p-6 sm:p-8 space-y-4">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-300">Hitung Mundur Race Day</p>
                                <p class="text-lg font-bold">{{ $event->start_at->translatedFormat('l, d F Y') }}</p>
                                <div class="grid grid-cols-4 gap-2 text-center" id="countdown-timer">
                                    <div class="bg-slate-800 rounded-md p-3">
                                        <span class="block text-2xl font-mono-num font-bold" id="cd-days">00</span>
                                        <span class="text-xs text-slate-300">Hari</span>
                                    </div>
                                    <div class="bg-slate-800 rounded-md p-3">
                                        <span class="block text-2xl font-mono-num font-bold" id="cd-hours">00</span>
                                        <span class="text-xs text-slate-300">Jam</span>
                                    </div>
                                    <div class="bg-slate-800 rounded-md p-3">
                                        <span class="block text-2xl font-mono-num font-bold" id="cd-minutes">00</span>
                                        <span class="text-xs text-slate-300">Menit</span>
                                    </div>
                                    <div class="bg-slate-800 rounded-md p-3">
                                        <span class="block text-2xl font-mono-num font-bold" id="cd-seconds">00</span>
                                        <span class="text-xs text-slate-300">Detik</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        @if($categories->count() > 0)
        <section id="categories" class="py-16 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Kategori & Biaya Pendaftaran</h2>
                        <p class="text-sm text-slate-700 mt-1">Pilih jarak lomba sesuai target lari Anda. Klik kategori untuk langsung mengisi formulir.</p>
                    </div>
                    @if((int)($event->promo_buy_x ?? 0) > 0)
                        <div class="bg-white border border-slate-200 rounded-md px-4 py-2 text-xs text-slate-800">
                            Promo Grup: Daftar <strong class="brand-text">{{ (int)$event->promo_buy_x }}</strong> peserta di kategori sama, <strong class="brand-text">Gratis 1</strong> peserta.
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($categories as $cat)
                        @php
                            $earlyValid = ($cat->price_early ?? 0) > 0
                                && (!$cat->early_bird_end_at || $now->lte($cat->early_bird_end_at))
                                && (!$cat->early_bird_quota || ($cat->early_bird_sold_count ?? 0) < $cat->early_bird_quota);
                            $priceRegular = (int) ($cat->price_regular ?? 0);
                            $priceLate = (int) ($cat->price_late ?? 0);
                            $displayPrice = $earlyValid ? (int) $cat->price_early : ($priceRegular > 0 ? $priceRegular : $priceLate);
                            $tierLabel = $earlyValid ? 'Early Bird' : 'Reguler';
                        @endphp
                        <article class="bg-white border border-slate-200 rounded-lg p-6 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-4">
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900">{{ $cat->name }}</h3>
                                        @if($cat->distance_km)
                                            <p class="text-xs text-slate-600 font-mono-num mt-0.5">Jarak {{ $cat->distance_km }} KM</p>
                                        @endif
                                    </div>
                                    <span class="px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-800">{{ $tierLabel }}</span>
                                </div>

                                <div class="mb-5 pb-5 border-b border-slate-200">
                                    @if($earlyValid && $priceRegular > $displayPrice)
                                        <span class="block text-xs text-slate-500 line-through font-mono-num">Rp {{ number_format($priceRegular, 0, ',', '.') }}</span>
                                    @endif
                                    <p class="text-2xl font-extrabold text-slate-900 font-mono-num">
                                        {{ $displayPrice > 0 ? 'Rp ' . number_format($displayPrice, 0, ',', '.') : 'Gratis' }}
                                    </p>
                                </div>

                                <dl class="space-y-2.5 text-sm mb-6">
                                    <div class="flex justify-between">
                                        <dt class="text-slate-600">Flag Off</dt>
                                        <dd class="text-slate-900 font-mono-num font-semibold">{{ $cat->start_time ? \Carbon\Carbon::parse($cat->start_time)->format('H:i') . ' WIB' : ($event->start_at ? $event->start_at->format('H:i') . ' WIB' : 'TBA') }}</dd>
                                    </div>
                                    @if($cat->cot_hours)
                                        <div class="flex justify-between">
                                            <dt class="text-slate-600">Cut Off Time</dt>
                                            <dd class="text-slate-900 font-mono-num font-semibold">{{ $cat->cot_hours }} Jam</dd>
                                        </div>
                                    @endif
                                    @if($cat->min_age !== null || $cat->max_age !== null)
                                        <div class="flex justify-between">
                                            <dt class="text-slate-600">Syarat Usia</dt>
                                            <dd class="text-slate-900 font-mono-num font-semibold">
                                                @if($cat->min_age !== null && $cat->max_age !== null)
                                                    {{ $cat->min_age }} - {{ $cat->max_age }} Tahun
                                                @elseif($cat->min_age !== null)
                                                    Min. {{ $cat->min_age }} Tahun
                                                @else
                                                    Maks. {{ $cat->max_age }} Tahun
                                                @endif
                                            </dd>
                                        </div>
                                    @endif
                                    @if($cat->quota)
                                        <div class="flex justify-between">
                                            <dt class="text-slate-600">Kuota</dt>
                                            <dd class="text-slate-900 font-mono-num font-semibold">{{ number_format($cat->quota, 0, ',', '.') }} Slot</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>

                            @if($isRegOpen)
                                <button type="button" onclick="selectCategoryAndScroll({{ $cat->id }})" class="w-full py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm rounded-md transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                    Daftar Kategori {{ $cat->name }}
                                </button>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- Facilities (Only when populated in Master Event) -->
        @if(count($facilities) > 0)
        <section id="facilities" class="py-16 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mb-10">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Fasilitas Peserta</h2>
                    <p class="text-sm text-slate-700 mt-1">Fasilitas resmi yang disiapkan panitia untuk seluruh peserta.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($facilities as $facility)
                        <div class="bg-slate-50 border border-slate-200 rounded-lg overflow-hidden flex flex-col">
                            @if(!empty($facility['image']))
                                <img src="{{ asset('storage/' . $facility['image']) }}" alt="{{ $facility['name'] }}" width="600" height="360" loading="lazy" class="w-full h-44 object-cover border-b border-slate-200">
                            @endif
                            <div class="p-5">
                                <h3 class="text-base font-bold text-slate-900">{{ $facility['name'] }}</h3>
                                @if(!empty($facility['description']))
                                    <p class="text-sm text-slate-700 mt-1.5 leading-relaxed">{{ $facility['description'] }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- Race Pack (Jersey & Medal) -->
        @if($hasRacePack)
        <section id="racepack" class="py-16 bg-slate-900 text-white border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mb-10">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Race Pack: Jersey & Medali</h2>
                    <p class="text-sm text-slate-300 mt-1">Perlengkapan resmi peserta terdaftar.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @if($event->jersey_image || $showSection('jersey'))
                        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 space-y-4">
                            <div class="flex items-center justify-between gap-2">
                                <h3 class="text-lg font-bold text-white">Jersey Resmi</h3>
                                <span class="text-xs text-slate-300 font-mono-num">{{ implode(' / ', $jerseySizes) }}</span>
                            </div>
                            @if($event->jersey_image)
                                <img src="{{ asset('storage/' . $event->jersey_image) }}" alt="Jersey {{ $event->name }}" width="800" height="500" loading="lazy" class="lightbox-trigger w-full h-64 object-cover rounded-md border border-slate-700 cursor-zoom-in">
                            @endif
                            @if(!empty($pa['jersey']['description']))
                                <p class="text-sm text-slate-200 leading-relaxed">{{ $pa['jersey']['description'] }}</p>
                            @endif
                        </div>
                    @endif

                    @if($event->medal_image || $showSection('medal'))
                        <div class="bg-slate-800 border border-slate-700 rounded-lg p-6 space-y-4">
                            <h3 class="text-lg font-bold text-white">Medali Finisher</h3>
                            @if($event->medal_image)
                                <img src="{{ asset('storage/' . $event->medal_image) }}" alt="Medali Finisher {{ $event->name }}" width="800" height="500" loading="lazy" class="lightbox-trigger w-full h-64 object-cover rounded-md border border-slate-700 cursor-zoom-in">
                            @endif
                            @if(!empty($pa['medal']['description']))
                                <p class="text-sm text-slate-200 leading-relaxed">{{ $pa['medal']['description'] }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        @include('events.partials.sponsor-carousel', [
            'gradientFrom' => 'from-white',
            'titleColor' => 'text-slate-600',
            'containerClass' => 'bg-white border border-slate-200',
            'sectionClass' => 'py-14 bg-white border-b border-slate-200'
        ])
        @include('events.partials.prizes-section', ['categories' => $categories])
        @include('events.partials.custom-section')

        <!-- Registration Section -->
        <section id="register" class="py-16 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl mb-10">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Formulir Pendaftaran</h2>
                    <p class="text-sm text-slate-700 mt-1">Lengkapi data penanggung jawab (PIC) dan peserta. Anda dapat menambahkan beberapa peserta dalam satu transaksi.</p>
                </div>

                @if(request('payment') === 'pending')
                    <div class="mb-6 bg-amber-50 border border-amber-300 rounded-md p-4 text-sm text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="font-bold">Pembayaran Masih Menunggu</p>
                            <p class="text-xs text-amber-900 mt-0.5">Jika popup pembayaran tertutup, lanjutkan pembayaran tanpa mengisi ulang formulir.</p>
                        </div>
                        <a href="{{ route('events.payments.continue', $event->slug) }}" class="brand-btn px-4 py-2 rounded-md text-xs font-bold shrink-0 text-center">Lanjutkan Pembayaran</a>
                    </div>
                @elseif(request('payment') === 'success' || request('success') === 'true')
                    <div class="mb-6 bg-emerald-50 border border-emerald-300 rounded-md p-4 text-sm text-emerald-950">
                        <p class="font-bold">Registrasi & Pembayaran Berhasil</p>
                        <p class="text-xs text-emerald-900 mt-0.5">Konfirmasi pendaftaran dan e-Tiket telah dikirim ke email PIC dan peserta.</p>
                    </div>
                @endif

                @if(!$isRegOpen)
                    <div class="bg-white border border-slate-200 rounded-lg p-8 text-center max-w-xl mx-auto">
                        <h3 class="text-lg font-bold text-slate-900">Pendaftaran Belum Dibuka / Sudah Ditutup</h3>
                        <p class="text-sm text-slate-600 mt-2">
                            @if($event->registration_open_at && $now < $event->registration_open_at)
                                Pendaftaran dibuka pada {{ $event->registration_open_at->translatedFormat('d F Y, H:i') }} WIB.
                            @else
                                Periode pendaftaran untuk event ini telah berakhir.
                            @endif
                        </p>
                    </div>
                @else
                    <div id="formAlertBanner" class="hidden mb-6 rounded-md p-4 text-sm border" role="alert"></div>

                    <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" novalidate class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        @csrf

                        <!-- Left Column: PIC & Participants -->
                        <div class="lg:col-span-8 space-y-6">
                            <div class="bg-white border border-slate-200 rounded-lg p-6 space-y-4">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">1. Data Penanggung Jawab (PIC)</h3>
                                    <p class="text-xs text-slate-600 mt-0.5">Tagihan dan konfirmasi pembayaran dikirim ke email dan WhatsApp PIC.</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="sm:col-span-2">
                                        <label for="pic_name" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap PIC <span class="text-rose-600">*</span></label>
                                        <input type="text" id="pic_name" name="pic_name" value="{{ old('pic_name') }}" required autocomplete="name" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="Nama lengkap penanggung jawab">
                                    </div>
                                    <div>
                                        <label for="pic_email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email PIC <span class="text-rose-600">*</span></label>
                                        <input type="email" id="pic_email" name="pic_email" value="{{ old('pic_email') }}" required autocomplete="email" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="nama@email.com">
                                    </div>
                                    <div>
                                        <label for="pic_phone" class="block text-xs font-semibold text-slate-700 mb-1.5">Nomor WhatsApp PIC <span class="text-rose-600">*</span></label>
                                        <input type="tel" id="pic_phone" name="pic_phone" value="{{ old('pic_phone') }}" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 font-mono-num placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="081234567890">
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-base font-bold text-slate-900">2. Data Peserta Lari</h3>
                                    <button type="button" id="addParticipantBtn" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-md transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                        + Tambah Peserta
                                    </button>
                                </div>

                                <div id="participantsWrapper" class="space-y-5">
                                    <div class="participant-item bg-white border border-slate-200 rounded-lg p-6 space-y-5" data-index="0">
                                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <strong class="participant-title text-sm font-bold text-slate-900">Peserta #1</strong>
                                                <button type="button" onclick="copyFromPic(this)" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-medium rounded border border-slate-200 transition duration-150">
                                                    Isi dari PIC
                                                </button>
                                                <button type="button" onclick="copyFromPrev(this)" class="copy-prev-btn hidden px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-medium rounded border border-slate-200 transition duration-150">
                                                    Salin Kontak Darurat
                                                </button>
                                            </div>
                                            <button type="button" class="remove-participant hidden text-xs font-semibold text-rose-600 hover:text-rose-800 px-2 py-1 rounded">
                                                Hapus Peserta
                                            </button>
                                        </div>

                                        <div>
                                            <span class="block text-xs font-semibold text-slate-700 mb-2">Pilih Kategori Lomba <span class="text-rose-600">*</span></span>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                @foreach($categories as $cat)
                                                    @php
                                                        $earlyValid = ($cat->price_early ?? 0) > 0
                                                            && (!$cat->early_bird_end_at || $now->lte($cat->early_bird_end_at))
                                                            && (!$cat->early_bird_quota || ($cat->early_bird_sold_count ?? 0) < $cat->early_bird_quota);
                                                        $pReg = (int) ($cat->price_regular ?? 0);
                                                        $pLate = (int) ($cat->price_late ?? 0);
                                                        $activePrice = $earlyValid ? (int) $cat->price_early : ($pReg > 0 ? $pReg : $pLate);
                                                    @endphp
                                                    <label class="relative block">
                                                        <input type="radio" name="participants[0][category_id]" value="{{ $cat->id }}" class="peer sr-only cat-radio brand-border-checked" data-price="{{ $activePrice }}" data-min-age="{{ $cat->min_age ?? '' }}" data-max-age="{{ $cat->max_age ?? '' }}" data-cat-name="{{ $cat->name }}" required>
                                                        <div class="cat-card p-3.5 bg-white border border-slate-300 rounded-md hover:border-slate-500 transition duration-150">
                                                            <div class="flex items-center justify-between gap-2">
                                                                <span class="font-bold text-sm text-slate-900">{{ $cat->name }}</span>
                                                                <span class="text-sm font-bold font-mono-num brand-text">
                                                                    {{ $activePrice > 0 ? 'Rp ' . number_format($activePrice, 0, ',', '.') : 'Gratis' }}
                                                                </span>
                                                            </div>
                                                            <p class="text-xs text-slate-600 mt-1">
                                                                @if($cat->distance_km){{ $cat->distance_km }} KM @endif
                                                                @if($cat->quota)&middot; {{ $cat->quota }} Slot @endif
                                                                @if($cat->min_age !== null && $cat->max_age !== null) &middot; Usia {{ $cat->min_age }}-{{ $cat->max_age }} Thn @elseif($cat->min_age !== null) &middot; Min. {{ $cat->min_age }} Thn @elseif($cat->max_age !== null) &middot; Maks. {{ $cat->max_age }} Thn @endif
                                                                @if($earlyValid && $pReg > $activePrice)
                                                                    <span class="line-through text-slate-400 ml-1">Rp {{ number_format($pReg, 0, ',', '.') }}</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Lengkap <span class="text-rose-600">*</span></label>
                                                <input type="text" data-field="name" name="participants[0][name]" required maxlength="255" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="Sesuai KTP/identitas">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Jenis Kelamin <span class="text-rose-600">*</span></label>
                                                <select data-field="gender" name="participants[0][gender]" required class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="male">Laki-laki</option>
                                                    <option value="female">Perempuan</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Email Peserta <span class="text-rose-600">*</span></label>
                                                <input type="email" data-field="email" name="participants[0][email]" required maxlength="255" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="peserta@email.com">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tanggal Lahir <span class="text-rose-600">*</span></label>
                                                <input type="date" data-field="date_of_birth" name="participants[0][date_of_birth]" required max="{{ now()->subDay()->format('Y-m-d') }}" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. HP / WhatsApp <span class="text-rose-600">*</span></label>
                                                <input type="tel" data-field="phone" name="participants[0][phone]" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 font-mono-num placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="081234567890">
                                            </div>

                                            @php
                                                $pcrFormFields = $pa['form_fields'] ?? null;
                                                $showIdCard = (is_array($pcrFormFields) && count($pcrFormFields) > 0) ? !empty($pcrFormFields['id_card']) : true;
                                                $idCardRequired = $showIdCard && !empty($pcrFormFields['id_card_required']);
                                            @endphp
                                            @if($showIdCard)
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. Identitas (KTP/SIM/Paspor) @if($idCardRequired)<span class="text-rose-600">*</span>@else<span class="text-slate-500">(Opsional)</span>@endif</label>
                                                <input type="text" data-field="id_card" name="participants[0][id_card]" {{ $idCardRequired ? 'required' : '' }} maxlength="50" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 font-mono-num placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="{{ $idCardRequired ? 'Nomor identitas resmi' : 'Nomor identitas (opsional)' }}">
                                            </div>
                                            @endif

                                            <div class="sm:col-span-2">
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Alamat Lengkap <span class="text-rose-600">*</span></label>
                                                <textarea data-field="address" name="participants[0][address]" required maxlength="500" rows="2" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="Alamat domisili saat ini"></textarea>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Kontak Darurat <span class="text-rose-600">*</span></label>
                                                <input type="text" data-field="emergency_contact_name" name="participants[0][emergency_contact_name]" required maxlength="255" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="Nama keluarga/kerabat">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">No. Kontak Darurat <span class="text-rose-600">*</span></label>
                                                <input type="tel" data-field="emergency_contact_number" name="participants[0][emergency_contact_number]" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 font-mono-num placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900" placeholder="081234567890">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Ukuran Jersey <span class="text-rose-600">*</span></label>
                                                <select data-field="jersey_size" name="participants[0][jersey_size]" required class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                                    <option value="">-- Pilih Ukuran --</option>
                                                    @foreach($jerseySizes as $size)
                                                        <option value="{{ $size }}">{{ $size }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Golongan Darah (Opsional)</label>
                                                <select data-field="blood_type" name="participants[0][blood_type]" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                                    <option value="">-- Pilih --</option>
                                                    <option value="A">A</option>
                                                    <option value="B">B</option>
                                                    <option value="AB">AB</option>
                                                    <option value="O">O</option>
                                                </select>
                                            </div>

                                            @if(!empty($pa['form_fields']['target_time']))
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Target Waktu Finish (Opsional)</label>
                                                    <input type="text" data-field="target_time" name="participants[0][target_time]" placeholder="HH:MM:SS (contoh: 00:55:00)" class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 font-mono-num placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                                </div>
                                            @endif

                                            @if(!empty($pa['form_fields']['strava_activity']) || !empty($pa['form_fields']['strava_url']))
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Link Aktivitas Strava (Opsional)</label>
                                                    <input type="url" data-field="strava_url" name="participants[0][strava_url]" placeholder="https://www.strava.com/activities/..." class="w-full bg-white border border-slate-300 rounded-md px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                                </div>
                                            @endif

                                            <div class="sm:col-span-2">
                                                @include('events.partials.dynamic-extra-fields', ['mode' => 'light'])
                                            </div>

                                            @if(count($eventAddons) > 0)
                                                <div class="sm:col-span-2 pt-3 border-t border-slate-200 space-y-2">
                                                    <p class="text-xs font-semibold text-slate-700">Add-ons Tambahan (Opsional)</p>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                        @foreach($eventAddons as $aIdx => $addon)
                                                            <label class="flex items-center justify-between gap-2 p-2.5 rounded-md bg-slate-50 border border-slate-200 hover:border-slate-400">
                                                                <span class="flex items-center gap-2 text-xs text-slate-800">
                                                                    <input type="hidden" name="participants[0][addons][{{ $aIdx }}][name]" value="{{ $addon['name'] }}">
                                                                    <input type="checkbox" data-addon-price="{{ (int)($addon['price'] ?? 0) }}" name="participants[0][addons][{{ $aIdx }}][selected]" value="1" class="addon-checkbox rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                                                    <span>{{ $addon['name'] }}</span>
                                                                </span>
                                                                <span class="text-xs font-mono-num text-slate-700">+Rp {{ number_format((int)($addon['price'] ?? 0), 0, ',', '.') }}</span>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Sticky Summary & Payment -->
                        <div class="lg:col-span-4">
                            <div class="sticky top-20 space-y-5">
                                <div class="bg-white border border-slate-200 rounded-lg p-5 space-y-3">
                                    <label for="coupon_code" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Kode Promo</label>
                                    <div class="flex gap-2">
                                        <input type="text" id="coupon_code" value="{{ old('coupon_code') }}" placeholder="Masukkan kode" class="flex-1 bg-white border border-slate-300 rounded-md px-3 py-2 text-sm text-slate-900 uppercase font-mono-num placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                                        <button type="button" id="applyCouponBtn" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-md transition duration-150">Pakai</button>
                                    </div>
                                    <div id="couponMessage" class="text-xs"></div>
                                    <input type="hidden" name="coupon_code" id="coupon_code_hidden">
                                </div>

                                <div class="bg-white border border-slate-200 rounded-lg p-6 space-y-5">
                                    <h3 class="text-base font-bold text-slate-900">Ringkasan Biaya</h3>

                                    <dl class="space-y-2.5 text-sm border-b border-slate-200 pb-4">
                                        <div class="flex justify-between text-slate-700">
                                            <dt>Subtotal</dt>
                                            <dd id="subtotalDisplay" class="font-mono-num text-slate-900 font-semibold">Rp 0</dd>
                                        </div>
                                        <div id="feeRow" class="flex justify-between text-slate-700 {{ ($event->platform_fee ?? 0) > 0 ? '' : 'hidden' }}">
                                            <dt>Biaya Layanan</dt>
                                            <dd id="feeDisplay" class="font-mono-num text-slate-900 font-semibold">Rp 0</dd>
                                        </div>
                                        <div id="discountRow" class="flex justify-between text-emerald-700 hidden">
                                            <dt>Diskon Kupon</dt>
                                            <dd id="discountDisplay" class="font-mono-num font-semibold">-Rp 0</dd>
                                        </div>
                                    </dl>

                                    <div class="space-y-2.5">
                                        <p class="text-xs font-semibold text-slate-700 uppercase tracking-wider">Metode Pembayaran</p>
                                        @if($showMidtrans)
                                            <label class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 hover:border-slate-400 rounded-md">
                                                <input type="radio" name="payment_method" value="midtrans" {{ $defaultPayment === 'midtrans' ? 'checked' : '' }} class="mt-0.5 text-slate-900 border-slate-300 focus:ring-slate-900">
                                                <span class="text-xs">
                                                    <span class="block font-bold text-slate-900">QRIS / E-Wallet / Virtual Account</span>
                                                    <span class="text-slate-600">Verifikasi otomatis via Midtrans</span>
                                                </span>
                                            </label>
                                        @endif
                                        @if($showMoota)
                                            <label class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 hover:border-slate-400 rounded-md">
                                                <input type="radio" name="payment_method" value="moota" {{ $defaultPayment === 'moota' ? 'checked' : '' }} class="mt-0.5 text-slate-900 border-slate-300 focus:ring-slate-900">
                                                <span class="text-xs">
                                                    <span class="block font-bold text-slate-900">Transfer Bank Otomatis (Moota)</span>
                                                    <span class="text-slate-600">Konfirmasi otomatis dengan kode unik</span>
                                                </span>
                                            </label>
                                        @endif
                                        @if($showManualTransfer)
                                            <label class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 hover:border-slate-400 rounded-md">
                                                <input type="radio" name="payment_method" value="manual_transfer" {{ $defaultPayment === 'manual_transfer' ? 'checked' : '' }} class="mt-0.5 text-slate-900 border-slate-300 focus:ring-slate-900">
                                                <span class="text-xs">
                                                    <span class="block font-bold text-slate-900">Transfer Rekening Panitia (EO)</span>
                                                    <span class="text-slate-600">Transfer manual + upload bukti pembayaran{{ $manualTransferWaiveFee ? ' (bebas biaya layanan)' : '' }}</span>
                                                </span>
                                            </label>
                                        @endif
                                        @if($showCod)
                                            <label class="flex items-start gap-3 p-3 bg-slate-50 border border-slate-200 hover:border-slate-400 rounded-md">
                                                <input type="radio" name="payment_method" value="cod" {{ $defaultPayment === 'cod' ? 'checked' : '' }} class="mt-0.5 text-slate-900 border-slate-300 focus:ring-slate-900">
                                                <span class="text-xs">
                                                    <span class="block font-bold text-slate-900">Bayar di Tempat (COD)</span>
                                                    <span class="text-slate-600">Pembayaran langsung ke panitia</span>
                                                </span>
                                            </label>
                                        @endif
                                    </div>

                                    @if($event->terms_and_conditions)
                                        <label class="flex items-start gap-2.5 text-xs text-slate-700">
                                            <input type="checkbox" name="terms_agreed" id="terms_agreed" required class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                            <span>
                                                Saya menyetujui <button type="button" onclick="openTermsModal()" class="brand-text underline font-semibold">Syarat & Ketentuan</button> event ini.
                                            </span>
                                        </label>
                                    @endif

                                    <input type="hidden" name="g-recaptcha-response" id="recaptchaToken">

                                    <div class="flex items-baseline justify-between pt-2 border-t border-slate-200">
                                        <span class="text-sm font-bold text-slate-900">Total Bayar</span>
                                        <span id="totalDisplay" class="text-2xl font-extrabold font-mono-num brand-text">Rp 0</span>
                                    </div>

                                    <button type="submit" id="submitBtn" class="w-full py-3.5 px-5 brand-btn font-bold text-base rounded-md transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900 disabled:opacity-60">
                                        Lanjutkan Pembayaran
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif
            </div>
        </section>

        <!-- Logistics: RPC, Venue, What to Bring -->
        @if($hasLogistics)
        <section class="py-16 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-8">Informasi Race Day & Race Pack Collection</h2>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    @if($showSection('rpc'))
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 space-y-2.5">
                            <h3 class="text-base font-bold text-slate-900">Race Pack Collection (RPC)</h3>
                            <p class="text-sm text-slate-800">{{ $event->rpc_location_name ?? ($event->location_name ?? 'Akan diinformasikan panitia') }}</p>
                            @if($event->rpc_location_address)
                                <p class="text-xs text-slate-600">{{ $event->rpc_location_address }}</p>
                            @endif
                            @if(!empty($pa['rpc']['schedule']))
                                <p class="text-xs text-slate-700 pt-2 border-t border-slate-200">{{ $pa['rpc']['schedule'] }}</p>
                            @endif
                        </div>
                    @endif

                    @if($showSection('venue'))
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 space-y-2.5">
                            <h3 class="text-base font-bold text-slate-900">Venue & Parkir</h3>
                            <p class="text-sm text-slate-800">{{ $event->location_name ?? '-' }}</p>
                            @if(!empty($pa['venue']['parking_info']))
                                <p class="text-xs text-slate-700">{{ $pa['venue']['parking_info'] }}</p>
                            @endif
                        </div>
                    @endif

                    @if($showSection('what_to_bring') && count($whatToBring) > 0)
                        <div class="bg-slate-50 border border-slate-200 rounded-lg p-6 space-y-2.5">
                            <h3 class="text-base font-bold text-slate-900">Perlengkapan yang Dibawa</h3>
                            <ul class="space-y-1.5 text-sm text-slate-700 list-disc pl-5">
                                @foreach($whatToBring as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        <!-- Location & Route -->
        @if($hasRouteSection)
        <section id="lokasi" class="py-16 bg-slate-50 border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-5 space-y-3">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Lokasi & Rute</h2>
                        @if($event->location_name)
                            <p class="text-base font-bold text-slate-900">{{ $event->location_name }}</p>
                        @endif
                        @if($event->location_address)
                            <p class="text-sm text-slate-700 leading-relaxed">{{ $event->location_address }}</p>
                        @endif
                    </div>
                    <div class="lg:col-span-7">
                        @if(!empty($event->route_map_image))
                            <img src="{{ asset('storage/' . $event->route_map_image) }}" alt="Peta Rute {{ $event->name }}" width="800" height="450" loading="lazy" class="lightbox-trigger w-full rounded-lg border border-slate-200 cursor-zoom-in">
                        @elseif(!empty($event->map_embed_url))
                            <div class="bg-white border border-slate-200 rounded-lg overflow-hidden aspect-[16/9]">
                                <iframe src="{{ $event->map_embed_url }}" title="Peta Lokasi {{ $event->name }}" class="w-full h-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
        @endif

        <!-- Gallery -->
        @if($showSection('gallery') && count($gallery) > 0)
        <section id="gallery" class="py-16 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-8">Galeri Event</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($gallery as $idx => $imgPath)
                        <button type="button" onclick="openLightbox('{{ asset('storage/' . $imgPath) }}')" class="bg-slate-100 border border-slate-200 rounded-lg overflow-hidden aspect-[4/3] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-900">
                            <img src="{{ asset('storage/' . $imgPath) }}" alt="Galeri {{ $event->name }} {{ $idx + 1 }}" width="400" height="300" loading="lazy" class="w-full h-full object-cover hover:opacity-90 transition duration-150">
                        </button>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        <!-- FAQ (Only real EO FAQ items) -->
        @if($showSection('faq') && count($faqItems) > 0)
        <section id="faq" class="py-16 bg-white border-b border-slate-200">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mb-8">Pertanyaan Umum (FAQ)</h2>
                <div class="space-y-3">
                    @foreach($faqItems as $faq)
                        <details class="bg-slate-50 border border-slate-200 rounded-lg">
                            <summary class="px-5 py-4 text-sm font-bold text-slate-900 list-none flex items-center justify-between gap-4">
                                <span>{{ $faq['question'] }}</span>
                                <span class="text-slate-500 text-xs shrink-0">+</span>
                            </summary>
                            <div class="px-5 pb-4 pt-2 text-sm text-slate-700 border-t border-slate-200 leading-relaxed">
                                {!! nl2br(e($faq['answer'])) !!}
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
    </main>

    <footer class="bg-white border-t border-slate-200 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-600">
            <p class="font-bold text-slate-900">{{ $event->name }}</p>
            <p>&copy; {{ date('Y') }} {{ $event->name }}. Didukung oleh <a href="{{ url('/') }}" class="text-slate-900 font-semibold hover:underline">RuangLari</a>.</p>
        </div>
    </footer>

    <!-- Terms Modal -->
    @if($event->terms_and_conditions)
    <div id="termsModal" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true" aria-labelledby="termsModalTitle">
        <div class="fixed inset-0 bg-slate-950/60" onclick="closeTermsModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="relative bg-white border border-slate-200 rounded-lg w-full max-w-2xl max-h-[80vh] flex flex-col shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 id="termsModalTitle" class="text-base font-bold text-slate-900">Syarat & Ketentuan</h3>
                    <button type="button" onclick="closeTermsModal()" aria-label="Tutup syarat dan ketentuan" class="w-9 h-9 inline-flex items-center justify-center rounded-md text-slate-600 hover:bg-slate-100">
                        <svg class="w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto prose prose-sm max-w-none text-slate-700">
                    {!! $event->terms_and_conditions !!}
                </div>
                <div class="px-6 py-4 border-t border-slate-200">
                    <button type="button" onclick="closeTermsModal()" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-md">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true" aria-label="Pratinjau gambar">
        <div class="fixed inset-0 bg-slate-950/90" onclick="closeLightbox()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <button type="button" onclick="closeLightbox()" aria-label="Tutup pratinjau gambar" class="absolute top-4 right-4 z-20 w-11 h-11 inline-flex items-center justify-center rounded-md bg-white text-slate-900 border border-slate-200 hover:bg-slate-100">
                <svg class="w-6 h-6" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <img id="lightboxImage" src="" alt="Pratinjau gambar penuh" class="relative z-10 max-w-full max-h-[85vh] object-contain rounded-lg border border-slate-700">
        </div>
    </div>

    @include('events.partials.moota-payment-modal', [
        'modalPanelClass' => 'bg-white text-slate-900 border border-slate-200',
        'modalTitleClass' => 'text-slate-900',
        'modalAccentClass' => 'brand-text',
        'modalCloseClass' => 'brand-btn',
    ])
    @include('events.partials.floating-whatsapp')

    <script>
        (function() {
            const eventId = {{ (int) $event->id }};
            const platformFee = {{ (int) ($event->platform_fee ?? 0) }};
            const manualTransferWaiveFee = {{ $manualTransferWaiveFee ? 'true' : 'false' }};
            const promoBuyX = {{ (int) ($event->promo_buy_x ?? 0) }};
            const recaptchaSiteKey = @json(env('RECAPTCHA_SITE_KEY_v3') ?: '');
            const formatRp = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(num || 0));

            // Mobile menu
            const navToggle = document.getElementById('navToggle');
            const mobileMenu = document.getElementById('mobileMenu');
            if (navToggle && mobileMenu) {
                navToggle.addEventListener('click', () => {
                    const isOpen = !mobileMenu.classList.contains('hidden');
                    mobileMenu.classList.toggle('hidden');
                    navToggle.setAttribute('aria-expanded', String(!isOpen));
                });
                mobileMenu.querySelectorAll('.mobile-nav-link').forEach(link => {
                    link.addEventListener('click', () => {
                        mobileMenu.classList.add('hidden');
                        navToggle.setAttribute('aria-expanded', 'false');
                    });
                });
            }

            // Modals + Escape
            window.openTermsModal = function() {
                const m = document.getElementById('termsModal');
                if (m) m.classList.remove('hidden');
            };
            window.closeTermsModal = function() {
                const m = document.getElementById('termsModal');
                if (m) m.classList.add('hidden');
            };
            window.openLightbox = function(src) {
                const m = document.getElementById('lightboxModal');
                const img = document.getElementById('lightboxImage');
                if (m && img && src) {
                    img.src = src;
                    m.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            };
            window.closeLightbox = function() {
                const m = document.getElementById('lightboxModal');
                const img = document.getElementById('lightboxImage');
                if (m) m.classList.add('hidden');
                if (img) img.src = '';
                document.body.style.overflow = '';
            };
            document.querySelectorAll('.lightbox-trigger').forEach(el => {
                el.addEventListener('click', () => window.openLightbox(el.getAttribute('src')));
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    window.closeTermsModal();
                    window.closeLightbox();
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                        if (navToggle) navToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            });

            // Optional countdown (when no hero image)
            @if($event->start_at && !$heroImg)
            const targetTs = new Date(@json($event->start_at->toIso8601String())).getTime();
            const cdTimer = document.getElementById('countdown-timer');
            if (cdTimer && !Number.isNaN(targetTs)) {
                const tick = () => {
                    const diff = targetTs - Date.now();
                    if (diff <= 0) {
                        cdTimer.innerHTML = '<div class="col-span-4 text-sm font-bold text-white py-2">Race Day Dimulai</div>';
                        return false;
                    }
                    document.getElementById('cd-days').textContent = String(Math.floor(diff / 86400000)).padStart(2, '0');
                    document.getElementById('cd-hours').textContent = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
                    document.getElementById('cd-minutes').textContent = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
                    document.getElementById('cd-seconds').textContent = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
                    return true;
                };
                if (tick()) setInterval(tick, 1000);
            }
            @endif

            // Registration form state
            const wrapper = document.getElementById('participantsWrapper');
            const addBtn = document.getElementById('addParticipantBtn');
            const firstItem = wrapper ? wrapper.querySelector('.participant-item') : null;
            const template = firstItem ? firstItem.cloneNode(true) : null;
            let discountAmount = 0;

            function reindexParticipants() {
                if (!wrapper) return;
                const items = wrapper.querySelectorAll('.participant-item');
                items.forEach((item, idx) => {
                    item.dataset.index = String(idx);
                    const title = item.querySelector('.participant-title');
                    if (title) title.textContent = `Peserta #${idx + 1}`;
                    const removeBtn = item.querySelector('.remove-participant');
                    if (removeBtn) removeBtn.classList.toggle('hidden', items.length <= 1);
                    const copyPrevBtn = item.querySelector('.copy-prev-btn');
                    if (copyPrevBtn) copyPrevBtn.classList.toggle('hidden', idx === 0);

                    item.querySelectorAll('input, select, textarea').forEach(el => {
                        const name = el.getAttribute('name');
                        if (name && name.includes('participants[')) {
                            el.setAttribute('name', name.replace(/participants\[\d+\]/, `participants[${idx}]`));
                        }
                    });
                });
            }

            function resetCoupon() {
                if (discountAmount > 0) {
                    discountAmount = 0;
                    const cIn = document.getElementById('coupon_code');
                    const cHid = document.getElementById('coupon_code_hidden');
                    const cMsg = document.getElementById('couponMessage');
                    if (cIn) cIn.value = '';
                    if (cHid) cHid.value = '';
                    if (cMsg) cMsg.textContent = '';
                }
            }

            function computeSubtotal() {
                const catCounts = new Map();
                const catPrices = new Map();
                let count = 0;
                let addonsTotal = 0;

                if (!wrapper) return { subtotal: 0, count: 0 };

                wrapper.querySelectorAll('.participant-item').forEach(item => {
                    const checked = item.querySelector('.cat-radio:checked');
                    if (checked) {
                        count++;
                        const cid = String(checked.value);
                        const price = parseFloat(checked.dataset.price || '0');
                        catCounts.set(cid, (catCounts.get(cid) || 0) + 1);
                        catPrices.set(cid, price);
                    }
                    item.querySelectorAll('.addon-checkbox:checked').forEach(cb => {
                        addonsTotal += parseFloat(cb.dataset.addonPrice || '0');
                    });
                });

                let ticketSubtotal = 0;
                catCounts.forEach((qty, cid) => {
                    const price = catPrices.get(cid) || 0;
                    let paidQty = qty;
                    if (promoBuyX > 0) {
                        paidQty = qty - Math.floor(qty / (promoBuyX + 1));
                    }
                    ticketSubtotal += price * paidQty;
                });

                return { subtotal: ticketSubtotal + addonsTotal, count };
            }

            function updatePrice() {
                const { subtotal, count } = computeSubtotal();
                const payMethod = document.querySelector('input[name="payment_method"]:checked')?.value || '';
                const feeWaived = manualTransferWaiveFee && payMethod === 'manual_transfer';
                const totalFee = (subtotal - discountAmount <= 0 || feeWaived) ? 0 : (count * platformFee);
                const total = Math.max(0, subtotal + totalFee - discountAmount);

                const subEl = document.getElementById('subtotalDisplay');
                const feeEl = document.getElementById('feeDisplay');
                const feeRow = document.getElementById('feeRow');
                const discRow = document.getElementById('discountRow');
                const discEl = document.getElementById('discountDisplay');
                const totEl = document.getElementById('totalDisplay');

                if (subEl) subEl.textContent = formatRp(subtotal);
                if (feeEl) feeEl.textContent = formatRp(totalFee);
                if (feeRow) feeRow.classList.toggle('hidden', platformFee <= 0);
                if (discRow) discRow.classList.toggle('hidden', discountAmount <= 0);
                if (discEl) discEl.textContent = '-' + formatRp(discountAmount);
                if (totEl) totEl.textContent = formatRp(total);
            }

            function bindParticipantEvents(item) {
                item.querySelectorAll('.cat-radio, .addon-checkbox').forEach(el => {
                    el.addEventListener('change', () => {
                        resetCoupon();
                        updatePrice();
                    });
                });
            }

            if (firstItem) bindParticipantEvents(firstItem);

            if (addBtn && template && wrapper) {
                addBtn.addEventListener('click', () => {
                    resetCoupon();
                    const clone = template.cloneNode(true);
                    clone.querySelectorAll('input, select, textarea').forEach(el => {
                        if (el.type === 'checkbox' || el.type === 'radio') el.checked = false;
                        else if (el.type !== 'hidden') el.value = '';
                    });
                    wrapper.appendChild(clone);
                    reindexParticipants();
                    bindParticipantEvents(clone);
                    updatePrice();
                });

                wrapper.addEventListener('click', (e) => {
                    if (e.target.classList.contains('remove-participant')) {
                        if (wrapper.querySelectorAll('.participant-item').length > 1) {
                            e.target.closest('.participant-item').remove();
                            resetCoupon();
                            reindexParticipants();
                            updatePrice();
                        }
                    }
                });
            }

            document.querySelectorAll('input[name="payment_method"]').forEach(r => r.addEventListener('change', updatePrice));

            window.selectCategoryAndScroll = function(categoryId) {
                const regSec = document.getElementById('register');
                if (regSec) regSec.scrollIntoView({ behavior: 'smooth' });
                const radio = wrapper ? wrapper.querySelector(`.participant-item .cat-radio[value="${categoryId}"]`) : null;
                if (radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            };

            window.copyFromPic = function(btn) {
                const item = btn.closest('.participant-item');
                if (!item) return;
                const map = {
                    name: document.getElementById('pic_name')?.value || '',
                    email: document.getElementById('pic_email')?.value || '',
                    phone: document.getElementById('pic_phone')?.value || '',
                };
                Object.entries(map).forEach(([field, val]) => {
                    const input = item.querySelector(`[data-field="${field}"]`);
                    if (input && val) input.value = val;
                });
            };

            window.copyFromPrev = function(btn) {
                const item = btn.closest('.participant-item');
                const prev = item ? item.previousElementSibling : null;
                if (!item || !prev) return;
                ['emergency_contact_name', 'emergency_contact_number', 'emergency_contact_relation'].forEach(field => {
                    const prevEl = prev.querySelector(`[data-field="${field}"]`);
                    const currEl = item.querySelector(`[data-field="${field}"]`);
                    if (prevEl && currEl && prevEl.value) currEl.value = prevEl.value;
                });
            };

            // Coupon
            const couponBtn = document.getElementById('applyCouponBtn');
            if (couponBtn) {
                couponBtn.addEventListener('click', async () => {
                    const code = (document.getElementById('coupon_code')?.value || '').trim();
                    const msgEl = document.getElementById('couponMessage');
                    const { subtotal } = computeSubtotal();
                    if (!code) {
                        if (msgEl) msgEl.innerHTML = '<span class="text-rose-600">Masukkan kode promo terlebih dahulu.</span>';
                        return;
                    }
                    if (subtotal <= 0) {
                        if (msgEl) msgEl.innerHTML = '<span class="text-rose-600">Pilih kategori peserta terlebih dahulu.</span>';
                        return;
                    }
                    couponBtn.disabled = true;
                    couponBtn.textContent = '...';
                    try {
                        const res = await fetch(`{{ route('events.register.coupon', $event->slug) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ event_id: eventId, coupon_code: code, total_amount: subtotal })
                        });
                        const data = await res.json();
                        if (data.success) {
                            discountAmount = parseFloat(data.discount_amount || '0');
                            document.getElementById('coupon_code_hidden').value = data.coupon.code;
                            document.getElementById('coupon_code').value = data.coupon.code;
                            if (msgEl) msgEl.innerHTML = '<span class="text-emerald-700 font-semibold">Kupon berhasil diterapkan.</span>';
                        } else {
                            discountAmount = 0;
                            document.getElementById('coupon_code_hidden').value = '';
                            if (msgEl) msgEl.innerHTML = `<span class="text-rose-600">${data.message || 'Kupon tidak valid.'}</span>`;
                        }
                        updatePrice();
                    } catch (err) {
                        if (msgEl) msgEl.innerHTML = '<span class="text-rose-600">Gagal memeriksa kupon. Coba lagi.</span>';
                    } finally {
                        couponBtn.disabled = false;
                        couponBtn.textContent = 'Pakai';
                    }
                });
            }

            // Form Submit
            const form = document.getElementById('registrationForm');
            const alertBanner = document.getElementById('formAlertBanner');
            function showFormBanner(type, message) {
                if (!alertBanner) return;
                alertBanner.className = type === 'error'
                    ? 'mb-6 rounded-md p-4 text-sm border bg-rose-50 border-rose-300 text-rose-900'
                    : 'mb-6 rounded-md p-4 text-sm border bg-emerald-50 border-emerald-300 text-emerald-900';
                alertBanner.textContent = message;
                alertBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    if (alertBanner) alertBanner.classList.add('hidden');

                    form.querySelectorAll('.border-rose-500').forEach(el => {
                        el.classList.remove('border-rose-500');
                        el.classList.add('border-slate-300');
                    });

                    let firstInvalid = null;
                    const markInvalid = (el) => {
                        el.classList.remove('border-slate-300');
                        el.classList.add('border-rose-500');
                        if (!firstInvalid) firstInvalid = el;
                    };

                    // Check category radio per participant
                    let missingCat = false;
                    wrapper.querySelectorAll('.participant-item').forEach(item => {
                        if (!item.querySelector('.cat-radio:checked')) {
                            missingCat = true;
                            const firstRadio = item.querySelector('.cat-radio');
                            if (!firstInvalid && firstRadio) firstInvalid = item;
                        }
                    });

                    form.querySelectorAll('[required]').forEach(el => {
                        if (el.type === 'radio') return;
                        if (el.type === 'checkbox') {
                            if (!el.checked) markInvalid(el);
                        } else if (!String(el.value || '').trim()) {
                            markInvalid(el);
                        }
                    });

                    let ageErrorMsg = '';
                    const refStr = "{{ $event->start_at ? $event->start_at->format('Y-m-d') : now()->format('Y-m-d') }}";
                    const [ry, rm, rd] = refStr.split('-').map(Number);
                    wrapper?.querySelectorAll('.participant-item').forEach((item, idx) => {
                        const radio = item.querySelector('.cat-radio:checked');
                        const dobEl = item.querySelector('[data-field="date_of_birth"]');
                        if (!radio || !dobEl || !dobEl.value) return;
                        const minAgeAttr = radio.getAttribute('data-min-age');
                        const maxAgeAttr = radio.getAttribute('data-max-age');
                        const catNameAttr = radio.getAttribute('data-cat-name') || 'kategori ini';
                        const minAge = minAgeAttr !== null && minAgeAttr !== '' ? parseInt(minAgeAttr, 10) : null;
                        const maxAge = maxAgeAttr !== null && maxAgeAttr !== '' ? parseInt(maxAgeAttr, 10) : null;
                        if (minAge === null && maxAge === null) return;
                        const [by, bm, bd] = dobEl.value.split('-').map(Number);
                        let age = ry - by;
                        if (rm < bm || (rm === bm && rd < bd)) age--;
                        if (age < 0) age = 0;
                        if (minAge !== null && age < minAge) {
                            markInvalid(dobEl);
                            if (!ageErrorMsg) ageErrorMsg = `Peserta #${idx + 1}: Usia (${age} tahun) kurang dari batas minimal ${catNameAttr} (${minAge} tahun).`;
                        } else if (maxAge !== null && age > maxAge) {
                            markInvalid(dobEl);
                            if (!ageErrorMsg) ageErrorMsg = `Peserta #${idx + 1}: Usia (${age} tahun) melebihi batas maksimal ${catNameAttr} (${maxAge} tahun).`;
                        }
                    });

                    if (firstInvalid) {
                        const msg = missingCat
                            ? 'Pilih kategori lomba untuk setiap peserta.'
                            : (ageErrorMsg || 'Mohon lengkapi semua kolom wajib yang ditandai merah.');
                        showFormBanner('error', msg);
                        if (typeof firstInvalid.focus === 'function') firstInvalid.focus();
                        return;
                    }

                    const submitBtn = document.getElementById('submitBtn');
                    const originalText = submitBtn.textContent;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Memproses pendaftaran...';

                    const sendRequest = async () => {
                        try {
                            const res = await fetch(form.action, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: new FormData(form)
                            });
                            const data = await res.json();
                            if (data.success) {
                                if (data.snap_token && typeof window.snap !== 'undefined') {
                                    window.snap.pay(data.snap_token, {
                                        onSuccess: () => { window.location.href = `{{ route('events.show', $event->slug) }}?payment=success`; },
                                        onPending: () => { window.location.href = `{{ route('events.show', $event->slug) }}?payment=pending`; },
                                        onError: () => {
                                            showFormBanner('error', 'Pembayaran gagal diproses. Silakan coba lagi.');
                                            submitBtn.disabled = false;
                                            submitBtn.textContent = originalText;
                                        },
                                        onClose: () => {
                                            submitBtn.disabled = false;
                                            submitBtn.textContent = originalText;
                                        }
                                    });
                                } else if (data.payment_gateway === 'moota' && window.RuangLariMoota && typeof window.RuangLariMoota.open === 'function' && data.transaction_id) {
                                    submitBtn.disabled = false;
                                    submitBtn.textContent = originalText;
                                    window.RuangLariMoota.open({
                                        transaction_id: data.transaction_id,
                                        registration_id: data.registration_id,
                                        final_amount: data.final_amount,
                                        unique_code: data.unique_code,
                                        phone: document.getElementById('pic_phone')?.value || '',
                                        name: document.getElementById('pic_name')?.value || '',
                                    });
                                } else if (data.redirect_url) {
                                    window.location.href = data.redirect_url;
                                } else {
                                    window.location.href = `{{ route('events.show', $event->slug) }}?payment=success`;
                                }
                            } else {
                                let msg = data.message || 'Validasi gagal. Periksa kembali data Anda.';
                                if (data.errors) {
                                    const firstErr = Object.values(data.errors).flat()[0];
                                    if (firstErr) msg = firstErr;
                                }
                                showFormBanner('error', msg);
                                submitBtn.disabled = false;
                                submitBtn.textContent = originalText;
                            }
                        } catch (err) {
                            showFormBanner('error', 'Gagal terhubung ke server. Periksa koneksi internet Anda dan coba lagi.');
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalText;
                        }
                    };

                    if (recaptchaSiteKey && typeof grecaptcha !== 'undefined' && typeof grecaptcha.ready === 'function') {
                        let done = false;
                        const timer = setTimeout(() => { if (!done) { done = true; sendRequest(); } }, 4000);
                        grecaptcha.ready(() => {
                            grecaptcha.execute(recaptchaSiteKey, { action: 'event_register' })
                                .then(token => {
                                    if (done) return;
                                    done = true;
                                    clearTimeout(timer);
                                    const tokEl = document.getElementById('recaptchaToken');
                                    if (tokEl) tokEl.value = token;
                                    sendRequest();
                                })
                                .catch(() => {
                                    if (done) return;
                                    done = true;
                                    clearTimeout(timer);
                                    sendRequest();
                                });
                        });
                    } else {
                        sendRequest();
                    }
                });
            }
        })();
    </script>
</body>
</html>
