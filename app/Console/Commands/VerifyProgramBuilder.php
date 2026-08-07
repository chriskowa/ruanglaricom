<?php

namespace App\Console\Commands;

use App\Services\DanielsRunningService;
use App\Services\ProgramBuilderService;
use Illuminate\Console\Command;

class VerifyProgramBuilder extends Command
{
    protected $signature = 'program:verify {--v}';
    protected $description = 'Verify ProgramBuilderService output for all distance/level combos';

    public function handle(): void
    {
        $builder = app(ProgramBuilderService::class);

        $scenarios = [
            ['5k',  'beginner',     8,  15,  30],
            ['5k',  'intermediate', 12, 30,  42],
            ['5k',  'advanced',     16, 55,  58],
            ['10k', 'beginner',     12, 20,  32],
            ['10k', 'intermediate', 12, 35,  45],
            ['10k', 'advanced',     16, 60,  60],
            ['21k', 'beginner',     16, 30,  34],
            ['21k', 'intermediate', 16, 50,  47],
            ['21k', 'advanced',     20, 70,  62],
            ['42k', 'beginner',     20, 40,  35],
            ['42k', 'intermediate', 20, 60,  48],
            ['42k', 'advanced',     24, 85,  63],
        ];

        $this->line('');
        $this->info('=== PROGRAM BUILDER VERIFICATION ===');

        foreach ($scenarios as $s) {
            [$dist, $level, $weeks, $mileage, $vdot] = $s;

            $config = [
                'target_distance'  => $dist,
                'weekly_mileage'   => $mileage,
                'frequency'        => match($level) { 'beginner' => 3, 'intermediate' => 4, default => 5 },
                'weeks'            => $weeks,
                'initial_vdot'     => $vdot,
                'target_vdot'      => $vdot + 2.5,
                'runner_level'     => $level,
                'long_run_day'     => 'sunday',
                'is_tropical'      => false,
                'include_strength' => false,
            ];

            try {
                $result   = $builder->build($config);
                $sessions = $result['sessions'] ?? [];

                $weekData = [];
                foreach ($sessions as $sess) {
                    $w = $sess['week'] ?? 0;
                    if (!isset($weekData[$w])) {
                        $weekData[$w] = ['phase' => $sess['phase'], 'deload' => $sess['is_deload'] ?? false, 'mileage' => 0, 'long_run' => 0, 'quality' => []];
                    }
                    $weekData[$w]['mileage'] += ($sess['distance'] ?? 0);
                    if ($sess['type'] === 'long_run') $weekData[$w]['long_run'] = $sess['distance'] ?? 0;
                    if (!in_array($sess['type'], ['rest', 'easy_run', 'recovery_run', 'strength', 'long_run'])) {
                        $weekData[$w]['quality'][] = $sess['workout_name'] ?? $sess['type'];
                    }
                }

                $phaseSummary = [];
                $peakMileage  = 0;
                $peakLongRun  = 0;
                foreach ($weekData as $wd) {
                    $p = $wd['phase'];
                    if (!isset($phaseSummary[$p])) $phaseSummary[$p] = ['weeks' => 0, 'qtypes' => [], 'miles' => []];
                    $phaseSummary[$p]['weeks']++;
                    $phaseSummary[$p]['miles'][] = round($wd['mileage'], 1);
                    foreach ($wd['quality'] as $qt) $phaseSummary[$p]['qtypes'][] = $qt;
                    $peakMileage = max($peakMileage, $wd['mileage']);
                    $peakLongRun = max($peakLongRun, $wd['long_run']);
                }

                $label = strtoupper($dist) . ' | ' . strtoupper($level) . " | {$weeks}W | {$mileage}km/w | VDOT={$vdot}";
                $this->line('');
                $this->comment("─── $label ───");
                $this->line(sprintf("  Peak: %.1f km/w | Peak Long Run: %.1f km", $peakMileage, $peakLongRun));

                $rows = [];
                foreach ($phaseSummary as $phase => $pd) {
                    $avgMil = count($pd['miles']) ? round(array_sum($pd['miles']) / count($pd['miles']), 1) : 0;
                    $qtypes = !empty($pd['qtypes']) ? implode(', ', array_unique(array_map(fn($q) => substr($q,0,30), $pd['qtypes']))) : '(pure aerobic)';
                    $rows[] = [$phase, $pd['weeks'] . 'W', "{$avgMil} km", $qtypes];
                }
                $this->table(['Phase', 'Dur', 'Avg km/w', 'Quality Session Types'], $rows);

                if ($this->option('v')) {
                    $wrows = [];
                    foreach ($weekData as $w => $wd) {
                        $wrows[] = ["W{$w}", $wd['phase'].($wd['deload']?'[D]':''), round($wd['mileage'],1), round($wd['long_run'],1), implode(', ', $wd['quality']) ?: '—'];
                    }
                    $this->table(['Wk', 'Phase', 'km', 'LR', 'Quality'], $wrows);
                }

            } catch (\Throwable $e) {
                $this->error("  ERROR [{$dist}/{$level}]: " . $e->getMessage());
                $this->error("  " . $e->getFile() . ':' . $e->getLine());
            }
        }

        $this->line('');
        $this->info('Done. Add --v flag for full week-by-week detail.');
    }
}
