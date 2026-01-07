@extends('layouts.pacerhub')
@php($withSidebar = true)

@section('title', 'Event Organizer — ruanglari')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-slate-900">
    <div class="max-w-6xl mx-auto px-4 pt-24 pb-20">
        <div class="text-center mb-12">
            <span class="px-3 py-1 rounded-full bg-neon/10 text-neon font-bold text-xs border border-neon/20">Event Organizer</span>
            <h1 class="mt-4 text-4xl md:text-5xl font-black text-white tracking-tight italic">Bangun Event Lari dengan Sistem Modern</h1>
            <p class="mt-3 text-slate-400">Solusi lengkap untuk komunitas dan Race Director: pendaftaran online, pembayaran otomatis, WhatsApp blaster, hingga publikasi hasil lomba.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <!-- Paket LITE -->
            <div class="group relative rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm p-6 hover:border-neon/30 transition-all shadow-lg hover:shadow-neon/20">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center text-neon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-extrabold text-xl">Paket LITE</h3>
                            <p class="text-xs text-slate-400 uppercase">Self-Service</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-lg bg-slate-800 text-slate-300 text-xs font-bold">Budget Friendly</span>
                </div>

                <p class="text-slate-300 text-sm mb-4">Cocok untuk komunitas kecil, Fun Run lokal, atau Virtual Run dengan budget terbatas.</p>

                <ul class="space-y-2 text-sm">
                    <li class="flex items-start gap-3">
                        <i class="fas fa-globe text-neon mt-0.5"></i>
                        <div><span class="text-white font-bold">Landing Page</span>: Template standar dinamis (custom teks & gambar)</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-clipboard-check text-neon mt-0.5"></i>
                        <div><span class="text-white font-bold">Registrasi</span>: Online form dengan sistem "Quick Reg"</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-credit-card text-neon mt-0.5"></i>
                        <div><span class="text-white font-bold">Pembayaran</span>: Gateway otomatis (VA, QRIS, E-Wallet)</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-database text-neon mt-0.5"></i>
                        <div><span class="text-white font-bold">Manajemen Peserta</span>: Dashboard untuk ekspor database pelari</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-hashtag text-neon mt-0.5"></i>
                        <div><span class="text-white font-bold">Nomor BIB</span>: Penomoran otomatis sesuai urutan pendaftaran</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-envelope-open-text text-neon mt-0.5"></i>
                        <div><span class="text-white font-bold">Support</span>: Email support & panduan penggunaan sistem</div>
                    </li>
                </ul>

                <div class="mt-6">
                    <a href="{{ route('events.index') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-neon text-dark font-black hover:bg-neon/90 transition">
                        <i class="fas fa-rocket"></i> Mulai dari Paket LITE
                    </a>
                </div>

                <div class="absolute inset-0 rounded-2xl ring-1 ring-white/5 group-hover:ring-neon/30 transition"></div>
            </div>

            <!-- Paket PRO -->
            <div class="group relative rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm p-6 hover:border-neon/30 transition-all shadow-lg hover:shadow-neon/20">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center text-yellow-400">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-extrabold text-xl">Paket PRO</h3>
                            <p class="text-xs text-slate-400 uppercase">Most Popular</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-lg bg-yellow-500/20 text-yellow-300 text-xs font-bold">Recommended</span>
                </div>

                <p class="text-slate-300 text-sm mb-4">Cocok untuk event skala menengah (500–2.000 peserta) yang membutuhkan otomasi komunikasi.</p>

                <ul class="space-y-2 text-sm">
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check-circle text-yellow-400 mt-0.5"></i>
                        <div>Semua Fitur Paket LITE</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fab fa-whatsapp text-green-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">WhatsApp Blaster</span>: Notifikasi otomatis daftar berhasil, pengingat pembayaran, dan pengiriman nomor BIB</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-users text-yellow-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Channel Pacer & Komunitas</span>: Koordinasi pacer dan integrasi grup komunitas partner ruanglari.com</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-list-ol text-yellow-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Manajemen BIB</span>: Kategori (10K, 5K, Kids Dash) dengan prefix BIB berbeda</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-trophy text-yellow-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Race Results</span>: Publikasi hasil lari langsung di landing page</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-headset text-yellow-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Support</span>: Dedicated Account Manager (WhatsApp support)</div>
                    </li>
                </ul>

                <div class="mt-6">
                    <a href="{{ route('events.index') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-yellow-400 text-black font-black hover:bg-yellow-300 transition">
                        <i class="fas fa-bolt"></i> Upgrade ke PRO
                    </a>
                </div>

                <div class="absolute inset-0 rounded-2xl ring-1 ring-white/5 group-hover:ring-yellow-300/40 transition"></div>
            </div>

            <!-- Paket ELITE -->
            <div class="group relative rounded-2xl border border-slate-800 bg-slate-900/60 backdrop-blur-sm p-6 hover:border-neon/30 transition-all shadow-lg hover:shadow-neon/20">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center text-purple-400">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-extrabold text-xl">Paket ELITE</h3>
                            <p class="text-xs text-slate-400 uppercase">Full Event Management</p>
                        </div>
                    </div>
                    <span class="px-2 py-1 rounded-lg bg-purple-500/20 text-purple-300 text-xs font-bold">Premium</span>
                </div>

                <p class="text-slate-300 text-sm mb-4">Cocok untuk Race Director profesional dan event besar dengan kompleksitas tinggi.</p>

                <ul class="space-y-2 text-sm">
                    <li class="flex items-start gap-3">
                        <i class="fas fa-check-circle text-purple-400 mt-0.5"></i>
                        <div>Semua Fitur Paket PRO</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-paint-brush text-purple-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Custom Landing Page</span>: Desain premium sesuai branding sponsor</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-chart-line text-purple-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Channel Race Director</span>: Dashboard analitik real-time (grafik pendaftaran, arus kas, demografi)</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-wallet text-purple-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Wallet Integration</span>: Pengelolaan dana pendaftaran lebih fleksibel bagi panitia</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-qrcode text-purple-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Race Management Advance</span>: Check-in RPC dengan QR Code</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-database text-purple-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Prioritas Database</span>: Rekomendasi event ke database pelari aktif ruanglari.com</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <i class="fas fa-people-carry text-purple-400 mt-0.5"></i>
                        <div><span class="text-white font-bold">Support</span>: On-site technical support saat hari H (opsional)</div>
                    </li>
                </ul>

                <div class="mt-6">
                    <a href="{{ route('events.index') }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-purple-500 text-white font-black hover:bg-purple-400 transition">
                        <i class="fas fa-crown"></i> Konsultasi Paket ELITE
                    </a>
                </div>

                <div class="absolute inset-0 rounded-2xl ring-1 ring-white/5 group-hover:ring-purple-400/40 transition"></div>
            </div>
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
    </div>
</div>
@endsection