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
    $logoImage = $event->logo_image ? asset('storage/'.$event->logo_image) : null;
    $faviconImage = $logoImage ?? $heroImage;
    $canonicalUrl = url()->current();
    $shortDescription = trim(strip_tags((string) ($event->short_description ?? '')));
    $platformFee = (int) ($event->platform_fee ?? 0);
    $defaultJersey = collect($event->jersey_sizes ?? [])->filter()->first() ?? 'L';
    $schemaDescription = $shortDescription !== '' ? $shortDescription : trim(strip_tags((string) ($event->full_description ?? '')));
    if ($schemaDescription === '') {
        $schemaDescription = $event->name;
    }
    $schemaEvent = [
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $event->name,
        'description' => $schemaDescription,
        'image' => [$heroImage],
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
    <title>{{ $event->name }} • Quick Light</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $shortDescription !== '' ? $shortDescription : $event->name }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <link rel="icon" href="{{ $faviconImage }}">
    <link rel="apple-touch-icon" href="{{ $faviconImage }}">
    <meta itemprop="name" content="{{ $event->name }}">
    <meta itemprop="description" content="{{ $schemaDescription }}">
    <meta itemprop="image" content="{{ $heroImage }}">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:type" content="event">
    <meta property="og:title" content="{{ $event->name }}">
    <meta property="og:description" content="{{ $schemaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $heroImage }}">
    @if($logoImage)
        <meta property="og:image:alt" content="{{ $event->name }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $event->name }}">
    <meta name="twitter:description" content="{{ $schemaDescription }}">
    <meta name="twitter:image" content="{{ $heroImage }}">
    <script type="application/ld+json">{!! json_encode($schemaEvent, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    @if($showMidtrans && $midtransClientKey)
        <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>
    @endif
    @if(env('RECAPTCHA_SITE_KEY_v3'))
        <script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY_v3') }}"></script>
    @endif
    <style>
        :root { --primary:#2563eb; --primary-dark:#1d4ed8; --ink:#0f172a; --muted:#64748b; --line:#dbe4f0; --bg:#f6f9fc; }
        html { scroll-behavior:smooth; }
        body { background:linear-gradient(180deg,#f8fbff 0%,#f5f7fb 100%); color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif; }
        .glass { background:rgba(255,255,255,.85); backdrop-filter:blur(18px); }
        .soft-card { border:1px solid rgba(148,163,184,.18); box-shadow:0 10px 40px rgba(15,23,42,.08); border-radius:28px; background:#fff; }
        .field { width:100%; border:1px solid #dbe4f0; border-radius:18px; padding:15px 16px; font-weight:600; color:#0f172a; background:#fff; outline:none; transition:.18s ease; }
        .field:focus { border-color:#60a5fa; box-shadow:0 0 0 4px rgba(37,99,235,.12); }
        .pill { display:inline-flex; align-items:center; gap:.5rem; border-radius:999px; padding:.55rem .9rem; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .choice input { position:absolute; opacity:0; pointer-events:none; }
        .choice-box { border:1px solid #dbe4f0; border-radius:20px; padding:14px 16px; background:#fff; transition:.18s ease; }
        .choice input:checked + .choice-box { border-color:#2563eb; background:linear-gradient(180deg,#eff6ff 0%,#ffffff 100%); box-shadow:0 0 0 4px rgba(37,99,235,.08); }
        .submit-btn[disabled] { opacity:.7; cursor:not-allowed; }
        .content-html p { margin-top:0; margin-bottom:1rem; line-height:1.75; color:#334155; }
        .content-html h1, .content-html h2, .content-html h3, .content-html h4 { color:#0f172a; font-weight:800; margin-top:1.4rem; margin-bottom:.8rem; }
        .content-html ul, .content-html ol { padding-left:1.25rem; color:#334155; margin-bottom:1rem; }
        .hero-overlay { background:linear-gradient(110deg, rgba(15,23,42,.72) 0%, rgba(15,23,42,.48) 35%, rgba(255,255,255,.05) 100%); }
    </style>
</head>
<body>
    <section class="relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="{{ $heroImage }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 hero-overlay"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 md:px-8 pt-10 pb-24 lg:pt-16 lg:pb-28">
            <div class="flex items-start justify-between gap-6 mb-12">
                <div class="pill bg-white/12 text-white border border-white/20">
                    <i class="fa-solid fa-bolt"></i>
                    Quick Light
                </div>
                @if($event->logo_image)
                    <img src="{{ asset('storage/'.$event->logo_image) }}" alt="{{ $event->name }}" class="h-12 md:h-16 w-auto object-contain rounded-2xl bg-white/80 p-2">
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
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Pembayaran</div>
                            <div class="mt-2 text-sm font-extrabold text-slate-900">
                                {{ collect([$showMidtrans ? 'Midtrans' : null, $showMoota ? 'Transfer' : null, $showCOD ? 'COD' : null])->filter()->implode(' • ') }}
                            </div>
                        </div>
                        <div class="rounded-2xl bg-white p-4 border border-slate-200">
                            <div class="text-[11px] font-bold uppercase tracking-[0.16em] text-slate-500">Template</div>
                            <div class="mt-2 text-sm font-extrabold text-slate-900">Simple • Fast • Clean</div>
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

    <main class="max-w-7xl mx-auto px-4 md:px-8 -mt-12 pb-16 relative z-10">
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
            <section id="detail-event" class="space-y-6">
                <div class="soft-card overflow-hidden">
                    <div class="grid md:grid-cols-[1.1fr_.9fr]">
                        <div class="p-6 md:p-8 lg:p-10">
                            <div class="text-sm font-black uppercase tracking-[0.2em] text-blue-600">Event Overview</div>
                            <h2 class="mt-3 text-2xl md:text-3xl font-black tracking-tight text-slate-900">Pendaftaran cepat dengan detail event yang tetap lengkap</h2>
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
                                <div class="mt-5 rounded-2xl bg-blue-50 border border-blue-100 p-4 text-sm text-slate-700 leading-7">
                                    <div class="font-extrabold text-slate-900 mb-1">Alamat</div>
                                    {{ $event->location_address }}
                                </div>
                            @endif
                        </div>
                        <div class="min-h-[280px]">
                            <img src="{{ $heroImage }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>

                @if($event->full_description)
                    <div class="soft-card p-6 md:p-8">
                        <div class="text-sm font-black uppercase tracking-[0.2em] text-blue-600">Description</div>
                        <div class="content-html mt-4 text-slate-700">
                            {!! $event->full_description !!}
                        </div>
                    </div>
                @elseif($event->short_description)
                    <div class="soft-card p-6 md:p-8">
                        <div class="text-sm font-black uppercase tracking-[0.2em] text-blue-600">Description</div>
                        <p class="mt-4 text-slate-700 leading-8">{{ $event->short_description }}</p>
                    </div>
                @endif
                
                @if(($hasPaidParticipants ?? false) && $event->show_participant_list)
                    <div class="soft-card p-6 md:p-8" id="participants-list">
                        <div class="text-sm font-black uppercase tracking-[0.2em] text-blue-600">Daftar Peserta</div>
                        <div class="mt-4" id="vue-participants-app">
                            @include('events.partials.participants-table')
                        </div>
                    </div>
                @endif
            </section>

            <aside id="register" class="lg:sticky lg:top-24 self-start">
                <div class="soft-card overflow-hidden">
                    <div class="px-6 md:px-7 py-6 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-cyan-500 text-white">
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
                                    <button id="addParticipantBtn" type="button" class="inline-flex items-center gap-2 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-black text-blue-700 hover:bg-blue-100 transition">
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
                                                <input class="field mt-2" data-field="name" name="participants[0][name]" placeholder="Nama lengkap peserta" required>
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
                                                                        <div class="text-sm font-black text-blue-600">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
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

                                            <input type="hidden" data-hidden-auto="id_card" name="participants[0][id_card]">
                                            <input type="hidden" data-hidden-auto="address" name="participants[0][address]">
                                            <input type="hidden" data-hidden-auto="emergency_contact_name" name="participants[0][emergency_contact_name]">
                                            <input type="hidden" data-hidden-auto="emergency_contact_number" name="participants[0][emergency_contact_number]">
                                            <input type="hidden" data-hidden-auto="date_of_birth" name="participants[0][date_of_birth]">
                                            <input type="hidden" data-hidden-auto="jersey_size" name="participants[0][jersey_size]" value="{{ $defaultJersey }}">
                                            <input type="hidden" data-hidden-auto="target_time" name="participants[0][target_time]" value="">
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
                                                                        <div class="text-sm font-black text-blue-600">Rp {{ number_format($displayPrice, 0, ',', '.') }}</div>
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

                                            <input type="hidden" data-hidden-auto="id_card">
                                            <input type="hidden" data-hidden-auto="address">
                                            <input type="hidden" data-hidden-auto="emergency_contact_name">
                                            <input type="hidden" data-hidden-auto="emergency_contact_number">
                                            <input type="hidden" data-hidden-auto="date_of_birth">
                                            <input type="hidden" data-hidden-auto="jersey_size" value="{{ $defaultJersey }}">
                                            <input type="hidden" data-hidden-auto="target_time" value="">
                                        </div>
                                    </div>
                                </template>

                                <div>
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

                                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                                    <div class="flex items-center justify-between text-sm text-slate-600">
                                        <span>Tiket</span>
                                        <span id="ql-ticket-price" class="font-bold text-slate-900">Rp 0</span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-sm text-slate-600">
                                        <span>Add-on</span>
                                        <span id="ql-addon-price" class="font-bold text-slate-900">Rp 0</span>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-sm text-slate-600">
                                        <span>Platform fee</span>
                                        <span id="ql-platform-fee" class="font-bold text-slate-900">Rp {{ number_format($platformFee, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="mt-4 pt-4 border-t border-slate-200 flex items-center justify-between">
                                        <span class="text-sm font-black uppercase tracking-[0.16em] text-slate-500">Total</span>
                                        <span id="ql-total-price" class="text-2xl font-black text-blue-600">Rp 0</span>
                                    </div>
                                </div>

                                <input type="hidden" name="pic_name">
                                <input type="hidden" name="pic_email">
                                <input type="hidden" name="pic_phone">

                                <button id="quickSubmitBtn" type="submit" class="submit-btn w-full inline-flex items-center justify-center gap-3 px-6 py-4 rounded-[20px] bg-blue-600 hover:bg-blue-700 text-white font-black text-base shadow-lg shadow-blue-600/20 transition">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    <span>Daftar Sekarang</span>
                                </button>
                                <div class="text-xs text-slate-500 text-center leading-6">Form ini dirancang singkat agar proses daftar terasa cepat tanpa mengorbankan kelengkapan data sistem.</div>
                            </form>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </main>

    @include('events.partials.moota-payment-modal')

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

            function formatCurrency(value) {
                return 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value || 0));
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

            function updateSummary() {
                const items = getParticipantItems();
                const ticket = items.reduce(function(sum, item) { return sum + selectedCategoryPrice(item); }, 0);
                const addons = items.reduce(function(sum, item) { return sum + selectedAddonPrice(item); }, 0);
                const participantCount = items.length;
                const subTotal = ticket + addons;
                const fee = subTotal > 0 ? platformFee * participantCount : 0;
                const total = subTotal + fee;
                if (ticketPriceEl) ticketPriceEl.textContent = formatCurrency(ticket);
                if (addonPriceEl) addonPriceEl.textContent = formatCurrency(addons);
                if (platformFeeEl) platformFeeEl.textContent = formatCurrency(fee);
                if (totalPriceEl) totalPriceEl.textContent = formatCurrency(total);
            }

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
                    item.querySelector('[data-hidden-auto="id_card"]').value = identity;
                    item.querySelector('[data-hidden-auto="address"]').value = '-';
                    item.querySelector('[data-hidden-auto="emergency_contact_name"]').value = name || 'Kontak Darurat';
                    item.querySelector('[data-hidden-auto="emergency_contact_number"]').value = phone || '0811111111';
                    item.querySelector('[data-hidden-auto="date_of_birth"]').value = '1990-01-01';
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
                    if (typeof grecaptcha !== 'undefined') {
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
                                processSubmission();
                            });
                        });
                        return;
                    }
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
