<?php

namespace App\Models;

use Database\Factories\TournamentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'status',
    'is_private',
    'room_code',
    'creator_id',
    'max_participants',
    'registration_opens_at',
    'registration_closes_at',
    'starts_at',
    'bracket_generated_at',
])]
class Tournament extends Model
{
    /** @use HasFactory<TournamentFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_REGISTRATION = 'registration';

    public const STATUS_LOCKED = 'locked';

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
            'is_private' => 'boolean',
            'max_participants' => 'integer',
            'registration_opens_at' => 'datetime',
            'registration_closes_at' => 'datetime',
            'starts_at' => 'datetime',
            'bracket_generated_at' => 'datetime',
        ];
    }

    /**
     * Tournament entries (participants).
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TournamentEntry::class);
    }

    /**
     * Get the human-readable status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Brouillon',
            self::STATUS_REGISTRATION => 'Inscriptions ouvertes',
            self::STATUS_LOCKED => 'Arbre verrouillé',
            self::STATUS_RUNNING => 'Combats en cours',
            self::STATUS_COMPLETED => 'Tournoi terminé',
            self::STATUS_CANCELLED => 'Annulé',
            default => ucfirst($this->status),
        };
    }

    /**
     * Matches in this tournament.
     */
    public function matches(): HasMany
    {
        return $this->hasMany(MatchGame::class)->orderBy('round')->orderBy('match_number');
    }

    /**
     * Creator of the private room.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
