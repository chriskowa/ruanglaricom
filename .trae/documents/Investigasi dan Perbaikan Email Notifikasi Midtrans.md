## Ringkasan Temuan (Berdasarkan Codebase)
- Email notifikasi registrasi **tidak dikirim langsung saat payment sukses**, tetapi melalui **webhook Midtrans** lalu **2 job queue**.
- Test email dari fitur edit event berjalan normal karena itu **langsung `Mail::send()` di request web**, **tanpa queue**.
- Di codebase ini, `QUEUE_CONNECTION` memakai **database** (job masuk tabel `jobs`). Jika **queue worker tidak berjalan**, email tidak akan pernah terkirim.
- Scheduler (cron `schedule:run`) saat ini **tidak menjalankan queue worker**. Kernel hanya menjalankan 3 command lain, tidak memproses queue.
- Email job untuk tiket dimasukkan ke queue bernama **`emails-tickets`**, sehingga worker harus listen queue ini (bukan hanya `default`).
- Ada tabel baru untuk limiter dan delivery log (`event_email_minute_counters`, `event_email_delivery_logs`) yang harus sudah termigrasi di hosting; jika belum, proses dispatch email bisa gagal.

## Alur Lengkap (Payment → Email)
1) Midtrans mengirim notifikasi ke endpoint webhook: `POST /events/transactions/webhook`.
2) Controller memverifikasi signature memakai server key sesuai mode sandbox/production.
3) Jika status `settlement|capture` dan fraud `accept` → transaksi ditandai `paid`.
4) Controller me-dispatch `ProcessPaidEventTransaction` (job queue, queue default).
5) Job ini menjalankan `EventRegistrationEmailDispatcher`.
6) Dispatcher me-dispatch `SendEventRegistrationNotification` ke queue **`emails-tickets`** (dengan optional delay).
7) Job email mengirim email via SMTP dan menyimpan log delivery.

## Hipotesis Root Cause Paling Mungkin
### A) Queue worker tidak berjalan di hosting
- Karena cron yang Anda pasang hanya `schedule:run`, sementara sistem email memakai queue database.
- Dampak: job `ProcessPaidEventTransaction` dan/atau `SendEventRegistrationNotification` menumpuk di tabel `jobs` dan tidak dieksekusi.

### B) Worker berjalan tapi tidak listen queue `emails-tickets`
- Jika worker hanya memproses `default`, maka `ProcessPaidEventTransaction` (default) bisa jalan, tetapi job email tetap menumpuk di `emails-tickets`.

### C) Webhook Midtrans tidak pernah sukses diproses
- Penyebab umum: Notification URL sandbox belum diset, signature invalid (server key salah/tidak sesuai), atau mode mismatch.
- Dampak: transaksi tidak pernah jadi `paid`, job tidak pernah di-dispatch.

### D) Migrasi tabel baru belum dijalankan di hosting
- Jika `event_email_minute_counters` belum ada, limiter bisa error saat reserve.
- Jika `event_email_delivery_logs` belum ada, logging delivery error (tidak selalu menghentikan send, tapi memperjelas kegagalan).

## Rencana Investigasi (Server/Hosting)
1) Verifikasi webhook benar-benar hit dan lolos signature
- Cari di `storage/logs/laravel.log` string seperti:
  - `Event transaction webhook:` (Transaction not found / Invalid signature / Mode mismatch / Server key not configured)
  - `ProcessPaidEventTransaction failed`
  - `Registration email job queued`
  - `SendEventRegistrationNotification failed`
  - `Failed to send email notification`

2) Verifikasi queue backlog
- Cek tabel `jobs`:
  - hitung jumlah job per `queue` (`default` vs `emails-tickets`).
- Cek `failed_jobs` untuk error yang pernah terjadi.

3) Verifikasi scheduler cron benar-benar jalan
- Karena cron output dibuang ke `/dev/null`, kita perlu bukti eksekusi:
  - cek log aplikasi untuk command scheduled yang memang menulis log, atau
  - ubah cron agar menulis ke file log scheduler.

## Rencana Perbaikan (Implementasi)
### 1) Pastikan queue diproses di hosting
Pilih salah satu pendekatan:
- **Opsi Recommended (VPS/managed):** jalankan Supervisor untuk `php artisan queue:work` dengan `--queue=default,emails-tickets`.
- **Opsi Shared Hosting (tanpa supervisor):** tambahkan ke scheduler: jalankan `queue:work --stop-when-empty --queue=default,emails-tickets` setiap menit (tanpa daemon), pakai `withoutOverlapping`.

### 2) Pastikan migrasi terkait email sudah ada di hosting
- Jalankan migrasi untuk tabel:
  - `event_email_minute_counters`
  - `event_email_delivery_logs`

### 3) Tambahkan logging yang eksplisit (agar cepat isolasi masalah)
- Tambah log `info` saat webhook sukses memproses transaksi paid (transaction_id, mode, status).
- Tambah log saat dispatch `ProcessPaidEventTransaction` dan saat dispatch email job.

### 4) Hardening kecil agar sistem tidak “silent fail”
- Jika limiter table tidak tersedia / error reserve, fallback ke `delay=0` (log warning) agar email tetap bisa dikirim.

## Rencana Testing Ulang
1) Sandbox payment end-to-end:
- Lakukan pembayaran sampai status settlement.
- Pastikan webhook menghasilkan log sukses.
- Pastikan transaksi jadi `paid`.
- Pastikan job `ProcessPaidEventTransaction` dieksekusi.
- Pastikan email job dieksekusi dan email terkirim.

2) Uji kondisi worker:
- Matikan worker → pastikan job menumpuk (untuk memastikan observabilitas).
- Nyalakan worker → pastikan backlog habis dan email terkirim.

Jika Anda setuju, saya lanjutkan dengan implementasi perbaikan (scheduler queue worker untuk skenario hosting + logging + fallback limiter) dan menyiapkan checklist verifikasi log/DB di hosting.