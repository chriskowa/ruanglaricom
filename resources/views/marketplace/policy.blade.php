@extends('layouts.pacerhub', ['lightMode' => false])

@section('title', 'Kebijakan & Keamanan Marketplace | RuangLari Indonesia')
@section('meta_title', 'Kebijakan & Keamanan Marketplace | RuangLari Indonesia')
@section('meta_description', 'Panduan resmi keamanan bertransaksi di Marketplace RuangLari: sistem escrow rekening bersama, garansi keaslian gear lari, transfer slot BIB, lelang, pengiriman, dan resolusi komplain.')
@section('canonical_url', route('marketplace.policy'))

@section('content')
<div class="min-h-screen bg-[#060A12] text-slate-200 font-sans selection:bg-neon selection:text-dark">

    <!-- HERO HEADER -->
    <header class="relative border-b border-slate-800 bg-[#0B101B] pt-28 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <nav class="flex mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2 text-xs text-slate-400">
                    <li>
                        <a href="{{ url('/') }}" class="hover:text-neon transition-colors">Beranda</a>
                    </li>
                    <li>
                        <span class="text-slate-600">/</span>
                    </li>
                    <li>
                        <a href="{{ route('marketplace.index') }}" class="hover:text-neon transition-colors">Marketplace</a>
                    </li>
                    <li>
                        <span class="text-slate-600">/</span>
                    </li>
                    <li class="text-white font-semibold" aria-current="page">
                        Kebijakan Transaksi
                    </li>
                </ol>
            </nav>

            <div class="max-w-3xl">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-white tracking-tight leading-tight mb-3">
                    Kebijakan & Keamanan Transaksi Marketplace
                </h1>
                <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-4">
                    Standar perlindungan pembeli dan penjual di ekosistem RuangLari. Menjamin keamanan dana melalui sistem rekening bersama (escrow), komitmen 100% originalitas perlengkapan lari, serta kepastian alur resolusi sengketa.
                </p>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 pt-1">
                    <span class="px-2.5 py-1 rounded bg-[#121826] border border-slate-800 text-slate-300 font-semibold">
                        Versi 2.1 &bull; Berlaku Efektif September 2026
                    </span>
                    <span>Diperbarui secara berkala untuk kenyamanan komunitas pelari</span>
                </div>
            </div>

            <!-- 3 CORE PILLARS OVERVIEW -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8 pt-6 border-t border-slate-800">
                <div class="p-4 rounded-lg bg-[#0E1524] border border-slate-800">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="p-1.5 rounded-md bg-[#182338] text-neon">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white">Sistem Escrow Terjamin</h2>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Dana pembayaran pembeli disimpan aman di rekening bersama RuangLari dan hanya dilepas ke penjual setelah pesanan diverifikasi tiba dengan baik.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-[#0E1524] border border-slate-800">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="p-1.5 rounded-md bg-[#182338] text-neon">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white">Garansi 100% Original</h2>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Larangan keras memperjualbelikan barang tiruan (KW/replika). Pengembalian dana penuh 100% jika produk terbukti tidak orisinil.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-[#0E1524] border border-slate-800">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="p-1.5 rounded-md bg-[#182338] text-neon">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-white">Mediasi Sengketa Adil</h2>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Tim admin independen siap menengahi komplain transaksi berdasarkan bukti video unboxing dan riwayat transaksi objektif.
                    </p>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN BODY (SIDEBAR TOC + CONTENT) -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            
            <!-- LEFT COLUMN: STICKY TABLE OF CONTENTS -->
            <aside class="lg:col-span-4 space-y-4 lg:sticky lg:top-24">
                <div class="p-5 rounded-lg bg-[#0E1524] border border-slate-800 shadow-md">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 pb-2 border-b border-slate-800">
                        Daftar Pasal Kebijakan
                    </h3>
                    <nav class="space-y-1 text-xs" aria-label="Daftar Isi Kebijakan">
                        <a href="#pasal-1-escrow" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            1. Rekening Bersama (Escrow)
                        </a>
                        <a href="#pasal-2-originalitas" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            2. Jaminan Keaslian & Anti-Palsu
                        </a>
                        <a href="#pasal-3-slot-bib" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            3. Ketentuan Slot Lari &amp; Transfer BIB
                        </a>
                        <a href="#pasal-4-lelang" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            4. Aturan Lelang (Auctions)
                        </a>
                        <a href="#pasal-5-konsinyasi" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            5. Titip Jual &amp; Kurasi (Consignment)
                        </a>
                        <a href="#pasal-6-pengiriman" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            6. Standar Pengiriman &amp; Resi
                        </a>
                        <a href="#pasal-7-refund" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            7. Retur &amp; Pengembalian Dana
                        </a>
                        <a href="#pasal-8-sengketa" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            8. Mediasi &amp; Resolusi Sengketa
                        </a>
                        <a href="#pasal-9-larangan" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            9. Barang yang Dilarang
                        </a>
                        <a href="#pasal-10-sanksi" class="block px-3 py-2 rounded-md font-semibold text-slate-300 hover:text-white hover:bg-[#162035] transition-colors">
                            10. Sanksi &amp; Keamanan Akun
                        </a>
                    </nav>
                </div>

                <!-- HELP & CONTACT CARD -->
                <div class="p-5 rounded-lg bg-[#0E1524] border border-slate-800 text-xs space-y-3">
                    <h3 class="font-bold text-white uppercase tracking-wider text-xs">Pusat Resolusi & Bantuan</h3>
                    <p class="text-slate-300 leading-relaxed">
                        Mengalami kendala pesanan atau indikasi kecurangan transaksi? Tim mediasi RuangLari siap membantu Anda.
                    </p>
                    <div class="pt-1 flex flex-col gap-2">
                        <a href="{{ route('marketplace.orders.index') }}" class="w-full py-2 px-3 rounded-md bg-[#162035] hover:bg-slate-700 text-white font-bold text-center border border-slate-700 transition">
                            Cek Status Pesanan Saya
                        </a>
                        <a href="{{ Route::has('vcard.index') ? route('vcard.index') : url('/card') }}" class="w-full py-2 px-3 rounded-md bg-neon hover:bg-lime-300 text-dark font-black text-center transition">
                            Hubungi Tim Support
                        </a>
                    </div>
                </div>
            </aside>

            <!-- RIGHT COLUMN: DETAILED POLICY CONTENT -->
            <div class="lg:col-span-8 space-y-8">

                <!-- PASAL 1 -->
                <section id="pasal-1-escrow" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">1. Sistem Rekening Bersama (Escrow Protection)</h2>
                        <span class="text-xs font-bold text-neon uppercase">Keamanan Finansial</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            RuangLari memberlakukan sistem Rekening Bersama (Escrow) secara wajib untuk seluruh transaksi di dalam platform Marketplace guna meniadakan risiko penipuan antara pembeli dan penjual:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>
                                <strong class="text-white">Alur Penahanan Dana:</strong> Saat pembeli menyelesaikan pembayaran (via Virtual Account, E-Wallet, atau QRIS Midtrans), dana masuk ke rekening escrow resmi RuangLari. Dana <span class="text-white font-semibold">tidak pernah ditransfer langsung</span> ke rekening pribadi penjual sebelum pesanan selesai.
                            </li>
                            <li>
                                <strong class="text-white">Pelepasan Dana ke Penjual:</strong> Dana hasil penjualan baru diteruskan ke Dompet (Wallet) penjual setelah salah satu kondisi berikut terpenuhi:
                                <ol class="list-decimal pl-5 mt-1 space-y-1 text-slate-300">
                                    <li>Pembeli mengklik tombol <span class="text-white font-semibold">"Konfirmasi Pesanan Selesai"</span> di halaman rincian pesanan.</li>
                                    <li>Waktu konfirmasi otomatis (auto-complete) berakhir, yakni <span class="text-white font-semibold">48 jam kerja</span> sejak status resi logistik dinyatakan <em>"Delivered"</em> oleh pihak ekspedisi, tanpa adanya pengajuan komplain sengketa dari pembeli.</li>
                                </ol>
                            </li>
                            <li>
                                <strong class="text-white">Pencairan Dana (Withdrawal):</strong> Saldo dompet yang telah masuk dapat ditarik oleh penjual ke rekening bank nasional terverifikasi kapan saja pada hari kerja.
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- PASAL 2 -->
                <section id="pasal-2-originalitas" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">2. Jaminan Keaslian Produk & Kebijakan Anti-Barang Palsu</h2>
                        <span class="text-xs font-bold text-neon uppercase">100% Original</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            Sebagai platform terpercaya komunitas pelari, RuangLari memberlakukan kebijakan <strong class="text-white">Nol Toleransi terhadap Barang Tiruan</strong> (Zero-Tolerance Anti-Counterfeit Policy).
                        </p>
                        <div class="p-4 rounded-md bg-[#141C2E] border border-slate-800 space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-neon">Kewajiban Penjual dalam Listing Produk</h4>
                            <ul class="list-disc pl-5 space-y-1.5 text-xs text-slate-300">
                                <li><strong>Sepatu Lari (Running Shoes):</strong> Wajib mencantumkan kondisi riil (BNIB / BNWB / Bekas/Pre-loved), estimasi jarak lari yang sudah ditempuh (<em>mileage</em> km), foto sol bawah (outsole), bantalan insole, dan tag ukuran (size label) yang jelas terbaca.</li>
                                <li><strong>Jam Tangan GPS & Sensor:</strong> Wajib melampirkan foto fungsional saat menyala, persentase kesehatan baterai, fungsi sensor denyut jantung optik, kelengkapan kabel pengisi daya, dan informasi garansi resmi distributor.</li>
                                <li><strong>Larangan Eufemisme Barang Palsu:</strong> Dilarang menggunakan istilah penyamaran seperti "Grade Ori", "Mirror 1:1", "Premium Authentic", "Replika Super", atau "Sisa Pabrik" untuk barang tiruan. Seluruh barang yang dijual wajib produk asli pabrikan resmi.</li>
                            </ul>
                        </div>
                        <p>
                            Jika produk yang diterima pembeli diverifikasi tidak original oleh tim kurator RuangLari atau ahli pihak ketiga yang kompeten, transaksi akan dibatalkan, pembeli berhak menerima <strong class="text-white">pengembalian dana 100%</strong>, dan akun penjual akan dibekukan secara permanen.
                        </p>
                    </div>
                </section>

                <!-- PASAL 3 -->
                <section id="pasal-3-slot-bib" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">3. Ketentuan Khusus Slot Lari & Transfer BIB</h2>
                        <span class="text-xs font-bold text-neon uppercase">Race Slots</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            Kategori slot lomba dan nomor dada lari (BIB) memiliki sensitivitas hukum dan keselamatan medis yang tinggi. Seluruh pihak yang bertransaksi slot lari wajib tunduk pada aturan berikut:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>
                                <strong class="text-white">Kepatuhan Regulasi Event Organizer (EO):</strong> Penjual dan pembeli wajib mematuhi syarat resmi yang ditetapkan oleh penyelenggara event terkait tata cara pindah tangan slot lomba atau perubahan nama peserta resmi.
                            </li>
                            <li>
                                <strong class="text-white">Kewajiban Pengalihan Data Resmi:</strong> Apabila event lomba menyediakan sistem transfer data peserta resmi (re-registrasi), penjual wajib memfasilitasi dan mengonfirmasi penggantian data kepada pembeli hingga tuntas.
                            </li>
                            <li>
                                <strong class="text-white">Bukti Konfirmasi Kepemilikan:</strong> Penjual wajib memiliki bukti konfirmasi pembayaran atau invoice registrasi resmi yang sah dari event tersebut. Dilarang memperjualbelikan slot fiktif atau nomor BIB yang belum terbayar lunas.
                            </li>
                            <li>
                                <strong class="text-white">Larangan Spekulasi Calo Berlebihan:</strong> RuangLari melarang praktik penimbunan slot massal untuk mencari keuntungan sepihak secara tidak wajar (*scalping*) yang merugikan komunitas pelari.
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- PASAL 4 -->
                <section id="pasal-4-lelang" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">4. Aturan Lelang Perlengkapan Lari (Auctions)</h2>
                        <span class="text-xs font-bold text-neon uppercase">Lelang Terbuka</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            Fitur lelang RuangLari dirancang untuk memberikan kesempatan adil bagi pelari mendapatkan perlengkapan lari langka atau edisi terbatas dengan sistem bidding transparan:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>
                                <strong class="text-white">Tawaran Bersifat Mengikat (Binding Bid):</strong> Setiap penawaran (bid) yang dimasukkan pengguna bersifat final dan tidak dapat ditarik kembali secara sepihak.
                            </li>
                            <li>
                                <strong class="text-white">Batas Waktu Pelunasan Pemenang:</strong> Pemenang lelang dengan bid tertinggi saat waktu lelang berakhir wajib melakukan checkout dan pelunasan dalam waktu maksimal <span class="text-white font-semibold">24 jam</span>.
                            </li>
                            <li>
                                <strong class="text-white">Konsekuensi Bid and Run:</strong> Pemenang lelang yang tidak menyelesaikan pembayaran tanpa alasan sah akan dikenakan penalti pembekuan hak mengikuti lelang, penalti reputasi profil, hingga penonaktifan akun.
                            </li>
                            <li>
                                <strong class="text-white">Larangan Bidding Palsu (Shill Bidding):</strong> Penjual dilarang keras menggunakan akun sekunder atau bersekongkol dengan pihak lain untuk menaikkan nilai penawaran produknya secara tidak wajar.
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- PASAL 5 -->
                <section id="pasal-5-konsinyasi" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">5. Layanan Titip Jual & Kurasi (Consignment)</h2>
                        <span class="text-xs font-bold text-neon uppercase">Consignment</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            Bagi pelari yang ingin menjual perlengkapan berkualitas tanpa repot mengelola pengiriman, RuangLari menyediakan program konsinyasi titip jual terpusat:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>
                                <strong class="text-white">Verifikasi Fisik Langsung:</strong> Barang titip jual dikirimkan terlebih dahulu ke fasilitas kurasi RuangLari untuk diverifikasi keaslian, kebersihan, serta fungsi mekanik/elektroniknya oleh kurator spesialis.
                            </li>
                            <li>
                                <strong class="text-white">Label Resmi "Verified by RuangLari":</strong> Produk yang lolos inspeksi fisik akan mendapatkan tanda verifikasi resmi sehingga meningkatkan kepercayaan calon pembeli.
                            </li>
                            <li>
                                <strong class="text-white">Transparansi Komisi:</strong> Biaya jasa kurasi, penyimpanan, dan pengemasan konsinyasi dipotong secara otomatis dari harga jual akhir sesuai kesepakatan tertulis di awal penerimaan barang.
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- PASAL 6 -->
                <section id="pasal-6-pengiriman" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">6. Standar Pengiriman, Resi & Logistik</h2>
                        <span class="text-xs font-bold text-neon uppercase">Logistik & Packing</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            Kecepatan dan keamanan pengemasan adalah kunci kepuasan bertransaksi. Penjual wajib mematuhi standar logistik platform:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>
                                <strong class="text-white">Batas Waktu Pengiriman:</strong> Penjual wajib menyerahkan paket ke pihak ekspedisi logistik dan menginput nomor resi yang valid maksimal <span class="text-white font-semibold">2 x 24 jam hari kerja</span> setelah pembayaran terkonfirmasi.
                            </li>
                            <li>
                                <strong class="text-white">Standar Pengemasan Aman:</strong> Sepatu lari bernilai tinggi wajib dikemas menggunakan lapisan pelindung box (double box / bubble wrap tebal). Jam tangan GPS wajib dibungkus bantalan peredam guncangan agar sensor tidak rusak saat transit ekspedisi.
                            </li>
                            <li>
                                <strong class="text-white">Anjuran Asuransi Pengiriman:</strong> Untuk produk bernilai di atas Rp 1.000.000, penjual dan pembeli sangat disarankan menggunakan opsi asuransi pengiriman ekspedisi guna mengantisipasi risiko kehilangan di pihak kurir.
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- PASAL 7 -->
                <section id="pasal-7-refund" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">7. Kebijakan Retur & Pengembalian Dana (Refund)</h2>
                        <span class="text-xs font-bold text-neon uppercase">Komplain & Retur</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <div class="p-4 rounded-md bg-[#1C1824] border border-red-900/50 text-slate-200 text-xs leading-relaxed">
                            <strong class="text-red-400 block mb-1">SYARAT UTAMA KLAIM KOMPLAIN / RETUR:</strong>
                            Pembeli <span class="text-white font-bold underline">WAJIB merekam video unboxing lengkap tanpa jeda (unbroken video)</span> mulai dari paket masih tersegel rapi di semua sisi hingga produk dikeluarkan dan diperiksa secara detail. Tanpa bukti video unboxing utuh, klaim kerusakan fisik atau ketidaksesuaian isi tidak dapat diproses oleh tim mediasi.
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                            <div class="p-4 rounded-md bg-[#141C2E] border border-slate-800 text-xs space-y-2">
                                <h4 class="font-bold text-neon uppercase">Alasan Retur yang Diterima:</h4>
                                <ul class="list-disc pl-4 space-y-1 text-slate-300">
                                    <li>Produk terbukti tidak orisinil (palsu/KW).</li>
                                    <li>Spesifikasi atau kondisi fisik berbeda nyata dari foto deskripsi penjual (misal: sol robek yang tidak diinfokan).</li>
                                    <li>Kerusakan fungsi sensor elektronik/GPS yang tidak disebutkan penjual.</li>
                                    <li>Penjual salah mengirimkan varian ukuran atau warna.</li>
                                </ul>
                            </div>

                            <div class="p-4 rounded-md bg-[#141C2E] border border-slate-800 text-xs space-y-2">
                                <h4 class="font-bold text-slate-400 uppercase">Alasan Retur yang Ditolak:</h4>
                                <ul class="list-disc pl-4 space-y-1 text-slate-400">
                                    <li>Pembeli salah memilih ukuran (misal: sepatu sempit padahal size yang dikirim sesuai deskripsi).</li>
                                    <li>Pembeli berubah pikiran setelah barang dikirim (<em>change of mind</em>).</li>
                                    <li>Kerusakan akibat kelalaian pemakaian oleh pembeli setelah paket dibuka.</li>
                                    <li>Klaim tanpa rekaman video unboxing utuh.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- PASAL 8 -->
                <section id="pasal-8-sengketa" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">8. Alur Mediasi & Resolusi Sengketa (Dispute Management)</h2>
                        <span class="text-xs font-bold text-neon uppercase">Resolusi Admin</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            RuangLari menyediakan tombol resmi <strong class="text-white">"Ajukan Komplain"</strong> di halaman pesanan pembeli sebelum status pesanan selesai. Alur penyelesaian sengketa berjalan sebagai berikut:
                        </p>
                        <ol class="list-decimal pl-5 space-y-2">
                            <li>
                                <strong class="text-white">Pembekuan Dana Sementara:</strong> Saat komplain diajukan, pelepasan dana otomatis langsung ditangguhkan oleh sistem.
                            </li>
                            <li>
                                <strong class="text-white">Pengumpulan Bukti Kedua Pihak:</strong> Pembeli mengunggah video unboxing dan rincian masalah. Penjual diberikan waktu 24 jam untuk memberikan tanggapan atau bukti foto pengemasan awal.
                            </li>
                            <li>
                                <strong class="text-white">Investigasi & Keputusan Admin:</strong> Tim Admin RuangLari meninjau bukti secara objektif. Putusan mediasi admin bersifat final:
                                <ul class="list-disc pl-5 mt-1 space-y-1 text-xs text-slate-300">
                                    <li>Jika komplain disetujui, pembeli mengirimkan kembali barang ke alamat penjual (dengan nomor resi), dan dana escrow dikembalikan 100% ke pembeli setelah barang tiba.</li>
                                    <li>Jika komplain tidak terbukti atau tidak memenuhi syarat, dana escrow dilepaskan ke penjual.</li>
                                </ul>
                            </li>
                        </ol>
                    </div>
                </section>

                <!-- PASAL 9 -->
                <section id="pasal-9-larangan" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">9. Barang yang Dilarang Diperjualbelikan</h2>
                        <span class="text-xs font-bold text-neon uppercase">Prohibited</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            Untuk menjaga integritas olahraga dan keselamatan komunitas, barang-barang berikut dilarang keras dipasarkan di RuangLari:
                        </p>
                        <ul class="list-disc pl-5 space-y-1.5 text-xs text-slate-300">
                            <li>Barang tiruan/palsu (sepatu KW, jersey bajakan, aksesori replika).</li>
                            <li>Zat doping, steroid anabolik, hormon pertumbuhan, atau substansi yang dilarang oleh World Anti-Doping Agency (WADA).</li>
                            <li>Suplemen dan obat-obatan yang tidak memiliki izin edar resmi BPOM.</li>
                            <li>Slot lari hasil pencurian identitas, peretasan akun, atau nomor BIB curian.</li>
                            <li>Perlengkapan lari bekas yang tidak higienis atau kotor berbau tanpa dibersihkan.</li>
                            <li>Barang hasil tindak pidana pencurian atau penadahan.</li>
                        </ul>
                    </div>
                </section>

                <!-- PASAL 10 -->
                <section id="pasal-10-sanksi" class="p-6 sm:p-7 rounded-lg bg-[#0E1524] border border-slate-800 space-y-4">
                    <div class="pb-3 border-b border-slate-800 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">10. Sanksi, Penalti & Keamanan Akun</h2>
                        <span class="text-xs font-bold text-neon uppercase">Ketentuan Sanksi</span>
                    </div>
                    <div class="space-y-3 text-sm text-slate-300 leading-relaxed">
                        <p>
                            Pelanggaran terhadap kebijakan marketplace ini akan ditindak tegas melalui tahapan sanksi berikut:
                        </p>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>
                                <strong class="text-white">Peringatan Tertulis & Penurunan Rating:</strong> Diberikan pada penjual yang terlambat mengirimkan pesanan tanpa konfirmasi atau membatalkan pesanan sepihak.
                            </li>
                            <li>
                                <strong class="text-white">Pembekuan Saldo Dompet Sementara:</strong> Diterapkan jika penjual sedang dalam proses investigasi laporan barang palsu atau sengketa berat.
                            </li>
                            <li>
                                <strong class="text-white">Pemblokiran Akun Permanen (Banning):</strong> Penjual yang terbukti menipu, menjual barang tiruan, atau melakukan manipulasi lelang akan diblokir permanen beserta daftar hitam (*blacklist*) identitas bank dan nomor telepon.
                            </li>
                            <li>
                                <strong class="text-white">Tindakan Hukum:</strong> RuangLari berhak meneruskan bukti penipuan atau pemalsuan kepada pihak berwajib jika terbukti merugikan konsumen secara materiil.
                            </li>
                        </ul>
                    </div>
                </section>

                <!-- BACK TO MARKETPLACE CTA BAR -->
                <div class="p-6 rounded-lg bg-[#0E1524] border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-white">Siap Berburu Gear Lari Impian?</h3>
                        <p class="text-xs text-slate-400 mt-1">Jelajahi koleksi sepatu, jam GPS, apparel, dan slot lari original dari komunitas.</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('marketplace.index') }}" class="px-5 py-2.5 rounded-md bg-neon hover:bg-lime-300 text-dark font-black text-xs uppercase tracking-wider transition shadow-sm">
                            Buka Marketplace
                        </a>
                        <a href="{{ route('legal', ['tab' => 'terms']) }}" class="px-4 py-2.5 rounded-md bg-[#162035] hover:bg-slate-700 text-white font-bold text-xs transition border border-slate-700">
                            Syarat Umum Platform
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>
</div>
@endsection
