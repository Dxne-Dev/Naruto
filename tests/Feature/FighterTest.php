<?php

namespace Tests\Feature;

use App\Models\Fighter;
use App\Models\User;
use Database\Seeders\JutsuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FighterTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_can_create_a_single_shinobi_via_destiny_wheels(): void
    {
        $this->seed(JutsuSeeder::class);

        $user = User::factory()->create(['wheel_tokens' => 3]);

        // 1. Roll candidate endpoint
        $this->actingAs($user)
            ->getJson(route('fighters.roll', ['clan' => 'Uchiha', 'element' => 'Katon']))
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'candidate' => [
                    'name_suggestion',
                    'clan',
                    'village',
                    'primary_element',
                    'specialization',
                    'passive_description',
                    'stats' => ['stat_nin', 'stat_tai', 'stat_gen', 'stat_int', 'stat_str', 'stat_spd', 'stat_end', 'stat_sea'],
                    'jutsus',
                ],
            ]);

        // 2. Store shinobi
        $response = $this->actingAs($user)->post(route('fighters.store'), [
            'name' => 'Sasuke Uchiha',
            'clan' => 'Uchiha',
            'village' => 'Konoha',
            'primary_element' => 'Katon',
            'secondary_element' => null,
            'specialization' => 'Ninjutsu',
            'stat_nin' => 3.5,
            'stat_tai' => 3.0,
            'stat_gen' => 3.0,
            'stat_int' => 3.0,
            'stat_str' => 2.5,
            'stat_spd' => 3.5,
            'stat_end' => 2.5,
            'stat_sea' => 3.0,
        ]);

        $response->assertRedirect(route('fighters.index'));
        $this->assertDatabaseHas('fighters', [
            'user_id' => $user->id,
            'name' => 'Sasuke Uchiha',
            'clan' => 'Uchiha',
            'level' => 1,
            'xp' => 0,
        ]);

        $fighter = Fighter::where('user_id', $user->id)->first();
        $this->assertGreaterThan(0, $fighter->jutsus()->count());

        // 3. Prevent duplicate active shinobi creation
        $dupResponse = $this->actingAs($user)->post(route('fighters.store'), [
            'name' => 'Sasuke Clone',
            'clan' => 'Uchiha',
            'village' => 'Konoha',
            'primary_element' => 'Katon',
            'specialization' => 'Ninjutsu',
            'stat_nin' => 3.0,
            'stat_tai' => 3.0,
            'stat_gen' => 3.0,
            'stat_int' => 3.0,
            'stat_str' => 2.5,
            'stat_spd' => 3.5,
            'stat_end' => 2.5,
            'stat_sea' => 3.0,
        ]);

        $dupResponse->assertRedirect(route('fighters.index'));
        $this->assertEquals(1, Fighter::where('user_id', $user->id)->count());
    }

    public function test_player_can_spend_wheel_tokens_in_shop(): void
    {
        $this->seed(JutsuSeeder::class);

        $user = User::factory()->create(['wheel_tokens' => 5]);
        $fighter = Fighter::create([
            'user_id' => $user->id,
            'name' => 'Shikamaru Nara',
            'archetype' => 'Hiden',
            'clan' => 'Nara',
            'village' => 'Konoha',
            'ninja_rank' => 'Genin',
            'primary_element' => 'Suiton',
            'specialization' => 'Hiden',
            'stat_nin' => 3.0,
            'stat_tai' => 2.0,
            'stat_gen' => 2.0,
            'stat_int' => 4.5,
            'stat_str' => 2.0,
            'stat_spd' => 2.5,
            'stat_end' => 2.5,
            'stat_sea' => 3.0,
            'power' => 10,
            'speed' => 12,
            'defense' => 10,
            'control' => 15,
            'special_power' => 'Ombre',
            'talent' => 'Ombre',
            'level' => 1,
            'xp' => 0,
        ]);

        // Spend 1 token for secondary element
        $this->actingAs($user)
            ->post(route('fighters.shop', $fighter), ['action' => 'reroll_element'])
            ->assertRedirect();

        $user->refresh();
        $fighter->refresh();

        $this->assertEquals(4, $user->wheel_tokens);
        $this->assertNotNull($fighter->secondary_element);
    }
}
