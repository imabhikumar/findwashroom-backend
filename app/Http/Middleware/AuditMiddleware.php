<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log API requests for critical endpoints
        $criticalEndpoints = [
            'api/v1/auth',
            'api/v1/partner',
            'api/v1/admin',
            'api/v1/bookings',
            'api/v1/payments',
            'api/v1/wallet',
        ];

        foreach ($criticalEndpoints as $endpoint) {
            if (str_contains($request->path(), $endpoint)) {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'user_type' => Auth::user()?->role ?? 'guest',
                    'action' => strtolower($request->method()),
                    'module' => 'api',
                    'entity_type' => 'request',
                    'entity_id' => null,
                    'old_data' => null,
                    'new_data' => $request->all(),
                    'description' => "API {$request->method()} {$request->path()}",
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'occurred_at' => now(),
                ]);
                break;
            }
        }

        return $response;
    }
}