<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Event;
use App\Models\EventRating;
use App\Models\RaceDistance;
use App\Models\RaceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PublicRunningEventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::whereIn('event_kind', ['directory', 'managed'])
            ->published()
            ->upcoming()
            ->with(['city', 'raceType', 'raceDistances', 'categories']);

        if ($request->filled('month')) {
            if (preg_match('/^\d{4}-\d{2}$/', $request->month)) {
                $parts = explode('-', $request->month);
                $query->whereYear('start_at', $parts[0])
                      ->whereMonth('start_at', $parts[1]);
            } else {
                $query->whereMonth('start_at', $request->month);
            }
        }

        if ($request->filled('year')) {
            $query->whereYear('start_at', $request->year);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        } elseif ($request->filled('city')) {
            $citySearch = $request->city;
            $query->where(function ($q) use ($citySearch) {
                $q->whereHas('city', function ($sq) use ($citySearch) {
                    $sq->where('name', 'like', "%{$citySearch}%");
                })->orWhere('location_name', 'like', "%{$citySearch}%");
            });
        }

        if ($request->filled('race_distance_id')) {
            $query->whereHas('raceDistances', function ($q) use ($request) {
                $q->where('event_distances.race_distance_id', $request->race_distance_id);
            });
        } elseif ($request->filled('category')) {
            $categorySearch = str_replace('-', ' ', $request->category);
            $query->whereHas('categories', function ($q) use ($categorySearch) {
                $q->where('name', 'like', "%{$categorySearch}%")
                  ->orWhere('distance_label', 'like', "%{$categorySearch}%");
            });
        }

        if ($request->filled('race_type_id')) {
            $query->where('race_type_id', $request->race_type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location_name', 'like', "%{$search}%");
            });
        }

        // Sorting
        $query->orderBy('start_at', 'asc');

        if ($request->ajax()) {
            $events = $query->paginate(10);

            return response()->json([
                'html' => view('events.partials.list', compact('events'))->render(),
                'pagination' => (string) $events->links(),
            ]);
        }

        $cities = City::whereHas('events', fn ($q) => $q->directory())->orderBy('name')->get();
        $raceTypes = RaceType::whereHas('events', fn ($q) => $q->directory())->get();
        $raceDistances = RaceDistance::whereHas('events', fn ($q) => $q->directory())->get();

        $events = $query->paginate(10);

        return view('events.landing', compact('events', 'cities', 'raceTypes', 'raceDistances'));
    }

    public function show($slug)
    {
        $event = Event::directory()
            ->where('slug', $slug)
            ->whereIn('status', ['published', 'archived'])
            ->with(['city', 'raceType', 'raceDistances', 'categories'])
            ->firstOrFail();

        // Related events by category (Race Type)
        $relatedEvents = collect();
        if ($event->race_type_id) {
            $relatedEvents = Event::where('race_type_id', $event->race_type_id)
                ->where('id', '!=', $event->id)
                ->directory()
                ->published()
                ->upcoming()
                ->with(['city', 'raceType'])
                ->limit(3)
                ->get();
        }

        // Events on the same date
        $sameDateEvents = Event::whereDate('start_at', $event->start_at)
            ->where('id', '!=', $event->id)
            ->directory()
            ->published()
            ->with(['city', 'raceType'])
            ->limit(3)
            ->get();

        $ratingAverage = 0.0;
        $ratingCount = 0;
        if (Schema::hasTable('event_ratings')) {
            try {
                $stats = EventRating::where('event_id', $event->id)
                    ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as rating_count')
                    ->first();

                $ratingAverage = round((float) ($stats->avg_rating ?? 0), 2);
                $ratingCount = (int) ($stats->rating_count ?? 0);
            } catch (\Throwable $e) {
                $ratingAverage = 0.0;
                $ratingCount = 0;
            }
        }

        $cookieName = 'rl_rating_id';
        $cookieValue = request()->cookie($cookieName) ?: (string) Str::uuid();
        $cookie = cookie(
            $cookieName,
            $cookieValue,
            60 * 24 * 365,
            '/',
            null,
            app()->environment('production'),
            true,
            false,
            'Lax'
        );

        return response()
            ->view('events.running-event-detail', compact('event', 'relatedEvents', 'sameDateEvents', 'ratingAverage', 'ratingCount'))
            ->withCookie($cookie);
    }

    public function cityArchive($citySlug)
    {
        $citySlug = trim(Str::lower((string) $citySlug));

        $city = City::where('seourl', $citySlug)->first();
        if (! $city) {
            $candidate = City::query()
                ->select(['id', 'name', 'seourl'])
                ->get()
                ->first(function ($c) use ($citySlug) {
                    return Str::slug((string) $c->name) === $citySlug;
                });

            if ($candidate) {
                $city = City::find($candidate->id);
            }
        }

        if (! $city) {
            abort(404);
        }

        if ($city->seourl && $city->seourl !== $citySlug) {
            $to = route('events.city', ['city' => $city->seourl]);
            $qs = request()->getQueryString();
            if ($qs) {
                $to .= '?'.$qs;
            }

            return redirect()->to($to, 301);
        }

        // Upcoming events
        $upcomingEvents = Event::published()
            ->upcoming()
            ->where('city_id', $city->id)
            ->with(['raceType', 'raceDistances'])
            ->orderBy('start_at', 'asc')
            ->get();

        // Past events
        $pastEvents = Event::published()
            ->where('start_at', '<', now())
            ->where('city_id', $city->id)
            ->with(['raceType', 'raceDistances'])
            ->orderBy('start_at', 'desc')
            ->limit(12)
            ->get();

        return view('events.city-archive', compact('city', 'upcomingEvents', 'pastEvents'));
    }

    public function categoryArchive(Request $request, $categorySlug)
    {
        $categorySlug = Str::lower($categorySlug);
        
        $categoryMeta = [
            '5k' => [
                'title' => 'Jadwal Lari 5K 2026 Indonesia | Event Lari Terbaru',
                'meta_title' => 'Jadwal Lari 5K 2026 Indonesia | Event Lari Terbaru',
                'meta_description' => 'Daftar lengkap jadwal event lari 5K di Indonesia tahun 2026. Temukan info pendaftaran fun run, road run, dan charity run 5K terdekat lengkap dengan tanggal dan lokasi.',
                'h1' => 'Jadwal Lari 5K 2026 Indonesia',
                'description' => 'Temukan jadwal lari 5K 2026 di Indonesia terupdate. Dapatkan info lengkap tentang event fun run 5K, rute jalan raya (road run), pendaftaran online, biaya, lokasi, dan tanggal pelaksanaan.',
                'filter_type' => 'distance',
                'filter_id' => 1 // 5K
            ],
            '10k' => [
                'title' => 'Jadwal Lari 10K 2026 Indonesia | Kalender Event Lari',
                'meta_title' => 'Jadwal Lari 10K 2026 Indonesia | Kalender Event Lari',
                'meta_description' => 'Daftar lengkap jadwal lari 10K di Indonesia tahun 2026. Cek jadwal lomba lari 10K terdekat, tanggal, lokasi, biaya pendaftaran, dan informasi race pack.',
                'h1' => 'Jadwal Lari 10K 2026 Indonesia',
                'description' => 'Temukan jadwal lari 10K 2026 di Indonesia terupdate. Dapatkan info lengkap tentang event lomba lari 10K, rute jalan raya (road run), pendaftaran online, biaya, lokasi, dan tanggal pelaksanaan.',
                'filter_type' => 'distance',
                'filter_id' => 2 // 10K
            ],
            'half-marathon' => [
                'title' => 'Jadwal Half Marathon 2026 Indonesia | Event Lari 21K Terbaru',
                'meta_title' => 'Jadwal Half Marathon 2026 Indonesia | Event Lari 21K Terbaru',
                'meta_description' => 'Daftar lengkap jadwal event lari Half Marathon (21K) di Indonesia tahun 2026. Temukan jadwal lomba lari HM terdekat lengkap dengan rute, lokasi, dan tanggal.',
                'h1' => 'Jadwal Half Marathon 2026 Indonesia',
                'description' => 'Temukan jadwal Half Marathon (21K) 2026 di Indonesia terupdate. Dapatkan info lengkap tentang event lomba lari HM, rute jalan raya, pendaftaran online, biaya, lokasi, dan tanggal pelaksanaan.',
                'filter_type' => 'distance',
                'filter_id' => 3 // Half Marathon (21K)
            ],
            'marathon' => [
                'title' => 'Jadwal Marathon 2026 Indonesia | Event Full Marathon 42K',
                'meta_title' => 'Jadwal Marathon 2026 Indonesia | Event Full Marathon 42K',
                'meta_description' => 'Daftar lengkap jadwal event lari Full Marathon (42K) di Indonesia tahun 2026. Cek tanggal, lokasi, biaya pendaftaran, dan rute lomba lari FM terbaru.',
                'h1' => 'Jadwal Marathon 2026 Indonesia',
                'description' => 'Temukan jadwal Full Marathon (42K) 2026 di Indonesia terupdate. Dapatkan info lengkap tentang event lomba lari FM, rute jalan raya, pendaftaran online, biaya, lokasi, dan tanggal pelaksanaan.',
                'filter_type' => 'distance',
                'filter_id' => 4 // Marathon (42K)
            ],
            'ultra-marathon' => [
                'title' => 'Jadwal Ultra Marathon 2026 Indonesia | Event Lari Ultra',
                'meta_title' => 'Jadwal Ultra Marathon 2026 Indonesia | Event Lari Ultra',
                'meta_description' => 'Daftar lengkap jadwal event lari Ultra Marathon (di atas 42K) di Indonesia tahun 2026. Temukan event ultra road & trail lari terdekat lengkap dengan info pendaftaran.',
                'h1' => 'Jadwal Ultra Marathon 2026 Indonesia',
                'description' => 'Temukan jadwal Ultra Marathon 2026 di Indonesia terupdate. Dapatkan info lengkap tentang event lomba lari ultra, rute jalan raya dan trail, pendaftaran online, biaya, lokasi, dan tanggal pelaksanaan.',
                'filter_type' => 'type',
                'filter_id' => 3 // Ultra Marathon
            ],
            'trail-run' => [
                'title' => 'Jadwal Trail Run 2026 Indonesia | Event Lari Lintas Alam',
                'meta_title' => 'Jadwal Trail Run 2026 Indonesia | Event Lari Lintas Alam',
                'meta_description' => 'Daftar lengkap jadwal event lari Trail Run (lintas alam) di Indonesia tahun 2026. Cek tanggal, lokasi gunung/hutan, kategori jarak, dan link pendaftaran trail run terbaru.',
                'h1' => 'Jadwal Trail Run 2026 Indonesia',
                'description' => 'Temukan jadwal Trail Run 2026 di Indonesia terupdate. Dapatkan info lengkap tentang event lomba lari lintas alam (trail run), rute pegunungan/hutan, pendaftaran online, biaya, lokasi, dan tanggal pelaksanaan.',
                'filter_type' => 'type',
                'filter_id' => 2 // Trail Run
            ],
            'fun-run' => [
                'title' => 'Jadwal Fun Run 2026 Indonesia | Event Lari Santai Terbaru',
                'meta_title' => 'Jadwal Fun Run 2026 Indonesia | Event Lari Santai Terbaru',
                'meta_description' => 'Daftar lengkap jadwal event Fun Run (lari santai/keluarga) di Indonesia tahun 2026. Temukan jadwal lari gembira terdekat lengkap dengan info registrasi dan lokasi.',
                'h1' => 'Jadwal Fun Run 2026 Indonesia',
                'description' => 'Temukan jadwal Fun Run 2026 di Indonesia terupdate. Dapatkan info lengkap tentang event lomba lari santai (fun run), rute jalan raya, pendaftaran online, biaya, lokasi, dan tanggal pelaksanaan.',
                'filter_type' => 'type',
                'filter_id' => 4 // Fun Run
            ],
            'virtual-run' => [
                'title' => 'Jadwal Virtual Run 2026 Indonesia | Event Lari Online',
                'meta_title' => 'Jadwal Virtual Run 2026 Indonesia | Event Lari Online',
                'meta_description' => 'Daftar lengkap jadwal event Virtual Run di Indonesia tahun 2026. Temukan jadwal lari virtual terdekat lengkap dengan info pendaftaran, submisi lari, dan medali.',
                'h1' => 'Jadwal Virtual Run 2026 Indonesia',
                'description' => 'Temukan jadwal Virtual Run 2026 di Indonesia terupdate. Dapatkan info lengkap tentang event lomba lari online (virtual run), pendaftaran online, biaya, lokasi, dan tanggal pelaksanaan.',
                'filter_type' => 'type',
                'filter_id' => 5 // Virtual Run
            ],
        ];

        if (!isset($categoryMeta[$categorySlug])) {
            abort(404);
        }

        $meta = $categoryMeta[$categorySlug];

        $query = Event::whereIn('event_kind', ['directory', 'managed'])
            ->published()
            ->upcoming()
            ->with(['city', 'raceType', 'raceDistances', 'categories']);

        if ($meta['filter_type'] === 'distance') {
            $query->whereHas('raceDistances', function ($q) use ($meta) {
                $q->where('event_distances.race_distance_id', $meta['filter_id']);
            });
        } else {
            $query->where('race_type_id', $meta['filter_id']);
        }

        // Sorting
        $query->orderBy('start_at', 'asc');

        if ($request->ajax()) {
            $events = $query->paginate(10);

            return response()->json([
                'html' => view('events.partials.list', compact('events'))->render(),
                'pagination' => (string) $events->links(),
            ]);
        }

        $cities = City::whereHas('events', fn ($q) => $q->directory())->orderBy('name')->get();
        $raceTypes = RaceType::whereHas('events', fn ($q) => $q->directory())->get();
        $raceDistances = RaceDistance::whereHas('events', fn ($q) => $q->directory())->get();

        $events = $query->paginate(10);

        return view('events.category-archive', compact('events', 'cities', 'raceTypes', 'raceDistances', 'meta', 'categorySlug'));
    }
}
