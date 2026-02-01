## Akar Masalah yang Ditemukan
- Di environment Anda, aplikasi berjalan di subfolder: `APP_URL=http://localhost/ruanglari/public`.
- Ada beberapa request JS yang memakai URL root-relative (diawali `/`) sehingga browser menembak `http://localhost/...` (tanpa `/ruanglari/public`) dan berujung 404.
- Contoh yang sudah terbukti: fetch peserta di [participants-table.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/partials/participants-table.blade.php#L22) memakai `fetch(`/event/${slug}/participants-list?...`)` → akan 404 bila app tidak di root domain.

## Strategi Solusi
- Jangan ambil `APP_URL` langsung dari env di frontend (JS tidak bisa akses env secara aman). Solusi yang benar: embed nilai base URL dari server ke HTML via Blade, memakai `url('/')` (mengikuti request saat ini).
- Buat variabel global `window.APP_URL` + helper URL builder `window.rlUrl(path)` untuk membangun URL yang kompatibel baik di root domain maupun subfolder.

## Perubahan yang Akan Diimplementasikan
1) Tambah `APP_URL` di sisi JS (theme paolo-fest)
- Di [paolo-fest.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/paolo-fest.blade.php) tambahkan:
  - `<meta name="app-url" content="{{ url('/') }}">`
  - `window.APP_URL = <json dari url('/')>`
  - helper `window.rlUrl = (path) => new URL(pathTanpaLeadingSlash, window.APP_URL + '/').toString()`
  - (Optional) `window.APP_BASE_PATH` jika diperlukan.

2) Perbaiki fetch peserta agar tidak 404
- Update [participants-table.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/partials/participants-table.blade.php):
  - Ganti `fetch(`/event/${props.eventSlug}/participants-list?...`)`
  - Menjadi `fetch(window.rlUrl(`event/${props.eventSlug}/participants-list?${params}`))`
  - Pastikan path yang dipass ke helper tidak diawali `/`.

3) Audit dan rapikan URL fetch lain yang berpotensi 404
- Cari semua `fetch('/...')` atau URL root-relative lain di theme paolo-fest.
- Ubah ke `rlUrl('...')` atau gunakan `route(..., false)` dengan parameter yang benar.

## Pengujian Fungsional
- Buka halaman event di localhost (subfolder), pastikan:
  - Daftar peserta (participants list) berhasil load (tidak 404).
  - Apply promo code tidak 404.
  - Submit registrasi / lanjut pembayaran tidak terkena “not found url”.
- Jika masih ada 404, gunakan Network tab untuk melihat Request URL yang ditembak dan bandingkan dengan base `window.APP_URL`.

## Output/Dokumentasi
- Catat root cause: URL root-relative tidak kompatibel dengan app yang berjalan di subfolder.
- Catat solusi permanen: standardisasi pembentukan URL via `APP_URL` + helper, dan hindari hardcoded `fetch('/...')`.

Jika Anda setuju, saya akan implement perubahan ini di 2 file tersebut dan lakukan pengecekan cepat via route list + hard refresh (view clear) agar hasilnya langsung terlihat.