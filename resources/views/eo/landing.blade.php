@extends('layouts.pacerhub')
@php
    $withSidebar = false;
    $packages = [
        [
            'name' => 'LITE',
            'tagline' => 'Budget-friendly, Fun Run, Quick Reg',
            'description' => 'Cocok untuk komunitas kecil atau Fun Run lokal.',
            'features' => [
                'Registrasi cepat online',
                'Pembayaran praktis (VA, QR, e-wallet)',
                'Landing page standar'
            ],
            // Visuals
            'icon_class' => 'fas fa-leaf',
            'icon_bg' => 'bg-slate-800',
            'icon_text' => 'text-neon',
            'border_hover' => 'hover:border-neon/30',
            'shadow_hover' => 'hover:shadow-neon/20',
            'badge' => 'Budget Friendly',
            'badge_bg' => 'bg-slate-800',
            'badge_text' => 'text-slate-300',
            'button_bg' => 'bg-neon',
            'button_text_color' => 'text-dark',
            'button_hover' => 'hover:bg-neon/90',
            'button_icon' => 'fas fa-rocket',
            'button_label' => 'Mulai dari Paket LITE',
            'ring_hover' => 'group-hover:ring-neon/30',
            
            'more_features' => [
                 ['icon' => 'fas fa-globe', 'text' => '<span class="text-white font-bold">Landing Page</span>: Template standar dinamis (custom teks & gambar)', 'icon_color' => 'text-neon'],
                 ['icon' => 'fas fa-clipboard-check', 'text' => '<span class="text-white font-bold">Registrasi</span>: Online form dengan sistem "Quick Reg"', 'icon_color' => 'text-neon'],
                 ['icon' => 'fas fa-credit-card', 'text' => '<span class="text-white font-bold">Pembayaran</span>: Gateway otomatis (VA, QRIS, E-Wallet)', 'icon_color' => 'text-neon'],
                 ['icon' => 'fas fa-database', 'text' => '<span class="text-white font-bold">Manajemen Peserta</span>: Dashboard untuk ekspor database pelari', 'icon_color' => 'text-neon'],
                 ['icon' => 'fas fa-hashtag', 'text' => '<span class="text-white font-bold">Nomor BIB</span>: Penomoran otomatis sesuai urutan pendaftaran', 'icon_color' => 'text-neon'],
                 ['icon' => 'fas fa-envelope-open-text', 'text' => '<span class="text-white font-bold">Support</span>: Email support & panduan penggunaan sistem', 'icon_color' => 'text-neon'],
            ]
        ],
        [
            'name' => 'PRO',
            'tagline' => 'Komunikasi otomatis, WhatsApp Blaster, 500–2000 peserta',
            'description' => 'Ideal untuk event menengah dengan kebutuhan komunikasi otomatis.',
            'features' => [
                'Semua fitur LITE',
                'WhatsApp otomatis (pengingat & nomor BIB)',
                'Channel komunitas & pacer'
            ],
            // Visuals
            'icon_class' => 'fas fa-bolt',
            'icon_bg' => 'bg-slate-800',
            'icon_text' => 'text-yellow-400',
            'border_hover' => 'hover:border-neon/30',
            'shadow_hover' => 'hover:shadow-neon/20',
            'badge' => 'Recommended',
            'badge_bg' => 'bg-yellow-500/20',
            'badge_text' => 'text-yellow-300',
            'button_bg' => 'bg-yellow-400',
            'button_text_color' => 'text-black',
            'button_hover' => 'hover:bg-yellow-300',
            'button_icon' => 'fas fa-bolt',
            'button_label' => 'Upgrade ke PRO',
            'ring_hover' => 'group-hover:ring-yellow-300/40',
            
            'more_features' => [
                 ['icon' => 'fas fa-check-circle', 'text' => 'Semua Fitur Paket LITE', 'icon_color' => 'text-yellow-400'],
                 ['icon' => 'fab fa-whatsapp', 'text' => '<span class="text-white font-bold">WhatsApp Blaster</span>: Notifikasi otomatis daftar berhasil, pengingat pembayaran, dan pengiriman nomor BIB', 'icon_color' => 'text-green-400'],
                 ['icon' => 'fas fa-users', 'text' => '<span class="text-white font-bold">Channel Pacer & Komunitas</span>: Koordinasi pacer dan integrasi grup komunitas partner ruanglari.com', 'icon_color' => 'text-yellow-400'],
                 ['icon' => 'fas fa-list-ol', 'text' => '<span class="text-white font-bold">Manajemen BIB</span>: Kategori (10K, 5K, Kids Dash) dengan prefix BIB berbeda', 'icon_color' => 'text-yellow-400'],
                 ['icon' => 'fas fa-trophy', 'text' => '<span class="text-white font-bold">Race Results</span>: Publikasi hasil lari langsung di landing page', 'icon_color' => 'text-yellow-400'],
                 ['icon' => 'fas fa-headset', 'text' => '<span class="text-white font-bold">Support</span>: Dedicated Account Manager (WhatsApp support)', 'icon_color' => 'text-yellow-400'],
            ]
        ],
        [
            'name' => 'Premium',
            'tagline' => 'Custom, Full Management, Event besar',
            'description' => 'Untuk event besar & kompleks dengan dukungan penuh Race Director.',
            'features' => [
                'Semua fitur PRO',
                'Landing page custom',
                'Monitoring peserta & pembayaran real-time',
                'Support penuh Race Director'
            ],
             // Visuals
            'icon_class' => 'fas fa-crown',
            'icon_bg' => 'bg-slate-800',
            'icon_text' => 'text-purple-400',
            'border_hover' => 'hover:border-neon/30',
            'shadow_hover' => 'hover:shadow-neon/20',
            'badge' => 'Premium',
            'badge_bg' => 'bg-purple-500/20',
            'badge_text' => 'text-purple-300',
            'button_bg' => 'bg-purple-500',
            'button_text_color' => 'text-white',
            'button_hover' => 'hover:bg-purple-400',
            'button_icon' => 'fas fa-crown',
            'button_label' => 'Konsultasi Paket Premium',
            'ring_hover' => 'group-hover:ring-purple-400/40',
            
            'more_features' => [
                 ['icon' => 'fas fa-check-circle', 'text' => 'Semua Fitur Paket PRO', 'icon_color' => 'text-purple-400'],
                 ['icon' => 'fas fa-paint-brush', 'text' => '<span class="text-white font-bold">Custom Landing Page</span>: Desain premium sesuai branding sponsor', 'icon_color' => 'text-purple-400'],
                 ['icon' => 'fas fa-chart-line', 'text' => '<span class="text-white font-bold">Channel Race Director</span>: Dashboard analitik real-time (grafik pendaftaran, arus kas, demografi)', 'icon_color' => 'text-purple-400'],
                 ['icon' => 'fas fa-wallet', 'text' => '<span class="text-white font-bold">Wallet Integration</span>: Pengelolaan dana pendaftaran lebih fleksibel bagi panitia', 'icon_color' => 'text-purple-400'],
                 ['icon' => 'fas fa-qrcode', 'text' => '<span class="text-white font-bold">Race Management Advance</span>: Check-in RPC dengan QR Code', 'icon_color' => 'text-purple-400'],
                 ['icon' => 'fas fa-database', 'text' => '<span class="text-white font-bold">Prioritas Database</span>: Rekomendasi event ke database pelari aktif ruanglari.com', 'icon_color' => 'text-purple-400'],
                 ['icon' => 'fas fa-people-carry', 'text' => '<span class="text-white font-bold">Support</span>: On-site technical support saat hari H (opsional)', 'icon_color' => 'text-purple-400'],
            ]
        ]
    ];
