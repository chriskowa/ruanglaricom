## Posisi Produk (Nilai Lebih)
- Fokus sebagai **marketplace komunitas lari** (gear + kebutuhan event), bukan general commerce seperti Shopee/Tokopedia.
- 2 pilar utama:
  - **Lelang untuk barang langka/limited/preloved premium** (sepatu rare, jersey event, jam/strap, bib collector).
  - **Titip Jual (konsinyasi) dengan kurasi + escrow + jaminan**: user titip barang, platform bantu foto/grade/listing/shipping, buyer lebih percaya.

## Konsep Fitur (UX & Bisnis)
- **Fixed Price vs Auction**
  - Fixed price: seperti marketplace biasa (sudah ada).
  - Auction: durasi 1–7 hari, starting price, min increment, opsi reserve price, opsional “Buy Now”.
  - Anti-snipe: jika bid masuk menit terakhir, auto-extend X menit.
- **Titip Jual (Konsinyasi)**
  - Alur: user ajukan titip → drop-off/pickup → QC/grade (A/B/C) → foto & listing oleh admin → sold → payout.
  - Transparansi biaya: fee platform + fee konsinyasi (lebih tinggi karena ada jasa).
  - “Trust layer”: verifikasi kondisi, foto standar, label grade, riwayat pemakaian.
- **Differentiator komunitas**
  - Rating seller berbasis transaksi lari (dan/atau verifikasi komunitas), opsi meetup pickup, bundling dengan event.

## Rancangan Teknis (MVP yang realistis di codebase sekarang)
- Base saat ini sudah ada: `marketplace_products`, `marketplace_orders`, wallet payout setelah order completed.
- Tambahan minimal untuk Auction + Konsinyasi:
  - **Tambahkan field pada marketplace_products**:
    - `sale_type` = `fixed|auction`
    - `auction_start_at`, `auction_end_at`, `starting_price`, `current_price`, `min_increment`, `reserve_price` (opsional), `buy_now_price` (opsional)
    - `auction_status` = `draft|running|ended|cancelled`
    - `fulfillment_mode` = `self_ship|consignment`
    - `consignment_status` = `none|requested|received|listed|sold|returned`
  - **Tabel baru `marketplace_bids`**:
    - `product_id`, `user_id`, `amount`, timestamps; index `(product_id, amount)`
  - (Opsional tahap 2) **Tabel `marketplace_consignment_intakes`** untuk data intake: dropoff location, grade, catatan QC, foto tambahan.

## Alur Auction (MVP)
- `POST /marketplace/product/{slug}/bid`:
  - Validasi: login, bukan owner, auction sedang running, amount >= current_price + min_increment.
  - Simpan bid, update `current_price`, extend waktu jika last-minute.
- Finalisasi auction:
  - Command terjadwal (Laravel Scheduler) untuk menutup auction yang lewat `auction_end_at`.
  - Tentukan pemenang (bid tertinggi), buat `MarketplaceOrder` status `pending_payment` dengan harga menang.
  - Notif pemenang + deadline bayar (mis. 30 menit / 2 jam). Jika tidak bayar: order cancelled, auction bisa “relist” atau tawarkan ke runner-up.

## Alur Titip Jual (MVP)
- Seller memilih `fulfillment_mode=consignment` saat buat produk, status awal `requested`.
- Admin screen untuk:
  - Approve intake → set grade & price recommendation → publish listing.
  - Saat sold, proses shipping ditangani admin/warehouse.
- Payout:
  - Tetap pakai mekanisme existing `markCompleted()` (credit ke wallet seller), tapi formula `seller_amount` dikurangi fee konsinyasi.

## UI/UX yang akan diubah
- Halaman detail produk:
  - Jika `sale_type=auction`: tampil countdown, current bid, tombol bid, riwayat bid.
  - Jika `fixed`: tetap tombol checkout sekarang.
- Seller dashboard:
  - Toggle “Jual Normal / Lelang”, dan opsi “Titip Jual”.
- Admin:
  - Panel auction monitoring (running/ended), panel consignment intake.

## Keamanan & Anti-fraud (minimum)
- Rate limit bidding per user.
- Cegah self-bid.
- (Opsional tapi direkomendasikan) escrow via wallet `locked_balance` untuk deposit bidder.

## Verifikasi
- Feature test untuk aturan bid, anti-snipe, finalisasi auction.
- Test alur consignment status + perhitungan fee.

Jika plan ini disetujui, saya akan mulai dari MVP: **sale_type + marketplace_bids + UI detail + finalizer command**, lalu lanjut **titip jual intake + admin flow**.