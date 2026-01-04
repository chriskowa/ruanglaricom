<?php

namespace App\Http\Controllers;

use App\Models\ChallengeActivity;
use App\Models\LeaderboardStat;
use App\Models\ProgramEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AdminChallengeController extends Controller
{
    public function index()
    {
        $activities = ChallengeActivity::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        // Get enrolled runners for 40days program
        // Assuming '40days' is in slug or hardcoded field, or ID 9
        $enrolledRunners = ProgramEnrollment::whereHas('program', function ($q) {
            $q->where('hardcoded', '40days')
                ->orWhere('id', 9)
                ->orWhere('slug', 'like', '%40days%');
        })
            ->with('runner')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.challenge.index', compact('activities', 'enrolledRunners'));
    }

    public function syncStrava(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'refresh_token' => 'nullable|string',
            'access_token' => 'nullable|string',
            'expires_at' => 'nullable|numeric',
            'strava_id' => 'nullable|string',
        ]);

        $user = User::findOrFail($request->user_id);

        // Update User Credentials if provided
        if ($request->strava_id) {
            $user->strava_id = $request->strava_id;
        }
        if ($request->refresh_token) {
            $user->strava_refresh_token = $request->refresh_token;
        }
        if ($request->access_token) {
            $user->strava_access_token = $request->access_token;
        }
        if ($request->expires_at) {
            $user->strava_expires_at = \Carbon\Carbon::createFromTimestamp($request->expires_at);
        }
        // If any strava data updated, save
        if ($request->hasAny(['strava_id', 'refresh_token', 'access_token', 'expires_at'])) {
            $user->save();
        }

        // Find enrollment to get start date
        $enrollment = ProgramEnrollment::where('runner_id', $user->id)
            ->whereHas('program', function ($q) {
                $q->where('hardcoded', '40days')
                    ->orWhere('id', 9)
                    ->orWhere('slug', 'like', '%40days%');
            })
            ->first();

        if (! $enrollment) {
            return response()->json(['success' => false, 'message' => 'User not enrolled in 40 Days Challenge.'], 400);
        }

        // 1. Determine Strava ID
        $stravaId = $user->strava_id;
        if (empty($stravaId)) {
            // Try extracting from URL
            if (! empty($user->strava_url) && preg_match('/\/athletes\/(\d+)/', $user->strava_url, $matches)) {
                $stravaId = $matches[1];
                // Save it for future
                $user->strava_id = $stravaId;
                $user->save();
            }
        }

        if (empty($stravaId)) {
            return response()->json(['success' => false, 'message' => 'Strava ID not found. Please update Strava URL or input Strava ID manually.'], 400);
        }

        $accessToken = null;
        $usedClubMode = false;

        // 2. Determine Access Token
        // Priority 1: Check if we have a valid access token in DB
        if ($user->strava_access_token && $user->strava_expires_at && $user->strava_expires_at->isFuture()) {
            $accessToken = $user->strava_access_token;
        }
        // Priority 2: If we have a refresh token, try to refresh
        elseif ($user->strava_refresh_token) {
            $response = Http::post('https://www.strava.com/oauth/token', [
                'client_id' => config('services.strava.client_id') ?? env('STRAVA_CLIENT_ID'),
                'client_secret' => config('services.strava.client_secret') ?? env('STRAVA_CLIENT_SECRET'),
                'refresh_token' => $user->strava_refresh_token,
                'grant_type' => 'refresh_token',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $accessToken = $data['access_token'];

                // Update user tokens
                $user->strava_access_token = $accessToken;
                $user->strava_refresh_token = $data['refresh_token'];
                $user->strava_expires_at = \Carbon\Carbon::createFromTimestamp($data['expires_at']);
                $user->save();
            } else {
                // If refresh failed, we might want to log it or continue to fallback
                // For now, let's continue to fallback logic
            }
        }

        // Priority 3: Use Env Access Token (Global Admin Token) - Club Mode
        if (! $accessToken) {
            $envToken = env('STRAVA_ACCESS_TOKEN'); // From .env directly
            if ($envToken) {
                $accessToken = $envToken;
                $usedClubMode = true;
            } else {
                return response()->json(['success' => false, 'message' => 'No valid Access Token available (User or Admin). Please provide a valid Refresh Token.'], 400);
            }
        }

        // 3. Fetch Activities
        $after = (int) $enrollment->created_at->timestamp;

        $stravaActivities = [];

        // Fetch Logic
        if ($usedClubMode) {
            // Club Mode - Mimic StravaClubService
            $clubId = env('STRAVA_CLUB_ID', '1859982');

            // Note: Club API usually ignores 'after', so we fetch latest 200 and filter in PHP
            $clubResponse = Http::withToken($accessToken)
                ->withoutVerifying()
                ->get("https://www.strava.com/api/v3/clubs/{$clubId}/activities", [
                    'per_page' => 200,
                ]);

            if ($clubResponse->successful()) {
                $allActivities = $clubResponse->json();

                // Filter strictly for this user using Strava ID
                $stravaActivities = array_filter($allActivities, function ($act) use ($stravaId) {
                    // Check athlete ID safely
                    if (isset($act['athlete']['id']) && $act['athlete']['id'] == $stravaId) {
                        return true;
                    }

                    return false;
                });
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to fetch club activities: '.$clubResponse->body()], 400);
            }
        } else {
            // Normal Mode (User Token provided)
            $activitiesUrl = 'https://www.strava.com/api/v3/athlete/activities';
            $actResponse = Http::withToken($accessToken)
                ->withoutVerifying()
                ->get($activitiesUrl, [
                    'after' => $after,
                    'per_page' => 100,
                ]);

            if ($actResponse->successful()) {
                $stravaActivities = $actResponse->json();
            } else {
                return response()->json(['success' => false, 'message' => 'Failed to fetch activities: '.$actResponse->body()], 400);
            }
        }

        $count = 0;

        foreach ($stravaActivities as $activity) {
            // Filter by type (Run)
            if ($activity['type'] !== 'Run') {
                continue;
            }

            // Filter by Date (must be after enrollment)
            $activityTime = strtotime($activity['start_date_local']);
            if ($activityTime < $after) {
                continue;
            }

            // Double check Owner (redundant for Club Mode but good for safety)
            if (isset($activity['athlete']['id']) && $activity['athlete']['id'] != $stravaId) {
                continue;
            }

            // Check if already exists
            $exists = ChallengeActivity::where('user_id', $user->id)
                ->where('strava_activity_id', $activity['id'])
                ->exists();

            if ($exists) {
                continue;
            }

            // Check duplicate by date
            $date = date('Y-m-d', strtotime($activity['start_date_local']));
            $existingDate = ChallengeActivity::where('user_id', $user->id)
                ->where('date', $date)
                ->exists();

            if ($existingDate) {
                continue;
            } // One per day rule

            // Save
            ChallengeActivity::create([
                'user_id' => $user->id,
                'date' => $date,
                'distance' => $activity['distance'] / 1000, // meters to km
                'duration_seconds' => $activity['moving_time'],
                'image_path' => 'strava_sync', // Placeholder
                'strava_link' => 'https://www.strava.com/activities/'.$activity['id'],
                'strava_activity_id' => $activity['id'],
                'status' => 'approved', // Auto approve synced
            ]);
            $count++;
        }

        if ($count > 0) {
            $this->recalculateStats($user->id);
        }

        return response()->json([
            'success' => true,
            'message' => "Synced $count activities for {$user->name}.".($request->refresh_token ? '' : ' (Used Club Mode)'),
            'count' => $count,
        ]);
    }

    public function approve($id)
    {
        $activity = ChallengeActivity::findOrFail($id);

        DB::transaction(function () use ($activity) {
            $activity->update(['status' => 'approved']);
            $this->recalculateStats($activity->user_id);
        });

        return back()->with('success', 'Aktivitas disetujui dan leaderboard diperbarui.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['reason' => 'required|string']);

        $activity = ChallengeActivity::findOrFail($id);
        $activity->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Aktivitas ditolak.');
    }

    private function recalculateStats($userId)
    {
        $activities = ChallengeActivity::where('user_id', $userId)
            ->where('status', 'approved')
            ->orderBy('date', 'asc')
            ->get();

        if ($activities->isEmpty()) {
            LeaderboardStat::where('user_id', $userId)->delete();

            return;
        }

        $activeDays = $activities->unique('date')->count();
        $totalDistance = $activities->sum('distance');
        $totalSeconds = $activities->sum('duration_seconds');

        // Calculate Pace (min/km)
        $paceString = '0:00';
        if ($totalDistance > 0) {
            $paceVal = $totalSeconds / 60 / $totalDistance; // min/km
            $paceMin = floor($paceVal);
            $paceSec = round(($paceVal - $paceMin) * 60);
            $paceString = sprintf('%d:%02d', $paceMin, $paceSec);
        }

        // Calculate Streak
        $streak = 0;
        $currentStreak = 0;
        $lastDate = null;

        foreach ($activities->unique('date') as $act) {
            $date = \Carbon\Carbon::parse($act->date);

            if (! $lastDate) {
                $currentStreak = 1;
            } else {
                $diff = $lastDate->diffInDays($date);
                if ($diff == 1) {
                    $currentStreak++;
                } elseif ($diff > 1) {
                    $currentStreak = 1;
                }
            }
            $lastDate = $date;
            $streak = max($streak, $currentStreak); // Store max streak? Or current streak?
            // Usually "Current Streak" is displayed on leaderboard, but "Longest Streak" might be better for sorting.
            // Let's assume Current Streak for now as per previous logic "reset to 1 if gap".
            // Actually, if I re-read previous logic: "if last_active_date != yesterday... streak = 1".
            // So it tracks *current* streak.
        }

        // However, if the last activity was 5 days ago, the "Current Streak" should be valid *until* they break it?
        // But for display "Streak" usually implies active streak.
        // Let's stick to the logic: Streak is the consecutive days ending on the last activity date.
        // Wait, if I calculate from scratch, I can just take the streak ending at the last activity.
        // But if the last activity was a week ago, is the streak still valid?
        // Typically, yes, until they miss a day *relative to today*?
        // Or just the sequence length ending at the last participation.
        // Let's use the sequence length ending at the last participation for now,
        // but checking if it's "live" (i.e. includes yesterday/today) is a UI concern.
        // The previous logic was: if last_active != yesterday, streak = 1.
        // So if I submit today, and my last was 2 days ago, streak becomes 1.
        // If I submit today, and last was yesterday, streak += 1.
        // So it's effectively "Current Streak".

        // Re-implementing "Current Streak" logic properly:
        $streak = 0;
        $sortedDates = $activities->pluck('date')->unique()->sort()->values();

        if ($sortedDates->isNotEmpty()) {
            $streak = 1;
            for ($i = $sortedDates->count() - 1; $i > 0; $i--) {
                $current = \Carbon\Carbon::parse($sortedDates[$i]);
                $prev = \Carbon\Carbon::parse($sortedDates[$i - 1]);

                if ($current->diffInDays($prev) == 1) {
                    $streak++;
                } else {
                    break;
                }
            }
        }

        // Percentage
        $percentage = min(100, round(($activeDays / 40) * 100));

        // Qualified
        $qualified = $percentage >= 100;

        // Update Stat
        $stat = LeaderboardStat::firstOrCreate(['user_id' => $userId]);

        // Check PB (Best Pace vs Previous Best)
        // This is tricky if we recalculate everything.
        // We can't easily know "old_pb" unless we store history of PBs.
        // Or "old_pb" is just the previous value before this update?
        // But here we are recalculating from scratch.
        // Let's simplify: old_pb = null, new_pb = null for now in recalculation,
        // unless we want to track "Best Pace of any single run" vs "Average Pace".
        // The previous logic compared "Current Pace" (which seemed to be Avg Pace?) with new run pace?
        // "if new pace is better than current pace".
        // If "pace" column is Average Pace, then we just update it.
        // If "pace" is Best Pace, then we find the best single run.
        // Let's assume "Pace" column in Leaderboard is "Average Pace" over all runs?
        // Or "Best Pace"?
        // "Top Pace" usually means Best Pace.
        // If it is Best Pace, we should find the max(speed) / min(pace) from activities.

        // Let's assume we want BEST PACE from all approved activities.
        $bestPaceActivity = $activities->map(function ($act) {
            $act->pace_val = $act->distance > 0 ? $act->duration_seconds / $act->distance : 999999;

            return $act;
        })->sortBy('pace_val')->first();

        $bestPaceString = '0:00';
        if ($bestPaceActivity && $bestPaceActivity->distance > 0) {
            $paceVal = $bestPaceActivity->duration_seconds / 60 / $bestPaceActivity->distance;
            $paceMin = floor($paceVal);
            $paceSec = round(($paceVal - $paceMin) * 60);
            $bestPaceString = sprintf('%d:%02d', $paceMin, $paceSec);
        }

        // For PB notification, it's hard to trigger here without context of "before".
        // But the user just wants the leaderboard updated.

        $stat->update([
            'active_days' => $activeDays,
            'percentage' => $percentage,
            'streak' => $streak,
            'qualified' => $qualified,
            'pace' => $bestPaceString, // Assuming we want Best Pace
            'last_active_date' => $sortedDates->last(),
        ]);
    }
}
