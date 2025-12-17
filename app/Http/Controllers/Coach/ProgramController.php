<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

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
        return view('coach.programs.create', compact('cities'));
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
        ]);

        $validated['coach_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        $validated['program_json'] = json_decode($validated['program_json'], true);
        $validated['is_published'] = $request->has('is_published') ? (bool)$request->is_published : false;

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
        $this->authorize('view', $program);
        return view('coach.programs.show', compact('program'));
    }

    public function edit(Program $program)
    {
        $this->authorize('update', $program);
        $cities = City::orderBy('name')->get();
        return view('coach.programs.edit', compact('program', 'cities'));
    }

    public function update(Request $request, Program $program)
    {
        $this->authorize('update', $program);

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
        ]);

        if ($validated['title'] !== $program->title) {
            $validated['slug'] = Str::slug($validated['title']) . '-' . uniqid();
        }

        $validated['program_json'] = json_decode($validated['program_json'], true);
        $validated['is_published'] = $request->has('is_published') ? (bool)$request->is_published : $program->is_published;

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
        $this->authorize('delete', $program);
        $program->delete();

        return redirect()->route('coach.programs.index')
            ->with('success', 'Program berhasil dihapus!');
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

        // Generate basic template
        for ($day = 1; $day <= $totalDays; $day++) {
            $sessions[] = [
                'day' => $day,
                'type' => 'easy_run',
                'distance' => 5,
                'duration' => '00:30:00',
                'description' => 'Easy run',
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
        $request->validate([
            'json_file' => 'required|file|mimes:json|max:2048',
        ]);

        $file = $request->file('json_file');
        $content = file_get_contents($file->getRealPath());
        $json = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['json_file' => 'File JSON tidak valid.']);
        }

        if (!isset($json['sessions']) || !is_array($json['sessions'])) {
            return back()->withErrors(['json_file' => 'Format JSON tidak valid. Harus memiliki field "sessions" berupa array.']);
        }

        return response()->json($json);
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
        $this->authorize('update', $program);
        
        $program->update(['is_published' => true]);
        
        return back()->with('success', 'Program berhasil dipublikasikan!');
    }

    /**
     * Unpublish program
     */
    public function unpublish(Program $program)
    {
        $this->authorize('update', $program);
        
        $program->update(['is_published' => false]);
        
        return back()->with('success', 'Program berhasil diunpublish!');
    }

    /**
     * Export program as JSON
     */
    public function exportJson(Program $program)
    {
        $this->authorize('view', $program);
        
        $programData = [
            'title' => $program->title,
            'description' => $program->description,
            'difficulty' => $program->difficulty,
            'distance_target' => $program->distance_target,
            'program_json' => $program->program_json,
            'duration_weeks' => $program->duration_weeks,
        ];
        
        return response()->json($programData)
            ->header('Content-Disposition', 'attachment; filename="' . Str::slug($program->title) . '.json"');
    }
}
