<?php
// app/Listeners/UpdateTrustOnReview.php

namespace App\Listeners;

use App\Events\ReviewSubmitted;
use App\Services\TrustService;

class UpdateTrustOnReview
{
    public function __construct(private readonly TrustService $trustService)
    {
    }

    public function handle(ReviewSubmitted $event): void
    {
        $review = $event->review;
        
        // Update reviewer's trust (positive for submitting review)
        $this->trustService->recordEvent(
            $review->reviewer_id,
            'review_submitted',
            'positive',
            2,
            ['reference_type' => 'review', 'reference_id' => $review->id]
        );
        
        // Update property owner's trust based on rating
        $this->trustService->recordReviewReceived(
            $review->property->owner_id,
            $review->rating
        );
    }
}