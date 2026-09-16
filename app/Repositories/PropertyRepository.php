<?php

namespace App\Repositories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class PropertyRepository
{
    public function create(array $payload): Property
    {
        $payload['owner_id'] = $payload['owner_user_id'];
        return Property::create($payload);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Property::query()->with('owner');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['city'])) {
            $query->where('city', $filters['city']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%"));
        }

        return $query->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findAdminPropertyOrFail(int $id): Property
    {
        $query = Property::query()->with('owner');

        if (Schema::hasTable('service_units')) {
            $query->with('serviceUnits');
        }

        return $query->findOrFail($id);
    }

    public function setStatus(Property $property, string $status): Property
    {
        $property->forceFill(['status' => $status])->save();
        return $property->refresh();
    }

    public function delete(Property $property): void
    {
        $property->delete();
    }

    public function getByOwner(int $ownerId): Collection
    {
        return Property::query()->where('owner_id', $ownerId)->latest('id')->get();
    }

    public function findByOwnerAndId(int $ownerId, int $propertyId): ?Property
    {
        return Property::query()
            ->where('owner_id', $ownerId)
            ->where('id', $propertyId)
            ->first();
    }

    public function update(Property $property, array $payload): Property
    {
        $property->update($payload);
        return $property->refresh();
    }

    public function getPublicList(): Collection
    {
        return Property::query()->where('is_active', true)->latest('id')->get();
    }

    public function findById(int $id): ?Property
    {
        return Property::find($id);
    }
}
