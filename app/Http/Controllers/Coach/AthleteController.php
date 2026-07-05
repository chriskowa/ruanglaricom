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

            // Check if dummy token
            if (str_contains($accessToken, 'dummy') || str_contains($runner->strava_refresh_token, 'dummy')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi Strava atlet tidak valid (token dummy). Silakan minta atlet untuk menghubungkan akun Strava riil dari dashboard mereka.',
                ], 400);
            }

            $needsRefresh = false;
            if ($runner->strava_expires_at) {
                try {
                    $needsRefresh = Carbon::parse($runner->strava_expires_at)->lte(now()->addMinute());
                } catch (\Throwable $e) {
                    $needsRefresh = true;
                }
            } else {
                $needsRefresh = true;
            }

            if ($needsRefresh) {
                $refresh = \Illuminate\Support\Facades\Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $runner->strava_refresh_token,
                ]);

                if ($refresh->successful()) {
                    $tokenData = $refresh->json();
                    $accessToken = data_get($tokenData, 'access_token');

                    $runner->update([
                        'strava_access_token' => $accessToken,
                        'strava_refresh_token' => data_get($tokenData, 'refresh_token', $runner->strava_refresh_token),
                        'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Koneksi Strava atlet kedaluwarsa dan gagal diperbarui. Minta atlet menghubungkan ulang akun Strava mereka.',
                    ], 401);
                }
            }

            $after = StravaActivity::where('user_id', $runner->id)->max('start_date');
            $afterEpoch = $after ? Carbon::parse($after)->subHours(6)->timestamp : now()->subDays(45)->timestamp;

            $all = [];
            $apiFailed = false;
            $apiErrorStatus = null;

            for ($page = 1; $page <= 5; $page++) {
                $res = \Illuminate\Support\Facades\Http::withoutVerifying()
                    ->withToken($accessToken)
                    ->get('https://www.strava.com/api/v3/athlete/activities', [
                        'after' => $afterEpoch,
                        'per_page' => 50,
                        'page' => $page,
                    ]);

                if (! $res->successful()) {
                    $apiFailed = true;
                    $apiErrorStatus = $res->status();
                    break;
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

            // If the initial API call returned 401, try to refresh token (even if we didn't think it was expired)
            if ($apiFailed && $apiErrorStatus === 401) {
                $refresh = \Illuminate\Support\Facades\Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $runner->strava_refresh_token,
                ]);

                if ($refresh->successful()) {
                    $tokenData = $refresh->json();
                    $accessToken = data_get($tokenData, 'access_token');

                    $runner->update([
                        'strava_access_token' => $accessToken,
                        'strava_refresh_token' => data_get($tokenData, 'refresh_token', $runner->strava_refresh_token),
                        'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
                    ]);

                    // Retry API call
                    $all = [];
                    $apiFailed = false;
                    for ($page = 1; $page <= 5; $page++) {
                        $res = \Illuminate\Support\Facades\Http::withoutVerifying()
                            ->withToken($accessToken)
                            ->get('https://www.strava.com/api/v3/athlete/activities', [
                                'after' => $afterEpoch,
                                'per_page' => 50,
                                'page' => $page,
                            ]);

                        if (! $res->successful()) {
                            $apiFailed = true;
                            break;
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
                }
            }

            if ($apiFailed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil aktivitas Strava. Silakan minta atlet untuk menghubungkan kembali akun Strava mereka.',
                ], 502);
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

    /**
     * Enroll runner manually
     */
    public function enrollRunner(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'start_date' => 'required|date',
            'vdot' => 'nullable|numeric|min:10|max:85',
            'vdot_mode' => 'nullable|string|in:direct,pb,balke',
            'pb_distance' => 'nullable|string|in:5k,10k,21k,42k',
            'pb_time' => 'nullable|string|regex:/^([0-9]{1,2}:)?[0-9]{1,2}:[0-9]{2}$/',
            'pb_balke' => 'nullable|numeric|min:100|max:10000',
        ]);

        $program = \App\Models\Program::findOrFail($validated['program_id']);
        if ((int)$program->coach_id !== (int)auth()->id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        // Calculate VDOT from input mode
        $computedVdot = null;
        $daniels = app(\App\Services\DanielsRunningService::class);

        if (($validated['vdot_mode'] ?? 'direct') === 'direct') {
            if (!empty($validated['vdot'])) {
                $computedVdot = (float)$validated['vdot'];
            }
        } elseif ($validated['vdot_mode'] === 'pb' && !empty($validated['pb_distance']) && !empty($validated['pb_time'])) {
            try {
                $computedVdot = $daniels->calculateVDOT($validated['pb_time'], $validated['pb_distance']);
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal menghitung VDOT dari Personal Best. Format waktu salah (gunakan MM:SS atau HH:MM:SS).');
            }
        } elseif ($validated['vdot_mode'] === 'balke' && !empty($validated['pb_balke'])) {
            try {
                $computedVdot = (($validated['pb_balke'] / 15) - 133) * 0.172 + 33.3;
                $computedVdot = max(10, min(85, round($computedVdot, 4)));
            } catch (\Exception $e) {
                // Ignore
            }
        }

        // Find or create runner
        $runner = \App\Models\User::where('email', $validated['email'])->first();

        if (!$runner) {
            // Create brand new runner
            $runner = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => 'runner',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'is_active' => true,
            ]);
            
            // Create a wallet for the new runner
            $wallet = \App\Models\Wallet::create([
                'user_id' => $runner->id,
                'balance' => 0.00,
            ]);
            $runner->update(['wallet_id' => $wallet->id]);
        } else {
            // Update phone if provided and not yet set
            if (!empty($validated['phone']) && empty($runner->phone)) {
                $runner->update(['phone' => $validated['phone']]);
            }
        }

        // Update PB & VDOT fields on the User model
        if ($computedVdot) {
            $times = $daniels->calculateEquivalentRaceTimes($computedVdot);
            $runnerUpdates = [];
            if (isset($times['5k']['time'])) {
                $runnerUpdates['pb_5k'] = $times['5k']['time'];
            }
            if (($validated['vdot_mode'] ?? 'direct') === 'pb' && !empty($validated['pb_distance']) && !empty($validated['pb_time'])) {
                $dist = $validated['pb_distance'];
                if ($dist === '5k') $runnerUpdates['pb_5k'] = $validated['pb_time'];
                elseif ($dist === '10k') $runnerUpdates['pb_10k'] = $validated['pb_time'];
                elseif ($dist === '21k') $runnerUpdates['pb_hm'] = $validated['pb_time'];
                elseif ($dist === '42k') $runnerUpdates['pb_fm'] = $validated['pb_time'];
            } elseif (($validated['vdot_mode'] ?? 'direct') === 'balke' && !empty($validated['pb_balke'])) {
                $runnerUpdates['pb_balke'] = $validated['pb_balke'];
            }
            $runner->update($runnerUpdates);
        }

        // Check if already enrolled in this program
        $exists = ProgramEnrollment::where('program_id', $program->id)
            ->where('runner_id', $runner->id)
            ->exists();

        if ($exists) {
            return back()->with('error', "Runner dengan email {$validated['email']} sudah terdaftar dalam program ini.");
        }

        // Enroll
        $durationWeeks = $program->duration_weeks ?? 12;
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addWeeks($durationWeeks);

        ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $runner->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'payment_status' => 'paid', // manually enrolled by coach
            'current_vdot' => $computedVdot ?? $runner->vdot,
        ]);

        return back()->with('success', "Runner {$runner->name} berhasil didaftarkan ke program {$program->title}.");
    }

    /**
     * Import runners and enroll them from CSV or JSON
     */
    public function importEnroll(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'file' => 'required|file|max:2048', // max 2MB
        ]);

        $program = \App\Models\Program::findOrFail($validated['program_id']);
        if ((int)$program->coach_id !== (int)auth()->id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        $runnersData = [];

        if ($extension === 'csv') {
            $path = $file->getRealPath();
            if (($handle = fopen($path, 'r')) !== false) {
                // Read header
                $header = fgetcsv($handle, 1000, ',');
                if ($header) {
                    $header = array_map(fn($h) => strtolower(trim($h)), $header);
                    
                    // Column mapping
                    $nameIdx = array_search('name', $header);
                    $emailIdx = array_search('email', $header);
                    $phoneIdx = array_search('phone', $header);
                    $vdotIdx = array_search('vdot', $header);
                    $pbDistanceIdx = array_search('pb_distance', $header);
                    $pbTimeIdx = array_search('pb_time', $header);
                    $pbBalkeIdx = array_search('pb_balke', $header);
                    $startDateIdx = array_search('start_date', $header);

                    if ($nameIdx === false || $emailIdx === false) {
                        fclose($handle);
                        return back()->with('error', 'CSV template tidak valid. Kolom "name" dan "email" wajib ada.');
                    }

                    while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                        if (count($row) <= max($nameIdx, $emailIdx)) continue;
                        
                        $runnersData[] = [
                            'name' => trim($row[$nameIdx] ?? ''),
                            'email' => trim($row[$emailIdx] ?? ''),
                            'phone' => $phoneIdx !== false && isset($row[$phoneIdx]) ? trim($row[$phoneIdx]) : null,
                            'vdot' => $vdotIdx !== false && isset($row[$vdotIdx]) && trim($row[$vdotIdx]) !== '' ? floatval($row[$vdotIdx]) : null,
                            'pb_distance' => $pbDistanceIdx !== false && isset($row[$pbDistanceIdx]) ? trim($row[$pbDistanceIdx]) : null,
                            'pb_time' => $pbTimeIdx !== false && isset($row[$pbTimeIdx]) ? trim($row[$pbTimeIdx]) : null,
                            'pb_balke' => $pbBalkeIdx !== false && isset($row[$pbBalkeIdx]) && trim($row[$pbBalkeIdx]) !== '' ? floatval($row[$pbBalkeIdx]) : null,
                            'start_date' => $startDateIdx !== false && isset($row[$startDateIdx]) && trim($row[$startDateIdx]) !== '' ? trim($row[$startDateIdx]) : now()->format('Y-m-d'),
                        ];
                    }
                }
                fclose($handle);
            }
        } elseif ($extension === 'json') {
            $content = file_get_contents($file->getRealPath());
            $json = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Format JSON tidak valid.');
            }

            // Standardize array structure
            $items = isset($json['runners']) ? $json['runners'] : $json;
            if (!is_array($items)) {
                return back()->with('error', 'Struktur JSON tidak valid. Harus berupa array runners.');
            }

            foreach ($items as $item) {
                $runnersData[] = [
                    'name' => trim($item['name'] ?? ''),
                    'email' => trim($item['email'] ?? ''),
                    'phone' => isset($item['phone']) ? trim($item['phone']) : null,
                    'vdot' => isset($item['vdot']) && trim($item['vdot']) !== '' ? floatval($item['vdot']) : null,
                    'pb_distance' => isset($item['pb_distance']) ? trim($item['pb_distance']) : null,
                    'pb_time' => isset($item['pb_time']) ? trim($item['pb_time']) : null,
                    'pb_balke' => isset($item['pb_balke']) && trim($item['pb_balke']) !== '' ? floatval($item['pb_balke']) : null,
                    'start_date' => isset($item['start_date']) && trim($item['start_date']) !== '' ? trim($item['start_date']) : now()->format('Y-m-d'),
                ];
            }
        } else {
            return back()->with('error', 'Tipe file tidak didukung. Harap upload file CSV atau JSON.');
        }

        if (empty($runnersData)) {
            return back()->with('error', 'Tidak ada data runner yang ditemukan dalam file.');
        }

        $successCount = 0;
        $skippedCount = 0;

        $daniels = app(\App\Services\DanielsRunningService::class);
        $durationWeeks = $program->duration_weeks ?? 12;

        foreach ($runnersData as $data) {
            $email = $data['email'];
            $name = $data['name'];
            if (empty($email) || empty($name)) {
                $skippedCount++;
                continue;
            }

            // Calculate VDOT
            $computedVdot = null;
            if (!empty($data['vdot'])) {
                $computedVdot = (float)$data['vdot'];
            } elseif (!empty($data['pb_distance']) && !empty($data['pb_time'])) {
                try {
                    $computedVdot = $daniels->calculateVDOT($data['pb_time'], $data['pb_distance']);
                } catch (\Exception $e) {}
            } elseif (!empty($data['pb_balke'])) {
                try {
                    $computedVdot = (($data['pb_balke'] / 15) - 133) * 0.172 + 33.3;
                    $computedVdot = max(10, min(85, round($computedVdot, 4)));
                } catch (\Exception $e) {}
            }

            // Find or create runner
            $runner = \App\Models\User::where('email', $email)->first();
            if (!$runner) {
                $runner = \App\Models\User::create([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $data['phone'] ?? null,
                    'role' => 'runner',
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'is_active' => true,
                ]);

                // Create a wallet for the new runner
                $wallet = \App\Models\Wallet::create([
                    'user_id' => $runner->id,
                    'balance' => 0.00,
                ]);
                $runner->update(['wallet_id' => $wallet->id]);
            } else {
                if (!empty($data['phone']) && empty($runner->phone)) {
                    $runner->update(['phone' => $data['phone']]);
                }
            }

            // Update PB & VDOT fields on the User model
            if ($computedVdot) {
                $times = $daniels->calculateEquivalentRaceTimes($computedVdot);
                $runnerUpdates = [];
                if (isset($times['5k']['time'])) {
                    $runnerUpdates['pb_5k'] = $times['5k']['time'];
                }
                if (!empty($data['pb_distance']) && !empty($data['pb_time'])) {
                    $dist = $data['pb_distance'];
                    if ($dist === '5k') $runnerUpdates['pb_5k'] = $data['pb_time'];
                    elseif ($dist === '10k') $runnerUpdates['pb_10k'] = $data['pb_time'];
                    elseif ($dist === '21k') $runnerUpdates['pb_hm'] = $data['pb_time'];
                    elseif ($dist === '42k') $runnerUpdates['pb_fm'] = $data['pb_time'];
                } elseif (!empty($data['pb_balke'])) {
                    $runnerUpdates['pb_balke'] = $data['pb_balke'];
                }
                $runner->update($runnerUpdates);
            }

            // Check if already enrolled
            $exists = ProgramEnrollment::where('program_id', $program->id)
                ->where('runner_id', $runner->id)
                ->exists();

            if ($exists) {
                $skippedCount++;
                continue;
            }

            // Enroll
            $startDate = Carbon::parse($data['start_date'] ?? now());
            $endDate = $startDate->copy()->addWeeks($durationWeeks);

            ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => $runner->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => 'active',
                'payment_status' => 'paid',
                'current_vdot' => $computedVdot ?? $runner->vdot,
            ]);

            $successCount++;
        }

        return back()->with('success', "Berhasil mengimpor runner: {$successCount} sukses, {$skippedCount} dilewati (data tidak valid atau sudah terdaftar).");
    }

    /**
     * Download import CSV template
     */
    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="runner_import_template.csv"',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['name', 'email', 'phone', 'vdot', 'pb_distance', 'pb_time', 'pb_balke', 'start_date']);
            fputcsv($file, ['John Doe', 'johndoe@example.com', '081234567890', '45.5', '', '', '', '2026-07-01']);
            fputcsv($file, ['Jane Smith', 'janesmith@example.com', '08987654321', '', '5k', '00:22:30', '', '2026-07-15']);
            fputcsv($file, ['Budi Santoso', 'budi@example.com', '087711223344', '', '', '', '3100', '2026-07-20']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Reschedule a workout (Drag & Drop) for coach
     */
    public function reschedule(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:program_session,custom_workout',
            'new_date' => 'required|date',
            // For custom workout
            'workout_id' => 'nullable|required_if:type,custom_workout|exists:custom_workouts,id',
            // For program session
            'session_day' => 'nullable|required_if:type,program_session|integer',
        ]);

        $newDate = Carbon::parse($validated['new_date']);
        $runnerId = $enrollment->runner_id;

        if ($validated['type'] === 'custom_workout') {
            $workout = \App\Models\CustomWorkout::where('id', $validated['workout_id'])
                ->where('runner_id', $runnerId)
                ->firstOrFail();

            try {
                $workout->update(['workout_date' => $newDate]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Swapping if duplicate date constraint
                if ($e->errorInfo[1] == 1062) {
                    $existingWorkout = \App\Models\CustomWorkout::where('runner_id', $runnerId)
                        ->where('workout_date', $newDate->format('Y-m-d'))
                        ->first();

                    if ($existingWorkout) {
                        \DB::transaction(function () use ($workout, $existingWorkout, $newDate) {
                            $originalDate = $workout->workout_date;
                            $tempDate = Carbon::parse('1970-01-01');
                            while (\App\Models\CustomWorkout::where('runner_id', $workout->runner_id)->where('workout_date', $tempDate->format('Y-m-d'))->exists()) {
                                $tempDate->subDay();
                            }

                            $existingWorkout->update(['workout_date' => $tempDate]);
                            $workout->update(['workout_date' => $newDate]);
                            $existingWorkout->update(['workout_date' => $originalDate]);
                        });

                        // Notify Runner
                        \App\Models\Notification::create([
                            'user_id' => $runnerId,
                            'type' => 'workout_rescheduled',
                            'title' => 'Workout Rescheduled',
                            'message' => 'Coach ' . auth()->user()->name . ' swapped your workouts for ' . $newDate->format('d M Y'),
                            'reference_type' => 'custom_workout',
                            'reference_id' => $workout->id,
                            'is_read' => false,
                        ]);

                        return response()->json(['success' => true, 'message' => 'Jadwal latihan ditukar karena tanggal tujuan sudah terisi.']);
                    }
                }
                throw $e;
            }

            // Notify Runner
            \App\Models\Notification::create([
                'user_id' => $runnerId,
                'type' => 'workout_rescheduled',
                'title' => 'Workout Rescheduled',
                'message' => 'Coach ' . auth()->user()->name . ' rescheduled your workout to ' . $newDate->format('d M Y'),
                'reference_type' => 'custom_workout',
                'reference_id' => $workout->id,
                'is_read' => false,
            ]);

            return response()->json(['success' => true, 'message' => 'Custom workout rescheduled']);
        } else {
            ProgramSessionTracking::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'session_day' => $validated['session_day'],
                ],
                [
                    'rescheduled_date' => $newDate,
                ]
            );

            // Notify Runner
            \App\Models\Notification::create([
                'user_id' => $runnerId,
                'type' => 'workout_rescheduled',
                'title' => 'Workout Rescheduled',
                'message' => 'Coach ' . auth()->user()->name . ' rescheduled your program session to ' . $newDate->format('d M Y'),
                'reference_type' => 'program_enrollment',
                'reference_id' => $enrollment->id,
                'is_read' => false,
            ]);

            return response()->json(['success' => true, 'message' => 'Program session rescheduled']);
        }
    }

    /**
     * Athlete Strava Activity AI Analysis
     */
    public function stravaActivityAiAnalysis(Request $request, $enrollmentId, $stravaActivityId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        if (! is_numeric($stravaActivityId) || (string) $stravaActivityId === '0') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activity id.',
            ], 422);
        }

        $runner = $enrollment->runner;
        $activity = StravaActivity::query()
            ->where('user_id', $runner->id)
            ->where('strava_activity_id', $stravaActivityId)
            ->first();

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity tidak ditemukan.',
            ], 404);
        }

        try {
            $api = app(StravaApiService::class);
            $raw = is_array($activity->raw) ? $activity->raw : [];

            $details = data_get($raw, 'details');
            if (! is_array($details) || empty($details)) {
                $details = $api->fetchActivityDetails($runner, $stravaActivityId);
                if (! $details) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengambil detail aktivitas Strava untuk AI.',
                    ], 422);
                }
                $raw['details'] = $details;
            }

            $streams = data_get($raw, 'streams');
            if (! is_array($streams) || empty($streams)) {
                $streams = $api->fetchActivityStreams($runner, $stravaActivityId);
                if (is_array($streams) && ! empty($streams)) {
                    $raw['streams'] = $streams;
                } else {
                    $streams = [];
                }
            }

            $activity->update(['raw' => $raw]);

            $profile = app(\App\Services\RunningProfileService::class)->getProfile($runner);
            
            $stravaCtrl = new \App\Http\Controllers\Runner\StravaController();
            $context = $stravaCtrl->buildRecentTrainingContext($runner->id, $activity, $profile);
            $metrics = $stravaCtrl->buildAiWorkoutPayload($activity, $details, $streams, $profile, $context, $api);
            $inputHash = md5(json_encode($metrics));

            $cachedHash = data_get($raw, 'ai_analysis.input_hash');
            $cachedResult = data_get($raw, 'ai_analysis.result');
            $force = $request->boolean('force');

            if (! $force && $cachedHash === $inputHash && is_array($cachedResult)) {
                return response()->json([
                    'success' => true,
                    'analysis' => $cachedResult,
                    'cached' => true,
                ]);
            }

            $systemPrompt = "Anda adalah AI Running Coach Ruang Lari. "
                ."Analisis workout lari berdasarkan data Strava dan konteks latihan mingguan. "
                ."Jawab hanya dalam Bahasa Indonesia yang ringkas, spesifik, dan actionable. "
                ."Jangan mengarang metrik yang tidak ada. Jika data kurang, katakan secara eksplisit. "
                ."Return HARUS JSON valid tanpa markdown dan tanpa teks lain.";

            $userPrompt = "Analisis workout berikut dan berikan insight pelatihan.\n"
                ."Wajib identifikasi jenis sesi berdasarkan variasi pace (split/stream) dan konteks pace latihan runner.\n"
                ."Jika konteks menyebut 'junk_miles_risk.level' = medium/high, tambahkan 1 item ke risk_flags dengan format: \"Junk miles risk: <level> - <alasan singkat>\".\n"
                ."Summary WAJIB diawali dengan 'Jenis sesi: <type>.'\n"
                ."Format output JSON:\n"
                ."{\n"
                ."  \"workout_classification\": {\n"
                ."    \"type\": \"easy|interval|tempo|threshold|mixed|unknown\",\n"
                ."    \"evidence\": [\"...\"]\n"
                ."  },\n"
                ."  \"summary\": \"...\",\n"
                ."  \"what_went_well\": [\"...\"],\n"
                ."  \"what_to_improve\": [\"...\"],\n"
                ."  \"risk_flags\": [\"...\"],\n"
                ."  \"next_workout_suggestion\": {\n"
                ."    \"type\": \"easy_run|recovery|tempo|interval|long_run|rest|cross_training\",\n"
                ."    \"reason\": \"...\",\n"
                ."    \"duration\": \"...\",\n"
                ."    \"target\": \"...\"\n"
                ."  },\n"
                ."  \"recovery_advice\": [\"...\"],\n"
                ."  \"improve_next_time\": [\"...\"],\n"
                ."  \"confidence\": \"low|medium|high\"\n"
                ."}\n\n"
                ."Data workout:\n".json_encode($metrics, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $aiRaw = app(\App\Services\OpenAiService::class)->getAiResponse($userPrompt, $systemPrompt, 'gpt-4o');
            if (! $aiRaw) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI tidak mengembalikan respons.',
                ], 502);
            }

            $jsonStr = trim(str_replace(["```json", "```"], '', $aiRaw));
            if (preg_match('/\{[\s\S]*\}/', $jsonStr, $matches)) {
                $jsonStr = $matches[0];
            }

            $decoded = json_decode($jsonStr, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI mengembalikan format analisis yang tidak valid.',
                    'raw' => $aiRaw,
                ], 500);
            }

            $decoded = $stravaCtrl->normalizeAiAnalysis($decoded);
            $decoded['junk_miles_risk'] = data_get($metrics, 'recent_training_context.junk_miles_risk', [
                'level' => 'unknown',
                'evidence' => [],
            ]);
            $raw['ai_analysis'] = [
                'model' => 'gpt-4o',
                'created_at' => now()->toIso8601String(),
                'input_hash' => $inputHash,
                'result' => $decoded,
            ];
            $activity->update(['raw' => $raw]);

            return response()->json([
                'success' => true,
                'analysis' => $decoded,
                'cached' => false,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menganalisis workout: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an athlete's enrollment
     */
    public function destroy($enrollmentId)
    {
        $enrollment = ProgramEnrollment::with('program')->findOrFail($enrollmentId);

        // Verify this enrollment belongs to a program owned by the coach
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // Delete all tracking records
            ProgramSessionTracking::where('enrollment_id', $enrollmentId)->delete();

            // Delete enrollment permanently
            $enrollment->delete();

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Atlet berhasil dihapus dari program ini.',
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus atlet: ' . $e->getMessage(),
            ], 500);
        }
    }
}
