<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\StravaActivity;
use App\Services\DanielsRunningService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateProgramController extends Controller
{
    protected $danielsService;

    public function __construct(DanielsRunningService $danielsService)
    {
        $this->danielsService = $danielsService;
    }

    /**
     * Generate program based on Daniels' Running Formula
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'age' => 'required|integer|min:10|max:100',
            'gender' => 'required|in:male,female',
            'weekly_mileage' => 'required|numeric|min:0|max:300',
            'training_frequency' => 'required|integer|min:2|max:7',
            'goal_distance' => 'required|in:5k,10k,21k,42k',
            'goal_time' => 'nullable|string|regex:/^(\d{1,2}:)?\d{1,2}:\d{2}$/',
            'duration_weeks' => 'nullable|integer|min:6|max:12', // Flexible short duration
        ]);

        $user = auth()->user();
        $vdot = $user->vdot;

        if (! $vdot) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan update Personal Best (PB) Anda terlebih dahulu untuk menghitung VDOT.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $adaptive = $this->buildAdaptiveInputs($user->id, $validated);
            $effectiveWeeklyMileage = (float) ($adaptive['weekly_mileage'] ?? $validated['weekly_mileage']);
            $effectiveFrequency = (int) ($adaptive['training_frequency'] ?? $validated['training_frequency']);

            $targetVdot = null;
            if (! empty($validated['goal_time'])) {
                $targetVdot = $this->danielsService->calculateVDOT($validated['goal_time'], $validated['goal_distance']);
            }
            $safeTargetVdot = $targetVdot ? min($targetVdot, $vdot + 3.0) : $vdot;

            // Generate program using VDOT directly
            $programData = $this->danielsService->generateProgramFromVDOT($vdot, [
                'goal_distance' => $validated['goal_distance'],
                'weekly_mileage' => $effectiveWeeklyMileage,
                'training_frequency' => $effectiveFrequency,
                'duration_weeks' => $validated['duration_weeks'] ?? 8,
                'initial_vdot' => $vdot,
                'target_vdot' => $safeTargetVdot,
                'pace_progression' => true,
                'max_vdot_delta' => 3.0,
            ]);

            if (isset($programData['sessions']) && is_array($programData['sessions'])) {
                $programData['sessions'] = $this->injectStrengthSessions(
                    $programData['sessions'],
                    $effectiveWeeklyMileage,
                    $effectiveFrequency,
                    $adaptive['recent'] ?? []
                );
            }

            // Create program
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => 'Program VDOT: '.strtoupper($validated['goal_distance']).' ('.$programData['duration_weeks'].' Weeks)',
                'slug' => 'vdot-'.strtolower($validated['goal_distance']).'-'.Str::random(8),
                'description' => $this->generateDescription($validated, $programData),
                'difficulty' => $this->determineDifficulty($validated['weekly_mileage']),
                'distance_target' => $validated['goal_distance'],
                'price' => 0,
                'program_json' => [
                    'sessions' => $programData['sessions'],
                    'duration_weeks' => $programData['duration_weeks'],
                ],
                'is_vdot_generated' => true,
                'vdot_score' => $programData['vdot'],
                'is_active' => true,
                'is_published' => true,
                'duration_weeks' => $programData['duration_weeks'],
                'is_self_generated' => true,
                'daniels_params' => [
                    'age' => $validated['age'],
                    'gender' => $validated['gender'],
                    'weekly_mileage' => $effectiveWeeklyMileage,
                    'training_frequency' => $effectiveFrequency,
                    'goal_distance' => $validated['goal_distance'],
                    'goal_time' => $validated['goal_time'] ?? null,
                    'training_paces' => $programData['training_paces'],
                    'initial_vdot' => $programData['initial_vdot'] ?? $vdot,
                    'target_vdot' => $programData['target_vdot'] ?? $vdot,
                    'adaptive' => $adaptive,
                ],
                'generated_vdot' => $programData['vdot'],
            ]);

            // Auto-enroll in the generated program (Add to Bag)
            $enrollment = ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => $user->id,
                'start_date' => null,
                'end_date' => null,
                'status' => 'purchased',
                'payment_status' => 'paid',
            ]);

            DB::commit();

            // Build improvement projection
            $initialVdot = (float) ($programData['initial_vdot'] ?? $vdot);
            $targetVdot  = (float) ($programData['target_vdot'] ?? $vdot);
            $vdotDiff = round($targetVdot - $initialVdot, 1);
            $vdotPct  = $initialVdot > 0 ? round(($vdotDiff / $initialVdot) * 100, 1) : 0;

            $initialEquiv = $this->danielsService->calculateEquivalentRaceTimes($initialVdot);
            $targetEquiv  = $this->danielsService->calculateEquivalentRaceTimes($targetVdot);

            $distLabels = ['5k' => '5K', '10k' => '10K', '21k' => 'Half Marathon', '42k' => 'Marathon'];
            $timeImprovements = [];
            foreach ($distLabels as $key => $label) {
                $oldTime = $initialEquiv[$key]['time'] ?? null;
                $newTime = $targetEquiv[$key]['time'] ?? null;
                if ($oldTime && $newTime) {
                    $oldSec = $this->secFromTime($oldTime);
                    $newSec = $this->secFromTime($newTime);
                    $diffSec = $oldSec - $newSec;
                    $pct = $oldSec > 0 ? round(abs($diffSec / $oldSec) * 100, 1) : 0;
                    $timeImprovements[$key] = [
                        'label'           => $label,
                        'current_time'    => $oldTime,
                        'projected_time'  => $newTime,
                        'diff_seconds'    => $diffSec,
                        'improvement_pct' => $pct,
                    ];
                }
            }

            $trainingPaces = $programData['training_paces'];
            $paceRationale = [
                [
                    'type'         => 'Easy (E)',
                    'pace'         => $this->formatPace($trainingPaces['E'] ?? 0),
                    'purpose'      => 'Membangun aerobic base yang kuat — fondasi dari semua peningkatan',
                    'contribution' => '~80% volume latihan',
                    'color'        => 'green',
                ],
                [
                    'type'         => 'Threshold (T)',
                    'pace'         => $this->formatPace($trainingPaces['T'] ?? 0),
                    'purpose'      => 'Meningkatkan lactate threshold — kunci utama lari lebih cepat lebih lama',
                    'contribution' => '~10-15% volume latihan',
                    'color'        => 'yellow',
                ],
                [
                    'type'         => 'Interval (I)',
                    'pace'         => $this->formatPace($trainingPaces['I'] ?? 0),
                    'purpose'      => 'Meningkatkan VO2Max — kapasitas aerobik maksimal Anda',
                    'contribution' => '~5-8% volume latihan',
                    'color'        => 'orange',
                ],
                [
                    'type'         => 'Repetition (R)',
                    'pace'         => $this->formatPace($trainingPaces['R'] ?? 0),
                    'purpose'      => 'Meningkatkan speed economy & running form — efisiensi lari',
                    'contribution' => '~2-5% volume latihan',
                    'color'        => 'red',
                ],
            ];

            $goalTimeProjected = !empty($validated['goal_time']) && !empty($validated['goal_distance'])
                ? $targetEquiv[$validated['goal_distance']] ?? null
                : null;

            return response()->json([
                'success'              => true,
                'message'              => 'Program berhasil di-generate! Program telah ditambahkan ke Program Bag Anda.',
                'program_id'           => $program->id,
                'vdot'                 => $programData['vdot'],
                'training_paces'       => $programData['training_paces'],
                'improvement_projection' => [
                    'initial_vdot'     => round($initialVdot, 1),
                    'target_vdot'      => round($targetVdot, 1),
                    'vdot_diff'        => $vdotDiff,
                    'vdot_pct'         => $vdotPct,
                    'duration_weeks'   => $programData['duration_weeks'],
                    'goal_distance'    => $validated['goal_distance'],
                    'goal_time_input'  => $validated['goal_time'] ?? null,
                    'goal_projected'   => $goalTimeProjected,
                    'time_improvements'=> $timeImprovements,
                    'pace_rationale'   => $paceRationale,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate program: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convert HH:MM:SS time string to total seconds
     */
    private function secFromTime(string $time): int
    {
        $parts = explode(':', $time);
        if (count($parts) === 3) {
            return (int)$parts[0] * 3600 + (int)$parts[1] * 60 + (int)$parts[2];
        } elseif (count($parts) === 2) {
            return (int)$parts[0] * 60 + (int)$parts[1];
        }
        return 0;
    }

    /**
     * Generate description for the program
     */
    private function generateDescription(array $params, array $programData): string
    {
        $description = "Program latihan yang di-generate menggunakan Daniels' Running Formula.\n\n";
        $description .= 'VDOT Score: '.($programData['vdot'] ?? '-')."\n";
        $description .= "Training Paces:\n";
        if (isset($programData['training_paces'])) {
            $description .= '- Easy (E): '.$this->formatPace($programData['training_paces']['E'])."/km\n";
            $description .= '- Threshold (T): '.$this->formatPace($programData['training_paces']['T'])."/km\n";
            $description .= '- Interval (I): '.$this->formatPace($programData['training_paces']['I'])."/km\n";
        }

        $durationWeeks = $programData['duration_weeks'] ?? 8;
        $description .= "\nTarget: ".strtoupper($params['goal_distance']).' ('.$durationWeeks.' Weeks)';

        return $description;
    }

    /**
     * Format pace for display
     */
    private function formatPace(float $minutesPerKm): string
    {
        $minutes = floor($minutesPerKm);
        $seconds = round(($minutesPerKm - $minutes) * 60);

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Determine difficulty based on weekly mileage
     */
    private function determineDifficulty(float $weeklyMileage): string
    {
        if ($weeklyMileage < 20) {
            return 'beginner';
        } elseif ($weeklyMileage < 50) {
            return 'intermediate';
        } else {
            return 'advanced';
        }
    }

    private function buildAdaptiveInputs(int $userId, array $validated): array
    {
        $recent = $this->getRecentTrainingSummary($userId);

        $weeklyMileageInput = (float) ($validated['weekly_mileage'] ?? 0);
        $frequencyInput = (int) ($validated['training_frequency'] ?? 4);

        $weeklyMileageEffective = max(5, $weeklyMileageInput);
        $frequencyEffective = max(2, min(7, $frequencyInput));

        $recentWeeklyKm = (float) data_get($recent, 'weekly_km_estimate', 0);
        if ($recentWeeklyKm > 0) {
            $upCap = max(5, round($recentWeeklyKm * 1.20, 1));
            if ($weeklyMileageEffective > $upCap) {
                $weeklyMileageEffective = $upCap;
            }

            $recentRunDays = (int) data_get($recent, 'run_days_14d', 0);
            if ($recentRunDays > 0) {
                $maxFreq = min(7, max(3, $recentRunDays + 1));
                if ($frequencyEffective > $maxFreq) {
                    $frequencyEffective = $maxFreq;
                }
            }
        }

        return [
            'weekly_mileage' => $weeklyMileageEffective,
            'training_frequency' => $frequencyEffective,
            'recent' => $recent,
        ];
    }

    private function getRecentTrainingSummary(int $userId): array
    {
        $end = Carbon::now();
        $start = $end->copy()->subDays(14);

        $activities = StravaActivity::query()
            ->where('user_id', $userId)
            ->whereNotNull('start_date')
            ->whereBetween('start_date', [$start, $end])
            ->whereIn('type', ['Run', 'VirtualRun', 'TrailRun', 'Treadmill', 'run', 'virtualrun', 'trailrun', 'treadmill'])
            ->orderByDesc('start_date')
            ->get(['start_date', 'distance_m']);

        $totalKm14 = round($activities->sum(fn ($a) => ((float) ($a->distance_m ?? 0)) / 1000), 2);
        $weeklyKmEstimate = round($totalKm14 / 2, 2);
        $dayKeys = $activities->map(function ($a) {
            try {
                return Carbon::parse($a->start_date)->toDateString();
            } catch (\Throwable $e) {
                return null;
            }
        })->filter()->unique()->values();

        return [
            'lookback_days' => 14,
            'total_km_14d' => $totalKm14,
            'weekly_km_estimate' => $weeklyKmEstimate,
            'run_days_14d' => $dayKeys->count(),
        ];
    }

    private function injectStrengthSessions(array $sessions, float $weeklyMileage, int $frequency, array $recent): array
    {
        $perWeek = 2;
        if ($weeklyMileage < 15 || $frequency <= 3) {
            $perWeek = 1;
        }

        $level = 'base';
        if ($weeklyMileage >= 45) {
            $level = 'maintenance';
        } elseif ((float) data_get($recent, 'weekly_km_estimate', 0) <= 12) {
            $level = 'beginner';
        }

        $out = $sessions;
        $byWeek = [];
        foreach ($out as $idx => $s) {
            $week = (int) data_get($s, 'week', 0);
            if ($week <= 0) continue;
            $byWeek[$week][] = $idx;
        }

        foreach ($byWeek as $week => $indexes) {
            $picked = [];
            $candidatesPreferred = [];
            $candidatesFallback = [];

            foreach ($indexes as $idx) {
                $s = $out[$idx] ?? null;
                if (! is_array($s)) continue;
                if (($s['type'] ?? null) !== 'rest') continue;

                $day = (int) data_get($s, 'day', 0);
                if ($day <= 0) continue;
                $dow = (($day - 1) % 7) + 1; // 1=Mon ... 7=Sun

                if (in_array($dow, [2, 4], true)) {
                    $candidatesPreferred[] = $idx;
                } elseif (! in_array($dow, [1, 7], true)) {
                    $candidatesFallback[] = $idx;
                }
            }

            $candidates = array_merge($candidatesPreferred, $candidatesFallback);
            foreach ($candidates as $idx) {
                if (count($picked) >= $perWeek) break;
                $picked[] = $idx;
            }

            foreach ($picked as $i => $idx) {
                $focus = ($i % 2 === 0) ? 'Lower + Core' : 'Core + Mobility';
                $out[$idx] = $this->makeStrengthSession($out[$idx], $level, $focus);
            }
        }

        return $out;
    }

    private function makeStrengthSession(array $session, string $level, string $focus): array
    {
        $plan = [];
        $duration = '00:30:00';
        $difficulty = 'moderate';

        if ($level === 'maintenance') {
            $duration = '00:25:00';
            $difficulty = 'easy';
            $plan = [
                ['name' => 'Glute Bridge', 'sets' => 3, 'reps' => 12, 'notes' => '2s squeeze at top'],
                ['name' => 'Single-leg RDL (Bodyweight)', 'sets' => 3, 'reps' => 8, 'notes' => 'Each leg'],
                ['name' => 'Calf Raise (Slow Eccentric)', 'sets' => 3, 'reps' => 12, 'notes' => '3s down'],
                ['name' => 'Side Plank', 'sets' => 3, 'reps' => '30s', 'notes' => 'Each side'],
                ['name' => 'Hip Mobility Flow', 'sets' => 1, 'reps' => '5 min', 'notes' => '90/90 + pigeon stretch'],
            ];
        } elseif ($level === 'beginner') {
            $duration = '00:20:00';
            $difficulty = 'easy';
            $plan = [
                ['name' => 'Bodyweight Squat', 'sets' => 3, 'reps' => 10, 'notes' => 'Controlled tempo'],
                ['name' => 'Reverse Lunge', 'sets' => 3, 'reps' => 8, 'notes' => 'Each leg'],
                ['name' => 'Calf Raise', 'sets' => 3, 'reps' => 12, 'notes' => 'Full range'],
                ['name' => 'Dead Bug', 'sets' => 2, 'reps' => 10, 'notes' => 'Each side'],
                ['name' => 'Plank', 'sets' => 2, 'reps' => '30s', 'notes' => 'Neutral spine'],
            ];
        } else {
            $plan = [
                ['name' => 'Goblet Squat', 'sets' => 3, 'reps' => 12, 'notes' => 'RPE 6-7'],
                ['name' => 'Romanian Deadlift', 'sets' => 3, 'reps' => 10, 'notes' => 'Hip hinge'],
                ['name' => 'Split Squat', 'sets' => 3, 'reps' => 8, 'notes' => 'Each leg'],
                ['name' => 'Calf Raise (Slow Eccentric)', 'sets' => 3, 'reps' => 15, 'notes' => '3s down'],
                ['name' => 'Side Plank', 'sets' => 3, 'reps' => '30s', 'notes' => 'Each side'],
            ];
        }

        if ($focus === 'Core + Mobility') {
            $plan = array_values(array_merge(
                [
                    ['name' => 'Hip Mobility Flow', 'sets' => 1, 'reps' => '6 min', 'notes' => '90/90 + hamstring stretch'],
                    ['name' => 'Dead Bug', 'sets' => 3, 'reps' => 10, 'notes' => 'Each side'],
                    ['name' => 'Plank', 'sets' => 3, 'reps' => '30s', 'notes' => 'Nasal breathing'],
                ],
                array_slice($plan, 0, 2)
            ));
        }

        $descLines = [];
        $descLines[] = 'Warmup: 5 min mobility (hips/ankles) + activation';
        foreach ($plan as $p) {
            $sets = $p['sets'] ?? 3;
            $reps = $p['reps'] ?? 10;
            $name = $p['name'] ?? 'Exercise';
            $descLines[] = "{$sets}x{$reps} {$name}";
        }
        $descLines[] = 'Cooldown: 3-5 min stretching';

        $session['type'] = 'strength';
        $session['distance'] = 0;
        $session['duration'] = $duration;
        $session['difficulty'] = $difficulty;
        $session['description'] = implode("\n", $descLines);
        $session['strength'] = [
            'category' => $focus,
            'equipment' => 'Bodyweight / Dumbbells / Mat',
            'plan' => $plan,
        ];

        return $session;
    }
}
