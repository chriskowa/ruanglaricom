<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\CustomWorkout;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get active enrollments for workout plan list
        $enrollments = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->whereHas('program', function ($query) {
                $query->where('is_active', true);
            })
            ->with('program')
            ->get();

        return view('runner.calendar_modern', [
            'enrollments' => $enrollments,
        ]);
    }

    /**
     * Get calendar events (programs + custom workouts) for the authenticated runner
     * Returns JSON format for FullCalendar
     */
    public function events(Request $request)
    {
        $user = auth()->user();
        $start = $request->get('start'); // ISO date string
        $end = $request->get('end'); // ISO date string

        $enrollments = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->whereHas('program', function ($query) {
                $query->where('is_active', true);
            })
            ->with('program')
            ->get();

        $events = [];

        foreach ($enrollments as $enrollment) {
            $program = $enrollment->program;
            $programJson = $program->program_json ?? [];
            $sessions = $programJson['sessions'] ?? [];
            
            if (!is_array($sessions) || empty($sessions)) {
                continue;
            }

            try {
                $startDate = Carbon::parse($enrollment->start_date);
            } catch (\Exception $e) {
                continue;
            }

            $totalWeeks = $program->duration_weeks ?? 12;
            $difficulty = $program->difficulty ?? 'beginner';
            
            foreach ($sessions as $index => $session) {
                if (!isset($session['day']) || !is_numeric($session['day'])) {
                    continue;
                }

                try {
                    $sessionDate = $startDate->copy()->addDays((int)$session['day'] - 1);
                } catch (\Exception $e) {
                    continue;
                }
                
                // Only include sessions within the requested date range
                if ($start && $end) {
                    try {
                        $startCarbon = Carbon::parse($start);
                        $endCarbon = Carbon::parse($end);
                        
                        if ($sessionDate->lt($startCarbon) || $sessionDate->gt($endCarbon)) {
                            continue;
                        }
                    } catch (\Exception $e) {
                        continue;
                    }
                }

                $phase = $this->getTrainingPhase((int)$session['day'], $totalWeeks);
                $colors = $this->getEventColors($difficulty, $phase);

                $events[] = [
                    'id' => "program_{$enrollment->id}_session_{$index}",
                    'title' => $session['type'] ?? 'Run',
                    'start' => $sessionDate->format('Y-m-d'),
                    'allDay' => true,
                    'backgroundColor' => $colors['background'],
                    'borderColor' => $colors['border'],
                    'textColor' => $colors['text'],
                    'extendedProps' => [
                        'type' => 'program_session',
                        'program_id' => $program->id,
                        'program_title' => $program->title,
                        'enrollment_id' => $enrollment->id,
                        'session' => $session,
                        'difficulty' => $difficulty,
                        'phase' => $phase,
                    ],
                ];
            }
        }

        // Add custom workouts
        $customWorkouts = CustomWorkout::where('runner_id', $user->id)
            ->when($start, function($query) use ($start, $end) {
                try {
                    $startCarbon = Carbon::parse($start);
                    $endCarbon = Carbon::parse($end);
                    $query->whereBetween('workout_date', [$startCarbon, $endCarbon]);
                } catch (\Exception $e) {
                    // Ignore date filter if parsing fails
                }
            })
            ->get();

        foreach ($customWorkouts as $workout) {
            $colors = $this->getEventColors($workout->difficulty ?? 'moderate', null);
            
            $events[] = [
                'id' => "custom_workout_{$workout->id}",
                'title' => $workout->type ?? 'Run',
                'start' => $workout->workout_date->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => $colors['background'],
                'borderColor' => $colors['border'],
                'textColor' => $colors['text'],
                'extendedProps' => [
                    'type' => 'custom_workout',
                    'workout_id' => $workout->id,
                    'workout' => [
                        'type' => $workout->type,
                        'distance' => $workout->distance,
                        'duration' => $workout->duration,
                        'description' => $workout->description,
                        'difficulty' => $workout->difficulty,
                        'status' => $workout->status,
                    ],
                ],
            ];
        }

        return response()->json($events);
    }

    /**
     * Get event colors based on difficulty and phase
     */
    private function getEventColors(string $difficulty, ?string $phase = null): array
    {
        // Phase colors (background)
        $phaseColors = [
            'foundation' => '#E3F2FD',      // Light blue
            'early_quality' => '#F3E5F5',   // Light purple
            'quality' => '#FFF3E0',         // Light orange
            'final_prep' => '#E8F5E9',      // Light green
        ];

        // Difficulty colors (border)
        $difficultyColors = [
            'beginner' => '#4CAF50',        // Green
            'easy' => '#4CAF50',            // Green
            'intermediate' => '#FF9800',    // Orange
            'moderate' => '#FF9800',        // Orange
            'advanced' => '#F44336',        // Red
            'hard' => '#F44336',            // Red
        ];

        $background = $phase ? ($phaseColors[$phase] ?? '#F5F5F5') : '#F5F5F5';
        $border = $difficultyColors[$difficulty] ?? '#9E9E9E';
        $text = '#212529';

        return [
            'background' => $background,
            'border' => $border,
            'text' => $text,
        ];
    }

    /**
     * Get workout plans list with filter
     */
    public function workoutPlans(Request $request)
    {
        try {
            $user = auth()->user();
            $filter = $request->get('filter', 'all'); // all, unfinished, finished

            $enrollments = ProgramEnrollment::where('runner_id', $user->id)
                ->whereHas('program', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('program')
                ->get();

            $workoutPlans = [];

            foreach ($enrollments as $enrollment) {
                $program = $enrollment->program;
                
                if (!$program) {
                    continue;
                }
                
                $programJson = $program->program_json ?? [];
                $sessions = $programJson['sessions'] ?? [];

                if (empty($sessions) || !is_array($sessions)) {
                    continue;
                }

                try {
                    $startDate = Carbon::parse($enrollment->start_date);
                } catch (\Exception $e) {
                    continue;
                }

                foreach ($sessions as $index => $session) {
                    // Skip if session doesn't have 'day' field
                    if (!isset($session['day']) || !is_numeric($session['day'])) {
                        continue;
                    }

                    try {
                        $sessionDate = $startDate->copy()->addDays((int)$session['day'] - 1);
                    } catch (\Exception $e) {
                        continue;
                    }
                    
                    // Check if session is in the future
                    if ($sessionDate->isFuture()) {
                        continue; // Skip future sessions
                    }

                    // Get tracking status
                    $tracking = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                        ->where('session_day', (int)$session['day'])
                        ->first();

                    $status = 'pending';
                    if ($tracking) {
                        $status = $tracking->status ?? 'pending';
                    }

                    // Apply filter
                    if ($filter === 'unfinished' && $status === 'completed') {
                        continue;
                    }
                    if ($filter === 'finished' && $status !== 'completed') {
                        continue;
                    }

                    $workoutPlans[] = [
                        'id' => $tracking ? $tracking->id : null,
                        'tracking_id' => $tracking ? $tracking->id : null,
                        'enrollment_id' => $enrollment->id,
                        'program_id' => $program->id,
                        'program_title' => $program->title,
                        'program_difficulty' => $program->difficulty ?? 'beginner',
                        'session_day' => (int)$session['day'],
                        'date' => $sessionDate->format('Y-m-d'),
                        'date_formatted' => $sessionDate->format('d M Y'),
                        'day_name' => $sessionDate->format('D'),
                        'day_number' => $sessionDate->format('d'),
                        'type' => $session['type'] ?? 'run',
                        'distance' => $session['distance'] ?? null,
                        'duration' => $session['duration'] ?? null,
                        'description' => $session['description'] ?? null,
                        'status' => $status,
                        'completed_at' => $tracking && $tracking->completed_at ? $tracking->completed_at->format('Y-m-d H:i:s') : null,
                        'phase' => $this->getTrainingPhase($session['day'], $program->duration_weeks ?? 12),
                    ];
                }
            }

            // Sort by date descending (most recent first)
            usort($workoutPlans, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return response()->json($workoutPlans);
        } catch (\Exception $e) {
            \Log::error('Error in workoutPlans: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal memuat workout plans: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get training phase based on day number
     */
    private function getTrainingPhase(int $day, int $totalWeeks): string
    {
        $totalDays = $totalWeeks * 7;
        $percentage = ($day / $totalDays) * 100;

        if ($percentage <= 25) {
            return 'foundation'; // Foundation phase
        } elseif ($percentage <= 50) {
            return 'early_quality'; // Early Quality phase
        } elseif ($percentage <= 75) {
            return 'quality'; // Quality phase
        } else {
            return 'final_prep'; // Final Preparation phase
        }
    }

    /**
     * Update session status (start or complete)
     */
    public function updateSessionStatus(Request $request)
    {
        $validated = $request->validate([
            'tracking_id' => 'nullable|exists:program_session_tracking,id',
            'enrollment_id' => 'required|exists:program_enrollments,id',
            'session_day' => 'required|integer',
            'status' => 'required|in:started,completed',
        ]);

        $user = auth()->user();

        // Verify enrollment belongs to user
        $enrollment = ProgramEnrollment::where('id', $validated['enrollment_id'])
            ->where('runner_id', $user->id)
            ->firstOrFail();

        // Create or update tracking
        $tracking = ProgramSessionTracking::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'session_day' => $validated['session_day'],
            ],
            [
                'status' => $validated['status'],
                'completed_at' => $validated['status'] === 'completed' ? now() : null,
            ]
        );

        return response()->json([
            'success' => true,
            'tracking' => $tracking,
        ]);
    }

    /**
     * Store or update custom workout
     */
    public function storeCustomWorkout(Request $request)
    {
        $validated = $request->validate([
            'workout_id' => 'nullable|exists:custom_workouts,id',
            'workout_date' => 'required|date',
            'type' => 'required|in:run,interval,tempo,easy_run,yoga,cycling,rest',
            'distance' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'difficulty' => 'required|in:easy,moderate,hard',
        ]);

        $user = auth()->user();

        // If workout_id exists, update; otherwise create new
        if (isset($validated['workout_id'])) {
            $workout = CustomWorkout::where('id', $validated['workout_id'])
                ->where('runner_id', $user->id)
                ->firstOrFail();

            $workout->update([
                'workout_date' => $validated['workout_date'],
                'type' => $validated['type'],
                'distance' => $validated['distance'] ?? null,
                'duration' => $validated['duration'] ?? null,
                'description' => $validated['description'] ?? null,
                'difficulty' => $validated['difficulty'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Workout berhasil diupdate',
                'workout' => $workout,
            ]);
        } else {
            $workout = CustomWorkout::create([
                'runner_id' => $user->id,
                'workout_date' => $validated['workout_date'],
                'type' => $validated['type'],
                'distance' => $validated['distance'] ?? null,
                'duration' => $validated['duration'] ?? null,
                'description' => $validated['description'] ?? null,
                'difficulty' => $validated['difficulty'],
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Workout berhasil ditambahkan',
                'workout' => $workout,
            ]);
        }
    }

    /**
     * Delete custom workout
     */
    public function deleteCustomWorkout(CustomWorkout $customWorkout)
    {
        $user = auth()->user();

        if ($customWorkout->runner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $customWorkout->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workout berhasil dihapus',
        ]);
    }

    /**
     * Delete enrollment (cancel program)
     */
    public function deleteEnrollment(ProgramEnrollment $enrollment)
    {
        $user = auth()->user();

        if ($enrollment->runner_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Delete all tracking records
            ProgramSessionTracking::where('enrollment_id', $enrollment->id)->delete();

            // Update enrollment status to cancelled
            $enrollment->update(['status' => 'cancelled']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Program berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus program: ' . $e->getMessage(),
            ], 500);
        }
    }
}
