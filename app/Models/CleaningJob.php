<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CleaningJob extends Model
{
    protected $fillable = [
        'property_id',
        'owner_id',
        'price_offer',
        'assigned_cleaner_id',
        'status',
        'proof_image_path',
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
