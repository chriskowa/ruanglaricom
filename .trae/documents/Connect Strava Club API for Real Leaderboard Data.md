Yes, we can connect to the Strava API to fetch club data.

To replace the mock data with real data from the "Ruang Lari" Strava Club, we need to implement a system that fetches club activities and calculates the leaderboard.

Here is the implementation plan:

# 1. Database Update
We need to store Strava access tokens in the database so the server can fetch data without requiring every visitor to log in.
- **Action**: Create a migration to add `strava_id`, `strava_access_token`, `strava_refresh_token`, and `strava_expires_at` columns to the `users` table.

# 2. Authentication Logic Update
Update the `stravaCallback` in `CalendarController` to:
- Save the received tokens to the currently authenticated user's record.
- This allows an "Admin" or "System User" to log in once, and their token will be used to fetch club data for the public homepage.

# 3. Strava Club Service
Create a new service `App\Services\StravaClubService` that handles:
- **Token Management**: Automatically refreshing expired tokens using the refresh token.
- **Data Fetching**: Calling `GET /clubs/1859982/activities`.
- **Leaderboard Calculation**: Aggregating activities from the current week to find:
    - Fastest Pace (Speed Demon)
    - Longest Distance (Distance Monster)
    - Highest Elevation (Mountain Goat)
- **Caching**: Caching the results (e.g., for 1 hour) to avoid hitting Strava API rate limits.

# 4. Homepage Integration
Update `App\Http\Controllers\HomeController` to:
- Call the `StravaClubService` to get the real leaderboard data.
- Pass this data to the `home.index` view.
- Update the blade view to display real names, stats, and photos instead of mock data.

**Note**: To make this work, after implementation, you (or an admin) will need to visit the Strava Connect link (`/runcalendar/strava/connect`) *once* to generate the initial token.
