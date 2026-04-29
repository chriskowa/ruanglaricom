<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    protected $aiService;

    public function __construct(OpenAiService $aiService)
    {
        $this->aiService = $aiService;
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
        $request->validate([
            'topic' => 'required|string',
            'url' => 'nullable|url',
        ]);

        $topic = $request->topic;
        $url = $request->url;
        
        $systemPrompt = "Anda adalah penulis SEO senior (Bahasa Indonesia) untuk Ruang Lari.\n\n"
            . "Aturan:\n"
            . "- Tulis unik (parafrase total), tidak plagiarisme.\n"
            . "- Factual: jangan mengarang data/statistik. Jika menyebut angka/klaim penting, sertakan URL sumber pada field sources.\n"
            . "- SEO 2026-friendly: fokus intent, E-E-A-T, dan keterbacaan mobile.\n"
            . "- Struktur: JANGAN gunakan <h1> di content (judul halaman sudah H1). Mulai dari <h2>/<h3>. Paragraf 2–4 kalimat.\n"
            . "- HTML saja untuk content (pakai <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <blockquote>, <table>).\n"
            . "- Jika URL referensi diberikan tetapi Anda tidak bisa mengakses isinya, jangan mengklaim sudah membaca URL tersebut; tetap tulis artikel original berdasarkan topik.\n\n"
            . "Input:\n"
            . "- Topik: {$topic}\n"
            . ($url ? "- URL referensi: {$url}\n" : "")
            . "\nOutput HARUS JSON valid TANPA markdown dan TANPA teks lain. Format:\n"
            . "{\n"
            . "  \"seo_title\": \"... (<= 60 karakter)\",\n"
            . "  \"keywords\": \"... (utama + 3-5 LSI)\",\n"
            . "  \"meta_description\": \"... (140-160 karakter)\",\n"
            . "  \"excerpt\": \"... (ringkas 1-2 kalimat)\",\n"
            . "  \"content\": \"... (HTML body, tanpa <h1>)\",\n"
            . "  \"slug\": \"... (slug pendek)\",\n"
            . "  \"sources\": [\"https://...\"]\n"
            . "}";

        try {
            $userPrompt = "Topik: {$topic}" . ($url ? "\nURL referensi: {$url}" : "");
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

        } catch (\Exception $e) {
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
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'featured_image_url' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'new_tags' => 'nullable|string', // Comma separated new tags

            // SEO
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
        ]);

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

        $article = Article::create($validated);

        // Handle Tags
        $tagIds = $validated['tags'] ?? [];

        // Handle new tags
        if ($request->filled('new_tags')) {
            $newTagNames = explode(',', $request->new_tags);
            foreach ($newTagNames as $tagName) {
                $tagName = trim($tagName);
                if ($tagName) {
                    $tag = BlogTag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }

        $article->tags()->sync(array_unique($tagIds));

        \Illuminate\Support\Facades\Cache::forget('home.featured_articles');

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article created successfully.');
    }

    public function edit(Article $article)
    {
        $categories = BlogCategory::all();
        $tags = BlogTag::all();
        $articleTags = $article->tags->pluck('id')->toArray();

        return view('admin.blog.articles.edit', compact('article', 'categories', 'tags', 'articleTags'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug,'.$article->id,
            'category_id' => 'nullable|exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'featured_image_url' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:blog_tags,id',
            'new_tags' => 'nullable|string',

            // SEO
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
        ]);

        if ($request->filled('slug')) {
            $validated['slug'] = Str::slug($request->slug);
        } else {
            $validated['slug'] = Str::slug($request->title);
        }

        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($article->featured_image && Storage::disk('public')->exists($article->featured_image)) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $path = $request->file('featured_image')->store('blog/featured', 'public');
            $validated['featured_image'] = $path;
        } elseif ($request->filled('featured_image_url')) {
            // If switching to URL, we might want to delete the old local file if it exists
            if ($article->featured_image && Storage::disk('public')->exists($article->featured_image)) {
                Storage::disk('public')->delete($article->featured_image);
            }
            $validated['featured_image'] = $request->featured_image_url;
        }

        if ($validated['status'] === 'published' && $article->status !== 'published') {
            $validated['published_at'] = now();
        }

        $validated['is_featured'] = $request->boolean('is_featured');

        $article->update($validated);

        // Handle Tags
        $tagIds = $validated['tags'] ?? [];

        if ($request->filled('new_tags')) {
            $newTagNames = explode(',', $request->new_tags);
            foreach ($newTagNames as $tagName) {
                $tagName = trim($tagName);
                if ($tagName) {
                    $tag = BlogTag::firstOrCreate(
                        ['slug' => Str::slug($tagName)],
                        ['name' => $tagName]
                    );
                    $tagIds[] = $tag->id;
                }
            }
        }

        $article->tags()->sync(array_unique($tagIds));

        \Illuminate\Support\Facades\Cache::forget('home.featured_articles');

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        if ($article->featured_image && Storage::disk('public')->exists($article->featured_image)) {
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
