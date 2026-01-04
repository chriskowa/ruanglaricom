<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\CustomWorkout;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
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
            ->with(['program.coach'])
            ->get();

        // Get Program Bag (Purchased)
        $programBag = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'purchased')
            ->with(['program.coach'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get Cancelled Programs (History/Archive)
        $cancelledPrograms = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'cancelled')
            ->with(['program.coach'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Training Profile Data via service
        $trainingProfile = app(\App\Services\RunningProfileService::class)->getProfile($user);

        // Check 40 Days Challenge Enrollment
        $isEnrolled40Days = $enrollments->contains(function ($enrollment) {
            return $enrollment->program_id == 9 ||
                   ($enrollment->program && ($enrollment->program->hardcoded === '40days' || \Illuminate\Support\Str::contains($enrollment->program->slug, '40days')));
        });

        return view('runner.calendar_modern', [
            'enrollments' => $enrollments,
            'programBag' => $programBag,
            'cancelledPrograms' => $cancelledPrograms,
            'trainingProfile' => $trainingProfile,
            'isEnrolled40Days' => $isEnrolled40Days,
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

        // Get user training paces
        $paces = $user->training_paces;

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

            if (! is_array($sessions) || empty($sessions)) {
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
                if (! isset($session['day']) || ! is_numeric($session['day'])) {
                    continue;
                }

                try {
                    $sessionDate = $startDate->copy()->addDays((int) $session['day'] - 1);
                } catch (\Exception $e) {
                    continue;
                }

                // Check for rescheduled date
                $tracking = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                    ->where('session_day', (int) $session['day'])
                    ->first();

                if ($tracking && $tracking->rescheduled_date) {
                    $sessionDate = Carbon::parse($tracking->rescheduled_date);
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

                $phase = $this->getTrainingPhase((int) $session['day'], $totalWeeks);
                $colors = $this->getEventColors($difficulty, $phase);

                $sessionType = $session['type'] ?? 'Run';
                $paceInfo = $this->getPaceForSessionType($sessionType, $paces);
                $title = $sessionType.($paceInfo ? " ({$paceInfo})" : '');

                $events[] = [
                    'id' => "program_{$enrollment->id}_session_{$index}",
                    'title' => $title,
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
                        'target_pace' => $paceInfo,
                    ],
                ];
            }
        }

        // Add custom workouts
        $customWorkouts = CustomWorkout::where('runner_id', $user->id)
            ->when($start, function ($query) use ($start, $end) {
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

            if ($workout->type === 'race') {
                $colors['background'] = '#FFD700'; // Gold
                $colors['border'] = '#DAA520';
                $colors['text'] = '#000000';
            }

            $events[] = [
                'id' => "custom_workout_{$workout->id}",
                'title' => ($workout->type === 'race' ? '🏆 ' : '').($workout->workout_structure['race_name'] ?? $workout->type ?? 'Run'),
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
                        'workout_structure' => $workout->workout_structure,
                    ],
                ],
            ];
        }

        // Dedup: jika ada custom pada tanggal tertentu, sembunyikan program_session tanggal itu
        $customDates = collect($customWorkouts)->map(fn ($w) => $w->workout_date->format('Y-m-d'))->unique()->toArray();
        $events = array_values(array_filter($events, function ($ev) use ($customDates) {
            $isProgram = isset($ev['extendedProps']['type']) && $ev['extendedProps']['type'] === 'program_session';
            if (! $isProgram) {
                return true;
            }

            return ! in_array($ev['start'], $customDates);
        }));

        return response()->json($events);
    }

    /**
     * Helper to get pace string based on session type
     */
    private function getPaceForSessionType($type, $paces)
    {
        if (! $paces) {
            return null;
        }

        $type = strtolower($type);
        $pace = null;
        $label = '';

        if (str_contains($type, 'easy') || str_contains($type, 'long') || str_contains($type, 'recovery') || str_contains($type, 'warmup') || str_contains($type, 'cool')) {
            $pace = $paces['E'] ?? null;
            $label = 'E';
        } elseif (str_contains($type, 'tempo') || str_contains($type, 'threshold')) {
            $pace = $paces['T'] ?? null;
            $label = 'T';
        } elseif (str_contains($type, 'interval')) {
            $pace = $paces['I'] ?? null;
            $label = 'I';
        } elseif (str_contains($type, 'repetition')) {
            $pace = $paces['R'] ?? null;
            $label = 'R';
        } elseif (str_contains($type, 'marathon')) {
            $pace = $paces['M'] ?? null;
            $label = 'M';
        }

        if ($pace) {
            // Format pace min/km
            $m = floor($pace);
            $s = round(($pace - $m) * 60);

            return sprintf('@ %d:%02d/km', $m, $s);
        }

        return null;
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

            // Get user training paces
            $paces = $user->training_paces;

            $enrollments = ProgramEnrollment::where('runner_id', $user->id)
                ->where('status', 'active')
                ->whereHas('program', function ($query) {
                    $query->where('is_active', true);
                })
                ->with('program')
                ->get();

            $workoutPlans = [];
            $plansByDate = [];

            foreach ($enrollments as $enrollment) {
                $program = $enrollment->program;

                if (! $program) {
                    continue;
                }

                $programJson = $program->program_json ?? [];
                $sessions = $programJson['sessions'] ?? [];

                if (empty($sessions) || ! is_array($sessions)) {
                    continue;
                }

                try {
                    $startDate = Carbon::parse($enrollment->start_date);
                } catch (\Exception $e) {
                    continue;
                }

                foreach ($sessions as $index => $session) {
                    // Skip if session doesn't have 'day' field
                    if (! isset($session['day']) || ! is_numeric($session['day'])) {
                        continue;
                    }

                    try {
                        $sessionDate = $startDate->copy()->addDays((int) $session['day'] - 1);
                    } catch (\Exception $e) {
                        continue;
                    }

                    // Get tracking status and rescheduled date
                    $tracking = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                        ->where('session_day', (int) $session['day'])
                        ->first();

                    if ($tracking && $tracking->rescheduled_date) {
                        $sessionDate = Carbon::parse($tracking->rescheduled_date);
                    }

                    // Check if session is in the future
                    // Removed to show all upcoming plans as per user request
                    // if ($sessionDate->isFuture()) {
                    //    continue;
                    // }

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
                    if ($filter === 'in_progress' && $status !== 'started') {
                        continue;
                    }

                    $sessionType = $session['type'] ?? 'run';
                    $paceInfo = $this->getPaceForSessionType($sessionType, $paces);
                    $description = $session['description'] ?? null;
                    if ($paceInfo) {
                        $description = ($description ? $description."\n" : '').'Target Pace: '.$paceInfo;
                    }

                    $plan = [
                        'id' => $tracking ? $tracking->id : null,
                        'tracking_id' => $tracking ? $tracking->id : null,
                        'enrollment_id' => $enrollment->id,
                        'program_id' => $program->id,
                        'program_title' => $program->title,
                        'program_difficulty' => $program->difficulty ?? 'beginner',
                        'session_day' => (int) $session['day'],
                        'date' => $sessionDate->format('Y-m-d'),
                        'date_formatted' => $sessionDate->format('d M Y'),
                        'day_name' => $sessionDate->format('D'),
                        'day_number' => $sessionDate->format('d'),
                        'type' => $sessionType,
                        'distance' => $session['distance'] ?? null,
                        'duration' => $session['duration'] ?? null,
                        'description' => $description,
                        'status' => $status,
                        'completed_at' => $tracking && $tracking->completed_at ? $tracking->completed_at->format('Y-m-d H:i:s') : null,
                        'strava_link' => $tracking ? $tracking->strava_link : null,
                        'notes' => $tracking ? $tracking->notes : null,
                        'rpe' => $tracking ? $tracking->rpe : null,
                        'feeling' => $tracking ? $tracking->feeling : null,
                        'coach_feedback' => $tracking ? $tracking->coach_feedback : null,
                        'coach_rating' => $tracking ? $tracking->coach_rating : null,
                        'phase' => $this->getTrainingPhase($session['day'], $program->duration_weeks ?? 12),
                        'target_pace' => $paceInfo,
                    ];
                    $plansByDate[$plan['date']] = $plan;
                }
            }

            // Seed from program plans map
            $workoutPlans = array_values($plansByDate);

            // Get custom workouts
            $customWorkouts = CustomWorkout::where('runner_id', $user->id)
                ->get();

            foreach ($customWorkouts as $workout) {
                $status = $workout->status ?? 'pending';

                // Apply filter
                if ($filter === 'unfinished' && $status === 'completed') {
                    continue;
                }
                if ($filter === 'finished' && $status !== 'completed') {
                    continue;
                }
                if ($filter === 'in_progress' && $status !== 'started') {
                    continue;
                }

                $customPlan = [
                    'id' => 'custom_'.$workout->id,
                    'type' => 'custom_workout',
                    'activity_type' => $workout->type ?? 'run',
                    'workout_id' => $workout->id,
                    'date' => $workout->workout_date->format('Y-m-d'),
                    'date_formatted' => $workout->workout_date->format('d M Y'),
                    'day_name' => $workout->workout_date->format('D'),
                    'day_number' => $workout->workout_date->format('d'),
                    'description' => $workout->description ?? $workout->type,
                    'distance' => $workout->distance,
                    'duration' => $workout->duration,
                    'difficulty' => $workout->difficulty,
                    'status' => $status,
                    'completed_at' => $workout->completed_at ? $workout->completed_at->format('Y-m-d H:i:s') : null,
                    'workout_structure' => $workout->workout_structure,
                    'program_title' => 'Custom Workout',
                    'notes' => $workout->notes,
                    'source' => 'custom',
                ];
                // Override program plan if same date
                $plansByDate[$customPlan['date']] = $customPlan;
                $workoutPlans = array_values($plansByDate);
            }

            // Re-sort with custom workouts included
            usort($workoutPlans, function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            return response()->json($workoutPlans);
        } catch (\Exception $e) {
            \Log::error('Error in workoutPlans: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Gagal memuat workout plans: '.$e->getMessage()], 500);
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
        \Log::info('updateSessionStatus payload:', $request->all());

        $validator = \Validator::make($request->all(), [
            'status' => 'required|in:started,completed',
            'strava_link' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:1000',
            'rpe' => 'nullable|integer|min:1|max:10',
            'feeling' => 'nullable|string|in:strong,good,average,weak,terrible',
            'enrollment_id' => 'nullable|exists:program_enrollments,id',
            'workout_id' => 'nullable|exists:custom_workouts,id',
            'session_day' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            \Log::warning('updateSessionStatus validation failed:', $validator->errors()->toArray());
            return response()->json([
                'message' => 'Validation failed', 
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $user = auth()->user();

        // 1. Check if Custom Workout
        if (!empty($validated['workout_id'])) {
            $workout = CustomWorkout::where('id', $validated['workout_id'])
                ->where('runner_id', $user->id)
                ->firstOrFail();

            $updateData = ['status' => $validated['status']];
            
            if ($request->has('strava_link')) $updateData['strava_link'] = $validated['strava_link'];
            if ($request->has('notes')) $updateData['notes'] = $validated['notes'];
            if ($request->has('rpe')) $updateData['rpe'] = $validated['rpe'];
            if ($request->has('feeling')) $updateData['feeling'] = $validated['feeling'];

            $workout->update($updateData);

            return response()->json([
                'success' => true,
                'tracking' => $workout,
            ]);
        }

        // 2. Check if Program Session
        if (!empty($validated['enrollment_id']) && isset($validated['session_day'])) {
            // Verify enrollment belongs to user
            $enrollment = ProgramEnrollment::where('id', $validated['enrollment_id'])
                ->where('runner_id', $user->id)
                ->firstOrFail();

            // Create or update tracking
            $tracking = ProgramSessionTracking::updateOrCreate(
                [
                    'enrollment_id' => $validated['enrollment_id'],
                    'session_day' => $validated['session_day'],
                ],
                [
                    'status' => $validated['status'],
                    'strava_link' => $validated['strava_link'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'rpe' => $validated['rpe'] ?? null,
                    'feeling' => $validated['feeling'] ?? null,
                    'completed_at' => $validated['status'] === 'completed' ? now() : null,
                ]
            );

            // Update program progress
            // $this->updateProgramProgress($enrollment);

            return response()->json([
                'success' => true,
                'tracking' => $tracking,
            ]);
        }

        return response()->json(['message' => 'Missing workout identification (enrollment_id+session_day OR workout_id)'], 422);
    }

    /**
     * Store or update custom workout
     */
    public function storeCustomWorkout(Request $request)
    {
        $validated = $request->validate([
            'workout_id' => 'nullable|exists:custom_workouts,id',
            'workout_date' => 'required|date',
            'type' => 'required|in:run,interval,tempo,easy_run,yoga,cycling,rest,race',
            'distance' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
            'difficulty' => 'required|in:easy,moderate,hard',
            'workout_structure' => 'nullable|array',
        ]);

        $user = auth()->user();

        // If workout_id exists, update; otherwise upsert by date
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
                'workout_structure' => $validated['workout_structure'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Workout berhasil diupdate',
                'workout' => $workout,
            ]);
        } else {
            $existing = CustomWorkout::where('runner_id', $user->id)
                ->whereDate('workout_date', $validated['workout_date'])
                ->first();

            if ($existing) {
                $existing->update([
                    'type' => $validated['type'],
                    'distance' => $validated['distance'] ?? null,
                    'duration' => $validated['duration'] ?? null,
                    'description' => $validated['description'] ?? null,
                    'difficulty' => $validated['difficulty'],
                    'workout_structure' => $validated['workout_structure'] ?? null,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Workout berhasil diupdate',
                    'workout' => $existing,
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
                    'workout_structure' => $validated['workout_structure'] ?? null,
                    'status' => 'pending',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Workout berhasil ditambahkan',
                    'workout' => $workout,
                ]);
            }
        }
    }

    /**
     * Delete custom workout
     */
    public function deleteCustomWorkout(CustomWorkout $customWorkout)
    {
        $user = auth()->user();

        if ((int) $customWorkout->runner_id !== (int) $user->id) {
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
                'message' => 'Gagal menghapus program: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset active plan to program bag
     */
    public function resetPlan(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:program_enrollments,id',
        ]);

        $enrollment = ProgramEnrollment::where('id', $validated['enrollment_id'])
            ->where('runner_id', auth()->id())
            ->firstOrFail();

        DB::transaction(function () use ($enrollment) {
            // Delete tracking data
            ProgramSessionTracking::where('enrollment_id', $enrollment->id)->delete();

            // Reset enrollment
            $enrollment->update([
                'status' => 'purchased',
                'start_date' => null,
                'end_date' => null,
            ]);
        });

        return response()->json(['success' => true, 'message' => 'Plan has been reset and moved to Program Bag.']);
    }

    /**
     * Apply a program from the bag
     */
    public function applyProgram(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:program_enrollments,id',
            'start_date' => 'required|date',
        ]);

        $user = auth()->user();

        // Check if user already has an active program
        $hasActive = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->exists();

        if ($hasActive) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sedang menjalankan program aktif. Harap selesaikan atau reset program saat ini terlebih dahulu.',
            ], 422);
        }

        $enrollment = ProgramEnrollment::where('id', $validated['enrollment_id'])
            ->where('runner_id', $user->id)
            ->where('status', 'purchased')
            ->firstOrFail();

        $program = $enrollment->program;
        $startDate = Carbon::parse($validated['start_date']);
        $durationWeeks = $program->duration_weeks ?? 12;
        $endDate = $startDate->copy()->addWeeks($durationWeeks);

        $enrollment->update([
            'status' => 'active',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return response()->json(['success' => true, 'message' => 'Program applied successfully!']);
    }

    /**
     * Restore cancelled program to bag
     */
    public function restoreProgram(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:program_enrollments,id',
        ]);

        $enrollment = ProgramEnrollment::where('id', $validated['enrollment_id'])
            ->where('runner_id', auth()->id())
            ->where('status', 'cancelled')
            ->firstOrFail();

        $enrollment->update([
            'status' => 'purchased',
            'start_date' => null,
            'end_date' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Program dikembalikan ke Program Bag.']);
    }

    /**
     * Reset all active plans to program bag
     */
    public function resetPlanList(Request $request)
    {
        $user = auth()->user();

        DB::transaction(function () use ($user) {
            // Get all active enrollments
            $enrollments = ProgramEnrollment::where('runner_id', $user->id)
                ->where('status', 'active')
                ->get();

            foreach ($enrollments as $enrollment) {
                // Delete tracking data
                ProgramSessionTracking::where('enrollment_id', $enrollment->id)->delete();

                // Reset enrollment
                $enrollment->update([
                    'status' => 'purchased',
                    'start_date' => null,
                    'end_date' => null,
                ]);
            }
        });

        return response()->json(['success' => true, 'message' => 'All active plans have been reset to Program Bag.']);
    }

    /**
     * Get weekly volume data for chart
     */
    public function weeklyVolume(Request $request)
    {
        $user = auth()->user();

        // Range: 12 weeks back, 4 weeks forward
        $start = now()->startOfWeek()->subWeeks(12);
        $end = now()->endOfWeek()->addWeeks(4);

        $weeks = [];
        $current = $start->copy();
        while ($current <= $end) {
            // Use o-W for ISO Year-Week to handle year boundaries correctly
            $key = $current->format('o-W');
            $weeks[$key] = [
                'week_label' => $current->format('d M'),
                'full_date' => $current->format('Y-m-d'),
                'planned' => 0,
                'actual' => 0,
            ];
            $current->addWeek();
        }

        // 1. Process Program Enrollments
        $enrollments = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->with('program')
            ->get();

        foreach ($enrollments as $enrollment) {
            $program = $enrollment->program;
            if (! $program) {
                continue;
            }

            $sessions = $program->program_json['sessions'] ?? [];
            $startDate = $enrollment->start_date;
            if (! $startDate) {
                continue;
            }

            // Get trackings for this enrollment
            $trackings = ProgramSessionTracking::where('enrollment_id', $enrollment->id)->get()->keyBy('session_day');

            foreach ($sessions as $session) {
                if (! isset($session['day'])) {
                    continue;
                }

                $day = (int) $session['day'];
                try {
                    $sessionDate = $startDate->copy()->addDays($day - 1);
                } catch (\Exception $e) {
                    continue;
                }

                // Check override
                if (isset($trackings[$day]) && $trackings[$day]->rescheduled_date) {
                    $sessionDate = $trackings[$day]->rescheduled_date;
                }

                if ($sessionDate->lt($start) || $sessionDate->gt($end)) {
                    continue;
                }

                $weekKey = $sessionDate->format('o-W');
                $dist = (float) ($session['distance'] ?? 0);

                if (isset($weeks[$weekKey])) {
                    $weeks[$weekKey]['planned'] += $dist;

                    // Add to actual if completed
                    if (isset($trackings[$day]) && $trackings[$day]->status === 'completed') {
                        $weeks[$weekKey]['actual'] += $dist;
                    }
                }
            }
        }

        // 2. Custom Workouts
        $customWorkouts = CustomWorkout::where('runner_id', $user->id)
            ->whereBetween('workout_date', [$start, $end])
            ->get();

        foreach ($customWorkouts as $cw) {
            $weekKey = $cw->workout_date->format('o-W');
            if (isset($weeks[$weekKey])) {
                $weeks[$weekKey]['planned'] += $cw->distance;
                if ($cw->status === 'completed') {
                    $weeks[$weekKey]['actual'] += $cw->distance;
                }
            }
        }

        return response()->json(array_values($weeks));
    }

    /**
     * Reschedule a workout (Drag & Drop)
     */
    public function reschedule(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:program_session,custom_workout',
            'new_date' => 'required|date',
            // For custom workout
            'workout_id' => 'nullable|required_if:type,custom_workout|exists:custom_workouts,id',
            // For program session
            'enrollment_id' => 'nullable|required_if:type,program_session|exists:program_enrollments,id',
            'session_day' => 'nullable|required_if:type,program_session|integer',
        ]);

        $user = auth()->user();
        $newDate = Carbon::parse($validated['new_date']);

        if ($validated['type'] === 'custom_workout') {
            $workout = CustomWorkout::where('id', $validated['workout_id'])
                ->where('runner_id', $user->id)
                ->firstOrFail();

            $workout->update(['workout_date' => $newDate]);

            return response()->json(['success' => true, 'message' => 'Custom workout rescheduled']);
        } else {
            $enrollment = ProgramEnrollment::where('id', $validated['enrollment_id'])
                ->where('runner_id', $user->id)
                ->firstOrFail();

            ProgramSessionTracking::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'session_day' => $validated['session_day'],
                ],
                [
                    'rescheduled_date' => $newDate,
                ]
            );

            return response()->json(['success' => true, 'message' => 'Program session rescheduled']);
        }
    }

    /**
     * Reschedule entire program (change start date)
     */
    public function rescheduleProgram(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:program_enrollments,id',
            'new_start_date' => 'required|date',
        ]);

        $user = auth()->user();
        $enrollment = ProgramEnrollment::where('id', $validated['enrollment_id'])
            ->where('runner_id', $user->id)
            ->firstOrFail();

        $startDate = Carbon::parse($validated['new_start_date']);
        $program = $enrollment->program;
        $durationWeeks = $program->duration_weeks ?? 12;
        $endDate = $startDate->copy()->addWeeks($durationWeeks);

        DB::transaction(function () use ($enrollment, $startDate, $endDate) {
            // Update enrollment dates
            $enrollment->update([
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            // Clear any individual session reschedules so they align with new start date
            ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                ->update(['rescheduled_date' => null]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Program rescheduled successfully',
        ]);
    }

    /**
     * Update Runner PB
     */
    public function updatePb(Request $request)
    {
        $validated = $request->validate([
            'pb_5k' => 'nullable|regex:/^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/',
            'pb_10k' => 'nullable|regex:/^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/',
            'pb_hm' => 'nullable|regex:/^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/',
            'pb_fm' => 'nullable|regex:/^[0-9]{2}:[0-5][0-9]:[0-5][0-9]$/',
        ]);

        $user = auth()->user();
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Personal Best updated',
            'vdot' => $user->vdot,
            'paces' => $user->training_paces,
            'equivalent_race_times' => $user->equivalent_race_times,
        ]);
    }

    /**
     * Update Runner Weekly Target
     */
    public function updateWeeklyTarget(Request $request)
    {
        $validated = $request->validate([
            'weekly_km_target' => 'nullable|numeric|min:0|max:999.99',
        ]);

        $user = auth()->user();
        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Weekly target updated',
            'weekly_km_target' => $user->weekly_km_target,
        ]);
    }
}
