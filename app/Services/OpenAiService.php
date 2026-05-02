<?php

namespace App\Services;

use App\Models\StravaActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    protected $apiKey;
    protected $chatCompletionsUrl = 'https://api.openai.com/v1/chat/completions';
    protected $responsesUrl = 'https://api.openai.com/v1/responses';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');
    }

    /**
     * Generic method to get AI response.
     */
    public function getAiResponse(string $prompt, string $systemMessage = 'You are a helpful assistant.', string $model = 'gpt-4o'): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API Key is not set.');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post($this->chatCompletionsUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::error('OpenAI API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function getChatResponse(array $messages, string $model = 'gpt-4o'): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API Key is not set.');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post($this->chatCompletionsUrl, [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::error('OpenAI API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function getAiResponseOrThrow(string $prompt, string $systemMessage = 'You are a helpful assistant.', ?string $model = null): string
    {
        $model = $model ?: (config('services.openai.model') ?: 'gpt-4o');
        $endpoint = config('services.openai.endpoint') ?: 'responses';

        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenAI API Key is not set.');
        }

        if ($endpoint === 'chat_completions') {
            $payload = [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(90)->post($this->chatCompletionsUrl, $payload);

            if (! $response->successful() && $this->isUnsupportedTemperatureError($response) && array_key_exists('temperature', $payload)) {
                unset($payload['temperature']);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout(90)->post($this->chatCompletionsUrl, $payload);
            }

            if (! $response->successful()) {
                $message = null;
                $json = $response->json();
                if (is_array($json)) {
                    $message = data_get($json, 'error.message') ?: data_get($json, 'message');
                }
                $message = $message ?: trim((string) $response->body());
                $message = mb_substr($message, 0, 800);
                throw new \RuntimeException("OpenAI API error ({$response->status()}): {$message}");
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;
            if (! is_string($content) || trim($content) === '') {
                throw new \RuntimeException('OpenAI returned empty content.');
            }

            return $content;
        }

        $payload = [
            'model' => $model,
            'instructions' => $systemMessage,
            'input' => $prompt,
            'temperature' => 0.7,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(90)->post($this->responsesUrl, $payload);

        if (! $response->successful() && $this->isUnsupportedTemperatureError($response) && array_key_exists('temperature', $payload)) {
            unset($payload['temperature']);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(90)->post($this->responsesUrl, $payload);
        }

        if (! $response->successful()) {
            $message = null;
            $json = $response->json();
            if (is_array($json)) {
                $message = data_get($json, 'error.message') ?: data_get($json, 'message');
            }
            $message = $message ?: trim((string) $response->body());
            $message = mb_substr($message, 0, 800);
            throw new \RuntimeException("OpenAI API error ({$response->status()}): {$message}");
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new \RuntimeException('OpenAI returned invalid JSON.');
        }

        $content = $data['output_text'] ?? null;
        if (is_string($content) && trim($content) !== '') {
            return $content;
        }

        $out = $data['output'] ?? null;
        if (is_array($out)) {
            $texts = [];
            foreach ($out as $item) {
                $contentArr = $item['content'] ?? null;
                if (! is_array($contentArr)) continue;
                foreach ($contentArr as $part) {
                    if (! is_array($part)) continue;
                    $type = $part['type'] ?? null;
                    if ($type === 'output_text' || $type === 'text') {
                        $t = $part['text'] ?? null;
                        if (is_string($t) && $t !== '') $texts[] = $t;
                    }
                }
            }
            if ($texts) {
                return trim(implode("\n", $texts));
            }
        }

        throw new \RuntimeException('OpenAI returned empty content.');
    }

    private function isUnsupportedTemperatureError($response): bool
    {
        $json = $response->json();
        if (! is_array($json)) {
            return str_contains((string) $response->body(), "Unsupported parameter: 'temperature'")
                || str_contains((string) $response->body(), 'temperature is not supported');
        }

        $message = data_get($json, 'error.message') ?: data_get($json, 'message') ?: '';
        if (! is_string($message)) {
            $message = '';
        }

        return str_contains($message, "Unsupported parameter: 'temperature'")
            || str_contains($message, 'temperature is not supported');
    }

    /**
     * Get response from AI Coach based on runner profile and message.
     */
    public function getCoachResponse(User $runner, string $userMessage, array $history = []): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API Key is not set.');
            return 'Maaf, layanan AI Coach sedang tidak tersedia saat ini (API Key belum dikonfigurasi).';
        }

        // Get runner profile data
        $profileService = app(RunningProfileService::class);
        $profileData = $profileService->getProfile($runner);

        $systemPrompt = $this->buildSystemPrompt($runner, $profileData);

        $messages = array_merge(
            [
                ['role' => 'system', 'content' => $systemPrompt],
            ],
            $this->normalizeChatHistory($history),
            [
                ['role' => 'user', 'content' => $userMessage],
            ]
        );

        $response = $this->getChatResponse($messages, 'gpt-4o');
        
        return $response ?: 'Maaf, Coach sedang beristirahat sejenak. Silakan coba lagi nanti.';
    }

    /**
     * Build a personalized system prompt for the AI Coach.
     */
    protected function buildSystemPrompt(User $user, array $profile): string
    {
        $pb = $profile['pb'] ?? [];
        $paces = $profile['paces'] ?? [];
        $recent = $this->buildRecentTrainingSummary($user);
        
        $prompt = "Anda adalah 'Coach AI' dari Ruang Lari, asisten lari profesional yang ahli, ramah, dan memotivasi. ";
        $prompt .= "Konteks chat harus selalu seputar dunia lari (teknik, nutrisi, recovery, jadwal latihan, sepatu, apparel lari, dll). ";
        $prompt .= "Jika user bertanya di luar konteks lari, jawab dengan sopan bahwa Anda hanya fokus pada perkembangan lari mereka.\n\n";
        $prompt .= "Gaya jawaban:\n";
        $prompt .= "- Selalu mulai dengan 1-2 kalimat ringkasan situasi pelari (berdasarkan data yang tersedia).\n";
        $prompt .= "- Tanyakan maksimal 2 pertanyaan klarifikasi jika data kurang.\n";
        $prompt .= "- Berikan rekomendasi yang actionable (contoh: durasi, pace/effort, tujuan sesi, recovery).\n";
        $prompt .= "- Jika user minta rencana, berikan rencana 7 hari yang realistis dan mudah diikuti.\n";
        $prompt .= "- Jangan mengarang metrik yang tidak ada. Jika data latihan kosong, bilang tidak ada data latihan.\n\n";
        
        $prompt .= "Profil Pelari:\n";
        $prompt .= "- Nama: {$user->name}\n";
        $prompt .= "- Gender: " . ($user->gender ?? 'Tidak disebutkan') . "\n";
        $prompt .= "- Berat/Tinggi: " . ($user->weight_kg ?? '-') . "kg / " . ($user->height_cm ?? '-') . "cm\n";
        
        if (!empty($pb)) {
            $prompt .= "- Personal Best: 5K ({$pb['5k']}), 10K ({$pb['10k']}), HM ({$pb['hm']}), FM ({$pb['fm']})\n";
        }
        
        if (isset($profile['vdot']) && $profile['vdot']) {
            $prompt .= "- Estimasi VDOT: " . round($profile['vdot'], 2) . "\n";
        }

        if (!empty($paces)) {
            $prompt .= "- Rekomendasi Pace Latihan: Easy (" . ($paces['easy'] ?? '-') . "), Tempo (" . ($paces['threshold'] ?? '-') . "), Interval (" . ($paces['interval'] ?? '-') . ")\n";
        }

        if (! empty($profile['weekly_km_target'])) {
            $prompt .= "- Target KM Mingguan: {$profile['weekly_km_target']} km\n";
        }

        $prompt .= "\nRingkasan Latihan Terakhir:\n";
        $prompt .= $this->formatRecentTrainingForPrompt($recent);

        $prompt .= "\nGunakan Bahasa Indonesia yang kasual namun profesional (gaya 'Coach Gaul'). Berikan saran yang spesifik berdasarkan data profil & ringkasan latihan di atas jika memungkinkan.";
        
        return $prompt;
    }

    protected function normalizeChatHistory(array $history): array
    {
        $out = [];
        foreach ($history as $item) {
            if (! is_array($item)) continue;
            $role = $item['role'] ?? null;
            $content = $item['content'] ?? null;
            if (! in_array($role, ['user', 'assistant'], true)) continue;
            if (! is_string($content) || trim($content) === '') continue;
            $out[] = ['role' => $role, 'content' => $content];
        }
        return $out;
    }

    protected function buildRecentTrainingSummary(User $user): array
    {
        $now = Carbon::now();
        $start14 = $now->copy()->subDays(14);
        $start7 = $now->copy()->subDays(7);

        $activities = StravaActivity::query()
            ->where('user_id', $user->id)
            ->whereNotNull('start_date')
            ->whereBetween('start_date', [$start14, $now])
            ->orderByDesc('start_date')
            ->limit(30)
            ->get();

        $runTypes = ['run', 'virtualrun', 'trailrun', 'treadmill'];
        $runs = $activities->filter(function (StravaActivity $a) use ($runTypes) {
            $t = strtolower((string) ($a->type ?? ''));
            return in_array($t, $runTypes, true);
        })->values();

        $sumKm14 = round($runs->sum(fn (StravaActivity $a) => ((float) ($a->distance_m ?? 0)) / 1000), 2);
        $sumKm7 = round($runs->filter(fn (StravaActivity $a) => $a->start_date && $a->start_date->gte($start7))
            ->sum(fn (StravaActivity $a) => ((float) ($a->distance_m ?? 0)) / 1000), 2);

        $longest = $runs->sortByDesc('distance_m')->first();
        $longestKm = $longest ? round(((float) ($longest->distance_m ?? 0)) / 1000, 2) : 0;

        $recentRuns = $runs->take(6)->map(function (StravaActivity $a) {
            $km = ((float) ($a->distance_m ?? 0)) / 1000;
            $pace = null;
            if ($km > 0 && (int) ($a->moving_time_s ?? 0) > 0) {
                $pace = ($a->moving_time_s / $km);
            }
            return [
                'date' => $a->local_start_date?->toDateString() ?: $a->start_date?->toDateString(),
                'name' => $a->name,
                'type' => $a->type,
                'distance_km' => round($km, 2),
                'moving_time_min' => round(((int) ($a->moving_time_s ?? 0)) / 60, 1),
                'avg_pace' => $pace ? $this->formatPaceFromSeconds((float) $pace) : null,
                'avg_hr' => data_get(is_array($a->raw) ? $a->raw : [], 'details.average_heartrate'),
            ];
        })->values()->all();

        return [
            'has_strava_data' => $activities->isNotEmpty(),
            'lookback_days' => 14,
            'run_count_14d' => $runs->count(),
            'total_km_7d' => $sumKm7,
            'total_km_14d' => $sumKm14,
            'longest_run_km_14d' => $longestKm,
            'recent_runs' => $recentRuns,
        ];
    }

    protected function formatRecentTrainingForPrompt(array $recent): string
    {
        if (($recent['has_strava_data'] ?? false) !== true) {
            return "- Data latihan tidak tersedia (Strava belum tersambung / belum ada aktivitas).\n";
        }

        $out = '';
        $out .= "- Total 7 hari: " . ($recent['total_km_7d'] ?? 0) . " km\n";
        $out .= "- Total 14 hari: " . ($recent['total_km_14d'] ?? 0) . " km\n";
        $out .= "- Jumlah lari 14 hari: " . ($recent['run_count_14d'] ?? 0) . " sesi\n";
        $out .= "- Long run 14 hari: " . ($recent['longest_run_km_14d'] ?? 0) . " km\n";

        $runs = $recent['recent_runs'] ?? [];
        if (is_array($runs) && ! empty($runs)) {
            $out .= "- Aktivitas terbaru:\n";
            foreach ($runs as $r) {
                if (! is_array($r)) continue;
                $date = $r['date'] ?? '-';
                $km = $r['distance_km'] ?? 0;
                $pace = $r['avg_pace'] ?? null;
                $hr = $r['avg_hr'] ?? null;
                $line = "  - {$date}: {$km} km";
                if ($pace) $line .= " @ {$pace}/km";
                if ($hr) $line .= " (avg HR {$hr})";
                $out .= $line . "\n";
            }
        }

        return $out;
    }

    protected function formatPaceFromSeconds(float $secondsPerKm): string
    {
        if ($secondsPerKm <= 0) return '-';
        $total = (int) round($secondsPerKm);
        $m = intdiv($total, 60);
        $s = $total % 60;
        return sprintf('%d:%02d', $m, $s);
    }
}
