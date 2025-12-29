# Transformasi Total: Interaksi Coach & Runner

Betul, sisi Runner juga harus di-upgrade agar komunikasi dua arah ini berjalan lancar. Berikut adalah rencana komprehensif yang mencakup kedua sisi.

## 1. Upgrade Database (Pondasi)
Agar interaksi bisa terjadi, kita perlu tempat penyimpanan data baru.
*   **Tabel `program_session_tracking`**:
    *   `coach_feedback`: Text (Komentar dari coach).
    *   `coach_rating`: Integer 1-5 (Nilai performa).
    *   `rpe`: Integer 1-10 (Rate of Perceived Exertion - seberapa capek runner merasa).
    *   `feeling`: Enum (Strong, Good, Average, Weak).

## 2. Sisi Coach (The Mentor)
Coach membutuhkan alat untuk memantau dan membimbing.
*   **Athlete Monitoring Dashboard:**
    *   Tabel daftar atlet aktif dengan indikator "Last Activity" dan "Compliance Score".
    *   Fitur "Review Mode": Filter cepat untuk melihat sesi latihan atlet yang belum dinilai.
*   **Interactive Calendar View (Ghost View):**
    *   Coach bisa melihat kalender spesifik milik atlet.
    *   Bisa klik sesi yang sudah "Completed" untuk memberi Feedback & Rating.
    *   Bisa mem-verifikasi data manual atlet (Checked/Verified).
*   **Visual Program Builder:** (Seperti rencana sebelumnya, drag & drop untuk membuat program lebih mudah).

## 3. Sisi Runner (The Athlete)
Runner perlu cara untuk melapor dan menerima masukan.
*   **Enhanced Workout Log (Form Input Lari):**
    *   Saat input hasil lari (atau sync), Runner wajib isi **RPE** (1-10) dan **Feeling**. Ini data krusial buat Coach.
    *   Kolom "Notes for Coach": Curhat spesifik tentang sesi tersebut.
*   **Feedback Display:**
    *   Di Kalender Runner, sesi yang sudah dinilai Coach akan punya indikator khusus (misal: ikon pesan/bintang).
    *   Saat diklik, muncul pop-up: "Coach says: Great job on the tempo run! Keep the cadence high."

## Rencana Eksekusi
1.  **Fase 1: Database & Model** (Menambah kolom feedback, rating, rpe, feeling).
2.  **Fase 2: Runner Update** (Update form input hasil lari untuk data RPE/Feeling & Tampilan Feedback).
3.  **Fase 3: Coach Monitoring** (Halaman list atlet & fitur memberi feedback di kalender atlet).
4.  **Fase 4: Coach Program Builder** (Visual editor).

Kita akan mulai dari Fase 1 & 2 agar datanya siap, lalu lanjut ke Fase 3 untuk Coach. Setuju?