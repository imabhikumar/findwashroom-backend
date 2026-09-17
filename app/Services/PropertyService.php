<?php

namespace App\Services;

use App\Repositories\PropertyRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PropertyService
{
    public function __construct(private readonly PropertyRepository $propertyRepository)
    {
    }

    public function create(int $ownerId, array $payload)
    {
        $payload['owner_id'] = $ownerId;
        return $this->propertyRepository->create($payload);
    }

    public function ownerList(int $ownerId)
    {
        return $this->propertyRepository->getByOwner($ownerId);
    }

    public function update(int $ownerId, int $propertyId, array $payload)
    {
        $property = $this->propertyRepository->findByOwnerAndId($ownerId, $propertyId);
        if (! $property) {
            throw new NotFoundHttpException('Property not found.');
        }

        return $this->propertyRepository->update($property, $payload);
    }

    public function publicList(array $filters = [])
    {
        return $this->propertyRepository->getPublicList($filters);
    }

    public function detail(int $id)
    {
        $property = $this->propertyRepository->findById($id);
        if (! $property) {
            throw new NotFoundHttpException('Property not found.');
        }

        return $property;
    }
}
