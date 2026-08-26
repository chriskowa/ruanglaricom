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

    public function index()
    {
        $articles = Article::with('category', 'user')->latest()->paginate(10);

        return view('admin.blog.articles.index', compact('articles'));
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
            
            $systemPrompt = "Anda adalah jurnalis dan penulis SEO/Google Discover senior (Bahasa Indonesia) untuk Ruang Lari dengan gaya penulisan berita faktual, lugas, dan mendalam seperti Kompas.com.\n\n"
                . "Aturan Penulisan Berita & Google Discover 2026:\n"
                . "- Faktual & Berimbang: Tulislah berita/artikel dengan gaya jurnalistik faktual (5W+1H pada lead berita). Jangan mengarang data/hoaks. Jika ada cuplikan berita dari Threads/Instagram/Media, olah menjadi liputan jurnalistik yang terstruktur, rapi, dan bersumber.\n"
                . "- ATURAN MUTLAK JUDUL & GOOGLE DISCOVER:\n"
                . "  1. 100% Content Match: Judul wajib selaras mutlak dan mencerminkan substansi tulisan secara jujur.\n"
                . "  2. Larangan Curiosity Gap Menipu: Jangan sembunyikan informasi kunci demi memicu klik (DILARANG: 'Ternyata Ini...', 'Gak Nyangka...', 'Inilah Alasannya...', 'Rahasia Terbesar...').\n"
                . "  3. Larangan Frasa Hiperbola: DILARANG menggunakan kata sensasional (DILARANG: 'Bikin Gempar', 'Bikin Melongo', 'Bikin Syok', 'Wajib Tahu!', 'Heboh').\n"
                . "  4. Standar E-E-A-T & Kredibilitas: Judul jelas, informatif, dan berbobot dengan entitas spesifik.\n"
                . "- Struktur HTML: JANGAN gunakan <h1> di content (judul halaman sudah H1). Gunakan <h2> dan <h3>. Paragraf 2–4 kalimat. Gunakan <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <blockquote>, <table>.\n"
                . ($internalLinkInstruction !== '' ? "{$internalLinkInstruction}\n\n" : '')
                . "- Jika URL referensi diberikan tetapi Anda tidak bisa mengakses isinya, jangan mengklaim sudah membaca URL tersebut; tetap tulis artikel original berdasarkan topik.\n\n"
                . "INSTRUKSI PROMPT GAMBAR (WAJIB):\n"
                . "- Pada setiap sub-heading (<h2>) dan bagian atas artikel (cover), buatkan marker prompt gambar [Gambar: Deskripsi visual...].\n"
                . "- GAYA PROMPT GAMBAR: Subjek orang Indonesia natural & realistis (candid photo, ekspresi wajar santai, bukan pose kaku/3D AI sintetis), skema warna netral (neutral muted tones, earth tones tanpa oversaturation), look soft & natural, kontras normal tidak terlalu kuat (gentle tonal rolloff, bayangan lembut), sharpen normal to low (bebas oversharpening), tekstur kulit halus alami (smooth delicate natural skin pores), lighting alami/hangat (Grok Imagine style), ratio landscape 3:2.\n\n"
                . "Input:\n"
                . "- Topik / Berita Realtime: {$topic}\n"
                . ($url ? "- URL referensi: {$url}\n" : "")
                . "Output HARUS JSON valid TANPA markdown dan TANPA teks lain. Format:\n"
                . "{\n"
                . "  \"seo_title\": \"... (<= 60 karakter, informatif, patuhi aturan anti-clickbait Google Discover)\",\n"
                . "  \"focus_keyword\": \"... (1 kata kunci utama target ranking)\",\n"
                . "  \"secondary_keywords\": \"... (3-5 kata kunci turunan/LSI, pisahkan koma)\",\n"
                . "  \"meta_description\": \"... (140-160 karakter, ringkasan faktual tanpa clickbait)\",\n"
                . "  \"excerpt\": \"... (ringkas 1-2 kalimat)\",\n"
                . "  \"content\": \"... (HTML body, tanpa <h1>)\",\n"
                . "  \"slug\": \"... (slug pendek)\",\n"
                . "  \"sources\": [\"https://...\"]\n"
                . "}";

            $userPrompt = "Topik / Berita Realtime: {$topic}" . ($url ? "\nURL referensi: {$url}" : "");
            $model = config('services.openai.blog_model') ?: config('services.openai.model') ?: 'gpt-4o';
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
