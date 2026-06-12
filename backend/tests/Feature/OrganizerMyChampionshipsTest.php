<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Championship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerMyChampionshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_sees_only_own_championships(): void
    {
        $carlos = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $maria = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $category = Category::create(['name' => 'Senior']);

        Championship::create([
            'user_id' => $carlos->id,
            'category_id' => $category->id,
            'name' => 'Liga Carlos',
            'season_year' => 2026,
            'status' => 'published',
        ]);

        Championship::create([
            'user_id' => $maria->id,
            'category_id' => $category->id,
            'name' => 'Liga Maria',
            'season_year' => 2026,
            'status' => 'published',
        ]);

        $response = $this->actingAs($carlos, 'sanctum')
            ->getJson('/api/my/championships');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Liga Carlos');
        $response->assertJsonPath('meta.total', 1);
    }

    public function test_pilot_cannot_access_my_championships(): void
    {
        $pilot = User::factory()->create(['role' => 'pilot', 'is_active' => true]);

        $this->actingAs($pilot, 'sanctum')
            ->getJson('/api/my/championships')
            ->assertForbidden();
    }
}
