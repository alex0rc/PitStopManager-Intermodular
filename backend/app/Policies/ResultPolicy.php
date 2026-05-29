<?php

namespace App\Policies;

use App\Models\Race;
use App\Models\Result;
use App\Models\User;

class ResultPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user, Race $race): bool
    {
        $race->loadMissing('championship');
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $race->championship->user_id);
    }

    public function update(User $user, Result $result): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $result->race->championship->user_id);
    }

    public function delete(User $user, Result $result): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $result->race->championship->user_id);
    }
}
