<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\RaceDistance;
use App\Models\RaceType;
use App\Models\RunningEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RunningEventController extends Controller
{
    public function index()
    {
        $events = RunningEvent::with('city', 'raceType', 'raceDistances')->latest()->paginate(10);
        return view('admin.events.index', compact('events'));
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
            'banner_image' => 'nullable|string', // URL or Path
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

        // Handle Banner Image Upload if file provided instead of URL
        // In this specific flow, user might paste URL or select from media library which returns URL
        // So we keep it as string path/url.
        
        $event = RunningEvent::create($validated);

        if (!empty($validated['race_distances'])) {
            $event->raceDistances()->sync($validated['race_distances']);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function edit(RunningEvent $event)
    {
        $cities = City::orderBy('name')->get();
        $raceTypes = RaceType::all();
        $raceDistances = RaceDistance::all();
        $selectedDistances = $event->raceDistances->pluck('id')->toArray();
        
        return view('admin.events.edit', compact('event', 'cities', 'raceTypes', 'raceDistances', 'selectedDistances'));
    }

    public function update(Request $request, RunningEvent $event)
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

        $event->update($validated);

        if (isset($validated['race_distances'])) {
            $event->raceDistances()->sync($validated['race_distances']);
        } else {
            $event->raceDistances()->detach();
        }

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(RunningEvent $event)
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
                // Expected format:
                // Name, Date (YYYY-MM-DD), Start Time (HH:MM), Location, City, Race Type, Distances (comma sep), Reg Link, Organizer
                
                if (count($data) < 2) continue;

                $name = $data[0] ?? null;
                $date = $data[1] ?? null;
                if (!$name || !$date) continue;

                $city = null;
                if (!empty($data[4])) {
                    $city = City::where('name', 'like', trim($data[4]))->first();
                }

                $raceType = null;
                if (!empty($data[5])) {
                    $raceType = RaceType::where('name', 'like', trim($data[5]))->first();
                }

                $event = RunningEvent::create([
                    'name' => $name,
                    'event_date' => $date,
                    'start_time' => !empty($data[2]) ? $data[2] : null,
                    'location_name' => $data[3] ?? null,
                    'city_id' => $city ? $city->id : null,
                    'race_type_id' => $raceType ? $raceType->id : null,
                    'registration_link' => $data[7] ?? null,
                    'organizer_name' => $data[8] ?? null,
                    'status' => 'published', // Default to published
                    'description' => 'Imported via CSV',
                ]);

                // Handle Distances
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
}
