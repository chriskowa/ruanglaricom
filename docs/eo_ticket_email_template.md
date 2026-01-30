# Panduan EO: Template Tiket Email (QR Code)

## Ringkasan

EO bisa memilih format email tiket apakah menyertakan **QR Code** atau tidak.

Opsi ini tersimpan di event dan dipakai saat sistem mengirim email tiket setelah pembayaran (paid/COD).

## UI

- **Create Event**: ada radio button "Template Tiket Email"
  - "Gunakan QR Code"
  - "Tanpa QR Code"
- **Edit Event**: radio button yang sama, berada dekat "Custom Email Message (Ticket)"

## Backend

- Field baru di tabel `events`: `ticket_email_use_qr` (boolean, default `true`).
- Disimpan oleh `EO\EventController@store` dan `EO\EventController@update`.
- Preview email (`EO\EventController@previewEmail`) mengikuti nilai radio yang dipilih.

## Email Ticket

- Template email: `resources/views/emails/events/registration-success.blade.php`.
- Jika `ticket_email_use_qr = true` → blok QR Code tampil.
- Jika `ticket_email_use_qr = false` → QR Code disembunyikan, nomor tiket tetap tampil.

## Validasi & Error Handling

- Server-side: `ticket_email_use_qr` divalidasi sebagai boolean.
- Client-side: form akan menampilkan error jika belum memilih salah satu opsi.
- Backward compatibility: jika field belum ada/NULL, sistem menganggap default **true**.

