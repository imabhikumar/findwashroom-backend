<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RequestRoleRequest;
use App\Http\Requests\Auth\SwitchRoleRequest;

class RoleController extends Controller
{
    /**
     * GET /auth/profile
     * Generic profile endpoint (API Contract Module 1) — works for any
     * authenticated identity regardless of active role.
     */
    public function profile()
    {
        $user = request()->user();

        return $this->successResponse('Profile fetched.', [
            'user' => $user,
            'active_role' => $user->role,
            'roles' => $user->userRoles()->pluck('role'),
        ]);
    }

    /**
     * GET /auth/roles
     * List the roles this identity currently holds.
     */
    public function myRoles()
    {
        $user = request()->user();

        return $this->successResponse('Roles fetched.', [
            'active_role' => $user->role,
            'roles' => $user->userRoles()->get(['role', 'status']),
        ]);
    }

    /**
     * POST /auth/roles
     * Self-register an additional role (owner or cleaner) on this identity.
     * Per PDL-008, verification is encouraged but not mandatory, so the
     * role is usable immediately — it does not switch the active role.
     */
    public function requestRole(RequestRoleRequest $request)
    {
        $user = $request->user();
        $role = $user->grantRole($request->validated('role'));

        return $this->successResponse('Role granted.', [
            'role' => $role->role,
            'status' => $role->status,
            'roles' => $user->userRoles()->pluck('role'),
        ], 201);
    }

    /**
     * POST /auth/switch-role
     * Switch the active role (what RoleMiddleware checks) to one this
     * identity already holds.
     */
    public function switchRole(SwitchRoleRequest $request)
    {
        $user = $request->user();
        $role = $request->validated('role');

        if (! $user->hasRole($role)) {
            return $this->errorResponse(
                "You don't have the {$role} role yet. Request it first via POST /auth/roles.",
                null,
                403
            );
        }

        $user->role = $role;
        $user->save();

        return $this->successResponse('Active role switched.', [
            'user' => $user->refresh(),
            'active_role' => $user->role,
        ]);
    }
}
