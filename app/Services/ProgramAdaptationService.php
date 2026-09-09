<?php

namespace App\Services;

use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProgramAdaptationService
{
    protected DanielsRunningService $daniels;
    protected ProgramBuilderService $builder;

    public function __construct(DanielsRunningService $daniels, ProgramBuilderService $builder)
    {
        $this->daniels = $daniels;
        $this->builder = $builder;
    }

    /**
     * Generate comprehensive feedback and 4-pillar adaptation recommendations
     * following an athlete check-in (PB or readiness update).
     */
    public function generateFeedback(User $user, float $newVdot, float $oldVdot, ?string $feeling = null, ?string $notes = null): ?array
    {
        $enrollment = ProgramEnrollment::where('runner_id', $user->id)
            ->whereIn('status', ['active', 'in_progress'])
            ->with('program')
            ->latest()
            ->first();

        if (! $enrollment || ! $enrollment->program) {
            return null;
        }

        $program = $enrollment->program;
        $programVdot = (float) ($enrollment->current_vdot ?? ($program->vdot_score ?? $oldVdot));
        if ($programVdot <= 0) {
            $programVdot = max(15.0, $oldVdot > 0 ? $oldVdot : 30.0);
        }

        $vdotDiff = round($newVdot - $programVdot, 1);
        $oldPaces = $this->daniels->calculateTrainingPaces($programVdot);
        $newPaces = $this->daniels->calculateTrainingPaces($newVdot);

        $oldEquiv = $this->daniels->calculateEquivalentRaceTimes($programVdot);
        $newEquiv = $this->daniels->calculateEquivalentRaceTimes($newVdot);

        $oldLevel = $this->getLevelData($programVdot);
        $newLevel = $this->getLevelData($newVdot);

        // Count completed vs remaining sessions
        $sessions = $program->program_json['sessions'] ?? [];
        $completedDays = [];
        if (!empty($enrollment->id)) {
            $completedDays = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                ->where('status', 'completed')
                ->pluck('session_day')
                ->map(fn($d) => (int)$d)
                ->toArray();
        }

        $daysPassed = 0;
        if ($enrollment->start_date) {
            $daysPassed = max(0, (int) $enrollment->start_date->diffInDays(now()->startOfDay()));
        }

        $completedCount = 0;
        $remainingCount = 0;
        $currentWeeklyMileage = (float) ($program->daniels_params['weekly_mileage'] ?? 25.0);

        foreach ($sessions as $s) {
            $day = (int) ($s['day'] ?? 0);
            if ($day <= $daysPassed || in_array($day, $completedDays, true)) {
                $completedCount++;
            } else {
                $remainingCount++;
            }
        }

        // Recommend volume based on new level & VDOT
        $recommendedMileage = $this->calculateOptimalMileage($newVdot, $currentWeeklyMileage, (string) ($program->distance_target ?? '10k'));

        // Readiness advisory
        $readiness = $this->evaluateReadiness($feeling, $vdotDiff);

        // Quality workout recommendations
        $quality = $this->generateQualityRecommendations($programVdot, $newVdot, (string) ($program->distance_target ?? '10k'), $newPaces);

        // Long run recommendations
        $longRun = $this->generateLongRunRecommendations($currentWeeklyMileage, $recommendedMileage, (string) ($program->distance_target ?? '10k'), $newPaces);

        // Strength & conditioning recommendations
        $strength = $this->generateStrengthRecommendations($programVdot, $newVdot);

        return [
            'has_active_program' => true,
            'enrollment_id' => $enrollment->id,
            'program_id' => $program->id,
            'program_title' => $program->title,
            'distance_target' => strtoupper((string) ($program->distance_target ?? '10k')),
            'current_vdot' => round($programVdot, 1),
            'new_vdot' => round($newVdot, 1),
            'vdot_diff' => $vdotDiff,
            'change_direction' => $vdotDiff > 0 ? 'increase' : ($vdotDiff < 0 ? 'decrease' : 'stable'),
            'old_level' => $oldLevel,
            'new_level' => $newLevel,
            'equivalent_race_times' => [
                '5k' => ['old' => $oldEquiv['5k']['time'] ?? '-', 'new' => $newEquiv['5k']['time'] ?? '-'],
                '10k' => ['old' => $oldEquiv['10k']['time'] ?? '-', 'new' => $newEquiv['10k']['time'] ?? '-'],
                '21k' => ['old' => $oldEquiv['21k']['time'] ?? '-', 'new' => $newEquiv['21k']['time'] ?? '-'],
                '42k' => ['old' => $oldEquiv['42k']['time'] ?? '-', 'new' => $newEquiv['42k']['time'] ?? '-'],
            ],
            'pillars' => [
                'pacing' => [
                    'easy' => [
                        'name' => 'Easy (E)',
                        'old' => $this->formatMinSec($oldPaces['E']),
                        'new' => $this->formatMinSec($newPaces['E']),
                        'purpose' => 'Pondasi aerobik & pemulihan aktif',
                    ],
                    'threshold' => [
                        'name' => 'Threshold (T)',
                        'old' => $this->formatMinSec($oldPaces['T']),
                        'new' => $this->formatMinSec($newPaces['T']),
                        'purpose' => 'Ambang laktat & daya tahan kecepatan',
                    ],
                    'interval' => [
                        'name' => 'Interval (I)',
                        'old' => $this->formatMinSec($oldPaces['I']),
                        'new' => $this->formatMinSec($newPaces['I']),
                        'purpose' => 'Kapasitas VO2Max & efisiensi kardio',
                    ],
                    'repetition' => [
                        'name' => 'Repetition (R)',
                        'old' => $this->formatMinSec($oldPaces['R']),
                        'new' => $this->formatMinSec($newPaces['R']),
                        'purpose' => 'Kecepatan neuromuskular & running economy',
                    ],
                ],
                'volume' => [
                    'current_km' => round($currentWeeklyMileage, 1),
                    'recommended_km' => round($recommendedMileage, 1),
                    'diff_km' => round($recommendedMileage - $currentWeeklyMileage, 1),
                    'rule' => '10% Safe Overload Rule',
                    'summary' => 'Volume latihan sisa minggu disesuaikan bertahap (maksimal kenaikan 7–10% per minggu) agar tendon dan sendi tidak mengalami stres berlebih.',
                ],
                'quality_speed' => $quality,
                'long_run' => $longRun,
                'strength' => $strength,
            ],
            'readiness' => $readiness,
            'completed_sessions_count' => $completedCount,
            'remaining_sessions_count' => $remainingCount,
            'can_adapt_program' => $remainingCount > 0 && abs($vdotDiff) >= 0.3,
        ];
    }

    /**
     * Apply adaptation to the runner's active program:
     * - Completed and past sessions remain completely unchanged.
     * - Future remaining sessions are recalculated with new pacing, progressive volume,
     *   updated speed variation main sets, and strength routines.
     */
    public function applyAdaptation(ProgramEnrollment $enrollment, float $newVdot, array $options = []): array
    {
        $program = $enrollment->program;
        if (! $program) {
            throw new \RuntimeException('Program tidak ditemukan.');
        }

        $programJson = $program->program_json ?? [];
        $sessions = $programJson['sessions'] ?? [];

        if (empty($sessions)) {
            throw new \RuntimeException('Sesi program latihan kosong.');
        }

        $completedDays = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
            ->where('status', 'completed')
            ->pluck('session_day')
            ->map(fn($d) => (int)$d)
            ->toArray();

        $daysPassed = 0;
        if ($enrollment->start_date) {
            $daysPassed = max(0, (int) $enrollment->start_date->diffInDays(now()->startOfDay()));
        }

        $oldVdot = (float) ($enrollment->current_vdot ?? ($program->vdot_score ?? 35.0));
        $newPaces = $this->daniels->calculateTrainingPaces($newVdot);

        $currentWeeklyMileage = (float) ($program->daniels_params['weekly_mileage'] ?? 25.0);
        $targetDistance = (string) ($program->distance_target ?? '10k');
        $optimalMileage = $this->calculateOptimalMileage($newVdot, $currentWeeklyMileage, $targetDistance);

        $adaptVolume = $options['adapt_volume'] ?? true;
        $adaptedCount = 0;

        // Group remaining sessions by week to apply progressive volume scaling
        $updatedSessions = [];
        $weekRemainingCounters = [];

        foreach ($sessions as $s) {
            $day = (int) ($s['day'] ?? 0);
            $isPast = ($day <= $daysPassed || in_array($day, $completedDays, true));

            // Past/Completed sessions are locked and strictly preserved
            if ($isPast) {
                $updatedSessions[] = $s;
                continue;
            }

            $week = (int) ($s['week'] ?? 1);
            $type = (string) ($s['type'] ?? 'rest');
            $phase = (string) ($s['phase'] ?? 'Build');

            // Calculate progressive week scale factor (10% ramp cap towards optimal)
            $weekOffset = max(0, $week - 1);
            $volumeRamp = $adaptVolume
                ? min(1.35, 1.0 + ($weekOffset * 0.08))
                : 1.0;

            if ($optimalMileage < $currentWeeklyMileage) {
                $volumeRamp = max(0.80, 1.0 - ($weekOffset * 0.05));
            }

            // Adapt based on workout type
            if ($type === 'easy_run' || $type === 'recovery_run') {
                $origDist = (float) ($s['distance'] ?? 4.0);
                $newDist = $adaptVolume ? round($origDist * $volumeRamp, 1) : $origDist;
                $newDist = max(3.0, min(15.0, $newDist));

                $paceMin = $newPaces['E'];
                $s['distance'] = $newDist;
                $s['target_pace'] = $this->formatMinSec($paceMin) . '/km';
                $s['duration'] = $this->builder->calculateDuration($newDist, $paceMin);
                $s['description'] = "Easy Run aerobik (VDOT {$newVdot}) — Jaga detak jantung di zona aerobik santai. Target: " . $this->formatMinSec($paceMin) . "/km.";
                $adaptedCount++;

            } elseif ($type === 'long_run') {
                $origDist = (float) ($s['distance'] ?? 8.0);
                $newDist = $adaptVolume ? round($origDist * $volumeRamp, 1) : $origDist;

                // Safe long run cap: max 30% of total weekly mileage or distance cap
                $maxCap = match(strtolower($targetDistance)) {
                    '42k' => 32.0,
                    '21k' => 21.0,
                    '10k' => 14.0,
                    default => 8.0,
                };
                $newDist = min($maxCap, max(4.0, $newDist));

                $paceMin = $newPaces['E'];
                $s['distance'] = $newDist;
                $s['target_pace'] = $this->formatMinSec($paceMin) . '/km';
                $s['duration'] = $this->builder->calculateDuration($newDist, $paceMin);
                $s['description'] = "Long Run Daya Tahan (VDOT {$newVdot}) — Membangun kapasitas cadangan glikogen & ketahanan kardiovaskular. Target: " . $this->formatMinSec($paceMin) . "/km.";
                $adaptedCount++;

            } elseif ($type === 'quality') {
                // Determine workout sub-type
                $workoutPaceKey = 'T';
                $workoutName = 'Threshold Tempo';
                $descLower = strtolower((string) ($s['description'] ?? ''));

                if (str_contains($descLower, 'interval') || str_contains($descLower, 'vo2max')) {
                    $workoutPaceKey = 'I';
                    $workoutName = 'VO2Max Interval';
                } elseif (str_contains($descLower, 'repetition') || str_contains($descLower, 'strides') || str_contains($descLower, 'speed')) {
                    $workoutPaceKey = 'R';
                    $workoutName = 'Speed Repetition';
                }

                $paceMin = $newPaces[$workoutPaceKey] ?? $newPaces['T'];
                $origDist = (float) ($s['distance'] ?? 6.0);
                $newDist = $adaptVolume ? round($origDist * $volumeRamp, 1) : $origDist;
                $newDist = max(4.0, min(16.0, $newDist));

                $s['distance'] = $newDist;
                $s['target_pace'] = $this->formatMinSec($paceMin) . '/km';
                $s['duration'] = $this->builder->calculateDuration($newDist, $paceMin);

                // Update workout details
                $s['description'] = "Sesi Kualitas: {$workoutName} (VDOT {$newVdot}) — Target pace: " . $this->formatMinSec($paceMin) . "/km. Pemanasan 1.5 km E, main set berkualitas, dan pendinginan 1.5 km E.";
                $adaptedCount++;

            } elseif ($type === 'strength') {
                // Adapt strength exercises according to new level
                $strengthRoutine = $this->getStrengthRoutineForLevel($newVdot);
                $s['description'] = $strengthRoutine['title'] . "\n" . implode("\n", array_map(fn($e) => "• {$e}", $strengthRoutine['exercises']));
                $adaptedCount++;
            }

            $updatedSessions[] = $s;
        }

        // Save back to DB
        $programJson['sessions'] = $updatedSessions;
        $program->program_json = $programJson;
        $program->vdot_score = $newVdot;

        // Record history log
        $rescheduleHistory = $enrollment->reschedule_history ?? [];
        $rescheduleHistory[] = [
            'type' => 'performance_checkin_adaptation',
            'timestamp' => now()->toIso8601String(),
            'old_vdot' => $oldVdot,
            'new_vdot' => $newVdot,
            'adapted_sessions_count' => $adaptedCount,
            'adapt_volume' => $adaptVolume,
            'feeling' => $options['feeling'] ?? 'good',
            'notes' => $options['notes'] ?? null,
        ];

        $enrollment->reschedule_history = $rescheduleHistory;
        $enrollment->current_vdot = $newVdot;

        $program->save();
        $enrollment->save();

        return [
            'success' => true,
            'adapted_count' => $adaptedCount,
            'new_vdot' => $newVdot,
            'message' => "Program aktif berhasil diadaptasi! {$adaptedCount} sesi masa depan telah diperbarui dengan target volume, speed, dan pacing terkini.",
        ];
    }

    /**
     * Calculate optimal weekly mileage based on VDOT and target distance
     */
    private function calculateOptimalMileage(float $vdot, float $currentMileage, string $distance): float
    {
        // Daniels recommended volume brackets
        $levelTarget = match (true) {
            $vdot >= 60.0 => 65.0, // Sub-Elite
            $vdot >= 50.0 => 50.0, // Advanced
            $vdot >= 40.0 => 38.0, // Intermediate
            $vdot >= 30.0 => 26.0, // Novice+
            default => 20.0,       // Beginner
        };

        // Distance modifier
        $distFactor = match (strtolower($distance)) {
            '42k' => 1.35,
            '21k' => 1.15,
            '10k' => 1.00,
            default => 0.85,
        };

        $ideal = $levelTarget * $distFactor;

        // Never jump more than 35% in a single adaptation cycle to prevent injury
        $maxAllowed = $currentMileage * 1.35;
        $minAllowed = $currentMileage * 0.75;

        return max($minAllowed, min($maxAllowed, $ideal));
    }

    /**
     * Evaluate athlete readiness and produce scientific advisory
     */
    private function evaluateReadiness(?string $feeling, float $vdotDiff): array
    {
        $feeling = $feeling ?: 'good';

        if (in_array($feeling, ['sore', 'tired', 'injured'], true)) {
            return [
                'feeling' => $feeling,
                'mode' => 'conservative',
                'badge' => 'Konservatif / Recovery Aware',
                'badge_color' => 'amber',
                'title' => 'Kondisi Fisik Memerlukan Kehati-hatian',
                'message' => 'Performa benchmark Anda mengalami perubahan, namun kondisi fisik hari ini terindikasi lelah atau tegang otot. Disarankan mengadopsi pacing baru secara bertahap dan menahan lonjakan volume latihan untuk 1 minggu ke depan.',
            ];
        }

        if ($feeling === 'strong') {
            return [
                'feeling' => $feeling,
                'mode' => 'progressive',
                'badge' => 'Progresif Penuh',
                'badge_color' => 'emerald',
                'title' => 'Kondisi Sangat Prima',
                'message' => 'Tingkat energi tinggi dan kesiapan fisik optimal. Tubuh siap menerima peningkatan volume bertahap dan variasi kecepatan yang lebih terstruktur.',
            ];
        }

        return [
            'feeling' => $feeling,
            'mode' => 'balanced',
            'badge' => 'Seimbang Terukur',
            'badge_color' => 'blue',
            'title' => 'Kondisi Bugar & Siap Latihan',
            'message' => 'Kondisi fisiologis siap menjalankan beban latihan sesuai target performa baru dengan kurva adaptasi teratur.',
        ];
    }

    /**
     * Generate quality workout variation recommendations
     */
    private function generateQualityRecommendations(float $oldVdot, float $newVdot, string $targetDistance, array $newPaces): array
    {
        $tPaceStr = $this->formatMinSec($newPaces['T']);
        $iPaceStr = $this->formatMinSec($newPaces['I']);
        $rPaceStr = $this->formatMinSec($newPaces['R']);

        $recommendation = match (true) {
            $newVdot >= 50.0 => [
                'type' => 'Advanced Mixed Intervals & Cruise Sets',
                'structure' => "• 4–5 x 1.5 km Threshold Run @ {$tPaceStr}/km (Rest 90s)\n• 5 x 1000m VO2Max Intervals @ {$iPaceStr}/km (Rest 2m)",
                'benefit' => 'Memaksimalkan ambang laktat dan kecepatan jelajah race-pace.',
            ],
            $newVdot >= 38.0 => [
                'type' => 'Structured VO2Max & Tempo Sets',
                'structure' => "• 4 x 1000m Interval @ {$iPaceStr}/km (Rest 2m jog)\n• 20–25 menit Continuous Tempo @ {$tPaceStr}/km\n• 6 x 200m Strides @ {$rPaceStr}/km",
                'benefit' => 'Mengembangkan efisiensi pernapasan dan adaptasi laktat pada kecepatan kompetitif.',
            ],
            default => [
                'type' => 'Introductory Repetitions & Easy Intervals',
                'structure' => "• 4–5 x 400m Repetitions @ {$rPaceStr}/km (Rest 200m walk/jog)\n• 15 menit Steady Tempo @ {$tPaceStr}/km",
                'benefit' => 'Membangun mekanika lari alami tanpa akumulasi asam laktat yang berlebihan.',
            ],
        };

        return [
            'title' => 'Variasi Speed & Quality Workout',
            'workout_type' => $recommendation['type'],
            'interval_pace' => $iPaceStr . '/km',
            'threshold_pace' => $tPaceStr . '/km',
            'repetition_pace' => $rPaceStr . '/km',
            'structure' => $recommendation['structure'],
            'benefit' => $recommendation['benefit'],
        ];
    }

    /**
     * Generate long run recommendations
     */
    private function generateLongRunRecommendations(float $currentMileage, float $recommendedMileage, string $targetDistance, array $newPaces): array
    {
        $maxRatio = 0.28; // Jack Daniels: 25-30% of weekly volume
        $recommendedLongRun = round($recommendedMileage * $maxRatio, 1);

        $floor = match(strtolower($targetDistance)) {
            '42k' => 18.0,
            '21k' => 12.0,
            '10k' => 7.0,
            default => 5.0,
        };

        $finalLongRun = max($floor, $recommendedLongRun);
        $ePace = $this->formatMinSec($newPaces['E']);

        return [
            'title' => 'Jarak & Batas Long Run',
            'recommended_distance_km' => $finalLongRun,
            'target_pace' => $ePace . '/km',
            'rule' => 'Maksimal 25–30% dari Total Weekly Volume',
            'summary' => "Jarak long run diproyeksikan pada kisaran {$finalLongRun} km dengan target pace aerobik santai {$ePace}/km (RPE 3–4).",
        ];
    }

    /**
     * Generate strength training recommendations based on speed demands
     */
    private function generateStrengthRecommendations(float $oldVdot, float $newVdot): array
    {
        $routine = $this->getStrengthRoutineForLevel($newVdot);

        return [
            'title' => 'Penguatan Otot & Stabilitas Sendi',
            'focus_area' => $routine['focus'],
            'summary' => $routine['rationale'],
            'exercises' => $routine['exercises'],
        ];
    }

    /**
     * Return strength routine array based on VDOT level
     */
    private function getStrengthRoutineForLevel(float $vdot): array
    {
        if ($vdot >= 50.0) {
            return [
                'title' => 'Fungsional Lanjutan & Tendon Elastic Recoil (Advanced)',
                'focus' => 'Tendon Stiffness & Rate of Force Development (RFD)',
                'rationale' => 'Pace tinggi menghasilkan gaya benturan 3.5x berat badan. Memerlukan kekakuan tendon achilles dan stabilitas sendi pinggul optimal.',
                'exercises' => [
                    'Pogo Hops & Ankle Bounding (3 set x 20 repetisi)',
                    'Bulgarian Split Squat dengan beban (3 set x 8-10 per kaki)',
                    'Single-Leg Romanian Deadlift (3 set x 10 per kaki)',
                    'Standing Weighted Calf Raises (3 set x 15 repetisi)',
                    'Pallof Press & Anti-Rotation Core (3 set x 30 detik)',
                ],
            ];
        }

        if ($vdot >= 36.0) {
            return [
                'title' => 'Stabilitas Unilateral & Penguatan Ekstremitas Bawah (Intermediate)',
                'focus' => 'Keseimbangan Glute Medius & Eksentrik Hamstring',
                'rationale' => 'Mengontrol rotasi panggul saat fase tumpuan tunggal dan melindungi sendi lutut dari stres impak berulang.',
                'exercises' => [
                    'Single-Leg Calf Raises (3 set x 12 per kaki)',
                    'Walking Lunges (3 set x 12 langkah per kaki)',
                    'Nordic / Eccentric Hamstring Sliders (3 set x 8 repetisi)',
                    'Side Plank dengan Hip Abduction (3 set x 20-30 detik)',
                    'Glute Bridge 1 Kaki (3 set x 12 per kaki)',
                ],
            ];
        }

        return [
            'title' => 'Fondasi Postur & Stabilitas Dasar (Beginner)',
            'focus' => 'Aktivasi Otot Glute & Penguatan Otot Inti (Core)',
            'rationale' => 'Membangun ketahanan postural agar atlet tidak membungkuk saat kelelahan di akhir sesi lari.',
            'exercises' => [
                'Bodyweight Squats dengan tempo terkontrol (3 set x 12 repetisi)',
                'Standard Plank & Bird Dog (3 set x 30 detik)',
                'Glute Bridges 2 Kaki (3 set x 15 repetisi)',
                'Calf Raises kedua kaki (3 set x 15 repetisi)',
                'Clamshells dengan mini resistance band (3 set x 15 per sisi)',
            ],
        ];
    }

    /**
     * Get label and color badge for runner level
     */
    private function getLevelData(float $vdot): array
    {
        return match (true) {
            $vdot >= 75.0 => ['name' => 'Elite', 'color' => 'yellow'],
            $vdot >= 60.0 => ['name' => 'Sub-Elite', 'color' => 'purple'],
            $vdot >= 50.0 => ['name' => 'Advanced', 'color' => 'orange'],
            $vdot >= 40.0 => ['name' => 'Intermediate', 'color' => 'blue'],
            $vdot >= 30.0 => ['name' => 'Beginner+', 'color' => 'green'],
            default => ['name' => 'Beginner', 'color' => 'slate'],
        };
    }

    /**
     * Format decimal minutes to MM:SS string
     */
    private function formatMinSec(float $decimalMinutes): string
    {
        $m = floor($decimalMinutes);
        $s = round(($decimalMinutes - $m) * 60);
        if ($s >= 60) {
            $m++;
            $s = 0;
        }
        return sprintf('%d:%02d', $m, $s);
    }
}
