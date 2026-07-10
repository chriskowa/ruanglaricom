<?php

namespace App\Policies\RunningAnalysis;

use App\Models\RunningAnalysis\Trial;
use App\Models\User;

class TrialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Trial $trial): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isRunner() && $trial->runner_id === $user->id) {
            return $trial->isPublished();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Trial $trial): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Trial $trial): bool
    {
        return $user->isAdmin();
    }

    public function review(User $user, Trial $trial): bool
    {
        return $user->isAdmin();
    }

    public function approve(User $user, Trial $trial): bool
    {
        return $user->isAdmin();
    }
    
    public function publish(User $user, Trial $trial): bool
    {
        return $user->isAdmin();
    }
}
