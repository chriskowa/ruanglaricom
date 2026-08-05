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
<style>
    @keyframes heroZoom {
        0% { transform: scale(1); }
        50% { transform: scale(1.08); }
        100% { transform: scale(1); }
    }
    .animate-hero-zoom {
        animation: heroZoom 22s ease-in-out infinite alternate;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-[#08111F] text-slate-300 antialiased selection:bg-[#B8FF00]/20 selection:text-[#B8FF00]">
    
    <!-- Hero Section with Background Zoom Effect & High Contrast H1 -->
    <section class="relative pt-32 pb-24 md:pt-40 md:pb-32 overflow-hidden flex items-center min-h-[90vh]">
        <!-- Hero Background Image Container with Smooth Zoom In/Out Animation & Balanced Overlay -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <img src="{{ asset('images/hero/management event lari gratis.webp') }}" 
                 alt="Management Event Lari Gratis RuangLari" 
                 class="w-full h-full object-cover object-center animate-hero-zoom"
                 style="filter: brightness(0.55) contrast(1.1);"
                 fetchpriority="high"
                 decoding="async">
            
            <!-- Soft Dark Solid Overlay Layer -->
            <div class="absolute inset-0" style="background-color: rgba(8, 17, 31, 0.60); backdrop-filter: blur(1px);"></div>
            
            <!-- Balanced Dark Gradient Vignette Overlay -->
            <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(8,17,31,0.85) 0%, rgba(8,17,31,0.35) 50%, rgba(8,17,31,0.95) 100%);"></div>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 w-full">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#B8FF00]/15 backdrop-blur-md text-[#B8FF00] text-xs font-black uppercase tracking-wider mb-6">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#B8FF00] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#B8FF00]"></span>
                    </span>
                    100% Gratis Tanpa Biaya Paket / Setup Awal
                </div>
                
                <!-- High Contrast Optimized H1 Title -->
                <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-white tracking-tight leading-tight mb-5 drop-shadow-[0_4px_12px_rgba(0,0,0,0.9)]">
                    Bingung Buat Page Registrasi? <br class="hidden sm:block">
                    <span class="text-[#B8FF00] drop-shadow-[0_2px_15px_rgba(184,255,0,0.4)]">Buat Registration Page Event Lari</span> Instan &amp; Otomatis
                </h1>
                
                <p class="text-slate-200 text-base sm:text-lg max-w-3xl mx-auto leading-relaxed font-normal mb-8 drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)]">
                    Tak perlu pusing coding atau sewa developer mahal. RuangLari menyediakan platform registrasi event lari siap pakai dengan <strong class="text-white font-bold">form pendaftaran instan, payment gateway otomatis, E-Ticket QR, notifikasi WA, &amp; dashboard peserta</strong> tanpa biaya berlangganan.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register', ['role' => 'eo']) }}" class="w-full sm:w-auto px-8 sm:px-10 py-4 rounded-xl bg-[#B8FF00] hover:bg-[#9FE000] text-[#08111F] font-black text-sm uppercase tracking-wider transition-all duration-300 transform hover:scale-105 shadow-xl shadow-[#B8FF00]/20 flex items-center justify-center gap-2.5">
                        <i class="fa-solid fa-rocket text-base"></i>
                        <span>Buat Registration Page</span>
                    </a>
                    <a href="#fitur-utama" class="w-full sm:w-auto px-8 sm:px-10 py-4 rounded-xl bg-[#0E1A2D] hover:bg-[#111F35] text-white font-bold text-sm uppercase tracking-wider transition-all backdrop-blur-md flex items-center justify-center gap-2">
                        <span>Pelajari Fitur Platform</span>
                        <i class="fa-solid fa-arrow-down text-xs text-[#B8FF00]"></i>
                    </a>
                </div>

                <!-- Trust Metrics Badges -->
                <div class="mt-14 grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto border-t border-slate-800/80 pt-8">
                    <div class="text-center p-2">
                        <div class="text-2xl font-black text-[#B8FF00]">Rp 0</div>
                        <div class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold mt-0.5">Biaya Setup Awal</div>
                    </div>
                    <div class="text-center p-2">
                        <div class="text-2xl font-black text-white">&lt; 5 Menit</div>
                        <div class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold mt-0.5">Page Siap Tayang</div>
                    </div>
                    <div class="text-center p-2">
                        <div class="text-2xl font-black text-[#B8FF00]">Otomatis</div>
                        <div class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold mt-0.5">Konfirmasi WA &amp; QR</div>
                    </div>
                    <div class="text-center p-2">
                        <div class="text-2xl font-black text-white">Excel / CSV</div>
                        <div class="text-[11px] text-slate-400 uppercase tracking-wider font-semibold mt-0.5">Export Laporan Real-Time</div>
                    </div>
                </div>
            </div>

            <!-- Realistic Product Dashboard Mockup -->
            <div class="mt-16 relative rounded-3xl border border-[#1F2D44] bg-[#0E1A2D]/90 p-4 md:p-6 shadow-2xl max-w-5xl mx-auto overflow-hidden backdrop-blur-xl">
                <div class="absolute inset-0 bg-gradient-to-tr from-[#B8FF00]/5 via-transparent to-transparent pointer-events-none"></div>
                <!-- Window Header Bar -->
                <div class="flex items-center justify-between border-b border-[#1F2D44] pb-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    </div>
                    <div class="text-xs font-mono text-slate-400 bg-[#08111F] px-4 py-1 rounded-full border border-[#1F2D44]">ruanglari.com/eo/events/create</div>
                    <div class="w-12"></div>
                </div>

                <!-- Dashboard Preview Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="hidden md:block col-span-1 border-r border-[#1F2D44] pr-4 space-y-3">
                        <div class="h-8 bg-[#B8FF00]/10 border border-[#B8FF00]/30 rounded-xl w-full flex items-center px-3 text-[11px] font-bold text-[#B8FF00]">⚡ Event Registration Page</div>
                        <div class="h-7 bg-[#08111F] rounded-lg w-full flex items-center px-3 text-[10px] text-slate-400">📊 Laporan &amp; Data Peserta</div>
                        <div class="h-7 bg-[#08111F] rounded-lg w-full flex items-center px-3 text-[10px] text-slate-400">🎟️ Kupon Diskon &amp; Promo</div>
                        <div class="h-7 bg-[#08111F] rounded-lg w-full flex items-center px-3 text-[10px] text-slate-400">📱 Pengirim WA Blaster</div>
                    </div>
                    
                    <div class="col-span-1 md:col-span-3 space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3.5 bg-[#08111F] rounded-2xl border border-[#1F2D44]">
                                <span class="text-[10px] font-mono text-slate-400 uppercase block">Total Pendaftar</span>
                                <div class="text-lg font-black text-white mt-1">1,480 <span class="text-xs text-[#B8FF00]">Peserta</span></div>
                            </div>
                            <div class="p-3.5 bg-[#08111F] rounded-2xl border border-[#1F2D44]">
                                <span class="text-[10px] font-mono text-slate-400 uppercase block">Status Slot</span>
                                <div class="text-lg font-black text-[#B8FF00] mt-1">92% <span class="text-[10px] text-slate-400 font-normal">Terisi</span></div>
                            </div>
                            <div class="p-3.5 bg-[#08111F] rounded-2xl border border-[#1F2D44]">
                                <span class="text-[10px] font-mono text-slate-400 uppercase block">Pembayaran</span>
                                <div class="text-lg font-black text-emerald-400 mt-1">Otomatis Lunas</div>
                            </div>
                        </div>

                        <div class="bg-[#08111F] rounded-2xl border border-[#1F2D44] p-4 space-y-2.5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-300 pb-2 border-b border-[#1F2D44]">
                                <span>Live Registration Feed</span>
                                <span class="text-[10px] text-[#B8FF00] font-mono">● Instant Updates</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">AW</div>
                                        <span class="text-white font-medium">Andi Wijaya</span>
                                    </div>
                                    <span class="text-slate-400 text-[11px]">10K Runner</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">LUNAS</span>
                                </div>
                                <div class="flex items-center justify-between text-xs py-1.5 border-b border-slate-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-[10px]">SR</div>
                                        <span class="text-white font-medium">Siti Rahma</span>
                                    </div>
                                    <span class="text-slate-400 text-[11px]">5K Runner</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold">LUNAS</span>
                                </div>
                                <div class="flex items-center justify-between text-xs py-1.5">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center font-bold text-[10px]">BS</div>
                                        <span class="text-white font-medium">Budi Santoso</span>
                                    </div>
                                    <span class="text-slate-400 text-[11px]">Half Marathon (21K)</span>
                                    <span class="px-2 py-0.5 rounded-full bg-yellow-500/20 text-yellow-400 text-[10px] font-bold">WAITING VA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Problem & Solution Section for EOs -->
    <section class="py-24 border-t border-[#1F2D44] bg-[#0E1A2D]/40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-black text-[#B8FF00] uppercase tracking-widest block mb-2">Solusi EO Event Lari</span>
                <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">Stuck / Bingung Cara Buat Page Registrasi Event Lari?</h2>
                <p class="mt-4 text-slate-400 text-base leading-relaxed">
                    Menyelenggarakan lomba lari itu penuh tantangan. Jangan biarkan urusan website pendaftaran yang rumit menghambat event Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Challenge 1 -->
                <div class="p-8 rounded-3xl bg-[#08111F] border border-red-500/30 relative">
                    <div class="w-12 h-12 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-400 flex items-center justify-center mb-6 text-xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="text-white font-extrabold text-lg mb-3">Cara Lama (Penuh Masalah)</h3>
                    <ul class="space-y-2.5 text-xs text-slate-400 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-red-400 font-bold">✕</span>
                            <span>Input manual via Google Form yang rawan rekap ganda &amp; kacau.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-400 font-bold">✕</span>
                            <span>Konfirmasi bukti transfer manual satu per satu hingga larut malam.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-red-400 font-bold">✕</span>
                            <span>Biaya mahal buat website custom di agency ($500 - $2000).</span>
                        </li>
                    </ul>
                </div>

                <!-- Solution Arrow -->
                <div class="p-8 rounded-3xl bg-[#0E1A2D] border border-[#B8FF00]/40 relative shadow-xl shadow-[#B8FF00]/5 flex flex-col justify-between md:col-span-2">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-[#B8FF00]/10 border border-[#B8FF00]/30 text-[#B8FF00] flex items-center justify-center mb-6 text-xl">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <h3 class="text-white font-extrabold text-2xl mb-3">Solusi RuangLari EO (Serba Otomatis)</h3>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">
                            Dalam 5 menit, Anda sudah memiliki <strong class="text-white">Registration Page Event Lari Profesional</strong> lengkap dengan form pendaftaran multi-kategori, penomoran BIB otomatis, Notifikasi WA, dan sistem checkout pembayaran instan.
                        </p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div class="flex items-center gap-2 text-slate-200">
                                <i class="fa-solid fa-circle-check text-[#B8FF00]"></i>
                                <span>Form Pendaftaran Multi-Kategori (5K, 10K, HM, FM)</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-200">
                                <i class="fa-solid fa-circle-check text-[#B8FF00]"></i>
                                <span>Pembayaran Otomatis (VA, QRIS, E-Wallet)</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-200">
                                <i class="fa-solid fa-circle-check text-[#B8FF00]"></i>
                                <span>Kupon Promo &amp; Diskon Khusus Komunitas</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-200">
                                <i class="fa-solid fa-circle-check text-[#B8FF00]"></i>
                                <span>Export Data Peserta ke Excel 1-Klik</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-[#1F2D44] flex items-center justify-between">
                        <span class="text-xs text-[#B8FF00] font-bold uppercase tracking-wider">Tanpa Biaya Langganan Bulanan</span>
                        <a href="{{ route('register', ['role' => 'eo']) }}" class="px-6 py-3 rounded-xl bg-[#B8FF00] hover:bg-[#9FE000] text-[#08111F] font-black text-xs uppercase tracking-wider transition-all">
                            Buat Event Sekarang →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Features Section -->
    <section id="fitur-utama" class="py-24 border-t border-[#1F2D44] bg-[#08111F]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-black text-[#B8FF00] uppercase tracking-widest block mb-2">Fitur Unggulan EO</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Fitur Platform Registrasi Event Lari Modern</h2>
                <p class="mt-4 text-slate-400 leading-relaxed">Seluruh alat yang dibutuhkan Event Organizer untuk sukses menyelenggarakan event lari skala kecil hingga marathon internasional.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Feature 1 -->
                <div class="rounded-3xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-7 hover:border-[#B8FF00]/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-[#08111F] border border-[#1F2D44] text-[#B8FF00] flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i class="fa-solid fa-desktop text-lg"></i>
                    </div>
                    <h3 class="text-white font-extrabold text-lg mb-2">Registration Page Instan</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Tampilan landing page event yang futuristik, responsif di HP/Desktop, dan siap dipublikasikan ke media sosial.</p>
                </div>

                <!-- Feature 2 -->
                <div class="rounded-3xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-7 hover:border-[#B8FF00]/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-[#08111F] border border-[#1F2D44] text-emerald-400 flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i class="fa-solid fa-qrcode text-lg"></i>
                    </div>
                    <h3 class="text-white font-extrabold text-lg mb-2">E-Ticket QR Code &amp; BIB</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Peserta otomatis mendapatkan E-Ticket QR Code resmi dan nomor BIB unik setelah pembayaran lunas.</p>
                </div>

                <!-- Feature 3 -->
                <div class="rounded-3xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-7 hover:border-[#B8FF00]/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-[#08111F] border border-[#1F2D44] text-cyan-400 flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                    </div>
                    <h3 class="text-white font-extrabold text-lg mb-2">Notifikasi WA Blaster</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Pesan konfirmasi registrasi, nomor BIB, dan pengingat jadwal lari otomatis dikirimkan ke WhatsApp peserta.</p>
                </div>

                <!-- Feature 4 -->
                <div class="rounded-3xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-7 hover:border-[#B8FF00]/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-[#08111F] border border-[#1F2D44] text-amber-400 flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i class="fa-solid fa-tags text-lg"></i>
                    </div>
                    <h3 class="text-white font-extrabold text-lg mb-2">Multi-Kupon &amp; Promo</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Buat kode kupon diskon komunitas (nominal/persen) dengan fitur pencarian AJAX &amp; pembatasan kuota.</p>
                </div>

                <!-- Feature 5 -->
                <div class="rounded-3xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-7 hover:border-[#B8FF00]/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-[#08111F] border border-[#1F2D44] text-purple-400 flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i class="fa-solid fa-file-excel text-lg"></i>
                    </div>
                    <h3 class="text-white font-extrabold text-lg mb-2">Export Data Excel / CSV</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Unduh data lengkap peserta (ukuran jersey, kontak darurat, golongan darah) sekali klik untuk persiapan Race Pack Collection.</p>
                </div>

                <!-- Feature 6 -->
                <div class="rounded-3xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-7 hover:border-[#B8FF00]/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-2xl bg-[#08111F] border border-[#1F2D44] text-rose-400 flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i class="fa-solid fa-shield-halved text-lg"></i>
                    </div>
                    <h3 class="text-white font-extrabold text-lg mb-2">Keamanan &amp; Anti Oversell</h3>
                    <p class="text-slate-400 text-xs leading-relaxed">Kontrol kuota real-time yang mencegah tiket terjual melebihi batas (oversell) secara akurat.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Transparancy & No Subscription Section -->
    <section class="py-20 border-t border-[#1F2D44] bg-[#0E1A2D]/40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
            <span class="px-3.5 py-1.5 rounded-full border border-[#B8FF00]/30 bg-[#B8FF00]/10 text-[#B8FF00] text-xs font-bold uppercase tracking-wider inline-block mb-4">
                Transparan &amp; Tanpa Resiko
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">Model Kerjasama Platform Fee Transparan</h2>
            <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-8 max-w-2xl mx-auto">
                RuangLari beroperasi dengan skema <strong class="text-white">Platform Fee kecil per tiket yang berhasil diproses</strong>. Anda bisa langsung mendaftar akun EO, membuat event, dan mempublikasikan registration page Anda hari ini tanpa biaya paket apapun.
            </p>
            
            <a href="{{ route('register', ['role' => 'eo']) }}" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-[#B8FF00] hover:bg-[#9FE000] text-[#08111F] font-black text-sm uppercase tracking-wider transition-all transform hover:scale-105 shadow-xl shadow-[#B8FF00]/20 gap-2">
                <i class="fa-solid fa-user-plus text-base"></i>
                <span>Daftar Akun EO &amp; Mulai Buat Event</span>
            </a>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-24 border-t border-[#1F2D44] bg-[#08111F]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-16">
                <span class="text-xs font-black text-[#B8FF00] uppercase tracking-widest block mb-2">Tanya Jawab EO</span>
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Pertanyaan Umum Event Organizer</h2>
                <p class="mt-3 text-slate-400 text-sm">Jawaban atas pertanyaan seputar pembuatan registration page dan sistem registrasi lari di RuangLari.</p>
            </div>

            <div class="space-y-4">
                <details class="group rounded-2xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-bold text-sm">Bagaimana cara membuat registration page event lari di RuangLari?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-300 text-xs leading-relaxed border-t border-[#1F2D44] pt-3">
                        Cukup daftar akun Event Organizer (EO) gratis di RuangLari, lalu masuk ke menu "Buat Event Baru". Isikan informasi nama event, kategori lari (5K, 10K, HM, FM), kuota, dan harga tiket. Dalam 5 menit, registration page event lari Anda siap dipublikasikan.
                    </div>
                </details>

                <details class="group rounded-2xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-bold text-sm">Apakah ada biaya berlangganan atau beli paket di awal?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-300 text-xs leading-relaxed border-t border-[#1F2D44] pt-3">
                        Tidak ada. Anda tidak perlu membayar paket atau langganan bulanan di awal. RuangLari beroperasi secara transparan dengan skema Platform Fee kecil yang terpotong otomatis hanya saat tiket pendaftaran berhasil diproses.
                    </div>
                </details>

                <details class="group rounded-2xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-bold text-sm">Metode pembayaran apa saja yang bisa digunakan peserta?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-300 text-xs leading-relaxed border-t border-[#1F2D44] pt-3">
                        Sistem RuangLari sudah terintegrasi dengan Payment Gateway nasional yang mendukung QRIS, Bank Virtual Account (BCA, Mandiri, BNI, BRI, Permata), serta E-Wallet populer. Pendaftaran langsung terverifikasi lunas secara otomatis tanpa perlu konfirmasi manual.
                    </div>
                </details>

                <details class="group rounded-2xl border border-[#1F2D44] bg-[#0E1A2D]/60 p-5">
                    <summary class="cursor-pointer list-none flex items-center justify-between gap-4 select-none">
                        <span class="text-white font-bold text-sm">Apakah bisa mengunduh data peserta untuk pencetakan BIB &amp; Jersey?</span>
                        <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="mt-3 text-slate-300 text-xs leading-relaxed border-t border-[#1F2D44] pt-3">
                        Ya. Anda memiliki akses penuh ke Dashboard EO untuk mengunduh laporan peserta real-time dalam format Excel / CSV kapan saja. Data mencakup nomor BIB, ukuran jersey, kontak WhatsApp, data medis, dan info penting lainnya.
                    </div>
                </details>
            </div>
            
            <div class="mt-16 text-center">
                <a href="{{ route('register', ['role' => 'eo']) }}" class="inline-flex items-center justify-center px-8 py-4 rounded-xl bg-[#B8FF00] hover:bg-[#9FE000] text-[#08111F] font-black text-sm uppercase tracking-wider transition duration-200">
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
