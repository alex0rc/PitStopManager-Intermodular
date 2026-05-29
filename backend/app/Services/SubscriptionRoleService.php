<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SubscriptionRoleService
{
    public function expireDueSubscriptions(): array
    {
        $expired = 0;
        $demoted = 0;

        $subscriptions = Subscription::query()
            ->where('status', 'active')
            ->where('ends_at', '<', now()->toDateString())
            ->with('user')
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->update(['status' => 'expired']);
            $expired++;

            $user = $subscription->user;
            if (!$user || $user->isAdmin()) {
                continue;
            }

            $before = $user->role;
            $this->syncRoleForUser($user);

            if ($before === 'organizer' && $user->fresh()->role === 'pilot') {
                $demoted++;
                Log::info('User demoted to pilot after subscription expired', [
                    'user_id'         => $user->id,
                    'subscription_id' => $subscription->id,
                ]);
            }
        }

        return ['expired' => $expired, 'demoted' => $demoted];
    }

    public function promoteToOrganizerIfNeeded(User $user): void
    {
        if ($user->isAdmin() || !$user->isPilot()) {
            return;
        }

        if ($user->hasActiveSubscription()) {
            $user->update(['role' => 'organizer']);
        }
    }

    public function syncRoleForUser(User $user): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $user->refresh();

        if ($user->hasActiveSubscription()) {
            if ($user->isPilot()) {
                $user->update(['role' => 'organizer']);
            }

            return;
        }

        if ($user->isOrganizer()) {
            $user->update(['role' => 'pilot']);
        }
    }
}
