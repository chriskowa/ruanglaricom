## Ringkasan
- Tambah kolom **address (Alamat)** di tabel `participants`, simpan dari form registrasi publik dan input manual EO.
- Sediakan **API JSON** untuk mengambil participants berdasarkan `event_id` (dengan pagination & filter).
- Sediakan **export besar** ke **CSV + Excel (XLSX)** dan opsi **sinkronisasi ke Google Sheets** (service account), lengkap dengan error handling, logging, dan test.

## 1) Database/Schema
- Buat migration baru:
  - Tambah `address` (nullable, `string` panjang 500 atau `text`) pada `participants`.
- Update model [Participant.php](file:///c:/laragon/www/ruanglari/app/Models/Participant.php) untuk menambahkan `address` ke `$fillable`.
- (Opsional tapi direkomendasikan untuk “hubungkan ke spreadsheet” per event) Tambah kolom JSON baru pada `events`:
  - `sheets_config` (json, nullable) berisi `{spreadsheet_id, sheet_name, last_synced_at, ...}`.

## 2) Update Form & Validasi (Public + EO)
- Update validasi di [StoreRegistrationAction](file:///c:/laragon/www/ruanglari/app/Actions/Events/StoreRegistrationAction.php):
  - Tambah rule `participants.*.address` (mis. `required|string|max:500` atau `nullable|string|max:500` sesuai keputusan final).
  - Saat `Participant::create(...)`, ikut simpan `address`.
- Update EO manual entry:
  - Tambah field `address` di validasi [EO EventController@storeParticipant](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php#L909-L936).
  - Tambah save `address` di [StoreManualParticipantAction](file:///c:/laragon/www/ruanglari/app/Actions/EO/StoreManualParticipantAction.php) pada `Participant::create`.
- Tambah input Alamat pada theme registrasi publik (berdasarkan temuan lokasi field peserta):
  - [paolo-fest.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/paolo-fest.blade.php#L1745-L1767)
  - [simple-minimal.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/simple-minimal.blade.php#L282-L351)
  - [paolo-fest-dark.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/paolo-fest-dark.blade.php#L933-L997)
  - [modern-dark.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/modern-dark.blade.php#L792-L869)
  - [professional-city-run.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/professional-city-run.blade.php#L505-L536)
  - [light-clean.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/light-clean.blade.php#L510-L564)
- Tambah input Alamat pada modal EO di [participants.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/participants.blade.php).
- Pastikan mekanisme restore `old()`/draft tetap berjalan (input baru ikut tersimpan/restore); jika ada script restore yang whitelist field, akan diupdate.

## 3) API Endpoint Ambil Participants per Event ID
- Tambah route baru di [web.php](file:///c:/laragon/www/ruanglari/routes/web.php):
  - `GET /api/eo/events/{event}/participants` (middleware `auth` + `role:eo`).
- Implement method baru di `EO\EventController` atau controller khusus API (pilih yang paling konsisten):
  - Output JSON berisi fields yang sama seperti list peserta, plus `address`.
  - Pakai pagination yang efisien (cursor pagination) untuk volume besar.
  - Support filter yang sudah ada di list/export (payment_status, gender, category_id, is_picked_up).

## 4) Export CSV + Excel (XLSX) dengan Performa Besar
- Rapikan export server-side existing [exportParticipants()](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php#L1008-L1100):
  - Tambahkan kolom **Alamat**.
  - Perbaiki issue `target_time` (sekarang string) yang masih diperlakukan seperti DateTime di export.
  - Ubah implementasi supaya chunking (`chunkById`) + streaming tetap aman untuk data besar.
- Tambahkan export XLSX:
  - Tambah dependency yang streaming-friendly (mis. `openspout/openspout`) agar memory tetap rendah.
  - Route contoh: `GET /eo/events/{event}/participants/export.xlsx`.

## 5) Integrasi Google Sheets (Service Account)
- Tambah dependency `google/apiclient`.
- Buat service `GoogleSheetsParticipantExporter` yang:
  - Membaca kredensial dari env (path JSON atau base64) dan membuat client.
  - Validasi spreadsheet access (spreadsheet_id + share ke service account).
  - Menulis header + rows menggunakan batch append (mis. 500 rows/batch) untuk performa.
  - Mendukung “create sheet tab jika belum ada”.
- Tambah action/endpoint EO untuk trigger export ke Sheets:
  - `POST /eo/events/{event}/participants/export/sheets` (auth+role:eo).
  - Input: `spreadsheet_id`, `sheet_name` (opsional), `mode` (append/replace).
  - Simpan `sheets_config` pada event (jika memakai kolom baru), sehingga “hubungkan” menjadi persistent.
- Pertimbangkan menjalankan export via queue job (recommended untuk volume besar) + progress/status.

## 6) Error Handling & Logging
- Tambah logging terstruktur untuk:
  - Mulai export, jumlah rows, durasi, event_id, spreadsheet_id.
  - Error detail (exception class/message) tanpa membocorkan secret.
- Semua endpoint export mengembalikan pesan error yang aman (tanpa stack trace) + HTTP status yang tepat.

## 7) Unit Test & Integration/Feature Test
- Tambah test validasi & persist alamat:
  - Public registration flow: memastikan `participants.*.address` divalidasi dan tersimpan.
  - EO manual store: memastikan alamat tersimpan.
- Tambah test API participants:
  - Auth EO wajib, response JSON mengandung `address`, pagination berjalan.
- Tambah test export:
  - CSV response headers benar, kolom Alamat ada.
  - XLSX basic (minimal assertion: status 200 + content-type + file signature).
- Untuk Google Sheets:
  - Service dibuat testable dengan adapter/interface (fake client) sehingga test tidak memanggil API eksternal.

## 8) Dokumentasi Format Output (di dalam codebase tanpa file baru)
- Tambahkan dokumentasi mapping kolom output (CSV/XLSX/Sheets) di docblock/constant pada exporter/service (tanpa membuat file .md baru), mencakup:
  - No, Nama, Gender, Email, Phone, ID Card, **Alamat**, Kategori, BIB, Jersey Size, Target Time, Payment Status, Pickup Status, Tanggal Registrasi, dll.

## Catatan Teknis Penting
- Saat ini export CSV server-side masih memanggil `$participant->target_time->format(...)` padahal `target_time` sudah string; akan saya perbaiki saat implementasi.
- Semua endpoint “API” saat ini memang berada di `routes/web.php`, jadi penambahan endpoint akan mengikuti pola yang sama.

Jika rencana ini OK, saya lanjut implementasi end-to-end (migration, form, API, export CSV/XLSX, Sheets exporter, logging, dan test).