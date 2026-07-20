<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUUID;
use App\Traits\AuditLoggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
        use HasUUID, AuditLoggable, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'raised_by',
        'description',
        'evidence_image_path',
        'status',
        'admin_note',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'raised_by' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }

    public function raisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'raised_by');
    }
}
