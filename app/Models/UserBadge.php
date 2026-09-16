<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    protected $table = 'user_badges';
    protected $fillable = ['user_id', 'badge_id', 'awarded_at', 'awarded_by'];
    protected $casts = ['awarded_at' => 'datetime'];
}