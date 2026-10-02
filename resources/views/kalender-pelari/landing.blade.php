@extends('layouts.pacerhub', ['hideFooter' => true, 'hideChat' => true, 'hideNav' => true])

@section('title', $pageMetaTitle)
@section('meta_title', $pageMetaTitle)
@section('meta_description', $pageMetaDescription)

@push('styles')
@vite(['resources/css/kalender-pelari.css'])
@endpush

@php
    $_kpFirstDay = new DateTimeImmutable($nextYear . '-01-01');
    $_kpOffset = (int) $_kpFirstDay->format('N') - 1;
    $_kpDays = (int) $_kpFirstDay->format('t');
    $_kpCells = (int) ceil(($_kpOffset + $_kpDays) / 7) * 7;
@endphp

@section('content')
<main class="kp-public-page bg-slate-950 text-slate-100">
    <header class="border-b border-slate-800 bg-slate-900">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
            <a href="{{ url('/') }}" class="min-h-12 inline-flex items-center text-sm font-semibold text-white underline underline-offset-4">RuangLari</a>
            <a href="{{ $wizardUrl }}" class="kp-secondary-link">Buat kalender</a>
        </div>
    </header>
    <section class="border-b border-slate-800">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 md:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] md:items-center md:gap-8 lg:gap-16 lg:py-24">
            <div class="max-w-xl">
                <p class="mb-5 text-sm font-semibold text-neon">KalenderPelari / {{ $nextYear }}</p>
                <h1 class="kp-public-title text-4xl text-white xl:text-[3.5rem]">
                    Tahun lari Anda, siap disusun jadi kalender.
                </h1>
                <p class="mt-6 max-w-lg text-base leading-relaxed text-slate-200">
                    Pilih jenis kalender dan ukuran cetaknya. Halaman bulanan langsung terisi tanggal,
                    lalu Anda bisa mengatur teks, target, catatan, dan foto di editor.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ $wizardUrl }}" class="kp-primary-link">Buat kalender saya</a>
                    <a href="#lihat-alurnya" class="kp-secondary-link">Lihat cara kerjanya</a>
                </div>
                <p class="mt-5 text-sm text-slate-300">
                    Bisa dicoba tanpa login. Trial tersimpan sementara selama {{ (int) (config('kalender-pelari.trial_expire_minutes', 10080) / 1440) }} hari.
                </p>
            </div>

            <div class="min-w-0">
                <div class="mb-3 flex items-center justify-between gap-4 text-xs font-semibold text-slate-200">
                    <span>Contoh halaman bulanan</span>
                    <span>A4 landscape · 297 × 210 mm</span>
                </div>
                <div class="kp-public-sheet p-[6%]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="kp-preview-heading text-2xl text-slate-900 sm:text-3xl">Januari</p>
                            <p class="mt-1 text-xs text-slate-600">Kalender lari</p>
                        </div>
                        <span class="text-sm font-semibold tabular-nums text-slate-700">{{ $nextYear }}</span>
                    </div>
                    <div class="mt-[8%] grid grid-cols-[minmax(0,1fr)_minmax(90px,0.32fr)] gap-[5%]">
                        <table class="kp-public-calendar" aria-label="Contoh kalender Januari {{ $nextYear }}">
                            <thead>
                                <tr>
                                    @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                                    <th scope="col">{{ $day }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @for($cell = 0; $cell < $_kpCells; $cell += 7)
                                <tr>
                                    @for($column = 0; $column < 7; $column++)
                                    @php($_kpDay = $cell + $column - $_kpOffset + 1)
                                    <td @class(['kp-public-weekend' => $column > 4])>
                                        {{ $_kpDay > 0 && $_kpDay <= $_kpDays ? $_kpDay : '' }}
                                    </td>
                                    @endfor
                                </tr>
                                @endfor
                            </tbody>
                        </table>
                        <div class="space-y-3">
                            <div class="border border-slate-200 bg-slate-50 p-3">
                                <p class="text-xs font-semibold text-slate-800">Target bulan ini</p>
                                <p class="mt-3 text-xs text-slate-600">Tentukan target Anda</p>
                            </div>
                            <div class="border border-slate-200 bg-slate-50 p-3">
                                <p class="text-xs font-semibold text-slate-800">Catatan</p>
                                <p class="mt-3 text-xs text-slate-600">Isi setelah kalender dibuat</p>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-300">Preview isi kalender. Data lari belum ditambahkan.</p>
            </div>
        </div>
    </section>

    <section id="lihat-alurnya" class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 lg:grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)] lg:gap-20 lg:py-20">
        <div>
            <p class="text-sm font-semibold text-neon">Dari pilihan ke halaman jadi</p>
            <h2 class="kp-public-title mt-3 text-3xl text-white">Mulai dari kalender, bukan kanvas kosong.</h2>
            <p class="mt-4 text-sm leading-relaxed text-slate-200">
                Kecuali Anda memilih mulai dari nol, editor menyiapkan cover, dua belas bulan, dan halaman ringkasan.
                Tanggal mengikuti tahun dan awal minggu yang Anda pilih.
            </p>
        </div>
        <ol class="border-t border-slate-700">
            <li class="kp-public-step">
                <span class="text-sm font-semibold tabular-nums text-neon">01</span>
                <div>
                    <h3 class="kp-public-heading text-lg text-white">Pilih isi dan ukuran</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-200">Tentukan tipe kalender, tahun, format cetak, dan tampilan awal.</p>
                </div>
            </li>
            <li class="kp-public-step">
                <span class="text-sm font-semibold tabular-nums text-neon">02</span>
                <div>
                    <h3 class="kp-public-heading text-lg text-white">Sesuaikan tiap halaman</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-200">Ubah teks dan target, tambahkan foto atau catatan, lalu atur posisinya pada halaman.</p>
                </div>
            </li>
            <li class="kp-public-step">
                <span class="text-sm font-semibold tabular-nums text-neon">03</span>
                <div>
                    <h3 class="kp-public-heading text-lg text-white">Lanjutkan saat siap</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-200">Perubahan trial disimpan otomatis. Masuk ke akun untuk menyimpan proyek secara permanen.</p>
                </div>
            </li>
        </ol>
    </section>

    <section class="border-t border-slate-800 bg-slate-900">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 px-5 py-10 sm:px-8 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="kp-public-heading text-xl text-white">Pilih kalender yang ingin Anda buat.</h2>
                <p class="mt-2 text-sm text-slate-200">Anda akan langsung melihat hasil awalnya di editor.</p>
            </div>
            <a href="{{ $wizardUrl }}" class="kp-primary-link shrink-0">Pilih kalender dan format</a>
        </div>
    </section>
</main>
@endsection
