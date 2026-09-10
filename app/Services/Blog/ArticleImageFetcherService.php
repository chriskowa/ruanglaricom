<?php

namespace App\Services\Blog;

use App\Models\ArticleAgent;
use App\Models\BlogMedia;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ArticleImageFetcherService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Search images across available providers.
     *
     * @param string $query
     * @param string $provider 'auto', 'tavily', 'unsplash', 'google', 'dalle'
     * @param int $limit
     * @return array Array of candidates: [['url' => string, 'thumb' => string, 'title' => string, 'source' => string]]
     */
    public function search(string $query, string $provider = 'auto', int $limit = 5): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        switch (strtolower($provider)) {
            case 'tavily':
                return $this->searchTavily($query, $limit);
            case 'unsplash':
                return $this->searchUnsplash($query, $limit);
            case 'google':
                return $this->searchGoogle($query, $limit);
            case 'dalle':
                return $this->generateDalle($query);
            case 'auto':
            default:
                // Auto mode: combine Tavily + Unsplash (if configured) + Google (if configured)
                $results = [];

                // 1. Tavily (Active in RuangLari)
                try {
                    $tavilyResults = $this->searchTavily($query, $limit);
                    if (!empty($tavilyResults)) {
                        $results = array_merge($results, $tavilyResults);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Tavily image search failed: " . $e->getMessage());
                }

                // 2. Unsplash (If Access Key is set)
                if (count($results) < $limit && config('services.unsplash.access_key')) {
                    try {
                        $unsplashResults = $this->searchUnsplash($query, $limit - count($results));
                        if (!empty($unsplashResults)) {
                            $results = array_merge($results, $unsplashResults);
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Unsplash image search failed: " . $e->getMessage());
                    }
                }

                // 3. Google CSE (If API Key & CX are set)
                if (count($results) < $limit && config('services.google_cse.api_key') && config('services.google_cse.cx')) {
                    try {
                        $googleResults = $this->searchGoogle($query, $limit - count($results));
                        if (!empty($googleResults)) {
                            $results = array_merge($results, $googleResults);
                        }
                    } catch (\Throwable $e) {
                        Log::warning("Google CSE image search failed: " . $e->getMessage());
                    }
                }

                return array_slice($results, 0, $limit);
        }
    }

    /**
     * Search Tavily for images related to query.
     */
    public function searchTavily(string $query, int $limit = 5): array
    {
        $apiKey = config('services.tavily.api_key');
        if (empty($apiKey)) {
            return [];
        }

        $cleanQuery = $this->simplifySearchQuery($query);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.tavily.com/search', [
            'api_key'        => $apiKey,
            'query'          => $cleanQuery,
            'include_images' => true,
            'max_results'    => max(5, $limit),
            'search_depth'   => 'basic',
        ]);

        if (!$response->successful()) {
            Log::error("Tavily API error: " . $response->body());
            return [];
        }

        $data = $response->json();
        $images = $data['images'] ?? [];
        $candidates = [];

        foreach ($images as $img) {
            $imgUrl = is_array($img) ? ($img['url'] ?? null) : $img;
            if ($imgUrl && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                $candidates[] = [
                    'url'    => $imgUrl,
                    'thumb'  => $imgUrl,
                    'title'  => $cleanQuery,
                    'source' => 'Tavily Web',
                ];
            }
            if (count($candidates) >= $limit) {
                break;
            }
        }

        return $candidates;
    }

    /**
     * Search Unsplash for royalty-free photography.
     */
    public function searchUnsplash(string $query, int $limit = 5): array
    {
        $accessKey = config('services.unsplash.access_key');
        if (empty($accessKey)) {
            return [];
        }

        $cleanQuery = $this->simplifySearchQuery($query);

        $response = Http::withHeaders([
            'Authorization' => "Client-ID {$accessKey}",
        ])->timeout(25)->get('https://api.unsplash.com/search/photos', [
            'query'       => $cleanQuery,
            'per_page'    => $limit,
            'orientation' => 'landscape',
        ]);

        if (!$response->successful()) {
            Log::error("Unsplash API error: " . $response->body());
            return [];
        }

        $data = $response->json();
        $results = $data['results'] ?? [];
        $candidates = [];

        foreach ($results as $photo) {
            $url = $photo['urls']['regular'] ?? ($photo['urls']['full'] ?? null);
            $thumb = $photo['urls']['small'] ?? $url;
            if ($url) {
                $candidates[] = [
                    'url'    => $url,
                    'thumb'  => $thumb,
                    'title'  => $photo['alt_description'] ?? $cleanQuery,
                    'source' => 'Unsplash (Free Royalty)',
                ];
            }
        }

        return $candidates;
    }

    /**
     * Search Google Custom Search Engine (Image Search).
     */
    public function searchGoogle(string $query, int $limit = 5): array
    {
        $apiKey = config('services.google_cse.api_key');
        $cx = config('services.google_cse.cx');

        if (empty($apiKey) || empty($cx)) {
            return [];
        }

        $cleanQuery = $this->simplifySearchQuery($query);

        $response = Http::timeout(25)->get('https://www.googleapis.com/customsearch/v1', [
            'key'        => $apiKey,
            'cx'         => $cx,
            'searchType' => 'image',
            'q'          => $cleanQuery,
            'num'        => min(10, $limit),
        ]);

        if (!$response->successful()) {
            Log::error("Google CSE error: " . $response->body());
            return [];
        }

        $data = $response->json();
        $items = $data['items'] ?? [];
        $candidates = [];

        foreach ($items as $item) {
            $link = $item['link'] ?? null;
            $thumb = $item['image']['thumbnailLink'] ?? $link;
            if ($link && filter_var($link, FILTER_VALIDATE_URL)) {
                $candidates[] = [
                    'url'    => $link,
                    'thumb'  => $thumb,
                    'title'  => $item['title'] ?? $cleanQuery,
                    'source' => 'Google Images',
                ];
            }
        }

        return $candidates;
    }

    /**
     * Generate image via OpenAI DALL-E 3.
     */
    public function generateDalle(string $prompt): array
    {
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            return [];
        }

        // Refine prompt for realistic natural running photo
        $refinedPrompt = "A realistic, high-quality photograph for a running article: " .
            trim($prompt) . ". Natural daylight, authentic Indonesian runners, candid athletic moment, 3:2 landscape aspect ratio, no text, no watermark, photorealistic style.";

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type'  => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/images/generations', [
            'model'   => 'dall-e-3',
            'prompt'  => substr($refinedPrompt, 0, 950),
            'n'       => 1,
            'size'    => '1792x1024',
            'quality' => 'standard',
        ]);

        if (!$response->successful()) {
            Log::error("OpenAI DALL-E 3 error: " . $response->body());
            return [];
        }

        $data = $response->json();
        $url = $data['data'][0]['url'] ?? null;
        if (!$url) {
            return [];
        }

        return [
            [
                'url'    => $url,
                'thumb'  => $url,
                'title'  => $prompt,
                'source' => 'OpenAI DALL-E 3',
            ]
        ];
    }

    /**
     * Download an image, convert to WebP, register to Media Library, and return SEO metadata.
     *
     * @param string $imageUrl
     * @param string $seoKeyword
     * @param int|null $userId
     * @return array
     */
    public function downloadAndProcess(string $imageUrl, string $seoKeyword, ?int $userId = null): array
    {
        if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new Exception("URL gambar tidak valid: {$imageUrl}");
        }

        // 1. Download image stream
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            'Accept'     => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        ])->timeout(45)->get($imageUrl);

        if (!$response->successful()) {
            throw new Exception("Gagal mengunduh gambar dari sumber (Status: {$response->status()}).");
        }

        $imageBinary = $response->body();
        if (strlen($imageBinary) < 100) {
            throw new Exception("File gambar yang diunduh kosong atau rusak.");
        }

        // 2. Read with Intervention Image & process to WebP
        try {
            $image = $this->imageManager->read($imageBinary);
        } catch (\Throwable $e) {
            throw new Exception("Gagal memproses format gambar: " . $e->getMessage());
        }

        // Scale down if width > 1200px (preserve aspect ratio)
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }

        $encodedWebp = $image->toWebp(quality: 80);

        // 3. Create SEO-friendly filename from Focus / Secondary Keyword
        $baseSlug = Str::slug($seoKeyword);
        if ($baseSlug === '') {
            $baseSlug = 'gambar-artikel-' . Str::lower(Str::random(6));
        }

        $folder = 'blog/media';
        $filename = "{$baseSlug}.webp";
        $counter = 1;

        // Ensure unique filename if already exists in storage
        while (Storage::disk('public')->exists("{$folder}/{$filename}")) {
            $counter++;
            $filename = "{$baseSlug}-{$counter}.webp";
        }

        $relativePath = "{$folder}/{$filename}";
        Storage::disk('public')->put($relativePath, (string) $encodedWebp);
        $fileSize = Storage::disk('public')->size($relativePath);
        $publicUrl = Storage::disk('public')->url($relativePath);

        // 4. Register to Media Library (BlogMedia)
        $cleanTitle = Str::title(str_replace('-', ' ', $seoKeyword));
        $media = BlogMedia::create([
            'user_id'   => $userId ?: (auth()->id() ?: 1),
            'filename'  => $filename,
            'path'      => $relativePath,
            'disk'      => 'public',
            'mime_type' => 'image/webp',
            'size'      => $fileSize,
            'alt_text'  => $cleanTitle,
        ]);

        return [
            'success'       => true,
            'url'           => $publicUrl,
            'relative_path' => $relativePath,
            'filename'      => $filename,
            'alt'           => $cleanTitle,
            'title'         => $cleanTitle,
            'media_id'      => $media->id,
            'size_kb'       => round($fileSize / 1024, 1),
        ];
    }

    /**
     * Auto-fetch images for all [Gambar: ...] markers in an ArticleAgent session.
     *
     * @param string $uuid
     * @param string $provider
     * @return array
     */
    public function autoFetchAllForSession(string $uuid, string $provider = 'auto'): array
    {
        $session = ArticleAgent::find($uuid);
        if (!$session) {
            throw new Exception("Sesi Article Agent tidak ditemukan.");
        }

        $generated = $session->generated_article_content;
        if (!is_array($generated)) {
            $generated = json_decode((string) $generated, true) ?? [];
        }

        $content = $generated['content'] ?? '';
        if (empty($content)) {
            throw new Exception("Konten artikel belum digenerate.");
        }

        // Find all [Gambar: ...] markers
        preg_match_all('/\[Gambar:\s*(.*?)\s*\]/us', $content, $matches, PREG_SET_ORDER);
        if (empty($matches)) {
            return [
                'success'       => true,
                'message'       => 'Tidak ada marker [Gambar: ...] yang perlu diproses.',
                'updated_count' => 0,
                'images'        => [],
                'content'       => $content,
            ];
        }

        $selectedOption = $session->selected_option_data ?? [];
        $focusKeyword   = $generated['focus_keyword'] ?? ($selectedOption['keyword'] ?? ($generated['title'] ?? 'lari-indonesia'));
        $rawSecondaries = $generated['secondary_keywords'] ?? ($selectedOption['secondary_keywords'] ?? '');

        $secondaryList = [];
        if (is_array($rawSecondaries)) {
            $secondaryList = $rawSecondaries;
        } elseif (is_string($rawSecondaries) && trim($rawSecondaries) !== '') {
            $secondaryList = array_values(array_filter(array_map('trim', explode(',', $rawSecondaries))));
        }

        $processedImages = [];
        $updatedContent  = $content;
        $coverImagePath  = null;

        foreach ($matches as $index => $match) {
            $marker = $match[0];
            $prompt = trim($match[1]);

            // Assign SEO Keyword according to slot:
            // Slot 0 (Cover): Focus Keyword
            // Slot 1: Secondary Keyword 1
            // Slot 2: Secondary Keyword 2
            // Slot N: Secondary Keyword N, or fallback to topic + sub-index
            if ($index === 0) {
                $seoKeyword = $focusKeyword;
            } elseif (isset($secondaryList[$index - 1])) {
                $seoKeyword = $secondaryList[$index - 1];
            } else {
                $seoKeyword = "{$focusKeyword}-" . ($index + 1);
            }

            // Search query: use the visual prompt or keyword
            $searchQuery = !empty($prompt) ? $prompt : $seoKeyword;

            // Search candidates
            $candidates = $this->search($searchQuery, $provider, 3);
            if (empty($candidates)) {
                // Fallback search using only the keyword
                $candidates = $this->search($seoKeyword, 'auto', 3);
            }

            if (!empty($candidates)) {
                $topCandidate = $candidates[0];
                try {
                    $downloadResult = $this->downloadAndProcess($topCandidate['url'], $seoKeyword, auth()->id());

                    // Replace marker with HTML img figure
                    $imgHtml = '<figure class="my-6">' .
                        '<img src="' . htmlspecialchars($downloadResult['url']) . '" ' .
                        'alt="' . htmlspecialchars($downloadResult['alt']) . '" ' .
                        'title="' . htmlspecialchars($downloadResult['title']) . '" ' .
                        'loading="lazy" class="w-full rounded-xl shadow-md">' .
                        '</figure>';

                    $updatedContent = str_replace($marker, $imgHtml, $updatedContent);

                    if ($index === 0 && empty($coverImagePath)) {
                        $coverImagePath = $downloadResult['relative_path'];
                    }

                    $processedImages[] = [
                        'marker'    => $marker,
                        'keyword'   => $seoKeyword,
                        'url'       => $downloadResult['url'],
                        'filename'  => $downloadResult['filename'],
                        'alt'       => $downloadResult['alt'],
                        'source'    => $topCandidate['source'] ?? 'Auto',
                    ];
                } catch (\Throwable $e) {
                    Log::warning("Failed to auto-download image for marker '{$marker}': " . $e->getMessage());
                }
            }
        }

        // Update session generated content
        $generated['content'] = $updatedContent;
        if ($coverImagePath) {
            $generated['featured_image'] = $coverImagePath;
        }

        $session->update(['generated_article_content' => json_encode($generated)]);

        return [
            'success'        => true,
            'updated_count'  => count($processedImages),
            'images'         => $processedImages,
            'content'        => $updatedContent,
            'featured_image' => $coverImagePath,
        ];
    }

    /**
     * Clean and simplify lengthy AI prompts into concise search queries for Google / Tavily / Unsplash.
     */
    protected function simplifySearchQuery(string $raw): string
    {
        // Remove common prompt noise
        $cleaned = preg_replace('/\[Gambar:\s*/i', '', $raw);
        $cleaned = preg_replace('/\]$/', '', $cleaned);
        $cleaned = preg_replace('/(ratio\s*3:2|candid|photorealistic|dslr|8k|4k|hyperrealistic|grok imagine style|soft natural light|neutral color palette)/i', '', $cleaned);
        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        // Limit to 6-8 words for optimal image search relevance
        $words = explode(' ', $cleaned);
        if (count($words) > 8) {
            $cleaned = implode(' ', array_slice($words, 0, 8));
        }

        return $cleaned ?: 'running athletic lifestyle';
    }
}
