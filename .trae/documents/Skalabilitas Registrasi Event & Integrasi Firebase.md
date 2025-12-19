## Tujuan
- Menjaga registrasi tetap responsif saat pengunjung membludak.
- Mengurangi beban memori/server dan load MySQL.
- Menetapkan alur Git yang aman agar perubahan lokal tidak bentrok dengan remote.

## Ringkasan Kondisi Saat Ini
- Backend: Laravel + Inertia/Vue, bundler Vite.
- Database utama: MySQL.
- `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `SESSION_DRIVER=database` (membebani MySQL saat trafik tinggi).
- Tidak ada Horizon/Telescope/Pulse terpasang.

## Rencana Teknis (Backend & Infrastruktur)
1. Pindahkan cache, queue, dan session ke Redis
   - Pasang Redis dan set `.env`: `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`.
   - Gunakan `phpredis` (sudah tersedia di config) untuk performa.
   - Manfaat: kurangi query ke MySQL, throughput lebih tinggi.

2. Tambahkan Laravel Horizon untuk antrian
   - Install dan konfigurasi Horizon untuk memonitor job.
   - Pindahkan pekerjaan berat ke job: email, invoice, integrasi pihak ketiga (Midtrans/Strava), pembuatan tiket.
   - Terapkan retry dan dead-letter queue.

3. Terapkan rate limiting dan proteksi endpoint registrasi
   - Middleware `ThrottleRequests` per-IP dan per-endpoint.
   - Captcha/Turnstile untuk cegah bot.

4. Idempoten dan tahan duplikasi
   - Token idempoten (mis. `X-Idempotency-Key`) atau kunci unik (email/no telp + event) di DB.
   - Safe retry di job tanpa membuat duplikasi.

5. Optimasi MySQL untuk beban puncak
   - Index yang tepat pada kolom unik/lookup.
   - Hindari transaksi panjang; gunakan insert cepat dan proses asinkron.
   - Gunakan `chunk`, `cursor`, `lazy` untuk proses data besar di worker.

6. Static asset & caching
   - Pastikan Vite build dengan cache headers via web server/CDN.
   - Cache konten read-heavy (jadwal, leaderboard) di Redis dengan TTL.

7. Observability & alerting
   - Pasang minimal logging & metrik (Pulse atau Sentry) untuk memantau throughput, waktu respon, error rate.

8. Opsional performa lanjut
   - Evaluasi Laravel Octane (RoadRunner/Swoole) jika server mendukung untuk konsumsi memori lebih rendah per request.

## Rencana Integrasi Firebase (Jika Diperlukan)
- Gunakan Firebase untuk real-time notifikasi/status (presence, progress), bukan untuk transaksi inti registrasi.
- Skema: MySQL tetap source of truth; publish event (via queue) ke Firebase untuk UI real-time (FCM/Firestore/Realtime DB).
- Hindari penulisan ganda transaksi ke dua datastore; gunakan pola event-driven.

## Alur Git Aman
1. Jelaskan status
   - "Branch behind 1 commit" artinya remote punya 1 commit yang belum ada di lokal; bisa fast-forward.
   - "Changes to be committed" artinya file `.github/workflows/deploy.yml` sudah di-stage untuk commit.
2. Langkah aman (pilih salah satu):
   - Commit dulu, lalu rebase pull:
     - `git commit -m "Update deploy workflow"`
     - `git pull --rebase`
     - Selesaikan konflik jika ada, lalu `git push`.
   - Atau stash perubahan dulu:
     - `git stash --include-untracked`
     - `git pull`
     - `git stash pop`
     - Komit dan push.
   - Jika ingin batalkan perubahan:
     - `git restore --staged .github/workflows/deploy.yml`
     - `git checkout -- .github/workflows/deploy.yml`

## Deliverables
- Konfigurasi `.env` untuk Redis (cache/queue/session) dan Horizon.
- Middleware rate limit & idempoten pada endpoint registrasi.
- Job antrian untuk pekerjaan berat + monitoring Horizon.
- Dokumentasi alur Git singkat untuk tim.

Silakan konfirmasi rencana ini. Setelah disetujui, saya akan menerapkan perubahan konfigurasi, menambahkan paket yang diperlukan, dan menyusun kode serta skrip yang relevan.