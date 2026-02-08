## Kondisi Saat Ini
- Scanner di [race-master.blade.php](file:///c:/laragon/www/ruanglari/resources/views/tools/race-master.blade.php) memakai `Html5Qrcode.start()` (fps 10, qrbox 250) dan hanya mengandalkan decode realtime.
- Saat peserta bergerak cepat, QR sering blur/keluar frame → decode realtime mudah miss.

## Ide Peningkatan (Fokus Race, peserta cepat)
- **Tombol Capture (burst)**: operator tekan tombol saat peserta lewat → sistem mengambil beberapa frame beruntun (mis. 5 frame/0.4s) lalu mencoba decode QR dari tiap frame sampai ketemu.
- **Decode dari frame (snapshot)**: decode QR dari `ImageData` (canvas) lebih fleksibel (bisa crop/downscale) dan tidak tergantung timing callback realtime.
- **Optimasi UX race**:
  - Tombol capture besar (mudah ditekan), shortcut keyboard (Space/Enter).
  - Feedback instan: “capturing…”, “QR terbaca”, “QR tidak terbaca, coba lagi”.
  - Tetap pakai debounce 3 detik per peserta yang sudah ada.

## Implementasi Teknis (tanpa ubah backend)
1. **Tambah library decode snapshot**
   - Tambahkan script CDN `jsQR` (ringan) di head.
   - Alasan: `html5-qrcode` API publik lebih fokus realtime/file; `jsQR` bisa decode langsung dari `canvas.getImageData()`.

2. **Tambah tombol Capture di UI Race**
   - Di header Race (sebelah tombol QR), tambahkan tombol baru: “Capture”.
   - Tombol aktif hanya saat kamera ON.

3. **Ambil frame dari stream kamera**
   - Saat kamera aktif, `html5-qrcode` membuat elemen `<video>` di dalam `#reader`.
   - Implement `getReaderVideo()` yang mengambil `document.querySelector('#reader video')`.

4. **Fungsi capture & decode (burst)**
   - Implement `captureAndDecodeBurst()`:
     - loop N kali: ambil frame → decode via `jsQR` → jika ketemu, ambil `decoded.data` sebagai `bib` → cari peserta → `recordLap(p.id,'scanner')`.
     - jika tidak ketemu, tampilkan pesan gagal.
   - Opsi tambahan: crop area tengah (sekitar qrbox) sebelum decode untuk mempercepat dan meningkatkan akurasi.

5. **Shortcut race**
   - Tambahkan listener keydown saat view `race` dan kamera aktif:
     - `Space` = capture.

6. **Tuning scanner realtime (opsional tapi cepat)**
   - Naikkan `fps` (mis. 15) dan perbesar `qrbox` (mis. 300) sebagai baseline.
   - Ini tidak menggantikan capture; hanya membantu realtime.

## Aturan & Edge Cases
- Jika QR terbaca tapi **BIB tidak ada** → tampilkan “Unknown BIB”.
- Jika timer belum jalan → tetap tidak mencatat lap (sesuai logic sekarang).
- Jika kamera belum siap (video belum ada) → pesan “Kamera belum siap”.

## Verifikasi
- Manual test di halaman Race:
  - Nyalakan kamera → tekan Capture sambil arahkan QR (HP/print) → harus muncul “Scanned: …” dan lap bertambah.
  - Test QR blur/gerak cepat → burst capture harus lebih sering berhasil dibanding realtime.
  - Test QR tidak dikenal → muncul “Unknown BIB”.

Jika Anda setuju, saya lanjutkan implementasi langsung di file itu saja (tanpa buat file baru) dan tuning parameter burst/fps sesuai hasil uji.