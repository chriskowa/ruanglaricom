<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Event;
use App\Models\RaceDistance;
use App\Models\RaceType;
use App\Models\RunningEvent;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicRunningEventController extends Controller
{
    private function getCombinedEvents(Request $request)
    {
        // 1. Query RunningEvents
        $query = RunningEvent::published()->upcoming()->with('city', 'raceType', 'raceDistances');

        if ($request->filled('month')) {
            $query->whereMonth('event_date', $request->month);
        }
        
        if ($request->filled('year')) {
            $query->whereYear('event_date', $request->year);
        }

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        if ($request->filled('race_type_id')) {
            $query->where('race_type_id', $request->race_type_id);
        }

        if ($request->filled('race_distance_id')) {
            $query->whereHas('raceDistances', function ($q) use ($request) {
                $q->where('race_distances.id', $request->race_distance_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location_name', 'like', "%{$search}%");
            });
        }

        $runningEvents = $query->get();

        // 2. Query EO Events
        $eoQuery = Event::where('start_at', '>=', now())
            ->with('categories');

        if ($request->filled('search')) {
            $search = $request->search;
            $eoQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location_name', 'like', "%{$search}%");
            });
        }
        
        // Apply date filters to EO events if present
        if ($request->filled('month')) {
            $eoQuery->whereMonth('start_at', $request->month);
        }
        if ($request->filled('year')) {
            $eoQuery->whereYear('start_at', $request->year);
        }

        $eoEvents = $eoQuery->get();

        // 3. Transform EO Events
        $eoEvents = $eoEvents->map(function ($event) {
            $event->event_date = $event->start_at;
            $event->start_time = $event->start_at;
            $event->is_featured = false; // EO events default to standard or add logic
            
            // Mock RaceType
            $event->raceType = (object) ['name' => 'Official Event'];
            
            // Mock City
            $event->city = null;
            
            // Map categories to raceDistances
            $event->raceDistances = $event->categories->map(function ($cat) {
                return (object) ['name' => $cat->name];
            });
            
            $event->is_eo = true;
            
            return $event;
        });

        // 4. Merge and Sort
        $allEvents = $runningEvents->concat($eoEvents)->sortBy('event_date');

        // 5. Paginate
        $page = $request->input('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $items = $allEvents->slice($offset, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $allEvents->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $events = $this->getCombinedEvents($request);

            return response()->json([
                'html' => view('events.partials.list', compact('events'))->render(),
                'pagination' => (string) $events->links()
            ]);
        }

        $cities = City::has('runningEvents')->orderBy('name')->get();
        $raceTypes = RaceType::has('events')->get();
        $raceDistances = RaceDistance::has('events')->get();
        
        // Initial load
        $events = $this->getCombinedEvents($request);

        return view('events.landing', compact('events', 'cities', 'raceTypes', 'raceDistances'));
    }

    public function show($slug)
    {
        $event = RunningEvent::where('slug', $slug)
            ->whereIn('status', ['published', 'archived'])
            ->with('city', 'raceType', 'raceDistances')
            ->firstOrFail();

        // Related events by category (Race Type)
        $relatedEvents = RunningEvent::where('race_type_id', $event->race_type_id)
            ->where('id', '!=', $event->id)
            ->published()
            ->upcoming()
            ->with('city', 'raceType')
            ->limit(3)
            ->get();

        // Events on the same date
        $sameDateEvents = RunningEvent::whereDate('event_date', $event->event_date)
            ->where('id', '!=', $event->id)
            ->published()
            ->with('city', 'raceType')
            ->limit(3)
            ->get();
            
        return view('events.running-event-detail', compact('event', 'relatedEvents', 'sameDateEvents'));
    }
}
