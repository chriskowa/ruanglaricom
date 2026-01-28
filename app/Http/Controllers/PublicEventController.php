<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            $categories = $event->categories()->where('is_active', true)->with('masterGpx')->get();
            $seo = $this->buildSeo($event);
            $hasPaidParticipants = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id)
                  ->whereIn('payment_status', ['paid', 'settlement', 'capture']);
            })->exists();
            if ($event->hardcoded === 'latbarkamis') {
                $participants = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                        $q->where('event_id', $event->id)->whereIn('payment_status', ['pending', 'paid']);
                    })
                    ->when($event->registration_open_at, function($q) use ($event) {
                        $q->where('created_at', '>=', $event->registration_open_at);
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get(['id','name']);
                return view('events.latbar3', [
                    'event' => $event,
                    'categories' => $categories,
                    'participants' => $participants,
                    'hasPaidParticipants' => $hasPaidParticipants,
                ]);
            }

            return view('events.show', [
                'event' => $event,
                'categories' => $categories,
                'seo' => $seo,
                'hasPaidParticipants' => $hasPaidParticipants,
            ]);
        }

        // Cache miss - query from database
        $event = Event::where('slug', $slug)
            ->with(['categories', 'user'])
            ->firstOrFail();

        // Cache the event detail
        $this->cacheService->cacheEventDetail($event);

        // Get categories
        $categories = $event->categories()->where('is_active', true)
            ->with('masterGpx')
            ->withCount(['participants as early_bird_sold_count' => function($q) {
                $q->where('price_type', 'early')
                  ->whereHas('transaction', function($t) {
                      $t->whereIn('payment_status', ['pending', 'paid', 'cod']);
                  });
            }])
            ->get();
        $seo = $this->buildSeo($event);
        $hasPaidParticipants = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id)
              ->whereIn('payment_status', ['paid', 'settlement', 'capture']);
        })->exists();

        if ($event->hardcoded === 'latbarkamis') {
            $participants = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id)->whereIn('payment_status', ['pending', 'paid']);
            })
            ->when($event->registration_open_at, function($q) use ($event) {
                $q->where('created_at', '>=', $event->registration_open_at);
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get(['id','name']);
            return view('events.latbar3', [
                'event' => $event,
                'categories' => $categories,
                'participants' => $participants,
                'hasPaidParticipants' => $hasPaidParticipants,
            ]);
        }

        return view('events.show', [
            'event' => $event,
            'categories' => $categories,
            'seo' => $seo,
            'hasPaidParticipants' => $hasPaidParticipants,
        ]);
    }

    private function buildSeo(Event $event): array
    {
        $baseTitle = trim((string) ($event->name ?? ''));
        $locationPart = $event->location_name ? (' di '.$event->location_name) : '';

        $rawDescription = strip_tags((string) ($event->short_description ?? ''));
        if ($rawDescription === '') {
            $rawDescription = strip_tags((string) ($event->full_description ?? ''));
        }
        if ($rawDescription === '') {
            $rawDescription = $baseTitle.$locationPart;
        }

        $description = preg_replace('/\s+/', ' ', $rawDescription);
        $description = Str::limit(trim((string) $description), 160);

        $url = route('events.show', $event->slug);
        $image = $event->getHeroImageUrl() ?? asset('images/ruanglari_green.png');

        $keywordsParts = [
            'ruang lari',
            'ruanglari',
            'event lari',
            'race',
            'running',
            'marathon',
        ];

        if ($baseTitle !== '') {
            $keywordsParts[] = strtolower($baseTitle);
        }

        if ($event->location_name) {
            $keywordsParts[] = strtolower($event->location_name);
        }

        return [
            'title' => ($baseTitle !== '' ? $baseTitle.' | RuangLari' : 'RuangLari'),
            'description' => $description,
            'keywords' => implode(', ', array_unique($keywordsParts)),
            'url' => $url,
            'image' => $image,
        ];
    }

    public function getParticipants(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        
        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id)
              ->whereIn('payment_status', ['paid', 'settlement', 'capture']);
        });

        if ($request->has('category_id') && $request->category_id) {
            $query->where('race_category_id', $request->category_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $participants = $query->orderBy('created_at', 'desc')
            ->select('name', 'bib_number', 'race_category_id', 'created_at', 'gender')
            ->with('category:id,name')
            ->paginate(10);

        return response()->json($participants);
    }
}
