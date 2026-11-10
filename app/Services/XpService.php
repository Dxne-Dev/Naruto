<?php

namespace App\Services;

use App\Models\Battle;
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

    public const XP_FRIEND_DUEL_WIN = 5;

    public const DAILY_FRIEND_DUEL_XP_CAP = 15;

    public const FRIEND_DUEL_COOLDOWN_HOURS = 48;

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

    /**
     * Award XP for winning a direct friend duel, respecting:
     * 1. 48-hour cooldown per opponent won against.
     * 2. Daily cap of 15 XP earned from friend duels.
     *
     * @return array{awarded: bool, amount: int, reason: string}
     */
    public function awardFriendDuelWinXp(Fighter $winnerFighter, Fighter $opponentFighter, Battle $battle): array
    {
        // 1. Verify 48-hour cooldown against this specific friend
        $recentBattleIdsAgainstFriend = Battle::where('winner_id', $winnerFighter->id)
            ->where('id', '!=', $battle->id)
            ->where(function ($q) use ($winnerFighter, $opponentFighter) {
                $q->where(function ($sub) use ($winnerFighter, $opponentFighter) {
                    $sub->where('fighter_a_id', $winnerFighter->id)
                        ->where('fighter_b_id', $opponentFighter->id);
                })->orWhere(function ($sub) use ($winnerFighter, $opponentFighter) {
                    $sub->where('fighter_a_id', $opponentFighter->id)
                        ->where('fighter_b_id', $winnerFighter->id);
                });
            })
            ->pluck('id');

        $hasRecentXpAgainstFriend = XpTransaction::where('fighter_id', $winnerFighter->id)
            ->where('reference_type', 'friend_duel')
            ->whereIn('reference_id', $recentBattleIdsAgainstFriend)
            ->where('created_at', '>=', now()->subHours(self::FRIEND_DUEL_COOLDOWN_HOURS))
            ->exists();

        if ($hasRecentXpAgainstFriend) {
            return [
                'awarded' => false,
                'amount' => 0,
                'reason' => "Délai de 48h non écoulé contre {$opponentFighter->name} (aucun XP accordé).",
            ];
        }

        // 2. Verify daily cap of 15 XP
        $todayXpEarned = (int) XpTransaction::where('fighter_id', $winnerFighter->id)
            ->where('reference_type', 'friend_duel')
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('amount');

        if ($todayXpEarned >= self::DAILY_FRIEND_DUEL_XP_CAP) {
            return [
                'awarded' => false,
                'amount' => 0,
                'reason' => 'Plafond journalier de 15 XP en duels amicaux atteint (aucun XP accordé).',
            ];
        }

        $awardAmount = min(self::XP_FRIEND_DUEL_WIN, self::DAILY_FRIEND_DUEL_XP_CAP - $todayXpEarned);

        if ($awardAmount <= 0) {
            return [
                'awarded' => false,
                'amount' => 0,
                'reason' => 'Plafond journalier atteint.',
            ];
        }

        $this->awardXp(
            $winnerFighter,
            $awardAmount,
            "Victoire en duel amical contre {$opponentFighter->name}",
            'friend_duel',
            $battle->id
        );

        $newTotal = $todayXpEarned + $awardAmount;

        return [
            'awarded' => true,
            'amount' => $awardAmount,
            'reason' => "+{$awardAmount} XP gagnés ({$newTotal}/".self::DAILY_FRIEND_DUEL_XP_CAP." XP aujourd'hui).",
        ];
    }
}
