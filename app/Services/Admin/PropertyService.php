<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\PropertyRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyService
{
    public function __construct(private readonly PropertyRepository $repository)
    {
    }

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginate($request->query());
    }

    public function findOrFail(int $id)
    {
        return $this->repository->findAdminPropertyOrFail($id);
    }

    public function create(array $data, Request $request)
    {
        return DB::transaction(function () use ($data, $request) {
            $property = $this->repository->create($data);
            $this->audit($request, 'create', null, $property->toArray(), $property->id);
            return $property->load('owner');
        });
    }

    public function update(int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $data, $request) {
            $property = $this->repository->findAdminPropertyOrFail($id);
            $old = $property->toArray();
            $property = $this->repository->update($property, $data);
            $this->audit($request, 'update', $old, $property->toArray(), $property->id);
            return $property->load('owner');
        });
    }

    public function changeStatus(int $id, string $status, ?string $reason, Request $request)
    {
        return DB::transaction(function () use ($id, $status, $reason, $request) {
            $property = $this->repository->findAdminPropertyOrFail($id);
            $old = $property->toArray();
            $property = $this->repository->setStatus($property, $status);
            $action = match ($status) {
                'approved' => 'approve',
                'rejected' => 'reject',
                default => $status,
            };
            $this->audit($request, $action, $old, [
                'status' => $status,
                ...($reason ? ['reason' => $reason] : []),
            ], $property->id);
            return $property->load('owner');
        });
    }

    public function delete(int $id, Request $request): void
    {
        DB::transaction(function () use ($id, $request) {
            $property = $this->repository->findAdminPropertyOrFail($id);
            $old = $property->toArray();
            $this->repository->delete($property);
            $this->audit($request, 'delete', $old, null, $property->id);
        });
    }

    private function audit(Request $request, string $action, ?array $old, ?array $new, int $entityId): void
    {
        DB::table('audit_logs')->insert([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()?->getKey(),
            'user_type' => Admin::class,
            'module' => 'properties',
            'action' => $action,
            'entity_type' => 'Property',
            'entity_id' => $entityId,
            'old_data' => $old ? json_encode($old) : null,
            'new_data' => $new ? json_encode($new) : null,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}