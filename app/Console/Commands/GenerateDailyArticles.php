<?php

namespace App\Console\Commands;

use App\Models\AiArticleTopic;
use App\Models\Article;
use App\Services\OpenAiService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GenerateDailyArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'articles:generate-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate draft articles daily based on topics';

    protected $aiService;

    public function __construct(OpenAiService $aiService)
    {
        parent::__construct();
        $this->aiService = $aiService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $topics = AiArticleTopic::where('is_active', true)->get();

        if ($topics->isEmpty()) {
            $this->info('No active topics found.');
            return;
        }

        foreach ($topics as $topicItem) {
            $this->info("Generating article for topic: {$topicItem->topic}");
            
            $systemPrompt = "Tugas: Buat artikel SEO berkualitas tinggi berdasarkan input berikut. Artikel harus: 
 1. **Unik dan faktual**: Parafrase total atau tulis ulang, susun ulang kalimat, struktur baru. Semua klaim harus **divalidasi dari sumber terpercaya** (jurnal, gov, edu, atau situs resmi industri terbaru). 
 2. **SEO 2026-friendly**: Optimasi untuk Google terbaru, fokus E-A-T, user intent, semantic / LSI keywords, mobile-friendly. 
 3. **Terstruktur**: H1 untuk judul, H2–H3 untuk subtopik, bullet/numbered list bila perlu, paragraf pendek (2–4 kalimat). 
 4. **Engaging & mudah dibaca**: Bahasa jelas, formal atau sesuai target audiens, tetap menarik. 

 **Input**: 
 - Keyword / Topik utama: " . $topicItem->topic . ($topicItem->url ? "\n - Rewrite dari URL: " . $topicItem->url : "") . "

 **Output yang harus dihasilkan dalam format JSON**: 
 {
  \"seo_title\": \"Judul unik, mengandung keyword utama, max 60 karakter\",
  \"keywords\": \"Keyword utama + 3–5 semantic / LSI keywords\",
  \"meta_description\": \"150–160 karakter, mengandung keyword utama\",
  \"content\": \"Artikel lengkap 800–2000 kata dalam format HTML (gunakan H1, H2, H3, p, ul, li)\",
  \"slug\": \"URL pendek, SEO-friendly\"
 }

 Sertakan hanya valid JSON dalam jawaban Anda.";

            try {
                $response = $this->aiService->getAiResponse("Generate article about: " . $topicItem->topic, $systemPrompt, 'gpt-4o');

                if (!$response) {
                    $this->error("AI did not return any content for topic: {$topicItem->topic}");
                    continue;
                }

                $jsonStr = $response;
                if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
                    $jsonStr = $matches[1];
                } elseif (preg_match('/```\s*(.*?)\s*```/s', $response, $matches)) {
                    $jsonStr = $matches[1];
                }

                $data = json_decode($jsonStr, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    Article::create([
                        'title' => $data['seo_title'],
                        'slug' => $data['slug'] ?? Str::slug($data['seo_title']),
                        'content' => $data['content'],
                        'excerpt' => $data['meta_description'],
                        'meta_title' => $data['seo_title'],
                        'meta_description' => $data['meta_description'],
                        'meta_keywords' => $data['keywords'],
                        'status' => 'draft',
                        'user_id' => 1, // Default to admin or a specific user
                        'published_at' => null,
                    ]);
                    $this->info("Article created successfully: {$data['seo_title']}");
                } else {
                    $this->error("Invalid JSON for topic: {$topicItem->topic}");
                    Log::error("AI Daily Article Generation Error: Invalid JSON", ['response' => $response]);
                }

            } catch (\Exception $e) {
                $this->error("Error generating article: " . $e->getMessage());
                Log::error("AI Daily Article Generation Exception", ['error' => $e->getMessage()]);
            }
        }
    }
}
