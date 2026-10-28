<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method',
        'phone_or_card',
        'transaction_reference',
        'user_note',
        'status',
        'admin_rejection_reason',
        'processed_by',
        'processed_at',
        'user_notified',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
            'user_notified' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'wave' => 'Wave',
            'orange_money' => 'Orange Money',
            'mtn_money' => 'MTN MoMo',
            'moov_money' => 'Moov Money',
            'card' => 'Carte Bancaire',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }
}
