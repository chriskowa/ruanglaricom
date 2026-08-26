<?php

namespace App\Services\Admin;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use App\Models\ArticleAgent;
use App\Models\Article;
use App\Lib\TavilyClient;
use App\Services\OpenAiService;
use App\Services\Blog\InternalLinkService;

class AdminArticleAgentService
{
    private OpenAiService $openai;
    private ?TavilyClient $tavily;
    private InternalLinkService $internalLinkService;

    private string $modelBrainstorm = 'gpt-5.6';
    private string $modelSummary    = 'gpt-4o-mini';
    private string $modelTranslate  = 'gpt-4o-mini';
    private string $modelWriting    = 'gpt-5.6';

    /**
     * Aturan struktur HTML untuk artikel versi Indonesia.
     * Dipisah dari system prompt agar tidak duplikat dengan versi EN
     * dan lebih mudah diaudit/diubah di satu tempat.
     */
    private const HTML_STRUCTURE_RULES_ID = <<<'TEXT'
INSTRUKSI STRUKTUR HTML (PENTING):
- Gunakan tag <h2> untuk sub-judul utama. Jangan gunakan <h1>.
- Gunakan tag <h3> jika butuh sub-sub-judul.
- Gunakan tag <p> untuk setiap paragraf.
- Gunakan tag <strong> untuk bold dan <em> untuk miring.
- Gunakan 1 tag <a> dengan attribute target='_blank' untuk hyperlink eksternal ke salah satu sumber.
- Jika ada poin-poin, WAJIB gunakan <ul>/<ol> dengan <li>.
- Jika ada data perbandingan/statistik, buat dalam <table> dengan <thead>/<tbody>.
- Jika ada kutipan, gunakan <blockquote>.
- Jangan sertakan markdown code block. Berikan raw string HTML.
TEXT;

    /**
     * Aturan struktur HTML untuk artikel versi English (terjemahan).
     * Struktur harus tetap sinkron dengan HTML_STRUCTURE_RULES_ID di atas.
     */
    private const HTML_STRUCTURE_RULES_EN = <<<'TEXT'
HTML STRUCTURE INSTRUCTIONS (IMPORTANT):
- Use <h2> for main sub-headings. Do not use <h1>.
- Use <h3> for sub-sub-headings if needed.
- Use <p> for each paragraph.
- Use <strong> for bold and <em> for italic.
- Use 1 <a> tag with target='_blank' for an external hyperlink to one of the sources.
- For bullet points, use <ul>/<ol> with <li>.
- For comparison/statistics data, use <table> with <thead>/<tbody>.
- For quotes, use <blockquote>.
- Do not wrap output in markdown code blocks. Return raw HTML string.
TEXT;

    /**
     * Berapa lama daftar artikel terpopuler di-cache (jam).
     * Data ini jarang berubah dalam rentang menit/jam, jadi aman di-cache
     * untuk menghindari query berulang saat admin brainstorm beberapa topik berturut-turut.
     */
    private const TOP_ARTICLES_CACHE_HOURS = 3;

    public function __construct(?InternalLinkService $internalLinkService = null)
    {
        $this->openai = new OpenAiService();
        $this->internalLinkService = $internalLinkService ?? new InternalLinkService();

        // Catatan: env() sengaja TIDAK dipanggil langsung di sini. Setelah
        // `php artisan config:cache` di production, env() di luar file config
        // akan selalu return null meski .env masih ada nilainya — pastikan
        // config/services.php punya: 'tavily' => ['api_key' => env('TAVILY_API_KEY')].
        $tavilyKey = config('services.tavily.api_key');
        $this->tavily = $tavilyKey ? new TavilyClient($tavilyKey) : null;
    }

