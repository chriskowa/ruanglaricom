## Jawaban Singkat
- **Email Report untuk EO sudah ada** (route `eo.email-reports.*`, controller [EmailReportController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EmailReportController.php), view [eo/email-reports/index.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/email-reports/index.blade.php)). Yang kurang hanya **menu/link di sidebar EO** (saat ini sidebar EO cuma Dashboard + Master Kupon) di [pacerhub-sidebar.blade.php](file:///c:/laragon/www/ruanglari/resources/views/layouts/components/pacerhub-sidebar.blade.php).
- **Email Monitoring** yang sekarang ada itu **admin-level** (global: queue backlog `jobs`, counter rate limit, failure log, dll). Untuk EO, lebih aman/tepat dibuat versi **monitoring yang terfilter event milik EO** (tanpa expose backlog queue global).
- Untuk **rate limit email non-instant**, bisa ditambah field di create/edit event, lalu dipakai oleh pengiriman **ticket email** (registrasi) dan (opsional tapi sangat relevan) **blast email**.

## Perubahan yang Akan Dibuat
### 1) Taruh “Email Report” di menu EO
- Tambah item menu di blok `@elseif(auth()->user()->isEventOrganizer())` pada [pacerhub-sidebar.blade.php](file:///c:/laragon/www/ruanglari/resources/views/layouts/components/pacerhub-sidebar.blade.php) ke `route('eo.email-reports.index')`.
- (Opsional) tambah “Email Monitoring” EO jika langkah #2 jadi.

### 2) Buat “Email Monitoring” khusus EO (scope per EO)
- Tambah route: `GET /eo/email-monitoring` → `eo.email-monitoring.index`.
- Buat controller baru `App\Http\Controllers\EO\EmailMonitoringController@index` yang menampilkan:
  - Reservasi rate limit (`event_email_minute_counters`) **hanya untuk event milik EO**.
  - Error email ticket/blast (`event_email_delivery_logs`) **hanya untuk event milik EO**.
  - Link cepat ke halaman “Email Laporan” EO yang sudah ada.
- Buat view `resources/views/eo/email-monitoring/index.blade.php` dengan tabel ringkas (mirip admin monitoring, tapi tanpa queue backlog global).

### 3) Tambah field rate limit untuk email non-instant (di Event)
- Tambah kolom baru pada tabel `events` via migration:
  - `ticket_email_rate_limit_per_minute` (nullable int)
  - `blast_email_rate_limit_per_minute` (nullable int)
- Update model [Event.php](file:///c:/laragon/www/ruanglari/app/Models/Event.php): `fillable` + `casts` (integer).
- Update validasi & save di [EO/EventController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php): terima input angka (mis. min 1, max 10000), dan simpan.
- Update form create/edit event:
  - [eo/events/create.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/create.blade.php)
  - [eo/events/edit.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/edit.blade.php)
  - Tambah input angka untuk rate limit “Non-instant” (dengan helper text: kosong = unlimited).

### 4) Terapkan rate limit “Non-instant” pada alur pengiriman
- Ticket email (registrasi): update [EventRegistrationEmailDispatcher.php](file:///c:/laragon/www/ruanglari/app/Services/EventRegistrationEmailDispatcher.php)
  - Saat `is_instant_notification = true` tetap pakai 5/min.
  - Saat `is_instant_notification = false`, jika `ticket_email_rate_limit_per_minute` terisi, gunakan limiter dengan nilai tersebut.
- Blast email: karena sekarang [SendEventBlastEmail.php](file:///c:/laragon/www/ruanglari/app/Jobs/SendEventBlastEmail.php) mengirim banyak email dalam 1 job, rate limit yang “rapi” lebih aman jika diubah menjadi fan-out:
  - `SendEventBlastEmail` → hanya query recipient, lalu dispatch job per recipient.
  - Tambah job baru (mis. `SendSingleEventBlastEmail`) untuk kirim 1 email + logging, di-queue `emails-blast`.
  - Delay per job dihitung memakai limiter + `blast_email_rate_limit_per_minute`.

## Testing & Verifikasi
- Tambah test untuk memastikan ketika non-instant + rate limit di-set, dispatcher menambahkan delay sesuai limit.
- Tambah test untuk memastikan route EO Email Monitoring terproteksi role EO dan hanya menampilkan data event milik EO.

Jika plan ini disetujui, saya lanjut implementasi end-to-end (migration, UI, controller, service/job, tests).