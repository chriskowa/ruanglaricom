<?php

namespace App\Policies\RunningAnalysis;

use App\Models\RunningAnalysis\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Report $report): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isRunner() && $report->runner_id === $user->id) {
            return $report->isPublished();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Report $report): bool
    {
        return $user->isAdmin();
    }
}
