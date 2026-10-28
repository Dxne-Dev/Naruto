<?php

namespace App\Services;

use App\Models\Jutsu;

class FighterGenerator
{
    /**
     * The 8 Canon Naruto / Boruto Clans with distinct passive traits and stat tendencies.
     */
    public const CLANS = [
        'Uchiha' => [
            'passive' => 'Immunité au 1er Genjutsu du combat. Précision physique +15%.',
            'favored_element' => 'Katon',
            'favored_spec' => 'Ninjutsu',
            'bonus_stats' => ['stat_nin' => 0.6, 'stat_gen' => 0.5, 'stat_spd' => 0.4],
        ],
        'Hyūga' => [
            'passive' => 'Si une attaque Taijutsu touche, réduit le Chakra max adverse de 15%.',
            'favored_element' => null,
            'favored_spec' => 'Taijutsu',
            'bonus_stats' => ['stat_tai' => 0.8, 'stat_spd' => 0.4, 'stat_sea' => 0.3],
        ],
        'Uzumaki' => [
            'passive' => 'Multiplie les PV max par 1.35. Régénère 5% de Chakra par tour.',
            'favored_element' => 'Fūton',
            'favored_spec' => 'Fūinjutsu',
            'bonus_stats' => ['stat_end' => 0.9, 'stat_nin' => 0.4, 'stat_sea' => 0.4],
        ],
        'Senju' => [
            'passive' => 'Régénère 8% de Vie (Endurance) à chaque fin de tour.',
            'favored_element' => 'Doton',
            'favored_spec' => 'Ninjutsu',
            'bonus_stats' => ['stat_end' => 0.7, 'stat_str' => 0.5, 'stat_nin' => 0.5],
        ],
        'Nara' => [
            'passive' => 'Si Intelligence > Intelligence adverse, l\'adversaire est étourdi au Tour 1.',
            'favored_element' => 'Suiton',
            'favored_spec' => 'Hiden',
            'bonus_stats' => ['stat_int' => 1.0, 'stat_sea' => 0.4],
        ],
        'Akimichi' => [
            'passive' => 'Chaque tour qui passe augmente la Force de 10% (effet cumulable).',
            'favored_element' => 'Doton',
            'favored_spec' => 'Taijutsu',
            'bonus_stats' => ['stat_str' => 0.9, 'stat_end' => 0.6],
        ],
        'Yamanaka' => [
            'passive' => 'Si un Genjutsu réussit, l\'adversaire s\'inflige 50% de ses propres dégâts au tour suivant.',
            'favored_element' => 'Doton',
            'favored_spec' => 'Genjutsu',
            'bonus_stats' => ['stat_gen' => 0.8, 'stat_int' => 0.6],
        ],
        'Aburame' => [
            'passive' => 'Inflige des dégâts fixes de poison (drain de 5% de vie et chakra par tour).',
            'favored_element' => null,
            'favored_spec' => 'Hiden',
            'bonus_stats' => ['stat_int' => 0.6, 'stat_sea' => 0.6, 'stat_end' => 0.3],
        ],
    ];

    /**
     * The 5 Elements of Chakra (Chakra Nature).
     */
    public const ELEMENTS = ['Katon', 'Fūton', 'Raiton', 'Doton', 'Suiton'];

    /**
     * Specializations.
     */
    public const SPECIALIZATIONS = ['Ninjutsu', 'Taijutsu', 'Genjutsu', 'Fūinjutsu', 'Bukijutsu', 'Hiden'];

    /**
     * Hidden villages.
     */
    public const VILLAGES = ['Konoha', 'Suna', 'Kiri', 'Iwa', 'Kumo'];

