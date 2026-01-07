@extends('layouts.pacerhub')
@php
    $withSidebar = true;
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
    </div>
</div>
@endsection
