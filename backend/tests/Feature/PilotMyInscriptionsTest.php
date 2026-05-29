<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Championship;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PilotMyInscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_pilot_receives_own_inscriptions_as_json_array(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer', 'is_active' => true]);
        $pilot = User::factory()->create(['role' => 'pilot', 'is_active' => true]);
        $category = Category::create(['name' => 'Senior']);

        $championship = Championship::create([
            'user_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Test Championship',
            'season_year' => 2026,
            'status' => 'published',
        ]);

        Inscription::create([
            'user_id' => $pilot->id,
            'championship_id' => $championship->id,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($pilot, 'sanctum')
            ->getJson('/api/my/inscriptions');

        $response->assertOk();
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'user_id' => $pilot->id,
            'championship_id' => $championship->id,
            'status' => 'confirmed',
        ]);
    }
}
