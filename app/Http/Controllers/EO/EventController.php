<?php

namespace App\Http\Controllers\EO;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class EventController extends Controller
{
    protected $cacheService;

    public function __construct(EventCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index()
    {
        $events = Event::where('user_id', auth()->id())
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
            'is_instant_notification' => 'nullable|boolean',
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
            'categories.*.reg_start_at' => 'nullable|date',
            'categories.*.reg_end_at' => 'nullable|date|after:categories.*.reg_start_at',
            'categories.*.is_active' => 'nullable|boolean',
            'categories.*.prizes' => 'nullable|array',
            'categories.*.prizes.1' => 'nullable|string|max:255',
            'categories.*.prizes.2' => 'nullable|string|max:255',
            'categories.*.prizes.3' => 'nullable|string|max:255',
            'payment_config' => 'nullable|array',
            'whatsapp_config' => 'nullable|array',
            'whatsapp_config.enabled' => 'nullable|boolean',
            'whatsapp_config.template' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_instant_notification'] = isset($validated['is_instant_notification']) ? (bool) $validated['is_instant_notification'] : false;

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

        // Single images are now paths from Dropzone
        if (isset($validated['hero_image'])) {
            $validated['hero_image_url'] = null;
        }

        // Create event
        $event = Event::create($validated);

        // Create categories
        foreach ($categories as $categoryData) {
            $categoryData['event_id'] = $event->id;
            $categoryData['is_active'] = isset($categoryData['is_active']) ? (bool) $categoryData['is_active'] : true;
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
            'is_instant_notification' => 'nullable|boolean',
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
            'categories.*.reg_start_at' => 'nullable|date',
            'categories.*.reg_end_at' => 'nullable|date|after:categories.*.reg_start_at',
            'categories.*.is_active' => 'nullable|boolean',
            'categories.*.prizes' => 'nullable|array',
            'categories.*.prizes.1' => 'nullable|string|max:255',
            'categories.*.prizes.2' => 'nullable|string|max:255',
            'categories.*.prizes.3' => 'nullable|string|max:255',
            'payment_config' => 'nullable|array',
            'whatsapp_config' => 'nullable|array',
            'whatsapp_config.enabled' => 'nullable|boolean',
            'whatsapp_config.template' => 'nullable|string',
        ]);

        $validated['is_instant_notification'] = isset($validated['is_instant_notification']) ? (bool) $validated['is_instant_notification'] : false;

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

                // Delete removed categories
                if (! empty($submittedCategoryIds)) {
                    $categoriesToDelete = array_diff($existingCategoryIds, $submittedCategoryIds);
                    if (! empty($categoriesToDelete)) {
                        \App\Models\RaceCategory::whereIn('id', $categoriesToDelete)->delete();
                        $deletedCategoryIds = array_merge($deletedCategoryIds, $categoriesToDelete);
                    }
                }

                // Update or create categories
                foreach ($categories as $categoryData) {
                    $categoryId = $categoryData['id'] ?? null;
                    unset($categoryData['id']);

                    if (isset($categoryData['prizes']) && ! isset($raceCategoryColumnMap['prizes'])) {
                        unset($categoryData['prizes']);
                    }

                    $categoryData = array_intersect_key($categoryData, $raceCategoryColumnMap);

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

        // Search filter
        if (request()->has('search') && trim(request()->search) !== '') {
            $search = trim(request()->search);
            $query->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $participants = $query->orderBy('created_at', 'desc')->paginate(20);

        if (request()->ajax() || request()->wantsJson()) {
            $items = $participants->getCollection()->map(function ($p) use ($event) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'gender' => $p->gender,
                    'email' => $p->email,
                    'phone' => $p->phone,
                    'category' => $p->category ? $p->category->name : '-',
                    'bib_number' => $p->bib_number,
                    'jersey_size' => $p->jersey_size,
                    'created_at' => $p->created_at ? $p->created_at->format('d M Y') : '',
                    'is_picked_up' => (bool) $p->is_picked_up,
                    'picked_up_by' => $p->picked_up_by,
                    'transaction_id' => optional($p->transaction)->id,
                    'payment_status' => optional($p->transaction)->payment_status ?? 'pending',
                    'payment_update_url' => route('eo.events.transactions.payment-status', [$event, optional($p->transaction)->id]),
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

        return view('eo.events.participants', compact('event', 'participants'));
    }

    /**
     * Export participants as CSV
     */
    public function exportParticipants(Event $event)
    {
        $this->authorizeEvent($event);

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

        $participants = $query->orderBy('created_at', 'desc')->get();

        $filename = 'participants_'.$event->slug.'_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($participants) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header
            fputcsv($file, [
                'No',
                'Nama',
                'Gender',
                'Email',
                'Phone',
                'ID Card',
                'Kategori',
                'BIB Number',
                'Jersey Size',
                'Target Time',
                'Status Pembayaran',
                'Status Pengambilan',
                'Tanggal Registrasi',
                'Tanggal Pengambilan',
                'Diambil Oleh (PIC)',
            ]);

            // Data
            foreach ($participants as $index => $participant) {
                fputcsv($file, [
                    $index + 1,
                    $participant->name,
                    ucfirst($participant->gender ?? '-'),
                    $participant->email,
                    $participant->phone,
                    $participant->id_card,
                    $participant->category ? $participant->category->name : '-',
                    $participant->bib_number ?? '-',
                    $participant->jersey_size ?? '-',
                    $participant->target_time ? $participant->target_time->format('H:i:s') : '-',
                    ucfirst($participant->transaction->payment_status ?? 'pending'),
                    $participant->is_picked_up ? 'Sudah Diambil' : 'Belum Diambil',
                    $participant->created_at->format('Y-m-d H:i:s'),
                    $participant->picked_up_at ? $participant->picked_up_at->format('Y-m-d H:i:s') : '-',
                    $participant->picked_up_by ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

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
        if ($user && ($user->role === 'admin' || $user->role === 'eo')) {
            return;
        }
        if ($event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
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
        if ($event->is_instant_notification) {
            \App\Jobs\SendEventBlastEmail::dispatchSync($event, $validated['subject'], $validated['content'], $filters);
            $msg = 'Email blast berhasil dikirim (Instant)!';
        } else {
            \App\Jobs\SendEventBlastEmail::dispatch($event, $validated['subject'], $validated['content'], $filters);
            $msg = 'Email blast sedang diproses dalam antrian.';
        }

        return redirect()->route('eo.events.blast', $event->id)
            ->with('success', $msg);
    }

    /**
     * Preview ticket email
     */
    public function previewEmail(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        // Update event with preview data (not saved to DB)
        $event->custom_email_message = $request->input('custom_email_message');
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
        ]);

        return view('emails.events.registration-success', [
            'event' => $event,
            'participants' => collect([$participant]),
            'transaction' => $transaction,
            'notifiableName' => $participant->name,
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
}
