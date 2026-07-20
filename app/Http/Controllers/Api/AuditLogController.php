<?php
// app/Http/Controllers/Api/AuditLogController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('user')
            ->when($request->module, fn($q) => $q->forModule($request->module))
            ->when($request->user_id, fn($q) => $q->forUser($request->user_id))
            ->when($request->action, fn($q) => $q->forAction($request->action))
            ->when($request->entity_type && $request->entity_id, 
                fn($q) => $q->forEntity($request->entity_type, $request->entity_id))
            ->orderBy('created_at', 'desc')
            ->limit($request->limit ?? 50)
            ->get();

        return $this->successResponse('Audit logs fetched.', AuditLogResource::collection($logs));
    }

    public function show(int $id)
    {
        $log = AuditLog::with('user')->findOrFail($id);
        return $this->successResponse('Audit log details.', new AuditLogResource($log));
    }
}