<?php

namespace App\Http\Controllers\Admin\Blog;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Admin\AdminArticleAgentService;
use Illuminate\Http\Request;

class ArticleAgentController extends Controller
{
    protected AdminArticleAgentService $service;

    public function __construct(AdminArticleAgentService $service)
    {
        $this->service = $service;

        // Langkah agent memanggil LLM (brainstorm/research/write) butuh waktu > 30s.
        // Naikkan batas eksekusi agar request web tidak timeout.
        set_time_limit(240);
        ini_set('max_execution_time', 240);
    }

    /**
     * Step 1: Brainstorming - hasilkan 10 ide dari topik.
     */
    public function brainstorm(Request $request)
    {
        $request->validate([
            'topic'    => 'nullable|string|max:255',
            'raw_news' => 'nullable|string|max:20000',
            'strategy' => 'nullable|in:free,gap,cluster,formula',
        ]);

        if (empty($request->topic) && empty($request->raw_news)) {
            return response()->json(['success' => false, 'message' => 'Topic or raw news content is required.'], 422);
        }

        try {
            $result = $this->service->step1_inputTopic($request->only('topic', 'raw_news', 'strategy', 'site'));
            return response()->json(['success' => true, ...$result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 2: Pilih ide / input manual lalu research.
     */
    public function research(Request $request)
    {
        $request->validate([
            'uuid'            => 'nullable|string',
            'selection'       => 'required|array',
            'selection.title' => 'required|string',
            'selection.keyword' => 'required|string',
            'research_manual' => 'nullable|boolean',
        ]);

        try {
            $result = $this->service->step2_selectAndResearch($request->only('uuid', 'selection', 'research_manual'));
            return response()->json(['success' => true, ...$result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 3: Generate artikel dari research summary.
     */
    public function write(Request $request)
    {
        $request->validate([
            'uuid'             => 'required|string',
            'research_summary' => 'nullable|string',
        ]);

        try {
            $result = $this->service->step3_doWrite($request->only('uuid', 'research_summary'));
            return response()->json(['success' => true, ...$result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Step 3b: Generate versi EN dari artikel ID yang sudah dibuat.
     */
    public function writeEn(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string',
        ]);

        try {
            $result = $this->service->step3_doWriteEn($request->uuid);
            return response()->json(['success' => true, ...$result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Terjemahkan konten ID (dari form) ke EN secara langsung.
     */
    public function translate(Request $request)
    {
        $request->validate([
            'title'            => 'nullable|string',
            'excerpt'          => 'nullable|string',
            'content'          => 'nullable|string',
            'meta_title'       => 'nullable|string',
            'meta_description' => 'nullable|string',
            'meta_keywords'    => 'nullable|string',
        ]);

        try {
            $result = $this->service->translateToEn($request->only(
                'title', 'excerpt', 'content', 'meta_title', 'meta_description', 'meta_keywords'
            ));
            return response()->json(['success' => true, ...$result]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Terapkan hasil agent ke Article (create baru atau update existing).
     */
    public function apply(Request $request)
    {
        $request->validate([
            'uuid'            => 'required|string',
            'article_id'      => 'nullable|integer|exists:articles,id',
            'content_override' => 'nullable|string',
        ]);

        try {
            $article = $this->service->applyToArticle(
                $request->uuid,
                $request->article_id,
                $request->filled('content_override') ? $request->content_override : null
            );
            return response()->json([
                'success' => true,
                'article_id' => $article->id,
                'redirect' => route('admin.blog.articles.edit', $article),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cari kandidat gambar dari berbagai provider (Tavily, Google, Unsplash, DALL-E).
     */
    public function searchImages(Request $request, \App\Services\Blog\ArticleImageFetcherService $fetcher)
    {
        $request->validate([
            'query'    => 'required|string|max:255',
            'provider' => 'nullable|string|in:auto,tavily,unsplash,google,dalle',
            'limit'    => 'nullable|integer|min:1|max:10',
        ]);

        try {
            $candidates = $fetcher->search($request->input('query'), $request->input('provider', 'auto'), (int) $request->input('limit', 5));
            return response()->json([
                'success'    => true,
                'candidates' => $candidates,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Download gambar kandidat pilihan, konversi WebP, masukkan ke Media Library, dan pasang ke artikel.
     */
    public function attachImage(Request $request, \App\Services\Blog\ArticleImageFetcherService $fetcher)
    {
        $request->validate([
            'uuid'      => 'required|string',
            'marker'    => 'required|string',
            'image_url' => 'required|url',
            'keyword'   => 'required|string|max:255',
        ]);

        try {
            $session = \App\Models\ArticleAgent::findOrFail($request->uuid);
            $generated = $session->generated_article_content;
            if (!is_array($generated)) {
                $generated = json_decode((string) $generated, true) ?? [];
            }

            $content = $generated['content'] ?? '';
            if (empty($content)) {
                return response()->json(['success' => false, 'message' => 'Konten artikel belum digenerate.'], 422);
            }

            $downloadResult = $fetcher->downloadAndProcess($request->image_url, $request->keyword, auth()->id());

            $imgHtml = '<figure class="my-6">' .
                '<img src="' . htmlspecialchars($downloadResult['url']) . '" ' .
                'alt="' . htmlspecialchars($downloadResult['alt']) . '" ' .
                'title="' . htmlspecialchars($downloadResult['title']) . '" ' .
                'loading="lazy" class="w-full rounded-xl shadow-md">' .
                '</figure>';

            $updatedContent = str_replace($request->marker, $imgHtml, $content);
            $generated['content'] = $updatedContent;

            // Jika cover belum ada, set ini sebagai featured_image
            if (empty($generated['featured_image'])) {
                $generated['featured_image'] = $downloadResult['relative_path'];
            }

            $session->update(['generated_article_content' => json_encode($generated)]);

            return response()->json([
                'success'         => true,
                'image'           => $downloadResult,
                'img_html'        => $imgHtml,
                'updated_content' => $updatedContent,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Ambil otomatis semua gambar per marker, konversi ke WebP, daftarkan ke Media Library, dan ganti di artikel.
     */
    public function autoFetchAllImages(Request $request, \App\Services\Blog\ArticleImageFetcherService $fetcher)
    {
        $request->validate([
            'uuid'     => 'required|string',
            'provider' => 'nullable|string|in:auto,tavily,unsplash,google,dalle',
        ]);

        try {
            $result = $fetcher->autoFetchAllForSession($request->uuid, $request->input('provider', 'auto'));
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Download an online image, convert to WebP, register to Media Library, and return for Featured Image.
     */
    public function fetchFeaturedImage(Request $request, \App\Services\Blog\ArticleImageFetcherService $fetcher)
    {
        $request->validate([
            'image_url' => 'required|url',
            'keyword'   => 'nullable|string',
        ]);

        try {
            $keyword = $request->input('keyword') ?: 'featured-image';
            $downloadResult = $fetcher->downloadAndProcess($request->image_url, $keyword, auth()->id());

            return response()->json([
                'success' => true,
                'image'   => $downloadResult,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

