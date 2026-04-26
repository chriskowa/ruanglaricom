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
        
        $systemPrompt = "Tugas: Buat artikel SEO berkualitas tinggi berdasarkan input berikut. Artikel harus: 
 1. **Unik dan faktual**: Parafrase total atau tulis ulang, susun ulang kalimat, struktur baru. Semua klaim harus **divalidasi dari sumber terpercaya** (jurnal, gov, edu, atau situs resmi industri terbaru). 
 2. **SEO 2026-friendly**: Optimasi untuk Google terbaru, fokus E-A-T, user intent, semantic / LSI keywords, mobile-friendly. 
 3. **Terstruktur**: H1 untuk judul, H2–H3 untuk subtopik, bullet/numbered list bila perlu, paragraf pendek (2–4 kalimat). 
 4. **Engaging & mudah dibaca**: Bahasa jelas, formal atau sesuai target audiens, tetap menarik. 

 **Input**: 
 - Keyword / Topik utama: " . $topic . ($url ? "\n - Rewrite dari URL: " . $url : "") . "

 **Langkah tambahan**: 
 - **Cek fakta**: sebelum menulis, gunakan data terbaru dari sumber terpercaya (jurnal, situs gov, edu, organisasi internasional, artikel 5 tahun terakhir).  
 - **Catat sumber**: bila memungkinkan, sertakan link/referensi yang digunakan. 

 **Output yang harus dihasilkan dalam format JSON**: 
 {
  \"seo_title\": \"Judul unik, mengandung keyword utama, max 60 karakter\",
  \"keywords\": \"Keyword utama + 3–5 semantic / LSI keywords\",
  \"meta_description\": \"150–160 karakter, mengandung keyword utama\",
  \"content\": \"Artikel lengkap 800–2000 kata dalam format HTML (gunakan H1, H2, H3, p, ul, li)\",
  \"slug\": \"URL pendek, SEO-friendly\"
 }

 **Instruksi tambahan**: 
 - Jika output kurang sesuai, perbaiki dengan menambah fakta, contoh terbaru, atau data statistik untuk meningkatkan kredibilitas. 
 - Hindari informasi yang tidak dapat diverifikasi atau spekulatif. 
 - Struktur konten harus logis dan mudah dipindai pembaca. 

 **Catatan**: Hasil harus siap dipublikasikan sebagai artikel SEO 2026, unik, faktual, teroptimasi penuh, dan valid secara ilmiah atau resmi. 
 Sertakan hanya valid JSON dalam jawaban Anda.";

        try {
            // Using gpt-4o as requested
            $response = $this->aiService->getAiResponse("Generate article about: " . $topic, $systemPrompt, 'gpt-4o');

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI did not return any content.'
                ], 500);
            }

            $jsonStr = $response;
            if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
                $jsonStr = $matches[1];
            } elseif (preg_match('/```\s*(.*?)\s*```/s', $response, $matches)) {
                $jsonStr = $matches[1];
            }

            $data = json_decode($jsonStr, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI returned invalid JSON format.',
                    'raw' => $response
                ], 500);
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

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article updated successfully.');
    }

    public function destroy(Article $article)
    {
        if ($article->featured_image && Storage::disk('public')->exists($article->featured_image)) {
            Storage::disk('public')->delete($article->featured_image);
        }
        $article->tags()->detach();
        $article->delete();

        return redirect()->route('admin.blog.articles.index')->with('success', 'Article deleted successfully.');
    }
}
