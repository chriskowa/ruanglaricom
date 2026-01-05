<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventCacheService;
use Illuminate\Http\Request;

class PublicEventController extends Controller
{
    protected $cacheService;

    public function __construct(EventCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Event landing list page
     */
    public function index(Request $request)
    {
        $query = Event::query();

        // Filters
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('location_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('month') && $request->month !== 'All') {
            $query->whereMonth('start_at', $this->mapMonthNameToNumber($request->month));
        }

        if ($request->filled('location') && $request->location !== 'All') {
            $query->where('location_name', $request->location);
        }

        // Load categories for distance chips
        $query->with(['categories' => function ($q) {
            $q->where('is_active', true);
        }]);

        $events = $query->orderByRaw('COALESCE(start_at, created_at) ASC')->paginate(12);

        // Distinct filters
        $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $locations = Event::select('location_name')->whereNotNull('location_name')->distinct()->pluck('location_name');
        $distances = ['5K', '10K', 'HM', 'FM', 'Ultra'];

        return view('events.index', compact('events', 'months', 'locations', 'distances'));
    }

    private function mapMonthNameToNumber(string $name): int
    {
        $map = [
            'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4, 'Mei' => 5, 'Juni' => 6,
            'Juli' => 7, 'Agustus' => 8, 'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12,
        ];

        return $map[$name] ?? date('n');
    }

    /**
     * Show event landing page with Redis cache
     */
    public function show($slug)
    {
        // Try to get from cache first
        $cached = $this->cacheService->getCachedEventDetail($slug);

        if ($cached) {
            $event = Event::find($cached['event']['id']);
            if (! $event) {
                abort(404);
            }

            // Load categories if not in cache
            $categories = $event->categories()->where('is_active', true)->get();
            if ($event->hardcoded === 'latbarkamis') {
                $participants = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                        $q->where('event_id', $event->id)->whereIn('payment_status', ['pending', 'paid']);
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get(['id','name']);
                return view('events.latbar3', [
                    'event' => $event,
                    'categories' => $categories,
                    'participants' => $participants,
                ]);
            }

            return view('events.show', [
                'event' => $event,
                'categories' => $categories,
            ]);
        }

        // Cache miss - query from database
        $event = Event::where('slug', $slug)
            ->with(['categories', 'user'])
            ->firstOrFail();

        // Cache the event detail
        $this->cacheService->cacheEventDetail($event);

        // Get categories
        $categories = $event->categories()->where('is_active', true)->get();

        if ($event->hardcoded === 'latbarkamis') {
            $participants = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id)->whereIn('payment_status', ['pending', 'paid']);
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get(['id','name']);
            return view('events.latbar3', [
                'event' => $event,
                'categories' => $categories,
                'participants' => $participants,
            ]);
        }

        return view('events.show', [
            'event' => $event,
            'categories' => $categories,
        ]);
    }
}
