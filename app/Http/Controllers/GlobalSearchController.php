<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GlobalSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rawQuery = trim((string) $request->query('q', ''));
        $query = mb_strtolower(preg_replace('/\s+/', ' ', $rawQuery));

        if (mb_strlen($query) < 2) {
            return response()->json([
                'query' => $rawQuery,
                'tools' => array_slice($this->getStaticTools(), 0, 6),
                'events' => [],
                'programs' => [],
                'articles' => [],
            ]);
        }

        $cacheKey = 'global_quick_search_v1:' . md5($query);

        $results = Cache::remember($cacheKey, 120, function () use ($query) {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';

            $tools = collect($this->getStaticTools())
                ->filter(function (array $tool) use ($query) {
                    return str_contains(mb_strtolower($tool['title']), $query)
                        || str_contains(mb_strtolower($tool['subtitle']), $query)
                        || str_contains(mb_strtolower($tool['keywords']), $query);
                })
                ->take(5)
                ->values()
                ->all();

            $events = Event::published()
                ->select(['id', 'name', 'slug', 'start_at', 'location_name'])
                ->where(function ($q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('location_name', 'like', $like);
                })
                ->orderByDesc('start_at')
                ->limit(4)
                ->get()
                ->map(fn (Event $e) => [
                    'title' => $e->name,
                    'subtitle' => trim(($e->start_at ? $e->start_at->format('d M Y') : '') . ($e->location_name ? ' · ' . $e->location_name : ''), ' ·'),
                    'url' => route('events.show', $e->slug),
                    'badge' => 'Event',
                ])
                ->all();

            $programs = Program::query()
                ->where('is_published', true)
                ->where('is_active', true)
                ->select(['id', 'title', 'slug', 'distance_target', 'duration_weeks'])
                ->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                        ->orWhere('distance_target', 'like', $like);
                })
                ->orderByDesc('id')
                ->limit(4)
                ->get()
                ->map(fn (Program $p) => [
                    'title' => $p->title,
                    'subtitle' => trim(strtoupper((string) ($p->distance_target ?? 'Program')) . ($p->duration_weeks ? ' · ' . $p->duration_weeks . ' Minggu' : ''), ' ·'),
                    'url' => route('programs.show', $p->slug),
                    'badge' => 'Program',
                ])
                ->all();

            $articles = Article::published()
                ->select(['id', 'title', 'slug', 'published_at'])
                ->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                        ->orWhere('excerpt', 'like', $like);
                })
                ->orderByDesc('published_at')
                ->limit(4)
                ->get()
                ->map(fn (Article $a) => [
                    'title' => $a->title,
                    'subtitle' => $a->published_at ? $a->published_at->format('d M Y') : 'Journal',
                    'url' => route('blog.show', $a->slug),
                    'badge' => 'Blog',
                ])
                ->all();

            return [
                'tools' => $tools,
                'events' => $events,
                'programs' => $programs,
                'articles' => $articles,
            ];
        });

        return response()->json([
            'query' => $rawQuery,
            'tools' => $results['tools'],
            'events' => $results['events'],
            'programs' => $results['programs'],
            'articles' => $results['articles'],
        ]);
    }

    private function getStaticTools(): array
    {
        return [
            [
                'title' => 'Buat Program Lari AI (Coach Ruang Lari)',
                'subtitle' => 'Susun jadwal latihan personal VDOT ke kalender',
                'keywords' => 'program ai coach vdot jadwal latihan marathon 5k 10k hm',
                'url' => route('programs.realistic'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'Kalkulator Pace & VDOT',
                'subtitle' => 'Hitung pace target, race predictor & zona latihan',
                'keywords' => 'calculator kalkulator pace vdot split prediksi lomba',
                'url' => route('calculator'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'Kalender Pelari Studio (Cetak PDF)',
                'subtitle' => 'Desain kalender meja/dinding dengan foto lari 300 DPI',
                'keywords' => 'kalender pelari studio cetak pdf poster a3 a4',
                'url' => route('kalender-pelari.landing'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'Pace Pro Strategy',
                'subtitle' => 'Rencana strategi pace per kilometer & elevasi GPX',
                'keywords' => 'pace pro strategi race split elevasi',
                'url' => route('tools.pace-pro'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'Buat Rute Lari & GPX',
                'subtitle' => 'Gambar rute lari dan ekspor file GPX',
                'keywords' => 'rute gpx peta map route maker',
                'url' => route('tools.buat-rute-lari'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'Running Form Analyzer',
                'subtitle' => 'Analisa teknik lari dan postur tubuh',
                'keywords' => 'form analyzer teknik postur cadence',
                'url' => route('tools.form-analyzer'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'Shoe Analyzer',
                'subtitle' => 'Rekomendasi sepatu lari sesuai kebutuhan & jarak',
                'keywords' => 'shoe sepatu lari rotasi carbon plate',
                'url' => route('tools.shoe-analyzer'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'TrackMaster & RaceMaster',
                'subtitle' => 'Pencatat lap & timing lomba komunitas',
                'keywords' => 'trackmaster racemaster timing lap stopwatch',
                'url' => route('tools.index'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'Database Rute GPX',
                'subtitle' => 'Jelajahi rute lari populer di Indonesia',
                'keywords' => 'database gpx rute strava jalur lari',
                'url' => route('gpx.index'),
                'badge' => 'Tool',
            ],
            [
                'title' => 'Kalender Event Lari',
                'subtitle' => 'Jadwal lomba lari nasional & komunitas',
                'keywords' => 'calendar kalender lomba event race',
                'url' => route('calendar.public'),
                'badge' => 'Tool',
            ],
        ];
    }
}
