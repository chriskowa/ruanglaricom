Saya akan mengeksekusi dua fitur sekaligus: **Weekly Volume Chart** dan **Smart Reschedule (Drag & Drop)**.

**1. Weekly Volume Chart (Analisa Mingguan)**
*   **Backend:** Menambahkan logika agregasi data untuk menghitung total jarak (Planned vs Actual) per minggu.
*   **Frontend:** Membuat komponen grafik batang (Bar Chart) menggunakan CSS murni (ringan & cepat) di atas kalender.
    *   Batang Abu-abu: Target Jarak (Plan).
    *   Batang Neon: Jarak Selesai (Actual).

**2. Smart Reschedule (Drag & Drop)**
Fitur ini memungkinkan Anda memindahkan jadwal latihan langsung di kalender.
*   **Database:** Menambahkan kolom `rescheduled_date` pada tabel `program_session_tracking` agar perubahan tanggal program tersimpan permanen tanpa merusak struktur asli program.
*   **Backend:** Membuat endpoint API untuk menangani perpindahan tanggal (`reschedule`).
*   **Frontend:** Mengaktifkan fitur `editable` pada FullCalendar agar event bisa di-drag & drop.

Apakah Anda setuju dengan rencana eksekusi ganda ini?