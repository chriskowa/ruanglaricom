# EO Manual Participants API

## Endpoint

**POST** `/eo/events/{event}/participants`

Menambahkan peserta secara manual ke event oleh operator EO.

## Authentication & Authorization

- Wajib login (session web).
- Wajib role `eo`.
- Wajib EO adalah pemilik event (event `user_id` sama dengan user yang login). Jika tidak, akan `403`.

## Request

Content-Type: `application/x-www-form-urlencoded` atau `application/json`

Field:

- `name` (required, string, max 255)
- `gender` (optional, enum: `male|female`)
- `email` (required, email, max 255)
- `phone` (required, numeric string, min 10 max 15)
- `id_card` (required, string, max 50)
- `category_id` (required, exists `race_categories.id`, harus milik event)
- `date_of_birth` (optional, date, before today)
- `target_time` (optional, string `HH:MM:SS`, tidak boleh `00:00:00`)
- `jersey_size` (optional, string, max 10)
- `emergency_contact_name` (optional, string, max 255)
- `emergency_contact_number` (optional, numeric string, min 10 max 15)

Validasi duplikasi:

- Jika email sudah ada pada event yang sama, request akan ditolak (`422`).

## Response

### JSON (jika `Accept: application/json`)

Status: `201`

```json
{
  "success": true,
  "transaction_id": 123,
  "participant_id": 456
}
```

### HTML (default web form)

- Redirect kembali ke halaman peserta `GET /eo/events/{event}/participants` dengan flash message `success`.

## Error Responses (JSON)

- `401/302` jika belum login (pada web biasanya redirect ke halaman login).
- `403` jika bukan EO atau bukan pemilik event.
- `422` jika validasi gagal (termasuk email duplikat atau kategori tidak valid atau kuota penuh).

## Notes

- Peserta manual dibuat dengan transaksi `payment_gateway = manual`, `payment_status = paid`, dan `admin_fee = 0`.
- Email konfirmasi dikirim **sinkron** (langsung saat request diproses). Pengiriman memakai mailer Laravel yang aktif (mis. jika `MAIL_MAILER=sendmail`, maka akan via sendmail).

## Example (curl)

```bash
curl -X POST "https://your-domain.test/eo/events/1/participants" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -b "laravel_session=YOUR_SESSION_COOKIE" \
  -d '{
    "name": "Budi",
    "gender": "male",
    "email": "budi@example.com",
    "phone": "081234567890",
    "id_card": "1234567890",
    "category_id": 10
  }'
```
