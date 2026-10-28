<?php

namespace Database\Seeders;

use App\Models\Jutsu;
use Illuminate\Database\Seeder;

class JutsuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jutsus = [
            // Katon (Feu)
            [
                'name' => 'Katon : Boule de Feu Suprême',
                'type' => 'Ninjutsu',
                'element' => 'Katon',
                'base_power' => 35,
                'chakra_cost' => 40,
                'rank' => 'C',
                'clan_required' => null,
                'description' => 'Projette une gigantesque sphère de flammes destructrice.',
            ],
            [
                'name' => 'Katon : Feu du Dragon Flamboyant',
                'type' => 'Ninjutsu',
                'element' => 'Katon',
                'base_power' => 50,
                'chakra_cost' => 65,
                'rank' => 'B',
                'clan_required' => null,
                'description' => 'Un souffle ardent en forme de tête de dragon calcinant la cible.',
            ],

            // Fūton (Vent)
            [
                'name' => 'Fūton : Lame du Vent',
                'type' => 'Ninjutsu',
                'element' => 'Fūton',
                'base_power' => 32,
                'chakra_cost' => 35,
                'rank' => 'C',
                'clan_required' => null,
                'description' => 'Tranche l\'adversaire avec une pression d\'air sous vide invisible.',
            ],
            [
                'name' => 'Fūton : Rasenshuriken',
                'type' => 'Ninjutsu',
                'element' => 'Fūton',
                'base_power' => 70,
                'chakra_cost' => 90,
                'rank' => 'S',
                'clan_required' => null,
                'description' => 'Sphère tourbillonnante de vent tranchant le réseau de chakra au niveau cellulaire.',
            ],

            // Raiton (Foudre)
            [
                'name' => 'Raiton : Chidori (Les Mille Oiseaux)',
                'type' => 'Ninjutsu',
                'element' => 'Raiton',
                'base_power' => 55,
                'chakra_cost' => 60,
                'rank' => 'A',
                'clan_required' => null,
                'description' => 'Concentration extrême d\'électricité dans la main pour une perforation fulgurante.',
            ],
            [
                'name' => 'Raiton : Éclair Pourfendeur',
                'type' => 'Ninjutsu',
                'element' => 'Raiton',
                'base_power' => 38,
                'chakra_cost' => 45,
                'rank' => 'C',
                'clan_required' => null,
                'description' => 'Faisceau électrique transperçant l\'armure ennemie.',
            ],

            // Doton (Terre)
            [
                'name' => 'Doton : Mur de Terre Protecteur',
                'type' => 'Ninjutsu',
                'element' => 'Doton',
                'base_power' => 25,
                'chakra_cost' => 30,
                'rank' => 'C',
                'clan_required' => null,
                'description' => 'Érige un rempart de roche renforçant la défense et repoussant les attaques.',
            ],
            [
                'name' => 'Doton : Poing de Pierre Broyeur',
                'type' => 'Ninjutsu',
                'element' => 'Doton',
                'base_power' => 45,
                'chakra_cost' => 50,
                'rank' => 'B',
                'clan_required' => null,
                'description' => 'Enrobe le bras d\'une roche ultra dense pour décupler l\'impact.',
            ],

            // Suiton (Eau)
            [
                'name' => 'Suiton : Dragon Aqueux',
                'type' => 'Ninjutsu',
                'element' => 'Suiton',
                'base_power' => 48,
                'chakra_cost' => 55,
                'rank' => 'B',
                'clan_required' => null,
                'description' => 'Invoque un colossal torrent d\'eau en forme de dragon s\'abattant sur la cible.',
            ],
            [
                'name' => 'Suiton : Prison Aqueuse',
                'type' => 'Ninjutsu',
                'element' => 'Suiton',
                'base_power' => 28,
                'chakra_cost' => 35,
                'rank' => 'C',
                'clan_required' => null,
                'description' => 'Piège l\'adversaire dans une sphère d\'eau dense le privant d\'oxygène.',
            ],

            // Taijutsu (Sans élément)
            [
                'name' => 'Taijutsu : Tornade de Konoha',
                'type' => 'Taijutsu',
                'element' => null,
                'base_power' => 30,
                'chakra_cost' => 15,
                'rank' => 'D',
                'clan_required' => null,
                'description' => 'Enchaînement tournoyant de coups de pied haut et bas.',
            ],
            [
                'name' => 'Taijutsu : Lotus Recto',
                'type' => 'Taijutsu',
                'element' => null,
                'base_power' => 50,
                'chakra_cost' => 30,
                'rank' => 'B',
                'clan_required' => null,
                'description' => 'Projette l\'adversaire dans les airs avant de le rabattre violemment au sol.',
            ],

            // Genjutsu (Sans élément)
            [
                'name' => 'Genjutsu : Illusion Démoniaque des Arbres',
                'type' => 'Genjutsu',
                'element' => null,
                'base_power' => 32,
                'chakra_cost' => 40,
                'rank' => 'B',
                'clan_required' => null,
                'description' => 'Fait croire à l\'adversaire qu\'il est enraciné et vulnérable.',
            ],
            [
                'name' => 'Genjutsu : Mirage des Corbeaux',
                'type' => 'Genjutsu',
                'element' => null,
                'base_power' => 40,
                'chakra_cost' => 50,
                'rank' => 'A',
                'clan_required' => 'Uchiha',
                'description' => 'Déstabilise la perception ennemie par une nuée de corbeaux mystiques.',
            ],

            // Techniques de Clans Héréditaires (Kekkei Genkai / Hiden)
            [
                'name' => 'Taijutsu Hyūga : Les 64 Poings du Hakke',
                'type' => 'Taijutsu',
                'element' => null,
                'base_power' => 52,
                'chakra_cost' => 45,
                'rank' => 'A',
                'clan_required' => 'Hyūga',
                'description' => 'Frappe les tenketsu adverses pour fermer hermétiquement la circulation du chakra.',
            ],
            [
                'name' => 'Hiden Nara : Manipulation des Ombres',
                'type' => 'Ninjutsu',
                'element' => null,
                'base_power' => 25,
                'chakra_cost' => 35,
                'rank' => 'C',
                'clan_required' => 'Nara',
                'description' => 'Étend son ombre pour immobiliser et contraindre les mouvements de l\'ennemi.',
            ],
            [
                'name' => 'Hiden Akimichi : Décuplement Partiel',
                'type' => 'Taijutsu',
                'element' => null,
                'base_power' => 46,
                'chakra_cost' => 35,
                'rank' => 'C',
                'clan_required' => 'Akimichi',
                'description' => 'Grossit instantanément le bras pour asséner un impact tellurique.',
            ],
            [
                'name' => 'Hiden Yamanaka : Transposition Sensorielle',
                'type' => 'Genjutsu',
                'element' => null,
                'base_power' => 35,
                'chakra_cost' => 40,
                'rank' => 'C',
                'clan_required' => 'Yamanaka',
                'description' => 'Perturbe l\'esprit ennemi pour lui faire subir ses propres actions.',
            ],
            [
                'name' => 'Hiden Aburame : Sphère d\'Insectes Kikaichū',
                'type' => 'Ninjutsu',
                'element' => null,
                'base_power' => 38,
                'chakra_cost' => 35,
                'rank' => 'C',
                'clan_required' => 'Aburame',
                'description' => 'Enveloppe l\'adversaire d\'une colonie d\'insectes drainant vitalité et chakra.',
            ],
            [
                'name' => 'Mokuton Senju : Naissance de la Forêt',
                'type' => 'Ninjutsu',
                'element' => 'Doton',
                'base_power' => 60,
                'chakra_cost' => 70,
                'rank' => 'A',
                'clan_required' => 'Senju',
                'description' => 'Fait jaillir des racines géantes qui écrasent la cible et régénèrent l\'utilisateur.',
            ],
        ];

        foreach ($jutsus as $data) {
            Jutsu::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
