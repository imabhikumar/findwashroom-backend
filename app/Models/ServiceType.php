// app/Models/ServiceType.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'default_duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'default_duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function serviceUnits(): HasMany
    {
        return $this->hasMany(ServiceUnit::class);
    }
}