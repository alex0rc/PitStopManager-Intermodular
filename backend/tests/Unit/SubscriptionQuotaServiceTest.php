<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Championship;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\SubscriptionQuotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionQuotaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_without_subscription_cannot_create(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $service = app(SubscriptionQuotaService::class);

        $this->assertFalse($service->canCreateChampionship($organizer));
        $this->assertStringContainsString('suscripción activa', $service->createChampionshipDeniedReason($organizer));
    }

    public function test_organizer_at_championship_limit_cannot_create_more(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $category = Category::create(['name' => 'Senior']);
        $plan = SubscriptionPlan::create([
            'name' => 'Básico',
            'slug' => 'basico-test',
            'price' => 10,
            'duration_days' => 30,
            'max_championships' => 1,
            'is_active' => true,
        ]);

        Subscription::create([
            'user_id' => $organizer->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addDays(30)->toDateString(),
        ]);

        Championship::create([
            'user_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Campeonato 1',
            'season_year' => 2026,
            'status' => 'draft',
        ]);

        $service = app(SubscriptionQuotaService::class);
        $summary = $service->summary($organizer);

        $this->assertSame(1, $summary['max_championships']);
        $this->assertSame(1, $summary['current_championships']);
        $this->assertSame(0, $summary['remaining_championships']);
        $this->assertFalse($service->canCreateChampionship($organizer));
    }

    public function test_organizer_with_room_can_create(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-test',
            'price' => 50,
            'duration_days' => 90,
            'max_championships' => 3,
            'is_active' => true,
        ]);

        Subscription::create([
            'user_id' => $organizer->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addDays(90)->toDateString(),
        ]);

        $service = app(SubscriptionQuotaService::class);
        $summary = $service->summary($organizer);

        $this->assertTrue($summary['has_active_subscription']);
        $this->assertSame(90, $summary['duration_days']);
        $this->assertTrue($service->canCreateChampionship($organizer));
    }
}
