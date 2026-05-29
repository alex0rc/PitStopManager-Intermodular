<?php

namespace Tests\Feature;

use App\Mail\SubscriptionExpiringMail;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionExpiryReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_week_and_day_reminder_emails(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-remind',
            'price' => 50,
            'duration_days' => 90,
            'max_championships' => 3,
            'is_active' => true,
        ]);

        $weekSub = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(83)->toDateString(),
            'ends_at' => now()->addDays(7)->toDateString(),
        ]);

        $dayUser = User::factory()->create(['email' => 'day@pitstop.com', 'role' => 'organizer', 'is_active' => true]);
        $daySub = Subscription::create([
            'user_id' => $dayUser->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDays(89)->toDateString(),
            'ends_at' => now()->addDay()->toDateString(),
        ]);

        $this->artisan('subscriptions:send-expiry-reminders')->assertSuccessful();

        Mail::assertSent(SubscriptionExpiringMail::class, function (SubscriptionExpiringMail $mail) use ($weekSub) {
            return $mail->subscription->id === $weekSub->id && $mail->daysRemaining === 7;
        });

        Mail::assertSent(SubscriptionExpiringMail::class, function (SubscriptionExpiringMail $mail) use ($daySub) {
            return $mail->subscription->id === $daySub->id && $mail->daysRemaining === 1;
        });

        $this->assertNotNull($weekSub->fresh()->reminder_week_sent_at);
        $this->assertNotNull($daySub->fresh()->reminder_day_sent_at);
    }
}
