<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ParticipantSupport;
use App\Services\EventCacheService;
use App\Services\MootaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class PublicEventController extends Controller
{
    protected $mootaService;
    protected $cacheService;

    public function __construct(MootaService $mootaService, EventCacheService $cacheService)
    {
        $this->mootaService = $mootaService;
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
                    ->whereIn('payment_status', ['paid', 'settlement', 'capture', 'cod']);
            })->exists();
            if ($event->hardcoded === 'latbarkamis') {
                $participants = $this->getLatbarParticipants($event);
                $stats = $this->getLatbarStats($event);
                $midtransDemoMode = filter_var($event->payment_config['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
                $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');

                return view('events.latbar3', [
                    'event' => $event,
                    'categories' => $categories,
                    'participants' => $participants,
                    'stats' => $stats,
                    'hasPaidParticipants' => $hasPaidParticipants,
                    'midtransUrl' => $midtransUrl,
                    'midtransClientKey' => $midtransClientKey,
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
            ->withCount(['participants as early_bird_sold_count' => function ($q) {
                $q->where('price_type', 'early')
                    ->whereHas('transaction', function ($t) {
                        $t->whereIn('payment_status', ['pending', 'paid', 'cod']);
                    });
            }])
            ->get();
        $seo = $this->buildSeo($event);
        $hasPaidParticipants = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id)
                ->whereIn('payment_status', ['paid', 'settlement', 'capture', 'cod']);
        })->exists();

        if ($event->hardcoded === 'latbarkamis') {
                $participants = $this->getLatbarParticipants($event);
                $stats = $this->getLatbarStats($event);
                $midtransDemoMode = filter_var($event->payment_config['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
                $midtransUrl = $midtransDemoMode ? config('midtrans.base_url_sandbox') : 'https://app.midtrans.com';
                $midtransClientKey = $midtransDemoMode ? config('midtrans.client_key_sandbox') : config('midtrans.client_key');

                return view('events.latbar3', [
                    'event' => $event,
                    'categories' => $categories,
                    'participants' => $participants,
                    'stats' => $stats,
                    'hasPaidParticipants' => $hasPaidParticipants,
                    'midtransUrl' => $midtransUrl,
                    'midtransClientKey' => $midtransClientKey,
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

        if (! $event->show_participant_list) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0,
            ]);
        }

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id)
                ->whereIn('payment_status', ['paid', 'settlement', 'capture', 'cod']);
        });

        if ($request->has('category_id') && $request->category_id) {
            $query->where('race_category_id', $request->category_id);
        }

        if ($request->has('gender') && $request->gender && $request->gender !== 'all') {
            $query->where('gender', $request->gender);
        }

        if ($request->has('age_group') && $request->age_group && $request->age_group !== 'all') {
            $group = $request->age_group;
            $eventDate = $event->start_at;
            if ($eventDate) {
                if ($group === '50+') {
                    $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears(50));
                } elseif ($group === 'Master 45+') {
                    $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears(45))
                        ->whereDate('date_of_birth', '>', $eventDate->copy()->subYears(50));
                } elseif ($group === 'Master') {
                    $query->whereDate('date_of_birth', '<=', $eventDate->copy()->subYears(40))
                        ->whereDate('date_of_birth', '>', $eventDate->copy()->subYears(45));
                } elseif ($group === 'Umum') {
                    $query->whereDate('date_of_birth', '>', $eventDate->copy()->subYears(40));
                }
            }
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id_card', 'like', "%{$search}%");
            });
        }

        $participants = $query->orderBy('created_at', 'desc')
            ->select('transaction_id', 'name', 'bib_number', 'race_category_id', 'created_at', 'gender', 'id_card', 'date_of_birth')
            ->with(['category:id,name', 'transaction:id,payment_status'])
            ->paginate(10);

        $participants->getCollection()->transform(function ($participant) use ($event) {
            $participant->age_group = $participant->getAgeGroup($event->start_at);
            $participant->payment_status_public = $participant->transaction?->payment_status === 'cod' ? 'cod' : 'paid';

            return $participant;
        });

        return response()->json($participants);
    }

    public function storeSupport(Request $request, $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $request->validate([
            'participant_id' => 'required|exists:participants,id',
            'supporter_name' => 'required|string|max:255',
            'supporter_phone' => 'required|string|max:20',
            'nominal' => 'required|numeric|min:5000',
            'payment_method' => 'required|in:midtrans,moota',
        ]);

        // Create initial support record
        $support = ParticipantSupport::create([
            'participant_id' => $request->participant_id,
            'supporter_name' => $request->supporter_name,
            'supporter_phone' => $request->supporter_phone,
            'nominal' => $request->nominal,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
        ]);

        if ($request->payment_method === 'moota') {
            try {
                // Generate unique code checking both tables
                $uniqueCode = 0;
                $finalAmount = $support->nominal;
                
                // Try up to 50 times
                for ($i = 0; $i < 50; $i++) {
                    $code = rand(1, 999);
                    $checkAmount = $support->nominal + $code;
                    
                    // Check Transaction table
                    $existsInTransactions = \App\Models\Transaction::where('payment_gateway', 'moota')
                        ->where('payment_status', 'pending')
                        ->where('final_amount', $checkAmount)
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();
                        
                    // Check ParticipantSupport table
                    $existsInSupports = ParticipantSupport::where('payment_method', 'moota')
                        ->where('status', 'pending')
                        ->where('nominal', $checkAmount) // nominal in DB stores the FINAL amount for Moota? 
                                                        // Wait, if I update nominal, I lose the original donation amount?
                                                        // Better to store unique_code separately or update nominal?
                                                        // Usually nominal is the final amount.
                        ->where('created_at', '>=', now()->subHours(24))
                        ->exists();

                    if (!$existsInTransactions && !$existsInSupports) {
                        $uniqueCode = $code;
                        $finalAmount = $checkAmount;
                        break;
                    }
                }

                if ($uniqueCode === 0) {
                    throw new \Exception('Gagal membuat kode unik pembayaran. Silakan coba lagi.');
                }

                $support->unique_code = $uniqueCode;
                $support->nominal = $finalAmount; // Update nominal to include unique code
                $support->expires_at = now()->addHours(24);
                $support->save();

                return response()->json([
                    'success' => true,
                    'payment_method' => 'moota',
                    'support_id' => $support->id,
                    'nominal' => $finalAmount,
                    'unique_code' => $uniqueCode,
                    'expires_at' => $support->expires_at,
                    'bank_accounts' => config('moota.bank_accounts')
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
        }

        // Midtrans Logic
        $midtransDemoMode = filter_var($event->payment_config['midtrans_demo_mode'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

        // Configure Midtrans
        Config::$serverKey = $midtransDemoMode ? config('midtrans.server_key_sandbox') : config('midtrans.server_key');
        Config::$isProduction = ! $midtransDemoMode;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = 'SUPPORT-' . $support->id . '-' . time();
        $support->midtrans_order_id = $orderId;
        $support->save();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $support->nominal,
            ],
            'customer_details' => [
                'first_name' => $support->supporter_name,
                'phone' => $support->supporter_phone,
            ],
            'item_details' => [
                [
                    'id' => 'SUPPORT-' . $support->participant_id,
                    'price' => (int) $support->nominal,
                    'quantity' => 1,
                    'name' => 'Dukungan untuk ' . $support->participant->name,
                ]
            ]
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $support->snap_token = $snapToken;
            $support->save();

            return response()->json([
                'success' => true,
                'payment_method' => 'midtrans',
                'snap_token' => $snapToken,
                'support_id' => $support->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get participants for Latbar event with caching
     */
    private function getLatbarParticipants(Event $event)
    {
        // Cache for 60 seconds to reduce DB load on high traffic
        // Using a short cache time because new registrations need to appear relatively quickly
        return Cache::remember('event_participants_' . $event->id, 60, function () use ($event) {
            return \App\Models\Participant::withSum(['supports as total_support' => function($q) {
                    $q->where('status', 'paid');
                }], 'nominal')
                ->select([
                    'id',
                    'name',
                    'email',
                    'target_time',
                    'result_time_ms',
                    'photo',
                    'address',
                    'isApproved',
                    'created_at',
                ])
                ->whereHas('transaction', function ($q) use ($event) {
                    $q->where('event_id', $event->id)
                      ->whereIn('payment_status', ['paid', 'settlement', 'capture', 'pending', 'cod']);
                })
                ->when($event->registration_open_at, function ($q) use ($event) {
                    $q->where('created_at', '>=', $event->registration_open_at);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    private function getLatbarStats(Event $event)
    {
        $baseQuery = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })->when($event->registration_open_at, function ($q) use ($event) {
            $q->where('created_at', '>=', $event->registration_open_at);
        });

        $codQuery = (clone $baseQuery)->whereHas('transaction', function ($q) {
            $q->where('payment_status', 'cod');
        });

        $paidQuery = (clone $baseQuery)->whereHas('transaction', function ($q) {
            $q->whereIn('payment_status', ['paid', 'settlement', 'capture']);
        });

        return [
            'codCount' => $codQuery->clone()->count(),
            'paidCount' => $paidQuery->clone()->count(),
            'codNames' => $codQuery->clone()->orderBy('created_at', 'desc')->limit(10)->get(['name']),
            'paidNames' => $paidQuery->clone()->orderBy('created_at', 'desc')->limit(10)->get(['name']),
        ];
    }
}
