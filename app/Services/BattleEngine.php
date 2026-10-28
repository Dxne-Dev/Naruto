<?php

namespace App\Services;

use App\Models\Battle;
use App\Models\BattleLog;
use App\Models\Fighter;
use App\Models\Jutsu;
use App\Models\MatchGame;
use Illuminate\Support\Str;

class BattleEngine
{
    public const MAX_ROUNDS = 12;

    /**
     * The 5 Elements Cycle:
     * Katon > Fūton > Raiton > Doton > Suiton > Katon.
     */
    public const ELEMENT_ADVANTAGES = [
        'Katon' => 'Fūton',
        'Fūton' => 'Raiton',
        'Raiton' => 'Doton',
        'Doton' => 'Suiton',
        'Suiton' => 'Katon',
    ];

    /**
     * Element Disadvantages:
     */
    public const ELEMENT_DISADVANTAGES = [
        'Katon' => 'Suiton',
        'Fūton' => 'Katon',
        'Raiton' => 'Fūton',
        'Doton' => 'Raiton',
        'Suiton' => 'Doton',
    ];

    /**
     * Calculate elemental multiplier between attacker element and defender element.
     */
    public function getElementMultiplier(?string $attackElement, ?string $defendElement): float
    {
        if (! $attackElement || ! $defendElement) {
            return 1.0;
        }

        if (isset(self::ELEMENT_ADVANTAGES[$attackElement]) && self::ELEMENT_ADVANTAGES[$attackElement] === $defendElement) {
            return 1.25; // Avantage élémentaire : Dégâts x 1.25
        }

        if (isset(self::ELEMENT_DISADVANTAGES[$attackElement]) && self::ELEMENT_DISADVANTAGES[$attackElement] === $defendElement) {
            return 0.75; // Désavantage élémentaire : Dégâts x 0.75
        }

        return 1.0;
    }

