<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRule extends Model
{
    protected $fillable = ['rule_name', 'conditions', 'refund_type', 'value', 'priority', 'is_active'];
    protected $casts = ['conditions' => 'array', 'value' => 'decimal:2', 'priority' => 'integer', 'is_active' => 'boolean'];
}