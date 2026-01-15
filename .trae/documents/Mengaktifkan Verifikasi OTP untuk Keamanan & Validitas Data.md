Untuk sistem keanggotaan seperti Ruang Lari, alur **Register → OTP Verifikasi → Login** adalah standar terbaik dan paling efektif. 

### Analisis & Rekomendasi
1.  **Langsung Login (Saat ini)**:
    *   **Pro**: User senang karena cepat tanpa hambatan.
    *   **Kontra**: Database kotor (banyak email/nomor palsu), tidak bisa reset password jika email typo, rawan akun spam.
2.  **Link Konfirmasi Email**:
    *   **Pro**: Standar web klasik.
    *   **Kontra**: Sering masuk spam/promosi, user malas buka email, konversi drop (banyak yang daftar tapi tidak verifikasi).
3.  **OTP (WhatsApp/Email) - REKOMENDASI SAYA**:
    *   **Pro**: Verifikasi instan (terutama WA di Indonesia), validitas data kontak terjamin (penting untuk info event), UX modern.
    *   **Status**: Fitur ini **SUDAH ADA** di kode Anda, hanya perlu diaktifkan.

### Rencana Implementasi
Saya akan mengaktifkan fitur verifikasi yang sudah tersedia di sistem agar alur pendaftaran menjadi lebih aman dan valid.

#### Langkah 1: Konfigurasi Environment (.env)
Mengubah pengaturan berikut di file `.env` Anda:
*   Set `LOGIN_OTP_ENABLED=true` (Agar user tidak langsung login & aktif setelah daftar).
*   Set `OTP_CHANNEL=whatsapp` (Disarankan untuk respons tercepat) atau `email`.

#### Langkah 2: Verifikasi Alur
Memastikan setelah user mendaftar:
1.  Status akun (`is_active`) default `false`.
2.  User dialihkan ke halaman input OTP.
3.  Setelah OTP valid, status menjadi `active` dan user otomatis login.

Apakah Anda setuju untuk mengaktifkan fitur verifikasi OTP ini?