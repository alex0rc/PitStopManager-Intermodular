<?php

namespace App\Policies;

use App\Models\Championship;
use App\Models\Inscription;
use App\Models\User;

class InscriptionPolicy
{
    private function ownsChampionship(User $user, Inscription $inscription): bool
    {
        return $inscription->championship()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user, ?Championship $championship = null): bool
    {
        if ($user->isPilot()) {
            return true;
        }

        if ($user->isOrganizer() && $championship) {
            return (int) $championship->user_id !== (int) $user->id;
        }

        return false;
    }

    public function updateStatus(User $user, Inscription $inscription): bool
    {
        return $user->isAdmin() || ($user->isOrganizer() && $this->ownsChampionship($user, $inscription));
    }

    public function delete(User $user, Inscription $inscription): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isOrganizer() && $this->ownsChampionship($user, $inscription)) {
            return true;
        }

        return $user->isPilot() && (int) $user->id === (int) $inscription->user_id;
    }

    public function updateRaces(User $user, Inscription $inscription): bool
    {
        return $this->manageRaces($user, $inscription)
            && ($user->isPilot() || $user->isOrganizer())
            && (int) $user->id === (int) $inscription->user_id
            && in_array($inscription->status, ['pending', 'confirmed'], true);
    }

    public function manageRaces(User $user, Inscription $inscription): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isOrganizer() && $this->ownsChampionship($user, $inscription)) {
            return true;
        }

        return $user->isPilot()
            && (int) $user->id === (int) $inscription->user_id
            && in_array($inscription->status, ['pending', 'confirmed'], true);
    }
}
