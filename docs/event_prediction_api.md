# API: Event Time Prediction

## Halaman (HTML)
`GET /event/{slug}/prediction`

## Hitung Prediksi (JSON)
`POST /event/{slug}/prediction/predict`

Headers:
- `Accept: application/json`
- `X-CSRF-TOKEN: {token}`

Body (form):
- `category_id` (required, integer)
- `weather` (required: `panas|dingin|hujan|gerimis`)
- `pb_h` (required, 0–23)
- `pb_m` (required, 0–59)
- `pb_s` (required, 0–59)
- `pb_date` (required, YYYY-MM-DD; maksimal 3 bulan terakhir)

Response sukses:
```json
{
  "ok": true,
  "event": { "id": 1, "name": "Event", "slug": "event" },
  "category": { "id": 10, "name": "5K", "distance_km": 5, "master_gpx_id": 3 },
  "result": {
    "vdot": 45.2,
    "distance_key": "5k",
    "pb_time": "00:25:00",
    "penalties": { "weather": 0.03, "elevation": 0.02, "terrain": 0.01, "total": 0.06 },
    "prediction": { "optimistic": "00:25:45", "realistic": "00:26:30", "pessimistic": "00:27:00" },
    "confidence": 0.8,
    "route": { "distance_km": 5, "elevation_gain_m": 60, "elevation_loss_m": 60, "gain_per_km": 12.0, "master_gpx_id": 3 },
    "strategy": "..."
  }
}
```

Response error validasi:
- HTTP 422 (`ok=false`, `message` atau `errors`)

