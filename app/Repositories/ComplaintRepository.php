<?php

namespace App\Repositories;

use App\Models\Appeal;
use App\Models\Complaint;
use App\Models\ComplaintTimeline;
use App\Models\Dispute;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ComplaintRepository
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Complaint::with(['raisedBy', 'againstUser', 'againstProperty']);
        foreach (['status', 'category', 'priority'] as $field) if (! empty($filters[$field])) $query->where($field, $filters[$field]);
        if (! empty($filters['from_date'])) $query->whereDate('created_at', '>=', $filters['from_date']);
        if (! empty($filters['to_date'])) $query->whereDate('created_at', '<=', $filters['to_date']);
        if (! empty($filters['search'])) $query->where(fn ($q) => $q->where('complaint_number', 'like', "%{$filters['search']}%")->orWhere('description', 'like', "%{$filters['search']}%"));
        return $query->latest()->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findOrFail(int $id): Complaint
    {
        return Complaint::with(['raisedBy', 'againstUser', 'againstProperty', 'evidence', 'timeline.actor', 'dispute.appeals'])->findOrFail($id);
    }

    public function findForUpdate(int $id): Complaint { return Complaint::lockForUpdate()->findOrFail($id); }
    public function update(Complaint $complaint, array $data): Complaint { $complaint->forceFill($data)->save(); return $complaint->refresh(); }
    public function createDispute(int $complaintId): Dispute { return Dispute::firstOrCreate(['complaint_id' => $complaintId], ['status' => 'open']); }
    public function createTimeline(int $id, string $action, ?string $note, ?int $actor): ComplaintTimeline { return ComplaintTimeline::create(['complaint_id' => $id, 'action' => $action, 'note' => $note, 'actor_user_id' => $actor, 'created_at' => now()]); }
    public function findDisputeForUpdate(int $id): Dispute { return Dispute::lockForUpdate()->findOrFail($id); }
    public function resolveDispute(Dispute $dispute, string $resolution, ?int $adminId): Dispute { $dispute->forceFill(['status' => 'resolved', 'resolution' => $resolution, 'resolved_by' => $adminId])->save(); return $dispute->refresh(); }
    public function findAppealForUpdate(int $id): Appeal { return Appeal::lockForUpdate()->findOrFail($id); }

    public function stats(): array
    {
        return [
            'by_status' => Complaint::select('status', DB::raw('COUNT(*) as count'))->groupBy('status')->pluck('count', 'status'),
            'by_priority' => Complaint::select('priority', DB::raw('COUNT(*) as count'))->groupBy('priority')->pluck('count', 'priority'),
            'by_category' => Complaint::select('category', DB::raw('COUNT(*) as count'))->groupBy('category')->pluck('count', 'category'),
            'average_resolution_hours' => round((float) Complaint::whereNotNull('resolved_at')->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as average')->value('average'), 2),
        ];
    }

    public function create(array $payload): Complaint
    {
        return Complaint::create($payload);
    }

    public function hasOpenByBookingAndRaisedBy(int $bookingId, int $raisedBy): bool
    {
        return Complaint::query()
            ->where('booking_id', $bookingId)
            ->where('raised_by', $raisedBy)
            ->where('status', 'pending')
            ->exists();
    }
}
