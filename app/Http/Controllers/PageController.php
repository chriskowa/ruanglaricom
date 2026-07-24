<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageTemplate;
use App\Models\Event;
use App\Models\HomepageContent;
use App\Models\Menu;
use App\Models\Article;
use App\Services\StravaClubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();
        
        if ($page->template_id && $template = $page->template) {
            // Use template-specific view
            return view($template->view_path, compact('page'));
        }

        // Fallback to default page view
        return view('pages.show', compact('page'));
    }

    public function homepage(StravaClubService $stravaService)
    {
        // Get primary navigation menu
        $primaryMenu = Cache::remember('primary_navigation', 3600, function () {
            return Menu::where('location', 'primary_navigation')->with('items.children')->first();
        });

        // Get homepage template or default (cached 30 min)
        $homepageTemplate = Cache::remember('home.page_template', 1800, function () {
            return PageTemplate::homepage()->active()->first();
        });
        
        $featuredEvents = Cache::remember('home.featured_events', 300, function () {
            return Event::query()
                ->where('is_featured', true)
                ->where('status', 'published')
                ->where('start_at', '>=', now())
                ->orderBy('start_at', 'asc')
                ->with('user')
                ->take(3)
                ->get();
        });

        if ($homepageTemplate) {
            $page = Page::published()
                ->where('template_id', $homepageTemplate->id)
                ->first();
                
            if ($page) {
                return view($homepageTemplate->view_path, [
                    'page' => $page,
                    'featuredEvents' => $featuredEvents,
                    'primaryMenu' => $primaryMenu,
                    'skipHeavyAssets' => true,
                ]);
            }
        }

        // Fallback to original home view logic from HomeController
        $homepageContent = Cache::remember('home.content', 3600, function () {
            return HomepageContent::first();
        });

        $featuredEvent = $featuredEvents->first() ?? Cache::remember('home.featured_event_fallback', 300, function () {
            return Event::query()
                ->where('is_featured', true)
                ->orderBy('start_at', 'desc')
                ->with('user')
                ->first();
        });

        $featuredArticles = Cache::remember('home.featured_articles', 300, function () {
            return Article::query()
                ->published()
                ->select(['id', 'title', 'slug', 'featured_image', 'views_count', 'published_at', 'created_at', 'updated_at', 'category_id', 'is_featured'])
                ->with(['category:id,name,slug'])
                ->orderByDesc('is_featured')
                ->orderByRaw('COALESCE(updated_at, published_at, created_at) DESC')
                ->limit(4)
                ->get();
        });



        return view('home.index', [
            'homepageContent' => $homepageContent,
            'featuredEvent'   => $featuredEvent,
            'featuredEvents'  => $featuredEvents,
            'featuredArticles' => $featuredArticles,
            'leaderboard'     => null, // Loaded asynchronously via AJAX
            'primaryMenu'     => $primaryMenu,
            'skipHeavyAssets' => true,
        ]);
    }
}
