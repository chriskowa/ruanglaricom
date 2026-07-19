<?php

namespace App\Services\Admin;

use Exception;
use Ramsey\Uuid\Uuid;
use App\Models\ArticleAgent;
use App\Models\Article;
use App\Lib\TavilyClient;
use App\Services\OpenAiService;

class AdminArticleAgentService
{
    private OpenAiService $openai;
    private ?TavilyClient $tavily;

    private string $modelBrainstorm = 'gpt-5.6';
    private string $modelSummary    = 'gpt-4o-mini';
    private string $modelTranslate  = 'gpt-4o-mini';
    private string $modelWriting    = 'gpt-5.5';

    public function __construct()
    {
        $this->openai = new OpenAiService();

        $tavilyKey = config('services.tavily.api_key') ?: env('TAVILY_API_KEY');
        $this->tavily = $tavilyKey ? new TavilyClient($tavilyKey) : null;
    }

    /**
     * Langkah 1: Input Topik (AI Brainstorming)
     * Menghasilkan 10 ide artikel berdasarkan topik + strategi SEO.
     */
    public function step1_inputTopic(array $input): array
    {
        $topic    = trim($input['topic'] ?? '');
        $strategy = $input['strategy'] ?? 'free';

        if ($topic === '') {
            throw new Exception("Topic is required.");
        }

        //? Get Top Articles as Reference (untuk strategi non-free)
        $topArticles = [];
        if ($strategy !== 'free') {
            $site        = $input['site'] ?? 'all';
            $topArticles = $this->getTopArticles($site, 50);
        }

        //* 1. Susun Base Prompt
        $prompt = "Kamu adalah seorang ahli strategi konten SEO untuk blog lari (Ruang Lari). Dengan topik utama '{$topic}', buatlah 10 ide artikel unik dan berpotensi trafik tinggi.\n\n";

        //* 2. Inject Referensi & Strategi
        if ($strategy !== 'free' && !empty($topArticles)) {
            $prompt .= "REFERENSI 50 ARTIKEL TERPOPULER SAYA:\n";
            foreach ($topArticles as $index => $article) {
                $num = $index + 1;
                $title    = $article['title'] ?? '';
                $keyword  = $article['keyword'] ?? '';
                $prompt  .= "{$num}. {$title} | Keyword: {$keyword}\n";
            }
            $prompt .= "\nINSTRUKSI STRATEGI:\n";

            switch ($strategy) {
                case 'gap': //! "Hindari Topik Serupa (Cari Celah Baru)"
                    $prompt .= "Bandingkan topik '{$topic}' dengan daftar referensi di atas. Buat 10 ide artikel dengan angle yang SAMA SEKALI BERBEDA dan belum ter-cover di daftar tersebut untuk mencegah keyword cannibalization.\n\n";
                    break;
                case 'cluster': //! "Buat Topik Turunan (Pillar & Cluster)"
                    $prompt .= "Gunakan daftar referensi di atas sebagai 'Pillar'. Buat 10 ide artikel turunan (cluster content) dari topik '{$topic}' yang bisa mendalami referensi tersebut dan sangat relevan untuk dipasang internal link menuju daftar referensi.\n\n";
                    break;
                case 'formula': //! "Tiru Formula Judul Teratas"
                    $prompt .= "Analisis pola psikologis (misal: listicle, FOMO, how-to) dari daftar referensi di atas. Terapkan formula penulisan judul dan angle sukses tersebut ke 10 ide artikel baru untuk topik '{$topic}'.\n\n";
                    break;
            }
        }

        //* 3. Format Output JSON Strict
        $prompt .= "Untuk setiap ide, berikan:\n" .
                   "1. Judul yang memancing klik (CTR tinggi)\n" .
                   "2. Kata kunci utama (Target Keyword)\n" .
                   "3. Ringkasan singkat isi konten (Maksimal 2 kalimat).\n\n" .
                   "KEMBALIKAN HASILNYA HANYA SEBAGAI ARRAY JSON objek dengan kunci persis seperti ini: 'title', 'keyword', 'summary'. Jangan sertakan format markdown, backticks (```json), atau teks pengantar apa pun di luar JSON.";

        //* 4. Hit LLM
        $rawResponse = $this->openai->getAiResponseOrThrow($prompt, "Kamu adalah ahli strategi konten SEO.", $this->modelBrainstorm);

        //* 5. Bersihkan dan Decode JSON
        $cleanJson    = trim(str_replace(['```json', '```'], '', $rawResponse));
        $optionsArray = json_decode($cleanJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($optionsArray)) {
            throw new Exception("Output bukan JSON valid: " . json_last_error_msg() . " | Raw: " . substr($rawResponse, 0, 100));
        }

        //* 6. Generate UUID & Simpan DB
        $uuid = Uuid::uuid4()->toString();
        ArticleAgent::create([
            'id'                    => $uuid,
            'user_input_topic'      => $topic,
            'strategy'              => $strategy,
            'brainstorming_options' => $optionsArray,
        ]);

        //* 7. Return Hasil
        return [
            'uuid'    => $uuid,
            'options' => $optionsArray
        ];
    }

