<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Championship;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChampionshipQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_cannot_create_championship_when_quota_exceeded(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $category = Category::create(['name' => 'Senior']);
        $plan = SubscriptionPlan::create([
            'name' => 'Básico',
            'slug' => 'basico-api',
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
            'name' => 'Existente',
            'season_year' => 2026,
            'status' => 'draft',
        ]);

        $this->actingAs($organizer, 'sanctum')
            ->postJson('/api/championships', [
                'name' => 'Segundo',
                'category_id' => $category->id,
                'season_year' => 2026,
                'kart_modality' => 'rental',
            ])
            ->assertForbidden()
            ->assertJsonFragment(['message' => 'Tu plan «Básico» permite 1 campeonato(s) activo(s). Mejora tu plan para crear más.']);
    }

    public function test_my_subscription_includes_quota_summary(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $plan = SubscriptionPlan::create([
            'name' => 'Pro',
            'slug' => 'pro-api',
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

        $this->actingAs($organizer, 'sanctum')
            ->getJson('/api/my/subscription')
            ->assertOk()
            ->assertJsonPath('quota.max_championships', 3)
            ->assertJsonPath('quota.duration_days', 90)
            ->assertJsonPath('quota.can_create_championship', true);
    }
}
