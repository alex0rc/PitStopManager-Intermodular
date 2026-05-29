<?php

namespace App\Policies;

use App\Models\Championship;
use App\Models\User;

class ChampionshipPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Championship $championship): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isOrganizer() || $user->isAdmin();
    }

    public function update(User $user, Championship $championship): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $championship->user_id);
    }

    public function delete(User $user, Championship $championship): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $championship->user_id);
    }

    public function updateStatus(User $user, Championship $championship): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $championship->user_id);
    }
}
