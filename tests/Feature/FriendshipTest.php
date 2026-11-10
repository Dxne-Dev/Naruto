<?php

namespace Tests\Feature;

use App\Models\Battle;
use App\Models\BattleLog;
use App\Models\Challenge;
use App\Models\Fighter;
use App\Models\Friendship;
use App\Models\User;
use App\Services\XpService;
use Database\Seeders\JutsuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FriendshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(JutsuSeeder::class);
    }

    private function createFighter(User $user, string $name = 'Sasuke Test'): Fighter
    {
        return Fighter::create([
            'user_id' => $user->id,
            'name' => $name,
            'archetype' => 'Ninjutsu',
            'village' => 'Konoha',
            'clan' => 'Uchiha',
            'primary_element' => 'Katon',
            'specialization' => 'Ninjutsu',
            'ninja_rank' => 'Genin',
            'stat_nin' => 4.0,
            'stat_tai' => 3.0,
            'stat_gen' => 3.5,
            'stat_int' => 3.5,
            'stat_str' => 3.0,
            'stat_spd' => 4.0,
            'stat_end' => 3.5,
            'stat_sea' => 3.0,
            'power' => 15,
            'speed' => 18,
            'defense' => 12,
            'control' => 16,
            'special_power' => 'Chidori',
            'talent' => 'Sharingan',
            'level' => 1,
            'xp' => 0,
            'strategy_stance' => 'aggressive',
            'jutsu_priority_type' => 'highest_power',
        ]);
    }

    public function test_user_can_send_friend_request(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $response = $this->actingAs($userA)->post(route('friends.request'), [
            'friend_id' => $userB->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('friendships', [
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => Friendship::STATUS_PENDING,
        ]);
    }

    public function test_user_can_accept_friend_request(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $friendship = Friendship::create([
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => Friendship::STATUS_PENDING,
        ]);

        $response = $this->actingAs($userB)->post(route('friends.accept', $friendship));

        $response->assertRedirect();
        $this->assertEquals(Friendship::STATUS_ACCEPTED, $friendship->fresh()->status);
        $this->assertTrue($userA->isFriendsWith($userB));
        $this->assertTrue($userB->isFriendsWith($userA));
    }

    public function test_user_can_decline_friend_request(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $friendship = Friendship::create([
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => Friendship::STATUS_PENDING,
        ]);

        $response = $this->actingAs($userB)->post(route('friends.decline', $friendship));

        $response->assertRedirect();
        $this->assertEquals(Friendship::STATUS_DECLINED, $friendship->fresh()->status);
        $this->assertFalse($userA->isFriendsWith($userB));
    }

    public function test_friends_can_challenge_each_other_in_1v1_duel(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->createFighter($userA, 'Naruto Uzumaki');
        $this->createFighter($userB, 'Sasuke Uchiha');

        Friendship::create([
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => Friendship::STATUS_ACCEPTED,
        ]);

        $response = $this->actingAs($userA)->post(route('friends.challenge', $userB));

        $response->assertRedirect();
        $challenge = Challenge::where('sender_id', $userA->id)->where('receiver_id', $userB->id)->first();
        $this->assertNotNull($challenge);
        $this->assertEquals(Challenge::STATUS_PENDING, $challenge->status);

        // Accept challenge
        $acceptResponse = $this->actingAs($userB)->post(route('friends.challenges.accept', $challenge));
        $acceptResponse->assertRedirect();
        $this->assertDatabaseCount('battles', 1);
        $this->assertTrue(BattleLog::count() > 0);
    }

    public function test_non_friends_cannot_challenge_each_other(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->createFighter($userA, 'Naruto Uzumaki');
        $this->createFighter($userB, 'Sasuke Uchiha');

        // Not friends
        $response = $this->actingAs($userA)->post(route('friends.challenge', $userB));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('battles', 0);
    }

    public function test_friend_duel_win_awards_5_xp(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $fighterA = $this->createFighter($userA, 'Naruto');
        $fighterB = $this->createFighter($userB, 'Sasuke');

        Friendship::create([
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => Friendship::STATUS_ACCEPTED,
        ]);

        $this->actingAs($userA)->post(route('friends.challenge', $userB));
        $challenge = Challenge::first();

        $this->actingAs($userB)->post(route('friends.challenges.accept', $challenge));

        $battle = Battle::first();
        $this->assertNotNull($battle);

        $winnerFighter = Fighter::find($battle->winner_id);
        $loserFighter = $winnerFighter->id === $fighterA->id ? $fighterB : $fighterA;

        // Winner gets 5 XP
        $this->assertEquals(5, $winnerFighter->xp);
        $this->assertDatabaseHas('xp_transactions', [
            'fighter_id' => $winnerFighter->id,
            'amount' => 5,
            'reference_type' => 'friend_duel',
            'reference_id' => $battle->id,
        ]);

        // Loser gets 0 XP
        $this->assertEquals(0, $loserFighter->xp);
    }

    public function test_friend_duel_has_48h_cooldown_per_friend(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $fighterA = $this->createFighter($userA, 'Naruto');
        $fighterB = $this->createFighter($userB, 'Sasuke');

        Friendship::create([
            'user_id' => $userA->id,
            'friend_id' => $userB->id,
            'status' => Friendship::STATUS_ACCEPTED,
        ]);

        // Duel 1
        $this->actingAs($userA)->post(route('friends.challenge', $userB));
        $challenge1 = Challenge::first();
        $this->actingAs($userB)->post(route('friends.challenges.accept', $challenge1));

        $battle1 = Battle::first();
        $winnerFighter = Fighter::find($battle1->winner_id);
        $this->assertEquals(5, $winnerFighter->xp);

        // Duel 2 within 48h against same friend
        $this->actingAs($userA)->post(route('friends.challenge', $userB));
        $challenge2 = Challenge::latest()->first();
        $this->actingAs($userB)->post(route('friends.challenges.accept', $challenge2));

        $battle2 = Battle::latest()->first();

        // If the same fighter wins, their XP remains 5 (no XP due to 48h cooldown)
        if ($battle2->winner_id === $winnerFighter->id) {
            $winnerFighter->refresh();
            $this->assertEquals(5, $winnerFighter->xp);
        }

        // Fast forward 49 hours into the future
        $this->travel(49)->hours();

        // Duel 3 after 48h cooldown
        $this->actingAs($userA)->post(route('friends.challenge', $userB));
        $challenge3 = Challenge::latest()->first();
        $this->actingAs($userB)->post(route('friends.challenges.accept', $challenge3));

        $battle3 = Battle::latest()->first();

        // Winner of duel 3 can earn 5 XP again
        $winner3 = Fighter::find($battle3->winner_id);
        $this->assertGreaterThanOrEqual(5, $winner3->xp);
    }

    public function test_friend_duel_respects_15_xp_daily_cap_across_multiple_friends(): void
    {
        $mainUser = User::factory()->create();
        $mainFighter = $this->createFighter($mainUser, 'Naruto');

        // Create 4 friends and award XP by simulating friend duel wins
        for ($i = 1; $i <= 4; $i++) {
            $friendUser = User::factory()->create();
            $friendFighter = $this->createFighter($friendUser, 'Friend_'.$i);

            Friendship::create([
                'user_id' => $mainUser->id,
                'friend_id' => $friendUser->id,
                'status' => Friendship::STATUS_ACCEPTED,
            ]);

            $battle = Battle::create([
                'fighter_a_id' => $mainFighter->id,
                'fighter_b_id' => $friendFighter->id,
                'winner_id' => $mainFighter->id,
                'fighter_a_snapshot' => ['name' => $mainFighter->name],
                'fighter_b_snapshot' => ['name' => $friendFighter->name],
                'fighter_a_final_hp' => 100,
                'fighter_b_final_hp' => 0,
                'rounds_count' => 3,
                'random_seed' => 12345,
            ]);

            $result = app(XpService::class)->awardFriendDuelWinXp($mainFighter, $friendFighter, $battle);

            if ($i <= 3) {
                // First 3 wins give 5 XP each = 15 XP
                $this->assertTrue($result['awarded']);
                $this->assertEquals(5, $result['amount']);
            } else {
                // 4th win hits the daily 15 XP cap
                $this->assertFalse($result['awarded']);
                $this->assertEquals(0, $result['amount']);
            }
        }

        $mainFighter->refresh();
        $this->assertEquals(15, $mainFighter->xp);
    }
}
