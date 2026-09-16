<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyBadge extends Model
{
    protected $table = 'property_badges';
    protected $fillable = ['property_id', 'badge_id', 'awarded_at', 'awarded_by'];
    protected $casts = ['awarded_at' => 'datetime'];
}