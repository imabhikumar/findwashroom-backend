<?php

namespace App\Repositories;

use App\Models\ServiceUnit;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Collection;

class ServiceUnitRepository
{
    public function getByProperty(int $propertyId): Collection
    {
        return ServiceUnit::with(['serviceType'])
            ->where('property_id', $propertyId)
            ->where('is_active', true)
            ->get();
    }

    public function getAvailableByProperty(int $propertyId): Collection
    {
        return ServiceUnit::with(['serviceType'])
            ->where('property_id', $propertyId)
            ->where('is_active', true)
            ->where('status', 'available')
            ->get();
    }

    public function findById(int $id): ?ServiceUnit
    {
        return ServiceUnit::with(['property', 'serviceType'])->find($id);
    }

    public function create(array $data): ServiceUnit
    {
        return ServiceUnit::create($data);
    }

    public function update(int $id, array $data): ?ServiceUnit
    {
        $serviceUnit = $this->findById($id);
        if ($serviceUnit) {
            $serviceUnit->update($data);
        }
        return $serviceUnit;
    }

    public function updateStatus(int $id, string $status): ?ServiceUnit
    {
        $serviceUnit = $this->findById($id);
        if ($serviceUnit) {
            $serviceUnit->update(['status' => $status]);
        }
        return $serviceUnit;
    }

    public function getServiceTypes(): Collection
    {
        return ServiceType::where('is_active', true)->get();
    }
}