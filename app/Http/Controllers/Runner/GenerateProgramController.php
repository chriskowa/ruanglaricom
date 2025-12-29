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
            'weekly_mileage' => 'required|numeric|min:0|max:300',
            'training_frequency' => 'required|integer|min:2|max:7',
            'goal_distance' => 'required|in:5k,10k,21k,42k',
            'duration_weeks' => 'nullable|integer|min:6|max:12', // Flexible short duration
        ]);

        $user = auth()->user();
        $vdot = $user->vdot;

        if (!$vdot) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan update Personal Best (PB) Anda terlebih dahulu untuk menghitung VDOT.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate program using VDOT directly
            $programData = $this->danielsService->generateProgramFromVDOT($vdot, [
                'goal_distance' => $validated['goal_distance'],
                'weekly_mileage' => $validated['weekly_mileage'],
                'training_frequency' => $validated['training_frequency'],
                'duration_weeks' => $validated['duration_weeks'] ?? 8,
            ]);

            // Create program
            $program = Program::create([
                'coach_id' => $user->id,
                'title' => 'Program VDOT: ' . strtoupper($validated['goal_distance']) . ' (' . $programData['duration_weeks'] . ' Weeks)',
                'slug' => 'vdot-' . strtolower($validated['goal_distance']) . '-' . Str::random(8),
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
                    'weekly_mileage' => $validated['weekly_mileage'],
                    'training_frequency' => $validated['training_frequency'],
                    'goal_distance' => $validated['goal_distance'],
                    'training_paces' => $programData['training_paces'],
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

            return response()->json([
                'success' => true,
                'message' => 'Program berhasil di-generate! Program telah ditambahkan ke Program Bag Anda.',
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
