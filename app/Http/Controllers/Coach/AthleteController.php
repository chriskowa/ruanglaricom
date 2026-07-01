<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\StravaActivity;
use App\Services\StravaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        $vdotMin = $request->input('vdot_min');
        $vdotMax = $request->input('vdot_max');
        $proximityRunnerId = $request->input('proximity_runner_id');
        $proximityDiff = $request->input('proximity_diff', 3.0);
        $sortBy = $request->input('sort_by', 'latest');
        $tab = $request->input('tab', 'all'); // 'all' or 'clusters'

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

        // Fetch all matching records first to filter/sort by PHP dynamic attributes (vdot)
        $allEnrollments = $query->get();

        // Filter by VDOT range in PHP
        if ($vdotMin !== null && $vdotMin !== '') {
            $allEnrollments = $allEnrollments->filter(fn($e) => ($e->runner->vdot ?? 0) >= (float)$vdotMin);
        }
        if ($vdotMax !== null && $vdotMax !== '') {
            $allEnrollments = $allEnrollments->filter(fn($e) => ($e->runner->vdot ?? 999) <= (float)$vdotMax);
        }

        // Proximity filter
        if ($proximityRunnerId) {
            $refRunner = \App\Models\User::find($proximityRunnerId);
            if ($refRunner && $refRunner->vdot) {
                $refVdot = $refRunner->vdot;
                $allEnrollments = $allEnrollments->filter(fn($e) => abs(($e->runner->vdot ?? 0) - $refVdot) <= (float)$proximityDiff);
            }
        }

        // Calculate clusters from the filtered list (for the clusters view)
        $sortedForClusters = $allEnrollments->filter(fn($e) => $e->runner->vdot !== null)
            ->sortByDesc(fn($e) => $e->runner->vdot);

        $vdotClusters = [];
        $currentCluster = [];
        $lastVdot = null;

        foreach ($sortedForClusters as $e) {
            $vdot = $e->runner->vdot;
            if ($lastVdot === null) {
                $currentCluster[] = $e;
            } elseif (abs($lastVdot - $vdot) <= 3.0) {
                $currentCluster[] = $e;
            } else {
                $vdotClusters[] = $currentCluster;
                $currentCluster = [$e];
            }
            $lastVdot = $vdot;
        }
        if (!empty($currentCluster)) {
            $vdotClusters[] = $currentCluster;
        }

        $noVdotAthletes = $allEnrollments->filter(fn($e) => $e->runner->vdot === null)->values();

        // Sort collection for flat list
        if ($sortBy === 'vdot_desc') {
            $allEnrollments = $allEnrollments->sortByDesc(fn($e) => $e->runner->vdot ?? -1);
        } elseif ($sortBy === 'vdot_asc') {
            $allEnrollments = $allEnrollments->sortBy(fn($e) => $e->runner->vdot ?? 999);
        } elseif ($sortBy === 'name') {
            $allEnrollments = $allEnrollments->sortBy(fn($e) => strtolower($e->runner->name));
        } else {
            $allEnrollments = $allEnrollments->sortByDesc('created_at');
        }

        // Paginate manually for the flat list view
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $currentItems = $allEnrollments->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $enrollments = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $allEnrollments->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );

        // Get coach's programs for filter dropdown
        $programs = \App\Models\Program::where('coach_id', $coachId)
            ->orderBy('title')
            ->get();

        // Get unique list of coach's athletes for proximity reference
        $allCoachAthletes = ProgramEnrollment::whereHas('program', function ($q) use ($coachId) {
            $q->where('coach_id', $coachId);
        })
            ->with('runner')
            ->get()
            ->unique('runner_id')
            ->map(fn($e) => $e->runner)
            ->filter(fn($r) => $r->vdot !== null)
            ->sortBy('name');

        if ($request->ajax()) {
            return view('coach.athletes._list', compact(
                'enrollments', 
                'vdotClusters', 
                'noVdotAthletes', 
                'tab'
            ))->render();
        }

        return view('coach.athletes.index', compact(
            'enrollments', 
            'programs', 
            'search', 
            'programId',
            'vdotMin',
            'vdotMax',
            'proximityRunnerId',
            'proximityDiff',
            'sortBy',
            'tab',
            'allCoachAthletes',
            'vdotClusters',
            'noVdotAthletes'
        ));
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
        $trainingProfile['strava_connected'] = !empty($enrollment->runner->strava_access_token);
        $trainingProfile['phone'] = $enrollment->runner->phone;

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
        $rangeStart = null;
        $rangeEnd = null;
        if ($request->filled('start') && $request->filled('end')) {
            try {
                $rangeStart = Carbon::parse($request->get('start'))->startOfDay();
                $rangeEnd = Carbon::parse($request->get('end'))->endOfDay();
            } catch (\Throwable $e) {
                $rangeStart = null;
                $rangeEnd = null;
            }
        }

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

        $stravaActivities = StravaActivity::query()
            ->where('user_id', $enrollment->runner_id)
            ->when($rangeStart && $rangeEnd, function ($q) use ($rangeStart, $rangeEnd) {
                $q->whereBetween('start_date', [$rangeStart, $rangeEnd]);
            })
            ->orderBy('start_date')
            ->get();

        foreach ($stravaActivities as $act) {
            if (! $act->local_start_date) {
                continue;
            }

            $t = strtolower((string) $act->type);

            $events[] = [
                'id' => 'strava_'.$act->strava_activity_id,
                'title' => 'Strava Activity',
                'start' => $act->local_start_date->format('Y-m-d\TH:i:s'),
                'end' => $act->local_start_date->copy()->addSeconds((int) ($act->elapsed_time_s ?: $act->moving_time_s ?: 3600))->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'backgroundColor' => '#1F2937',
                'borderColor' => '#FC4C02',
                'textColor' => '#FFFFFF',
                'extendedProps' => [
                    'event_type' => 'strava_activity',
                    'type' => $t ?: 'run',
                    'status' => 'completed',
                    'is_strava' => true,
                    'strava_activity_id' => $act->strava_activity_id,
                    'strava_url' => $act->strava_url,
                    'distance' => $act->distance_m ? round(((float) $act->distance_m) / 1000, 2) : null,
                    'duration' => $act->moving_time_s ? gmdate('H:i:s', (int) $act->moving_time_s) : null,
                    'description' => $act->name,
                    'tracking' => null,
                ],
            ];
        }

        return response()->json($events);
    }

    public function stravaActivityDetails(Request $request, $enrollmentId, string $stravaActivityId)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        if (! is_numeric($stravaActivityId) || (string) $stravaActivityId === '0') {
            return response()->json(['success' => false, 'message' => 'Invalid activity id.'], 422);
        }
        $activityId = (string) $stravaActivityId;

        $runner = $enrollment->runner;
        $api = app(StravaApiService::class);

        $activity = StravaActivity::query()
            ->where('user_id', $runner->id)
            ->where('strava_activity_id', $activityId)
            ->first();

        if (! $activity) {
            $details = $api->fetchActivityDetails($runner, $activityId);
            if (! $details) {
                return response()->json(['success' => false, 'message' => 'Gagal mengambil detail aktivitas Strava.'], 422);
            }

            $activity = StravaActivity::create([
                'user_id' => $runner->id,
                'strava_activity_id' => $activityId,
                'name' => data_get($details, 'name'),
                'type' => data_get($details, 'type'),
                'start_date' => data_get($details, 'start_date_local') ?: data_get($details, 'start_date'),
                'distance_m' => (int) round((float) data_get($details, 'distance', 0)),
                'moving_time_s' => (int) data_get($details, 'moving_time', 0),
                'elapsed_time_s' => (int) data_get($details, 'elapsed_time', 0),
                'average_speed' => data_get($details, 'average_speed'),
                'total_elevation_gain' => data_get($details, 'total_elevation_gain'),
                'raw' => ['details' => $details],
            ]);
        }

        $raw = is_array($activity->raw) ? $activity->raw : [];
        $details = data_get($raw, 'details');
        if (! is_array($details) || empty($details)) {
            $details = $api->fetchActivityDetails($runner, $activityId);
            if (! $details) {
                return response()->json(['success' => false, 'message' => 'Gagal mengambil detail aktivitas Strava.'], 422);
            }
            $activity->update(['raw' => array_merge($raw, ['details' => $details])]);
        }

        $avgSpeed = data_get($details, 'average_speed', $activity->average_speed);
        $pace = $api->formatPaceFromSpeed($avgSpeed);

        $photos = data_get($details, 'photos', []);
        $media = [];
        if (is_array($photos)) {
            $primary = data_get($photos, 'primary.urls.600') ?? data_get($photos, 'primary.urls.100');
            if ($primary) {
                $media[] = $primary;
            }
            $list = data_get($photos, 'photos', []);
            if (is_array($list)) {
                foreach ($list as $p) {
                    $url = data_get($p, 'urls.600') ?? data_get($p, 'urls.100');
                    if ($url) {
                        $media[] = $url;
                    }
                }
            }
        }
        $media = array_values(array_unique($media));

        $startDate = data_get($details, 'start_date') ?: ($activity->start_date?->toIso8601String());
        $elapsedTime = (int) data_get($details, 'elapsed_time', $activity->elapsed_time_s);
        $movingTime = (int) data_get($details, 'moving_time', $activity->moving_time_s);
        $totalTime = $elapsedTime > 0 ? $elapsedTime : ($activity->elapsed_time_s ?: 0);
        $pauseTime = max(0, ($totalTime ?: 0) - ($movingTime ?: 0));
        $endDate = null;
        if ($startDate && $totalTime) {
            try {
                $endDate = Carbon::parse($startDate)->addSeconds($totalTime)->toIso8601String();
            } catch (\Throwable $e) {
                $endDate = null;
            }
        }

        $splits = data_get($details, 'splits_metric', []);
        $splitsOut = [];
        if (is_array($splits)) {
            foreach ($splits as $s) {
                if (! is_array($s)) {
                    continue;
                }
                $splitSpeed = data_get($s, 'average_speed');
                $splitsOut[] = [
                    'split' => data_get($s, 'split'),
                    'distance_m' => data_get($s, 'distance'),
                    'moving_time_s' => data_get($s, 'moving_time'),
                    'elapsed_time_s' => data_get($s, 'elapsed_time'),
                    'elevation_difference' => data_get($s, 'elevation_difference'),
                    'average_speed' => $splitSpeed,
                    'pace' => $api->formatPaceFromSpeed($splitSpeed),
                ];
            }
        }

        $laps = data_get($details, 'laps', []);
        $lapsOut = [];
        if (is_array($laps)) {
            foreach ($laps as $l) {
                if (! is_array($l)) {
                    continue;
                }
                $lapSpeed = data_get($l, 'average_speed');
                $lapsOut[] = [
                    'name' => data_get($l, 'name'),
                    'distance_m' => data_get($l, 'distance'),
                    'moving_time_s' => data_get($l, 'moving_time'),
                    'elapsed_time_s' => data_get($l, 'elapsed_time'),
                    'average_speed' => $lapSpeed,
                    'pace' => $api->formatPaceFromSpeed($lapSpeed),
                    'average_heartrate' => data_get($l, 'average_heartrate'),
                    'max_heartrate' => data_get($l, 'max_heartrate'),
                    'average_cadence' => data_get($l, 'average_cadence'),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'activity' => [
                'strava_activity_id' => $activity->strava_activity_id,
                'name' => $activity->name,
                'type' => $activity->type,
                'start_date' => $activity->start_date?->toIso8601String(),
                'end_date' => $endDate,
                'distance_m' => $activity->distance_m,
                'moving_time_s' => $activity->moving_time_s,
                'elapsed_time_s' => $activity->elapsed_time_s,
                'total_time_s' => $totalTime ?: null,
                'pause_time_s' => $pauseTime ?: null,
                'average_speed' => $avgSpeed,
                'pace' => $pace,
                'average_heartrate' => data_get($details, 'average_heartrate'),
                'max_heartrate' => data_get($details, 'max_heartrate'),
                'average_cadence' => data_get($details, 'average_cadence'),
                'media' => $media,
                'splits_metric' => $splitsOut,
                'laps' => $lapsOut,
            ],
        ]);
    }

    public function stravaActivityStreams(Request $request, $enrollmentId, string $stravaActivityId)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        if (! is_numeric($stravaActivityId) || (string) $stravaActivityId === '0') {
            return response()->json(['success' => false, 'message' => 'Invalid activity id.'], 422);
        }
        $activityId = (string) $stravaActivityId;

        $runner = $enrollment->runner;
        $api = app(StravaApiService::class);

        $activity = StravaActivity::query()
            ->where('user_id', $runner->id)
            ->where('strava_activity_id', $activityId)
            ->first();

        if (! $activity) {
            $details = $api->fetchActivityDetails($runner, $activityId);
            if (! $details) {
                return response()->json(['success' => false, 'message' => 'Gagal mengambil aktivitas Strava.'], 422);
            }

            $activity = StravaActivity::create([
                'user_id' => $runner->id,
                'strava_activity_id' => $activityId,
                'name' => data_get($details, 'name'),
                'type' => data_get($details, 'type'),
                'start_date' => data_get($details, 'start_date_local') ?: data_get($details, 'start_date'),
                'distance_m' => (int) round((float) data_get($details, 'distance', 0)),
                'moving_time_s' => (int) data_get($details, 'moving_time', 0),
                'elapsed_time_s' => (int) data_get($details, 'elapsed_time', 0),
                'average_speed' => data_get($details, 'average_speed'),
                'total_elevation_gain' => data_get($details, 'total_elevation_gain'),
                'raw' => ['details' => $details],
            ]);
        }

        $raw = is_array($activity->raw) ? $activity->raw : [];
        $streams = data_get($raw, 'streams');
        if (! is_array($streams) || empty($streams)) {
            $streams = $api->fetchActivityStreams($runner, $activityId);
            if (! $streams) {
                return response()->json(['success' => false, 'message' => 'Gagal mengambil streams aktivitas Strava.'], 422);
            }
            $activity->update(['raw' => array_merge($raw, ['streams' => $streams])]);
        }

        $keys = ['time', 'heartrate', 'cadence', 'velocity_smooth', 'watts'];
        $out = [];
        foreach ($keys as $k) {
            $data = data_get($streams, $k.'.data');
            if (is_array($data)) {
                $out[$k] = $data;
            }
        }

        return response()->json([
            'success' => true,
            'streams' => $out,
        ]);
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

    public function updateWeeklyTarget(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'weekly_km_target' => 'nullable|numeric|min:0|max:999.99',
        ]);

        $runner = $enrollment->runner;
        $runner->update($validated);

        // Notify Runner
        \App\Models\Notification::create([
            'user_id' => $runner->id,
            'type' => 'target_updated',
            'title' => 'Weekly Target Updated',
            'message' => 'Coach '.auth()->user()->name.' updated your weekly target to '.($validated['weekly_km_target'] ?? 0).' km',
            'reference_type' => 'user',
            'reference_id' => $runner->id,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Weekly target updated',
            'weekly_km_target' => $runner->weekly_km_target,
        ]);
    }

    public function nudgeStrava(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        // Send In-App Notification
        \App\Models\Notification::create([
            'user_id' => $enrollment->runner_id,
            'type' => 'nudge_strava',
            'title' => 'Hubungkan Strava Anda',
            'message' => 'Coach ' . auth()->user()->name . ' meminta Anda menghubungkan akun Strava Anda agar data lari dapat dipantau otomatis.',
            'reference_type' => 'program_enrollment',
            'reference_id' => $enrollment->id,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi in-app berhasil dikirim ke atlet untuk menyambungkan Strava!'
        ]);
    }

    public function syncStrava(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $runner = $enrollment->runner;
        if (! $runner->strava_access_token || ! $runner->strava_refresh_token) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Strava atlet belum tersambung.',
            ], 422);
        }

        $config = \App\Models\Admin\StravaConfig::first();
        $clientId = $config->client_id ?? env('STRAVA_CLIENT_ID');
        $clientSecret = $config->client_secret ?? env('STRAVA_CLIENT_SECRET');
        if (! $clientId || ! $clientSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Strava belum dikonfigurasi oleh admin.',
            ], 500);
        }

        try {
            $accessToken = $runner->strava_access_token;
            if ($runner->strava_expires_at && Carbon::parse($runner->strava_expires_at)->lte(now()->addMinute())) {
                $refresh = \Illuminate\Support\Facades\Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $runner->strava_refresh_token,
                ]);

                if (! $refresh->successful()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal refresh token Strava atlet.',
                    ], 401);
                }

                $tokenData = $refresh->json();
                $accessToken = data_get($tokenData, 'access_token');

                $runner->update([
                    'strava_access_token' => $accessToken,
                    'strava_refresh_token' => data_get($tokenData, 'refresh_token', $runner->strava_refresh_token),
                    'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
                ]);
            }

            $after = StravaActivity::where('user_id', $runner->id)->max('start_date');
            $afterEpoch = $after ? Carbon::parse($after)->subHours(6)->timestamp : now()->subDays(45)->timestamp;

            $all = [];
            for ($page = 1; $page <= 5; $page++) {
                $res = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withToken($accessToken)
                    ->get('https://www.strava.com/api/v3/athlete/activities', [
                        'after' => $afterEpoch,
                        'per_page' => 50,
                        'page' => $page,
                    ]);

                if (! $res->successful()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengambil aktivitas Strava.',
                    ], 502);
                }

                $items = $res->json();
                if (! is_array($items) || empty($items)) {
                    break;
                }

                $all = array_merge($all, $items);
                if (count($items) < 50) {
                    break;
                }
            }

            $uniqueById = [];
            foreach ($all as $row) {
                $id = data_get($row, 'id');
                if (is_numeric($id) && (string) $id !== '0') {
                    $uniqueById[(string) $id] = $row;
                }
            }
            $all = array_values($uniqueById);

            $imported = 0;
            $linked = 0;
            $rangeStart = null;
            $rangeEnd = null;
            $warnings = [];

            \Illuminate\Support\Facades\DB::transaction(function () use ($runner, $all, &$imported, &$linked, &$rangeStart, &$rangeEnd, &$warnings) {
                foreach ($all as $a) {
                    $activityId = data_get($a, 'id');
                    if (! is_numeric($activityId) || (string) $activityId === '0') {
                        continue;
                    }
                    $activityId = (string) $activityId;

                    $startDate = data_get($a, 'start_date_local') ?: data_get($a, 'start_date');
                    $start = null;
                    if ($startDate) {
                        try {
                            $start = Carbon::parse($startDate)->setTimezone(config('app.timezone'));
                        } catch (\Throwable $e) {
                            $warnings[] = 'Aktivitas '.$activityId.' punya start_date tidak valid, dilewati.';
                            $start = null;
                        }
                    }
                    if ($start) {
                        $rangeStart = $rangeStart ? min($rangeStart, $start) : $start;
                        $rangeEnd = $rangeEnd ? max($rangeEnd, $start) : $start;
                    }

                    $payload = [
                        'user_id' => $runner->id,
                        'strava_activity_id' => $activityId,
                        'name' => data_get($a, 'name'),
                        'type' => data_get($a, 'type'),
                        'start_date' => $start,
                        'distance_m' => (int) round((float) data_get($a, 'distance', 0)),
                        'moving_time_s' => (int) data_get($a, 'moving_time', 0),
                        'elapsed_time_s' => (int) data_get($a, 'elapsed_time', 0),
                        'average_speed' => data_get($a, 'average_speed'),
                        'total_elevation_gain' => data_get($a, 'total_elevation_gain'),
                        'raw' => $a,
                    ];

                    try {
                        $row = StravaActivity::query()->where('strava_activity_id', $activityId)->first();
                        if ($row) {
                            $row->update($payload);
                        } else {
                            StravaActivity::create($payload);
                            $imported++;
                        }
                    } catch (\Illuminate\Database\QueryException $e) {
                        $dup = (int) ($e->errorInfo[1] ?? 0) === 1062;
                        if (! $dup) {
                            throw $e;
                        }
                        $row = StravaActivity::query()->where('strava_activity_id', $activityId)->first();
                        if ($row) {
                            $row->update($payload);
                        }
                    }
                }

                if (! $rangeStart || ! $rangeEnd) {
                    return;
                }

                $rangeStartDate = Carbon::parse($rangeStart)->startOfDay();
                $rangeEndDate = Carbon::parse($rangeEnd)->endOfDay();

                $activitiesByDate = StravaActivity::query()
                    ->where('user_id', $runner->id)
                    ->whereBetween('start_date', [$rangeStartDate, $rangeEndDate])
                    ->get()
                    ->filter(function ($act) {
                        $t = strtolower((string) $act->type);

                        return in_array($t, ['run', 'virtualrun', 'trailrun', 'treadmill']);
                    })
                    ->groupBy(fn ($act) => $act->local_start_date?->format('Y-m-d'))
                    ->map(function ($group) {
                        return $group->sortByDesc('distance_m')->first();
                    });

                if ($activitiesByDate->isEmpty()) {
                    return;
                }

                $enrollments = ProgramEnrollment::where('runner_id', $runner->id)
                    ->where('status', 'active')
                    ->with('program')
                    ->get();

                foreach ($enrollments as $enrollment) {
                    $program = $enrollment->program;
                    if (! $program || ! $enrollment->start_date) {
                        continue;
                    }

                    $sessions = data_get($program->program_json, 'sessions', []);
                    if (! is_array($sessions) || empty($sessions)) {
                        continue;
                    }

                    $trackings = ProgramSessionTracking::query()
                        ->where('enrollment_id', $enrollment->id)
                        ->get()
                        ->keyBy('session_day');

                    try {
                        $startBase = Carbon::parse($enrollment->start_date);
                    } catch (\Throwable $e) {
                        continue;
                    }
                    $seenDays = [];
                    foreach ($sessions as $session) {
                        $day = (int) data_get($session, 'day', 0);
                        if ($day <= 0) {
                            continue;
                        }
                        if (isset($seenDays[$day])) {
                            continue;
                        }
                        $seenDays[$day] = true;

                        $date = $startBase->copy()->addDays($day - 1);
                        $tracking = $trackings->get($day);
                        if ($tracking && $tracking->rescheduled_date) {
                            try {
                                $date = Carbon::parse($tracking->rescheduled_date);
                            } catch (\Throwable $e) {
                            }
                        }

                        $key = $date->format('Y-m-d');
                        $act = $activitiesByDate->get($key);
                        if (! $act) {
                            continue;
                        }

                        if (! $tracking) {
                            try {
                                $tracking = ProgramSessionTracking::firstOrCreate([
                                    'enrollment_id' => $enrollment->id,
                                    'session_day' => $day,
                                ], [
                                    'status' => 'pending',
                                ]);
                            } catch (\Illuminate\Database\QueryException $e) {
                                $dup = (int) ($e->errorInfo[1] ?? 0) === 1062;
                                if (! $dup) {
                                    throw $e;
                                }
                                $tracking = ProgramSessionTracking::query()
                                    ->where('enrollment_id', $enrollment->id)
                                    ->where('session_day', $day)
                                    ->first();
                            }
                            if ($tracking) {
                                $trackings->put($day, $tracking);
                            }
                        }

                        if (! $tracking) {
                            continue;
                        }
                        if ($tracking->strava_link) {
                            continue;
                        }

                        $newStatus = in_array($tracking->status, ['pending', 'started', null], true) ? 'completed' : $tracking->status;
                        $tracking->update([
                            'strava_link' => $act->strava_url,
                            'notes' => $tracking->notes ?: 'Auto-linked dari Strava sync',
                            'status' => $newStatus,
                            'completed_at' => $tracking->completed_at ?: ($act->local_start_date ?: $act->start_date),
                        ]);

                        $linked++;
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas Strava berhasil disinkronkan!',
                'imported' => $imported,
                'linked_sessions' => $linked,
                'warnings' => $warnings,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateWeeklyReport(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $runner = $enrollment->runner;
        $hasStrava = !empty($runner->strava_access_token);

        // Get the past 7 days of training events
        $now = Carbon::now();
        $startOfWeek = $now->copy()->subDays(7)->startOfDay();
        $endOfWeek = $now->copy()->endOfDay();

        // 1. Scheduled program workouts
        $program = $enrollment->program;
        $startDate = $enrollment->start_date;
        $sessions = $program->program_json['sessions'] ?? [];
        $trackings = ProgramSessionTracking::where('enrollment_id', $enrollment->id)->get()->keyBy('session_day');

        $completedWorkoutsText = [];
        $totalDistanceCompleted = 0;
        
        foreach ($sessions as $session) {
            $day = (int) ($session['day'] ?? 0);
            if ($day <= 0) continue;
            
            $sessionDate = $startDate->copy()->addDays($day - 1);
            $tracking = $trackings->get($day);
            if ($tracking && $tracking->rescheduled_date) {
                $sessionDate = Carbon::parse($tracking->rescheduled_date);
            }

            if ($sessionDate->between($startOfWeek, $endOfWeek)) {
                $type = $session['type'] ?? 'Run';
                $plannedDist = $session['distance'] ?? null;
                $status = $tracking ? $tracking->status : 'pending';
                
                $detailStr = "- Rencana: " . ucwords(str_replace('_', ' ', $type));
                if ($plannedDist) $detailStr .= " ({$plannedDist} km)";
                
                if ($status === 'completed') {
                    $rpe = $tracking->rpe ?? '-';
                    $feeling = $tracking->feeling ?? '-';
                    $notes = $tracking->notes ?? 'Tidak ada catatan';
                    $detailStr .= " | Status: SELESAI, RPE: {$rpe}, Feeling: {$feeling}, Catatan: '{$notes}'";
                    
                    $completedWorkoutsText[] = $detailStr;
                    $totalDistanceCompleted += (float) $plannedDist;
                } elseif ($status === 'missed') {
                    $completedWorkoutsText[] = $detailStr . " | Status: LEWAT (MISSED)";
                } else {
                    $completedWorkoutsText[] = $detailStr . " | Status: PENDING / BELUM SELESAI";
                }
            }
        }

        // 2. Custom Workouts
        $customWorkouts = \App\Models\CustomWorkout::where('runner_id', $runner->id)
            ->whereBetween('workout_date', [$startOfWeek, $endOfWeek])
            ->get();
            
        foreach ($customWorkouts as $cw) {
            $type = $cw->type;
            $dist = $cw->distance ?? 0;
            $status = $cw->status ?? 'pending';
            $detailStr = "- Kustom: " . ucwords(str_replace('_', ' ', $type)) . " ({$dist} km)";
            if ($status === 'completed') {
                $detailStr .= " | Status: SELESAI";
                $totalDistanceCompleted += (float) $dist;
                $completedWorkoutsText[] = $detailStr;
            } else {
                $detailStr .= " | Status: " . strtoupper($status);
                $completedWorkoutsText[] = $detailStr;
            }
        }

        // 3. Strava activities
        $stravaDetailsText = [];
        if ($hasStrava) {
            $stravaActivities = StravaActivity::where('user_id', $runner->id)
                ->whereBetween('start_date', [$startOfWeek, $endOfWeek])
                ->get();
                
            foreach ($stravaActivities as $act) {
                $distKm = $act->distance_m ? round($act->distance_m / 1000, 2) : 0;
                $durationMin = $act->moving_time_s ? round($act->moving_time_s / 60, 1) : 0;
                $pace = ($distKm > 0 && $act->moving_time_s > 0) ? ($act->moving_time_s / $distKm) : null;
                $paceStr = $pace ? gmdate('i:s', (int) $pace) : '-';
                
                $stravaDetailsText[] = "- Strava Run: {$act->name} | {$distKm} km dalam {$durationMin} menit (Avg Pace: {$paceStr}/km)";
            }
        }

        // Build OpenAI Prompt
        $prompt = "Anda adalah Coach AI Ruang Lari.\n";
        $prompt .= "Buat draf laporan mingguan (Weekly Report Card) untuk atlet bernama {$runner->name}.\n";
        $prompt .= "Profil Atlet:\n";
        $prompt .= "- VDOT saat ini: " . ($runner->vdot ?? 'Belum ada') . "\n";
        $prompt .= "- Target Jarak Mingguan: " . ($runner->weekly_km_target ?? 'Belum ada') . " km\n\n";
        
        $prompt .= "Data Latihan (7 Hari Terakhir):\n";
        $prompt .= "Aktivitas Terjadwal & Log Manual:\n";
        if (empty($completedWorkoutsText)) {
            $prompt .= "- Tidak ada aktivitas terjadwal minggu ini.\n";
        } else {
            $prompt .= implode("\n", $completedWorkoutsText) . "\n";
        }
        
        if ($hasStrava) {
            $prompt .= "\nAktivitas Sinkronisasi Strava:\n";
            if (empty($stravaDetailsText)) {
                $prompt .= "- Tidak ada aktivitas Strava terdeteksi minggu ini.\n";
            } else {
                $prompt .= implode("\n", $stravaDetailsText) . "\n";
            }
        } else {
            $prompt .= "\n(Atlet belum menghubungkan Strava, analisis sepenuhnya berdasarkan log manual & RPE/perasaan diatas)\n";
        }
        
        $prompt .= "\nBerikan analisis yang konstruktif dan memotivasi menggunakan bahasa Indonesia yang santai, bersahabat namun profesional (gaya 'Coach Gaul'). Jangan terlalu kaku.\n";
        $prompt .= "Struktur laporan wajib memiliki:\n";
        $prompt .= "1. **Ringkasan Performa**: Evaluasi volume latihan (jarak/frekuensi) dibanding target.\n";
        $prompt .= "2. **Analisis Kepatuhan & Intensitas**: Ulas RPE/perasaan atlet atau kesesuaian pace Strava.\n";
        $prompt .= "3. **Rekomendasi Pemulihan & Cedera**: Peringatan jika RPE tinggi berturut-turut atau fatigue tinggi.\n";
        $prompt .= "4. **Tindakan Lanjutan**: Apa yang harus dilakukan minggu depan.\n";

        $aiService = app(\App\Services\OpenAiService::class);
        $draft = $aiService->getAiResponse($prompt, "Anda adalah pelatih lari AI profesional Indonesia yang disebut Coach Gaul.");

        return response()->json([
            'success' => true,
            'draft' => $draft ?: "Gagal menghasilkan draf laporan. Silakan coba beberapa saat lagi."
        ]);
    }

    public function storeWeeklyReport(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'week_number' => 'required|integer|min:1',
            'report_text' => 'required|string',
        ]);

        $report = \App\Models\ProgramWeeklyReport::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'week_number' => $validated['week_number'],
            ],
            [
                'report_text' => $validated['report_text'],
                'status' => 'published',
            ]
        );

        // Notify Runner
        \App\Models\Notification::create([
            'user_id' => $enrollment->runner_id,
            'type' => 'weekly_report',
            'title' => 'Weekly Report Card Baru!',
            'message' => 'Coach ' . auth()->user()->name . ' telah menerbitkan Rapor Mingguan (Minggu ' . $validated['week_number'] . ') Anda.',
            'reference_type' => 'program_weekly_report',
            'reference_id' => $report->id,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan mingguan berhasil disimpan dan diterbitkan untuk atlet.',
            'report' => $report
        ]);
    }
}
