## Tujuan
- Dropdown event di `/registrasi-komunitas` bisa di-search + UI lebih modern.
- Integrasi komunitas konsisten: komunitas yang mendaftar terhubung ke master `communities`.
- URL flow konsisten: dari `/registrasi-komunitas/{slug-event}/{id}` menjadi `/registrasi-komunitas/{slug-event}/{slug-community}`.

## Kenapa `community_registrations` tidak digabung ke `communities`
- `communities` = master data (profil komunitas).
- `community_registrations` = data transaksional per event (status draft/invoiced/paid, invoice, participants, timestamps).
- Satu komunitas bisa ikut banyak event → struktur yang tepat adalah `communities (1) -> (N) community_registrations` (relasi ini sudah ada via `community_id`).

## Perubahan URL (Konsistensi Route)
- Update route GET show:
  - Dari: `/registrasi-komunitas/{event:slug}/{registration}`
  - Menjadi: `/registrasi-komunitas/{event:slug}/{community:slug}`
- Update semua endpoint turunan agar juga memakai `event slug + community slug`:
  - `/registrasi-komunitas/{event:slug}/{community:slug}/pic`
  - `/registrasi-komunitas/{event:slug}/{community:slug}/participants` (GET/POST)
  - `/registrasi-komunitas/{event:slug}/{community:slug}/participants/{participant}` (DELETE)
  - `/registrasi-komunitas/{event:slug}/{community:slug}/invoice` (POST)
- Compatibility:
  - Pertahankan route lama berbasis `{registration}` sebagai legacy (opsional), lalu redirect ke URL baru jika `community_id` tersedia.

## Konsistensi Data (Supaya URL slug selalu bisa dipakai)
- Pastikan setiap `community_registrations` punya `community_id`:
  - Jika user memilih dari master: sudah ada.
  - Jika user isi manual: buat/temukan `Community` dan set `community_id`.
- Tambah unique constraint untuk mencegah ambigu URL:
  - `community_registrations` unique `(event_id, community_id)`.
  - Konsekuensi: 1 komunitas hanya punya 1 registrasi per event (sesuai flow URL yang Anda minta).

## UI: Searchable Event Select + Redesign
- Update [index.blade.php](file:///c:/laragon/www/ruanglari/resources/views/community/index.blade.php)
  - Ganti select event menjadi combobox Alpine (tanpa jQuery/plugin): input search + dropdown hasil filter.
  - Hidden input `name="event_id"` tetap dipakai untuk submit.
  - Default selection tetap support `old('event_id')` dan query string `eventId/slug`.
  - Redesign form menjadi step sections (lebih modern, spacing lebih rapi, card styling konsisten pacerhub).

## Backend: Integrasi Community saat Start
- Update [CommunityRegistrationController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/CommunityRegistrationController.php)
  - `start()`:
    - Jika manual input: cari community existing by nama (case-insensitive) atau email PIC.
    - Jika tidak ada, buat `Community` baru + generate `slug` unik.
    - Buat/ambil `CommunityRegistration` dengan `firstOrCreate([event_id, community_id])`.
    - Redirect ke route baru: `community.register.show` memakai `{event:slug}` + `{community:slug}`.
  - Semua action lain (`show`, `updatePic`, `participants`, `invoice`) mengambil registration via `(event_id, community_id)` agar konsisten dengan URL slug.

## Update View Show (Agar API URL ikut konsisten)
- Update [show.blade.php](file:///c:/laragon/www/ruanglari/resources/views/community/show.blade.php)
  - Ubah generator URL JS (`urls.savePic`, `urls.listParticipants`, dst.) agar memakai parameter `event` + `community` (slug), bukan `registration->id`.

## Migrasi DB
- Tambah migration untuk unique index `(event_id, community_id)` pada `community_registrations`.
- (Opsional tapi direkomendasikan) Backfill `community_id` untuk data lama yang masih null:
  - Match by `community_name` / `pic_email`, else create community, lalu set `community_id`.

## Tes & Verifikasi
- Update test yang terdampak route:
  - [CommunityViewTest.php](file:///c:/laragon/www/ruanglari/tests/Feature/CommunityViewTest.php)
  - [CommunityRegistrationFlowTest.php](file:///c:/laragon/www/ruanglari/tests/Feature/CommunityRegistrationFlowTest.php)
- Tambah test untuk memastikan:
  - Registrasi manual membuat/menautkan `communities` dan URL show memakai slug community.
  - Endpoint participants/invoice tetap jalan lewat URL baru.

## File yang Akan Disentuh
- [routes/web.php](file:///c:/laragon/www/ruanglari/routes/web.php)
- [CommunityRegistrationController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/CommunityRegistrationController.php)
- [index.blade.php](file:///c:/laragon/www/ruanglari/resources/views/community/index.blade.php)
- [show.blade.php](file:///c:/laragon/www/ruanglari/resources/views/community/show.blade.php)
- `database/migrations/*` (unique index + opsional backfill)
- `tests/Feature/*`

Jika plan ini sudah sesuai, saya lanjut eksekusi perubahan-perubahan tersebut.