<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthOtp extends Model
{
    protected $fillable = [
        'channel',
        'identifier',
        'otp_hash',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];
}

