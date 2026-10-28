<?php

namespace App\Models;

use Database\Factories\TournamentEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tournament_id',
    'fighter_id',
    'seed',
    'final_position',
    'registered_at',
    'registration_ip',
    'device_fingerprint',
])]
class TournamentEntry extends Model
{
    /** @use HasFactory<TournamentEntryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'seed' => 'integer',
            'final_position' => 'integer',
            'registered_at' => 'datetime',
        ];
    }

    /**
     * The tournament for this entry.
     */
    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * The registered fighter.
     */
    public function fighter(): BelongsTo
    {
        return $this->belongsTo(Fighter::class);
    }
}
