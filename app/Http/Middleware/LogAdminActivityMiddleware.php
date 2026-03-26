<?php

namespace App\Http\Middleware;

use App\Services\AdminActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivityMiddleware
{
    public function __construct(private readonly AdminActivityLogService $adminActivityLogService)
    {
    }

    public function handle(Request $request, Closure $next, string $actionType = 'admin.action'): Response
    {
        $admin = $request->user();

        $ip = $request->ip();
        $ua = $request->userAgent();

        // Default action_type for governance visibility.
        // We include route name if available; otherwise fallback to HTTP method+path.
        $routeAction = $request->route()?->getActionName();
        $computedActionType = $routeAction ?: ($request->method() . ' ' . $request->path());

        $description = $request->input('description')
            ?: $request->headers->get('X-Admin-Action-Description');

        $response = $next($request);

        // Best-effort logging: never block the response.
        try {
            $adminId = $admin?->id;
            $this->adminActivityLogService->log(
                $adminId,
                $computedActionType ?: $actionType,
                is_string($description) ? $description : null,
                is_string($ip) ? $ip : null,
                is_string($ua) ? $ua : null
            );
        } catch (\Throwable) {
            // ignore logging failures
        }

        return $response;
    }
}

