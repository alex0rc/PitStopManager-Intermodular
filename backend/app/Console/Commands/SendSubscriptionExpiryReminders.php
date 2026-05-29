<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSubscriptionExpiryReminders extends Command
{
    protected $signature = 'subscriptions:send-expiry-reminders';

    protected $description = 'Envía correos 7 días y 1 día antes de que caduque una suscripción activa';

    public function handle(): int
    {
        $weekTarget = now()->addDays(7)->toDateString();
        $dayTarget = now()->addDay()->toDateString();

        $weekSent = $this->sendReminders(
            daysRemaining: 7,
            endsOn: $weekTarget,
            column: 'reminder_week_sent_at',
        );

        $daySent = $this->sendReminders(
            daysRemaining: 1,
            endsOn: $dayTarget,
            column: 'reminder_day_sent_at',
        );

        $this->info("Recordatorios 7 días: {$weekSent}");
        $this->info("Recordatorios 1 día: {$daySent}");

        return self::SUCCESS;
    }

    private function sendReminders(int $daysRemaining, string $endsOn, string $column): int
    {
        $sent = 0;

        $subscriptions = Subscription::query()
            ->where('status', 'active')
            ->whereDate('ends_at', $endsOn)
            ->whereNull($column)
            ->with(['plan', 'user'])
            ->get();

        foreach ($subscriptions as $subscription) {
            $user = $subscription->user;
            if (!$user?->email) {
                continue;
            }

            try {
                Mail::to($user->email)->send(new SubscriptionExpiringMail($subscription, $daysRemaining));
                $subscription->update([$column => now()]);
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Failed to send subscription expiry reminder', [
                    'subscription_id' => $subscription->id,
                    'days_remaining' => $daysRemaining,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }
}
