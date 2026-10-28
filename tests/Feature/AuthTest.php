<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_unique_username(): void
    {
        $response = $this->post(route('register.post'), [
            'username' => 'Goku_99',
            'name' => 'Son Goku',
            'email' => 'goku@dbz.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('onboarding.index'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'Goku_99',
            'email' => 'goku@dbz.com',
            'role' => User::ROLE_PLAYER,
        ]);
    }

    public function test_user_can_login_with_username_or_email(): void
    {
        $user = User::factory()->create([
            'username' => 'vegeta',
            'email' => 'vegeta@dbz.com',
            'password' => bcrypt('prince123'),
        ]);

        // Login with username
        $response = $this->post(route('login.post'), [
            'login' => 'vegeta',
            'password' => 'prince123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // Login with email
        $responseEmail = $this->post(route('login.post'), [
            'login' => 'vegeta@dbz.com',
            'password' => 'prince123',
        ]);

        $responseEmail->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_game_master_middleware_blocks_normal_players(): void
    {
        $player = User::factory()->create(['role' => User::ROLE_PLAYER]);
        $gm = User::factory()->gameMaster()->create();

        // Player blocked
        $this->actingAs($player)
            ->get(route('admin.tournaments.index'))
            ->assertStatus(403);

        // GM allowed
        $this->actingAs($gm)
            ->get(route('admin.tournaments.index'))
            ->assertStatus(200);
    }
}
