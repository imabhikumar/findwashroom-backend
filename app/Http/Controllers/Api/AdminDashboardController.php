<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(private readonly AdminDashboardService $adminDashboardService)
    {
    }

    public function index(Request $request)
    {
        $data = $this->adminDashboardService->getDashboardStats();

        return $this->successResponse('Dashboard stats fetched.', $data);
    }
}

