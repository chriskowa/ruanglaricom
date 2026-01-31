## Gambaran Umum
Implementasi dibagi bertahap agar stabilitas sistem terjaga dan perubahan bisa diverifikasi per fitur. Stack dan pola yang sudah ada akan dipakai (queue database, export CSV/print-to-PDF, Leaflet/GPX parsing dari tools yang sudah tersedia).

## 1) Menu “Email Report” di Sidebar Admin + Halaman Manajemen
- **Sidebar admin**: tambah item menu “Email Report” (ikon) di sidebar layout pacerhub ([pacerhub-sidebar.blade.php](file:///c:/laragon/www/ruanglari/resources/views/layouts/components/pacerhub-sidebar.blade.php)).
- **Halaman admin**: buat route `/admin/email-reports` (controller + view) untuk menampilkan daftar delivery email report (filter `tanggal`, `event`, `status`).
- **Export**:
  - Tahap aman (tanpa dependency baru):
    - Export **CSV** (Excel-friendly, mirip pola `exportParticipants` yang sudah ada).
    - Export **PDF** via halaman print khusus + `window.print()` (pola sudah dipakai di beberapa halaman).
  - Tahap lanjutan (opsional): jika benar-benar butuh XLSX/PDF server-side, baru tambah dependency (Maatwebsite Excel/Dompdf) setelah tahap aman stabil.

## 2) Rate Limiting “Instant Email Notification” (5 email/menit/event) + Queue Prioritas
- **Tujuan**: mode “Instant” tidak lagi mengirim tanpa batas; dibatasi **maks 5 email/menit per event**.
- **Tidak mengganggu email ticket**: email ticket pendaftar tetap **prioritas utama** dengan:
  - Queue khusus `emails-tickets` (prioritas tinggi)
  - Queue lain (mis. blast/report) dipisah (`emails-blast`, `emails-reports`)
  - Worker disarankan listen urutan: `emails-tickets,default,emails-reports,emails-blast`
- **Implementasi limiter**:
  - Karena 1 job bisa mengirim lebih dari 1 email (PIC + peserta), limiter dihitung berbasis **jumlah email**.
  - Tambah tabel counter per-event per-menit (DB) agar aman dari race-condition (lebih stabil dibanding cache increment tanpa lock).
  - Saat limit tercapai: job **tidak dibatalkan**, tetapi di-**delay** ke menit berikutnya (queue tetap jalan).
- **Integrasi titik kirim**:
  - Di flow paid/COD: ubah dispatch agar selalu melewati “gate” limiter untuk mode instant.
- **Logging status**:
  - Catat attempt + sukses/gagal untuk tiap email (minimal per delivery/job; detail per recipient opsional jika diperlukan).
- **Monitoring**:
  - Tambah halaman admin ringkas untuk backlog queue email (ambil dari tabel `jobs` per queue) + status limiter (counter per event).

## 3) Modal Popup Pasca Registrasi (paolo-fest.blade.php)
- Trigger modal otomatis berdasarkan query parameter yang sudah dipakai sekarang:
  - `?payment=success` → tampil modal “Terima kasih, tiket dikirim via email”.
  - Untuk registrasi gratis (yang saat ini `alert` + reload), ubah agar memunculkan modal juga.
- Modal berisi:
  - Ucapan terima kasih + info tiket via email.
  - Loading animation + countdown 5 detik, lalu auto-close.
  - Tombol “Cek Email Saya”:
    - Default: `mailto:` (buka email client).
    - Opsional: tampilkan tombol link cepat (Gmail/Outlook/Yahoo) tanpa mengunci ke provider.
- Pastikan tidak mengganggu conversion: modal tidak memblokir interaksi lama, bisa ditutup manual, dan tidak memaksa redirect.

## 4) Time Race Prediction + Analisis GPX
### 4.1 Data GPX per kategori
- Gunakan relasi yang sudah ada: `RaceCategory.master_gpx_id` → `MasterGpx` (file gpx di storage).
- Rapikan inkonsistensi admin GPX controller yang masih memakai `running_event_id` agar manajemen GPX tetap benar.

### 4.2 Parsing GPX + Peta + Profil Elevasi
- Render peta OpenStreetMap dengan Leaflet (sudah dipakai di tools dan theme).
- Ambil file GPX dari endpoint download (pola sudah ada di PaceProController) lalu parse:
  - Extract lat/lng, elevation (ele), distance cumulative.
  - Jika ele tidak ada/berantakan: fallback ambil elevation via Open-Meteo (pola sudah ada di tool).
- Buat grafik elevasi interaktif menggunakan Chart.js (sudah dipakai via CDN di tools).
- Hitung metrik: total elevation gain, min/max elevation, waypoint count.
- Performance 10MB+: parsing dilakukan dengan downsampling (mis. max 2.000 titik untuk chart) + opsi Web Worker (tahap lanjutan).

### 4.3 UI Prediksi
- Weather radio (panas/dingin/hujan/gerimis) dengan ikon.
- Input PB jam/menit/detik + tanggal PB.
- Validasi:
  - PB sesuai jarak (mis. pilih 5K/10K sesuai kategori).
  - PB harus dalam 3 bulan terakhir (cek tanggal input).

### 4.4 Kalkulasi & “AI Analysis” (Heuristik Terukur)
- VDOT dihitung dari PB via `DanielsRunningService` (sudah ada).
- Penyesuaian berdasarkan:
  - Elevation gain vs rute PB (anggap PB flat baseline, lalu adjust waktu berdasar gain/terrain).
  - Cuaca (penalty/bonus persentase).
  - Terrain dari GPX (heuristik: variasi elevasi, slope, dll).
- Output:
  - Rentang optimis/realistis/pesimis.
  - Saran pacing per segmen elevasi (naik/turun/datar).
  - Confidence score berdasarkan kualitas data (recency PB, kelengkapan elevasi, ukuran file, konsistensi jarak).

## 5) Testing & QA
- Unit test:
  - Limiter per-event 5 email/menit (termasuk kasus multi-email dalam 1 job).
  - Delay ketika limit tercapai.
  - Endpoint admin email report (filter + export CSV minimal smoke test).
  - GPX parser (gain/min/max + downsampling) dan VDOT calculation (range output konsisten).
- Integration test:
  - Pastikan email ticket pendaftar tetap terkirim dan tidak tertahan oleh blast/report.
- Frontend sanity:
  - Modal muncul pada `payment=success` dan auto-close 5 detik.
- Performance check:
  - Uji parsing GPX besar dengan batas waktu dan memory yang wajar; fallback downsample.

## 6) Dokumentasi & Monitoring
- Dokumentasi API (docs/) untuk:
  - Admin Email Report: list/filter/export.
  - Rate limit status/monitoring.
  - Endpoint prediksi (ambil GPX, compute summary, dsb).
- Monitoring:
  - Admin page: backlog queue email (count per queue + oldest job).
  - Logging terstruktur untuk error GPX/VDOT.
  - Notifikasi admin/EO saat error parsing kritikal (opsional).

## Urutan Eksekusi yang Disarankan
1) Admin Email Report menu + halaman + export CSV/print.
2) Rate limit instant notification + queue prioritas + monitoring ringkas.
3) Modal pasca registrasi paolo-fest.
4) GPX prediction (mulai dari data pipeline + peta/elevasi, lalu kalkulasi).
5) Testing/QA + dokumentasi endpoint per tahap.