Saya akan menambahkan fitur **Race Predictor** dan **Runner Archetype** ke dalam Share Stats agar terlihat lebih premium dan menarik untuk dibagikan.

### Langkah Implementasi:

1.  **Update Logika Vue.js (`resources/views/calendar/index.blade.php`):**
    *   Membuat fungsi `calculateRacePredictions()` menggunakan rumus Riegel sederhana berdasarkan *Best Pace* user.
    *   Membuat fungsi `determineArchetype()` untuk menganalisis waktu lari (pagi/sore) dan performa (cepat/jauh) guna menentukan julukan.

2.  **Update UI Share Stats:**
    *   **Menambahkan Panel "Race Potential":** Di bawah grafik mingguan, menampilkan prediksi waktu 5K, 10K, 21K, 42K dalam format grid modern.
    *   **Menambahkan Badge Archetype:** Di sebelah nama user atau di pojok kartu profil, menampilkan ikon dan julukan (misal: "🌅 Early Bird" atau "⚡ Speedster").

3.  **Styling:**
    *   Menggunakan desain *glassmorphism* (transparan gelap) agar menyatu dengan tema "PacerHub".
    *   Warna aksen emas/neon untuk menonjolkan angka prediksi.

Hasilnya, gambar yang dibagikan akan memuat informasi pro-level yang biasanya hanya ada di aplikasi berbayar.