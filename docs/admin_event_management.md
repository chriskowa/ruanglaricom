# Panduan Admin: Event Management

## Akses

- Menu: **Event Management** (sidebar admin)
- URL: `/admin/events`
- Hak akses: hanya role `admin`

## Tampilan Data

Tabel menampilkan kolom:

- **Event**: nama event dan lokasi/kota
- **EO**: nama akun EO (fallback ke `organizer_name`)
- **Tanggal**: tanggal & jam dari `start_at`
- **Publish**: `draft` / `published` (badge)
- **Featured**: `Yes` / `No`
- **Aktif**: `Aktif` / `Non-aktif`

## Filter & Pencarian (AJAX)

- Pencarian mendukung **nama event**, **lokasi**, dan **nama EO**.
- Filter:
  - Status publish: `draft` / `published` / `archived`
  - Featured: `Featured` / `Unfeatured`
  - Aktif: `Aktif` / `Non-aktif`
  - EO: pilih EO tertentu
- Pagination tetap berjalan tanpa reload halaman.

## Aksi (Real-time)

Semua aksi di bawah ini berjalan via AJAX, tabel otomatis refresh, dan menampilkan notifikasi sukses/gagal:

- **Toggle Featured**: menandai/menghapus event dari featured (`is_featured`).
- **Aktif/Non-aktif**: mengaktifkan/menonaktifkan event (`is_active`).
- **Toggle Publish**: mengubah status publish `published` ⇄ `draft`.
- **Delete**: menghapus event (dengan konfirmasi).

## Audit Trail

Setiap perubahan status oleh admin dicatat ke tabel `event_audits`:

- `toggle_featured`
- `toggle_active`
- `set_status`
- `delete`

Field audit mencakup `admin_id`, `action`, `before`, `after`, `ip`, dan `user_agent`.

## Proteksi Concurrent Update

Untuk mencegah race condition saat dua admin melakukan aksi pada event yang sama:

- Sistem memakai `lock_version` pada tabel `events`.
- Jika `lock_version` yang dikirim dari UI sudah tidak sama dengan data terbaru, server mengembalikan **HTTP 409** dan UI akan melakukan refresh tabel.

