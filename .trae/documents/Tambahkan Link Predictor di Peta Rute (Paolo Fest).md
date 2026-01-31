## Temuan
- Di theme [paolo-fest.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/paolo-fest.blade.php#L789-L848), section **Peta Rute** hanya menampilkan tab kategori + peta GPX dan overlay stats. Tidak ada CTA/link ke halaman predictor.
- Halaman predictor sebenarnya sudah ada: [prediction.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/prediction.blade.php) dan bisa diakses lewat route `events.prediction` (`/event/{slug}/prediction`). Halaman ini juga butuh kategori yang punya `masterGpx` (ada pesan “GPX harus terhubung”).

## Kenapa Membingungkan
- User melihat “Peta Rute” (berbasis GPX) tapi tidak ada jalur UX untuk lanjut ke “Prediksi Waktu”, padahal fiturnya sudah tersedia dan relevan dengan GPX.

## Perubahan yang Akan Saya Lakukan
1. Tambahkan CTA button/link di section **Peta Rute** (paolo-fest)
   - Teks contoh: “Coba Prediksi Waktu”
   - Link: `route('events.prediction', $event->slug)`
   - Letakkan dekat judul “Peta Rute” (area header section) agar ketemu dengan cepat.

2. Kondisional tampil
   - Tampilkan CTA hanya jika `$categoriesWithGpx->count() > 0` (agar tidak mengarahkan ke predictor tanpa GPX).

3. Verifikasi
   - Pastikan halaman event paolo-fest render tanpa error.
   - Pastikan tombol menuju `/event/{slug}/prediction` dan halaman predictor bisa dibuka.

## Output
- UX “Peta Rute” punya jalur langsung ke predictor, dan tetap aman karena hanya muncul saat GPX tersedia.