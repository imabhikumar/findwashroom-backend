<?php
// app/Listeners/UpdateTrustOnBookingCompleted.php

namespace App\Listeners;

use App\Events\BookingCompleted;
use App\Services\TrustService;

class UpdateTrustOnBookingCompleted
{
    public function __construct(private readonly TrustService $trustService)
    {
    }

    public function handle(BookingCompleted $event): void
    {
        $booking = $event->booking;
        
        // Customer trust
        $this->trustService->recordBookingCompleted(
            $booking->customer_id,
            $booking->id
        );
        
        // Property owner trust
        $this->trustService->recordBookingCompleted(
            $booking->property->owner_id,
            $booking->id
        );
    }
}