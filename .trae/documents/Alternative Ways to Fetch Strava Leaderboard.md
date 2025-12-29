The reason "Belum ada data leaderboard minggu ini" appears is because **no user has connected their Strava account to the application yet**, so the system cannot fetch data from the API.

My check confirmed that `0` users have a `strava_access_token` in the database.

# Solution Plan

1.  **Connect Account (Action Required by You)**
    You need to visit the connection URL I set up: `http://localhost:8000/runcalendar/strava/connect` (or your live domain) and authorize the app. This will save the token to the database.

2.  **Refine Leaderboard Logic (My Task)**
    The current logic aggregates *all* fetched activities (last 200). To match the "Last Week's Leaders" or "This Week's Leaderboard" concept more accurately (like your screenshot), I will update `StravaClubService.php` to:
    - Filter activities by `start_date_local` to only include the current week (Monday-Sunday).
    - Handle the case where the API returns 0 activities gracefully.

3.  **Update View**
    Add a temporary "Connect Button" visible only to Admins on the homepage leaderboard section, so you don't have to type the URL manually next time.

I will implement the logic refinement and the admin button now. You will then need to click that button to populate the data.
