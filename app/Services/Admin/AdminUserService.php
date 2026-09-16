<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\AdminUserRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminUserService
{
    public function __construct(private readonly AdminUserRepository $repository)
    {
    }

    public function paginate(Request $request): LengthAwarePaginator
    {
        return $this->repository->paginate($request->query());
    }

    public function findOrFail(int $id)
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data, Request $request)
    {
        return DB::transaction(function () use ($data, $request) {
            $user = $this->repository->create($data);
            $this->audit($request, 'create', null, $user->toArray(), $user->id);
            return $user;
        });
    }

    public function update(int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $data, $request) {
            $user = $this->repository->findOrFail($id);
            $old = $user->toArray();
            $user = $this->repository->update($user, $data);
            $this->audit($request, 'update', $old, $user->toArray(), $user->id);
            return $user;
        });
    }

    public function changeStatus(int $id, string $status, ?string $reason, Request $request)
    {
        return DB::transaction(function () use ($id, $status, $reason, $request) {
            $user = $this->repository->findOrFail($id);
            $old = $user->toArray();
            $user = $this->repository->setStatus($user, $status);
            $action = match ($status) {
                'suspended' => 'suspend',
                'banned' => 'ban',
                default => 'reactivate',
            };
            $this->audit($request, $action, $old, [
                'status' => $status,
                ...($reason ? ['reason' => $reason] : []),
            ], $user->id);
            return $user;
        });
    }

    public function delete(int $id, Request $request): void
    {
        DB::transaction(function () use ($id, $request) {
            $user = $this->repository->findOrFail($id);
            $old = $user->toArray();
            $this->repository->delete($user);
            $this->audit($request, 'delete', $old, null, $user->id);
        });
    }

    private function audit(Request $request, string $action, ?array $old, ?array $new, int $entityId): void
    {
        DB::table('audit_logs')->insert([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()?->getKey(),
            'user_type' => Admin::class,
            'action' => $action,
            'module' => 'users',
            'entity_type' => 'User',
            'entity_id' => $entityId,
            'old_data' => $old ? json_encode($old) : null,
            'new_data' => $new ? json_encode($new) : null,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}