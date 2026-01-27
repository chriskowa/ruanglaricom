# Rencana Implementasi Metode Pembayaran Moota (Bank Transfer)

Berikut adalah rencana komprehensif untuk mengintegrasikan Moota sebagai opsi pembayaran transfer bank otomatis menggunakan sistem Kode Unik.

## 1. Analisis & Perencanaan

*   **Model Pembayaran**: Menggunakan **Sistem Kode Unik**.
    *   Setiap transaksi akan ditambahkan kode unik 3 digit acak (misal: Harga Rp 150.000 + Kode 123 = Total Rp 150.123).
    *   Moota akan mendeteksi mutasi masuk sebesar Rp 150.123 dan sistem akan otomatis mencocokkan dengan transaksi tersebut.
*   **Struktur Data**: Perlu penyesuaian tabel `transactions` untuk mendukung multi-gateway (bukan hanya Midtrans).
*   **Skenario**:
    *   **Full Payment**: User transfer nominal tepat.
    *   **Kelebihan/Kekurangan**: Sistem Moota biasanya strict match. Jika user transfer tanpa kode unik, perlu penanganan manual (admin).

## 2. Desain Arsitektur Sistem

### Alur Pembayaran
1.  **User Checkout**: Pilih "Transfer Bank (Verifikasi Otomatis)".
2.  **System**:
    *   Hitung `unique_code` (1-999) yang belum terpakai untuk nominal tersebut dalam range waktu tertentu.
    *   Set `final_amount` = `total_original` + `unique_code`.
    *   Simpan `payment_gateway = 'moota'` dan `unique_code` di database.
3.  **UI**: Redirect ke Halaman Instruksi Pembayaran (Tampilkan No Rekening & **Total Nominal Wajib**).
4.  **User Transfer**: User melakukan transfer via ATM/M-Banking.
5.  **Moota Webhook**: Moota mendeteksi dana masuk -> kirim webhook ke Server.
6.  **System Validation**: Server cek Signature & Nominal -> Update status `paid` -> Kirim Email Sukses.

### Struktur Database
Modifikasi tabel `transactions`:
*   `payment_gateway`: `enum('midtrans', 'moota')` (Default: midtrans)
*   `unique_code`: `integer` (Nullable, menyimpan 3 digit kode)
*   `payment_channel`: `string` (Misal: 'bca', 'mandiri' - opsi jika user memilih bank tujuan spesifik)

## 3. Implementasi Keamanan

*   **Signature Verification**: Memverifikasi header/payload dari webhook Moota menggunakan Secret Key.
*   **IP Whitelisting**: (Opsional) Hanya terima webhook dari IP server Moota.
*   **Input Validation**: Sanitasi data webhook.
*   **Audit Log**: Mencatat setiap request webhook ke tabel `payment_logs` untuk debugging.

## 4. Langkah Integrasi

### A. Backend Development
1.  **Migration**: Tambah kolom ke tabel `transactions`.
2.  **Config**: Buat `config/moota.php` untuk menyimpan API Key & Bank Accounts.
3.  **MootaService**:
    *   `generateUniqueCode()`: Generate kode unik & pastikan tidak bentrok.
    *   `verifySignature()`: Validasi webhook.
4.  **Controller**:
    *   `MootaWebhookController`: Endpoint penerima notifikasi.
    *   Update `EventRegistrationController`: Logic untuk memilih gateway Moota.

### B. Frontend Development
1.  **Halaman Checkout**: Tambah opsi radio button "Bank Transfer".
2.  **Halaman Instruksi**: Buat view `payment-instruction.blade.php` yang menampilkan:
    *   Nomor Rekening (BCA/Mandiri milik Merchant).
    *   **Nominal Transfer** (Besar & Jelas).
    *   Timer (Misal 24 jam) sebelum kode unik kadaluarsa.

## 5. Rencana Eksekusi (To-Do List)
Saya akan membagi implementasi menjadi 3 fase utama: **Database & Config**, **Logic & API**, dan **Frontend Integration**.
