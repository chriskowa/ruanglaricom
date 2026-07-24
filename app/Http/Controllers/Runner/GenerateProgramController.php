<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\StravaActivity;
use App\Services\DanielsRunningService;
use App\Services\OpenAiService;
use App\Services\ProgramBuilderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateProgramController extends Controller
{
    protected $danielsService;
    protected $openAiService;
    protected $builderService;

    public function __construct(DanielsRunningService $danielsService, OpenAiService $openAiService, ProgramBuilderService $builderService)
    {
        $this->danielsService = $danielsService;
        $this->openAiService = $openAiService;
        $this->builderService = $builderService;
    }

    /**
     * Generate program based on Daniels' Running Formula & AI Refinement
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
            'duration_weeks' => 'nullable|integer|min:6|max:24',
            
            // AI and Periodization inputs
            'race_distance' => 'nullable|in:5k,10k,21k,42k',
            'race_time' => 'nullable|string|regex:/^(\d{1,2}:)?\d{1,2}:\d{2}$/',
            'goal_race_date' => 'nullable|date|after_or_equal:today',
            'runner_level' => 'nullable|in:beginner,intermediate,advanced',
            'long_run_day' => 'nullable|in:saturday,sunday',
            'is_tropical' => 'nullable|boolean',
            'use_ai' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        
        // Calculate VDOT from recent race data if provided
        $vdot = null;
        if (!empty($validated['race_distance']) && !empty($validated['race_time'])) {
            $vdot = $this->danielsService->calculateVDOT($validated['race_time'], $validated['race_distance']);
            if ($vdot && $user) {
                $user->update(['vdot' => $vdot]);
            }
        }

        if (!$vdot && $user) {
            $vdot = $user->vdot;
        }

        if (! $vdot) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan update Personal Best (PB) Anda terlebih dahulu atau isi kolom fitness terbaru untuk menghitung VDOT.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $adaptive = $this->buildAdaptiveInputs($user->id, $validated);
            $effectiveWeeklyMileage = (float) ($adaptive['weekly_mileage'] ?? $validated['weekly_mileage']);
            $effectiveFrequency = (int) ($adaptive['training_frequency'] ?? $validated['training_frequency']);

            $runnerLevel = $validated['runner_level'] ?? 'intermediate';
            $longRunDay = $validated['long_run_day'] ?? 'sunday';
            $isTropical = filter_var($validated['is_tropical'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $useAi = filter_var($validated['use_ai'] ?? true, FILTER_VALIDATE_BOOLEAN);

            // Determine duration in weeks
            $durationWeeks = 8;
            if (!empty($validated['goal_race_date'])) {
                $targetDate = Carbon::parse($validated['goal_race_date']);
                $durationWeeks = max(8, min(24, (int) ceil(now()->diffInWeeks($targetDate))));
            } elseif (!empty($validated['duration_weeks'])) {
                $durationWeeks = (int) $validated['duration_weeks'];
            }

            // Cap the improvement to a realistic level based on runner level
            $maxVdotImprovement = 3.0;
            if ($runnerLevel === 'beginner') {
                $maxVdotImprovement = 2.0;
            } elseif ($runnerLevel === 'advanced') {
                $maxVdotImprovement = 4.0;
            }

            $targetVdot = null;
            if (! empty($validated['goal_time'])) {
                $targetVdot = $this->danielsService->calculateVDOT($validated['goal_time'], $validated['goal_distance']);
            }
            $safeTargetVdot = $targetVdot ? min($targetVdot, $vdot + $maxVdotImprovement) : $vdot;

            // Calculate training paces
            $paces = $this->danielsService->calculateTrainingPaces($vdot);
            if ($isTropical) {
                $paces['E'] += 0.25;  // +15s/km
                $paces['M'] += 0.20;  // +12s/km
                $paces['T'] += 0.167; // +10s/km
                $paces['I'] += 0.133; // +8s/km
                $paces['R'] += 0.083; // +5s/km
            }

            // Generate periodized program sessions
            $programData = $this->builderService->build([
                'target_distance' => $validated['goal_distance'],
                'weekly_mileage' => $effectiveWeeklyMileage,
                'frequency' => $effectiveFrequency,
                'weeks' => $durationWeeks,
                'initial_vdot' => $vdot,
                'target_vdot' => $safeTargetVdot,
                'runner_level' => $runnerLevel,
                'long_run_day' => $longRunDay,
                'is_tropical' => $isTropical,
            ]);

            $sessions = $programData['sessions'] ?? [];

            // Inject Strength Sessions
            if (is_array($sessions)) {
                $sessions = $this->injectStrengthSessions(
                    $sessions,
                    $effectiveWeeklyMileage,
                    $effectiveFrequency,
                    $adaptive['recent'] ?? []
                );
            }

            // AI Description Refinement
            if ($useAi && is_array($sessions) && $sessions) {
                $sessions = $this->improveProgramSessionsWithAi($sessions, [
                    'target_distance' => $validated['goal_distance'],
                    'weeks' => $durationWeeks,
                    'weekly_mileage' => $effectiveWeeklyMileage,
                    'frequency' => $effectiveFrequency,
                    'runner_level' => $runnerLevel,
                    'long_run_day' => $longRunDay,
                    'initial_vdot' => $vdot,
                    'target_vdot' => $safeTargetVdot,
                    'is_tropical' => $isTropical,
                    'paces' => $paces,
                ]);
            }

            // Create program
            $title = 'Program VDOT AI: '.strtoupper($validated['goal_distance']).' ('.$durationWeeks.' Weeks)';
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => $title,
                'slug' => $this->generateUniqueSlug($title),
                'description' => "AI VDOT periodized program for " . strtoupper($validated['goal_distance']),
                'difficulty' => $this->determineDifficulty($effectiveWeeklyMileage),
                'distance_target' => $validated['goal_distance'],
                'price' => 0,
                'program_json' => [
                    'sessions' => $sessions,
                    'duration_weeks' => $durationWeeks,
                ],
                'is_vdot_generated' => true,
                'vdot_score' => $vdot,
                'is_active' => true,
                'is_published' => true,
                'duration_weeks' => $durationWeeks,
                'is_self_generated' => true,
                'daniels_params' => [
                    'age' => $validated['age'],
                    'gender' => $validated['gender'],
                    'weekly_mileage' => $effectiveWeeklyMileage,
                    'training_frequency' => $effectiveFrequency,
                    'goal_distance' => $validated['goal_distance'],
                    'goal_time' => $validated['goal_time'] ?? null,
                    'training_paces' => $paces,
                    'initial_vdot' => $vdot,
                    'target_vdot' => $safeTargetVdot,
                    'adaptive' => $adaptive,
                    'runner_level' => $runnerLevel,
                    'long_run_day' => $longRunDay,
                    'is_tropical' => $isTropical,
                    'use_ai' => $useAi,
                ],
                'generated_vdot' => $vdot,
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
            $initialVdot = (float) $vdot;
            $targetVdot  = (float) $safeTargetVdot;
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

            $paceRationale = [
                [
                    'type'         => 'Easy (E)',
                    'pace'         => $this->formatPace($paces['E'] ?? 0),
                    'purpose'      => 'Membangun aerobic base yang kuat — fondasi dari semua peningkatan',
                    'contribution' => '~80% volume latihan',
                    'color'        => 'green',
                ],
                [
                    'type'         => 'Threshold (T)',
                    'pace'         => $this->formatPace($paces['T'] ?? 0),
                    'purpose'      => 'Meningkatkan lactate threshold — kunci utama lari lebih cepat lebih lama',
                    'contribution' => '~10-15% volume latihan',
                    'color'        => 'yellow',
                ],
                [
                    'type'         => 'Interval (I)',
                    'pace'         => $this->formatPace($paces['I'] ?? 0),
                    'purpose'      => 'Meningkatkan VO2Max — kapasitas aerobik maksimal Anda',
                    'contribution' => '~5-8% volume latihan',
                    'color'        => 'orange',
                ],
                [
                    'type'         => 'Repetition (R)',
                    'pace'         => $this->formatPace($paces['R'] ?? 0),
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
                'vdot'                 => $vdot,
                'training_paces'       => $paces,
                'improvement_projection' => [
                    'initial_vdot'     => round($initialVdot, 1),
                    'target_vdot'      => round($targetVdot, 1),
                    'vdot_diff'        => $vdotDiff,
                    'vdot_pct'         => $vdotPct,
                    'duration_weeks'   => $durationWeeks,
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
     * Format pace for display
     */
    private function formatPace(float $minutesPerKm): string
    {
        $minutes = floor($minutesPerKm);
        $seconds = round(($minutesPerKm - $minutes) * 60);

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    private function formatPacePeriodized(float $minPerKm)
    {
        $m = floor($minPerKm);
        $s = round(($minPerKm - $m) * 60);
        return sprintf('@ %d:%02d/km', $m, $s);
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

    private function buildPeriodizedProgram(array $config)
    {
        return $this->builderService->build($config);
    }

    private function improveProgramSessionsWithAi(array $sessions, array $context): array
    {
        $hasKey = (bool) (config('services.openai.api_key') ?: env('OPENAI_API_KEY'));
        if (! $hasKey) {
            return $sessions;
        }

        // Collect unique Phase_Type combinations to generate tailored phase-specific & type-specific templates
        $combinations = [];
        foreach ($sessions as $s) {
            if (! is_array($s)) continue;
            $phase = $s['phase'] ?? null;
            $type = $s['type'] ?? null;
            if (is_string($phase) && $phase !== '' && is_string($type) && $type !== '') {
                $combinations[] = $phase . '_' . $type;
            }
        }
        $combinations = array_values(array_unique($combinations));
        if (empty($combinations)) {
            return $sessions;
        }

        $paces = $context['paces'] ?? [];
        $paceLines = [];
        foreach (['E', 'M', 'T', 'I', 'R'] as $k) {
            $v = $paces[$k] ?? null;
            if (is_numeric($v)) {
                $paceLines[] = $k.': '.$this->builderService->formatPace((float) $v);
            }
        }

        $system = 'Anda adalah Coach AI Ruang Lari, asisten pelatih lari profesional kelas dunia dengan keahlian mendalam pada formula periodisasi VDOT Jack Daniels. Tugas Anda adalah menyusun deskripsi latihan lari yang sangat spesifik, aman, ilmiah, dan meningkatkan performa atlet berdasarkan tingkat kebugaran mereka.';
        
        $prompt = "Sempurnakan deskripsi latihan lari untuk tingkat: " . strtoupper($context['runner_level']) . " (Beginner/Intermediate/Advanced-Elite).\n\n" .
            "Konteks Latihan:\n" .
            "- Target Jarak Lomba: " . strtoupper($context['target_distance'] ?? '-') . "\n" .
            "- Durasi: " . ($context['weeks'] ?? '-') . " Minggu\n" .
            "- Volume Latihan: " . ($context['weekly_mileage'] ?? '-') . " km/minggu\n" .
            "- Penyesuaian Suhu Tropis: " . ($context['is_tropical'] ? 'Aktif (Pace disesuaikan untuk cuaca panas Indonesia)' : 'Nonaktif') . "\n" .
            "- VDOT Awal: " . ($context['initial_vdot'] ?? '-') . " -> Target VDOT: " . ($context['target_vdot'] ?? '-') . "\n" .
            "Pace Latihan (min/km):\n" . implode("\n", $paceLines) . "\n\n" .
            "Daftar kombinasi latihan (Format: Phase_Type) yang membutuhkan deskripsi:\n" . implode(', ', $combinations) . "\n\n" .
            "Kembalikan format JSON murni (tanpa tag markdown ```json) dengan struktur:\n" .
            "{\n" .
            "  \"templates\": {\n" .
            "    \"Phase_Type\": \"Deskripsi latihan spesifik\"\n" .
            "  }\n" .
            "}\n\n" .
            "Panduan Penulisan Deskripsi berdasarkan Level Pelari:\n" .
            "1. BEGINNER (Pemula):\n" .
            "   - Fokus: Membangun daya tahan dasar (aerobic base), pencegahan cedera, dan konsistensi.\n" .
            "   - Deskripsi lari mudah/recovery harus santai, menyarankan pernapasan berirama (3:3), hidrasi teratur, dan berjalan kaki jika detak jantung terlalu tinggi.\n" .
            "   - Latihan kualitas (jika ada) harus ringan, memberi penekanan pada form lari yang relaks.\n" .
            "2. INTERMEDIATE (Medium):\n" .
            "   - Fokus: Meningkatkan laktat threshold, efisiensi energi, dan ketahanan jarak jauh.\n" .
            "   - Berikan panduan terstruktur: Warmup (pemanasan) lari mudah 10-15 menit + dinamis stretch, Main Set (latihan inti) sesuai pace target dengan instruksi kontrol pace agar tidak overshoot, dan Cooldown (pendinginan).\n" .
            "   - Contoh long run: fokus pada pacing konstan dan asupan nutrisi latihan (gel/elektrolit).\n" .
            "3. ADVANCED / ELITE (Mahir):\n" .
            "   - Fokus: Memaksimalkan VO2Max, running economy, mental toughness, dan taktis lomba.\n" .
            "   - Berikan instruksi set latihan yang presisi dan atletis. Contoh untuk Interval (I): 'Pemanasan 3km E + drills. Latihan Inti: set interval {distance_km}km @ {target_pace} dengan recovery jog aktif. Pendinginan 2km E. Fokus pada cadance tinggi (180+ SPM) dan postur tegak.'\n" .
            "   - Contoh Long Run: Sertakan variasi seperti progresif atau fast-finish block di akhir sesi.\n\n" .
            "Aturan Penting & Pemulihan (De-load):\n" .
            "- Jika sesi berada dalam minggu De-load (Minggu pemulihan kelipatan 4, misal minggu 4, 8, 12, 16), tekankan pentingnya adaptasi tubuh, lari santai yang rileks, dan kurangi intensitas mental.\n" .
            "- Gunakan Bahasa Indonesia yang profesional namun memotivasi (gaya Coach lari yang suportif).\n" .
            "- Harus memakai placeholder {distance_km} dan {target_pace} untuk menggambarkan jarak sesi dan pace target.\n" .
            "- Jangan mengubah tipe latihan, jarak, atau target pace.\n" .
            "- JANGAN menggunakan EMOJI sama sekali dalam teks (dilarang keras).\n" .
            "- Untuk latihan kualitas (seperti interval, threshold, repetition, marathon_pace, progression, atau long_run_quality), Anda WAJIB menggunakan dan mengisi template berikut ini (pisahkan setiap baris dengan new line):\n" .
            "  Warm Up: [Deskripsi pemanasan]\n" .
            "  Main Set: [Latihan inti, gunakan placeholder {distance_km} dan {target_pace}]\n" .
            "  Recovery: [Jeda pemulihan/jogging santai]\n" .
            "  Cool Down: [Deskripsi pendinginan]\n" .
            "  Intensity: [Detail zona intensitas/pace target]\n" .
            "  Reason: [Alasan ilmiah dan fokus latihan]\n" .
            "- Buat deskripsi yang ringkas, informatif, dan mudah dibaca di kalender.\n" .
            "- Untuk tipe 'rest', berikan panduan pemulihan aktif (active recovery) spesifik per level (peregangan otot ringan untuk pemula, foam rolling/mobility untuk intermediate, core strength ringan untuk advanced).\n";

        try {
            $raw = $this->openAiService->getAiResponseOrThrow($prompt, $system);
            
            $raw = preg_replace('/^```json\s*/i', '', $raw);
            $raw = preg_replace('/```$/', '', $raw);
            $raw = trim($raw);

            $decoded = json_decode($raw, true);
            $templates = is_array($decoded) ? ($decoded['templates'] ?? null) : null;
            if (! is_array($templates) || ! $templates) {
                return $sessions;
            }

            foreach ($sessions as $i => $s) {
                if (! is_array($s)) continue;
                $phase = $s['phase'] ?? null;
                $type = $s['type'] ?? null;
                if (! is_string($phase) || ! is_string($type)) continue;
                
                $key = $phase . '_' . $type;
                $tpl = $templates[$key] ?? null;
                if (! is_string($tpl) || trim($tpl) === '') continue;

                $repl = [
                    '{distance_km}' => (string) ($s['distance'] ?? ''),
                    '{target_pace}' => (string) ($s['target_pace'] ?? ''),
                ];
                $s['description'] = trim(strtr($tpl, $repl));
                $sessions[$i] = $s;
            }

            return $sessions;
        } catch (\Throwable $e) {
            Log::warning('AI refine generator failed: '.$e->getMessage());
            return $sessions;
        }
    }

    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while (true) {
            $query = Program::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
