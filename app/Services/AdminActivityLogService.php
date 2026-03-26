<?php

namespace App\Services;

use App\Repositories\AdminActivityLogRepository;
use Illuminate\Database\Eloquent\Collection;

class AdminActivityLogService
{
    public function __construct(private readonly AdminActivityLogRepository $adminActivityLogRepository)
    {
    }

    public function log(?int $adminId, string $actionType, ?string $description, ?string $ipAddress, ?string $userAgent): void
    {
        $this->adminActivityLogRepository->log($adminId, $actionType, $description, $ipAddress, $userAgent);
    }

    public function lastDays(?int $adminId, int $days = 30, int $limit = 100): Collection
    {
        return $this->adminActivityLogRepository->getLastDaysLogs($adminId, $days, $limit);
    }

    public function suspicious(int $days = 30, int $minDistinctIps = 3, int $limit = 50): Collection
    {
        return $this->adminActivityLogRepository->getSuspiciousAdmins($days, $minDistinctIps, $limit);
    }
}

