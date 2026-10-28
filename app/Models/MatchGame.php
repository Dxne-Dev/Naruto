<?php

namespace App\Models;

use Database\Factories\MatchGameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table('matches')]
#[Fillable([
    'tournament_id',
    'round',
    'match_number',
    'fighter_a_id',
    'fighter_b_id',
    'winner_id',
    'status',
    'scheduled_at',
    'completed_at',
])]
class MatchGame extends Model
{
    /** @use HasFactory<MatchGameFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'round' => 'integer',
            'match_number' => 'integer',
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * The tournament this match belongs to.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
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
     * Match winner.
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Fighter::class, 'winner_id');
    }

    /**
     * The detailed battle result.
     */
    public function battle(): HasOne
    {
        return $this->hasOne(Battle::class, 'match_id');
    }
}
