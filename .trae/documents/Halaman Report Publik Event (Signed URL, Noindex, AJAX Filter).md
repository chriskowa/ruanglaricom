## Tujuan
- Membuat halaman publik `/report/{id-event}` yang menampilkan:
  - Ringkasan laporan: kapasitas/total slot, terjual (paid), pending, sisa slot.
  - Ringkasan kupon terpakai (per kode + jumlah pemakaian).
  - Tabel peserta responsif: nama, email, tanggal registrasi, status pembayaran (dengan filter AJAX).
- Halaman bisa diakses tanpa login, tetapi hanya via link langsung (token/signature sulit ditebak) dan tidak terindeks Google.

## Keamanan Akses (Tanpa Login)
- Mengamankan URL dengan **signed URL** (signature di query string), sehingga hanya link yang dibagikan EO yang valid.
  - Route `/report/{event}` akan memakai middleware `\Illuminate\Routing\Middleware\ValidateSignature::class` (bukan alias) agar kompatibel dengan konfigurasi middleware saat ini (Laravel 11-style di [bootstrap/app.php](file:///c:/laragon/www/ruanglari/bootstrap/app.php)).
  - Signature otomatis “sulit ditebak” dan terikat ke APP_KEY.
- Validasi event:
  - `{id-event}` harus numerik (`whereNumber`) dan event harus `is_active = true` serta `status = published` (atau minimal `is_active=true`, mengikuti kebutuhan “valid dan aktif”).

## Anti-Indexing (Google/Robots)
- Di halaman report, tambahkan meta:
  - `<meta name="robots" content="noindex,nofollow,noarchive">`
- Tambahkan header response:
  - `X-Robots-Tag: noindex, nofollow, noarchive`
- Update [public/robots.txt](file:///c:/laragon/www/ruanglari/public/robots.txt) agar menolak crawling endpoint report:
  - `Disallow: /report/`

## Routing
- Tambahkan route baru di [routes/web.php](file:///c:/laragon/www/ruanglari/routes/web.php):
  - `GET /report/{event}` → controller `PublicEventReportController@show` (nama route mis. `report.show`).
  - Route ini menerima request HTML (render view) dan request AJAX JSON (untuk filter/pagination) lewat deteksi `Accept: application/json` / `expectsJson()`.

## Controller (Query, Validasi, Caching)
- Buat controller baru `app/Http/Controllers/PublicEventReportController.php` (atau nama serupa) dengan 1 endpoint `show(Request $request, $eventId)`:
  - **Load event** + validasi aktif.
  - **Ringkasan report**: reuse [EventReportService](file:///c:/laragon/www/ruanglari/app/Services/EventReportService.php) untuk sold/pending/remaining (TTL 30 detik).
  - **Ringkasan kupon terpakai**:
    - Query transaksi event yang `coupon_id` tidak null, group by `coupon_id`, join ke `coupons` untuk `code`, hitung pemakaian + total diskon.
  - **List participants** (untuk tabel):
    - Query `Participant` join `transaction` (event_id) dan select kolom: `participants.name`, `participants.email`, `participants.created_at`, `transactions.payment_status`.
    - Filter AJAX via query string: `payment_status`, `start_date`, `end_date`, `search` (nama/email), opsional `category_id`.
    - Pagination server-side (mis. 25/50 per page).
  - **Caching participants** untuk dataset besar:
    - `Cache::remember()` 30–60 detik dengan key berbasis `event_id + filters + page` untuk response JSON tabel.
  - Response:
    - Jika request JSON: return `{participants, report, coupons}`.
    - Jika request HTML: render view dengan data awal (report+coupons) dan tabel pertama.
  - Set header `X-Robots-Tag` di response HTML (dan JSON bila diperlukan).

## View (Halaman Report)
- Buat view baru (mis. `resources/views/reports/event.blade.php`) memakai layout publik (rekomendasi `layouts.pacerhub.blade.php`) dan menambahkan meta noindex.
- Konten halaman:
  - Header: nama event + badge status.
  - Card ringkasan: total slot, sold, pending, remaining.
  - Card kupon terpakai: tabel kecil per kode kupon.
  - Filter bar: search, status pembayaran, rentang tanggal.
  - Tabel peserta responsif (`.table-responsive`) dengan kolom yang diminta.

## AJAX Filtering
- JS di halaman report:
  - Submit filter tanpa reload via `fetch()` ke URL yang sama (menjaga signature query string).
  - Render ulang tbody + pagination dari JSON.
  - Support klik pagination (mirip pola di halaman EO participants) namun tanpa butuh login.

## Verifikasi
- Uji akses:
  - URL tanpa signature → 403/404.
  - URL dengan signature valid → halaman terbuka.
- Uji anti-index:
  - Pastikan meta `robots` dan header `X-Robots-Tag` ada.
  - Pastikan robots.txt memuat `Disallow: /report/`.
- Uji filter:
  - Search, payment_status, pagination berjalan via AJAX.
- Uji performa:
  - Pastikan query participants memakai `select` minimal dan eager loading hanya yang perlu.
  - Cache hit mengurangi beban untuk list besar.

## File yang Akan Diubah/Ditambah
- Ubah: [routes/web.php](file:///c:/laragon/www/ruanglari/routes/web.php)
- Ubah: [public/robots.txt](file:///c:/laragon/www/ruanglari/public/robots.txt)
- Tambah: `app/Http/Controllers/PublicEventReportController.php`
- Tambah: `resources/views/reports/event.blade.php`

Jika plan ini disetujui, saya lanjut implementasi end-to-end (routing, controller, view, AJAX, noindex, caching) dan validasi di lokal.