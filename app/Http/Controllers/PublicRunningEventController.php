<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Event;
use App\Models\EventRating;
use App\Models\RaceDistance;
use App\Models\RaceType;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class PublicRunningEventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::published()
            ->upcoming()
            ->with(['city', 'raceType', 'raceDistances', 'categories']);

        if ($request->filled('month')) {
            $query->whereMonth('start_at', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('start_at', $request->year);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('race_type_id')) {
            $query->where('race_type_id', $request->race_type_id);
        }

        if ($request->filled('race_distance_id')) {
            $query->whereHas('raceDistances', function ($q) use ($request) {
                $q->where('event_distances.race_distance_id', $request->race_distance_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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
                'pagination' => (string) $events->links()
            ]);
        }

        $cities = City::has('events')->orderBy('name')->get();
        $raceTypes = RaceType::has('events')->get();
        $raceDistances = RaceDistance::has('events')->get();
        
        $events = $query->paginate(10);

        return view('events.landing', compact('events', 'cities', 'raceTypes', 'raceDistances'));
    }

    public function show($slug)
    {
        $event = Event::where('slug', $slug)
            ->whereIn('status', ['published', 'archived'])
            ->with(['city', 'raceType', 'raceDistances', 'categories'])
            ->firstOrFail();

        // Related events by category (Race Type)
        $relatedEvents = collect();
        if ($event->race_type_id) {
            $relatedEvents = Event::where('race_type_id', $event->race_type_id)
                ->where('id', '!=', $event->id)
                ->published()
                ->upcoming()
                ->with(['city', 'raceType'])
                ->limit(3)
                ->get();
        }

        // Events on the same date
        $sameDateEvents = Event::whereDate('start_at', $event->start_at)
            ->where('id', '!=', $event->id)
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
        $city = City::where('seourl', $citySlug)->firstOrFail();

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
}
