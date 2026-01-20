## Status aktivitas Strava vs status plan
- Aktivitas Strava itu secara faktual sudah selesai, jadi di UI event Strava seharusnya tampil **Finished/Completed**.
- Tapi itu **bukan berarti** sesi plan yang UNFINISHED otomatis jadi selesai, kecuali memang sudah ter-link (mis. auto-linked lewat sync ke `ProgramSessionTracking.strava_link`).
- Implementasi: bedakan
  - **Status plan**: `pending/started/completed` (tetap pakai tracking)
  - **Status Strava event**: tampil `completed` + label “Strava” (atau status khusus `imported` tapi dipetakan ke tampilan hijau).

## Coach melihat Strava “realtime” di /coach/athletes/{enrollment}
- Update [AthleteController::calendarEvents](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Coach/AthleteController.php) untuk menambahkan event dari tabel `strava_activities` milik runner enrollment.
- Beri `extendedProps.type = 'strava_activity'`, `extendedProps.status = 'completed'`, serta properti ringkas (distance_km, moving_time_s, strava_url).
- Tambah auto-refresh di FullCalendar coach (mis. `setInterval(()=>calendar.refetchEvents(), 60_000)`) supaya setelah runner sync, coach langsung lihat tanpa reload.

## Detail aktivitas lengkap + grafik (pace/HR/cadence/power)
- Buat service/helper internal (mis. `StravaApiService`) yang dipakai runner+coach:
  - Refresh token user bila expired (pakai `users.strava_access_token/refresh_token/expires_at`).
  - Fetch detail activity: `/api/v3/activities/{id}` (untuk HR avg/max, cadence, splits, laps).
  - Fetch streams: `/api/v3/activities/{id}/streams?keys=time,heartrate,cadence,velocity_smooth,watts&key_by_type=true`.
  - Cache ke `strava_activities.raw` (mis. `raw.details`, `raw.streams`) supaya tidak hit API terus.

- Endpoint Runner (dipakai kalender runner):
  - `GET /runner/strava/activities/{id}/details` (sudah ada) → pastikan output mencakup KM, HR avg/max, pace, cadence, splits, laps.
  - Tambah `GET /runner/strava/activities/{id}/streams` untuk data grafik.

- Endpoint Coach (authorized by ownership enrollment):
  - Tambah `GET /coach/athletes/{enrollment}/strava/activities/{id}/details`
  - Tambah `GET /coach/athletes/{enrollment}/strava/activities/{id}/streams`
  - Controller memastikan coach memang pemilik enrollment sebelum mengakses token runner.

## Update UI Runner Calendar (modal detail)
- Update mapping status: `imported` ditampilkan sebagai Finished (badge hijau) dan teks “FINISHED (STRAVA)”.
- Tambah section detail (jika belum lengkap) untuk: KM, HR avg/max, avg pace, cadence, splits, laps.
- Tambah grafik line multi-dataset:
  - Gunakan Chart.js yang sudah ada di repo (public/vendor/chart-js).
  - Render `time` di x-axis; dataset: pace (dari `velocity_smooth`), heart rate, cadence, power (opsional jika ada).

## Update UI Coach Athlete page
- Saat klik event:
  - Jika program/custom dengan `tracking.strava_link`: fetch details+streams dan tampilkan panel detail di kolom kanan.
  - Jika event `strava_activity`: tampilkan panel detail yang sama.
- Tambah canvas Chart.js untuk grafik multi-metrik.

## Verifikasi
- Runner calendar: event Strava tampil Finished; plan session tetap UNFINISHED kecuali ter-link.
- Coach calendar: event Strava muncul dan auto-refetch menampilkan data baru setelah runner sync.
- Detail view: splits/laps muncul jika tersedia; grafik muncul jika stream tersedia; fallback aman jika data tidak ada.
