# Audit Alur Registrasi Event

## Ringkasan Eksekutif
Audit ini mencakup analisis alur registrasi mulai dari input data peserta, validasi, penyimpanan database, hingga konfirmasi pembayaran. Ditemukan beberapa potensi masalah integritas data terkait *race condition* pada validasi duplikasi peserta dan manajemen slot (quota).

## Temuan Rinci

### 1. Potensi Double Booking (Race Condition)
*   **Lokasi:** `App\Actions\Events\StoreRegistrationAction.php` (Line 236-249)
*   **Deskripsi:** Sistem melakukan pengecekan manual (`Participant::where(...)->exists()`) untuk mencegah pendaftaran ganda berdasarkan `id_card` dan `category_id`. Namun, pengecekan ini dilakukan di level aplikasi sebelum transaksi database dibuat.
*   **Risiko:** Jika dua request masuk secara bersamaan (concurrent), keduanya bisa lolos pengecekan `exists()` sebelum salah satu data tersimpan, menyebabkan duplikasi peserta.
*   **Rekomendasi:** Tambahkan *unique constraint* pada level database untuk kombinasi `(race_category_id, id_card)` dengan kondisi status aktif, atau gunakan `lockForUpdate()` saat pengecekan.

### 2. Manajemen Kuota Slot Tidak Atomik
*   **Lokasi:** `App\Models\RaceCategory.php` method `getRemainingQuota` dan `StoreRegistrationAction`.
*   **Deskripsi:** Perhitungan sisa kuota dilakukan dengan menghitung jumlah peserta secara real-time (`count()`). Tidak ada mekanisme *row locking* pada tabel `race_categories` saat slot dipesan.
*   **Risiko:** *Overselling* (penjualan melebihi kuota) sangat mungkin terjadi jika traffic tinggi mendekati batas kuota.
*   **Rekomendasi:** Implementasikan mekanisme *atomic decrement* pada kolom `quota_used` atau gunakan *pessimistic locking* (`lockForUpdate`) pada row kategori saat proses registrasi.

### 3. Sinkronisasi Status Pembayaran
*   **Lokasi:** `App\Http\Controllers\EventTransactionWebhookController.php` & `App\Jobs\ProcessPaidEventTransaction.php`.
*   **Deskripsi:** Status pembayaran bergantung sepenuhnya pada webhook. Jika webhook gagal atau tertunda, status peserta tetap 'pending'.
*   **Risiko:** Peserta yang sudah bayar tidak mendapatkan konfirmasi (BIB number) jika job antrian gagal.
*   **Rekomendasi:** Implementasikan mekanisme *retry* yang kuat untuk webhook dan job processing, serta *reconciliation cron job* untuk memverifikasi status transaksi yang menggantung.

### 4. Validasi BIB Number
*   **Lokasi:** `App\Jobs\ProcessPaidEventTransaction.php` (Line 92).
*   **Deskripsi:** Pembuatan nomor BIB menggunakan loop `while` dengan pengecekan `exists()`.
*   **Risiko:** *Race condition* saat generate BIB number jika banyak job berjalan paralel, meskipun kemungkinannya kecil karena format BIB cukup unik.
*   **Rekomendasi:** Gunakan *sequence* database atau mekanisme *locking* yang lebih aman.

## Kesimpulan
Secara umum alur logika bisnis sudah berjalan baik, namun aspek *concurrency control* perlu ditingkatkan untuk mencegah inkonsistensi data pada beban tinggi.

---

# Rencana Implementasi Fitur Laporan (Next Steps)

Sesuai permintaan, akan diimplementasikan komponen card laporan pada halaman `/eo/events/{id}/participants` dengan spesifikasi:
1.  **Metrik Real-time:** Total Slot, Terjual, Breakdown Kategori, Persentase.
2.  **Fitur:** Filter (Tanggal, Tipe Tiket), Export (Excel/PDF), Warning Indicator (<10%).
3.  **Teknis:** Menggunakan `EventReportService` dengan caching untuk performa.
