@extends('layouts.pacerhub')
@php
    $withSidebar = false;
    $packages = [
        [
            'name' => 'Lite',
            'slug' => 'lite',
            'tagline' => 'Untuk event komunitas kecil atau fun run lokal',
            'description' => 'Sistem pendaftaran dasar dengan fitur pembayaran otomatis yang andal.',
            'badge' => 'Komunitas',
            'is_recommended' => false,
            'features' => [
                'Landing page pendaftaran standar',
                'Form pendaftaran online (Quick Reg)',
                'Integrasi payment gateway (VA, QRIS, E-Wallet)',
                'Penomoran BIB otomatis sesuai urutan',
                'Export data peserta ke Excel/CSV',
                'Dukungan via Email & dokumentasi online'
            ],
            'cta_label' => 'Mulai dengan Lite',
        ],
        [
            'name' => 'Pro',
            'slug' => 'pro',
            'tagline' => 'Untuk event menengah dengan 500 – 2.000 peserta',
            'description' => 'Solusi lengkap dengan integrasi komunikasi otomatis untuk kepuasan pelari.',
            'badge' => 'Recommended',
            'is_recommended' => true,
            'features' => [
                'Semua fitur paket Lite',
                'WhatsApp Blaster otomatis (konfirmasi registrasi, pengingat bayar)',
                'Pengiriman info nomor BIB via WhatsApp',
                'Manajemen kuota per kategori lari (10K, 5K, HM, dll.)',
                'Publikasi Race Results dinamis di landing page',
                'Dukungan dedicated Account Manager via WhatsApp'
            ],
            'cta_label' => 'Mulai dengan Pro',
        ],
        [
            'name' => 'Premium',
            'slug' => 'premium',
            'tagline' => 'Untuk event skala besar dan kebutuhan kustom',
            'description' => 'Dukungan penuh dari tim kami untuk memastikan operasional lomba berjalan lancar.',
            'badge' => 'Kustom Ops',
            'is_recommended' => false,
            'features' => [
                'Semua fitur paket Pro',
                'Kustom desain landing page sesuai sponsor/branding',
                'Dashboard khusus Race Director (arus kas, tren harian)',
                'Integrasi penarikan dana (Wallet Integration) fleksibel',
                'Race Management: Check-in RPC dengan sistem QR Code',
                'Prioritas rekomendasi event ke database pelari aktif RuangLari',
                'On-site technical support saat hari H pelaksanaan (opsional)'
            ],
            'cta_label' => 'Hubungi Kontak Kami',
        ]
    ];
@endphp

