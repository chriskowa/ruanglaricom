<?php

namespace App\Http\Controllers;

use App\Models\ChallengeActivity;
use App\Models\LeaderboardStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminChallengeController extends Controller
{
    public function index()
    {
        $activities = ChallengeActivity::with('user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('admin.challenge.index', compact('activities'));
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
            'rejection_reason' => $request->reason
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
            
            if (!$lastDate) {
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
                $prev = \Carbon\Carbon::parse($sortedDates[$i-1]);
                
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
        $bestPaceActivity = $activities->map(function($act) {
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
            'last_active_date' => $sortedDates->last()
        ]);
    }
}
