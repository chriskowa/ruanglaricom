<?php

namespace App\Http\Controllers;

use App\Models\LeaderboardStat;
use App\Models\User;
use App\Services\StravaClubService;

class LeaderboardController extends Controller
{
    public function index()
    {
        $stats = LeaderboardStat::with('user')->get();

        $discipline = $stats->map(function ($s) {
            $u = $s->user;
            return [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->avatar_url ?? ($u->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($u->name)),
                'gender' => strtoupper($u->gender ?? 'M') === 'FEMALE' ? 'F' : 'M',
                'active_days' => (int)($s->active_days ?? 0),
                'percentage' => (int)($s->percentage ?? 0),
                'streak' => (int)($s->streak ?? 0),
                'qualified' => (bool)($s->qualified ?? false),
            ];
        })->values();

        $performance = $stats->filter(function ($s) {
            return !empty($s->new_pb);
        })->map(function ($s) {
            $u = $s->user;
            return [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->avatar_url ?? ($u->photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($u->name)),
                'gender' => strtoupper($u->gender ?? 'M') === 'FEMALE' ? 'F' : 'M',
                'old_pb' => $s->old_pb,
                'new_pb' => $s->new_pb,
                'gap' => $s->gap,
                'gap_seconds' => (int)($s->gap_seconds ?? 0),
                'pace' => $s->pace,
            ];
        })->values();

        return response()->json([
            'discipline' => $discipline,
            'performance' => $performance,
        ]);
    }

    public function clubMembers(StravaClubService $strava)
    {
        $members = $strava->getClubMembers();
        return response()->json(['members' => $members]);
    }
}