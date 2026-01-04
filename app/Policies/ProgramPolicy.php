<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function view(User $user, Program $program): bool
    {
        return $user->id === $program->coach_id || $user->isAdmin();
    }

    public function update(User $user, Program $program): bool
    {
        return $user->id === $program->coach_id || $user->isAdmin();
    }

    public function delete(User $user, Program $program): bool
    {
        return $user->id === $program->coach_id || $user->isAdmin();
    }
}
