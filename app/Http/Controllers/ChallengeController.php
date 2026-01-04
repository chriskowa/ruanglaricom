<?php

namespace App\Http\Controllers;

use App\Models\ChallengeActivity;
use App\Models\LeaderboardStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChallengeController extends Controller
{
    public function index(Request $request)
    {
        // Get sort parameter, default to percentage
        $sortBy = $request->get('sort', 'percentage');

        $query = LeaderboardStat::with('user');

        switch ($sortBy) {
            case 'streak':
                $query->orderBy('streak', 'desc');
                break;
            case 'pace':
                $query->orderBy('pace', 'asc');
                break;
            case 'percentage':
            default:
                $query->orderBy('percentage', 'desc');
                break;
        }

        // Secondary sort by active_days desc
        $query->orderBy('active_days', 'desc');

        $runners = $query->get();

        // Prepare data for Vue to avoid complex Blade logic
        $runnersJson = $runners->map(function ($stat) {
            $user = $stat->user;
            $avatar = $user && $user->avatar
                ? (str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/'.$user->avatar))
                : 'https://ui-avatars.com/api/?name='.urlencode($user->name ?? 'Runner');

            return [
                'user_id' => $stat->user_id,
                'name' => $user->name ?? 'Runner',
                'avatar' => $avatar,
                'active_days' => $stat->active_days,
                'percentage' => $stat->percentage,
                'streak' => $stat->streak,
                'qualified' => $stat->qualified,
                'old_pb' => $stat->old_pb,
                'new_pb' => $stat->new_pb,
                'gap' => $stat->gap,
                'pace' => $stat->pace ?? '0:00',
            ];
        });

        return view('challenge.index', compact('runners', 'sortBy', 'runnersJson'));
    }

    public function create()
    {
        // Check if user is enrolled
        $activities = ChallengeActivity::where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->get();

        return view('challenge.submit', compact('activities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'distance' => 'required|numeric|min:0.01',
            'duration_hours' => 'required|integer|min:0',
            'duration_minutes' => 'required|integer|min:0|max:59',
            'duration_seconds' => 'required|integer|min:0|max:59',
            'image' => 'required|image|max:5120', // 5MB max
            'strava_link' => 'nullable|url',
        ]);

        $user = Auth::user();

        // Check for duplicate submission for the same date
        $existing = ChallengeActivity::where('user_id', $user->id)
            ->where('date', $request->date)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah menyetor aktivitas untuk tanggal ini! Mohon tunggu persetujuan atau setor untuk tanggal lain.',
            ], 422);
        }

        // Calculate total seconds
        $totalSeconds = ($request->duration_hours * 3600) + ($request->duration_minutes * 60) + $request->duration_seconds;

        if ($totalSeconds <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Durasi tidak boleh 0!',
            ], 422);
        }

        // Upload Image
        $imagePath = $request->file('image')->store('activity_proofs', 'public');

        // Create Activity Record
        ChallengeActivity::create([
            'user_id' => $user->id,
            'date' => $request->date,
            'distance' => $request->distance,
            'duration_seconds' => $totalSeconds,
            'image_path' => $imagePath,
            'strava_link' => $request->strava_link,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lari berhasil disetor dan menunggu persetujuan admin!',
            'is_pb' => false,
        ]);
    }

    private function comparePace($pace1, $pace2)
    {
        // Returns -1 if pace1 < pace2 (faster), 0 if equal, 1 if pace1 > pace2 (slower)
        [$m1, $s1] = explode(':', $pace1);
        [$m2, $s2] = explode(':', $pace2);

        $sec1 = $m1 * 60 + $s1;
        $sec2 = $m2 * 60 + $s2;

        return $sec1 <=> $sec2;
    }
}
