<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\StravaActivity;
use App\Services\DanielsRunningService;
use App\Services\ProgramBuilderService;
use App\Services\StravaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

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

        $resHistory = is_array($enrollment->reschedule_history) ? $enrollment->reschedule_history : [];
        $deletedSessionDays = $resHistory['deleted_session_days'] ?? [];

        foreach ($sessions as $index => $session) {
            if (! isset($session['day'])) {
                continue;
            }

            $sessionDayInt = (int) $session['day'];
            if (in_array($sessionDayInt, $deletedSessionDays, true)) {
                continue;
            }

            $sessionDate = $startDate->copy()->addDays($sessionDayInt - 1);

            // Get tracking
            $tracking = ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                ->where('session_day', $sessionDayInt)
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
                'title' => $titlePrefix.($session['title'] ?? $session['session_name'] ?? ucfirst(str_replace('_', ' ', $session['type'] ?? 'Run'))),
                'start' => $sessionDate->format('Y-m-d'),
                'backgroundColor' => $backgroundColor,
                'borderColor' => $borderColor,
                'textColor' => '#FFFFFF', // Ensure text is white
                'extendedProps' => [
                    'session_day' => $session['day'],
                    'type' => $session['type'] ?? 'run',
                    'distance' => $session['distance'] ?? null,
                    'duration' => $session['duration'] ?? null,
                    'description' => $session['description'] ?? null,
                    'notes' => $session['notes'] ?? null,
                    'status' => $status,
                    'workout_structure' => $session['workout_structure'] ?? $session['structure_json'] ?? $session['structure'] ?? null,
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
            ->where('strava_activity_id', $activityId)
            ->first();

        if (! $activity) {
            $details = $api->fetchActivityDetails($runner, $activityId);
            if (! $details) {
                return response()->json(['success' => false, 'message' => 'Gagal mengambil detail aktivitas Strava.'], 422);
            }

            $activity = StravaActivity::updateOrCreate([
                'strava_activity_id' => $activityId,
            ], [
                'user_id' => $runner->id,
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
            $raw['details'] = $details;
        }

        // Also fetch streams if not present so we can calculate exact HR & pace zones
        $streams = data_get($raw, 'streams');
        if (! is_array($streams) || empty($streams)) {
            $fetchedStreams = $api->fetchActivityStreams($runner, $activityId, ['distance', 'altitude', 'time', 'heartrate', 'cadence', 'velocity_smooth', 'watts']);
            if (is_array($fetchedStreams) && ! empty($fetchedStreams)) {
                $raw['streams'] = $fetchedStreams;
                $activity->update(['raw' => $raw]);
                $streams = $fetchedStreams;
            }
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
            foreach ($splits as $idx => $s) {
                if (! is_array($s)) {
                    continue;
                }
                $splitSpeed = data_get($s, 'average_speed');
                $splitsOut[] = [
                    'split' => data_get($s, 'split') ?: ($idx + 1),
                    'distance_m' => data_get($s, 'distance'),
                    'distance_km' => round(((float) data_get($s, 'distance', 0)) / 1000, 2),
                    'moving_time_s' => data_get($s, 'moving_time'),
                    'elapsed_time_s' => data_get($s, 'elapsed_time'),
                    'elevation_difference' => data_get($s, 'elevation_difference'),
                    'average_speed' => $splitSpeed,
                    'pace' => $api->formatPaceFromSpeed($splitSpeed),
                    'average_heartrate' => data_get($s, 'average_heartrate'),
                    'average_cadence' => data_get($s, 'average_cadence'),
                ];
            }
        }

        $laps = data_get($details, 'laps', []);
        $lapsOut = [];
        if (is_array($laps)) {
            foreach ($laps as $idx => $l) {
                if (! is_array($l)) {
                    continue;
                }
                $lapSpeed = data_get($l, 'average_speed');
                $lapsOut[] = [
                    'lap_index' => $idx + 1,
                    'name' => data_get($l, 'name') ?: 'Lap ' . ($idx + 1),
                    'distance_m' => data_get($l, 'distance'),
                    'distance_km' => round(((float) data_get($l, 'distance', 0)) / 1000, 2),
                    'moving_time_s' => data_get($l, 'moving_time'),
                    'elapsed_time_s' => data_get($l, 'elapsed_time'),
                    'average_speed' => $lapSpeed,
                    'pace' => $api->formatPaceFromSpeed($lapSpeed),
                    'average_heartrate' => data_get($l, 'average_heartrate'),
                    'max_heartrate' => data_get($l, 'max_heartrate'),
                    'average_cadence' => data_get($l, 'average_cadence'),
                    'elevation_difference' => data_get($l, 'elevation_difference', data_get($l, 'total_elevation_gain', 0)),
                ];
            }
        }

        // Best efforts extraction
        $bestEfforts = data_get($details, 'best_efforts', []);
        $bestEffortsOut = [];
        if (is_array($bestEfforts)) {
            foreach ($bestEfforts as $be) {
                if (! is_array($be)) {
                    continue;
                }
                $beDist = (float) data_get($be, 'distance', 0);
                $beTime = (float) data_get($be, 'moving_time', data_get($be, 'elapsed_time', 0));
                $beSpeed = ($beDist > 0 && $beTime > 0) ? ($beDist / $beTime) : 0;
                $bestEffortsOut[] = [
                    'name' => data_get($be, 'name'),
                    'distance_m' => $beDist,
                    'distance_km' => round($beDist / 1000, 2),
                    'moving_time_s' => (int) $beTime,
                    'elapsed_time_s' => (int) data_get($be, 'elapsed_time', $beTime),
                    'pace' => $api->formatPaceFromSpeed($beSpeed),
                    'pr_rank' => data_get($be, 'pr_rank'),
                ];
            }
        }

        // Calculate Heart Rate Zones
        $age = $runner->date_of_birth ? Carbon::parse($runner->date_of_birth)->age : 30;
        $maxHr = (int) (data_get($details, 'max_heartrate') ?: (220 - $age));
        if ($maxHr < 140) $maxHr = 190;

        $hrZonesDef = [
            ['name' => 'Zone 1 (Recovery)', 'min' => round($maxHr * 0.50), 'max' => round($maxHr * 0.60), 'color' => '#64748b'],
            ['name' => 'Zone 2 (Aerobic / Easy)', 'min' => round($maxHr * 0.60), 'max' => round($maxHr * 0.70), 'color' => '#22c55e'],
            ['name' => 'Zone 3 (Tempo)', 'min' => round($maxHr * 0.70), 'max' => round($maxHr * 0.80), 'color' => '#eab308'],
            ['name' => 'Zone 4 (Threshold)', 'min' => round($maxHr * 0.80), 'max' => round($maxHr * 0.90), 'color' => '#f97316'],
            ['name' => 'Zone 5 (Anaerobic / VO2max)', 'min' => round($maxHr * 0.90), 'max' => $maxHr + 30, 'color' => '#ef4444'],
        ];

        $hrStream = data_get($streams, 'heartrate.data', []);
        $timeStream = data_get($streams, 'time.data', []);
        $hrZonesOut = [];

        if (is_array($hrStream) && count($hrStream) > 0 && is_array($timeStream) && count($timeStream) === count($hrStream)) {
            $totalStreamSeconds = 0;
            $zoneTimes = [0, 0, 0, 0, 0];

            for ($i = 0; $i < count($hrStream); $i++) {
                $hr = $hrStream[$i];
                if (! is_numeric($hr) || $hr <= 0) continue;
                $deltaSec = ($i === 0) ? ($timeStream[$i] ?? 1) : max(1, ($timeStream[$i] - $timeStream[$i - 1]));
                $totalStreamSeconds += $deltaSec;

                if ($hr < $hrZonesDef[1]['min']) {
                    $zoneTimes[0] += $deltaSec;
                } elseif ($hr < $hrZonesDef[2]['min']) {
                    $zoneTimes[1] += $deltaSec;
                } elseif ($hr < $hrZonesDef[3]['min']) {
                    $zoneTimes[2] += $deltaSec;
                } elseif ($hr < $hrZonesDef[4]['min']) {
                    $zoneTimes[3] += $deltaSec;
                } else {
                    $zoneTimes[4] += $deltaSec;
                }
            }

            foreach ($hrZonesDef as $idx => $z) {
                $secs = $zoneTimes[$idx];
                $pct = $totalStreamSeconds > 0 ? round(($secs / $totalStreamSeconds) * 100, 1) : 0;
                $hrZonesOut[] = [
                    'name' => $z['name'],
                    'range' => "{$z['min']} - " . ($idx === 4 ? "{$maxHr}+" : "{$z['max']}") . " bpm",
                    'seconds' => $secs,
                    'duration' => gmdate($secs >= 3600 ? 'H:i:s' : 'i:s', $secs),
                    'percentage' => $pct,
                    'color' => $z['color'],
                ];
            }
        }

        // Runner Training Profile Paces for context
        $trainingProfile = app(\App\Services\RunningProfileService::class)->getProfile($runner);

        return response()->json([
            'success' => true,
            'activity' => [
                'strava_activity_id' => $activity->strava_activity_id,
                'name' => $activity->name,
                'type' => $activity->type,
                'start_date' => $activity->start_date?->toIso8601String(),
                'end_date' => $endDate,
                'distance_m' => $activity->distance_m,
                'distance_km' => $activity->distance_m ? round(((float) $activity->distance_m) / 1000, 2) : 0,
                'moving_time_s' => $activity->moving_time_s,
                'elapsed_time_s' => $activity->elapsed_time_s,
                'total_time_s' => $totalTime ?: null,
                'pause_time_s' => $pauseTime ?: null,
                'average_speed' => $avgSpeed,
                'pace' => $pace,
                'average_heartrate' => data_get($details, 'average_heartrate'),
                'max_heartrate' => data_get($details, 'max_heartrate', $maxHr),
                'average_cadence' => data_get($details, 'average_cadence'),
                'total_elevation_gain' => data_get($details, 'total_elevation_gain', $activity->total_elevation_gain),
                'elev_high' => data_get($details, 'elev_high'),
                'elev_low' => data_get($details, 'elev_low'),
                'calories' => data_get($details, 'calories'),
                'kilojoules' => data_get($details, 'kilojoules'),
                'device_name' => data_get($details, 'device_name'),
                'media' => $media,
                'splits_metric' => $splitsOut,
                'laps' => $lapsOut,
                'best_efforts' => $bestEffortsOut,
                'hr_zones' => $hrZonesOut,
                'training_profile' => $trainingProfile,
                'ai_analysis' => data_get($raw, 'ai_analysis.result'),
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
            $streams = $api->fetchActivityStreams($runner, $activityId, ['distance', 'altitude', 'time', 'heartrate', 'cadence', 'velocity_smooth', 'watts', 'temp']);
            if (! $streams) {
                return response()->json(['success' => false, 'message' => 'Gagal mengambil streams aktivitas Strava.'], 422);
            }
            $activity->update(['raw' => array_merge($raw, ['streams' => $streams])]);
        }

        $keys = ['distance', 'altitude', 'time', 'heartrate', 'cadence', 'velocity_smooth', 'watts', 'temp'];
        $out = [];
        foreach ($keys as $k) {
            $data = data_get($streams, $k.'.data');
            if (is_array($data)) {
                $out[$k] = $data;
            }
        }

        // Generate pace stream in min/km for charting (capped at max 12:00 /km for clean charts)
        if (isset($out['velocity_smooth']) && is_array($out['velocity_smooth'])) {
            $paceSeries = [];
            foreach ($out['velocity_smooth'] as $speed) {
                if (is_numeric($speed) && (float) $speed > 0.5) {
                    $minPerKm = 1000 / (float) $speed / 60;
                    $paceSeries[] = round(min(12.0, max(2.5, $minPerKm)), 2);
                } else {
                    $paceSeries[] = null;
                }
            }
            $out['pace_min_km'] = $paceSeries;
        }

        // Distance in KM
        if (isset($out['distance']) && is_array($out['distance'])) {
            $out['distance_km'] = array_map(fn($m) => round(((float)$m) / 1000, 2), $out['distance']);
        }

        return response()->json([
            'success' => true,
            'streams' => $out,
        ]);
    }

    /**
     * AI Analysis for Coach Athlete Strava Activity
     */
    public function stravaActivityAiAnalysis(Request $request, $enrollmentId, string $stravaActivityId)
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

        $activity = StravaActivity::query()
            ->where('user_id', $runner->id)
            ->where('strava_activity_id', $activityId)
            ->first();

        if (! $activity) {
            return response()->json(['success' => false, 'message' => 'Activity tidak ditemukan.'], 404);
        }

        try {
            $api = app(StravaApiService::class);
            $raw = is_array($activity->raw) ? $activity->raw : [];

            $details = data_get($raw, 'details');
            if (! is_array($details) || empty($details)) {
                $details = $api->fetchActivityDetails($runner, $activityId);
                if (! $details) {
                    return response()->json(['success' => false, 'message' => 'Gagal mengambil detail aktivitas Strava.'], 422);
                }
                $raw['details'] = $details;
            }

            $streams = data_get($raw, 'streams');
            if (! is_array($streams) || empty($streams)) {
                $streams = $api->fetchActivityStreams($runner, $activityId, ['distance', 'altitude', 'time', 'heartrate', 'cadence', 'velocity_smooth', 'watts']);
                if (is_array($streams) && ! empty($streams)) {
                    $raw['streams'] = $streams;
                } else {
                    $streams = [];
                }
            }

            $activity->update(['raw' => $raw]);

            $runnerCtrl = app(\App\Http\Controllers\Runner\StravaController::class);
            $profile = app(\App\Services\RunningProfileService::class)->getProfile($runner);
            $context = $runnerCtrl->buildRecentTrainingContext($runner->id, $activity, $profile);
            $metrics = $runnerCtrl->buildAiWorkoutPayload($activity, $details, $streams, $profile, $context, $api);
            $inputHash = md5(json_encode($metrics));

            $cachedHash = data_get($raw, 'ai_analysis.input_hash');
            $cachedResult = data_get($raw, 'ai_analysis.result');
            $force = $request->boolean('force');

            if (! $force && $cachedHash === $inputHash && is_array($cachedResult)) {
                // Return cached with formatted WA text
                $waText = $this->buildWhatsAppMessage($enrollment, $activity, $cachedResult);
                return response()->json([
                    'success' => true,
                    'analysis' => $cachedResult,
                    'wa_message' => $waText,
                    'cached' => true,
                ]);
            }

            $systemPrompt = "Anda adalah AI Senior Running Coach Ruang Lari yang sangat teliti dan ahli dalam menganalisis telemetri dan lap sesi latihan atlet.\n"
                ."PRINSIP UTAMA KLASIFIKASI & EVALUASI WORKOUT (WAJIB DIPATUHI):\n"
                ."1. DETEKSI INTERVAL / REPETISI PENDEK (200m, 300m, 400m, 600m, 800m, 1000m, 1200m, 1600m):\n"
                ."   - WAJIB periksa data 'laps_summary', 'detected_interval_structure', dan 'telemetry_surge_analysis'.\n"
                ."   - Jika atlet melakukan repetisi interval (misal 10x 200m, 8x 400m, 5x 1km), klasifikasikan sebagai 'interval'.\n"
                ."   - Tuliskan struktur repetisi pada 'summary' secara spesifik (contoh: 'Jenis sesi: Interval (10x 200m Repetisi).')\n"
                ."   - Evaluasi konsistensi pacing per repetisi: apakah merata/konsisten, terlalu kencang di awal (front-loaded), atau mengalami dropoff pace di repetisi akhir.\n"
                ."   - Evaluasi fase recovery: apakah waktu istirahat antar repetisi cukup untuk mengembalikan detak jantung.\n"
                ."2. Lari dengan jarak total >= 14 km (misal 15km, 18km, 24km, 30km) BERSTATUS 'long_run' atau 'long_run_quality'. DILARANG KERAS mengklasifikasikan lari jarak >= 14 km sebagai 'interval'!\n"
                ."3. Adanya variasi pace sesaat akibat water stop atau tanjakan pada lari 24 km TIDAK BOLEH membuat lari tersebut dianggap sebagai interval.\n"
                ."4. Jawab hanya dalam Bahasa Indonesia yang ringkas, berbobot teknis pelatih lari, dan objektif. Return HARUS JSON valid.";

            $userPrompt = "Analisis workout berikut untuk pelatih (coach) dengan memperhatikan detail laps dan telemetri repetisi pendek.\n"
                ."Summary WAJIB diawali dengan 'Jenis sesi: <type>.'\n"
                ."Format output JSON:\n"
                ."{\n"
                ."  \"workout_classification\": {\n"
                ."    \"type\": \"easy_run|long_run|long_run_quality|interval|tempo|threshold|recovery|mixed|unknown\",\n"
                ."    \"evidence\": [\"...\"]\n"
                ."  },\n"
                ."  \"interval_analysis\": {\n"
                ."    \"detected_structure\": \"misal: 10x 200m / 8x 400m / Tidak ada repetisi\",\n"
                ."    \"pacing_consistency\": \"konsisten|dropoff_di_akhir|progresif|tidak_teratur\",\n"
                ."    \"recovery_quality\": \"optimal|terlalu_singkat|terlalu_panjang|tidak_terekam\",\n"
                ."    \"notes\": \"...\"\n"
                ."  },\n"
                ."  \"summary\": \"...\",\n"
                ."  \"what_went_well\": [\"...\"],\n"
                ."  \"what_to_improve\": [\"...\"],\n"
                ."  \"risk_flags\": [\"...\"],\n"
                ."  \"pacing_evaluation\": {\n"
                ."    \"split_consistency\": \"stabil|progresif|melambat_di_akhir|fluktuatif\",\n"
                ."    \"cardiac_drift\": \"rendah|sedang|tinggi|tidak_ada_data_hr\",\n"
                ."    \"notes\": \"...\"\n"
                ."  },\n"
                ."  \"next_workout_suggestion\": {\n"
                ."    \"type\": \"easy_run|recovery|tempo|interval|long_run|rest|cross_training\",\n"
                ."    \"reason\": \"...\",\n"
                ."    \"duration\": \"...\",\n"
                ."    \"target\": \"...\"\n"
                ."  },\n"
                ."  \"recovery_advice\": [\"...\"],\n"
                ."  \"improve_next_time\": [\"...\"],\n"
                ."  \"coach_recommendation\": \"...\",\n"
                ."  \"confidence\": \"low|medium|high\"\n"
                ."}\n\n"
                ."Data workout lengkap (termasuk laps dan stream telemetri):\n".json_encode($metrics, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $aiRaw = app(\App\Services\OpenAiService::class)->getAiResponse($userPrompt, $systemPrompt, 'gpt-4o');
            if (! $aiRaw) {
                return response()->json(['success' => false, 'message' => 'AI tidak mengembalikan respons.'], 502);
            }

            $jsonStr = trim(str_replace(["```json", "```"], '', $aiRaw));
            if (preg_match('/\{[\s\S]*\}/', $jsonStr, $matches)) {
                $jsonStr = $matches[0];
            }

            $decoded = json_decode($jsonStr, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return response()->json(['success' => false, 'message' => 'AI mengembalikan format yang tidak valid.', 'raw' => $aiRaw], 500);
            }

            // Guardrail distance >= 14km
            $distKm = (float) data_get($metrics, 'activity.distance_km', 0);
            if ($distKm >= 14 && in_array(data_get($decoded, 'workout_classification.type'), ['interval', 'easy', 'mixed', 'unknown'], true)) {
                $hasVariation = data_get($metrics, 'activity.split_pace_stats.cv', 0) >= 0.15;
                $newType = $hasVariation ? 'long_run_quality' : 'long_run';
                $decoded['workout_classification']['type'] = $newType;
            }

            $raw['ai_analysis'] = [
                'model' => 'gpt-4o',
                'created_at' => now()->toIso8601String(),
                'input_hash' => $inputHash,
                'result' => $decoded,
            ];
            $activity->update(['raw' => $raw]);

            $waText = $this->buildWhatsAppMessage($enrollment, $activity, $decoded);

            return response()->json([
                'success' => true,
                'analysis' => $decoded,
                'wa_message' => $waText,
                'cached' => false,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Coach Strava AI Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal menganalisis workout: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper to build a clean, professional WhatsApp review message
     */
    private function buildWhatsAppMessage(ProgramEnrollment $enrollment, StravaActivity $activity, array $analysis): string
    {
        $runnerName = $enrollment->runner->name ?? 'Runner';
        $coachName = auth()->user()->name ?? 'Coach';
        $actName = $activity->name ?? 'Sesi Lari';
        $dateStr = $activity->start_date ? Carbon::parse($activity->start_date)->format('d M Y') : date('d M Y');
        $distKm = $activity->distance_m ? number_format($activity->distance_m / 1000, 2) : '-';
        $durStr = $activity->moving_time_s ? gmdate('H:i:s', (int) $activity->moving_time_s) : '-';
        $paceStr = app(StravaApiService::class)->formatPaceFromSpeed($activity->average_speed) ?? '-';

        $summary = data_get($analysis, 'summary', '');
        $classification = data_get($analysis, 'workout_classification.type', 'Lari');
        $intervalAnalysis = data_get($analysis, 'interval_analysis');
        $wentWell = (array) data_get($analysis, 'what_went_well', []);
        $toImprove = (array) data_get($analysis, 'what_to_improve', []);
        $nextSuggestion = data_get($analysis, 'next_workout_suggestion.type', '');
        $nextReason = data_get($analysis, 'next_workout_suggestion.reason', '');
        $coachRec = data_get($analysis, 'coach_recommendation', '');

        $msg = "*EVALUASI SESI LATIHAN — {$coachName}*\n";
        $msg .= "Halo {$runnerName},\nBerikut hasil review dan analisis sesi latihan Anda:\n\n";
        $msg .= "Aktivitas: {$actName}\n";
        $msg .= "Tanggal: {$dateStr}\n";
        $msg .= "Jarak: {$distKm} km | Waktu: {$durStr} | Pace Rata-rata: {$paceStr} /km\n";
        if ($activity->average_heartrate) {
            $msg .= "Heart Rate Rata-rata: " . round($activity->average_heartrate) . " bpm\n";
        }
        $msg .= "\n--- RINGKASAN ANALISIS ---\n";
        $msg .= "{$summary}\n\n";

        if (is_array($intervalAnalysis) && !empty($intervalAnalysis['detected_structure']) && !str_contains(strtolower($intervalAnalysis['detected_structure']), 'tidak ada')) {
            $msg .= "*Analisis Repetisi & Interval:*\n";
            $msg .= "- Struktur: {$intervalAnalysis['detected_structure']}\n";
            if (!empty($intervalAnalysis['pacing_consistency'])) {
                $msg .= "- Konsistensi Pace: " . ucfirst(str_replace('_', ' ', $intervalAnalysis['pacing_consistency'])) . "\n";
            }
            if (!empty($intervalAnalysis['recovery_quality'])) {
                $msg .= "- Pemulihan / Rest: " . ucfirst(str_replace('_', ' ', $intervalAnalysis['recovery_quality'])) . "\n";
            }
            if (!empty($intervalAnalysis['notes'])) {
                $msg .= "- Catatan: {$intervalAnalysis['notes']}\n";
            }
            $msg .= "\n";
        }

        if (! empty($wentWell)) {
            $msg .= "*Poin Positif:*\n";
            foreach ($wentWell as $w) {
                $msg .= "- {$w}\n";
            }
            $msg .= "\n";
        }

        if (! empty($toImprove)) {
            $msg .= "*Evaluasi & Catatan:*\n";
            foreach ($toImprove as $ti) {
                $msg .= "- {$ti}\n";
            }
            $msg .= "\n";
        }

        if ($nextSuggestion) {
            $msg .= "*Rekomendasi Sesi Berikutnya:*\n";
            $msg .= "- Tipe: " . strtoupper(str_replace('_', ' ', $nextSuggestion)) . "\n";
            if ($nextReason) {
                $msg .= "- Fokus: {$nextReason}\n";
            }
            $msg .= "\n";
        }

        if ($coachRec) {
            $msg .= "*Saran Pemulihan & Pelatih:*\n";
            $msg .= "{$coachRec}\n\n";
        }

        $msg .= "Tetap konsisten dan jaga pemulihan dengan baik!";

        return $msg;
    }

    /**
     * Delete/Unassign program from athlete (destroy enrollment)
     */
    public function destroy($enrollmentId)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $runnerName = $enrollment->runner->name ?? 'Atlet';
        $programTitle = $enrollment->program->title ?? 'Program';

        // Delete associated records & enrollment
        \App\Models\ProgramSessionTracking::where('enrollment_id', $enrollment->id)->delete();
        \App\Models\ProgramWeeklyReport::where('enrollment_id', $enrollment->id)->delete();
        \App\Models\RunnerInjuryLog::where('enrollment_id', $enrollment->id)->delete();
        $enrollment->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Program {$programTitle} untuk {$runnerName} berhasil dihapus/dibatalkan.",
                'redirect' => route('coach.athletes.index')
            ]);
        }

        return redirect()->route('coach.athletes.index')->with('success', "Program {$programTitle} untuk {$runnerName} berhasil dihapus/dibatalkan.");
    }

    /**
     * Reset athlete's program progress and calendar schedule
     */
    public function resetProgram(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'delete_custom_workouts' => 'nullable|boolean',
        ]);

        // Clear all tracking logs for this enrollment
        \App\Models\ProgramSessionTracking::where('enrollment_id', $enrollment->id)->delete();

        // Optionally clear custom workouts if requested
        if ($request->boolean('delete_custom_workouts')) {
            \App\Models\CustomWorkout::where('runner_id', $enrollment->runner_id)->delete();
        }

        // Reset start date & duration
        $startDate = !empty($validated['start_date']) ? Carbon::parse($validated['start_date']) : ($enrollment->start_date ?: now()->startOfDay());
        $durationDays = 84;
        $programJson = $enrollment->program->program_json ?? [];
        $sessions = $programJson['sessions'] ?? [];
        if (! empty($sessions)) {
            $maxDay = collect($sessions)->max('day') ?: 84;
            $durationDays = (int) $maxDay;
        }
        $endDate = $startDate->copy()->addDays($durationDays - 1);

        $enrollment->update([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
            'reschedule_history' => [
                'reset_at' => now()->toIso8601String(),
                'deleted_session_days' => [],
            ],
        ]);

        // Notify runner
        \App\Models\Notification::create([
            'user_id' => $enrollment->runner_id,
            'type' => 'program_reset',
            'title' => 'Program Direset',
            'message' => 'Coach ' . auth()->user()->name . ' telah mereset jadwal program latihan Anda ke awal.',
            'reference_type' => 'program_enrollment',
            'reference_id' => $enrollment->id,
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Program latihan atlet berhasil direset ke awal.',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]);
    }

    /**
     * Delete a single program session day from calendar
     */
    public function destroySession(Request $request, $enrollmentId, $sessionDay)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);
        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $day = (int) $sessionDay;
        if ($day <= 0) {
            return response()->json(['success' => false, 'message' => 'Hari sesi tidak valid.'], 422);
        }

        $history = is_array($enrollment->reschedule_history) ? $enrollment->reschedule_history : [];
        $deletedDays = $history['deleted_session_days'] ?? [];
        if (! in_array($day, $deletedDays, true)) {
            $deletedDays[] = $day;
            $history['deleted_session_days'] = array_values($deletedDays);
            $enrollment->update(['reschedule_history' => $history]);
        }

        \App\Models\ProgramSessionTracking::where('enrollment_id', $enrollment->id)
            ->where('session_day', $day)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Sesi latihan hari ke-{$day} berhasil dihapus dari kalender.",
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
                        $isDuplicate = (int) ($e->errorInfo[1] ?? 0) === 1062 
                            || $e->getCode() === '23000' 
                            || str_contains($e->getMessage(), 'Duplicate entry')
                            || str_contains($e->getMessage(), '1062');
                        if (! $isDuplicate) {
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
     * AJAX search for existing runner users (for manual enrollment autocomplete)
     */
    public function searchUsers(Request $request)
    {
        $query = trim($request->get('q', ''));
        $programId = $request->get('program_id');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $users = \App\Models\User::where('role', 'runner')
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->when($programId, function ($q) use ($programId) {
                // Exclude runners already enrolled in this program
                $q->whereDoesntHave('programEnrollments', function ($eq) use ($programId) {
                    $eq->where('program_id', $programId);
                });
            })
            ->select('id', 'name', 'email', 'phone', 'avatar')
            ->limit(8)
            ->get()
            ->map(fn ($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'email'  => $u->email,
                'phone'  => $u->phone ?? '',
                'avatar' => $u->avatar ? asset('storage/' . $u->avatar) : null,
                'initials' => strtoupper(substr($u->name, 0, 1)),
            ]);

        return response()->json($users);
    }

    /**
     * Enroll runner manually
     */
    public function enrollRunner(Request $request)
    {
        $validated = $request->validate([
            'program_id'       => 'required|exists:programs,id',
            'existing_user_id' => 'nullable|exists:users,id',
            'name'             => 'required_without:existing_user_id|string|max:255',
            'email'            => 'required_without:existing_user_id|email|max:255',
            'phone'            => 'nullable|string|max:20',
            'start_date'       => 'required|date',
            'vdot'             => 'nullable|numeric|min:10|max:85',
            'vdot_mode'        => 'nullable|string|in:direct,pb,balke',
            'pb_distance'      => 'nullable|string|in:5k,10k,21k,42k',
            'pb_time'          => 'nullable|string|regex:/^([0-9]{1,2}:)?[0-9]{1,2}:[0-9]{2}$/',
            'pb_balke'         => 'nullable|numeric|min:100|max:10000',
            'password'         => 'nullable|string|min:6',
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

        // ── Fast-path: use existing user selected via AJAX search ──
        if (!empty($validated['existing_user_id'])) {
            $runner = \App\Models\User::findOrFail($validated['existing_user_id']);

            // Check already enrolled
            $exists = ProgramEnrollment::where('program_id', $program->id)
                ->where('runner_id', $runner->id)
                ->exists();
            if ($exists) {
                return back()->with('error', "{$runner->name} sudah terdaftar dalam program ini.");
            }

            // Enroll directly into Program Bag
            ProgramEnrollment::create([
                'program_id'     => $program->id,
                'runner_id'      => $runner->id,
                'start_date'     => null,
                'end_date'       => null,
                'status'         => 'purchased',
                'payment_status' => 'paid',
                'current_vdot'   => $computedVdot ?? $runner->vdot,
            ]);

            return back()->with('success', "{$runner->name} berhasil didaftarkan ke program bag.");
        }
        // ────────────────────────────────────────────────────────────

        // Find or create runner
        $runner = \App\Models\User::where('email', $validated['email'])->first();

        $isNewUser = false;
        $cleartextPassword = null;

        if (!$runner) {
            $isNewUser = true;
            $cleartextPassword = $validated['password'] ?? \Illuminate\Support\Str::random(8);

            // Create brand new runner
            $runner = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => 'runner',
                'password' => \Illuminate\Support\Facades\Hash::make($cleartextPassword),
                'is_active' => 1,
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

        // Enroll (go to Program Bag first)
        ProgramEnrollment::create([
            'program_id' => $program->id,
            'runner_id' => $runner->id,
            'start_date' => null,
            'end_date' => null,
            'status' => 'purchased',
            'payment_status' => 'paid', // manually enrolled by coach
            'current_vdot' => $computedVdot ?? $runner->vdot,
        ]);

        // Generate Magic Link (expires in 7 days)
        $magicLink = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'login.token', now()->addDays(7), ['user' => $runner->id]
        );

        // Send Email
        \Illuminate\Support\Facades\Mail::to($runner->email)->queue(
            new \App\Mail\RunnerEnrolledMail($runner, $program, $cleartextPassword, $magicLink)
        );

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

            // Enroll (go to Program Bag first)
            ProgramEnrollment::create([
                'program_id' => $program->id,
                'runner_id' => $runner->id,
                'start_date' => null,
                'end_date' => null,
                'status' => 'purchased',
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
     * Reschedule the entire program for a runner (coach action)
     */
    public function reschedule(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::findOrFail($enrollmentId);

        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        // 1. Single Workout / Program Session Drag & Drop Reschedule
        if ($request->has('new_date') || $request->has('type')) {
            $validated = $request->validate([
                'type'        => 'required|in:program_session,custom_workout',
                'new_date'    => 'required|date',
                'workout_id'  => 'nullable|required_if:type,custom_workout|exists:custom_workouts,id',
                'session_day' => 'nullable|required_if:type,program_session|integer',
            ]);

            $newDate = Carbon::parse($validated['new_date']);

            if ($validated['type'] === 'custom_workout') {
                $workout = CustomWorkout::where('id', $validated['workout_id'])
                    ->where('runner_id', $enrollment->runner_id)
                    ->firstOrFail();

                try {
                    $workout->update(['workout_date' => $newDate]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // Handle Duplicate Entry (1062) by Swapping
                    if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
                        $existingWorkout = CustomWorkout::where('runner_id', $enrollment->runner_id)
                            ->where('workout_date', $newDate->format('Y-m-d'))
                            ->first();

                        if ($existingWorkout) {
                            \DB::transaction(function () use ($workout, $existingWorkout, $newDate) {
                                $originalDate = $workout->workout_date;
                                $tempDate = Carbon::parse('1970-01-01');
                                while (CustomWorkout::where('runner_id', $workout->runner_id)->where('workout_date', $tempDate->format('Y-m-d'))->exists()) {
                                    $tempDate->subDay();
                                }
                                $existingWorkout->update(['workout_date' => $tempDate]);
                                $workout->update(['workout_date' => $newDate]);
                                $existingWorkout->update(['workout_date' => $originalDate]);
                            });

                            return response()->json(['success' => true, 'message' => 'Jadwal latihan ditukar karena tanggal tujuan sudah terisi.']);
                        }
                    }
                    throw $e;
                }

                return response()->json(['success' => true, 'message' => 'Latihan kustom berhasil dipindahkan.']);
            } else {
                ProgramSessionTracking::updateOrCreate(
                    [
                        'enrollment_id' => $enrollment->id,
                        'session_day'   => $validated['session_day'],
                    ],
                    [
                        'rescheduled_date' => $newDate,
                    ]
                );

                return response()->json(['success' => true, 'message' => 'Sesi program berhasil dipindahkan.']);
            }
        }

        // 2. Entire Program Reschedule (New Start Date)
        $validated = $request->validate([
            'new_start_date' => 'required|date',
        ]);

        $startDate     = Carbon::parse($validated['new_start_date']);
        $program       = $enrollment->program;
        $durationWeeks = $program->duration_weeks ?? 12;
        $endDate       = $startDate->copy()->addWeeks($durationWeeks);
        $runnerId      = $enrollment->runner_id;

        \DB::transaction(function () use ($enrollment, $startDate, $endDate) {
            // Update enrollment dates
            $enrollment->update([
                'start_date' => $startDate,
                'end_date'   => $endDate,
                'status'     => 'active',
            ]);

            // Clear all per-session reschedule overrides so sessions align to new start
            ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                ->update(['rescheduled_date' => null]);
        });

        // Notify runner
        \App\Models\Notification::create([
            'user_id'        => $runnerId,
            'type'           => 'program_rescheduled',
            'title'          => 'Program Dijadwalkan Ulang',
            'message'        => 'Coach ' . auth()->user()->name . ' telah mengubah jadwal program "' . $program->title . '" mulai ' . $startDate->format('d M Y') . '.',
            'reference_type' => 'program_enrollment',
            'reference_id'   => $enrollment->id,
            'is_read'        => false,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Program berhasil diaktifkan / dijadwalkan ulang mulai ' . $startDate->format('d M Y') . '.',
            'start_date' => $startDate->format('Y-m-d'),
            'end_date'   => $endDate->format('Y-m-d'),
            'status'     => 'active',
        ]);
    }

    /**
     * Update athlete's VDOT & PBs (Coach Action)
     * Supports Cooper Test 12-min, Balke Test 15-min, Race PB (5k-FM), and Direct VDOT.
     */
    public function updateVdot(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);

        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $validated = $request->validate([
            'mode'            => 'required|string|in:cooper,balke,pb,direct',
            'cooper_distance' => 'nullable|numeric|min:100|max:10000',
            'balke_distance'  => 'nullable|numeric|min:100|max:10000',
            'pb_distance'     => 'nullable|string|in:5k,10k,21k,42k',
            'pb_time'         => 'nullable|string',
            'vdot_score'      => 'nullable|numeric|min:10|max:85',
        ]);

        $mode = $validated['mode'];
        $daniels = app(\App\Services\DanielsRunningService::class);
        $computedVdot = null;
        $runnerUpdates = [];

        if ($mode === 'cooper') {
            if (empty($validated['cooper_distance'])) {
                return response()->json(['success' => false, 'message' => 'Jarak tes Cooper 12 menit wajib diisi (meter).'], 422);
            }
            $meters = (float) $validated['cooper_distance'];
            $computedVdot = $daniels->calculateVDOT((string) $meters, 'cooper12');
            $runnerUpdates['pb_balke'] = (int) $meters;
        } elseif ($mode === 'balke') {
            if (empty($validated['balke_distance'])) {
                return response()->json(['success' => false, 'message' => 'Jarak tes Balke 15 menit wajib diisi (meter).'], 422);
            }
            $meters = (float) $validated['balke_distance'];
            $computedVdot = $daniels->calculateVDOT((string) $meters, 'balke15');
            $runnerUpdates['pb_balke'] = (int) $meters;
        } elseif ($mode === 'pb') {
            if (empty($validated['pb_distance']) || empty($validated['pb_time'])) {
                return response()->json(['success' => false, 'message' => 'Jarak dan waktu PB wajib diisi.'], 422);
            }
            try {
                $computedVdot = $daniels->calculateVDOT($validated['pb_time'], $validated['pb_distance']);
                $dist = $validated['pb_distance'];
                if ($dist === '5k') $runnerUpdates['pb_5k'] = $validated['pb_time'];
                elseif ($dist === '10k') $runnerUpdates['pb_10k'] = $validated['pb_time'];
                elseif ($dist === '21k') $runnerUpdates['pb_hm'] = $validated['pb_time'];
                elseif ($dist === '42k') $runnerUpdates['pb_fm'] = $validated['pb_time'];
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Format waktu PB tidak valid (gunakan MM:SS atau HH:MM:SS).'], 422);
            }
        } elseif ($mode === 'direct') {
            if (empty($validated['vdot_score'])) {
                return response()->json(['success' => false, 'message' => 'Skor VDOT wajib diisi.'], 422);
            }
            $computedVdot = (float) $validated['vdot_score'];
        }

        if (!$computedVdot) {
            return response()->json(['success' => false, 'message' => 'Gagal menghitung VDOT.'], 422);
        }

        $computedVdot = max(10, min(85, round($computedVdot, 4)));

        // Generate equivalent race times to keep runner's PBs in sync
        $times = $daniels->calculateEquivalentRaceTimes($computedVdot);
        if (isset($times['5k']['time'])) {
            $runnerUpdates['pb_5k'] = $runnerUpdates['pb_5k'] ?? $times['5k']['time'];
        }

        $runner = $enrollment->runner;

        // Update runner profile & enrollment
        if (!empty($runnerUpdates)) {
            $runner->update($runnerUpdates);
        }
        $enrollment->update(['current_vdot' => $computedVdot]);

        // Fetch updated training profile
        $profileService = app(\App\Services\RunningProfileService::class);
        $updatedProfile = $profileService->getProfile($runner->fresh());

        return response()->json([
            'success'         => true,
            'message'         => 'PB & Skor VDOT atlet berhasil diperbarui!',
            'computed_vdot'   => $computedVdot,
            'trainingProfile' => $updatedProfile,
        ]);
    }

    /**
     * Update custom training paces for an athlete (Coach Action)
     */
    public function updatePaces(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);

        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        if ($request->boolean('reset')) {
            $enrollment->runner->update(['custom_training_paces' => null]);

            $profile = app(\App\Services\RunningProfileService::class)->getProfile($enrollment->runner->fresh());

            return response()->json([
                'success'         => true,
                'message'         => 'Pace latihan berhasil dikembalikan ke perhitungan otomatis VDOT.',
                'is_custom'       => false,
                'trainingProfile' => $profile,
            ]);
        }

        $validated = $request->validate([
            'paces'   => 'required|array',
            'paces.E' => 'nullable|string',
            'paces.M' => 'nullable|string',
            'paces.T' => 'nullable|string',
            'paces.I' => 'nullable|string',
            'paces.R' => 'nullable|string',
        ]);

        $customPaces = [];
        foreach (['E', 'M', 'T', 'I', 'R'] as $type) {
            $val = trim($validated['paces'][$type] ?? '');
            if ($val !== '') {
                $customPaces[$type] = $val;
            }
        }

        if (empty($customPaces)) {
            $enrollment->runner->update(['custom_training_paces' => null]);
        } else {
            $enrollment->runner->update(['custom_training_paces' => $customPaces]);
        }

        $profile = app(\App\Services\RunningProfileService::class)->getProfile($enrollment->runner->fresh());

        return response()->json([
            'success'         => true,
            'message'         => 'Pace latihan khusus berhasil diperbarui!',
            'is_custom'       => !empty($customPaces),
            'trainingProfile' => $profile,
        ]);
    }



    /**
     * Send manual program reminder to runner (coach action)
     */
    public function sendReminder(Request $request, $enrollmentId)
    {
        try {
            $enrollment = ProgramEnrollment::with(['program', 'runner'])->findOrFail($enrollmentId);

            if (!$enrollment->program) {
                return response()->json([
                    'success' => false,
                    'message' => 'Atlet tidak terdaftar dalam program apa pun.'
                ], 200);
            }

            if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
                abort(403);
            }

            $validated = $request->validate([
                'session_day' => 'nullable|integer',
                'custom_workout_id' => 'nullable|integer',
                'channel' => 'required|in:wa,email,both',
                'custom_message' => 'nullable|string',
            ]);

            $runner = $enrollment->runner;
            if (!$runner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data atlet tidak ditemukan.'
                ], 200);
            }

            $program = $enrollment->program;
            $sessionData = null;

            if ($request->filled('custom_workout_id')) {
                $customWorkout = \App\Models\CustomWorkout::findOrFail($validated['custom_workout_id']);
                if ((int) $customWorkout->runner_id !== (int) $runner->id) {
                    abort(403);
                }
                $sessionData = [
                    'type' => $customWorkout->type,
                    'distance' => $customWorkout->distance,
                    'duration' => $customWorkout->duration,
                    'target_pace' => $customWorkout->workout_structure['target_pace'] ?? null,
                    'description' => $customWorkout->description,
                    'notes' => $customWorkout->notes,
                ];
            } elseif ($request->filled('session_day')) {
                $sessions = $program->program_json['sessions'] ?? [];
                $session = collect($sessions)->firstWhere('day', $validated['session_day']);
                if (!$session) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi program tidak ditemukan.'
                    ], 200);
                }
                $sessionData = $session;
            } else {
                // Automatically resolve tomorrow's session, or today's session, or the next upcoming session
                if (!$enrollment->start_date) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Program belum dimulai oleh atlet.'
                    ], 200);
                }

                $sessions = $program->program_json['sessions'] ?? [];
                $trackings = ProgramSessionTracking::where('enrollment_id', $enrollment->id)->get()->keyBy('session_day');

                $tomorrow = Carbon::tomorrow()->toDateString();
                $today = Carbon::today()->toDateString();

                $tomorrowSession = null;
                $todaySession = null;
                $upcomingSession = null;
                $minUpcomingDiff = null;

                foreach ($sessions as $session) {
                    $day = (int) ($session['day'] ?? 0);
                    if ($day <= 0) continue;

                    $sessionDate = $enrollment->start_date->copy()->addDays($day - 1);
                    $tracking = $trackings->get($day);
                    if ($tracking && $tracking->rescheduled_date) {
                        $sessionDate = Carbon::parse($tracking->rescheduled_date);
                    }

                    $sessionDateStr = $sessionDate->toDateString();

                    if ($sessionDateStr === $tomorrow) {
                        $tomorrowSession = $session;
                    }
                    if ($sessionDateStr === $today) {
                        $todaySession = $session;
                    }
                    if ($sessionDateStr >= $today) {
                        $diff = Carbon::parse($sessionDateStr)->diffInDays(Carbon::today());
                        if ($minUpcomingDiff === null || $diff < $minUpcomingDiff) {
                            $minUpcomingDiff = $diff;
                            $upcomingSession = $session;
                        }
                    }
                }

                // Also check custom workouts for tomorrow or today
                $tomorrowCustom = \App\Models\CustomWorkout::where('runner_id', $runner->id)
                    ->whereDate('workout_date', $tomorrow)
                    ->first();
                $todayCustom = \App\Models\CustomWorkout::where('runner_id', $runner->id)
                    ->whereDate('workout_date', $today)
                    ->first();

                if ($tomorrowCustom) {
                    $sessionData = [
                        'type' => $tomorrowCustom->type,
                        'distance' => $tomorrowCustom->distance,
                        'duration' => $tomorrowCustom->duration,
                        'target_pace' => $tomorrowCustom->workout_structure['target_pace'] ?? null,
                        'description' => $tomorrowCustom->description,
                        'notes' => $tomorrowCustom->notes,
                    ];
                } elseif ($tomorrowSession) {
                    $sessionData = $tomorrowSession;
                } elseif ($todayCustom) {
                    $sessionData = [
                        'type' => $todayCustom->type,
                        'distance' => $todayCustom->distance,
                        'duration' => $todayCustom->duration,
                        'target_pace' => $todayCustom->workout_structure['target_pace'] ?? null,
                        'description' => $todayCustom->description,
                        'notes' => $todayCustom->notes,
                    ];
                } elseif ($todaySession) {
                    $sessionData = $todaySession;
                } elseif ($upcomingSession) {
                    $sessionData = $upcomingSession;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada sesi latihan aktif atau mendatang yang ditemukan untuk atlet ini.'
                    ], 200);
                }
            }

            $message = $validated['custom_message'] ?? null;

            if (!$message) {
                // Generate using OpenAI or fallback
                try {
                    $profileService = app(\App\Services\RunningProfileService::class);
                    $openAiService = app(\App\Services\OpenAiService::class);
                    $profileData = $profileService->getProfile($runner);
                    
                    $type = strtolower($sessionData['type'] ?? 'rest');
                    $isRest = in_array($type, ['rest', 'rest day', 'libur']);
                    
                    $distance = $sessionData['distance'] ?? '';
                    $duration = $sessionData['duration'] ?? '';
                    $targetPace = $sessionData['target_pace'] ?? '';
                    $description = $sessionData['description'] ?? $sessionData['notes'] ?? $sessionData['instruction'] ?? '';
                    
                    $pacesInfo = "";
                    if (!empty($profileData['paces'])) {
                        $paces = $profileData['paces'];
                        $pacesInfo = "Pace Latihan: Easy (" . ($paces['easy'] ?? '-') . "), Tempo (" . ($paces['threshold'] ?? '-') . "), Interval (" . ($paces['interval'] ?? '-') . ").";
                    }

                    $prompt = "Buatkan pesan WhatsApp pengingat jadwal program lari besok.\n\n";
                    $prompt .= "Nama Atlet: {$runner->name}\n";
                    $prompt .= "Nama Program: {$program->title}\n";
                    if ($pacesInfo) $prompt .= $pacesInfo . "\n";

                    if ($isRest) {
                        $prompt .= "Jadwal Besok: REST DAY (Hari Istirahat/Pemulihan).\n";
                        $prompt .= "Instruksi: Tulis pesan singkat yang hangat agar atlet beristirahat dengan baik besok. Wajib sertakan '[LINK_CALENDAR]'.";
                    } else {
                        $prompt .= "Jadwal Besok: {$sessionData['type']}\n";
                        if ($distance) $prompt .= "Jarak: {$distance} km\n";
                        if ($duration) $prompt .= "Durasi: {$duration}\n";
                        if ($targetPace) $prompt .= "Target Pace: {$targetPace}\n";
                        if ($description) $prompt .= "Deskripsi Latihan (Instruksi Coach): {$description}\n";
                        
                        $prompt .= "Instruksi: Informasikan menu latihan besok secara singkat berdasarkan deskripsi latihan dari coach, dan beri motivasi ringkas agar semangat. Wajib sertakan '[LINK_CALENDAR]'.";
                    }

                    $coachName = auth()->user()->name ?? 'Coach';
                    $systemMessage = "Anda adalah pelatih lari (Coach lari) Ruang Lari bernama {$coachName}.\n\n"
                        . "Wajib ikuti struktur format persis berikut tanpa diubah:\n"
                        . "Halo [Nama Atlet], Kamu terdaftar di program [Nama Program] oleh coach [Nama Coach], besok kamu ada sesi: [Nama Workout]\n\n"
                        . "Deskripsi: [Penjelasan deskripsi dan panduan pace spesifik sesuai tipe latihan (Easy/Tempo/Interval/Long Run)]\n\n"
                        . "Detail kalender: [LINK_CALENDAR]\n\n"
                        . "ATURAN:\n"
                        . "- Jangan gunakan emoji sama sekali.\n"
                        . "- Jangan gunakan format markdown (seperti *bold* atau _miring_).\n"
                        . "- Tulis teks santai, jelas, profesional, dan alami.";
                    
                    $message = $openAiService->getAiResponse($prompt, $systemMessage);
                    
                    $calendarUrl = route('runner.calendar');
                    if ($message) {
                        $message = preg_replace('/[*_~`]+/', '', $message);
                        $message = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2190}-\x{21FF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}]/u', '', $message);
                        $message = preg_replace('/[ \t]+/', ' ', $message);
                        $message = preg_replace('/\n{3,}/', "\n\n", $message);
                        $message = trim($message);
                        
                        $message = str_replace('[LINK_CALENDAR]', $calendarUrl, $message);
                        if (!str_contains($message, $calendarUrl)) {
                            $message .= "\n\nDetail kalender: " . $calendarUrl;
                        }
                    } else {
                        $message = $this->getManualFallbackMessage($runner, $sessionData, $program, $calendarUrl);
                    }
                } catch (\Exception $e) {
                    $calendarUrl = route('runner.calendar');
                    $message = $this->getManualFallbackMessage($runner, $sessionData, $program, $calendarUrl);
                }
            }

            $sentChannels = [];

            if ($validated['channel'] === 'wa' || $validated['channel'] === 'both') {
                if (!$runner->phone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Runner tidak memiliki nomor telepon terdaftar untuk WhatsApp.'
                    ], 200);
                }
                $waMessage = $message . "\n\nBalas STOP untuk berhenti menerima pengingat.";
                \App\Helpers\WhatsApp::send($runner->phone, $waMessage);
                $sentChannels[] = 'WhatsApp';
            }

            if ($validated['channel'] === 'email' || $validated['channel'] === 'both') {
                if (!$runner->email) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Runner tidak memiliki email terdaftar.'
                    ], 200);
                }
                \Illuminate\Support\Facades\Mail::to($runner->email)->send(
                    new \App\Mail\ProgramReminderMail($runner, $sessionData, $program, $message)
                );
                $sentChannels[] = 'Email';
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengingat berhasil dikirim via ' . implode(' & ', $sentChannels) . '.'
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            throw $e;
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pengingat: ' . $e->getMessage()
            ], 200);
        }
    }

    /**
     * Send Login Access credentials & instructions to athlete via WhatsApp or Email
     */
    public function sendLoginAccess(Request $request, $enrollmentId)
    {
        $enrollment = ProgramEnrollment::with(['runner', 'program'])->findOrFail($enrollmentId);

        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $validated = $request->validate([
            'channel' => 'required|in:wa,email,both',
        ]);

        $runner = $enrollment->runner;
        $coachName = auth()->user()->name ?? 'Coach';
        $loginUrl = route('login');
        $resetUrl = route('password.request');
        $username = $runner->username ?: $runner->email;

        $accessMessage = "Halo {$runner->name},\n\nBerikut informasi akses login akun RuangLari Anda:\n\n🌐 Login: {$loginUrl}\n👤 Username / Email: {$username}\n🔑 Password: (Gunakan password yang sudah dibuat, atau atur ulang melalui: {$resetUrl})\n\nJika ada pertanyaan mengenai latihan, hubungi Coach {$coachName}.\n\nSalam,\nRuangLari Team";

        $sentChannels = [];
        $waUrl = null;

        try {
            if ($validated['channel'] === 'wa' || $validated['channel'] === 'both') {
                if (!$runner->phone) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Atlet tidak memiliki nomor WhatsApp terdaftar.'
                    ], 422);
                }

                $cleanPhone = preg_replace('/\D+/', '', $runner->phone);
                if (str_starts_with($cleanPhone, '0')) {
                    $cleanPhone = '62' . substr($cleanPhone, 1);
                } elseif (!str_starts_with($cleanPhone, '62')) {
                    $cleanPhone = '62' . $cleanPhone;
                }

                try {
                    \App\Helpers\WhatsApp::send($runner->phone, $accessMessage);
                } catch (\Throwable $waErr) {
                    Log::warning('WhatsApp API send failed, providing wa.me fallback', ['error' => $waErr->getMessage()]);
                }

                $waUrl = 'https://wa.me/' . $cleanPhone . '?text=' . urlencode($accessMessage);
                $sentChannels[] = 'WhatsApp';
            }

            if ($validated['channel'] === 'email' || $validated['channel'] === 'both') {
                if (!$runner->email) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Atlet tidak memiliki email terdaftar.'
                    ], 422);
                }

                \Illuminate\Support\Facades\Mail::raw($accessMessage, function ($m) use ($runner) {
                    $m->to($runner->email)
                      ->subject('Informasi Akses Login Akun RuangLari');
                });
                $sentChannels[] = 'Email';
            }

            return response()->json([
                'success' => true,
                'message' => 'Akses login berhasil dikirim via ' . implode(' & ', $sentChannels) . '.',
                'wa_url' => $waUrl
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim akses: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getManualFallbackMessage($runner, $sessionData, $program, $calendarUrl)
    {
        $runnerName = $runner->name ?? 'Atlet';
        $programTitle = $program->title ?? 'Program Lari';
        $coachName = auth()->user()->name ?? 'Coach';
        
        $type = strtolower($sessionData['type'] ?? 'rest');
        $workoutTitle = $sessionData['session_name'] ?? $sessionData['title'] ?? $sessionData['name'] ?? ucfirst(str_replace('_', ' ', $type));
        $isRest = in_array($type, ['rest', 'rest day', 'libur']);

        if ($isRest) {
            return "Halo {$runnerName}, Kamu terdaftar di program {$programTitle} oleh coach {$coachName}, besok kamu ada sesi: Rest Day\n\nDeskripsi: Selamat beristirahat dan jaga kondisi tubuh untuk sesi berikutnya.\n\nDetail kalender: {$calendarUrl}";
        }

        // Retrieve runner paces profile for tailored pace guidance
        $profileService = app(\App\Services\RunningProfileService::class);
        $profileData = $profileService->getProfile($runner);
        $paces = $profileData['paces'] ?? [];

        $distanceVal = $sessionData['distance'] ?? $sessionData['target_distance'] ?? $sessionData['distance_km'] ?? '';
        $distanceStr = !empty($distanceVal) ? "{$distanceVal} km" : '-';

        $targetPaceVal = $sessionData['target_pace'] ?? $sessionData['pace'] ?? '';
        if (empty($targetPaceVal)) {
            $distNum = !empty($distanceVal) ? (float)$distanceVal : 0;
            $descLower = strtolower(($sessionData['description'] ?? '') . ' ' . ($sessionData['notes'] ?? '') . ' ' . ($sessionData['title'] ?? ''));

            $isShortRep = ($distNum > 0 && $distNum <= 0.45) || (bool)preg_match('/\b(55|50|100|150|200|250|300|350|400)\s*m\b/i', $descLower);

            if (in_array($type, ['easy_run', 'easy', 'recovery', 'recovery_run', 'run']) && !$isShortRep) {
                if (!empty($paces['E_high']) && !empty($paces['E_low'])) {
                    $targetPaceVal = $this->formatMinPerKm($paces['E_high']) . ' - ' . $this->formatMinPerKm($paces['E_low']) . ' /km (Easy Pace)';
                } elseif (isset($paces['E'])) {
                    $targetPaceVal = '~' . $this->formatMinPerKm($paces['E']) . ' /km (Easy Pace)';
                } else {
                    $targetPaceVal = 'Zona aerobik ringan (Easy Pace)';
                }
            } elseif (in_array($type, ['tempo', 'threshold', 'tempo_run'])) {
                $tPace = isset($paces['T']) ? $this->formatMinPerKm($paces['T']) : null;
                $targetPaceVal = $tPace ? "~{$tPace} /km (Tempo/Threshold)" : 'Zona threshold terkontrol';
            } elseif (in_array($type, ['repetition', 'speed', 'repeats']) || ($type === 'interval' && $isShortRep)) {
                $rPace = isset($paces['R']) ? $this->formatMinPerKm($paces['R']) : (isset($paces['I']) ? $this->formatMinPerKm($paces['I']) : null);
                $targetPaceVal = $rPace ? "~{$rPace} /km (Repetition Pace 100m-400m)" : 'Repetition Pace (Kecepatan Neuromuskular)';
            } elseif (in_array($type, ['interval', 'vo2max'])) {
                $iPace = isset($paces['I']) ? $this->formatMinPerKm($paces['I']) : null;
                $targetPaceVal = $iPace ? "~{$iPace} /km (Interval Pace 800m-1000m)" : 'Interval Pace VO2max';
            } elseif (in_array($type, ['long_run', 'long'])) {
                $mPace = isset($paces['M']) ? $this->formatMinPerKm($paces['M']) : null;
                $ePace = isset($paces['E']) ? $this->formatMinPerKm($paces['E']) : null;
                $targetPaceVal = $mPace ? "~{$mPace} /km (Marathon Pace)" : ($ePace ? "~{$ePace} /km (Endurance)" : 'Zona endurance terkontrol');
            } else {
                $targetPaceVal = 'Sesuaikan pace dengan target instruksi coach';
            }
        }

        $description = $sessionData['description'] ?? $sessionData['notes'] ?? $sessionData['instruction'] ?? 'Lakukan latihan sesuai arahan coach.';

        return "Halo {$runnerName}, Kamu terdaftar di program {$programTitle} oleh coach {$coachName}, besok kamu ada sesi: {$workoutTitle}\n\n- Jarak: {$distanceStr}\n- Target Pace: {$targetPaceVal}\n- Deskripsi: {$description}\n\nDetail kalender: {$calendarUrl}";
    }

    private function formatMinPerKm($minutes)
    {
        if (is_string($minutes) && str_contains($minutes, ':')) return $minutes;
        $m = floor((float)$minutes);
        $s = round(((float)$minutes - $m) * 60);
        return sprintf('%d:%02d', $m, $s);
    }

    /**
     * Generate AI Program preview for an athlete
     */
    public function generateAiProgram(Request $request, $enrollmentId)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('coach.athletes.show', $enrollmentId);
        }

        $enrollment = ProgramEnrollment::with(['runner', 'program'])->findOrFail($enrollmentId);

        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'target_distance' => 'required|in:5k,10k,21k,42k',
            'target_date'     => 'required|date|after_or_equal:today',
            'start_date'      => 'nullable|date',
            'goal_time'       => 'required|string|regex:/^(\d{1,2}:)?\d{1,2}:\d{2}$/',
            'current_vdot'    => 'nullable|numeric|min:10|max:85',
            'pb_distance'     => 'nullable|string|in:5k,10k,21k,42k',
            'pb_time'         => 'nullable|string|regex:/^(\d{1,2}:)?\d{1,2}:\d{2}$/',
            'weekly_mileage'  => 'required|numeric|min:5|max:200',
            'frequency'       => 'required|integer|min:3|max:7',
            'runner_level'    => 'required|in:beginner,intermediate,advanced',
            'long_run_day'    => 'required|in:saturday,sunday',
            'starting_phase'  => 'required|in:base,build,peak',
            'intensity_tone'  => 'required|in:standard,sharp,conservative',
            'include_strength'=> 'nullable|boolean',
            'strength_type'   => 'nullable|in:bodyweight,gym',
            'is_tropical'     => 'nullable|boolean',
        ]);

        try {
            $daniels = app(DanielsRunningService::class);
            $builder = app(ProgramBuilderService::class);

            // Determine baseline VDOT
            $currentVdot = null;
            if (!empty($validated['current_vdot'])) {
                $currentVdot = (float) $validated['current_vdot'];
            } elseif (!empty($validated['pb_distance']) && !empty($validated['pb_time'])) {
                $currentVdot = $daniels->calculateVDOT($validated['pb_time'], $validated['pb_distance']);
            } elseif ($enrollment->runner && $enrollment->runner->vdot) {
                $currentVdot = (float) $enrollment->runner->vdot;
            } else {
                $currentVdot = 35.0; // safe baseline fallback
            }

            // Target VDOT from goal time
            $targetVdot = $daniels->calculateVDOT($validated['goal_time'], $validated['target_distance']);

            // Level-aware max VDOT improvement limit
            $maxImprovement = match ($validated['runner_level']) {
                'beginner' => 0.08,
                'advanced' => 0.12,
                default    => 0.10,
            };

            // If intensity is sharp, allow slightly sharper progression ceiling
            if ($validated['intensity_tone'] === 'sharp') {
                $maxImprovement += 0.02;
            } elseif ($validated['intensity_tone'] === 'conservative') {
                $maxImprovement = max(0.04, $maxImprovement - 0.03);
            }

            $safeTargetVdot = min($targetVdot, $currentVdot * (1 + $maxImprovement));

            // Calculate duration in weeks
            $targetDate = Carbon::parse($validated['target_date']);
            if (!empty($validated['start_date'])) {
                $startDate = Carbon::parse($validated['start_date']);
                $diffDays = max(7, $startDate->diffInDays($targetDate, false));
                $weeks = max(4, min(24, (int) ceil($diffDays / 7)));
            } else {
                $weeks = max(4, min(24, (int) ceil(now()->diffInWeeks($targetDate))));
                $startDate = now()->next(Carbon::MONDAY);
            }

            $isTropical = filter_var($validated['is_tropical'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $includeStrength = filter_var($validated['include_strength'] ?? true, FILTER_VALIDATE_BOOLEAN);

            // Build program using ProgramBuilderService
            $built = $builder->build([
                'target_distance' => $validated['target_distance'],
                'weekly_mileage'  => (float) $validated['weekly_mileage'],
                'frequency'       => (int) $validated['frequency'],
                'weeks'           => $weeks,
                'initial_vdot'    => $currentVdot,
                'target_vdot'     => $safeTargetVdot,
                'runner_level'    => $validated['runner_level'],
                'long_run_day'    => $validated['long_run_day'],
                'is_tropical'     => $isTropical,
                'include_strength'=> $includeStrength,
                'strength_type'   => $validated['strength_type'] ?? 'bodyweight',
                'injury_history'  => 'none',
                'starting_phase'  => $validated['starting_phase'],
                'intensity_tone'  => $validated['intensity_tone'],
            ]);

            // Calculate training paces
            $paces = $daniels->calculateTrainingPaces($currentVdot);
            if ($isTropical) {
                $paces['E'] *= 1.05;
                $paces['M'] *= 1.05;
                $paces['T'] *= 1.04;
                $paces['I'] *= 1.03;
                $paces['R'] *= 1.02;
            }

            return response()->json([
                'success' => true,
                'program' => [
                    'title' => "AI Plan: " . strtoupper($validated['target_distance']) . " (" . round($safeTargetVdot, 1) . ")",
                    'target_distance' => $validated['target_distance'],
                    'start_date' => $startDate->toDateString(),
                    'target_date' => $targetDate->toDateString(),
                    'weeks' => $weeks,
                    'initial_vdot' => round($currentVdot, 1),
                    'target_vdot' => round($safeTargetVdot, 1),
                    'starting_phase' => $validated['starting_phase'],
                    'intensity_tone' => $validated['intensity_tone'],
                    'phases' => $built['phases'] ?? [],
                    'paces' => $paces,
                    'weekly_mileage_curve' => $built['weekly_mileage_curve'] ?? [],
                    'sessions' => $built['sessions'] ?? [],
                    'summary' => $built['summary'] ?? [],
                ],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI Program Generation Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate program AI: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply generated AI program to athlete's enrollment and calendar
     */
    public function applyAiProgram(Request $request, $enrollmentId)
    {
        if ($request->isMethod('GET')) {
            return redirect()->route('coach.athletes.show', $enrollmentId);
        }

        $enrollment = ProgramEnrollment::with(['runner', 'program'])->findOrFail($enrollmentId);

        if ((int) $enrollment->program->coach_id !== (int) auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'start_date'      => 'required|date',
            'target_date'     => 'required|date',
            'target_distance' => 'required|string',
            'sessions'        => 'required|array|min:1',
            'vdot'            => 'nullable|numeric',
            'weekly_mileage'  => 'nullable|numeric',
            'summary'         => 'nullable|array',
            'title'           => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $runner = $enrollment->runner;
            $startDate = Carbon::parse($validated['start_date']);
            $targetDate = Carbon::parse($validated['target_date']);
            $sessions = $validated['sessions'];
            $totalDays = count($sessions);
            $durationWeeks = (int) max(1, ceil($totalDays / 7));
            $endDate = $startDate->copy()->addDays($totalDays - 1);
            $vdot = isset($validated['vdot']) ? (float)$validated['vdot'] : ($runner->vdot ?? 35);
            $title = $validated['title'] ?? ("AI " . strtoupper($validated['target_distance']) . " Plan (" . round($vdot, 1) . ")");

            // If the program currently has multiple enrollments, create a dedicated program for this athlete
            $otherEnrollmentsCount = ProgramEnrollment::where('program_id', $enrollment->program_id)
                ->where('id', '!=', $enrollment->id)
                ->count();

            if ($otherEnrollmentsCount > 0) {
                // Fork/Create a unique Program for this athlete
                $newProgram = Program::create([
                    'coach_id' => auth()->id(),
                    'title' => $title,
                    'slug' => \Illuminate\Support\Str::slug($title) . '-' . \Illuminate\Support\Str::random(6),
                    'description' => "Program latihan terpersonalisasi AI Daniels VDOT untuk " . $runner->name,
                    'distance_target' => $validated['target_distance'],
                    'duration_weeks' => $durationWeeks,
                    'program_json' => [
                        'sessions' => $sessions,
                        'summary'  => $validated['summary'] ?? [],
                    ],
                    'is_active' => true,
                    'is_public' => false,
                ]);

                $enrollment->update([
                    'program_id'   => $newProgram->id,
                    'start_date'   => $startDate,
                    'end_date'     => $endDate,
                    'current_vdot' => $vdot,
                    'status'       => 'active',
                ]);
            } else {
                // Update the single program directly
                $enrollment->program->update([
                    'title'           => $title,
                    'distance_target' => $validated['target_distance'],
                    'duration_weeks'  => $durationWeeks,
                    'program_json'    => [
                        'sessions' => $sessions,
                        'summary'  => $validated['summary'] ?? [],
                    ],
                ]);

                $enrollment->update([
                    'start_date'   => $startDate,
                    'end_date'     => $endDate,
                    'current_vdot' => $vdot,
                    'status'       => 'active',
                ]);
            }

            // Clean up old pending session tracking so new calendar is fresh
            ProgramSessionTracking::where('enrollment_id', $enrollment->id)
                ->where(function ($q) {
                    $q->where('status', 'pending')
                      ->orWhereNull('status');
                })
                ->delete();

            // Update runner metrics if provided
            if ($vdot > 0) {
                $runner->update([
                    'vdot' => $vdot,
                    'weekly_km_target' => $validated['weekly_mileage'] ?? $runner->weekly_km_target,
                ]);
            }

            // Create notification for runner
            \App\Models\Notification::create([
                'user_id' => $runner->id,
                'type' => 'program_updated',
                'title' => 'Program Latihan AI Diperbarui',
                'message' => 'Coach ' . auth()->user()->name . ' telah memperbarui kalender program latihan Anda.',
                'reference_type' => 'program_enrollment',
                'reference_id' => $enrollment->id,
                'is_read' => false,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Program latihan AI berhasil diterapkan ke kalender atlet.',
                'vdot' => $vdot,
                'weekly_km_target' => $runner->weekly_km_target,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menerapkan program AI: ' . $e->getMessage(),
            ], 500);
        }
    }
}
