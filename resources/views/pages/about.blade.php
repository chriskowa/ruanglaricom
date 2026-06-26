@extends('layouts.pacerhub')

@section('title', 'About Ruang Lari | Ticketing Event Lari Advanced & Ekosistem Pelari')
@section('meta_title', 'Ruang Lari: Ticketing Event Lari Advanced & Ekosistem Pelari')
@section('meta_description', 'Ruang Lari adalah platform event lari & ekosistem pelari: ticketing dinamis, kategori & kuota real-time, promo/kupon, add-ons, pembayaran terintegrasi, dashboard EO, dan manajemen peserta.')
@section('canonical_url', url('/about'))

@section('content')
<div id="about-app" class="overflow-x-hidden bg-dark text-slate-300">
    <header class="relative min-h-[60vh] flex items-center justify-center pt-24 pb-12">
        <div class="absolute inset-0 z-0 overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-dark to-black opacity-80"></div>
            <div class="absolute top-[-10%] left-[-10%] w-[500px] h-[500px] bg-neon/10 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[150px] animate-pulse-slow" style="animation-delay: 2s"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full text-center" data-aos="fade-up">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neon/20 bg-neon/5 backdrop-blur-md text-neon text-xs font-bold uppercase tracking-wider mb-6">
                PLATFORM EVENT LARI & RUNNER ECOSYSTEM
            </div>
            <h1 class="text-5xl md:text-7xl font-black leading-tight mb-6 text-white tracking-tighter">
                TICKETING EVENT <br class="hidden md:block">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-neon via-green-400 to-emerald-500">YANG DINAMIS</span>
            </h1>
            <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed font-light">
                Ruang Lari membantu Event Organizer menjual tiket dengan lebih cepat dan rapi—sekaligus membangun ekosistem pelari lewat kalender event, konten, pacer, coach, dan tools performa.
            </p>
        </div>
    </header>

    <section class="py-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid md:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right">
                    <div class="relative rounded-[2.5rem] overflow-hidden border-4 border-slate-800/50 shadow-2xl">
                        <img src="https://ruanglari.com/storage/blog/media/3l1BGrryunIsbK1nQitzCXVUXpmwxsQ0vd5ziuA1.webp" alt="Running Community" class="w-full h-auto object-cover hover:scale-105 transition duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-dark/80 to-transparent"></div>
                        <div class="absolute bottom-6 left-6 right-6">
                            <h3 class="text-2xl font-black text-white italic">BERSAMA KITA BISA</h3>
                        </div>
                    </div>
                </div>
                <div data-aos="fade-left">
                    <h2 class="text-3xl md:text-4xl font-black text-white mb-6">SIAPA <span class="text-neon">KAMI?</span></h2>
                    <p class="text-slate-400 mb-6 leading-relaxed">
                        Ruang Lari dibangun oleh pelari yang paham dua sisi: pengalaman peserta dan operasional Event Organizer. Fokus kami bukan hanya “daftar event”, tapi sistem yang membuat event lebih mudah dikelola dan lebih mudah ditemukan.
                    </p>
                    <p class="text-slate-400 mb-8 leading-relaxed">
                        Dengan ticketing yang fleksibel, pembayaran terintegrasi, serta manajemen peserta yang rapi, Ruang Lari membantu EO meningkatkan konversi pendaftaran, menekan beban CS, dan menjaga data peserta tetap akurat.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
                            <div class="text-3xl text-neon mb-2"><i class="fas fa-users"></i></div>
                            <div class="text-2xl font-black text-white">7700+</div>
                            <div class="text-sm text-slate-500 uppercase font-bold tracking-wider">Pelari Bergabung</div>
                        </div>
                        <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl">
                            <div class="text-3xl text-blue-500 mb-2"><i class="fas fa-calendar-check"></i></div>
                            <div class="text-2xl font-black text-white">RATUSAN</div>
                            <div class="text-sm text-slate-500 uppercase font-bold tracking-wider">Event Terdaftar</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-slate-900/50 relative overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-neon/5 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h2 class="text-3xl md:text-5xl font-black text-white mb-16">VISI & <span class="text-neon">MISI</span></h2>
            
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-slate-900 border border-slate-800 p-10 rounded-3xl hover:border-neon/50 transition duration-300 group" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-16 h-16 rounded-2xl bg-neon/10 flex items-center justify-center text-neon mx-auto mb-6 group-hover:scale-110 transition duration-300 border border-neon/20">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Visi</h3>
                    <p class="text-slate-400 leading-relaxed">
                        Menjadi platform event lari dan ekosistem pelari paling tepercaya—dengan ticketing yang fleksibel, data yang rapi, dan pengalaman peserta yang premium.
                    </p>
                </div>
                
                <div class="bg-slate-900 border border-slate-800 p-10 rounded-3xl hover:border-blue-500/50 transition duration-300 group" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-16 h-16 rounded-2xl bg-blue-500/10 flex items-center justify-center text-blue-500 mx-auto mb-6 group-hover:scale-110 transition duration-300 border border-blue-500/20">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Misi</h3>
                    <ul class="text-slate-400 text-left space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Menyediakan ticketing event lari yang dinamis: kategori, kuota real-time, promo/kupon, add-ons, dan checkout multi-peserta.
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Menghubungkan pelari dengan pacer, coach, dan tools untuk mencapai target event.
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Memberikan dashboard EO dan pelaporan yang membantu keputusan operasional dan pemasaran.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-black text-white mb-4">LAYANAN \u0026 <span class="text-neon">KEUNGGULAN</span></h2>
                <p class="text-slate-400 max-w-2xl mx-auto">Dibangun untuk konversi pendaftaran yang tinggi, data yang rapi, dan operasional event yang efisien.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="100">
                    <h3 class="text-xl font-bold text-white mb-4 border-b border-slate-800 pb-4">Untuk Pelari</h3>
                    <ul class="space-y-4 text-slate-400">
                        <li class="flex items-start gap-3">
                            <div class="text-neon mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Kalender event terupdate</div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="text-neon mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Program latihan & kalkulator pace</div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="text-neon mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Booking pacer & coach dengan mudah</div>
                        </li>
                    </ul>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 hover:-translate-y-2 transition duration-300 relative" data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute inset-0 bg-gradient-to-b from-blue-500/5 to-transparent rounded-3xl pointer-events-none"></div>
                    <h3 class="text-xl font-bold text-white mb-4 border-b border-slate-800 pb-4 relative z-10">Ticketing Event Lari</h3>
                    <ul class="space-y-4 text-slate-400 relative z-10">
                        <li class="flex items-start gap-3">
                            <div class="text-blue-500 mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Kategori tiket, kuota, dan status sold-out real-time</div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="text-blue-500 mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Promo/kupon, add-ons, dan checkout multi-peserta</div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="text-blue-500 mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Pembayaran terintegrasi + notifikasi + monitoring transaksi</div>
                        </li>
                    </ul>
                    <div class="mt-6 relative z-10">
                        <a href="{{ route('eo.landing') }}" class="inline-flex items-center justify-center w-full px-6 py-3 rounded-xl bg-blue-600 text-white font-black hover:bg-blue-500 transition">
                            Lihat Solusi Untuk EO
                        </a>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 hover:-translate-y-2 transition duration-300" data-aos="fade-up" data-aos-delay="300">
                    <h3 class="text-xl font-bold text-white mb-4 border-b border-slate-800 pb-4">Race Ops \u0026 Data</h3>
                    <ul class="space-y-4 text-slate-400">
                        <li class="flex items-start gap-3">
                            <div class="text-purple-500 mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Manajemen peserta, BIB, dan proses operasional event</div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="text-purple-500 mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Dashboard, laporan, dan export data</div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="text-purple-500 mt-1"><i class="fas fa-check-circle"></i></div>
                            <div>Tools pendukung: QR, sertifikat, dan hasil</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 relative border-t border-slate-800">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-900/50 to-dark"></div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center" data-aos="zoom-in">
            <h2 class="text-4xl font-black text-white mb-6">SIAP MEMBUAT EVENT <span class="text-neon">LEBIH CEPAT TERJUAL?</span></h2>
            <p class="text-slate-400 mb-10 text-lg">Aktifkan ticketing dinamis, rapikan data peserta, dan tingkatkan konversi pendaftaran dengan sistem yang dibuat khusus untuk event lari.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('eo.landing') }}" class="px-8 py-4 bg-neon text-dark font-black rounded-xl hover:bg-white hover:scale-105 transition transform shadow-[0_0_30px_rgba(204,255,0,0.3)] text-center uppercase tracking-wider">
                    Saya Event Organizer
                </a>
                <a href="{{ route('events.index') }}" class="px-8 py-4 border border-slate-700 text-white font-bold rounded-xl hover:bg-slate-800 transition flex items-center justify-center gap-2">
                    Jelajahi Event
                </a>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endpush

@push('scripts')
<script>
    AOS.init({duration:800, once:true, offset:50});
</script>
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
        ],
        [
            '@type' => 'WebSite',
            'name' => 'Ruang Lari',
            'url' => url('/'),
        ],
        [
            '@type' => 'WebPage',
            'name' => 'About Ruang Lari',
            'url' => url('/about'),
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
