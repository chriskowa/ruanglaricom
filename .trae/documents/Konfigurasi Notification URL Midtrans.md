Untuk Notification URL endpoint Midtrans, URL yang harus digunakan adalah:

**`https://app.ruanglari.com/events/transactions/webhook`**

Namun, agar notifikasi dari Midtrans dapat diterima oleh sistem Laravel tanpa terblokir, kita perlu menonaktifkan proteksi CSRF untuk rute tersebut. Saat ini konfigurasi tersebut belum ada di `bootstrap/app.php`.

Berikut rencana perubahannya:

1. **Modifikasi** **`bootstrap/app.php`**:

   * Menambahkan pengecualian CSRF (VerifyCsrfToken) untuk rute webhook.

   * Rute yang akan dikecualikan:

     * `events/transactions/webhook` (Untuk Event)

     * `marketplace/webhook` (Untuk Marketplace, opsional tapi disarankan)

     * `wallet/topup/callback` (Untuk Wallet, opsional tapi disarankan)

2. **Verifikasi**:

   * Memastikan kode `bootstrap/app.php` valid.

   * Menjelaskan kepada Anda langkah selanjutnya untuk input di dashboard Midtrans.

