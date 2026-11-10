<?php

namespace App\Models;

use Database\Factories\BattleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'match_id',
    'fighter_a_id',
    'fighter_b_id',
    'fighter_a_snapshot',
    'fighter_b_snapshot',
    'fighter_a_final_hp',
    'fighter_b_final_hp',
    'ai_commentary',
    'winner_id',
    'rounds_count',
    'random_seed',
])]
class Battle extends Model
{
    /** @use HasFactory<BattleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fighter_a_snapshot' => 'array',
            'fighter_b_snapshot' => 'array',
            'fighter_a_final_hp' => 'integer',
            'fighter_b_final_hp' => 'integer',
            'rounds_count' => 'integer',
        ];
    }

    /**
     * Associated match in tournament.
     */
    public function match(): BelongsTo
    {
        return $this->belongsTo(MatchGame::class, 'match_id');
    }

    /**
     * Fighter A.
     */
    public function fighterA(): BelongsTo
    {
        return $this->belongsTo(Fighter::class, 'fighter_a_id');
    }

    /**
     * Fighter B.
     */
    public function fighterB(): BelongsTo
    {
        return $this->belongsTo(Fighter::class, 'fighter_b_id');
    }

    /**
     * Battle winner.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Fighter::class, 'winner_id');
    }

    /**
     * Detailed turn-by-turn logs.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(BattleLog::class)->orderBy('round_number')->orderBy('id');
    }

    /**
     * Determine if this battle involves at least one human player.
     * Bot vs Bot returns false.
     * Player vs Bot or Player vs Player returns true.
     */
    public function involvesHumanPlayer(): bool
    {
        $fighterA = $this->fighterA;
        $fighterB = $this->fighterB;

        $isBotA = $fighterA ? ($fighterA->is_bot || empty($fighterA->user_id)) : false;
        $isBotB = $fighterB ? ($fighterB->is_bot || empty($fighterB->user_id)) : false;

        // If either fighter is not a bot, it involves a human player
        return (! $isBotA) || (! $isBotB);
    }

    /**
     * Associated challenge if this battle was a friend duel.
     */
    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class, 'id', 'battle_id');
    }

    /**
     * Get XP transaction earned by a given fighter in this battle (if any).
     */
    public function xpTransactionFor(int $fighterId): ?XpTransaction
    {
        return XpTransaction::where('fighter_id', $fighterId)
            ->where(function ($q) {
                $q->where(function ($sq) {
                    $sq->where('reference_type', 'friend_duel')
                        ->where('reference_id', $this->id);
                });
                if ($this->match_id) {
                    $q->orWhere(function ($mq) {
                        $mq->whereIn('reference_type', ['match_win', 'match_loss_round_1'])
                            ->where('reference_id', $this->match_id);
                    });
                }
            })
            ->first();
    }
}
