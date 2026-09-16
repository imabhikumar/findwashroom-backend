<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DisputeResolveRequest;
use App\Services\Admin\ComplaintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminDisputeController extends Controller
{
    public function __construct(private readonly ComplaintService $service) {}

    public function resolve(DisputeResolveRequest $request, int $id): JsonResponse
    {
        return $this->successResponse('Dispute resolved successfully.', $this->service->resolveDispute($id, $request->validated('resolution'), $request));
    }

    public function rejectAppeal(Request $request, int $id): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string', 'min:5']]);
        return $this->successResponse('Appeal rejected successfully.', $this->service->rejectAppeal($id, $request->input('reason'), $request));
    }
}