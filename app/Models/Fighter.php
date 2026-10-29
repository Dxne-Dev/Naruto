<?php

namespace App\Models;

use Database\Factories\FighterFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'name',
    'archetype',
    'clan',
    'village',
    'ninja_rank',
    'primary_element',
    'secondary_element',
    'specialization',
    'stat_nin',
    'stat_tai',
    'stat_gen',
    'stat_int',
    'stat_str',
    'stat_spd',
    'stat_end',
    'stat_sea',
    'power',
    'speed',
    'defense',
    'control',
    'special_power',
    'talent',
    'source_photo_path',
    'portrait_path',
    'portrait_status',
    'portrait_ordered_at',
    'level',
    'xp',
    'available_stat_points',
    'strategy_stance',
    'jutsu_priority_type',
    'is_bot',
])]
class Fighter extends Model
{
    /** @use HasFactory<FighterFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_nin' => 'float',
            'stat_tai' => 'float',
            'stat_gen' => 'float',
            'stat_int' => 'float',
            'stat_str' => 'float',
            'stat_spd' => 'float',
            'stat_end' => 'float',
            'stat_sea' => 'float',
            'available_stat_points' => 'float',
            'power' => 'integer',
            'speed' => 'integer',
            'defense' => 'integer',
            'control' => 'integer',
            'level' => 'integer',
            'xp' => 'integer',
            'portrait_ordered_at' => 'datetime',
        ];
    }

    /**
     * The user owning the fighter.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Jutsus known by this shinobi.
     */
    public function jutsus(): BelongsToMany
    {
        return $this->belongsToMany(Jutsu::class, 'fighter_jutsu');
    }

    /**
     * Tournament entries for this fighter.
     */
    public function tournamentEntries(): HasMany
    {
        return $this->hasMany(TournamentEntry::class);
    }

    /**
     * XP transactions history.
     */
    public function xpTransactions(): HasMany
    {
        return $this->hasMany(XpTransaction::class);
    }

    /**
     * Get accessible URL for source photo.
     */
    public function getSourcePhotoUrlAttribute(): ?string
    {
        if (! $this->source_photo_path) {
            return null;
        }

        if (str_starts_with($this->source_photo_path, 'http://') || str_starts_with($this->source_photo_path, 'https://')) {
            return $this->source_photo_path;
        }

        return asset(ltrim($this->source_photo_path, '/'));
    }

    /**
     * Get accessible URL for forged portrait.
     */
    public function getPortraitUrlAttribute(): ?string
    {
        if (! $this->portrait_path) {
            return null;
        }

        if (str_starts_with($this->portrait_path, 'http://') || str_starts_with($this->portrait_path, 'https://')) {
            return $this->portrait_path;
        }

        return asset(ltrim($this->portrait_path, '/'));
    }

    /**
     * Calculate Max HP based on Databook Endurance: HP = Stat_Endurance * 100 (x 1.35 if Uzumaki).
     */
    public function getMaxHp(): int
    {
        $base = (int) round($this->stat_end * 100);
        if ($this->clan === 'Uzumaki') {
            return (int) round($base * 1.35);
        }

        return $base;
    }

    /**
     * Calculate Max Chakra based on Databook Ninjutsu: Chakra = Stat_Ninjutsu * 80.
     */
    public function getMaxChakra(): int
    {
        return (int) round($this->stat_nin * 80);
    }

    /**
     * Total Databook score (sum of 8 attributes).
     */
    public function getTotalDatabookScore(): float
    {
        return $this->stat_nin + $this->stat_tai + $this->stat_gen + $this->stat_int +
               $this->stat_str + $this->stat_spd + $this->stat_end + $this->stat_sea;
    }

    /**
     * Calculate Shinobi rank dynamically based on level.
     * Niv 1 à 4 : Genin
     * Niv 5 à 9 : Chūnin
     * Niv 10 à 14 : Jōnin
     * Niv 15+ : Kage / Anbu
     */
    public function getCalculatedRank(): string
    {
        if ($this->level >= 15) {
            return 'Kage';
        }

        if ($this->level >= 10) {
            return 'Jōnin';
        }

        if ($this->level >= 5) {
            return 'Chūnin';
        }

        return 'Genin';
    }

    /**
     * Sync ninja_rank field with current level progression.
     */
    public function syncRank(): self
    {
        $calculated = $this->getCalculatedRank();
        if ($this->ninja_rank !== $calculated) {
            $this->update(['ninja_rank' => $calculated]);
        }

        return $this;
    }

    /**
     * Snapshot representation for battle engine.
     *
     * @return array<string, mixed>
     */
    public function toSnapshot(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'clan' => $this->clan,
            'village' => $this->village,
            'ninja_rank' => $this->ninja_rank,
            'primary_element' => $this->primary_element,
            'secondary_element' => $this->secondary_element,
            'specialization' => $this->specialization,
            'stat_nin' => $this->stat_nin,
            'stat_tai' => $this->stat_tai,
            'stat_gen' => $this->stat_gen,
            'stat_int' => $this->stat_int,
            'stat_str' => $this->stat_str,
            'stat_spd' => $this->stat_spd,
            'stat_end' => $this->stat_end,
            'stat_sea' => $this->stat_sea,
            'max_hp' => $this->getMaxHp(),
            'max_chakra' => $this->getMaxChakra(),
            'level' => $this->level,
            'portrait_path' => $this->portrait_path,
        ];
    }
}
