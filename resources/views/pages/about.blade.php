@extends('layouts.pacerhub')

@section('title', 'Tentang Ruang Lari - Ekosistem Lari Modern & Ticketing Event Indonesia')
@section('meta_title', 'Tentang Ruang Lari - Platform Event Lari, Coach & Komunitas')
@section('meta_description', 'Ruang Lari adalah ekosistem lari terintegrasi di Indonesia: ticketing event dinamis, direktori coach lari berlisensi, kalkulator pace, kalender lomba, dan komunitas pelari.')
@section('canonical_url', route('about'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">

<style>
    .font-heading {
        font-family: 'Inter Tight', 'Sora', -apple-system, BlinkMacSystemFont, sans-serif;
        font-weight: 800;
        letter-spacing: -0.03em;
    }
    .font-body {
        font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .focus-ring:focus {
        outline: none;
        box-shadow: 0 0 0 2px #020617, 0 0 0 4px #ea580c;
    }
</style>
@endpush

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            'name' => 'Ruang Lari',
            'url' => url('/'),
            'logo' => asset('images/ruanglari_green.png'),
            'description' => 'Ekosistem lari modern di Indonesia: ticketing event, direktori coach lari, kalkulator performa, dan komunitas pelari.',
        ],
        [
            '@type' => 'WebPage',
            '@id' => route('about') . '#webpage',
            'name' => 'Tentang Ruang Lari',
            'url' => route('about'),
            'description' => 'Mengenal visi, misi, dan pilar ekosistem Ruang Lari bagi pelari dan penyelenggara event di Indonesia.',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@section('content')
<div class="bg-slate-950 text-slate-200 font-body min-h-screen pt-0">

    <!-- ================================================
         SECTION 1: HERO AREA (BG-SLATE-900 WITH BORDER-B)
         Solid, grounded athletic hero surface
         ================================================ -->
    <header class="bg-slate-900 border-b border-slate-800 py-16 sm:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Text Column -->
                <div class="lg:col-span-7">
                    <h1 class="font-heading text-3xl sm:text-4xl lg:text-5xl text-white leading-tight">
                        Membangun Ekosistem Lari yang Terstruktur & Tepercaya di Indonesia
                    </h1>
                    
                    <p class="text-base sm:text-lg text-slate-300 mt-5 leading-relaxed max-w-2xl">
                        Ruang Lari hadir untuk menjembatani pelari dan Event Organizer melalui teknologi ticketing dinamis, kalkulator performa, pendampingan coach berlisensi, dan ruang bertumbuh bagi komunitas.
                    </p>

                    <!-- CTAs -->
                    <div class="mt-8 flex flex-wrap items-center gap-3 sm:gap-4">
                        <a href="#ekosistem" 
                           class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-orange-600 hover:bg-orange-500 text-white text-sm font-bold tracking-wide transition-colors">
                            Jelajahi Ekosistem
                        </a>
                        <a href="{{ route('eo.landing') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 text-sm font-semibold tracking-wide transition-colors">
                            Solusi Event Organizer
                        </a>
                    </div>

                    <!-- Telemetry Strip (Key Metrics) -->
                    <div class="mt-10 pt-8 border-t border-slate-800 grid grid-cols-3 gap-4 text-left max-w-lg">
                        <div>
                            <div class="font-mono text-2xl sm:text-3xl font-bold text-white">7.700+</div>
                            <div class="text-xs text-slate-400 mt-0.5">Pelari Terhubung</div>
                        </div>
                        <div>
                            <div class="font-mono text-2xl sm:text-3xl font-bold text-white">100+</div>
                            <div class="text-xs text-slate-400 mt-0.5">Event Terdaftar</div>
                        </div>
                        <div>
                            <div class="font-mono text-2xl sm:text-3xl font-bold text-white">100%</div>
                            <div class="text-xs text-slate-400 mt-0.5">Berbasis Data Riil</div>
                        </div>
                    </div>
                </div>

                <!-- Visual Card Column -->
                <div class="lg:col-span-5">
                    <div class="relative rounded-lg overflow-hidden border border-slate-800 bg-slate-950 shadow-lg aspect-[4/3] lg:aspect-[5/4]">
                        <img src="https://ruanglari.com/storage/blog/media/3l1BGrryunIsbK1nQitzCXVUXpmwxsQ0vd5ziuA1.webp" 
                             alt="Komunitas pelari Ruang Lari berlatih bersama" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent pointer-events-none"></div>
                        <div class="absolute bottom-4 left-5 right-5 text-white">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-orange-400 block mb-0.5">Komunitas & Integritas</span>
                            <p class="text-xs text-slate-300">Menghubungkan pelari rekreasi hingga atlet maraton di seluruh nusantara</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ================================================
         SECTION 2: SIAPA KAMI (BG-SLATE-950 CANVAS)
         Sharp surface contrast vs hero above
         ================================================ -->
    <section class="py-16 sm:py-24 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                <!-- Text Story -->
                <div class="lg:col-span-7">
                    <span class="text-xs font-bold text-orange-500 uppercase tracking-wider">Identitas & Fondasi</span>
                    <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl text-white mt-2 mb-6">
                        Dibuat oleh Pelari, untuk Seluruh Ekosistem Lari
                    </h2>
                    
                    <div class="space-y-4 text-sm sm:text-base text-slate-300 leading-relaxed">
                        <p>
                            Ruang Lari didirikan atas kesadaran bahwa olahraga lari di Indonesia membutuhkan fondasi digital yang lebih tertata. Kami memahami dinamika dua sisi: pengalaman para pelari yang menginginkan informasi jelas dan latihan terarah, serta kebutuhan operasional Event Organizer yang memerlukan manajemen peserta yang cepat dan akurat.
                        </p>
                        <p>
                            Fokus kami melampaui sekadar direktori lomba. Kami membangun sistem terintegrasi yang mencakup registrasi tiket dinamis, pemantauan kuota real-time, kalkulator pace berbasis elevasi rute, bimbingan coach bersertifikasi, serta media komunitas yang mengedepankan edukasi ilmiah.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-8">
                        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Pengalaman Peserta</div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Pendaftaran lomba bebas kendala, e-ticket otomatis, dan materi latihan pendukung yang dapat diakses kapan saja.
                            </p>
                        </div>
                        <div class="bg-slate-900 border border-slate-800 rounded-lg p-5">
                            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Keandalan Operasional</div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Dashboard pengelolaan BIB, pelaporan keuangan transparan, dan sistem kuota yang mencegah kelebihan kapasitas.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Secondary Visual -->
                <div class="lg:col-span-5">
                    <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 sm:p-8">
                        <h3 class="font-heading text-lg font-bold text-white mb-4">
                            Nilai-Nilai Utama
                        </h3>
                        <div class="space-y-4 text-xs sm:text-sm">
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded bg-slate-800 border border-slate-700 flex items-center justify-center text-orange-500 font-mono font-bold shrink-0">1</span>
                                <div>
                                    <h4 class="font-bold text-white">Transparansi Data</h4>
                                    <p class="text-slate-400 mt-0.5">Informasi event, rute, elevasi, dan kuota tiket disajikan apa adanya tanpa manipulasi.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded bg-slate-800 border border-slate-700 flex items-center justify-center text-orange-500 font-mono font-bold shrink-0">2</span>
                                <div>
                                    <h4 class="font-bold text-white">Pendekatan Berbasis Sains</h4>
                                    <p class="text-slate-400 mt-0.5">Alat hitung pace, VDOT, dan program latihan disusun mengacu pada kaidah fisiologi olahraga modern.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <span class="w-6 h-6 rounded bg-slate-800 border border-slate-700 flex items-center justify-center text-orange-500 font-mono font-bold shrink-0">3</span>
                                <div>
                                    <h4 class="font-bold text-white">Inklusivitas Komunitas</h4>
                                    <p class="text-slate-400 mt-0.5">Mendukung pelari dari semua latar belakang, mulai dari lari 5K pertama hingga maratonis berpengalaman.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================
         SECTION 3: VISI & MISI (BG-SLATE-900 WITH BORDER-Y)
         Distinct visual section break
         ================================================ -->
    <section class="py-16 sm:py-24 bg-slate-900 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold text-orange-500 uppercase tracking-wider">Arah & Komitmen</span>
                <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl text-white mt-1">
                    Visi & Misi Ruang Lari
                </h2>
                <p class="text-sm text-slate-400 mt-2">
                    Prinsip pemandu kami dalam mengembangkan layanan olahraga lari di Indonesia.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Visi Card -->
                <div class="bg-slate-950 border border-slate-800 rounded-lg p-8 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-center text-orange-500 mb-6">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <h3 class="font-heading text-xl font-bold text-white mb-3">Visi</h3>
                        <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
                            Menjadi platform event lari dan ekosistem pelari paling tepercaya di Indonesia—dengan infrastruktur ticketing yang fleksibel, basis data yang rapi, dan pengalaman peserta yang aman, nyaman, serta berstandar tinggi.
                        </p>
                    </div>
                </div>

                <!-- Misi Card -->
                <div class="bg-slate-950 border border-slate-800 rounded-lg p-8 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-md bg-slate-900 border border-slate-800 flex items-center justify-center text-orange-500 mb-6">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="font-heading text-xl font-bold text-white mb-3">Misi</h3>
                        <ul class="space-y-3.5 text-xs sm:text-sm text-slate-300">
                            <li class="flex items-start gap-3">
                                <span class="text-orange-500 font-bold shrink-0 mt-0.5">•</span>
                                <span>Menyediakan ticketing lomba lari yang dinamis: kategori jarak, manajemen kuota real-time, kupon promo, add-ons, serta checkout multi-peserta.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-orange-500 font-bold shrink-0 mt-0.5">•</span>
                                <span>Menghubungkan pelari dengan pelatih lari terverifikasi, kalkulator pace akurat, dan fitur temu rekan lari (*Run Connect*).</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="text-orange-500 font-bold shrink-0 mt-0.5">•</span>
                                <span>Memberdayakan Event Organizer melalui dashboard operasional, distribusi BIB terstruktur, dan analisis data peserta yang komprehensif.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================
         SECTION 4: EKOSISTEM RUANG LARI (BG-SLATE-950)
         3 Pillars of services & technology
         ================================================ -->
    <section id="ekosistem" class="py-16 sm:py-24 bg-slate-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold text-orange-500 uppercase tracking-wider">Infrastruktur Layanan</span>
                <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl text-white mt-1">
                    Tiga Pilar Ekosistem Ruang Lari
                </h2>
                <p class="text-sm text-slate-400 mt-2">
                    Dirancang untuk meningkatkan konversi pendaftaran, keakuratan data, dan kualitas latihan pelari.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Pilar 1 -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 sm:p-8 hover:border-slate-700 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-mono font-bold text-orange-500 mb-2">01 / RUNNER PLATFORM</div>
                        <h3 class="font-heading text-lg font-bold text-white mb-4">Untuk Pelari</h3>
                        <ul class="space-y-3 text-xs sm:text-sm text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Kalender event terkurasi & terupdate di seluruh kota Indonesia.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Kalkulator pace, VDOT, dan simulator split lari berdasarkan elevasi.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Direktori coach lari berlisensi untuk program latihan terstruktur.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Temukan rekan lari terdekat melalui fitur interaktif Run Connect.</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-6 mt-6 border-t border-slate-800">
                        <a href="{{ route('events.index') }}" class="text-xs font-bold text-orange-400 hover:text-orange-300">
                            Lihat Kalender Lari →
                        </a>
                    </div>
                </div>

                <!-- Pilar 2 -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 sm:p-8 hover:border-slate-700 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-mono font-bold text-orange-500 mb-2">02 / RACE TICKETING</div>
                        <h3 class="font-heading text-lg font-bold text-white mb-4">Ticketing Event Lari</h3>
                        <ul class="space-y-3 text-xs sm:text-sm text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Kategori tiket, alokasi kuota, dan status ketersediaan real-time.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Dukungan kode promo/kupon, add-ons jersey, dan registrasi grup.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Integrasi pembayaran otomatis via Virtual Account & QRIS resmi.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Penerbitan e-ticket instan dengan QR Code verifikasi hari perlombaan.</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-6 mt-6 border-t border-slate-800">
                        <a href="{{ route('eo.landing') }}" class="text-xs font-bold text-orange-400 hover:text-orange-300">
                            Pelajari Sistem Tiket EO →
                        </a>
                    </div>
                </div>

                <!-- Pilar 3 -->
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-6 sm:p-8 hover:border-slate-700 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-mono font-bold text-orange-500 mb-2">03 / RACE OPERATIONS</div>
                        <h3 class="font-heading text-lg font-bold text-white mb-4">Manajemen & Data Race</h3>
                        <ul class="space-y-3 text-xs sm:text-sm text-slate-300">
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Manajemen data peserta lengkap, kategori usia, dan nomor BIB.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Dashboard finansial dan pelaporan transaksi yang transparan.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Fungsi ekspor data cepat untuk kebutuhan timing system & race pack.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-emerald-400 font-bold">✓</span>
                                <span>Integrasi pengumuman hasil lomba dan sertifikat digital pelari.</span>
                            </li>
                        </ul>
                    </div>
                    <div class="pt-6 mt-6 border-t border-slate-800">
                        <a href="{{ route('eo.landing') }}" class="text-xs font-bold text-orange-400 hover:text-orange-300">
                            Konsultasi Operasional Race →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================================================
         SECTION 5: CTA / PARTNERSHIP (BG-SLATE-900)
         Final grounded action block
         ================================================ -->
    <section class="py-16 sm:py-20 bg-slate-900 border-t border-slate-800 text-center">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-heading text-2xl sm:text-3xl lg:text-4xl text-white mb-4">
                Siap Mengembangkan Event Lari Anda Bersama Kami?
            </h2>
            <p class="text-sm sm:text-base text-slate-300 mb-8 max-w-2xl mx-auto leading-relaxed">
                Tingkatkan penjualan tiket, kelola ribuan peserta secara teratur, dan hadirkan pengalaman lomba yang profesional bersama Ruang Lari.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4">
                <a href="{{ route('eo.landing') }}" 
                   class="inline-flex items-center justify-center px-6 py-3.5 rounded-md bg-orange-600 hover:bg-orange-500 text-white font-bold text-sm tracking-wide transition-colors">
                    Daftarkan Event Anda
                </a>
                <a href="{{ route('events.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3.5 rounded-md bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-semibold text-sm tracking-wide transition-colors">
                    Jelajahi Kalender Lari
                </a>
            </div>
        </div>
    </section>

</div>
@endsection
