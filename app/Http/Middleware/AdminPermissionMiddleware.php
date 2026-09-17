<?php

namespace App\Http\Middleware;

use App\Models\AdminRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ($user->role ?? null) !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'data' => (object) [],
            ], 403);
        }

        $hasPermission = $user->adminRoles()
            ->whereHas('permissions', fn ($query) => $query->where('key', $permission))
            ->exists();

        if (! $hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
                'data' => (object) [],
            ], 403);
        }

        return $next($request);
    }
}
