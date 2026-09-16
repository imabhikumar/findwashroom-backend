<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasUUID;
use App\Traits\AuditLoggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
        use HasUUID, AuditLoggable, SoftDeletes;

    protected $fillable = [
        'property_id',
        'customer_id',
        'customer_user_id',
        'booking_number',
        'booking_type',
        'scheduled_at',
        'started_at',
        'ended_at',
        'total_amount',
        'start_time',
        'end_time',
        'amount',
        'status',
        'payment_status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'amount' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function serviceUnits(): HasMany
    {
        return $this->hasMany(BookingServiceUnit::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(BookingProduct::class);
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(BookingExtension::class)->latest('id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(BookingEvent::class)->orderBy('created_at');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
