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
            
            $systemPrompt = "Tugas: Buat artikel lari berdaya viral tinggi, sarat data edukatif, dan siap mendominasi Google Search & Google Discover 2026 berdasarkan input berikut.\n"
                . "Pedoman Penulisan & Viralitas Berkualitas (High-CTR Ethical Virality 2026):\n"
                . "1. Judul Viral Bermartabat (seo_title): Maksimal 60 karakter, bernilai tinggi, memikat klik alami (High CTR), menghubungkan mitos vs fakta sains lari atau dilema komunitas, 100% selaras dengan isi tulisan (DILARANG clickbait murahan alay).\n"
                . "2. Lead Hook 3 Detik: Buka langsung dengan situasi emosional pelari atau fakta fisiologis mengejutkan. DILARANG membuka dengan kalimat klise AI ('Di era modern...', 'Lari adalah olahraga...').\n"
                . "3. Kotak Key Takeaways: Tepat setelah lead paragraf, wajib buatkan callout box ringkasan 3-4 poin kunci untuk merebut Google AI Overview / Featured Snippet:\n"
                . "   <div style=\"background:#12161F; border:1px solid #232B3B; border-radius:8px; padding:16px; margin:20px 0;\"><strong style=\"color:#ccff00; font-size:14px; text-transform:uppercase;\">Ringkasan Inti (Key Takeaways):</strong><ul style=\"margin-top:8px; padding-left:20px; color:#e2e8f0; font-size:14px;\"><li>...poin 1...</li><li>...poin 2...</li><li>...poin 3...</li></ul></div>\n"
                . "4. Data Konkret & Relevansi Pelari Indonesia: Sertakan data angka spesifik (pace, detak jantung Zone 2, cadence 170-180 spm, nutrisi carbo loading g/kg) dan kondisi iklim tropis Indonesia (28-32°C, kelembaban >80%, rute CFD/aspal).\n"
                . "5. Tabel Data: Wajib sertakan minimal 1 tabel perbandingan atau matriks data menggunakan <table>, <thead>, <tbody>, <tr>, <th>, <td>.\n"
                . "6. Actionable Checklist & FAQ: Berikan checklist praktis langkah demi langkah sebelum penutup dan seksi <h2>Pertanyaan yang Sering Diajukan (FAQ)</h2> dengan 3-4 kueri Google terpopuler.\n"
                . "7. Struktur HTML: JANGAN gunakan <h1> di content (judul sudah H1). Gunakan <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <table>.\n\n"
                . "**Input**:\n"
                . "- Keyword / Topik utama: " . $topicItem->topic . ($topicItem->url ? "\n - Rewrite dari URL: " . $topicItem->url : "") . "\n\n"
                . "**Output yang harus dihasilkan dalam format JSON**:\n"
                . "{\n"
                . "  \"seo_title\": \"Judul viral bermartabat, mengandung keyword utama, max 60 karakter\",\n"
                . "  \"keywords\": \"Keyword utama + 3–5 semantic / LSI keywords\",\n"
                . "  \"meta_description\": \"140–155 karakter persuasif merangkum manfaat baca\",\n"
                . "  \"content\": \"Artikel lengkap 800–1500 kata dalam format HTML valid tanpa <h1>\",\n"
                . "  \"slug\": \"url-pendek-seo-friendly\"\n"
                . "}\n\n"
                . "Sertakan hanya valid JSON dalam jawaban Anda.";

            try {
                $model = config('services.openai.blog_model') ?: config('services.openai.model') ?: 'gpt-6-astra';
                $response = $this->aiService->getAiResponse("Generate article about: " . $topicItem->topic, $systemPrompt, $model);

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
