<?php

namespace App\Services;

use App\Models\User;
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

    public function getAiResponseOrThrow(string $prompt, string $systemMessage = 'You are a helpful assistant.', ?string $model = null): string
    {
        $model = $model ?: (config('services.openai.model') ?: 'gpt-4o');
        $endpoint = config('services.openai.endpoint') ?: 'responses';

        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenAI API Key is not set.');
        }

        if ($endpoint === 'chat_completions') {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(90)->post($this->chatCompletionsUrl, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

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

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout(90)->post($this->responsesUrl, [
            'model' => $model,
            'instructions' => $systemMessage,
            'input' => $prompt,
            'temperature' => 0.7,
        ]);

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

    /**
     * Get response from AI Coach based on runner profile and message.
     */
    public function getCoachResponse(User $runner, string $userMessage): ?string
    {
        if (empty($this->apiKey)) {
            Log::error('OpenAI API Key is not set.');
            return 'Maaf, layanan AI Coach sedang tidak tersedia saat ini (API Key belum dikonfigurasi).';
        }

        // Get runner profile data
        $profileService = app(RunningProfileService::class);
        $profileData = $profileService->getProfile($runner);

        $systemPrompt = $this->buildSystemPrompt($runner, $profileData);

        $response = $this->getAiResponse($userMessage, $systemPrompt, 'gpt-4o');
        
        return $response ?: 'Maaf, Coach sedang beristirahat sejenak. Silakan coba lagi nanti.';
    }

    /**
     * Build a personalized system prompt for the AI Coach.
     */
    protected function buildSystemPrompt(User $user, array $profile): string
    {
        $pb = $profile['pb'] ?? [];
        $paces = $profile['paces'] ?? [];
        
        $prompt = "Anda adalah 'Coach AI' dari Ruang Lari, asisten lari profesional yang ahli, ramah, dan memotivasi. ";
        $prompt .= "Konteks chat harus selalu seputar dunia lari (teknik, nutrisi, recovery, jadwal latihan, sepatu, apparel lari, dll). ";
        $prompt .= "Jika user bertanya di luar konteks lari, jawab dengan sopan bahwa Anda hanya fokus pada perkembangan lari mereka.\n\n";
        
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

        $prompt .= "\nGunakan Bahasa Indonesia yang kasual namun profesional (gaya 'Coach Gaul'). Berikan saran yang spesifik berdasarkan data profil di atas jika memungkinkan.";
        
        return $prompt;
    }
}
