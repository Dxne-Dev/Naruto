<?php

namespace Database\Seeders;

use App\Models\Fighter;
use App\Models\Tournament;
use App\Models\User;
use App\Services\TournamentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(TournamentService $tournamentService): void
    {
        // 1. Admin account (Dxne)
        $admin = User::updateOrCreate(
            ['username' => 'Dxne'],
            [
                'name' => 'Dxne Admin',
                'email' => 'dxne@otaku-arena.com',
                'password' => Hash::make('Secret123'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        // 2. Demo Player 1
        $player1 = User::create([
            'username' => 'sasuke',
            'name' => 'Sasuke Uchiha',
            'email' => 'sasuke@otaku-arena.com',
            'password' => Hash::make('secret123'),
            'role' => User::ROLE_PLAYER,
        ]);

        $fighter1 = Fighter::create([
            'user_id' => $player1->id,
            'name' => 'Sasuke des Ombres',
            'archetype' => 'Assassin de l\'Ombre',
            'power' => 16,
            'speed' => 20,
            'defense' => 6,
            'control' => 8,
            'special_power' => 'Flammes Noires d\'Amaterasu',
            'talent' => 'Contre-Éclair',
            'level' => 2,
            'xp' => 120,
        ]);

        // 3. Demo Tournament
        $tournament = Tournament::create([
            'title' => 'Tournoi des Jonins — Édition #1',
            'status' => Tournament::STATUS_REGISTRATION,
            'registration_opens_at' => now()->subHours(2),
            'registration_closes_at' => now()->addDays(2),
            'starts_at' => now()->addDays(2)->addHour(),
        ]);

        // Register Sasuke
        $tournamentService->registerFighter($tournament, $fighter1);

        // 4. Create 7 additional fighters
        $archetypesData = [
            ['name' => 'Naruto le Fougueux', 'arch' => 'Guerrier Shonen', 'p' => 18, 's' => 13, 'd' => 13, 'c' => 6, 'power' => 'Rasengan Céleste', 'talent' => 'Volonté du Héros'],
            ['name' => 'Kakashi du Sharingan', 'arch' => 'Moine du Ki', 'p' => 14, 's' => 15, 'd' => 11, 'c' => 10, 'power' => 'Lame du Dragon de Foudre', 'talent' => 'Maîtrise Parfaite'],
            ['name' => 'Gaara du Désert', 'arch' => 'Titan Protecteur', 'p' => 12, 's' => 6, 'd' => 22, 'c' => 10, 'power' => 'Frappe Sismique Suprême', 'talent' => 'Blindage d\'Acier'],
            ['name' => 'Itachi le Mirage', 'arch' => 'Mage Élémentaire', 'p' => 17, 's' => 8, 'd' => 8, 'c' => 17, 'power' => 'Jugement Astral', 'talent' => 'Flux Inépuisable'],
            ['name' => 'Rock Lee le Bolide', 'arch' => 'Guerrier Shonen', 'p' => 15, 's' => 21, 'd' => 9, 'c' => 5, 'power' => 'Tempête de Mille Lames', 'talent' => 'Coup de Sang'],
            ['name' => 'Pain le Destructeur', 'arch' => 'Cyborg de Combat', 'p' => 16, 's' => 10, 'd' => 15, 'c' => 9, 'power' => 'Vortex Dimensionnel', 'talent' => 'Blindage d\'Acier'],
            ['name' => 'Minato l\'Éclair', 'arch' => 'Assassin de l\'Ombre', 'p' => 14, 's' => 22, 'd' => 6, 'c' => 8, 'power' => 'Rayon Plasma Concentré', 'talent' => 'Contre-Éclair'],
        ];

        foreach ($archetypesData as $idx => $data) {
            $u = User::create([
                'username' => 'ninja_'.($idx + 1),
                'name' => $data['name'],
                'email' => 'ninja'.($idx + 1).'@otaku-arena.com',
                'password' => Hash::make('secret123'),
                'role' => User::ROLE_PLAYER,
            ]);

            $f = Fighter::create([
                'user_id' => $u->id,
                'name' => $data['name'],
                'archetype' => $data['arch'],
                'power' => $data['p'],
                'speed' => $data['s'],
                'defense' => $data['d'],
                'control' => $data['c'],
                'special_power' => $data['power'],
                'talent' => $data['talent'],
                'level' => 1,
                'xp' => 40,
            ]);

            $tournamentService->registerFighter($tournament, $f);
        }
    }
}
