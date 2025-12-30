<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\MasterWorkout;
use App\Models\WorkoutVisibilityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomWorkoutController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:easy_run,long_run,tempo,interval,strength,rest',
            'description' => 'nullable|string',
            'default_distance' => 'nullable|numeric|min:0',
            'default_duration' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $validated['coach_id'] = auth()->id();
        $validated['intensity'] = $this->determineIntensity($validated['type']);
        $validated['is_public'] = $request->boolean('is_public');

        $workout = MasterWorkout::create($validated);

        // Audit Log
        if ($workout->is_public) {
            WorkoutVisibilityLog::create([
                'master_workout_id' => $workout->id,
                'user_id' => auth()->id(),
                'old_visibility' => false,
                'new_visibility' => true,
            ]);
        }

        return response()->json([
            'message' => 'Custom workout created successfully',
            'workout' => $workout
        ]);
    }

    public function update(Request $request, MasterWorkout $customWorkout)
    {
        // Authorization
        if ((int)$customWorkout->coach_id !== (int)auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:easy_run,long_run,tempo,interval,strength,rest',
            'description' => 'nullable|string',
            'default_distance' => 'nullable|numeric|min:0',
            'default_duration' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        $oldVisibility = $customWorkout->is_public;
        $newVisibility = $request->boolean('is_public');

        $customWorkout->update([
            'title' => $validated['title'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'default_distance' => $validated['default_distance'] ?? null,
            'default_duration' => $validated['default_duration'] ?? null,
            'is_public' => $newVisibility,
            'intensity' => $this->determineIntensity($validated['type']),
        ]);

        if ($oldVisibility !== $newVisibility) {
            WorkoutVisibilityLog::create([
                'master_workout_id' => $customWorkout->id,
                'user_id' => auth()->id(),
                'old_visibility' => $oldVisibility,
                'new_visibility' => $newVisibility,
            ]);
        }

        return response()->json([
            'message' => 'Custom workout updated successfully',
            'workout' => $customWorkout
        ]);
    }

    public function destroy(MasterWorkout $customWorkout)
    {
        if ((int)$customWorkout->coach_id !== (int)auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $customWorkout->delete();

        return response()->json(['message' => 'Custom workout deleted successfully']);
    }

    private function determineIntensity($type)
    {
        return match ($type) {
            'easy_run', 'rest' => 'low',
            'long_run', 'tempo' => 'medium',
            'interval', 'strength' => 'high',
            default => 'low',
        };
    }
}
