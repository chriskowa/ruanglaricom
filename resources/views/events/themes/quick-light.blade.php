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
    $faviconImage = $logoImage ?? $heroImage;
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
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }} • Ruang Lari</title>
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.tailwindcss.com"></script>
    @if($showMidtrans && $midtransClientKey)
        <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
    @endif
    @if(env('RECAPTCHA_SITE_KEY_v3'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}" onerror="this.onerror=null;this.src='https://www.recaptcha.net/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}';"></script>
    @endif
    <style>
        :root { --primary:#f1631e; --primary-dark:#d94f0b; --ink:#0f172a; --muted:#64748b; --line:#dbe4f0; --bg:#f6f9fc; }
        html { scroll-behavior:smooth; }
        body { background:linear-gradient(180deg,#f8fbff 0%,#f5f7fb 100%); color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif; }
        .glass { background:rgba(255,255,255,.85); backdrop-filter:blur(18px); }
        .soft-card { border:1px solid rgba(148,163,184,.18); box-shadow:0 10px 40px rgba(15,23,42,.08); border-radius:28px; background:#fff; }
        .no-scrollbar { scrollbar-width:none; }
        .no-scrollbar::-webkit-scrollbar { display:none; }
        .field { width:100%; border:1px solid #dbe4f0; border-radius:18px; padding:15px 16px; font-weight:600; color:#0f172a; background:#fff; outline:none; transition:.18s ease; }
        .field:focus { border-color:var(--primary); box-shadow:0 0 0 4px rgba(241,99,30,.12); }
        .pill { display:inline-flex; align-items:center; gap:.5rem; border-radius:999px; padding:.55rem .9rem; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .choice input { position:absolute; opacity:0; pointer-events:none; }
        .choice-box { border:1px solid #dbe4f0; border-radius:20px; padding:14px 16px; background:#fff; transition:.18s ease; }
        .choice input:checked + .choice-box { border-color:var(--primary); background:linear-gradient(180deg,#fff7ed 0%,#ffffff 100%); box-shadow:0 0 0 4px rgba(241,99,30,.08); }
        .submit-btn[disabled] { opacity:.7; cursor:not-allowed; }
        .content-html p { margin-top:0; margin-bottom:1rem; line-height:1.75; color:#334155; }
        .content-html h1, .content-html h2, .content-html h3, .content-html h4 { color:#0f172a; font-weight:800; margin-top:1.4rem; margin-bottom:.8rem; }
        .content-html ul, .content-html ol { padding-left:1.25rem; color:#334155; margin-bottom:1rem; }
        .hero-overlay { background:linear-gradient(110deg, rgba(15,23,42,.72) 0%, rgba(15,23,42,.48) 35%, rgba(255,255,255,.05) 100%); }
        .ql-gallery-dot { width:8px; height:8px; border-radius:999px; background:rgba(255,255,255,.45); transition:transform .15s ease, background .15s ease; }
        .ql-gallery-dot[data-active="1"] { background:#fff; transform:scale(1.25); }
        #qlSponsorDots .ql-gallery-dot { background:rgba(15,23,42,.18); }
        #qlSponsorDots .ql-gallery-dot[data-active="1"] { background:var(--primary); }
        @if(env('RECAPTCHA_SITE_KEY_v3'))
        .grecaptcha-badge { visibility:hidden !important; }
        @endif
    </style>
</head>
<body class="overflow-x-hidden max-w-full">
    <section class="relative overflow-hidden w-full max-w-full">
        <div class="absolute inset-0">
            <img src="{{ $heroImage }}" alt="{{ $event->name }}" class="w-full h-full object-cover" fetchpriority="high" decoding="async">
            <div class="absolute inset-0 hero-overlay"></div>
        </div>
        <div class="relative w-full max-w-7xl mx-auto px-5 sm:px-6 md:px-8 pt-10 pb-24 lg:pt-16 lg:pb-28">
            <div class="flex items-start justify-between gap-6 mb-12">
                <div class="pill bg-white/12 text-white border border-white/20">
                    <i class="fa-solid fa-bolt"></i>
                    Ruang Lari Portal Registration
                </div>
                @if($event->logo_image)
                    <img src="{{ asset('storage/'.$event->logo_image) }}" alt="{{ $event->name }}" class="h-12 md:h-16 w-auto object-contain rounded-2xl bg-white/80 p-2" loading="lazy" decoding="async">
                @endif
            </div>

            <div class="grid lg:grid-cols-[1.15fr_.85fr] gap-8 items-end">
                <div class="text-white max-w-3xl">
                    <div class="flex flex-wrap gap-3 mb-5">
                        <span class="pill bg-white/12 text-white border border-white/20">
                            <i class="fa-regular fa-calendar"></i>
                            {{ optional($event->start_at)->format('d F Y') ?: 'Tanggal menyusul' }}
                        </span>
                        <span class="pill bg-white/12 text-white border border-white/20">
                            <i class="fa-solid fa-location-dot"></i>
                            {{ $event->location_name ?: 'Lokasi menyusul' }}
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-black leading-[1.02] tracking-tight">{{ $event->name }}</h1>
                    <p class="mt-5 text-base md:text-lg text-white/85 leading-8 max-w-2xl">
                        {{ $shortDescription !== '' ? $shortDescription : 'Template pendaftaran cepat dengan tampilan modern, ringan, dan fokus konversi untuk peserta.' }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#register" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-white text-slate-900 font-extrabold shadow-xl hover:-translate-y-0.5 transition">
                            <i class="fa-solid fa-rocket"></i>
                            Daftar Sekarang
                        </a>
                        <a href="#detail-event" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-white/12 border border-white/20 text-white font-bold hover:bg-white/20 transition">
                            <i class="fa-solid fa-circle-info"></i>
                            Lihat Detail
                        </a>
                    </div>
                </div>

                <div class="glass rounded-[28px] border border-white/40 shadow-2xl p-5 md:p-6">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white p-4 border border-slate-200">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Running</div>
                            <div class="mt-2 text-sm font-extrabold text-slate-900">
                                Habbit 
                            </div>
                        </div>
                        <div class="rounded-2xl bg-white p-4 border border-slate-200">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">What to Bring</div>
                            <div class="mt-2 text-sm font-extrabold text-slate-900">Running Gear • e-Tiket</div>
                        </div>
                        <div class="rounded-2xl bg-white p-4 border border-slate-200">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Kategori</div>
                            <div class="mt-2 text-sm font-extrabold text-slate-900">{{ $categories->count() }} pilihan</div>
                        </div>
                        <div class="rounded-2xl bg-white p-4 border border-slate-200">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Registrasi</div>
                            <div class="mt-2 text-sm font-extrabold {{ $isRegOpen ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $isRegOpen ? 'Sedang Dibuka' : 'Ditutup' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="max-w-7xl mx-auto px-5 sm:px-6 md:px-8 -mt-12 pb-16 relative z-10 w-full">
        @if(count($sponsorUrls) > 0)
            <section class="soft-card p-5 md:p-6 mb-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-xs md:text-sm font-black uppercase tracking-[0.2em] text-[#f1631e]">Sponsors</div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="qlSponsorPrev" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-900 hover:bg-slate-50 transition" aria-label="Sebelumnya">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button type="button" id="qlSponsorNext" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 shadow-sm flex items-center justify-center text-slate-900 hover:bg-slate-50 transition" aria-label="Berikutnya">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div id="qlSponsorTrack" class="mt-4 flex gap-4 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-2 no-scrollbar md:justify-center">
                    @foreach($sponsorUrls as $logo)
                        <div class="snap-center flex-none w-full sm:w-1/2 md:w-1/3 lg:w-1/4">
                            <div class="h-20 md:h-24 rounded-2xl border border-slate-200 bg-white flex items-center justify-center p-4">
                                <img src="{{ $logo }}" alt="Sponsor {{ $event->name }}" class="max-h-full max-w-full object-contain" loading="lazy" decoding="async">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div id="qlSponsorDots" class="mt-3 flex items-center justify-center gap-2"></div>
            </section>
        @endif

        @if(request('payment') === 'pending')
            <div class="soft-card p-5 md:p-6 mb-6 border-yellow-200 bg-yellow-50">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-yellow-100 text-yellow-700 flex items-center justify-center text-xl"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <div class="text-lg font-black text-slate-900">Menunggu pembayaran</div>
                        <div class="mt-1 text-slate-600">Transaksi kamu belum selesai. Jika popup sebelumnya tertutup, lanjutkan dari tombol di bawah.</div>
                        <a href="{{ route('events.payments.continue', $event->slug) }}" class="inline-flex mt-4 items-center gap-2 px-4 py-3 rounded-2xl bg-yellow-400 text-black font-extrabold hover:bg-yellow-300 transition">
                            Lanjutkan Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        @elseif(request('payment') === 'success')
            <div class="soft-card p-5 md:p-6 mb-6 border-emerald-200 bg-emerald-50">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <div class="text-lg font-black text-slate-900">Pendaftaran berhasil</div>
                        <div class="mt-1 text-slate-600">Cek email kamu untuk detail transaksi dan voucher event.</div>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid lg:grid-cols-[1.05fr_.95fr] gap-6">
            <section id="detail-event" class="space-y-6 w-full max-w-full overflow-hidden">
                <div class="soft-card overflow-hidden">
                    <div class="grid md:grid-cols-[1.1fr_.9fr]">
                        <div class="p-6 md:p-8 lg:p-10">
                            <div class="text-sm font-black uppercase tracking-[0.2em] text-[#f1631e]">Event Overview</div>
                            <h2 class="mt-3 text-2xl md:text-3xl font-black tracking-tight text-slate-900">Pendaftaran cepat</h2>
                            <div class="mt-5 grid sm:grid-cols-3 gap-3">
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="text-xs font-bold uppercase text-slate-500">Tanggal</div>
                                    <div class="mt-2 text-sm font-extrabold text-slate-900">{{ optional($event->start_at)->format('d M Y') ?: '-' }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="text-xs font-bold uppercase text-slate-500">Jam</div>
                                    <div class="mt-2 text-sm font-extrabold text-slate-900">{{ optional($event->start_at)->format('H:i') ?: '-' }}</div>
                                </div>
                                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                                    <div class="text-xs font-bold uppercase text-slate-500">Lokasi</div>
                                    <div class="mt-2 text-sm font-extrabold text-slate-900">{{ $event->location_name ?: '-' }}</div>
                                </div>
                            </div>
                            @if($event->location_address)
                                <div class="mt-5 rounded-2xl bg-orange-50 border border-orange-100 p-4 text-sm text-slate-700 leading-7">
                                    <div class="font-extrabold text-slate-900 mb-1">Alamat</div>
                                    {{ $event->location_address }}
                                </div>
                            @endif
                        </div>
                        <div class="min-h-[280px] relative" id="qlGallery" data-images='@json($galleryUrls)'>
                            <img id="qlGalleryImg" src="{{ $galleryUrls[0] }}" alt="{{ $event->name }}" class="w-full h-full object-cover" fetchpriority="high" decoding="async">
                            <button type="button" id="qlGalleryPrev" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-2xl bg-white/85 hover:bg-white border border-white/40 shadow-lg flex items-center justify-center text-slate-900 transition">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button" id="qlGalleryNext" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-2xl bg-white/85 hover:bg-white border border-white/40 shadow-lg flex items-center justify-center text-slate-900 transition">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                            <div id="qlGalleryDots" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 px-3 py-2 rounded-2xl bg-slate-900/40 border border-white/15 backdrop-blur-md"></div>
                        </div>
                    </div>
                </div>

                @if($event->full_description)
                    <div class="soft-card p-6 md:p-8">
                        <div class="text-sm font-black uppercase tracking-[0.2em] text-[#f1631e]">Description</div>
                        <div class="content-html mt-4 text-slate-700">
                            {!! $event->full_description !!}
                        </div>
                    </div>
                @elseif($event->short_description)
                    <div class="soft-card p-6 md:p-8">
                        <div class="text-sm font-black uppercase tracking-[0.2em] text-[#f1631e]">Description</div>
                        <p class="mt-4 text-slate-700 leading-8">{{ $event->short_description }}</p>
                    </div>
                @endif

                {{-- Rute Section --}}
                <div class="soft-card p-6 md:p-8">
                    <div class="text-sm font-black uppercase tracking-[0.2em] text-[#f1631e]">Rute</div>
                    <div class="mt-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-900">Peta Rute Lari</h3>
                            <p class="text-sm text-slate-500 mt-1">Jelajahi jalur rute lari, check point, pos medis, dan detail elevasi event ini.</p>
                        </div>
                        <div>
                            <button type="button" onclick="openRouteModal()" class="inline-flex items-center gap-2 rounded-2xl border border-orange-200 bg-orange-50 px-5 py-3 text-sm font-black text-[#f1631e] hover:bg-orange-100 transition whitespace-nowrap w-full md:w-auto justify-center">
                                <i class="fa-solid fa-map-location-dot"></i>
                                Lihat Peta Rute
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Terms & Conditions Section --}}
                @if($event->terms_and_conditions)
                    <div class="soft-card p-6 md:p-8">
                        <div class="text-sm font-black uppercase tracking-[0.2em] text-[#f1631e]">Syarat & Ketentuan</div>
                        <div class="mt-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-black text-slate-900">Peraturan & Ketentuan Event</h3>
                                <p class="text-sm text-slate-500 mt-1">Baca syarat, ketentuan, serta peraturan keselamatan sebelum mendaftar.</p>
                            </div>
                            <div>
                                <button type="button" onclick="document.getElementById('termsModal').classList.remove('hidden')" class="inline-flex items-center gap-2 rounded-2xl border border-orange-200 bg-orange-50 px-5 py-3 text-sm font-black text-[#f1631e] hover:bg-orange-100 transition whitespace-nowrap w-full md:w-auto justify-center">
                                    <i class="fa-solid fa-file-shield"></i>
                                    Lihat Syarat & Ketentuan
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
                    <div class="soft-card p-6 md:p-8" id="participants-list">
                        <div class="text-sm font-black uppercase tracking-[0.2em] text-[#f1631e]">Daftar Peserta</div>
                        <div class="mt-4" id="vue-participants-app">
                            @include('events.partials.participants-table-light')
                        </div>
                    </div>
                @endif
            </section>

            <aside id="register" class="lg:sticky lg:top-24 self-start">
                <div class="soft-card overflow-hidden">
                    <div class="px-6 md:px-7 py-6 border-b border-slate-200 bg-gradient-to-r from-[#f1631e] to-[#ff8a3d] text-white">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <div class="text-sm font-black uppercase tracking-[0.2em] text-white/80">Quick Registration</div>
                                <h3 class="mt-1 text-2xl font-black">Daftar dalam 1 menit</h3>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center text-xl">
                                <i class="fa-solid fa-feather-pointed"></i>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-7">
                        @if(!$isRegOpen)
                            <div class="rounded-3xl border border-slate-200 bg-slate-50 p-8 text-center">
                                <div class="w-16 h-16 mx-auto rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-2xl">
                                    <i class="fa-solid fa-lock"></i>
                                </div>
                                <div class="mt-4 text-xl font-black text-slate-900">Pendaftaran ditutup</div>
                                <div class="mt-2 text-slate-600">Saat ini pendaftaran belum tersedia untuk event ini.</div>
                            </div>
                        @else
                            <form id="quickForm" action="{{ route('events.register.store', ['slug' => $event->slug]) }}" method="POST" class="space-y-5">
                                @csrf
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Peserta</div>
                                        <div class="mt-1 text-sm text-slate-600">Bisa daftar beberapa peserta sekaligus dalam satu transaksi.</div>
                                    </div>
                                    <button id="addParticipantBtn" type="button" class="inline-flex items-center gap-2 rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-black text-[#f1631e] hover:bg-orange-100 transition">
                                        <i class="fa-solid fa-plus"></i>
                                        Tambah Peserta
                                    </button>
                                </div>

                                <div id="participantsWrapper" class="space-y-5">
                                    <div class="participant-card rounded-[24px] border border-slate-200 bg-slate-50/80 p-5" data-participant-item>
                                        <div class="flex items-center justify-between gap-4 pb-4 border-b border-slate-200">
                                            <div>
                                                <div class="participant-title text-base font-black text-slate-900">Peserta 1</div>
                                                <div class="text-xs text-slate-500 mt-1">Isi data ringkas, sistem melengkapi data wajib otomatis.</div>
                                            </div>
                                            <button type="button" class="remove-participant hidden rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-black text-rose-600 hover:bg-rose-100 transition">
                                                Hapus
                                            </button>
                                        </div>

                                        <div class="mt-5 space-y-5">
                                            <div>
                                                <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Nama</label>
                                                <input class="field mt-2" data-field="name" name="participants[0][name]" placeholder="Nama  peserta" required>
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Gender</label>
                                                    <select class="field mt-2" data-field="gender" name="participants[0][gender]" required>
                                                        <option value="">Pilih gender</option>
                                                        <option value="male">Laki-laki</option>
                                                        <option value="female">Perempuan</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">No. HP</label>
                                                    <input class="field mt-2" data-field="phone" name="participants[0][phone]" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Email</label>
                                                <input class="field mt-2" type="email" data-field="email" name="participants[0][email]" placeholder="email@contoh.com" required>
                                            </div>

                                            @if($categories && $categories->count() > 0)
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Kategori</label>
                                                    <div class="mt-3 grid gap-3">
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
                                                                <div class="choice-box flex items-center justify-between gap-4">
                                                                    <div>
                                                                        <div class="text-sm font-black text-slate-900">{{ $cat->name }}</div>
                                                                        @if($cat->distance_km)
                                                                            <div class="mt-1 text-xs font-semibold text-slate-500">{{ number_format($cat->distance_km, 0, ',', '.') }}K</div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="text-right">
                                                                        @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                            <div class="text-[11px] text-slate-400 line-through">Rp {{ number_format($priceRegular, 0, ',', '.') }}</div>
                                                                        @endif
                                                                        <div class="text-sm font-black text-[#f1631e]">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if(!empty($addons))
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Add-on</label>
                                                    <div class="mt-3 grid gap-3">
                                                        @foreach($addons as $idx => $addon)
                                                            <label class="choice relative block cursor-pointer">
                                                                <input type="checkbox" data-addon-index="{{ $idx }}" name="participants[0][addons][{{ $idx }}][selected]" value="1" data-addon-price="{{ (int) ($addon['price'] ?? 0) }}">
                                                                <div class="choice-box flex items-center justify-between gap-4">
                                                                    <div>
                                                                        <div class="text-sm font-black text-slate-900">{{ $addon['name'] }}</div>
                                                                        <div class="mt-1 text-xs text-slate-500">Opsional</div>
                                                                    </div>
                                                                    <div class="text-sm font-black text-slate-900">
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
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">No. Identitas (KTP/SIM)</label>
                                                    <input class="field mt-2" data-field="id_card" name="participants[0][id_card]" placeholder="Nomor KTP/SIM" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="id_card" name="participants[0][id_card]">
                                            @endif

                                            @if($showAddress)
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Alamat Lengkap</label>
                                                    <textarea class="field mt-2" data-field="address" name="participants[0][address]" placeholder="Alamat lengkap" rows="2" required></textarea>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="address" name="participants[0][address]">
                                            @endif

                                            @if($showEmergency)
                                                <div class="grid sm:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Nama Kontak Darurat</label>
                                                        <input class="field mt-2" data-field="emergency_contact_name" name="participants[0][emergency_contact_name]" placeholder="Nama kontak darurat" required>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">No. HP Kontak Darurat</label>
                                                        <input class="field mt-2" data-field="emergency_contact_number" name="participants[0][emergency_contact_number]" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="emergency_contact_name" name="participants[0][emergency_contact_name]">
                                                <input type="hidden" data-hidden-auto="emergency_contact_number" name="participants[0][emergency_contact_number]">
                                            @endif

                                            @if($showDob)
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Tanggal Lahir</label>
                                                    <input type="date" class="field mt-2" data-field="date_of_birth" name="participants[0][date_of_birth]" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="date_of_birth" name="participants[0][date_of_birth]">
                                            @endif

                                            @if($showJersey)
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Ukuran Jersey</label>
                                                    <select class="field mt-2" data-field="jersey_size" name="participants[0][jersey_size]" required>
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
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Personal Best</label>
                                                    <div class="grid grid-cols-3 gap-2 mt-2">
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
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Golongan Darah</label>
                                                    <select class="field mt-2" data-field="blood_type" name="participants[0][blood_type]" required>
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

                                <template id="participantTemplate">
                                    <div class="participant-card rounded-[24px] border border-slate-200 bg-slate-50/80 p-5" data-participant-item>
                                        <div class="flex items-center justify-between gap-4 pb-4 border-b border-slate-200">
                                            <div>
                                                <div class="participant-title text-base font-black text-slate-900">Peserta</div>
                                                <div class="text-xs text-slate-500 mt-1">Isi data ringkas, sistem melengkapi data wajib otomatis.</div>
                                            </div>
                                            <button type="button" class="remove-participant rounded-2xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-black text-rose-600 hover:bg-rose-100 transition">
                                                Hapus
                                            </button>
                                        </div>

                                        <div class="mt-5 space-y-5">
                                            <div>
                                                <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Nama</label>
                                                <input class="field mt-2" data-field="name" placeholder="Nama lengkap peserta" required>
                                            </div>
                                            <div class="grid sm:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Gender</label>
                                                    <select class="field mt-2" data-field="gender" required>
                                                        <option value="">Pilih gender</option>
                                                        <option value="male">Laki-laki</option>
                                                        <option value="female">Perempuan</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">No. HP</label>
                                                    <input class="field mt-2" data-field="phone" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Email</label>
                                                <input class="field mt-2" type="email" data-field="email" placeholder="email@contoh.com" required>
                                            </div>

                                            @if($categories && $categories->count() > 0)
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Kategori</label>
                                                    <div class="mt-3 grid gap-3">
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
                                                                <div class="choice-box flex items-center justify-between gap-4">
                                                                    <div>
                                                                        <div class="text-sm font-black text-slate-900">{{ $cat->name }}</div>
                                                                        @if($cat->distance_km)
                                                                            <div class="mt-1 text-xs font-semibold text-slate-500">{{ number_format($cat->distance_km, 0, ',', '.') }}K</div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="text-right">
                                                                        @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                                            <div class="text-[11px] text-slate-400 line-through">Rp {{ number_format($priceRegular, 0, ',', '.') }}</div>
                                                                        @endif
                                                                        <div class="text-sm font-black text-[#f1631e]">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
                                                                    </div>
                                                                </div>
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if(!empty($addons))
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Add-on</label>
                                                    <div class="mt-3 grid gap-3">
                                                        @foreach($addons as $idx => $addon)
                                                            <label class="choice relative block cursor-pointer">
                                                                <input type="checkbox" data-addon-index="{{ $idx }}" value="1" data-addon-price="{{ (int) ($addon['price'] ?? 0) }}">
                                                                <div class="choice-box flex items-center justify-between gap-4">
                                                                    <div>
                                                                        <div class="text-sm font-black text-slate-900">{{ $addon['name'] }}</div>
                                                                        <div class="mt-1 text-xs text-slate-500">Opsional</div>
                                                                    </div>
                                                                    <div class="text-sm font-black text-slate-900">
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
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">No. Identitas (KTP/SIM)</label>
                                                    <input class="field mt-2" data-field="id_card" placeholder="Nomor KTP/SIM" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="id_card">
                                            @endif

                                            @if($showAddress)
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Alamat Lengkap</label>
                                                    <textarea class="field mt-2" data-field="address" placeholder="Alamat lengkap" rows="2" required></textarea>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="address">
                                            @endif

                                            @if($showEmergency)
                                                <div class="grid sm:grid-cols-2 gap-4">
                                                    <div>
                                                        <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Nama Kontak Darurat</label>
                                                        <input class="field mt-2" data-field="emergency_contact_name" placeholder="Nama kontak darurat" required>
                                                    </div>
                                                    <div>
                                                        <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">No. HP Kontak Darurat</label>
                                                        <input class="field mt-2" data-field="emergency_contact_number" inputmode="numeric" minlength="10" maxlength="15" placeholder="08xxxxxxxxxx" required>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="emergency_contact_name">
                                                <input type="hidden" data-hidden-auto="emergency_contact_number">
                                            @endif

                                            @if($showDob)
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Tanggal Lahir</label>
                                                    <input type="date" class="field mt-2" data-field="date_of_birth" required>
                                                </div>
                                            @else
                                                <input type="hidden" data-hidden-auto="date_of_birth">
                                            @endif

                                            @if($showJersey)
                                                <div>
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Ukuran Jersey</label>
                                                    <select class="field mt-2" data-field="jersey_size" required>
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
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Target Time</label>
                                                    <div class="grid grid-cols-3 gap-2 mt-2">
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
                                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Golongan Darah</label>
                                                    <select class="field mt-2" data-field="blood_type" required>
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

                                <div id="payment-methods-container">
                                    <label class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Metode Pembayaran</label>
                                    <div class="mt-3 grid gap-3">
                                        @if($showMidtrans)
                                            <label class="choice relative block cursor-pointer">
                                                <input type="radio" name="payment_method" value="midtrans" {{ $showMidtrans ? 'checked' : '' }} required>
                                                <div class="choice-box flex items-center justify-between">
                                                    <div>
                                                        <div class="text-sm font-black text-slate-900">Midtrans</div>
                                                        <div class="mt-1 text-xs text-slate-500">E-wallet, VA, kartu, QRIS</div>
                                                    </div>
                                                    <i class="fa-solid fa-credit-card text-slate-400"></i>
                                                </div>
                                            </label>
                                        @endif
                                        @if($showMoota)
                                            <label class="choice relative block cursor-pointer">
                                                <input type="radio" name="payment_method" value="moota" {{ !$showMidtrans && $showMoota ? 'checked' : '' }} required>
                                                <div class="choice-box flex items-center justify-between">
                                                    <div>
                                                        <div class="text-sm font-black text-slate-900">Transfer Bank</div>
                                                        <div class="mt-1 text-xs text-slate-500">Verifikasi otomatis via Moota</div>
                                                    </div>
                                                    <i class="fa-solid fa-building-columns text-slate-400"></i>
                                                </div>
                                            </label>
                                        @endif
                                        @if($showCOD)
                                            <label class="choice relative block cursor-pointer">
                                                <input type="radio" name="payment_method" value="cod" {{ !$showMidtrans && !$showMoota && $showCOD ? 'checked' : '' }} required>
                                                <div class="choice-box flex items-center justify-between">
                                                    <div>
                                                        <div class="text-sm font-black text-slate-900">Bayar COD</div>
                                                        <div class="mt-1 text-xs text-slate-500">Bayar langsung sesuai arahan EO</div>
                                                    </div>
                                                    <i class="fa-solid fa-money-bill-wave text-slate-400"></i>
                                                </div>
                                            </label>
                                        @endif
                                    </div>
                                </div>

                                <div class="rounded-3xl border border-slate-200 bg-white p-5">
                                    <div class="flex items-center justify-between gap-4">
                                        <div>
                                            <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Kupon</div>
                                            <div class="text-sm text-slate-600 mt-1">Masukkan kode kupon jika ada.</div>
                                        </div>
                                        <div class="text-xs font-bold text-slate-400">Opsional</div>
                                    </div>
                                    <div class="mt-4 flex gap-2">
                                        <input id="qlCouponInput" type="text" class="field" placeholder="Contoh: PROMO2026" autocomplete="off" />
                                        <button id="qlCouponApplyBtn" type="button" class="px-4 py-3 rounded-2xl bg-slate-900 text-white font-black hover:bg-slate-800 transition">Apply</button>
                                        <button id="qlCouponClearBtn" type="button" class="hidden px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-700 font-black hover:bg-slate-50 transition">Clear</button>
                                    </div>
                                    <input type="hidden" name="coupon_code" id="qlCouponCodeHidden" />
                                    <div id="qlCouponMessage" class="hidden mt-2 text-xs font-bold"></div>
                                </div>

                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                    <div class="flex items-center justify-between text-sm text-slate-600">
                                        <span>Tiket</span>
                                        <span id="ql-ticket-price" class="font-bold text-slate-900">Rp 0</span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-sm text-slate-600">
                                        <span>Add-on</span>
                                        <span id="ql-addon-price" class="font-bold text-slate-900">Rp 0</span>
                                    </div>
                                    <div id="qlCouponRow" class="hidden mt-2 flex items-center justify-between text-sm text-slate-600">
                                        <span>Diskon <span id="qlCouponLabel" class="font-bold text-slate-700"></span></span>
                                        <span id="qlDiscountAmount" class="font-bold text-emerald-700">-Rp 0</span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-sm text-slate-600">
                                        <span>Platform fee</span>
                                        <span id="ql-platform-fee" class="font-bold text-slate-900">Rp {{ number_format($platformFee, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-slate-200 flex items-center justify-between">
                                        <span class="text-sm font-black uppercase tracking-[0.16em] text-slate-500">Total</span>
                                        <span id="ql-total-price" class="text-2xl font-black text-[#f1631e]">Rp 0</span>
                                    </div>
                                </div>

                                <input type="hidden" name="pic_name">
                                <input type="hidden" name="pic_email">
                                <input type="hidden" name="pic_phone">

                                <button id="quickSubmitBtn" type="submit" class="submit-btn w-full inline-flex items-center justify-center gap-3 px-6 py-4 rounded-[20px] bg-[#f1631e] hover:bg-[#d94f0b] text-white font-black text-base shadow-lg shadow-[#f1631e]/20 transition">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>Daftar Sekarang</span>
                                </button>
                                <div class="text-xs text-slate-500 text-center leading-6">Form ini dirancang singkat agar proses daftar terasa cepat tanpa mengorbankan kelengkapan data sistem.</div>
                                @if(env('RECAPTCHA_SITE_KEY_v3'))
                                    <div class="text-[11px] text-slate-400 text-center leading-5">
                                        Halaman ini dilindungi reCAPTCHA, serta Google
                                        <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="underline">Kebijakan Privasi</a>
                                        dan
                                        <a href="https://policies.google.com/terms" target="_blank" rel="noopener noreferrer" class="underline">Persyaratan Layanan</a>
                                        berlaku.
                                    </div>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </main>

    @include('events.partials.moota-payment-modal')

    {{-- Route Map Modal --}}
    <div id="routeModal" class="fixed inset-0 z-[999] hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeRouteModal()"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-[28px] border border-slate-200/50 w-full max-w-4xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-orange-50 text-[#f1631e] flex items-center justify-center">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-lg text-slate-900">Peta Rute Lari</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $event->name }}</p>
                        </div>
                    </div>
                    <button onclick="closeRouteModal()" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center text-slate-500 transition-colors">
                        <i class="fa-solid fa-xmark"></i>
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
                    
                    // Also include direct event-level GPX if any
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
                    {{-- GPX Tabs / Selector --}}
                    <div class="flex flex-wrap gap-2 p-4 bg-slate-50 border-b border-slate-100">
                        @foreach($gpxList as $idx => $gpx)
                            <button type="button" 
                                    class="gpx-tab-btn px-4 py-2 text-xs font-black rounded-2xl border transition-all"
                                    data-gpx-url="{{ asset('storage/' . $gpx->gpx_path) }}" 
                                    data-idx="{{ $idx }}"
                                    onclick="selectGpxTrack(this)">
                                {{ $gpx->title }} @if($gpx->distance_km) ({{ number_format($gpx->distance_km, 2) }} km) @endif
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="flex-1 min-h-[350px] md:min-h-[450px] relative">
                    <div id="route-leaflet-map" class="w-full h-full min-h-[350px] md:min-h-[450px] z-10"></div>
                    
                    {{-- Fallback Iframe container if Leaflet has issues or fallback is needed --}}
                    <div id="route-iframe-container" class="hidden w-full h-full min-h-[350px] md:min-h-[450px]">
                        @if($event->map_embed_url)
                            <iframe src="{{ $event->map_embed_url }}" class="w-full h-full border-0" allowfullscreen="" loading="lazy"></iframe>
                        @endif
                    </div>
                </div>
                
                @if($gpxList->isNotEmpty())
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-semibold">
                        <span>Pilih kategori di atas untuk melihat rute lari pada peta.</span>
                        <a id="gpx-download-link" href="#" download class="inline-flex items-center gap-1.5 text-[#f1631e] hover:underline font-bold">
                            <i class="fa-solid fa-download"></i> Download GPX
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Terms & Conditions Modal --}}
    @if($event->terms_and_conditions)
        <div id="termsModal" class="fixed inset-0 z-[999] hidden">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="bg-white rounded-[28px] border border-slate-200/50 w-full max-w-3xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-orange-50 text-[#f1631e] flex items-center justify-center">
                                <i class="fa-solid fa-file-shield"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-lg text-slate-900">Syarat & Ketentuan</h3>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $event->name }}</p>
                            </div>
                        </div>
                        <button onclick="document.getElementById('termsModal').classList.add('hidden')" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center text-slate-500 transition-colors">
                            <i class="fa-solid fa-xmark"></i>
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
        var routePolyline = null;
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
            
            // Allow DOM to update and render layout, then init Leaflet
            setTimeout(initRouteMap, 100);
        }

        function closeRouteModal() {
            const modal = document.getElementById('routeModal');
            modal.classList.add('hidden');
        }

        function initRouteMap() {
            if (!window.L) {
                // If Leaflet is not loaded, show fallback iframe
                showIframeFallback();
                return;
            }

            if (!routeMap) {
                routeMap = L.map('route-leaflet-map', {
                    zoomControl: true,
                    attributionControl: true
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(routeMap);
            } else {
                routeMap.invalidateSize();
            }

            if (hasGpx && gpxTracks.length > 0) {
                // Trigger first GPX file
                const firstBtn = document.querySelector('.gpx-tab-btn');
                if (firstBtn) {
                    selectGpxTrack(firstBtn);
                }
            } else if (eventLat && eventLng) {
                // Center map at event location and put a marker
                routeMap.setView([eventLat, eventLng], 14);
                
                if (startMarker) {
                    routeMap.removeLayer(startMarker);
                }
                
                startMarker = L.marker([eventLat, eventLng], {
                    icon: L.divIcon({
                        className: '',
                        html: '<div style="width:24px;height:24px;border-radius:999px;background:#f1631e;border:2px solid #fff;box-shadow:0 4px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;"><i class="fa-solid fa-location-dot"></i></div>',
                        iconSize: [24, 24],
                        iconAnchor: [12, 12]
                    })
                }).addTo(routeMap);
                startMarker.bindPopup("<b>{{ $event->location_name }}</b>").openPopup();
            } else {
                // Fallback to Iframe
                showIframeFallback();
            }
        }

        function showIframeFallback() {
            document.getElementById('route-leaflet-map').classList.add('hidden');
            document.getElementById('route-iframe-container').classList.remove('hidden');
        }

        function selectGpxTrack(btn) {
            // Update Tab styles
            document.querySelectorAll('.gpx-tab-btn').forEach(b => {
                b.classList.remove('bg-[#f1631e]', 'text-white', 'border-[#f1631e]');
                b.classList.add('bg-white', 'text-slate-700', 'border-slate-200');
            });
            btn.classList.remove('bg-white', 'text-slate-700', 'border-slate-200');
            btn.classList.add('bg-[#f1631e]', 'text-white', 'border-[#f1631e]');

            const gpxUrl = btn.getAttribute('data-gpx-url');
            
            // Update download link
            const downloadLink = document.getElementById('gpx-download-link');
            if (downloadLink) {
                downloadLink.href = gpxUrl;
            }

            // Fetch and parse GPX
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
                            points.push([lat, lon]);
                        }
                    });

                    if (points.length === 0) return;

                    // Clear previous layers
                    if (routePolyline) routeMap.removeLayer(routePolyline);
                    if (startMarker) routeMap.removeLayer(startMarker);
                    if (finishMarker) routeMap.removeLayer(finishMarker);

                    // Draw route polyline
                    routePolyline = L.polyline(points, {
                        color: '#f1631e',
                        weight: 5,
                        opacity: 0.85
                    }).addTo(routeMap);

                    // Create start/finish markers
                    const startPt = points[0];
                    const finishPt = points[points.length - 1];

                    // Start marker (Green Play / Dot)
                    startMarker = L.marker(startPt, {
                        icon: L.divIcon({
                            className: '',
                            html: '<div style="width:24px;height:24px;border-radius:999px;background:#22c55e;border:2px solid #fff;box-shadow:0 4px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;"><i class="fa-solid fa-play ml-0.5"></i></div>',
                            iconSize: [24, 24],
                            iconAnchor: [12, 12]
                        })
                    }).addTo(routeMap).bindPopup('<b>Start</b>');

                    // Finish marker (Red Checkered / Flag)
                    finishMarker = L.marker(finishPt, {
                        icon: L.divIcon({
                            className: '',
                            html: '<div style="width:24px;height:24px;border-radius:999px;background:#ef4444;border:2px solid #fff;box-shadow:0 4px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:#fff;font-size:10px;"><i class="fa-solid fa-flag-checkered"></i></div>',
                            iconSize: [24, 24],
                            iconAnchor: [12, 12]
                        })
                    }).addTo(routeMap).bindPopup('<b>Finish</b>');

                    // Fit map bounds
                    routeMap.fitBounds(routePolyline.getBounds(), { padding: [30, 30] });
                })
                .catch(err => {
                    console.error('Error loading GPX file:', err);
                });
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
                imgEl.alt = '{{ $event->name }}' + ' • ' + (index + 1);
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

                // Handle payment method visibility and requirement dynamically
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
                submitBtn.innerHTML = isSubmitting ? '<i class="fa-solid fa-spinner fa-spin"></i><span>Memproses...</span>' : originalSubmitText;
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
