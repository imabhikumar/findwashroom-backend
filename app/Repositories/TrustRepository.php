<?php
// app/Repositories/TrustRepository.php

namespace App\Repositories;

use App\Models\TrustEvent;
use App\Models\UserTrustScore;
use App\Models\Badge;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class TrustRepository
{
    public function addEvent(int $userId, string $eventType, string $category, int $scoreChange, array $data = []): TrustEvent
    {
        return TrustEvent::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'event_category' => $category,
            'score_change' => $scoreChange,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'reason' => $data['reason'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'occurred_at' => $data['occurred_at'] ?? now(),
        ]);
    }

    public function calculateTrustScore(int $userId): int
    {
        $events = TrustEvent::where('user_id', $userId)->get();
        
        $score = 0;
        foreach ($events as $event) {
            $score += $event->score_change;
        }

        // Ensure score is between 0 and 100
        return max(0, min(100, $score));
    }

    public function saveTrustScore(int $userId): UserTrustScore
    {
        $score = $this->calculateTrustScore($userId);
        
        return UserTrustScore::updateOrCreate(
            ['user_id' => $userId],
            [
                'score' => $score,
                'calculated_at' => now(),
            ]
        );
    }

    public function getTrustScore(int $userId): UserTrustScore
    {
        return UserTrustScore::where('user_id', $userId)->first();
    }

    public function getBadges(string $type = 'user'): \Illuminate\Database\Eloquent\Collection
    {
        return Badge::where('type', $type)
            ->where('is_active', true)
            ->get();
    }

    public function getUserBadges(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Badge::whereHas('users', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    }

    public function getPropertyBadges(int $propertyId): \Illuminate\Database\Eloquent\Collection
    {
        return Badge::whereHas('properties', function ($query) use ($propertyId) {
            $query->where('property_id', $propertyId);
        })->get();
    }

    public function assignBadgeToUser(int $userId, int $badgeId): void
    {
        DB::table('user_badges')->updateOrInsert(
            ['user_id' => $userId, 'badge_id' => $badgeId],
            ['awarded_at' => now(), 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function assignBadgeToProperty(int $propertyId, int $badgeId): void
    {
        DB::table('property_badges')->updateOrInsert(
            ['property_id' => $propertyId, 'badge_id' => $badgeId],
            ['awarded_at' => now(), 'created_at' => now(), 'updated_at' => now()]
        );
    }

    public function removeBadgeFromUser(int $userId, int $badgeId): void
    {
        DB::table('user_badges')
            ->where('user_id', $userId)
            ->where('badge_id', $badgeId)
            ->delete();
    }
}