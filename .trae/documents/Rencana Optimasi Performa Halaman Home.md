Analisis saya menunjukkan 3 penyebab utama halaman Home terasa berat/lemot:

1.  **Query Database "Bocor" di View**: Ada perhitungan jumlah user (`User::count()`) yang dijalankan langsung di file tampilan (Blade), melewati sistem *cache* yang sudah Anda buat. Ini memaksa database bekerja setiap kali halaman dibuka.
2.  **Aset Eksternal Berlebihan**: Halaman memuat banyak gambar dari server lain (`pravatar.cc`, `wikimedia`, `cloudinary`). Jika salah satu server ini lambat, website Anda ikut menunggu (loading berputar terus).
3.  **Masalah N+1 Query**: Data program pelatih diambil satu-per-satu (lazy loading) di tampilan, bukan sekaligus di awal.

---

### **Rencana Perbaikan (Step-by-Step)**

**Langkah 1: Optimasi Backend (Logic)**
*   Pindahkan query hitung user dari `index.blade.php` ke `routes/web.php`.
*   Masukkan perhitungan tersebut ke dalam sistem Cache (update tiap 1 jam) agar database lebih ringan.
*   Perbaiki pengambilan data "Top Coach" agar langsung membawa jumlah programnya (Eager Loading) untuk mencegah query berulang.

**Langkah 2: "Hide" Fitur/Elemen Berat (Sesuai Request)**
*   **Sembunyikan Section "Brands"** (Logo Nike, Adidas, dll): Mengambil gambar dari Wikimedia yang ukurannya besar dan tidak stabil.
*   **Sembunyikan "Social Proof"** (Foto-foto user bulat kecil): Menggunakan `pravatar.cc` yang sering lambat merespon request.

**Langkah 3: Pembersihan View**
*   Ganti query database di view dengan variabel siap pakai dari controller.
*   Hapus/komentari kode HTML untuk elemen yang disembunyikan.

Setelah perbaikan ini, halaman Home akan memuat jauh lebih cepat karena:
1.  Tidak ada query database berat saat load (semua dari cache).
2.  Ketergantungan pada gambar server luar berkurang drastis.
