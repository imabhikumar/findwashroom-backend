<?php
// app/Models/TrustEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustEvent extends Model
{
    protected $fillable = [
        'user_id',
        'event_type',
        'event_category',
        'score_change',
        'reference_type',
        'reference_id',
        'reason',
        'metadata',
        'occurred_at',
    ];

    protected $casts = [
        'score_change' => 'integer',
        'metadata' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePositive($query)
    {
        return $query->where('event_category', 'positive');
    }

    public function scopeNegative($query)
    {
        return $query->where('event_category', 'negative');
    }

    public function scopeNeutral($query)
    {
        return $query->where('event_category', 'neutral');
    }
}