<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtension extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'booking_id',
        'duration_added',
        'amount',
        'approved_by',
        'created_at',
    ];

    protected $casts = [
        'duration_added' => 'integer',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }
}