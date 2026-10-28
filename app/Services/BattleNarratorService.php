<?php

namespace App\Services;

use App\Models\BattleLog;
use Illuminate\Support\Collection;

class BattleNarratorService
{
    /**
     * Build an immersive, natural narrative paragraph for each round.
     * Respects the exact storytelling arc requested:
     * - Opening: Presentation of fighters, vital health & chakra reserves.
     * - Turn progression: Who took the initiative and why (speed/posture/roll), followed by the exchange of techniques.
     * - Consequences: Damage, elemental interactions, dodges, or reflexive clan traits.
     * - Resolution: Ending vital state (HP and Chakra) of each shinobi before the next round.
     *
     * Written as a single, coherent prose paragraph without dry metadata labels ("Début", "Déroulement").
     *
     * @param  Collection<int, BattleLog>  $logs
     * @return Collection<int, array<string, mixed>>
     */
    public function buildNarrativeRounds(Collection $logs, $fighterA, $fighterB): Collection
    {
        $grouped = $logs->groupBy('round_number');
        $roundsNarrative = collect();

        // Track running health and chakra from initial base values
        $currentHpA = $fighterA->getMaxHp();
        $currentHpB = $fighterB->getMaxHp();
        $currentChakraA = $fighterA->getMaxChakra();
        $currentChakraB = $fighterB->getMaxChakra();

        foreach ($grouped as $roundNumber => $roundLogs) {
            $storySentences = [];

            // 1. OPENING: State of the fighters at the start of this round
            if ($roundNumber === 1) {
                $storySentences[] = "Au signal de l'arbitre, {$fighterA->name} du clan {$fighterA->clan} ({$currentHpA} PV, {$currentChakraA} Chakra) et {$fighterB->name} du clan {$fighterB->clan} ({$currentHpB} PV, {$currentChakraB} Chakra) se toisent au centre de l'arène, prêts à en découdre.";
            } else {
                $storySentences[] = "Le combat s'engage dans son {$roundNumber}e round avec {$fighterA->name} affichant {$currentHpA} PV ({$currentChakraA} Chakra) face à {$fighterB->name} qui tient bon avec {$currentHpB} PV ({$currentChakraB} Chakra).";
            }

            // Passive triggers at round start
            $passives = $roundLogs->where('action_type', 'clan_passive');
            foreach ($passives as $p) {
                $userFighter = ($p->attacker_id === $fighterA->id) ? $fighterA : $fighterB;
                $isA = ($p->attacker_id === $fighterA->id);

                if (str_contains($p->narrative, 'Senju')) {
                    $healed = $p->damage_dealt > 0 ? $p->damage_dealt : 30;
                    if ($healed > 0) {
                        $storySentences[] = "L'héritage Senju de {$userFighter->name} se manifeste : ses cellules se régénèrent, lui restituant {$healed} précieux points de vie.";
                    }
                } elseif (str_contains($p->narrative, 'Yamanaka')) {
                    $storySentences[] = "Sous l'emprise persistante de l'art mental Yamanaka, {$userFighter->name} voit ses propres impulsions se retourner contre lui et encaisse {$p->damage_dealt} dégâts en contrecoup.";
                } elseif (str_contains($p->narrative, 'Nara')) {
                    $storySentences[] = "D'un geste millimétré, l'ombre du clan Nara fuse sur les dalles de pierre et immobilise totalement la cible, la privant d'initiative.";
                } elseif (str_contains($p->narrative, 'Aburame')) {
                    $storySentences[] = "Les insectes Kikaichū de {$userFighter->name} s'activent dans l'ombre et continuent de ronger les réserves de chakra ennemies.";
                }
            }

            // 2. EXCHANGES: Combat actions in this round
            $combatLogs = $roundLogs->where('action_type', '!=', 'clan_passive')->values();

            if ($combatLogs->isNotEmpty()) {
                // First action analysis (Initiative)
                $firstAction = $combatLogs->first();
                $firstAttacker = ($firstAction->attacker_id === $fighterA->id) ? $fighterA : $fighterB;
                $firstDefender = ($firstAction->attacker_id === $fighterA->id) ? $fighterB : $fighterA;

                // Initiative rationale
                $stanceDesc = '';
                if (in_array($firstAttacker->strategy_stance, ['offensive', 'aggressive'])) {
                    $stanceDesc = 'porté par une posture résolument agressive et une plus grande célérité, ';
                } elseif ($firstAttacker->stat_spd > $firstDefender->stat_spd) {
                    $stanceDesc = "grâce à une vitesse de déplacement supérieure ({$firstAttacker->stat_spd} contre {$firstDefender->stat_spd}), ";
                } else {
                    $stanceDesc = 'surprenant son adversaire par un jaillissement soudain, ';
                }

                $this->describeAction($firstAction, $firstAttacker, $firstDefender, $stanceDesc, $storySentences);

                // Subsequent actions in this round (Counters / Ripostes)
                for ($i = 1; $i < $combatLogs->count(); $i++) {
                    $act = $combatLogs->get($i);
                    $actAttacker = ($act->attacker_id === $fighterA->id) ? $fighterA : $fighterB;
                    $actDefender = ($act->attacker_id === $fighterA->id) ? $fighterB : $fighterA;

                    $connector = $i === 1 ? 'Sans perdre un instant pour riposter, ' : "Dans la continuité de l'affrontement, ";
                    $this->describeAction($act, $actAttacker, $actDefender, $connector, $storySentences);
                }
            }

            // 3. ROUND END / RESOLUTION: Final state for this round
            $lastLog = $roundLogs->last();
            $endHpA = $lastLog->hp_remaining_a;
            $endHpB = $lastLog->hp_remaining_b;

            // Estimate Chakra deductions based on combat jutsus used
            foreach ($combatLogs as $cLog) {
                $cost = 0;
                if ($cLog->action_type === 'ninjutsu' || $cLog->action_type === 'genjutsu') {
                    $cost = 35;
                } elseif ($cLog->action_type === 'taijutsu') {
                    $cost = 15;
                }
                if ($cLog->attacker_id === $fighterA->id) {
                    $currentChakraA = max(0, $currentChakraA - $cost);
                } else {
                    $currentChakraB = max(0, $currentChakraB - $cost);
                }
            }

            $currentHpA = $endHpA;
            $currentHpB = $endHpB;

            if ($endHpA <= 0 || $endHpB <= 0) {
                $koFighter = $endHpA <= 0 ? $fighterA->name : $fighterB->name;
                $victor = $endHpA <= 0 ? $fighterB->name : $fighterA->name;
                $storySentences[] = "Cette dernière frappe est décisive : {$koFighter} s'écroule lourdement sur l'arène, laissant {$victor} s'imposer par K.O. au terme de ce duel sans merci.";
            } else {
                $storySentences[] = "Au coup de sifflet clôturant ce round, la poussière retombe sur {$fighterA->name} qui conserve {$endHpA} PV et {$currentChakraA} Chakra, tandis que {$fighterB->name} termine l'échange avec {$endHpB} PV et {$currentChakraB} Chakra.";
            }

            // Combine into single smooth story paragraph
            $fullStory = implode(' ', $storySentences);

            $roundsNarrative->push([
                'round_number' => $roundNumber,
                'summary' => $fullStory,
                'hp_a' => $endHpA,
                'hp_b' => $endHpB,
                'is_ko' => ($endHpA <= 0 || $endHpB <= 0),
            ]);
        }

        return $roundsNarrative;
    }

