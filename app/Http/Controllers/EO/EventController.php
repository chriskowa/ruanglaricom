<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Actions\EO\StoreManualParticipantAction;
use App\Mail\EventRegistrationSuccess;
use App\Models\Event;
use App\Models\RaceCategory;
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
            ->with(['categories' => function($query) {
                $query->withCount(['participants as total_participants', 'participants as paid_participants' => function($q) {
                    $q->whereHas('transaction', function($t) {
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
            'map_embed_url' => 'nullable|string',
            'google_calendar_url' => 'nullable|url',
            'registration_open_at' => 'nullable|date',
            'registration_close_at' => 'nullable|date|after:registration_open_at',
            'promo_code' => 'nullable|string|max:50',
            'promo_buy_x' => 'nullable|integer|min:1',
            'custom_email_message' => 'nullable|string',
            'ticket_email_use_qr' => 'nullable|boolean',
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
            'jersey_sizes.*' => 'nullable|string|in:XS,S,M,L,XL,XXL',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required_with:addons|string|max:255',
            'addons.*.price' => 'nullable|numeric|min:0',
            'premium_amenities' => 'nullable|array',
            'template' => 'nullable|string|in:modern-dark,light-clean,simple-minimal,paolo-fest,paolo-fest-dark,professional-city-run',
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
                $validated['jersey_sizes'] = null;
            }
        } else {
            $validated['jersey_sizes'] = null;
        }

        // Process payment_config (Handle 'all' option)
        if (isset($validated['payment_config']['allowed_methods'])) {
            $methods = $validated['payment_config']['allowed_methods'];
            if (in_array('all', $methods)) {
                $validated['payment_config']['allowed_methods'] = ['midtrans', 'moota'];
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
            'map_embed_url' => 'nullable|string',
            'google_calendar_url' => 'nullable|url',
            'registration_open_at' => 'nullable|date',
            'registration_close_at' => 'nullable|date|after:registration_open_at',
            'promo_code' => 'nullable|string|max:50',
            'promo_buy_x' => 'nullable|integer|min:1',
            'custom_email_message' => 'nullable|string',
            'ticket_email_use_qr' => 'nullable|boolean',
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
            'jersey_sizes.*' => 'nullable|string|in:XS,S,M,L,XL,XXL',
            'addons' => 'nullable|array',
            'addons.*.name' => 'required_with:addons|string|max:255',
            'addons.*.price' => 'nullable|numeric|min:0',
            'premium_amenities' => 'nullable|array',
            'template' => 'nullable|string|in:modern-dark,light-clean,simple-minimal,professional-city-run,paolo-fest,paolo-fest-dark',
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
                $validated['jersey_sizes'] = null;
            }
        } else {
            $validated['jersey_sizes'] = null;
        }

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
            if (in_array('all', $methods)) {
                $validated['payment_config']['allowed_methods'] = ['midtrans', 'moota'];
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

        DB::transaction(function () use ($event, $validated, $categories, &$affectedCategoryIds, &$deletedCategoryIds) {
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

        $query = \App\Models\Participant::whereHas('transaction', function ($q) use ($event) {
            $q->where('event_id', $event->id);
        })
            ->with(['transaction', 'category']);

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

        // Search filter
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

        $participants = $query->orderBy('created_at', 'desc')->paginate(20);

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
                ];
                $eventReport = $this->reportService->getEventReport($event, $reportFilters);
                return response()->json([
                    'success' => true,
                    'report' => $eventReport
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
                    'date_of_birth' => $p->date_of_birth,
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

        $reportLink = URL::signedRoute('report.show', ['event' => $event->id]);

        return view('eo.events.participants', compact('event', 'participants', 'financials', 'eventReport', 'reportLink'));
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
                'date_of_birth' => $p->date_of_birth,
                'age_group' => $p->getAgeGroup($event->start_at),
                'jersey_size' => $p->jersey_size,
                'created_at' => $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null,
                'payment_status' => $p->transaction->payment_status ?? 'pending',
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
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|min:10|max:15|regex:/^[0-9]+$/',
            'send_whatsapp' => 'nullable|boolean',
            'use_queue' => 'nullable|boolean',
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

        if (!$belongsToEvent) {
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
            'bib_number' => 'nullable|string|max:20',
            'jersey_size' => 'nullable|string|max:10',
            'is_picked_up' => 'nullable|boolean',
        ]);

        // If is_picked_up is toggled, handle timestamp
        if (isset($validated['is_picked_up'])) {
            $validated['is_picked_up'] = (bool) $validated['is_picked_up'];
            if ($validated['is_picked_up'] && !$participant->is_picked_up) {
                $validated['picked_up_at'] = now();
                $validated['picked_up_by'] = auth()->user()->name ?? 'Admin';
            } elseif (!$validated['is_picked_up'] && $participant->is_picked_up) {
                $validated['picked_up_at'] = null;
                $validated['picked_up_by'] = null;
            }
        }

        $participant->update($validated);

        // Refresh to get relationship data if needed (e.g. category name)
        $participant->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Data peserta berhasil diperbarui.',
            'data' => [
                'id' => $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'gender' => $participant->gender,
                'date_of_birth' => $participant->date_of_birth,
                'address' => $participant->address,
                'city' => $participant->city,
                'province' => $participant->province,
                'postal_code' => $participant->postal_code,
                'race_category_id' => $participant->race_category_id,
                'category_name' => $participant->category->name ?? '-',
                'bib_number' => $participant->bib_number,
                'jersey_size' => $participant->jersey_size,
                'age_group' => $participant->getAgeGroup($event->start_at),
                'is_picked_up' => $participant->is_picked_up,
                'picked_up_at' => $participant->picked_up_at,
                'picked_up_by' => $participant->picked_up_by,
            ]
        ]);
    }

    /**
     * Resend event registration email to a specific participant
     */
    public function resendEmail(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'participant_id' => 'required|exists:participants,id'
        ]);

        $participant = \App\Models\Participant::with('transaction')->find($request->participant_id);

        $participantEventId = (int) ($participant?->transaction?->event_id ?? 0);
        if (! $participant || $participantEventId !== (int) $event->id) {
            return response()->json([
                'success' => false, 
                'message' => 'Participant not found or does not belong to this event.'
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
                'message' => 'Email sent successfully to ' . $participant->email
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Resend Email Error: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
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

        $filename = 'participants_'.$event->slug.'_'.date('Y-m-d').'.xlsx';

        $queryForStream = clone $query;

        return response()->streamDownload(function () use ($queryForStream) {
            $writer = new \OpenSpout\Writer\XLSX\Writer();
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

    /**
     * Update participant picked up status
     */
    public function updateParticipantStatus(Request $request, Event $event, \App\Models\Participant $participant)
    {
        $this->authorizeEvent($event);

        // Verify participant belongs to this event
        if ($participant->transaction->event_id !== $event->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'is_picked_up' => 'required|boolean',
            'picked_up_by' => 'nullable|string|max:255',
        ]);

        $participant->update([
            'is_picked_up' => $validated['is_picked_up'],
            'picked_up_at' => $validated['is_picked_up'] ? now() : null,
            'picked_up_by' => $validated['is_picked_up'] ? ($validated['picked_up_by'] ?? null) : null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status pengambilan berhasil diperbarui',
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
            \App\Jobs\ProcessPaidEventTransaction::dispatch($transaction);
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
        if (!empty($validated['category_id'])) {
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
        if ($request->has('name')) $event->name = $request->input('name');
        
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
}
