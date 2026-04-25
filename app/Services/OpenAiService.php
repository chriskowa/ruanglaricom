<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.openai.key') ?: env('OPENAI_API_KEY');
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

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::error('OpenAI API Error: ' . $response->body());
            return 'Maaf, Coach sedang beristirahat sejenak. Silakan coba lagi nanti.';

        } catch (\Exception $e) {
            Log::error('OpenAI Exception: ' . $e->getMessage());
            return 'Terjadi kesalahan saat menghubungi Coach.';
        }
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
