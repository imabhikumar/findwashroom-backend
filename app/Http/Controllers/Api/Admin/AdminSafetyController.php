<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IncidentEscalateRequest;
use App\Http\Requests\Admin\IncidentStoreRequest;
use App\Http\Requests\Admin\IncidentUpdateRequest;
use App\Http\Requests\Admin\SosFalseAlarmRequest;
use App\Http\Requests\Admin\SosResolveRequest;
use App\Http\Resources\Admin\IncidentResource;
use App\Http\Resources\Admin\SafetyReportResource;
use App\Http\Resources\Admin\SosAlertResource;
use App\Services\Admin\SafetyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSafetyController extends Controller
{
    public function __construct(private readonly SafetyService $service) {}

    public function sos(Request $request): JsonResponse { return $this->paginated($this->service->sos($request), SosAlertResource::class, 'SOS alerts fetched successfully.'); }
    public function showSos(int $id): JsonResponse { return $this->successResponse('SOS alert fetched successfully.', new SosAlertResource($this->service->findSos($id))); }
    public function acknowledge(Request $request, int $id): JsonResponse { return $this->successResponse('SOS alert acknowledged successfully.', new SosAlertResource($this->service->acknowledge($id, $request))); }
    public function resolveSos(SosResolveRequest $request, int $id): JsonResponse { return $this->successResponse('SOS alert resolved successfully.', new SosAlertResource($this->service->resolveSos($id, $request->validated(), $request))); }
    public function falseAlarm(SosFalseAlarmRequest $request, int $id): JsonResponse { return $this->successResponse('SOS alert marked as false alarm.', new SosAlertResource($this->service->falseAlarm($id, $request->validated('reason'), $request))); }
    public function incidents(Request $request): JsonResponse { return $this->paginated($this->service->incidents($request), IncidentResource::class, 'Incidents fetched successfully.'); }
    public function showIncident(int $id): JsonResponse { return $this->successResponse('Incident fetched successfully.', new IncidentResource($this->service->findIncident($id))); }
    public function storeIncident(IncidentStoreRequest $request): JsonResponse { return $this->successResponse('Incident created successfully.', new IncidentResource($this->service->createIncident($request->validated(), $request)), 201); }
    public function updateIncident(IncidentUpdateRequest $request, int $id): JsonResponse { return $this->successResponse('Incident updated successfully.', new IncidentResource($this->service->updateIncident($id, $request->validated(), $request))); }
    public function escalateIncident(IncidentEscalateRequest $request, int $id): JsonResponse { return $this->successResponse('Incident escalated successfully.', new IncidentResource($this->service->escalateIncident($id, $request->validated(), $request))); }
    public function reports(): JsonResponse { return $this->successResponse('Safety reports fetched successfully.', SafetyReportResource::collection($this->service->reports())); }
    public function stats(): JsonResponse { return $this->successResponse('Safety statistics fetched successfully.', $this->service->stats()); }

    private function paginated($paginator, string $resource, string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $resource::collection($paginator->getCollection()), 'meta' => ['total' => $paginator->total(), 'page' => $paginator->currentPage(), 'per_page' => $paginator->perPage(), 'last_page' => $paginator->lastPage()]]);
    }
}