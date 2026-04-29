# Rencana Eksekusi: Upgrade Race Master → Fitur Race (Landing + Slug + Join Runner + Leaderboard + Reset)
Tanggal: 2026-04-29

## Tujuan
Membuat “Race” sebagai mini-event:
- Ada landing page publik berbasis slug (mirip event).
- Dalam 1 race ada beberapa kategori.
- Runner bisa daftar/join kategori (minimal gratis dulu).
- Ada leaderboard per kategori yang bisa di-reset.
- Pengelolaan race (create/update/start/finish/reset) hanya untuk user login role admin/EO.

Catatan performa:
- Race start/stop dan input lap harus responsif.
- UI timer dan interaksi utama berjalan di sisi klien (optimistic UI), server hanya sinkronisasi.

---

## Kondisi Codebase Saat Ini (ringkas)
- Admin CRUD Race sudah ada: `/admin/races` via `Admin\RaceController`.
- Tool Race Master sudah ada:
  - UI: `/tools/race-master`
  - API: `/api/tools/race-master/*` (perlu dipastikan proteksi middleware untuk endpoint write)
  - Public results: `/tools/race-master/results/{slug}` berbasis `race_sessions.slug`, bukan `races.slug`.
- Struktur data Race Master:
  - `races`: name, logo_path, created_by, event_id (nullable)
  - `race_sessions`: slug (unique), category, distance_km, started_at, ended_at
  - `race_session_participants`: race_id + bib + name (+ result fields)
  - `race_session_laps`: lap-by-lap per session

---

## Prinsip Akses & Security
### Hak Akses
- Admin/EO:
  - Bisa create/update/delete Race.
  - Bisa buat kategori (RaceSession), start/finish kategori, reset leaderboard.
  - Bisa import peserta (bulk) dan manage peserta.
- Runner:
  - Hanya bisa join/daftar kategori (membuat entry peserta untuk dirinya).
  - Hanya bisa lihat landing & leaderboard.

### Middleware Wajib
- Semua endpoint yang mengubah data Race Master wajib:
  - `auth`
  - `role:admin,eo` (atau policy/guard sejenis yang sudah dipakai proyek)
- Endpoint publik:
  - Landing race publik + leaderboard publik boleh tanpa login.
  - Join butuh login runner: `auth` + `role:runner`

---

## Desain Data (Minimal tapi siap berkembang)
### 1) Tambah Slug + Field Landing di `races`
Tambahkan kolom (minimal):
- `slug` (unique) untuk landing publik `/races/{slug}`
- `is_published` boolean (default false)
- `published_at` timestamp nullable

Opsional untuk “mirip event”:
- `description` text nullable
- `location_name` string nullable
- `start_at` timestamp nullable
- `end_at` timestamp nullable
- `banner_path` string nullable
- `reg_open_at`, `reg_close_at` timestamp nullable

Catatan: Race saat ini bisa terkait event lewat `event_id`. Itu boleh tetap, tapi landing race tetap berdiri sendiri.

### 2) Multi Kategori dalam 1 Race
Paling efektif: gunakan `RaceSession` sebagai “Kategori”.
- 1 Race bisa punya banyak RaceSession.
- Field kategori sudah ada: `category`, `distance_km`, `slug`.
- Untuk kebutuhan pendaftaran, tambahkan per session (opsional bertahap):
  - `quota` (int nullable)
  - `price` (int nullable) jika suatu saat mau bayar
  - `bib_start` (int nullable), `bib_prefix` (string nullable)

### 3) Join Runner Otomatis (Pendaftaran)
Agar runner join kategori:
- Tambahkan ke `race_session_participants`:
  - `race_session_id` nullable (FK ke race_sessions)
  - `user_id` nullable (FK ke users)

Kaidah:
- Peserta hasil timing tetap mengacu ke `race_session_participants.id` sehingga kompatibel dengan lap.
- Saat join runner, entry dibuat pada `race_session_participants` dengan:
  - `race_id`
  - `race_session_id`
  - `user_id`
  - `name` (dari profil)
  - `bib_number` (auto-generate)

