## Gambaran

Target: aplikasi Laravel ini pindah dari lokasi lama ke domain utama `ruanglari.com` dengan cara mengubah Document Root domain di cPanel ke folder `app.ruanglari.com`. Selain `APP_URL`, ada beberapa hal krusial yang biasanya ikut “pecah”: cookie/session domain, HTTPS detection, reCAPTCHA, Google OAuth, Strava OAuth, dan webhook Midtrans.

## 1) Pastikan Struktur Folder (paling penting di cPanel)

1. **Opsi ideal (disarankan):** Document Root diarahkan ke folder **`.../app.ruanglari.com/public`** (Laravel standard). Ini paling aman (tidak expose file selain public).
2. **Kalau Anda terpaksa set DocRoot ke** **`.../app.ruanglari.com`** **(root proyek):**

   * Anda harus menambahkan mekanisme yang mengarahkan semua request ke `public/` (atau memindahkan isi `public/` ke root dan menyesuaikan path di `index.php`).

   * Ini riskan di shared hosting karena bisa mengekspos `.env`, `composer.json`, dll kalau salah konfigurasi.

## 2) Update .env untuk Domain Baru (selain APP\_URL)

1. **APP\_URL** → set ke `https://ruanglari.com` (atau `https://app.ruanglari.com` kalau memang subdomain yang dipakai).

   * Ini mempengaruhi URL generator `route()` dan juga URL storage publik di [filesystems.php](file:///c:/laragon/www/ruanglari/config/filesystems.php#L41-L48).
2. **SESSION\_DOMAIN**

   * Jika hanya satu host (`ruanglari.com` saja): kosongkan atau set `ruanglari.com`.

   * Jika ingin login tetap berlaku di `ruanglari.com` dan `app.ruanglari.com`: set `.ruanglari.com`.

   * Konfig: [session.php](file:///c:/laragon/www/ruanglari/config/session.php#L148-L173)
3. **SESSION\_SECURE\_COOKIE=true** (wajib jika HTTPS).

   * Konfig: [session.php](file:///c:/laragon/www/ruanglari/config/session.php#L161-L173)
4. **APP\_ENV/APP\_DEBUG**

   * Production: `APP_ENV=production`, `APP_DEBUG=false`.
5. **FILESYSTEM**

   * Pastikan `storage:link` bekerja (symlink `public/storage`). Bila shared hosting melarang symlink, perlu fallback (dibahas di langkah verifikasi).

## 3) reCAPTCHA (ya, perlu disesuaikan)

Aplikasi Anda **wajib** captcha untuk login & register (selalu required) di [AuthController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Auth/AuthController.php#L25-L41) dan [AuthController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Auth/AuthController.php#L117-L137).

Step-by-step:

1. Buka Google reCAPTCHA Admin.
2. Pastikan domain **`ruanglari.com`** (dan `app.ruanglari.com` kalau dipakai) ada di allowed domains.
3. Jika tidak bisa menambah domain ke key lama, buat **site key baru**.
4. Update di `.env` server:

   * `RECAPTCHA_SITE_KEY=...`

   * `RECAPTCHA_SECRET_KEY=...`

## 4) Google Login (ya, perlu disesuaikan)

Google OAuth memakai Socialite dan env `GOOGLE_REDIRECT_URI` dari [services.php](file:///c:/laragon/www/ruanglari/config/services.php#L31-L35).

Step-by-step:

1. Di Google Cloud Console → OAuth Client.
2. Tambahkan:

   * **Authorized JavaScript origins**: `https://ruanglari.com` (dan `https://app.ruanglari.com` jika dipakai)

   * **Authorized redirect URIs**: `https://ruanglari.com/auth/google/callback`
3. Update `.env`:

   * `GOOGLE_REDIRECT_URI=https://ruanglari.com/auth/google/callback`
4. Pastikan route callback tetap sama: [web.php](file:///c:/laragon/www/ruanglari/routes/web.php#L340-L357).

## 5) Strava OAuth (perlu disesuaikan di dashboard Strava)

Redirect URI Strava dibangun dari `route()` (jadi ikut `APP_URL`), bukan dari `STRAVA_REDIRECT_URI`.

* Callback runner: `/strava/callback` ([web.php](file:///c:/laragon/www/ruanglari/routes/web.php#L481-L486))

* Callback calendar: `/calendar/strava/callback` ([web.php](file:///c:/laragon/www/ruanglari/routes/web.php#L160-L163))

Step-by-step:

1. Strava Developer Settings → pastikan domain `ruanglari.com` diizinkan.
2. Pastikan redirect/callback yang dipakai aplikasi valid di domain baru:

   * `https://ruanglari.com/strava/callback`

   * `https://ruanglari.com/calendar/strava/callback`

## 6) Webhook Midtrans (perlu update di dashboard Midtrans)

Ada beberapa endpoint webhook yang harus bisa diakses dari domain baru:

* `POST /wallet/topup/callback`

* `POST /events/transactions/webhook`

* `POST /marketplace/webhook`
  Referensi routes: [web.php](file:///c:/laragon/www/ruanglari/routes/web.php#L643-L646)

Step-by-step:

1. Di Midtrans Dashboard, update **Payment Notification URL** ke domain baru.
2. Pastikan endpoint menerima request (cek log Laravel setelah dicoba).

## 7) Audit hardcode domain (opsional tapi disarankan)

Karena Anda pindah ke `ruanglari.com`, ada beberapa tempat yang masih hardcode domain lain:

* OG/Twitter image/url masih `ruanglari.id` di [pacerhub.blade.php](file:///c:/laragon/www/ruanglari/resources/views/layouts/pacerhub.blade.php#L50-L63) dan [coach.blade.php](file:///c:/laragon/www/ruanglari/resources/views/layouts/coach.blade.php#L20-L33).

* Ada call outbound sync event ke WordPress `https://ruanglari.com/wp-json/...` di [EventController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Admin/EventController.php#L227-L233) (ini bukan masalah untuk migrasi domain app, tapi penting kalau sumber WP ikut berubah).

## 8) Setelah Deploy: cache, storage, dan HTTPS

1. Jalankan (via SSH/Terminal cPanel bila ada):

   * `php artisan config:clear && php artisan cache:clear && php artisan view:clear`
2. Pastikan `public/storage` ter-link:

   * `php artisan storage:link`
3. HTTPS:

   * Pastikan SSL aktif dan `APP_URL` pakai `https`.

   * Jika pakai Cloudflare/proxy, mungkin perlu TrustProxies agar Laravel membaca scheme `https` dengan benar (nanti saya bisa bantu implement jika Anda butuh).

## 9) Checklist Verifikasi (quick test)

* Login/Register (reCAPTCHA lolos)

* Google Login (redirect & callback sukses)

* Strava Connect (redirect & callback sukses)

* Upload/read file di `storage` (gambar event, avatar, dsb)

* Payment/Webhook Midtrans masuk (cek log)

Jika Anda setuju, langkah berikutnya saya bisa:

1. buatkan **daftar env yang perlu disiapkan di server**, 2) usulkan perubahan kecil code untuk membuat reCAPTCHA login/register conditional saat dev (opsional), 3) siapkan patch untuk menghilangkan hardcode domain `ruanglari.id` agar semua meta mengikuti `APP_URL`.

