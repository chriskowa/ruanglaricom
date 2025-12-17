## Tujuan
- Menangani lonjakan pendaftar dengan latensi rendah dan error minimal.
- Mengurangi beban memori dan koneksi DB pada puncak trafik.
- Opsional: Integrasi Firebase untuk real‑time atau sebagai penyangga (buffer) saat peak.

## Konteks Saat Ini
- Backend: Laravel (MySQL sebagai DB utama).
- Cache/Queue/Session: driver `database` (bukan Redis) — berpotensi membebani MySQL saat trafik tinggi (`config/cache.php`, `config/queue.php`, `config/session.php`).
- Redis sudah dikonfigurasi namun bukan default (`config/database.php` bagian `redis`).

## Strategi Utama
- Prioritaskan MySQL sebagai source of truth; gunakan Redis untuk antrian, cache, rate limiting.
- Terapkan arsitektur write‑path yang tahan lonjakan: idempoten, ter‑throttle, berantrian, dan ber‑index baik.

## Hardening Endpoint Registrasi
- Tambah rate limiting per IP/user menggunakan middleware `ThrottleRequests` atau `RateLimiter` di routes.
- Idempotensi submit: gunakan `unique index` pada kolom identitas pendaftar dan `upsert`/`firstOrCreate` untuk cegah duplikasi.
- Validasi ringan dan cepat; hindari loading data besar di memory; kirimkan pekerjaan berat ke queue.

## Optimisasi Memori Aplikasi
- Jadikan server stateless; simpan session di Redis (alih‑driver dari `database` ke `redis`).
- Hindari operasi yang memuat banyak baris di RAM; gunakan `cursor()`/`chunkById()` untuk pekerjaan batch.
- Streaming response/log yang perlu; batasi payload dan kolom yang di‑select.

## Antrian & Backpressure
- Ganti `QUEUE_CONNECTION` dari `database` ke `redis` untuk throughput lebih tinggi.
- Kirim proses pasca‑registrasi (email/whatsapp/integrasi) ke queue; gunakan retry dan dead‑letter.
- Terapkan backpressure: jika DB melambat, antrian menahan beban, bukan endpoint.

## Database & Indexing
- Tambah indeks tepat pada kolom pencarian dan unik pada identitas pendaftar.
- Tulis sesingkat mungkin (hindari JOIN tidak perlu); gunakan transaksi seperlunya.
- Gunakan connection pooling; kurangi waktu hidup koneksi; atur `max_connections` dan pool.

## Integrasi Firebase: Dua Pola
- Pola A (Mirror Real‑Time):
  - Tetap tulis utama ke MySQL; worker mem‑publish event ke Firebase (Firestore/Realtime DB) untuk UI live dan antrean notifikasi.
  - Konsistensi kuat di MySQL, Firebase sebagai read‑optimized mirror.
- Pola B (Buffer Saat Peak):
  - Tulis cepat ke Firestore terlebih dulu saat lonjakan; worker menyerap ke MySQL secara batch dengan idempotensi.
  - Terima konsistensi akhirnya (eventual consistency) dengan audit log.

## Observabilitas & Proteksi Lonjakan
- Metrics: request rate, p95 latensi, error rate, queue lag, DB QPS.
- Alert saat queue backlog melewati ambang dan saat error melonjak.
- Circuit breaker/fallback pesan antrian penuh agar UX jelas.

## Uji Beban
- Siapkan skenario k6/Locust (ramp‑up ke beberapa ribu RPS, skenario retry).
- Ukur p95/p99 latensi dan memory footprint sebelum dan sesudah perubahan.

## Perubahan Konfigurasi (Ringkas)
- `.env`: ubah `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`.
- `config/cache.php`, `config/queue.php`, `config/session.php`: pastikan koneksi Redis aktif; gunakan `phpredis` sesuai `config/database.php`.
- Tambah middleware rate‑limit di route registrasi.

## Rollout Bertahap
- Jalankan canary untuk sebagian traffic terlebih dulu.
- Tampilkan banner sistem sibuk saat backlog tinggi.
- Log audit untuk semua write path.

## Hasil yang Diharapkan
- Penurunan beban MySQL signifikan pada peak (queue & cache pindah ke Redis).
- Latensi stabil, memori aplikasi lebih rendah karena operasi berat dialihkan ke queue.
- Firebase tersedia untuk real‑time dan/atau buffer sesuai pola yang dipilih.

## Permintaan Konfirmasi
- Pilih pola Firebase: A (mirror real‑time) atau B (buffer saat peak).
- Setujui pengalihan cache/session/queue ke Redis dan penambahan rate limit + idempotensi di endpoint registrasi.