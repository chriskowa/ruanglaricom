<?php

namespace App\Services;

class CoachFeasibilityService
{
    private const MATRIX = [
        '5k' => [
            'beginner'     => ['min' => 15, 'ideal' => 22, 'max' => 30, 'min_weeks' => 8,  'min_freq' => 3],
            'intermediate' => ['min' => 22, 'ideal' => 32, 'max' => 45, 'min_weeks' => 8,  'min_freq' => 4],
            'advanced'     => ['min' => 30, 'ideal' => 42, 'max' => 60, 'min_weeks' => 8,  'min_freq' => 5],
        ],
        '10k' => [
            'beginner'     => ['min' => 18, 'ideal' => 26, 'max' => 35, 'min_weeks' => 10, 'min_freq' => 3],
            'intermediate' => ['min' => 28, 'ideal' => 40, 'max' => 55, 'min_weeks' => 10, 'min_freq' => 4],
            'advanced'     => ['min' => 42, 'ideal' => 55, 'max' => 75, 'min_weeks' => 10, 'min_freq' => 5],
        ],
        '21k' => [
            'beginner'     => ['min' => 22, 'ideal' => 34, 'max' => 48, 'min_weeks' => 12, 'min_freq' => 4],
            'intermediate' => ['min' => 35, 'ideal' => 48, 'max' => 68, 'min_weeks' => 14, 'min_freq' => 4],
            'advanced'     => ['min' => 50, 'ideal' => 68, 'max' => 95, 'min_weeks' => 14, 'min_freq' => 5],
        ],
        '42k' => [
            'beginner'     => ['min' => 30, 'ideal' => 45, 'max' => 65, 'min_weeks' => 18, 'min_freq' => 4],
            'intermediate' => ['min' => 50, 'ideal' => 70, 'max' => 95, 'min_weeks' => 16, 'min_freq' => 5],
            'advanced'     => ['min' => 70, 'ideal' => 90, 'max' => 120, 'min_weeks' => 16, 'min_freq' => 5],
        ],
    ];

    private const VDOT_RATE = [
        'beginner'     => 0.4,
        'intermediate' => 0.5,
        'advanced'     => 0.6,
    ];

    private const AGGRESSIVENESS_MULTIPLIER = [
        'conservative' => ['mileage' => 0.92, 'weeks' => 1.15],
        'standard'     => ['mileage' => 1.00, 'weeks' => 1.00],
        'sharp'        => ['mileage' => 1.10, 'weeks' => 0.90],
    ];

