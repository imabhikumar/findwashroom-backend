<?php

namespace App\Services;

use App\Repositories\AdminDashboardRepository;

class AdminDashboardService
{
    public function __construct(private readonly AdminDashboardRepository $adminDashboardRepository)
    {
    }

    public function getDashboardStats(): array
    {
        return $this->adminDashboardRepository->getDashboardStats();
    }
}

