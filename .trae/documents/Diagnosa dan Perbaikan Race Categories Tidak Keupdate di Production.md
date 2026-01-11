## Temuan Cepat (dari code)
- Update Race Categories terjadi di [EventController::update](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php#L258-L558) lewat payload nested `categories[0][...]`.
- Field yang disimpan termasuk `prizes` (JSON) karena form mengirim `categories[i][prizes][1..3]` ([edit.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/edit.blade.php#L616-L673)).
- Ada migrasi baru yang menambah kolom `race_categories.prizes` ([2026_01_10_133742_add_prizes_to_race_categories_table.php](file:///c:/laragon/www/ruanglari/database/migrations/2026_01_10_133742_add_prizes_to_race_categories_table.php#L12-L17)). Jika kolom ini belum ada di production, update category akan 500 dan terasa “tidak keupdate”.

## Hipotesis Paling Mungkin (kenapa hanya production)
1. **Migrasi `prizes` belum ter-apply di production** → request membawa `prizes`, DB melempar error “Unknown column prizes”.
2. **View cache / deployment versi view berbeda** → nama field yang dikirim tidak sesuai rule backend (mis. field lama vs field baru), sehingga data tidak ikut ter-validate dan tidak ikut di-update.
3. **Request terpotong karena limit PHP (`max_input_vars`, `post_max_size`)** → sebagian `categories[...]` hilang, update jadi tidak konsisten/terasa tidak berubah.

## Langkah Diagnosa (tanpa perubahan kode dulu)
1. **Cek response saat Save di production**
   - Dari DevTools Network: pastikan request update event tidak 500.
   - Di Console: cek error JS (kalau script berhenti, input `name` untuk categories bisa tidak kebentuk).
2. **Cek log production**
   - Cari `SQLSTATE` terutama “Unknown column 'prizes'” atau error terkait JSON.
3. **Verifikasi schema production untuk `race_categories`**
   - Pastikan ada kolom `prizes` (JSON/longtext tergantung MySQL) sesuai migrasi.
4. **Verifikasi view cache**
   - Jika production memakai `php artisan view:cache`, pastikan view sudah di-clear / di-regenerate setelah deploy (indikasi: markup field categories tidak sesuai code saat ini).
5. **Verifikasi limit input PHP**
   - Jika event punya banyak field + banyak kategori, cek `max_input_vars` (default 1000) dan `post_max_size`.

## Perbaikan yang Akan Saya Implementasikan (setelah kamu konfirmasi)
1. Bungkus update event + categories dalam **DB transaction** supaya tidak terjadi partial update.
2. Ubah update kategori agar lebih aman:
   - Ambil kategori existing via relasi `$event->categories()` (bukan `RaceCategory::find`).
   - Hindari delete massal jika payload categories terindikasi tidak lengkap (mitigasi kasus `max_input_vars`).
3. Tambahkan invalidasi cache yang lebih lengkap bila diperlukan (mis. invalidate cache quota category yang berubah) menggunakan [EventCacheService](file:///c:/laragon/www/ruanglari/app/Services/EventCacheService.php#L118-L159).

## Verifikasi Setelah Fix
- Test update kategori: tambah, edit, hapus, dan update hadiah.
- Pastikan hasil terlihat di edit page dan public page (public show selalu query kategori aktif dari DB di [PublicEventController::show](file:///c:/laragon/www/ruanglari/app/Http/Controllers/PublicEventController.php#L71-L137)).