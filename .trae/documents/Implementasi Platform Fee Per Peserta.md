Untuk menambahkan fitur "Biaya Tambahan Per Peserta" (Platform Fee), saya akan melakukan perubahan berikut:

### 1. Database & Model
*   **Tabel `events`**: Menambahkan kolom `platform_fee` (nominal, default 0) untuk menyimpan konfigurasi biaya per peserta untuk event tersebut.
*   **Tabel `transactions`**: Menambahkan kolom `admin_fee` untuk mencatat total biaya tambahan yang dikenakan pada transaksi tersebut (agar pelaporan keuangan akurat).
*   **Model**: Update `Event.php` dan `Transaction.php` untuk mendaftarkan kolom baru ke `fillable`.

### 2. Backend Logic
*   **`StoreRegistrationAction.php`**:
    *   Mengambil nilai `platform_fee` dari event.
    *   Menghitung total fee: `platform_fee` × `jumlah_peserta`.
    *   Menambahkan total fee ke dalam kalkulasi `final_amount`.
    *   Menyimpan nilai fee ke kolom `admin_fee` di tabel transaksi.

### 3. Dashboard Admin/EO
*   **Create & Edit Event**: Menambahkan input field "Platform Fee (Per Participant)" pada form pembuatan dan pengeditan event. Ini memungkinkan Anda mengatur biaya yang berbeda-beda untuk setiap event.

### 4. Tampilan Publik (Frontend)
*   Update ketiga tema (**Modern Dark**, **Light Clean**, **Simple Minimal**) untuk:
    *   Menampilkan rincian "Biaya Admin/Platform" pada ringkasan pembayaran.
    *   Mengupdate logika JavaScript agar total bayar otomatis bertambah saat jumlah peserta bertambah (`Total = (Harga Tiket + Fee) * Jumlah Peserta`).

Apakah Anda setuju dengan rencana implementasi ini?