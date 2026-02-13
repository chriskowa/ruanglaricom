## Tujuan
- Buat mekanisme reminder untuk transaksi berstatus `pending` lebih dari 1 hari agar EO bisa:
  - Kirim reminder manual (lebih interaktif) dari halaman peserta.
  - Kirim reminder otomatis terjadwal via job/command.

## Kondisi & Data yang Dipakai
- Basis data status pembayaran: `transactions.payment_status` (pending/paid/failed/expired/cod) dari tabel `transactions`.
- Ambang “lebih dari 1 hari”: `transactions.created_at <= now() - 24 jam` (hanya jika masih `pending`).
- CTA utama: arahkan ke halaman “Lanjutkan Pembayaran” `route('events.payments.continue', $event->slug)` (sudah ada di [web.php](file:///c:/laragon/www/ruanglari/routes/web.php#L451)).

## Perubahan Database (Tracking agar tidak spam)
- Tambah kolom pada `transactions`:
  - `pending_reminder_last_sent_at` (datetime, nullable)
  - `pending_reminder_count` (unsigned int, default 0)
  - opsional: `pending_reminder_last_channel` (string, nullable)
- Tambah kolom `context` (atau `purpose`) pada `event_email_delivery_logs` supaya log bisa membedakan “registration_success” vs “pending_payment_reminder” (tabel log ini sudah dipakai di [SendEventRegistrationNotification](file:///c:/laragon/www/ruanglari/app/Jobs/SendEventRegistrationNotification.php)).

## Backend: Pengiriman Reminder
- Buat Job baru `SendPendingPaymentReminder` (ShouldQueue):
  - Input: `transaction_id`, `channels` (email/whatsapp), `triggered_by` (manual/auto), opsional `note`.
  - Di `handle()`:
    - Reload transaction + relasi (`event`, `participants`, `coupon`).
    - Guard: kalau status sudah bukan `pending` → stop.
    - Guard: kalau `pending_reminder_last_sent_at` masih < 24 jam (atau count sudah max) → stop.
    - Build konten pesan:
      - Event name, ID registrasi (`public_ref`/id), nominal, cara bayar, link “lanjutkan pembayaran”, opsi “registrasi ulang” + info support.
    - Kirim email (Mailable baru) ke PIC email (`transaction.pic_data.email`) jika ada.
    - Kirim WhatsApp via helper [WhatsApp.php](file:///c:/laragon/www/ruanglari/app/Helpers/WhatsApp.php) jika env tersedia dan PIC phone ada.
    - Update kolom tracking reminder di `transactions`.
    - Catat ke `event_email_delivery_logs` dengan `context='pending_payment_reminder'`.

## Mailable (Email Template)
- Tambah Mailable `PendingPaymentReminder` (mirip struktur `EventRegistrationSuccess`):
  - CTA button menuju `events.payments.continue`.
  - Sertakan informasi kontak support dari `event.organizer_contact` / `event.whatsapp_config` (fallback bila kosong).

## Endpoint Manual untuk EO
- Tambah route POST baru di grup EO:
  - contoh: `/eo/events/{event}/transactions/{transaction}/remind-pending`
  - authorize: pastikan transaction milik event dan user EO berhak (pola otorisasi sama seperti [EventController::participants](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php#L769-L950)).
- Controller method:
  - Validasi channel dipilih minimal 1.
  - Dispatch `SendPendingPaymentReminder`.
  - Return JSON sukses + timestamp (untuk update UI tanpa reload).

## UI di /eo/events/{id}/participants (Interaktif)
- Update [participants.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/participants.blade.php):
  - Tampilkan indikator “Pending > 1 hari” pada baris jika `status=pending` dan `transaction_date` melewati threshold.
  - Tambah tombol aksi “Kirim Reminder” pada kolom Actions (hanya untuk pending overdue).
  - Saat klik:
    - Modal pilih channel (Email/WhatsApp) + optional catatan.
    - Submit via fetch ke endpoint EO.
    - Setelah sukses, tampilkan toast dan update label “Last reminder” di baris.
  - (Opsional cepat) Tambah link WhatsApp prefilled message sebagai fallback jika helper WhatsApp tidak tersedia.

## Otomatis (Job Terjadwal)
- Buat Artisan Command `payments:remind-pending`:
  - Query transactions pending overdue.
  - Skip yang sudah di-remind dalam 24 jam / sudah cap max reminder.
  - Dispatch job per transaction.
- Tambahkan schedule di [Kernel.php](file:///c:/laragon/www/ruanglari/app/Console/Kernel.php) (mis. daily), dengan tanpa tumpang tindih.

## Verifikasi
- Tambah test sederhana/feature test untuk:
  - Endpoint EO hanya bisa untuk transaction milik event.
  - Job tidak mengirim jika status berubah jadi paid.
  - Throttling reminder (tidak dobel dalam 24 jam).
- Uji manual di halaman EO participants:
  - Filter `payment_status=pending` dan pastikan tombol reminder muncul untuk transaksi lama.

## Catatan Implementasi
- Reminder idealnya per-`transaction` (registrasi) bukan per-participant, karena PIC data dan pembayaran ada di transaksi.
- Untuk WhatsApp otomatis: jika env `WHATSAPP_APPKEY/AUTHKEY` kosong, sistem tetap bisa mengirim email dan menyediakan WA prefilled link.