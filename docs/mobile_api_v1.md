# Ruang Lari Mobile REST API V1 Documentation

Dokumentasi resmi API V1 Ruang Lari untuk integrasi aplikasi mobile (iOS/Android) dan layanan pihak ketiga.

---

## 🔑 Authentication & Headers
Semua endpoint terproteksi membutuhkan header otentikasi Sanctum Bearer Token:

```http
Authorization: Bearer {your_sanctum_token}
Accept: application/json
Content-Type: application/json
```

---

## 📑 Daftar Modul API

- [1. Authentication Module](#1-authentication-module)
- [2. Profile & Pace Settings](#2-profile--pace-settings)
- [3. Event Calendar](#3-event-calendar)
- [4. Event Bookmark](#4-event-bookmark)
- [5. Runner Calendar & Training Schedule](#5-runner-calendar--training-schedule)
- [6. Run Connect (Cari Teman Lari)](#6-run-connect-cari-teman-lari)
- [7. Chat Messages & Participant Approvals](#7-chat-messages--participant-approvals)
- [8. Push Notifications & Device Token](#8-push-notifications--device-token)
- [9. Strava Sync Integration](#9-strava-sync-integration)
- [10. Running Communities](#10-running-communities)
- [11. Marketplace Module](#11-marketplace-module)
- [12. Biomechanics AI Form Analysis](#12-biomechanics-ai-form-analysis)
- [13. Training Program & Custom Workouts](#13-training-program--custom-workouts)
- [14. Event Time Prediction](#14-event-time-prediction)

---

## 1. Authentication Module

### 1.1 Register
- **Endpoint:** `POST /api/v1/auth/register`
- **Auth Required:** No
- **Body Request:**
  ```json
  {
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "password": "Password123!",
    "password_confirmation": "Password123!",
    "role": "runner"
  }
  ```
- **Response (201 Created):**
  ```json
  {
    "success": true,
    "message": "Registrasi berhasil",
    "data": {
      "user": { "id": 5, "name": "Budi Santoso", "email": "budi@example.com", "role": "runner" },
      "token": "1|sanctum_token_string..."
    }
  }
  ```

### 1.2 Login
- **Endpoint:** `POST /api/v1/auth/login`
- **Auth Required:** No
- **Body Request:**
  ```json
  {
    "email": "budi@example.com",
    "password": "Password123!"
  }
  ```

### 1.3 Social Login (Google / Apple)
- **Endpoint:** `POST /api/v1/auth/social-login`
- **Auth Required:** No
- **Body Request:**
  ```json
  {
    "provider": "google",
    "id_token": "google_oauth_id_token_string",
    "name": "Budi Santoso",
    "email": "budi@example.com"
  }
  ```

### 1.4 Forgot Password
- **Endpoint:** `POST /api/v1/auth/forgot-password`
- **Auth Required:** No
- **Body Request:** `{"email": "budi@example.com"}`

### 1.5 Logout
- **Endpoint:** `POST /api/v1/auth/logout`
- **Auth Required:** Yes (Bearer Token)

---

## 2. Profile & Pace Settings

### 2.1 Get Current Profile (`/me`)
- **Endpoint:** `GET /api/v1/me`
- **Auth Required:** Yes
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": {
      "id": 5,
      "name": "Budi Santoso",
      "username": "budi_runner",
      "email": "budi@example.com",
      "avatar_url": "https://ruanglari.com/storage/avatars/budi.jpg",
      "gender": "male",
      "city": "Jakarta Selatan",
      "vdot": 42.5,
      "easy_pace": "06:15",
      "threshold_pace": "05:10",
      "interval_pace": "04:30",
      "repetition_pace": "04:05"
    }
  }
  ```

### 2.2 Update Profile Info
- **Endpoint:** `PUT /api/v1/me/profile`
- **Auth Required:** Yes
- **Body Request:**
  ```json
  {
    "name": "Budi Santoso",
    "username": "budi_runner",
    "gender": "male",
    "city_id": 12,
    "bio": "Marathon enthusiast"
  }
  ```

### 2.3 Update Pace Settings
- **Endpoint:** `PUT /api/v1/me/paces`
- **Auth Required:** Yes
- **Body Request:**
  ```json
  {
    "easy_pace": "06:15",
    "threshold_pace": "05:10",
    "interval_pace": "04:30",
    "repetition_pace": "04:05",
    "vdot": 42.5
  }
  ```

---

## 3. Event Calendar

### 3.1 List Race Events
- **Endpoint:** `GET /api/v1/events`
- **Auth Required:** No
- **Query Params:** `month` (e.g. `2026-08`), `city_id`, `category_id`, `search`
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 857,
        "name": "Jakarta Marathon 2026",
        "slug": "jakarta-marathon-2026",
        "start_at": "2026-10-25 05:00:00",
        "location_name": "GBK Senayan",
        "city": { "id": 1, "name": "Jakarta" },
        "banner_url": "https://ruanglari.com/storage/events/banner.jpg"
      }
    ]
  }
  ```

### 3.2 Event Detail
- **Endpoint:** `GET /api/v1/events/{slug}`
- **Auth Required:** No

---

## 4. Event Bookmark

### 4.1 Toggle Bookmark Event
- **Endpoint:** `POST /api/v1/events/{id}/bookmark`
- **Auth Required:** Yes
- **Response (200 OK):**
  ```json
  {
    "success": true,
    "bookmarked": true,
    "message": "Event berhasil disimpan ke bookmark"
  }
  ```

### 4.2 List Saved / Bookmarked Events
- **Endpoint:** `GET /api/v1/me/saved-events`
- **Auth Required:** Yes

---

## 5. Runner Calendar & Training Schedule

### 5.1 Monthly Calendar Sessions
- **Endpoint:** `GET /api/v1/calendar/month`
- **Auth Required:** Yes
- **Query Params:** `month` (format: `YYYY-MM`)

### 5.2 Daily Schedule Detail
- **Endpoint:** `GET /api/v1/calendar/day/{date}`
- **Auth Required:** Yes (date format: `YYYY-MM-DD`)

### 5.3 Complete Session
- **Endpoint:** `POST /api/v1/calendar/sessions/{id}/complete`
- **Auth Required:** Yes
- **Body Request:**
  ```json
  {
    "actual_distance_km": 5.2,
    "actual_duration": "00:31:15",
    "perceived_exertion": 7,
    "notes": "Latihan pagi lancar"
  }
  ```

### 5.4 Reschedule Session
- **Endpoint:** `POST /api/v1/calendar/sessions/{id}/reschedule`
- **Auth Required:** Yes
- **Body Request:** `{"new_date": "2026-08-02"}`

### 5.5 Store Custom Workout
- **Endpoint:** `POST /api/v1/calendar/custom-workouts`
- **Auth Required:** Yes

---

## 6. Run Connect (Cari Teman Lari)

### 6.1 List Threads / Latbar
- **Endpoint:** `GET /api/v1/run-connect/threads`
- **Auth Required:** No
- **Query Params:** `city_id`, `pace_category`, `date`, `search`

### 6.2 Create Latbar Thread
- **Endpoint:** `POST /api/v1/run-connect/threads`
- **Auth Required:** Yes
- **Body Request:**
  ```json
  {
    "title": "Easy Run Pagi Monas",
    "location": "Monas Jakarta",
    "scheduled_at": "2026-08-01 06:00:00",
    "target_pace": "06:00",
    "distance_km": 7,
    "max_participants": 5,
    "description": "Lari santai 7km mengelilingi Monas"
  }
  ```

### 6.3 Generate AI Description for Thread
- **Endpoint:** `POST /api/v1/run-connect/generate-description`
- **Auth Required:** Yes

### 6.4 Join Latbar Thread
- **Endpoint:** `POST /api/v1/run-connect/threads/{id}/join`
- **Auth Required:** Yes

### 6.5 Leave Latbar Thread
- **Endpoint:** `POST /api/v1/run-connect/threads/{id}/leave`
- **Auth Required:** Yes

### 6.6 Random Buddy Match
- **Endpoint:** `GET /api/v1/run-connect/random-match`
- **Auth Required:** Yes

### 6.7 History Latbar
- **Endpoint:** `GET /api/v1/run-connect/history`
- **Auth Required:** Yes

---

## 7. Chat Messages & Participant Approvals

### 7.1 List Thread Chat Messages
- **Endpoint:** `GET /api/v1/run-connect/threads/{id}/messages`
- **Auth Required:** Yes

### 7.2 Send Thread Chat Message
- **Endpoint:** `POST /api/v1/run-connect/threads/{id}/messages`
- **Auth Required:** Yes
- **Body Request:** `{"message": "Halo teman-teman, kumpul dekat gerbang utama ya!"}`

### 7.3 Approve Participant (Host Only)
- **Endpoint:** `POST /api/v1/run-connect/threads/{id}/approve/{participantId}`
- **Auth Required:** Yes

### 7.4 Reject Participant (Host Only)
- **Endpoint:** `POST /api/v1/run-connect/threads/{id}/reject/{participantId}`
- **Auth Required:** Yes

### 7.5 Pending Approvals List
- **Endpoint:** `GET /api/v1/run-connect/approvals`
- **Auth Required:** Yes

### 7.6 Rate Running Buddy
- **Endpoint:** `POST /api/v1/run-connect/threads/{id}/rate`
- **Auth Required:** Yes
- **Body Request:**
  ```json
  {
    "target_user_id": 12,
    "rating": 5,
    "review": "Sangat tepat waktu dan pace stabil!"
  }
  ```

---

## 8. Push Notifications & Device Token

### 8.1 Register FCM/APNS Device Token
- **Endpoint:** `POST /api/v1/device-token`
- **Auth Required:** Yes
- **Body Request:**
  ```json
  {
    "device_token": "fcm_token_xyz123...",
    "device_type": "android"
  }
  ```

### 8.2 List Notifications
- **Endpoint:** `GET /api/v1/notifications`
- **Auth Required:** Yes

### 8.3 Mark Notification Read
- **Endpoint:** `POST /api/v1/notifications/{id}/read`
- **Auth Required:** Yes

### 8.4 Mark All Notifications Read
- **Endpoint:** `POST /api/v1/notifications/read-all`
- **Auth Required:** Yes

---

## 9. Strava Sync Integration

### 9.1 Check Strava Connection Status
- **Endpoint:** `GET /api/v1/strava/status`
- **Auth Required:** Yes

### 9.2 Trigger Manual Sync Strava Activities
- **Endpoint:** `POST /api/v1/strava/sync`
- **Auth Required:** Yes

### 9.3 Disconnect Strava Account
- **Endpoint:** `POST /api/v1/strava/disconnect`
- **Auth Required:** Yes

---

## 10. Running Communities

### 10.1 Browse Communities
- **Endpoint:** `GET /api/v1/communities`
- **Auth Required:** No

### 10.2 Community Profile Detail
- **Endpoint:** `GET /api/v1/communities/{slug}`
- **Auth Required:** No

---

## 11. Marketplace Module

### 11.1 Products List
- **Endpoint:** `GET /api/v1/marketplace/products`
- **Auth Required:** No

### 11.2 Product Detail
- **Endpoint:** `GET /api/v1/marketplace/products/{slug}`
- **Auth Required:** No

### 11.3 Marketplace Categories
- **Endpoint:** `GET /api/v1/marketplace/categories`
- **Auth Required:** No

---

## 12. Biomechanics AI Form Analysis

### 12.1 Upload Video / Image for Analysis
- **Endpoint:** `POST /api/v1/analysis/upload`
- **Auth Required:** Yes (Multipart Form Data)
- **Body (Form-Data):** `video` (file), `running_type` (indoor/outdoor)

### 12.2 My Form Analysis Reports
- **Endpoint:** `GET /api/v1/analysis/my-reports`
- **Auth Required:** Yes

### 12.3 Form Analysis Report Detail
- **Endpoint:** `GET /api/v1/analysis/reports/{id}`
- **Auth Required:** Yes

---

## 13. Training Program & Custom Workouts

### 13.1 Coach Custom Workouts
- **Endpoint:** `GET /coach/custom-workouts`
- **Auth Required:** Yes (Role: Coach)

### 13.2 Create Coach Custom Workout
- **Endpoint:** `POST /coach/custom-workouts`
- **Auth Required:** Yes (Role: Coach)

---

## 14. Event Time Prediction

### 14.1 Predict Race Finish Time & Pacing Strategy
- **Endpoint:** `POST /event/{slug}/prediction/predict`
- **Auth Required:** No
- **Body Request:**
  ```json
  {
    "category_id": 10,
    "weather": "cerah",
    "pb_h": 0,
    "pb_m": 24,
    "pb_s": 30,
    "pb_date": "2026-06-15"
  }
  ```
- **Response (200 OK):**
  ```json
  {
    "ok": true,
    "event": { "id": 1, "name": "Jakarta Marathon", "slug": "jakarta-marathon-2026" },
    "category": { "id": 10, "name": "5K", "distance_km": 5 },
    "result": {
      "vdot": 42.5,
      "predicted_time_seconds": 1470,
      "predicted_time_formatted": "00:24:30",
      "avg_pace": "04:54",
      "pacing_strategy": []
    }
  }
  ```
