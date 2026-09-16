<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportFilterRequest;
use App\Services\Admin\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function __construct(private readonly ReportService $service)
    {
    }

    public function bookings(ReportFilterRequest $request): JsonResponse
    {
        return $this->successResponse(
            'Booking report fetched successfully.',
            $this->service->bookings($request->validated())
        );
    }

    public function revenue(ReportFilterRequest $request): JsonResponse
    {
        return $this->successResponse(
            'Revenue report fetched successfully.',
            $this->service->revenue($request->validated())
        );
    }

    public function complaints(ReportFilterRequest $request): JsonResponse
    {
        return $this->successResponse(
            'Complaint report fetched successfully.',
            $this->service->complaints($request->validated())
        );
    }

    public function trust(ReportFilterRequest $request): JsonResponse
    {
        return $this->successResponse(
            'Trust report fetched successfully.',
            $this->service->trust($request->validated())
        );
    }

    public function safety(ReportFilterRequest $request): JsonResponse
    {
        return $this->successResponse(
            'Safety report fetched successfully.',
            $this->service->safety($request->validated())
        );
    }

    public function export(ReportFilterRequest $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $type = $request->validate([
            'type' => ['required', 'in:bookings,revenue,complaints'],
        ])['type'];

        return $this->service->export($type, $request->validated());
    }
}