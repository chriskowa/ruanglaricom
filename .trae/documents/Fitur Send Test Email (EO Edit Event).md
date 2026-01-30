## Tujuan
Menambahkan fitur **“Send Test Email”** pada halaman **EO → Edit Event** di bagian **Custom Email Message (Ticket)** agar EO bisa mengirim email percobaan tanpa menyimpan perubahan terlebih dahulu.

## Kondisi Sistem Saat Ini (Temuan)
- Field custom ticket email sudah ada dan diedit via CKEditor: `custom_email_message` di [edit.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/edit.blade.php#L325-L339).
- Sudah ada fitur **Preview Email** yang membuka tab baru dan merender template ticket email dengan data mock: [EventController@previewEmail](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php#L1060-L1103).
- Template email aktual yang dipakai produksi adalah [registration-success.blade.php](file:///c:/laragon/www/ruanglari/resources/views/emails/events/registration-success.blade.php) dengan variabel utama: `$event`, `$participants`, `$transaction`, `$notifiableName`.

## Rancangan Solusi
### 1) Endpoint backend untuk kirim test email
- Tambah route baru di group EO:
  - `POST /eo/events/{event}/send-test-email` (nama: `eo.events.send-test-email`).
- Tambah method baru di [EO\EventController](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php): `sendTestEmail(Request $request, Event $event)`.
- Validasi input:
  - `test_email` wajib, format email valid.
  - `custom_email_message` (string) + `ticket_email_use_qr` + `name` untuk preview (tanpa simpan DB).
- Data representatif:
  - Reuse pola `previewEmail`: buat mock `Participant`, `RaceCategory`, `Transaction` dengan field-field yang dipakai template (name/email/phone/bib_number/id/transaction_id, final_amount, payment_status, dll).
  - Set `event->custom_email_message` dari request agar hasil sama seperti yang sedang diedit.
- Kirim email menggunakan Mailable yang sama dengan produksi: [EventRegistrationSuccess](file:///c:/laragon/www/ruanglari/app/Mail/EventRegistrationSuccess.php).

### 2) Rate limiting 3x per session
- Implement rate limit **berbasis session** di backend (bukan global throttle) dengan key seperti:
  - `eo:test-email:{event_id}`
- Jika sudah 3 kali, return `429` dengan pesan jelas.
- Response JSON juga mengembalikan `remaining` agar UI bisa menampilkan sisa kuota.

### 3) UI/UX di halaman edit event
- Di bawah editor `Custom Email Message (Ticket)` tambahkan:
  - Input email tujuan test (`type=email`).
  - Tombol **Send Test Email**.
  - Area status (loading/success/error) yang jelas.
- Validasi format email di client sebelum request.
- Saat mengirim:
  - Tombol disabled + teks “Sending…”
  - Setelah selesai: tampil pesan sukses atau error.

### 4) Integrasi dengan editor tanpa save
- JS mengambil nilai terkini:
  - `document.querySelector('#custom_email_message').value`
  - `ticket_email_use_qr` yang dipilih
  - `name` event
- Kirim via `fetch` ke endpoint test email (AJAX) dengan CSRF.

### 5) Testing
- Tambah feature test:
  - Email terkirim saat input valid (pakai `Mail::fake()` dan `assertSent`).
  - Rate limit: request ke-4 dalam session yang sama return `429`.
  - Validasi email: format invalid return `422`.

## File yang Akan Diubah/Ditambah
- Update: [routes/web.php](file:///c:/laragon/www/ruanglari/routes/web.php) (route EO send-test-email)
- Update: [EventController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php) (method `sendTestEmail`)
- Update: [edit.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/edit.blade.php) (UI + JS send test email)
- Add: `tests/Feature/EoEventSendTestEmailTest.php`

## Output yang Diharapkan
- Tombol “Send Test Email” berfungsi tanpa save.
- Email test tampak identik/representatif dengan email produksi (template sama, variabel sama).
- User mendapat feedback loading/sukses/gagal.
- Rate limit efektif: max 3 kali per session.