Tentu, saya bisa mengubah tampilan halaman **Login** dan **Dashboard** (Runner & Coach) menjadi jauh lebih modern dengan gaya **"Cyber-Athletic"** (Dark Mode, Neon Accents, Glassmorphism) yang konsisten dengan halaman pendaftaran Pacer/Coach yang baru saja kita buat.

Berikut adalah rencana implementasinya:

# Rencana UI/UX Overhaul: RuangLari Modern

## 1. Redesign Halaman Login (`resources/views/auth/login.blade.php`)
Mengubah halaman login standar menjadi portal masuk yang imersif.
*   **Visual:** Background gelap dengan animasi subtle (gradient blob/scan lines), kartu login *glassmorphism* di tengah.
*   **UX:** Input field dengan efek *glow* saat fokus, transisi halus, dan tombol "Masuk" yang menonjol dengan gradasi neon.
*   **Fitur:** Menampilkan logo RuangLari dengan efek modern, link "Lupa Password" dan "Daftar" yang rapi.

## 2. Redesign Dashboard Runner (`resources/views/runner/dashboard.blade.php`)
Mengubah dashboard runner menjadi **"Athlete's Cockpit"**.
*   **Hero Section:** Sapaan personal ("Good Morning, Runner"), ringkasan cuaca/kondisi (mockup), dan status latihan hari ini.
*   **Stats Cards:** Grid kartu *glass* yang menampilkan:
    *   Weekly Volume (KM) dengan progress bar.
    *   Fitness Level (VDOT).
    *   Upcoming Race countdown.
*   **Quick Actions:** Tombol akses cepat ke "Training Calendar", "My Programs", dan "Marketplace".
*   **Activity Feed:** List ringkas aktivitas terakhir dengan ikon visual.

## 3. Redesign Dashboard Coach (`resources/views/coach/dashboard.blade.php`)
Karena kita sudah membuat `coach/hub.blade.php` yang sangat bagus, saya akan:
*   Mengadopsi elemen UI dari `hub.blade.php` ke `dashboard.blade.php`.
*   Menampilkan ringkasan: Total Atlet Aktif, Pending Review, dan Revenue/Pendapatan bulan ini.
*   Memastikan konsistensi navigasi dengan layout `pacerhub`.

## 4. Integrasi Layout (`layouts.pacerhub`)
*   Memastikan semua halaman di atas menggunakan `layouts.pacerhub` agar style Tailwind (warna `neon`, font, dll) terbaca dengan benar.
*   Menghapus dependensi style lama (Bootstrap/CSS bawaan) jika ada yang bentrok.

Apakah Anda setuju dengan rencana modernisasi ini? Jika ya, saya akan mulai dari halaman Login terlebih dahulu.