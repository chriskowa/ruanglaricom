<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\HomepageContent;
use App\Models\Page;
use App\Models\PageTemplate;
use Carbon\Carbon;
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

    public function homepage()
    {
        // Check if custom page template is configured for home
        $homepageTemplate = Cache::remember('home.page_template', 1800, function () {
            return PageTemplate::homepage()->active()->first();
        });

        if ($homepageTemplate) {
            $page = Cache::remember('home.page_for_template_' . $homepageTemplate->id, 1800, function () use ($homepageTemplate) {
                return Page::published()
                    ->where('template_id', $homepageTemplate->id)
                    ->first();
            });
                
            if ($page) {
                return view($homepageTemplate->view_path, [
                    'page'            => $page,
                    'skipHeavyAssets' => true,
                ]);
            }
        }

        // 1. Homepage editable contents (hero slides, athlete photos, badges)
        $homepageContent = Cache::remember('home.content', 3600, function () {
            return HomepageContent::first();
        });

        // 2. Warta Pilihan (Top 5 curated cards: events + featured editorial)
        $heroHighlights = Cache::remember('home.hero_highlights_v2', 300, function () {
            $highlights = collect();

            // Featured Events (up to 3 upcoming)
            $events = Event::query()
                ->published()
                ->upcoming()
                ->where('is_featured', true)
                ->take(3)
                ->get();

            if ($events->isEmpty()) {
                $events = Event::query()
                    ->published()
                    ->where('is_featured', true)
                    ->orderBy('start_at', 'desc')
                    ->take(1)
                    ->get();
            }

            foreach ($events as $ev) {
                $evImg = method_exists($ev, 'getHeroImageUrl') ? $ev->getHeroImageUrl() : null;
                if (empty($evImg) && !empty($ev->hero_image)) {
                    $evImg = asset($ev->hero_image);
                }
                if (empty($evImg)) {
                    $evImg = 'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp';
                }

                $highlights->push([
                    'type'     => 'event',
                    'tag'      => 'RACE',
                    'meta'     => $ev->city ?? ($ev->location_name ?? 'Indonesia'),
                    'title'    => $ev->name ?? $ev->title,
                    'date'     => optional($ev->start_at)->translatedFormat('d M Y') ?? 'Segera Dibuka',
                    'url'      => route('events.show', $ev->slug ?? $ev->id),
                    'cta'      => 'Detail Race',
                    'image'    => $evImg,
                    'is_event' => true,
                ]);
            }

            // Featured Articles (fill up to 5 slots)
            $slotsLeft = 5 - $highlights->count();
            if ($slotsLeft > 0) {
                $articles = Article::query()
                    ->published()
                    ->select(['id', 'title', 'title_en', 'slug', 'featured_image', 'published_at', 'created_at', 'category_id'])
                    ->with(['category:id,name,slug'])
                    ->where('is_featured', true)
                    ->orderByRaw('COALESCE(published_at, created_at) DESC')
                    ->limit($slotsLeft)
                    ->get();

                if ($articles->isEmpty()) {
                    $articles = Article::query()
                        ->published()
                        ->select(['id', 'title', 'title_en', 'slug', 'featured_image', 'published_at', 'created_at', 'category_id'])
                        ->with(['category:id,name,slug'])
                        ->orderByRaw('COALESCE(published_at, created_at) DESC')
                        ->limit($slotsLeft)
                        ->get();
                }

                foreach ($articles as $art) {
                    $artImg = method_exists($art, 'getFeaturedImageUrl') ? $art->getFeaturedImageUrl() : null;
                    if (empty($artImg) && !empty($art->featured_image)) {
                        $artImg = asset($art->featured_image);
                    }
                    if (empty($artImg)) {
                        $artImg = 'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp';
                    }

                    $highlights->push([
                        'type'     => 'news',
                        'tag'      => strtoupper($art->category->name ?? 'WARTA'),
                        'meta'     => optional($art->published_at ?: $art->created_at)->translatedFormat('d M Y'),
                        'title'    => $art->localized_title ?? $art->title,
                        'date'     => optional($art->published_at ?: $art->created_at)->translatedFormat('d M Y'),
                        'url'      => route('blog.show', $art->slug),
                        'cta'      => 'Baca Ulasan',
                        'image'    => $artImg,
                        'is_event' => false,
                    ]);
                }
            }

            // Fallbacks if total highlights is still < 5
            $fallbacks = [
                [
                    'type'     => 'event',
                    'tag'      => 'KALENDER LARI',
                    'meta'     => 'Nasional',
                    'title'    => 'Kalender Event & Marathon Indonesia Terverifikasi',
                    'date'     => 'Jadwal Lengkap',
                    'url'      => route('events.index'),
                    'cta'      => 'Lihat Agenda',
                    'image'    => 'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp',
                    'is_event' => true,
                ],
                [
                    'type'     => 'news',
                    'tag'      => 'RISET & TIPS',
                    'meta'     => 'Panduan Latihan',
                    'title'    => 'Panduan Periodisasi Latihan & Riset Fisiologi Lari Terkini',
                    'date'     => 'Riset Terbaru',
                    'url'      => route('blog.index'),
                    'cta'      => 'Baca Riset',
                    'image'    => 'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp',
                    'is_event' => false,
                ],
                [
                    'type'     => 'event',
                    'tag'      => 'HALF MARATHON',
                    'meta'     => 'Jawa & Bali',
                    'title'    => 'Daftar Race 21K Terpopuler Musim Ini di Indonesia',
                    'date'     => 'Registrasi Dibuka',
                    'url'      => route('events.index'),
                    'cta'      => 'Detail Race',
                    'image'    => 'https://ruanglari.com/storage/blog/media/21bb785d-d104-4129-99ce-d84ae98afd3a.webp',
                    'is_event' => true,
                ],
                [
                    'type'     => 'news',
                    'tag'      => 'GEAR REVIEW',
                    'meta'     => 'Sepatu & Peralatan',
                    'title'    => 'Review Super Shoes & Rekomendasi Daily Trainer Terbaik',
                    'date'     => 'Ulasan Gear',
                    'url'      => route('blog.index'),
                    'cta'      => 'Ulasan Lengkap',
                    'image'    => 'https://ruanglari.com/storage/blog/media/92e97762-78b7-48e4-8b94-176b4e7fdf95.webp',
                    'is_event' => false,
                ],
                [
                    'type'     => 'event',
                    'tag'      => 'TRAIL RUN',
                    'meta'     => 'Nusantara',
                    'title'    => 'Eksplorasi Kalender Trail Run & Ultra Marathon Indonesia',
                    'date'     => 'Lihat Agenda',
                    'url'      => route('events.index'),
                    'cta'      => 'Ikuti Race',
                    'image'    => 'https://ruanglari.com/storage/blog/media/305fd042-17e8-4e89-86d7-8666d4e2229f.webp',
                    'is_event' => true,
                ],
            ];

            $fbIdx = 0;
            while ($highlights->count() < 5 && $fbIdx < count($fallbacks)) {
                $highlights->push($fallbacks[$fbIdx]);
                $fbIdx++;
            }

            return $highlights;
        });

        // 3. Upcoming Events for Race Calendar section (SSR - Zero CLS, Instant FCP)
        $upcomingEvents = Cache::remember('home.upcoming_events_ssr_v1', 600, function () {
            try {
                $events = Event::with('categories')
                    ->select('id', 'name', 'slug', 'start_at', 'location_name', 'created_at', 'is_eo')
                    ->published()
                    ->upcoming()
                    ->limit(6)
                    ->get();

                return $events->map(function ($e) {
                    $dt = $e->start_at ?: $e->created_at;
                    $date = $dt ? Carbon::parse($dt) : now();

                    return [
                        'name'      => $e->name,
                        'slug'      => $e->slug ?: \Illuminate\Support\Str::slug($e->name),
                        'is_eo'     => (bool) $e->is_eo,
                        'day'       => $date->format('d'),
                        'month'     => strtoupper($date->translatedFormat('M')),
                        'year'      => $date->format('Y'),
                        'time'      => (function () use ($dt) {
                            $t = optional($dt)->format('H:i');
                            return ($t === '00:00' || ! $t) ? '05:00' : $t;
                        })(),
                        'location'  => $e->location_name ?: 'Indonesia',
                        'url'       => route('events.show', $e->slug ?: $e->id),
                        'distances' => $e->categories->pluck('name')->filter()->values()->toArray(),
                    ];
                })->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });

        // 4. Latest Blog Articles for Journal section (SSR - Instant FCP, Full SEO)
        $latestArticles = Cache::remember('home.latest_articles_ssr_v1', 600, function () {
            try {
                return Article::published()
                    ->select(['id', 'title', 'title_en', 'slug', 'featured_image', 'published_at', 'created_at', 'category_id'])
                    ->with(['category:id,name,slug'])
                    ->orderByRaw('COALESCE(published_at, created_at, updated_at) DESC')
                    ->limit(6)
                    ->get()
                    ->map(function ($a) {
                        $dt = $a->published_at ?: $a->created_at;
                        return [
                            'title'    => $a->localized_title ?? $a->title,
                            'slug'     => $a->slug,
                            'category' => $a->category->name ?? 'Journal',
                            'date'     => $dt ? Carbon::parse($dt)->translatedFormat('d M Y') : '',
                            'image'    => $a->getFeaturedImageUrl(),
                            'url'      => route('blog.show', $a->slug),
                        ];
                    })->toArray();
            } catch (\Throwable $e) {
                return [];
            }
        });

        return view('home.index', [
            'homepageContent' => $homepageContent,
            'heroHighlights'  => $heroHighlights,
            'upcomingEvents'  => $upcomingEvents,
            'latestArticles'  => $latestArticles,
            'skipHeavyAssets' => true,
        ]);
    }
}