    /**
     * Langkah 2: Pilih ide (atau input manual) lalu Research (Deep Dive).
     */
    public function step2_selectAndResearch(array $input): array
    {
        $uuid          = $input['uuid'] ?? null;
        $selectionData = $input['selection'] ?? null;

        if (!$selectionData) {
            throw new Exception("Selection data is required.");
        }

        if ($uuid) {
            $session = ArticleAgent::find($uuid);
            if (!$session) {
                throw new Exception("Session not found.");
            }
            $session->update([
                'selected_option_data' => $selectionData
            ]);
        } else {
            $uuid = Uuid::uuid4()->toString();
            ArticleAgent::create([
                'id'                   => $uuid,
                'selected_option_data' => $selectionData,
            ]);
        }

        $researchManual = $input['research_manual'] ?? false;
        $researchResult = $this->doResearch(['uuid' => $uuid, 'research_manual' => $researchManual]);
        return array_merge(['uuid' => $uuid], $researchResult);
    }

    /**
     * Langkah 3: Research (Deep Dive) via Tavily.
     */
    public function doResearch(array $input): array
    {
        $session = ArticleAgent::find($input['uuid']);
        if (!$session) {
            throw new Exception("Session not found.");
        }

        $selectedData = $session->selected_option_data;
        if (!$selectedData || !isset($selectedData['keyword'])) {
            throw new Exception("No keyword selected for research.");
        }

        //* JIKA MANUAL, SKIP TAVILY & SUMMARY
        $researchManual = $input['research_manual'] ?? false;
        if ($researchManual || !$this->tavily) {
            return [
                'uuid'                => $input['uuid'],
                'research_raw_tavily' => null,
                'research_summary'    => null,
                'cleaned'             => null
            ];
        }

        //* 1. Tavily Search
        $query        = ($selectedData['title'] ?? "") . ". Keyword: " . $selectedData['keyword'];
        $tavilyResult = $this->tavily->search($query, 7, ['youtube.com', 'tiktok.com']);
        if (!$tavilyResult) {
            throw new Exception("Failed to retrieve research data.");
        }

        //* 2. Extract text for Research
        $textForResearch = $this->cleanTavilyContext($tavilyResult['results'] ?? [], 12000);

        $prompt = "Kamu adalah seorang analis riset profesional untuk blog lari. Analisis data riset mentah berikut yang berisi cuplikan dan konten dari hasil pencarian.\n" .
                  "Tugas:\n" .
                  "1. Saring informasi yang tidak relevan atau duplikat.\n" .
                  "2. Sintesiskan poin-poin penting menjadi ringkasan yang komprehensif.\n" .
                  "3. Sertakan fakta, statistik, dan wawasan yang relevan dengan lari/olahraga.\n" .
                  "4. Cantumkan URL sumber untuk klaim utama jika memungkinkan.\n" .
                  "\nData Mentah:\n" . $textForResearch . "\n\n" .
                  "INSTRUKSI OUTPUT (PENTING):\n" .
                  "- Hasilkan ringkasan dalam format Markdown yang rapi.\n" .
                  "- JANGAN berikan kalimat pembuka, kata pengantar, atau basa-basi.\n" .
                  "- Karakter pertama dari responmu HARUS berupa tanda pagar (#) untuk Judul Utama.";

        $summary = $this->openai->getAiResponseOrThrow($prompt, "Kamu adalah analis riset profesional.", $this->modelSummary);

        //* 3. Update DB
        $session->update([
            'research_raw_tavily' => json_encode($tavilyResult),
            'research_summary'    => $summary
        ]);

        return [
            'uuid'                => $input['uuid'],
            'research_raw_tavily' => $tavilyResult,
            'research_summary'    => $summary,
            'cleaned'             => $textForResearch
        ];
    }

