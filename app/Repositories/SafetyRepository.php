<?php

namespace App\Repositories;

use App\Models\Incident;
use App\Models\SafetyReport;
use App\Models\SosAlert;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SafetyRepository
{
    public function sos(array $filters): LengthAwarePaginator { return SosAlert::with(['user', 'booking'])->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($filters['from_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))->when($filters['to_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))->latest()->paginate((int) ($filters['per_page'] ?? 20)); }
    public function findSos(int $id): SosAlert { return SosAlert::with(['user', 'booking'])->findOrFail($id); }
    public function updateSos(SosAlert $sos, array $data): SosAlert { $sos->forceFill($data)->save(); return $sos->refresh(); }
    public function incidents(array $filters): LengthAwarePaginator { return Incident::with('reporter')->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($filters['severity'] ?? null, fn ($q, $v) => $q->where('severity', $v))->when($filters['category'] ?? null, fn ($q, $v) => $q->where('category', $v))->when($filters['from_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))->when($filters['to_date'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))->latest()->paginate((int) ($filters['per_page'] ?? 20)); }
    public function findIncident(int $id): Incident { return Incident::with('reporter')->findOrFail($id); }
    public function createIncident(array $data): Incident { $incident = Incident::create($data); return $incident->refresh(); }
    public function updateIncident(Incident $incident, array $data): Incident { $incident->forceFill($data)->save(); return $incident->refresh(); }
    public function reports() { return SafetyReport::with(['reporter', 'againstUser', 'againstProperty'])->latest()->get(); }
    public function stats(): array { return ['sos_open' => SosAlert::whereIn('status', ['triggered', 'acknowledged'])->count(), 'sos_today' => SosAlert::whereDate('created_at', today())->count(), 'incidents_open' => Incident::whereIn('status', ['open', 'investigating'])->count(), 'critical_incidents' => Incident::where('severity', 'critical')->whereIn('status', ['open', 'investigating'])->count(), 'avg_ack_seconds' => (float) (SosAlert::whereNotNull('acknowledged_at')->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, acknowledged_at)) as average')->value('average') ?? 0)]; }
}