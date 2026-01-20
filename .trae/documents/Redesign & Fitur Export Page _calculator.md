## Target
- UI /calculator lebih enak dilihat dan konsisten dengan tema Pacerhub (dark + neon).
- Tambah 2 kalkulator yang paling dibutuhkan pelari.
- Setiap hasil kalkulator punya tombol Export hasil.

## Kondisi Saat Ini (yang sudah ada)
- Halaman /calculator adalah Blade: [calculator.blade.php](file:///c:/laragon/www/ruanglari/resources/views/tools/calculator.blade.php).
- Sudah ada banyak tab kalkulator (Magic Mile, Marathon Pace, Pace, Predictor, Improvement, Splits, Steps, Stride, Training, VO2 Max, Heart Rate).
- Sudah ada dependency html2canvas di halaman Blade, tapi belum ada tombol/logic export hasil per kalkulator.

## Perubahan UI (Redesign Warna)
1. Ubah palet CSS variables di `#rl-calculator` agar mengikuti Pacerhub:
   - primary: neon (#ccff00) dan highlight ring neon
   - background/card: nuansa slate/dark dengan border glass
   - text: slate-200/300 agar kontras
2. Rapikan komponen utama:
   - Header: gradient neon → biru (lebih “sporty”) dan subtitle lebih soft
   - Tab button: state aktif lebih tegas (neon) + hover/active micro-interaction
   - Form input: border + focus ring neon konsisten
   - Results: tampil seperti “summary card” yang siap di-export

## Fitur Export (Setiap Hasil)
1. Tambahkan tombol Export di tiap tab hasil (mis. di bawah `...Results`):
   - Label: “Export Hasil (PNG)”
   - Hidden default, muncul setelah hasil muncul.
2. Implementasi 1 fungsi universal export:
   - `exportResults(resultsId, title)` menggunakan html2canvas
   - Membuat container export (judul + tanggal + watermark Ruang Lari)
   - Mengubah styling hasil (clone node) agar rapi untuk PNG
   - Download otomatis dengan nama file konsisten.
3. Integrasikan ke mekanisme `showResults()` agar:
   - otomatis menampilkan tombol export yang sesuai untuk results tersebut.

## Fitur Kalkulator Baru (Paling Dibutuhkan Pelari)
### 1) Hydration & Electrolyte Calculator
- Input: durasi lari (menit), suhu, kelembaban, perkiraan sweat rate (opsional), target intake (% dari sweat loss).
- Output: estimasi kebutuhan cairan (ml/jam), total cairan, range aman, dan rekomendasi elektrolit (mg sodium/jam).
- Tambahkan Export hasil.

### 2) Carb/Fueling Plan Calculator (Race Nutrition)
- Input: durasi target (jam), intensitas (easy/tempo/race), toleransi GI (low/medium/high), tipe fuel (gel/drink/mix).
- Output: rekomendasi gram karbo/jam (range), total karbo, dan jadwal konsumsi per 20–30 menit.
- Tambahkan Export hasil.

## Implementasi Teknis
- Edit hanya file yang sudah ada: [calculator.blade.php](file:///c:/laragon/www/ruanglari/resources/views/tools/calculator.blade.php).
- Tambah 2 tab baru + form + container results.
- Tambah CSS kecil untuk tombol export dan layout hasil.
- Tambah JS:
  - fungsi kalkulasi 2 fitur baru
  - fungsi export universal + helper styling export
  - update `showResults()` untuk menampilkan tombol export.

## Verifikasi
- Jalankan server lokal dan akses `/calculator`.
- Cek setiap tab existing masih berfungsi.
- Cek hasil muncul + tombol export muncul setelah kalkulasi.
- Cek export menghasilkan file PNG (download) untuk beberapa tab (minimal 3 tab + 2 fitur baru).

Jika kamu setuju, aku lanjut eksekusi perubahan sesuai plan ini.