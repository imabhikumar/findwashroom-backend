<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintTimeline extends Model
{
    protected $table = 'complaint_timeline';

    public $timestamps = false;
    protected $fillable = ['complaint_id', 'actor_user_id', 'action', 'note', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function complaint(): BelongsTo { return $this->belongsTo(Complaint::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_user_id'); }
}