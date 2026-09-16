<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Repositories\ComplaintRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComplaintService
{
    public function __construct(private readonly ComplaintRepository $repository) {}
    public function paginate(Request $request): LengthAwarePaginator { return $this->repository->paginate($request->query()); }
    public function findOrFail(int $id) { return $this->repository->findOrFail($id); }

    public function update(int $id, array $data, Request $request)
    {
        return DB::transaction(function () use ($id, $data, $request) {
            $complaint = $this->repository->findForUpdate($id);
            $old = $complaint->toArray();
            $complaint = $this->repository->update($complaint, ['status' => $data['status'], 'priority' => $data['priority']]);
            $this->timeline($complaint->id, 'updated_by_admin', $data['note'] ?? null, $request->user()?->getKey());
            $this->audit($request, 'update', $old, $data, $complaint->id);
            return $this->repository->findOrFail($complaint->id);
        });
    }

    public function resolve(int $id, string $resolution, Request $request)
    {
        return DB::transaction(function () use ($id, $resolution, $request) {
            $complaint = $this->repository->findForUpdate($id);
            $old = $complaint->toArray();
            $complaint = $this->repository->update($complaint, ['status' => 'resolved', 'resolved_at' => now()]);
            $this->timeline($complaint->id, 'resolved_by_admin', $resolution, $request->user()?->getKey());
            $this->audit($request, 'resolve', $old, ['resolution' => $resolution, 'status' => 'resolved'], $complaint->id);
            return $this->repository->findOrFail($complaint->id);
        });
    }

    public function escalate(int $id, Request $request)
    {
        return DB::transaction(function () use ($id, $request) {
            $complaint = $this->repository->findForUpdate($id);
            $dispute = $this->repository->createDispute($complaint->id);
            $this->timeline($complaint->id, 'escalated_to_dispute', null, $request->user()?->getKey());
            $this->audit($request, 'escalate', null, ['dispute_id' => $dispute->id], $complaint->id);
            return $this->repository->findOrFail($complaint->id);
        });
    }

    public function resolveDispute(int $id, string $resolution, Request $request)
    {
        return DB::transaction(function () use ($id, $resolution, $request) {
            $dispute = $this->repository->findDisputeForUpdate($id);
            $dispute = $this->repository->resolveDispute($dispute, $resolution, $request->user()?->getKey());
            $this->timeline($dispute->complaint_id, 'dispute_resolved', $resolution, $request->user()?->getKey());
            $this->audit($request, 'resolve', null, ['resolution' => $resolution], $dispute->id, 'Dispute');
            return $dispute->load('complaint');
        });
    }

    public function rejectAppeal(int $id, string $reason, Request $request)
    {
        return DB::transaction(function () use ($id, $reason, $request) {
            $appeal = $this->repository->findAppealForUpdate($id);
            $appeal->forceFill(['status' => 'rejected'])->save();
            $this->audit($request, 'reject_appeal', null, ['reason' => $reason], $appeal->id, 'Appeal');
            return $appeal->refresh();
        });
    }

    public function stats(): array { return $this->repository->stats(); }

    private function timeline(int $complaintId, string $action, ?string $note, ?int $actor): void
    {
        $this->repository->createTimeline($complaintId, $action, $note, $actor);
    }

    private function audit(Request $request, string $action, ?array $old, ?array $new, int $id, string $type = 'Complaint'): void
    {
        DB::table('audit_logs')->insert([
            'uuid' => (string) Str::uuid(), 'user_id' => $request->user()?->getKey(), 'user_type' => Admin::class,
            'module' => strtolower($type) . 's', 'action' => $action, 'entity_type' => $type, 'entity_id' => $id,
            'old_data' => $old ? json_encode($old) : null, 'new_data' => $new ? json_encode($new) : null,
            'ip_address' => $request->ip(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}