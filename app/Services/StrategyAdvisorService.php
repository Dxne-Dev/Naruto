<?php

namespace App\Services;

use App\Models\Fighter;

class StrategyAdvisorService
{
    /**
     * Compute radar points and combat metrics according to a given stance and fighter Databook stats.
     *
     * Radar canvas: 320 x 300 (Center at (160, 150), Max Radius = 90)
     * Text labels are placed in SVG at dedicated coordinates outside the pentagon:
     * 1. Initiative (Top, angle -90°): x=160, y=36 (Anchor: middle)
     * 2. Dégâts (Top-Right, angle -18°): x=270, y=120 (Anchor: start)
     * 3. Critique (Bottom-Right, angle 54°): x=235, y=250 (Anchor: start)
     * 4. Défense (Bottom-Left, angle 126°): x=85, y=250 (Anchor: end)
     * 5. Esquive (Top-Left, angle 198°): x=50, y=120 (Anchor: end)
     */
    public function computeTacticalProfile(Fighter $fighter, string $stance, string $priority): array
    {
        // Base Databook stats (scale ~ 1.0 to 5.0)
        $spd = (float) $fighter->stat_spd;
        $str = (float) $fighter->stat_str;
        $nin = (float) $fighter->stat_nin;
        $tai = (float) $fighter->stat_tai;
        $gen = (float) $fighter->stat_gen;
        $int = (float) $fighter->stat_int;
        $end = (float) $fighter->stat_end;
        $sea = (float) $fighter->stat_sea;

        // Neutral values (score 0 to 100)
        $neutralInitiative = (int) round(min(100, max(15, ($spd * 16))));
        $highestAttackStat = max($nin, $tai, $gen, $str);
        $neutralDamage = (int) round(min(100, max(15, ($highestAttackStat * 17))));
        $neutralCrit = (int) round(min(100, max(15, (($int * 10) + ($sea * 8)) * ($fighter->clan === 'Uchiha' ? 1.15 : 1.0))));
        $neutralDefense = (int) round(min(100, max(15, (($end * 14) + ($str * 4)))));
        $neutralDodge = (int) round(min(100, max(15, ($spd * 17))));

        // Stance Modifiers
        $modInitiative = 0;
        $modDamage = 0;
        $labelDamage = '+0%';
        $modCrit = 0;
        $modDefense = 0;
        $modDodge = 0;
        $labelDodge = '+0%';

        $isOffensive = in_array($stance, ['offensive', 'aggressive']);
        $isDefensive = $stance === 'defensive';

        if ($isOffensive) {
            $modInitiative = +10;
            $modDamage = +25;
            $labelDamage = '+25%';
            $modCrit = +15;
            $modDefense = -15;
            $modDodge = -15;
            $labelDodge = '-15%';
        } elseif ($isDefensive) {
            $modInitiative = -10;
            $modDamage = -20;
            $labelDamage = '-20%';
            $modCrit = -10;
            $modDefense = +40;
            $modDodge = +20;
            $labelDodge = '+20%';
        } else {
            // Balanced
            $modInitiative = 0;
            $modDamage = +10;
            $labelDamage = '+10%';
            $modCrit = +5;
            $modDefense = +10;
            $modDodge = 0;
            $labelDodge = '0%';
        }

        // Active final scores clamped between 15 and 95 so vertices remain clean
        $activeInitiative = (int) round(min(95, max(15, $neutralInitiative + $modInitiative)));
        $activeDamage = (int) round(min(95, max(15, $neutralDamage + $modDamage)));
        $activeCrit = (int) round(min(95, max(15, $neutralCrit + $modCrit)));
        $activeDefense = (int) round(min(95, max(15, $neutralDefense + $modDefense)));
        $activeDodge = (int) round(min(95, max(15, $neutralDodge + $modDodge)));

        // SVG Polygon points calculation
        $neutralPoints = $this->pointsToSvgCoordinates([
            $neutralInitiative,
            $neutralDamage,
            $neutralCrit,
            $neutralDefense,
            $neutralDodge,
        ]);

        $activePoints = $this->pointsToSvgCoordinates([
            $activeInitiative,
            $activeDamage,
            $activeCrit,
            $activeDefense,
            $activeDodge,
        ]);

        // Dynamic Sensei Analysis based on clan, primary element, stance and priority
        $analysis = $this->generateSenseiAdvice($fighter, $stance, $priority, $highestAttackStat);

        return [
            'metrics' => [
                'initiative' => $activeInitiative,
                'damage' => $activeDamage,
                'damage_label' => $labelDamage,
                'crit' => $activeCrit,
                'defense' => $activeDefense,
                'dodge' => $activeDodge,
                'dodge_label' => $labelDodge,
            ],
            'neutral_svg_points' => $neutralPoints['polygon'],
            'active_svg_points' => $activePoints['polygon'],
            'active_circles' => $activePoints['circles'],
            'color' => $isOffensive ? '#E50914' : ($isDefensive ? '#10B981' : '#38E2F6'),
            'color_bg' => $isOffensive ? 'rgba(229,9,20,0.25)' : ($isDefensive ? 'rgba(16,185,129,0.25)' : 'rgba(56,226,246,0.25)'),
            'advice' => $analysis,
        ];
    }

