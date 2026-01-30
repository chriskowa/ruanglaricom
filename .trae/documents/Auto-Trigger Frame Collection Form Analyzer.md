## Tujuan
- Membuat pengambilan frame dari video berhenti otomatis dan langsung lanjut ke analisis komprehensif ketika sistem sudah mengumpulkan frame yang **cukup** dan **berkualitas** untuk tiap kategori gerakan: landing, lever, push, pull, ayunan tangan, dan postur.

## Kondisi Saat Ini (Temuan)
- Analisis video di sisi klien saat ini mengambil sampel timestamp merata (12–28 frame) lalu menghitung metrik (landing/contact heuristik) dan memilih 1 frame terbaik untuk visualisasi. Tidak ada konsep “kategori gerakan” per-frame maupun penghentian dini berdasarkan kecukupan frame.
- Lokasi implementasi: fungsi `analyzeVideoFile` di [form-analyzer.blade.php](file:///c:/laragon/www/ruanglari/resources/views/tools/form-analyzer.blade.php#L537-L727).

## Desain Solusi
### 1) Definisi Kriteria Minimum per Kategori
Saya akan menambahkan konfigurasi threshold dan minimum frame (dalam satu object config) agar mudah dituning:
- **Landing**: min 3 frame
- **Lever (mid-stance)**: min 3 frame
- **Push (toe-off)**: min 3 frame
- **Pull (swing/pull-through)**: min 3 frame
- **Ayunan tangan**: min 4 frame
- **Postur**: min 4 frame
- Semua kategori wajib punya **≥ 1 frame representatif** (best frame) untuk dipakai sebagai representasi/visualisasi.

### 2) Threshold Kualitas Frame (Quality Gate)
Sebelum sebuah frame dihitung ke kategori manapun, frame harus lolos quality gate:
- **Visibility average** landmark kunci ≥ 0.55 (hip, knee, ankle, heel, footIndex, shoulders, wrists bila tersedia)
- **Body-in-frame**: titik hip/shoulder/ankle tidak terlalu mepet tepi (mis. 0.05 < x,y < 0.95)
- **Relevansi pose**: untuk kategori tertentu, wajib landmark tertentu tersedia (mis. push/pull wajib ankle+hip+toe/heel; ayunan tangan wajib wrists+shoulders)

Catatan: ini ringan (hanya pakai visibility/posisi landmark) sehingga tidak menambah berat komputasi.

### 3) Klasifikasi Kategori per Frame (Heuristik Berbasis Landmark)
Saya akan menambahkan fungsi `classifyFrame(frameFeatures)` untuk menempatkan satu frame ke 0/1 kategori utama (atau multi-kategori jika masuk akal). Fitur yang sudah dihitung di kode saat ini akan dipakai + beberapa tambahan ringan:
- **Landing**: foot dekat ground (footY tinggi relatif max), ankleHipDx besar, dan (opsional) heelLower true
- **Lever**: foot dekat ground, ankleHipDx moderat, kneeFlex moderat
- **Push**: foot dekat ground, ankle relatif “di belakang” hip (arah gerak disimpulkan dari toe vs heel seperti yang sudah ada di analyzeImageFile)
- **Pull**: foot tidak dekat ground, kneeFlex lebih tinggi, ankleHipDx moderat, fase swing
- **Ayunan tangan**: wrist visibility baik + wrist bergerak cukup jauh dari garis torso (indikasi swing; dihitung dari jarak wrist ke mid-shoulder/hip)
- **Postur**: trunkAng terukur + shoulder/hip visibility baik

Selain kategori, setiap frame akan punya **qualityScore** (gabungan visibility + body-in-frame + stabilitas fitur) untuk memilih “best frame” per kategori.

### 4) Mekanisme Validasi Real-time & Penghentian Dini
- Dalam loop pengambilan sampel video, sistem akan memelihara accumulator:
  - `counts[category]`, `bestFrame[category]`, `framesByCategory[category]` (dibatasi max N agar tidak bengkak)
- Setelah tiap frame diproses:
  - Update counts jika lolos quality gate & lolos kategori
  - Update bestFrame jika qualityScore lebih tinggi
  - Update progress UI (mis. `scanSubtext`: “Landing 2/3 • Lever 1/3 …”)
- **Stop condition**: bila semua kategori memenuhi minimum + bestFrame tersedia untuk semuanya, loop berhenti lebih awal.
- Untuk mencegah loop terlalu lama, ada **hard cap** misalnya max 60–80 frame atau max durasi scan tertentu.

### 5) Output Analisis
- Setelah stop condition terpenuhi (atau cap tercapai), sistem menjalankan perhitungan metrik komprehensif menggunakan frame yang terkumpul:
  - Metrik yang sudah ada tetap dihitung (confidence, heel_strike_pct, overstride_pct, shin_angle_deg, knee_flex_deg, trunk_lean_deg, arm_cross_pct, vertical_oscillation)
  - Tambahan internal: `coverage` per kategori (untuk debugging/UI), tanpa dikirim ke server jika berpotensi membesar.
- Visualisasi: gunakan **bestFrame landing** (atau fallback postur) untuk gambar biomekanik.

## Perubahan File yang Akan Dilakukan
- [form-analyzer.blade.php](file:///c:/laragon/www/ruanglari/resources/views/tools/form-analyzer.blade.php)
  - Refactor `analyzeVideoFile` agar:
    - punya config thresholds + mins
    - punya quality gate + classifier
    - loop sampling adaptif + stop condition
    - update progress UI berbasis counts per kategori

## Skenario Uji (Manual) yang Akan Saya Jalankan
- Video side-view 5–10 detik dengan pose jelas:
  - memastikan pengambilan frame berhenti lebih cepat (sebelum targetSamples habis) saat semua kategori terpenuhi
- Video blur/gelap:
  - memastikan quality gate menolak frame buruk; sistem tetap lanjut sampai cap dan memberi warning “frame tidak cukup jelas”
- Video pendek 2–3 detik:
  - memastikan fallback bekerja (tidak infinite loop), hasil tetap keluar tapi coverage menunjukkan kurang

## Kriteria Selesai
- Pengambilan frame berhenti otomatis saat semua kategori memenuhi min frame + tiap kategori punya ≥1 frame representatif.
- Progress scan menampilkan status akumulasi frame per kategori.
- Frame buruk tidak ikut dihitung (berdasarkan threshold kualitas).
- Tidak membuat payload `metrics` membengkak (tidak memasukkan data frame mentah/visualization ke payload).