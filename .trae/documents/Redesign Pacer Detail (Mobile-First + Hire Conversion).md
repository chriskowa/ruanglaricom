## Tujuan
- Membuat halaman pacer detail lebih menarik (mobile-first), lebih jelas value-nya, dan meningkatkan conversion untuk “hire” (chat/booking).

## Kondisi Saat Ini (Temuan)
- Halaman detail aktif: [profile.blade.php](file:///c:/laragon/www/ruanglari/resources/views/pacer/profile.blade.php) via route `/pacer/{slug}` ([web.php](file:///c:/laragon/www/ruanglari/routes/web.php#L165-L172)).
- Belum ada fitur booking/hire resmi di backend (hanya tombol sosial/WhatsApp; bahkan social/WA disembunyikan untuk guest) sehingga funnel hire belum optimal.

## Perubahan UI/UX (Mobile-First)
1. **Hero yang “jualan” (Above-the-fold)**
   - Ringkas, fokus: Foto + nama + verified + spesialis (category) + lokasi.
   - Tambah “Key Highlights” 3 kartu kecil: Pace, Total Races, PB relevan (yang sesuai kategori) agar langsung meyakinkan.
   - Gunakan layout 1 kolom untuk mobile, dengan spacing dan tap-target besar.

2. **CTA Conversion yang jelas (Sticky CTA mobile)**
   - Tambah sticky bottom bar di mobile:
     - Primary: “Hire Pacer”
     - Secondary: “Chat WhatsApp” / “Request via Form” (tergantung strategi privacy)
     - Tertiary: Share
   - Untuk desktop: CTA diletakkan di kanan (sidebar) dalam card “Hire this Pacer”.

3. **Struktur konten yang lebih “scan-able”**
   - Ubah section menjadi gaya cards + accordion:
     - About (collapsible)
     - Experience / Race Portfolio (chips tetap, tapi tambahkan tampilan list/timeline jika panjang)
     - Gallery (tetap horizontal, tambah full-screen viewer)
     - Availability/Preferred race type (jika datanya belum ada: tampilkan “Available by request”)

4. **Trust Signals**
   - Badge verified lebih menonjol + microcopy “Verified by RuangLari”.
   - Tambahkan “What you get” (bullet singkat): pace guidance, race strategy, warmup, hydration reminders.
   - Jika belum ada review/rating: tampilkan placeholder “Reviews coming soon” (tanpa angka palsu).

5. **Share yang lebih benar + rapi**
   - Fix QR yang saat ini mengarah ke `runner.profile.show` agar mengarah ke URL pacer detail (`pacer.show`).
   - Copy link + share sheet tetap, tapi desain tombolnya dibuat lebih konsisten.

## Funnel “Hire” (Agar orang benar-benar bisa meng-hire)
Pilihan implementasi (saya implement yang paling aman untuk privacy dan conversion):
1. **In-page Hire Modal (Recommended)**
   - Tombol “Hire Pacer” membuka modal form singkat:
     - Event/race name
     - Date
     - Distance
     - Target pace
     - Meeting point
     - Notes
     - Contact user (nama + WA/email)
   - Output:
     - Untuk pacer yang punya WhatsApp: generate `wa.me` message prefilled (langsung chat).
     - Opsional: simpan lead ke database (pacer bisa lihat daftar request di dashboard/EO/admin).

2. **Guest handling**
   - Guest tetap boleh klik “Hire Pacer” (jangan diblur total), lalu:
     - Opsi A: isi form minimal + lanjut WhatsApp
     - Opsi B: diarahkan login jika ingin “save request”

## Implementasi Teknis (File yang disentuh)
- Redesign UI utama: [profile.blade.php](file:///c:/laragon/www/ruanglari/resources/views/pacer/profile.blade.php)
- Jika perlu komponen reusable (CTA bar / modal): buat partial di `resources/views/layouts/components/`.
- Jika memilih “save lead” ke DB: tambah migration + model baru (mis. `PacerHireRequest`) + controller + routes.

## Verifikasi
- Uji responsif (320px–430px) dan desktop.
- Uji guest vs auth state (CTA tetap berfungsi).
- Uji share (Web Share API fallback) + QR mengarah benar.
- Uji performa: gambar lazy-load, layout tidak “lompat”.

Jika kamu setuju, aku lanjut implement redesign + funnel “Hire Pacer” (modal + WA prefilled, dan opsional penyimpanan request).