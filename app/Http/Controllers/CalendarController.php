<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\StravaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $stravaToken = null;
        if (auth()->check()) {
            $stravaToken = app(StravaApiService::class)->getValidAccessToken(auth()->user());
        }

        $embed = $request->boolean('embed');

        return view('calendar.index', [
            'stravaToken' => $stravaToken,
            'hideNav' => $embed,
            'hideFooter' => $embed,
            'hideChat' => $embed,
            'needCalendar' => true,
        ]);
    }

    public function getAiAnalysis(Request $request)
    {
        $data = $request->input('data');

        $prompt = 'You are a professional running coach. Analyze the following weekly running data: '.json_encode($data).". Provide a concise summary of performance and 1 specific actionable tip for next week. Keep it under 100 words. Speak in Bahasa Indonesia style 'Coach Gaul'.";

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=AIzaSyBkGYYIr1MPrbqQsBijXb9s_w8gQ--Lx_w', [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI Analysis Failed', ['status' => $response->status(), 'body' => $response->body()]);

            return response()->json([
                'error' => 'AI Service Unavailable',
                'details' => $response->json() ?? $response->body(),
                'upstream_status' => $response->status(),
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('AI Analysis Exception', ['message' => $e->getMessage()]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getEvents()
    {
        try {
            // Fetch from unified Event table (Local API)
            $events = Event::select('id', 'name', 'start_at', 'slug', 'location_name')
                ->where('status', 'published')
                ->orderBy('start_at', 'desc')
                ->get()
                ->map(function ($ev) {
                    return [
                        'id' => $ev->id,
                        'name' => $ev->name,
                        'start_at' => $ev->start_at,
                        'slug' => $ev->slug,
                        'location_name' => $ev->location_name,
                        'source' => 'events',
                    ];
                });

            if (auth()->check()) {
                $userActs = \App\Models\UserActivity::where('user_id', auth()->id())
                    ->orderBy('start_time', 'desc')
                    ->get()
                    ->map(function ($act) {
                        return [
                            'id' => $act->id,
                            'name' => '🏃 ' . $act->title . ' (' . number_format((float)$act->distance_km, 2) . ' km)',
                            'start_at' => $act->start_time ? $act->start_time->format('Y-m-d H:i:s') : $act->created_at->format('Y-m-d H:i:s'),
                            'slug' => 'activity-' . $act->id,
                            'location_name' => $act->formatted_avg_pace . ' • ' . $act->formatted_moving_time,
                            'source' => 'user_activity',
                            'activity_id' => $act->id,
                            'distance_km' => $act->distance_km,
                            'pace' => $act->formatted_avg_pace,
                            'duration' => $act->formatted_moving_time,
                            'gain' => $act->elevation_gain_m,
                            'url' => route('activities.show', $act->id),
                        ];
                    });
                $events = $events->concat($userActs);
            }

            return response()->json($events);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stravaConnect(Request $request)
    {
        $returnTo = $request->string('return_to')->toString();
        if ($returnTo === '' || ! str_starts_with($returnTo, '/') || str_starts_with($returnTo, '//')) {
            $returnTo = route('calendar.public').'#strava';
        }

        // If user is already authenticated and has a valid Strava token, don't force re-authorization unless force is requested
        $force = $request->boolean('force');
        if (! $force && auth()->check()) {
            $validToken = app(StravaApiService::class)->getValidAccessToken(auth()->user());
            if ($validToken) {
                return redirect($returnTo)->with('success', 'Akun Strava Anda sudah terhubung.');
            }
        }

        session(['strava_return_to' => $returnTo]);

        $clientId = \App\Models\Admin\StravaConfig::first()->client_id ?? env('STRAVA_CLIENT_ID');

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('calendar.strava.callback'),
            'response_type' => 'code',
            'scope' => 'read,activity:read_all,profile:read_all,activity:write',
            'approval_prompt' => 'force',
        ]);

        return redirect('https://www.strava.com/oauth/authorize?'.$query);
    }

    public function stravaCallback(Request $request)
    {
        $returnTo = session()->pull('strava_return_to') ?: (route('calendar.public').'#strava');

        if (! $request->has('code')) {
            return redirect($returnTo)->with('error', 'Authorization failed');
        }

        try {
            $config = \App\Models\Admin\StravaConfig::first();
            $clientId = $config->client_id ?? env('STRAVA_CLIENT_ID');
            $clientSecret = $config->client_secret ?? env('STRAVA_CLIENT_SECRET');

            $response = Http::withoutVerifying()->post('https://www.strava.com/oauth/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $request->code,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();

                // Save to Authenticated User (if logged in)
                $user = auth()->user() ?? $request->user();
                if ($user) {
                    $user->update([
                        'strava_id' => $tokenData['athlete']['id'] ?? null,
                        'strava_access_token' => $tokenData['access_token'],
                        'strava_refresh_token' => $tokenData['refresh_token'],
                        'strava_expires_at' => now()->addSeconds($tokenData['expires_in']),
                    ]);
                    $user->refresh();
                }

                Cache::forget('strava_club_leaderboard');

                $pendingKey = session()->pull('strava_pending_upload_key');
                if ($pendingKey) {
                    $payload = Cache::pull($pendingKey);
                    if (is_array($payload) && $user) {
                        try {
                            $uploadResult = $this->uploadPointsToStrava($user, $payload, $payload['start_at'] ?? null);
                            if (data_get($uploadResult, 'ok')) {
                                session()->flash('success', data_get($uploadResult, 'message', 'Aktivitas berhasil dikirim ke Strava.'));
                            } else {
                                session()->flash('error', data_get($uploadResult, 'message', 'Gagal mengirim aktivitas ke Strava.'));
                            }
                        } catch (\Throwable $e) {
                            session()->flash('error', 'Gagal mengirim aktivitas ke Strava: '.$e->getMessage());
                        }
                    }
                }

                // Return a view that saves to localStorage and closes/redirects
                return view('calendar.strava-callback', [
                    'tokenData' => $tokenData,
                    'redirectTo' => $returnTo,
                ]);
            }

            $errorMessage = 'Token exchange failed';
            $body = $response->json();
            if (isset($body['message'])) {
                $errorMessage .= ': '.$body['message'];
            }
            if (isset($body['errors'])) {
                $errorMessage .= ' ('.json_encode($body['errors']).')';
            }

            return redirect($returnTo)->with('error', $errorMessage);

        } catch (\Exception $e) {
            return redirect($returnTo)->with('error', 'Connection error: '.$e->getMessage());
        }
    }

    public function uploadRouteToStrava(Request $request, StravaApiService $strava)
    {
        $user = $request->user() ?? auth()->user();
        if (! $user) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
            }
            return redirect()->route('login');
        }

        $payload = $this->parseRoutePostPayload($request);
        $startAt = $payload['start_at'] ?? null;
        unset($payload['start_at']);

        $validToken = $strava->getValidAccessToken($user);
        if (! $validToken) {
            if ($request->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'need_reconnect' => true,
                    'message' => 'Koneksi Strava tidak valid atau telah kedaluwarsa. Silakan hubungkan ulang akun Strava.',
                    'connect_url' => route('calendar.strava.connect', ['force' => 1, 'return_to' => '/tools/buat-rute-lari#strava-form-panel']),
                ], 401);
            }

            $key = 'rl_strava_pending_upload_'.bin2hex(random_bytes(16));
            $payload['start_at'] = $startAt;
            Cache::put($key, $payload, now()->addMinutes(15));
            session(['strava_pending_upload_key' => $key]);

            return redirect()->route('calendar.strava.connect', [
                'force' => 1,
                'return_to' => '/tools/buat-rute-lari#strava-form-panel',
            ]);
        }

        $result = $this->uploadPointsToStrava($user, $payload, $startAt);

        if ($request->wantsJson()) {
            return response()->json($result, ($result['ok'] ?? false) ? 200 : 422);
        }

        if ($result['ok'] ?? false) {
            return redirect()->route('tools.buat-rute-lari')->with('success', $result['message'] ?? 'Upload dikirim ke Strava.');
        }

        return redirect()->route('tools.buat-rute-lari')->with('error', $result['message'] ?? 'Gagal upload ke Strava.');
    }

    public function authorizeAndPostRouteToStrava(Request $request, StravaApiService $strava)
    {
        $user = $request->user() ?? auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $payload = $this->parseRoutePostPayload($request);
        $startAt = $payload['start_at'] ?? null;
        unset($payload['start_at']);

        $validToken = $strava->getValidAccessToken($user);
        if ($validToken) {
            $result = $this->uploadPointsToStrava($user, $payload, $startAt);
            if ($result['ok'] ?? false) {
                return redirect()->route('tools.buat-rute-lari')->with('success', $result['message'] ?? 'Upload dikirim ke Strava.');
            }

            return redirect()->route('tools.buat-rute-lari')->with('error', $result['message'] ?? 'Gagal upload ke Strava.');
        }

        $key = 'rl_strava_pending_upload_'.bin2hex(random_bytes(16));
        $payload['start_at'] = $startAt;
        Cache::put($key, $payload, now()->addMinutes(15));
        session(['strava_pending_upload_key' => $key]);

        return redirect()->route('calendar.strava.connect', [
            'force' => 1,
            'return_to' => '/tools/buat-rute-lari#strava-form-panel',
        ]);
    }

    private function parseRoutePostPayload(Request $request): array
    {
        $rawPoints = $request->input('points');
        if (! is_array($rawPoints)) {
            $rawJson = (string) $request->input('points_json', '');
            $decoded = $rawJson !== '' ? json_decode($rawJson, true) : null;
            $rawPoints = is_array($decoded) ? $decoded : null;
        }

        $paceSec = $request->input('pace_sec_per_km');
        if (! is_numeric($paceSec) || (int) $paceSec <= 0) {
            $paceText = trim((string) $request->input('pace_text', ''));
            $paceSec = $this->parsePaceTextToSec($paceText) ?: 360;
        } else {
            $paceSec = (int) $paceSec;
        }

        $data = [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'activity_type' => $request->input('activity_type'),
            'private' => $request->boolean('private'),
            'pace_sec_per_km' => $paceSec,
            'start_at' => $request->input('start_at'),
            'device' => $request->input('device'),
            'hr' => $request->input('hr'),
            'cadence' => $request->input('cadence'),
            'power' => $request->input('power'),
            'split_type' => $request->input('split_type', 'even'),
            'split_pct' => $request->input('split_pct', 2.0),
            'points' => $rawPoints,
        ];

        $v = Validator::make($data, [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'activity_type' => 'nullable|string|in:run,trailrun,walk,ride',
            'private' => 'nullable|boolean',
            'pace_sec_per_km' => 'nullable|integer|min:1|max:3600',
            'start_at' => 'nullable|date',
            'device' => 'nullable|string|max:100',
            'hr' => 'nullable|integer|min:30|max:250',
            'cadence' => 'nullable|integer|min:60|max:300',
            'power' => 'nullable|integer|min:0|max:2000',
            'split_type' => 'nullable|string|in:even,negative,positive',
            'split_pct' => 'nullable|numeric|min:0.1|max:15',
            'points' => 'required|array|min:2|max:8000',
            'points.*.lat' => 'required|numeric|between:-90,90',
            'points.*.lng' => 'required|numeric|between:-180,180',
        ]);
        $validated = $v->validate();

        $startAt = null;
        if (! empty($validated['start_at'])) {
            try {
                $startAt = Carbon::parse($validated['start_at'], config('app.timezone'))->utc();
            } catch (\Throwable $e) {
                $startAt = null;
            }
        }

        $splitType = strtolower(trim((string) ($validated['split_type'] ?? 'even')));
        $splitType = in_array($splitType, ['even', 'negative', 'positive'], true) ? $splitType : 'even';
        $splitPct = isset($validated['split_pct']) && is_numeric($validated['split_pct']) ? (float) $validated['split_pct'] : 2.0;
        $splitPct = max(0.1, min(15.0, $splitPct));

        $description = trim((string) ($validated['description'] ?? ''));
        $extras = [];
        if (! empty($validated['device'])) {
            $extras[] = 'Device: '.$validated['device'];
        }
        if (! empty($validated['hr'])) {
            $extras[] = 'Avg HR: '.$validated['hr'].' bpm';
        }
        if (! empty($validated['cadence'])) {
            $extras[] = 'Avg Cadence: '.$validated['cadence'].' spm';
        }
        if (! empty($validated['power'])) {
            $extras[] = 'Avg Power: '.$validated['power'].' W';
        }
        if (! empty($validated['pace_sec_per_km'])) {
            $extras[] = 'Target Pace: '.$this->formatPaceSec((int) $validated['pace_sec_per_km']).'/km';
        }
        if ($splitType === 'negative') {
            $extras[] = 'Split: Negative Split (+'.number_format($splitPct, 1).' %/km)';
        } elseif ($splitType === 'positive') {
            $extras[] = 'Split: Positive Split (-'.number_format($splitPct, 1).' %/km)';
        }
        if (! empty($extras)) {
            $description = trim($description."\n\n".implode(' • ', $extras));
        }

        return [
            'name' => $validated['name'] ?? null,
            'description' => $description,
            'activity_type' => $validated['activity_type'] ?? null,
            'private' => (bool) ($validated['private'] ?? false),
            'pace_sec_per_km' => $validated['pace_sec_per_km'] ?? 360,
            'device' => ! empty($validated['device']) ? trim((string) $validated['device']) : null,
            'hr' => ! empty($validated['hr']) ? (int) $validated['hr'] : null,
            'cadence' => ! empty($validated['cadence']) ? (int) $validated['cadence'] : null,
            'power' => ! empty($validated['power']) ? (int) $validated['power'] : null,
            'split_type' => $splitType,
            'split_pct' => $splitPct,
            'points' => $validated['points'],
            'start_at' => $startAt,
        ];
    }

    private function uploadPointsToStrava($user, array $data, $startAt): array
    {
        $strava = app(StravaApiService::class);
        $accessToken = $strava->getValidAccessToken($user);
        if (! $accessToken) {
            return [
                'ok' => false,
                'message' => 'Strava belum tersambung atau izin kedaluwarsa. Silakan hubungkan ulang akun Strava.',
            ];
        }

        if (is_string($startAt)) {
            try {
                $startAt = Carbon::parse($startAt);
            } catch (\Throwable $e) {
                $startAt = null;
            }
        }

        $name = trim((string) ($data['name'] ?? 'RuangLari Route'));
        if ($name === '') {
            $name = 'RuangLari Route';
        }

        $activityType = strtolower((string) ($data['activity_type'] ?? 'run'));
        $paceSecPerKm = isset($data['pace_sec_per_km']) && (int) $data['pace_sec_per_km'] > 0 ? (int) $data['pace_sec_per_km'] : 360;

        $points = array_values(array_map(function ($p) {
            return [
                'lat' => (float) $p['lat'],
                'lng' => (float) $p['lng'],
            ];
        }, $data['points'] ?? []));

        if (count($points) < 2) {
            return [
                'ok' => false,
                'message' => 'Titik rute minimal harus ada 2 titik.',
            ];
        }

        $telemetry = [
            'hr' => isset($data['hr']) && (int) $data['hr'] > 0 ? (int) $data['hr'] : null,
            'cadence' => isset($data['cadence']) && (int) $data['cadence'] > 0 ? (int) $data['cadence'] : null,
            'power' => isset($data['power']) && (int) $data['power'] > 0 ? (int) $data['power'] : null,
        ];

        $device = ! empty($data['device']) ? trim((string) $data['device']) : null;
        $splitType = strtolower(trim((string) ($data['split_type'] ?? 'even')));
        $splitType = in_array($splitType, ['even', 'negative', 'positive'], true) ? $splitType : 'even';
        $splitPct = isset($data['split_pct']) && is_numeric($data['split_pct']) ? (float) $data['split_pct'] : 2.0;
        $splitPct = max(0.1, min(15.0, $splitPct));

        $gpx = $this->buildGpxFromPoints($name, $points, $paceSecPerKm, $startAt, $telemetry, $device, $activityType, $splitType, $splitPct);

        $externalId = 'rl-route-'.$user->id.'-'.now()->format('YmdHis').'.gpx';
        $payload = [
            'data_type' => 'gpx',
            'activity_type' => $activityType,
            'name' => $name,
            'description' => (string) ($data['description'] ?? ''),
            'external_id' => $externalId,
            'private' => ! empty($data['private']) ? 1 : 0,
        ];

        $res = Http::withoutVerifying()
            ->withToken($accessToken)
            ->attach('file', $gpx, $externalId)
            ->post('https://www.strava.com/api/v3/uploads', $payload);

        if (! $res->successful()) {
            $status = $res->status();
            $upstreamBody = $res->json() ?? $res->body();

            Log::error('Strava Upload Failed', [
                'user_id' => $user->id,
                'http_status' => $status,
                'upstream_body' => $upstreamBody,
                'token_prefix' => substr($accessToken, 0, 8) . '...',
                'gpx_length' => strlen($gpx),
                'points_count' => count($points),
            ]);

            $message = 'Gagal upload ke Strava (HTTP ' . $status . ').';

            if ($status === 401 || $status === 403) {
                $user->update([
                    'strava_access_token' => null,
                    'strava_refresh_token' => null,
                    'strava_expires_at' => null,
                ]);
                $message = 'Izin upload Strava tidak valid atau belum diberikan (scope activity:write). Silakan hubungkan ulang akun Strava.';
            } elseif ($status === 409) {
                $message = 'Aktivitas duplikat. Rute ini sudah pernah diupload ke Strava sebelumnya.';
            } elseif ($status === 429) {
                $message = 'Terlalu banyak request ke Strava (rate limit). Silakan coba lagi beberapa menit.';
            } else {
                $upstreamMsg = is_array($upstreamBody) ? ($upstreamBody['message'] ?? json_encode($upstreamBody)) : (string) $upstreamBody;
                $message .= ' Detail: ' . mb_substr($upstreamMsg, 0, 200);
            }

            return [
                'ok' => false,
                'message' => $message,
                'upstream_status' => $status,
                'upstream' => $upstreamBody,
            ];
        }

        return [
            'ok' => true,
            'message' => 'Upload berhasil dikirim ke Strava! Aktivitas sedang diproses oleh Strava.',
            'upload' => $res->json(),
        ];
    }

    private function generateTelemetrySeries(
        int $count,
        ?int $target,
        string $type,
        string $activityType = 'run',
        array $speedRatios = [],
        array $cumDists = [],
        float $totalDist = 0.0
    ): array {
        if (! $target || $count < 1) {
            return array_fill(0, $count, null);
        }

        // In GPX TrackPointExtension for running/walking, Strava treats <gpxtpx:cad> as RPM (single-leg strides)
        // and automatically multiplies it by 2 to display SPM. If target is in SPM (>= 110) for non-ride activity,
        // convert to RPM (target / 2) so Strava displays the exact SPM entered.
        $effectiveTarget = ($type === 'cadence' && $activityType !== 'ride' && $target >= 110)
            ? ($target / 2.0)
            : (float) $target;

        if ($count === 1) {
            return [(int) round($effectiveTarget)];
        }

        $series = [];
        $warmupCount = ($type === 'hr') ? min(15, max(0, (int) round($count * 0.08))) : 0;

        for ($i = 0; $i < $count; $i++) {
            $r = $speedRatios[$i] ?? 1.0;
            $distProgress = ($totalDist > 0 && isset($cumDists[$i])) ? ($cumDists[$i] / $totalDist) : 0.0;

            if ($type === 'hr') {
                // Heart rate rises when speed is faster than average, and drops when slower
                $speedDelta = ($r - 1.0) * ($target * 0.65);
                $drift = $distProgress * 2.5; // Natural progressive cardiac drift
                $wave = sin($i * 0.15) * 1.5 + cos($i * 0.04) * 1.0;

                if ($warmupCount > 0 && $i < $warmupCount) {
                    $pct = $i / (float) $warmupCount;
                    $startHr = max(50, (int) round($target * 0.85));
                    $val = (int) round($startHr + ($target - $startHr) * $pct + $wave);
                } else {
                    $val = (int) round($target + $speedDelta + $drift + $wave);
                }
                $val = max(40, min(230, $val));
            } elseif ($type === 'cadence') {
                // Cadence (stride rate) rises slightly with faster speed (~25% sensitivity)
                $speedDeltaRpm = ($r - 1.0) * ($effectiveTarget * 0.25);
                $wave = sin($i * 0.25) * 1.0 + cos($i * 0.07) * 0.7;
                $val = (int) round($effectiveTarget + $speedDeltaRpm + $wave);
                $val = max(20, min(150, $val));
            } else { // power
                $wave = sin($i * 0.22) * 8.0 + cos($i * 0.05) * 5.0;
                $val = (int) round($target * pow($r, 1.5) + $wave);
                $val = max(0, min(1500, $val));
            }
            $series[] = $val;
        }

        // Calibrate so series average strictly matches user's specified target!
        $targetMean = ($type === 'cadence') ? $effectiveTarget : (float) $target;
        $desiredSum = (int) round($targetMean * $count);
        $diff = $desiredSum - array_sum($series);
        if ($diff !== 0) {
            $step = $diff > 0 ? 1 : -1;
            $absDiff = abs($diff);
            $startIdx = ($type === 'hr') ? min($warmupCount, $count - 1) : 0;
            $span = max(1, $count - $startIdx);
            for ($k = 0; $k < $absDiff; $k++) {
                $series[$startIdx + ($k % $span)] += $step;
            }
        }

        return $series;
    }

    private function buildGpxFromPoints(
        string $name,
        array $points,
        ?int $paceSecPerKm,
        $startAt,
        array $telemetry = [],
        ?string $device = null,
        string $activityType = 'run',
        string $splitType = 'even',
        float $splitPct = 2.0
    ): string {
        $effectivePace = ($paceSecPerKm && $paceSecPerKm > 0) ? $paceSecPerKm : 360;
        $count = count($points);

        if ($startAt !== null && ! ($startAt instanceof Carbon)) {
            try {
                $startAt = Carbon::parse($startAt);
            } catch (\Throwable $e) {
                $startAt = null;
            }
        }

        // Precompute segment distances & cumulative progress
        $segDists = [];
        $cumDists = [0.0];
        $totalDistKm = 0.0;

        for ($i = 1; $i < $count; $i++) {
            $d = $this->haversineKm(
                (float) $points[$i - 1]['lat'],
                (float) $points[$i - 1]['lng'],
                (float) $points[$i]['lat'],
                (float) $points[$i]['lng']
            );
            $segDists[] = $d;
            $totalDistKm += $d;
            $cumDists[] = $totalDistKm;
        }

        $midDistKm = $totalDistKm / 2.0;

        // Calculate speed ratios Ri for each point based on split strategy
        $speedRatios = [];
        $pointPaces = [];
        for ($i = 0; $i < $count; $i++) {
            $distFromMid = $cumDists[$i] - $midDistKm;
            $ratio = 1.0;
            if ($splitType === 'negative') {
                $ratio = 1.0 + ($distFromMid * ($splitPct / 100.0));
            } elseif ($splitType === 'positive') {
                $ratio = 1.0 - ($distFromMid * ($splitPct / 100.0));
            }
            $ratio = max(0.4, min(2.5, $ratio));
            $speedRatios[] = $ratio;
            $pointPaces[] = $effectivePace / $ratio;
        }

        // Segment durations with normalization to preserve exact desired total duration
        $rawDurations = [];
        $rawTotalSec = 0.0;
        for ($i = 1; $i < $count; $i++) {
            $avgPace = ($pointPaces[$i - 1] + $pointPaces[$i]) / 2.0;
            $sec = $segDists[$i - 1] * $avgPace;
            $rawDurations[] = $sec;
            $rawTotalSec += $sec;
        }

        $desiredTotalSec = $totalDistKm * $effectivePace;
        $corr = $rawTotalSec > 0 ? ($desiredTotalSec / $rawTotalSec) : 1.0;
        $segDurations = array_map(fn($s) => $s * $corr, $rawDurations);
        $totalDurationSec = $desiredTotalSec;

        $startedAt = $startAt ? $startAt->copy() : now()->utc();

        // Enforce activity finish time is in the past (Strava strictly rejects future timestamps)
        $nowUtc = now()->utc();
        $expectedEndAt = $startedAt->copy()->addSeconds((int) ceil($totalDurationSec));
        if ($expectedEndAt->gt($nowUtc)) {
            // End time is in the future; adjust startedAt so that it ended 2 minutes ago
            $startedAt = $nowUtc->copy()->subSeconds((int) ceil($totalDurationSec) + 120);
        }

        $hrSeries = $this->generateTelemetrySeries($count, $telemetry['hr'] ?? null, 'hr', $activityType, $speedRatios, $cumDists, $totalDistKm);
        $cadenceSeries = $this->generateTelemetrySeries($count, $telemetry['cadence'] ?? null, 'cadence', $activityType, $speedRatios, $cumDists, $totalDistKm);
        $powerSeries = $this->generateTelemetrySeries($count, $telemetry['power'] ?? null, 'power', $activityType, $speedRatios, $cumDists, $totalDistKm);
        $hasTelemetry = ($telemetry['hr'] ?? null) || ($telemetry['cadence'] ?? null) || ($telemetry['power'] ?? null);

        $accumulatedSeconds = 0.0;
        $trkpts = '';
        $lastLat = null;
        $lastLng = null;

        foreach ($points as $idx => $p) {
            $lat = (float) $p['lat'];
            $lng = (float) $p['lng'];

            if ($idx > 0) {
                $accumulatedSeconds += $segDurations[$idx - 1];
            }

            $t = $startedAt->copy()->addMilliseconds((int) ($accumulatedSeconds * 1000));

            $extContent = '';
            $tpxContent = '';
            if (isset($hrSeries[$idx]) && $hrSeries[$idx] !== null) {
                $tpxContent .= '<gpxtpx:hr>'.$hrSeries[$idx].'</gpxtpx:hr>';
            }
            if (isset($cadenceSeries[$idx]) && $cadenceSeries[$idx] !== null) {
                $tpxContent .= '<gpxtpx:cad>'.$cadenceSeries[$idx].'</gpxtpx:cad>';
            }
            if (isset($powerSeries[$idx]) && $powerSeries[$idx] !== null) {
                $tpxContent .= '<gpxtpx:watts>'.$powerSeries[$idx].'</gpxtpx:watts>';
            }
            if ($tpxContent !== '') {
                $extContent .= '<gpxtpx:TrackPointExtension>'.$tpxContent.'</gpxtpx:TrackPointExtension>';
            }
            if (isset($powerSeries[$idx]) && $powerSeries[$idx] !== null) {
                $extContent .= '<power>'.$powerSeries[$idx].'</power>';
            }

            $extXml = $extContent !== '' ? '<extensions>'.$extContent.'</extensions>' : '';

            $trkpts .= '<trkpt lat="'.$this->fmtCoord($lat).'" lon="'.$this->fmtCoord($lng).'">'
                .'<time>'.$t->format('Y-m-d\TH:i:s\Z').'</time>'
                .$extXml
                .'</trkpt>';

            $lastLat = $lat;
            $lastLng = $lng;
        }

        $safeName = $this->escapeXml($name);
        $time = $startedAt->format('Y-m-d\TH:i:s\Z');

        $creator = ! empty($device) ? $this->escapeXml($device) : 'RuangLari';
        $gpxAttrs = 'version="1.1" creator="'.$creator.'" xmlns="http://www.topografix.com/GPX/1/1"';
        if ($hasTelemetry) {
            $gpxAttrs .= ' xmlns:gpxtpx="http://www.garmin.com/xmlschemas/TrackPointExtension/v1"'
                .' xmlns:gpxx="http://www.garmin.com/xmlschemas/GpxExtensions/v3"'
                .' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
                .' xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd http://www.garmin.com/xmlschemas/TrackPointExtension/v1 http://www.garmin.com/xmlschemas/TrackPointExtensionv1.xsd"';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<gpx '.$gpxAttrs.'>'
            .'<metadata><name>'.$safeName.'</name><time>'.$time.'</time></metadata>'
            .'<trk><name>'.$safeName.'</name><trkseg>'.$trkpts.'</trkseg></trk>'
            .'</gpx>';
    }

    private function parsePaceTextToSec(string $pace): ?int
    {
        if ($pace === '') {
            return null;
        }
        if (! preg_match('/^(\d{1,2}):([0-5]\d)$/', $pace, $m)) {
            return null;
        }

        return ((int) $m[1] * 60) + (int) $m[2];
    }

    private function formatPaceSec(int $sec): string
    {
        $min = (int) floor($sec / 60);
        $s = $sec % 60;

        return $min.':'.str_pad((string) $s, 2, '0', STR_PAD_LEFT);
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a =
            sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    private function escapeXml(string $v): string
    {
        return str_replace(
            ['&', '<', '>', '"', "'"],
            ['&amp;', '&lt;', '&gt;', '&quot;', '&apos;'],
            $v
        );
    }

    private function fmtCoord(float $v): string
    {
        return number_format($v, 6, '.', '');
    }

    public function stravaDisconnect(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->update([
                'strava_id' => null,
                'strava_access_token' => null,
                'strava_refresh_token' => null,
                'strava_expires_at' => null,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Koneksi Strava berhasil diputuskan.']);
        }

        return back()->with('success', 'Koneksi Strava berhasil diputuskan.');
    }
}
