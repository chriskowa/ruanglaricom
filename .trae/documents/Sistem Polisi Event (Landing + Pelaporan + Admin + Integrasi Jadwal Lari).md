## Tujuan
- Menambahkan sistem rating bintang (1–5) pada halaman detail event lari `/event-lari/{slug}` yang tahan duplikasi menggunakan 3 identifier: cookie browser, IP address, dan browser fingerprint.
- Deteksi duplikasi memakai threshold **minimum 2 dari 3 identifier** yang cocok.
- Menyediakan endpoint API submit rating dengan validasi server-side + pesan error jika sudah pernah rating.
- Menambahkan test untuk memverifikasi skenario perubahan identifier.

## Desain Data (Database)
- Buat tabel `event_ratings`:
  - `id`, `event_id` (FK ke `events.id`)
  - `rating` (tinyint 1–5)
  - `cookie_hash` (char(64))
  - `ip_hash` (char(64))
  - `fingerprint_hash` (char(64))
  - timestamps
- Indeks:
  - `(event_id, cookie_hash)`, `(event_id, ip_hash)`, `(event_id, fingerprint_hash)`
  - opsional: `(event_id, cookie_hash, ip_hash)` dan `(event_id, cookie_hash, fingerprint_hash)` dan `(event_id, ip_hash, fingerprint_hash)` untuk mempercepat query duplikasi.
- Privasi: simpan **hash** (SHA-256 + salt `APP_KEY`) agar tidak menyimpan IP/fingerprint mentah.

## Logika Identifikasi  Deteksi Duplikasi (2 dari 3)
- Pada submit rating, server membentuk 3 hash:
  - `cookie_hash = sha256(cookie_value + '|' + APP_KEY)`
  - `ip_hash = sha256(request_ip + '|' + APP_KEY)`
  - `fingerprint_hash = sha256(fingerprint_string + '|' + APP_KEY)`
- Duplikasi untuk event yang sama jika ada record existing yang memenuhi salah satu kondisi:
  - `(cookie_hash == X AND ip_hash == Y)`
  - `(cookie_hash == X AND fingerprint_hash == Z)`
  - `(ip_hash == Y AND fingerprint_hash == Z)`
- Jika duplikat: return HTTP **409** + JSON message “Anda sudah pernah memberikan rating untuk event ini.”

## Cookie Identifier (Browser Cookie)
- Nama cookie: `rl_rating_id` (UUID/random string)
- Strategi set cookie:
  - Jika cookie belum ada saat load halaman, halaman detail akan menyetel cookie (server-side response cookie) sehingga konsisten dan tidak bergantung JS.
  - JS tetap memastikan request `fetch` mengirim cookie dengan `credentials: 'same-origin'`.

## Browser Fingerprint (Client-side)
- Di `running-event-detail.blade.php`, buat fungsi fingerprint ringan (mobile-friendly) tanpa library tambahan:
  - Gabungkan sinyal yang cukup stabil: `navigator.userAgent`, `navigator.language`, `navigator.platform`, `screen.width/height/colorDepth`, timezone offset.
  - Kirim sebagai string ke server (server yang hashing dengan salt), bukan menyimpan mentah di DB.
- Tambahkan dukungan `prefers-reduced-motion` untuk UX (tidak mengganggu aksesibilitas).

## Endpoint API Submit Rating
- Tambah route (konsisten dengan pola repo yang sudah punya endpoint `/api/...` di `routes/web.php`):
  - `POST /api/running-events/{slug}/rating`
- Controller baru `EventRatingController@store`:
  - Validasi: `rating` wajib integer 1–5, `fingerprint` wajib string (min length), event slug harus valid.
  - Ambil IP dari request, cookie dari request (buat jika tidak ada), fingerprint dari body.
  - Jalankan query duplikasi 2/3.
  - Jika lolos: simpan rating dan return JSON ringkasan (avg+count) agar UI bisa update.

## UI di Halaman Detail `/event-lari/`
- Update [running-event-detail.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/running-event-detail.blade.php) untuk menambahkan:
  - Widget rating bintang 1–5 (button accessible, aria-label, focus states)
  - Ringkasan “Rata-rata X (N rating)” (data dari controller atau hasil response API)
  - State UI: loading, success, error (khusus duplikasi)
- JS:
  - Generate fingerprint string.
  - Submit ke endpoint API dengan `fetch` + `credentials`.
  - Handle response 201/200 vs 409 vs 422.

## Pengujian (PHPUnit)
- Buat test (Feature/Unit) untuk deteksi duplikasi:
  - Skenario A: rating pertama berhasil.
  - Skenario B: cookie+ip sama, fingerprint berubah → **ditolak**.
  - Skenario C: cookie+fingerprint sama, IP berubah → **ditolak**.
  - Skenario D: ip+fingerprint sama, cookie berubah → **ditolak**.
  - Skenario E: hanya 1 identifier sama (mis. cookie sama saja, IP+fingerprint beda) → **boleh**.
  - Skenario F: invalid payload (rating di luar 1–5 / fingerprint kosong) → **422**.
- Teknik test:
  - IP via `withServerVariables(['REMOTE_ADDR' => '...'])`
  - Cookie via `withCookie('rl_rating_id', '...')`

## Dampak Minimal ke Sistem Lain
- Tidak mengubah flow jadwal-lari selain menambahkan rating widget di halaman detail.
- Tidak menambah dependency baru (menggunakan JS native + hashing server-side).

Jika plan ini disetujui, saya lanjut implementasi: migration+model → endpoint controller+route → UI widget di `running-event-detail.blade.php` → test suite untuk skenario duplikasi 2/3.