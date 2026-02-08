<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <script>
        // Nominatim CORS Proxy Interceptor
        (function() {
            var originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (typeof url === 'string' && url.includes('nominatim.openstreetmap.org')) {
                    var proxyUrl = '/image-proxy?url=' + encodeURIComponent(url);
                    return originalFetch(proxyUrl, options);
                }
                return originalFetch(url, options);
            };
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    @php
        $seoTitle = isset($seo['title']) && $seo['title'] ? $seo['title'] : $event->name . ' - ' . ($event->location_name ?? 'Official Event');
        $seoDesc = isset($seo['description']) && $seo['description'] ? $seo['description'] : Str::limit(strip_tags($event->short_description ?? $event->description), 155);
        $seoKeywords = isset($seo['keywords']) && $seo['keywords'] ? $seo['keywords'] : 'lari, event lari, ' . $event->name . ', ' . ($event->location_name ?? '') . ', pendaftaran lari, ruanglari';
        $seoUrl = isset($seo['url']) && $seo['url'] ? $seo['url'] : route('events.show', $event->slug);
        $seoImage = isset($seo['image']) && $seo['image'] ? $seo['image'] : ($event->hero_image ? asset('storage/' . $event->hero_image) : asset('images/ruanglari_green.png'));
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}" />
    <meta name="keywords" content="{{ $seoKeywords }}">
    <link rel="canonical" href="{{ $seoUrl }}">
    <meta name="theme-color" content="#1e3a8a">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="event" />
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

    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Event",
      "name": "{{ $event->name }}",
      "description": "{{ $seoDesc }}",
      "image": "{{ $seoImage }}",
      "startDate": "{{ $event->start_at ? $event->start_at->toIso8601String() : '' }}",
      "endDate": "{{ $event->end_at ? $event->end_at->toIso8601String() : ($event->start_at ? $event->start_at->addHours(4)->toIso8601String() : '') }}",
      "eventStatus": "https://schema.org/EventScheduled",
      "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
      "location": {
        "@@type": "Place",
        "name": "{{ $event->location_name ?? 'TBA' }}",
        "address": {
          "@@type": "PostalAddress",
          "addressLocality": "{{ $event->city ?? '' }}",
          "addressCountry": "ID"
        }
      },
      "organizer": {
        "@@type": "Organization",
        "name": "RuangLari",
        "url": "{{ url('/') }}"
      },
      "offers": {
        "@@type": "Offer",
        "url": "{{ $seoUrl }}",
        "price": "0",
        "priceCurrency": "IDR",
        "availability": "{{ (isset($isRegOpen) && $isRegOpen) ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut' }}",
        "validFrom": "{{ $event->registration_open_at ? $event->registration_open_at->toIso8601String() : '' }}"
      }
    }
    </script>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/green/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/green/favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/green/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/green/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    @if(env('RECAPTCHA_SITE_KEY'))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    @php
        $midtransDemoMode = filter_var($event->payment_config['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
        $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
        $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');
    @endphp
    <link rel="stylesheet" href="{{ $midtransUrl }}/snap/snap.css" />
    <script type="text/javascript" src="{{ $midtransUrl }}/snap/snap.js" data-client-key="{{ $midtransClientKey }}"></script>

    <script>
        tailwind.config = {
            theme: {
                fontFamily: {
                    sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                },
                extend: {
                    colors: {
                        primary: '#111827', // Gray 900 (Black-ish)
                        secondary: '#4B5563', // Gray 600
                        accent: '#2563eb', // Blue 600
                    }
                }
            }
        }
    </script>
    <style>
        /* Optimasi Form Elements */
        .form-radio:checked { background-color: #111827; border-color: #111827; }
        .form-input:focus, .form-select:focus { border-color: #111827; ring: 0; box-shadow: 0 0 0 2px rgba(17, 24, 39, 0.1); }
        /* Hide scrollbar for category selector on mobile */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased min-h-screen flex flex-col">

    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if($event->logo_image)
                    <img src="{{ asset('storage/' . $event->logo_image) }}" class="h-8 w-auto">
                @else
                    <div class="h-8 w-8 bg-black rounded text-white flex items-center justify-center font-bold">E</div>
                @endif
                <span class="font-bold tracking-tight text-sm md:text-base truncate max-w-[200px]">{{ $event->name }}</span>
            </div>
            
            @if(!($event->registration_open_at && now() < $event->registration_open_at) && !($event->registration_close_at && now() > $event->registration_close_at))
            <a href="#register" class="bg-black text-white px-5 py-2 rounded-full text-xs font-bold hover:bg-gray-800 transition">
                Daftar
            </a>
            @endif
        </div>
    </nav>

    <main class="flex-grow">
        <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 px-4 py-10">
            
            <div class="lg:col-span-7 space-y-10">
                
                <div>
                    <span class="inline-block py-1 px-3 rounded-full bg-gray-100 text-gray-600 text-xs font-bold uppercase tracking-wider mb-4">Official Event</span>
                    <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mb-4 leading-tight">{{ $event->name }}</h1>
                    
                    <div class="flex flex-wrap gap-4 text-sm font-medium text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            {{ $event->start_at->format('d F Y, H:i') }} WIB
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            {{ $event->location_name }}
                        </div>
                    </div>
                </div>

                <div class="prose prose-slate prose-lg text-gray-600 leading-relaxed">
                    {!! $event->full_description ?? $event->short_description !!}
                </div>

                @include('events.partials.prizes-section', ['categories' => $categories])

                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-l-4 border-black pl-3">Kategori & Harga</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                        <div class="border border-gray-200 p-5 rounded-xl hover:border-black transition duration-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-bold text-lg">{{ $cat->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">Jarak: {{ $cat->distance_km }}KM</p>
                                </div>
                                <div class="text-right">
                                    @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                        <p class="text-xs font-bold text-gray-400 line-through">Rp {{ number_format($priceRegular, 0, ',', '.') }}</p>
                                    @endif
                                    <p class="font-bold">Rp {{ number_format($displayPrice, 0, ',', '.') }}</p>
                                    @if($cat->quota > 0)
                                        <span class="inline-block mt-1 w-2 h-2 bg-green-500 rounded-full"></span>
                                    @else
                                        <span class="text-xs text-red-500 font-bold">Habis</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="lg:col-span-5">
                <div class="sticky top-24" id="register">
                    
                    @php
                        $now = now();
                        $isRegOpen = !($event->registration_open_at && $now < $event->registration_open_at) && !($event->registration_close_at && $now > $event->registration_close_at);

                        $paymentConfig = $event->payment_config ?? [];
                        
                        // Support both new (allowed_methods) and legacy (direct keys) structures
                        if (isset($paymentConfig['allowed_methods']) && is_array($paymentConfig['allowed_methods'])) {
                            $allowed = $paymentConfig['allowed_methods'];
                            $showMidtrans = in_array('midtrans', $allowed) || in_array('all', $allowed);
                            $showMoota = in_array('moota', $allowed) || in_array('all', $allowed);
                        } else {
                            $showMidtrans = $paymentConfig['midtrans'] ?? true;
                            $showMoota = $paymentConfig['moota'] ?? false;
                        }

                        if (!$showMidtrans && !$showMoota) {
                            $showMidtrans = true;
                        }

                        $pa = $event->premium_amenities ?? null;
                    @endphp

                    @if(!$isRegOpen)
                        <div class="bg-gray-100 rounded-2xl p-8 text-center border border-gray-200">
                            <h3 class="font-bold text-xl text-gray-400">Registrasi Ditutup</h3>
                            <p class="text-sm text-gray-500 mt-2">Maaf, event ini belum dibuka atau sudah berakhir.</p>
                        </div>
                    @else
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100">
                                <h3 class="font-bold text-lg text-gray-900">Formulir Pendaftaran</h3>
                                <p class="text-xs text-gray-500">Isi data dengan benar.</p>
                            </div>

                            <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="registrationForm" class="p-6 space-y-5">
                                @csrf

                                @if(request('payment') === 'pending')
                                    <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-900">
                                        <div class="font-bold">Pembayaran masih pending</div>
                                        <div class="text-xs text-gray-700 mt-1">Jika popup Midtrans tertutup/refresh, Anda bisa melanjutkan tanpa registrasi ulang.</div>
                                        <a href="{{ route('events.payments.continue', $event->slug) }}" class="inline-block mt-3 bg-yellow-400 hover:bg-yellow-300 text-black font-bold px-4 py-2 rounded-lg">Lanjutkan Pembayaran</a>
                                    </div>
                                @elseif(request('payment') === 'success')
                                    <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-900">
                                        <div class="font-bold">Pembayaran berhasil</div>
                                        <div class="text-xs text-gray-700 mt-1">Jika belum menerima konfirmasi, coba refresh beberapa saat lagi.</div>
                                    </div>
                                @endif
                                
                                <div class="space-y-3">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Penanggung Jawab</label>
                                    <input type="text" name="pic_name" id="pic_name" placeholder="Nama Lengkap" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                    <div class="grid grid-cols-2 gap-3">
                                        <input type="email" name="pic_email" id="pic_email" placeholder="Email" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                        <input type="text" name="pic_phone" id="pic_phone" placeholder="WhatsApp" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                </div>

                                <div class="h-px bg-gray-100 w-full"></div>

                                <div id="participantsWrapper" class="space-y-8">
                                    <!-- Participant Item Template -->
                                    <div class="participant-item space-y-3" data-index="0">
                                        <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                            <div class="flex items-center gap-2">
                                                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider participant-title">Peserta #1</label>
                                                <button type="button" class="copy-pic-btn text-[10px] bg-gray-200 hover:bg-gray-300 text-gray-600 px-2 py-0.5 rounded transition" onclick="copyFromPic(this)">
                                                    Isi Data PIC
                                                </button>
                                                <button type="button" class="copy-prev-btn text-[10px] bg-gray-200 hover:bg-gray-300 text-gray-600 px-2 py-0.5 rounded transition hidden" onclick="copyFromPrev(this)">
                                                    Salin Peserta Sebelumnya
                                                </button>
                                            </div>
                                            <button type="button" class="text-xs text-red-500 hover:text-red-700 font-medium remove-participant hidden">Hapus</button>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 gap-2">
                                            <p class="text-xs text-gray-500 mb-1">Pilih Kategori:</p>
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
                                            <label class="cursor-pointer relative">
                                                <input type="radio" name="participants[0][category_id]" value="{{ $cat->id }}" class="peer sr-only category-radio" data-price="{{ $displayPrice }}" required>
                                                <div class="p-3 border border-gray-200 rounded-lg peer-checked:border-black peer-checked:bg-gray-50 peer-checked:ring-1 peer-checked:ring-black transition flex justify-between items-center">
                                                    <span class="text-sm font-medium">{{ $cat->name }}</span>
                                                    <span class="text-sm font-bold">
                                                        @if($displayPrice !== $priceRegular && $priceRegular > 0)
                                                            <span class="text-xs text-gray-400 line-through mr-1">Rp {{ number_format($priceRegular/1000, 0) }}k</span>
                                                        @endif
                                                        Rp {{ number_format($displayPrice/1000, 0) }}k
                                                    </span>
                                                </div>
                                            </label>
                                            @endforeach
                                        </div>

                                        <div class="grid grid-cols-2 gap-3 pt-2">
                                            <input type="text" name="participants[0][name]" placeholder="Nama Peserta" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                            <select name="participants[0][gender]" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                                <option value="">Gender</option>
                                                <option value="male">Laki-laki</option>
                                                <option value="female">Perempuan</option>
                                            </select>
                                        </div>
                                        <input type="email" name="participants[0][email]" placeholder="Email Peserta" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                        
                                        <div class="grid grid-cols-2 gap-3">
                                             <input type="text" name="participants[0][phone]" placeholder="No. HP" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                             <select name="participants[0][jersey_size]" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                                <option value="">Jersey</option>
                                                @foreach(['S','M','L','XL'] as $s) <option value="{{ $s }}">{{ $s }}</option> @endforeach
                                            </select>
                                        </div>
                                        <input type="text" name="participants[0][id_card]" placeholder="No. KTP/SIM" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                        <textarea name="participants[0][address]" placeholder="Alamat Peserta" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required maxlength="500" rows="3"></textarea>
                                        <input type="date" name="participants[0][date_of_birth]" placeholder="Tanggal Lahir" aria-label="Tanggal Lahir" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                        <input type="text" name="participants[0][emergency_contact_name]" placeholder="Nama Kontak Darurat" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required>
                                        <input type="text" name="participants[0][emergency_contact_number]" placeholder="No. Kontak Darurat" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition" required minlength="10" maxlength="15" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                        <input type="text" name="participants[0][target_time]" placeholder="Target Waktu (Optional)" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:bg-white transition">
                                    </div>
                                </div>

                                <button type="button" id="addParticipantBtn" class="w-full py-3 border border-dashed border-gray-300 rounded-xl text-sm font-bold text-gray-500 hover:text-black hover:border-black transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Tambah Peserta Lain
                                </button>

                            <!-- Coupon Section -->
                                <div class="mb-6">
                                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 block">Kode Promo</label>
                                    <div class="flex gap-2">
                                        <input type="text" id="coupon_code" placeholder="KODE..." class="flex-1 bg-gray-50 border border-gray-200 rounded-lg px-4 py-2 text-sm focus:bg-white focus:border-black outline-none transition uppercase font-bold">
                                        <button type="button" id="applyCouponBtn" class="bg-black text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-800 transition">Pakai</button>
                                    </div>
                                    <div id="couponMessage" class="mt-2 text-xs font-medium"></div>
                                    <input type="hidden" name="coupon_code" id="coupon_code_hidden">
                                </div>

                                <div class="bg-gray-50 p-4 rounded-xl space-y-3">
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500">Subtotal</span>
                                        <span class="font-bold text-black" id="subtotalDisplay">Rp 0</span>
                                    </div>
                                    
                                    <div class="flex justify-between items-center text-sm text-green-600 hidden" id="discountRow">
                                        <span>Diskon</span>
                                        <span class="font-bold" id="discountDisplay">-Rp 0</span>
                                    </div>

                                    <div class="flex justify-between items-center text-sm {{ $event->platform_fee > 0 ? '' : 'hidden' }}" id="feeRow">
                                        <span class="text-gray-500">Biaya Admin</span>
                                        <span class="font-bold text-black" id="feeDisplay">Rp 0</span>
                                    </div>
                                    <div class="h-px bg-gray-200 w-full"></div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500 font-bold">Total Bayar</span>
                                        <span class="font-bold text-lg text-black" id="totalDisplay">Rp 0</span>
                                    </div>
                                    
                                    @if($event->terms_and_conditions)
                                    <label class="flex items-start gap-2 cursor-pointer">
                                        <input type="checkbox" name="terms_agreed" required class="mt-1 w-3.5 h-3.5 rounded border-gray-300 text-black focus:ring-black">
                                        <span class="text-[10px] text-gray-500 leading-tight">
                                            Setuju dengan <button type="button" onclick="document.getElementById('termsModal').classList.remove('hidden')" class="underline hover:text-black">Syarat & Ketentuan</button>.
                                        </span>
                                    </label>
                                    @endif

                                    <div class="space-y-3 mb-6">
                                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Metode Pembayaran</label>
                                        
                                        @if($showMidtrans)
                                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-black transition bg-white">
                                            <input type="radio" name="payment_method" value="midtrans" class="w-4 h-4 text-black focus:ring-black" {{ $showMidtrans && !$showMoota ? 'checked' : '' }} required>
                                            <div class="flex-1">
                                                <span class="block text-sm font-bold text-gray-900">Otomatis (QRIS, VA, E-Wallet)</span>
                                            </div>
                                        </label>
                                        @endif

                                        @if($showMoota)
                                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl cursor-pointer hover:border-black transition bg-white">
                                            <input type="radio" name="payment_method" value="moota" class="w-4 h-4 text-black focus:ring-black" {{ !$showMidtrans && $showMoota ? 'checked' : '' }} required>
                                            <div class="flex-1">
                                                <span class="block text-sm font-bold text-gray-900">Transfer Bank (Moota)</span>
                                                <span class="text-[10px] text-gray-500">Verifikasi Otomatis</span>
                                            </div>
                                        </label>
                                        @endif
                                    </div>

                                    @if(env('RECAPTCHA_SITE_KEY'))
                                        <div class="flex justify-center">
                                            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" data-theme="light"></div>
                                        </div>
                                    @endif
                                </div>

                                <button type="submit" id="submitBtn" class="w-full bg-black text-white font-bold py-4 rounded-xl hover:bg-gray-800 hover:shadow-lg transform transition active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                                    Bayar Sekarang
                                </button>
                                <p class="text-[10px] text-center text-gray-400 mt-2 flex items-center justify-center gap-1">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Pembayaran Aman via Midtrans
                                </p>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    @include('events.partials.sponsor-carousel', [
        'gradientFrom' => 'from-white',
        'titleColor' => 'text-gray-400',
        'containerClass' => 'bg-white border border-gray-100',
        'sectionClass' => 'py-12 border-t border-gray-100 max-w-5xl mx-auto px-4'
    ])

    <footer class="border-t border-gray-100 py-8 text-center text-xs text-gray-400">
        &copy; {{ date('Y') }} {{ $event->name }}. All rights reserved.
    </footer>

    @if($event->terms_and_conditions)
    <div id="termsModal" class="fixed inset-0 z-[100] hidden">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" onclick="document.getElementById('termsModal').classList.add('hidden')"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl flex flex-col max-h-[80vh]">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Syarat & Ketentuan</h3>
                    <button type="button" onclick="document.getElementById('termsModal').classList.add('hidden')" class="text-gray-400 hover:text-black">✕</button>
                </div>
                <div class="p-5 overflow-y-auto prose prose-sm max-w-none text-gray-600">
                    {!! $event->terms_and_conditions !!}
                </div>
                <div class="p-5 border-t border-gray-100">
                    <button onclick="document.getElementById('termsModal').classList.add('hidden')" class="w-full bg-black text-white py-3 rounded-lg font-bold text-sm">Saya Mengerti</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        // Currency Formatter
        const formatRupiah = (num) => 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
        const platformFee = {{ $event->platform_fee ?? 0 }};
        const promoBuyX = {{ (int) ($event->promo_buy_x ?? 0) }};
        const eventId = {{ $event->id }};
        const eventSlug = "{{ $event->slug }}";
        
        // --- Coupon Variables ---
        let appliedCoupon = null;
        let discountAmount = 0;

        function resetCoupon() {
            if (appliedCoupon || discountAmount > 0) {
                appliedCoupon = null;
                discountAmount = 0;
                const codeInput = document.getElementById('coupon_code');
                const codeHidden = document.getElementById('coupon_code_hidden');
                const msg = document.getElementById('couponMessage');
                if (codeInput) codeInput.value = '';
                if (codeHidden) codeHidden.value = '';
                if (msg) msg.innerHTML = '';
            }
        }

        // --- Multi Participant Logic ---
        let participantIndex = 1;
        const wrapper = document.getElementById('participantsWrapper');
        const addBtn = document.getElementById('addParticipantBtn');
        const totalDisplay = document.getElementById('totalDisplay');
        
        // Template for cloning (deep clone the first item)
        const template = wrapper.querySelector('.participant-item').cloneNode(true);

        function computePayableSubtotalAndCount() {
            const categoryCounts = new Map();
            const categoryPrices = new Map();
            const checkedRadios = wrapper.querySelectorAll('input.category-radio:checked');

            checkedRadios.forEach(radio => {
                const categoryId = radio.value;
                const price = parseFloat(radio.getAttribute('data-price') || 0);
                categoryCounts.set(categoryId, (categoryCounts.get(categoryId) || 0) + 1);
                categoryPrices.set(categoryId, price);
            });

            let subtotal = 0;
            categoryCounts.forEach((qty, categoryId) => {
                const price = categoryPrices.get(categoryId) || 0;
                let paidQty = qty;
                if (promoBuyX > 0) {
                    const bundleSize = promoBuyX + 1;
                    const freeCount = Math.floor(qty / bundleSize);
                    paidQty = qty - freeCount;
                }
                subtotal += price * paidQty;
            });

            return { subtotal, selectedCount: checkedRadios.length };
        }

        // Function to update total price
        function updateTotal() {
            const { subtotal, selectedCount } = computePayableSubtotalAndCount();
            
            const fee = selectedCount * platformFee;
            let total = subtotal + fee - discountAmount;
            if(total < 0) total = 0;

            document.getElementById('subtotalDisplay').textContent = formatRupiah(subtotal);
            
            if(discountAmount > 0) {
                document.getElementById('discountRow').classList.remove('hidden');
                document.getElementById('discountDisplay').textContent = '- ' + formatRupiah(discountAmount);
            } else {
                document.getElementById('discountRow').classList.add('hidden');
            }

            if(platformFee > 0) {
                document.getElementById('feeDisplay').textContent = formatRupiah(fee);
            }
            totalDisplay.textContent = formatRupiah(total);
        }

        // Event Delegation for Radio Changes (to update price)
        wrapper.addEventListener('change', (e) => {
            if(e.target.classList.contains('category-radio')) {
                resetCoupon();
                updateTotal();
            }
        });

        // Add Participant
        addBtn.addEventListener('click', () => {
            resetCoupon();
            const newItem = template.cloneNode(true);
            const idx = participantIndex++;
            
            newItem.setAttribute('data-index', idx);
            newItem.querySelector('.participant-title').textContent = `Peserta #${wrapper.children.length + 1}`;

            // Show Copy Prev Button for Index > 0
            const copyPrevBtn = newItem.querySelector('.copy-prev-btn');
            if (idx > 0) {
                copyPrevBtn.classList.remove('hidden');
            } else {
                copyPrevBtn.classList.add('hidden');
            }
            
            // Update Inputs
            newItem.querySelectorAll('input, select').forEach(el => {
                const name = el.getAttribute('name');
                if(name) {
                    // Replace index in name: participants[0][field] -> participants[idx][field]
                    el.setAttribute('name', name.replace(/participants\[\d+\]/, `participants[${idx}]`));
                    
                    // Clear values but keep radio values (value attribute)
                    if(el.type !== 'radio' && el.type !== 'hidden') {
                        el.value = '';
                    }
                    if(el.type === 'radio') {
                        el.checked = false;
                    }
                }
            });

            // Show Remove Button
            const removeBtn = newItem.querySelector('.remove-participant');
            removeBtn.classList.remove('hidden');
            
            wrapper.appendChild(newItem);
        });

        // Remove Participant
        wrapper.addEventListener('click', (e) => {
            if(e.target.classList.contains('remove-participant')) {
                e.target.closest('.participant-item').remove();
                resetCoupon();
                
                // Renumber Titles
                wrapper.querySelectorAll('.participant-item').forEach((item, i) => {
                    item.querySelector('.participant-title').textContent = `Peserta #${i + 1}`;
                });
                
                updateTotal();
            }
        });

        // Form Submit
        const form = document.getElementById('registrationForm');

        function copyFromPic(btn) {
            const participantItem = btn.closest('.participant-item');
            const picName = document.getElementById('pic_name').value;
            const picEmail = document.getElementById('pic_email').value;
            const picPhone = document.getElementById('pic_phone').value;

            if (picName) participantItem.querySelector('input[name*="[name]"]').value = picName;
            if (picEmail) participantItem.querySelector('input[name*="[email]"]').value = picEmail;
            if (picPhone) participantItem.querySelector('input[name*="[phone]"]').value = picPhone;
        }

        function copyFromPrev(btn) {
            const currentItem = btn.closest('.participant-item');
            const currentIndex = parseInt(currentItem.dataset.index);
            
            if (currentIndex > 0) {
                const prevItem = document.querySelector(`.participant-item[data-index="${currentIndex - 1}"]`);
                if (prevItem) {
                    const fields = ['emergency_contact_name', 'emergency_contact_number']; // Fields to copy
                    
                    fields.forEach(field => {
                        const prevValue = prevItem.querySelector(`input[name*="[${field}]"]`).value;
                        if (prevValue) {
                            currentItem.querySelector(`input[name*="[${field}]"]`).value = prevValue;
                        }
                    });
                }
            }
        }

        // Coupon Logic
        const couponBtn = document.getElementById('applyCouponBtn');
        if(couponBtn) {
            couponBtn.addEventListener('click', () => {
                const code = document.getElementById('coupon_code').value;
                if(!code) { alert('Masukkan kode kupon'); return; }

                let subtotal = computePayableSubtotalAndCount().subtotal;

                if(subtotal === 0) {
                    alert("Pilih kategori peserta terlebih dahulu"); return;
                }

                const originalText = couponBtn.innerHTML;
                couponBtn.innerHTML = '...';
                couponBtn.disabled = true;

                fetch(`{{ route('events.register.coupon', $event->slug) }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ event_id: eventId, coupon_code: code, total_amount: subtotal })
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        appliedCoupon = data.coupon;
                        discountAmount = data.discount_amount;
                        document.getElementById('coupon_code_hidden').value = data.coupon.code;
                        document.getElementById('coupon_code').value = data.coupon.code;
                        document.getElementById('couponMessage').innerHTML = '<span class="text-green-600">Kupon berhasil digunakan!</span>';
                        updateTotal();
                    } else {
                        document.getElementById('couponMessage').innerHTML = `<span class="text-red-500">${data.message}</span>`;
                        discountAmount = 0;
                        document.getElementById('coupon_code_hidden').value = '';
                        updateTotal();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal memproses kupon');
                })
                .finally(() => {
                    couponBtn.innerHTML = originalText;
                    couponBtn.disabled = false;
                });
            });
        }

        if(form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                if (typeof grecaptcha !== 'undefined') {
                    const recaptchaResponse = grecaptcha.getResponse();
                    if (!recaptchaResponse) {
                        alert('Silakan verifikasi reCAPTCHA terlebih dahulu.');
                        return;
                    }
                }

                const btn = document.getElementById('submitBtn');
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<span class="animate-pulse">Memproses...</span>';
                btn.disabled = true;
                
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if(data.success && data.snap_token) {
                        snap.pay(data.snap_token, {
                            onSuccess: function(result){ window.location.href = `{{ route("events.show", $event->slug) }}?payment=success`; },
                            onPending: function(result){ window.location.href = `{{ route("events.show", $event->slug) }}?payment=pending`; },
                            onError: function(result){ alert("Pembayaran gagal"); btn.disabled=false; btn.innerHTML=originalText; },
                            onClose: function(){ btn.disabled=false; btn.innerHTML=originalText; }
                        });
                    } else if (data.success && (data.payment_gateway === 'moota' || data.redirect_url)) {
                        if (window.RuangLariMoota && typeof window.RuangLariMoota.open === 'function' && data.transaction_id) {
                            btn.disabled = false;
                            btn.innerHTML = originalText;

                            const phoneEl = form.querySelector('[name="pic_phone"]');
                            const nameEl = form.querySelector('[name="pic_name"]');
                            window.RuangLariMoota.open({
                                transaction_id: data.transaction_id,
                                registration_id: data.registration_id,
                                final_amount: data.final_amount,
                                unique_code: data.unique_code,
                                phone: phoneEl ? phoneEl.value : '',
                                name: nameEl ? nameEl.value : '',
                            });
                        } else if (data.redirect_url) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert('Registrasi berhasil, namun data pembayaran tidak lengkap.');
                            btn.disabled=false; btn.innerHTML=originalText;
                        }
                    } else if(data.success) {
                         window.location.href = `{{ route("events.show", $event->slug) }}?success=true`;
                    } else {
                        alert(data.message || 'Error occurred');
                        btn.disabled=false; btn.innerHTML=originalText;
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal terhubung ke server');
                    btn.disabled=false; btn.innerHTML=originalText;
                });
            });
        }
    </script>

    @include('events.partials.moota-payment-modal', [
        'modalPanelClass' => 'bg-white text-slate-900 border border-slate-200',
        'modalTitleClass' => 'text-slate-900',
        'modalAccentClass' => 'text-accent',
        'modalCloseClass' => 'bg-accent text-white hover:bg-blue-700',
    ])
</body>
</html>
