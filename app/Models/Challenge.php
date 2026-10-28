<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Challenge extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'sender_fighter_id',
        'receiver_fighter_id',
        'status',
        'battle_id',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function senderFighter(): BelongsTo
    {
        return $this->belongsTo(Fighter::class, 'sender_fighter_id');
    }

    public function receiverFighter(): BelongsTo
    {
        return $this->belongsTo(Fighter::class, 'receiver_fighter_id');
    }

    public function battle(): BelongsTo
    {
        return $this->belongsTo(Battle::class, 'battle_id');
    }
}
