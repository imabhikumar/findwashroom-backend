<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    protected $fillable = [
        'wallet_id',
        'user_id',
        'amount',
        'payout_method',
        'account_details',
        'status',
        'reference_id',
        'requested_at',
        'processed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'account_details' => 'array',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
