<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * ProgramBuilderService
 *
 * Builds periodized running training programs using Jack Daniels' VDOT formula.
 * Consolidates shared logic from SelfGeneratedProgramController and GenerateProgramController.
 *
 * Key improvements over the legacy hardcoded approach:
 * - Progressive mileage build-up (respects 10% rule)
 * - Gradual long run progression with per-distance caps
 * - Ordered workout selection by difficulty (not random)
 * - Smart taper that varies by target distance
 * - Phase-aware and difficulty-progressive workout cycling
 */
class ProgramBuilderService
{
    protected DanielsRunningService $danielsService;

    public function __construct(DanielsRunningService $danielsService)
    {
        $this->danielsService = $danielsService;
    }

    /**
     * Build a periodized running program.
     */
    public function build(array $config): array
    {
        $targetDistance = $config['target_distance'] ?? '10k';

        if ($targetDistance === 'cooper12') {
            $library = $this->loadLibrary();
            return $this->buildCooper12Program($config, $library);
        }

        return $this->buildPeriodizedProgram($config);
    }

    /**
     * Build a periodized program with progressive overload, ordered workout selection,
     * and smart taper — replacing the legacy flat/random approach.
     */
    private function buildPeriodizedProgram(array $config): array
    {
        $weeks = $config['weeks'];
        $frequency = $config['frequency'];
        $targetMileage = $config['weekly_mileage'];
        $initialVdot = $config['initial_vdot'];
        $targetVdot = $config['target_vdot'];
        $targetDistance = $config['target_distance'] ?? '10k';
        $runnerLevel = $config['runner_level'] ?? 'intermediate';
        $longRunDay = $config['long_run_day'] ?? 'sunday';
        $isTropical = $config['is_tropical'] ?? false;

        $library = $this->loadLibrary();

        // Adjust training frequency by level
        $levelRules = $library['level_rules'][$runnerLevel] ?? [
            'max_quality_sessions_per_week' => 1,
            'volume_adjustment' => 1.00,
            'max_frequency' => 5,
        ];
        $maxFrequency = $levelRules['max_frequency'] ?? 5;
        $frequency = min($frequency, $maxFrequency);

        $volumeFactor = $levelRules['volume_adjustment'] ?? 1.00;
        $maxQualitySessions = $levelRules['max_quality_sessions_per_week'] ?? 1;

        $longRunDayIndex = $longRunDay === 'saturday' ? 6 : 7;

        // Map target distance to goal categories
        $distanceMap = [
            '5k' => '5K', '10k' => '10K',
            '21k' => 'HALF_MARATHON', '42k' => 'FULL_MARATHON',
        ];
        $goal = $distanceMap[strtolower($targetDistance)] ?? '10K';

        // ===== PHASE CALCULATION =====
        $phases = $this->calculatePhases($weeks, $targetDistance);
        $taperConfig = $library['taper_config'][strtolower($targetDistance)] ?? ['weeks' => 1, 'factors' => [0.50]];
        $taperWeeks = $taperConfig['weeks'];

        // ===== VDOT PROGRESSION =====
        $deltaVdot = $targetVdot - $initialVdot;

        // ===== MILEAGE PROGRESSION =====
        $mileageSchedule = $this->buildMileageSchedule($weeks, $targetMileage, $taperConfig, $runnerLevel);

        // ===== LONG RUN PROGRESSION =====
        $longRunCaps = $library['long_run_caps'][strtolower($targetDistance)] ?? ['max_km' => 20, 'max_ratio' => 0.35];

        // ===== WORKOUT ORDERING =====
        // Get all workouts sorted by difficulty_order for ordered cycling
        $allWorkouts = $library['goals'][$goal] ?? [];
        $workoutCycleIndex = 0; // Global counter for ordered cycling

        $sessions = [];
        $dayCount = 1;

        for ($w = 1; $w <= $weeks; $w++) {
            // Determine current phase
            $phase = $this->getPhaseForWeek($w, $phases, $weeks, $taperWeeks);

            // Calculate current VDOT for this week (progressive improvement)
            $currentVdot = $this->interpolateVdot($w, $weeks, $initialVdot, $targetVdot, $phases);

            // Training paces for this week's VDOT
            $paces = $this->danielsService->calculateTrainingPaces($currentVdot);
            if ($isTropical) {
                $paces['E'] *= 1.05;
                $paces['M'] *= 1.05;
                $paces['T'] *= 1.04;
                $paces['I'] *= 1.03;
                $paces['R'] *= 1.02;
            }

            // Current week's mileage from progressive schedule
            $currentMileage = $mileageSchedule[$w - 1];

            // Smart deload: only during Base/Strength, never during Taper
            $isDeload = ($w % 4 === 0) && !in_array($phase, ['Taper']);
            if ($isDeload) {
                $currentMileage *= 0.80;
            }

            // ===== LONG RUN DISTANCE (progressive) =====
            $longRunDistance = $this->calculateLongRunForWeek(
                $w, $weeks, $currentMileage, $targetDistance, $longRunCaps, $phase, $runnerLevel, $taperWeeks
            );

            // Mileage warning
            $mileageWarning = ($longRunDistance > $currentMileage * 0.40);

            // ===== QUALITY WORKOUT SELECTION (ordered, not random) =====
            $weeklyQualityCount = $maxQualitySessions;

            // Professional Coaching Rules for Quality Session Counts:
            if ($w === 1) {
                // Week 1: Beginner gets 0 quality sessions (100% aerobic base). Intermediate/Advanced gets max 1.
                $weeklyQualityCount = ($runnerLevel === 'beginner') ? 0 : min($weeklyQualityCount, 1);
            } elseif ($phase === 'Base') {
                // Base phase: Cap quality sessions at max 1 per week to build aerobic foundation safely
                $weeklyQualityCount = min($weeklyQualityCount, 1);
            }

            if ($isDeload && $weeklyQualityCount > 1) {
                $weeklyQualityCount = 1;
            }
            if ($phase === 'Taper') {
                $weeklyQualityCount = min($weeklyQualityCount, 1);
            }

            $weekQualityWorkouts = [];
            for ($q = 0; $q < $weeklyQualityCount; $q++) {
                $workout = $this->getOrderedWorkoutForPhase(
                    $goal, $phase, $library, $allWorkouts, $workoutCycleIndex
                );
                if ($workout) {
                    $weekQualityWorkouts[] = $workout;
                    $workoutCycleIndex++;
                }
            }

            // ===== DAY ASSIGNMENTS =====
            $dayAssignments = array_fill(1, 7, ['type' => 'rest', 'workout' => null]);

            // Step 1: Long Run Day
            $dayAssignments[$longRunDayIndex] = ['type' => 'long_run', 'workout' => null];

            // Step 2: Quality Days
            if (count($weekQualityWorkouts) === 1) {
                $dayAssignments[3] = ['type' => 'quality', 'workout' => $weekQualityWorkouts[0]];
            } elseif (count($weekQualityWorkouts) === 2) {
                $dayAssignments[2] = ['type' => 'quality', 'workout' => $weekQualityWorkouts[0]];
                $dayAssignments[4] = ['type' => 'quality', 'workout' => $weekQualityWorkouts[1]];
            }

            // Step 3: Easy and Recovery Days
            $runningDaysCount = 1 + count($weekQualityWorkouts);
            $easyDaysNeeded = max(0, $frequency - $runningDaysCount);

            $candidateDays = [1, 5, 6, 7, 2, 3, 4];
            $assignedEasyCount = 0;
            foreach ($candidateDays as $d) {
                if ($assignedEasyCount >= $easyDaysNeeded) break;
                if ($dayAssignments[$d]['type'] === 'rest') {
                    $prevDay = ($d == 1) ? 7 : $d - 1;
                    $isAfterQuality = ($dayAssignments[$prevDay]['type'] === 'quality');
                    $easyType = ($isDeload || $isAfterQuality) ? 'recovery_run' : 'easy_run';
                    $dayAssignments[$d] = ['type' => $easyType, 'workout' => null];
                    $assignedEasyCount++;
                }
            }

            // ===== CALCULATE DISTANCES =====
            $qualityDistances = [];
            foreach ($dayAssignments as $d => $assign) {
                if ($assign['type'] === 'quality') {
                    $workout = $assign['workout'];
                    $scaledMainSet = $this->scaleWorkoutVolume($workout['main_set'], $volumeFactor);
                    $mainSetDist = $this->calculateMainSetDistance($scaledMainSet);
                    $totalQDist = round(2.0 + $mainSetDist + 1.5, 1);
                    $qualityDistances[$d] = $totalQDist;
                }
            }

            $totalAssignedHardDist = $longRunDistance + array_sum($qualityDistances);
            $easyPool = max(0, $currentMileage - $totalAssignedHardDist);

            $easyDaysCount = 0;
            foreach ($dayAssignments as $assign) {
                if ($assign['type'] === 'easy_run' || $assign['type'] === 'recovery_run') {
                    $easyDaysCount++;
                }
            }

            $easyDistance = $easyDaysCount > 0 ? round($easyPool / $easyDaysCount, 1) : 0;
            if ($easyDistance < 3.0 && $easyDaysCount > 0 && $easyPool > 0) {
                $easyDistance = 3.0;
            } elseif ($easyDistance > 15.0) {
                $easyDistance = 15.0;
            }

            // ===== CONSTRUCT SESSIONS =====
            for ($d = 1; $d <= 7; $d++) {
                $assignment = $dayAssignments[$d];
                $session = [
                    'day' => $dayCount++,
                    'week' => $w,
                    'phase' => $phase,
                    'is_deload' => $isDeload,
                    'type' => 'rest',
                    'description' => 'Rest Day',
                    'distance' => 0,
                    'duration' => null,
                    'target_pace' => null,
                ];

                if ($assignment['type'] === 'long_run') {
                    $session['type'] = 'long_run';
                    $session['distance'] = $longRunDistance;
                    $session['target_pace'] = $this->formatPace($paces['E']);
                    $session['duration'] = $this->calculateDuration($longRunDistance, $paces['E']);

                    $paceFast = max(0, $paces['E'] - (5 / 60));
                    $paceSlow = $paces['E'] + (10 / 60);
                    $rangeStr = sprintf(
                        '%d:%02d - %d:%02d/km',
                        floor($paceFast), round(($paceFast - floor($paceFast)) * 60),
                        floor($paceSlow), round(($paceSlow - floor($paceSlow)) * 60)
                    );

                    $session['description'] = $isDeload
                        ? "Long Easy Run (De-load) - Berlari santai dengan volume dikurangi untuk pemulihan.\nTarget: $rangeStr (RPE 3-4)"
                        : "Long Easy Run - Fokus pada daya tahan kardio.\nTarget: $rangeStr (RPE 3-4)";

                    if ($mileageWarning) {
                        $session['description'] .= "\n\n[WARNING: Weekly mileage Anda terlalu rendah untuk mengadaptasi jarak lari ini dengan optimal. Kami telah memaksakan batas minimal untuk Long Run ini.]";
                    }
                } elseif ($assignment['type'] === 'quality') {
                    $workout = $assignment['workout'];
                    $scaledMainSet = $this->scaleWorkoutVolume($workout['main_set'], $volumeFactor);

                    $workoutPaceKey = 'E';
                    $rpe = '3-4';
                    if ($workout['type'] === 'interval') {
                        $workoutPaceKey = 'I'; $rpe = '9-10';
                    } elseif ($workout['type'] === 'threshold' || $workout['type'] === 'progression') {
                        $workoutPaceKey = 'T'; $rpe = '7-8';
                    } elseif ($workout['type'] === 'marathon_pace') {
                        $workoutPaceKey = 'M'; $rpe = '5-6';
                    } elseif ($workout['type'] === 'repetition') {
                        $workoutPaceKey = 'R'; $rpe = '9-10';
                    } elseif ($workout['type'] === 'hill') {
                        $workoutPaceKey = 'R'; $rpe = '8-9';
                    }

                    $targetPaceStr = $this->formatPace($paces[$workoutPaceKey]);
                    $session['duration'] = $this->calculateDuration($qualityDistances[$d], $paces[$workoutPaceKey]);

                    $paceFast = max(0, $paces[$workoutPaceKey] - (5 / 60));
                    $paceSlow = $paces[$workoutPaceKey] + (10 / 60);
                    $rangeStr = sprintf(
                        '%d:%02d - %d:%02d/km',
                        floor($paceFast), round(($paceFast - floor($paceFast)) * 60),
                        floor($paceSlow), round(($paceSlow - floor($paceSlow)) * 60)
                    );

                    $mainSetText = str_replace(
                        ['{distance_km}', '{target_pace}'],
                        [$qualityDistances[$d], $rangeStr . " (RPE $rpe)"],
                        $scaledMainSet
                    );

                    $warmUpText = $library['default_warm_up'] ?? '10 to 15 min easy run + dynamic drills';
                    $coolDownText = $library['default_cool_down'] ?? '10 min easy jog';

                    $descriptionLines = [
                        "Warm Up: " . $warmUpText,
                        "Main Set: " . $mainSetText,
                        "Recovery: " . $workout['recovery'],
                        "Cool Down: " . $coolDownText,
                        "Intensity: " . ($workout['intensity'] ?? "Target pace: $rangeStr (RPE $rpe)"),
                        "Reason: " . ($workout['best_for'] ?? $workout['focus'] ?? 'Meningkatkan performa lari') . " (" . ($workout['focus'] ?? '') . ")",
                    ];

                    $session['type'] = $workout['type'];
                    $session['distance'] = $qualityDistances[$d];
                    $session['target_pace'] = $targetPaceStr;
                    $session['description'] = implode("\n", $descriptionLines);
                    $session['workout_id'] = $workout['id'];
                    $session['workout_name'] = $workout['name'];
                } elseif ($assignment['type'] === 'easy_run' || $assignment['type'] === 'recovery_run') {
                    $session['type'] = $assignment['type'];
                    $session['distance'] = $easyDistance;
                    $session['target_pace'] = $this->formatPace($paces['E']);
                    $session['duration'] = $this->calculateDuration($easyDistance, $paces['E']);

                    $paceFast = max(0, $paces['E'] - (5 / 60));
                    $paceSlow = $paces['E'] + (10 / 60);
                    $rangeStr = sprintf(
                        '%d:%02d - %d:%02d/km',
                        floor($paceFast), round(($paceFast - floor($paceFast)) * 60),
                        floor($paceSlow), round(($paceSlow - floor($paceSlow)) * 60)
                    );

                    $session['description'] = $assignment['type'] === 'recovery_run'
                        ? ($isDeload ? "Recovery Run (De-load) - Lari pemulihan sangat santai.\nTarget: $rangeStr (RPE < 3)" : "Recovery Run - Membantu pemulihan otot pasca latihan keras.\nTarget: $rangeStr (RPE < 3)")
                        : "Easy Aerobic Run - Membangun daya tahan dasar.\nTarget: $rangeStr (RPE 3-4)";
                } else {
                    // Rest day
                    $session['duration'] = '00:00:00';
                    if ($runnerLevel === 'beginner') {
                        $session['description'] = 'Rest Day - Istirahat total atau peregangan otot ringan untuk pemulihan.';
                    } elseif ($runnerLevel === 'intermediate') {
                        $session['description'] = 'Active Recovery - Peregangan ringan, foam rolling, atau mobilitas sendi.';
                    } else {
                        $session['description'] = 'Active Recovery - Latihan kekuatan core ringan (plank, bird-dog) & foam rolling.';
                    }
                }

                $sessions[] = $session;
            }
        }

        return [
            'sessions' => $sessions,
            'summary' => [
                'total_weeks' => $weeks,
                'target' => strtoupper($config['target_distance']),
                'vdot' => round($initialVdot, 1),
                'target_vdot' => round($targetVdot, 1),
            ],
        ];
    }

