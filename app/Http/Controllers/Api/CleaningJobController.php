<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CleaningJob\StoreCleaningJobRequest;
use App\Http\Requests\CleaningJob\UploadCleaningProofRequest;
use App\Http\Controllers\Controller;
use App\Services\CleaningJobService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CleaningJobController extends Controller
{
    public function __construct(private readonly CleaningJobService $cleaningJobService)
    {
    }

    public function store(StoreCleaningJobRequest $request)
    {
        try {
            $job = $this->cleaningJobService->create((int) auth()->id(), $request->validated());
            return $this->successResponse('Cleaning job created successfully.', $job);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }

    public function index()
    {
        $jobs = $this->cleaningJobService->openJobs();
        return $this->successResponse('Open cleaning jobs fetched successfully.', $jobs);
    }

    public function accept(int $id)
    {
        try {
            $job = $this->cleaningJobService->accept((int) auth()->id(), $id);
            return $this->successResponse('Cleaning job accepted successfully.', $job);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        } catch (BadRequestHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 400);
        }
    }

    public function uploadProof(UploadCleaningProofRequest $request, int $id)
    {
        try {
            $proofPath = $request->file('proof')->store('cleaning-jobs', 'public');
            $job = $this->cleaningJobService->uploadProof((int) auth()->id(), $id, $proofPath);
            return $this->successResponse('Cleaning proof uploaded successfully.', $job);
        } catch (NotFoundHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 404);
        }
    }
}
