@extends('layouts.pacerhub', [
    'hideFooter' => false,
    'hideChat' => true,
    'hideNav' => false,
])

@section('title', $pageMetaTitle)
@section('meta_title', $pageMetaTitle)
@section('meta_description', $pageMetaDescription)

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter+Tight:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
@endpush

@push('structured_data')
<meta name="keywords" content="kalender lari, kalender pelari {{ $nextYear }}, buat kalender lari, cetak kalender marathon, desain kalender foto lari, running calendar studio, jadwal lari indonesia">
<meta property="og:title" content="{{ $pageMetaTitle }}">
<meta property="og:description" content="{{ $pageMetaDescription }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta name="twitter:card" content="summary_large_image">

@php
$schemaData = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'WebApplication',
            'name' => 'Ruang Lari Calendar Studio',
            'url' => route('kalender-pelari.landing'),
            'applicationCategory' => 'DesignApplication',
            'operatingSystem' => 'Web Browser',
            'description' => $pageMetaDescription,
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'IDR'
            ]
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Apakah hasil kalender bisa dicetak sampai 13 halaman?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Ya, Studio Calendar Ruang Lari menghasilkan 13 halaman lengkap (1 Sampul Utama + 12 Halaman Bulan Januari sampai Desember) dalam format PDF siap cetak atau PNG satuan.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Berapa resolusi cetak yang dihasilkan?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Engine Canvas mengekspor dengan resolusi cetak standar percetakan profesional 300 DPI (hingga 3780 x 5669 piksel untuk kalender dinding dan 2382 x 1590 piksel untuk kalender meja/A4).'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Apakah jadwal hari libur nasional Indonesia sudah tercantum otomatis?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Benar. Sistem kalender memuat database hari libur nasional Indonesia resmi untuk tahun 2025, 2026, 2027 dan seterusnya secara otomatis dengan penandaan warna khusus.'
                    ]
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Apakah bisa mendesain tanpa mendaftar akun terlebih dahulu?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Bisa. Studio dapat langsung dicoba secara gratis dalam mode trial tanpa login. Anda dapat login kapan saja untuk menyimpan proyek secara permanen ke akun pelari Anda.'
                    ]
                ]
            ]
        ]
    ]
];
@endphp
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<main class="bg-slate-950 text-slate-100 pt-0">
    <!-- HERO SECTION -->
    <section class="border-b border-slate-800 bg-gradient-to-b from-slate-900 to-slate-950">
        <div class="mx-auto max-w-7xl px-5 py-12 sm:px-8 lg:py-20">
            <div class="grid gap-10 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-7 space-y-6">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-white font-['Inter_Tight','Sora',sans-serif]">
                        Buat Kalender Lari Custom {{ $nextYear }} & Cetak PDF.
                    </h1>
                    <p class="text-base sm:text-lg leading-relaxed text-slate-300 max-w-2xl">
                        Rancang kalender fisik 13 halaman (Cover + 12 Bulan) lengkap dengan foto maraton terbaik,
                        target kilometer, jadwal lomba lari nasional, dan QR code profil pelari resmi Anda.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                        <a href="{{ $studioUrl }}"
                           class="inline-flex items-center justify-center rounded-md bg-red-600 px-6 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition">
                            Mulai Desain Kalender
                        </a>
                        <a href="#pilihan-desain"
                           class="inline-flex items-center justify-center rounded-md border border-slate-700 bg-slate-900 px-6 py-3.5 text-sm font-semibold text-slate-200 hover:bg-slate-800 hover:text-white transition">
                            Lihat 36 Desain Template
                        </a>
                    </div>

                    <div class="grid grid-cols-3 gap-4 pt-4 border-t border-slate-800 text-xs text-slate-400">
                        <div>
                            <span class="block text-white font-bold text-sm">300 DPI</span>
                            Kualitas cetak tajam
                        </div>
                        <div>
                            <span class="block text-white font-bold text-sm">36 Template</span>
                            Desain atletik siap pakai
                        </div>
                        <div>
                            <span class="block text-white font-bold text-sm">Trial Bebas</span>
                            Coba tanpa registrasi
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="rounded-lg border border-slate-800 bg-slate-900 p-4 shadow-xl">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800 text-xs">
                            <span class="font-bold text-white">Pratinjau Format Cetak</span>
                            <span class="text-sky-400 font-mono">A4 Landscape / Wall A3</span>
                        </div>
                        <div class="mt-3 aspect-[4/3] rounded bg-slate-950 border border-slate-800 overflow-hidden relative flex flex-col justify-between p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-xs font-bold text-red-500 uppercase tracking-wider font-mono">Ruang Lari</span>
                                    <h3 class="text-lg font-extrabold text-white font-['Inter_Tight','Sora',sans-serif]">Januari {{ $nextYear }}</h3>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-800 text-slate-300 border border-slate-700">Cover + 12 Bulan</span>
                            </div>
                            <div class="py-4 text-center">
                                <span class="text-xs text-slate-400">Mode Kolase 1–5 Foto Pribadi & Stiker Atletik</span>
                            </div>
                            <div class="flex justify-between items-end text-[10px] text-slate-400 border-t border-slate-800 pt-2">
                                <span>Hari Libur Nasional Otomatis</span>
                                <span class="text-sky-400 font-mono">QR Code Profil Aktif</span>
                            </div>
                        </div>
                        <p class="text-center text-xs text-slate-400 mt-3">
                            Semua elemen dapat digeser, diperbesar, dan disesuaikan langsung di editor canvas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TEMPLATE SHOWCASE -->
    <section id="pilihan-desain" class="mx-auto max-w-7xl px-5 py-16 sm:px-8">
        <div class="max-w-2xl mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white font-['Inter_Tight','Sora',sans-serif]">
                36 Pilihan Desain Kalender Atletik
            </h2>
            <p class="mt-3 text-sm text-slate-300">
                Pilih gaya yang mencerminkan perjalanan lari Anda: dari gaya race kontras tinggi, minimalis Skandinavia, hingga nuansa rute Strava dan nomor dada maraton.
            </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($templateHighlights as $tpl)
            <div class="rounded-lg border border-slate-800 bg-slate-900 p-5 flex flex-col justify-between hover:border-slate-700 transition">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-mono text-sky-400 uppercase tracking-wider">{{ $tpl['id'] }}</span>
                        <span class="text-xs text-slate-500">Preset Siap Pakai</span>
                    </div>
                    <h3 class="text-lg font-bold text-white font-['Inter_Tight','Sora',sans-serif]">{{ $tpl['name'] }}</h3>
                    <p class="mt-2 text-xs leading-relaxed text-slate-300">{{ $tpl['desc'] }}</p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-800">
                    <a href="{{ $studioUrl }}?template={{ $tpl['id'] }}"
                       class="inline-flex w-full items-center justify-center rounded-md border border-slate-700 bg-slate-800 py-2 text-xs font-semibold text-slate-200 hover:bg-slate-700 hover:text-white transition">
                        Gunakan Desain Ini
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- FITUR UNGGULAN -->
    <section class="border-t border-slate-800 bg-slate-900/50">
        <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8">
            <div class="max-w-2xl mb-12">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white font-['Inter_Tight','Sora',sans-serif]">
                    Dirancang Khusus untuk Kebutuhan Pelari
                </h2>
                <p class="mt-3 text-sm text-slate-300">
                    Bukan template kalender biasa. Dibuat dengan presisi data dan instrumen lari untuk mengabadikan pencapaian Anda.
                </p>
            </div>

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-3">
                    <div class="w-10 h-10 rounded-md bg-slate-800 border border-slate-700 flex items-center justify-center text-red-500 font-bold text-sm">
                        HD
                    </div>
                    <h3 class="text-base font-bold text-white">Ekspor 300 DPI</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Hasil render HTML5 Canvas beresolusi cetak tinggi (hingga 3780 x 5669 px) sehingga cetakan tidak akan pecah atau buram.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="w-10 h-10 rounded-md bg-slate-800 border border-slate-700 flex items-center justify-center text-sky-400 font-bold text-sm">
                        QR
                    </div>
                    <h3 class="text-base font-bold text-white">QR Code Profil Resmi</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Tiap lembar kalender menyematkan kode QR yang jika di-scan langsung membuka profil dan rekap log lari Anda di Ruang Lari.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="w-10 h-10 rounded-md bg-slate-800 border border-slate-700 flex items-center justify-center text-emerald-400 font-bold text-sm">
                        RI
                    </div>
                    <h3 class="text-base font-bold text-white">Libur Nasional Otomatis</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Database hari libur nasional Indonesia otomatis menghitung tanggal merah dan cuti bersama resmi tanpa repot manual.
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="w-10 h-10 rounded-md bg-slate-800 border border-slate-700 flex items-center justify-center text-amber-400 font-bold text-sm">
                        RC
                    </div>
                    <h3 class="text-base font-bold text-white">Agenda Race Terintegrasi</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Tandai jadwal lomba lari maraton nasional se-Indonesia (Maybank, Borobudur, Pocari) langsung ke atas tanggal kalender.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section class="border-t border-slate-800 mx-auto max-w-7xl px-5 py-16 sm:px-8">
        <div class="max-w-2xl mb-12">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white font-['Inter_Tight','Sora',sans-serif]">
                Pertanyaan Seputar Kalender Pelari
            </h2>
            <p class="mt-3 text-sm text-slate-300">
                Informasi teknis dan panduan pencetakan kalender pribadi Anda.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div class="rounded-lg border border-slate-800 bg-slate-900 p-5 space-y-2">
                <h3 class="text-sm font-bold text-white">Apakah kalender bisa dicetak hingga 13 halaman?</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Ya, fitur Cetak PDF merender 13 halaman lengkap (1 Sampul Utama + 12 Halaman Bulan) dalam satu file dokumen teratur tanpa margin terpotong.
                </p>
            </div>

            <div class="rounded-lg border border-slate-800 bg-slate-900 p-5 space-y-2">
                <h3 class="text-sm font-bold text-white">Format ukuran apa saja yang tersedia?</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Tersedia format Kalender Meja (A4 Landscape 297 × 210 mm) dan Kalender Dinding (A3/Wall 320 × 480 mm). Format 1 bulan atau 2 bulan per halaman juga dapat diatur bebas.
                </p>
            </div>

            <div class="rounded-lg border border-slate-800 bg-slate-900 p-5 space-y-2">
                <h3 class="text-sm font-bold text-white">Apakah saya harus login untuk mendesain?</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Tidak wajib. Anda dapat langsung mendesain dalam mode trial. Proyek akan tersimpan sementara di browser, dan Anda dapat mengaitkannya ke akun kapan saja.
                </p>
            </div>

            <div class="rounded-lg border border-slate-800 bg-slate-900 p-5 space-y-2">
                <h3 class="text-sm font-bold text-white">Bagaimana cara menyimpan backup proyek?</h3>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Di dalam studio, Anda dapat mengunduh file proyek berekstensi .json. File ini menyimpan seluruh posisi foto, teks, dan stiker untuk dibuka kembali di kemudian hari.
                </p>
            </div>
        </div>

        <!-- BOTTOM CTA -->
        <div class="mt-16 rounded-lg border border-slate-800 bg-slate-900 p-8 text-center sm:p-12">
            <h3 class="text-2xl sm:text-3xl font-extrabold text-white font-['Inter_Tight','Sora',sans-serif]">
                Mulai Susun Kalender Lari Anda Sekarang
            </h3>
            <p class="mt-3 text-sm text-slate-300 max-w-xl mx-auto">
                Buka studio desain gratis, pilih foto terbaik Anda, dan cetak kalender maraton impian Anda dalam hitungan menit.
            </p>
            <div class="mt-6">
                <a href="{{ $studioUrl }}"
                   class="inline-flex items-center justify-center rounded-md bg-red-600 px-8 py-3.5 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition">
                    Buka Calendar Studio
                </a>
            </div>
        </div>
    </section>
</main>
@endsection