    /**
     * Langkah 4: Writing (Generation).
     */
    public function step3_doWrite(array $input): array
    {
        $uuid    = $input['uuid'] ?? null;
        $summary = $input['research_summary'] ?? null;

        $query = ArticleAgent::find($uuid);
        if (!$query) {
            throw new Exception("Session not found.");
        }

        if (!empty($summary)) {
            $query->update(['research_summary' => $summary]);
            $query->refresh();
        }
        if (!$query->research_summary) {
            throw new Exception("Research summary missing. Cannot generate article.");
        }

        $selectedData = $query->selected_option_data;
        $systemPrompt = "Aku ingin Kamu menjawab hanya dalam bahasa Indonesia.\n" .
                        "Aku ingin Kamu bertindak sebagai SEO yang sangat mahir dan penulis konten berkualitas tinggi untuk blog lari (Ruang Lari).\n" .
                        "Tugas Kamu adalah menulis artikel yang dimulai dengan Judul SEO {$selectedData['title']}.\n" .
                        "Tulis ulang konten dan sertakan daftar, tabel, atau subjudul menggunakan kata kunci terkait.\n" .
                        "Artikel harus 100% unik dan bebas plagiarisme.\n" .
                        "Artikel harus terdiri dari 300 hingga 1000 kata.\n" .
                        "1 subjudul minimal 2 paragraf.\n" .
                        "1 paragraf maksimal 4 kalimat.\n" .
                        "1 kalimat maksimal 20 kata.\n" .
                        "Sebarkan kata kunci fokus di awal, tengah, dan akhir artikel.\n" .
                        "Jangan menambahkan kesimpulan di akhir artikel.\n" .
                        "Semua hasil harus dalam bahasa Indonesia dan harus 100% gaya penulisan manusia.\n" .
                        "Perbaiki masalah tata bahasa dan ubah ke kalimat aktif.\n" .

                        "INSTRUKSI STRUKTUR HTML (PENTING):\n" .
                        "- Gunakan tag <h2> untuk sub-judul utama. Jangan gunakan <h1>.\n" .
                        "- Gunakan tag <h3> jika butuh sub-sub-judul.\n" .
                        "- Gunakan tag <p> untuk setiap paragraf.\n" .
                        "- Gunakan tag <strong> untuk bold dan <em> untuk miring.\n" .
                        "- Gunakan 1 tag <a> dengan attribute target='_blank' untuk hyperlink eksternal ke salah satu sumber.\n" .
                        "- Jika ada poin-poin, WAJIB gunakan <ul>/<ol> dengan <li>.\n" .
                        "- Jika ada data perbandingan/statistik, buat dalam <table> dengan <thead>/<tbody>.\n" .
                        "- Jika ada kutipan, gunakan <blockquote>.\n" .
                        "- Jangan sertakan markdown code block. Berikan raw string HTML.\n" .

                        "Selain itu, buatlah meta deskripsi SEO maksimal 150 karakter, excerpt 1-2 kalimat, dan slug pendek.\n" .
                        "Jangan buat focus keyword menjadi bold di dalam artikel.\n" .

                        "INSTRUKSI PROMPT GAMBAR (WAJIB):\n" .
                        "- Pada setiap sub-heading (<h2>), buatkan Prompt Gambar terkait topik tersebut.\n" .
                        "- Buatkan juga 1 Prompt Gambar Cover di bagian paling atas artikel (tepat di atas paragraf pertama).\n" .
                        "- WAJIB gunakan format teks persis seperti ini: [Gambar: Deskripsi detail visual disini...]\n" .
                        "- Letakkan teks prompt tersebut di dalam tag <p> tersendiri atau tepat di bawah <h2>.\n" .
                        "- Jangan gunakan tanda kurung biasa, HARUS diawali dan diakhiri dengan tanda kurung siku [ dan ].\n" .

                        "Return format: JSON object dengan keys: 'content' (HTML body), 'meta_description', 'excerpt', 'slug'.\n" .
                        "IMPORTANT: Pastikan semua double quote di dalam 'content' ter-escape agar JSON valid.";

        $userPrompt = "Title: {$selectedData['title']}\n" .
                      "Focus Keyword: {$selectedData['keyword']}\n" .
                      "Text to Rewrite:\n{$query->research_summary}\n";

        $rawResponse = $this->openai->getAiResponseOrThrow($userPrompt, $systemPrompt, $this->modelWriting);

        $decoded = json_decode($rawResponse, true);
        if (is_array($decoded)) {
            $decoded['title']   = $selectedData['title'] ?? '';
            $decoded['keyword'] = $selectedData['keyword'] ?? '';
            $contentToSave = json_encode($decoded);
        } else {
            $contentToSave = $rawResponse;
        }

        $query->update(['generated_article_content' => $contentToSave]);

        $content = $decoded['content'] ?? '';
        $imagePrompts = $this->parseImagePrompts($content);

        return [
            'uuid'         => $uuid,
            'result'       => $decoded ? $decoded : $rawResponse,
            'image_prompts' => $imagePrompts,
        ];
    }

