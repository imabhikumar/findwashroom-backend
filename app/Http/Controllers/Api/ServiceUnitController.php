// app/Http/Controllers/Api/ServiceUnitController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceUnit\CreateServiceUnitRequest;
use App\Http\Requests\ServiceUnit\UpdateServiceUnitRequest;
use App\Http\Resources\ServiceUnitResource;
use App\Services\ServiceUnitService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceUnitController extends Controller
{
    public function __construct(private readonly ServiceUnitService $serviceUnitService)
    {
    }

    public function index(int $propertyId)
    {
        $units = $this->serviceUnitService->getPropertyServiceUnits($propertyId);
        return $this->successResponse('Service units fetched.', ServiceUnitResource::collection($units));
    }

    public function available(int $propertyId)
    {
        $units = $this->serviceUnitService->getPropertyServiceUnits($propertyId, true);
        return $this->successResponse('Available service units fetched.', ServiceUnitResource::collection($units));
    }

    public function types()
    {
        $types = $this->serviceUnitService->getServiceTypes();
        return $this->successResponse('Service types fetched.', $types);
    }

    public function store(CreateServiceUnitRequest $request)
    {
        $unit = $this->serviceUnitService->create(
            (int) $request->validated('property_id'),
            $request->validated()
        );
        return $this->successResponse('Service unit created.', new ServiceUnitResource($unit));
    }

    public function show(int $id)
    {
        $unit = $this->serviceUnitService->getServiceUnitDetails($id);
        if (!$unit) {
            throw new NotFoundHttpException('Service unit not found');
        }
        return $this->successResponse('Service unit details.', new ServiceUnitResource($unit));
    }

    public function update(UpdateServiceUnitRequest $request, int $id)
    {
        $unit = $this->serviceUnitService->update($id, $request->validated());
        if (!$unit) {
            throw new NotFoundHttpException('Service unit not found');
        }
        return $this->successResponse('Service unit updated.', new ServiceUnitResource($unit));
    }

    public function status(int $id, string $status)
    {
        $unit = $this->serviceUnitService->updateStatus($id, $status);
        if (!$unit) {
            throw new NotFoundHttpException('Service unit not found');
        }
        return $this->successResponse('Service unit status updated.', new ServiceUnitResource($unit));
    }
}