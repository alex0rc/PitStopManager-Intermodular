<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionQuotaService
{
    public const QUOTA_STATUSES = ['draft', 'published', 'in_progress'];

    public function activeSubscription(User $user): ?Subscription
    {
        return $user->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>=', now()->toDateString())
            ->with('plan')
            ->latest()
            ->first();
    }

    public function countActiveChampionships(User $user): int
    {
        return $user->championships()
            ->whereIn('status', self::QUOTA_STATUSES)
            ->count();
    }

    /**
     * @return array{
     *   has_active_subscription: bool,
     *   plan_name: string|null,
     *   max_championships: int,
     *   current_championships: int,
     *   remaining_championships: int,
     *   can_create_championship: bool,
     *   duration_days: int|null,
     *   starts_at: string|null,
     *   ends_at: string|null,
     *   days_remaining: int|null,
     *   deny_reason: string|null
     * }
     */
    public function summary(User $user): array
    {
        if ($user->isAdmin()) {
            return [
                'has_active_subscription' => true,
                'plan_name' => 'Administrador',
                'max_championships' => 0,
                'current_championships' => $this->countActiveChampionships($user),
                'remaining_championships' => 0,
                'can_create_championship' => true,
                'duration_days' => null,
                'starts_at' => null,
                'ends_at' => null,
                'days_remaining' => null,
                'deny_reason' => null,
            ];
        }

        $subscription = $this->activeSubscription($user);

        if (!$subscription || !$subscription->plan) {
            return [
                'has_active_subscription' => false,
                'plan_name' => null,
                'max_championships' => 0,
                'current_championships' => $this->countActiveChampionships($user),
                'remaining_championships' => 0,
                'can_create_championship' => false,
                'duration_days' => null,
                'starts_at' => null,
                'ends_at' => null,
                'days_remaining' => null,
                'deny_reason' => 'Necesitas una suscripción activa para crear campeonatos.',
            ];
        }

        $plan = $subscription->plan;
        $max = (int) $plan->max_championships;
        $current = $this->countActiveChampionships($user);
        $remaining = max(0, $max - $current);
        $denyReason = $current >= $max
            ? "Tu plan «{$plan->name}» permite {$max} campeonato(s) activo(s). Mejora tu plan para crear más."
            : null;

        $endsAt = Carbon::parse($subscription->ends_at)->startOfDay();
        $daysRemaining = (int) now()->startOfDay()->diffInDays($endsAt, false);

        return [
            'has_active_subscription' => true,
            'plan_name' => $plan->name,
            'max_championships' => $max,
            'current_championships' => $current,
            'remaining_championships' => $remaining,
            'can_create_championship' => $remaining > 0,
            'duration_days' => (int) $plan->duration_days,
            'starts_at' => $subscription->starts_at?->toDateString(),
            'ends_at' => $subscription->ends_at?->toDateString(),
            'days_remaining' => max(0, $daysRemaining),
            'deny_reason' => $denyReason,
        ];
    }

    public function canCreateChampionship(User $user): bool
    {
        return $this->summary($user)['can_create_championship'];
    }

    public function createChampionshipDeniedReason(User $user): ?string
    {
        return $this->summary($user)['deny_reason'];
    }
}
