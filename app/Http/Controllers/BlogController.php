<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BlogController extends Controller
{
    public function index(Request $request, ?BlogCategory $category = null)
    {
        $categorySlug = $category ? $category->slug : $request->query('category');
        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'latest');

        $categories = Cache::remember('blog.categories_with_count_v1', 300, function () {
            return BlogCategory::query()
                ->select(['id', 'name', 'slug', 'description', 'meta_title', 'meta_description'])
                ->whereHas('articles', function ($q) {
                    $q->published();
                })
                ->withCount([
                    'articles as published_articles_count' => function ($q) {
                        $q->published();
                    },
                ])
                ->orderByDesc('published_articles_count')
                ->orderBy('name')
                ->get();
        });

        $articlesQuery = Article::query()
            ->published()
            ->select([
                'id',
                'user_id',
                'category_id',
                'title',
                'title_en',
                'slug',
                'excerpt',
                'excerpt_en',
                DB::raw('LEFT(content, 400) as content'),
                DB::raw('LEFT(content_en, 400) as content_en'),
                'featured_image',
                'views_count',
                'published_at',
                'created_at',
            ])
            ->with(['user:id,name', 'category:id,name,slug']);

        $activeCategory = $category;
        if (!$activeCategory && $categorySlug) {
            $activeCategory = $categories->firstWhere('slug', $categorySlug);
        }

        if ($activeCategory) {
            $articlesQuery->whereHas('categories', function ($q) use ($activeCategory) {
                $q->where('blog_categories.id', $activeCategory->id);
            });
        }

        if ($search !== '') {
            $articlesQuery->where(function ($q) use ($search) {
                if (app()->getLocale() === 'en') {
                    $q->where('title_en', 'like', '%'.$search.'%')
                        ->orWhere('excerpt_en', 'like', '%'.$search.'%')
                        ->orWhere('content_en', 'like', '%'.$search.'%')
                        ->orWhere('title', 'like', '%'.$search.'%')
                        ->orWhere('excerpt', 'like', '%'.$search.'%')
                        ->orWhere('content', 'like', '%'.$search.'%');
                } else {
                    $q->where('title', 'like', '%'.$search.'%')
                        ->orWhere('excerpt', 'like', '%'.$search.'%')
                        ->orWhere('content', 'like', '%'.$search.'%');
                }
            });
        }

        if ($sort === 'popular') {
            $articlesQuery->orderByDesc('views_count')
                ->orderByRaw('COALESCE(published_at, created_at) DESC');
        } else {
            $articlesQuery->orderByRaw('COALESCE(published_at, created_at) DESC');
        }

        $articles = $articlesQuery->paginate(12)->withQueryString();

        if ($request->ajax()) {
            return view('blog.partials.results', compact('articles'));
        }

        $trending = Cache::remember('blog.trending_v1', 300, function () {
            return Article::query()
                ->published()
                ->select(['id', 'title', 'title_en', 'slug', 'featured_image', 'views_count', 'published_at', 'created_at', 'category_id'])
                ->with(['category:id,name,slug'])
                ->orderByDesc('views_count')
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->limit(6)
                ->get();
        })->take(5);

        // Build Clean SEO Metadata & Canonical URL
        if ($activeCategory) {
            $canonicalUrl = route('blog.category', $activeCategory->slug);
            $metaTitle = $activeCategory->meta_title ?: "Artikel & Panduan {$activeCategory->name} | Ruang Lari";
            $metaDescription = $activeCategory->meta_description ?: ($activeCategory->description ?: "Kumpulan artikel edukasi, berita, dan tips latihan lari seputar {$activeCategory->name} di Ruang Lari.");
            $metaKeywords = "blog {$activeCategory->name}, artikel {$activeCategory->name}, panduan {$activeCategory->name}, lari indonesia";
            $pageHeading = "Artikel & Panduan " . $activeCategory->name;
            $pageSubheading = $activeCategory->description ?: "Kumpulan panduan terstruktur, tips, dan wawasan seputar {$activeCategory->name} untuk pelari Indonesia.";
        } else {
            $canonicalUrl = route('blog.index');
            $metaTitle = "Warta, Panduan & Berita Lari Indonesia | Ruang Lari";
            $metaDescription = "Portal media lari Indonesia: tips latihan, panduan rute, review sepatu, info event & race, dan wawasan komunitas pelari terpercaya.";
            $metaKeywords = "blog lari, berita lari, panduan lari pemula, tips marathon, sepatu lari, kalender lari indonesia, ruang lari";
            $pageHeading = "Warta, Panduan & Berita Lari";
            $pageSubheading = "Berita teraktual, tips latihan terstruktur, dan wawasan komunitas pelari Indonesia.";
        }

        $isSearch = !empty($search);

        return view('blog.index', compact(
            'categories',
            'activeCategory',
            'categorySlug',
            'search',
            'sort',
            'articles',
            'trending',
            'canonicalUrl',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'pageHeading',
            'pageSubheading',
            'isSearch'
        ));
    }

    public function category(Request $request, $slug)
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();
        return $this->index($request, $category);
    }

    public function show(Request $request, $slug)
    {
        $requestedLang = strtolower((string) $request->query('lang', ''));
        if (in_array($requestedLang, ['id', 'en'], true)) {
            app()->setLocale($requestedLang);
            $request->session()->put('locale', $requestedLang);
        } else {
            $sessionLang = strtolower((string) $request->session()->get('locale', 'id'));
            if (in_array($sessionLang, ['id', 'en'], true)) {
                app()->setLocale($sessionLang);
            }
        }

        $article = Article::with(['user:id,name', 'category:id,name,slug', 'tags:id,name,slug'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $viewedKey = 'blog_viewed_' . $article->id;
        if (! $request->session()->has($viewedKey)) {
            $article->increment('views_count');
            $request->session()->put($viewedKey, true);
        }

        $relatedArticles = Article::query()
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->published()
            ->select([
                'id',
                'category_id',
                'title',
                'title_en',
                'slug',
                'excerpt',
                'excerpt_en',
                DB::raw('LEFT(content, 300) as content'),
                DB::raw('LEFT(content_en, 300) as content_en'),
                'featured_image',
                'published_at',
                'created_at',
            ])
            ->with(['category:id,name,slug'])
            ->latest('published_at')
            ->limit(3)
            ->get();

        $trending = Cache::remember('blog.trending_v1', 300, function () {
            return Article::query()
                ->published()
                ->select(['id', 'title', 'title_en', 'slug', 'featured_image', 'views_count', 'published_at', 'created_at', 'category_id'])
                ->with(['category:id,name,slug'])
                ->orderByDesc('views_count')
                ->orderByRaw('COALESCE(published_at, created_at) DESC')
                ->limit(6)
                ->get();
        })->where('id', '!=', $article->id)->take(5)->values();

        $upcomingEvents = Cache::remember('blog.sidebar_upcoming_events_v1', 600, function () {
            $events = \App\Models\Event::query()
                ->select(['id', 'name', 'slug', 'start_at', 'city_id', 'location_name'])
                ->where('is_active', true)
                ->where('start_at', '>=', now()->startOfDay())
                ->orderBy('start_at', 'asc')
                ->with(['city:id,name'])
                ->limit(4)
                ->get();

            if ($events->isEmpty()) {
                $events = \App\Models\Event::query()
                    ->select(['id', 'name', 'slug', 'start_at', 'city_id', 'location_name'])
                    ->where('is_active', true)
                    ->orderBy('start_at', 'desc')
                    ->with(['city:id,name'])
                    ->limit(4)
                    ->get();
            }

            return $events;
        });

        return view('blog.show', compact('article', 'relatedArticles', 'trending', 'upcomingEvents'));
    }
}