    /**
     * Ekstrak semua marker [Gambar: ...] dari konten HTML.
     * Mengembalikan array asosiatif [marker => prompt].
     */
    public function parseImagePrompts(string $content): array
    {
        $prompts = [];
        if (empty($content)) {
            return $prompts;
        }

        preg_match_all('/\[Gambar:\s*(.*?)\s*\]/u', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $marker = $m[0];
            $prompt = trim($m[1]);
            if ($prompt !== '') {
                $prompts[$marker] = $prompt;
            }
        }

        return $prompts;
    }

    /**
     * Langkah 4b: Translate/Generate versi EN dari artikel ID yang sudah dibuat.
     * Menggunakan research_summary + selected_option_data yang sama, lalu
     * menerjemahkan & mengadaptasi ke bahasa Inggris (SEO EN).
     */
    public function step3_doWriteEn(string $uuid): array
    {
        $session = ArticleAgent::find($uuid);
        if (!$session) {
            throw new Exception("Session not found.");
        }

        $generatedId = $session->generated_article_content;
        if (!is_array($generatedId)) {
            $generatedId = json_decode($generatedId, true) ?: [];
        }
        if (empty($generatedId['content'])) {
            throw new Exception("Konten ID belum dibuat. Generate versi Indonesia terlebih dahulu.");
        }

        $selectedData = $session->selected_option_data ?? [];
        $idContent     = $generatedId['content'];

        $systemPrompt = "You are a highly skilled SEO specialist and high-quality content writer for a running blog (Ruang Lari).\n" .
                        "Your task is to translate and adapt the following Indonesian running article into fluent, natural English.\n" .
                        "Keep the same structure, headings, lists, tables, and the [Gambar: ...] image prompt markers exactly as they are.\n" .
                        "Adapt the title, meta description, excerpt, and slug for an English-speaking audience with proper English SEO keywords.\n" .
                        "The article must be 100% unique, plagiarism-free, and read like human-written English.\n" .
                        "Do NOT add a conclusion at the end.\n" .

                        "HTML STRUCTURE INSTRUCTIONS (IMPORTANT):\n" .
                        "- Use <h2> for main sub-headings. Do not use <h1>.\n" .
                        "- Use <h3> for sub-sub-headings if needed.\n" .
                        "- Use <p> for each paragraph.\n" .
                        "- Use <strong> for bold and <em> for italic.\n" .
                        "- Use 1 <a> tag with target='_blank' for an external hyperlink to one of the sources.\n" .
                        "- For bullet points, use <ul>/<ol> with <li>.\n" .
                        "- For comparison/statistics data, use <table> with <thead>/<tbody>.\n" .
                        "- For quotes, use <blockquote>.\n" .
                        "- Do not wrap output in markdown code blocks. Return raw HTML string.\n" .

                        "IMAGE PROMPT INSTRUCTIONS (REQUIRED):\n" .
                        "- Keep every [Gambar: ...] marker exactly as in the source, translated to English inside the brackets.\n" .
                        "- Format must be exactly: [Gambar: detailed visual description...]\n" .

                        "Return format: JSON object with keys: 'content' (HTML body), 'title', 'meta_description', 'excerpt', 'slug'.\n" .
                        "IMPORTANT: Ensure all double quotes inside 'content' are escaped so the JSON is valid.";

        $userPrompt = "Original Indonesian Title: {$generatedId['title']}\n" .
                      "Focus Keyword (ID): {$selectedData['keyword']}\n" .
                      "Indonesian Article Content (translate & adapt to English):\n{$idContent}\n";

        $rawResponse = $this->openai->getAiResponseOrThrow($userPrompt, $systemPrompt, $this->modelTranslate);

        $decoded = json_decode($rawResponse, true);
        if (!is_array($decoded)) {
            $decoded = ['content' => $rawResponse];
        }

        $decoded['title']   = $decoded['title'] ?? ($generatedId['title'] ?? '');
        $decoded['keyword'] = $selectedData['keyword'] ?? '';

        $session->update(['generated_article_content_en' => $decoded]);

        $content = $decoded['content'] ?? '';
        $imagePrompts = $this->parseImagePrompts($content);

        return [
            'uuid'          => $uuid,
            'result'        => $decoded,
            'image_prompts' => $imagePrompts,
        ];
    }

