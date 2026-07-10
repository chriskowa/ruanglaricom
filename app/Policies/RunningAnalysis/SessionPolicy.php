<?php

namespace App\Policies\RunningAnalysis;

use App\Models\RunningAnalysis\Session;
use App\Models\User;

class SessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Session $session): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Session $session): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Session $session): bool
    {
        return $user->isAdmin();
    }

    public function manageRunners(User $user, Session $session): bool
    {
        return $user->isAdmin();
    }

    public function capture(User $user, Session $session): bool
    {
        return $user->isAdmin();
    }
}
