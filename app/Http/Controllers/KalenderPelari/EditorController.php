<?php

namespace App\Http\Controllers\KalenderPelari;

use App\Http\Controllers\Controller;
use App\Models\KalenderPelari\CalendarProject;
use App\Services\KalenderPelari\CalendarDateEngineService;
use App\Services\KalenderPelari\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EditorController extends Controller
{
    public function __construct(
        private readonly CalendarDateEngineService $dates,
        private readonly ProjectService $projects,
    ) {}

    public function myProjects(Request $request)
    {
        $user = $request->user();
        $projects = $this->projects->getRunnerProjectsPaginated($user, 12);

        $freeLimit = (int) config('kalender-pelari.free_project_limit', 3);
        $totalOwned = CalendarProject::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->count();

        return view('kalender-pelari.my-projects', [
            'projects' => $projects,
            'free_limit' => $freeLimit,
            'total_owned' => $totalOwned,
            'can_create_new' => $totalOwned < $freeLimit,
        ]);
    }

    public function index(Request $request, CalendarProject $project)
    {
        Gate::authorize('update', $project);

        $yearMonths = $this->dates->yearMonthNames($project->year, 'id_ID');
        $canvas = $this->dates->canvasSizeFor($project->format_type);

        return view('kalender-pelari.editor.trial', [
            'project' => $project,
            'yearMonths' => $yearMonths,
            'canvas' => $canvas,
            'authLoginUrl' => route('login'),
            'authRunnerStravaUrl' => route('runner.strava.connect'),
            'autosaveApiUrl' => route('kalender-pelari.api.autosave'),
            'csrfToken' => csrf_token(),
            'isAuthenticatedEditor' => true,
            'runnerDataMonthlyUrl' => route('kalender-pelari.api.runner.monthly').'?year='.((int) $project->year),
        ]);
    }

    public function destroy(Request $request, CalendarProject $project)
    {
        Gate::authorize('delete', $project);

        $this->projects->delete($project);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Proyek berhasil dihapus.',
                'redirect' => route('kalender-pelari.my-projects'),
            ]);
        }

        return redirect()
            ->route('kalender-pelari.my-projects')
            ->with('success', 'Proyek berhasil dihapus.');
    }
}
