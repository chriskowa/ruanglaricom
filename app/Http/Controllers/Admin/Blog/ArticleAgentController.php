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
        set_time_limit(180);
        ini_set('max_execution_time', 180);
    }

    /**
     * Step 1: Brainstorming - hasilkan 10 ide dari topik.
     */
    public function brainstorm(Request $request)
    {
        $request->validate([
            'topic'    => 'required|string|max:255',
            'strategy' => 'nullable|in:free,gap,cluster,formula',
        ]);

        try {
            $result = $this->service->step1_inputTopic($request->only('topic', 'strategy', 'site'));
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
}
