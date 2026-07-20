<?php
// app/Http/Controllers/Api/TrustController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrustScoreResource;
use App\Http\Resources\BadgeResource;
use App\Services\TrustService;
use Illuminate\Http\Request;

class TrustController extends Controller
{
    public function __construct(private readonly TrustService $trustService)
    {
    }

    public function myTrustScore(Request $request)
    {
        $userId = $request->user()->id;
        
        return $this->successResponse('Trust score fetched.', [
            'score' => $this->trustService->getTrustScore($userId),
            'level' => $this->trustService->getTrustLevel($userId),
        ]);
    }

    public function myBadges(Request $request)
    {
        $userId = $request->user()->id;
        $badges = $this->trustService->getUserBadges($userId);
        
        return $this->successResponse('Badges fetched.', BadgeResource::collection($badges));
    }

    public function propertyBadges(int $propertyId)
    {
        $badges = $this->trustService->getPropertyBadges($propertyId);
        
        return $this->successResponse('Property badges fetched.', BadgeResource::collection($badges));
    }

    public function trustSummary(Request $request)
    {
        $userId = $request->user()->id;
        
        return $this->successResponse('Trust summary fetched.', [
            'score' => $this->trustService->getTrustScore($userId),
            'level' => $this->trustService->getTrustLevel($userId),
            'badges' => BadgeResource::collection($this->trustService->getUserBadges($userId)),
        ]);
    }
}