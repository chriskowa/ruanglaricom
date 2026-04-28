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
            $context = $this->buildRecentTrainingContext($user->id, $activity);
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
                ."Format output JSON:\n"
                ."{\n"
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

    private function buildRecentTrainingContext(int $userId, StravaActivity $currentActivity): array
    {
        $end = $currentActivity->local_start_date ?: $currentActivity->start_date ?: now();
        $start = $end->copy()->subDays(7);

        $recentActivities = StravaActivity::query()
            ->where('user_id', $userId)
            ->where('id', '!=', $currentActivity->id)
            ->whereBetween('start_date', [$start, $end])
            ->orderByDesc('start_date')
            ->get();

        $totalDistanceKm = round($recentActivities->sum(fn ($item) => ((float) ($item->distance_m ?? 0)) / 1000), 2);
        $runCount = 0;
        $hardSessions = 0;

        foreach ($recentActivities as $item) {
            $type = strtolower((string) $item->type);
            if (in_array($type, ['run', 'virtualrun', 'trailrun', 'treadmill'], true)) {
                $runCount++;
            }

            $details = is_array($item->raw) ? data_get($item->raw, 'details', []) : [];
            $avgHr = (float) data_get($details, 'average_heartrate', 0);
            $distanceKm = ((float) ($item->distance_m ?? 0)) / 1000;
            if ($avgHr >= 160 || $distanceKm >= 15) {
                $hardSessions++;
            }
        }

        return [
            'lookback_days' => 7,
            'recent_runs' => $runCount,
            'recent_distance_km' => $totalDistanceKm,
            'estimated_hard_sessions' => $hardSessions,
        ];
    }

    private function buildAiWorkoutPayload(
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
                'average_heartrate' => data_get($details, 'average_heartrate'),
                'max_heartrate' => data_get($details, 'max_heartrate'),
                'average_cadence' => data_get($details, 'average_cadence'),
                'elevation_gain_m' => data_get($details, 'total_elevation_gain', $activity->total_elevation_gain),
                'split_count' => is_array($splits) ? count($splits) : 0,
                'first_split_pace' => $this->extractSplitPace($splits, 0, $api),
                'last_split_pace' => $this->extractSplitPace($splits, -1, $api),
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

    private function normalizeAiAnalysis(array $decoded): array
    {
        return [
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
