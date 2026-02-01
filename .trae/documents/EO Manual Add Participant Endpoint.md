## Tujuan
- Menambah fitur penambahan peserta manual oleh EO via endpoint `POST /eo/events/{event}/participants` (sesuai permintaan `/eo/events/{id}/participants`).
- Menyediakan form pada halaman EO Participants untuk input data peserta.
- Menjamin validasi, cek duplikasi (berdasarkan email), transaksi DB aman, kirim email konfirmasi, dan pembatasan akses hanya untuk EO pemilik event.

## Kondisi Saat Ini (Yang Akan Di-reuse)
- Routing EO sudah ada dengan `auth` + `role:eo` di [web.php](file:///c:/laragon/www/ruanglari/routes/web.php#L726-L771).
- Halaman daftar peserta EO sudah ada di [participants.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/participants.blade.php).
- Proteksi kepemilikan event sudah ada lewat `authorizeEvent($event)` di [EO/EventController.php](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php#L1109-L1125).
- Mekanisme kirim email registrasi sudah tersedia via [EventRegistrationEmailDispatcher.php](file:///c:/laragon/www/ruanglari/app/Services/EventRegistrationEmailDispatcher.php).

## Desain Endpoint
- **Route baru (web)**: `POST /eo/events/{event}/participants` → `EO\EventController@storeParticipant`.
- **Response**:
  - Jika request HTML biasa: redirect balik ke halaman participants dengan flash `success`.
  - Jika `Accept: application/json`: return JSON sukses (mis. `{success:true, transaction_id, participant_id}`) atau error `422/403`.

## Validasi & Cek Duplikasi
- Validasi minimal:
  - `name` (required)
  - `email` (required, email)
  - `phone` (required, numeric-like + panjang wajar)
  - `id_card` (required)
  - `gender` (optional: male/female)
  - `category_id` (required; harus milik event)
  - Opsional “informasi tambahan relevan”: `date_of_birth`, `emergency_contact_name`, `emergency_contact_number`, `jersey_size`, `target_time`.
- Cek duplikasi:
  - Tolak jika sudah ada peserta pada **event yang sama** dengan email yang sama (case-insensitive). (Jika perlu lebih longgar, bisa dibuat per kategori).

## Penyimpanan DB yang Aman (Atomic)
- Implementasi di sebuah Action khusus (mis. `StoreManualParticipantAction`) yang dipanggil controller.
- Jalankan di `DB::transaction()`.
- `lockForUpdate()` pada race category terkait + cek quota (menggunakan count participant paid/cod pada kategori tsb) sebelum create.
- Buat `Transaction` dengan `payment_gateway = 'manual'`, `payment_status = 'paid'`, `paid_at = now()`, `admin_fee = 0`.
- Buat `Participant` terhubung ke transaction (race_category_id terisi, field lain sesuai input).

## Pengiriman Email Konfirmasi
- Setelah transaksi commit berhasil:
  - Panggil `EventRegistrationEmailDispatcher::dispatch($transaction)` agar email terkirim via job yang sudah ada.

## Authorization
- Di `storeParticipant`, panggil `authorizeEvent($event)` (pola existing) agar hanya EO pemilik event yang bisa menambah peserta.

## UI Form di Halaman Participants
- Tambah tombol “Tambah Peserta” di header halaman [participants.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/participants.blade.php).
- Tambah modal form (mirip gaya modal pickup/detail yang sudah ada): input field sesuai validasi dan submit ke route POST di atas.
- Tampilkan error validasi (Laravel errors bag) dan flash success.

## Testing
- **Feature/Integration tests**:
  - Berhasil menambah peserta (assert participant & transaction tercipta, status paid, gateway manual).
  - Ditolak jika email duplikat pada event yang sama.
  - Ditolak jika EO bukan pemilik event (`403`).
  - Ditolak jika bukan role EO (middleware `role:eo`).
  - Assert job email didispatch (gunakan `Queue::fake()`/`Bus::fake()` dan cek job `SendEventRegistrationNotification`).
- **Unit tests** (untuk Action):
  - Sukses create record dengan input valid.
  - Gagal jika duplikat email.
  - Gagal jika quota kategori tidak mencukupi.

## Dokumentasi API
- Tambah dokumen markdown baru di folder `docs/` (mengikuti pola docs yang sudah ada) berisi:
  - Endpoint, auth, request body, contoh request (curl), response sukses & error, dan catatan duplikasi/quota.

Jika plan ini sudah sesuai, saya akan lanjut implementasi (route + controller + action + view modal + tests + docs) lalu menjalankan test suite terkait.