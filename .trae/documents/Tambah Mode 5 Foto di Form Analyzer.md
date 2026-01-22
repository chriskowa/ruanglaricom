## Ringkas
- Tambah mode baru **analisis 5 foto** (phase-based: landing, lever/mid-stance, push-off, pull/swing, + 1 slot tambahan).
- Perbaiki aturan input video: **video portrait diperbolehkan** (bukan sekadar “warning”), dengan panduan framing agar hasil tetap akurat.

## Improve Ide Anda (agar UX  akurasi naik)
- Selain “upload 5 foto”, tambahkan opsi yang lebih enak:
  - **Upload 1 video → ambil 5 frame** (frame picker) untuk landing/lever/push/pull (+front jika ada). Ini mengurangi friction (user tidak perlu screenshot manual) dan memastikan semua frame konsisten.
- Slot ke-5 rekomendasi:
  - **Tampak depan** (cek crossover/valgus/hip drop) lebih berharga daripada 5 foto samping.
  - Jika mau full samping semua: slot ke-5 bisa “mid-stance (stacked)” terpisah dari landing.

## Perubahan: Video Portrait Diperbolehkan
### UX
- Ubah copy/validasi client: portrait **tidak dianggap masalah**, hanya tampilkan guidance:
  - full body masuk frame, jangan terlalu dekat,
  - kamera stabil, pinggul dan kaki terlihat jelas,
  - ideal 5–10 detik.
### Teknis
- Pastikan perhitungan pose tetap robust untuk portrait:
  - gunakan `videoWidth/videoHeight` apa adanya (sudah),
  - tetap gunakan threshold kualitas berbasis **min dimension** (mis. min(w,h) ≥ 240) bukan “harus landscape”.

## Implementasi Mode 5 Foto (Tanpa Upload Foto, Privacy-friendly)
### 1) UI
- Tambah toggle: **Mode Video** / **Mode 5 Foto**.
- Mode foto: 5 slot upload (accept image/*, support camera capture di mobile).
- Tiap slot:
  - label phase (Landing/Lever/Push/Pull/Front)
  - thumbnail preview + tombol ganti/hapus
  - checklist singkat (side view untuk 1–4, front view untuk 5)

### 2) Pose Estimation untuk Foto
- Reuse Mediapipe Tasks Vision yang sudah dipakai.
- Tambah landmarker untuk `runningMode: "IMAGE"`.
- Buat fungsi:
  - `analyzeImageFile(file, phase)` → deteksi landmark → hitung metrik per phase.
  - Validasi “kelayakan foto” (keypoint visibility minimal; kalau gagal, tampilkan warning per slot).

### 3) Kirim Metrics ke Server (tanpa video)
- Untuk mode foto, request ke endpoint analyze dengan:
  - `upload_video=0`
  - `metrics` JSON (berisi `phases` + `summary` yang kompatibel dengan backend sekarang)
  - tanpa field `video`

## Backend Improvement (Opsional, tapi recommended)
- Saat request **tanpa video** dan hanya `metrics`, backend sebaiknya **bypass slot queue** (karena tidak ada ffmpeg/probe) supaya tidak “antri” untuk mode hemat / mode foto.

## Output di UI
- Tetap tampilkan skor global + rekomendasi (existing).
- Tambahkan section “Per Phase”:
  - Landing: overstride  knee flex
  - Lever/mid-stance: stacked  trunk
  - Push: hip extension proxy  ankle/foot angle
  - Pull/swing: heel recovery proxy
  - Front: crossover/valgus proxy

## File yang Akan Diubah
- [form-analyzer.blade.php](file:///c:/laragon/www/ruanglari/resources/views/tools/form-analyzer.blade.php)
- (Opsional) [FormAnalyzerController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/FormAnalyzerController.php)

## Validasi
- Test video landscape dan portrait (durasi/resolusi ok) → bisa lanjut.
- Test mode 5 foto → hasil keluar tanpa perlu refresh/antri.
- Test foto kualitas buruk → warning per slot, tapi tetap ada feedback umum.
