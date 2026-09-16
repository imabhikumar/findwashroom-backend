<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\SafetyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SafetyService
{
    public function __construct(private readonly SafetyRepository $repository) {}
    public function sos(Request $request) { return $this->repository->sos($request->query()); }
    public function findSos(int $id) { return $this->repository->findSos($id); }
    public function acknowledge(int $id, Request $request) { return $this->changeSos($id, ['status' => 'acknowledged', 'acknowledged_by' => $request->user()?->getKey(), 'acknowledged_at' => now()], $request, 'acknowledge'); }
    public function resolveSos(int $id, array $data, Request $request) { return $this->changeSos($id, ['status' => 'resolved', 'resolved_at' => now()], $request, 'resolve', $data['note'] ?? null); }
    public function falseAlarm(int $id, string $reason, Request $request) { return $this->changeSos($id, ['status' => 'false_alarm'], $request, 'false_alarm', $reason); }
    public function incidents(Request $request) { return $this->repository->incidents($request->query()); }
    public function findIncident(int $id) { return $this->repository->findIncident($id); }
    public function reports() { return $this->repository->reports(); }
    public function stats(): array { return $this->repository->stats(); }

    public function createIncident(array $data, Request $request)
    {
        return DB::transaction(function () use ($data, $request) { $incident = $this->repository->createIncident($data); $this->audit($request, 'create', null, $incident->toArray(), $incident->id, 'Incident'); return $incident->load('reporter'); });
    }

    public function updateIncident(int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $data, $request) { $incident = $this->repository->findIncident($id); $old = $incident->toArray(); $incident = $this->repository->updateIncident($incident, $data); $this->audit($request, 'update', $old, $incident->toArray(), $id, 'Incident'); return $incident->load('reporter'); });
    }

    public function escalateIncident(int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $data, $request) { $incident = $this->repository->findIncident($id); $old = $incident->toArray(); $incident = $this->repository->updateIncident($incident, ['escalation_level' => $data['level']]); $this->audit($request, 'escalate', $old, ['level' => $data['level'], 'reason' => $data['reason']], $id, 'Incident'); return $incident->load('reporter'); });
    }

    private function changeSos(int $id, array $data, Request $request, string $action, ?string $note = null)
    {
        return DB::transaction(function () use ($id, $data, $request, $action, $note) { $sos = $this->repository->findSos($id); $old = $sos->toArray(); $sos = $this->repository->updateSos($sos, $data); $this->audit($request, $action, $old, $data + ($note ? ['note' => $note] : []), $id, 'SosAlert'); return $sos->load(['user', 'booking']); });
    }

    private function audit(Request $request, string $action, ?array $old, ?array $new, int $id, string $type): void
    {
        DB::table('audit_logs')->insert(['uuid' => (string) Str::uuid(), 'user_id' => $request->user()?->getKey(), 'user_type' => Admin::class, 'module' => 'safety', 'action' => $action, 'entity_type' => $type, 'entity_id' => $id, 'old_data' => $old ? json_encode($old) : null, 'new_data' => $new ? json_encode($new) : null, 'ip_address' => $request->ip(), 'created_at' => now(), 'updated_at' => now()]);
    }
}