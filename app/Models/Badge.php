<?php
// app/Models/Badge.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Badge extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'type',
        'criteria',
        'min_trust_score',
        'is_auto_assign',
        'is_active',
    ];

    protected $casts = [
        'criteria' => 'array',
        'min_trust_score' => 'integer',
        'is_auto_assign' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('awarded_at')
            ->withTimestamps();
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_badges')
            ->withPivot('awarded_at')
            ->withTimestamps();
    }

    public function isEligibleForUser(User $user, int $trustScore): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->min_trust_score && $trustScore < $this->min_trust_score) {
            return false;
        }

        // Check dynamic criteria
        $criteria = $this->criteria;
        if (isset($criteria['event_type'])) {
            $count = TrustEvent::where('user_id', $user->id)
                ->where('event_type', $criteria['event_type'])
                ->count();
            
            if (isset($criteria['min_count']) && $count < $criteria['min_count']) {
                return false;
            }
        }

        return true;
    }
}