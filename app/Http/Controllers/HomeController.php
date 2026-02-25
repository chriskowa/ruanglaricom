<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\HomepageContent;
use App\Models\Pacer;
use App\Models\ProgramEnrollment;
use App\Models\User;
use App\Services\StravaClubService;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(StravaClubService $stravaService)
    {
        $homepageContent = Cache::remember('home.content', 3600, function () {
            return HomepageContent::first();
        });

        $featuredEvent = Cache::remember('home.featured_event', 300, function () {
            $event = Event::query()
                ->where('is_featured', true)
                ->where('status', 'published')
                ->where('start_at', '>=', now())
                ->orderBy('start_at', 'asc')
                ->with('user')
                ->first();

            if ($event) {
                return $event;
            }

            return Event::query()
                ->where('is_featured', true)
                ->orderBy('start_at', 'desc')
                ->with('user')
                ->first();
        });

        $leaderboard = Cache::remember('home.leaderboard.data', 3600, function () use ($stravaService) {
            try {
                $data = $stravaService->getLeaderboard();

                return (is_array($data) && ($data['fastest'] || $data['distance'] || $data['elevation'])) ? $data : null;
            } catch (\Throwable $e) {
                return null;
            }
        });

        // Fallback to permanent cache if recent fetch failed/returned null but we have old data
        if (! $leaderboard) {
            $leaderboard = Cache::get('home.leaderboard.last');
        } else {
            // Update permanent cache
            Cache::forever('home.leaderboard.last', $leaderboard);
            Cache::forever('home.leaderboard.last_at', now()->toISOString());
        }

        $topStats = Cache::remember('home.top_stats', 3600, function () {
            // OPTIMIZATION: Use role index (added in migration)
            // Use followers_count column for sorting (indexed) instead of withCount subquery
            $runner = User::where('role', 'runner')
                ->withCount('posts') // Only count posts, followers_count is a column
                ->orderByDesc('followers_count')
                ->first();

            $pacer = Pacer::with('user')
                ->orderByDesc('total_races')
                ->first();

            $coachData = ProgramEnrollment::selectRaw('programs.coach_id as coach_id, COUNT(*) as students_count')
                ->join('programs', 'program_enrollments.program_id', '=', 'programs.id')
                ->groupBy('programs.coach_id')
                ->orderByDesc('students_count')
                ->first();

            $coach = $coachData ? User::withCount('programs')->find($coachData->coach_id) : null;

            $totalUsers = User::whereIn('role', ['runner', 'coach'])->count();

            return [
                'runner' => $runner,
                'pacer' => $pacer,
                'coach' => $coach,
                'coachData' => $coachData,
                'totalUsers' => $totalUsers,
            ];
        });

        return view('home.index', [
            'homepageContent' => $homepageContent,
            'featuredEvent' => $featuredEvent,
            'leaderboard' => $leaderboard,
            'topRunner' => $topStats['runner'],
            'topPacer' => $topStats['pacer'],
            'topCoach' => $topStats['coach'],
            'topCoachData' => $topStats['coachData'],
            'totalUsers' => $topStats['totalUsers'] ?? 0,
        ]);
    }
}
