<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Event;
use App\Models\RaceDistance;
use App\Models\RaceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['city', 'raceType', 'raceDistances']);
        
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('location_name', 'like', "%{$s}%");
            });
        }

        $events = $query->latest('start_at')->paginate(10)->appends($request->only('search'));

        return view('admin.events.index', [
            'events' => $events,
            'search' => $request->input('search'),
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
            'location_name' => 'nullable|string|max:255',
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

        if (empty($validated['slug'])) {
            $validated['slug'] = $this->generateSlug($validated['name']);
        }
        
        $validated['user_id'] = 1; // Admin

        $event = Event::create($validated);

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
            'location_name' => 'nullable|string|max:255',
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

        $event->update($validated);

        if (isset($validated['race_distances'])) {
            $event->raceDistances()->sync($validated['race_distances']);
        } else {
            $event->raceDistances()->detach();
        }

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->raceDistances()->detach();
        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
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
