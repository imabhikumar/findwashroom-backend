<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = ['reported_by', 'category', 'severity', 'status', 'escalation_level', 'description'];
    protected $casts = ['escalation_level' => 'integer'];
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reported_by'); }
}