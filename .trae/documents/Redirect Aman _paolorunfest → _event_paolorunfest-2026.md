## Jawaban Singkat
- Cara paling aman adalah membuat **redirect 301 (permanent)** dari `/paolorunfest` ke route existing `events.show` (`/event/paolorunfest-2026`) tanpa mengubah route utama, sehingga semua fungsionalitas (registrasi, participants-list, payment, recovery) tetap mengacu ke URL asli `/event/...`.

## Kenapa Aman
- Saat ini route utama event sudah ada: `GET /event/{slug}` → `PublicEventController@show` ([web.php](file:///c:/laragon/www/ruanglari/routes/web.php#L341-L350)).
- Tidak ada route “catch-all” seperti `/{slug}` yang bisa bentrok, jadi menambah `/paolorunfest` aman.
- Redirect 301 bagus untuk SEO dan konsistensi share link.

## Implementasi yang Diusulkan
- Tambahkan route baru di [web.php](file:///c:/laragon/www/ruanglari/routes/web.php) (di dekat block public event routes), misalnya tepat sebelum/atau sesudah block “Kalender Lari”.
- Gunakan closure yang:
  - Menghasilkan URL tujuan via `route('events.show', 'paolorunfest-2026')` agar selalu mengikuti APP_URL dan konfigurasi Laravel.
  - **Meneruskan query string** (utm, ref, dsb) supaya tracking tidak hilang.
  - Mengembalikan redirect status **301**.

## Verifikasi
- Setelah ditambahkan, cek:
  - `GET /paolorunfest` → 301 ke `/event/paolorunfest-2026`
  - `GET /paolorunfest?utm_source=x` → 301 ke `/event/paolorunfest-2026?utm_source=x`

Jika Anda setuju, saya akan langsung menambahkan 1 route redirect tersebut di `routes/web.php` dengan preserve query string.