    public function assess(array $input): array
    {
        $distance       = strtolower((string) ($input['target_distance'] ?? '10k'));
        $goalSec        = (int)    ($input['goal_time_sec'] ?? 0);
        $level          = strtolower((string) ($input['runner_level'] ?? 'intermediate'));
        $weeklyMileage  = (float)  ($input['weekly_mileage'] ?? 0);
        $frequency      = (int)    ($input['frequency'] ?? 4);
        $weeks          = (int)    ($input['weeks'] ?? 12);
        $initialVdot    = (float)  ($input['initial_vdot'] ?? 35);
        $targetVdot     = (float)  ($input['target_vdot'] ?? 0);
        $injury         = strtolower((string) ($input['injury_history'] ?? 'none'));
        $aggressiveness = strtolower((string) ($input['aggressiveness'] ?? 'standard'));

        if (!isset(self::MATRIX[$distance])) {
            $distance = '10k';
        }
        if (!isset(self::MATRIX[$distance][$level])) {
            $level = array_key_first(self::MATRIX[$distance]);
        }
        if (!isset(self::AGGRESSIVENESS_MULTIPLIER[$aggressiveness])) {
            $aggressiveness = 'standard';
        }

        $base = self::MATRIX[$distance][$level];
        $agg  = self::AGGRESSIVENESS_MULTIPLIER[$aggressiveness];

        if ($injury !== 'none') {
            $aggressiveness = match ($aggressiveness) {
                'sharp'        => 'standard',
                'standard'     => 'conservative',
                default        => 'conservative',
            };
            $agg = self::AGGRESSIVENESS_MULTIPLIER[$aggressiveness];
        }

        $minMileage = (float) $base['min'];
        $idealMileage = round($base['ideal'] * $agg['mileage']);
        $maxMileage = (float) $base['max'];
        $minWeeks   = (int) ceil($base['min_weeks'] * $agg['weeks']);
        $maxWeeks   = (int) ($minWeeks * 2.2);
        $minFreq    = (int) $base['min_freq'];

        $goalPacePerKmSec = ($goalSec > 0) ? ($goalSec / $this->distanceKm($distance)) : 0;

        if ($distance === '10k' && $goalSec > 0 && $goalPacePerKmSec <= 222) {
            if ($level === 'advanced') {
                $minMileage = max($minMileage, 42);
                $idealMileage = match (true) {
                    $aggressiveness === 'sharp' => max($idealMileage, 56),
                    default                     => max($idealMileage, 52),
                };
            } else {
                $minMileage = max($minMileage, ($level === 'intermediate' ? 45 : 48));
                $idealMileage = match (true) {
                    $aggressiveness === 'sharp' => max($idealMileage, 55),
                    default                     => max($idealMileage, 50),
                };
            }
            $minFreq = max($minFreq, 5);
            $minWeeks = max($minWeeks, 10);
        }

        if ($injury !== 'none') {
            $minWeeks += 2;
            $idealMileage = (int) round($idealMileage * 0.95);
        }

        $vdotRate = self::VDOT_RATE[$level] ?? 0.45;
        if ($targetVdot <= 0) {
            $targetVdot = $initialVdot;
        }
        $deltaVdot = max(0, $targetVdot - $initialVdot);
        $requiredWeeksByVdot = $vdotRate > 0 ? (int) ceil($deltaVdot / $vdotRate) : 0;
        if ($distance === '42k') {
            $requiredWeeksByVdot = max($requiredWeeksByVdot, (int) ceil($minWeeks * 0.85));
        }

        $violations = [];
        $score = 100;

        if ($weeklyMileage > 0) {
            if ($weeklyMileage < $minMileage) {
                $violations[] = "Beban latihan saat ini ($weeklyMileage km/minggu) di bawah minimum fisiologis ($minMileage km) untuk target ini.";
                $score -= 35;
            } elseif ($weeklyMileage > $maxMileage) {
                $violations[] = "Beban latihan ($weeklyMileage km) melebihi batas aman ($maxMileage km) untuk level $level.";
                $score -= 30;
            } elseif ($weeklyMileage > $idealMileage * 1.04) {
                $score -= 8;
                $violations[] = "Beban latihan mendekati batas atas aman; pastikan recovery cukup.";
            }
        }

        if ($frequency < $minFreq) {
            $violations[] = "Frekuensi latih ($frequency hari/minggu) kurang dari minimum ($minFreq hari) untuk target ini.";
            $score -= 18;
        }

        if ($weeks < $minWeeks) {
            $violations[] = "Timeline persiapan ($weeks pekan) kurang dari minimum ($minWeeks pekan) untuk jarak target ini.";
            $score -= 30;
        } elseif ($weeks > $maxWeeks) {
            $violations[] = "Timeline terlalu panjang ($weeks pekan) — risiko peaking terlalu dini / burnout.";
            $score -= 8;
        }

        if ($requiredWeeksByVdot > 0 && $weeks < $requiredWeeksByVdot) {
            $pct = $deltaVdot > 0 ? round(($deltaVdot / max(1, $initialVdot)) * 100, 1) : 0;
            $violations[] = "Peningkatan VDOT target sebesar +$pct% (VDOT $initialVdot → $targetVdot) membutuhkan minimal $requiredWeeksByVdot pekan dengan rate $vdotRate/minggu; $weeks pekan tersedia tidak cukup.";
            $score -= 32;
        }

        $score = max(0, min(100, $score));

        [$feasibility, $color, $label] = $this->feasibilityTier($score, $requiredWeeksByVdot, $weeks, $weeklyMileage, $minMileage);

        $paceStr = $goalSec > 0 ? $this->formatPace($goalPacePerKmSec) : '—';
        $goalTimeStr = $goalSec > 0 ? $this->formatDuration($goalSec) : '—';

        $reason = $this->buildReason(
            $distance,
            $goalTimeStr,
            $paceStr,
            $level,
            $weeklyMileage,
            $minMileage,
            $idealMileage,
            $weeks,
            $minWeeks,
            $initialVdot,
            $targetVdot,
            $deltaVdot,
            $vdotRate,
            $requiredWeeksByVdot,
            $feasibility,
            $violations
        );

        $options = $this->buildOptions(
            $distance,
            $goalSec,
            $level,
            $minMileage,
            $idealMileage,
            $minWeeks,
            $weeks,
            $feasibility,
            $requiredWeeksByVdot,
            $weeklyMileage
        );

        return [
            'feasibility' => $feasibility,
            'score' => $score,
            'min_required_peak_mileage' => round($minMileage, 1),
            'ideal_peak_mileage' => (float) $idealMileage,
            'max_safe_peak_mileage' => round($maxMileage, 1),
            'min_weeks' => $minWeeks,
            'max_weeks' => $maxWeeks,
            'min_frequency' => $minFreq,
            'color' => $color,
            'label' => $label,
            'reason' => $reason,
            'options' => $options,
            'vdot_rate_per_week' => $vdotRate,
            'required_weeks_by_vdot' => $requiredWeeksByVdot,
            'violations' => $violations,
        ];
    }

