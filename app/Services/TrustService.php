<?php
// app/Services/TrustService.php

namespace App\Services;

use App\Repositories\TrustRepository;
use App\Models\User;
use App\Models\Property;

class TrustService
{
    public function __construct(private readonly TrustRepository $repository)
    {
    }

    // === Event Recording ===
    public function recordEvent(int $userId, string $eventType, string $category, int $scoreChange, array $data = []): void
    {
        $this->repository->addEvent($userId, $eventType, $category, $scoreChange, $data);
        $this->recalculateAndSaveScore($userId);
        $this->checkAndAssignBadges($userId);
    }

    // === Score Management ===
    public function getTrustScore(int $userId): int
    {
        $score = $this->repository->getTrustScore($userId);
        return $score ? $score->score : 0;
    }

    public function getTrustLevel(int $userId): string
    {
        $score = $this->repository->getTrustScore($userId);
        return $score ? $score->level : 'unverified';
    }

    public function recalculateAndSaveScore(int $userId): void
    {
        $this->repository->saveTrustScore($userId);
    }

    // === Badge Management ===
    public function getUserBadges(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getUserBadges($userId);
    }

    public function getPropertyBadges(int $propertyId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->repository->getPropertyBadges($propertyId);
    }

    public function checkAndAssignBadges(int $userId): void
    {
        $user = User::find($userId);
        if (!$user) return;

        $score = $this->getTrustScore($userId);
        $badges = $this->repository->getBadges('user');

        foreach ($badges as $badge) {
            if ($badge->isEligibleForUser($user, $score)) {
                $this->repository->assignBadgeToUser($userId, $badge->id);
            }
        }
    }

    public function checkAndAssignPropertyBadges(int $propertyId): void
    {
        $property = Property::find($propertyId);
        if (!$property) return;

        $badges = $this->repository->getBadges('property');

        foreach ($badges as $badge) {
            // Check property criteria
            $criteria = $badge->criteria;
            
            if (isset($criteria['min_rating']) && $property->average_rating < $criteria['min_rating']) {
                continue;
            }
            
            if (isset($criteria['min_reviews']) && $property->total_reviews < $criteria['min_reviews']) {
                continue;
            }

            $this->repository->assignBadgeToProperty($propertyId, $badge->id);
        }
    }

    // === Pre-defined Event Helpers ===
    public function recordVerification(int $userId, string $type): void
    {
        $this->recordEvent(
            $userId,
            "verification_{$type}",
            'positive',
            10,
            ['reason' => "Verified {$type}", 'metadata' => ['type' => $type]]
        );
    }

    public function recordBookingCompleted(int $userId, int $bookingId): void
    {
        $this->recordEvent(
            $userId,
            'booking_completed',
            'positive',
            5,
            [
                'reference_type' => 'booking',
                'reference_id' => $bookingId,
                'reason' => 'Booking completed successfully'
            ]
        );
    }

    public function recordBookingCancelled(int $userId, int $bookingId): void
    {
        $this->recordEvent(
            $userId,
            'booking_cancelled',
            'negative',
            -3,
            [
                'reference_type' => 'booking',
                'reference_id' => $bookingId,
                'reason' => 'Booking cancelled'
            ]
        );
    }

    public function recordReviewReceived(int $userId, float $rating): void
    {
        $scoreChange = $rating >= 4 ? 5 : ($rating >= 3 ? 2 : -2);
        
        $this->recordEvent(
            $userId,
            'review_received',
            $rating >= 3 ? 'positive' : 'negative',
            $scoreChange,
            ['reason' => "Received {$rating}-star review", 'metadata' => ['rating' => $rating]]
        );
    }

    public function recordComplaintRaised(int $userId, int $complaintId): void
    {
        $this->recordEvent(
            $userId,
            'complaint_raised',
            'negative',
            -5,
            [
                'reference_type' => 'complaint',
                'reference_id' => $complaintId,
                'reason' => 'Complaint raised against user'
            ]
        );
    }

    public function recordComplaintResolved(int $userId): void
    {
        $this->recordEvent(
            $userId,
            'complaint_resolved',
            'positive',
            3,
            ['reason' => 'Complaint resolved successfully']
        );
    }

    public function recordAccountAgeBonus(int $userId): void
    {
        // Check if user account is 30+ days old
        $user = User::find($userId);
        if ($user && $user->created_at->diffInDays(now()) >= 30) {
            $this->recordEvent(
                $userId,
                'account_age',
                'positive',
                5,
                ['reason' => 'Account active for 30+ days']
            );
        }
    }
}