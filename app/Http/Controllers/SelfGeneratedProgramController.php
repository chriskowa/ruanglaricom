<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Services\DanielsRunningService;
use App\Services\MidtransService;
use App\Services\OpenAiService;
use App\Services\ProgramBuilderService;
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
    protected $builderService;

    public function __construct(DanielsRunningService $danielsService, MidtransService $midtransService, OpenAiService $openAiService, ProgramBuilderService $builderService)
    {
        $this->danielsService = $danielsService;
        $this->midtransService = $midtransService;
        $this->openAiService = $openAiService;
        $this->builderService = $builderService;
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
        $form = $data['form'] ?? [];
        $result = $data['result'] ?? [];

        DB::beginTransaction();
        try {
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

            // 1. Create Program
            $title = "AI " . strtoupper($targetDistance) . " Plan (" . $vdot . ")";
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => $title,
                'slug' => $this->generateUniqueSlug($title),
                'description' => "Program latihan lari periodisasi yang di-generate menggunakan algoritma Daniels' VDOT v2.0.",
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
                'price' => 0,
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
                    'training_paces' => $result['paces'] ?? [],
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
            'is_tropical' => 'nullable|boolean',
            'use_ai' => 'nullable|boolean',
        ], [
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
            if (!empty($validated['start_date'])) {
                $startDate = Carbon::parse($validated['start_date']);
                $diffDays = max(1, $startDate->diffInDays($targetDate, false));
                $weeksUntilRace = max(8, min(24, (int) ceil($diffDays / 7)));
            } else {
                $weeksUntilRace = max(8, min(24, (int) ceil(now()->diffInWeeks($targetDate))));
            }
            
            $programData = $this->builderService->build([
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

            $useAi = $request->boolean('use_ai', false);
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

        DB::beginTransaction();
        try {
            if ($activeEnrollment && $action === 'replace') {
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

            // Create a virtual program record
            $title = "AI " . strtoupper($targetDistance) . " Plan (" . $vdot . ")";
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => $title,
                'slug' => $this->generateUniqueSlug($title),
                'description' => "AI Generated Program for " . strtoupper($targetDistance),
                'distance_target' => $targetDistance,
                'duration_weeks' => $durationWeeks,
                'program_json' => [
                    'sessions' => $sessions,
                    'summary' => $result['summary'] ?? []
                ],
                'is_self_generated' => true,
                'is_active' => true,
                'is_published' => false,
                'price' => 0, 
                'generated_vdot' => $vdot,
                'daniels_params' => [
                    'training_paces' => $result['paces'] ?? [],
                    'runner_level' => $form['runner_level'] ?? null,
                    'long_run_day' => $form['long_run_day'] ?? null,
                    'start_date' => $startDateStr,
                    'target_date' => $targetDateStr,
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


    // =========================================================================
    // AI DESCRIPTION ENHANCEMENT
    // =========================================================================

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
