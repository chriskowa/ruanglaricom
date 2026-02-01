## Temuan Awal (Dari Kode & Konfigurasi)
- 419 di Laravel umumnya berarti TokenMismatch (CSRF gagal) karena session tidak terbaca/berubah di request POST.
- Di theme paolo-fest, form registrasi memakai `fetch` dan mengirim header `X-CSRF-TOKEN` dari meta tag + form mengandung `@csrf` (jadi token seharusnya tersedia).
- `.env` lokal memakai `SESSION_DRIVER=database`, `APP_URL=http://localhost/ruanglari/public`, dan `APP_NAME=Laravel` sehingga nama cookie session default menjadi `laravel-session` (rawan bentrok dengan project Laravel lain di localhost).
- Form action saat ini memakai `route('events.register.store', ...)` yang menghasilkan URL absolut berdasarkan APP_URL.

## Hipotesis Akar Masalah (Paling Mungkin)
1) Cookie session tidak konsisten/tergantikan (collision) karena cookie name `laravel-session` dipakai banyak project di localhost → session berubah → CSRF token mismatch → 419.
2) Origin/URL mismatch (URL absolut dari APP_URL berbeda dengan host yang sedang dibuka) → cookies tidak ikut terkirim / session berbeda → 419.
3) Session database tidak persisten (config/DB/session table issue) → tiap request session baru → token mismatch.

## Investigasi yang Akan Dilakukan (Read-only + Instrumentasi Aman)
1) Verifikasi di browser (cek Network saat submit):
   - Pastikan `Request Headers` ada `X-CSRF-TOKEN` dan nilainya tidak kosong.
   - Pastikan `FormData` mengandung field `_token`.
   - Pastikan `Cookies` mengandung `laravel-session`.
2) Tambahkan logging server khusus untuk kasus TokenMismatch (tanpa membocorkan token full):
   - Log path, method, referer/origin, ada/tidaknya cookie session, serta hash pendek token (session vs request).
   - Render response JSON 419 yang user-friendly ketika `Accept: application/json`.

## Implementasi Solusi Permanen
1) Isolasi cookie session untuk project ini:
   - Ubah `.env` lokal: `APP_NAME=RuangLari` (atau nama unik) dan/atau set `SESSION_COOKIE=ruanglari_session`.
   - Ini mencegah tabrakan cookie dengan project Laravel lain di localhost.
2) Hilangkan ketergantungan ke URL absolut:
   - Ubah `action` form dan endpoint fetch kupon/registrasi menjadi URL relatif (`route(..., absolute:false)`), agar selalu same-origin dengan host yang sedang dibuka.
3) Hardening request fetch:
   - Set `credentials: 'same-origin'` (atau `include` bila memang perlu) agar cookie session selalu ikut terkirim.
4) Client-side handling untuk 419 tanpa loop:
   - Saat 419: tampilkan pesan “Sesi habis, halaman akan dimuat ulang…”, lakukan reload 1x.
   - Jika masih 419 setelah reload: tampilkan instruksi jelas (hapus cookie site / buka incognito) dan stop retry.

## Backup Sebelum Perbaikan
- Backup file yang akan disentuh: 
  - EventRegistrationController (atau lokasi handler exception yang dipakai)
  - paolo-fest.blade.php
  - .env (hanya lokal) bila perlu

## Pengujian Fungsional
- Submit registrasi normal (tanpa menunggu lama) harus sukses.
- Biarkan tab terbuka > SESSION_LIFETIME, lalu submit:
  - Harus muncul pesan sesi habis + reload otomatis + submit berhasil setelah reload.
- Uji bersamaan dengan membuka project Laravel lain di localhost:
  - Setelah cookie diisolasi, submit registrasi tetap sukses.
- Uji endpoint kupon tetap berfungsi.

## Output/Dokumentasi Perubahan
- Ringkas perubahan per file (apa yang diubah & alasan).
- Catat root-cause final berdasarkan logging TokenMismatch (collision vs origin mismatch vs session persistence).

Jika Anda setuju, saya lanjut implementasi perubahan di file terkait (exception handling + cookie isolation via .env + perbaikan URL relatif + fetch credentials + UX 419).