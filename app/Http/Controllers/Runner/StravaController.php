<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Admin\StravaConfig;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\StravaActivity;
use App\Services\OpenAiService;
use App\Services\RunningProfileService;
use App\Services\StravaApiService;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class StravaController extends Controller
{
    public function connect()
    {
        $user = auth()->user();
        if ($user && $user->strava_access_token) {
            return redirect()->route('runner.dashboard')->with('success', 'Akun Strava Anda sudah terhubung.');
        }

        $config = StravaConfig::first();
        $clientId = $config->client_id ?? env('STRAVA_CLIENT_ID');
        if (! $clientId) {
            return back()->with('error', 'Strava belum dikonfigurasi.');
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('runner.strava.callback'),
            'response_type' => 'code',
            'scope' => 'activity:read_all,profile:read_all,activity:write',
            'approval_prompt' => 'force',
            'state' => Str::random(24),
        ]);

        return redirect('https://www.strava.com/oauth/authorize?'.$query);
    }

    public function callback(Request $request)
    {
        if (! $request->filled('code')) {
            return redirect()->route('runner.dashboard')->with('error', 'Koneksi Strava gagal.');
        }

        $config = StravaConfig::first();
        $clientId = $config->client_id ?? env('STRAVA_CLIENT_ID');
        $clientSecret = $config->client_secret ?? env('STRAVA_CLIENT_SECRET');
        if (! $clientId || ! $clientSecret) {
            return redirect()->route('runner.dashboard')->with('error', 'Strava belum dikonfigurasi.');
        }

        try {
            $response = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $request->string('code')->toString(),
                'grant_type' => 'authorization_code',
            ]);

            if (! $response->successful()) {
                return redirect()->route('runner.dashboard')->with('error', 'Token exchange gagal.');
            }

            $tokenData = $response->json();
            $user = auth()->user();
            $user->update([
                'strava_id' => data_get($tokenData, 'athlete.id'),
                'strava_access_token' => data_get($tokenData, 'access_token'),
                'strava_refresh_token' => data_get($tokenData, 'refresh_token'),
                'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
            ]);

            return redirect()->route('runner.dashboard')->with('success', 'Strava berhasil tersambung.');
        } catch (\Throwable $e) {
            return redirect()->route('runner.dashboard')->with('error', 'Koneksi error: '.$e->getMessage());
        }
    }

    public function sync(Request $request)
    {
        $user = auth()->user();
        if (! $user->strava_access_token || ! $user->strava_refresh_token) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Strava belum tersambung.',
            ], 422);
        }

        $config = StravaConfig::first();
        $clientId = $config->client_id ?? env('STRAVA_CLIENT_ID');
        $clientSecret = $config->client_secret ?? env('STRAVA_CLIENT_SECRET');
        if (! $clientId || ! $clientSecret) {
            return response()->json([
                'success' => false,
                'message' => 'Strava belum dikonfigurasi.',
            ], 500);
        }

        try {
            $accessToken = $user->strava_access_token;

            // Check if dummy token
            if (str_contains($accessToken, 'dummy') || str_contains($user->strava_refresh_token, 'dummy')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi Strava Anda tidak valid (token dummy). Silakan hubungkan kembali akun Strava riil Anda dari pengaturan.',
                ], 400);
            }

            $needsRefresh = false;
            if ($user->strava_expires_at) {
                try {
                    $needsRefresh = $user->strava_expires_at->lte(now()->addMinute());
                } catch (\Throwable $e) {
                    $needsRefresh = true;
                }
            } else {
                $needsRefresh = true;
            }

            if ($needsRefresh) {
                $refresh = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $user->strava_refresh_token,
                ]);

                if ($refresh->successful()) {
                    $tokenData = $refresh->json();
                    $accessToken = data_get($tokenData, 'access_token');

                    $user->update([
                        'strava_access_token' => $accessToken,
                        'strava_refresh_token' => data_get($tokenData, 'refresh_token', $user->strava_refresh_token),
                        'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal refresh token Strava. Silakan hubungkan kembali akun Strava Anda.',
                    ], 401);
                }
            }

            $after = StravaActivity::where('user_id', $user->id)->max('start_date');
            $afterEpoch = $after ? Carbon::parse($after)->subHours(6)->timestamp : now()->subDays(45)->timestamp;

            $all = [];
            $apiFailed = false;
            $apiErrorStatus = null;

            for ($page = 1; $page <= 5; $page++) {
                $res = Http::withoutVerifying()
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

            // If the initial API call returned 401, try to refresh token
            if ($apiFailed && $apiErrorStatus === 401) {
                $refresh = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $user->strava_refresh_token,
                ]);

                if ($refresh->successful()) {
                    $tokenData = $refresh->json();
                    $accessToken = data_get($tokenData, 'access_token');

                    $user->update([
                        'strava_access_token' => $accessToken,
                        'strava_refresh_token' => data_get($tokenData, 'refresh_token', $user->strava_refresh_token),
                        'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
                    ]);

                    // Retry API call
                    $all = [];
                    $apiFailed = false;
                    for ($page = 1; $page <= 5; $page++) {
                        $res = Http::withoutVerifying()
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
                    'message' => 'Gagal mengambil aktivitas Strava. Silakan hubungkan kembali akun Strava Anda.',
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

            DB::transaction(function () use ($user, $all, &$imported, &$linked, &$rangeStart, &$rangeEnd, &$warnings) {
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
                        'user_id' => $user->id,
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
                    } catch (QueryException $e) {
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
                    ->where('user_id', $user->id)
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

                $enrollments = ProgramEnrollment::where('runner_id', $user->id)
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
                            } catch (QueryException $e) {
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
                'message' => 'Sync selesai.',
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

    private function findOrFetchActivity($user, string $activityId): ?StravaActivity
    {
        $activity = StravaActivity::query()
            ->where('strava_activity_id', $activityId)
            ->first();

        if ($activity) {
            return $activity;
        }

        $api = app(StravaApiService::class);
        $details = $api->fetchActivityDetails($user, $activityId);

        if (! $details) {
            return null;
        }

        return StravaActivity::updateOrCreate(
            [
                'strava_activity_id' => $activityId,
            ],
            [
                'user_id' => $user->id,
                'name' => data_get($details, 'name'),
                'type' => data_get($details, 'sport_type') ?? data_get($details, 'type'),
                'start_date' => data_get($details, 'start_date') ? \Carbon\Carbon::parse(data_get($details, 'start_date'))->format('Y-m-d H:i:s') : null,
                'distance_m' => data_get($details, 'distance'),
                'moving_time_s' => data_get($details, 'moving_time'),
                'elapsed_time_s' => data_get($details, 'elapsed_time'),
                'average_speed' => data_get($details, 'average_speed'),
                'total_elevation_gain' => data_get($details, 'total_elevation_gain'),
                'raw' => ['details' => $details],
            ]
        );
    }

    public function activityDetails(Request $request, string $stravaActivityId)
    {
        $user = auth()->user();
        if (! is_numeric($stravaActivityId) || (string) $stravaActivityId === '0') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activity id.',
            ], 422);
        }
        $activityId = (string) $stravaActivityId;

        $activity = $this->findOrFetchActivity($user, $activityId);

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity tidak ditemukan atau gagal diakses dari Strava.',
            ], 404);
        }

        $raw = is_array($activity->raw) ? $activity->raw : [];
        $details = data_get($raw, 'details');

        if (! is_array($details) || empty($details)) {
            $api = app(StravaApiService::class);
            $details = $api->fetchActivityDetails($user, $activityId);
            if (! $details) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil detail aktivitas Strava.',
                ], 422);
            }

            $activity->update([
                'raw' => array_merge($raw, ['details' => $details]),
            ]);
        }

        $avgSpeed = data_get($details, 'average_speed', $activity->average_speed);
        $api = app(StravaApiService::class);
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

    public function activityStreams(Request $request, string $stravaActivityId)
    {
        $user = auth()->user();
        if (! is_numeric($stravaActivityId) || (string) $stravaActivityId === '0') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activity id.',
            ], 422);
        }
        $activityId = (string) $stravaActivityId;

        $activity = $this->findOrFetchActivity($user, $activityId);

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity tidak ditemukan atau gagal diakses dari Strava.',
            ], 404);
        }

        $raw = is_array($activity->raw) ? $activity->raw : [];
        $streams = data_get($raw, 'streams');
        if (! is_array($streams) || empty($streams)) {
            $api = app(StravaApiService::class);
            $streams = $api->fetchActivityStreams($user, $activityId);
            if (! $streams) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengambil streams aktivitas Strava.',
                ], 422);
            }

            $activity->update([
                'raw' => array_merge($raw, ['streams' => $streams]),
            ]);
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

    public function activityAiAnalysis(Request $request, string $stravaActivityId)
    {
        $user = auth()->user();
        if (! is_numeric($stravaActivityId) || (string) $stravaActivityId === '0') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid activity id.',
            ], 422);
        }

        $activityId = (string) $stravaActivityId;
        
        $activity = $this->findOrFetchActivity($user, $activityId);

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity tidak ditemukan atau gagal diakses dari Strava.',
            ], 404);
        }

        try {
            $api = app(StravaApiService::class);
            $raw = is_array($activity->raw) ? $activity->raw : [];

            $details = data_get($raw, 'details');
            if (! is_array($details) || empty($details)) {
                $details = $api->fetchActivityDetails($user, $activityId);
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
                $streams = $api->fetchActivityStreams($user, $activityId);
                if (is_array($streams) && ! empty($streams)) {
                    $raw['streams'] = $streams;
                } else {
                    $streams = [];
                }
            }

            $activity->update(['raw' => $raw]);

            $profile = app(RunningProfileService::class)->getProfile($user);
            $context = $this->buildRecentTrainingContext($user->id, $activity, $profile);
            $metrics = $this->buildAiWorkoutPayload($activity, $details, $streams, $profile, $context, $api);
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

            $aiRaw = app(OpenAiService::class)->getAiResponse($userPrompt, $systemPrompt, 'gpt-4o');
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

            $decoded = $this->normalizeAiAnalysis($decoded);
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

    public function buildRecentTrainingContext(int $userId, StravaActivity $currentActivity, array $profile): array
    {
        $end = $currentActivity->local_start_date ?: $currentActivity->start_date ?: now();
        $start7 = $end->copy()->subDays(7);
        $start14 = $end->copy()->subDays(14);

        $recentActivities = StravaActivity::query()
            ->where('user_id', $userId)
            ->where('id', '!=', $currentActivity->id)
            ->whereBetween('start_date', [$start14, $end])
            ->orderByDesc('start_date')
            ->get();

        $runCount7 = 0;
        $runCount14 = 0;
        $totalDistanceKm7 = 0.0;
        $totalDistanceKm14 = 0.0;
        $hardSessions7 = 0;

        $minutes14 = 0;
        $easyMinutes14 = 0;
        $greyMinutes14 = 0;
        $qualityMinutes14 = 0;
        $unknownMinutes14 = 0;

        foreach ($recentActivities as $item) {
            $type = strtolower((string) $item->type);
            if (in_array($type, ['run', 'virtualrun', 'trailrun', 'treadmill'], true)) {
                $runCount14++;
                $distanceKm = ((float) ($item->distance_m ?? 0)) / 1000;
                $totalDistanceKm14 += $distanceKm;

                if ($item->start_date && $item->start_date->gte($start7)) {
                    $runCount7++;
                    $totalDistanceKm7 += $distanceKm;
                }

                $details = is_array($item->raw) ? data_get($item->raw, 'details', []) : [];
                $avgHr = (float) data_get($details, 'average_heartrate', 0);
                if ($item->start_date && $item->start_date->gte($start7) && ($avgHr >= 160 || $distanceKm >= 15)) {
                    $hardSessions7++;
                }

                $movingMinutes = (int) round(((int) ($item->moving_time_s ?? 0)) / 60);
                if ($movingMinutes > 0) {
                    $minutes14 += $movingMinutes;
                    $paceSec = null;
                    if (is_numeric($item->average_speed) && (float) $item->average_speed > 0) {
                        $paceSec = (1000 / (float) $item->average_speed);
                    }
                    $bucket = $this->inferPaceBucket($paceSec, $profile);
                    $bucketType = (string) data_get($bucket, 'bucket', 'unknown');
                    if ($bucketType === 'easy') {
                        $easyMinutes14 += $movingMinutes;
                    } elseif ($bucketType === 'grey') {
                        $greyMinutes14 += $movingMinutes;
                    } elseif (in_array($bucketType, ['threshold', 'tempo', 'interval'], true)) {
                        $qualityMinutes14 += $movingMinutes;
                    } else {
                        $unknownMinutes14 += $movingMinutes;
                    }
                }
            }
        }

        $totalDistanceKm7 = round($totalDistanceKm7, 2);
        $totalDistanceKm14 = round($totalDistanceKm14, 2);

        $junk = $this->inferJunkMilesRisk($minutes14, $easyMinutes14, $greyMinutes14, $qualityMinutes14, $unknownMinutes14);

        return [
            'lookback_days' => 14,
            'recent_runs_7d' => $runCount7,
            'recent_runs_14d' => $runCount14,
            'recent_distance_km_7d' => $totalDistanceKm7,
            'recent_distance_km_14d' => $totalDistanceKm14,
            'estimated_hard_sessions_7d' => $hardSessions7,
            'intensity_minutes_14d' => [
                'total' => $minutes14,
                'easy' => $easyMinutes14,
                'grey' => $greyMinutes14,
                'quality' => $qualityMinutes14,
                'unknown' => $unknownMinutes14,
            ],
            'junk_miles_risk' => $junk,
        ];
    }

    public function buildAiWorkoutPayload(
        StravaActivity $activity,
        array $details,
        array $streams,
        array $profile,
        array $context,
        StravaApiService $api
    ): array {
        $avgSpeed = (float) data_get($details, 'average_speed', $activity->average_speed);
        $distanceKm = round(((float) data_get($details, 'distance', $activity->distance_m ?? 0)) / 1000, 2);
        $splits = data_get($details, 'splits_metric', []);
        $avgPaceSeconds = $avgSpeed > 0 ? round((float) (1000 / $avgSpeed), 1) : null;
        $splitPaceSeconds = $this->extractSplitPaceSeconds($splits);
        $splitStats = $this->summarizeSeconds($splitPaceSeconds);
        $hint = $this->inferWorkoutTypeHint($distanceKm, $splitStats, $profile);
        $paceBucket = $this->inferPaceBucket($avgPaceSeconds, $profile);

        return [
            'activity' => [
                'id' => $activity->strava_activity_id,
                'name' => $activity->name,
                'type' => $activity->type,
                'date' => $activity->local_start_date?->toDateString(),
                'distance_km' => $distanceKm,
                'moving_time_minutes' => round(((int) data_get($details, 'moving_time', $activity->moving_time_s ?? 0)) / 60, 1),
                'elapsed_time_minutes' => round(((int) data_get($details, 'elapsed_time', $activity->elapsed_time_s ?? 0)) / 60, 1),
                'average_pace' => $api->formatPaceFromSpeed($avgSpeed),
                'average_pace_seconds' => $avgPaceSeconds,
                'average_heartrate' => data_get($details, 'average_heartrate'),
                'max_heartrate' => data_get($details, 'max_heartrate'),
                'average_cadence' => data_get($details, 'average_cadence'),
                'elevation_gain_m' => data_get($details, 'total_elevation_gain', $activity->total_elevation_gain),
                'split_count' => is_array($splits) ? count($splits) : 0,
                'first_split_pace' => $this->extractSplitPace($splits, 0, $api),
                'last_split_pace' => $this->extractSplitPace($splits, -1, $api),
                'split_pace_seconds' => array_slice($splitPaceSeconds, 0, 30),
                'split_pace_stats' => $splitStats,
                'workout_type_hint' => (string) data_get($hint, 'type', 'unknown'),
                'workout_type_hint_evidence' => data_get($hint, 'evidence', []),
                'pace_bucket' => (string) data_get($paceBucket, 'bucket', 'unknown'),
                'pace_bucket_evidence' => data_get($paceBucket, 'evidence', []),
            ],
            'stream_summary' => [
                'heartrate' => $this->summarizeStream(data_get($streams, 'heartrate.data', []), 0),
                'cadence' => $this->summarizeStream(data_get($streams, 'cadence.data', []), 1),
                'pace' => $this->summarizePaceStream(data_get($streams, 'velocity_smooth.data', []), $api),
            ],
            'runner_profile' => [
                'pb' => $profile['pb'] ?? [],
                'vdot' => $profile['vdot'] ?? null,
                'weekly_km_target' => $profile['weekly_km_target'] ?? null,
                'paces' => $profile['paces'] ?? null,
            ],
            'recent_training_context' => $context,
        ];
    }

    private function summarizeStream(array $values, int $precision = 0): ?array
    {
        $numbers = array_values(array_filter($values, fn ($value) => is_numeric($value)));
        if (empty($numbers)) {
            return null;
        }

        sort($numbers);
        $count = count($numbers);
        $avg = array_sum($numbers) / $count;
        $median = $numbers[(int) floor(($count - 1) / 2)];

        return [
            'min' => round((float) $numbers[0], $precision),
            'avg' => round((float) $avg, $precision),
            'median' => round((float) $median, $precision),
            'max' => round((float) $numbers[$count - 1], $precision),
        ];
    }

    private function summarizePaceStream(array $speeds, StravaApiService $api): ?array
    {
        $numbers = array_values(array_filter($speeds, fn ($value) => is_numeric($value) && (float) $value > 0));
        if (empty($numbers)) {
            return null;
        }

        sort($numbers);
        $count = count($numbers);
        $avg = array_sum($numbers) / $count;
        $median = $numbers[(int) floor(($count - 1) / 2)];

        return [
            'fastest_pace' => $api->formatPaceFromSpeed((float) $numbers[$count - 1]),
            'average_pace' => $api->formatPaceFromSpeed((float) $avg),
            'median_pace' => $api->formatPaceFromSpeed((float) $median),
            'slowest_pace' => $api->formatPaceFromSpeed((float) $numbers[0]),
        ];
    }

    private function extractSplitPace($splits, int $index, StravaApiService $api): ?string
    {
        if (! is_array($splits) || empty($splits)) {
            return null;
        }

        $split = $index === -1 ? end($splits) : ($splits[$index] ?? null);
        if (! is_array($split)) {
            return null;
        }

        $speed = data_get($split, 'average_speed');

        return $speed ? $api->formatPaceFromSpeed((float) $speed) : null;
    }

    private function extractSplitPaceSeconds($splits): array
    {
        if (! is_array($splits) || empty($splits)) {
            return [];
        }

        $out = [];
        foreach ($splits as $s) {
            if (! is_array($s)) {
                continue;
            }
            $speed = data_get($s, 'average_speed');
            if (! is_numeric($speed)) {
                continue;
            }
            $speed = (float) $speed;
            if ($speed <= 0) {
                continue;
            }
            $out[] = (1000 / $speed);
        }

        return $out;
    }

    private function summarizeSeconds(array $values): ?array
    {
        $numbers = array_values(array_filter($values, fn ($v) => is_numeric($v) && (float) $v > 0));
        if (! $numbers) {
            return null;
        }

        sort($numbers);
        $count = count($numbers);
        $avg = array_sum($numbers) / $count;
        $median = $numbers[(int) floor(($count - 1) / 2)];
        $min = $numbers[0];
        $max = $numbers[$count - 1];

        $variance = 0.0;
        foreach ($numbers as $n) {
            $variance += pow(((float) $n) - $avg, 2);
        }
        $std = $count > 1 ? sqrt($variance / ($count - 1)) : 0.0;
        $cv = $avg > 0 ? ($std / $avg) : 0.0;
        $ratio = $min > 0 ? ($max / $min) : null;

        return [
            'count' => $count,
            'min_pace' => $this->formatPaceSeconds((float) $min),
            'median_pace' => $this->formatPaceSeconds((float) $median),
            'avg_pace' => $this->formatPaceSeconds((float) $avg),
            'max_pace' => $this->formatPaceSeconds((float) $max),
            'cv' => round((float) $cv, 3),
            'slowest_to_fastest_ratio' => $ratio ? round((float) $ratio, 3) : null,
        ];
    }

    private function inferWorkoutTypeHint(float $distanceKm, ?array $splitStats, array $profile): array
    {
        $type = 'unknown';
        $evidence = [];

        $count = (int) data_get($splitStats, 'count', 0);
        $cv = (float) data_get($splitStats, 'cv', 0);
        $ratio = (float) data_get($splitStats, 'slowest_to_fastest_ratio', 0);

        $paces = is_array($profile['paces'] ?? null) ? $profile['paces'] : [];
        $easySec = $this->minutesPerKmToSeconds(data_get($paces, 'E'));
        $thresholdSec = $this->minutesPerKmToSeconds(data_get($paces, 'T'));
        $medianSec = $this->paceStringToSeconds(data_get($splitStats, 'median_pace'));

        if ($splitStats && $count >= 6 && ($ratio >= 1.22 || $cv >= 0.12)) {
            return [
                'type' => 'interval',
                'evidence' => ["Variasi pace split tinggi (ratio {$ratio}, cv {$cv})."],
            ];
        }

        if ($thresholdSec && $medianSec) {
            $diff = abs($medianSec - $thresholdSec) / $thresholdSec;
            if ($diff <= 0.04) {
                $type = 'threshold';
                $evidence[] = 'Median pace mendekati T pace runner.';
            } elseif ($medianSec > $thresholdSec && (($medianSec - $thresholdSec) / $thresholdSec) <= 0.12) {
                $type = 'tempo';
                $evidence[] = 'Median pace sedikit lebih lambat dari T pace (tempo).';
            }
        }

        if ($type === 'unknown' && $easySec && $medianSec) {
            $diff = abs($medianSec - $easySec) / $easySec;
            if ($diff <= 0.12) {
                $type = 'easy';
                $evidence[] = 'Median pace berada di sekitar easy pace runner.';
            }
        }

        if ($type === 'unknown' && $distanceKm >= 14) {
            $type = 'mixed';
            $evidence[] = 'Jarak cukup panjang; kemungkinan sesi campuran.';
        }

        return ['type' => $type, 'evidence' => $evidence];
    }

    private function minutesPerKmToSeconds($minutesPerKm): ?float
    {
        if (! is_numeric($minutesPerKm)) {
            return null;
        }
        $m = (float) $minutesPerKm;
        return $m > 0 ? $m * 60 : null;
    }

    private function paceStringToSeconds($pace): ?float
    {
        if (! is_string($pace)) {
            return null;
        }
        $pace = trim($pace);
        if (! preg_match('/^(\d+):(\d{2})$/', $pace, $m)) {
            return null;
        }
        return ((int) $m[1] * 60) + (int) $m[2];
    }

    private function formatPaceSeconds(float $secondsPerKm): string
    {
        if ($secondsPerKm <= 0) {
            return '-';
        }
        $t = (int) round($secondsPerKm);
        return sprintf('%d:%02d', intdiv($t, 60), $t % 60);
    }

    private function inferPaceBucket(?float $paceSeconds, array $profile): array
    {
        if (! $paceSeconds || $paceSeconds <= 0) {
            return ['bucket' => 'unknown', 'evidence' => ['Pace tidak tersedia.']];
        }

        $paces = is_array($profile['paces'] ?? null) ? $profile['paces'] : [];
        $easySec = $this->minutesPerKmToSeconds(data_get($paces, 'E'));
        $thresholdSec = $this->minutesPerKmToSeconds(data_get($paces, 'T'));
        $intervalSec = $this->minutesPerKmToSeconds(data_get($paces, 'I'));

        if ($intervalSec && $paceSeconds <= ($intervalSec * 1.06)) {
            return ['bucket' => 'interval', 'evidence' => ['Pace mendekati/lebih cepat dari I pace.']];
        }

        if ($thresholdSec && $paceSeconds <= ($thresholdSec * 1.06)) {
            return ['bucket' => 'threshold', 'evidence' => ['Pace mendekati T pace.']];
        }

        if ($easySec && $paceSeconds >= ($easySec * 0.92)) {
            return ['bucket' => 'easy', 'evidence' => ['Pace berada di sekitar easy pace.']];
        }

        if ($easySec && $thresholdSec && $paceSeconds < ($easySec * 0.92) && $paceSeconds > ($thresholdSec * 1.06)) {
            return ['bucket' => 'grey', 'evidence' => ['Pace berada di antara easy dan threshold (grey zone).']];
        }

        return ['bucket' => 'unknown', 'evidence' => ['Tidak cukup data untuk menentukan zona pace.']];
    }

    private function inferJunkMilesRisk(int $totalMinutes, int $easyMinutes, int $greyMinutes, int $qualityMinutes, int $unknownMinutes): array
    {
        if ($totalMinutes <= 0) {
            return ['level' => 'unknown', 'evidence' => ['Tidak ada data durasi latihan.']];
        }

        if ($totalMinutes < 120) {
            return ['level' => 'unknown', 'evidence' => ['Data 14 hari masih terlalu sedikit untuk menilai junk miles.']];
        }

        $greyShare = $greyMinutes / $totalMinutes;
        $qualityShare = $qualityMinutes / $totalMinutes;

        $level = 'low';
        if ($greyShare >= 0.45 && $qualityShare < 0.25) {
            $level = 'high';
        } elseif ($greyShare >= 0.30 && $qualityShare < 0.30) {
            $level = 'medium';
        }

        return [
            'level' => $level,
            'evidence' => [
                'Grey zone ' . round($greyShare * 100) . '% dari total durasi 14 hari.',
                'Quality ' . round($qualityShare * 100) . '% dari total durasi 14 hari.',
                'Total durasi 14 hari: ' . $totalMinutes . ' menit.',
            ],
        ];
    }

    public function normalizeAiAnalysis(array $decoded): array
    {
        return [
            'workout_classification' => [
                'type' => (string) data_get($decoded, 'workout_classification.type', ''),
                'evidence' => array_values(array_filter(data_get($decoded, 'workout_classification.evidence', []), fn ($item) => is_string($item) && trim($item) !== '')),
            ],
            'junk_miles_risk' => [
                'level' => (string) data_get($decoded, 'junk_miles_risk.level', 'unknown'),
                'evidence' => array_values(array_filter(data_get($decoded, 'junk_miles_risk.evidence', []), fn ($item) => is_string($item) && trim($item) !== '')),
            ],
            'summary' => (string) ($decoded['summary'] ?? ''),
            'what_went_well' => array_values(array_filter($decoded['what_went_well'] ?? [], fn ($item) => is_string($item) && trim($item) !== '')),
            'what_to_improve' => array_values(array_filter($decoded['what_to_improve'] ?? [], fn ($item) => is_string($item) && trim($item) !== '')),
            'risk_flags' => array_values(array_filter($decoded['risk_flags'] ?? [], fn ($item) => is_string($item) && trim($item) !== '')),
            'next_workout_suggestion' => [
                'type' => (string) data_get($decoded, 'next_workout_suggestion.type', ''),
                'reason' => (string) data_get($decoded, 'next_workout_suggestion.reason', ''),
                'duration' => (string) data_get($decoded, 'next_workout_suggestion.duration', ''),
                'target' => (string) data_get($decoded, 'next_workout_suggestion.target', ''),
            ],
            'recovery_advice' => array_values(array_filter($decoded['recovery_advice'] ?? [], fn ($item) => is_string($item) && trim($item) !== '')),
            'improve_next_time' => array_values(array_filter($decoded['improve_next_time'] ?? [], fn ($item) => is_string($item) && trim($item) !== '')),
            'confidence' => (string) ($decoded['confidence'] ?? 'medium'),
        ];
    }
}
