<?php

namespace App\Http\Controllers\Runner;

use App\Http\Controllers\Controller;
use App\Models\Admin\StravaConfig;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\StravaActivity;
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
            'approval_prompt' => 'auto',
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
            if ($user->strava_expires_at && $user->strava_expires_at->lte(now()->addMinute())) {
                $refresh = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $user->strava_refresh_token,
                ]);

                if (! $refresh->successful()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal refresh token Strava. Silakan connect ulang.',
                    ], 401);
                }

                $tokenData = $refresh->json();
                $accessToken = data_get($tokenData, 'access_token');

                $user->update([
                    'strava_access_token' => $accessToken,
                    'strava_refresh_token' => data_get($tokenData, 'refresh_token', $user->strava_refresh_token),
                    'strava_expires_at' => now()->addSeconds((int) data_get($tokenData, 'expires_in', 0)),
                ]);
            }

            $after = StravaActivity::where('user_id', $user->id)->max('start_date');
            $afterEpoch = $after ? Carbon::parse($after)->subHours(6)->timestamp : now()->subDays(45)->timestamp;

            $all = [];
            for ($page = 1; $page <= 5; $page++) {
                $res = Http::withoutVerifying()
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

            DB::transaction(function () use ($user, $all, &$imported, &$linked, &$rangeStart, &$rangeEnd, &$warnings) {
                foreach ($all as $a) {
                    $activityId = data_get($a, 'id');
                    if (! is_numeric($activityId) || (string) $activityId === '0') {
                        continue;
                    }
                    $activityId = (string) $activityId;

                    $startDate = data_get($a, 'start_date');
                    $start = null;
                    if ($startDate) {
                        try {
                            $start = Carbon::parse($startDate);
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
                        'start_date' => $startDate,
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
                    ->where('user_id', $user->id)
                    ->whereBetween('start_date', [$rangeStartDate, $rangeEndDate])
                    ->get()
                    ->filter(function ($act) {
                        $t = strtolower((string) $act->type);

                        return in_array($t, ['run', 'virtualrun', 'trailrun', 'treadmill']);
                    })
                    ->groupBy(fn ($act) => $act->start_date?->format('Y-m-d'))
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
                            'completed_at' => $tracking->completed_at ?: $act->start_date,
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

        $activity = StravaActivity::query()
            ->where('user_id', $user->id)
            ->where('strava_activity_id', $activityId)
            ->first();

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity tidak ditemukan.',
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

        $activity = StravaActivity::query()
            ->where('user_id', $user->id)
            ->where('strava_activity_id', $activityId)
            ->first();

        if (! $activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity tidak ditemukan.',
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
}
