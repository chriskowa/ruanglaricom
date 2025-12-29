# Custom Workout API Documentation

## Overview
This API allows coaches to manage custom workouts. Custom workouts can be private (only visible to the creator) or public (visible to all coaches).

## Endpoints

### 1. Create Custom Workout
**Endpoint:** `POST /coach/custom-workouts`
**Auth Required:** Yes (Role: Coach)

**Request Body:**
| Field | Type | Required | Description |
|---|---|---|---|
| `title` | string | Yes | Name of the workout |
| `type` | string | Yes | One of: `easy_run`, `long_run`, `tempo`, `interval`, `strength`, `rest` |
| `description` | string | No | Detailed description |
| `default_distance` | number | No | Default distance in km |
| `default_duration` | string | No | Default duration (e.g., "00:30:00") |
| `is_public` | boolean | No | Visibility status (default: false) |

**Example Request:**
```json
{
    "title": "Morning Interval",
    "type": "interval",
    "description": "5x1km intervals",
    "default_distance": 5,
    "is_public": false
}
```

**Success Response (200 OK):**
```json
{
    "message": "Custom workout created successfully",
    "workout": {
        "id": 15,
        "title": "Morning Interval",
        "type": "interval",
        "coach_id": 5,
        "is_public": false,
        ...
    }
}
```

### 2. Update Custom Workout
**Endpoint:** `PUT /coach/custom-workouts/{id}`
**Auth Required:** Yes (Role: Coach, Must be Creator)

**Request Body:**
Same as Create.

**Success Response (200 OK):**
```json
{
    "message": "Custom workout updated successfully",
    "workout": { ... }
}
```

### 3. Delete Custom Workout
**Endpoint:** `DELETE /coach/custom-workouts/{id}`
**Auth Required:** Yes (Role: Coach, Must be Creator)

**Success Response (200 OK):**
```json
{
    "message": "Custom workout deleted successfully"
}
```

### 4. List Custom Workouts
**Endpoint:** `GET /coach/custom-workouts`
**Auth Required:** Yes (Role: Coach)

**Query Parameters:**
- `type` (optional): Filter by workout type

**Response:**
Returns list of workouts visible to the coach (own workouts + public workouts + system workouts).