@section('title', 'Ticketing Event Lari untuk Event Organizer | Ruang Lari')
@section('meta_title', 'Ticketing Event Lari Advanced untuk Event Organizer | Ruang Lari')
@section('meta_description', 'Bangun event lari dengan sistem ticketing dinamis: kategori & kuota real-time, promo/kupon, add-ons, pembayaran terintegrasi, notifikasi, dashboard, dan manajemen peserta. Cocok untuk fun run sampai marathon.')
@section('canonical_url', url('/event-organizer'))

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-300 antialiased selection:bg-brand-500/20 selection:text-brand-400">
    
    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border border-white/10 bg-white/5 text-slate-300 text-xs font-semibold uppercase tracking-wider">
                    Solusi Ticketing Event Lari
                </span>
                
                <h1 class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight max-w-4xl mx-auto">
                    Ticketing Event Lari untuk Event Organizer yang <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-emerald-400">Butuh Sistem Rapi</span>
                </h1>
                
                <p class="mt-6 text-base sm:text-lg text-slate-400 max-w-3xl mx-auto leading-relaxed">
                    RuangLari membantu tim EO mengelola kategori tiket, kuota peserta, promo, pembayaran, komunikasi peserta, dan laporan event dalam satu sistem yang mudah dipantau.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register', ['role' => 'eo']) }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-white hover:bg-brand-400 text-slate-950 font-bold transition-all text-center duration-200">
                        Mulai untuk EO
                    </a>
                    <a href="#paket" class="w-full sm:w-auto px-8 py-4 rounded-xl border border-slate-800 hover:border-slate-700 bg-slate-900/40 text-white font-bold transition-all text-center duration-200">
                        Lihat Paket
                    </a>
                </div>
            </div>

            <!-- Realistic Product Dashboard Mockup -->
            <div class="mt-20 relative rounded-2xl border border-slate-800 bg-slate-950/80 p-4 md:p-6 shadow-2xl max-w-4xl mx-auto overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-tr from-brand-500/5 via-transparent to-transparent pointer-events-none"></div>
                <!-- Window header -->
                <div class="flex items-center justify-between border-b border-slate-900 pb-4 mb-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-red-500/80"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500/80"></span>
                        <span class="w-3 h-3 rounded-full bg-green-500/80"></span>
                    </div>
                    <div class="text-[11px] font-mono text-slate-600 tracking-wider">ruanglari.com/dashboard/eo</div>
                    <div class="w-12"></div>
                </div>
                <!-- Dashboard inner grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Sidebar mockup -->
                    <div class="hidden md:block col-span-1 border-r border-slate-900 pr-4 space-y-3">
                        <div class="h-6 bg-brand-500/10 border border-brand-500/20 rounded-lg w-full"></div>
                        <div class="h-6 bg-slate-900 rounded-lg w-11/12"></div>
                        <div class="h-6 bg-slate-900 rounded-lg w-10/12"></div>
                        <div class="h-6 bg-slate-900 rounded-lg w-8/12"></div>
                    </div>
                    <!-- Main content mockup -->
                    <div class="col-span-1 md:col-span-3 space-y-5">
                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3 bg-slate-900 rounded-xl border border-slate-800">
                                <span class="text-[9px] font-mono text-slate-500 uppercase">Total Tiket</span>
                                <div class="text-sm font-black text-white mt-1">1,250</div>
                            </div>
                            <div class="p-3 bg-slate-900 rounded-xl border border-slate-800">
                                <span class="text-[9px] font-mono text-slate-500 uppercase">Terjual</span>
                                <div class="text-sm font-black text-brand-400 mt-1">984 <span class="text-[9px] text-slate-400 font-normal">(78%)</span></div>
                            </div>
                            <div class="p-3 bg-slate-900 rounded-xl border border-slate-800">
                                <span class="text-[9px] font-mono text-slate-500 uppercase">Pendapatan</span>
                                <div class="text-sm font-black text-white mt-1">Rp 147.6M</div>
                            </div>
                        </div>
                        <!-- Recent Sales table -->
                        <div class="bg-slate-900/50 rounded-xl border border-slate-800/80 p-4 space-y-3">
                            <div class="text-xs font-semibold text-slate-400">Pendaftaran Terbaru</div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-900">
                                    <span class="text-slate-300 font-medium">Andi Wijaya</span>
                                    <span class="text-slate-500">10K Runner</span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">LUNAS</span>
                                </div>
                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-900">
                                    <span class="text-slate-300 font-medium">Siti Rahma</span>
                                    <span class="text-slate-500">5K Runner</span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">LUNAS</span>
                                </div>
                                <div class="flex items-center justify-between text-xs py-1.5">
                                    <span class="text-slate-300 font-medium">Budi Santoso</span>
                                    <span class="text-slate-500">Half Marathon</span>
                                    <span class="px-2 py-0.5 rounded bg-yellow-500/10 text-yellow-400 text-[10px] font-bold">WAITING</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Benefits (Features) Section -->
    <section class="py-24 border-t border-slate-900 bg-slate-950">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Sistem Ticketing yang Didesain untuk Skala</h2>
                <p class="mt-4 text-slate-400 leading-relaxed">Kelola seluruh kebutuhan operasional pendaftaran lomba lari Anda dengan fitur B2B SaaS yang solid dan andal.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="rounded-2xl border border-slate-900 bg-slate-900/40 p-6 hover:border-slate-800 transition duration-200">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-slate-400 mb-5 border border-slate-800">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-white font-bold text-base mb-2">Kelola kategori & kuota</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Cegah oversell, status sold-out otomatis, dan kontrol kuota per kategori secara real-time.</p>
                </div>

                <!-- Card 2 -->
                <div class="rounded-2xl border border-slate-900 bg-slate-900/40 p-6 hover:border-slate-800 transition duration-200">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-slate-400 mb-5 border border-slate-800">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="text-white font-bold text-base mb-2">Pantau bayar & registrasi</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Sistem checkout cepat terintegrasi dengan pencatatan pembayaran otomatis yang rapi.</p>
                </div>

                <!-- Card 3 -->
                <div class="rounded-2xl border border-slate-900 bg-slate-900/40 p-6 hover:border-slate-800 transition duration-200">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-slate-400 mb-5 border border-slate-800">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 8V8m0 8h2m-2 0H8m4 0V9" />
                        </svg>
                    </div>
                    <h3 class="text-white font-bold text-base mb-2">Atur kupon & add-ons</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Strategi pricing lebih dinamis untuk early momentum dengan diskon unik atau bundling.</p>
                </div>

                <!-- Card 4 -->
                <div class="rounded-2xl border border-slate-900 bg-slate-900/40 p-6 hover:border-slate-800 transition duration-200">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-slate-400 mb-5 border border-slate-800">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h4m-4 4h6m-6 4h6m2 0a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 0H7a2 2 0 00-2 2v9a2 2 0 002 2h2" />
                        </svg>
                    </div>
                    <h3 class="text-white font-bold text-base mb-2">Akses dashboard & laporan</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Manajemen peserta lengkap, ekspor database excel sekali klik, dan monitoring operasional.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Workflow Section -->
    <section class="py-24 border-t border-slate-900 bg-slate-900/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
                <div class="lg:col-span-1">
                    <span class="text-xs font-semibold text-brand-400 uppercase tracking-widest">Alur Kerja</span>
                    <h2 class="mt-3 text-3xl font-extrabold text-white tracking-tight">Cara kerja yang simple untuk tim EO</h2>
                    <p class="mt-4 text-slate-400 text-sm leading-relaxed">Dari setting event sampai seluruh peserta terkonfirmasi—tanpa dokumen spreadsheet yang berantakan.</p>
                </div>
                
                <div class="lg:col-span-2 space-y-4">
                    <!-- Step 1 -->
                    <div class="flex gap-4 p-5 rounded-xl border border-slate-900 bg-slate-950/40">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-900 border border-slate-850 flex items-center justify-center text-xs font-bold text-slate-300">1</div>
                        <div>
                            <h4 class="text-white font-bold text-sm">Setup Event</h4>
                            <p class="mt-1 text-slate-400 text-xs">Buat kategori tiket, kuota, harga, add-ons, dan periode registrasi.</p>
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="flex gap-4 p-5 rounded-xl border border-slate-900 bg-slate-950/40">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-900 border border-slate-850 flex items-center justify-center text-xs font-bold text-slate-300">2</div>
                        <div>
                            <h4 class="text-white font-bold text-sm">Atur Tiket dan Kuota</h4>
                            <p class="mt-1 text-slate-400 text-xs">Tentukan batas tiket sold-out otomatis dan masa berlaku early bird.</p>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="flex gap-4 p-5 rounded-xl border border-slate-900 bg-slate-950/40">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-900 border border-slate-850 flex items-center justify-center text-xs font-bold text-slate-300">3</div>
                        <div>
                            <h4 class="text-white font-bold text-sm">Publikasikan Event</h4>
                            <p class="mt-1 text-slate-400 text-xs">Landing page siap tayang dan pendaftaran resmi dibuka untuk umum.</p>
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="flex gap-4 p-5 rounded-xl border border-slate-900 bg-slate-950/40">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-900 border border-slate-850 flex items-center justify-center text-xs font-bold text-slate-300">4</div>
                        <div>
                            <h4 class="text-white font-bold text-sm">Pantau Registrasi</h4>
                            <p class="mt-1 text-slate-400 text-xs">Pantau transaksi masuk, sisa kuota kategori, dan status lunas secara langsung.</p>
                        </div>
                    </div>
                    <!-- Step 5 -->
                    <div class="flex gap-4 p-5 rounded-xl border border-slate-900 bg-slate-950/40">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-slate-900 border border-slate-850 flex items-center justify-center text-xs font-bold text-slate-300">5</div>
                        <div>
                            <h4 class="text-white font-bold text-sm">Export Data Peserta</h4>
                            <p class="mt-1 text-slate-400 text-xs">Unduh data lengkap peserta untuk penyiapan race pack dan kebutuhan di hari-H.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="paket" class="py-24 border-t border-slate-900 bg-slate-950">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Pilihan Paket Kerjasama</h2>
                <p class="mt-4 text-slate-400 leading-relaxed">Sistem pendaftaran dan biaya jasa transaksi yang fleksibel sesuai skala event Anda.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($packages as $pkg)
                <div class="relative rounded-2xl border {{ $pkg['is_recommended'] ? 'border-brand-500/40 bg-slate-900/60 shadow-brand-500/5' : 'border-slate-900 bg-slate-900/30' }} p-8 flex flex-col h-full transition duration-300 hover:border-slate-800 shadow-lg">
                    
                    @if($pkg['is_recommended'])
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full border border-brand-500/20 bg-brand-500/10 text-brand-400 text-[10px] font-bold uppercase tracking-wider">
                            Paling Populer
                        </span>
                    </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-white font-extrabold text-2xl">{{ $pkg['name'] }}</h3>
                        <p class="mt-2 text-xs text-brand-400 font-semibold uppercase tracking-wider">{{ $pkg['tagline'] }}</p>
                        <p class="mt-4 text-slate-400 text-sm leading-relaxed">{{ $pkg['description'] }}</p>
                    </div>

                    <div class="border-t border-slate-900 my-6"></div>

                    <!-- Features -->
                    <div class="flex-grow">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block mb-4">Fitur Utama</span>
                        <ul class="space-y-3.5">
                            @foreach($pkg['features'] as $feature)
                            <li class="flex items-start gap-3">
                                <svg class="w-4 h-4 text-emerald-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-slate-200 text-xs font-medium leading-relaxed">{{ $feature }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="border-t border-slate-900 my-6"></div>

                    <!-- Action -->
                    <div class="mt-auto">
                        <a href="{{ route('register', ['role' => 'eo', 'package_tier' => $pkg['slug']]) }}" class="w-full inline-flex items-center justify-center px-5 py-3.5 rounded-xl {{ $pkg['is_recommended'] ? 'bg-white hover:bg-brand-400 text-slate-950' : 'bg-slate-900 border border-slate-800 hover:bg-slate-800 text-white' }} font-bold transition duration-200 text-sm">
                            {{ $pkg['cta_label'] }}
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Simple FAQ Section -->
    <section class="py-24 border-t border-slate-900 bg-slate-950">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16">
                <span class="text-xs font-semibold text-brand-400 uppercase tracking-widest">Pertanyaan & Jawaban</span>
                <h2 class="mt-3 text-3xl font-extrabold text-white tracking-tight">FAQ Sistem RuangLari</h2>
                <p class="mt-3 text-slate-400 text-sm">Jawaban atas pertanyaan umum seputar pendaftaran dan kerjasama dengan RuangLari.</p>
            </div>

            <div class="space-y-4">
                <details class="group rounded-xl border border-slate-900 bg-slate-900/10 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-bold text-sm">Apakah bisa multi-kategori dan kuota per kategori?</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-400 text-xs leading-relaxed border-t border-slate-900/50 pt-3">
                        Bisa. Event dapat memiliki beberapa kategori sekaligus (seperti 5K, 10K, HM) dengan alokasi kuota tiket masing-masing. Sistem akan otomatis menutup penjualan saat kuota kategori tercapai.
                    </div>
                </details>

                <details class="group rounded-xl border border-slate-900 bg-slate-900/10 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-bold text-sm">Apakah mendukung kupon/promo dan add-ons?</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-400 text-xs leading-relaxed border-t border-slate-900/50 pt-3">
                        Ya. Anda bisa menyetel kode kupon diskon (nominal atau persentase) dengan pembatasan jumlah pemakaian. Kebutuhan penjualan add-ons (seperti upgrade jersey atau merchandise) juga didukung dalam alur registrasi.
                    </div>
                </details>

                <details class="group rounded-xl border border-slate-900 bg-slate-900/10 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-bold text-sm">Apakah peserta bisa daftar untuk beberapa orang dalam satu checkout?</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-400 text-xs leading-relaxed border-t border-slate-900/50 pt-3">
                        Ya. Alur checkout mendukung input data multi-peserta sekaligus untuk mempercepat pendaftaran kolektif atau pendaftaran bersama keluarga/komunitas dalam satu kali transaksi pembayaran.
                    </div>
                </details>

                <details class="group rounded-xl border border-slate-900 bg-slate-900/10 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-bold text-sm">Bagaimana dengan ekspor data peserta untuk race day?</span>
                        <svg class="w-4 h-4 text-slate-500 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-400 text-xs leading-relaxed border-t border-slate-900/50 pt-3">
                        Tim EO memiliki akses penuh ke dashboard pendaftaran untuk mengunduh laporan real-time dan mengekspor seluruh database peserta (data profil, ukuran jersey, kontak WhatsApp) ke format Excel/CSV kapan saja.
                    </div>
                </details>
            </div>
            
            <div class="mt-16 text-center">
                <a href="{{ route('register', ['role' => 'eo']) }}" class="inline-flex items-center justify-center px-6 py-3.5 rounded-xl bg-white hover:bg-brand-400 text-slate-950 font-bold transition duration-200 text-sm">
                    Mulai untuk EO Sekarang
                </a>
            </div>
        </div>
    </section>

</div>
@endsection

@push('structured_data')
@php
    $faqItems = [
        [
            'question' => 'Apakah bisa multi-kategori dan kuota per kategori?',
            'answer' => 'Bisa. Event dapat memiliki beberapa kategori sekaligus (seperti 5K, 10K, HM) dengan alokasi kuota tiket masing-masing. Sistem akan otomatis menutup penjualan saat kuota kategori tercapai.',
        ],
        [
            'question' => 'Apakah mendukung kupon/promo dan add-ons?',
            'answer' => 'Ya. Anda bisa menyetel kode kupon diskon (nominal atau persentase) dengan pembatasan jumlah pemakaian. Kebutuhan penjualan add-ons (seperti upgrade jersey atau merchandise) juga didukung dalam alur registrasi.',
        ],
        [
            'question' => 'Apakah peserta bisa daftar untuk beberapa orang dalam satu checkout?',
            'answer' => 'Ya. Alur checkout mendukung pendaftaran multi-peserta sekaligus dalam satu kali pembayaran.',
        ],
        [
            'question' => 'Bagaimana dengan ekspor data peserta untuk race day?',
            'answer' => 'Tim EO memiliki akses penuh ke dashboard untuk mengunduh laporan real-time dan mengekspor seluruh database peserta ke format Excel/CSV.',
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
