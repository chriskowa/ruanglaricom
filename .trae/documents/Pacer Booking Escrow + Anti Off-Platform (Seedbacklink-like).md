## Jawaban Singkat (Platform fee harus masuk dompet admin?)
Iya—kalau tujuan Anda adalah mencegah transaksi off-platform dan membuat model “marketplace/escrow” seperti Seedbacklink, maka **platform fee/commission harus masuk ke dompet platform (admin/system wallet)** sebagai revenue platform, bukan ikut masuk ke pacer/EO.

Catatan penting: **di code saat ini platform fee/commission memang sudah dihitung, tapi belum pernah benar-benar diposting ke wallet admin**.
- Event: `admin_fee` ikut masuk ke wallet EO karena yang dideposit `final_amount` ([ProcessPaidEventTransaction](file:///c:/laragon/www/ruanglari/app/Jobs/ProcessPaidEventTransaction.php)).
- Marketplace: `commission_amount` hanya disimpan di order tapi tidak pernah dideposit ke wallet admin ([CheckoutController](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Marketplace/CheckoutController.php), [OrderController](file:///c:/laragon/www/ruanglari/app/Http/Controllers/Marketplace/OrderController.php)).

## Target Model (Seedbacklink-like)
- Runner bayar → uang masuk escrow platform.
- Platform fee dianggap revenue platform.
- Pacer dibayar **net** (setelah fee) saat booking selesai.
- Jika dispute/refund, logika refund jelas (fee refundable/tidak) sesuai kebijakan.

## Rencana Step-by-Step (Terstruktur)

### Step 0 — Tetapkan “Platform Wallet” (Dompet Admin/System)
**Tujuan:** semua platform fee terkumpul di 1 wallet yang konsisten.
- Opsi A (paling sederhana): pakai wallet milik user admin (sudah dibuat di seeder) ([AdminUserSeeder](file:///c:/laragon/www/ruanglari/database/seeders/AdminUserSeeder.php)).
- Opsi B (lebih rapi): buat “system user” khusus (mis. `platform@ruanglari.com`) + wallet = “Platform Revenue Wallet”.
- Simpan referensinya di settings agar tidak hardcode, mis. `platform_wallet_user_id` via `AppSettings`.

### Step 1 — Definisikan Booking Contract (PacerBooking)
- Entity `PacerBooking` mirip marketplace order:
  - pricing: `total_amount`, `platform_fee_amount`, `pacer_amount`
  - state: `pending → paid → confirmed → completed / cancelled / disputed`
  - payment: `midtrans_order_id`, `snap_token`

### Step 2 — Sumber Platform Fee (sesuaikan dengan admin setting)
Karena di admin sudah ada “platform fee”, rencananya harus konsisten:
- Gunakan **satu** sumber untuk komisi pacer booking:
  - Prefer: `AppSettings::get('platform_fee_percent')` (sudah ada di admin integrations settings; saat ini belum dipakai di transaksi lain).
- Hitung:
  - `platform_fee_amount = total_amount * percent`
  - `pacer_amount = total_amount - platform_fee_amount`

### Step 3 — Midtrans Checkout (Escrow)
- Buat Snap transaction untuk `PacerBooking` (reuse pola marketplace/event) ([MidtransService](file:///c:/laragon/www/ruanglari/app/Services/MidtransService.php)).
- Saat runner bayar sukses (webhook `settlement/capture`): set booking `paid`.

### Step 4 — Posting ke Wallet (Ini bagian paling penting untuk fee)
**Metode yang disarankan (paling aman untuk escrow):**
1) Pada saat booking menjadi `paid`:
   - Buat 1 record escrow di wallet platform (admin/system) menggunakan `locked_balance`:
     - `platform_wallet.locked_balance += total_amount`
   - Catat `WalletTransaction` dengan metadata `{type:'pacer_booking_escrow', booking_id, total, fee, pacer_amount}`.
2) Pada saat booking `completed`:
   - Release payout:
     - `platform_wallet.locked_balance -= total_amount`
     - `platform_wallet.balance += platform_fee_amount` (revenue platform)
     - `pacer_wallet.balance += pacer_amount`
   - Catat 2 transaksi:
     - `platform_fee_income`
     - `pacer_payout`

**Kenapa begini?**
- Platform fee **benar-benar** masuk wallet admin.
- Uang pacer baru cair saat selesai (anti off-platform + trust).

### Step 5 — Anti Off-Platform: Gate kontak + Chat
- Kontak pacer (WA/IG) **disembunyikan sebelum paid**.
- Tombol utama di profile pacer:
  - `Request Booking` (selalu)
  - Setelah `paid`: `Chat WhatsApp` / `Unlock contact` (opsional)
- Tambahkan warning di form: blok/flag kalau user menulis nomor/URL di notes.

### Step 6 — Notifikasi Booking (Email + WhatsApp)
**Trigger minimal yang direkomendasikan:**
- `paid` → pacer dapat notifikasi masuk.
- `confirmed` → runner dapat notifikasi.
- `completed` → pacer dapat notifikasi payout.

Implementasi:
- In-app: gunakan model [Notification](file:///c:/laragon/www/ruanglari/app/Models/Notification.php).
- Email: buat mailable baru (mirip [EventRegistrationSuccess](file:///c:/laragon/www/ruanglari/app/Mail/EventRegistrationSuccess.php)).
- WhatsApp: gunakan helper [WhatsApp.php](file:///c:/laragon/www/ruanglari/app/Helpers/WhatsApp.php) dalam job queue (pattern [SendEventRegistrationNotification](file:///c:/laragon/www/ruanglari/app/Jobs/SendEventRegistrationNotification.php)).
  - Format WA ke pacer: ringkas (invoice, race, tanggal, target pace, tombol link ke dashboard pacer).

### Step 7 — Dashboard Pacer + Admin Moderation
- Pacer bisa: accept/confirm, mark done, dispute.
- Admin bisa: force refund/force release, audit chat/log.

### Step 8 — Konsistensi Global (Opsional tapi disarankan)
Sekalian rapikan agar seluruh platform fee benar-benar masuk dompet admin:
- **Event:** ubah posting wallet agar `admin_fee` masuk platform wallet, dan EO hanya menerima `organizer_amount`.
- **Marketplace:** saat order completed, selain `seller_amount` ke seller wallet, posting `commission_amount` ke platform wallet.

## Output Akhir
- Platform fee benar-benar tercatat dan masuk dompet admin/system.
- Model escrow mengunci uang sampai service selesai.
- Notifikasi booking otomatis ke pacer via email + WhatsApp.
- Kontak pacer tidak bocor sebelum pembayaran, sehingga jauh lebih sulit transaksi di luar platform.

Jika plan ini oke, saya lanjut implementasi dimulai dari Step 0–6 (core escrow + fee + notifikasi) dulu, baru Step 7–8 (dashboard dan merapikan event/marketplace agar fee konsisten).