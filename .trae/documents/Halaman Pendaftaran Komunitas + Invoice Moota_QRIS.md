## Ringkasan
Anda akan punya 2 sisi:
1) **Publik (PIC komunitas)**: pilih event managed → isi PIC → tambah peserta komunitas (tabel khusus) → generate invoice (Moota / QRIS dinamis) → status pembayaran real-time.
2) **EO (pemilik event)**: menu/section baru untuk melihat community registrations + peserta → setelah invoice paid, bisa **import massal** ke tabel `participants` event sebagai **PAID**, lalu **email dikirim via queue/job**.

## Bagian A — Pendaftaran Komunitas (Publik)
### A1. Data Model (tabel baru, peserta tidak campur)
1) `community_registrations`
- `event_id`, `community_name` (atau code), `pic_name`, `pic_email`, `pic_phone`, `status` (`draft|invoiced|paid|cancelled`)

2) `community_participants`
- `community_registration_id`, `event_id`
- field peserta (mirip peserta biasa tapi berdiri sendiri)
- field pricing snapshot: `base_price`, `is_free`, `final_price`

3) `community_invoices` (direkomendasikan)
- `community_registration_id`, `transaction_id` (FK `transactions`), `payment_method` (`moota|qris`)
- snapshot nominal: `total_original`, `discount_amount`, `unique_code`, `final_amount`

Catatan: untuk status pembayaran dan polling, invoice komunitas akan **reuse** tabel `transactions` (agar tidak bikin mekanisme pembayaran baru).

### A2. Landing PIC  Pilih Event Managed
- URL contoh: `/komunitas/daftar`
- PIC memilih event dengan filter: `event_kind='managed'` + `status='published'` (+ optional `is_active`)
- Submit membuat `community_registration` dan redirect ke form.

### A3. Form Komunitas (PIC + peserta dinamis + daftar peserta)
- URL contoh: `/komunitas/daftar/{event:slug}/{communityRegistration}`
- Section PIC (update)
- Section tambah/hapus peserta (AJAX)
- Tabel bawah: daftar peserta hanya milik `community_registration_id`
- Tombol **Selesai & Generate Invoice**:
  - Melock data peserta (agar nominal invoice stabil)
  - Membuat `community_invoice` + `transaction` (pending)

### A4. Promo “Beli 10 Gratis 1” (tanpa platform fee)
- Platform fee: selalu 0.
- Pricing: ambil harga kategori (`price_*`, minimal `price_regular`).
- Aturan promo yang akan dipakai:
  - `freeCount = floor(totalParticipants / 11)`
  - Diskon = total harga dari `freeCount` peserta dengan harga termurah
  - Update `community_participants.is_free=true` untuk peserta gratis

### A5. Pembayaran Moota + QRIS Dinamis
**Moota (transfer)**
- Buat `transactions` `payment_gateway='moota'` `payment_status='pending'`
- Tambahkan `unique_code` (1..999) dan `final_amount = total - discount + unique_code`
- UI: modal instruksi + polling status.

**QRIS dinamis**
- Tambah `.env`:
  - `QRIS_STATIC` (QRIS static merchant)
  - `QRIS_NMID` (NMID)
- Implement service generator QRIS dinamis (port algoritma Anda ke PHP agar nominal tidak bisa dimanipulasi client).
- UI menampilkan QR Code memakai library yang sudah pernah dipakai di repo (`qrcodejs`).
- Status pembayaran:
  - Asumsi utama: settlement QRIS masuk rekening yang sama dan dibaca Moota → konfirmasi bisa tetap lewat webhook Moota berbasis `final_amount`.
  - `payment_channel` pada transaksi bisa diset `qris` agar EO tahu sumber pembayaran.
  - Webhook Moota akan disesuaikan supaya tidak “menghapus” label channel bila sudah `qris`.

### A6. Status real-time + error handling
- Polling ke endpoint status transaksi yang sudah ada: `/api/events/{slug}/payments/{transaction}/status?phone=...`
- UI indicator:
  - pending: polling 5 detik
  - error: tampil pesan + retry (backoff hingga 30 detik)
  - timeout 5 menit: tombol “Cek lagi”
  - paid/failed: stop polling

## Bagian B — EO: Manage Community Participants + Bulk Import
### B1. Menu  Section baru di panel EO
- Tambah menu di sidebar EO: **Community Participants**
- Halaman per event: `/eo/events/{event}/community-participants`
  - List `community_registrations` untuk event tsb
  - Detail peserta per komunitas
  - Status invoice (pending/paid/failed)
  - Aksi: **Import ke peserta event (Paid)**

### B2. Import massal ke tabel `participants` (status paid)
Tujuan: peserta komunitas tetap “terpisah” saat input, tapi setelah pembayaran valid, EO bisa memindahkan ke peserta event resmi.

Rencana teknis import:
- Prasyarat: invoice komunitas sudah `paid` (berdasarkan `transactions.payment_status='paid'`).
- Saat klik Import:
  1) Validasi quota kategori (lockForUpdate per kategori)
  2) Cek duplicate email / id_card di event (agar tidak bentrok)
  3) Buat 1 `transactions` baru untuk event:
     - `payment_status='paid'`, `paid_at=now()`, `payment_gateway='community_import'`
     - `admin_fee=0` (no platform fee)
     - `final_amount` bisa mengikuti invoice komunitas (atau 0, tapi lebih rapi jika sama)
     - `pic_data` dari PIC komunitas
  4) Buat banyak record di tabel `participants` untuk event (FK `transaction_id` tersebut)
  5) Dispatch job **`ProcessPaidEventTransaction`** (queue) untuk:
     - generate BIB
     - set status peserta confirmed
     - kirim email via dispatcher (queue)

Catatan: ini memanfaatkan pipeline yang sudah ada di sistem event, sehingga email pasti masuk job/queue.

### B3. UI/UX untuk EO
- Preview summary sebelum import:
  - total peserta, total gratis, total bayar
  - warning jika ada duplicate atau quota kurang
- Setelah import sukses:
  - tandai `community_registration`/`community_invoice` sebagai `imported_at` atau `import_status` (opsional) untuk mencegah import dua kali.

## Routing  Controller yang akan dibuat
- Public:
  - `CommunityRegistrationController` (landing, form, tambah/hapus peserta, generate invoice)
- EO:
  - `EO\CommunityParticipantController` (list komunitas per event, detail, import)

## Testing  Verifikasi
- Feature test publik:
  - buat registration komunitas, tambah peserta, generate invoice (moota/qris)
  - cek respons invoice memuat nominal + kode unik + (qris payload bila qris)
  - polling status mengembalikan pending
- Feature test EO:
  - import komunitas yang sudah paid → peserta masuk tabel `participants` event
  - pastikan job `ProcessPaidEventTransaction` didispatch

## Catatan penting (asumsi yang dipakai)
- Konfirmasi paid untuk QRIS dinamis diasumsikan tetap bisa terdeteksi dari mutasi rekening (Moota) berdasarkan nominal unik.
- Promo “10 gratis 1” diterapkan lintas kategori dengan memberi gratis pada peserta termurah.

Jika rencana ini sudah sesuai, saya lanjut implementasi tahap demi tahap (mulai dari skema tabel + route/controller publik, lalu EO manage  bulk import).