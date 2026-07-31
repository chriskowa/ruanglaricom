<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\ArticleResource;
use App\Http\Resources\Api\V1\BlogCategoryResource;
use App\Models\Article;
use App\Models\BlogCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleApiController extends BaseApiController
{
    /**
     * Browse published blog articles with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::query()
            ->published()
            ->with(['category']);

        // Category filter
        if ($request->filled('category')) {
            $cat = trim($request->category);
            if (is_numeric($cat)) {
                $query->where('category_id', (int) $cat);
            } else {
                $query->whereHas('category', fn ($q) => $q->where('slug', $cat));
            }
        }

        // Featured filter
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Search filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Order strictly by latest published date
        $articles = $query->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate($request->get('per_page', 10));

        return $this->successResponse([
            'articles' => ArticleResource::collection($articles),
            'pagination' => [
                'total' => $articles->total(),
                'per_page' => $articles->perPage(),
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
            ],
        ], 'Daftar artikel blog berhasil dimuat');
    }

    /**
     * Get top latest published articles (for home/discovery)
     */
    public function latest(Request $request): JsonResponse
    {
        $limit = max(1, min(20, (int) $request->get('limit', 5)));

        $articles = Article::query()
            ->published()
            ->with(['category'])
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->take($limit)
            ->get();

        return $this->successResponse(ArticleResource::collection($articles), 'Artikel terbaru berhasil dimuat');
    }

    /**
     * Get featured blog articles (for hero slider)
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = max(1, min(10, (int) $request->get('limit', 5)));

        $articles = Article::query()
            ->published()
            ->where('is_featured', true)
            ->with(['category'])
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->take($limit)
            ->get();

        return $this->successResponse(ArticleResource::collection($articles), 'Artikel unggulan berhasil dimuat');
    }

    /**
     * Get trending / most popular articles (ordered by views count)
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = max(1, min(20, (int) $request->get('limit', 5)));

        $articles = Article::query()
            ->published()
            ->with(['category'])
            ->orderByDesc('views_count')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->take($limit)
            ->get();

        return $this->successResponse(ArticleResource::collection($articles), 'Artikel terpopuler berhasil dimuat');
    }

    /**
     * Get all blog categories
     */
    public function categories(Request $request): JsonResponse
    {
        $categories = BlogCategory::withCount(['articles' => fn ($q) => $q->published()])
            ->orderBy('name', 'asc')
            ->get();

        return $this->successResponse(BlogCategoryResource::collection($categories), 'Kategori blog berhasil dimuat');
    }

    /**
     * Get full details of an article by slug or ID
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $article = Article::where('slug', $slug)
            ->orWhere('id', $slug)
            ->published()
            ->with(['category'])
            ->first();

        if (! $article) {
            return $this->errorResponse('Artikel tidak ditemukan.', 404);
        }

        // Increment views count
        $article->increment('views_count');

        // Fetch related articles from same category
        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category_id', $article->category_id)
            ->take(3)
            ->get();

        return $this->successResponse([
            'article' => new ArticleResource($article),
            'related_articles' => ArticleResource::collection($related),
        ], 'Detail artikel berhasil dimuat');
    }
}
