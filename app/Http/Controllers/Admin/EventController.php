<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Event;
use App\Models\EventAudit;
use App\Models\RaceDistance;
use App\Models\RaceType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['city', 'raceType', 'raceDistances', 'user']);
        
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('location_name', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', (bool) ((int) $request->input('featured')));
        }

        if ($request->filled('active')) {
            $query->where('is_active', (bool) ((int) $request->input('active')));
        }

        if ($request->filled('eo_id')) {
            $query->where('user_id', (int) $request->input('eo_id'));
        }

        $sort = $request->input('sort', 'created_at_desc');
        switch ($sort) {
            case 'created_at_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'start_at_desc':
                $query->orderBy('start_at', 'desc');
                break;
            case 'start_at_asc':
                $query->orderBy('start_at', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $events = $query->paginate(10)->appends($request->only('search', 'sort', 'status', 'featured', 'active', 'eo_id'));

        if ($request->ajax()) {
            return view('admin.events.partials.table', compact('events'))->render();
        }

        $eventOrganizers = User::query()
            ->where('role', 'eo')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.events.index', [
            'events' => $events,
            'search' => $request->input('search'),
            'sort' => $sort,
            'status' => $request->input('status'),
            'featured' => $request->input('featured'),
            'active' => $request->input('active'),
            'eoId' => $request->input('eo_id'),
            'eventOrganizers' => $eventOrganizers,
        ]);
    }

    public function create()
    {
        $cities = City::orderBy('name')->get();
        $raceTypes = RaceType::all();
        $raceDistances = RaceDistance::all();
        
        return view('admin.events.create', compact('cities', 'raceTypes', 'raceDistances'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'banner_image' => 'nullable|string',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'city_id' => 'nullable|exists:cities,id',
            'location_name' => 'required|string|max:255',
            'race_type_id' => 'nullable|exists:race_types,id',
            'race_distances' => 'nullable|array',
            'race_distances.*' => 'exists:race_distances,id',
            'registration_link' => 'nullable|url',
            'social_media_link' => 'nullable|url',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_contact' => 'nullable|string|max:255',
            'contributor_contact' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ]);

        $startAt = Carbon::parse($validated['event_date']);
        if (!empty($validated['start_time'])) {
            $time = Carbon::createFromFormat('H:i', $validated['start_time']);
            $startAt->setTime($time->hour, $time->minute);
        }
        $validated['start_at'] = $startAt;

        $validated['slug'] = $this->generateSlug($validated['name']);
        
        $validated['user_id'] = 1; // Admin

        $event = Event::create([
            'user_id' => auth()->id() ?? 1,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'short_description' => $validated['description'] ?? null,
            'full_description' => $validated['description'] ?? null,
            'start_at' => $validated['start_at'],
            'city_id' => $validated['city_id'] ?? null,
            'location_name' => $validated['location_name'] ?? null,
            'race_type_id' => $validated['race_type_id'] ?? null,
            'hero_image_url' => $validated['banner_image'] ?? null,
            'external_registration_link' => $validated['registration_link'] ?? null,
            'social_media_link' => $validated['social_media_link'] ?? null,
            'organizer_name' => $validated['organizer_name'] ?? null,
            'organizer_contact' => $validated['organizer_contact'] ?? null,
            'contributor_contact' => $validated['contributor_contact'] ?? null,
            'status' => $validated['status'],
            'is_active' => true,
        ]);

        if (!empty($validated['race_distances'])) {
            $event->raceDistances()->sync($validated['race_distances']);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(Event $event)
    {
        $cities = City::orderBy('name')->get();
        $raceTypes = RaceType::all();
        $raceDistances = RaceDistance::all();
        $selectedDistances = $event->raceDistances->pluck('id')->toArray();
        
        return view('admin.events.edit', compact('event', 'cities', 'raceTypes', 'raceDistances', 'selectedDistances'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'banner_image' => 'nullable|string',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'city_id' => 'nullable|exists:cities,id',
            'location_name' => 'required|string|max:255',
            'race_type_id' => 'nullable|exists:race_types,id',
            'race_distances' => 'nullable|array',
            'race_distances.*' => 'exists:race_distances,id',
            'registration_link' => 'nullable|url',
            'social_media_link' => 'nullable|url',
            'organizer_name' => 'nullable|string|max:255',
            'organizer_contact' => 'nullable|string|max:255',
            'contributor_contact' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ]);

        $startAt = Carbon::parse($validated['event_date']);
        if (!empty($validated['start_time'])) {
            $time = Carbon::createFromFormat('H:i', $validated['start_time']);
            $startAt->setTime($time->hour, $time->minute);
        }
        $validated['start_at'] = $startAt;

        $event->update([
            'name' => $validated['name'],
            'short_description' => $validated['description'] ?? null,
            'full_description' => $validated['description'] ?? null,
            'start_at' => $validated['start_at'],
            'city_id' => $validated['city_id'] ?? null,
            'location_name' => $validated['location_name'] ?? null,
            'race_type_id' => $validated['race_type_id'] ?? null,
            'hero_image_url' => $validated['banner_image'] ?? null,
            'external_registration_link' => $validated['registration_link'] ?? null,
            'social_media_link' => $validated['social_media_link'] ?? null,
            'organizer_name' => $validated['organizer_name'] ?? null,
            'organizer_contact' => $validated['organizer_contact'] ?? null,
            'contributor_contact' => $validated['contributor_contact'] ?? null,
            'status' => $validated['status'],
        ]);

        if (isset($validated['race_distances'])) {
            $event->raceDistances()->sync($validated['race_distances']);
        } else {
            $event->raceDistances()->detach();
        }

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Request $request, Event $event)
    {
        $before = [
            'event_id' => $event->id,
            'name' => $event->name,
            'status' => $event->status,
            'is_featured' => $event->is_featured,
            'is_active' => $event->is_active,
        ];

        $this->writeAudit(request(), $event, 'delete', $before, null);

        $event->raceDistances()->detach();
        $event->delete();

        if ($request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Event berhasil dihapus.',
            ]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }

    public function toggleFeatured(Request $request, Event $event)
    {
        $expectedLockVersion = $request->integer('lock_version');

        return DB::transaction(function () use ($request, $event, $expectedLockVersion) {
            $locked = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($expectedLockVersion !== null && (int) $locked->lock_version !== (int) $expectedLockVersion) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Data event sudah berubah. Silakan refresh.',
                ], 409);
            }

            $before = [
                'is_featured' => (bool) $locked->is_featured,
            ];

            $locked->is_featured = ! $locked->is_featured;
            $locked->lock_version = ((int) $locked->lock_version) + 1;
            $locked->save();

            $after = [
                'is_featured' => (bool) $locked->is_featured,
            ];

            $this->writeAudit($request, $locked, 'toggle_featured', $before, $after);

            return response()->json([
                'ok' => true,
                'message' => $locked->is_featured ? 'Event ditandai sebagai featured.' : 'Event dihapus dari featured.',
                'is_featured' => (bool) $locked->is_featured,
                'lock_version' => (int) $locked->lock_version,
            ]);
        });
    }

    public function toggleActive(Request $request, Event $event)
    {
        $expectedLockVersion = $request->integer('lock_version');

        return DB::transaction(function () use ($request, $event, $expectedLockVersion) {
            $locked = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($expectedLockVersion !== null && (int) $locked->lock_version !== (int) $expectedLockVersion) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Data event sudah berubah. Silakan refresh.',
                ], 409);
            }

            $before = [
                'is_active' => (bool) $locked->is_active,
            ];

            $locked->is_active = ! $locked->is_active;
            $locked->lock_version = ((int) $locked->lock_version) + 1;
            $locked->save();

            $after = [
                'is_active' => (bool) $locked->is_active,
            ];

            $this->writeAudit($request, $locked, 'toggle_active', $before, $after);

            return response()->json([
                'ok' => true,
                'message' => $locked->is_active ? 'Event diaktifkan.' : 'Event dinonaktifkan.',
                'is_active' => (bool) $locked->is_active,
                'lock_version' => (int) $locked->lock_version,
            ]);
        });
    }

    public function setStatus(Request $request, Event $event)
    {
        $data = $request->validate([
            'status' => 'required|in:draft,published',
            'lock_version' => 'nullable|integer',
        ]);

        $expectedLockVersion = array_key_exists('lock_version', $data) ? (int) $data['lock_version'] : null;

        return DB::transaction(function () use ($request, $event, $data, $expectedLockVersion) {
            $locked = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();

            if ($expectedLockVersion !== null && (int) $locked->lock_version !== (int) $expectedLockVersion) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Data event sudah berubah. Silakan refresh.',
                ], 409);
            }

            $before = [
                'status' => (string) $locked->status,
            ];

            $locked->status = $data['status'];
            $locked->lock_version = ((int) $locked->lock_version) + 1;
            $locked->save();

            $after = [
                'status' => (string) $locked->status,
            ];

            $this->writeAudit($request, $locked, 'set_status', $before, $after);

            return response()->json([
                'ok' => true,
                'message' => 'Status publish diperbarui.',
                'status' => (string) $locked->status,
                'lock_version' => (int) $locked->lock_version,
            ]);
        });
    }

    protected function writeAudit(Request $request, ?Event $event, string $action, ?array $before, ?array $after): void
    {
        $adminId = auth()->id();
        if (! $adminId) {
            return;
        }

        EventAudit::create([
            'event_id' => $event?->id,
            'admin_id' => $adminId,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }

    public function import()
    {
        return view('admin.events.import');
    }

    public function storeImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        
        // Skip header
        fgetcsv($handle);

        $row = 0;
        $success = 0;
        $errors = [];

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row++;
            try {
                if (count($data) < 2) continue;

                $name = $data[0] ?? null;
                $dateStr = $data[1] ?? null;
                if (!$name || !$dateStr) continue;

                $city = null;
                if (!empty($data[4])) {
                    $city = City::where('name', 'like', trim($data[4]))->first();
                }

                $raceType = null;
                if (!empty($data[5])) {
                    $raceType = RaceType::where('name', 'like', trim($data[5]))->first();
                }

                $startAt = Carbon::parse($dateStr);
                if (!empty($data[2])) {
                    $time = Carbon::createFromFormat('H:i', $data[2]);
                    $startAt->setTime($time->hour, $time->minute);
                }

                $event = Event::create([
                    'name' => $name,
                    'start_at' => $startAt,
                    'location_name' => $data[3] ?? null,
                    'city_id' => $city ? $city->id : null,
                    'race_type_id' => $raceType ? $raceType->id : null,
                    'registration_link' => $data[7] ?? null,
                    'organizer_name' => $data[8] ?? null,
                    'status' => 'published',
                    'description' => 'Imported via CSV',
                    'user_id' => 1,
                    'slug' => $this->generateSlug($name),
                ]);

                if (!empty($data[6])) {
                    $distanceNames = explode(',', $data[6]);
                    $distanceIds = [];
                    foreach ($distanceNames as $distName) {
                        $distName = trim($distName);
                        $dist = RaceDistance::where('name', 'like', $distName)->first();
                        if ($dist) {
                            $distanceIds[] = $dist->id;
                        }
                    }
                    if (!empty($distanceIds)) {
                        $event->raceDistances()->sync($distanceIds);
                    }
                }

                $success++;

            } catch (\Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
            }
        }
        
        fclose($handle);

        $message = "Imported {$success} events.";
        if (count($errors) > 0) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
        }

        return redirect()->route('admin.events.index')->with('success', $message);
    }

    public function sync()
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders(['ruangLariKey' => 'Thinkpadx390'])
                ->get('https://ruanglari.com/wp-json/ruanglari/v1/events');

            if (!$response->successful()) {
                return back()->with('error', 'Failed to fetch events from source.');
            }

            $events = $response->json();
            $count = 0;
            $updated = 0;

            foreach ($events as $item) {
                try {
                    $date = Carbon::createFromFormat('m/d/Y', $item['date']);
                } catch (\Exception $e) {
                    continue;
                }

                $name = html_entity_decode($item['title']);
                
                $existing = Event::where('name', $name)
                    ->whereDate('start_at', $date->toDateString())
                    ->first();

                if ($existing) {
                    if (empty($existing->registration_link) && !empty($item['link'])) {
                        $existing->update(['registration_link' => $item['link']]);
                        $updated++;
                    }
                    continue;
                }

                $city = null;
                if (!empty($item['location'])) {
                    $location = trim($item['location']);
                    $city = City::where('name', 'like', "%{$location}%")
                        ->orWhere('name', 'like', "Kota {$location}%")
                        ->orWhere('name', 'like', "Kabupaten {$location}%")
                        ->first();
                }

                Event::create([
                    'name' => $name,
                    'slug' => $this->generateSlug($name),
                    'start_at' => $date, // Time defaults to 00:00:00
                    'location_name' => $item['location'],
                    'city_id' => $city ? $city->id : null,
                    'registration_link' => $item['link'],
                    'status' => 'published',
                    'description' => 'Imported from RuangLari.com',
                    'user_id' => 1,
                ]);
                $count++;
            }

            return redirect()->route('admin.events.index')
                ->with('success', "Synced successfully. Added {$count} new events, updated {$updated} events.");

        } catch (\Exception $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    private function generateSlug($name)
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        return $slug;
    }
}
