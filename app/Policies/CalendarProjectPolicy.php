<?php

namespace App\Policies;

use App\Models\KalenderPelari\CalendarProject;
use App\Models\User;

class CalendarProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CalendarProject $project): bool
    {
        return $this->isOwner($user, $project);
    }

    public function update(User $user, CalendarProject $project): bool
    {
        return $this->isOwner($user, $project);
    }

    public function delete(User $user, CalendarProject $project): bool
    {
        return $this->isOwner($user, $project);
    }

    public function exportPdf(User $user, CalendarProject $project): bool
    {
        return $this->isOwner($user, $project);
    }

    public function uploadAsset(User $user, CalendarProject $project): bool
    {
        return $this->isOwner($user, $project);
    }

    private function isOwner(User $user, CalendarProject $project): bool
    {
        $projectUserId = $project->user_id;
        if ($projectUserId === null) {
            return false;
        }

        return (int) $projectUserId === (int) $user->id;
    }
}
