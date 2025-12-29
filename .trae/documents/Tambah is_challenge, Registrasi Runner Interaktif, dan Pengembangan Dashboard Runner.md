## Modifikasi Database (Programs)
- Tambah kolom `is_challenge` (boolean, default false) pada tabel `programs` melalui migration baru.
- Update `App\Models\Program`:
  - Tambah `is_challenge` ke `$fillable` dan `$casts` (boolean).
  - Pastikan getter/akses tidak mempengaruhi endpoint existing.
- Dampak: tidak mengubah data lama; nilai default otomatis false.

## Update Form Create/Edit Program (Coach)
- File: `resources/views/coach/programs/create.blade.php` dan `edit.blade.php`
- Tambahkan checkbox `is_challenge` di panel pengaturan program.
- Binding ke form Vue: `v-model="form.is_challenge"`.
- Controller: `App\Http\Controllers\Coach\ProgramController`
  - Validasi: `is_challenge` => `nullable|boolean`.
  - Store/Update: konversi ke boolean (`$request->has('is_challenge')`).
- Uji manual: create & edit menyimpan nilai challenge dengan benar.

## Halaman Registrasi Runner Interaktif
- Route baru: `GET /runner/register`, `POST /runner/register` (middleware `guest`).
- Controller: `App\Http\Controllers\Runner\RunnerRegistrationController` (baru).
- View: `resources/views/runner/register.blade.php` (UI interaktif modern mengacu pada gaya `programs/design.blade.php`):
  - Form Step-by-step (multi-step) atau modal-driven:
    - Data pribadi: nama, email, password, gender, tanggal lahir, kota.
    - Data kesehatan dasar: tinggi/berat, riwayat cedera (opsional), preferensi latihan.
    - Personal Best (PB): 5K, 10K, 21K, 42K (format `H:i:s`).
    - Tes awal: Cooper test distance (m), resting HR, VO2max (opsional / kalkulasi sederhana).
  - Validasi komprehensif di frontend (Vue) & backend (Laravel Request).
- Penyimpanan data:
  - Simpan akun user (role `runner`).
  - Simpan PB & hasil tes ke kolom di `users` (gunakan migration yang sudah ada untuk PB; jika belum cukup, buat migration tambahan: `pb_5k_time`, `pb_10k_time`, `pb_21k_time`, `pb_42k_time`, `cooper_distance`, `resting_hr`, dll.).
- Setelah sukses:
  - Auto-login user atau redirect ke login.
  - Redirect ke halaman pemilihan program (lihat bagian berikut).

## Alur Pemilihan Program Challenge
- Halaman seleksi: `GET /runner/programs/challenges`
  - Gunakan komponen grid mirip `resources/views/programs/index.blade.php` tetapi difilter `is_challenge=true`.
  - Query: `Program::where('is_published', true)->where('is_active', true)->where('is_challenge', true)`.
- Pemilihan program:
  - Aksi: `POST /runner/programs/{program}/enroll-free` (reuse existing controller `Runner\ProgramEnrollmentController`) atau `purchase` jika berbayar.
- Setelah memilih:
  - Redirect ke `runner.dashboard`.

## Pengembangan Dashboard Runner
- File: `resources/views/runner/dashboard.blade.php` (rework / extend existing `Runner\DashboardController@index`).
- Fitur:
  - Progress tracking: grafik volume mingguan & jumlah sesi selesai (gunakan data `ProgramSessionTracking`).
  - Personal statistics: ringkasan PB, pace rata-rata, total jarak.
  - Notifikasi & reminder: integrasi dengan `NotificationController` (existing), tampilkan unread count.
  - Integrasi kalender latihan: link ke `runner.calendar`, ringkasan minggu berjalan.
  - Achievement/badge: rule sederhana (mis. 4 minggu konsisten, 100km total, PB improve) dan tampilan badge.
- UI/UX: konsisten dengan halaman registrasi (tailwind, glass-panel, neon theme). Responsive.

## Routing & Akses
- Routes (di `routes/web.php`):
  - Guest: `GET/POST /runner/register` (RunnerRegistrationController).
  - Auth runner: `GET /runner/programs/challenges` untuk seleksi program challenge.
- Middleware: pastikan route challenge & dashboard hanya untuk `role:runner`.

## Validasi & Error Handling
- Backend: Form Request untuk runner register (sanitize & rules), create/edit program (boolean `is_challenge`).
- Frontend: cek field wajib, format waktu PB, angka tes.
- Feedback jelas (toast/alert), fallback default.

## Testing
- Unit:
  - Program model cast & default `is_challenge`.
  - Filter query yang mengembalikan hanya challenge programs.
- Feature:
  - Registrasi runner (happy path dan invalid input).
  - Create/Edit program menyimpan `is_challenge`.
  - Seleksi program challenge mengarahkan ke dashboard.
- UI (dengan Dusk bila tersedia) atau snapshot minimal untuk grid & form.

## Dokumentasi
- API/Routes: tambahkan dokumentasi endpoint registrasi runner & list challenge programs.
- User Manual: alur registrasi → pilih program challenge → dashboard.
- Dev Notes: perubahan model, migration, dan komponen UI.

## Rencana Implementasi Bertahap
1. Migration & model update `Program` untuk `is_challenge`.
2. Update form create/edit + controller validasi & simpan.
3. Halaman Registrasi Runner (UI, controller, validasi, penyimpanan PB & tes).
4. Halaman seleksi challenge + enrollment flow.
5. Dashboard runner fitur-fitur visualisasi & statistik.
6. Testing & dokumentasi.

## Acceptance Criteria
- Coach dapat menandai program sebagai challenge saat create/edit.
- Runner dapat registrasi dengan data profil/PB/tes tersimpan.
- Runner melihat daftar program challenge dan dapat memilihnya.
- Setelah memilih, runner masuk dashboard dengan progress & statistik tampil.
- Semua halaman responsive, error handling jelas, dan test lulus.

Silakan konfirmasi, setelah itu saya mulai implementasi sesuai langkah di atas (satu codebase kohesif).