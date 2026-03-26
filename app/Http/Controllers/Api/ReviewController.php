<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReviewController extends Controller
{
    public function __construct(private readonly ReviewService $reviewService)
    {
    }

    public function store(StoreReviewRequest $request)
    {
        try {
            $review = $this->reviewService->create((int) auth()->id(), $request->validated());
            return $this->successResponse('Review submitted successfully.', $review);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        } catch (BadRequestHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }
}
