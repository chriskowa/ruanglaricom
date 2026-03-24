<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageTemplate;
use App\Models\Event;
use App\Models\HomepageContent;
use App\Models\Menu;
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

        // Get homepage template or default
        $homepageTemplate = PageTemplate::homepage()->active()->first();
        
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

        $leaderboard = Cache::remember('home.leaderboard.data', 3600, function () use ($stravaService) {
            try {
                $data = $stravaService->getLeaderboard();
                return (is_array($data) && ($data['fastest'] || $data['distance'] || $data['elevation'])) ? $data : null;
            } catch (\Throwable $e) {
                return null;
            }
        });

        if (!$leaderboard) {
            $leaderboard = Cache::get('home.leaderboard.last');
        } else {
            Cache::forever('home.leaderboard.last', $leaderboard);
            Cache::forever('home.leaderboard.last_at', now()->toISOString());
        }

        return view('home.index', [
            'homepageContent' => $homepageContent,
            'featuredEvent' => $featuredEvent,
            'leaderboard' => $leaderboard,
            'primaryMenu' => $primaryMenu,
        ]);
    }
}