    /**
     * Langkah 1: Input Topik (AI Brainstorming)
     * Menghasilkan 10 ide artikel berdasarkan topik + strategi SEO & Google Discover 2026.
     */
    public function step1_inputTopic(array $input): array
    {
        $topic    = trim($input['topic'] ?? '');
        $rawNews  = trim($input['raw_news'] ?? '');
        $strategy = $input['strategy'] ?? 'free';

        if ($topic === '' && $rawNews === '') {
            throw new Exception("Silakan masukkan topik atau cuplikan berita.");
        }

        // Combine inputs for DB saving and AI prompt
        $fullTopicInput = $topic;
        if ($rawNews !== '') {
            $fullTopicInput .= ($fullTopicInput !== '' ? "\n\n" : "") . "[Cuplikan Berita Realtime / Threads / IG]:\n" . $rawNews;
        }

        //? Get Top Articles as Reference (untuk strategi non-free)
        $topArticles = [];
        if ($strategy !== 'free') {
            $site        = $input['site'] ?? 'all';
            $topArticles = $this->getTopArticles($site, 50);
        }

        //* 1. Susun Base Prompt dengan Aturan Mutlak Algoritma Google Discover 2026
        $prompt = "Kamu adalah seorang Redaktur Utama & Ahli Strategi Konten SEO/Google Discover senior untuk Ruang Lari.\n" .
                  "Input berikut berasal dari user yang memberikan topik lari atau cuplikan berita realtime / isu viral dari Threads, Instagram, atau media berita terkini:\n" .
                  "=== INPUT BERITA / TOPIK ===\n" .
                  "{$fullTopicInput}\n" .
                  "===========================\n\n" .
                  "Tugasmu: Analisis topik/berita realtime tersebut dan hasilkan 10 ide artikel berita & panduan SEO yang tajam, faktual (gaya jurnalisme Kompas.com / media olahraga terpercaya), informatif, dan memiliki nilai edukasi tinggi bagi pelari.\n\n" .
                  "ATURAN MUTLAK PEMBUATAN JUDUL SESUAI ALGORITMA GOOGLE DISCOVER TERBARU:\n" .
                  "1. LARANGAN CURIOSITY GAP MENIPU: Dilarang keras menahan atau menyembunyikan informasi kunci hanya untuk memancing klik (DILARANG: 'Ternyata Ini...', 'Gak Nyangka...', 'Inilah Alasannya...', 'Jangan Lakukan Ini Sebelum...', 'Ini Rahasianya...'). Tuliskan fakta, solusi, atau subjek intinya secara langsung dan transparan.\n" .
                  "2. LARANGAN FRASA HIPERBOLA & EMOSIONAL EKSTREM: DILARANG menggunakan kata-kata sensasional, berlebihan, atau bombastis (DILARANG: 'Bikin Gempar', 'Bikin Melongo', 'Bikin Syok', 'Rahasia Terbesar', 'Wajib Tahu!', 'Mengejutkan', 'Heboh', 'Bikin Merinding').\n" .
                  "3. KESESUAIAN MUTLAK JUDUL DAN ISI (100% CONTENT MATCH): Judul harus secara akurat, jujur, dan spesifik mencerminkan substansi data/fakta yang akan dibahas.\n" .
                  "4. STANDAR E-E-A-T & OTORITAS TINGGI: Gunakan gaya bahasa jurnalistik berbobot, berbasis sains olahraga, medis lari, atau fakta berita nyata dengan entitas jelas (nama event, jenis cedera, teknik latihan, durasi, dll).\n" .
                  "5. MENCEGAH PENALTI DOMAIN TINGKAT SISTEM: Hindari manipulasi CTR murahan. Klik tinggi harus diraih lewat 'clear value proposition', aktualitas berita, dan kejelasan manfaat bagi pembaca, bukan jebakan penasaran.\n\n";

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
                    $prompt .= "Analisis pola struktur judul informatif dan sukses dari daftar referensi di atas. Terapkan prinsip kejelasan informasi dan angle sukses tersebut ke 10 ide artikel baru untuk topik '{$topic}' dengan tetap mematuhi aturan anti-clickbait Google Discover.\n\n";
                    break;
            }
        }

        //* 3. Format Output JSON Strict
        $prompt .= "Untuk setiap ide, berikan:\n" .
                   "1. Judul informatif, bernilai tinggi, dan akurat (patuhi 100% aturan Google Discover di atas)\n" .
                   "2. Kata kunci utama (Focus Keyword / Target Ranking Utama)\n" .
                   "3. Kata kunci pendukung/turunan (Secondary Keywords / LSI, 3-5 kata kunci relevan, pisahkan koma)\n" .
                   "4. Ringkasan singkat isi konten (Maksimal 2 kalimat yang faktual dan selaras 100% dengan judul).\n\n" .
                   "KEMBALIKAN HASILNYA HANYA SEBAGAI ARRAY JSON objek dengan kunci persis seperti ini: 'title', 'keyword', 'secondary_keywords', 'summary'. Jangan sertakan format markdown, backticks (```json), atau teks pengantar apa pun di luar JSON.";

        //* 4. Hit LLM
        $rawResponse = $this->openai->getAiResponseOrThrow($prompt, "Kamu adalah ahli strategi konten SEO.", $this->modelBrainstorm);

        //* 5. Bersihkan dan Decode JSON (helper terpusat, konsisten dengan step lain)
        $optionsArray = $this->parseAiJson($rawResponse);

        if (!is_array($optionsArray)) {
            throw new Exception("Output bukan JSON valid. Raw: " . substr($rawResponse, 0, 200));
        }

        //* 6. Generate UUID & Simpan DB
        $uuid = Uuid::uuid4()->toString();
        ArticleAgent::create([
            'id'                    => $uuid,
            'user_input_topic'      => Str::limit($fullTopicInput, 60000),
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

        //* JIKA MANUAL (SKIP RISET WEB), BUAT SYNTHETIC RESEARCH SUMMARY
        $researchManual = $input['research_manual'] ?? false;
        if ($researchManual || !$this->tavily) {
            $fallbackSummary = $this->buildFallbackSummary($selectedData);

            $session->update([
                'research_raw_tavily' => null,
                'research_summary'    => $fallbackSummary
            ]);

            return [
                'uuid'                => $input['uuid'],
                'research_raw_tavily' => null,
                'research_summary'    => $fallbackSummary,
                'cleaned'             => null
            ];
        }

        //* 1. Tavily Search (General Web + Target Authority Outlets: Runner's World, CitiusMag, Marathon Handbook)
        $query = ($selectedData['title'] ?? "") . ". Keyword: " . $selectedData['keyword'];
        $authorityDomains = ['runnersworld.com', 'citiusmag.com', 'marathonhandbook.com'];

        // General Web Search
        $tavilyResult = $this->tavily->search($query, 5, ['youtube.com', 'tiktok.com']);
        
        // Targeted Search on Top Running Publications
        $nicheResult = $this->tavily->search($query, 5, [], $authorityDomains);

        $combinedResults = array_merge(
            $tavilyResult['results'] ?? [],
            $nicheResult['results'] ?? []
        );

        // Deduplicate results by URL
        $uniqueResults = [];
        $seenUrls = [];
        foreach ($combinedResults as $item) {
            $url = $item['url'] ?? '';
            if ($url && ! isset($seenUrls[$url])) {
                $seenUrls[$url] = true;
                $uniqueResults[] = $item;
            }
        }

        if (empty($uniqueResults)) {
            throw new Exception("Failed to retrieve research data.");
        }

        $tavilyResult = ['results' => $uniqueResults];

        //* 2. Extract text for Research
        $textForResearch = $this->cleanTavilyContext($uniqueResults, 14000);

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

        $selectedData = $query->selected_option_data ?: [];

        if (!$query->research_summary) {
            $query->update(['research_summary' => $this->buildFallbackSummary($selectedData)]);
            $query->refresh();
        }

        $selectedData = $query->selected_option_data;
        $titleTopic   = $selectedData['title'] ?? '';
        $focusKw      = $selectedData['keyword'] ?? '';

        //? Cari target internal link yang relevan dengan topik & kata kunci
        $internalLinkTargets     = $this->internalLinkService->getRelevantTargets($titleTopic, $focusKw, null, 8);
        $internalLinkInstruction = $this->internalLinkService->formatPromptInstruction($internalLinkTargets);

        $systemPrompt = "Aku ingin Kamu menjawab hanya dalam bahasa Indonesia.\n" .
                        "Aku ingin Kamu bertindak sebagai Jurnalis Utama & Penulis SEO/Google Discover Senior untuk Ruang Lari dengan gaya penulisan berita faktual, lugas, dan mendalam seperti Kompas.com.\n" .
                        "Tugas Kamu adalah menyusun artikel berita/panduan yang dimulai dengan Judul: {$titleTopic}.\n\n" .
                        "ATURAN GAYA PENULISAN BERITA & DISCOVER (KOMPAS STYLE & E-E-A-T 2026):\n" .
                        "- Paragraf pertama (Lead Berita): Mulai langsung dengan fakta utama berprinsip 5W+1H (Apa, Siapa, Kapan, Di mana, Mengapa, Bagaimana) yang jelas, padat, dan transparan.\n" .
                        "- Nada Jurnalistik & Bersumber: Gunakan kalimat aktif, lugas, obyektif, faktual, dan bersumber (sebutkan rujukan secara eksplisit jika ada rincian kutipan, data, atau cuplikan dari Threads/Instagram/berita terkini, misal: 'Berdasarkan laporan...', 'Sebagaimana diungkapkan dalam...'). JANGAN mengarang data atau hoaks.\n" .
                        "- Kedalaman & Edukasi: Hubungkan isu/berita realtime tersebut dengan panduan praktis, riset ilmiah, atau dampaknya bagi dunia lari.\n" .
                        "- Panjang & Struktur: 500 hingga 1300 kata. Setiap subjudul minimal 2 paragraf. 1 paragraf 2-4 kalimat. 1 kalimat maksimal 20-25 kata.\n" .
                        "- Keterbacaan & Optimasi SEO 2026: Sisipkan Focus Keyword secara alami di judul, paragraf pembuka (lead), dan minimal 1 sub-heading. Distribusikan Secondary Keywords secara alami ke dalam sub-heading (<h2>/<h3>) dan tubuh konten tanpa keyword stuffing.\n" .
                        "- Jangan menambahkan kata 'Kesimpulan' atau 'Penutup' sebagai subjudul kaku di akhir artikel.\n\n" .
                        "PEDOMAN JUDUL & META TITLE (GOOGLE DISCOVER ANTI-CLICKBAIT):\n" .
                        "- 100% Content Match: Judul & Meta Title wajib selaras mutlak dengan substansi artikel.\n" .
                        "- Dilarang Curiosity Gap yang Menipu & Frasa Hiperbola (DILARANG: 'Ternyata Ini...', 'Gak Nyangka...', 'Bikin Gempar', 'Rahasia Terbesar', dll).\n" .
                        "- Meta title maksimal 60 karakter (mengandung Focus Keyword, lugas, kredibel).\n" .
                        "- Meta description maksimal 150 karakter (faktual, merangkum intisari artikel tanpa clickbait).\n" .
                        "- Excerpt 1-2 kalimat ringkas dan padat.\n\n" .
                        self::HTML_STRUCTURE_RULES_ID . "\n\n" .
                        ($internalLinkInstruction !== '' ? "{$internalLinkInstruction}\n\n" : '') .
                        "INSTRUKSI PROMPT GAMBAR (WAJIB):\n" .
                        "- Pada setiap sub-heading (<h2>), buatkan Prompt Gambar terkait topik tersebut.\n" .
                        "- Buatkan juga 1 Prompt Gambar Cover di bagian paling atas artikel (tepat di atas paragraf pertama).\n" .
                        "- WAJIB gunakan format teks persis seperti ini: [Gambar: Deskripsi detail visual disini...]\n" .
                        "- Letakkan teks prompt tersebut di dalam tag <p> tersendiri atau tepat di bawah <h2>.\n" .
                        "- Jangan gunakan tanda kurung biasa, HARUS diawali dan diakhiri dengan tanda kurung siku [ dan ].\n" .
                        "- KETENTUAN GAYA VISUAL PROMPT GAMBAR:\n" .
                        "  * Subjek/Objek: Orang Indonesia natural, realistis, dan autentik (candid photo, ekspresi wajar khas Indonesia, gestur santai, bukan pose kaku atau model AI sintetis/plastik).\n" .
                        "  * Skema Warna: Palet warna netral (neutral color palette, muted natural tones, earth tones, warna bersih tanpa oversaturation).\n" .
                        "  * Look & Mood: Sangat soft dan natural (cinematic soft realism, suasana tenang dan estetis).\n" .
                        "  * Kontras: Kontras normal tidak terlalu kuat (balanced normal contrast, gentle tonal rolloff, bayangan lembut alami tanpa area hitam pekat berlebihan).\n" .
                        "  * Ketajaman & Tekstur: Sharpen normal to low (ketajaman lembut optik lensa nyata, bebas oversharpening), tekstur kulit lebih halus alami (smooth delicate natural skin pores, soft clean natural skin texture, tidak ada efek plastik 3D render).\n" .
                        "  * Lighting & Ratio: Soft natural daylight / diffuse gentle ambient lighting (gaya visual Grok Imagine yang hangat & tenang), Aspect Ratio wajib 3:2 (landscape 3:2).\n" .
                        "  * Tulis deskripsi visual di dalam [Gambar: ...] secara detail dalam bahasa Inggris (atau campuran ID/EN) yang spesifik menyebutkan subjek orang Indonesia, ekspresi candid, lokasi, warna netral (neutral muted tones), pencahayaan soft natural, kontras normal yang lembut, sharpen normal to low, dan tekstur kulit halus alami ratio 3:2.\n\n" .
                        "Return format: JSON object dengan keys: 'content' (HTML body), 'meta_title', 'meta_description', 'excerpt', 'slug', 'secondary_keywords'.\n" .
                        "IMPORTANT: Pastikan semua double quote di dalam 'content' ter-escape agar JSON valid.";

        $sourcesBlock = $this->extractSourceLinks($query->research_raw_tavily);

        $userPrompt = "Title: {$titleTopic}\n" .
                      "Focus Keyword: {$focusKw}\n" .
                      (!empty($selectedData['secondary_keywords']) ? "Secondary Keywords (LSI): {$selectedData['secondary_keywords']}\n" : '') .
                      "Text to Rewrite:\n{$query->research_summary}\n" .
                      ($sourcesBlock !== '' ? "\n{$sourcesBlock}\n" : '');

        $rawResponse = $this->openai->getAiResponseOrThrow($userPrompt, $systemPrompt, $this->modelWriting);

        //* Decode dengan helper terpusat (strip code fence + fallback regex extraction)
        $decoded = $this->parseAiJson($rawResponse);

        if (!is_array($decoded) || empty($decoded['content'])) {
            throw new Exception("Gagal parse hasil artikel dari AI menjadi JSON valid. Raw: " . substr($rawResponse, 0, 200));
        }

        $decoded['title']   = $titleTopic;
        $decoded['keyword'] = $focusKw;
        if (empty($decoded['secondary_keywords']) && !empty($selectedData['secondary_keywords'])) {
            $decoded['secondary_keywords'] = $selectedData['secondary_keywords'];
        }

        //? Post-processing: Pastikan ada 2-3 internal link alami yang terpasang di konten HTML
        if (!empty($internalLinkTargets)) {
            $decoded['content'] = $this->internalLinkService->injectInternalLinks($decoded['content'], $internalLinkTargets, 3);
        }

        $query->update(['generated_article_content' => json_encode($decoded)]);

        $content = $decoded['content'] ?? '';
        $imagePrompts = $this->parseImagePrompts($content);

        return [
            'uuid'          => $uuid,
            'result'        => $decoded,
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

        // Modifier 's' (dotall) ditambahkan supaya '.' juga match newline —
        // tanpa ini, prompt [Gambar: ...] yang kebetulan dipecah AI jadi
        // beberapa baris akan gagal ter-capture penuh oleh regex.
        preg_match_all('/\[Gambar:\s*(.*?)\s*\]/us', $content, $matches, PREG_SET_ORDER);
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
            $generatedId = $this->parseAiJson((string) $generatedId) ?? [];
        }
        if (empty($generatedId['content'])) {
            throw new Exception("Konten ID belum dibuat. Generate versi Indonesia terlebih dahulu.");
        }

        $selectedData = $session->selected_option_data ?? [];

        $decoded = $this->translateArticleFields([
            'title'   => $generatedId['title'] ?? '',
            'content' => $generatedId['content'],
        ], $selectedData['keyword'] ?? '', context: 'full_article');

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
     * Terjemahkan konten ID (dari form artikel) ke EN secara langsung.
     * Menerima field ID dan mengembalikan hasil terjemahan EN.
     */
    public function translateToEn(array $input): array
    {
        $titleId     = trim($input['title'] ?? '');
        $excerptId   = trim($input['excerpt'] ?? '');
        $contentId   = trim($input['content'] ?? '');
        $metaTitleId = trim($input['meta_title'] ?? '');
        $metaDescId  = trim($input['meta_description'] ?? '');
        $keywordsId  = trim($input['meta_keywords'] ?? '');

        if ($titleId === '' && $contentId === '') {
            throw new Exception("Konten ID (title/content) kosong. Tidak ada yang diterjemahkan.");
        }

        $decoded = $this->translateArticleFields([
            'title'             => $titleId,
            'excerpt'           => $excerptId,
            'content'           => $contentId,
            'meta_title'        => $metaTitleId,
            'meta_description'  => $metaDescId,
            'meta_keywords'     => $keywordsId,
        ], $keywordsId, context: 'form_fields');

        return [
            'title'            => $decoded['title'] ?? '',
            'excerpt'          => $decoded['excerpt'] ?? '',
            'content'          => $decoded['content'] ?? '',
            'meta_title'       => $decoded['meta_title'] ?? '',
            'meta_description' => $decoded['meta_description'] ?? '',
            'meta_keywords'    => $decoded['meta_keywords'] ?? '',
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
            $generated = $this->parseAiJson((string) $generated) ?? [];
        }

        $generatedEn = $session->generated_article_content_en;
        if (!is_array($generatedEn)) {
            $generatedEn = $this->parseAiJson((string) $generatedEn) ?? [];
        }

        $selected = $session->selected_option_data ?? [];
        $title    = $this->normalizeToString($generated['title'] ?? $selected['title'] ?? $session->user_input_topic ?? 'Untitled', ' ');
        $rawSlug  = $generated['slug'] ?? null;
        $slug     = !empty($rawSlug) ? Str::slug($this->normalizeToString($rawSlug, '-')) : Str::slug($title ?? 'article');

        // Gunakan konten yang sudah direplace dengan <img> jika dikirim dari modal.
        // Fallback berjenjang agar kolom 'content' (NOT NULL) tidak pernah null:
        // 1) override dari modal, 2) key 'content' dari JSON, 3) raw hasil AI, 4) string kosong.
        if ($contentOverride !== null && $contentOverride !== '') {
            $content = is_array($contentOverride) ? json_encode($contentOverride) : (string) $contentOverride;
        } elseif (!empty($generated['content'])) {
            $content = is_array($generated['content']) ? json_encode($generated['content']) : (string) $generated['content'];
        } elseif (is_string($session->generated_article_content) && $session->generated_article_content !== '') {
            $content = $session->generated_article_content;
        } else {
            $content = '';
        }

        $focusKeyword       = $this->normalizeToString($selected['keyword'] ?? $generated['focus_keyword'] ?? null);
        $secondaryKeywords  = $this->normalizeToString($generated['secondary_keywords'] ?? $selected['secondary_keywords'] ?? null);
        $metaKeywords       = $this->normalizeToString($generated['meta_keywords'] ?? $focusKeyword);

        $data = [
            'title'              => $title,
            'slug'               => $slug,
            'excerpt'            => $this->normalizeToString($generated['excerpt'] ?? $generated['meta_description'] ?? null, ' '),
            'content'            => $content,
            'meta_title'         => $this->normalizeToString($generated['meta_title'] ?? null, ' '),
            'meta_description'   => $this->normalizeToString($generated['meta_description'] ?? null, ' '),
            'focus_keyword'      => $focusKeyword,
            'secondary_keywords' => $secondaryKeywords,
            'meta_keywords'      => $metaKeywords,
            'status'             => 'draft',
            'user_id'            => auth()->id(),
        ];

        // Isi versi EN jika sudah digenerate.
        if (!empty($generatedEn['content'])) {
            $focusKeywordEn      = $this->normalizeToString($selected['keyword'] ?? $generatedEn['focus_keyword'] ?? null);
            $secondaryKeywordsEn = $this->normalizeToString($generatedEn['secondary_keywords'] ?? null);
            $metaKeywordsEn      = $this->normalizeToString($generatedEn['meta_keywords'] ?? $focusKeywordEn);

            $data['title_en']              = $this->normalizeToString($generatedEn['title'] ?? null, ' ');
            $data['excerpt_en']            = $this->normalizeToString($generatedEn['excerpt'] ?? $generatedEn['meta_description'] ?? null, ' ');
            $data['content_en']            = is_array($generatedEn['content']) ? json_encode($generatedEn['content']) : (string) $generatedEn['content'];
            $data['meta_title_en']         = $this->normalizeToString($generatedEn['meta_title'] ?? $generatedEn['title'] ?? null, ' ');
            $data['meta_description_en']   = $this->normalizeToString($generatedEn['meta_description'] ?? null, ' ');
            $data['focus_keyword_en']      = $focusKeywordEn;
            $data['secondary_keywords_en'] = $secondaryKeywordsEn;
            $data['meta_keywords_en']      = $metaKeywordsEn;
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
     * Pastikan nilai selalu berupa string bersih (mencegah error "Array to string conversion" saat disimpan ke MySQL).
     */
    private function normalizeToString(mixed $value, string $glue = ', '): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $flat = [];
            array_walk_recursive($value, function ($v) use (&$flat) {
                if ($v !== null && $v !== '') {
                    $flat[] = trim((string) $v);
                }
            });
            $flat = array_filter($flat, fn($v) => $v !== '');
            return !empty($flat) ? implode($glue, $flat) : null;
        }

        $str = trim((string) $value);
        return $str !== '' ? $str : null;
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
     * Di-cache karena data ini jarang berubah dalam rentang menit/jam,
     * sementara step1_inputTopic bisa dipanggil berulang kali (admin coba beberapa topik)
     * dengan kombinasi site+limit yang sama.
     */
    private function getTopArticles(string $site = 'all', int $limit = 50): array
    {
        $cacheKey = "article_agent:top_articles:{$site}:{$limit}";

        return Cache::remember($cacheKey, now()->addHours(self::TOP_ARTICLES_CACHE_HOURS), function () use ($site, $limit) {
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
        });
    }

    /**
     * Ambil daftar singkat (judul + URL) dari hasil riset Tavily mentah, untuk
     * dikirim eksplisit ke prompt penulisan sebagai referensi hyperlink yang VALID.
     *
     * Tanpa ini, step3_doWrite hanya mengandalkan URL yang (mungkin) ikut
     * terbawa di dalam teks research_summary — padahal instruksi di doResearch
     * untuk mencantumkan URL sifatnya "jika memungkinkan" (tidak dijamin).
     * Jika tidak ada URL nyata yang sampai ke model penulisan, ia tetap
     * diminta menyisipkan 1 <a> tag ke "salah satu sumber" dan berisiko
     * mengarang URL yang sebenarnya tidak ada (broken link di artikel published).
     */
    private function extractSourceLinks($rawTavily, int $limit = 5): string
    {
        $data = $rawTavily;
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (!is_array($data) || empty($data['results'])) {
            return '';
        }

        $lines = [];
        foreach (array_slice($data['results'], 0, $limit) as $item) {
            $title = $item['title'] ?? '';
            $url   = $item['url'] ?? '';
            if ($url) {
                $lines[] = "- {$title}: {$url}";
            }
        }

        if (empty($lines)) {
            return '';
        }

        return "DAFTAR SUMBER VALID (gunakan salah satu URL PERSIS berikut untuk tag <a>, JANGAN mengarang atau mengubah URL lain):\n" . implode("\n", $lines);
    }

    /**
     * Bangun ringkasan riset sintetis (fallback) dari data opsi yang dipilih.
     * Dipakai saat research_manual=true / Tavily client tidak tersedia (doResearch),
     * dan sebagai jaring pengaman terakhir jika research_summary masih kosong
     * saat proses penulisan dimulai (step3_doWrite). Disatukan di sini supaya
     * perubahan format fallback cukup dilakukan di satu tempat.
     */
    private function buildFallbackSummary(array $selectedData): string
    {
        $title       = $selectedData['title'] ?? 'Topik Lari';
        $keyword     = $selectedData['keyword'] ?? '';
        $summaryText = $selectedData['summary'] ?? $title;

        return "# {$title}\n\n" .
               "**Kata Kunci Utama**: {$keyword}\n\n" .
               "**Ringkasan Topik**: {$summaryText}\n\n" .
               "Tuliskan artikel mendalam mengenai {$title} yang berorientasi pada panduan praktis, tips latihan yang aman, dan solusi terbaik untuk pelari di Indonesia.";
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

    /**
     * Terjemahkan field artikel ID ke EN. Dipakai bersama oleh step3_doWriteEn
     * (translate dari sesi agent yang sudah punya artikel ID lengkap) dan
     * translateToEn (translate dari form manual). Disatukan supaya perubahan
     * pada instruksi/gaya translate cukup dilakukan di satu tempat.
     *
     * @param array  $fields  Field ID yang mau diterjemahkan (title, content, excerpt, meta_title, meta_description, meta_keywords — semua opsional kecuali title/content untuk konteks 'full_article')
     * @param string $keyword Focus keyword ID, dipakai sebagai konteks SEO
     * @param string $context 'full_article' untuk artikel HTML lengkap, 'form_fields' untuk field form terpisah — hanya mempengaruhi framing user prompt
     */
    private function translateArticleFields(array $fields, string $keyword, string $context): array
    {
        $systemPrompt = "You are a professional translator and SEO editor for a running blog (Ruang Lari).\n" .
                        "Translate and adapt the provided Indonesian article fields into fluent, natural English.\n" .
                        "Keep the same structure, headings, lists, tables, and the [Gambar: ...] image prompt markers exactly as they are.\n" .
                        "Adapt the title, meta title, meta description, excerpt, and keywords for an English-speaking audience with proper English SEO.\n" .
                        "The result must read like human-written English, 100% unique and plagiarism-free.\n" .
                        "Do NOT add a conclusion at the end.\n" .
                        self::HTML_STRUCTURE_RULES_EN . "\n" .

                        "IMAGE PROMPT INSTRUCTIONS (REQUIRED):\n" .
                        "- Keep every [Gambar: ...] marker exactly as in the source, translated to detailed English inside the brackets.\n" .
                        "- Format must be exactly: [Gambar: detailed visual description...]\n" .
                        "- VISUAL STYLE REQUIREMENTS:\n" .
                        "  * Subject: Authentic, natural, realistic Indonesian people (natural skin tones, relaxed candid expressions, realistic Indonesian features, avoiding stiff AI model poses).\n" .
                        "  * Color Palette: Neutral color palette, muted natural tones, earth tones, clean colors without oversaturation.\n" .
                        "  * Look & Mood: Soft and natural look, cinematic soft realism, calm and aesthetic atmosphere.\n" .
                        "  * Contrast: Balanced normal contrast (not too strong, gentle tonal rolloff, soft natural shadows without harsh crushing blacks).\n" .
                        "  * Sharpness & Skin Texture: Normal to low sharpness (soft optical lens clarity, zero oversharpening), smooth delicate natural skin pores, soft clean natural skin texture (no plastic 3D CGI or fake AI render look).\n" .
                        "  * Lighting & Ratio: Soft natural daylight, diffuse gentle warm ambient light (Grok Imagine aesthetic), 3:2 landscape aspect ratio.\n\n" .

                        "Return format: JSON object with keys: 'title', 'excerpt', 'content', 'meta_title', 'meta_description', 'focus_keyword', 'secondary_keywords', 'meta_keywords'.\n" .
                        "IMPORTANT: Ensure all double quotes inside 'content' are escaped so the JSON is valid.";

        if ($context === 'full_article') {
            $userPrompt = "Original Indonesian Title: {$fields['title']}\n" .
                          "Focus Keyword (ID): {$keyword}\n" .
                          (!empty($fields['secondary_keywords']) ? "Secondary Keywords (ID): {$fields['secondary_keywords']}\n" : '') .
                          "Indonesian Article Content (translate & adapt to English):\n{$fields['content']}\n";
        } else {
            $userPrompt = "Translate the following Indonesian article fields into English:\n\n" .
                          "Title (ID): {$fields['title']}\n" .
                          "Excerpt (ID): " . ($fields['excerpt'] ?? '') . "\n" .
                          "Meta Title (ID): " . ($fields['meta_title'] ?? '') . "\n" .
                          "Meta Description (ID): " . ($fields['meta_description'] ?? '') . "\n" .
                          "Focus Keyword (ID): " . ($fields['focus_keyword'] ?? $keyword) . "\n" .
                          "Secondary Keywords (ID): " . ($fields['secondary_keywords'] ?? '') . "\n" .
                          "Meta Keywords (ID): " . ($fields['meta_keywords'] ?? '') . "\n" .
                          "Content (ID HTML):\n{$fields['content']}\n";
        }

        $rawResponse = $this->openai->getAiResponseOrThrow($userPrompt, $systemPrompt, $this->modelTranslate);

        $decoded = $this->parseAiJson($rawResponse);
        if (!is_array($decoded)) {
            // Gagal parse: kembalikan raw sebagai content agar tidak kosong,
            // sama seperti perilaku translateToEn() sebelumnya.
            $decoded = ['content' => $rawResponse];
        }

        return $decoded;
    }

    /**
     * Parse respons AI menjadi array, dengan toleransi terhadap:
     * - Code fence markdown (```json ... ```) yang kadang disisipkan model
     * - Teks pengantar/penutup di luar objek JSON (diekstrak via regex sebagai fallback)
     *
     * Dipakai di semua tempat yang mengharapkan JSON dari AI (brainstorming,
     * writing, translate) agar penanganan "output tidak bersih" konsisten
     * dan tidak perlu ditulis ulang di tiap fungsi.
     *
     * @return array|null null jika benar-benar tidak bisa di-decode sebagai JSON object/array
     */
    private function parseAiJson(string $raw): ?array
    {
        if (trim($raw) === '') {
            return null;
        }

        $clean = trim(str_replace(['```json', '```'], '', $raw));
        $decoded = json_decode($clean, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Fallback: model mungkin menambahkan teks pembuka sebelum/sesudah JSON.
        // Ekstrak substring objek JSON pertama yang ditemukan.
        if (preg_match('/\{.*\}/s', $clean, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}