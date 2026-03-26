<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        if (! $user || ($user->role ?? null) !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'data' => (object) [],
            ], 403);
        }

        return $next($request);
    }
}

