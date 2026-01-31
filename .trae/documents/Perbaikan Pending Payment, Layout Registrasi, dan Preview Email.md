## Temuan Cepat
- Perubahan “anti zombie” sudah ada di [StoreRegistrationAction.php](file:///c:/laragon/www/ruanglari/app/Actions/Events/StoreRegistrationAction.php#L430-L459): jika token Midtrans gagal dibuat, transaksi di-update jadi `failed`.
- Perubahan “resume token” di [EventPaymentRecoveryController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EventPaymentRecoveryController.php#L152-L219) saat ini memanggil `$midtransService` tapi method `resume()` belum menerima dependency tersebut → akan error (undefined variable).
- Layout form registrasi di tema modern-dark memakai `grid grid-cols-1 lg:grid-cols-3` (2/3 + 1/3 sidebar). Pada kondisi `?payment=pending` ini bikin form terasa sempit.
- Preview email pakai view langsung [registration-success.blade.php](file:///c:/laragon/www/ruanglari/resources/views/emails/events/registration-success.blade.php). Styling banyak bergantung pada `<style>` di `<head>` (sering di-strip oleh email client) dan konten `custom_email_message` dirender raw (`{!! !!}`) sehingga jika ada gambar hasil paste (data URL) bisa muncul sebagai attachment `unnamed.jpg` di beberapa client.

## Rencana Perbaikan (tanpa eksekusi dulu)
### 1) Benarkan “Resume Pembayaran” agar tidak error
- Update signature `resume()` untuk menerima `MidtransService $midtransService` lewat DI.
- Rapikan implementasi agar jika `snap_token` kosong:
  - coba generate ulang token,
  - kalau gagal, balikan message yang jelas.
- Hapus komentar yang baru ditambahkan (agar konsisten dengan rule “tidak menambah komentar”).

File: [EventPaymentRecoveryController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EventPaymentRecoveryController.php)

### 2) Pastikan transaksi gagal token tidak “mengunci slot”
- Di `StoreRegistrationAction`, logic sudah mengubah status ke `failed` saat token gagal.
- Rapikan dengan menghapus komentar baru.

File: [StoreRegistrationAction.php](file:///c:/laragon/www/ruanglari/app/Actions/Events/StoreRegistrationAction.php)

### 3) Pending belum bayar “jangan dimasukkan dulu” (pilih salah satu pendekatan)
- Opsi A (minimal, tanpa migrasi): data tetap dibuat, tapi **slot/quota dan validasi “sudah terdaftar” hanya menghitung `paid` (dan `cod` bila dianggap valid)**, sehingga `pending` tidak memblok.
- Opsi B (paling sesuai kalimat “jangan dimasukkan dulu”, tapi butuh migrasi): simpan payload peserta sebagai JSON di transaksi (atau tabel draft), baru buat record `participants` setelah webhook Midtrans sukses (`paid`).

Saya sarankan Opsi A dulu (lebih cepat dan aman untuk dikerjakan tanpa struktur baru). Kalau butuh benar-benar zero insert sebelum bayar, lanjut Opsi B.

### 4) Perbaiki UI saat `?payment=pending` agar form tidak jadi kecil
- Ubah breakpoint layout grid pada theme yang dipakai (mulai dari modern-dark):
  - `lg:grid-cols-3` → `xl:grid-cols-3`
  - `lg:col-span-2` → `xl:col-span-2`
  - `lg:col-span-1` → `xl:col-span-1`
  Ini membuat di layar “besar” (lg) tetap 1 kolom (form full width), sidebar turun ke bawah; baru di layar extra-large (xl) kembali 2/3 + 1/3.
- Jika event bisa memakai tema lain, lakukan perubahan yang sama di tema-tema lain yang punya wrapper serupa.

File utama: [modern-dark.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/modern-dark.blade.php#L758-L876)

### 5) Perbaiki gambar “putih” & attachment `unnamed.jpg` di email
- Ubah template email agar lebih kompatibel email client:
  - Kurangi ketergantungan pada `<style>` di `<head>` dengan memindahkan style penting ke inline style pada elemen-elemen kunci.
  - Pastikan URL gambar adalah absolute (berdasarkan `APP_URL`) dan tidak mengarah ke `localhost` saat produksi.
- Untuk `custom_email_message`:
  - Deteksi dan blok/bersihkan `<img src="data:image/...">` (hasil paste) atau konversi ke file tersimpan lalu ganti jadi URL `asset('storage/...')` supaya tidak jadi `unnamed.jpg`.

File: [registration-success.blade.php](file:///c:/laragon/www/ruanglari/resources/views/emails/events/registration-success.blade.php)
Controller penyimpanan event (untuk proses custom_email_message) akan saya telusuri lalu disesuaikan.

## Verifikasi
- Jalankan test manual:
  - Buat transaksi dengan Midtrans key kosong → pastikan status transaksi jadi `failed`.
  - Buka `lanjutkan-pembayaran` untuk transaksi `pending` tanpa token → token diregenerate dan Snap muncul.
  - Buka `/event/{slug}?payment=pending` → form tidak mengecil di layar lg.
  - Preview email + send test email → logo tampil benar, tidak ada `unnamed.jpg`.

Jika Anda setuju, saya lanjut eksekusi sesuai rencana di atas (mulai dari fix error `$midtransService` di resume dulu).