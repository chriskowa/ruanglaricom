<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TavilyClient
{
    private string $apiKey;
    private string $endpoint = 'https://api.tavily.com/search';

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Search the web via Tavily.
     *
     * @param string $query
     * @param int    $maxResults
     * @param array  $excludeDomains
     * @return array|null
     */
    public function search(string $query, int $maxResults = 7, array $excludeDomains = []): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Tavily API Key is not set.');
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($this->endpoint, [
                'api_key'        => $this->apiKey,
                'query'          => $query,
                'max_results'    => $maxResults,
                'search_depth'   => 'advanced',
                'include_answer' => false,
                'exclude_domains' => $excludeDomains,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Tavily API Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Tavily Exception: ' . $e->getMessage());
            return null;
        }
    }
}
