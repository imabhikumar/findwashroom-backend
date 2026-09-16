<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = ['rule_name', 'conditions', 'commission_type', 'value', 'priority', 'is_active'];
    protected $casts = ['conditions' => 'array', 'value' => 'decimal:2', 'priority' => 'integer', 'is_active' => 'boolean'];
}