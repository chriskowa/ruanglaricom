Saya akan mengubah tampilan modal detail workout di `calendar_modern.blade.php` untuk memberikan pengalaman "Runna-style", khususnya untuk latihan Strength Training.

**Rencana Implementasi:**

1.  **Deteksi Tipe Latihan Otomatis:**
    *   Menambahkan logika di modal untuk membedakan tampilan antara `Run` (Lari) dan `Strength` (Kekuatan).
    *   Jika tipe adalah `strength`, modal akan berubah menjadi mode "Strength Trainer".

2.  **Transformasi UI Strength Training:**
    *   **Overview Tab:** Menampilkan ringkasan latihan (Muscle Groups, Equipment, Est. Duration).
    *   **Exercise List (Playlist Style):** Mengubah daftar teks statis menjadi daftar kartu interaktif.
        *   Jika data terstruktur (`strength.plan`) tersedia, akan ditampilkan per gerakan.
        *   Jika hanya deskripsi teks, saya akan membuat *parser sederhana* untuk mencoba memecahnya menjadi daftar gerakan (misal: memisahkan baris baru/koma).

3.  **Fitur Simulasi & Animasi (Runna-like):**
    *   **Mode "Guided Workout":** Menambahkan tombol "Start Workout" yang mengubah modal menjadi "Player Mode".
    *   **Tampilan Player:**
        *   Menampilkan **satu gerakan** pada satu waktu dengan fokus penuh.
        *   **Timer Visual:** Lingkaran waktu atau progress bar animasi untuk durasi set/istirahat.
        *   **Placeholder Animasi:** Menambahkan slot UI untuk menampilkan GIF/Video gerakan (saya akan gunakan placeholder visual atau CSS animation sederhana sebagai simulasi karena kita belum punya library aset video gerakan).
    *   **Interaktif:** Tombol "Next Set", "Rest Timer", dan checklist otomatis saat set selesai.

4.  **Penyempurnaan Data:**
    *   Memastikan data dari JSON `program` atau `description` biasa bisa diterjemahkan ke format yang bisa "dimainkan" oleh player ini.

Apakah Anda setuju dengan rencana perombakan modal ini?