<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminRbacController extends Controller
{
    public function roles()
    {
        $roles = AdminRole::with(['permissions:id,key,name,module,description'])
            ->orderBy('code')->get(['id','code','name','description']);
        return $this->successResponse('Admin roles fetched.', $roles);
    }

    public function permissions()
    {
        $permissions = Permission::orderBy('module')->orderBy('key')
            ->get(['id','key','name','module','description']);
        return $this->successResponse('Admin permissions fetched.', $permissions);
    }

    public function updateRolePermissions(Request $request, AdminRole $role)
    {
        $data = $request->validate([
            'permission_keys' => ['required','array','min:1'],
            'permission_keys.*' => ['string','distinct',Rule::exists('permissions','key')],
        ]);

        $permissions = Permission::whereIn('key', $data['permission_keys'])
            ->orderBy('key')->get(['id','key']);

        return DB::transaction(function () use ($request,$role,$permissions) {
            $before = $role->permissions()->orderBy('permissions.key')
                ->pluck('permissions.key')->values()->all();
            $after = $permissions->pluck('key')->values()->all();

            if ($before !== $after) {
                $role->permissions()->sync($permissions->pluck('id')->all());
                $this->audit($request, 'permission_change', AdminRole::class, $role->id,
                    ['permission_keys'=>$before], ['permission_keys'=>$after],
                    'Updated permissions for admin role '.$role->code);
            }

            $role->load(['permissions:id,key,name,module,description']);
            return $this->successResponse('Admin role permissions updated.', $role);
        });
    }

    public function updateUserRoles(Request $request, Admin $user)
    {
        $data = $request->validate([
            'role_codes' => ['required','array','min:1'],
            'role_codes.*' => ['string','distinct',Rule::exists('admin_roles','code')],
        ]);

        $roles = AdminRole::whereIn('code',$data['role_codes'])
            ->orderBy('code')->get(['id','code']);

        return DB::transaction(function () use ($request,$user,$roles) {
            $before = $user->adminRoles()->orderBy('admin_roles.code')
                ->pluck('admin_roles.code')->values()->all();
            $after = $roles->pluck('code')->values()->all();

            if ($before !== $after) {
                $user->adminRoles()->sync($roles->pluck('id')->all());
                $this->audit($request, 'admin_role_change', User::class, $user->id,
                    ['role_codes'=>$before], ['role_codes'=>$after],
                    'Updated admin RBAC roles for user #'.$user->id);
            }

            $user->load(['adminRoles:id,code,name,description']);
            return $this->successResponse('Admin user roles updated.', $user);
        });
    }

    private function audit(Request $request, string $action, string $entityType, int $entityId,
        array $oldData, array $newData, string $description): void
    {
        AuditLog::create([
            'user_id'=>$request->user()->id,
            'user_type'=>$request->user()->role ?? 'admin',
            'action'=>$action,
            'module'=>'admin_rbac',
            'entity_type'=>$entityType,
            'entity_id'=>$entityId,
            'old_data'=>$oldData,
            'new_data'=>$newData,
            'description'=>$description,
            'ip_address'=>$request->ip(),
            'user_agent'=>$request->userAgent(),
            'metadata'=>['actor_user_id'=>$request->user()->id],
            'occurred_at'=>now(),
        ]);
    }
}