    /**
     * Describe a single combat action naturally.
     */
    private function describeAction($log, $attacker, $defender, string $contextPrefix, array &$storySentences): void
    {
        $jutsu = $log->jutsu_used;
        if (! $jutsu || $jutsu === 'une attaque') {
            if ($log->narrative && preg_match('/\[(.*?)\]/', $log->narrative, $m)) {
                $jutsu = $m[1];
            } else {
                $jutsu = match ($log->action_type) {
                    'ninjutsu' => 'une technique de Ninjutsu',
                    'genjutsu' => 'une illusion de Genjutsu',
                    'taijutsu' => 'un coup de Taijutsu',
                    'taijutsu_basique' => 'une frappe de base',
                    default => 'une offensive',
                };
            }
        }
        $dmg = $log->damage_dealt;

        if ($log->action_type === 'dodge') {
            $storySentences[] = "{$contextPrefix}{$attacker->name} tente de porter [{$jutsu}], mais {$defender->name} fait preuve d'un réflexe salvateur et esquive la trajectoire au millimètre.";
        } elseif ($log->action_type === 'counter_attack') {
            $storySentences[] = "Profitant immédiatement de l'ouverture créée par son esquive, {$attacker->name} enclenche une riposte tactique foudroyante qui fait mouche pour {$dmg} points de dégâts.";
        } elseif ($log->action_type === 'genjutsu_immunity') {
            $storySentences[] = "{$contextPrefix}{$attacker->name} compose l'illusion [{$jutsu}], mais le Sharingan de {$defender->name} démasque instantanément le mirage, annulant tout effet.";
        } else {
            // Hit with possible elemental context
            $elemNote = '';
            if (str_contains($log->narrative, 'Avantage Élémentaire')) {
                $elemNote = ", bénéficiant de l'ascendant naturel de son affinité élémentaire (+25% de puissance),";
            } elseif (str_contains($log->narrative, 'Désavantage Élémentaire')) {
                $elemNote = ", bien qu'amoindri par la résistance élémentaire adverse (-25%),";
            }

            if ($dmg >= 80) {
                $storySentences[] = "{$contextPrefix}{$attacker->name} déploie toute la fureur de [{$jutsu}]{$elemNote} et transperce la garde de {$defender->name}, lui infligeant un impact écrasant de {$dmg} points de dégâts.";
            } elseif ($dmg >= 40) {
                $storySentences[] = "{$contextPrefix}{$attacker->name} exécute [{$jutsu}]{$elemNote} avec une précision chirurgicale, prélevant {$dmg} points de vitalité à son rival.";
            } else {
                $storySentences[] = "{$contextPrefix}{$attacker->name} porte [{$jutsu}]{$elemNote} qui effleure {$defender->name} pour {$dmg} points de dégâts.";
            }
        }
    }
}
