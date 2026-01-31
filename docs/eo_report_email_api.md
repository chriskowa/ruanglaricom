# API: Email Laporan EO

Dokumen ini menjelaskan endpoint untuk membuat, memonitor, dan mengirim ulang email laporan EO.

## Autentikasi

- Semua endpoint berada di prefix `/eo/*` dan membutuhkan login user dengan role `eo`.
- Request AJAX harus menyertakan header `X-CSRF-TOKEN`.

## Endpoint

### 1) Dashboard (HTML)

`GET /eo/email-reports`

Query params (opsional):
- `event_id` (integer)
- `status` (string: `pending|processing|sent|failed`)
- `date_from` (YYYY-MM-DD)
- `date_to` (YYYY-MM-DD)

### 2) Data Real-time (JSON)

`GET /eo/email-reports/data`

Query params sama seperti dashboard HTML.

Response:
```json
{
  "ok": true,
  "filters": {
    "event_id": 123,
    "status": "failed",
    "date_from": "2026-01-01",
    "date_to": "2026-01-31"
  },
  "deliveries": [
    {
      "id": 1,
      "event_id": 123,
      "event_name": "Nama Event",
      "to_email": "eo@example.com",
      "subject": "Laporan Event: Nama Event",
      "status": "failed",
      "attempts": 1,
      "created_at": "2026-01-31T10:00:00.000000Z",
      "first_attempt_at": "2026-01-31T10:00:10.000000Z",
      "last_attempt_at": "2026-01-31T10:00:10.000000Z",
      "sent_at": null,
      "failure_code": "transport_error",
      "failure_message": "Connection timeout"
    }
  ]
}
```

### 3) Kirim Email Laporan (Enqueue)

`POST /eo/email-reports/send`

Body (form-data / x-www-form-urlencoded):
- `event_id` (required, integer)
- `to_email` (required, email)
- `subject` (optional, string)
- `date_from` (optional, date)
- `date_to` (optional, date)
- `status` (optional, string; disimpan sebagai filter laporan)

Response:
```json
{ "ok": true, "delivery_id": 10 }
```

### 4) Kirim Ulang Manual

`POST /eo/email-reports/{delivery}/resend`

Body (opsional):
- `to_email` (optional, email) — jika kosong, memakai alamat email sebelumnya

Response:
```json
{ "ok": true, "delivery_id": 11 }
```

## Status Delivery

- `pending`: sudah tercatat, menunggu diproses job
- `processing`: job sedang berjalan
- `sent`: email berhasil terkirim
- `failed`: gagal (lihat `failure_code` dan `failure_message`)

`failure_code` yang digunakan saat ini:
- `invalid_email`
- `transport_error`
- `server_error`
- `bounce` (heuristik / siap integrasi webhook ESP)
- `unknown`