    /**
     * Map 5 percentage scores (0..100) to pentagon SVG coordinates.
     * Canvas: 320 x 300, Center: (160, 150), MaxRadius: 90.
     */
    protected function pointsToSvgCoordinates(array $scores): array
    {
        $cx = 160;
        $cy = 150;
        $maxR = 90;
        $minR = 20;

        // Angles for 5 vertexes starting from top (-90 degrees)
        $angles = [
            -90 * (M_PI / 180), // Top: Initiative
            -18 * (M_PI / 180), // Top-Right: Damage
            54 * (M_PI / 180),  // Bottom-Right: Crit
            126 * (M_PI / 180), // Bottom-Left: Defense
            198 * (M_PI / 180), // Top-Left: Dodge
        ];

        $pointStrings = [];
        $circles = [];

        foreach ($scores as $idx => $score) {
            $r = $minR + (($score / 100.0) * ($maxR - $minR));
            $x = round($cx + ($r * cos($angles[$idx])), 1);
            $y = round($cy + ($r * sin($angles[$idx])), 1);

            $pointStrings[] = "{$x},{$y}";
            $circles[] = ['cx' => $x, 'cy' => $y];
        }

        return [
            'polygon' => implode(' ', $pointStrings),
            'circles' => $circles,
        ];
    }