    /**
     * Simulate a deterministic combat between two Naruto Shinobis.
     */
    public function simulate(Fighter $fighterA, Fighter $fighterB, ?MatchGame $match = null, ?string $customSeed = null): Battle
    {
        $seed = $customSeed ?? Str::random(16);
        mt_srand((int) crc32($seed));

        // Load Jutsus for both fighters deterministically
        $jutsusA = $fighterA->jutsus()->orderBy('id')->get();
        if ($jutsusA->isEmpty()) {
            $jutsusA = Jutsu::orderBy('id')->limit(3)->get();
        }

        $jutsusB = $fighterB->jutsus()->orderBy('id')->get();
        if ($jutsusB->isEmpty()) {
            $jutsusB = Jutsu::orderBy('id')->limit(3)->get();
        }

        // [Étape 1 : Calcul des Points de Vie (HP) et Chakra de base]
        // HP = Stat_Endurance × 100 (x 1.35 if Uzumaki)
        // Chakra = Stat_Ninjutsu × 80
        $maxHpA = $fighterA->getMaxHp();
        $maxHpB = $fighterB->getMaxHp();
        $hpA = $maxHpA;
        $hpB = $maxHpB;

        $maxChakraA = $fighterA->getMaxChakra();
        $maxChakraB = $fighterB->getMaxChakra();
        $chakraA = $maxChakraA;
        $chakraB = $maxChakraB;

        // Cumulative clan stats & states
        $akimichiStacksA = 0;
        $akimichiStacksB = 0;
        $yamanakaReflectNextA = false;
        $yamanakaReflectNextB = false;
        $uchihaGenjutsuImmunityUsedA = false;
        $uchihaGenjutsuImmunityUsedB = false;

        $logs = [];

        for ($round = 1; $round <= self::MAX_ROUNDS; $round++) {
            if ($hpA <= 0 || $hpB <= 0) {
                break;
            }

            // [Étape 3 : Déclenchement des Passifs de Clans au début du tour]

            // Uzumaki: Régénère 5% de Chakra par tour
            if ($fighterA->clan === 'Uzumaki') {
                $chakraA = min($maxChakraA, $chakraA + (int) round($maxChakraA * 0.05));
            }
            if ($fighterB->clan === 'Uzumaki') {
                $chakraB = min($maxChakraB, $chakraB + (int) round($maxChakraB * 0.05));
            }

            // Senju: Régénère 8% de Vie (Endurance) si blessé
            if ($fighterA->clan === 'Senju' && $hpA < $maxHpA) {
                $regen = min($maxHpA - $hpA, (int) round($maxHpA * 0.08));
                if ($regen > 0) {
                    $hpA += $regen;
                    $logs[] = [
                        'round_number' => $round,
                        'attacker_id' => $fighterA->id,
                        'action_type' => 'clan_passive',
                        'damage_dealt' => $regen,
                        'hp_remaining_a' => $hpA,
                        'hp_remaining_b' => $hpB,
                        'chakra_remaining_a' => $chakraA,
                        'chakra_remaining_b' => $chakraB,
                        'jutsu_used' => 'Vitalité Senju',
                        'narrative' => "🌿 [Clan Senju] {$fighterA->name} puise dans la force vitale ancestrale et régénère {$regen} PV !",
                    ];
                }
            }
            if ($fighterB->clan === 'Senju' && $hpB < $maxHpB) {
                $regen = min($maxHpB - $hpB, (int) round($maxHpB * 0.08));
                if ($regen > 0) {
                    $hpB += $regen;
                    $logs[] = [
                        'round_number' => $round,
                        'attacker_id' => $fighterB->id,
                        'action_type' => 'clan_passive',
                        'damage_dealt' => $regen,
                        'hp_remaining_a' => $hpA,
                        'hp_remaining_b' => $hpB,
                        'chakra_remaining_a' => $chakraA,
                        'chakra_remaining_b' => $chakraB,
                        'jutsu_used' => 'Vitalité Senju',
                        'narrative' => "🌿 [Clan Senju] {$fighterB->name} puise dans la force vitale ancestrale et régénère {$regen} PV !",
                    ];
                }
            }

            // Akimichi: Force +10% chaque tour qui passe
            if ($fighterA->clan === 'Akimichi') {
                $akimichiStacksA++;
            }
            if ($fighterB->clan === 'Akimichi') {
                $akimichiStacksB++;
            }

            // Aburame: Poison drain 5% vie et chakra par tour à l'adversaire
            if ($fighterA->clan === 'Aburame') {
                $drainHp = max(1, (int) round($maxHpB * 0.05));
                $drainChakra = max(1, (int) round($maxChakraB * 0.05));
                $hpB = max(0, $hpB - $drainHp);
                $chakraB = max(0, $chakraB - $drainChakra);
                $logs[] = [
                    'round_number' => $round,
                    'attacker_id' => $fighterA->id,
                    'action_type' => 'clan_passive',
                    'damage_dealt' => $drainHp,
                    'hp_remaining_a' => $hpA,
                    'hp_remaining_b' => $hpB,
                    'chakra_remaining_a' => $chakraA,
                    'chakra_remaining_b' => $chakraB,
                    'jutsu_used' => 'Insectes Kikaichū',
                    'narrative' => "🦗 [Clan Aburame] Les insectes de {$fighterA->name} dévorent {$drainHp} PV et {$drainChakra} Chakra de {$fighterB->name} !",
                ];
            }
            if ($fighterB->clan === 'Aburame') {
                $drainHp = max(1, (int) round($maxHpA * 0.05));
                $drainChakra = max(1, (int) round($maxChakraA * 0.05));
                $hpA = max(0, $hpA - $drainHp);
                $chakraA = max(0, $chakraA - $drainChakra);
                $logs[] = [
                    'round_number' => $round,
                    'attacker_id' => $fighterB->id,
                    'action_type' => 'clan_passive',
                    'damage_dealt' => $drainHp,
                    'hp_remaining_a' => $hpA,
                    'hp_remaining_b' => $hpB,
                    'chakra_remaining_a' => $chakraA,
                    'chakra_remaining_b' => $chakraB,
                    'jutsu_used' => 'Insectes Kikaichū',
                    'narrative' => "🦗 [Clan Aburame] Les insectes de {$fighterB->name} dévorent {$drainHp} PV et {$drainChakra} Chakra de {$fighterA->name} !",
                ];
            }

            if ($hpA <= 0 || $hpB <= 0) {
                break;
            }

            // Nara: Stun Tour 1 si Intelligence > adversaire
            $isStunnedA = false;
            $isStunnedB = false;

            if ($round === 1) {
                if ($fighterA->clan === 'Nara' && $fighterA->stat_int > $fighterB->stat_int) {
                    $isStunnedB = true;
                    $logs[] = [
                        'round_number' => $round,
                        'attacker_id' => $fighterA->id,
                        'action_type' => 'clan_passive',
                        'damage_dealt' => 0,
                        'hp_remaining_a' => $hpA,
                        'hp_remaining_b' => $hpB,
                        'chakra_remaining_a' => $chakraA,
                        'chakra_remaining_b' => $chakraB,
                        'jutsu_used' => 'Immobilisation des Ombres',
                        'narrative' => "♟️ [Clan Nara] {$fighterA->name} anticipe la stratégie ennemie et paralyse {$fighterB->name} avec l'Étreinte des Ombres !",
                    ];
                }
                if ($fighterB->clan === 'Nara' && $fighterB->stat_int > $fighterA->stat_int) {
                    $isStunnedA = true;
                    $logs[] = [
                        'round_number' => $round,
                        'attacker_id' => $fighterB->id,
                        'action_type' => 'clan_passive',
                        'damage_dealt' => 0,
                        'hp_remaining_a' => $hpA,
                        'hp_remaining_b' => $hpB,
                        'chakra_remaining_a' => $chakraA,
                        'chakra_remaining_b' => $chakraB,
                        'jutsu_used' => 'Immobilisation des Ombres',
                        'narrative' => "♟️ [Clan Nara] {$fighterB->name} anticipe la stratégie ennemie et paralyse {$fighterA->name} avec l'Étreinte des Ombres !",
                    ];
                }
            }

            // [Étape 2 : Phase d'Initiative (Détermination de l'attaquant)]
            // Personnage avec la plus haute statistique de 'Vitesse' attaque en premier (bonus +5 si posture offensive/agressive)
            $isAggressiveA = in_array($fighterA->strategy_stance, ['offensive', 'aggressive']);
            $isAggressiveB = in_array($fighterB->strategy_stance, ['offensive', 'aggressive']);
            $spdBonusA = $isAggressiveA ? 5 : 0;
            $spdBonusB = $isAggressiveB ? 5 : 0;
            $spdRollA = ($fighterA->stat_spd * 10) + $spdBonusA + mt_rand(1, 10);
            $spdRollB = ($fighterB->stat_spd * 10) + $spdBonusB + mt_rand(1, 10);
            $firstIsA = $spdRollA >= $spdRollB;

            $turnOrder = $firstIsA
                ? [['fighter' => $fighterA, 'target' => $fighterB, 'isA' => true, 'stunned' => $isStunnedA], ['fighter' => $fighterB, 'target' => $fighterA, 'isA' => false, 'stunned' => $isStunnedB]]
                : [['fighter' => $fighterB, 'target' => $fighterA, 'isA' => false, 'stunned' => $isStunnedB], ['fighter' => $fighterA, 'target' => $fighterB, 'isA' => true, 'stunned' => $isStunnedA]];

            foreach ($turnOrder as $turn) {
                if ($hpA <= 0 || $hpB <= 0) {
                    break;
                }

                $attacker = $turn['fighter'];
                $defender = $turn['target'];
                $isAttackerA = $turn['isA'];

                if ($turn['stunned']) {
                    $logs[] = [
                        'round_number' => $round,
                        'attacker_id' => $attacker->id,
                        'action_type' => 'stunned',
                        'damage_dealt' => 0,
                        'hp_remaining_a' => $hpA,
                        'hp_remaining_b' => $hpB,
                        'chakra_remaining_a' => $chakraA,
                        'chakra_remaining_b' => $chakraB,
                        'jutsu_used' => null,
                        'narrative' => "⏳ {$attacker->name} est paralysé par l'Ombre et doit passer son tour !",
                    ];

                    continue;
                }

                // Pick Jutsu (if sufficient chakra) or basic Taijutsu strike, taking into account jutsu_priority_type
                $availableJutsus = $isAttackerA ? $jutsusA : $jutsusB;
                $currentChakra = $isAttackerA ? $chakraA : $chakraB;

                $usableJutsus = $availableJutsus->filter(fn ($j) => $j->chakra_cost <= $currentChakra)->values();
                $selectedJutsu = null;

                $priority = $attacker->jutsu_priority_type ?? 'auto';
                if ($usableJutsus->isNotEmpty()) {
                    if ($priority === 'highest_power') {
                        $selectedJutsu = $usableJutsus->sortByDesc('base_power')->first();
                    } elseif ($priority === 'lowest_chakra') {
                        $selectedJutsu = $usableJutsus->sortBy('chakra_cost')->first();
                    } elseif ($priority === 'element_counter') {
                        $counterJutsu = $usableJutsus->first(function ($j) use ($defender) {
                            return $j->element && $this->getElementMultiplier($j->element, $defender->primary_element) > 1.0;
                        });
                        $selectedJutsu = $counterJutsu ?? $usableJutsus->get(mt_rand(0, $usableJutsus->count() - 1));
                    } elseif (in_array(strtolower($priority), ['ninjutsu', 'taijutsu', 'genjutsu'])) {
                        $filtered = $usableJutsus->filter(fn ($j) => strtolower($j->type) === strtolower($priority))->values();
                        if ($filtered->isNotEmpty()) {
                            $selectedJutsu = $filtered->get(mt_rand(0, $filtered->count() - 1));
                        }
                    }
                }

                if (! $selectedJutsu && $usableJutsus->isNotEmpty()) {
                    $selectedJutsu = $usableJutsus->get(mt_rand(0, $usableJutsus->count() - 1));
                }

                $actionType = $selectedJutsu ? strtolower($selectedJutsu->type) : 'taijutsu_basique';
                $jutsuName = $selectedJutsu ? $selectedJutsu->name : 'Frappe de Taijutsu de base';
                $jutsuPower = $selectedJutsu ? $selectedJutsu->base_power : 20;
                $chakraCost = $selectedJutsu ? $selectedJutsu->chakra_cost : 0;

                // Deduct Chakra
                if ($isAttackerA) {
                    $chakraA = max(0, $chakraA - $chakraCost);
                } else {
                    $chakraB = max(0, $chakraB - $chakraCost);
                }

                // [Étape 4 : Calcul de Précision / Esquive]
                // Basé sur Intelligence & Vitesse de l'attaquant vs Vitesse & Réflexes du défenseur
                $accRoll = ($attacker->stat_int * 5) + ($attacker->stat_spd * 8) + mt_rand(1, 20);
                if ($attacker->clan === 'Uchiha') {
                    $accRoll *= 1.15; // Uchiha : Précision physique +15%
                }

                $dodgeRoll = ($defender->stat_spd * 10) + mt_rand(1, 20);
                $hit = $accRoll >= $dodgeRoll;

                // Genjutsu checks vs Uchiha immunity
                if ($selectedJutsu && $selectedJutsu->type === 'Genjutsu') {
                    if ($defender->clan === 'Uchiha') {
                        $immunityUsed = $isAttackerA ? $uchihaGenjutsuImmunityUsedB : $uchihaGenjutsuImmunityUsedA;
                        if (! $immunityUsed) {
                            if ($isAttackerA) {
                                $uchihaGenjutsuImmunityUsedB = true;
                            } else {
                                $uchihaGenjutsuImmunityUsedA = true;
                            }
                            $logs[] = [
                                'round_number' => $round,
                                'attacker_id' => $attacker->id,
                                'action_type' => 'genjutsu_immunity',
                                'damage_dealt' => 0,
                                'hp_remaining_a' => $hpA,
                                'hp_remaining_b' => $hpB,
                                'chakra_remaining_a' => $chakraA,
                                'chakra_remaining_b' => $chakraB,
                                'jutsu_used' => $jutsuName,
                                'narrative' => "👁️ [Sharingan Uchiha] {$defender->name} perce instantanément le Genjutsu ennemi [{$jutsuName}] sans subir le moindre effet !",
                            ];

                            continue;
                        }
                    }
                }

                if (! $hit) {
                    $logs[] = [
                        'round_number' => $round,
                        'attacker_id' => $attacker->id,
                        'action_type' => 'dodge',
                        'damage_dealt' => 0,
                        'hp_remaining_a' => $hpA,
                        'hp_remaining_b' => $hpB,
                        'chakra_remaining_a' => $chakraA,
                        'chakra_remaining_b' => $chakraB,
                        'jutsu_used' => $jutsuName,
                        'narrative' => "💨 {$defender->name} anticipe le mouvement et esquive [{$jutsuName}] de {$attacker->name} !",
                    ];

                    // Posture Contre-Attaque / Trickster : riposte immédiate après esquive réussie
                    if (in_array($defender->strategy_stance, ['counter', 'trickster'])) {
                        $counterDmg = max(8, (int) round(($defender->stat_str * 5.0) + mt_rand(2, 6)));
                        if ($isAttackerA) {
                            $hpA = max(0, $hpA - $counterDmg);
                        } else {
                            $hpB = max(0, $hpB - $counterDmg);
                        }

                        $logs[] = [
                            'round_number' => $round,
                            'attacker_id' => $defender->id,
                            'action_type' => 'counter_attack',
                            'damage_dealt' => $counterDmg,
                            'hp_remaining_a' => $hpA,
                            'hp_remaining_b' => $hpB,
                            'chakra_remaining_a' => $chakraA,
                            'chakra_remaining_b' => $chakraB,
                            'jutsu_used' => 'Riposte Tactique',
                            'narrative' => "⚡ [Posture Contre-Attaque] Profitant de son esquive parfaite, {$defender->name} riposte instantanément et inflige {$counterDmg} dégâts à {$attacker->name} !",
                        ];
                    }

                    continue;
                }

                // [Étape 5 : Calcul des Dégâts Nets (Formule mathématique)]
                // Dégâts = (Puissance_Jutsu × Stat_Spécialisée) - (Défense_Adversaire × Stat_Force_ou_Endurance)
                $statSpec = match ($selectedJutsu?->type) {
                    'Genjutsu' => $attacker->stat_gen,
                    'Taijutsu' => $attacker->stat_tai,
                    default => $attacker->stat_nin,
                };

                // Akimichi force stack multiplier
                if ($attacker->clan === 'Akimichi') {
                    $stacks = $isAttackerA ? $akimichiStacksA : $akimichiStacksB;
                    $statSpec *= (1.0 + ($stacks * 0.10));
                }

                $rawDamage = $jutsuPower * ($statSpec / 2.0);

                // Modificateurs de posture tactique de l'attaquant
                if (in_array($attacker->strategy_stance, ['offensive', 'aggressive'])) {
                    $rawDamage *= 1.25; // +25% de dégâts en mode offensif / agressif
                } elseif ($attacker->strategy_stance === 'defensive') {
                    $rawDamage *= 0.80; // -20% de dégâts en mode défensif
                }

                // Elemental multiplier check
                $elementMultiplier = 1.0;
                if ($selectedJutsu && $selectedJutsu->element) {
                    $defElement = $defender->primary_element;
                    $elementMultiplier = $this->getElementMultiplier($selectedJutsu->element, $defElement);
                    $rawDamage *= $elementMultiplier;
                }

                // Defender reduction: (Stat_Endurance / 3.0) ajusté selon la posture
                $defenseReduction = ($defender->stat_end * 4.5);
                if ($defender->strategy_stance === 'defensive') {
                    $defenseReduction *= 1.40; // Défense accrue de 40%
                } elseif (in_array($defender->strategy_stance, ['offensive', 'aggressive'])) {
                    $defenseReduction *= 0.85; // Défense réduite en mode tout-offensif
                }

                $netDamage = max(8, (int) round($rawDamage - $defenseReduction));

                // Hyūga Taijutsu Passive: réduit le chakra max adverse de 15% de sa valeur actuelle
                if ($attacker->clan === 'Hyūga' && ($selectedJutsu?->type === 'Taijutsu' || ! $selectedJutsu)) {
                    if ($isAttackerA) {
                        $chakraDrain = (int) round($chakraB * 0.15);
                        $chakraB = max(0, $chakraB - $chakraDrain);
                        $maxChakraB = max(10, $maxChakraB - $chakraDrain);
                    } else {
                        $chakraDrain = (int) round($chakraA * 0.15);
                        $chakraA = max(0, $chakraA - $chakraDrain);
                        $maxChakraA = max(10, $maxChakraA - $chakraDrain);
                    }
                }

                // Apply damage
                if ($isAttackerA) {
                    $hpB = max(0, $hpB - $netDamage);
                } else {
                    $hpA = max(0, $hpA - $netDamage);
                }

                // Yamanaka Genjutsu check
                if ($attacker->clan === 'Yamanaka' && $selectedJutsu?->type === 'Genjutsu') {
                    if ($isAttackerA) {
                        $yamanakaReflectNextB = true;
                    } else {
                        $yamanakaReflectNextA = true;
                    }
                }

                // Build narrative
                $elemNote = '';
                if ($elementMultiplier > 1.0) {
                    $elemNote = ' (⚡ Avantage Élémentaire ×1.25)';
                } elseif ($elementMultiplier < 1.0) {
                    $elemNote = ' (🛡️ Désavantage Élémentaire ×0.75)';
                }

                $logs[] = [
                    'round_number' => $round,
                    'attacker_id' => $attacker->id,
                    'action_type' => $actionType,
                    'damage_dealt' => $netDamage,
                    'hp_remaining_a' => $hpA,
                    'hp_remaining_b' => $hpB,
                    'chakra_remaining_a' => $chakraA,
                    'chakra_remaining_b' => $chakraB,
                    'jutsu_used' => $jutsuName,
                    'narrative' => "💥 {$attacker->name} exécute [{$jutsuName}]{$elemNote} et inflige {$netDamage} points de dégâts à {$defender->name} !",
                ];

                // Yamanaka Reflect application if active
                $reflectActive = $isAttackerA ? $yamanakaReflectNextA : $yamanakaReflectNextB;
                if ($reflectActive) {
                    $reflectDmg = (int) round($netDamage * 0.5);
                    if ($isAttackerA) {
                        $hpA = max(0, $hpA - $reflectDmg);
                        $yamanakaReflectNextA = false;
                    } else {
                        $hpB = max(0, $hpB - $reflectDmg);
                        $yamanakaReflectNextB = false;
                    }
                    $logs[] = [
                        'round_number' => $round,
                        'attacker_id' => $attacker->id,
                        'action_type' => 'clan_passive',
                        'damage_dealt' => $reflectDmg,
                        'hp_remaining_a' => $hpA,
                        'hp_remaining_b' => $hpB,
                        'chakra_remaining_a' => $chakraA,
                        'chakra_remaining_b' => $chakraB,
                        'jutsu_used' => 'Transposition Yamanaka',
                        'narrative' => "🌀 [Clan Yamanaka] L'esprit troublé de {$attacker->name} lui fait subir 50% de ses propres dégâts ({$reflectDmg} dégâts en retour) !",
                    ];
                }
            }
        }

        // [Étape 6 : Enregistrement des Logs et Détermination du Vainqueur]
        $winnerId = null;
        if ($hpA > $hpB) {
            $winnerId = $fighterA->id;
        } elseif ($hpB > $hpA) {
            $winnerId = $fighterB->id;
        } else {
            // Tie breaker on remaining chakra
            $winnerId = $chakraA >= $chakraB ? $fighterA->id : $fighterB->id;
        }

        $battle = Battle::create([
            'match_id' => $match?->id,
            'fighter_a_id' => $fighterA->id,
            'fighter_b_id' => $fighterB->id,
            'winner_id' => $winnerId,
            'rounds_count' => min(self::MAX_ROUNDS, count(array_unique(array_column($logs, 'round_number')))),
            'random_seed' => $seed,
            'fighter_a_snapshot' => $fighterA->toSnapshot(),
            'fighter_b_snapshot' => $fighterB->toSnapshot(),
            'fighter_a_final_hp' => $hpA,
            'fighter_b_final_hp' => $hpB,
        ]);

        foreach ($logs as $logData) {
            $logData['battle_id'] = $battle->id;
            BattleLog::create($logData);
        }

        if ($match) {
            $match->update([
                'winner_id' => $winnerId,
                'status' => MatchGame::STATUS_COMPLETED,
            ]);
        }

        return $battle;
    }
}
