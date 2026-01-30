## 1) Pemetaan Sistem Saat Ini (End-to-End)

### 1.1 Entry point user (public)
- User membuka halaman event: `GET /event/{slug}` → [PublicEventController@show](file:///c:/laragon/www/ruanglari/app/Http/Controllers/PublicEventController.php) merender `events.show` / theme tertentu.
- Registrasi dilakukan via form di theme dan dikirim **AJAX** ke:
  - `POST /event/{slug}/register` → [EventRegistrationController@store](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EventRegistrationController.php#L146-L196)

### 1.2 Pembuatan transaksi & Snap token
- Controller memanggil [StoreRegistrationAction@execute](file:///c:/laragon/www/ruanglari/app/Actions/Events/StoreRegistrationAction.php#L35-L479) yang:
  - Validasi input (PIC + peserta)
  - Membuat record `transactions` (payment_gateway default `midtrans` via code path) + `participants`
  - Memanggil [MidtransService@createEventTransaction](file:///c:/laragon/www/ruanglari/app/Services/MidtransService.php#L289-L363)
  - Menyimpan `transactions.snap_token` dan `transactions.midtrans_order_id`

### 1.3 Kapan popup Midtrans muncul/hilang
- Popup Midtrans muncul hanya jika `data.snap_token` ada dan JS memanggil:
  - `snap.pay(data.snap_token, callbacks...)`
  - Contoh: [modern-dark.blade.php](file:///c:/laragon/www/ruanglari/resources/views/events/themes/modern-dark.blade.php#L1842-L1858)
- Popup “hilang” (dari perspektif user) jika:
  - User menutup popup → callback `onClose` (di code: tombol submit aktif lagi, tetapi **tidak ada persist token di client**).
  - Pembayaran sukses/pending → callback `onSuccess`/`onPending` melakukan redirect ke `GET /event/{slug}?payment=success|pending`.
  - Halaman direfresh/navigasi back/tab crash/koneksi drop → popup otomatis hilang karena konteks JS hilang.

### 1.4 Status pembayaran yang tercatat di database
- Tabel `transactions` memiliki:
  - `payment_status` enum: `pending | paid | failed | expired` (lihat migration: [create_transactions_table.php](file:///c:/laragon/www/ruanglari/database/migrations/2025_11_30_150642_create_transactions_table.php#L14-L33))
  - `midtrans_transaction_status` string (mis. `pending`, `settlement`, `capture`, `expire`, `cancel`)
  - `snap_token`, `midtrans_order_id`
- Update status saat ini:
  - Saat registrasi dibuat: umumnya `payment_status = pending`
  - Saat webhook Midtrans masuk:
    - `settlement|capture` + `fraud_status=accept` → `markAsPaid()` → `payment_status=paid`
    - `pending` → `payment_status=pending`
    - `deny|expire|cancel` → `markAsFailed()` → `payment_status=failed`
    - Implementasi: [EventTransactionWebhookController@handle](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EventTransactionWebhookController.php)

### 1.5 Mekanisme pengecekan status pembayaran yang ada
- Mekanisme utama: **webhook Midtrans** (`POST /events/transactions/webhook`). Tidak ada scheduler/cron untuk “reconcile” transaksi pending (lihat [Console Kernel](file:///c:/laragon/www/ruanglari/app/Console/Kernel.php)).
- Ada helper backend yang bisa cek status ke Midtrans:
  - [MidtransService@checkTransactionStatus](file:///c:/laragon/www/ruanglari/app/Services/MidtransService.php#L345-L363) memakai `Midtrans\Transaction::status($orderId)`
  - Namun saat ini **belum dipakai** oleh flow user (tidak ada endpoint publik untuk itu).

### 1.6 Catatan penting (potensi mismatch status)
- Ada beberapa query yang menganggap `payment_status` bisa bernilai `settlement|capture` (contoh: [PublicEventController](file:///c:/laragon/www/ruanglari/app/Http/Controllers/PublicEventController.php#L84-L88)), padahal enum `payment_status` tidak punya nilai tersebut. Praktiknya status Midtrans ada di `midtrans_transaction_status`, sedangkan `payment_status` hanya `paid/pending/failed/expired`.

---

## 2) Pain Points Utama (Dari kode & UX)

### 2.1 Popup hilang → user tidak bisa melanjutkan pembayaran yang sama
- Token Snap hanya dipakai di sesi browser saat itu. Begitu refresh/back/close, user tidak punya cara memanggil `snap.pay()` lagi.
- Token sebenarnya tersimpan di DB (`transactions.snap_token`), tetapi tidak ada UI/endpoint untuk mengambilnya dengan aman.

### 2.2 User sulit melihat status pending
- Redirect `?payment=pending` dilakukan, tapi tidak ada UI yang memproses query param tersebut untuk menampilkan status/instruksi.
- Tidak ada halaman “Riwayat transaksi” untuk user publik (non-auth) sehingga transaksi pending “menggantung”.

### 2.3 User tergoda registrasi ulang (bahkan ganti email)
- Sistem mencegah duplikasi registrasi aktif berdasarkan `id_card + category` untuk transaksi `pending/paid` (lihat [StoreRegistrationAction](file:///c:/laragon/www/ruanglari/app/Actions/Events/StoreRegistrationAction.php#L231-L253)).
- Akibatnya, saat transaksi stuck pending, user **tidak bisa registrasi ulang** (mengganti email pun tidak membantu karena rule pakai `id_card`).

---

## 3) Solusi Optimal (Minim perubahan core registrasi)

### Prinsip desain
- Jangan mengubah alur utama registrasi (tetap POST register → buat tx → dapat snap_token → snap.pay).
- Tambahkan “jalur samping” (recovery flow) yang:
  1) bisa menemukan transaksi pending milik user,
  2) bisa menampilkan status terkini,
  3) bisa memanggil ulang pembayaran (resume) dengan Snap token yang tersimpan atau token baru,
  4) bisa menandai transaksi pending menjadi failed jika sudah expire (agar user bisa daftar ulang tanpa trik).

### 3.1 Mekanisme recovery untuk pembayaran pending
- Tambah halaman ringan: **“Lanjutkan Pembayaran”** untuk setiap event:
  - `GET /event/{slug}/lanjutkan-pembayaran`
  - Isi: input sederhana (No. HP PIC + opsi ID registrasi jika ada), lalu list transaksi pending yang cocok.

### 3.2 Interface cek status & lanjutkan pembayaran
- UI/UX minimal:
  - Step 1: user input `No. HP` (atau `No. HP + ID registrasi`)
  - Step 2: tampilkan list transaksi pending (tanggal, total, kategori ringkas, status)
  - Step 3: tombol:
    - “Cek Status” (sinkronkan ke Midtrans API)
    - “Lanjutkan Pembayaran” (memanggil `snap.pay(snap_token)`)

### 3.3 Identifier unik selain email
- Pakai kombinasi:
  - `transaction_id` (ID registrasi), dan/atau
  - `pic_data.phone` (No. HP)
- Jika ingin lebih aman tanpa perubahan besar:
  - Input: `No. HP + ID Registrasi` (mengurangi risiko orang lain menebak transaksi)
- Jika ingin keamanan lebih tinggi (opsional):
  - Tambah `transactions.public_ref` (random, mis. 10–12 char) untuk ditampilkan ke user & dipakai lookup.

### 3.4 Penyimpanan token agar bisa resume tanpa registrasi ulang
- Saat ini `snap_token` sudah tersimpan di DB.
- Recovery endpoint cukup mengembalikan token untuk transaksi `pending` yang terverifikasi.
- Jika token kadaluarsa/invalid:
  - Endpoint melakukan `Transaction::status(order_id)` ke Midtrans:
    - jika `expire/cancel/deny` → update DB menjadi `failed` (unblock registrasi baru)
    - jika masih `pending` tapi token invalid → generate **attempt baru** (opsi low-risk dengan audit):
      - Menyimpan token & `midtrans_order_id` baru, sambil menyimpan `midtrans_order_id` lama ke “history” (opsional schema kecil) untuk menghindari pembayaran nyasar.

---

## 4) Spesifikasi Teknis

### 4.1 Endpoint API (minimal)
1) **Lookup transaksi pending**
- `POST /api/events/{slug}/payments/pending`
- Body: `{ phone: string, transaction_id?: number|string }`
- Output: list transaksi pending (masked) + id.

2) **Cek status transaksi (server-to-server)**
- `GET /api/events/{slug}/payments/{transaction}/status`
- Validasi ownership via phone (query/body) sebelum respon.
- Implementasi: panggil `MidtransService->checkTransactionStatus(midtrans_order_id)` lalu map ke status internal.

3) **Lanjutkan pembayaran**
- `POST /api/events/{slug}/payments/{transaction}/resume`
- Output: `{ snap_token, payment_status, midtrans_transaction_status }`
- Jika status sudah paid/failed → jangan keluarkan token, keluarkan status + pesan.

### 4.2 Modifikasi database schema (minimal)
- **Opsi paling minimal (tanpa schema)**: gunakan `transaction_id + phone` sebagai identifier.
- **Opsi recommended (1 kolom kecil)**: tambah `transactions.public_ref` untuk “ID registrasi” yang lebih user-friendly & tidak mudah ditebak.
- **Opsi advanced (low risk, tapi lebih robust)**: table kecil `transaction_payment_attempts` untuk menyimpan history order_id/snap_token agar regenerasi token tidak risk double-pay.

### 4.3 Integrasi Midtrans API untuk transaksi belum selesai
- Gunakan `Midtrans\Transaction::status($orderId)` (sudah ada method di service).
- Mapping status:
  - `settlement|capture` → internal `paid`
  - `pending` → internal `pending`
  - `expire|cancel|deny` → internal `failed` (atau `expired` jika ingin memakai enum)
- Penting: update DB dari hasil status check agar rule “active registration” tidak membuat user stuck.

### 4.4 UI/UX flow “Lanjutkan Pembayaran”
- Di event theme:
  - Jika URL mengandung `?payment=pending` → tampilkan banner “Pembayaran pending” + tombol “Lanjutkan Pembayaran”.
  - Tombol mengarah ke `/event/{slug}/lanjutkan-pembayaran`.
- Halaman lanjutkan pembayaran:
  - Form input phone (+ optional ID registrasi)
  - Hasil list pending
  - Button “Lanjutkan” → memanggil endpoint resume, lalu `snap.pay(token)`.

---

## 5) Kriteria Keberhasilan
- User bisa melanjutkan pembayaran pending tanpa registrasi ulang.
- Perubahan pada alur registrasi utama minimal (hanya menambah “return transaction_id/public_ref” opsional dan UI banner).
- Edge cases tertangani:
  - Pending yang sudah expired bisa di-detect (status check) dan diubah ke failed sehingga user bisa daftar ulang.
  - Webhook delay tidak membuat user stuck (status check menjadi fallback).
- Implementasi cepat dan risiko rendah (mengandalkan data yang sudah ada: snap_token, order_id, phone).

---

## Rencana Implementasi Detail (Jika Anda setuju)
1) Tambah halaman publik `lanjutkan-pembayaran` + controller ringan.
2) Tambah 2–3 endpoint API (lookup, status, resume) dengan rate limit + validasi phone.
3) Integrasi status sync memakai `MidtransService->checkTransactionStatus`.
4) Update theme event untuk banner `payment=pending` + link.
5) (Opsional) Tambah `public_ref` kolom untuk ID registrasi yang user-friendly.
6) Tambah test feature untuk:
   - lookup by phone + tx id
   - status sync mapping
   - resume only jika pending

## Estimasi Effort (Rendah Risiko)
- Backend endpoint + status sync: 0.5–1 hari
- UI halaman lanjutkan pembayaran + banner: 0.5–1 hari
- Testing + hardening (rate limit, masking, edge cases): 0.5–1 hari
- Total: 1.5–3 hari kerja (tanpa opsi “payment_attempts” table). Jika tambah history attempts: +0.5–1 hari.

Jika plan ini disetujui, saya bisa lanjut ke implementasi bertahap dengan perubahan minimal core registrasi.