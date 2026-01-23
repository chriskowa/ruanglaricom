<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index()
    {
        // 1. Static Pages
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('events.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('programs.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('marketplace.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('blog.index'), 'priority' => '0.8', 'changefreq' => 'daily'],
            ['loc' => route('calculator'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('pacer.index'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('coaches.index'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['loc' => route('challenge.index'), 'priority' => '0.7', 'changefreq' => 'daily'],
            ['loc' => route('vcard.index'), 'priority' => '0.6', 'changefreq' => 'weekly'],
        ];

        // 2. Events (Published)
        \App\Models\Event::published()->upcoming()->chunk(100, function ($events) use (&$urls) {
            foreach ($events as $event) {
                $urls[] = [
                    'loc' => route('running-event.detail', $event->slug),
                    'lastmod' => $event->updated_at->toIso8601String(),
                    'priority' => '0.9',
                    'changefreq' => 'weekly',
                ];
            }
        });

        // 3. Blog Articles (Published)
        \App\Models\Article::published()->chunk(100, function ($articles) use (&$urls) {
            foreach ($articles as $article) {
                $urls[] = [
                    'loc' => route('blog.show', $article->slug),
                    'lastmod' => $article->updated_at->toIso8601String(),
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                ];
            }
        });

        // 4. Marketplace Products (Active)
        \App\Models\Marketplace\MarketplaceProduct::where('is_active', true)->chunk(100, function ($products) use (&$urls) {
            foreach ($products as $product) {
                $urls[] = [
                    'loc' => route('marketplace.show', $product->slug),
                    'lastmod' => $product->updated_at->toIso8601String(),
                    'priority' => '0.7',
                    'changefreq' => 'daily',
                ];
            }
        });

        // 5. Pacers (Verified)
        \App\Models\Pacer::where('verified', true)->chunk(100, function ($pacers) use (&$urls) {
            foreach ($pacers as $pacer) {
                $urls[] = [
                    'loc' => route('pacer.show', $pacer->seo_slug),
                    'lastmod' => $pacer->updated_at->toIso8601String(),
                    'priority' => '0.6',
                    'changefreq' => 'monthly',
                ];
            }
        });

        return response()->view('sitemap.index', compact('urls'))
            ->header('Content-Type', 'text/xml');
    }
}