    // =========================================================================
    // PROGRESSIVE MILEAGE BUILD-UP
    // =========================================================================

    /**
     * Build a weekly mileage schedule with progressive build-up and smart taper.
     *
     * Strategy:
     * - Start at ~75% of target mileage (beginner: 65%, advanced: 80%)
     * - Build up gradually over the training weeks (respecting ~7-10% per week)
     * - Peak mileage is reached 2-3 weeks before taper starts
     * - Taper uses per-distance factors from workout_library
     *
     * @return float[] Array of weekly mileage values (0-indexed)
     */
    private function buildMileageSchedule(int $totalWeeks, float $targetMileage, array $taperConfig, string $runnerLevel): array
    {
        $taperWeeks = $taperConfig['weeks'];
        $taperFactors = $taperConfig['factors'];

        // Starting mileage percentage based on level
        $startPercent = match ($runnerLevel) {
            'beginner' => 0.65,
            'advanced' => 0.80,
            default => 0.75,
        };

        $startMileage = round($targetMileage * $startPercent, 1);
        $buildWeeks = max(1, $totalWeeks - $taperWeeks);
        $peakWeek = $buildWeeks; // Last build week = peak

        $schedule = [];

        for ($w = 1; $w <= $totalWeeks; $w++) {
            if ($w > $buildWeeks) {
                // TAPER phase
                $taperIndex = $w - $buildWeeks - 1;
                $factor = $taperFactors[$taperIndex] ?? end($taperFactors);
                $schedule[] = round($targetMileage * $factor, 1);
            } else {
                // BUILD phase — smooth progression from start to target
                if ($buildWeeks <= 1) {
                    $weekMileage = $targetMileage;
                } else {
                    // Use a smooth curve: linear interpolation with a slight ease-in
                    $progress = ($w - 1) / ($buildWeeks - 1); // 0 → 1
                    // Ease-in curve: slightly slower start, faster ramp at end
                    $easedProgress = pow($progress, 0.85);
                    $weekMileage = $startMileage + ($targetMileage - $startMileage) * $easedProgress;
                }

                // Enforce max 10% increase from previous week
                if ($w > 1 && count($schedule) > 0) {
                    $prevMileage = $schedule[$w - 2];
                    $maxAllowed = $prevMileage * 1.10;
                    $weekMileage = min($weekMileage, $maxAllowed);
                }

                $schedule[] = round($weekMileage, 1);
            }
        }

        return $schedule;
    }

