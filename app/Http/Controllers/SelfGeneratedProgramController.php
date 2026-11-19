<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Services\AiCoachContinuityService;
use App\Services\CoachFeasibilityService;
use App\Services\DanielsRunningService;
use App\Services\MidtransService;
use App\Services\OpenAiService;
use App\Services\ProgramBuilderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Snap;

use App\Models\Participant;
use App\Models\User;
use App\Models\UsedPromoCode; // Assuming we will create this or use a simple check

class SelfGeneratedProgramController extends Controller
{
    protected $danielsService;
    protected $midtransService;
    protected $openAiService;
    protected $builderService;
    protected $coachFeasibility;
    protected $continuityService;

    public function __construct(
        DanielsRunningService $danielsService,
        MidtransService $midtransService,
        OpenAiService $openAiService,
        ProgramBuilderService $builderService,
        CoachFeasibilityService $coachFeasibility,
        AiCoachContinuityService $continuityService
    ) {
        $this->danielsService = $danielsService;
        $this->midtransService = $midtransService;
        $this->openAiService = $openAiService;
        $this->builderService = $builderService;
        $this->coachFeasibility = $coachFeasibility;
        $this->continuityService = $continuityService;
    }

    public function index()
    {
        // Check if there is a pending program to save after login
        if (auth()->check() && session()->has('pending_program_data')) {
            return $this->processAutoSave();
        }

        $aiInitialProfile = [];
        $aiTrialAvailable = false;
        $aiQuotaStatus = null;
        if (auth()->check()) {
            $user = auth()->user();
            $aiTrialAvailable = $this->continuityService->hasAvailableTrial($user);
            $aiQuotaStatus = $this->continuityService->getDailyQuotaStatus($user);
            if (in_array($user->gender, ['male', 'female'], true)) {
                $aiInitialProfile['gender'] = $user->gender;
            }
            if ($user->date_of_birth) {
                $age = Carbon::parse($user->date_of_birth)->age;
                if ($age >= 10 && $age <= 100) {
                    $aiInitialProfile['age'] = $age;
                }
            }
            foreach (['5k' => 'pb_5k', '10k' => 'pb_10k', '21k' => 'pb_hm', '42k' => 'pb_fm'] as $dKey => $col) {
                if (!empty($user->{$col})) {
                    $normalized = $this->normalizeTimeString((string) $user->{$col});
                    if ($normalized) {
                        $aiInitialProfile['pb_distance'] = $dKey;
                        $aiInitialProfile['pb_time'] = $normalized;
                        break;
                    }
                }
            }
        }

        return view('programs.generator_v2', compact('aiInitialProfile', 'aiTrialAvailable', 'aiQuotaStatus'));
    }

    /**
     * AJAX: Store program data in session for guest users
     */
    public function storePending(Request $request)
    {
        $validated = $request->validate([
            'form' => 'required|array',
            'result' => 'required|array',
        ]);

        session(['pending_program_data' => $validated]);

        return response()->json(['success' => true]);
    }

    /**
     * Public helper: Process auto-save directly from session for authenticated user
     */
    public function processAutoSaveDirect(?User $user = null): ?ProgramEnrollment
    {
        if (!session()->has('pending_program_data')) {
            return null;
        }

        $data = session()->pull('pending_program_data');
        $user = $user ?: auth()->user();
        if (!$user) {
            return null;
        }

        return $this->saveProgramFromData($user, $data, 'replace');
    }

    /**
     * Core helper: Save program and enrollment from structured form + result data
     */
    public function saveProgramFromData(User $user, array $data, ?string $action = 'replace'): ?ProgramEnrollment
    {
        $form = $data['form'] ?? [];
        $result = $data['result'] ?? [];
        if (empty($form) || empty($result)) {
            return null;
        }

        DB::beginTransaction();
        try {
            if ($action === 'replace') {
                ProgramEnrollment::where('runner_id', $user->id)
                    ->where('status', 'active')
                    ->update(['status' => 'cancelled']);
            }

            $targetDateStr = $form['target_date'] ?? null;
            $targetDate = !empty($targetDateStr) ? Carbon::parse($targetDateStr) : now()->addWeeks(12);
            $sessions = $result['sessions'] ?? [];
            $totalDays = count($sessions);
            $durationWeeks = (int) max(1, ceil($totalDays / 7));
            
            $startDateStr = $form['start_date'] ?? null;
            if (!empty($startDateStr)) {
                $startDate = Carbon::parse($startDateStr);
            } else {
                $startDate = $targetDate->copy()->subDays(max(0, $totalDays - 1))->startOfWeek();
            }

            $targetDistance = $form['target_distance'] ?? '10k';
            $vdot = $result['vdot'] ?? 30;

            // 1. Create Program (Assigned to Coach AI Official)
            $coachAiId = User::where('email', 'ai-coach@ruanglari.com')
                ->orWhere('username', 'coach-ai')
                ->value('id') ?? 392;

            $title = "Program Latihan " . strtoupper($targetDistance) . " (VDOT " . $vdot . ")";
            $isFreeTrial = $this->continuityService->consumeTrialIfAvailable($user);
            $paymentStatus = $isFreeTrial ? 'paid' : 'pending';

            $program = Program::create([
                'coach_id' => $coachAiId,
                'title' => $title,
                'slug' => $this->generateUniqueSlug($title),
                'description' => "Program latihan lari periodisasi ilmiah berbasis Jack Daniels' VDOT Formula.",
                'distance_target' => $targetDistance,
                'duration_weeks' => $durationWeeks,
                'program_json' => [
                    'sessions' => $sessions,
                    'summary' => $result['summary'] ?? []
                ],
                'is_vdot_generated' => true,
                'vdot_score' => $vdot,
                'is_active' => true,
                'is_published' => false,
                'price' => $isFreeTrial ? 0 : AiCoachContinuityService::PROGRAM_PRICE_IDR,
                'is_self_generated' => true,
                'generated_vdot' => $vdot,
                'daniels_params' => [
                    'pb_distance' => $form['pb_distance'] ?? null,
                    'pb_time' => $form['pb_time'] ?? null,
                    'target_distance' => $targetDistance,
                    'start_date' => $startDateStr,
                    'target_date' => $targetDateStr,
                    'weekly_mileage' => $form['weekly_mileage'] ?? null,
                    'frequency' => $form['frequency'] ?? null,
                    'runner_level' => $form['runner_level'] ?? null,
                    'long_run_day' => $form['long_run_day'] ?? null,
                    'height_cm' => $form['height_cm'] ?? null,
                    'weight_kg' => $form['weight_kg'] ?? null,
                    'injury_history' => $form['injury_history'] ?? 'none',
                    'include_strength' => $form['include_strength'] ?? true,
                    'strength_type' => $form['strength_type'] ?? 'hybrid',
                    'combine_plyometric' => $form['combine_plyometric'] ?? true,
                    'training_paces' => $result['paces'] ?? [],
                    'ai_coach_notes' => $result['coach_notes'] ?? null,
                ],
            ]);

            // 2. Create Enrollment
            $enrollment = ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => $user->id,
                'start_date' => $startDate,
                'end_date' => $targetDate,
                'status' => 'active',
                'payment_status' => $paymentStatus,
            ]);

