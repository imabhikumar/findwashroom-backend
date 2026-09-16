<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_group',
        'description',
        'updated_by',
    ];

    protected $casts = ['setting_value' => 'array'];
}