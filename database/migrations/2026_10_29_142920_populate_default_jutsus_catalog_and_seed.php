<?php

use App\Models\Fighter;
use App\Models\Jutsu;
use Database\Seeders\JutsuSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Seed or update the catalog of Jutsus
        $seeder = new JutsuSeeder;
        $seeder->run();

        // 2. Retroactively attach starter Jutsus to any existing fighters without jutsus
        $fightersWithoutJutsus = Fighter::whereDoesntHave('jutsus')->get();

        foreach ($fightersWithoutJutsus as $fighter) {
            $starterJutsuIds = collect();

            // Technique élémentaire
            if ($fighter->primary_element) {
                $elementalJutsu = Jutsu::where('element', $fighter->primary_element)
                    ->whereIn('rank', ['D', 'C', 'B'])
                    ->first();
                if ($elementalJutsu) {
                    $starterJutsuIds->push($elementalJutsu->id);
                }
            }

            // Taijutsu de base
            $basicTaijutsu = Jutsu::where('name', 'like', '%Tornade de Konoha%')->first();
            if ($basicTaijutsu) {
                $starterJutsuIds->push($basicTaijutsu->id);
            }

            // Technique de Clan
            if ($fighter->clan) {
                $clanJutsu = Jutsu::where('name', 'like', "%{$fighter->clan}%")->first();
                if ($clanJutsu) {
                    $starterJutsuIds->push($clanJutsu->id);
                }
            }

            // Fallback si moins de 2 jutsus
            if ($starterJutsuIds->count() < 2) {
                $fallbacks = Jutsu::whereIn('rank', ['D', 'C'])->take(3)->pluck('id');
                $starterJutsuIds = $starterJutsuIds->merge($fallbacks);
            }

            $fighter->jutsus()->syncWithoutDetaching($starterJutsuIds->unique());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse
    }
};
