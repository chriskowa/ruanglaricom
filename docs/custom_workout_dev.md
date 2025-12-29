# Custom Workout Feature - Developer Documentation

## Architecture

### Database Schema
1. **master_workouts** table:
   - `coach_id` (FK to users): Nullable. If null, it's a system workout. If set, it belongs to that coach.
   - `is_public` (boolean): Determines visibility for non-creators.
   - `intensity`: Derived from `type` automatically.

2. **workout_visibility_logs** table:
   - Tracks changes to `is_public` status for audit purposes.
   - Fields: `master_workout_id`, `user_id`, `old_visibility`, `new_visibility`.

### Models
- **MasterWorkout**:
  - Scope `visibleFor(User $user)`: Handles complex logic to fetch system workouts, public workouts, and user's own workouts.
  - Relationship `coach()`: BelongsTo User.

### Access Control
- **Backend**:
  - `CustomWorkoutController` enforces ownership checks for Update/Delete.
  - `visibleFor` scope filters List queries.
- **Frontend**:
  - UI hides Edit/Delete buttons for workouts not owned by the current user.

### Testing
- **Feature Tests**: `tests/Feature/CustomWorkoutTest.php`
  - Covers CRUD operations.
  - Verifies Access Control Logic (ACL).
  - Checks Audit Logging.
  - **Note**: Ensure `APP_URL` is set correctly in `phpunit.xml` or `.env` for route generation.

## Usage Examples

### Fetching Available Workouts for a Coach
```php
$coach = auth()->user();
$workouts = MasterWorkout::visibleFor($coach)->get();
```

### Creating a Private Workout
```php
MasterWorkout::create([
    'title' => 'Secret Technique',
    'coach_id' => $coach->id,
    'is_public' => false,
    ...
]);
```

### Toggling Visibility (with Audit Log)
```php
$workout->update(['is_public' => true]);
// Audit log is created automatically in Controller
```
