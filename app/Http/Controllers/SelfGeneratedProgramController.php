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
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => "AI " . strtoupper($form['target_distance']) . " Plan (" . $result['vdot'] . ")",
                'slug' => 'ai-' . strtolower($form['target_distance']) . '-' . Str::random(8),
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
                'payment_status' => 'pending',
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
            'target_distance' => 'required|in:5k,10k,21k,42k',
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
            
            // Limit the improvement to a realistic level based on runner level
            $maxVdotImprovement = 3.0;
            if ($validated['runner_level'] === 'beginner') {
                $maxVdotImprovement = 2.0;
            } elseif ($validated['runner_level'] === 'advanced') {
                $maxVdotImprovement = 4.0;
            }
            $safeTargetVdot = min($targetVdot, $currentVdot + $maxVdotImprovement);

            $isTropical = filter_var($validated['is_tropical'] ?? false, FILTER_VALIDATE_BOOLEAN);

            // Calculate base and adjusted training paces
            $paces = $this->danielsService->calculateTrainingPaces($currentVdot);
            if ($isTropical) {
                $paces['E'] += 0.25;  // +15s/km
                $paces['M'] += 0.20;  // +12s/km
                $paces['T'] += 0.167; // +10s/km
                $paces['I'] += 0.133; // +8s/km
                $paces['R'] += 0.083; // +5s/km
            }

            // Calculate target Heart Rate zones based on age (Haskell & Fox formula: 220 - age)
            $age = (int) $validated['age'];
            $maxHr = 220 - $age;
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
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => "AI " . strtoupper($form['target_distance']) . " Plan (" . $result['vdot'] . ")",
                'slug' => 'gen-' . Str::random(10),
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
                'payment_status' => 'pending', 
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

        // Adjust training frequency by level to optimize recovery and prevent injuries
        if ($runnerLevel === 'beginner') {
            $frequency = min($frequency, 4);
        } elseif ($runnerLevel === 'intermediate') {
            $frequency = min($frequency, 5);
        }

        $distanceProfiles = [
            '5k' => [
                'long_run' => ['Base' => 0.20, 'Strength' => 0.22, 'Speed' => 0.22, 'Taper' => 0.18],
                'quality_strength' => 0.10,
                'quality_speed' => 0.14
            ],
            '10k' => [
                'long_run' => ['Base' => 0.22, 'Strength' => 0.24, 'Speed' => 0.24, 'Taper' => 0.20],
                'quality_strength' => 0.12,
                'quality_speed' => 0.15
            ],
            '21k' => [
                'long_run' => ['Base' => 0.28, 'Strength' => 0.30, 'Speed' => 0.30, 'Taper' => 0.22],
                'quality_strength' => 0.18,
                'quality_speed' => 0.18,
                'secondary' => 0.12
            ],
            '42k' => [
                'long_run' => ['Base' => 0.32, 'Strength' => 0.34, 'Speed' => 0.34, 'Taper' => 0.24],
                'quality_strength' => 0.20,
                'quality_speed' => 0.20,
                'secondary' => 0.14
            ]
        ];

        $profile = $distanceProfiles[$targetDistance] ?? $distanceProfiles['10k'];
        $levelFactors = [
            'beginner' => 0.8,      // Lower quality volume for beginners
            'intermediate' => 1.0,  // Standard quality volume
            'advanced' => 1.25      // Higher/Elite intensity capacity
        ];
        $longRunFactors = [
            'beginner' => 0.9,      // Conservative long run build-up
            'intermediate' => 1.0,  // Standard long run ratio
            'advanced' => 1.15      // Elite aerobic endurance long runs
        ];
        $qualityFactor = $levelFactors[$runnerLevel] ?? 1;
        $longRunFactor = $longRunFactors[$runnerLevel] ?? 1;
        $longRunDayIndex = $longRunDay === 'saturday' ? 6 : 7;
        $qualityDayIndex = 3;
        $secondaryDayIndex = $longRunDayIndex === 6 ? 7 : 6;
        
        $sessions = [];
        $dayCount = 1;

        // Phases: 25% Base, 25% Strength, 40% Speed, 10% Taper
        $p1 = (int)($weeks * 0.25);
        $p2 = (int)($weeks * 0.25);
        $p3 = (int)($weeks * 0.40);

        $deltaVdot = $targetVdot - $initialVdot;
        $strengthStart = $p1 + 1;
        $strengthEnd = $p1 + $p2;
        $speedStart = $strengthEnd + 1;
        $speedEnd = $strengthEnd + $p3;

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
                $paces['E'] += 0.25;
                $paces['M'] += 0.20;
                $paces['T'] += 0.167;
                $paces['I'] += 0.133;
                $paces['R'] += 0.083;
            }

            $currentMileage = $mileage;
            if ($phase === 'Taper') $currentMileage *= 0.6;
            if ($w % 4 === 0) $currentMileage *= 0.8;

            $longRunRatio = $profile['long_run'][$phase] ?? 0.24;
            $longRunDistance = round($currentMileage * $longRunRatio * $longRunFactor, 1);
            $longRunDistance = min($longRunDistance, round($currentMileage * 0.35, 1));

            $qualityType = null;
            $qualityDistance = 0;
            $qualityPace = null;
            $qualityDescription = null;

            if ($phase === 'Strength') {
                if (in_array($targetDistance, ['5k', '10k'], true)) {
                    $qualityType = 'repetition';
                    $qualityDistance = round($currentMileage * $profile['quality_strength'] * $qualityFactor, 1);
                    $qualityPace = 'R';
                    $qualityDescription = 'Repetition Run - Ekonomi dan kecepatan';
                } else {
                    $qualityType = 'threshold';
                    $qualityDistance = round($currentMileage * $profile['quality_strength'] * $qualityFactor, 1);
                    $qualityPace = 'T';
                    $qualityDescription = 'Threshold Run - Ketahanan aerobik';
                }
            }

            if ($phase === 'Speed') {
                if (in_array($targetDistance, ['5k', '10k'], true)) {
                    $qualityType = 'interval';
                    $qualityDistance = round($currentMileage * $profile['quality_speed'] * $qualityFactor, 1);
                    $qualityPace = 'I';
                    $qualityDescription = 'Interval Run - VO2 Max';
                } else {
                    $qualityType = 'threshold';
                    $qualityDistance = round($currentMileage * $profile['quality_speed'] * $qualityFactor, 1);
                    $qualityPace = 'T';
                    $qualityDescription = 'Tempo Run - Kecepatan lomba';
                }
            }

            $secondaryType = null;
            $secondaryDistance = 0;
            $secondaryPace = null;
            $secondaryDescription = null;

            if (in_array($targetDistance, ['21k', '42k'], true) && $phase !== 'Base' && $phase !== 'Taper' && $frequency >= 5) {
                $secondaryType = 'marathon';
                $secondaryDistance = round($currentMileage * ($profile['secondary'] ?? 0.12) * $qualityFactor, 1);
                $secondaryDistance = min($secondaryDistance, round($currentMileage * 0.18, 1));
                $secondaryPace = 'M';
                $secondaryDescription = 'Marathon Pace Run - Daya tahan lomba';
            }

            if ($qualityDistance > 0) {
                $qualityDistance = min($qualityDistance, round($currentMileage * 0.22, 1));
            }

            $fixedDistance = $longRunDistance + max(0, $qualityDistance) + max(0, $secondaryDistance);
            if ($fixedDistance > $currentMileage && $fixedDistance > 0) {
                $scale = $currentMileage / $fixedDistance;
                $longRunDistance = round($longRunDistance * $scale, 1);
                $qualityDistance = round($qualityDistance * $scale, 1);
                $secondaryDistance = round($secondaryDistance * $scale, 1);
            }

            $trainingDays = [];
            if ($longRunDistance > 0) {
                $trainingDays[] = $longRunDayIndex;
            }
            if ($qualityDistance > 0) {
                $trainingDays[] = $qualityDayIndex;
            }
            if ($secondaryDistance > 0) {
                $trainingDays[] = $secondaryDayIndex;
            }
            $trainingDays = array_values(array_unique($trainingDays));
            $easySlots = max(0, $frequency - count($trainingDays));
            $availableDays = [1, 2, 3, 4, 5, 6, 7];
            $easyDays = [];
            foreach ($availableDays as $day) {
                if ($easySlots <= 0) {
                    break;
                }
                if (in_array($day, $trainingDays, true)) {
                    continue;
                }
                $easyDays[] = $day;
                $easySlots--;
            }
            $easyPool = $currentMileage - $longRunDistance - $qualityDistance - $secondaryDistance;
            $easyDistance = count($easyDays) > 0 ? round(max(0, $easyPool) / count($easyDays), 1) : 0;

            $isDeload = ($w % 4 === 0);
            for ($d = 1; $d <= 7; $d++) {
                $session = [
                    'day' => $dayCount++,
                    'week' => $w,
                    'phase' => $phase,
                    'is_deload' => $isDeload,
                    'type' => 'rest',
                    'description' => 'Rest Day',
                    'distance' => 0,
                    'target_pace' => null
                ];

                if ($d === $longRunDayIndex) {
                    $session['type'] = 'long_run';
                    $session['distance'] = $longRunDistance;
                    $session['target_pace'] = $this->formatPace($paces['E']);
                    $session['description'] = $isDeload 
                        ? 'Long Easy Run (De-load) - Berlari santai dengan volume dikurangi untuk pemulihan.'
                        : 'Long Easy Run - Fokus pada daya tahan kardio.';
                } elseif ($d === $qualityDayIndex && $qualityDistance > 0) {
                    $session['type'] = $qualityType;
                    $session['distance'] = $qualityDistance;
                    $session['target_pace'] = $this->formatPace($paces[$qualityPace]);
                    $session['description'] = $qualityDescription;
                } elseif ($d === $secondaryDayIndex && $secondaryDistance > 0) {
                    $session['type'] = $secondaryType;
                    $session['distance'] = $secondaryDistance;
                    $session['target_pace'] = $this->formatPace($paces[$secondaryPace]);
                    $session['description'] = $secondaryDescription;
                } elseif (in_array($d, $easyDays, true) && $easyDistance > 0) {
                    $session['type'] = 'easy_run';
                    $session['distance'] = $easyDistance;
                    $session['target_pace'] = $this->formatPace($paces['E']);
                    $session['description'] = $isDeload
                        ? 'Easy Recovery Run (De-load) - Lari santai pemulihan pasca beban.'
                        : 'Easy Recovery Run';
                } else {
                    // Rest day fallbacks based on runner level
                    if ($runnerLevel === 'beginner') {
                        $session['description'] = 'Rest Day - Fokus peregangan otot ringan atau istirahat total.';
                    } elseif ($runnerLevel === 'intermediate') {
                        $session['description'] = 'Active Recovery - 15-20 menit foam rolling dan mobilitas sendi.';
                    } else {
                        $session['description'] = 'Active Recovery - Latihan core strength ringan (plank, bird-dog) & foam rolling.';
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

    private function formatPace(float $minPerKm)
    {
        $m = floor($minPerKm);
        $s = round(($minPerKm - $m) * 60);
        return sprintf('@ %d:%02d/km', $m, $s);
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
            "- Buat deskripsi yang ringkas, informatif, dan mudah dibaca di kalender (maksimal 3-4 baris per deskripsi).\n" .
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
}
