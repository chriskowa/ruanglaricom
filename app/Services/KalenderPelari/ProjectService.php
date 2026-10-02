<?php

namespace App\Services\KalenderPelari;

use App\Models\KalenderPelari\CalendarPage;
use App\Models\KalenderPelari\CalendarProject;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectService
{
    public function __construct(
        private readonly CalendarDateEngineService $dates,
    ) {}

    public function createTrial(array $params, ?User $user = null): CalendarProject
    {
        return DB::transaction(function () use ($params, $user): CalendarProject {
            $isAuth = (bool) $user;
            $project = CalendarProject::create([
                'user_id' => $isAuth ? $user->id : null,
                'anonymous_uuid' => $isAuth ? null : (string) Str::uuid(),
                'name' => mb_substr($params['name'] ?? 'Kalender Lariku', 0, 120),
                'year' => (int) ($params['year'] ?? (int) now()->format('Y') + 1),
                'format_type' => $params['format_type'] ?? 'A4_LANDSCAPE',
                'start_week_on' => $params['start_week_on'] ?? 'MONDAY',
                'status' => $isAuth ? 'ACTIVE' : 'DRAFT_TRIAL',
                'is_anonymous' => ! $isAuth,
                'expires_at' => $isAuth ? null : now()->addMinutes(
                    (int) config('kalender-pelari.trial_expire_minutes', 60 * 24 * 7)
                ),
            ]);

            $this->initializePages($project, [
                'calendarType' => $params['calendar_type'] ?? null,
                'templateFamily' => $params['template_family'] ?? 'MINIMAL_RUNNER',
            ]);

            return $project->fresh('pages');
        });
    }

    public function convertTrialToAuthenticated(string $anonymousUuid, User $user): ?CalendarProject
    {
        $project = CalendarProject::where('anonymous_uuid', $anonymousUuid)
            ->where('is_anonymous', true)
            ->first();

        if (! $project) {
            return null;
        }

        $freeLimit = (int) config('kalender-pelari.free_project_limit', 3);
        $currentCount = CalendarProject::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->count();
        if ($currentCount >= $freeLimit) {
            return null;
        }

        return DB::transaction(function () use ($project, $user): CalendarProject {
            $project->user_id = $user->id;
            $project->is_anonymous = false;
            $project->anonymous_uuid = null;
            $project->expires_at = null;
            $project->status = 'ACTIVE';
            $project->save();

            return $project->fresh();
        });
    }

    public function getRunnerProjectsPaginated(User $user, int $perPage = 9): LengthAwarePaginator
    {
        return CalendarProject::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    public function delete(CalendarProject $project): bool
    {
        return (bool) DB::transaction(function () use ($project): bool {
            $project->pages()->delete();

            return (bool) $project->delete();
        });
    }

    public function initializePages(CalendarProject $project, array $options = []): void
    {
        if ($project->pages()->count()) {
            return;
        }

        $canvas = $this->dates->canvasSizeFor($project->format_type);
        $pagesMeta = $this->dates->yearMonthNames($project->year, 'id_ID');

        DB::transaction(function () use ($project, $pagesMeta, $canvas, $options) {
            $inserts = [];
            $now = Carbon::now();
            foreach ($pagesMeta as $m) {
                $monthNumber = (int) $m['month_number'];
                $pageType = $m['page_type'];
                $inserts[] = [
                    'project_id' => $project->id,
                    'month_number' => $monthNumber,
                    'page_type' => $pageType,
                    'canvas_width_mm' => $canvas['width_mm'],
                    'canvas_height_mm' => $canvas['height_mm'],
                    'layout_config' => json_encode([
                        'background_color' => '#0f172a',
                        'bleed_mm' => 3,
                        'safe_area_mm' => 5,
                        'grid_enabled' => true,
                        'snap_enabled_px' => 5,
                        'calendar_type' => $options['calendarType'] ?? 'MY_RUNNING_YEAR',
                        'template_family' => $options['templateFamily'] ?? 'MINIMAL_RUNNER',
                    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            CalendarPage::insert($inserts);
        });
    }

    public function applyAutosave(CalendarProject $project, array $payload): CalendarProject
    {
        return DB::transaction(function () use ($project, $payload): CalendarProject {
            $clientVersion = (int) ($payload['version_counter'] ?? 0);

            if ($clientVersion > 0 && $clientVersion < $project->version_counter) {
                return $project;
            }

            if (! empty($payload['name'])) {
                $project->name = mb_substr($payload['name'], 0, 120);
            }

            $state = $payload['state_draft'] ?? [];
            if (is_array($state)) {
                $project->state_draft = $state;
            }

            $project->version_counter = ($project->version_counter ?? 1) + 1;
            if ($project->status === 'EXPIRED_TRIAL') {
                $project->status = 'DRAFT_TRIAL';
            }
            $project->save();

            return $project->fresh();
        });
    }
}
