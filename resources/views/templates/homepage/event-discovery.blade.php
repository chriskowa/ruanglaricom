@extends('layouts.pacerhub', ['lightMode' => false])

@section('title', $page->meta_title ?? $page->title ?? 'RuangLari - Portal Registrasi Event Lari #1')

@push('styles')
<style>
    .hero-parallax {
        position: relative;
        z-index: 0; /* penting untuk stacking context */
        background-image: url('https://ruanglari.com/storage/blog/media/0ldCsNEORqzDKeAQzMFMzSNHn0EKmok8UnVSHkEA.webp');
        background-attachment: fixed;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        }

        /* Fallback sporty pattern jika gambar gagal load */
        .hero-parallax::before {
        content: '';
        position: absolute;
        inset: 0; /* shorthand top/right/bottom/left:0 */
        background-color: #0f172a; /* fallback dark slate */
        background-image: radial-gradient(#334155 1px, transparent 1px);
        background-size: 20px 20px;
        z-index: -1; /* cukup -1, biar di bawah gambar */
        }

        .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(
            to bottom,
            rgba(15, 23, 42, 0.4) 0%,   /* lebih transparan */
            rgba(15, 23, 42, 0.7) 100%  /* jangan terlalu pekat */
        );
        z-index: 1; /* di atas gambar, tapi tetap transparan */
        }

        /* Card efek hover */
        .feature-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05),
                    0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }

        /* Sporty Energetic Gradient (Neon Yellow to Electric Lime) */
        .text-gradient {
        background-image: linear-gradient(90deg, #ccff00, #a3cc00);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        }

        .bg-sporty-primary {
        background-color: #ccff00;
        color: #0f172a;
        }
        .bg-sporty-primary:hover {
        background-color: #b3e600;
        }

</style>
@endpush

@section('content')
    <!-- Hero Section -->
    <section class="hero-parallax pt-32 pb-24 relative overflow-hidden z-10">
        <div class="hero-overlay"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-5xl mx-auto text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-800/80 border border-slate-700 backdrop-blur-sm shadow-sm mb-8">
                    <span class="flex h-2 w-2 rounded-full bg-neon animate-pulse" style="background-color: #ccff00;"></span>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Solusi Manajemen Event Lari</span>
                </div>
                
                <h1 class="text-4xl md:text-5xl font-black text-white mb-6 leading-tight tracking-tight uppercase italic">
                    TINGKATKAN KUALITAS <br/>
                    <span class="text-gradient">EVENT LARI ANDA</span>
                </h1>
                
                <p class="text-lg md:text-xl text-slate-300 mb-12 max-w-3xl mx-auto leading-relaxed font-medium">
                    Platform registrasi end-to-end dengan sistem pembayaran otomatis, manajemen peserta real-time, dan fitur unggulan untuk memanjakan pelari Anda.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-sporty-primary rounded-xl font-bold text-lg transition-all shadow-xl shadow-lime-500/20 transform hover:-translate-y-1 uppercase tracking-wide">
                        Buat Event Sekarang
                    </a>
                    <a href="#features" class="w-full sm:w-auto px-8 py-4 bg-slate-800/80 backdrop-blur-md text-white border border-slate-600 rounded-xl font-bold text-lg hover:bg-slate-700 transition-all shadow-sm flex items-center justify-center gap-2 uppercase tracking-wide">
                        Pelajari Fitur <i class="fas fa-arrow-down text-sm text-neon" style="color: #ccff00;"></i>
                    </a>
                </div>
                
                <!-- Complete Event Search Filter -->
                <div class="bg-white/10 backdrop-blur-xl p-4 md:p-6 rounded-3xl shadow-2xl border border-white/20 max-w-4xl mx-auto relative overflow-hidden">
                    <!-- Subtle inner glow -->
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent pointer-events-none"></div>
                    
                    <p class="text-sm font-bold text-slate-300 mb-4 text-left pl-2 uppercase tracking-wide">Cari Event Lari:</p>
                    <form action="{{ route('events.index') }}" method="GET" class="grid md:grid-cols-4 gap-3 items-center relative z-10">
                        <div class="md:col-span-1">
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <input type="text" name="city" placeholder="Pilih Kota" class="w-full pl-11 pr-4 py-3.5 bg-white border-transparent rounded-xl focus:ring-2 focus:ring-[#ccff00] focus:border-transparent font-medium transition-all text-sm text-slate-900 shadow-inner">
                            </div>
                        </div>
                        <div class="md:col-span-1">
                            <div class="relative">
                                <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <select name="month" class="w-full pl-11 pr-4 py-3.5 bg-white border-transparent rounded-xl focus:ring-2 focus:ring-[#ccff00] focus:border-transparent font-medium appearance-none transition-all text-sm text-slate-900 shadow-inner">
                                    <option value="">Pilih Bulan</option>
                                    @php
                                        $currentMonth = now()->startOfMonth();
                                        $months = [];
                                        for ($i = 0; $i < 12; $i++) {
                                            $months[] = [
                                                'value' => $currentMonth->format('Y-m'),
                                                'label' => $currentMonth->translatedFormat('F Y')
                                            ];
                                            $currentMonth->addMonth();
                                        }
                                    @endphp
                                    @foreach($months as $m)
                                        <option value="{{ $m['value'] }}">{{ $m['label'] }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>
                        </div>
                        <div class="md:col-span-1">
                            <div class="relative">
                                <i class="fas fa-flag absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <select name="category" class="w-full pl-11 pr-4 py-3.5 bg-white border-transparent rounded-xl focus:ring-2 focus:ring-[#ccff00] focus:border-transparent font-medium appearance-none transition-all text-sm text-slate-900 shadow-inner">
                                    <option value="">Kategori</option>
                                    <option value="5k">5K</option>
                                    <option value="10k">10K</option>
                                    <option value="half-marathon">Half Marathon</option>
                                    <option value="marathon">Marathon</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                            </div>
                        </div>
                        <div class="md:col-span-1">
                            <button type="submit" class="w-full bg-slate-900 text-white px-8 py-3.5 rounded-xl text-sm font-bold hover:bg-black transition-colors shadow-lg shadow-black/50 border border-slate-700 uppercase tracking-wide flex items-center justify-center gap-2">
                                <i class="fas fa-search text-[#ccff00]"></i> Cari
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Core Organizer Features Section -->
    <section id="features" class="py-24 bg-white relative">
        <div class="container mx-auto px-4">
            <div class="text-center mb-20">
                <h2 class="text-3xl md:text-5xl font-black italic text-slate-900 mb-4 uppercase tracking-tight">MENGAPA MEMILIH <span class="relative inline-block px-2 mx-1"><span class="absolute inset-0 bg-black -skew-x-12"></span><span class="relative text-[#ccff00]">RUANG LARI</span></span>?</h2>
                <p class="text-lg text-slate-500 max-w-2xl mx-auto font-medium">Kami mengurus kerumitan teknis agar Anda bisa fokus menciptakan pengalaman lari yang tak terlupakan.</p>
            </div>
            
            <div class="grid lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                <!-- Feature 1 -->
                <div class="feature-card bg-slate-50 p-8 rounded-3xl border border-slate-100 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-slate-200 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl shadow-sm border border-slate-800 flex items-center justify-center mb-6 relative z-10">
                        <i class="fas fa-bolt text-2xl" style="color: #ccff00;"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900 relative z-10">Pembayaran Otomatis</h3>
                    <p class="text-slate-600 text-sm leading-relaxed relative z-10">Integrasi payment gateway untuk verifikasi instan. Dukungan VA, QRIS, Kartu Kredit, dan e-Wallet. Ucapkan selamat tinggal pada mutasi manual.</p>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-slate-50 p-8 rounded-3xl border border-slate-100 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-slate-200 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl shadow-sm border border-slate-800 flex items-center justify-center mb-6 relative z-10">
                        <i class="fas fa-chart-pie text-2xl" style="color: #ccff00;"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900 relative z-10">Dashboard Analitik</h3>
                    <p class="text-slate-600 text-sm leading-relaxed relative z-10">Pantau pendaftaran, pendapatan, dan demografi peserta secara real-time. Ekspor data dengan mudah untuk keperluan race pack collection.</p>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-slate-50 p-8 rounded-3xl border border-slate-100 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-slate-200 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl shadow-sm border border-slate-800 flex items-center justify-center mb-6 relative z-10">
                        <i class="fas fa-ticket-alt text-2xl" style="color: #ccff00;"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900 relative z-10">Manajemen E-Ticket</h3>
                    <p class="text-slate-600 text-sm leading-relaxed relative z-10">Generasi e-ticket otomatis dengan QR code unik. Sistem check-in cepat saat pengambilan race pack untuk menghindari antrean panjang.</p>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card bg-slate-50 p-8 rounded-3xl border border-slate-100 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-slate-200 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl shadow-sm border border-slate-800 flex items-center justify-center mb-6 relative z-10">
                        <i class="fas fa-layer-group text-2xl" style="color: #ccff00;"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900 relative z-10">Add-ons & Kustomisasi</h3>
                    <p class="text-slate-600 text-sm leading-relaxed relative z-10">Jual merchandise tambahan (jersey, medali custom) langsung saat registrasi. Formulir pendaftaran yang dapat disesuaikan dengan kebutuhan event.</p>
                </div>

                <!-- Feature 5 -->
                <div class="feature-card bg-slate-50 p-8 rounded-3xl border border-slate-100 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-slate-200 rounded-bl-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl shadow-sm border border-slate-800 flex items-center justify-center mb-6 relative z-10">
                        <i class="fas fa-users text-2xl" style="color: #ccff00;"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-slate-900 relative z-10">Registrasi Komunitas</h3>
                    <p class="text-slate-600 text-sm leading-relaxed relative z-10">Fasilitasi pendaftaran kolektif untuk komunitas lari. Sistem undangan dan manajemen anggota yang mempermudah kapten komunitas.</p>
                </div>

                <!-- Feature 6 -->
                <div class="feature-card bg-slate-900 p-8 rounded-3xl border border-slate-800 relative overflow-hidden group flex flex-col justify-center text-center">
                    <h3 class="text-2xl font-black italic uppercase tracking-tight mb-4 text-white">Siap Memulai?</h3>
                    <p class="text-slate-400 text-sm mb-6 font-medium">Konsultasikan kebutuhan event Anda dengan tim kami sekarang.</p>
                    <a href="https://wa.me/6281234567890" target="_blank" class="px-6 py-3 bg-sporty-primary rounded-xl font-bold text-sm transition-colors shadow-lg shadow-lime-500/20 inline-flex items-center justify-center gap-2 uppercase tracking-wide">
                        <i class="fab fa-whatsapp text-lg"></i> Hubungi Tim Sales
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Runner Ecosystem Section (Secondary Value) -->
    <section class="py-24 bg-slate-50 border-t border-slate-200">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-12 max-w-6xl mx-auto">
                <div class="flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-bold uppercase tracking-wider mb-6">
                        <i class="fas fa-running" style="color: #ccff00;"></i> Ekosistem Pelari
                    </div>
                    <h2 class="text-2xl md:text-4xl font-black italic text-slate-900 mb-6 leading-tight uppercase tracking-tight">
                        BUKAN SEKADAR TIKET, TAPI<br/>
                        <span class="relative inline-block px-2 mx-1"><span class="absolute inset-0 bg-black -skew-x-12"></span><span class="relative text-[#ccff00]">PENINGKATAN PERFORMA</span></span>
                    </h2>
                    <p class="text-slate-600 text-lg mb-8 leading-relaxed font-medium">
                        RuangLari juga menyediakan tools canggih bagi pelari untuk mempersiapkan diri menghadapi event Anda. Ini menambah nilai (value) bagi peserta yang mendaftar.
                    </p>
                    
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 shadow-md flex items-center justify-center shrink-0 mt-1">
                                <i class="fas fa-route text-xl" style="color: #ccff00;"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-lg uppercase tracking-wide">Route Builder & GPX</h4>
                                <p class="text-slate-500 text-sm mt-1">Pelari dapat mempelajari rute elevasi event Anda sebelum hari H.</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 shadow-md flex items-center justify-center shrink-0 mt-1">
                                <i class="fas fa-calendar-check text-xl" style="color: #ccff00;"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-lg uppercase tracking-wide">Training Program</h4>
                                <p class="text-slate-500 text-sm mt-1">Rencana latihan terstruktur yang dapat disesuaikan dengan jarak kategori (5K hingga Marathon).</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-slate-900 shadow-md flex items-center justify-center shrink-0 mt-1">
                                <i class="fas fa-running text-xl" style="color: #ccff00;"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900 text-lg uppercase tracking-wide">Analisis Form Lari</h4>
                                <p class="text-slate-500 text-sm mt-1">Analisis Form Lari dengan bantuan teknologi untuk mengetahui kesalahan dan bagaimana cara fixnya.</p>
                            </div>
                        </li>
                    </ul>
                </div>
                
                <div class="flex-1 w-full relative">
                    <!-- Placeholder for App/Dashboard Mockup -->
                    <div class="relative rounded-3xl bg-slate-900 p-2 shadow-2xl transform rotate-2 hover:rotate-0 transition-transform duration-500 border border-slate-700">
                        <div class="absolute inset-0 rounded-3xl opacity-20" style="background: linear-gradient(to top right, #ccff00, transparent);"></div>
                        <div class="bg-slate-800 rounded-2xl overflow-hidden aspect-[4/3] flex items-center justify-center border border-slate-700 relative z-10">
                            <div class="text-center">
                                <i class="fas fa-laptop-code text-6xl text-slate-600 mb-4 block"></i>
                                <span class="text-slate-500 font-mono text-sm">Dashboard Analytics</span>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 bg-slate-900/80 backdrop-blur p-4 rounded-xl border border-slate-700">
                                <div class="h-2 w-1/3 bg-slate-600 rounded mb-2"></div>
                                <div class="h-2 w-1/2 bg-slate-700 rounded"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative Elements -->
                    <div class="absolute -z-10 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[100%] h-[100%] rounded-full mix-blend-multiply filter blur-3xl opacity-30" style="background-color: #ccff00;"></div>
                </div>
            </div>
        </div>
    </section>
@endsection