Uniqueness yang disarankan:
- Minimal: unique `race_session_id + user_id` (agar runner tidak join dua kali kategori yang sama)
- Untuk BIB:
  - unique `race_session_id + bib_number` (lebih realistis untuk multi kategori)

---

## Routing & UX Publik
### Landing Race (Publik)
- `GET /races/{slug}`
  - Tampilkan banner/logo, info race, dan daftar kategori (RaceSession).
  - Untuk tiap kategori: tombol “Join” (jika login runner) atau tombol “Login untuk Join”.

### Join Race Kategori (Runner)
- `POST /races/{slug}/categories/{raceSession}/join`
  - Middleware: `auth` + `role:runner`
  - Buat `race_session_participants` untuk runner.
  - Return: BIB number + info kategori + link leaderboard.

### Leaderboard Publik
- `GET /races/{slug}/categories/{raceSession}/leaderboard`
  - Ambil standings dari lap (`computeStandings` sudah ada, tapi perlu di-reuse untuk view publik race).
  - Bisa juga embed JSON endpoint existing, tapi idealnya langsung query untuk menghindari ketergantungan slug session-only.

---

## Admin/EO: Management Flow (dari /admin/races)
### 1) Dari Listing `/admin/races`
- Tambah kolom/aksi:
  - Public URL: `/races/{slug}` (copy/open)
  - Toggle publish (opsional tahap 1 atau tahap 2)

### 2) Halaman Detail Race
- Section “Kategori” (RaceSessions):
  - Create kategori (category + distance + quota/bib config).
  - Tombol “Open Leaderboard” (public).
  - Tombol “Reset Leaderboard” per kategori.
  - Tombol “Start/Finish” per kategori.

---

## Reset Leaderboard (Per Kategori / Session)
Reset tidak boleh merusak kategori lain.

Untuk satu `race_session_id`:
- Hapus semua `race_session_laps` untuk session tsb.
- Hapus `race_certificates` untuk session tsb (jika ada).
- Jangan hapus peserta join (biar tetap terdaftar), kecuali ada opsi “reset peserta”.

Endpoint admin/eo:
- `POST /admin/races/{race}/sessions/{session}/reset`
  - Middleware: admin/eo
  - DB transaction

---

## Performa: Start/Stop & Input Lap Responsif (Client-Side First)
Tujuan: race day tidak terasa “lemot” karena tiap klik mengakses DB.

### Prinsip
- UI selalu cepat: timer dan state “RUNNING/FINISHED” dihitung di klien (optimistic UI).
- Server hanya:
  - menerima event sinkronisasi (start/finish) secara ringan,
  - menerima lap dalam batch,
  - menghitung standings saat finish.

### Mekanisme yang Disarankan
1) Local Timer
- Saat operator klik “Start”:
  - Simpan `client_started_at = Date.now()` (localStorage) + session_id.
  - UI timer jalan dari client.
  - Kirim request start ke server secara async; kalau gagal, retry background.

2) Lap Queue (anti spam DB)
- Ketika scan input lap:
  - push ke queue in-memory + persist minimal ke localStorage (agar tahan refresh).
  - kirim ke server dalam batch:
    - setiap N lap (mis. 10) atau setiap X detik (mis. 2–5 detik)
- Jika network down:
  - tetap terima scan, queue menumpuk, tampilkan indikator “OFFLINE / UNSYNCED”.
  - saat online kembali: flush batch.

3) Start/Stop Minimal Write
- Start server cukup sekali per session (set `started_at`).
- Finish server:
  - set `ended_at`
  - compute standings
  - generate certificates (opsional: bisa tombol terpisah agar finish tidak berat)

4) Idempotensi
- Tiap lap item punya key idempotent (mis: `bib + total_time_ms + recorded_at`) agar double submit tidak menduplikasi.
- Server memvalidasi monotonic increase `total_time_ms`.

