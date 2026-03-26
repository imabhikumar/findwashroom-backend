<?php

namespace App\Repositories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;

class PropertyRepository
{
    public function create(array $payload): Property
    {
        return Property::create($payload);
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