    // =========================================================================
    // LONG RUN PROGRESSION
    // =========================================================================

    /**
     * Calculate long run distance for a specific week with progressive build-up.
     *
     * - Starts at ~60% of the target long run distance
     * - Peaks 2-3 weeks before race
     * - Respects per-distance caps (max_km, max_ratio)
     * - Reduces during taper
     */
    private function calculateLongRunForWeek(
        int $week, int $totalWeeks, float $weeklyMileage,
        string $targetDistance, array $longRunCaps, string $phase,
        string $runnerLevel, int $taperWeeks
    ): float {
        $maxKm = $longRunCaps['max_km'];
        $maxRatio = $longRunCaps['max_ratio'];
        $minLongRunFloor = $this->getMinLongRunFloor($targetDistance);

        // Calculate the peak long run distance (capped)
        $peakLongRun = min($maxKm, round($weeklyMileage * $maxRatio, 1));

        // Runner level adjustment
        if ($runnerLevel === 'beginner') {
            $peakLongRun = round($peakLongRun * 0.85, 1);
        } elseif ($runnerLevel === 'advanced') {
            $peakLongRun = round($peakLongRun * 1.10, 1);
            $peakLongRun = min($peakLongRun, $maxKm); // Still respect cap
        }

        if ($phase === 'Taper') {
            // During taper, long run is reduced
            $buildWeeks = $totalWeeks - $taperWeeks;
            $weeksIntoTaper = $week - $buildWeeks;
            $taperReduction = 0.5 + (0.15 * max(0, $taperWeeks - $weeksIntoTaper));
            return round(max($minLongRunFloor * 0.6, $peakLongRun * min(1.0, $taperReduction)), 1);
        }

        // Progressive build: start at 60% of peak, build to 100%
        $buildWeeks = max(1, $totalWeeks - $taperWeeks);
        $progress = min(1.0, ($week - 1) / max(1, $buildWeeks - 1));
        // Ease-in curve for gradual increase
        $easedProgress = pow($progress, 0.8);
        $startLongRun = max($minLongRunFloor, round($peakLongRun * 0.60, 1));

        $longRunDistance = round($startLongRun + ($peakLongRun - $startLongRun) * $easedProgress, 1);

        // Never exceed cap
        $longRunDistance = min($longRunDistance, $maxKm);
        // Never exceed ratio of this week's mileage
        $longRunDistance = min($longRunDistance, round($weeklyMileage * $maxRatio, 1));

        // Floor enforcement
        if ($longRunDistance < $minLongRunFloor) {
            $longRunDistance = $minLongRunFloor;
        }

        return $longRunDistance;
    }

