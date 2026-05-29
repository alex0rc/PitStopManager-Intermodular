<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Championship;
use App\Models\Circuit;
use App\Models\Inscription;
use App\Models\Race;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerInscriptionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_organizer_can_delete_inscription_from_championship(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $pilot = User::factory()->create(['role' => 'pilot']);
        $category = Category::create(['name' => 'Senior']);

        $championship = Championship::create([
            'user_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Test',
            'season_year' => 2026,
            'status' => 'published',
        ]);

        $inscription = Inscription::create([
            'user_id' => $pilot->id,
            'championship_id' => $championship->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($organizer, 'sanctum')
            ->deleteJson("/api/inscriptions/{$inscription->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('inscriptions', ['id' => $inscription->id]);
    }

    public function test_organizer_can_detach_pilot_from_race(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $pilot = User::factory()->create(['role' => 'pilot']);
        $category = Category::create(['name' => 'Senior']);

        $championship = Championship::create([
            'user_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Test',
            'season_year' => 2026,
            'status' => 'published',
        ]);

        $circuit = Circuit::create([
            'user_id' => $organizer->id,
            'name' => 'Circuit Test',
            'location' => 'Madrid',
            'status' => 'approved',
        ]);

        $race1 = Race::create([
            'championship_id' => $championship->id,
            'circuit_id' => $circuit->id,
            'name' => 'Carrera 1',
            'status' => 'scheduled',
            'scheduled_at' => now()->addWeek(),
        ]);

        $race2 = Race::create([
            'championship_id' => $championship->id,
            'circuit_id' => $circuit->id,
            'name' => 'Carrera 2',
            'status' => 'scheduled',
            'scheduled_at' => now()->addWeeks(2),
        ]);

        $inscription = Inscription::create([
            'user_id' => $pilot->id,
            'championship_id' => $championship->id,
            'status' => 'confirmed',
        ]);

        $inscription->races()->sync([$race1->id, $race2->id]);

        $this->actingAs($organizer, 'sanctum')
            ->deleteJson("/api/inscriptions/{$inscription->id}/races/{$race1->id}")
            ->assertOk();

        $inscription->refresh();
        $this->assertEquals([$race2->id], $inscription->races()->pluck('races.id')->all());
    }
}
