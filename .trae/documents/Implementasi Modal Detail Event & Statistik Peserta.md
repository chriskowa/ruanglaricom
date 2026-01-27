Saya akan mengimplementasikan perubahan ini dengan langkah-langkah berikut:

1.  **Update `App\Http\Controllers\EO\EventController.php`**:
    *   Memodifikasi method `index` untuk memuat data kategori beserta jumlah pesertanya secara efisien (Eager Loading).
    *   Saya akan menambahkan perhitungan jumlah peserta total (`participants_count`) dan peserta yang sudah bayar (`paid_participants_count`) per kategori langsung dari query database.

2.  **Update `resources/views/eo/events/index.blade.php`**:
    *   Mengubah halaman ini agar menggunakan **Alpine.js** untuk manajemen state Modal (`x-data`).
    *   Menambahkan komponen **Modal** di bagian bawah halaman (hidden by default).
    *   Modal ini akan menampilkan:
        *   **Info Event Lengkap**: Nama, Deskripsi, Tanggal, Lokasi.
        *   **Ringkasan Peserta**: Tabel yang merinci per Kategori (Nama Kategori, Harga, Kuota, Total Terdaftar, Total Paid).
    *   Mengubah tombol "Detail" (ikon mata) agar tidak lagi redirect ke halaman baru, melainkan membuka Modal dan mengisi datanya secara dinamis.

Apakah Anda setuju dengan rencana ini? (Terutama poin bahwa "data participant" yang dimaksud adalah **ringkasan statistik per kategori**, bukan list nama ribuan peserta yang akan memberatkan modal).