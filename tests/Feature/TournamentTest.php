<?php

namespace Tests\Feature;

use App\Models\Fighter;
use App\Models\MatchGame;
use App\Models\Tournament;
use App\Models\TournamentEntry;
use App\Models\User;
use App\Services\BattleEngine;
use App\Services\TournamentService;
use Database\Seeders\JutsuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_8_player_tournament_lifecycle_and_xp_distribution(): void
    {
        $this->seed(JutsuSeeder::class);

        $tournamentService = app(TournamentService::class);

        $tournament = Tournament::create([
            'title' => 'Examen Chūnin de Konoha #1',
            'status' => Tournament::STATUS_REGISTRATION,
        ]);

        $fighters = [];
        for ($i = 1; $i <= 8; $i++) {
            $user = User::factory()->create(['username' => 'shinobi_'.$i, 'wheel_tokens' => 1]);
            $fighter = Fighter::create([
                'user_id' => $user->id,
                'name' => 'Shinobi #'.$i,
                'archetype' => 'Ninjutsu',
                'clan' => 'Uzumaki',
                'village' => 'Konoha',
                'ninja_rank' => 'Genin',
                'primary_element' => 'Fūton',
                'specialization' => 'Ninjutsu',
                'stat_nin' => 2.5,
                'stat_tai' => 2.5,
                'stat_gen' => 2.0,
                'stat_int' => 2.5,
                'stat_str' => 2.5,
                'stat_spd' => 2.5,
                'stat_end' => 3.0,
                'stat_sea' => 2.0,
                'power' => 15,
                'speed' => 15,
                'defense' => 10,
                'control' => 10,
                'special_power' => 'Rasengan',
                'talent' => 'Force Vitale',
                'level' => 1,
                'xp' => 0,
            ]);
            $fighters[] = $fighter;
            $tournamentService->registerFighter($tournament, $fighter);
        }

        // Check 8 registered
        $this->assertEquals(8, $tournament->entries()->count());

        // Lock & generate bracket
        $tournamentService->lockAndGenerateBracket($tournament);

        $this->assertEquals(Tournament::STATUS_LOCKED, $tournament->status);
        $this->assertEquals(7, $tournament->matches()->count()); // 4 QF + 2 SF + 1 Final

        // Resolve all rounds
        $tournamentService->resolveAllPendingMatches($tournament);

        $tournament->refresh();
        $this->assertEquals(Tournament::STATUS_COMPLETED, $tournament->status);

        // Final match must have a champion
        $finalMatch = $tournament->matches()->where('round', 3)->first();
        $this->assertEquals(MatchGame::STATUS_COMPLETED, $finalMatch->status);
        $this->assertNotNull($finalMatch->winner_id);

        // Champion must have final_position = 1
        $championEntry = $tournament->entries()->where('fighter_id', $finalMatch->winner_id)->first();
        $this->assertEquals(1, $championEntry->final_position);

        // Check that XP was awarded and all participants gained XP
        foreach ($fighters as $f) {
            $f->refresh();
            $this->assertGreaterThan(0, $f->xp);
        }

        // Check champion received tournament match wins + 500 XP bonus + 1 wheel token
        $championFighter = Fighter::find($finalMatch->winner_id);
        $this->assertGreaterThanOrEqual(500, $championFighter->xp);
        $this->assertGreaterThanOrEqual(2, $championFighter->user->wheel_tokens);
    }

    public function test_tournament_device_fingerprint_quota_enforcement(): void
    {
        $tournamentService = app(TournamentService::class);

        $t1 = Tournament::create(['title' => 'Tournoi 1', 'status' => Tournament::STATUS_REGISTRATION]);
        $t2 = Tournament::create(['title' => 'Tournoi 2', 'status' => Tournament::STATUS_REGISTRATION]);
        $t3 = Tournament::create(['title' => 'Tournoi 3', 'status' => Tournament::STATUS_REGISTRATION]);

        $ip = '105.235.10.12';
        $deviceA_UA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Mobile NinjaApp';
        $deviceB_UA = 'Mozilla/5.0 (Linux; Android 14) Chrome/120.0 Mobile';

        $user1 = User::factory()->create(['has_paid_microtransaction' => false]);
        $fighter1 = Fighter::factory()->create(['user_id' => $user1->id]);

        $this->assertTrue($user1->canEnterTournament($ip, $deviceA_UA));

        // First registration on Device A
        $tournamentService->registerFighter($t1, $fighter1, $ip, null, $deviceA_UA);
        $this->assertTrue($user1->canEnterTournament($ip, $deviceA_UA));

        // Second registration on Device A
        $tournamentService->registerFighter($t2, $fighter1, $ip, null, $deviceA_UA);

        // Third registration should be blocked on Device A
        $this->assertFalse($user1->canEnterTournament($ip, $deviceA_UA));

        // But Device B sharing the exact same IP (CGNAT / mobile tower) is NOT blocked!
        $user2 = User::factory()->create(['has_paid_microtransaction' => false]);
        $fighter2 = Fighter::factory()->create(['user_id' => $user2->id]);
        $this->assertTrue($user2->canEnterTournament($ip, $deviceB_UA));

        // Paid user is never blocked on Device A
        $paidUser = User::factory()->create(['has_paid_microtransaction' => true]);
        $this->assertTrue($paidUser->canEnterTournament($ip, $deviceA_UA));
    }

    public function test_quota_persists_across_account_deletion_via_device_fingerprint(): void
    {
        $tournamentService = app(TournamentService::class);
        $t1 = Tournament::create(['title' => 'Tournoi Delta 1', 'status' => Tournament::STATUS_REGISTRATION]);
        $t2 = Tournament::create(['title' => 'Tournoi Delta 2', 'status' => Tournament::STATUS_REGISTRATION]);

        $ip = '41.202.219.5';
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/122.0.0.0';

        $user = User::factory()->create([
            'password' => bcrypt('secret123'),
            'has_paid_microtransaction' => false,
        ]);
        $fighter = Fighter::factory()->create(['user_id' => $user->id]);

        // Register once
        $tournamentService->registerFighter($t1, $fighter, $ip, null, $userAgent);

        // Delete account
        $this->actingAs($user)
            ->withServerVariables(['REMOTE_ADDR' => $ip, 'HTTP_USER_AGENT' => $userAgent])
            ->delete(route('profile.destroy'), [
                'delete_confirmation_password' => 'secret123',
            ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        // New account created on the same device
        $newUser = User::factory()->create(['has_paid_microtransaction' => false]);
        $newFighter = Fighter::factory()->create(['user_id' => $newUser->id]);

        // Can register once more (total = 2)
        $this->assertTrue($newUser->canEnterTournament($ip, $userAgent));
        $tournamentService->registerFighter($t2, $newFighter, $ip, null, $userAgent);

        // Now quota is exhausted (2 reached)
        $this->assertFalse($newUser->canEnterTournament($ip, $userAgent));
    }

    public function test_auto_fill_tournament_bots_command_at_t_minus_5_minutes(): void
    {
        $this->seed(JutsuSeeder::class);

        $tournament = Tournament::create([
            'title' => 'Tournoi de Sélection Chūnin',
            'status' => Tournament::STATUS_REGISTRATION,
            'registration_opens_at' => now()->subHours(2),
            'registration_closes_at' => now()->addMinutes(3), // T-3 minutes (less than threshold of 5)
            'starts_at' => now()->addMinutes(10),
        ]);

        $tournamentService = app(TournamentService::class);

        // 2 real players registered
        for ($i = 1; $i <= 2; $i++) {
            $user = User::factory()->create();
            $fighter = Fighter::factory()->create(['user_id' => $user->id, 'is_bot' => false]);
            $tournamentService->registerFighter($tournament, $fighter);
        }

        $this->assertEquals(2, $tournament->entries()->count());

        // Run auto-fill command
        $this->artisan('tournaments:auto-fill-bots')
            ->expectsOutputToContain('Injection de 6 bots PNJ...')
            ->assertSuccessful();

        $this->assertEquals(8, $tournament->entries()->count());
        $this->assertEquals(6, Fighter::where('is_bot', true)->count());

        // Check that bots have jutsus assigned
        $bot = Fighter::where('is_bot', true)->first();
        $this->assertNotNull($bot);
        $this->assertGreaterThan(0, $bot->jutsus()->count());

        // Test with lock option
        $this->artisan('tournaments:auto-fill-bots', ['--lock' => true])
            ->assertSuccessful();

        $tournament->refresh();
        $this->assertEquals(Tournament::STATUS_LOCKED, $tournament->status);
        $this->assertEquals(7, $tournament->matches()->count());
    }

    public function test_hall_of_fame_displays_champions_and_archives(): void
    {
        $this->seed(JutsuSeeder::class);

        $user = User::factory()->create();
        $fighter = Fighter::factory()->create(['user_id' => $user->id]);

        $tournament = Tournament::create([
            'title' => 'Examen Chūnin Antique',
            'status' => Tournament::STATUS_COMPLETED,
        ]);

        TournamentEntry::create([
            'tournament_id' => $tournament->id,
            'fighter_id' => $fighter->id,
            'final_position' => 1,
            'registered_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('tournaments.hall-of-fame'));
        $response->assertStatus(200);
        $response->assertSee('Panthéon des Champions');
        $response->assertSee($fighter->name);
        $response->assertSee('Examen Chūnin Antique');
    }

    public function test_payment_checkout_and_pass_activation(): void
    {
        $user = User::factory()->create(['has_paid_microtransaction' => false]);
        $fighter = Fighter::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('payment.checkout'))
            ->assertStatus(200)
            ->assertSee('Pass Shinobi Illimité');

        $this->actingAs($user)
            ->post(route('payment.process'), [
                'payment_method' => 'wave',
                'phone_number' => '+225 07 11 22 33 44',
            ])
            ->assertRedirect(route('tournaments.open-list'));

        $user->refresh();
        $this->assertTrue($user->has_paid_microtransaction);
    }

    public function test_tactical_stance_and_strategy_effects_in_combat(): void
    {
        $this->seed(JutsuSeeder::class);

        $userA = User::factory()->create();
        $fighterA = Fighter::factory()->create([
            'user_id' => $userA->id,
            'strategy_stance' => 'offensive',
            'jutsu_priority_type' => 'highest_power',
        ]);

        $userB = User::factory()->create();
        $fighterB = Fighter::factory()->create([
            'user_id' => $userB->id,
            'strategy_stance' => 'counter',
            'jutsu_priority_type' => 'lowest_chakra',
        ]);

        $battleEngine = app(BattleEngine::class);
        $battle = $battleEngine->simulate($fighterA, $fighterB);

        $this->assertNotNull($battle->winner_id);
        $this->assertGreaterThan(0, $battle->logs()->count());
    }
}
