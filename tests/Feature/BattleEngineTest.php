<?php

namespace Tests\Feature;

use App\Models\Fighter;
use App\Models\User;
use App\Services\BattleEngine;
use Database\Seeders\JutsuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BattleEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_battle_engine_produces_deterministic_results_with_logs_and_naruto_rules(): void
    {
        $this->seed(JutsuSeeder::class);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $fighterA = Fighter::create([
            'user_id' => $userA->id,
            'name' => 'Sasuke Uchiha',
            'archetype' => 'Ninjutsu',
            'clan' => 'Uchiha',
            'village' => 'Konoha',
            'ninja_rank' => 'Genin',
            'primary_element' => 'Katon',
            'specialization' => 'Ninjutsu',
            'stat_nin' => 3.5,
            'stat_tai' => 3.0,
            'stat_gen' => 3.0,
            'stat_int' => 3.0,
            'stat_str' => 2.5,
            'stat_spd' => 3.5,
            'stat_end' => 2.5,
            'stat_sea' => 3.0,
            'power' => 15,
            'speed' => 18,
            'defense' => 12,
            'control' => 16,
            'special_power' => 'Chidori',
            'talent' => 'Sharingan',
        ]);

        $fighterB = Fighter::create([
            'user_id' => $userB->id,
            'name' => 'Naruto Uzumaki',
            'archetype' => 'Ninjutsu',
            'clan' => 'Uzumaki',
            'village' => 'Konoha',
            'ninja_rank' => 'Genin',
            'primary_element' => 'Fūton',
            'specialization' => 'Ninjutsu',
            'stat_nin' => 3.0,
            'stat_tai' => 3.0,
            'stat_gen' => 1.5,
            'stat_int' => 2.0,
            'stat_str' => 3.0,
            'stat_spd' => 3.0,
            'stat_end' => 4.0,
            'stat_sea' => 2.0,
            'power' => 18,
            'speed' => 15,
            'defense' => 15,
            'control' => 14,
            'special_power' => 'Rasengan',
            'talent' => 'Force Vitale',
        ]);

        $engine = app(BattleEngine::class);

        $seed = 'fixed-naruto-seed-456';
        $battle1 = $engine->simulate($fighterA, $fighterB, null, $seed);
        $battle2 = $engine->simulate($fighterA, $fighterB, null, $seed);

        // Deterministic: Same winner and final HPs for exact same seed
        $this->assertEquals($battle1->winner_id, $battle2->winner_id);
        $this->assertEquals($battle1->fighter_a_final_hp, $battle2->fighter_a_final_hp);
        $this->assertEquals($battle1->fighter_b_final_hp, $battle2->fighter_b_final_hp);
        $this->assertNotEmpty($battle1->logs);
        $this->assertNotNull($battle1->winner_id);

        // Check elemental advantage formula: Katon vs Fūton = 1.25 multiplier
        $multiplier = $engine->getElementMultiplier('Katon', 'Fūton');
        $this->assertEquals(1.25, $multiplier);

        $disadvantage = $engine->getElementMultiplier('Katon', 'Suiton');
        $this->assertEquals(0.75, $disadvantage);
    }
}