### Catatan: Saat ini API `storeLap` adalah 1 per request
Rencana perubahan:
- Tambah endpoint baru: `POST /api/tools/race-master/sessions/{session}/laps/bulk`
  - menerima array lap
  - insert/update dalam transaction
  - response ringkas (count ok, count ignored)

---

## Tahapan Implementasi (sekali jalan saat eksekusi)
### Tahap 0 — Audit & Kunci Akses
- Pastikan semua route write Race Master diproteksi:
  - `/api/tools/race-master/races` (POST/PUT)
  - `/api/tools/race-master/races/{race}/participants/bulk`
  - `/api/tools/race-master/races/{race}/sessions` (start session)
  - `/api/tools/race-master/sessions/{session}/laps`
  - `/api/tools/race-master/sessions/{session}/finish`
  - poster/certificate generation endpoints yang write
- Public hanya:
  - landing race + leaderboard race (baru)
  - existing `public/*` endpoint yang memang untuk publik

Deliverable:
- Route group middleware `auth + role admin/eo` untuk write endpoints.

### Tahap 1 — Landing Race dengan Slug
1. Migration: tambah `slug`, `is_published`, `published_at` (plus field landing optional).
2. Update model `Race` fillable/casts.
3. Admin UI:
   - form create/edit slug + publish toggle
   - tampilkan public URL di index/show admin.
4. Public route:
   - `GET /races/{slug}` view landing.

Deliverable:
- Race bisa punya landing publik by slug.

### Tahap 2 — Kategori sebagai RaceSession (CRUD ringan)
1. Tambah UI admin untuk create RaceSession sebagai kategori (tanpa start).
2. Generate `race_sessions.slug` untuk public leaderboard per kategori.
3. Tampilkan list kategori di landing race + link leaderboard.

Deliverable:
- 1 race bisa punya beberapa kategori.

### Tahap 3 — Join Runner Otomatis (Pendaftaran)
1. Migration: tambah `race_session_id`, `user_id` ke `race_session_participants`.
2. Tambah unique constraints yang diperlukan (session+user, session+bib).
3. Public POST join:
   - allocate BIB (server-side)
   - create row peserta untuk runner.
4. Landing race: tombol join & status “sudah join”.

Deliverable:
- Runner bisa daftar/join kategori dan dapat BIB.

### Tahap 4 — Leaderboard Reset Per Kategori
1. Endpoint admin/eo reset session:
   - delete laps + certificates untuk session.
2. Admin UI: tombol reset.

Deliverable:
- Leaderboard bisa direset tanpa ganggu kategori lain.

### Tahap 5 — Race Day Responsif (Client Queue + Bulk Lap)
1. Tambah endpoint bulk lap.
2. Update UI Race Master tool:
   - local timer
   - queue + batching + retry + indikator sync/offline
   - start/finish optimistic.

Deliverable:
- Race start/stop & scan lap responsif walau koneksi/server lambat.

---

## Checklist Testing
- Feature test (minimal):
  - Admin/EO bisa create race + slug.
  - Public bisa buka landing race jika published.
  - Runner login bisa join kategori dan mendapat BIB unik.
  - Admin reset leaderboard hanya menghapus laps/certs session tsb.
  - Non-admin/eo tidak bisa akses write API.
- Manual test race day:
  - Start (offline mode) → scan beberapa lap → online → flush sukses.
  - Double submit lap tidak menduplikasi.

---

## Catatan Teknis Penting
- Saat ini public results sudah berbasis `race_sessions.slug`. Itu tetap bisa dipakai untuk leaderboard per kategori.
- Untuk “landing race mirip event”, kita butuh `races.slug` terpisah dari `race_sessions.slug`.
- Di `routes/web.php` terlihat ada 2 definisi route yang sama untuk `/tools/race-master` — perlu dibereskan saat tahap 0 agar tidak membingungkan.

