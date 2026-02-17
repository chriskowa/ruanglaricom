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
        // 1: Easy Run, 2: Intervals, 3: Easy Run, 4: Tempo, 5: Strength, 6: Long Run, 7: Rest
        for ($day = 1; $day <= $totalDays; $day++) {
            $weekDay = (($day - 1) % 7) + 1;

            switch ($weekDay) {
                case 1:
                case 3:
                    $type = 'easy_run';
                    $distance = 5;
                    $duration = '00:35:00';
                    $description = 'Easy run';
                    break;
                case 2:
                    $type = 'interval';
                    $distance = 6;
                    $duration = '00:45:00';
                    $description = 'Interval session';
                    break;
                case 4:
                    $type = 'tempo';
                    $distance = 8;
                    $duration = '00:45:00';
                    $description = 'Tempo run';
                    break;
                case 5:
                    $type = 'strength';
                    $distance = null;
                    $duration = '00:40:00';
                    $description = 'Strength & mobility';
                    break;
                case 6:
                    $type = 'long_run';
                    $distance = 12;
                    $duration = '01:30:00';
                    $description = 'Long run';
                    break;
                default:
                    $type = 'rest';
                    $distance = null;
                    $duration = null;
                    $description = 'Rest day';
                    break;
            }

            $sessions[] = [
                'day' => $day,
                'type' => $type,
                'distance' => $distance,
                'duration' => $duration,
                'description' => $description,
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