    /**
     * Catalogue interne de 25 prénoms originaux, crédibles et immersifs par Clan (aucun héros principal copié).
     */
    public const CLAN_NAMES_CATALOG = [
        'Uchiha' => [
            'Homura', 'Kagari', 'Enoki', 'Setsu', 'Raikō', 'Karyū', 'Senkō', 'Suzaku',
            'Shinra', 'Kasumi', 'Tomoe', 'Shion', 'Ginga', 'Kurohiko', 'Jakō', 'Hayato',
            'Rikuto', 'Kuren', 'Yozora', 'Tenma', 'Benkei', 'Shinri', 'Kensei', 'Tsubaki', 'Kairi',
        ],
        'Hyūga' => [
            'Hatsumi', 'Keijin', 'Seiryu', 'Chihaya', 'Mizuki', 'Tōma', 'Kazahana', 'Hakuto',
            'Yukihiko', 'Shinra', 'Yūkoku', 'Karin', 'Hisoka', 'Atsuki', 'Kirihiko', 'Sakuya',
            'Kanna', 'Genji', 'Meiko', 'Shiro', 'Sōjiro', 'Reika', 'Kōya', 'Hidenori', 'Yuzuru',
        ],
        'Uzumaki' => [
            'Renjiro', 'Akari', 'Homare', 'Kazuma', 'Kōki', 'Daiki', 'Kenshō', 'Ryūto',
            'Suzuna', 'Hinata', 'Kagura', 'Tatsumi', 'Genshin', 'Chitose', 'Shinzo', 'Hayate',
            'Manabu', 'Raiden', 'Umihiko', 'Kiyoshi', 'Kousei', 'Tsukasa', 'Akihiko', 'Masumi', 'Renka',
        ],
        'Senju' => [
            'Morihei', 'Shinzan', 'Kazumori', 'Ritsuki', 'Katsumasa', 'Daigo', 'Tōgo', 'Genkō',
            'Jukō', 'Kanzaburō', 'Nobuyuki', 'Teruhiko', 'Hidetaka', 'Yasumori', 'Takatora', 'Shūma',
            'Ranjirō', 'Gaku', 'Munenori', 'Katsushige', 'Toratsugu', 'Shinshō', 'Renpō', 'Masahiko', 'Seijirō',
        ],
        'Nara' => [
            'Kagemori', 'Shieki', 'Sōkaku', 'Rantei', 'Gakuto', 'Enshū', 'Kurōdo', 'Senshū',
            'Shikei', 'Moritsugu', 'Kageki', 'Shinsaku', 'Hōzan', 'Kensaku', 'Ritsu', 'Yukimori',
            'Chōshū', 'Kansuke', 'Sōzō', 'Shūkei', 'Katsuyuki', 'Seiichi', 'Munenari', 'Keisaku', 'Fujimaru',
        ],
        'Akimichi' => [
            'Daikaku', 'Gorōta', 'Tetsuzan', 'Banzō', 'Kongō', 'Mantaro', 'Ranzō', 'Tatsumasa',
            'Genpachi', 'Kintoki', 'Daimon', 'Rikimaru', 'Chōshirō', 'Gōshirō', 'Buntaro', 'Mankyō',
            'Toramaru', 'Daihachirō', 'Katsuzan', 'Bōshō', 'Yūzan', 'Shingō', 'Tetsutarō', 'Gennosuke', 'Morikaku',
        ],
        'Yamanaka' => [
            'Kirika', 'Sanae', 'Sayuri', 'Kōsuke', 'Kazumi', 'Yūshin', 'Rikka', 'Chifuyu',
            'Minori', 'Kishō', 'Hazuki', 'Shion', 'Suzumi', 'Kaede', 'Katsura', 'Shinno',
            'Ayane', 'Miharu', 'Tōsen', 'Renka', 'Shinozaki', 'Hisaki', 'Yūsei', 'Kuren', 'Reiji',
        ],
        'Aburame' => [
            'Muroto', 'Kurohiko', 'Chitsugu', 'Kurogane', 'Kenzō', 'Yōzō', 'Gōji', 'Senba',
            'Shinro', 'Jindō', 'Kanmuri', 'Moritsuna', 'Renba', 'Katsuto', 'Tenba', 'Sokurō',
            'Kitsune', 'Hozumi', 'Rensho', 'Mugento', 'Sōjun', 'Gensho', 'Kiyosumi', 'Maboroshi', 'Shūkō',
        ],
    ];