            DB::commit();

            if ($paymentStatus === 'paid') {
                $this->continuityService->postProgramWelcomeMessage($user, $program, $enrollment, $result);
            }

            return $enrollment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('saveProgramFromData failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Internal: Process auto-save after login
     */
    protected function processAutoSave()
    {
        $user = auth()->user();
        $enrollment = $this->processAutoSaveDirect($user);

        if ($enrollment) {
            return redirect()->route('runner.calendar')->with('success', 'Program latihan Anda telah berhasil disimpan ke kalender!');
        }

        return redirect()->route('programs.realistic')->with('error', 'Gagal menyimpan program secara otomatis.');
    }

    /**
     * AJAX: Generate program logic based on Daniels Periodization
     */
    public function generate(Request $request)
    {
        if ($request->has('start_date') && ($request->input('start_date') === '' || $request->input('start_date') === null)) {
            $request->merge(['start_date' => null]);
        }

        $validated = $request->validate([
            'pb_distance' => 'required|in:5k,10k,21k,42k,cooper12,balke15',
            'pb_time' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $dist = $request->input('pb_distance');
                    if ($dist === 'cooper12' || $dist === 'balke15') {
                        if (!is_numeric($value) || (float)$value <= 0) {
                            $fail('Jarak hasil tes harus berupa angka positif (meter).');
                        }
                    } else {
                        if (!preg_match('/^(\d{1,2}:)?\d{1,2}:\d{2}$/', $value)) {
                            $fail('Format waktu PB harus HH:MM:SS atau MM:SS.');
                        }
                    }
                }
            ],
            'start_date' => 'nullable|date',
            'target_distance' => 'required|in:5k,10k,21k,42k,cooper12',
            'target_date' => 'required|date|after_or_equal:yesterday',
            'goal_time' => 'required|string|regex:/^(\d{1,2}:)?\d{1,2}:\d{2}$/',
            'weekly_mileage' => 'required|numeric|min:5|max:200',
            'frequency' => 'required|integer|min:3|max:7',
            'runner_level' => 'required|in:beginner,intermediate,advanced',
            'long_run_day' => 'required|in:saturday,sunday',
            'gender' => 'required|in:male,female',
            'age' => 'required|integer|min:10|max:100',
            'height_cm' => 'nullable|numeric|min:100|max:250',
            'weight_kg' => 'nullable|numeric|min:30|max:200',
            'injury_history' => 'nullable|string|in:none,knee,hamstring,ankle,shin,back',
            'include_strength' => 'nullable|boolean',
            'strength_type' => 'nullable|string|in:bodyweight,gym,plyometric,isometric,hybrid',
            'combine_plyometric' => 'nullable|boolean',
            'is_tropical' => 'nullable|boolean',
            'use_ai' => 'nullable|boolean',
            'force_infeasible_ack' => 'nullable|boolean',
            'aggressiveness' => 'nullable|string|in:conservative,standard,sharp',
        ], [
            'goal_time.regex' => 'Format waktu Goal harus HH:MM:SS atau MM:SS.',
            'target_date.after_or_equal' => 'Tanggal race harus hari ini atau di masa depan.',
        ]);

