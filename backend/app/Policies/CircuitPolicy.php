<?php

namespace App\Policies;

use App\Models\Circuit;
use App\Models\User;

class CircuitPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Circuit $circuit): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isOrganizer() || $user->isAdmin();
    }

    public function update(User $user, Circuit $circuit): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $circuit->user_id);
    }

    public function delete(User $user, Circuit $circuit): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $user->id === $circuit->user_id);
    }
}
