<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintEvidence extends Model
{
    protected $table = 'complaint_evidence';

    public $timestamps = false;
    protected $fillable = ['complaint_id', 'evidence_type', 'file_url', 'uploaded_by', 'created_at'];
    protected $casts = ['created_at' => 'datetime'];

    public function complaint(): BelongsTo { return $this->belongsTo(Complaint::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
}