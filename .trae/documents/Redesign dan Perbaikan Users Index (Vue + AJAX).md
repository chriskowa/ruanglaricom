## Ringkasan
Halaman `users/index.blade.php` akan didesain ulang dengan UI/UX modern (Dark Neon) dan seluruh interaksi (filter, follow/unfollow) berjalan tanpa reload menggunakan Vue + AJAX. Link profil menggunakan `username`, avatar memuat dari `APP_URL/storage/...`, dan lokasi menampilkan `city` hasil relasi.

## Perbaikan Filter & Data
- Pastikan parameter `gender` dan `city_id` selalu dikirim saat filter diubah; gunakan watcher `filters.gender` dan `filters.city_id` untuk memanggil `fetchUsers()` otomatis.
- Tampilkan lokasi dari relasi `city` (`user.city.name`). Jika `city` null, tampilkan status fallback.
- Gunakan `users.data` dari respons JSON paginate untuk grid.

## Avatar & URL Absolut
- Tambahkan konstanta `APP_URL = '{{ url('/') }}'` di view.
- Ubah `src` avatar: `user.avatar ? APP_URL + '/storage/' + user.avatar : defaultMale/ defaultFemale`.
- Ubah tautan profil agar selalu memakai `username`: `'/runner/' + user.username'` (jika `username` tidak ada, tampilkan tombol disabled/tooltip).

## Follow/Unfollow via AJAX
- Gunakan endpoint yang benar: `POST /follow/{id}` dan `POST /unfollow/{id}`.
- Tambahkan loading state per-user (spinner) dan toggle `user.is_following` di client saat request sukses.
- Tangani error (mis. tidak boleh follow diri sendiri) dengan notifikasi ringan.

## Chat URL
- Gunakan `'/chat/' + user.id'` sesuai binding `User $user` di route `chat/{user}`.

## UX Modern
- Header hero dengan neon gradient dan glass panels.
- Grid kartu user dengan hover glow, badge gender, dan badge jumlah program untuk coach.
- Skeleton loading saat fetch berlangsung.

## Implementasi Teknis
- Edit `resources/views/users/index.blade.php`:
  - Inject `APP_URL`, ganti formula avatar, ganti tautan profil ke `username`.
  - Tambah watcher untuk `filters.gender` dan `filters.city_id` agar otomatis memanggil `fetchUsers()`.
  - Pastikan `fetchUsers()` mengirim `Accept: application/json`.
  - Perbaiki tombol Follow/Unfollow agar memanggil endpoint yang benar via AJAX dan update state lokal.

## Verifikasi
- Uji filter gender dan lokasi: pilih `male/female` dan kota, pastikan hasil berubah tanpa reload.
- Uji avatar tampil: pengguna dengan avatar tampil absolut dari `APP_URL/storage/...`; tanpa avatar gunakan SVG lokal.
- Uji follow/unfollow: status tombol berubah; backend mengembalikan JSON sesuai `FollowController`.
- Uji link profil: membuka `runner/{username}`.
- Uji chat: membuka `chat/{id}`.

Konfirmasi untuk menjalankan perubahan di atas.