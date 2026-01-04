<?php

namespace App\Console\Commands;

use App\Models\LeaderboardStat;
use App\Models\User;
use App\Services\StravaClubService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LeaderboardSync extends Command
{
    protected $signature = 'leaderboard:sync';

    protected $description = 'Sync Strava club activities into 40days leaderboard stats';

    public function handle(StravaClubService $strava)
    {
        $this->info('Fetching Strava club activities...');
        $activities = $strava->fetchClubActivitiesByEnv();
        $count = count($activities);
        $this->info("Fetched {$count} activities");

        $users = User::where('program', '40days')->get();
        $this->info('Processing users: '.$users->count());

        $byAthlete = [];
        foreach ($activities as $act) {
            $name = trim(($act['athlete']['firstname'] ?? '').' '.($act['athlete']['lastname'] ?? ''));
            if ($name === '') {
                continue;
            }
            $byAthlete[$name][] = $act;
        }

        foreach ($users as $user) {
            $matchName = $this->findMatchName($user->name, array_keys($byAthlete));
            $userActs = $matchName ? ($byAthlete[$matchName] ?? []) : [];

            $stat = LeaderboardStat::firstOrCreate(['user_id' => $user->id]);
            $today = Carbon::today();

            $didWorkoutToday = false;
            $best5kPaceSecondsPerKm = null;
            $best5kTimeSeconds = null;

            foreach ($userActs as $act) {
                $type = $act['type'] ?? '';
                $distanceMeters = (int) ($act['distance'] ?? 0);
                $startDate = Carbon::parse($act['start_date_local'] ?? $act['start_date'] ?? Carbon::now());
                if ($startDate->isSameDay($today)) {
                    $didWorkoutToday = true;
                }

                if (Str::lower($type) === 'run' && $distanceMeters >= 5000) {
                    $elapsedSec = (int) ($act['elapsed_time'] ?? 0);
                    if ($elapsedSec > 0) {
                        if ($best5kTimeSeconds === null || $elapsedSec < $best5kTimeSeconds) {
                            $best5kTimeSeconds = $elapsedSec;
                            $km = max($distanceMeters / 1000, 1);
                            $best5kPaceSecondsPerKm = (int) round($elapsedSec / $km);
                        }
                    }
                }
            }

            if ($didWorkoutToday) {
                if (! $stat->last_active_date || ! $stat->last_active_date->isSameDay($today)) {
                    $stat->active_days = ($stat->active_days ?? 0) + 1;
                    if ($stat->last_active_date && $stat->last_active_date->isYesterday()) {
                        $stat->streak = ($stat->streak ?? 0) + 1;
                    } else {
                        $stat->streak = max($stat->streak ?? 0, 1);
                    }
                    $stat->last_active_date = $today;
                }
            }

            $stat->percentage = min(100, (int) floor((($stat->active_days ?? 0) / 40) * 100));
            $stat->qualified = ($stat->active_days ?? 0) >= 40;

            if ($best5kTimeSeconds) {
                $oldPb = $user->baseline_5k ?? null;
                $stat->old_pb = $oldPb ? $this->formatTimeSeconds($oldPb) : null;
                $stat->new_pb = $this->formatTimeSeconds($best5kTimeSeconds);
                $gapSec = $oldPb ? ($oldPb - $best5kTimeSeconds) : 0;
                $stat->gap_seconds = $gapSec;
                $stat->gap = $this->formatGap($gapSec);
                if ($best5kPaceSecondsPerKm) {
                    $stat->pace = $this->formatPaceSeconds($best5kPaceSecondsPerKm);
                }
            }

            $stat->save();
            $this->line("Updated stat for {$user->name}");
        }

        $this->info('Leaderboard sync completed.');

        return Command::SUCCESS;
    }

    private function normalizeName(string $name): string
    {
        return Str::lower(preg_replace('/\s+/', ' ', trim($name)));
    }

    private function findMatchName(?string $userName, array $candidateNames): ?string
    {
        if (! $userName) {
            return null;
        }
        $normUser = $this->normalizeName($userName);
        $best = null;
        $bestScore = 0;
        foreach ($candidateNames as $cand) {
            $normCand = $this->normalizeName($cand);
            similar_text($normUser, $normCand, $percent);
            if ($percent > $bestScore) {
                $bestScore = $percent;
                $best = $cand;
            }
        }

        return $bestScore >= 70 ? $best : null;
    }

    private function formatTimeSeconds(int $seconds): string
    {
        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        return sprintf('%d:%02d', $m, $s);
    }

    private function formatPaceSeconds(int $secondsPerKm): string
    {
        $m = intdiv($secondsPerKm, 60);
        $s = $secondsPerKm % 60;

        return sprintf('%d:%02d', $m, $s);
    }

    private function formatGap(int $gapSec): string
    {
        $sign = $gapSec < 0 ? '-' : ($gapSec > 0 ? '+' : '');
        $abs = abs($gapSec);
        $m = intdiv($abs, 60);
        $s = $abs % 60;

        return sprintf('%s%dm %02ds', $sign, $m, $s);
    }
}
