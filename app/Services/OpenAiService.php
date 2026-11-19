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
    protected int $timeout;
    protected $chatCompletionsUrl = 'https://api.openai.com/v1/chat/completions';
    protected $responsesUrl = 'https://api.openai.com/v1/responses';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key') ?: env('OPENAI_API_KEY');
        $this->timeout = (int) (config('services.openai.timeout') ?: env('OPENAI_TIMEOUT', 180));
        if ($this->timeout < 60) {
            $this->timeout = 180;
        }
    }

    /**
     * Generic method to get AI response.
     */
    public function getAiResponse(string $prompt, string $systemMessage = 'You are a helpful assistant.', string $model = 'gpt-6-astra'): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API Key is not set.');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout)->post($this->chatCompletionsUrl, [
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

    private function supportsTemperature(string $model): bool
    {
        return !preg_match('/^(gpt-5|gpt-6|o1|o3|o4)/i', trim($model));
    }

    public function getChatResponse(array $messages, string $model = 'gpt-6-astra', ?User $user = null): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API Key is not set.');
            return null;
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
        ];
        if ($this->supportsTemperature($model)) {
            $payload['temperature'] = 0.7;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout($this->timeout)->post($this->chatCompletionsUrl, $payload);

            if (! $response->successful() && $this->isUnsupportedTemperatureError($response) && array_key_exists('temperature', $payload)) {
                unset($payload['temperature']);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout($this->timeout)->post($this->chatCompletionsUrl, $payload);
            }

            if ($response->successful()) {
                $data = $response->json();
                app(AiCoachContinuityService::class)->recordTokenUsage($user, (int) data_get($data, 'usage.total_tokens', 0));
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
        $model = $model ?: (config('services.openai.blog_model') ?: config('services.openai.model') ?: 'gpt-6-astra');
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

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout($this->timeout)->post($this->chatCompletionsUrl, $payload);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error("OpenAI timeout ({$this->timeout}s) on chat_completions: " . $e->getMessage());
                throw new \RuntimeException("Koneksi ke OpenAI timed out setelah {$this->timeout} detik. Server OpenAI sedang memproses artikel panjang. Silakan coba kembali.", 0, $e);
            }

            if (! $response->successful() && $this->isUnsupportedTemperatureError($response) && array_key_exists('temperature', $payload)) {
                unset($payload['temperature']);
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->timeout($this->timeout)->post($this->chatCompletionsUrl, $payload);
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    Log::error("OpenAI timeout ({$this->timeout}s) on retry chat_completions: " . $e->getMessage());
                    throw new \RuntimeException("Koneksi ke OpenAI timed out setelah {$this->timeout} detik. Silakan coba kembali.", 0, $e);
                }
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

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout($this->timeout)->post($this->responsesUrl, $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("OpenAI timeout ({$this->timeout}s) on responses: " . $e->getMessage());
            throw new \RuntimeException("Koneksi ke OpenAI timed out setelah {$this->timeout} detik. Server OpenAI sedang memproses artikel panjang. Silakan coba kembali.", 0, $e);
        }

        if (! $response->successful() && $this->isUnsupportedTemperatureError($response) && array_key_exists('temperature', $payload)) {
            unset($payload['temperature']);
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout($this->timeout)->post($this->responsesUrl, $payload);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error("OpenAI timeout ({$this->timeout}s) on retry responses: " . $e->getMessage());
                throw new \RuntimeException("Koneksi ke OpenAI timed out setelah {$this->timeout} detik. Silakan coba kembali.", 0, $e);
            }
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
            return 'Maaf, layanan Coach Ruang Lari sedang tidak tersedia saat ini (API Key belum dikonfigurasi).';
        }

        $continuity = app(AiCoachContinuityService::class);
        if (! $continuity->hasRemainingQuota($runner)) {
            return 'Kuota konsultasi Coach Ruang Lari harian Anda sudah tercapai. Anda tetap dapat mengikuti jadwal di Kalender Dashboard dan melanjutkan tanya-jawab besok.';
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

        $chatModel = config('services.openai.chat_model') ?: config('services.openai.model') ?: 'gpt-4.1';
        $response = $this->getChatResponse($messages, $chatModel, $runner);
        
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
        $activeProgramCtx = app(AiCoachContinuityService::class)->buildActiveProgramPromptContext($user);
        
        $prompt = "Anda adalah 'Coach Ruang Lari', pelatih lari profesional di platform Ruang Lari yang ahli, terstruktur, dan peduli keberlanjutan latihan atlet (anti junk miles, recovery presisi, dan progres bertahap). ";
        $prompt .= "Konteks chat harus selalu seputar dunia lari (teknik, nutrisi, recovery, penyesuaian jadwal latihan, sepatu, dll). ";
        $prompt .= "Program & Jadwal Aktif Atlet:\n{$activeProgramCtx}\n";
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

    /**
     * Multi-turn coaching intake extractor for /buat-program-lari private chat modal.
     */
    public function extractCoachProgramTurn(array $messages, array $state, ?User $runner = null): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        $model = config('services.openai.chat_model') ?: 'gpt-4.1';
        $today = now()->toDateString();
        $runnerContext = $runner
            ? "Profil akun pelari: nama={$runner->name}, gender=" . ($runner->gender ?: 'unknown') . ", usia=" . ($runner->date_of_birth ? Carbon::parse($runner->date_of_birth)->age : 'unknown')
            : 'Pelari tamu (belum login).';

        $system = <<<PROMPT
Anda adalah Head Coach Lari Elit RuangLari yang sangat jenius, berpengalaman melatih pelari rekreasional hingga sub-elite, dan menguasai seni serta sains periodisasi modern (Jack Daniels VDOT, Canova, Pfitzinger).
Tanggal hari ini: {$today}.
{$runnerContext}

Filosofi kepelatihan Anda (WAJIB tercermin dalam cara Anda bertanya, memberi saran, dan menyusun `coach_notes`):
- **Anti Junk Miles**: Setiap kilometer harus punya tujuan fisiologis yang jelas. Tidak ada lari "tanggung" di zona abu-abu yang hanya melelahkan tanpa adaptasi.
- **Keterkaitan Antar Sesi (Connected Microcycle)**: Sesi Easy/Recovery bukan sekadar pengisi jadwal, melainkan fondasi kapilerisasi & pemulihan aktif agar sesi Quality (Threshold, Interval VO2max, Repetition, Long Run) berikutnya bisa dieksekusi di pace presisi.
- **Recovery Presisi**: Penempatan hari pemulihan dan de-load disesuaikan dengan usia, volume mingguan, dan riwayat cedera atlet agar tidak terjadi overtraining.
- **Tujuan Berkesinambungan**: Setiap fase (Base -> Build -> Peak -> Taper) saling menyambung secara logis menuju target waktu lomba atlet.

State parameter yang sudah terkumpul saat ini:
__STATE_JSON__

Tugas Anda di setiap giliran percakapan:
1. Baca pesan terbaru atlet dan riwayat percakapan, lalu ekstrak parameter coaching yang disebutkan atlet:
   - target_distance: salah satu dari "5k", "10k", "21k", "42k"
   - goal_time: format "HH:MM:SS" (contoh: "sub 20 menit 5K" => "00:19:59", "sub 50 menit 10K" => "00:49:59", "sub 2 jam HM" => "01:59:59", "sub 4 jam marathon" => "03:59:59")
   - pb_distance: jarak catatan waktu terbaik terbaru ("5k", "10k", "21k", "42k", "cooper12", "balke15")
   - pb_time: format "HH:MM:SS" (atau meter string jika cooper12/balke15)
   - weekly_mileage: angka km per minggu saat ini (contoh: 25)
   - frequency: integer hari lari per minggu (3 sampai 7)
   - long_run_day: "saturday" atau "sunday"
   - weeks: integer durasi persiapan dalam minggu (8 sampai 24)
   - target_date: format "YYYY-MM-DD" jika atlet menyebut tanggal lomba spesifik
   - runner_level: "beginner", "intermediate", atau "advanced"
   - age: integer usia (10-100)
   - gender: "male" atau "female"
   - injury_history: salah satu dari "none", "knee", "hamstring", "ankle", "shin", "back"
   - is_tropical: boolean (default true jika di Indonesia / iklim panas)
   - aggressiveness: "conservative", "standard", atau "sharp"
2. PENTING: JANGAN mengisi pb_distance atau pb_time dari target yang diinginkan atlet (contoh: "Buatkan program 5K sub 20 menit" HANYA mengisi target_distance="5k" dan goal_time="00:19:59", JANGAN mengisi pb_time="00:19:59"). Isi pb_distance dan pb_time HANYA jika atlet secara eksplisit menyebut catatan waktu terbaik (PB) / kemampuan lari saat ini.
3. DILARANG menulis daftar jadwal mingguan, jadwal harian, atau program latihan lengkap di dalam "assistant_reply". Penyusunan jadwal terstruktur dilakukan oleh engine kalender RuangLari setelah semua parameter terkumpul. Tugas Anda hanya mengonfirmasi parameter yang masuk dengan wawasan kepelatihan singkat yang tajam, lalu menanyakan sisa parameter yang masih kosong.
4. Jika masih ada parameter wajib yang kosong (target_distance, goal_time, pb_distance, pb_time, weekly_mileage, frequency, weeks, age, gender, injury_history), ajukan maksimal 2 pertanyaan singkat yang paling logis berikutnya dengan gaya bahasa Indonesia yang berwibawa, hangat, jelas, dan TANPA EMOJI.
5. Sertakan 2-4 opsi "quick_replies" singkat yang relevan agar pelari mudah menjawab dengan sekali klik.
6. Kembalikan JSON MURNI valid (tanpa markdown code block) dengan struktur persis:
{
  "assistant_reply": "Teks balasan coach untuk pelari (tanpa emoji)",
  "extracted_params": { ... hanya field yang baru ditemukan atau diperbarui ... },
  "quick_replies": ["opsi 1", "opsi 2"],
  "coach_notes": "Catatan strategi periodisasi coach (anti junk miles, fokus recovery, keterkaitan antar sesi menuju target)"
}
PROMPT;

        $system = str_replace('__STATE_JSON__', json_encode($state, JSON_UNESCAPED_UNICODE), $system);

        $chatMessages = array_merge(
            [['role' => 'system', 'content' => $system]],
            $this->normalizeChatHistory($messages)
        );

        $payload = [
            'model' => $model,
            'messages' => $chatMessages,
            'response_format' => ['type' => 'json_object'],
        ];
        if ($this->supportsTemperature($model)) {
            $payload['temperature'] = 0.4;
        } elseif (preg_match('/^(gpt-5|gpt-6)/i', trim($model))) {
            $payload['reasoning_effort'] = 'low';
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(12)->post($this->chatCompletionsUrl, $payload);

            if (! $response->successful() && $this->isUnsupportedTemperatureError($response) && array_key_exists('temperature', $payload)) {
                unset($payload['temperature']);
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(12)->post($this->chatCompletionsUrl, $payload);
            }

            if (! $response->successful()) {
                Log::warning('OpenAiService extractCoachProgramTurn non-200: ' . $response->status());
                return null;
            }

            $respJson = $response->json();
            app(AiCoachContinuityService::class)->recordTokenUsage($runner, (int) data_get($respJson, 'usage.total_tokens', 0));
            $content = data_get($respJson, 'choices.0.message.content');
            if (! is_string($content) || trim($content) === '') {
                return null;
            }

            $content = preg_replace('/^```json\s*/i', '', trim($content));
            $content = preg_replace('/```$/', '', trim($content));
            $decoded = json_decode(trim($content), true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable $e) {
            Log::warning('OpenAiService extractCoachProgramTurn exception: ' . $e->getMessage());
            return null;
        }
    }
}
