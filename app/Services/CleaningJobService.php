<?php

namespace App\Services;

use App\Repositories\CleaningJobRepository;
use App\Repositories\PropertyRepository;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CleaningJobService
{
    public function __construct(
        private readonly CleaningJobRepository $cleaningJobRepository,
        private readonly PropertyRepository $propertyRepository
    ) {
    }

    public function create(int $ownerId, array $payload)
    {
        $property = $this->propertyRepository->findByOwnerAndId($ownerId, (int) $payload['property_id']);
        if (! $property) {
            throw new NotFoundHttpException('Property not found for this owner.');
        }

        return $this->cleaningJobRepository->create([
            'property_id' => $property->id,
            'owner_id' => $ownerId,
            'price_offer' => $payload['price_offer'],
            'status' => 'open',
        ]);
    }

    public function openJobs()
    {
        return $this->cleaningJobRepository->getOpenJobs();
    }

    public function accept(int $cleanerId, int $jobId)
    {
        return DB::transaction(function () use ($cleanerId, $jobId) {
            $job = $this->cleaningJobRepository->findOpenByIdForUpdate($jobId);
            if (! $job) {
                throw new NotFoundHttpException('Cleaning job not found or already assigned.');
            }

            return $this->cleaningJobRepository->update($job, [
                'assigned_cleaner_id' => $cleanerId,
                'status' => 'assigned',
            ]);
        });
    }

    public function uploadProof(int $cleanerId, int $jobId, string $proofPath)
    {
        $job = $this->cleaningJobRepository->findById($jobId);
        if (! $job) {
            throw new NotFoundHttpException('Cleaning job not found.');
        }
        if ((int) $job->assigned_cleaner_id !== $cleanerId) {
            throw new NotFoundHttpException('Cleaning job not assigned to this cleaner.');
        }
        if ($job->status !== 'assigned') {
            throw new BadRequestHttpException('Proof can be uploaded only for assigned jobs.');
        }

        return $this->cleaningJobRepository->update($job, [
            'proof_image_path' => $proofPath,
            'status' => 'completed',
        ]);
    }
}
