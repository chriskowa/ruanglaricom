<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\MasterWorkout;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::where('coach_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('coach.programs.index', compact('programs'));
    }

    public function create()
    {
        $cities = City::orderBy('name')->get();
        $masterWorkouts = MasterWorkout::visibleFor(auth()->user())->get()->groupBy('type');

        return view('coach.programs.create', compact('cities', 'masterWorkouts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'distance_target' => 'required|in:5k,10k,21k,42k,fm',
            'target_time' => 'nullable|date_format:H:i:s',
            'price' => 'required|numeric|min:0',
            'city_id' => 'nullable|exists:cities,id',
            'program_json' => 'required|json',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'duration_weeks' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean',
            'is_challenge' => 'nullable|boolean',
        ]);

        $validated['coach_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']).'-'.uniqid();
        $validated['program_json'] = json_decode($validated['program_json'], true);
        $validated['is_published'] = $request->has('is_published') ? (bool) $request->is_published : false;
        $validated['is_challenge'] = $request->boolean('is_challenge');

        // Handle file uploads
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('programs/thumbnails', 'public');
        }
        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('programs/banners', 'public');
        }

        Program::create($validated);

        return redirect()->route('coach.programs.index')
            ->with('success', 'Program berhasil dibuat!');
    }

    public function show(Program $program)
    {
        if ((int) $program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        return view('coach.programs.show', compact('program'));
    }

    public function edit(Program $program)
    {
        if ((int) $program->coach_id !== (int) auth()->id()) {
            abort(403);
        }
        $cities = City::orderBy('name')->get();
        $masterWorkouts = MasterWorkout::all()->groupBy('type');

        return view('coach.programs.edit', compact('program', 'cities', 'masterWorkouts'));
    }

    public function update(Request $request, Program $program)
    {
        if ((int) $program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'distance_target' => 'required|in:5k,10k,21k,42k,fm',
            'target_time' => 'nullable|date_format:H:i:s',
            'price' => 'required|numeric|min:0',
            'city_id' => 'nullable|exists:cities,id',
            'program_json' => 'required|json',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'duration_weeks' => 'nullable|integer|min:1',
            'is_published' => 'nullable|boolean',
            'is_challenge' => 'nullable|boolean',
        ]);

        if ($validated['title'] !== $program->title) {
            $validated['slug'] = Str::slug($validated['title']).'-'.uniqid();
        }

        $validated['program_json'] = json_decode($validated['program_json'], true);
        $validated['is_published'] = $request->has('is_published') ? (bool) $request->is_published : $program->is_published;
        $validated['is_challenge'] = $request->boolean('is_challenge');

        // Handle file uploads
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($program->thumbnail) {
                Storage::disk('public')->delete($program->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('programs/thumbnails', 'public');
        }
        if ($request->hasFile('banner')) {
            // Delete old banner if exists
            if ($program->banner) {
                Storage::disk('public')->delete($program->banner);
            }
            $validated['banner'] = $request->file('banner')->store('programs/banners', 'public');
        }

        $program->update($validated);

        return redirect()->route('coach.programs.index')
            ->with('success', 'Program berhasil diperbarui!');
    }

    public function destroy(Program $program)
    {
        if ((int) $program->coach_id !== (int) auth()->id()) {
            abort(403);
        }
        $program->delete();

        return redirect()->route('coach.programs.index')
            ->with('success', 'Program berhasil dihapus!');
    }

    /**
     * Generate program using VDOT method (placeholder - implementasi nanti)
     */
    public function generateVDOT(Request $request)
    {
        $validated = $request->validate([
            'current_5k_time' => 'required|date_format:H:i:s',
            'target_5k_time' => 'required|date_format:H:i:s',
            'duration_weeks' => 'required|integer|min:1|max:52',
        ]);

        // TODO: Implement VDOT calculation
        // Untuk sekarang return template
        return $this->generateTemplate($request);
    }

    /**
     * Publish program
     */
    public function publish(Program $program)
    {
        if ((int) $program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $program->update(['is_published' => true]);

        return back()->with('success', 'Program berhasil dipublikasikan!');
    }

    /**
     * Unpublish program
     */
    public function unpublish(Program $program)
    {
        if ((int) $program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $program->update(['is_published' => false]);

        return back()->with('success', 'Program berhasil diunpublish!');
    }

    /**
     * Generate program JSON template
     */
    public function generateTemplate(Request $request)
    {
        $validated = $request->validate([
            'duration_weeks' => 'required|integer|min:1|max:52',
        ]);

        $sessions = [];
        $totalDays = $validated['duration_weeks'] * 7;

        // Generate more realistic weekly pattern
        // Pattern per 7-day block:
        // 1: Easy Run, 2: Intervals, 3: Easy Run, 4: Tempo (or Time Trial on w4, w8, w12), 5: Strength, 6: Long Run, 7: Rest
        for ($day = 1; $day <= $totalDays; $day++) {
            $weekDay = (($day - 1) % 7) + 1;
            $advancedConfig = [];

            switch ($weekDay) {
                case 1:
                case 3:
                    $type = 'easy_run';
                    $distance = 5;
                    $duration = '00:35:00';
                    $description = 'Easy run';
                    $advancedConfig = [
                        'type' => 'easy_run',
                        'title' => 'Easy Run 5K',
                        'description' => 'Easy run',
                        'intensity' => 'low',
                        'warmup' => ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''],
                        'cooldown' => ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''],
                        'main' => ['by' => 'distance', 'distance' => 5, 'unit' => 'km', 'duration' => '', 'pace' => '']
                    ];
                    break;
                case 2:
                    $type = 'interval';
                    $distance = 6;
                    $duration = '00:45:00';
                    $description = 'Interval session';
                    $advancedConfig = [
                        'type' => 'interval',
                        'title' => 'Interval Session',
                        'description' => 'Interval session',
                        'intensity' => 'high',
                        'warmup' => ['enabled' => true, 'by' => 'time', 'duration' => '00:10:00'],
                        'cooldown' => ['enabled' => true, 'by' => 'time', 'duration' => '00:10:00'],
                        'interval' => ['reps' => 6, 'by' => 'distance', 'repDistance' => 0.8, 'repDistanceUnit' => 'km', 'repTime' => '', 'pace' => '', 'recovery' => 'Jog 2:00']
                    ];
                    break;
                case 4:
                    $weekNum = ceil($day / 7);
                    if ($weekNum % 4 === 0) {
                        $type = 'time_trial';
                        $distance = 5;
                        $duration = '00:20:00';
                        $description = 'Time Trial 5K Max Effort';
                        $advancedConfig = [
                            'type' => 'time_trial',
                            'title' => 'Time Trial 5K',
                            'description' => 'Max effort 5km time trial to test current fitness.',
                            'intensity' => 'high',
                            'warmup' => ['enabled' => true, 'by' => 'time', 'duration' => '00:10:00'],
                            'cooldown' => ['enabled' => true, 'by' => 'time', 'duration' => '00:10:00'],
                            'timeTrial' => ['by' => 'distance', 'distance' => 5, 'unit' => 'km', 'duration' => '', 'pace' => '', 'effort' => 'max_effort']
                        ];
                    } else {
                        $type = 'tempo';
                        $distance = 8;
                        $duration = '00:45:00';
                        $description = 'Tempo run';
                        $advancedConfig = [
                            'type' => 'tempo',
                            'title' => 'Tempo Run 8K',
                            'description' => 'Tempo run',
                            'intensity' => 'medium',
                            'warmup' => ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''],
                            'cooldown' => ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''],
                            'tempo' => ['by' => 'distance', 'distance' => 8, 'unit' => 'km', 'duration' => '', 'pace' => '', 'effort' => 'moderate']
                        ];
                    }
                    break;
                case 5:
                    $type = 'strength';
                    $distance = null;
                    $duration = '00:40:00';
                    $description = 'Strength & mobility';
                    $advancedConfig = [
                        'type' => 'strength',
                        'title' => 'Strength & Mobility',
                        'description' => 'Strength & mobility',
                        'intensity' => 'medium',
                        'strength' => [
                            'category' => 'core',
                            'plan' => [
                                ['name' => 'Plank', 'sets' => '3', 'reps' => '45-60s', 'equipment' => 'Bodyweight'],
                                ['name' => 'Russian Twist', 'sets' => '3', 'reps' => '20', 'equipment' => 'Bodyweight']
                            ]
                        ]
                    ];
                    break;
                case 6:
                    $type = 'long_run';
                    $distance = 12;
                    $duration = '01:30:00';
                    $description = 'Long run';
                    $advancedConfig = [
                        'type' => 'long_run',
                        'title' => 'Long Run 12K',
                        'description' => 'Long run',
                        'intensity' => 'medium',
                        'warmup' => ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''],
                        'cooldown' => ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''],
                        'main' => ['by' => 'distance', 'distance' => 12, 'unit' => 'km', 'duration' => '', 'pace' => ''],
                        'longRun' => ['fastFinish' => ['enabled' => false, 'distance' => 0, 'unit' => 'km', 'pace' => '']]
                    ];
                    break;
                default:
                    $type = 'rest';
                    $distance = null;
                    $duration = null;
                    $description = 'Rest day';
                    $advancedConfig = [
                        'type' => 'rest',
                        'title' => 'Rest Day',
                        'description' => 'Rest day',
                        'intensity' => 'low'
                    ];
                    break;
            }

            $sessions[] = [
                'day' => $day,
                'type' => $type,
                'distance' => $distance,
                'duration' => $duration,
                'description' => $description,
                'advanced_config' => json_encode($advancedConfig)
            ];
        }

        return response()->json([
            'sessions' => $sessions,
            'duration_weeks' => $validated['duration_weeks'],
        ]);
    }

    /**
     * Import JSON program from file
     */
    public function importJson(Request $request)
    {
        // Removed mimes:json validation as it's unreliable for some environments
        $request->validate([
            'json_file' => 'required|file|max:2048',
        ]);

        $file = $request->file('json_file');
        $content = file_get_contents($file->getRealPath());

        // Attempt to decode
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'message' => 'The uploaded file is not a valid JSON file.',
                'errors' => ['json_file' => ['Invalid JSON format.']],
            ], 422);
        }

        if (! isset($json['sessions']) || ! is_array($json['sessions'])) {
            return response()->json([
                'message' => 'Invalid JSON structure.',
                'errors' => ['json_file' => ['Missing "sessions" array in JSON.']],
            ], 422);
        }

        return response()->json($json);
    }

    /**
     * Import CSV program
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|max:2048',
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();

        $sessions = [];
        $maxDay = 0;

        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Read header
            $header = fgetcsv($handle, 1000, ",");
            if (!$header) {
                fclose($handle);
                return response()->json([
                    'message' => 'Empty CSV file.',
                    'errors' => ['csv_file' => ['File has no content.']],
                ], 422);
            }

            // Normalize header (lowercase, trim)
            $header = array_map(function($h) {
                return strtolower(trim($h));
            }, $header);

            // Find column indices
            $dayIdx = array_search('day', $header);
            $typeIdx = array_search('type', $header);
            $distanceIdx = array_search('distance', $header);
            $durationIdx = array_search('duration', $header);
            $descriptionIdx = array_search('description', $header);
            $advancedConfigIdx = array_search('advanced_config', $header);

            if ($dayIdx === FALSE || $typeIdx === FALSE) {
                fclose($handle);
                return response()->json([
                    'message' => 'Invalid CSV structure.',
                    'errors' => ['csv_file' => ['Missing required columns: "day" and "type".']],
                ], 422);
            }

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Skip empty rows or rows without day/type
                if (count($data) <= max($dayIdx, $typeIdx) || empty($data[$dayIdx])) continue;

                $day = intval($data[$dayIdx]);
                $type = trim($data[$typeIdx]);
                $distance = $distanceIdx !== FALSE && isset($data[$distanceIdx]) && $data[$distanceIdx] !== '' ? floatval($data[$distanceIdx]) : null;
                $duration = $durationIdx !== FALSE && isset($data[$durationIdx]) ? trim($data[$durationIdx]) : null;
                $description = $descriptionIdx !== FALSE && isset($data[$descriptionIdx]) ? trim($data[$descriptionIdx]) : '';
                
                if ($day > $maxDay) {
                    $maxDay = $day;
                }

                // Clean/normalize type
                $type = strtolower(str_replace(' ', '_', $type));
                if (!in_array($type, ['easy_run', 'interval', 'tempo', 'strength', 'long_run', 'rest', 'time_trial', 'custom'])) {
                    $type = 'easy_run'; // fallback
                }

                // Generate advanced config if not provided or empty
                $advancedConfigStr = $advancedConfigIdx !== FALSE && isset($data[$advancedConfigIdx]) ? trim($data[$advancedConfigIdx]) : '';
                $advancedConfig = [];
                if (!empty($advancedConfigStr)) {
                    $advancedConfig = json_decode($advancedConfigStr, true) ?? [];
                }

                if (empty($advancedConfig)) {
                    // Generate basic advanced config based on type
                    $advancedConfig = [
                        'type' => $type,
                        'title' => ucwords(str_replace('_', ' ', $type)),
                        'description' => $description,
                        'intensity' => in_array($type, ['interval', 'time_trial']) ? 'high' : (in_array($type, ['easy_run', 'rest']) ? 'low' : 'medium'),
                    ];
                    // Add type-specific defaults
                    if ($type === 'easy_run' || $type === 'long_run') {
                        $advancedConfig['warmup'] = ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''];
                        $advancedConfig['cooldown'] = ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''];
                        $advancedConfig['main'] = ['by' => 'distance', 'distance' => $distance ?? 5, 'unit' => 'km', 'duration' => $duration ?? '', 'pace' => ''];
                        if ($type === 'long_run') {
                            $advancedConfig['longRun'] = ['fastFinish' => ['enabled' => false, 'distance' => 0, 'unit' => 'km', 'pace' => '']];
                        }
                    } elseif ($type === 'tempo') {
                        $advancedConfig['warmup'] = ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''];
                        $advancedConfig['cooldown'] = ['enabled' => false, 'by' => 'distance', 'distance' => 0, 'unit' => 'km', 'duration' => ''];
                        $advancedConfig['tempo'] = ['by' => 'distance', 'distance' => $distance ?? 8, 'unit' => 'km', 'duration' => $duration ?? '', 'pace' => '', 'effort' => 'moderate'];
                    } elseif ($type === 'interval') {
                        $advancedConfig['warmup'] = ['enabled' => true, 'by' => 'time', 'duration' => '00:10:00'];
                        $advancedConfig['cooldown'] = ['enabled' => true, 'by' => 'time', 'duration' => '00:10:00'];
                        $advancedConfig['interval'] = ['reps' => 6, 'by' => 'distance', 'repDistance' => 0.8, 'repDistanceUnit' => 'km', 'repTime' => '', 'pace' => '', 'recovery' => 'Jog 2:00'];
                    } elseif ($type === 'time_trial') {
                        $advancedConfig['warmup'] = ['enabled' => true, 'by' => 'time', 'duration' => '00:10:00'];
                        $advancedConfig['cooldown'] = ['enabled' => true, 'by' => 'time', 'duration' => '00:10:00'];
                        $advancedConfig['timeTrial'] = ['by' => 'distance', 'distance' => $distance ?? 5, 'unit' => 'km', 'duration' => $duration ?? '', 'pace' => '', 'effort' => 'max_effort'];
                    } elseif ($type === 'strength') {
                        $advancedConfig['strength'] = [
                            'category' => 'core',
                            'plan' => [
                                ['name' => 'Plank', 'sets' => '3', 'reps' => '45-60s', 'equipment' => 'Bodyweight']
                            ]
                        ];
                    } else {
                        $advancedConfig['intensity'] = 'low';
                    }
                }

                $sessions[] = [
                    'day' => $day,
                    'type' => $type,
                    'distance' => $distance,
                    'duration' => $duration,
                    'description' => $description,
                    'advanced_config' => json_encode($advancedConfig)
                ];
            }
            fclose($handle);
        }

        $durationWeeks = ceil($maxDay / 7);
        if ($durationWeeks < 1) $durationWeeks = 1;

        return response()->json([
            'sessions' => $sessions,
            'duration_weeks' => $durationWeeks,
        ]);
    }

    /**
     * Import JSON program and save it directly to database as a new draft
     */
    public function importAndSaveJson(Request $request)
    {
        $request->validate([
            'json_file' => 'required|file|max:2048',
        ]);

        $file = $request->file('json_file');
        $content = file_get_contents($file->getRealPath());
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'The uploaded file is not a valid JSON file.'
            ], 422);
        }

        // Validate structure: can be either flat {sessions: [...]} or nested {program_json: {sessions: [...]}}
        $programJson = isset($json['program_json']) ? $json['program_json'] : (isset($json['sessions']) ? $json : null);
        if (!$programJson || !isset($programJson['sessions']) || !is_array($programJson['sessions'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON structure: Missing "sessions" array.'
            ], 422);
        }

        $title = $json['title'] ?? 'Imported Program ' . date('Y-m-d H:i');
        $slug = Str::slug($title) . '-' . uniqid();

        $program = Program::create([
            'coach_id' => auth()->id(),
            'title' => $title,
            'slug' => $slug,
            'description' => $json['description'] ?? 'Imported from JSON.',
            'difficulty' => $json['difficulty'] ?? 'beginner',
            'distance_target' => $json['distance_target'] ?? '5k',
            'price' => 0.00,
            'program_json' => $programJson,
            'duration_weeks' => $json['duration_weeks'] ?? 12,
            'is_published' => false,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Program imported successfully as draft!',
            'program' => $program
        ]);
    }


    /**
     * Export program as JSON
     */
    public function exportJson(Program $program)
    {
        if ((int) $program->coach_id !== (int) auth()->id()) {
            abort(403);
        }

        $programData = [
            'title' => $program->title,
            'description' => $program->description,
            'difficulty' => $program->difficulty,
            'distance_target' => $program->distance_target,
            'program_json' => $program->program_json,
            'duration_weeks' => $program->duration_weeks,
        ];

        return response()->json($programData)
            ->header('Content-Disposition', 'attachment; filename="'.Str::slug($program->title).'.json"');
    }
}
