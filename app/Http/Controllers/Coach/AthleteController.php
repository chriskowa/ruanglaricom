<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use Illuminate\Http\Request;

class AthleteController extends Controller
{
    /**
     * List all athletes enrolled in coach's programs
     */
    public function index(Request $request)
    {
        $coachId = auth()->id();
        $search = $request->input('search');
        $programId = $request->input('program_id');

        // Get enrollments for programs created by this coach
        $query = ProgramEnrollment::whereHas('program', function ($q) use ($coachId) {
            $q->where('coach_id', $coachId);
        })
            ->with(['runner', 'program']);

        if ($search) {
            $query->whereHas('runner', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($programId) {
            $query->where('program_id', $programId);
        }

        $enrollments = $query->latest()->paginate(10);

        // Get coach's programs for filter dropdown
        $programs = \App\Models\Program::where('coach_id', $coachId)
            ->orderBy('title')
            ->get();

        return view('coach.athletes.index', compact('enrollments', 'programs', 'search', 'programId'));
    }

    /**
     * Show athlete details and calendar (Ghost View)
     */
    public function show($enrollmentId)
    {
        $enrollment = ProgramEnrollment::with(['runner', 'program'])
            ->findOrFail($enrollmentId);

        // Verify this enrollment belongs to a program owned by the coach
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        // Get runner profile for context
        $trainingProfile = app(\App\Services\RunningProfileService::class)->getProfile($enrollment->runner);

        return view('coach.athletes.show', compact('enrollment', 'trainingProfile'));
    }

    /**
     * Get athlete calendar events (Ghost View API)
     */
    public function calendarEvents(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);

        // Verify ownership
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $program = $enrollment->program;
        $programJson = $program->program_json ?? [];
        $sessions = $programJson['sessions'] ?? [];
        $startDate = $enrollment->start_date;

        if (! $startDate) {
            return response()->json([]);
        }

        $events = [];

        $typeColors = [
            'easy_run' => '#10B981', // Emerald 500
            'long_run' => '#6366F1', // Indigo 500
            'tempo' => '#F97316',    // Orange 500
            'interval' => '#EF4444', // Red 500
            'strength' => '#64748B', // Slate 500
            'race' => '#EAB308',     // Yellow 500
            'rest' => '#94A3B8',     // Slate 400
            'run' => '#3B82F6',      // Blue 500
            'recovery' => '#14B8A6', // Teal 500
            'yoga' => '#8B5CF6',     // Violet 500
        ];

        foreach ($sessions as $index => $session) {
            if (! isset($session['day'])) {
                continue;
            }

            $sessionDate = $startDate->copy()->addDays((int) $session['day'] - 1);

            // Get tracking
            $tracking = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                ->where('session_day', (int) $session['day'])
                ->first();

            // Override date if rescheduled
            if ($tracking && $tracking->rescheduled_date) {
                $sessionDate = $tracking->rescheduled_date;
            }

            $status = $tracking->status ?? 'pending';

            // Determine Color
            $type = $session['type'] ?? 'run';
            $baseColor = $typeColors[$type] ?? $typeColors['run'];

            // Visual logic:
            // - Pending/Future: Type Color
            // - Completed: Green Border + Type Color (or slight variation)
            // - Missed: Red

            if ($status === 'completed') {
                $backgroundColor = $baseColor;
                $borderColor = '#22C55E'; // Green border to indicate success
                $titlePrefix = '✅ ';
            } elseif ($status === 'missed') {
                $backgroundColor = '#EF4444'; // Red for missed
                $borderColor = '#EF4444';
                $titlePrefix = '❌ ';
            } else {
                $backgroundColor = $baseColor;
                $borderColor = $baseColor;
                $titlePrefix = '';
            }

            $events[] = [
                'id' => "session_{$index}",
                'title' => $titlePrefix.($session['type'] ?? 'Run'),
                'start' => $sessionDate->format('Y-m-d'),
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'textColor' => '#FFFFFF', // Ensure text is white
                'extendedProps' => [
                    'session_day' => $session['day'],
                    'type' => $session['type'],
                    'distance' => $session['distance'] ?? null,
                    'description' => $session['description'] ?? null,
                    'status' => $status,
                    'tracking' => $tracking, // Contains feedback, rating, rpe, feeling
                ],
            ];
        }

        // Fetch custom workouts & races
        $customWorkouts = \App\Models\CustomWorkout::where('runner_id', $enrollment->runner_id)->get();

        foreach ($customWorkouts as $workout) {
            $type = $workout->type;
            $baseColor = $typeColors[$type] ?? $typeColors['run'];

            if ($workout->status === 'completed') {
                $backgroundColor = $baseColor;
                $borderColor = '#22C55E';
                $titlePrefix = '✅ ';
            } elseif ($workout->status === 'missed') {
                $backgroundColor = '#EF4444';
                $borderColor = '#EF4444';
                $titlePrefix = '❌ ';
            } else {
                $backgroundColor = $baseColor;
                $borderColor = $baseColor;
                $titlePrefix = '';
            }

            if ($workout->type === 'race') {
                // Race special handling
                $backgroundColor = $typeColors['race'];
                $borderColor = $typeColors['race'];
                $titlePrefix = '🏆 '; // Always trophy for race
            }

            $title = $workout->type === 'race'
                ? $titlePrefix.($workout->workout_structure['race_name'] ?? 'Race')
                : $titlePrefix.ucfirst(str_replace('_', ' ', $workout->type));

            $events[] = [
                'id' => "custom_{$workout->id}",
                'title' => $title,
                'start' => $workout->workout_date->format('Y-m-d'),
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'textColor' => '#FFFFFF',
                'extendedProps' => [
                    'is_custom' => true,
                    'id' => $workout->id,
                    'type' => $workout->type,
                    'distance' => $workout->distance,
                    'duration' => $workout->duration,
                    'difficulty' => $workout->difficulty,
                    'description' => $workout->description,
                    'notes' => $workout->notes,
                    'status' => $workout->status,
                    'workout_structure' => $workout->workout_structure,
                    'tracking' => null, // Placeholder for now
                ],
            ];
        }

        // Dedup: jika ada custom untuk tanggal tertentu, sembunyikan sesi program default di tanggal itu
        $customDates = collect($customWorkouts)->map(fn ($w) => $w->workout_date->format('Y-m-d'))->unique()->toArray();
        $events = array_values(array_filter($events, function ($ev) use ($customDates) {
            $isCustom = isset($ev['extendedProps']['is_custom']) && $ev['extendedProps']['is_custom'];
            if ($isCustom) {
                return true;
            }

            return ! in_array($ev['start'], $customDates);
        }));

        return response()->json($events);
    }

    /**
     * Submit coach feedback and rating
     */
    public function storeFeedback(Request $request, $enrollmentId)
    {
        $validated = $request->validate([
            'session_day' => 'required|integer',
            'coach_feedback' => 'nullable|string',
            'coach_rating' => 'required|integer|min:1|max:5',
        ]);

        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);

        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $tracking = ProgramSessionTracking::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'session_day' => $validated['session_day'],
            ],
            [
                'coach_feedback' => $validated['coach_feedback'],
                'coach_rating' => $validated['coach_rating'],
                // Ensure status is marked as completed if coach grades it?
                // Usually coach only grades completed sessions, but let's keep status as is or default to completed if not set
            ]
        );

        return response()->json(['success' => true, 'message' => 'Feedback saved']);
    }

    public function storeRace(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'race_name' => 'required|string',
            'workout_date' => 'required|date',
            'distance' => 'nullable|numeric',
            'dist_label' => 'nullable|string',
            'goal_time' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $workout = \App\Models\CustomWorkout::create([
            'runner_id' => $enrollment->runner_id,
            'workout_date' => $validated['workout_date'],
            'type' => 'race',
            'difficulty' => 'hard',
            'distance' => $validated['distance'] ?? null,
            'description' => $validated['notes'] ?? null,
            'status' => 'pending',
            'workout_structure' => [
                'race_name' => $validated['race_name'],
                'goal_time' => $validated['goal_time'] ?? null,
                'dist_label' => $validated['dist_label'] ?? null,
            ],
        ]);

        return response()->json(['success' => true]);
    }

    public function storeWorkout(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'workout_date' => 'required|date',
            'type' => 'required|string',
            'difficulty' => 'required|string',
            'distance' => 'nullable|numeric',
            'duration' => 'nullable|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'workout_structure' => 'nullable|array',
        ]);

        // Upsert per tanggal untuk runner ini (override)
        $existing = \App\Models\CustomWorkout::where('runner_id', $enrollment->runner_id)
            ->whereDate('workout_date', $validated['workout_date'])
            ->first();

        if ($existing) {
            $existing->update([
                'type' => $validated['type'],
                'difficulty' => $validated['difficulty'],
                'distance' => $validated['distance'] ?? null,
                'duration' => $validated['duration'] ?? null,
                'description' => $validated['description'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'workout_structure' => $validated['workout_structure'] ?? null,
                'status' => $existing->status ?? 'pending',
            ]);

            // Notify Runner (updated)
            \App\Models\Notification::create([
                'user_id' => $enrollment->runner_id,
                'type' => 'workout_updated',
                'title' => 'Workout Updated',
                'message' => 'Coach '.auth()->user()->name.' updated your workout for '.\Carbon\Carbon::parse($validated['workout_date'])->format('d M Y'),
                'reference_type' => 'custom_workout',
                'reference_id' => $existing->id,
                'is_read' => false,
            ]);
        } else {
            $workout = \App\Models\CustomWorkout::create([
                'runner_id' => $enrollment->runner_id,
                'workout_date' => $validated['workout_date'],
                'type' => $validated['type'],
                'difficulty' => $validated['difficulty'],
                'distance' => $validated['distance'] ?? null,
                'duration' => $validated['duration'] ?? null,
                'description' => $validated['description'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'workout_structure' => $validated['workout_structure'] ?? null,
                'status' => 'pending',
            ]);

            // Notify Runner (assigned)
            \App\Models\Notification::create([
                'user_id' => $enrollment->runner_id,
                'type' => 'workout_assigned',
                'title' => 'New Workout Assigned',
                'message' => 'Coach '.auth()->user()->name.' assigned a new workout for '.\Carbon\Carbon::parse($validated['workout_date'])->format('d M Y'),
                'reference_type' => 'custom_workout',
                'reference_id' => $workout->id,
                'is_read' => false,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function updateWorkout(Request $request, $enrollmentId, $customWorkoutId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $workout = \App\Models\CustomWorkout::findOrFail($customWorkoutId);

        // Verify workout belongs to this runner
        if ((int) $workout->runner_id !== (int) $enrollment->runner_id) {
            abort(403, 'Workout does not belong to this athlete');
        }

        $validated = $request->validate([
            'workout_date' => 'required|date',
            'type' => 'required|string',
            'difficulty' => 'required|string',
            'distance' => 'nullable|numeric',
            'duration' => 'nullable|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'workout_structure' => 'nullable|array',
        ]);

        $workout->update([
            'workout_date' => $validated['workout_date'],
            'type' => $validated['type'],
            'difficulty' => $validated['difficulty'],
            'distance' => $validated['distance'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'workout_structure' => $validated['workout_structure'] ?? null,
        ]);

        // Notify Runner
        \App\Models\Notification::create([
            'user_id' => $enrollment->runner_id,
            'type' => 'workout_updated',
            'title' => 'Workout Updated',
            'message' => 'Coach '.auth()->user()->name.' updated your workout for '.\Carbon\Carbon::parse($validated['workout_date'])->format('d M Y'),
            'reference_type' => 'custom_workout',
            'reference_id' => $workout->id,
            'is_read' => false,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroyWorkout($enrollmentId, $customWorkoutId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $workout = \App\Models\CustomWorkout::findOrFail($customWorkoutId);

        // Verify workout belongs to this runner
        if ((int) $workout->runner_id !== (int) $enrollment->runner_id) {
            abort(403, 'Workout does not belong to this athlete');
        }

        $workout->delete();

        return response()->json(['success' => true]);
    }
}
