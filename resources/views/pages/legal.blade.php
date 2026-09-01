@extends('layouts.pacerhub')

@section('title', 'Kebijakan Privasi, Syarat & Ketentuan, dan FAQ | RuangLari Indonesia')
@section('meta_title', 'Kebijakan Privasi, Syarat & Ketentuan, dan FAQ | RuangLari Indonesia')
@section('meta_description', 'Pusat informasi legalitas, transparansi perlindungan privasi data pelari, syarat ketentuan penggunaan platform, dan panduan lengkap fitur RuangLari.')
@section('canonical_url', url('/legal'))

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-200" 
     x-data="{
        activeTab: '{{ request()->query('tab') ?? 'privacy' }}',
        searchQuery: '',
        activeCategory: 'all',
        openFaq: null,
        init() {
            const hash = window.location.hash.replace('#', '');
            if (['privacy', 'terms', 'faq'].includes(hash)) {
                this.activeTab = hash;
            }
            window.addEventListener('hashchange', () => {
                const newHash = window.location.hash.replace('#', '');
                if (['privacy', 'terms', 'faq'].includes(newHash)) {
                    this.activeTab = newHash;
                }
            });
        },
        setTab(tab) {
            this.activeTab = tab;
            window.location.hash = tab;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
     }">

    <!-- HERO HEADER -->
    <header class="relative border-b border-slate-800/80 bg-slate-900/60 pt-28 pb-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded bg-slate-800 border border-slate-700 text-[11px] font-bold tracking-wider text-slate-300 uppercase mb-4">
                    <span>RuangLari Legal & Help Center</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight mb-3">
                    Kebijakan, Ketentuan & Bantuan
                </h1>
                <p class="text-sm sm:text-base text-slate-400 leading-relaxed">
                    Panduan resmi perlindungan data pribadi pelari, syarat layanan ekosistem RuangLari, serta pusat bantuan untuk seluruh fitur platform.
                </p>
            </div>

            <!-- TAB NAVIGATION CONTROLS -->
            <div class="mt-8">
                <div class="inline-flex p-1 rounded-md bg-slate-900 border border-slate-800 gap-1 flex-wrap sm:flex-nowrap">
                    <button type="button" 
                            @click="setTab('privacy')"
                            :class="activeTab === 'privacy' ? 'bg-neon text-slate-950 font-black shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 font-bold'"
                            class="px-4 py-2 rounded-md text-xs uppercase tracking-wider transition cursor-pointer">
                        Kebijakan Privasi
                    </button>
                    <button type="button" 
                            @click="setTab('terms')"
                            :class="activeTab === 'terms' ? 'bg-neon text-slate-950 font-black shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 font-bold'"
                            class="px-4 py-2 rounded-md text-xs uppercase tracking-wider transition cursor-pointer">
                        Syarat & Ketentuan
                    </button>
                    <button type="button" 
                            @click="setTab('faq')"
                            :class="activeTab === 'faq' ? 'bg-neon text-slate-950 font-black shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/80 font-bold'"
                            class="px-4 py-2 rounded-md text-xs uppercase tracking-wider transition cursor-pointer">
                        FAQ & Pusat Bantuan
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT CONTAINER -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- ========================================== -->
        <!-- TAB 1: KEBIJAKAN PRIVASI (PRIVACY POLICY)   -->
        <!-- ========================================== -->
        <section x-show="activeTab === 'privacy'" x-cloak class="space-y-8">
            <div class="p-6 rounded-lg bg-slate-900/90 border border-slate-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-800">
                    <div>
                        <h2 class="text-xl font-bold text-white">Kebijakan Privasi RuangLari</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Versi 2.4 — Berlaku efektif sejak 1 September 2026</p>
                    </div>
                    <span class="inline-flex self-start px-2.5 py-1 rounded bg-slate-800 text-slate-300 text-xs font-mono font-semibold border border-slate-700">
                        GDPR & UU PDP Compliant
                    </span>
                </div>

                <div class="mt-6 space-y-6 text-sm text-slate-300 leading-relaxed">
                    <p>
                        RuangLari berkomitmen penuh melindungi privasi pengguna, pelari, pelatih (coach), penjual (seller), serta Event Organizer (EO) yang menggunakan platform kami. Kebijakan ini menjelaskan bagaimana data Anda dikumpulkan, diproses, disimpan, dan dilindungi saat mengakses situs web, aplikasi web, tools performa lari, dan layanan terkait RuangLari.
                    </p>

                    <!-- Section 1 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">1. Data yang Kami Kumpulkan</h3>
                        <p>Kami mengumpulkan jenis data berikut untuk mendukung pengalaman ekosistem lari yang optimal:</p>
                        <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                            <li><strong class="text-white">Informasi Identitas & Kontak:</strong> Nama lengkap, alamat email, nomor telepon/WhatsApp, jenis kelamin, tanggal lahir, ukuran jersey lari, dan kontak darurat untuk keperluan pendaftaran event lari.</li>
                            <li><strong class="text-white">Data Performa & Telemetri Lari:</strong> Catatan Personal Best (PB 5K, 10K, HM, FM), kalkulasi nilai VDOT, riwayat aktivitas lari yang disinkronkan melalui integrasi resmi API Strava (jarak, waktu tempuh, pace, elevation gain, heart rate, dan cadence), serta file GPX rute lari.</li>
                            <li><strong class="text-white">Data Transaksi & Pembayaran:</strong> Riwayat pembelian tiket event, langganan program coaching, transaksi Marketplace perlengkapan lari, nomor invoice, dan bukti konfirmasi transfer bank atau payment gateway. Kami tidak menyimpan nomor kartu kredit atau PIN perbankan pengguna.</li>
                            <li><strong class="text-white">Data Teknis & Akses:</strong> Alamat IP, jenis browser, preferensi bahasa, dan data sesi autentikasi untuk menjaga keamanan akun.</li>
                        </ul>
                    </div>

                    <!-- Section 2 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">2. Tujuan Penggunaan Data</h3>
                        <p>Data pribadi Anda hanya digunakan untuk tujuan yang jelas dan sah:</p>
                        <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                            <li>Memproses penerbitan e-ticket resmi event lari, nomor BIB peserta, dan koordinasi Race Pack Collection bersama Event Organizer.</li>
                            <li>Menghitung rekomendasi pace latihan, target race, dan beban volume mingguan secara presisi melalui modul Performance Lab & TrackMaster.</li>
                            <li>Menyediakan notifikasi jadwal latihan harian atau update status pesanan melalui pesan WhatsApp dan email konfirmasi.</li>
                            <li>Memfasilitasi fitur Run Connect untuk menemukan sesama pelari di kota/wilayah yang sama atas persetujuan pengguna.</li>
                            <li>Memproses pesanan produk perlengkapan lari di Marketplace serta penerusan alamat pengiriman ke jasa kurir logistik terdaftar.</li>
                        </ul>
                    </div>

                    <!-- Section 3 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">3. Integrasi Strava API & Akses Pihak Ketiga</h3>
                        <p>
                            Saat Anda memilih untuk menghubungkan akun Strava ke RuangLari:
                        </p>
                        <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                            <li>Otorisasi dilakukan melalui protokol resmi OAuth 2.0 Strava. RuangLari tidak pernah mengetahui atau menyimpan kata sandi akun Strava Anda.</li>
                            <li>Akses hanya digunakan untuk membaca aktivitas lari Anda guna analisis pace, kepatuhan program latihan, dan leaderboard komunitas.</li>
                            <li>Anda berhak memutuskan sambungan (disconnect) akun Strava dari RuangLari kapan saja melalui menu Pengaturan Profil Pelari.</li>
                        </ul>
                    </div>

                    <!-- Section 4 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">4. Keamanan & Penyimpanan Data</h3>
                        <p>
                            Seluruh data ditransmisikan melalui protokol aman terenkripsi TLS 1.3 / SSL 256-bit dan disimpan pada infrastruktur server terlindung. RuangLari memberlakukan kebijakan tanpa penjualan data (<em class="text-white not-italic font-semibold">Strict No-Sell Policy</em>)—kami tidak pernah menjual data pribadi atau aktivitas olahraga Anda kepada agensi iklan pihak ketiga manapun.
                        </p>
                    </div>

                    <!-- Section 5 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">5. Hak & Kendali Pengguna atas Data</h3>
                        <p>Sesuai undang-undang perlindungan data pribadi yang berlaku, Anda memiliki hak penuh untuk:</p>
                        <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                            <li>Mengakses, memeriksa, dan memperbarui informasi biodata profil Anda kapan saja.</li>
                            <li>Mengatur preferensi penerimaan notifikasi WhatsApp dan email promosi.</li>
                            <li>Mengajukan permohonan penghapusan akun beserta seluruh riwayat aktivitas secara permanen dengan menghubungi tim dukungan resmi kami.</li>
                        </ul>
                    </div>

                    <!-- Section 6 -->
                    <div class="p-4 rounded-md bg-slate-950 border border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold text-white">Pertanyaan seputar privasi data Anda?</div>
                            <div class="text-xs text-slate-400 mt-0.5">Hubungi Petugas Keamanan Data & Privasi RuangLari melalui email resmi.</div>
                        </div>
                        <a href="mailto:privacy@ruanglari.com" class="px-3.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 transition">
                            privacy@ruanglari.com
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- TAB 2: SYARAT & KETENTUAN (TERMS OF SERVICE)-->
        <!-- ========================================== -->
        <section x-show="activeTab === 'terms'" x-cloak class="space-y-8">
            <div class="p-6 rounded-lg bg-slate-900/90 border border-slate-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-800">
                    <div>
                        <h2 class="text-xl font-bold text-white">Syarat & Ketentuan Layanan</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Terakhir diperbarui: 1 September 2026</p>
                    </div>
                    <span class="inline-flex self-start px-2.5 py-1 rounded bg-slate-800 text-slate-300 text-xs font-mono font-semibold border border-slate-700">
                        Ketentuan Resmi Pengguna
                    </span>
                </div>

                <div class="mt-6 space-y-6 text-sm text-slate-300 leading-relaxed">
                    <p>
                        Selamat datang di RuangLari. Dokumen Syarat dan Ketentuan ini mengatur hak, kewajiban, dan tata tertib penggunaan platform RuangLari (situs web, ticketing event, marketplace, modul kalkulator, dan program coaching). Dengan mendaftar atau menggunakan layanan kami, Anda menyatakan telah membaca, memahami, dan menyetujui seluruh ketentuan di bawah ini.
                    </p>

                    <!-- Section 1 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">1. Akun Pengguna & Kelayakan</h3>
                        <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                            <li>Pengguna wajib berusia sekurang-kurangnya 17 tahun atau memiliki persetujuan orang tua/wali resmi jika mengikuti event kategori remaja/junior.</li>
                            <li>Pengguna bertanggung jawab penuh atas keakuratan data profil dan keamanan kata sandi akun masing-masing.</li>
                            <li>Satu akun hanya diperuntukkan bagi satu individu pelari. Dilarang keras memindahtangankan akun tanpa konfirmasi resmi.</li>
                        </ul>
                    </div>

                    <!-- Section 2 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">2. Pembelian Tiket & Partisipasi Event Lari</h3>
                        <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                            <li><strong class="text-white">Pendaftaran & E-Ticket:</strong> Pembayaran tiket event lari harus diselesaikan dalam batas waktu yang ditentukan pada saat checkout. E-ticket resmi beserta kode QR unik akan diterbitkan setelah pembayaran diverifikasi.</li>
                            <li><strong class="text-white">Pengambilan Race Pack (RPC):</strong> Pengambilan Race Pack wajib menyertakan e-ticket resmi dan kartu identitas asli (KTP/SIM/Paspor). Jika diwakilkan, penerima kuasa wajib membawa surat kuasa bermaterai dan fotokopi identitas peserta.</li>
                            <li><strong class="text-white">Kebijakan Pemindahtanganan & Pengembalian Dana (Refund):</strong> Tiket event yang telah dibeli bersifat final dan tidak dapat di-refund, kecuali jika event dibatalkan secara sepihak oleh Event Organizer (EO) penyelenggara sesuai regulasi yang berlaku pada masing-masing event.</li>
                            <li><strong class="text-white">Kondisi Force Majeure:</strong> Perubahan jadwal, rute, atau pembatalan event akibat bencana alam, cuaca ekstrem, atau kebijakan pemerintah berada di bawah wewenang Event Organizer penyelenggara.</li>
                        </ul>
                    </div>

                    <!-- Section 3 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">3. Disclaimer Kesehatan & Tanggung Jawab Fisik Pelari</h3>
                        <div class="p-4 rounded-md bg-slate-950 border border-slate-800 space-y-2">
                            <div class="text-xs font-bold text-white uppercase tracking-wider">Perhatian Penting Kebugaran Fisik:</div>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                Olahraga lari jarak jauh (5K, 10K, Half Marathon, Full Marathon, Ultra) membutuhkan kesiapan fisik dan kardiovaskular yang matang. Kalkulator pace, formula VDOT, dan program latihan di RuangLari disediakan sebagai panduan pelatihan olahraga terstruktur dan <strong class="text-white">bukan merupakan anjuran atau diagnosis medis klinis</strong>. Setiap pelari bertanggung jawab penuh atas kondisi kesehatannya sendiri dan dianjurkan berkonsultasi dengan dokter spesialis kedokteran olahraga sebelum memulai program intensif.
                            </p>
                        </div>
                    </div>

                    <!-- Section 4 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">4. Transaksi Marketplace & Penjualan Produk</h3>
                        <ul class="list-disc pl-5 space-y-1.5 text-slate-300">
                            <li>Seller di Marketplace RuangLari wajib memastikan keaslian, kualitas, dan kesesuaian stok produk perlengkapan lari yang dipajang.</li>
                            <li>Pembeli wajib memeriksa kelengkapan barang saat pesanan tiba. Komplain kerusakan atau ketidaksesuaian barang wajib disertai video unboxing utuh dalam waktu maksimal 2x24 jam sejak barang diterima.</li>
                            <li>RuangLari memfasilitasi sistem rekening bersama (*escrow payment*) guna memastikan dana diteruskan kepada Seller hanya setelah pesanan terkonfirmasi selesai.</li>
                        </ul>
                    </div>

                    <!-- Section 5 -->
                    <div class="space-y-2.5">
                        <h3 class="text-base font-semibold text-white">5. Hak Kekayaan Intelektual & Hukum yang Berlaku</h3>
                        <p>
                            Seluruh merk, logo, algoritma kalkulator, metodologi program latihan TrackMaster, dan materi konten pada platform RuangLari merupakan hak kekayaan intelektual yang dilindungi undang-undang Republik Indonesia. Syarat dan ketentuan ini tunduk pada hukum yang berlaku di wilayah Negara Kesatuan Republik Indonesia.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================== -->
        <!-- TAB 3: FAQ & PUSAT BANTUAN                  -->
        <!-- ========================================== -->
        <section x-show="activeTab === 'faq'" x-cloak class="space-y-6"
                 x-data="{
                    faqs: [
                        {
                            id: 1,
                            category: 'event',
                            q: 'Bagaimana cara mendaftar dan membeli tiket event lari di RuangLari?',
                            a: 'Pilih event lari yang ingin Anda ikuti melalui menu Event Lari, pilih kategori jarak (5K, 10K, HM, FM), isi kelengkapan data peserta dan ukuran jersey, lalu lanjutkan ke pembayaran. Setelah pembayaran berhasil diverifikasi, e-ticket dengan QR Code akan langsung tersedia di menu Tiket Saya dan dikirimkan ke email Anda.'
                        },
                        {
                            id: 2,
                            category: 'event',
                            q: 'Di mana saya bisa melihat e-ticket dan bukti pendaftaran saya?',
                            a: 'E-ticket dapat diakses kapan saja melalui menu Profil > Pesanan Saya atau melalui tab My Program / Orders di dashboard runner Anda. Anda dapat mengunduh e-ticket dalam format PDF atau menunjukkannya langsung dari ponsel saat Race Pack Collection.'
                        },
                        {
                            id: 3,
                            category: 'event',
                            q: 'Apakah tiket event yang sudah dibeli bisa dipindahtangankan atau diubah namanya?',
                            a: 'Kebijakan pemindahtanganan (transfer slot) dan perubahan nama sepenuhnya mengikuti regulasi masing-masing Event Organizer (EO). Jika EO mengizinkan transfer slot, fitur pergantian data peserta akan dibuka di dashboard pesanan sebelum batas waktu penutupan Race Pack yang ditentukan.'
                        },
                        {
                            id: 4,
                            category: 'training',
                            q: 'Bagaimana cara menghubungkan akun Strava saya ke RuangLari?',
                            a: 'Masuk ke Dashboard Runner, buka tab Profil atau Pengaturan Akun, lalu klik tombol Hubungkan Strava. Anda akan diarahkan ke halaman login resmi Strava untuk memberikan otorisasi sinkronisasi aktivitas lari. Setelah terhubung, aktivitas lari harian Anda akan otomatis tersinkronisasi ke kalender latihan.'
                        },
                        {
                            id: 5,
                            category: 'training',
                            q: 'Bagaimana cara kerja Kalkulator Pace dan VDOT di RuangLari?',
                            a: 'Kalkulator RuangLari mengadopsi formula Jack Daniels VDOT dan formula Riegel untuk menghitung zona intensitas latihan (Easy, Marathon, Threshold, Interval, Repetition) serta memproyeksikan target waktu lari dari jarak uji (benchmark/time-trial) ke jarak target Anda secara presisi.'
                        },
                        {
                            id: 6,
                            category: 'training',
                            q: 'Apakah saya bisa menyelesaikan sesi latihan kalender tanpa mengklik Start Activity?',
                            a: 'Ya. Jika Anda berlari menggunakan smartwatch (Garmin, Coros, Apple Watch) dan telah menyelesaikan sesi di luar aplikasi, cukup buka detail sesi di kalender lalu klik tombol Finish Activity. Anda dapat memasukkan link aktivitas Strava, nilai RPE (tingkat kelelahan), feeling, dan catatan latihan secara langsung.'
                        },
                        {
                            id: 7,
                            category: 'marketplace',
                            q: 'Bagaimana sistem pembayaran belanja di Marketplace RuangLari?',
                            a: 'Marketplace RuangLari mendukung pembayaran melalui Transfer Bank Virtual Account, QRIS, dan dompet digital. Dana Anda diamankan dengan sistem escrow RuangLari dan baru akan diteruskan ke penjual setelah Anda mengonfirmasi bahwa produk telah diterima dengan baik.'
                        },
                        {
                            id: 8,
                            category: 'marketplace',
                            q: 'Bagaimana cara membuka toko dan berjualan perlengkapan lari sebagai Seller?',
                            a: 'Masuk ke menu Marketplace lalu pilih Mulai Jual / Buka Toko. Lengkapi profil toko, nomor rekening penerimaan dana, dan data verifikasi identitas. Setelah disetujui, Anda dapat langsung mengunggah produk sepatu, apparel, suplemen energi gel, atau aksesori lari Anda.'
                        },
                        {
                            id: 9,
                            category: 'community',
                            q: 'Apa itu fitur Run Connect dan bagaimana cara mencari teman lari terdekat?',
                            a: 'Run Connect adalah fitur pencarian rekan lari berdasarkan kota dan lokasi geografis. Anda dapat melihat pelari lain di area Anda, mengirimkan pesan pertemanan, dan membuat janji lari pagi atau sore bersama secara aman.'
                        },
                        {
                            id: 10,
                            category: 'account',
                            q: 'Bagaimana jika saya lupa kata sandi atau ingin memperbarui nomor WhatsApp?',
                            a: 'Gunakan opsi Lupa Kata Sandi pada halaman login untuk menerima tautan reset kata sandi melalui email. Untuk memperbarui nomor WhatsApp notifikasi harian, buka Dashboard Runner > Tab Profile dan perbarui nomor telepon Anda.'
                        }
                    ],
                    matchesFilter(item) {
                        const matchCat = this.activeCategory === 'all' || item.category === this.activeCategory;
                        const matchText = this.searchQuery.trim() === '' || 
                            item.q.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                            item.a.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchCat && matchText;
                    }
                 }">
            
            <!-- FAQ SEARCH & CATEGORY BAR -->
            <div class="p-6 rounded-lg bg-slate-900/90 border border-slate-800 space-y-4">
                <div>
                    <h2 class="text-xl font-bold text-white">Pusat Bantuan & Pertanyaan Umum</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Cari jawaban cepat seputar tiket event, kalender latihan, Strava, dan marketplace.</p>
                </div>

                <!-- Live Search Box -->
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery"
                           placeholder="Cari pertanyaan... (contoh: tiket, strava, vdot, refund, toko)" 
                           class="w-full bg-slate-950 border border-slate-800 rounded-md px-4 py-3 text-white text-xs placeholder-slate-500 focus:border-neon focus:outline-none transition">
                    <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-3 top-3 text-xs text-slate-400 hover:text-white">
                        Reset
                    </button>
                </div>

                <!-- Category Filter Chips -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" 
                            @click="activeCategory = 'all'"
                            :class="activeCategory === 'all' ? 'bg-white text-slate-950 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 font-medium border border-slate-700'"
                            class="px-3 py-1.5 rounded text-xs transition">
                        Semua Topik
                    </button>
                    <button type="button" 
                            @click="activeCategory = 'event'"
                            :class="activeCategory === 'event' ? 'bg-white text-slate-950 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 font-medium border border-slate-700'"
                            class="px-3 py-1.5 rounded text-xs transition">
                        Tiket & Event Lari
                    </button>
                    <button type="button" 
                            @click="activeCategory = 'training'"
                            :class="activeCategory === 'training' ? 'bg-white text-slate-950 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 font-medium border border-slate-700'"
                            class="px-3 py-1.5 rounded text-xs transition">
                        Training & Strava
                    </button>
                    <button type="button" 
                            @click="activeCategory = 'marketplace'"
                            :class="activeCategory === 'marketplace' ? 'bg-white text-slate-950 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 font-medium border border-slate-700'"
                            class="px-3 py-1.5 rounded text-xs transition">
                        Marketplace & Pesanan
                    </button>
                    <button type="button" 
                            @click="activeCategory = 'community'"
                            :class="activeCategory === 'community' ? 'bg-white text-slate-950 font-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 font-medium border border-slate-700'"
                            class="px-3 py-1.5 rounded text-xs transition">
                        Komunitas & Akun
                    </button>
                </div>
            </div>

            <!-- FAQ ACCORDION LIST -->
            <div class="space-y-3">
                <template x-for="item in faqs" :key="item.id">
                    <div x-show="matchesFilter(item)" 
                         class="rounded-lg bg-slate-900/90 border border-slate-800 overflow-hidden transition">
                        <button type="button" 
                                @click="openFaq = (openFaq === item.id ? null : item.id)"
                                class="w-full p-4 sm:p-5 text-left flex items-center justify-between gap-4 hover:bg-slate-800/40 transition">
                            <span class="text-sm font-semibold text-white" x-text="item.q"></span>
                            <span class="text-slate-400 font-mono text-sm font-bold flex-shrink-0" x-text="openFaq === item.id ? '−' : '+'"></span>
                        </button>
                        <div x-show="openFaq === item.id" 
                             x-collapse
                             class="px-4 pb-5 sm:px-5 border-t border-slate-800/60 pt-3">
                            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed" x-text="item.a"></p>
                        </div>
                    </div>
                </template>

                <div x-show="faqs.filter(i => matchesFilter(i)).length === 0" class="p-8 text-center bg-slate-900/60 rounded-lg border border-slate-800">
                    <p class="text-sm text-slate-400">Tidak ada pertanyaan yang sesuai dengan pencarian Anda.</p>
                </div>
            </div>

            <!-- STILL NEED HELP BANNER -->
            <div class="p-6 rounded-lg bg-slate-900 border border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mt-8">
                <div>
                    <h3 class="text-base font-bold text-white">Masih membutuhkan bantuan lebih lanjut?</h3>
                    <p class="text-xs text-slate-400 mt-1">Tim dukungan RuangLari siap membantu Anda setiap hari kerja (08.00 - 20.00 WIB).</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="https://wa.me/6281234567890?text=Halo%20RuangLari%20saya%20butuh%20bantuan" target="_blank" class="px-4 py-2.5 rounded-md bg-neon hover:bg-white text-slate-950 font-black text-xs uppercase tracking-wider transition shadow-sm">
                        WhatsApp Support
                    </a>
                    <a href="mailto:support@ruanglari.com" class="px-4 py-2.5 rounded-md bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider border border-slate-700 transition">
                        Email Bantuan
                    </a>
                </div>
            </div>
        </section>

    </main>
</div>
@endsection
