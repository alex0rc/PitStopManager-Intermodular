<?php

namespace App\Policies;

use App\Models\Race;
use App\Models\User;

class RacePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Race $race): bool
    {
        return true;
    }

    public function create(User $user, Race $race): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $race->championship->user_id);
    }

    public function update(User $user, Race $race): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $race->championship->user_id);
    }

    public function delete(User $user, Race $race): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $race->championship->user_id);
    }
}
