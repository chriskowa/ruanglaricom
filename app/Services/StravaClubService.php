<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class StravaClubService
{
    private $clubId = '1859982'; // Ruang Lari Club ID

    public function getLeaderboard()
    {
        // Cache for 30 minutes to avoid hitting rate limits
        return Cache::remember('strava_club_leaderboard', 1800, function () {
            return $this->fetchClubActivities();
        });
    }

    private function fetchClubActivities()
    {
        // 1. Get a valid token from a "System User" (Admin)
        // We'll use the first user who has a Strava token connected
        $admin = User::whereNotNull('strava_access_token')->first();

        if (!$admin) {
            return [
                'fastest' => null,
                'distance' => null,
                'elevation' => null
            ];
        }

        // 2. Ensure token is valid
        $accessToken = $this->getValidToken($admin);
        if (!$accessToken) {
            return [
                'fastest' => null,
                'distance' => null,
                'elevation' => null
            ];
        }

        // 3. Fetch Club Activities
        $response = Http::withToken($accessToken)
            ->withoutVerifying()
            ->get("https://www.strava.com/api/v3/clubs/{$this->clubId}/activities", [
                'per_page' => 200, // Get enough data
            ]);

        if ($response->failed()) {
            return [
                'fastest' => null,
                'distance' => null,
                'elevation' => null
            ];
        }

        $activities = $response->json();
        return $this->processLeaderboard($activities);
    }

    private function getValidToken($user)
    {
        // Check if token is expired
        if ($user->strava_expires_at && now()->greaterThan($user->strava_expires_at)) {
            // Refresh Token
            $response = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                'client_id' => env('STRAVA_CLIENT_ID'),
                'client_secret' => env('STRAVA_CLIENT_SECRET'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->strava_refresh_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $user->update([
                    'strava_access_token' => $data['access_token'],
                    'strava_refresh_token' => $data['refresh_token'],
                    'strava_expires_at' => now()->addSeconds($data['expires_in']),
                ]);
                return $data['access_token'];
            }
            return null; // Failed to refresh
        }

        return $user->strava_access_token;
    }

    private function processLeaderboard($activities)
    {
        if (empty($activities)) {
            return [
                'fastest' => null,
                'distance' => null,
                'elevation' => null
            ];
        }

        $athletes = [];
        $startOfWeek = Carbon::now()->startOfWeek()->subWeek(); // Get Last Week (Monday)
        $endOfWeek = Carbon::now()->startOfWeek(); // Until This Week Monday (Sunday night)
        
        // Alternatively, for "This Week":
        // $startOfWeek = Carbon::now()->startOfWeek();

        // Aggregate data by athlete
        foreach ($activities as $activity) {
            // Only count Run activities for now
            if ($activity['type'] !== 'Run') continue;

            // Filter by Date (Optional: Remove comment to enforce strictly current/last week)
            // Strava activities usually come sorted by date desc.
            // $activityDate = Carbon::parse($activity['start_date_local']);
            // if ($activityDate->lt($startOfWeek)) continue; 

            $athleteName = $activity['athlete']['firstname'] . ' ' . $activity['athlete']['lastname'];
            
            if (!isset($athletes[$athleteName])) {
                $athletes[$athleteName] = [
                    'name' => $athleteName,
                    'distance' => 0,
                    'elevation' => 0,
                    'max_speed' => 0,
                    'fastest_pace' => 9999, // Minutes per km
                    'avatar' => 'https://i.pravatar.cc/150?u=' . md5($athleteName) // Fallback avatar if not available
                ];
            }

            // Sum Distance (meters to km)
            $athletes[$athleteName]['distance'] += ($activity['distance'] / 1000);
            
            // Sum Elevation (meters)
            $athletes[$athleteName]['elevation'] += $activity['total_elevation_gain'];
            
            // Track Fastest Pace (min/km)
            // Strava gives speed in m/s. Pace = 1000 / (speed * 60)
            if ($activity['average_speed'] > 0) {
                $pace = (1000 / $activity['average_speed']) / 60;
                if ($pace < $athletes[$athleteName]['fastest_pace']) {
                    $athletes[$athleteName]['fastest_pace'] = $pace;
                }
            }
        }

        // Sort for each category
        $distanceLeader = collect($athletes)->sortByDesc('distance')->first();
        $elevationLeader = collect($athletes)->sortByDesc('elevation')->first();
        $paceLeader = collect($athletes)->sortBy('fastest_pace')->first();

        // Format for display
        return [
            'distance' => $distanceLeader ? [
                'name' => $distanceLeader['name'],
                'value' => number_format($distanceLeader['distance'], 1),
                'unit' => 'km',
                'avatar' => $distanceLeader['avatar']
            ] : null,
            
            'elevation' => $elevationLeader ? [
                'name' => $elevationLeader['name'],
                'value' => number_format($elevationLeader['elevation'], 0),
                'unit' => 'm',
                'avatar' => $elevationLeader['avatar']
            ] : null,
            
            'fastest' => $paceLeader ? [
                'name' => $paceLeader['name'],
                'value' => $this->formatPace($paceLeader['fastest_pace']),
                'unit' => '/km',
                'avatar' => $paceLeader['avatar']
            ] : null,
        ];
    }

    private function formatPace($decimalMinutes)
    {
        $minutes = floor($decimalMinutes);
        $seconds = round(($decimalMinutes - $minutes) * 60);
        return sprintf('%d:%02d', $minutes, $seconds);
    }
}
