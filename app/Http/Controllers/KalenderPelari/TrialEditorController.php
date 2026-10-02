<?php

namespace App\Http\Controllers\KalenderPelari;

use App\Http\Controllers\Controller;
use App\Models\KalenderPelari\CalendarProject;
use App\Services\KalenderPelari\CalendarDateEngineService;
use App\Services\KalenderPelari\ProjectService;
use App\Services\KalenderPelari\TrialHandoffService;
use Illuminate\Http\Request;

class TrialEditorController extends Controller
{
    public function __construct(
        private readonly ProjectService $projects,
        private readonly CalendarDateEngineService $dates,
        private readonly TrialHandoffService $trials,
    ) {}

    public function index(Request $request, string $anonymousUuid)
    {
        $project = CalendarProject::query()
            ->where('anonymous_uuid', $anonymousUuid)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->firstOrFail();

        if (! $project->is_anonymous && $project->user_id) {
            if (! auth()->check() || (int) auth()->id() !== (int) $project->user_id) {
                abort(403);
            }

            return redirect()->to($project->routeEditor(), 302);
        }

        if (auth()->check() && $project->is_anonymous) {
            $user = $request->user();
            $freeLimit = (int) config('kalender-pelari.free_project_limit', 3);
            $ownedCount = CalendarProject::where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->count();
            if ($ownedCount < $freeLimit) {
                $converted = $this->projects->convertTrialToAuthenticated($anonymousUuid, $user);
                if ($converted) {
                    return redirect()
                        ->to($converted->routeEditor(), 302)
                        ->with('success', 'Proyek trial otomatis tersimpan sebagai proyek permanen milik Anda.');
                }
            }
        }

        $yearMonths = $this->dates->yearMonthNames($project->year, 'id_ID');
        $canvas = $this->dates->canvasSizeFor($project->format_type);

        if (! $project->pages()->count()) {
            $this->projects->initializePages($project, [
                'calendarType' => $request->query('cal'),
                'templateFamily' => $request->query('tpl', 'MINIMAL_RUNNER'),
            ]);
            $project->refresh()->load('pages');
        }

        if ($project->is_anonymous) {
            try {
                $this->trials->storeToSession(
                    $project->state_draft ?: [],
                    $project->name,
                    [],
                    $project->anonymous_uuid
                );
            } catch (\Throwable) {
            }
        }

        return view('kalender-pelari.editor.trial', [
            'project' => $project,
            'yearMonths' => $yearMonths,
            'canvas' => $canvas,
            'authLoginUrl' => route('login'),
            'authRunnerStravaUrl' => route('runner.strava.connect'),
            'autosaveApiUrl' => route('kalender-pelari.api.trial.autosave'),
            'csrfToken' => csrf_token(),
            'isAuthenticatedEditor' => false,
            'trialConvertUrl' => route('kalender-pelari.api.trial.convert'),
            'runnerDataMonthlyUrl' => route('kalender-pelari.api.runner.monthly').'?year='.((int) $project->year),
        ]);
    }
}