@endphp

@section('title', 'Ticketing Event Lari untuk Event Organizer | Ruang Lari')
@section('meta_title', 'Ticketing Event Lari Advanced untuk Event Organizer | Ruang Lari')
@section('meta_description', 'Bangun event lari dengan sistem ticketing dinamis: kategori & kuota real-time, promo/kupon, add-ons, pembayaran terintegrasi, notifikasi, dashboard, dan manajemen peserta. Cocok untuk fun run sampai marathon.')
@section('canonical_url', url('/event-organizer'))

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-900">
    <div class="max-w-6xl mx-auto px-4 pt-24 pb-20">
        <div class="text-center mb-10">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neon/20 bg-neon/5 backdrop-blur-md text-neon text-xs font-bold uppercase tracking-wider">
                Solusi Ticketing Event Lari
            </span>
            <h1 class="mt-5 text-4xl md:text-6xl font-black text-white tracking-tight">
                Ticketing Event Lari <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon via-green-400 to-emerald-500">Advanced</span>
            </h1>
            <p class="mt-4 text-slate-400 max-w-3xl mx-auto">
                Ruang Lari membantu Event Organizer menjual tiket lebih cepat dengan sistem yang dinamis: kategori & kuota real-time, promo/kupon, add-ons, pembayaran terintegrasi, notifikasi, dashboard, dan manajemen peserta.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('register', ['role' => 'eo']) }}" class="px-7 py-3.5 rounded-xl bg-neon text-dark font-black hover:bg-white transition w-full sm:w-auto text-center">
                    Mulai Untuk EO
                </a>
                <a href="#paket" class="px-7 py-3.5 rounded-xl border border-slate-700 text-white font-bold hover:bg-slate-800 transition w-full sm:w-auto text-center">
                    Lihat Paket
                </a>
                <a href="{{ route('events.index') }}" class="px-7 py-3.5 rounded-xl border border-slate-700 text-white font-bold hover:bg-slate-800 transition w-full sm:w-auto text-center">
                    Contoh Event
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-12">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm p-6">
                <div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                </div>
                <div class="text-white font-black mb-1">Kategori & Kuota Real-time</div>
                <div class="text-slate-400 text-sm">Cegah oversell, status sold-out otomatis, dan kontrol kuota per kategori.</div>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm p-6">
                <div class="w-12 h-12 rounded-xl bg-neon/10 border border-neon/20 flex items-center justify-center text-neon mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5 5.5L4 19l5.5-5.5M14 9l5.5-5.5L20 4l-5.5 5.5M6 6l12 12" /></svg>
                </div>
                <div class="text-white font-black mb-1">Promo/Kupon & Add-ons</div>
                <div class="text-slate-400 text-sm">Pricing lebih lincah untuk early momentum: kupon dan add-ons per peserta.</div>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm p-6">
                <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-300 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2v1c0 1.105 1.343 2 3 2s3 .895 3 2v1c0 1.105-1.343 2-3 2m0-14v2m0 16v2m9-10a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div class="text-white font-black mb-1">Pembayaran Terintegrasi</div>
                <div class="text-slate-400 text-sm">Checkout cepat dengan status pembayaran yang rapi dan mudah dipantau.</div>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm p-6">
                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-300 mb-4">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h2m-6 6h6m2 0a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2h2" /></svg>
                </div>
                <div class="text-white font-black mb-1">Dashboard & Race Ops</div>
                <div class="text-slate-400 text-sm">Manajemen peserta, export data, dan dukungan operasional untuk hari-H.</div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-800 bg-slate-900/50 p-8 md:p-10 mb-12">
            <div class="grid md:grid-cols-3 gap-6">
                <div class="md:col-span-1">
                    <div class="text-neon text-xs font-black uppercase tracking-widest">Alur Kerja</div>
                    <div class="mt-2 text-2xl md:text-3xl font-black text-white">Cara kerja yang simple untuk tim EO</div>
                    <div class="mt-3 text-slate-400 text-sm">Dari setting event sampai peserta terkonfirmasi—tanpa spreadsheet yang berantakan.</div>
                </div>
                <div class="md:col-span-2 grid sm:grid-cols-3 gap-4">
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                        <div class="text-slate-400 text-xs font-black uppercase tracking-widest">Step 01</div>
                        <div class="mt-2 text-white font-black">Setup Event</div>
                        <div class="mt-2 text-slate-400 text-sm">Buat kategori tiket, kuota, harga, add-ons, dan periode registrasi.</div>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                        <div class="text-slate-400 text-xs font-black uppercase tracking-widest">Step 02</div>
                        <div class="mt-2 text-white font-black">Jual Tiket</div>
                        <div class="mt-2 text-slate-400 text-sm">Peserta daftar, gunakan kupon, pilih add-ons, dan bayar via metode tersedia.</div>
                    </div>
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                        <div class="text-slate-400 text-xs font-black uppercase tracking-widest">Step 03</div>
                        <div class="mt-2 text-white font-black">Kelola Peserta</div>
                        <div class="mt-2 text-slate-400 text-sm">Pantau transaksi, rapikan data, export, dan dukung kebutuhan race day.</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="paket" class="grid md:grid-cols-3 gap-6">
            @foreach($packages as $pkg)
            <div class="group relative rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm p-6 {{ $pkg['border_hover'] }} transition-all shadow-lg {{ $pkg['shadow_hover'] }} flex flex-col h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl {{ $pkg['icon_bg'] }} flex items-center justify-center {{ $pkg['icon_text'] }}">
                            <i class="{{ $pkg['icon_class'] }}"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-extrabold text-xl">{{ $pkg['name'] }}</h3>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-lg {{ $pkg['badge_bg'] }} {{ $pkg['badge_text'] }} text-xs font-bold">{{ $pkg['badge'] }}</span>
                </div>
                
                <div class="mb-4">
                    <p class="text-xs text-neon font-bold uppercase tracking-wide mb-1">{{ $pkg['tagline'] }}</p>
                    <p class="text-slate-300 text-sm">{{ $pkg['description'] }}</p>
                </div>

                <ul class="space-y-2 text-sm mb-6">
                    @foreach($pkg['features'] as $feature)
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check text-neon mt-0.5"></i>
                        <span class="text-white font-bold">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>

                <div class="mt-auto space-y-4">
                    <!-- Accordion for more features -->
                    <details class="group/accordion">
                        <summary class="list-none cursor-pointer flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-xs font-bold uppercase tracking-wider select-none">
                            <span>Fitur Lainnya</span>
                            <i class="fas fa-chevron-down transition-transform group-open/accordion:rotate-180"></i>
                        </summary>
                        <ul class="space-y-2 text-sm mt-3 pt-3 border-t border-slate-800/50 animate-in fade-in slide-in-from-top-2 duration-300">
                            @foreach($pkg['more_features'] as $more)
                            <li class="flex items-start gap-3">
                                <i class="{{ $more['icon'] }} {{ $more['icon_color'] ?? 'text-neon' }} mt-0.5"></i>
                                <div class="text-slate-300 text-xs">{!! $more['text'] !!}</div>
                            </li>
                            @endforeach
                        </ul>
                    </details>

                    <a href="{{ route('register', ['role' => 'eo']) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl {{ $pkg['button_bg'] }} {{ $pkg['button_text_color'] }} font-black {{ $pkg['button_hover'] }} transition relative z-10">
                        <i class="{{ $pkg['button_icon'] }}"></i> {{ $pkg['button_label'] }}
                    </a>
                </div>

                <div class="absolute inset-0 rounded-2xl ring-1 ring-white/5 {{ $pkg['ring_hover'] }} transition pointer-events-none"></div>
            </div>
            @endforeach
        </div>

        <div class="mt-12 grid md:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <i class="fas fa-shield-alt text-neon"></i>
                    <h4 class="text-white font-bold">Pembayaran Aman</h4>
                </div>
                <p class="text-slate-400 text-sm">VA, QRIS, dan E-Wallet dengan settlement yang transparan.</p>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <i class="fas fa-mobile-alt text-neon"></i>
                    <h4 class="text-white font-bold">Komunikasi Otomatis</h4>
                </div>
                <p class="text-slate-400 text-sm">WhatsApp blaster terintegrasi agar peserta selalu terinformasi.</p>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
                <div class="flex items-center gap-3 mb-3">
                    <i class="fas fa-chart-pie text-neon"></i>
                    <h4 class="text-white font-bold">Analitik & Dashboard</h4>
                </div>
                <p class="text-slate-400 text-sm">Pantau pendaftaran, demografi, dan status pembayaran secara real-time.</p>
            </div>
        </div>

        <div class="mt-12 rounded-3xl border border-slate-800 bg-slate-900/50 p-8 md:p-10">
            <div class="flex flex-col md:flex-row items-start md:items-end justify-between gap-6">
                <div>
                    <div class="text-neon text-xs font-black uppercase tracking-widest">FAQ</div>
                    <div class="mt-2 text-2xl md:text-3xl font-black text-white">Pertanyaan yang sering ditanyakan</div>
                    <div class="mt-3 text-slate-400 text-sm max-w-2xl">Jawaban singkat untuk mempercepat keputusan dan eksekusi event.</div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('register', ['role' => 'eo']) }}" class="px-6 py-3 rounded-xl bg-neon text-dark font-black hover:bg-white transition">
                        Mulai Sekarang
                    </a>
                    <a href="{{ route('home') }}" class="px-6 py-3 rounded-xl border border-slate-700 text-white font-bold hover:bg-slate-800 transition">
                        Kembali ke Home
                    </a>
                </div>
            </div>

            <div class="mt-8 space-y-3">
                <details class="group rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4">
                        <span class="text-white font-black">Apakah bisa multi-kategori dan kuota per kategori?</span>
                        <i class="fas fa-chevron-down text-slate-400 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <div class="mt-3 text-slate-400 text-sm leading-relaxed">
                        Bisa. Event dapat memiliki beberapa kategori (mis. 5K/10K/HM) dengan kuota masing-masing dan status sold-out yang mengikuti kuota.
                    </div>
                </details>

                <details class="group rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4">
                        <span class="text-white font-black">Apakah mendukung kupon/promo dan add-ons?</span>
                        <i class="fas fa-chevron-down text-slate-400 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <div class="mt-3 text-slate-400 text-sm leading-relaxed">
                        Ya. Kupon/promo dan add-ons bisa membantu strategi konversi (contoh: early momentum, bundling, upsell).
                    </div>
                </details>

                <details class="group rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4">
                        <span class="text-white font-black">Apakah peserta bisa daftar untuk beberapa orang dalam satu checkout?</span>
                        <i class="fas fa-chevron-down text-slate-400 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <div class="mt-3 text-slate-400 text-sm leading-relaxed">
                        Bisa. Alur pendaftaran mendukung input multi-peserta sehingga pembelian kolektif lebih mudah.
                    </div>
                </details>

                <details class="group rounded-2xl border border-slate-800 bg-slate-950/40 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4">
                        <span class="text-white font-black">Bagaimana dengan data peserta dan kebutuhan race day?</span>
                        <i class="fas fa-chevron-down text-slate-400 transition-transform group-open:rotate-180"></i>
                    </summary>
                    <div class="mt-3 text-slate-400 text-sm leading-relaxed">
                        Anda bisa mengelola data peserta, ekspor data, dan menyiapkan operasional yang lebih rapi untuk hari-H.
                    </div>
                </details>
            </div>
        </div>
    </div>
</div>
@endsection

@push('structured_data')
@php
    $faqItems = [
        [
            'question' => 'Apakah bisa multi-kategori dan kuota per kategori?',
            'answer' => 'Bisa. Event dapat memiliki beberapa kategori (mis. 5K/10K/HM) dengan kuota masing-masing dan status sold-out yang mengikuti kuota.',
        ],
        [
            'question' => 'Apakah mendukung kupon/promo dan add-ons?',
            'answer' => 'Ya. Kupon/promo dan add-ons bisa membantu strategi konversi (contoh: early momentum, bundling, upsell).',
        ],
        [
            'question' => 'Apakah peserta bisa daftar untuk beberapa orang dalam satu checkout?',
            'answer' => 'Bisa. Alur pendaftaran mendukung input multi-peserta sehingga pembelian kolektif lebih mudah.',
        ],
        [
            'question' => 'Bagaimana dengan data peserta dan kebutuhan race day?',
            'answer' => 'Anda bisa mengelola data peserta, ekspor data, dan menyiapkan operasional yang lebih rapi untuk hari-H.',
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebPage',
            'name' => 'Ticketing Event Lari untuk Event Organizer',
            'url' => url('/event-organizer'),
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function ($item) {
                return [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            }, $faqItems),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
