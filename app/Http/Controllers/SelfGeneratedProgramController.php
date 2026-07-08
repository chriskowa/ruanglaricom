<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Services\DanielsRunningService;
use App\Services\MidtransService;
use App\Services\OpenAiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Snap;

use App\Models\Participant;
use App\Models\UsedPromoCode; // Assuming we will create this or use a simple check

class SelfGeneratedProgramController extends Controller
{
    protected $danielsService;
    protected $midtransService;
    protected $openAiService;

    public function __construct(DanielsRunningService $danielsService, MidtransService $midtransService, OpenAiService $openAiService)
    {
        $this->danielsService = $danielsService;
        $this->midtransService = $midtransService;
        $this->openAiService = $openAiService;
    }

    public function index()
    {
        // Check if there is a pending program to save after login
        if (auth()->check() && session()->has('pending_program_data')) {
            return $this->processAutoSave();
        }

        return view('programs.generator_v2');
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
     * Internal: Process auto-save after login
     */
    protected function processAutoSave()
    {
        $data = session()->pull('pending_program_data');
        $user = auth()->user();
        $form = $data['form'];
        $result = $data['result'];

        DB::beginTransaction();
        try {
            $targetDate = Carbon::parse($form['target_date']);
            $sessions = $result['sessions'] ?? [];
            $totalDays = count($sessions);
            $durationWeeks = (int) ceil($totalDays / 7);
            
            // Default start date: subtract total days from target date
            $startDate = $targetDate->copy()->subDays($totalDays - 1)->startOfWeek();

            // 1. Create Program
            $title = "AI " . strtoupper($form['target_distance']) . " Plan (" . $result['vdot'] . ")";
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => $title,
                'slug' => $this->generateUniqueSlug($title),
                'description' => "Program latihan lari periodisasi yang di-generate menggunakan algoritma Daniels' VDOT v2.0.",
                'distance_target' => $form['target_distance'],
                'duration_weeks' => $durationWeeks,
                'program_json' => [
                    'sessions' => $result['sessions'],
                    'summary' => $result['summary'] ?? []
                ],
                'is_vdot_generated' => true,
                'vdot_score' => $result['vdot'],
                'is_active' => true,
                'is_published' => false,
                'price' => 0,
                'is_self_generated' => true,
                'generated_vdot' => $result['vdot'],
                'daniels_params' => [
                    'pb_distance' => $form['pb_distance'],
                    'pb_time' => $form['pb_time'],
                    'target_distance' => $form['target_distance'],
                    'target_date' => $form['target_date'],
                    'weekly_mileage' => $form['weekly_mileage'],
                    'frequency' => $form['frequency'],
                    'runner_level' => $form['runner_level'] ?? null,
                    'long_run_day' => $form['long_run_day'] ?? null,
                    'training_paces' => $result['paces'],
                ],
            ]);

            // 2. Create Enrollment
            $enrollment = ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => $user->id,
                'start_date' => $startDate,
                'end_date' => $targetDate,
                'status' => 'active',
                'payment_status' => 'paid', // Fully free
            ]);

            DB::commit();

            return redirect()->route('runner.calendar')->with('success', 'Program latihan Anda telah berhasil disimpan ke kalender!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto-save program failed: ' . $e->getMessage());
            return redirect()->route('programs.realistic')->with('error', 'Gagal menyimpan program secara otomatis.');
        }
    }

    /**
     * AJAX: Generate program logic based on Daniels Periodization
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'pb_distance' => 'required|in:5k,10k,21k,42k',
            'pb_time' => 'required|string|regex:/^(\d{1,2}:)?\d{1,2}:\d{2}$/',
            'target_distance' => 'required|in:5k,10k,21k,42k,cooper12',
            'target_date' => 'required|date|after_or_equal:today',
            'goal_time' => 'required|string|regex:/^(\d{1,2}:)?\d{1,2}:\d{2}$/',
            'weekly_mileage' => 'required|numeric|min:5|max:200',
            'frequency' => 'required|integer|min:3|max:7',
            'runner_level' => 'required|in:beginner,intermediate,advanced',
            'long_run_day' => 'required|in:saturday,sunday',
            'gender' => 'required|in:male,female',
            'age' => 'required|integer|min:10|max:100',
            'is_tropical' => 'nullable|boolean',
        ], [
            'pb_time.regex' => 'Format waktu PB harus HH:MM:SS atau MM:SS.',
            'goal_time.regex' => 'Format waktu Goal harus HH:MM:SS atau MM:SS.',
            'target_date.after_or_equal' => 'Tanggal race harus hari ini atau di masa depan.',
        ]);

        try {
            $currentVdot = $this->danielsService->calculateVDOT($validated['pb_time'], $validated['pb_distance']);
            $targetVdot = $this->danielsService->calculateVDOT($validated['goal_time'], $validated['target_distance']);
            
            // Limit the improvement to a realistic level based on percentage of current VDOT
            $maxVdotImprovementPercent = 0.08; // 8% for intermediate
            if ($validated['runner_level'] === 'beginner') {
                $maxVdotImprovementPercent = 0.06;
            } elseif ($validated['runner_level'] === 'advanced') {
                $maxVdotImprovementPercent = 0.10;
            }
            $safeTargetVdot = min($targetVdot, $currentVdot * (1 + $maxVdotImprovementPercent));

            $isTropical = filter_var($validated['is_tropical'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Calculate base and adjusted training paces
            $paces = $this->danielsService->calculateTrainingPaces($currentVdot);
            if ($isTropical) {
                // Tropical offset: +5% for E/M, +4% for T, +3% for I, +2% for R
                $paces['E'] *= 1.05;
                $paces['M'] *= 1.05;
                $paces['T'] *= 1.04;
                $paces['I'] *= 1.03;
                $paces['R'] *= 1.02;
            }

            // Calculate target Heart Rate zones based on age (Tanaka formula: 208 - 0.7 * age)
            $age = (int) $validated['age'];
            $maxHr = (int) round(208 - (0.7 * $age));
            $hrZones = [
                'E' => [
                    'min' => (int) round($maxHr * 0.60),
                    'max' => (int) round($maxHr * 0.79),
                    'label' => 'Zone 2 (Aerobic Base)'
                ],
                'M' => [
                    'min' => (int) round($maxHr * 0.80),
                    'max' => (int) round($maxHr * 0.85),
                    'label' => 'Zone 3 (Tempo/Marathon)'
                ],
                'T' => [
                    'min' => (int) round($maxHr * 0.88),
                    'max' => (int) round($maxHr * 0.92),
                    'label' => 'Zone 4 (Lactate Threshold)'
                ],
                'I' => [
                    'min' => (int) round($maxHr * 0.93),
                    'max' => (int) round($maxHr * 0.97),
                    'label' => 'Zone 5 (VO2 Max)'
                ],
                'R' => [
                    'min' => (int) round($maxHr * 0.98),
                    'max' => (int) $maxHr,
                    'label' => 'Zone 5+ (Anaerobic Capacity)'
                ]
            ];
            
            // Generate sessions based on distance and duration
            $targetDate = Carbon::parse($validated['target_date']);
            $weeksUntilRace = max(8, min(24, (int) ceil(now()->diffInWeeks($targetDate))));
            
            $programData = $this->buildPeriodizedProgram([
                'target_distance' => $validated['target_distance'],
                'weekly_mileage' => $validated['weekly_mileage'],
                'frequency' => $validated['frequency'],
                'weeks' => $weeksUntilRace,
                'initial_vdot' => $currentVdot,
                'target_vdot' => $safeTargetVdot,
                'runner_level' => $validated['runner_level'],
                'long_run_day' => $validated['long_run_day'],
                'is_tropical' => $isTropical,
            ]);

            $sessions = $programData['sessions'] ?? [];

            $useAi = $request->boolean('use_ai', auth()->check());
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

            return response()->json([
                'success' => true,
                'data' => [
                    'vdot' => round($currentVdot, 1),
                    'paces' => $paces,
                    'hr_zones' => $hrZones,
                    'weeks' => $weeksUntilRace,
                    'sessions' => $sessions,
                    'summary' => [
                        'total_weeks' => $weeksUntilRace,
                        'target' => strtoupper($validated['target_distance']),
                        'vdot' => round($currentVdot, 1),
                        'target_vdot' => round($safeTargetVdot, 1),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Generator Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal generate program.'], 500);
        }
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
        $user = auth()->user();
        $validated = $request->validate([
            'form' => 'required|array',
            'result' => 'required|array',
        ]);

        $form = $validated['form'];
        $result = $validated['result'];

        DB::beginTransaction();
        try {
            $targetDate = Carbon::parse($form['target_date']);
            $sessions = $result['sessions'] ?? [];
            $totalDays = count($sessions);
            $durationWeeks = (int) ceil($totalDays / 7);
            
            // Default start date: subtract total days from target date
            $startDate = $targetDate->copy()->subDays($totalDays - 1)->startOfWeek();

            // Create a virtual program record
            $title = "AI " . strtoupper($form['target_distance']) . " Plan (" . $result['vdot'] . ")";
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => $title,
                'slug' => $this->generateUniqueSlug($title),
                'description' => "AI Generated Program for " . strtoupper($form['target_distance']),
                'distance_target' => $form['target_distance'],
                'duration_weeks' => $durationWeeks,
                'program_json' => [
                    'sessions' => $result['sessions'],
                    'summary' => $result['summary'] ?? []
                ],
                'is_self_generated' => true,
                'is_active' => true,
                'is_published' => false,
                'price' => 0, 
                'generated_vdot' => $result['vdot'],
                'daniels_params' => [
                    'training_paces' => $result['paces'],
                    'runner_level' => $form['runner_level'] ?? null,
                    'long_run_day' => $form['long_run_day'] ?? null
                ]
            ]);

            // Create enrollment
            $enrollment = ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => $user->id,
                'start_date' => $startDate,
                'end_date' => $targetDate,
                'status' => 'active',
                'payment_status' => 'paid', // Fully free
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'enrollment_id' => $enrollment->id,
                'message' => 'Program berhasil disimpan ke kalender!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Save Program Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan program: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Create Midtrans Payment for donation
     */
    public function createPayment(Request $request)
    {
        $user = auth()->user();
        $enrollmentId = $request->enrollment_id;
        $amount = $request->amount ?? 25000;

        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ($enrollment->runner_id !== $user->id) abort(403);

        $orderId = 'GEN-DONATE-' . $enrollment->id . '-' . time();
        
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'item_details' => [
                [
                    'id' => 'UNLOCK-GEN',
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => 'Donasi Unlock Full Program Lari',
                ]
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            
            // Update enrollment with transaction info
            $enrollment->update([
                'payment_transaction_id' => null, // We'll link this in webhook
            ]);

            return response()->json([
                'success' => true,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Webhook for Midtrans Donation
     */
    public function webhook(Request $request)
    {
        $orderId = $request->order_id;
        $status = $request->transaction_status;

        // Order ID format: GEN-DONATE-{enrollment_id}-{time}
        $parts = explode('-', $orderId);
        if (count($parts) < 3) return response()->json(['message' => 'Invalid Order ID'], 400);
        
        $enrollmentId = $parts[2];
        $enrollment = ProgramEnrollment::find($enrollmentId);

        if (!$enrollment) return response()->json(['message' => 'Enrollment not found'], 404);

        if ($status == 'capture' || $status == 'settlement') {
            $enrollment->update([
                'payment_status' => 'paid',
                'payment_transaction_id' => $request->transaction_id
            ]);
            
            Log::info("Donation success for Enrollment ID: " . $enrollmentId);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Logic to build periodized program sessions
     */
    private function buildPeriodizedProgram(array $config)
    {
        $weeks = $config['weeks'];
        $frequency = $config['frequency'];
        $mileage = $config['weekly_mileage'];
        $initialVdot = $config['initial_vdot'];
        $targetVdot = $config['target_vdot'];
        $targetDistance = $config['target_distance'] ?? '10k';
        $runnerLevel = $config['runner_level'] ?? 'intermediate';
        $longRunDay = $config['long_run_day'] ?? 'sunday';
        $isTropical = $config['is_tropical'] ?? false;

        if ($targetDistance === 'cooper12') {
            $libraryPath = config_path('workout_library.php');
            $library = file_exists($libraryPath) ? include($libraryPath) : [];
            return $this->buildCooper12Program($config, $library);
        }

        // Adjust training frequency by level to optimize recovery and prevent injuries
        if ($runnerLevel === 'beginner') {
            $frequency = min($frequency, 4);
        } elseif ($runnerLevel === 'intermediate') {
            $frequency = min($frequency, 5);
        }

        // Map target distance to goal categories in workout_library.php
        $distanceMap = [
            '5k' => '5K',
            '10k' => '10K',
            '21k' => 'HALF_MARATHON',
            '42k' => 'FULL_MARATHON'
        ];
        $goal = $distanceMap[strtolower($targetDistance)] ?? '10K';

        // Load workout library
        $libraryPath = config_path('workout_library.php');
        $library = file_exists($libraryPath) ? include($libraryPath) : [];

        $levelRules = $library['level_rules'][$runnerLevel] ?? [
            'max_quality_sessions_per_week' => 1,
            'volume_adjustment' => 1.00
        ];
        $volumeFactor = $levelRules['volume_adjustment'] ?? 1.00;
        $maxQualitySessions = $levelRules['max_quality_sessions_per_week'] ?? 1;

        $longRunDayIndex = $longRunDay === 'saturday' ? 6 : 7;

        $sessions = [];
        $dayCount = 1;

        // Dynamic Phases based on Target Distance
        if ($targetDistance === '42k') {
            $baseRatio = 0.40; $strengthRatio = 0.30; $speedRatio = 0.20;
        } elseif ($targetDistance === '21k') {
            $baseRatio = 0.30; $strengthRatio = 0.30; $speedRatio = 0.30;
        } else {
            $baseRatio = 0.20; $strengthRatio = 0.30; $speedRatio = 0.40;
        }

        $p1 = (int)($weeks * $baseRatio);
        $p2 = (int)($weeks * $strengthRatio);
        $p3 = (int)($weeks * $speedRatio);

        $deltaVdot = $targetVdot - $initialVdot;
        $strengthStart = $p1 + 1;
        $strengthEnd = $p1 + $p2;
        $speedStart = $strengthEnd + 1;
        $speedEnd = $strengthEnd + $p3;

        // Keep track of recently used workout IDs to promote variety
        $usedWorkoutIds = [];

        for ($w = 1; $w <= $weeks; $w++) {
            $phase = 'Base';
            $currentVdot = $initialVdot;

            if ($w >= $strengthStart && $w <= $strengthEnd) {
                $phase = 'Strength';
                $t = ($w - $strengthStart + 1) / max(1, $p2);
                $currentVdot = $initialVdot + ($deltaVdot * 0.5 * $t);
            } elseif ($w >= $speedStart && $w <= $speedEnd) {
                $phase = 'Speed';
                $t = ($w - $speedStart + 1) / max(1, $p3);
                $currentVdot = ($initialVdot + ($deltaVdot * 0.5)) + ($deltaVdot * 0.5 * $t);
            } elseif ($w > $speedEnd) {
                $phase = 'Taper';
                $currentVdot = $targetVdot;
            }

            $paces = $this->danielsService->calculateTrainingPaces($currentVdot);
            if ($isTropical) {
                $paces['E'] *= 1.05;
                $paces['M'] *= 1.05;
                $paces['T'] *= 1.04;
                $paces['I'] *= 1.03;
                $paces['R'] *= 1.02;
            }

            $currentMileage = $mileage;
            if ($phase === 'Taper') $currentMileage *= 0.6;
            if ($w % 4 === 0) $currentMileage *= 0.8;

            $isDeload = ($w % 4 === 0);

            // Determine long run distance (dynamic ratio and floor)
            $longRunRatio = 0.20;
            $minLongRunFloor = 5.0;
            if ($targetDistance === '42k') {
                $longRunRatio = 0.30;
                $minLongRunFloor = 18.0;
            } elseif ($targetDistance === '21k') {
                $longRunRatio = 0.25;
                $minLongRunFloor = 12.0;
            }
            $longRunDistance = round($currentMileage * $longRunRatio, 1);
            $longRunDistance = min($longRunDistance, round($currentMileage * 0.35, 1));
            
            // Apply runner level adjustments to long run
            if ($runnerLevel === 'beginner') {
                $longRunDistance = round($longRunDistance * 0.9, 1);
            } elseif ($runnerLevel === 'advanced') {
                $longRunDistance = round($longRunDistance * 1.1, 1);
            }

            // Apply Long Run Floor
            $mileageWarning = false;
            if ($longRunDistance < $minLongRunFloor) {
                $longRunDistance = $minLongRunFloor;
                // If it forces long run to be > 40% of weekly mileage, flag warning
                if ($longRunDistance > $currentMileage * 0.40) {
                    $mileageWarning = true;
                }
            }

            // Determine how many quality sessions to schedule this week
            $weeklyQualityCount = $maxQualitySessions;
            if ($isDeload && $weeklyQualityCount > 1) {
                $weeklyQualityCount = 1;
            }

            // Select quality workouts from pool
            $weekQualityWorkouts = [];
            for ($q = 0; $q < $weeklyQualityCount; $q++) {
                $workout = $this->getQualityWorkoutForPhase($goal, $phase, $library, $usedWorkoutIds);
                if ($workout) {
                    $weekQualityWorkouts[] = $workout;
                    $usedWorkoutIds[] = $workout['id'];
                    if (count($usedWorkoutIds) > 15) {
                        array_shift($usedWorkoutIds);
                    }
                }
            }

            // Map which days get what sessions based on long run day
            $dayAssignments = array_fill(1, 7, ['type' => 'rest', 'workout' => null]);
            
            // Step 1: Assign Long Run Day
            $dayAssignments[$longRunDayIndex] = ['type' => 'long_run', 'workout' => null];

            // Step 2: Assign Quality Days
            if (count($weekQualityWorkouts) === 1) {
                // Place it on Day 3 (Wednesday)
                $dayAssignments[3] = ['type' => 'quality', 'workout' => $weekQualityWorkouts[0]];
            } elseif (count($weekQualityWorkouts) === 2) {
                // Place on Day 2 (Tuesday) and Day 4 (Thursday)
                $dayAssignments[2] = ['type' => 'quality', 'workout' => $weekQualityWorkouts[0]];
                $dayAssignments[4] = ['type' => 'quality', 'workout' => $weekQualityWorkouts[1]];
            }

            // Step 3: Assign Easy and Recovery Days based on frequency
            $runningDaysCount = 1 + count($weekQualityWorkouts);
            $easyDaysNeeded = max(0, $frequency - $runningDaysCount);

            $candidateDays = [1, 5, 6, 7, 2, 3, 4];
            $assignedEasyCount = 0;
            foreach ($candidateDays as $d) {
                if ($assignedEasyCount >= $easyDaysNeeded) {
                    break;
                }
                if ($dayAssignments[$d]['type'] === 'rest') {
                    $isAfterQuality = false;
                    $prevDay = ($d == 1) ? 7 : $d - 1;
                    if ($dayAssignments[$prevDay]['type'] === 'quality') {
                        $isAfterQuality = true;
                    }
                    
                    $easyType = ($isDeload || $isAfterQuality) ? 'recovery_run' : 'easy_run';
                    $dayAssignments[$d] = ['type' => $easyType, 'workout' => null];
                    $assignedEasyCount++;
                }
            }

            // Calculate actual distances for quality workouts
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

            // Calculate easy run distances from remaining mileage pool
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

            // Now, construct the sessions for this week
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
                    'target_pace' => null
                ];

                if ($assignment['type'] === 'long_run') {
                    $session['type'] = 'long_run';
                    $session['distance'] = $longRunDistance;
                    $session['target_pace'] = $this->formatPace($paces['E']);
                    $session['duration'] = $this->calculateDuration($longRunDistance, $paces['E']);
                    
                    $paceFast = max(0, $paces['E'] - (5/60));
                    $paceSlow = $paces['E'] + (10/60);
                    $rangeStr = sprintf('%d:%02d - %d:%02d/km', floor($paceFast), round(($paceFast - floor($paceFast))*60), floor($paceSlow), round(($paceSlow - floor($paceSlow))*60));
                    
                    $session['description'] = $isDeload 
                        ? "Long Easy Run (De-load) - Berlari santai dengan volume dikurangi untuk pemulihan.\nTarget: $rangeStr (RPE 3-4)"
                        : "Long Easy Run - Fokus pada daya tahan kardio.\nTarget: $rangeStr (RPE 3-4)";
                        
                    if (isset($mileageWarning) && $mileageWarning) {
                        $session['description'] .= "\n\n[WARNING: Weekly mileage Anda terlalu rendah untuk mengadaptasi jarak lari ini dengan optimal. Kami telah memaksakan batas minimal untuk Long Run ini.]";
                    }
                } elseif ($assignment['type'] === 'quality') {
                    $workout = $assignment['workout'];
                    $scaledMainSet = $this->scaleWorkoutVolume($workout['main_set'], $volumeFactor);

                    $workoutPaceKey = 'E';
                    $rpe = '3-4';
                    if ($workout['type'] === 'interval') {
                        $workoutPaceKey = 'I';
                        $rpe = '9-10';
                    } elseif ($workout['type'] === 'threshold' || $workout['type'] === 'progression') {
                        $workoutPaceKey = 'T';
                        $rpe = '7-8';
                    } elseif ($workout['type'] === 'marathon_pace') {
                        $workoutPaceKey = 'M';
                        $rpe = '5-6';
                    } elseif ($workout['type'] === 'repetition') {
                        $workoutPaceKey = 'R';
                        $rpe = '9-10';
                    }
                    
                    $targetPaceStr = $this->formatPace($paces[$workoutPaceKey]);
                    $session['duration'] = $this->calculateDuration($qualityDistances[$d], $paces[$workoutPaceKey]);
                    
                    $paceFast = max(0, $paces[$workoutPaceKey] - (5/60));
                    $paceSlow = $paces[$workoutPaceKey] + (10/60);
                    $rangeStr = sprintf('%d:%02d - %d:%02d/km', floor($paceFast), round(($paceFast - floor($paceFast))*60), floor($paceSlow), round(($paceSlow - floor($paceSlow))*60));
                    
                    $mainSetText = str_replace(
                        ['{distance_km}', '{target_pace}'],
                        [$scaledMainSet, $rangeStr . " (RPE $rpe)"],
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
                        "Reason: " . ($workout['best_for'] ?? $workout['focus'] ?? 'Meningkatkan performa lari') . " (" . ($workout['focus'] ?? '') . ")"
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
                    
                    $paceFast = max(0, $paces['E'] - (5/60));
                    $paceSlow = $paces['E'] + (10/60);
                    $rangeStr = sprintf('%d:%02d - %d:%02d/km', floor($paceFast), round(($paceFast - floor($paceFast))*60), floor($paceSlow), round(($paceSlow - floor($paceSlow))*60));
                    
                    $session['description'] = $assignment['type'] === 'recovery_run'
                        ? ($isDeload ? "Recovery Run (De-load) - Lari pemulihan sangat santai.\nTarget: $rangeStr (RPE < 3)" : "Recovery Run - Membantu pemulihan otot pasca latihan keras.\nTarget: $rangeStr (RPE < 3)")
                        : "Easy Aerobic Run - Membangun daya tahan dasar.\nTarget: $rangeStr (RPE 3-4)";
                } else {
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
                'target_vdot' => round($targetVdot, 1)
            ]
        ];
    }

    private function scaleWorkoutVolume(string $mainSet, float $factor)
    {
        if ($factor >= 1.0) {
            return $mainSet;
        }

        if (preg_match('/^(\d+)\s*reps\s*x\s*(.+)$/i', $mainSet, $matches)) {
            $reps = (int)$matches[1];
            $scaledReps = max(1, (int)round($reps * $factor));
            return $scaledReps . ' reps x ' . $matches[2];
        }

        if (preg_match('/^(.*?)(\d+)\s*x\s*(\d+(?:\.\d+)?\s*[Km])(.*?)$/i', $mainSet, $matches)) {
            $reps = (int)$matches[2];
            $scaledReps = max(1, (int)round($reps * $factor));
            return $matches[1] . $scaledReps . ' x ' . $matches[3] . $matches[4];
        }

        $scaled = preg_replace_callback('/(\d+(?:\.\d+)?)\s*(K|m|meters|K\b)/i', function($m) use ($factor) {
            $val = (float)$m[1];
            $scaledVal = round($val * $factor, 1);
            if ($scaledVal == (int)$scaledVal) {
                $scaledVal = (int)$scaledVal;
            }
            return $scaledVal . $m[2];
        }, $mainSet);

        return $scaled;
    }

    private function calculateMainSetDistance(string $mainSet): float
    {
        if (preg_match('/(\d+)\s*reps\s*x\s*(\d+(?:\.\d+)?)\s*(K|m|meters)/i', $mainSet, $matches)) {
            $reps = (float)$matches[1];
            $val = (float)$matches[2];
            $unit = strtolower($matches[3]);
            if ($unit === 'm' || $unit === 'meters') {
                return ($reps * $val) / 1000.0;
            }
            return $reps * $val;
        }
        
        if (preg_match('/(\d+)\s*x\s*(\d+(?:\.\d+)?\s*)(K|m|meters)/i', $mainSet, $matches)) {
            $reps = (float)$matches[1];
            $val = (float)$matches[2];
            $unit = strtolower($matches[3]);
            if ($unit === 'm' || $unit === 'meters') {
                return ($reps * $val) / 1000.0;
            }
            return $reps * $val;
        }

        if (preg_match_all('/(\d+(?:\.\d+)?)\s*(K|m|meters)/i', $mainSet, $matches, PREG_SET_ORDER)) {
            $total = 0.0;
            foreach ($matches as $match) {
                $val = (float)$match[1];
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

    private function getQualityWorkoutForPhase(string $goal, string $phase, array $library, array $usedWorkoutIds = [])
    {
        $allWorkouts = $library['goals'][$goal] ?? [];
        if (empty($allWorkouts)) {
            return null;
        }

        $phaseRule = $library['training_phase_rules'][strtolower($phase)] ?? null;
        $preferredTypes = $phaseRule['preferred_types'] ?? [];

        $preferredWorkouts = array_filter($allWorkouts, function ($w) use ($preferredTypes) {
            return in_array($w['type'], $preferredTypes, true);
        });

        $pool = !empty($preferredWorkouts) ? $preferredWorkouts : $allWorkouts;

        $unusedPool = array_filter($pool, function ($w) use ($usedWorkoutIds) {
            return !in_array($w['id'], $usedWorkoutIds, true);
        });

        $selectedPool = !empty($unusedPool) ? $unusedPool : $pool;
        return $selectedPool[array_rand($selectedPool)];
    }


    private function formatPace(float $minPerKm)
    {
        $m = floor($minPerKm);
        $s = round(($minPerKm - $m) * 60);
        return sprintf('@ %d:%02d/km', $m, $s);
    }

    private function calculateDuration(float $distanceKm, float $paceMinPerKm): string
    {
        $totalSeconds = round($distanceKm * $paceMinPerKm * 60);
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
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
                $paceLines[] = $k.': '.$this->formatPace((float) $v);
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
            Log::warning('AI refine generator_v2 failed: '.$e->getMessage());
            return $sessions;
        }
    }

    private function buildCooper12Program(array $config, array $library)
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
            'cooper12_20min_threshold'
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
                            'target_pace' => null
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
                    'target_pace' => null
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
                'peak_mileage' => round($peakMileage, 1)
            ]
        ];
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