    /**
     * Simpan hasil agent ke Article (create baru atau update existing).
     */
    public function applyToArticle(string $uuid, ?int $articleId = null, ?string $contentOverride = null): Article
    {
        $session = ArticleAgent::find($uuid);
        if (!$session) {
            throw new Exception("Session not found.");
        }

        $generated = $session->generated_article_content;
        if (!is_array($generated)) {
            $generated = json_decode($generated, true) ?: [];
        }

        $generatedEn = $session->generated_article_content_en;
        if (!is_array($generatedEn)) {
            $generatedEn = json_decode($generatedEn, true) ?: [];
        }

        $selected = $session->selected_option_data ?? [];
        $title    = $generated['title'] ?? $selected['title'] ?? $session->user_input_topic ?? 'Untitled';
        $slug     = !empty($generated['slug']) ? \Illuminate\Support\Str::slug($generated['slug']) : \Illuminate\Support\Str::slug($title);

        // Gunakan konten yang sudah direplace dengan <img> jika dikirim dari modal.
        // Fallback berjenjang agar kolom 'content' (NOT NULL) tidak pernah null:
        // 1) override dari modal, 2) key 'content' dari JSON, 3) raw hasil AI, 4) string kosong.
        if ($contentOverride !== null && $contentOverride !== '') {
            $content = $contentOverride;
        } elseif (!empty($generated['content'])) {
            $content = $generated['content'];
        } elseif (is_string($session->generated_article_content) && $session->generated_article_content !== '') {
            $content = $session->generated_article_content;
        } else {
            $content = '';
        }

        $data = [
            'title'            => $title,
            'slug'             => $slug,
            'excerpt'          => $generated['excerpt'] ?? $generated['meta_description'] ?? null,
            'content'          => $content,
            'meta_description' => $generated['meta_description'] ?? null,
            'meta_keywords'    => $selected['keyword'] ?? null,
            'status'           => 'draft',
            'user_id'          => auth()->id(),
        ];

        // Isi versi EN jika sudah digenerate.
        if (!empty($generatedEn['content'])) {
            $data['title_en']            = $generatedEn['title'] ?? null;
            $data['excerpt_en']          = $generatedEn['excerpt'] ?? $generatedEn['meta_description'] ?? null;
            $data['content_en']          = $generatedEn['content'];
            $data['meta_title_en']       = $generatedEn['title'] ?? null;
            $data['meta_description_en'] = $generatedEn['meta_description'] ?? null;
            $data['meta_keywords_en']    = $selected['keyword'] ?? null;
        }

        if ($articleId) {
            $article = Article::findOrFail($articleId);
            $article->update($data);
        } else {
            $article = Article::create($data);
        }

        $session->update(['id_parent' => $article->id]);

        return $article;
    }

    /**
     * Retrieve Session Data.
     */
    public function detail(array $input): array
    {
        $query = ArticleAgent::where('id_parent', $input['id_article'])->latest()->first();
        if (!$query) {
            throw new Exception("Article Agent not found.");
        }

        return [
            'uuid'                      => $query->id ?? null,
            'user_input_topic'          => $query->user_input_topic ?? null,
            'brainstorming_options'     => $query->brainstorming_options ?? null,
            'selected_option_data'      => $query->selected_option_data ?? null,
            'research_raw_tavily'       => $query->research_raw_tavily ?? null,
            'research_summary'          => $query->research_summary ?? null,
            'generated_article_content' => $query->generated_article_content ?? null,
        ];
    }

    /**
     * Ambil artikel terpopuler untuk referensi strategi.
     */
    private function getTopArticles(string $site = 'all', int $limit = 50): array
    {
        $q = Article::query()->where('status', 'published');

        if ($site !== 'all') {
            $q->whereHas('category', function ($q) use ($site) {
                $q->where('slug', $site);
            });
        }

        $articles = $q->orderByDesc('views_count')
            ->limit($limit)
            ->get(['title', 'meta_keywords as keyword']);

        return $articles->toArray();
    }

    /**
     * Bersihkan & potong konteks Tavily untuk dipakai di prompt.
     */
    private function cleanTavilyContext(array $results, int $maxChars = 12000): string
    {
        $parts = [];
        $total = 0;

        foreach ($results as $item) {
            $title   = $item['title'] ?? '';
            $url     = $item['url'] ?? '';
            $content = $item['content'] ?? $item['raw_content'] ?? '';

            if (is_array($content)) {
                $content = implode(' ', $content);
            }

            $block = "Sumber: {$title} ({$url})\n{$content}\n";
            if (($total + strlen($block)) > $maxChars) {
                break;
            }
            $parts[] = $block;
            $total  += strlen($block);
        }

        return implode("\n---\n", $parts);
    }
}
