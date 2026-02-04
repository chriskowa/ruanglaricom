## Keputusan
Pakai **Opsi A (staging table `event_submissions`)**: submit publik tidak langsung masuk `events`, tetapi masuk antrian moderasi, lalu admin approve baru dipindah ke `events`.

## Alur Fitur (User Publik)
1. Di [/jadwal-lari](file:///c:/laragon/www/ruanglari/resources/views/events/landing.blade.php) tambahkan tombol **Submit Event Lari**.
2. Klik tombol → modal form input event + reCAPTCHA.
3. Step OTP:
   - Klik **Kirim OTP** → OTP dikirim ke email (wajib lulus reCAPTCHA + throttle).
   - Input OTP → klik **Submit** → server validasi OTP + captcha + data → simpan ke `event_submissions` status `pending`.
4. UI tampil pesan sukses.

## Alur Admin (Moderasi + Notifikasi)
1. Saat submission baru masuk, sistem membuat **Notification** untuk seluruh user role `admin`.
   - `type`: mis. `system`
   - `title`: “Submit Event Lari Baru”
   - `message`: ringkas (nama event, kota, tanggal)
   - `reference_type`: `EventSubmission`
   - `reference_id`: id submission
2. Badge/bell Notifications di nav admin otomatis menyala karena nav sudah polling endpoint [notifications.unread](file:///c:/laragon/www/ruanglari/app/Http/Controllers/NotificationController.php#L19-L42).
3. Di Admin panel ditambah halaman:
   - list pending submissions
   - detail submission
   - aksi approve/reject
4. Klik notification akan diarahkan ke halaman detail submission (perlu menambah mapping URL untuk `reference_type === 'EventSubmission'`).

## Database
Tambahkan tabel baru:
- `event_submissions`: menyimpan data event + kontak pengaju + status moderasi + audit IP/UA hash.
- `event_submission_otps`: menyimpan OTP berbasis email (hash code, expiry, used_at, attempts, ip/ua hash).

## Endpoint Public
- `POST /jadwal-lari/submissions/request-otp`
  - Validasi: email + reCAPTCHA + honeypot + throttle
  - Simpan OTP hash + expiry
  - Kirim email OTP
- `POST /jadwal-lari/submissions`
  - Validasi: seluruh field form + reCAPTCHA
  - Verifikasi OTP (hash compare, expiry, max attempts, one-time)
  - Simpan ke `event_submissions` status `pending`
  - Buat notifikasi untuk admin

## Hardening “Super Aman”
- reCAPTCHA server-side (pakai env `RECAPTCHA_SITE_KEY/RECAPTCHA_SECRET_KEY` yang sudah ada di project).
- Throttle berlapis (per IP dan per email) untuk OTP dan submit.
- OTP disimpan **hash** (bukan plaintext), expiry singkat (mis. 10 menit), max attempts (mis. 5), lockout sementara.
- Honeypot + minimal time-to-submit.
- Dedupe fingerprint untuk mencegah double submit.
- Tidak ada upload file publik (minim attack surface) — poster cukup URL.

## Perubahan File (inti)
- Edit:
  - `resources/views/events/landing.blade.php` (tombol + modal + JS fetch + load recaptcha api.js)
  - `routes/web.php` (route public submission + route admin moderation)
  - `resources/views/layouts/components/header-scripts.blade.php` (fungsi `getNotificationUrl()` agar `EventSubmission` mengarah ke halaman admin detail)
  - `resources/views/notifications/index.blade.php` (link notification untuk `EventSubmission`)
- Tambah:
  - migration `event_submissions` dan `event_submission_otps`
  - model `EventSubmission` (+ scopes)
  - controller public submission
  - controller admin submission
  - mailable OTP

## Verifikasi
- Feature tests: OTP request, OTP verify+submit, throttle, notifikasi admin tercipta, approve membuat record `events`.
- Manual test: submit dari /jadwal-lari → lihat bell notifications admin → buka detail → approve.

Jika plan ini sudah sesuai, saya lanjut implementasinya end-to-end.