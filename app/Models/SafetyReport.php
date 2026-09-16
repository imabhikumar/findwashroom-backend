<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafetyReport extends Model
{
    protected $fillable = ['reported_by', 'against_user_id', 'against_property_id', 'category', 'description'];
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reported_by'); }
    public function againstUser(): BelongsTo { return $this->belongsTo(User::class, 'against_user_id'); }
    public function againstProperty(): BelongsTo { return $this->belongsTo(Property::class, 'against_property_id'); }
}