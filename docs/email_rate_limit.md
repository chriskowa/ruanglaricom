# Rate Limiting Email Notification (Instant)

## Ringkasan
- Mode “Instant Email Notification” pada event dibatasi **maks 5 email per menit per event**.
- Pembatasan dilakukan berdasarkan **jumlah email unik** yang dikirim per transaksi (PIC + peserta).

## Queue Prioritas
Untuk menjaga email ticket pendaftar tetap menjadi prioritas utama, sistem memakai queue berbeda:
- `emails-tickets` (prioritas utama)
- `emails-blast` (prioritas rendah)
- `emails-reports` (laporan EO)

Rekomendasi worker:
- Jalankan worker dengan urutan queue: `emails-tickets,default,emails-reports,emails-blast`

## Storage Counter
Reservasi rate limit disimpan di tabel:
- `event_email_minute_counters` (per event, per menit) — kolom `reserved_emails`.

## Perilaku Saat Limit Tercapai
- Email tidak dibuang.
- Job email ticket akan di-**delay** sampai slot menit berikutnya tersedia.

## Logging
Setiap pengiriman email/WA ticket akan dicatat ke tabel:
- `event_email_delivery_logs` (`sent|failed`, channel `email|whatsapp`)

## Monitoring
Halaman admin:
- `/admin/email-monitoring`

