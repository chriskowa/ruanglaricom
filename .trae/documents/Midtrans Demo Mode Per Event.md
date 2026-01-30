## Kekhawatiran Anda (Valid)
Kalau mode sandbox “global” dipakai untuk **wallet topup**, user bisa terlihat “punya saldo” lalu withdraw (tergantung rule wallet). Jadi solusinya harus **diisolasi**: sandbox hanya untuk **pembayaran event**, bukan untuk wallet/membership/marketplace/pacer.

## Prinsip Solusi
- **Config Midtrans default tetap production** (tidak diubah oleh Demo Mode event).
- “Demo Mode” disimpan **per event** dan hanya mempengaruhi:
  - pembuatan Snap token untuk transaksi event,
  - script Snap (`snap.js`) di halaman event public.
- Flow lain (wallet topup, membership, marketplace, pacer booking) tetap memakai config production seperti sekarang.

## Rencana Implementasi (Tidak Ribet, Aman dari Wallet)
### 1) Update `config/midtrans.php`
- Tambahkan hanya 2 entry baru (tanpa mengubah perilaku existing):
  - `client_key_sandbox` ← `env('MIDTRANS_CLIENT_KEY_SANDBOX')`
  - `server_key_sandbox` ← `env('MIDTRANS_SERVER_KEY_SANDBOX')`
  - `base_url_sandbox` ← `'https://app.sandbox.midtrans.com'`
- **Jangan** mengubah `client_key/server_key/base_url/is_production` yang sudah ada (tetap dianggap production / existing behavior).

### 2) Tambah “Demo Mode” per event (EO create + edit)
- Tambahkan radio button di:
  - [create.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/create.blade.php)
  - [edit.blade.php](file:///c:/laragon/www/ruanglari/resources/views/eo/events/edit.blade.php)
- Field: `payment_config[midtrans_demo_mode]` (boolean)
  - ON  → sandbox untuk event
  - OFF → production untuk event
- Validasi server-side di [EO/EventController@store/update](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EO/EventController.php):
  - `payment_config.midtrans_demo_mode` wajib boolean jika ada.

### 3) Switching env hanya pada transaksi event (backend)
- Refactor ringan di [MidtransService](file:///c:/laragon/www/ruanglari/app/Services/MidtransService.php):
  - `createEventTransaction()` memilih server key + isProduction **berdasarkan event.payment_config.midtrans_demo_mode**.
  - `createTopupTransaction()` dan `createMembershipTransaction()` **tetap** memakai config lama (production) → tidak terpengaruh.

### 4) Switching env hanya pada halaman event public (frontend)
- Update semua theme event yang memuat Snap script agar memilih:
  - `base_url` = sandbox jika demo mode aktif, else base_url production.
  - `data-client-key` = sandbox client key jika demo mode aktif, else client key production.
- Ini hanya mengganti beberapa baris pada masing-masing theme (tanpa loader dinamis).

### 5) Anti “ketuker mode” untuk transaksi event
- Tambahkan kolom baru di `transactions`:
  - `midtrans_mode` (`sandbox|production`) default `production`.
- Saat Snap token dibuat untuk event:
  - simpan `transactions.midtrans_mode` sesuai mode.
  - buat `order_id` prefix berbeda: `EVENT-SBX-...` vs `EVENT-PRD-...`.
- Di [EventTransactionWebhookController](file:///c:/laragon/www/ruanglari/app/Http/Controllers/EventTransactionWebhookController.php):
  - cek prefix order_id vs `transactions.midtrans_mode`, mismatch → return 409 (tidak mark paid).

### 6) (Sangat Disarankan) Signature validation untuk webhook event
- Agar sandbox tidak bisa “memalsukan paid” dan juga mencegah penyalahgunaan:
  - Validasi `signature_key` memakai server key yang sesuai `transactions.midtrans_mode`.
  - Jika invalid → return 401.

## Testing
- Feature test: EO simpan `payment_config[midtrans_demo_mode]`.
- Feature test: pembuatan transaksi event menyimpan `midtrans_mode` + order_id prefix.
- Feature test: webhook menolak mismatch mode dan signature invalid.

## Jaminan Tidak Mengganggu Wallet
- Wallet topup menggunakan `createTopupTransaction()` dan halaman wallet memuat `config('midtrans.client_key')` (production) → **tidak berubah**.
- Tidak ada switch global yang bisa mengubah behavior wallet.

Jika Anda setuju, saya lanjut implementasi sesuai rencana ini (isolated sandbox untuk event saja).