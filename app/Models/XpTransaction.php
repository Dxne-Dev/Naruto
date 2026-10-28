<?php

namespace App\Models;

use Database\Factories\XpTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'fighter_id',
    'amount',
    'reason',
    'reference_type',
    'reference_id',
])]
class XpTransaction extends Model
{
    /** @use HasFactory<XpTransactionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'reference_id' => 'integer',
        ];
    }

    /**
     * The fighter awarded XP.
     */
    public function fighter(): BelongsTo
    {
        return $this->belongsTo(Fighter::class);
    }
}
