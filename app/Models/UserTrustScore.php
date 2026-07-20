<?php
// app/Models/UserTrustScore.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTrustScore extends Model
{
    protected $fillable = [
        'user_id',
        'score',
        'level',
        'calculated_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'calculated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getLevelAttribute($value)
    {
        if ($this->score >= 80) return 'premium';
        if ($this->score >= 60) return 'verified';
        if ($this->score >= 40) return 'trusted';
        if ($this->score >= 20) return 'basic';
        return 'unverified';
    }
}