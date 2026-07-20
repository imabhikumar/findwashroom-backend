<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUUID;
use App\Traits\AuditLoggable;
use Illuminate\Database\Eloquent\SoftDeletes;

class CleaningJob extends Model
{
        use HasUUID, AuditLoggable, SoftDeletes;

    protected $fillable = [
        'property_id',
        'owner_id',
        'price_offer',
        'assigned_cleaner_id',
        'status',
        'proof_image_path',
    ];

    protected $casts = [
        'price_offer' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function cleaner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_cleaner_id');
    }
}