    private function distanceKm(string $distance): float
    {
        return match ($distance) {
            '5k'  => 5.0,
            '10k' => 10.0,
            '21k' => 21.0975,
            '42k' => 42.195,
            default => 10.0,
        };
    }

    private function feasibilityTier(int $score, int $reqWeeks, int $weeks, float $weeklyMileage, float $minMileage): array
    {
        if ($weeklyMileage > 0 && $weeklyMileage < $minMileage * 0.85) {
            return ['INFEASIBLE', 'red', 'Tidak Realistis'];
        }
        if ($reqWeeks > 0 && $weeks < $reqWeeks * 0.8) {
            return ['INFEASIBLE', 'red', 'Tidak Realistis'];
        }
        if ($score >= 70) return ['FEASIBLE',   'emerald', 'Realistis'];
        if ($score >= 40) return ['AGGRESSIVE', 'amber',   'Agresif'];
        if ($score >= 20) return ['HIGH_RISK',  'orange',  'Risiko Tinggi'];
        return ['INFEASIBLE', 'red', 'Tidak Realistis'];
    }

    private function buildReason(...$args): string
    {
        [
            $distance, $goalTimeStr, $paceStr, $level, $weeklyMileage, $minMileage,
            $idealMileage, $weeks, $minWeeks, $initialVdot, $targetVdot,
            $deltaVdot, $vdotRate, $requiredWeeksByVdot, $feasibility, $violations
        ] = $args;

        $distanceLabel = strtoupper($distance);
        $pctVdot = $initialVdot > 0 ? round(($deltaVdot / $initialVdot) * 100, 1) : 0;

        $parts = [];
        if ($distance === '10k' && $minMileage >= 42) {
            $parts[] = "Target $distanceLabel $goalTimeStr ($paceStr/km termasuk kategori cepat) menuntut stimulus aerobik + adaptasi ambang laktat yang tidak dapat dicapai hanya dengan 20–30 km/minggu.";
            $parts[] = "Minimum puncak mingguan = $minMileage km dengan frekuensi latih minimal 5 hari/minggu, idealnya $idealMileage km selama minimal $minWeeks pekan.";
        } else {
            $parts[] = "Target {$distanceLabel} {$goalTimeStr} untuk level {$level} membutuhkan puncak beban {$minMileage}–{$idealMileage} km/minggu.";
        }

        if ($weeklyMileage > 0) {
            $ratio = $weeklyMileage > 0 ? round(($weeklyMileage / max(1, $minMileage)) * 100, 0) : 0;
            $parts[] = "Beban Anda saat ini = $weeklyMileage km ($ratio% dari minimum fisiologis).";
        }

        if ($deltaVdot > 0) {
            $parts[] = "Peningkatan kemampuan yang dibutuhkan VDOT $initialVdot → $targetVdot (+$pctVdot%) pada rate fisiologis $vdotRate VDOT/minggu membutuhkan minimal $requiredWeeksByVdot pekan; timeline Anda $weeks pekan.";
            if ($requiredWeeksByVdot > $weeks) {
                $parts[] = "Risiko: beban stimulasi per pekan melebihi ambang adaptasi aman → risiko cedera > 60% (ITBS, shin splint, overload jantung).";
            }
        }

        if ($feasibility === 'FEASIBLE' || $feasibility === 'AGGRESSIVE') {
            $parts[] = "Kami tetap menerapkan aturan 10% peningkatan mingguan + deload 20% setiap 4 minggu untuk menjaga keamanan.";
        }

        if (!empty($violations)) {
            $parts[] = "Hal yang perlu diperbaiki: " . implode(" ", $violations);
        }

        return implode(" ", $parts);
    }

