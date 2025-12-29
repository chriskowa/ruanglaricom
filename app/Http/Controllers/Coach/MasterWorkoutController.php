<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\MasterWorkout;
use Illuminate\Http\Request;

class MasterWorkoutController extends Controller
{
    public function index()
    {
        $workouts = MasterWorkout::orderBy('type')->orderBy('title')->get();
        return view('coach.master_workouts.index', compact('workouts'));
    }

    public function create()
    {
        return view('coach.master_workouts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:easy_run,long_run,tempo,interval,strength,rest',
            'description' => 'nullable|string',
            'default_distance' => 'nullable|numeric|min:0',
            'default_duration' => 'nullable|string', // Format HH:MM:SS or generic text
            'intensity' => 'required|in:low,medium,high',
        ]);

        MasterWorkout::create($validated);

        return redirect()->route('coach.master-workouts.index')
            ->with('success', 'Workout template created successfully!');
    }

    public function edit(MasterWorkout $masterWorkout)
    {
        return view('coach.master_workouts.edit', compact('masterWorkout'));
    }

    public function update(Request $request, MasterWorkout $masterWorkout)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:easy_run,long_run,tempo,interval,strength,rest',
            'description' => 'nullable|string',
            'default_distance' => 'nullable|numeric|min:0',
            'default_duration' => 'nullable|string',
            'intensity' => 'required|in:low,medium,high',
        ]);

        $masterWorkout->update($validated);

        return redirect()->route('coach.master-workouts.index')
            ->with('success', 'Workout template updated successfully!');
    }

    public function destroy(MasterWorkout $masterWorkout)
    {
        $masterWorkout->delete();
        return redirect()->route('coach.master-workouts.index')
            ->with('success', 'Workout template deleted successfully!');
    }
}
