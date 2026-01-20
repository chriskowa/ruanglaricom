<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class StravaClubService
{
    private $clubId = '1859982'; // Ruang Lari Club ID

    public function getLeaderboard()
    {
        // Cache for 30 minutes to avoid hitting rate limits
        return Cache::remember('strava_club_leaderboard', 1800, function () {
            $token = $this->getSystemToken();
            if (! empty($token)) {
                $activities = $this->fetchClubActivitiesByToken($token);
                return $this->processLeaderboard($activities);
            }

            return $this->fetchClubActivities();
        });
    }

    private function getSystemToken()
    {
        // 1. Try DB Config
        try {
            $config = \App\Models\Admin\StravaConfig::first();
            if ($config) {
                // Check expiry
                if ($config->refresh_token && (!$config->access_token || ($config->expires_at && now()->greaterThan($config->expires_at)))) {
                    // Refresh it
                    $response = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                        'client_id' => $config->client_id,
                        'client_secret' => $config->client_secret,
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $config->refresh_token,
                    ]);
    
                    if ($response->successful()) {
                        $data = $response->json();
                        $config->update([
                            'access_token' => $data['access_token'],
                            'refresh_token' => $data['refresh_token'],
                            'expires_at' => now()->addSeconds($data['expires_in']),
                        ]);
                        return $data['access_token'];
                    }
                }
                if ($config->access_token) {
                    return $config->access_token;
                }
            }
        } catch (\Throwable $e) {
            // Fallback to env if DB fails or table missing
        }

        // 2. Fallback to ENV
        return env('STRAVA_ACCESS_TOKEN');
    }

    private function getClubId()
    {
        try {
            $config = \App\Models\Admin\StravaConfig::first();
            return $config->club_id ?? env('STRAVA_CLUB_ID', '1859982');
        } catch (\Throwable $e) {
            return env('STRAVA_CLUB_ID', '1859982');
        }
    }

    private function fetchClubActivities()
    {
        // 1. Get a valid token from a "System User" (Admin)
        // We'll use the first user who has a Strava token connected
        $admin = User::whereNotNull('strava_access_token')->first();

        if (! $admin) {
            return [
                'fastest' => null,
                'distance' => null,
                'elevation' => null,
            ];
        }

        // 2. Ensure token is valid
        $accessToken = $this->getValidToken($admin);
        if (! $accessToken) {
            return [
                'fastest' => null,
                'distance' => null,
                'elevation' => null,
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
                'elevation' => null,
            ];
        }

        $activities = $response->json();

        return $this->processLeaderboard($activities);
    }

    public function fetchClubActivitiesByEnv(): array
    {
        $clubId = env('STRAVA_CLUB_ID', $this->clubId);
        $token = env('STRAVA_ACCESS_TOKEN');
        if (! $token) {
            return [];
        }
        $response = Http::withToken($token)
            ->withoutVerifying()
            ->get("https://www.strava.com/api/v3/clubs/{$clubId}/activities", ['per_page' => 200]);
        if ($response->failed()) {
            return [];
        }

        return $response->json() ?: [];
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
                'elevation' => null,
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
            if ($activity['type'] !== 'Run') {
                continue;
            }

            // Filter by Date (Optional: Remove comment to enforce strictly current/last week)
            // Strava activities usually come sorted by date desc.
            // $activityDate = Carbon::parse($activity['start_date_local']);
            // if ($activityDate->lt($startOfWeek)) continue;

            $athleteName = $activity['athlete']['firstname'].' '.$activity['athlete']['lastname'];

            if (! isset($athletes[$athleteName])) {
                $athletes[$athleteName] = [
                    'name' => $athleteName,
                    'distance' => 0,
                    'elevation' => 0,
                    'max_speed' => 0,
                    'fastest_pace' => 9999, // Minutes per km
                    'avatar' => 'https://i.pravatar.cc/150?u='.md5($athleteName), // Fallback avatar if not available
                ];
            }

            // Sum Distance (meters to km)
            $distanceMeters = (float) ($activity['distance'] ?? 0);
            $athletes[$athleteName]['distance'] += ($distanceMeters > 0 ? $distanceMeters / 1000 : 0);

            // Sum Elevation (meters)
            $elevationGain = (float) ($activity['total_elevation_gain'] ?? 0);
            $athletes[$athleteName]['elevation'] += $elevationGain;

            // Track Fastest Pace (min/km)
            // Strava gives speed in m/s. Pace = 1000 / (speed * 60)
            $avgSpeed = isset($activity['average_speed']) ? (float) $activity['average_speed'] : null;
            $pace = null;
            if ($avgSpeed && $avgSpeed > 0) {
                $pace = (1000 / $avgSpeed) / 60;
            } elseif ($distanceMeters > 0) {
                $movingTime = (int) ($activity['moving_time'] ?? 0);
                if ($movingTime > 0) {
                    // Pace (min/km) = moving_time (sec) / (distance_km) / 60
                    $pace = ($movingTime / ($distanceMeters / 1000)) / 60;
                }
            }
            if ($pace !== null && $pace < $athletes[$athleteName]['fastest_pace']) {
                $athletes[$athleteName]['fastest_pace'] = $pace;
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
                'avatar' => $distanceLeader['avatar'],
            ] : null,

            'elevation' => $elevationLeader ? [
                'name' => $elevationLeader['name'],
                'value' => number_format($elevationLeader['elevation'], 0),
                'unit' => 'm',
                'avatar' => $elevationLeader['avatar'],
            ] : null,

            'fastest' => $paceLeader ? [
                'name' => $paceLeader['name'],
                'value' => $this->formatPace($paceLeader['fastest_pace']),
                'unit' => '/km',
                'avatar' => $paceLeader['avatar'],
            ] : null,
        ];
    }

    private function formatPace($decimalMinutes)
    {
        $minutes = floor($decimalMinutes);
        $seconds = round(($decimalMinutes - $minutes) * 60);

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function getClubMembers(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('strava_club_members', 1800, function () {
            // Prefer ENV access token if provided (useful for server-side/system token)
            $envToken = env('STRAVA_ACCESS_TOKEN');
            if (! empty($envToken)) {
                return $this->fetchClubMembersByEnv();
            }

            return $this->fetchClubMembers();
        });
    }

    private function fetchClubMembers(): array
    {
        $admin = \App\Models\User::whereNotNull('strava_access_token')->first();

        // If no admin or token found, use Mock Data in local/dev environment
        if (! $admin) {
            return $this->getMockMembers();
        }

        $accessToken = $this->getValidToken($admin);
        if (! $accessToken) {
            return $this->getMockMembers();
        }

        $response = \Illuminate\Support\Facades\Http::withToken($accessToken)
            ->withoutVerifying()
            ->get("https://www.strava.com/api/v3/clubs/{$this->clubId}/members", ['per_page' => 200]);

        if ($response->failed()) {
            return $this->getMockMembers();
        }

        $members = $response->json() ?: [];

        return array_map(function ($m) {
            $name = trim(($m['firstname'] ?? '').' '.($m['lastname'] ?? ''));

            return [
                'id' => $m['id'] ?? null,
                'name' => $name !== '' ? $name : ($m['username'] ?? 'Unknown'),
                'avatar' => $m['profile'] ?? ($m['profile_medium'] ?? 'https://ui-avatars.com/api/?name='.urlencode($name)),
                'gender' => strtoupper(($m['sex'] ?? '')) === 'F' ? 'F' : 'M',
                'city' => $m['city'] ?? null,
                'state' => $m['state'] ?? null,
                'country' => $m['country'] ?? null,
            ];
        }, $members);
    }

    private function fetchClubMembersByToken($token): array
    {
        $clubId = $this->getClubId();
        
        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->withoutVerifying()
            ->get("https://www.strava.com/api/v3/clubs/{$clubId}/members", ['per_page' => 200]);
        if ($response->failed()) {
            return $this->getMockMembers();
        }
        $members = $response->json() ?: [];

        return array_map(function ($m) {
            $name = trim(($m['firstname'] ?? '').' '.($m['lastname'] ?? ''));

            return [
                'id' => $m['id'] ?? null,
                'name' => $name !== '' ? $name : ($m['username'] ?? 'Unknown'),
                'avatar' => $m['profile'] ?? ($m['profile_medium'] ?? 'https://ui-avatars.com/api/?name='.urlencode($name)),
                'gender' => strtoupper(($m['sex'] ?? '')) === 'F' ? 'F' : 'M',
                'city' => $m['city'] ?? null,
                'state' => $m['state'] ?? null,
                'country' => $m['country'] ?? null,
            ];
        }, $members);
    }

    private function getMockMembers(): array
    {
        // Return mock data for development/testing when no Strava connection exists
        $mockNames = [
            ['Sarah Connor', 'F', 'Los Angeles', 'CA'],
            ['John Wick', 'M', 'New York', 'NY'],
            ['Ellen Ripley', 'F', 'Nostromo', 'Space'],
            ['Neo Anderson', 'M', 'Mega City', 'Matrix'],
            ['Trinity', 'F', 'Zion', 'Matrix'],
            ['Forrest Gump', 'M', 'Greenbow', 'Alabama'],
            ['Lara Croft', 'F', 'London', 'UK'],
            ['Bruce Wayne', 'M', 'Gotham', 'NJ'],
            ['Diana Prince', 'F', 'Themyscira', 'Greece'],
            ['Tony Stark', 'M', 'Malibu', 'CA'],
        ];

        $members = [];
        foreach ($mockNames as $idx => $data) {
            $members[] = [
                'id' => 1000 + $idx,
                'name' => $data[0],
                'avatar' => 'https://ui-avatars.com/api/?name='.urlencode($data[0]).'&background=random',
                'gender' => $data[1],
                'city' => $data[2],
                'state' => $data[3],
                'country' => 'USA',
            ];
        }

        return $members;
    }
}
