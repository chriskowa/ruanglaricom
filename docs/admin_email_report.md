# Admin: Email Report

Fitur ini menyediakan halaman admin untuk memonitor email report yang dikirim, termasuk filter dan export.

## Menu
- Sidebar Admin → **Email Report**
- Sidebar Admin → **Email Monitoring**

## Halaman
### 1) List Email Report (HTML)
`GET /admin/email-reports`

Query params (opsional):
- `event_id` (integer)
- `status` (`pending|processing|sent|failed`)
- `date_from` (YYYY-MM-DD)
- `date_to` (YYYY-MM-DD)

### 2) Export Excel (CSV)
`GET /admin/email-reports/export`

Query params sama seperti list.

Output:
- CSV UTF-8 dengan BOM agar terbaca baik di Excel.

### 3) Export PDF (Print View)
`GET /admin/email-reports/print`

Query params sama seperti list.

Catatan:
- Halaman ini otomatis memanggil `window.print()` sehingga dapat “Save as PDF”.

## Monitoring
`GET /admin/email-monitoring`

Menampilkan:
- Backlog queue email (tabel `jobs` untuk queue `emails-*`)
- Reservasi rate limit instant (tabel `event_email_minute_counters`)
- Error email ticket terbaru (tabel `event_email_delivery_logs`)
- Error prediksi terbaru (tabel `prediction_error_logs`)