    private function buildOptions(string $distance, int $goalSec, string $level, float $minMileage, float $idealMileage, int $minWeeks, int $weeks, string $feasibility, int $requiredWeeksByVdot, float $weeklyMileage): array
    {
        $options = [];
        $needsAdjust = in_array($feasibility, ['AGGRESSIVE', 'HIGH_RISK', 'INFEASIBLE'], true);

        if ($weeklyMileage < $minMileage || $needsAdjust) {
            $options[] = [
                'id' => 'apply_mileage',
                'label' => 'Terapkan saran beban: '. (int) $idealMileage .' km peak',
                'apply' => ['weekly_mileage' => (int) $idealMileage],
            ];
        }

        if ($weeks < $minWeeks || ($requiredWeeksByVdot > 0 && $weeks < $requiredWeeksByVdot)) {
            $targetWeeks = (int) max($minWeeks, $requiredWeeksByVdot + 2);
            $options[] = [
                'id' => 'extend_weeks',
                'label' => 'Perpanjang timeline ke '. $targetWeeks .' pekan',
                'apply' => ['weeks' => $targetWeeks],
            ];
        }

        if ($goalSec > 0) {
            $goalMin = (int) floor($goalSec / 60);
            if ($distance === '10k' && $goalMin <= 37) {
                $options[] = [
                    'id' => 'ease_goal_42',
                    'label' => 'Ubah target finish menjadi 00:42:00 (4:12/km)',
                    'apply' => ['goal_time_sec' => 42 * 60],
                ];
            }
            if ($distance === '10k' && $goalMin <= 40 && $goalMin > 37) {
                $options[] = [
                    'id' => 'ease_goal_45',
                    'label' => 'Ubah target finish menjadi 00:45:00 (4:30/km)',
                    'apply' => ['goal_time_sec' => 45 * 60],
                ];
            }
            if ($distance === '42k' && $minWeeks > 16) {
                $options[] = [
                    'id' => 'ease_goal_fm345',
                    'label' => 'Turunkan target Marathon menjadi 03:45:00 (5:20/km)',
                    'apply' => ['goal_time_sec' => (3 * 3600) + (45 * 60)],
                ];
            }
            if ($distance === '21k' && $minWeeks > 14) {
                $options[] = [
                    'id' => 'ease_goal_hm145',
                    'label' => 'Turunkan target Half-Marathon menjadi 01:45:00 (4:58/km)',
                    'apply' => ['goal_time_sec' => (1 * 3600) + (45 * 60)],
                ];
            }
        }

        if ($weeklyMileage >= $idealMileage && $weeks >= $minWeeks) {
            $options[] = [
                'id' => 'conservative_mode',
                'label' => 'Pilih mode konservatif (beban -10%) untuk mengurangi risiko cedera',
                'apply' => ['aggressiveness' => 'conservative'],
            ];
        }

        return $options;
    }

    private function formatPace(float $secPerKm): string
    {
        $m = (int) floor($secPerKm / 60);
        $s = (int) round($secPerKm - ($m * 60));
        return sprintf('%d:%02d', $m, $s);
    }

    private function formatDuration(int $totalSec): string
    {
        $h = (int) floor($totalSec / 3600);
        $m = (int) floor(($totalSec % 3600) / 60);
        $s = (int) ($totalSec % 60);
        if ($h > 0) return sprintf('%02d:%02d:%02d', $h, $m, $s);
        return sprintf('%02d:%02d', $m, $s);
    }
}
