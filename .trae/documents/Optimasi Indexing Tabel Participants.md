Saat ini tabel `participants` **belum sepenuhnya dioptimalkan** untuk pola query yang sering digunakan di sistem Anda.

Meskipun sudah ada index dasar untuk Foreign Key (`transaction_id`, `race_category_id`, `event_package_id`) dan `bib_number`, namun masih kurang index untuk pencarian kombinasi yang sering dilakukan saat validasi registrasi.

**Analisis Kekurangan:**
1.  **Pencarian Duplikasi Peserta**: Sistem sering melakukan query: `where('race_category_id', $id)->where('id_card', $nik)`. Saat ini hanya ada index di `race_category_id`. Tanpa composite index (index gabungan), database harus memindai lebih banyak baris.
2.  **Pencarian Identitas**: Kolom `email` dan `id_card` sering digunakan untuk pencarian, namun belum memiliki index sendiri.
3.  **Filter Status**: Kolom `status` belum di-index, padahal sering digunakan untuk memfilter peserta yang sudah confirm/paid.

**Rencana Optimasi (Database Migration):**
Saya akan membuat migration baru untuk menambahkan index berikut agar performa query jauh lebih cepat:
1.  **Composite Index**: `['race_category_id', 'id_card']` -> Mempercepat validasi peserta ganda dalam satu kategori.
2.  **Single Index**: `email` -> Mempercepat pencarian peserta by email.
3.  **Single Index**: `id_card` -> Mempercepat pencarian peserta by NIK/KTP secara global.
4.  **Single Index**: `status` -> Mempercepat reporting/filtering status peserta.

Apakah Anda setuju untuk saya jalankan optimasi ini sekarang?