## Kondisi Saat Ini
- Secara konsep ada 2 jenis event yang sekarang sama-sama berada di tabel `events`:
  - **Directory/Listing event** (dulunya dari `running_events`, untuk halaman `/event-lari`). Migrasi merge memang memasukkan data `running_events` ke `events` dan set `user_id = 1` (admin). Lalu `running_events` di-rename jadi `running_events_backup`. Lihat [merge_running_events_to_events_table.php](file:///c:/laragon/www/ruanglari/database/migrations/2026_01_22_065824_merge_running_events_to_events_table.php#L65-L152).
  - **Managed/EO event** (dibuat EO untuk registrasi, kategori tiket, dsb) via [EO EventController](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php#L73-L170).
- Cara “membedakan” saat ini masih heuristik:
  - `Event::is_eo` menganggap EO bila `user_id !== 1` ([Event.php](file:///c:/laragon/www/ruanglari/app/Models/Event.php#L287-L321)).
  - `Event::public_url` memilih `/event-lari` bila `external_registration_link` ada atau `user_id === 1`, selebihnya `/event/{slug}` ([Event.php](file:///c:/laragon/www/ruanglari/app/Models/Event.php#L294-L321)).
- Problem yang bikin “EO harus aman”:
  - Halaman listing publik `/event-lari` saat ini query `Event::published()->upcoming()` tanpa filter jenis event, jadi EO event berpotensi ikut tampil ([PublicRunningEventController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/PublicRunningEventController.php#L17-L71)).
  - Modul **Master GPX** harusnya mengait ke **`events`** (kolom `event_id`), karena migrasi merge sudah rename `master_gpxes.running_event_id` → `event_id` dan FK ke `events` ([merge migration](file:///c:/laragon/www/ruanglari/database/migrations/2026_01_22_065824_merge_running_events_to_events_table.php#L115-L131)).

## Target
- **EO event aman**: tidak nyampur ke listing/directory dan tidak muncul sebagai pilihan di Master GPX (kecuali memang diinginkan).
- **Master GPX jelas**: saat pilih “event”, yang dimuat adalah event directory/aggregator (bukan EO managed), dan kolom relasi sesuai skema DB.

## Perubahan yang Akan Saya Lakukan
1. **Rapikan Master GPX agar sesuai skema hasil migrasi**
   - Kembalikan relasi `MasterGpx` → `Event` menggunakan `event_id`.
   - Perbaiki `Admin/MasterGpxController` agar load event dari `Event`, bukan `RunningEvent`.
   - Update form create/edit Master GPX: field dropdown pakai `event_id`, tampilkan tanggal dari `start_at`.

2. **Buat pemisahan yang eksplisit untuk jenis event (agar tidak bergantung `user_id===1`)**
   - Tambah kolom `event_kind` (mis. `directory` | `managed`) pada tabel `events`.
   - Backfill:
     - Set `event_kind='directory'` untuk event yang berasal dari aggregator (minimal rule aman: `user_id === 1`; bisa diperkaya dengan `external_registration_link` bila perlu).
     - Set `event_kind='managed'` untuk event EO (`user_id !== 1`).
   - Tambah scope Eloquent `Event::directory()` dan `Event::managed()`.

3. **Pastikan listing `/event-lari` hanya menampilkan directory events**
   - Update query di `PublicRunningEventController` menjadi `Event::directory()->published()->upcoming()`.
   - Update sumber filter `City::has('events')` dan sejenisnya menjadi `whereHas('events', fn($q)=>$q->directory())` agar dropdown filter juga konsisten.

4. **Batasi dropdown Event di Master GPX hanya untuk directory events**
   - Dropdown event di Master GPX hanya memuat `Event::directory()` sehingga EO event tidak pernah kebawa dan tidak membingungkan.

5. **Verifikasi**
   - Tambah/adjust feature test:
     - Pastikan endpoint listing `/event-lari` tidak mengandung event EO.
     - Pastikan halaman Admin Master GPX create tidak menampilkan event EO di dropdown.
   - Jalankan test suite yang relevan.

## Dampak dan Keamanan
- EO event tetap berada di tabel `events` (karena arsitektur saat ini memang begitu), tapi “aman” karena:
  - Tidak akan ikut query directory/listing.
  - Tidak akan muncul di Master GPX dropdown.
  - Penentuan jenis event tidak lagi bergantung pada asumsi `user_id === 1` semata.

Jika sudah oke, saya lanjut implementasi perubahan di atas dan pastikan semuanya lewat test.