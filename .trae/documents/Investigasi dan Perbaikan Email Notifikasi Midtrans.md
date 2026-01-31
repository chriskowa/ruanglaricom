## Diagnosis (berdasarkan pesan Midtrans + codebase)
- Email dari Midtrans menyatakan mereka **gagal mengirim notifikasi** ke endpoint yang Anda set: `https://ruanglari.com/marketplace/webhook` dengan error **URL not found (404)**.
- Di codebase, email tiket hanya dikirim setelah **webhook Midtrans** berhasil diproses, karena webhook yang me-dispatch job queue untuk proses paid + email.
- Untuk transaksi order id `EVENT-SBX-...`, endpoint yang sesuai di aplikasi adalah **`POST /events/transactions/webhook`**: [web.php:L794-L800](file:///c:/laragon/www/ruanglari/routes/web.php#L794-L800).

## Dampak ke kronologi Anda
- “Status pembayaran paid” bisa terjadi karena redirect/cek manual atau update lain, tapi **tanpa webhook sukses**, job `ProcessPaidEventTransaction` tidak terpicu secara konsisten → **email tiket tidak pernah terkirim**, meskipun fitur “send test email” cepat (karena test email berjalan langsung di request web, bukan lewat queue).

## Rencana Investigasi Cepat (untuk memastikan akar masalah)
1) Pastikan endpoint yang dipakai Midtrans benar-benar hidup di production
- Uji response 200 untuk:
  - `POST https://ruanglari.com/events/transactions/webhook`
  - `POST https://ruanglari.com/marketplace/webhook`
- Tujuan: pastikan tidak ada reverse-proxy/WAF/routing yang membuat POST 404.

2) Pastikan notifikasi Midtrans mengarah ke endpoint yang tepat
- Karena order id Anda prefix `EVENT-SBX-`, set **Payment Notification URL** ke endpoint yang bisa menangani **event**.

## Rencana Perbaikan (pilih salah satu strategi, saya implementasikan yang paling aman)
### Strategi A (paling simpel): ubah Notification URL Midtrans
- Set Payment Notification URL Midtrans (sandbox) ke:
  - `https://ruanglari.com/events/transactions/webhook`
- Catatan: jika akun Midtrans yang sama juga dipakai untuk wallet/membership/marketplace, strategi ini membuat webhook non-event tidak ter-handle.

### Strategi B (recommended): buat 1 endpoint webhook “router” untuk semua order_id
- Tambah endpoint baru misalnya `POST /midtrans/webhook`.
- Endpoint ini mendeteksi prefix `order_id`:
  - `EVENT-...` → forward ke [EventTransactionWebhookController](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EventTransactionWebhookController.php)
  - `...` lainnya (wallet/membership/marketplace) → forward ke controller masing-masing.
- Setelah itu, set Payment Notification URL Midtrans ke:
  - `https://ruanglari.com/midtrans/webhook`
- Keuntungan: satu URL untuk semua produk; tidak perlu gonta-ganti saat ada fitur baru.

## Perbaikan Tambahan yang Berpotensi Menghambat Email (sekunder tapi penting)
- Setelah webhook sudah masuk, flow akan menjalankan job `ProcessPaidEventTransaction`.
- Di job ini ada potensi gagal karena menulis `wallet_transactions.type = 'platform_fee_income'` yang **tidak ada di enum migration**:
  - Job: [ProcessPaidEventTransaction.php:L148-L173](file:///c:/laragon/www/ruanglari/app/Jobs/ProcessPaidEventTransaction.php#L148-L173)
  - Enum: [2025_11_25_103816_create_wallet_transactions_table.php:L14-L28](file:///c:/laragon/www/ruanglari/database/migrations/2025_11_25_103816_create_wallet_transactions_table.php#L14-L28)
- Rencana fix: ubah type tersebut ke nilai enum yang valid (mis. `fee`) agar job tidak failed dan email bisa jalan.

## Verifikasi Setelah Perbaikan
- Lakukan 1 transaksi sandbox sampai `capture/settlement`.
- Pastikan:
  - tidak ada lagi email Midtrans “URL not found”
  - `storage/logs/laravel.log` mencatat webhook event sukses
  - `failed_jobs` kosong untuk `ProcessPaidEventTransaction`
  - job email muncul di queue `emails-tickets` dan terkirim (cek `event_email_delivery_logs`).

Jika Anda setuju, saya lanjutkan implementasi Strategi B (endpoint router) + fix enum `platform_fee_income` → `fee`, lalu saya siapkan checklist uji end-to-end.