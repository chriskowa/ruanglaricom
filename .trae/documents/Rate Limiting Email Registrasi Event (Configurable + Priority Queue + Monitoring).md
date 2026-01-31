## Ringkasan
- Menambahkan sistem **pelacakan status pengiriman “email laporan” untuk EO** (pending/terkirim/gagal), lengkap dengan dashboard real-time, notifikasi gagal >5 menit, fitur kirim ulang manual, dan riwayat terfilter.
- Karena saat ini codebase **belum punya konsep “email laporan EO” khusus**, implementasi dibuat sebagai modul email “report” yang bisa dipicu EO (manual) dan siap diperluas untuk otomatis/scheduled.

## Data  Storage (Database)
- Tambahkan tabel baru `eo_report_email_deliveries` (atau nama sepadan) untuk mencatat setiap pengiriman email laporan per recipient:
  - Identitas: `id`, `event_id`, `eo_user_id`, `triggered_by_user_id`
  - Penerima: `to_email`, `to_name` (opsional)
  - Konten/parameter laporan: `filters` (JSON: tanggal, kategori, dsb), `subject`, `report_type`
  - Status  audit: `status` (pending/processing/sent/failed/bounced), `attempts`, `first_attempt_at`, `last_attempt_at`, `sent_at`
  - Error detail: `failure_code` (invalid_email/server_error/transport_error/bounce/unknown), `failure_message`, `provider_message_id` (opsional)
  - Flag monitoring: `failure_notified_at` (untuk mencegah notif berulang)

## Pengiriman Email  Update Status
- Buat Job `SendEoReportEmail`:
  - Saat mulai: set `status=processing`, update `attempts`, `first_attempt_at`/`last_attempt_at`
  - Lakukan `Mail::to(...)->send(new EoReportMail(...))`
  - Jika sukses: set `status=sent`, isi `sent_at`
  - Jika exception: set `status=failed`, isi `failure_code` + `failure_message`
- Klasifikasi alasan gagal (minimal yang bisa dideteksi dari exception):
  - `invalid_email` (format/Address parsing)
  - `transport_error` (koneksi SMTP/timeout)
  - `server_error` (5xx atau error provider)
  - `unknown`
- `bounce` disiapkan sebagai status/field (karena bounce biasanya butuh webhook/ESP event). Nanti bisa dihubungkan jika ada integrasi provider.

## Dashboard Real-time (EO)
- Tambah halaman EO “Email Laporan”:
  - Tabel riwayat delivery dengan indikator visual (badge): pending/terkirim/gagal
  - Filter: rentang tanggal, event, status
  - Auto-refresh via polling endpoint JSON (mis. tiap 5–10 detik) agar real-time tanpa Horizon
- Akses dibatasi: EO hanya melihat delivery untuk event miliknya.

## Kirim Ulang Manual (EO)
- Tombol aksi “Kirim Ulang Manual” per baris:
  - Memunculkan dialog konfirmasi
  - Validasi email sebelum enqueue ulang (Laravel validation + normalisasi sederhana)
  - Membuat attempt baru (atau membuat record baru ter-link) lalu dispatch job ulang

## Notifikasi Otomatis Jika Gagal > 5 Menit
- Implementasi monitoring yang tidak butuh restart:
  - Buat Command terjadwal (scheduler) yang berjalan **setiap menit**:
    - Cari delivery `status=failed/pending` dengan `first_attempt_at <= now()-5min` dan `failure_notified_at is null`
    - Buat notifikasi ke EO (dan opsional admin) di tabel `notifications`
    - Set `failure_notified_at` agar tidak spam
  - Alternatif (opsional): dispatch job “check status” dengan `delay(5 minutes)` saat attempt pertama dibuat.

## Endpoint API  Dokumentasi
- Sediakan endpoint JSON untuk integrasi internal:
  - `GET /eo/email-reports` (HTML) + `GET /eo/email-reports/data` (JSON list + filter)
  - `POST /eo/email-reports/send` (buat delivery baru + enqueue)
  - `POST /eo/email-reports/{id}/resend` (validasi + enqueue)
- Tambahkan dokumentasi API singkat (format markdown) di folder `docs/` mengikuti gaya dokumen existing.

## Logging
- Tambahkan log terstruktur pada:
  - Enqueue: event_id, delivery_id, to_email, filters, queue
  - Job execution: duration, status akhir, failure_code, failure_message ringkas

## Unit Testing
- Test sukses:
  - Buat delivery, jalankan job dengan `Mail::fake()`, pastikan status berubah ke `sent` dan `sent_at` terisi
- Test gagal:
  - Mock `Mail` agar melempar exception transport/invalid address, pastikan status `failed` dan `failure_code` benar
- Test notifikasi 5 menit:
  - Set `first_attempt_at` mundur >5 menit, jalankan command, pastikan notif dibuat dan `failure_notified_at` terisi

## Integrasi “Email Laporan” (Konten Email)
- Fase awal: email berisi ringkasan + link ke halaman laporan EO (mis. participants/report card) + parameter filter.
- Opsional lanjutan: attach CSV/PDF export dari service report yang sudah ada (tanpa mengubah UI utama).
