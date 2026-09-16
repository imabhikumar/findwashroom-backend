<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUUID;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Complaint extends Model
{
        use HasUUID, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'raised_by',
        'description',
        'evidence_image_path',
        'status',
        'admin_note',
        'complaint_number',
        'against_user_id',
        'against_property_id',
        'category',
        'priority',
        'sla_due_at',
        'resolved_at',
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'raised_by' => 'integer',
        'sla_due_at' => 'datetime',
        'resolved_at' => 'datetime',
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

    public function againstUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'against_user_id');
    }

    public function againstProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'against_property_id');
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(ComplaintEvidence::class);
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(ComplaintTimeline::class)->orderBy('created_at');
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }
}
