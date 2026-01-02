# Rencana Pengembangan Marketplace RuangLari

Rencana ini dirancang untuk membangun marketplace internal yang aman, modern, dan mudah digunakan bagi Runner dan Coach, dengan RuangLari sebagai penengah (Escrow) yang mengambil komisi otomatis.

## 1. Arsitektur Database (Schema)
Kita akan membuat tabel baru untuk mendukung produk fisik (gear) dan digital (slot lari).

*   **`marketplace_categories`**: Kategori produk (Jersey, Sepatu, Aksesoris, Slot Event).
*   **`marketplace_products`**:
    *   `user_id` (Penjual: Runner/Coach).
    *   `category_id`.
    *   `type` (Enum: 'physical', 'digital_slot').
    *   `title`, `description`, `price`, `stock`, `condition` (New/Used).
    *   `meta_data` (JSON: untuk menyimpan ukuran, warna, atau detail tiket lari).
*   **`marketplace_product_images`**: Galeri foto produk.
*   **`marketplace_orders`**: Transaksi utama.
    *   `buyer_id`, `total_amount`, `commission_fee` (Potongan admin), `seller_amount` (Yang diterima penjual).
    *   `status` (pending, paid, shipped, completed, cancelled, disputed).
    *   `snap_token` (Midtrans).
*   **`app_settings`** (Baru): Tabel key-value untuk menyimpan konfigurasi dinamis, seperti `marketplace_commission_percentage` (Default: 1%).

## 2. Backend & Logika Bisnis (Laravel)
*   **Product Management**: CRUD untuk penjual menambah produk. Validasi khusus untuk "Slot Event" (misal: wajib cantumkan nama event & kategori).
*   **Transaction Logic (Escrow System)**:
    1.  **Pembayaran**: Pembeli membayar via Midtrans -> Status `paid`.
    2.  **Penahanan Dana**: Uang tercatat masuk ke sistem RuangLari.
    3.  **Pengiriman**: Penjual input resi (fisik) atau bukti transfer slot (digital) -> Status `shipped`.
    4.  **Penyelesaian**: Pembeli konfirmasi terima -> Status `completed`.
    5.  **Pencairan**: Sistem otomatis menambahkan saldo ke `Wallet` penjual: `Harga - Komisi Admin`.
*   **Admin Config**: Controller untuk Admin mengubah persentase komisi kapan saja.

## 3. User Interface & Experience (UI/UX)
Menggunakan Tailwind CSS dan Vue.js (sesuai stack yang ada) untuk pengalaman yang fluid.

### A. Marketplace Hub (Halaman Utama)
*   **Layout**: Grid responsif card produk dengan foto besar.
*   **Filtering**: Sidebar filter canggih (Kategori, Rentang Harga, Kondisi, Lokasi Penjual).
*   **Badge**: Penanda khusus untuk "Verified Seller" atau "Coach" agar lebih terpercaya.

### B. Halaman Detail Produk
*   **Galeri**: Image slider/lightbox.
*   **Seller Info**: Avatar penjual, rating (jika ada), dan tombol chat/WA.
*   **Action**: Tombol "Beli Sekarang" atau "Tambah ke Keranjang".

### C. Dashboard Penjual (Manage Produk)
*   **Upload**: Drag-and-drop foto.
*   **Form Simpel**: Pilihan kategori otomatis mengubah field input (misal: pilih 'Sepatu' muncul opsi Size, pilih 'Slot' muncul opsi Transfer Fee).

### D. Dashboard Admin
*   **Setting Komisi**: Input sederhana untuk mengubah % fee.
*   **Transaction Monitor**: Tabel riwayat transaksi dengan detail potongan fee.

## 4. Tahapan Implementasi
1.  **Database Migration**: Membuat tabel-tabel yang dibutuhkan.
2.  **Admin Setup**: Membuat fitur setting komisi.
3.  **Product CRUD**: Fitur upload produk bagi User.
4.  **Frontend Gallery**: Halaman browsing produk.
5.  **Checkout & Payment**: Integrasi Midtrans untuk order marketplace.
6.  **Order Management**: Flow konfirmasi terima barang & pembagian saldo (Wallet).

Apakah Anda setuju dengan rencana struktur ini?