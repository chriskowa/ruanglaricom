<?php

namespace App\Http\Controllers;

use App\Services\StravaApiService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Event;

class CalendarController extends Controller
{
    public function index()
    {
        return view('calendar.index');
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
                        'source' => 'events'
                    ];
                });

            return response()->json($events);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function stravaConnect(Request $request)
    {
        $clientId = \App\Models\Admin\StravaConfig::first()->client_id ?? env('STRAVA_CLIENT_ID');

        $returnTo = $request->string('return_to')->toString();
        if ($returnTo !== '' && str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//')) {
            session(['strava_return_to' => $returnTo]);
        } else {
            session()->forget('strava_return_to');
        }
        
        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => route('calendar.strava.callback'),
            'response_type' => 'code',
            'scope' => 'activity:read_all,profile:read_all,activity:write',
            'approval_prompt' => 'auto',
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
                if (auth()->check()) {
                    $user = auth()->user();
                    $user->update([
                        'strava_id' => $tokenData['athlete']['id'] ?? null,
                        'strava_access_token' => $tokenData['access_token'],
                        'strava_refresh_token' => $tokenData['refresh_token'],
                        'strava_expires_at' => now()->addSeconds($tokenData['expires_in']),
                    ]);
                }

                Cache::forget('strava_club_leaderboard');

                $pendingKey = session()->pull('strava_pending_upload_key');
                if ($pendingKey) {
                    $payload = Cache::pull($pendingKey);
                    if (is_array($payload)) {
                        try {
                            $uploadResult = $this->uploadPointsToStrava($request->user(), $payload, $payload['start_at'] ?? null);
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
        $payload = $this->parseRoutePostPayload($request);
        $startAt = $payload['start_at'] ?? null;
        unset($payload['start_at']);

        $result = $this->uploadPointsToStrava($request->user(), $payload, $startAt);

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
        $payload = $this->parseRoutePostPayload($request);
        $startAt = $payload['start_at'] ?? null;
        unset($payload['start_at']);

        $user = $request->user();
        $hasToken = (bool) $strava->getValidAccessToken($user);
        if ($hasToken) {
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
        if (! is_numeric($paceSec)) {
            $paceText = trim((string) $request->input('pace_text', ''));
            $paceSec = $this->parsePaceTextToSec($paceText);
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
            'points' => $rawPoints,
        ];

        $v = Validator::make($data, [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'activity_type' => 'nullable|string|in:run,trailrun,walk,ride',
            'private' => 'nullable|boolean',
            'pace_sec_per_km' => 'nullable|integer|min:1|max:3600',
            'start_at' => 'nullable|date',
            'device' => 'nullable|string|max:50',
            'hr' => 'nullable|integer|min:30|max:250',
            'cadence' => 'nullable|integer|min:60|max:300',
            'power' => 'nullable|integer|min:0|max:2000',
            'points' => 'required|array|min:2|max:8000',
            'points.*.lat' => 'required|numeric|between:-90,90',
            'points.*.lng' => 'required|numeric|between:-180,180',
        ]);
        $validated = $v->validate();

        $startAt = null;
        if (! empty($validated['start_at'])) {
            $startAt = Carbon::parse($validated['start_at'], config('app.timezone'))->utc();
        }

        $description = trim((string) ($validated['description'] ?? ''));
        $extras = [];
        if (! empty($validated['device'])) $extras[] = 'Device: '.$validated['device'];
        if (! empty($validated['hr'])) $extras[] = 'Avg HR: '.$validated['hr'].' bpm';
        if (! empty($validated['cadence'])) $extras[] = 'Avg Cadence: '.$validated['cadence'].' spm';
        if (! empty($validated['power'])) $extras[] = 'Avg Power: '.$validated['power'].' W';
        if (! empty($validated['pace_sec_per_km'])) $extras[] = 'Target Pace: '.$this->formatPaceSec((int) $validated['pace_sec_per_km']).'/km';
        if (! empty($extras)) {
            $description = trim($description."\n\n".implode(' • ', $extras));
        }

        return [
            'name' => $validated['name'] ?? null,
            'description' => $description,
            'activity_type' => $validated['activity_type'] ?? null,
            'private' => (bool) ($validated['private'] ?? false),
            'pace_sec_per_km' => $validated['pace_sec_per_km'] ?? null,
            'points' => $validated['points'],
            'start_at' => $startAt,
        ];
    }

    private function uploadPointsToStrava($user, array $data, ?Carbon $startAt): array
    {
        $strava = app(StravaApiService::class);
        $accessToken = $strava->getValidAccessToken($user);
        if (! $accessToken) {
            return [
                'ok' => false,
                'message' => 'Strava belum tersambung. Silakan connect dulu.',
            ];
        }

        $name = trim((string) ($data['name'] ?? 'RuangLari Route'));
        if ($name === '') {
            $name = 'RuangLari Route';
        }

        $activityType = strtolower((string) ($data['activity_type'] ?? 'run'));
        $paceSecPerKm = isset($data['pace_sec_per_km']) ? (int) $data['pace_sec_per_km'] : null;

        $points = array_map(function ($p) {
            return [
                'lat' => (float) $p['lat'],
                'lng' => (float) $p['lng'],
            ];
        }, $data['points'] ?? []);

        $distanceKm = 0.0;
        for ($i = 1; $i < count($points); $i++) {
            $distanceKm += $this->haversineKm($points[$i - 1]['lat'], $points[$i - 1]['lng'], $points[$i]['lat'], $points[$i]['lng']);
        }

        $elapsedSec = null;
        if ($paceSecPerKm && $distanceKm > 0) {
            $elapsedSec = (int) max(1, round($distanceKm * $paceSecPerKm));
        }

        $gpx = $this->buildGpxFromPoints($name, $points, $elapsedSec, $startAt);

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
            $message = 'Gagal upload ke Strava.';
            if ($status === 401 || $status === 403) {
                $message = 'Strava token tidak valid / belum punya izin write. Silakan connect ulang Strava.';
            }

            return [
                'ok' => false,
                'message' => $message,
                'upstream_status' => $status,
                'upstream' => $res->json() ?? $res->body(),
            ];
        }

        return [
            'ok' => true,
            'message' => 'Upload dikirim ke Strava. Prosesnya bisa butuh beberapa saat.',
            'distance_km' => $distanceKm,
            'elapsed_time_s' => $elapsedSec,
            'upload' => $res->json(),
        ];
    }

    private function buildGpxFromPoints(string $name, array $points, ?int $elapsedSec, ?Carbon $startAt): string
    {
        $startedAt = $startAt ? $startAt->copy() : now()->utc();
        $totalPoints = count($points);
        $step = null;
        if ($elapsedSec && $totalPoints > 1) {
            $step = $elapsedSec / ($totalPoints - 1);
        }

        $trkpts = '';
        foreach ($points as $idx => $p) {
            $t = $startedAt;
            if ($step !== null) {
                $t = $startedAt->copy()->addSeconds((int) round($idx * $step));
            }
            $trkpts .= '<trkpt lat="'.$this->fmtCoord($p['lat']).'" lon="'.$this->fmtCoord($p['lng']).'"><time>'.$t->toAtomString().'</time></trkpt>';
        }

        $safeName = $this->escapeXml($name);
        $time = $startedAt->toAtomString();

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<gpx version="1.1" creator="RuangLari" xmlns="http://www.topografix.com/GPX/1/1">'
            .'<metadata><name>'.$safeName.'</name><time>'.$time.'</time></metadata>'
            .'<trk><name>'.$safeName.'</name><trkseg>'.$trkpts.'</trkseg></trk>'
            .'</gpx>';
    }

    private function parsePaceTextToSec(string $pace): ?int
    {
        if ($pace === '') return null;
        if (! preg_match('/^(\d{1,2}):([0-5]\d)$/', $pace, $m)) return null;
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
}
