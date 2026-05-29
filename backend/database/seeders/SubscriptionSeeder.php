<?php

namespace Database\Seeders;

use App\Mail\SubscriptionActivatedMail;
use App\Mail\SubscriptionExpiringMail;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionRoleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $basico = SubscriptionPlan::where('slug', 'basico')->first();
        $pro = SubscriptionPlan::where('slug', 'profesional')->first();

        if (!$basico || !$pro) {
            return;
        }

        $this->seedCarlosActive($pro);
        $this->seedExpiringSoon('maria@pitstop.com', $basico, daysUntilEnd: 7);
        $this->seedExpiringSoon('piloto2@pitstop.com', $basico, daysUntilEnd: 1, asOrganizer: true);
    }

    private function seedCarlosActive(SubscriptionPlan $plan): void
    {
        $carlos = User::where('email', 'carlos@pitstop.com')->first();
        if (!$carlos) {
            return;
        }

        $startsAt = today();
        $endsAt = today()->addDays($plan->duration_days);

        $subscription = Subscription::updateOrCreate(
            [
                'user_id' => $carlos->id,
                'plan_id' => $plan->id,
                'status'  => 'active',
            ],
            [
                'starts_at'             => $startsAt->toDateString(),
                'ends_at'               => $endsAt->toDateString(),
                'reminder_week_sent_at' => null,
                'reminder_day_sent_at'  => null,
            ]
        );

        Payment::updateOrCreate(
            [
                'subscription_id' => $subscription->id,
                'user_id'         => $carlos->id,
                'status'          => 'succeeded',
            ],
            [
                'amount'   => $plan->price,
                'currency' => 'EUR',
                'paid_at'  => now(),
            ]
        );

        $this->sendActivatedMail($subscription);
        $this->pauseBetweenMails();
    }

    // --- Suscripción por caducar ---
    private function seedExpiringSoon(
        string $email,
        SubscriptionPlan $plan,
        int $daysUntilEnd,
        bool $asOrganizer = false,
    ): void {
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->command?->warn("Usuario no encontrado para seed de suscripción: {$email}");

            return;
        }

        if ($asOrganizer) {
            $user->update(['role' => 'organizer']);
        }

        $endsAt = today()->addDays($daysUntilEnd);
        $startsAt = today()->subDays(max($plan->duration_days, $daysUntilEnd + 1) - $daysUntilEnd);

        $subscription = Subscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status'  => 'active',
            ],
            [
                'starts_at'             => $startsAt->toDateString(),
                'ends_at'               => $endsAt->toDateString(),
                'reminder_week_sent_at' => null,
                'reminder_day_sent_at'  => null,
            ]
        );

        if ($asOrganizer) {
            app(SubscriptionRoleService::class)->syncRoleForUser($user->fresh());
        }

        $this->sendExpiringMail($subscription->fresh()->load(['plan', 'user']), $daysUntilEnd);
    }

    private function pauseBetweenMails(): void
    {
        sleep(2);
    }

    private function sendActivatedMail(Subscription $subscription): void
    {
        $subscription->load(['plan', 'user']);
        if (!$subscription->user?->email) {
            return;
        }

        try {
            Mail::to($subscription->user->email)->send(new SubscriptionActivatedMail($subscription));
            $this->command?->info("Email de activación enviado a {$subscription->user->email}");
        } catch (\Throwable $e) {
            Log::warning('SubscriptionSeeder: activation email failed', [
                'email' => $subscription->user->email,
                'error' => $e->getMessage(),
            ]);
            $this->command?->warn("Email de activación no enviado ({$subscription->user->email}): {$e->getMessage()}");
        }
    }

    private function sendExpiringMail(Subscription $subscription, int $daysRemaining): void
    {
        if (!$subscription->user?->email) {
            return;
        }

        $label = $daysRemaining <= 1 ? '1 día' : "{$daysRemaining} días";

        try {
            Mail::to($subscription->user->email)->send(
                new SubscriptionExpiringMail($subscription, $daysRemaining)
            );

            $subscription->update([
                'reminder_week_sent_at' => $daysRemaining >= 7 ? now() : $subscription->reminder_week_sent_at,
                'reminder_day_sent_at'  => $daysRemaining <= 1 ? now() : $subscription->reminder_day_sent_at,
            ]);

            $this->command?->info(
                "Recordatorio ({$label}) enviado a {$subscription->user->email} — caduca {$subscription->ends_at->format('d/m/Y')}"
            );
        } catch (\Throwable $e) {
            Log::warning('SubscriptionSeeder: expiry email failed', [
                'email' => $subscription->user->email,
                'days'  => $daysRemaining,
                'error' => $e->getMessage(),
            ]);
            $this->command?->warn(
                "Recordatorio ({$label}) no enviado ({$subscription->user->email}): {$e->getMessage()}"
            );
        }

        $this->pauseBetweenMails();
    }
}
