<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Jutsu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'element',
        'base_power',
        'chakra_cost',
        'rank',
        'clan_required',
        'description',
    ];

    /**
     * Fighters knowing this jutsu.
     */
    public function fighters(): BelongsToMany
    {
        return $this->belongsToMany(Fighter::class, 'fighter_jutsu');
    }
}
