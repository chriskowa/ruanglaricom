<?php

namespace App\Http\Controllers\EO;

use App\Actions\EO\StoreManualParticipantAction;
use App\Http\Controllers\Controller;
use App\Mail\EventRegistrationSuccess;
use App\Models\Event;
use App\Models\RaceCategory;
use App\Models\Transaction;
use App\Jobs\SendPendingPaymentReminder;
use App\Services\EventCacheService;
use App\Services\EventReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class EventController extends Controller
{
    protected $cacheService;

    protected $reportService;

    public function __construct(EventCacheService $cacheService, EventReportService $reportService)
    {
        $this->cacheService = $cacheService;
        $this->reportService = $reportService;
    }

    public function index()
    {
        $events = Event::where('user_id', auth()->id())
            ->with(['categories' => function ($query) {
                $query->withCount(['participants as total_participants', 'participants as paid_participants' => function ($q) {
                    $q->whereHas('transaction', function ($t) {
                        $t->where('payment_status', 'paid');
                    });
                }]);
            }])
            ->latest()
            ->paginate(10);

        return view('eo.events.index', compact('events'));
    }

    public function create()
    {
        $gpxList = \App\Models\MasterGpx::where('is_published', true)->orderBy('title')->get();

        return view('eo.events.create', compact('gpxList'));
    }

    /**
     * Upload media via Dropzone
     */
    public function uploadMedia(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'folder' => 'nullable|string',
        ]);

        $folder = $request->input('folder', 'events/temp');
        $path = $this->processImage($request->file('file'), $folder, 1920, 85);

        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => Storage::url($path),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug',
            'hardcoded' => 'nullable|string|max:50',
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'rpc_location_name' => 'nullable|string|max:255',
            'rpc_location_address' => 'nullable|string',
            'rpc_latitude' => 'nullable|numeric',
            'rpc_longitude' => 'nullable|numeric',
            'hero_image_url' => 'nullable|url',
            'hero_image' => 'nullable|string', // Changed to string (path)
            'logo_image' => 'nullable|string',
            'floating_image' => 'nullable|string',
            'medal_image' => 'nullable|string',
            'jersey_image' => 'nullable|string',
            'twibbon_image' => 'nullable|string',
            'map_embed_url' => 'nullable|string',
            'google_calendar_url' => 'nullable|url',
            'registration_open_at' => 'nullable|date',
            'registration_close_at' => 'nullable|date|after:registration_open_at',
            'promo_code' => 'nullable|string|max:50',
            'promo_buy_x' => 'nullable|integer|min:1',
            'custom_email_message' => 'nullable|string',
            'ticket_email_use_qr' => 'nullable|boolean',
            'show_participant_list' => 'required|boolean',
            'is_instant_notification' => 'nullable|boolean',
            'ticket_email_rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'blast_email_rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'facilities' => 'nullable|array',
            'facilities.*.name' => 'nullable|string|max:255',
            'facilities.*.description' => 'nullable|string',
            'facilities.*.enabled' => 'nullable|boolean',
            'facilities.*.image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'gallery' => 'nullable|array', // Now expects array of paths
            'gallery.*' => 'string',
            'sponsors' => 'nullable|array|max:30',
            'sponsors.*' => 'string',
            'theme_colors' => 'nullable|array',
            'theme_colors.dark' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.card' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.input' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.neon' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.neonHover' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.accent' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.danger' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'jersey_sizes' => 'nullable|array',
            'jersey_sizes.*' => 'nullable|string|in:XXS,XS,S,M,L,XL,2XL,3XL,4XL,5XL',
            'jersey_stock' => 'nullable|array',
            'jersey_stock.*' => 'nullable|integer|min:0',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required_with:addons|string|max:255',
            'addons.*.price' => 'nullable|numeric|min:0',
            'premium_amenities' => 'nullable|array',
            'template' => 'nullable|string|in:modern-dark,light-clean,simple-minimal,paolo-fest,paolo-fest-dark,professional-city-run,golden-run,quick-light',
            'platform_fee' => 'nullable|numeric|min:0',
            'categories' => 'required|array|min:1',
            'categories.*.master_gpx_id' => 'nullable|exists:master_gpxes,id',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.distance_km' => 'nullable|numeric|min:0',
            'categories.*.code' => 'nullable|string|max:50',
            'categories.*.quota' => 'nullable|integer|min:0',
            'categories.*.min_age' => 'nullable|integer|min:0',
            'categories.*.max_age' => 'nullable|integer|min:0',
            'categories.*.cutoff_minutes' => 'nullable|integer|min:0',
            'categories.*.price_early' => 'nullable|integer|min:0',
            'categories.*.price_regular' => 'nullable|integer|min:0',
            'categories.*.price_late' => 'nullable|integer|min:0',
            'categories.*.early_bird_quota' => 'nullable|integer|min:0',
            'categories.*.early_bird_end_at' => 'nullable|date',
            'categories.*.reg_start_at' => 'nullable|date',
            'categories.*.reg_end_at' => 'nullable|date|after:categories.*.reg_start_at',
            'categories.*.is_active' => 'nullable|boolean',
            'categories.*.prizes' => 'nullable|array',
            'categories.*.prizes.*' => 'nullable|string|max:255',
            'payment_config' => 'nullable|array',
            'payment_config.midtrans_demo_mode' => 'nullable|boolean',
            'payment_config.allowed_methods' => 'nullable|array|min:1',
            'payment_config.allowed_methods.*' => 'in:midtrans,moota,cod,all',
            'whatsapp_config' => 'nullable|array',
            'whatsapp_config.enabled' => 'nullable|boolean',
            'whatsapp_config.template' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        if (Schema::hasColumn('events', 'ticket_email_use_qr')) {
            $validated['ticket_email_use_qr'] = array_key_exists('ticket_email_use_qr', $validated)
                ? (bool) $validated['ticket_email_use_qr']
                : true;
        } else {
            unset($validated['ticket_email_use_qr']);
        }

        if (Schema::hasColumn('events', 'is_instant_notification')) {
            $validated['is_instant_notification'] = isset($validated['is_instant_notification']) ? (bool) $validated['is_instant_notification'] : false;
        } else {
            unset($validated['is_instant_notification']);
        }

        if (Schema::hasColumn('events', 'ticket_email_rate_limit_per_minute')) {
            $validated['ticket_email_rate_limit_per_minute'] = $validated['ticket_email_rate_limit_per_minute'] ?? null;
        } else {
            unset($validated['ticket_email_rate_limit_per_minute']);
        }

        if (Schema::hasColumn('events', 'blast_email_rate_limit_per_minute')) {
            $validated['blast_email_rate_limit_per_minute'] = $validated['blast_email_rate_limit_per_minute'] ?? null;
        } else {
            unset($validated['blast_email_rate_limit_per_minute']);
        }

        // Default premium_amenities to empty array if not present (for new events to not be treated as legacy)
        if (! isset($validated['premium_amenities'])) {
            $validated['premium_amenities'] = [];
        } else {
            // Re-index nested items arrays to ensure JSON array structure (not object)
            foreach ($validated['premium_amenities'] as $key => &$amenity) {
                if (isset($amenity['items']) && is_array($amenity['items'])) {
                    $amenity['items'] = array_values($amenity['items']);
                }
            }
        }

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $count = Event::where('slug', $validated['slug'])->count();
            if ($count > 0) {
                $validated['slug'] .= '-'.uniqid();
            }
        }

        // Extract categories
        $categories = $validated['categories'];
        unset($validated['categories']);

        // Process facilities - filter only enabled facilities
        if (isset($validated['facilities']) && is_array($validated['facilities'])) {
            $enabledFacilities = [];
            foreach ($validated['facilities'] as $key => $facility) {
                if (! empty($facility['enabled']) && ! empty($facility['name'])) {
                    $facilityData = [
                        'name' => $facility['name'],
                        'description' => $facility['description'] ?? '',
                    ];

                    // Handle facility image (Still using old method for now, or update if needed)
                    if (isset($request->facilities[$key]['image']) && $request->hasFile("facilities.{$key}.image")) {
                        $facilityData['image'] = $this->processImage($request->file("facilities.{$key}.image"), 'events/facilities', 800, 85);
                    }

                    $enabledFacilities[] = $facilityData;
                }
            }
            $validated['facilities'] = ! empty($enabledFacilities) ? $enabledFacilities : null;
        } else {
            $validated['facilities'] = null;
        }

        // Process Gallery (Already paths from Dropzone)
        // No processing needed, just usage
        if (isset($validated['gallery'])) {
            // Move temp files to permanent location if needed, or just use as is
            // Since we used 'events/temp' or similar in uploadMedia, we might want to move them?
            // But for simplicity, let's assume uploadMedia puts them in a usable place.
            // Ideally uploadMedia should accept a folder.
        }

        // Process Sponsors (Already paths)
        // No processing needed

        // Process Theme Colors (remove nulls)
        if (isset($validated['theme_colors']) && is_array($validated['theme_colors'])) {
            $validated['theme_colors'] = array_filter($validated['theme_colors']);
            if (empty($validated['theme_colors'])) {
                $validated['theme_colors'] = null;
            }
        }

        // Process jersey_sizes - ensure it's an array
        if (isset($validated['jersey_sizes']) && is_array($validated['jersey_sizes'])) {
            $validated['jersey_sizes'] = array_values(array_filter($validated['jersey_sizes']));
            if (empty($validated['jersey_sizes'])) {
                $validated['jersey_sizes'] = [];
            }
        } else {
            $validated['jersey_sizes'] = [];
        }

        // Extract jersey_stock — not a column on events table
        $jerseyStockInput = $validated['jersey_stock'] ?? [];
        unset($validated['jersey_stock']);

        // Process payment_config (Handle 'all' option)
        if (isset($validated['payment_config']['allowed_methods'])) {
            $methods = $validated['payment_config']['allowed_methods'];
            if (is_string($methods)) {
                $methods = [$methods];
            }
            if (in_array('all', $methods)) {
                $validated['payment_config']['allowed_methods'] = ['midtrans', 'moota', 'cod'];
            } else {
                $validated['payment_config']['allowed_methods'] = array_values(array_unique($methods));
            }
        }
        if (isset($validated['payment_config']['midtrans_demo_mode'])) {
            $validated['payment_config']['midtrans_demo_mode'] = (bool) $validated['payment_config']['midtrans_demo_mode'];
        }

        // Single images are now paths from Dropzone
        if (isset($validated['hero_image'])) {
            $validated['hero_image_url'] = null;
        }

        // Create event
        $event = Event::create($validated);

        // Create categories
        \Illuminate\Support\Facades\Log::info('Creating categories', ['categories' => $categories]);
        $hasPrizesColumn = Schema::hasColumn('race_categories', 'prizes');
        foreach ($categories as $categoryData) {
            $categoryData['event_id'] = $event->id;
            $categoryData['is_active'] = isset($categoryData['is_active']) ? (bool) $categoryData['is_active'] : true;
            if ($hasPrizesColumn) {
                $prizes = is_array($categoryData['prizes'] ?? null) ? $categoryData['prizes'] : [];
                $cleanedPrizes = [];
                $rank = 1;
                foreach ($prizes as $value) {
                    $value = is_string($value) ? trim($value) : $value;
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $cleanedPrizes[$rank++] = $value;
                }
                $categoryData['prizes'] = $cleanedPrizes;
            } else {
                unset($categoryData['prizes']);
            }
            \App\Models\RaceCategory::create($categoryData);
        }

        // Save Jersey Size Stock Quotas
        if ($event->premium_amenities['jersey']['enabled'] ?? false) {
            $stockData = ['event_id' => $event->id];
            foreach (['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'] as $size) {
                $col = strtolower($size);
                if (in_array($size, $validated['jersey_sizes'] ?? [])) {
                    $stockData[$col] = isset($jerseyStockInput[$size]) && $jerseyStockInput[$size] !== ''
                        ? (int) $jerseyStockInput[$size]
                        : null;
                } else {
                    $stockData[$col] = null;
                }
            }
            \App\Models\JerseySize::create($stockData);
        }

        return redirect()->route('eo.events.index')
            ->with('success', 'Event berhasil dibuat!');
    }

    public function show(Event $event)
    {
        $this->authorizeEvent($event);

        $event->load(['categories', 'user']);

        return view('eo.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $this->authorizeEvent($event);

        $event->load(['categories']);
        $gpxList = \App\Models\MasterGpx::where('is_published', true)->orderBy('title')->get();

        return view('eo.events.edit', compact('event', 'gpxList'));
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        // Debug Log
        \Illuminate\Support\Facades\Log::info('Update Event Payload:', $request->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:events,slug,'.$event->id,
            'hardcoded' => 'nullable|string|max:50',
            'short_description' => 'nullable|string',
            'full_description' => 'nullable|string',
            'terms_and_conditions' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after:start_at',
            'location_name' => 'required|string|max:255',
            'location_address' => 'nullable|string',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'rpc_location_name' => 'nullable|string|max:255',
            'rpc_location_address' => 'nullable|string',
            'rpc_latitude' => 'nullable|numeric',
            'rpc_longitude' => 'nullable|numeric',
            'hero_image_url' => 'nullable|url',
            'hero_image' => 'nullable|string',
            'logo_image' => 'nullable|string',
            'floating_image' => 'nullable|string',
            'medal_image' => 'nullable|string',
            'jersey_image' => 'nullable|string',
            'twibbon_image' => 'nullable|string',
            'map_embed_url' => 'nullable|string',
            'google_calendar_url' => 'nullable|url',
            'registration_open_at' => 'nullable|date',
            'registration_close_at' => 'nullable|date|after:registration_open_at',
            'promo_code' => 'nullable|string|max:50',
            'promo_buy_x' => 'nullable|integer|min:1',
            'custom_email_message' => 'nullable|string',
            'ticket_email_use_qr' => 'nullable|boolean',
            'show_participant_list' => 'required|boolean',
            'is_instant_notification' => 'nullable|boolean',
            'ticket_email_rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'blast_email_rate_limit_per_minute' => 'nullable|integer|min:1|max:10000',
            'facilities' => 'nullable|array',
            'facilities.*.name' => 'nullable|string|max:255',
            'facilities.*.description' => 'nullable|string',
            'facilities.*.enabled' => 'nullable|boolean',
            'facilities.*.image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'facilities.*.existing_image' => 'nullable|string',
            'gallery' => 'nullable|array',
            'gallery.*' => 'string',
            'sponsors' => 'nullable|array|max:30',
            'sponsors.*' => 'string',
            'theme_colors' => 'nullable|array',
            'theme_colors.dark' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.card' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.input' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.neon' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.neonHover' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.accent' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'theme_colors.danger' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'jersey_sizes' => 'nullable|array',
            'jersey_sizes.*' => 'nullable|string|in:XXS,XS,S,M,L,XL,2XL,3XL,4XL,5XL',
            'jersey_stock' => 'nullable|array',
            'jersey_stock.*' => 'nullable|integer|min:0',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required_with:addons|string|max:255',
            'addons.*.price' => 'nullable|numeric|min:0',
            'premium_amenities' => 'nullable|array',
            'template' => 'nullable|string|in:modern-dark,light-clean,simple-minimal,professional-city-run,paolo-fest,paolo-fest-dark,golden-run,quick-light',
            'platform_fee' => 'nullable|numeric|min:0',
            'categories' => 'sometimes|array',
            'categories.*.id' => 'nullable|exists:race_categories,id',
            'categories.*.master_gpx_id' => 'nullable|exists:master_gpxes,id',
            'categories.*.name' => 'required_with:categories|string|max:255',
            'categories.*.distance_km' => 'nullable|numeric|min:0',
            'categories.*.code' => 'nullable|string|max:50',
            'categories.*.quota' => 'nullable|integer|min:0',
            'categories.*.min_age' => 'nullable|integer|min:0',
            'categories.*.max_age' => 'nullable|integer|min:0',
            'categories.*.cutoff_minutes' => 'nullable|integer|min:0',
            'categories.*.price_early' => 'nullable|integer|min:0',
            'categories.*.price_regular' => 'nullable|integer|min:0',
            'categories.*.price_late' => 'nullable|integer|min:0',
            'categories.*.early_bird_quota' => 'nullable|integer|min:0',
            'categories.*.early_bird_end_at' => 'nullable|date',
            'categories.*.reg_start_at' => 'nullable|date',
            'categories.*.reg_end_at' => 'nullable|date|after:categories.*.reg_start_at',
            'categories.*.is_active' => 'nullable|boolean',
            'categories.*.prizes' => 'nullable|array',
            'categories.*.prizes.*' => 'nullable|string|max:255',
            'payment_config' => 'nullable|array',
            'payment_config.midtrans_demo_mode' => 'nullable|boolean',
            'payment_config.allowed_methods' => 'nullable|array|min:1',
            'payment_config.allowed_methods.*' => 'in:midtrans,moota,cod,all',
            'whatsapp_config' => 'nullable|array',
            'whatsapp_config.enabled' => 'nullable|boolean',
            'whatsapp_config.template' => 'nullable|string',
        ]);

        if (Schema::hasColumn('events', 'is_instant_notification')) {
            $validated['is_instant_notification'] = isset($validated['is_instant_notification']) ? (bool) $validated['is_instant_notification'] : false;
        } else {
            unset($validated['is_instant_notification']);
        }

        if (Schema::hasColumn('events', 'ticket_email_rate_limit_per_minute')) {
            $validated['ticket_email_rate_limit_per_minute'] = $validated['ticket_email_rate_limit_per_minute'] ?? null;
        } else {
            unset($validated['ticket_email_rate_limit_per_minute']);
        }

        if (Schema::hasColumn('events', 'blast_email_rate_limit_per_minute')) {
            $validated['blast_email_rate_limit_per_minute'] = $validated['blast_email_rate_limit_per_minute'] ?? null;
        } else {
            unset($validated['blast_email_rate_limit_per_minute']);
        }

        if (Schema::hasColumn('events', 'ticket_email_use_qr')) {
            $validated['ticket_email_use_qr'] = array_key_exists('ticket_email_use_qr', $validated)
                ? (bool) $validated['ticket_email_use_qr']
                : (bool) ($event->ticket_email_use_qr ?? true);
        } else {
            unset($validated['ticket_email_use_qr']);
        }

        // Default premium_amenities to empty array if not present (to allow unchecking all)
        if (! isset($validated['premium_amenities'])) {
            $validated['premium_amenities'] = [];
        } else {
            // Re-index nested items arrays to ensure JSON array structure (not object)
            foreach ($validated['premium_amenities'] as $key => &$amenity) {
                if (isset($amenity['items']) && is_array($amenity['items'])) {
                    $amenity['items'] = array_values($amenity['items']);
                }
            }
        }

        // Process facilities - filter only enabled facilities
        if (isset($validated['facilities']) && is_array($validated['facilities'])) {
            $enabledFacilities = [];
            foreach ($validated['facilities'] as $key => $facility) {
                if (! empty($facility['enabled']) && ! empty($facility['name'])) {
                    $facilityData = [
                        'name' => $facility['name'],
                        'description' => $facility['description'] ?? '',
                    ];

                    // Handle facility image
                    if (isset($request->facilities[$key]['image']) && $request->hasFile("facilities.{$key}.image")) {
                        // Delete old image if exists (optional, if we track it)
                        if (isset($facility['existing_image']) && ! empty($facility['existing_image'])) {
                            $this->deleteImage($facility['existing_image']);
                        }
                        $facilityData['image'] = $this->processImage($request->file("facilities.{$key}.image"), 'events/facilities', 800, 85);
                    } elseif (isset($facility['existing_image']) && ! empty($facility['existing_image'])) {
                        $facilityData['image'] = $facility['existing_image'];
                    }

                    $enabledFacilities[] = $facilityData;
                }
            }
            $validated['facilities'] = ! empty($enabledFacilities) ? $enabledFacilities : null;
        } else {
            $validated['facilities'] = null;
        }

        // Process Gallery
        // The frontend now sends the full ordered list of paths in 'gallery' array
        // If not present, it might mean cleared, or just not updated?
        // With Dropzone form logic, we should send empty array if cleared.
        // If 'gallery' key is missing from request, it might be safer to keep existing?
        // But with standard form submit, unchecked checkboxes/empty selects usually send nothing.
        // We will assume if 'gallery' is present (even empty array), we update.
        if ($request->has('gallery')) {
            $validated['gallery'] = $request->input('gallery');
        } else {
            // If completely missing from request (e.g. not even empty array), keep old?
            // Or maybe frontend sends empty array.
            // Let's assume frontend sends hidden inputs 'gallery[]'.
            $validated['gallery'] = null;
        }

        // Process Sponsors
        if ($request->has('sponsors')) {
            $validated['sponsors'] = $request->input('sponsors');
        } else {
            $validated['sponsors'] = null;
        }

        // Process Theme Colors (remove nulls)
        if (isset($validated['theme_colors']) && is_array($validated['theme_colors'])) {
            $validated['theme_colors'] = array_filter($validated['theme_colors']);
            if (empty($validated['theme_colors'])) {
                $validated['theme_colors'] = null;
            }
        }

        // Process jersey_sizes - ensure it's an array
        if (isset($validated['jersey_sizes']) && is_array($validated['jersey_sizes'])) {
            $validated['jersey_sizes'] = array_values(array_filter($validated['jersey_sizes']));
            if (empty($validated['jersey_sizes'])) {
                $validated['jersey_sizes'] = [];
            }
        } else {
            // Preserve existing jersey_sizes — do NOT reset when not submitted
            $validated['jersey_sizes'] = $event->jersey_sizes ?? [];
        }

        // Extract jersey_stock from validated (not a real DB column on events table)
        $jerseyStockInput = $validated['jersey_stock'] ?? [];
        unset($validated['jersey_stock']);

        // Process addons
        if (isset($validated['addons']) && is_array($validated['addons'])) {
            $processedAddons = [];
            foreach ($validated['addons'] as $addon) {
                if (! empty($addon['name'])) {
                    $processedAddons[] = [
                        'name' => $addon['name'],
                        'price' => isset($addon['price']) ? (int) $addon['price'] : 0,
                    ];
                }
            }
            $validated['addons'] = ! empty($processedAddons) ? $processedAddons : null;
        } else {
            $validated['addons'] = null;
        }

        $categories = [];
        if (isset($validated['categories']) && is_array($validated['categories'])) {
            $categories = $validated['categories'];
        }
        unset($validated['categories']);

        // Auto-generate slug if not provided and name changed
        if (empty($validated['slug']) && $validated['name'] !== $event->name) {
            $validated['slug'] = Str::slug($validated['name']);
            // Ensure uniqueness
            $count = Event::where('slug', $validated['slug'])->where('id', '!=', $event->id)->count();
            if ($count > 0) {
                $validated['slug'] .= '-'.uniqid();
            }
        } elseif (empty($validated['slug'])) {
            unset($validated['slug']);
        }

        // Single images - already paths
        if ($request->filled('hero_image')) {
            $validated['hero_image_url'] = null;
        }

        // Explicitly handle image updates if present in request but not in validated (though validate should catch them)
        // This ensures that if the frontend sends a new path, it overrides whatever was there
        foreach (['hero_image', 'logo_image', 'floating_image', 'medal_image', 'jersey_image'] as $imgField) {
            if ($request->has($imgField)) {
                $validated[$imgField] = $request->input($imgField);
            }
        }

        // Process payment_config (Handle 'all' option)
        if (isset($validated['payment_config']['allowed_methods'])) {
            $methods = $validated['payment_config']['allowed_methods'];
            if (is_string($methods)) {
                $methods = [$methods];
            }
            if (in_array('all', $methods)) {
                $validated['payment_config']['allowed_methods'] = ['midtrans', 'moota', 'cod'];
            } else {
                $validated['payment_config']['allowed_methods'] = array_values(array_unique($methods));
            }
        }
        if (isset($validated['payment_config']['midtrans_demo_mode'])) {
            $validated['payment_config']['midtrans_demo_mode'] = (bool) $validated['payment_config']['midtrans_demo_mode'];
        }

        if (array_key_exists('custom_email_message', $validated)) {
            $validated['custom_email_message'] = $this->normalizeCustomEmailMessage($validated['custom_email_message'] ?? null, $event);
        }

        $affectedCategoryIds = [];
        $deletedCategoryIds = [];

        DB::transaction(function () use ($event, $validated, $categories, $jerseyStockInput, &$affectedCategoryIds, &$deletedCategoryIds) {
            $event->update($validated);

            // Handle categories if provided
            if (! empty($categories)) {
                $existingCategoryIds = $event->categories()->pluck('id')->toArray();
                $submittedCategoryIds = array_values(array_filter(array_column($categories, 'id')));
                $raceCategoryColumns = Schema::getColumnListing('race_categories');
                $raceCategoryColumnMap = array_flip($raceCategoryColumns);
                $hasPrizesColumn = array_key_exists('prizes', $raceCategoryColumnMap);

                // Delete removed categories
                if (! empty($submittedCategoryIds)) {
                    $categoriesToDelete = array_diff($existingCategoryIds, $submittedCategoryIds);
                    if (! empty($categoriesToDelete)) {
                        \App\Models\RaceCategory::whereIn('id', $categoriesToDelete)->delete();
                        $deletedCategoryIds = array_merge($deletedCategoryIds, $categoriesToDelete);
                    }
                }

                // Update or create categories
                \Illuminate\Support\Facades\Log::info('Processing categories update', ['categories' => $categories]);
                foreach ($categories as $categoryData) {
                    $categoryId = $categoryData['id'] ?? null;
                    unset($categoryData['id']);

                    if ($hasPrizesColumn) {
                        $prizes = is_array($categoryData['prizes'] ?? null) ? $categoryData['prizes'] : [];
                        $cleanedPrizes = [];
                        $rank = 1;
                        foreach ($prizes as $value) {
                            $value = is_string($value) ? trim($value) : $value;
                            if ($value === null || $value === '') {
                                continue;
                            }
                            $cleanedPrizes[$rank++] = $value;
                        }
                        $categoryData['prizes'] = $cleanedPrizes;
                    } else {
                        unset($categoryData['prizes']);
                    }

                    if ($categoryId && in_array($categoryId, $existingCategoryIds)) {
                        // Update existing category
                        $category = $event->categories()->whereKey($categoryId)->first();
                        if ($category) {
                            $categoryData['is_active'] = isset($categoryData['is_active']) ? (bool) $categoryData['is_active'] : true;
                            $category->update($categoryData);
                            $affectedCategoryIds[] = $category->id;
                        }
                    } else {
                        // Create new category
                        $categoryData['event_id'] = $event->id;
                        $categoryData['is_active'] = isset($categoryData['is_active']) ? (bool) $categoryData['is_active'] : true;
                        $category = \App\Models\RaceCategory::create($categoryData);
                        $affectedCategoryIds[] = $category->id;
                    }
                }
            }

            // Handle Jersey Size Stock Quotas
            if ($event->premium_amenities['jersey']['enabled'] ?? false) {
                $stockData = [];
                foreach (['XXS', 'XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'] as $size) {
                    $col = strtolower($size);
                    if (in_array($size, $validated['jersey_sizes'] ?? [])) {
                        $stockData[$col] = isset($jerseyStockInput[$size]) && $jerseyStockInput[$size] !== ''
                            ? (int) $jerseyStockInput[$size]
                            : null;
                    } else {
                        $stockData[$col] = null;
                    }
                }
                $event->jerseyStock()->updateOrCreate(['event_id' => $event->id], $stockData);
            } else {
                $event->jerseyStock()->delete();
            }
        });

        // Invalidate cache
        $this->cacheService->invalidateEventCache($event);
        foreach (array_unique(array_merge($affectedCategoryIds, $deletedCategoryIds)) as $categoryId) {
            Cache::forget("category:quota:{$categoryId}");
        }

        return redirect()->route('eo.events.index')
            ->with('success', 'Event berhasil diperbarui!');
    }

    /**
     * Preview event (redirect to public event page)
     */
    public function preview(Event $event)
    {
        $this->authorizeEvent($event);

        return redirect()->route('events.show', $event->slug)
            ->with('preview', true);
    }

    public function duplicate(Event $event)
    {
        if ((int) $event->user_id !== (int) auth()->id()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Kamu tidak punya akses untuk menduplikasi event ini.',
                ], 403);
            }

            abort(403, 'Kamu tidak punya akses untuk menduplikasi event ini.');
        }

        $event->load(['categories']);

        $newEvent = DB::transaction(function () use ($event) {
            $newEvent = $event->replicate();
            $newEvent->name = $this->duplicateName($event->name);
            $newEvent->slug = $this->uniqueEventSlug($event->slug);
            $newEvent->created_at = now();
            $newEvent->updated_at = now();
            $newEvent->save();

            foreach ($event->categories as $category) {
                $newCategory = $category->replicate();
                $newCategory->event_id = $newEvent->id;
                $newCategory->created_at = now();
                $newCategory->updated_at = now();
                $newCategory->save();
            }

            $packages = \App\Models\EventPackage::query()->where('event_id', $event->id)->get();
            foreach ($packages as $package) {
                $newPackage = $package->replicate();
                $newPackage->event_id = $newEvent->id;
                $newPackage->sold_count = 0;
                $newPackage->is_sold_out = false;
                $newPackage->created_at = now();
                $newPackage->updated_at = now();
                $newPackage->save();
            }

            return $newEvent;
        });

        return redirect()
            ->route('eo.events.edit', $newEvent)
            ->with('success', 'Event berhasil diduplikasi!');
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);

        $event->delete();

        // Invalidate cache
        $this->cacheService->invalidateEventCache($event);

        return redirect()->route('eo.events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    /**
     * Show participants list for an event
     */
    public function participants(Event $event)
    {
        $this->authorizeEvent($event);

        $eventDetailAnalytics = $this->getPublicEventDetailAnalytics($event);

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction.coupon', 'category']);

        // Filter by payment status
        if (request()->has('payment_status') && request()->payment_status) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_status', request()->payment_status);
            });
        }

        // Filter by picked up status
        if (request()->has('is_picked_up') && request()->is_picked_up !== '') {
            $query->where('is_picked_up', request()->is_picked_up == '1');
        }

        // Filter by gender
        if (request()->has('gender') && request()->gender) {
            $query->where('gender', request()->gender);
        }

        // Filter by category
        if (request()->has('category_id') && request()->category_id) {
            $query->where('race_category_id', request()->category_id);
        }

        // Filter by coupon
        if (request()->has('coupon_id') && request()->coupon_id) {
            $query->whereHas('transaction', function ($q) {
                $q->where('coupon_id', request()->coupon_id);
            });
        }

        // Filter by addons
        if (request()->filled('addon')) {
            $addonFilter = request()->query('addon');
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        // Filter by Age Group
        if (request()->has('age_group') && request()->age_group) {
            $group = request()->age_group;
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

        if (request()->has('search') && trim(request()->search) !== '') {
            $search = trim(request()->search);
            $query->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('bib_number', 'like', "%{$search}%")
                    ->orWhere('id_card', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $sortBy = request()->query('sort_by', 'created_at');
        $sortDir = strtolower((string) request()->query('sort_dir', 'desc'));
        if (! in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $allowedSorts = [
            'created_at' => 'participants.created_at',
            'name' => 'participants.name',
            'id_card' => 'participants.id_card',
            'bib_number' => 'participants.bib_number',
            'is_picked_up' => 'participants.is_picked_up',
            'payment_status' => 'transactions.payment_status',
        ];

        $perPage = (int) request()->query('per_page', 20);
        if ($perPage < 5) {
            $perPage = 5;
        }
        if ($perPage > 200) {
            $perPage = 200;
        }

        if (! array_key_exists($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        if ($sortBy === 'payment_status') {
            $query->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
                ->where('transactions.event_id', $event->id)
                ->select('participants.*')
                ->orderBy('transactions.payment_status', $sortDir);
        } else {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        }

        $participants = $query->paginate($perPage)->withQueryString();

        $financials = [
            'gross_revenue' => \App\Models\Transaction::where('event_id', $event->id)
                ->where('payment_status', 'paid')
                ->sum('final_amount'),
            'platform_fee' => \App\Models\Transaction::where('event_id', $event->id)
                ->where('payment_status', 'paid')
                ->sum('admin_fee'),
        ];
        $financials['net_revenue'] = $financials['gross_revenue'] - $financials['platform_fee'];

        if (request()->ajax() || request()->wantsJson()) {
            // Handle Report AJAX
            if (request('action') === 'get_report') {
                $reportFilters = [
                    'start_date' => request('report_start_date'),
                    'end_date' => request('report_end_date'),
                    'ticket_type' => request('report_ticket_type'),
                    'sort_dir' => request('report_sort_dir'),
                ];
                $eventReport = $this->reportService->getEventReport($event, $reportFilters);
                $eventReport['jersey_sizes_pending_pickup'] = $this->getJerseyPendingPickupCounts($event);

                return response()->json([
                    'success' => true,
                    'report' => $eventReport,
                ]);
            }

            $items = $participants->getCollection()->map(function ($p) use ($event) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'gender' => $p->gender,
                    'email' => $p->email,
                    'phone' => $p->phone,
                    'id_card' => $p->id_card,
                    'date_of_birth' => $p->date_of_birth ? $p->date_of_birth->toDateString() : null,
                    'address' => $p->address,
                    'city' => $p->city,
                    'province' => $p->province,
                    'postal_code' => $p->postal_code,
                    'category' => $p->category ? $p->category->name : '-',
                    'race_category_id' => $p->race_category_id,
                    'bib_number' => $p->bib_number,
                    'age_group' => $p->getAgeGroup($event->start_at),
                    'jersey_size' => $p->jersey_size,
                    'created_at' => $p->created_at ? $p->created_at->format('d M Y') : '',
                    'payment_status' => $p->transaction->payment_status ?? 'pending',
                    'transaction_id' => $p->transaction->id,
                    'is_picked_up' => $p->is_picked_up,
                    'picked_up_by' => $p->picked_up_by,
                    'payment_update_url' => route('eo.events.transactions.payment-status', [$event, $p->transaction->id]),
                    'pic_name' => $p->transaction->pic_data['name'] ?? '-',
                    'pic_phone' => $p->transaction->pic_data['phone'] ?? '-',
                    'pic_email' => $p->transaction->pic_data['email'] ?? '-',
                    'transaction_date' => $p->transaction->created_at ? $p->transaction->created_at->format('d M Y H:i') : '-',
                    'payment_method' => $p->transaction->payment_gateway ?? '-',
                    'coupon_code' => $p->transaction->coupon?->code ?? null,
                    'coupon_id' => $p->transaction->coupon_id ?? null,
                    'addons' => $p->addons,
                ];
            });

            $stats = [
                'total_registered' => $participants->total(),
                'paid_confirmed' => \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                    $q->where('event_id', $event->id)->where('payment_status', 'paid');
                })->count(),
                'race_pack_picked_up' => \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                    $q->where('event_id', $event->id);
                })->where('is_picked_up', true)->count(),
                'pending_pickup' => \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
                    $q->where('event_id', $event->id)->where('payment_status', 'paid');
                })->where('is_picked_up', false)->count(),
                'jersey_sizes_pending_pickup' => $this->getJerseyPendingPickupCounts($event),
            ];

            return response()->json([
                'success' => true,
                'data' => $items,
                'stats' => $stats,
                'financials' => $financials,
                'meta' => [
                    'current_page' => $participants->currentPage(),
                    'last_page' => $participants->lastPage(),
                    'per_page' => $participants->perPage(),
                    'total' => $participants->total(),
                    'next_page_url' => $participants->nextPageUrl(),
                    'prev_page_url' => $participants->previousPageUrl(),
                ],
            ]);
        }

        // Initial Report Data
        $eventReport = $this->reportService->getEventReport($event, [
            'start_date' => request('report_start_date'),
            'end_date' => request('report_end_date'),
            'ticket_type' => request('report_ticket_type'),
        ]);
        $eventReport['jersey_sizes_pending_pickup'] = $this->getJerseyPendingPickupCounts($event);

        $reportLink = URL::signedRoute('report.show', ['event' => $event->id]);

        $bibNumbers = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->whereNotNull('bib_number')
            ->pluck('bib_number');

        $nextBibNumber = null;

        if ($bibNumbers->isNotEmpty()) {
            $maxNumber = null;
            $prefix = '';
            $suffixLength = null;

            foreach ($bibNumbers as $bib) {
                if (! preg_match('/(\d+)\s*$/', $bib, $m)) {
                    continue;
                }

                $number = (int) $m[1];

                if ($maxNumber === null || $number > $maxNumber) {
                    $maxNumber = $number;
                    $suffixLength = strlen($m[1]);
                    $prefix = substr($bib, 0, -$suffixLength);
                }
            }

            if ($maxNumber !== null) {
                $next = $maxNumber + 1;
                $nextBibNumber = $prefix.str_pad((string) $next, $suffixLength, '0', STR_PAD_LEFT);
            }
        }

        if (! $nextBibNumber) {
            $nextBibNumber = '1';
        }

        $coupons = \App\Models\Coupon::where('event_id', $event->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('eo.events.participants', compact('event', 'participants', 'financials', 'eventReport', 'reportLink', 'nextBibNumber', 'coupons', 'eventDetailAnalytics'));
    }

    private function getJerseyPendingPickupCounts(Event $event): array
    {
        $soldStatuses = ['paid'];

        $raw = \App\Models\Participant::query()
            ->join('transactions', 'transactions.id', '=', 'participants.transaction_id')
            ->where('transactions.event_id', $event->id)
            ->whereIn('transactions.payment_status', $soldStatuses)
            ->where('participants.is_picked_up', false)
            ->whereNotNull('participants.jersey_size')
            ->where('participants.jersey_size', '!=', '')
            ->selectRaw('UPPER(participants.jersey_size) as jersey_size, COUNT(*) as total')
            ->groupBy('jersey_size')
            ->pluck('total', 'jersey_size')
            ->toArray();

        $normalized = [];
        foreach ($raw as $size => $total) {
            $key = strtoupper(trim((string) $size));
            if ($key === 'XXL') {
                $key = '2XL';
            } elseif ($key === 'XXXL') {
                $key = '3XL';
            }
            $normalized[$key] = (int) ($normalized[$key] ?? 0) + (int) $total;
        }

        return $normalized;
    }

    public function participantsApi(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $perPage = (int) $request->query('per_page', 200);
        if ($perPage < 1) {
            $perPage = 1;
        }
        if ($perPage > 500) {
            $perPage = 500;
        }

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction', 'category']);

        if ($request->filled('payment_status')) {
            $status = $request->query('payment_status');
            $query->whereHas('transaction', function ($q) use ($status) {
                $q->where('payment_status', $status);
            });
        }

        if ($request->has('is_picked_up') && $request->query('is_picked_up') !== '') {
            $query->where('is_picked_up', (string) $request->query('is_picked_up') === '1');
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->query('gender'));
        }

        if ($request->filled('category_id')) {
            $query->where('race_category_id', $request->query('category_id'));
        }

        if ($request->filled('addon')) {
            $addonFilter = $request->query('addon');
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        if ($request->filled('age_group')) {
            $group = $request->query('age_group');
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

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('bib_number', 'like', "%{$search}%")
                    ->orWhere('id_card', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $participants = $query->orderBy('id')->cursorPaginate($perPage)->withQueryString();

        $items = collect($participants->items())->map(function ($p) use ($event) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'gender' => $p->gender,
                'email' => $p->email,
                'phone' => $p->phone,
                'id_card' => $p->id_card,
                'address' => $p->address,
                'city' => $p->city,
                'province' => $p->province,
                'postal_code' => $p->postal_code,
                'race_category_id' => $p->race_category_id,
                'category' => $p->category ? $p->category->name : '-',
                'bib_number' => $p->bib_number,
                'date_of_birth' => $p->date_of_birth ? $p->date_of_birth->toDateString() : null,
                'age_group' => $p->getAgeGroup($event->start_at),
                'jersey_size' => $p->jersey_size,
                'blood_type' => $p->blood_type,
                'created_at' => $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null,
                'payment_status' => $p->transaction->payment_status ?? 'pending',
                'payment_update_url' => route('eo.events.participants.payment-update', ['event' => $event->slug, 'participant' => $p->id]),
                'payment_method' => $p->transaction->payment_channel ?? $p->transaction->payment_gateway ?? '-',
                'transaction_id' => $p->transaction->id,
                'transaction_date' => $p->transaction->created_at ? $p->transaction->created_at->format('Y-m-d H:i:s') : '-',
                'is_picked_up' => $p->is_picked_up,
                'picked_up_at' => $p->picked_up_at ? $p->picked_up_at->format('Y-m-d H:i:s') : null,
                'picked_up_by' => $p->picked_up_by,
                'pic_name' => $p->pic_name,
                'pic_phone' => $p->pic_phone,
                'pic_email' => $p->pic_email,
                'addons' => $p->addons,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'per_page' => $participants->perPage(),
                'next_cursor' => $participants->nextCursor()?->encode(),
                'prev_cursor' => $participants->previousCursor()?->encode(),
            ],
        ]);
    }

    public function storeParticipant(Request $request, Event $event, StoreManualParticipantAction $action)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            'id_card' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'category_id' => [
                'required',
                'exists:race_categories,id',
                function ($attribute, $value, $fail) use ($event) {
                    $category = RaceCategory::find($value);
                    if (! $category || (int) $category->event_id !== (int) $event->id) {
                        $fail('Kategori tidak valid untuk event ini.');
                    }
                },
            ],
            'date_of_birth' => 'nullable|date|before:today',
            'target_time' => ['nullable', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', 'not_in:00:00:00'],
            'jersey_size' => 'nullable|string|max:10',
            'blood_type' => 'nullable|string|in:A,B,AB,O',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|min:10|max:15|regex:/^[0-9]+$/',
            'bib_number' => ['nullable', 'string', 'max:20', Rule::unique('participants', 'bib_number')],
            'send_whatsapp' => 'nullable|boolean',
            'use_queue' => 'nullable|boolean',
            'coupon_id' => [
                'nullable',
                'exists:coupons,id',
                function ($attribute, $value, $fail) use ($event) {
                    $coupon = \App\Models\Coupon::find($value);
                    if ($coupon && (int) $coupon->event_id !== (int) $event->id) {
                        $fail('Kupon tidak valid untuk event ini.');
                    }
                },
            ],
        ]);

        $transaction = $action->execute($event, $validated, $request->user());

        if ($request->boolean('use_queue')) {
            \App\Jobs\SendEventRegistrationNotification::dispatch($transaction);
        } else {
            \App\Jobs\SendEventRegistrationNotification::dispatchSync($transaction);
        }

        if ($request->wantsJson() || $request->ajax()) {
            $participant = $transaction->participants->first();

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->id,
                'participant_id' => $participant?->id,
            ], 201);
        }

        return redirect()
            ->route('eo.events.participants', $event)
            ->with('success', 'Peserta berhasil ditambahkan dan email konfirmasi dikirim.');
    }

    public function downloadParticipantsImportTemplate(Event $event)
    {
        $this->authorizeEvent($event);

        $headers = [
            'group_key',
            'pic_name',
            'pic_email',
            'pic_phone',
            'name',
            'email',
            'phone',
            'gender',
            'category_id',
            'id_card',
            'address',
            'city',
            'province',
            'postal_code',
            'emergency_contact_name',
            'emergency_contact_number',
            'date_of_birth',
            'target_time',
            'jersey_size',
            'blood_type',
            'bib_number',
            'payment_status',
            'coupon_code',
        ];

        $example = [
            'GROUP-001',
            'PIC Name',
            'pic@example.com',
            '081234567890',
            'Nama Peserta',
            'peserta@example.com',
            '081234567891',
            'male',
            optional($event->categories->first())->id ?: '',
            'IDCARD-001',
            'Alamat peserta',
            '',
            '',
            '',
            '',
            '',
            '1990-01-01',
            '01:30:00',
            'M',
            'O',
            '',
            'paid',
            '',
        ];

        $out = implode(',', $headers)."\n".implode(',', array_map(function ($v) {
            $v = (string) $v;
            $v = str_replace('"', '""', $v);
            return '"'.$v.'"';
        }, $example))."\n";

        $filename = 'participants-import-template-'.$event->id.'.csv';

        return response($out, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function importParticipantsCsv(Request $request, Event $event, \App\Actions\EO\ImportParticipantsCsvAction $action)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'file' => 'required|file|max:5120|mimes:csv,txt',
            'dry_run' => 'nullable|boolean',
            'send_email_if_paid' => 'nullable|boolean',
            'use_queue' => 'nullable|boolean',
        ]);

        $result = $action->execute(
            $event,
            $request->file('file'),
            [
                'dry_run' => $request->boolean('dry_run', true),
                'send_email_if_paid' => $request->boolean('send_email_if_paid', true),
                'use_queue' => $request->boolean('use_queue', true),
            ],
            $request->user()
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Update participant details
     */
    public function updateParticipant(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        // Ensure participant belongs to event (via Transaction or Category)
        $belongsToEvent = false;
        if ($participant->transaction && $participant->transaction->event_id == $event->id) {
            $belongsToEvent = true;
        } elseif ($participant->category && $participant->category->event_id == $event->id) {
            $belongsToEvent = true;
        }

        if (! $belongsToEvent) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|min:8|max:20',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'race_category_id' => 'required|exists:race_categories,id',
            'bib_number' => ['nullable', 'string', 'max:20', Rule::unique('participants', 'bib_number')->ignore($participant->id)],
            'jersey_size' => 'nullable|string|max:10',
            'blood_type' => 'nullable|string|in:A,B,AB,O',
            'is_picked_up' => 'nullable|boolean',
            'coupon_id' => 'nullable|exists:coupons,id',
            'target_time' => ['nullable', 'string', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'],
            'pic_name' => 'nullable|string|max:255',
            'pic_email' => 'nullable|email|max:255',
            'pic_phone' => 'nullable|string|min:8|max:20',
            'addons' => 'nullable|array|max:50',
            'addons.*' => 'array',
            'addons.*.name' => 'nullable|string|max:100',
            'addons.*.value' => 'nullable',
        ]);

        if (!empty($validated['jersey_size'])) {
            $sz = strtoupper(trim($validated['jersey_size']));
            if ($sz === 'XXL') {
                $sz = '2XL';
            } elseif ($sz === 'XXXL') {
                $sz = '3XL';
            }
            $validated['jersey_size'] = $sz;
        }

        // If is_picked_up is toggled, handle timestamp
        if (isset($validated['is_picked_up'])) {
            $validated['is_picked_up'] = (bool) $validated['is_picked_up'];
            if ($validated['is_picked_up'] && ! $participant->is_picked_up) {
                $validated['picked_up_at'] = now();
                $validated['picked_up_by'] = auth()->user()->name ?? 'Admin';
            } elseif (! $validated['is_picked_up'] && $participant->is_picked_up) {
                $validated['picked_up_at'] = null;
                $validated['picked_up_by'] = null;
            }
        }

        if ($participant->transaction && ($request->has('pic_name') || $request->has('pic_email') || $request->has('pic_phone'))) {
            $pic = is_array($participant->transaction->pic_data) ? $participant->transaction->pic_data : [];

            $picName = trim((string) $request->input('pic_name', ''));
            $picEmail = trim((string) $request->input('pic_email', ''));
            $picPhone = trim((string) $request->input('pic_phone', ''));

            if ($picName === '') {
                unset($pic['name']);
            } else {
                $pic['name'] = $picName;
            }
            if ($picEmail === '') {
                unset($pic['email']);
            } else {
                $pic['email'] = $picEmail;
            }
            if ($picPhone === '') {
                unset($pic['phone']);
            } else {
                $pic['phone'] = $picPhone;
            }

            $participant->transaction->update([
                'pic_data' => empty($pic) ? null : $pic,
            ]);
        }

        $participantData = $validated;
        unset($participantData['coupon_id'], $participantData['pic_name'], $participantData['pic_email'], $participantData['pic_phone']);
        $participant->update($participantData);

        if ($participant->transaction) {
            $transaction = $participant->transaction;
            $event = $transaction->event;

            // Load all participants for this transaction to calculate totals
            $allParticipants = $transaction->participants()->with('category')->get();

            $categoryQuantities = [];
            foreach ($allParticipants as $p) {
                if ($p->category) {
                    $catId = $p->category->id;
                    $categoryQuantities[$catId] = ($categoryQuantities[$catId] ?? 0) + 1;
                }
            }

            $totalOriginal = 0;
            foreach ($categoryQuantities as $catId => $qty) {
                $category = \App\Models\RaceCategory::find($catId);
                if (!$category) continue;

                $catParticipants = $allParticipants->where('race_category_id', $catId);
                $categorySum = 0;

                foreach ($catParticipants as $p) {
                    $priceType = $p->price_type ?? 'regular';
                    $price = 0;
                    if ($priceType === 'early' && isset($category->price_early) && $category->price_early > 0) {
                        $price = (int) $category->price_early;
                    } elseif ($priceType === 'late' && isset($category->price_late) && $category->price_late > 0) {
                        $price = (int) $category->price_late;
                    } else {
                        $price = (int) ($category->price_regular ?? 0);
                    }
                    $categorySum += $price;
                }

                if ($event->promo_buy_x && $event->promo_buy_x > 0) {
                    $bundleSize = $event->promo_buy_x + 1;
                    $freeCount = floor($qty / $bundleSize);
                    if ($freeCount > 0) {
                        $avgPrice = $categorySum / $qty;
                        $categorySum -= $avgPrice * $freeCount;
                    }
                }

                $totalOriginal += $categorySum;
            }

            // Sum addons price
            $totalAddonsPrice = 0;
            foreach ($allParticipants as $p) {
                if (! empty($p->addons) && is_array($p->addons)) {
                    foreach ($p->addons as $addon) {
                        $price = isset($addon['price']) ? (int) $addon['price'] : (isset($addon['value']) ? (int) $addon['value'] : 0);
                        $totalAddonsPrice += $price;
                    }
                }
            }
            $totalOriginal += $totalAddonsPrice;

            // Apply coupon
            $couponId = array_key_exists('coupon_id', $validated) ? $validated['coupon_id'] : $transaction->coupon_id;
            $discountAmount = 0.00;
            if ($couponId) {
                $coupon = Coupon::find($couponId);
                if ($coupon) {
                    $discountAmount = (float) $coupon->applyDiscount((float) $totalOriginal);
                }
            }

            // Recalculate platform fee
            $totalParticipants = $allParticipants->count();
            $platformFeePerParticipant = $event->platform_fee ?? 0;
            
            if (($totalOriginal - $discountAmount) <= 0) {
                $totalAdminFee = 0;
            } else {
                $totalAdminFee = $platformFeePerParticipant * $totalParticipants;
            }

            $finalAmount = ($totalOriginal - $discountAmount) + $totalAdminFee;
            if ($finalAmount < 0) {
                $finalAmount = 0;
            }

            if ($transaction->payment_gateway === 'moota' && $transaction->unique_code > 0) {
                $finalAmount += (float) $transaction->unique_code;
            }

            $transaction->update([
                'coupon_id' => $couponId ?: null,
                'total_original' => $totalOriginal,
                'discount_amount' => $discountAmount,
                'admin_fee' => $totalAdminFee,
                'final_amount' => $finalAmount,
            ]);
        }

        // Refresh to get relationship data if needed (e.g. category name)
        $participant->load(['category', 'transaction.coupon', 'transaction.user']);

        return response()->json([
            'success' => true,
            'message' => 'Data peserta berhasil diperbarui.',
            'data' => [
                'id' => $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'gender' => $participant->gender,
                'date_of_birth' => $participant->date_of_birth ? $participant->date_of_birth->toDateString() : null,
                'address' => $participant->address,
                'city' => $participant->city,
                'province' => $participant->province,
                'postal_code' => $participant->postal_code,
                'race_category_id' => $participant->race_category_id,
                'category_name' => $participant->category->name ?? '-',
                'target_time' => $participant->target_time,
                'bib_number' => $participant->bib_number,
                'jersey_size' => $participant->jersey_size,
                'blood_type' => $participant->blood_type,
                'age_group' => $participant->getAgeGroup($event->start_at),
                'is_picked_up' => $participant->is_picked_up,
                'picked_up_at' => $participant->picked_up_at,
                'picked_up_by' => $participant->picked_up_by,
                'coupon_id' => $participant->transaction->coupon_id ?? null,
                'coupon_code' => $participant->transaction->coupon?->code ?? null,
                'pic_name' => $participant->pic_name,
                'pic_phone' => $participant->pic_phone,
                'pic_email' => $participant->pic_email,
                'addons' => $participant->addons,
            ],
        ]);
    }

    /**
     * Resend event registration email to a specific participant
     */
    public function resendEmail(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_id' => 'required|exists:participants,id',
        ]);

        $participant = \App\Models\Participant::with('transaction')->find($request->participant_id);

        $participantEventId = (int) ($participant?->transaction?->event_id ?? 0);
        if (! $participant || $participantEventId !== (int) $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Participant not found or does not belong to this event.',
            ], 404);
        }

        try {
            // Construct data for the email
            // We pass a collection containing only this participant so the email is specific to them
            $participants = collect([$participant]);

            Mail::to($participant->email)->send(
                new EventRegistrationSuccess(
                    $event,
                    $participant->transaction,
                    $participants,
                    $participant->name
                )
            );

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully to '.$participant->email,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Resend Email Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend event registration email to multiple participants in bulk
     */
    public function resendEmailBulk(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:participants,id',
        ]);

        $participants = \App\Models\Participant::with('transaction')
            ->whereIn('id', $request->participant_ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->get();

        if ($participants->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada peserta valid yang ditemukan.',
            ], 400);
        }

        $sentCount = 0;
        $errors = [];

        foreach ($participants as $participant) {
            try {
                Mail::to($participant->email)->send(
                    new EventRegistrationSuccess(
                        $event,
                        $participant->transaction,
                        collect([$participant]),
                        $participant->name
                    )
                );
                $sentCount++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Bulk Resend Email Error for ' . $participant->email . ': ' . $e->getMessage());
                $errors[] = $participant->email;
            }
        }

        if ($sentCount > 0) {
            $msg = "Berhasil mengirim {$sentCount} email konfirmasi tiket.";
            if (count($errors) > 0) {
                $msg .= " Gagal mengirim ke: " . implode(', ', $errors);
            }
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim email konfirmasi tiket ke semua peserta terpilih.',
        ], 500);
    }

    /**
     * Export participants as CSV
     */
    public function exportParticipants(Event $event)
    {
        $this->authorizeEvent($event);

        \Illuminate\Support\Facades\Log::info('participants_export_csv_start', [
            'event_id' => $event->id,
            'payment_status' => request()->payment_status,
            'is_picked_up' => request()->is_picked_up,
            'gender' => request()->gender,
            'category_id' => request()->category_id,
            'addon' => request()->query('addon'),
        ]);

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction', 'category']);

        // Apply same filters as list
        if (request()->has('payment_status') && request()->payment_status) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_status', request()->payment_status);
            });
        }

        if (request()->has('is_picked_up') && request()->is_picked_up !== '') {
            $query->where('is_picked_up', request()->is_picked_up == '1');
        }

        // Filter by gender
        if (request()->has('gender') && request()->gender) {
            $query->where('gender', request()->gender);
        }

        // Filter by category
        if (request()->has('category_id') && request()->category_id) {
            $query->where('race_category_id', request()->category_id);
        }

        // Filter by coupon
        if (request()->has('coupon_id') && request()->coupon_id) {
            $query->whereHas('transaction', function ($q) {
                $q->where('coupon_id', request()->coupon_id);
            });
        }

        if (request()->filled('addon')) {
            $addonFilter = request()->query('addon');
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        $filename = 'participants_'.$event->slug.'_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $queryForStream = clone $query;

        $callback = function () use ($queryForStream) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, \App\Services\GoogleSheetsParticipantExporter::OUTPUT_COLUMNS);

            // Data
            $rowNumber = 0;
            $queryForStream->orderBy('id')->chunkById(1000, function ($participants) use ($file, &$rowNumber) {
                foreach ($participants as $participant) {
                    $rowNumber++;
                    fputcsv($file, [
                        $rowNumber,
                        $participant->name,
                        ucfirst($participant->gender ?? '-'),
                        $participant->email,
                        $participant->phone,
                        $participant->id_card,
                        $participant->address ?? '-',
                        $participant->category ? $participant->category->name : '-',
                        $participant->bib_number ?? '-',
                        $participant->jersey_size ?? '-',
                        $participant->blood_type ?? '-',
                        (! empty($participant->addons) && is_array($participant->addons)) ? collect($participant->addons)->pluck('name')->filter()->implode(', ') : '-',
                        $participant->target_time ?? '-',
                        ucfirst($participant->transaction->payment_status ?? 'pending'),
                        $participant->is_picked_up ? 'Sudah Diambil' : 'Belum Diambil',
                        $participant->created_at ? $participant->created_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_by ?? '-',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportParticipantsXlsx(Event $event)
    {
        $this->authorizeEvent($event);

        \Illuminate\Support\Facades\Log::info('participants_export_xlsx_start', [
            'event_id' => $event->id,
            'payment_status' => request()->payment_status,
            'is_picked_up' => request()->is_picked_up,
            'gender' => request()->gender,
            'category_id' => request()->category_id,
            'addon' => request()->query('addon'),
        ]);

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction', 'category']);

        if (request()->has('payment_status') && request()->payment_status) {
            $query->whereHas('transaction', function ($q) {
                $q->where('payment_status', request()->payment_status);
            });
        }

        if (request()->has('is_picked_up') && request()->is_picked_up !== '') {
            $query->where('is_picked_up', request()->is_picked_up == '1');
        }

        if (request()->has('gender') && request()->gender) {
            $query->where('gender', request()->gender);
        }

        if (request()->has('category_id') && request()->category_id) {
            $query->where('race_category_id', request()->category_id);
        }

        // Filter by coupon
        if (request()->has('coupon_id') && request()->coupon_id) {
            $query->whereHas('transaction', function ($q) {
                $q->where('coupon_id', request()->coupon_id);
            });
        }

        if (request()->filled('addon')) {
            $addonFilter = request()->query('addon');
            if ($addonFilter === 'with') {
                $query->whereNotNull('addons')->whereJsonLength('addons', '>', 0);
            } elseif ($addonFilter === 'without') {
                $query->where(function ($q) {
                    $q->whereNull('addons')->orWhereJsonLength('addons', 0);
                });
            } else {
                $query->whereJsonContains('addons', ['name' => $addonFilter]);
            }
        }

        $filename = 'participants_'.$event->slug.'_'.date('Y-m-d').'.xlsx';

        $queryForStream = clone $query;

        return response()->streamDownload(function () use ($queryForStream) {
            $writer = new \OpenSpout\Writer\XLSX\Writer;
            $writer->openToFile('php://output');

            $writer->addRow(\OpenSpout\Common\Entity\Row::fromValues(\App\Services\GoogleSheetsParticipantExporter::OUTPUT_COLUMNS));

            $rowNumber = 0;
            $queryForStream->orderBy('id')->chunkById(1000, function ($participants) use (&$rowNumber, $writer) {
                $rows = [];
                foreach ($participants as $participant) {
                    $rowNumber++;
                    $rows[] = \OpenSpout\Common\Entity\Row::fromValues([
                        $rowNumber,
                        $participant->name,
                        ucfirst($participant->gender ?? '-'),
                        $participant->email,
                        $participant->phone,
                        $participant->id_card,
                        $participant->address ?? '-',
                        $participant->category ? $participant->category->name : '-',
                        $participant->bib_number ?? '-',
                        $participant->jersey_size ?? '-',
                        $participant->blood_type ?? '-',
                        (! empty($participant->addons) && is_array($participant->addons)) ? collect($participant->addons)->pluck('name')->filter()->implode(', ') : '-',
                        $participant->target_time ?? '-',
                        ucfirst($participant->transaction->payment_status ?? 'pending'),
                        $participant->is_picked_up ? 'Sudah Diambil' : 'Belum Diambil',
                        $participant->created_at ? $participant->created_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : '-',
                        $participant->picked_up_by ?? '-',
                    ]);
                }
                if ($rows) {
                    $writer->addRows($rows);
                }
            });

            $writer->close();
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Google Sheets export removed as per request

    /**
     * Delete a participant (only if not paid)
     */
    public function destroyParticipant(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        // Check if participant belongs to event via Transaction OR Category
        $belongsToEvent = false;

        if ($participant->transaction && $participant->transaction->event_id == $event->id) {
            $belongsToEvent = true;
        } elseif ($participant->category && $participant->category->event_id == $event->id) {
            $belongsToEvent = true;
        } elseif ($participant->package && $participant->package->event_id == $event->id) {
            $belongsToEvent = true;
        }

        if (! $belongsToEvent) {
            abort(403, 'Unauthorized: Participant does not belong to this event');
        }

        $status = $participant->transaction->payment_status ?? 'pending';
        if ($status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Peserta dengan transaksi paid tidak dapat dihapus',
            ], 422);
        }
        $participant->delete();
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Peserta berhasil dihapus',
            ]);
        }

        return back()->with('success', 'Peserta berhasil dihapus');
    }

    public function clearParticipants(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $includePaid = (bool) $request->boolean('include_paid', false);

        $baseQuery = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        });

        if ($includePaid) {
            $validated = $request->validate([
                'confirm' => 'required|string',
            ]);

            if (($validated['confirm'] ?? '') !== 'DELETE_ALL') {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfirmasi tidak valid. Ketik DELETE_ALL untuk menghapus peserta termasuk paid.',
                ], 422);
            }

            $deletedCount = (clone $baseQuery)->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} peserta (termasuk paid).",
                'deleted' => $deletedCount,
                'skipped' => 0,
            ]);
        }

        $protectedStatuses = ['paid', 'settlement', 'capture'];

        $skippedCount = (clone $baseQuery)
            ->whereHas('transaction', function ($q) use ($protectedStatuses) {
                $q->whereIn('payment_status', $protectedStatuses);
            })
            ->count();

        $deletedCount = (clone $baseQuery)
            ->whereHas('transaction', function ($q) use ($protectedStatuses) {
                $q->where(function ($t) use ($protectedStatuses) {
                    $t->whereNull('payment_status')
                        ->orWhereNotIn('payment_status', $protectedStatuses);
                });
            })
            ->delete();

        $message = "Berhasil menghapus {$deletedCount} peserta.";
        if ($skippedCount > 0) {
            $message .= " {$skippedCount} peserta dilewati karena transaksi sudah paid.";
        }

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deletedCount,
                'skipped' => $skippedCount,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Update participant picked up status
     */
    public function updateParticipantStatus(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        // Verify participant belongs to this event
        $participantEventId = (int) ($participant?->transaction?->event_id ?? 0);
        if ($participantEventId !== (int) $event->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'is_picked_up' => 'required|boolean',
            'picked_up_by' => 'nullable|string|max:255',
        ]);

        $wasPickedUp = (bool) $participant->is_picked_up;
        $isPickedUp = (bool) $validated['is_picked_up'];
        if ($isPickedUp) {
            $paymentStatus = (string) ($participant->transaction->payment_status ?? '');
            if (! in_array($paymentStatus, ['paid', 'cod'], true)) {
                $message = 'Tidak bisa pickup: status pembayaran belum paid.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return back()->with('error', $message);
            }
        }

        $participant->update([
            'is_picked_up' => $isPickedUp,
            'picked_up_at' => $isPickedUp ? now() : null,
            'picked_up_by' => $isPickedUp ? (($validated['picked_up_by'] ?? null) ?: (auth()->user()->name ?? null)) : null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pengambilan berhasil diperbarui',
                'pickup_changed' => $wasPickedUp !== $isPickedUp,
                'participant' => [
                    'id' => $participant->id,
                    'name' => $participant->name,
                    'bib_number' => $participant->bib_number,
                    'jersey_size' => $participant->jersey_size,
                    'is_picked_up' => (bool) $participant->is_picked_up,
                    'picked_up_at' => $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : null,
                    'picked_up_by' => $participant->picked_up_by,
                    'payment_status' => $participant->transaction->payment_status ?? 'pending',
                ],
                'jersey_sizes_pending_pickup' => $this->getJerseyPendingPickupCounts($event),
            ]);
        }

        return back()->with('success', 'Status pengambilan berhasil diperbarui');
    }

    /**
     * Update transaction payment status
     */
    public function updatePaymentStatus(Request $request, Event $event, $transaction_id)
    {
        $this->authorizeEvent($event);

        $transaction = \App\Models\Transaction::findOrFail($transaction_id);

        // Verify transaction belongs to this event
        if ($transaction->event_id != $event->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,expired,cod',
        ]);

        $transaction->update([
            'payment_status' => $validated['payment_status'],
            'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
        ]);

        if ($validated['payment_status'] === 'paid') {
            \App\Jobs\ProcessPaidEventTransaction::dispatchAfterResponse($transaction);
        }

        // Always return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran berhasil diperbarui',
            ]);
        }

        return back()->with('success', 'Status pembayaran berhasil diperbarui');
    }

    protected function authorizeEvent(Event $event)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Kamu tidak punya akses untuk mengelola event ini.');
        }

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'eo' && (int) $event->user_id === (int) $user->id) {
            return;
        }

        abort(403, 'Kamu tidak punya akses untuk mengelola event ini.');
    }

    private function getPublicEventDetailAnalytics(Event $event): array
    {
        if (! Schema::hasTable('eo_page_stats')) {
            return [
                'today' => ['views' => 0, 'unique' => 0],
                'last30' => ['views' => 0, 'unique' => 0],
            ];
        }

        $today = now()->toDateString();
        $page = 'public_event_detail';

        $todayRow = DB::table('eo_page_stats')
            ->where('event_id', $event->id)
            ->where('page', $page)
            ->where('stat_date', $today)
            ->first();

        $from = now()->subDays(29)->startOfDay()->toDateString();
        $last30 = DB::table('eo_page_stats')
            ->where('event_id', $event->id)
            ->where('page', $page)
            ->where('stat_date', '>=', $from)
            ->selectRaw('COALESCE(SUM(views),0) as views, COALESCE(SUM(unique_views),0) as unique_views')
            ->first();

        return [
            'today' => [
                'views' => (int) ($todayRow->views ?? 0),
                'unique' => (int) ($todayRow->unique_views ?? 0),
            ],
            'last30' => [
                'views' => (int) ($last30->views ?? 0),
                'unique' => (int) ($last30->unique_views ?? 0),
            ],
        ];
    }

    private function duplicateName(string $name): string
    {
        $suffix = ' (Copy)';
        $max = 255;
        if (mb_strlen($name.$suffix) <= $max) {
            return $name.$suffix;
        }

        return mb_substr($name, 0, $max - mb_strlen($suffix)).$suffix;
    }

    private function uniqueEventSlug(string $sourceSlug): string
    {
        $base = $sourceSlug.'-copy';
        $base = Str::limit($base, 240, '');

        $slug = $base;
        $i = 1;
        while (Event::query()->where('slug', $slug)->exists()) {
            $suffix = '-'.$i;
            $slug = Str::limit($base, 240 - mb_strlen($suffix), '').$suffix;
            $i++;
        }

        return $slug;
    }

    /**
     * Process and save uploaded image
     */
    private function processImage($file, $folder = 'events', $maxWidth = 1920, $quality = 85)
    {
        $manager = new ImageManager(new Driver);

        // Generate unique filename
        $filename = uniqid().'_'.time().'.webp';
        $path = $folder.'/'.$filename;

        // Process image
        $image = $manager->read($file);

        // Resize if too large
        if ($image->width() > $maxWidth) {
            $image->scale(width: $maxWidth);
        }

        // Convert to WebP
        $webpImage = $image->toWebp($quality);

        // Ensure directory exists
        $directory = Storage::disk('public')->path($folder);
        if (! is_dir($directory)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        // Save image
        $fullPath = Storage::disk('public')->path($path);
        $webpImage->save($fullPath);

        return $path;
    }

    /**
     * Show blast email form
     */
    public function blast(Event $event)
    {
        $this->authorizeEvent($event);
        $event->load('categories');

        return view('eo.events.blast', compact('event'));
    }

    /**
     * Send blast email
     */
    public function sendBlast(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:race_categories,id',
        ]);

        $filters = [];
        if (! empty($validated['category_id'])) {
            $filters['category_id'] = $validated['category_id'];
        }

        // Dispatch job
        \App\Jobs\SendEventBlastEmail::dispatch($event, $validated['subject'], $validated['content'], $filters)
            ->onQueue('emails-blast');
        $msg = 'Email blast sedang diproses dalam antrian.';

        return redirect()->route('eo.events.blast', $event->id)
            ->with('success', $msg);
    }

    /**
     * Preview ticket email
     */
    public function previewEmail(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $event->custom_email_message = $this->normalizeCustomEmailMessage($request->input('custom_email_message'), $event);
        if ($request->has('ticket_email_use_qr')) {
            $event->ticket_email_use_qr = (bool) $request->boolean('ticket_email_use_qr');
        }
        if ($request->has('name')) {
            $event->name = $request->input('name');
        }

        // Mock Participant
        $participant = new \App\Models\Participant([
            'id' => 12345,
            'user_id' => auth()->id(),
            'event_id' => $event->id,
            'name' => auth()->user()->name ?? 'John Doe',
            'email' => auth()->user()->email ?? 'john@example.com',
            'bib_number' => '1001',
            'gender' => 'male',
        ]);

        // Mock Category
        $category = new \App\Models\RaceCategory([
            'name' => '10K Open',
            'distance_km' => 10,
        ]);
        $participant->setRelation('category', $category);
        $participant->transaction_id = 99999;

        // Mock Transaction
        $transaction = new \App\Models\Transaction([
            'id' => 99999,
            'final_amount' => 150000,
            'payment_status' => 'paid',
            'pic_data' => [
                'name' => 'Budi Santoso (PIC)',
                'email' => 'pic@example.com',
                'phone' => '081234567890',
            ],
        ]);

        return view('emails.events.registration-success', [
            'event' => $event,
            'participants' => collect([$participant]),
            'transaction' => $transaction,
            'notifiableName' => $participant->name,
        ]);
    }

    public function sendTestEmail(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $validated = $request->validate([
            'test_email' => 'required|email|max:255',
            'custom_email_message' => 'nullable|string',
            'ticket_email_use_qr' => 'nullable|boolean',
            'name' => 'nullable|string|max:255',
        ]);

        $rateKey = 'eo:test-email:'.$event->id;
        $count = (int) $request->session()->get($rateKey, 0);
        if ($count >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Batas kirim test email tercapai untuk sesi ini (maksimal 3 kali).',
                'remaining' => 0,
            ], 429);
        }

        $request->session()->put($rateKey, $count + 1);
        $remaining = max(0, 3 - ($count + 1));

        $event->custom_email_message = $this->normalizeCustomEmailMessage($validated['custom_email_message'] ?? null, $event);
        if ($request->has('ticket_email_use_qr')) {
            $event->ticket_email_use_qr = (bool) $request->boolean('ticket_email_use_qr');
        }
        if (! empty($validated['name'])) {
            $event->name = $validated['name'];
        }

        $authUser = $request->user();
        $participant = new \App\Models\Participant([
            'id' => 12345,
            'user_id' => $authUser?->id,
            'event_id' => $event->id,
            'name' => $authUser?->name ?? 'John Doe',
            'email' => $authUser?->email ?? 'john@example.com',
            'phone' => $authUser?->phone ?? '08123456789',
            'bib_number' => '1001',
            'gender' => 'male',
        ]);

        $category = new \App\Models\RaceCategory([
            'name' => '10K Open',
            'distance_km' => 10,
        ]);
        $participant->setRelation('category', $category);
        $participant->transaction_id = 99999;

        $transaction = new \App\Models\Transaction([
            'id' => 99999,
            'final_amount' => 150000,
            'payment_status' => 'paid',
            'pic_data' => [
                'name' => $authUser?->name ?? 'John Doe',
                'email' => $authUser?->email ?? 'john@example.com',
                'phone' => $authUser?->phone ?? '08123456789',
            ],
        ]);

        try {
            Mail::to($validated['test_email'])->send(
                new EventRegistrationSuccess($event, $transaction, collect([$participant]), $participant->name)
            );
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email. '.$e->getMessage(),
                'remaining' => $remaining,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Test email berhasil dikirim.',
            'remaining' => $remaining,
        ]);
    }

    /**
     * Delete image file
     */
    private function deleteImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function normalizeCustomEmailMessage(?string $html, Event $event): ?string
    {
        if (! is_string($html)) {
            return null;
        }

        $html = trim($html);
        if ($html === '') {
            return null;
        }

        if (! str_contains($html, 'data:image/')) {
            return $html;
        }

        $prev = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $images = $dom->getElementsByTagName('img');
        $imageNodes = [];
        foreach ($images as $img) {
            $imageNodes[] = $img;
        }

        foreach ($imageNodes as $img) {
            $src = (string) $img->getAttribute('src');
            if (! str_starts_with($src, 'data:image/')) {
                continue;
            }

            if (! preg_match('/^data:image\/([a-zA-Z0-9+]+);base64,(.+)$/', $src, $m)) {
                continue;
            }

            $ext = strtolower($m[1]);
            if ($ext === 'jpeg') {
                $ext = 'jpg';
            }
            if (! in_array($ext, ['jpg', 'png', 'gif', 'webp'], true)) {
                $ext = 'png';
            }

            $binary = base64_decode($m[2], true);
            if ($binary === false) {
                continue;
            }

            $path = 'email-custom/'.$event->id.'/'.Str::random(40).'.'.$ext;
            Storage::disk('public')->put($path, $binary);

            $img->setAttribute('src', asset('storage/'.$path));

            $style = trim((string) $img->getAttribute('style'));
            $extraStyle = 'max-width:100%;height:auto;';
            $img->setAttribute('style', ($style !== '' ? rtrim($style, ';').';' : '').$extraStyle);
        }

        return $dom->saveHTML();
    }

    public function remindPending(Request $request, Event $event, $transaction)
    {
        $this->authorizeEvent($event);

        $tx = Transaction::where('id', $transaction)
            ->where('event_id', $event->id)
            ->first();

        if (! $tx) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan untuk event ini.',
            ], 404);
        }

        if ($tx->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak dalam status pending.',
            ], 422);
        }

        SendPendingPaymentReminder::dispatch($tx);

        return response()->json([
            'success' => true,
            'message' => 'Reminder berhasil dijadwalkan.',
        ]);
    }

    public function bulkDelete(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'exists:participants,id'
        ]);

        // Ensure participants belong to this event
        $count = \App\Models\Participant::whereIn('id', $request->participant_ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Berhasil menghapus {$count} peserta."
        ]);
    }

    public function remindPendingBulk(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $query = Transaction::where('event_id', $event->id)
            ->where('payment_status', 'pending');

        if ($request->has('participant_ids') && is_array($request->participant_ids)) {
             $query->whereHas('participants', function($q) use ($request) {
                $q->whereIn('id', $request->participant_ids);
            });
        } else {
            $query->where('created_at', '<', now()->subDay());
        }

        $transactions = $query->where(function ($q) {
                $q->whereNull('pending_reminder_last_sent_at')
                  ->orWhere('pending_reminder_last_sent_at', '<', now()->subHours(24));
            })
            ->get();

        $count = 0;
        foreach ($transactions as $transaction) {
            SendPendingPaymentReminder::dispatch($transaction);
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menjadwalkan {$count} reminder pembayaran."
        ]);
    }

    public function sendCustomWaReminderBulk(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_ids' => 'required|array',
            'participant_ids.*' => 'integer|exists:participants,id',
            'message' => 'required|string|max:1000',
        ]);

        $participants = \App\Models\Participant::whereIn('id', $request->participant_ids)
            ->whereHas('transaction', function ($q) use ($event) {
                $q->where('event_id', $event->id);
            })
            ->get();

        $count = 0;
        foreach ($participants as $participant) {
            \App\Jobs\SendCustomWhatsAppJob::dispatch($participant->id, $request->message);
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil mengirim {$count} pesan reminder WhatsApp ke antrean."
        ]);
    }
}
