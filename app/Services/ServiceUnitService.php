<?php

namespace App\Services;

use App\Repositories\ServiceUnitRepository;
use App\Models\ServiceUnit;
use Illuminate\Database\Eloquent\Collection;

class ServiceUnitService
{
    public function __construct(private readonly ServiceUnitRepository $repository)
    {
    }

    public function getPropertyServiceUnits(int $propertyId, bool $onlyAvailable = false): Collection
    {
        return $onlyAvailable
            ? $this->repository->getAvailableByProperty($propertyId)
            : $this->repository->getByProperty($propertyId);
    }

    public function getServiceTypes(): Collection
    {
        return $this->repository->getServiceTypes();
    }

    public function create(int $propertyId, array $data): ServiceUnit
    {
        $data['property_id'] = $propertyId;
        return $this->repository->create($data);
    }

    public function update(int $serviceUnitId, array $data): ?ServiceUnit
    {
        return $this->repository->update($serviceUnitId, $data);
    }

    public function updateStatus(int $serviceUnitId, string $status): ?ServiceUnit
    {
        $validStatuses = ['available', 'limited', 'busy', 'cleaning', 'closed', 'emergency'];
        if (!in_array($status, $validStatuses)) {
            throw new \InvalidArgumentException('Invalid status');
        }
        return $this->repository->updateStatus($serviceUnitId, $status);
    }

    public function getServiceUnitDetails(int $serviceUnitId): ?ServiceUnit
    {
        $serviceUnit = $this->repository->findById($serviceUnitId);
        if ($serviceUnit) {
            $serviceUnit->current_occupancy = $serviceUnit->getCurrentOccupancy();
            $serviceUnit->has_capacity = $serviceUnit->hasCapacity();
        }
        return $serviceUnit;
    }
}