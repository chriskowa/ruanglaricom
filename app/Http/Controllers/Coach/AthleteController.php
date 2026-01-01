<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\User;
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
        if ((int)$enrollment->program->coach_id !== (int)auth()->id()) {
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
        if ((int)$enrollment->program->coach_id !== (int)auth()->id()) {
            abort(403);
        }

        $program = $enrollment->program;
        $programJson = $program->program_json ?? [];
        $sessions = $programJson['sessions'] ?? [];
        $startDate = $enrollment->start_date;

        $events = [];

        foreach ($sessions as $index => $session) {
            if (!isset($session['day'])) continue;

            $sessionDate = $startDate->copy()->addDays((int)$session['day'] - 1);

            // Get tracking
            $tracking = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                ->where('session_day', (int)$session['day'])
                ->first();

            // Override date if rescheduled
            if ($tracking && $tracking->rescheduled_date) {
                $sessionDate = $tracking->rescheduled_date;
            }

            $status = $tracking->status ?? 'pending';
            $color = $status === 'completed' ? '#4CAF50' : ($status === 'started' ? '#FFC107' : '#9E9E9E');

            $events[] = [
                'id' => "session_{$index}",
                'title' => $session['type'] ?? 'Run',
                'start' => $sessionDate->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'session_day' => $session['day'],
                    'type' => $session['type'],
                    'distance' => $session['distance'] ?? null,
                    'description' => $session['description'] ?? null,
                    'status' => $status,
                    'tracking' => $tracking, // Contains feedback, rating, rpe, feeling
                ]
            ];
        }

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
        
        if ((int)$enrollment->program->coach_id !== (int)auth()->id()) {
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
        if ((int)$enrollment->program->coach_id !== (int)auth()->id()) {
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
}
