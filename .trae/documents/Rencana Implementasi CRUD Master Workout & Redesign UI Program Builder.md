# Rencana Implementasi CRUD Master Workout & UI Toolbar Horizontal

Saya akan segera mengeksekusi rencana yang telah disetujui. Berikut detail teknis langkah-langkahnya:

## 1. Implementasi CRUD Master Workout
Membuat fitur manajemen library latihan agar database latihan bisa berkembang.
*   **Controller**: Membuat `Coach\MasterWorkoutController`.
*   **Routes**: Menambahkan resource route di `web.php` (`coach/master-workouts`).
*   **Views**:
    *   `index.blade.php`: Daftar latihan dengan pengelompokan berdasarkan Tipe.
    *   `create.blade.php` & `edit.blade.php`: Form input standar.
*   **Menu**: Menambahkan link "Master Workouts" di sidebar dashboard coach.

## 2. Redesign Program Builder (Create & Edit)
Mengubah tampilan Program Builder menjadi lebih luas dan fokus pada kalender.
*   **Layout Change**: Mengubah grid layout dari `grid-cols-3` menjadi layout vertikal yang lebih *fluid*.
*   **New Component: Workout Toolbar**:
    *   Posisi: Di atas Grid Kalender.
    *   Desain: Baris horizontal berisi kategori (Tab/Pills).
    *   Interaksi: Hover/Klik pada kategori akan membuka panel *dropdown* berisi kartu-kartu latihan yang *draggable*.
*   **Program Settings**: Memindahkan input "Judul", "Deskripsi", "Durasi" ke dalam *collapsible panel* atau modal "Settings" agar tidak memakan tempat layar utama.

Saya akan mulai dengan membuat CRUD Master Workout terlebih dahulu, kemudian lanjut ke perombakan UI Program Builder.

**Langkah Eksekusi:**
1.  Buat Controller & Route untuk Master Workout.
2.  Buat View Index & Form untuk Master Workout.
3.  Update Sidebar Menu.
4.  Refactor `create.blade.php` untuk implementasi Horizontal Toolbar.
5.  Refactor `edit.blade.php` untuk implementasi Horizontal Toolbar.