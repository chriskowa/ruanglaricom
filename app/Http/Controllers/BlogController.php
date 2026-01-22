<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = $request->query('category');
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'latest');

        $categories = BlogCategory::query()
            ->select(['id', 'name', 'slug'])
            ->withCount([
                'articles as published_articles_count' => function ($q) {
                    $q->published();
                },
            ])
            ->orderByDesc('published_articles_count')
            ->orderBy('name')
            ->get();

        $articlesQuery = Article::query()
            ->published()
            ->with(['user:id,name', 'category:id,name,slug']);

        $activeCategory = null;
        if ($categorySlug) {
            $activeCategory = $categories->firstWhere('slug', $categorySlug);
            if ($activeCategory) {
                $articlesQuery->where('category_id', $activeCategory->id);
            }
        }

        if ($search !== '') {
            $articlesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('excerpt', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        if ($sort === 'popular') {
            $articlesQuery->orderByDesc('views_count')
                ->orderByRaw('COALESCE(published_at, created_at) DESC');
        } else {
            $articlesQuery->orderByRaw('COALESCE(published_at, created_at) DESC');
        }

        $articles = $articlesQuery->paginate(10)->withQueryString();

        $heroArticle = null;
        $canShowHero = ! $categorySlug && $search === '' && $sort !== 'popular';
        if ($canShowHero && (int) $articles->currentPage() === 1 && $articles->count() > 0) {
            $heroArticle = $articles->first();
            $articles->setCollection($articles->getCollection()->slice(1)->values());
        }

        $trending = Article::query()
            ->published()
            ->select(['id', 'title', 'slug', 'featured_image', 'views_count', 'published_at', 'created_at', 'category_id'])
            ->with(['category:id,name,slug'])
            ->orderByDesc('views_count')
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(5)
            ->get();

        if ($request->ajax()) {
            return view('blog.partials.results', compact('articles'));
        }

        return view('blog.index', compact(
            'categories',
            'activeCategory',
            'categorySlug',
            'search',
            'sort',
            'heroArticle',
            'articles',
            'trending'
        ));
    }

    public function show($slug)
    {
        $article = Article::where('slug', $slug)->published()->firstOrFail();

        $article->increment('views_count');
        
        $relatedArticles = Article::where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->published()
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('article', 'relatedArticles'));
    }
}
