@extends('layouts.pacerhub')

@php
    $withSidebar = false;
@endphp

@section('title', 'Buat Registration Page & Sistem Registrasi Event Lari Gratis | RuangLari EO')
@section('meta_title', 'Buat Registration Page & Sistem Registrasi Event Lari Gratis | RuangLari EO')
@section('meta_description', 'Bingung buat registration page & sistem pendaftaran event lari? RuangLari menyediakan platform registrasi event lari gratis tanpa biaya awal. Form pendaftaran instan, E-Ticket QR, Notifikasi WA otomatis, & Dashboard EO.')
@section('meta_keywords', 'buat registration page event lari, platform registrasi event lari gratis, buat website event lari, sistem ticketing event lari, buat form pendaftaran lari, registrasi event lari otomatis, ruang lari eo, manajemen peserta lari')
@section('canonical_url', url('/event-organizer'))

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@600;700;800&family=Sora:wght@700;800&display=swap" rel="stylesheet">
<style>
    .eo-heading {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.1;
    }
    .eo-heading-sm {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.15;
    }
</style>
@endpush

@section('content')
<div class="bg-slate-950 text-slate-200 antialiased">

    {{-- Hero Section --}}
    <section class="relative pt-24 pb-16 overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('images/hero/management event lari gratis.webp') }}"
                 alt="Management Event Lari Gratis RuangLari"
                 class="w-full h-full object-cover object-center"
                 style="filter: brightness(0.35) contrast(1.1);"
                 fetchpriority="high"
                 decoding="async">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-950/60 via-slate-950/40 to-slate-950"></div>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <h1 class="eo-heading text-2xl sm:text-3xl md:text-4xl text-white mb-5 max-w-3xl">
                Buat Registration Page Event Lari Instan, Tanpa Coding, Tanpa Biaya Awal
            </h1>

            <p class="text-sm sm:text-base text-slate-200 max-w-2xl leading-relaxed mb-8">
                RuangLari menyediakan platform registrasi event lari siap pakai dengan form pendaftaran multi-kategori, payment gateway otomatis, E-Ticket QR, notifikasi WhatsApp, dan dashboard peserta.
            </p>

            <div class="flex flex-col sm:flex-row items-start gap-3">
                <a href="{{ route('register', ['role' => 'eo']) }}"
                   class="px-6 py-3 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-sm transition duration-150">
                    Buat Registration Page
                </a>
                <a href="#fitur-utama"
                   class="px-6 py-3 rounded-md bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm border border-slate-700 transition duration-150">
                    Pelajari Fitur Platform
                </a>
            </div>

            {{-- Key Metrics --}}
            <div class="mt-12 grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-2xl">
                <div>
                    <div class="text-xl font-bold text-lime-400 font-mono tabular-nums">Rp 0</div>
                    <div class="text-xs text-slate-300 mt-0.5">Biaya setup awal</div>
                </div>
                <div>
                    <div class="text-xl font-bold text-white font-mono tabular-nums">&lt; 5 Min</div>
                    <div class="text-xs text-slate-300 mt-0.5">Page siap tayang</div>
                </div>
                <div>
                    <div class="text-xl font-bold text-lime-400">Otomatis</div>
                    <div class="text-xs text-slate-300 mt-0.5">Konfirmasi WA dan QR</div>
                </div>
                <div>
                    <div class="text-xl font-bold text-white">Excel / CSV</div>
                    <div class="text-xs text-slate-300 mt-0.5">Export data real-time</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Problem & Solution Section --}}
    <section class="py-16 sm:py-20 border-t border-slate-800 bg-slate-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="eo-heading text-xl sm:text-2xl text-white mb-3">Masih Pakai Google Form dan Transfer Manual?</h2>
            <p class="text-sm text-slate-300 leading-relaxed mb-10 max-w-2xl">
                Mengelola event lari dengan alat seadanya membuang waktu dan mempersulit peserta. Berikut perbandingannya.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Cara Lama --}}
                <div class="p-6 rounded-lg bg-slate-950 border border-slate-800">
                    <h3 class="text-white font-semibold text-base mb-4">Cara Lama</h3>
                    <ul class="space-y-3 text-sm text-slate-300">
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-xmark text-red-400 text-xs mt-1 shrink-0"></i>
                            <span>Input manual via Google Form, rawan rekap ganda dan data kacau.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-xmark text-red-400 text-xs mt-1 shrink-0"></i>
                            <span>Konfirmasi bukti transfer satu per satu hingga larut malam.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-xmark text-red-400 text-xs mt-1 shrink-0"></i>
                            <span>Biaya mahal buat website custom di agency ($500 - $2000).</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-xmark text-red-400 text-xs mt-1 shrink-0"></i>
                            <span>Tidak ada notifikasi otomatis, peserta sering bertanya status pendaftaran.</span>
                        </li>
                    </ul>
                </div>

                {{-- Solusi RuangLari --}}
                <div class="p-6 rounded-lg bg-slate-800 border border-slate-700">
                    <h3 class="text-white font-semibold text-base mb-4">Dengan RuangLari EO</h3>
                    <ul class="space-y-3 text-sm text-slate-200">
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-check text-lime-400 text-xs mt-1 shrink-0"></i>
                            <span>Form pendaftaran multi-kategori (5K, 10K, HM, FM) langsung online.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-check text-lime-400 text-xs mt-1 shrink-0"></i>
                            <span>Pembayaran otomatis via VA, QRIS, dan E-Wallet. Langsung lunas.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-check text-lime-400 text-xs mt-1 shrink-0"></i>
                            <span>Notifikasi WhatsApp otomatis: konfirmasi, BIB, dan pengingat jadwal.</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <i class="fa-solid fa-check text-lime-400 text-xs mt-1 shrink-0"></i>
                            <span>Export data peserta 1-klik ke Excel untuk pencetakan BIB dan jersey.</span>
                        </li>
                    </ul>
                    <div class="mt-6 pt-4 border-t border-slate-700 flex items-center justify-between">
                        <span class="text-xs text-lime-400 font-semibold">Tanpa biaya langganan bulanan</span>
                        <a href="{{ route('register', ['role' => 'eo']) }}"
                           class="px-4 py-2 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-xs transition duration-150">
                            Buat Event
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Key Features Section --}}
    <section id="fitur-utama" class="py-16 sm:py-20 border-t border-slate-800 bg-slate-950">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="eo-heading text-xl sm:text-2xl text-white mb-3">Fitur Platform Registrasi Event Lari</h2>
            <p class="text-sm text-slate-300 leading-relaxed mb-10 max-w-2xl">
                Seluruh alat yang dibutuhkan Event Organizer untuk sukses menyelenggarakan event lari skala kecil hingga marathon.
            </p>

            {{-- Feature: Registration Page (full width, flagship) --}}
            <div class="mb-6 p-6 rounded-lg bg-slate-900 border border-slate-800">
                <div class="flex flex-col md:flex-row md:items-start gap-5">
                    <div class="shrink-0 w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-white">
                        <i class="fa-solid fa-desktop text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="eo-heading-sm text-base text-white mb-1.5">Registration Page Instan</h3>
                        <p class="text-sm text-slate-300 leading-relaxed mb-3">
                            Halaman pendaftaran event yang responsif di HP dan Desktop, lengkap dengan informasi kategori, harga tiket, lokasi, dan jadwal. Siap dipublikasikan ke media sosial dalam hitungan menit.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <span class="text-xs text-slate-300 bg-slate-800 border border-slate-700 px-2.5 py-1 rounded">Multi-Kategori</span>
                            <span class="text-xs text-slate-300 bg-slate-800 border border-slate-700 px-2.5 py-1 rounded">Responsif Mobile</span>
                            <span class="text-xs text-slate-300 bg-slate-800 border border-slate-700 px-2.5 py-1 rounded">Custom Field</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature pairs: 2 columns --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="p-6 rounded-lg bg-slate-900 border border-slate-800">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-white">
                            <i class="fa-solid fa-qrcode text-sm"></i>
                        </div>
                        <div>
                            <h3 class="eo-heading-sm text-base text-white mb-1.5">E-Ticket QR Code dan BIB</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Peserta otomatis mendapatkan E-Ticket QR Code resmi dan nomor BIB unik setelah pembayaran lunas. Scan QR saat Race Pack Collection untuk verifikasi cepat.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg bg-slate-900 border border-slate-800">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-white">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </div>
                        <div>
                            <h3 class="eo-heading-sm text-base text-white mb-1.5">Notifikasi WA Blaster</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Konfirmasi registrasi, nomor BIB, dan pengingat jadwal lari dikirimkan otomatis ke WhatsApp peserta. Tidak perlu follow-up manual satu per satu.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Feature pairs: 2 columns --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="p-6 rounded-lg bg-slate-900 border border-slate-800">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-white">
                            <i class="fa-solid fa-tags text-sm"></i>
                        </div>
                        <div>
                            <h3 class="eo-heading-sm text-base text-white mb-1.5">Multi-Kupon dan Promo</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Buat kode kupon diskon komunitas (nominal/persen) dengan pembatasan kuota otomatis. Peserta tinggal masukkan kode saat checkout.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-lg bg-slate-900 border border-slate-800">
                    <div class="flex items-start gap-4">
                        <div class="shrink-0 w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-white">
                            <i class="fa-solid fa-file-excel text-sm"></i>
                        </div>
                        <div>
                            <h3 class="eo-heading-sm text-base text-white mb-1.5">Export Data Excel / CSV</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Unduh data lengkap peserta (ukuran jersey, kontak darurat, golongan darah) sekali klik untuk persiapan Race Pack Collection dan pencetakan BIB.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Single feature: security --}}
            <div class="p-6 rounded-lg bg-slate-900 border border-slate-800">
                <div class="flex items-start gap-4">
                    <div class="shrink-0 w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-white">
                        <i class="fa-solid fa-shield-halved text-sm"></i>
                    </div>
                    <div>
                        <h3 class="eo-heading-sm text-base text-white mb-1.5">Keamanan dan Anti Oversell</h3>
                        <p class="text-sm text-slate-300 leading-relaxed max-w-2xl">
                            Kontrol kuota real-time yang mencegah tiket terjual melebihi batas (oversell) secara akurat. Setiap transaksi terjaga integritas datanya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Transparency & No Subscription --}}
    <section class="py-16 sm:py-20 border-t border-slate-800 bg-slate-900">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <h2 class="eo-heading text-xl sm:text-2xl text-white mb-3">Model Kerjasama Platform Fee Transparan</h2>
            <p class="text-sm text-slate-200 leading-relaxed mb-6">
                RuangLari beroperasi dengan skema <strong class="text-white">Platform Fee kecil per tiket yang berhasil diproses</strong>. Anda bisa langsung mendaftar akun EO, membuat event, dan mempublikasikan registration page Anda hari ini tanpa biaya paket apapun.
            </p>

            <a href="{{ route('register', ['role' => 'eo']) }}"
               class="inline-flex items-center px-6 py-3 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-sm transition duration-150">
                Buat Event Sekarang
            </a>
        </div>
    </section>

    {{-- FAQ Section --}}
    <section class="py-16 sm:py-20 border-t border-slate-800 bg-slate-950">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            <h2 class="eo-heading text-xl sm:text-2xl text-white mb-2">Pertanyaan Umum Event Organizer</h2>
            <p class="text-sm text-slate-300 mb-8">Jawaban atas pertanyaan seputar pembuatan registration page dan sistem registrasi lari di RuangLari.</p>

            <div class="space-y-3">
                <details class="group rounded-lg border border-slate-800 bg-slate-900 p-4">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-semibold text-sm">Bagaimana cara membuat registration page event lari di RuangLari?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-300 text-sm leading-relaxed border-t border-slate-800 pt-3">
                        Cukup daftar akun Event Organizer (EO) gratis di RuangLari, lalu masuk ke menu "Buat Event Baru". Isikan informasi nama event, kategori lari (5K, 10K, HM, FM), kuota, dan harga tiket. Dalam 5 menit, registration page event lari Anda siap dipublikasikan.
                    </div>
                </details>

                <details class="group rounded-lg border border-slate-800 bg-slate-900 p-4">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-semibold text-sm">Apakah ada biaya berlangganan atau beli paket di awal?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-300 text-sm leading-relaxed border-t border-slate-800 pt-3">
                        Tidak ada. Anda tidak perlu membayar paket atau langganan bulanan di awal. RuangLari beroperasi secara transparan dengan skema Platform Fee kecil yang terpotong otomatis hanya saat tiket pendaftaran berhasil diproses.
                    </div>
                </details>

                <details class="group rounded-lg border border-slate-800 bg-slate-900 p-4">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-semibold text-sm">Metode pembayaran apa saja yang bisa digunakan peserta?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-300 text-sm leading-relaxed border-t border-slate-800 pt-3">
                        Sistem RuangLari sudah terintegrasi dengan Payment Gateway nasional yang mendukung QRIS, Bank Virtual Account (BCA, Mandiri, BNI, BRI, Permata), serta E-Wallet populer. Pendaftaran langsung terverifikasi lunas secara otomatis tanpa perlu konfirmasi manual.
                    </div>
                </details>

                <details class="group rounded-lg border border-slate-800 bg-slate-900 p-4">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-semibold text-sm">Apakah bisa mengunduh data peserta untuk pencetakan BIB &amp; Jersey?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-300 text-sm leading-relaxed border-t border-slate-800 pt-3">
                        Ya. Anda memiliki akses penuh ke Dashboard EO untuk mengunduh laporan peserta real-time dalam format Excel / CSV kapan saja. Data mencakup nomor BIB, ukuran jersey, kontak WhatsApp, data medis, dan info penting lainnya.
                    </div>
                </details>
            </div>

            <div class="mt-10">
                <a href="{{ route('register', ['role' => 'eo']) }}"
                   class="inline-flex items-center px-6 py-3 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-sm transition duration-150">
                    Buat Registration Page Sekarang
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
            'question' => 'Bagaimana cara membuat registration page event lari di RuangLari?',
            'answer' => 'Cukup daftar akun Event Organizer (EO) gratis di RuangLari, lalu masuk ke menu Buat Event Baru. Isikan informasi nama event, kategori lari (5K, 10K, HM, FM), kuota, dan harga tiket. Dalam 5 menit, registration page event lari Anda siap dipublikasikan.',
        ],
        [
            'question' => 'Apakah ada biaya berlangganan atau beli paket di awal?',
            'answer' => 'Tidak ada. Anda tidak perlu membayar paket atau langganan bulanan di awal. RuangLari beroperasi secara transparan dengan skema Platform Fee kecil yang terpotong otomatis hanya saat tiket pendaftaran berhasil diproses.',
        ],
        [
            'question' => 'Metode pembayaran apa saja yang bisa digunakan peserta?',
            'answer' => 'Sistem RuangLari sudah terintegrasi dengan Payment Gateway nasional yang mendukung QRIS, Bank Virtual Account (BCA, Mandiri, BNI, BRI, Permata), serta E-Wallet populer.',
        ],
        [
            'question' => 'Apakah bisa mengunduh data peserta untuk pencetakan BIB & Jersey?',
            'answer' => 'Ya. Anda memiliki akses penuh ke Dashboard EO untuk mengunduh laporan peserta real-time dalam format Excel / CSV kapan saja.',
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebPage',
            'name' => 'Buat Registration Page & Sistem Registrasi Event Lari Gratis',
            'url' => url('/event-organizer'),
        ],
        [
            '@type' => 'SoftwareApplication',
            'name' => 'RuangLari EO Platform',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'All',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'IDR',
            ],
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
