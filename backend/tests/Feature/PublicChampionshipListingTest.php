<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Championship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicChampionshipListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_excludes_draft_championships_by_default(): void
    {
        $organizer = User::factory()->create(['role' => 'organizer']);
        $category = Category::create(['name' => 'Senior']);

        Championship::create([
            'user_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Campeonato Publicado',
            'season_year' => 2026,
            'status' => 'published',
        ]);

        Championship::create([
            'user_id' => $organizer->id,
            'category_id' => $category->id,
            'name' => 'Campeonato Borrador',
            'season_year' => 2026,
            'status' => 'draft',
        ]);

        $response = $this->getJson('/api/championships');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();

        $this->assertContains('Campeonato Publicado', $names);
        $this->assertNotContains('Campeonato Borrador', $names);
    }
}
