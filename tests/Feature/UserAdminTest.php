<?php

namespace Tests\Feature;

use App\Models\Fighter;
use App\Models\Tournament;
use App\Models\TournamentEntry;
use App\Models\User;
use App\Services\TournamentService;
use Database\Seeders\JutsuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_index_shows_only_real_players_by_default(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $realUser = User::factory()->create([
            'username' => 'NarutoUzumaki',
            'role' => User::ROLE_PLAYER,
            'is_bot' => false,
        ]);

        $botUser = User::factory()->create([
            'username' => 'Bot_123_abcd',
            'email' => 'Bot_123_abcd@arena.local',
            'role' => User::ROLE_PLAYER,
            'is_bot' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('NarutoUzumaki');
        $response->assertDontSee('Bot_123_abcd');
        $response->assertSee('Joueurs Réels');
        $response->assertSee('Bots IA');
    }

    public function test_admin_user_index_can_filter_bots_and_search(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $realUser = User::factory()->create([
            'username' => 'SasukeUchiha',
            'role' => User::ROLE_PLAYER,
            'is_bot' => false,
        ]);

        $botUser = User::factory()->create([
            'username' => 'Bot_999_xyz',
            'email' => 'Bot_999_xyz@arena.local',
            'role' => User::ROLE_PLAYER,
            'is_bot' => true,
        ]);

        // Filter by bots
        $botsResponse = $this->actingAs($admin)->get(route('admin.users.index', ['type' => 'bots']));
        $botsResponse->assertStatus(200);
        $botsResponse->assertSee('Bot_999_xyz');
        $botsResponse->assertDontSee('SasukeUchiha');

        // Search query
        $searchResponse = $this->actingAs($admin)->get(route('admin.users.index', ['type' => 'all', 'q' => 'Sasuke']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('SasukeUchiha');
        $searchResponse->assertDontSee('Bot_999_xyz');
    }

    public function test_admin_can_purge_bots(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $realUser = User::factory()->create([
            'username' => 'SakuraHaruno',
            'role' => User::ROLE_PLAYER,
            'is_bot' => false,
        ]);

        $bot1 = User::factory()->create(['username' => 'Bot_111', 'email' => 'bot1@arena.local', 'is_bot' => true]);
        $bot2 = User::factory()->create(['username' => 'Bot_222', 'email' => 'bot2@arena.local', 'is_bot' => true]);

        $this->assertEquals(2, User::bots()->count());

        $response = $this->actingAs($admin)->delete(route('admin.users.purge-bots'));

        $response->assertRedirect(route('admin.users.index', ['type' => 'real']));
        $this->assertEquals(0, User::bots()->count());
        $this->assertDatabaseHas('users', ['id' => $realUser->id]);
    }

    public function test_tournament_entries_distinguish_bots_from_humans(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $tournament = Tournament::create([
            'title' => 'Examen Chūnin Test',
            'status' => Tournament::STATUS_REGISTRATION,
        ]);

        $humanUser = User::factory()->create(['is_bot' => false]);
        $humanFighter = Fighter::factory()->create(['user_id' => $humanUser->id, 'is_bot' => false]);

        $botUser = User::factory()->create(['is_bot' => true]);
        $botFighter = Fighter::factory()->create(['user_id' => $botUser->id, 'is_bot' => true]);

        $entryHuman = TournamentEntry::create([
            'tournament_id' => $tournament->id,
            'fighter_id' => $humanFighter->id,
        ]);

        $entryBot = TournamentEntry::create([
            'tournament_id' => $tournament->id,
            'fighter_id' => $botFighter->id,
        ]);

        $this->assertFalse($entryHuman->is_bot);
        $this->assertTrue($entryBot->is_bot);

        // Edit page must show "1 Humains / 1 Bots"
        $response = $this->actingAs($admin)->get(route('admin.tournaments.edit', $tournament));
        $response->assertStatus(200);
        $response->assertSee('1 Humains / 1 Bots');
    }

    public function test_fill_bots_creates_bots_with_is_bot_flag(): void
    {
        $this->seed(JutsuSeeder::class);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $tournament = Tournament::create([
            'title' => 'Tournoi de Remplissage',
            'status' => Tournament::STATUS_REGISTRATION,
        ]);

        // Add 1 human
        $humanUser = User::factory()->create(['is_bot' => false]);
        $humanFighter = Fighter::factory()->create(['user_id' => $humanUser->id, 'is_bot' => false]);
        app(TournamentService::class)->registerFighter($tournament, $humanFighter);

        // Fill remaining 7 slots with bots
        $response = $this->actingAs($admin)->post(route('admin.tournaments.fill-bots', $tournament));
        $response->assertRedirect();

        $tournament->refresh();
        $this->assertEquals(8, $tournament->entries()->count());

        $botEntries = $tournament->entries->filter(fn ($e) => $e->is_bot);
        $humanEntries = $tournament->entries->filter(fn ($e) => ! $e->is_bot);

        $this->assertEquals(7, $botEntries->count());
        $this->assertEquals(1, $humanEntries->count());

        // Visit edit page
        $editResponse = $this->actingAs($admin)->get(route('admin.tournaments.edit', $tournament));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('1 Humains / 7 Bots');
    }
}
