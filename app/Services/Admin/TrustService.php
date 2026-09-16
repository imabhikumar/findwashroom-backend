<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\TrustRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TrustService
{
    public function __construct(private readonly TrustRepository $repository) {}
    public function events(Request $request) { return $this->repository->events($request->query()); }
    public function users(Request $request) { return $this->repository->users($request->query()); }
    public function user(int $id): array { return $this->repository->user($id); }
    public function badges() { return $this->repository->badges(); }

    public function storeEvent(array $data, Request $request)
    {
        return DB::transaction(function () use ($data, $request) {
            $event = $this->repository->createEvent($data);
            $this->audit($request, 'create_event', null, $event->toArray(), $event->id, 'TrustEvent');
            return $event->load('user');
        });
    }

    public function createBadge(array $data, Request $request)
    {
        return DB::transaction(function () use ($data, $request) {
            $badge = $this->repository->createBadge($data);
            $this->audit($request, 'create', null, $badge->toArray(), $badge->id, 'Badge');
            return $badge;
        });
    }

    public function updateBadge(int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $data, $request) {
            $badge = $this->repository->findBadge($id);
            $old = $badge->toArray();
            $badge = $this->repository->updateBadge($badge, $data);
            $this->audit($request, 'update', $old, $badge->toArray(), $id, 'Badge');
            return $badge;
        });
    }

    public function deleteBadge(int $id, Request $request): void
    {
        DB::transaction(function () use ($id, $request) {
            $badge = $this->repository->findBadge($id);
            $old = $badge->toArray();
            $badge->delete();
            $this->audit($request, 'delete', $old, null, $id, 'Badge');
        });
    }

    public function assignBadge(array $data, Request $request): array
    {
        return DB::transaction(function () use ($data, $request) {
            $result = $this->repository->assignBadge($data, $request->user()?->getKey());
            $this->audit($request, 'assign', null, $data, $result['id'], 'BadgeAssignment');
            return $result;
        });
    }

    public function revokeBadge(array $data, Request $request): array
    {
        return DB::transaction(function () use ($data, $request) {
            $result = $this->repository->revokeBadge($data);
            $this->audit($request, 'revoke', $data, null, $result['id'] ?? 0, 'BadgeAssignment');
            return $result;
        });
    }

    private function audit(Request $request, string $action, ?array $old, ?array $new, int $id, string $type): void
    {
        DB::table('audit_logs')->insert([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()?->getKey(),
            'user_type' => Admin::class,
            'module' => 'trust',
            'action' => $action,
            'entity_type' => $type,
            'entity_id' => $id,
            'old_data' => $old ? json_encode($old) : null,
            'new_data' => $new ? json_encode($new) : null,
            'ip_address' => $request->ip(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}