<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SosAlert extends Model
{
    protected $table = 'sos_alerts';
    protected $fillable = ['user_id', 'booking_id', 'latitude', 'longitude', 'status', 'acknowledged_by', 'acknowledged_at', 'resolved_at'];
    protected $casts = ['latitude' => 'decimal:7', 'longitude' => 'decimal:7', 'acknowledged_at' => 'datetime', 'resolved_at' => 'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}