    private function getMinLongRunFloor(string $targetDistance): float
    {
        return match (strtolower($targetDistance)) {
            '42k' => 18.0,
            '21k' => 12.0,
            '10k' => 8.0,
            '5k' => 5.0,
            default => 5.0,
        };
    }

    // =========================================================================
    // PHASE CALCULATION
    // =========================================================================

    /**
     * Calculate phase boundaries based on target distance and total weeks.
     *
     * Returns: ['base' => weeks, 'strength' => weeks, 'speed' => weeks]
     * Taper is handled separately from taper_config.
     */
    private function calculatePhases(int $totalWeeks, string $targetDistance): array
    {
        // Taper weeks are taken from config, remaining weeks are split among Base/Strength/Speed
        $library = $this->loadLibrary();
        $taperConfig = $library['taper_config'][strtolower($targetDistance)] ?? ['weeks' => 1, 'factors' => [0.50]];
        $taperWeeks = $taperConfig['weeks'];
        $trainingWeeks = max(3, $totalWeeks - $taperWeeks);

        // Phase ratios per distance
        if ($targetDistance === '42k') {
            $baseRatio = 0.40; $strengthRatio = 0.30; $speedRatio = 0.30;
        } elseif ($targetDistance === '21k') {
            $baseRatio = 0.30; $strengthRatio = 0.30; $speedRatio = 0.40;
        } else { // 5k, 10k
            $baseRatio = 0.25; $strengthRatio = 0.30; $speedRatio = 0.45;
        }

        $baseWeeks = max(1, (int) round($trainingWeeks * $baseRatio));
        $strengthWeeks = max(1, (int) round($trainingWeeks * $strengthRatio));
        $speedWeeks = max(1, $trainingWeeks - $baseWeeks - $strengthWeeks);

        return [
            'base' => $baseWeeks,
            'strength' => $strengthWeeks,
            'speed' => $speedWeeks,
        ];
    }

