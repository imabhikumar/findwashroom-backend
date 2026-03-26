<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminActivityLogService;
use Illuminate\Http\Request;

class AdminActivityController extends Controller
{
    public function __construct(private readonly AdminActivityLogService $adminActivityLogService)
    {
    }

    public function index(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $limit = (int) $request->query('limit', 100);

        $admin = $request->user();

        $logs = $this->adminActivityLogService->lastDays($admin?->id, $days, $limit);

        return $this->successResponse('Activity logs fetched.', [
            'logs' => $logs,
        ]);
    }

    public function suspicious(Request $request)
    {
        $days = (int) $request->query('days', 30);
        $minDistinctIps = (int) $request->query('minDistinctIps', 3);
        $limit = (int) $request->query('limit', 50);

        $items = $this->adminActivityLogService->suspicious($days, $minDistinctIps, $limit);

        return $this->successResponse('Suspicious activity fetched.', [
            'suspicious' => $items,
        ]);
    }
}

