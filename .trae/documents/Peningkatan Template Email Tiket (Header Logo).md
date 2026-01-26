Baik, saya akan menambahkan fitur **Preview Email** di halaman Edit Event agar Anda bisa melihat hasil tampilan email (termasuk logo dan custom message) secara langsung.

Berikut adalah rencana implementasi yang diperbarui:

### 1. Update Template Email (`registration-success.blade.php`)
*   **Header Logo:** Mengganti teks statis dengan logika untuk menampilkan **Logo Event**.
*   **Fallback:** Jika event tidak memiliki logo, akan menampilkan logo **RuangLari**.
*   **Styling:** Menyesuaikan ukuran dan layout header agar rapi.

### 2. Implementasi Fitur Preview Email
*   **Backend (Controller):**
    *   Membuat route baru `POST /eo/events/{event}/preview-email`.
    *   Membuat method `previewEmail` di `EventController`.
    *   Method ini akan menggunakan **Data Dummy** (Peserta & Transaksi palsu) agar template bisa dirender tanpa perlu ada pendaftaran asli.
    *   Method ini akan mengambil "Custom Email Message" dari **Input Form** (bukan database), jadi Anda bisa preview **sebelum save**.

*   **Frontend (Edit Event Page):**
    *   Menambahkan tombol **"Preview Ticket Email"** di dekat editor *Custom Email Message*.
    *   Menggunakan JavaScript untuk membuka tab baru yang me-render tampilan email berdasarkan apa yang sedang Anda ketik di editor.

Apakah rencana penambahan fitur Preview ini sudah sesuai dengan kebutuhan Anda?
