## Tujuan
- Mengaktifkan kembali fitur Connect Strava di halaman Calendar/Strava.
- Membatasi agar hanya user yang sudah login/terdaftar yang bisa melakukan connect dan memakai data Strava.

## Temuan Saat Ini
- Route Strava connect & callback ada di [routes/web.php](file:///c:/laragon/www/ruanglari/routes/web.php) dan masih publik.
- UI di [calendar/index.blade.php](file:///c:/laragon/www/ruanglari/resources/views/calendar/index.blade.php) menganggap “connected” jika `localStorage.strava_access_token` ada (tanpa cek login).
- Callback view [calendar/strava-callback.blade.php](file:///c:/laragon/www/ruanglari/resources/views/calendar/strava-callback.blade.php) selalu menyimpan token ke localStorage.
- Backend hanya menyimpan token ke DB jika `auth()->check()` (di [CalendarController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/CalendarController.php)).

## Perubahan yang Akan Dilakukan
### 1) Gate route Strava dengan auth
- Pindahkan / bungkus route berikut dengan middleware `auth`:
  - `GET /calendar/strava/connect` (calendar.strava.connect)
  - `GET /calendar/strava/callback` (calendar.strava.callback)
- Efek: user yang belum login akan diarahkan ke halaman login dulu, lalu setelah login akan kembali ke connect flow.

### 2) Perbaiki UI: kalau belum login, tampilkan CTA Login/Register
- Ubah section `!isStravaConnected` di [calendar/index.blade.php](file:///c:/laragon/www/ruanglari/resources/views/calendar/index.blade.php) agar:
  - Jika guest: tampilkan tombol “Login untuk Connect” + “Daftar”.
  - Jika sudah login: tampilkan tombol “Connect with Strava” seperti sekarang.

### 3) Perbaiki logic JS: token hanya dianggap aktif kalau user login
- Tambahkan flag `isAuthenticated` dari Blade (`auth()->check()`) ke Vue state.
- Saat `mounted()`, set `isStravaConnected` hanya jika `isAuthenticated === true` dan token ada.
- Jika guest tapi token ada di localStorage: jangan dianggap connected (token boleh dibiarkan tersimpan, tapi tidak dipakai sampai user login).

### 4) (Opsional tapi disarankan) Perketat callback view
- Pastikan [calendar/strava-callback.blade.php](file:///c:/laragon/www/ruanglari/resources/views/calendar/strava-callback.blade.php) tidak menulis localStorage jika user tidak login (sebagai safety net).

## Verifikasi
- Cek route terdaftar dengan middleware auth.
- Test manual:
  - Guest buka `/calendar#strava` → melihat CTA login/register, bukan tombol connect.
  - Guest klik connect → diarahkan login.
  - Setelah login → bisa lanjut connect Strava dan kembali ke `#strava`.
  - User login tanpa token → tombol connect muncul.
  - User login dengan token → dashboard Strava tampil.

Kalau kamu setuju, aku lanjut eksekusi perubahan di `routes/web.php`, `resources/views/calendar/index.blade.php`, dan (opsional) `resources/views/calendar/strava-callback.blade.php`.