<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\OpenAiService;
use App\Services\Blog\InternalLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    protected $aiService;
    protected $internalLinkService;

    public function __construct(OpenAiService $aiService, InternalLinkService $internalLinkService)
    {
        $this->aiService = $aiService;
        $this->internalLinkService = $internalLinkService;
    }

    public function index(Request $request)
    {
        // Self-healing normalization for legacy malformed featured_image entries in DB
        try {
            Article::where('featured_image', 'like', '%storage/http%')
                ->orWhere('featured_image', 'like', '%https://ruanglari.com/storage/%')
                ->orWhere('featured_image', 'like', '%http://localhost%/storage/%')
                ->orWhere('featured_image', 'like', '%http://127.0.0.1%/storage/%')
                ->get()
                ->each(function ($art) {
                    $art->featured_image = $art->featured_image;
                    $art->saveQuietly();
                });
        } catch (\Throwable $e) {}

        $query = Article::with('category', 'user');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            if (strlen($search) > 100) {
                $search = substr($search, 0, 100);
            }
            $escapedSearch = addcslashes($search, '%_\\');

            $query->where(function ($q) use ($escapedSearch) {
                $q->where('title', 'like', "%{$escapedSearch}%")
                  ->orWhere('excerpt', 'like', "%{$escapedSearch}%")
                  ->orWhere('slug', 'like', "%{$escapedSearch}%")
                  ->orWhereHas('user', function ($uq) use ($escapedSearch) {
                      $uq->where('name', 'like', "%{$escapedSearch}%");
                  });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if (in_array($status, ['published', 'draft', 'archived'])) {
                $query->where('status', $status);
            }
        }

        $articles = $query->latest()->paginate(15)->withQueryString();
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.blog.articles.index', compact('articles', 'categories'));
    }

    /**
     * Generate article using AI.
     */
    public function generate(Request $request)
    {
        set_time_limit(180);
        ini_set('max_execution_time', 180);

        try {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'topic' => 'required|string',
                'url'   => 'nullable|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $topic = $request->topic;
            $url   = $request->url;

            $internalLinkTargets = $this->internalLinkService->getRelevantTargets($topic, '', null, 6);
            $internalLinkInstruction = $this->internalLinkService->formatPromptInstruction($internalLinkTargets);
            
            $systemPrompt = "Anda adalah Redaktur Eksekutif & Jurnalis Investigatif Senior Ruang Lari (Bahasa Indonesia). Gaya tulisan Anda menggabungkan kedalaman analisis Runner's World dengan keluwesan jurnalisme Kompas.com yang tajam, berwibawa, sarat data fisiologis nyata, dan sangat diminati komunitas lari.\n\n"
                . "PANDUAN VIRALITAS BERMUTU TINGGI & GOOGLE DISCOVER 2026:\n"
                . "1. HOOK 3 DETIK (LEAD PARAGRAF): DILARANG membuka dengan kalimat klise AI ('Di era modern ini...', 'Olahraga lari kian digemari...', 'Bukan rahasia lagi...', 'Tak dapat dimungkiri bahwa...'). Buka LANGSUNG dengan narasi situasi nyata pelari, paradoks sains yang mengejutkan, atau pertanyaan provokatif yang membakar rasa ingin tahu. Sisipkan Focus Keyword di 100 kata pertama.\n"
                . "2. KOTAK RINGKASAN INTI (KEY TAKEAWAYS) WAJIB: Tepat setelah lead paragraf pertama, buatkan callout box ringkasan 3-4 poin kunci untuk merebut Google AI Overview / Featured Snippets:\n"
                . "   <div style=\"background:#12161F; border:1px solid #232B3B; border-radius:8px; padding:16px; margin:20px 0;\">\n"
                . "     <strong style=\"color:#ccff00; font-size:14px; text-transform:uppercase; letter-spacing:0.05em;\">Ringkasan Inti (Key Takeaways):</strong>\n"
                . "     <ul style=\"margin-top:8px; padding-left:20px; color:#e2e8f0; font-size:14px;\">\n"
                . "       <li>...fakta kunci 1...</li>\n"
                . "       <li>...fakta kunci 2...</li>\n"
                . "       <li>...fakta kunci 3...</li>\n"
                . "     </ul>\n"
                . "   </div>\n"
                . "3. HIGH INFORMATION GAIN & KONTEKS PELARI INDONESIA: Sertakan data angka terukur (pace, Zone 2 vs Zone 4, cadence 170-180 spm, VO2 max, carbo-loading gram/kg berat badan), serta realitas pelari lokal (iklim tropis 28-32°C, kelembaban >80%, rute CFD/aspal, event maraton nasional seperti Maybank Marathon Bali / Borobudur Marathon).\n"
                . "4. TABEL KOMPARASI/DATA WAJIB: Minimal 1 tabel perbandingan atau matriks data menggunakan <table>, <thead>, <tbody>, <tr>, <th>, <td>.\n"
                . "5. ACTIONABLE CHECKLIST: Satu subjudul menjelang akhir dengan <ul>/<ol> berisi checklist langkah taktis yang bisa langsung dipraktekkan pembaca saat lari besok pagi (membuat artikel di-bookmark dan dibagikan ke WhatsApp grup lari).\n"
                . "6. SEKSI FAQ (PEOPLE ALSO ASK OPTIMIZATION) WAJIB: Di akhir artikel, buat seksi <h2>Pertanyaan yang Sering Diajukan (FAQ)</h2> berisi 3-4 pertanyaan kueri Google terpopuler dengan jawaban ringkas 2-3 kalimat.\n"
                . "7. STRUKTUR HTML: JANGAN gunakan <h1> di content (judul sudah H1). Gunakan <h2> dan <h3>. Paragraf 2–4 kalimat. DILARANG membuat subjudul kaku 'Kesimpulan' atau 'Penutup'.\n"
                . ($internalLinkInstruction !== '' ? "{$internalLinkInstruction}\n\n" : '')
                . "- Gunakan 1 tag <a> dengan attribute target='_blank' ke salah satu sumber referensi terpercaya.\n\n"
                . "INSTRUKSI PROMPT GAMBAR (WAJIB):\n"
                . "- Pada setiap sub-heading (<h2>) dan bagian atas artikel (cover), buatkan marker prompt gambar [Gambar: Deskripsi visual...].\n"
                . "- GAYA PROMPT GAMBAR: Subjek orang Indonesia natural & realistis (candid photo, ekspresi wajar santai, bukan pose kaku/3D AI sintetis), skema warna netral (neutral muted tones, earth tones tanpa oversaturation), look soft & natural, kontras normal tidak terlalu kuat (gentle tonal rolloff, bayangan lembut), sharpen normal to low (bebas oversharpening), tekstur kulit halus alami (smooth delicate natural skin pores), lighting alami/hangat (Grok Imagine style), ratio landscape 3:2.\n\n"
                . "Input:\n"
                . "- Topik / Berita Realtime: {$topic}\n"
                . ($url ? "- URL referensi: {$url}\n" : "")
                . "Output HARUS JSON valid TANPA markdown dan TANPA teks lain. Format:\n"
                . "{\n"
                . "  \"seo_title\": \"... (<= 60 karakter, judul viral bermartabat, tajam, high-CTR, 100% selaras dengan isi)\",\n"
                . "  \"focus_keyword\": \"... (1 kata kunci utama target ranking Google)\",\n"
                . "  \"secondary_keywords\": \"... (3-5 kata kunci turunan/LSI, pisahkan koma)\",\n"
                . "  \"meta_description\": \"... (140-155 karakter, ringkasan persuasif memicu klik)\",\n"
                . "  \"excerpt\": \"... (ringkas 1-2 kalimat menggugah rasa penasaran)\",\n"
                . "  \"content\": \"... (HTML body lengkap sesuai instruksi di atas, tanpa <h1>)\",\n"
                . "  \"slug\": \"... (slug pendek SEO-friendly)\",\n"
                . "  \"sources\": [\"https://...\"]\n"
                . "}";

            $userPrompt = "Topik / Berita Realtime: {$topic}" . ($url ? "\nURL referensi: {$url}" : "");
            $model = config('services.openai.blog_model') ?: config('services.openai.model') ?: 'gpt-6-astra';
            $response = $this->aiService->getAiResponseOrThrow($userPrompt, $systemPrompt, $model);

            $jsonStr = trim($response);
            $jsonStr = str_replace(["```json", "```"], '', $jsonStr);
            $jsonStr = trim($jsonStr);

            if (preg_match('/\{[\s\S]*\}/', $jsonStr, $matches)) {
                $jsonStr = $matches[0];
            }

            $data = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI returned invalid JSON format.',
                    'raw' => $response
                ], 500);
            }

            if (!empty($data['content']) && !empty($internalLinkTargets)) {
                $data['content'] = $this->internalLinkService->injectInternalLinks($data['content'], $internalLinkTargets, 3);
            }

            if (isset($data['slug'])) {
                $data['slug'] = Str::slug((string) $data['slug']);
            } elseif (isset($data['seo_title'])) {
                $data['slug'] = Str::slug((string) $data['seo_title']);
            }

            if (!isset($data['excerpt']) && isset($data['meta_description'])) {
                $data['excerpt'] = (string) $data['meta_description'];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refine and perfect an existing article (title, excerpt, content, SEO) using AI.
     */
    public function refine(Request $request)
    {
        set_time_limit(180);
        ini_set('max_execution_time', 180);

        try {
            $title = trim((string) $request->input('title', ''));
            $content = trim((string) $request->input('content', ''));
            $excerpt = trim((string) $request->input('excerpt', ''));
            $focusKeyword = trim((string) $request->input('focus_keyword', ''));
            $secondaryKeywords = trim((string) $request->input('secondary_keywords', ''));
            $topic = trim((string) $request->input('topic', ''));

            // Filter out empty HTML tags from content
            $cleanContent = trim(strip_tags($content));
            if ($cleanContent === '' && preg_match('/^(<p>(&nbsp;|\s|<br>)*<\/p>)+$/i', $content)) {
                $content = '';
                $cleanContent = '';
            }

            // Only validate failure if ALL fields are empty
            if ($title === '' && $cleanContent === '' && $excerpt === '' && $focusKeyword === '' && $topic === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'Harap isi minimal salah satu bidang (judul, konten, excerpt, atau focus keyword) untuk disempurnakan.'
                ], 422);
            }

            // Determine primary subject / topic
            $primaryTopic = $title ?: ($focusKeyword ?: ($topic ?: ($excerpt ?: Str::limit($cleanContent, 100, ''))));

            $internalLinkTargets = $this->internalLinkService->getRelevantTargets($primaryTopic, '', null, 6);
            $internalLinkInstruction = $this->internalLinkService->formatPromptInstruction($internalLinkTargets);

            // Limit existing content context to around 6000 chars to prevent token overflow
            $contentContext = Str::limit($content ?: $cleanContent, 6000, '... [konten lama dipotong]');

            $systemPrompt = "Anda adalah Redaktur Eksekutif & Jurnalis Investigatif Senior Ruang Lari (Bahasa Indonesia). Tugas Anda adalah MENYEMPURNAKAN dan MENINGKATKAN KUALITAS artikel lama/draf di Ruang Lari agar memiliki standar editorial kelas dunia (Runner's World + Kompas.com), viral bermartabat, dan mendominasi Google Discover serta Google Search 2026.\n\n"
                . "PANDUAN PENYEMPURNAAN ARTIKEL (VIRALITAS BERMUTU TINGGI & GOOGLE SEARCH 2026):\n"
                . "1. HOOK 3 DETIK (LEAD PARAGRAF): DILARANG membuka dengan kalimat klise AI ('Di era modern ini...', 'Olahraga lari kian digemari...', 'Bukan rahasia lagi...', 'Tak dapat dimungkiri bahwa...'). Buka LANGSUNG dengan narasi situasi nyata pelari, paradoks sains yang mengejutkan, atau pertanyaan provokatif yang membakar rasa ingin tahu. Sisipkan Focus Keyword di 100 kata pertama.\n"
                . "2. KOTAK RINGKASAN INTI (KEY TAKEAWAYS) WAJIB: Tepat setelah lead paragraf pertama, buatkan callout box ringkasan 3-4 poin kunci untuk merebut Google AI Overview / Featured Snippets:\n"
                . "   <div style=\"background:#12161F; border:1px solid #232B3B; border-radius:8px; padding:16px; margin:20px 0;\">\n"
                . "     <strong style=\"color:#ccff00; font-size:14px; text-transform:uppercase; letter-spacing:0.05em;\">Ringkasan Inti (Key Takeaways):</strong>\n"
                . "     <ul style=\"margin-top:8px; padding-left:20px; color:#e2e8f0; font-size:14px;\">\n"
                . "       <li>...fakta kunci 1...</li>\n"
                . "       <li>...fakta kunci 2...</li>\n"
                . "       <li>...fakta kunci 3...</li>\n"
                . "     </ul>\n"
                . "   </div>\n"
                . "3. HIGH INFORMATION GAIN & KONTEKS PELARI INDONESIA: Sertakan data angka terukur (pace, Zone 2 vs Zone 4, cadence 170-180 spm, VO2 max, carbo-loading gram/kg berat badan), serta realitas pelari lokal (iklim tropis 28-32°C, kelembaban >80%, rute CFD/aspal, event maraton nasional seperti Maybank Marathon Bali / Borobudur Marathon).\n"
                . "4. TABEL KOMPARASI/DATA WAJIB: Minimal 1 tabel perbandingan atau matriks data terukur menggunakan <table>, <thead>, <tbody>, <tr>, <th>, <td>.\n"
                . "5. ACTIONABLE CHECKLIST: Satu subjudul menjelang akhir dengan <ul>/<ol> berisi checklist langkah taktis yang bisa langsung dipraktekkan pembaca saat lari besok pagi (membuat artikel di-bookmark dan dibagikan ke WhatsApp grup lari).\n"
                . "6. EVALUASI CERDAS SEKSI FAQ (PEOPLE ALSO ASK OPTIMIZATION):\n"
                . "   - Analisis tipe dan tujuan artikel secara dinamis:\n"
                . "     a. JIKA artikel merupakan panduan/tips/tutorial/edukasi fisiologi lari/review mendalam/analisis teknis sepatu atau latihan: WAJIB buat seksi <h2>Pertanyaan yang Sering Diajukan (FAQ)</h2> di akhir artikel berisi 3-4 pertanyaan kueri Google terpopuler dengan format <h3> untuk pertanyaan dan <p> untuk jawaban langsung 2-3 kalimat.\n"
                . "     b. JIKA artikel merupakan berita langsung (straight news), laporan hasil lomba/race recap, pengumuman event resmi, atau profil singkat atlet: JANGAN sertakan seksi FAQ, karena pembaca berita membutuhkan liputan padat, ringkas, dan fokus tanpa FAQ yang terkesan dipaksakan.\n"
                . "7. STRUKTUR HTML: JANGAN gunakan <h1> di content (judul sudah H1). Gunakan <h2> dan <h3>. Paragraf 2–4 kalimat. DILARANG membuat subjudul kaku 'Kesimpulan' atau 'Penutup'.\n"
                . ($internalLinkInstruction !== '' ? "{$internalLinkInstruction}\n\n" : '')
                . "- Gunakan 1 tag <a> dengan attribute target='_blank' ke salah satu sumber referensi terpercaya jika relevan.\n\n"
                . "INSTRUKSI PROMPT GAMBAR (WAJIB):\n"
                . "- Pada setiap sub-heading (<h2>) dan bagian atas artikel (cover), buatkan marker prompt gambar [Gambar: Deskripsi visual...].\n"
                . "- GAYA PROMPT GAMBAR: Subjek orang Indonesia natural & realistis (candid photo, ekspresi wajar santai, bukan pose kaku/3D AI sintetis), skema warna netral (neutral muted tones, earth tones tanpa oversaturation), look soft & natural, kontras normal tidak terlalu kuat (gentle tonal rolloff, bayangan lembut), sharpen normal to low (bebas oversharpening), tekstur kulit halus alami (smooth delicate natural skin pores), lighting alami/hangat (Grok Imagine style), ratio landscape 3:2.\n\n"
                . "Output HARUS JSON valid TANPA markdown dan TANPA teks pembuka/penutup. Format JSON:\n"
                . "{\n"
                . "  \"seo_title\": \"... (<= 60 karakter, judul viral bermartabat, tajam, high-CTR, menyempurnakan judul lama jika ada)\",\n"
                . "  \"focus_keyword\": \"... (1 kata kunci utama target ranking Google)\",\n"
                . "  \"secondary_keywords\": \"... (3-5 kata kunci turunan/LSI, pisahkan koma)\",\n"
                . "  \"meta_description\": \"... (140-155 karakter, ringkasan persuasif memicu klik)\",\n"
                . "  \"excerpt\": \"... (ringkas 1-2 kalimat menggugah rasa penasaran)\",\n"
                . "  \"content\": \"... (HTML body lengkap yang sudah disempurnakan sesuai seluruh instruksi di atas, tanpa <h1>)\",\n"
                . "  \"slug\": \"... (slug pendek SEO-friendly)\",\n"
                . "  \"has_faq\": true,\n"
                . "  \"sources\": [\"https://...\"]\n"
                . "}";

            $userPrompt = "Berikut adalah data artikel/topik yang perlu disempurnakan:\n";
            if ($title !== '') {
                $userPrompt .= "- Judul saat ini: {$title}\n";
            }
            if ($focusKeyword !== '') {
                $userPrompt .= "- Focus Keyword saat ini: {$focusKeyword}\n";
            }
            if ($secondaryKeywords !== '') {
                $userPrompt .= "- Secondary Keywords: {$secondaryKeywords}\n";
            }
            if ($excerpt !== '') {
                $userPrompt .= "- Excerpt / Ringkasan saat ini: {$excerpt}\n";
            }
            if ($topic !== '') {
                $userPrompt .= "- Topik / Catatan Tambahan: {$topic}\n";
            }
            if ($contentContext !== '') {
                $userPrompt .= "- Isi Konten Saat Ini (Draf/Lama):\n{$contentContext}\n";
            }

            $userPrompt .= "\nSilakan sempurnakan seluruh aspek artikel di atas (judul lebih menarik & tajam, excerpt memikat, struktur konten berbobot sesuai standar Ruang Lari, sesuaikan perlunya seksi FAQ atau tidak sesuai tipe konten, dan optimasi SEO menyeluruh).";

            $model = config('services.openai.blog_model') ?: config('services.openai.model') ?: 'gpt-6-astra';
            $response = $this->aiService->getAiResponseOrThrow($userPrompt, $systemPrompt, $model);

            $jsonStr = trim($response);
            $jsonStr = str_replace(["```json", "```"], '', $jsonStr);
            $jsonStr = trim($jsonStr);

            if (preg_match('/\{[\s\S]*\}/', $jsonStr, $matches)) {
                $jsonStr = $matches[0];
            }

            $data = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI returned invalid JSON format.',
                    'raw' => $response
                ], 500);
            }

            if (!empty($data['content']) && !empty($internalLinkTargets)) {
                $data['content'] = $this->internalLinkService->injectInternalLinks($data['content'], $internalLinkTargets, 3);
            }

            if (isset($data['slug'])) {
                $data['slug'] = Str::slug((string) $data['slug']);
            } elseif (isset($data['seo_title'])) {
                $data['slug'] = Str::slug((string) $data['seo_title']);
            }

            if (!isset($data['excerpt']) && isset($data['meta_description'])) {
                $data['excerpt'] = (string) $data['meta_description'];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        $categories = BlogCategory::all();
        $tags = BlogTag::all();

        return view('admin.blog.articles.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'excerpt_en' => 'nullable|string',
            'content' => 'required|string',
            'content_en' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'featured_image_url' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'new_tags' => 'nullable|string', // Comma separated new tags

            // SEO
            'meta_title' => 'nullable|string|max:255',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_description_en' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:255',
            'focus_keyword_en' => 'nullable|string|max:255',
            'secondary_keywords' => 'nullable|string|max:1000',
            'secondary_keywords_en' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_keywords_en' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
            'canonical_url_en' => 'nullable|url',
        ]);

        if (empty($validated['meta_keywords']) && (!empty($validated['focus_keyword']) || !empty($validated['secondary_keywords']))) {
            $validated['meta_keywords'] = implode(', ', array_filter([$validated['focus_keyword'] ?? null, $validated['secondary_keywords'] ?? null]));
        }

        if (empty($validated['meta_keywords_en']) && (!empty($validated['focus_keyword_en']) || !empty($validated['secondary_keywords_en']))) {
            $validated['meta_keywords_en'] = implode(', ', array_filter([$validated['focus_keyword_en'] ?? null, $validated['secondary_keywords_en'] ?? null]));
        }

        $validated['user_id'] = auth()->id();
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->filled('slug')) {
            $validated['slug'] = Str::slug($request->slug);
        } else {
            $validated['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('blog/featured', 'public');
            $validated['featured_image'] = $path;
        } elseif ($request->filled('featured_image_url')) {
            $validated['featured_image'] = $request->featured_image_url;
        }

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $categoryIds = array_values(array_unique(array_map('intval', $validated['categories'] ?? [])));
        $validated['category_id'] = $categoryIds[0] ?? null;
        unset($validated['categories']);

        $article = Article::create($validated);
        $article->categories()->sync($categoryIds);

        $tagIds = array_values(array_unique(array_map('intval', $validated['tags'] ?? [])));

        if ($request->filled('new_tags')) {
            $rawNames = collect(explode(',', (string) $request->new_tags))
                ->map(fn ($v) => trim((string) $v))
                ->filter(fn ($v) => $v !== '')
                ->values();

            if ($rawNames->isNotEmpty()) {
                $slugToName = $rawNames
                    ->mapWithKeys(fn ($name) => [Str::slug($name) => $name])
                    ->filter(fn ($name, $slug) => $slug !== '');

                $slugs = $slugToName->keys()->values()->all();

                if ($slugs) {
                    $existing = BlogTag::query()->whereIn('slug', $slugs)->pluck('id', 'slug')->all();

                    $now = now();
                    $toInsert = [];
                    foreach ($slugToName as $slug => $name) {
                        if (! isset($existing[$slug])) {
                            $toInsert[] = [
                                'name' => $name,
                                'slug' => $slug,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    if ($toInsert) {
                        BlogTag::query()->insertOrIgnore($toInsert);
                        $existing = BlogTag::query()->whereIn('slug', $slugs)->pluck('id', 'slug')->all();
                    }

                    $tagIds = array_merge($tagIds, array_values($existing));
                }
            }
        }

        $article->tags()->sync(array_values(array_unique($tagIds)));

        \Illuminate\Support\Facades\Cache::forget('home.featured_articles');

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $categories = BlogCategory::all();
        $tags = BlogTag::all();
        $articleTags = $article->tags->pluck('id')->toArray();

        $articleCategoryIds = $article->categories()->pluck('blog_categories.id')->toArray();

        return view('admin.blog.articles.edit', compact('article', 'categories', 'tags', 'articleTags', 'articleCategoryIds'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug,'.$article->id,
            'categories' => 'nullable|array',
            'categories.*' => 'exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'excerpt_en' => 'nullable|string',
            'content' => 'required|string',
            'content_en' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'featured_image_url' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'new_tags' => 'nullable|string',

            // SEO
            'meta_title' => 'nullable|string|max:255',
            'meta_title_en' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_description_en' => 'nullable|string|max:500',
            'focus_keyword' => 'nullable|string|max:255',
            'focus_keyword_en' => 'nullable|string|max:255',
            'secondary_keywords' => 'nullable|string|max:1000',
            'secondary_keywords_en' => 'nullable|string|max:1000',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_keywords_en' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
            'canonical_url_en' => 'nullable|url',
        ]);

        if (empty($validated['meta_keywords']) && (!empty($validated['focus_keyword']) || !empty($validated['secondary_keywords']))) {
            $validated['meta_keywords'] = implode(', ', array_filter([$validated['focus_keyword'] ?? null, $validated['secondary_keywords'] ?? null]));
        }

        if (empty($validated['meta_keywords_en']) && (!empty($validated['focus_keyword_en']) || !empty($validated['secondary_keywords_en']))) {
            $validated['meta_keywords_en'] = implode(', ', array_filter([$validated['focus_keyword_en'] ?? null, $validated['secondary_keywords_en'] ?? null]));
        }

        if ($request->filled('slug')) {
            $validated['slug'] = Str::slug($request->slug);
        } else {
            $validated['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('featured_image')) {
            if ($article->featured_image
                && ! Str::startsWith($article->featured_image, ['http://', 'https://'])
                && Storage::disk('public')->exists($article->featured_image)
            ) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $path = $request->file('featured_image')->store('blog/featured', 'public');
            $validated['featured_image'] = $path;
        } elseif ($request->filled('featured_image_url')) {
            if ($article->featured_image
                && ! Str::startsWith($article->featured_image, ['http://', 'https://'])
                && Storage::disk('public')->exists($article->featured_image)
            ) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $validated['featured_image'] = $request->featured_image_url;
        }

        if ($validated['status'] === 'published' && $article->status !== 'published') {
            $validated['published_at'] = now();
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        $categoryIds = array_values(array_unique(array_map('intval', $validated['categories'] ?? [])));
        $validated['category_id'] = $categoryIds[0] ?? null;
        unset($validated['categories']);

        $article->update($validated);
        $article->categories()->sync($categoryIds);

        $tagIds = array_values(array_unique(array_map('intval', $validated['tags'] ?? [])));

        if ($request->filled('new_tags')) {
            $rawNames = collect(explode(',', (string) $request->new_tags))
                ->map(fn ($v) => trim((string) $v))
                ->filter(fn ($v) => $v !== '')
                ->values();

            if ($rawNames->isNotEmpty()) {
                $slugToName = $rawNames
                    ->mapWithKeys(fn ($name) => [Str::slug($name) => $name])
                    ->filter(fn ($name, $slug) => $slug !== '');

                $slugs = $slugToName->keys()->values()->all();

                if ($slugs) {
                    $existing = BlogTag::query()->whereIn('slug', $slugs)->pluck('id', 'slug')->all();

                    $now = now();
                    $toInsert = [];
                    foreach ($slugToName as $slug => $name) {
                        if (! isset($existing[$slug])) {
                            $toInsert[] = [
                                'name' => $name,
                                'slug' => $slug,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }

                    if ($toInsert) {
                        BlogTag::query()->insertOrIgnore($toInsert);
                        $existing = BlogTag::query()->whereIn('slug', $slugs)->pluck('id', 'slug')->all();
                    }

                    $tagIds = array_merge($tagIds, array_values($existing));
                }
            }
        }

        $article->tags()->sync(array_values(array_unique($tagIds)));

        \Illuminate\Support\Facades\Cache::forget('home.featured_articles');

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        if ($article->featured_image
            && ! Str::startsWith($article->featured_image, ['http://', 'https://'])
            && Storage::disk('public')->exists($article->featured_image)
        ) {
            Storage::disk('public')->delete($article->featured_image);
        }
        $article->tags()->detach();
        $article->delete();

        \Illuminate\Support\Facades\Cache::forget('home.featured_articles');

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article deleted successfully.');
    }

    public function toggleFeatured(Article $article)
    {
        $article->update(['is_featured' => ! $article->is_featured]);
        \Illuminate\Support\Facades\Cache::forget('home.featured_articles');

        return response()->json([
            'success' => true,
            'is_featured' => (bool) $article->is_featured,
        ]);
    }
}
