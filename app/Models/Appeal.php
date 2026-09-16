<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appeal extends Model
{
    public $timestamps = false;
    protected $fillable = ['dispute_id', 'requested_by', 'reason', 'status', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];
    public function dispute(): BelongsTo { return $this->belongsTo(Dispute::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
}