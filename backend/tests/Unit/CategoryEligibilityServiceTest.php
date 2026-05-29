<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\PilotProfile;
use App\Models\User;
use App\Services\CategoryEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryEligibilityServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_pilot_within_age_range_is_eligible(): void
    {
        $user = User::factory()->create(['role' => 'pilot']);
        PilotProfile::create([
            'user_id' => $user->id,
            'birth_date' => now()->subYears(20)->toDateString(),
        ]);

        $category = Category::create([
            'name' => 'Senior',
            'min_age' => 15,
            'max_age' => 35,
        ]);

        $message = app(CategoryEligibilityService::class)->validateForCategory($user, $category);

        $this->assertNull($message);
    }

    public function test_pilot_too_young_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'pilot']);
        PilotProfile::create([
            'user_id' => $user->id,
            'birth_date' => now()->subYears(10)->toDateString(),
        ]);

        $category = Category::create([
            'name' => 'Senior',
            'min_age' => 15,
        ]);

        $message = app(CategoryEligibilityService::class)->validateForCategory($user, $category);

        $this->assertStringContainsString('15 años', $message);
    }
}
