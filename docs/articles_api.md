# Articles & Blog V1 API Documentation

## Overview
API v1 publik untuk mengakses daftar artikel blog, artikel unggulan, artikel terbaru, artikel terpopuler/trending, kategori artikel, dan detail artikel di platform Ruang Lari.

---

## Endpoints Summary

| Method | Endpoint | Description | Auth Required |
|---|---|---|---|
| `GET` | `/api/v1/articles` | Browse & search published articles (paginated) | No |
| `GET` | `/api/v1/articles/latest` | Get top latest published articles | No |
| `GET` | `/api/v1/articles/featured` | Get featured articles (for hero slider) | No |
| `GET` | `/api/v1/articles/trending` | Get trending / most popular articles (ordered by views count) | No |
| `GET` | `/api/v1/articles/popular` | Alias for `/api/v1/articles/trending` | No |
| `GET` | `/api/v1/articles/categories` | List all blog categories with article counts | No |
| `GET` | `/api/v1/articles/{slug}` | Get full article details and related articles by slug or ID | No |

---

## Detailed Endpoint Specifications

### 1. Browse Articles
**Endpoint:** `GET /api/v1/articles`  
**Auth Required:** No  

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|---|---|---|---|---|
| `category` | string/number | No | - | Filter by category ID or slug |
| `featured` | boolean | No | `false` | Set `true` or `1` to filter featured articles only |
| `search` | string | No | - | Search term matching title, excerpt, or content |
| `per_page` | integer | No | `10` | Number of items per page |
| `page` | integer | No | `1` | Page number |

**Example Request:**
`GET /api/v1/articles?category=training&search=marathon&per_page=10`

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Daftar artikel blog berhasil dimuat",
  "data": {
    "articles": [
      {
        "id": 10,
        "title": "Panduan Latihan Marathon untuk Pemula",
        "slug": "panduan-latihan-marathon-pemula",
        "excerpt": "Ringkasan tips latihan marathon...",
        "featured_image": "storage/articles/marathon.jpg",
        "views_count": 850,
        "is_featured": true,
        "published_at": "2026-07-28 10:00:00",
        "category": {
          "id": 2,
          "name": "Training",
          "slug": "training"
        }
      }
    ],
    "pagination": {
      "total": 25,
      "per_page": 10,
      "current_page": 1,
      "last_page": 3
    }
  }
}
```

---

### 2. Latest Articles
**Endpoint:** `GET /api/v1/articles/latest`  
**Auth Required:** No  

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|---|---|---|---|---|
| `limit` | integer | No | `5` | Maximum number of articles to retrieve (1-20) |

**Example Request:**
`GET /api/v1/articles/latest?limit=5`

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Artikel terbaru berhasil dimuat",
  "data": [
    {
      "id": 15,
      "title": "Tips Nutrisi Sebelum Race",
      "slug": "tips-nutrisi-sebelum-race",
      "excerpt": "Persiapan carbo loading yang tepat...",
      "featured_image": "storage/articles/nutrition.jpg",
      "views_count": 120,
      "published_at": "2026-07-30 08:00:00",
      "category": {
        "id": 3,
        "name": "Nutrisi",
        "slug": "nutrisi"
      }
    }
  ]
}
```

---

### 3. Featured Articles
**Endpoint:** `GET /api/v1/articles/featured`  
**Auth Required:** No  

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|---|---|---|---|---|
| `limit` | integer | No | `5` | Maximum number of featured articles (1-10) |

**Example Request:**
`GET /api/v1/articles/featured?limit=3`

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Artikel unggulan berhasil dimuat",
  "data": [
    {
      "id": 1,
      "title": "Ruang Lari Community Championship 2026",
      "slug": "ruang-lari-community-championship-2026",
      "is_featured": true,
      "views_count": 3400,
      "category": {
        "id": 1,
        "name": "Event",
        "slug": "event"
      }
    }
  ]
}
```

---

### 4. Trending / Popular Articles (NEW)
**Endpoint:** `GET /api/v1/articles/trending`  
**Alias:** `GET /api/v1/articles/popular`  
**Auth Required:** No  

**Query Parameters:**
| Parameter | Type | Required | Default | Description |
|---|---|---|---|---|
| `limit` | integer | No | `5` | Maximum number of popular articles (1-20) |

**Description:**
Mengembalikan daftar artikel terpopuler berdasarkan jumlah dibaca (`views_count DESC`) dan tanggal publikasi terbaru.

**Example Request:**
`GET /api/v1/articles/trending?limit=5`

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Artikel terpopuler berhasil dimuat",
  "data": [
    {
      "id": 12,
      "title": "5 Sepatu Lari Daily Trainer Terbaik 2026",
      "slug": "5-sepatu-lari-daily-trainer-terbaik-2026",
      "excerpt": "Rekomendasi sepatu lari harian terbaik...",
      "featured_image": "storage/articles/shoes.jpg",
      "views_count": 4520,
      "published_at": "2026-07-25 14:00:00",
      "category": {
        "id": 4,
        "name": "Gear",
        "slug": "gear"
      }
    },
    {
      "id": 8,
      "title": "Cara Mencegah Cedera Shin Splints",
      "slug": "cara-mencegah-cedera-shin-splints",
      "excerpt": "Langkah pemulihan dan pencegahan sakit tulang kering...",
      "featured_image": "storage/articles/shinsplints.jpg",
      "views_count": 3890,
      "published_at": "2026-07-20 09:30:00",
      "category": {
        "id": 2,
        "name": "Training",
        "slug": "training"
      }
    }
  ]
}
```

---

### 5. Blog Categories
**Endpoint:** `GET /api/v1/articles/categories`  
**Auth Required:** No  

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Kategori blog berhasil dimuat",
  "data": [
    {
      "id": 1,
      "name": "Event",
      "slug": "event",
      "articles_count": 12
    },
    {
      "id": 2,
      "name": "Training",
      "slug": "training",
      "articles_count": 45
    }
  ]
}
```

---

### 6. Article Detail
**Endpoint:** `GET /api/v1/articles/{slug}`  
**Auth Required:** No  

**Path Parameters:**
| Parameter | Type | Required | Description |
|---|---|---|---|
| `slug` | string/integer | Yes | Article slug or numeric ID |

**Description:**
Memuat detail lengkap artikel beserta artikel terkait (`related_articles`) dalam kategori yang sama. Secara otomatis menambah jumlah pembaca (`views_count`).

**Example Request:**
`GET /api/v1/articles/5-sepatu-lari-daily-trainer-terbaik-2026`

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Detail artikel berhasil dimuat",
  "data": {
    "article": {
      "id": 12,
      "title": "5 Sepatu Lari Daily Trainer Terbaik 2026",
      "slug": "5-sepatu-lari-daily-trainer-terbaik-2026",
      "excerpt": "Rekomendasi sepatu lari harian terbaik...",
      "content": "<p>Berikut adalah ulasan lengkap sepatu lari harian...</p>",
      "featured_image": "storage/articles/shoes.jpg",
      "views_count": 4521,
      "published_at": "2026-07-25 14:00:00",
      "category": {
        "id": 4,
        "name": "Gear",
        "slug": "gear"
      }
    },
    "related_articles": [
      {
        "id": 14,
        "title": "Review Carbon Plated Race Shoes 2026",
        "slug": "review-carbon-plated-race-shoes-2026",
        "category": {
          "id": 4,
          "name": "Gear",
          "slug": "gear"
        }
      }
    ]
  }
}
```
