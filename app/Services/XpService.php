<?php

namespace App\Services;

use App\Models\Fighter;
use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Support\Facades\DB;

class XpService
{
    // Specific economy rules requested by the user:
    // • Défaite au 1er tour : +50 XP (Consolation)
    // • Victoire de match : +150 XP
    // • Victoire finale du Tournoi : +500 XP + 1 Jeton de Roue Premium.
    // • Évolution : Tous les 500 XP, l'OC gagne 1 niveau et 0.5 point d'attribut à allouer.
    public const XP_FIRST_ROUND_LOSS = 50;

    public const XP_MATCH_WIN = 150;

    public const XP_CHAMPION = 500;

    /**
     * Award XP to a shinobi and handle level up (+0.5 attribute point every 500 XP).
     */
    public function awardXp(Fighter $fighter, int $amount, string $reason, ?string $refType = null, ?int $refId = null): ?XpTransaction
    {
        return DB::transaction(function () use ($fighter, $amount, $reason, $refType, $refId) {
            if ($refType && $refId) {
                $exists = XpTransaction::where('fighter_id', $fighter->id)
                    ->where('reference_type', $refType)
                    ->where('reference_id', $refId)
                    ->exists();

                if ($exists) {
                    return null;
                }
            }

            $transaction = XpTransaction::create([
                'fighter_id' => $fighter->id,
                'amount' => $amount,
                'reason' => $reason,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);

            $newXp = $fighter->xp + $amount;
            $newLevel = $this->calculateLevel($newXp);
            $levelsGained = max(0, $newLevel - $fighter->level);
            $newAvailablePoints = $fighter->available_stat_points + ($levelsGained * 0.5);

            $newRank = 'Genin';
            if ($newLevel >= 15) {
                $newRank = 'Kage';
            } elseif ($newLevel >= 10) {
                $newRank = 'Jōnin';
            } elseif ($newLevel >= 5) {
                $newRank = 'Chūnin';
            }

            $fighter->update([
                'xp' => $newXp,
                'level' => $newLevel,
                'ninja_rank' => $newRank,
                'available_stat_points' => $newAvailablePoints,
            ]);

            // If tournament win, also credit 1 wheel token to the user
            if ($amount >= self::XP_CHAMPION && $fighter->user) {
                $fighter->user->increment('wheel_tokens', 1);
            }

            return $transaction;
        });
    }

    /**
     * Level calculation: 1 level every 500 XP.
     */
    public function calculateLevel(int $xp): int
    {
        return 1 + (int) floor($xp / 500);
    }
}
