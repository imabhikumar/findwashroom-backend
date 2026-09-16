<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $raisedBy = $this->raisedBy;
        $data = [
            'id' => $this->id,
            'complaint_number' => $this->complaint_number,
            'raised_by' => $raisedBy ? ['id' => $raisedBy->id, 'name' => $raisedBy->name, 'role' => $raisedBy->role] : null,
            'against' => $this->againstUser ? ['id' => $this->againstUser->id, 'name' => $this->againstUser->name] : null,
            'against_property' => $this->againstProperty ? ['id' => $this->againstProperty->id, 'name' => $this->againstProperty->name] : null,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => $this->status,
            'sla_due_at' => $this->sla_due_at,
            'created_at' => $this->created_at,
        ];

        if ($request->route('id') !== null) {
            $data += [
                'description' => $this->description,
                'resolved_at' => $this->resolved_at,
                'updated_at' => $this->updated_at,
                'evidence' => $this->evidence,
                'timeline' => $this->timeline,
                'dispute' => $this->dispute,
                'appeals' => $this->dispute?->appeals ?? [],
            ];
        }
        return $data;
    }
}