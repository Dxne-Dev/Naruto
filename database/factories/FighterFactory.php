<?php

namespace Database\Factories;

use App\Models\Fighter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fighter>
 */
class FighterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'archetype' => 'Ninjutsu',
            'clan' => 'Uzumaki',
            'village' => 'Konoha',
            'ninja_rank' => 'Genin',
            'primary_element' => 'Fūton',
            'secondary_element' => null,
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
            'portrait_path' => 'portraits/default.png',
            'level' => 1,
            'xp' => 0,
        ];
    }
}