    /**
     * Determine the training phase for a given week.
     */
    private function getPhaseForWeek(int $week, array $phases, int $totalWeeks, int $taperWeeks): string
    {
        $baseEnd = $phases['base'];
        $strengthEnd = $baseEnd + $phases['strength'];
        $speedEnd = $strengthEnd + $phases['speed'];

        if ($week <= $baseEnd) {
            return 'Base';
        } elseif ($week <= $strengthEnd) {
            return 'Strength';
        } elseif ($week <= $speedEnd) {
            return 'Speed';
        } else {
            return 'Taper';
        }
    }

    /**
     * Interpolate VDOT for a given week across the training phases.
     */
    private function interpolateVdot(int $week, int $totalWeeks, float $initialVdot, float $targetVdot, array $phases): float
    {
        $deltaVdot = $targetVdot - $initialVdot;
        $baseEnd = $phases['base'];
        $strengthEnd = $baseEnd + $phases['strength'];
        $speedEnd = $strengthEnd + $phases['speed'];

        if ($week <= $baseEnd) {
            // Base: minimal VDOT improvement (0-20% of delta)
            $t = $week / max(1, $baseEnd);
            return $initialVdot + ($deltaVdot * 0.20 * $t);
        } elseif ($week <= $strengthEnd) {
            // Strength: 20-60% of delta
            $t = ($week - $baseEnd) / max(1, $phases['strength']);
            return $initialVdot + ($deltaVdot * 0.20) + ($deltaVdot * 0.40 * $t);
        } elseif ($week <= $speedEnd) {
            // Speed: 60-100% of delta
            $t = ($week - $strengthEnd) / max(1, $phases['speed']);
            return $initialVdot + ($deltaVdot * 0.60) + ($deltaVdot * 0.40 * $t);
        } else {
            // Taper: target VDOT
            return $targetVdot;
        }
    }

