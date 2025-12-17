<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Services\DanielsRunningService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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
            'height' => 'nullable|numeric|min:100|max:250',
            'weight' => 'nullable|numeric|min:30|max:200',
            'race_distance' => 'required|in:5k,10k,21k,42k',
            'race_time' => 'required|regex:/^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/',
            'race_date' => 'required|date|before_or_equal:today',
            'weekly_mileage' => 'required|numeric|min:0|max:300',
            'peak_mileage' => 'required|numeric|min:0|max:300',
            'training_frequency' => 'required|integer|min:2|max:7',
            'goal_distance' => 'required|in:5k,10k,21k,42k',
            'goal_race_date' => 'required|date|after:today',
            'goal_time' => 'nullable|regex:/^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/',
            'injury_history' => 'nullable|string|max:1000',
        ]);

        // Verify race date is within 3 months
        $raceDate = Carbon::parse($validated['race_date']);
        if ($raceDate->diffInMonths(Carbon::now()) > 3) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal lomba harus dalam 3 bulan terakhir.',
            ], 422);
        }

        // Verify goal race date allows for 18-24 weeks training
        $goalRaceDate = Carbon::parse($validated['goal_race_date']);
        $weeksUntilRace = $goalRaceDate->diffInWeeks(Carbon::now());
        
        if ($weeksUntilRace < 18) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal lomba target harus minimal 18 minggu dari sekarang untuk program yang optimal.',
            ], 422);
        }

        if ($weeksUntilRace > 24) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal lomba target tidak boleh lebih dari 24 minggu dari sekarang.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate program using Daniels Running Service
            $programData = $this->danielsService->generateProgram([
                'race_time' => $validated['race_time'],
                'race_distance' => $validated['race_distance'],
                'goal_distance' => $validated['goal_distance'],
                'goal_race_date' => $validated['goal_race_date'],
                'weekly_mileage' => $validated['weekly_mileage'],
                'training_frequency' => $validated['training_frequency'],
            ]);

            // Create program
            $program = Program::create([
                'coach_id' => auth()->id(), // Self-generated, runner is the "coach"
                'title' => 'Program Self-Generated: ' . strtoupper($validated['goal_distance']) . ' Training Plan',
                'slug' => 'self-generated-' . strtolower($validated['goal_distance']) . '-' . Str::random(8),
                'description' => $this->generateDescription($validated, $programData),
                'difficulty' => $this->determineDifficulty($validated['weekly_mileage']),
                'distance_target' => $validated['goal_distance'],
                'target_time' => $validated['goal_time'] ?? null,
                'price' => 0, // Self-generated programs are free
                'program_json' => [
                    'sessions' => $programData['sessions'],
                    'duration_weeks' => $programData['duration_weeks'],
                ],
                'is_vdot_generated' => true,
                'vdot_score' => $programData['vdot'],
                'is_active' => true,
                'is_published' => true, // Auto-publish self-generated programs
                'duration_weeks' => $programData['duration_weeks'],
                'is_self_generated' => true,
                'daniels_params' => [
                    'age' => $validated['age'],
                    'gender' => $validated['gender'],
                    'race_distance' => $validated['race_distance'],
                    'race_time' => $validated['race_time'],
                    'race_date' => $validated['race_date'],
                    'weekly_mileage' => $validated['weekly_mileage'],
                    'peak_mileage' => $validated['peak_mileage'],
                    'training_frequency' => $validated['training_frequency'],
                    'goal_distance' => $validated['goal_distance'],
                    'goal_race_date' => $validated['goal_race_date'],
                    'goal_time' => $validated['goal_time'] ?? null,
                    'training_paces' => $programData['training_paces'],
                ],
                'generated_vdot' => $programData['vdot'],
            ]);

            // Auto-enroll in the generated program
            $enrollment = ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => auth()->id(),
                'start_date' => Carbon::today(),
                'end_date' => $goalRaceDate,
                'status' => 'active',
                'payment_status' => 'paid', // Free program
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Program berhasil di-generate! Anda telah otomatis terdaftar di program ini.',
                'program_id' => $program->id,
                'vdot' => $programData['vdot'],
                'training_paces' => $programData['training_paces'],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate program: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate description for the program
     */
    private function generateDescription(array $params, array $programData): string
    {
        $description = "Program latihan yang di-generate menggunakan Daniels' Running Formula.\n\n";
        $description .= "VDOT Score: " . $programData['vdot'] . "\n";
        $description .= "Training Paces:\n";
        $description .= "- Easy (E): " . $this->formatPace($programData['training_paces']['E']) . "/km\n";
        $description .= "- Threshold (T): " . $this->formatPace($programData['training_paces']['T']) . "/km\n";
        $description .= "- Interval (I): " . $this->formatPace($programData['training_paces']['I']) . "/km\n";
        $description .= "\nTarget: " . strtoupper($params['goal_distance']) . " pada " . Carbon::parse($params['goal_race_date'])->format('d M Y');
        
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
}
