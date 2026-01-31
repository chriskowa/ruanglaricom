# User Guide: Time Race Prediction

## Akses
- Buka halaman: `/event/{slug}/prediction`

## Langkah Penggunaan
1. Pilih **Kategori** (mis. 5K/10K/HM/FM).
2. Pilih **Cuaca** (panas/dingin/hujan/gerimis).
3. Isi **PB** (jam/menit/detik) untuk jarak kategori tersebut.
4. Isi **Tanggal PB** (harus dalam 3 bulan terakhir).
5. Klik **Prediksi Waktu**.

## Yang Ditampilkan
- **Peta rute** (OpenStreetMap) dari file GPX kategori.
- **Profil elevasi** (grafik) dari data GPX.
- Statistik rute: jarak, elevation gain, min/max elevasi, perkiraan terrain.
- Hasil prediksi: **optimis / realistis / pesimis**, beserta **VDOT**, **confidence score**, dan **saran pacing**.

## Validasi
- Tanggal PB harus dalam 3 bulan terakhir dan tidak boleh di masa depan.
- PB harus masuk rentang wajar untuk jarak kategori (contoh 5K: 10–90 menit).

## Catatan
- Jika kategori belum memiliki GPX, peta/profil elevasi tidak dapat dianalisis sampai GPX dihubungkan.
- Error saat kalkulasi akan dicatat pada tabel `prediction_error_logs` dan terlihat di `/admin/email-monitoring`.