    // =========================================================================
    // ORDERED WORKOUT SELECTION
    // =========================================================================

    /**
     * Select a workout using ordered cycling instead of random selection.
     *
     * Strategy:
     * 1. Filter workouts by phase preference
     * 2. Sort by difficulty_order
     * 3. Cycle through in order using the global index
     *
     * This ensures progressive difficulty and no random repetition.
     */
    private function getOrderedWorkoutForPhase(
        string $goal, string $phase, array $library, array $allWorkouts, int $cycleIndex
    ): ?array {
        if (empty($allWorkouts)) {
            return null;
        }

        // Filter by phase preference first
        $phaseMatched = array_filter($allWorkouts, function ($w) use ($phase) {
            $prefs = $w['phase_preference'] ?? [];
            return in_array($phase, $prefs, true);
        });

        // Fallback: use training_phase_rules preferred_types
        if (empty($phaseMatched)) {
            $phaseRule = $library['training_phase_rules'][strtolower($phase)] ?? null;
            $preferredTypes = $phaseRule['preferred_types'] ?? [];

            $phaseMatched = array_filter($allWorkouts, function ($w) use ($preferredTypes) {
                return in_array($w['type'], $preferredTypes, true);
            });
        }

        $pool = !empty($phaseMatched) ? array_values($phaseMatched) : $allWorkouts;

        // Base Phase Coaching Guardrail: Exclude heavy VO2Max intervals during Base phase if non-interval workouts exist
        if ($phase === 'Base') {
            $nonIntervals = array_filter($pool, function ($w) {
                return ($w['type'] ?? '') !== 'interval';
            });
            if (!empty($nonIntervals)) {
                $pool = array_values($nonIntervals);
            }
        }

        // Sort by difficulty_order
        usort($pool, function ($a, $b) {
            return ($a['difficulty_order'] ?? 99) <=> ($b['difficulty_order'] ?? 99);
        });

        // Cycle through ordered pool
        $index = $cycleIndex % count($pool);
        return $pool[$index];
    }

    // =========================================================================
    // HELPER METHODS (migrated from controllers)
    // =========================================================================

