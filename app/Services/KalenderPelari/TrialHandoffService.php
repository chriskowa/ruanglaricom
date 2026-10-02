<?php

namespace App\Services\KalenderPelari;

use App\Models\KalenderPelari\CalendarProject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TrialHandoffService
{
    private const SESSION_KEY_STATE = 'kp_trial_state_compressed';

    private const SESSION_KEY_PROJECT_NAME = 'kp_trial_project_name';

    private const SESSION_KEY_ASSETS_INDEX = 'kp_trial_assets_index';

    private const SESSION_KEY_ANONYMOUS_UUID = 'kp_trial_anonymous_uuid';

    public function storeToSession(array $state, ?string $projectName, array $assetIndex, ?string $anonymousUuid = null): void
    {
        $serialized = json_encode($state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $compressed = gzencode($serialized, 9);
        if ($compressed === false) {
            $compressed = $serialized;
        }
        $encrypted = encrypt($compressed);

        session([
            self::SESSION_KEY_STATE => $encrypted,
            self::SESSION_KEY_PROJECT_NAME => $projectName,
            self::SESSION_KEY_ASSETS_INDEX => $assetIndex,
            self::SESSION_KEY_ANONYMOUS_UUID => $anonymousUuid,
        ]);
        session()->save();
    }

    public function hasPendingTrialInSession(): bool
    {
        return ! empty(session(self::SESSION_KEY_STATE)) || ! empty(session(self::SESSION_KEY_ANONYMOUS_UUID));
    }

    public function restoreFromSession(User $user): ?array
    {
        $encrypted = session(self::SESSION_KEY_STATE);
        $anonymousUuid = session(self::SESSION_KEY_ANONYMOUS_UUID);

        $state = null;
        if ($encrypted) {
            try {
                $compressed = decrypt($encrypted);
                $json = @gzdecode($compressed);
                if ($json === false) {
                    $json = $compressed;
                }
                $state = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                $state = null;
            }
        }

        if (! $state && ! $anonymousUuid) {
            $this->clearSession();

            return null;
        }

        return [
            'state' => $state,
            'project_name' => session(self::SESSION_KEY_PROJECT_NAME),
            'assets_index' => session(self::SESSION_KEY_ASSETS_INDEX) ?? [],
            'anonymous_uuid' => $anonymousUuid,
        ];
    }

    public function persistRestoredToUser(User $user, array $restored): ?CalendarProject
    {
        $anonymousUuid = $restored['anonymous_uuid'] ?? null;
        $state = $restored['state'] ?? null;
        $projectName = $restored['project_name'] ?? null;
        $assetIndex = $restored['assets_index'] ?? [];

        $anonymousProject = null;
        if ($anonymousUuid) {
            $anonymousProject = CalendarProject::where('anonymous_uuid', $anonymousUuid)
                ->where('is_anonymous', true)
                ->first();
        }

        if ($anonymousProject) {
            return DB::transaction(function () use ($user, $anonymousProject, $state, $projectName) {
                $anonymousProject->user_id = $user->id;
                $anonymousProject->is_anonymous = false;
                $anonymousProject->anonymous_uuid = null;
                $anonymousProject->expires_at = null;
                $anonymousProject->status = 'ACTIVE';
                if ($projectName) {
                    $anonymousProject->name = mb_substr($projectName, 0, 120);
                }
                if ($state) {
                    $anonymousProject->state_draft = $state;
                }
                $anonymousProject->save();

                $this->clearSession();

                return $anonymousProject->fresh();
            });
        }

        if (! $state) {
            $this->clearSession();

            return null;
        }

        return DB::transaction(function () use ($user, $state, $projectName) {
            $project = CalendarProject::create([
                'user_id' => $user->id,
                'anonymous_uuid' => null,
                'name' => mb_substr($projectName ?? 'Kalender Lariku', 0, 120),
                'year' => (int) ($state['project']['year'] ?? (int) now()->format('Y') + 1),
                'format_type' => $state['project']['format_type'] ?? 'A4_LANDSCAPE',
                'start_week_on' => $state['project']['start_week_on'] ?? 'MONDAY',
                'status' => 'ACTIVE',
                'is_anonymous' => false,
                'expires_at' => null,
                'state_draft' => $state,
            ]);

            $this->clearSession();

            return $project;
        });
    }

    public function clearSession(): void
    {
        session()->forget([
            self::SESSION_KEY_STATE,
            self::SESSION_KEY_PROJECT_NAME,
            self::SESSION_KEY_ASSETS_INDEX,
            self::SESSION_KEY_ANONYMOUS_UUID,
        ]);
        session()->save();
    }
}
