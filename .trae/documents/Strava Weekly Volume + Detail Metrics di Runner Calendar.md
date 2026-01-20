## Sumber Weekly Volume Analysis
- Weekly Volume Analysis di /runner/calendar berasal dari endpoint **GET /runner/calendar/weekly-volume** (route `runner.calendar.weekly-volume`).
- Perhitungannya ada di [CalendarController::weeklyVolume](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Runner/CalendarController.php#L900-L994):
  - Range: 12 minggu ke belakang s/d 4 minggu ke depan.
  - `planned`: total km dari program sessions + custom workouts.
  - `actual`: total km yang berstatus selesai (ProgramSessionTracking `completed` + custom workout `completed`).
  - Saat ini **belum memasukkan** aktivitas Strava.

## Gabungkan Volume Strava ke Weekly Volume
- Update `weeklyVolume()` agar menambahkan komponen Strava:
  - Ambil aktivitas Strava (`strava_activities`) dalam range minggu.
  - Hitung `strava_km` per minggu (khusus run types: run/virtualrun/trailrun/treadmill; atau semua jenis jika diinginkan).
- Hindari double-count:
  - Ekstrak `strava_activity_id` dari `ProgramSessionTracking.strava_link` (URL Strava) untuk sesi program yang sudah auto-linked.
  - Saat menjumlahkan `strava_km`, **exclude** aktivitas yang sudah ter-link ke session (supaya tidak dobel dengan `actual` dari plan).
- Output JSON weekly volume diperluas:
  - `planned`
  - `actual_plan` (actual versi sekarang)
  - `actual_strava_unplanned` (Strava yang belum ter-link)
  - `actual_total` = `actual_plan + actual_strava_unplanned`
- Update chart di [calendar_modern.blade.php](file:///c:/laragon/www/ruanglari/resources/views/runner/calendar_modern.blade.php) agar menampilkan:
  - Planned bar (abu)
  - Actual bar (neon) berbasis `actual_total` dan tooltip memecah angka (plan vs strava).

## Detail Workout Strava Lebih Lengkap (HR, pace, splits, laps, cadence)
- Masalah saat ini:
  - Klik event `strava_activity` hanya menampilkan ringkas (nama, jarak, durasi), karena data event berasal dari summary `athlete/activities`.
  - HR/cadence/splits/laps biasanya ada di endpoint **activity detail** Strava (`/api/v3/activities/{id}?include_all_efforts=true`).

## Implementasi Endpoint Detail Strava (server-side)
- Tambah endpoint baru (runner protected), misalnya:
  - **GET /runner/strava/activities/{strava_activity_id}/details**
- Controller akan:
  - Validasi activity milik user.
  - Pakai token dari tabel `users` (`strava_access_token`, `strava_refresh_token`, `strava_expires_at`) untuk request Strava.
  - Jika token expired: refresh token (client id/secret tetap dari StravaConfig/env).
  - Fetch detail Strava activity (include splits/laps) dan cache hasilnya ke `strava_activities.raw` (merge) supaya klik berikutnya tidak hit API terus.
  - Return JSON yang sudah disanitasi untuk UI: avg/max HR, avg cadence, avg pace, splits_metric, laps.

## Update UI Modal Detail di Calendar
- Di [calendar_modern.blade.php](file:///c:/laragon/www/ruanglari/resources/views/runner/calendar_modern.blade.php):
  - Saat `showEventDetail()` untuk `strava_activity`, panggil endpoint detail dan isi `detail.strava_metrics`.
  - Jika program/custom punya `detail.strava_link`, parse activity id dari link dan fetch detail yang sama.
  - Tambah section baru di modal:
    - **Strava Metrics**: Avg/Max HR, Avg Pace, Cadence.
    - **Splits**: daftar per km (pace & waktu; HR jika tersedia).
    - **Laps**: table ringkas (distance, time, pace, avg HR/cadence jika ada).
  - Tambahkan state loading & error (mis. “Fetching Strava details…”).

## Catatan tentang “secret key dari table users”
- Strava API butuh access token user (memang dari tabel `users`).
- Proses refresh token butuh client secret aplikasi; saat ini aman di StravaConfig/env (bukan per-user). Jika benar-benar wajib per-user, kita perlu tambah kolom baru di users, tapi itu berisiko keamanan.

## Verifikasi
- Cek weekly volume chart: planned/actual_total berubah setelah sync dan tidak double count sesi yang sudah ter-link.
- Klik event Strava / workout dengan strava_link: modal menampilkan HR/pace/splits/laps/cadence bila datanya ada dari Strava.

Jika OK, saya lanjut implementasi perubahan controller + route + UI modal sesuai langkah di atas.