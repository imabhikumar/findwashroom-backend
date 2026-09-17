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

    /**
     * Public discovery endpoint backing Customer Find Washroom.
     * Filters are intentionally limited to data represented by the current schema.
     */
    public function getPublicList(array $filters = []): Collection
    {
        $query = Property::query()
            ->where('is_active', true)
            ->where(function ($q) {
                // Older rows may not have a status. New rows should be approved
                // before appearing in public discovery.
                $q->whereNull('status')->orWhere('status', 'approved');
            });

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category'])) {
            $query->where('property_type', $filters['category']);
        }

        if (! empty($filters['availability'])) {
            $query->whereHas('serviceUnits', function ($serviceQuery) use ($filters) {
                $serviceQuery->where('status', $filters['availability'])
                    ->where('is_active', true);
            });
        }

        if (isset($filters['price_min'])) {
            $query->where('price_per_use', '>=', (float) $filters['price_min']);
        }

        if (isset($filters['price_max'])) {
            $query->where('price_per_use', '<=', (float) $filters['price_max']);
        }

        if (! empty($filters['price_range'])) {
            [$min, $max] = $this->parsePriceRange($filters['price_range']);
            if ($min !== null) {
                $query->where('price_per_use', '>=', $min);
            }
            if ($max !== null) {
                $query->where('price_per_use', '<=', $max);
            }
        }

        // Women Safe / Family Safe are represented by property badges in the
        // current trust schema. Accessibility has no backing table in the
        // current migration set, so it is intentionally not guessed here.
        if ($this->truthy($filters['women_safe'] ?? false)) {
            $this->whereBadge($query, 'women safe');
        }

        if ($this->truthy($filters['family_safe'] ?? false)) {
            $this->whereBadge($query, 'family safe');
        }

        if (! empty($filters['badge'])) {
            $this->whereBadge($query, (string) $filters['badge']);
        }

        /** @var Collection $properties */
        $properties = $query->latest('id')->get();

        $lat = isset($filters['lat']) ? (float) $filters['lat'] : null;
        $lng = isset($filters['lng']) ? (float) $filters['lng'] : null;
        $radius = isset($filters['radius']) ? (float) $filters['radius'] : null;
        $hasOrigin = $lat !== null && $lng !== null;

        if ($hasOrigin) {
            $properties = $properties->map(function (Property $property) use ($lat, $lng) {
                $property->distance_km = $this->distanceKm(
                    $lat,
                    $lng,
                    $property->latitude !== null ? (float) $property->latitude : null,
                    $property->longitude !== null ? (float) $property->longitude : null,
                );
                return $property;
            });

            if ($radius !== null) {
                $properties = $properties->filter(
                    fn (Property $property) => $property->distance_km !== null && $property->distance_km <= $radius
                );
            }
        }

        $sort = $filters['sort'] ?? ($hasOrigin ? 'distance' : 'rating');
        $properties = match ($sort) {
            'distance' => $hasOrigin
                ? $properties->sortBy(fn (Property $property) => $property->distance_km ?? PHP_FLOAT_MAX)
                : $properties->sortByDesc('average_rating'),
            'price_asc' => $properties->sortBy('price_per_use'),
            'price_desc' => $properties->sortByDesc('price_per_use'),
            'name' => $properties->sortBy(fn (Property $property) => strtolower((string) $property->name)),
            default => $properties->sortByDesc('average_rating'),
        };

        $limit = (int) ($filters['limit'] ?? 100);
        return $properties->values()->take($limit);
    }

    public function findById(int $id): ?Property
    {
        return Property::find($id);
    }

    private function whereBadge($query, string $badge): void
    {
        $needle = mb_strtolower(trim($badge));
        $query->whereExists(function ($subQuery) use ($needle) {
            $subQuery->selectRaw('1')
                ->from('property_badges')
                ->join('badges', 'badges.id', '=', 'property_badges.badge_id')
                ->whereColumn('property_badges.property_id', 'properties.id')
                ->where(function ($q) use ($needle) {
                    $q->whereRaw('LOWER(badges.name) = ?', [$needle])
                        ->orWhereRaw('LOWER(badges.slug) = ?', [$needle]);
                });
        });
    }

    private function parsePriceRange(string $range): array
    {
        $parts = array_map('trim', explode(',', $range, 2));
        $min = ($parts[0] ?? '') !== '' && is_numeric($parts[0]) ? (float) $parts[0] : null;
        $max = ($parts[1] ?? '') !== '' && is_numeric($parts[1]) ? (float) $parts[1] : null;
        return [$min, $max];
    }

    private function truthy(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private function distanceKm(float $lat1, float $lng1, ?float $lat2, ?float $lng2): ?float
    {
        if ($lat2 === null || $lng2 === null) {
            return null;
        }

        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return round($earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }
}
