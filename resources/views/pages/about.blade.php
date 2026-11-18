@extends('layouts.pacerhub', ['skipHeavyAssets' => true, 'skipLoader' => true])

@section('title', 'Tentang Ruang Lari - Ekosistem Lari Modern & Ticketing Event Indonesia')
@section('meta_title', 'Tentang Ruang Lari - Platform Event Lari, Pacer, Coach & Komunitas')
@section('meta_description', 'Ruang Lari adalah ekosistem lari terintegrasi di Indonesia: ticketing event dinamis, direktori pacer & coach lari, kalkulator pace, kalender lomba, dan komunitas pelari.')
@section('canonical_url', route('about'))
@section('og_image', 'https://ruanglari.com/storage/blog/media/3l1BGrryunIsbK1nQitzCXVUXpmwxsQ0vd5ziuA1.webp')

@push('styles')
<style>
    .about-heading {
        font-family: 'Inter Tight', 'Sora', sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .about-body {
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    }
</style>
@endpush

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
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
                    'name' => 'Tentang Ruang Lari',
                    'item' => route('about'),
                ],
            ],
        ],
        [
            '@type' => 'Organization',
            'name' => 'Ruang Lari',
            'url' => url('/'),
            'logo' => asset('images/ruanglari_green.png'),
            'description' => 'Ekosistem lari modern di Indonesia: ticketing event, direktori pacer dan coach lari, kalkulator performa, serta komunitas pelari.',
        ],
        [
            '@type' => 'AboutPage',
            '@id' => route('about') . '#webpage',
            'name' => 'Tentang Ruang Lari',
            'url' => route('about'),
            'description' => 'Mengenal visi, misi, dan pilar ekosistem Ruang Lari bagi pelari dan penyelenggara event di Indonesia.',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="bg-slate-950 text-slate-200 about-body min-h-screen pt-0">

    <header class="bg-slate-900 border-b border-slate-800 pt-6 pb-14 sm:pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav aria-label="Breadcrumb" class="mb-6 text-xs text-slate-400">
                <ol class="flex flex-wrap items-center gap-1.5">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition">Beranda</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-slate-200 font-medium">Tentang Ruang Lari</li>
                </ol>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                <div class="lg:col-span-7">
                    <h1 class="about-heading text-3xl sm:text-4xl lg:text-5xl text-white leading-tight">
                        Membangun Ekosistem Lari yang Terstruktur &amp; Tepercaya di Indonesia
                    </h1>

                    <p class="text-sm sm:text-base text-slate-300 mt-5 leading-relaxed max-w-2xl">
                        Ruang Lari menghubungkan pelari dan Event Organizer melalui sistem ticketing dinamis, instrumen latihan berbasis data, direktori pacer dan coach terverifikasi, serta ruang tumbuh bagi komunitas lari di Indonesia.
                    </p>

                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <a href="#ekosistem"
                           class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 text-sm font-bold transition-colors">
                            Jelajahi Ekosistem
                        </a>
                        <a href="{{ route('eo.landing') }}"
                           class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-sm font-semibold transition-colors">
                            Solusi Event Organizer
                        </a>
                    </div>

                    <div class="mt-9 pt-7 border-t border-slate-800 grid grid-cols-3 gap-4 max-w-lg">
                        <div>
                            <div class="font-mono tabular-nums text-2xl sm:text-3xl font-bold text-lime-300">7.700+</div>
                            <div class="text-xs text-slate-300 mt-1">Pelari Terhubung</div>
                        </div>
                        <div>
                            <div class="font-mono tabular-nums text-2xl sm:text-3xl font-bold text-white">100+</div>
                            <div class="text-xs text-slate-300 mt-1">Event Terdaftar</div>
                        </div>
                        <div>
                            <div class="font-mono tabular-nums text-2xl sm:text-3xl font-bold text-white">Real-Time</div>
                            <div class="text-xs text-slate-300 mt-1">Kuota &amp; Data Race</div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-lg overflow-hidden border border-slate-800 bg-slate-950">
                        <div class="aspect-[4/3] bg-slate-950 overflow-hidden">
                            <img src="https://ruanglari.com/storage/blog/media/3l1BGrryunIsbK1nQitzCXVUXpmwxsQ0vd5ziuA1.webp"
                                 alt="Komunitas pelari Ruang Lari berlatih bersama"
                                 class="w-full h-full object-cover"
                                 loading="eager"
                                 decoding="async">
                        </div>
                        <div class="p-4 border-t border-slate-800 bg-slate-950">
                            <div class="text-xs font-semibold text-lime-300">Komunitas &amp; Integritas</div>
                            <p class="text-xs text-slate-300 mt-1 leading-relaxed">Menghubungkan pelari rekreasi hingga atlet maraton di berbagai kota Indonesia.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="py-14 sm:py-20 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-start">
                <div class="lg:col-span-7">
                    <div class="text-xs font-semibold text-lime-300">Identitas &amp; Fondasi</div>
                    <h2 class="about-heading text-2xl sm:text-3xl text-white mt-2 mb-5">
                        Dibuat oleh Pelari, untuk Seluruh Ekosistem Lari
                    </h2>

                    <div class="space-y-4 text-sm sm:text-base text-slate-300 leading-relaxed">
                        <p>
                            Ruang Lari dibangun dari kebutuhan nyata akan fondasi digital yang tertata di dunia lari Indonesia. Pelari membutuhkan informasi lomba yang akurat dan panduan latihan yang terukur, sementara Event Organizer memerlukan manajemen peserta yang cepat, rapi, dan dapat diaudit.
                        </p>
                        <p>
                            Layanan kami mencakup registrasi tiket dinamis, pemantauan kuota secara real-time, kalkulator pace dan VDOT, direktori pacer serta coach berlisensi, hingga portal warta dan edukasi lari berbasis sains.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8">
                        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                            <h3 class="about-heading text-base text-white mb-1.5">Pengalaman Peserta</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Pendaftaran lomba lebih ringkas, e-ticket terbit otomatis, dan alat bantu latihan dapat diakses kapan saja.
                            </p>
                        </div>
                        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                            <h3 class="about-heading text-base text-white mb-1.5">Keandalan Operasional</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Pengelolaan nomor BIB, pelaporan transaksi transparan, dan kontrol kuota yang mencegah kelebihan kapasitas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                        <h3 class="about-heading text-lg text-white mb-5">
                            Nilai Utama
                        </h3>
                        <div class="space-y-5">
                            <div class="flex items-start gap-3.5">
                                <span class="w-7 h-7 rounded bg-slate-950 border border-slate-800 flex items-center justify-center text-lime-300 font-mono tabular-nums text-xs font-bold shrink-0">01</span>
                                <div>
                                    <h4 class="font-semibold text-sm text-white">Transparansi Data</h4>
                                    <p class="text-xs sm:text-sm text-slate-300 mt-1 leading-relaxed">Informasi event, jarak, elevasi, dan ketersediaan kuota tiket ditampilkan apa adanya.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3.5">
                                <span class="w-7 h-7 rounded bg-slate-950 border border-slate-800 flex items-center justify-center text-lime-300 font-mono tabular-nums text-xs font-bold shrink-0">02</span>
                                <div>
                                    <h4 class="font-semibold text-sm text-white">Pendekatan Berbasis Sains</h4>
                                    <p class="text-xs sm:text-sm text-slate-300 mt-1 leading-relaxed">Alat hitung pace, VDOT, dan program latihan mengacu pada kaidah fisiologi olahraga modern.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3.5">
                                <span class="w-7 h-7 rounded bg-slate-950 border border-slate-800 flex items-center justify-center text-lime-300 font-mono tabular-nums text-xs font-bold shrink-0">03</span>
                                <div>
                                    <h4 class="font-semibold text-sm text-white">Inklusivitas Komunitas</h4>
                                    <p class="text-xs sm:text-sm text-slate-300 mt-1 leading-relaxed">Mendukung pelari dari semua tingkat kemampuan, mulai dari 5K pertama hingga maraton penuh.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-14 sm:py-20 bg-slate-900 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mb-10">
                <div class="text-xs font-semibold text-lime-300">Arah &amp; Komitmen</div>
                <h2 class="about-heading text-2xl sm:text-3xl text-white mt-1.5">
                    Visi &amp; Misi Ruang Lari
                </h2>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                    Prinsip yang memandu pengembangan teknologi dan layanan bagi pelari maupun penyelenggara lomba.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="md:col-span-5 bg-slate-950 border border-slate-800 rounded-lg p-6 sm:p-7">
                    <div class="text-xs font-mono text-lime-300 mb-2">Visi</div>
                    <h3 class="about-heading text-xl text-white mb-3">Platform Lari Paling Tepercaya</h3>
                    <p class="text-sm text-slate-300 leading-relaxed">
                        Menjadi ekosistem lari dan platform event paling tepercaya di Indonesia, dengan infrastruktur ticketing yang fleksibel, basis data yang rapi, serta pengalaman peserta yang aman dan terstandar.
                    </p>
                </div>

                <div class="md:col-span-7 bg-slate-950 border border-slate-800 rounded-lg p-6 sm:p-7">
                    <div class="text-xs font-mono text-lime-300 mb-2">Misi</div>
                    <h3 class="about-heading text-xl text-white mb-3">Eksekusi Nyata di Tiga Sisi</h3>
                    <ul class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <li>Menyediakan ticketing lomba yang dinamis: kategori jarak, kuota real-time, kupon promo, registrasi komunitas, dan checkout multi-peserta.</li>
                        <li>Menghubungkan pelari dengan pacer dan pelatih terverifikasi, kalkulator pace akurat, serta fitur temu rekan lari Run Connect.</li>
                        <li>Memberdayakan Event Organizer lewat dashboard operasional, distribusi nomor BIB terstruktur, dan laporan peserta yang siap pakai.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section id="ekosistem" class="py-14 sm:py-20 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mb-10">
                <div class="text-xs font-semibold text-lime-300">Infrastruktur Layanan</div>
                <h2 class="about-heading text-2xl sm:text-3xl text-white mt-1.5">
                    Ekosistem Ruang Lari
                </h2>
                <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                    Seluruh instrumen dirancang untuk mempermudah persiapan pelari sekaligus menjaga kerapian operasional event.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-7 bg-slate-900 border border-slate-800 rounded-lg p-6 sm:p-7 flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-mono text-lime-300 mb-2">01 / Untuk Pelari &amp; Komunitas</div>
                        <h3 class="about-heading text-xl text-white mb-3">Persiapan Race, Pacer &amp; Kalender Lari</h3>
                        <p class="text-sm text-slate-300 leading-relaxed mb-5">
                            Pelari dapat menelusuri jadwal lomba di seluruh Indonesia, menghitung target pace dan VDOT, memilih program latihan, hingga menyewa pacer terverifikasi untuk hari perlombaan.
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs sm:text-sm">
                            <a href="{{ route('events.index') }}" class="p-3.5 rounded-md bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="font-semibold text-white">Kalender Lari Indonesia</div>
                                <div class="text-xs text-slate-400 mt-1">Jadwal road race &amp; trail terkurasi</div>
                            </a>
                            <a href="{{ route('pacer.index') }}" class="p-3.5 rounded-md bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="font-semibold text-white">Direktori Pacer</div>
                                <div class="text-xs text-slate-400 mt-1">Pacer terverifikasi per kota &amp; pace</div>
                            </a>
                            <a href="{{ route('programs.realistic') }}" class="p-3.5 rounded-md bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="font-semibold text-white">Program Latihan VDOT</div>
                                <div class="text-xs text-slate-400 mt-1">Rencana latihan personal berbasis data</div>
                            </a>
                            <a href="{{ route('blog.index') }}" class="p-3.5 rounded-md bg-slate-950 border border-slate-800 hover:border-slate-700 transition-colors">
                                <div class="font-semibold text-white">Warta &amp; Panduan Lari</div>
                                <div class="text-xs text-slate-400 mt-1">Artikel nutrisi, sepatu, dan strategi race</div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5 flex flex-col gap-6">
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="text-xs font-mono text-lime-300 mb-2">02 / Ticketing Event</div>
                            <h3 class="about-heading text-lg text-white mb-2.5">Registrasi &amp; Pembayaran Otomatis</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Mendukung kategori jarak, kontrol kuota real-time, kode promo, registrasi komunitas, pembayaran Virtual Account dan QRIS via Midtrans, serta penerbitan e-ticket dengan QR Code verifikasi.
                            </p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-800">
                            <a href="{{ route('eo.landing') }}" class="text-xs font-semibold text-lime-300 hover:text-lime-200 transition-colors">
                                Pelajari Sistem Tiket EO
                            </a>
                        </div>
                    </div>

                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="text-xs font-mono text-lime-300 mb-2">03 / Operasional Race</div>
                            <h3 class="about-heading text-lg text-white mb-2.5">Manajemen BIB &amp; Data Peserta</h3>
                            <p class="text-sm text-slate-300 leading-relaxed">
                                Pengelolaan kategori usia, penomoran BIB otomatis, dashboard finansial transparan, serta ekspor data siap pakai untuk kebutuhan timing system dan distribusi race pack.
                            </p>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-800">
                            <a href="{{ route('eo.landing') }}" class="text-xs font-semibold text-lime-300 hover:text-lime-200 transition-colors">
                                Konsultasi Operasional Race
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-14 sm:py-16 bg-slate-900 border-t border-slate-800 text-center">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="about-heading text-2xl sm:text-3xl text-white mb-3">
                Siap Mengembangkan Event Lari Anda Bersama Kami?
            </h2>
            <p class="text-sm sm:text-base text-slate-300 mb-7 leading-relaxed">
                Kelola pendaftaran peserta secara terstruktur, pantau kuota secara real-time, dan hadirkan pengalaman lomba yang tertata bersama Ruang Lari.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('eo.landing') }}"
                   class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-lime-400 hover:bg-lime-300 text-slate-950 font-bold text-sm transition-colors">
                    Daftarkan Event Anda
                </a>
                <a href="{{ route('events.index') }}"
                   class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-semibold text-sm transition-colors">
                    Jelajahi Kalender Lari
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