    /**
     * Fallback ninja names originaux.
     */
    public const NINJA_NAMES = [
        'Kaito', 'Ryo', 'Hayate', 'Genzō', 'Raikō', 'Atsuki', 'Kotetsu', 'Izumo',
        'Kazuma', 'Kenzō', 'Shizuma', 'Shinji', 'Asuka', 'Seiji', 'Sōra', 'Tatsuma',
        'Morio', 'Shinjirō', 'Kōetsu', 'Renji', 'Kagura', 'Hōki', 'Renga', 'Sojuro', 'Chikara',
    ];

    /**
     * Generate a balanced Shinobi candidate with Databook stats and connected Jutsus.
     *
     * @return array<string, mixed>
     */
    public function generateCandidate(?string $chosenClan = null, ?string $chosenElement = null): array
    {
        $clanKeys = array_keys(self::CLANS);
        $clan = $chosenClan && isset(self::CLANS[$chosenClan])
            ? $chosenClan
            : $clanKeys[array_rand($clanKeys)];

        $clanData = self::CLANS[$clan];

        // 1. Element wheel
        $primaryElement = $chosenElement && in_array($chosenElement, self::ELEMENTS)
            ? $chosenElement
            : ($clanData['favored_element'] ?? self::ELEMENTS[array_rand(self::ELEMENTS)]);

        // 2. Specialization wheel
        $specialization = $clanData['favored_spec'] ?? self::SPECIALIZATIONS[array_rand(self::SPECIALIZATIONS)];

        // 3. Village wheel
        $village = self::VILLAGES[array_rand(self::VILLAGES)];

        // 4. Generate Databook Stats (from 1.0 to 5.0, sum balanced around 20.0 points total)
        // 8 Databook dimensions: Nin, Tai, Gen, Int, Str, Spd, End, Sea
        $stats = [
            'stat_nin' => 2.0,
            'stat_tai' => 2.0,
            'stat_gen' => 2.0,
            'stat_int' => 2.0,
            'stat_str' => 2.0,
            'stat_spd' => 2.0,
            'stat_end' => 2.0,
            'stat_sea' => 2.0,
        ];

        // Apply clan bonus stats
        if (! empty($clanData['bonus_stats'])) {
            foreach ($clanData['bonus_stats'] as $st => $bonus) {
                if (isset($stats[$st])) {
                    $stats[$st] += $bonus;
                }
            }
        }

        // Add small variations
        foreach ($stats as $st => $val) {
            $variance = (mt_rand(-3, 4)) / 10.0;
            $stats[$st] = max(1.0, min(5.0, round($val + $variance, 1)));
        }

        // Pick Jutsus according to Clan and Element
        $jutsus = Jutsu::query()
            ->where(function ($q) use ($clan) {
                $q->whereNull('clan_required')
                    ->orWhere('clan_required', $clan);
            })
            ->where(function ($q) use ($primaryElement) {
                $q->whereNull('element')
                    ->orWhere('element', $primaryElement);
            })
            ->inRandomOrder()
            ->limit(3)
            ->get();

        // If none found, fallback to any available 3 jutsus
        if ($jutsus->isEmpty()) {
            $jutsus = Jutsu::inRandomOrder()->limit(3)->get();
        }

        // Tirage aléatoire uniforme à partir du catalogue spécifique au clan
        $clanPool = self::CLAN_NAMES_CATALOG[$clan] ?? self::NINJA_NAMES;
        $firstName = $clanPool[array_rand($clanPool)];
        $suggestedName = $firstName.' '.$clan;

        return [
            'name_suggestion' => $suggestedName,
            'clan' => $clan,
            'village' => $village,
            'ninja_rank' => 'Genin',
            'primary_element' => $primaryElement,
            'secondary_element' => null,
            'specialization' => $specialization,
            'passive_description' => $clanData['passive'],
            'stats' => $stats,
            'jutsus' => $jutsus->toArray(),
            'jutsu_ids' => $jutsus->pluck('id')->toArray(),
            'portrait' => 'ninja_shadow.png',
        ];
    }
}
