<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Services\DanielsRunningService;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Snap;

class SelfGeneratedProgramController extends Controller
{
    protected $danielsService;
    protected $midtransService;

    public function __construct(DanielsRunningService $danielsService, MidtransService $midtransService)
    {
        $this->danielsService = $danielsService;
        $this->midtransService = $midtransService;
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
            'weekly_mileage' => 'required|numeric|min:5|max:200',
            'frequency' => 'required|integer|min:3|max:7',
        ], [
            'pb_time.regex' => 'Format waktu PB harus HH:MM:SS atau MM:SS (contoh: 00:45:00 atau 25:30).',
            'target_date.after_or_equal' => 'Tanggal race harus hari ini atau di masa depan.',
        ]);

        try {
            $vdot = $this->danielsService->calculateVDOT($validated['pb_time'], $validated['pb_distance']);
            $paces = $this->danielsService->calculateTrainingPaces($vdot);
            
            // Generate sessions based on distance and duration
            $targetDate = Carbon::parse($validated['target_date']);
            $weeksUntilRace = max(8, min(24, (int) ceil(now()->diffInWeeks($targetDate))));
            
            $programData = $this->danielsService->generateProgramFromVDOT($vdot, [
                'goal_distance' => $validated['target_distance'],
                'weekly_mileage' => $validated['weekly_mileage'],
                'training_frequency' => $validated['frequency'],
                'duration_weeks' => $weeksUntilRace
            ]);

            return response()->json([
                'success' => true,
                'vdot' => round($vdot, 1),
                'paces' => $paces,
                'weeks' => $weeksUntilRace,
                'sessions' => $programData['sessions'],
                'summary' => [
                    'total_weeks' => $weeksUntilRace,
                    'target' => strtoupper($validated['target_distance']),
                    'vdot' => round($vdot, 1)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Generator Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal generate program.'], 500);
        }
    }

    /**
     * AJAX: Save generated program to user's calendar
     */
    public function saveToCalendar(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'title' => 'required|string',
            'target_distance' => 'required|string',
            'target_date' => 'required|date',
            'program_json' => 'required|array',
            'vdot' => 'required|numeric',
            'paces' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $targetDate = Carbon::parse($data['target_date']);
            $sessions = $data['program_json']['sessions'] ?? [];
            $totalDays = count($sessions);
            $durationWeeks = (int) ceil($totalDays / 7);
            
            // Default start date: subtract total days from target date
            $startDate = $targetDate->copy()->subDays($totalDays - 1)->startOfWeek();

            // Create a virtual program record
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => $data['title'],
                'slug' => 'gen-' . Str::random(10),
                'description' => "AI Generated Program for " . strtoupper($data['target_distance']),
                'distance_target' => $data['target_distance'],
                'duration_weeks' => $durationWeeks,
                'program_json' => $data['program_json'],
                'is_self_generated' => true,
                'is_active' => true,
                'is_published' => false,
                'price' => 0, 
                'generated_vdot' => $data['vdot'],
                'daniels_params' => [
                    'training_paces' => $data['paces']
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
        $paces = $config['paces'];
        $frequency = $config['frequency'];
        $mileage = $config['weekly_mileage'];
        
        $sessions = [];
        $dayCount = 1;

        // Simplified Periodization: 
        // 25% Base, 25% Strength, 40% Speed, 10% Taper
        $p1 = (int)($weeks * 0.25);
        $p2 = (int)($weeks * 0.25);
        $p3 = (int)($weeks * 0.40);
        $p4 = $weeks - $p1 - $p2 - $p3;

        for ($w = 1; $w <= $weeks; $w++) {
            $phase = 'Base';
            if ($w > $p1) $phase = 'Strength';
            if ($w > ($p1 + $p2)) $phase = 'Speed';
            if ($w > ($p1 + $p2 + $p3)) $phase = 'Taper';

            // Weekly Mileage Adjustment
            $currentMileage = $mileage;
            if ($phase === 'Taper') $currentMileage *= 0.6;
            if ($w % 4 === 0) $currentMileage *= 0.8; // Recovery week

            for ($d = 1; $d <= 7; $d++) {
                $session = [
                    'day' => $dayCount++,
                    'week' => $w,
                    'phase' => $phase,
                    'type' => 'rest',
                    'description' => 'Rest Day',
                    'distance' => 0,
                    'target_pace' => null
                ];

                // Simple session allocation
                if ($d === 7) { // Sunday Long Run
                    $session['type'] = 'long_run';
                    $session['distance'] = round($currentMileage * 0.25, 1);
                    $session['target_pace'] = $this->formatPace($paces['E']);
                    $session['description'] = "Long Easy Run - Fokus pada daya tahan";
                } elseif ($d === 3 && $phase !== 'Base' && $phase !== 'Taper') { // Wednesday Workout
                    if ($phase === 'Strength') {
                        $session['type'] = 'threshold';
                        $session['distance'] = round($currentMileage * 0.15, 1);
                        $session['target_pace'] = $this->formatPace($paces['T']);
                        $session['description'] = "Threshold Run - Tingkatkan stamina";
                    } else {
                        $session['type'] = 'interval';
                        $session['distance'] = round($currentMileage * 0.12, 1);
                        $session['target_pace'] = $this->formatPace($paces['I']);
                        $session['description'] = "Interval Training - Fokus pada VO2 Max";
                    }
                } elseif ($d <= $frequency && $d !== 3) { // Easy Days
                    $session['type'] = 'easy_run';
                    $session['distance'] = round(($currentMileage * 0.6) / ($frequency - 1), 1);
                    $session['target_pace'] = $this->formatPace($paces['E']);
                    $session['description'] = "Easy Recovery Run";
                }

                $sessions[] = $session;
            }
        }

        return [
            'sessions' => $sessions,
            'summary' => [
                'total_weeks' => $weeks,
                'target' => strtoupper($config['target_distance']),
                'vdot' => round($config['vdot'], 1)
            ]
        ];
    }

    private function formatPace(float $minPerKm)
    {
        $m = floor($minPerKm);
        $s = round(($minPerKm - $m) * 60);
        return sprintf('%d:%02d', $m, $s);
    }
}
