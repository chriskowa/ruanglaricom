## Ringkas
- MySQL tetap untuk data utama (.env DB_* sudah benar).
- Redis dipakai untuk `cache`, `queue`, dan `session` agar beban MySQL turun dan kunci (`Cache::lock`) di registrasi bekerja atomik saat trafik tinggi.

## Penjelasan Singkat Redis
- In‑memory data store dengan latensi sangat rendah.
- Cocok untuk cache, antrian, dan session. Tidak menggantikan MySQL (data transaksi tetap di MySQL).
- Di kode Anda, lock kuota menggunakan `Cache::lock` (app/Actions/Events/StoreRegistrationAction.php:80–86); fitur lock ini idealnya memakai Redis agar benar‑benar atomik.

## Langkah Implementasi
1. Pasang Redis Server (Windows)
   - Opsi A: Docker
     - `docker run -d --name redis -p 6379:6379 redis:7`
   - Opsi B: WSL Ubuntu
     - `sudo apt update && sudo apt install redis-server`
     - Pastikan listen ke `127.0.0.1:6379` (default) dan service aktif: `sudo systemctl status redis-server`
   - Opsi C: Memurai (Redis for Windows)
     - Install Memurai, jalankan service pada port `6379`.

2. Aktifkan ekstensi PHP `phpredis`
   - Buka `php.ini` Laragon (`c:\laragon\bin\php\php-<versi>\php.ini`) dan aktifkan: `extension=php_redis.dll`.
   - Restart Apache/Nginx di Laragon.

3. Konfigurasi `.env`
   - Pastikan:
     - `REDIS_CLIENT=phpredis`
     - `REDIS_HOST=127.0.0.1`
     - `REDIS_PORT=6379`
   - Ubah driver agar memakai Redis:
     - `CACHE_STORE=redis`
     - `QUEUE_CONNECTION=redis`
     - `SESSION_DRIVER=redis`
   - Biarkan DB MySQL seperti sekarang (tidak berubah):
     - `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=ruanglariweb`, `DB_USERNAME=root`, `DB_PASSWORD=`

4. Sinkronisasi konfigurasi
   - Jalankan: `php artisan config:clear` lalu `php artisan config:cache`.

5. Verifikasi
   - Cache:
     - `php artisan tinker`
     - `Cache::store('redis')->put('foo','bar',60);`
     - `Cache::store('redis')->get('foo'); // 'bar'`
   - Lock atomik:
     - `Cache::lock('test-lock', 10)->get()` harus mengunci; percobaan kedua dalam 10 detik harus gagal.
   - Queue:
     - `php artisan queue:work redis -v` lalu kirim job (mis. email) dan pastikan dieksekusi.
   - Session:
     - Login, lalu cek via `redis-cli keys "*laravel*"` (akan terlihat key session jika Redis CLI tersedia).

6. Dampak pada Registrasi
   - Kode lock kuota di `StoreRegistrationAction` (app/Actions/Events/StoreRegistrationAction.php:79–96) kini bekerja atomik dengan Redis, menghindari race condition saat pendaftar banyak.
   - Rate limiting sudah aktif di route registrasi (`routes/web.php:64`) sehingga beban request menurun saat burst.

7. Opsional: Laravel Horizon
   - `composer require laravel/horizon`
   - `php artisan horizon:install && php artisan migrate`
   - Jalankan: `php artisan horizon` untuk monitoring antrian.

8. Catatan Produksi
   - Atur `maxmemory` dan eviction policy (`volatile-ttl`) di redis.conf untuk mencegah kehabisan memori.
   - Pertimbangkan AOF persistence jika ingin durability minimal untuk queue/session.
   - Pastikan firewall hanya membuka `6379` ke host yang perlu.

## Rollback Aman
- Jika ada masalah, kembalikan `.env` ke: `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `SESSION_DRIVER=database` lalu `php artisan config:clear && php artisan config:cache`.

Siap mengeksekusi langkah di atas (menyalakan Redis, mengaktifkan phpredis, mengubah `.env`, dan verifikasi) begitu Anda setuju.