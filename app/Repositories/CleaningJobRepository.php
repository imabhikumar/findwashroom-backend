<?php

namespace App\Repositories;

use App\Models\CleaningJob;
use Illuminate\Database\Eloquent\Collection;

class CleaningJobRepository
{
    public function create(array $payload): CleaningJob
    {
        return CleaningJob::create($payload);
    }

    public function getOpenJobs(): Collection
    {
        return CleaningJob::query()
            ->with(['property', 'owner'])
            ->where('status', 'open')
            ->latest('id')
            ->get();
    }

    public function findById(int $jobId): ?CleaningJob
    {
        return CleaningJob::find($jobId);
    }

    public function findByIdAndOwner(int $jobId, int $ownerId): ?CleaningJob
    {
        return CleaningJob::query()
            ->where('id', $jobId)
            ->where('owner_id', $ownerId)
            ->first();
    }

    public function update(CleaningJob $cleaningJob, array $payload): CleaningJob
    {
        $cleaningJob->update($payload);
        return $cleaningJob->refresh();
    }
}
