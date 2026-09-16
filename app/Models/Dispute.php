<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    protected $fillable = ['complaint_id', 'status', 'resolution', 'resolved_by'];
    public function complaint(): BelongsTo { return $this->belongsTo(Complaint::class); }
    public function resolver(): BelongsTo { return $this->belongsTo(Admin::class, 'resolved_by'); }
    public function appeals(): HasMany { return $this->hasMany(Appeal::class); }
}