<?php

namespace App\Models;

use Database\Factories\BattleLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'battle_id',
    'round_number',
    'attacker_id',
    'action_type',
    'damage_dealt',
    'hp_remaining_a',
    'hp_remaining_b',
    'chakra_remaining_a',
    'chakra_remaining_b',
    'jutsu_used',
    'narrative',
])]
class BattleLog extends Model
{
    /** @use HasFactory<BattleLogFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'round_number' => 'integer',
            'damage_dealt' => 'integer',
            'hp_remaining_a' => 'integer',
            'hp_remaining_b' => 'integer',
        ];
    }

    /**
     * The battle parent.
     */
    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class);
    }

    /**
     * The fighter who took the action.
     */
    public function attacker(): BelongsTo
    {
        return $this->belongsTo(Fighter::class, 'attacker_id');
    }
}
