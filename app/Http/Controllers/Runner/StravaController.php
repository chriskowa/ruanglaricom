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
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

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
            $athleteProfile = data_get($tokenData, 'athlete.profile') ?: data_get($tokenData, 'athlete.profile_medium');
            $updateData = [
                'strava_id' => data_get($tokenData, 'athlete.id'),
                'strava_access_token' => data_get($tokenData, 'access_token'),
                'strava_refresh_token' => data_get($tokenData, 'refresh_token'),
                'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
            ];

            if ($athleteProfile && (! $user->avatar || str_contains($user->avatar, 'strava') || str_contains($user->avatar, 'cloudfront') || str_contains($user->avatar, 'default'))) {
                $updateData['avatar'] = $athleteProfile;
            }

            $user->update($updateData);

            return redirect()->route('runner.dashboard')->with('success', 'Strava berhasil tersambung.');
        } catch (\Throwable $e) {
            return redirect()->route('runner.dashboard')->with('error', 'Koneksi error: '.$e->getMessage());
        }
    }

    public function disconnect(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            if ($user->strava_access_token) {
                try {
                    Http::withoutVerifying()->post('https://www.strava.com/oauth/deauthorize', [
                        'access_token' => $user->strava_access_token,
                    ]);
                } catch (\Throwable $e) {
                    // Ignore upstream network issues when revoking token on Strava end
                }
            }

            $user->update([
                'strava_id' => null,
                'strava_access_token' => null,
                'strava_refresh_token' => null,
                'strava_expires_at' => null,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Koneksi Strava berhasil dilepas (unauthorized).',
            ]);
        }

        return redirect()->route('runner.dashboard')->with('success', 'Akun Strava Anda berhasil dilepas (unauthorized).');
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
                    'average_heartrate' => data_get($s, 'average_heartrate'),
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

            $systemPrompt = "Anda adalah AI Running Coach Ruang Lari yang sangat cerdas & teliti.\n"
                ."PRINSIP UTAMA KLASIFIKASI JENIS WORKOUT (WAJIB DIPATUHI):\n"
                ."1. Lari dengan jarak >= 14 km (misal 15km, 18km, 24km, 30km) BERSTATUS 'long_run' atau 'long_run_quality'. DILARANG KERAS mengklasifikasikan lari jarak >= 14 km sebagai 'interval'!\n"
                ."2. Lari 'interval' HANYA berlaku untuk sesi dengan repetisi cepat-lambat berulang (sawtooth) dengan total jarak biasanya <= 14 km.\n"
                ."3. Adanya variasi pace akibat water stop, lampu merah, atau tanjakan pada lari 24 km TIDAK BOLEH membuat lari tersebut dianggap sebagai interval.\n"
                ."4. Jika jarak lari >= 14 km dan terdapat segmen tempo/fast finish di dalamnya, gunakan tipe 'long_run_quality'.\n\n"
                ."Jawab hanya dalam Bahasa Indonesia yang ringkas, spesifik, dan presisi. Return HARUS JSON valid.";

            $userPrompt = "Analisis workout berikut dan berikan insight pelatihan.\n"
                ."Wajib identifikasi jenis sesi berdasarkan variasi pace (split/stream), jarak total, dan konteks pace latihan runner.\n"
                ."Jika konteks menyebut 'junk_miles_risk.level' = medium/high, tambahkan 1 item ke risk_flags dengan format: \"Junk miles risk: <level> - <alasan singkat>\".\n"
                ."Summary WAJIB diawali dengan 'Jenis sesi: <type>.'\n"
                ."Format output JSON:\n"
                ."{\n"
                ."  \"workout_classification\": {\n"
                ."    \"type\": \"easy_run|long_run|long_run_quality|interval|tempo|threshold|recovery|mixed|unknown\",\n"
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

            // Post-AI Safety Guardrail: Force distance >= 14km to be long_run or long_run_quality
            $distKm = (float) data_get($metrics, 'activity.distance_km', 0);
            if ($distKm >= 14 && in_array(data_get($decoded, 'workout_classification.type'), ['interval', 'easy', 'mixed', 'unknown'], true)) {
                $hasVariation = data_get($metrics, 'activity.split_pace_stats.cv', 0) >= 0.15;
                $newType = $hasVariation ? 'long_run_quality' : 'long_run';
                $decoded['workout_classification']['type'] = $newType;
                $decoded['workout_classification']['evidence'] = array_merge(
                    ["Jarak total {$distKm} km diklasifikasikan secara definitif sebagai Long Run."],
                    $decoded['workout_classification']['evidence'] ?? []
                );
                if (isset($decoded['summary']) && str_contains($decoded['summary'], 'Jenis sesi:')) {
                    $decoded['summary'] = preg_replace('/Jenis sesi:\s*[^.]+\./i', 'Jenis sesi: ' . ($hasVariation ? 'Long Run Quality' : 'Long Run') . '.', $decoded['summary']);
                }
            }

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
        $rawLaps = data_get($details, 'laps', []);
        
        $avgPaceSeconds = $avgSpeed > 0 ? round((float) (1000 / $avgSpeed), 1) : null;
        $splitPaceSeconds = $this->extractSplitPaceSeconds($splits);
        $splitStats = $this->summarizeSeconds($splitPaceSeconds);

        // Analyze Laps (200m, 400m, 800m, 1000m repetitions)
        $lapsAnalysis = $this->analyzeLapsForIntervals($rawLaps, $profile, $api);

        // Analyze Stream Surges (High intensity bursts from velocity stream)
        $streamSurges = $this->analyzeVelocityStreamForSurges($streams, $profile, $api);

        $hint = $this->inferWorkoutTypeHint($distanceKm, $splitStats, $profile, $lapsAnalysis, $streamSurges);
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
                'laps_count' => count($lapsAnalysis['laps_list']),
                'laps_summary' => array_slice($lapsAnalysis['laps_list'], 0, 40),
                'detected_interval_structure' => $lapsAnalysis['interval_structure'],
                'telemetry_surge_analysis' => $streamSurges,
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

    public function analyzeLapsForIntervals(array $rawLaps, array $profile, StravaApiService $api): array
    {
        $lapsList = [];
        if (! is_array($rawLaps) || empty($rawLaps)) {
            return [
                'laps_list' => [],
                'interval_structure' => null,
                'is_structured_interval' => false,
            ];
        }

        $workLaps = [];
        $recoveryLaps = [];
        $paces = is_array($profile['paces'] ?? null) ? $profile['paces'] : [];
        $easyPaceSec = $this->minutesPerKmToSeconds(data_get($paces, 'E', 360));

        foreach ($rawLaps as $idx => $lap) {
            if (! is_array($lap)) continue;
            $distM = (float) data_get($lap, 'distance', 0);
            $movSec = (int) data_get($lap, 'moving_time', data_get($lap, 'elapsed_time', 0));
            $speed = ($distM > 0 && $movSec > 0) ? ($distM / $movSec) : 0;
            $paceStr = $api->formatPaceFromSpeed($speed);
            $paceSec = $speed > 0 ? (1000 / $speed) : null;

            $item = [
                'lap' => $idx + 1,
                'name' => data_get($lap, 'name') ?: 'Lap ' . ($idx + 1),
                'distance_m' => round($distM),
                'moving_time_s' => $movSec,
                'pace' => $paceStr,
                'pace_seconds' => $paceSec ? round($paceSec, 1) : null,
                'avg_hr' => data_get($lap, 'average_heartrate'),
                'max_hr' => data_get($lap, 'max_heartrate'),
                'avg_cadence' => data_get($lap, 'average_cadence'),
            ];
            $lapsList[] = $item;

            // Classify short repetition laps vs recovery laps
            if ($distM >= 120 && $distM <= 1800 && $paceSec) {
                if ($paceSec <= ($easyPaceSec * 0.92) || ($distM <= 600 && $paceSec < $easyPaceSec)) {
                    $workLaps[] = $item;
                } elseif ($distM <= 600 && $paceSec >= $easyPaceSec) {
                    $recoveryLaps[] = $item;
                }
            }
        }

        $workCount = count($workLaps);
        $structure = null;
        $isInterval = false;

        if ($workCount >= 3) {
            $isInterval = true;
            $distances = array_column($workLaps, 'distance_m');
            $avgDist = array_sum($distances) / $workCount;
            
            $approxDist = 'Custom';
            if ($avgDist >= 150 && $avgDist <= 280) $approxDist = '200m';
            elseif ($avgDist >= 281 && $avgDist <= 380) $approxDist = '300m';
            elseif ($avgDist >= 381 && $avgDist <= 550) $approxDist = '400m';
            elseif ($avgDist >= 551 && $avgDist <= 750) $approxDist = '600m';
            elseif ($avgDist >= 751 && $avgDist <= 950) $approxDist = '800m';
            elseif ($avgDist >= 951 && $avgDist <= 1150) $approxDist = '1000m / 1K';
            elseif ($avgDist >= 1151 && $avgDist <= 1400) $approxDist = '1200m';
            elseif ($avgDist >= 1401 && $avgDist <= 1800) $approxDist = '1600m / 1 Mil';
            else $approxDist = round($avgDist) . 'm';

            $workPaceSecs = array_values(array_filter(array_column($workLaps, 'pace_seconds')));
            $avgWorkPaceSec = !empty($workPaceSecs) ? (array_sum($workPaceSecs) / count($workPaceSecs)) : 0;
            $minWorkPaceSec = !empty($workPaceSecs) ? min($workPaceSecs) : 0;
            $maxWorkPaceSec = !empty($workPaceSecs) ? max($workPaceSecs) : 0;

            // Pacing dropoff: compare last chunk of reps vs first chunk of reps
            $dropoffSec = 0;
            if ($workCount >= 4) {
                $firstChunk = array_slice($workPaceSecs, 0, (int) ceil($workCount / 3));
                $lastChunk = array_slice($workPaceSecs, - (int) ceil($workCount / 3));
                $avgFirst = array_sum($firstChunk) / count($firstChunk);
                $avgLast = array_sum($lastChunk) / count($lastChunk);
                $dropoffSec = round($avgLast - $avgFirst, 1);
            }

            $recDurations = array_column($recoveryLaps, 'moving_time_s');
            $avgRecSec = !empty($recDurations) ? round(array_sum($recDurations) / count($recDurations)) : null;

            $structure = [
                'repetition_count' => $workCount,
                'target_distance_approx' => $approxDist,
                'avg_rep_distance_m' => round($avgDist),
                'avg_work_pace' => $this->formatPaceSeconds($avgWorkPaceSec) . ' /km',
                'fastest_rep_pace' => $this->formatPaceSeconds($minWorkPaceSec) . ' /km',
                'slowest_rep_pace' => $this->formatPaceSeconds($maxWorkPaceSec) . ' /km',
                'pace_dropoff_seconds' => $dropoffSec,
                'pacing_trend' => ($dropoffSec > 5 ? 'fatigue_slowdown' : ($dropoffSec < -5 ? 'progressive_negative_split' : 'consistent_even')),
                'avg_recovery_seconds' => $avgRecSec,
                'rep_paces_summary' => array_map(fn($w) => "Lap {$w['lap']} ({$w['distance_m']}m @ {$w['pace']})", array_slice($workLaps, 0, 20)),
                'title' => "{$workCount}x {$approxDist} Repetisi (Avg {$this->formatPaceSeconds($avgWorkPaceSec)} /km)",
            ];
        }

        return [
            'laps_list' => $lapsList,
            'interval_structure' => $structure,
            'is_structured_interval' => $isInterval,
            'detected_workout_title' => $structure['title'] ?? null,
        ];
    }

    public function analyzeVelocityStreamForSurges(array $streams, array $profile, StravaApiService $api): array
    {
        $velocities = data_get($streams, 'velocity_smooth.data', []);
        $times = data_get($streams, 'time.data', []);
        $distances = data_get($streams, 'distance.data', []);
        $heartrates = data_get($streams, 'heartrate.data', []);

        if (! is_array($velocities) || count($velocities) < 30 || ! is_array($times) || count($times) !== count($velocities)) {
            return ['detected_surge_count' => 0, 'surges' => []];
        }

        $validSpeeds = array_values(array_filter($velocities, fn($v) => is_numeric($v) && (float)$v > 0.5));
        if (count($validSpeeds) < 30) return ['detected_surge_count' => 0, 'surges' => []];
        
        sort($validSpeeds);
        $medianSpeed = $validSpeeds[(int) floor(count($validSpeeds) / 2)];
        $surgeThresholdSpeed = max(3.2, $medianSpeed * 1.20);

        $surges = [];
        $inSurge = false;
        $surgeStartIdx = 0;

        for ($i = 0; $i < count($velocities); $i++) {
            $speed = (float) ($velocities[$i] ?? 0);
            if ($speed >= $surgeThresholdSpeed) {
                if (! $inSurge) {
                    $inSurge = true;
                    $surgeStartIdx = $i;
                }
            } else {
                if ($inSurge) {
                    $inSurge = false;
                    $durSec = ($times[$i] ?? 0) - ($times[$surgeStartIdx] ?? 0);
                    $distM = ($distances[$i] ?? 0) - ($distances[$surgeStartIdx] ?? 0);
                    if ($durSec >= 15 && $durSec <= 400 && $distM >= 80) {
                        $surgeSpeeds = array_slice($velocities, $surgeStartIdx, $i - $surgeStartIdx + 1);
                        $avgSurgeSpeed = array_sum($surgeSpeeds) / count($surgeSpeeds);
                        $surges[] = [
                            'duration_s' => $durSec,
                            'distance_m' => round($distM),
                            'pace' => $api->formatPaceFromSpeed($avgSurgeSpeed),
                            'avg_hr' => !empty($heartrates) ? round(array_sum(array_slice($heartrates, $surgeStartIdx, $i - $surgeStartIdx + 1)) / max(1, $i - $surgeStartIdx + 1)) : null,
                        ];
                    }
                }
            }
        }

        return [
            'detected_surge_count' => count($surges),
            'surges' => array_slice($surges, 0, 20),
            'avg_surge_duration_s' => !empty($surges) ? round(array_sum(array_column($surges, 'duration_s')) / count($surges)) : null,
            'avg_surge_distance_m' => !empty($surges) ? round(array_sum(array_column($surges, 'distance_m')) / count($surges)) : null,
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

    private function inferWorkoutTypeHint(
        float $distanceKm,
        ?array $splitStats,
        array $profile,
        array $lapsAnalysis = [],
        array $streamSurges = []
    ): array {
        $type = 'unknown';
        $evidence = [];

        $count = (int) data_get($splitStats, 'count', 0);
        $cv = (float) data_get($splitStats, 'cv', 0);
        $ratio = (float) data_get($splitStats, 'slowest_to_fastest_ratio', 0);

        $paces = is_array($profile['paces'] ?? null) ? $profile['paces'] : [];
        $easySec = $this->minutesPerKmToSeconds(data_get($paces, 'E'));
        $thresholdSec = $this->minutesPerKmToSeconds(data_get($paces, 'T'));
        $medianSec = $this->paceStringToSeconds(data_get($splitStats, 'median_pace'));

        // RULE 1: Distance >= 14 km is ALWAYS a Long Run (long_run or long_run_quality). NEVER interval.
        if ($distanceKm >= 14) {
            if ($cv >= 0.15 || $ratio >= 1.25) {
                return [
                    'type' => 'long_run_quality',
                    'evidence' => ["Jarak {$distanceKm} km merupakan Lari Jarak Jauh (Long Run) dengan segmen variasi pace / fast finish."],
                ];
            }
            return [
                'type' => 'long_run',
                'evidence' => ["Jarak {$distanceKm} km diklasifikasikan secara definitif sebagai Long Run (Lari Jarak Jauh)."],
            ];
        }

        // RULE 2: Laps based interval detection (e.g. 10x 200m, 8x 400m, 5x 1km)
        if ($distanceKm < 14 && !empty($lapsAnalysis['is_structured_interval'])) {
            $struct = $lapsAnalysis['interval_structure'] ?? [];
            return [
                'type' => 'interval',
                'evidence' => [
                    "Terdeteksi struktur latihan {$struct['title']} dari rekaman lap jam tangan atlet.",
                    "Konsistensi repetisi: {$struct['pacing_trend']} (Fastest: {$struct['fastest_rep_pace']}, Slowest: {$struct['slowest_rep_pace']})."
                ],
            ];
        }

        // RULE 3: Telemetry Stream Surges interval detection
        if ($distanceKm < 14 && ($streamSurges['detected_surge_count'] ?? 0) >= 4) {
            return [
                'type' => 'interval',
                'evidence' => ["Terdeteksi {$streamSurges['detected_surge_count']} surge/lonjakan kecepatan tinggi dari grafik telemetri."],
            ];
        }

        // RULE 4: Split metric variation
        if ($distanceKm < 14 && $splitStats && $count >= 4 && ($ratio >= 1.30 || $cv >= 0.14)) {
            return [
                'type' => 'interval',
                'evidence' => ["Lari {$distanceKm} km menunjukkan variasi pace split berulang khas latihan interval (ratio {$ratio}, cv {$cv})."],
            ];
        }

        if ($thresholdSec && $medianSec) {
            $diff = abs($medianSec - $thresholdSec) / $thresholdSec;
            if ($diff <= 0.04) {
                $type = 'threshold';
                $evidence[] = 'Median pace mendekati T (Threshold) pace runner.';
            } elseif ($medianSec > $thresholdSec && (($medianSec - $thresholdSec) / $thresholdSec) <= 0.12) {
                $type = 'tempo';
                $evidence[] = 'Median pace sedikit lebih lambat dari T pace (tempo).';
            }
        }

        if ($type === 'unknown' && $easySec && $medianSec) {
            $diff = abs($medianSec - $easySec) / $easySec;
            if ($diff <= 0.12) {
                $type = 'easy_run';
                $evidence[] = 'Median pace berada di sekitar Easy pace runner.';
            }
        }

        if ($type === 'unknown') {
            $type = $distanceKm >= 10 ? 'long_run' : 'easy_run';
            $evidence[] = "Jarak {$distanceKm} km dikategorikan sebagai " . ($distanceKm >= 10 ? 'Long Run' : 'Easy Run') . '.';
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