    /**
     * Generate dynamic, tailored sensei tactical advice.
     */
    protected function generateSenseiAdvice(Fighter $fighter, string $stance, string $priority, float $highestAttackStat): array
    {
        $clan = $fighter->clan;
        $element = $fighter->primary_element;
        $isOffensive = in_array($stance, ['offensive', 'aggressive']);
        $isDefensive = $stance === 'defensive';

        // 1. Clan Synergies
        $synergyQuality = 'Bonne';
        $synergyReason = '';

        if ($clan === 'Uchiha') {
            if ($isOffensive) {
                $synergyQuality = 'Optimale';
                $synergyReason = 'La précision naturelle du Sharingan (+15%) et l\'immunité au premier Genjutsu renforcent violemment votre assaut frontal Katon.';
            } else {
                $synergyQuality = 'Équilibrée';
                $synergyReason = 'Une posture prudente permet de temporiser afin d\'épuiser le chakra adverse grâce aux illusions du Sharingan.';
            }
        } elseif ($clan === 'Hyūga') {
            if ($isOffensive || $stance === 'balanced') {
                $synergyQuality = 'Optimale';
                $synergyReason = 'Chaque frappe Taijutsu draine 15% du chakra max ennemi. Maintenir une pression offensive asphyxie rapidement l\'ennemi.';
            } else {
                $synergyQuality = 'Défensive';
                $synergyReason = 'Le Tourbillon Divin compense la perte de dégâts, mais retarde le drain de chakra adverse.';
            }
        } elseif ($clan === 'Uzumaki') {
            if ($isDefensive) {
                $synergyQuality = 'Légendaire';
                $synergyReason = 'Avec votre réserve de PV colossale (+35%) et la régénération de 5% de chakra par tour, jouer l\'usure défensive rend votre défaite quasi-impossible.';
            } else {
                $synergyQuality = 'Robuste';
                $synergyReason = 'Votre vitalité compense amplement la baisse d\'esquive du mode assaut frontal.';
            }
        } elseif ($clan === 'Senju') {
            if ($isDefensive || $stance === 'balanced') {
                $synergyQuality = 'Optimale';
                $synergyReason = 'La régénération de 8% de vie à chaque tour de votre corps d\'Ermite brille particulièrement dans les combats prolongés.';
            } else {
                $synergyQuality = 'Bonne';
                $synergyReason = 'Votre force brute écrase les lignes adverses, tout en conservant une régénération naturelle constante.';
            }
        } elseif ($clan === 'Akimichi') {
            if ($isDefensive || $stance === 'balanced') {
                $synergyQuality = 'Optimale';
                $synergyReason = 'Chaque tour qui passe augmente votre Force de 10% cumulable. Une posture d\'endurance transforme votre shinobi en titan au fil des tours.';
            } else {
                $synergyQuality = 'Agressive';
                $synergyReason = 'Vous frappez fort immédiatement, sans exploiter le plein potentiel du cumul de puissance sur la durée.';
            }
        } elseif ($clan === 'Nara') {
            $synergyQuality = ($priority === 'element_counter' || $stance === 'balanced') ? 'Optimale' : 'Bonne';
            $synergyReason = 'Votre intelligence supérieure étourdit l\'adversaire au Tour 1. L\'adaptation tactique et l\'avantage élémentaire font honneur à l\'esprit du clan Nara.';
        } else {
            $synergyQuality = 'Harmonieuse';
            $synergyReason = "Les prédispositions du clan {$clan} sont efficacement exploitées avec votre affinité {$element}.";
        }

        // 2. Chakra consumption estimation based on priority
        if ($priority === 'lowest_chakra') {
            $chakraConsumption = 'Économique';
        } elseif ($priority === 'highest_power') {
            $chakraConsumption = $isOffensive ? 'Élevée' : 'Importante';
        } elseif ($priority === 'element_counter') {
            $chakraConsumption = 'Modérée & Ciblée';
        } else {
            $chakraConsumption = 'Modérée';
        }

        // 3. Main Sensei Text
        if ($isOffensive) {
            $analysisText = "Votre posture offensive augmente vos dégâts infligés de +25% et vous confère l'initiative du premier coup. ".
                "Avec votre spécialité majeure ({$highestAttackStat}/5.0), cela met immédiatement l'adversaire en danger critique. ".
                $synergyReason." Attention toutefois à la réduction de vos capacités d'esquive face aux contre-attaques rapides.";
        } elseif ($isDefensive) {
            $analysisText = 'Votre posture défensive renforce votre résistance de +40% et amplifie votre esquive. '.
                "Vous contraignez l'adversaire à épuiser ses réserves de chakra sur votre garde d'acier. ".
                $synergyReason." Idéal face aux attaquants impulsifs que vous pourrez punir dès l'essoufflement de leur assaut.";
        } else {
            $analysisText = "La voie de l'équilibre garantit un contrôle complet du champ de bataille. ".
                'Vous conservez une excellente flexibilité tout en réduisant le coût en chakra de vos techniques. '.
                $synergyReason." Une stratégie polyvalente sans faille évidente, adaptée à n'importe quel type d'opposant.";
        }

        return [
            'synergy' => $synergyQuality,
            'chakra_consumption' => $chakraConsumption,
            'text' => $analysisText,
        ];
    }
}
