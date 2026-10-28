<?php

namespace Tests\Feature;

use App\Models\BattleLog;
use App\Models\Fighter;
use App\Models\Friendship;
use App\Models\User;
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
}