        try {
            $built = $this->buildProgramPayload($validated, $request->boolean('use_ai', false));
            return response()->json($built['body'], $built['status']);
        } catch (\Exception $e) {
            Log::error('Generator Error: ' . $e->getMessage());
            $safeAssess = [
                'feasibility' => 'HIGH_RISK',
                'score' => 30,
                'min_required_peak_mileage' => 30,
                'ideal_peak_mileage' => 40,
                'max_safe_peak_mileage' => 60,
                'min_weeks' => 10,
                'max_weeks' => 24,
                'min_frequency' => 4,
                'color' => 'orange',
                'label' => 'Risiko Kesalahan Sistem',
                'reason' => 'Terdapat kesalahan saat membangun program. Silakan coba beberapa saat lagi atau sesuaikan parameter beban/target.',
                'options' => [],
                'vdot_rate_per_week' => 0.5,
                'required_weeks_by_vdot' => 12,
                'violations' => ['Internal error: '.$e->getMessage()],
            ];
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate program. Silakan coba lagi.',
                'feasibility' => $safeAssess,
            ], 500);
        }
    }

    private function buildProgramPayload(array $validated, bool $useAi = false): array
    {
        $currentVdot = $this->danielsService->calculateVDOT($validated['pb_time'], $validated['pb_distance']);
        $targetVdot = $this->danielsService->calculateVDOT($validated['goal_time'], $validated['target_distance']);

        $targetDate = Carbon::parse($validated['target_date']);
        if (!empty($validated['start_date'])) {
            $startDate = Carbon::parse($validated['start_date']);
            $diffDays = max(1, $startDate->diffInDays($targetDate, false));
            $weeksUntilRace = max(8, min(24, (int) ceil($diffDays / 7)));
        } else {
            $weeksUntilRace = max(8, min(24, (int) ceil(now()->diffInWeeks($targetDate))));
        }

        $aggressiveness = $validated['aggressiveness'] ?? 'standard';
        if (!in_array($aggressiveness, ['conservative', 'standard', 'sharp'], true)) {
            $aggressiveness = 'standard';
        }
        $injuryHistory = $validated['injury_history'] ?? 'none';
        $goalTimeSec = $this->parseTimeToSeconds($validated['goal_time']);
        $weeklyMileageInput = (float) $validated['weekly_mileage'];
        $frequencyInput = (int) $validated['frequency'];
        $runnerLevelInput = $validated['runner_level'];

        $assess = $this->coachFeasibility->assess([
            'target_distance' => $validated['target_distance'],
            'goal_time_sec' => $goalTimeSec,
            'runner_level' => $runnerLevelInput,
            'weekly_mileage' => $weeklyMileageInput,
            'frequency' => $frequencyInput,
            'weeks' => $weeksUntilRace,
            'initial_vdot' => $currentVdot,
            'target_vdot' => $targetVdot,
            'injury_history' => $injuryHistory,
            'aggressiveness' => $aggressiveness,
        ]);

        $forceAck = (bool) ($validated['force_infeasible_ack'] ?? false);

        if ($assess['feasibility'] === 'INFEASIBLE' && !$forceAck) {
            return [
                'status' => 422,
                'body' => [
                    'success' => false,
                    'message' => $assess['reason'],
                    'feasibility' => $assess,
                    'current_vdot' => round($currentVdot, 1),
                    'target_vdot' => round($targetVdot, 1),
                ],
            ];
        }

        $maxVdotImprovementPercent = 0.08;
        if ($validated['runner_level'] === 'beginner') {
            $maxVdotImprovementPercent = 0.06;
        } elseif ($validated['runner_level'] === 'advanced') {
            $maxVdotImprovementPercent = 0.10;
        }
        $vdotRate = $assess['vdot_rate_per_week'] ?? 0.5;
        $maxAllowedByRate = $vdotRate * $weeksUntilRace;
        $maxByRatePercent = $currentVdot > 0 ? ($maxAllowedByRate / $currentVdot) : $maxVdotImprovementPercent;
        $effectiveMaxPercent = min($maxVdotImprovementPercent, $maxByRatePercent);
        $safeTargetVdot = min($targetVdot, $currentVdot * (1 + $effectiveMaxPercent));

        $isTropical = filter_var($validated['is_tropical'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $includeStrength = filter_var($validated['include_strength'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $strengthType = $validated['strength_type'] ?? 'hybrid';
        $combinePlyometric = filter_var($validated['combine_plyometric'] ?? ($strengthType === 'hybrid'), FILTER_VALIDATE_BOOLEAN);
        if ($combinePlyometric && $strengthType !== 'hybrid') {
            $strengthType = 'hybrid';
        }

        $heightCm = isset($validated['height_cm']) ? (float) $validated['height_cm'] : null;
        $weightKg = isset($validated['weight_kg']) ? (float) $validated['weight_kg'] : null;
        $heightM = ($heightCm && $heightCm > 0) ? ($heightCm / 100) : null;
        $bmi = ($heightM && $weightKg && $weightKg > 0) ? round($weightKg / ($heightM * $heightM), 1) : null;

        $proteinRecommendation = null;
        if ($weightKg && $weightKg > 0) {
            $level = $validated['runner_level'];
            [$minF, $maxF] = match ($level) {
                'beginner' => [1.4, 1.6],
                'advanced' => [1.8, 2.0],
                default => [1.6, 1.8],
            };
            $proteinRecommendation = [
                'min' => (int) round($weightKg * $minF),
                'max' => (int) round($weightKg * $maxF),
                'note' => "Berdasarkan berat {$weightKg}kg x {$minF}-{$maxF} g/kg (standar ACSM)",
            ];
        }

        $paces = $this->danielsService->calculateTrainingPaces($currentVdot);
        if ($isTropical) {
            $paces['E'] *= 1.05;
            $paces['M'] *= 1.05;
            $paces['T'] *= 1.04;
            $paces['I'] *= 1.03;
            $paces['R'] *= 1.02;
        }
        if (!empty($paces['is_run_walk'])) {
            $paces['E'] = min($paces['E'], 8.50);
            if (isset($paces['E_high'])) $paces['E_high'] = min($paces['E_high'], 8.25);
            if (isset($paces['E_low'])) $paces['E_low'] = min($paces['E_low'], 8.50);
        }

        $age = (int) $validated['age'];
        $maxHr = (int) round(208 - (0.7 * $age));
        $hrZones = [
            'E' => ['min' => (int) round($maxHr * 0.60), 'max' => (int) round($maxHr * 0.79), 'label' => 'Zone 2 (Aerobic Base)'],
            'M' => ['min' => (int) round($maxHr * 0.80), 'max' => (int) round($maxHr * 0.85), 'label' => 'Zone 3 (Tempo/Marathon)'],
            'T' => ['min' => (int) round($maxHr * 0.88), 'max' => (int) round($maxHr * 0.92), 'label' => 'Zone 4 (Lactate Threshold)'],
            'I' => ['min' => (int) round($maxHr * 0.93), 'max' => (int) round($maxHr * 0.97), 'label' => 'Zone 5 (VO2 Max)'],
            'R' => ['min' => (int) round($maxHr * 0.98), 'max' => (int) $maxHr, 'label' => 'Zone 5+ (Anaerobic Capacity)'],
        ];

        $programData = $this->builderService->build([
            'target_distance' => $validated['target_distance'],
            'weekly_mileage' => $weeklyMileageInput,
            'frequency' => $frequencyInput,
            'weeks' => $weeksUntilRace,
            'initial_vdot' => $currentVdot,
            'target_vdot' => $safeTargetVdot,
            'runner_level' => $runnerLevelInput,
            'long_run_day' => $validated['long_run_day'],
            'is_tropical' => $isTropical,
            'include_strength' => $includeStrength,
            'strength_type' => $strengthType,
            'combine_plyometric' => $combinePlyometric,
            'injury_history' => $injuryHistory,
            'aggressiveness' => $aggressiveness,
            'feasibility_assess' => $assess,
            'force_infeasible_ack' => $forceAck,
        ]);

        $sessions = $programData['sessions'] ?? [];

        if ($useAi && is_array($sessions) && $sessions) {
            $sessions = $this->improveProgramSessionsWithAi($sessions, [
                'target_distance' => $validated['target_distance'],
                'weeks' => $weeksUntilRace,
                'weekly_mileage' => (float) $validated['weekly_mileage'],
                'frequency' => (int) $validated['frequency'],
                'runner_level' => $validated['runner_level'],
                'long_run_day' => $validated['long_run_day'],
                'initial_vdot' => (float) $currentVdot,
                'target_vdot' => (float) $safeTargetVdot,
                'is_tropical' => $isTropical,
                'paces' => $paces,
            ]);
        }

        $adjustments = $programData['adjustments_applied'] ?? [];
        $feasibilityWarnings = array_values(array_filter([
            !empty($adjustments) ? 'Beban latihan disesuaikan otomatis agar aman dan memadai.' : null,
            (!empty($assess['feasibility']) && $assess['feasibility'] !== 'FEASIBLE') ? $assess['reason'] : null,
        ]));

        return [
            'status' => 200,
            'body' => [
                'success' => true,
                'data' => [
                    'vdot' => round($currentVdot, 1),
                    'paces' => $paces,
                    'hr_zones' => $hrZones,
                    'bmi' => $bmi,
                    'protein_recommendation' => $proteinRecommendation,
                    'injury_history' => $injuryHistory,
                    'include_strength' => $includeStrength,
                    'strength_type' => $strengthType,
                    'combine_plyometric' => $combinePlyometric,
                    'weeks' => $weeksUntilRace,
                    'sessions' => $sessions,
                    'feasibility' => $assess,
                    'adjustments_applied' => $adjustments,
                    'feasibility_warnings' => $feasibilityWarnings,
                    'summary' => [
                        'total_weeks' => $weeksUntilRace,
                        'target' => strtoupper($validated['target_distance']),
                        'vdot' => round($currentVdot, 1),
                        'target_vdot' => round($safeTargetVdot, 1),
                        'adjustments_applied' => $adjustments,
                        'feasibility' => [
                            'label' => $assess['label'] ?? 'Realistis',
                            'color' => $assess['color'] ?? 'emerald',
                            'score' => $assess['score'] ?? 100,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * AJAX: Multi-turn private AI coaching chat for /buat-program-lari modal
     */
    public function aiChatTurn(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:20',
            'state' => 'nullable|array',
            'action' => 'nullable|string|in:message,use_safe_goal,extend_weeks_16,force_safe_build',
        ]);

        $userMessage = trim($validated['message']);
        $state = is_array($validated['state'] ?? null) ? $validated['state'] : [];
        $action = $validated['action'] ?? 'message';

        // Pre-populate authenticated user defaults if empty
        if (auth()->check()) {
            $user = auth()->user();
            if (empty($state['gender']) && in_array($user->gender, ['male', 'female'], true)) {
                $state['gender'] = $user->gender;
            }
            if (empty($state['age']) && $user->date_of_birth) {
                $uAge = Carbon::parse($user->date_of_birth)->age;
                if ($uAge >= 10 && $uAge <= 100) {
                    $state['age'] = $uAge;
                }
            }
        }

        // Apply quick action overrides if user confirmed a feasibility adjustment
        if ($action === 'extend_weeks_16') {
            $state['weeks'] = 16;
            $state['target_date'] = now()->startOfWeek()->addWeeks(16)->toDateString();
        } elseif ($action === 'force_safe_build') {
            $state['force_infeasible_ack'] = true;
        } elseif ($action === 'use_safe_goal' && !empty($state['safe_goal_time'])) {
            $state['goal_time'] = $state['safe_goal_time'];
            $state['force_infeasible_ack'] = true;
        }

        $aiResult = null;
        if ($action === 'message') {
            $hadPbBefore = !empty($state['pb_time']);
            $hadGoalBefore = !empty($state['goal_time']);
            $history = is_array($validated['history'] ?? null) ? $validated['history'] : [];
            $history[] = ['role' => 'user', 'content' => $userMessage];
            $user = auth()->user();
            if ($user && $this->continuityService->hasRemainingQuota($user)) {
                $aiResult = $this->openAiService->extractCoachProgramTurn($history, $state, $user);
            }
            if (is_array($aiResult['extracted_params'] ?? null)) {
                $extracted = $aiResult['extracted_params'];
                // Prevent LLM from echoing goal_time into pb_time when user only stated a goal
                if (!$hadPbBefore && !$hadGoalBefore && !preg_match('/\b(pb|personal\s*best|catatan|rekor|terakhir|sekarang|saat\s*ini)\b/i', $userMessage)) {
                    unset($extracted['pb_time'], $extracted['pb_distance']);
                }
                $state = $this->mergeCoachState($state, $extracted);
            }
            $state = $this->parseDeterministicCoachInput($userMessage, $state, $hadGoalBefore);
            if (!empty($aiResult['coach_notes']) && is_string($aiResult['coach_notes'])) {
                $state['coach_notes'] = trim($aiResult['coach_notes']);
            }
        }

        // Infer runner_level from PB VDOT once PB is known and level not explicitly provided
        if (empty($state['runner_level']) && !empty($state['pb_distance']) && !empty($state['pb_time'])) {
            $estVdot = $this->danielsService->calculateVDOT($state['pb_time'], $state['pb_distance']);
            $state['runner_level'] = $estVdot < 40 ? 'beginner' : ($estVdot < 55 ? 'intermediate' : 'advanced');
        }

        // Sensible defaults for optional coaching fields once primary params exist
        if (!isset($state['is_tropical'])) {
            $state['is_tropical'] = true;
        }
        if (empty($state['long_run_day']) && !empty($state['frequency'])) {
            $state['long_run_day'] = 'sunday';
        }

        $missing = $this->findMissingCoachFields($state);

        if (!empty($missing)) {
            $fallback = $this->buildMissingFieldPrompt($missing[0], $state);
            $reply = (!empty($aiResult['assistant_reply']) && is_string($aiResult['assistant_reply']))
                ? trim($aiResult['assistant_reply'])
                : $fallback['reply'];
            $quickReplies = (!empty($aiResult['quick_replies']) && is_array($aiResult['quick_replies']))
                ? array_values(array_slice(array_filter($aiResult['quick_replies'], 'is_string'), 0, 4))
                : $fallback['quick_replies'];

            return response()->json([
                'success' => true,
                'ready_program' => false,
                'needs_confirmation' => false,
                'assistant_reply' => $reply,
                'quick_replies' => $quickReplies,
                'state' => $state,
                'missing_fields' => $missing,
            ]);
        }

        // All required parameters are collected -> normalize payload & run feasibility + builder
        $weeks = max(8, min(24, (int) ($state['weeks'] ?? 12)));
        $startDate = now()->dayOfWeekIso <= 3
            ? now()->startOfWeek()->toDateString()
            : now()->next(Carbon::MONDAY)->toDateString();
        $targetDate = !empty($state['target_date'])
            ? Carbon::parse($state['target_date'])->toDateString()
            : Carbon::parse($startDate)->addWeeks($weeks)->subDay()->toDateString();

        $formPayload = [
            'pb_distance' => $state['pb_distance'],
            'pb_time' => $state['pb_time'],
            'start_date' => $startDate,
            'target_distance' => $state['target_distance'],
            'target_date' => $targetDate,
            'goal_time' => $state['goal_time'],
            'weekly_mileage' => (float) $state['weekly_mileage'],
            'frequency' => (int) $state['frequency'],
            'runner_level' => $state['runner_level'] ?: 'intermediate',
            'long_run_day' => $state['long_run_day'] ?: 'sunday',
            'gender' => $state['gender'] ?: 'male',
            'age' => (int) ($state['age'] ?? 28),
            'injury_history' => $state['injury_history'] ?: 'none',
            'include_strength' => true,
            'strength_type' => 'hybrid',
            'combine_plyometric' => true,
            'is_tropical' => (bool) ($state['is_tropical'] ?? true),
            'aggressiveness' => $state['aggressiveness'] ?? 'standard',
            'force_infeasible_ack' => (bool) ($state['force_infeasible_ack'] ?? false),
        ];

        $built = $this->buildProgramPayload($formPayload, false);

        if ($built['status'] === 422) {
            $body = $built['body'];
            $currentVdot = (float) ($body['current_vdot'] ?? 35);
            $safePct = match ($formPayload['runner_level']) {
                'beginner' => 0.06,
                'advanced' => 0.10,
                default => 0.08,
            };
            $safeVdot = min(85, $currentVdot * (1 + min($safePct, ($weeks * 0.5) / max(1, $currentVdot))));
            $eqTimes = $this->danielsService->calculateEquivalentRaceTimes($safeVdot);
            $distKey = $formPayload['target_distance'] === 'cooper12' ? '5k' : $formPayload['target_distance'];
            $safeGoalTime = $eqTimes[$distKey]['time'] ?? '00:25:00';
            $state['safe_goal_time'] = $safeGoalTime;

            $reason = $body['message'] ?? 'Target saat ini terlalu agresif untuk durasi dan volume latihan Anda.';

            return response()->json([
                'success' => true,
                'ready_program' => false,
                'needs_confirmation' => true,
                'assistant_reply' => "Analisis kelayakan Coach: {$reason}\n\nAgar progres tetap aman tanpa risiko cedera, pilih salah satu penyesuaian berikut (atau saya bisa susun dengan target aman {$safeGoalTime} terlebih dahulu):",
                'quick_replies' => [],
                'confirmation_actions' => [
                    ['action' => 'use_safe_goal', 'label' => "Pakai Target Aman ({$safeGoalTime})"],
                    ['action' => 'extend_weeks_16', 'label' => 'Perpanjang Persiapan Jadi 16 Minggu'],
                    ['action' => 'force_safe_build', 'label' => 'Lanjutkan dengan Penyesuaian Beban Aman'],
                ],
                'state' => $state,
                'missing_fields' => [],
            ]);
        }

        $data = $built['body']['data'];
        if (!empty($state['coach_notes'])) {
            $data['coach_notes'] = $state['coach_notes'];
        }

        $savedToCalendar = false;
        $enrollmentId = null;
        $requiresPayment = false;
        $hasActiveProgram = false;
        $activeProgramTitle = null;

        if (auth()->check()) {
            $user = auth()->user();
            $activeEnrollment = ProgramEnrollment::where('runner_id', $user->id)
                ->where('status', 'active')
                ->with('program')
                ->first();

            if ($activeEnrollment) {
                $hasActiveProgram = true;
                $activeProgramTitle = $activeEnrollment->program?->title ?? 'Program Aktif';
            } else {
                $enrollment = $this->saveProgramFromData($user, ['form' => $formPayload, 'result' => $data], 'add');
                if ($enrollment) {
                    $savedToCalendar = true;
                    $enrollmentId = $enrollment->id;
                    $requiresPayment = ($enrollment->payment_status !== 'paid');
                }
            }
        }

        $totalSessions = is_array($data['sessions'] ?? null) ? count($data['sessions']) : 0;
        if ($savedToCalendar && !$requiresPayment) {
            $summaryReply = "Program latihan {$data['summary']['target']} selama {$data['weeks']} minggu ({$totalSessions} hari jadwal) sudah selesai disusun (Free Trial) dan OTOMATIS diterapkan ke My Program & Kalender Dashboard Anda (VDOT awal {$data['summary']['vdot']} -> target {$data['summary']['target_vdot']}).";
        } elseif ($savedToCalendar && $requiresPayment) {
            $summaryReply = "Program latihan {$data['summary']['target']} selama {$data['weeks']} minggu ({$totalSessions} hari jadwal) sudah selesai disusun (VDOT awal {$data['summary']['vdot']} -> target {$data['summary']['target_vdot']}). Karena jatah Free Trial Anda sudah digunakan, silakan selesaikan pembayaran Rp 15.000 (QRIS) untuk membuka seluruh jadwal dan pendampingan penuh Coach Ruang Lari di Kalender Dashboard.";
        } elseif ($hasActiveProgram) {
            $summaryReply = "Program latihan {$data['summary']['target']} selama {$data['weeks']} minggu ({$totalSessions} hari jadwal) sudah siap (VDOT awal {$data['summary']['vdot']} -> target {$data['summary']['target_vdot']}). Karena Anda masih memiliki program aktif (\"{$activeProgramTitle}\"), silakan pilih ingin mengganti atau menambahkan ke Kalender Dashboard.";
        } else {
            $summaryReply = "Program latihan {$data['summary']['target']} selama {$data['weeks']} minggu ({$totalSessions} hari jadwal) sudah siap.";
        }

        return response()->json([
            'success' => true,
            'ready_program' => true,
            'needs_confirmation' => false,
            'saved_to_calendar' => $savedToCalendar,
            'enrollment_id' => $enrollmentId,
            'requires_payment' => $requiresPayment,
            'price_idr' => AiCoachContinuityService::PROGRAM_PRICE_IDR,
            'has_trial' => auth()->check() ? $this->continuityService->hasAvailableTrial(auth()->user()) : false,
            'quota' => auth()->check() ? $this->continuityService->getDailyQuotaStatus(auth()->user()) : null,
            'has_active_program' => $hasActiveProgram,
            'active_program_title' => $activeProgramTitle,
            'calendar_url' => route('runner.calendar'),
            'assistant_reply' => $summaryReply,
            'quick_replies' => [],
            'state' => $state,
            'form_payload' => $formPayload,
            'program_data' => $data,
        ]);
    }

    private function mergeCoachState(array $state, array $extracted): array
    {
        $validDistances = ['5k', '10k', '21k', '42k'];
        $validPbDistances = ['5k', '10k', '21k', '42k', 'cooper12', 'balke15'];

        if (!empty($extracted['target_distance']) && in_array(strtolower((string) $extracted['target_distance']), $validDistances, true)) {
            $state['target_distance'] = strtolower((string) $extracted['target_distance']);
        }
        if (!empty($extracted['goal_time'])) {
            $t = $this->normalizeTimeString((string) $extracted['goal_time']);
            if ($t) $state['goal_time'] = $t;
        }
        if (!empty($extracted['pb_distance']) && in_array(strtolower((string) $extracted['pb_distance']), $validPbDistances, true)) {
            $state['pb_distance'] = strtolower((string) $extracted['pb_distance']);
        }
        if (!empty($extracted['pb_time'])) {
            $pbDist = $state['pb_distance'] ?? '5k';
            if (in_array($pbDist, ['cooper12', 'balke15'], true) && is_numeric($extracted['pb_time'])) {
                $state['pb_time'] = (string) ((int) $extracted['pb_time']);
            } else {
                $t = $this->normalizeTimeString((string) $extracted['pb_time']);
                if ($t) $state['pb_time'] = $t;
            }
        }
        if (isset($extracted['weekly_mileage']) && is_numeric($extracted['weekly_mileage']) && (float) $extracted['weekly_mileage'] >= 5) {
            $state['weekly_mileage'] = min(200, max(5, (float) $extracted['weekly_mileage']));
        }
        if (isset($extracted['frequency']) && is_numeric($extracted['frequency'])) {
            $state['frequency'] = min(7, max(3, (int) $extracted['frequency']));
        }
        if (!empty($extracted['long_run_day']) && in_array(strtolower((string) $extracted['long_run_day']), ['saturday', 'sunday'], true)) {
            $state['long_run_day'] = strtolower((string) $extracted['long_run_day']);
        }
        if (isset($extracted['weeks']) && is_numeric($extracted['weeks'])) {
            $state['weeks'] = min(24, max(8, (int) $extracted['weeks']));
        }
        if (!empty($extracted['target_date'])) {
            try {
                $d = Carbon::parse((string) $extracted['target_date']);
                if ($d->isFuture()) {
                    $state['target_date'] = $d->toDateString();
                    $state['weeks'] = min(24, max(8, (int) ceil(now()->diffInDays($d) / 7)));
                }
            } catch (\Throwable $e) {}
        }
        if (!empty($extracted['runner_level']) && in_array(strtolower((string) $extracted['runner_level']), ['beginner', 'intermediate', 'advanced'], true)) {
            $state['runner_level'] = strtolower((string) $extracted['runner_level']);
        }
        if (isset($extracted['age']) && is_numeric($extracted['age'])) {
            $age = (int) $extracted['age'];
            if ($age >= 10 && $age <= 100) $state['age'] = $age;
        }
        if (!empty($extracted['gender']) && in_array(strtolower((string) $extracted['gender']), ['male', 'female'], true)) {
            $state['gender'] = strtolower((string) $extracted['gender']);
        }
        if (!empty($extracted['injury_history']) && in_array(strtolower((string) $extracted['injury_history']), ['none', 'knee', 'hamstring', 'ankle', 'shin', 'back'], true)) {
            $state['injury_history'] = strtolower((string) $extracted['injury_history']);
        }
        if (isset($extracted['is_tropical'])) {
            $state['is_tropical'] = filter_var($extracted['is_tropical'], FILTER_VALIDATE_BOOLEAN);
        }
        if (!empty($extracted['aggressiveness']) && in_array(strtolower((string) $extracted['aggressiveness']), ['conservative', 'standard', 'sharp'], true)) {
            $state['aggressiveness'] = strtolower((string) $extracted['aggressiveness']);
        }

        return $state;
    }

    private function parseDeterministicCoachInput(string $message, array $state, bool $goalSetEarlier = false): array
    {
        $lower = mb_strtolower($message);

        // Target distance detection when target_distance is empty
        if (empty($state['target_distance'])) {
            if (preg_match('/\b(42k|42\.2k|full\s*marathon|marathon|fm)\b/i', $lower)) {
                $state['target_distance'] = '42k';
            } elseif (preg_match('/\b(21k|21\.1k|half\s*marathon|hm)\b/i', $lower)) {
                $state['target_distance'] = '21k';
            } elseif (preg_match('/\b10k\b|\b10\s*km\b/i', $lower)) {
                $state['target_distance'] = '10k';
            } elseif (preg_match('/\b5k\b|\b5\s*km\b/i', $lower)) {
                $state['target_distance'] = '5k';
            }
        }

        $mentionsPbExplicitly = (bool) preg_match('/\b(pb|personal\s*best|catatan|rekor|terbaik|terakhir)\b/i', $lower);

        // Goal time patterns like "sub 20 menit", "19:59", "01:45:00"
        if (empty($state['goal_time']) && !$mentionsPbExplicitly) {
            if (preg_match('/\b(?:sub|target|di\s*bawah)?\s*(\d{1,2})\s*jam(?:\s*(\d{1,2})\s*menit)?\b/i', $lower, $m)) {
                $h = (int) $m[1];
                $min = isset($m[2]) ? (int) $m[2] : 0;
                $sec = $min === 0 ? 59 : 0;
                if ($min === 0 && $h > 0) {
                    $h -= 1;
                    $min = 59;
                }
                $state['goal_time'] = sprintf('%02d:%02d:%02d', max(0, $h), $min, $sec);
            } elseif (preg_match('/\b(?:sub|target)?\s*(\d{1,2})\s*menit\b/i', $lower, $m) && empty($state['pb_time'])) {
                $min = (int) $m[1];
                $isSub = str_contains($lower, 'sub') || str_contains($lower, 'bawah');
                $totalSec = $isSub ? max(300, ($min * 60) - 1) : ($min * 60);
                $state['goal_time'] = $this->formatSecondsToTime($totalSec);
            } elseif (preg_match('/\b(\d{1,2}:\d{2}(?::\d{2})?)\b/', $lower, $m)) {
                $norm = $this->normalizeTimeString($m[1]);
                if ($norm) {
                    $state['goal_time'] = $norm;
                }
            }
        }

        // PB time ONLY when goal was already set in an earlier turn OR "pb" explicitly mentioned
        if ((empty($state['pb_time']) && ($goalSetEarlier || $mentionsPbExplicitly)) || preg_match('/\b(pb|personal\s*best)\b/i', $lower)) {
            if (empty($state['pb_distance']) || preg_match('/\b(pb|personal\s*best)\b/i', $lower)) {
                if (preg_match('/\b(?:pb|personal\s*best)?\s*(42k|42\.2k|marathon|fm)\b/i', $lower)) $state['pb_distance'] = '42k';
                elseif (preg_match('/\b(?:pb|personal\s*best)?\s*(21k|21\.1k|half\s*marathon|hm)\b/i', $lower)) $state['pb_distance'] = '21k';
                elseif (preg_match('/\b(?:pb|personal\s*best)?\s*(10k|10\s*km)\b/i', $lower)) $state['pb_distance'] = '10k';
                elseif (preg_match('/\b(?:pb|personal\s*best)?\s*(5k|5\s*km)\b/i', $lower)) $state['pb_distance'] = '5k';
            }
            if (preg_match('/\b(\d{1,2}:\d{2}(?::\d{2})?)\b/', $lower, $m)) {
                $norm = $this->normalizeTimeString($m[1]);
                if ($norm && ($goalSetEarlier || $mentionsPbExplicitly)) {
                    $state['pb_time'] = $norm;
                    if (empty($state['pb_distance'])) {
                        $state['pb_distance'] = $state['target_distance'] ?? '5k';
                    }
                }
            } elseif (preg_match('/\b(\d{1,2})\s*menit\b/i', $lower, $m) && ($goalSetEarlier || $mentionsPbExplicitly)) {
                $state['pb_time'] = $this->formatSecondsToTime(((int) $m[1]) * 60);
                if (empty($state['pb_distance'])) {
                    $state['pb_distance'] = $state['target_distance'] ?? '5k';
                }
            }
        }

        if (empty($state['weekly_mileage']) && preg_match('/(\d+(?:\.\d+)?)\s*km/i', $lower, $m)) {
            $km = (float) $m[1];
            if ($km >= 5 && $km <= 200) $state['weekly_mileage'] = $km;
        }
        if (empty($state['frequency']) && preg_match('/\b([3-7])\s*(?:x|kali|hari)\b/i', $lower, $m)) {
            $state['frequency'] = (int) $m[1];
        }
        if (empty($state['long_run_day'])) {
            if (str_contains($lower, 'sabtu')) $state['long_run_day'] = 'saturday';
            elseif (str_contains($lower, 'minggu') && !preg_match('/\d+\s*minggu/i', $lower)) $state['long_run_day'] = 'sunday';
        }
        if (empty($state['weeks']) && preg_match('/\b(\d{1,2})\s*(?:minggu|pekan|week)\b/i', $lower, $m)) {
            $state['weeks'] = min(24, max(8, (int) $m[1]));
        }
        if (empty($state['age']) && preg_match('/\b(\d{2})\s*(?:tahun|thn)\b/i', $lower, $m)) {
            $age = (int) $m[1];
            if ($age >= 10 && $age <= 100) $state['age'] = $age;
        }
        if (empty($state['gender'])) {
            if (preg_match('/\b(pria|laki-laki|cowo|male)\b/i', $lower)) $state['gender'] = 'male';
            elseif (preg_match('/\b(wanita|perempuan|cewe|female)\b/i', $lower)) $state['gender'] = 'female';
        }
        if (empty($state['injury_history'])) {
            if (preg_match('/tidak\s+ada\s+cedera|tanpa\s+cedera|aman|sehat/i', $lower)) $state['injury_history'] = 'none';
            elseif (str_contains($lower, 'lutut') || str_contains($lower, 'knee')) $state['injury_history'] = 'knee';
            elseif (str_contains($lower, 'hamstring')) $state['injury_history'] = 'hamstring';
            elseif (str_contains($lower, 'ankle') || str_contains($lower, 'engkel')) $state['injury_history'] = 'ankle';
            elseif (str_contains($lower, 'shin') || str_contains($lower, 'tulang kering')) $state['injury_history'] = 'shin';
            elseif (str_contains($lower, 'punggung') || str_contains($lower, 'pinggang')) $state['injury_history'] = 'back';
        }

        return $state;
    }

    private function findMissingCoachFields(array $state): array
    {
        $required = ['target_distance', 'goal_time', 'pb_distance', 'pb_time', 'weekly_mileage', 'frequency', 'weeks', 'age', 'gender', 'injury_history'];
        $missing = [];
        foreach ($required as $key) {
            if (!isset($state[$key]) || $state[$key] === '' || $state[$key] === null) {
                $missing[] = $key;
            }
        }
        return $missing;
    }

    private function buildMissingFieldPrompt(string $firstMissing, array $state): array
    {
        return match ($firstMissing) {
            'target_distance', 'goal_time' => [
                'reply' => 'Halo, saya Coach AI RuangLari. Target jarak lomba dan catatan waktu apa yang ingin Anda kejar saat ini?',
                'quick_replies' => ['5K sub 20 menit', '10K sub 50 menit', 'Half Marathon sub 2 jam', 'Marathon sub 4 jam'],
            ],
            'pb_distance', 'pb_time' => [
                'reply' => 'Baik, target sudah dicatat. Agar zona pace presisi, berapa catatan waktu terbaik (PB) atau lari maksimal Anda dalam 3-6 bulan terakhir (beserta jaraknya)?',
                'quick_replies' => ['PB 5K 21:30', 'PB 5K 25:00', 'PB 10K 52:00', 'PB Half Marathon 1:55:00'],
            ],
            'weekly_mileage', 'frequency' => [
                'reply' => 'Oke, PB sudah saya kunci. Saat ini rata-rata berapa kilometer jarak lari Anda per minggu dan sanggup latihan berapa hari per minggu (long run Sabtu atau Minggu)?',
                'quick_replies' => ['25 km/minggu, 4x, Minggu', '35 km/minggu, 4x, Minggu', '45 km/minggu, 5x, Sabtu', '15 km/minggu, 3x, Minggu'],
            ],
            'weeks' => [
                'reply' => 'Berapa minggu durasi persiapan yang Anda miliki menjelang hari lomba (minimal 8 minggu, ideal 12-16 minggu)?',
                'quick_replies' => ['8 minggu', '12 minggu', '16 minggu', '20 minggu'],
            ],
            default => [
                'reply' => 'Terakhir, sebutkan usia, jenis kelamin, dan apakah Anda memiliki riwayat cedera tertentu (misal lutut, shin splint, ankle, atau tidak ada cedera)?',
                'quick_replies' => ['30 tahun, pria, tidak ada cedera', '28 tahun, wanita, tidak ada cedera', '35 tahun, pria, riwayat lutut'],
            ],
        };
    }

    private function normalizeTimeString(string $raw): ?string
    {
        $t = trim($raw);
        if (!preg_match('/^(\d{1,2}:)?\d{1,2}:\d{2}$/', $t)) {
            return null;
        }
        $parts = explode(':', $t);
        if (count($parts) === 2) {
            return sprintf('00:%02d:%02d', (int) $parts[0], (int) $parts[1]);
        }
        return sprintf('%02d:%02d:%02d', (int) $parts[0], (int) $parts[1], (int) $parts[2]);
    }

    private function formatSecondsToTime(int $totalSeconds): string
    {
        $totalSeconds = max(60, $totalSeconds);
        $h = intdiv($totalSeconds, 3600);
        $m = intdiv($totalSeconds % 3600, 60);
        $s = $totalSeconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    private function parseTimeToSeconds(string $timeStr): int
    {
        $t = trim($timeStr);
        if ($t === '') return 0;
        $parts = explode(':', $t);
        $parts = array_map('intval', $parts);
        $n = count($parts);
        if ($n === 3) {
            return max(0, ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2]);
        }
        if ($n === 2) {
            return max(0, ($parts[0] * 60) + $parts[1]);
        }
        return 0;
    }

    /**
     * AJAX: Verify if a phone number is a valid promo code (event participant)
     */
    public function verifyPromoCode(Request $request)
    {
        $code = $request->code;
        if (!$code) return response()->json(['success' => false, 'message' => 'Kode promo wajib diisi']);

        // 1. Check if phone exists in paid participants of active events
        $participant = Participant::where('phone', $code)
            ->whereHas('transaction', function($q) {
                $q->whereIn('payment_status', ['paid', 'settlement', 'capture']);
            })
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Nomor HP tidak terdaftar sebagai peserta event aktif']);
        }

        // 2. Check if this code (phone) has been used before for generator
        $isUsed = ProgramEnrollment::where('promo_code_used', $code)
            ->where('payment_status', 'paid')
            ->exists();

        if ($isUsed) {
            return response()->json(['success' => false, 'message' => 'Kode promo ini sudah pernah digunakan']);
        }

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Unlock program using promo code
     */
    public function unlockWithPromo(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:program_enrollments,id',
            'code' => 'required|string'
        ]);

        $user = auth()->user();
        $enrollment = ProgramEnrollment::findOrFail($request->enrollment_id);
        
        if ($enrollment->runner_id !== $user->id) abort(403);

        // Re-verify logic (security)
        $participant = Participant::where('phone', $request->code)
            ->whereHas('transaction', function($q) {
                $q->whereIn('payment_status', ['paid', 'settlement', 'capture']);
            })
            ->first();

        if (!$participant) {
            return response()->json(['success' => false, 'message' => 'Verifikasi gagal']);
        }

        $isUsed = ProgramEnrollment::where('promo_code_used', $request->code)
            ->where('payment_status', 'paid')
            ->exists();

        if ($isUsed) {
            return response()->json(['success' => false, 'message' => 'Kode sudah digunakan']);
        }

        // Process Unlock
        $enrollment->update([
            'payment_status' => 'paid',
            'promo_code_used' => $request->code,
            'payment_method' => 'promo_code'
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * AJAX: Save generated program to user's calendar
     */
    public function saveToCalendar(Request $request)
    {
        if ($request->has('action') && !in_array($request->input('action'), ['replace', 'add'], true)) {
            $request->merge(['action' => null]);
        }

        $user = auth()->user();
        $validated = $request->validate([
            'form' => 'required|array',
            'result' => 'required|array',
            'action' => 'nullable|in:replace,add',
        ]);

        $form = $validated['form'];
        $result = $validated['result'];
        $action = $validated['action'] ?? null;

        // Check if user already has an active program enrollment
        $activeEnrollment = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->with('program')
            ->first();

        if ($activeEnrollment && !$action) {
            return response()->json([
                'success' => false,
                'has_active_program' => true,
                'active_program_title' => $activeEnrollment->program?->title ?? 'Program Aktif',
                'active_start_date' => $activeEnrollment->start_date ? Carbon::parse($activeEnrollment->start_date)->format('d M Y') : '',
                'active_end_date' => $activeEnrollment->end_date ? Carbon::parse($activeEnrollment->end_date)->format('d M Y') : '',
                'message' => 'Anda saat ini memiliki program aktif di kalender.'
            ]);
        }

        $enrollment = $this->saveProgramFromData($user, ['form' => $form, 'result' => $result], $action ?: 'add');

        if ($enrollment) {
            return response()->json([
                'success' => true,
                'enrollment_id' => $enrollment->id,
                'requires_payment' => ($enrollment->payment_status !== 'paid'),
                'price_idr' => AiCoachContinuityService::PROGRAM_PRICE_IDR,
                'message' => $enrollment->payment_status === 'paid'
                    ? 'Program berhasil disimpan ke kalender!'
                    : 'Program tersimpan. Selesaikan pembayaran Rp 15.000 untuk membuka seluruh minggu di kalender.',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Gagal menyimpan program.'], 500);
    }

    /**
     * AJAX: Create Midtrans Payment (Rp 15.000 via QRIS / Snap)
     */
    public function createPayment(Request $request)
    {
        $user = auth()->user();
        $enrollmentId = $request->enrollment_id;
        $amount = (int) ($request->amount ?? AiCoachContinuityService::PROGRAM_PRICE_IDR);
        if ($amount < AiCoachContinuityService::PROGRAM_PRICE_IDR) {
            $amount = AiCoachContinuityService::PROGRAM_PRICE_IDR;
        }

        $enrollment = ProgramEnrollment::with('program')->findOrFail($enrollmentId);
        if ($enrollment->runner_id !== $user->id) abort(403);

        $orderId = 'GEN-PROG-' . $enrollment->id . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'enabled_payments' => ['other_qris', 'gopay', 'shopeepay', 'bank_transfer'],
            'item_details' => [
                [
                    'id' => 'COACH-PROG-UNLOCK',
                    'price' => $amount,
                    'quantity' => 1,
                    'name' => mb_substr($enrollment->program?->title ?? 'Program Lari Coach Ruang Lari', 0, 50),
                ]
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            $enrollment->update([
                'payment_transaction_id' => null,
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken,
                'amount' => $amount,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Webhook for Midtrans Program Payment (with signature verification)
     */
    public function webhook(Request $request)
    {
        $orderId = (string) $request->input('order_id', '');
        $statusCode = (string) $request->input('status_code', '');
        $grossAmount = (string) $request->input('gross_amount', '');
        $signatureKey = (string) $request->input('signature_key', '');
        $serverKey = (string) config('midtrans.server_key');

        if ($serverKey !== '' && $signatureKey !== '') {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            if (! hash_equals($expectedSignature, $signatureKey)) {
                Log::warning("Midtrans invalid signature for order_id: {$orderId}");
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        $status = (string) $request->input('transaction_status', '');

        // Order ID format: GEN-PROG-{enrollment_id}-{time} or GEN-DONATE-{enrollment_id}-{time}
        $parts = explode('-', $orderId);
        if (count($parts) < 3) return response()->json(['message' => 'Invalid Order ID'], 400);

        $enrollmentId = $parts[2];
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->find($enrollmentId);

        if (!$enrollment) return response()->json(['message' => 'Enrollment not found'], 404);

        if ($status === 'capture' || $status === 'settlement') {
            $wasUnpaid = ($enrollment->payment_status !== 'paid');
            $enrollment->update([
                'payment_status' => 'paid',
                'payment_transaction_id' => $request->input('transaction_id'),
            ]);

            if ($wasUnpaid && $enrollment->runner && $enrollment->program) {
                $this->continuityService->postProgramWelcomeMessage($enrollment->runner, $enrollment->program, $enrollment);
            }

            Log::info("Program payment success for Enrollment ID: " . $enrollmentId);
        }

        return response()->json(['status' => 'ok']);
    }


    // =========================================================================
    // AI DESCRIPTION ENHANCEMENT
    // =========================================================================

    private function improveProgramSessionsWithAi(array $sessions, array $context): array
    {
        $hasKey = (bool) config('services.openai.api_key');
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

        sort($combinations);
        $cacheKey = 'program_ai_templates_v1_' . md5(json_encode([
            'level' => $context['runner_level'] ?? 'intermediate',
            'distance' => $context['target_distance'] ?? '10k',
            'tropical' => (bool) ($context['is_tropical'] ?? false),
            'combinations' => $combinations,
        ]));

        $cachedTemplates = Cache::get($cacheKey);
        if (is_array($cachedTemplates) && ! empty($cachedTemplates)) {
            return $this->applyAiTemplatesToSessions($sessions, $cachedTemplates);
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
            
            // Clean markdown tags if OpenAI accidentally returns them
            $raw = preg_replace('/^```json\s*/i', '', $raw);
            $raw = preg_replace('/```$/', '', $raw);
            $raw = trim($raw);

            $decoded = json_decode($raw, true);
            $templates = is_array($decoded) ? ($decoded['templates'] ?? null) : null;
            if (! is_array($templates) || ! $templates) {
                return $sessions;
            }

            Cache::put($cacheKey, $templates, now()->addHours(24));

            return $this->applyAiTemplatesToSessions($sessions, $templates);
        } catch (\Throwable $e) {
            Log::warning('AI refine generator_v2 failed: '.$e->getMessage());
            return $sessions;
        }
    }

    private function applyAiTemplatesToSessions(array $sessions, array $templates): array
    {
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