    public function scaleWorkoutVolume(string $mainSet, float $factor): string
    {
        if ($factor >= 1.0) {
            return $mainSet;
        }

        if (preg_match('/^(\d+)\s*reps\s*x\s*(.+)$/i', $mainSet, $matches)) {
            $reps = (int) $matches[1];
            $scaledReps = max(1, (int) round($reps * $factor));
            return $scaledReps . ' reps x ' . $matches[2];
        }

        if (preg_match('/^(.*?)(\d+)\s*x\s*(\d+(?:\.\d+)?\s*[Km])(.*?)$/i', $mainSet, $matches)) {
            $reps = (int) $matches[2];
            $scaledReps = max(1, (int) round($reps * $factor));
            return $matches[1] . $scaledReps . ' x ' . $matches[3] . $matches[4];
        }

        $scaled = preg_replace_callback('/(\d+(?:\.\d+)?)\s*(K|m|meters|K\b)/i', function ($m) use ($factor) {
            $val = (float) $m[1];
            $scaledVal = round($val * $factor, 1);
            if ($scaledVal == (int) $scaledVal) {
                $scaledVal = (int) $scaledVal;
            }
            return $scaledVal . $m[2];
        }, $mainSet);

        return $scaled;
    }

    public function calculateMainSetDistance(string $mainSet): float
    {
        if (preg_match('/(\d+)\s*reps\s*x\s*(\d+(?:\.\d+)?)\s*(K|m|meters)/i', $mainSet, $matches)) {
            $reps = (float) $matches[1];
            $val = (float) $matches[2];
            $unit = strtolower($matches[3]);
            if ($unit === 'm' || $unit === 'meters') {
                return ($reps * $val) / 1000.0;
            }
            return $reps * $val;
        }

        if (preg_match('/(\d+)\s*x\s*(\d+(?:\.\d+)?)\s*(K|m|meters)/i', $mainSet, $matches)) {
            $reps = (float) $matches[1];
            $val = (float) $matches[2];
            $unit = strtolower($matches[3]);
            if ($unit === 'm' || $unit === 'meters') {
                return ($reps * $val) / 1000.0;
            }
            return $reps * $val;
        }

        if (preg_match_all('/(\d+(?:\.\d+)?)\s*(K|m|meters)/i', $mainSet, $matches, PREG_SET_ORDER)) {
            $total = 0.0;
            foreach ($matches as $match) {
                $val = (float) $match[1];
                $unit = strtolower($match[2]);
                if ($unit === 'm' || $unit === 'meters') {
                    $total += $val / 1000.0;
                } else {
                    $total += $val;
                }
            }
            if ($total > 0) {
                return $total;
            }
        }

        return 5.0;
    }

    public function formatPace(float $minPerKm): string
    {
        $m = floor($minPerKm);
        $s = round(($minPerKm - $m) * 60);
        return sprintf('@ %d:%02d/km', $m, $s);
    }

