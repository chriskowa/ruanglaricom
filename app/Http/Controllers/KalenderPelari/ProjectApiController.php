<?php

namespace App\Http\Controllers\KalenderPelari;

use App\Http\Controllers\Controller;
use App\Models\KalenderPelari\CalendarProject;
use App\Services\KalenderPelari\ProjectService;
use App\Services\KalenderPelari\RunnerDataBindingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProjectApiController extends Controller
{
    public function __construct(
        private readonly ProjectService $projects,
        private readonly RunnerDataBindingService $runnerData,
    ) {}

    public function convertTrial(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'code' => 'LOGIN_REQUIRED',
                'message' => 'Silakan login untuk mengonversi trial menjadi proyek permanen.',
                'login_url' => route('login'),
            ], 401);
        }

        $valid = $request->validate([
            'anonymous_uuid' => ['required', 'uuid'],
        ]);

        $converted = $this->projects->convertTrialToAuthenticated($valid['anonymous_uuid'], $user);

        if (! $converted) {
            $freeLimit = (int) config('kalender-pelari.free_project_limit', 3);

            return response()->json([
                'success' => false,
                'code' => 'LIMIT_REACHED_OR_NOT_FOUND',
                'message' => "Gagal mengonversi. Maksimal {$freeLimit} proyek free per user atau trial UUID tidak valid.",
                'free_limit' => $freeLimit,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Proyek trial berhasil disimpan sebagai proyek permanen milik Anda.',
            'project' => [
                'id' => $converted->id,
                'name' => $converted->name,
                'year' => $converted->year,
                'status' => $converted->status,
                'editor_url' => $converted->routeEditor(),
                'my_projects_url' => route('kalender-pelari.my-projects'),
            ],
        ]);
    }

    public function runnerMonthlyData(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'code' => 'LOGIN_REQUIRED',
                'login_url' => route('login'),
            ], 401);
        }

        $year = (int) $request->query('year', (int) now()->format('Y'));
        if ($year < 2020 || $year > 2040) {
            $year = (int) now()->format('Y');
        }

        $data = $this->runnerData->getMonthlyAggregate($user, $year);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function storeManualActivity(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'code' => 'LOGIN_REQUIRED',
                'login_url' => route('login'),
            ], 401);
        }

        try {
            $valid = $request->validate([
                'title' => ['nullable', 'string', 'max:160'],
                'sport_type' => ['nullable', 'string', 'max:32'],
                'start_time' => ['required', 'date'],
                'distance_km' => ['required', 'numeric', 'min:0', 'max:500'],
                'moving_time_s' => ['nullable', 'integer', 'min:0'],
                'elapsed_time_s' => ['nullable', 'integer', 'min:0'],
                'avg_pace_sec_per_km' => ['nullable', 'integer', 'min:0'],
                'elevation_gain_m' => ['nullable', 'numeric', 'min:0'],
                'elevation_loss_m' => ['nullable', 'numeric', 'min:0'],
                'calories' => ['nullable', 'integer', 'min:0'],
                'notes' => ['nullable', 'string', 'max:1000'],
                'is_public' => ['nullable', 'boolean'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'code' => 'VALIDATION_FAILED',
                'errors' => $e->validator->errors()->toArray(),
            ], 422);
        }

        $activity = $this->runnerData->storeManualActivity($user, $valid);

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas manual berhasil disimpan.',
            'activity' => [
                'id' => $activity->id,
                'title' => $activity->title,
                'distance_km' => (float) $activity->distance_km,
                'moving_time_s' => (int) $activity->moving_time_s,
                'formatted_avg_pace' => $activity->formatted_avg_pace,
                'formatted_moving_time' => $activity->formatted_moving_time,
            ],
        ]);
    }

    public function autosaveTrial(Request $request)
    {
        $valid = $this->validateAutosavePayload($request, requireUser: false);

        $project = CalendarProject::query()
            ->where('anonymous_uuid', $valid['anonymous_uuid'])
            ->where('is_anonymous', true)
            ->first();

        if (! $project) {
            return response()->json([
                'success' => false,
                'code' => 'TRIAL_NOT_FOUND',
                'message' => 'Proyek trial tidak ditemukan. Silakan buat proyek baru lewat wizard.',
            ], 404);
        }

        $saved = $this->projects->applyAutosave($project, $valid);

        return response()->json([
            'success' => true,
            'project' => [
                'id' => $project->id,
                'anonymous_uuid' => $project->anonymous_uuid,
                'version_counter' => $saved->version_counter,
                'updated_at' => $saved->updated_at?->toIso8601String(),
                'expires_at' => $saved->expires_at?->toIso8601String(),
                'editor_url' => $saved->routeEditor(),
            ],
        ]);
    }

    public function autosave(Request $request)
    {
        $valid = $this->validateAutosavePayload($request, requireUser: true);

        $project = CalendarProject::query()
            ->where('id', $valid['project_id'])
            ->where('user_id', auth()->id())
            ->first();

        if (! $project) {
            return response()->json([
                'success' => false,
                'code' => 'FORBIDDEN',
                'message' => 'Anda tidak memiliki izin untuk menyimpan proyek ini.',
            ], 403);
        }

        $saved = $this->projects->applyAutosave($project, $valid);

        return response()->json([
            'success' => true,
            'project' => [
                'id' => $saved->id,
                'version_counter' => $saved->version_counter,
                'updated_at' => $saved->updated_at?->toIso8601String(),
            ],
        ]);
    }

    private function validateAutosavePayload(Request $request, bool $requireUser): array
    {
        $rules = [
            'state_draft' => ['required_without:state_draft_gzip', 'array'],
            'state_draft_gzip' => ['required_without:state_draft', 'string', 'max:1000000'],
            'version_counter' => ['nullable', 'integer', 'min:1'],
            'name' => ['nullable', 'string', 'max:120'],
        ];

        if ($requireUser) {
            $rules['project_id'] = ['required', 'integer', 'min:1'];
        } else {
            $rules['anonymous_uuid'] = ['required', 'uuid'];
        }

        try {
            $valid = $request->validate($rules);
            if (isset($valid['state_draft_gzip'])) {
                $compressed = base64_decode($valid['state_draft_gzip'], true);
                $decoded = $compressed === false ? false : @gzdecode($compressed, 2 * 1024 * 1024);
                $state = $decoded === false ? null : json_decode($decoded, true);
                if (! is_array($state) || ! isset($state['pages']) || ! is_array($state['pages'])) {
                    throw ValidationException::withMessages([
                        'state_draft_gzip' => 'Data kalender tidak valid. Muat ulang halaman dan coba lagi.',
                    ]);
                }
                $valid['state_draft'] = $state;
                unset($valid['state_draft_gzip']);
            }

            return $valid;
        } catch (ValidationException $e) {
            $errors = $e->validator->errors()->toArray();

            return response()->json([
                'success' => false,
                'code' => 'VALIDATION_FAILED',
                'errors' => $errors,
            ], 422)->throwResponse();
        }
    }
}
