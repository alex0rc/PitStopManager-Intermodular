<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'piloto@test.com',
            'password' => 'password',
            'role' => 'pilot',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'piloto@test.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']]);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'piloto@test.com']);

        $this->postJson('/api/login', [
            'email' => 'piloto@test.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJson(['message' => 'Credenciales incorrectas.']);
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@test.com',
            'password' => 'password',
            'is_active' => false,
        ]);

        $this->postJson('/api/login', [
            'email' => 'inactive@test.com',
            'password' => 'password',
        ])
            ->assertForbidden()
            ->assertJson(['message' => 'Tu cuenta está desactivada.']);
    }

    public function test_inactive_user_token_is_rejected(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('test')->plainTextToken;

        $user->update(['is_active' => false]);

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertForbidden()
            ->assertJson(['message' => 'Tu cuenta está desactivada. Contacta con el administrador.']);
    }
}
