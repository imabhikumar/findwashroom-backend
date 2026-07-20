<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceUnit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'service_type_id',
        'name',
        'description',
        'capacity',
        'default_duration_minutes',
        'price',
        'pricing_model',
        'status',
        'operating_hours',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'default_duration_minutes' => 'integer',
        'price' => 'decimal:2',
        'operating_hours' => 'array',
        'is_active' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function bookingServiceUnits(): HasMany
    {
        return $this->hasMany(BookingServiceUnit::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function getCurrentOccupancy(): int
    {
        return $this->bookingServiceUnits()
            ->whereNull('ended_at')
            ->count();
    }

    public function hasCapacity(): bool
    {
        return $this->getCurrentOccupancy() < $this->capacity;
    }
}