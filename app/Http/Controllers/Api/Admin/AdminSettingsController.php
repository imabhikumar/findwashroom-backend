<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CancellationRuleRequest;
use App\Http\Requests\Admin\CommissionRuleRequest;
use App\Http\Requests\Admin\RefundRuleRequest;
use App\Http\Requests\Admin\SettingsBulkUpdateRequest;
use App\Services\Admin\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function __construct(private readonly SettingsService $service) {}

    public function index(): JsonResponse
    {
        return $this->successResponse('Settings fetched successfully.', $this->service->settings());
    }

    public function update(SettingsBulkUpdateRequest $request): JsonResponse
    {
        return $this->successResponse('Settings updated successfully.', $this->service->updateSettings($request->validated('settings'), $request));
    }

    public function commissionRules(): JsonResponse { return $this->successResponse('Commission rules fetched successfully.', $this->service->rules('commission')); }
    public function storeCommission(CommissionRuleRequest $request): JsonResponse { return $this->successResponse('Commission rule created successfully.', $this->service->createRule('commission', $request->validated(), $request), 201); }
    public function updateCommission(CommissionRuleRequest $request, int $id): JsonResponse { return $this->successResponse('Commission rule updated successfully.', $this->service->updateRule('commission', $id, $request->validated(), $request)); }
    public function deleteCommission(Request $request, int $id): JsonResponse { $this->service->deleteRule('commission', $id, $request); return $this->successResponse('Commission rule deleted successfully.'); }

    public function refundRules(): JsonResponse { return $this->successResponse('Refund rules fetched successfully.', $this->service->rules('refund')); }
    public function storeRefund(RefundRuleRequest $request): JsonResponse { return $this->successResponse('Refund rule created successfully.', $this->service->createRule('refund', $request->validated(), $request), 201); }
    public function updateRefund(RefundRuleRequest $request, int $id): JsonResponse { return $this->successResponse('Refund rule updated successfully.', $this->service->updateRule('refund', $id, $request->validated(), $request)); }
    public function deleteRefund(Request $request, int $id): JsonResponse { $this->service->deleteRule('refund', $id, $request); return $this->successResponse('Refund rule deleted successfully.'); }

    public function cancellationRules(): JsonResponse { return $this->successResponse('Cancellation rules fetched successfully.', $this->service->rules('cancellation')); }
    public function storeCancellation(CancellationRuleRequest $request): JsonResponse { return $this->successResponse('Cancellation rule created successfully.', $this->service->createRule('cancellation', $request->validated(), $request), 201); }
    public function updateCancellation(CancellationRuleRequest $request, int $id): JsonResponse { return $this->successResponse('Cancellation rule updated successfully.', $this->service->updateRule('cancellation', $id, $request->validated(), $request)); }
    public function deleteCancellation(Request $request, int $id): JsonResponse { $this->service->deleteRule('cancellation', $id, $request); return $this->successResponse('Cancellation rule deleted successfully.'); }
}