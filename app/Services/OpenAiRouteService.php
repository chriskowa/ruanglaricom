<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiRouteService
{
    /**
     * Generate relative shape coordinates or waypoints using OpenAI API.
     *
     * @param float $startLat
     * @param float $startLng
     * @param float $targetDistanceKm
     * @param string $promptOrShape
     * @param string|null $apiKey
     * @return array
     */
    public function generateWaypoints(float $startLat, float $startLng, float $targetDistanceKm, string $promptOrShape, ?string $apiKey = null): array
    {
        $key = $apiKey ?: config('services.openai.api_key', env('OPENAI_API_KEY'));

        if (empty($key)) {
            throw new \InvalidArgumentException('API Key OpenAI tidak dikonfigurasi.');
        }

        $systemPrompt = <<<EOT
Anda adalah AI Spesialis GPS Art & Route Generator untuk pelari. 
Tugas Anda adalah menghasilkan daftar koordinat (waypoints) latitude dan longitude yang membentuk rute lari sesuai permintaan pengguna (misalnya bentuk Hati, Pistol, Bintang, Segitiga, Angka 8, atau rute tematik).

PERATURAN OUTPUT:
1. Kembalikan HANYA JSON murni tanpa format markdown ```json ... ``` dan tanpa teks tambahan.
2. Format JSON yang wajib:
{
  "shape_name": "Nama Bentuk / Tema",
  "description": "Penjelasan singkat rute",
  "waypoints": [
    {"lat": -6.175392, "lng": 106.827153},
    {"lat": -6.176100, "lng": 106.828200}
  ]
}
3. Pastikan titik pertama (index 0) dan titik terakhir dari waypoints dekat dengan koordinat Start ({$startLat}, {$startLng}) agar rute membentuk loop tertutup (jika sesuai).
4. Buat antara 6 hingga 16 titik waypoint yang jika dihubungkan secara berurutan akan membentuk siluet yang diminta.
5. Skalakan penyebaran koordinat lat/lng agar estimasi keliling total rute kira-kira {$targetDistanceKm} km (1 derajat latitude ~ 111 km).
EOT;

        $userPrompt = "Titik Start: Lat {$startLat}, Lng {$startLng}. Jarak target: {$targetDistanceKm} km. Bentuk/Prompt Rute: '{$promptOrShape}'. Hasilkan waypoints JSON.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $key,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.7,
                'max_tokens' => 1000,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI Route API Failed: ' . $response->body());
                throw new \RuntimeException('Gagal terhubung ke OpenAI API: ' . $response->status());
            }

            $content = $response->json('choices.0.message.content', '');
            $cleanContent = trim(preg_replace('/^```(?:json)?|```$/m', '', $content));

            $data = json_decode($cleanContent, true);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($data['waypoints']) || !is_array($data['waypoints'])) {
                Log::warning('OpenAI Route API Output Malformed: ' . $content);
                throw new \RuntimeException('Format respons dari OpenAI tidak valid.');
            }

            return $data;
        } catch (\Throwable $e) {
            Log::error('OpenAiRouteService Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
