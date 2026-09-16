<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use App\Traits\HasUUID;
use App\Traits\AuditLoggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{

    use HasUUID, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'owner_user_id',
        'name',
        'description',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'latitude',
        'longitude',
        'property_type',
        'status',
        'price_per_use',
        'average_rating',
        'total_reviews',
        'is_active'
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'price_per_use' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function serviceUnits(): HasMany
    {
        return $this->hasMany(ServiceUnit::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function complaints(): HasManyThrough
    {
        return $this->hasManyThrough(Complaint::class, Booking::class, 'property_id', 'booking_id', 'id', 'id');
    }
}