    public function calculateDuration(float $distanceKm, float $paceMinPerKm): string
    {
        $totalSeconds = round($distanceKm * $paceMinPerKm * 60);
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    // =========================================================================
    // COOPER 12-MIN PROGRAM (kept as-is from original)
    // =========================================================================

    private function buildCooper12Program(array $config, array $library): array
    {
        $weeks = $config['weeks'];
        $frequency = $config['frequency'];
        $mileage = $config['weekly_mileage'];
        $runnerLevel = $config['runner_level'] ?? 'intermediate';
        $longRunDay = $config['long_run_day'] ?? 'sunday';

        $hardSessionsCount = 1;
        if ($runnerLevel === 'intermediate') {
            $hardSessionsCount = rand(1, 2);
        } elseif ($runnerLevel === 'advanced') {
            $hardSessionsCount = 2;
        }

        $cooperWorkouts = collect($library['COOPER_12_MIN_GENERAL'] ?? [])->keyBy('id');

        $sessions = [];
        $dayCount = 1;
        $totalDistance = 0;
        $totalSessions = 0;
        $maxLongRun = 0;
        $peakMileage = 0;

        $longRunDayIndex = $longRunDay === 'saturday' ? 6 : 7;

        // 1-based index mapping for 8-week cycle
        $primaryWorkouts = [
            1 => 'cooper12_3x6min_threshold',
            2 => 'cooper12_8x400_target_pace',
            3 => 'cooper12_5x600_overpace',
            4 => 'cooper12_full_12min_time_trial',
            5 => 'cooper12_6x800_target_effort',
            6 => 'cooper12_4x1000_controlled',
            7 => 'cooper12_3x1200_race_pressure',
            0 => 'cooper12_12x200_sharpening', // week 8 maps to 0 for mod 8
        ];

        $secondaryWorkouts = [
            'cooper12_hill_10x30sec',
            'cooper12_10x300_fast_relaxed',
            'cooper12_20min_threshold',
        ];

        for ($w = 1; $w <= $weeks; $w++) {
            $mod = $w % 8;
            $primaryId = $primaryWorkouts[$mod];

            $currentMileage = $mileage;
            if ($w % 4 === 0) $currentMileage *= 0.8;

            $peakMileage = max($peakMileage, $currentMileage);

            $longRunDistance = min(round($currentMileage * 0.25, 1), 12);
            $maxLongRun = max($maxLongRun, $longRunDistance);

            $activeDays = [];
            if ($frequency >= 3) {
                $activeDays = [2, 4, $longRunDayIndex];
            }
            if ($frequency >= 4) {
                $activeDays = [2, 4, 5, $longRunDayIndex];
            }
            if ($frequency >= 5) {
                $activeDays = [2, 3, 4, 5, $longRunDayIndex];
            }
            if ($frequency >= 6) {
                $activeDays = [1, 2, 3, 4, 5, $longRunDayIndex];
            }
            if ($frequency === 7) {
                $activeDays = [1, 2, 3, 4, 5, 6, 7];
            }

            $qualityDaysAssigned = 0;

            for ($d = 1; $d <= 7; $d++) {
                if (!in_array($d, $activeDays)) {
                    if ($d === 1 || $d === 3) {
                        $sessions[] = [
                            'day' => $dayCount,
                            'date_offset' => $dayCount - 1,
                            'type' => 'strength',
                            'distance' => null,
                            'duration' => '30 mins',
                            'description' => "Strength training: squat, calf raise, lunge, plank, hip bridge, plyometric ringan.",
                            'workout_id' => null,
                            'target_pace' => null,
                        ];
                        $totalSessions++;
                    }
                    $dayCount++;
                    continue;
                }

                $type = 'easy_run';
                $dist = round($currentMileage * 0.15, 1);
                $desc = "Easy run. Jaga detak jantung tetap rendah.";
                $wId = null;

                if ($d === $longRunDayIndex) {
                    $type = 'long_run';
                    $dist = $longRunDistance;
                    $desc = "Long run santai. Bangun aerobic base.";

                    if ($w === $weeks) {
                        $type = 'time_trial';
                        $dist = 3.2;
                        $desc = "Tes 12 Menit Cooper (Target 3200m - 3400m)";
                        $wId = 'cooper12_full_12min_time_trial';
                    }
                } elseif ($qualityDaysAssigned === 0 && $d === 2) {
                    $wData = $cooperWorkouts->get($primaryId);
                    $type = $wData['type'] ?? 'interval';
                    $dist = round($currentMileage * 0.2, 1);
                    $desc = ($wData['name'] ?? '') . "\n" . ($wData['main_set'] ?? '') . "\n" . ($wData['intensity'] ?? '') . "\n" . ($wData['note'] ?? '');
                    $wId = $primaryId;
                    $qualityDaysAssigned++;
                } elseif ($qualityDaysAssigned < $hardSessionsCount && $d === 4) {
                    $secId = $secondaryWorkouts[array_rand($secondaryWorkouts)];
                    $wData = $cooperWorkouts->get($secId);
                    $type = $wData['type'] ?? 'interval';
                    $dist = round($currentMileage * 0.2, 1);
                    $desc = ($wData['name'] ?? '') . "\n" . ($wData['main_set'] ?? '') . "\n" . ($wData['intensity'] ?? '') . "\n" . ($wData['note'] ?? '');
                    $wId = $secId;
                    $qualityDaysAssigned++;
                }

                $sessions[] = [
                    'day' => $dayCount,
                    'date_offset' => $dayCount - 1,
                    'type' => $type,
                    'distance' => $dist,
                    'duration' => null,
                    'description' => $desc,
                    'workout_id' => $wId,
                    'target_pace' => null,
                ];

                $totalDistance += (float) $dist;
                $totalSessions++;
                $dayCount++;
            }
        }

        return [
            'sessions' => $sessions,
            'summary' => [
                'total_weeks' => $weeks,
                'total_distance' => round($totalDistance, 1),
                'total_sessions' => $totalSessions,
                'max_long_run' => round($maxLongRun, 1),
                'peak_mileage' => round($peakMileage, 1),
            ],
        ];
    }

    // =========================================================================
    // INTERNAL HELPERS
    // =========================================================================

    private function loadLibrary(): array
    {
        $libraryPath = config_path('workout_library.php');
        return file_exists($libraryPath) ? include($libraryPath) : [];
    }
}
